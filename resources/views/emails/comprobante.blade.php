<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tus Entradas Cine+</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0b0f19; color: #f3f4f6; margin: 0; padding: 30px 15px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #111827; border-radius: 16px; overflow: hidden; border: 1px solid #1f2937;">
        {{-- Header --}}
        <tr>
            <td style="padding: 24px; background: linear-gradient(to right, #991b1b, #dc2626); text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 900; letter-spacing: 2px;">
                    CINE<span style="color: #f59e0b;">+</span>
                </h1>
                <p style="color: #fecaca; margin: 6px 0 0 0; font-size: 13px; font-weight: bold; text-transform: uppercase;">
                    ¡Pago Confirmado! Aquí tienes tus entradas
                </p>
            </td>
        </tr>

        {{-- Body --}}
        <tr>
            <td style="padding: 30px 24px;">
                <p style="font-size: 16px; color: #e5e7eb; margin: 0 0 16px 0;">
                    Hola, <strong>{{ $comprobante->cliente_nombre }}</strong> 👋
                </p>
                <p style="font-size: 14px; color: #9ca3af; line-height: 1.6; margin: 0 0 20px 0;">
                    Tu compra ha sido procesada con éxito. Adjunto a este correo encontrarás tu <strong>comprobante oficial en formato PDF</strong> con tu código QR de acceso.
                </p>

                {{-- Tarjeta de la Función --}}
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #1f2937; border-radius: 12px; padding: 20px; margin-bottom: 24px; border: 1px solid #374151;">
                    <tr>
                        <td colspan="2" style="padding-bottom: 12px;">
                            <span style="background-color: rgba(220, 38, 38, 0.2); color: #f87171; border: 1px solid rgba(220, 38, 38, 0.4); font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 6px; text-transform: uppercase;">
                                🎬 {{ $comprobante->pelicula_titulo }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af; font-size: 12px;">🏛️ Sede:</td>
                        <td style="padding: 6px 0; color: #ffffff; font-size: 13px; font-weight: bold; text-align: right;">{{ $comprobante->sede }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af; font-size: 12px;">🚪 Sala:</td>
                        <td style="padding: 6px 0; color: #ffffff; font-size: 13px; font-weight: bold; text-align: right;">{{ $comprobante->sala }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af; font-size: 12px;">📅 Horario:</td>
                        <td style="padding: 6px 0; color: #ffffff; font-size: 13px; font-weight: bold; text-align: right;">
                            {{ $comprobante->fecha_funcion ? $comprobante->fecha_funcion->locale('es')->isoFormat('ddd DD MMM, HH:mm [hrs]') : 'Programado' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af; font-size: 12px;">🎟️ Butacas:</td>
                        <td style="padding: 6px 0; color: #f87171; font-size: 13px; font-weight: 900; text-align: right;">
                            {{ $comprobante->asientos_texto }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #9ca3af; font-size: 12px;">💰 Total Pagado:</td>
                        <td style="padding: 6px 0; color: #34d399; font-size: 15px; font-weight: 900; text-align: right;">
                            S/ {{ number_format($comprobante->total, 2) }}
                        </td>
                    </tr>
                </table>

                {{-- Botón para Ver Comprobante Online --}}
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px; text-align: center;">
                    <tr>
                        <td>
                            <a href="{{ route('pago.comprobante', $comprobante->codigo_reserva) }}"
                                style="background-color: #dc2626; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; padding: 14px 28px; border-radius: 10px; display: inline-block;">
                                Ver Comprobante Digital con QR »
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 12px; color: #6b7280; text-align: center; margin: 0;">
                    Código de Reserva: <strong>#{{ $comprobante->codigo_reserva }}</strong> | Boleta: <strong>{{ $comprobante->serie_numero }}</strong>
                </p>
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td style="padding: 20px; background-color: #0b0f19; text-align: center; border-top: 1px solid #1f2937;">
                <p style="margin: 0; font-size: 11px; color: #4b5563;">
                    © {{ date('Y') }} Cine+ Perú. Todos los derechos reservados.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

