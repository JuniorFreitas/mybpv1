<?php

namespace Tests\Unit\Services\AdmissoesPrevista;

use App\Models\Admissao;
use App\Models\AdmissoesPrevista;
use App\Models\Arquivo;
use App\Models\User;
use App\Models\Vaga;
use App\Services\AdmissoesPrevista\AdmissoesPrevistaEditPayloadMapper;
use Tests\TestCase;

class AdmissoesPrevistaEditPayloadMapperTest extends TestCase
{
    public function test_map_retorna_apenas_campos_necessarios_do_modal(): void
    {
        $gestor = new User(['nome' => 'Carlos Gestor']);
        $gestor->id = 20;

        $aprovador = new User(['nome' => 'Beatriz Aprovadora']);
        $aprovador->id = 30;

        $cargo = new Vaga(['nome' => 'Analista']);
        $cargo->id = 8;

        $anexo = new Arquivo([
            'nome' => 'contrato',
            'file' => 'contrato.pdf',
            'disco' => 'local',
            'imagem' => false,
            'thumb' => null,
            'extensao' => '.pdf',
            'bytes' => 100,
            'chave' => '',
            'temporario' => false,
        ]);
        $anexo->id = 99;

        $item = new AdmissoesPrevista([
            'nome_pessoa' => 'João Silva',
            'centro_custo_id' => 5,
            'filial' => false,
            'centro_custo_filial_id' => null,
            'tipo_contrato' => 'Fixo',
            'cargo_id' => 8,
            'data_admissao' => '01/03/2026',
            'salario' => 2500.0,
            'gestor_id' => 20,
            'obs' => 'Observação',
            'user_aprovacao_id' => 30,
            'obs_aprovacao' => 'Ok',
            'data_aprovacao' => '02/03/2026',
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
        $item->setRelation('GestorAprovacao', $gestor);
        $item->setRelation('UserAprovacao', $aprovador);
        $item->setRelation('RhAprovacao', null);
        $item->setRelation('UserAprovacaoExtra', null);
        $item->setRelation('Cargo', $cargo);
        $item->setRelation('Anexos', collect([$anexo]));

        $payload = (new AdmissoesPrevistaEditPayloadMapper())->map($item);

        $this->assertSame(7, $payload['id']);
        $this->assertSame('João Silva', $payload['nome_pessoa']);
        $this->assertSame(Admissao::TIPO_ADMISSAO_FIXO, $payload['tipo_contrato']);
        $this->assertSame('Analista', $payload['autocomplete_label_cargo']);
        $this->assertSame('Carlos Gestor', $payload['autocomplete_label_gestor_modal']);
        $this->assertSame(['id' => 30, 'nome' => 'Beatriz Aprovadora'], $payload['user_aprovacao']);
        $this->assertSame('Aprovado', $payload['status_aprovacao_gestor']);
        $this->assertNull($payload['rh_aprovacao']);
        $this->assertSame([], $payload['anexosDel']);
        $this->assertCount(1, $payload['anexos']);
        $this->assertSame(99, $payload['anexos'][0]['id']);
        $this->assertArrayHasKey('urlDownload', $payload['anexos'][0]);
        $this->assertArrayNotHasKey('Cargo', $payload);
        $this->assertArrayNotHasKey('created_at', $payload);
    }
}
