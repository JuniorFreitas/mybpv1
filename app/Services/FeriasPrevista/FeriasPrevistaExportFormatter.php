<?php

namespace App\Services\FeriasPrevista;

use App\Models\Ferias;
use MasterTag\DataHora;

class FeriasPrevistaExportFormatter
{
    /** Nome configurado da aprovação extra (ex: "Gerência"). Quando null, usa "Aprovação Extra". */
    private ?string $nomeAprovacaoExtra;

    public function __construct(?string $nomeAprovacaoExtra = null)
    {
        $this->nomeAprovacaoExtra = $nomeAprovacaoExtra ?: 'Aprovação Extra';
    }

    public function getHeaders(): array
    {
        $extra = $this->nomeAprovacaoExtra;
        return [
            'Nome',
            'Cargo',
            'Data da Admissão',
            'Centro de Custo',
            'Período Aquisitivo',
            'Última Data',
            'Quantidade de Faltas',
            'Data Saída',
            'Data Retorno',
            'Quantidade de Dias',
            'Saldo de Dias',
            'Abono Pecuniário',
            'Adiantamento Décimo Terceiro',
            'Quem Cadastrou',
            'Gestor Indicado',
            'Gestor Aprovação',
            'Data da Aprovação',
            'Status',
            "Status {$extra}",
            "Quem Aprovou {$extra}",
            "Data e Hora Aprovação {$extra}",
            'RH Aprovação',
            'Data da Aprovação RH',
            'Resposta RH',
        ];
    }

    public function formatRow(Ferias $row): array
    {
        $ultimaData = $row->ultima_data ? (new DataHora($row->ultima_data))->dataCompleta() : '';
        $dataSaida = $row->data_saida ? (is_object($row->data_saida) ? $row->data_saida->format('d/m/Y') : (new DataHora($row->data_saida))->dataCompleta()) : '';
        $dataRetorno = $row->data_retorno ? (is_object($row->data_retorno) ? $row->data_retorno->format('d/m/Y') : (new DataHora($row->data_retorno))->dataCompleta()) : '';
        $dataAdmissao = $row->Admissao && $row->Admissao->data_admissao
            ? (is_object($row->Admissao->data_admissao) ? $row->Admissao->data_admissao->format('d/m/Y') : (new DataHora($row->Admissao->data_admissao))->dataCompleta())
            : '';

        $dataHoraAprovacaoExtra = '';
        if (!empty($row->data_aprovacao_extra)) {
            $dataHoraAprovacaoExtra = (new DataHora($row->data_aprovacao_extra))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_extra))->horaCompleta(), 0, 5);
        }

        return [
            $this->cleanText($row->Admissao->Feedback->Curriculo->nome ?? ''),
            $this->cleanText($row->Admissao->cargo ?? ''),
            $this->cleanText($dataAdmissao),
            $this->cleanText($row->Admissao->CentroCusto->label ?? 'Não Informado'),
            $this->cleanText($row->PeriodoAquisitivo->label ?? ''),
            $this->cleanText($ultimaData),
            $this->cleanText((string) ($row->qnt_faltas ?? '')),
            $this->cleanText($dataSaida),
            $this->cleanText($dataRetorno),
            $this->cleanText((string) ($row->qnt_dias ?? '')),
            $this->cleanText((string) ($row->dias_saldo ?? '')),
            $this->cleanText($row->abono_pecuniario ? 'Sim' : 'Não'),
            $this->cleanText($row->adiantamento_decimo_terceiro ? 'Sim' : 'Não'),
            $this->cleanText($row->Solicitante->nome ?? ''),
            $this->cleanText($row->Gestor->nome ?? ''),
            $this->cleanText($row->GestorAprovacao->nome ?? ''),
            $this->cleanText($row->status_aprovacao_gestor && $row->data_aprovacao_gestor ? (new DataHora($row->data_aprovacao_gestor))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_gestor))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->status_aprovacao_gestor ?? ''),
            $this->cleanText($row->status_aprovacao_extra ?? ''),
            $this->cleanText($row->AprovacaoExtra->nome ?? ''),
            $this->cleanText($dataHoraAprovacaoExtra),
            $this->cleanText($row->status_aprovacao_rh && $row->RhAprovacao ? $row->RhAprovacao->nome : ''),
            $this->cleanText($row->status_aprovacao_rh && $row->data_aprovacao_rh ? (new DataHora($row->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_rh))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->status_aprovacao_rh ?? ''),
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
