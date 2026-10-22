<x-layouts::auth.clear :title="'Dulcería - ' . $funcion->pelicula->titulo">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6" x-data="dulceriaController({
        productosData: {{ json_encode($productos->keyBy('id')->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre, 'precio' => (float)$p->precio, 'precio_puntos' => $p->precio_puntos, 'categoria' => $p->categoria, 'stock' => (int) $p->stock, 'activo' => (bool) $p->activo])) }},
        montoEntradas: {{ $reserva['monto_total'] }},
        cantidadesIniciales: {{ json_encode((object) collect($reserva['productos'] ?? [])->map(fn ($item) => $item['cantidad'])->all()) }}
    })">

        {{-- Barra de Progreso del Flujo --}}
        <div class="flex items-center justify-center space-x-2 sm:space-x-4 text-xs font-bold tracking-wider">
            <a href="{{ route('reservas.butacas', $funcion) }}" class="flex items-center space-x-1.5 text-green-400">
                <span class="w-6 h-6 bg-green-600 text-white rounded-full flex items-center justify-center text-xs">✓</span>
                <span class="hidden sm:inline">BUTACAS</span>
            </a>
            <div class="w-8 h-px bg-green-600"></div>
            <div class="flex items-center space-x-1.5 text-red-400">
                <span class="w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center text-xs">2</span>
                <span class="hidden sm:inline">DULCERÍA</span>
            </div>
            <div class="w-8 h-px bg-gray-700"></div>
            <div class="flex items-center space-x-1.5 text-gray-500">
                <span class="w-6 h-6 border border-gray-700 text-gray-500 rounded-full flex items-center justify-center text-xs">3</span>
                <span class="hidden sm:inline">PAGO</span>
            </div>
        </div>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Dulcería & Snacks</h1>
                <p class="text-xs sm:text-sm text-gray-400 mt-1">Agrega lo que quieras llevar a la sala. Podrás recogerlo en el módulo express.</p>
            </div>
            <a href="{{ route('reservas.butacas', $funcion) }}"
                class="inline-flex items-center space-x-2 text-xs sm:text-sm font-semibold text-gray-400 hover:text-white transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="underline underline-offset-4">Volver a Butacas</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">

            {{-- Columna Izquierda: Catálogo de Productos --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- Tabs de Categorías (Scroll horizontal en celulares) --}}
                <div class="flex items-center gap-2 overflow-x-auto border-b border-gray-800 pb-3 scrollbar-none">
                    <button @click="categoriaActiva = 'todos'"
                        :class="categoriaActiva === 'todos' ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-900/40' : 'bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800 hover:border-gray-700'"
                        class="cursor-pointer border px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs font-bold whitespace-nowrap shrink-0 transition-all duration-200">
                        🎬 Todos
                    </button>
                    @foreach ($categorias as $cat)
                        <button @click="categoriaActiva = '{{ $cat->slug }}'"
                            :class="categoriaActiva === '{{ $cat->slug }}' ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-900/40' : 'bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800 hover:border-gray-700'"
                            class="cursor-pointer border px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs font-bold whitespace-nowrap shrink-0 transition-all duration-200">
                            {{ $cat->icono ?? '🍿' }} {{ $cat->nombre }}
                        </button>
                    @endforeach
                </div>

                {{-- Grid de Productos --}}
                @foreach ($categorias as $cat)
                    @if ($cat->productos->isNotEmpty())
                        <div x-show="categoriaActiva === 'todos' || categoriaActiva === '{{ $cat->slug }}'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-4">

                            {{-- Título de Categoría --}}
                            <div class="flex items-center space-x-3">
                                <div class="w-1.5 h-6 bg-gradient-to-b from-amber-400 to-red-600 rounded-full"></div>
                                <h2 class="text-lg font-bold text-white tracking-tight">{{ $cat->icono ?? '🍿' }} {{ $cat->nombre }}</h2>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                @foreach ($cat->productos as $producto)
                                    @php
                                        $desactivado = ! (bool) $producto->activo;
                                        $sinStock = (int) $producto->stock <= 0;
                                        $noDisponible = $desactivado || $sinStock;
                                    @endphp
                                    <div class="border rounded-2xl overflow-hidden transition-all duration-300 shadow-lg {{ $noDisponible ? 'border-gray-900 bg-gray-950/80 opacity-60 grayscale' : 'border-gray-800 bg-gray-900/60 hover:border-gray-700 hover:shadow-xl group' }}"
                                         :class="(cantidadesProductos[{{ $producto->id }}] || 0) > 0 ? 'border-red-500/50 shadow-red-900/20' : ''">

                                        {{-- Imagen del Producto --}}
                                        <div class="h-36 sm:h-40 w-full bg-gray-800 relative overflow-hidden">
                                            <img src="{{ $producto->imagen_url }}"
                                                 alt="{{ $producto->nombre }}"
                                                 class="w-full h-full object-cover {{ !$noDisponible ? 'group-hover:scale-105 transition-transform duration-500' : '' }}"
                                                 loading="lazy">

                                            {{-- Badge de categoría --}}
                                            <span class="absolute top-3 left-3 bg-gray-950/80 backdrop-blur-sm text-gray-200 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg">
                                                {{ $cat->nombre }}
                                            </span>

                                            @if ($desactivado)
                                                <div class="absolute inset-0 bg-gray-950/80 backdrop-blur-[2px] flex items-center justify-center p-2 text-center">
                                                    <span class="bg-zinc-900/90 text-zinc-300 border border-zinc-700/60 font-black text-[11px] uppercase tracking-wider px-3 py-1.5 rounded-xl shadow-xl flex items-center gap-1.5">
                                                        <span>🔒</span>
                                                        <span>NO DISPONIBLE</span>
                                                    </span>
                                                </div>
                                            @elseif ($sinStock)
                                                <div class="absolute inset-0 bg-gray-950/80 backdrop-blur-[2px] flex items-center justify-center p-2 text-center">
                                                    <span class="bg-red-900/90 text-white border border-red-500/60 font-black text-[11px] uppercase tracking-wider px-3 py-1.5 rounded-xl shadow-xl flex items-center gap-1.5">
                                                        <span>🚫</span>
                                                        <span>AGOTADO / SIN STOCK</span>
                                                    </span>
                                                </div>
                                            @else
                                                @if ($producto->stock <= 5)
                                                    <span class="absolute bottom-2 left-2 bg-amber-500/90 text-black text-[10px] font-black uppercase px-2 py-0.5 rounded shadow">
                                                        ¡Solo quedan {{ $producto->stock }}!
                                                    </span>
                                                @endif

                                                {{-- Badge de cantidad si hay seleccionados --}}
                                                <template x-if="(cantidadesProductos[{{ $producto->id }}] || 0) > 0">
                                                    <span class="absolute top-3 right-3 bg-red-600 text-white text-xs font-black w-7 h-7 rounded-full flex items-center justify-center shadow-lg"
                                                          x-text="cantidadesProductos[{{ $producto->id }}]"></span>
                                                </template>
                                            @endif
                                        </div>

                                        {{-- Info del Producto --}}
                                        <div class="p-4 space-y-3">
                                            <div>
                                                <h3 class="font-bold text-sm leading-tight {{ $noDisponible ? 'text-gray-400' : 'text-white' }}">{{ $producto->nombre }}</h3>
                                                <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $producto->descripcion }}</p>
                                            </div>

                                            <div class="flex items-center justify-between">
                                                {{-- Precios --}}
                                                <div>
                                                    <span class="text-lg font-black {{ $noDisponible ? 'text-gray-400' : 'text-white' }}">S/ {{ number_format($producto->precio, 2) }}</span>
                                                    @if ($producto->precio_puntos > 0)
                                                        <span class="text-[10px] {{ $noDisponible ? 'text-gray-500' : 'text-amber-400' }} font-bold block mt-0.5">
                                                            ó {{ $producto->precio_puntos }} pts
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Controles +/- --}}
                                                @if ($desactivado)
                                                    <span class="text-xs font-bold text-gray-400 uppercase bg-gray-900 border border-gray-800 px-3 py-1.5 rounded-xl">
                                                        No Disponible
                                                    </span>
                                                @elseif ($sinStock)
                                                    <span class="text-xs font-bold text-red-400 uppercase bg-red-950/40 border border-red-900/60 px-3 py-1.5 rounded-xl">
                                                        Agotado
                                                    </span>
                                                @else
                                                    <div class="flex items-center space-x-1.5">
                                                        <template x-if="(cantidadesProductos[{{ $producto->id }}] || 0) > 0">
                                                            <button type="button"
                                                                    @click="modificarCantidad({{ $producto->id }}, -1)"
                                                                    class="w-9 h-9 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white font-bold flex items-center justify-center transition-colors cursor-pointer">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                                            </button>
                                                        </template>

                                                        <template x-if="(cantidadesProductos[{{ $producto->id }}] || 0) > 0">
                                                            <span class="w-8 text-center text-sm font-black text-white"
                                                                  x-text="cantidadesProductos[{{ $producto->id }}]">0</span>
                                                        </template>

                                                        <button type="button"
                                                                @click="modificarCantidad({{ $producto->id }}, 1)"
                                                                :disabled="(cantidadesProductos[{{ $producto->id }}] || 0) >= (productosData[{{ $producto->id }}]?.stock || 0)"
                                                                :class="(cantidadesProductos[{{ $producto->id }}] || 0) >= (productosData[{{ $producto->id }}]?.stock || 0) ? 'opacity-40 cursor-not-allowed bg-gray-800 border-gray-700 text-gray-500' : 'bg-red-600 hover:bg-red-500 border-red-500 text-white cursor-pointer shadow-md shadow-red-900/30'"
                                                                class="w-9 h-9 rounded-xl border font-bold flex items-center justify-center transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Columna Derecha: Resumen del Pedido (Sticky) --}}
            <div class="lg:col-span-4 lg:sticky lg:top-6 space-y-4">

                {{-- Resumen de la Función --}}
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
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($reserva['butacas_codigos'] as $codigo)
                            <span class="border border-gray-700 bg-gray-800 px-2 py-0.5 rounded text-[10px] font-bold text-gray-300">{{ $codigo }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Carrito de Dulcería --}}
                <div class="border border-gray-800 bg-gray-900/60 rounded-2xl p-5 space-y-4 shadow-xl">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300">Tu Pedido de Dulcería</h2>

                    {{-- Items del Carrito --}}
                    <div class="space-y-2 max-h-48 overflow-y-auto" x-show="totalItems > 0">
                        <template x-for="(item, id) in itemsCarrito" :key="id">
                            <div class="flex items-center justify-between text-sm py-2 border-b border-gray-800/60">
                                <div class="flex-1 min-w-0">
                                    <p class="text-gray-200 font-medium text-xs truncate" x-text="item.nombre"></p>
                                    <p class="text-gray-500 text-[10px]">
                                        <span x-text="item.cantidad"></span>x S/ <span x-text="item.precio.toFixed(2)"></span>
                                    </p>
                                </div>
                                <span class="text-white font-bold text-xs ml-2" x-text="'S/ ' + item.subtotal.toFixed(2)"></span>
                            </div>
                        </template>
                    </div>

                    <div x-show="totalItems === 0" class="text-center py-4">
                        <p class="text-gray-500 text-xs italic">No has agregado nada aún</p>
                        <p class="text-gray-600 text-[10px] mt-1">¡Elige tus snacks favoritos!</p>
                    </div>

                    <hr class="border-gray-800">

                    {{-- Totales --}}
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-300">
                            <span>{{ $reserva['cantidad'] }}x Entradas</span>
                            <span class="font-bold text-white">S/ {{ number_format($reserva['monto_total'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-300" x-show="subtotalDulceria > 0">
                            <span>Dulcería (<span x-text="totalItems">0</span> items)</span>
                            <span class="font-bold text-white">S/ <span x-text="subtotalDulceria.toFixed(2)">0.00</span></span>
                        </div>
                    </div>

                    <hr class="border-gray-800">

                    <div class="flex justify-between items-baseline">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Parcial</span>
                        <span class="text-xl font-black text-white">S/ <span x-text="totalGeneral.toFixed(2)">0.00</span></span>
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <form action="{{ route('reservas.dulceria.store') }}" method="POST" class="space-y-3">
                    @csrf
                    {{-- Hidden inputs para productos seleccionados --}}
                    <template x-for="(item, id) in itemsCarrito" :key="'input-' + id">
                        <input type="hidden" :name="'productos[' + id + ']'" :value="item.cantidad">
                    </template>

                    <button type="submit"
                        class="w-full bg-red-600 border border-red-500 text-white hover:bg-red-700 py-3.5 px-4 rounded-xl text-xs font-bold tracking-widest uppercase shadow-lg shadow-red-900/40 transition-all duration-200 cursor-pointer">
                        CONTINUAR AL PAGO
                    </button>
                </form>

                <a href="{{ route('pago.index') }}"
                   onclick="event.preventDefault(); document.getElementById('skip-dulceria-form').submit();"
                   class="block text-center text-xs font-bold text-gray-400 hover:text-white underline underline-offset-4 transition-colors cursor-pointer">
                    Omitir Dulcería →
                </a>
                <form id="skip-dulceria-form" action="{{ route('reservas.dulceria.store') }}" method="POST" class="hidden">
                    @csrf
                </form>

                <div class="text-center pt-1">
                    <a href="{{ route('reservas.cancelar') }}"
                       class="text-xs font-bold text-gray-400 hover:text-white underline underline-offset-4 transition-colors">
                        Cancelar Compra
                    </a>
                </div>

            </div>
        </div>
    </main>

    {{-- Alpine.js Logic --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dulceriaController', (config) => ({
                productosData: config.productosData || {},
                montoEntradas: config.montoEntradas || 0,
                cantidadesProductos: {},
                categoriaActiva: 'todos',
                subtotalDulceria: 0,
                totalItems: 0,
                totalGeneral: 0,

                get itemsCarrito() {
                    let items = {};
                    for (let id in this.cantidadesProductos) {
                        let cantidad = this.cantidadesProductos[id];
                        if (cantidad > 0 && this.productosData[id]) {
                            let producto = this.productosData[id];
                            items[id] = {
                                nombre: producto.nombre,
                                cantidad: cantidad,
                                precio: producto.precio,
                                subtotal: producto.precio * cantidad
                            };
                        }
                    }
                    return items;
                },

                modificarCantidad(id, cambio) {
                    let actual = this.cantidadesProductos[id] || 0;
                    let prod = this.productosData[id];
                    if (!prod || !prod.activo || (prod.stock || 0) <= 0) {
                        return;
                    }
                    let maxStock = prod.stock ?? 10;
                    let nuevo = Math.max(0, Math.min(maxStock, actual + cambio));
                    this.cantidadesProductos[id] = nuevo;
                    this.calcularTotales();
                },

                calcularTotales() {
                    this.subtotalDulceria = 0;
                    this.totalItems = 0;
                    for (let id in this.cantidadesProductos) {
                        let cantidad = this.cantidadesProductos[id];
                        if (cantidad > 0 && this.productosData[id]) {
                            this.subtotalDulceria += this.productosData[id].precio * cantidad;
                            this.totalItems += cantidad;
                        }
                    }
                    this.totalGeneral = this.montoEntradas + this.subtotalDulceria;
                },

                init() {
                    this.cantidadesProductos = { ...(config.cantidadesIniciales || {}) };
                    this.calcularTotales();
                }
            }));
        });
    </script>
</x-layouts::auth.clear>

