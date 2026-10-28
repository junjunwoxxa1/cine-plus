<?php

namespace App\Models;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $codigo_reserva
 * @property int|null $user_id
 * @property string $cliente_nombre
 * @property string|null $cliente_email
 * @property string $sede
 * @property string|null $sala
 * @property string|null $pelicula_titulo
 * @property string|null $pelicula_imagen
 * @property Carbon|null $fecha_funcion
 * @property array|null $asientos
 * @property int $cantidad_entradas
 * @property float $monto_entradas
 * @property float $monto_dulceria
 * @property float $monto_descuento
 * @property string|null $descuento_concepto
 * @property string $tipo
 * @property string $serie
 * @property string $numero
 * @property float $subtotal
 * @property float $igv
 * @property float $total
 * @property string|null $metodo_pago_nombre
 * @property string $estado
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Comprobante extends Model
{
    use HasFactory;

    protected $table = 'comprobante';

    protected $fillable = [
        'codigo_reserva',
        'user_id',
        'cliente_nombre',
        'cliente_email',
        'sede',
        'sala',
        'pelicula_titulo',
        'pelicula_imagen',
        'fecha_funcion',
        'asientos',
        'cantidad_entradas',
        'monto_entradas',
        'monto_dulceria',
        'monto_descuento',
        'descuento_concepto',
        'tipo',
        'serie',
        'numero',
        'subtotal',
        'igv',
        'total',
        'metodo_pago_nombre',
        'estado',
    ];

    protected $casts = [
        'fecha_funcion' => 'datetime',
        'asientos' => 'array',
        'cantidad_entradas' => 'integer',
        'monto_entradas' => 'decimal:2',
        'monto_dulceria' => 'decimal:2',
        'monto_descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ComprobanteDetalle::class, 'comprobante_id');
    }

    /**
     * Genera el código QR vectorial en SVG para escanear en la puerta de la sala.
     */
    public function getQrSvgAttribute(): string
    {
        try {
            $contenido = sprintf(
                "CINE+ TICKET | Reserva: %s | Comprobante: %s-%s | Película: %s | Sala: %s | Asientos: %s | Total: S/ %.2f",
                $this->codigo_reserva,
                $this->serie,
                $this->numero,
                $this->pelicula_titulo,
                $this->sala,
                $this->asientos_texto,
                (float) $this->total
            );

            $renderer = new ImageRenderer(
                new RendererStyle(200, 1),
                new SvgImageBackEnd()
            );

            $writer = new Writer($renderer);
            $svg = $writer->writeString($contenido);

            // Asegurar que el SVG sea fluido y encaje perfectamente en el contenedor blanco
            $svg = str_replace('<svg ', '<svg class="w-full h-full max-w-full max-h-full block" preserveAspectRatio="xMidYMid meet" ', $svg);
            $svg = preg_replace('/width="[^"]*"/', 'width="100%"', $svg, 1);
            $svg = preg_replace('/height="[^"]*"/', 'height="100%"', $svg, 1);

            return $svg;
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function getAsientosTextoAttribute(): string
    {
        if (is_array($this->asientos)) {
            return implode(', ', $this->asientos);
        }

        return (string) ($this->asientos ?? '-');
    }

    public function getSerieNumeroAttribute(): string
    {
        return sprintf('%s-%s', $this->serie, $this->numero);
    }

    /**
     * Determina si la compra puede ser cancelada automáticamente por el cliente.
     * Regla: Debe ser con al menos 12 horas de anticipación al inicio de la función.
     * Si la compra es para una función que se estrena en menos de 12 horas, no es cancelable.
     */
    public function puedeCancelar(): bool
    {
        if ($this->estado !== 'pagado') {
            return false;
        }

        if (! $this->fecha_funcion) {
            // Compras sin función (ej. dulcería directa): permitidas dentro de las primeras 12 horas de creada
            return $this->created_at && $this->created_at->diffInHours(now(), false) <= 12;
        }

        // Requiere al menos 12 horas antes del inicio de la función
        return now()->diffInMinutes($this->fecha_funcion, false) >= (12 * 60);
    }

    /**
     * Calcula las horas restantes hasta el inicio de la función.
     */
    public function horasHastaFuncion(): ?float
    {
        if (! $this->fecha_funcion) {
            return null;
        }

        return round(now()->diffInMinutes($this->fecha_funcion, false) / 60, 1);
    }

    /**
     * Retorna el motivo por el cual la compra no puede ser cancelada.
     */
    public function motivoNoCancelable(): ?string
    {
        if ($this->estado === 'anulado') {
            return 'Esta compra ya se encuentra cancelada / anulada.';
        }

        if ($this->estado !== 'pagado') {
            return 'La compra no se encuentra en estado pagado.';
        }

        if ($this->fecha_funcion) {
            $horasRestantes = now()->diffInMinutes($this->fecha_funcion, false) / 60;

            if ($horasRestantes <= 0) {
                return 'La función ya se llevó a cabo o se encuentra en curso.';
            }

            if ($horasRestantes < 12) {
                return sprintf(
                    'No se puede cancelar esta compra. Faltan %.1f hrs para la función. La política exige un mínimo de 12 horas de anticipación (funciones próximas o estreno inmediato no aplican para cancelación).',
                    max(0, $horasRestantes)
                );
            }
        }

        return null;
    }
}

