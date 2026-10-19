<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AutorizaTemplateTreinamentoGlobal;
use App\Models\CarteiraTreinamento;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CarteiraTreinamentoController extends Controller
{
    use AutorizaTemplateTreinamentoGlobal;

    public function index()
    {
        return view('g.cadastros.carteirastreinamento.index');
    }

    public function listar(Request $request): JsonResponse
    {
        $empresaId = $this->empresaIdUsuario();
        $apenasProprias = $request->boolean('apenas_proprias');
        $comInativos = $request->boolean('com_inativos');

        $query = CarteiraTreinamento::query()->orderBy('nome');
        if (!$comInativos) {
            $query->ativos();
        }

        if ($apenasProprias) {
            $query->where('empresa_id', $empresaId);
        } else {
            $query->visiveisParaEmpresa($empresaId);
        }

        return response()->json($query->get([
            'id', 'empresa_id', 'nome', 'slug', 'ativo', 'cabecalho_img', 'verso_img',
        ]));
    }

    /**
     * Empresas ativas para combobox de template customizado (admin 100).
     */
    public function empresas(): JsonResponse
    {
        $this->autorizarTemplateGlobal();

        $empresas = Cliente::query()
            ->where('ativo', true)
            ->orderBy('nome_fantasia')
            ->orderBy('razao_social')
            ->orderBy('apelido')
            ->get(['id', 'apelido', 'nome_fantasia', 'razao_social', 'cnpj']);

        return response()->json($empresas);
    }

    public function atualizar(Request $request): JsonResponse
    {
        $query = CarteiraTreinamento::query()
            ->with(['Empresa:id,apelido,nome_fantasia,razao_social'])
            ->orderBy('nome');

        $this->aplicarFiltroEscopoListagem($query, $request);

        if ($request->filled('campoBusca')) {
            $busca = trim((string) $request->input('campoBusca'));
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('slug', 'like', "%{$busca}%");
                if (ctype_digit($busca)) {
                    $q->orWhere('id', (int) $busca);
                }
            });
        }

        if ($request->filled('campoStatus')) {
            $query->where('ativo', filter_var($request->input('campoStatus'), FILTER_VALIDATE_BOOLEAN));
        }

        $resultado = $query->paginate((int) $request->input('per_page', 50));

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['items' => $resultado->items()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $empresaId = $this->resolverEmpresaIdParaStore($request);

        $validated = $this->validar($request, $empresaId);
        $validated['empresa_id'] = $empresaId;
        $validated['ativo'] = $request->boolean('ativo', true);
        $validated['slug'] = filled($validated['slug'] ?? null)
            ? $validated['slug']
            : Str::slug($validated['nome']);

        CarteiraTreinamento::create($validated);

        return response()->json([], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $carteira = CarteiraTreinamento::findOrFail($id);
        $this->autorizarEscritaTemplate($carteira->empresa_id);

        $validated = $this->validar($request, $carteira->empresa_id, $id);
        $validated['ativo'] = $request->boolean('ativo', true);
        $carteira->update($validated);

        return response()->json([], 201);
    }

    private function validar(Request $request, ?int $empresaId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nome' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('carteiras_treinamento', 'slug')
                    ->where(fn ($q) => $empresaId === null ? $q->whereNull('empresa_id') : $q->where('empresa_id', $empresaId))
                    ->ignore($ignoreId),
            ],
            'ativo' => 'boolean',
            'cabecalho_img' => 'nullable|string|max:500',
            'verso_img' => 'nullable|string|max:500',
        ]);
    }
}
