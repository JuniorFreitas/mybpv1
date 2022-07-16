<?php

namespace App\Http\Controllers;

use App\Exports\carteiraEtiquetaExport;
use App\Jobs\JobExportaExcel;
use App\Models\ExameTreinamento;
use App\Models\FeedbackCurriculo;
use App\Models\Pivot\TreinamentoVencimento;
use App\Models\ResultadoIntegrado;
use App\Models\Treinamento;
use App\Models\Vencimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use MasterTag\DataHora;

class TreinamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.treinamentos.index');
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['cadastrou'] = auth()->id();
        $exame = $dados['exame'];

        try {
            DB::beginTransaction();
//            if ($exame['exame_realizado']){
////                return
//                $exame['user_id'] = auth()->id();
//                $exameTreinamento = ExameTreinamento::whereFeedbackId($exame['feedback_id']);
//
//                if ($exameTreinamento->count() == 0){
//                    ExameTreinamento::create($exame);
//                }else{
//                    $exameTreinamento->update($exame);
//                }
//            }

            $listaVencimentos = collect($dados['listaVencimentos'])->filter(function ($item) {
                return $item['fez_treinamento'];
            });

            if (isset($dados['id'])) {
                $this->authorize('treinamento_carteira-etiquetas_update');
                $treinamento = Treinamento::find($dados['id']);

                // retirando envio de e-mail ao atualizar
                unset($dados['enviado_email']);
                unset($dados['email_aberto']);
                unset($dados['data_email_aberto']);
                unset($dados['data_envio']);

                $treinamento->update($dados);
                $treinamento->Vencimentos()->detach();

            } else {
                $this->authorize('treinamento_carteira-etiquetas_insert');
                $treinamento = Treinamento::create($dados);
            }

            foreach ($listaVencimentos as $lista) {
                $dataHora = new DataHora($lista['data_treinamento']);
                $dataTreinamento = $dataHora->dataInsert();

                if ($dados['tipo'] == 'Parada') {
                    $dataVencimento = $lista['prazo_parada'] ? $dataHora->addDia($lista['prazo_parada']) : $lista['data_vencimento'];
                } else {
                    $dataVencimento = $lista['prazo_fixo'] ? $dataHora->addDia($lista['prazo_fixo']) : $lista['data_vencimento'];
                }

                $treinamento->Vencimentos()->attach($lista['id'], [
                    'data_treinamento' => $dataTreinamento,
                    'data_vencimento' => (new DataHora($dataVencimento))->dataInsert(),
                    'numero_fat' => $lista['numero_fat']
                ]);
            }

            DB::commit();

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();

            $msg = "error Treinamento:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}, USUARIO: " . auth()->user()->nome;

            \Log::debug($msg);
            return response()->json(['msg' => 'Não foi possivel realizar o cadastro'], 400);
        }
    }

    public function storeMassa(Request $request)
    {
        $dados = $request->input();
        $dados['cadastrou'] = auth()->id();
        $exame = $dados['exame'];

        try {
            DB::beginTransaction();

            $listaVencimentos = collect($dados['listaVencimentos'])->filter(function ($item) {
                return $item['fez_treinamento'];
            });

            foreach ($dados['selecionadosMassa'] as $feedback_id) {

                $dados['feedback_id'] = $feedback_id;
                $treinamento = Treinamento::whereFeedbackId($feedback_id)->first();

                if (isset($treinamento)) {
                    $this->authorize('treinamento_carteira-etiquetas_update');

                    unset($dados['enviado_email']);
                    unset($dados['email_aberto']);
                    unset($dados['data_email_aberto']);
                    unset($dados['data_envio']);

                    $treinamento->update($dados);
                    $treinamento->Vencimentos()->detach();

                } else {
                    $this->authorize('treinamento_carteira-etiquetas_insert');
                    $treinamento = Treinamento::create($dados);
                }


                foreach ($listaVencimentos as $lista) {
                    $dataHora = new DataHora($lista['data_treinamento']);
                    $dataTreinamento = $dataHora->dataInsert();

                    if ($dados['tipo'] == 'Parada') {
                        $dataVencimento = $lista['prazo_parada'] ? $dataHora->addDia($lista['prazo_parada']) : $lista['data_vencimento'];
                    } else {
                        $dataVencimento = $lista['prazo_fixo'] ? $dataHora->addDia($lista['prazo_fixo']) : $lista['data_vencimento'];
                    }

                    $treinamento->Vencimentos()->attach($lista['id'], [
                        'data_treinamento' => $dataTreinamento,
                        'data_vencimento' => (new DataHora($dataVencimento))->dataInsert(),
                        'numero_fat' => $lista['numero_fat'] ?? null
                    ]);
                }
            }

            DB::commit();

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();

            $msg = "error Treinamento:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}, USUARIO: " . auth()->user()->nome;

            \Log::debug($msg);
//            return response()->json(['msg' => 'Não foi possivel realizar o cadastro'], 400);
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Treinamento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function show(Treinamento $treinamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Treinamento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function edit($treinamento)
    {
        $treinamento = ResultadoIntegrado::whereFeedbackId($treinamento)->first();

        $treinamento = $treinamento->load('Treinamento', 'Curriculo:id,nome,nascimento,id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor', 'Admissao', 'Feedback.Exame');

        if (!is_null($treinamento->Admissao)) {
            $treinamento->nr_trinta_tres = $treinamento->Admissao->nr_trinta_tres == 'NÃO SE APLICA' ? false : true;
            $treinamento->nr_trinta_cinco = $treinamento->Admissao->nr_trinta_cinco == 'NÃO SE APLICA' ? false : true;
        } else {
            $treinamento->nr_trinta_tres = true;
            $treinamento->nr_trinta_cinco = true;
        }

        $treinamento->listaVencimentos = Vencimento::whereAtivo(true)->orderBy('ordem')->get()->transform(function ($item) use ($treinamento) {
            if ($treinamento->Treinamento) {
                $pivo = $treinamento->Treinamento->Vencimentos()->whereId($item->id);
                $item->data_treinamento = $pivo->count() > 0 ? $pivo->first()->pivot->data_treinamento : null;
                $item->data_vencimento = $pivo->count() > 0 ? $pivo->first()->pivot->data_vencimento : null;
                $item->numero_fat = $pivo->count() > 0 ? $pivo->first()->pivot->numero_fat : null;
                $item->fez_treinamento = $pivo->count() > 0 ? true : false;
            } else {
                $item->data_treinamento = null;
                $item->data_vencimento = null;
                $item->fez_treinamento = false;
                $item->numero_fat = null;
            }

            return $item;
        });

        return response()->json($treinamento, 200);

//         $treinamento = Treinamento::whereCurriculoId($curriculo_id)->first();

//         return $treinamento->load('Vencimentos');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Treinamento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $treinamento)
    {
        $dados = $request->input();
        $treinamento = ResultadoIntegrado::whereFeedbackId($treinamento)->first();
        try {
            DB::beginTransaction();
            DB::commit();

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::debug("error TREINAMENTO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}");
            return "error ADMISSAO AVULSA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}";
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Treinamento $treinamento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Treinamento $treinamento)
    {
        //
    }

    public function vencimentos()
    {
        $vencimentos = Vencimento::whereAtivo(true)->orderBy('ordem')->get();
        return response()->json($vencimentos, 200);
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);

        $itens = collect($resultado->items());
        $vencimentos = Vencimento::whereAtivo(true)->orderBy('ordem')->get();

        $vencimentos->transform(function ($i) {
            $i->fez_treinamento = false;
            return $i;
        });

        $itens->transform(function ($item) {
            if ($item->Treinamento) {
                $item->nr_33 = $item->Treinamento->Vencimentos->where('label', 'NR33')->count() > 0 ? $item->Treinamento->Vencimentos->where('label', 'NR33')->first()->pivot : null;
                $item->nr_35 = $item->Treinamento->Vencimentos->where('label', 'NR35')->count() > 0 ? $item->Treinamento->Vencimentos->where('label', 'NR35')->first()->pivot : null;
                $item->ebtv = $item->Treinamento->Vencimentos->where('label', 'EBTV')->count() > 0 ? $item->Treinamento->Vencimentos->where('label', 'EBTV')->first()->pivot : null;
            } else {
                $item->nr_33 = null;
                $item->nr_35 = null;
                $item->ebtv = null;
            }
            return $item;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'vencimentos' => $vencimentos
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $this->authorize('treinamento_carteira-etiquetas');

        $resultado = FeedbackCurriculo::Admitidos()->whereHas('ResultadoIntegrado', function ($q) {
            $q->whereEncaminhadoTreinamento(true);
        })->with(
            'Curriculo:id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
            'VagaSelecionada:id,nome',
            'Admissao.AreaEtiqueta',
            'Curriculo.FotoTres:id',
            'Treinamento.Vencimentos',
            'Treinamento.QuemCadastrou'
        );

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('Feedback.VagaSelecionada', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('Feedback.Curriculo', function ($q) use ($request) {
                $q->whereUfVaga($request->campoUf);
            });
        }

        if ($request->filled('campoArea')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->whereAreaEtiquetaId($request->campoArea);
            });
        }

        if ($request->filled('campoCargo')) {
            $resultado->whereHas('Admissao', function ($query) use ($request) {
                $query->where('cargo', 'like', '%' . $request->campoCargo . '%');
            });
        }

        if ($request->filled('campo_treinados')) {

            if ($request->campo_treinados == 'true') {
                $resultado->has('Treinamento');
            }
            if ($request->campo_treinados == 'false') {
                $resultado->whereDoesntHave('Treinamento');
            }

        }

        if ($request->filled('campoNr_trinta_tres')) {
            if ($request->campoNr_trinta_tres) {
                $resultado->whereHas('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'NR33');
                });
            }
            if (!$request->campoNr_trinta_tres) {
                $resultado->whereDoesntHave('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'NR33');
                });
            }
            if ($request->campoNr_trinta_tres == 'NÃO SE APLICA') {
                $resultado->whereHas('Admissao', function ($query) use ($request) {
                    $query->where('nr_trinta_tres', $request->campoNr_trinta_tres);
                });
            }
        }

        if ($request->filled('campoNr_trinta_cinco')) {
            if ($request->campoNr_trinta_cinco) {
                $resultado->whereHas('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'NR35');
                });
            }
            if (!$request->campoNr_trinta_cinco) {
                $resultado->whereDoesntHave('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'NR35');
                });
            }
            if ($request->campoNr_trinta_cinco == 'NÃO SE APLICA') {
                $resultado->whereHas('Admissao', function ($query) use ($request) {
                    $query->where('nr_trinta_tres', $request->campoNr_trinta_tres);
                });
            }
        }

        if ($request->filled('campoNr_ebtv')) {

            if ($request->campoNr_ebtv) {
                $resultado->whereHas('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'EBTV');
                });
            }
            if (!$request->campoNr_ebtv) {
                $resultado->whereDoesntHave('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'EBTV');
                });
            }

        }

        if ($request->filled('campoAdmitido')) {
            if ($request->campoAdmitido == 'true') {
                $resultado->whereHas('Admissao', function ($q) {
                    $q->whereStatus('ADMITIDO');
                });
            }
            if ($request->campoAdmitido == 'false') {
                $resultado->whereDoesntHave('Admissao');
            }
        }

        if ($request->filled('campoCracha')) {
            if ($request->campoCracha == 'true') {
                $resultado->whereHas('Admissao', function ($q) {
                    $q->whereNotNull('numero_cracha');
                });
            }
            if ($request->campoCracha == 'false') {
                $resultado->whereDoesntHave('Admissao', function ($query) use ($request) {
                    $query->whereNull('numero_cracha');
                });
            }
        }

        if ($request->filled('campoFoto')) {
            if ($request->campoFoto == 'true') {
                $resultado->has('FotoTres');
            }
            if ($request->campoFoto == 'false') {
                $resultado->whereDoesntHave('FotoTres');
            }
        }


        if ($request->filled('campoPcd')) {
            $campoPcd = $request->campoPcd == 'true' ? true : false;
            $resultado->whereHas('Curriculo', function ($query) use ($campoPcd) {
                $query->wherePcd($campoPcd);
            });
        }

        $campoVencimento = $request->campoVencimento == 'true' ? true : false;
        if ($campoVencimento) {
            $periodo = explode(' até ', $request->vencimento);
            $dataInicio = new DataHora($periodo[0]);
            $dataFim = new DataHora($periodo[1]);
            $resultado->whereHas('Treinamento', function ($query) use ($dataInicio, $dataFim) {
                $query->whereHas('Vencimentos', function ($q) use ($dataInicio, $dataFim) {
                    $q->where('data_vencimento', '>=', $dataInicio->dataInsert())->where('data_vencimento', '<=', $dataFim->dataInsert());
                });
            });
        }

        return $resultado->orderByDesc('created_at');
    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();
        $head = [
            "Nome",
            "Vaga",
            "Cargo",
            "Área",
            "Foto 3x4",
            "NR-33",
            "NR-35",
            "EBTV",
            "Ultima Atualização",
            "Quem Atualizou",
            "Data Admissão",
            "PCD",
            "Status",
            "Tipo"
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->Curriculo->nome,
                $row->VagaSelecionada->nome,
                $row->Admissao->funcao,
                $row->Admissao->AreaEtiqueta->label,
                $row->Curriculo->foto_tres ? "Sim" : "Não",
                $row->nr_33 != null ? "Sim" : "Não",
                $row->nr_35 != null ? "Sim" : "Não",
                $row->ebtv != null ? "Sim" : "Não",
                $row->Treinamento ? $row->Treinamento->created_at : "",
                $row->Treinamento ? $row->Treinamento->QuemCadastrou->nome : "",
                $row->Admissao->data_admissao,
                $row->Curriculo->pcd != false ? "SIM":"NÂO",
                $row->Admissao->status,
                $row->Treinamento ? $row->Treinamento->tipo : "",

            ];
        }

        $nameArquivo = "carteira_etiqueta" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Carteira - Etiqueta", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function carteiraPdf(Request $request)
    {
        $treinamentos = Treinamento::whereIn('feedback_id', $request->selecionados)->get();

        return view('pdf.treinamento.carteira.pdf', compact('treinamentos'));
    }

    public function enviarCarteiraEmail(Request $request)
    {
        $dados = $request->input();
        try {
            Mail::send('email.treinamento.carteira', $dados, function ($m) use ($dados) {
                $m->from('naoresponda@mybp.com.br', 'MyBP');
                $m->subject("Carteira e etiqueta de treinamentos");
                $m->to(trim(mb_strtolower($dados['email'])));
            });

            $treinamento = Treinamento::find(\Crypt::decrypt($dados['token']));

            $treinamento->update([
                'enviado_email' => true,
                'email_envio' => $dados['email'],
                'enviou_id' => auth()->id(),
                'data_envio' => (new DataHora())->dataHoraInsert()
            ]);
            return response()->json(['enviado' => true], 200);
        } catch (\Exception $e) {
            $msg = "Error ao enviar e-maill de Revisão no Cloud: {$e->getMessage()}, {$e->getFile()}, {$e->getLine()}, {$e->getCode()}, {$e->getTrace()} ";
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
            return response()->json(['enviado' => false], 400);
        }
    }

    public function carteiraIndividual($curriculo)
    {
        $treinamentos = Treinamento::whereId(\Crypt::decrypt($curriculo))->get();
        $treinamentos->first()->update([
            'email_aberto' => true,
            'data_email_aberto' => (new DataHora())->dataHoraInsert()
        ]);

        return view('pdf.treinamento.carteira.pdf', compact('treinamentos'));
//        return view('pdf.treinamento.carteira.individualEmail', compact('treinamento'));
    }

    public function treinamentoProximoVencimento(Request $request)
    {
        $hoje = new DataHora();
        $trintaDias = new DataHora($hoje->addDia(60));

        $treinamentos = Treinamento::whereHas('Curriculo.Feedback', function ($q) {
            $q->whereClienteId(1);
        })->whereHas('Vencimentos', function ($q) use ($trintaDias) {
            $q->where('data_vencimento', '<=', $trintaDias->dataInsert());
        })->with('Curriculo', 'Vencimentos');


        if ($treinamentos->count() >= 1) {
            $data = $request->input();
            $dados = ['dados' => $treinamentos->get()];
            try {
                Mail::send('email.treinamento.vencendo', $dados, function ($m) use ($dados, $data) {
                    $m->from('naoresponda@mybp.com.br', 'SGIBPSE - E-mail Automatico');
                    $m->subject("Treinamentos Vencidos ou próximo ao vencimento");
                    $m->to(trim(mb_strtolower($data['email'])));
                });
                \Log::info("E-mail enviado com sucesso de treinamento  vencidos ou vencendo total de {$treinamentos->count()}");
                return response()->json(['enviado' => true], 200);
            } catch (\Exception $e) {
                \Log::debug("Error ao enviar e-mail de Vencimento de Servicos: {$e->getMessage()}, {$e->getFile()}, {$e->getLine()}, {$e->getCode()}, {$e->getTrace()} ");
                return response()->json(['enviado' => false], 400);
            }
        }
    }
}
