<?php

namespace App\Http\Controllers;

use App\Domain\Recrutamento\GateEtapaSeletivo;
use App\Domain\Recrutamento\WorkflowSeletivoPadrao;
use App\Domain\Recrutamento\WorkflowSeletivoService;
use App\Models\CartaOferta;
use App\Models\CartaOfertaAceite;
use App\Models\FeedbackCurriculo;
use App\Models\NotificacaoWhats;
use App\Models\SimuladoCandidato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use MasterTag\DataHora;

class CurriculosSelecionadosController extends Controller
{
    private const CURRICULO_COLS = 'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,disponibilidade_sabado,disponibilidade_domingo,uf_vaga';

    /** @var array<string, \Illuminate\Support\Collection> */
    private array $cacheEtapasWorkflow = [];

    public function __construct(
        private readonly WorkflowSeletivoService $workflow,
        private readonly GateEtapaSeletivo $gate
    ) {
    }

    public function index()
    {
        return view('g.curriculos.selecionados.index', [
            'urls_workflow' => $this->urlsModulosWorkflow(),
        ]);
    }

    public function getCurriculo(FeedbackCurriculo $feedback)
    {
        $feedback->load(
            self::CURRICULO_COLS,
            'Curriculo.Formacao',
            'Curriculo.CartaOferta.aceite',
            'VagaAberta.VagaSelecionada',
            'VagaAberta.Vaga',
            'VagaAberta.Municipio',
            'VagaSelecionada:id,nome',
            'TelPrincipal',
            'Cliente',
            'Simulados.SimuladoVaga.Simulado',
            'EtapaStatus',
            'CartaOferta.aceite',
            'parecerRh',
            'parecerTecnica',
            'parecerRota'
        )
            ->load(['Vinculo' => function ($q) use ($feedback) {
                $q->whereVagaId($feedback->vaga_id);
            }]);

        $carta = $feedback->CartaOferta ?: $feedback->Curriculo?->CartaOferta;
        $feedback->setAttribute('carta_resumo', $this->montarResumoCarta($carta));
        $feedback->setAttribute('url_carta_oferta', url('/g/admissao/documentos/carta-oferta'));
        $feedback->setAttribute('workflow_progresso', $this->progressoDoFeedback($feedback));

        return $feedback;
    }

    public function modificaStatus(Request $request)
    {
        if ($request->simulado_id > 0) {
            $simulado = SimuladoCandidato::find($request->simulado_id);

            $simulado->status = $request->status;
            $simulado->save();
            $simulado->refresh();

            return response()->json(['ativo' => $simulado->status], 201);
        }

        return response()->json([], 201);
    }

