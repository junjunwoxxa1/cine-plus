<?php

namespace App\Livewire\Admin;

use App\Models\MetodoPago;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Medios de Pago - Admin')]
class MetodosPago extends Component
{
    public $metodos;
    public bool $modalAbierto = false;

    public ?int $metodoId = null;
    public string $nombre = '';
    public string $codigo = '';
    public string $instrucciones = '';
    public bool $requiere_tarjeta = false;
    public int $orden = 0;
    public bool $activo = true;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'codigo' => 'required|string|max:50',
            'instrucciones' => 'nullable|string|max:500',
            'requiere_tarjeta' => 'boolean',
            'orden' => 'required|integer|min:0',
            'activo' => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->cargarMetodos();
    }

    public function cargarMetodos(): void
    {
        $this->metodos = MetodoPago::orderBy('orden')->orderBy('nombre')->get();
    }

    public function updatedNombre($val): void
    {
        if (!$this->metodoId && $this->codigo === '') {
            $this->codigo = Str::slug($val, '_');
        }
    }

    public function crear(): void
    {
        $this->reset(['metodoId', 'nombre', 'codigo', 'instrucciones', 'requiere_tarjeta', 'orden', 'activo']);
        $this->orden = $this->metodos->count();
        $this->activo = true;
        $this->modalAbierto = true;
    }

    public function editar(int $id): void
    {
        $m = MetodoPago::findOrFail($id);
        $this->metodoId = $m->id;
        $this->nombre = $m->nombre;
        $this->codigo = $m->codigo;
        $this->instrucciones = $m->instrucciones ?? '';
        $this->requiere_tarjeta = (bool) $m->requiere_tarjeta;
        $this->orden = (int) $m->orden;
        $this->activo = (bool) $m->activo;
        $this->modalAbierto = true;
    }

    public function toggleActivo(int $id): void
    {
        $m = MetodoPago::findOrFail($id);
        $m->activo = !$m->activo;
        $m->save();
        $this->cargarMetodos();
        Flux::toast(variant: 'success', text: $m->activo ? 'Método activado.' : 'Método desactivado.');
    }

    public function guardar(): void
    {
        $validated = $this->validate();

        if ($this->metodoId) {
            $m = MetodoPago::findOrFail($this->metodoId);
            $m->update($validated);
            Flux::toast(variant: 'success', text: 'Método de pago actualizado.');
        } else {
            MetodoPago::create($validated);
            Flux::toast(variant: 'success', text: 'Método de pago creado.');
        }

        $this->modalAbierto = false;
        $this->cargarMetodos();
    }

    public function eliminar(int $id): void
    {
        $m = MetodoPago::findOrFail($id);
        $m->delete();
        $this->cargarMetodos();
        Flux::toast(variant: 'success', text: 'Método eliminado.');
    }

    public function render()
    {
        return view('livewire.admin.metodos-pago');
    }
}
