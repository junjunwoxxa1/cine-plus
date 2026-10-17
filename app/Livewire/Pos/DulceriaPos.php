<?php

namespace App\Livewire\Pos;

use App\Models\CategoriaProducto;
use App\Models\Comprobante;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
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

    // Datos del cobro
    public string $clienteNombre = 'Cliente Mostrador';
    public string $clienteEmail = '';
    public string $metodoPago = 'efectivo';
    public float $montoRecibido = 0.0;

    // Venta completada para el modal/ticket
    public ?int $ventaExitosaId = null;
    public ?array $ticketVenta = null;
    public bool $modalTicketAbierto = false;

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

    public function eliminarDelCarrito(int $productoId): void
    {
        unset($this->carrito[$productoId]);
    }

    public function limpiarCarrito(): void
    {
        $this->carrito = [];
        $this->montoRecibido = 0;
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
        if ($this->metodoPago !== 'efectivo' || $this->montoRecibido <= $this->total) {
            return 0.0;
        }

        return round($this->montoRecibido - $this->total, 2);
    }

    public function registrarVenta(): void
    {
        if (empty($this->carrito)) {
            return;
        }

        // Verificar stock de cada producto en BD antes de confirmar
        foreach ($this->carrito as $item) {
            $prod = Producto::find($item['id']);
            if (! $prod || $prod->stock < $item['cantidad']) {
                $this->addError('carrito', 'Stock insuficiente para ' . ($prod->nombre ?? 'el producto'));
                return;
            }
        }

        $totalFinal = $this->total;
        $itemsSnapshot = $this->carrito;
        $cajeroNombre = auth()->user()?->name ?? 'Cajero Dulcería';
        $metodoSeleccionado = match ($this->metodoPago) {
            'efectivo' => 'Efectivo Mostrador',
            'tarjeta' => 'Tarjeta POS',
            'yape' => 'Yape QR',
            'plin' => 'Plin QR',
            default => 'Otro',
        };

        $comprobante = DB::transaction(function () use ($totalFinal, $itemsSnapshot, $metodoSeleccionado) {
            $correlativo = (int) (Comprobante::max('id') ?? 0) + 1;
            $numeroTicket = str_pad((string) $correlativo, 7, '0', STR_PAD_LEFT);
            $codigoReserva = 'CP-POS-' . strtoupper(Str::random(4));

            $nuevoComprobante = Comprobante::create([
                'codigo_reserva' => $codigoReserva,
                'user_id' => auth()->id(),
                'cliente_nombre' => trim($this->clienteNombre) ?: 'Cliente Mostrador',
                'cliente_email' => $this->clienteEmail ?: null,
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

            return $nuevoComprobante;
        });

        // Guardar datos para el modal de ticket impreso
        $this->ticketVenta = [
            'codigo_reserva' => $comprobante->codigo_reserva,
            'serie_numero' => $comprobante->serie_numero,
            'fecha' => now()->format('d/m/Y H:i:s'),
            'cliente' => $comprobante->cliente_nombre,
            'cajero' => $cajeroNombre,
            'items' => $itemsSnapshot,
            'total' => $totalFinal,
            'metodo' => $metodoSeleccionado,
            'monto_recibido' => $this->montoRecibido,
            'vuelto' => $this->vuelto,
        ];

        $this->ventaExitosaId = $comprobante->id;
        $this->modalTicketAbierto = true;

        // Limpiar carrito para siguiente venta
        $this->limpiarCarrito();
        $this->clienteNombre = 'Cliente Mostrador';
        $this->clienteEmail = '';
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
            $t = '%' . $this->busqueda . '%';
            $productosQuery->where(function ($q) use ($t) {
                $q->where('nombre', 'like', $t)
                    ->orWhere('descripcion', 'like', $t);
            });
        }

        $productos = $productosQuery->orderBy('nombre')->get();

        return view('livewire.pos.dulceria-pos', compact('categorias', 'productos'));
    }
}

