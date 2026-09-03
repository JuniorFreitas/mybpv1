<?php

namespace App\Http\Controllers;

use App\Services\Preadmissao\DocumentoPreadmissaoCadastroService;
use App\Models\User;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DocumentoPreadmissaoController extends Controller
{
    public function __construct(private readonly DocumentoPreadmissaoCadastroService $cadastroService)
    {
    }

    public function index(): View
    {
        $this->authorize('cadastro_documentos_preadmissao');

        return view('g.cadastros.documentospreadmissao.index', [
            'canInsert' => Gate::allows('cadastro_documentos_preadmissao_insert'),
            'canUpdate' => Gate::allows('cadastro_documentos_preadmissao_update'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('cadastro_documentos_preadmissao_insert');

        $dadosValidados = \Validator::make($request->input(), $this->regrasValidacao());

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao cadastrar documento',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            $dados = $dadosValidados->validated();
            $dados['configuracoes'] = $request->input('configuracoes', []);
            $this->cadastroService->criar($dados, $this->empresaIdObrigatoria());

            return response()->json([], 201);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logErro('STORE DOCUMENTO PREADMISSAO', $e);

            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit($documento): JsonResponse
    {
        $this->authorize('cadastro_documentos_preadmissao');

        try {
            $registro = $this->cadastroService->buscarParaEdicao((int) $documento, $this->empresaIdObrigatoria());

            return response()->json($registro);
        } catch (DomainException $e) {
            $status = $e->getMessage() === 'Documento não encontrado.' ? 404 : 400;

            return response()->json(['msg' => $e->getMessage()], $status);
        }
    }

    public function update(Request $request, $documento): JsonResponse
    {
        $this->authorize('cadastro_documentos_preadmissao_update');

        $dadosValidados = \Validator::make($request->input(), $this->regrasValidacao());

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao editar documento',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            $dados = $dadosValidados->validated();
            $dados['configuracoes'] = $request->input('configuracoes', []);
            $this->cadastroService->atualizar((int) $documento, $dados, $this->empresaIdObrigatoria());

            return response()->json([], 201);
        } catch (DomainException $e) {
            $status = $e->getMessage() === 'Documento não encontrado.' ? 404 : 400;

            return response()->json(['msg' => $e->getMessage()], $status);
        } catch (\Exception $e) {
            $this->logErro('UPDATE DOCUMENTO PREADMISSAO', $e);

            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizar(Request $request): JsonResponse
    {
        $this->authorize('cadastro_documentos_preadmissao');

        $empresaId = $this->empresaIdObrigatoria();
        $porPagina = (int) $request->get('porPagina', $request->get('pages', 20));
        $page = (int) $request->get('page', 1);

        $resultado = $this->cadastroService->listar(
            $empresaId,
            [
                'campoBusca' => $request->get('campoBusca'),
                'campoStatus' => $request->get('campoStatus'),
                'campoCategoria' => $request->get('campoCategoria'),
            ],
            $porPagina,
            $page
        );

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'categorias' => $this->cadastroService->listarCategorias($empresaId),
                'empresa_id' => $empresaId,
            ],
        ], 200);
    }

    public function ativaDesativa(Request $request, $documento): JsonResponse
    {
        $this->authorize('cadastro_documentos_preadmissao_update');

        try {
            $registro = $this->cadastroService->alternarAtivo((int) $documento, $this->empresaIdObrigatoria());

            return response()->json(['ativo' => $registro->ativo], 201);
        } catch (DomainException $e) {
            $status = $e->getMessage() === 'Documento não encontrado.' ? 404 : 400;

            return response()->json(['msg' => $e->getMessage()], $status);
        } catch (\Exception $e) {
            $this->logErro('ATIVA DESATIVA DOCUMENTO PREADMISSAO', $e);

            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function regrasValidacao(): array
    {
        return [
            'label' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'categoria_id' => 'nullable|integer',
            'categoria_nova' => 'nullable|string|max:255',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'required|boolean',
            'configuracoes' => 'nullable|array',
        ];
    }

    private function empresaId(): ?int
    {
        $empresaId = auth()->user()->empresa_id ?? null;

        return $empresaId !== null ? (int) $empresaId : null;
    }

    private function empresaIdObrigatoria(): int
    {
        $empresaId = $this->empresaId();
        if (!$empresaId) {
            throw new DomainException('Usuário sem empresa vinculada.');
        }

        return $empresaId;
    }

    private function logErro(string $contexto, \Exception $e): void
    {
        $usuario = User::find(auth()->id());
        $nome = $usuario?->nome ?? 'desconhecido';
        \Log::debug("error {$contexto}: {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: {$nome}");
    }
}
