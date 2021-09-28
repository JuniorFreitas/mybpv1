<?php

namespace App\Http\Controllers;

use App\Mail\AniversariantesMail;
use App\Models\Cliente;
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
        $this->authorize('aniversariantes');
        $porPagina = $request->get('porPagina');
        $dataHoje = new DataHora();

        $dia = $dataHoje->dia();
        $mes = $dataHoje->mes();
        $ano = $dataHoje->ano();

        $ano = intval($ano);
        $curriculo = Curriculo::whereHas('FeedBack', function ($q) {
            $q->whereHas('Cliente');
            $q->where('selecionado', 'sim');
        })
            ->with(['Parabens' => function ($q) use ($ano) {
                $q->where('ano', $ano);
            }])
            ->get();

        $resultado = [];
        foreach ($curriculo as $c) {
            $data = explode('/', $c->nascimento);
            if ($data[0] == $dia && $data[1] == $mes) {
                if (!isset($c['Parabens'])) {
                    $resultado[] = [
                        'idade' => $c->idade,
                        'nome' => $c->nome,
                        'email' => $c->email,
                        'id' => $c->id,
                        'aniversario' => $c->nascimento,
                        'tipo' => 'Funcionário'
                    ];
                }
            }
        }

        $cliente = Cliente::whereAtivo(true)->with('Parabens')->get();
        $resultado2 = [];
        foreach ($cliente as $ci) {
            $data = explode('/', $ci->aniversario);
            if ($data[0] == $dia && $data[1] == $mes) {
                if (!isset($ci['Parabens'])) {
                    $resultado2[] = [
                        'nome' => $ci->nome_fantasia,
                        'email' => $ci->email,
                        'id' => $ci->id,
                        'aniversario' => $ci->aniversario,
                        'tipo' => 'Cliente'
                    ];
                }
            }
        }

        $resultado = array_merge($resultado, $resultado2);

        return response()->json(['dados' => $resultado], 200);
    }
}
