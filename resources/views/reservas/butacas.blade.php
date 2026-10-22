<x-layouts::auth.clear :title="'Seleccionar Butacas - ' . $funcion->pelicula->titulo">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6" x-data="reservaButacas({
        cantidadPermitida: {{ (int) ($cantidadSeleccionada ?? 2) }},
        butacasIniciales: {{ json_encode($butacasSeleccionadas ?? []) }}
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
                class="border border-gray-800 bg-gray-900/80 text-gray-200 px-3 sm:px-4 py-1.5 sm:py-2 text-xs font-bold tracking-wider rounded-xl flex items-center gap-2 shadow-lg">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                </svg>
                <span>Reserva: <span class="text-white" x-text="formatearTiempo(tiempoRestante)">09:59</span>
                    </span>
            </div>
        </div>

        <!-- Contenedor Principal de Selección y Resumen -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">

            <div
                class="lg:col-span-8 border border-gray-800 bg-gray-900/60 rounded-2xl p-4 sm:p-8 lg:p-12 min-h-[380px] sm:min-h-[480px] flex flex-col justify-between items-center shadow-xl overflow-hidden">

                <!-- Pantalla -->
                <div class="w-full max-w-lg text-center space-y-2.5 sm:space-y-3">
                    <p class="text-[11px] sm:text-xs font-bold tracking-widest uppercase text-gray-400">PANTALLA DE SALA</p>
                    <div
                        class="w-full h-1.5 bg-gradient-to-r from-gray-800 via-red-600 to-gray-800 rounded-full shadow-lg shadow-red-900/20">
                    </div>
                    <p class="text-[10px] text-gray-500 sm:hidden">↔ Desliza horizontalmente si la sala es amplia</p>
                </div>

                <!-- Mapa de Asientos (Scroll horizontal suave en celulares para mantener cada fila intacta) -->
                @php
                    $escalerasCol = $mapaConfig['escaleras_despues_columna'] ?? [];
                    $pasillosFila = $mapaConfig['pasillos_despues_fila'] ?? [];
                    $celdasEscalera = $mapaConfig['celdas_escalera'] ?? [];
                @endphp
                <div class="my-6 sm:my-10 w-full overflow-x-auto pb-2">
                    <div class="space-y-2.5 sm:space-y-4 min-w-max mx-auto px-1">
                        @foreach ($butacasPorFila as $fila => $butacas)
                            @php
                                $tienePasilloDespues = in_array($fila, $pasillosFila, true);
                            @endphp
                            <div class="flex items-center justify-center space-x-2 sm:space-x-3">
                                <!-- Indicador Fila Izquierda -->
                                <span class="w-4 text-center font-bold text-xs sm:text-sm text-gray-400 shrink-0">{{ $fila }}</span>

                                <div class="flex flex-nowrap items-center gap-1.5 sm:gap-2 justify-center">
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
                                                class="w-7 h-7 sm:w-9 sm:h-9 border border-dashed border-amber-500/30 bg-amber-950/20 rounded-lg text-amber-400/80 flex flex-col items-center justify-center shrink-0 select-none">
                                                <span class="text-[10px] sm:text-xs leading-none">🪜</span>
                                                <span class="text-[7px] font-mono uppercase tracking-tighter hidden sm:block">ESC</span>
                                            </div>
                                        @else
                                            <button type="button"
                                                @if ($esDisponible) @click="toggleButaca('{{ $codigoClean }}')" @endif
                                                :disabled="{{ $esDisponible ? 'false' : 'true' }}"
                                                :class="{
                                                    'border-slate-700 bg-slate-800/85 text-slate-400 cursor-not-allowed': {{ !$esDisponible ? 'true' : 'false' }},
                                                    'border-red-400 bg-red-600 text-white shadow-lg shadow-red-900/50 scale-105': {{ $esDisponible ? 'true' : 'false' }} &&
                                                        esSeleccionada('{{ $codigoClean }}'),
                                                    'border-slate-500 bg-slate-700 text-white hover:border-red-400 hover:bg-slate-600 shadow-sm': {{ $esDisponible ? 'true' : 'false' }} &&
                                                        !esSeleccionada('{{ $codigoClean }}')
                                                }"
                                                class="cursor-pointer w-7 h-7 sm:w-9 sm:h-9 border rounded-lg text-[10px] sm:text-xs font-bold flex items-center justify-center shrink-0 transition-all duration-200">
                                                {{ $codigoClean }}
                                            </button>
                                        @endif

                                        <!-- Escalera Vertical entre Columnas -->
                                        @if ($numCol < $butacas->count() && $tieneEscaleraVertical)
                                            <div title="Pasillo / Escalera"
                                                class="w-6 sm:w-8 h-7 sm:h-9 rounded bg-gray-950/80 border-x border-amber-500/25 flex flex-col items-center justify-between py-1 shrink-0 select-none">
                                                <span class="w-3.5 h-0.5 bg-amber-500/30 rounded"></span>
                                                <span class="text-[9px] leading-none">🪜</span>
                                                <span class="w-3.5 h-0.5 bg-amber-500/30 rounded"></span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Indicador Fila Derecha -->
                                <span class="w-4 text-center font-bold text-xs sm:text-sm text-gray-400 shrink-0">{{ $fila }}</span>
                            </div>

                            @if ($tienePasilloDespues)
                                <div class="my-1.5 py-1 px-4 rounded-lg bg-gray-950/70 border border-dashed border-amber-500/25 text-center text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-amber-400/80 flex items-center justify-center gap-2">
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
                    class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-xs font-medium text-gray-300 pt-4 border-t border-gray-800/60 w-full">
                    <div class="flex items-center space-x-2">
                        <span class="w-3.5 h-3.5 sm:w-4 sm:h-4 border border-slate-500 bg-slate-700 rounded inline-block"></span>
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
    </main>

    <!-- Lógica en Alpine JS -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('reservaButacas', (config) => ({
                cantidadPermitida: config.cantidadPermitida || 2,
                seleccionadas: (config.butacasIniciales && Array.isArray(config.butacasIniciales)) ? [...config.butacasIniciales] : [],
                tiempoRestante: 599, // 9 minutos y 59 segundos en segundos

                init() {
                    setInterval(() => {
                        if (this.tiempoRestante > 0) {
                            this.tiempoRestante--;
                        }
                    }, 1000);
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
