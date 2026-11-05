<?php

namespace Tests\Unit\Domain\SaudeOcupacional\Regras;

use App\Domain\SaudeOcupacional\Regras\MotorPeriodicidadeAso;
use App\Domain\SaudeOcupacional\Regras\PerfilColaborador;
use App\Domain\SaudeOcupacional\Regras\Periodicidade;
use App\Domain\SaudeOcupacional\Regras\RegraPeriodicidade;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class MotorPeriodicidadeAsoTest extends TestCase
{
    private MotorPeriodicidadeAso $motor;

    private Periodicidade $doisAnos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->motor = new MotorPeriodicidadeAso();
        $this->doisAnos = new Periodicidade('anos', 2);
    }

    public function test_periodicidade_anos_menos_um_dia_e_dias_exatos(): void
    {
        $realizacao = CarbonImmutable::parse('2024-03-15');

        $this->assertSame('2025-03-14', (new Periodicidade('anos', 1))->vencimentoPara($realizacao)->toDateString());
        $this->assertSame('2024-09-11', (new Periodicidade('dias', 180))->vencimentoPara($realizacao)->toDateString());
        $this->assertSame('1 ano', (new Periodicidade('anos', 1))->descricao());
        $this->assertSame('180 dias', (new Periodicidade('dias', 180))->descricao());
    }

    public function test_sem_regras_usa_padrao(): void
    {
        $r = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), $this->doisAnos, $this->perfil('1980-01-01'), []);

        $this->assertSame('2028-01-09', $r->vencimento->toDateString());
        $this->assertNull($r->regra);
        $this->assertSame('Padrão do tipo de exame: 2 anos', $r->motivo());
    }

    public function test_quem_faz_40_durante_o_ciclo_ja_entra_na_regra(): void
    {
        // 39 anos na realização, 40 antes do vencimento padrão (2028-01-09).
        $perfil = $this->perfil('1987-06-01');
        $r = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), $this->doisAnos, $perfil, [$this->regraIdade(40)]);

        $this->assertSame('2027-01-09', $r->vencimento->toDateString());
        $this->assertSame('Regra "Idade 40+": 1 ano', $r->motivo());
    }

    public function test_quem_ainda_nao_tem_40_no_vencimento_padrao_fica_no_padrao(): void
    {
        $perfil = $this->perfil('1990-06-01'); // 37 em 2028
        $r = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), $this->doisAnos, $perfil, [$this->regraIdade(40)]);

        $this->assertSame('2028-01-09', $r->vencimento->toDateString());
    }

    public function test_sem_data_de_nascimento_regra_de_idade_nao_se_aplica(): void
    {
        $perfil = new PerfilColaborador(1, null, 5);
        $r = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), $this->doisAnos, $perfil, [$this->regraIdade(40)]);

        $this->assertNull($r->regra);
    }

    public function test_vale_o_menor_prazo_entre_regras(): void
    {
        $perfil = $this->perfil('1970-01-01', vagaId: 7);
        $regras = [
            $this->regraIdade(40), // 1 ano
            new RegraPeriodicidade(2, 'Motorista 6 meses', [1], [['tipo' => 'cargo', 'valor' => [7]]], new Periodicidade('dias', 180)),
        ];

        $r = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), $this->doisAnos, $perfil, $regras);

        $this->assertSame('2026-07-09', $r->vencimento->toDateString());
        $this->assertSame(2, $r->regra->id);
    }

    public function test_regra_nunca_alonga_o_padrao(): void
    {
        $perfil = $this->perfil('1970-01-01');
        $regraLonga = new RegraPeriodicidade(3, 'Longa', [1], [['tipo' => 'idade_minima', 'valor' => 40]], new Periodicidade('anos', 2));

        $r = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), new Periodicidade('anos', 1), $perfil, [$regraLonga]);

        $this->assertSame('2027-01-09', $r->vencimento->toDateString());
        $this->assertNull($r->regra);
    }

    public function test_todas_as_condicoes_precisam_valer(): void
    {
        $regra = new RegraPeriodicidade(4, '40+ motorista', [1], [
            ['tipo' => 'idade_minima', 'valor' => 40],
            ['tipo' => 'cargo', 'valor' => [7]],
        ], new Periodicidade('anos', 1));

        $semCargo = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), $this->doisAnos, $this->perfil('1970-01-01', vagaId: 8), [$regra]);
        $comCargo = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), $this->doisAnos, $this->perfil('1970-01-01', vagaId: 7), [$regra]);

        $this->assertNull($semCargo->regra);
        $this->assertSame(4, $comCargo->regra->id);
    }

    public function test_menores_de_18_com_idade_maxima(): void
    {
        $regra = new RegraPeriodicidade(5, 'Menores de 18', [1], [['tipo' => 'idade_maxima', 'valor' => 17]], new Periodicidade('anos', 1));

        // 16 na realização, 17 no vencimento de 1 ano... mas referência é o padrão (2 anos): 18 → não aplica.
        $quase18 = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), $this->doisAnos, $this->perfil('2010-01-01'), [$regra]);
        $menor = $this->motor->calcular(CarbonImmutable::parse('2026-01-10'), $this->doisAnos, $this->perfil('2011-06-01'), [$regra]);

        $this->assertNull($quase18->regra);
        $this->assertSame(5, $menor->regra->id);
    }

    public function test_explicar_mostra_cada_condicao(): void
    {
        $regra = new RegraPeriodicidade(4, '40+ motorista', [1], [
            ['tipo' => 'idade_minima', 'valor' => 40],
            ['tipo' => 'cargo', 'valor' => [7]],
        ], new Periodicidade('anos', 1));

        $detalhe = $this->motor->explicar(
            CarbonImmutable::parse('2026-01-10'),
            $this->doisAnos,
            $this->perfil('1970-01-01', vagaId: 8),
            [$regra],
            ['cargos' => [7 => 'MOTORISTA']]
        );

        $this->assertFalse($detalhe[0]['aplica']);
        $this->assertSame(
            [['texto' => 'idade a partir de 40 anos', 'ok' => true], ['texto' => 'cargo: MOTORISTA', 'ok' => false]],
            $detalhe[0]['condicoes']
        );
    }

    private function perfil(string $nascimento, ?int $vagaId = null): PerfilColaborador
    {
        return new PerfilColaborador(1, CarbonImmutable::parse($nascimento), $vagaId);
    }

    private function regraIdade(int $idade): RegraPeriodicidade
    {
        return new RegraPeriodicidade(1, "Idade {$idade}+", [1], [['tipo' => 'idade_minima', 'valor' => $idade]], new Periodicidade('anos', 1));
    }
}
