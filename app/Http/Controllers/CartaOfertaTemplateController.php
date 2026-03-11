<?php

namespace App\Http\Controllers;

use App\Models\CartaOfertaTemplate;
use App\Models\Cliente;
use App\Services\CartaOferta\CartaOfertaTemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartaOfertaTemplateController extends Controller
{
    public function index()
    {
        return view('g.administracao.carta-oferta-template.index');
    }

    public function dados()
    {
        $empresaId = auth()->user()->empresa_id;

        $template = CartaOfertaTemplate::where('empresa_id', $empresaId)
            ->orderBy('versao', 'desc')
            ->first();

        return response()->json([
            'template' => $template,
        ]);
    }

    public function salvar(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:150',
            'conteudo_html' => 'required|string',
            'status' => 'required|in:' . CartaOfertaTemplate::STATUS_PUBLICADO . ',' . CartaOfertaTemplate::STATUS_RASCUNHO,
        ]);

        $empresaId = auth()->user()->empresa_id;

        try {
            DB::beginTransaction();

            $versaoAtual = CartaOfertaTemplate::where('empresa_id', $empresaId)->max('versao');
            $novaVersao = $versaoAtual ? $versaoAtual + 1 : 1;

            if ($request->status === CartaOfertaTemplate::STATUS_PUBLICADO) {
                CartaOfertaTemplate::where('empresa_id', $empresaId)
                    ->update(['status' => CartaOfertaTemplate::STATUS_RASCUNHO]);
            }

            $template = CartaOfertaTemplate::create([
                'empresa_id' => $empresaId,
                'titulo' => $request->titulo,
                'conteudo_html' => $request->conteudo_html,
                'status' => $request->status,
                'versao' => $novaVersao,
                'criado_por' => auth()->id(),
                'atualizado_por' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'template' => $template,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar template: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function preview(Request $request, CartaOfertaTemplateRenderer $renderer)
    {
        $request->validate([
            'conteudo_html' => 'required|string',
        ]);

        $empresaId = auth()->user()->empresa_id;
        $empresa = Cliente::select(['id', 'razao_social', 'nome_fantasia', 'cnpj'])
            ->where('id', $empresaId)
            ->first();

        $dados = [
            'colaborador' => [
                'nome' => 'Joao da Silva',
                'cpf' => '000.000.000-00',
                'email' => 'joao.silva@exemplo.com',
            ],
            'cargo' => 'Analista de RH',
            'setor' => 'Recursos Humanos',
            'salario' => 'R$ 3.500,00',
            'data_inicio' => '10/03/2026',
            'empresa' => [
                'nome_fantasia' => $empresa->nome_fantasia ?? '',
                'razao_social' => $empresa->razao_social ?? '',
                'cnpj' => $empresa->cnpj ?? '',
            ],
            'data_emissao' => now()->format('d/m/Y'),
        ];

        $html = $renderer->render($request->conteudo_html, $dados);

        return response()->json([
            'html' => $html,
        ]);
    }
}
