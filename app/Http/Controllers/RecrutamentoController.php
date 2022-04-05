<?php

namespace App\Http\Controllers;

use App\Exports\CurriculoExport;
use App\Mail\DesclassificacaoMail;
use App\Mail\ProvaMail;
use App\Mail\ProximaEtapaMail;
use App\Models\Curriculo;
use App\Models\NotificacaoWhats;
use App\Models\SimuladoVaga;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;
use MasterTag\DataHora;
use PDF;

class RecrutamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $curriculos = Curriculo::count();
        return view('g.curriculos.recrutamento.index', compact('curriculos'));
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Curriculo $recrutamento)
    {
        $pdf = PDF::loadView('pdf.recrutamento.curriculo', compact('recrutamento'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("curriculo" . Str::slug($recrutamento->nome) . ".pdf");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Curriculo $recrutamento)
    {

//        $value = cache()->rememberForever("curriculo_{$recrutamento->id}", function () use($recrutamento) {
        return $recrutamento->load('Atualizacao', 'Qualificacoes', 'Experiencias', 'VagaAberta.VagaSelecionada', 'Formacao', 'Telefones', 'Usuario')->load(['FeedBack' => function ($query) {
            $query->with('VagaSelecionada.SimuladoVaga', 'Cliente', 'QuemMarcou', 'TelPrincipal');
        }]);
//        });
//
//        return $value;

//        return \Cache::remember("curriculo_{$recrutamento->id}", function () use ($recrutamento) {
//           return $recrutamento->load('Atualizacao', 'Qualificacoes', 'Experiencias', 'Vaga', 'Formacao', 'Telefones', 'Usuario', ['FeedBack' => function ($query) {
//                $query->with('VagaSelecionada.SimuladoVaga', 'Cliente', 'QuemMarcou', 'TelPrincipal');
//            }]);
//        });

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Curriculo $recrutamento)
    {
        $curriculo = $recrutamento;
        $dados = $request->input();
        $dados['contato_realizado'] = $dados['contato_realizado'] == 'true' ? true : false;

        $dados['interesse'] = $dados['interesse'] == 'true' ? true : false;
        $dados['data_entrevista'] = $dados['interesse'] ? $dados['data_entrevista'] : null;

        $dados['tem_provas'] = $dados['tem_provas'] == 'true' ? true : false;
        $dados['envia_mail_provas'] = $dados['envia_mail_provas'] == 'true' ? true : false;
        $dados['envia_mail_proxima_etapa'] = $dados['envia_mail_proxima_etapa'] == 'true' ? true : false;
        $dados['envia_mail_desclassificacao'] = $dados['envia_mail_desclassificacao'] == 'true' ? true : false;

        $infCurriculo = $dados['curriculos'];

        try {
            DB::beginTransaction();
            $candidato = Curriculo::find($infCurriculo['id']);
            $dadosCurriculo = [
                'nome' => $infCurriculo['nome'],
                'rg' => $infCurriculo['rg'],
                'orgao_expeditor' => $infCurriculo['orgao_expeditor'],
                'cnh' => $infCurriculo['cnh'],
                'nascimento' => $infCurriculo['nascimento'],
                'filiacao_pai' => $infCurriculo['filiacao_pai'],
                'filiacao_mae' => $infCurriculo['filiacao_mae'],
                'email' => $infCurriculo['email'],
                'cep' => $infCurriculo['cep'],
                'logradouro' => $infCurriculo['logradouro'],
                'bairro' => $infCurriculo['bairro'],
                'municipio' => $infCurriculo['municipio'],
                'uf' => $infCurriculo['uf']
            ];

            if (isset($infCurriculo['telefonesDelete'])) {
                foreach ($infCurriculo['telefonesDelete'] as $index) {
                    TelefoneCurriculo::find($index)->delete();
                }
            }

            if (isset($infCurriculo['telefones'])) {
                foreach ($infCurriculo['telefones'] as $linha) {
                    $linha['principal'] = $linha['principal'] == 'true' ? true : false;
                    if ($linha['id'] == 0) {
                        $telPrincipal = $candidato->Telefones()->create($linha)->id;
                        if ($linha['principal']) {
                            $dados['telefone_id'] = $telPrincipal;
                        }
                    } else {
                        $candidato->Telefones->find($linha['id'])->update($linha);
                        if ($linha['principal']) {
                            $dados['telefone_id'] = $linha['id'];
                        }
                    }
                }
            }

            $candidato->update($dadosCurriculo);

            $dados['data_envia_mail_desclassificacao'] = null;
            $dados['user_envia_mail_desclassificacao'] = null;

            $dados['data_envia_mail_proxima_etapa'] = null;
            $dados['user_envia_mail_proxima_etapa'] = null;

            $dados['data_envia_mail_provas'] = null;
            $dados['user_envia_mail_provas'] = null;

            $dados['data_envia_whatsapp'] = null;
            $dados['user_envia_whatsapp'] = null;

            if ($dados['contato_realizado']) {
                $dados['envia_whatsapp'] = $dados['envia_whatsapp'] == 'true' ? true : false;
                $dados['data_envia_whatsapp'] = $dados['envia_whatsapp'] ? (new DataHora())->dataHoraInsert() : null;
                $dados['user_envia_whatsapp'] = $dados['envia_whatsapp'] ? auth()->id() : null;
            } else {
                $dados['envia_whatsapp'] = null;
                $dados['data_envia_whatsapp'] = null;
                $dados['user_envia_whatsapp'] = null;
            }

            if (is_null($curriculo->FeedBack)) {

                if ($dados['selecionado'] == 'nao') {
                    if ($dados['envia_mail_desclassificacao']) {
                        $dados['data_envia_mail_desclassificacao'] = (new DataHora())->dataHoraInsert();
                        $dados['user_envia_mail_desclassificacao'] = auth()->id();
                        \Mail::send(new DesclassificacaoMail([
                            'nome' => $infCurriculo['nome'],
                            'email' => $infCurriculo['email']
                        ]));
                    }
                    $curriculo->FeedBack()->create($dados);
                } else {
                    if ($dados['selecionado'] == 'sim') {
                        if ($dados['envia_mail_proxima_etapa']) {
                            $dados['data_envia_mail_proxima_etapa'] = (new DataHora())->dataHoraInsert();
                            $dados['user_envia_mail_proxima_etapa'] = auth()->id();
                            \Mail::send(new ProximaEtapaMail([
                                'nome' => $infCurriculo['nome'],
                                'email' => $infCurriculo['email']
                            ]));
                        }
                    }
                    if ($dados['selecionado'] == 'sim' && $dados['tem_provas']) {
                        if ($dados['envia_mail_provas']) {
                            $dados['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
                            $dados['user_envia_mail_provas'] = auth()->id();
                            $provas = SimuladoVaga::whereVagaId($dados['vaga_id'])->whereOnline(true)->get();
                            if ($dados['contato_realizado'] && $dados['envia_whatsapp']) {
                                $qntProvas = count($provas);
                                $mensagem = "";
                                if ($qntProvas > 1) {
                                    $mensagem .= "Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!
                                Você está recebendo um convite para realizar  as avaliações abaixo relacionadas ao seu processo seletivo para a vaga de *{$dados['autocomplete_label_vaga_modal']}* através da empresa BPSE.
                                Uma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.


                                ";
                                } else {
                                    $mensagem .= "Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!
                                Você está recebendo um convite para realizar a avaliação abaixo relacionada ao seu processo seletivo para a vaga de *{$dados['autocomplete_label_vaga_modal']}* através da empresa BPSE.
                                Uma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.


                                ";
                                }

                                foreach ($provas as $prova) {
                                    $mensagem .= route('provas.prova.simulado', [$prova->vaga_id, $prova->simulado_id, $prova->Simulado->slug]);
                                }

                                $mensagem .= "

                                Cuidado para não perder o prazo! Esperamos te ver em breve!

                                *Equipe RH BPSE* ";

                                NotificacaoWhats::sendNotificacaoAptoAdmissao([
                                    'fone' => TelefoneCurriculo::find($dados['telefone_id'])->sonumero,
                                    'curriculo_id' => $infCurriculo['id'],
                                    'vaga_id' => $dados['vaga_id'],
                                    'etapa_id' => 5,
                                ], $mensagem);

                            }
                            \Mail::send(new ProvaMail([
                                'nome' => $infCurriculo['nome'],
                                'email' => $infCurriculo['email'],
                                'vaga' => $dados['autocomplete_label_vaga_modal'],
                                'vaga_id' => $dados['vaga_id'],
                                'provas' => $provas
                            ]));
                        }
//                        else{
//                            $dados['envia_mail_provas'] = false;
//                            $dados['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
//                            $dados['user_envia_mail_provas'] = auth()->id();
//                        }
                    }
                    $dados['vagas_abertas_id'] = $dados['vaga_id'];
                    $curriculo->FeedBack()->create($dados);
                    DB::commit();
                }
            } else {
                $curriculo->FeedBack->update($dados);
                if ($dados['selecionado'] == 'nao') {
                    if ($dados['envia_mail_desclassificacao']) {
                        $dados['data_envia_mail_desclassificacao'] = (new DataHora())->dataHoraInsert();
                        $dados['user_envia_mail_desclassificacao'] = auth()->id();
                        \Mail::send(new DesclassificacaoMail([
                            'nome' => $infCurriculo['nome'],
                            'email' => $infCurriculo['email']
                        ]));
                    }
                } else {
                    if ($dados['selecionado'] == 'sim') {
                        if ($dados['envia_mail_proxima_etapa']) {
                            $dados['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
                            $dados['user_envia_mail_provas'] = auth()->id();
                            \Mail::send(new ProximaEtapaMail([
                                'nome' => $infCurriculo['nome'],
                                'email' => $infCurriculo['email']
                            ]));
                        }
                    }
                    if ($dados['selecionado'] == 'sim' && $dados['tem_provas']) {
                        if ($dados['envia_mail_provas']) {
                            $dados['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
                            $dados['user_envia_mail_provas'] = auth()->id();
                            $provas = SimuladoVaga::whereVagaId($dados['vaga_id'])->whereOnline(true)->get();
                            if ($dados['contato_realizado'] && $dados['envia_whatsapp']) {
                                $qntProvas = count($provas);
                                $mensagem = "";
                                if ($qntProvas > 1) {
                                    $mensagem .= "Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!
                                Você está recebendo um convite para realizar  as avaliações abaixo relacionadas ao seu processo seletivo para a vaga de *{$dados['autocomplete_label_vaga_modal']}* através da empresa BPSE.
                                Uma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.


                                ";
                                } else {
                                    $mensagem .= "Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!
                                Você está recebendo um convite para realizar a avaliação abaixo relacionada ao seu processo seletivo para a vaga de *{$dados['autocomplete_label_vaga_modal']}* através da empresa BPSE.
                                Uma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.


                                ";
                                }

                                foreach ($provas as $prova) {
                                    $mensagem .= route('provas.prova.simulado', [$prova->vaga_id, $prova->simulado_id, $prova->Simulado->slug]);
                                }

                                $mensagem .= "

                                Cuidado para não perder o prazo! Esperamos te ver em breve!

                                *Equipe RH BPSE* ";

                                NotificacaoWhats::sendNotificacaoAptoAdmissao([
                                    'fone' => TelefoneCurriculo::find($dados['telefone_id'])->sonumero,
                                    'curriculo_id' => $infCurriculo['id'],
                                    'vaga_id' => $dados['vaga_id'],
                                    'etapa_id' => 5,
                                ], $mensagem);

                            }
                            \Mail::send(new ProvaMail([
                                'nome' => $infCurriculo['nome'],
                                'email' => $infCurriculo['email'],
                                'vaga' => $dados['autocomplete_label_vaga_modal'],
                                'vaga_id' => $dados['vaga_id'],
                                'provas' => $provas
                            ]));
                        }
                    }
                }
            }
            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            $msg = "error FEEDBACK:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            DB::rollback();
            return response()->json(['msg' => $msg], 400);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Curriculo $recrutamento)
    {
        $this->authorize('curriculos_delete');
        $recrutamento->delete();

    }

    public function atualizar(Request $request)
    {
        $resultado = Curriculo::select(
            'id',
            'cpf',
            'rg',
            'orgao_expeditor',
            'nome',
            'nascimento',
            'logradouro',
            'complemento',
            'bairro',
            'municipio',
            'email',
            'vaga_pretendida',
            'pcd',
            'uf_vaga',
            'municipio_id',
            'lido',
            'created_at',
        )->with('VagaAberta.VagaSelecionada','FeedBack.parecerRh')->doesntHave('FeedBack.parecerRh');


//        $this->authorize('clientes');
//        $resultado = Curriculo::select(
//            'id',
//            'cpf',
//            'rg',
//            'orgao_expeditor',
//            'nome',
//            'nascimento',
//            'logradouro',
//            'complemento',
//            'bairro',
//            'municipio',
//            'email',
//            'vaga_pretendida',
//            'pcd',
//            'uf_vaga',
//            'municipio_id',
//            'lido',
//            'created_at',
//        )->with('Vaga', 'FeedBack')->doesntHave('parecerRh');

//        Curriculo::wherePcd(null)->update(['pcd' => false]);

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
//            $resultado->whereBetween('created_at', [$dataInicio->dataInsert(), $dataFim->dataInsert()]);

            $resultado->where('updated_at', '>=', $dataInicio->dataInsert())->where('updated_at', '<=', $dataFim->dataInsert());
        }

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%');
        }
        if ($request->filled('campoCPF')) {
            $resultado->where('cpf', 'like', '%' . $request->campoCPF . '%');
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoLido')) {
            $campoLido = $request->campoLido == 'true' ? true : false;
            $resultado->whereLido($campoLido);
        }

        if ($request->filled('campoUf')) {
            $resultado->whereUfVaga($request->campoUf);
        }

        if ($request->filled('campoPcd')) {
            $campoPcd = $request->campoPcd == 'true' ? true : false;
            $resultado->wherePcd($campoPcd);
        }

        $resultado = $resultado->orderBy('created_at', 'desc')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items()
        ]);
    }

    public function marcaLido(Curriculo $curriculo)
    {

        if (!$curriculo->lido) {
            $curriculo->lido = !$curriculo->lido;
            $curriculo->usuario_lido = auth()->id();
            $curriculo->datalido = (new DataHora())->dataHoraInsert();
            $curriculo->save();
            $curriculo->refresh();
            return response()->json(['lido' => $curriculo->lido], 201);
        }
    }

    public function export()
    {
        return Excel::download(new CurriculoExport, 'curriculos.xlsx');
    }
}
