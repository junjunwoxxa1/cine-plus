<?php

namespace App\Livewire\Admin;

use App\Models\Butaca;
use App\Models\Sala;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Salas y Butacas - Admin')]
class Salas extends Component
{
    public $salas;
    public string $filtroSede = '';
    public bool $modalAbierto = false;

    public ?int $salaId = null;
    public string $numero_sala = '';
    public string $sede = 'Cine+ Alcázar (Miraflores)';
    public int $filas = 5;
    public int $asientos_por_fila = 10;
    public string $plantilla = 'clasica';

    protected function rules(): array
    {
        return [
            'numero_sala' => 'required|string|max:100',
            'sede' => 'required|string|max:150',
            'filas' => 'required|integer|min:1|max:26',
            'asientos_por_fila' => 'required|integer|min:1|max:30',
            'plantilla' => 'required|string|max:50',
        ];
    }

    public function mount(): void
    {
        $this->cargarSalas();
    }

    public function updatedFiltroSede(): void
    {
        $this->cargarSalas();
    }

    public function cargarSalas(): void
    {
        $query = Sala::with('butacas')->withCount(['butacas', 'funciones'])->orderBy('sede')->orderBy('numero_sala');
        if ($this->filtroSede !== '') {
            $query->where('sede', $this->filtroSede);
        }
        $this->salas = $query->get();
    }

    public function crear(): void
    {
        $this->reset(['salaId', 'numero_sala']);
        $this->sede = $this->filtroSede !== '' ? $this->filtroSede : 'Cine+ Alcázar (Miraflores)';
        $this->filas = 5;
        $this->asientos_por_fila = 10;
        $this->plantilla = 'escalera_col2';
        $this->numero_sala = 'Sala '.(Sala::count() + 1).' (2D)';
        $this->modalAbierto = true;
    }

    public function editar(int $id): void
    {
        $sala = Sala::with('butacas')->findOrFail($id);
        $this->salaId = $sala->id;
        $this->numero_sala = $sala->numero_sala;
        $this->sede = $sala->sede ?: 'Cine+ Alcázar (Miraflores)';
        $this->plantilla = $sala->plantilla ?: 'clasica';

        $porFila = $sala->butacas->groupBy(fn ($b) => explode('-', $b->numero_butaca)[0] ?? 'A');
        $this->filas = max(1, $porFila->count() ?: 5);
        $this->asientos_por_fila = max(1, $porFila->first()?->count() ?: 10);

        $this->modalAbierto = true;
    }

