<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ], [
            'name.required' => 'Por favor ingresa tu nombre completo.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'Debes ingresar una contraseña.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.mixed' => 'La contraseña debe incluir al menos una letra mayúscula y una minúscula.',
            'password.letters' => 'La contraseña debe incluir al menos una letra.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
            'password.symbols' => 'La contraseña debe incluir al menos un carácter especial (ej. @ # $ % & * !).',
        ])->validate();

        $user = User::ForceCreate([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'email_verified_at' => now(),
        ]);

        $user->role()->associate(Role::firstWhere('slug', Role::CLIENTE));
        $user->save();

        if (! empty($user->email)) {
            try {
                \Illuminate\Support\Facades\Mail::mailer('smtp')
                    ->to($user->email)
                    ->send(new \App\Mail\BienvenidaUserMail($user));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error enviando correo de bienvenida a '.$user->email.': '.$e->getMessage());
            }
        }

        return $user;
    }
}
