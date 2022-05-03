<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\VagaProjeto;
use Illuminate\Http\Request;
use DB;

class ProjetoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('cadastro_projetos');
        return view('g.cadastros.projeto.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('cadastro_projetos_insert');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required',
            'qnt_total' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Projeto',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();

                $dados['qnt_total_restante'] = $dados['qnt_total'];
                $dados['preenchidas'] = 0;

                Projeto::create($dados);

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return Projeto::find($id)->load('VagasProjeto.VagaAberta.Vaga');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->authorize('cadastro_projetos_update');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required',
            'qnt_total' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Projeto',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();

                $projeto = Projeto::find($id);

                switch (true) {
                    case ($dados['qnt_total'] > $projeto->qnt_total):
                        $dados['qnt_total_restante'] = ($dados['qnt_total'] - $projeto->qnt_total) + $projeto->qnt_total_restante;
                        break;
                    case ($dados['qnt_total'] < $projeto->qnt_total && $projeto->qnt_total == $projeto->qnt_total_restante && $projeto->preenchidas == 0):
                        $dados['qnt_total_restante'] = $dados['qnt_total'];
                        break;
                    case ($dados['qnt_total'] < $projeto->qnt_total && $dados['qnt_total'] >= $projeto->qnt_total_restante):
                        $dados['qnt_total_restante'] = $projeto->qnt_total_restante - ($projeto->qnt_total - $dados['qnt_total']);
                        break;
                    case ($dados['qnt_total'] < $projeto->qnt_total && $dados['qnt_total'] < $projeto->qnt_total_restante):
                        $dados['qnt_total_restante'] = ($projeto->qnt_total_restante - ($projeto->qnt_total - $dados['qnt_total']));
                        break;
                    case ($dados['qnt_total'] == $projeto->qnt_total):
                        $projeto->update($dados);
                        break;
                    default:
                        return response()->json(['msg' => 'Erro ao atualizar projeto, a quantidade de vagas restantes não pode ficar negativo!'], 400);

                }
                if ($dados['qnt_total_restante'] >= 0) {
                    $projeto->update($dados);
                } else {
                    return response()->json(['msg' => 'Erro ao atualizar projeto, a quantidade de vagas restantes não pode ficar negativo!'], 400);
                }
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_projetos');

        if ($request->filled('campoBusca')) {
            $resultado = Projeto::where('nome', 'like', '%' . $request->campoBusca . '%')->orderByDesc('updated_at')->paginate(50);
        } else {
            $resultado = Projeto::orderByDesc('updated_at')->paginate(50);
        }

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items()
            ]
        ]);
    }

    public function buscaProjeto($vaga_aberta_id)
    {
        $dados = VagaProjeto::whereVagaAbertaId($vaga_aberta_id)->with('Projeto')->first();
        return response()->json(['dados' => $dados, 'encontrou' => !empty($dados)], 201);
    }

    public function buscaTodosProjeto()
    {
        $dados = VagaProjeto::with('Projeto')->get();
        return response()->json(['dados' => $dados], 201);
    }
}
