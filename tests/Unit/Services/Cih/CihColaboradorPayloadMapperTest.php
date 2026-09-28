<?php

namespace Tests\Unit\Services\Cih;

use App\Models\Admissao;
use App\Models\CentroCusto;
use App\Models\Curriculo;
use App\Models\Demissao;
use App\Models\FeedbackCurriculo;
use App\Services\Cih\CihColaboradorPayloadMapper;
use Tests\TestCase;

class CihColaboradorPayloadMapperTest extends TestCase
{
    public function test_map_for_modal_retorna_apenas_campos_da_tabela(): void
    {
        $curriculo = new Curriculo(['nome' => 'Ana Silva']);
        $curriculo->id = 10;

        $centro = new CentroCusto(['label' => 'CC Operações']);
        $centro->id = 5;

        $admissao = new Admissao(['cargo' => 'Analista', 'centro_custo_id' => 5]);
        $admissao->id = 20;
        $admissao->setRelation('CentroCusto', $centro);

        $feedback = new FeedbackCurriculo();
        $feedback->id = 100;
        $feedback->setRelation('Curriculo', $curriculo);
        $feedback->setRelation('Admissao', $admissao);
        $feedback->setRelation('Demissao', null);

        $payload = (new CihColaboradorPayloadMapper())->mapForModal(collect([$feedback]));

        $this->assertSame([
            [
                'id' => 100,
                'nome' => 'ANA SILVA',
                'cargo' => 'ANALISTA',
                'centro_custo' => 'CC Operações',
                'centro_custo_id' => 5,
                'demitido' => false,
            ],
        ], $payload);
    }

    public function test_map_for_modal_marca_demitido_no_nome(): void
    {
        $curriculo = new Curriculo(['nome' => 'Bruno']);
        $curriculo->id = 11;

        $feedback = new FeedbackCurriculo();
        $feedback->id = 101;
        $feedback->setRelation('Curriculo', $curriculo);
        $feedback->setRelation('Admissao', null);
        $feedback->setRelation('Demissao', new Demissao());

        $payload = (new CihColaboradorPayloadMapper())->mapForModal(collect([$feedback]));

        $this->assertSame('BRUNO - Demitido(a)', $payload[0]['nome']);
        $this->assertTrue($payload[0]['demitido']);
        $this->assertSame('', $payload[0]['cargo']);
        $this->assertNull($payload[0]['centro_custo_id']);
    }
}
