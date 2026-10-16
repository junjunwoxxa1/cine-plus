<?php

use App\Models\Butaca;
use App\Models\Funcion;
use App\Models\Pelicula;
use App\Models\Producto;
use App\Models\Sala;

/**
 * Crea una función con una butaca disponible.
 *
 * @return array{funcion: Funcion, butaca: Butaca}
 */
function crearFuncionConButaca(): array
{
    $pelicula = Pelicula::create(['titulo' => 'Dune', 'duracion' => 120]);
    $sala = Sala::create(['numero_sala' => 'Sala 1', 'capacidad' => 1]);
    $butaca = Butaca::create(['numero_butaca' => 'A-1', 'sala_id' => $sala->id]);

    $funcion = Funcion::create([
        'pelicula_id' => $pelicula->id,
        'sala_id' => $sala->id,
        'fecha_hora' => now()->addDay(),
        'precio' => 25,
        'precio_puntos' => 250,
        'asientos_disponibles' => 1,
    ]);
    $funcion->butacas()->attach($butaca->id, ['disponible' => true]);

    return ['funcion' => $funcion, 'butaca' => $butaca];
}

/**
 * @return array{funcion_id: int, butacas_ids: array<int, int>, butacas_codigos: array<int, string>, cantidad: int, monto_total: float}
 */
function reservaTemporal(Funcion $funcion, Butaca $butaca): array
{
    return [
        'funcion_id' => $funcion->id,
        'butacas_ids' => [$butaca->id],
        'butacas_codigos' => ['A1'],
        'cantidad' => 1,
        'monto_total' => 25.0,
    ];
}

test('reserving seats redirects to the dulceria page', function () {
    ['funcion' => $funcion] = crearFuncionConButaca();

    $response = $this->post(route('reservas.store'), [
        'funcion_id' => $funcion->id,
        'cantidad_entradas' => 1,
        'butacas' => ['A1'],
    ]);

    $response->assertRedirect(route('reservas.dulceria'));
});

test('dulceria page shows products grouped by category', function () {
    ['funcion' => $funcion, 'butaca' => $butaca] = crearFuncionConButaca();
    Producto::create(['nombre' => 'Combo Dúo', 'categoria' => 'combos', 'precio' => 35, 'stock' => 5]);
    Producto::create(['nombre' => 'Agotado', 'categoria' => 'snacks', 'precio' => 5, 'stock' => 0]);

    $response = $this->withSession(['reserva_temp' => reservaTemporal($funcion, $butaca)])
        ->get(route('reservas.dulceria'));

    $response->assertOk()
        ->assertSee('Combo Dúo')
        ->assertDontSee('Agotado');
});

test('dulceria page redirects home without an active reservation', function () {
    $this->get(route('reservas.dulceria'))->assertRedirect(route('home'));
});

test('selected products are stored in the session with their subtotal', function () {
    ['funcion' => $funcion, 'butaca' => $butaca] = crearFuncionConButaca();
    $combo = Producto::create(['nombre' => 'Combo Dúo', 'categoria' => 'combos', 'precio' => 35, 'stock' => 5]);
    $agua = Producto::create(['nombre' => 'Agua', 'categoria' => 'bebidas', 'precio' => 6, 'stock' => 5]);

    $response = $this->withSession(['reserva_temp' => reservaTemporal($funcion, $butaca)])
        ->post(route('reservas.dulceria.store'), [
            'productos' => [$combo->id => 2, $agua->id => 0],
        ]);

    $response->assertRedirect(route('pago.index'));
    expect(session('reserva_temp.monto_dulceria'))->toEqual(70)
        ->and(session('reserva_temp.productos'))->toHaveKey($combo->id)
        ->and(session('reserva_temp.productos'))->not->toHaveKey($agua->id);
});

test('skipping dulceria continues to payment with no products', function () {
    ['funcion' => $funcion, 'butaca' => $butaca] = crearFuncionConButaca();

    $response = $this->withSession(['reserva_temp' => reservaTemporal($funcion, $butaca)])
        ->post(route('reservas.dulceria.store'));

    $response->assertRedirect(route('pago.index'));
    expect(session('reserva_temp.productos'))->toBe([])
        ->and(session('reserva_temp.monto_dulceria'))->toEqual(0);
});

test('payment page lists the products chosen in the dulceria', function () {
    ['funcion' => $funcion, 'butaca' => $butaca] = crearFuncionConButaca();
    $reserva = reservaTemporal($funcion, $butaca) + [
        'productos' => [1 => ['nombre' => 'Combo Dúo', 'cantidad' => 2, 'precio_unitario' => 35.0, 'subtotal' => 70.0]],
        'monto_dulceria' => 70.0,
    ];

    $response = $this->withSession(['reserva_temp' => $reserva])->get(route('pago.index'));

    $response->assertOk()->assertSee('2x Combo Dúo');
});
