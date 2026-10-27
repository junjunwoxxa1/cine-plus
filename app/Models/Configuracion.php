<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Modelo para configuraciones y parámetros globales del sistema.
 *
 * @property int $id
 * @property string $clave
 * @property string|null $valor
 * @property string|null $descripcion
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
    ];

    /**
     * Obtiene el valor de una configuración global.
     */
    public static function get(string $clave, mixed $default = null): mixed
    {
        $registro = static::where('clave', $clave)->first();

        if (! $registro) {
            return $default;
        }

        return $registro->valor;
    }

    /**
     * Guarda o actualiza una configuración global.
     */
    public static function set(string $clave, mixed $valor, ?string $descripcion = null): static
    {
        $valorString = is_bool($valor) ? ($valor ? '1' : '0') : (string) $valor;

        $registro = static::updateOrCreate(
            ['clave' => $clave],
            array_filter([
                'valor' => $valorString,
                'descripcion' => $descripcion,
            ], fn ($val) => $val !== null)
        );

        Cache::forget("config:{$clave}");

        return $registro;
    }

    /**
     * Determina si la modalidad general de puntos separados (Cine vs Dulcería) está activa.
     */
    public static function puntosSeparadosActivo(): bool
    {
        $valor = static::get('puntos_separados_activo', '1');

        return in_array((string) $valor, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Alterna la activación general de puntos separados.
     */
    public static function togglePuntosSeparados(): bool
    {
        $estadoActual = static::puntosSeparadosActivo();
        $nuevoEstado = ! $estadoActual;

        static::set(
            'puntos_separados_activo',
            $nuevoEstado ? '1' : '0',
            'Activa o desactiva a nivel general los puntos y canjes separados (Cine vs Dulcería)'
        );

        return $nuevoEstado;
    }
}
