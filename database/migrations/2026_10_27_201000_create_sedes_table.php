<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('ciudad')->default('Lima');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar las sedes actuales para poblar la tabla sin alterar nada existente
        $sedesIniciales = [
            [
                'nombre' => 'Cine+ Alcázar (Miraflores)',
                'ciudad' => 'Lima',
                'direccion' => 'Av. Santa Cruz 814, Óvalo Gutiérrez, Miraflores',
                'telefono' => '(01) 620-3001',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cine+ Mall del Sur (SJM)',
                'ciudad' => 'Lima',
                'direccion' => 'Av. Los Lirios con Pedro Miotta, San Juan de Miraflores',
                'telefono' => '(01) 620-3002',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cine+ San Miguel',
                'ciudad' => 'Lima',
                'direccion' => 'Av. La Marina 2000, Plaza San Miguel',
                'telefono' => '(01) 620-3003',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('sedes')->insert($sedesIniciales);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedes');
    }
};

