<?php

namespace App\Http\Controllers;

use App\Models\AtaReuniao;
use App\Models\AtaReuniaoAcao;
use App\Models\AtaReuniaoAcesso;
use App\Models\AtaReuniaoAnexo;
use App\Models\AtaReuniaoCiencia;
use App\Models\AtaReuniaoComentario;
use App\Models\AtaReuniaoCompartilhamentoExterno;
use App\Models\AtaReuniaoNotificacao;
use App\Models\AtaReuniaoNotificacaoConfig;
use App\Models\AtaReuniaoAssunto;
use App\Models\AtaReuniaoParticipante;
use App\Models\AtaReuniaoTipo;
use App\Models\User;
use App\Jobs\AtaReuniao\ExportAtaReuniaoRelatorioJob;
use App\Services\AtaReuniao\AtaReuniaoAprovacaoService;
use App\Services\AtaReuniao\AtaReuniaoAccessService;
use App\Services\AtaReuniao\AtaReuniaoCodigoService;
use App\Services\AtaReuniao\AtaReuniaoCompartilhamentoService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        $this->authorize('administracao_atareuniao_insert');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'local' => 'required',
                'data_inicio' => 'required',
                'data_fim' => 'required',
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
                $empresaId = auth()->user()->empresa_id;

                $newTime = explode(' às ', $dados['data_inicio']);
                $newDH = $newTime[0] . ' ' . $newTime[1];
                $data = new DataHora($newDH);
                $dados['data_inicio'] = $data->dataHoraInsert();

                $newTime = explode(' às ', $dados['data_fim']);
                $newDH = $newTime[0] . ' ' . $newTime[1];
                $data = new DataHora($newDH);
                $dados['data_fim'] = $data->dataHoraInsert();

                $ata = [
                    'codigo' => app(AtaReuniaoCodigoService::class)->gerar((int) $empresaId, $dados['area_etiqueta_id'] ?? null),
                    'uuid_publico' => (string) Str::uuid(),
                    'centro_custo_id' => $dados['centro_custo_id'] ?? null,
                    'area_etiqueta_id' => $dados['area_etiqueta_id'] ?? null,
                    'quem_cadastrou' => auth()->user()->id,
                    'organizador_id' => $dados['organizador_id'] ?? auth()->id(),
                    'redator_id' => $dados['redator_id'] ?? null,
                    'titulo' => $dados['titulo'] ?? 'Ata de Reunião',
                    'objetivo' => $dados['objetivo'] ?? null,
                    'status' => AtaReuniao::STATUS_RASCUNHO,
                    'nivel_acesso' => $dados['nivel_acesso'] ?? 'privada',
                    'classificacao_confidencialidade' => $dados['classificacao_confidencialidade'] ?? 'uso_interno',
                    'empresa_id' => $empresaId,
                    'local' => $dados['local'],
                    'data_inicio' => $dados['data_inicio'],
                    'data_fim' => $dados['data_fim']
                ];

                $id = AtaReuniao::create($ata)->id;
                $ataCriada = AtaReuniao::withoutGlobalScopes()->find($id);

                AtaReuniaoAcesso::withoutGlobalScopes()->updateOrCreate([
                    'empresa_id' => $empresaId,
                    'ata_reuniao_id' => $id,
                    'user_id' => auth()->id(),
                    'papel' => AtaReuniaoAcesso::PAPEL_PROPRIETARIO,
                ], [
                    'origem' => 'criacao',
                    'revogado_em' => null,
                ]);

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
                        'empresa_id' => $empresaId,
                        'ata_reuniao_id' => $id,
                        'titulo' => $a['titulo'] ?? Str::limit(strip_tags($a['acao'] ?? 'Pendência'), 120, ''),
                        'descricao' => $a['descricao'] ?? ($a['acao'] ?? null),
                        'acao' => $a['acao'],
                        'prazo' => $a['prazo'],
                        'continuo' => $a['continuo'],
                        'status' => AtaReuniaoAcao::STATUS_EM_ANDAMENTO,
                        'observacao' => $a['observacao'],
                        'responsavel' => $a['responsavel'],
                        'responsavel_id' => $a['responsavel_id'] ?? null,
                        'criado_por' => auth()->id(),
                        'email' => $a['email'],
                        'prioridade' => $a['prioridade'] ?? 'media',
                    ];
                    AtaReuniaoAcao::create($ac);

                    if (!empty($a['responsavel_id'])) {
                        $responsavel = User::where('empresa_id', $empresaId)->find($a['responsavel_id']);
                        if ($responsavel && $ataCriada) {
                            app(AtaReuniaoAccessService::class)->concederAcessoMinimoPendencia($ataCriada, $responsavel);
                        }
                    }
                }

                foreach ($dados['participantes'] as $p) {
                    $pa = [
                        'ata_reuniao_id' => $id,
                        'nome' => $p['nome'],
                        'user_id' => $p['user_id'] ?? null,
                        'funcao' => $p['funcao'],
                    ];
                    AtaReuniaoParticipante::create($pa);

                    if (!empty($p['user_id'])) {
                        AtaReuniaoAcesso::withoutGlobalScopes()->updateOrCreate([
                            'empresa_id' => $empresaId,
                            'ata_reuniao_id' => $id,
                            'user_id' => $p['user_id'],
                            'papel' => AtaReuniaoAcesso::PAPEL_PARTICIPANTE,
                        ], [
                            'origem' => 'participante',
                            'revogado_em' => null,
                        ]);
                    }
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
        $ata = AtaReuniao::where('id', $id)->with('Assuntos', 'Tipos', 'Acoes', 'Participantes', 'QuemCadastrou')->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canView(auth()->user(), $ata)) {
            abort(403, 'Sem permissão para acessar esta ata.');
        }

        return $ata;
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
        $this->authorize('administracao_atareuniao_update');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'local' => 'required',
                'data_inicio' => 'required',
                'data_fim' => 'required',
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

                $ata = AtaReuniao::where('id', $id)->firstOrFail();

                if (!app(AtaReuniaoAccessService::class)->canEdit(auth()->user(), $ata)) {
                    return response()->json(['msg' => 'Sem permissão para editar esta ata ou ata bloqueada para edição.'], 403);
                }

                $dadosAta = [
                    'local' => $dados['local'],
                    'centro_custo_id' => $dados['centro_custo_id'] ?? null,
                    'area_etiqueta_id' => $dados['area_etiqueta_id'] ?? null,
                    'titulo' => $dados['titulo'] ?? $ata->titulo,
                    'objetivo' => $dados['objetivo'] ?? $ata->objetivo,
                ];

                $ata->update($dadosAta);


                foreach ($dados['acoes'] as $a) {
                    if (empty($a['id'])) {
                        continue;
                    }

                    $acao = AtaReuniaoAcao::where('id', $a['id'])->first();

                    if (!$acao || (int) $acao->ata_reuniao_id !== (int) $ata->id) {
                        continue;
                    }

                    if (!empty($a['status'])) {
                        $acao->update(['status' => $a['status']]);
                    }
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
        $this->authorize('administracao_atareuniao');
        $porPagina = $request->get('porPagina');

        if(auth()->user()->can('administracao_atareuniao_privilegio_adm')) {
            $resultado = AtaReuniao::with('Assuntos', 'Tipos', 'Acoes', 'Participantes', 'QuemCadastrou');
        }else{
            $userId = auth()->id();
            $resultado = AtaReuniao::where(function ($query) use ($userId) {
                $query->where('quem_cadastrou', $userId)
                    ->orWhere('organizador_id', $userId)
                    ->orWhere('redator_id', $userId)
                    ->orWhereHas('Acessos', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)->whereNull('revogado_em');
                    })
                    ->orWhereHas('Participantes', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    })
                    ->orWhereHas('Acoes', function ($subQuery) use ($userId) {
                        $subQuery->where('responsavel_id', $userId);
                    });
            })->with('Assuntos', 'Tipos', 'Acoes', 'Participantes', 'QuemCadastrou');
        }

        // se tiver busca
        if ($request->filled('campoBusca')) {
            $resultado->where(function ($q) use ($request) {
                $q->where('codigo', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('titulo', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('local', 'like', '%' . $request->campoBusca . '%')
                    ->orWhereHas('Assuntos', function ($q) use ($request) {
                        $q->where('assunto', 'like', '%' . $request->campoBusca . '%');
                    });
            });
        }
        // se for um tipo Problema ou Anotação
        if ($request->filled('campoTipo')) {
            $resultado->where('status', $request->campoTipo);
        }

//      privilegio_gestor_area
//      privilegio_gestor_centro_custo

        $resultado = $resultado->orderByDesc('updated_at')->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'empresa_id' => auth()->user()->empresa_id,
            ]
        ], 200);

    }

    public function pdf($item)
    {

        $atareuniao = AtaReuniao::where('id', $item)->with('Assuntos', 'Tipos', 'Acoes', 'Participantes', 'QuemCadastrou', 'Aprovacoes.Aprovador')->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canView(auth()->user(), $atareuniao)) {
            abort(403, 'Sem permissão para acessar esta ata.');
        }

        //dd($atareuniao);

        $pdf = PDF::loadView('pdf.administracao.atareuniao.atareuniao', compact('atareuniao'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('ata_de_reuniao_'.(new DataHora())->nomeUnico() . ".pdf");
    }

    public function solicitarAprovacao(Request $request, int $id, AtaReuniaoAprovacaoService $service)
    {
        $this->authorize('administracao_atareuniao_update');

        $dados = $request->validate([
            'aprovador_ids' => ['required', 'array', 'min:1'],
            'aprovador_ids.*' => ['integer'],
        ]);

        $ata = AtaReuniao::where('id', $id)->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canEdit(auth()->user(), $ata)) {
            return response()->json(['msg' => 'Sem permissão para solicitar aprovação desta ata.'], 403);
        }

        $total = $service->solicitar($ata, auth()->user(), $dados['aprovador_ids']);

        return response()->json(['total_aprovadores' => $total], 201);
    }

    public function decidirAprovacao(Request $request, int $id, AtaReuniaoAprovacaoService $service)
    {
        $dados = $request->validate([
            'decisao' => ['required', 'in:aprovado,aprovado_com_ressalva,ajustes_solicitados,rejeitado'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        $ata = AtaReuniao::where('id', $id)->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canView(auth()->user(), $ata)) {
            return response()->json(['msg' => 'Sem permissão para aprovar esta ata.'], 403);
        }

        $aprovacao = $service->decidir($ata, auth()->user(), $dados['decisao'], $dados['comentario'] ?? null);

        return response()->json(['aprovacao' => $aprovacao->fresh()], 200);
    }

    public function compartilharExterno(Request $request, int $id, AtaReuniaoCompartilhamentoService $service)
    {
        $this->authorize('administracao_atareuniao_update');

        $dados = $request->validate([
            'email' => ['nullable', 'email'],
            'nome' => ['nullable', 'string', 'max:255'],
            'escopo' => ['nullable', 'in:leitura'],
        ]);

        $ata = AtaReuniao::where('id', $id)->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canEdit(auth()->user(), $ata)) {
            return response()->json(['msg' => 'Sem permissão para compartilhar esta ata.'], 403);
        }

        return response()->json($service->criarLink(
            $ata,
            auth()->user(),
            $dados['email'] ?? null,
            $dados['nome'] ?? null,
            $dados['escopo'] ?? 'leitura'
        ), 201);
    }

    public function revogarCompartilhamento(int $compartilhamentoId, AtaReuniaoCompartilhamentoService $service)
    {
        $this->authorize('administracao_atareuniao_update');

        $compartilhamento = AtaReuniaoCompartilhamentoExterno::where('id', $compartilhamentoId)->firstOrFail();
        $ata = AtaReuniao::where('id', $compartilhamento->ata_reuniao_id)->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canEdit(auth()->user(), $ata)) {
            return response()->json(['msg' => 'Sem permissão para revogar este compartilhamento.'], 403);
        }

        $service->revogar($compartilhamento, auth()->user());

        return response()->json([], 204);
    }

    public function externo(string $token, AtaReuniaoCompartilhamentoService $service)
    {
        $compartilhamento = $service->resolver($token);

        if (!$compartilhamento) {
            abort(403, 'Link expirado, revogado ou inválido.');
        }

        $atareuniao = AtaReuniao::withoutGlobalScopes()
            ->where('empresa_id', $compartilhamento->empresa_id)
            ->where('id', $compartilhamento->ata_reuniao_id)
            ->with('Assuntos', 'Tipos', 'Acoes', 'Participantes', 'QuemCadastrou', 'Aprovacoes')
            ->firstOrFail();

        $service->registrarAcesso($compartilhamento);

        return view('g.administracao.atareuniao.externo', compact('atareuniao', 'compartilhamento'));
    }

    public function minhasAtas(Request $request)
    {
        $this->authorize('administracao_atareuniao');

        $userId = auth()->id();
        $porPagina = (int) $request->get('porPagina', 20);

        $atas = AtaReuniao::query()
            ->select(['id', 'codigo', 'titulo', 'status', 'local', 'data_inicio', 'data_fim', 'quem_cadastrou', 'organizador_id', 'redator_id', 'updated_at'])
            ->where(function ($query) use ($userId) {
                $query->where('quem_cadastrou', $userId)
                    ->orWhere('organizador_id', $userId)
                    ->orWhere('redator_id', $userId)
                    ->orWhereHas('Acessos', function ($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId)->whereNull('revogado_em');
                    })
                    ->orWhereHas('Aprovacoes', function ($subQuery) use ($userId) {
                        $subQuery->where('aprovador_id', $userId)->where('status', 'pendente');
                    })
                    ->orWhereHas('Acoes', function ($subQuery) use ($userId) {
                        $subQuery->where('responsavel_id', $userId);
                    });
            })
            ->withCount([
                'Aprovacoes as aprovacoes_pendentes_count' => function ($query) use ($userId) {
                    $query->where('aprovador_id', $userId)->where('status', 'pendente');
                },
                'Acoes as pendencias_responsavel_count' => function ($query) use ($userId) {
                    $query->where('responsavel_id', $userId)->whereNotIn('status', ['concluido', 'concluida', 'cancelada', 'cancelado']);
                },
            ])
            ->orderByDesc('updated_at')
            ->paginate($porPagina);

        return response()->json($atas);
    }

    public function minhasPendencias(Request $request)
    {
        $this->authorize('administracao_atareuniao');

        $porPagina = (int) $request->get('porPagina', 20);

        $pendencias = DB::table('ata_reuniao_acaos as a')
            ->join('ata_reuniaos as ata', 'ata.id', '=', 'a.ata_reuniao_id')
            ->where('ata.empresa_id', auth()->user()->empresa_id)
            ->whereNull('ata.deleted_at')
            ->whereNull('a.deleted_at')
            ->where('a.responsavel_id', auth()->id())
            ->select([
                'a.id',
                'a.ata_reuniao_id',
                'a.titulo',
                'a.acao',
                'a.descricao',
                'a.prazo',
                'a.status',
                'a.prioridade',
                'a.percentual_conclusao',
                'ata.codigo as ata_codigo',
                'ata.titulo as ata_titulo',
            ])
            ->orderByRaw("CASE WHEN a.status = 'atrasada' THEN 0 ELSE 1 END")
            ->orderBy('a.prazo')
            ->paginate($porPagina);

        return response()->json($pendencias);
    }

    public function dashboardResumo()
    {
        $this->authorize('administracao_atareuniao');

        $empresaId = auth()->user()->empresa_id;

        $atas = DB::table('ata_reuniaos')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('rascunho', 'em_elaboracao') THEN 1 ELSE 0 END) as em_elaboracao")
            ->selectRaw("SUM(CASE WHEN status = 'aguardando_aprovacao' THEN 1 ELSE 0 END) as aguardando_aprovacao")
            ->selectRaw("SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) as aprovadas")
            ->first();

        $pendencias = DB::table('ata_reuniao_acaos as a')
            ->join('ata_reuniaos as ata', 'ata.id', '=', 'a.ata_reuniao_id')
            ->where('ata.empresa_id', $empresaId)
            ->whereNull('ata.deleted_at')
            ->whereNull('a.deleted_at')
            ->selectRaw("SUM(CASE WHEN a.status NOT IN ('concluido', 'concluida', 'cancelado', 'cancelada') THEN 1 ELSE 0 END) as abertas")
            ->selectRaw("SUM(CASE WHEN a.status = 'atrasada' THEN 1 ELSE 0 END) as atrasadas")
            ->selectRaw("SUM(CASE WHEN a.prazo BETWEEN ? AND ? AND a.status NOT IN ('concluido', 'concluida', 'cancelado', 'cancelada') THEN 1 ELSE 0 END) as proximas", [now()->toDateString(), now()->addDays(2)->toDateString()])
            ->selectRaw("SUM(CASE WHEN a.status IN ('concluido', 'concluida') THEN 1 ELSE 0 END) as concluidas")
            ->first();

        return response()->json([
            'atas' => $atas,
            'pendencias' => $pendencias,
        ]);
    }

    public function notificacaoConfig()
    {
        $this->authorize('administracao_atareuniao_privilegio_adm');

        return response()->json(AtaReuniaoNotificacaoConfig::obterOuPadrao((int) auth()->user()->empresa_id));
    }

    public function salvarNotificacaoConfig(Request $request)
    {
        $this->authorize('administracao_atareuniao_privilegio_adm');

        $dados = $request->validate([
            'usar_dias_uteis' => ['required', 'boolean'],
            'dias_antecedencia' => ['required', 'integer', 'min:0', 'max:30'],
            'horario_envio' => ['required', 'date_format:H:i'],
            'timezone' => ['required', 'string', 'max:80'],
            'incluir_gestor_copia' => ['required', 'boolean'],
            'reenviar_no_vencimento' => ['required', 'boolean'],
            'cobrar_apos_atraso' => ['required', 'boolean'],
            'dias_escalonamento' => ['nullable', 'array'],
            'dias_escalonamento.*' => ['integer', 'min:1', 'max:365'],
        ]);

        $config = AtaReuniaoNotificacaoConfig::obterOuPadrao((int) auth()->user()->empresa_id);
        $config->update([
            ...$dados,
            'horario_envio' => $dados['horario_envio'] . ':00',
            'dias_escalonamento' => collect($dados['dias_escalonamento'] ?? [1, 3, 5, 10])
                ->map(fn ($dia) => (int) $dia)
                ->filter(fn ($dia) => $dia > 0)
                ->unique()
                ->values()
                ->all(),
        ]);

        return response()->json($config->fresh());
    }

    public function relatorios(Request $request)
    {
        $this->authorize('administracao_atareuniao');

        $empresaId = auth()->user()->empresa_id;
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');

        $atasPorStatus = DB::table('ata_reuniaos')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->when($dataInicio, fn ($query) => $query->whereDate('data_inicio', '>=', $dataInicio))
            ->when($dataFim, fn ($query) => $query->whereDate('data_inicio', '<=', $dataFim))
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $pendenciasPorStatus = DB::table('ata_reuniao_acaos as a')
            ->join('ata_reuniaos as ata', 'ata.id', '=', 'a.ata_reuniao_id')
            ->where('ata.empresa_id', $empresaId)
            ->whereNull('ata.deleted_at')
            ->whereNull('a.deleted_at')
            ->when($dataInicio, fn ($query) => $query->whereDate('a.prazo', '>=', $dataInicio))
            ->when($dataFim, fn ($query) => $query->whereDate('a.prazo', '<=', $dataFim))
            ->select('a.status', DB::raw('COUNT(*) as total'))
            ->groupBy('a.status')
            ->orderBy('a.status')
            ->get();

        $notificacoes = AtaReuniaoNotificacao::where('empresa_id', $empresaId)
            ->when($dataInicio, fn ($query) => $query->whereDate('created_at', '>=', $dataInicio))
            ->when($dataFim, fn ($query) => $query->whereDate('created_at', '<=', $dataFim))
            ->select('tipo', 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo', 'status')
            ->orderBy('tipo')
            ->get();

        return response()->json([
            'atas_por_status' => $atasPorStatus,
            'pendencias_por_status' => $pendenciasPorStatus,
            'notificacoes' => $notificacoes,
        ]);
    }

    public function exportarRelatorio(Request $request)
    {
        $this->authorize('administracao_atareuniao');

        $dados = $request->validate([
            'formato' => ['required', 'in:csv,xlsx,pdf'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date'],
        ]);

        ExportAtaReuniaoRelatorioJob::dispatch(auth()->id(), $dados['formato'], [
            'data_inicio' => $dados['data_inicio'] ?? null,
            'data_fim' => $dados['data_fim'] ?? null,
        ]);

        return response()->json(['msg' => 'Exportação enfileirada. Você será notificado ao concluir.'], 202);
    }

    public function comentarios(int $id)
    {
        $this->authorize('administracao_atareuniao');

        $ata = AtaReuniao::where('id', $id)->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canView(auth()->user(), $ata)) {
            return response()->json(['msg' => 'Sem permissão para acessar os comentários desta ata.'], 403);
        }

        return response()->json(AtaReuniaoComentario::where('ata_reuniao_id', $ata->id)
            ->with('Autor:id,nome,login')
            ->orderBy('created_at')
            ->get());
    }

    public function comentar(Request $request, int $id)
    {
        $this->authorize('administracao_atareuniao');

        $dados = $request->validate([
            'texto' => ['required', 'string', 'max:5000'],
            'ata_reuniao_acao_id' => ['nullable', 'integer'],
            'mencoes' => ['nullable', 'array'],
        ]);

        $ata = AtaReuniao::where('id', $id)->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canView(auth()->user(), $ata)) {
            return response()->json(['msg' => 'Sem permissão para comentar nesta ata.'], 403);
        }

        $comentario = AtaReuniaoComentario::create([
            'empresa_id' => auth()->user()->empresa_id,
            'ata_reuniao_id' => $ata->id,
            'ata_reuniao_acao_id' => $dados['ata_reuniao_acao_id'] ?? null,
            'autor_id' => auth()->id(),
            'texto' => $dados['texto'],
            'mencoes' => $dados['mencoes'] ?? null,
        ]);

        return response()->json($comentario->load('Autor:id,nome,login'), 201);
    }

    public function anexos(int $id)
    {
        $this->authorize('administracao_atareuniao');

        $ata = AtaReuniao::where('id', $id)->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canView(auth()->user(), $ata)) {
            return response()->json(['msg' => 'Sem permissão para acessar os anexos desta ata.'], 403);
        }

        return response()->json(AtaReuniaoAnexo::where('ata_reuniao_id', $ata->id)
            ->orderByDesc('created_at')
            ->get());
    }

    public function registrarAnexo(Request $request, int $id)
    {
        $this->authorize('administracao_atareuniao');

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'max:120'],
            'tamanho' => ['nullable', 'integer', 'min:0'],
            'link' => ['nullable', 'url', 'max:1000'],
            'arquivo_id' => ['nullable', 'integer'],
            'ata_reuniao_acao_id' => ['nullable', 'integer'],
            'secao' => ['nullable', 'string', 'max:60'],
        ]);

        $ata = AtaReuniao::where('id', $id)->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canView(auth()->user(), $ata)) {
            return response()->json(['msg' => 'Sem permissão para anexar nesta ata.'], 403);
        }

        $anexo = AtaReuniaoAnexo::create([
            'empresa_id' => auth()->user()->empresa_id,
            'ata_reuniao_id' => $ata->id,
            'ata_reuniao_acao_id' => $dados['ata_reuniao_acao_id'] ?? null,
            'arquivo_id' => $dados['arquivo_id'] ?? null,
            'usuario_id' => auth()->id(),
            'nome' => $dados['nome'],
            'tipo' => $dados['tipo'] ?? null,
            'tamanho' => $dados['tamanho'] ?? null,
            'link' => $dados['link'] ?? null,
            'secao' => $dados['secao'] ?? 'ata',
        ]);

        return response()->json($anexo, 201);
    }

    public function confirmarCiencia(Request $request, int $id)
    {
        $this->authorize('administracao_atareuniao');

        $dados = $request->validate([
            'tipo' => ['nullable', 'in:leitura,ciencia'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        $ata = AtaReuniao::where('id', $id)->firstOrFail();

        if (!app(AtaReuniaoAccessService::class)->canView(auth()->user(), $ata)) {
            return response()->json(['msg' => 'Sem permissão para confirmar ciência desta ata.'], 403);
        }

        $ciencia = AtaReuniaoCiencia::withoutGlobalScopes()->updateOrCreate([
            'empresa_id' => auth()->user()->empresa_id,
            'ata_reuniao_id' => $ata->id,
            'user_id' => auth()->id(),
            'tipo' => $dados['tipo'] ?? 'ciencia',
        ], [
            'ip' => $request->ip(),
            'comentario' => $dados['comentario'] ?? null,
            'confirmado_em' => now(),
        ]);

        return response()->json($ciencia, 201);
    }
}
