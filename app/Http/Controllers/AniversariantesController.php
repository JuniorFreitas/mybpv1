<?php

namespace App\Http\Controllers;

use App\Mail\AniversariantesMail;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\ParabensEnviado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class AniversariantesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.administracao.aniversariantes.index');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
        //
    }

    public function show()
    {

    }

    public function enviaEmail(Request $request)
    {
        $dt = new DataHora();
        $ano = $dt->ano();
        if ($request['tipo'] == 'Funcionário') {
            $dados = [
                'curriculo_id' => $request['id'],
                'ano' => $ano,
            ];
        } else {
            $dados = [
                'cliente_id' => $request['id'],
                'ano' => $ano,
            ];
        }

        ParabensEnviado::create($dados);

        \Mail::send(new AniversariantesMail([
            'nome' => $request['nome'],
            'email' => $request['email'],
        ]));

        return response()->json('', 200);
    }

    public function atualizar(Request $request)
    {
        $this->authorize('administracao_aniversariantes');
        $dataHoje = new DataHora();
        $ano = $dataHoje->ano();
        $ano = intval($ano);

        $funcionarios = Curriculo::select(['id','nome','email','nascimento', 'rg', 'orgao_expeditor'])->whereHas('FeedBack', function($q){
            $q->admitidos();
        })->whereRaw('month(nascimento) = month(now())')
          ->with('Parabens', function ($query) use ($ano) {
             $query->where('ano', $ano);
          })->orderByRaw('day(nascimento)')->get()->map(function ($item) {
              $data_nascimento = new DataHora($item->nascimento);
              return [
                  'idade' => $item->idade,
                  'nome' => $item->nome,
                  'email' => $item->email,
                  'id' => $item->id,
                  'aniversario' => $data_nascimento->dia().'/'.$data_nascimento->mes(),
                  'enviado' => $item->Parabens()->count() > 0 ? 'Sim' : 'Não',
              ];
            });

        return response()->json(['dados' => $funcionarios], 200);
    }
}
