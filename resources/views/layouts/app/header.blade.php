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
