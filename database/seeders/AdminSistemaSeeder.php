<?php

namespace Database\Seeders;

use App\Enums\TipoUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Cocar CEO',
            'email' => 'admin@cocar.com.br',
            'password' => Hash::make('SenhaForte!'),
            'cpf' => '00666034044',
            'tipo' => TipoUsuario::ADMINISTRADOR_SISTEMA,
        ]);
    }
}
