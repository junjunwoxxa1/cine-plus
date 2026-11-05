<?php

use App\Livewire\Admin\Salas;
use App\Models\Funcion;
use App\Models\Pelicula;
use App\Models\User;
use Livewire\Livewire;

test('pelicula y funcion formatean la duracion en horas y minutos (ej. 1:39 hrs)', function () {
    $pelicula = new Pelicula([
        'titulo' => 'Test Movie',
        'duracion' => 99,
    ]);

    expect($pelicula->duracion_formateada)->toBe('1:39 hrs');

    $pelicula2 = new Pelicula([
        'titulo' => 'Corto Movie',
        'duracion' => 45,
    ]);
    expect($pelicula2->duracion_formateada)->toBe('0:45 hrs');

    expect(Funcion::minutosAHorasFormato(135))->toBe('2:15 hrs');
    expect(Funcion::recesoLimpiezaMinutos())->toBe(15);
});

test('administracion de salas valida el limite de entre 30 y 300 butacas totales', function () {
    $admin = User::first() ?? User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin);

    // Intentar crear sala con 2 filas x 10 columnas = 20 butacas (menor al mínimo 30)
    Livewire::test(Salas::class)
        ->set('numero_sala', 'Sala Test Insuficiente')
        ->set('filas', 2)
        ->set('asientos_por_fila', 10)
        ->call('guardar')
        ->assertHasErrors(['filas']);

    // Intentar crear sala con 20 filas x 20 columnas = 400 butacas (mayor al máximo 300)
    Livewire::test(Salas::class)
        ->set('numero_sala', 'Sala Test Excesiva')
        ->set('filas', 20)
        ->set('asientos_por_fila', 20)
        ->call('guardar')
        ->assertHasErrors(['filas']);

    // Intentar crear sala con 5 filas x 8 columnas = 40 butacas (válida en el rango 30-300)
    Livewire::test(Salas::class)
        ->set('numero_sala', 'Sala Test Valida')
        ->set('filas', 5)
        ->set('asientos_por_fila', 8)
        ->call('guardar')
        ->assertHasNoErrors(['filas']);
});

test('pasarela de pagos rechaza tarjeta de prueba con saldo insuficiente terminada en 0002', function () {
    $this->seed();

    $user = User::first();
    $funcion = Funcion::first();

    $this->actingAs($user);

    $response = $this->withSession([
        'reserva_temp' => [
            'funcion_id' => $funcion->id,
            'pelicula_id' => $funcion->pelicula_id,
            'cantidad' => 1,
            'asientos' => ['A1'],
            'total' => 25.00,
            'monto_dulceria' => 0,
        ],
    ])->post(route('pago.procesar'), [
        'nombre' => 'Test User',
        'email' => 'test@example.com',
        'metodo_pago' => 'tarjeta',
        'numero_tarjeta' => '4000 0000 0000 0002',
        'vencimiento' => '12/28',
        'cvc' => '123',
    ]);

    $response->assertSessionHasErrors(['numero_tarjeta']);
});
