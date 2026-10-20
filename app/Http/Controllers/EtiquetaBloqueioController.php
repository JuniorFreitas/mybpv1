<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AutorizaTemplateTreinamentoGlobal;
use App\Models\EtiquetaBloqueio;
use App\Services\Treinamento\CarteiraMontagem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class EtiquetaBloqueioController extends Controller
{
    use AutorizaTemplateTreinamentoGlobal;

    public function index()
    {
        $this->autorizarTemplateGlobal();

        return redirect()->route('g.carteirastreinamento.carteirastreinamento.index', [
            'aba' => 'etiquetas',
        ]);
    }

    /**
     * Combo do Padrão de treinamento — leitura para qualquer tenant (globais + próprias).
     */
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
        $this->autorizarTemplateGlobal();

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
        $this->autorizarTemplateGlobal();
        $empresaId = $this->resolverEmpresaIdParaStore($request);

        $validated = $this->validar($request);
        $validated['empresa_id'] = $empresaId;
        $validated['ativo'] = $request->boolean('ativo', true);
        $validated['slug'] = $this->gerarSlugUnico('etiquetas_bloqueio', $validated['nome'], $empresaId);

        EtiquetaBloqueio::create($validated);

        return response()->json([], 201);
    }

    public function clonar(int $id): JsonResponse
    {
        $this->autorizarTemplateGlobal();
        $origem = EtiquetaBloqueio::findOrFail($id);
        $this->autorizarEscritaTemplate($origem->empresa_id);

        $nome = $this->nomeClone($origem->nome);

        EtiquetaBloqueio::create([
            'empresa_id' => $origem->empresa_id,
            'nome' => $nome,
            'slug' => $this->gerarSlugUnico('etiquetas_bloqueio', $nome, $origem->empresa_id),
            'ativo' => false,
            'ramal_emergencia' => $origem->ramal_emergencia,
            'bloqueio_texto_nao_use' => $origem->bloqueio_texto_nao_use,
            'bloqueio_texto_demissao' => $origem->bloqueio_texto_demissao,
            'bloqueio_texto_cuidado' => $origem->bloqueio_texto_cuidado,
            'bloqueio_texto_homens_trabalhando' => $origem->bloqueio_texto_homens_trabalhando,
            'identificador' => $origem->identificador ?: CarteiraMontagem::IDENTIFICADOR_CHAPA,
            'blade' => $origem->blade,
        ]);

        return response()->json([], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->autorizarTemplateGlobal();
        $etiqueta = EtiquetaBloqueio::findOrFail($id);
        $this->autorizarEscritaTemplate($etiqueta->empresa_id);

        $validated = $this->validar($request);
        $validated['ativo'] = $request->boolean('ativo', true);
        $etiqueta->update($validated);

        return response()->json([], 201);
    }

    private function validar(Request $request): array
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ativo' => 'boolean',
            'ramal_emergencia' => 'nullable|string|max:50',
            'bloqueio_texto_nao_use' => 'nullable|string|max:500',
            'bloqueio_texto_demissao' => 'nullable|string|max:500',
            'bloqueio_texto_cuidado' => 'nullable|string|max:255',
            'bloqueio_texto_homens_trabalhando' => 'nullable|string|max:500',
            'identificador' => 'nullable|string|in:' . implode(',', CarteiraMontagem::identificadores()),
            'blade' => 'nullable|string|max:80',
        ]);
        $validated['identificador'] = CarteiraMontagem::normalizarIdentificador($validated['identificador'] ?? null);
        $validated['blade'] = CarteiraMontagem::normalizarBlade($validated['blade'] ?? null);

        return $validated;
    }
}
