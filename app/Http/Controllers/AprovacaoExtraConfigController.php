<?php

namespace App\Http\Controllers;

use App\Models\AprovacaoExtraConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AprovacaoExtraConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('g.administracao.aprovacao-extra-config.index');
    }

    /**
     * Lista todas as configurações de aprovação extra da empresa
     */
    public function listar(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $configs = AprovacaoExtraConfig::where('empresa_id', $empresaId)
            ->orderBy('tipo_processo')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($configs);
    }

    /**
     * Busca configuração ativa para um tipo específico de processo
     */
    public function buscarPorTipo(Request $request)
    {
        $request->validate([
            'tipo_processo' => 'required|string'
        ]);

        $empresaId = auth()->user()->empresa_id;
        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, $request->tipo_processo);

        return response()->json([
            'config' => $config,
            'tem_aprovacao_extra' => !is_null($config)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_processo' => 'required|in:demissao,ferias,mudanca_cargo,transferencia,intermitente_fixo,valor_extra,requisicao_vaga,admissao',
            'nome_aprovacao' => 'required|string|max:255',
            'usuarios_autorizados' => 'nullable|array',
            'usuarios_autorizados.*' => 'exists:users,id',
            'ativo' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $empresaId = auth()->user()->empresa_id;

            // Validar se já existe configuração para este tipo de processo
            $configExistente = AprovacaoExtraConfig::where('empresa_id', $empresaId)
                ->where('tipo_processo', $request->tipo_processo)
                ->first();

            if ($configExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe uma configuração para este tipo de processo'
                ], 422);
            }

            // Se estiver ativando uma nova config, desativa as outras do mesmo tipo
            if ($request->ativo) {
                AprovacaoExtraConfig::where('empresa_id', $empresaId)
                    ->where('tipo_processo', $request->tipo_processo)
                    ->update(['ativo' => false]);
            }

            $config = AprovacaoExtraConfig::create([
                'empresa_id' => $empresaId,
                'tipo_processo' => $request->tipo_processo,
                'nome_aprovacao' => $request->nome_aprovacao,
                'usuarios_autorizados' => $request->usuarios_autorizados,
                'ativo' => $request->ativo
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Configuração criada com sucesso!',
                'data' => $config
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar configuração: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nome_aprovacao' => 'required|string|max:255',
            'usuarios_autorizados' => 'nullable|array',
            'usuarios_autorizados.*' => 'exists:users,id',
            'ativo' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $empresaId = auth()->user()->empresa_id;
            $config = AprovacaoExtraConfig::where('empresa_id', $empresaId)
                ->findOrFail($id);

            // Se estiver ativando esta config, desativa as outras do mesmo tipo
            if ($request->ativo && !$config->ativo) {
                AprovacaoExtraConfig::where('empresa_id', $empresaId)
                    ->where('tipo_processo', $config->tipo_processo)
                    ->where('id', '!=', $id)
                    ->update(['ativo' => false]);
            }

            $config->update([
                'nome_aprovacao' => $request->nome_aprovacao,
                'usuarios_autorizados' => $request->usuarios_autorizados,
                'ativo' => $request->ativo
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Configuração atualizada com sucesso!',
                'data' => $config
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar configuração: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $empresaId = auth()->user()->empresa_id;
            $config = AprovacaoExtraConfig::where('empresa_id', $empresaId)
                ->findOrFail($id);

            $config->delete();

            return response()->json([
                'success' => true,
                'message' => 'Configuração removida com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover configuração: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativa ou desativa uma configuração (padrão ativa-desativa do sistema, usado pelo bt-ativo)
     */
    public function ativaDesativa($id)
    {
        try {
            DB::beginTransaction();

            $empresaId = auth()->user()->empresa_id;
            $config = AprovacaoExtraConfig::where('empresa_id', $empresaId)
                ->findOrFail($id);

            $novoStatus = !$config->ativo;

            // Se estiver ativando, desativa as outras do mesmo tipo
            if ($novoStatus) {
                AprovacaoExtraConfig::where('empresa_id', $empresaId)
                    ->where('tipo_processo', $config->tipo_processo)
                    ->where('id', '!=', $id)
                    ->update(['ativo' => false]);
            }

            $config->update(['ativo' => $novoStatus]);

            DB::commit();

            return response()->json(['ativo' => $config->fresh()->ativo]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna os tipos de processo disponíveis
     */
    public function tiposProcesso()
    {
        return response()->json([
            'tipos' => AprovacaoExtraConfig::TIPOS_PROCESSO
        ]);
    }

    /**
     * Verifica se o usuário atual pode aprovar uma solicitação
     */
    public function podeAprovar(Request $request)
    {
        $request->validate([
            'tipo_processo' => 'required|string'
        ]);

        $empresaId = auth()->user()->empresa_id;
        $userId = auth()->user()->id;

        $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, $request->tipo_processo);

        if (!$config) {
            return response()->json([
                'pode_aprovar' => false,
                'motivo' => 'Nenhuma aprovação extra configurada'
            ]);
        }

        $podeAprovar = $config->podeAprovar($userId);

        return response()->json([
            'pode_aprovar' => $podeAprovar,
            'nome_aprovacao' => $config->nome_aprovacao,
            'motivo' => $podeAprovar ? null : 'Usuário não autorizado'
        ]);
    }

    /**
     * Listar usuários da empresa para seleção
     */
    public function listarUsuarios(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $usuarios = User::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->select('id', 'nome', 'login')
            ->orderBy('nome')
            ->get();

        return response()->json($usuarios);
    }
}
