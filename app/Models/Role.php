<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const ADMIN = 'admin';

    public const DULCERIA = 'dulceria';

    public const CLIENTE = 'cliente';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
