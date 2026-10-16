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
        Schema::create('pelicula', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->string('imagen')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('director')->nullable();
            $table->string('genero')->nullable();
            $table->date('fecha_estreno')->nullable();
            $table->integer('duracion')->nullable(); // Duración en minutos
            $table->string('clasificacion')->nullable(); // Clasificación de la película
            $table->string('idioma')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelicula');
    }
};
