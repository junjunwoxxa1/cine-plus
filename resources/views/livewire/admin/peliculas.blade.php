<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Películas en Cartelera</h1>
            <p class="text-sm text-zinc-400 mt-1">Añade o modifica películas, pósters, sinopsis, duración e idiomas.</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="crear">
            Nueva Película
        </flux:button>
    </div>

    <!-- Buscador -->
    <div class="max-w-md">
        <flux:input wire:model.live.debounce.300ms="busqueda" icon="magnifying-glass" placeholder="Buscar por título o género..." />
    </div>

    <!-- Tabla -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-zinc-950/60 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-800">
                    <tr>
                        <th class="px-6 py-4">Película</th>
                        <th class="px-6 py-4">Género</th>
                        <th class="px-6 py-4">Duración</th>
                        <th class="px-6 py-4">Clasificación</th>
                        <th class="px-6 py-4 text-center">Carrusel Banner</th>
                        <th class="px-6 py-4">Funciones</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($peliculas as $p)
                        <tr class="hover:bg-zinc-800/30 transition-colors" wire:key="pel-{{ $p->id }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $p->imagen_url }}" alt="{{ $p->titulo }}" class="w-12 h-16 object-cover rounded-lg border border-zinc-800 bg-zinc-950 shrink-0">
                                    <div>
                                        <p class="font-bold text-white leading-tight">{{ $p->titulo }}</p>
                                        <p class="text-xs text-zinc-400 mt-0.5">{{ $p->director ?? 'Director no asignado' }} &bull; {{ $p->idioma }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-zinc-300">{{ $p->genero }}</td>
                            <td class="px-6 py-4 text-xs text-zinc-400 font-mono whitespace-nowrap">{{ $p->duracion }} min</td>
                            <td class="px-6 py-4">
                                <span class="text-[10px] font-black uppercase tracking-wider bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded border border-zinc-700">
                                    {{ $p->clasificacion }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button type="button" wire:click="toggleBanner({{ $p->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $p->en_banner ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 hover:bg-amber-500/30' : 'bg-zinc-800/80 text-zinc-500 border border-zinc-700 hover:text-zinc-300 hover:border-zinc-600' }}">
                                    <span>{{ $p->en_banner ? '⭐ En Carrusel' : '☆ No visible' }}</span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-zinc-400 whitespace-nowrap">
                                {{ $p->funciones_count }} funciones
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <flux:button size="sm" variant="subtle" wire:click="editar({{ $p->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="danger" wire:click="eliminar({{ $p->id }})" wire:confirm="¿Seguro que deseas eliminar esta película?">Eliminar</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500 italic">No se encontraron películas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <p class="text-xs text-zinc-400 flex items-center gap-1.5">
                <span class="text-amber-400">💡 Tip:</span> Haz clic en el botón <span class="text-amber-400 font-bold">⭐ En Carrusel</span> de cualquier película para mostrarla u ocultarla instantáneamente en el carrusel de inicio.
            </p>
            {{ $peliculas->links() }}
        </div>
    </div>

    <!-- Modal Formulario -->
    <flux:modal wire:model="modalAbierto" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $peliculaId ? 'Editar Película' : 'Nueva Película' }}</flux:heading>
            <flux:subheading>Datos que se visualizarán en la cartelera principal y detalle.</flux:subheading>
        </div>

        <form wire:submit="guardar" class="space-y-4">
            <flux:field>
                <flux:label>Título de la Película</flux:label>
                <flux:input wire:model="titulo" placeholder="Ej. Interstellar" required />
                <flux:error name="titulo" />
            </flux:field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Director</flux:label>
                    <flux:input wire:model="director" placeholder="Ej. Christopher Nolan" />
                    <flux:error name="director" />
                </flux:field>

                <flux:field>
                    <flux:label>Género</flux:label>
                    <flux:input wire:model="genero" placeholder="Ej. Acción / Aventura" />
                    <flux:error name="genero" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Póster / Imagen (URL o path en storage)</flux:label>
                <flux:input wire:model="imagen" placeholder="peliculas/inception.jpg o https://..." />
                <flux:error name="imagen" />
            </flux:field>

            <flux:field>
                <flux:label>Sinopsis / Descripción</flux:label>
                <flux:textarea wire:model="descripcion" rows="3" placeholder="Sinopsis completa..." />
                <flux:error name="descripcion" />
            </flux:field>

            <div class="grid grid-cols-3 gap-3">
                <flux:field>
                    <flux:label>Duración (min)</flux:label>
                    <flux:input type="number" wire:model="duracion" required min="1" />
                    <flux:error name="duracion" />
                </flux:field>

                <flux:field>
                    <flux:label>Clasificación</flux:label>
                    <flux:input wire:model="clasificacion" placeholder="PG-13, R, ATP" />
                    <flux:error name="clasificacion" />
                </flux:field>

                <flux:field>
                    <flux:label>Idioma</flux:label>
                    <flux:input wire:model="idioma" placeholder="Español Latino / SUB" />
                    <flux:error name="idioma" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Fecha de Estreno</flux:label>
                <flux:input type="date" wire:model="fecha_estreno" />
                <flux:error name="fecha_estreno" />
            </flux:field>

            <div class="p-4 bg-zinc-950/60 border border-zinc-800 rounded-xl space-y-3">
                <flux:checkbox wire:model="en_banner" label="Destacar en el Carrusel Banner Principal de la Web" description="Si se activa, esta película aparecerá con su imagen de portada en el carrusel de inicio." />
                
                <div x-show="$wire.en_banner" class="pt-2">
                    <flux:field>
                        <flux:label>Orden de aparición en el Carrusel</flux:label>
                        <flux:input type="number" wire:model="orden_banner" min="0" placeholder="0 = primero" />
                        <flux:error name="orden_banner" />
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="subtle" wire:click="$set('modalAbierto', false)">Cancelar</flux:button>
                <flux:button variant="primary" type="submit">Guardar Película</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
