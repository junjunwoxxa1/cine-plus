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
        Schema::table('pelicula', function (Blueprint $table) {
            $table->boolean('en_banner')->default(false)->after('idioma');
            $table->integer('orden_banner')->default(0)->after('en_banner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelicula', function (Blueprint $table) {
            $table->dropColumn(['en_banner', 'orden_banner']);
        });
    }
};
