<?php

namespace Tests\Unit\Domain\Ajuda;

use App\Domain\Ajuda\ManualTrecho;
use App\Domain\Ajuda\Services\BuscadorTrechosManual;
use App\Domain\Ajuda\Services\GlossarioRh;
use App\Domain\Ajuda\Services\MontadorContextoManual;
use App\Domain\Ajuda\Services\TokenizadorManual;
use PHPUnit\Framework\TestCase;

/**
 * Mede a busca do Assistente do Manual contra a base real, sem chamar o provedor de IA.
 *
 * tests/Fixtures/assistente-manual-perguntas.json traz perguntas no vocabulário do usuário
 * e a seção (âncora) do manual que deveria respondê-las. Ao mexer na busca, no glossário ou
 * no manual, este teste mostra se a qualidade caiu. Inclua ali as perguntas reais que o
 * assistente errar (sem dados pessoais).
 */
class BuscaManualAvaliacaoTest extends TestCase
{
    private const BASE = __DIR__.'/../../../../docs/manual-completo/base-conhecimento/chunks.jsonl';

    private const PERGUNTAS = __DIR__.'/../../../Fixtures/assistente-manual-perguntas.json';

    /** Mínimo de perguntas com a seção certa entre as 3 primeiras / em primeiro lugar. */
    private const MINIMO_TOP3 = 0.95;

    private const MINIMO_TOP1 = 0.70;

    public function testBuscaEncontraASecaoEsperada(): void
    {
        $trechos = $this->trechos();
        $buscador = $this->buscador();
        $casos = json_decode((string) file_get_contents(self::PERGUNTAS), true, flags: JSON_THROW_ON_ERROR);

        $top1 = 0;
        $top3 = 0;
        $falhas = [];
        foreach ($casos as $caso) {
            $ancoras = array_values(array_unique(array_map(
                static fn (ManualTrecho $trecho) => $trecho->ancora,
                $buscador->buscar($caso['pergunta'], $trechos, 6),
            )));

            $top1 += in_array($ancoras[0] ?? '', $caso['esperado'], true) ? 1 : 0;
            if (array_intersect(array_slice($ancoras, 0, 3), $caso['esperado']) !== []) {
                $top3++;
            } else {
                $falhas[] = sprintf('"%s" → %s (esperado: %s)', $caso['pergunta'], implode(', ', array_slice($ancoras, 0, 3)) ?: 'nada', implode('/', $caso['esperado']));
            }
        }

        $total = count($casos);
        $resumo = sprintf("top1 %d/%d, top3 %d/%d\n%s", $top1, $total, $top3, $total, implode("\n", $falhas));

        $this->assertGreaterThanOrEqual(self::MINIMO_TOP3, $top3 / $total, $resumo);
        $this->assertGreaterThanOrEqual(self::MINIMO_TOP1, $top1 / $total, $resumo);
    }

    public function testPerguntaForaDoAssuntoNaoEncontraNada(): void
    {
        $trechos = $this->trechos();

        foreach (['qual a capital da França?', 'como fazer um bolo de cenoura', 'oi'] as $pergunta) {
            $this->assertSame([], $this->buscador()->buscar($pergunta, $trechos), $pergunta);
        }
    }

    public function testFuncionarioEColaboradorDaoOMesmoResultado(): void
    {
        $trechos = $this->trechos();
        $ids = fn (string $pergunta) => array_map(
            static fn (ManualTrecho $trecho) => $trecho->id,
            $this->buscador()->buscar($pergunta, $trechos, 6),
        );

        $this->assertSame($ids('como demitir um colaborador'), $ids('como demitir um funcionário'));
        $this->assertSame($ids('férias do colaborador'), $ids('férias dos empregados'));
    }

    public function testContextoIncluiAsSubsecoesDaTelaMaisRelevante(): void
    {
        $trechos = $this->trechos();
        $encontrados = $this->buscador()->buscar('como aprovar uma requisição de vaga', $trechos, 6);

        $contexto = (new MontadorContextoManual())->montar($encontrados, $trechos);
        $ids = array_map(static fn (ManualTrecho $trecho) => $trecho->id, $contexto);

        $daSecao = array_filter($trechos, static fn (ManualTrecho $trecho) => $trecho->ancora === 'planejamento-2');
        foreach ($daSecao as $trecho) {
            $this->assertContains($trecho->id, $ids);
        }
        $this->assertSame('planejamento-2', $contexto[0]->ancora);
        $this->assertLessThanOrEqual(16000 + 4000, array_sum(array_map(static fn (ManualTrecho $trecho) => strlen($trecho->texto), $contexto)));
    }

    public function testTermosForaDoManualNaoSaoDevolvidos(): void
    {
        $termos = $this->buscador()->termosNoManual('como demitir o Fulano Beltrano cpf 12345678901', $this->trechos());

        $this->assertContains('demit', $termos['conhecidos']);
        $this->assertNotContains('fulan', $termos['conhecidos']);
        $this->assertNotContains('12345678901', $termos['conhecidos']);
        $this->assertGreaterThanOrEqual(3, $termos['desconhecidos']);
    }

    private function buscador(): BuscadorTrechosManual
    {
        $tokenizador = new TokenizadorManual();

        return new BuscadorTrechosManual($tokenizador, new GlossarioRh($tokenizador));
    }

    /**
     * @return array<int, ManualTrecho>
     */
    private function trechos(): array
    {
        static $trechos = null;

        return $trechos ??= array_map(
            static fn (string $linha) => ManualTrecho::fromArray(json_decode($linha, true)),
            file(self::BASE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
        );
    }
}
