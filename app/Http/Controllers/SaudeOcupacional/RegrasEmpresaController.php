<?php

namespace App\Http\Controllers\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Regras\PreviaImpactoRegra;
use App\Domain\SaudeOcupacional\Regras\RegrasEmpresaService;
use App\Domain\SaudeOcupacional\Regras\SimuladorVencimento;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaudeOcupacional\SalvarRegraRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Configurações → Regras da empresa. Controller só orquestra; regras no domínio.
 */
class RegrasEmpresaController extends Controller
{
    public function __construct(
        private readonly RegrasEmpresaService $regras,
        private readonly PreviaImpactoRegra $previa,
        private readonly SimuladorVencimento $simulador
    ) {
    }

    public function index()
    {
        return view('g.saude-ocupacional.regras.index');
    }

    public function dados(): JsonResponse
    {
        $empresaId = $this->empresaId();

        return response()->json([
            'regras' => $this->regras->listar($empresaId),
            'opcoes' => $this->regras->opcoes($empresaId),
        ]);
    }

    public function store(SalvarRegraRequest $request): JsonResponse
    {
        return response()->json($this->regras->salvar(
            $this->empresaId(),
            null,
            $request->validated(),
            $request->input('modo_aplicacao'),
            $request->input('data_aplicacao'),
            $request->user()
        ), 201);
    }

    public function update(SalvarRegraRequest $request, int $id): JsonResponse
    {
        return response()->json($this->regras->salvar(
            $this->empresaId(),
            $id,
            $request->validated(),
            $request->input('modo_aplicacao'),
            $request->input('data_aplicacao'),
            $request->user()
        ));
    }

    public function ativo(Request $request, int $id): JsonResponse
    {
        $dados = $request->validate([
            'ativo' => ['required', 'boolean'],
            'modo_aplicacao' => ['required', 'in:futuro,retroativo,a_partir_de'],
            'data_aplicacao' => ['nullable', 'string', 'max:10'],
        ]);

        return response()->json($this->regras->alterarAtivo(
            $this->empresaId(),
            $id,
            (bool) $dados['ativo'],
            $dados['modo_aplicacao'],
            $dados['data_aplicacao'] ?? null,
            $request->user()
        ));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $dados = $request->validate([
            'modo_aplicacao' => ['required', 'in:futuro,retroativo,a_partir_de'],
            'data_aplicacao' => ['nullable', 'string', 'max:10'],
        ]);

        return response()->json($this->regras->excluir(
            $this->empresaId(),
            $id,
            $dados['modo_aplicacao'],
            $dados['data_aplicacao'] ?? null,
            $request->user()
        ));
    }

    public function previa(SalvarRegraRequest $request): JsonResponse
    {
        $empresaId = $this->empresaId();
        $regraId = $request->integer('regra_id') ?: null;
        $normalizados = $this->regras->normalizar($empresaId, $request->validated());

        return response()->json($this->previa->calcular(
            $empresaId,
            $this->regras->comoRegraPeriodicidade($normalizados, $regraId),
            $regraId
        ));
    }

    /** Prévia de desativar/excluir uma regra existente. */
    public function previaRemocao(int $id): JsonResponse
    {
        return response()->json($this->previa->calcular($this->empresaId(), null, $id));
    }

    public function historico(Request $request): JsonResponse
    {
        return response()->json($this->regras->historico($this->empresaId(), (int) $request->input('pages', 15)));
    }

    public function colaboradores(Request $request): JsonResponse
    {
        $termo = (string) $request->validate(['busca' => ['required', 'string', 'max:100']])['busca'];

        return response()->json(['itens' => $this->simulador->buscar($this->empresaId(), $termo)]);
    }

    public function simular(int $feedbackId): JsonResponse
    {
        return response()->json($this->simulador->simular($this->empresaId(), $feedbackId));
    }

    private function empresaId(): int
    {
        return (int) auth()->user()->empresa_id;
    }
}
