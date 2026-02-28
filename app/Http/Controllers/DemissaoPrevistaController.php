<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaExportaExcel;
use App\Jobs\Movimentacao\DemissaoPrevista\JobNotificacaoRecursiva;
use App\Jobs\AssinaturaDigital\JobProcessarEnvioAssinatura;
use App\Models\AprovacaoExtraConfig;
use App\Models\Arquivo;
use App\Models\DemissaoPrevista;
use App\Models\DocumentoParaAssinatura;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

class DemissaoPrevistaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['valor'] = $dados['valor_format'];
        $dados['user_id'] = auth()->user()->id;

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_id' => 'required',
                'centro_custo_filial_id' => 'required_if:filial,true',
                'colaborador_id' => 'required',
                'valor_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Demissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $demissaoPrevista = DemissaoPrevista::create($dados);

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $demissaoPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            Log::info('DemissaoPrevistaController@store - Demissão Prevista ID: ' . $demissaoPrevista->id);

            // Notifica próxima etapa + etapas anteriores (recursivo)
            JobNotificacaoRecursiva::dispatch($demissaoPrevista->id, $demissaoPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Demissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\DemissaoPrevista $demissaoPrevista
     * @return DemissaoPrevista
     */
    public function edit(DemissaoPrevista $demissaoPrevista)
    {
        $demissaoPrevista->autocomplete_label_colaborador = $demissaoPrevista->Colaborador ? $demissaoPrevista->Colaborador->nome : '';
        $demissaoPrevista->autocomplete_label_colaborador_anterior = $demissaoPrevista->Colaborador ? $demissaoPrevista->Colaborador->nome : '';

        $demissaoPrevista->autocomplete_label_gestor_modal = $demissaoPrevista->GestorAprovacao ? $demissaoPrevista->GestorAprovacao->nome : '';
        $demissaoPrevista->autocomplete_label_gestor_modal_anterior = $demissaoPrevista->GestorAprovacao ? $demissaoPrevista->GestorAprovacao->nome : '';
        $demissaoPrevista->anexosDel = [];
        $demissaoPrevista->user_aprovacao = $demissaoPrevista->UserAprovacao ? $demissaoPrevista->UserAprovacao->nome : '';
        $demissaoPrevista->rh_aprovacao = $demissaoPrevista->RhAprovacao ? $demissaoPrevista->RhAprovacao->nome : '';
        $demissaoPrevista->aprovacao_extra = $demissaoPrevista->AprovacaoExtra ? [
            'nome' => $demissaoPrevista->AprovacaoExtra->nome,
            'id' => $demissaoPrevista->AprovacaoExtra->id
        ] : null;
        $demissaoPrevista->aprovacao_extra_nome = $demissaoPrevista->AprovacaoExtra ? $demissaoPrevista->AprovacaoExtra->nome : null;
        $demissaoPrevista->status_aprovacao = $demissaoPrevista->status_aprovacao ?: '';
        $demissaoPrevista->status_aprovacao_rh = $demissaoPrevista->status_aprovacao_rh ?: '';
        $demissaoPrevista->load('Anexos');

        return $demissaoPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\DemissaoPrevista $demissaoPrevista
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, DemissaoPrevista $demissaoPrevista)
    {
        $dados = $request->input();
        $dados['valor'] = $dados['valor_format'];

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'valor_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Demissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $demissaoPrevista->update($dados);

            if (isset($dados['anexosDel'])) {
                foreach ($dados['anexosDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $demissaoPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao atualizar Solicitação de Demissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);
        $itens = collect($resultado->items());

        $demissoesIds = $itens->pluck('id')->filter()->values()->all();
        $docsByDemissaoId = [];
        if (!empty($demissoesIds)) {
            $docs = DocumentoParaAssinatura::withoutGlobalScopes()
                ->select(['id', 'token', 'status', 'arquivo_assinado_id', 'tipo_documento', 'documentable_id'])
                ->where('empresa_id', auth()->user()->empresa_id)
                ->where('documentable_type', DemissaoPrevista::class)
                ->whereIn('documentable_id', $demissoesIds)
                ->orderBy('id', 'desc')
                ->get();

            foreach ($docs as $doc) {
                if (!isset($docsByDemissaoId[$doc->documentable_id])) {
                    $docsByDemissaoId[$doc->documentable_id] = [
                        'id' => $doc->id,
                        'token' => $doc->token,
                        'status' => $doc->status,
                        'arquivo_assinado_id' => $doc->arquivo_assinado_id,
                        'tipo_documento' => $doc->tipo_documento,
                    ];
                }
            }
        }

        $itens = $itens->map(function ($item) use ($docsByDemissaoId) {
            $item->documento_para_assinatura = $docsByDemissaoId[$item->id] ?? null;
            return $item;
        })->values();

        // Busca configuração de aprovação extra ativa
        $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'demissao');
        $podeAprovarExtra = false;
        $nomeAprovacaoExtra = '';

        if ($config) {
            $podeAprovarExtra = $config->podeAprovar(auth()->id());
            $nomeAprovacaoExtra = $config->nome_aprovacao;
        }

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
                'pode_aprovar_extra' => $podeAprovarExtra,
                'tem_aprovacao_extra' => $config ? true : false,
                'nome_aprovacao_extra' => $nomeAprovacaoExtra,
                'mimes' => Arquivo::MIMEAPENASIMAGENSPDF
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = DB::table('demissao_previstas as dp')
            ->select(
                'dp.id',
                'us.nome as solicitante_nome',
                'dp.empresa_id',
                'dp.colaborador_id',
                'c.nome as colaborador_nome',
                'c.email as colaborador_email',
                'c.cpf as colaborador_cpf',
                'dp.centro_custo_id',
                'cc.label as centro_custo',
                'dp.filial',
                'dp.centro_custo_filial_id',
                'dp.data_aprovacao',
                'dp.data_aprovacao_rh',
                'dp.data_aprovacao_extra',
                DB::raw("DATE_FORMAT(d.data_desmobilizacao, '%d/%m/%Y') as data_desmobilizacao"),
                DB::raw("DATE_FORMAT(a.data_admissao, '%d/%m/%Y') as data_admissao"),
                DB::raw("DATE_FORMAT(dp.data_demissao, '%d/%m/%Y') as data_demissao"),
                DB::raw("DATE_FORMAT(dp.created_at, '%d/%m/%Y às %H:%i:%s') as data_solicitacao"),
                DB::raw("DATE_FORMAT(dp.data_aprovacao_rh, '%d/%m/%Y às %H:%i:%s') as data_aprovacao_rh"),
                DB::raw("DATE_FORMAT(dp.data_aprovacao, '%d/%m/%Y às %H:%i:%s') as data_aprovacao"),
                DB::raw("DATE_FORMAT(dp.data_aprovacao_extra, '%d/%m/%Y às %H:%i:%s') as data_aprovacao_extra"),
                'a.cargo',
                'dp.tipo_aviso',
                'dp.aprovado_via_script',
                'dp.status',
                'dp.status_aprovacao',
                'dp.status_aprovacao_rh',
                'dp.status_aprovacao_extra',
                'ugestor.nome as gestor_nome',
                'usa.nome as user_aprovacao_nome',
                'urh.nome as rh_aprovacao_nome',
                'uextra.nome as aprovacao_extra_nome',
                'dp.created_at',
                'dp.obs',
                'dp.obs_rh'
            )
            ->join('users as u', 'dp.colaborador_id', '=', 'u.id')
            ->join('users as us', 'dp.user_id', '=', 'us.id')
            ->join('curriculos as c', 'u.id', '=', 'c.id')
            ->join('feedback_curriculos as fc', function ($join) {
                $join->on('u.id', '=', 'fc.curriculo_id')
                    ->whereNull('fc.deleted_at')
                    ->whereRaw('fc.id = (SELECT MAX(id) FROM feedback_curriculos WHERE curriculo_id = u.id AND feedback_curriculos.deleted_at IS NULL)');
            })
            ->join('admissoes as a', function ($join) {
                $join->on('fc.id', '=', 'a.feedback_id')
                    ->whereNull('a.deleted_at')
                    ->whereRaw('a.id = (SELECT MAX(id) FROM admissoes WHERE feedback_id = fc.id AND admissoes.deleted_at IS NULL)');
            })
            ->leftjoin('centro_custos as cc', 'dp.centro_custo_id', '=', 'cc.id')
            ->leftjoin('centro_custo_filials as ccf', 'dp.centro_custo_filial_id', '=', 'ccf.id')
            ->leftjoin('users as ugestor', 'ugestor.id', '=', 'dp.gestor_id')
            ->leftjoin('users as urh', 'urh.id', '=', 'dp.rh_aprovacao_id')
            ->leftjoin('users as usa', 'dp.user_aprovacao_id', '=', 'usa.id')
            ->leftjoin('users as uextra', 'dp.aprovacao_extra_id', '=', 'uextra.id')
            ->leftjoin('demissaos as d', function ($join) {
                $join->on('fc.id', '=', 'd.feedback_id')
                    ->whereRaw('d.id = (SELECT MAX(id) FROM demissaos WHERE feedback_id = fc.id)');
            })
            ->where('dp.empresa_id', '=', auth()->user()->empresa_id)
            ->whereNull('dp.deleted_at');

        // Filtro por token (mascara o id na URL/request). Formato: hash . 'lpve' . id
        if ($request->filled('token')) {
            $token = (string) $request->token;
            if (strpos($token, 'lpve') !== false) {
                $parts = explode('lpve', $token, 2);
                $id = isset($parts[1]) ? (int) $parts[1] : 0;
                if ($id > 0) {
                    $resultado->where('dp.id', $id);
                }
            }
        }

        $filtroPeriodo = $request->boolean('filtroPeriodo') || $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $dataInicio = $request->dataInicio;
            $dataFim = $request->dataFim;

            if ($dataInicio && $dataFim) {
                $inicio = new DataHora($dataInicio . ' 00:00:00');
                $fim = new DataHora($dataFim . ' 23:59:59');
                $resultado->where('dp.created_at', '>=', $inicio->dataHoraInsert())
                    ->where('dp.created_at', '<=', $fim->dataHoraInsert());
            } elseif ($request->filled('periodo')) {
                $periodo = explode(' até ', $request->periodo);
                if (count($periodo) === 2) {
                    $inicio = new DataHora($periodo[0] . ' 00:00:00');
                    $fim = new DataHora($periodo[1] . ' 23:59:59');
                    $resultado->where('dp.created_at', '>=', $inicio->dataHoraInsert())
                        ->where('dp.created_at', '<=', $fim->dataHoraInsert());
                }
            }
        }

        if ($request->filled('campoBusca')) {
            $resultado->where(function ($r) use ($request) {
                $r->where('c.nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('c.id', $request->campoBusca)
                    ->orWhere('dp.id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatusAprovacao')) {
            $resultado->when($request->campoStatusAprovacao == 'aberto', function ($query) {
                return $query->whereNull('dp.status_aprovacao');
            })
                ->when($request->campoStatusAprovacao == 'aprovado_gestor', function ($query) {
                    return $query->where('dp.status_aprovacao', DemissaoPrevista::STATUS_APROVADO)
                        ->whereNull('dp.status_aprovacao_extra')
                        ->whereNull('dp.status_aprovacao_rh');
                })
                ->when($request->campoStatusAprovacao == 'aprovado_extra', function ($query) {
                    return $query->where('dp.status_aprovacao_extra', DemissaoPrevista::STATUS_APROVADO)
                        ->whereNull('dp.status_aprovacao_rh');
                })
                ->when($request->campoStatusAprovacao == 'aprovado_rh', function ($query) {
                    return $query->where('dp.status_aprovacao_rh', DemissaoPrevista::STATUS_APROVADO);
                })
                ->when($request->campoStatusAprovacao == 'reprovado', function ($query) {
                    return $query->where(function ($query) {
                        $query->where('dp.status_aprovacao', DemissaoPrevista::STATUS_REPROVADO)
                            ->orWhere('dp.status_aprovacao_extra', DemissaoPrevista::STATUS_REPROVADO)
                            ->orWhere('dp.status_aprovacao_rh', DemissaoPrevista::STATUS_REPROVADO);
                    });
                });
        }

        if (!auth()->user()->temPrivilegioGestaoRh()) {
            $resultado->where(function ($query) {
                $query->where('dp.user_id', auth()->user()->id)
                    ->orWhere('dp.gestor_id', auth()->user()->id);
            });
        }

        // Ordenação
        $ordenacao = $request->input('ordenacao', 'created_at_desc');
        switch ($ordenacao) {
            case 'created_at_asc':
                $resultado->orderBy('dp.created_at', 'asc');
                break;
            case 'updated_at_desc':
                $resultado->orderBy('dp.updated_at', 'desc');
                break;
            case 'created_at_desc':
            default:
                $resultado->orderBy('dp.created_at', 'desc');
                break;
        }

        return $resultado;
    }

    public function aprovar(Request $request, DemissaoPrevista $demissaoPrevista)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();

            // Busca configuração ativa de aprovação extra
            $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'demissao');

            $demissaoPrevista->update([
                'user_aprovacao_id' => auth()->id(),
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);
            DB::commit();

            // Dispara notificação recursiva
            JobNotificacaoRecursiva::dispatch($demissaoPrevista->id, $demissaoPrevista->empresa_id);

            return response()->json(['tem_aprovacao_extra' => $config ? true : false], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Solicitação:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarExtra(Request $request, DemissaoPrevista $demissaoPrevista)
    {
        $dados = $request->input();

        // Busca configuração ativa de aprovação extra para demissão
        $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'demissao');

        if (!$config) {
            return response()->json(['msg' => 'Não existe configuração de aprovação extra ativa'], 400);
        }

        // Verifica se o usuário pode aprovar
        if (!$config->podeAprovar(auth()->id())) {
            return response()->json(['msg' => 'Você não tem permissão para aprovar esta solicitação'], 403);
        }

        try {
            DB::beginTransaction();
            $demissaoPrevista->update([
                'aprovacao_extra_id' => auth()->id(),
                'status_aprovacao_extra' => $dados['status_aprovacao_extra'],
                'obs_aprovacao_extra' => $dados['obs_aprovacao_extra'],
                'data_aprovacao_extra' => (new DataHora())->dataHoraInsert()
            ]);
            DB::commit();

            // Dispara notificação recursiva
            JobNotificacaoRecursiva::dispatch($demissaoPrevista->id, $demissaoPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "Erro ao aprovar solicitação (Aprovação Extra): {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Aprovação pelo RH
     *
     * @param Request $request
     * @param DemissaoPrevista $demissaoPrevista
     * @return \Illuminate\Http\JsonResponse
     */
    public function aprovarRH(Request $request, DemissaoPrevista $demissaoPrevista)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $demissaoPrevista->update([
                'rh_aprovacao_id' => auth()->id(),
                'status_aprovacao_rh' => $dados['status_aprovacao_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert()
            ]);

            DB::commit();

            // Dispara notificação recursiva
            JobNotificacaoRecursiva::dispatch($demissaoPrevista->id, $demissaoPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    // Notificações agora são gerenciadas pelo JobNotificacaoRecursiva
    // Não há mais necessidade de métodos separados de notificação

    //Excel
    public function export(Request $request)
    {
        $filtros = $request->all();
        $filtros['_full_export_access'] = auth()->user()->temPrivilegioGestaoRh()
            || auth()->user()->can('privilegio_aprovar_por_rh')
            || auth()->user()->can('privilegio_aprovar_rh');

        $nomeArquivo = 'demissao_prevista_' . rand(1000, 9999) . '_' . date('YmdHis') . '.csv';
        JobDemissaoPrevistaExportaExcel::dispatch(auth()->id(), 'Planejamento - Movimentação - Demissão', $nomeArquivo, $filtros);
        return response()->json(['msg' => 'Estamos gerando seu arquivo, assim que finalizado você será notificado.']);
    }

    public function pdf(DemissaoPrevista $demissaoPrevista, Request $request)
    {
        $pdf = PDF::loadView('pdf.planejamento.movimentacao.demissao.avisoprevio', compact('demissaoPrevista'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("pdf_" . Str::slug($demissaoPrevista->Colaborador->nome) . (new DataHora())->nomeUnico() . ".pdf");
    }

    /**
     * Envia documento de demissão (Aviso Prévio) para assinatura digital.
     */
    public function enviarParaAssinatura(Request $request)
    {
        $request->validate([
            'demissao_prevista_id' => 'required|integer',
            'signatarios' => 'required|array|min:1',
            'signatarios.*.email' => 'required|email',
            'signatarios.*.nome' => 'required|string|max:255',
            'signatarios.*.cpf' => 'nullable|string|max:14',
            'signatarios.*.user_id' => 'nullable|exists:users,id',
        ]);

        $demissaoPrevista = DemissaoPrevista::whereId($request->demissao_prevista_id)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with('Colaborador')
            ->first();
        if (!$demissaoPrevista || !$demissaoPrevista->Colaborador) {
            return response()->json(['success' => false, 'message' => 'Demissão não encontrada.'], 404);
        }

        $empresaId = auth()->user()->empresa_id;

        JobProcessarEnvioAssinatura::dispatch(
            JobProcessarEnvioAssinatura::TIPO_DEMISSAO,
            $empresaId,
            auth()->id(),
            ['demissao_prevista_id' => (int) $request->demissao_prevista_id],
            $request->signatarios
        );

        return response()->json([
            'success' => true,
            'message' => 'Solicitação recebida. O documento será processado e enviado para assinatura.',
        ], 202);
    }

    /**
     * Atualização de status em massa
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = DemissaoPrevista::find($selecionado);

                $dados->update([
                    'user_aprovacao_id' => auth()->id(),
                    'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                    'obs_aprovacao' => $request->obs_aprovacao,
                    'status_aprovacao' => $request->status_aprovacao,
                ]);

                DB::commit();
            }
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação em massa:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }
}
