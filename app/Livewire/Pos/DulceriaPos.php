<?php

namespace App\Livewire\Pos;

use App\Models\CategoriaProducto;
use App\Models\Comprobante;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth.clear')]
#[Title('Punto de Venta - Dulcería & Consumos')]
class DulceriaPos extends Component
{
    public string $busqueda = '';

    public string $categoriaSeleccionada = 'todos';

    // Carrito de ventas POS
    // id => ['id', 'nombre', 'precio', 'cantidad', 'subtotal', 'stock_max']
    public array $carrito = [];

    // Datos del cobro y fidelización de puntos Club Cine+
    public string $clienteNombre = 'Cliente Mostrador';

    public string $clienteEmail = '';

    public string $clientePassword = '';

    public bool $esClienteNuevo = false;

    public ?string $mensajeSocio = null;

    public string $metodoPago = 'efectivo';

    public $montoRecibido = 0.0;

    // Venta completada para el modal/ticket
    public ?int $ventaExitosaId = null;

    public ?array $ticketVenta = null;

    public bool $modalTicketAbierto = false;

    public function updatedClienteEmail(string $val): void
    {
        $email = trim($val);
        $this->resetErrorBag(['clienteEmail', 'clienteNombre', 'clientePassword']);

        if (empty($email)) {
            $this->esClienteNuevo = false;
            $this->mensajeSocio = null;
            $this->clienteNombre = 'Cliente Mostrador';
            $this->clientePassword = '';

            return;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->esClienteNuevo = false;
            $this->mensajeSocio = null;

            return;
        }

        $usuario = User::where('email', $email)->first();

        if ($usuario) {
            $this->esClienteNuevo = false;
            $this->clienteNombre = $usuario->name;
            $this->clientePassword = '';
            $this->mensajeSocio = "Socio Club Cine+: {$usuario->name} (Saldo: {$usuario->saldo_puntos} pts)";
        } else {
            $this->esClienteNuevo = true;
            $this->mensajeSocio = 'Nuevo cliente / Invitado: Se creará su cuenta para acumular sus puntos.';
            if ($this->clienteNombre === 'Cliente Mostrador') {
                $this->clienteNombre = '';
            }
        }
    }

    public function agregarAlCarrito(int $productoId): void
    {
        $producto = Producto::findOrFail($productoId);

        if (! $producto->activo || $producto->stock <= 0) {
            return;
        }

        $cantidadActual = $this->carrito[$productoId]['cantidad'] ?? 0;

        if ($cantidadActual >= $producto->stock) {
            return;
        }

        $nuevaCantidad = $cantidadActual + 1;

        $this->carrito[$productoId] = [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'precio' => (float) $producto->precio,
            'cantidad' => $nuevaCantidad,
            'subtotal' => round($producto->precio * $nuevaCantidad, 2),
            'stock_max' => (int) $producto->stock,
        ];
    }

    public function modificarCantidad(int $productoId, int $cambio): void
    {
        if (! isset($this->carrito[$productoId])) {
            return;
        }

        $nueva = $this->carrito[$productoId]['cantidad'] + $cambio;
        $stockMax = $this->carrito[$productoId]['stock_max'];

        if ($nueva <= 0) {
            unset($this->carrito[$productoId]);
        } else {
            $nueva = min($stockMax, $nueva);
            $this->carrito[$productoId]['cantidad'] = $nueva;
            $this->carrito[$productoId]['subtotal'] = round($this->carrito[$productoId]['precio'] * $nueva, 2);
        }
    }

    public function updatedMontoRecibido($val): void
    {
        $this->resetErrorBag('montoRecibido');
        if (is_numeric($val) && (float) $val < 0) {
            $this->addError('montoRecibido', 'El monto recibido no puede ser un valor negativo.');
        }
    }

    public function eliminarDelCarrito(int $productoId): void
    {
        unset($this->carrito[$productoId]);
    }

    public function limpiarCarrito(): void
    {
        $this->carrito = [];
        $this->montoRecibido = 0.0;
        $this->clienteNombre = 'Cliente Mostrador';
        $this->clienteEmail = '';
        $this->clientePassword = '';
        $this->esClienteNuevo = false;
        $this->mensajeSocio = null;
        $this->resetErrorBag();
    }

    public function getSubtotalProperty(): float
    {
        return round(array_sum(array_column($this->carrito, 'subtotal')), 2);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal;
    }

