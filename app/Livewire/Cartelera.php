<?php

namespace App\Livewire;

use App\Models\Pelicula;
use App\Models\Sala;
use Livewire\Attributes\Url;
use Livewire\Component;

class Cartelera extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $sede = 'all';

    #[Url]
    public string $dia = 'all';

    #[Url]
    public string $genero = 'all';

    public bool $mostrarSugerencias = false;

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'sede', 'dia', 'genero', 'mostrarSugerencias']);
    }

    public function seleccionarSugerencia(string $titulo): void
    {
        $this->search = $titulo;
        $this->mostrarSugerencias = false;
    }

    public function updatedSearch(): void
    {
        $this->mostrarSugerencias = mb_strlen(trim($this->search)) >= 1;
    }

    public function render()
    {
        // 1. Sedes disponibles registradas
        $sedes = \App\Models\Sede::activas()->orderBy('nombre')->pluck('nombre');

        if ($sedes->isEmpty()) {
            $sedes = Sala::query()
                ->select('sede')
                ->distinct()
                ->whereNotNull('sede')
                ->where('sede', '!=', '')
                ->orderBy('sede')
                ->pluck('sede');
        }

        // 2. Próximos 7 días para filtros por fecha estilo Cineplanet
        $diasDisponibles = collect();
        for ($i = 0; $i < 7; $i++) {
            $fecha = today()->addDays($i);
            $clave = $i === 0 ? 'hoy' : ($i === 1 ? 'manana' : $fecha->format('Y-m-d'));
            $etiqueta = $i === 0 ? 'Hoy' : ($i === 1 ? 'Mañana' : $fecha->locale('es')->isoFormat('ddd D'));

            $diasDisponibles->push([
                'clave' => $clave,
                'fecha' => $fecha->format('Y-m-d'),
                'etiqueta' => ucfirst($etiqueta),
                'dia_semana' => ucfirst($fecha->locale('es')->isoFormat('ddd')),
                'dia_numero' => $fecha->format('d'),
                'mes' => strtoupper($fecha->locale('es')->isoFormat('MMM')),
            ]);
        }

        // 3. Géneros de películas disponibles
        $generos = Pelicula::query()
            ->select('genero')
            ->distinct()
            ->whereNotNull('genero')
            ->pluck('genero')
            ->flatMap(fn ($g) => explode(',', str_replace(['/', '-'], ',', (string) $g)))
            ->map(fn ($g) => trim($g))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // 4. Consulta dinámica de películas y horarios
        $peliculasQuery = Pelicula::query()
            ->with(['funciones' => function ($q) {
                $q->with('sala')
                    ->when($this->sede !== 'all', function ($qSede) {
                        $qSede->whereHas('sala', fn ($s) => $s->where('sede', $this->sede));
                    })
                    ->when($this->dia !== 'all', function ($qDia) {
                        if ($this->dia === 'hoy') {
                            $qDia->whereDate('fecha_hora', today());
                        } elseif ($this->dia === 'manana') {
                            $qDia->whereDate('fecha_hora', today()->addDay());
                        } else {
                            $qDia->whereDate('fecha_hora', $this->dia);
                        }
                    })
                    ->where('fecha_hora', '>=', now()->subHours(1))
                    ->orderBy('fecha_hora');
            }]);

        // Filtro buscador en tiempo real
        if ($this->search !== '') {
            $termino = '%'.$this->search.'%';
            $peliculasQuery->where(function ($q) use ($termino) {
                $q->where('titulo', 'like', $termino)
                    ->orWhere('genero', 'like', $termino)
                    ->orWhere('director', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino);
            });
        }

        // Filtro por Género
        if ($this->genero !== 'all') {
            $peliculasQuery->where('genero', 'like', '%'.$this->genero.'%');
        }

        // Filtro por Sede o Día: si están seleccionados, mostrar solo películas que tienen funciones para ese criterio
        if ($this->sede !== 'all' || $this->dia !== 'all') {
            $peliculasQuery->whereHas('funciones', function ($q) {
                $q->when($this->sede !== 'all', function ($qSede) {
                    $qSede->whereHas('sala', fn ($s) => $s->where('sede', $this->sede));
                })
                    ->when($this->dia !== 'all', function ($qDia) {
                        if ($this->dia === 'hoy') {
                            $qDia->whereDate('fecha_hora', today());
                        } elseif ($this->dia === 'manana') {
                            $qDia->whereDate('fecha_hora', today()->addDay());
                        } else {
                            $qDia->whereDate('fecha_hora', $this->dia);
                        }
                    })
                    ->where('fecha_hora', '>=', now()->subHours(1));
            });
        }

        $sugerencias = collect();
        if ($this->search !== '' && $this->mostrarSugerencias) {
            $sugerencias = Pelicula::query()
                ->where(function ($q) {
                    $termino = '%'.$this->search.'%';
                    $q->where('titulo', 'like', $termino)
                        ->orWhere('genero', 'like', $termino);
                })
                ->select(['id', 'titulo', 'slug', 'genero', 'imagen', 'clasificacion', 'duracion'])
                ->limit(6)
                ->get();
        }

        $peliculas = $peliculasQuery->orderBy('titulo')->get();

        return view('livewire.cartelera', compact('peliculas', 'sedes', 'diasDisponibles', 'generos', 'sugerencias'));
    }
}

