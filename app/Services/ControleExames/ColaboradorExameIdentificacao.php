<?php

namespace App\Services\ControleExames;

use App\Models\CentroCusto;
use App\Models\CentroCustoFilial;
use App\Models\Cliente;
use App\Models\ClienteFilial;
use App\Models\FeedbackCurriculo;
use App\Models\Vaga;
use App\Models\VagasAbertas;
use App\Services\Planejamento\Movimentacao\LotacaoLabelResolver;

class ColaboradorExameIdentificacao
{
    public const NAO_INFORMADO = 'Não informado';

    /**
     * @return array{
     *     lotacao: string,
     *     centro_custo: string,
     *     cargo: string,
     *     possui_centro_custo: bool,
     *     possui_cargo: bool
     * }
     */
    public function paraFeedback(?FeedbackCurriculo $feedback): array
    {
        if (!$feedback) {
            return $this->apresentar(null, null, null);
        }

        $feedback->loadMissing(['Admissao', 'ResultadoIntegrado']);
        $admissao = $feedback->Admissao;
        $resultado = $feedback->ResultadoIntegrado;
        $empresaId = (int) ($feedback->empresa_id ?? 0);
        $centroCustoId = (int) ($admissao->centro_custo_id ?? 0);
        $filial = (bool) ($admissao->filial ?? false);
        $filialId = (int) ($admissao->centro_custo_filial_id ?? 0);
        if ($centroCustoId <= 0 && (int) ($resultado?->centro_custo_id ?? 0) > 0) {
            $centroCustoId = (int) $resultado->centro_custo_id;
            $filial = false;
            $filialId = 0;
        }

        return $this->apresentar(
            $this->lotacao($empresaId, $centroCustoId, $filial, $filialId),
            $this->centroCusto($empresaId, $centroCustoId),
            $this->cargo($feedback, $admissao?->cargo, $empresaId)
        );
    }

    /**
     * @return array{
     *     lotacao: string,
     *     centro_custo: string,
     *     cargo: string,
     *     possui_centro_custo: bool,
     *     possui_cargo: bool
     * }
     */
    public function apresentar(?string $lotacao, ?string $centroCusto, ?string $cargo): array
    {
        $lotacao = $this->limpo($lotacao);
        if ($lotacao === self::NAO_INFORMADO) {
            $lotacao = '';
        }

        $centroCusto = $this->limpo($centroCusto);
        $cargo = $this->limpo($cargo);

        return [
            'lotacao' => $lotacao,
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

    private function lotacao(int $empresaId, int $centroCustoId, bool $filial, int $centroCustoFilialId): string
    {
        if ($empresaId <= 0) {
            return '';
        }

        if ($centroCustoId > 0) {
            $vinculo = CentroCustoFilial::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->where('centro_custo_id', $centroCustoId)
                ->whereNull('deleted_at')
                ->orderByDesc('ativo')
                ->orderBy('id')
                ->first();

            if ($vinculo) {
                $filialModel = ClienteFilial::query()
                    ->where('empresa_id', $empresaId)
                    ->whereKey($vinculo->cliente_filial_id)
                    ->first();

                return $this->comTipo(LotacaoLabelResolver::fromFilial($filialModel), 'Filial');
            }

            return $this->comTipo(
                LotacaoLabelResolver::fromEmpresa(
                    Cliente::withoutGlobalScopes()->select(['id', 'nome_fantasia', 'razao_social', 'cnpj'])->find($empresaId)
                ),
                'Matriz'
            );
        }

        if ($filial && $centroCustoFilialId > 0) {
            $mapa = LotacaoLabelResolver::filiaisByCentroCustoFilialIds([$centroCustoFilialId]);

            return $this->comTipo(LotacaoLabelResolver::fromFilial($mapa[$centroCustoFilialId] ?? null), 'Filial');
        }

        return '';
    }

    private function comTipo(string $label, string $tipo): string
    {
        $label = $this->limpo($label);
        if ($label === '' || $label === self::NAO_INFORMADO) {
            return '';
        }

        return $label . ' (' . $tipo . ')';
    }

    private function limpo(?string $valor): string
    {
        return trim((string) $valor);
    }
}
