<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Promociones y Descuentos</h1>
            <p class="text-sm text-zinc-400 mt-1">Crea descuentos aplicables en el pago o cupones con código promocional.</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="crear">
            Nueva Promoción
        </flux:button>
    </div>

    <!-- Modalidad General de Puntos Separados (Cine vs. Dulcería) -->
    <div class="bg-gradient-to-r from-zinc-900 via-zinc-900 to-zinc-950 border {{ $puntosSeparadosActivo ? 'border-amber-500/40' : 'border-zinc-800' }} rounded-2xl p-5 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xl">⭐</span>
                    <h2 class="text-base font-bold text-white">Modalidad General del Club Cine+</h2>
                    @if ($puntosSeparadosActivo)
                        <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full">
                            ● PUNTOS SEPARADOS ACTIVADO
                        </span>
                    @else
                        <span class="bg-zinc-800 text-zinc-400 border border-zinc-700 text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full">
                            ● MODO UNIFICADO ESTÁNDAR
                        </span>
                    @endif
                </div>
                <p class="text-xs text-zinc-400 leading-relaxed">
                    @if ($puntosSeparadosActivo)
                        <strong>Modo Separado Activo a Nivel General:</strong> Los clientes acumulan y canjean de forma independiente sus puntos de <span class="text-amber-300">🎟️ Cine</span> (para entradas) y <span class="text-amber-300">🍿 Dulcería</span> (para combos y confitería).
                    @else
                        <strong>Modo Unificado Estándar:</strong> Todos los puntos de los clientes se acumulan en un único saldo total del Club Cine+ para canjes globales.
                    @endif
                </p>
            </div>
            <div class="shrink-0">
                <button
                    type="button"
                    wire:click="togglePuntosSeparados"
                    wire:loading.attr="disabled"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all duration-200 cursor-pointer shadow-md flex items-center gap-2 {{ $puntosSeparadosActivo ? 'bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white border border-amber-500/40' : 'bg-red-600 hover:bg-red-500 text-white shadow-red-900/30' }}"
                >
                    <span>{{ $puntosSeparadosActivo ? '⚙️ Cambiar a Modo Unificado' : '🚀 Activar Puntos Separados (Cine vs. Dulcería)' }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Lista -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-zinc-950/60 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-800">
                    <tr>
                        <th class="px-6 py-4">Promoción</th>
                        <th class="px-6 py-4">Tipo</th>
                        <th class="px-6 py-4">Valor</th>
                        <th class="px-6 py-4">Código Requerido</th>
                        <th class="px-6 py-4">Vigencia</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($descuentos as $d)
                        <tr class="hover:bg-zinc-800/30 transition-colors" wire:key="desc-{{ $d->id }}">
                            <td class="px-6 py-4">
                                <p class="font-bold text-white">{{ $d->nombre }}</p>
                                @if ($d->descripcion)
                                    <p class="text-xs text-zinc-400 mt-0.5 line-clamp-1">{{ $d->descripcion }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-zinc-300 whitespace-nowrap">{{ $d->tipo->label() }}</td>
                            <td class="px-6 py-4 font-black text-white whitespace-nowrap">
                                @if ($d->tipo->value === 'porcentaje')
                                    {{ number_format($d->valor, 0) }}%
                                @else
                                    S/ {{ number_format($d->valor, 2) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($d->codigo)
                                    <span class="font-mono text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded">
                                        {{ $d->codigo }}
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-500 italic">Público (selector)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-zinc-400 whitespace-nowrap">
                                @if ($d->fecha_inicio || $d->fecha_fin)
                                    {{ $d->fecha_inicio ? $d->fecha_inicio->format('d/m/Y') : 'Inicio' }} - {{ $d->fecha_fin ? $d->fecha_fin->format('d/m/Y') : 'Sin fin' }}
                                @else
                                    <span class="text-zinc-500 italic">Permanente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button type="button" wire:click="toggleActivo({{ $d->id }})" class="cursor-pointer">
                                    @if ($d->activo)
                                        <span class="bg-green-500/10 text-green-400 border border-green-500/30 text-xs px-2.5 py-1 rounded-full font-bold">Activo</span>
                                    @else
                                        <span class="bg-zinc-800 text-zinc-400 text-xs px-2.5 py-1 rounded-full font-bold">Inactivo</span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <flux:button size="sm" variant="subtle" wire:click="editar({{ $d->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="danger" wire:click="eliminar({{ $d->id }})" wire:confirm="¿Seguro que deseas eliminar esta promoción?">Eliminar</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500 italic">No hay promociones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Formulario -->
    <flux:modal wire:model="modalAbierto" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $descuentoId ? 'Editar Promoción' : 'Nueva Promoción' }}</flux:heading>
            <flux:subheading>Configura cómo y cuándo se aplicará la rebaja sobre las entradas.</flux:subheading>
        </div>

        <form wire:submit="guardar" class="space-y-4">
            <flux:field>
                <flux:label>Nombre de la Promoción</flux:label>
                <flux:input wire:model="nombre" placeholder="Ej. Descuento Estudiante / Promo Entel" required />
                <flux:error name="nombre" />
            </flux:field>

            <flux:field>
                <flux:label>Descripción o condiciones</flux:label>
                <flux:textarea wire:model="descripcion" rows="2" placeholder="Ej. Presentar carné universitario al ingreso" />
                <flux:error name="descripcion" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Tipo de Descuento</flux:label>
                    <select wire:model="tipo" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg p-2.5 text-sm text-zinc-200">
                        @foreach ($tipos as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    <flux:error name="tipo" />
                </flux:field>

                <flux:field>
                    <flux:label>Valor (S/ o %)</flux:label>
                    <flux:input type="number" step="0.50" wire:model="valor" required />
                    <flux:error name="valor" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Código de Descuento</flux:label>
                <flux:input wire:model="codigo" placeholder="Ej. CINE50, ESTUDIANTE, VIP20" required />
                <flux:description>Código que el cliente ingresará en la pantalla de pago para aplicar el descuento.</flux:description>
                <flux:error name="codigo" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Fecha Inicio (Opcional)</flux:label>
                    <flux:input type="date" wire:model="fecha_inicio" />
                    <flux:error name="fecha_inicio" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha Fin (Opcional)</flux:label>
                    <flux:input type="date" wire:model="fecha_fin" />
                    <flux:error name="fecha_fin" />
                </flux:field>
            </div>

            <flux:field>
                <flux:checkbox wire:model="activo" label="Promoción activa inmediatamente" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('modalAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Guardar Promoción</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
