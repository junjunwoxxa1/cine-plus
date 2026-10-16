<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Cine+</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0b0f19; color: #f3f4f6; margin: 0; padding: 30px 15px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 560px; background-color: #111827; border-radius: 16px; overflow: hidden; border: 1px solid #1f2937;">
        <tr>
            <td style="padding: 28px; background: linear-gradient(to right, #991b1b, #dc2626); text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 30px; font-weight: 900; letter-spacing: 2px;">
                    CINE<span style="color: #f59e0b;">+</span>
                </h1>
                <p style="color: #fecaca; margin: 6px 0 0 0; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                    ¡Bienvenido a la Mejor Experiencia de Cine!
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 32px 24px;">
                <p style="font-size: 16px; color: #ffffff; margin: 0 0 16px 0;">
                    ¡Hola, <strong>{{ $user->name }}</strong>! 🎉
                </p>
                <p style="font-size: 14px; color: #9ca3af; line-height: 1.6; margin: 0 0 24px 0;">
                    Gracias por unirte a <strong>Cine+</strong>. Tu cuenta ha sido creada y verificada exitosamente con el correo <strong>{{ $user->email }}</strong>.
                </p>

                <div style="background-color: #1f2937; border-radius: 12px; padding: 20px; border: 1px solid #374151; margin-bottom: 24px;">
                    <h3 style="color: #f59e0b; margin: 0 0 12px 0; font-size: 14px; font-weight: bold; text-transform: uppercase;">
                        🍿 Beneficios de tu Cuenta Cine+
                    </h3>
                    <ul style="margin: 0; padding-left: 20px; color: #d1d5db; font-size: 13px; line-height: 1.8;">
                        <li><strong>Acceso Rápido:</strong> Inicia sesión con tu contraseña o con tu código de acceso por correo.</li>
                        <li><strong>Reserva de Butacas:</strong> Elige tus asientos favoritos en tiempo real.</li>
                        <li><strong>Dulcería Express:</strong> Compra tus combos y snacks favoritos sin hacer colas.</li>
                        <li><strong>Comprobante Digital:</strong> Recibe tus boletas y entradas con código QR directamente en tu correo.</li>
                    </ul>
                </div>

                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px; text-align: center;">
                    <tr>
                        <td>
                            <a href="{{ route('home') }}"
                                style="background-color: #dc2626; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; padding: 14px 28px; border-radius: 10px; display: inline-block;">
                                Ver Cartelera de Películas »
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 12px; color: #6b7280; text-align: center; margin: 0; border-top: 1px solid #1f2937; padding-top: 18px;">
                    Si tienes alguna duda o consulta, puedes responder directamente a este correo.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 18px; background-color: #0b0f19; text-align: center; border-top: 1px solid #1f2937;">
                <p style="margin: 0; font-size: 11px; color: #4b5563;">
                    © {{ date('Y') }} Cine+ Perú. Todos los derechos reservados.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
