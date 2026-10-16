<section class="bg-gray-900/40 border border-gray-800 rounded-xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-base font-semibold text-white">Eliminar cuenta</h2>
        <p class="text-sm text-gray-400 mt-0.5">
            Esta acción es permanente y eliminará todos los datos asociados a tu perfil.
        </p>
    </div>

    <div class="shrink-0">
        <flux:modal.trigger name="confirm-user-deletion">
            <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
                Eliminar cuenta
            </flux:button>
        </flux:modal.trigger>
    </div>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-md">
        <form method="POST" wire:submit="deleteUser" class="space-y-5">
            <div>
                <flux:heading size="lg">Confirmar eliminación de cuenta</flux:heading>
                <flux:subheading>
                    Ingresa tu contraseña actual para confirmar que deseas eliminar tu cuenta de forma permanente.
                </flux:subheading>
            </div>

            <flux:input wire:model="password" label="Contraseña" type="password" viewable />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">Eliminar cuenta</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
