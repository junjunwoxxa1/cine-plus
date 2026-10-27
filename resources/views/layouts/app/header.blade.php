<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100">
        <flux:header container class="border-b border-zinc-800 bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('home') }}" />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="home" :href="route('home')">
                    Cartelera
                </flux:navbar.item>
                <flux:navbar.item icon="shopping-bag" :href="route('dulceria.index')">
                    Dulcería
                </flux:navbar.item>
                <flux:navbar.item icon="ticket" :href="route('compras.index')">
                    Mis Compras
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            @auth
                <a href="{{ route('profile.edit') }}"
                    title="Tus Puntos Club Cine+ (Clic para ver detalles)"
                    class="mr-2 inline-flex items-center gap-1.5 text-xs font-bold text-amber-300 hover:text-white bg-amber-500/10 border border-amber-500/30 hover:border-amber-400 px-2.5 sm:px-3 py-1.5 rounded-xl transition-all shadow-sm">
                    <span>⭐</span>
                    <span class="font-mono">{{ (int) auth()->user()->saldo_puntos }}</span>
                    <span class="text-[10px] text-amber-400/80 hidden xs:inline">pts</span>
                </a>
            @endauth

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-800 bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('home') }}" />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Cine+">
                    <flux:sidebar.item icon="home" :href="route('home')">
                        Cartelera Principal
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('dulceria.index')">
                        Dulcería Online
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="ticket" :href="route('compras.index')">
                        Mis Compras
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
