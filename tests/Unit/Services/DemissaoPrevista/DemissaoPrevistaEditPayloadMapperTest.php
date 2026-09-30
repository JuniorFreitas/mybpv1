<?php

namespace Tests\Unit\Services\DemissaoPrevista;

use App\Models\Arquivo;
use App\Models\DemissaoPrevista;
use App\Models\User;
use App\Services\DemissaoPrevista\DemissaoPrevistaEditPayloadMapper;
use Tests\TestCase;

class DemissaoPrevistaEditPayloadMapperTest extends TestCase
{
    public function test_map_retorna_apenas_campos_necessarios_do_modal(): void
    {
        $colaborador = new User(['nome' => 'Ana Silva']);
        $colaborador->id = 10;

        $gestor = new User(['nome' => 'Carlos Gestor']);
        $gestor->id = 20;

        $aprovador = new User(['nome' => 'Beatriz Aprovadora']);
        $aprovador->id = 30;

        $anexo = new Arquivo([
            'nome' => 'aviso',
            'file' => 'aviso.pdf',
            'disco' => 'local',
            'imagem' => false,
            'thumb' => null,
            'extensao' => '.pdf',
            'bytes' => 100,
            'chave' => '',
            'temporario' => false,
        ]);
        $anexo->id = 99;

        $item = new DemissaoPrevista([
            'colaborador_id' => 10,
            'centro_custo_id' => 5,
            'filial' => false,
            'centro_custo_filial_id' => null,
            'data_demissao' => '01/02/2026',
            'tipo_aviso' => 'Trabalhado',
            'valor' => '1.500,00',
            'gestor_id' => 20,
            'obs' => 'Observação',
            'user_aprovacao_id' => 30,
            'obs_aprovacao' => 'Ok',
            'data_aprovacao' => '02/02/2026',
            'status_aprovacao' => 'aprovado',
            'aprovacao_extra_id' => null,
            'status_aprovacao_extra' => null,
            'obs_aprovacao_extra' => null,
            'data_aprovacao_extra' => null,
            'rh_aprovacao_id' => null,
            'obs_rh' => null,
            'status_aprovacao_rh' => null,
            'data_aprovacao_rh' => null,
            'aprovado_via_script' => false,
            'empresa_id' => 1,
        ]);
        $item->id = 7;
        $item->setRelation('Colaborador', $colaborador);
        $item->setRelation('GestorAprovacao', $gestor);
        $item->setRelation('UserAprovacao', $aprovador);
        $item->setRelation('RhAprovacao', null);
        $item->setRelation('AprovacaoExtra', null);
        $item->setRelation('Anexos', collect([$anexo]));

        $payload = (new DemissaoPrevistaEditPayloadMapper())->map($item);

        $this->assertSame(7, $payload['id']);
        $this->assertSame(10, $payload['colaborador_id']);
        $this->assertSame('Ana Silva', $payload['autocomplete_label_colaborador']);
        $this->assertSame('Carlos Gestor', $payload['autocomplete_label_gestor_modal']);
        $this->assertSame('01/02/2026', $payload['data_demissao']);
        $this->assertSame('02/02/2026', $payload['data_aprovacao']);
        $this->assertSame(['id' => 30, 'nome' => 'Beatriz Aprovadora'], $payload['user_aprovacao']);
        $this->assertNull($payload['rh_aprovacao']);
        $this->assertNull($payload['aprovacao_extra']);
        $this->assertSame([], $payload['anexosDel']);
        $this->assertCount(1, $payload['anexos']);
        $this->assertSame(99, $payload['anexos'][0]['id']);
        $this->assertSame('aviso', $payload['anexos'][0]['nome']);
        $this->assertArrayHasKey('urlDownload', $payload['anexos'][0]);
        $this->assertArrayNotHasKey('solicitante', $payload);
        $this->assertArrayNotHasKey('created_at', $payload);
        $this->assertArrayNotHasKey('Colaborador', $payload);
    }

    public function test_map_formata_data_demissao_em_d_m_y_quando_carbon(): void
    {
        $item = new DemissaoPrevista([
            'colaborador_id' => 1,
            'centro_custo_id' => 1,
            'data_demissao' => '15/03/2026',
            'tipo_aviso' => 'NA',
            'valor' => '0,00',
            'gestor_id' => 1,
            'empresa_id' => 1,
        ]);
        $item->id = 1;
        $item->setRelation('Colaborador', null);
        $item->setRelation('GestorAprovacao', null);
        $item->setRelation('UserAprovacao', null);
        $item->setRelation('RhAprovacao', null);
        $item->setRelation('AprovacaoExtra', null);
        $item->setRelation('Anexos', collect());

        $payload = (new DemissaoPrevistaEditPayloadMapper())->map($item);

        $this->assertSame('15/03/2026', $payload['data_demissao']);
        $this->assertIsString($payload['data_demissao']);
        $this->assertDoesNotMatchRegularExpression('/T|\d{4}-\d{2}-\d{2}/', $payload['data_demissao']);
    }
}
