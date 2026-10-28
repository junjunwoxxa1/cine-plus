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

                <!-- SECCIÓN DE HORARIOS Y FUNCIONES DISPONIBLES CON FILTRADO EN TIEMPO REAL -->
                <div class="bg-gray-900/80 border border-gray-800 rounded-3xl p-5 sm:p-7 space-y-6 shadow-2xl"
                    x-data="{
                        sedeActiva: '{{ $filtroSede ?: 'all' }}',
                        diaActivo: '{{ $filtroDia ?: 'all' }}',
                        cambiarSede(s) { this.sedeActiva = s; },
                        cambiarDia(d) { this.diaActivo = d; },
                        limpiarFiltros() { this.sedeActiva = 'all'; this.diaActivo = 'all'; },
                        cumpleSede(sede) {
                            return this.sedeActiva === 'all' || this.sedeActiva === '' || this.sedeActiva === sede;
                        },
                        cumpleDia(fechaYmd, esHoy, esManana) {
                            if (this.diaActivo === 'all' || this.diaActivo === '') return true;
                            if (this.diaActivo === 'hoy') return esHoy;
                            if (this.diaActivo === 'manana') return esManana;
                            return this.diaActivo === fechaYmd;
                        }
                    }">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-800 pb-5">
                        <div class="flex items-center space-x-3">
                            <div class="w-1.5 h-6 bg-gradient-to-b from-amber-400 to-red-600 rounded-full"></div>
                            <div>
                                <h2 class="text-xl font-bold text-white tracking-tight">Horarios Disponibles</h2>
                                <p class="text-xs text-gray-400">Selecciona tu sede y día en tiempo real para ver las salas y elegir tus butacas.</p>
                            </div>
                        </div>

                        <button type="button" x-show="sedeActiva !== 'all' || diaActivo !== 'all'" @click="limpiarFiltros()"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-red-400 hover:text-red-300 bg-red-950/40 border border-red-800/60 px-3 py-1.5 rounded-xl transition-all cursor-pointer"
                            x-cloak>
                            <span>✕</span>
                            <span>Ver Todos los Horarios</span>
                        </button>
                    </div>

                    <!-- BARRA DE FILTRADO DINÁMICO EN TIEMPO REAL (Solo Sede y Día) -->
                    <div class="space-y-4 bg-gray-950/60 border border-gray-800/80 p-4 rounded-2xl">
                        
                        {{-- Filtro por Sede --}}
                        @if ($sedesDisponibles->isNotEmpty())
                            <div class="space-y-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">📍 Seleccionar Sede:</span>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="cambiarSede('all')"
                                        :class="sedeActiva === 'all' || sedeActiva === '' ? 'bg-red-600 text-white shadow-md shadow-red-900/30 font-bold' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800'"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition-all cursor-pointer">
                                        Todas las Sedes
                                    </button>
                                    @foreach ($sedesDisponibles as $sedeNombre)
                                        <button type="button" @click="cambiarSede('{{ $sedeNombre }}')"
                                            :class="sedeActiva === '{{ $sedeNombre }}' ? 'bg-red-600 text-white shadow-md shadow-red-900/30 font-bold' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800'"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition-all cursor-pointer">
                                            {{ $sedeNombre }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Filtro por Día --}}
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">📅 Seleccionar Día:</span>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="cambiarDia('all')"
                                    :class="diaActivo === 'all' || diaActivo === '' ? 'bg-red-600 text-white shadow-md shadow-red-900/30 font-bold' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800'"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition-all cursor-pointer">
                                    Todos los Días
                                </button>
                                <button type="button" @click="cambiarDia('hoy')"
                                    :class="diaActivo === 'hoy' ? 'bg-red-600 text-white shadow-md shadow-red-900/30 font-bold' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800'"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition-all cursor-pointer">
                                    Hoy
                                </button>
                                <button type="button" @click="cambiarDia('manana')"
                                    :class="diaActivo === 'manana' ? 'bg-red-600 text-white shadow-md shadow-red-900/30 font-bold' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800'"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition-all cursor-pointer">
                                    Mañana
                                </button>
                                @foreach ($fechasDisponibles as $fDisp)
                                    @php
                                        $carbonF = \Carbon\Carbon::parse($fDisp);
                                        $esHoy = $carbonF->isToday();
                                        $esManana = $carbonF->isTomorrow();
                                    @endphp
                                    @if (!$esHoy && !$esManana)
                                        <button type="button" @click="cambiarDia('{{ $fDisp }}')"
                                            :class="diaActivo === '{{ $fDisp }}' ? 'bg-red-600 text-white shadow-md shadow-red-900/30 font-bold' : 'bg-gray-900 border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800'"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-medium capitalize transition-all cursor-pointer">
                                            {{ $carbonF->locale('es')->isoFormat('ddd D MMM') }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                    </div>

                    <!-- LISTADO DE FUNCIONES AGRUPADAS POR SEDE, DÍA Y SALA CON REACTIVIDAD A TIEMPO REAL -->
                    @forelse ($funcionesAgrupadas as $nombreSede => $diasDeSede)
                        <div class="border border-gray-800 bg-gray-950/40 rounded-2xl p-5 space-y-5 transition-all duration-300"
                            x-show="cumpleSede('{{ $nombreSede }}')"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-98"
                            x-transition:enter-end="opacity-100 transform scale-100">

                            <div class="flex items-center space-x-2 text-base font-extrabold text-white">
                                <span class="text-red-500">📍</span>
                                <span>Complejo: {{ $nombreSede }}</span>
                            </div>

                            @foreach ($diasDeSede as $fechaYmd => $salasDeFecha)
                                @php
                                    $carbonFecha = \Carbon\Carbon::parse($fechaYmd);
                                    $esDiaHoy = $carbonFecha->isToday();
                                    $esDiaManana = $carbonFecha->isTomorrow();
                                @endphp
                                <div class="bg-gray-900/60 border border-gray-800/80 rounded-xl p-4 space-y-4 transition-all duration-200"
                                    x-show="cumpleDia('{{ $fechaYmd }}', {{ $esDiaHoy ? 'true' : 'false' }}, {{ $esDiaManana ? 'true' : 'false' }})"
                                    x-transition>

                                    <div class="flex items-center justify-between border-b border-gray-800/60 pb-2">
                                        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                            <span>📅</span>
                                            <span>{{ $esDiaHoy ? 'Hoy, ' : ($esDiaManana ? 'Mañana, ' : '') }}{{ $carbonFecha->locale('es')->isoFormat('dddd D [de] MMMM') }}</span>
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
                            <h3 class="text-base font-bold text-white">No hay funciones disponibles en este momento</h3>
                            <p class="text-xs text-gray-400">Pronto se programarán nuevas funciones para esta película.</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </main>
</x-layouts::auth.clear>
