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
        if ($this->imagen && filter_var($this->imagen, FILTER_VALIDATE_URL)) {
            return $this->imagen;
        }

        return $this->imagen
            ? asset('storage/' . $this->imagen)
            : asset('images/default-movie.jpg');
    }

    /**
     * Una película tiene muchas funciones.
     */
    public function funciones(): HasMany
    {
        return $this->hasMany(Funcion::class, 'pelicula_id');
    }
}
