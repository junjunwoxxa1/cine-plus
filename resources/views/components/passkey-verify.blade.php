@php
    $enPasoVerificar = session('login_code_step') === 'verify' || $errors->has('login_code_token');
    $correoGuardado = session('login_code_email', old('email', ''));
    $abrirPanelCodigo = $enPasoVerificar || session('login_code_open') || $errors->has('login_code_email') || session('smtp_saved_status');
    $codigoLocal = session('dev_mail_code');
    $errorSmtp = session('dev_mail_error');
@endphp

<div x-data="{
        mostrarAccesoCodigo: {{ $abrirPanelCodigo ? 'true' : 'false' }},
        mostrarConfigSmtp: false,
        codigoInput: ''
    }" class="space-y-4">
    <!-- Botón de Llave de Acceso sin Contraseña -->
    <flux:button
        type="button"
        variant="outline"
        icon="key"
        class="w-full cursor-pointer"
        @click="mostrarAccesoCodigo = !mostrarAccesoCodigo"
    >
        <span x-show="!mostrarAccesoCodigo">Ingresar con llave de acceso (Código al correo)</span>
        <span x-show="mostrarAccesoCodigo" x-cloak>Usar inicio de sesión con contraseña</span>
    </flux:button>

    <!-- Panel Desplegable: Iniciar Sesión con Código enviado al Correo -->
    <div x-show="mostrarAccesoCodigo" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="rounded-xl border border-zinc-800 bg-zinc-900/90 p-4 space-y-4">

        @if (session('smtp_saved_status'))
            <div class="rounded-lg border border-emerald-500/40 bg-emerald-950/50 px-3 py-2 text-xs text-emerald-300 font-medium text-center">
                {{ session('smtp_saved_status') }}
            </div>
        @endif

        @if (session('login_code_status'))
            <div class="rounded-lg border border-emerald-500/40 bg-emerald-950/50 px-3 py-2 text-xs text-emerald-300 font-medium text-center">
                {{ session('login_code_status') }}
            </div>
        @endif

        @if ($errors->has('login_code_email') || $errors->has('login_code_token'))
            <div class="rounded-lg border border-red-500/40 bg-red-950/50 px-3 py-2 text-xs text-red-300 font-medium text-center">
                {{ $errors->first('login_code_email') ?: $errors->first('login_code_token') }}
            </div>
        @endif

        @if (! $enPasoVerificar)
            <!-- Paso 1: Pedir solo el correo registrado -->
            <div class="space-y-1">
                <h3 class="text-sm font-semibold text-white">Acceso sin contraseña</h3>
                <p class="text-xs text-zinc-400">
                    Ingresa tu correo registrado y te enviaremos un código de 6 dígitos para entrar directamente.
                </p>
            </div>

            <form method="POST" action="{{ route('login.send-code') }}" class="space-y-3">
                @csrf
                <div>
                    <label for="login_code_email_input" class="block text-xs font-medium text-zinc-300 mb-1">
                        Correo electrónico
                    </label>
                    <input
                        id="login_code_email_input"
                        type="email"
                        name="email"
                        value="{{ $correoGuardado }}"
                        required
                        placeholder="tucorreo@ejemplo.com"
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3.5 py-2 text-sm text-white placeholder-zinc-500 focus:border-red-500 focus:outline-none"
                    />
                </div>

                <flux:button variant="primary" type="submit" class="w-full cursor-pointer">
                    Enviar código de acceso al correo
                </flux:button>
            </form>
        @else
            <!-- Paso 2: Ingresar el código de 6 dígitos recibido en el correo -->
            <div class="space-y-1">
                <h3 class="text-sm font-semibold text-white">Ingresa tu código de acceso</h3>
                <p class="text-xs text-zinc-400">
                    Introduce el código de 6 dígitos enviado a <strong class="text-zinc-200">{{ $correoGuardado }}</strong>.
                </p>
            </div>

            <!-- Bandeja de Correo Simulada si MAIL_MAILER=log o si aún no hay SMTP configurado -->
            @if ($codigoLocal)
                <div class="rounded-xl border border-amber-500/40 bg-amber-950/30 p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                            📬 Correo Recibido (Modo Local)
                        </span>
                        <button
                            type="button"
                            @click="codigoInput = '{{ $codigoLocal }}'"
                            class="rounded-lg bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold text-[11px] px-2.5 py-1 transition cursor-pointer">
                            Autocompletar código
                        </button>
                    </div>

                    @if ($errorSmtp)
                        <p class="text-[11px] text-red-300 leading-relaxed">{{ $errorSmtp }}</p>
                    @endif

                    <div class="flex items-center justify-between rounded-lg bg-zinc-950/90 border border-amber-500/30 px-3 py-2">
                        <span class="text-xs text-zinc-400">Tu código de 6 dígitos:</span>
                        <span class="font-mono text-lg font-black tracking-widest text-amber-300">{{ $codigoLocal }}</span>
                    </div>

                    <div class="pt-1 border-t border-amber-500/20 flex items-center justify-between text-[11px] text-zinc-400">
                        <span>¿Quieres que llegue a tu Gmail real?</span>
                        <button type="button" @click="mostrarConfigSmtp = !mostrarConfigSmtp" class="text-amber-400 hover:text-amber-300 font-semibold underline cursor-pointer">
                            <span x-text="mostrarConfigSmtp ? 'Ocultar configuración SMTP' : 'Configurar Gmail SMTP'"></span>
                        </button>
                    </div>

                    <!-- Formulario rápido para configurar SMTP de Gmail en .env -->
                    <form x-show="mostrarConfigSmtp" x-cloak method="POST" action="{{ route('mail.save-smtp') }}" class="pt-2 space-y-2.5 border-t border-zinc-800">
                        @csrf
                        <p class="text-[11px] text-zinc-300 leading-relaxed">
                            Para enviar correos reales desde Gmail, ingresa tu correo y una <strong>Contraseña de Aplicación de Google</strong> (de 16 letras generada en <em>Cuenta de Google → Seguridad → Verificación en 2 pasos → Contraseñas de aplicaciones</em>):
                        </p>
                        <div>
                            <input type="email" name="smtp_email" value="{{ config('mail.mailers.smtp.username') !== 'null' ? config('mail.mailers.smtp.username') : $correoGuardado }}" required
                                placeholder="tucorreo@gmail.com"
                                class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-1.5 text-xs text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <input type="password" name="smtp_password" required
                                placeholder="Contraseña de aplicación de 16 letras (ej: abcd efgh ijkl mnop)"
                                class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-1.5 text-xs text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none">
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-zinc-800 hover:bg-zinc-700 border border-zinc-600 text-white font-semibold text-xs py-1.5 transition cursor-pointer">
                            Guardar SMTP Gmail en .env
                        </button>
                    </form>
                </div>
            @endif

            <form method="POST" action="{{ route('login.verify-code') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="email" value="{{ $correoGuardado }}">

                <div>
                    <label for="login_code_token_input" class="block text-xs font-medium text-zinc-300 mb-1">
                        Código de 6 dígitos
                    </label>
                    <input
                        id="login_code_token_input"
                        type="text"
                        name="token"
                        x-model="codigoInput"
                        maxlength="6"
                        required
                        autofocus
                        placeholder="123456"
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3.5 py-2.5 text-center font-mono text-xl font-bold tracking-widest text-white placeholder-zinc-600 focus:border-red-500 focus:outline-none"
                    />
                </div>

                <flux:button variant="primary" type="submit" class="w-full cursor-pointer">
                    Verificar código e ingresar
                </flux:button>
            </form>

            <div class="flex items-center justify-between text-xs text-zinc-400 pt-1">
                <a href="{{ route('login.reset-code-step') }}" class="hover:text-white underline">
                    Cambiar correo
                </a>
                <form method="POST" action="{{ route('login.send-code') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $correoGuardado }}">
                    <button type="submit" class="text-red-400 hover:text-red-300 font-medium underline cursor-pointer">
                        Reenviar código
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Separador -->
    <div class="relative my-4">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-zinc-200 dark:border-zinc-700"></div>
        </div>
        <div class="relative flex justify-center text-xs uppercase">
            <span class="px-2 text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-900">
                O ingresa con tu contraseña
            </span>
        </div>
    </div>
</div>

