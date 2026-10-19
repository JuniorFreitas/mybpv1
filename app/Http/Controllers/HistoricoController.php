<?php

namespace App\Http\Controllers;

use App\Jobs\GerarTokenAvaliacaoNoventaDiasJob;
use App\Models\Admissao;
use App\Models\AvaliacaoNoventaDias;
use App\Models\AvaliacaoNoventaFeedback;
use App\Models\AvaliacaoNoventaFeedbackQuantidade;
use App\Models\AvaliacaoNoventaVencimento;
use App\Models\CentroCusto;
use App\Services\AvaliacaoNoventaService;
use App\Models\Cliente;
use App\Models\FeedbackCurriculo;
use App\Models\LogHistorico;
use App\Models\MedidaAdministrativa;
use App\Models\User;
use App\Models\Vaga;
use App\Services\Curriculo\Foto3x4ListagemCache;
use App\Services\Historico\MedidaAdministrativaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;
use PDF;

class HistoricoController extends Controller
{
    protected $avaliacaoNoventaService;

    public function __construct(AvaliacaoNoventaService $avaliacaoNoventaService)
    {
        $this->avaliacaoNoventaService = $avaliacaoNoventaService;
    }

    public function index()
    {
        return view('g.admissao.historico.index');
    }

    public function show(Request $request, $feedback)
    {
        $feedback_id = $feedback;

        $feedback = FeedbackCurriculo::whereId($feedback_id)
            ->with([
                'MedidasAdministrativas.Anexos',
                'MedidasAdministrativas.documentoParaAssinatura',
                'Curriculo:id,nome,cpf,email',
                'Curriculo.FotoTres' => function ($q) {
                    $q->select('arquivos.id', 'arquivos.file', 'arquivos.thumb', 'arquivos.disco', 'arquivos.imagem');
                },
                'Admissao:id,feedback_id,status,tipo_admissao,data_admissao,data_encerramento,matricula,cargo,funcao,centro_custo_id,filial,centro_custo_filial_id',
                'Empresa:id,nome,nome_fantasia',
                'VagaAberta.Vaga:id,nome'
            ])
            ->first();

        if ($feedback && $feedback->Curriculo) {
            $foto = $feedback->Curriculo->relationLoaded('FotoTres')
                ? $feedback->Curriculo->FotoTres->first()
                : null;
            $feedback->Curriculo->setAttribute(
                'foto_3x4_thumb',
                Foto3x4ListagemCache::paraListagem($foto)
            );
            $feedback->Curriculo->unsetRelation('FotoTres');
        }

        if ($feedback && $feedback->Admissao) {
            $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
            $centrosFlat = collect($cc['centros_custos'] ?? [])->collapse();
            $cc_colaborador = $centrosFlat->where('id', $feedback->Admissao->centro_custo_id)->first();
            $feedback->Admissao->emp_cnpj = $cc_colaborador['cnpj_format'] ?? null;
            $feedback->Admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'] ?? null;
            $feedback->Admissao->emp_centro_custo = $cc_colaborador['label'] ?? null;
            $feedback->Admissao->emp_tipo = !empty($cc_colaborador['matriz']) ? 'Matriz' : 'Filial';
        }

        $perguntas = AvaliacaoNoventaDias::get()->transform(function ($item) {
            $item->nota = '';
            return $item;
        });
        $tabelaNoventa = AvaliacaoNoventaFeedbackQuantidade::with('Feedback')->whereFeedbackId($feedback_id)->get();

        $avNoventaVencimento = Admissao::whereFeedbackId($feedback_id)->with('Feedback.AvaliacaoNoventaVencimento')->first();
        $restricao = new DataHora();
        $restricao->addDia(7);

        $itemAvaliacaoExperiencia = $this->avaliacaoNoventaService->getItemIndividualParaHistorico(
            (int) $feedback_id,
            (int) auth()->user()->empresa_id
        );

        Foto3x4ListagemCache::agendarAquecimentoAposResposta();

        return response()->json([
            'feedback' => $feedback,
            'causas' => MedidaAdministrativa::CAUSAS,
            'definicao' => MedidaAdministrativa::DEFINICAO,
            'tipos' => MedidaAdministrativa::TIPOS,
            'perguntas' => $perguntas,
            'tabelaNoventa' => $tabelaNoventa,
            'avNoventaVencimento' => $avNoventaVencimento,
            'item_avaliacao_experiencia' => $itemAvaliacaoExperiencia,
            'hoje' => (new DataHora())->dataCompleta(),
            'restricao' => $restricao->dataCompleta(),
            'privilegio_gestao_rh' => auth()->user()->can('privilegio_gestao_rh')
        ], 200);
    }

