<?php

namespace App\Services\DemissaoPrevista;

use App\Models\DemissaoPrevista;
use MasterTag\DataHora;

class DemissaoPrevistaExportFormatter
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
            'Data da Solicitação',
            'Centro de Custo',
            'Filial',
            'Colaborador',
            'Data Admissão',
            'Cargo',
            'Data Demissão',
            'Tipo de Aviso',
            'Gestor Aprovação',
            'Observação',
            'Status',
            'Quem Aprovou/Reprovou',
            'Data da Aprovação/Reprovação',
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

    public function formatRow(DemissaoPrevista $row): array
    {
        $colaboradorNome = $row->Colaborador->Curriculo->nome ?? $row->Colaborador->nome ?? '';
        $dataAdmissao = '';
        $cargo = '';
        $admissao = $row->Colaborador && $row->Colaborador->relationLoaded('Feedback') && $row->Colaborador->Feedback
            ? $row->Colaborador->Feedback->Admissao ?? null
            : null;
        if ($admissao) {
            $dataAdmissao = $admissao->data_admissao ? (new DataHora($admissao->data_admissao))->dataCompleta() : '';
            $cargo = $admissao->cargo ?? '';
        }

        return [
            $this->cleanText($row->UserCadastrou->nome ?? ''),
            $this->cleanText($row->created_at ? (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->CentroCusto->label ?? ''),
            $this->cleanText($row->filial && $row->CentroCustoFilial ? (string) $row->centro_custo_filial_id : ''),
            $this->cleanText($colaboradorNome),
            $this->cleanText($dataAdmissao),
            $this->cleanText($cargo),
            $this->cleanText($row->data_demissao ? (new DataHora($row->data_demissao))->dataCompleta() : ''),
            $this->cleanText($row->tipo_aviso ?? ''),
            $this->cleanText($row->GestorAprovacao->nome ?? ''),
            $this->cleanText($row->obs ?? ''),
            $this->cleanText($row->status_aprovacao ?: 'aberto'),
            $this->cleanText($row->UserAprovacao->nome ?? 'aguardando'),
            $this->cleanText($row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->obs_aprovacao ?? ''),
            $this->cleanText($row->status_aprovacao_rh ?? ''),
            $this->cleanText($row->RhAprovacao->nome ?? ''),
            $this->cleanText($row->data_aprovacao_rh ? (new DataHora($row->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_rh))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->obs_rh ?? ''),
            $this->cleanText($row->status_aprovacao_extra ?? ''),
            $this->cleanText($row->AprovacaoExtra->nome ?? ''),
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
