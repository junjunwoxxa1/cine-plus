<div class="flex items-start gap-8 max-md:flex-col">
    <!-- Menú Lateral de Configuración de Perfil -->
    <div class="w-full md:w-60 shrink-0">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-3 shadow-lg space-y-1">
            <p class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-zinc-500">
                Ajustes de Perfil
            </p>
            <flux:navlist aria-label="Configuración">
                <flux:navlist.item icon="user" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                    Datos de Perfil
                </flux:navlist.item>
                <flux:navlist.item icon="lock-closed" :href="route('security.edit')" :current="request()->routeIs('security.edit')" wire:navigate>
                    Contraseña y Seguridad
                </flux:navlist.item>
                <flux:navlist.item icon="swatch" :href="route('appearance.edit')" :current="request()->routeIs('appearance.edit')" wire:navigate>
                    Apariencia
                </flux:navlist.item>
            </flux:navlist>
        </div>
    </div>

    <!-- Contenido Principal de Configuración -->
    <div class="flex-1 self-stretch min-w-0">
        @if (!empty($heading) || !empty($subheading))
            <div class="mb-5">
                <h2 class="text-xl font-black text-white tracking-tight">{{ $heading ?? '' }}</h2>
                @if (!empty($subheading))
                    <p class="text-sm text-zinc-400 mt-1">{{ $subheading }}</p>
                @endif
            </div>
        @endif

        <div class="w-full max-w-3xl space-y-6">
            {{ $slot }}
        </div>
    </div>
</div>
