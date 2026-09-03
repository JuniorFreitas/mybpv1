<?php

namespace App\Http\Controllers;

use App\Models\GestorAprovacaoConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestorAprovacaoConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('g.administracao.gestor-aprovacao-config.index');
    }

    /**
     * Lista todas as configurações de gestor aprovação da empresa
     */
    public function listar(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $configs = GestorAprovacaoConfig::where('empresa_id', $empresaId)
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
        $config = GestorAprovacaoConfig::getConfigAtiva($empresaId, $request->tipo_processo);

        return response()->json([
            'config' => $config,
            'tem_gestor_aprovacao' => !is_null($config)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_processo' => 'required|in:demissao,ferias,mudanca_cargo,transferencia,intermitente_fixo,valor_extra,requisicao_vaga,admissao',
            'gestor_aprovacao_id' => 'required|exists:users,id',
            'ativo' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $empresaId = auth()->user()->empresa_id;

            $configExistente = GestorAprovacaoConfig::where('empresa_id', $empresaId)
                ->where('tipo_processo', $request->tipo_processo)
                ->first();

            if ($configExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe uma configuração para este tipo de processo'
                ], 422);
            }

            $config = GestorAprovacaoConfig::create([
                'empresa_id' => $empresaId,
                'tipo_processo' => $request->tipo_processo,
                'gestor_aprovacao_id' => $request->gestor_aprovacao_id,
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
            'gestor_aprovacao_id' => 'required|exists:users,id',
            'ativo' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $empresaId = auth()->user()->empresa_id;
            $config = GestorAprovacaoConfig::where('empresa_id', $empresaId)
                ->findOrFail($id);

            $config->update([
                'gestor_aprovacao_id' => $request->gestor_aprovacao_id,
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
            $config = GestorAprovacaoConfig::where('empresa_id', $empresaId)
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
            $config = GestorAprovacaoConfig::where('empresa_id', $empresaId)
                ->findOrFail($id);

            $config->update(['ativo' => !$config->ativo]);

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
            'tipos' => GestorAprovacaoConfig::TIPOS_PROCESSO
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
