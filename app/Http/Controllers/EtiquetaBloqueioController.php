<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AutorizaTemplateTreinamentoGlobal;
use App\Models\EtiquetaBloqueio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EtiquetaBloqueioController extends Controller
{
    use AutorizaTemplateTreinamentoGlobal;

    public function index()
    {
        return redirect()->route('g.carteirastreinamento.carteirastreinamento.index', [
            'aba' => 'etiquetas',
        ]);
    }

    public function listar(Request $request): JsonResponse
    {
        $empresaId = $this->empresaIdUsuario();
        $apenasProprias = $request->boolean('apenas_proprias');
        $comInativos = $request->boolean('com_inativos');

        $query = EtiquetaBloqueio::query()->orderBy('nome');
        if (!$comInativos) {
            $query->ativos();
        }

        if ($apenasProprias) {
            $query->where('empresa_id', $empresaId);
        } else {
            $query->visiveisParaEmpresa($empresaId);
        }

        return response()->json($query->get());
    }

    public function atualizar(Request $request): JsonResponse
    {
        $query = EtiquetaBloqueio::query()
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

        EtiquetaBloqueio::create($validated);

        return response()->json([], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $etiqueta = EtiquetaBloqueio::findOrFail($id);
        $this->autorizarEscritaTemplate($etiqueta->empresa_id);

        $validated = $this->validar($request, $etiqueta->empresa_id, $id);
        $validated['ativo'] = $request->boolean('ativo', true);
        $etiqueta->update($validated);

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
                Rule::unique('etiquetas_bloqueio', 'slug')
                    ->where(fn ($q) => $empresaId === null ? $q->whereNull('empresa_id') : $q->where('empresa_id', $empresaId))
                    ->ignore($ignoreId),
            ],
            'ativo' => 'boolean',
            'ramal_emergencia' => 'nullable|string|max:50',
            'bloqueio_texto_nao_use' => 'nullable|string|max:500',
            'bloqueio_texto_demissao' => 'nullable|string|max:500',
            'bloqueio_texto_cuidado' => 'nullable|string|max:255',
            'bloqueio_texto_homens_trabalhando' => 'nullable|string|max:500',
        ]);
    }
}
