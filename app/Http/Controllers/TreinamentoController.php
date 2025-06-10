<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaExcel;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\CentroCusto;
use App\Models\ClienteConfig;
use App\Models\FeedbackCurriculo;
use App\Models\Pivot\TreinamentoVencimento;
use App\Models\ResultadoIntegrado;
use App\Models\Treinamento;
use App\Models\Vencimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

                if (isset($lista['arquivoDel'])) {
                    foreach ($lista['arquivoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->update([
                            'temporario' => true,
                            'chave' => \Str::uuid(),
                        ]);
                    }
                }

                if (isset($lista['arquivo'][0]) && $lista['arquivo'][0]['temporario'] && strlen($lista['arquivo'][0]['chave']) > 0 && !$lista['arquivo'][0]['falhou']) {
                    $arquivo = Arquivo::find($lista['arquivo'][0]['id']);
                    if ($arquivo) {
                        $arquivo->update([
                            'chave' => '',
                            'nome' => $lista['arquivo'][0]['nome'],
                            'temporario' => false,
                        ]);
                    }
                }

                $treinamento->Vencimentos()->attach($lista['id'], [
                    'data_treinamento' => $dataTreinamento,
                    'data_vencimento' => (new DataHora($dataVencimento))->dataInsert(),
                    'numero_fat' => $lista['numero_fat'],
                    'arquivo_id' => isset($lista['arquivo'][0]) ? $lista['arquivo'][0]['id'] : null
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($treinamento)
    {
        $treinamento = ResultadoIntegrado::whereFeedbackId($treinamento)->first();

        $treinamento = $treinamento->load(
            'Treinamento',
            'Treinamento.Vencimentos',
            'Treinamento.QuemCadastrou:id,nome',
            'Curriculo:id,nome,nascimento,id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
            'Admissao',
            'Feedback.Exame',
            'Feedback.Curriculo:id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
        );

        if (!is_null($treinamento->Admissao)) {
            $treinamento->nr_trinta_tres = $treinamento->Admissao->nr_trinta_tres == 'NÃO SE APLICA' ? false : true;
            $treinamento->nr_trinta_cinco = $treinamento->Admissao->nr_trinta_cinco == 'NÃO SE APLICA' ? false : true;
        } else {
            $treinamento->nr_trinta_tres = true;
            $treinamento->nr_trinta_cinco = true;
        }

        // Obter todos os relacionamentos treinamento_vencimento com seus arquivos
        $treinamentoVencimentos = null;
        if ($treinamento->Treinamento) {
            $treinamentoVencimentos = TreinamentoVencimento::with('arquivo')
                ->where('treinamento_id', $treinamento->Treinamento->id)
                ->get();

            // Adicionar os arquivos aos vencimentos já carregados
            foreach ($treinamento->Treinamento->Vencimentos as $vencimento) {
                $pivotData = $treinamentoVencimentos->where('vencimento_id', $vencimento->id)->first();
                $vencimento->arquivo = [];
                $vencimento->arquivoDel = [];
                if ($pivotData) {
                    $vencimento->arquivo = [$pivotData->arquivo];
                }
            }
        }

        $treinamento->listaVencimentos = Vencimento::whereAtivo(true)
            ->orderBy('ordem')
            ->get()
            ->transform(function ($item) use ($treinamento, $treinamentoVencimentos) {
                if ($treinamento->Treinamento) {
                    $pivo = $treinamento->Treinamento->Vencimentos()->whereId($item->id);
                    $item->data_treinamento = $pivo->count() > 0 ? $pivo->first()->pivot->data_treinamento : null;
                    $item->data_vencimento = $pivo->count() > 0 ? $pivo->first()->pivot->data_vencimento : null;
                    $item->numero_fat = $pivo->count() > 0 ? $pivo->first()->pivot->numero_fat : null;
                    $item->fez_treinamento = $pivo->count() > 0 ? true : false;
                    $item->arquivo = [];
                    $item->arquivoDel = [];

                    // Adicionar cálculo de dias_vencer para cada item
                    if ($item->data_vencimento) {
                        $item->dias_vencer = DataHora::diferencaDias(
                            (new DataHora())->dataInsert() . ' 00:00:00',
                            (new DataHora($item->data_vencimento))->dataInsert() . ' 23:59:59'
                        );
                    } else {
                        $item->dias_vencer = PHP_INT_MAX; // Valor alto para itens sem data de vencimento
                    }

                    // Resto do código para arquivos
                    if ($treinamentoVencimentos) {
                        $pivotData = $treinamentoVencimentos->where('vencimento_id', $item->id)->first();
                        if ($pivotData) {
                            $item->arquivo = $pivotData->arquivo ? [$pivotData->arquivo] : [];
                        }
                    }
                } else {
                    $item->data_treinamento = null;
                    $item->data_vencimento = null;
                    $item->fez_treinamento = false;
                    $item->numero_fat = null;
                    $item->arquivo = [];
                    $item->arquivoDel = [];
                    $item->dias_vencer = PHP_INT_MAX; // Valor alto para itens sem treinamento
                }

                return $item;
            })
            ->sortBy('dias_vencer') // Ordenar pelo dias_vencer do menor para o maior
            ->values(); // Reindexar a coleção para garantir índices sequenciais

        /*  $treinamento->listaVencimentos = Vencimento::whereAtivo(true)->orderBy('ordem')->get()->transform(function ($item) use ($treinamento, $treinamentoVencimentos) {
              if ($treinamento->Treinamento) {
                  $pivo = $treinamento->Treinamento->Vencimentos()->whereId($item->id);
                  $item->data_treinamento = $pivo->count() > 0 ? $pivo->first()->pivot->data_treinamento : null;
                  $item->data_vencimento = $pivo->count() > 0 ? $pivo->first()->pivot->data_vencimento : null;
                  $item->numero_fat = $pivo->count() > 0 ? $pivo->first()->pivot->numero_fat : null;
                  $item->fez_treinamento = $pivo->count() > 0 ? true : false;
                  $item->arquivo = [];
                  $item->arquivoDel = [];


  //                $item->Treinamento->Vencimentos->transform(function ($i) use ($item) {
  //                    $i->fez_treinamento = $item->Treinamento->Vencimentos()->where('vencimento_id', $i->id)->count() > 0 ? true : false;
  //                    $i->pivot->status = $this->StatusTreinamento($i->pivot->data_vencimento);
  //                    $i->dias_vencer = DataHora::diferencaDias((new DataHora())->dataInsert() . ' 00:00:00', (new DataHora($i->pivot->data_vencimento))->dataInsert() . ' 23:59:59');
  //                    return $i;
  //                });
  //
  //                $item->treinamento->vencimentos = $item->Treinamento->Vencimentos->sortBy('dias_vencer')->values()->all();
                  // Adicionar informações do arquivo
                  if ($treinamentoVencimentos) {
                      $pivotData = $treinamentoVencimentos->where('vencimento_id', $item->id)->first();
                      if ($pivotData) {
                          $item->arquivo = $pivotData->arquivo ? [$pivotData->arquivo] : [];
                      }
                  }
              } else {
                  $item->data_treinamento = null;
                  $item->data_vencimento = null;
                  $item->fez_treinamento = false;
                  $item->numero_fat = null;
                  $item->arquivo = [];
                  $item->arquivoDel = [];
              }

              return $item;
          });*/

//        <div class="row">
//                        <div class="col-12">
//                            <p>
//    Nome: <strong>@{{ dados.nome }}</strong> - @{{ dados.idade }} anos <br>
//                                Cargo: <strong>@{{ dados.cargo }}</strong> <br>
//    Endereço: <strong>@{{ dados.endereco_completo }}</strong><br>
//    Contato: <strong><i class="fab fa-whatsapp text-success"
//                                                    v-show="dados.tel_principal.tipo === 'whatsapp'"></i> @{{
//        dados.tel_principal.numero }}</strong> - E-mail: <strong>@{{dados.email}}</strong>
//                                <br>
//                            </p>
//                        </div>
//                    </div>

        $treinamento->dadosFuncionario = [
            'nome' => $treinamento->Feedback->Curriculo->nome,
            'cargo' => $treinamento->Admissao ? $treinamento->Admissao->cargo : null,
            'endereco_completo' => $treinamento->Feedback->Curriculo->endereco_completo,
            'tel_principal' => $treinamento->Feedback->telefonePrincipal,
            'email' => $treinamento->Feedback->Curriculo->email,
            'idade' => $treinamento->Feedback->Curriculo->idade
        ];

        return response()->json($treinamento);
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
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);

        $itens = collect($resultado->items());
        $vencimentos = Vencimento::whereAtivo(true)->orderBy('ordem')->get();

        $vencimentos->transform(function ($i) {
            $i->fez_treinamento = false;
            return $i;
        });

        $itens->transform(function ($item) use ($cc) {
            if ($item->Treinamento) {
                $item->nr_33 = $item->Treinamento->Vencimentos()->where(function ($q) {
                    $q->where('label', 'like', '%NR33%')->orWhere('label', 'like', '%NR-33%');
                })->count() > 0 ? $item->Treinamento->Vencimentos()->where(function ($q) {
                    $q->where('label', 'like', '%NR33%')->orWhere('label', 'like', '%NR-33%');
                })->first()->pivot : null;
                $item->nr_35 = $item->Treinamento->Vencimentos()->where(function ($q) {
                    $q->where('label', 'like', '%NR35%')->orWhere('label', 'like', '%NR-35%');
                })->count() > 0 ? $item->Treinamento->Vencimentos()->where(function ($q) {
                    $q->where('label', 'like', '%NR35%')->orWhere('label', 'like', '%NR-35%');
                })->first()->pivot : null;
                $item->ebtv = $item->Treinamento->Vencimentos()->where('label', 'like', '%EBTV%')->count() > 0 ? $item->Treinamento->Vencimentos()->where('label', 'like', '%EBTV%')->first()->pivot : null;

                $item->Treinamento->Vencimentos->transform(function ($i) use ($item) {
                    $i->fez_treinamento = $item->Treinamento->Vencimentos()->where('vencimento_id', $i->id)->count() > 0 ? true : false;
                    $i->pivot->status = $this->StatusTreinamento($i->pivot->data_vencimento);
                    $i->dias_vencer = DataHora::diferencaDias((new DataHora())->dataInsert() . ' 00:00:00', (new DataHora($i->pivot->data_vencimento))->dataInsert() . ' 23:59:59');
                    return $i;
                });

                $item->treinamento->vencimentos = $item->Treinamento->Vencimentos->sortBy('dias_vencer')->values()->all();


            } else {
                $item->nr_33 = null;
                $item->nr_35 = null;
                $item->ebtv = null;
            }

            if ($item->admissao) {
                $cc_colaborador = collect($cc['centros_custos'])->collapse()->where('id', $item->admissao->centro_custo_id)->first();
                $item->admissao->emp_cnpj = null;
                $item->admissao->emp_nome_fantasia = null;
                $item->admissao->emp_centro_custo = null;
                $item->admissao->emp_tipo = null;

                if ($cc_colaborador) {
                    $item->admissao->emp_cnpj = $cc_colaborador['cnpj_format'];
                    $item->admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'];
                    $item->admissao->emp_centro_custo = $cc_colaborador['label'];
                    $item->admissao->emp_tipo = $cc_colaborador['matriz'] ? 'Matriz' : 'Filial';
                }
            }

            return $item;
        });


        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'vencimentos' => $vencimentos,
                'cc' => $cc
            ]
        ]);
    }

    private function StatusTreinamento($dataVencimento)
    {
        $dataVencimento = new DataHora($dataVencimento . ' 23:59:59');
        $hoje = new DataHora();

        $diffHojeVencimento = DataHora::diferencaDias($hoje->dataHoraInsert(), $dataVencimento->dataHoraInsert());

        $status = [
            'label' => 'Em dia',
            'corBorder' => 'border-left-success',
            'bg' => 'bg-success',
            'text' => 'text-white',
            'badge' => 'badge-success'
        ];

        if ($diffHojeVencimento < 0) {
            $status = [
                'label' => 'Vencido a ' . abs($diffHojeVencimento) . ' dia(s)',
                'corBorder' => 'border-left-danger',
                'bg' => 'bg-danger',
                'text' => 'text-white',
                'badge' => 'badge-danger'
            ];
        }
        if ($diffHojeVencimento <= 30 && $diffHojeVencimento > 0) {
            $status = [
                'label' => 'Vence em ' . abs($diffHojeVencimento) . ' dia(s)',
                'corBorder' => 'border-left-warning',
                'bg' => 'bg-warning',
                'text' => 'text-white',
                'badge' => 'badge-warning text-dark'
            ];
        }

        return $status;
    }

    public function filtro(Request $request)
    {
        $this->authorize('treinamento_carteira-etiquetas');

        $resultado = FeedbackCurriculo::select([
            'id', 'curriculo_id', 'telefone_id', 'vaga_id', 'vagas_abertas_id', 'vaga_projeto_id'
        ])->with(
            'Curriculo:id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
            'Curriculo.FotoTres:id',
//            'Admissao:id,feedback_id,area_etiqueta_id,data_admissao,matricula,funcao,nr_trinta_cinco,nr_trinta_tres,numero_cracha,status,cargo',
            'Admissao.AreaEtiqueta',
            'VagaSelecionada:id,nome',
            'Treinamento:id,cadastrou,feedback_id,tipo,created_at,updated_at',
            'Treinamento.Vencimentos',
            'Treinamento.QuemCadastrou:id,nome'
        )->filtrarPorCnpjECentroCusto($request);

        $campoVencimento = $request->campoVencimento == 'true';
        if ($campoVencimento) {
            $periodo = explode(' até ', $request->vencimento);
            $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo[1] . ' 23:59:59');
            $resultado->whereHas('Treinamento', function ($query) use ($dataInicio, $dataFim) {
                $query->whereHas('Vencimentos', function ($q) use ($dataInicio, $dataFim) {
                    $q->where('data_vencimento', '>=', $dataInicio->dataHoraInsert())->where('data_vencimento', '<=', $dataFim->dataHoraInsert());
                });
            });
        }

        $campoPeriodoTreinado = $request->campoPeriodoTreinado == 'true';
        if ($campoPeriodoTreinado) {
            $periodo_treinado = explode(' até ', $request->periodoTreinado);
            $dataInicio = new DataHora($periodo_treinado[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo_treinado[1] . ' 23:59:59');
//            $resultado->whereHas('Treinamento', function ($query) use ($dataInicio_treinado, $dataFim_treinado) {
//                $query->where('created_at', '>=', $dataInicio_treinado->dataInsert())->where('created_at', '<=', $dataFim_treinado->dataInsert());
//            });
            $resultado->whereHas('Treinamento', function ($query) use ($dataInicio, $dataFim) {
                $query->whereHas('Vencimentos', function ($q) use ($dataInicio, $dataFim) {
                    $q->where('data_treinamento', '>=', $dataInicio->dataHoraInsert())->where('data_treinamento', '<=', $dataFim->dataHoraInsert());
                });
            });
        }

        if ($request->campoDemitido) {
            $resultado->Demitidos();
        } else {
            $resultado->Admitidos()->whereHas('ResultadoIntegrado', function ($q) {
                $q->whereEncaminhadoTreinamento(true);
            });
        }


        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('nome', 'like', '%' . $request->campoBusca . '%')->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')->orWhere('id', $request->campoBusca);
                });
