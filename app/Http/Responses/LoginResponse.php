<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirige a cada usuario según su rol después de iniciar sesión o registrarse:
 * administradores al panel, personal de dulcería al POS y clientes a la cartelera,
 * limpiando cualquier URL "intended" de administración si el usuario no tiene permisos.
 */
class LoginResponse implements LoginResponseContract, RegisterResponseContract, TwoFactorLoginResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false]);
        }

        return self::redirectForUser($request);
    }

    /**
     * Calcula la redirección segura según el rol del usuario autenticado.
     */
    public static function redirectForUser(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            $destinoPorDefecto = route('admin.dashboard');
        } elseif ($user?->isDulceria()) {
            $destinoPorDefecto = route('pos.dulceria');
        } else {
            $destinoPorDefecto = route('home');
        }

        $intended = $request->session()->get('url.intended');

        if (is_string($intended)) {
            $path = parse_url($intended, PHP_URL_PATH) ?: '';

            // Evitar redirigir a rutas de administración si el usuario no es administrador
            if (! $user?->isAdmin() && str_starts_with($path, '/admin')) {
                $request->session()->forget('url.intended');
            }

            // Evitar redirigir al POS de dulcería si el usuario es un cliente estándar
            if (! $user?->isAdmin() && ! $user?->isDulceria() && str_starts_with($path, '/pos-dulceria')) {
                $request->session()->forget('url.intended');
            }
        }

        return redirect()->intended($destinoPorDefecto);
    }
}


