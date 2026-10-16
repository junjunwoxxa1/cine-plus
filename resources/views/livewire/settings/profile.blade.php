<main class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-12 space-y-8">
    @php
        $usuario = auth()->user();
        $puntos = (int) ($usuario->saldo_puntos ?? 0);
    @endphp

    <!-- Encabezado de Cuenta y Saldo de Puntos -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-gray-800">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-lg font-bold text-white shrink-0">
                {{ $usuario->initials() }}
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">{{ $usuario->name }}</h1>
                <p class="text-sm text-gray-400">{{ $usuario->email }}</p>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-3.5 flex items-center justify-between sm:justify-end gap-6">
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400">Puntos Club Cine+</span>
                <span class="text-xs text-gray-500">50 pts = 1 entrada gratis</span>
            </div>
            <div class="text-right">
                <span class="text-2xl font-bold text-white">{{ number_format($puntos) }}</span>
                <span class="text-xs font-semibold text-gray-400 ml-0.5">pts</span>
            </div>
        </div>
    </div>

    <!-- Sección 1: Información Personal -->
    <section class="bg-gray-900/60 border border-gray-800 rounded-xl p-6 space-y-6">
        <div>
            <h2 class="text-base font-semibold text-white">Información personal</h2>
            <p class="text-sm text-gray-400 mt-0.5">Actualiza tu nombre y dirección de correo electrónico.</p>
        </div>

        <form wire:submit="updateProfileInformation" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model="name" label="Nombre completo" type="text" required autocomplete="name" />
                <flux:input wire:model="email" label="Correo electrónico" type="email" required autocomplete="email" />
            </div>

            @if ($this->hasUnverifiedEmail)
                <div class="text-xs text-amber-300 bg-amber-950/40 border border-amber-800/50 rounded-lg px-3.5 py-2.5">
                    Tu correo electrónico no está verificado.
                    <button type="button" class="underline font-semibold ml-1 cursor-pointer" wire:click.prevent="resendVerificationNotification">
                        Reenviar enlace de verificación
                    </button>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="primary" type="submit">
                    Guardar cambios
                </flux:button>
            </div>
        </form>
    </section>

    <!-- Sección 2: Cambiar Contraseña -->
    <section class="bg-gray-900/60 border border-gray-800 rounded-xl p-6 space-y-6">
        <div>
            <h2 class="text-base font-semibold text-white">Cambiar contraseña</h2>
            <p class="text-sm text-gray-400 mt-0.5">Ingresa tu contraseña actual para establecer una nueva.</p>
        </div>

        <form wire:submit="updatePassword" class="space-y-5">
            <flux:input
                wire:model="current_password"
                label="Contraseña actual"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input
                    wire:model="password"
                    label="Nueva contraseña"
                    type="password"
                    required
                    autocomplete="new-password"
                    viewable
                />

                <flux:input
                    wire:model="password_confirmation"
                    label="Confirmar nueva contraseña"
                    type="password"
                    required
                    autocomplete="new-password"
                    viewable
                />
            </div>

            <div class="flex justify-end">
                <flux:button variant="primary" type="submit">
                    Actualizar contraseña
                </flux:button>
            </div>
        </form>
    </section>

    <!-- Sección 3: Eliminar Cuenta -->
    @if ($this->showDeleteUser)
        <livewire:settings.delete-user-form />
    @endif
</main>
