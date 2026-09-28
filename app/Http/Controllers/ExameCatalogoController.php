<?php

namespace App\Http\Controllers;

use App\Domain\Exames\Services\ExameCatalogoService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExameCatalogoController extends Controller
{
    public function __construct(private readonly ExameCatalogoService $service)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $dados = $this->validar($request);
        if ($dados instanceof JsonResponse) {
            return $dados;
        }

        try {
            $exame = $this->service->criar($dados, $this->empresaIdObrigatoria());

            return response()->json($exame, 201);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            \Log::error('EXAME CATALOGO STORE: '.$e->getMessage());

            return response()->json(['msg' => 'Houve um erro, tente novamente.'], 400);
        }
    }

    public function edit($id): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $exame = \App\Models\Exame::query()
            ->where('id', $id)
            ->where('empresa_id', $this->empresaId())
            ->first();

        if (!$exame) {
            return response()->json(['msg' => 'Exame não encontrado.'], 404);
        }

        return response()->json($exame);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $dados = $this->validar($request);
        if ($dados instanceof JsonResponse) {
            return $dados;
        }

        try {
            $exame = $this->service->atualizar((int) $id, $dados, $this->empresaIdObrigatoria());

            return response()->json($exame);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            \Log::error('EXAME CATALOGO UPDATE: '.$e->getMessage());

            return response()->json(['msg' => 'Houve um erro, tente novamente.'], 400);
        }
    }

    public function atualizar(Request $request): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $porPagina = (int) $request->get('porPagina', $request->get('pages', 20));
        $page = (int) $request->get('page', 1);

        $resultado = $this->service->listar(
            $this->empresaIdObrigatoria(),
            [
                'campoBusca' => $request->get('campoBusca'),
                'campoStatus' => $request->get('campoStatus'),
                'exame_tipo_id' => $request->get('exame_tipo_id'),
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
            $exame = \App\Models\Exame::query()
                ->where('id', $id)
                ->where('empresa_id', $this->empresaId())
                ->firstOrFail();
            $exame->update(['ativo' => !$exame->ativo]);

            return response()->json(['ativo' => $exame->ativo]);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Exame não encontrado.'], 404);
        }
    }

    public function listarAtivos(Request $request): JsonResponse
    {
        $itens = $this->service->listarAtivos(
            $this->empresaIdObrigatoria(),
            $request->get('exame_tipo_id') ? (int) $request->get('exame_tipo_id') : null
        );

        return response()->json($itens);
    }

    private function validar(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'exame_tipo_id' => 'nullable|integer',
            'ativo' => 'nullable|boolean',
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
