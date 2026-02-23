<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\SegmentoTreinamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Cadastro global de segmentos de treinamento (ALUMAR, VALE, etc.).
 * Grava apenas na base SegmentoTreinamento (tabela segmentos_treinamento), sem cliente/empresa.
 * Definir se cada empresa vai usar ou não um segmento é feito no cadastro de Cliente (pivot cliente_segmento_treinamento).
 * Acesso ao CRUD desta tela: apenas empresa 100.
 */
class SegmentoTreinamentoController extends Controller
{
    /**
     * ID da empresa que pode gerenciar o cadastro global de segmentos.
     */
    public const EMPRESA_ID_CADASTRO_SEGMENTOS = 100;

    /**
     * Garante que apenas a empresa 100 pode acessar o cadastro global de segmentos.
     */
    private function autorizarCadastroSegmentos(): void
    {
        if ((int) auth()->user()->empresa_id !== self::EMPRESA_ID_CADASTRO_SEGMENTOS) {
            abort(403, 'O cadastro de segmentos de treinamento é restrito à empresa responsável pela configuração global.');
        }
    }

    /**
     * Segmentos habilitados para a empresa do usuário (para select na admissão).
     * Se nenhum habilitado, retorna ao menos ALUMAR (default).
     */
    public function habilitadosEmpresa(): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;
        $cliente = Cliente::find($empresaId);
        $segmentos = $cliente && $cliente->SegmentosTreinamento()->where('ativo', true)->count() > 0
            ? $cliente->SegmentosTreinamento()->where('segmentos_treinamento.ativo', true)->orderBy('nome')->get(['segmentos_treinamento.id', 'nome', 'slug'])
            : SegmentoTreinamento::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'slug']);
        return response()->json($segmentos);
    }

    /**
     * Página de cadastro de segmentos (padrão do sistema). Apenas empresa 100.
     */
    public function index()
    {
        $this->autorizarCadastroSegmentos();
        return view('g.cadastros.segmentostreinamento.index');
    }

    /**
     * Lista segmentos para select (ativos). API.
     */
    public function listar(Request $request): JsonResponse
    {
        $query = SegmentoTreinamento::query()->where('ativo', true)->orderBy('nome');
        if ($request->filled('com_inativos')) {
            $query = SegmentoTreinamento::query()->orderBy('nome');
        }
        $segmentos = $query->get(['id', 'nome', 'slug', 'ativo', 'config_carteira']);
        return response()->json($segmentos);
    }

    /**
     * Lista todos para cadastro/admin (com paginação). Apenas empresa 100.
     */
    public function atualizar(Request $request): JsonResponse
    {
        $this->autorizarCadastroSegmentos();
        $resultado = SegmentoTreinamento::query()
            ->orderBy('nome')
            ->paginate((int) $request->input('per_page', 50));
        $items = collect($resultado->items())->map(function (SegmentoTreinamento $s) {
            $arr = $s->toArray();
            $arr['config_carteira'] = $this->normalizarConfigCarteira($s->config_carteira);
            return $arr;
        });
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['items' => $items],
        ]);
    }

    /**
     * Retorna a estrutura padrão de config_carteira (carteira + etiqueta bloqueio).
     */
    private function getDefaultConfigCarteira(): array
    {
        return [
            'cabecalho_img' => '',
            'verso_img' => '',
            'exibir_etiqueta_bloqueio' => true,
            'ramal_emergencia' => '1199',
            'bloqueio_texto_nao_use' => 'NÃO USE, MOVA OU OPERE ENQUANTO ESTA ETIQUETA ESTIVER COLOCADA',
            'bloqueio_texto_demissao' => 'QUEM OPERAR O EQUIPAMENTO OU REMOVER A ETIQUETA ESTÁ SUJEITO A DEMISSÃO',
            'bloqueio_texto_cuidado' => 'CUIDADO!',
            'bloqueio_texto_homens_trabalhando' => 'HOMENS TRABALHANDO NÃO OPERE ESTE EQUIPAMENTO',
        ];
    }

    /**
     * Normaliza config_carteira para gravar completo e com tipos corretos.
     */
    private function normalizarConfigCarteira(?array $config): array
    {
        $default = $this->getDefaultConfigCarteira();
        if (!is_array($config)) {
            return $default;
        }
        $merged = array_merge($default, $config);
        $merged['exibir_etiqueta_bloqueio'] = filter_var($merged['exibir_etiqueta_bloqueio'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $merged['ramal_emergencia'] = (string) ($merged['ramal_emergencia'] ?? '1199');
        foreach (['cabecalho_img', 'verso_img', 'bloqueio_texto_nao_use', 'bloqueio_texto_demissao', 'bloqueio_texto_cuidado', 'bloqueio_texto_homens_trabalhando'] as $key) {
            $merged[$key] = (string) ($merged[$key] ?? $default[$key]);
        }
        return $merged;
    }

    /**
     * Store a newly created resource. Apenas empresa 100.
     */
    public function store(Request $request): JsonResponse
    {
        $this->autorizarCadastroSegmentos();
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:segmentos_treinamento,slug',
            'ativo' => 'boolean',
            'config_carteira' => 'nullable|array',
        ]);
        $validated['ativo'] = $request->boolean('ativo', true);
        $validated['config_carteira'] = $this->normalizarConfigCarteira($validated['config_carteira'] ?? null);
        SegmentoTreinamento::create($validated);
        return response()->json([], 201);
    }

    /**
     * Update the specified resource. Apenas empresa 100.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->autorizarCadastroSegmentos();
        $segmento = SegmentoTreinamento::findOrFail($id);
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:segmentos_treinamento,slug,' . $id,
            'ativo' => 'boolean',
            'config_carteira' => 'nullable|array',
        ]);
        $validated['ativo'] = $request->boolean('ativo', true);
        $validated['config_carteira'] = $this->normalizarConfigCarteira($validated['config_carteira'] ?? null);
        $segmento->update($validated);
        return response()->json([], 201);
    }
}
