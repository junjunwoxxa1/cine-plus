<?php

namespace Database\Seeders;

use App\Models\MetodoPago;
use Illuminate\Database\Seeder;

class MetodoPagoSeeder extends Seeder
{
    /**
     * Métodos de pago iniciales.
     */
    public function run(): void
    {
        $metodos = [
            ['nombre' => 'Tarjeta', 'codigo' => 'tarjeta', 'requiere_tarjeta' => true, 'instrucciones' => 'Visa, Mastercard, AMEX o Diners.'],
            ['nombre' => 'Yape', 'codigo' => 'yape', 'requiere_tarjeta' => false, 'instrucciones' => 'Se generará un código QR para completar tu pago con Yape.'],
            ['nombre' => 'Plin', 'codigo' => 'plin', 'requiere_tarjeta' => false, 'instrucciones' => 'Se generará un código QR para completar tu pago con Plin.'],
            ['nombre' => 'Apple Pay', 'codigo' => 'apple_pay', 'requiere_tarjeta' => false, 'instrucciones' => 'Confirma el pago desde tu dispositivo Apple.'],
        ];

        foreach ($metodos as $orden => $metodo) {
            MetodoPago::updateOrCreate(['codigo' => $metodo['codigo']], [...$metodo, 'orden' => $orden, 'activo' => true]);
        }
    }
}

