<div class="space-y-6">
    {{-- Header Principal --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-2xl">🏢</span>
                <h1 class="text-2xl font-black text-white tracking-tight">Sedes y Complejos de Cine+</h1>
            </div>
            <p class="text-sm text-zinc-400 mt-1">Registra y administra las sedes de cine que estarán disponibles al programar funciones y crear salas.</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="crear">
            Nueva Sede
        </flux:button>
    </div>

    {{-- Métricas Rápidas --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Total Sedes</span>
            <p class="text-2xl font-black text-white mt-1">{{ $totalSedes }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Sedes Activas</span>
            <p class="text-2xl font-black text-emerald-400 mt-1">{{ $sedes->where('activo', true)->count() }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Total Salas</span>
            <p class="text-2xl font-black text-white mt-1">{{ $totalSalas }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Total Funciones</span>
            <p class="text-2xl font-black text-amber-400 mt-1">{{ $totalFunciones }}</p>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.250ms="busqueda" icon="magnifying-glass" placeholder="Buscar sede por nombre, dirección o ciudad..." />
        </div>
    </div>

    {{-- Tabla de Sedes --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-zinc-950/60 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-800">
                    <tr>
                        <th class="px-6 py-4">Sede / Complejo</th>
                        <th class="px-6 py-4">Ciudad</th>
                        <th class="px-6 py-4">Dirección</th>
                        <th class="px-6 py-4">Salas</th>
                        <th class="px-6 py-4">Funciones</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($sedes as $s)
                        @php
                            $countSalas = \App\Models\Sala::where('sede', $s->nombre)->count();
                            $countFunciones = \App\Models\Funcion::whereHas('sala', fn ($q) => $q->where('sede', $s->nombre))->count();
                        @endphp
                        <tr class="hover:bg-zinc-800/30 transition-colors" wire:key="sede-row-{{ $s->id }}">
                            <td class="px-6 py-4">
                                <div class="space-y-0.5">
                                    <p class="font-bold text-white text-sm flex items-center gap-1.5">
                                        <span>📍</span>
                                        <span>{{ $s->nombre }}</span>
                                    </p>
                                    @if ($s->telefono)
                                        <p class="text-[11px] text-zinc-500 font-mono">📞 {{ $s->telefono }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-zinc-300 text-xs">
                                {{ $s->ciudad }}
                            </td>
                            <td class="px-6 py-4 text-zinc-400 text-xs max-w-xs">
                                {{ $s->direccion ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-zinc-950 border border-zinc-800 px-2.5 py-1 rounded-lg text-xs font-mono font-bold text-zinc-200">
                                    {{ $countSalas }} {{ Str::plural('sala', $countSalas) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-zinc-950 border border-zinc-800 px-2.5 py-1 rounded-lg text-xs font-mono font-bold text-amber-400">
                                    {{ $countFunciones }} {{ Str::plural('función', $countFunciones) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    type="button"
                                    wire:click="toggleActivo({{ $s->id }})"
                                    class="cursor-pointer transition-transform active:scale-95"
                                    title="Clic para cambiar estado"
                                >
                                    @if ($s->activo)
                                        <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[10px] font-black uppercase px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Activa
                                        </span>
                                    @else
                                        <span class="bg-zinc-800 text-zinc-400 border border-zinc-700 text-[10px] font-black uppercase px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span>
                                            Inactiva
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" variant="subtle" wire:click="editar({{ $s->id }})">
                                        Editar
                                    </flux:button>
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        wire:click="eliminar({{ $s->id }})"
                                        wire:confirm="¿Deseas eliminar la sede '{{ $s->nombre }}'?"
                                    >
                                        Eliminar
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500 text-xs italic">
                                No se encontraron sedes. Haz clic en <strong>"Nueva Sede"</strong> para agregar una.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL CREAR / EDITAR SEDE --}}
    <flux:modal wire:model="modalAbierto" class="max-w-md w-full space-y-5">
        <div>
            <flux:heading size="lg">{{ $sedeId ? 'Editar Sede' : 'Nueva Sede' }}</flux:heading>
            <flux:subheading>Esta sede estará inmediatamente conectada y disponible al programar funciones y crear salas.</flux:subheading>
        </div>

        <form wire:submit="guardar" class="space-y-4">
            <flux:field>
                <flux:label>Nombre de la Sede</flux:label>
                <flux:input type="text" wire:model="nombre" placeholder="Ej: Cine+ Salaverry (Jesús María)" required />
                <flux:description>El nombre exacto de la sede como aparecerá en cartelera y tickets.</flux:description>
                <flux:error name="nombre" />
            </flux:field>

            <flux:field>
                <flux:label>Ciudad</flux:label>
                <flux:input type="text" wire:model="ciudad" placeholder="Ej: Lima" required />
                <flux:error name="ciudad" />
            </flux:field>

            <flux:field>
                <flux:label>Dirección</flux:label>
                <flux:input type="text" wire:model="direccion" placeholder="Ej: Av. Salaverry 2370, C.C. Real Plaza" />
                <flux:error name="direccion" />
            </flux:field>

            <flux:field>
                <flux:label>Teléfono de Contacto</flux:label>
                <flux:input type="text" wire:model="telefono" placeholder="Ej: (01) 620-3000" />
                <flux:error name="telefono" />
            </flux:field>

            <flux:field>
                <flux:checkbox wire:model="activo" label="Sede activa para funciones y venta al público" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="subtle" wire:click="$set('modalAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit">
                    {{ $sedeId ? 'Guardar Cambios' : 'Crear Sede' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
