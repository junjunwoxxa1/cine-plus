<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Medios de Pago</h1>
            <p class="text-sm text-zinc-400 mt-1">Activa o añade métodos de pago (Tarjetas, Yape, Plin, Efectivo, etc.) disponibles en el checkout.</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="crear">
            Nuevo Medio de Pago
        </flux:button>
    </div>

    <!-- Lista -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-zinc-950/60 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-800">
                    <tr>
                        <th class="px-6 py-4">Orden</th>
                        <th class="px-6 py-4">Nombre</th>
                        <th class="px-6 py-4">Código interno</th>
                        <th class="px-6 py-4">Requiere Tarjeta</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($metodos as $m)
                        <tr class="hover:bg-zinc-800/30 transition-colors" wire:key="mp-{{ $m->id }}">
                            <td class="px-6 py-4 font-mono text-xs text-zinc-500">{{ $m->orden }}</td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-white whitespace-nowrap">{{ $m->nombre }}</p>
                                @if ($m->instrucciones)
                                    <p class="text-xs text-zinc-400 mt-0.5 line-clamp-1">{{ $m->instrucciones }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-zinc-400 whitespace-nowrap">{{ $m->codigo }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($m->requiere_tarjeta)
                                    <span class="text-xs text-indigo-400 font-bold bg-indigo-500/10 border border-indigo-500/20 px-2 py-0.5 rounded">Formulario Tarjeta</span>
                                @else
                                    <span class="text-xs text-zinc-400">Instrucción / QR</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button type="button" wire:click="toggleActivo({{ $m->id }})" class="cursor-pointer">
                                    @if ($m->activo)
                                        <span class="bg-green-500/10 text-green-400 border border-green-500/30 text-xs px-2.5 py-1 rounded-full font-bold">Activo</span>
                                    @else
                                        <span class="bg-zinc-800 text-zinc-400 text-xs px-2.5 py-1 rounded-full font-bold">Inactivo</span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <flux:button size="sm" variant="subtle" wire:click="editar({{ $m->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="danger" wire:click="eliminar({{ $m->id }})" wire:confirm="¿Seguro que deseas eliminar este medio de pago?">Eliminar</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-500 italic">No hay medios de pago registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Formulario -->
    <flux:modal wire:model="modalAbierto" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $metodoId ? 'Editar Medio de Pago' : 'Nuevo Medio de Pago' }}</flux:heading>
            <flux:subheading>Define el nombre, si pide tarjeta o genera QR, y sus instrucciones.</flux:subheading>
        </div>

        <form wire:submit="guardar" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Nombre</flux:label>
                    <flux:input wire:model.live="nombre" placeholder="Ej. Yape / Plin / Billetera" required />
                    <flux:error name="nombre" />
                </flux:field>

                <flux:field>
                    <flux:label>Código Interno</flux:label>
                    <flux:input wire:model="codigo" placeholder="ej. yape" required />
                    <flux:error name="codigo" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Instrucciones para el cliente</flux:label>
                <flux:textarea wire:model="instrucciones" placeholder="Instrucción mostrada cuando el usuario selecciona este método" rows="2" />
                <flux:error name="instrucciones" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Orden de visualización</flux:label>
                    <flux:input type="number" wire:model="orden" min="0" />
                    <flux:error name="orden" />
                </flux:field>

                <div class="space-y-2 pt-6">
                    <flux:checkbox wire:model="requiere_tarjeta" label="Pide Número, Fecha y CVC" />
                    <flux:checkbox wire:model="activo" label="Habilitado en el checkout" />
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('modalAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
