<?php

namespace Tests\Unit\Services\ValorExtraPrevista;

use App\Models\Arquivo;
use App\Models\User;
use App\Models\ValorExtraPrevista;
use App\Services\ValorExtraPrevista\ValorExtraPrevistaEditPayloadMapper;
use Tests\TestCase;

class ValorExtraPrevistaEditPayloadMapperTest extends TestCase
{
    public function test_map_retorna_apenas_campos_necessarios_do_modal(): void
    {
        $colaborador = new User(['nome' => 'Ana Silva']);
        $colaborador->id = 10;

        $gestor = new User(['nome' => 'Carlos Gestor']);
        $gestor->id = 20;

        $aprovador = new User(['nome' => 'Beatriz Aprovadora']);
        $aprovador->id = 30;

        $extra = new User(['nome' => 'Diana Extra']);
        $extra->id = 40;

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

        $item = new ValorExtraPrevista([
            'colaborador_id' => 10,
            'centro_custo_id' => 5,
            'filial' => false,
            'centro_custo_filial_id' => null,
            'tipo' => 'Liderança',
            'periodo_dias' => 30,
            'gestor_id' => 20,
            'obs' => 'Observação',
            'user_aprovacao_id' => 30,
            'obs_aprovacao' => 'Ok',
            'data_aprovacao' => '02/02/2026',
            'status_aprovacao' => 'aprovado',
            'aprovacao_extra_id' => 40,
            'status_aprovacao_extra' => 'aprovado',
            'obs_aprovacao_extra' => null,
            'data_aprovacao_extra' => '03/02/2026',
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
        $item->setRelation('AprovacaoExtra', $extra);
        $item->setRelation('Anexos', collect([$anexo]));

        $payload = (new ValorExtraPrevistaEditPayloadMapper())->map($item);

        $this->assertSame(7, $payload['id']);
        $this->assertSame(10, $payload['colaborador_id']);
        $this->assertSame('Ana Silva', $payload['autocomplete_label_colaborador']);
        $this->assertSame('Carlos Gestor', $payload['autocomplete_label_gestor_modal']);
        $this->assertSame(['id' => 30, 'nome' => 'Beatriz Aprovadora'], $payload['user_aprovacao']);
        $this->assertNull($payload['rh_aprovacao']);
        $this->assertSame('Diana Extra', $payload['aprovacao_extra_nome']);
        $this->assertSame([], $payload['anexosDel']);
        $this->assertCount(1, $payload['anexos']);
        $this->assertSame(99, $payload['anexos'][0]['id']);
        $this->assertArrayHasKey('urlDownload', $payload['anexos'][0]);
        $this->assertArrayNotHasKey('created_at', $payload);
        $this->assertArrayNotHasKey('Colaborador', $payload);
    }
}
