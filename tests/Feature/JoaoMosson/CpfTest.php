<?php

namespace Tests\Feature\JoaoMosson;

use App\Rules\Cpf;
use PHPUnit\Framework\TestCase;

class CpfTest extends TestCase
{
    private Cpf $regra;

    protected function setUp(): void
    {
        parent::setUp();
        $this->regra = new Cpf;
    }

    private function validarCpf(mixed $valor): ?string
    {
        $erro = null;
        $this->regra->validate('cpf', $valor, function ($mensagem) use (&$erro) {
            $erro = $mensagem;
        });

        return $erro;
    }

    public function test_valida_cpfs_validos_com_e_sem_mascara(): void
    {
        $this->assertNull($this->validarCpf('52998224725'));
        $this->assertNull($this->validarCpf('529.982.247-25'));
    }

    public function test_rejeita_cpf_com_quantidade_de_digitos_incorreta(): void
    {
        $this->assertNotNull($this->validarCpf('123456789'));
        $this->assertNotNull($this->validarCpf('123456789012'));
    }

    public function test_rejeita_cpfs_com_todos_os_digitos_iguais(): void
    {
        $cpfsInvalidos = [
            '000.000.000-00',
            '11111111111',
            '99999999999',
        ];

        foreach ($cpfsInvalidos as $cpf) {
            $this->assertEquals(
                'O campo :attribute não é um CPF válido.',
                $this->validarCpf($cpf),
                "Falha ao validar CPF inválido conhecido: {$cpf}"
            );
        }
    }

    public function test_rejeita_cpf_com_digitos_verificadores_incorretos(): void
    {
        // 529.982.247-25 é o correto; invertendo para 26
        $this->assertNotNull($this->validarCpf('52998224726'));
    }
}
