<?php

namespace Tests\Feature\JoaoMosson;

use App\Rules\Cnpj;
use PHPUnit\Framework\TestCase;

class CnpjTest extends TestCase
{
    private Cnpj $regra;

    protected function setUp(): void
    {
        parent::setUp();
        $this->regra = new Cnpj;
    }

    private function validarCnpj(mixed $valor): ?string
    {
        $erro = null;
        $this->regra->validate('cnpj', $valor, function ($mensagem) use (&$erro) {
            $erro = $mensagem;
        });

        return $erro;
    }

    public function test_valida_cnpjs_autenticos_com_e_sem_pontuacao(): void
    {
        $this->assertNull($this->validarCnpj('11444777000161'));
        $this->assertNull($this->validarCnpj('11.444.777/0001-61'));
    }

    public function test_rejeita_cnpj_com_menos_de_14_digitos_ou_vazio(): void
    {
        $this->assertNotNull($this->validarCnpj(''));
        $this->assertNotNull($this->validarCnpj('1144477700016'));
    }

    public function test_rejeita_sequencias_de_cnpjs_repetidos(): void
    {
        $this->assertEquals(
            'O campo :attribute não é um CNPJ válido.',
            $this->validarCnpj('00.000.000/0000-00')
        );
        $this->assertNotNull($this->validarCnpj('11111111111111'));
    }

    public function test_rejeita_cnpj_com_primeiro_ou_segundo_digito_verificador_adulterado(): void
    {
        // 11.444.777/0001-61 é o autêntico
        $this->assertNotNull($this->validarCnpj('11444777000162'));
        $this->assertNotNull($this->validarCnpj('11444777000151'));
    }
}
