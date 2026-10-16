<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodo_pago';

    protected $fillable = [
        'nombre',
        'codigo',
        'instrucciones',
        'requiere_tarjeta',
        'orden',
        'activo',
    ];

    protected $casts = [
        'requiere_tarjeta' => 'boolean',
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    /**
     * @param  Builder<MetodoPago>  $query
     */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true)->orderBy('orden')->orderBy('nombre');
    }
}
