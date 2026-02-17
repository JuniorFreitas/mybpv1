<?php

/**
 * EXEMPLO DE IMPLEMENTAÇÃO ATUALIZADA NO CONTROLLER DE DEMISSÃO
 *
 * IMPORTANTE: RH SEMPRE É A ÚLTIMA APROVAÇÃO
 * Fluxo: Gestor → Aprovação Extra → RH
 */

namespace App\Http\Controllers\Examples;

use App\Models\AprovacaoExtraConfig;
use App\Models\DemissaoPrevista;
use Illuminate\Http\Request;

class DemissaoComAprovacaoExtraExampleV2
{
    /**
     * Exemplo 1: Aprovar pela Aprovação Extra (antes do RH)
     * Apenas usuários autorizados ou com privilegio_rh podem aprovar
     */
    public function aprovarExtra(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aprovado,reprovado',
            'observacao' => 'nullable|string'
        ]);

        $empresaId = auth()->user()->empresa_id;
        $userId = auth()->user()->id;

        $demissao = DemissaoPrevista::where('empresa_id', $empresaId)
            ->findOrFail($id);

        // Verificar se gestor já aprovou
        if ($demissao->status_aprovacao !== 'aprovado') {
            return response()->json([
                'success' => false,
                'message' => 'Demissão precisa ser aprovada primeiro pelo Gestor'
            ], 400);
        }

        // Buscar configuração
        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não possui aprovação extra configurada'
            ], 400);
        }

        // IMPORTANTE: Verificar se usuário pode aprovar
        if (!$config->podeAprovar($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para aprovar como ' . $config->nome_aprovacao
            ], 403);
        }

        // Registrar aprovação extra
        $demissao->update([
            'aprovacao_extra_id' => $userId,
            'status_aprovacao_extra' => $request->status,
            'obs_aprovacao_extra' => $request->observacao,
            'data_aprovacao_extra' => now()
        ]);

        $nomeAprovacao = $config->nome_aprovacao;
        $mensagem = $request->status === 'aprovado'
            ? "Demissão aprovada pelo {$nomeAprovacao}! Aguardando aprovação final do RH."
            : "Demissão reprovada pelo {$nomeAprovacao}";

        // TODO: Notificar RH que pode fazer aprovação final

        return response()->json([
            'success' => true,
            'message' => $mensagem,
            'demissao' => $demissao->load('AprovacaoExtra')
        ]);
    }

    /**
     * Exemplo 2: Aprovar pelo RH (SEMPRE A ÚLTIMA APROVAÇÃO)
     * RH só pode aprovar depois da aprovação extra (se houver)
     */
    public function aprovarRH(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aprovado,reprovado',
            'observacao' => 'nullable|string'
        ]);

        $empresaId = auth()->user()->empresa_id;
        $userId = auth()->user()->id;

        $demissao = DemissaoPrevista::where('empresa_id', $empresaId)
            ->findOrFail($id);

        // Verificar se gestor aprovou
        if ($demissao->status_aprovacao !== 'aprovado') {
            return response()->json([
                'success' => false,
                'message' => 'Demissão precisa ser aprovada primeiro pelo Gestor'
            ], 400);
        }

        // IMPORTANTE: Verificar se tem aprovação extra configurada
        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

        if ($config) {
            // Se tem aprovação extra, verificar se já foi aprovada
            if ($demissao->status_aprovacao_extra !== 'aprovado') {
                return response()->json([
                    'success' => false,
                    'message' => "Demissão precisa ser aprovada primeiro pelo {$config->nome_aprovacao}"
                ], 400);
            }
        }

        // RH pode aprovar agora (todas as aprovações anteriores foram feitas)
        $demissao->update([
            'rh_aprovacao_id' => $userId,
            'status_aprovacao_rh' => $request->status,
            'obs_rh' => $request->observacao,
            'data_aprovacao_rh' => now()
        ]);

        $mensagem = $request->status === 'aprovado'
            ? 'Demissão aprovada pelo RH! Processo concluído.'
            : 'Demissão reprovada pelo RH';

        return response()->json([
            'success' => true,
            'message' => $mensagem,
            'demissao' => $demissao
        ]);
    }

    /**
     * Exemplo 3: Verificar status do fluxo de aprovação
     */
    public function statusAprovacao($demissao)
    {
        $empresaId = $demissao->empresa_id;
        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

        $fluxo = [
            'gestor' => [
                'ordem' => 1,
                'status' => $demissao->status_aprovacao,
                'aprovador' => $demissao->UserAprovacao?->nome,
                'data' => $demissao->data_aprovacao,
                'pendente' => $demissao->status_aprovacao !== 'aprovado'
            ]
        ];

        // Se tem aprovação extra, adicionar no fluxo
        if ($config) {
            $fluxo['aprovacao_extra'] = [
                'ordem' => 2,
                'nome' => $config->nome_aprovacao,
                'status' => $demissao->status_aprovacao_extra,
                'aprovador' => $demissao->AprovacaoExtra?->nome,
                'data' => $demissao->data_aprovacao_extra,
                'pendente' => $demissao->status_aprovacao === 'aprovado' &&
                    !$demissao->status_aprovacao_extra
            ];
        }

        // RH SEMPRE é o último
        $fluxo['rh'] = [
            'ordem' => $config ? 3 : 2,
            'status' => $demissao->status_aprovacao_rh,
            'aprovador' => $demissao->RhAprovacao?->nome,
            'data' => $demissao->data_aprovacao_rh,
            'pendente' => $demissao->status_aprovacao === 'aprovado' &&
                (!$config || $demissao->status_aprovacao_extra === 'aprovado') &&
                !$demissao->status_aprovacao_rh,
            'final' => true // RH é sempre a aprovação final
        ];

        $totalmenteAprovado = $demissao->status_aprovacao === 'aprovado' &&
            (!$config || $demissao->status_aprovacao_extra === 'aprovado') &&
            $demissao->status_aprovacao_rh === 'aprovado';

        return [
            'fluxo' => $fluxo,
            'totalmente_aprovado' => $totalmenteAprovado,
            'proxima_aprovacao' => $this->proximaAprovacao($demissao, $config)
        ];
    }

    /**
     * Exemplo 4: Determinar qual é a próxima aprovação pendente
     */
    private function proximaAprovacao($demissao, $config)
    {
        // 1. Se gestor não aprovou
        if ($demissao->status_aprovacao !== 'aprovado') {
            return 'gestor';
        }

        // 2. Se tem aprovação extra e não foi aprovada
        if ($config && $demissao->status_aprovacao_extra !== 'aprovado') {
            return 'aprovacao_extra';
        }

        // 3. Se RH não aprovou (sempre a última)
        if ($demissao->status_aprovacao_rh !== 'aprovado') {
            return 'rh';
        }

        return null; // Todas as aprovações foram feitas
    }

    /**
     * Exemplo 5: Listar pendências para o usuário logado
     */
    public function minhasPendencias(Request $request)
    {
        $userId = auth()->user()->id;
        $empresaId = auth()->user()->empresa_id;
        $user = auth()->user();

        $pendencias = [];

        // Verificar se usuário pode aprovar como aprovação extra
        $configs = AprovacaoExtraConfig::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->get();

        foreach ($configs as $config) {
            if ($config->podeAprovar($userId)) {
                // Buscar demissões pendentes de aprovação extra
                $demissoesPendentes = DemissaoPrevista::where('empresa_id', $empresaId)
                    ->where('status_aprovacao', 'aprovado')
                    ->whereNull('status_aprovacao_extra')
                    ->get();

                if ($demissoesPendentes->count() > 0) {
                    $pendencias[] = [
                        'tipo' => 'aprovacao_extra',
                        'nome_aprovacao' => $config->nome_aprovacao,
                        'tipo_processo' => $config->tipo_processo,
                        'quantidade' => $demissoesPendentes->count(),
                        'demissoes' => $demissoesPendentes
                    ];
                }
            }
        }

        // Verificar se usuário tem privilegio_rh para aprovação final
        if (in_array('privilegio_rh', $user->listaDeHabilidades())) {
            foreach ($configs as $config) {
                // Buscar demissões aprovadas por extra, pendentes de RH
                $demissoesPendentesRh = DemissaoPrevista::where('empresa_id', $empresaId)
                    ->where('status_aprovacao', 'aprovado')
                    ->where('status_aprovacao_extra', 'aprovado')
                    ->whereNull('status_aprovacao_rh')
                    ->get();

                if ($demissoesPendentesRh->count() > 0) {
                    $pendencias[] = [
                        'tipo' => 'rh',
                        'nome_aprovacao' => 'RH (Final)',
                        'tipo_processo' => $config->tipo_processo,
                        'quantidade' => $demissoesPendentesRh->count(),
                        'demissoes' => $demissoesPendentesRh
                    ];
                }
            }

            // Também buscar demissões sem aprovação extra configurada
            $demissoesSemExtra = DemissaoPrevista::where('empresa_id', $empresaId)
                ->where('status_aprovacao', 'aprovado')
                ->whereNull('status_aprovacao_rh')
                ->whereNotIn('id', function ($query) use ($empresaId) {
                    $query->select('demissao_previstas.id')
                        ->from('demissao_previstas')
                        ->join('aprovacao_extra_configs', function ($join) use ($empresaId) {
                            $join->on('demissao_previstas.empresa_id', '=', 'aprovacao_extra_configs.empresa_id')
                                ->where('aprovacao_extra_configs.tipo_processo', '=', 'demissao')
                                ->where('aprovacao_extra_configs.ativo', '=', true);
                        });
                })
                ->get();

            if ($demissoesSemExtra->count() > 0) {
                $pendencias[] = [
                    'tipo' => 'rh',
                    'nome_aprovacao' => 'RH (Final)',
                    'tipo_processo' => 'demissao',
                    'quantidade' => $demissoesSemExtra->count(),
                    'demissoes' => $demissoesSemExtra
                ];
            }
        }

        return response()->json([
            'pendencias' => $pendencias,
            'total' => array_sum(array_column($pendencias, 'quantidade'))
        ]);
    }

    /**
     * Exemplo 6: Dashboard com métricas de aprovação
     */
    public function dashboard(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

        $metricas = [
            'aguardando_gestor' => DemissaoPrevista::where('empresa_id', $empresaId)
                ->whereNull('status_aprovacao')
                ->count(),

            'aguardando_rh' => 0,
            'concluidas' => DemissaoPrevista::where('empresa_id', $empresaId)
                ->where('status_aprovacao_rh', 'aprovado')
                ->count()
        ];

        if ($config) {
            $metricas['aguardando_' . strtolower(str_replace(' ', '_', $config->nome_aprovacao))] =
                DemissaoPrevista::where('empresa_id', $empresaId)
                ->where('status_aprovacao', 'aprovado')
                ->whereNull('status_aprovacao_extra')
                ->count();

            $metricas['aguardando_rh'] = DemissaoPrevista::where('empresa_id', $empresaId)
                ->where('status_aprovacao', 'aprovado')
                ->where('status_aprovacao_extra', 'aprovado')
                ->whereNull('status_aprovacao_rh')
                ->count();
        } else {
            $metricas['aguardando_rh'] = DemissaoPrevista::where('empresa_id', $empresaId)
                ->where('status_aprovacao', 'aprovado')
                ->whereNull('status_aprovacao_rh')
                ->count();
        }

        return response()->json([
            'metricas' => $metricas,
            'tem_aprovacao_extra' => !is_null($config),
            'nome_aprovacao_extra' => $config?->nome_aprovacao
        ]);
    }
}
