<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Comidas y Snacks</h1>
            <p class="text-sm text-zinc-400 mt-1">Administra los productos de dulcería, precios, puntos y stock.</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="crear">
            Nuevo Producto
        </flux:button>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="busqueda" icon="magnifying-glass" placeholder="Buscar por nombre..." />
        </div>
        <div class="w-full sm:w-64">
            <select wire:model.live="filtroCategoria" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-3 py-2 text-sm text-zinc-200 focus:outline-none focus:border-red-500">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->icono }} {{ $cat->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-zinc-950/60 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-800">
                    <tr>
                        <th class="px-6 py-4">Producto</th>
                        <th class="px-6 py-4">Categoría</th>
                        <th class="px-6 py-4">Precio (S/)</th>
                        <th class="px-6 py-4">Puntos</th>
                        <th class="px-6 py-4">Stock</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($productos as $prod)
                        <tr class="hover:bg-zinc-800/30 transition-colors" wire:key="prod-{{ $prod->id }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre }}" class="w-12 h-12 object-cover rounded-lg border border-zinc-800 bg-zinc-950 shrink-0">
                                    <div>
                                        <p class="font-bold text-white leading-tight">{{ $prod->nombre }}</p>
                                        <p class="text-xs text-zinc-400 mt-0.5 line-clamp-1">{{ $prod->descripcion }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-zinc-300 whitespace-nowrap">
                                {{ $prod->categoria?->icono }} {{ $prod->categoria?->nombre ?? 'Sin categoría' }}
                            </td>
                            <td class="px-6 py-4 font-black text-white whitespace-nowrap">S/ {{ number_format($prod->precio, 2) }}</td>
                            <td class="px-6 py-4 text-xs text-amber-400 font-bold whitespace-nowrap">{{ $prod->precio_puntos }} pts</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($prod->stock > 10)
                                    <span class="text-xs font-bold text-zinc-300">{{ $prod->stock }} un.</span>
                                @elseif ($prod->stock > 0)
                                    <span class="text-xs font-bold text-amber-400">{{ $prod->stock }} un. (Bajo)</span>
                                @else
                                    <span class="text-xs font-bold text-red-500">Agotado</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button type="button" wire:click="toggleActivo({{ $prod->id }})" class="cursor-pointer">
                                    @if ($prod->activo)
                                        <span class="bg-green-500/10 text-green-400 border border-green-500/30 text-xs px-2.5 py-1 rounded-full font-bold">Activo</span>
                                    @else
                                        <span class="bg-zinc-800 text-zinc-400 text-xs px-2.5 py-1 rounded-full font-bold">Inactivo</span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <flux:button size="sm" variant="subtle" wire:click="editar({{ $prod->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="danger" wire:click="eliminar({{ $prod->id }})" wire:confirm="¿Seguro que deseas eliminar este producto?">Eliminar</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500 italic">No se encontraron productos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-800">
            {{ $productos->links() }}
        </div>
    </div>

    <!-- Modal Formulario -->
    <flux:modal wire:model="modalAbierto" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $productoId ? 'Editar Producto' : 'Nuevo Producto' }}</flux:heading>
            <flux:subheading>Configura el nombre, categoría, precio y stock de la dulcería.</flux:subheading>
        </div>

        <form wire:submit="guardar" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Categoría</flux:label>
                    <select wire:model="categoria_producto_id" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg p-2.5 text-sm text-zinc-200 focus:border-red-500" required>
                        <option value="">Selecciona categoría</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->icono }} {{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    <flux:error name="categoria_producto_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Nombre del Producto</flux:label>
                    <flux:input wire:model="nombre" placeholder="Ej. Canchita Gigante Dulce" required />
                    <flux:error name="nombre" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Descripción / Contenido</flux:label>
                <flux:textarea wire:model="descripcion" placeholder="Ej. 1 canchita gigante + 2 gaseosas de 32oz" rows="2" />
                <flux:error name="descripcion" />
            </flux:field>

            <flux:field>
                <flux:label>URL de Imagen o path (opcional)</flux:label>
                <flux:input wire:model="imagen" placeholder="https://... o dulceria/mi-foto.jpg" />
                <flux:description>Si se deja vacío, tomará una foto representativa según categoría.</flux:description>
                <flux:error name="imagen" />
            </flux:field>

            <div class="grid grid-cols-3 gap-3">
                <flux:field>
                    <flux:label>Precio (S/)</flux:label>
                    <flux:input type="number" step="0.50" wire:model.live="precio" required />
                    <flux:error name="precio" />
                </flux:field>

                <flux:field>
                    <flux:label>Puntos Cine+</flux:label>
                    <flux:input type="number" wire:model="precio_puntos" required />
                    <flux:error name="precio_puntos" />
                </flux:field>

                <flux:field>
                    <flux:label>Stock Inicial</flux:label>
                    <flux:input type="number" wire:model="stock" required />
                    <flux:error name="stock" />
                </flux:field>
            </div>

            <flux:field>
                <flux:checkbox wire:model="activo" label="Producto disponible para la venta" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('modalAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Guardar Producto</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
