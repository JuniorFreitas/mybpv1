<?php

namespace App\Http\Controllers;

use App\Models\AtaReuniao;
use App\Models\AtaReuniaoAcao;
use App\Models\AtaReuniaoAssunto;
use App\Models\AtaReuniaoParticipante;
use App\Models\AtaReuniaoTipo;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;
use PDF;

class AtaReuniaoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.administracao.atareuniao.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('atareuniao_insert');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'local' => 'required',
                'assuntos' => 'required',
                'tipos' => 'required',
                'acoes' => 'required',
                'participantes' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Ata de Reunião',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $newTime = explode(' às ', $dados['data_inicio']);
                $newDH = $newTime[0] . ' ' . $newTime[1];
                $data = new DataHora($newDH);
                $dados['data_inicio'] = $data->dataHoraInsert();

                $newTime = explode(' às ', $dados['data_fim']);
                $newDH = $newTime[0] . ' ' . $newTime[1];
                $data = new DataHora($newDH);
                $dados['data_fim'] = $data->dataHoraInsert();

                $ata = [
                    'quem_cadastrou' => auth()->user()->id,
                    'local' => $dados['local'],
                    'data_inicio' => $dados['data_inicio'],
                    'data_fim' => $dados['data_fim']
                ];

                $id = AtaReuniao::create($ata)->id;

                foreach ($dados['assuntos'] as $s) {
                    $as = [
                        'ata_reuniao_id' => $id,
                        'assunto' => $s['assunto'],
                    ];
                    AtaReuniaoAssunto::create($as);
                }

                foreach ($dados['tipos'] as $t) {
                    $ti = [
                        'ata_reuniao_id' => $id,
                        'tipo' => $t['tipo'],
                        'observacao' => $t['observacao'],
                    ];
                    AtaReuniaoTipo::create($ti);
                }

                foreach ($dados['acoes'] as $a) {
                    $ac = [
                        'ata_reuniao_id' => $id,
                        'acao' => $a['acao'],
                        'prazo' => $a['prazo'],
                        'continuo' => $a['continuo'],
                        'status' => 'andamento',
                        'observacao' => $a['observacao'],
                        'responsavel' => $a['responsavel'],
                        'email' => $a['email'],
                    ];
                    AtaReuniaoAcao::create($ac);
                }

                foreach ($dados['participantes'] as $p) {
                    $pa = [
                        'ata_reuniao_id' => $id,
                        'nome' => $p['nome'],
                        'funcao' => $p['funcao'],
                    ];
                    AtaReuniaoParticipante::create($pa);
                }


                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE ATA REUNIÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
                //return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
     * @param AtaReuniao $ataReuniao
     * @return void
     */
    public function edit($id)
    {
        return AtaReuniao::where('id', $id)->with('Assuntos', 'Tipos', 'Acoes', 'Participantes', 'QuemCadastrou')->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('beneficio_update');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'local' => 'required',
                'assuntos' => 'required',
                'tipos' => 'required',
                'acoes' => 'required',
                'participantes' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Editar Ata Reunião',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $ata = AtaReuniao::where('id', $dados['id'])->first();

                $dadosAta = [
                    'local' => $dados['local'],
                ];

                $ata->update($dadosAta);


                foreach ($dados['acoes'] as $a) {

                    $acao = AtaReuniaoAcao::where('id', $a['id'])->first();

                    $acao->update(['status' => $a['status']]);
                }


                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE ATA REUNIÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
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
        $this->authorize('atareuniao');
        $porPagina = $request->get('porPagina');
        $resultado = AtaReuniao::with('Assuntos', 'Tipos', 'Acoes', 'Participantes', 'QuemCadastrou');

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

        $resultado = $resultado->orderByDesc('updated_at')->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
            ]
        ], 200);

    }

    public function pdf($item)
    {

        $atareuniao = AtaReuniao::where('id', $item)->with('Assuntos', 'Tipos', 'Acoes', 'Participantes', 'QuemCadastrou')->first();

        //dd($atareuniao);

        $pdf = PDF::loadView('pdf.administracao.atareuniao.atareuniao', compact('atareuniao'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('ata_de_reuniao_'.(new DataHora())->nomeUnico() . ".pdf");
    }
}
