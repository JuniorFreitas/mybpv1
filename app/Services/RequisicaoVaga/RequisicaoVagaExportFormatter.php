<?php

namespace App\Services\RequisicaoVaga;

use App\Models\RequisicaoVagaMovimentacao;
use MasterTag\DataHora;

class RequisicaoVagaExportFormatter
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
            'ID',
            'Quem Solicitou',
            'Data da Solicitação',
            'Cargo',
            'Área',
            'Centro de Custo',
            'Quantidade',
            'Tipo de Contratação',
            'Prioridade',
            'Imediata',
            'Data Previsão Início',
            'Solicitante (nome)',
            'Observação',
            'Posição',
            'Processo',
            'Nome Indicação',
            'Contrato',
            'Local Trabalho',
            'Horário',
            'Gestor (nome)',
            'PPRA',
            'Salário',
            'Salário Valor',
            'Benefício',
            'Benefício Exceção',
            'Treinamento',
            'Treinamento Exceção',
            'Status Aprovação Gestor',
            'Quem Aprovou/Reprovou Gestor',
            'Data Aprovação/Reprovação Gestor',
            "Status {$extra}",
            "Quem Aprovou {$extra}",
            "Data e Hora Aprovação {$extra}",
            'Status Aprovação RH',
            'Quem Aprovou RH',
            'Data Aprovação RH',
        ];
    }

    public function formatRow(RequisicaoVagaMovimentacao $row): array
    {
        return [
            $this->cleanText((string) $row->id),
            $this->cleanText($row->UserCadastrou->nome ?? ''),
            $this->cleanText($row->created_at ? (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->Cargo->nome ?? ''),
            $this->cleanText($row->Area->label ?? ''),
            $this->cleanText($row->CentroCusto->label ?? ''),
            $this->cleanText((string) $row->quantidade),
            $this->cleanText($row->tipo_contratacao ?? ''),
            $this->cleanText($row->prioridade ?? ''),
            $this->cleanText($row->imediata ? 'Sim' : 'Não'),
            $this->cleanText($row->imediata ? 'Imediata' : ($row->previsao_inicio ? (is_object($row->previsao_inicio) ? $row->previsao_inicio->format('d/m/Y') : (string) $row->previsao_inicio) : '')),
            $this->cleanText($row->solicitante ?? ''),
            $this->cleanText($row->observacao ?? ''),
            $this->cleanText($row->posicao ?? ''),
            $this->cleanText($row->processo ?? ''),
            $this->cleanText($row->nome_indicacao ?? ''),
            $this->cleanText($row->contrato ?? ''),
            $this->cleanText($row->local_trabalho ?? ''),
            $this->cleanText($row->horario ?? ''),
            $this->cleanText($row->GestorContratacao->nome ?? $row->gestor ?? ''),
            $this->cleanText($row->ppra === null ? '' : ($row->ppra ? 'Sim' : 'Não')),
            $this->cleanText($row->salario ?? ''),
            $this->cleanText($row->salario_valor !== null ? (string) $row->salario_valor : ''),
            $this->cleanText($row->beneficio ?? ''),
            $this->cleanText($row->beneficio_excecao ?? ''),
            $this->cleanText($row->treinamento ?? ''),
            $this->cleanText($row->treinamento_excecao ?? ''),
            $this->cleanText($row->status_aprovacao ?? 'aberto'),
            $this->cleanText($row->UserAprovacao->nome ?? 'aguardando'),
            $this->cleanText($row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : 'aguardando'),
            $this->cleanText($row->status_aprovacao_extra ?? ''),
            $this->cleanText($row->AprovacaoExtra->nome ?? ''),
            $this->cleanText($row->data_aprovacao_extra ? (new DataHora($row->data_aprovacao_extra))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_extra))->horaCompleta(), 0, 5) : ''),
            $this->cleanText($row->status_aprovacao_rh ?? ''),
            $this->cleanText($row->AprovacaoRh->nome ?? ''),
            $this->cleanText($row->data_aprovacao_rh ? (new DataHora($row->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_rh))->horaCompleta(), 0, 5) : ''),
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
