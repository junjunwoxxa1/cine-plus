<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use Illuminate\Http\Request;

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
            $historial = collect();

            return view('compras.index', compact('comprobantes', 'entradasActivas', 'historial', 'queryCodigo', 'queryEmail'));
        }

        $comprobantes = $comprobantesQuery->get();

        // Entradas activas (función no vencida o de hoy) y pasadas
        $ahora = now();
        $entradasActivas = $comprobantes->filter(function ($c) use ($ahora) {
            return $c->fecha_funcion && $c->fecha_funcion->isAfter($ahora->copy()->subHours(3));
        });

        $historial = $comprobantes->reject(function ($c) use ($entradasActivas) {
            return $entradasActivas->contains('id', $c->id);
        });

        return view('compras.index', compact('comprobantes', 'entradasActivas', 'historial', 'queryCodigo', 'queryEmail'));
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
}

