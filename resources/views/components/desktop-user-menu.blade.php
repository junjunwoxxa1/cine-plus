<flux:dropdown position="bottom" align="start">
    <flux:sidebar.profile
        :name="auth()->user()->name"
        :initials="auth()->user()->initials()"
        icon:trailing="chevrons-up-down"
        data-test="sidebar-menu-button"
    />

    <flux:menu>
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

        {{-- Tarjeta de Puntos en el Menú de Usuario --}}
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
        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>
                Mi Perfil
            </flux:menu.item>
            @if (auth()->user()->canAccessPos())
                <flux:menu.item :href="route('pos.dulceria')" icon="calculator" target="_blank">
                    Punto de Venta (POS)
                </flux:menu.item>
            @endif
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
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
