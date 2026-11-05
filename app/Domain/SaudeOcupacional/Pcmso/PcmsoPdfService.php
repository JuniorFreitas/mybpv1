<?php

namespace App\Domain\SaudeOcupacional\Pcmso;

use App\Models\Sistema;
use App\Services\Demissao\ComunicadoDispensaSemJustaCausaPdfService;
use Barryvdh\DomPDF\PDF;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * PDF formal do PCMSO (versão): identificação, coordenador, vigência e exames por cargo.
 * Lista só cargos com exames ou riscos. Sem dado de saúde de colaborador.
 */
class PcmsoPdfService
{
    public function __construct(
        private readonly PcmsoService $pcmso,
        private readonly ComunicadoDispensaSemJustaCausaPdfService $padrao
    ) {
    }

    public function gerar(int $empresaId, int $versaoId, ?string $solicitante): PDF
    {
        $detalhe = $this->pcmso->detalhe($empresaId, $versaoId);
        $versao = $this->pcmso->versao($empresaId, $versaoId);
        $versao->load('Coordenador');

        $dados = [
            'dados_empresa' => Sistema::getEmpresa($empresaId),
            'programa' => (string) DB::table('pcmsos')->where('id', $versao->pcmso_id)->value('label'),
            'versao' => $detalhe['versao'],
            'coordenador' => $versao->Coordenador,
            'pgr_vigente' => $detalhe['pgr_vigente'],
            'cargos' => array_values(array_filter($detalhe['cargos'], fn ($c) => $c['exames'] !== [] || $c['riscos'] !== [])),
            'data_emissao' => CarbonImmutable::now()->format('d/m/Y'),
        ];

        /** @var PDF $pdf */
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.saude-ocupacional.pcmso', ['dados' => $dados]);
        $pdf->setPaper('a4', 'portrait');

        return $this->padrao->aplicarPadraoDocumento($pdf, $solicitante);
    }
}
