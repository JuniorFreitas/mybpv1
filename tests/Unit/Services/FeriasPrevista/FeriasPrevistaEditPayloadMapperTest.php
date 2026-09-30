<?php

namespace Tests\Unit\Services\FeriasPrevista;

use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\Ferias;
use App\Models\PeriodoAquisitivo;
use App\Models\User;
use App\Services\FeriasPrevista\FeriasPrevistaEditPayloadMapper;
use Tests\TestCase;

class FeriasPrevistaEditPayloadMapperTest extends TestCase
{
    public function test_map_retorna_apenas_campos_necessarios_do_modal(): void
    {
        $curriculo = new Curriculo(['nome' => 'Ana Silva']);
        $curriculo->id = 10;

        $feedback = new FeedbackCurriculo();
        $feedback->id = 11;
        $feedback->setRelation('Curriculo', $curriculo);

        $admissao = new Admissao([
            'centro_custo_id' => 5,
            'data_admissao' => '2024-01-01',
            'feedback_id' => 11,
            'filial' => false,
            'centro_custo_filial_id' => null,
        ]);
        $admissao->id = 12;
        $admissao->setRelation('Feedback', $feedback);

        $gestor = new User(['nome' => 'Carlos Gestor']);
        $gestor->id = 20;

        $solicitante = new User(['nome' => 'Maria Solicitante']);
        $solicitante->id = 21;

        $gestorAprovacao = new User(['nome' => 'Beatriz Aprovadora']);
        $gestorAprovacao->id = 30;

        $periodo = new PeriodoAquisitivo(['label' => '2024/2025']);
        $periodo->id = 3;

        $anexo = new Arquivo([
            'nome' => 'comprovante',
            'file' => 'comprovante.pdf',
            'disco' => 'local',
            'imagem' => false,
            'thumb' => null,
            'extensao' => '.pdf',
            'bytes' => 100,
            'chave' => '',
            'temporario' => false,
        ]);
        $anexo->id = 99;

        $item = new Ferias([
            'admissao_id' => 12,
            'periodo_aquisitivo_id' => 3,
            'data_saida' => '2026-02-01',
            'data_retorno' => '2026-02-15',
            'ultima_data' => '2026-12-01',
            'qnt_dias' => 15,
            'dias_saldo' => 15,
            'tem_faltas' => false,
            'qnt_faltas' => 0,
            'solicitante_id' => 21,
            'obs_solicitante' => 'Observação',
            'data_solicitacao' => '2026-01-20 10:00:00',
            'gestor_aprovacao_id' => 30,
            'gestor_id' => 20,
            'obs_gestor' => 'Ok',
            'status_aprovacao_gestor' => 'aprovado',
            'data_aprovacao_gestor' => '2026-01-21 10:00:00',
            'aprovacao_extra_id' => null,
            'status_aprovacao_extra' => null,
            'obs_aprovacao_extra' => null,
            'data_aprovacao_extra' => null,
            'rh_aprovacao_id' => null,
            'obs_rh' => null,
            'status_aprovacao_rh' => null,
            'data_aprovacao_rh' => null,
            'aprovado_via_script' => false,
            'abono_pecuniario' => false,
            'adiantamento_decimo_terceiro' => true,
            'empresa_id' => 1,
            'ferias_prevista_id' => null,
        ]);
        $item->id = 7;
        $item->setRelation('Admissao', $admissao);
        $item->setRelation('Gestor', $gestor);
        $item->setRelation('Solicitante', $solicitante);
        $item->setRelation('GestorAprovacao', $gestorAprovacao);
        $item->setRelation('AprovacaoExtra', null);
        $item->setRelation('RhAprovacao', null);
        $item->setRelation('PeriodoAquisitivo', $periodo);
        $item->setRelation('FeriasPrevista', null);
        $item->setRelation('Anexos', collect([$anexo]));

        $payload = (new FeriasPrevistaEditPayloadMapper())->map($item);

        $this->assertSame(7, $payload['id']);
        $this->assertSame(10, $payload['colaborador_id']);
        $this->assertSame('ANA SILVA', $payload['autocomplete_label_colaborador']);
        $this->assertSame('Carlos Gestor', $payload['autocomplete_label_gestor_modal']);
        $this->assertSame(5, $payload['centro_custo_id']);
        $this->assertFalse($payload['filial']);
        $this->assertNull($payload['centro_custo_filial_id']);
        $this->assertSame('2024/2025', $payload['periodo_label']);
        $this->assertSame('Maria Solicitante', $payload['solicitante']);
        $this->assertSame(21, $payload['solicitante_id']);
        $this->assertSame(['id' => 30, 'nome' => 'Beatriz Aprovadora'], $payload['gestor_aprovacao']);
        $this->assertNull($payload['rh_aprovacao']);
        $this->assertNull($payload['aprovacao_extra']);
        $this->assertSame([], $payload['anexosDel']);
        $this->assertCount(1, $payload['anexos']);
        $this->assertSame(99, $payload['anexos'][0]['id']);
        $this->assertArrayHasKey('urlDownload', $payload['anexos'][0]);
        $this->assertArrayNotHasKey('created_at', $payload);
        $this->assertArrayNotHasKey('Admissao', $payload);
        $this->assertArrayNotHasKey('Gestor', $payload);
    }
}
