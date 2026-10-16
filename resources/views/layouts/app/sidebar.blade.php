<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-800 bg-zinc-900">
            <flux:sidebar.header>
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-2 py-1">
                    <span class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center font-black text-white text-sm shadow-md shadow-red-900/40">C+</span>
                    <div>
                        <span class="font-black text-white tracking-tight text-base block leading-none">CINE<span class="text-red-500">+</span></span>
                        <span class="text-[10px] text-zinc-400 uppercase tracking-widest font-bold">Mi Perfil</span>
                    </div>
                </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Configuración de Perfil" class="grid">
                    <flux:sidebar.item icon="user" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                        Datos y Puntos
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="lock-closed" :href="route('security.edit')" :current="request()->routeIs('security.edit')" wire:navigate>
                        Contraseña y Seguridad
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="swatch" :href="route('appearance.edit')" :current="request()->routeIs('appearance.edit')" wire:navigate>
                        Apariencia
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Regresar" class="grid mt-4">
                    <flux:sidebar.item icon="arrow-left" :href="route('home')">
                        Volver al Cine
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden border-b border-zinc-800 bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            Cerrar Sesión
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
