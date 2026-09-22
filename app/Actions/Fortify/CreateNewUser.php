<?php

namespace App\Actions\Fortify;

use App\Enums\TipoUsuario;
use App\Models\Organizacao;
use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email:rfc,spoof|max:255|unique:users',
            'password' => $this->passwordRules(),
            'cpf' => ['required', 'string', new Cpf, 'unique:users'],
        ])->validate();

        $email = $input['email'];
        $dominio = substr($email, strpos($email, '@') + 1);
        $organizacao = Organizacao::where('dominio_email', $dominio)->first();

        if (! $organizacao) {
            throw ValidationException::withMessages([
                'email' => ['O domínio do seu e-mail não está cadastrado com nenhuma organização parceira.'],
            ]);
        }

        $user = $organizacao->integrantes()->create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'cpf' => $input['cpf'],
            'tipo' => TipoUsuario::PADRAO,
        ]);

        $user->carteira()->create();

        return $user;
    }
}
