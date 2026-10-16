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
        Schema::table('producto', function (Blueprint $table) {
            $table->foreignId('categoria_producto_id')
                ->nullable()
                ->after('id')
                ->constrained('categoria_producto')
                ->restrictOnDelete();
            $table->string('imagen')->nullable()->after('descripcion');
            $table->boolean('activo')->default(true)->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_producto_id');
            $table->dropColumn(['imagen', 'activo']);
        });
    }
};
