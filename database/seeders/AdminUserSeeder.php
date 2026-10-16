<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::updateOrCreate(['slug' => Role::ADMIN], [
            'name' => 'Administrador',
            'description' => 'Acceso total al sistema y administración de recursos',
            'level' => 4,
        ]);

        Role::updateOrCreate(['slug' => Role::CLIENTE], [
            'name' => 'Cliente',
            'description' => 'Cliente que compra entradas y dulcería',
            'level' => 1,
        ]);

        $dulceria = Role::updateOrCreate(['slug' => Role::DULCERIA], [
            'name' => 'Cajero Dulcería / Consumos',
            'description' => 'Venta de comidas en mostrador y gestión de inventario',
            'level' => 2,
        ]);

        $user = User::updateOrCreate(['email' => 'admin@cineplus.com'], [
            'name' => 'Administrador',
            'password' => 'admin123',
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
            'role_id' => $admin->id,
        ])->save();

        $cajero = User::updateOrCreate(['email' => 'dulceria@cineplus.com'], [
            'name' => 'Cajero Dulcería',
            'password' => 'dulceria123',
        ]);

        $cajero->forceFill([
            'email_verified_at' => now(),
            'role_id' => $dulceria->id,
        ])->save();
    }
}