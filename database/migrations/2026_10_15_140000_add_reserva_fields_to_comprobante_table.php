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
        Schema::table('comprobante', function (Blueprint $table) {
            $table->string('codigo_reserva', 20)->nullable()->unique()->after('id');
            $table->string('sede')->default('Cine+ Alcázar - Miraflores')->after('cliente_email');
            $table->string('sala')->nullable()->after('sede');
            $table->string('pelicula_titulo')->nullable()->after('sala');
            $table->string('pelicula_imagen')->nullable()->after('pelicula_titulo');
            $table->dateTime('fecha_funcion')->nullable()->after('pelicula_imagen');
            $table->json('asientos')->nullable()->after('fecha_funcion');
            $table->integer('cantidad_entradas')->default(1)->after('asientos');
            $table->decimal('monto_entradas', 10, 2)->default(0)->after('cantidad_entradas');
            $table->decimal('monto_dulceria', 10, 2)->default(0)->after('monto_entradas');
            $table->decimal('monto_descuento', 10, 2)->default(0)->after('monto_dulceria');
            $table->string('descuento_concepto')->nullable()->after('monto_descuento');
            $table->string('metodo_pago_nombre')->nullable()->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comprobante', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_reserva',
                'sede',
                'sala',
                'pelicula_titulo',
                'pelicula_imagen',
                'fecha_funcion',
                'asientos',
                'cantidad_entradas',
                'monto_entradas',
                'monto_dulceria',
                'monto_descuento',
                'descuento_concepto',
                'metodo_pago_nombre',
            ]);
        });
    }
};

