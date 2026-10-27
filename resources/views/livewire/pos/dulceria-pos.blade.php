<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-6">

    {{-- Header del Punto de Venta --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800 pb-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-red-600 to-amber-500 flex items-center justify-center text-white text-xl shadow-lg shadow-red-900/40">
                🍿
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-black text-white tracking-tight">Punto de Venta - Dulcería Express</h1>
                    <span class="bg-red-600/20 text-red-400 border border-red-500/30 text-[10px] font-black uppercase px-2 py-0.5 rounded-full">
                        POS Venta Directa
                    </span>
                </div>
                <p class="text-xs text-gray-400">Atención en mostrador • Stock sincronizado en tiempo real</p>
            </div>
        </div>

        <div class="flex items-center space-x-3 text-xs">
            <div class="bg-gray-900 border border-gray-800 px-3.5 py-1.5 rounded-xl text-gray-300 flex items-center space-x-2">
                <span>👤 Cajero:</span>
                <strong class="text-white">{{ auth()->user()->name }}</strong>
            </div>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-800 hover:bg-gray-700 text-red-400 border border-gray-700 px-3.5 py-1.5 rounded-xl font-bold transition-colors">
                    ⚙️ Panel Admin
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white border border-red-500/30 px-3.5 py-1.5 rounded-xl font-bold transition-all cursor-pointer">
                    Salir
                </button>
            </form>
        </div>
    </div>

    {{-- Layout Principal: Catálogo (Izquierda) + Ticket de Venta (Derecha) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- Columna Izquierda: Buscador + Categorías + Catálogo de Productos --}}
        <div class="lg:col-span-7 xl:col-span-8 space-y-4">
            
            {{-- Buscador y Filtro por Categorías --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" wire:model.live.debounce.250ms="busqueda"
                        placeholder="Buscar producto o snack..."
                        class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all">
                </div>

                <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                    <button type="button" wire:click="$set('categoriaSeleccionada', 'todos')"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap border {{ $categoriaSeleccionada === 'todos' ? 'bg-red-600 border-red-500 text-white shadow-md shadow-red-900/30' : 'bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800' }}">
                        🎬 Todos
                    </button>
                    @foreach ($categorias as $cat)
                        <button type="button" wire:click="$set('categoriaSeleccionada', '{{ $cat->slug }}')"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap border {{ $categoriaSeleccionada === $cat->slug ? 'bg-red-600 border-red-500 text-white shadow-md shadow-red-900/30' : 'bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800' }}">
                            {{ $cat->icono ?? '🍿' }} {{ $cat->nombre }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Grid de Productos para Punto de Venta --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                @forelse ($productos as $p)
                    @php
                        $desactivado = ! (bool) $p->activo;
                        $sinStock = (int) $p->stock <= 0;
                        $noDisponible = $desactivado || $sinStock;
                        $enCarrito = $carrito[$p->id]['cantidad'] ?? 0;
                        $limiteAlcanzado = $enCarrito >= $p->stock;
                    @endphp
                    <div @if (!$noDisponible) wire:click="agregarAlCarrito({{ $p->id }})" @endif
                        class="border rounded-2xl overflow-hidden transition-all duration-200 shadow-md relative flex flex-col justify-between select-none {{ $noDisponible ? 'border-gray-900 bg-gray-950 opacity-40 grayscale cursor-not-allowed' : ($limiteAlcanzado ? 'border-amber-500/50 bg-gray-900/80 cursor-not-allowed' : 'border-gray-800 bg-gray-900/80 hover:border-red-500/60 hover:bg-gray-800/90 cursor-pointer hover:shadow-lg hover:-translate-y-0.5') }}"
                        wire:key="pos-prod-{{ $p->id }}">

                        {{-- Imagen y Badges --}}
                        <div class="aspect-video bg-gray-950 relative overflow-hidden">
                            <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}" class="w-full h-full object-cover">
                            
                            {{-- Badge de Estado y Stock --}}
                            @if ($desactivado)
                                <div class="absolute inset-0 bg-black/80 flex items-center justify-center p-1">
                                    <span class="bg-zinc-800 text-zinc-300 text-[9px] font-black uppercase px-2 py-0.5 rounded border border-zinc-700 shadow">
                                        DESACTIVADO
                                    </span>
                                </div>
                            @elseif ($sinStock)
                                <div class="absolute inset-0 bg-black/75 flex items-center justify-center p-1">
                                    <span class="bg-red-600 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded shadow">
                                        AGOTADO
                                    </span>
                                </div>
                            @elseif ($p->stock <= 5)
                                <span class="absolute top-2 right-2 bg-amber-500 text-black text-[9px] font-black uppercase px-1.5 py-0.5 rounded shadow">
                                    Stock: {{ $p->stock }}
                                </span>
                            @else
                                <span class="absolute top-2 right-2 bg-gray-950/80 backdrop-blur-sm text-green-400 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded border border-gray-700">
                                    Stock: {{ $p->stock }}
                                </span>
                            @endif

                            {{-- Badge de Cantidad en Carrito --}}
                            @if ($enCarrito > 0)
                                <span class="absolute bottom-2 left-2 bg-red-600 text-white text-[11px] font-black w-6 h-6 rounded-full flex items-center justify-center shadow-lg border border-white/20">
                                    {{ $enCarrito }}
                                </span>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="p-3 space-y-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-gray-500 block truncate">
                                {{ $p->categoria->nombre ?? 'Snack' }}
                            </span>
                            <h4 class="text-xs font-bold text-white leading-tight line-clamp-2">
                                {{ $p->nombre }}
                            </h4>
                            <div class="flex items-baseline justify-between pt-1">
                                <span class="text-sm font-black text-white">S/ {{ number_format($p->precio, 2) }}</span>
                                @if (!$noDisponible && !$limiteAlcanzado)
                                    <span class="text-[10px] text-red-400 font-bold">+ Añadir</span>
                                @endif
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full border border-gray-800 bg-gray-900/50 rounded-2xl p-8 text-center text-gray-500 text-xs italic">
                        No se encontraron productos en esta categoría.
                    </div>
                @endforelse
            </div>

        </div>

        {{-- Columna Derecha: Ticket de Venta / Carrito POS --}}
        <div class="lg:col-span-5 xl:col-span-4 border border-gray-800 bg-gray-900/90 rounded-3xl p-5 space-y-5 shadow-2xl sticky top-4">
            
            <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="text-base">🧾</span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-white">Orden Actual</h2>
                    <span class="bg-gray-800 text-gray-300 text-[10px] font-bold px-2 py-0.5 rounded-full">
                        {{ count($carrito) }} ítems
                    </span>
                </div>
                
                @if (!empty($carrito))
                    <button type="button" wire:click="limpiarCarrito"
                        class="text-[10px] text-red-400 hover:text-red-300 font-bold underline cursor-pointer">
                        Vaciar
                    </button>
                @endif
            </div>

            @error('carrito')
                <div class="bg-red-950/60 border border-red-500/50 text-red-300 p-2.5 rounded-xl text-xs">
                    {{ $message }}
                </div>
            @enderror

            {{-- Lista de Productos en la Orden --}}
            <div class="space-y-2 max-h-60 overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-gray-800">
                @forelse ($carrito as $item)
                    <div class="border border-gray-800/80 bg-gray-950/60 rounded-xl p-3 flex items-center justify-between gap-2"
                        wire:key="pos-cart-{{ $item['id'] }}">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-white truncate">{{ $item['nombre'] }}</p>
                            <p class="text-[10px] text-gray-400">
                                S/ {{ number_format($item['precio'], 2) }} c/u • <span class="text-gray-500">Máx: {{ $item['stock_max'] }}</span>
                            </p>
                        </div>

                        {{-- Controles +/- --}}
                        <div class="flex items-center space-x-1.5 flex-shrink-0">
                            <button type="button" wire:click="modificarCantidad({{ $item['id'] }}, -1)"
                                class="w-7 h-7 rounded-lg bg-gray-800 hover:bg-gray-700 text-white font-bold flex items-center justify-center text-xs transition-colors cursor-pointer">
                                -
                            </button>
                            <span class="w-6 text-center text-xs font-black text-white">
                                {{ $item['cantidad'] }}
                            </span>
                            <button type="button" wire:click="modificarCantidad({{ $item['id'] }}, 1)"
                                @if ($item['cantidad'] >= $item['stock_max']) disabled @endif
                                class="w-7 h-7 rounded-lg border font-bold flex items-center justify-center text-xs transition-colors {{ $item['cantidad'] >= $item['stock_max'] ? 'opacity-30 border-gray-800 text-gray-600 cursor-not-allowed' : 'bg-red-600 hover:bg-red-500 border-red-500 text-white cursor-pointer' }}">
                                +
                            </button>
                        </div>

                        <div class="text-right flex-shrink-0 min-w-[55px]">
                            <span class="text-xs font-black text-white block">S/ {{ number_format($item['subtotal'], 2) }}</span>
                            <button type="button" wire:click="eliminarDelCarrito({{ $item['id'] }})"
                                class="text-[10px] text-gray-500 hover:text-red-400 transition-colors">
                                Quitar
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 space-y-2">
                        <span class="text-3xl block opacity-40">🛒</span>
                        <p class="text-xs">Orden vacía.</p>
                        <p class="text-[10px] text-gray-600">Haz clic en los productos para agregarlos a la venta.</p>
                    </div>
                @endforelse
            </div>

            {{-- Totales --}}
            <div class="border-t border-gray-800 pt-3 space-y-1.5 text-xs">
                <div class="flex justify-between text-gray-400">
                    <span>Op. Gravada (Subtotal)</span>
                    <span>S/ {{ number_format(round($this->total / 1.18, 2), 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-400">
                    <span>I.G.V. (18%)</span>
                    <span>S/ {{ number_format(round($this->total - ($this->total / 1.18), 2), 2) }}</span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-gray-800/80">
                    <span class="text-xs font-black uppercase tracking-wider text-gray-300">TOTAL A COBRAR</span>
                    <span class="text-2xl font-black text-white">S/ {{ number_format($this->total, 2) }}</span>
                </div>
            </div>

            {{-- Datos del Comprador Mostrador --}}
            <div class="space-y-2 pt-2 border-t border-gray-800">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nombre del Cliente</label>
                    <input type="text" wire:model="clienteNombre" placeholder="Cliente Mostrador"
                        class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                {{-- Método de Pago --}}
                <div class="space-y-1 pt-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Método de Pago</label>
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach (['efectivo' => '💵 Efec', 'tarjeta' => '💳 Tarj', 'yape' => '🟣 Yape', 'plin' => '🔵 Plin'] as $mKey => $mLabel)
                            <button type="button" wire:click="$set('metodoPago', '{{ $mKey }}')"
                                class="py-2 px-1 rounded-xl text-[11px] font-bold transition-all border text-center cursor-pointer {{ $metodoPago === $mKey ? 'bg-red-600 border-red-500 text-white shadow-md' : 'bg-gray-950 border-gray-800 text-gray-300 hover:bg-gray-800' }}">
                                {{ $mLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Cálculo de Vuelto si es Efectivo --}}
                @if ($metodoPago === 'efectivo')
                    <div class="grid grid-cols-2 gap-2 pt-1 bg-gray-950/60 p-2.5 rounded-xl border border-gray-800">
                        <div>
                            <label class="text-[9px] font-bold text-gray-400 uppercase block">Paga con S/</label>
                            <input type="number" step="0.5" wire:model.live="montoRecibido" placeholder="0.00"
                                class="w-full bg-gray-900 border border-gray-700 rounded-lg px-2.5 py-1 text-xs text-white font-mono font-bold focus:outline-none focus:border-red-500">
                        </div>
                        <div>
                            <label class="text-[9px] font-bold text-gray-400 uppercase block">Vuelto / Cambio</label>
                            <span class="text-sm font-black text-green-400 font-mono block pt-1">
                                S/ {{ number_format($this->vuelto, 2) }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Botón Cobrar --}}
            <button type="button" wire:click="registrarVenta"
                @if (empty($carrito)) disabled @endif
                class="w-full bg-red-600 hover:bg-red-500 disabled:opacity-40 disabled:cursor-not-allowed border border-red-500 text-white py-3.5 px-4 rounded-2xl text-xs font-bold tracking-widest uppercase transition-all shadow-xl shadow-red-900/40 cursor-pointer">
                REGISTRAR VENTA Y COBRAR (S/ {{ number_format($this->total, 2) }})
            </button>

        </div>

    </div>

    {{-- MODAL DE TICKET IMPRESO / VENTA EXITOSA --}}
    @if ($modalTicketAbierto && $ticketVenta)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             x-transition>
            <div class="bg-gray-900 border border-gray-800 rounded-3xl max-w-sm w-full p-6 space-y-5 shadow-2xl relative">
                
                <div class="text-center space-y-1">
                    <div class="w-12 h-12 bg-green-600/20 text-green-400 rounded-full flex items-center justify-center mx-auto border border-green-500/40">
                        ✓
                    </div>
                    <h3 class="text-lg font-black text-white">¡Venta Registrada con Éxito!</h3>
                    <p class="text-xs text-gray-400">Stock descontado del inventario general</p>
                </div>

                {{-- Ticket Térmico Preview --}}
                <div class="bg-white text-black p-4 rounded-2xl font-mono text-xs space-y-2 border border-gray-300 shadow-inner">
                    <div class="text-center border-b border-gray-400 pb-2">
                        <strong class="text-sm block">CINE+ DULCERÍA EXPRESS</strong>
                        <span class="text-[10px] text-gray-600 block">RUC: 20601234567 • Mostrador</span>
                        <span class="text-[10px] text-gray-600 block">Ticket: {{ $ticketVenta['serie_numero'] }}</span>
                        <span class="text-[9px] text-gray-500 block">{{ $ticketVenta['fecha'] }}</span>
                    </div>

                    <div class="text-[10px] space-y-0.5 border-b border-gray-400 pb-2">
                        <div>Cliente: <strong>{{ $ticketVenta['cliente'] }}</strong></div>
                        <div>Cajero: <strong>{{ $ticketVenta['cajero'] }}</strong></div>
                        <div>Pago: <strong>{{ $ticketVenta['metodo'] }}</strong></div>
                    </div>

                    <div class="space-y-1 border-b border-gray-400 pb-2 text-[11px]">
                        @foreach ($ticketVenta['items'] as $it)
                            <div class="flex justify-between">
                                <span>{{ $it['cantidad'] }}x {{ $it['nombre'] }}</span>
                                <strong class="text-right">S/ {{ number_format($it['subtotal'], 2) }}</strong>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between text-sm font-black pt-1">
                        <span>TOTAL</span>
                        <span>S/ {{ number_format($ticketVenta['total'], 2) }}</span>
                    </div>

                    @if ($ticketVenta['vuelto'] > 0)
                        <div class="flex justify-between text-[10px] text-gray-700">
                            <span>Vuelto:</span>
                            <span>S/ {{ number_format($ticketVenta['vuelto'], 2) }}</span>
                        </div>
                    @endif

                    <div class="text-center text-[9px] text-gray-500 pt-2 border-t border-dashed border-gray-400">
                        ¡Gracias por tu compra! Disfruta la función 🍿
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="space-y-2">
                    <button type="button" onclick="window.print()"
                        class="w-full bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white py-2.5 rounded-xl text-xs font-bold uppercase transition-colors cursor-pointer">
                        🖨️ Imprimir Ticket Térmico
                    </button>
                    <button type="button" wire:click="cerrarModalTicket"
                        class="w-full bg-red-600 hover:bg-red-500 text-white py-3 rounded-xl text-xs font-black tracking-wider uppercase transition-colors cursor-pointer shadow-lg shadow-red-900/40">
                        ✓ Nueva Venta Mostrador
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>

