<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Lancamento;
use App\Models\LancamentoForma;
use App\Models\PlanoConta;
use App\Models\User;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FluxoCaixaController extends Controller {
    public function index(Request $request) {
        return view('g.financeiro.fluxo-caixa.index');
    }

    public function show(Request $request, User $empresa) {


        $dataFinal = new DataHora();
        $dataFinal->setDia($dataFinal->ultimoDiaMes());
        $dataInicial = new DataHora();
        $dataInicial->setDia(1);
        $empresa->load([
            'FormasPagamento'
        ]);

        return response()->json([
            'cliente' => $empresa,
            'dataInicial' => $dataInicial->dataCompleta(),
            'dataFinal' => $dataFinal->dataCompleta(),
            'pode_insert' => auth()->user()->can('fluxo-caixa_insert'),
            'pode_update' => auth()->user()->can('fluxo-caixa_update'),
            'pode_delete' => auth()->user()->can('fluxo-caixa_delete'),
            'pode_realizar' => auth()->user()->can('realizar-lancamento'),
        ], 200);
    }

    public function buscaNomePlanoConta(Request $request) {

        $busca = $request->query('busca');
        if ($busca == '') {
            return response()->json([], 201);
        }
        $quantidade = $request->query('rows');

        return PlanoConta::where('descricao', 'like', '%' . $busca . '%')
            ->whereAtivo(true)
            ->take($quantidade)
            ->get(['id', 'descricao', 'operacao'])
            ->transform(function ($item) {
                $item->label = $item->descricao;
                return $item;
            });
    }

    public function carregarLancamento(Request $request, User $empresa, Lancamento $lancamento) {
        $lancamento->load([
            'PlanoConta',
            'Formas',
            'QuemAlterou:id,nome',
            'QuemCadastrou:id,nome',
            'Formas'
        ]);

        return $lancamento;
    }

    public function cadastrarLancamento(Request $request, User $empresa, Lancamento $lancamento) {

        $dadosValidados = \Validator::make($request->all(), [
            'data_hora' => 'required|min:19|date_format:d/m/Y à\s H:i',
            'plano_id' => 'required|numeric|min:1',
            'operacao' => 'required|in:C,D,T',
            'formas.*.id' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar o lançamento',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();

            // 1- Verificar o prazo limite
            $data_hora = explode(' às ', $request->data_hora);
            $dataHoraLancamento = new DataHora($data_hora[0] . " " . $data_hora[1] . ":00");

            // Validando essa operacao com a rubrica
            if (!PlanoConta::operacaoValida($request->plano_id, $request->operacao)) {
                return response()->json(['msg' => "Operação não suportada pelo plano de conta!"], 400);
            }

            // Se o valor das formas de pagamento é igual a da operação, e valor total
            $erro_pagametos_Credito = FALSE;
            $erro_pagametos_Debito = FALSE;

            $valorTotal = 0.00;
            foreach ($request->formas as $forma) {
                $valorForma = $forma['valorFormat'];
                $valorForma = str_replace('.', '', $valorForma);
                $valorForma = str_replace(',', '.', $valorForma);

                if ($request->operacao == PlanoConta::OPERACAO_CREDITO && $valorForma < 0.00) {
                    $erro_pagametos_Credito = TRUE;
                }

                if ($request->operacao == PlanoConta::OPERACAO_DEBITO && $valorForma > 0.00) {
                    $erro_pagametos_Debito = TRUE;
                }

                $valorTotal += floatval($valorForma);
            }

            if ($erro_pagametos_Credito) {
                return response()->json(['msg' => "Operação de crédito não aceita formas de pagamentos negativos"], 400);
            }
            if ($erro_pagametos_Debito) {
                return response()->json(['msg' => "Operação de débito não aceita formas de pagamentos positivos"], 400);
            }

            // 1° Cadastrar o lançamento
            $EMPRESA_ID = auth()->user()->empresa_id;

            $lancamento = Lancamento::cadastrar($dataHoraLancamento->dataHoraInsert(),$EMPRESA_ID, $request->plano_id, $request->descricao, $valorTotal);

            // 2° Cadastrar/alterar as formas de pagamento
            foreach ($request->formas as $forma) {

                $id_forma = $forma['id'];
                $valor = $forma['valorFormat'];
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);

                LancamentoForma::cadastrar($lancamento->id, $valor, $forma['forma_pagamento_id'], $forma['observacoes']);

            }

            // 3º - Agendamento
            if($request->agendar){
                $data_futura = new DataHora($request->data_pendete);
                $lancamento->concluido=false;
                $lancamento->data_pendente = $data_futura->dataInsert();
                $lancamento->data_hora_concluido=null;
                $lancamento->save();
            }else{
                $lancamento->concluido=true;
                $lancamento->data_pendente = null;
                $lancamento->data_hora_concluido=null;
                $lancamento->save();
            }

            \DB::commit();

            return response()->json($lancamento, 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function alterarLancamento(Request $request, User $empresa, Lancamento $lancamento) {

        $this->authorize('fluxo-caixa_update');
        $PODE_REALIZAR = auth()->user()->can('realizar-lancamento');


        $dadosValidados = \Validator::make($request->all(), [
            'data_hora' => 'required|min:19|date_format:d/m/Y à\s H:i',
            'plano_id' => 'required|numeric|min:1',
            'operacao' => 'required|in:C,D,T',
            'formas.*.id' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar o lançamento',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();



            $data_hora = explode(' às ', $request->data_hora);
            $dataHoraLancamento = new DataHora($data_hora[0] . " " . $data_hora[1] . ":00");


            // Validando essa operacao com a rubrica
            if (!PlanoConta::operacaoValida($request->plano_id, $request->operacao)) {
                return response()->json(['msg' => "Operação não suportada pelo plano de conta!"], 400);
            }

            // Se o valor das formas de pagamento é igual a da operação, e valor total
            $erro_pagametos_Credito = FALSE;
            $erro_pagametos_Debito = FALSE;

            $valorTotal = 0.00;
            foreach ($request->formas as $forma) {
                $valorForma = $forma['valorFormat'];
                $valorForma = str_replace('.', '', $valorForma);
                $valorForma = str_replace(',', '.', $valorForma);

                if ($request->operacao == PlanoConta::OPERACAO_CREDITO && $valorForma < 0.00) {
                    $erro_pagametos_Credito = TRUE;
                }

                if ($request->operacao == PlanoConta::OPERACAO_DEBITO && $valorForma > 0.00) {
                    $erro_pagametos_Debito = TRUE;
                }

                $valorTotal += floatval($valorForma);
            }

            if ($erro_pagametos_Credito) {
                return response()->json(['msg' => "Operação de crédito não aceita formas de pagamentos negativos"], 400);
            }
            if ($erro_pagametos_Debito) {
                return response()->json(['msg' => "Operação de débito não aceita formas de pagamentos positivos"], 400);
            }


            // 1° Alterar o lançamento
            $lancamento->editar($request->plano_id, $request->descricao, $valorTotal, $request->operacao, $dataHoraLancamento->dataHoraInsert());

            // 2° Deletar as formas de pagamento que não terá mais
            if (isset($request->formasDelete)) {
                foreach ($request->formasDelete as $id_forma_delete) {
                    LancamentoForma::whereId($id_forma_delete)->delete();
                }
            }

            // 3° Cadastrar/alterar as formas de pagamento
            foreach ($request->formas as $forma) {

                $id_forma = $forma['id'];
                $valor = $forma['valorFormat'];
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);

                // se o if for zero, entao esta cadastrando uma nova forma
                if ($id_forma == 0) {
                    LancamentoForma::cadastrar($lancamento->id, $valor, $forma['forma_pagamento_id'], $forma['observacoes']);
                } else {
                    LancamentoForma::alterar($id_forma, $valor, $forma['forma_pagamento_id'], $forma['observacoes']);
                }

            }

            //4° Saber se vai agendar ou remover
            if($request->agendar){
                $data_pendenteAntes = new DataHora($lancamento->data_pendente);
                $data_pendente = new DataHora($request->data_pendete);

                //mudou da data do agendamento, mas o lancamento ja estava com data de conclusão e nao tem permissão
                if( ($data_pendenteAntes->dataInsert() !==  $data_pendente->dataInsert()) && $lancamento->data_hora_concluido != null && !$PODE_REALIZAR){
                    return response()->json(['msg' => 'Sem permissão mudar o agendamento'], 400);
                }
                $lancamento->data_pendente = $data_pendente->dataInsert();
                //------------------------------------------------------------
                $datahora_concluidoAntes = new DataHora($lancamento->data_hora_concluido);
                $datahora_concluidoNova = explode(' às ', $request->data_hora_concluido);
                $datahora_concluidoNova = new DataHora($datahora_concluidoNova[0] . " " . $datahora_concluidoNova[1] . ":00");

                if($PODE_REALIZAR){
                    if($request->concluido ===false){
                        $lancamento->data_hora_concluido = null;
                        $lancamento->concluido = false;
                    }
                    if($request->concluido ===true){
                        $lancamento->data_hora_concluido = $datahora_concluidoNova->dataHoraInsert();
                        $lancamento->concluido = true;
                    }
                }

                /*// quer marcar ou desmarcar concluido sem term permissao...
                if($request->concluido != $lancamento->concluido && !$PODE_REALIZAR){
                    return response()->json(['msg' => 'Sem permissão para mudar status de realizado'], 400);
                }

                // mudar a data de concluir sem ter permissão
                if( $datahora_concluidoAntes->dataHoraInsert() !=  $datahora_concluidoNova->dataHoraInsert() && !$PODE_REALIZAR){
                    return response()->json(['msg' => 'Sem permissão para mudar status de realizado'], 400);
                }*/




            }else{
                // nao vai agendar, nada, ou limpando informação que ja tinha
                if($lancamento->data_hora_concluido != null && !$PODE_REALIZAR){
                    return response()->json(['msg' => 'Sem permissão para remover o agendamento'], 400);
                }
                $lancamento->data_pendente = null;
                $lancamento->data_hora_concluido = null;
                $lancamento->concluido = true;
            }
            $lancamento->save();
            $lancamento->refresh();


            \DB::commit();

            return response()->json($lancamento, 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }


    }

    public function excluirLancamento(Request $request, User $empresa, Lancamento $lancamento) {

        $this->authorize('fluxo-caixa_delete');

        try {
            \DB::beginTransaction();

            // Excluir
            $lancamento->excluir();

            \DB::commit();
        } catch (\Exception $e) {

            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }


    }

    public function mudarStatus(Request $request, User $empresa, Lancamento $lancamento) {

        $dadosValidados = \Validator::make($request->all(), [
            'status' => 'required|boolean',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao mudar status o lançamento',
                'erros' => $dadosValidados->errors(),
                'lancamento' => $lancamento
            ], 400);
        }
        try {
            \DB::beginTransaction();

            $agora = new DataHora();

            //4° Saber se vai agendar ou remover
            if($request->status){
                $lancamento->data_hora_concluido = $agora->dataHoraInsert();
                $lancamento->concluido = true;
                $lancamento->save();
            }else{
                $lancamento->data_hora_concluido = null;
                $lancamento->concluido = false;
                $lancamento->save();
            }

            $lancamento->refresh();


            \DB::commit();

            return response()->json($lancamento, 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
                'erros' => $dadosValidados->errors(),
                'lancamento' => $lancamento
            ], 400);
            return response()->json(['msg' => $e->getMessage()], 400);
        }


    }

    public function atualizaFluxoCaixa(Request $request, User $empresa) {


        $datas = explode(' até ', $request->intervalo);
        $dataInicio = new DataHora($datas[0] . ' 00:00:00');
        $dataFim = new DataHora($datas[1] . ' 23:59:59');
        $FILTRAR = $request->filtrar != null ? $request->filtrar : null;
        $porPagina = $request->get('porPagina');

        $lancamentos = Lancamento::whereEmpresaId($empresa->id);
        if($request->filled('por_periodo') && $request->por_periodo===true){
            $lancamentos->whereBetween('data_hora', [$dataInicio->dataHoraInsert(), $dataFim->dataHoraInsert()]);
        }

        if ($FILTRAR) {
            $lancamentos->whereIn('plano_id', $FILTRAR);
        }
        if ($request->has('campoBusca') && $request->campoBusca!='') {
            $busca = $request->get('campoBusca');
            $lancamentos->where('descricao', 'like', '%' . $busca . '%');
        }

        $lancamentos->orderBy('data_hora')
            ->orderBy('id');

        //Pegando a filtragem de rubricas
        $lancamentosRubrica = Lancamento::whereEmpresaId($empresa->id)
            ->whereBetween('data_hora', [$dataInicio->dataHoraInsert(), $dataFim->dataHoraInsert()]);

        $lancamentosRubrica->groupBy('plano_id')->select('plano_id');

        $planosDeConta = PlanoConta::distinct()->with([
            'Categoria:id,descricao',
        ])->whereIn('id', $lancamentosRubrica->get('plano_id')->pluck('plano_id')->toArray())->get();

        $lancamentos->with([
            'PlanoConta:id,descricao',
        ]);


        $totalCredito = clone $lancamentos;
        $totalDebito = clone $lancamentos;
        $totalCredito = (float) $totalCredito->whereConcluido(true)->whereOperacao('C')->where('valor','>',0)->sum('valor');
        $totalDebito = (float) $totalDebito->whereConcluido(true)->whereOperacao('D')->where('valor','<',0)->sum('valor');
        $saldoTotal = $totalCredito + $totalDebito;


        $lancamentos = $lancamentos->paginate($porPagina);
        collect($lancamentos->items())->transform(function($lan){
            $lan->saldoAtual= $lan->saldo;
            return $lan;
        });
        $primeiroLancamentoDaLista = collect($lancamentos->items())->first();
        $saldoAnterior=0;
        if($primeiroLancamentoDaLista){
            $saldoAnterior = (float) Lancamento::where('data_hora','<=',$primeiroLancamentoDaLista->data_hora)
                ->whereNotIn('id', [$primeiroLancamentoDaLista->id])
                //->where('cliente_id', $CLIENTE_ID)
                ->orderBy('data_hora')
                ->orderBy('id')
                ->whereConcluido(true)->sum('valor');
        }

        return response()->json([
            'atual' => $lancamentos->currentPage(),
            'ultima' => $lancamentos->lastPage(),
            'total' => $lancamentos->total(),
            'dados' => [
                'lista' => $lancamentos->items(),
                'planos_filtro' => $planosDeConta,
                'total_receitas' => $totalCredito,
                'total_despesas' => $totalDebito,
                'saldo_total' => $saldoTotal,
                'saldoAntetior'=>$saldoAnterior,
            ]
        ], 200);

    }

    //---- PDF ------
    /*public function imprimirContaCorrente(Request $request, ContaCorrente $conta) {

        $RESUMO = $request->has('resumo') ? true : false;
        $intervalo = explode(' até ', $request->intervalo);
        $dataInicial = new DataHora($intervalo[0] . " 00:00:00");
        $dataFinal = new DataHora($intervalo[1] . " 23:59:59");
        $ID_IMOVEL = $request->has('imovel_id') ? intval($request->imovel_id) : null;
        $FILTRAR = $request->filtro != null ? explode(',', $request->filtro) : null;


        $lancamentos = ContaCorrenteLancamento::whereContaId($conta->id)->whereBetween('data_hora', [$dataInicial->dataHoraInsert(), $dataFinal->dataHoraInsert()]);
        if ($ID_IMOVEL > 0) {
            $lancamentos->where('imovel_id', $ID_IMOVEL);
        }
        if ($FILTRAR && count($FILTRAR) > 0) {
            $lancamentos->whereIn('rubrica_id', $FILTRAR);
        }

        $lancamentos = $lancamentos->orderBy('data_hora')->orderBy('id')->get();


        //Titular
        $titular = '';
        if ($conta->contaCliente() || $conta->contaBanco() || $conta->contaImobiliaria() || $conta->contaCaixa()) {
            $titular = $conta->Usuario->nome;
        }
        if ($conta->contaCorretor()) {
            $titular = $conta->Usuario->nome . ' (Corretor)';
        }
        if ($conta->contaAdministrador()) {
            return response('Reservado para administrador do sistema.', 403);
        }

        //Saldo anterior
        $saldoInicialDe = new DataHora($dataInicial->dataCompleta());
        $saldoInicialDe->subtrairSegundo(1);

        $saldoAnterior = ContaCorrenteLancamento::saldoLancamentoAnterior($saldoInicialDe->dataHoraInsert(), $conta->id, $ID_IMOVEL, NULL);

        $dados = [
            'lancamentos' => $lancamentos,
            'titular' => mb_strtoupper($titular),
            'dataInicial' => $dataInicial->dataCompleta(),
            'dataFinal' => $dataFinal->dataCompleta(),
            'saldoAnterior' => $saldoAnterior,
            'saldoInicialDe' => $saldoInicialDe->dataCompleta(),
            'filtrarPorImovel' => $ID_IMOVEL == null ? false : true,
            'resumo' => $RESUMO,
        ];

        if ($RESUMO) {

            //Filtrando...
            $grupoReceitas = collect($lancamentos)->filter(function ($item, $key) {
                return $item->valor > 0;
            });
            $grupoDespesas = collect($lancamentos)->filter(function ($item, $key) {
                return $item->valor < 0;
            });
            //Agrupando por rubrica
            $grupoReceitas = collect($grupoReceitas)->groupBy('rubrica_id');
            $grupoDespesas = collect($grupoDespesas)->groupBy('rubrica_id');

            $LISTA_MOSTRAR_DATAS = Rubrica::listaDeRubricasGastoFixo();

            $RECEITAS = [];
            $totalReceitas = 0;
            foreach ($grupoReceitas as $id_rubrica => $lancamentosDeReceita) {
                $rubrica = Rubrica::find($id_rubrica);
                $ultimoLancamento = collect($lancamentosDeReceita)->last();
                $total = collect($lancamentosDeReceita)->sum('valor');
                $RECEITAS[] = [
                    'id' => $rubrica->id,
                    'nome' => $rubrica->descricao,
                    'pagamento' => in_array($id_rubrica, $LISTA_MOSTRAR_DATAS) ? $ultimoLancamento->dataLancamento() : '', // apenas as de gasto fixos mostrarão as datas
                    'quantidade' => count($lancamentosDeReceita),
                    'total' => $total
                ];
                $totalReceitas += $total;
            }
            $dados['grupoReceitas'] = $RECEITAS;
            $dados['totalReceitas'] = $totalReceitas;


            $DESPESAS = [];
            $totalDespesas = 0;
            foreach ($grupoDespesas as $id_rubrica => $lancamentosDeDespesa) {
                $rubrica = Rubrica::find($id_rubrica);
                $ultimoLancamento = collect($lancamentosDeDespesa)->last();
                $total = collect($lancamentosDeDespesa)->sum('valor');
                $DESPESAS[] = [
                    'id' => $rubrica->id,
                    'nome' => $rubrica->descricao,
                    'pagamento' => in_array($id_rubrica, $LISTA_MOSTRAR_DATAS) ? $ultimoLancamento->dataLancamento() : '', // apenas as de gasto fixos mostrarão as datas
                    'quantidade' => count($lancamentosDeDespesa),
                    'total' => $total
                ];
                $totalDespesas += $total;
            }
            $dados['grupoDespesas'] = $DESPESAS;
            $dados['totalDespesas'] = $totalDespesas;
        }
        //return view('pdf.g.contas-correntes.extrato',$dados);
        $pdf = PDF::loadView('pdf.g.contas-correntes.extrato', $dados);

        return $pdf->stream("Extrado de conta corrente Nº {$conta->id} .pdf");
    }*/

    public function correcaoSaldo(Request $request) {

        require __DIR__ . '/vendor/autoload.php';

        ini_set("memory_limit", -1);
        ini_set('max_execution_time', -1);

        //ini_set('memory_limit', '2000M');
        //ini_set('max_execution_time', '-1');
        /*$contas = ContaCorrente::whereHas('Usuario', function ($q) {
            //$q->whereIn('tipo', [User::$PESSOA_FISICA, User::$PESSOA_JURIDICA]);
        });*/
        $contas = ContaCorrente::whereIn('id', [91]);
        foreach ($contas->get() as $contaCorrente) {

            $ID_CONTA = $contaCorrente->id;
            $SALDO_TOTAL = 0;
            $SALDO_IMOVEL = [];
            $SALDO_IMOVEL_TOTAL = 0;
            $USER_ID = $contaCorrente->user_id;
            $ERRO_SALDO = false;
            $ERRO_SALDO_IMOVEL = false;

            $SALDO_TOTAL_ANTES = (float)ContaCorrenteLancamento::whereContaId($ID_CONTA)->whereBetween('data_hora', ['2014-01-01 00:00:00', '2040-03-31 23:59:59'])->orderBy('data_hora')->orderBy('id')->sum('valor');
            $SALDO_TOTAL_ATUAL_ANTES = ContaCorrenteLancamento::whereContaId($ID_CONTA)->whereBetween('data_hora', ['2014-01-01 00:00:00', '2040-03-31 23:59:59'])->orderBy('data_hora')->orderBy('id')->get()->last();

            if ($SALDO_TOTAL_ATUAL_ANTES && number_format($SALDO_TOTAL_ANTES, 2, '.', '') != number_format($SALDO_TOTAL_ATUAL_ANTES->saldo_total, 2, '.', '')) {
                //dd($contaCorrente->Usuario->nome,$SALDO_TOTAL_ANTES,$SALDO_TOTAL_ATUAL_ANTES->saldo_total);
                echo "A conta de {$contaCorrente->Usuario->nome} ($ID_CONTA) tem um saldo de R$ {$SALDO_TOTAL_ATUAL_ANTES->saldo_total}, mas o correto é R$ $SALDO_TOTAL_ANTES<br>";
            } else {
                continue;
            }

            foreach (ContaCorrenteLancamento::whereContaId($ID_CONTA)->whereBetween('data_hora', ['2014-01-01 00:00:00', '2040-03-31 23:59:59'])->orderBy('data_hora')->orderBy('id')->get() as $lancamento) {
                //foreach (ContaCorrenteLancamento::whereContaId($ID_CONTA)->whereBetween('data_hora', ['2020-03-01 00:00:00', '2020-03-31 23:59:59'])->orderBy('data_hora')->orderBy('id')->get() as $lancamento) {
                $SALDO_TOTAL += floatval($lancamento->valor);

                if (number_format($SALDO_TOTAL, 2, '.', '') !== number_format($lancamento->saldo_total, 2, '.', '')) {
                    $lancamento->saldo_total = (float)number_format($SALDO_TOTAL, 2, '.', '');
                    $lancamento->save();
                    if (!$ERRO_SALDO) {
                        echo "Conta {$lancamento->conta_id} de <strong>{$lancamento->Usuario->nome}</strong> :Saldo diferente no lancamento em {$lancamento->dataHoraLancamento()} valor de {$lancamento->valor_format}. O saldo total esta somando até aqui $SALDO_TOTAL mas no lancamento esta totalizando {$lancamento->saldo_total}<br>";
                        $ERRO_SALDO = true;
                        //break;
                    }
                }

                /*if ($lancamento->imovel_id) {
                    $SALDO_IMOVEL[$lancamento->imovel_id] = isset($SALDO_IMOVEL[$lancamento->imovel_id]) ? $SALDO_IMOVEL[$lancamento->imovel_id] : 0;
                    $SALDO_IMOVEL[$lancamento->imovel_id] += floatval($lancamento->valor);
                    $SALDO_IMOVEL_TOTAL += $lancamento->valor; // todos os imóveis juntos

                    if (number_format($SALDO_IMOVEL[$lancamento->imovel_id], 2, '.', '') !== number_format($lancamento->saldo, 2, '.', '')) {
                        //dd("Conta {$lancamento->conta_id} de {$lancamento->Usuario->nome} : Saldo diferente no lancamento id {$lancamento->id} em {$lancamento->dataHoraLancamento()}. O saldo do imovel ID {$lancamento->imovel_id} esta somando até aqui " . $SALDO_IMOVEL[$lancamento->imovel_id] . " mas o lancamento esta marcando {$lancamento->saldo}", $SALDO_IMOVEL[$lancamento->imovel_id], $lancamento->saldo, $lancamento);
                        if(!$ERRO_SALDO_IMOVEL){
                            echo "Conta {$lancamento->conta_id} de {$lancamento->Usuario->nome} : Saldo diferente no lancamento id {$lancamento->id} em {$lancamento->dataHoraLancamento()}. O saldo do imovel ID {$lancamento->imovel_id} esta somando até aqui " . $SALDO_IMOVEL[$lancamento->imovel_id] . " mas o lancamento esta marcando {$lancamento->saldo}<br>";
                            $ERRO_SALDO_IMOVEL = true;
                        }
                    }
                }*/
                /* if ($ERRO_SALDO || $ERRO_SALDO_IMOVEL) {
                     break;
                 }*/
            }

            /*if (number_format($SALDO_IMOVEL_TOTAL, 2, '.', '') !== number_format(ContaCorrenteSaldo::saldo($USER_ID), 2, '.', '')) {
                //dd("A soma total dos imóveis esta dando $SALDO_IMOVEL_TOTAL mas no banco de dados está dando " . ContaCorrenteSaldo::saldo($USER_ID), $contaCorrente);
                echo "A soma total dos imóveis esta dando $SALDO_IMOVEL_TOTAL mas no banco de dados está dando ".number_format(ContaCorrenteSaldo::saldo($USER_ID), 2, '.', '')."<br>";

            }*/
            echo "----------------------------------------------------------------------------------------------------------------------<br>";


        }
    }
}
