<section class="w-full max-w-6xl mx-auto">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">Apariencia</flux:heading>

    <x-settings.layout heading="Apariencia y Tema Visual" subheading="Elige cómo prefieres visualizar la interfaz de tu cuenta en Cine+.">
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-6 shadow-lg space-y-4">
            <div>
                <h3 class="text-base font-bold text-white">Modo de Color</h3>
                <p class="text-xs text-zinc-400 mt-0.5">Selecciona entre el modo claro, el modo oscuro de sala de cine o el tema automático de tu dispositivo.</p>
            </div>

            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">Claro</flux:radio>
                <flux:radio value="dark" icon="moon">Oscuro (Cine+)</flux:radio>
                <flux:radio value="system" icon="computer-desktop">Sistema</flux:radio>
            </flux:radio.group>
        </div>
    </x-settings.layout>
</section>
