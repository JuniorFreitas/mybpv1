<?php

namespace App\Http\Controllers;

use App\Helpers\RHHelper;
use App\Http\Requests\Configuracoes\StoreNotificacaoGrupoRequest;
use App\Http\Requests\Configuracoes\UpdateNotificacaoGrupoRequest;
use App\Models\CentroCusto;
use App\Models\CentroCustoFilial;
use App\Models\NotificacaoGrupo;
use App\Models\NotificacaoTipo;
use App\Services\Notificacao\NotificacaoGrupoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacaoConfigController extends Controller
{
    public function __construct(
        private readonly NotificacaoGrupoService $grupoService
    ) {
        $this->middleware('can:configuracao_notificacoes');
    }

    public function index(): View
    {
        return view('g.configuracoes.notificacoes.index');
    }

    public function tipos(): JsonResponse
    {
        $tipos = NotificacaoTipo::query()
            ->where('ativo', true)
            ->orderBy('ordem')
            ->get(['codigo', 'nome', 'canal', 'regra_elegibilidade', 'editavel', 'ordem']);

        return response()->json(['tipos' => $tipos]);
    }

    public function listarGrupos(Request $request): JsonResponse
    {
        $empresaId = (int) auth()->user()->empresa_id;
        $tipo = $request->query('tipo_codigo', NotificacaoTipo::APROVACAO_RH);
        $busca = trim((string) $request->query('busca', ''));
        $status = $request->query('status'); // ativo|inativo|all
        $pages = max(1, min(100, (int) $request->query('pages', 50)));

        $query = NotificacaoGrupo::query()
            ->select(['id', 'empresa_id', 'tipo_codigo', 'nome', 'ativo', 'escopo_cc', 'updated_at'])
            ->where('empresa_id', $empresaId)
            ->where('tipo_codigo', $tipo)
            ->withCount('destinatarios')
            ->with([
                'destinatarios:id,nome,login',
                'centrosCusto:id,label',
            ]);

        if ($busca !== '') {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', '%' . $busca . '%');
                if (ctype_digit($busca)) {
                    $q->orWhere('id', (int) $busca);
                }
            });
        }

        if ($status === 'ativo') {
            $query->where('ativo', true);
        } elseif ($status === 'inativo') {
            $query->where('ativo', false);
        }

        $itens = $query->orderByDesc('id')->paginate($pages);

        $lista = collect($itens->items())->map(function (NotificacaoGrupo $grupo) {
            $serializado = $this->serializeGrupo($grupo);
            $serializado['destinatarios_amostra'] = collect($serializado['destinatarios'])
                ->take(3)
                ->map(fn ($u) => $u['nome'] ?: $u['login'])
                ->values()
                ->all();
            $serializado['centros_custo_amostra'] = collect($serializado['centros_custo'])
                ->take(3)
                ->map(fn ($c) => $c['label'])
                ->values()
                ->all();

            return $serializado;
        })->values();

        return response()->json([
            'itens' => $lista,
            'paginacao' => [
                'current_page' => $itens->currentPage(),
                'last_page' => $itens->lastPage(),
                'per_page' => $itens->perPage(),
                'total' => $itens->total(),
            ],
            'aviso_sem_grupos' => $itens->total() === 0,
        ]);
    }

    public function store(StoreNotificacaoGrupoRequest $request): JsonResponse
    {
        $empresaId = (int) auth()->user()->empresa_id;
        $dados = $request->validated();
        $grupo = $this->grupoService->criar($empresaId, $dados['tipo_codigo'], $dados);

        return response()->json(['item' => $this->serializeGrupo($grupo)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $grupo = $this->findGrupoEmpresa($id);
        $grupo->load(['destinatarios:id,nome,login', 'centrosCusto:id,label']);

        return response()->json(['item' => $this->serializeGrupo($grupo)]);
    }

    public function update(UpdateNotificacaoGrupoRequest $request, int $id): JsonResponse
    {
        $grupo = $this->findGrupoEmpresa($id);
        $grupo = $this->grupoService->atualizar($grupo, $request->validated());

        return response()->json(['item' => $this->serializeGrupo($grupo)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $grupo = $this->findGrupoEmpresa($id);
        $this->grupoService->excluir($grupo);

        return response()->json([], 204);
    }

    /**
     * Ativa ou desativa o grupo (padrão bt-ativo / ativa-desativa do sistema).
     */
    public function ativaDesativa(int $id): JsonResponse
    {
        $grupo = $this->findGrupoEmpresa($id);
        $grupo = $this->grupoService->alterarAtivo($grupo);

        return response()->json(['ativo' => (bool) $grupo->ativo]);
    }

    public function candidatos(Request $request): JsonResponse
    {
        $empresaId = (int) auth()->user()->empresa_id;
        $tipo = $request->query('tipo_codigo', NotificacaoTipo::APROVACAO_RH);

        $tipoModel = NotificacaoTipo::query()->where('codigo', $tipo)->first();
        if (!$tipoModel) {
            return response()->json(['candidatos' => []]);
        }

        if ($tipoModel->regra_elegibilidade === NotificacaoTipo::REGRA_PRIVILEGIOS_RH) {
            $candidatos = RHHelper::buscarUsuariosRH($empresaId)
                ->map(fn ($u) => [
                    'id' => (int) $u->id,
                    'nome' => $u->nome,
                    'login' => $u->login,
                    'label' => $u->nome . ' (' . $u->login . ')',
                ])
                ->values();

            return response()->json(['candidatos' => $candidatos]);
        }

        return response()->json(['candidatos' => []]);
    }

    public function centrosCusto(): JsonResponse
    {
        $empresaId = (int) auth()->user()->empresa_id;

        $ccs = CentroCusto::query()
            ->withoutGlobalScopes()
            ->select(['id', 'label', 'empresa_id', 'ativo'])
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('label')
            ->get();

        $filiaisCcIds = CentroCustoFilial::query()
            ->withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->whereIn('centro_custo_id', $ccs->pluck('id'))
            ->pluck('centro_custo_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $filiaisSet = array_fill_keys($filiaisCcIds, true);
        $temFiliais = count($filiaisSet) > 0;

        $opcoes = $ccs->map(function ($cc) use ($filiaisSet, $temFiliais) {
            $matriz = !isset($filiaisSet[(int) $cc->id]);
            $sufixo = '';
            if ($temFiliais) {
                $sufixo = $matriz ? ' (Matriz)' : ' (Filial)';
            }

            return [
                'value' => (string) $cc->id,
                'label' => $cc->label . $sufixo,
                'matriz' => $matriz,
            ];
        })->values();

        return response()->json([
            'centros_custo' => $opcoes,
            'tem_filiais' => $temFiliais,
        ]);
    }

    private function findGrupoEmpresa(int $id): NotificacaoGrupo
    {
        return NotificacaoGrupo::query()
            ->where('empresa_id', (int) auth()->user()->empresa_id)
            ->findOrFail($id);
    }

    private function serializeGrupo(NotificacaoGrupo $grupo): array
    {
        return [
            'id' => $grupo->id,
            'tipo_codigo' => $grupo->tipo_codigo,
            'nome' => $grupo->nome,
            'ativo' => (bool) $grupo->ativo,
            'escopo_cc' => $grupo->escopo_cc,
            'destinatarios_count' => $grupo->destinatarios_count ?? $grupo->destinatarios->count(),
            'destinatarios' => $grupo->relationLoaded('destinatarios')
                ? $grupo->destinatarios->map(fn ($u) => [
                    'id' => (int) $u->id,
                    'nome' => $u->nome,
                    'login' => $u->login,
                    'label' => $u->nome . ' (' . $u->login . ')',
                ])->values()
                : [],
            'centros_custo' => $grupo->relationLoaded('centrosCusto')
                ? $grupo->centrosCusto->map(fn ($c) => [
                    'id' => (int) $c->id,
                    'label' => $c->label,
                    'value' => (string) $c->id,
                ])->values()
                : [],
            'centro_custo_ids' => $grupo->relationLoaded('centrosCusto')
                ? $grupo->centrosCusto->pluck('id')->map(fn ($id) => (int) $id)->values()
                : [],
            'user_ids' => $grupo->relationLoaded('destinatarios')
                ? $grupo->destinatarios->pluck('id')->map(fn ($id) => (int) $id)->values()
                : [],
        ];
    }
}
