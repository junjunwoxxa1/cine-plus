<?php

namespace App\Http\Controllers;

use App\Models\Pelicula;
use Illuminate\Http\Request;

class PeliculaController extends Controller
{
    public function show(Request $request, Pelicula $pelicula)
    {
        $filtroSede = trim((string) $request->query('sede', ''));
        $filtroDia = trim((string) $request->query('dia', ''));
        $filtroTurno = trim((string) $request->query('turno', '')); // 'manana', 'tarde', 'noche'

        // Obtener solo funciones vigentes y con asientos disponibles
        $funcionesQuery = $pelicula->funciones()
            ->with(['sala', 'pelicula'])
            ->where('fecha_hora', '>=', now()->subMinutes(15))
            ->where('asientos_disponibles', '>', 0)
            ->orderBy('fecha_hora', 'asc');

        // Filtrar por sede si está seleccionada
        if ($filtroSede !== '' && $filtroSede !== 'all') {
            $funcionesQuery->whereHas('sala', function ($q) use ($filtroSede) {
                $q->where('sede', $filtroSede);
            });
        }

        // Filtrar por día si está seleccionado
        if ($filtroDia !== '' && $filtroDia !== 'all') {
            if ($filtroDia === 'hoy') {
                $funcionesQuery->whereDate('fecha_hora', today());
            } elseif ($filtroDia === 'manana') {
                $funcionesQuery->whereDate('fecha_hora', today()->addDay());
            } else {
                $funcionesQuery->whereDate('fecha_hora', $filtroDia);
            }
        }

        $todasLasFunciones = $funcionesQuery->get();

        // Filtrar por turno horario si aplica
        if ($filtroTurno !== '' && $filtroTurno !== 'all') {
            $todasLasFunciones = $todasLasFunciones->filter(function ($f) use ($filtroTurno) {
                $hora = (int) $f->fecha_hora->format('H');

                return match ($filtroTurno) {
                    'manana' => $hora < 14,
                    'tarde' => $hora >= 14 && $hora < 19,
                    'noche' => $hora >= 19,
                    default => true,
                };
            });
        }

        // Sedes disponibles en total para esta película
        $sedesDisponibles = $pelicula->funciones()
            ->where('fecha_hora', '>=', now()->subMinutes(15))
            ->where('asientos_disponibles', '>', 0)
            ->with('sala')
            ->get()
            ->pluck('sala.sede')
            ->filter()
            ->unique()
            ->values();

        // Fechas únicas disponibles en total para esta película
        $fechasDisponibles = $pelicula->funciones()
            ->where('fecha_hora', '>=', now()->subMinutes(15))
            ->where('asientos_disponibles', '>', 0)
            ->get()
            ->map(fn ($f) => $f->fecha_hora->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();

        // Agrupar funciones disponibles por: Sede -> Fecha (Día) -> Sala
        $funcionesAgrupadas = $todasLasFunciones->groupBy(function ($f) {
            return $f->sala->sede ?? 'Sede Principal';
        })->map(function ($porSede) {
            return $porSede->groupBy(function ($f) {
                return $f->fecha_hora->format('Y-m-d');
            })->map(function ($porFecha) {
                return $porFecha->groupBy('sala_id');
            });
        });

        return view('detalle', compact(
            'pelicula',
            'funcionesAgrupadas',
            'todasLasFunciones',
            'filtroSede',
            'filtroDia',
            'filtroTurno',
            'sedesDisponibles',
            'fechasDisponibles'
        ));
    }
}