    public function getVueltoProperty(): float
    {
        $recibido = is_numeric($this->montoRecibido) ? (float) $this->montoRecibido : 0.0;
        if ($this->metodoPago !== 'efectivo' || $recibido <= $this->total || $recibido < 0) {
            return 0.0;
        }

        return round($recibido - $this->total, 2);
    }

    public function registrarVenta(): void
    {
        if (empty($this->carrito)) {
            $this->addError('carrito', 'El carrito de venta está vacío.');

            return;
        }

        $totalFinal = $this->total;

        if ($totalFinal <= 0) {
            $this->addError('carrito', 'Error: El total de la venta debe ser mayor a S/ 0.00 y no puede ser negativo.');

            return;
        }

        $recibido = is_numeric($this->montoRecibido) ? (float) $this->montoRecibido : 0.0;

        if ($recibido < 0) {
            $this->addError('montoRecibido', 'El monto recibido no puede ser un valor negativo.');

            return;
        }

        if ($this->metodoPago === 'efectivo' && $recibido < $totalFinal) {
            $this->addError('montoRecibido', sprintf('Monto insuficiente: El efectivo recibido (S/ %.2f) es menor que el total de la venta (S/ %.2f).', $recibido, $totalFinal));

            return;
        }

        // Verificar stock de cada producto en BD antes de confirmar
        foreach ($this->carrito as $item) {
            $prod = Producto::find($item['id']);
            if (! $prod || $prod->stock < $item['cantidad']) {
                $this->addError('carrito', 'Stock insuficiente para '.($prod->nombre ?? 'el producto'));

                return;
            }
        }
        // Identificación del cliente y validación de nuevo registro para otorgar puntos
        $clienteUsuario = null;
        $emailLimpio = trim($this->clienteEmail);

        if (! empty($emailLimpio)) {
            if (! filter_var($emailLimpio, FILTER_VALIDATE_EMAIL)) {
                $this->addError('clienteEmail', 'Ingresa un correo electrónico válido.');

                return;
            }

            $usuarioExistente = User::where('email', $emailLimpio)->first();

            if ($usuarioExistente) {
                $clienteUsuario = $usuarioExistente;
            } else {
                // Nuevo cliente invitado: pide nombre y contraseña para registrar cuenta y acumular puntos
                $nombreLimpio = trim($this->clienteNombre);
                if (empty($nombreLimpio) || $nombreLimpio === 'Cliente Mostrador') {
                    $this->addError('clienteNombre', 'Ingresa el nombre del cliente para crear su cuenta Club Cine+.');

                    return;
                }

                if (empty($this->clientePassword) || mb_strlen($this->clientePassword) < 6) {
                    $this->addError('clientePassword', 'Ingresa una contraseña de al menos 6 caracteres para crear la cuenta de socio del cliente.');

                    return;
                }

                $rolCliente = Role::firstWhere('slug', Role::CLIENTE);

                $clienteUsuario = User::create([
                    'name' => $nombreLimpio,
                    'email' => $emailLimpio,
                    'password' => Hash::make($this->clientePassword),
                    'role_id' => $rolCliente?->id,
                    'saldo_puntos' => 0,
                    'puntos_cine' => 0,
                    'puntos_dulceria' => 0,
                    'email_verified_at' => now(),
                ]);
            }
        }

        $itemsSnapshot = $this->carrito;
        $cajeroNombre = auth()->user()?->name ?? 'Cajero Dulcería';
        $metodoSeleccionado = match ($this->metodoPago) {
            'efectivo' => 'Efectivo Mostrador',
            'tarjeta' => 'Tarjeta POS',
            'yape' => 'Yape QR',
            'plin' => 'Plin QR',
            default => 'Otro',
        };

        $comprobante = DB::transaction(function () use ($totalFinal, $itemsSnapshot, $metodoSeleccionado, $clienteUsuario, $emailLimpio) {
            $correlativo = (int) (Comprobante::max('id') ?? 0) + 1;
            $numeroTicket = str_pad((string) $correlativo, 7, '0', STR_PAD_LEFT);
            $codigoReserva = 'CP-POS-'.strtoupper(Str::random(4));

            $nuevoComprobante = Comprobante::create([
                'codigo_reserva' => $codigoReserva,
                'user_id' => $clienteUsuario ? $clienteUsuario->id : auth()->id(),
                'cliente_nombre' => $clienteUsuario ? $clienteUsuario->name : (trim($this->clienteNombre) ?: 'Cliente Mostrador'),
                'cliente_email' => $clienteUsuario ? $clienteUsuario->email : ($emailLimpio ?: null),
                'sede' => 'Cine+ Mostrador Dulcería',
                'sala' => 'Módulo Express Consumos',
                'pelicula_titulo' => 'Consumos Dulcería / Venta Directa',
                'asientos' => [],
                'cantidad_entradas' => 0,
                'monto_entradas' => 0,
                'monto_dulceria' => $totalFinal,
                'monto_descuento' => 0,
                'tipo' => 'boleta',
                'serie' => 'T001',
                'numero' => $numeroTicket,
                'subtotal' => round($totalFinal / 1.18, 2),
                'igv' => round($totalFinal - ($totalFinal / 1.18), 2),
                'total' => $totalFinal,
                'metodo_pago_nombre' => $metodoSeleccionado,
                'estado' => 'pagado',
            ]);

            // Registrar cada producto y descontar stock en SQLite
            foreach ($itemsSnapshot as $item) {
                $nuevoComprobante->detalles()->create([
                    'itemable_type' => 'App\Models\Producto',
                    'itemable_id' => $item['id'],
                    'descripcion' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['subtotal'],
                    'es_canje' => false,
                    'puntos_utilizados' => 0,
                ]);

                // Descontar inventario inmediatamente
                Producto::where('id', $item['id'])->decrement('stock', $item['cantidad']);
            }

            // Registrar pago
            DB::table('comprobante_pago')->insert([
                'comprobante_id' => $nuevoComprobante->id,
                'metodo' => $this->metodoPago === 'tarjeta' ? 'tarjeta' : 'efectivo',
                'monto' => $totalFinal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Otorgar puntos correspondientes al cliente por su compra
            if ($clienteUsuario && $totalFinal > 0) {
                if (Configuracion::puntosSeparadosActivo()) {
                    $clienteUsuario->sumarPuntosDulceria(5);
                } else {
                    $clienteUsuario->increment('saldo_puntos', 5);
                    $clienteUsuario->puntos_dulceria = (int) ($clienteUsuario->puntos_dulceria ?? 0) + 5;
                    $clienteUsuario->save();
                }
            }

            return $nuevoComprobante;
        });

        // Guardar datos para el modal de ticket impreso
        $this->ticketVenta = [
            'codigo_reserva' => $comprobante->codigo_reserva,
            'serie_numero' => $comprobante->serie_numero,
            'fecha' => now()->format('d/m/Y H:i:s'),
            'cliente' => $comprobante->cliente_nombre,
            'email' => $comprobante->cliente_email,
            'puntos_ganados' => $clienteUsuario ? 5 : 0,
            'saldo_puntos' => $clienteUsuario ? (int) $clienteUsuario->saldo_puntos : null,
            'cajero' => $cajeroNombre,
            'items' => $itemsSnapshot,
            'total' => $totalFinal,
            'metodo' => $metodoSeleccionado,
            'monto_recibido' => $recibido,
            'vuelto' => $this->vuelto,
        ];

        $this->ventaExitosaId = $comprobante->id;
        $this->modalTicketAbierto = true;

        // Limpiar carrito para siguiente venta
        $this->limpiarCarrito();
    }

    public function cerrarModalTicket(): void
    {
        $this->modalTicketAbierto = false;
        $this->ticketVenta = null;
    }

    public function render()
    {
        $categorias = CategoriaProducto::activas()->orderBy('orden')->get();

        $productosQuery = Producto::query()
            ->with('categoria');

        if ($this->categoriaSeleccionada !== 'todos') {
            $productosQuery->whereHas('categoria', fn ($q) => $q->where('slug', $this->categoriaSeleccionada));
        }

        if ($this->busqueda !== '') {
            $t = '%'.$this->busqueda.'%';
            $productosQuery->where(function ($q) use ($t) {
                $q->where('nombre', 'like', $t)
                    ->orWhere('descripcion', 'like', $t);
            });
        }

        $productos = $productosQuery->orderBy('nombre')->get();

        return view('livewire.pos.dulceria-pos', compact('categorias', 'productos'));
    }
}
