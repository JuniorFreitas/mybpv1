<?php

namespace App\Http\Controllers;

use App\Exports\Entrevistas\entrevistaRhExport;
use App\Exports\Entrevistas\parecerRhExport;
use App\Models\Curriculo;
use App\Models\EntrevistaRh;
use App\Models\FeedbackCurriculo;
use App\Models\ParecerRh;
use App\Models\SimuladoVaga;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use MasterTag\DataHora;
use PDF;

class EntrevistaRhClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.entrevistas.entrevista_rh.index');
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
//        $this->authorize('parecer_rh_insert');
        $dados = $request->input();


//        if ($dadosRh['tipo_entrevista'] == 'Fixo') {
//            $dadosValidados = \Validator::make($dados, [
//                'dados.parecer_rh.destro' => 'required',
//                'dados.parecer_rh.rota_bairro' => 'required',
//                'dados.parecer_rh.mora_com_quem' => 'required',
//                'dados.parecer_rh.grau_instrucao' => 'required',
//                'dados.parecer_rh.situacao_saude' => 'required',
//                'dados.parecer_rh.comportamento_seguro' => 'required',
//                'dados.parecer_rh.energia_para_trabalho' => 'required',
//                'dados.parecer_rh.postura' => 'required',
//
//            ]);
//        }

        $dadosValidados = \Validator::make($dados, [
//            'curriculo.municipio_id' => 'required',
//            'cliente_id' => 'required',
//            'vaga_id' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao confirmar Parecer',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $feedback = FeedbackCurriculo::find($dados['id']);
                $dados['parecer_rh']['entrevista_rh']['curriculo_id'] = $feedback->curriculo_id;
                $dados['parecer_rh']['entrevista_rh']['feedback_id'] = $feedback->id;
                $feedback->parecerRh->entrevistaRh()->create($dados['parecer_rh']['entrevista_rh']);
                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error PARECER RH STORE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}| Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ParecerRh $parecerRh
     * @return \Illuminate\Http\Response
     */
    public function show(ParecerRh $parecerRh)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ParecerRh $parecerRh
     * @return \Illuminate\Http\Response
     */
    public function edit(FeedbackCurriculo $parecerRh)
    {
        $feedback = $parecerRh; //FeedbackCurriculo

        $feedback->load('parecerRh.individualRh',
            'parecerRh.gestorRh',
            'parecerRh.entrevistaRh',
            'CertificadosNr',
            'CursosFormacoes',
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
            'Curriculo.Formacao',
            'TelPrincipal',
            'VagaAberta.VagaSelecionada',
            'Cliente:id,razao_social,cnpj,nome,cpf,area_id',
            'Cliente.Area'
        )->load(['Simulados' => function ($query) {
                $query->with('SimuladoVaga.Simulado');
            }]);

        $feedback->Curriculo->autocomplete_label_municipio_modal = $feedback->Curriculo->Cidade ? $feedback->Curriculo->Cidade->nome . ' - ' . $feedback->Curriculo->Cidade->uf : '';
        $feedback->Curriculo->autocomplete_label_municipio_modal_anterior = $feedback->Curriculo->Cidade ? $feedback->Curriculo->Cidade->nome . ' - ' . $feedback->Curriculo->Cidade->uf : '';

        $feedback->autocomplete_label_vaga_modal = $feedback->VagaAberta->vagaSelecionada ? $feedback->VagaAberta->vagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->uf   : '';
        $feedback->autocomplete_label_vaga_modal_anterior = $feedback->VagaAberta->vagaSelecionada ? $feedback->VagaAberta->vagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->uf   : '';

        $feedback->autocomplete_label_cliente_modal = $feedback->Cliente ? $feedback->Cliente->razao_social . ' | ' . $feedback->Cliente->cnpj : '';
        $feedback->autocomplete_label_cliente_modal_anterior = $feedback->Cliente ? $feedback->Cliente->razao_social . ' | ' . $feedback->Cliente->cnpj : '';

        $simulados = SimuladoVaga::whereVagaId($feedback->vaga_id)
            ->whereHas('Simulado', function ($q) {
                $q->whereAtivo(true);
            })->count();

        return response()->json(['feedback' => $feedback, 'provas' => $simulados], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ParecerRh $parecerRh
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EntrevistaRh $parecerRh)
    {
//        $this->authorize('parecer_rh_update');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
//            'curriculo.municipio_id' => 'required',
//            'cliente_id' => 'required',
//            'vaga_id' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar a Entrevista',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $parecerRh->update($dados);
                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error PARECER RH UPDATE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}| Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ParecerRh $parecerRh
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParecerRh $parecerRh)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $resultado = FeedbackCurriculo::whereInteresse(true)
            ->whereIn('selecionado', ['sim', 'standby'])
            ->with(
                'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
                'Cliente:id,razao_social,area_id',
                'Cliente.Area',
                'VagaAberta.VagaSelecionada',
                'parecerRh:id,feedback_id,nota,created_at',
                'parecerRh.individualRh:feedback_id,nota,parecer',
                'parecerRh.entrevistaRh'
            )
            ->whereHas('parecerRh.individualRh', function ($q) {
                $q->whereIn('parecer', ['destaque', 'favoravel']);
            });

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                $q->where('created_at', '>=', $dataInicio->dataInsert())->where('created_at', '<=', $dataFim->dataInsert());
            });
        }


        if ($request->filled('campoCliente')) {
            $resultado->whereClienteId($request->campoCliente);
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->whereCpf($request->campoBusca);
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->whereUfVaga($request->campoUf);
            });
        }

        //Se for id 35 ou campo cliente preenchido e que seja igual a 35
