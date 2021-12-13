<?php

namespace App\Http\Controllers;

use App\Exports\admissao\apontamento\cihExport;
use App\Models\AreaEtiqueta;
use App\Models\Arquivo;
use App\Models\Cih;
use App\Models\CihTag;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use MasterTag\DataHora;
use PDF;

class CihController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.admissao.apontamento.cih.index');
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
        $this->authorize('cih_lancar');
        $dados = $request->input();
        $dados['user_lancamento_id'] = auth()->id();
        $dados['data_lancamento'] = (new DataHora($dados['data_lancamento'] . ' ' . date('H:m:s')))->dataHoraInsert();
        $dados['outra_tag'] = $dados['tag_id'] == 0 ? $dados['outra_tag'] : null;
        $dados['outra_area'] = $dados['area_id'] == 0 ? $dados['outra_area'] : null;

        $dadosValidados = \Validator::make($dados, [
            'tag_id' => 'required',
            'feedback_id' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $dados['tag_id'] = $dados['tag_id'] > 0 ? $dados['tag_id'] : null;
                $dados['area_id'] = $dados['area_id'] > 0 ? $dados['area_id'] : null;
                $dados['empresa_id'] =
                $cih = Cih::create($dados);

                if (isset($dados['anexosDel'])) {
                    foreach ($dados['anexosDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }

                // inseri uma nova foto de anexo
                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $cih->Anexos()->attach($arquivo->id);
                        }
                    }
                }

                DB::commit();
                return response()->json([$cih->load('Anexos')], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE CIH:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
//                return response()->json(['msg' => $msg], 400);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Cih $cih
     * @return \Illuminate\Http\Response
     */
    public function show(Cih $cih)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Cih $cih
     * @return \Illuminate\Http\Response
     */
    public function edit(Cih $cih)
    {
        $cih->autocomplete_label_colaborador = "{$cih->Colaborador->Curriculo->nome} - {$cih->Colaborador->VagaSelecionada->nome}";
        $cih->autocomplete_label_colaborador_anterior = $cih->autocomplete_label_colaborador;
        $cih->tag_id = is_null($cih->tag_id) ? 0 : $cih->tag_id;
        $cih->area_id = is_null($cih->area_id) ? 0 : $cih->area_id;
        $cih->status_aprovacao = $cih->status;


        return $cih->load('Anexos', 'Tag', 'Area');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Cih $cih
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cih $cih)
    {
        //
    }

    /**
     * Aprovar the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Cih $cih
     * @return \Illuminate\Http\Response
     */
    public function aprovar(Request $request, Cih $cih)
    {
        $this->authorize('cih_aprovar');
        $dados = $request->input();
        $dados['user_aprovacao_id'] = auth()->id();
        $dados['status'] = $dados['status_aprovacao'];
        $dados['data_aprovacao'] = (new DataHora())->dataHoraInsert();

        try {
            DB::beginTransaction();
            $cih->update([
                'user_aprovacao_id' => $dados['user_aprovacao_id'],
                'data_aprovacao' => $dados['data_aprovacao'],
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status' => $dados['status']
            ]);
            DB::commit();
            return response()->json([$cih], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE CIH:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Cih $cih
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cih $cih)
    {
        //
    }


    public function atualizar(Request $request)
    {
        $resultado = Cih::with('Tag', 'Area',
            'Colaborador.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        );


        $tags = CihTag::orderBy('label')->whereAtivo(true)->get();
        $areas = AreaEtiqueta::orderBy('label')->whereAtivo(true)->get();

        $data = new DataHora();
        $intervalo = $data->dataCompleta() . ' até ' . $data->addDia(7);

        $clientes = Cliente::whereAtivo(true)->get();

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Colaborador.Curriculo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%');
            });
        }

        if ($request->filled('campoStatus')) {
            $resultado->whereStatus($request->campoStatus);
        }

        if ($request->filled('campoTags')) {
            $resultado->whereHas('Tag', function ($q) use ($request) {
                $q->whereId($request->campoTags);
            });
        }

        if ($request->filled('campoAreas')) {
            $resultado->whereHas('Area', function ($q) use ($request) {
                $q->whereId($request->campoAreas);
            });
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'tags' => $tags,
                'cliente_id' => auth()->user()->cliente_id,
                'intervalo' => $intervalo,
                'areas' => $areas,
                'listaClientes' => $clientes,
                'hoje' => (new DataHora())->dataCompleta()
            ]
        ]);
    }


    public function atualizarHistorico($feedback)
    {

        $resultado = Cih::whereFeedbackId($feedback)->with('Tag', 'Area',
            'Colaborador.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        );

        $data = new DataHora();
        $intervalo = $data->dataCompleta() . ' até ' . $data->addDia(7);

        //$clientes = Clientes::whereAtivo(true)->get();

        return response()->json([
            'itens' => $resultado->get(),
            'cliente_id' => auth()->user()->cliente_id,
            'intervalo' => $intervalo,
            //'listaClientes' => $clientes,
            'hoje' => (new DataHora())->dataCompleta()
        ]);
    }

    public function relatorioPdf(Request $request)
    {
        $intervalo = explode(' até ', $request->intervalo);
        $dataInicio = (new DataHora($intervalo[0] . ' 00:00:00'))->dataHoraInsert();
        $dataFim = (new DataHora($intervalo[1] . ' 23:59:59'))->dataHoraInsert();


        $dados = Cih::with('Tag', 'Empresa',
            'Colaborador.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        )->where('data_aprovacao', '>=', $dataInicio)
            ->where('data_aprovacao', '<=', $dataFim)
            ->whereStatus('aprovado');

        $dados = $dados->orderBy('data_aprovacao')->get();

        $empresa = User::whereId(auth()->user()->empresa_id)->first();

        $pdf = PDF::loadView('pdf.admissao.apontamento.cih', compact('dados', 'empresa', 'dataInicio', 'dataFim'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("relatorio_cih_" . (new DataHora())->nomeUnico() . ".pdf");
    }

    public function relatorioExcel(Request $request)
    {

        $intervalo = explode(' até ', $request->intervalo);
        $dataInicio = (new DataHora($intervalo[0] . ' 00:00:00'))->dataHoraInsert();
        $dataFim = (new DataHora($intervalo[1] . ' 23:59:59'))->dataHoraInsert();


        $dados = Cih::with('Tag',
            'Cliente:id,nome,razao_social,cpf,cnpj,nome_fantasia',
            'Colaborador.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        )->where('data_aprovacao', '>=', $dataInicio)
            ->where('data_aprovacao', '<=', $dataFim)
            ->whereIn('status', ['aprovado', 'reprovado']);

        if (auth()->user()->cliente_id == 1) {
            $dados->whereClienteId($request->cliente_relatorio);
            $cliente = Cliente::find($request->cliente_relatorio);
        } else {
            $dados->whereClienteId(auth()->user()->cliente_id);
            $cliente = Cliente::find(auth()->user()->cliente_id);
        }

        $dados = $dados->orderBy('data_aprovacao')->get();

        return Excel::download(new cihExport($dados), 'cih_' . (new DataHora())->nomeUnico() . '.xlsx');
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_CIH);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CIH, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CIH, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CIH, $arquivo);
    }


}
