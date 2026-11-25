<?php

namespace Tests\Unit\Services\Admissao;

use App\Models\Admissao;
use App\Models\Examesesmt;
use App\Models\FeedbackCurriculo;
use App\Models\TelefoneCurriculo;
use App\Services\Admissao\AdmissaoProcessoListagemPresenter;
use Tests\TestCase;

/**
 * Cobre AdmissaoProcessoListagemPresenter (extraído de AdmissaoController::atualizar,
 * TD-002 fatia 3).
 *
 * transformarItens() exige um FeedbackCurriculo real (setAppends/setVisible/
 * unsetRelation são métodos de Eloquent Model), então aqui focamos nos métodos puros
 * extraídos (montarTelefonePrincipal/resolverDadosCentroCusto), que concentram as
 * regras de negócio da transformação e são testáveis sem Schema/DB.
 */
class AdmissaoProcessoListagemPresenterTest extends TestCase
{
    private AdmissaoProcessoListagemPresenter $presenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->presenter = new AdmissaoProcessoListagemPresenter();
    }

    public function test_presenter_instancia_corretamente(): void
    {
        $this->assertInstanceOf(AdmissaoProcessoListagemPresenter::class, $this->presenter);
    }

    public function test_montar_telefone_principal_retorna_null_quando_numero_vazio_ou_nulo(): void
    {
        $this->assertNull($this->presenter->montarTelefonePrincipal(null, 'whatsapp'));
        $this->assertNull($this->presenter->montarTelefonePrincipal('', 'whatsapp'));
    }

    public function test_montar_telefone_principal_marca_whatsapp_quando_tipo_e_whats(): void
    {
        $resultado = $this->presenter->montarTelefonePrincipal('11999998888', TelefoneCurriculo::TIPO_WHATS);

        $this->assertSame([
            'numero' => '11999998888',
            'tipo' => TelefoneCurriculo::TIPO_WHATS,
            'whatsapp' => true,
        ], $resultado);
    }

    public function test_montar_telefone_principal_nao_marca_whatsapp_para_outros_tipos(): void
    {
        $resultado = $this->presenter->montarTelefonePrincipal('1133224455', 'fixo');

        $this->assertSame([
            'numero' => '1133224455',
            'tipo' => 'fixo',
            'whatsapp' => false,
        ], $resultado);
    }

    public function test_montar_telefone_principal_aceita_tipo_nulo(): void
    {
        $resultado = $this->presenter->montarTelefonePrincipal('1133224455', null);

        $this->assertSame([
            'numero' => '1133224455',
            'tipo' => null,
            'whatsapp' => false,
        ], $resultado);
    }

    public function test_resolver_dados_centro_custo_retorna_vazio_quando_nao_encontra(): void
    {
        $cc = ['centros_custos' => [[], []]];

        $resultado = $this->presenter->resolverDadosCentroCusto($cc, 999);

        $this->assertSame([
            'emp_cnpj' => null,
            'emp_nome_fantasia' => null,
            'emp_centro_custo' => null,
            'emp_tipo' => null,
        ], $resultado);
    }

    public function test_resolver_dados_centro_custo_retorna_vazio_quando_centro_custo_id_nulo(): void
    {
        $cc = ['centros_custos' => [[
            ['id' => 1, 'label' => 'Matriz', 'cnpj_format' => '11.222.333/0001-81', 'nome_fantasia' => 'Empresa', 'matriz' => true],
        ]]];

        $resultado = $this->presenter->resolverDadosCentroCusto($cc, null);

        $this->assertNull($resultado['emp_cnpj']);
    }

    public function test_resolver_dados_centro_custo_encontra_matriz(): void
    {
        $cc = ['centros_custos' => [[
            ['id' => 1, 'label' => 'Matriz CC', 'cnpj_format' => '11.222.333/0001-81', 'nome_fantasia' => 'Empresa Matriz', 'razao_social' => 'Empresa Matriz LTDA', 'matriz' => true, 'ativo' => true],
        ]]];

        $resultado = $this->presenter->resolverDadosCentroCusto($cc, 1);

        $this->assertSame([
            'emp_cnpj' => '11.222.333/0001-81',
            'emp_nome_fantasia' => 'Empresa Matriz',
            'emp_centro_custo' => 'Matriz CC',
            'emp_tipo' => 'Matriz',
        ], $resultado);
    }

    public function test_resolver_dados_centro_custo_encontra_filial(): void
    {
        $cc = ['centros_custos' => [
            [['id' => 1, 'label' => 'Matriz CC', 'cnpj_format' => '11.222.333/0001-81', 'nome_fantasia' => 'Empresa Matriz', 'matriz' => true, 'ativo' => true]],
            [['id' => 2, 'label' => 'Filial CC', 'cnpj_format' => '22.333.444/0001-11', 'nome_fantasia' => 'Empresa Filial', 'matriz' => false, 'ativo' => true]],
        ]];

        $resultado = $this->presenter->resolverDadosCentroCusto($cc, 2);

        $this->assertSame('Filial CC', $resultado['emp_centro_custo']);
        $this->assertSame('Filial', $resultado['emp_tipo']);
    }

    /**
     * Regressão: eager-load 'Admissao' deve ser reutilizado (sem lazy-load)
     * e UltimoAso aninhado deve sobreviver ao toArray (snake_case).
     */
    public function test_transformar_itens_reusa_admissao_eager_loaded_com_ultimo_aso(): void
    {
        $feedback = new FeedbackCurriculo();
        $feedback->setRelation('VagaAberta', null);
        $feedback->setRelation('Curriculo', null);
        $feedback->setRelation('ResultadoIntegrado', null);
        $feedback->setRelation('UltimoAso', null);
        $feedback->forceFill(['id' => 1, 'empresa_id' => 1, 'curriculo_id' => 1, 'vagas_abertas_id' => null]);

        $aso = new Examesesmt();
        $aso->forceFill(['id' => 7, 'feedback_id' => 1, 'data_realizacao' => '2026-01-15']);

        $admissao = new Admissao();
        $admissao->forceFill([
            'id' => 5,
            'feedback_id' => 1,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'tipo_admissao' => 'FIXO',
            'data_admissao' => '01/01/2026',
            'numero_cracha' => null,
            'centro_custo_id' => 1,
            'cargo' => 'Analista',
            'funcao' => 'RH',
        ]);
        $admissao->setRelation('UltimoAso', $aso);
        $admissao->setRelation('Demissao', null);
        $feedback->setRelation('Admissao', $admissao);

        $cc = ['centros_custos' => [[
            ['id' => 1, 'label' => 'Matriz CC', 'cnpj_format' => '11.222.333/0001-81', 'nome_fantasia' => 'Empresa', 'matriz' => true],
        ]]];

        $itens = $this->presenter->transformarItens(collect([$feedback]), $cc);
        $payload = $itens->first()->toArray();

        $this->assertArrayHasKey('admissao', $payload);
        $this->assertSame('ANALISTA', $payload['admissao']['cargo']);
        $this->assertSame('Matriz CC', $payload['admissao']['emp_centro_custo']);
        $this->assertArrayHasKey('ultimo_aso', $payload['admissao']);
        $this->assertNotNull($payload['admissao']['ultimo_aso']);
        $this->assertArrayNotHasKey('Admissao', $payload);
    }
}
