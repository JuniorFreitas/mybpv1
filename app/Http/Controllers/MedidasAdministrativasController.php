<?php

namespace App\Http\Controllers;

use App\Jobs\AssinaturaDigital\JobProcessarEnvioAssinatura;
use App\Models\Arquivo;
use App\Models\Sistema;
use App\Services\AssinaturaDigital\AssinaturaCotaService;
use App\Services\Historico\MedidaAdministrativaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

class MedidasAdministrativasController extends Controller
{
    public function __construct(
        protected MedidaAdministrativaService $medidaAdministrativaService
    ) {
    }

    public function medidasAdministrativasPDF($medida, $feedback_id)
    {
        $medida = $this->medidaAdministrativaService->buscarParaPDF($medida);

        if (!$medida) {
            return abort(404);
        }

        $dados = [
            'dados_empresa' => Sistema::getEmpresaFilialMatriz(
                $medida->Feedback->Admissao?->centro_custo_filial_id,
                $medida->Feedback->empresa_id
            ),
        ];

        $pdf = PDF::loadView('pdf.admissao.historico.medidasadministrativas.carta-advertencia', compact('medida', 'dados'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("carta_" . Str::slug($medida->tipo) . (new DataHora())->nomeUnico() . ".pdf");
    }

    /**
     * Envia medida administrativa (carta advertência) para assinatura digital.
     */
    public function enviarMedidaParaAssinatura(Request $request)
    {
        $request->validate([
            'medida_id' => 'required|integer',
            'signatarios' => 'required|array|min:1',
            'signatarios.*.email' => 'required|email',
            'signatarios.*.nome' => 'required|string|max:255',
            'signatarios.*.cpf' => 'required|string|min:11|max:14',
            'signatarios.*.user_id' => 'nullable|exists:users,id',
        ]);

        $medida = $this->medidaAdministrativaService->buscarParaPDF($request->medida_id);
        if (!$medida || $medida->Feedback->empresa_id != auth()->user()->empresa_id) {
            return response()->json(['success' => false, 'message' => 'Medida não encontrada.'], 404);
        }

        $empresaId = auth()->user()->empresa_id;
        try {
            app(AssinaturaCotaService::class)->validarDisponibilidadeOrFail($empresaId);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
        JobProcessarEnvioAssinatura::dispatch(
            JobProcessarEnvioAssinatura::TIPO_MEDIDA,
            $empresaId,
            auth()->id(),
            ['medida_id' => (int) $request->medida_id],
            $request->signatarios
        );

        return response()->json([
            'success' => true,
            'message' => 'Solicitação recebida. O documento será processado e enviado para assinatura.',
        ], 202);
    }

    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_MEDIDAS);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_MEDIDAS, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_MEDIDAS, $arquivo);
    }

    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_MEDIDAS, $arquivo);
    }

    public function removerMedidaAdministrativa(Request $request)
    {
        $this->authorize('privilegio_gestao_rh');
        $dados = $request->input();

        try {
            $medidaId = $dados['medida_id'] ?? $dados['id'];
            $motivo = $dados['motivo'] ?? null;

            $this->medidaAdministrativaService->removerMedidaAdministrativa($medidaId, $motivo);

            return response()->json([], 201);
        } catch (\Exception $e) {
            $msg = "error HISTÓRICO - REMOVER MEDIDA ADMINISTRATIVA: {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $e->getMessage() === 'Medida administrativa não encontrada!' ? $e->getMessage() : 'Houve um erro por favor tente novamente!'], 400);
        }
    }
}
