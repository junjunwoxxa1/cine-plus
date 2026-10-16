<?php

namespace App\Livewire\Admin;

use App\Models\CategoriaProducto;
use App\Models\Producto;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Control de Inventario - Admin')]
class Inventario extends Component
{
    public string $busqueda = '';
    public string $filtroCategoria = 'todas';
    public string $filtroEstado = 'todos'; // todos, saludable, bajo, agotado

    // Modal para Ajuste o Reabastecimiento de Stock
    public bool $modalStockAbierto = false;
    public ?int $productoSeleccionadoId = null;
    public string $productoSeleccionadoNombre = '';
    public int $stockActual = 0;
    public int $cantidadAjuste = 10;
    public string $tipoAjuste = 'sumar'; // 'sumar' o 'fijar'

    public function abrirAjuste(int $id): void
    {
        $prod = Producto::findOrFail($id);
        $this->productoSeleccionadoId = $prod->id;
        $this->productoSeleccionadoNombre = $prod->nombre;
        $this->stockActual = (int) $prod->stock;
        $this->cantidadAjuste = 10;
        $this->tipoAjuste = 'sumar';
        $this->modalStockAbierto = true;
    }

    public function guardarAjuste(): void
    {
        if (! $this->productoSeleccionadoId) {
            return;
        }

        $prod = Producto::findOrFail($this->productoSeleccionadoId);

        if ($this->tipoAjuste === 'sumar') {
            $nuevoStock = max(0, $prod->stock + $this->cantidadAjuste);
        } else {
            $nuevoStock = max(0, $this->cantidadAjuste);
        }

        $prod->update(['stock' => $nuevoStock]);

        $this->modalStockAbierto = false;
        Flux::toast('Stock actualizado correctamente para ' . $prod->nombre, variant: 'success');
    }

    public function sumarRapido(int $id, int $cantidad): void
    {
        $prod = Producto::findOrFail($id);
        $nuevo = max(0, $prod->stock + $cantidad);
        $prod->update(['stock' => $nuevo]);

        Flux::toast("Se agregaron +{$cantidad} unidades a {$prod->nombre}", variant: 'success');
    }

    public function render()
    {
        $categorias = CategoriaProducto::orderBy('nombre')->get();

        $query = Producto::with('categoria');

        if ($this->filtroCategoria !== 'todas') {
            $query->where('categoria_producto_id', $this->filtroCategoria);
        }

        if ($this->busqueda !== '') {
            $t = '%' . $this->busqueda . '%';
            $query->where(function ($q) use ($t) {
                $q->where('nombre', 'like', $t)
                    ->orWhere('descripcion', 'like', $t);
            });
        }

        if ($this->filtroEstado === 'agotado') {
            $query->where('stock', '<=', 0);
        } elseif ($this->filtroEstado === 'bajo') {
            $query->where('stock', '>', 0)->where('stock', '<=', 10);
        } elseif ($this->filtroEstado === 'saludable') {
            $query->where('stock', '>', 10);
        }

        $productos = $query->orderBy('stock')->orderBy('nombre')->get();

        // Métricas Globales de Inventario
        $todos = Producto::all();
        $totalItems = $todos->count();
        $agotadosCount = $todos->where('stock', '<=', 0)->count();
        $bajoStockCount = $todos->where('stock', '>', 0)->where('stock', '<=', 10)->count();
        $saludablesCount = $todos->where('stock', '>', 10)->count();
        $valorTotal = $todos->sum(fn ($p) => $p->precio * max(0, $p->stock));

        return view('livewire.admin.inventario', compact(
            'productos',
            'categorias',
            'totalItems',
            'agotadosCount',
            'bajoStockCount',
            'saludablesCount',
            'valorTotal'
        ));
    }
}

