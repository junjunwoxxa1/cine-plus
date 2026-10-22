<?php

namespace App\Http\Controllers;

use App\Models\Butaca;
use App\Models\Funcion;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function selectButacas(Funcion $funcion)
    {
        $reservaPrevia = session('reserva_temp');
        $butacasSeleccionadas = [];
        $cantidadSeleccionada = 2;

        if ($reservaPrevia) {
            if (($reservaPrevia['funcion_id'] ?? null) == $funcion->id) {
                $butacasSeleccionadas = $reservaPrevia['butacas_codigos'] ?? [];
                $cantidadSeleccionada = (int) ($reservaPrevia['cantidad'] ?? (count($butacasSeleccionadas) ?: 2));
            }

            // Liberar las butacas ocupadas previamente en la BD para que queden disponibles y seleccionables
            $this->liberarButacasDeReserva($reservaPrevia);

            // Conservar la sesión actualizada manteniendo productos de dulcería si eran de esta misma función
            if (($reservaPrevia['funcion_id'] ?? null) == $funcion->id) {
                session(['reserva_temp' => [
                    'funcion_id' => $funcion->id,
                    'butacas_ids' => [],
                    'butacas_codigos' => $butacasSeleccionadas,
                    'cantidad' => $cantidadSeleccionada,
                    'monto_total' => $reservaPrevia['monto_total'] ?? 0,
                    'productos' => $reservaPrevia['productos'] ?? [],
                    'monto_dulceria' => $reservaPrevia['monto_dulceria'] ?? 0,
                ]]);
            } else {
                session()->forget('reserva_temp');
            }
        }

        // Carga la función con la película, sala y las butacas asociadas con su estado (disponible / ocupada)
        $funcion->load(['pelicula', 'sala', 'butacas']);

        $mapaConfig = $funcion->obtenerMapaConfig();

        // Ordenamos y agrupamos las butacas por fila extraída del número de butaca (ej: "A-1" -> "A")
        $butacasPorFila = $funcion->butacas
            ->sortBy(function ($butaca) {
                $partes = explode('-', $butaca->numero_butaca);
                $fila = $partes[0] ?? 'A';
                $num = (int) ($partes[1] ?? 1);

                return sprintf('%s-%03d', $fila, $num);
            })
            ->groupBy(function ($butaca) {
                return explode('-', $butaca->numero_butaca)[0] ?? 'A';
            });

        return view('reservas.butacas', compact(
            'funcion',
            'butacasPorFila',
            'mapaConfig',
            'butacasSeleccionadas',
            'cantidadSeleccionada'
        ));
    }

    public function store(Request $request)
    {
        // 1. Validación de la petición
        $validated = $request->validate([
            'funcion_id' => 'required|exists:funcion,id',
            'cantidad_entradas' => 'required|integer|min:1|max:10',
            'butacas' => 'required|array|min:1',
            'butacas.*' => 'required|string',
        ]);

        if (count($validated['butacas']) !== (int) $validated['cantidad_entradas']) {
            return back()->withErrors(['butacas' => 'La cantidad de butacas seleccionadas no coincide con las entradas solicitadas.']);
        }

        $funcion = Funcion::with('sala')->findOrFail($validated['funcion_id']);
        $mapaConfig = $funcion->obtenerMapaConfig();
        $celdasEscalera = $mapaConfig['celdas_escalera'] ?? [];

        // 2. Procesamiento dentro de una transacción para evitar inconsistencias
        try {
            DB::beginTransaction();

            // Si el usuario ya tenía una reserva previa en sesión, liberamos sus butacas en BD
            $reservaPrevia = session('reserva_temp');
            $productosPrevios = [];
            $montoDulceriaPrevia = 0;

            if ($reservaPrevia) {
                if (($reservaPrevia['funcion_id'] ?? null) == $funcion->id) {
                    $productosPrevios = $reservaPrevia['productos'] ?? [];
                    $montoDulceriaPrevia = $reservaPrevia['monto_dulceria'] ?? 0;
                }
                $this->liberarButacasDeReserva($reservaPrevia);
            }

            // Formatear códigos de asientos de la vista ("A1") al formato almacenado en BD ("A-1")
            $numerosButacas = array_map(function ($codigo) {
                preg_match('/([A-Za-z]+)(\d+)/', $codigo, $matches);
                return isset($matches[1], $matches[2]) ? $matches[1] . '-' . $matches[2] : $codigo;
            }, $validated['butacas']);

            // Obtener los IDs de las butacas pertenecientes a la sala
            $butacas = Butaca::where('sala_id', $funcion->sala_id)
                ->whereIn('numero_butaca', $numerosButacas)
                ->get();

            if ($butacas->count() !== count($numerosButacas)) {
                DB::rollBack();
                return back()->withErrors(['butacas' => 'Una o más butacas seleccionadas no son válidas.']);
            }

            // Verificar disponibilidad en la tabla pivote funcion_butaca y que no sea una escalera
            foreach ($butacas as $butaca) {
                if (in_array($butaca->numero_butaca, $celdasEscalera, true)) {
                    DB::rollBack();
                    return back()->withErrors(['butacas' => "La posición {$butaca->numero_butaca} corresponde a una escalera de acceso."]);
                }

                $disponible = $funcion->butacas()
                    ->where('butaca_id', $butaca->id)
                    ->wherePivot('disponible', true)
                    ->exists();

                if (!$disponible) {
                    DB::rollBack();
                    return back()->withErrors(['butacas' => "La butaca {$butaca->numero_butaca} ya no está disponible."]);
                }
            }

            // Marcar las butacas como no disponibles en la tabla pivote
            foreach ($butacas as $butaca) {
                $funcion->butacas()->updateExistingPivot($butaca->id, [
                    'disponible' => false,
                ]);
            }

            // Descontar asientos disponibles en la función
            $funcion->decrement('asientos_disponibles', count($butacas));

            DB::commit();

            // Guardar detalles temporales en la sesión para el flujo de pago, conservando dulcería si ya se había escogido
            session([
                'reserva_temp' => [
                    'funcion_id' => $funcion->id,
                    'butacas_ids' => $butacas->pluck('id')->toArray(),
                    'butacas_codigos' => $validated['butacas'],
                    'cantidad' => $validated['cantidad_entradas'],
                    'monto_total' => $funcion->precio * count($butacas),
                    'productos' => $productosPrevios,
                    'monto_dulceria' => $montoDulceriaPrevia,
                ],
            ]);

            // Redirigir a la dulcería para escoger comidas antes del pago
            return redirect()->route('reservas.dulceria');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Ocurrió un error al procesar la reserva. Inténtalo de nuevo.']);
        }
    }

    /**
     * Muestra la página de dulcería/comidas para que el cliente escoja snacks.
     */
    public function selectDulceria()
    {
        $reserva = session('reserva_temp');

        if (!$reserva) {
            return redirect()->route('home')->withErrors(['error' => 'No hay una reserva activa.']);
        }

        $funcion = Funcion::with(['pelicula', 'sala'])->findOrFail($reserva['funcion_id']);

        // Categorías activas con todos sus productos (para mostrar disponibles, desactivados y agotados en gris)
        $categorias = \App\Models\CategoriaProducto::activas()
            ->with(['productos' => fn ($q) => $q->orderBy('nombre')])
            ->get();

        $productos = $categorias->flatMap->productos;

        return view('reservas.dulceria', compact(
            'reserva',
            'funcion',
            'productos',
            'categorias'
        ));
    }

    /**
     * Guarda los productos de dulcería seleccionados y redirige al pago.
     */
    public function storeDulceria(Request $request)
    {
        $validated = $request->validate([
            'productos' => 'nullable|array',
            'productos.*' => 'integer|min:0',
        ]);

        $reserva = session('reserva_temp');

        if (!$reserva) {
            return redirect()->route('home')->withErrors(['error' => 'La sesión ha expirado.']);
        }

        // Calcular subtotal de dulcería validando stock real y estado activo
        $productosSeleccionados = [];
        $montoDulceria = 0;

        if (!empty($validated['productos'])) {
            $productosIds = array_keys(array_filter($validated['productos'], fn ($qty) => $qty > 0));
            $productos = Producto::whereIn('id', $productosIds)->get()->keyBy('id');

            foreach ($validated['productos'] as $productoId => $cantidad) {
                if ($cantidad > 0 && $productos->has($productoId)) {
                    $producto = $productos[$productoId];

                    // Omitir si el producto fue desactivado por el admin o no tiene existencias
                    if (! $producto->activo || $producto->stock <= 0) {
                        continue;
                    }

                    // No exceder el stock disponible
                    $cantidadReal = min((int) $producto->stock, (int) $cantidad);
                    $subtotal = $producto->precio * $cantidadReal;

                    $productosSeleccionados[$productoId] = [
                        'id' => (int) $producto->id,
                        'nombre' => $producto->nombre,
                        'cantidad' => $cantidadReal,
                        'precio' => (float) $producto->precio,
                        'precio_unitario' => (float) $producto->precio,
                        'subtotal' => (float) $subtotal,
                    ];
                    $montoDulceria += $subtotal;
                }
            }
        }

        // Actualizar la sesión con los productos de dulcería
        $reserva['productos'] = $productosSeleccionados;
        $reserva['monto_dulceria'] = $montoDulceria;
        session(['reserva_temp' => $reserva]);

        return redirect()->route('pago.index');
    }

    /**
     * Cancela la reserva temporal actual, liberando las butacas en BD y limpiando la sesión.
     */
    public function cancelar()
    {
        $reserva = session('reserva_temp');
        if ($reserva) {
            $this->liberarButacasDeReserva($reserva);
            session()->forget('reserva_temp');
        }

        return redirect()->route('home')->with('info', 'La reserva ha sido cancelada.');
    }

    /**
     * Libera las butacas bloqueadas de una reserva temporal en la base de datos.
     *
     * @param  array<string, mixed>|null  $reserva
     */
    private function liberarButacasDeReserva(?array $reserva): void
    {
        if (! $reserva) {
            return;
        }

        $funcionId = $reserva['funcion_id'] ?? null;
        $butacasIds = array_filter($reserva['butacas_ids'] ?? []);

        if ($funcionId && ! empty($butacasIds)) {
            $funcion = Funcion::find($funcionId);
            if ($funcion) {
                foreach ($butacasIds as $butacaId) {
                    $funcion->butacas()->updateExistingPivot($butacaId, [
                        'disponible' => true,
                    ]);
                }
                $funcion->increment('asientos_disponibles', count($butacasIds));
            }
        }
    }
}
