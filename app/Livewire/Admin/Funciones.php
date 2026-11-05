<?php

namespace App\Livewire\Admin;

use App\Models\Butaca;
use App\Models\Funcion;
use App\Models\Pelicula;
use App\Models\Sala;
use App\Models\Sede;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Funciones, Salas y Butacas - Admin')]
class Funciones extends Component
{
    use WithPagination;

    // Filtros y modo de visualización
    public string $modoVista = 'cines'; // 'cines' | 'peliculas' | 'tabla'

    public string $filtroSede = '';

    public string $filtroSala = '';

    public string $filtroPelicula = '';

    public string $filtroFecha = '';

    // Modal Crear / Editar Función
    public bool $modalAbierto = false;

    public ?int $funcionId = null;

    public ?int $pelicula_id = null;

    public ?int $sala_id = null;

    public string $fecha_hora = '';

    public $precio = 25.00;

    public $precio_puntos = 250;

    public string $plantilla_funcion = 'heredar';

    public array $horariosAdicionales = [];

    public string $nuevoHorarioExtra = '';

    public ?string $errorConflicto = null;

    // Modal Mapa Interactivo de Butacas y Plantillas de Forma
    public bool $modalButacas = false;

    public ?int $funcionButacasId = null;

    public string $modoClicMapa = 'ocupacion'; // 'ocupacion' | 'escaleras'

    public bool $aplicarPlantillaASala = true;

    // Modal Edición Rápida de Sala y Matriz de Butacas
    public bool $modalSala = false;

    public ?int $editSalaId = null;

    public string $sala_numero = '';

    public string $sala_sede = 'Cine+ Alcázar (Miraflores)';

    public $sala_filas = 5;

    public $sala_asientos_por_fila = 10;

    public string $sala_plantilla = 'clasica';

    public bool $guardando = false;

    protected function rules(): array
    {
        return [
            'pelicula_id' => 'required|exists:pelicula,id',
            'sala_id' => 'required|exists:sala,id',
            'fecha_hora' => 'required|date',
            'precio' => 'required|numeric|min:1',
            'precio_puntos' => 'required|integer|min:0',
        ];
    }

    public function updatedPeliculaId(): void
    {
        $this->verificarConflictoEnVivo();
    }

    public function updatedSalaId(): void
    {
        $this->verificarConflictoEnVivo();
    }

    public function updatedFechaHora(): void
    {
        $this->verificarConflictoEnVivo();
    }

    public function verificarConflictoEnVivo(): void
    {
        $this->errorConflicto = null;

        if (! $this->modalAbierto || ! $this->sala_id || empty($this->fecha_hora) || ! $this->pelicula_id) {
            return;
        }

        try {
            $dtInicio = Carbon::parse($this->fecha_hora);
            $fechaDia = $dtInicio->format('Y-m-d');
            $pelicula = Pelicula::find($this->pelicula_id);
            $duracion = (int) ($pelicula?->duracion ?: 120);

            $funcionesDelDia = Funcion::with('pelicula')
                ->where('sala_id', $this->sala_id)
                ->whereDate('fecha_hora', $fechaDia)
                ->when($this->funcionId, fn ($q) => $q->where('id', '!=', $this->funcionId))
                ->get();

            $conflicto = $funcionesDelDia->first(fn ($item) => $item->colisionaCon($dtInicio, $duracion));

            if ($conflicto) {
                $this->errorConflicto = sprintf(
                    'La sala ya está ocupada por «%s» de %s a %s hrs (incluye 15 min de limpieza).',
                    $conflicto->pelicula->titulo,
                    $conflicto->fecha_hora->format('H:i'),
                    $conflicto->fecha_fin_limpieza->format('H:i')
                );
            }
        } catch (\Exception $e) {
        }
    }

    public function updatedFiltroSede(): void
    {
        $this->filtroSala = '';
        $this->resetPage();
    }

