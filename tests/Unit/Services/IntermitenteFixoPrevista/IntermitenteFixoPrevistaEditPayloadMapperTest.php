<?php

namespace Tests\Unit\Services\IntermitenteFixoPrevista;

use App\Models\Arquivo;
use App\Models\IntermitenteFixoPrevista;
use App\Models\User;
use App\Models\Vaga;
use App\Models\VagasAbertas;
use App\Services\IntermitenteFixoPrevista\IntermitenteFixoPrevistaEditPayloadMapper;
use Tests\TestCase;

class IntermitenteFixoPrevistaEditPayloadMapperTest extends TestCase
{
    public function test_map_retorna_apenas_campos_necessarios_do_modal(): void
    {
        $colaborador = new User(['nome' => 'Ana Silva']);
        $colaborador->id = 10;

        $gestor = new User(['nome' => 'Carlos Gestor']);
        $gestor->id = 20;

        $aprovador = new User(['nome' => 'Beatriz Aprovadora']);
        $aprovador->id = 30;

        $cargoAnterior = new Vaga(['nome' => 'Cargo Antigo']);
        $cargoAnterior->id = 40;

        $novoCargo = new Vaga(['nome' => 'Cargo Novo']);
        $novoCargo->id = 41;

        $vagaAnterior = new VagasAbertas(['titulo' => 'Vaga Anterior']);
        $vagaAnterior->id = 50;

        $vagaNova = new VagasAbertas(['titulo' => 'Vaga Nova']);
        $vagaNova->id = 51;

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

        $item = new IntermitenteFixoPrevista([
            'colaborador_id' => 10,
            'centro_custo_id' => 5,
            'filial' => false,
            'centro_custo_filial_id' => null,
            'cargo_anterior_id' => 40,
            'salario_anterior' => 1500.0,
            'novo_cargo_id' => 41,
            'novo_salario' => 2000.0,
            'gestor_id' => 20,
            'motivos' => 'Motivo teste',
            'user_aprovacao_id' => 30,
            'obs_aprovacao' => 'Ok',
            'status_aprovacao' => 'aprovado',
            'aprovacao_extra_id' => null,
            'status_aprovacao_extra' => null,
            'obs_aprovacao_extra' => null,
            'rh_aprovacao_id' => null,
            'obs_rh' => null,
            'status_aprovacao_rh' => null,
            'anterior_vaga_aberta_id' => 50,
            'nova_vaga_aberta_id' => 51,
            'area_etiqueta_id' => null,
            'aprovado_via_script' => false,
            'empresa_id' => 1,
        ]);
        $item->id = 7;
        $item->setRelation('Colaborador', $colaborador);
        $item->setRelation('GestorAprovacao', $gestor);
        $item->setRelation('UserAprovacao', $aprovador);
        $item->setRelation('RhAprovacao', null);
        $item->setRelation('CargoAnterior', $cargoAnterior);
        $item->setRelation('NovoCargo', $novoCargo);
        $item->setRelation('VagaAbertaAnterior', $vagaAnterior);
        $item->setRelation('VagaAbertaNova', $vagaNova);
        $item->setRelation('Anexos', collect([$anexo]));

        $payload = (new IntermitenteFixoPrevistaEditPayloadMapper())->map($item);

        $this->assertSame(7, $payload['id']);
        $this->assertSame(10, $payload['colaborador_id']);
        $this->assertSame('Ana Silva', $payload['autocomplete_label_colaborador']);
        $this->assertSame('Cargo Antigo', $payload['autocomplete_label_cargoanterior']);
        $this->assertSame('Vaga Anterior', $payload['autocomplete_label_vaga_anterior']);
        $this->assertSame('Vaga Nova', $payload['autocomplete_label_vaga_nova']);
        $this->assertSame('Carlos Gestor', $payload['autocomplete_label_gestor_modal']);
        $this->assertSame(['id' => 30, 'nome' => 'Beatriz Aprovadora'], $payload['user_aprovacao']);
        $this->assertNull($payload['rh_aprovacao']);
        $this->assertSame([], $payload['anexosDel']);
        $this->assertCount(1, $payload['anexos']);
        $this->assertSame(99, $payload['anexos'][0]['id']);
        $this->assertArrayHasKey('urlDownload', $payload['anexos'][0]);
        $this->assertArrayNotHasKey('Colaborador', $payload);
        $this->assertArrayNotHasKey('created_at', $payload);
    }
}
