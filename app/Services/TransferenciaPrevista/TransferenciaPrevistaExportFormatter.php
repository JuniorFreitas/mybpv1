<?php

namespace App\Services\TransferenciaPrevista;

use App\Models\TransferenciaPrevista;
use MasterTag\DataHora;

class TransferenciaPrevistaExportFormatter
{
    public function getHeaders(): array
    {
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
        ];
    }

    public function formatRow(TransferenciaPrevista $row): array
    {
        $colaboradorNome = $row->Colaborador ? $row->Colaborador->nome : '';
        return [
            $this->cleanText($row->UserCadastrou->nome ?? ''),
            $this->cleanText($row->created_at ? (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($colaboradorNome),
            $this->cleanText($row->CentroCustoOrigem->label ?? ''),
            $this->cleanText($row->CentroCustoDestino->label ?? ''),
            $this->cleanText($row->data_transferencia ? (new DataHora($row->data_transferencia))->dataCompleta() : ''),
            $this->cleanText($row->GestorAprovacao->nome ?? ''),
            $this->cleanText($row->obs ?? ''),
            $this->cleanText($row->status_aprovacao ?: 'aberto'),
            $this->cleanText($row->QuemAprovou->nome ?? 'aguardando'),
            $this->cleanText($row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->obs_aprovacao ?? ''),
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