//        if (auth()->user()->Cliente->area_id > 1 || auth()->user()->cliente_id == 1) {

            $resultado->whereStatus('classificado')
                ->whereIn('selecionado', ['sim', 'standby'])
                ->whereInteresse(true);


            if ($request->filled('campoRh')) {
                if ($request->campoRh == '0') {
                    $resultado->whereHas('parecerRH.entrevistaRh', function ($q) {
                        $q->where('nota', 0);
                    });
                }
                if ($request->campoRh == '1-5') {
                    $resultado->whereHas('parecerRH.individualRh', function ($q) {
                        $q->where('nota', '>=', 1)->where('nota', '<=', 5);
                    });
                }
                if ($request->campoRh == '5-7') {
                    $resultado->whereHas('parecerRH.individualRh', function ($q) {
                        $q->where('nota', '>=', 5)->where('nota', '<=', 7);
                    });
                }
                if ($request->campoRh == '8-10') {
                    $resultado->whereHas('parecerRH.individualRh', function ($q) {
                        $q->where('nota', '>=', 8)->where('nota', '<=', 10);
                    });
                }
            }

            if ($request->filled('parecer_individual')) {
                if ($request->parecer_individual == 'entrevistado') {
                    $resultado->has('parecerRH.individualRh');
                }
                if ($request->parecer_individual == 'nao_entrevistado') {
                    $resultado->whereDoesntHave('parecerRH.individualRh');
                }

                if ($request->parecer_individual != 'entrevistado' && $request->parecer_individual != 'nao_entrevistado') {
                    $resultado->whereHas('parecerRH.individualRh', function ($q) use ($request) {
                        $q->whereParecer($request->parecer_individual);
                    });
                }
            }
//        } else {
            // Se não for 35 (55 solucoes)

            $resultado->with(
                'parecerTecnica:feedback_id,nota',
                'parecerRota:feedback_id,tem_rota',
                'parecerTeste:feedback_id,nota_teste');


            if ($request->filled('campoPcd')) {
                $campoPcd = $request->campoPcd == 'true' ? true : false;
                $resultado->whereHas('Curriculo', function ($query) use ($campoPcd) {
                    $query->wherePcd($campoPcd);
                });
            }

            if ($request->filled('campoRh')) {
                if ($request->campoRh == 'realizado') {
                    $resultado->has('parecerRh');
                } else {
                    $resultado->whereHas('parecerRH', function ($q) use ($request) {
                        $q->whereNota($request->campoRh);
                    });
                }
            }

            if ($request->filled('campoFinalRh')) {
                $resultado->whereHas('parecerRH', function ($q) use ($request) {
                    $q->whereParecerFinalUm($request->campoFinalRh);
                });
            }
