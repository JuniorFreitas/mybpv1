<?php

namespace Tests\Unit\Services\ControleExames;

use App\Services\ControleExames\RestricaoOrdemExameDemitido;
use Tests\TestCase;

class RestricaoOrdemExameDemitidoTest extends TestCase
{
    private RestricaoOrdemExameDemitido $restricao;

    protected function setUp(): void
    {
        parent::setUp();
        $this->restricao = new RestricaoOrdemExameDemitido();
    }

    public function test_demitido_so_mantem_tipo_demissional(): void
    {
        $tipos = collect([
            (object) ['id' => 1, 'label' => 'Admissional'],
            (object) ['id' => 2, 'label' => 'Periódico'],
            (object) ['id' => 6, 'label' => 'Demissional'],
        ]);

        $filtrados = $this->restricao->filtrarTipos($tipos, true);

        $this->assertCount(1, $filtrados);
        $this->assertSame('Demissional', $filtrados->first()->label);
    }

    public function test_colaborador_ativo_mantem_todos_os_tipos(): void
    {
        $tipos = collect([
            (object) ['id' => 1, 'label' => 'Admissional'],
            (object) ['id' => 6, 'label' => 'Demissional'],
        ]);

        $this->assertCount(2, $this->restricao->filtrarTipos($tipos, false));
    }

    public function test_reconhece_label_demissional_sem_diferenciar_maiusculas(): void
    {
        $this->assertTrue($this->restricao->ehDemissional('Demissional'));
        $this->assertTrue($this->restricao->ehDemissional(' demissional '));
        $this->assertFalse($this->restricao->ehDemissional('Admissional'));
        $this->assertFalse($this->restricao->ehDemissional(null));
    }
}
