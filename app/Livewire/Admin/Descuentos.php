<?php

namespace App\Livewire\Admin;

use App\Enums\TipoDescuento;
use App\Models\Descuento;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Promociones y Descuentos - Admin')]
class Descuentos extends Component
{
    public $descuentos;
    public bool $modalAbierto = false;

    public ?int $descuentoId = null;
    public string $nombre = '';
    public string $descripcion = '';
    public string $codigo = '';
    public string $tipo = 'monto_por_entrada';
    public $valor = 4.00;
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public bool $activo = true;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'codigo' => 'required|string|max:50',
            'tipo' => 'required|in:porcentaje,monto_por_entrada,monto_total',
            'valor' => 'required|numeric|min:0.01',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'activo' => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->cargarDescuentos();
    }

    public function cargarDescuentos(): void
    {
        $this->descuentos = Descuento::orderBy('id', 'desc')->get();
    }

    public function crear(): void
    {
        $this->reset(['descuentoId', 'nombre', 'descripcion', 'codigo', 'fecha_inicio', 'fecha_fin']);
        $this->tipo = 'monto_por_entrada';
        $this->valor = 4.00;
        $this->activo = true;
        $this->modalAbierto = true;
    }

    public function editar(int $id): void
    {
        $d = Descuento::findOrFail($id);
        $this->descuentoId = $d->id;
        $this->nombre = $d->nombre;
        $this->descripcion = $d->descripcion ?? '';
        $this->codigo = $d->codigo ?? '';
        $this->tipo = $d->tipo->value;
        $this->valor = (float) $d->valor;
        $this->fecha_inicio = $d->fecha_inicio ? $d->fecha_inicio->format('Y-m-d') : '';
        $this->fecha_fin = $d->fecha_fin ? $d->fecha_fin->format('Y-m-d') : '';
        $this->activo = (bool) $d->activo;
        $this->modalAbierto = true;
    }

    public function toggleActivo(int $id): void
    {
        $d = Descuento::findOrFail($id);
        $d->activo = !$d->activo;
        $d->save();
        $this->cargarDescuentos();
        Flux::toast(variant: 'success', text: $d->activo ? 'Promoción activada' : 'Promoción desactivada');
    }

    public function guardar(): void
    {
        $validated = $this->validate();
        $validated['codigo'] = !empty($this->codigo) ? strtoupper(trim($this->codigo)) : null;
        $validated['fecha_inicio'] = !empty($this->fecha_inicio) ? $this->fecha_inicio : null;
        $validated['fecha_fin'] = !empty($this->fecha_fin) ? $this->fecha_fin : null;

        if ($this->descuentoId) {
            $d = Descuento::findOrFail($this->descuentoId);
            $d->update($validated);
            Flux::toast(variant: 'success', text: 'Promoción actualizada.');
        } else {
            Descuento::create($validated);
            Flux::toast(variant: 'success', text: 'Promoción creada.');
        }

        $this->modalAbierto = false;
        $this->cargarDescuentos();
    }

    public function togglePuntosSeparados(): void
    {
        $nuevoEstado = \App\Models\Configuracion::togglePuntosSeparados();
        if ($nuevoEstado) {
            Flux::toast(variant: 'success', text: 'Modalidad de Puntos Separados (Cine vs. Dulcería) ACTIVADA a nivel general.');
        } else {
            Flux::toast(variant: 'warning', text: 'Modalidad de Puntos Separados DESACTIVADA. Sistema operando en Modo Unificado estándar.');
        }
    }

    public function eliminar(int $id): void
    {
        $d = Descuento::findOrFail($id);
        $d->delete();
        $this->cargarDescuentos();
        Flux::toast(variant: 'success', text: 'Promoción eliminada.');
    }

    public function render()
    {
        return view('livewire.admin.descuentos', [
            'tipos' => TipoDescuento::cases(),
            'puntosSeparadosActivo' => \App\Models\Configuracion::puntosSeparadosActivo(),
        ]);
    }
}
