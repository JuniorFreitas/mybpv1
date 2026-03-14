<?php

namespace Tests\Unit\Services\Admissao\Importacao;

use App\Models\Sistema;
use App\Services\Admissao\Importacao\MapperLinhaPlanilhaParaPayload;
use Tests\TestCase;

class MapperLinhaPlanilhaParaPayloadTest extends TestCase
{
    private MapperLinhaPlanilhaParaPayload $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new MapperLinhaPlanilhaParaPayload();
    }

    public function testMapRetornaEstruturaCurriculoEAdmissao(): void
    {
        $linha = [
            'cpf' => '12345678909',
            'nome' => 'Fulano',
            'cep' => '01310100',
            'endereco' => 'Av Paulista',
            'numero' => '1000',
            'bairro' => 'Bela Vista',
            'municipio' => 'São Paulo',
            'uf' => 'SP',
            'telefone_numero' => '11999998888',
            'tipo_admissao' => 'FIXO',
            'data_admissao' => '01/01/2025',
            'data_aso' => '02/01/2025',
        ];
        $payload = $this->mapper->map($linha, 1, 2, 3);
        $this->assertArrayHasKey('curriculo', $payload);
        $this->assertArrayHasKey('admissao', $payload);
        $this->assertSame(Sistema::mascaraCpf('12345678909'), $payload['curriculo']['cpf']);
        $this->assertSame('Fulano', $payload['curriculo']['nome']);
        $this->assertSame(1, $payload['curriculo']['vaga_pretendida']);
        $this->assertSame(2, $payload['admissao']['area_etiqueta_id']);
        $this->assertSame(3, $payload['admissao']['centro_custo_id']);
        $this->assertSame('2025-01-01', $payload['admissao']['data_admissao']);
        $this->assertSame('2025-01-02', $payload['admissao']['data_aso']);
    }

    public function testMapEmailVazioUsaEmailPadrao(): void
    {
        $linha = ['cpf' => '12345678909', 'nome' => 'X', 'email' => ''];
        $payload = $this->mapper->map($linha, 1, null, 1);
        $this->assertSame(Sistema::EMAILPADRAO, $payload['curriculo']['email']);
    }

    public function testMapAreaNullPermitido(): void
    {
        $linha = ['cpf' => '12345678909', 'nome' => 'X'];
        $payload = $this->mapper->map($linha, 1, null, 1);
        $this->assertNull($payload['admissao']['area_etiqueta_id']);
    }

    public function testMapCnhVencimentoPlaceholderViraNull(): void
    {
        $linha = [
            'cpf' => '12345678909',
            'nome' => 'X',
            'cnh_vencimento' => 'aaaa-mm-dd',
        ];
        $payload = $this->mapper->map($linha, 1, null, 1);
        $this->assertNull($payload['curriculo']['cnh_vencimento']);
    }

    public function testMapNascimentoFormatoTrocadoPlanilhaCorrigido(): void
    {
        $linha = [
            'cpf' => '12345678909',
            'nome' => 'X',
            'nascimento' => '1994-29-7',
        ];
        $payload = $this->mapper->map($linha, 1, null, 1);
        $this->assertSame('1994-07-29', $payload['curriculo']['nascimento']);
    }
}
