<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ ($modo ?? 'reset') === 'login' ? 'Código de Acceso Directo' : 'Código de Recuperación de Cuenta' }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0b0f19; color: #f3f4f6; margin: 0; padding: 30px 15px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; background-color: #111827; border-radius: 16px; overflow: hidden; border: 1px solid #1f2937;">
        <tr>
            <td style="padding: 24px; background: linear-gradient(to right, #991b1b, #dc2626); text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 900; letter-spacing: 2px;">
                    CINE<span style="color: #f59e0b;">+</span>
                </h1>
                <p style="color: #fecaca; margin: 6px 0 0 0; font-size: 12px; font-weight: bold; text-transform: uppercase;">
                    {{ ($modo ?? 'reset') === 'login' ? 'Llave de Acceso por Correo' : 'Autenticador de Seguridad' }}
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px 24px; text-align: center;">
                <div style="font-size: 40px; margin-bottom: 15px;">
                    {{ ($modo ?? 'reset') === 'login' ? '🔑' : '🔐' }}
                </div>
                <h2 style="color: #ffffff; font-size: 20px; font-weight: bold; margin: 0 0 10px 0;">
                    {{ ($modo ?? 'reset') === 'login' ? 'Código para Iniciar Sesión sin Contraseña' : 'Recuperación de Contraseña' }}
                </h2>
                <p style="font-size: 14px; color: #9ca3af; line-height: 1.6; margin: 0 0 24px 0;">
                    @if (($modo ?? 'reset') === 'login')
                        Has solicitado ingresar a tu cuenta <strong>{{ $email }}</strong> mediante llave de acceso. Introduce el siguiente código de 6 dígitos en la pantalla de inicio de sesión:
                    @else
                        Has solicitado restablecer tu contraseña para la cuenta <strong>{{ $email }}</strong>. Ingresa el siguiente código token en la pantalla de verificación:
                    @endif
                </p>

                {{-- Caja con el Token de 6 Dígitos --}}
                <div style="background-color: #1f2937; border: 2px dashed #dc2626; border-radius: 12px; padding: 18px 24px; display: inline-block; margin-bottom: 24px;">
                    <span style="font-family: 'Courier New', Courier, monospace; font-size: 32px; font-weight: 900; letter-spacing: 8px; color: #f87171;">
                        {{ $token }}
                    </span>
                </div>

                <p style="font-size: 12px; color: #6b7280; line-height: 1.5; margin: 0 0 20px 0;">
                    ⏱️ Este código es válido durante los próximos <strong>15 minutos</strong>.
                </p>

                <p style="font-size: 11px; color: #4b5563; margin: 0; border-top: 1px solid #1f2937; padding-top: 15px;">
                    Si no realizaste esta solicitud, puedes ignorar este mensaje de forma segura.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
