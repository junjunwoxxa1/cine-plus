<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Control de Inventario & Stock</h1>
            <p class="text-sm text-zinc-400 mt-1">Supervisa en tiempo real el stock de comidas, alertas de bajo inventario y reabastece productos.</p>
        </div>

        <a href="{{ route('pos.dulceria') }}" target="_blank"
            class="inline-flex items-center space-x-2 bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider py-2.5 px-4 rounded-xl shadow-lg shadow-red-900/30 transition-all">
            <span>🍿 Abrir POS Dulcería</span>
            <span>↗</span>
        </a>
    </div>

    {{-- Tarjetas KPI de Resumen de Inventario --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-zinc-400">Total Ítems</span>
            <p class="text-2xl font-black text-white">{{ $totalItems }}</p>
            <span class="text-[11px] text-zinc-500 block">En carta activa</span>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-green-400">Stock Saludable</span>
            <p class="text-2xl font-black text-green-400">{{ $saludablesCount }}</p>
            <span class="text-[11px] text-zinc-500 block">&gt; 10 unidades</span>
        </div>

        <div class="bg-zinc-900 border border-amber-500/30 rounded-2xl p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-amber-400">Stock Bajo</span>
            <p class="text-2xl font-black text-amber-400">{{ $bajoStockCount }}</p>
            <span class="text-[11px] text-zinc-500 block">1 a 10 unidades</span>
        </div>

        <div class="bg-zinc-900 border border-red-500/30 rounded-2xl p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-red-400">Agotados</span>
            <p class="text-2xl font-black text-red-500">{{ $agotadosCount }}</p>
            <span class="text-[11px] text-zinc-500 block">0 unidades (sin venta)</span>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 space-y-1 col-span-2 md:col-span-1">
            <span class="text-[10px] uppercase font-bold text-zinc-400">Valor Inventario</span>
            <p class="text-2xl font-black text-white">S/ {{ number_format($valorTotal, 2) }}</p>
            <span class="text-[11px] text-zinc-500 block">Valorizado a PVP</span>
        </div>
    </div>

    {{-- Barra de Filtros --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-zinc-900/60 border border-zinc-800 rounded-2xl p-4">
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto flex-1">
            <flux:input wire:model.live.debounce.250ms="busqueda" placeholder="Buscar por snack o combo..." class="w-full sm:w-64" />
            
            <flux:select wire:model.live="filtroCategoria" class="w-full sm:w-48">
                <option value="todas">Todas las Categorías</option>
                @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->icono ?? '🍿' }} {{ $cat->nombre }}</option>
                @endforeach
            </flux:select>
        </div>

        {{-- Filtro por Estado de Stock --}}
        <div class="flex gap-1.5 w-full sm:w-auto overflow-x-auto pb-1">
            <button type="button" wire:click="$set('filtroEstado', 'todos')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap border {{ $filtroEstado === 'todos' ? 'bg-zinc-700 border-zinc-600 text-white' : 'bg-zinc-900 border-zinc-800 text-zinc-400 hover:text-white' }}">
                Todos ({{ $totalItems }})
            </button>
            <button type="button" wire:click="$set('filtroEstado', 'saludable')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap border {{ $filtroEstado === 'saludable' ? 'bg-green-600 border-green-500 text-white' : 'bg-zinc-900 border-zinc-800 text-zinc-400 hover:text-white' }}">
                Saludables ({{ $saludablesCount }})
            </button>
            <button type="button" wire:click="$set('filtroEstado', 'bajo')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap border {{ $filtroEstado === 'bajo' ? 'bg-amber-600 border-amber-500 text-white' : 'bg-zinc-900 border-zinc-800 text-zinc-400 hover:text-white' }}">
                Bajo Stock ({{ $bajoStockCount }})
            </button>
            <button type="button" wire:click="$set('filtroEstado', 'agotado')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap border {{ $filtroEstado === 'agotado' ? 'bg-red-600 border-red-500 text-white' : 'bg-zinc-900 border-zinc-800 text-zinc-400 hover:text-white' }}">
                Agotados ({{ $agotadosCount }})
            </button>
        </div>
    </div>

    {{-- Tabla de Inventario --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-950/80 text-zinc-400 text-[11px] uppercase tracking-wider border-b border-zinc-800">
                    <tr>
                        <th class="p-4">Producto</th>
                        <th class="p-4">Categoría</th>
                        <th class="p-4">Precio Venta</th>
                        <th class="p-4">Stock Actual</th>
                        <th class="p-4">Valor Total</th>
                        <th class="p-4 text-right">Reabastecimiento Rápido</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 text-xs">
                    @forelse ($productos as $p)
                        @php
                            $agotado = $p->stock <= 0;
                            $bajo = $p->stock > 0 && $p->stock <= 10;
                        @endphp
                        <tr class="hover:bg-zinc-800/40 transition-colors {{ $agotado ? 'bg-red-950/10' : '' }}" wire:key="inv-prod-{{ $p->id }}">
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg bg-zinc-950 overflow-hidden flex-shrink-0 border border-zinc-800">
                                        <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}" class="w-full h-full object-cover {{ $agotado ? 'grayscale opacity-60' : '' }}">
                                    </div>
                                    <div>
                                        <strong class="text-white text-sm block">{{ $p->nombre }}</strong>
                                        <span class="text-zinc-500 text-[11px] block truncate max-w-xs">{{ $p->descripcion }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded text-[11px] font-bold">
                                    {{ $p->categoria->icono ?? '🍿' }} {{ $p->categoria->nombre ?? 'Snack' }}
                                </span>
                            </td>
                            <td class="p-4 font-mono font-bold text-white text-sm">
                                S/ {{ number_format($p->precio, 2) }}
                            </td>
                            <td class="p-4">
                                @if ($agotado)
                                    <span class="inline-flex items-center gap-1 bg-red-600/20 text-red-400 border border-red-500/40 font-black text-xs px-2.5 py-1 rounded-lg">
                                        <span>🚫</span>
                                        <span>0 unids (AGOTADO)</span>
                                    </span>
                                @elseif ($bajo)
                                    <span class="inline-flex items-center gap-1 bg-amber-500/20 text-amber-400 border border-amber-500/40 font-black text-xs px-2.5 py-1 rounded-lg">
                                        <span>⚠️</span>
                                        <span>{{ $p->stock }} unids (BAJO)</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-green-500/20 text-green-400 border border-green-500/40 font-black text-xs px-2.5 py-1 rounded-lg">
                                        <span>✓</span>
                                        <span>{{ $p->stock }} unids</span>
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 font-mono text-zinc-300">
                                S/ {{ number_format($p->precio * max(0, $p->stock), 2) }}
                            </td>
                            <td class="p-4 text-right space-x-1.5 whitespace-nowrap">
                                <button type="button" wire:click="sumarRapido({{ $p->id }}, 5)"
                                    class="bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 px-2.5 py-1 rounded-lg font-bold text-xs transition-colors cursor-pointer">
                                    +5
                                </button>
                                <button type="button" wire:click="sumarRapido({{ $p->id }}, 10)"
                                    class="bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 px-2.5 py-1 rounded-lg font-bold text-xs transition-colors cursor-pointer">
                                    +10
                                </button>
                                <flux:button size="sm" variant="primary" wire:click="abrirAjuste({{ $p->id }})">
                                    Ajustar Stock
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-zinc-500 italic">
                                No se encontraron productos con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal de Ajuste de Stock --}}
    <flux:modal wire:model="modalStockAbierto" class="space-y-6">
        <div>
            <flux:heading size="lg">Ajuste de Stock: {{ $productoSeleccionadoNombre }}</flux:heading>
            <flux:subheading>Actualiza el inventario físico para sincronizar con la venta online y en mostrador.</flux:subheading>
        </div>

        <form wire:submit="guardarAjuste" class="space-y-4">
            <div class="bg-zinc-950 border border-zinc-800 rounded-xl p-3.5 flex items-center justify-between text-xs">
                <span class="text-zinc-400">Stock Actual en Base de Datos:</span>
                <span class="font-mono text-base font-black text-white">{{ $stockActual }} unidades</span>
            </div>

            <flux:field>
                <flux:label>Tipo de Ajuste</flux:label>
                <flux:select wire:model="tipoAjuste">
                    <option value="sumar">Sumar existencias al stock actual (+)</option>
                    <option value="fijar">Fijar cantidad exacta (conteo físico directo)</option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Cantidad de Unidades</flux:label>
                <flux:input type="number" wire:model="cantidadAjuste" min="0" max="10000" required />
                <flux:description>
                    {{ $tipoAjuste === 'sumar' ? 'Se sumará al stock actual. Nuevo stock estimado: ' . ($stockActual + (int) $cantidadAjuste) : 'El nuevo stock quedará fijado en ' . (int) $cantidadAjuste . ' unidades.' }}
                </flux:description>
            </flux:field>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('modalStockAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Actualizar Inventario</flux:button>
            </div>
        </form>
    </flux:modal>

</div>

