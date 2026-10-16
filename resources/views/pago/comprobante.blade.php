<x-layouts::auth.clear :title="'Comprobante de Entrada - ' . $comprobante->codigo_reserva">
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-8 print:p-0 print:m-0 print:max-w-none">

        {{-- Barra de Navegación y Acciones Superiores (Oculta en Impresión) --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 print:hidden">
            <a href="{{ route('home') }}"
                class="inline-flex items-center space-x-2 text-xs sm:text-sm font-semibold text-gray-400 hover:text-white transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="underline underline-offset-4">Volver a la Cartelera</span>
            </a>

            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-2 w-full sm:w-auto">
                <a href="{{ route('pago.pdf', $comprobante->codigo_reserva) }}"
                    class="inline-flex items-center space-x-1.5 bg-red-600 hover:bg-red-500 text-white border border-red-500 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold tracking-wider uppercase transition-colors shadow-lg shadow-red-900/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Descargar PDF</span>
                </a>
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center space-x-1.5 bg-gray-800 hover:bg-gray-700 text-white border border-gray-700 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold tracking-wider uppercase transition-colors cursor-pointer shadow-lg">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Imprimir</span>
                </button>
                <a href="{{ route('home') }}"
                    class="bg-gray-900 hover:bg-gray-800 text-gray-300 border border-gray-700 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs font-bold tracking-wider uppercase transition-colors">
                    Nueva Compra
                </a>
            </div>
        </div>

        {{-- Banner de Confirmación Exitosa --}}
        <div class="border border-green-500/40 bg-green-950/30 rounded-2xl p-6 text-center space-y-2 shadow-2xl print:border-black print:bg-white print:text-black">
            <div class="w-12 h-12 bg-green-600/20 text-green-400 rounded-full flex items-center justify-center mx-auto border border-green-500/40 print:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight print:text-black">¡Pago Confirmado con Éxito!</h1>
            <p class="text-sm text-green-300 font-medium print:text-gray-700">Tu entrada digital ha sido generada. Presenta el código QR directamente en el acceso a la sala.</p>
            <p class="text-xs text-gray-400 pt-1">Código de Reserva: <strong class="text-white print:text-black font-mono text-sm tracking-wider">#{{ $comprobante->codigo_reserva }}</strong></p>
        </div>

        {{-- TICKET DE ENTRADA ESTILO CINEPLANET (Con Perforación Central) --}}
        <div class="border border-gray-800 bg-gray-900/90 rounded-3xl overflow-hidden shadow-2xl print:border-black print:bg-white print:text-black print:shadow-none">
            
            {{-- Encabezado del Ticket --}}
            <div class="bg-gradient-to-r from-red-700 via-red-600 to-amber-600 p-4 sm:p-5 flex items-center justify-between text-white print:bg-black print:text-white">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl font-black tracking-widest">CINE<span class="text-amber-300">+</span></span>
                    <span class="border-l border-white/30 pl-3 text-xs uppercase tracking-widest font-bold hidden sm:inline">Pase de Ingreso Directo</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider block opacity-80">Boleta Electrónica</span>
                    <span class="text-sm font-mono font-bold">{{ $comprobante->serie_numero }}</span>
                </div>
            </div>

            {{-- Cuerpo del Ticket: 2 Columnas (Datos + QR) --}}
            <div class="grid grid-cols-1 md:grid-cols-12 divide-y md:divide-y-0 md:divide-x divide-gray-800 print:divide-black">
                
                {{-- Columna Izquierda: Información de la Función y Asientos / Dulcería --}}
                <div class="md:col-span-8 p-6 sm:p-8 space-y-6">
                    <div>
                        @if ($comprobante->cantidad_entradas > 0)
                            <span class="bg-red-600/20 text-red-400 border border-red-500/30 text-[10px] font-bold uppercase px-2.5 py-1 rounded-md print:border-black print:text-black">
                                {{ $comprobante->cantidad_entradas }} {{ $comprobante->cantidad_entradas > 1 ? 'Entradas' : 'Entrada' }}
                            </span>
                        @else
                            <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-bold uppercase px-2.5 py-1 rounded-md print:border-black print:text-black">
                                🍿 Pedido de Dulcería & Snacks (Sin Función)
                            </span>
                        @endif
                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-2 print:text-black">
                            {{ $comprobante->pelicula_titulo }}
                        </h2>
                    </div>

                    {{-- Grid de Datos Clave (Sede, Sala / Módulo, Fecha, Asientos) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        
                        {{-- Sede --}}
                        <div class="border border-gray-800/80 bg-gray-950/40 rounded-xl p-3.5 space-y-1 print:border-gray-300 print:bg-gray-50">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block print:text-gray-600">🏛️ SEDE / CINE</span>
                            <span class="text-white font-bold text-sm block print:text-black">{{ $comprobante->sede }}</span>
                        </div>

                        {{-- Sala / Módulo --}}
                        <div class="border border-gray-800/80 bg-gray-950/40 rounded-xl p-3.5 space-y-1 print:border-gray-300 print:bg-gray-50">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block print:text-gray-600">
                                {{ $comprobante->cantidad_entradas > 0 ? '🚪 SALA' : '🍿 PUNTO DE ENTREGA' }}
                            </span>
                            <span class="text-white font-bold text-sm block print:text-black">{{ $comprobante->sala ?? 'Módulo Express' }}</span>
                        </div>

                        {{-- Fecha y Hora --}}
                        <div class="border border-gray-800/80 bg-gray-950/40 rounded-xl p-3.5 space-y-1 print:border-gray-300 print:bg-gray-50">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block print:text-gray-600">
                                {{ $comprobante->cantidad_entradas > 0 ? '📅 FECHA Y HORA' : '📅 FECHA DE COMPRA' }}
                            </span>
                            <span class="text-white font-bold text-sm block capitalize print:text-black">
                                {{ $comprobante->fecha_funcion ? $comprobante->fecha_funcion->locale('es')->isoFormat('ddd DD MMM, HH:mm [hrs]') : now()->locale('es')->isoFormat('ddd DD MMM, HH:mm [hrs]') }}
                            </span>
                        </div>

                        {{-- Butacas / Asientos (o badge de comida) --}}
                        @if ($comprobante->cantidad_entradas > 0)
                            <div class="border border-red-500/40 bg-red-950/20 rounded-xl p-3.5 space-y-1 print:border-black print:bg-gray-50">
                                <span class="text-[10px] font-bold text-red-400 uppercase tracking-wider block print:text-black">🎟️ BUTACAS / ASIENTOS</span>
                                <div class="flex flex-wrap gap-1.5 pt-0.5">
                                    @if (is_array($comprobante->asientos))
                                        @foreach ($comprobante->asientos as $asiento)
                                            <span class="bg-red-600 text-white font-black text-xs px-2.5 py-0.5 rounded-md shadow print:border print:border-black print:text-black print:bg-white">
                                                {{ $asiento }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-white font-bold text-sm print:text-black">{{ $comprobante->asientos_texto }}</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="border border-amber-500/40 bg-amber-950/20 rounded-xl p-3.5 space-y-1 print:border-black print:bg-gray-50">
                                <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block print:text-black">⚡ RETIRO RÁPIDO</span>
                                <p class="text-xs text-gray-300 font-medium">Presenta este comprobante con tu QR en la dulcería express.</p>
                            </div>
                        @endif

                    </div>

                    {{-- Datos del Comprador --}}
                    <div class="border-t border-gray-800 pt-4 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-gray-400 gap-2 print:border-gray-300 print:text-black">
                        <div>
                            <span>Comprador: </span>
                            <strong class="text-white print:text-black">{{ $comprobante->cliente_nombre }}</strong>
                        </div>
                        <div>
                            <span>Correo: </span>
                            <strong class="text-white print:text-black">{{ $comprobante->cliente_email }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha: Código QR de Entrada --}}
                <div class="md:col-span-4 p-6 sm:p-8 flex flex-col items-center justify-center text-center space-y-4 bg-gray-950/30 print:bg-white">
                    <div class="bg-white p-3 rounded-2xl shadow-xl flex items-center justify-center max-w-[200px] border border-gray-200">
                        {!! $comprobante->qr_svg !!}
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] font-black uppercase tracking-widest text-red-400 block print:text-black">
                            ACCESO SALA DIRECTO
                        </span>
                        <p class="text-xs font-mono font-bold text-white tracking-widest print:text-black">
                            #{{ $comprobante->codigo_reserva }}
                        </p>
                        <p class="text-[11px] text-gray-400 leading-tight pt-1 print:text-gray-600">
                            Escanea este código en el ingreso a la sala desde tu celular. ¡Sin filas!
                        </p>
                    </div>
                </div>

            </div>

            {{-- Pie del Ticket con Mensaje de Acceso --}}
            <div class="bg-gray-950/60 p-4 border-t border-gray-800 text-center text-xs text-gray-400 print:bg-white print:border-gray-300 print:text-gray-700">
                <span>📍 Dirección: Av. Paseo de la República / Sede {{ $comprobante->sede }}. Presentar carné en caso de tarifas promocionales.</span>
            </div>

        </div>

        {{-- SECCIÓN DULCERÍA EXPRESS (Si adquirió productos) --}}
        @php
            $snacksItems = $comprobante->detalles->where('itemable_type', 'App\Models\Producto');
        @endphp
        @if ($snacksItems->isNotEmpty())
            <div class="border border-amber-500/40 bg-gray-900/80 rounded-2xl p-6 space-y-4 shadow-xl print:border-gray-400 print:bg-white print:text-black">
                <div class="flex items-center justify-between border-b border-gray-800 pb-3 print:border-gray-300">
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl">🍿</span>
                        <div>
                            <h3 class="text-base font-bold text-white print:text-black uppercase tracking-tight">Dulcería Express - Entrega Rápida</h3>
                            <p class="text-xs text-amber-300 print:text-gray-600">Recoge tus combos en el módulo Express con este comprobante</p>
                        </div>
                    </div>
                    <span class="border border-amber-500/30 bg-amber-500/10 text-amber-300 text-xs font-bold px-3 py-1 rounded-full print:border-black print:text-black">
                        {{ $snacksItems->sum('cantidad') }} productos
                    </span>
                </div>

                <div class="divide-y divide-gray-800/60 text-sm print:divide-gray-200">
                    @foreach ($snacksItems as $item)
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded bg-amber-500/20 text-amber-300 font-bold text-xs flex items-center justify-center print:border print:border-black print:text-black">
                                    {{ $item->cantidad }}x
                                </span>
                                <span class="text-gray-200 font-medium print:text-black">{{ $item->descripcion }}</span>
                            </div>
                            <span class="font-bold text-white print:text-black">S/ {{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- DETALLE DE BOLETA ELECTRÓNICA & DESGLOSE FISCAL (Estilo SUNAT / Cineplanet) --}}
        <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl print:border-black print:bg-white print:text-black">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-800 gap-2 print:border-gray-300">
                <div>
                    <h3 class="text-lg font-bold text-white tracking-tight print:text-black">Detalle de Comprobante de Pago</h3>
                    <p class="text-xs text-gray-400">R.U.C. 20601234567 - CINE PLUS PERÚ S.A.C.</p>
                </div>
                <div class="sm:text-right text-xs text-gray-400">
                    <p>Emisión: <strong class="text-white print:text-black">{{ $comprobante->created_at?->format('d/m/Y H:i:s') }}</strong></p>
                    <p>Medio de Pago: <strong class="text-white print:text-black">{{ $comprobante->metodo_pago_nombre }}</strong></p>
                </div>
            </div>

            {{-- Tabla de Importes --}}
            <div class="space-y-3 text-sm">
                
                {{-- Entradas --}}
                <div class="flex justify-between text-gray-300 print:text-black">
                    <span>{{ $comprobante->cantidad_entradas }}x Entradas de Cine ({{ $comprobante->pelicula_titulo }})</span>
                    <span class="font-bold text-white print:text-black">S/ {{ number_format($comprobante->monto_entradas, 2) }}</span>
                </div>

                {{-- Snacks --}}
                @if ($comprobante->monto_dulceria > 0)
                    <div class="flex justify-between text-gray-300 print:text-black">
                        <span>Dulcería / Snacks Express</span>
                        <span class="font-bold text-white print:text-black">S/ {{ number_format($comprobante->monto_dulceria, 2) }}</span>
                    </div>
                @endif

                {{-- Descuento --}}
                @if ($comprobante->monto_descuento > 0)
                    <div class="flex justify-between text-red-400 print:text-black">
                        <span>Descuento Aplicado ({{ $comprobante->descuento_concepto ?? 'Promoción' }})</span>
                        <span class="font-bold">- S/ {{ number_format($comprobante->monto_descuento, 2) }}</span>
                    </div>
                @endif

                <hr class="border-gray-800 print:border-gray-300">

                {{-- Subtotal e IGV --}}
                <div class="flex justify-between text-xs text-gray-400 print:text-gray-600">
                    <span>Op. Gravada (Subtotal sin IGV)</span>
                    <span>S/ {{ number_format($comprobante->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-400 print:text-gray-600">
                    <span>I.G.V. (18%)</span>
                    <span>S/ {{ number_format($comprobante->igv, 2) }}</span>
                </div>

                <hr class="border-gray-800 print:border-gray-300">

                {{-- Total Final --}}
                <div class="flex justify-between items-baseline pt-1">
                    <span class="text-sm font-bold text-gray-300 uppercase tracking-wider print:text-black">TOTAL PAGADO</span>
                    <span class="text-3xl font-black text-white print:text-black">S/ {{ number_format($comprobante->total, 2) }}</span>
                </div>

            </div>

        {{-- Acciones Inferiores (Oculta en Impresión) --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4 print:hidden">
            <button type="button" onclick="window.print()"
                class="w-full sm:w-auto bg-gray-800 hover:bg-gray-700 text-white border border-gray-700 px-6 py-3.5 rounded-xl text-xs font-bold tracking-wider uppercase transition-colors cursor-pointer shadow-lg flex items-center justify-center space-x-2">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Imprimir / Guardar en PDF</span>
            </button>
            <a href="{{ route('home') }}"
                class="w-full sm:w-auto bg-red-600 hover:bg-red-500 text-white border border-red-500 px-8 py-3.5 rounded-xl text-xs font-bold tracking-wider uppercase transition-colors text-center shadow-lg shadow-red-900/30">
                Volver a la Cartelera
            </a>
        </div>

    </main>

    {{-- Estilos para Impresión Limpia en PDF --}}
    <style>
        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }
            header, footer, nav {
                display: none !important;
            }
        }
    </style>
</x-layouts::auth.clear>

