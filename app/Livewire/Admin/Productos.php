<?php

namespace App\Livewire\Admin;

use App\Models\CategoriaProducto;
use App\Models\Producto;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Productos de Dulcería - Admin')]
class Productos extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $filtroCategoria = '';
    public bool $modalAbierto = false;

    public ?int $productoId = null;
    public ?int $categoria_producto_id = null;
    public string $nombre = '';
    public string $descripcion = '';
    public string $imagen = '';
    public $precio = 0.00;
    public int $precio_puntos = 0;
    public int $stock = 50;
    public bool $activo = true;

    protected function rules(): array
    {
        return [
            'categoria_producto_id' => 'required|exists:categoria_producto,id',
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:500',
            'imagen' => 'nullable|string|max:255',
            'precio' => 'required|numeric|min:0',
            'precio_puntos' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'activo' => 'boolean',
        ];
    }

    public function updatedPrecio($val): void
    {
        if (is_numeric($val) && (int)$this->precio_puntos === 0) {
            $this->precio_puntos = (int) round((float)$val * 10);
        }
    }

    public function crear(): void
    {
        $this->reset(['productoId', 'nombre', 'descripcion', 'imagen', 'precio', 'precio_puntos', 'stock', 'activo']);
        $primeraCat = CategoriaProducto::first();
        $this->categoria_producto_id = $primeraCat ? $primeraCat->id : null;
        $this->stock = 50;
        $this->activo = true;
        $this->modalAbierto = true;
    }

    public function editar(int $id): void
    {
        $prod = Producto::findOrFail($id);
        $this->productoId = $prod->id;
        $this->categoria_producto_id = $prod->categoria_producto_id;
        $this->nombre = $prod->nombre;
        $this->descripcion = $prod->descripcion ?? '';
        $this->imagen = $prod->imagen ?? '';
        $this->precio = (float) $prod->precio;
        $this->precio_puntos = (int) $prod->precio_puntos;
        $this->stock = (int) $prod->stock;
        $this->activo = (bool) $prod->activo;
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $validated = $this->validate();

        if ($this->productoId) {
            $prod = Producto::findOrFail($this->productoId);
            $prod->update($validated);
            Flux::toast(variant: 'success', text: 'Producto actualizado con éxito.');
        } else {
            Producto::create($validated);
            Flux::toast(variant: 'success', text: 'Producto creado con éxito.');
        }

        $this->modalAbierto = false;
    }

    public function eliminar(int $id): void
    {
        $prod = Producto::findOrFail($id);
        $prod->delete();
        Flux::toast(variant: 'success', text: 'Producto eliminado.');
    }

    public function toggleActivo(int $id): void
    {
        $prod = Producto::findOrFail($id);
        $prod->activo = !$prod->activo;
        $prod->save();
        Flux::toast(variant: 'success', text: $prod->activo ? 'Producto activado' : 'Producto desactivado');
    }

    public function render()
    {
        $query = Producto::with('categoria')->orderBy('id', 'desc');

        if ($this->busqueda !== '') {
            $query->where('nombre', 'like', "%{$this->busqueda}%");
        }

        if ($this->filtroCategoria !== '') {
            $query->where('categoria_producto_id', $this->filtroCategoria);
        }

        return view('livewire.admin.productos', [
            'productos' => $query->paginate(10),
            'categorias' => CategoriaProducto::orderBy('orden')->get(),
        ]);
    }
}
