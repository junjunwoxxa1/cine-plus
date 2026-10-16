<?php

namespace Database\Factories;

use App\Models\CategoriaProducto;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $precio = fake()->randomFloat(2, 5, 60);

        return [
            'categoria_producto_id' => CategoriaProducto::factory(),
            'nombre' => fake()->unique()->words(2, true),
            'descripcion' => fake()->sentence(),
            'imagen' => null,
            'precio' => $precio,
            'precio_puntos' => (int) round($precio * 10),
            'stock' => 50,
            'activo' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => ['activo' => false]);
    }

    public function agotado(): static
    {
        return $this->state(fn (array $attributes) => ['stock' => 0]);
    }
}
