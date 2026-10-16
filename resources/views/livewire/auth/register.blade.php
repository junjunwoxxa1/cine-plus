<x-layouts::auth :title="__('Crear cuenta')">
    <div class="flex flex-col gap-6"
         x-data="{
            password: '',
            passwordConfirmation: '',
            showPassword: false,
            copied: false,
            get hasMinLength() { return this.password.length >= 8; },
            get hasUpper() { return /[A-Z]/.test(this.password); },
            get hasLower() { return /[a-z]/.test(this.password); },
            get hasNumber() { return /[0-9]/.test(this.password); },
            get hasSymbol() { return /[^A-Za-z0-9]/.test(this.password); },
            get passwordsMatch() { return this.password.length > 0 && this.password === this.passwordConfirmation; },
            get score() {
                let s = 0;
                if (this.hasMinLength) s++;
                if (this.hasUpper) s++;
                if (this.hasLower) s++;
                if (this.hasNumber) s++;
                if (this.hasSymbol) s++;
                return s;
            },
            get strengthLabel() {
                if (this.password.length === 0) return 'Sin contraseña';
                if (this.score <= 2) return 'Débil';
                if (this.score === 3) return 'Regular';
                if (this.score === 4) return 'Buena';
                return 'Muy segura';
            },
            get strengthColor() {
                if (this.password.length === 0) return 'bg-zinc-700';
                if (this.score <= 2) return 'bg-red-500';
                if (this.score === 3) return 'bg-amber-500';
                if (this.score === 4) return 'bg-blue-500';
                return 'bg-emerald-500';
            },
            get strengthTextColor() {
                if (this.password.length === 0) return 'text-zinc-500';
                if (this.score <= 2) return 'text-red-400';
                if (this.score === 3) return 'text-amber-400';
                if (this.score === 4) return 'text-blue-400';
                return 'text-emerald-400';
            },
            generateStrongPassword() {
                const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
                const lower = 'abcdefghijkmnopqrstuvwxyz';
                const digits = '23456789';
                const symbols = '@#$%&*!._-';
                const all = upper + lower + digits + symbols;

                let pwd = [
                    upper[Math.floor(Math.random() * upper.length)],
                    upper[Math.floor(Math.random() * upper.length)],
                    lower[Math.floor(Math.random() * lower.length)],
                    lower[Math.floor(Math.random() * lower.length)],
                    digits[Math.floor(Math.random() * digits.length)],
                    digits[Math.floor(Math.random() * digits.length)],
                    symbols[Math.floor(Math.random() * symbols.length)],
                    symbols[Math.floor(Math.random() * symbols.length)],
                ];

                for (let i = pwd.length; i < 14; i++) {
                    pwd.push(all[Math.floor(Math.random() * all.length)]);
                }

                pwd = pwd.sort(() => Math.random() - 0.5).join('');
                this.password = pwd;
                this.passwordConfirmation = pwd;
                this.showPassword = true;
                this.copied = false;
            },
            copyPassword() {
                if (!this.password) return;
                navigator.clipboard.writeText(this.password);
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2500);
            }
         }">
        <x-auth-header
            title="Crear una cuenta"
            description="Ingresa tus datos a continuación para registrarte en Cine+"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Nombre completo -->
            <flux:input
                name="name"
                label="Nombre completo"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Ej. Juan Pérez"
            />

            <!-- Correo electrónico -->
            <flux:input
                name="email"
                label="Correo electrónico"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="correo@ejemplo.com"
            />

            <!-- Contraseña con asistente de seguridad -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <label for="register_password" class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        Contraseña
                    </label>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="generateStrongPassword()"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/30 bg-red-500/10 px-2.5 py-1 text-xs font-semibold text-red-400 hover:bg-red-500/20 hover:text-red-300 transition cursor-pointer"
                            title="Generar una contraseña segura automáticamente">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Sugerir contraseña segura
                        </button>

                        <button
                            type="button"
                            x-show="password.length > 0"
                            x-cloak
                            @click="copyPassword()"
                            class="inline-flex items-center gap-1 rounded-lg border border-zinc-700 bg-zinc-800 px-2 py-1 text-xs font-medium text-zinc-300 hover:bg-zinc-700 transition cursor-pointer">
                            <span x-text="copied ? '¡Copiada!' : 'Copiar'"></span>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <input
                        id="register_password"
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        x-model="password"
                        required
                        autocomplete="new-password"
                        placeholder="Crea una contraseña segura"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/90 px-3.5 py-2.5 pr-10 text-sm text-zinc-900 dark:text-white placeholder-zinc-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 hover:text-zinc-200"
                        tabindex="-1">
                        <svg x-show="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>

                @error('password')
                    <p class="text-xs font-medium text-red-400">{{ $message }}</p>
                @enderror

                <!-- Barra e indicadores de nivel de seguridad -->
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/70 p-3 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium">Nivel de seguridad:</span>
                        <span class="font-bold" :class="strengthTextColor" x-text="strengthLabel"></span>
                    </div>

                    <!-- Barra segmentada de 5 niveles -->
                    <div class="grid grid-cols-5 gap-1.5 h-1.5">
                        <template x-for="n in 5" :key="n">
                            <div class="h-full rounded-full transition-colors duration-300"
                                 :class="score >= n ? strengthColor : 'bg-zinc-200 dark:bg-zinc-800'"></div>
                        </template>
                    </div>

                    <!-- Checklist de requisitos en vivo -->
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pt-1 text-xs">
                        <li class="flex items-center gap-1.5" :class="hasMinLength ? 'text-emerald-400 font-medium' : 'text-zinc-500 dark:text-zinc-400'">
                            <span x-text="hasMinLength ? '✓' : '○'" class="font-bold"></span>
                            Mínimo 8 caracteres
                        </li>
                        <li class="flex items-center gap-1.5" :class="hasUpper ? 'text-emerald-400 font-medium' : 'text-zinc-500 dark:text-zinc-400'">
                            <span x-text="hasUpper ? '✓' : '○'" class="font-bold"></span>
                            Una mayúscula (A-Z)
                        </li>
                        <li class="flex items-center gap-1.5" :class="hasLower ? 'text-emerald-400 font-medium' : 'text-zinc-500 dark:text-zinc-400'">
                            <span x-text="hasLower ? '✓' : '○'" class="font-bold"></span>
                            Una minúscula (a-z)
                        </li>
                        <li class="flex items-center gap-1.5" :class="hasNumber ? 'text-emerald-400 font-medium' : 'text-zinc-500 dark:text-zinc-400'">
                            <span x-text="hasNumber ? '✓' : '○'" class="font-bold"></span>
                            Al menos un número (0-9)
                        </li>
                        <li class="flex items-center gap-1.5 sm:col-span-2" :class="hasSymbol ? 'text-emerald-400 font-medium' : 'text-zinc-500 dark:text-zinc-400'">
                            <span x-text="hasSymbol ? '✓' : '○'" class="font-bold"></span>
                            Un carácter especial (@ # $ % & * ! . _ -)
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Confirmar contraseña -->
            <div class="space-y-1.5">
                <label for="register_password_confirmation" class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    Confirmar contraseña
                </label>
                <input
                    id="register_password_confirmation"
                    name="password_confirmation"
                    :type="showPassword ? 'text' : 'password'"
                    x-model="passwordConfirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Repite tu contraseña"
                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/90 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-white placeholder-zinc-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                />
                <p x-show="passwordConfirmation.length > 0" x-cloak class="text-xs font-medium"
                   :class="passwordsMatch ? 'text-emerald-400' : 'text-amber-400'"
                   x-text="passwordsMatch ? '✓ Las contraseñas coinciden' : 'Las contraseñas aún no coinciden'">
                </p>
            </div>

            <div class="flex items-center justify-end pt-1">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    Crear cuenta
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>¿Ya tienes una cuenta?</span>
            <flux:link :href="route('login')" wire:navigate>Inicia sesión</flux:link>
        </div>
    </div>
</x-layouts::auth>

