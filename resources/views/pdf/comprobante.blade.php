<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Entrada - {{ $comprobante->codigo_reserva }}</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            background-color: #ffffff;
        }
        .header {
            border-bottom: 2px solid #dc2626;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo-title {
            font-size: 28px;
            font-weight: 900;
            color: #dc2626;
            letter-spacing: 2px;
        }
        .logo-plus {
            color: #f59e0b;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-left {
            width: 60%;
            vertical-align: top;
        }
        .header-right {
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        .badge-box {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px;
            display: inline-block;
            text-align: center;
        }
        .badge-title {
            font-size: 10px;
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
        }
        .badge-number {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            font-family: monospace;
        }
        .ticket-hero {
            background-color: #111827;
            color: #ffffff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .movie-title {
            font-size: 22px;
            font-weight: 900;
            color: #ffffff;
            margin: 5px 0 15px 0;
        }
        .details-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .details-grid td {
            padding: 6px 10px;
            vertical-align: top;
        }
        .label {
            font-size: 9px;
            color: #9ca3af;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
        }
        .value {
            font-size: 13px;
            font-weight: bold;
            color: #ffffff;
        }
        .seat-pill {
            display: inline-block;
            background-color: #dc2626;
            color: #ffffff;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            margin-right: 4px;
            margin-bottom: 4px;
            font-size: 11px;
        }
        .qr-section {
            text-align: center;
            background-color: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .qr-code {
            width: 130px;
            height: 130px;
            margin: 0 auto;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .table-items th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
            padding: 8px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        .table-items td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-box {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .total-box td {
            padding: 5px 8px;
        }
        .total-row {
            font-size: 14px;
            font-weight: 900;
            color: #dc2626;
            border-top: 2px solid #dc2626;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <span class="logo-title">CINE<span class="logo-plus">+</span></span>
                    <p style="margin: 4px 0 0 0; color: #6b7280; font-size: 11px;">
                        Experiencia Cinematográfica Digital & Dulcería
                    </p>
                </td>
                <td class="header-right">
                    <div class="badge-box">
                        <div class="badge-title">Boleta Electrónica</div>
                        <div class="badge-number">{{ $comprobante->serie_numero }}</div>
                        <div style="font-size: 9px; color: #6b7280; margin-top: 3px;">
                            Reserva: #{{ $comprobante->codigo_reserva }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Hero Ticket --}}
    <div class="ticket-hero">
        <div style="font-size: 10px; color: #f87171; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;">
            {{ $comprobante->cantidad_entradas > 0 ? '🎟️ ENTRADA DE CINE OFICIAL' : '🍿 COMPROBANTE DE DULCERÍA & SNACKS' }}
        </div>
        <div class="movie-title">
            {{ $comprobante->pelicula_titulo }}
        </div>

        <table class="details-grid">
            <tr>
                <td style="width: 50%;">
                    <span class="label">🏛️ Sede / Complejo</span>
                    <span class="value">{{ $comprobante->sede }}</span>
                </td>
                <td style="width: 50%;">
                    <span class="label">{{ $comprobante->cantidad_entradas > 0 ? '🚪 Sala' : '🍿 Punto de Entrega' }}</span>
                    <span class="value">{{ $comprobante->sala ?? 'Módulo Express' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">{{ $comprobante->cantidad_entradas > 0 ? '📅 Fecha y Hora de Función' : '📅 Fecha de Pedido' }}</span>
                    <span class="value">
                        {{ $comprobante->fecha_funcion ? $comprobante->fecha_funcion->locale('es')->isoFormat('ddd DD MMM YYYY, HH:mm [hrs]') : now()->locale('es')->isoFormat('ddd DD MMM YYYY, HH:mm [hrs]') }}
                    </span>
                </td>
                <td>
                    @if ($comprobante->cantidad_entradas > 0)
                        <span class="label">🎟️ Asientos / Butacas</span>
                        <div style="margin-top: 4px;">
                            @if (is_array($comprobante->asientos))
                                @foreach ($comprobante->asientos as $asiento)
                                    <span class="seat-pill">{{ $asiento }}</span>
                                @endforeach
                            @else
                                <span class="value">{{ $comprobante->asientos_texto }}</span>
                            @endif
                        </div>
                    @else
                        <span class="label">⚡ Modalidad de Entrega</span>
                        <span class="value" style="color: #fbbf24;">Retiro Rápido en Dulcería Express</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">👤 Cliente</span>
                    <span class="value">{{ $comprobante->cliente_nombre }}</span>
                </td>
                <td>
                    <span class="label">✉️ Correo Electrónico</span>
                    <span class="value">{{ $comprobante->cliente_email }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Código QR para escaneo en puerta --}}
    <div class="qr-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 35%; text-align: center; vertical-align: middle;">
                    @php
                        $qrData = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=4&data=' . urlencode(route('pago.comprobante', $comprobante->codigo_reserva));
                    @endphp
                    <img src="{{ $qrData }}" alt="Código QR de Acceso" class="qr-code">
                </td>
                <td style="width: 65%; vertical-align: middle; text-align: left; padding-left: 15px;">
                    <div style="font-size: 13px; font-weight: bold; color: #111827;">
                        Código de Validación en Puerta
                    </div>
                    <div style="font-size: 11px; color: #4b5563; margin: 4px 0 8px 0;">
                        Presenta este código QR desde tu celular o impreso para el ingreso directo a la sala sin hacer colas.
                    </div>
                    <div style="font-family: monospace; font-size: 12px; font-weight: bold; color: #dc2626; background: #fee2e2; display: inline-block; padding: 3px 8px; border-radius: 4px;">
                        #{{ $comprobante->codigo_reserva }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Desglose de Ítems (Entradas y Snacks) --}}
    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 50%;">Descripción del Ítem</th>
                <th class="text-center" style="width: 15%;">Cant.</th>
                <th class="text-right" style="width: 15%;">Precio Unit.</th>
                <th class="text-right" style="width: 20%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($comprobante->detalles as $det)
                <tr>
                    <td>{{ $det->descripcion }}</td>
                    <td class="text-center">{{ $det->cantidad }}</td>
                    <td class="text-right">S/ {{ number_format($det->precio_unitario, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($det->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totales --}}
    <table class="total-box">
        @if ($comprobante->cantidad_entradas > 0)
            <tr>
                <td style="color: #6b7280;">Entradas ({{ $comprobante->cantidad_entradas }}x):</td>
                <td class="text-right" style="font-weight: bold;">S/ {{ number_format($comprobante->monto_entradas, 2) }}</td>
            </tr>
        @endif
        @if ($comprobante->monto_dulceria > 0)
            <tr>
                <td style="color: #6b7280;">Dulcería / Snacks:</td>
                <td class="text-right" style="font-weight: bold;">S/ {{ number_format($comprobante->monto_dulceria, 2) }}</td>
            </tr>
        @endif
        @if ($comprobante->monto_descuento > 0)
            <tr>
                <td style="color: #16a34a;">Descuento ({{ $comprobante->descuento_concepto ?? 'Promoción' }}):</td>
                <td class="text-right" style="font-weight: bold; color: #16a34a;">-S/ {{ number_format($comprobante->monto_descuento, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td style="color: #6b7280;">Op. Gravada:</td>
            <td class="text-right">S/ {{ number_format($comprobante->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #6b7280;">IGV (18%):</td>
            <td class="text-right">S/ {{ number_format($comprobante->igv, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL PAGADO:</td>
            <td class="text-right">S/ {{ number_format($comprobante->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 10px; color: #6b7280; text-align: right; padding-top: 4px;">
                Medio de pago: {{ $comprobante->metodo_pago_nombre }}
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>¡Gracias por elegir Cine+! Este documento es tu comprobante de pago electrónico y boleto de acceso oficial.</p>
        <p>Cine+ Perú S.A.C. - RUC: 20601234567 - Atención al cliente: contacto@cineplus.com</p>
    </div>

</body>
</html>

