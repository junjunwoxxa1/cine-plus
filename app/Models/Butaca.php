<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Butaca extends Model
{
    use HasFactory;

    protected $table = 'butaca';

    protected $fillable = [
        'numero_butaca',
        'sala_id',
    ];

    /**
     * La butaca pertenece a una sala.
     */
    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class, 'sala_id');
    }

    /**
     * Relación muchos a muchos con funciones a través de funcion_butaca.
     */
    public function funciones(): BelongsToMany
    {
        return $this->belongsToMany(Funcion::class, 'funcion_butaca', 'butaca_id', 'funcion_id')
            ->withPivot('disponible')
            ->withTimestamps();
    }
}
