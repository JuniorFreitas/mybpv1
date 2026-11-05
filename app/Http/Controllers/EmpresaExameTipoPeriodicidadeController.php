<?php

namespace App\Http\Controllers;

use App\Services\ControleExames\AsoPeriodicidadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmpresaExameTipoPeriodicidadeController extends Controller
{
    public function __construct(private AsoPeriodicidadeService $service)
    {
    }

    public function index(): View
    {
        $this->authorize('cadastro_empresa_exame');

        return view('g.cadastros.aso-periodicidade.index');
    }

    public function listar(): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        return response()->json([
            'itens' => $this->service->listarParaEmpresa((int) auth()->user()->empresa_id),
        ]);
    }

    public function historico(Request $request): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $porPagina = max(5, min(50, (int) $request->input('pages', 15)));
        $paginator = $this->service->listarHistorico((int) auth()->user()->empresa_id, $porPagina);

        return response()->json($paginator);
    }

    public function salvar(Request $request): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $dados = $request->validate([
            'itens' => 'required|array|min:1',
            'itens.*.exame_tipo_id' => 'required|integer',
            'itens.*.unidade' => 'required|in:anos,dias',
            'itens.*.valor' => 'required|integer|min:1',
            'modo_aplicacao' => 'nullable|in:retroativo,futuro,a_partir_de',
            'data_aplicacao' => 'nullable|string',
        ]);

        try {
            $resultado = $this->service->salvar(
                (int) auth()->user()->empresa_id,
                $dados['itens'],
                $dados['modo_aplicacao'] ?? null,
                $dados['data_aplicacao'] ?? null,
                auth()->user()
            );

            return response()->json($resultado);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => collect($e->errors())->flatten()->first() ?? 'Dados inválidos.',
                'erros' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Erro ao salvar periodicidade ASO: ' . $e->getMessage(), [
                'empresa_id' => auth()->user()->empresa_id ?? null,
                'user_id' => auth()->id(),
            ]);

            return response()->json(['msg' => 'Houve um erro ao salvar a periodicidade.'], 400);
        }
    }
}
