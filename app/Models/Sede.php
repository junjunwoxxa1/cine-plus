<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Modelo para las Sedes / Complejos de Cine+.
 *
 * @property int $id
 * @property string $nombre
 * @property string $ciudad
 * @property string|null $direccion
 * @property string|null $telefono
 * @property bool $activo
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Collection<int, Sala> $salas
 * @property-read Collection<int, Funcion> $funciones
 */
class Sede extends Model
{
    use HasFactory;

    protected $table = 'sedes';

    protected $fillable = [
        'nombre',
        'ciudad',
        'direccion',
        'telefono',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Salas pertenecientes a esta sede.
     */
    public function salas(): HasMany
    {
        return $this->hasMany(Sala::class, 'sede', 'nombre');
    }

    /**
     * Funciones programadas en todas las salas de esta sede.
     */
    public function funciones(): HasManyThrough
    {
        return $this->hasManyThrough(Funcion::class, Sala::class, 'sede', 'sala_id', 'nombre', 'id');
    }

    /**
     * Scope para filtrar sólo sedes activas.
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}

