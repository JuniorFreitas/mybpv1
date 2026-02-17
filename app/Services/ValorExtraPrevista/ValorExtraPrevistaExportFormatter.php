<?php

namespace App\Services\ValorExtraPrevista;

use App\Models\ValorExtraPrevista;
use MasterTag\DataHora;

class ValorExtraPrevistaExportFormatter
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
            'Quem solicitou',
            'Data da Solicitação',
            'CENTRO DE CUSTO',
            'FILIAL',
            'COLABORADOR',
            'Cargo',
            'TIPO',
            'PERÍODO EM DIAS',
            'GESTOR APROVAÇÃO',
            'OBSERVAÇÃO',
            'STATUS',
            'QUEM APROVOU/REPROVOU',
            'DATA DA APROVAÇÃO/REPROVAÇÃO',
            'OBSERVAÇÃO APROVAÇÃO/REPROVAÇÃO',
            "Status {$extra}",
            "Quem Aprovou {$extra}",
            "Data e Hora Aprovação {$extra}",
            'Status RH',
            'Quem Aprovou/Reprovou RH',
            'Data da Aprovação/Reprovação RH',
            'Observação Aprovação/Reprovação RH',
        ];
    }

    public function formatRow(ValorExtraPrevista $row): array
    {
        $cargo = '';
        if ($row->Colaborador && $row->Colaborador->relationLoaded('Feedback') && $row->Colaborador->Feedback) {
            $vagaAberta = $row->Colaborador->Feedback->VagaAberta ?? null;
            $cargo = $vagaAberta && $vagaAberta->Vaga ? $vagaAberta->Vaga->nome : '';
        }
        $filialLabel = '';
        if ($row->filial && $row->CentroCustoFilial && $row->CentroCustoFilial->relationLoaded('Filial') && $row->CentroCustoFilial->Filial) {
            $dados = $row->CentroCustoFilial->Filial->dados ?? null;
            $filialLabel = is_object($dados) && isset($dados->razao_social) ? $dados->razao_social : (is_array($dados) ? ($dados['razao_social'] ?? '') : '');
        }

        $dataHoraAprovacaoExtra = '';
        if (!empty($row->data_aprovacao_extra)) {
            $dataHoraAprovacaoExtra = (new DataHora($row->data_aprovacao_extra))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_extra))->horaCompleta(), 0, 5);
        }

        return [
            $this->cleanText($row->UserCadastrou->nome ?? ''),
            $this->cleanText($row->created_at ? (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->CentroCusto->label ?? ''),
            $this->cleanText($filialLabel),
            $this->cleanText($row->Colaborador->nome ?? ''),
            $this->cleanText($cargo),
            $this->cleanText($row->tipo ?? ''),
            $this->cleanText((string) ($row->periodo_dias ?? '')),
            $this->cleanText($row->GestorAprovacao->nome ?? ''),
            $this->cleanText($row->obs ?? ''),
            $this->cleanText($row->status_aprovacao ?: 'aberto'),
            $this->cleanText($row->UserAprovacao->nome ?? 'aguardando'),
            $this->cleanText($row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->obs_aprovacao ?? ''),
            $this->cleanText($row->status_aprovacao_extra ?? ''),
            $this->cleanText($row->AprovacaoExtra->nome ?? ''),
            $this->cleanText($dataHoraAprovacaoExtra),
            $this->cleanText($row->status_aprovacao_rh ?? ''),
            $this->cleanText($row->RhAprovacao->nome ?? ''),
            $this->cleanText($row->data_aprovacao_rh ? (new DataHora($row->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_rh))->horaCompleta(), 0, 5) : ''),
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
