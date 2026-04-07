<?php

namespace App\Services\Cih;

use App\Models\Cih;
use Carbon\Carbon;

class CihExportFormatter
{
    private string $configModel;

    public function __construct(string $configModel)
    {
        $this->configModel = $configModel;
    }

    public function getHeaders(): array
    {
        // Verificar se é configuração centro de custo
        if ($this->isCostCenterConfig()) {
            return [
                "CIH ID", "Colaborador", "PIS", "Cargo", "Centro de Custo",
                "Data Ocorrência", "data_ocorrencia_iso", "Ocorrência",
                "Responsável Lançamento", "Data Lançamento", "Ação",
                "Status Aprovação Gestor", "Data Aprovação Gestor", "Responsável Aprovação Gestor",
                "Status Aprovação RH", "Data Aprovação RH", "Responsável Aprovação RH"
            ];
        }

        return [
            "CIH ID", "Colaborador", "PIS", "Cargo", "Área", "Centro de Custo",
            "Data Ocorrência", "data_ocorrencia_iso", "Ocorrência",
            "Responsável Lançamento", "Data Lançamento", "data_iso_lancamento", "Ação",
            "Status Aprovação Gestor", "Data Aprovação Gestor", "data_iso_aprovacao_gestor", "Responsável Aprovação Gestor",
            "Status Aprovação RH", "Data Aprovação RH", "data_iso_aprovacao_rh", "Responsável Aprovação RH"
        ];
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
        // Verificar múltiplas formas de identificar configuração centro de custo
        $costCenterIdentifiers = [
            'centro_custo',
            'centro-custo',
            'centrocusto',
            'cost_center'
        ];

        $configLower = strtolower($this->configModel);

        // Se for número, verificar se é a constante CONFIG_CENTRO_DE_CUSTO
        if (is_numeric($this->configModel)) {
            $configValue = (int)$this->configModel;
            // Verificar se existe a constante no modelo
            if (defined('\App\Models\Cih::CONFIG_CENTRO_DE_CUSTO')) {
                return $configValue === \App\Models\Cih::CONFIG_CENTRO_DE_CUSTO;
            }
            // Fallback: assumir que 1 ou 2 podem ser centro de custo
            return in_array($configValue, [1, 2]);
        }

        // Se for string, verificar se contém identificadores
        return collect($costCenterIdentifiers)->contains(fn($identifier) => str_contains($configLower, $identifier)
        );
    }

    private function formatCostCenterRow(Cih $cih, $colaborador): array
    {
        return [
            $this->cleanText($cih->id),
            $this->cleanText($colaborador->Curriculo->nome ?? ''),
            $this->cleanText($colaborador->Admissao->pis ?? ''),
            $this->cleanText($colaborador->VagaAberta->Vaga->nome ?? ''),
            $this->cleanText($cih->CentroDeCusto->label ?? ''),
            $this->cleanText($this->formatDateOnlyBr($cih)),
            $this->cleanText($cih->data_iso_lancamento),
            $this->cleanText($cih->Tag?->label ?? $cih->outra_tag ?? ''),
            $this->cleanText($cih->ResponsavelLancamento?->nome ?? ''),
            $this->cleanText($cih->data_criacao),
            $this->cleanText($cih->acao),
            $this->cleanText($cih->status ?? "aguardando"),
            $this->cleanText($cih->data_aprovacao),
            $this->cleanText($cih->ResponsavelAprovacao?->nome ?? ''),
            $this->cleanText($cih->resposta_rh ?? ""),
            $this->cleanText($cih->data_aprovacao_rh),
            $this->cleanText($cih->RhAprovacao?->nome ?? ''),
        ];
    }

    private function formatStandardRow(Cih $cih, $colaborador): array
    {
        return [
            $this->cleanText($cih->id),
            $this->cleanText($colaborador->Curriculo->nome ?? ''),
            $this->cleanText($colaborador->Admissao->pis ?? ''),
            $this->cleanText($colaborador->VagaAberta->Vaga->nome ?? ''),
            $this->cleanText($cih->area_id ? ($cih->Area->label ?? '') : ($cih->outra_area ?? '')),
            $this->cleanText($colaborador->Admissao->CentroDeCusto->label ?? ''),
            $this->cleanText($this->formatDateOnlyBr($cih)),
            $this->cleanText($cih->data_iso_lancamento),
            $this->cleanText($cih->Tag?->label ?? $cih->outra_tag ?? ''),
            $this->cleanText($cih->ResponsavelLancamento?->nome ?? ''),
            $this->cleanText($cih->data_criacao),
            $this->cleanText($cih->data_iso_criacao),
            $this->cleanText($cih->acao),
            $this->cleanText($cih->status ?? "aguardando"),
            $this->cleanText($cih->data_aprovacao),
            $this->cleanText($cih->data_iso_aprovacao_gestor),
            $this->cleanText($cih->ResponsavelAprovacao?->nome ?? ''),
            $this->cleanText($cih->resposta_rh ?? ""),
            $this->cleanText($cih->data_aprovacao_rh),
            $this->cleanText($cih->data_iso_aprovacao_rh),
            $this->cleanText($cih->RhAprovacao?->nome ?? ''),
        ];
    }

    private function formatDateOnlyBr(Cih $cih): string
    {
        if (!empty($cih->data_iso_lancamento)) {
            try {
                return Carbon::parse($cih->data_iso_lancamento)->format('d/m/Y');
            } catch (\Throwable $exception) {
                // fallback para o valor exibido no model
            }
        }

        if (!empty($cih->data_lancamento)) {
            if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $cih->data_lancamento, $matches)) {
                return $matches[1];
            }

            try {
                return Carbon::parse($cih->data_lancamento)->format('d/m/Y');
            } catch (\Throwable $exception) {
                return '';
            }
        }

        return '';
    }

    private function cleanText(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Converter para UTF-8 se necessário
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');

        // Remover quebras de linha e caracteres de controle
        $text = preg_replace('/[\r\n\t]+/', ' ', $text);

        // Remover espaços extras
        $text = trim($text);

        // Substituir caracteres problemáticos
        $replacements = [
            '√†' => 'ã', '√°' => 'á', '√≥' => 'ó', '√≠' => 'í',
            '√ß' => 'ç', '√£' => 'ã', '√µ' => 'õ', '√∫' => 'ú'
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
