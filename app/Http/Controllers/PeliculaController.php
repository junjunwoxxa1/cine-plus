<?php

namespace App\Http\Controllers;

use App\Models\Pelicula;
use Illuminate\Http\Request;

class PeliculaController extends Controller
{
    public function show(Pelicula $pelicula)
    {
        $pelicula->load(['funciones' => function ($query) {
            $query->with('sala')->orderBy('fecha_hora', 'asc');
        }]);
        return view('detalle', compact('pelicula'));
    }
}
