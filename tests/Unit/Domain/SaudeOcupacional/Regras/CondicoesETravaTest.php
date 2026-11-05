<?php

namespace Tests\Unit\Domain\SaudeOcupacional\Regras;

use App\Domain\SaudeOcupacional\Regras\Condicoes\CatalogoCondicoes;
use App\Domain\SaudeOcupacional\Regras\Periodicidade;
use App\Domain\SaudeOcupacional\Regras\TravaLegalNr7;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CondicoesETravaTest extends TestCase
{
    public function test_normaliza_condicoes_validas(): void
    {
        $condicoes = (new CatalogoCondicoes())->normalizar([
            ['tipo' => 'idade_minima', 'valor' => '40'],
            ['tipo' => 'cargo', 'valor' => ['3', 3, 5, 'x']],
        ]);

        $this->assertSame([
            ['tipo' => 'idade_minima', 'valor' => 40],
            ['tipo' => 'cargo', 'valor' => [3, 5]],
        ], $condicoes);
    }

    #[DataProvider('condicoesInvalidas')]
    public function test_rejeita_condicoes_invalidas(array $condicoes): void
    {
        $this->expectException(ValidationException::class);

        (new CatalogoCondicoes())->normalizar($condicoes);
    }

    public static function condicoesInvalidas(): array
    {
        return [
            'vazia' => [[]],
            'tipo desconhecido' => [[['tipo' => 'sexo', 'valor' => 'F']]],
            'repetida' => [[['tipo' => 'idade_minima', 'valor' => 40], ['tipo' => 'idade_minima', 'valor' => 45]]],
            'idade fora da faixa' => [[['tipo' => 'idade_minima', 'valor' => 5]]],
            'minima maior que maxima' => [[['tipo' => 'idade_minima', 'valor' => 50], ['tipo' => 'idade_maxima', 'valor' => 40]]],
            'cargo sem itens' => [[['tipo' => 'cargo', 'valor' => []]]],
        ];
    }

    public function test_trava_nr7_limita_em_2_anos(): void
    {
        $trava = new TravaLegalNr7();

        $this->assertFalse($trava->excedeLimite(new Periodicidade('anos', 2)));
        $this->assertFalse($trava->excedeLimite(new Periodicidade('dias', 730)));
        $this->assertTrue($trava->excedeLimite(new Periodicidade('anos', 3)));
        $this->assertTrue($trava->excedeLimite(new Periodicidade('dias', 731)));

        $this->expectException(ValidationException::class);
        $trava->validar(new Periodicidade('anos', 3));
    }

    public function test_regras_de_mercado_para_a_tela(): void
    {
        $regras = (new TravaLegalNr7())->regrasMercado();

        $this->assertNotEmpty($regras);
        $this->assertSame('Limite de 2 anos', $regras[0]['titulo']);
        $this->assertTrue($regras[0]['ativa_no_sistema']);
    }
}
