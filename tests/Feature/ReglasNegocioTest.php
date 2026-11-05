<?php

use App\Livewire\Admin\Funciones;
use App\Livewire\Admin\Salas;
use App\Livewire\Pos\DulceriaPos;
use App\Models\Funcion;
use App\Models\Pelicula;
use App\Models\Producto;
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

test('componente de salas tolera valores vacios o texto durante la edicion reactiva sin error 500', function () {
    $admin = User::first() ?? User::factory()->create(['rol' => 'administrador']);
    $this->actingAs($admin);

    Livewire::test(Salas::class)
        ->set('filas', '')
        ->set('asientos_por_fila', '')
        ->assertSet('filas', '')
        ->assertSet('asientos_por_fila', '');
});

test('programador de funciones detecta colisiones y mantiene el modal abierto con alerta explicita', function () {
    $this->seed();

    $admin = User::first() ?? User::factory()->create(['rol' => 'administrador']);
    $this->actingAs($admin);

    $funcion = Funcion::first();
    expect($funcion)->not->toBeNull();

    // Intentar crear otra función en la misma sala, mismo día y misma hora exacta
    Livewire::test(Funciones::class)
        ->call('crear', $funcion->sala_id, $funcion->pelicula_id)
        ->set('fecha_hora', $funcion->fecha_hora->format('Y-m-d\TH:i'))
        ->call('guardar')
        ->assertSet('modalAbierto', true)
        ->assertHasErrors(['fecha_hora']);
});

test('sugerencias de horarios para el mismo dia no incluyen el horario principal ni los ya seleccionados', function () {
    $this->seed();

    $admin = User::first() ?? User::factory()->create(['rol' => 'administrador']);
    $this->actingAs($admin);

    $funcion = Funcion::first();
    expect($funcion)->not->toBeNull();

    $component = Livewire::test(Funciones::class)
        ->call('crear', $funcion->sala_id, $funcion->pelicula_id)
        ->set('fecha_hora', $funcion->fecha_hora->format('Y-m-d\TH:i'));

    $horaPrincipal = $funcion->fecha_hora->format('H:i');
    $sugeridos = $component->instance()->calcularHorariosSugeridosParaMismoDia();

    expect($sugeridos)->not->toContain($horaPrincipal);

    if (! empty($sugeridos)) {
        $primeraSugerida = $sugeridos[0];
        $component->call('agregarHorarioExtra', $primeraSugerida);
        $sugeridosDespues = $component->instance()->calcularHorariosSugeridosParaMismoDia();
        expect($sugeridosDespues)->not->toContain($primeraSugerida);
    }
});

test('pos dulceria tolera montos negativos y valida limpiamente sin error 500', function () {
    $this->seed();

    $user = User::first() ?? User::factory()->create(['rol' => 'administrador']);
    $this->actingAs($user);

    $producto = Producto::where('activo', true)->where('stock', '>', 5)->first();
    expect($producto)->not->toBeNull();

    Livewire::test(DulceriaPos::class)
        ->call('agregarAlCarrito', $producto->id)
        ->set('metodoPago', 'efectivo')
        ->set('montoRecibido', '-96')
        ->assertHasErrors(['montoRecibido'])
        ->call('registrarVenta')
        ->assertHasErrors(['montoRecibido'])
        ->assertSet('modalTicketAbierto', false);
});

test('flujo de compra de entradas recuerda los productos de dulceria al retroceder a butacas', function () {
    $this->seed();

    $user = User::first();
    $this->actingAs($user);

    $funcion = Funcion::with('sala')->first();
    $producto = Producto::where('activo', true)->where('stock', '>', 5)->first();

    // 1. Establecer reserva temporal inicial (butaca seleccionada)
    session([
        'reserva_temp' => [
            'funcion_id' => $funcion->id,
            'butacas_ids' => [],
            'butacas_codigos' => ['A1'],
            'cantidad' => 1,
            'monto_total' => 25.00,
            'productos' => [],
            'monto_dulceria' => 0,
        ],
    ]);

    // 2. Sincronizar selección de dulcería
    $response = $this->postJson(route('reservas.dulceria.sync'), [
        'productos' => [
            $producto->id => 2,
        ],
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'total_items' => 2,
    ]);

    // Verificar que la sesión guardó los productos
    $reserva = session('reserva_temp');
    expect($reserva['productos'])->toHaveKey($producto->id);
    expect($reserva['productos'][$producto->id]['cantidad'])->toBe(2);

    // 3. Volver a la pantalla de selección de butacas: NO debe mostrar el bloque de dulcería guardada
    $responseButacas = $this->get(route('reservas.butacas', $funcion));
    $responseButacas->assertOk();
    $responseButacas->assertDontSee('DULCERÍA GUARDADA');
    $responseButacas->assertDontSee('Tus snacks se mantendrán listos al continuar');

    // La sesión debe seguir conservando los productos de dulcería internamente
    $reservaPersistida = session('reserva_temp');
    expect($reservaPersistida['productos'])->toHaveKey($producto->id);

    // 4. Al continuar al paso de dulcería, los productos seleccionados sí deben estar presentes
    $responseDulceria = $this->get(route('reservas.dulceria'));
    $responseDulceria->assertOk();
    $responseDulceria->assertSee($producto->nombre);
});
