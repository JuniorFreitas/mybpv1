<?php

namespace App\Services\ControleExames;

use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\FeedbackCurriculo;
use App\Models\Vaga;
use App\Models\VagasAbertas;
use App\Services\Planejamento\Movimentacao\LotacaoLabelResolver;
use Illuminate\Http\JsonResponse;

class ColaboradorExameIdentificacao
{
    public const NAO_INFORMADO = 'Não informado';

    /**
     * @return array{
     *     lotacao: string,
     *     lotacao_nome: string,
     *     lotacao_cnpj: string,
     *     centro_custo: string,
     *     cargo: string,
     *     possui_centro_custo: bool,
     *     possui_cargo: bool
     * }
     */
    public function paraFeedback(?FeedbackCurriculo $feedback): array
    {
        if (! $feedback) {
            return $this->apresentar(null, null, null, null);
        }

        $feedback->loadMissing(['Admissao', 'ResultadoIntegrado']);
        $admissao = $feedback->Admissao;
        $resultado = $feedback->ResultadoIntegrado;
        $empresaId = (int) ($feedback->empresa_id ?? 0);
        $centroCustoId = (int) ($admissao->centro_custo_id ?? 0);
        if ($centroCustoId <= 0 && (int) ($resultado?->centro_custo_id ?? 0) > 0) {
            $centroCustoId = (int) $resultado->centro_custo_id;
        }

        $partes = $this->partesLotacao($empresaId, $centroCustoId);

        return $this->apresentar(
            $partes['nome'],
            $partes['cnpj'],
            $this->centroCusto($empresaId, $centroCustoId),
            $this->cargo($feedback, $admissao?->cargo, $empresaId)
        );
    }

    /**
     * @return array{
     *     lotacao: string,
     *     lotacao_nome: string,
     *     lotacao_cnpj: string,
     *     centro_custo: string,
     *     cargo: string,
     *     possui_centro_custo: bool,
     *     possui_cargo: bool
     * }
     */
    public function apresentar(?string $lotacaoNome, ?string $lotacaoCnpj, ?string $centroCusto, ?string $cargo): array
    {
        $lotacaoNome = $this->limpo($lotacaoNome);
        $lotacaoCnpj = $this->limpo($lotacaoCnpj);
        if ($lotacaoNome === self::NAO_INFORMADO) {
            $lotacaoNome = '';
        }
        if ($lotacaoCnpj === self::NAO_INFORMADO) {
            $lotacaoCnpj = '';
        }

        $centroCusto = $this->limpo($centroCusto);
        $cargo = $this->limpo($cargo);
        $lotacao = LotacaoLabelResolver::format($lotacaoNome ?: null, null, $lotacaoCnpj ?: null);
        if ($lotacao === self::NAO_INFORMADO) {
            $lotacao = '';
        }

        return [
            'lotacao' => $lotacao,
            'lotacao_nome' => $lotacaoNome,
            'lotacao_cnpj' => $lotacaoCnpj,
            'centro_custo' => $centroCusto,
            'cargo' => $cargo,
            'possui_centro_custo' => $centroCusto !== '',
            'possui_cargo' => $cargo !== '',
        ];
    }

    private function cargo(FeedbackCurriculo $feedback, ?string $cargoAdmissao, int $empresaId): string
    {
        $cargo = $this->limpo($cargoAdmissao);
        if ($cargo !== '' || $empresaId <= 0) {
            return $cargo;
        }

        $vagaId = 0;
        if ($feedback->vagas_abertas_id) {
            $vagaId = (int) VagasAbertas::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->whereKey($feedback->vagas_abertas_id)
                ->value('vaga_id');
        }

        if ($vagaId <= 0 && $feedback->vaga_id) {
            $vagaId = (int) $feedback->vaga_id;
        }

        if ($vagaId <= 0) {
            return '';
        }

        return (string) Vaga::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereKey($vagaId)
            ->value('nome');
    }

    private function centroCusto(int $empresaId, int $centroCustoId): string
    {
        if ($empresaId <= 0 || $centroCustoId <= 0) {
            return '';
        }

        return (string) CentroCusto::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereKey($centroCustoId)
            ->value('label');
    }

    /**
     * @return array{nome: string, cnpj: string}
     */
    private function partesLotacao(int $empresaId, int $centroCustoId): array
    {
        if ($empresaId <= 0 || $centroCustoId <= 0) {
            return ['nome' => '', 'cnpj' => ''];
        }

        $lista = (new CentroCusto())->listaCentroCustoPorCnpj($empresaId);
        if ($lista instanceof JsonResponse) {
            $lista = [];
        }

        $cnpjs = collect($lista['cnpjs'] ?? []);
        foreach (collect($lista['centros_custos'] ?? []) as $cnpjKey => $centros) {
            $info = $cnpjs[$cnpjKey] ?? [];
            if (! is_array($info)) {
                $info = (array) $info;
            }

            foreach (collect($centros) as $centro) {
                $centro = (object) $centro;
                if ((int) ($centro->id ?? 0) !== $centroCustoId) {
                    continue;
                }

                $nome = $this->limpo($info['nome_fantasia'] ?? $info['razao_social'] ?? null);
                $cnpj = $this->limpo($info['cnpj'] ?? $centro->cnpj_format ?? null);

                return ['nome' => $nome, 'cnpj' => $cnpj];
            }
        }

        $empresa = Cliente::withoutGlobalScopes()
            ->select(['id', 'nome_fantasia', 'razao_social', 'cnpj'])
            ->find($empresaId);

        $label = LotacaoLabelResolver::fromCentroCustoId($empresaId, $centroCustoId, $empresa);
        $label = $this->limpo($label);
        if ($label === '' || $label === self::NAO_INFORMADO) {
            return ['nome' => '', 'cnpj' => ''];
        }

        $idx = strrpos($label, ' - ');
        if ($idx === false) {
            return ['nome' => $label, 'cnpj' => ''];
        }

        return [
            'nome' => trim(substr($label, 0, $idx)),
            'cnpj' => trim(substr($label, $idx + 3)),
        ];
    }

    private function limpo(?string $valor): string
    {
        return trim((string) $valor);
    }
}
