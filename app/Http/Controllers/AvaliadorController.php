<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EmpresaConfig;
use App\Models\FeedbackCurriculo;
use App\Models\FormaPagamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AvaliadorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.avaliacoes.avaliador.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\EmpresaConfig $empresaConfig
     * @return \Illuminate\Http\Response
     */
    public function show(EmpresaConfig $config)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\EmpresaConfig $config
     * @return \Illuminate\Http\Response
     */
    public function edit(EmpresaConfig $config)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\EmpresaConfig $config
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmpresaConfig $config)
    {

        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'tipo_frequencia' => 'required|min:1',
            'limite_tolerancia' => 'required|numeric|min:1',
            'tempo_limite_falta' => 'required|numeric|min:1',
            'tempo_limite_saida' => 'required|numeric|min:1',
            'dia_nova_frequencia' => 'required|numeric|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao salvar as configuraçôes da empresa',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                \DB::beginTransaction();
                $config->update($dados);
                \DB::commit();
                return response()->json([], 200);
            }catch (\Exception $e) {
                \DB::rollBack();
                \Log::error($e->getMessage());
                return response()->json([
                    'msg' => 'Erro ao salvar as configuraçôes da empresa',
                    'erros' => $e->getMessage()
                ], 400);
            }

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\EmpresaConfig $config
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmpresaConfig $config)
    {
    }

    public function getPermissoes(Request $request)
    {

        return response()->json([
            'perimetros_insert' => auth()->user()->can('controle_ponto_perimetros_insert'),
            'perimetros_update' => auth()->user()->can('controle_ponto_perimetros_update'),
            'perimetros_delete' => auth()->user()->can('controle_ponto_perimetros_delete'),
            'perimetros_funcionarios' => auth()->user()->can('controle_ponto_perimetros_funcionarios'),
            'config_empresa' => auth()->user()->can('controle_ponto_config_empresa'),
        ]);
    }

    public function atualizarFuncionarios(Request $request)
    {
        $resultado = FeedbackCurriculo::select(['id','curriculo_id'])->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor','Avaliadores.Avaliador:id,nome')->admitidos();
        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->filled('campoBusca')) {
            $busca = $request->get('campoBusca');
            $resultado = $resultado->where('nome', 'like', '%' . $busca . '%');
        }
//        $resultado->with([
//            'avaliadoresFuncionario:id,nome'
//        ]);

        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items(),
        ]);
    }
}
