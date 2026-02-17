<?php

namespace App\Services\AdmissoesPrevista;

use App\Models\AdmissoesPrevista;
use MasterTag\DataHora;

class AdmissoesPrevistaExportFormatter
{
    /** Nome configurado da aprovação extra (ex: "Gerência"). Quando null, usa "Aprovação Extra". */
    private string $nomeAprovacaoExtra;

    public function __construct(?string $nomeAprovacaoExtra = null)
    {
        $this->nomeAprovacaoExtra = $nomeAprovacaoExtra ?: 'Aprovação Extra';
    }

    public function getHeaders(): array
    {
        $extra = $this->nomeAprovacaoExtra;
        return [
            'Quem Solicitou',
            'Nome do Colaborador',
            'Cargo',
            'Data da Solicitação',
            'Centro de Custo',
            'Filial',
            'Tipo de Contrato',
            'Data da Admissão',
            'Observação',
            'Salário',
            'Gestor Aprovação',
            'Quem Aprovou/Reprovou',
            'Data da Aprovação/Reprovação',
            'Status',
            'Observação Aprovação/Reprovação',
            'Status RH',
            'Quem Aprovou/Reprovou RH',
            'Data da Aprovação/Reprovação RH',
            'Observação Aprovação/Reprovação RH',
            "Status {$extra}",
            "Quem Aprovou {$extra}",
            "Data e Hora Aprovação {$extra}",
        ];
    }

    public function formatRow(AdmissoesPrevista $row): array
    {
        $filialLabel = '';
        if ($row->filial && $row->CentroCustoFilial && $row->CentroCustoFilial->relationLoaded('Filial') && $row->CentroCustoFilial->Filial) {
            $dados = $row->CentroCustoFilial->Filial->dados ?? null;
            $filialLabel = is_object($dados) && isset($dados->razao_social) ? $dados->razao_social : (is_array($dados) ? ($dados['razao_social'] ?? '') : '');
        }

        $nomePessoa = $row->nome_pessoa ?? ($row->Colaborador ? $row->Colaborador->nome : '');
        return [
            $this->cleanText($row->UserCadastrou->nome ?? ''),
            $this->cleanText($nomePessoa),
            $this->cleanText($row->Cargo->nome ?? ''),
            $this->cleanText($row->created_at ? (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->CentroCusto->label ?? ''),
            $this->cleanText($filialLabel),
            $this->cleanText($row->tipo_contrato ?? ''),
            $this->cleanText($row->data_admissao ? (new DataHora($row->data_admissao))->dataCompleta() : ''),
            $this->cleanText($row->obs ?? ''),
            $this->cleanText($row->salario_format ?? (string) $row->salario),
            $this->cleanText($row->GestorAprovacao->nome ?? ''),
            $this->cleanText($row->UserAprovacao->nome ?? 'aguardando'),
            $this->cleanText($row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->status_aprovacao ?: 'aberto'),
            $this->cleanText($row->obs_aprovacao ?? ''),
            $this->cleanText($row->status_aprovacao_rh ?? ''),
            $this->cleanText($row->RhAprovacao->nome ?? ''),
            $this->cleanText($row->data_aprovacao_rh ? (new DataHora($row->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_rh))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->obs_rh ?? ''),
            $this->cleanText($row->status_aprovacao_extra ?? ''),
            $this->cleanText($row->UserAprovacaoExtra->nome ?? ''),
            $this->cleanText($row->data_aprovacao_extra ? (new DataHora($row->data_aprovacao_extra))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_extra))->horaCompleta(), 0, 5) : ''),
        ];
    }

    private function cleanText(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        $text = (string) $text;
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