    public function atualizar(Request $request)
    {
        // Paridade SGI: tela Selecionados lista quem tem interesse=true (inclui sim e standby).
        // Não filtrar por selecionado='sim' — no SGI a base é só whereHas FeedBack.interesse.
        $resultado = FeedbackCurriculo::whereInteresse(true)
            ->with(
                self::CURRICULO_COLS,
                'Cliente:id,razao_social,cnpj',
                'Simulados.SimuladoVaga.Simulado',
                'Curriculo.Formacao',
                'Curriculo.CartaOferta.aceite',
                'VagaAberta.VagaSelecionada',
                'VagaAberta.Vaga',
                'VagaAberta.Municipio',
                'VagaSelecionada:id,nome',
                'EtapaStatus',
                'TelPrincipal',
                'CartaOferta.aceite',
                'parecerTecnica',
                'parecerRota'
            );

        $this->aplicarFiltroPeriodo($resultado, $request);

        if ($request->filled('campoBusca')) {
            $busca = $request->campoBusca;
            $resultado->where(function ($q) use ($busca) {
                $q->where('id', $busca)
                    ->orWhereHas('Curriculo', function ($cq) use ($busca) {
                        $cq->where('nome', 'like', '%' . $busca . '%')
                            ->orWhere('id', $busca);
                    });
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->whereCpf($request->campoCPF);
            });
        }

        if ($request->filled('campoCliente')) {
            $resultado->whereClienteId($request->campoCliente);
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->where('uf_vaga', $request->campoUf);
            });
        }

        if ($request->filled('campoPcd')) {
            $pcd = filter_var($request->campoPcd, FILTER_VALIDATE_BOOLEAN);
            $resultado->whereHas('Curriculo', function ($q) use ($pcd) {
                $q->wherePcd($pcd);
            });
        }

        if ($request->filled('campoDisponibilidadeSabado')) {
            $valor = filter_var($request->campoDisponibilidadeSabado, FILTER_VALIDATE_BOOLEAN);
            $resultado->whereHas('Curriculo', function ($q) use ($valor) {
                $q->whereDisponibilidadeSabado($valor);
            });
        }

        if ($request->filled('campoDisponibilidadeDomingo')) {
            $valor = filter_var($request->campoDisponibilidadeDomingo, FILTER_VALIDATE_BOOLEAN);
            $resultado->whereHas('Curriculo', function ($q) use ($valor) {
                $q->whereDisponibilidadeDomingo($valor);
            });
        }

        $this->aplicarFiltroCartaOferta($resultado, $request);

        if ($request->filled('campoProvas')) {
            if ($request->campoProvas == 'sim') {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->whereFinalizado(true);
                });
            }
            if ($request->campoProvas == 'nao') {
                $resultado->doesntHave('Simulados');
            }
            if ($request->campoProvas == 'andamento') {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->whereFinalizado(false);
                });
            }
        }

        if ($request->filled('campoNota')) {
            if ($request->campoNota == 0 || $request->campoNota === '0') {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->whereAcertos(0);
                });
            }
            if ($request->campoNota == '1-5') {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->where('acertos', '>=', 1)->where('acertos', '<=', 5);
                });
            }
            if ($request->campoNota == '5-7') {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->where('acertos', '>=', 5)->where('acertos', '<=', 7);
                });
            }
            if ($request->campoNota == '8-10') {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->where('acertos', '>=', 8)->where('acertos', '<=', 10);
                });
            }
        }

        if ($request->filled('campoStatus')) {
            if ($request->campoStatus != 'sem_status') {
                $resultado->whereStatus($request->campoStatus);
            }
            if ($request->campoStatus == 'sem_status') {
                $resultado->doesntHave('EtapaStatus');
            }
        }

        if ($request->filled('campoEtapa')) {
            $campoEtapa = $request->campoEtapa;
            $resultado->whereHas('EtapaStatus', function ($q) use ($campoEtapa) {
                $q->where(function ($qq) use ($campoEtapa) {
                    $qq->where('etapa', $campoEtapa);
                    if (is_numeric($campoEtapa)) {
                        $qq->orWhere('passo_id', (int) $campoEtapa);
                    }
                });
            });
        }

        if ($request->filled('campoPendenciaWorkflow')) {
            $chavePendencia = (string) $request->campoPendenciaWorkflow;
            // Filtro leve por módulos conhecidos (evita varrer progresso em SQL)
            if ($chavePendencia === WorkflowSeletivoPadrao::CHAVE_CARTA) {
                $resultado->where(function ($q) {
                    $q->whereDoesntHave('CartaOferta', function ($cq) {
                        $cq->where('status', CartaOferta::STATUS_ACEITO_RH);
                    })->whereDoesntHave('Curriculo.CartaOferta', function ($cq) {
                        $cq->where('status', CartaOferta::STATUS_ACEITO_RH);
                    });
                });
            } elseif ($chavePendencia === WorkflowSeletivoPadrao::CHAVE_PREADMISSAO) {
                $resultado->doesntHave('FeedbackPreadmissao');
            } elseif ($chavePendencia === WorkflowSeletivoPadrao::CHAVE_TECNICA) {
                $resultado->doesntHave('parecerTecnica');
            } elseif ($chavePendencia === WorkflowSeletivoPadrao::CHAVE_ROTA) {
                $resultado->doesntHave('parecerRota');
            } elseif ($chavePendencia === WorkflowSeletivoPadrao::CHAVE_APTO) {
                $resultado->where(function ($q) {
                    $q->whereDoesntHave('EtapaStatus', function ($eq) {
                        $eq->where('status', 'classificado')
                            ->where(function ($qq) {
                                $qq->where('etapa', 'Apto para Admissao')
                                    ->orWhere('etapa', 'Apto para Admissão');
                            });
                    });
                });
            }
        }

        $resultado = $resultado->orderBy('created_at', 'desc')->paginate($request->pages ?: 20);

        $user = auth()->user();
        $etapasFiltro = $this->workflow->filtrarPorPermissao(
            $this->workflow->resolverEtapas((int) $user->empresa_id, null, true),
            $user
        );

        $itens = collect($resultado->items())->map(function (FeedbackCurriculo $item) {
            $carta = $item->CartaOferta ?: $item->Curriculo?->CartaOferta;
            $item->setAttribute('carta_resumo', $this->montarResumoCarta($carta));
            $item->setAttribute('data_selecao_br', $item->created_at
                ? (new DataHora($item->created_at))->dataHoraCompleta()
                : null);
            $item->setAttribute('curriculo_ctoken', $item->curriculo_id
                ? Crypt::encrypt($item->curriculo_id)
                : null);
            $item->setAttribute('prova_label', $this->rotuloProva($item));
            $item->setAttribute('workflow_progresso', $this->progressoDoFeedback($item));

            return $item;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens->values(),
                'usuario_cliente_id' => auth()->user()->cliente_id,
                'url_carta_oferta' => url('/g/admissao/documentos/carta-oferta'),
                'urls_workflow' => $this->urlsModulosWorkflow(),
                'etapas_filtro' => $etapasFiltro->map(fn ($e) => [
                    'id' => $e->id,
                    'chave' => $e->chave,
                    'nome' => $e->nome,
                    'tipo' => $e->tipo,
                    'is_apto_admissao' => (bool) $e->is_apto_admissao,
                ])->values(),
            ],
        ]);
    }

    public function proximaEtapa()
    {
    }

    public function emailProva()
    {
    }

    public function enviaNotificacao(Request $request)
    {
        $dados = $request->input();
        NotificacaoWhats::sendNotificacaoAptoAdmissao($dados, null);
    }

    private function aplicarFiltroPeriodo($resultado, Request $request): void
    {
        $filtroPeriodo = filter_var($request->input('filtroPeriodo'), FILTER_VALIDATE_BOOLEAN);
        if (!$filtroPeriodo) {
            return;
        }

        if ($request->filled('dataInicio') && $request->filled('dataFim')) {
            $dataInicio = new DataHora($request->dataInicio . ' 00:00:00');
            $dataFim = new DataHora($request->dataFim . ' 23:59:59');
            $resultado->where('created_at', '>=', $dataInicio->dataHoraInsert())
                ->where('created_at', '<=', $dataFim->dataHoraInsert());

            return;
        }

        if (!$request->filled('periodo') || !str_contains((string) $request->periodo, ' até ')) {
            return;
        }

        $periodo = explode(' até ', $request->periodo);
        $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
        $dataFim = new DataHora($periodo[1] . ' 23:59:59');
        $resultado->where('created_at', '>=', $dataInicio->dataHoraInsert())
            ->where('created_at', '<=', $dataFim->dataHoraInsert());
    }

    private function aplicarFiltroCartaOferta($resultado, Request $request): void
    {
        if (!$request->filled('campoCartaOferta')) {
            return;
        }

        $filtro = $request->campoCartaOferta;

        if ($filtro === 'Aguardando') {
            $resultado->where(function ($q) {
                $q->whereHas('CartaOferta', function ($cq) {
                    $cq->whereIn('status', [
                        CartaOferta::STATUS_PENDENTE_ANEXO,
                        CartaOferta::STATUS_AGUARDANDO_RH,
                    ])->whereDoesntHave('aceite');
                })->orWhereHas('Curriculo.CartaOferta', function ($cq) {
                    $cq->whereIn('status', [
                        CartaOferta::STATUS_PENDENTE_ANEXO,
                        CartaOferta::STATUS_AGUARDANDO_RH,
                    ])->whereDoesntHave('aceite');
                });
            });

            return;
        }

        if ($filtro === 'Sim') {
            $resultado->where(function ($q) {
                $q->whereHas('CartaOferta', function ($cq) {
                    $cq->where(function ($inner) {
                        $inner->where('status', CartaOferta::STATUS_ACEITO_RH)
                            ->orWhereHas('aceite', function ($aq) {
                                $aq->where('decisao', CartaOfertaAceite::DECISAO_ACEITO);
                            });
                    });
                })->orWhereHas('Curriculo.CartaOferta', function ($cq) {
                    $cq->where(function ($inner) {
                        $inner->where('status', CartaOferta::STATUS_ACEITO_RH)
                            ->orWhereHas('aceite', function ($aq) {
                                $aq->where('decisao', CartaOfertaAceite::DECISAO_ACEITO);
                            });
                    });
                });
            });

            return;
        }

        if ($filtro === 'Não') {
            $resultado->where(function ($q) {
                $q->whereHas('CartaOferta', function ($cq) {
                    $cq->where(function ($inner) {
                        $inner->whereIn('status', [
                            CartaOferta::STATUS_RECUSADO_RH,
                            CartaOferta::STATUS_RECUSADO_CANDIDATO,
                        ])->orWhereHas('aceite', function ($aq) {
                            $aq->where('decisao', CartaOfertaAceite::DECISAO_RECUSADO);
                        });
                    });
                })->orWhereHas('Curriculo.CartaOferta', function ($cq) {
                    $cq->where(function ($inner) {
                        $inner->whereIn('status', [
                            CartaOferta::STATUS_RECUSADO_RH,
                            CartaOferta::STATUS_RECUSADO_CANDIDATO,
                        ])->orWhereHas('aceite', function ($aq) {
                            $aq->where('decisao', CartaOfertaAceite::DECISAO_RECUSADO);
                        });
                    });
                });
            });
        }
    }

    private function montarResumoCarta(?CartaOferta $carta): ?array
    {
        if (!$carta) {
            return null;
        }

        $aceite = $carta->aceite;
        $chave = 'aguardando';
        $texto = $carta->status ?: 'Aguardando';

        if ($carta->status === CartaOferta::STATUS_ACEITO_RH
            || ($aceite && $aceite->decisao === CartaOfertaAceite::DECISAO_ACEITO)) {
            $chave = 'aceita';
            $texto = 'Aceita';
        } elseif (in_array($carta->status, [
            CartaOferta::STATUS_RECUSADO_RH,
            CartaOferta::STATUS_RECUSADO_CANDIDATO,
        ], true) || ($aceite && $aceite->decisao === CartaOfertaAceite::DECISAO_RECUSADO)) {
            $chave = 'recusada';
            $texto = 'Recusada';
        } elseif ($carta->status === CartaOferta::STATUS_EXPIRADO) {
            $chave = 'expirada';
            $texto = 'Expirada';
        }

        return [
            'id' => (int) $carta->id,
            'status' => (string) $carta->status,
            'chave' => $chave,
            'texto' => $texto,
            'envio_br' => $carta->created_at
                ? (new DataHora($carta->created_at))->dataHoraCompleta()
                : null,
            'resposta_br' => $aceite?->data_decisao_br,
            'decisao' => $aceite?->decisao,
            'expirado' => $carta->status === CartaOferta::STATUS_EXPIRADO,
        ];
    }

    private function rotuloProva(FeedbackCurriculo $item): string
    {
        $simulados = $item->Simulados;
        if (!$simulados || $simulados->isEmpty()) {
            return '-';
        }

        $emAndamento = $simulados->contains(function ($prova) {
            return !$prova->finalizado;
        });

        return $emAndamento ? 'Andamento' : 'SIM';
    }

    private function progressoDoFeedback(FeedbackCurriculo $feedback): array
    {
        $user = auth()->user();
        $empresaId = (int) ($feedback->empresa_id ?: $user->empresa_id);
        $vagaId = $feedback->vaga_id ? (int) $feedback->vaga_id : null;
        $cacheKey = $empresaId . ':' . ($vagaId ?: '0');

        if (!isset($this->cacheEtapasWorkflow[$cacheKey])) {
            $this->cacheEtapasWorkflow[$cacheKey] = $this->workflow->resolverEtapas($empresaId, $vagaId, true);
        }

        $todas = $this->cacheEtapasWorkflow[$cacheKey];
        $visiveis = $this->workflow->filtrarPorPermissao($todas, $user);

        return $this->gate->montarProgresso($feedback, $visiveis);
    }

    /**
     * @return array<string, string>
     */
    private function urlsModulosWorkflow(): array
    {
        return [
            WorkflowSeletivoPadrao::CHAVE_CARTA => route('g.admissao.documentos.cartaoferta.index'),
            WorkflowSeletivoPadrao::CHAVE_PREADMISSAO => route('g.admissao.preadm.index'),
            WorkflowSeletivoPadrao::CHAVE_TECNICA => route('g.entrevista.parecer_entrevista_tecnica.parecer-entrevista-tecnica.index'),
            WorkflowSeletivoPadrao::CHAVE_ROTA => route('g.entrevista.parecer_rota_transporte.parecer-rota.index'),
        ];
    }
}
