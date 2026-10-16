<x-layouts::auth.clear title="Cartelera y Horarios - Cine+" :title="__('Cartelera y Horarios - Cine+')">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-10 space-y-14">
        
        {{-- Banner Carrusel Principal de Películas Destacadas (Editable por Admin) --}}
        <div x-data="{
                activeSlide: 0,
                totalSlides: {{ $peliculasBanner->count() }},
                autoplayTimer: null,
                init() {
                    this.startAutoplay();
                },
                startAutoplay() {
                    if (this.totalSlides > 1) {
                        this.autoplayTimer = setInterval(() => {
                            this.next();
                        }, 6000);
                    }
                },
                stopAutoplay() {
                    if (this.autoplayTimer) clearInterval(this.autoplayTimer);
                },
                next() {
                    this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
                },
                prev() {
                    this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
                },
                goTo(index) {
                    this.activeSlide = index;
                }
            }"
            @mouseenter="stopAutoplay()"
            @mouseleave="startAutoplay()"
            class="relative overflow-hidden rounded-3xl border border-red-500/20 bg-gray-950 shadow-[0_0_60px_-15px_rgba(220,38,38,0.35)] min-h-[410px] sm:h-[440px] group">

            {{-- Diapositivas de Películas Seleccionadas por el Administrador --}}
            @foreach ($peliculasBanner as $index => $pelicula)
                <div x-show="activeSlide === {{ $index }}"
                    @if ($index > 0) x-cloak @endif
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 scale-105"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute inset-0 overflow-hidden bg-gray-950">

                    {{-- Fondo Cinemático de Alto Impacto con la Imagen de la Película --}}
                    <div class="absolute inset-0 pointer-events-none overflow-hidden">
                        {{-- Imagen expandida en la mitad derecha con máscara de degradado --}}
                        <img src="{{ $pelicula->imagen_url }}" alt="{{ $pelicula->titulo }}"
                            class="absolute right-0 top-0 w-full sm:w-3/4 h-full object-cover object-center opacity-30 sm:opacity-45 scale-105 transition-transform duration-1000 group-hover:scale-110">
                        {{-- Resplandor ambiental rojo y ámbar --}}
                        <div class="absolute -top-24 -left-24 w-96 h-96 bg-red-600/20 rounded-full blur-3xl"></div>
                        <div class="absolute -bottom-24 right-1/4 w-80 h-80 bg-amber-500/15 rounded-full blur-3xl"></div>
                        {{-- Capas de degradado para lectura perfecta --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-950/95 via-55% to-gray-950/30"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/40 to-gray-950/70"></div>
                    </div>

                    {{-- Contenido Promocional de la Película --}}
                    <div class="relative z-10 h-full px-5 sm:px-14 pt-5 pb-12 sm:py-8 flex items-center justify-between gap-6 sm:gap-12">
                        {{-- Columna Izquierda: Promoción, Título, Género, Duración y Sinopsis --}}
                        <div class="w-full max-w-2xl space-y-3.5 sm:space-y-5">
                            {{-- Cabecera en Móvil (Póster miniatura + Badges + Título) / Cabecera en Desktop --}}
                            <div class="flex items-start gap-3.5 sm:block sm:space-y-4">
                                {{-- Póster visible en celulares (Clic para ir a funciones) --}}
                                <a href="{{ route('peliculas.show', $pelicula) }}"
                                    title="Ver funciones de {{ $pelicula->titulo }}"
                                    class="sm:hidden shrink-0 w-20 h-28 rounded-xl overflow-hidden border border-white/20 shadow-xl bg-gray-900 block">
                                    <img src="{{ $pelicula->imagen_url }}" alt="{{ $pelicula->titulo }}" class="w-full h-full object-cover">
                                </a>

                                <div class="space-y-2 sm:space-y-4 min-w-0 flex-1">
                                    {{-- Etiquetas Superiores de Promoción --}}
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2.5">
                                        <span class="inline-flex items-center gap-1.5 sm:gap-2 bg-gradient-to-r from-red-600 to-amber-600 text-white text-[10px] sm:text-[11px] font-black uppercase tracking-widest px-3 sm:px-4 py-1 sm:py-1.5 rounded-full shadow-lg shadow-red-600/30">
                                            <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-white animate-ping"></span>
                                            <span>🔥 Gran Estreno</span>
                                        </span>

                                        @if ($pelicula->fecha_estreno)
                                            <span class="bg-white/10 backdrop-blur-md text-amber-300 border border-amber-400/30 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full">
                                                📅 {{ $pelicula->fecha_estreno->translatedFormat('d M Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Título Monumental --}}
                                    <h1 class="text-2xl sm:text-5xl lg:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-br from-white via-gray-100 to-gray-400 tracking-tight leading-tight sm:leading-[1.08] drop-shadow-sm line-clamp-2">
                                        <a href="{{ route('peliculas.show', $pelicula) }}" class="hover:opacity-90 transition-opacity">
                                            {{ $pelicula->titulo }}
                                        </a>
                                    </h1>
                                </div>
                            </div>

                            {{-- Ficha Técnica Promocional: Género, Hora/Duración, Clasificación, Idioma --}}
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2.5">
                                @if ($pelicula->genero)
                                    <div class="inline-flex items-center gap-1 sm:gap-1.5 bg-red-500/15 border border-red-500/30 text-red-300 text-[11px] sm:text-xs font-extrabold px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-xl backdrop-blur-md">
                                        <span>🎬</span>
                                        <span>{{ $pelicula->genero }}</span>
                                    </div>
                                @endif

                                @if ($pelicula->duracion)
                                    <div class="inline-flex items-center gap-1 sm:gap-1.5 bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[11px] sm:text-xs font-extrabold px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-xl backdrop-blur-md">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ intdiv($pelicula->duracion, 60) }}h {{ $pelicula->duracion % 60 }}m ({{ $pelicula->duracion }} min)</span>
                                    </div>
                                @endif

                                @if ($pelicula->clasificacion)
                                    <div class="inline-flex items-center gap-1 bg-gray-900/90 border border-gray-700 text-gray-200 text-[11px] sm:text-xs font-black px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-xl">
                                        <span>🎟️ {{ $pelicula->clasificacion }}</span>
                                    </div>
                                @endif

                                @if ($pelicula->idioma)
                                    <div class="inline-flex items-center gap-1 bg-gray-900/90 border border-gray-700 text-gray-300 text-[11px] sm:text-xs font-bold px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-xl">
                                        <span>🎧 {{ $pelicula->idioma }}</span>
                                    </div>
                                @endif

                                @if ($pelicula->director)
                                    <div class="hidden md:inline-flex items-center gap-1.5 bg-gray-900/80 border border-gray-800 text-gray-400 text-xs font-semibold px-3 py-1.5 rounded-xl">
                                        <span>🎥 Dir. {{ $pelicula->director }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Sinopsis Destacada con Estilo Editorial --}}
                            @if ($pelicula->descripcion)
                                <div class="relative pl-3.5 sm:pl-4 border-l-4 border-red-500/80 bg-gray-900/50 backdrop-blur-sm py-2 sm:py-2.5 pr-3.5 sm:pr-4 rounded-r-2xl max-w-xl">
                                    <p class="text-xs sm:text-sm md:text-base text-gray-200 leading-relaxed line-clamp-3 sm:line-clamp-4 font-normal">
                                        {{ $pelicula->descripcion }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Columna Derecha: Póster Oficial Iluminado en Vitrina (Clic directo para ver funciones) --}}
                        <div class="hidden sm:flex shrink-0 items-center justify-center relative pr-2 lg:pr-6">
                            {{-- Halo luminoso detrás del póster --}}
                            <div class="absolute -inset-3 bg-gradient-to-tr from-red-600/40 via-amber-500/25 to-transparent rounded-3xl blur-xl opacity-80 group-hover:opacity-100 transition-opacity duration-500"></div>

                            <a href="{{ route('peliculas.show', $pelicula) }}"
                                title="Clic para ver funciones de {{ $pelicula->titulo }}"
                                class="relative w-48 sm:w-52 md:w-64 h-72 sm:h-76 md:h-88 rounded-2xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.95)] border-2 border-white/15 hover:border-red-500/60 bg-gray-900 transform transition-all duration-700 group-hover:scale-[1.03] group-hover:-rotate-1 block cursor-pointer">
                                <img src="{{ $pelicula->imagen_url }}" alt="{{ $pelicula->titulo }}"
                                    class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-950/90 via-transparent to-transparent"></div>
                                <div class="absolute top-3 right-3">
                                    <span class="bg-red-600 text-white text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg shadow-lg">
                                        Exclusivo Cine+
                                    </span>
                                </div>
                                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-xs text-white font-bold">
                                    <span class="bg-black/75 backdrop-blur-md px-2.5 py-1 rounded-lg border border-white/15 text-[11px] text-amber-300">
                                        ⭐ Destacada
                                    </span>
                                    <span class="bg-black/75 backdrop-blur-md px-2.5 py-1 rounded-lg border border-white/15 text-[11px]">
                                        {{ $pelicula->duracion }} min
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Controles de Navegación del Carrusel (Adaptados para móvil sin tapar texto) --}}
            @if ($peliculasBanner->count() > 1)
                <button type="button" @click="prev()"
                    aria-label="Película anterior"
                    class="absolute left-3 bottom-2.5 sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-950/85 hover:bg-red-600 text-white border border-white/15 hover:border-red-500 flex items-center justify-center transition-all z-20 cursor-pointer backdrop-blur-md opacity-90 sm:opacity-70 group-hover:opacity-100 shadow-xl hover:scale-110">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" @click="next()"
                    aria-label="Siguiente película"
                    class="absolute right-3 bottom-2.5 sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-950/85 hover:bg-red-600 text-white border border-white/15 hover:border-red-500 flex items-center justify-center transition-all z-20 cursor-pointer backdrop-blur-md opacity-90 sm:opacity-70 group-hover:opacity-100 shadow-xl hover:scale-110">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>

                {{-- Indicadores Inferiores --}}
                <div class="absolute bottom-4 sm:bottom-3.5 left-12 right-12 sm:left-0 sm:right-0 z-20 flex items-center justify-center gap-2 sm:gap-2.5 px-4">
                    @foreach ($peliculasBanner as $idx => $pelicula)
                        <button type="button" @click="goTo({{ $idx }})"
                            :class="activeSlide === {{ $idx }} ? 'w-8 sm:w-10 bg-gradient-to-r from-red-500 to-amber-500 shadow-lg shadow-red-500/50' : 'w-2 sm:w-2.5 bg-white/25 hover:bg-white/50'"
                            title="{{ $pelicula->titulo }}"
                            class="h-2 rounded-full transition-all duration-300 cursor-pointer"></button>
                    @endforeach
                </div>
            @endif

        </div>

        {{-- Sección: Top Destacadas de la Semana --}}
        @if ($peliculas->isNotEmpty())
            <section class="space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                    <div class="flex items-center space-x-3">
                        <div class="w-1.5 h-7 bg-gradient-to-b from-amber-400 to-red-600 rounded-full"></div>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white">🔥 Películas Más Populares</h2>
                    </div>
                    <span class="text-xs font-semibold text-gray-400 hidden sm:inline">Selección de la semana</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
                    @foreach ($peliculas->take(5) as $pelicula)
                        <a href="{{ route('peliculas.show', $pelicula) }}" class="group space-y-2.5 cursor-pointer">
                            <div class="border border-gray-800 aspect-[3/4] rounded-2xl bg-gray-950 flex items-center justify-center relative overflow-hidden group-hover:border-red-500/50 transition-all shadow-lg group-hover:shadow-red-900/20">
                                @if ($pelicula->imagen)
                                    <img src="{{ $pelicula->imagen_url }}" alt="{{ $pelicula->titulo }}"
                                        class="w-full h-full object-cover rounded-2xl group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <span class="text-3xl text-gray-700">🎬</span>
                                @endif
                                
                                @if ($pelicula->clasificacion)
                                    <span class="absolute top-2 left-2 bg-gray-950/80 backdrop-blur-sm text-white text-[9px] font-bold px-1.5 py-0.5 rounded">
                                        {{ $pelicula->clasificacion }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-sm leading-tight text-white group-hover:text-red-400 transition-colors truncate">
                                    {{ $pelicula->titulo }}
                                </h3>
                                <p class="text-[11px] text-gray-400 font-medium mt-0.5 truncate">{{ $pelicula->genero }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Sección Principal: Cartelera Interactiva en Tiempo Real con Filtros --}}
        <section id="cartelera" class="space-y-6">
            <div class="flex items-center space-x-3 pb-3 border-b border-gray-800">
                <div class="w-1.5 h-7 bg-gradient-to-b from-amber-400 to-red-600 rounded-full"></div>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-white">Cartelera Completa</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Filtra por cine/sede, fecha o escribe el nombre de la película.</p>
                </div>
            </div>

            {{-- Componente Livewire de Cartelera Reactiva --}}
            <livewire:cartelera />
        </section>

    </main>
</x-layouts::auth.clear>
