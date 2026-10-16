<x-layouts::auth title="Recuperar Contraseña por Token - Cine+">
    <div class="flex flex-col gap-6 max-w-md mx-auto w-full">
        
        <div class="text-center space-y-2">
            <div class="w-12 h-12 bg-red-600/20 text-red-500 rounded-2xl flex items-center justify-center mx-auto border border-red-500/30 text-2xl">
                🔐
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Recuperar Cuenta</h1>
            <p class="text-xs text-gray-400">
                Ingresa tu correo electrónico registrado y te enviaremos un <strong>código token de 6 dígitos</strong> para autenticarte.
            </p>
        </div>

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

        <form method="POST" action="{{ route('password.send-token') }}" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">
                    Correo Electrónico
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="ejemplo@cineplus.com"
                    class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all shadow-inner">
            </div>

            <button type="submit"
                class="w-full bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider py-3 px-4 rounded-xl transition-all shadow-lg shadow-red-900/30 cursor-pointer flex items-center justify-center gap-2">
                <span>Enviar Código Token »</span>
            </button>
        </form>

        <div class="border-t border-gray-800/80 pt-4 text-center text-xs text-gray-400 space-y-2">
            <div>
                ¿Ya tienes un código token recibido? 
                <a href="{{ route('password.verify-token') }}" class="text-red-400 hover:text-red-300 font-bold underline">
                    Ingresar token aquí
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