//                $query->where('nome', 'like', '%' . $request->campoBusca . '%')->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->whereCpf($request->campoCPF);
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

            if ($request->campo_treinados == 'S') {
                $resultado->has('Treinamento');
            }
            if ($request->campo_treinados == 'N') {
                $resultado->whereDoesntHave('Treinamento');
            }

        }

        if (count($request->treinamentos_selecionados) > 0) {
            $resultado->whereHas('Treinamento.Vencimentos', function ($query) use ($request) {
                $query->whereIn('label', $request->treinamentos_selecionados);
            });
        }

        if ($request->filled('campoNr_trinta_tres')) {
            if ($request->campoNr_trinta_tres == 'Realizado') {
                $resultado->whereHas('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'like', '%NR33');

                });
            }
            if ($request->campoNr_trinta_tres == 'Não Realizado') {
                $resultado->whereDoesntHave('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'like', '%NR33');
                });
            }
            if ($request->campoNr_trinta_tres == 'NÃO SE APLICA') {
                $resultado->whereHas('Admissao', function ($query) use ($request) {
                    $query->where('nr_trinta_tres', $request->campoNr_trinta_tres);
                });
            }
        }

        if ($request->filled('campoNr_trinta_cinco')) {
            if ($request->campoNr_trinta_cinco == 'Realizado') {
                $resultado->whereHas('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'like', '%NR35');
                });
            }
            if (!$request->campoNr_trinta_cinco == 'Não Realizado') {
                $resultado->whereDoesntHave('Treinamento.Vencimentos', function ($query) {
                    $query->where('label', 'like', '%NR35');
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
            if ($request->campoAdmitido == 'S') {
                $resultado->whereHas('Admissao', function ($q) {
                    $q->whereStatus('ADMITIDO');
                });
            }
            if ($request->campoAdmitido == 'N') {
                $resultado->whereDoesntHave('Admissao');
            }
        }

        if ($request->filled('campoCracha')) {
            if ($request->campoCracha == 'S') {
                $resultado->whereHas('Admissao', function ($q) {
                    $q->whereNotNull('numero_cracha');
                });
            }
            if ($request->campoCracha == 'N') {
                $resultado->whereDoesntHave('Admissao', function ($query) use ($request) {
                    $query->whereNull('numero_cracha');
                });
            }
        }

        if ($request->filled('campoFoto')) {
            if ($request->campoFoto == 'true') {
                $resultado->has('Admissao.FotoTres');
            }
            if ($request->campoFoto == 'false') {
                $resultado->whereDoesntHave('Admissao.FotoTres');
            }
        }

        if ($request->filled('campoPcd')) {
            $campoPcd = $request->campoPcd == 'true' ? true : false;
            $resultado->whereHas('Curriculo', function ($query) use ($campoPcd) {
                $query->wherePcd($campoPcd);
            });
        }

        return $resultado->orderByDesc('created_at');
    }

    public function export(Request $request)
    {
        if ($request->selecionados) {
            $resultado = $this->filtro($request)->whereIn('id', $request->selecionados);
        } else {
            $resultado = $this->filtro($request);
        }

        $resultado = $resultado->get()->toArray();

        $treinamentos_selecionados = $request->treinamentos_selecionados;

        $resultado = collect($resultado)->map(function ($item) {
            $item['treinamento']['vencimentos'] = collect($item['treinamento']['vencimentos'])->toArray();
            return $item;
        })->toArray();

        if (count($treinamentos_selecionados) > 0) {
            $resultado = collect($resultado)->map(function ($item) use ($treinamentos_selecionados) {
                $item['treinamento']['vencimentos'] = collect($item['treinamento']['vencimentos'])->filter(function ($vencimento) use ($treinamentos_selecionados) {
                    return in_array($vencimento['label'], $treinamentos_selecionados);
                })->toArray();
                return $item;
            })->toArray();
        }

        $head = [
            "Nome",
//            "Vaga",
            "Cargo",
            "Status",
            "Data Admissão",
            "PCD",
            "Área",
            "Foto 3x4",
            "Treinamento",
            "Data do treinamento",
            "Data do vencimento",
            "Ultima Atualização",
        ];

        $rows = [];

        foreach ($resultado as $row) {
            foreach ($row['treinamento']['vencimentos'] as $vencimento) {
                $rows[] = [
                    $row['curriculo']['nome'],
//                    $row['vaga_aberta']['titulo'],
                    $row['admissao'] ? $row['admissao']['cargo'] : "",
                    $row['admissao'] ? $row['admissao']['status'] : "",
                    $row['admissao'] ? $row['admissao']['data_admissao'] : "",

                    $row['curriculo']['pcd'] ? 'Sim' : 'Não',
                    $row['admissao']['area_etiqueta'] ? $row['admissao']['area_etiqueta']['label'] : 'Não informado',
                    $row['curriculo']['foto_tres'] ? 'Sim' : 'Não',
                    $vencimento['label'],
                    $vencimento['pivot']['data_treinamento'],
                    $vencimento['pivot']['data_vencimento'],
                    $row['treinamento']['updated_at'],
                ];
            }
        }

        $nameArquivo = "treinamentos" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Carteira - Etiqueta", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function carteiraPdf(Request $request)
    {
        //ToDo: Melhoria ajustar query para trazer apenas os dados necessários
        $telefone_supervisor = ClienteConfig::where('cliente_id', auth()->user()->empresa_id)->first()->supervisor_etiqueta_bloqueio;
        $treinamentos = Treinamento::whereIn('feedback_id', $request->selecionados)
            ->get()->transform(function ($item) use ($telefone_supervisor) {
                $telefone = "";
                if ($item->FeedbackCurriculo->Admissao) {
                    $telefone = $telefone_supervisor ? Admissao::getNumeroSupervisor($item->FeedbackCurriculo->empresa_id, $item->FeedbackCurriculo->Admissao->area_etiqueta_id) : \App\Models\Curriculo::getTelPrincipal($item->FeedbackCurriculo->curriculo_id, false);
                }

                $item->telefone = $telefone;
                return $item;
            });

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
//            return response()->json(['msg' => $msg], 400);
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
                    $m->from('naoresponda@mybp.com.br', 'MyBP - E-mail Automatico');
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

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASDOCIMGPDF, Arquivo::DISCO_CLOUD);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CLOUD, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CLOUD, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CLOUD, $arquivo);
    }
    
}
