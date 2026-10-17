<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $clienteRole = Role::firstOrCreate(['slug' => Role::CLIENTE], [
            'name' => 'Cliente',
            'description' => 'Cliente que compra entradas y dulcería',
            'level' => 1,
        ]);

        $comprador = User::updateOrCreate(['email' => 'cliente@cineplus.com'], [
            'name' => 'Usuario Comprador',
            'password' => Hash::make('cliente123'),
        ]);

        $comprador->forceFill([
            'email_verified_at' => now(),
            'role_id' => $clienteRole->id,
            'saldo_puntos' => 250,
        ])->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where('email', 'cliente@cineplus.com')->delete();
    }
};
