<?php

namespace App\Http\Controllers;

use App\Mail\ComprobanteCompraMail;
use App\Models\CategoriaProducto;
use App\Models\Comprobante;
use App\Models\Descuento;
use App\Models\MetodoPago;
use App\Models\Producto;
use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DulceriaOnlineController extends Controller
{
    /**
     * Catálogo completo de dulcería para compra online directa sin entradas.
     */
    public function index()
    {
        $categorias = CategoriaProducto::with(['productos' => function ($q) {
            $q->orderBy('stock', 'desc')->orderBy('nombre');
        }])->activas()->get();

        $productos = $categorias->flatMap->productos;

        $sedes = Sala::query()
            ->select('sede')
            ->distinct()
            ->whereNotNull('sede')
            ->where('sede', '!=', '')
            ->orderBy('sede')
            ->pluck('sede');

        if ($sedes->isEmpty()) {
            $sedes = collect(['Cine+ Alcázar (Miraflores)', 'Cine+ Mall del Sur (SJM)', 'Cine+ San Miguel']);
        }

        $carritoDulceria = session('dulceria_solo_carrito', []);

        return view('dulceria.index', compact('categorias', 'productos', 'sedes', 'carritoDulceria'));
    }

    /**
     * Guarda el carrito de solo dulcería en sesión y redirige al checkout/pago de comida.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'sede' => 'required|string',
            'productos' => 'required|array',
            'productos.*' => 'integer|min:0',
        ], [
            'sede.required' => 'Por favor selecciona la sede donde recogerás tus alimentos.',
            'productos.required' => 'Debes agregar al menos un combo o snack.',
        ]);

        $cantidades = array_filter($validated['productos'], fn ($qty) => $qty > 0);

        if (empty($cantidades)) {
            return back()->withErrors(['productos' => 'Debes seleccionar al menos un producto para continuar.']);
        }

        $productos = Producto::whereIn('id', array_keys($cantidades))->get()->keyBy('id');
        $items = [];
        $totalDulceria = 0;

        foreach ($cantidades as $id => $cantidad) {
            if ($productos->has($id)) {
                $prod = $productos[$id];
                // Omitir si fue desactivado por el admin o no tiene existencias
                if (! $prod->activo || $prod->stock <= 0) {
                    continue;
                }
                $cantidadReal = min((int) $prod->stock, (int) $cantidad);
                $subtotal = round($cantidadReal * (float) $prod->precio, 2);

                $items[$id] = [
                    'id' => $prod->id,
                    'nombre' => $prod->nombre,
                    'precio' => (float) $prod->precio,
                    'cantidad' => $cantidadReal,
                    'subtotal' => $subtotal,
                ];
                $totalDulceria += $subtotal;
            }
        }

        if (empty($items)) {
            return back()->withErrors(['productos' => 'Los productos seleccionados no tienen stock disponible en este momento.']);
        }

        session([
            'pedido_dulceria_solo' => [
                'sede' => $validated['sede'],
                'items' => $items,
                'total' => $totalDulceria,
                'created_at' => now()->timestamp,
            ],
        ]);

        return redirect()->route('dulceria.pago');
    }

    /**
     * Pantalla de pago exclusiva para compra de comida / dulcería online.
     */
    public function pago()
    {
        $pedido = session('pedido_dulceria_solo');

        if (! $pedido || empty($pedido['items'])) {
            return redirect()->route('dulceria.index')->withErrors(['error' => 'No tienes un pedido activo de dulcería.']);
        }

        $user = auth()->user();
        $cliente = [
            'nombre' => $user ? $user->name : '',
            'email' => $user ? $user->email : '',
            'puntos' => $user ? (int) $user->saldo_puntos : 0,
        ];

        $metodosPago = MetodoPago::activos()->get();
        $descuentos = Descuento::vigentes()->get();

        return view('dulceria.pago', compact('pedido', 'cliente', 'metodosPago', 'descuentos'));
    }

    /**
     * Procesa la compra de comida sin entradas, genera el comprobante y descuenta el stock en BD.
     */
    public function procesarPago(Request $request)
    {
        $pedido = session('pedido_dulceria_solo');

        if (! $pedido || empty($pedido['items'])) {
            return redirect()->route('dulceria.index')->withErrors(['error' => 'La sesión del pedido ha expirado.']);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'metodo_pago' => 'required|string|exists:metodo_pago,codigo',
            'numero_tarjeta' => 'nullable|string',
            'vencimiento' => 'nullable|string',
            'cvc' => 'nullable|string',
            'descuento_id' => 'nullable|exists:descuento,id',
            'codigo_promo' => 'nullable|string',
        ]);

        $user = auth()->user();
        $metodo = MetodoPago::where('codigo', $validated['metodo_pago'])->first();

        $subtotalComida = (float) $pedido['total'];
        $montoDescuento = 0.0;
        $descuentoConcepto = null;

        if ($request->filled('descuento_id')) {
            $descuento = Descuento::vigentes()->find($request->descuento_id);
            if ($descuento) {
                $montoDescuento = (float) $descuento->calcular($subtotalComida, count($pedido['items']));
                $descuentoConcepto = $descuento->codigo
                    ? 'Cupón '.$descuento->codigo.' ('.$descuento->nombre.')'
                    : $descuento->nombre;
            }
        } elseif ($request->filled('codigo_promo')) {
            $codigoLimpio = strtoupper(trim((string) $request->codigo_promo));
            $descuento = Descuento::vigentes()->where('codigo', $codigoLimpio)->first();
            if ($descuento) {
                $montoDescuento = (float) $descuento->calcular($subtotalComida, count($pedido['items']));
                $descuentoConcepto = 'Cupón Promocional: '.$codigoLimpio.' ('.$descuento->nombre.')';
            }
        }

        $montoDescuento = round(min(max($montoDescuento, 0), $subtotalComida), 2);
        $totalFinal = max(0, round($subtotalComida - $montoDescuento, 2));

        // Validar campos de tarjeta solo si el método lo exige y el monto final es mayor a 0
        if ($totalFinal > 0 && $metodo && $metodo->requiere_tarjeta) {
            $request->validate([
                'numero_tarjeta' => 'required|string|min:12|max:22',
                'vencimiento' => 'required|string|min:4|max:7',
                'cvc' => 'required|string|min:3|max:4',
            ], [
                'numero_tarjeta.required' => 'El número de tarjeta es obligatorio para este método de pago.',
                'vencimiento.required' => 'Ingresa la fecha de expiración (MM/YY).',
                'cvc.required' => 'Ingresa el código CVC.',
            ]);
        }

        // Transacción para registrar el comprobante y descontar inventario
        $comprobante = DB::transaction(function () use (
            $validated,
            $user,
            $pedido,
            $subtotalComida,
            $montoDescuento,
            $descuentoConcepto,
            $totalFinal,
            $metodo
        ) {
            $correlativo = (int) (Comprobante::max('id') ?? 0) + 1;
            $numeroSerie = str_pad((string) $correlativo, 7, '0', STR_PAD_LEFT);
            $codigoReserva = 'SNK-'.strtoupper(Str::random(6));

            $nuevoComprobante = Comprobante::create([
                'codigo_reserva' => $codigoReserva,
                'user_id' => $user?->id,
                'cliente_nombre' => $validated['nombre'],
                'cliente_email' => $validated['email'],
                'sede' => $pedido['sede'],
                'sala' => 'Módulo Express Dulcería',
                'pelicula_titulo' => 'Consumo Dulcería & Snacks (Sin función)',
                'pelicula_imagen' => null,
                'fecha_funcion' => now(),
                'asientos' => [],
                'cantidad_entradas' => 0,
                'monto_entradas' => 0,
                'monto_dulceria' => $subtotalComida,
                'monto_descuento' => $montoDescuento,
                'descuento_concepto' => $descuentoConcepto,
                'tipo' => 'boleta',
                'serie' => 'B002',
                'numero' => $numeroSerie,
                'subtotal' => round($totalFinal / 1.18, 2),
                'igv' => round($totalFinal - ($totalFinal / 1.18), 2),
                'total' => $totalFinal,
                'metodo_pago_nombre' => $totalFinal == 0 ? 'Promoción 100%' : ($metodo?->nombre ?? 'Tarjeta / Digital'),
                'estado' => 'pagado',
            ]);

            // Registrar cada producto en detalle_comprobante y descontar stock
            foreach ($pedido['items'] as $item) {
                $nuevoComprobante->detalles()->create([
                    'itemable_type' => 'App\Models\Producto',
                    'itemable_id' => (int) $item['id'],
                    'descripcion' => $item['nombre'],
                    'cantidad' => (int) $item['cantidad'],
                    'precio_unitario' => (float) $item['precio'],
                    'subtotal' => (float) $item['subtotal'],
                    'es_canje' => false,
                    'puntos_utilizados' => 0,
                ]);

                if ($prod = Producto::find($item['id'])) {
                    $prod->decrement('stock', min($prod->stock, (int) $item['cantidad']));
                }
            }

            // Registrar pago en comprobante_pago
            DB::table('comprobante_pago')->insert([
                'comprobante_id' => $nuevoComprobante->id,
                'metodo' => $metodo && $metodo->requiere_tarjeta ? 'tarjeta' : 'transferencia',
                'monto' => $totalFinal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Sumar puntos al usuario si está autenticado
            if ($user && $totalFinal > 0) {
                if (\App\Models\Configuracion::puntosSeparadosActivo()) {
                    $user->sumarPuntosDulceria(5);
                } else {
                    $user->increment('saldo_puntos', 5);
                    $user->puntos_dulceria = (int) ($user->puntos_dulceria ?? 0) + 5;
                    $user->save();
                }
            }

            return $nuevoComprobante;
        });

        // Enviar comprobante con PDF al correo del cliente
        if (! empty($comprobante->cliente_email)) {
            try {
                Mail::to($comprobante->cliente_email)
                    ->send(new ComprobanteCompraMail($comprobante));
            } catch (\Throwable $e) {
                Log::error('Error enviando correo de pedido de solo comida: '.$e->getMessage());
            }
        }

        session()->forget('pedido_dulceria_solo');

        return redirect()->route('pago.comprobante', $comprobante->codigo_reserva)->with(
            'success',
            '¡Pedido de comida procesado con éxito! Presenta este código QR en el Módulo Express de dulcería de '.$comprobante->sede.' para retirar tus productos.'
        );
    }
}
