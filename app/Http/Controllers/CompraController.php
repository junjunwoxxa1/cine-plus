<?php

namespace App\Http\Controllers;

use App\Models\Butaca;
use App\Models\Comprobante;
use App\Models\Funcion;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    /**
     * Muestra el panel de "Mis Compras" con entradas activas, QR y comprobantes.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $queryCodigo = trim((string) $request->query('codigo', ''));
        $queryEmail = trim((string) $request->query('email', ''));

        $comprobantesQuery = Comprobante::with('detalles')->latest();

        if ($queryCodigo !== '') {
            $comprobantesQuery->where('codigo_reserva', strtoupper($queryCodigo));
        } elseif ($queryEmail !== '') {
            $comprobantesQuery->where('cliente_email', $queryEmail);
        } elseif ($user) {
            $comprobantesQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('cliente_email', $user->email);
            });
        } else {
            // Usuario no autenticado sin búsqueda
            $comprobantes = collect();
            $entradasActivas = collect();
            $entradasAnuladas = collect();
            $historial = collect();

            return view('compras.index', compact('comprobantes', 'entradasActivas', 'entradasAnuladas', 'historial', 'queryCodigo', 'queryEmail'));
        }

        $comprobantes = $comprobantesQuery->get();

        // Entradas activas (función vigente no vencida y estado pagado)
        $ahora = now();
        $entradasActivas = $comprobantes->filter(function ($c) use ($ahora) {
            return $c->estado === 'pagado'
                && $c->fecha_funcion
                && $c->fecha_funcion->isAfter($ahora->copy()->subHours(3));
        });

        // Entradas canceladas / anuladas
        $entradasAnuladas = $comprobantes->filter(function ($c) {
            return $c->estado === 'anulado';
        });

        // Historial de compras pasadas
        $historial = $comprobantes->reject(function ($c) use ($entradasActivas, $entradasAnuladas) {
            return $entradasActivas->contains('id', $c->id) || $entradasAnuladas->contains('id', $c->id);
        });

        return view('compras.index', compact(
            'comprobantes',
            'entradasActivas',
            'entradasAnuladas',
            'historial',
            'queryCodigo',
            'queryEmail'
        ));
    }

    /**
     * Búsqueda rápida de reserva por código o correo para invitados.
     */
    public function buscar(Request $request)
    {
        $validated = $request->validate([
            'criterio' => 'required|string|max:100',
        ]);

        $criterio = trim($validated['criterio']);

        // Si es código de reserva (ej: CP-XXXXXX)
        if (str_starts_with(strtoupper($criterio), 'CP-') || strlen($criterio) <= 12) {
            $comprobante = Comprobante::where('codigo_reserva', strtoupper($criterio))->first();
            if ($comprobante) {
                return redirect()->route('pago.comprobante', $comprobante->codigo_reserva);
            }
        }

        // Búsqueda por email o código
        return redirect()->route('compras.index', [
            filter_var($criterio, FILTER_VALIDATE_EMAIL) ? 'email' : 'codigo' => $criterio,
        ]);
    }

    /**
     * Procesa la cancelación / anulación automática de una compra o entrada.
     * Regla: Solo válida si faltan al menos 12 horas para el inicio de la función.
     * Si la compra es para una función que se estrena en menos de 12 horas, se rechaza automáticamente.
     */
    public function cancelar(Request $request)
    {
        $validated = $request->validate([
            'codigo_reserva' => 'required|string|max:50',
            'motivo' => 'nullable|string|max:255',
        ]);

        $codigo = strtoupper(trim($validated['codigo_reserva']));
        $comprobante = Comprobante::with('detalles')->where('codigo_reserva', $codigo)->first();

        if (! $comprobante) {
            return back()->withErrors(['error' => "No se encontró ningún comprobante con el código {$codigo}."]);
        }

        // Validar propiedad de la compra si el usuario está autenticado
        $user = auth()->user();
        if ($user && ! $user->isAdmin()) {
            if ($comprobante->user_id && $comprobante->user_id !== $user->id && $comprobante->cliente_email !== $user->email) {
                return back()->withErrors(['error' => 'No tienes autorización para cancelar este comprobante.']);
            }
        }

        // Validar la regla estricta de las 12 horas
        if (! $comprobante->puedeCancelar()) {
            $motivo = $comprobante->motivoNoCancelable() ?? 'Esta compra no cumple con las condiciones para cancelación automática.';

            return back()->withErrors(['error' => $motivo]);
        }

        // Proceso automático de anulación y liberación de recursos
        DB::transaction(function () use ($comprobante, $user) {
            // 1. Cambiar estado a anulado
            $comprobante->estado = 'anulado';
            $comprobante->save();

            // 2. Liberar las butacas ocupadas en la función
            $detallesFuncion = $comprobante->detalles()->where('itemable_type', 'App\Models\Funcion')->get();
            foreach ($detallesFuncion as $detalle) {
                $funcion = Funcion::with('sala')->find($detalle->itemable_id);
                if ($funcion && is_array($comprobante->asientos) && ! empty($comprobante->asientos)) {
                    $butacaIds = Butaca::where('sala_id', $funcion->sala_id)
                        ->whereIn('numero_butaca', $comprobante->asientos)
                        ->pluck('id');

                    if ($butacaIds->isNotEmpty()) {
                        DB::table('funcion_butaca')
                            ->where('funcion_id', $funcion->id)
                            ->whereIn('butaca_id', $butacaIds)
                            ->update([
                                'disponible' => true,
                                'updated_at' => now(),
                            ]);

                        // Recalcular asientos disponibles en la sala
                        $disponiblesCount = DB::table('funcion_butaca')
                            ->where('funcion_id', $funcion->id)
                            ->where('disponible', true)
                            ->count();

                        $funcion->update(['asientos_disponibles' => $disponiblesCount]);
                    }
                }
            }

            // 3. Restaurar stock de productos de confitería / dulcería
            $detallesProductos = $comprobante->detalles()->where('itemable_type', 'App\Models\Producto')->get();
            foreach ($detallesProductos as $detalle) {
                $prod = Producto::find($detalle->itemable_id);
                if ($prod) {
                    $prod->increment('stock', (int) $detalle->cantidad);
                }
            }

            // 4. Reembolsar puntos si hubo canje
            $cliente = $comprobante->user ?? ($user && $user->email === $comprobante->cliente_email ? $user : null);
            if ($cliente) {
                $puntosEntradas = (int) $comprobante->detalles()->where('itemable_type', 'App\Models\Funcion')->sum('puntos_utilizados');
                $puntosDulceria = (int) $comprobante->detalles()->where('itemable_type', 'App\Models\Producto')->sum('puntos_utilizados');

                if ($puntosEntradas > 0) {
                    $cliente->sumarPuntosCine($puntosEntradas);
                }
                if ($puntosDulceria > 0) {
                    $cliente->sumarPuntosDulceria($puntosDulceria);
                }

                // Si la compra acumuló puntos con dinero real, revertir los puntos otorgados
                if ($comprobante->total > 0) {
                    $puntosGanadosEntradas = $comprobante->monto_entradas > 0 ? 10 : 0;
                    $puntosGanadosDulceria = $comprobante->monto_dulceria > 0 ? 5 : 0;

                    if ($puntosGanadosEntradas > 0) {
                        $cliente->descontarPuntosCine($puntosGanadosEntradas);
                    }
                    if ($puntosGanadosDulceria > 0) {
                        $cliente->descontarPuntosDulceria($puntosGanadosDulceria);
                    }
                }

                // Registrar en el historial de puntos
                DB::table('puntos_movimiento')->insert([
                    'user_id' => $cliente->id,
                    'comprobante_id' => $comprobante->id,
                    'tipo' => 'ajuste',
                    'puntos' => 0,
                    'concepto' => "Anulación automática de reserva #{$comprobante->codigo_reserva} (12h de anticipación)",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('compras.index')->with('success', "¡Tu compra #{$comprobante->codigo_reserva} ha sido cancelada exitosamente! Las butacas fueron liberadas y el proceso de anulación se completó de forma automática.");
    }
}
