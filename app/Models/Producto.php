<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'producto';

    protected $fillable = [
        'categoria_producto_id',
        'nombre',
        'descripcion',
        'imagen',
        'precio',
        'precio_puntos',
        'stock',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_puntos' => 'integer',
        'stock' => 'integer',
        'activo' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_producto_id');
    }

    /**
     * Productos activos con stock disponible.
     *
     * @param  Builder<Producto>  $query
     */
    public function scopeDisponibles(Builder $query): void
    {
        $query->where('activo', true)->where('stock', '>', 0);
    }

    /**
     * Accesor para obtener la URL completa de la imagen del producto.
     */
    public function getImagenUrlAttribute(): string
    {
        if ($this->imagen && filter_var($this->imagen, FILTER_VALIDATE_URL)) {
            return $this->imagen;
        }

        if ($this->imagen) {
            return asset('storage/'.$this->imagen);
        }

        return 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=400&h=300&fit=crop';
    }
}
