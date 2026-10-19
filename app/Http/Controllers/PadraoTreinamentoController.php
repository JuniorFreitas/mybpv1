<?php

namespace App\Http\Controllers;

use App\Models\CarteiraTreinamento;
use App\Models\Cliente;
use App\Models\ClienteFilial;
use App\Models\EtiquetaBloqueio;
use App\Models\PadraoTreinamento;
use App\Models\PadraoTreinamentoLotacao;
use App\Models\Vencimento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PadraoTreinamentoController extends Controller
{
    public function index()
    {
        return view('g.cadastros.padroestreinamento.index');
    }

    /**
     * Lista padrões da empresa (cadastro de treinamento indústria / combos).
     */
    public function listar(Request $request): JsonResponse
    {
        $apenasAtivos = !$request->boolean('com_inativos');

        $query = PadraoTreinamento::query()->orderBy('nome');
        if ($apenasAtivos) {
            $query->ativos();
        }

        return response()->json($query->get(['id', 'nome', 'slug', 'ativo']));
    }

    /**
     * Padrões ativos da empresa (selects de admissão/carteira).
     * Query: is_matriz=1 e/ou cliente_filial_id=
     */
    public function habilitadosEmpresa(Request $request): JsonResponse
    {
        $query = PadraoTreinamento::query()->ativos()->orderBy('nome');

        if ($request->boolean('is_matriz')) {
            $query->paraLotacao(true);
        } elseif ($request->filled('cliente_filial_id')) {
            $query->paraLotacao(false, (int) $request->input('cliente_filial_id'));
        }

        $padroes = $query->get(['id', 'nome', 'slug', 'tem_carteira', 'tem_etiqueta_bloqueio']);

        return response()->json($padroes);
    }

    /**
     * Opções do formulário de padrão: lotações (Nome - CNPJ) e treinamentos da empresa.
     */
    public function opcoesFormulario(): JsonResponse
    {
        $empresaId = (int) auth()->user()->empresa_id;

        $empresa = Cliente::query()
            ->where('id', $empresaId)
            ->first(['id', 'cnpj', 'nome_fantasia', 'razao_social', 'apelido']);

        $lotacoes = [];
        if ($empresa) {
            $nomeMatriz = $empresa->nome_fantasia ?: $empresa->razao_social ?: $empresa->apelido ?: 'Empresa';
            $cnpjMatriz = (string) ($empresa->cnpj ?? '');
            $lotacoes[] = [
                'key' => 'matriz',
                'label' => $this->labelLotacaoCnpj($nomeMatriz, $cnpjMatriz),
                'meta' => 'Matriz',
                'is_matriz' => true,
                'cliente_filial_id' => null,
                'cnpj' => $cnpjMatriz,
                'nome' => $nomeMatriz,
            ];
        }

        $filiais = ClienteFilial::query()
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('id')
            ->get(['id', 'dados']);

        foreach ($filiais as $filial) {
            $dados = is_array($filial->dados) ? $filial->dados : (array) $filial->dados;
            $nome = $dados['nome_fantasia']
                ?? $dados['nome_fntasia']
                ?? $dados['razao_social']
                ?? ('Filial #'.$filial->id);
            $cnpj = (string) ($dados['cnpj'] ?? '');
            $lotacoes[] = [
                'key' => 'f:'.$filial->id,
                'label' => $this->labelLotacaoCnpj($nome, $cnpj),
                'meta' => 'Filial',
                'is_matriz' => false,
                'cliente_filial_id' => (int) $filial->id,
                'cnpj' => $cnpj,
                'nome' => $nome,
            ];
        }

        $vencimentos = Vencimento::query()
            ->where('empresa_id', $empresaId)
            ->whereNotNull('label')
            ->where('label', '!=', '')
            ->orderBy('label')
            ->get(['id', 'label', 'ativo'])
            ->map(fn (Vencimento $v) => [
                'id' => (int) $v->id,
                'label' => $v->label.($v->ativo ? '' : ' (inativo)'),
                'ativo' => (bool) $v->ativo,
            ])
            ->values();

        return response()->json([
            'lotacoes' => $lotacoes,
            'vencimentos' => $vencimentos,
        ]);
    }

    private function labelLotacaoCnpj(string $nome, string $cnpj): string
    {
        $nome = trim($nome);
        $cnpj = trim($cnpj);

        if ($nome !== '' && $cnpj !== '') {
            return "{$nome} - {$cnpj}";
        }

        return $nome !== '' ? $nome : ($cnpj !== '' ? $cnpj : 'Não informado');
    }

    public function atualizar(Request $request): JsonResponse
    {
        $empresaId = (int) auth()->user()->empresa_id;

        $query = PadraoTreinamento::query()
            ->where('empresa_id', $empresaId)
            ->with([
                'CarteiraTreinamento:id,nome,slug,empresa_id',
                'EtiquetaBloqueio:id,nome,slug,empresa_id',
                'Lotacoes.ClienteFilial:id,empresa_id,dados',
                'Vencimentos:id,label,empresa_id',
            ])
            ->orderBy('nome');

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
        $empresaId = (int) auth()->user()->empresa_id;
        $validated = $this->validarPayload($request, $empresaId);
        $this->validarTemplates($validated, $empresaId);
        $lotacoes = $this->normalizarLotacoes($request->input('lotacoes', []), $empresaId);
        $vencimentoIds = $this->normalizarVencimentos($request->input('vencimento_ids', []), $empresaId);

        DB::transaction(function () use ($validated, $empresaId, $lotacoes, $vencimentoIds) {
            $validated['empresa_id'] = $empresaId;
            $validated['slug'] = filled($validated['slug'] ?? null)
                ? $validated['slug']
                : Str::slug($validated['nome']);
            $padrao = PadraoTreinamento::create($validated);
            $this->syncLotacoes($padrao, $lotacoes);
            $padrao->Vencimentos()->sync($vencimentoIds);
        });

        return response()->json([], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $empresaId = (int) auth()->user()->empresa_id;
        $padrao = PadraoTreinamento::query()->where('empresa_id', $empresaId)->findOrFail($id);
        $validated = $this->validarPayload($request, $empresaId, $id);
        $this->validarTemplates($validated, $empresaId);
        $lotacoes = $this->normalizarLotacoes($request->input('lotacoes', []), $empresaId);
        $vencimentoIds = $this->normalizarVencimentos($request->input('vencimento_ids', []), $empresaId);

        DB::transaction(function () use ($padrao, $validated, $lotacoes, $vencimentoIds) {
            $padrao->update($validated);
            $this->syncLotacoes($padrao, $lotacoes);
            $padrao->Vencimentos()->sync($vencimentoIds);
        });

        return response()->json([], 201);
    }

    private function validarPayload(Request $request, int $empresaId, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('padroes_treinamento', 'slug')
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($ignoreId),
            ],
            'ativo' => 'boolean',
            'tem_carteira' => 'boolean',
            'carteira_treinamento_id' => 'nullable|integer|exists:carteiras_treinamento,id',
            'tem_etiqueta_bloqueio' => 'boolean',
            'etiqueta_bloqueio_id' => 'nullable|integer|exists:etiquetas_bloqueio,id',
            'lotacoes' => 'required|array|min:1',
            'vencimento_ids' => 'nullable|array',
            'vencimento_ids.*' => 'integer',
        ]);

        $validated['ativo'] = $request->boolean('ativo', true);
        $validated['tem_carteira'] = $request->boolean('tem_carteira');
        $validated['tem_etiqueta_bloqueio'] = $request->boolean('tem_etiqueta_bloqueio');

        if ($validated['tem_carteira'] && empty($validated['carteira_treinamento_id'])) {
            throw ValidationException::withMessages([
                'carteira_treinamento_id' => 'Selecione a carteira quando "Usa carteira" estiver ativo.',
            ]);
        }
        if (!$validated['tem_carteira']) {
            $validated['carteira_treinamento_id'] = null;
        }

        if ($validated['tem_etiqueta_bloqueio'] && empty($validated['etiqueta_bloqueio_id'])) {
            throw ValidationException::withMessages([
                'etiqueta_bloqueio_id' => 'Selecione a etiqueta quando "Usa etiqueta de bloqueio" estiver ativo.',
            ]);
        }
        if (!$validated['tem_etiqueta_bloqueio']) {
            $validated['etiqueta_bloqueio_id'] = null;
        }

        unset($validated['lotacoes'], $validated['vencimento_ids']);

        return $validated;
    }

    private function validarTemplates(array $validated, int $empresaId): void
    {
        if (!empty($validated['carteira_treinamento_id'])) {
            $ok = CarteiraTreinamento::query()
                ->where('id', $validated['carteira_treinamento_id'])
                ->visiveisParaEmpresa($empresaId)
                ->exists();
            if (!$ok) {
                throw ValidationException::withMessages([
                    'carteira_treinamento_id' => 'Carteira inválida para esta empresa.',
                ]);
            }
        }

        if (!empty($validated['etiqueta_bloqueio_id'])) {
            $ok = EtiquetaBloqueio::query()
                ->where('id', $validated['etiqueta_bloqueio_id'])
                ->visiveisParaEmpresa($empresaId)
                ->exists();
            if (!$ok) {
                throw ValidationException::withMessages([
                    'etiqueta_bloqueio_id' => 'Etiqueta inválida para esta empresa.',
                ]);
            }
        }
    }

    /**
     * @param  array<int, array{is_matriz?:bool,cliente_filial_id?:int|null}>  $lotacoes
     * @return list<array{is_matriz:bool,cliente_filial_id:int|null,lotacao_key:string}>
     */
    private function normalizarLotacoes(array $lotacoes, int $empresaId): array
    {
        if ($lotacoes === []) {
            throw ValidationException::withMessages([
                'lotacoes' => 'Informe ao menos uma lotação (Matriz e/ou Filial).',
            ]);
        }

        $out = [];
        $keys = [];
        foreach ($lotacoes as $item) {
            $isMatriz = filter_var($item['is_matriz'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $filialId = isset($item['cliente_filial_id']) ? (int) $item['cliente_filial_id'] : null;

            if ($isMatriz) {
                $key = PadraoTreinamentoLotacao::keyMatriz();
                $row = ['is_matriz' => true, 'cliente_filial_id' => null, 'lotacao_key' => $key];
            } else {
                if (!$filialId) {
                    throw ValidationException::withMessages([
                        'lotacoes' => 'Filial inválida na lista de lotações.',
                    ]);
                }
                $filialOk = ClienteFilial::query()
                    ->where('id', $filialId)
                    ->where('empresa_id', $empresaId)
                    ->where('ativo', true)
                    ->exists();
                if (!$filialOk) {
                    throw ValidationException::withMessages([
                        'lotacoes' => "Filial {$filialId} não pertence a esta empresa.",
                    ]);
                }
                $key = PadraoTreinamentoLotacao::keyFilial($filialId);
                $row = ['is_matriz' => false, 'cliente_filial_id' => $filialId, 'lotacao_key' => $key];
            }

            if (isset($keys[$key])) {
                continue;
            }
            $keys[$key] = true;
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param  array<int, int|string>  $ids
     * @return list<int>
     */
    private function normalizarVencimentos(array $ids, int $empresaId): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        $validos = Vencimento::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validos) !== count($ids)) {
            throw ValidationException::withMessages([
                'vencimento_ids' => 'Um ou mais treinamentos não pertencem a esta empresa.',
            ]);
        }

        return $validos;
    }

    /**
     * @param  list<array{is_matriz:bool,cliente_filial_id:int|null,lotacao_key:string}>  $lotacoes
     */
    private function syncLotacoes(PadraoTreinamento $padrao, array $lotacoes): void
    {
        $padrao->Lotacoes()->delete();
        foreach ($lotacoes as $lotacao) {
            $padrao->Lotacoes()->create($lotacao);
        }
    }
}
