<?php

namespace App\Http\Controllers;

use App\Models\Beneficio;
use App\Models\BeneficioFeedback;
use App\Models\Cliente;
use App\Models\TipoBeneficio;

use DB;
use Illuminate\Http\Request;

class BeneficioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        //$this->authorize('cadastro_beneficio');
        return view('g.cadastros.beneficio.index');
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cadastro_beneficio_insert');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required',
                'tipobeneficio_id' => 'required',
                'valor' => 'required',
                'aplicacao' => 'required',
                'periodicidade' => 'required',
                'valor_descontado' => 'required',
                'opcao_desconto' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Benefício',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                Beneficio::create($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE BENEFICIO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . \auth()->user()->nome;
                \Log::debug($msg);
                //return response()->json(['msg' => $msg], 400);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    public function cadastroTipo(Request $request)
    {
        $this->authorize('cadastro_beneficio_insert');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required|min:1',
                'ativo' => 'required|boolean'
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar Tipo de Benefício',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            TipoBeneficio::create($dados);
            return response()->json([], 201);
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
     * @return Beneficio
     */
    public function edit(Beneficio $beneficio)
    {
        return $beneficio->load('TipoBeneficio');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, Beneficio $beneficio)
    {
        $this->authorize('cadastro_beneficio_update');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required',
                'tipobeneficio_id' => 'required',
                'valor' => 'required',
                'aplicacao' => 'required',
                'periodicidade' => 'required',
                'valor_descontado' => 'required',
                'opcao_desconto' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Editar Benefício',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $beneficio->update($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE BENEFICIO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                //return response()->json(['msg' => $msg], 400);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
        $this->authorize('cadastro_beneficio');
        $porPagina = $request->get('porPagina');
        $resultado = Beneficio::with('TipoBeneficio');
        $tipos = TipoBeneficio::whereHas('Empresa')->get();
        $tiposAtivos = TipoBeneficio::where('ativo', true)->get();


        // se tiver busca
        if ($request->filled('campoBusca')) {
            $resultado->where(function ($q) use ($request) {
                $q->where('assunto', 'like', '%' . $request->campoBusca . '%')
                    ->orWhereHas('Respostas', function ($q) use ($request) {
                        $q->where('resposta', 'like', '%' . $request->campoBusca . '%');
                    });
            });
        }
        // se for um tipo Problema ou Anotação
        if ($request->filled('campoTipo')) {
            $resultado->where('tipo', $request->campoTipo);
        }

        $permissoes = auth()->user()->listaDeHabilidades();

        $resultado = $resultado->orderByDesc('updated_at')->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'tipos' => $tipos,
                'tiposAtivos' => $tiposAtivos,
                'permissoes' => $permissoes,
            ]
        ], 200);

    }

    public function showBeneficio($feedback)
    {
        $this->authorize('cadastro_beneficio');

        $beneficio = Beneficio::get();

        $listaBeneficios = BeneficioFeedback::where('feedback_id', $feedback)
            ->with('Beneficio', 'Feedback.Curriculo')->get();

        return response()->json([
            'beneficio' => $beneficio,
            'listaBeneficio' => $listaBeneficios,
            'feedback_id' => $feedback
        ], 200);

    }

    public function storeBeneficio(Request $request, $feedback)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'feedback_id' => 'required',
            'beneficio_id' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar as Notas',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                BeneficioFeedback::create($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE AVALIACAO NOVENTA FEEDBACK:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
                return response()->json(['msg' => $msg], 400);
            }
        }
    }


    public function ativaDesativa($beneficio)
    {
        $this->authorize('cadastro_beneficio');

        $beneficio = TipoBeneficio::where('id', $beneficio)->first();

        $beneficio->ativo = !$beneficio->ativo;
        $beneficio->save();
        $beneficio->refresh();
        return response()->json(['ativo' => $beneficio->ativo], 201);
    }

    public function editarTipo(TipoBeneficio $tipobeneficio)
    {
        $this->authorize('cadastro_beneficio_update');
        return $tipobeneficio;
    }

    public function updateTipo(Request $request, TipoBeneficio $tipobeneficio)
    {
        $this->authorize('cadastro_beneficio_update');
        $dados = $request->input();

        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required',
            'ativo' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Tipo de Benefício',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $tipobeneficio->update($dados);
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

}
