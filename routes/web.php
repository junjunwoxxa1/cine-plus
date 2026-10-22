<?php

use App\Http\Controllers\CompraController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PeliculaController;
use App\Http\Controllers\ReservaController;
use App\Models\Pelicula;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $peliculas = Pelicula::all();
    $peliculasBanner = Pelicula::enBanner()
        ->with(['funciones' => fn ($q) => $q->where('fecha_hora', '>=', now()->subHours(1))->orderBy('fecha_hora')])
        ->get();

    // Si aún no se ha seleccionado ninguna película para el banner, tomar las 4 más recientes por defecto
    if ($peliculasBanner->isEmpty()) {
        $peliculasBanner = Pelicula::with(['funciones' => fn ($q) => $q->where('fecha_hora', '>=', now()->subHours(1))->orderBy('fecha_hora')])
            ->latest('id')
            ->take(4)
            ->get();
    }

    $combosDestacados = \App\Models\Producto::where('categoria_producto_id', 1)
        ->where('activo', true)
        ->take(4)
        ->get();

    return view('welcome', compact('peliculas', 'peliculasBanner', 'combosDestacados'));
})->name('home');

Route::get('/peliculas/{pelicula:slug}', [PeliculaController::class, 'show'])->name('peliculas.show');
Route::get('/funciones/{funcion}/butacas', [ReservaController::class, 'selectButacas'])->name('reservas.butacas');
Route::post('/reservas/procesar', [ReservaController::class, 'store'])->name('reservas.store');
Route::get('/reservas/dulceria', [ReservaController::class, 'selectDulceria'])->name('reservas.dulceria');
Route::post('/reservas/dulceria', [ReservaController::class, 'storeDulceria'])->name('reservas.dulceria.store');
Route::get('/reservas/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');

// Compra Online Directa de Dulcería y Snacks (Sin Entrada)
Route::get('/dulceria', [\App\Http\Controllers\DulceriaOnlineController::class, 'index'])->name('dulceria.index');
Route::post('/dulceria/checkout', [\App\Http\Controllers\DulceriaOnlineController::class, 'checkout'])->name('dulceria.checkout');
Route::get('/dulceria/pago', [\App\Http\Controllers\DulceriaOnlineController::class, 'pago'])->name('dulceria.pago');
Route::post('/dulceria/pago/procesar', [\App\Http\Controllers\DulceriaOnlineController::class, 'procesarPago'])->name('dulceria.pago.procesar');
Route::get('/pago', [PagoController::class, 'index'])->name('pago.index');
Route::post('/pago/procesar', [PagoController::class, 'procesar'])->name('pago.procesar');
Route::get('/pago/comprobante/{codigo}', [PagoController::class, 'comprobante'])->name('pago.comprobante');
Route::get('/pago/comprobante/{codigo}/pdf', [PagoController::class, 'descargarPdf'])->name('pago.pdf');

// Mis Compras (Entradas y Comprobantes de Clientes)
Route::get('/mis-compras', [CompraController::class, 'index'])->name('compras.index');
Route::post('/mis-compras/buscar', [CompraController::class, 'buscar'])->name('compras.buscar');

// Recuperación de Cuenta y Llave de Acceso sin Contraseña por Token de 6 Dígitos
Route::middleware('guest')->group(function () {
    Route::post('/login/codigo/enviar', [\App\Http\Controllers\Auth\PasswordResetTokenController::class, 'sendLoginCode'])->name('login.send-code');
    Route::post('/login/codigo/verificar', [\App\Http\Controllers\Auth\PasswordResetTokenController::class, 'verifyLoginCode'])->name('login.verify-code');
    Route::get('/login/codigo/cambiar-correo', [\App\Http\Controllers\Auth\PasswordResetTokenController::class, 'resetLoginCodeStep'])->name('login.reset-code-step');

    Route::get('/recuperar-cuenta', [\App\Http\Controllers\Auth\PasswordResetTokenController::class, 'showRequestForm'])->name('password.forgot-token');
    Route::post('/recuperar-cuenta/enviar', [\App\Http\Controllers\Auth\PasswordResetTokenController::class, 'sendToken'])->name('password.send-token');
    Route::get('/recuperar-cuenta/verificar', [\App\Http\Controllers\Auth\PasswordResetTokenController::class, 'showVerifyForm'])->name('password.verify-token');
    Route::post('/recuperar-cuenta/verificar', [\App\Http\Controllers\Auth\PasswordResetTokenController::class, 'resetWithToken'])->name('password.reset-token');

    Route::post('/correo/configurar-smtp', [\App\Http\Controllers\Auth\PasswordResetTokenController::class, 'saveSmtpConfig'])->name('mail.save-smtp');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()?->isDulceria()) {
            return redirect()->route('pos.dulceria');
        }

        return redirect()->route('compras.index');
    })->name('dashboard');
});

// Punto de Venta (POS) para Cajeros de Dulcería y Administradores
Route::get('/pos-dulceria', \App\Livewire\Pos\DulceriaPos::class)
    ->middleware(['auth', 'can:access-pos'])
    ->name('pos.dulceria');

// Panel de Administración (protegido por autenticación y Gate de Admin)
Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/categorias', \App\Livewire\Admin\Categorias::class)->name('categorias');
    Route::get('/productos', \App\Livewire\Admin\Productos::class)->name('productos');
    Route::get('/inventario', \App\Livewire\Admin\Inventario::class)->name('inventario');
    Route::get('/metodos-pago', \App\Livewire\Admin\MetodosPago::class)->name('metodos-pago');
    Route::get('/peliculas', \App\Livewire\Admin\Peliculas::class)->name('peliculas');
    Route::get('/funciones', \App\Livewire\Admin\Funciones::class)->name('funciones');
    Route::get('/salas', \App\Livewire\Admin\Salas::class)->name('salas');
    Route::get('/descuentos', \App\Livewire\Admin\Descuentos::class)->name('descuentos');
    Route::get('/usuarios', \App\Livewire\Admin\Usuarios::class)->name('usuarios');
});

require __DIR__.'/settings.php';
