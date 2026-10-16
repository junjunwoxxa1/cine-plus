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
        $img = trim((string) $this->imagen);

        if ($img === '') {
            return 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=400&h=300&fit=crop';
        }

        if (preg_match('/^(https?:\/\/|\/\/|data:image\/)/i', $img)) {
            return $img;
        }

        $img = ltrim($img, '/\\');

        if (str_starts_with($img, 'storage/')) {
            return asset($img);
        }

        return asset('storage/' . $img);
    }
}
