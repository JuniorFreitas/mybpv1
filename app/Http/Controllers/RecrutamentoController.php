<?php

namespace App\Http\Controllers;

use App\Jobs\JobEnviaZap;
use App\Jobs\JobExportaExcel;
use App\Jobs\Recrutamento\JobDesclassificacao;
use App\Jobs\Recrutamento\JobProva;
use App\Jobs\Recrutamento\JobProximaEtapa;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\Curriculo;
use App\Models\SimuladoVaga;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\VagasAbertas;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
    public function show($curriculo)
    {
        $recrutamento = Curriculo::where('id', \Crypt::decrypt($curriculo))->first();
        $pdf = PDF::loadView('pdf.recrutamento.curriculo', compact('recrutamento'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("curriculo" . Str::slug($recrutamento->nome) . ".pdf");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Curriculo
     */
    public function edit(Curriculo $recrutamento)
    {

//        $value = cache()->rememberForever("curriculo_{$recrutamento->id}", function () use($recrutamento) {
        $recrutamento->estado_civil = $recrutamento->estado_civil ?? '';
        $recrutamento->sexo = $recrutamento->sexo ?? '';
        return $recrutamento->load('Atualizacao', 'Qualificacoes', 'Experiencias', 'VagaAberta.VagaSelecionada', 'Formacao', 'Telefones', 'Usuario')->load(['FeedBack' => function ($query) {
            $query->with('VagaAberta.VagaSelecionada.SimuladoVaga', 'VagaAberta.Municipio', 'Cliente', 'QuemMarcou', 'TelPrincipal');
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
     * @param Request $request
     * @param Curriculo $recrutamento
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, Curriculo $recrutamento)
    {
        $curriculo = $recrutamento;
        $dados = $request->input();

        $dados['cliente_id'] = auth()->user()->empresa_id;
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
            $empresa = Cliente::find(auth()->user()->empresa_id);
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
                'uf' => $infCurriculo['uf'],
                'sexo' => $infCurriculo['sexo'],
                'estado_civil' => $infCurriculo['estado_civil'],
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

            $permite_envio_whatsapp = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
            $permite_envio_whatsapp = !empty($permite_envio_whatsapp) && $permite_envio_whatsapp->envia_whatsapp;

            if ($dados['contato_realizado'] && $permite_envio_whatsapp) {
                $dados['envia_whatsapp'] = $dados['envia_whatsapp'] == 'true';
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

                        JobDesclassificacao::dispatch([
                            'nome' => $infCurriculo['nome'],
                            'email' => $infCurriculo['email'],
                            'razao_social' => $empresa->razao_social,
                            'empresa_id' => $empresa->id,
                        ]);
                    }

                    $curriculo->FeedBack()->create($dados);
                } else {
                    if ($dados['selecionado'] == 'sim') {
                        if ($dados['envia_mail_proxima_etapa']) {
                            $dados['data_envia_mail_proxima_etapa'] = (new DataHora())->dataHoraInsert();
                            $dados['user_envia_mail_proxima_etapa'] = auth()->id();
                            $vaga_aberta = VagasAbertas::find($dados['vaga_id']);

                            JobProximaEtapa::dispatch(
                                [
                                    'nome' => $infCurriculo['nome'],
                                    'email' => $infCurriculo['email'],
                                    'empresa' => $empresa->razao_social,
                                    'logo' => env('AWS_URL') . "/public/email_" . $empresa->apelido . ".jpg",
                                    'vaga_selecionada' => $vaga_aberta->titulo . ' -' . $vaga_aberta->Municipio->nome . '/' . $vaga_aberta->Municipio->uf,
                                    'local_entrevista' => $dados['local_entrevista'],
                                    'data_entrevista' => $dados['data_entrevista'],
                                ]);
                        }
                        if ($dados['contato_realizado'] && $dados['envia_whatsapp'] && $permite_envio_whatsapp) {
                            $mensagem = "👏🏽👏🏽Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!\nPara a vaga *{$vaga_aberta->titulo} - {$vaga_aberta->Municipio->nome}/{$vaga_aberta->Municipio->uf}* fique atento as próximas etapas do processo!\n\n📆 Data da entrevista: {$dados['data_entrevista']}\n📍Local da entrevista: {$dados['local_entrevista']}\n\nSucesso e esperamos vê-lo em breve. \n\n*☺️ Um forte abraço da equipe " . $empresa->razao_social . "*\n\n_Esta mensagem foi enviada automaticamente pela plataforma *MyBP*, por favor não responda._";

                            $telefonePrincipal = TelefoneCurriculo::whereCurriculoId($curriculo->id)->wherePrincipal(true)->first();
                            if ($telefonePrincipal->tipo == 'whatsapp') {
                                JobEnviaZap::dispatch([
                                    'enviado_id' => $curriculo->id,
                                    'telefone' => $telefonePrincipal->sonumero,
                                    'mensagem' => $mensagem,
                                ]);
                            }
                        }
                    }
                    if ($dados['selecionado'] == 'sim' && $dados['tem_provas']) {
                        if ($dados['envia_mail_provas']) {
                            $dados['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
                            $dados['user_envia_mail_provas'] = auth()->id();
                            $provas = SimuladoVaga::whereVagasAbertasId($dados['vagas_abertas_id'])->whereOnline(true)->get();
                            if ($dados['contato_realizado'] && $dados['envia_whatsapp'] && $permite_envio_whatsapp) {
                                $qntProvas = count($provas);
                                if ($qntProvas > 1) {
                                    $mensagem .= "Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!\nVocê está recebendo um convite para realizar  as avaliações abaixo relacionadas ao seu processo seletivo para a vaga de *{$dados['autocomplete_label_vaga_modal']}* através da plataforma MyBP.\nUma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.\n\n";
                                } else {
                                    $mensagem .= "Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!\nVocê está recebendo um convite para realizar a avaliação abaixo relacionada ao seu processo seletivo para a vaga de *{$dados['autocomplete_label_vaga_modal']}* através da plataforma MyBP.\nUma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.\n\n";
                                }
                                foreach ($provas as $prova) {
                                    $mensagem .= route('provas.prova.simulado', [$prova->vaga_id, $prova->simulado_id, $prova->Simulado->slug]) . "\n";
                                }
                                $mensagem .= "\n\nCuidado para não perder o prazo! Esperamos te ver em breve!\n\n*Equipe RH BPSE* ";

                                if ($telefonePrincipal->tipo == 'whatsapp') {
                                    JobEnviaZap::dispatch([
                                        'enviado_id' => $curriculo->id,
                                        'telefone' => $telefonePrincipal->sonumero,
                                        'mensagem' => $mensagem,
                                    ]);
                                }
                            }
                            JobProva::dispatch(
                                [
                                    'nome' => $infCurriculo['nome'],
                                    'email' => $infCurriculo['email'],
                                    'vaga' => $dados['autocomplete_label_vaga_modal'],
                                    'vaga_id' => $dados['vaga_id'],
                                    'provas' => $provas
                                ]
                            );
                        } else {
                            $dados['envia_mail_provas'] = false;
                            $dados['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
                            $dados['user_envia_mail_provas'] = auth()->id();
                        }
                    }

                    $dados['vagas_abertas_id'] = $dados['vaga_id'];
                    $dados['vaga_id'] = VagasAbertas::find($dados['vagas_abertas_id'])->vaga_id;

                    $curriculo->FeedBack()->create($dados);
                }
            } else {
                $dados['vagas_abertas_id'] = $dados['vaga_aberta']['id'];
                $curriculo->FeedBack->update($dados);
                if ($dados['selecionado'] == 'nao') {
                    if ($dados['envia_mail_desclassificacao']) {
                        $dados['data_envia_mail_desclassificacao'] = (new DataHora())->dataHoraInsert();
                        $dados['user_envia_mail_desclassificacao'] = auth()->id();
                        JobDesclassificacao::dispatch([
                            'nome' => $infCurriculo['nome'],
                            'email' => $infCurriculo['email'],
                            'razao_social' => $empresa->razao_social,
                            'empresa_id' => $empresa->id,
                        ]);
                    }
                } else {

                    if ($dados['selecionado'] == 'sim') {
                        if ($dados['envia_mail_proxima_etapa']) {
                            $dados['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
                            $dados['user_envia_mail_provas'] = auth()->id();
                            $vaga_aberta = VagasAbertas::find($dados['vagas_abertas_id']);
                            JobProximaEtapa::dispatch(
                                [
                                    'nome' => $infCurriculo['nome'],
                                    'email' => $infCurriculo['email'],
                                    'empresa' => $empresa->razao_social,
                                    'logo' => env('AWS_URL') . "/public/email_" . $empresa->apelido . ".jpg",
                                    'vaga_selecionada' => $vaga_aberta->titulo . ' -' . $vaga_aberta->Municipio->nome . '/' . $vaga_aberta->Municipio->uf,
                                    'local_entrevista' => $dados['local_entrevista'],
                                    'data_entrevista' => $dados['data_entrevista'],
                                ]
                            );
                        }

                        if ($dados['contato_realizado'] && $dados['envia_whatsapp'] && $permite_envio_whatsapp) {
                            $mensagem = "👏🏽👏🏽Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!\nPara a vaga *{$vaga_aberta->titulo} - {$vaga_aberta->Municipio->nome}/{$vaga_aberta->Municipio->uf}* fique atento as próximas etapas do processo!\n\n📆 Data da entrevista: {$dados['data_entrevista']}\n📍Local da entrevista: {$dados['local_entrevista']}\n\nSucesso e esperamos vê-lo em breve. \n\n*☺️ Um forte abraço da equipe " . $empresa->razao_social . "*\n\n_Esta mensagem foi enviada automaticamente pela plataforma *MyBP*, por favor não responda._";
                            $telefonePrincipal = TelefoneCurriculo::whereCurriculoId($curriculo->id)->wherePrincipal(true)->first();
                            if ($telefonePrincipal->tipo == 'whatsapp') {
                                JobEnviaZap::dispatch([
                                    'enviado_id' => $curriculo->id,
                                    'telefone' => $telefonePrincipal->sonumero,
                                    'mensagem' => $mensagem,
                                ]);
                            }
                        }
                    }

                    if ($dados['selecionado'] == 'sim' && $dados['tem_provas']) {
                        if ($dados['envia_mail_provas']) {
                            $dados['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
                            $dados['user_envia_mail_provas'] = auth()->id();
                            $provas = SimuladoVaga::whereVagasAbertasId($dados['vagas_abertas_id'])->whereOnline(true)->get();
                            if ($dados['contato_realizado'] && $dados['envia_whatsapp'] && $permite_envio_whatsapp) {
                                $qntProvas = count($provas);
                                if ($qntProvas > 1) {
                                    $mensagem .= "Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!\nVocê está recebendo um convite para realizar  as avaliações abaixo relacionadas ao seu processo seletivo para a vaga de *{$dados['autocomplete_label_vaga_modal']}* através da plataforma MyBP.\nUma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.\n\n";
                                } else {
                                    $mensagem .= "Parabéns, *{$infCurriculo['nome']}*. Você foi *selecionado(a)*!\nVocê está recebendo um convite para realizar a avaliação abaixo relacionada ao seu processo seletivo para a vaga de *{$dados['autocomplete_label_vaga_modal']}* através da plataforma MyBP.\nUma vez iniciado o teste não existe a possibilidade de pausar, portanto se prepare e reserve um tempo para preenchê-los.\n\n";
                                }
                                foreach ($provas as $prova) {
                                    $mensagem .= route('provas.prova.simulado', [$prova->vaga_id, $prova->simulado_id, $prova->Simulado->slug]) . "\n";
                                }
                                $mensagem .= "\n\nCuidado para não perder o prazo! Esperamos te ver em breve!\n\n*Equipe RH BPSE* ";

                                if ($telefonePrincipal->tipo == 'whatsapp') {
                                    JobEnviaZap::dispatch([
                                        'enviado_id' => $curriculo->id,
                                        'telefone' => $telefonePrincipal->sonumero,
                                        'mensagem' => $mensagem,
                                    ]);
                                }
                            }
                            JobProva::dispatch([
                                'nome' => $infCurriculo['nome'],
                                'email' => $infCurriculo['email'],
                                'vaga' => $dados['autocomplete_label_vaga_modal'],
                                'vaga_id' => $dados['vaga_id'],
                                'provas' => $provas
                            ]);
                        }
                    }
                }
            }
            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error FEEDBACK:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
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

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate(500);

        $permite_envio_whatsapp = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();

        $permite_envio_whatsapp = !empty($permite_envio_whatsapp) ? $permite_envio_whatsapp->envia_whatsapp : false;

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => collect($resultado->items())->transform(function ($item) {
                    $item->ctoken = \Crypt::encrypt($item->id);
                    return $item;
                }),
                'permite_envio_whatsapp' => $permite_envio_whatsapp,
                'lista_sexos' => Curriculo::TIPOS_SEXOS,
                'lista_estados_civis' => Curriculo::ESTADOS_CIVIS,
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = Curriculo::select(
            ['id',
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
                'sexo',
                'estado_civil',
                'lido',
                'created_at']
        )->with('VagaAberta.VagaSelecionada')
            ->doesntHave('FeedBack.parecerRh');


        $filtroPeriodo = $request->filtroPeriodo == 'true';
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo[1] . ' 23:59:59');

            $resultado->where('updated_at', '>=', $dataInicio->dataHoraInsert())
                ->where('updated_at', '<=', $dataFim->dataHoraInsert());
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

        return $resultado->orderByDesc('created_at');

    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->select(
            ['id',
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
                'created_at']
        )->get();

        $head = [
            "Nome",
            "CPF",
            "Nascimento",
            "PCD",
            "CNH",
            "E-mail",
            "Endereço",
            "Vaga",
            "Telefones",
            "Data Cadastro",
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $telefones = "";
            foreach ($row->Telefones as $tel) {
                $telefones .= $tel->numero . " ($tel->tipoText) | ";
            }
            $rows[] = [
                $row->nome,
                $row->cpf,
                $row->nascimento,
                !$row->pcd ? "Não" : "Sim - {$row->cid}",
                $row->cnh ?: "",
                mb_strtolower($row->email),
                $row->endereco_completo,
                $row->VagaAberta->VagaSelecionada->nome . " - " . $row->VagaAberta->Municipio->nome . " - " . $row->VagaAberta->Municipio->uf,
                substr($telefones, 0, -3),
                (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
            ];
        }

        $nameArquivo = "recrutamento" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Recrutamento", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
}
