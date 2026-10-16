<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Categorías de Comida</h1>
            <p class="text-sm text-zinc-400 mt-1">Organiza las comidas y snacks que se muestran en la dulcería.</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="crear">
            Nueva Categoría
        </flux:button>
    </div>

    <!-- Lista de Categorías -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-zinc-950/60 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-800">
                    <tr>
                        <th class="px-6 py-4">Orden</th>
                        <th class="px-6 py-4">Ícono</th>
                        <th class="px-6 py-4">Nombre</th>
                        <th class="px-6 py-4">Productos</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($categorias as $cat)
                        <tr class="hover:bg-zinc-800/30 transition-colors" wire:key="cat-{{ $cat->id }}">
                            <td class="px-6 py-4 font-mono text-xs text-zinc-500">{{ $cat->orden }}</td>
                            <td class="px-6 py-4 text-xl">{{ $cat->icono ?? '🍿' }}</td>
                            <td class="px-6 py-4 font-bold text-white whitespace-nowrap">{{ $cat->nombre }}</td>
                            <td class="px-6 py-4 text-zinc-400 whitespace-nowrap">{{ $cat->productos_count }} producto(s)</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($cat->activo)
                                    <span class="bg-green-500/10 text-green-400 border border-green-500/30 text-xs px-2.5 py-1 rounded-full font-bold">Activo</span>
                                @else
                                    <span class="bg-zinc-800 text-zinc-400 text-xs px-2.5 py-1 rounded-full font-bold">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <flux:button size="sm" variant="subtle" wire:click="editar({{ $cat->id }})">
                                    Editar
                                </flux:button>
                                <flux:button size="sm" variant="danger" wire:click="eliminar({{ $cat->id }})" wire:confirm="¿Seguro que deseas eliminar esta categoría?">
                                    Eliminar
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-500 italic">No hay categorías registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Formulario -->
    <flux:modal wire:model="modalAbierto" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $categoriaId ? 'Editar Categoría' : 'Nueva Categoría' }}</flux:heading>
            <flux:subheading>Define el nombre, emoji identificador y orden en el menú.</flux:subheading>
        </div>

        <form wire:submit="guardar" class="space-y-4">
            <flux:field>
                <flux:label>Nombre</flux:label>
                <flux:input wire:model="nombre" placeholder="Ej. Combos Especiales" required />
                <flux:error name="nombre" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Ícono / Emoji</flux:label>
                    <flux:input wire:model="icono" placeholder="🍿, 🥤, 🌭" />
                    <flux:error name="icono" />
                </flux:field>

                <flux:field>
                    <flux:label>Orden de visualización</flux:label>
                    <flux:input type="number" wire:model="orden" min="0" />
                    <flux:error name="orden" />
                </flux:field>
            </div>

            <flux:field>
                <flux:checkbox wire:model="activo" label="Categoría visible en la tienda / dulcería" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('modalAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
