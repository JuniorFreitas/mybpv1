<?php

namespace App\Services\Demissao;

use App\Models\Demissao;
use App\Models\DemissaoPrevista;
use App\Models\Sistema;
use App\Models\User;
use MasterTag\DataHora;

class ComunicadoDispensaSemJustaCausaPdfService
{
    public const VIEW = 'pdf.demissao.comunicado_dispensa_sem_justa_causa';

    public function fromDemissao(Demissao $dados, ?string $solicitante = null): array
    {
        $admissao = optional($dados->Feedback)->Admissao;
        $empresaId = (int) (optional($dados->Feedback)->empresa_id
            ?? optional($dados->User)->empresa_id
            ?? auth()->user()?->empresa_id
            ?? 0);
        $filialId = $admissao->centro_custo_filial_id ?? null;
        $dadosEmpresa = $this->resolverDadosEmpresa($filialId, $empresaId);

        $tipoAviso = trim((string) ($dados->tipoAviso->descricao ?? ''));
        $dataDesligamento = $this->rawDate($dados, 'data_desmobilizacao');
        $dataAdmissao = $this->rawDate($admissao, 'data_admissao');

        return $this->buildPayload(
            title: $dados->motivoRescisao->descricao ?? 'Demissão sem justa causa',
            empregadoNome: $dados->Feedback->Curriculo->nome ?? '—',
            tipoAviso: $tipoAviso,
            dataDesligamento: $dataDesligamento,
            dataAdmissao: $dataAdmissao,
            dadosEmpresa: $dadosEmpresa,
            solicitante: $solicitante,
        );
    }

    public function fromDemissaoPrevista(DemissaoPrevista $demissaoPrevista, ?string $solicitante = null): array
    {
        $colaborador = $demissaoPrevista->Colaborador;
        $admissao = optional(optional($colaborador)->Feedback)->Admissao;
        $empresaId = (int) ($demissaoPrevista->empresa_id
            ?? optional($colaborador)->empresa_id
            ?? auth()->user()?->empresa_id
            ?? 0);
        $filialId = $demissaoPrevista->centro_custo_filial_id
            ?? ($admissao->centro_custo_filial_id ?? null);
        $dadosEmpresa = $this->resolverDadosEmpresa($filialId, $empresaId);

        $tipoAviso = trim((string) ($demissaoPrevista->tipo_aviso ?? ''));
        $dataDesligamento = $this->rawDate($demissaoPrevista, 'data_demissao');
        $dataAdmissao = $this->rawDate($admissao, 'data_admissao');

        return $this->buildPayload(
            title: $demissaoPrevista->tipo_aviso ?? 'Aviso prévio',
            empregadoNome: $colaborador->nome ?? '—',
            tipoAviso: $tipoAviso,
            dataDesligamento: $dataDesligamento,
            dataAdmissao: $dataAdmissao,
            dadosEmpresa: $dadosEmpresa,
            solicitante: $solicitante,
        );
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    public function makePdf(array $viewData): \Barryvdh\DomPDF\PDF
    {
        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = app('dompdf.wrapper');
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('defaultFont', 'dejavu sans');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isJavascriptEnabled', false);
        $pdf->loadView(self::VIEW, $viewData);

        $linha1 = 'Esse documento foi gerado automaticamente'
            . (!empty($viewData['solicitante']) ? ' por ' . $viewData['solicitante'] : '')
            . ':';
        $agora = new DataHora();
        $linha2 = 'Sistema Integrado BPIN by MyBPIN em '
            . $agora->dataCompleta()
            . ' às '
            . $agora->horaCompleta()
            . '.';

        // Rodapé em todas as páginas via canvas (não usa position:fixed — evita quebrar o layout DomPDF)
        $pdf->setCallbacks([
            [
                'event' => 'end_document',
                'f' => function (
                    int $pageNumber,
                    int $pageCount,
                    \Dompdf\Canvas $canvas,
                    \Dompdf\FontMetrics $fontMetrics
                ) use ($linha1, $linha2): void {
                    $font = $fontMetrics->getFont('DejaVu Sans');
                    $size = 7;
                    $color = [0.25, 0.25, 0.25];
                    $pageWidth = $canvas->get_width();
                    $pageHeight = $canvas->get_height();
                    // Rodapé na margem inferior (~18mm), sem colar no texto
                    $y1 = $pageHeight - 40;
                    $y2 = $pageHeight - 29;

                    $w1 = $fontMetrics->getTextWidth($linha1, $font, $size);
                    $w2 = $fontMetrics->getTextWidth($linha2, $font, $size);
                    $canvas->text(($pageWidth - $w1) / 2, $y1, $linha1, $font, $size, $color);
                    $canvas->text(($pageWidth - $w2) / 2, $y2, $linha2, $font, $size, $color);
                },
            ],
        ]);

        return $pdf;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(
        string $title,
        string $empregadoNome,
        string $tipoAviso,
        mixed $dataDesligamento,
        mixed $dataAdmissao,
        array $dadosEmpresa,
        ?string $solicitante = null,
    ): array {
        $isIndenizado = str_contains(mb_strtolower($tipoAviso), 'indeniz');
        $dataExtenso = $dataDesligamento
            ? (new DataHora($dataDesligamento))->dataCompletaExt()
            : (new DataHora())->dataCompletaExt();
        $dataCiencia = $dataDesligamento
            ? (new DataHora($dataDesligamento))->dataCompleta()
            : (new DataHora())->dataCompleta();
        $diasAviso = $this->calcularDiasAviso($dataAdmissao, $dataDesligamento);
        $razaoSocial = (string) ($dadosEmpresa['razao_social'] ?? '—');
        $cidade = $this->formatarCidade($dadosEmpresa);

        if ($solicitante === null || $solicitante === '') {
            $solicitante = '';
            if (auth()->check()) {
                $solicitante = (string) (User::query()->select('nome')->find(auth()->id())?->nome ?? '');
            }
        }

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
            'dados_empresa' => $dadosEmpresa,
            'solicitante' => $solicitante,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolverDadosEmpresa(mixed $centroCustoFilialId, int $empresaId): array
    {
        if ($empresaId <= 0) {
            return [];
        }

        $dados = Sistema::getEmpresaFilialMatriz($centroCustoFilialId, $empresaId);
        if (!empty($dados)) {
            return $dados;
        }

        return Sistema::getEmpresa($empresaId) ?: [];
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

    /**
     * @param  array<string, mixed>  $dadosEmpresa
     */
    private function formatarCidade(array $dadosEmpresa): string
    {
        $endereco = (string) ($dadosEmpresa['endereco_completo'] ?? '');
        if ($endereco !== '' && preg_match('/,\s*([^,]+)-([A-Z]{2})\s*$/u', $endereco, $m)) {
            return trim($m[1]) . '-' . $m[2];
        }

        $municipio = trim((string) ($dadosEmpresa['municipio'] ?? ''));
        $uf = trim((string) ($dadosEmpresa['uf'] ?? ''));
        if ($municipio !== '') {
            return $uf !== '' ? "{$municipio}-{$uf}" : $municipio;
        }

        return 'São Luís-MA';
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
