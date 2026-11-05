<?php

namespace App\Domain\SaudeOcupacional\Regras;

use App\Domain\SaudeOcupacional\Regras\Condicoes\CatalogoCondicoes;
use Carbon\CarbonImmutable;

/**
 * Calcula o vencimento do ASO aplicando as regras da empresa sobre o padrão do tipo.
 *
 * - Idade medida na data do vencimento padrão (quem faz 40 durante o ciclo já entra na regra 40+).
 * - Todas as condições da regra precisam ser verdadeiras.
 * - Entre o padrão e as regras que se aplicam, vale o vencimento mais cedo (mais protetivo).
 */
class MotorPeriodicidadeAso
{
    public function __construct(private readonly CatalogoCondicoes $catalogo = new CatalogoCondicoes())
    {
    }

    /**
     * @param  list<RegraPeriodicidade>  $regras  regras ativas do tipo de exame
     */
    public function calcular(
        CarbonImmutable $realizacao,
        Periodicidade $padrao,
        ?PerfilColaborador $perfil,
        array $regras
    ): ResultadoVencimento {
        // Referência fixa no vencimento padrão (não no vencimento já reduzido por outra regra).
        $referencia = $padrao->vencimentoPara($realizacao);
        $resultado = new ResultadoVencimento($referencia, $padrao);

        if (!$perfil || $regras === []) {
            return $resultado;
        }

        foreach ($regras as $regra) {
            if (!$this->regraAplica($regra, $perfil, $referencia)) {
                continue;
            }

            $vencimento = $regra->periodicidade->vencimentoPara($realizacao);
            if ($vencimento->lessThan($resultado->vencimento)) {
                $resultado = new ResultadoVencimento($vencimento, $regra->periodicidade, $regra);
            }
        }

        return $resultado;
    }

    /**
     * Detalhe por regra, para o simulador ("por que vence nesta data").
     *
     * @param  list<RegraPeriodicidade>  $regras
     * @param  array{cargos?: array<int, string>, riscos?: array<int, string>}  $rotulos
     * @return list<array{regra_id: int, nome: string, aplica: bool, periodicidade: string, condicoes: list<array{texto: string, ok: bool}>}>
     */
    public function explicar(
        CarbonImmutable $realizacao,
        Periodicidade $padrao,
        ?PerfilColaborador $perfil,
        array $regras,
        array $rotulos = []
    ): array {
        $referencia = $padrao->vencimentoPara($realizacao);

        return array_map(function (RegraPeriodicidade $regra) use ($perfil, $referencia, $rotulos) {
            $condicoes = array_map(function (array $condicao) use ($perfil, $referencia, $rotulos) {
                $estrategia = $this->catalogo->obter($condicao['tipo']);

                return [
                    'texto' => $estrategia->descrever($condicao['valor'], $rotulos),
                    'ok' => $perfil !== null && $estrategia->aplica($condicao['valor'], $perfil, $referencia),
                ];
            }, $regra->condicoes);

            return [
                'regra_id' => $regra->id,
                'nome' => $regra->nome,
                'aplica' => $condicoes !== [] && !in_array(false, array_column($condicoes, 'ok'), true),
                'periodicidade' => $regra->periodicidade->descricao(),
                'condicoes' => $condicoes,
            ];
        }, $regras);
    }

    private function regraAplica(RegraPeriodicidade $regra, PerfilColaborador $perfil, CarbonImmutable $referencia): bool
    {
        if ($regra->condicoes === []) {
            return false;
        }

        foreach ($regra->condicoes as $condicao) {
            $estrategia = $this->catalogo->obter((string) $condicao['tipo']);
            if (!$estrategia->aplica($condicao['valor'], $perfil, $referencia)) {
                return false;
            }
        }

        return true;
    }
}
