<?php

namespace App\Domain\SaudeOcupacional\Regras;

use Illuminate\Validation\ValidationException;

/**
 * Regras de mercado (Padrão NR-7). Sempre ativas e não podem ser apagadas.
 *
 * Trava: nenhuma periodicidade pode passar de 2 anos (NR-7 7.5.8 II b, empregados não expostos).
 * Expostos a risco do PGR vigente: 1 ano, aplicado pelo motor (regraExpostoRisco).
 */
class TravaLegalNr7
{
    public const LIMITE_ANOS = 2;

    public const LIMITE_DIAS = 730;

    public const MENSAGEM_LIMITE = 'Pela NR-7, a validade do ASO não pode passar de 2 anos (730 dias).';

    public const ID_REGRA_EXPOSTO = -1;

    /**
     * NR-7 7.5.8 II a: exposto a risco do PGR → periódico anual. Aplicada pelo motor
     * em todos os tipos com vencimento quando a empresa tem PGR vigente.
     */
    public function regraExpostoRisco(): RegraPeriodicidade
    {
        return new RegraPeriodicidade(
            self::ID_REGRA_EXPOSTO,
            'NR-7: exposto a risco do PGR',
            [],
            [['tipo' => Condicoes\CondicaoExpostoRisco::TIPO, 'valor' => true]],
            new Periodicidade(Periodicidade::UNIDADE_ANOS, 1)
        );
    }

    public function excedeLimite(Periodicidade $periodicidade): bool
    {
        return $periodicidade->unidade === Periodicidade::UNIDADE_ANOS
            ? $periodicidade->valor > self::LIMITE_ANOS
            : $periodicidade->valor > self::LIMITE_DIAS;
    }

    /**
     * @throws ValidationException
     */
    public function validar(Periodicidade $periodicidade, string $campo = 'valor'): void
    {
        if ($this->excedeLimite($periodicidade)) {
            throw ValidationException::withMessages([$campo => self::MENSAGEM_LIMITE]);
        }
    }

    /**
     * Texto do quadro "Regras de mercado" na tela.
     *
     * @param  bool  $pgrVigente  empresa tem PGR vigente (liga a regra "exposto a risco")
     * @return list<array{titulo: string, texto: string, fonte: string, ativa_no_sistema: bool}>
     */
    public function regrasMercado(bool $pgrVigente = false): array
    {
        $exposto = $pgrVigente
            ? [
                'titulo' => 'Expostos a risco: anual',
                'texto' => 'Quem tem cargo com risco no PGR vigente faz o periódico a cada ano (ou menos). O sistema aplica sozinho, em todos os tipos de exame com vencimento.',
                'fonte' => 'NR-7 7.5.8 II a',
                'ativa_no_sistema' => true,
            ]
            : [
                'titulo' => 'Expostos a risco: anual',
                'texto' => 'Quem está exposto a risco do PGR faz o periódico a cada ano ou menos. Publique o PGR (riscos por cargo) para o sistema aplicar sozinho.',
                'fonte' => 'NR-7 7.5.8 II a',
                'ativa_no_sistema' => false,
            ];

        return [
            [
                'titulo' => 'Limite de 2 anos',
                'texto' => 'Nenhum ASO pode ter validade maior que 2 anos. O sistema bloqueia periodicidades acima disso.',
                'fonte' => 'NR-7 7.5.8 II b',
                'ativa_no_sistema' => true,
            ],
            $exposto,
            [
                'titulo' => 'Prazo menor é sempre permitido',
                'texto' => 'O médico do PCMSO pode reduzir prazos. Por isso as regras da empresa só encurtam a validade: entre o padrão e as regras que se aplicam, vale o menor prazo.',
                'fonte' => 'NR-7',
                'ativa_no_sistema' => true,
            ],
            [
                'titulo' => 'Idade não é exigência legal',
                'texto' => 'A regra por idade (menores de 18 e maiores de 45 anuais) saiu da NR-7 em 2020, mas ainda é prática comum. Use os modelos "Idade 40+" ou "Idade 45+" se o PCMSO adotar.',
                'fonte' => 'NR-7 (revisão 2020)',
                'ativa_no_sistema' => false,
            ],
            [
                'titulo' => 'Demissional sem vencimento',
                'texto' => 'O ASO demissional encerra o ciclo e não tem validade.',
                'fonte' => 'NR-7',
                'ativa_no_sistema' => true,
            ],
        ];
    }
}
