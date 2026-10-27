<?php

namespace App\Livewire\Admin;

use App\Models\Funcion;
use App\Models\Sala;
use App\Models\Sede;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Gestión de Sedes - Admin')]
class Sedes extends Component
{
    public string $busqueda = '';

    // Modal Crear / Editar Sede
    public bool $modalAbierto = false;
    public ?int $sedeId = null;
    public string $nombre = '';
    public string $ciudad = 'Lima';
    public string $direccion = '';
    public string $telefono = '';
    public bool $activo = true;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150|unique:sedes,nombre,'.($this->sedeId ?? 'NULL'),
            'ciudad' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'activo' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la sede es obligatorio.',
            'nombre.unique' => 'Ya existe una sede registrada con este nombre.',
            'ciudad.required' => 'La ciudad es obligatoria.',
        ];
    }

    public function crear(): void
    {
        $this->reset(['sedeId', 'nombre', 'direccion', 'telefono']);
        $this->ciudad = 'Lima';
        $this->activo = true;
        $this->modalAbierto = true;
    }

    public function editar(int $id): void
    {
        $sede = Sede::findOrFail($id);
        $this->sedeId = $sede->id;
        $this->nombre = $sede->nombre;
        $this->ciudad = $sede->ciudad;
        $this->direccion = $sede->direccion ?? '';
        $this->telefono = $sede->telefono ?? '';
        $this->activo = (bool) $sede->activo;
        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $validated = $this->validate();

        if ($this->sedeId) {
            $sede = Sede::findOrFail($this->sedeId);
            $nombreAnterior = $sede->nombre;

            $sede->update($validated);

            // Si cambió el nombre de la sede, sincronizar las salas existentes
            if ($nombreAnterior !== $this->nombre) {
                Sala::where('sede', $nombreAnterior)->update(['sede' => $this->nombre]);
            }

            Flux::toast(variant: 'success', text: "Sede '{$sede->nombre}' actualizada exitosamente.");
        } else {
            $sede = Sede::create($validated);
            Flux::toast(variant: 'success', text: "Sede '{$sede->nombre}' creada correctamente y disponible para funciones y salas.");
        }

        $this->modalAbierto = false;
    }

    public function eliminar(int $id): void
    {
        $sede = Sede::findOrFail($id);
        $countSalas = Sala::where('sede', $sede->nombre)->count();

        if ($countSalas > 0) {
            Flux::toast(variant: 'danger', text: "No se puede eliminar la sede '{$sede->nombre}' porque tiene {$countSalas} sala(s) asociadas.");
            return;
        }

        $nombre = $sede->nombre;
        $sede->delete();

        Flux::toast(variant: 'success', text: "Sede '{$nombre}' eliminada.");
    }

    public function toggleActivo(int $id): void
    {
        $sede = Sede::findOrFail($id);
        $sede->activo = ! $sede->activo;
        $sede->save();

        $estado = $sede->activo ? 'activada' : 'desactivada';
        Flux::toast(variant: 'success', text: "Sede '{$sede->nombre}' {$estado}.");
    }

    public function render()
    {
        $query = Sede::query();

        if ($this->busqueda !== '') {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->busqueda}%")
                    ->orWhere('ciudad', 'like', "%{$this->busqueda}%")
                    ->orWhere('direccion', 'like', "%{$this->busqueda}%");
            });
        }

        $sedes = $query->orderBy('nombre')->get();

        return view('livewire.admin.sedes', [
            'sedes' => $sedes,
            'totalSedes' => Sede::count(),
            'totalSalas' => Sala::count(),
            'totalFunciones' => Funcion::count(),
        ]);
    }
}
