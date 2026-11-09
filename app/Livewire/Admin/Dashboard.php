<?php

namespace App\Livewire\Admin;

use App\Models\CategoriaProducto;
use App\Models\Comprobante;
use App\Models\Funcion;
use App\Models\Pelicula;
use App\Models\Producto;
use App\Models\Sala;
use App\Models\Sede;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard Admin')]
class Dashboard extends Component
{
    public function render()
    {
        // Estadísticas de ventas y recaudación por película desde comprobantes pagados
        $statsVentas = Comprobante::where('estado', 'pagado')
            ->whereNotNull('pelicula_titulo')
            ->where('pelicula_titulo', '!=', '')
            ->where('pelicula_titulo', '!=', 'Consumos Dulcería / Venta Directa')
            ->groupBy('pelicula_titulo')
            ->selectRaw('pelicula_titulo, SUM(cantidad_entradas) as butacas_vendidas, SUM(monto_entradas) as total_recaudado')
            ->get()
            ->keyBy('pelicula_titulo');

        $peliculas = Pelicula::withCount('funciones')->get();

        $resumenPeliculas = $peliculas->map(function ($p) use ($statsVentas) {
            $stats = $statsVentas->get($p->titulo);
            $butacas = (int) ($stats->butacas_vendidas ?? 0);
            $recaudado = (float) ($stats->total_recaudado ?? 0);

            return [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'imagen_url' => $p->imagen_url,
                'genero' => $p->genero,
                'duracion' => $p->duracion,
                'fecha_estreno' => $p->fecha_estreno,
                'fecha_fin' => $p->fecha_fin,
                'is_expirada' => $p->isExpirada(),
                'funciones_count' => $p->funciones_count,
                'butacas_vendidas' => $butacas,
                'total_recaudado' => $recaudado,
            ];
        })->sortByDesc('total_recaudado')->values();

        $peliculaMasRecaudada = $resumenPeliculas->sortByDesc('total_recaudado')->first();
        $peliculaMasButacas = $resumenPeliculas->sortByDesc('butacas_vendidas')->first();
        $totalButacasVendidasGeneral = $resumenPeliculas->sum('butacas_vendidas');
        $totalRecaudacionGeneral = $resumenPeliculas->sum('total_recaudado');

        return view('livewire.admin.dashboard', [
            'totalSedes' => Sede::count(),
            'totalPeliculas' => Pelicula::count(),
            'totalFunciones' => Funcion::count(),
            'totalSalas' => Sala::count(),
            'totalProductos' => Producto::count(),
            'totalCategorias' => CategoriaProducto::count(),
            'totalUsuarios' => User::count(),
            'proximasFunciones' => Funcion::with(['pelicula', 'sala'])
                ->where('fecha_hora', '>=', now())
                ->orderBy('fecha_hora', 'asc')
                ->take(5)
                ->get(),
            'resumenPeliculas' => $resumenPeliculas,
            'peliculaMasRecaudada' => $peliculaMasRecaudada,
            'peliculaMasButacas' => $peliculaMasButacas,
            'totalButacasVendidasGeneral' => $totalButacasVendidasGeneral,
            'totalRecaudacionGeneral' => $totalRecaudacionGeneral,
        ]);
    }
}
