<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 font-sans antialiased">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-800 bg-zinc-900">
            <flux:sidebar.header class="flex items-center gap-2">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-white font-bold tracking-tight text-lg" wire:navigate>
                    <span class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center text-white font-black text-sm shadow-md shadow-red-900/50">C+</span>
                    <span>Admin Panel</span>
                </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="General" class="grid">
                    <flux:sidebar.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                        Dashboard
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Dulcería / Comidas" class="grid">
                    <flux:sidebar.item icon="tag" :href="route('admin.categorias')" :current="request()->routeIs('admin.categorias')" wire:navigate>
                        Categorías
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('admin.productos')" :current="request()->routeIs('admin.productos')" wire:navigate>
                        Productos / Snacks
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cube" :href="route('admin.inventario')" :current="request()->routeIs('admin.inventario')" wire:navigate>
                        Inventario & Stock
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calculator" :href="route('pos.dulceria')" target="_blank">
                        POS Dulcería (Mostrador)
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Cine y Cartelera" class="grid">
                    <flux:sidebar.item icon="building-office-2" :href="route('admin.sedes')" :current="request()->routeIs('admin.sedes')" wire:navigate>
                        Sedes
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="film" :href="route('admin.peliculas')" :current="request()->routeIs('admin.peliculas')" wire:navigate>
                        Películas
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" :href="route('admin.funciones')" :current="request()->routeIs('admin.funciones')" wire:navigate>
                        Funciones / Horarios
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="tv" :href="route('admin.salas')" :current="request()->routeIs('admin.salas')" wire:navigate>
                        Salas y Butacas
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Ventas y Clientes" class="grid">
                    <flux:sidebar.item icon="credit-card" :href="route('admin.metodos-pago')" :current="request()->routeIs('admin.metodos-pago')" wire:navigate>
                        Medios de Pago
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="receipt-percent" :href="route('admin.descuentos')" :current="request()->routeIs('admin.descuentos')" wire:navigate>
                        Promociones y Descuentos
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('admin.usuarios')" :current="request()->routeIs('admin.usuarios')" wire:navigate>
                        Usuarios y Roles
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="arrow-top-right-on-square" :href="route('home')" target="_blank">
                    Ver Sitio Web
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden border-b border-zinc-800 bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-red-600 flex items-center justify-center text-white font-bold text-xs">C+</span>
                <span class="font-bold text-sm">Cine+ Admin</span>
            </div>
            <flux:spacer />
            <x-desktop-user-menu :name="auth()->user()->name" />
        </flux:header>

        <flux:main class="p-4 sm:p-6 max-w-7xl mx-auto w-full">
            {{ $slot }}
        </flux:main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

