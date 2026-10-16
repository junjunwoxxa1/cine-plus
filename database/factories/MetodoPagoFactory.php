<?php

namespace Database\Factories;

use App\Models\MetodoPago;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MetodoPago>
 */
class MetodoPagoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->word();

        return [
            'nombre' => ucfirst($nombre),
            'codigo' => Str::slug($nombre, '_'),
            'instrucciones' => null,
            'requiere_tarjeta' => false,
            'orden' => 0,
            'activo' => true,
        ];
    }

    public function tarjeta(): static
    {
        return $this->state(fn (array $attributes) => ['requiere_tarjeta' => true]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => ['activo' => false]);
    }
}
