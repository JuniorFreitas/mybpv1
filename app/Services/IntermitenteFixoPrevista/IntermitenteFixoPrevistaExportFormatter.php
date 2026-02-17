<?php

namespace App\Services\IntermitenteFixoPrevista;

use App\Models\IntermitenteFixoPrevista;
use MasterTag\DataHora;

class IntermitenteFixoPrevistaExportFormatter
{
    public function getHeaders(): array
    {
        return [
            'Quem Solicitou',
            'Data da Solicitação',
            'Centro de Custo',
            'Filial',
            'Área Etiqueta',
            'Colaborador',
            'Cargo Anterior',
            'Salário Anterior',
            'Cargo Novo',
            'Salário Novo',
            'Gestor Aprovação',
            'Motivos',
            'Status',
            'Quem Aprovou/Reprovou',
            'Data da Aprovação/Reprovação',
            'Observação Aprovação/Reprovação',
            'RH Aprovação',
            'Data da Aprovação RH',
            'Resposta RH',
            'OBS RH',
        ];
    }

    public function formatRow(IntermitenteFixoPrevista $row): array
    {
        $filialLabel = '';
        if ($row->filial && $row->CentroCustoFilial && $row->CentroCustoFilial->relationLoaded('Filial') && $row->CentroCustoFilial->Filial) {
            $dados = $row->CentroCustoFilial->Filial->dados ?? null;
            $filialLabel = is_object($dados) && isset($dados->razao_social) ? $dados->razao_social : (is_array($dados) ? ($dados['razao_social'] ?? '') : '');
        }
        $dataAprovacaoRh = $row->status_aprovacao_rh && $row->data_aprovacao_rh
            ? (new DataHora($row->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_rh))->horaCompleta(), 0, 5) : '';

        return [
            $this->cleanText($row->UserCadastrou->nome ?? ''),
            $this->cleanText($row->created_at ? (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->CentroCusto->label ?? ''),
            $this->cleanText($filialLabel),
            $this->cleanText($row->AreaEtiqueta->label ?? ''),
            $this->cleanText($row->Colaborador->nome ?? ''),
            $this->cleanText($row->VagaAbertaAnterior->titulo ?? ''),
            $this->cleanText($row->salario_anterior_format ?? (string) ($row->salario_anterior ?? '')),
            $this->cleanText($row->VagaAbertaNova->titulo ?? ''),
            $this->cleanText($row->novo_salario_format ?? (string) ($row->novo_salario ?? '')),
            $this->cleanText($row->GestorAprovacao->nome ?? ''),
            $this->cleanText($row->motivos ?? ''),
            $this->cleanText($row->status_aprovacao ?: 'aberto'),
            $this->cleanText($row->UserAprovacao->nome ?? 'aguardando'),
            $this->cleanText($row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->obs_aprovacao ?? ''),
            $this->cleanText($row->status_aprovacao_rh && $row->RhAprovacao ? $row->RhAprovacao->nome : ''),
            $this->cleanText($dataAprovacaoRh),
            $this->cleanText($row->status_aprovacao_rh ?? ''),
            $this->cleanText($row->obs_rh ?? ''),
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
