<?php

namespace Database\Factories;

use App\Enums\TipoDescuento;
use App\Models\Descuento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Descuento>
 */
class DescuentoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true),
            'descripcion' => fake()->sentence(),
            'codigo' => null,
            'tipo' => TipoDescuento::MontoPorEntrada,
            'valor' => 4,
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'activo' => true,
        ];
    }

    public function conCodigo(string $codigo): static
    {
        return $this->state(fn (array $attributes) => ['codigo' => $codigo]);
    }

    public function vencido(): static
    {
        return $this->state(fn (array $attributes) => ['fecha_fin' => today()->subDay()]);
    }
}
