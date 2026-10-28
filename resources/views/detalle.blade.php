<x-layouts::auth.clear :title="$pelicula->titulo . ' - Horarios y Funciones - Cine+'">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6 sm:space-y-8">
        <div>
            <a href="{{ route('home') }}"
                class="inline-flex items-center space-x-2 text-sm font-semibold text-gray-400 hover:text-white transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                <span class="underline underline-offset-4">Volver a Cartelera</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-10 items-start">
            <!-- Póster de la Película -->
            <div class="md:col-span-4 lg:col-span-4 max-w-[230px] sm:max-w-xs mx-auto md:max-w-none w-full">
                <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-3 sm:p-4 shadow-xl relative overflow-hidden group">
                    <div class="border border-gray-800 aspect-[3/4] rounded-xl bg-gray-900 flex items-center justify-center relative overflow-hidden">
                        @if ($pelicula->imagen)
                            <img src="{{ $pelicula->imagen_url }}" alt="{{ $pelicula->titulo }}"
                                class="w-full h-full object-cover rounded-xl">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-700 text-5xl">🎬</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información de la Película y Horarios -->
            <div class="md:col-span-8 lg:col-span-8 space-y-6 sm:space-y-8">

                <!-- Encabezado e Insignias -->
                <div class="space-y-3 sm:space-y-4 text-center md:text-left">
                    <div class="flex flex-wrap justify-center md:justify-start gap-2 text-xs font-bold">
                        @if ($pelicula->clasificacion)
                            <span class="border border-gray-700 bg-gray-900 text-gray-300 px-3 py-1 rounded-md">
                                {{ $pelicula->clasificacion }}
                            </span>
                        @endif

                        @if ($pelicula->genero)
                            <span class="border border-gray-700 bg-gray-900 text-gray-300 px-3 py-1 rounded-md">
                                {{ $pelicula->genero }}
                            </span>
                        @endif

                        @if ($pelicula->duracion)
                            <span class="border border-gray-700 bg-gray-900 text-gray-300 px-3 py-1 rounded-md">
                                {{ $pelicula->duracion }} min
                            </span>
                        @endif

                        @if ($pelicula->idioma)
                            <span class="border border-gray-700 bg-gray-900 text-gray-300 px-3 py-1 rounded-md">
                                {{ $pelicula->idioma }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white">
                        {{ $pelicula->titulo }}
                    </h1>

                    @if ($pelicula->director)
                        <p class="text-sm font-medium text-gray-400">
                            Director: <span class="text-gray-200">{{ $pelicula->director }}</span>
                        </p>
                    @endif
                </div>

                <!-- Sección Sinopsis -->
                <div class="space-y-2.5 sm:space-y-3">
                    <h3 class="text-xs font-bold tracking-wider text-gray-400 uppercase">SINOPSIS</h3>
                    <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 sm:p-6 text-gray-300 text-xs sm:text-sm leading-relaxed shadow-lg">
                        {{ $pelicula->descripcion ?? 'Sin descripción disponible por el momento.' }}
                    </div>
                </div>

                <!-- SECCIÓN DE HORARIOS Y FUNCIONES DISPONIBLES CON FILTROS -->
                <div class="bg-gray-900/80 border border-gray-800 rounded-3xl p-5 sm:p-7 space-y-6 shadow-2xl">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-800 pb-5">
                        <div class="flex items-center space-x-3">
                            <div class="w-1.5 h-6 bg-gradient-to-b from-amber-400 to-red-600 rounded-full"></div>
                            <div>
                                <h2 class="text-xl font-bold text-white tracking-tight">Horarios Disponibles</h2>
                                <p class="text-xs text-gray-400">Selecciona tu horario preferido para elegir butacas.</p>
                            </div>
                        </div>

                        @if ($filtroSede || $filtroDia || $filtroTurno)
                            <a href="{{ route('peliculas.show', $pelicula) }}"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-red-400 hover:text-red-300 bg-red-950/40 border border-red-800/60 px-3 py-1.5 rounded-xl transition-all">
                                <span>✕</span>
                                <span>Limpiar Filtros</span>
                            </a>
                        @endif
                    </div>

                    <!-- BARRA DE FILTRADO DINÁMICO (Sede, Día y Turno de Horario) -->
                    <div class="space-y-3.5 bg-gray-950/60 border border-gray-800/80 p-4 rounded-2xl">
                        
                        {{-- Filtro por Sede --}}
                        @if ($sedesDisponibles->isNotEmpty())
                            <div class="space-y-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">📍 Seleccionar Sede:</span>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => null, 'dia' => $filtroDia ?: null, 'turno' => $filtroTurno ?: null])) }}"
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ empty($filtroSede) || $filtroSede === 'all' ? 'bg-red-600 text-white shadow-md shadow-red-900/30' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800' }}">
                                        Todas las Sedes
                                    </a>
                                    @foreach ($sedesDisponibles as $sedeNombre)
                                        <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => $sedeNombre, 'dia' => $filtroDia ?: null, 'turno' => $filtroTurno ?: null])) }}"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filtroSede === $sedeNombre ? 'bg-red-600 text-white shadow-md shadow-red-900/30' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800' }}">
                                            {{ $sedeNombre }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Filtro por Día --}}
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">📅 Seleccionar Día:</span>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => $filtroSede ?: null, 'dia' => null, 'turno' => $filtroTurno ?: null])) }}"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ empty($filtroDia) || $filtroDia === 'all' ? 'bg-red-600 text-white shadow-md shadow-red-900/30' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800' }}">
                                    Todos los Días
                                </a>
                                <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => $filtroSede ?: null, 'dia' => 'hoy', 'turno' => $filtroTurno ?: null])) }}"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filtroDia === 'hoy' ? 'bg-red-600 text-white shadow-md shadow-red-900/30' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800' }}">
                                    Hoy
                                </a>
                                <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => $filtroSede ?: null, 'dia' => 'manana', 'turno' => $filtroTurno ?: null])) }}"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filtroDia === 'manana' ? 'bg-red-600 text-white shadow-md shadow-red-900/30' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800' }}">
                                    Mañana
                                </a>
                                @foreach ($fechasDisponibles as $fDisp)
                                    @php
                                        $carbonF = \Carbon\Carbon::parse($fDisp);
                                        $esHoy = $carbonF->isToday();
                                        $esManana = $carbonF->isTomorrow();
                                    @endphp
                                    @if (!$esHoy && !$esManana)
                                        <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => $filtroSede ?: null, 'dia' => $fDisp, 'turno' => $filtroTurno ?: null])) }}"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold capitalize transition-all {{ $filtroDia === $fDisp ? 'bg-red-600 text-white shadow-md shadow-red-900/30' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800' }}">
                                            {{ $carbonF->locale('es')->isoFormat('ddd D MMM') }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- Filtro por Turno de Horario --}}
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">⏰ Filtrar por Horario / Turno:</span>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => $filtroSede ?: null, 'dia' => $filtroDia ?: null, 'turno' => null])) }}"
                                    class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ empty($filtroTurno) || $filtroTurno === 'all' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold' : 'bg-gray-900 text-gray-400 border border-gray-800 hover:text-white' }}">
                                    Todos los Horarios
                                </a>
                                <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => $filtroSede ?: null, 'dia' => $filtroDia ?: null, 'turno' => 'manana'])) }}"
                                    class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $filtroTurno === 'manana' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold' : 'bg-gray-900 text-gray-400 border border-gray-800 hover:text-white' }}">
                                    🌅 Mañana (Antes de 14:00)
                                </a>
                                <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => $filtroSede ?: null, 'dia' => $filtroDia ?: null, 'turno' => 'tarde'])) }}"
                                    class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $filtroTurno === 'tarde' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold' : 'bg-gray-900 text-gray-400 border border-gray-800 hover:text-white' }}">
                                    🌇 Tarde (14:00 - 18:59)
                                </a>
                                <a href="{{ route('peliculas.show', array_filter(['pelicula' => $pelicula->slug, 'sede' => $filtroSede ?: null, 'dia' => $filtroDia ?: null, 'turno' => 'noche'])) }}"
                                    class="px-3 py-1 rounded-lg text-xs font-medium transition-all {{ $filtroTurno === 'noche' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold' : 'bg-gray-900 text-gray-400 border border-gray-800 hover:text-white' }}">
                                    🌙 Noche (19:00 en adelante)
                                </a>
                            </div>
                        </div>

                    </div>

                    <!-- LISTADO DE FUNCIONES AGRUPADAS POR SEDE, DÍA Y SALA -->
                    @forelse ($funcionesAgrupadas as $nombreSede => $diasDeSede)
                        <div class="border border-gray-800 bg-gray-950/40 rounded-2xl p-5 space-y-5">
                            <div class="flex items-center space-x-2 text-base font-extrabold text-white">
                                <span class="text-red-500">📍</span>
                                <span>Complejo: {{ $nombreSede }}</span>
                            </div>

                            @foreach ($diasDeSede as $fechaYmd => $salasDeFecha)
                                @php
                                    $carbonFecha = \Carbon\Carbon::parse($fechaYmd);
                                @endphp
                                <div class="bg-gray-900/60 border border-gray-800/80 rounded-xl p-4 space-y-4">
                                    <div class="flex items-center justify-between border-b border-gray-800/60 pb-2">
                                        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                            <span>📅</span>
                                            <span>{{ $carbonFecha->isToday() ? 'Hoy, ' : ($carbonFecha->isTomorrow() ? 'Mañana, ' : '') }}{{ $carbonFecha->locale('es')->isoFormat('dddd D [de] MMMM') }}</span>
                                        </span>
                                    </div>

                                    @foreach ($salasDeFecha as $salaId => $funcionesDeSala)
                                        @php
                                            $sala = $funcionesDeSala->first()->sala;
                                        @endphp
                                        <div class="space-y-2.5">
                                            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-300">
                                                <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span>{{ $sala->numero_sala }}</span>
                                            </div>

                                            <!-- Horarios disponibles de esta sala -->
                                            <div class="flex flex-wrap gap-3">
                                                @foreach ($funcionesDeSala as $funcion)
                                                    <a href="{{ route('reservas.butacas', $funcion) }}"
                                                        class="group border border-gray-800 bg-gray-900 hover:border-red-500 hover:bg-red-600 px-4 py-2.5 rounded-xl transition-all duration-200 shadow-md flex items-center space-x-3 cursor-pointer">
                                                        <span class="text-sm font-black text-white group-hover:text-white font-mono">
                                                            {{ $funcion->fecha_hora->format('H:i') }}
                                                        </span>
                                                        <div class="text-right border-l border-gray-800 group-hover:border-red-500/60 pl-2.5">
                                                            <span class="text-[10px] block text-green-400 group-hover:text-white font-bold leading-none">
                                                                {{ $funcion->asientos_disponibles }} libres
                                                            </span>
                                                            <span class="text-[9px] block text-gray-400 group-hover:text-red-100 font-mono mt-0.5 leading-none">
                                                                S/ {{ number_format($funcion->precio, 2) }}
                                                            </span>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="p-8 text-center border border-dashed border-gray-800 rounded-2xl space-y-3 bg-gray-950/40">
                            <span class="text-3xl block">🎟️</span>
                            <h3 class="text-base font-bold text-white">No hay horarios disponibles con los filtros seleccionados</h3>
                            <p class="text-xs text-gray-400">Prueba cambiando la sede, el día o el turno de horario para ver otras proyecciones.</p>
                            <a href="{{ route('peliculas.show', $pelicula) }}"
                                class="inline-block bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase px-4 py-2 rounded-xl transition-colors mt-2">
                                Ver Todos los Horarios
                            </a>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </main>
</x-layouts::auth.clear>
