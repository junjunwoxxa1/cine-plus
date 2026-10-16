<?php

namespace Database\Seeders;

use App\Models\CategoriaProducto;
use Illuminate\Database\Seeder;

class CatalogoDulceriaSeeder extends Seeder
{
    /**
     * Categorías y productos iniciales de la dulcería.
     */
    public function run(): void
    {
        $catalogo = [
            ['nombre' => 'Combos', 'slug' => 'combos', 'icono' => '🍿', 'productos' => [
                ['Combo 1: Personal', '1 Canchita Mediana + 1 Gaseosa Mediana', 22.00, 50],
                ['Combo 2: Dúo', '1 Canchita Grande + 2 Gaseosas Medianas', 35.00, 50],
                ['Combo 3: Familiar', '2 Canchitas Grandes + 4 Gaseosas Medianas + Nachos', 55.00, 30],
            ]],
            ['nombre' => 'Canchitas', 'slug' => 'canchitas', 'icono' => '🍿', 'productos' => [
                ['Canchita Popcorn Grande', 'Canchita salada tamaño grande (150g)', 18.00, 100],
                ['Canchita Popcorn Mediana', 'Canchita salada tamaño mediano (100g)', 14.00, 100],
                ['Canchita Dulce Grande', 'Canchita acaramelada tamaño grande', 20.00, 80],
                ['Canchita Mixta Grande', 'Mitad salada, mitad dulce', 19.00, 70],
            ]],
            ['nombre' => 'Bebidas', 'slug' => 'bebidas', 'icono' => '🥤', 'productos' => [
                ['Gaseosa Mediana (32 oz)', 'Sabor a elección: Coca Cola, Inca Kola o Fanta', 10.00, 150],
                ['Gaseosa Grande (44 oz)', 'Sabor a elección: Coca Cola, Inca Kola o Fanta', 12.50, 150],
                ['Agua Mineral 600ml', 'Agua sin gas', 6.00, 200],
            ]],
            ['nombre' => 'Snacks', 'slug' => 'snacks', 'icono' => '🌭', 'productos' => [
                ['Nachos con Queso', 'Nachos crocantes con queso caliente', 16.00, 60],
                ['Hot Dog Gigante', 'Hot dog con salchicha de res y salsas a elección', 13.00, 40],
                ['Tequeños con Guacamole', '6 tequeños de queso con salsa guacamole', 18.00, 40],
                ['Chocolate Sublime', 'Barra de chocolate con leche y maní (30g)', 5.00, 120],
            ]],
        ];

        foreach ($catalogo as $orden => $datos) {
            $categoria = CategoriaProducto::updateOrCreate(['slug' => $datos['slug']], [
                'nombre' => $datos['nombre'],
                'icono' => $datos['icono'],
                'orden' => $orden,
                'activo' => true,
            ]);

            foreach ($datos['productos'] as [$nombre, $descripcion, $precio, $stock]) {
                $categoria->productos()->updateOrCreate(['nombre' => $nombre], [
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'precio_puntos' => (int) round($precio * 10),
                    'stock' => $stock,
                    'activo' => true,
                ]);
            }
        }
    }
}

