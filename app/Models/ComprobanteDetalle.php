<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $comprobante_id
 * @property string $itemable_type
 * @property int $itemable_id
 * @property string $descripcion
 * @property int $cantidad
 * @property float $precio_unitario
 * @property float $subtotal
 * @property bool $es_canje
 * @property int $puntos_utilizados
 */
class ComprobanteDetalle extends Model
{
    use HasFactory;

    protected $table = 'comprobante_detalle';

    protected $fillable = [
        'comprobante_id',
        'itemable_type',
        'itemable_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'es_canje',
        'puntos_utilizados',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'es_canje' => 'boolean',
        'puntos_utilizados' => 'integer',
    ];

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}

