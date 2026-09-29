<?php

namespace Tests\Unit\Services\MudaCargoPrevista;

use App\Models\Arquivo;
use App\Models\MudaCargoPrevista;
use App\Models\User;
use App\Models\Vaga;
use App\Services\MudaCargoPrevista\MudaCargoPrevistaEditPayloadMapper;
use Tests\TestCase;

class MudaCargoPrevistaEditPayloadMapperTest extends TestCase
{
    public function test_map_retorna_apenas_campos_necessarios_do_modal(): void
    {
        $colaborador = new User(['nome' => 'Ana Silva']);
        $colaborador->id = 10;

        $gestor = new User(['nome' => 'Carlos Gestor']);
        $gestor->id = 20;

        $cargoAnterior = new Vaga(['nome' => 'Auxiliar']);
        $cargoAnterior->id = 30;

        $novoCargo = new Vaga(['nome' => 'Analista']);
        $novoCargo->id = 40;

        $anexo = new Arquivo([
            'nome' => 'termo',
            'file' => 'termo.pdf',
            'disco' => 'local',
            'imagem' => false,
            'thumb' => null,
            'extensao' => '.pdf',
            'bytes' => 100,
            'chave' => '',
            'temporario' => false,
        ]);
        $anexo->id = 99;

        $item = new MudaCargoPrevista([
            'colaborador_id' => 10,
            'centro_custo_id' => 5,
            'cargo_anterior_id' => 30,
            'salario_anterior' => 2000,
            'novo_cargo_id' => 40,
            'novo_salario' => 2500,
            'gestor_id' => 20,
            'obs' => 'Observação',
            'status_aprovacao' => 'aprovado',
            'aprovacao_extra_id' => null,
            'status_aprovacao_extra' => null,
            'obs_aprovacao_extra' => null,
            'data_aprovacao_extra' => null,
            'empresa_id' => 1,
        ]);
        $item->id = 7;
        $item->setRelation('Colaborador', $colaborador);
        $item->setRelation('GestorAprovacao', $gestor);
        $item->setRelation('CargoAnterior', $cargoAnterior);
        $item->setRelation('NovoCargo', $novoCargo);
        $item->setRelation('UserAprovacao', null);
        $item->setRelation('AprovacaoExtra', null);
        $item->setRelation('Anexos', collect([$anexo]));

        $payload = (new MudaCargoPrevistaEditPayloadMapper())->map($item);

        $this->assertSame(7, $payload['id']);
        $this->assertSame(10, $payload['colaborador_id']);
        $this->assertSame('Ana Silva', $payload['autocomplete_label_colaborador']);
        $this->assertSame('Carlos Gestor', $payload['autocomplete_label_gestor_modal']);
        $this->assertSame('Auxiliar', $payload['autocomplete_label_cargoanterior']);
        $this->assertSame('Analista', $payload['autocomplete_label_novo_cargo']);
        $this->assertSame([], $payload['anexosDel']);
        $this->assertCount(1, $payload['anexos']);
        $this->assertSame(99, $payload['anexos'][0]['id']);
        $this->assertArrayNotHasKey('Colaborador', $payload);
        $this->assertArrayNotHasKey('created_at', $payload);
    }
}
