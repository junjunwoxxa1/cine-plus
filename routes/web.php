<?php

use App\Http\Controllers\Auth\PasswordResetTokenController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DulceriaOnlineController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PeliculaController;
use App\Http\Controllers\ReservaController;
use App\Livewire\Admin\Categorias;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Descuentos;
use App\Livewire\Admin\Funciones;
use App\Livewire\Admin\Inventario;
use App\Livewire\Admin\MetodosPago;
use App\Livewire\Admin\Peliculas;
use App\Livewire\Admin\Productos;
use App\Livewire\Admin\Salas;
use App\Livewire\Admin\Sedes;
use App\Livewire\Admin\Usuarios;
use App\Livewire\Pos\DulceriaPos;
use App\Models\Pelicula;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $peliculas = Pelicula::all();
    $peliculasBanner = Pelicula::enBanner()
        ->with(['funciones' => fn ($q) => $q->where('fecha_hora', '>=', now()->subHours(1))->orderBy('fecha_hora')])
        ->get();

    // Si aún no se ha seleccionado ninguna película para el banner, tomar solo 1 por defecto
    if ($peliculasBanner->isEmpty()) {
        $peliculasBanner = Pelicula::with(['funciones' => fn ($q) => $q->where('fecha_hora', '>=', now()->subHours(1))->orderBy('fecha_hora')])
            ->latest('id')
            ->take(1)
            ->get();
    }

    $combosDestacados = Producto::where('categoria_producto_id', 1)
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
Route::post('/reservas/dulceria/sync', [ReservaController::class, 'syncDulceria'])->name('reservas.dulceria.sync');
Route::get('/reservas/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');

// Compra Online Directa de Dulcería y Snacks (Sin Entrada)
Route::get('/dulceria', [DulceriaOnlineController::class, 'index'])->name('dulceria.index');
Route::post('/dulceria/checkout', [DulceriaOnlineController::class, 'checkout'])->name('dulceria.checkout');
Route::get('/dulceria/pago', [DulceriaOnlineController::class, 'pago'])->name('dulceria.pago');
Route::post('/dulceria/pago/procesar', [DulceriaOnlineController::class, 'procesarPago'])->name('dulceria.pago.procesar');
Route::get('/pago', [PagoController::class, 'index'])->name('pago.index');
Route::post('/pago/procesar', [PagoController::class, 'procesar'])->name('pago.procesar');
Route::get('/pago/comprobante/{codigo}', [PagoController::class, 'comprobante'])->name('pago.comprobante');
Route::get('/pago/comprobante/{codigo}/pdf', [PagoController::class, 'descargarPdf'])->name('pago.pdf');

// Mis Compras (Entradas y Comprobantes de Clientes)
Route::get('/mis-compras', [CompraController::class, 'index'])->name('compras.index');
Route::post('/mis-compras/buscar', [CompraController::class, 'buscar'])->name('compras.buscar');
Route::post('/mis-compras/cancelar', [CompraController::class, 'cancelar'])->name('compras.cancelar');

// Recuperación de Cuenta y Llave de Acceso sin Contraseña por Token de 6 Dígitos
Route::middleware('guest')->group(function () {
    Route::post('/login/codigo/enviar', [PasswordResetTokenController::class, 'sendLoginCode'])->name('login.send-code');
    Route::post('/login/codigo/verificar', [PasswordResetTokenController::class, 'verifyLoginCode'])->name('login.verify-code');
    Route::get('/login/codigo/cambiar-correo', [PasswordResetTokenController::class, 'resetLoginCodeStep'])->name('login.reset-code-step');

    Route::get('/recuperar-cuenta', [PasswordResetTokenController::class, 'showRequestForm'])->name('password.forgot-token');
    Route::post('/recuperar-cuenta/enviar', [PasswordResetTokenController::class, 'sendToken'])->name('password.send-token');
    Route::get('/recuperar-cuenta/verificar', [PasswordResetTokenController::class, 'showVerifyForm'])->name('password.verify-token');
    Route::post('/recuperar-cuenta/verificar', [PasswordResetTokenController::class, 'resetWithToken'])->name('password.reset-token');

    Route::post('/correo/configurar-smtp', [PasswordResetTokenController::class, 'saveSmtpConfig'])->name('mail.save-smtp');
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
Route::get('/pos-dulceria', DulceriaPos::class)
    ->middleware(['auth', 'can:access-pos'])
    ->name('pos.dulceria');

// Panel de Administración (protegido por autenticación y Gate de Admin)
Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/categorias', Categorias::class)->name('categorias');
    Route::get('/productos', Productos::class)->name('productos');
    Route::get('/inventario', Inventario::class)->name('inventario');
    Route::get('/metodos-pago', MetodosPago::class)->name('metodos-pago');
    Route::get('/peliculas', Peliculas::class)->name('peliculas');
    Route::get('/sedes', Sedes::class)->name('sedes');
    Route::get('/funciones', Funciones::class)->name('funciones');
    Route::get('/salas', Salas::class)->name('salas');
    Route::get('/descuentos', Descuentos::class)->name('descuentos');
    Route::get('/usuarios', Usuarios::class)->name('usuarios');
});

require __DIR__.'/settings.php';
