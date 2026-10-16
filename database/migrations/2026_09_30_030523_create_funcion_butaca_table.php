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
        Schema::create('funcion_butaca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcion_id')->constrained('funcion')->onDelete('cascade');
            $table->foreignId('butaca_id')->constrained('butaca')->onDelete('cascade');
            $table->boolean('disponible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcion_butaca');
    }
};
