<?php

namespace App\Http\Controllers\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Pgr\PgrInventarioService;
use App\Domain\SaudeOcupacional\Pgr\RiscosCatalogoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Programas → PGR: riscos por cargo, versões e catálogo de riscos. Regras no domínio.
 */
class PgrController extends Controller
{
    public function __construct(
        private readonly PgrInventarioService $pgr,
        private readonly RiscosCatalogoService $riscos
    ) {
    }

    public function index()
    {
        return view('g.saude-ocupacional.pgr.index');
    }

    public function dados(): JsonResponse
    {
        $empresaId = $this->empresaId();

        return response()->json([
            'versoes' => $this->pgr->listarVersoes($empresaId),
            'opcoes' => $this->pgr->opcoes($empresaId),
        ]);
    }

    public function detalhe(int $id): JsonResponse
    {
        return response()->json($this->pgr->detalhe($this->empresaId(), $id));
    }

    public function criarRascunho(Request $request): JsonResponse
    {
        $rascunho = $this->pgr->criarRascunho($this->empresaId(), $request->user());

        return response()->json(['id' => $rascunho->id, 'mensagem' => 'Versão ' . $rascunho->versao . ' criada em rascunho.'], 201);
    }

    public function cabecalho(Request $request, int $id): JsonResponse
    {
        $dados = $request->validate([
            'responsavel_tecnico' => ['nullable', 'string', 'max:150'],
            'responsavel_registro' => ['nullable', 'string', 'max:60'],
            'observacao' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->pgr->salvarCabecalho($this->empresaId(), $id, $dados);

        return response()->json(['mensagem' => 'Dados da versão salvos.']);
    }

    public function salvarCargo(Request $request, int $id, int $vagaId): JsonResponse
    {
        $dados = $request->validate([
            'ausencia_risco' => ['required', 'boolean'],
            'observacao' => ['nullable', 'string', 'max:500'],
            'riscos' => ['nullable', 'array', 'max:50'],
            'riscos.*.exame_risco_id' => ['required', 'integer', 'min:1'],
            'riscos.*.fonte_geradora' => ['nullable', 'string', 'max:200'],
            'riscos.*.intensidade' => ['nullable', 'string', 'max:100'],
            'riscos.*.medidas_controle' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->pgr->salvarCargo($this->empresaId(), $id, $vagaId, $dados);

        return response()->json(['mensagem' => 'Riscos do cargo salvos.']);
    }

    public function previa(int $id): JsonResponse
    {
        return response()->json($this->pgr->previaPublicacao($this->empresaId(), $id));
    }

    public function publicar(Request $request, int $id): JsonResponse
    {
        $dados = $request->validate([
            'vigencia_inicio' => ['required', 'string', 'max:10'],
            'modo_aplicacao' => ['required', 'in:futuro,retroativo,a_partir_de'],
            'data_aplicacao' => ['nullable', 'string', 'max:10'],
        ]);

        return response()->json($this->pgr->publicar(
            $this->empresaId(),
            $id,
            $dados['vigencia_inicio'],
            $dados['modo_aplicacao'],
            $dados['data_aplicacao'] ?? null,
            $request->user()
        ));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->pgr->excluirRascunho($this->empresaId(), $id, $request->user());

        return response()->json(['mensagem' => 'Rascunho excluído.']);
    }

    public function salvarRisco(Request $request, ?int $riscoId = null): JsonResponse
    {
        $dados = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'risco_tipo' => ['required', 'integer'],
            'ativo' => ['sometimes', 'boolean'],
        ]);
        $risco = $this->riscos->salvar($this->empresaId(), $riscoId, $dados);

        return response()->json(['id' => $risco->id, 'mensagem' => 'Risco salvo.'], $riscoId ? 200 : 201);
    }

    private function empresaId(): int
    {
        return (int) auth()->user()->empresa_id;
    }
}
