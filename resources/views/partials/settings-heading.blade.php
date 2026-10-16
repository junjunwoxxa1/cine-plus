@php
    $usuarioActual = auth()->user();
    $puntos = (int) ($usuarioActual->saldo_puntos ?? 0);
    $entradasCanjeables = intdiv($puntos, 50);
    $progresoSiguiente = min(100, round((($puntos % 50) / 50) * 100));
    $faltanPuntos = 50 - ($puntos % 50);
    $rolEtiqueta = $usuarioActual->isAdmin()
        ? '🛡️ Administrador Cine+'
        : ($usuarioActual->isDulceria() ? '🍿 Operador Dulcería / POS' : '⭐ Socio VIP Club Cine+');
@endphp

<div class="relative mb-8 w-full overflow-hidden rounded-3xl border border-zinc-800 bg-gradient-to-br from-zinc-900 via-zinc-900 to-zinc-950 p-6 sm:p-8 shadow-2xl">
    <!-- Resplandor decorativo -->
    <div class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-red-600/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-amber-500/10 blur-3xl"></div>

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <!-- Avatar e Información del Socio -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-5">
            <div class="h-16 w-16 sm:h-20 sm:w-20 shrink-0 rounded-2xl bg-gradient-to-br from-red-600 to-amber-600 p-0.5 shadow-xl shadow-red-950/50">
                <div class="flex h-full w-full items-center justify-center rounded-[14px] bg-zinc-950/90 text-xl sm:text-2xl font-black text-white">
                    {{ $usuarioActual->initials() }}
                </div>
            </div>

            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-red-500/15 border border-red-500/30 px-3 py-0.5 text-[11px] font-black uppercase tracking-wider text-red-400">
                        {{ $rolEtiqueta }}
                    </span>
                    @if ($usuarioActual->created_at)
                        <span class="text-xs text-zinc-400 font-medium">
                            • Miembro desde {{ $usuarioActual->created_at->translatedFormat('M Y') }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    {{ $usuarioActual->name }}
                </h1>
                <p class="text-sm text-zinc-400 font-medium flex items-center gap-2">
                    <span>✉️ {{ $usuarioActual->email }}</span>
                </p>
            </div>
        </div>

        <!-- Tarjeta Digital de Puntos Club Cine+ -->
        <div class="w-full lg:w-80 rounded-2xl border border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-zinc-950/90 to-red-600/10 p-4 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-widest text-amber-400">🎟️ Tarjeta Club Cine+</span>
                @if ($entradasCanjeables > 0)
                    <span class="rounded-full bg-emerald-500/20 border border-emerald-500/40 px-2.5 py-0.5 text-[10px] font-black text-emerald-300">
                        {{ $entradasCanjeables }} {{ $entradasCanjeables === 1 ? 'entrada canjeable' : 'entradas canjeables' }}
                    </span>
                @endif
            </div>

            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-white">{{ number_format($puntos) }}</span>
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">puntos acumulados</span>
            </div>

            <div class="mt-3 space-y-1.5">
                <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-red-500 transition-all duration-500"
                        style="width: {{ $puntos >= 50 && ($puntos % 50 === 0) ? 100 : $progresoSiguiente }}%"></div>
                </div>
                <p class="text-[11px] text-zinc-400">
                    @if ($puntos >= 50)
                        ¡Tienes puntos suficientes para canjear entradas gratis al pagar!
                    @else
                        Te faltan <strong class="text-amber-300">{{ $faltanPuntos }} pts</strong> para tu próxima entrada gratis (50 pts).
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
