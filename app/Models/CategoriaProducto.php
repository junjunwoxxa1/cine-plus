<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaProducto extends Model
{
    use HasFactory;

    protected $table = 'categoria_producto';

    protected $fillable = [
        'nombre',
        'slug',
        'icono',
        'orden',
        'activo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * @param  Builder<CategoriaProducto>  $query
     */
    public function scopeActivas(Builder $query): void
    {
        $query->where('activo', true)->orderBy('orden')->orderBy('nombre');
    }

    /**
     * @param  Builder<CategoriaProducto>  $query
     */
    public function scopeActivos(Builder $query): void
    {
        $query->where('activo', true)->orderBy('orden')->orderBy('nombre');
    }
}
