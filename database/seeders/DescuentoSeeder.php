<?php

namespace Database\Seeders;

use App\Enums\TipoDescuento;
use App\Models\Descuento;
use Illuminate\Database\Seeder;

class DescuentoSeeder extends Seeder
{
    /**
     * Descuentos y promociones iniciales.
     */
    public function run(): void
    {
        $descuentos = [
            ['nombre' => 'Estudiante', 'descripcion' => 'Descuento especial universitario.', 'codigo' => 'ESTUDIANTE', 'tipo' => TipoDescuento::MontoPorEntrada, 'valor' => 4],
            ['nombre' => 'Socio Cine+', 'descripcion' => 'Promoción exclusiva para socios.', 'codigo' => 'SOCIOCINE', 'tipo' => TipoDescuento::MontoTotal, 'valor' => 8],
            ['nombre' => 'Miércoles 50%', 'descripcion' => 'Código promocional de lanzamiento.', 'codigo' => 'CINE50', 'tipo' => TipoDescuento::Porcentaje, 'valor' => 50],
        ];

        foreach ($descuentos as $descuento) {
            Descuento::updateOrCreate(['nombre' => $descuento['nombre']], [...$descuento, 'activo' => true]);
        }
    }
}