    /**
     * Gera link de Avaliação de Experiência para um feedback (individual).
     * Mesma regra do relatório: empresa, máximo 2 avaliações, enfileira job.
     */
    public function gerarLinkAvaliacaoExperiencia(Request $request, int $feedbackId)
    {
        $usuario = auth()->user();
        $empresaId = $usuario->empresa_id;

        $vencimento = AvaliacaoNoventaVencimento::query()
            ->where('feedback_id', $feedbackId)
            ->whereHas('FeedbackCurriculo', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->withCount('qntFeedback')
            ->first();

        if (!$vencimento) {
            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado'
            ], 404);
        }

        if (($vencimento->qnt_feedback_count ?? 0) >= AvaliacaoNoventaService::MAX_AVALIACOES_PERMITIDAS) {
            return response()->json([
                'success' => false,
                'message' => 'Avaliação já completa. Não é possível gerar novo link.'
            ], 422);
        }

        GerarTokenAvaliacaoNoventaDiasJob::dispatch($feedbackId, $empresaId);

        return response()->json([
            'success' => true,
            'message' => 'Geração de link enfileirada'
        ], 202);
    }

    public function atualizar(Request $request)
    {
        // Listagem histórico: só colunas/relações dos cards (payload enxuto, sem N+1)
        $resultado = FeedbackCurriculo::query()
            ->select([
                'feedback_curriculos.id',
                'feedback_curriculos.curriculo_id',
                'feedback_curriculos.empresa_id',
                'feedback_curriculos.created_at',
            ])
            ->whereHas('Admissao', function ($q) {
                $q->whereIn('status', [Admissao::STATUS_ADMISSAO_ADMITIDO, Admissao::STATUS_DEMITIDO]);
            })
            ->with([
                'Curriculo:id,nome,cpf',
                'Curriculo.FotoTres' => function ($q) {
                    $q->select('arquivos.id', 'arquivos.file', 'arquivos.thumb', 'arquivos.disco', 'arquivos.imagem');
                },
                'Admissao:id,feedback_id,status,tipo_admissao,data_admissao,data_encerramento,matricula,cargo,funcao,centro_custo_id,filial,centro_custo_filial_id',
            ]);

        $cliente = new Cliente();
        $filialOuMatriz = $cliente->findFiliarOuMatriz(auth()->user()->empresa_id);
        $temFilial = !isset($filialOuMatriz['matriz']);
        if (!$temFilial && isset($filialOuMatriz['cnpjkey'])) {
            $request->merge(['campoCnpj' => $filialOuMatriz['cnpjkey']]);
        }
        if ($request->filled('campoCnpj')) {
            $resultado->filtrarPorCnpjECentroCusto($request);
        }

        if ($request->campoDemitido) {
            $resultado->Demitidos();
        } else {
            $resultado->Admitidos();
        }
        if ($request->filled('campoBusca')) {
            $busca = trim((string) $request->campoBusca);
            $resultado->whereHas('Curriculo', function ($q) use ($busca) {
                $q->where('nome', 'like', '%' . $busca . '%');
            });
        }
        if ($request->filled('campoCargo')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->where('cargo', 'like', '%' . $request->campoCargo . '%');
            });
        }
        if ($request->filled('campoCPF')) {
            $cpf = preg_replace('/[^0-9]/', '', $request->campoCPF);
            $resultado->whereHas('Curriculo', function ($q) use ($cpf) {
                $q->whereRaw('REPLACE(REPLACE(REPLACE(cpf, ".", ""), "-", ""), "/", "") like ?', ['%' . $cpf . '%']);
            });
        }
        if ($request->filled('campoMatricula')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->where('matricula', 'like', '%' . $request->campoMatricula . '%');
            });
        }
        if ($request->filled('campoTipoAdmissao')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->where('tipo_admissao', $request->campoTipoAdmissao);
            });
        }
        if ($request->filled('campoFuncao')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->where('funcao', 'like', '%' . $request->campoFuncao . '%');
            });
        }
        $cargos = Vaga::whereAtivo(true)->orderBy('nome')->get(['id', 'nome']);
        $tiposAdmissao = Admissao::TODOS_TIPOS_ADMISSAO;

        $ordenacao = $request->filled('ordenacao') ? $request->ordenacao : 'created_at_desc';
        $campo = 'feedback_curriculos.created_at';
        $direcao = 'desc';
        switch ($ordenacao) {
            case 'created_at_asc':
                $campo = 'feedback_curriculos.created_at';
                $direcao = 'asc';
                break;
            case 'updated_at_desc':
                $ultimoLogSub = LogHistorico::selectRaw('feedback_id, MAX(id) as ultimo_log_id')->groupBy('feedback_id');
                $resultado = $resultado->leftJoinSub($ultimoLogSub, 'ultimo_log', 'feedback_curriculos.id', '=', 'ultimo_log.feedback_id')
                    ->orderByRaw('COALESCE(ultimo_log.ultimo_log_id, 0) DESC')
                    ->select([
                        'feedback_curriculos.id',
                        'feedback_curriculos.curriculo_id',
                        'feedback_curriculos.empresa_id',
                        'feedback_curriculos.created_at',
                    ]);
                break;
            case 'updated_at_asc':
                $ultimoLogSub = LogHistorico::selectRaw('feedback_id, MAX(id) as ultimo_log_id')->groupBy('feedback_id');
                $resultado = $resultado->leftJoinSub($ultimoLogSub, 'ultimo_log', 'feedback_curriculos.id', '=', 'ultimo_log.feedback_id')
                    ->orderByRaw('COALESCE(ultimo_log.ultimo_log_id, 0) ASC')
                    ->select([
                        'feedback_curriculos.id',
                        'feedback_curriculos.curriculo_id',
                        'feedback_curriculos.empresa_id',
                        'feedback_curriculos.created_at',
                    ]);
                break;
            case 'nome_asc':
                $resultado = $resultado->join('curriculos', 'feedback_curriculos.curriculo_id', '=', 'curriculos.id')
                    ->orderBy('curriculos.nome', 'asc')
                    ->select([
                        'feedback_curriculos.id',
                        'feedback_curriculos.curriculo_id',
                        'feedback_curriculos.empresa_id',
                        'feedback_curriculos.created_at',
                    ]);
                break;
            case 'nome_desc':
                $resultado = $resultado->join('curriculos', 'feedback_curriculos.curriculo_id', '=', 'curriculos.id')
                    ->orderBy('curriculos.nome', 'desc')
                    ->select([
                        'feedback_curriculos.id',
                        'feedback_curriculos.curriculo_id',
                        'feedback_curriculos.empresa_id',
                        'feedback_curriculos.created_at',
                    ]);
                break;
            case 'data_admissao_desc':
                $resultado = $resultado->join('admissoes', 'feedback_curriculos.id', '=', 'admissoes.feedback_id')
                    ->whereNull('admissoes.deleted_at')
                    ->orderBy('admissoes.data_admissao', 'desc')
                    ->select([
                        'feedback_curriculos.id',
                        'feedback_curriculos.curriculo_id',
                        'feedback_curriculos.empresa_id',
                        'feedback_curriculos.created_at',
                    ]);
                break;
            case 'data_admissao_asc':
                $resultado = $resultado->join('admissoes', 'feedback_curriculos.id', '=', 'admissoes.feedback_id')
                    ->whereNull('admissoes.deleted_at')
                    ->orderBy('admissoes.data_admissao', 'asc')
                    ->select([
                        'feedback_curriculos.id',
                        'feedback_curriculos.curriculo_id',
                        'feedback_curriculos.empresa_id',
                        'feedback_curriculos.created_at',
                    ]);
                break;
            case 'cargo_asc':
                $resultado = $resultado->join('admissoes', 'feedback_curriculos.id', '=', 'admissoes.feedback_id')
                    ->whereNull('admissoes.deleted_at')
                    ->orderBy('admissoes.cargo', 'asc')
                    ->select([
                        'feedback_curriculos.id',
                        'feedback_curriculos.curriculo_id',
                        'feedback_curriculos.empresa_id',
                        'feedback_curriculos.created_at',
                    ]);
                break;
            case 'cargo_desc':
                $resultado = $resultado->join('admissoes', 'feedback_curriculos.id', '=', 'admissoes.feedback_id')
                    ->whereNull('admissoes.deleted_at')
                    ->orderBy('admissoes.cargo', 'desc')
                    ->select([
                        'feedback_curriculos.id',
                        'feedback_curriculos.curriculo_id',
                        'feedback_curriculos.empresa_id',
                        'feedback_curriculos.created_at',
                    ]);
                break;
            default:
                break;
        }
        if (! in_array($ordenacao, ['nome_asc', 'nome_desc', 'data_admissao_desc', 'data_admissao_asc', 'cargo_asc', 'cargo_desc', 'updated_at_desc', 'updated_at_asc'], true)) {
            $resultado = $resultado->orderBy($campo, $direcao);
        }
        $resultado = $resultado->paginate($request->pages);
        $permissoes = [
            'dossie' => auth()->user()->can('admissao_historico_aba_dossie'),
            'medida_administrativa' => auth()->user()->can('admissao_historico_aba_medidas_administrativas'),
            'feedback' => auth()->user()->can('admissao_historico_aba_feedback'),
            'avaliacao_noventa_dias' => auth()->user()->can('admissao_historico_aba_avaliacao_noventa_dias'),
            'avaliacao_anual' => auth()->user()->can('admissao_historico_aba_avaliacao_anual'),
            'ferias' => auth()->user()->can('admissao_historico_aba_ferias'),
            'promocao' => auth()->user()->can('admissao_historico_aba_promocao'),
            'metas' => auth()->user()->can('admissao_historico_aba_metas'),
            'beneficio' => auth()->user()->can('cadastro_beneficio'),
            'cih' => auth()->user()->can('admissao_cih'),
            'afastamento' => auth()->user()->can('admissao_historico_aba_afastamento'),
            'filtrar_demitido' => auth()->user()->can('admissao_historico_filtrar_demitido'),
            'logs' => auth()->user()->can('admissao_historico_aba_log'),
            'privilegio_gestao_rh' => auth()->user()->can('privilegio_gestao_rh'),
        ];

        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        $centrosFlat = collect($cc['centros_custos'] ?? [])->collapse();
        $paginaItens = collect($resultado->items());
        $feedbackIds = $paginaItens->pluck('id')->filter()->values()->all();

        $ultimasAtualizacoes = [];
        if ($feedbackIds !== []) {
            $maxLogIds = LogHistorico::query()
                ->selectRaw('feedback_id, MAX(id) as max_id')
                ->whereIn('feedback_id', $feedbackIds)
                ->groupBy('feedback_id')
                ->pluck('max_id', 'feedback_id');

            if ($maxLogIds->isNotEmpty()) {
                $logs = LogHistorico::query()
                    ->whereIn('id', $maxLogIds->values()->all())
                    ->get(['id', 'feedback_id', 'data']);
                foreach ($logs as $log) {
                    $ultimasAtualizacoes[(int) $log->feedback_id] = $log->data ?: '';
                }
            }
        }

        $itens = $paginaItens->transform(function ($item) use ($centrosFlat, $ultimasAtualizacoes) {
            // Igual processo: não usar setVisible no pai (quebra relações Curriculo/Admissao)
            $item->setAppends([]);
            $item->makeHidden(['empresa_id', 'created_at']);
            // CIH na modal precisa do token (append foi desligado no payload enxuto)
            $item->setAttribute('fc_token', \Crypt::encrypt($item->id));
            $item->ultima_atualizacao = $ultimasAtualizacoes[(int) $item->id] ?? '';

            if ($item->Curriculo) {
                $foto = $item->Curriculo->relationLoaded('FotoTres')
                    ? $item->Curriculo->FotoTres->first()
                    : null;
                $item->Curriculo->setAppends([]);
                $item->Curriculo->setAttribute(
                    'foto_3x4_thumb',
                    Foto3x4ListagemCache::paraListagem($foto)
                );
                $item->Curriculo->unsetRelation('FotoTres');
                $item->Curriculo->setVisible(['nome', 'cpf', 'foto_3x4_thumb']);
            }

            if ($item->Admissao) {
                // Mesma regra do processo (AdmissaoController@atualizar)
                $cc_colaborador = $centrosFlat->where('id', $item->Admissao->centro_custo_id)->first();
                $item->Admissao->emp_cnpj = null;
                $item->Admissao->emp_nome_fantasia = null;
                $item->Admissao->emp_centro_custo = null;
                $item->Admissao->emp_tipo = null;

                if ($cc_colaborador) {
                    $item->Admissao->emp_cnpj = $cc_colaborador['cnpj_format'] ?? null;
                    $item->Admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'] ?? null;
                    $item->Admissao->emp_centro_custo = $cc_colaborador['label'] ?? null;
                    $item->Admissao->emp_tipo = !empty($cc_colaborador['matriz']) ? 'Matriz' : 'Filial';
                }

                $item->Admissao->setVisible([
                    'status',
                    'tipo_admissao',
                    'data_admissao',
                    'data_encerramento',
                    'matricula',
                    'cargo',
                    'funcao',
                    'centro_custo_id',
                    'emp_cnpj',
                    'emp_nome_fantasia',
                    'emp_centro_custo',
                    'emp_tipo',
                ]);
            }

            return $item;
        });
        $listaCentrosCusto = [];
        if (!$temFilial && isset($filialOuMatriz['cnpjkey'], $cc['centros_custos'][$filialOuMatriz['cnpjkey']])) {
            $listaCentrosCusto = $cc['centros_custos'][$filialOuMatriz['cnpjkey']]->values()->toArray();
        }

        $listaCnpjs = isset($cc['cnpjs']) ? $cc['cnpjs']->toArray() : [];
        $listaCentrosPorCnpj = [];
        if (isset($cc['centros_custos'])) {
            foreach ($cc['centros_custos'] as $cnpjKey => $coll) {
                $listaCentrosPorCnpj[$cnpjKey] = $coll->values()->toArray();
            }
        }

        Foto3x4ListagemCache::agendarAquecimentoAposResposta();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'cargos' => $cargos,
                'permissoes' => $permissoes,
                'lista_centros_custo' => $listaCentrosCusto,
                'lista_cnpjs' => $listaCnpjs,
                'lista_centros_por_cnpj' => $listaCentrosPorCnpj,
                'tem_filial' => $temFilial,
                'tipos_admissao' => $tiposAdmissao
            ]
        ]);
    }

    //************MEDIDAS ADMINISTRATIVAS**************//
    public function storeMedidas(Request $request, $feedback)
    {
        $service = new MedidaAdministrativaService();
        $dados = $request->input();

        $dadosValidados = $service->validarDados($dados);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            $service->storeMedidas($dados, $feedback);
            return response()->json([], 201);
        } catch (\Exception $e) {
            $usuario = User::find(auth()->id());
            $msg = "error STORE MEDIDAS ADMINISTRATIVAS: {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . ($usuario ? $usuario->nome : 'N/A');
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function updateMedidas(Request $request, $feedback)
    {
        $service = new MedidaAdministrativaService();
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
//            'data_inicio' => 'required',
//            'data_fim' => 'required',
//            'empresa_treinamento_id' => 'required',
//            'treinamento_sgi_id' => 'required',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            $service->updateMedidas($dados, $feedback);
            return response()->json([], 201);
        } catch (\Exception $e) {
            $usuario = User::find(auth()->id());
            $msg = "error UPDATE MEDIDAS ADMINISTRATIVAS: {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . ($usuario ? $usuario->nome : 'N/A');
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    //**************************FORMULARIO NOVENTA DIAS**************************//

    public function storeFormularioNoventaDias(Request $request)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'gestor_imediato' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar as Notas',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $total = AvaliacaoNoventaFeedbackQuantidade::where('feedback_id', $dados['feedback_id'])
                    ->sum('quantidade_avaliacao');
                $qntAvaliacao = $total > 0 ? intval($total) + 1 : 1;

                $info = [
                    'feedback_id' => $dados['feedback_id'],
                    'quantidade_avaliacao' => $qntAvaliacao,
                ];
                AvaliacaoNoventaFeedbackQuantidade::create($info);

                foreach ($dados['perguntas'] as $form) {
                    $formulario = [];
                    $formulario['feedback_id'] = $dados['feedback_id'];
                    $formulario['pergunta_id'] = $form['id'];
                    $formulario['gestor_id'] = auth()->user()->id;
                    $formulario['nota'] = $form['nota'];
                    $formulario['quantidade_avaliacao'] = $qntAvaliacao;
                    $formulario['gestor_imediato'] = $dados['gestor_imediato'];
                    $formulario['observacao'] = $dados['observacao'];
                    AvaliacaoNoventaFeedback::create($formulario);
                }
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE AVALIACAO NOVENTA FEEDBACK:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} " . User::find(auth()->id())->nome;
                \Log::debug($msg);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
                return response()->json(['msg' => $msg], 400);
            }
        }
    }

    public function formularioNoventaDiasPDF($quantidade_avaliacao, $feedback_id)
    {
        $avaliacaoPerguntas = AvaliacaoNoventaFeedback::whereFeedbackId($feedback_id)->whereQuantidadeAvaliacao($quantidade_avaliacao)->get();
        $avaliacao = AvaliacaoNoventaFeedbackQuantidade::whereFeedbackId($feedback_id)->whereQuantidadeAvaliacao($quantidade_avaliacao)->first();
        $pdf = PDF::loadView('pdf.admissao.historico.formularionoventadias.avaliacao', compact('avaliacao', 'avaliacaoPerguntas'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream((new DataHora())->nomeUnico() . ".pdf");

    }

}
