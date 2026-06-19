<?php

namespace App\Services\TransferenciaPrevista;

use App\Models\TransferenciaPrevista;
use MasterTag\DataHora;

class TransferenciaPrevistaExportFormatter
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
            'Colaborador',
            'Centro de Custo Origem',
            'Centro de Custo Destino',
            'Data da Transferência',
            'Gestor Aprovação',
            'Observação',
            'Status',
            'Quem Aprovou/Reprovou',
            'Data da Aprovação/Reprovação',
            'Observação Aprovação/Reprovação',
            "Status {$extra}",
            "Quem Aprovou {$extra}",
            "Data e Hora Aprovação {$extra}",
            'Status RH',
            'Quem Aprovou/Reprovou RH',
            'Data da Aprovação/Reprovação RH',
            'Observação Aprovação/Reprovação RH',
        ];
    }

    public function formatRow(TransferenciaPrevista $row): array
    {
        $colaboradorNome = $row->Colaborador ? $row->Colaborador->nome : '';
        $dataHoraAprovacaoExtra = '';
        if (!empty($row->data_aprovacao_extra)) {
            $dataHoraAprovacaoExtra = (new DataHora($row->data_aprovacao_extra))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_extra))->horaCompleta(), 0, 5);
        }
        $dataAprovacaoRh = '';
        if (!empty($row->data_aprovacao_rh)) {
            $dataAprovacaoRh = (new DataHora($row->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_rh))->horaCompleta(), 0, 5);
        }

        return [
            $this->cleanText($row->UserCadastrou->nome ?? ''),
            $this->cleanText($row->created_at ? (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($colaboradorNome),
            $this->cleanText($row->CentroCustoOrigem?->label ?? ''),
            $this->cleanText($row->CentroCustoDestino->label ?? ''),
            $this->cleanText($row->data_transferencia ? (new DataHora($row->data_transferencia))->dataCompleta() : ''),
            $this->cleanText($row->GestorAprovacao->nome ?? ''),
            $this->cleanText($row->obs ?? ''),
            $this->cleanText($row->status_aprovacao ?: 'aberto'),
            $this->cleanText($row->QuemAprovou->nome ?? 'aguardando'),
            $this->cleanText($row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->obs_aprovacao ?? ''),
            $this->cleanText($row->status_aprovacao_extra ?? ''),
            $this->cleanText($row->UserAprovacaoExtra->nome ?? ''),
            $this->cleanText($dataHoraAprovacaoExtra),
            $this->cleanText($row->resposta_rh ?? ''),
            $this->cleanText($row->RhAprovacao->nome ?? ''),
            $this->cleanText($dataAprovacaoRh),
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
