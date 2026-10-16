<?php

namespace App\Livewire\Admin;

use App\Models\CategoriaProducto;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Categorías de Comida - Admin')]
class Categorias extends Component
{
    public $categorias;
    public ?int $categoriaId = null;
    public string $nombre = '';
    public string $icono = '🍿';
    public int $orden = 0;
    public bool $activo = true;
    public bool $modalAbierto = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'icono' => 'nullable|string|max:20',
            'orden' => 'required|integer|min:0',
            'activo' => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->cargarCategorias();
    }

    public function cargarCategorias(): void
    {
        $this->categorias = CategoriaProducto::withCount('productos')->orderBy('orden')->orderBy('nombre')->get();
    }

    public function crear(): void
    {
        $this->reset(['categoriaId', 'nombre', 'icono', 'orden', 'activo']);
        $this->icono = '🍿';
        $this->orden = $this->categorias->count();
        $this->activo = true;
        $this->modalAbierto = true;
    }

    public function editar(int $id): void
    {
        $cat = CategoriaProducto::findOrFail($id);
        $this->categoriaId = $cat->id;
        $this->nombre = $cat->nombre;
        $this->icono = $cat->icono ?? '🍿';
        $this->orden = $cat->orden;
        $this->activo = (bool) $cat->activo;
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $validated = $this->validate();

        if ($this->categoriaId) {
            $cat = CategoriaProducto::findOrFail($this->categoriaId);
            $cat->update([
                'nombre' => $validated['nombre'],
                'icono' => $validated['icono'],
                'orden' => $validated['orden'],
                'activo' => $validated['activo'],
            ]);
            Flux::toast(variant: 'success', text: 'Categoría actualizada exitosamente.');
        } else {
            CategoriaProducto::create([
                'nombre' => $validated['nombre'],
                'slug' => Str::slug($validated['nombre']),
                'icono' => $validated['icono'],
                'orden' => $validated['orden'],
                'activo' => $validated['activo'],
            ]);
            Flux::toast(variant: 'success', text: 'Categoría creada exitosamente.');
        }

        $this->modalAbierto = false;
        $this->cargarCategorias();
    }

    public function eliminar(int $id): void
    {
        $cat = CategoriaProducto::withCount('productos')->findOrFail($id);

        if ($cat->productos_count > 0) {
            Flux::toast(variant: 'danger', text: "No se puede eliminar la categoría porque tiene {$cat->productos_count} productos asociados.");
            return;
        }

        $cat->delete();
        Flux::toast(variant: 'success', text: 'Categoría eliminada.');
        $this->cargarCategorias();
    }

    public function render()
    {
        return view('livewire.admin.categorias');
    }
}
