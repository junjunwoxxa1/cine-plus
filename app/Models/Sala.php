<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sala extends Model
{
    use HasFactory;

    protected $table = 'sala';

    protected $fillable = [
        'numero_sala',
        'sede',
        'capacidad',
        'plantilla',
        'mapa_config',
    ];

    protected $casts = [
        'capacidad' => 'integer',
        'mapa_config' => 'array',
    ];

    /**
     * Catálogo de plantillas predefinidas de distribución de butacas y escaleras.
     *
     * @return array<string, array{nombre: string, icono: string, descripcion: string, preview: string}>
     */
    public static function catalogoPlantillas(): array
    {
        return [
            'clasica' => [
                'nombre' => 'Clásica Continua',
                'icono' => '🟦',
                'descripcion' => 'Bloque continuo de butacas sin pasillos intermedios.',
                'preview' => '▪▪▪▪▪▪▪▪',
            ],
            'escalera_col2' => [
                'nombre' => 'Escalera en 2da Columna',
                'icono' => '🪜',
                'descripcion' => '2 columnas de butacas a la izquierda + Escalera vertical + bloque principal.',
                'preview' => '▪▪ 🪜 ▪▪▪▪▪▪',
            ],
            'doble_escalera_extremos' => [
                'nombre' => '2 Escaleras en Ambos Extremos',
                'icono' => '🏛️',
                'descripcion' => '2 columnas a cada extremo separadas por 2 escaleras verticales laterales.',
                'preview' => '▪▪ 🪜 ▪▪▪▪ 🪜 ▪▪',
            ],
            'escaleras_extremos_2filas' => [
                'nombre' => '2 Escaleras en Extremos (2 Primeras Filas)',
                'icono' => '🎭',
                'descripcion' => 'En las primeras 2 filas (A y B) los 2 extremos tienen escaleras de acceso (2 posiciones por lado).',
                'preview' => '🪜🪜 ▪▪▪▪ 🪜🪜 (Filas A-B)',
            ],
            'escaleras_extremos_ultimas_2filas' => [
                'nombre' => '2 Escaleras en Extremos (2 Últimas Filas)',
                'icono' => '🎟️',
                'descripcion' => 'En las 2 últimas filas los 2 extremos son escaleras laterales de ingreso superior.',
                'preview' => '🪜🪜 ▪▪▪▪ 🪜🪜 (Últimas 2 filas)',
            ],
            'pasillo_central' => [
                'nombre' => 'Escalera Central + Pasillo Fila B',
                'icono' => '➗',
                'descripcion' => 'Escalera vertical al centro de la sala y pasillo horizontal tras la 2da fila.',
                'preview' => '▪▪▪▪ 🪜 ▪▪▪▪',
            ],
            'personalizada' => [
                'nombre' => 'Personalizada (Diseño Libre)',
                'icono' => '✨',
                'descripcion' => 'Agrega o quita escaleras en cualquier columna, fila o asiento con un clic.',
                'preview' => 'Diseño interactivo a medida',
            ],
        ];
    }

    /**
     * Genera la estructura JSON de configuración del mapa según la plantilla elegida.
     *
     * @return array{plantilla: string, escaleras_despues_columna: array<int>, pasillos_despues_fila: array<string>, celdas_escalera: array<string>}
     */
    public static function generarConfiguracionPlantilla(string $plantilla, int $filas = 5, int $columnas = 10): array
    {
        $letras = array_slice(range('A', 'Z'), 0, max(1, min(26, $filas)));
        $escalerasCol = [];
        $pasillosFila = [];
        $celdasEscalera = [];

        switch ($plantilla) {
            case 'escalera_col2':
                if ($columnas > 2) {
                    $escalerasCol = [2];
                }
                break;

            case 'doble_escalera_extremos':
                if ($columnas > 4) {
                    $escalerasCol = array_values(array_unique([2, $columnas - 2]));
                } elseif ($columnas > 2) {
                    $escalerasCol = [2];
                }
                break;

            case 'escaleras_extremos_2filas':
                $filasTarget = array_slice($letras, 0, min(2, count($letras)));
                $anchoExtremo = $columnas >= 6 ? 2 : 1;
                foreach ($filasTarget as $letra) {
                    for ($c = 1; $c <= $anchoExtremo; $c++) {
                        $celdasEscalera[] = "{$letra}-{$c}";
                    }
                    for ($c = max(1, $columnas - $anchoExtremo + 1); $c <= $columnas; $c++) {
                        $celdasEscalera[] = "{$letra}-{$c}";
                    }
                }
                if ($columnas > 4) {
                    $escalerasCol = array_values(array_unique([2, $columnas - 2]));
                }
                break;

            case 'escaleras_extremos_ultimas_2filas':
                $filasTarget = array_slice($letras, -min(2, count($letras)));
                $anchoExtremo = $columnas >= 6 ? 2 : 1;
                foreach ($filasTarget as $letra) {
                    for ($c = 1; $c <= $anchoExtremo; $c++) {
                        $celdasEscalera[] = "{$letra}-{$c}";
                    }
                    for ($c = max(1, $columnas - $anchoExtremo + 1); $c <= $columnas; $c++) {
                        $celdasEscalera[] = "{$letra}-{$c}";
                    }
                }
                if ($columnas > 4) {
                    $escalerasCol = array_values(array_unique([2, $columnas - 2]));
                }
                break;

            case 'pasillo_central':
                if ($columnas >= 4) {
                    $escalerasCol = [(int) floor($columnas / 2)];
                }
                if (count($letras) >= 3) {
                    $pasillosFila = ['B'];
                }
                break;

            case 'clasica':
            default:
                break;
        }

        return [
            'plantilla' => $plantilla,
            'escaleras_despues_columna' => $escalerasCol,
            'pasillos_despues_fila' => $pasillosFila,
            'celdas_escalera' => array_values(array_unique($celdasEscalera)),
        ];
    }

    /**
     * Obtiene la configuración normalizada del mapa de la sala.
     *
     * @return array{plantilla: string, escaleras_despues_columna: array<int>, pasillos_despues_fila: array<string>, celdas_escalera: array<string>}
     */
    public function obtenerMapaConfig(): array
    {
        $cfg = is_array($this->mapa_config) ? $this->mapa_config : [];

        return [
            'plantilla' => $cfg['plantilla'] ?? ($this->plantilla ?: 'clasica'),
            'escaleras_despues_columna' => array_map('intval', $cfg['escaleras_despues_columna'] ?? []),
            'pasillos_despues_fila' => array_values($cfg['pasillos_despues_fila'] ?? []),
            'celdas_escalera' => array_values($cfg['celdas_escalera'] ?? []),
        ];
    }

    /**
     * Una sala tiene muchas butacas.
     */
    public function butacas(): HasMany
    {
        return $this->hasMany(Butaca::class, 'sala_id');
    }

    /**
     * Una sala tiene muchas funciones programadas.
     */
    public function funciones(): HasMany
    {
        return $this->hasMany(Funcion::class, 'sala_id');
    }

    /**
     * Sede o complejo a la que pertenece la sala.
     */
    public function sedeRelacion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede', 'nombre');
    }
}
