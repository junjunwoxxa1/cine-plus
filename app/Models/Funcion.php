<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Funcion extends Model
{
    use HasFactory;

    protected $table = 'funcion';

    protected $fillable = [
        'pelicula_id',
        'sala_id',
        'fecha_hora',
        'precio',
        'precio_puntos',
        'asientos_disponibles',
        'plantilla',
        'mapa_config',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'precio' => 'decimal:2',
        'precio_puntos' => 'integer',
        'asientos_disponibles' => 'integer',
        'mapa_config' => 'array',
    ];

    /**
     * Obtiene la configuración de distribución de butacas y escaleras para esta función
     * (usa la personalizada de la función si existe, o hereda la de su sala).
     *
     * @return array{plantilla: string, escaleras_despues_columna: array<int>, pasillos_despues_fila: array<string>, celdas_escalera: array<string>}
     */
    public function obtenerMapaConfig(): array
    {
        if (is_array($this->mapa_config) && ! empty($this->mapa_config)) {
            return [
                'plantilla' => $this->mapa_config['plantilla'] ?? ($this->plantilla ?: 'clasica'),
                'escaleras_despues_columna' => array_map('intval', $this->mapa_config['escaleras_despues_columna'] ?? []),
                'pasillos_despues_fila' => array_values($this->mapa_config['pasillos_despues_fila'] ?? []),
                'celdas_escalera' => array_values($this->mapa_config['celdas_escalera'] ?? []),
            ];
        }

        if ($this->relationLoaded('sala') && $this->sala) {
            return $this->sala->obtenerMapaConfig();
        }

        return $this->sala ? $this->sala->obtenerMapaConfig() : [
            'plantilla' => 'clasica',
            'escaleras_despues_columna' => [],
            'pasillos_despues_fila' => [],
            'celdas_escalera' => [],
        ];
    }

    /**
     * La función pertenece a una película.
     */
    public function pelicula(): BelongsTo
    {
        return $this->belongsTo(Pelicula::class, 'pelicula_id');
    }

    /**
     * La función se realiza en una sala.
     */
    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class, 'sala_id');
    }

    /**
     * Relación muchos a muchos con butacas (para verificar estado de disponibilidad).
     */
    public function butacas(): BelongsToMany
    {
        return $this->belongsToMany(Butaca::class, 'funcion_butaca', 'funcion_id', 'butaca_id')
            ->withPivot('disponible')
            ->withTimestamps();
    }
}
