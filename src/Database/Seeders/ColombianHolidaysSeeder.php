<?php

namespace Goloba\GolobaRMA\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder de carga inicial de festivos colombianos.
 *
 * Carga los días festivos nacionales de Colombia para los años 2026 y 2027.
 * Los datos provienen de los archivos CSV oficiales ubicados en la carpeta
 * de documentación del módulo RMA.
 *
 * Para cargar años adicionales, agregar las fechas correspondientes al array
 * $holidays y ejecutar este seeder de nuevo (usa updateOrInsert — es idempotente).
 *
 * Ejecución:
 *   php artisan db:seed --class="Goloba\GolobaRMA\Database\Seeders\ColombianHolidaysSeeder"
 */
class ColombianHolidaysSeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            // -----------------------------------------------------------------
            // 2026 — fuente: festivos_es_2026.csv
            // -----------------------------------------------------------------
            ['date' => '2026-01-01', 'name' => 'Año Nuevo'],
            ['date' => '2026-01-12', 'name' => 'Epifanía'],
            ['date' => '2026-03-23', 'name' => 'Día de San José'],
            ['date' => '2026-04-02', 'name' => 'Jueves Santo'],
            ['date' => '2026-04-03', 'name' => 'Viernes Santo'],
            ['date' => '2026-05-01', 'name' => 'Día del Trabajo'],
            ['date' => '2026-05-18', 'name' => 'Ascensión de Jesús'],
            ['date' => '2026-06-08', 'name' => 'Corpus Christi'],
            ['date' => '2026-06-15', 'name' => 'Sagrado Corazón de Jesús'],
            ['date' => '2026-06-29', 'name' => 'San Pedro y San Pablo'],
            ['date' => '2026-07-20', 'name' => 'Día de la Independencia'],
            ['date' => '2026-08-07', 'name' => 'Batalla de Boyacá'],
            ['date' => '2026-08-17', 'name' => 'Asunción de la Virgen'],
            ['date' => '2026-10-12', 'name' => 'Día de la Diversidad Étnica y Cultural'],
            ['date' => '2026-11-02', 'name' => 'Todos los Santos'],
            ['date' => '2026-11-16', 'name' => 'Independencia de Cartagena'],
            ['date' => '2026-12-08', 'name' => 'Inmaculada Concepción'],
            ['date' => '2026-12-25', 'name' => 'Navidad'],

            // -----------------------------------------------------------------
            // 2027 — fuente: festivos_es_2027.csv
            // -----------------------------------------------------------------
            ['date' => '2027-01-01', 'name' => 'Año Nuevo'],
            ['date' => '2027-01-11', 'name' => 'Epifanía'],
            ['date' => '2027-03-22', 'name' => 'Día de San José'],
            ['date' => '2027-03-25', 'name' => 'Jueves Santo'],
            ['date' => '2027-03-26', 'name' => 'Viernes Santo'],
            ['date' => '2027-05-01', 'name' => 'Día del Trabajo'],
            ['date' => '2027-05-10', 'name' => 'Ascensión de Jesús'],
            ['date' => '2027-05-31', 'name' => 'Corpus Christi'],
            ['date' => '2027-06-07', 'name' => 'Sagrado Corazón de Jesús'],
            ['date' => '2027-07-05', 'name' => 'San Pedro y San Pablo'],
            ['date' => '2027-07-20', 'name' => 'Día de la Independencia'],
            ['date' => '2027-08-07', 'name' => 'Batalla de Boyacá'],
            ['date' => '2027-08-16', 'name' => 'Asunción de la Virgen'],
            ['date' => '2027-10-18', 'name' => 'Día de la Diversidad Étnica y Cultural'],
            ['date' => '2027-11-01', 'name' => 'Todos los Santos'],
            ['date' => '2027-11-15', 'name' => 'Independencia de Cartagena'],
            ['date' => '2027-12-08', 'name' => 'Inmaculada Concepción'],
            ['date' => '2027-12-25', 'name' => 'Navidad'],
        ];

        foreach ($holidays as $holiday) {
            DB::table('colombian_holidays')->updateOrInsert(
                ['date' => $holiday['date']],
                [
                    'name'       => $holiday['name'],
                    'year'       => (int) substr($holiday['date'], 0, 4),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $count = count($holidays);
        if ($this->command) {
            $this->command->info("ColombianHolidaysSeeder: {$count} festivos cargados (2026-2027).");
        } else {
            echo "ColombianHolidaysSeeder: {$count} festivos cargados (2026-2027)." . PHP_EOL;
        }
    }
}
