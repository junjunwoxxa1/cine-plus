@php
    $codigoLocal = session('dev_mail_code');
    $errorSmtp = session('dev_mail_error');
@endphp

<x-layouts::auth title="Verificar Token y Cambiar Contraseña - Cine+">
    <div class="flex flex-col gap-5 max-w-md mx-auto w-full"
         x-data="{
            tokenInput: '{{ old('token', '') }}',
            mostrarConfigSmtp: false
         }">

        <div class="text-center space-y-2">
            <div class="w-12 h-12 bg-amber-500/20 text-amber-400 rounded-2xl flex items-center justify-center mx-auto border border-amber-500/30 text-2xl">
                🔑
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Autenticador por Token</h1>
            <p class="text-xs text-gray-400">
                Ingresa el código numérico de <strong>6 dígitos</strong> enviado a tu correo y define tu nueva contraseña.
            </p>
        </div>

        @if (session('smtp_saved_status'))
            <div class="p-3 bg-emerald-950/60 border border-emerald-500/50 rounded-xl text-emerald-300 text-xs font-semibold text-center">
                {{ session('smtp_saved_status') }}
            </div>
        @endif

        <!-- Session Status -->
        @if (session('status'))
            <div class="p-3 bg-green-950/60 border border-green-500/50 rounded-xl text-green-300 text-xs font-semibold text-center">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3 bg-red-950/60 border border-red-500/50 rounded-xl text-red-300 text-xs font-semibold text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Formulario para ingresar Token y Nueva Contraseña -->

        <form method="POST" action="{{ route('password.reset-token') }}" class="flex flex-col gap-4">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">
                    Correo Electrónico
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required
                    placeholder="ejemplo@cineplus.com"
                    class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all shadow-inner">
            </div>

            <!-- Token de 6 Dígitos -->
            <div>
                <label for="token" class="block text-xs font-bold uppercase tracking-wider text-amber-400 mb-1.5 flex items-center justify-between">
                    <span>Código Token (6 Dígitos)</span>
                    <span class="text-[10px] text-gray-500 font-normal">Válido por 15 min</span>
                </label>
                <input type="text" id="token" name="token" x-model="tokenInput" required autofocus maxlength="6"
                    placeholder="123456"
                    class="w-full bg-gray-950 border border-amber-500/50 rounded-xl px-4 py-3 text-center text-2xl font-black font-mono tracking-widest text-amber-300 placeholder-gray-700 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-all shadow-inner">
            </div>

            <!-- Nueva Contraseña -->
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">
                    Nueva Contraseña
                </label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                    class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all shadow-inner">
            </div>

            <!-- Confirmar Contraseña -->
            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">
                    Confirmar Nueva Contraseña
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                    placeholder="Repite tu nueva contraseña"
                    class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all shadow-inner">
            </div>

            <button type="submit"
                class="w-full bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-red-900/40 cursor-pointer flex items-center justify-center gap-2">
                <span>Restablecer Contraseña Ahora »</span>
            </button>
        </form>

        <div class="border-t border-gray-800/80 pt-4 text-center text-xs text-gray-400 space-y-2">
            <div>
                ¿No recibiste el código?
                <a href="{{ route('password.forgot-token') }}" class="text-amber-400 hover:text-amber-300 font-bold underline">
                    Solicitar nuevo token
                </a>
            </div>
            <div>
                <span>O regresa a </span>
                <a href="{{ route('login') }}" class="text-gray-300 hover:text-white underline">
                    Iniciar sesión
                </a>
            </div>
        </div>

    </div>
</x-layouts::auth>


