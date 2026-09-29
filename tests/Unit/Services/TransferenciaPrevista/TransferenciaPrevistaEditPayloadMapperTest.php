<?php

namespace Tests\Unit\Services\TransferenciaPrevista;

use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\TransferenciaPrevista;
use App\Models\User;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaEditPayloadMapper;
use Tests\TestCase;

class TransferenciaPrevistaEditPayloadMapperTest extends TestCase
{
    public function test_map_retorna_apenas_campos_necessarios_do_modal(): void
    {
        $colaborador = new Curriculo(['nome' => 'Ana Silva']);
        $colaborador->id = 10;

        $feedback = new FeedbackCurriculo(['curriculo_id' => 10]);
        $feedback->id = 5;

        $admissao = new Admissao(['centro_custo_id' => 8, 'feedback_id' => 5]);
        $admissao->id = 3;
        $feedback->setRelation('Admissao', $admissao);
        $colaborador->setRelation('FeedBack', $feedback);

        $gestorOrigem = new User(['nome' => 'Gestor Origem']);
        $gestorOrigem->id = 20;

        $gestorDestino = new User(['nome' => 'Gestor Destino']);
        $gestorDestino->id = 21;

        $aprovador = new User(['nome' => 'Beatriz Aprovadora']);
        $aprovador->id = 30;

        $anexo = new Arquivo([
            'nome' => 'doc',
            'file' => 'doc.pdf',
            'disco' => 'local',
            'imagem' => false,
            'thumb' => null,
            'extensao' => '.pdf',
            'bytes' => 100,
            'chave' => '',
            'temporario' => false,
        ]);
        $anexo->id = 99;

        $item = new TransferenciaPrevista([
            'colaborador_id' => 10,
            'centro_custo_origem_id' => 1,
            'centro_custo_destino_id' => 2,
            'data_transferencia' => '01/03/2026',
            'obs' => 'Observação',
            'gestor_id' => 20,
            'gestor_destino_id' => 21,
            'modo_aprovacao' => 'padrao',
            'exige_aprovacao_gestor_destino' => true,
            'fluxo_gestores_automatico' => true,
            'status_aprovacao' => '',
            'empresa_id' => 1,
        ]);
        $item->id = 7;
        $item->setRelation('Colaborador', $colaborador);
        $item->setRelation('GestorOrigem', $gestorOrigem);
        $item->setRelation('GestorDestino', $gestorDestino);
        $item->setRelation('GestorAprovacaoUnico', null);
        $item->setRelation('QuemAprovouGestorDestino', null);
        $item->setRelation('QuemAprovouGestorUnico', null);
        $item->setRelation('UserAprovacao', $aprovador);
        $item->setRelation('AprovacaoExtra', null);
        $item->setRelation('RhAprovacao', null);
        $item->setRelation('Anexos', collect([$anexo]));

        $payload = (new TransferenciaPrevistaEditPayloadMapper())->map($item);

        $this->assertSame(7, $payload['id']);
        $this->assertSame(10, $payload['colaborador_id']);
        $this->assertSame('ANA SILVA', $payload['autocomplete_label_colaborador']);
        $this->assertSame(8, $payload['centro_custo_id']);
        $this->assertSame('Gestor Origem', $payload['label_gestor_origem']);
        $this->assertSame('Gestor Destino', $payload['label_gestor_destino']);
        $this->assertSame(['id' => 30, 'nome' => 'Beatriz Aprovadora'], $payload['user_aprovacao']);
        $this->assertSame([], $payload['anexosDel']);
        $this->assertCount(1, $payload['anexos']);
        $this->assertSame(99, $payload['anexos'][0]['id']);
        $this->assertArrayHasKey('urlDownload', $payload['anexos'][0]);
        $this->assertArrayNotHasKey('tem_aprovacao_extra', $payload);
        $this->assertArrayNotHasKey('created_at', $payload);
        $this->assertArrayNotHasKey('Colaborador', $payload);
    }
}
