<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Usuarios y Roles - Admin')]
class Usuarios extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $filtroRol = '';

    public function cambiarRol(int $userId, int $roleId): void
    {
        $usuario = User::findOrFail($userId);

        if ($usuario->id === Auth::id() && $usuario->isAdmin() && $roleId !== $usuario->role_id) {
            Flux::toast(variant: 'danger', text: 'No puedes quitarte el rol de Administrador a ti mismo.');
            return;
        }

        $usuario->role_id = $roleId;
        $usuario->save();

        Flux::toast(variant: 'success', text: "Rol de {$usuario->name} actualizado exitosamente.");
    }

    public function eliminarUsuario(int $userId): void
    {
        $usuario = User::findOrFail($userId);

        if ($usuario->id === Auth::id()) {
            Flux::toast(variant: 'danger', text: 'No puedes eliminar tu propia cuenta de administrador.');

            return;
        }

        $nombre = $usuario->name;
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $usuario->email)->delete();
        $usuario->delete();

        Flux::toast(variant: 'success', text: "Usuario {$nombre} eliminado correctamente.");
    }

    public function render()
    {
        $query = User::with('role')->orderBy('id', 'desc');

        if ($this->busqueda !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->busqueda}%")
                    ->orWhere('email', 'like', "%{$this->busqueda}%");
            });
        }

        if ($this->filtroRol !== '') {
            $query->where('role_id', $this->filtroRol);
        }

        return view('livewire.admin.usuarios', [
            'usuarios' => $query->paginate(12),
            'roles' => Role::all(),
        ]);
    }
}