    public function guardar(): void
    {
        $validated = $this->validate();
        $letras = range('A', 'Z');

        $mapaConfig = Sala::generarConfiguracionPlantilla(
            $validated['plantilla'],
            $validated['filas'],
            $validated['asientos_por_fila']
        );
        $celdasEscalera = $mapaConfig['celdas_escalera'] ?? [];
        $capacidad = max(1, ($validated['filas'] * $validated['asientos_por_fila']) - count($celdasEscalera));

        if ($this->salaId) {
            $sala = Sala::with(['butacas', 'funciones'])->findOrFail($this->salaId);

            if ($validated['plantilla'] === 'personalizada' && is_array($sala->mapa_config)) {
                $mapaConfig = $sala->mapa_config;
                $celdasEscalera = $mapaConfig['celdas_escalera'] ?? [];
                $capacidad = max(1, ($validated['filas'] * $validated['asientos_por_fila']) - count($celdasEscalera));
            }

            $codigosEsperados = [];
            for ($f = 0; $f < $validated['filas']; $f++) {
                $filaLetra = $letras[$f];
                for ($num = 1; $num <= $validated['asientos_por_fila']; $num++) {
                    $codigosEsperados[] = "{$filaLetra}-{$num}";
                }
            }

            $sala->update([
                'numero_sala' => $validated['numero_sala'],
                'sede' => $validated['sede'],
                'capacidad' => $capacidad,
                'plantilla' => $validated['plantilla'],
                'mapa_config' => $mapaConfig,
            ]);

            $sala->butacas()->whereNotIn('numero_butaca', $codigosEsperados)->delete();
            $existentes = $sala->butacas()->pluck('numero_butaca')->toArray();

            $nuevas = [];
            foreach ($codigosEsperados as $codigo) {
                if (! in_array($codigo, $existentes, true)) {
                    $nuevas[] = [
                        'numero_butaca' => $codigo,
                        'sala_id' => $sala->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (! empty($nuevas)) {
                Butaca::insert($nuevas);
            }

            $butacasActivas = $sala->butacas()->get();
            $butacasIds = $butacasActivas->pluck('id')->toArray();

            foreach ($sala->funciones as $funcion) {
                DB::table('funcion_butaca')
                    ->where('funcion_id', $funcion->id)
                    ->whereNotIn('butaca_id', $butacasIds)
                    ->delete();

                $pivoteExistentes = DB::table('funcion_butaca')
                    ->where('funcion_id', $funcion->id)
                    ->pluck('butaca_id')
                    ->toArray();

                $faltantes = array_diff($butacasIds, $pivoteExistentes);
                $pivoteRows = [];
                foreach ($faltantes as $bId) {
                    $pivoteRows[] = [
                        'funcion_id' => $funcion->id,
                        'butaca_id' => $bId,
                        'disponible' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if (! empty($pivoteRows)) {
                    DB::table('funcion_butaca')->insert($pivoteRows);
                }

                $cfgF = $funcion->obtenerMapaConfig();
                $escF = $cfgF['celdas_escalera'] ?? [];
                $idsEscalera = $butacasActivas->whereIn('numero_butaca', $escF)->pluck('id')->toArray();

                $disponiblesQuery = DB::table('funcion_butaca')
                    ->where('funcion_id', $funcion->id)
                    ->where('disponible', true);

                if (! empty($idsEscalera)) {
                    $disponiblesQuery->whereNotIn('butaca_id', $idsEscalera);
                }

                $funcion->update(['asientos_disponibles' => $disponiblesQuery->count()]);
            }

            Flux::toast(variant: 'success', text: "Sala y plantilla actualizadas ({$capacidad} butacas útiles).");
        } else {
            $sala = Sala::create([
                'numero_sala' => $validated['numero_sala'],
                'sede' => $validated['sede'],
                'capacidad' => $capacidad,
                'plantilla' => $validated['plantilla'],
                'mapa_config' => $mapaConfig,
            ]);

            for ($f = 0; $f < $validated['filas']; $f++) {
                $filaLetra = $letras[$f];
                for ($num = 1; $num <= $validated['asientos_por_fila']; $num++) {
                    Butaca::create([
                        'numero_butaca' => "{$filaLetra}-{$num}",
                        'sala_id' => $sala->id,
                    ]);
                }
            }

            Flux::toast(variant: 'success', text: "Sala creada con su plantilla y {$capacidad} butacas útiles.");
        }

        $this->modalAbierto = false;
        $this->cargarSalas();
    }

    public function eliminar(int $id): void
    {
        $sala = Sala::withCount('funciones')->findOrFail($id);
        if ($sala->funciones_count > 0) {
            Flux::toast(variant: 'danger', text: "No se puede eliminar porque tiene {$sala->funciones_count} funciones asignadas.");

            return;
        }

        $sala->butacas()->delete();
        $sala->delete();
        Flux::toast(variant: 'success', text: 'Sala y sus butacas eliminadas.');
        $this->cargarSalas();
    }

    public function render()
    {
        $sedes = Sala::query()
            ->select('sede')
            ->distinct()
            ->whereNotNull('sede')
            ->where('sede', '!=', '')
            ->orderBy('sede')
            ->pluck('sede');

        return view('livewire.admin.salas', [
            'sedes' => $sedes,
            'catalogoPlantillas' => Sala::catalogoPlantillas(),
        ]);
    }
}
