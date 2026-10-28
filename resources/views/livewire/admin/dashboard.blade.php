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
