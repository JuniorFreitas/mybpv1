<?php

namespace App\Http\Controllers;

use App\Models\CartaOfertaTemplate;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\VagasAbertas;
use App\Services\CartaOferta\CartaOfertaTemplateRenderer;
use App\Services\CartaOferta\CartaOfertaContextoBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

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

        $publicado = CartaOfertaTemplate::where('empresa_id', $empresaId)
            ->publicado()
            ->orderBy('versao', 'desc')
            ->first();

        return response()->json([
            'template' => $template,
            'publicado' => $publicado,
            'historico' => $this->listarHistorico($empresaId),
            'html_padrao' => CartaOfertaTemplate::htmlPadrao(),
        ]);
    }

    public function historico()
    {
        return response()->json([
            'historico' => $this->listarHistorico((int) auth()->user()->empresa_id),
        ]);
    }

    public function versao($id)
    {
        $template = CartaOfertaTemplate::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('id', $id)
            ->first();

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Versão não encontrada.',
            ], 404);
        }

        return response()->json([
            'template' => $template,
        ]);
    }

    public function padrao()
    {
        return response()->json([
            'titulo' => 'Carta Oferta',
            'conteudo_html' => CartaOfertaTemplate::htmlPadrao(),
        ]);
    }

    protected function listarHistorico(int $empresaId)
    {
        return CartaOfertaTemplate::query()
            ->where('empresa_id', $empresaId)
            ->with(['criador' => function ($q) {
                $q->withoutGlobalScopes()->select(['id', 'nome']);
            }])
            ->orderByDesc('versao')
            ->limit(50)
            ->get(['id', 'titulo', 'status', 'versao', 'criado_por', 'created_at'])
            ->map(function (CartaOfertaTemplate $item) {
                return [
                    'id' => $item->id,
                    'titulo' => $item->titulo,
                    'status' => $item->status,
                    'versao' => $item->versao,
                    'criado_por_nome' => $item->criador->nome ?? '—',
                    'created_at' => $item->created_at
                        ? (new DataHora($item->created_at))->dataHoraCompleta()
                        : null,
                ];
            })
            ->values();
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

    public function preview(Request $request, CartaOfertaTemplateRenderer $renderer, CartaOfertaContextoBuilder $builder)
    {
        $request->validate([
            'conteudo_html' => 'required|string',
            'curriculo_id' => 'nullable|integer',
            'vagas_abertas_id' => 'nullable|integer',
            'setor' => 'nullable|string|max:150',
            'salario' => 'nullable|string|max:80',
            'data_inicio' => 'nullable|string|max:20',
            'usar_dados_reais' => 'nullable|boolean',
        ]);

        $empresaId = auth()->user()->empresa_id;
        $empresa = Cliente::withoutGlobalScopes()
            ->select(['id', 'razao_social', 'nome_fantasia', 'cnpj'])
            ->where('id', $empresaId)
            ->first();

        $usarReais = filter_var($request->input('usar_dados_reais'), FILTER_VALIDATE_BOOLEAN)
            && $request->filled('curriculo_id');

        if ($usarReais) {
            // curriculos não tem empresa_id: tenant via users (Pessoa) / ScopeEmpresa
            $curriculo = Curriculo::query()
                ->select(['id', 'nome', 'cpf', 'email'])
                ->where('id', $request->curriculo_id)
                ->whereHas('Pessoa', function ($q) use ($empresaId) {
                    $q->where('empresa_id', $empresaId);
                })
                ->first();

            if (!$curriculo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Currículo não encontrado nesta empresa.',
                ], 422);
            }

            $vaga = null;
            if ($request->filled('vagas_abertas_id')) {
                $vaga = VagasAbertas::query()
                    ->where('id', $request->vagas_abertas_id)
                    ->where('empresa_id', $empresaId)
                    ->first();
            }

            $dados = $builder->paraPreview($curriculo, $vaga, $empresa, [
                'setor' => $request->input('setor'),
                'salario' => $request->input('salario'),
                'data_inicio' => $request->input('data_inicio'),
            ]);
            $modo = 'reais';
        } else {
            $dados = $builder->fake($empresa);
            $modo = 'simulados';
        }

        $html = $renderer->render($request->conteudo_html, $dados);

        return response()->json([
            'html' => $html,
            'modo' => $modo,
        ]);
    }
}
