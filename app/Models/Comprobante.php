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

            return $writer->writeString($contenido);
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
}

