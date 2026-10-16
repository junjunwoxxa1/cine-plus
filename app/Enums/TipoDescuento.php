<?php

namespace App\Enums;

enum TipoDescuento: string
{
    case Porcentaje = 'porcentaje';
    case MontoPorEntrada = 'monto_por_entrada';
    case MontoTotal = 'monto_total';

    public function label(): string
    {
        return match ($this) {
            self::Porcentaje => 'Porcentaje sobre entradas',
            self::MontoPorEntrada => 'Monto fijo por entrada',
            self::MontoTotal => 'Monto fijo total',
        };
    }

    /**
     * Calcula el descuento aplicable sobre el subtotal de entradas (nunca mayor al subtotal).
     */
    public function calcular(float $valor, float $subtotalEntradas, int $cantidadEntradas): float
    {
        $descuento = match ($this) {
            self::Porcentaje => $subtotalEntradas * $valor / 100,
            self::MontoPorEntrada => $valor * $cantidadEntradas,
            self::MontoTotal => $valor,
        };

        return round(min(max($descuento, 0), $subtotalEntradas), 2);
    }
}
