<x-layouts::auth.clear title="Mis Compras & Entradas - Cine+">
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 space-y-8" x-data="{ tab: '{{ $entradasActivas->isNotEmpty() ? 'activas' : ($comprobantes->isNotEmpty() ? 'historial' : 'buscar') }}' }">

        {{-- Encabezado Principal --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-800 pb-6">
            <div>
                <div class="flex items-center space-x-3">
                    <div class="w-1.5 h-7 bg-gradient-to-b from-amber-400 to-red-600 rounded-full"></div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">Mis Compras & Entradas</h1>
                </div>
                <p class="text-sm text-gray-400 mt-1">Revisa tus entradas activas, códigos QR de acceso a sala y comprobantes de pago.</p>
            </div>

            @auth
                <div class="flex items-center space-x-3 bg-gray-900 border border-gray-800 rounded-2xl px-5 py-3 shadow-lg">
                    <div class="w-10 h-10 rounded-full bg-red-600/20 text-red-400 font-bold flex items-center justify-center border border-red-500/30">
                        {{ auth()->user()->initials() }}
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-amber-400 font-medium">★ {{ (int) auth()->user()->saldo_puntos }} Puntos Club Cine+</p>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-xs font-bold text-red-400 hover:text-red-300 underline underline-offset-4">
                    Inicia sesión para ver automáticamente todas tus entradas
                </a>
            @endauth
        </div>

        {{-- Selector de Pestañas / Tabs --}}
        <div class="flex flex-wrap gap-2 border-b border-gray-800 pb-3">
            <button type="button" @click="tab = 'activas'"
                :class="tab === 'activas' ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-900/40' : 'bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800'"
                class="border px-5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center space-x-2">
                <span>🎟️ Entradas Activas</span>
                <span class="bg-gray-950/60 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $entradasActivas->count() }}</span>
            </button>

            <button type="button" @click="tab = 'historial'"
                :class="tab === 'historial' ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-900/40' : 'bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800'"
                class="border px-5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center space-x-2">
                <span>📜 Historial & Boletas</span>
                <span class="bg-gray-950/60 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $comprobantes->count() }}</span>
            </button>

            <button type="button" @click="tab = 'buscar'"
                :class="tab === 'buscar' ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-900/40' : 'bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800'"
                class="border px-5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center space-x-2">
                <span>🔍 Buscar Entrada</span>
            </button>
        </div>

        {{-- TAB 1: ENTRADAS ACTIVAS --}}
        <div x-show="tab === 'activas'" class="space-y-6" x-transition>
            @forelse ($entradasActivas as $c)
                <div class="border border-gray-800 bg-gray-900/70 rounded-3xl overflow-hidden shadow-2xl hover:border-gray-700 transition-all duration-300 group">
                    <div class="grid grid-cols-1 md:grid-cols-12 divide-y md:divide-y-0 md:divide-x divide-gray-800">
                        
                        {{-- Información de la Entrada --}}
                        <div class="md:col-span-8 p-6 sm:p-7 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="bg-green-600/20 text-green-400 border border-green-500/30 text-[10px] font-bold uppercase px-2.5 py-1 rounded-md">
                                    Vigente / Próxima Función
                                </span>
                                <span class="text-xs font-mono text-gray-400">Reserva: <strong class="text-white">#{{ $c->codigo_reserva }}</strong></span>
                            </div>

                            <div>
                                <h3 class="text-2xl font-black text-white group-hover:text-red-400 transition-colors">
                                    {{ $c->pelicula_titulo }}
                                </h3>
                                <p class="text-xs text-gray-400 mt-1 font-medium">
                                    Boleta: {{ $c->serie_numero }} • Titular: {{ $c->cliente_nombre }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div class="bg-gray-950/50 p-3 rounded-xl border border-gray-800">
                                    <span class="text-[10px] text-gray-400 font-bold block uppercase">🏛️ SEDE</span>
                                    <strong class="text-white text-xs block truncate">{{ $c->sede }}</strong>
                                </div>
                                <div class="bg-gray-950/50 p-3 rounded-xl border border-gray-800">
                                    <span class="text-[10px] text-gray-400 font-bold block uppercase">🚪 SALA</span>
                                    <strong class="text-white text-xs block">{{ $c->sala }}</strong>
                                </div>
                                <div class="bg-gray-950/50 p-3 rounded-xl border border-gray-800">
                                    <span class="text-[10px] text-gray-400 font-bold block uppercase">📅 FECHA / HORA</span>
                                    <strong class="text-white text-xs block capitalize">
                                        {{ $c->fecha_funcion ? $c->fecha_funcion->locale('es')->isoFormat('ddd DD MMM, HH:mm') : '-' }}
                                    </strong>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                                <div>
                                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Asientos Seleccionados:</span>
                                    <div class="flex flex-wrap gap-1.5 mt-1">
                                        @if (is_array($c->asientos))
                                            @foreach ($c->asientos as $asiento)
                                                <span class="bg-red-600 text-white font-black text-xs px-2.5 py-0.5 rounded-md">
                                                    {{ $asiento }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-white font-bold text-sm">{{ $c->asientos_texto }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Total Pagado</span>
                                    <span class="text-xl font-black text-white">S/ {{ number_format($c->total, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- QR Preview y Botón de Ingreso --}}
                        <div class="md:col-span-4 p-6 flex flex-col items-center justify-center text-center space-y-4 bg-gray-950/40">
                            <div class="bg-white p-2.5 rounded-2xl shadow-xl max-w-[150px] border border-gray-200">
                                {!! $c->qr_svg !!}
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-black uppercase text-red-400 tracking-wider block">CÓDIGO DE INGRESO</span>
                                <p class="text-xs font-mono text-gray-300 font-bold">#{{ $c->codigo_reserva }}</p>
                            </div>
                            <div class="w-full space-y-2">
                                <a href="{{ route('pago.comprobante', $c->codigo_reserva) }}"
                                    class="block w-full bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider py-2.5 px-4 rounded-xl transition-all shadow-md shadow-red-900/30 text-center">
                                    VER ENTRADA & QR →
                                </a>
                                <a href="{{ route('pago.pdf', $c->codigo_reserva) }}"
                                    class="block w-full bg-gray-900 hover:bg-gray-800 text-gray-300 border border-gray-700 font-bold text-xs uppercase tracking-wider py-2 px-4 rounded-xl transition-all text-center">
                                    📥 Descargar PDF
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="border border-gray-800 bg-gray-900/50 rounded-3xl p-12 text-center space-y-4 shadow-xl">
                    <div class="w-16 h-16 bg-gray-800 text-gray-500 rounded-full flex items-center justify-center mx-auto text-2xl">
                        🎬
                    </div>
                    <h3 class="text-xl font-bold text-white">No tienes entradas activas por el momento</h3>
                    <p class="text-sm text-gray-400 max-w-md mx-auto">
                        Tus próximas compras de entradas para el cine y snacks aparecerán aquí con sus códigos QR de acceso.
                    </p>
                    <a href="{{ route('home') }}" class="inline-block bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-colors shadow-lg shadow-red-900/40">
                        Explorar Cartelera
                    </a>
                </div>
            @endforelse
        </div>

        {{-- TAB 2: HISTORIAL COMPLETO & BOLETAS --}}
        <div x-show="tab === 'historial'" class="space-y-4" x-transition>
            @if ($comprobantes->isNotEmpty())
                <div class="border border-gray-800 bg-gray-900/70 rounded-2xl overflow-hidden shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-950/80 text-gray-400 text-[11px] uppercase tracking-wider border-b border-gray-800">
                                <tr>
                                    <th class="p-4">Boleta / Reserva</th>
                                    <th class="p-4">Película & Sede</th>
                                    <th class="p-4">Fecha Función</th>
                                    <th class="p-4">Asientos</th>
                                    <th class="p-4">Total</th>
                                    <th class="p-4">Medio Pago</th>
                                    <th class="p-4 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/60 text-xs">
                                @foreach ($comprobantes as $item)
                                    <tr class="hover:bg-gray-800/40 transition-colors">
                                        <td class="p-4 font-mono font-bold text-white">
                                            <span>{{ $item->serie_numero }}</span>
                                            <span class="block text-[10px] text-gray-400 font-normal">#{{ $item->codigo_reserva }}</span>
                                        </td>
                                        <td class="p-4">
                                            <strong class="text-white block text-sm">{{ $item->pelicula_titulo }}</strong>
                                            <span class="text-gray-400 text-[11px]">{{ $item->sede }} • {{ $item->sala }}</span>
                                        </td>
                                        <td class="p-4 text-gray-300 capitalize">
                                            {{ $item->fecha_funcion ? $item->fecha_funcion->locale('es')->isoFormat('ddd DD/MM/YY HH:mm') : '-' }}
                                        </td>
                                        <td class="p-4 text-gray-300">
                                            <span class="font-bold text-red-400">{{ $item->asientos_texto }}</span>
                                            <span class="block text-[10px] text-gray-500">({{ $item->cantidad_entradas }} entradas)</span>
                                        </td>
                                        <td class="p-4 font-bold text-white text-sm">
                                            S/ {{ number_format($item->total, 2) }}
                                        </td>
                                        <td class="p-4 text-gray-400">
                                            {{ $item->metodo_pago_nombre }}
                                        </td>
                                        <td class="p-4 text-right">
                                            <a href="{{ route('pago.comprobante', $item->codigo_reserva) }}"
                                                class="inline-flex items-center space-x-1.5 border border-red-500/40 bg-red-600/10 hover:bg-red-600 text-red-400 hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                                                <span>Ver Boleta</span>
                                                <span>→</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="border border-gray-800 bg-gray-900/50 rounded-2xl p-8 text-center text-gray-400 text-sm">
                    No hay comprobantes de compra registrados en esta cuenta.
                </div>
            @endif
        </div>

        {{-- TAB 3: BÚSQUEDA RÁPIDA DE ENTRADAS --}}
        <div x-show="tab === 'buscar'" class="max-w-xl mx-auto space-y-6" x-transition>
            <div class="border border-gray-800 bg-gray-900/80 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl">
                <div class="text-center space-y-2">
                    <span class="text-3xl">🔍</span>
                    <h3 class="text-xl font-bold text-white tracking-tight">Buscar Reserva o Comprobante</h3>
                    <p class="text-xs text-gray-400">Ingresa el código de reserva (ej: <strong>CP-829143</strong>) o tu correo para consultar tus entradas compradas.</p>
                </div>

                <form action="{{ route('compras.buscar') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Código de Reserva o Correo Electrónico</label>
                        <input type="text" name="criterio" required placeholder="Ej: CP-XXXXXX o tu@correo.com"
                            class="w-full uppercase tracking-wider border border-gray-800 rounded-xl p-3.5 text-sm bg-gray-900 text-white placeholder-gray-600 focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                    </div>

                    <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-500 border border-red-500 text-white py-3.5 px-4 rounded-xl text-xs font-bold tracking-widest uppercase shadow-lg shadow-red-900/40 transition-all cursor-pointer">
                        CONSULTAR COMPROBANTE
                    </button>
                </form>

                <div class="border border-dashed border-gray-800 rounded-xl p-4 bg-gray-950/40 text-xs text-gray-400 text-center">
                    💡 Si compraste como invitado sin registrarte, tu código de reserva fue mostrado en la pantalla de confirmación.
                </div>
            </div>
        </div>

    </main>
</x-layouts::auth.clear>

