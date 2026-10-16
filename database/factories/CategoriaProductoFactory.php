<?php

namespace Database\Factories;

use App\Models\CategoriaProducto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CategoriaProducto>
 */
class CategoriaProductoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->word();

        return [
            'nombre' => ucfirst($nombre),
            'slug' => Str::slug($nombre),
            'icono' => '🍿',
            'orden' => 0,
            'activo' => true,
        ];
    }

    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => ['activo' => false]);
    }
}
