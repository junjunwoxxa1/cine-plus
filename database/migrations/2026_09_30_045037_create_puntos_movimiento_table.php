<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('puntos_movimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('comprobante_id')->nullable()->constrained('comprobante')->onDelete('set null');

            $table->enum('tipo', ['ganado', 'canjeado', 'expirado', 'ajuste']);
            $table->integer('puntos'); // Positivo si gana, negativo si canjea (ej. +50 o -200)
            $table->string('concepto'); // Ej: "Ganado por Compra de Boleta #001-123" o "Canje por Canchita Gigante"

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_movimiento');
    }
};
