<x-layouts::auth title="Iniciar Sesión - Cine+">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Inicia sesión en tu cuenta" description="Elige ingresar con llave de acceso por correo o con tu contraseña" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <!-- Opción 1: Llave de Acceso sin Contraseña (Código de 6 dígitos al correo) -->
        <x-passkey-verify />

        <!-- Opción 2: Inicio de Sesión con Correo y Contraseña -->
        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                label="Correo electrónico"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="correo@ejemplo.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    label="Contraseña"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Tu contraseña"
                    viewable
                />

                <a class="absolute top-0 text-xs end-0 text-red-400 hover:text-red-300 font-semibold" href="{{ route('password.forgot-token') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" label="Recordar mi sesión" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    Iniciar sesión
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>¿No tienes una cuenta?</span>
            <flux:link :href="route('register')" wire:navigate>Regístrate aquí</flux:link>
        </div>
    </div>
</x-layouts::auth>
