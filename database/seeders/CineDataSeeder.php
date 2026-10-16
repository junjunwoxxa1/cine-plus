<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CineDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // -------------------------------------------------------------
        // 1. SALAS Y BUTACAS
        // (Los productos de dulcería se cargan en CatalogoDulceriaSeeder)
        // -------------------------------------------------------------
        $salasData = [
            ['numero_sala' => 'Sala 1 (3D)', 'sede' => 'Cine+ Alcázar (Miraflores)', 'filas' => ['A', 'B', 'C', 'D'], 'asientos_por_fila' => 8],
            ['numero_sala' => 'Sala 2 (2D)', 'sede' => 'Cine+ Mall del Sur (SJM)', 'filas' => ['A', 'B', 'C', 'D', 'E'], 'asientos_por_fila' => 10],
            ['numero_sala' => 'Sala 3 (XD / IMAX)', 'sede' => 'Cine+ San Miguel', 'filas' => ['A', 'B', 'C', 'D', 'E', 'F'], 'asientos_por_fila' => 10],
        ];

        $salasCreadas = [];

        foreach ($salasData as $salaInfo) {
            $capacidadTotal = count($salaInfo['filas']) * $salaInfo['asientos_por_fila'];

            $salaId = DB::table('sala')->insertGetId([
                'numero_sala' => $salaInfo['numero_sala'],
                'sede' => $salaInfo['sede'],
                'capacidad' => $capacidadTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $butacaIds = [];

            // Generación de butacas (ej. A-1, A-2, B-1, etc.)
            foreach ($salaInfo['filas'] as $fila) {
                for ($num = 1; $num <= $salaInfo['asientos_por_fila']; $num++) {
                    $butacaId = DB::table('butaca')->insertGetId([
                        'numero_butaca' => "{$fila}-{$num}",
                        'sala_id' => $salaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $butacaIds[] = $butacaId;
                }
            }

            $salasCreadas[] = [
                'id' => $salaId,
                'capacidad' => $capacidadTotal,
                'butaca_ids' => $butacaIds,
            ];
        }

        // -------------------------------------------------------------
        // 2. FUNCIONES Y DISPONIBILIDAD DE BUTACAS
        // -------------------------------------------------------------
        $peliculasIds = DB::table('pelicula')->pluck('id')->toArray();

        // Si no existen películas aún, omitimos la creación de funciones
        if (empty($peliculasIds)) {
            return;
        }

        $horarios = ['14:30:00', '17:45:00', '20:30:00', '22:15:00'];
        $dias = [0, 1, 2, 3, 4, 5, 6]; // Próximos 7 días (Hoy a 6 días después)

        foreach ($dias as $diasOffset) {
            $fechaBase = Carbon::today()->addDays($diasOffset);

            foreach ($salasCreadas as $index => $sala) {
                foreach ($horarios as $hIndex => $hora) {
                    $peliculaId = $peliculasIds[($diasOffset * 4 + $index * 3 + $hIndex) % count($peliculasIds)];
                    $fechaHora = $fechaBase->format('Y-m-d').' '.$hora;

                    $funcionId = DB::table('funcion')->insertGetId([
                        'pelicula_id' => $peliculaId,
                        'sala_id' => $sala['id'],
                        'fecha_hora' => $fechaHora,
                        'precio' => 25.00,
                        'precio_puntos' => 250,
                        'asientos_disponibles' => $sala['capacidad'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Generar registros pivote en batch para máximo rendimiento
                    $pivoteRows = [];
                    foreach ($sala['butaca_ids'] as $butacaId) {
                        $pivoteRows[] = [
                            'funcion_id' => $funcionId,
                            'butaca_id' => $butacaId,
                            'disponible' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (! empty($pivoteRows)) {
                        DB::table('funcion_butaca')->insert($pivoteRows);
                    }
                }
            }
        }
    }
}
