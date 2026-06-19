<?php

namespace App\Services\MudancaCargo;

use App\Models\MudancaCargo;
use MasterTag\DataHora;

class MudancaCargoExportFormatter
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
            'Nome',
            'Centro de Custo Atual',
            'Filial Atual',
            'Centro de Custo Novo',
            'Filial Nova',
            'Cargo Atual',
            'Cargo Novo',
            'Função Atual',
            'Função Nova',
            'Salário Atual',
            'Salário Novo',
            'Solicitante',
            'Data Solicitação',
            'OBS Solicitante',
            'Gestor',
            'Gestor Aprovação',
            'Data da Aprovação',
            'Status',
            'OBS Gestor',
            "Status {$extra}",
            "Quem Aprovou {$extra}",
            "Data e Hora Aprovação {$extra}",
            'Status RH',
            'Quem Aprovou/Reprovou RH',
            'Data da Aprovação/Reprovação RH',
            'Observação Aprovação/Reprovação RH',
        ];
    }

    public function formatRow(MudancaCargo $row): array
    {
        $dataSolicitacao = $row->data_solicitacao ? (is_object($row->data_solicitacao) ? $row->data_solicitacao->format('d/m/Y H:i') : (new DataHora($row->data_solicitacao))->dataCompleta()) : '';
        $dataAprovacaoGestor = $row->status_aprovacao_gestor && $row->data_aprovacao_gestor
            ? (new DataHora($row->data_aprovacao_gestor))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_gestor))->horaCompleta(), 0, 5) : '';
        $dataAprovacaoRh = $row->status_aprovacao_rh && $row->data_aprovacao_rh
            ? (new DataHora($row->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_rh))->horaCompleta(), 0, 5) : '';
        $dataHoraAprovacaoExtra = '';
        if (!empty($row->data_aprovacao_extra)) {
            $dataHoraAprovacaoExtra = (new DataHora($row->data_aprovacao_extra))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_extra))->horaCompleta(), 0, 5);
        }

        return [
            $this->cleanText($row->Admissao && $row->Admissao->Feedback && $row->Admissao->Feedback->Curriculo ? $row->Admissao->Feedback->Curriculo->nome : ''),
            $this->cleanText($row->CentroCustoAnterior->label ?? ''),
            $this->cleanText($this->filialLabel($row->anterior_filial, $row->CentroCustoFilialAnterior)),
            $this->cleanText($row->mantem_centro_custo ? '' : ($row->CentroCustoNovo->label ?? '')),
            $this->cleanText($this->filialLabel($row->novo_filial, $row->CentroCustoFilialNovo)),
            $this->cleanText($row->VagaAbertaAnterior && $row->VagaAbertaAnterior->Vaga ? $row->VagaAbertaAnterior->Vaga->nome : ''),
            $this->cleanText($row->mantem_cargo ? '' : ($row->VagaAbertaNova && $row->VagaAbertaNova->Vaga ? $row->VagaAbertaNova->Vaga->nome : '')),
            $this->cleanText($row->anterior_funcao ?? ''),
            $this->cleanText($row->mantem_funcao ? '' : ($row->nova_funcao ?? '')),
            $this->cleanText($row->anterior_salario ?? ''),
            $this->cleanText($row->mantem_salario ? '' : ($row->novo_salario ?? '')),
            $this->cleanText($row->Solicitante->nome ?? ''),
            $this->cleanText($dataSolicitacao),
            $this->cleanText($row->obs_solicitante ?? ''),
            $this->cleanText($row->Gestor->nome ?? ''),
            $this->cleanText($row->GestorAprovacao->nome ?? ''),
            $this->cleanText($dataAprovacaoGestor),
            $this->cleanText($row->status_aprovacao_gestor ?? ''),
            $this->cleanText($row->obs_gestor_aprovacao ?? ''),
            $this->cleanText($row->status_aprovacao_extra ?? ''),
            $this->cleanText($row->AprovacaoExtra->nome ?? ''),
            $this->cleanText($dataHoraAprovacaoExtra),
            $this->cleanText($row->status_aprovacao_rh ?? ''),
            $this->cleanText($row->RhAprovacao->nome ?? ''),
            $this->cleanText($dataAprovacaoRh),
            $this->cleanText($row->obs_rh ?? ''),
        ];
    }

    private function filialLabel($condicao, $centroCustoFilial): string
    {
        if (!$condicao || !$centroCustoFilial || !$centroCustoFilial->relationLoaded('Filial') || !$centroCustoFilial->Filial) {
            return '';
        }
        $dados = $centroCustoFilial->Filial->dados ?? null;
        return is_object($dados) && isset($dados->razao_social) ? $dados->razao_social : (is_array($dados) ? ($dados['razao_social'] ?? '') : '');
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
