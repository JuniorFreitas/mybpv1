<?php

namespace App\Http\Controllers;

use App\Domain\Exames\Services\ExameTipoAdminService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExameTipoController extends Controller
{
    public function __construct(private readonly ExameTipoAdminService $service)
    {
    }

    public function index(): View
    {
        return view('g.cadastros.exames.index');
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $dados = $this->validar($request);
        if ($dados instanceof JsonResponse) {
            return $dados;
        }

        try {
            $tipo = $this->service->criar($dados, $this->empresaIdObrigatoria());

            return response()->json($tipo, 201);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            \Log::error('EXAME TIPO STORE: '.$e->getMessage());

            return response()->json(['msg' => 'Houve um erro, tente novamente.'], 400);
        }
    }

    public function edit($id): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        try {
            $tipos = $this->service->listar($this->empresaId(), ['campoBusca' => (string) $id], 1, 1);
            $tipo = collect($tipos->items())->first(fn ($t) => (int) $t->id === (int) $id);
            if (!$tipo) {
                $tipo = \App\Models\ExameTipo::query()
                    ->visivelParaEmpresa($this->empresaId())
                    ->where('id', $id)
                    ->first();
            }
            if (!$tipo) {
                return response()->json(['msg' => 'Tipo não encontrado.'], 404);
            }

            return response()->json($tipo);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Tipo não encontrado.'], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $dados = $this->validar($request);
        if ($dados instanceof JsonResponse) {
            return $dados;
        }

        try {
            $tipo = $this->service->atualizar((int) $id, $dados, $this->empresaId());

            return response()->json($tipo, 200);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            \Log::error('EXAME TIPO UPDATE: '.$e->getMessage());

            return response()->json(['msg' => 'Houve um erro, tente novamente.'], 400);
        }
    }

    public function atualizar(Request $request): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $porPagina = (int) $request->get('porPagina', $request->get('pages', 20));
        $page = (int) $request->get('page', 1);

        $resultado = $this->service->listar(
            $this->empresaId(),
            [
                'campoBusca' => $request->get('campoBusca'),
                'campoStatus' => $request->get('campoStatus'),
                'campoEscopo' => $request->get('campoEscopo'),
            ],
            $porPagina,
            $page
        );

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['items' => $resultado->items()],
        ]);
    }

    public function ativaDesativa($id): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        try {
            $tipo = \App\Models\ExameTipo::query()
                ->visivelParaEmpresa($this->empresaId())
                ->where('id', $id)
                ->firstOrFail();

            if ($tipo->empresa_id === null) {
                return response()->json(['msg' => 'Tipos globais não podem ser desativados por aqui. Clone para a empresa.'], 400);
            }

            $tipo->update(['ativo' => !$tipo->ativo]);

            return response()->json(['ativo' => $tipo->ativo], 200);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Tipo não encontrado.'], 404);
        }
    }

    public function vincularFormularios(Request $request, $id): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        try {
            $tipo = $this->service->vincularFormularios(
                (int) $id,
                $this->empresaId(),
                $request->input('formulario_encaminhamento_id'),
                $request->input('formulario_resultado_id')
            );

            return response()->json($tipo);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function listarAtivos(): JsonResponse
    {
        return response()->json($this->service->listarAtivosParaOperacional($this->empresaId()));
    }

    private function validar(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'ativo' => 'nullable|boolean',
            'ordem' => 'nullable|integer|min:0',
            'formulario_encaminhamento_id' => 'nullable|integer',
            'formulario_resultado_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg' => 'Erro de validação',
                'erros' => $validator->errors(),
            ], 400);
        }

        $dados = $validator->validated();
        $dados['ativo'] = $request->boolean('ativo', true);

        return $dados;
    }

    private function empresaId(): ?int
    {
        $id = auth()->user()->empresa_id ?? null;

        return $id !== null ? (int) $id : null;
    }

    private function empresaIdObrigatoria(): int
    {
        $id = $this->empresaId();
        if (!$id) {
            throw new DomainException('Usuário sem empresa vinculada.');
        }

        return $id;
    }
}
