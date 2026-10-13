<?php

namespace App\Services\Demissao;

use App\Models\Demissao;
use App\Models\DemissaoPrevista;
use MasterTag\DataHora;

class ComunicadoDispensaSemJustaCausaPdfService
{
    public const VIEW = 'pdf.demissao.comunicado_dispensa_sem_justa_causa';

    public function fromDemissao(Demissao $dados): array
    {
        $empresa = $dados->User->DadosEmpresa ?? $dados->Feedback->Empresa ?? null;
        $razaoSocial = $empresa->razao_social ?? ($dados->Feedback->Empresa->razao_social ?? '—');
        $tipoAviso = trim((string) ($dados->tipoAviso->descricao ?? ''));
        $dataDesligamento = $this->rawDate($dados, 'data_desmobilizacao');
        $dataAdmissao = $this->rawDate(optional($dados->Feedback)->Admissao, 'data_admissao');

        return $this->buildPayload(
            title: $dados->motivoRescisao->descricao ?? 'Demissão sem justa causa',
            empregadoNome: $dados->Feedback->Curriculo->nome ?? '—',
            razaoSocial: $razaoSocial,
            tipoAviso: $tipoAviso,
            dataDesligamento: $dataDesligamento,
            dataAdmissao: $dataAdmissao,
            empresa: $empresa,
        );
    }

    public function fromDemissaoPrevista(DemissaoPrevista $demissaoPrevista): array
    {
        $colaborador = $demissaoPrevista->Colaborador;
        $empresa = $colaborador->DadosEmpresa ?? optional($colaborador->Feedback)->Empresa ?? null;
        $razaoSocial = $empresa->razao_social
            ?? optional(optional($colaborador->Feedback)->Empresa)->razao_social
            ?? '—';
        $tipoAviso = trim((string) ($demissaoPrevista->tipo_aviso ?? ''));
        $dataDesligamento = $this->rawDate($demissaoPrevista, 'data_demissao');
        $dataAdmissao = $this->rawDate(optional($colaborador->Feedback)->Admissao, 'data_admissao');

        return $this->buildPayload(
            title: $demissaoPrevista->tipo_aviso ?? 'Aviso prévio',
            empregadoNome: $colaborador->nome ?? '—',
            razaoSocial: $razaoSocial,
            tipoAviso: $tipoAviso,
            dataDesligamento: $dataDesligamento,
            dataAdmissao: $dataAdmissao,
            empresa: $empresa,
        );
    }

    /**
     * @param  object|null  $empresa  Cliente ou equivalente (municipio, uf)
     * @return array<string, mixed>
     */
    private function buildPayload(
        string $title,
        string $empregadoNome,
        string $razaoSocial,
        string $tipoAviso,
        mixed $dataDesligamento,
        mixed $dataAdmissao,
        ?object $empresa,
    ): array {
        $isIndenizado = str_contains(mb_strtolower($tipoAviso), 'indeniz');
        $dataExtenso = $dataDesligamento
            ? (new DataHora($dataDesligamento))->dataCompletaExt()
            : (new DataHora())->dataCompletaExt();
        $dataCiencia = $dataDesligamento
            ? (new DataHora($dataDesligamento))->dataCompleta()
            : (new DataHora())->dataCompleta();
        $diasAviso = $this->calcularDiasAviso($dataAdmissao, $dataDesligamento);
        $cidade = $this->formatarCidade($empresa);

        return [
            'pdfTitle' => $title,
            'empregadoNome' => $empregadoNome,
            'razaoSocial' => $razaoSocial,
            'tipoAviso' => $tipoAviso,
            'isIndenizado' => $isIndenizado,
            'dataCiencia' => $dataCiencia,
            'dataExtenso' => $dataExtenso,
            'diasAviso' => $diasAviso,
            'cidade' => $cidade,
        ];
    }

    private function calcularDiasAviso(mixed $dataAdmissao, mixed $dataDesligamento): int
    {
        if (!$dataAdmissao || !$dataDesligamento) {
            return 30;
        }

        try {
            $anos = \Carbon\Carbon::parse($dataAdmissao)
                ->diffInYears(\Carbon\Carbon::parse($dataDesligamento));

            return min(90, 30 + (3 * (int) $anos));
        } catch (\Throwable $e) {
            return 30;
        }
    }

    private function formatarCidade(?object $empresa): string
    {
        $municipio = trim((string) ($empresa->municipio ?? ''));
        $uf = trim((string) ($empresa->uf ?? ''));

        if ($municipio === '') {
            return 'São Luís-MA';
        }

        return $uf !== '' ? "{$municipio}-{$uf}" : $municipio;
    }

    private function rawDate(?object $model, string $attribute): mixed
    {
        if ($model === null) {
            return null;
        }

        if (method_exists($model, 'getRawOriginal')) {
            $raw = $model->getRawOriginal($attribute);
            if ($raw !== null) {
                return $raw;
            }
        }

        return $model->{$attribute} ?? null;
    }
}
