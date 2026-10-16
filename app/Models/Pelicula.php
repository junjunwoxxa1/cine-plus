<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pelicula extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'pelicula';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'titulo',
        'slug',
        'imagen',
        'descripcion',
        'director',
        'genero',
        'fecha_estreno',
        'duracion',
        'clasificacion',
        'idioma',
        'en_banner',
        'orden_banner',
    ];

    /**
     * Los atributos que deben ser casteados a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_estreno' => 'date',
        'duracion' => 'integer',
        'en_banner' => 'boolean',
        'orden_banner' => 'integer',
    ];

    /**
     * Scope para películas elegidas para el banner carrusel principal.
     */
    public function scopeEnBanner($query)
    {
        return $query->where('en_banner', true)->orderBy('orden_banner')->orderBy('id', 'desc');
    }

    /**
     * Boot del modelo para generar el slug automáticamente al crear o actualizar si no se provee.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pelicula) {
            if (empty($pelicula->slug)) {
                $pelicula->slug = Str::slug($pelicula->titulo);
            }
        });

        static::updating(function ($pelicula) {
            if ($pelicula->isDirty('titulo') && !$pelicula->isDirty('slug')) {
                $pelicula->slug = Str::slug($pelicula->titulo);
            }
        });
    }

    /**
     * Accesor para obtener la URL completa de la imagen de la película.
     */
    public function getImagenUrlAttribute(): string
    {
        $img = trim((string) $this->imagen);

        if ($img === '') {
            return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600&h=800&fit=crop';
        }

        // Si ya es un enlace completo http:// o https:// o protocol-relative //
        if (preg_match('/^(https?:\/\/|\/\/|data:image\/)/i', $img)) {
            return $img;
        }

        // Quitar diagonales iniciales
        $img = ltrim($img, '/\\');

        // Si ya empieza con storage/, evitar duplicar asset('storage/storage/...')
        if (str_starts_with($img, 'storage/')) {
            return asset($img);
        }

        return asset('storage/' . $img);
    }

    /**
     * Una película tiene muchas funciones.
     */
    public function funciones(): HasMany
    {
        return $this->hasMany(Funcion::class, 'pelicula_id');
    }
}
