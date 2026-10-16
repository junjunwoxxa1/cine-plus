<?php

use App\Models\Sala;
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
        Schema::table('sala', function (Blueprint $table) {
            if (! Schema::hasColumn('sala', 'plantilla')) {
                $table->string('plantilla', 50)->default('clasica')->after('capacidad');
            }
            if (! Schema::hasColumn('sala', 'mapa_config')) {
                $table->json('mapa_config')->nullable()->after('plantilla');
            }
        });

        Schema::table('funcion', function (Blueprint $table) {
            if (! Schema::hasColumn('funcion', 'plantilla')) {
                $table->string('plantilla', 50)->nullable()->after('asientos_disponibles');
            }
            if (! Schema::hasColumn('funcion', 'mapa_config')) {
                $table->json('mapa_config')->nullable()->after('plantilla');
            }
        });

        // Inicializar plantillas variadas en las salas existentes
        $plantillasIniciales = ['escalera_col2', 'doble_escalera_extremos', 'escaleras_extremos_2filas'];
        $salas = Sala::with('butacas')->orderBy('id')->get();
        foreach ($salas as $idx => $sala) {
            $porFila = $sala->butacas->groupBy(fn ($b) => explode('-', $b->numero_butaca)[0] ?? 'A');
            $filas = max(1, $porFila->count() ?: 5);
            $col = max(1, $porFila->first()?->count() ?: 10);
            $plantilla = $plantillasIniciales[$idx % count($plantillasIniciales)];
            $cfg = Sala::generarConfiguracionPlantilla($plantilla, $filas, $col);

            $sala->update([
                'plantilla' => $plantilla,
                'mapa_config' => $cfg,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcion', function (Blueprint $table) {
            $table->dropColumn(['plantilla', 'mapa_config']);
        });

        Schema::table('sala', function (Blueprint $table) {
            $table->dropColumn(['plantilla', 'mapa_config']);
        });
    }
};

