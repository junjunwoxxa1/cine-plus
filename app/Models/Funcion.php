<?php

namespace App\Models;

use Carbon\CarbonInterface;
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
     * Duración en minutos de la película asignada a la función (por defecto 120 min).
     */
    public function getDuracionMinutosAttribute(): int
    {
        return (int) ($this->pelicula?->duracion ?: 120);
    }

    /**
     * Convierte minutos enteros a formato 'H:MM hrs' (ej. 99 min => '1:39 hrs').
     */
    public static function minutosAHorasFormato(int $minutos): string
    {
        $horas = intdiv(max(0, $minutos), 60);
        $resto = max(0, $minutos) % 60;

        return sprintf('%d:%02d hrs', $horas, $resto);
    }

    /**
     * Duración formateada de la película en horas (ej. 99 min => '1:39 hrs').
     */
    public function getDuracionFormateadaAttribute(): string
    {
        return $this->pelicula ? $this->pelicula->duracion_formateada : self::minutosAHorasFormato($this->duracion_minutos);
    }

    /**
     * Tiempo reglamentario de receso y limpieza de sala entre funciones (15 minutos).
     */
    public static function recesoLimpiezaMinutos(): int
    {
        return 15;
    }

    /**
     * Duración total del bloque de ocupación en sala (duración de la película + 15 min de limpieza).
     */
    public function getDuracionTotalMinutosAttribute(): int
    {
        return $this->duracion_minutos + self::recesoLimpiezaMinutos();
    }

    /**
     * Duración total formateada en horas (película + 15 min limpieza).
     */
    public function getDuracionTotalFormateadaAttribute(): string
    {
        return self::minutosAHorasFormato($this->duracion_total_minutos);
    }

    /**
     * Fecha y hora en que culmina la proyección de la película.
     */
    public function getFechaFinPeliculaAttribute(): ?CarbonInterface
    {
        return $this->fecha_hora ? $this->fecha_hora->copy()->addMinutes($this->duracion_minutos) : null;
    }

    /**
     * Fecha y hora en que culmina el receso de limpieza y la sala queda libre para la siguiente función.
     */
    public function getFechaFinLimpiezaAttribute(): ?CarbonInterface
    {
        return $this->fecha_hora ? $this->fecha_hora->copy()->addMinutes($this->duracion_total_minutos) : null;
    }

    /**
     * Determina si un horario propuesto se solapa con esta función (incluyendo sus 15 min de limpieza).
     */
    public function colisionaCon(CarbonInterface $inicioPropuesto, int $duracionPropuestaMinutos): bool
    {
        if (! $this->fecha_hora) {
            return false;
        }

        $inicioExistente = $this->fecha_hora->copy();
        $finExistente = $inicioExistente->copy()->addMinutes($this->duracion_total_minutos);

        $inicioNuevo = $inicioPropuesto->copy();
        $finNuevo = $inicioNuevo->copy()->addMinutes($duracionPropuestaMinutos + self::recesoLimpiezaMinutos());

        // Solapamiento de intervalos de tiempo [A_start, A_end) y [B_start, B_end)
        return $inicioNuevo->lt($finExistente) && $finNuevo->gt($inicioExistente);
    }

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
