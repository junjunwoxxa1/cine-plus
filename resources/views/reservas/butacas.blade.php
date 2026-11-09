<x-layouts::auth.clear :title="'Seleccionar Butacas - ' . $funcion->pelicula->titulo">
    <style>
        [x-cloak] {
            display: none !important;
        }
        .seat-map-scroll::-webkit-scrollbar {
            height: 6px;
        }
        .seat-map-scroll::-webkit-scrollbar-track {
            background: rgba(17, 24, 39, 0.7);
            border-radius: 9999px;
        }
        .seat-map-scroll::-webkit-scrollbar-thumb {
            background: rgba(239, 68, 68, 0.45);
            border-radius: 9999px;
        }
        .seat-map-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(239, 68, 68, 0.75);
        }
    </style>

    <main class="max-w-[1440px] mx-auto px-3 sm:px-6 py-4 sm:py-6 space-y-5" x-data="reservaButacas({
        cantidadPermitida: {{ (int) ($cantidadSeleccionada ?? 2) }},
        butacasIniciales: {{ json_encode($butacasSeleccionadas ?? []) }},
        tiempoInicial: {{ (int) request('tiempo', 300) }},
        urlCancelar: '{{ route('reservas.cancelar') }}'
    })">

        <div class="flex flex-wrap items-center justify-between gap-2">
            <a href="{{ route('peliculas.show', $funcion->pelicula) }}"
                class="inline-flex items-center space-x-2 text-xs sm:text-sm font-semibold text-gray-400 hover:text-white transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                <span class="underline underline-offset-4">Cambiar Función</span>
            </a>

            <div
                @click="adelantarTiempoPrueba()"
                title="Tiempo restante de reserva (clic para simular aviso de expiración)"
                :class="tiempoRestante <= 60 ? 'border-red-500/80 bg-red-950/70 text-red-200 animate-pulse' : 'border-gray-800 bg-gray-900/80 text-gray-200'"
                class="cursor-pointer border px-3 sm:px-4 py-1.5 sm:py-2 text-xs font-bold tracking-wider rounded-xl flex items-center gap-2 shadow-lg transition-colors select-none">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                </svg>
                <span>Reserva: <span class="text-white font-mono" x-text="formatearTiempo(tiempoRestante)">05:00</span>
                    </span>
            </div>
        </div>

        @php
            $escalerasCol = $mapaConfig['escaleras_despues_columna'] ?? [];
            $pasillosFila = $mapaConfig['pasillos_despues_fila'] ?? [];
            $celdasEscalera = $mapaConfig['celdas_escalera'] ?? [];

            $maxCols = $butacasPorFila->map(fn($fila) => $fila->count())->max() ?: 10;
            $totalFilas = $butacasPorFila->count() ?: 5;
            $totalButacas = $funcion->butacas->count();

            // Calibración adaptativa según capacidad y dimensiones de la sala (ej. 30 a 300 butacas)
            if ($maxCols >= 19 || $totalButacas >= 180 || $totalFilas >= 13) {
                // Sala grande / masiva (hasta 300 asientos, ej: 12x25 o 15x20)
                $tamanoButaca = 'w-[22px] h-[22px] sm:w-[25px] sm:h-[25px] lg:w-[26px] lg:h-[26px] xl:w-[28px] xl:h-[28px]';
                $textoButaca = 'text-[7px] sm:text-[8px] lg:text-[8px] xl:text-[8.5px] font-extrabold tracking-tighter';
                $tamanoEscalera = 'w-[14px] sm:w-[18px] lg:w-[18px] xl:w-[20px] h-[22px] sm:h-[25px] lg:h-[26px] xl:h-[28px]';
                $gapColumnas = 'gap-1 sm:gap-1.5';
                $gapFilas = 'space-y-1.5 sm:space-y-2';
                $paddingCard = 'p-3 sm:p-5 lg:p-6';
                $tamanoLetraFila = 'w-3.5 sm:w-4 text-[9px] sm:text-[10px] md:text-xs';
                $tamanoPantalla = 'max-w-xl sm:max-w-2xl';
                $esSalaGrande = true;
            } elseif ($maxCols >= 12 || $totalButacas >= 80) {
                // Sala mediana (ej: 8x12 o 10x15)
                $tamanoButaca = 'w-6 h-6 sm:w-7 sm:h-7 lg:w-[30px] lg:h-[30px]';
                $textoButaca = 'text-[8.5px] sm:text-[9.5px] lg:text-[10px] font-bold';
                $tamanoEscalera = 'w-4.5 sm:w-6 h-6 sm:h-7 lg:h-[30px]';
                $gapColumnas = 'gap-1 sm:gap-1.5 sm:gap-2';
                $gapFilas = 'space-y-2 sm:space-y-2.5';
                $paddingCard = 'p-4 sm:p-6 lg:p-7';
                $tamanoLetraFila = 'w-4 text-[10px] sm:text-xs';
                $tamanoPantalla = 'max-w-lg sm:max-w-xl';
                $esSalaGrande = false;
            } else {
                // Sala estándar o pequeña (< 80 asientos)
                $tamanoButaca = 'w-7 h-7 sm:w-8 sm:h-8 lg:w-9 lg:h-9';
                $textoButaca = 'text-[10px] sm:text-xs font-bold';
                $tamanoEscalera = 'w-5 sm:w-7 h-7 sm:h-8 lg:h-9';
                $gapColumnas = 'gap-1.5 sm:gap-2';
                $gapFilas = 'space-y-2.5 sm:space-y-3.5';
                $paddingCard = 'p-4 sm:p-8 lg:p-10';
                $tamanoLetraFila = 'w-4 text-xs sm:text-sm';
                $tamanoPantalla = 'max-w-md sm:max-w-lg';
                $esSalaGrande = false;
            }
        @endphp

        <!-- Contenedor Principal de Selección y Resumen -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-start">

            <div
                class="lg:col-span-8 border border-gray-800 bg-gray-900/60 rounded-2xl {{ $paddingCard }} min-h-[380px] sm:min-h-[460px] flex flex-col justify-between items-center shadow-xl overflow-hidden">

                <!-- Pantalla -->
                <div class="w-full {{ $tamanoPantalla }} text-center space-y-2 sm:space-y-2.5">
                    <p class="text-[10px] sm:text-xs font-bold tracking-widest uppercase text-gray-400">PANTALLA DE SALA</p>
                    <div
                        class="w-full h-1.5 bg-gradient-to-r from-gray-800 via-red-600 to-gray-800 rounded-full shadow-lg shadow-red-900/30">
                    </div>
                    @if ($esSalaGrande)
                        <p class="text-[10px] text-gray-400 font-medium xl:hidden">↔ Desliza suavemente para ver todas las butacas si tu pantalla es pequeña</p>
                    @else
                        <p class="text-[10px] text-gray-500 sm:hidden">↔ Desliza horizontalmente si la sala es amplia</p>
                    @endif
                </div>

                <!-- Mapa de Asientos -->
                <div class="my-4 sm:my-6 w-full overflow-x-auto pb-3 seat-map-scroll">
                    <div class="{{ $gapFilas }} min-w-max mx-auto px-2">
                        @foreach ($butacasPorFila as $fila => $butacas)
                            @php
                                $tienePasilloDespues = in_array($fila, $pasillosFila, true);
                            @endphp
                            <div class="flex items-center justify-center space-x-1.5 sm:space-x-2">
                                <!-- Indicador Fila Izquierda -->
                                <span class="{{ $tamanoLetraFila }} text-center font-bold text-gray-400 shrink-0 select-none">{{ $fila }}</span>

                                <div class="flex flex-nowrap items-center {{ $gapColumnas }} justify-center">
                                    @foreach ($butacas->values() as $idx => $butaca)
                                        @php
                                            $numCol = $idx + 1;
                                            $esEscaleraCelda = in_array($butaca->numero_butaca, $celdasEscalera, true);
                                            $tieneEscaleraVertical = in_array($numCol, $escalerasCol, true);
                                            $esDisponible = !$esEscaleraCelda && (bool) $butaca->pivot->disponible;
                                            $codigoClean = str_replace('-', '', $butaca->numero_butaca);
                                        @endphp

                                        @if ($esEscaleraCelda)
                                            <!-- Escalera en extremo de fila -->
                                            <div title="Escalera de acceso"
                                                class="{{ $tamanoButaca }} border border-dashed border-amber-500/30 bg-amber-950/20 rounded sm:rounded-md text-amber-400/80 flex flex-col items-center justify-center shrink-0 select-none">
                                                <span class="{{ $esSalaGrande ? 'text-[8px] sm:text-[9px]' : 'text-[10px] sm:text-xs' }} leading-none">🪜</span>
                                            </div>
                                        @else
                                            <button type="button"
                                                @if ($esDisponible) @click="toggleButaca('{{ $codigoClean }}')" @endif
                                                :disabled="{{ $esDisponible ? 'false' : 'true' }}"
                                                :class="{
                                                    'border-slate-700 bg-slate-800/85 text-slate-400 cursor-not-allowed': {{ !$esDisponible ? 'true' : 'false' }},
                                                    'border-red-400 bg-red-600 text-white shadow-lg shadow-red-900/50 scale-105 z-10': {{ $esDisponible ? 'true' : 'false' }} &&
                                                        esSeleccionada('{{ $codigoClean }}'),
                                                    'border-sky-400 bg-sky-600/90 text-white hover:border-red-400 hover:bg-sky-500 shadow-sm': {{ $esDisponible ? 'true' : 'false' }} &&
                                                        !esSeleccionada('{{ $codigoClean }}')
                                                }"
                                                class="cursor-pointer {{ $tamanoButaca }} border rounded sm:rounded-md {{ $textoButaca }} flex items-center justify-center shrink-0 transition-all duration-200">
                                                {{ $codigoClean }}
                                            </button>
                                        @endif

                                        <!-- Escalera Vertical entre Columnas -->
                                        @if ($numCol < $butacas->count() && $tieneEscaleraVertical)
                                            <div title="Pasillo / Escalera"
                                                class="{{ $tamanoEscalera }} rounded bg-gray-950/80 border-x border-amber-500/25 flex flex-col items-center justify-between py-0.5 sm:py-1 shrink-0 select-none">
                                                <span class="w-1.5 sm:w-2.5 h-0.5 bg-amber-500/30 rounded"></span>
                                                <span class="{{ $esSalaGrande ? 'text-[7.5px] sm:text-[8.5px]' : 'text-[9px]' }} leading-none">🪜</span>
                                                <span class="w-1.5 sm:w-2.5 h-0.5 bg-amber-500/30 rounded"></span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Indicador Fila Derecha -->
                                <span class="{{ $tamanoLetraFila }} text-center font-bold text-gray-400 shrink-0 select-none">{{ $fila }}</span>
                            </div>

                            @if ($tienePasilloDespues)
                                <div class="my-1 sm:my-1.5 py-0.5 sm:py-1 px-3 sm:px-4 rounded-lg bg-gray-950/70 border border-dashed border-amber-500/25 text-center text-[8px] sm:text-[9px] font-bold uppercase tracking-widest text-amber-400/80 flex items-center justify-center gap-1.5">
                                    <span>🪜</span>
                                    <span>PASILLO DE ACCESO</span>
                                    <span>🪜</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Leyenda de Estados -->
                <div
                    class="flex flex-wrap items-center justify-center gap-3 sm:gap-6 text-xs font-medium text-gray-300 pt-3 border-t border-gray-800/60 w-full">
                    <div class="flex items-center space-x-2">
                        <span class="w-3.5 h-3.5 sm:w-4 sm:h-4 border border-sky-400 bg-sky-600/90 rounded inline-block shadow-sm"></span>
                        <span class="text-white">Disponible</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3.5 h-3.5 sm:w-4 sm:h-4 border border-red-400 bg-red-600 rounded inline-block"></span>
                        <span class="text-white">Seleccionada</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span
                            class="w-3.5 h-3.5 sm:w-4 sm:h-4 border border-slate-700 bg-slate-800/85 rounded inline-block"></span>
                        <span class="text-slate-300">Ocupada</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-4 border border-dashed border-amber-500/40 bg-amber-950/30 rounded inline-flex items-center justify-center text-[10px]">🪜</span>
                        <span class="text-amber-300/90">Escalera / Pasillo</span>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-4 border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-6 shadow-xl">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-white">Tu Selección</h2>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-3">PELÍCULA</p>
                    <p class="text-base font-bold text-white mt-0.5">
                        {{ $funcion->pelicula->titulo }} - {{ $funcion->sala->numero_sala }}
                    </p>
                    <p class="text-sm font-medium text-gray-400 mt-1">
                        {{ $funcion->fecha_hora->locale('es')->isoFormat('dddd, HH:mm [hrs]') }}
                    </p>
                </div>

                <hr class="border-gray-800">

                <!-- Cantidad de Entradas -->
                <div class="space-y-2">
                    <label for="cantidad_entradas"
                        class="block text-xs font-bold text-gray-400 uppercase tracking-wider">
                        Cantidad de Entradas
                    </label>
                    <select id="cantidad_entradas" x-model.number="cantidadPermitida" @change="alCambiarCantidad()"
                        class="w-full border border-gray-800 rounded-xl p-3 text-sm font-bold bg-gray-900 text-gray-200 focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                        <option value="1">1 Entrada</option>
                        <option value="2">2 Entradas</option>
                        <option value="3">3 Entradas</option>
                        <option value="4">4 Entradas</option>
                        <option value="5">5 Entradas</option>
                    </select>
                </div>

                <!-- Butacas Seleccionadas -->
                <div class="space-y-2">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">BUTACAS SELECCIONADAS</p>
                    <div class="flex flex-wrap gap-2 min-h-[36px]">
                        <template x-for="b in seleccionadas" :key="b">
                            <span
                                class="border border-red-500/50 bg-red-600/10 px-3 py-1.5 rounded-lg text-xs font-bold text-red-400"
                                x-text="b"></span>
                        </template>
                        <template x-if="seleccionadas.length === 0">
                            <span class="text-xs text-gray-500 italic">Ninguna butaca seleccionada</span>
                        </template>
                    </div>
                </div>

                <hr class="border-gray-800">

                <!-- Botones de Acción -->
                <form action="{{ route('reservas.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="funcion_id" value="{{ $funcion->id }}">
                    <input type="hidden" name="cantidad_entradas" :value="cantidadPermitida">
                    <template x-for="b in seleccionadas" :key="b">
                        <input type="hidden" name="butacas[]" :value="b">
                    </template>

                    <button type="submit" :disabled="seleccionadas.length !== cantidadPermitida"
                        :class="seleccionadas.length === cantidadPermitida ?
                            'bg-red-600 border-red-500 text-white hover:bg-red-700 shadow-lg shadow-red-900/40 cursor-pointer' :
                            'bg-gray-800 border-gray-700 text-gray-500 cursor-not-allowed'"
                        class="w-full border py-3 px-4 rounded-xl text-xs font-bold tracking-widest uppercase transition-all duration-200">
                        CONTINUAR A DULCERÍA →
                    </button>
                </form>

                <div class="text-center">
                    <a href="{{ route('reservas.cancelar') }}"
                        class="text-xs font-bold text-gray-400 hover:text-white underline underline-offset-4 transition-colors">
                        Cancelar Compra
                    </a>
                </div>
            </div>

        </div>

        <!-- Modal de Notificación por Tiempo de Espera Agotado -->
        <div x-show="modalTiempoAgotado" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">

            <div x-show="modalTiempoAgotado"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-gray-900 border border-red-500/40 rounded-2xl max-w-md w-full p-6 sm:p-7 shadow-2xl shadow-red-950/50 space-y-5 text-center relative overflow-hidden">

                <!-- Barra de progreso superior de los 20 segundos -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gray-800">
                    <div class="h-full bg-gradient-to-r from-red-600 via-amber-500 to-red-500 transition-all duration-1000 ease-linear"
                        :style="'width: ' + ((tiempoRespuesta / 20) * 100) + '%'"></div>
                </div>

                <!-- Icono de Alerta de Tiempo -->
                <div class="w-16 h-16 rounded-full bg-red-600/15 border border-red-500/30 flex items-center justify-center mx-auto text-red-500 animate-bounce">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-extrabold text-white tracking-tight">¡Tu tiempo de espera terminó!</h3>
                    <p class="text-sm text-gray-300 leading-relaxed">
                        ¿Deseas quedarte <strong class="text-amber-400">5 minutos más</strong> para terminar de elegir tus butacas o prefieres salir?
                    </p>
                </div>

                <!-- Contador regresivo de 20 segundos de respuesta -->
                <div class="bg-gray-950/90 border border-gray-800 rounded-xl p-3.5 space-y-1">
                    <p class="text-xs text-gray-400">Si no hay respuesta, retornarás al inicio en:</p>
                    <p class="text-2xl font-black font-mono text-red-400">
                        <span x-text="tiempoRespuesta">20</span> <span class="text-xs font-bold uppercase tracking-wider text-gray-400">segundos</span>
                    </p>
                </div>

                <!-- Botones de Decisión -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <button type="button" @click="extenderTiempo()"
                        class="cursor-pointer w-full py-3 px-4 rounded-xl bg-red-600 hover:bg-red-500 border border-red-500 text-white text-xs font-extrabold uppercase tracking-wider shadow-lg shadow-red-900/40 transition-all">
                        ⏱️ +5 Minutos más
                    </button>

                    <button type="button" @click="salirAlInicio()"
                        class="cursor-pointer w-full py-3 px-4 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-200 hover:text-white text-xs font-bold uppercase tracking-wider transition-all">
                        Salir al inicio
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Lógica en Alpine JS -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('reservaButacas', (config) => ({
                cantidadPermitida: config.cantidadPermitida || 2,
                seleccionadas: (config.butacasIniciales && Array.isArray(config.butacasIniciales)) ? [...config.butacasIniciales] : [],
                tiempoRestante: (config.tiempoInicial && config.tiempoInicial > 0) ? config.tiempoInicial : 300, // 5 minutos en segundos
                modalTiempoAgotado: false,
                tiempoRespuesta: 20, // 20 segundos de espera antes de retornar al inicio
                urlCancelar: config.urlCancelar || '/',
                intervaloPrincipal: null,
                redirigiendo: false,

                init() {
                    this.iniciarTemporizador();
                },

                iniciarTemporizador() {
                    if (this.intervaloPrincipal) {
                        clearInterval(this.intervaloPrincipal);
                    }

                    this.intervaloPrincipal = setInterval(() => {
                        if (!this.modalTiempoAgotado) {
                            if (this.tiempoRestante > 0) {
                                this.tiempoRestante--;
                            }
                            if (this.tiempoRestante <= 0) {
                                this.abrirModalTiempoAgotado();
                            }
                        } else {
                            if (this.tiempoRespuesta > 0) {
                                this.tiempoRespuesta--;
                            }
                            if (this.tiempoRespuesta <= 0 && !this.redirigiendo) {
                                this.salirAlInicio();
                            }
                        }
                    }, 1000);
                },

                abrirModalTiempoAgotado() {
                    this.tiempoRespuesta = 20;
                    this.modalTiempoAgotado = true;
                },

                extenderTiempo() {
                    this.modalTiempoAgotado = false;
                    this.tiempoRestante = 300; // Otorga 5 minutos más (300 segundos)
                    this.tiempoRespuesta = 20;
                },

                salirAlInicio() {
                    if (this.redirigiendo) return;
                    this.redirigiendo = true;
                    if (this.intervaloPrincipal) {
                        clearInterval(this.intervaloPrincipal);
                    }
                    window.location.href = this.urlCancelar;
                },

                adelantarTiempoPrueba() {
                    if (!this.modalTiempoAgotado && this.tiempoRestante > 3) {
                        this.tiempoRestante = 3;
                    }
                },

                formatearTiempo(segundos) {
                    const min = String(Math.floor(segundos / 60)).padStart(2, '0');
                    const seg = String(segundos % 60).padStart(2, '0');
                    return `${min}:${seg}`;
                },

                toggleButaca(codigo) {
                    if (this.esSeleccionada(codigo)) {
                        this.seleccionadas = this.seleccionadas.filter(item => item !== codigo);
                    } else {
                        if (this.seleccionadas.length < this.cantidadPermitida) {
                            this.seleccionadas.push(codigo);
                        } else {
                            // Si supera el límite de entradas, reemplaza la primera seleccionada
                            this.seleccionadas.shift();
                            this.seleccionadas.push(codigo);
                        }
                    }
                },

                esSeleccionada(codigo) {
                    return this.seleccionadas.includes(codigo);
                },

                alCambiarCantidad() {
                    if (this.seleccionadas.length > this.cantidadPermitida) {
                        this.seleccionadas = this.seleccionadas.slice(0, this.cantidadPermitida);
                    }
                }
            }));
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</x-layouts::auth.clear>
