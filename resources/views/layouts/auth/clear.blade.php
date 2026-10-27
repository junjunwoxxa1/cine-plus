<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen pb-16 sm:pb-0">
    <!-- Header Navigation -->
    <header
        class="border-b border-gray-800 bg-gray-950/85 backdrop-blur-md sticky top-0 z-50 py-3 px-4 sm:px-6 flex items-center justify-between gap-2">
        <!-- Logo -->
        <div class="flex items-center shrink-0">
            <a href="{{ route('home') }}">
                <x-app-logo-icon class="max-w-16 sm:max-w-20 fill-current text-white dark:text-black" />
            </a>
        </div>

        <!-- Actions Navigation -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Link Directo a Cartelera -->
            <a href="{{ route('home') }}#cartelera"
                class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-gray-300 hover:text-white bg-gray-900 border border-gray-800 hover:border-gray-700 px-3.5 py-1.5 rounded-xl transition-all shadow-sm">
                <span>🎬</span>
                <span>Cartelera</span>
            </a>
            <!-- Link Directo a Dulcería Online -->
            <a href="{{ route('dulceria.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-300 hover:text-white bg-amber-500/10 border border-amber-500/30 hover:border-amber-400 px-3 sm:px-3.5 py-1.5 rounded-xl transition-all shadow-sm">
                <span>🍿</span>
                <span>Dulcería</span>
            </a>
            <!-- Mis Compras Link -->
            <a href="{{ route('compras.index') }}"
                class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-gray-300 hover:text-white bg-gray-900 border border-gray-800 hover:border-gray-700 px-3.5 py-1.5 rounded-xl transition-all shadow-sm">
                <span>🎟️</span>
                <span>Mis Compras</span>
            </a>

            @auth
                <div class="flex items-center gap-2 sm:gap-3">
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-[11px] sm:text-xs font-bold text-red-400 hover:text-red-300 bg-red-600/10 border border-red-500/30 px-2.5 sm:px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <span>⚙️</span>
                            <span class="hidden md:inline">Panel Admin</span>
                        </a>
                    @endif

                    @if (auth()->user()->canAccessPos())
                        <a href="{{ route('pos.dulceria')" target="_blank" class="hidden sm:inline-flex text-[11px] sm:text-xs font-bold text-amber-400 hover:text-amber-300 bg-amber-500/10 border border-amber-500/30 px-2.5 sm:px-3 py-1.5 rounded-lg transition-colors items-center gap-1">
                            <span>🍿</span>
                            <span>POS Dulcería</span>
                        </a>
                    @endif

                    {{-- Saldo de Puntos en la Barra Superior --}}
                    <a href="{{ route('profile.edit') }}"
                        title="Tus Puntos Club Cine+ (Clic para ver detalles)"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-300 hover:text-white bg-amber-500/10 border border-amber-500/30 hover:border-amber-400 px-2.5 sm:px-3 py-1.5 rounded-xl transition-all shadow-sm">
                        <span>⭐</span>
                        <span class="font-mono">{{ (int) auth()->user()->saldo_puntos }}</span>
                        <span class="text-[10px] text-amber-400/80 hidden xs:inline">pts</span>
                    </a>

                    <flux:dropdown position="bottom" align="end">
                        <button type="button" class="border border-gray-700 bg-gray-800 hover:border-gray-500 rounded-full w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center font-bold text-xs uppercase cursor-pointer transition-all shadow-md text-white">
                            {{ auth()->user()->initials() }}
                        </button>
                        <flux:menu class="w-64">
                            <div class="px-2 py-1.5 text-xs text-zinc-400">
                                <p class="font-bold text-white text-sm">{{ auth()->user()->name }}</p>
                                <p class="truncate">{{ auth()->user()->email }}</p>
                            </div>

                            {{-- Tarjeta de Puntos en el Menú de Perfil --}}
                            <div class="px-2.5 py-2 my-1 rounded-xl bg-zinc-950 border border-zinc-800 space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-zinc-400 font-semibold flex items-center gap-1">
                                        <span>⭐</span>
                                        <span>Puntos Club Cine+:</span>
                                    </span>
                                    <span class="font-mono font-black text-amber-400 text-sm">
                                        {{ (int) auth()->user()->saldo_puntos }} pts
                                    </span>
                                </div>
                                @if (\App\Models\Configuracion::puntosSeparadosActivo())
                                    <div class="pt-1.5 border-t border-zinc-800/80 flex items-center justify-between text-[11px] text-zinc-400">
                                        <span class="flex items-center gap-1">
                                            <span>🎟️ Cine:</span>
                                            <strong class="text-zinc-200">{{ (int) auth()->user()->puntos_cine }}</strong>
                                        </span>
                                        <span class="text-zinc-600">•</span>
                                        <span class="flex items-center gap-1">
                                            <span>🍿 Dulcería:</span>
                                            <strong class="text-zinc-200">{{ (int) auth()->user()->puntos_dulceria }}</strong>
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <flux:menu.separator />
                            <flux:menu.item :href="route('compras.index')" icon="ticket">
                                Mis Compras & Entradas
                            </flux:menu.item>
                            <flux:menu.item :href="route('profile.edit')" icon="cog">
                                Mi Cuenta
                            </flux:menu.item>
                            @if (auth()->user()->isAdmin())
                                <flux:menu.item :href="route('admin.dashboard')" icon="command-line">
                                    Panel de Administración
                                </flux:menu.item>
                            @endif
                            @if (auth()->user()->canAccessPos())
                                <flux:menu.item :href="route('pos.dulceria')" icon="calculator" target="_blank">
                                    Punto de Venta (POS)
                                </flux:menu.item>
                            @endif
                            <flux:menu.separator />
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer text-red-400">
                                    Cerrar Sesión
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            @else
                <a href="{{ route('register') }}"
                    class="hidden xs:inline-flex font-semibold text-xs sm:text-sm text-gray-300 hover:text-white transition-colors items-center gap-1 hover:underline underline-offset-4">
                    Regístrate
                </a>
                <a href="{{ route('login') }}"
                    class="font-bold text-xs sm:text-sm text-white bg-red-600 hover:bg-red-500 px-3 py-1.5 rounded-xl transition-colors shadow-sm">
                    Ingresar
                </a>
            @endauth
        </div>
    </header>

    {{ $slot }}

    <!-- Barra de Navegación Inferior Fija para Celulares -->
    <nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 bg-gray-950/95 backdrop-blur-xl border-t border-gray-800 px-2 py-1.5 flex items-center justify-around shadow-[0_-10px_25px_rgba(0,0,0,0.8)] print:hidden">
        <a href="{{ route('home') }}#cartelera"
            class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-[10px] font-bold transition-colors {{ request()->routeIs('home') ? 'text-red-400' : 'text-gray-400 hover:text-white' }}">
            <span class="text-base leading-none mb-0.5">🎬</span>
            <span>Cartelera</span>
        </a>
        <a href="{{ route('dulceria.index') }}"
            class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-[10px] font-bold transition-colors {{ request()->routeIs('dulceria.*') ? 'text-amber-400' : 'text-gray-400 hover:text-white' }}">
            <span class="text-base leading-none mb-0.5">🍿</span>
            <span>Dulcería</span>
        </a>
        <a href="{{ route('compras.index') }}"
            class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-[10px] font-bold transition-colors {{ request()->routeIs('compras.*') ? 'text-red-400' : 'text-gray-400 hover:text-white' }}">
            <span class="text-base leading-none mb-0.5">🎟️</span>
            <span>Mis Compras</span>
        </a>
        @auth
            <a href="{{ route('profile.edit') }}"
                class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-[10px] font-bold text-gray-400 hover:text-white transition-colors">
                <span class="text-base leading-none mb-0.5">👤</span>
                <span>Mi Cuenta</span>
            </a>
        @else
            <a href="{{ route('login') }}"
                class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-[10px] font-bold text-gray-400 hover:text-white transition-colors">
                <span class="text-base leading-none mb-0.5">🔑</span>
                <span>Ingresar</span>
            </a>
        @endauth
    </nav>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
