<div class="space-y-8 relative z-20">

    {{-- Panel de Filtros y Buscador en Tiempo Real --}}
    <div class="relative z-30 border border-gray-800 bg-gray-900/90 backdrop-blur-md rounded-3xl p-5 sm:p-7 shadow-2xl space-y-6">

        {{-- Fila Superior: Buscador + Filtro por Sede + Filtro por Género --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
            
            {{-- 1. Buscador en Tiempo Real con Sugerencias (Único Buscador) --}}
            <div class="md:col-span-5 relative z-40" x-data="{ open: true }" @click.outside="open = false">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 z-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.200ms="search"
                    @focus="open = true; $wire.set('mostrarSugerencias', true)"
                    placeholder="Buscar película, género, director..."
                    class="w-full bg-gray-950 border border-gray-800 rounded-2xl pl-11 pr-10 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all shadow-inner">
                
                @if ($search !== '')
                    <button type="button" wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-white cursor-pointer z-10">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                @endif

                {{-- Menú Desplegable de Sugerencias --}}
                @if ($mostrarSugerencias && $sugerencias->isNotEmpty())
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute left-0 right-0 top-full mt-2 bg-gray-900 border border-gray-700 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.85)] z-50 overflow-hidden divide-y divide-gray-800 backdrop-blur-2xl">
                        
                        <div class="px-3.5 py-2 bg-gray-950/60 flex items-center justify-between text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                            <span>💡 Sugerencias de películas</span>
                            <span class="text-[10px] text-gray-500 font-normal">Click para filtrar o ir</span>
                        </div>

                        <div class="max-h-80 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-800">
                            @foreach ($sugerencias as $sug)
                                <div class="p-2.5 hover:bg-gray-800/90 transition-colors flex items-center justify-between gap-3 group">
                                    <button type="button" 
                                        wire:click="seleccionarSugerencia('{{ addslashes($sug->titulo) }}')"
                                        @click="open = false"
                                        class="flex items-center gap-3 text-left flex-1 min-w-0 cursor-pointer">
                                        <div class="w-10 h-14 rounded-lg bg-gray-950 border border-gray-800 overflow-hidden shrink-0 flex items-center justify-center">
                                            @if ($sug->imagen)
                                                <img src="{{ $sug->imagen_url }}" alt="{{ $sug->titulo }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-xs">🎬</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-white group-hover:text-red-400 transition-colors truncate">
                                                {{ $sug->titulo }}
                                            </p>
                                            <p class="text-xs text-gray-400 truncate flex items-center gap-2 mt-0.5">
                                                <span class="text-red-400 font-medium">{{ $sug->genero }}</span>
                                                @if ($sug->clasificacion)
                                                    <span class="text-[10px] bg-gray-800 text-gray-300 px-1.5 py-0.2 rounded font-semibold">{{ $sug->clasificacion }}</span>
                                                @endif
                                                @if ($sug->duracion)
                                                    <span class="text-[10px] text-amber-400/90 font-medium">{{ $sug->duracion }}m</span>
                                                @endif
                                            </p>
                                        </div>
                                    </button>
                                    @php
                                        $paramsSug = ['pelicula' => $sug->slug];
                                        if ($sede !== 'all') { $paramsSug['sede'] = $sede; }
                                        if ($dia !== 'all') { $paramsSug['dia'] = $dia; }
                                    @endphp
                                    <a href="{{ route('peliculas.show', $paramsSug) }}"
                                        class="shrink-0 text-[11px] font-bold bg-red-600/20 hover:bg-red-600 text-red-300 hover:text-white border border-red-500/30 px-3 py-1.5 rounded-xl transition-all">
                                        Ver »
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. Filtro por Lugar / Sede --}}
            <div class="md:col-span-4 relative">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">🏛️ Cine / Sede</label>
                <div class="relative">
                    <select wire:model.live="sede"
                        class="w-full bg-gray-950 border border-gray-800 rounded-2xl px-4 py-2.5 text-xs font-bold text-gray-200 focus:outline-none focus:border-red-500 cursor-pointer shadow-inner appearance-none pr-8">
                        <option value="all">📍 Todas las Sedes / Cines</option>
                        @foreach ($sedes as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- 3. Filtro por Género --}}
            <div class="md:col-span-3 relative">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">🎬 Género</label>
                <div class="relative">
                    <select wire:model.live="genero"
                        class="w-full bg-gray-950 border border-gray-800 rounded-2xl px-4 py-2.5 text-xs font-bold text-gray-200 focus:outline-none focus:border-red-500 cursor-pointer shadow-inner appearance-none pr-8">
                        <option value="all">Todos los Géneros</option>
                        @foreach ($generos as $g)
                            <option value="{{ $g }}">{{ $g }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- Fila Inferior: Filtro por Día / Fecha (Píldoras Estilo Cineplanet) --}}
        <div class="space-y-2 border-t border-gray-800/80 pt-4">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold uppercase tracking-wider text-gray-400 flex items-center space-x-1.5">
                    <span>📅</span>
                    <span>Elige tu Día:</span>
                </span>
                
                @if ($search !== '' || $sede !== 'all' || $dia !== 'all' || $genero !== 'all')
                    <button type="button" wire:click="limpiarFiltros"
                        class="text-red-400 hover:text-red-300 font-bold underline cursor-pointer text-xs flex items-center gap-1">
                        <span>Restablecer Filtros</span>
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-800">
                
                {{-- Botón: Todos los días --}}
                <button type="button" wire:click="$set('dia', 'all')"
                    class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap flex flex-col items-center justify-center min-w-[70px] border {{ $dia === 'all' ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-900/40' : 'bg-gray-950 border-gray-800 text-gray-300 hover:bg-gray-800' }}">
                    <span class="text-[10px] uppercase opacity-80">Fecha</span>
                    <span class="text-sm font-black">Todas</span>
                </button>

                {{-- Píldoras de los Próximos 7 Días --}}
                @foreach ($diasDisponibles as $d)
                    <button type="button" wire:click="$set('dia', '{{ $d['clave'] }}')"
                        class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap flex flex-col items-center justify-center min-w-[75px] border {{ $dia === $d['clave'] ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-900/40' : 'bg-gray-950 border-gray-800 text-gray-300 hover:bg-gray-800 hover:border-gray-700' }}">
                        <span class="text-[10px] uppercase tracking-wider opacity-80">{{ $d['dia_semana'] }}</span>
                        <span class="text-base font-black leading-tight">{{ $d['dia_numero'] }}</span>
                        <span class="text-[9px] uppercase opacity-70">{{ $d['mes'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Indicador de Resultados Activos --}}
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-400 pt-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse shrink-0"></span>
                <span>Cartelera en vivo: <strong class="text-white">{{ $peliculas->count() }}</strong> {{ $peliculas->count() === 1 ? 'película encontrada' : 'películas encontradas' }}</span>
                @if ($sede !== 'all')
                    <span class="bg-gray-800 text-red-400 px-2 py-0.5 rounded-md font-bold">📍 {{ $sede }}</span>
                @endif
                @if ($dia !== 'all')
                    <span class="bg-gray-800 text-amber-400 px-2 py-0.5 rounded-md font-bold">📅 {{ ucfirst($dia) }}</span>
                @endif
            </div>

            <div wire:loading class="text-red-400 font-bold flex items-center space-x-1.5 animate-pulse">
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" stroke-dasharray="32" stroke-linecap="round"></circle></svg>
                <span>Buscando...</span>
            </div>
        </div>

    </div>

    {{-- Grid de Películas en Cartelera (2 columnas en móvil estilo App Cineplanet) --}}
    <div class="relative z-0 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-6">
        @forelse ($peliculas as $pelicula)
            @php
                $paramsShow = ['pelicula' => $pelicula->slug];
                if ($sede !== 'all') { $paramsShow['sede'] = $sede; }
                if ($dia !== 'all') { $paramsShow['dia'] = $dia; }
            @endphp
            <a href="{{ route('peliculas.show', $paramsShow) }}"
                class="bg-gray-900/70 border border-gray-800 rounded-2xl sm:rounded-3xl overflow-hidden flex flex-col justify-between hover:border-red-500/50 transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-red-950/20 hover:-translate-y-1.5 group cursor-pointer"
                wire:key="pelicula-{{ $pelicula->id }}">

                {{-- Póster de la Película (Clic directo para ver funciones) --}}
                <div class="relative aspect-[3/4] bg-gray-950 overflow-hidden">
                    @if ($pelicula->imagen)
                        <img src="{{ $pelicula->imagen_url }}" alt="{{ $pelicula->titulo }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-700 text-4xl">🎬</div>
                    @endif

                    {{-- Degradado sutil al pasar el cursor indicando clic para ver funciones --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-950/90 via-gray-950/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center p-4">
                        <span class="bg-black/80 backdrop-blur-md border border-white/20 text-white text-[11px] font-bold px-3.5 py-1.5 rounded-full shadow-lg flex items-center gap-1.5">
                            <span>🎟️</span>
                            <span>Ver funciones</span>
                        </span>
                    </div>

                    {{-- Badges Flotantes --}}
                    <div class="absolute top-2 left-2 sm:top-3 sm:left-3 flex flex-wrap gap-1 sm:gap-1.5">
                        @if ($pelicula->clasificacion)
                            <span class="bg-gray-950/85 backdrop-blur-md text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md border border-gray-700">
                                {{ $pelicula->clasificacion }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Información de la Película: Género, Nombre y Duración/Hora --}}
                <div class="p-3.5 sm:p-5 flex-1 flex flex-col justify-between space-y-2.5">
                    <div>
                        <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-red-500 block truncate">
                            {{ $pelicula->genero }}
                        </span>
                        <h3 class="font-bold text-sm sm:text-lg text-white group-hover:text-red-400 transition-colors leading-tight mt-0.5 sm:mt-1 line-clamp-2">
                            {{ $pelicula->titulo }}
                        </h3>
                    </div>

                    @if ($pelicula->duracion)
                        <div class="pt-2 border-t border-gray-800/70 flex items-center justify-between text-xs text-amber-400 font-bold">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ intdiv($pelicula->duracion, 60) }}h {{ $pelicula->duracion % 60 }}m ({{ $pelicula->duracion }} min)</span>
                            </span>
                        </div>
                    @endif
                </div>

            </a>
        @empty
            <div class="col-span-full border border-gray-800 bg-gray-900/50 rounded-3xl p-12 text-center space-y-4 shadow-xl">
                <div class="w-16 h-16 bg-gray-800 text-gray-400 rounded-full flex items-center justify-center mx-auto text-3xl">
                    🔍
                </div>
                <h3 class="text-xl font-bold text-white">No se encontraron películas</h3>
                <p class="text-sm text-gray-400 max-w-md mx-auto">
                    No hay resultados para los filtros seleccionados
                    @if ($search !== '') para "<strong class="text-white">{{ $search }}</strong>"@endif.
                </p>
                <div>
                    <button type="button" wire:click="limpiarFiltros"
                        class="bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-colors shadow-lg shadow-red-900/40 cursor-pointer">
                        Restablecer Filtros
                    </button>
                </div>
            </div>
        @endforelse
    </div>

</div>

