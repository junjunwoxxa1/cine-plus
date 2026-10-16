<x-layouts::auth.clear :title="'Pago y Confirmación - ' . $funcion->pelicula->titulo">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6" x-data="pagoController({
        precioUnitario: {{ (float) $funcion->precio }},
        cantidadEntradas: {{ (int) $reserva['cantidad'] }},
        puntosUsuario: {{ (int) $cliente['puntos'] }},
        subtotalProductos: {{ (float) $montoDulceria }},
        metodoInicial: '{{ $metodosPago->first()?->codigo ?? 'tarjeta' }}',
        requiereTarjetaInicial: {{ ($metodosPago->first()?->requiere_tarjeta ?? true) ? 'true' : 'false' }},
        descuentosData: {{ json_encode($descuentos->map(fn($d) => [
            'id' => $d->id,
            'nombre' => $d->nombre,
            'codigo' => $d->codigo,
            'tipo' => $d->tipo->value,
            'valor' => (float) $d->valor,
            'descripcion' => $d->descripcion,
        ])) }}
    })">

        {{-- Barra de Progreso del Flujo --}}
        <div class="flex items-center justify-center space-x-2 sm:space-x-4 text-xs font-bold tracking-wider">
            <a href="{{ route('reservas.butacas', $funcion) }}" class="flex items-center space-x-1.5 text-green-400 hover:text-green-300 transition-colors">
                <span class="w-6 h-6 bg-green-600 text-white rounded-full flex items-center justify-center text-xs">✓</span>
                <span class="hidden sm:inline">BUTACAS</span>
            </a>
            <div class="w-8 h-px bg-green-600"></div>
            <a href="{{ route('reservas.dulceria') }}" class="flex items-center space-x-1.5 text-green-400 hover:text-green-300 transition-colors">
                <span class="w-6 h-6 bg-green-600 text-white rounded-full flex items-center justify-center text-xs">✓</span>
                <span class="hidden sm:inline">DULCERÍA</span>
            </a>
            <div class="w-8 h-px bg-red-600"></div>
            <div class="flex items-center space-x-1.5 text-red-400">
                <span class="w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-xs">3</span>
                <span class="hidden sm:inline">PAGO</span>
            </div>
        </div>

        {{-- Enlace Volver a Dulcería --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <a href="{{ route('reservas.dulceria') }}"
                class="inline-flex items-center space-x-2 text-xs sm:text-sm font-semibold text-gray-400 hover:text-white transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="underline underline-offset-4">Volver a Dulcería</span>
            </a>
            <span class="text-[11px] sm:text-xs text-gray-500">Paso final: Revisa tu orden y completa el pago</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Pago y Confirmación</h1>

        <form action="{{ route('pago.procesar') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf

            {{-- Hidden inputs para descuento y método de pago --}}
            <input type="hidden" name="metodo_pago" :value="metodoPago">
            <input type="hidden" name="descuento_id" :value="descuentoId">
            <input type="hidden" name="codigo_promo" :value="codigoPromoAplicado">

            <!-- Columna Izquierda: Formularios de Pago -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. Datos del Cliente -->
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-4 shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-1.5 h-5 bg-red-600 rounded-full"></div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300">Datos del Comprador</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nombre Completo</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $cliente['nombre']) }}" required
                                placeholder="Ej. Juan Pérez"
                                class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-900 text-white placeholder-gray-600 focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Correo Electrónico</label>
                            <input type="email" name="email" value="{{ old('email', $cliente['email']) }}" required
                                placeholder="ejemplo@correo.com"
                                class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-900 text-white placeholder-gray-600 focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- 2. Resumen de Dulcería elegida -->
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-4 shadow-xl">
                    <div class="flex items-center justify-between border-b border-gray-800/80 pb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-1.5 h-5 bg-amber-500 rounded-full"></div>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300">Tu Dulcería & Snacks</h2>
                        </div>
                        <a href="{{ route('reservas.dulceria') }}" class="text-xs font-bold text-red-400 hover:text-red-300 underline underline-offset-4">
                            Editar snacks
                        </a>
                    </div>

                    @forelse ($productosSeleccionados as $item)
                        <div class="flex items-center justify-between text-sm py-1 border-b border-gray-800/40 last:border-0">
                            <span class="text-gray-200">{{ $item['cantidad'] }}x {{ $item['nombre'] }}</span>
                            <span class="font-bold text-white">S/ {{ number_format($item['subtotal'], 2) }}</span>
                        </div>
                    @empty
                        <div class="flex items-center justify-between text-xs text-gray-500 italic py-1">
                            <span>No agregaste productos de dulcería.</span>
                            <a href="{{ route('reservas.dulceria') }}" class="text-amber-400 hover:underline not-italic font-bold">¡Agregar combos o canchita!</a>
                        </div>
                    @endforelse
                </div>

                <!-- 3. Código de Descuento / Cupón (Dinámico desde BD) -->
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-4 shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-1.5 h-5 bg-blue-500 rounded-full"></div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300">Código de Descuento</h2>
                    </div>

                    {{-- Código Promocional / Cupón --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block">¿Tienes un código promocional o cupón?</label>

                        <div class="flex gap-2" x-show="!codigoPromoAplicado">
                            <input type="text" x-model="inputCodigoPromo" placeholder="Ej. CINE50"
                                @keydown.enter.prevent="aplicarCodigoPromo()"
                                class="flex-1 uppercase tracking-wider border border-gray-800 rounded-xl px-4 py-2.5 text-sm bg-gray-900 text-white placeholder-gray-600 focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none">
                            <button type="button" @click="aplicarCodigoPromo()"
                                class="border border-red-500 bg-red-600 hover:bg-red-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold uppercase transition-colors cursor-pointer">
                                Aplicar
                            </button>
                        </div>

                        {{-- Badge de código aplicado con éxito --}}
                        <div x-show="codigoPromoAplicado" class="flex items-center justify-between border border-green-500/40 bg-green-950/40 text-green-300 px-4 py-2.5 rounded-xl text-xs">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Cupón <strong class="uppercase" x-text="codigoPromoAplicado"></strong> aplicado (<span x-text="codigoPromoInfo"></span>)</span>
                            </div>
                            <button type="button" @click="quitarCodigoPromo()" class="text-xs text-red-400 hover:text-red-300 underline font-bold cursor-pointer">
                                Quitar
                            </button>
                        </div>

                        {{-- Mensaje de error de código --}}
                        <p x-show="errorCodigoPromo" x-text="errorCodigoPromo" class="text-xs text-red-400 font-medium"></p>
                    </div>
                </div>

                <!-- 4. Canje de Puntos Club Cine+ -->
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-4 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-amber-400 font-bold">★</span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300">Club Cine+</h2>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Tienes <strong class="text-white" x-text="puntosUsuario">0</strong> puntos disponibles en tu cuenta.</p>
                    </div>

                    <label class="inline-flex items-center space-x-3 border border-gray-800 bg-gray-900 hover:bg-gray-800 px-4 py-3 rounded-xl cursor-pointer transition-colors"
                           :class="puntosUsuario < 50 ? 'opacity-60 cursor-not-allowed' : ''">
                        <input type="checkbox" name="canjear_puntos" value="1" x-model="canjearPuntos" @change="alCambiarCanjePuntos()"
                            :disabled="puntosUsuario < 50"
                            class="w-4 h-4 rounded border-gray-700 text-red-600 focus:ring-red-500 bg-gray-950 cursor-pointer">
                        <span class="text-xs font-bold text-gray-200">
                            Canjear Entradas (50 pts)
                        </span>
                    </label>
                </div>

                <!-- 5. Método de Pago (Dinámico desde BD) -->
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-6 shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-1.5 h-5 bg-emerald-500 rounded-full"></div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300">Método de Pago</h2>
                    </div>

                    {{-- Botones Dinámicos de Métodos de Pago --}}
                    <div class="flex flex-wrap gap-3">
                        @foreach ($metodosPago as $metodo)
                            <button type="button"
                                @click="seleccionarMetodo('{{ $metodo->codigo }}', {{ $metodo->requiere_tarjeta ? 'true' : 'false' }}, '{{ addslashes($metodo->instrucciones ?? '') }}')"
                                :class="metodoPago === '{{ $metodo->codigo }}' ? 'border-red-500 bg-red-600 text-white shadow-lg shadow-red-900/40' : 'border-gray-800 bg-gray-900 text-gray-300 hover:bg-gray-800'"
                                class="border px-5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                                {{ $metodo->nombre }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Formulario para Tarjeta de Crédito/Débito --}}
                    <div x-show="requiereTarjeta" class="space-y-4 pt-2" x-transition>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Número de Tarjeta</label>
                            <input type="text" name="numero_tarjeta" placeholder="0000 0000 0000 0000"
                                :required="requiereTarjeta"
                                maxlength="19"
                                class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-900 text-white placeholder-gray-600 focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition-all font-mono">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Vencimiento</label>
                                <input type="text" name="vencimiento" placeholder="MM/YY"
                                    :required="requiereTarjeta"
                                    maxlength="5"
                                    class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-900 text-white placeholder-gray-600 focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition-all font-mono">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">CVC</label>
                                <input type="password" name="cvc" placeholder="***" maxlength="4"
                                    :required="requiereTarjeta"
                                    class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-900 text-white placeholder-gray-600 focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition-all font-mono">
                            </div>
                        </div>
                    </div>

                    {{-- Instrucciones para Billeteras / Pagos QR / Apple Pay --}}
                    <div x-show="!requiereTarjeta" class="p-4 border border-dashed border-gray-800 rounded-xl bg-gray-950/40 text-center text-xs text-gray-300 space-y-2" x-transition>
                        <div class="w-10 h-10 rounded-full bg-red-600/10 text-red-400 flex items-center justify-center mx-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <p x-text="instruccionesMetodo || ('Completa tu pago seguro mediante ' + metodoPago.toUpperCase())"></p>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha: Resumen del Pedido (Sticky) -->
            <div class="lg:col-span-4 lg:sticky lg:top-6 space-y-6">

                {{-- Tarjeta de la Función y Butacas --}}
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-5 space-y-3 shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-16 rounded-lg bg-gray-800 overflow-hidden flex-shrink-0">
                            @if ($funcion->pelicula->imagen)
                                <img src="{{ $funcion->pelicula->imagen_url }}" alt="{{ $funcion->pelicula->titulo }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-white truncate">{{ $funcion->pelicula->titulo }}</p>
                            <p class="text-xs text-gray-400">{{ $funcion->sala->numero_sala }}</p>
                            <p class="text-xs text-gray-400">{{ $funcion->fecha_hora->locale('es')->isoFormat('ddd DD MMM, HH:mm [hrs]') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-800/60">
                        @foreach ($reserva['butacas_codigos'] as $codigo)
                            <span class="border border-gray-700 bg-gray-800 px-2 py-0.5 rounded text-[10px] font-bold text-gray-300">Asiento {{ $codigo }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Totales y Botón de Pago --}}
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-6 shadow-xl">
                    <h2 class="text-xl font-bold tracking-tight text-white">Resumen del Pedido</h2>

                    <div class="space-y-3 text-sm">
                        <!-- Subtotal Entradas -->
                        <div class="flex justify-between text-gray-300">
                            <span>{{ $reserva['cantidad'] }}x Entradas</span>
                            <span class="font-bold text-white">S/ <span x-text="subtotalEntradas.toFixed(2)">0.00</span></span>
                        </div>

                        <!-- Subtotal Dulcería -->
                        <div class="flex justify-between text-gray-300" x-show="subtotalProductos > 0">
                            <span>Dulcería / Snacks</span>
                            <span class="font-bold text-white">S/ <span x-text="subtotalProductos.toFixed(2)">0.00</span></span>
                        </div>

                        <!-- Descuento -->
                        <div class="flex justify-between text-red-400 text-xs" x-show="montoDescuento > 0">
                            <span>Descuento aplicado</span>
                            <span>- S/ <span x-text="montoDescuento.toFixed(2)">0.00</span></span>
                        </div>
                    </div>

                    <hr class="border-gray-800">

                    <div class="flex justify-between items-baseline">
                        <span class="text-sm font-bold text-gray-300 uppercase tracking-wider">Total a Pagar</span>
                        <span class="text-3xl font-black text-white">S/ <span x-text="total.toFixed(2)">0.00</span></span>
                    </div>

                    <div class="border border-dashed border-amber-500/40 bg-amber-500/10 rounded-xl p-3.5 text-xs text-amber-300 flex items-center space-x-2.5">
                        <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span>Esta compra te suma <strong>+10 Puntos Cine+</strong></span>
                    </div>

                    <button type="submit"
                        class="w-full bg-red-600 border border-red-500 text-white hover:bg-red-700 py-4 px-4 rounded-xl text-xs font-bold tracking-widest uppercase shadow-lg shadow-red-900/40 transition-all duration-200 cursor-pointer">
                        PAGAR Y CONFIRMAR
                    </button>
                </div>

            </div>

        </form>
    </main>

    <!-- Lógica Alpine.js -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pagoController', (config) => ({
                precioUnitario: config.precioUnitario || 0,
                cantidadEntradas: config.cantidadEntradas || 1,
                puntosUsuario: config.puntosUsuario || 0,
                subtotalProductos: config.subtotalProductos || 0,
                metodoPago: config.metodoInicial || 'tarjeta',
                requiereTarjeta: config.requiereTarjetaInicial ?? true,
                instruccionesMetodo: '',

                descuentosData: config.descuentosData || [],
                descuentoId: '',

                inputCodigoPromo: '',
                codigoPromoAplicado: '',
                codigoPromoInfo: '',
                errorCodigoPromo: '',

                canjearPuntos: false,
                subtotalEntradas: 0,
                montoDescuento: 0,
                total: 0,

                init() {
                    this.subtotalEntradas = this.precioUnitario * this.cantidadEntradas;
                    this.calcularTotales();
                },

                seleccionarMetodo(codigo, requiereTarjeta, instrucciones) {
                    this.metodoPago = codigo;
                    this.requiereTarjeta = requiereTarjeta;
                    this.instruccionesMetodo = instrucciones || '';
                },

                alCambiarCanjePuntos() {
                    if (this.canjearPuntos) {
                        this.descuentoId = '';
                        this.codigoPromoAplicado = '';
                    }
                    this.calcularTotales();
                },

                aplicarCodigoPromo() {
                    this.errorCodigoPromo = '';
                    const codigo = (this.inputCodigoPromo || '').trim().toUpperCase();
                    if (!codigo) {
                        this.errorCodigoPromo = 'Por favor ingresa un código promocional.';
                        return;
                    }

                    const encontrado = this.descuentosData.find(d => (d.codigo || '').toUpperCase() === codigo);
                    if (!encontrado) {
                        this.errorCodigoPromo = 'El código ingresado no existe o no se encuentra vigente.';
                        return;
                    }

                    this.canjearPuntos = false;
                    this.descuentoId = encontrado.id;
                    this.codigoPromoAplicado = encontrado.codigo;
                    this.codigoPromoInfo = encontrado.tipo === 'porcentaje'
                        ? '-' + encontrado.valor + '%'
                        : '-S/ ' + Number(encontrado.valor).toFixed(2);
                    this.inputCodigoPromo = '';
                    this.calcularTotales();
                },

                quitarCodigoPromo() {
                    this.codigoPromoAplicado = '';
                    this.descuentoId = '';
                    this.errorCodigoPromo = '';
                    this.calcularTotales();
                },

                calcularTotales() {
                    let desc = 0;

                    if (this.canjearPuntos && this.puntosUsuario >= 50) {
                        desc = this.subtotalEntradas;
                    } else if (this.descuentoId) {
                        const d = this.descuentosData.find(item => item.id == this.descuentoId);
                        if (d) {
                            if (d.tipo === 'porcentaje') {
                                desc = (this.subtotalEntradas * d.valor) / 100;
                            } else if (d.tipo === 'monto_por_entrada') {
                                desc = d.valor * this.cantidadEntradas;
                            } else if (d.tipo === 'monto_total') {
                                desc = d.valor;
                            }
                        }
                    }

                    this.montoDescuento = Math.min(Math.max(0, desc), this.subtotalEntradas);
                    this.total = Math.max(0, (this.subtotalEntradas - this.montoDescuento) + this.subtotalProductos);
                }
            }));
        });
    </script>
</x-layouts::auth.clear>