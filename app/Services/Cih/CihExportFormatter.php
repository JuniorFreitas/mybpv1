<?php

namespace App\Services\Cih;

use App\Models\Cih;

class CihExportFormatter
{
    public function __construct(
        private string|int $configModel,
        private bool $temFilial = false,
        private ?CihLotacaoResolver $lotacaoResolver = null,
    ) {
    }

    public function getHeaders(): array
    {
        if ($this->isCostCenterConfig()) {
            $headers = [
                'CIH ID',
                'Colaborador',
                'PIS',
                'Cargo',
                'Centro de Custo',
            ];
            if ($this->temFilial) {
                $headers[] = 'Lotação';
            }
            $headers = array_merge($headers, [
                'Data Ocorrência',
                'data_ocorrencia_iso',
                'Ocorrência',
                'Responsável Lançamento',
                'Data Lançamento',
                'Ação',
                'Status Aprovação Gestor',
                'Data Aprovação Gestor',
                'Responsável Aprovação Gestor',
                'Status Aprovação RH',
                'Data Aprovação RH',
                'Responsável Aprovação RH',
            ]);

            return $headers;
        }

        $headers = [
            'CIH ID',
            'Colaborador',
            'PIS',
            'Cargo',
            'Área',
            'Centro de Custo',
        ];
        if ($this->temFilial) {
            $headers[] = 'Lotação';
        }
        $headers = array_merge($headers, [
            'Data Ocorrência',
            'data_ocorrencia_iso',
            'Ocorrência',
            'Responsável Lançamento',
            'Data Lançamento',
            'data_iso_lancamento',
            'Ação',
            'Status Aprovação Gestor',
            'Data Aprovação Gestor',
            'data_iso_aprovacao_gestor',
            'Responsável Aprovação Gestor',
            'Status Aprovação RH',
            'Data Aprovação RH',
            'data_iso_aprovacao_rh',
            'Responsável Aprovação RH',
        ]);

        return $headers;
    }

    public function formatRow(Cih $cih, $colaborador): array
    {
        if ($this->isCostCenterConfig()) {
            return $this->formatCostCenterRow($cih, $colaborador);
        }

        return $this->formatStandardRow($cih, $colaborador);
    }

    private function isCostCenterConfig(): bool
    {
        $costCenterIdentifiers = [
            'centro_custo',
            'centro-custo',
            'centrocusto',
            'cost_center',
        ];

        $configLower = strtolower((string) $this->configModel);

        if (is_numeric($this->configModel)) {
            $configValue = (int) $this->configModel;
            if (defined('\App\Models\Cih::CONFIG_CENTRO_DE_CUSTO')) {
                return $configValue === \App\Models\Cih::CONFIG_CENTRO_DE_CUSTO;
            }

            return in_array($configValue, [1, 2], true);
        }

        return collect($costCenterIdentifiers)->contains(
            fn ($identifier) => str_contains($configLower, $identifier)
        );
    }

    private function formatCostCenterRow(Cih $cih, $colaborador): array
    {
        $row = [
            $this->cleanText($cih->id),
            $this->cleanText($colaborador->Curriculo->nome ?? ''),
            $this->cleanText($colaborador->Admissao->pis ?? ''),
            $this->cleanText($colaborador->VagaAberta->Vaga->nome ?? ''),
            $this->cleanText($cih->CentroDeCusto->label ?? ''),
        ];

        if ($this->temFilial) {
            $row[] = $this->cleanText($this->resolverLotacao($colaborador, $cih));
        }

        return array_merge($row, [
            $this->cleanText($cih->data_lancamento),
            $this->cleanText($cih->data_iso_lancamento),
            $this->cleanText($cih->Tag?->label ?? $cih->outra_tag ?? ''),
            $this->cleanText($cih->ResponsavelLancamento?->nome ?? ''),
            $this->cleanText($cih->data_criacao),
            $this->cleanText($cih->acao),
            $this->cleanText($cih->status ?? 'aguardando'),
            $this->cleanText($cih->data_aprovacao),
            $this->cleanText($cih->ResponsavelAprovacao?->nome ?? ''),
            $this->cleanText($cih->resposta_rh ?? ''),
            $this->cleanText($cih->data_aprovacao_rh),
            $this->cleanText($cih->RhAprovacao?->nome ?? ''),
        ]);
    }

    private function formatStandardRow(Cih $cih, $colaborador): array
    {
        $row = [
            $this->cleanText($cih->id),
            $this->cleanText($colaborador->Curriculo->nome ?? ''),
            $this->cleanText($colaborador->Admissao->pis ?? ''),
            $this->cleanText($colaborador->VagaAberta->Vaga->nome ?? ''),
            $this->cleanText($cih->area_id ? ($cih->Area->label ?? '') : ($cih->outra_area ?? '')),
            $this->cleanText($colaborador->Admissao->CentroCusto->label ?? ''),
        ];

        if ($this->temFilial) {
            $row[] = $this->cleanText($this->resolverLotacao($colaborador, $cih));
        }

        return array_merge($row, [
            $this->cleanText($cih->data_lancamento),
            $this->cleanText($cih->data_iso_lancamento),
            $this->cleanText($cih->Tag?->label ?? $cih->outra_tag ?? ''),
            $this->cleanText($cih->ResponsavelLancamento?->nome ?? ''),
            $this->cleanText($cih->data_criacao),
            $this->cleanText($cih->data_iso_criacao),
            $this->cleanText($cih->acao),
            $this->cleanText($cih->status ?? 'aguardando'),
            $this->cleanText($cih->data_aprovacao),
            $this->cleanText($cih->data_iso_aprovacao_gestor),
            $this->cleanText($cih->ResponsavelAprovacao?->nome ?? ''),
            $this->cleanText($cih->resposta_rh ?? ''),
            $this->cleanText($cih->data_aprovacao_rh),
            $this->cleanText($cih->data_iso_aprovacao_rh),
            $this->cleanText($cih->RhAprovacao?->nome ?? ''),
        ]);
    }

    private function resolverLotacao($colaborador, Cih $cih): string
    {
        if (!$this->lotacaoResolver) {
            return '';
        }

        $centroId = $colaborador->Admissao->centro_custo_id
            ?? $cih->centro_custo_id
            ?? null;

        return $this->lotacaoResolver->format($centroId !== null ? (int) $centroId : null);
    }

    private function cleanText(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        $text = preg_replace('/[\r\n\t]+/', ' ', $text);
        $text = trim($text);

        $replacements = [
            '√†' => 'ã', '√°' => 'á', '√≥' => 'ó', '√≠' => 'í',
            '√ß' => 'ç', '√£' => 'ã', '√µ' => 'õ', '√∫' => 'ú',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
