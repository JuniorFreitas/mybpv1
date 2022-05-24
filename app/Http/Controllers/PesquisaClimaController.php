<?php

namespace App\Http\Controllers;

use App\Models\PesquisaClimaCliente;
use App\Models\PesquisaClimaPergunta;
use App\Models\PesquisaClimaPerguntaRespostaCandidato;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesquisaClimaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('pesquisaclima.index');
    }


    public function autenticar(Request $request)
    {
        //faz a autenticacao
        if (Auth::attempt(['login' => $request->login, 'password' => $request->password, 'tipo' => 'Pessoa', 'ativo' => 1])) {
            $candidato = User::whereHas('Cliente.PesquisaClimaCliente')
                ->with('Cliente.PesquisaClimaCliente.Tipo.PesquisaClimaPergunta.Resposta', 'Feedback')->first();

            $feedback_id = \auth()->user()->Feedback->id;
            $cliente_id = $candidato->Cliente->id;

            $candidato->Cliente->PesquisaClimaCliente->Tipo->PesquisaClimaPergunta->transform(function ($item) use ($feedback_id, $cliente_id) {
                $item->respostacandidato = '';
                $item->feedback_id = $feedback_id;
                $item->cliente_id = $cliente_id;
                return $item;
            });

            return response()->json(['curriculo' => $candidato, 'autenticado' => true]);
        } else {
            return response()->json(['msg' => 'Não foi possivel autenticar, CPF e/ou Data de Nascimento inválido. Ou a EMPRESA ainda não autorizou seu acesso.', 'autenticado' => false], 400);
        }
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
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                '*.respostacandidato' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Pesquisa de Clima',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                foreach ($dados as $d) {

                    if (empty($d['resposta'])) {
                        $array2 = [
                            'feedback_id' => $d['feedback_id'],
                            'cliente_id' => $d['cliente_id'],
                            'pergunta_id' => $d['id'],
                            'resposta_id' => 143,
                            'respostadigitada' => $d['respostacandidato'],
                        ];

                        PesquisaClimaPerguntaRespostaCandidato::create($array2);

                    }
                    if (!empty($d['resposta'])) {
                        $array = [
                            'feedback_id' => $d['feedback_id'],
                            'cliente_id' => $d['cliente_id'],
                            'pergunta_id' => $d['id'],
                            'resposta_id' => $d['respostacandidato'],
                        ];

                        PesquisaClimaPerguntaRespostaCandidato::create($array);
                    }
                }

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE PESQUISA CLIMA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}";
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
                //return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }


    public function indexAdm()
    {
        return view('g.administracao.pesquisaclima.index');
    }


    public function chart($cliente_id)
    {

        $tipo = PesquisaClimaCliente::where('cliente_id', $cliente_id)->first();


        $perguntas = PesquisaClimaPergunta::where('tipo_id', $tipo->tipo_id)
            ->whereHas('PerguntaResposta', function ($q) use ($cliente_id) {
                $q->where('cliente_id', $cliente_id);
            })->with('Resposta.PerguntaResposta');

        if ($perguntas->count() == 0){
            return response()->json(['msg' => 'Nenhum Questionado respondido'],400);
        }

        $perguntas = $perguntas->get();

        $respostas = [];


        foreach ($perguntas as $key => $pergunta) {
            $dados = [];
            foreach ($pergunta['Resposta'] as $resposta) {
                $numero = count($resposta['PerguntaResposta']);
                if ($resposta['pergunta_id'] === $perguntas[$key]['id'] && $resposta['id'] != 143) {
                    $dados[] =
                        [
                            'resposta' => $resposta['resposta'],
                            'contagem' => intval($numero)
                        ];
                }
                if ($resposta['id'] == 143) {
                    foreach ($resposta['PerguntaResposta'] as $digitada)
                        $dados[] =
                            [
                                'respostadigitada' => $digitada['respostadigitada'],
                            ];
                }
            }
            $respostas[$key] = $dados;
            $respostas[$key] += ['pergunta' => $pergunta['pergunta']];
        }
        $valor = 0;
        foreach ($respostas[0] as $n) {
            if (isset($n['contagem'])) {
                $valor += $n['contagem'];
            }
        }
        return response()->json([
            'respostas' => $respostas,
            'entrevistados' => $valor
        ], 200);
    }

    public function atualizar()
    {
        $this->authorize('administracao_pesquisaclima');

        $resultado = PesquisaClimaCliente::with('Cliente')->get();

        return response()->json([
            'items' => $resultado,
        ], 200);

    }

//    public function contador($cliente_id)
//    {
//        $tipo = PesquisaClimaCliente::where('cliente_id', $cliente_id)->first();
//
//        $perguntas = PesquisaClimaPergunta::where('tipo_id', $tipo->tipo_id)
//            ->whereHas('PerguntaResposta', function ($q) use ($cliente_id) {
//                $q->where('cliente_id', $cliente_id);
//            })->with('Resposta.PerguntaResposta')->get();
//
//        $respostas = [];
//
//        //dd($perguntas[0]);
//
//        foreach ($perguntas as $key => $pergunta) {
//            $dados = [];
//            foreach ($pergunta['Resposta'] as $resposta) {
//                $numero = count($resposta['PerguntaResposta']);
//                if ($resposta['pergunta_id'] === $perguntas[$key]['id']) {
//                    $dados[] =
//                        [
//                            'resposta' => $resposta['resposta'],
//                            'contagem' => intval($numero)
//                        ];
//                }
//            }
//            $respostas[$key] = $dados;
//            $respostas[$key] += ['pergunta' => $pergunta['pergunta']];
//        }
//
//        return response()->json(['respostas' => $respostas]);
//    }

}
