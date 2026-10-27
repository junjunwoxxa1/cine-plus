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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('puntos_cine')->default(0)->after('saldo_puntos');
            $table->unsignedInteger('puntos_dulceria')->default(0)->after('puntos_cine');
        });

        // Inicializar usuarios existentes distribuyendo su saldo_puntos actual
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $saldo = (int) ($user->saldo_puntos ?? 0);
            if ($saldo > 0) {
                // Distribuir 60% a cine y 40% a dulcería
                $puntosCine = (int) round($saldo * 0.6);
                $puntosDulceria = $saldo - $puntosCine;

                DB::table('users')->where('id', $user->id)->update([
                    'puntos_cine' => $puntosCine,
                    'puntos_dulceria' => $puntosDulceria,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['puntos_cine', 'puntos_dulceria']);
        });
    }
};