//        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items()
            ]
        ]);
    }

    public function export(Request $request)
    {
        $resultado = FeedbackCurriculo::whereInteresse(true)
            ->whereIn('selecionado', ['sim', 'standby'])
            ->has('parecerRh');

        if ($request->selecionados) {
            $resultado->whereIn('id', $request->selecionados);
            $resultado = $resultado->get();
            //Criar um que pega tudo
            if ($request->campoCliente == 35 || auth()->user()->cliente_id === 35) {
                return Excel::download(new entrevistaRhExport($resultado), 'parecer_rh' . (new DataHora())->nomeUnico() . '.xlsx');
            }
            return Excel::download(new parecerRhExport($resultado), 'parecer_rh' . (new DataHora())->nomeUnico() . '.xlsx');
        } else {
            $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;
            if ($filtroPeriodo) {
                $periodo = explode(' até ', $request->periodo);
                $dataInicio = new DataHora($periodo[0], ' 00:00:00');
                $dataFim = new DataHora($periodo[1], ' 23:59:59');
                $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                    $q->where('created_at', '>=', $dataInicio->dataInsert())->where('created_at', '<=', $dataFim->dataInsert());
                });
            }

            if ($request->filled('campoCliente')) {
                $resultado->whereClienteId($request->campoCliente);
            }

            if ($request->filled('campoBusca')) {
                $resultado->whereHas('Curriculo', function ($query) use ($request) {
                    $query->where('nome', 'like', '%' . $request->campoBusca . '%')
                        ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                        ->orWhere('id', $request->campoBusca);
                });
            }

            if ($request->filled('campoCPF')) {
                $resultado->whereHas('Curriculo', function ($query) use ($request) {
                    $query->whereCpf($request->campoBusca);
                });
            }

            if ($request->filled('campoVaga')) {
                $resultado->whereHas('VagaSelecionada', function ($query) use ($request) {
                    $query->whereId($request->campoVaga);
                });
            }

            if ($request->filled('campoUf')) {
                $resultado->whereHas('Curriculo', function ($q) use ($request) {
                    $q->whereUfVaga($request->campoUf);
                });
            }

            //Se for id 35 ou campo cliente preenchido e que seja igual a 35
            if ($request->campoCliente == 35 || auth()->user()->cliente_id === 35) {
                $resultado->whereStatus('classificado')
                    ->whereIn('selecionado', ['sim', 'standby'])
                    ->whereInteresse(true)->has('parecerRh.individualRh');

                if ($request->filled('campoRh')) {
                    if ($request->campoRh == '0') {
                        $resultado->whereHas('parecerRH.entrevistaRh', function ($q) {
                            $q->where('nota', 0);
                        });
                    }
                    if ($request->campoRh == '1-5') {
                        $resultado->whereHas('parecerRH.individualRh', function ($q) {
                            $q->where('nota', '>=', 1)->where('nota', '<=', 5);
                        });
                    }
                    if ($request->campoRh == '5-7') {
                        $resultado->whereHas('parecerRH.individualRh', function ($q) {
                            $q->where('nota', '>=', 5)->where('nota', '<=', 7);
                        });
                    }
                    if ($request->campoRh == '8-10') {
                        $resultado->whereHas('parecerRH.individualRh', function ($q) {
                            $q->where('nota', '>=', 8)->where('nota', '<=', 10);
                        });
                    }
                }

                if ($request->filled('parecer_individual')) {
                    if ($request->parecer_individual == 'entrevistado') {
                        $resultado->has('parecerRH.individualRh');
                    }
                    if ($request->parecer_individual == 'nao_entrevistado') {
                        $resultado->whereDoesntHave('parecerRH.individualRh');
                    }

                    if ($request->parecer_individual != 'entrevistado' && $request->parecer_individual != 'nao_entrevistado') {
                        $resultado->whereHas('parecerRH.individualRh', function ($q) use ($request) {
                            $q->whereParecer($request->parecer_individual);
                        });
                    }
                }

                $resultado = $resultado->get();
                return Excel::download(new entrevistaRhExport($resultado), 'parecer_rh' . (new DataHora())->nomeUnico() . '.xlsx');
            } else {
                $resultado->with(
                    'parecerTecnica:feedback_id,nota',
                    'parecerRota:feedback_id,tem_rota',
                    'parecerTeste:feedback_id,nota_teste');

                if ($request->filled('campoRota')) {
                    $campoRota = $request->campoRota == 'true' ? true : false;
                    if ($request->campoRota == 'realizado') {
                        $resultado->has('parecerRota');
                    } else {
                        $resultado->whereHas('parecerRota', function ($q) use ($campoRota) {
                            $q->whereTemRota($campoRota);
                        });
                    }
                }

                if ($request->filled('campoTeste')) {
                    if ($request->campoRota == 'realizado') {
                        $resultado->has('parecerTeste');
                    } else {
                        $resultado->whereHas('parecerTeste', function ($q) use ($request) {
                            $q->whereNotaTeste($request->campoTeste);
                        });
                    }
                }

                if ($request->filled('campoTecnica')) {
                    $resultado->has('parecerTecnica');
                }

                if ($request->filled('campoPcd')) {
                    $campoPcd = $request->campoPcd == 'true' ? true : false;
                    $resultado->whereHas('Curriculo', function ($query) use ($campoPcd) {
                        $query->wherePcd($campoPcd);
                    });
                }

                if ($request->filled('campoRh')) {
                    if ($request->campoRh == 'realizado') {
                        $resultado->has('parecerRh');
                    } else {
                        $resultado->whereHas('parecerRH', function ($q) use ($request) {
                            $q->whereNota($request->campoRh);
                        });
                    }
                }

                if ($request->filled('campoFinalRh')) {
                    $resultado->whereHas('parecerRH', function ($q) use ($request) {
                        $q->whereParecerFinalUm($request->campoFinalRh);
                    });
                }

                $resultado = $resultado->get();
                return Excel::download(new parecerRhExport($resultado), 'parecer_rh' . (new DataHora())->nomeUnico() . '.xlsx');
            }
        }

    }

    public function curriculoPdf(Curriculo $recrutamento)
    {

        $pdf = PDF::loadView('pdf.recrutamento.curriculo', compact('recrutamento'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("curriculo" . ST($recrutamento->nome) . ".pdf");

        return view('pdf.recrutamento.curriculo', compact('recrutamento'));
    }
}
