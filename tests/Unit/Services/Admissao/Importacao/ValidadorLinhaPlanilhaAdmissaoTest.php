<?php

namespace Tests\Unit\Services\Admissao\Importacao;

use App\Models\Admissao;
use App\Services\Admissao\Importacao\ValidadorLinhaPlanilhaAdmissao;
use Tests\TestCase;

class ValidadorLinhaPlanilhaAdmissaoTest extends TestCase
{
    private ValidadorLinhaPlanilhaAdmissao $validador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validador = new ValidadorLinhaPlanilhaAdmissao();
    }

    public function testLinhaValidaRetornaSemErros(): void
    {
        $linha = $this->linhaValidaMinima();
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertEmpty($erros);
    }

    public function testCampoObrigatorioVazioRetornaErroNoCampo(): void
    {
        $linha = $this->linhaValidaMinima();
        $linha['cpf'] = '';
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertArrayHasKey('cpf', $erros);
        $this->assertArrayHasKey('mensagem', $erros['cpf']);
        $this->assertNotEmpty($erros['cpf']['mensagem']);
    }

    public function testCpfInvalidoRetornaErro(): void
    {
        $linha = $this->linhaValidaMinima();
        $linha['cpf'] = '11111111111';
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertArrayHasKey('cpf', $erros);
    }

    public function testTipoAdmissaoFixoSemPrazoExperienciaRetornaErro(): void
    {
        $linha = $this->linhaValidaMinima();
        $linha['tipo_admissao'] = Admissao::TIPO_ADMISSAO_FIXO;
        $linha['prazo_experiencia'] = '';
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertArrayHasKey('prazo_experiencia', $erros);
    }

    public function testTipoAdmissaoTemporarioSemAdmissaoEncerramentoRetornaErro(): void
    {
        $linha = $this->linhaValidaMinima();
        $linha['tipo_admissao'] = Admissao::TIPO_ADMISSAO_TEMPORARIO;
        $linha['admissao_encerramento'] = '';
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertArrayHasKey('admissao_encerramento', $erros);
    }

    public function testPcdSimSemCidRetornaErro(): void
    {
        $linha = $this->linhaValidaMinima();
        $linha['pcd'] = 'SIM';
        $linha['cid'] = '';
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertArrayHasKey('cid', $erros);
    }

    public function testPixSimSemPixChaveRetornaErro(): void
    {
        $linha = $this->linhaValidaMinima();
        $linha['pix'] = 'SIM';
        $linha['pix_tipo_chave'] = '';
        $linha['pix_chave'] = '';
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertArrayHasKey('pix_chave', $erros);
    }

    public function testDataAdmissaoFormatoInvalidoRetornaErro(): void
    {
        $linha = $this->linhaValidaMinima();
        $linha['data_admissao'] = '2025-03-15';
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertArrayHasKey('data_admissao', $erros);
    }

    public function testTipoAdmissaoValorInvalidoRetornaErro(): void
    {
        $linha = $this->linhaValidaMinima();
        $linha['tipo_admissao'] = 'INVALIDO';
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertArrayHasKey('tipo_admissao', $erros);
    }

    public function testEstruturaRetornoContemMensagemEComoCorrigir(): void
    {
        $linha = $this->linhaValidaMinima();
        $linha['nome'] = '';
        $erros = $this->validador->validar($linha, 2, 1);
        $this->assertArrayHasKey('nome', $erros);
        $this->assertArrayHasKey('mensagem', $erros['nome']);
        $this->assertArrayHasKey('como_corrigir', $erros['nome']);
    }

    private function linhaValidaMinima(): array
    {
        return [
            'cpf' => '123.456.789-09',
            'nome' => 'Fulano de Tal',
            'cep' => '65000000',
            'endereco' => 'Rua Teste',
            'numero' => '100',
            'bairro' => 'Centro',
            'municipio' => 'São Luís',
            'uf' => 'MA',
            'telefone_numero' => '98987654321',
            'cod_vaga' => '1',
            'centro_custo' => '1',
            'tipo_admissao' => Admissao::TIPO_ADMISSAO_FIXO,
            'data_admissao' => '15/03/2025',
            'data_aso' => '20/03/2025',
            'prazo_experiencia' => Admissao::PRAZO_NENHUM,
        ];
    }
}
