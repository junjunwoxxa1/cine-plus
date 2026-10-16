<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetTokenMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetTokenController extends Controller
{
    /**
     * Envía el correo con el código de 6 dígitos por SMTP real si está configurado,
     * o activa la bandeja simulada en pantalla si aún está en modo local (log) o si falla SMTP.
     *
     * @return array{enviado_real: bool, mensaje: string}
     */
    private function dispatchTokenEmail(string $email, string $token, string $modo): array
    {
        $mailer = config('mail.default');
        $smtpUser = config('mail.mailers.smtp.username');
        $smtpPass = config('mail.mailers.smtp.password');

        $smtpConfigurado = $mailer === 'smtp'
            && filled($smtpUser) && $smtpUser !== 'null'
            && filled($smtpPass) && $smtpPass !== 'null';

        if ($smtpConfigurado) {
            try {
                Mail::mailer('smtp')->to($email)->send(new PasswordResetTokenMail($token, $email, $modo));
                session()->forget(['dev_mail_code', 'dev_mail_error']);

                return [
                    'enviado_real' => true,
                    'mensaje' => 'Hemos enviado el código de 6 dígitos a tu correo '.$email.'. Revisa tu Bandeja de Entrada o Spam.',
                ];
            } catch (\Throwable $e) {
                Log::error('Error SMTP enviando código a '.$email.': '.$e->getMessage());

                try {
                    Mail::mailer('log')->to($email)->send(new PasswordResetTokenMail($token, $email, $modo));
                } catch (\Throwable) {
                }

                session([
                    'dev_mail_code' => $token,
                    'dev_mail_error' => 'No se pudo conectar al servidor SMTP ('.$e->getMessage().'). Se muestra el código aquí para que puedas continuar:',
                ]);

                return [
                    'enviado_real' => false,
                    'mensaje' => 'El servidor SMTP devolvió un error de conexión, pero generamos tu código de 6 dígitos abajo.',
                ];
            }
        }

        // Modo local sin SMTP configurado (MAIL_MAILER=log)
        try {
            Mail::mailer('log')->to($email)->send(new PasswordResetTokenMail($token, $email, $modo));
        } catch (\Throwable $e) {
            Log::error('Error registrando correo en log: '.$e->getMessage());
        }

        session([
            'dev_mail_code' => $token,
            'dev_mail_error' => null,
        ]);

        return [
            'enviado_real' => false,
            'mensaje' => 'Código generado para '.$email.'. (Modo local activo: puedes ver y usar tu código directamente aquí abajo o configurar tu SMTP Gmail).',
        ];
    }

    /**
     * Guarda las credenciales SMTP de Gmail en el archivo .env para habilitar el envío real de correos.
     */
    public function saveSmtpConfig(Request $request)
    {
        $request->validate([
            'smtp_email' => ['required', 'email'],
            'smtp_password' => ['required', 'string', 'min:8'],
        ], [
            'smtp_email.required' => 'Ingresa tu correo Gmail emisor.',
            'smtp_email.email' => 'Ingresa un correo Gmail válido.',
            'smtp_password.required' => 'Ingresa tu Contraseña de Aplicación de Google (16 letras).',
        ]);

        $smtpEmail = trim($request->input('smtp_email'));
        // Quitar espacios de la contraseña de aplicación de Google (ej. "abcd efgh ijkl mnop" -> "abcdefghijklmnop")
        $smtpPassword = str_replace(' ', '', trim($request->input('smtp_password')));

        $envPath = base_path('.env');
        if (file_exists($envPath) && is_writable($envPath)) {
            $envContent = file_get_contents($envPath);

            $replacements = [
                'MAIL_MAILER' => 'smtp',
                'MAIL_SCHEME' => 'tls',
                'MAIL_HOST' => 'smtp.gmail.com',
                'MAIL_PORT' => '587',
                'MAIL_USERNAME' => '"'.$smtpEmail.'"',
                'MAIL_PASSWORD' => '"'.$smtpPassword.'"',
                'MAIL_FROM_ADDRESS' => '"'.$smtpEmail.'"',
            ];

            foreach ($replacements as $key => $val) {
                if (preg_match("/^{$key}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$val}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$val}";
                }
            }

            file_put_contents($envPath, $envContent);

            try {
                Artisan::call('config:clear');
            } catch (\Throwable) {
            }
        }

        return back()->with(
            'smtp_saved_status',
            '¡Servidor SMTP Gmail configurado con '.$smtpEmail.'! Haz clic en "Reenviar código" para recibirlo en tu bandeja real.'
        );
    }

    /**
     * Envía un código de 6 dígitos al correo para iniciar sesión SIN contraseña (llave de acceso por correo).
     */
    public function sendLoginCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Ingresa tu correo electrónico registrado.',
            'email.email' => 'Ingresa un correo electrónico válido.',
        ]);

        $email = Str::lower(trim($request->input('email')));
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()
                ->with('login_code_open', true)
                ->withErrors(['login_code_email' => 'No existe ninguna cuenta registrada con este correo electrónico.'])
                ->withInput();
        }

        $token = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resultado = $this->dispatchTokenEmail($email, $token, 'login');

        session([
            'login_code_email' => $email,
            'login_code_step' => 'verify',
        ]);

        return redirect()->route('login')->with('login_code_status', $resultado['mensaje']);
    }

    /**
     * Verifica el código de 6 dígitos e inicia la sesión del usuario directamente sin contraseña.
     */
    public function verifyLoginCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string', 'size:6'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'token.required' => 'Ingresa el código de 6 dígitos enviado a tu correo.',
            'token.size' => 'El código debe tener exactamente 6 dígitos.',
        ]);

        $email = Str::lower(trim($request->input('email')));
        $tokenInput = trim($request->input('token'));

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $record) {
            return back()
                ->withErrors(['login_code_token' => 'No hay ningún código activo para este correo. Solicita uno nuevo.'])
                ->withInput();
        }

        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            session()->forget(['login_code_email', 'login_code_step', 'dev_mail_code', 'dev_mail_error']);

            return back()->withErrors([
                'login_code_email' => 'El código ha expirado (15 min). Solicita uno nuevo.',
            ]);
        }

        if (! Hash::check($tokenInput, $record->token) && $tokenInput !== $record->token) {
            return back()
                ->withErrors(['login_code_token' => 'El código ingresado es incorrecto. Revisa tu correo.'])
                ->withInput();
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors(['login_code_email' => 'Usuario no encontrado.']);
        }

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        // Eliminar el código ya utilizado e iniciar sesión directamente
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget(['login_code_email', 'login_code_step', 'dev_mail_code', 'dev_mail_error']);

        Auth::login($user, true);
        $request->session()->regenerate();

        return \App\Http\Responses\LoginResponse::redirectForUser($request);
    }

    /**
     * Permite cambiar el correo en el flujo de acceso por código.
     */
    public function resetLoginCodeStep()
    {
        session()->forget(['login_code_email', 'login_code_step', 'dev_mail_code', 'dev_mail_error']);

        return redirect()->route('login')->with('login_code_open', true);
    }

    /**
     * Muestra la vista de solicitud de token al correo (recuperar contraseña).
     */
    public function showRequestForm()
    {
        return view('auth.forgot-password-token');
    }

    /**
     * Genera un token numérico de 6 dígitos, lo guarda en password_reset_tokens y lo envía al correo.
     */
    public function sendToken(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Por favor ingresa un correo electrónico válido.',
        ]);

        $email = Str::lower(trim($request->input('email')));
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'No encontramos ninguna cuenta asociada a este correo electrónico.',
            ])->withInput();
        }

        $token = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resultado = $this->dispatchTokenEmail($email, $token, 'reset');

        session(['reset_password_email' => $email]);

        return redirect()->route('password.verify-token')->with('status', $resultado['mensaje']);
    }

    /**
     * Muestra la pantalla para ingresar el token y la nueva contraseña.
     */
    public function showVerifyForm()
    {
        $email = session('reset_password_email', request('email', ''));

        return view('auth.verify-reset-token', compact('email'));
    }

    /**
     * Valida el token del autenticador y restablece la contraseña.
     */
    public function resetWithToken(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'token.required' => 'Debes ingresar el código de 6 dígitos.',
            'token.size' => 'El código de seguridad debe tener exactamente 6 dígitos.',
            'password.required' => 'Ingresa una nueva contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $email = Str::lower(trim($request->input('email')));
        $tokenInput = trim($request->input('token'));

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $record) {
            return back()->withErrors([
                'token' => 'No se encontró ninguna solicitud de recuperación activa para este correo.',
            ])->withInput();
        }

        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            session()->forget(['dev_mail_code', 'dev_mail_error']);

            return back()->withErrors([
                'token' => 'El código ha expirado (validez de 15 minutos). Por favor solicita uno nuevo.',
            ])->withInput();
        }

        if (! Hash::check($tokenInput, $record->token) && $tokenInput !== $record->token) {
            return back()->withErrors([
                'token' => 'El código token ingresado es incorrecto. Verifica en tu bandeja de entrada o spam.',
            ])->withInput();
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->forceFill([
                'password' => Hash::make($request->input('password')),
                'email_verified_at' => $user->email_verified_at ?? now(),
                'remember_token' => Str::random(60),
            ])->save();
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget(['reset_password_email', 'dev_mail_code', 'dev_mail_error']);

        return redirect()->route('login')->with(
            'status',
            '¡Tu contraseña ha sido restablecida exitosamente! Ya puedes iniciar sesión.'
        );
    }
}

