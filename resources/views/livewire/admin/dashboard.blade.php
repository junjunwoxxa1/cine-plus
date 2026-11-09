<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Panel de Administración</h1>
            <p class="text-sm text-zinc-400 mt-1">Gestión general del cine, dulcería, catálogo y usuarios.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button variant="subtle" icon="calculator" :href="route('pos.dulceria')" target="_blank" class="border-amber-500/30 text-amber-400 hover:text-amber-300 bg-amber-500/10">
                🍿 POS Dulcería
            </flux:button>
            <flux:button variant="primary" icon="plus" :href="route('admin.productos')" wire:navigate>
                Nuevo Producto
            </flux:button>
            <flux:button variant="subtle" icon="film" :href="route('admin.peliculas')" wire:navigate>
                Nueva Película
            </flux:button>
        </div>
    </div>

    <!-- Metricas Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-3 sm:gap-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Sedes</span>
            <p class="text-3xl font-black text-white mt-1">{{ $totalSedes }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Películas</span>
            <p class="text-3xl font-black text-white mt-1">{{ $totalPeliculas }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Funciones</span>
            <p class="text-3xl font-black text-white mt-1">{{ $totalFunciones }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Salas</span>
            <p class="text-3xl font-black text-white mt-1">{{ $totalSalas }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Snacks</span>
            <p class="text-3xl font-black text-white mt-1">{{ $totalProductos }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Categorías</span>
            <p class="text-3xl font-black text-white mt-1">{{ $totalCategorias }}</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-sm">
            <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Usuarios</span>
            <p class="text-3xl font-black text-white mt-1">{{ $totalUsuarios }}</p>
        </div>
    </div>

    <!-- Dashboard y Ranking de Películas: Butacas Más Vendidas y Mayor Recaudación -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-5 sm:p-6 shadow-xl space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-800 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl">📊</span>
                    <h2 class="text-lg font-black text-white tracking-tight">Dashboard de Películas y Rendimiento</h2>
                </div>
                <p class="text-xs text-zinc-400 mt-1">
                    Análisis de taquilla: películas con más butacas compradas, mayor recaudación en ventas y estado de vigencia.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.peliculas') }}" class="text-xs font-bold text-amber-400 hover:text-amber-300 bg-amber-500/10 border border-amber-500/30 px-3 py-1.5 rounded-xl transition-colors" wire:navigate>
                    🎬 Gestionar Fechas y Películas &rarr;
                </a>
            </div>
        </div>

        {{-- Tarjetas Destacadas de Taquilla --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Más Recaudada --}}
            <div class="p-4 rounded-xl bg-zinc-950 border border-amber-500/30 shadow-md space-y-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                    <span>🏆</span> Película Más Taquillera
                </span>
                <p class="text-sm font-bold text-white truncate" title="{{ $peliculaMasRecaudada['titulo'] ?? 'Sin ventas' }}">
                    {{ $peliculaMasRecaudada['titulo'] ?? 'Sin registros' }}
                </p>
                <div class="flex items-baseline justify-between pt-1">
                    <span class="text-lg font-black text-amber-400 font-mono">
                        S/ {{ number_format($peliculaMasRecaudada['total_recaudado'] ?? 0, 2) }}
                    </span>
                    <span class="text-[11px] text-zinc-400 font-medium">
                        {{ $peliculaMasRecaudada['butacas_vendidas'] ?? 0 }} butacas
                    </span>
                </div>
            </div>

            {{-- Más Butacas Vendidas --}}
            <div class="p-4 rounded-xl bg-zinc-950 border border-red-500/30 shadow-md space-y-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-red-400 flex items-center gap-1.5">
                    <span>🎟️</span> Mayor Ocupación (Más Butacas)
                </span>
                <p class="text-sm font-bold text-white truncate" title="{{ $peliculaMasButacas['titulo'] ?? 'Sin ventas' }}">
                    {{ $peliculaMasButacas['titulo'] ?? 'Sin registros' }}
                </p>
                <div class="flex items-baseline justify-between pt-1">
                    <span class="text-lg font-black text-red-400 font-mono">
                        {{ $peliculaMasButacas['butacas_vendidas'] ?? 0 }} <span class="text-xs font-normal text-zinc-400">entradas</span>
                    </span>
                    <span class="text-[11px] text-zinc-400 font-medium">
                        S/ {{ number_format($peliculaMasButacas['total_recaudado'] ?? 0, 2) }}
                    </span>
                </div>
            </div>

            {{-- Total Recaudado en Boletos --}}
            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-800 shadow-md space-y-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
                    <span>💰</span> Recaudación Total en Entradas
                </span>
                <p class="text-2xl font-black text-white font-mono mt-1">
                    S/ {{ number_format($totalRecaudacionGeneral, 2) }}
                </p>
                <p class="text-[11px] text-zinc-500">Acumulado histórico pagado</p>
            </div>

            {{-- Total Butacas Vendidas --}}
            <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-800 shadow-md space-y-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400 flex items-center gap-1.5">
                    <span>🪑</span> Total Butacas Ocupadas
                </span>
                <p class="text-2xl font-black text-white font-mono mt-1">
                    {{ number_format($totalButacasVendidasGeneral) }}
                </p>
                <p class="text-[11px] text-zinc-500">Asientos confirmados por el cine</p>
            </div>
        </div>

        {{-- Tabla Resumen de Todas las Películas --}}
        <div class="border border-zinc-800 rounded-xl overflow-hidden bg-zinc-950">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-300">
                    <thead class="bg-zinc-900/90 uppercase text-[10px] font-bold text-zinc-400 border-b border-zinc-800">
                        <tr>
                            <th class="px-4 py-3">Película</th>
                            <th class="px-4 py-3 text-center">Butacas Vendidas</th>
                            <th class="px-4 py-3 text-right">Recaudación Total</th>
                            <th class="px-4 py-3 text-center">Funciones</th>
                            <th class="px-4 py-3">Temporada / Vigencia</th>
                            <th class="px-4 py-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60 font-medium">
                        @php
                            $maxRecaudacion = max(1, $resumenPeliculas->max('total_recaudado') ?: 1);
                        @endphp
                        @forelse ($resumenPeliculas as $pel)
                            <tr class="hover:bg-zinc-900/50 transition-colors {{ $pel['is_expirada'] ? 'opacity-60 bg-zinc-950' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $pel['imagen_url'] }}" alt="{{ $pel['titulo'] }}" class="w-9 h-12 object-cover rounded border border-zinc-800 bg-zinc-900 shrink-0">
                                        <div>
                                            <p class="font-bold text-white leading-tight">{{ $pel['titulo'] }}</p>
                                            <p class="text-[10px] text-zinc-400 mt-0.5">{{ $pel['genero'] }} &bull; {{ $pel['duracion'] }} min</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 font-mono font-bold text-white text-xs bg-zinc-900 px-2.5 py-1 rounded-md border border-zinc-800">
                                        🎟️ {{ $pel['butacas_vendidas'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <span class="font-mono font-black text-amber-400 text-sm">
                                        S/ {{ number_format($pel['total_recaudado'], 2) }}
                                    </span>
                                    <div class="w-24 ml-auto h-1 bg-zinc-800 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-amber-500 to-red-500 rounded-full"
                                            style="width: {{ min(100, round(($pel['total_recaudado'] / $maxRecaudacion) * 100)) }}%"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center text-zinc-400 whitespace-nowrap">
                                    {{ $pel['funciones_count'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($pel['is_expirada'])
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider bg-red-950 text-red-400 border border-red-800 px-2 py-0.5 rounded">
                                            ⛔ Expirada ({{ $pel['fecha_fin'] ? $pel['fecha_fin']->format('d/m/Y') : '' }})
                                        </span>
                                    @elseif ($pel['fecha_fin'])
                                        <span class="inline-flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider bg-emerald-950 text-emerald-300 border border-emerald-500/30 px-2 py-0.5 rounded">
                                            ✅ Hasta {{ $pel['fecha_fin']->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-[9px] font-medium text-zinc-400 bg-zinc-900 border border-zinc-800 px-2 py-0.5 rounded">
                                            ♾️ Continua
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.peliculas') }}" class="text-[11px] font-bold text-red-400 hover:text-red-300 hover:underline" wire:navigate>
                                        Detalles &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-zinc-500 italic">No hay películas registradas en el sistema.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Secciones Rápidas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Próximas Funciones -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h2 class="text-base font-bold text-white">Próximas Funciones</h2>
                <a href="{{ route('admin.funciones') }}" class="text-xs font-bold text-red-400 hover:text-red-300" wire:navigate>Ver todas &rarr;</a>
            </div>

            <div class="space-y-3">
                @forelse ($proximasFunciones as $funcion)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-950 border border-zinc-800/60">
                        <div>
                            <p class="text-sm font-bold text-white">{{ $funcion->pelicula->titulo }}</p>
                            <p class="text-xs text-zinc-400 mt-0.5">
                                {{ $funcion->sala->numero_sala }} &bull; {{ $funcion->fecha_hora->format('d/m/Y H:i') }} hrs
                            </p>
                        </div>
                        <span class="text-xs font-black text-white bg-zinc-900 border border-zinc-700 px-2.5 py-1 rounded-md">
                            S/ {{ number_format($funcion->precio, 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-zinc-500 italic py-4 text-center">No hay funciones próximas programadas.</p>
                @endforelse
            </div>
        </div>

        <!-- Accesos directos / Módulos -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 shadow-sm space-y-4">
            <div class="border-b border-zinc-800 pb-3">
                <h2 class="text-base font-bold text-white">Gestión de Catálogos</h2>
                <p class="text-xs text-zinc-400">Accede rápidamente a editar cualquier sección del sistema.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('admin.sedes') }}" class="p-3 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/80 transition-all flex items-center gap-3" wire:navigate>
                    <span class="text-xl">🏢</span>
                    <div>
                        <p class="text-xs font-bold text-white">Sedes / Cines</p>
                        <p class="text-[10px] text-zinc-400">Salas y complejos</p>
                    </div>
                </a>
                <a href="{{ route('admin.categorias') }}" class="p-3 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/80 transition-all flex items-center gap-3" wire:navigate>
                    <span class="text-xl">🏷️</span>
                    <div>
                        <p class="text-xs font-bold text-white">Categorías</p>
                        <p class="text-[10px] text-zinc-400">Dulcería</p>
                    </div>
                </a>
                <a href="{{ route('admin.productos') }}" class="p-3 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/80 transition-all flex items-center gap-3" wire:navigate>
                    <span class="text-xl">🍿</span>
                    <div>
                        <p class="text-xs font-bold text-white">Comidas y Snacks</p>
                        <p class="text-[10px] text-zinc-400">Precios y stock</p>
                    </div>
                </a>
                <a href="{{ route('admin.peliculas') }}" class="p-3 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/80 transition-all flex items-center gap-3" wire:navigate>
                    <span class="text-xl">🎬</span>
                    <div>
                        <p class="text-xs font-bold text-white">Películas</p>
                        <p class="text-[10px] text-zinc-400">Cartelera</p>
                    </div>
                </a>
                <a href="{{ route('admin.metodos-pago') }}" class="p-3 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/80 transition-all flex items-center gap-3" wire:navigate>
                    <span class="text-xl">💳</span>
                    <div>
                        <p class="text-xs font-bold text-white">Medios de Pago</p>
                        <p class="text-[10px] text-zinc-400">Activar/Desactivar</p>
                    </div>
                </a>
                <a href="{{ route('admin.descuentos') }}" class="p-3 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/80 transition-all flex items-center gap-3" wire:navigate>
                    <span class="text-xl">🎟️</span>
                    <div>
                        <p class="text-xs font-bold text-white">Promociones</p>
                        <p class="text-[10px] text-zinc-400">Códigos y descuentos</p>
                    </div>
                </a>
                <a href="{{ route('admin.usuarios') }}" class="p-3 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/80 transition-all flex items-center gap-3" wire:navigate>
                    <span class="text-xl">👥</span>
                    <div>
                        <p class="text-xs font-bold text-white">Usuarios</p>
                        <p class="text-[10px] text-zinc-400">Roles y permisos</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
