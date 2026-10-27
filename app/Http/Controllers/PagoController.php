<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Descuento;
use App\Models\Funcion;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PagoController extends Controller
{
    public function index()
    {
        $reserva = session('reserva_temp');

        if (! $reserva) {
            return redirect()->route('home')->withErrors(['error' => 'No hay una reserva activa.']);
        }

        $funcion = Funcion::with(['pelicula', 'sala'])->findOrFail($reserva['funcion_id']);

        // Productos elegidos previamente en la página de dulcería
        $productosSeleccionados = $reserva['productos'] ?? [];
        $montoDulceria = (float) ($reserva['monto_dulceria'] ?? 0);

        // Datos del usuario autenticado o valores por defecto
        $user = auth()->user();
        $cliente = [
            'nombre' => $user ? $user->name : '',
            'email' => $user ? $user->email : '',
            'puntos' => $user ? (int) $user->saldo_puntos : 0,
            'puntos_cine' => $user ? (int) ($user->puntos_cine ?? 0) : 0,
            'puntos_dulceria' => $user ? (int) ($user->puntos_dulceria ?? 0) : 0,
        ];

        // Métodos de pago y promociones dinámicas desde la BD
        $metodosPago = MetodoPago::activos()->get();
        $descuentos = Descuento::vigentes()->get();

        return view('pago.index', compact(
            'reserva',
            'funcion',
            'productosSeleccionados',
            'montoDulceria',
            'cliente',
            'metodosPago',
            'descuentos'
        ));
    }

    public function procesar(Request $request)
    {
        $reserva = session('reserva_temp');

        if (! $reserva) {
            return redirect()->route('home')->withErrors(['error' => 'La sesión de pago ha expirado.']);
        }

        // Obtener el método de pago seleccionado
        $metodo = MetodoPago::activos()->where('codigo', $request->input('metodo_pago'))->first();

        // Obtener función y calcular subtotales
        $funcion = Funcion::with(['pelicula', 'sala'])->findOrFail($reserva['funcion_id']);
        $subtotalEntradas = (float) ($funcion->precio * (int) $reserva['cantidad']);
        $montoDulceria = (float) ($reserva['monto_dulceria'] ?? 0);
        $montoDescuento = 0.0;
        $descuentoConcepto = null;
        $esCanjeCine = false;
        $esCanjeDulceria = false;
        $esCanjePuntos = false;
        $puntosUtilizadosCine = 0;
        $puntosUtilizadosDulceria = 0;

        $user = auth()->user();

        // 1. Canje de Puntos de Cine para Entradas (50 pts = 100% descuento en entradas)
        if ($request->boolean('canjear_puntos_cine') || $request->boolean('canjear_puntos')) {
            $ptsCine = (int) ($user?->puntos_cine ?? 0);
            $ptsTotal = (int) ($user?->saldo_puntos ?? 0);

            if ($user && ($ptsCine >= 50 || $ptsTotal >= 50)) {
                $montoDescuento += $subtotalEntradas;
                $descuentoConcepto = 'Canje de Entradas (50 Puntos Cine)';
                $esCanjeCine = true;
                $puntosUtilizadosCine = 50;
            }
        }

        // 2. Canje de Puntos de Dulcería para Snacks/Alimentos (50 pts = hasta S/ 20.00 de descuento en dulcería)
        if ($request->boolean('canjear_puntos_dulceria') && $montoDulceria > 0) {
            $ptsDulceria = (int) ($user?->puntos_dulceria ?? 0);
            $ptsTotalRestante = (int) ($user?->saldo_puntos ?? 0) - $puntosUtilizadosCine;

            if ($user && ($ptsDulceria >= 50 || $ptsTotalRestante >= 50)) {
                $descDulceria = min($montoDulceria, 20.00);
                $montoDescuento += $descDulceria;
                $descuentoConcepto = $descuentoConcepto
                    ? $descuentoConcepto . ' + Canje Dulcería (50 Puntos Dulcería)'
                    : 'Canje de Snacks (50 Puntos Dulcería)';
                $esCanjeDulceria = true;
                $puntosUtilizadosDulceria = 50;
            }
        }

        $esCanjePuntos = $esCanjeCine || $esCanjeDulceria;

        // 3. Descuento seleccionado por ID (si no hubo canje total)
        if (! $esCanjeCine && $request->filled('descuento_id')) {
            $descuento = Descuento::vigentes()->find($request->descuento_id);
            if ($descuento) {
                $descId = (float) $descuento->calcular($subtotalEntradas, (int) $reserva['cantidad']);
                $montoDescuento += $descId;
                $descuentoConcepto = $descuento->codigo
                    ? 'Cupón '.$descuento->codigo.' ('.$descuento->nombre.')'
                    : $descuento->nombre;
            }
        // 4. Código promocional / Cupón tipeado
        } elseif (! $esCanjeCine && $request->filled('codigo_promo')) {
            $codigoLimpio = strtoupper(trim((string) $request->codigo_promo));
            $descuento = Descuento::vigentes()->where('codigo', $codigoLimpio)->first();
            if ($descuento) {
                $descPromo = (float) $descuento->calcular($subtotalEntradas, (int) $reserva['cantidad']);
                $montoDescuento += $descPromo;
                $descuentoConcepto = 'Cupón Promocional: '.$codigoLimpio.' ('.$descuento->nombre.')';
            }
        }

        // El descuento no puede exceder el total de entradas + dulcería
        $maximoDescuento = $subtotalEntradas + ($esCanjeDulceria ? min($montoDulceria, 20.00) : 0);
        $montoDescuento = round(min(max($montoDescuento, 0), $maximoDescuento), 2);
        $totalFinal = max(0, round(($subtotalEntradas + $montoDulceria) - $montoDescuento, 2));

        // Reglas de validación
        $rules = [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'metodo_pago' => 'required|exists:metodo_pago,codigo',
            'descuento_id' => 'nullable|exists:descuento,id',
            'codigo_promo' => 'nullable|string|max:50',
            'canjear_puntos' => 'nullable|boolean',
            'canjear_puntos_cine' => 'nullable|boolean',
            'canjear_puntos_dulceria' => 'nullable|boolean',
        ];

        // Solo requerir tarjeta si hay un saldo restante a pagar y el método lo exige
        if ($totalFinal > 0 && $metodo && $metodo->requiere_tarjeta) {
            $rules['numero_tarjeta'] = 'required|string|min:12|max:22';
            $rules['vencimiento'] = 'required|string|min:4|max:7';
            $rules['cvc'] = 'required|string|min:3|max:4';
        }

        $validated = $request->validate($rules);

        // Crear Comprobante y detalles dentro de una transacción atómica
        $comprobante = DB::transaction(function () use (
            $validated,
            $user,
            $funcion,
            $reserva,
            $subtotalEntradas,
            $montoDulceria,
            $montoDescuento,
            $descuentoConcepto,
            $esCanjePuntos,
            $esCanjeCine,
            $esCanjeDulceria,
            $puntosUtilizadosCine,
            $puntosUtilizadosDulceria,
            $totalFinal,
            $metodo
        ) {
            // Descontar puntos de Club Cine+ de forma individual o general
            if ($user) {
                if ($esCanjeCine && $puntosUtilizadosCine > 0) {
                    $user->descontarPuntosCine($puntosUtilizadosCine);
                }
                if ($esCanjeDulceria && $puntosUtilizadosDulceria > 0) {
                    $user->descontarPuntosDulceria($puntosUtilizadosDulceria);
                }
                if ($esCanjePuntos && ! $esCanjeCine && ! $esCanjeDulceria) {
                    $user->decrement('saldo_puntos', 50);
                }
            }

            $correlativo = (int) (Comprobante::max('id') ?? 0) + 1;
            $numeroSerie = str_pad((string) $correlativo, 7, '0', STR_PAD_LEFT);
            $codigoReserva = 'CP-'.strtoupper(Str::random(6));

            $nombreMetodoPago = $totalFinal == 0
                ? ($esCanjePuntos ? 'Canje Club Cine+ (Puntos)' : 'Promoción 100%')
                : ($metodo?->nombre ?? 'Tarjeta de Crédito / Débito');

            $nuevoComprobante = Comprobante::create([
                'codigo_reserva' => $codigoReserva,
                'user_id' => $user?->id,
                'cliente_nombre' => $validated['nombre'],
                'cliente_email' => $validated['email'],
                'sede' => 'Cine+ Alcázar - Miraflores',
                'sala' => $funcion->sala->numero_sala,
                'pelicula_titulo' => $funcion->pelicula->titulo,
                'pelicula_imagen' => $funcion->pelicula->imagen,
                'fecha_funcion' => $funcion->fecha_hora,
                'asientos' => $reserva['butacas_codigos'] ?? [],
                'cantidad_entradas' => (int) $reserva['cantidad'],
                'monto_entradas' => $subtotalEntradas,
                'monto_dulceria' => $montoDulceria,
                'monto_descuento' => $montoDescuento,
                'descuento_concepto' => $descuentoConcepto,
                'tipo' => 'boleta',
                'serie' => 'B001',
                'numero' => $numeroSerie,
                'subtotal' => round($totalFinal / 1.18, 2),
                'igv' => round($totalFinal - ($totalFinal / 1.18), 2),
                'total' => $totalFinal,
                'metodo_pago_nombre' => $nombreMetodoPago,
                'estado' => 'pagado',
            ]);

            // Detalle de Entradas
            $nuevoComprobante->detalles()->create([
                'itemable_type' => 'App\Models\Funcion',
                'itemable_id' => $funcion->id,
                'descripcion' => 'Entrada(s) para '.$funcion->pelicula->titulo.' (Asientos: '.$nuevoComprobante->asientos_texto.')',
                'cantidad' => (int) $reserva['cantidad'],
                'precio_unitario' => (float) $funcion->precio,
                'subtotal' => $subtotalEntradas,
                'es_canje' => $esCanjeCine || $esCanjePuntos,
                'puntos_utilizados' => $esCanjeCine ? $puntosUtilizadosCine : ($esCanjePuntos ? 50 : 0),
            ]);

            // Detalles de Dulcería / Snacks
            foreach (($reserva['productos'] ?? []) as $prodKey => $prodItem) {
                $productoId = (int) ($prodItem['id'] ?? (is_numeric($prodKey) ? $prodKey : 0));
                $precioUnitario = (float) ($prodItem['precio_unitario'] ?? $prodItem['precio'] ?? 0);
                $cantidad = (int) ($prodItem['cantidad'] ?? 1);
                $subtotalItem = (float) ($prodItem['subtotal'] ?? ($precioUnitario * $cantidad));

                $nuevoComprobante->detalles()->create([
                    'itemable_type' => 'App\Models\Producto',
                    'itemable_id' => $productoId,
                    'descripcion' => $prodItem['nombre'] ?? 'Producto de Dulcería',
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalItem,
                    'es_canje' => $esCanjeDulceria,
                    'puntos_utilizados' => $esCanjeDulceria ? $puntosUtilizadosDulceria : 0,
                ]);

                // Descontar inventario / stock sincronizado
                if ($prod = \App\Models\Producto::find($productoId)) {
                    $prod->decrement('stock', min($prod->stock, $cantidad));
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

            // Otorgar puntos individuales si el usuario pagó un saldo con dinero real
            if ($user && $totalFinal > 0) {
                if ($subtotalEntradas > 0 && ! $esCanjeCine) {
                    $user->sumarPuntosCine(10);
                }
                if ($montoDulceria > 0 && ! $esCanjeDulceria) {
                    $user->sumarPuntosDulceria(5);
                }
            }

            return $nuevoComprobante;
        });

        // Enviar comprobante con PDF adjunto al correo del cliente
        if (! empty($comprobante->cliente_email)) {
            try {
                \Illuminate\Support\Facades\Mail::mailer('smtp')
                    ->to($comprobante->cliente_email)
                    ->send(new \App\Mail\ComprobanteCompraMail($comprobante));
                \Illuminate\Support\Facades\Log::info('Comprobante PDF enviado exitosamente a: '.$comprobante->cliente_email);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error enviando correo con comprobante PDF a '.$comprobante->cliente_email.': '.$e->getMessage().' en '.$e->getFile().':'.$e->getLine());
            }
        }

        // Limpiar sesión temporal de reserva
        session()->forget('reserva_temp');

        return redirect()->route('pago.comprobante', $comprobante->codigo_reserva)->with(
            'success',
            '¡Pago procesado con éxito! Aquí tienes tu comprobante de entrada digital.'
        );
    }

    /**
     * Muestra el comprobante de pago con QR escaneable y ticket de entradas (estilo Cineplanet).
     */
    public function comprobante(string $codigo)
    {
        $comprobante = Comprobante::with('detalles')
            ->where('codigo_reserva', $codigo)
            ->firstOrFail();

        return view('pago.comprobante', compact('comprobante'));
    }

    /**
     * Descarga directa del comprobante en formato PDF.
     */
    public function descargarPdf(string $codigo, \App\Services\PdfService $pdfService)
    {
        $comprobante = Comprobante::with('detalles')
            ->where('codigo_reserva', $codigo)
            ->firstOrFail();

        $pdfBinary = $pdfService->generarComprobantePdf($comprobante);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="CinePlus_'.$comprobante->codigo_reserva.'.pdf"',
        ]);
    }
}
