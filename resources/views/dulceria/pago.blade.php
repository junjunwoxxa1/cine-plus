<x-layouts::auth.clear title="Pago de Comida & Dulcería - Cine+">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-6" x-data="pagoDulceriaSoloController({
        subtotalComida: {{ (float) $pedido['total'] }},
        metodoInicial: '{{ $metodosPago->first()?->codigo ?? 'tarjeta' }}',
        requiereTarjetaInicial: {{ ($metodosPago->first()?->requiere_tarjeta ?? true) ? 'true' : 'false' }},
        descuentosData: {{ json_encode($descuentos->map(fn($d) => [
            'id' => $d->id,
            'nombre' => $d->nombre,
            'codigo' => $d->codigo,
            'tipo' => $d->tipo->value,
            'valor' => (float) $d->valor,
        ])) }}
    })">

        {{-- Enlace Volver --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('dulceria.index') }}"
                class="inline-flex items-center space-x-2 text-sm font-semibold text-gray-400 hover:text-white transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="underline underline-offset-4">Volver al Menú de Dulcería</span>
            </a>
            <span class="text-xs text-amber-400 font-bold">🍿 Compra Exclusiva de Snacks</span>
        </div>

        <h1 class="text-3xl font-extrabold text-white tracking-tight">Pago de tu Pedido de Dulcería</h1>

        <form action="{{ route('dulceria.pago.procesar') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf

            <input type="hidden" name="metodo_pago" :value="metodoPago">
            <input type="hidden" name="descuento_id" :value="descuentoId">
            <input type="hidden" name="codigo_promo" :value="codigoPromoAplicado">

            {{-- Columna Izquierda: Formularios de Pago --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- 1. Datos del Cliente para el Comprobante --}}
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-4 shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-1.5 h-5 bg-amber-500 rounded-full"></div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300">1. Datos para tu Comprobante Digital</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nombre Completo</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $cliente['nombre']) }}" required
                                placeholder="Tu nombre y apellido"
                                class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-950 text-white placeholder-gray-600 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Correo Electrónico (Para envío de PDF)</label>
                            <input type="email" name="email" value="{{ old('email', $cliente['email']) }}" required
                                placeholder="recibiras_tu_ticket@ejemplo.com"
                                class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-950 text-white placeholder-gray-600 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
                        </div>
                    </div>
                </div>

                {{-- 2. Cupón de Descuento --}}
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-4 shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-1.5 h-5 bg-blue-500 rounded-full"></div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300">2. Cupones de Dulcería</h2>
                    </div>

                    <div class="flex gap-3">
                        <input type="text" x-model="codigoCuponInput" placeholder="Ej: SNACK10, CINEVIP"
                            class="flex-1 border border-gray-800 rounded-xl px-4 py-2.5 text-sm bg-gray-950 text-white uppercase font-mono tracking-wider placeholder-gray-600 focus:ring-1 focus:ring-amber-500 outline-none">
                        <button type="button" @click="aplicarCupon()"
                            class="bg-gray-800 hover:bg-gray-700 text-white font-bold text-xs uppercase px-5 py-2.5 rounded-xl border border-gray-700 cursor-pointer transition-all">
                            Aplicar
                        </button>
                    </div>

                    <div x-show="mensajeCupon" :class="cuponValido ? 'text-green-400' : 'text-red-400'" class="text-xs font-bold" x-text="mensajeCupon"></div>
                </div>

                {{-- 3. Métodos de Pago --}}
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 space-y-6 shadow-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-1.5 h-5 bg-emerald-500 rounded-full"></div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300">3. Método de Pago</h2>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @foreach ($metodosPago as $metodo)
                            <button type="button"
                                @click="seleccionarMetodo('{{ $metodo->codigo }}', {{ $metodo->requiere_tarjeta ? 'true' : 'false' }}, '{{ addslashes($metodo->instrucciones ?? '') }}')"
                                :class="metodoPago === '{{ $metodo->codigo }}' ? 'border-amber-500 bg-amber-500 text-gray-950 font-black shadow-lg shadow-amber-900/40' : 'border-gray-800 bg-gray-900 text-gray-300 hover:bg-gray-800'"
                                class="border px-5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                                {{ $metodo->nombre }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Formulario para Tarjeta --}}
                    <div x-show="requiereTarjeta" class="space-y-4 pt-2" x-transition>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Número de Tarjeta</label>
                            <input type="text" name="numero_tarjeta" placeholder="0000 0000 0000 0000"
                                :required="requiereTarjeta"
                                maxlength="19"
                                class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-950 text-white placeholder-gray-600 focus:ring-1 focus:ring-amber-500 outline-none font-mono">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Vencimiento</label>
                                <input type="text" name="vencimiento" placeholder="MM/YY"
                                    :required="requiereTarjeta"
                                    maxlength="5"
                                    class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-950 text-white placeholder-gray-600 focus:ring-1 focus:ring-amber-500 outline-none font-mono">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">CVC</label>
                                <input type="password" name="cvc" placeholder="***" maxlength="4"
                                    :required="requiereTarjeta"
                                    class="w-full border border-gray-800 rounded-xl p-3 text-sm bg-gray-950 text-white placeholder-gray-600 focus:ring-1 focus:ring-amber-500 outline-none font-mono">
                            </div>
                        </div>
                    </div>

                    {{-- Instrucciones para Yape/Plin/Transferencia --}}
                    <div x-show="!requiereTarjeta" class="p-4 border border-dashed border-gray-800 rounded-xl bg-gray-950/40 text-center text-xs text-gray-300 space-y-2" x-transition>
                        <p class="text-amber-400 font-bold" x-text="'Pago mediante ' + metodoPago.toUpperCase()"></p>
                        <p x-text="instruccionesMetodo || 'Completa tu pago seguro mediante nuestra pasarela digital integrada.'"></p>
                    </div>
                </div>

            </div>

            {{-- Columna Derecha: Resumen del Pedido --}}
            <div class="lg:col-span-4 lg:sticky lg:top-6 space-y-6">

                <div class="border border-gray-800 bg-gray-900/80 rounded-3xl p-6 space-y-5 shadow-2xl">
                    <div class="border-b border-gray-800 pb-3">
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest block">Resumen del Pedido</span>
                        <h2 class="text-xl font-black text-white mt-0.5">Módulo Dulcería Express</h2>
                    </div>

                    {{-- Sede de Retiro --}}
                    <div class="bg-gray-950/60 p-3.5 rounded-xl border border-gray-800 text-xs space-y-1">
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">🏛️ Retiro en Sede:</span>
                        <strong class="text-white text-xs block">{{ $pedido['sede'] }}</strong>
                    </div>

                    {{-- Lista de Items --}}
                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @foreach ($pedido['items'] as $item)
                            <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-800/60">
                                <div>
                                    <p class="text-white font-bold">{{ $item['nombre'] }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $item['cantidad'] }}x S/ {{ number_format($item['precio'], 2) }}</p>
                                </div>
                                <span class="text-white font-black">S/ {{ number_format($item['subtotal'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totales --}}
                    <div class="space-y-2 pt-2 border-t border-gray-800 text-sm">
                        <div class="flex justify-between text-gray-400 text-xs">
                            <span>Subtotal Snacks</span>
                            <span class="font-bold text-white">S/ <span x-text="subtotalComida.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between text-red-400 text-xs" x-show="montoDescuento > 0">
                            <span>Descuento aplicado</span>
                            <span>- S/ <span x-text="montoDescuento.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-gray-800">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Final</span>
                            <span class="text-2xl font-black text-amber-400">S/ <span x-text="total.toFixed(2)"></span></span>
                        </div>
                    </div>

                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3 text-xs text-amber-300">
                        🍿 Esta compra te otorga <strong>+5 Puntos Cine+</strong> y genera tu comprobante con código QR para retiro inmediato.
                    </div>

                    <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-400 text-gray-950 font-black text-xs uppercase tracking-wider py-4 px-4 rounded-xl transition-all shadow-lg shadow-amber-900/30 cursor-pointer flex items-center justify-center gap-2">
                        <span>Pagar y Generar Comprobante con QR »</span>
                    </button>
                </div>

            </div>

        </form>

    </main>

    <script>
        function pagoDulceriaSoloController({ subtotalComida, metodoInicial, requiereTarjetaInicial, descuentosData }) {
            return {
                subtotalComida,
                metodoPago: metodoInicial,
                requiereTarjeta: requiereTarjetaInicial,
                instruccionesMetodo: '',
                descuentosData,
                descuentoId: null,
                codigoPromoAplicado: null,
                codigoCuponInput: '',
                mensajeCupon: '',
                cuponValido: false,
                montoDescuento: 0,

                seleccionarMetodo(codigo, requiere, instrucciones) {
                    this.metodoPago = codigo;
                    this.requiereTarjeta = requiere;
                    this.instruccionesMetodo = instrucciones;
                },

                aplicarCupon() {
                    const codigo = this.codigoCuponInput.trim().toUpperCase();
                    if (!codigo) return;

                    const promo = this.descuentosData.find(d => d.codigo && d.codigo.toUpperCase() === codigo);
                    if (promo) {
                        this.descuentoId = promo.id;
                        this.codigoPromoAplicado = promo.codigo;
                        this.cuponValido = true;
                        if (promo.tipo === 'porcentaje') {
                            this.montoDescuento = (this.subtotalComida * promo.valor) / 100;
                        } else {
                            this.montoDescuento = Math.min(this.subtotalComida, promo.valor);
                        }
                        this.mensajeCupon = `¡Cupón "${promo.codigo}" aplicado con éxito!`;
                    } else {
                        this.cuponValido = false;
                        this.mensajeCupon = 'El cupón ingresado no es válido o está vencido.';
                    }
                },

                get total() {
                    return Math.max(0, this.subtotalComida - this.montoDescuento);
                }
            };
        }
    </script>
</x-layouts::auth.clear>

