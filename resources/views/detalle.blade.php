<x-layouts::auth.clear :title="$pelicula->titulo">
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

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-10 items-start" x-data="{ horarioSeleccionado: null }">
            <!-- Póster de la Película -->
            <div class="md:col-span-4 lg:col-span-4 max-w-[230px] sm:max-w-xs mx-auto md:max-w-none w-full">
                <div
                    class="bg-gray-900/60 border border-gray-800 rounded-2xl p-3 sm:p-4 shadow-xl relative overflow-hidden group">
                    <div
                        class="border border-gray-800 aspect-[3/4] rounded-xl bg-gray-900 flex items-center justify-center relative overflow-hidden">
                        @if ($pelicula->imagen)
                            <img src="{{ $pelicula->imagen_url }}" alt="{{ $pelicula->titulo }}"
                                class="w-full h-full object-cover rounded-xl">
                        @else
                            <svg class="w-full h-full text-gray-800 stroke-current opacity-60" viewBox="0 0 100 100"
                                preserveAspectRatio="none">
                                <line x1="0" y1="0" x2="100" y2="100" stroke-width="0.5" />
                                <line x1="100" y1="0" x2="0" y2="100" stroke-width="0.5" />
                            </svg>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información de la Película y Horarios -->
            <div class="md:col-span-8 lg:col-span-8 space-y-6 sm:space-y-8">

                <!-- Encabezado e Insignias -->
                <div class="space-y-3 sm:space-y-4 text-center md:text-left">
                    <!-- Badges -->
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

                    <!-- Título -->
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
                    <div
                        class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 sm:p-6 text-gray-300 text-xs sm:text-sm leading-relaxed shadow-lg">
                        {{ $pelicula->descripcion ?? 'Sin descripción disponible por el momento.' }}
                    </div>
                </div>

                <!-- Sección Horarios y Salas -->
                <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 sm:p-6 space-y-5 sm:space-y-6 shadow-lg">
                    <div class="flex items-center space-x-3 border-b border-gray-800 pb-4">
                        <div class="w-1.5 h-6 bg-gradient-to-b from-amber-400 to-red-600 rounded-full"></div>
                        <h2 class="text-xl font-bold text-white tracking-tight">Horarios y Salas</h2>
                    </div>
                    @forelse ($pelicula->funciones->groupBy('sala_id') as $salaId => $funcionesPorSala)
                        @php
                            $sala = $funcionesPorSala->first()->sala;
                        @endphp
                        <div class="space-y-3 pb-4 {{ !$loop->last ? 'border-b border-gray-800/60' : '' }}">
                            <!-- Nombre de la Sala -->
                            <div class="flex items-center space-x-2 text-sm font-semibold text-gray-300">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span>{{ $sala->numero_sala }}</span>
                            </div>

                            <!-- Botones de Horario de esta Sala -->
                            <div class="flex flex-wrap gap-3">
                                @foreach ($funcionesPorSala as $funcion)
                                    @php
                                        $horaFormateada = $funcion->fecha_hora->format('H:i');
                                    @endphp
                                    {{-- <button @click="horarioSeleccionado = {{ $funcion->id }}"
                                        :class="horarioSeleccionado === {{ $funcion->id }} ?
                                            'bg-red-600 border-red-500 text-white shadow-lg shadow-red-900/40' :
                                            'bg-gray-900 border-gray-800 text-gray-200 hover:border-gray-700 hover:bg-gray-800'"
                                        class="cursor-pointer border px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
                                        {{ $horaFormateada }}
                                    </button> --}}
                                    <a href="{{ route('reservas.butacas', $funcion) }}"
                                        class="border border-gray-800 bg-gray-900 text-gray-200 hover:border-red-500 hover:bg-red-600 hover:text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 shadow-md">
                                        {{ $horaFormateada }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No hay funciones disponibles en este momento.</p>
                    @endforelse
                </div>

            </div>
        </div>
    </main>
</x-layouts::auth.clear>
