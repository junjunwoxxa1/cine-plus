<?php

namespace App\Models;

use App\Enums\TipoDescuento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    use HasFactory;

    protected $table = 'descuento';

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'tipo',
        'valor',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'tipo' => TipoDescuento::class,
        'valor' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Descuentos activos dentro de su rango de fechas.
     *
     * @param  Builder<Descuento>  $query
     */
    public function scopeVigentes(Builder $query): void
    {
        $hoy = today();

        $query->where('activo', true)
            ->where(fn (Builder $q) => $q->whereNull('fecha_inicio')->orWhereDate('fecha_inicio', '<=', $hoy))
            ->where(fn (Builder $q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $hoy));
    }

    /**
     * Descuentos visibles en el selector del checkout (sin código promocional).
     *
     * @param  Builder<Descuento>  $query
     */
    public function scopePublicos(Builder $query): void
    {
        $query->whereNull('codigo');
    }

    public function calcular(float $subtotalEntradas, int $cantidadEntradas): float
    {
        return $this->tipo->calcular((float) $this->valor, $subtotalEntradas, $cantidadEntradas);
    }
}
