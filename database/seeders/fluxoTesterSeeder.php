<?php

namespace Database\Seeders;

use App\Enums\TipoUsuario;
use App\Models\Organizacao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use function Illuminate\Support\now;

class fluxoTesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $org = Organizacao::create([
            'cnpj' => '75054728000103',
            'nome' => 'OrganizacaoTeste',
            'dominio_email' => 'org.com.br',
        ]);

        $org->integrantes()->create([
            'name' => 'admin',
            'email' => 'admin@org.com.br',
            'cpf' => '63507515075',
            'password' => Hash::make('SenhaForte!'),
            'tipo' => TipoUsuario::ADMINISTRADOR_ORGANIZACAO,

        ]);

        $motorista = $org->integrantes()->create([
            'name' => 'motorista',
            'email' => 'motorista@org.com.br',
            'password' => Hash::make('SenhaForte!'),
            'cpf' => '79874737077',
            'tipo' => TipoUsuario::PADRAO,
        ]);

        $motorista->carteira()->create();

        $motorista->perfilMotorista()->create([
            'cnh' => '92189958447',
            'aprovado_em' => now(),
        ]);

        $passageiro = $org->integrantes()->create([
            'name' => 'passageiro',
            'email' => 'passageiro@org.com.br',
            'password' => Hash::make('SenhaForte!'),
            'cpf' => '64090083095',
            'tipo' => TipoUsuario::PADRAO,
        ]);
        $passageiro->carteira()->create();
    }
}
