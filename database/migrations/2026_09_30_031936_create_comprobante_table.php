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
        Schema::create('comprobante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('cliente_nombre');
            $table->string('cliente_email')->nullable();
            $table->string('tipo')->default('boleta'); // boleta, factura
            $table->string('serie', 10);
            $table->string('numero', 10);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('igv', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['pendiente', 'pagado', 'anulado'])->default('pagado');
            $table->timestamps();
        });

        Schema::create('comprobante_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobante')->onDelete('cascade');

            // CAMPOS POLIMÓRFICOS
            // itemable_type: 'App\Models\Producto' o 'App\Models\FuncionButaca'
            // itemable_id: ID correspondiente en su respectiva tabla
            $table->morphs('itemable');

            $table->string('descripcion'); // Snapshot: ej. "Canchita Gigante" o "Avatar 3 - Sala 2 (Asiento B4)"
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->boolean('es_canje')->default(false); // Indica si este ítem fue canjeado con puntos
            $table->integer('puntos_utilizados')->default(0); // Cantidad de puntos usados en este ítem
            $table->timestamps();
        });

        Schema::create('comprobante_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobante')->onDelete('cascade');
            $table->enum('metodo', ['efectivo', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->decimal('monto', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_detalle');
        Schema::dropIfExists('comprobante');
        Schema::dropIfExists('comprobante_pago');
    }
};
