<?php

namespace App\Livewire\Admin;

use App\Models\Pelicula;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Películas - Admin')]
class Peliculas extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public bool $modalAbierto = false;

    public ?int $peliculaId = null;
    public string $titulo = '';
    public string $imagen = '';
    public string $descripcion = '';
    public string $director = '';
    public string $genero = '';
    public string $fecha_estreno = '';
    public int $duracion = 120;
    public string $clasificacion = 'PG-13';
    public string $idioma = 'Español Latino';
    public bool $en_banner = false;
    public int $orden_banner = 0;

    protected function rules(): array
    {
        return [
            'titulo' => 'required|string|max:200',
            'imagen' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'director' => 'nullable|string|max:100',
            'genero' => 'nullable|string|max:100',
            'fecha_estreno' => 'nullable|date',
            'duracion' => 'required|integer|min:1',
            'clasificacion' => 'nullable|string|max:20',
            'idioma' => 'nullable|string|max:50',
            'en_banner' => 'boolean',
            'orden_banner' => 'integer|min:0',
        ];
    }

    public function toggleBanner(int $id): void
    {
        $p = Pelicula::findOrFail($id);
        $p->en_banner = ! $p->en_banner;
        $p->save();

        Flux::toast(
            variant: 'success',
            text: $p->en_banner ? "«{$p->titulo}» añadida al carrusel principal." : "«{$p->titulo}» quitada del carrusel principal."
        );
    }

    public function crear(): void
    {
        $this->reset(['peliculaId', 'titulo', 'imagen', 'descripcion', 'director', 'genero', 'fecha_estreno', 'en_banner', 'orden_banner']);
        $this->duracion = 120;
        $this->clasificacion = 'PG-13';
        $this->idioma = 'Español Latino';
        $this->en_banner = false;
        $this->orden_banner = 0;
        $this->modalAbierto = true;
    }

    public function editar(int $id): void
    {
        $p = Pelicula::findOrFail($id);
        $this->peliculaId = $p->id;
        $this->titulo = $p->titulo;
        $this->imagen = $p->imagen ?? '';
        $this->descripcion = $p->descripcion ?? '';
        $this->director = $p->director ?? '';
        $this->genero = $p->genero ?? '';
        $this->fecha_estreno = $p->fecha_estreno ? $p->fecha_estreno->format('Y-m-d') : '';
        $this->duracion = (int) $p->duracion;
        $this->clasificacion = $p->clasificacion ?? 'PG-13';
        $this->idioma = $p->idioma ?? 'Español Latino';
        $this->en_banner = (bool) $p->en_banner;
        $this->orden_banner = (int) $p->orden_banner;
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $validated = $this->validate();
        $validated['fecha_estreno'] = $this->fecha_estreno ? $this->fecha_estreno : null;

        if ($this->peliculaId) {
            $p = Pelicula::findOrFail($this->peliculaId);
            $p->update($validated);
            Flux::toast(variant: 'success', text: 'Película actualizada.');
        } else {
            $validated['slug'] = Str::slug($this->titulo);
            Pelicula::create($validated);
            Flux::toast(variant: 'success', text: 'Película creada.');
        }

        $this->modalAbierto = false;
    }

    public function eliminar(int $id): void
    {
        $p = Pelicula::withCount('funciones')->findOrFail($id);
        if ($p->funciones_count > 0) {
            Flux::toast(variant: 'danger', text: "No se puede eliminar porque tiene {$p->funciones_count} funciones asignadas.");
            return;
        }

        $p->delete();
        Flux::toast(variant: 'success', text: 'Película eliminada.');
    }

    public function render()
    {
        $query = Pelicula::withCount('funciones')->orderBy('id', 'desc');

        if ($this->busqueda !== '') {
            $query->where('titulo', 'like', "%{$this->busqueda}%")
                ->orWhere('genero', 'like', "%{$this->busqueda}%");
        }

        return view('livewire.admin.peliculas', [
            'peliculas' => $query->paginate(8),
        ]);
    }
}
