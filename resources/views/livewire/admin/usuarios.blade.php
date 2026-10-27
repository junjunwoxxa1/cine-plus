<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Usuarios y Roles</h1>
            <p class="text-sm text-zinc-400 mt-1">Supervisa las cuentas de clientes y asigna privilegios de Administrador.</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="busqueda" icon="magnifying-glass" placeholder="Buscar por nombre o correo..." />
        </div>
        <div class="w-full sm:w-64">
            <select wire:model.live="filtroRol" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-200">
                <option value="">Todos los roles</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-zinc-950/60 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-800">
                    <tr>
                        <th class="px-6 py-4">Usuario</th>
                        <th class="px-6 py-4">Puntos Cine+</th>
                        <th class="px-6 py-4">Registrado el</th>
                        <th class="px-6 py-4">Rol Asignado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($usuarios as $u)
                        <tr class="hover:bg-zinc-800/30 transition-colors" wire:key="user-{{ $u->id }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-zinc-800 border border-zinc-700 flex items-center justify-center font-bold text-xs text-white shrink-0">
                                        {{ $u->initials() }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-white leading-tight whitespace-nowrap">
                                            {{ $u->name }}
                                            @if ($u->id === auth()->id())
                                                <span class="text-[10px] text-zinc-400 font-normal">(Tú)</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-zinc-400 mt-0.5">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-xs font-black text-amber-400">{{ $u->saldo_puntos ?? 0 }} pts</span>
                                        <span class="text-[10px] text-zinc-500 font-bold uppercase">(Total)</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-zinc-400">
                                        <span title="Puntos ganados por cine / entradas" class="flex items-center gap-0.5">
                                            🎟️ <strong class="text-zinc-200">{{ $u->puntos_cine ?? 0 }}</strong> cine
                                        </span>
                                        <span class="text-zinc-600">•</span>
                                        <span title="Puntos ganados por dulcería / snacks" class="flex items-center gap-0.5">
                                            🍿 <strong class="text-zinc-200">{{ $u->puntos_dulceria ?? 0 }}</strong> dulcería
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-zinc-400 whitespace-nowrap">
                                {{ $u->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <select
                                    wire:change="cambiarRol({{ $u->id }}, $event.target.value)"
                                    class="bg-zinc-950 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-zinc-200 focus:border-red-500 font-semibold cursor-pointer"
                                >
                                    @foreach ($roles as $r)
                                        <option value="{{ $r->id }}" @selected($u->role_id === $r->id)>
                                            {{ $r->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button
                                        size="sm"
                                        variant="subtle"
                                        wire:click="editarPuntos({{ $u->id }})"
                                        title="Gestionar puntos individuales (Cine vs Dulcería)"
                                    >
                                        🎁 Puntos
                                    </flux:button>

                                    @if ($u->id !== auth()->id())
                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            wire:click="eliminarUsuario({{ $u->id }})"
                                            wire:confirm="¿Seguro que deseas eliminar al usuario {{ $u->name }} ({{ $u->email }})?"
                                        >
                                            Eliminar
                                        </flux:button>
                                    @else
                                        <span class="text-xs text-zinc-500 italic">Sesión actual</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-zinc-500 italic">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-800">
            {{ $usuarios->links() }}
        </div>
    </div>

    <!-- Modal Gestión de Puntos Individuales -->
    <flux:modal wire:model="modalPuntosAbierto" class="max-w-md w-full space-y-5">
        <div>
            <flux:heading size="lg">Gestionar Puntos Individuales</flux:heading>
            <flux:subheading>Asigna y distribuye los puntos de <strong class="text-white">{{ $usuarioPuntosNombre }}</strong> entre entradas de cine y dulcería.</flux:subheading>
        </div>

        <form wire:submit="guardarPuntos" class="space-y-4">
            <flux:field>
                <flux:label>🎟️ Puntos Ganados por Cine (Entradas)</flux:label>
                <flux:input type="number" min="0" wire:model.live="puntos_cine_edit" required />
                <flux:description>Puntos canjeables por boletos y entradas de películas.</flux:description>
                <flux:error name="puntos_cine_edit" />
            </flux:field>

            <flux:field>
                <flux:label>🍿 Puntos Ganados por Dulcería (Snacks / Combos)</flux:label>
                <flux:input type="number" min="0" wire:model.live="puntos_dulceria_edit" required />
                <flux:description>Puntos canjeables por productos de confitería y bebidas.</flux:description>
                <flux:error name="puntos_dulceria_edit" />
            </flux:field>

            <div class="p-3 bg-zinc-950 border border-zinc-800 rounded-xl flex items-center justify-between text-xs">
                <span class="text-zinc-400 font-medium">⭐ Saldo Total Acumulado:</span>
                <span class="text-amber-400 font-black font-mono text-sm">
                    {{ (int) $puntos_cine_edit + (int) $puntos_dulceria_edit }} pts
                </span>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="subtle" wire:click="$set('modalPuntosAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled">Guardar Puntos</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
