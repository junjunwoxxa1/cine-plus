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

        // Obtener todas las funciones vigentes y con asientos disponibles para esta película
        $todasLasFunciones = $pelicula->funciones()
            ->with(['sala', 'pelicula'])
            ->where('fecha_hora', '>=', now()->subMinutes(15))
            ->where('asientos_disponibles', '>', 0)
            ->orderBy('fecha_hora', 'asc')
            ->get();

        // Sedes disponibles en total para esta película
        $sedesDisponibles = $todasLasFunciones
            ->pluck('sala.sede')
            ->filter()
            ->unique()
            ->values();

        // Fechas únicas disponibles en total para esta película
        $fechasDisponibles = $todasLasFunciones
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
            'sedesDisponibles',
            'fechasDisponibles'
        ));
    }
}
