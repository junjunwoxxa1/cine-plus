<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property int|null $role_id
 * @property int $saldo_puntos
 * @property int $puntos_cine
 * @property int $puntos_dulceria
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Role|null $role
 */
#[Fillable(['name', 'email', 'password', 'role_id', 'saldo_puntos', 'puntos_cine', 'puntos_dulceria'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isDulceria(): bool
    {
        return $this->hasRole(Role::DULCERIA);
    }

    public function canAccessPos(): bool
    {
        return $this->isAdmin() || $this->isDulceria();
    }

    public function comprobantes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comprobante::class)->latest();
    }

    /**
     * Sincroniza el saldo total de puntos sumando los puntos individuales.
     */
    public function actualizarTotalPuntos(): void
    {
        $this->saldo_puntos = max(0, (int) ($this->puntos_cine ?? 0) + (int) ($this->puntos_dulceria ?? 0));
        $this->save();
    }

    /**
     * Suma puntos obtenidos por compra de entradas de cine.
     */
    public function sumarPuntosCine(int $puntos): void
    {
        $this->puntos_cine = (int) ($this->puntos_cine ?? 0) + $puntos;
        $this->actualizarTotalPuntos();
    }

    /**
     * Suma puntos obtenidos por compra de snacks / dulcería.
     */
    public function sumarPuntosDulceria(int $puntos): void
    {
        $this->puntos_dulceria = (int) ($this->puntos_dulceria ?? 0) + $puntos;
        $this->actualizarTotalPuntos();
    }

    /**
     * Descuenta puntos de cine por canje de entradas.
     */
    public function descontarPuntosCine(int $puntos): void
    {
        $puntosDisponibles = (int) ($this->puntos_cine ?? 0);
        $this->puntos_cine = max(0, $puntosDisponibles - $puntos);
        $this->actualizarTotalPuntos();
    }

    /**
     * Descuenta puntos de dulcería por canje de alimentos / snacks.
     */
    public function descontarPuntosDulceria(int $puntos): void
    {
        $puntosDisponibles = (int) ($this->puntos_dulceria ?? 0);
        $this->puntos_dulceria = max(0, $puntosDisponibles - $puntos);
        $this->actualizarTotalPuntos();
    }
}
