<?php

namespace Goloba\GolobaRMA\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Webkul\Admin\Http\Controllers\Controller;

class HolidaysController extends Controller
{
    /**
     * Muestra la página de gestión de festivos (resumen por año).
     */
    public function index(): View
    {
        $yearCounts = DB::table('colombian_holidays')
            ->select('year', DB::raw('count(*) as total'))
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return view('goloba-rma::admin.holidays.index', compact('yearCounts'));
    }

    /**
     * Muestra el detalle de los festivos de un año específico.
     */
    public function show(int $year): View
    {
        $holidays = DB::table('colombian_holidays')
            ->where('year', $year)
            ->orderBy('date')
            ->get();

        if ($holidays->isEmpty()) {
            abort(404, "No hay festivos registrados para el año {$year}.");
        }

        return view('goloba-rma::admin.holidays.show', compact('year', 'holidays'));
    }

    /**
     * Elimina todos los festivos de un año completo.
     */
    public function destroyYear(int $year): JsonResponse
    {
        $deleted = DB::table('colombian_holidays')
            ->where('year', $year)
            ->delete();

        if (! $deleted) {
            return new JsonResponse(['message' => "No hay festivos registrados para el año {$year}."], 404);
        }

        return new JsonResponse(['message' => "Se eliminaron {$deleted} festivo(s) del año {$year}."]);
    }

    /**
     * Elimina un festivo individual por su fecha (YYYY-MM-DD).
     */
    public function destroy(Request $request, int $year, string $date): JsonResponse
    {
        $deleted = DB::table('colombian_holidays')
            ->where('year', $year)
            ->where('date', $date)
            ->delete();

        if (! $deleted) {
            return new JsonResponse(['message' => 'Festivo no encontrado.'], 404);
        }

        return new JsonResponse(['message' => "Festivo del {$date} eliminado correctamente."]);
    }


    /**
     * Procesa la importación de un CSV de festivos.
     *
     * Formato esperado del CSV:
     *   nombre,fecha
     *   "Año Nuevo",2028-01-01
     *
     * Solo inserta fechas que aún no existen en BD (updateOrInsert por date).
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:512',
        ]);

        $file   = $request->file('csv');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return new JsonResponse(['message' => 'No se pudo leer el archivo.'], 422);
        }

        // Saltar encabezado
        fgetcsv($handle);

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];
        $row      = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $row++;

            if (empty(array_filter($line))) {
                continue;
            }

            if (count($line) < 2) {
                $errors[] = "Fila {$row}: formato inválido (se esperan 2 columnas).";
                continue;
            }

            [$name, $date] = [$line[0], $line[1]];
            $name = trim($name);
            $date = trim($date);

            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $errors[] = "Fila {$row}: fecha '{$date}' inválida. Formato esperado: YYYY-MM-DD.";
                continue;
            }

            $year = (int) substr($date, 0, 4);

            if (DB::table('colombian_holidays')->where('date', $date)->exists()) {
                $skipped++;
                continue;
            }

            DB::table('colombian_holidays')->insert([
                'date'       => $date,
                'name'       => $name,
                'year'       => $year,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inserted++;
        }

        fclose($handle);

        $message = "{$inserted} festivo(s) importado(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} omitido(s) (ya existían en BD).";
        }
        if (! empty($errors)) {
            $message .= ' Se encontraron errores en algunas filas.';
        }

        return new JsonResponse([
            'message'  => $message,
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }
}
