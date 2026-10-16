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
        Schema::create('funcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelicula_id')->constrained('pelicula')->onDelete('cascade');
            $table->foreignId('sala_id')->constrained('sala')->onDelete('cascade');
            $table->dateTime('fecha_hora');
            $table->decimal('precio', 10, 2);
            $table->integer('precio_puntos')->default(0);
            $table->integer('asientos_disponibles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcion');
    }
};
