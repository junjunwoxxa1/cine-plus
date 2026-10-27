<div class="space-y-6">
    <!-- Cabecera Principal y Selector de Vista -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Funciones, Salas y Butacas</h1>
            <p class="text-sm text-zinc-400 mt-1">
                Ubica horarios por cine, sala o película y personaliza la plantilla de escaleras y butacas de cada función.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Conmutador de Modo de Vista -->
            <div class="inline-flex bg-zinc-900 border border-zinc-800 rounded-xl p-1 gap-1">
                <button type="button" wire:click="$set('modoVista', 'cines')"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $modoVista === 'cines' ? 'bg-red-600 text-white shadow-sm' : 'text-zinc-400 hover:text-white' }}">
                    <span>🏛️</span>
                    <span>Por Cine y Sala</span>
                </button>
                <button type="button" wire:click="$set('modoVista', 'peliculas')"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $modoVista === 'peliculas' ? 'bg-red-600 text-white shadow-sm' : 'text-zinc-400 hover:text-white' }}">
                    <span>🎬</span>
                    <span>Por Película</span>
                </button>
                <button type="button" wire:click="$set('modoVista', 'tabla')"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $modoVista === 'tabla' ? 'bg-red-600 text-white shadow-sm' : 'text-zinc-400 hover:text-white' }}">
                    <span>📋</span>
                    <span>Lista</span>
                </button>
            </div>

            <flux:button variant="primary" icon="plus" wire:click="crear()">
                Programar Función
            </flux:button>
        </div>
    </div>

    <!-- Barra Rápida de Fechas -->
    <div class="bg-zinc-900/90 border border-zinc-800 rounded-xl p-3 space-y-3">
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <span class="text-[11px] font-black uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
                <span>📅</span> Filtrar por Día de Proyección
            </span>
            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="filtroFecha"
                    class="bg-zinc-950 border border-zinc-800 rounded-lg px-2.5 py-1 text-xs text-zinc-200 focus:border-red-500 outline-none" />
                @if ($filtroSede !== '' || $filtroSala !== '' || $filtroPelicula !== '' || $filtroFecha !== '')
                    <button type="button" wire:click="limpiarFiltros"
                        class="text-xs font-bold text-red-400 hover:text-red-300 underline underline-offset-4 cursor-pointer">
                        Limpiar filtros
                    </button>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <button type="button" wire:click="$set('filtroFecha', '')"
                class="px-3.5 py-2 rounded-xl border text-xs font-bold whitespace-nowrap transition-all cursor-pointer shrink-0 {{ $filtroFecha === '' ? 'bg-red-600 border-red-500 text-white shadow-md shadow-red-950/50' : 'bg-zinc-950/70 border-zinc-800 text-zinc-400 hover:border-zinc-700 hover:text-white' }}">
                Todas las fechas
            </button>

            @foreach ($fechasRapidas as $dia)
                <button type="button" wire:click="seleccionarFecha('{{ $dia['valor'] }}')"
                    class="px-3.5 py-1.5 rounded-xl border text-left whitespace-nowrap transition-all cursor-pointer shrink-0 {{ $filtroFecha === $dia['valor'] ? 'bg-red-600 border-red-500 text-white shadow-md shadow-red-950/50' : 'bg-zinc-950/70 border-zinc-800 text-zinc-300 hover:border-zinc-700 hover:text-white' }}">
                    <span class="block text-xs font-black leading-tight">{{ $dia['etiqueta'] }}</span>
                    <span class="block text-[10px] {{ $filtroFecha === $dia['valor'] ? 'text-red-100' : 'text-zinc-500' }}">{{ $dia['subtitulo'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Filtros por Cine (Sede), Sala y Película -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-400 mb-1">📍 Cine / Sede</label>
            <select wire:model.live="filtroSede" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-sm text-zinc-200 focus:border-red-500 outline-none">
                <option value="">Todos los cines ({{ $sedes->count() }} sedes)</option>
                @foreach ($sedes as $sedeNombre)
                    <option value="{{ $sedeNombre }}">{{ $sedeNombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-400 mb-1">🚪 Sala de Proyección</label>
            <select wire:model.live="filtroSala" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-sm text-zinc-200 focus:border-red-500 outline-none">
                <option value="">Todas las salas ({{ $salas->count() }})</option>
                @foreach ($salas as $s)
                    <option value="{{ $s->id }}">{{ $s->numero_sala }} — {{ $s->sede }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-400 mb-1">🎬 Película</label>
            <select wire:model.live="filtroPelicula" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-sm text-zinc-200 focus:border-red-500 outline-none">
                <option value="">Todas las películas ({{ $peliculas->count() }})</option>
                @foreach ($peliculas as $p)
                    <option value="{{ $p->id }}">{{ $p->titulo }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Barra de Resumen de Resultados -->
    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-zinc-400 px-1">
        <div>
            Mostrando <strong class="text-white">{{ $totalFuncionesFiltradas }}</strong> funciones programadas
            @if ($filtroSede)
                en <strong class="text-red-400">{{ $filtroSede }}</strong>
            @endif
            @if ($filtroFecha)
                para el día <strong class="text-amber-400">{{ \Carbon\Carbon::parse($filtroFecha)->format('d/m/Y') }}</strong>
            @endif
        </div>
        <div class="flex items-center gap-4 text-[11px]">
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Alta disponibilidad</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Media ocupación</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> Casi lleno / Agotado</span>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODO VISTA 1: AGRUPADO POR CINE (SEDE) Y SALA                          -->
    <!-- ===================================================================== -->
    @if ($modoVista === 'cines')
        <div class="space-y-8">
            @forelse ($cinesAgrupados as $nombreSede => $salasDeSede)
                <div class="bg-zinc-900/70 border border-zinc-800 rounded-2xl overflow-hidden shadow-lg" wire:key="sede-{{ md5($nombreSede) }}">
                    <!-- Cabecera del Cine / Sede -->
                    <div class="bg-zinc-950/90 border-b border-zinc-800 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-red-600/15 border border-red-500/30 flex items-center justify-center text-lg shrink-0">
                                📍
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-red-400 block">COMPLEJO / SEDE</span>
                                <h2 class="text-lg font-black text-white">{{ $nombreSede }}</h2>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-zinc-400">
                            <span class="px-2.5 py-1 rounded-lg bg-zinc-900 border border-zinc-800 font-semibold text-zinc-300">
                                🚪 {{ $salasDeSede->count() }} {{ $salasDeSede->count() === 1 ? 'Sala' : 'Salas' }}
                            </span>
                        </div>
                    </div>

                    <!-- Salas dentro de este Cine -->
                    <div class="p-4 sm:p-5 space-y-6 divide-y divide-zinc-800/70">
                        @foreach ($salasDeSede as $salaItem)
                            @php
                                $funcionesSala = $funcionesPorSala->get($salaItem->id, collect());
                                $plantillaSalaKey = $salaItem->plantilla ?: 'clasica';
                                $infoPlantillaSala = $catalogoPlantillas[$plantillaSalaKey] ?? $catalogoPlantillas['clasica'];
                            @endphp
                            <div class="{{ !$loop->first ? 'pt-6' : '' }} space-y-4" wire:key="sala-card-{{ $salaItem->id }}">
                                <!-- Cabecera de la Sala -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-zinc-950/50 border border-zinc-800/90 rounded-xl p-3.5">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <span class="px-2.5 py-1 rounded-lg bg-red-500/10 border border-red-500/20 text-xs font-black text-red-400">
                                            {{ $salaItem->numero_sala }}
                                        </span>
                                        <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/25 text-xs font-bold text-amber-300 inline-flex items-center gap-1">
                                            <span>{{ $infoPlantillaSala['icono'] }}</span>
                                            <span>Plantilla: {{ $infoPlantillaSala['nombre'] }}</span>
                                        </span>
                                        <span class="text-xs text-zinc-300 font-semibold">
                                            💺 <strong class="text-white">{{ $salaItem->capacidad }} butacas</strong>
                                        </span>
                                        <span class="text-xs text-zinc-400">
                                            • {{ $funcionesSala->count() }} {{ $funcionesSala->count() === 1 ? 'horario' : 'horarios' }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button" wire:click="abrirEditarSala({{ $salaItem->id }})"
                                            class="px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-bold transition-colors cursor-pointer inline-flex items-center gap-1.5">
                                            <span>📐</span>
                                            <span>Plantilla y Sala</span>
                                        </button>
                                        <button type="button" wire:click="crear({{ $salaItem->id }})"
                                            class="px-3 py-1.5 rounded-lg bg-red-600/20 hover:bg-red-600 border border-red-500/40 text-red-300 hover:text-white text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-1">
                                            <span>+</span>
                                            <span>Nuevo Horario aquí</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tarjetas de Horarios de la Sala -->
                                @if ($funcionesSala->isNotEmpty())
                                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5">
                                        @foreach ($funcionesSala as $f)
                                            @php
                                                $cfgFunc = $f->obtenerMapaConfig();
                                                $plantillaFuncNombre = $catalogoPlantillas[$cfgFunc['plantilla'] ?? 'clasica']['nombre'] ?? 'Personalizada';
                                                $capacidadTotal = max(1, (int) $salaItem->capacidad);
                                                $libres = (int) $f->asientos_disponibles;
                                                $ocupadas = max(0, $capacidadTotal - $libres);
                                                $porcentajeLibre = min(100, round(($libres / $capacidadTotal) * 100));
                                                $colorBarra = $porcentajeLibre > 50 ? 'bg-emerald-500' : ($porcentajeLibre > 15 ? 'bg-amber-500' : 'bg-red-500');
                                            @endphp
                                            <div class="bg-zinc-950/90 border border-zinc-800 hover:border-zinc-700 rounded-xl p-3.5 flex flex-col justify-between gap-3 transition-all shadow-sm"
                                                wire:key="func-card-{{ $f->id }}">
                                                <div class="flex items-start gap-3">
                                                    @if ($f->pelicula && $f->pelicula->imagen_url)
                                                        <img src="{{ $f->pelicula->imagen_url }}" alt="{{ $f->pelicula->titulo }}"
                                                            class="w-12 h-16 object-cover rounded-lg border border-zinc-800 shrink-0">
                                                    @else
                                                        <div class="w-12 h-16 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-lg shrink-0">🎬</div>
                                                    @endif

                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span class="px-2 py-0.5 rounded bg-zinc-900 border border-zinc-700 text-xs font-black text-amber-400 font-mono">
                                                                🕒 {{ $f->fecha_hora->format('H:i') }} hrs
                                                            </span>
                                                            <span class="text-[11px] font-mono text-zinc-400">
                                                                {{ $f->fecha_hora->format('d/m/Y') }}
                                                            </span>
                                                        </div>
                                                        <h3 class="text-sm font-bold text-white truncate mt-1.5" title="{{ $f->pelicula->titulo ?? '' }}">
                                                            {{ $f->pelicula->titulo ?? 'Película' }}
                                                        </h3>
                                                        <div class="flex items-center gap-2 text-[11px] text-zinc-400 mt-0.5">
                                                            <span class="font-bold text-white">S/ {{ number_format($f->precio, 2) }}</span>
                                                            <span>•</span>
                                                            <span class="text-amber-400 font-semibold">{{ $f->precio_puntos }} pts</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Barra de estado de Butacas y Plantilla -->
                                                <div class="space-y-1.5 bg-zinc-900/90 border border-zinc-800/80 rounded-lg p-2.5">
                                                    <div class="flex items-center justify-between text-[11px]">
                                                        <span class="text-zinc-400 font-medium">
                                                            <strong class="text-emerald-400">{{ $libres }} libres</strong>
                                                            @if ($ocupadas > 0)
                                                                <span class="text-red-400 font-bold">({{ $ocupadas }} ocup.)</span>
                                                            @endif
                                                        </span>
                                                        <span class="text-[10px] text-amber-300/90 font-semibold truncate max-w-[140px]" title="{{ $plantillaFuncNombre }}">
                                                            🪜 {{ $plantillaFuncNombre }}
                                                        </span>
                                                    </div>
                                                    <div class="w-full h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                                                        <div class="h-full {{ $colorBarra }} transition-all" style="width: {{ $porcentajeLibre }}%"></div>
                                                    </div>
                                                </div>

                                                <!-- Botones de Acción Directa -->
                                                <div class="flex items-center justify-between gap-2 pt-1">
                                                    <button type="button" wire:click="abrirMapaButacas({{ $f->id }})"
                                                        class="flex-1 py-1.5 px-2.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-bold transition-colors cursor-pointer inline-flex items-center justify-center gap-1.5">
                                                        <span>💺</span>
                                                        <span>Butacas y Escaleras</span>
                                                    </button>
                                                    <button type="button" wire:click="editar({{ $f->id }})"
                                                        title="Editar Horario, Sala o Precio"
                                                        class="p-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-700 text-zinc-300 hover:text-white text-xs font-bold cursor-pointer">
                                                        ✏️
                                                    </button>
                                                    <button type="button" wire:click="eliminar({{ $f->id }})"
                                                        wire:confirm="¿Seguro que deseas eliminar esta función?"
                                                        title="Eliminar función"
                                                        class="p-1.5 rounded-lg bg-red-950/40 hover:bg-red-900/60 border border-red-800/40 text-red-400 text-xs font-bold cursor-pointer">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-zinc-950/40 border border-dashed border-zinc-800 rounded-xl p-6 text-center text-xs text-zinc-500">
                                        No hay funciones programadas en esta sala con los filtros actuales.
                                        <button type="button" wire:click="crear({{ $salaItem->id }})" class="text-red-400 font-bold underline ml-1 cursor-pointer">
                                            Programar ahora
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-8 text-center text-zinc-500 italic">
                    No se encontraron cines ni salas para los filtros seleccionados.
                </div>
            @endforelse
        </div>
    @endif

    <!-- ===================================================================== -->
    <!-- MODO VISTA 2: AGRUPADO POR PELÍCULA                                    -->
    <!-- ===================================================================== -->
    @if ($modoVista === 'peliculas')
        <div class="space-y-6">
            @forelse ($peliculas as $peliculaItem)
                @php
                    $funcionesPeli = $funcionesPorPelicula->get($peliculaItem->id, collect());
                @endphp
                @if ($filtroPelicula === '' || (int) $filtroPelicula === $peliculaItem->id)
                    @if ($funcionesPeli->isNotEmpty() || $filtroPelicula !== '')
                        <div class="bg-zinc-900/75 border border-zinc-800 rounded-2xl overflow-hidden shadow-lg" wire:key="peli-group-{{ $peliculaItem->id }}">
                            <!-- Cabecera de la Película -->
                            <div class="bg-zinc-950/90 border-b border-zinc-800 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    @if ($peliculaItem->imagen_url)
                                        <img src="{{ $peliculaItem->imagen_url }}" alt="{{ $peliculaItem->titulo }}"
                                            class="w-14 h-20 object-cover rounded-xl border border-zinc-700 shrink-0">
                                    @endif
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="px-2 py-0.5 rounded bg-red-500/10 border border-red-500/20 text-[10px] font-black text-red-400 uppercase">
                                                {{ $peliculaItem->genero }}
                                            </span>
                                            <span class="text-xs text-zinc-400">⏱️ {{ $peliculaItem->duracion }} min</span>
                                            <span class="text-xs text-zinc-400">• 🎟️ {{ $peliculaItem->clasificacion }}</span>
                                        </div>
                                        <h2 class="text-lg sm:text-xl font-black text-white mt-1">{{ $peliculaItem->titulo }}</h2>
                                    </div>
                                </div>

                                <button type="button" wire:click="crear(null, {{ $peliculaItem->id }})"
                                    class="px-3.5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition-colors cursor-pointer self-start sm:self-auto shrink-0">
                                    + Agregar Horario a esta Película
                                </button>
                            </div>

                            <!-- Agrupado internamente por Cine y Sala -->
                            <div class="p-4 sm:p-5 space-y-4">
                                @php
                                    $porSalaEnPeli = $funcionesPeli->groupBy('sala_id');
                                @endphp
                                @forelse ($porSalaEnPeli as $salaIdPeli => $horariosEnSala)
                                    @php
                                        $salaObj = $horariosEnSala->first()->sala;
                                    @endphp
                                    <div class="bg-zinc-950/70 border border-zinc-800/90 rounded-xl p-4 space-y-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-800/80 pb-2.5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="text-xs font-black text-red-400">📍 {{ $salaObj->sede }}</span>
                                                <span class="text-zinc-600">→</span>
                                                <span class="px-2 py-0.5 rounded bg-zinc-900 border border-zinc-700 text-xs font-bold text-white">
                                                    {{ $salaObj->numero_sala }} ({{ $salaObj->capacidad }} butacas)
                                                </span>
                                            </div>
                                            <button type="button" wire:click="abrirEditarSala({{ $salaObj->id }})"
                                                class="text-[11px] font-bold text-zinc-400 hover:text-white underline cursor-pointer">
                                                📐 Plantilla y Sala
                                            </button>
                                        </div>

                                        <div class="flex flex-wrap gap-2.5">
                                            @foreach ($horariosEnSala as $f)
                                                <div class="bg-zinc-900 border border-zinc-800 hover:border-zinc-600 rounded-xl p-2.5 flex items-center gap-3">
                                                    <div>
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="text-sm font-black text-amber-400 font-mono">{{ $f->fecha_hora->format('H:i') }}</span>
                                                            <span class="text-[10px] font-mono text-zinc-400">{{ $f->fecha_hora->format('d/m') }}</span>
                                                        </div>
                                                        <span class="block text-[10px] text-zinc-400">
                                                            💺 {{ $f->asientos_disponibles }}/{{ $salaObj->capacidad }} libres • S/ {{ number_format($f->precio, 2) }}
                                                        </span>
                                                    </div>

                                                    <div class="flex items-center gap-1 border-l border-zinc-800 pl-2.5">
                                                        <button type="button" wire:click="abrirMapaButacas({{ $f->id }})"
                                                            title="Ver / Editar Mapa de Butacas y Escaleras"
                                                            class="px-2 py-1 rounded bg-zinc-800 hover:bg-zinc-700 text-[11px] font-bold text-white cursor-pointer">
                                                            💺 Mapa
                                                        </button>
                                                        <button type="button" wire:click="editar({{ $f->id }})"
                                                            title="Editar horario o sala"
                                                            class="p-1 rounded bg-zinc-800 hover:bg-zinc-700 text-xs cursor-pointer">
                                                            ✏️
                                                        </button>
                                                        <button type="button" wire:click="eliminar({{ $f->id }})"
                                                            wire:confirm="¿Eliminar este horario?"
                                                            title="Eliminar horario"
                                                            class="p-1 rounded bg-red-950/60 hover:bg-red-900 text-xs cursor-pointer">
                                                            🗑️
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-zinc-500 italic">Sin funciones programadas para esta película.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                @endif
            @empty
                <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-8 text-center text-zinc-500 italic">
                    No hay películas registradas.
                </div>
            @endforelse
        </div>
    @endif

    <!-- ===================================================================== -->
    <!-- MODO VISTA 3: TABLA COMPACTA MEJORADA                                  -->
    <!-- ===================================================================== -->
    @if ($modoVista === 'tabla')
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-300">
                    <thead class="bg-zinc-950/60 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-800">
                        <tr>
                            <th class="px-5 py-4">Cine (Sede) y Sala</th>
                            <th class="px-5 py-4">Película</th>
                            <th class="px-5 py-4">Fecha y Horario</th>
                            <th class="px-5 py-4">Precio / Puntos</th>
                            <th class="px-5 py-4">Butacas y Plantilla</th>
                            <th class="px-5 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        @forelse ($funcionesPaginadas as $f)
                            @php
                                $cfgTabla = $f->obtenerMapaConfig();
                                $nombrePlantillaTabla = $catalogoPlantillas[$cfgTabla['plantilla'] ?? 'clasica']['nombre'] ?? 'Personalizada';
                            @endphp
                            <tr class="hover:bg-zinc-800/30 transition-colors" wire:key="func-row-{{ $f->id }}">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="text-[11px] font-bold text-red-400 block">📍 {{ $f->sala->sede }}</span>
                                    <span class="text-xs font-black text-white">{{ $f->sala->numero_sala }}</span>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-white">{{ $f->pelicula->titulo }}</td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded bg-zinc-950 border border-zinc-800 text-amber-400 font-mono text-xs font-bold">
                                        {{ $f->fecha_hora->format('H:i') }} hrs
                                    </span>
                                    <span class="text-xs text-zinc-400 font-mono ml-1.5">{{ $f->fecha_hora->format('d/m/Y') }}</span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-black text-white">S/ {{ number_format($f->precio, 2) }}</span>
                                    <span class="text-xs text-amber-400 font-bold block">{{ $f->precio_puntos }} pts</span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <button type="button" wire:click="abrirMapaButacas({{ $f->id }})"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-xs font-bold text-zinc-200 transition-colors cursor-pointer">
                                        <span>💺 {{ $f->asientos_disponibles }} / {{ $f->sala->capacidad }} libres</span>
                                        <span class="text-amber-400 text-[10px]">({{ $nombrePlantillaTabla }})</span>
                                    </button>
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                    <flux:button size="sm" variant="subtle" wire:click="editar({{ $f->id }})">Editar</flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="eliminar({{ $f->id }})" wire:confirm="¿Seguro que deseas eliminar esta función?">Eliminar</flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-zinc-500 italic">No se encontraron funciones programadas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-zinc-800">
                {{ $funcionesPaginadas->links() }}
            </div>
        </div>
    @endif

    <!-- ===================================================================== -->
    <!-- MODAL 1: MAPA INTERACTIVO DE BUTACAS Y PLANTILLAS DE ESCALERAS         -->
    <!-- ===================================================================== -->
    <flux:modal wire:model="modalButacas" class="max-w-5xl w-full space-y-5">
        @if ($funcionActivaButacas)
            @php
                $escalerasColModal = $mapaConfigModal['escaleras_despues_columna'] ?? [];
                $pasillosFilaModal = $mapaConfigModal['pasillos_despues_fila'] ?? [];
                $celdasEscaleraModal = $mapaConfigModal['celdas_escalera'] ?? [];
                $plantillaActivaModal = $mapaConfigModal['plantilla'] ?? 'clasica';

                $butacasReales = $funcionActivaButacas->butacas->reject(fn ($b) => in_array($b->numero_butaca, $celdasEscaleraModal, true));
                $totalCap = $butacasReales->count();
                $totalLibres = $butacasReales->where('pivot.disponible', true)->count();
                $totalOcupadas = max(0, $totalCap - $totalLibres);
                $primeraFilaCol = $butacasPorFilaModal->first() ?? collect();
            @endphp

            <!-- Cabecera del Modal -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 border-b border-zinc-800 pb-4">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-red-400">
                        📍 {{ $funcionActivaButacas->sala->sede }} — {{ $funcionActivaButacas->sala->numero_sala }}
                    </span>
                    <h2 class="text-lg sm:text-xl font-black text-white mt-0.5">
                        {{ $funcionActivaButacas->pelicula->titulo }}
                    </h2>
                    <p class="text-xs text-zinc-400 font-mono mt-0.5">
                        🕒 {{ $funcionActivaButacas->fecha_hora->format('d/m/Y H:i') }} hrs • S/ {{ number_format($funcionActivaButacas->precio, 2) }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="liberarTodasButacas"
                        wire:confirm="¿Liberar TODAS las butacas de esta función?"
                        class="px-3 py-1.5 rounded-lg bg-emerald-600/20 hover:bg-emerald-600 border border-emerald-500/40 text-emerald-300 hover:text-white text-xs font-bold transition-colors cursor-pointer">
                        🔓 Liberar Todas
                    </button>
                    <button type="button" wire:click="ocuparTodasButacas"
                        wire:confirm="¿Marcar TODAS las butacas como ocupadas/bloqueadas?"
                        class="px-3 py-1.5 rounded-lg bg-red-600/20 hover:bg-red-600 border border-red-500/40 text-red-300 hover:text-white text-xs font-bold transition-colors cursor-pointer">
                        🔒 Ocupar Todas
                    </button>
                    <button type="button" wire:click="abrirEditarSala({{ $funcionActivaButacas->sala->id }})"
                        class="px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-bold transition-colors cursor-pointer">
                        📐 Redimensionar Sala
                    </button>
                </div>
            </div>

            <!-- Selector Visual de Plantillas de Forma y Escaleras -->
            <div class="bg-zinc-900/90 border border-zinc-800 rounded-xl p-3.5 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                            <span>🪜</span> Plantilla de Forma de Butacas y Escaleras
                        </h3>
                        <p class="text-[11px] text-zinc-400">
                            Escoge una distribución predefinida (escalera en 2da columna, 2 escaleras en extremos de 2 filas, etc.) o personalízala a medida.
                        </p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs text-zinc-300 font-semibold cursor-pointer shrink-0">
                        <input type="checkbox" wire:model.live="aplicarPlantillaASala" class="rounded border-zinc-700 bg-zinc-950 text-red-600 focus:ring-red-500">
                        <span>Guardar para toda la {{ $funcionActivaButacas->sala->numero_sala }}</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach ($catalogoPlantillas as $keyPlantilla => $itemPlantilla)
                        <button type="button"
                            wire:click="seleccionarPlantillaMapa('{{ $keyPlantilla }}')"
                            class="p-2.5 rounded-xl border text-left transition-all cursor-pointer flex items-start gap-2.5 {{ $plantillaActivaModal === $keyPlantilla ? 'bg-red-600/15 border-red-500 text-white shadow-sm' : 'bg-zinc-950/80 border-zinc-800 text-zinc-300 hover:border-zinc-700' }}">
                            <span class="text-base shrink-0 mt-0.5">{{ $itemPlantilla['icono'] }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-xs font-black truncate">{{ $itemPlantilla['nombre'] }}</span>
                                    @if ($plantillaActivaModal === $keyPlantilla)
                                        <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-red-600 text-white shrink-0">Activa</span>
                                    @endif
                                </div>
                                <span class="block text-[10px] font-mono text-amber-400/90 mt-0.5">{{ $itemPlantilla['preview'] }}</span>
                                <span class="block text-[10px] text-zinc-400 leading-snug mt-0.5">{{ $itemPlantilla['descripcion'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Indicadores y Conmutador de Modo de Clic -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="grid grid-cols-3 gap-2.5 flex-1 text-center">
                    <div class="bg-zinc-950 border border-zinc-800 rounded-xl p-2">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Butacas Útiles</span>
                        <span class="text-sm font-black text-white">{{ $totalCap }}</span>
                    </div>
                    <div class="bg-emerald-950/30 border border-emerald-500/30 rounded-xl p-2">
                        <span class="text-[10px] uppercase font-bold text-emerald-400 block">Disponibles</span>
                        <span class="text-sm font-black text-emerald-300">{{ $totalLibres }}</span>
                    </div>
                    <div class="bg-red-950/30 border border-red-500/30 rounded-xl p-2">
                        <span class="text-[10px] uppercase font-bold text-red-400 block">Ocupadas</span>
                        <span class="text-sm font-black text-red-300">{{ $totalOcupadas }}</span>
                    </div>
                </div>

                <!-- Modo de Clic -->
                <div class="inline-flex bg-zinc-900 border border-zinc-800 rounded-xl p-1 gap-1 self-center">
                    <button type="button" wire:click="$set('modoClicMapa', 'ocupacion')"
                        class="px-3 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $modoClicMapa === 'ocupacion' ? 'bg-emerald-600 text-white shadow' : 'text-zinc-400 hover:text-white' }}">
                        <span>💺</span>
                        <span>Modo Ocupación (Liberar/Ocupar)</span>
                    </button>
                    <button type="button" wire:click="$set('modoClicMapa', 'escaleras')"
                        class="px-3 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $modoClicMapa === 'escaleras' ? 'bg-amber-500 text-zinc-950 shadow font-black' : 'text-zinc-400 hover:text-white' }}">
                        <span>🪜</span>
                        <span>Modo Editar Escaleras / Forma</span>
                    </button>
                </div>
            </div>

            <!-- Pantalla y Mapa Interactivo con Escaleras -->
            <div class="bg-zinc-950 border border-zinc-800 rounded-2xl p-4 sm:p-6 space-y-5">
                <div class="max-w-md mx-auto text-center space-y-1.5">
                    <p class="text-[10px] font-black tracking-widest uppercase text-zinc-500">PANTALLA DE PROYECCIÓN</p>
                    <div class="w-full h-1.5 bg-gradient-to-r from-zinc-800 via-red-600 to-zinc-800 rounded-full shadow-lg shadow-red-900/30"></div>
                    @if ($modoClicMapa === 'escaleras')
                        <p class="text-[11px] text-amber-300 font-semibold pt-1">
                            🪜 Modo Editar Forma activo: Haz clic en cualquier asiento para convertirlo en <strong>Escalera (🪜)</strong> o usa los botones superiores <strong>+🪜</strong> para abrir pasillos verticales entre columnas.
                        </p>
                    @else
                        <p class="text-[11px] text-zinc-400 pt-1">
                            💺 Haz clic en cualquier butaca para alternar entre <strong class="text-emerald-400">Disponible</strong> y <strong class="text-red-400">Ocupada</strong>.
                        </p>
                    @endif
                </div>

                <div class="overflow-x-auto pb-2">
                    <div class="space-y-2.5 min-w-max mx-auto">
                        <!-- Controles superiores de Escalera por Columna -->
                        @if ($primeraFilaCol->count() > 1)
                            <div class="flex items-center justify-center gap-2 pb-1">
                                <span class="w-7 text-[9px] text-zinc-500 font-bold text-center shrink-0">COL</span>
                                <div class="flex flex-nowrap items-center gap-1.5">
                                    @foreach ($primeraFilaCol->values() as $idxCol => $bCol)
                                        @php
                                            $numCol = $idxCol + 1;
                                            $tieneEscaleraDespues = in_array($numCol, $escalerasColModal, true);
                                        @endphp
                                        <div class="w-8 sm:w-9 text-center text-[10px] font-mono font-bold text-zinc-500 shrink-0">
                                            C{{ $numCol }}
                                        </div>

                                        @if ($numCol < $primeraFilaCol->count())
                                            @if ($tieneEscaleraDespues)
                                                <button type="button" wire:click="toggleEscaleraColumna({{ $numCol }})"
                                                    title="Quitar escalera vertical después de columna {{ $numCol }}"
                                                    class="w-7 sm:w-8 h-6 rounded bg-amber-500/20 border border-amber-500/50 text-[10px] font-black text-amber-300 hover:bg-red-600 hover:text-white transition-colors cursor-pointer shrink-0">
                                                    🪜×
                                                </button>
                                            @elseif ($modoClicMapa === 'escaleras')
                                                <button type="button" wire:click="toggleEscaleraColumna({{ $numCol }})"
                                                    title="Insertar escalera después de columna {{ $numCol }}"
                                                    class="w-5 h-6 rounded bg-zinc-900 hover:bg-amber-500/30 border border-dashed border-zinc-700 hover:border-amber-400 text-[9px] font-bold text-zinc-400 hover:text-amber-300 transition-colors cursor-pointer shrink-0">
                                                    +🪜
                                                </button>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                                <span class="w-7 shrink-0"></span>
                            </div>
                        @endif

                        <!-- Filas de Butacas y Escaleras -->
                        @foreach ($butacasPorFilaModal as $filaLetra => $butacasDeFila)
                            @php
                                $tienePasilloDespuesFila = in_array($filaLetra, $pasillosFilaModal, true);
                            @endphp
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" wire:click="alternarFilaButacas('{{ $filaLetra }}')"
                                    title="Alternar disponibilidad de toda la fila {{ $filaLetra }}"
                                    class="w-7 h-8 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-700 text-xs font-black text-amber-400 cursor-pointer shrink-0">
                                    {{ $filaLetra }}
                                </button>

                                <div class="flex flex-nowrap items-center gap-1.5">
                                    @foreach ($butacasDeFila->values() as $idxAsiento => $butacaItem)
                                        @php
                                            $numCol = $idxAsiento + 1;
                                            $esCeldaEscalera = in_array($butacaItem->numero_butaca, $celdasEscaleraModal, true);
                                            $tieneEscaleraDespues = in_array($numCol, $escalerasColModal, true);
                                            $disp = (bool) $butacaItem->pivot->disponible;
                                            $etiquetaButaca = str_replace('-', '', $butacaItem->numero_butaca);
                                        @endphp

                                        @if ($esCeldaEscalera)
                                            <!-- Celda convertida en Escalera de Extremo / Acceso -->
                                            <button type="button"
                                                wire:click="clicEnCeldaButaca({{ $butacaItem->id }})"
                                                title="Escalera ({{ $etiquetaButaca }}) — En Modo Editar Escaleras haz clic para restaurar como butaca"
                                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg border border-dashed border-amber-500/40 bg-amber-950/25 text-amber-400 text-[10px] font-bold flex flex-col items-center justify-center shrink-0 transition-all {{ $modoClicMapa === 'escaleras' ? 'cursor-pointer hover:bg-amber-500/30 hover:border-amber-400' : 'cursor-default opacity-80' }}">
                                                <span class="leading-none text-xs">🪜</span>
                                                <span class="text-[8px] font-mono uppercase tracking-tighter">ESC</span>
                                            </button>
                                        @else
                                            <!-- Butaca Normal -->
                                            <button type="button"
                                                wire:click="clicEnCeldaButaca({{ $butacaItem->id }})"
                                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg border text-[11px] font-bold transition-all cursor-pointer flex items-center justify-center shrink-0 {{ $modoClicMapa === 'escaleras' ? 'ring-1 ring-amber-500/30 hover:bg-amber-500 hover:text-zinc-950 hover:border-amber-400' : '' }} {{ $disp ? 'bg-emerald-950/50 border-emerald-500/40 text-emerald-200 hover:bg-emerald-600 hover:text-white' : 'bg-red-600 border-red-500 text-white shadow-md shadow-red-950/50 hover:bg-red-500' }}">
                                                {{ $etiquetaButaca }}
                                            </button>
                                        @endif

                                        <!-- Escalera Vertical entre Columnas -->
                                        @if ($numCol < $butacasDeFila->count())
                                            @if ($tieneEscaleraDespues)
                                                <div class="w-7 sm:w-8 h-8 sm:h-9 rounded-md bg-zinc-900/90 border-x border-amber-500/30 flex flex-col items-center justify-between py-1 shrink-0"
                                                    title="Pasillo / Escalera Vertical">
                                                    <span class="w-4 h-0.5 bg-amber-500/40 rounded"></span>
                                                    <span class="text-[9px] leading-none">🪜</span>
                                                    <span class="w-4 h-0.5 bg-amber-500/40 rounded"></span>
                                                </div>
                                            @elseif ($modoClicMapa === 'escaleras')
                                                <div class="w-5 h-8 shrink-0"></div>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <span class="w-7 text-center text-xs font-bold text-zinc-500">{{ $filaLetra }}</span>
                                    @if ($modoClicMapa === 'escaleras' && !$loop->last)
                                        <button type="button" wire:click="togglePasilloFila('{{ $filaLetra }}')"
                                            title="Insertar o quitar pasillo horizontal debajo de la fila {{ $filaLetra }}"
                                            class="px-1.5 py-0.5 rounded border text-[9px] font-bold cursor-pointer {{ $tienePasilloDespuesFila ? 'bg-amber-500/20 border-amber-500/50 text-amber-300' : 'bg-zinc-900 border-zinc-800 text-zinc-500 hover:text-zinc-300' }}">
                                            {{ $tienePasilloDespuesFila ? 'Pasillo ×' : '+Pasillo' }}
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Pasillo Horizontal Transversal -->
                            @if ($tienePasilloDespuesFila)
                                <div class="my-1.5 py-1 px-4 rounded-lg bg-zinc-900/80 border border-dashed border-amber-500/30 text-center text-[10px] font-black uppercase tracking-widest text-amber-400/80 flex items-center justify-center gap-2">
                                    <span>🪜</span>
                                    <span>PASILLO / ESCALERA TRANSVERSAL</span>
                                    <span>🪜</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Leyenda -->
                <div class="flex flex-wrap items-center justify-center gap-5 text-xs text-zinc-400 border-t border-zinc-800/80 pt-3">
                    <span class="inline-flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded bg-emerald-950/60 border border-emerald-500/40 inline-block"></span>
                        Butaca Disponible
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded bg-red-600 border border-red-500 inline-block"></span>
                        Butaca Ocupada / Bloqueada
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="w-4 h-4 rounded border border-dashed border-amber-500/40 bg-amber-950/30 text-[10px] flex items-center justify-center">🪜</span>
                        Escalera / Pasillo de Acceso
                    </span>
                </div>
            </div>

            <div class="flex justify-end">
                <flux:button variant="primary" wire:click="$set('modalButacas', false)">Listo</flux:button>
            </div>
        @endif
    </flux:modal>

    <!-- ===================================================================== -->
    <!-- MODAL 2: CREAR / EDITAR FUNCIÓN Y HORARIOS                            -->
    <!-- ===================================================================== -->
    <flux:modal wire:model="modalAbierto" class="max-w-xl w-full space-y-6">
        <div>
            <flux:heading size="lg">{{ $funcionId ? 'Editar Función y Sala' : 'Programar Función' }}</flux:heading>
            <flux:subheading>Selecciona la película, el cine/sala, la plantilla de butacas y el horario.</flux:subheading>
        </div>

        <form wire:submit="guardar" class="space-y-4">
            <flux:field>
                <flux:label>Película</flux:label>
                <select wire:model="pelicula_id" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg p-2.5 text-sm text-zinc-200" required>
                    <option value="">Selecciona película</option>
                    @foreach ($peliculas as $p)
                        <option value="{{ $p->id }}">{{ $p->titulo }} ({{ $p->duracion }} min)</option>
                    @endforeach
                </select>
                <flux:error name="pelicula_id" />
            </flux:field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Cine y Sala</flux:label>
                    <select wire:model="sala_id" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg p-2.5 text-sm text-zinc-200" required>
                        <option value="">Selecciona sala</option>
                        @foreach ($todasLasSalas->groupBy('sede') as $sedeGrupo => $salasDelGrupo)
                            <optgroup label="📍 {{ $sedeGrupo }}">
                                @foreach ($salasDelGrupo as $s)
                                    <option value="{{ $s->id }}">{{ $s->numero_sala }} ({{ $s->capacidad }} butacas)</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <flux:error name="sala_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha y Hora Principal</flux:label>
                    <flux:input type="datetime-local" wire:model="fecha_hora" required />
                    <flux:error name="fecha_hora" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>🪜 Plantilla de Forma de Butacas y Escaleras</flux:label>
                <select wire:model="plantilla_funcion" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg p-2.5 text-sm text-zinc-200">
                    <option value="heredar">Usar plantilla predeterminada de la Sala</option>
                    @foreach ($catalogoPlantillas as $kPlant => $infoPlant)
                        <option value="{{ $kPlant }}">{{ $infoPlant['icono'] }} {{ $infoPlant['nombre'] }} — {{ $infoPlant['preview'] }}</option>
                    @endforeach
                </select>
            </flux:field>

            @if (!$funcionId)
                <!-- Programación rápida de múltiples horarios en el mismo día -->
                <div class="bg-zinc-950/80 border border-zinc-800 rounded-xl p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-zinc-300">⚡ ¿Agregar más horarios el mismo día en esta sala? (Opcional)</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach (['14:30', '16:15', '17:45', '19:15', '20:30', '22:15'] as $horaSugerida)
                            <button type="button" wire:click="agregarHorarioExtra('{{ $horaSugerida }}')"
                                class="px-2.5 py-1 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-700 text-xs font-mono text-zinc-300 hover:text-white cursor-pointer">
                                + {{ $horaSugerida }}
                            </button>
                        @endforeach
                    </div>

                    @if (!empty($horariosAdicionales))
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <span class="text-[11px] text-zinc-400">Horarios extra a crear:</span>
                            @foreach ($horariosAdicionales as $idx => $hExtra)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-600/20 border border-red-500/40 text-xs font-mono font-bold text-red-300">
                                    {{ $hExtra }} hrs
                                    <button type="button" wire:click="quitarHorarioExtra({{ $idx }})" class="hover:text-white cursor-pointer">×</button>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Precio de Entrada (S/)</flux:label>
                    <flux:input type="number" step="0.50" wire:model.live="precio" required />
                    <flux:error name="precio" />
                </flux:field>

                <flux:field>
                    <flux:label>Precio en Puntos</flux:label>
                    <flux:input type="number" wire:model="precio_puntos" required />
                    <flux:error name="precio_puntos" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('modalAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="guardar">
                    {{ $funcionId ? 'Guardar Cambios' : 'Programar Función' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- ===================================================================== -->
    <!-- MODAL 3: EDITAR SALA, CINE (SEDE) Y PLANTILLA DE BUTACAS               -->
    <!-- ===================================================================== -->
    <flux:modal wire:model="modalSala" class="max-w-lg w-full space-y-5">
        <div>
            <flux:heading size="lg">Configurar Sala y Plantilla de Butacas</flux:heading>
            <flux:subheading>Edita el nombre de la sala, su sede, dimensiones y la disposición de sus escaleras.</flux:subheading>
        </div>

        <form wire:submit="guardarSala" class="space-y-4">
            <flux:field>
                <flux:label>Nombre o Número de la Sala</flux:label>
                <flux:input wire:model="sala_numero" placeholder="Ej. Sala 1 (3D / XD)" required />
                <flux:error name="sala_numero" />
            </flux:field>

            <flux:field>
                <flux:label>Cine / Sede</flux:label>
                <flux:select wire:model="sala_sede">
                    <option value="Cine+ Alcázar (Miraflores)">Cine+ Alcázar (Miraflores)</option>
                    <option value="Cine+ Mall del Sur (SJM)">Cine+ Mall del Sur (SJM)</option>
                    <option value="Cine+ San Miguel">Cine+ San Miguel</option>
                    <option value="Cine+ Salaverry (Jesús María)">Cine+ Salaverry (Jesús María)</option>
                    <option value="Cine+ Norte (Independencia)">Cine+ Norte (Independencia)</option>
                </flux:select>
                <flux:error name="sala_sede" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Filas (A, B, C...)</flux:label>
                    <flux:input type="number" wire:model.live="sala_filas" min="1" max="26" required />
                    <flux:error name="sala_filas" />
                </flux:field>

                <flux:field>
                    <flux:label>Columnas por Fila</flux:label>
                    <flux:input type="number" wire:model.live="sala_asientos_por_fila" min="1" max="30" required />
                    <flux:error name="sala_asientos_por_fila" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>🪜 Plantilla de Distribución y Escaleras</flux:label>
                <select wire:model.live="sala_plantilla" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg p-2.5 text-sm text-zinc-200">
                    @foreach ($catalogoPlantillas as $kPlant => $infoPlant)
                        <option value="{{ $kPlant }}">{{ $infoPlant['icono'] }} {{ $infoPlant['nombre'] }} ({{ $infoPlant['preview'] }})</option>
                    @endforeach
                </select>
                @if (isset($catalogoPlantillas[$sala_plantilla]))
                    <p class="text-[11px] text-amber-400/90 mt-1">{{ $catalogoPlantillas[$sala_plantilla]['descripcion'] }}</p>
                @endif
            </flux:field>

            <div class="p-3 bg-zinc-950 border border-zinc-800 rounded-xl text-xs text-zinc-300">
                Matriz base: <strong class="text-white font-mono">{{ (int) $sala_filas * (int) $sala_asientos_por_fila }} posiciones</strong>
                (Filas A hasta {{ range('A', 'Z')[max(0, min(25, (int) $sala_filas - 1))] }}, {{ (int) $sala_asientos_por_fila }} columnas).
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="subtle" wire:click="$set('modalSala', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="guardarSala">Actualizar Sala y Plantilla</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
