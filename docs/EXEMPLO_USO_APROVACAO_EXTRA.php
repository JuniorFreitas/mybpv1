<?php

/**
 * EXEMPLO DE IMPLEMENTAÇÃO NO CONTROLLER DE DEMISSÃO
 *
 * Este arquivo demonstra como integrar o sistema de aprovação extra
 * no fluxo de aprovação de demissões
 */

namespace App\Http\Controllers\Examples;

use App\Models\AprovacaoExtraConfig;
use App\Models\DemissaoPrevista;
use Illuminate\Http\Request;

class DemissaoComAprovacaoExtraExample
{
    /**
     * Exemplo 1: Ao listar demissões, incluir informação da aprovação extra
     */
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        // Buscar configuração de aprovação extra para demissão
        $configAprovacaoExtra = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

        $demissoes = DemissaoPrevista::with([
            'Colaborador',
            'UserAprovacao',
            'RhAprovacao',
            'AprovacaoExtra' // Incluir o relacionamento de aprovação extra
        ])
            ->where('empresa_id', $empresaId)
            ->get();

        return response()->json([
            'demissoes' => $demissoes,
            'tem_aprovacao_extra' => !is_null($configAprovacaoExtra),
            'nome_aprovacao_extra' => $configAprovacaoExtra?->nome_aprovacao
        ]);
    }

    /**
     * Exemplo 2: Ao criar uma demissão, verificar se precisa de aprovação extra
     */
    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        // Criar demissão
        $demissao = DemissaoPrevista::create([
            'empresa_id' => $empresaId,
            'colaborador_id' => $request->colaborador_id,
            'data_demissao' => $request->data_demissao,
            // ... outros campos
        ]);

        // Verificar se empresa tem aprovação extra configurada
        $configAprovacaoExtra = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

        $mensagem = 'Demissão criada com sucesso!';
        if ($configAprovacaoExtra) {
            $mensagem .= " Aguardando aprovação de: Gestor, RH e {$configAprovacaoExtra->nome_aprovacao}";
        }

        return response()->json([
            'success' => true,
            'message' => $mensagem,
            'demissao' => $demissao,
            'requer_aprovacao_extra' => !is_null($configAprovacaoExtra)
        ]);
    }

    /**
     * Exemplo 3: Endpoint para aprovar/reprovar pela aprovação extra
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

        // Verificar se já foi aprovado por gestor e RH
        if (
            $demissao->status_aprovacao !== 'aprovado' ||
            $demissao->status_aprovacao_rh !== 'aprovado'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Demissão precisa ser aprovada primeiro por Gestor e RH'
            ], 400);
        }

        // Verificar se empresa tem aprovação extra configurada
        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa não possui aprovação extra configurada para demissões'
            ], 400);
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
            ? "Demissão aprovada pelo {$nomeAprovacao}!"
            : "Demissão reprovada pelo {$nomeAprovacao}";

        // TODO: Enviar notificação para RH informando sobre a aprovação extra

        return response()->json([
            'success' => true,
            'message' => $mensagem,
            'demissao' => $demissao->load('AprovacaoExtra')
        ]);
    }

    /**
     * Exemplo 4: Verificar se demissão está totalmente aprovada
     */
    public function verificarAprovacaoCompleta($demissao)
    {
        $empresaId = $demissao->empresa_id;
        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

        // Aprovações padrão
        $gestorAprovado = $demissao->status_aprovacao === 'aprovado';
        $rhAprovado = $demissao->status_aprovacao_rh === 'aprovado';

        // Se não tem aprovação extra configurada
        if (!$config) {
            return $gestorAprovado && $rhAprovado;
        }

        // Se tem aprovação extra, verificar também ela
        $extraAprovado = $demissao->status_aprovacao_extra === 'aprovado';

        return $gestorAprovado && $rhAprovado && $extraAprovado;
    }

    /**
     * Exemplo 5: Buscar demissões pendentes de aprovação extra
     */
    public function pendentesAprovacaoExtra(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        // Verificar se empresa tem aprovação extra
        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

        if (!$config) {
            return response()->json([
                'demissoes' => [],
                'message' => 'Empresa não possui aprovação extra configurada'
            ]);
        }

        // Buscar demissões aprovadas por gestor e RH, mas sem aprovação extra
        $demissoes = DemissaoPrevista::with(['Colaborador', 'UserAprovacao', 'RhAprovacao'])
            ->where('empresa_id', $empresaId)
            ->where('status_aprovacao', 'aprovado')
            ->where('status_aprovacao_rh', 'aprovado')
            ->whereNull('status_aprovacao_extra') // Ainda não teve aprovação extra
            ->get();

        return response()->json([
            'demissoes' => $demissoes,
            'nome_aprovacao' => $config->nome_aprovacao,
            'total' => $demissoes->count()
        ]);
    }

    /**
     * Exemplo 6: Relatório de demissões com status de todas as aprovações
     */
    public function relatorioAprovacoes(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

        $demissoes = DemissaoPrevista::with([
            'Colaborador',
            'UserAprovacao',
            'RhAprovacao',
            'AprovacaoExtra'
        ])
            ->where('empresa_id', $empresaId)
            ->get()
            ->map(function ($demissao) use ($config) {
                return [
                    'id' => $demissao->id,
                    'colaborador' => $demissao->Colaborador->nome ?? '',
                    'data_demissao' => $demissao->data_demissao,
                    'aprovacoes' => [
                        'gestor' => [
                            'status' => $demissao->status_aprovacao,
                            'aprovador' => $demissao->UserAprovacao->nome ?? '',
                            'data' => $demissao->data_aprovacao
                        ],
                        'rh' => [
                            'status' => $demissao->status_aprovacao_rh,
                            'aprovador' => $demissao->RhAprovacao->nome ?? '',
                            'data' => $demissao->data_aprovacao_rh
                        ],
                        'extra' => $config ? [
                            'nome' => $config->nome_aprovacao,
                            'status' => $demissao->status_aprovacao_extra,
                            'aprovador' => $demissao->AprovacaoExtra->nome ?? '',
                            'data' => $demissao->data_aprovacao_extra,
                            'obs' => $demissao->obs_aprovacao_extra
                        ] : null
                    ],
                    'totalmente_aprovada' => $this->verificarAprovacaoCompleta($demissao)
                ];
            });

        return response()->json([
            'demissoes' => $demissoes,
            'tem_aprovacao_extra' => !is_null($config)
        ]);
    }
}
