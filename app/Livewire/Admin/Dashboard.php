<?php

namespace App\Livewire\Admin;

use App\Models\CategoriaProducto;
use App\Models\Funcion;
use App\Models\Pelicula;
use App\Models\Producto;
use App\Models\Sala;
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
        return view('livewire.admin.dashboard', [
            'totalSedes' => \App\Models\Sede::count(),
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
        ]);
    }
}
