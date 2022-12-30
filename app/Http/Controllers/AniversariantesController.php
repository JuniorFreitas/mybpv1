<?php

namespace App\Http\Controllers;

use App\Jobs\JobAniversariantes;
use App\Models\Curriculo;
use App\Models\ParabensEnviado;
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
//        dd($request->selecionados);

        $dados = [
            'selecionados' => $request->selecionados,
            'empresa_id' => auth()->user()->empresa_id
        ];

        foreach ($request->selecionados as $selecionado){
            ParabensEnviado::withoutGlobalScopes()->create([
                'empresa_id' => auth()->user()->empresa_id,
                'status' => ParabensEnviado::STATUS_ENVIANDO,
                'curriculo_id' => $selecionado,
                'ano' => (int) date('Y'),
            ]);
        }

        JobAniversariantes::dispatch($dados);

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
              $dia_nascimento = $data_nascimento->dia();
              return [
                  'idade' => $item->idade,
                  'nome' => $item->nome,
                  'email' => $item->email,
                  'id' => $item->id,
                  'aniversario' => $data_nascimento->dia().'/'.$data_nascimento->mes(),
                  'enviado' => $item->Parabens()->count() > 0 ? $item->Parabens->status : 'Não',
                  'hoje' => date('d') == $dia_nascimento,
              ];
            });

        return response()->json(['dados' => $funcionarios], 200);
    }
}
