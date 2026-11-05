<?php

namespace App\Http\Controllers\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Aso\AsoDocumentoService;
use App\Domain\SaudeOcupacional\Aso\HistoricoAsoService;
use App\Http\Controllers\Controller;
use App\Models\Sistema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * ASO e Exames → Histórico de ASO: linha do tempo do colaborador, emissão do ASO (NR-7), PDF e assinatura.
 */
class HistoricoAsoController extends Controller
{
    public function __construct(
        private readonly HistoricoAsoService $historico,
        private readonly AsoDocumentoService $aso
    ) {
    }

    public function index()
    {
        return view('g.saude-ocupacional.historico.index');
    }

    public function colaboradores(Request $request): JsonResponse
    {
        $termo = (string) $request->validate(['busca' => ['required', 'string', 'max:100']])['busca'];

        return response()->json(['itens' => $this->historico->buscar($this->empresaId(), $termo)]);
    }

    public function show(Request $request, int $feedbackId): JsonResponse
    {
        return response()->json($this->historico->historico($this->empresaId(), $feedbackId, $request->user()) + [
            'pode_emitir' => Gate::allows('admissao_controle_exames'),
            'assinatura_habilitada' => Sistema::assinaturaDigitalHabilitada($this->empresaId()),
        ]);
    }

    public function preparar(int $resultadoId): JsonResponse
    {
        return response()->json($this->aso->preparar($this->empresaId(), $resultadoId));
    }

    public function emitir(Request $request, int $resultadoId): JsonResponse
    {
        $dados = $request->validate([
            'saude_medico_id' => ['required', 'integer'],
            'exames' => ['present', 'array', 'max:' . AsoDocumentoService::MAX_EXAMES],
            'exames.*.label' => ['required', 'string', 'max:120'],
            'exames.*.data' => ['nullable', 'string', 'max:10'],
        ], ['saude_medico_id.required' => 'Escolha o médico examinador.']);

        $this->aso->emitir($this->empresaId(), $resultadoId, $dados, $request->user());

        return response()->json(['mensagem' => 'ASO emitido.']);
    }

    public function pdf(Request $request, int $resultadoId)
    {
        return $this->aso->gerarPdf($this->empresaId(), $resultadoId, (string) ($request->user()?->nome ?? ''))
            ->stream('ASO ' . $resultadoId . '.pdf');
    }

    public function assinar(Request $request, int $resultadoId): JsonResponse
    {
        $r = $this->aso->enviarParaAssinatura($this->empresaId(), $resultadoId, $request->user());

        return response()->json($r + ['mensagem' => 'ASO enviado para assinatura do médico examinador e ciência do colaborador.']);
    }

    private function empresaId(): int
    {
        return (int) auth()->user()->empresa_id;
    }
}
