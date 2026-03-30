<?php

namespace Goloba\GolobaRMA\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de Derecho de Retracto (Colombia — Ley 1480 de 2011, Art. 47).
 *
 * Reglas implementadas:
 *  - El plazo empieza a contar el día hábil SIGUIENTE a la fecha de entrega.
 *  - La ventana es de 5 días hábiles.
 *  - No cuentan sábados, domingos, ni festivos nacionales colombianos.
 *  - Los festivos se leen desde la tabla `colombian_holidays` (BD).
 *    Para agregar años futuros, ejecutar ColombianHolidaysSeeder con los datos
 *    correspondientes.
 */
class RetractoService
{
    /**
     * Cache en memoria de festivos por año para evitar queries repetidas
     * dentro del mismo request/proceso.
     *
     * @var array<int, string[]>
     */
    private array $holidaysCache = [];

    // =========================================================================
    // VENTANA Y ELEGIBILIDAD
    // =========================================================================

    /**
     * Verifica si la fecha de entrega aún está dentro de la ventana de retracto.
     */
    public function isWithinWindow(string $deliveryDate): bool
    {
        return Carbon::now(config('app.timezone', 'America/Bogota'))
            ->lte($this->calculateExpiresAt($deliveryDate));
    }

    /**
     * Calcula la fecha/hora límite para ejercer el retracto.
     * El cliente tiene hasta el final del día (23:59:59) del 5.° día hábil,
     * contando desde el día hábil siguiente a la entrega.
     */
    public function calculateExpiresAt(string $deliveryDate): Carbon
    {
        $tz      = config('app.timezone', 'America/Bogota');
        $current = Carbon::parse($deliveryDate, $tz)->startOfDay();
        $count   = 0;

        while ($count < 5) {
            $current->addDay();
            if ($this->isBusinessDay($current)) {
                $count++;
            }
        }

        return $current->endOfDay();
    }

    /**
     * Días hábiles restantes del plazo de retracto.
     *
     * Cuenta cuántos de los 5 días hábiles del plazo todavía no han transcurrido
     * completamente. El día de entrega no forma parte del plazo y no se cuenta,
     * incluso si el cliente solicita el retracto ese mismo día.
     *
     * Ejemplos:
     *  - Entrega hoy (lunes) → 5 días restantes (el plazo aún no ha empezado a correr)
     *  - Entrega ayer (viernes) → 4 días restantes si hoy es el 1.er día hábil del plazo
     *  - Último día del plazo → 1 día restante
     *  - Plazo expirado → 0
     */
    public function remainingBusinessDays(string $deliveryDate): int
    {
        $tz      = config('app.timezone', 'America/Bogota');
        $now     = Carbon::now($tz);
        $expires = $this->calculateExpiresAt($deliveryDate);

        if ($now->gt($expires)) {
            return 0;
        }

        // Recorremos los 5 días hábiles del plazo y contamos cuántos
        // comienzan después del momento actual.
        $count   = 0;
        $cursor  = Carbon::parse($deliveryDate, $tz)->startOfDay();
        $counted = 0;

        while ($counted < 5) {
            $cursor->addDay();
            if ($this->isBusinessDay($cursor)) {
                $counted++;
                // El día cuenta como "restante" si todavía no ha terminado.
                if ($now->lt($cursor->copy()->endOfDay())) {
                    $count++;
                }
            }
        }

        return $count;
    }

    // =========================================================================
    // CATEGORÍAS
    // =========================================================================

    /**
     * Evalúa la elegibilidad de retracto según las categorías de los productos.
     *
     * @param  int[]  $categoryIds
     * @return array{
     *   eligible: bool,
     *   has_conditional: bool,
     *   excluded_categories: int[],
     *   conditional_categories: int[]
     * }
     */
    public function checkCategories(array $categoryIds): array
    {
        $excluded    = config('retracto.excluded', []);
        $conditional = config('retracto.conditional', []);

        $excludedFound    = array_values(array_intersect($categoryIds, $excluded));
        $conditionalFound = array_values(array_intersect($categoryIds, $conditional));

        return [
            'eligible'               => empty($excludedFound),
            'has_conditional'        => !empty($conditionalFound),
            'excluded_categories'    => $excludedFound,
            'conditional_categories' => $conditionalFound,
        ];
    }

    // =========================================================================
    // DÍAS HÁBILES Y FESTIVOS
    // =========================================================================

    /**
     * Determina si una fecha es día hábil en Colombia.
     */
    public function isBusinessDay(Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return false;
        }

        return !in_array($date->format('Y-m-d'), $this->getHolidays($date->year), true);
    }

    /**
     * Retorna los festivos colombianos de un año dado desde la BD.
     *
     * Los resultados se cachean en memoria para evitar queries repetidas
     * dentro del mismo request (calculateExpiresAt puede iterar varios días
     * del mismo año consecutivamente).
     *
     * Si el año no tiene registros en `colombian_holidays`, lanza una excepción
     * para que el problema sea visible en lugar de silenciarse. Ejecuta
     * ColombianHolidaysSeeder para cargar años adicionales.
     *
     * @return string[]  Fechas en formato 'Y-m-d'
     *
     * @throws \RuntimeException  Si no hay festivos cargados para el año solicitado.
     */
    public function getHolidays(int $year): array
    {
        if (isset($this->holidaysCache[$year])) {
            return $this->holidaysCache[$year];
        }

        $rows = DB::table('colombian_holidays')
            ->where('year', $year)
            ->pluck('date')
            ->toArray();

        if (empty($rows)) {
            $message = "RetractoService: no hay festivos cargados en BD para el año {$year}. "
                . 'Ejecuta ColombianHolidaysSeeder para cargar el año.';

            Log::error($message);

            throw new \RuntimeException($message);
        }

        $this->holidaysCache[$year] = $rows;

        return $rows;
    }
}
