<?php

namespace App\Http\Controllers;

use App\Domain\Recrutamento\GateEtapaSeletivo;
use App\Domain\Recrutamento\WorkflowSeletivoPadrao;
use App\Domain\Recrutamento\WorkflowSeletivoService;
use App\Models\EtapaTipo;
use App\Models\FeedbackCurriculo;
use App\Models\Habilidade;
use App\Models\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EtapaTipoSeletivoController extends Controller
{
    public function __construct(
        private readonly WorkflowSeletivoService $workflow,
        private readonly GateEtapaSeletivo $gate
    ) {
    }

    public function index()
    {
        return view('g.administracao.etapas-seletivo.index');
    }

    public function listar(Request $request)
    {
        $empresaId = (int) auth()->user()->empresa_id;
        $vagaId = $request->filled('vaga_id') ? (int) $request->vaga_id : null;

        $this->workflow->garantirPadraoEmpresa($empresaId);

        $etapas = $this->workflow->listarEscopo($empresaId, $vagaId, false);
        $habilidadesMaster = $this->workflow->habilidadesDoMaster($empresaId);

        return response()->json([
            'etapas' => $etapas,
            'tem_override' => $vagaId ? $this->workflow->cargoTemOverride($empresaId, $vagaId) : false,
            'habilidades_disponiveis' => Habilidade::query()
                ->whereIn('nome', $habilidadesMaster)
                ->orderBy('descricao')
                ->get(['id', 'nome', 'descricao']),
            'tipos' => [
                WorkflowSeletivoPadrao::TIPO_AVANCO,
                WorkflowSeletivoPadrao::TIPO_DESCLASSIFICACAO,
                WorkflowSeletivoPadrao::TIPO_SISTEMA,
            ],
        ]);
    }

    public function salvar(Request $request)
    {
        $empresaId = (int) auth()->user()->empresa_id;
        $vagaId = $request->filled('vaga_id') ? (int) $request->vaga_id : null;

        $dados = $request->validate([
            'id' => 'nullable|integer',
            'vaga_id' => 'nullable|integer',
            'nome' => 'required|string|max:190',
            'chave' => 'nullable|string|max:80',
            'tipo' => ['required', Rule::in([
                WorkflowSeletivoPadrao::TIPO_AVANCO,
                WorkflowSeletivoPadrao::TIPO_DESCLASSIFICACAO,
                WorkflowSeletivoPadrao::TIPO_SISTEMA,
            ])],
            'ordem' => 'required|integer|min:0',
            'obrigatoria' => 'boolean',
            'ativo' => 'boolean',
            'is_apto_admissao' => 'boolean',
            'permite_email' => 'boolean',
            'habilidade' => 'nullable|string|max:120',
            'depende_de_id' => 'nullable|integer',
        ]);

        if (!empty($dados['depende_de_id']) && !empty($dados['id']) && (int) $dados['depende_de_id'] === (int) $dados['id']) {
            return response()->json(['msg' => 'A etapa não pode depender de si mesma'], 422);
        }

        return DB::transaction(function () use ($empresaId, $vagaId, $dados) {
            if (!empty($dados['id'])) {
                $etapa = EtapaTipo::query()
                    ->where('cliente_id', $empresaId)
                    ->when($vagaId === null, fn ($q) => $q->whereNull('vaga_id'))
                    ->when($vagaId !== null, fn ($q) => $q->where('vaga_id', $vagaId))
                    ->findOrFail($dados['id']);

                // Sistema: não altera chave/tipo
                if ($etapa->tipo === WorkflowSeletivoPadrao::TIPO_SISTEMA) {
                    $etapa->fill([
                        'nome' => $dados['nome'],
                        'ordem' => $dados['ordem'],
                        'obrigatoria' => (bool) ($dados['obrigatoria'] ?? false),
                        'ativo' => (bool) ($dados['ativo'] ?? true),
                        'permite_email' => (bool) ($dados['permite_email'] ?? true),
                        'habilidade' => $dados['habilidade'] ?? $etapa->habilidade,
                        'depende_de_id' => $dados['depende_de_id'] ?? null,
                        'is_apto_admissao' => (bool) ($dados['is_apto_admissao'] ?? $etapa->is_apto_admissao),
                    ]);
                } else {
                    $etapa->fill([
                        'nome' => $dados['nome'],
                        'chave' => $dados['chave'] ?: $etapa->chave,
                        'tipo' => $dados['tipo'],
                        'ordem' => $dados['ordem'],
                        'obrigatoria' => (bool) ($dados['obrigatoria'] ?? false),
                        'ativo' => (bool) ($dados['ativo'] ?? true),
                        'is_apto_admissao' => (bool) ($dados['is_apto_admissao'] ?? false),
                        'permite_email' => (bool) ($dados['permite_email'] ?? true),
                        'habilidade' => $dados['habilidade'] ?? null,
                        'depende_de_id' => $dados['depende_de_id'] ?? null,
                    ]);
                }
                $etapa->save();
            } else {
                $chave = $dados['chave'] ?: str()->slug($dados['nome'], '_');
                $etapa = EtapaTipo::create([
                    'cliente_id' => $empresaId,
                    'vaga_id' => $vagaId,
                    'nome' => $dados['nome'],
                    'chave' => $chave,
                    'tipo' => $dados['tipo'] === WorkflowSeletivoPadrao::TIPO_SISTEMA
                        ? WorkflowSeletivoPadrao::TIPO_AVANCO
                        : $dados['tipo'],
                    'ordem' => $dados['ordem'],
                    'obrigatoria' => (bool) ($dados['obrigatoria'] ?? false),
                    'ativo' => (bool) ($dados['ativo'] ?? true),
                    'is_apto_admissao' => (bool) ($dados['is_apto_admissao'] ?? false),
                    'permite_email' => (bool) ($dados['permite_email'] ?? true),
                    'habilidade' => $dados['habilidade'] ?? 'curriculos_selecionados',
                    'depende_de_id' => $dados['depende_de_id'] ?? null,
                ]);
            }

            if (!empty($dados['is_apto_admissao'])) {
                EtapaTipo::query()
                    ->where('cliente_id', $empresaId)
                    ->when($vagaId === null, fn ($q) => $q->whereNull('vaga_id'))
                    ->when($vagaId !== null, fn ($q) => $q->where('vaga_id', $vagaId))
                    ->where('id', '!=', $etapa->id)
                    ->update(['is_apto_admissao' => false]);
            }

            return response()->json(['etapa' => $etapa->fresh()], 201);
        });
    }

    /**
     * Reordena o funil (drag & drop): `ids` na nova sequência, ordem = posição × 10.
     */
    public function reordenar(Request $request)
    {
        $dados = $request->validate([
            'vaga_id' => 'nullable|integer',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $empresaId = (int) auth()->user()->empresa_id;
        $vagaId = !empty($dados['vaga_id']) ? (int) $dados['vaga_id'] : null;

        DB::transaction(function () use ($empresaId, $vagaId, $dados) {
            $validos = EtapaTipo::query()
                ->where('cliente_id', $empresaId)
                ->when($vagaId === null, fn ($q) => $q->whereNull('vaga_id'))
                ->when($vagaId !== null, fn ($q) => $q->where('vaga_id', $vagaId))
                ->whereIn('id', $dados['ids'])
                ->pluck('id')
                ->all();

            foreach (array_values($dados['ids']) as $posicao => $id) {
                if (!in_array($id, $validos, true)) {
                    continue;
                }
                EtapaTipo::query()->where('id', $id)->update(['ordem' => ($posicao + 1) * 10]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function restaurarPadrao(Request $request)
    {
        $empresaId = (int) auth()->user()->empresa_id;
        $vagaId = $request->filled('vaga_id') ? (int) $request->vaga_id : null;

        if ($vagaId) {
            $this->workflow->removerOverrideCargo($empresaId, $vagaId);
            $this->workflow->clonarPadraoParaCargo($empresaId, $vagaId);
        } else {
            $this->workflow->garantirPadraoEmpresa($empresaId);
        }

        return response()->json(['ok' => true]);
    }

    public function personalizarCargo(Request $request)
    {
        $request->validate(['vaga_id' => 'required|integer']);
        $empresaId = (int) auth()->user()->empresa_id;
        $vagaId = (int) $request->vaga_id;

        Vaga::query()->where('id', $vagaId)->firstOrFail();
        $etapas = $this->workflow->clonarPadraoParaCargo($empresaId, $vagaId);

        return response()->json(['etapas' => $etapas, 'tem_override' => true]);
    }

    public function voltarPadraoCargo(Request $request)
    {
        $request->validate(['vaga_id' => 'required|integer']);
        $empresaId = (int) auth()->user()->empresa_id;
        $this->workflow->removerOverrideCargo($empresaId, (int) $request->vaga_id);

        return response()->json(['ok' => true]);
    }

    /**
     * API operacional: etapas do Classificar filtradas por permissão.
     */
    public function etapasOperacionais(Request $request)
    {
        $request->validate([
            'feedback_id' => 'nullable|integer',
            'vaga_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $empresaId = (int) $user->empresa_id;
        $vagaId = $request->filled('vaga_id') ? (int) $request->vaga_id : null;
        $feedback = null;

        if ($request->filled('feedback_id')) {
            $feedback = FeedbackCurriculo::query()->findOrFail((int) $request->feedback_id);
            $vagaId = $feedback->vaga_id ? (int) $feedback->vaga_id : $vagaId;
        }

        $etapas = $this->workflow->resolverEtapas($empresaId, $vagaId, true);
        $visiveis = $this->workflow->filtrarPorPermissao($etapas, $user);

        $payload = [
            'avanco' => $visiveis->where('tipo', WorkflowSeletivoPadrao::TIPO_AVANCO)
                ->merge($visiveis->where('tipo', WorkflowSeletivoPadrao::TIPO_SISTEMA)->where('chave', WorkflowSeletivoPadrao::CHAVE_APTO))
                ->values(),
            'desclassificacao' => $visiveis->where('tipo', WorkflowSeletivoPadrao::TIPO_DESCLASSIFICACAO)->values(),
            'sistema' => $visiveis->where('tipo', WorkflowSeletivoPadrao::TIPO_SISTEMA)->values(),
            'todas_visiveis' => $visiveis->values(),
        ];

        if ($feedback) {
            $feedback->loadMissing(['parecerTecnica', 'parecerRota', 'CartaOferta']);
            $payload['progresso'] = $this->gate->montarProgresso($feedback, $visiveis);
        }

        return response()->json($payload);
    }
}