    public function updatedFiltroSala(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroPelicula(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroFecha(): void
    {
        $this->resetPage();
    }

    public function seleccionarFecha(string $fecha): void
    {
        $this->filtroFecha = $this->filtroFecha === $fecha ? '' : $fecha;
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['filtroSede', 'filtroSala', 'filtroPelicula', 'filtroFecha']);
        $this->resetPage();
    }

    public function updatedPrecio($val): void
    {
        if (is_numeric($val)) {
            $this->precio_puntos = (int) round((float) $val * 10);
        }
    }

    public function agregarHorarioExtra(?string $horaRapida = null): void
    {
        $hora = $horaRapida ?: trim($this->nuevoHorarioExtra);
        if ($hora !== '' && ! in_array($hora, $this->horariosAdicionales, true)) {
            $this->horariosAdicionales[] = $hora;
            sort($this->horariosAdicionales);
        }
        $this->nuevoHorarioExtra = '';
    }

    public function aplicarHorarioRecomendado(string $hora): void
    {
        try {
            $fecha = ! empty($this->fecha_hora) ? Carbon::parse($this->fecha_hora)->format('Y-m-d') : today()->format('Y-m-d');
            $this->fecha_hora = Carbon::parse($fecha.' '.$hora)->format('Y-m-d\TH:i');
            $this->verificarConflictoEnVivo();
            Flux::toast(variant: 'success', text: "Horario {$hora} hrs asignado a la función.");
        } catch (\Exception $e) {
        }
    }

    public function calcularHorariosRecomendados(): array
    {
        if (! $this->modalAbierto || ! $this->sala_id || empty($this->fecha_hora)) {
            return [];
        }

        try {
            $fechaDia = Carbon::parse($this->fecha_hora)->format('Y-m-d');
            $pelicula = Pelicula::find($this->pelicula_id);
            $duracion = (int) ($pelicula?->duracion ?: 120);

            $funcionesOcupadas = Funcion::with('pelicula')
                ->where('sala_id', $this->sala_id)
                ->whereDate('fecha_hora', $fechaDia)
                ->when($this->funcionId, fn ($q) => $q->where('id', '!=', $this->funcionId))
                ->orderBy('fecha_hora', 'asc')
                ->get();

            $candidatos = [];

            // 1. Recomendaciones inmediatas tras concluir cada función y sus 15 min de aseo
            foreach ($funcionesOcupadas as $f) {
                if ($f->fecha_fin_limpieza) {
                    $cand = $f->fecha_fin_limpieza->copy();
                    // Redondear a múltiplo de 5 min hacia arriba
                    $min = (int) $cand->minute;
                    $resto = $min % 5;
                    if ($resto !== 0) {
                        $cand->addMinutes(5 - $resto);
                    }
                    if ($cand->format('Y-m-d') === $fechaDia && (int) $cand->format('H') <= 23) {
                        $candidatos[] = $cand->format('H:i');
                    }
                }
            }

            // 2. Horarios comerciales habituales de cine a lo largo de la tarde/noche
            $estandar = ['12:30', '13:45', '15:15', '16:45', '18:15', '19:45', '21:15', '22:30'];
            foreach ($estandar as $h) {
                if (! in_array($h, $candidatos, true)) {
                    $candidatos[] = $h;
                }
            }

            // Filtrar únicamente los horarios donde NO colisione con ninguna proyección ni su receso de limpieza
            $recomendados = [];
            foreach ($candidatos as $horaStr) {
                $dtPropuesto = Carbon::parse($fechaDia.' '.$horaStr);
                $colisiona = false;
                foreach ($funcionesOcupadas as $f) {
                    if ($f->colisionaCon($dtPropuesto, $duracion)) {
                        $colisiona = true;
                        break;
                    }
                }
                if (! $colisiona && ! in_array($horaStr, $recomendados, true)) {
                    $recomendados[] = $horaStr;
                }
            }

            sort($recomendados);

            return array_slice($recomendados, 0, 8);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Calcula sugerencias de horarios para añadir en el mismo día, basadas en la duración
     * de la película seleccionada y asegurando que no solapen con el horario principal,
     * funciones existentes o horarios adicionales ya elegidos.
     */
    public function calcularHorariosSugeridosParaMismoDia(): array
    {
        if (! $this->modalAbierto || ! $this->sala_id || empty($this->fecha_hora) || ! $this->pelicula_id) {
            return [];
        }

        try {
            $dtPrincipal = Carbon::parse($this->fecha_hora);
            $fechaDia = $dtPrincipal->format('Y-m-d');
            $horaPrincipal = $dtPrincipal->format('H:i');
            $pelicula = Pelicula::find($this->pelicula_id);
            $duracion = (int) ($pelicula?->duracion ?: 120);
            $duracionTotalBloque = $duracion + Funcion::recesoLimpiezaMinutos();

            $funcionesOcupadas = Funcion::with('pelicula')
                ->where('sala_id', $this->sala_id)
                ->whereDate('fecha_hora', $fechaDia)
                ->when($this->funcionId, fn ($q) => $q->where('id', '!=', $this->funcionId))
                ->orderBy('fecha_hora', 'asc')
                ->get();

            $candidatos = [];

            // 1. Proyección hacia adelante desde la función principal
            $forward = $dtPrincipal->copy();
            for ($k = 0; $k < 6; $k++) {
                $forward->addMinutes($duracionTotalBloque);
                $min = (int) $forward->minute;
                $resto = $min % 5;
                if ($resto !== 0) {
                    $forward->addMinutes(5 - $resto);
                }
                if ($forward->format('Y-m-d') === $fechaDia && (int) $forward->format('H') <= 23) {
                    $candidatos[] = $forward->format('H:i');
                } else {
                    break;
                }
            }

            // 2. Proyección hacia atrás desde la función principal
            $backward = $dtPrincipal->copy();
            for ($k = 0; $k < 6; $k++) {
                $backward->subMinutes($duracionTotalBloque);
                $min = (int) $backward->minute;
                $resto = $min % 5;
                if ($resto !== 0) {
                    $backward->subMinutes($resto);
                }
                if ($backward->format('Y-m-d') === $fechaDia && (int) $backward->format('H') >= 11) {
                    $candidatos[] = $backward->format('H:i');
                } else {
                    break;
                }
            }

            // 3. Recomendaciones tras culminar las funciones ocupadas existentes en la sala
            foreach ($funcionesOcupadas as $f) {
                if ($f->fecha_fin_limpieza) {
                    $cand = $f->fecha_fin_limpieza->copy();
                    $min = (int) $cand->minute;
                    $resto = $min % 5;
                    if ($resto !== 0) {
                        $cand->addMinutes(5 - $resto);
                    }
                    if ($cand->format('Y-m-d') === $fechaDia && (int) $cand->format('H') <= 23) {
                        $candidatos[] = $cand->format('H:i');
                    }
                }
            }

            // 4. Franjas comerciales habituales de cine
            $comerciales = ['12:30', '13:45', '14:30', '15:15', '16:00', '17:00', '18:15', '19:30', '20:15', '21:30', '22:15'];
            foreach ($comerciales as $com) {
                if (! in_array($com, $candidatos, true)) {
                    $candidatos[] = $com;
                }
            }

            // 5. Filtrar candidatos válidos
            $sugeridos = [];
            foreach ($candidatos as $hStr) {
                if ($hStr === $horaPrincipal || in_array($hStr, $this->horariosAdicionales, true)) {
                    continue;
                }

                $dtCandidato = Carbon::parse($fechaDia.' '.$hStr);
                $finPrincipal = $dtPrincipal->copy()->addMinutes($duracionTotalBloque);
                $finCandidato = $dtCandidato->copy()->addMinutes($duracionTotalBloque);

                if ($dtCandidato->lt($finPrincipal) && $finCandidato->gt($dtPrincipal)) {
                    continue;
                }

                $colisionaBD = false;
                foreach ($funcionesOcupadas as $f) {
                    if ($f->colisionaCon($dtCandidato, $duracion)) {
                        $colisionaBD = true;
                        break;
                    }
                }
                if ($colisionaBD) {
                    continue;
                }

                $colisionaExtras = false;
                foreach ($this->horariosAdicionales as $hExtra) {
                    $dtExtra = Carbon::parse($fechaDia.' '.$hExtra);
                    $finExtra = $dtExtra->copy()->addMinutes($duracionTotalBloque);
                    if ($dtCandidato->lt($finExtra) && $finCandidato->gt($dtExtra)) {
                        $colisionaExtras = true;
                        break;
                    }
                }
                if ($colisionaExtras) {
                    continue;
                }

                if (! in_array($hStr, $sugeridos, true)) {
                    $sugeridos[] = $hStr;
                }
            }

            sort($sugeridos);

            return array_slice($sugeridos, 0, 10);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function quitarHorarioExtra(int $index): void
    {
        unset($this->horariosAdicionales[$index]);
        $this->horariosAdicionales = array_values($this->horariosAdicionales);
    }

    public function crear(?int $salaPreseleccionada = null, ?int $peliculaPreseleccionada = null): void
    {
        $this->guardando = false;
        $this->resetErrorBag();
        $this->resetValidation();
        $this->errorConflicto = null;
        $this->reset(['funcionId', 'pelicula_id', 'sala_id', 'fecha_hora', 'horariosAdicionales', 'nuevoHorarioExtra']);

        $p = $peliculaPreseleccionada
            ? Pelicula::find($peliculaPreseleccionada)
            : ($this->filtroPelicula ? Pelicula::find($this->filtroPelicula) : Pelicula::first());

        $s = $salaPreseleccionada
            ? Sala::find($salaPreseleccionada)
            : ($this->filtroSala ? Sala::find($this->filtroSala) : Sala::first());

        $this->pelicula_id = $p ? $p->id : null;
        $this->sala_id = $s ? $s->id : null;
        $this->plantilla_funcion = 'heredar';

        $fechaBase = $this->filtroFecha !== ''
            ? Carbon::parse($this->filtroFecha)->setTime(17, 45)
            : now()->addHours(2)->minute(30);

        $this->fecha_hora = $fechaBase->format('Y-m-d\TH:i');
        $this->precio = 25.00;
        $this->precio_puntos = 250;
        $this->modalAbierto = true;

        $this->verificarConflictoEnVivo();
    }

    public function editar(int $id): void
    {
        $f = Funcion::findOrFail($id);
        $this->funcionId = $f->id;
        $this->pelicula_id = $f->pelicula_id;
        $this->sala_id = $f->sala_id;
        $this->fecha_hora = $f->fecha_hora ? $f->fecha_hora->format('Y-m-d\TH:i') : '';
        $this->precio = (float) $f->precio;
        $this->precio_puntos = (int) $f->precio_puntos;
        $this->plantilla_funcion = $f->plantilla ?: 'heredar';
        $this->horariosAdicionales = [];
        $this->nuevoHorarioExtra = '';
        $this->errorConflicto = null;
        $this->modalAbierto = true;

        $this->verificarConflictoEnVivo();
    }

    public function guardar(): void
    {
        if ($this->guardando) {
            return;
        }

        $this->guardando = true;

        try {
            $validated = $this->validate();

            $sala = Sala::with('butacas')->findOrFail($validated['sala_id']);
            $pelicula = Pelicula::findOrFail($validated['pelicula_id']);
            $duracionPelicula = (int) ($pelicula->duracion ?: 120);

            [$filasCount, $colCount] = $this->dimensionesDeSala($sala);

            $plantillaElegida = $this->plantilla_funcion !== 'heredar' ? $this->plantilla_funcion : null;
            $mapaConfigFuncion = $plantillaElegida
                ? Sala::generarConfiguracionPlantilla($plantillaElegida, $filasCount, $colCount)
                : null;

            if ($this->funcionId) {
                $dtPrincipal = Carbon::parse($validated['fecha_hora']);

                // Validar que no haya colisión con otra función en la misma sala (duración + 15 min limpieza)
                $conflicto = Funcion::with('pelicula')
                    ->where('sala_id', $validated['sala_id'])
                    ->whereDate('fecha_hora', $dtPrincipal->format('Y-m-d'))
                    ->where('id', '!=', $this->funcionId)
                    ->get()
                    ->first(fn ($item) => $item->colisionaCon($dtPrincipal, $duracionPelicula));

                if ($conflicto) {
                    $msgConflicto = sprintf(
                        'No se puede programar a las %s hrs: La sala ya está ocupada por «%s» de %s a %s (incluye 15 min de limpieza).',
                        $dtPrincipal->format('H:i'),
                        $conflicto->pelicula->titulo,
                        $conflicto->fecha_hora->format('H:i'),
                        $conflicto->fecha_fin_limpieza->format('H:i')
                    );
                    $this->errorConflicto = $msgConflicto;
                    $this->addError('fecha_hora', $msgConflicto);
                    Flux::toast(variant: 'danger', text: $msgConflicto);
                    $this->modalAbierto = true;

                    return;
                }

                $f = Funcion::findOrFail($this->funcionId);
                $cambioSala = (int) $f->sala_id !== (int) $sala->id;

                $datosUpdate = [
                    'pelicula_id' => $validated['pelicula_id'],
                    'sala_id' => $validated['sala_id'],
                    'fecha_hora' => $validated['fecha_hora'],
                    'precio' => $validated['precio'],
                    'precio_puntos' => $validated['precio_puntos'],
                ];

                if ($this->plantilla_funcion === 'heredar') {
                    $datosUpdate['plantilla'] = null;
                    $datosUpdate['mapa_config'] = null;
                } elseif ($this->plantilla_funcion !== $f->plantilla) {
                    $datosUpdate['plantilla'] = $plantillaElegida;
                    $datosUpdate['mapa_config'] = $mapaConfigFuncion;
                }

                $f->update($datosUpdate);

                if ($cambioSala) {
                    $this->sincronizarButacasDeFuncion($f, $sala, true);
                    Flux::toast(variant: 'success', text: 'Función actualizada y butacas reasignadas a la nueva sala.');
                } else {
                    $this->recalcularAsientosDisponibles($f->fresh(['sala', 'butacas']));
                    Flux::toast(variant: 'success', text: 'Función actualizada correctamente.');
                }
            } else {
                $fechasHorasCrear = [Carbon::parse($validated['fecha_hora'])];

                $fechaDia = Carbon::parse($validated['fecha_hora'])->format('Y-m-d');
                foreach ($this->horariosAdicionales as $horaExtra) {
                    $candidato = Carbon::parse($fechaDia.' '.$horaExtra);
                    if (! collect($fechasHorasCrear)->contains(fn ($dt) => $dt->format('Y-m-d H:i') === $candidato->format('Y-m-d H:i'))) {
                        $fechasHorasCrear[] = $candidato;
                    }
                }

                // 1. Validar solapamientos internos entre los horarios adicionales seleccionados
                for ($i = 0; $i < count($fechasHorasCrear); $i++) {
                    for ($j = $i + 1; $j < count($fechasHorasCrear); $j++) {
                        $dt1 = $fechasHorasCrear[$i];
                        $dt2 = $fechasHorasCrear[$j];
                        $fin1 = $dt1->copy()->addMinutes($duracionPelicula + Funcion::recesoLimpiezaMinutos());
                        $fin2 = $dt2->copy()->addMinutes($duracionPelicula + Funcion::recesoLimpiezaMinutos());
                        if ($dt1->lt($fin2) && $fin1->gt($dt2)) {
                            $msg = sprintf(
                                'Conflicto interno entre horarios seleccionados (%s y %s). Cada función requiere %s de proyección + 0:15 hrs de limpieza.',
                                $dt1->format('H:i'),
                                $dt2->format('H:i'),
                                Funcion::minutosAHorasFormato($duracionPelicula)
                            );
                            $this->errorConflicto = $msg;
                            $this->addError('fecha_hora', $msg);
                            Flux::toast(variant: 'danger', text: $msg);
                            $this->modalAbierto = true;

                            return;
                        }
                    }
                }

                // 2. Validar que ninguno de los horarios colisione con funciones ya existentes en BD
                $funcionesDelDia = Funcion::with('pelicula')
                    ->where('sala_id', $validated['sala_id'])
                    ->whereDate('fecha_hora', $fechaDia)
                    ->get();

                foreach ($fechasHorasCrear as $dt) {
                    $conflicto = $funcionesDelDia->first(fn ($item) => $item->colisionaCon($dt, $duracionPelicula));
                    if ($conflicto) {
                        $msg = sprintf(
                            'No se puede programar a las %s hrs: La sala ya está ocupada por «%s» de %s a %s (incluye 15 min de limpieza).',
                            $dt->format('H:i'),
                            $conflicto->pelicula->titulo,
                            $conflicto->fecha_hora->format('H:i'),
                            $conflicto->fecha_fin_limpieza->format('H:i')
                        );
                        $this->errorConflicto = $msg;
                        $this->addError('fecha_hora', $msg);
                        Flux::toast(variant: 'danger', text: $msg);
                        $this->modalAbierto = true;

                        return;
                    }
                }

                $creadasExitosas = 0;

                foreach ($fechasHorasCrear as $dt) {
                    DB::transaction(function () use ($validated, $dt, $sala, $plantillaElegida, $mapaConfigFuncion) {
                        $funcion = Funcion::create([
                            'pelicula_id' => $validated['pelicula_id'],
                            'sala_id' => $validated['sala_id'],
                            'fecha_hora' => $dt,
                            'precio' => $validated['precio'],
                            'precio_puntos' => $validated['precio_puntos'],
                            'asientos_disponibles' => $sala->capacidad,
                            'plantilla' => $plantillaElegida,
                            'mapa_config' => $mapaConfigFuncion,
                        ]);

                        $this->sincronizarButacasDeFuncion($funcion, $sala, true);
                    });

                    $creadasExitosas++;
                }

                if ($creadasExitosas > 0) {
                    Flux::toast(
                        variant: 'success',
                        text: $creadasExitosas > 1
                            ? "Se programaron {$creadasExitosas} funciones respetando el receso de limpieza."
                            : 'Función programada correctamente (con 15 min de limpieza).'
                    );
                }
            }

            $this->modalAbierto = false;
        } finally {
            $this->guardando = false;
        }
    }

    public function eliminar(int $id): void
    {
        $f = Funcion::findOrFail($id);
        $f->butacas()->detach();
        $f->delete();

        if ($this->funcionButacasId === $id) {
            $this->modalButacas = false;
            $this->funcionButacasId = null;
        }

        Flux::toast(variant: 'success', text: 'Función eliminada.');
    }

    // =========================================================================
    // GESTIÓN VISUAL DEL MAPA DE BUTACAS Y PLANTILLAS DE ESCALERAS
    // =========================================================================

    public function abrirMapaButacas(int $funcionId): void
    {
        $funcion = Funcion::with(['sala.butacas', 'butacas'])->findOrFail($funcionId);

        if ($funcion->butacas->count() !== $funcion->sala->butacas->count()) {
            $this->sincronizarButacasDeFuncion($funcion, $funcion->sala, false);
        }

        $this->funcionButacasId = $funcionId;
        $this->modoClicMapa = 'ocupacion';
        $this->modalButacas = true;
    }

    /**
     * Aplica una plantilla predefinida (escalera en 2da columna, doble escalera, extremos en 2 filas, etc.).
     */
    public function seleccionarPlantillaMapa(string $codigoPlantilla): void
    {
        if (! $this->funcionButacasId) {
            return;
        }

        $funcion = Funcion::with(['sala.butacas', 'butacas'])->findOrFail($this->funcionButacasId);
        [$filas, $columnas] = $this->dimensionesDeSala($funcion->sala);

        if ($codigoPlantilla === 'personalizada') {
            $cfgActual = $funcion->obtenerMapaConfig();
            $cfgActual['plantilla'] = 'personalizada';
            $this->modoClicMapa = 'escaleras';
            $this->guardarConfiguracionMapaEnFuncionYSala($funcion, $cfgActual);
            Flux::toast(variant: 'success', text: 'Modo Personalizado activado: haz clic en columnas, filas o butacas para colocar escaleras.');

            return;
        }

        $nuevaConfig = Sala::generarConfiguracionPlantilla($codigoPlantilla, $filas, $columnas);
        $this->guardarConfiguracionMapaEnFuncionYSala($funcion, $nuevaConfig);

        $catalogo = Sala::catalogoPlantillas();
        $nombrePlantilla = $catalogo[$codigoPlantilla]['nombre'] ?? $codigoPlantilla;

        Flux::toast(
            variant: 'success',
            text: "Plantilla «{$nombrePlantilla}» aplicada correctamente."
        );
    }

    /**
     * Activa o desactiva una escalera vertical después de la columna indicada (ej. después de columna 2).
     */
    public function toggleEscaleraColumna(int $columna): void
    {
        if (! $this->funcionButacasId) {
            return;
        }

        $funcion = Funcion::with(['sala.butacas', 'butacas'])->findOrFail($this->funcionButacasId);
        $cfg = $funcion->obtenerMapaConfig();
        $actuales = $cfg['escaleras_despues_columna'] ?? [];

        if (in_array($columna, $actuales, true)) {
            $actuales = array_values(array_diff($actuales, [$columna]));
        } else {
            $actuales[] = $columna;
            sort($actuales);
        }

        $cfg['plantilla'] = 'personalizada';
        $cfg['escaleras_despues_columna'] = $actuales;

        $this->guardarConfiguracionMapaEnFuncionYSala($funcion, $cfg);
    }

    /**
     * Activa o desactiva un pasillo horizontal después de la fila indicada (ej. después de fila B).
     */
    public function togglePasilloFila(string $filaLetra): void
    {
        if (! $this->funcionButacasId) {
            return;
        }

        $funcion = Funcion::with(['sala.butacas', 'butacas'])->findOrFail($this->funcionButacasId);
        $cfg = $funcion->obtenerMapaConfig();
        $actuales = $cfg['pasillos_despues_fila'] ?? [];

        if (in_array($filaLetra, $actuales, true)) {
            $actuales = array_values(array_diff($actuales, [$filaLetra]));
        } else {
            $actuales[] = $filaLetra;
            sort($actuales);
        }

        $cfg['plantilla'] = 'personalizada';
        $cfg['pasillos_despues_fila'] = $actuales;

        $this->guardarConfiguracionMapaEnFuncionYSala($funcion, $cfg);
    }

    /**
     * Maneja el clic sobre una butaca en el modal según el modo activo:
     * - 'ocupacion': alterna entre Disponible y Ocupada
     * - 'escaleras': convierte esa celda en Escalera (🪜) o la restaura a Butaca (💺)
     */
    public function clicEnCeldaButaca(int $butacaId): void
    {
        if (! $this->funcionButacasId) {
            return;
        }

        $funcion = Funcion::with(['sala.butacas', 'butacas'])->findOrFail($this->funcionButacasId);
        $butacaActual = $funcion->butacas->firstWhere('id', $butacaId);

        if (! $butacaActual) {
            return;
        }

        if ($this->modoClicMapa === 'escaleras') {
            $cfg = $funcion->obtenerMapaConfig();
            $celdasEscalera = $cfg['celdas_escalera'] ?? [];
            $codigo = $butacaActual->numero_butaca; // ej. "A-1"

            if (in_array($codigo, $celdasEscalera, true)) {
                $celdasEscalera = array_values(array_diff($celdasEscalera, [$codigo]));
            } else {
                $celdasEscalera[] = $codigo;
            }

            $cfg['plantilla'] = 'personalizada';
            $cfg['celdas_escalera'] = $celdasEscalera;

            $this->guardarConfiguracionMapaEnFuncionYSala($funcion, $cfg);

            return;
        }

        // Modo ocupación normal: si es una celda de escalera no se ocupa como asiento normal
        $cfg = $funcion->obtenerMapaConfig();
        if (in_array($butacaActual->numero_butaca, $cfg['celdas_escalera'] ?? [], true)) {
            Flux::toast(variant: 'warning', text: 'Esta posición es una escalera. Cambia al modo «Editar Escaleras» si deseas convertirla en butaca.');

            return;
        }

        $nuevoEstado = ! (bool) $butacaActual->pivot->disponible;

        $funcion->butacas()->updateExistingPivot($butacaId, [
            'disponible' => $nuevoEstado,
            'updated_at' => now(),
        ]);

        $this->recalcularAsientosDisponibles($funcion);
    }

    public function alternarFilaButacas(string $fila): void
    {
        if (! $this->funcionButacasId) {
            return;
        }

        $funcion = Funcion::with(['sala', 'butacas'])->findOrFail($this->funcionButacasId);
        $cfg = $funcion->obtenerMapaConfig();
        $celdasEscalera = $cfg['celdas_escalera'] ?? [];

        $butacasFila = $funcion->butacas->filter(function ($b) use ($fila, $celdasEscalera) {
            return (explode('-', $b->numero_butaca)[0] ?? '') === $fila
                && ! in_array($b->numero_butaca, $celdasEscalera, true);
        });

        if ($butacasFila->isEmpty()) {
            return;
        }

        $todasDisponibles = $butacasFila->every(fn ($b) => (bool) $b->pivot->disponible);
        $nuevoEstado = ! $todasDisponibles;

        DB::table('funcion_butaca')
            ->where('funcion_id', $funcion->id)
            ->whereIn('butaca_id', $butacasFila->pluck('id'))
            ->update([
                'disponible' => $nuevoEstado,
                'updated_at' => now(),
            ]);

        $this->recalcularAsientosDisponibles($funcion);
        Flux::toast(
            variant: 'success',
            text: $nuevoEstado ? "Fila {$fila} habilitada." : "Fila {$fila} marcada como ocupada/bloqueada."
        );
    }

    public function liberarTodasButacas(): void
    {
        if (! $this->funcionButacasId) {
            return;
        }

        $funcion = Funcion::with(['sala', 'butacas'])->findOrFail($this->funcionButacasId);

        DB::table('funcion_butaca')
            ->where('funcion_id', $funcion->id)
            ->update([
                'disponible' => true,
                'updated_at' => now(),
            ]);

        $this->recalcularAsientosDisponibles($funcion);
        Flux::toast(variant: 'success', text: 'Todas las butacas activas de esta función fueron liberadas.');
    }

    public function ocuparTodasButacas(): void
    {
        if (! $this->funcionButacasId) {
            return;
        }

        $funcion = Funcion::with(['sala', 'butacas'])->findOrFail($this->funcionButacasId);

        DB::table('funcion_butaca')
            ->where('funcion_id', $funcion->id)
            ->update([
                'disponible' => false,
                'updated_at' => now(),
            ]);

        $this->recalcularAsientosDisponibles($funcion);
        Flux::toast(variant: 'success', text: 'Todas las butacas fueron marcadas como ocupadas/bloqueadas.');
    }

    // =========================================================================
    // EDICIÓN RÁPIDA DE SALA, DIMENSIONES Y PLANTILLA PREDETERMINADA
    // =========================================================================

    public function abrirEditarSala(int $salaId): void
    {
        $sala = Sala::with('butacas')->findOrFail($salaId);
        $this->editSalaId = $sala->id;
        $this->sala_numero = $sala->numero_sala;
        $this->sala_sede = $sala->sede ?: 'Cine+ Alcázar (Miraflores)';
        $this->sala_plantilla = $sala->plantilla ?: 'clasica';

        [$this->sala_filas, $this->sala_asientos_por_fila] = $this->dimensionesDeSala($sala);

        $this->modalSala = true;
    }

    public function guardarSala(): void
    {
        $this->validate([
            'sala_numero' => 'required|string|max:100',
            'sala_sede' => 'required|string|max:150',
            'sala_filas' => 'required|integer|min:1|max:26',
            'sala_asientos_por_fila' => 'required|integer|min:1|max:30',
            'sala_plantilla' => 'required|string|max:50',
        ]);

        $filasNum = (int) $this->sala_filas;
        $colsNum = (int) $this->sala_asientos_por_fila;
        $totalButacas = $filasNum * $colsNum;

        if ($totalButacas < 30) {
            $this->addError('sala_filas', "La sala debe tener un mínimo de 30 butacas totales (actualmente {$totalButacas}).");
            Flux::toast(variant: 'danger', text: "Límite insuficiente: Se requieren mínimo 30 butacas (actual: {$totalButacas}).");

            return;
        }

        if ($totalButacas > 300) {
            $this->addError('sala_filas', "La sala no puede exceder las 300 butacas totales (actualmente {$totalButacas}).");
            Flux::toast(variant: 'danger', text: "Límite excedido: Máximo 300 butacas (actual: {$totalButacas}).");

            return;
        }

        $duplicado = Sala::where('sede', $this->sala_sede)
            ->where('numero_sala', $this->sala_numero)
            ->where('id', '!=', $this->editSalaId)
            ->exists();

        if ($duplicado) {
            $this->addError('sala_numero', "Ya existe otra sala con el nombre '{$this->sala_numero}' en la sede '{$this->sala_sede}'.");
            Flux::toast(variant: 'danger', text: "Ya existe una sala con este nombre en '{$this->sala_sede}'.");

            return;
        }

        $sala = Sala::with(['butacas', 'funciones'])->findOrFail($this->editSalaId);

        $letras = range('A', 'Z');
        $codigosEsperados = [];
        for ($f = 0; $f < $this->sala_filas; $f++) {
            $filaLetra = $letras[$f];
            for ($num = 1; $num <= $this->sala_asientos_por_fila; $num++) {
                $codigosEsperados[] = "{$filaLetra}-{$num}";
            }
        }

        $codigosActuales = $sala->butacas->pluck('numero_butaca')->toArray();
        $cambioMatriz = count($codigosEsperados) !== count($codigosActuales)
            || ! empty(array_diff($codigosEsperados, $codigosActuales));

        $mapaConfigSala = $this->sala_plantilla === 'personalizada' && is_array($sala->mapa_config)
            ? $sala->mapa_config
            : Sala::generarConfiguracionPlantilla($this->sala_plantilla, $this->sala_filas, $this->sala_asientos_por_fila);

        $cantEscalerasCeldas = count($mapaConfigSala['celdas_escalera'] ?? []);
        $nuevaCapacidad = max(1, ($this->sala_filas * $this->sala_asientos_por_fila) - $cantEscalerasCeldas);

        $sala->update([
            'numero_sala' => $this->sala_numero,
            'sede' => $this->sala_sede,
            'capacidad' => $nuevaCapacidad,
            'plantilla' => $this->sala_plantilla,
            'mapa_config' => $mapaConfigSala,
        ]);

        if ($cambioMatriz) {
            $sala->butacas()->whereNotIn('numero_butaca', $codigosEsperados)->delete();

            $existentes = $sala->butacas()->pluck('numero_butaca')->toArray();
            $nuevasButacas = [];
            foreach ($codigosEsperados as $codigo) {
                if (! in_array($codigo, $existentes, true)) {
                    $nuevasButacas[] = [
                        'numero_butaca' => $codigo,
                        'sala_id' => $sala->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (! empty($nuevasButacas)) {
                Butaca::insert($nuevasButacas);
            }
        }

        $sala->load(['butacas', 'funciones']);

        foreach ($sala->funciones as $funcion) {
            $this->sincronizarButacasDeFuncion($funcion, $sala, false);
        }

        $this->modalSala = false;
        Flux::toast(variant: 'success', text: "Sala «{$sala->numero_sala}» y su plantilla actualizadas ({$nuevaCapacidad} butacas útiles).");
    }

    /**
     * Guarda la configuración de escaleras/plantilla en la función actual y, si está marcado,
     * también como plantilla predeterminada de toda la sala.
     */
    private function guardarConfiguracionMapaEnFuncionYSala(Funcion $funcion, array $nuevaConfig): void
    {
        $plantillaCodigo = $nuevaConfig['plantilla'] ?? 'personalizada';

        $funcion->update([
            'plantilla' => $plantillaCodigo,
            'mapa_config' => $nuevaConfig,
        ]);

        if ($this->aplicarPlantillaASala && $funcion->sala) {
            $totalMatriz = $funcion->sala->butacas()->count();
            $celdasEscaleraCount = count($nuevaConfig['celdas_escalera'] ?? []);
            $capacidadEfectiva = max(1, $totalMatriz - $celdasEscaleraCount);

            $funcion->sala->update([
                'plantilla' => $plantillaCodigo,
                'mapa_config' => $nuevaConfig,
                'capacidad' => $capacidadEfectiva,
            ]);

            // Actualizar las funciones de esa sala que no tengan una plantilla distinta propia
            Funcion::where('sala_id', $funcion->sala_id)
                ->where('id', '!=', $funcion->id)
                ->whereNull('mapa_config')
                ->get()
                ->each(fn (Funcion $fHermana) => $this->recalcularAsientosDisponibles($fHermana));
        }

        $this->recalcularAsientosDisponibles($funcion->fresh(['sala', 'butacas']));
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function dimensionesDeSala(Sala $sala): array
    {
        $porFila = $sala->butacas->groupBy(fn ($b) => explode('-', $b->numero_butaca)[0] ?? 'A');
        $filas = max(1, $porFila->count() ?: 5);
        $columnas = max(1, $porFila->first()?->count() ?: 10);

        return [$filas, $columnas];
    }

    private function sincronizarButacasDeFuncion(Funcion $funcion, Sala $sala, bool $reiniciarDisponibilidad = false): void
    {
        $butacasSalaIds = $sala->butacas()->pluck('id')->toArray();

        if ($reiniciarDisponibilidad) {
            $funcion->butacas()->detach();
            $pivoteRows = [];
            foreach ($butacasSalaIds as $bId) {
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
            $this->recalcularAsientosDisponibles($funcion->fresh(['sala', 'butacas']));

            return;
        }

        $existentesIds = DB::table('funcion_butaca')
            ->where('funcion_id', $funcion->id)
            ->pluck('butaca_id')
            ->toArray();

        DB::table('funcion_butaca')
            ->where('funcion_id', $funcion->id)
            ->whereNotIn('butaca_id', $butacasSalaIds)
            ->delete();

        $faltantes = array_diff($butacasSalaIds, $existentesIds);
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

        $this->recalcularAsientosDisponibles($funcion->fresh(['sala', 'butacas']));
    }

    private function recalcularAsientosDisponibles(Funcion $funcion): void
    {
        $funcion->loadMissing(['sala', 'butacas']);
        $cfg = $funcion->obtenerMapaConfig();
        $celdasEscalera = $cfg['celdas_escalera'] ?? [];

        $disponibles = $funcion->butacas->filter(function ($butaca) use ($celdasEscalera) {
            return ! in_array($butaca->numero_butaca, $celdasEscalera, true)
                && (bool) $butaca->pivot->disponible;
        })->count();

        $funcion->update(['asientos_disponibles' => $disponibles]);
    }

    public function render()
    {
        $sedes = Sede::activas()->orderBy('nombre')->pluck('nombre');

        if ($sedes->isEmpty()) {
            $sedes = Sala::query()
                ->select('sede')
                ->distinct()
                ->whereNotNull('sede')
                ->where('sede', '!=', '')
                ->orderBy('sede')
                ->pluck('sede');
        }

        $salasQuery = Sala::withCount('butacas')->orderBy('sede')->orderBy('numero_sala');
        if ($this->filtroSede !== '') {
            $salasQuery->where('sede', $this->filtroSede);
        }
        $salasFiltradas = $salasQuery->get();
        $todasLasSalas = Sala::orderBy('sede')->orderBy('numero_sala')->get();
        $peliculas = Pelicula::orderBy('titulo')->get();
        $catalogoPlantillas = Sala::catalogoPlantillas();

        $fechasRapidas = collect(range(0, 6))->map(function (int $offset) {
            $fecha = Carbon::today()->addDays($offset);

            return [
                'valor' => $fecha->format('Y-m-d'),
                'etiqueta' => match ($offset) {
                    0 => 'Hoy',
                    1 => 'Mañana',
                    default => ucfirst($fecha->locale('es')->isoFormat('ddd D MMM')),
                },
                'subtitulo' => $fecha->format('d/m'),
            ];
        });

        $query = Funcion::with(['pelicula', 'sala']);

        if ($this->filtroSede !== '') {
            $query->whereHas('sala', fn ($q) => $q->where('sede', $this->filtroSede));
        }

        if ($this->filtroSala !== '') {
            $query->where('sala_id', $this->filtroSala);
        }

        if ($this->filtroPelicula !== '') {
            $query->where('pelicula_id', $this->filtroPelicula);
        }

        if ($this->filtroFecha !== '') {
            $query->whereDate('fecha_hora', $this->filtroFecha);
        }

        $funcionesColeccion = (clone $query)->orderBy('fecha_hora', 'asc')->get();

        $salasParaCines = $salasFiltradas;
        if ($this->filtroSala !== '') {
            $salasParaCines = $salasParaCines->where('id', (int) $this->filtroSala);
        }

        $funcionesPorSala = $funcionesColeccion->groupBy('sala_id');
        $cinesAgrupados = $salasParaCines->groupBy(fn ($s) => $s->sede ?: 'Sede Principal');
        $funcionesPorPelicula = $funcionesColeccion->groupBy('pelicula_id');

        $funcionActivaButacas = null;
        $butacasPorFilaModal = collect();
        $mapaConfigModal = [
            'plantilla' => 'clasica',
            'escaleras_despues_columna' => [],
            'pasillos_despues_fila' => [],
            'celdas_escalera' => [],
        ];

        if ($this->modalButacas && $this->funcionButacasId) {
            $funcionActivaButacas = Funcion::with(['pelicula', 'sala', 'butacas'])->find($this->funcionButacasId);
            if ($funcionActivaButacas) {
                $mapaConfigModal = $funcionActivaButacas->obtenerMapaConfig();
                $butacasPorFilaModal = $funcionActivaButacas->butacas
                    ->sortBy(function ($b) {
                        $partes = explode('-', $b->numero_butaca);
                        $fila = $partes[0] ?? 'A';
                        $num = (int) ($partes[1] ?? 1);

                        return sprintf('%s-%03d', $fila, $num);
                    })
                    ->groupBy(fn ($b) => explode('-', $b->numero_butaca)[0] ?? 'A');
            }
        }

        // Horarios ya creados/ocupados en la sala seleccionada para la fecha actual del modal
        $horariosOcupadosModal = collect();
        $horariosRecomendadosModal = [];
        $horariosSugeridosMismoDia = [];

        if ($this->modalAbierto && $this->sala_id && $this->fecha_hora !== '') {
            try {
                $fechaDiaModal = Carbon::parse($this->fecha_hora)->format('Y-m-d');
                $horariosOcupadosModal = Funcion::with('pelicula')
                    ->where('sala_id', $this->sala_id)
                    ->whereDate('fecha_hora', $fechaDiaModal)
                    ->when($this->funcionId, fn ($q) => $q->where('id', '!=', $this->funcionId))
                    ->orderBy('fecha_hora', 'asc')
                    ->get();

                $horariosRecomendadosModal = $this->calcularHorariosRecomendados();
                $horariosSugeridosMismoDia = $this->calcularHorariosSugeridosParaMismoDia();
            } catch (\Exception $e) {
                // Formato de fecha en edición
            }
        }

        return view('livewire.admin.funciones', [
            'funcionesPaginadas' => (clone $query)->orderBy('fecha_hora', 'asc')->paginate(15),
            'totalFuncionesFiltradas' => $funcionesColeccion->count(),
            'cinesAgrupados' => $cinesAgrupados,
            'funcionesPorSala' => $funcionesPorSala,
            'funcionesPorPelicula' => $funcionesPorPelicula,
            'sedes' => $sedes,
            'salas' => $salasFiltradas,
            'todasLasSalas' => $todasLasSalas,
            'peliculas' => $peliculas,
            'fechasRapidas' => $fechasRapidas,
            'catalogoPlantillas' => $catalogoPlantillas,
            'funcionActivaButacas' => $funcionActivaButacas,
            'butacasPorFilaModal' => $butacasPorFilaModal,
            'mapaConfigModal' => $mapaConfigModal,
            'horariosOcupadosModal' => $horariosOcupadosModal,
            'horariosRecomendadosModal' => $horariosRecomendadosModal,
            'horariosSugeridosMismoDia' => $horariosSugeridosMismoDia,
        ]);
    }
}
