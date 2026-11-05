<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Salas, Plantillas y Butacas</h1>
            <p class="text-sm text-zinc-400 mt-1">Configura las salas por cine (sede), sus dimensiones y la plantilla de escaleras.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <select wire:model.live="filtroSede" class="bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-xs font-bold text-zinc-200">
                <option value="">📍 Todos los Cines / Sedes</option>
                @foreach ($sedes as $sedeItem)
                    <option value="{{ $sedeItem }}">{{ $sedeItem }}</option>
                @endforeach
            </select>
            <flux:button variant="primary" icon="plus" wire:click="crear">
                Nueva Sala
            </flux:button>
        </div>
    </div>

    <!-- Lista de Salas agrupadas por Sede -->
    @forelse ($salas->groupBy('sede') as $nombreSede => $salasSede)
        <div class="space-y-3" wire:key="grupo-sede-{{ md5($nombreSede) }}">
            <div class="flex items-center gap-2 border-b border-zinc-800 pb-2">
                <span class="text-sm font-black text-red-400">📍 {{ $nombreSede ?: 'Sede General' }}</span>
                <span class="text-xs text-zinc-500">({{ $salasSede->count() }} {{ $salasSede->count() === 1 ? 'sala' : 'salas' }})</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($salasSede as $s)
                    @php
                        $porFila = $s->butacas->groupBy(fn ($b) => explode('-', $b->numero_butaca)[0] ?? 'A');
                        $cantFilas = $porFila->count();
                        $cantCol = $porFila->first()?->count() ?? 0;
                        $plantillaKey = $s->plantilla ?: 'clasica';
                        $infoPlantilla = $catalogoPlantillas[$plantillaKey] ?? $catalogoPlantillas['clasica'];
                    @endphp
                    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 space-y-4 shadow-sm" wire:key="sala-{{ $s->id }}">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-red-500 bg-red-500/10 border border-red-500/20 px-2 py-0.5 rounded">
                                        Sala ID: #{{ $s->id }}
                                    </span>
                                    <span class="text-[10px] font-bold text-amber-300 bg-amber-500/10 border border-amber-500/25 px-2 py-0.5 rounded">
                                        {{ $infoPlantilla['icono'] }} {{ $infoPlantilla['nombre'] }}
                                    </span>
                                </div>
                                <h2 class="text-lg font-bold text-white mt-1.5">{{ $s->numero_sala }}</h2>
                                <span class="text-xs text-red-400 font-medium block">📍 {{ $s->sede }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <flux:button size="sm" variant="subtle" wire:click="editar({{ $s->id }})">
                                    Editar
                                </flux:button>
                                <flux:button size="sm" variant="danger" wire:click="eliminar({{ $s->id }})" wire:confirm="¿Seguro que deseas eliminar esta sala y todas sus butacas?">
                                    Eliminar
                                </flux:button>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-xs border-y border-zinc-800/80 py-3">
                            <div>
                                <span class="text-zinc-500 block">Capacidad Útil</span>
                                <span class="text-white font-bold text-sm">{{ $s->capacidad }} butacas</span>
                            </div>
                            <div>
                                <span class="text-zinc-500 block">Matriz</span>
                                <span class="text-zinc-200 font-mono font-bold text-xs">{{ $cantFilas }} filas × {{ $cantCol }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-500 block">Funciones</span>
                                <span class="text-white font-bold text-sm">{{ $s->funciones_count }} asociadas</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-zinc-400">
                            <span class="font-mono text-amber-400/90">{{ $infoPlantilla['preview'] }}</span>
                            <a href="{{ route('admin.funciones') }}" class="text-red-400 hover:text-red-300 font-bold underline">
                                Ver horarios y mapa →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-8 text-center text-zinc-500 italic">
            No hay salas registradas.
        </div>
    @endforelse

    <!-- Modal Formulario -->
    <flux:modal wire:model="modalAbierto" class="max-w-lg w-full space-y-6">
        <div>
            <flux:heading size="lg">{{ $salaId ? 'Editar Sala y Plantilla' : 'Crear Nueva Sala' }}</flux:heading>
            <flux:subheading>Indica el nombre, sede, dimensiones y la plantilla de escaleras de la sala.</flux:subheading>
        </div>

        <form wire:submit="guardar" class="space-y-4">
            <flux:field>
                <flux:label>Nombre o Número de la Sala</flux:label>
                <flux:input wire:model="numero_sala" placeholder="Ej. Sala 4 (XD / Dolby Atmos)" required />
                <flux:error name="numero_sala" />
            </flux:field>

            <flux:field>
                <flux:label>Sede o Cine</flux:label>
                <flux:select wire:model="sede">
                    @foreach ($sedes as $sedeItem)
                        <option value="{{ $sedeItem }}">{{ $sedeItem }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="sede" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Número de Filas (A, B, C...)</flux:label>
                    <flux:input type="number" wire:model.live="filas" min="1" max="26" required />
                    <flux:description>Máx. 26 letras del abecedario</flux:description>
                    <flux:error name="filas" />
                </flux:field>

                <flux:field>
                    <flux:label>Columnas por Fila</flux:label>
                    <flux:input type="number" wire:model.live="asientos_por_fila" min="1" max="30" required />
                    <flux:description>Ej. 10 asientos por fila</flux:description>
                    <flux:error name="asientos_por_fila" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>🪜 Plantilla de Distribución y Escaleras</flux:label>
                <select wire:model.live="plantilla" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg p-2.5 text-sm text-zinc-200">
                    @foreach ($catalogoPlantillas as $kPlant => $infoPlant)
                        <option value="{{ $kPlant }}">{{ $infoPlant['icono'] }} {{ $infoPlant['nombre'] }} — {{ $infoPlant['preview'] }}</option>
                    @endforeach
                </select>
                @if (isset($catalogoPlantillas[$plantilla]))
                    <p class="text-[11px] text-amber-400/90 mt-1">{{ $catalogoPlantillas[$plantilla]['descripcion'] }}</p>
                @endif
            </flux:field>

            <div class="p-3.5 bg-zinc-950 border rounded-xl text-xs space-y-2 {{ $this->totalButacas >= 20 && $this->totalButacas <= 300 ? 'border-zinc-800' : 'border-red-600/60 bg-red-950/20' }}">
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400 font-semibold flex items-center gap-1.5">
                        <span>🪑</span>
                        <span>Resolución y cálculo de butacas a crear:</span>
                    </span>
                    <span class="text-sm font-black font-mono {{ $this->totalButacas >= 20 && $this->totalButacas <= 300 ? 'text-amber-400' : 'text-red-400' }}">
                        {{ $this->totalButacas }} butacas
                    </span>
                </div>

                <div class="flex items-center justify-between text-[11px] pt-1 border-t border-zinc-800/60 text-zinc-400">
                    <span>Dimensiones: <strong class="text-zinc-200">{{ (int) $filas }} filas × {{ (int) $asientos_por_fila }} columnas</strong></span>
                    <span class="font-mono text-zinc-500">(Fila A-1 a {{ range('A', 'Z')[max(0, min(25, (int) $filas - 1))] }}-{{ (int) $asientos_por_fila }})</span>
                </div>

                @if ($this->totalButacas >= 20 && $this->totalButacas <= 300)
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-400 bg-emerald-950/40 border border-emerald-500/30 px-2.5 py-1 rounded-lg">
                        <span>✅</span>
                        <span>Capacidad válida: Cumple con el rango reglamentario (mínimo 20 y máximo 300 butacas).</span>
                    </div>
                @elseif ($this->totalButacas < 20)
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-red-400 bg-red-950/60 border border-red-500/40 px-2.5 py-1 rounded-lg">
                        <span>⛔</span>
                        <span>Capacidad insuficiente: Mínimo 20 butacas requeridas (faltan {{ 20 - $this->totalButacas }} butacas).</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-red-400 bg-red-950/60 border border-red-500/40 px-2.5 py-1 rounded-lg">
                        <span>⛔</span>
                        <span>Capacidad excedida: Máximo 300 butacas permitidas (sobran {{ $this->totalButacas - 300 }} butacas).</span>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('modalAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="guardar">{{ $salaId ? 'Guardar Cambios' : 'Crear Sala y Butacas' }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
