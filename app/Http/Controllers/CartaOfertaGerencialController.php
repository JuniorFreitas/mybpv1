<?php

namespace App\Http\Controllers;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappCurriculoTelefoneResolver;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Http\Controllers\Api\IntegraSgiMybpController;
use App\Http\Requests\CartaOfertaInternaStoreRequest;
use App\Jobs\AssinaturaDigital\JobProcessarEnvioAssinatura;
use App\Jobs\Entrevista\JobEnvioDocumento;
use App\Models\CartaOferta;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\DocumentoParaAssinatura;
use App\Models\Sistema;
use App\Services\CartaOferta\CartaOfertaInternaService;
use App\Services\CartaOferta\CartaOfertaListagemPresenter;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use MasterTag\DataHora;
use RuntimeException;

class CartaOfertaGerencialController extends Controller
{
    public function index()
    {
        return view('g.admissao.documentos.cartaoferta.index');
    }

    public function buscarCurriculos(Request $request)
    {
        $busca = trim((string) $request->query('busca', ''));
        if ($busca === '') {
            return response()->json([], 200);
        }

        $rows = (int) ($request->query('rows') ?: 20);

        return Curriculo::query()
            ->select(['id', 'nome', 'cpf', 'email'])
            ->where(function ($q) use ($busca) {
                $q->where('nome', 'like', '%' . $busca . '%')
                    ->orWhere('cpf', 'like', '%' . $busca . '%');
            })
            ->orderBy('nome')
            ->limit($rows)
            ->get()
            ->map(function ($item) {
                $cpf = $item->cpf ? " — {$item->cpf}" : '';
                $item->label = "{$item->nome}{$cpf}";
                $item->curriculo_id = $item->id;

                return $item;
            });
    }

    public function criar(CartaOfertaInternaStoreRequest $request, CartaOfertaInternaService $service)
    {
        try {
            $carta = $service->criar(
                $request->validated(),
                (int) auth()->user()->empresa_id,
                (int) auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Carta oferta criada e envio iniciado.',
                'carta' => $carta,
            ], 201);
        } catch (RuntimeException $e) {
            if ($e instanceof \Illuminate\Database\QueryException) {
                \Log::error('Erro ao criar carta oferta interna', [
                    'message' => $e->getMessage(),
                    'empresa_id' => auth()->user()->empresa_id ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível criar a carta oferta.',
                ], 500);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Erro ao criar carta oferta interna', [
                'message' => $e->getMessage(),
                'empresa_id' => auth()->user()->empresa_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível criar a carta oferta.',
            ], 500);
        }
    }

    /**
     * Envia Carta Oferta para assinatura digital (signatário = candidato).
     */
    public function enviarParaAssinatura(Request $request)
    {
        $request->validate([
            'carta_oferta_id' => 'required|integer',
        ]);

        $cartaOferta = CartaOferta::where('id', $request->carta_oferta_id)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with([
                'Curriculo:id,nome,email,cpf',
                'empresa:id,razao_social,nome_fantasia',
                'vagaAberta:id,vaga_id',
                'vagaAberta.Vaga:id,nome',
                'vagaProjeto:id,vaga_aberta_id',
                'vagaProjeto.VagaAberta:id,vaga_id',
                'vagaProjeto.VagaAberta.Vaga:id,nome',
            ])
            ->first();

        if (!$cartaOferta || !$cartaOferta->Curriculo) {
            return response()->json(['success' => false, 'message' => 'Carta oferta não encontrada.'], 404);
        }
        if ($cartaOferta->status !== CartaOferta::STATUS_PENDENTE_ANEXO) {
            return response()->json(['success' => false, 'message' => 'Esta carta oferta já foi enviada ou não está pendente de anexo.'], 422);
        }

        $nomeCargo = 'Cargo';
        if ($cartaOferta->vagaAberta && $cartaOferta->vagaAberta->Vaga) {
            $nomeCargo = $cartaOferta->vagaAberta->Vaga->nome;
        } elseif ($cartaOferta->vagaProjeto && $cartaOferta->vagaProjeto->VagaAberta && $cartaOferta->vagaProjeto->VagaAberta->Vaga) {
            $nomeCargo = $cartaOferta->vagaProjeto->VagaAberta->Vaga->nome;
        }

        $empresaId = auth()->user()->empresa_id;
        if (empty($cartaOferta->Curriculo->email)) {
            return response()->json(['success' => false, 'message' => 'O candidato não possui e-mail cadastrado.'], 422);
        }
        try {
            app(\App\Services\AssinaturaDigital\AssinaturaCotaService::class)->validarDisponibilidadeOrFail($empresaId);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        JobProcessarEnvioAssinatura::dispatch(
            JobProcessarEnvioAssinatura::TIPO_CARTA_OFERTA,
            $empresaId,
            auth()->id(),
            ['carta_oferta_id' => (int) $cartaOferta->id],
            []
        );

        return response()->json([
            'success' => true,
            'message' => 'Solicitação recebida. A carta oferta será processada e enviada para assinatura em background.',
        ], 202);
    }

    public function filtro(Request $request)
    {
        $filtroIntervalo = filter_var($request->filtroPeriodo, FILTER_VALIDATE_BOOLEAN);

        $query = CartaOferta::query()
            ->select([
                'curriculo_carta_oferta.id',
                'curriculo_carta_oferta.token',
                'curriculo_carta_oferta.public_id',
                'curriculo_carta_oferta.user_id',
                'curriculo_carta_oferta.curriculo_id',
                'curriculo_carta_oferta.vagas_abertas_id',
                'curriculo_carta_oferta.vaga_projeto_id',
                'curriculo_carta_oferta.arquivo_id',
                'curriculo_carta_oferta.status',
                'curriculo_carta_oferta.local',
                'curriculo_carta_oferta.setor',
                'curriculo_carta_oferta.salario',
                'curriculo_carta_oferta.data_inicio',
                'curriculo_carta_oferta.logs',
                'curriculo_carta_oferta.created_at',
                'curriculo_carta_oferta.updated_at',
            ])
            ->with([
                'curriculo:id,nome,cpf,email',
                'curriculo.TelPrincipal:id,numero,curriculo_id,principal',
                'user:id,nome',
                'vagaProjeto:id,projeto_id,vaga_aberta_id',
                'vagaProjeto.Projeto:id,nome',
                'vagaAberta:id,vaga_id,titulo',
                'vagaAberta.Vaga:id,nome',
                'vagaAberta.Projetos:id,vaga_aberta_id,projeto_id',
                'vagaAberta.Projetos.Projeto:id,nome',
                'anexo:id,file,disco,imagem,thumb',
            ])
            ->join('curriculos as c', 'curriculo_carta_oferta.curriculo_id', '=', 'c.id')
            ->whereNull('c.deleted_at');

        if ($request['order'] == 'nome') {
            $query->orderBy('c.nome');
        } else {
            $query->orderBy('curriculo_carta_oferta.updated_at', 'desc');
        }

        if (isset($request['status']) && $request['status'] != '') {
            $query->where('curriculo_carta_oferta.status', $request['status']);
        }

        if ($filtroIntervalo && $request->filled('periodo') && str_contains((string) $request->periodo, ' até ')) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
            $dataFim = new DataHora(trim($periodo[1]) . ' 23:59:59');
            $query->where('curriculo_carta_oferta.updated_at', '>=', $dataInicio->dataHoraInsert())
                ->where('curriculo_carta_oferta.updated_at', '<=', $dataFim->dataHoraInsert());
        }

        if (isset($request['campoBusca']) && $request['campoBusca'] != '') {
            $busca = $request['campoBusca'];
            $query->where(function ($q) use ($busca) {
                $q->where('c.nome', 'like', '%' . $busca . '%')
                    ->orWhere('c.cpf', 'like', '%' . $busca . '%');
            });
        }

        if (isset($request['projeto_id']) && $request['projeto_id'] != '') {
            $query->whereHas('vagaProjeto.Projeto', function ($q) use ($request) {
                $q->where('projetos.id', $request['projeto_id']);
            });
        }

        if (isset($request['curriculo_id']) && $request['curriculo_id'] != '') {
            $query->where('curriculo_carta_oferta.curriculo_id', $request['curriculo_id']);
        }

        if (isset($request['vaga_projeto_id']) && $request['vaga_projeto_id'] != '') {
            $query->where('curriculo_carta_oferta.vaga_projeto_id', $request['vaga_projeto_id']);
        }

        return $query;
    }

    public function responder(Request $request)
    {

        $cartaOferta = CartaOferta::where('token', $request['token'])->first();

        if (!$cartaOferta) {
            return response()->json(['message' => 'Carta de oferta não encontrada!'], 404);
        }

        if ($cartaOferta->status == CartaOferta::STATUS_EXPIRADO) {
            return response()->json(['message' => 'Carta de oferta expirada'], 422);
        }

        if ($cartaOferta->status == CartaOferta::STATUS_AGUARDANDO_RH) {

            try {
                \DB::beginTransaction();
                $logs = is_array($cartaOferta->logs) ? $cartaOferta->logs : [];

                $logs[] = [
                    'data' => (new DataHora())->dataHoraInsert(),
                    'mensagem' => "Carta de oferta {$request->resposta}",
                    'status' => $request->resposta,
                    'usuario' => auth()->user()->nome
                ];

                $cartaOferta->update([
                    'status' => $request->resposta,
                    'logs' => $logs
                ]);

                // Integração SGI apenas para cartas originadas no SGI
                if ($cartaOferta->local == CartaOferta::LOCAL_SGI && $request->resposta == CartaOferta::STATUS_ACEITO_RH) {
                    IntegraSgiMybpController::integra($request->integraMybp);
                }

                \DB::commit();

                if ($request->resposta == CartaOferta::STATUS_ACEITO_RH) {
                    try {
                        $cartaOferta->loadMissing('Curriculo');
                        $empresaApelido = Cliente::withoutGlobalScopes()
                            ->where('id', $cartaOferta->empresa_id)
                            ->value('apelido') ?: '';
                        $urlDocumentos = app(\App\Services\Preadmissao\DocumentosPreAdmissaoAcessoService::class)
                            ->urlPublica((int) $cartaOferta->empresa_id, (int) $cartaOferta->curriculo_id, $empresaApelido);

                        if ($cartaOferta->Curriculo && $cartaOferta->Curriculo->email) {
                            JobEnvioDocumento::dispatch([
                                'nome' => $cartaOferta->Curriculo->nome,
                                'email' => $cartaOferta->Curriculo->email,
                                'empresa_id' => $cartaOferta->empresa_id,
                                'url_documento' => $urlDocumentos,
                                'url_checklist' => route('download-checklist', ['empresa' => $empresaApelido]),
                            ]);
                        }

                        $telefone = app(WhatsappCurriculoTelefoneResolver::class)
                            ->resolverPrincipalWhatsapp((int) $cartaOferta->curriculo_id);

                        if (
                            $telefone
                            && app(WhatsappNotificationGateService::class)->podeEnviar(
                                TipoMensagemWhatsapp::CartaOfertaGerencial,
                                (int) $cartaOferta->empresa_id,
                            )
                        ) {
                            $dados['telefone'] = $telefone->sonumero;

                            $ambiente = env('AMBIENTE', 'local') == 'prod' ?: 'local';
                            if ($ambiente != 'prod') {
                                $zapTelAtivo = \DB::table("zap_numeros")->where("ativo", true)->first();
                                $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '559899023762';
                            }

                            $mensagemWhats = app(WhatsappMessageFactory::class)->render(
                                TipoMensagemWhatsapp::CartaOfertaGerencial,
                                (int) $cartaOferta->empresa_id,
                                [
                                    'nome_destinatario' => $cartaOferta->Curriculo->nome ?? '',
                                    'url_documentos' => $urlDocumentos,
                                ]
                            );

                            (new ZapNotificacao())->enviar([
                                'enviado_id' => auth()->id(),
                                'telefone' => preg_replace('/[^0-9]/', '', $dados['telefone']),
                                'mensagem' => $mensagemWhats,
                                '_whatsapp_meta' => ZapNotificacao::meta(
                                    TipoMensagemWhatsapp::CartaOfertaGerencial,
                                    (int) $cartaOferta->empresa_id,
                                ),
                                'anexo' => [
                                    'arquivo' => CartaOferta::checklistArquivo($empresaApelido),
                                    'tipo' => 'pdf',
                                ],
                            ]);
                        }
                    } catch (\Throwable $e) {
                        \Log::error('Carta oferta aceita, mas falhou notificação pós-aceite', [
                            'message' => $e->getMessage(),
                            'carta_id' => $cartaOferta->id,
                        ]);
                    }
                }

                return response()->json(['message' => 'Resposta computada com successo!'], 201);

            } catch (\Exception $e) {
                \DB::rollBack();
                $nomeUsuario = auth()->user()->nome ?? ('user:' . auth()->id());
                $msg = "Erro ao processar resposta da carta oferta | Usuario: {$nomeUsuario}";
                \Log::error('Erro ao responder carta oferta', [
                    'message' => $e->getMessage(),
                    'carta_token' => $request['token'] ?? null,
                ]);
                return response()->json(['message' => $msg], 400);
            }

        }

        return response()->json(['message' => 'Nao encontrado'], 404);

    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request['pages']);
        $itens = $resultado->getCollection();
        $cartaIds = $itens->pluck('id')->filter()->values()->all();
        $docsByCartaId = [];
        if (!empty($cartaIds)) {
            $docs = DocumentoParaAssinatura::withoutGlobalScopes()
                ->select(['id', 'token', 'status', 'arquivo_assinado_id', 'tipo_documento', 'documentable_id'])
                ->where('empresa_id', auth()->user()->empresa_id)
                ->where('documentable_type', CartaOferta::class)
                ->whereIn('documentable_id', $cartaIds)
                ->orderBy('id', 'desc')
                ->get();

            foreach ($docs as $doc) {
                if (!isset($docsByCartaId[$doc->documentable_id])) {
                    $docsByCartaId[$doc->documentable_id] = [
                        'id' => $doc->id,
                        'token' => $doc->token,
                        'status' => $doc->status,
                        'arquivo_assinado_id' => $doc->arquivo_assinado_id,
                        'tipo_documento' => $doc->tipo_documento,
                    ];
                }
            }
        }

        $apelido = optional(auth()->user()->Empresa)->apelido ?? '';
        $baseUrl = rtrim((string) env('APP_URL'), '/');
        $presenter = app(CartaOfertaListagemPresenter::class);

        $itens = $itens->map(function (CartaOferta $item) use ($presenter, $docsByCartaId, $apelido, $baseUrl) {
            return $presenter->apresentar($item, $docsByCartaId, $apelido, $baseUrl);
        })->values();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'lista_status' => CartaOferta::STATUS,
                'lista_projetos' => [],
            ]
        ]);
    }

    public function requestSgi($token)
    {

        switch (env('APP_URL')) {
            case 'https://sistema.mybp.com.br':
                $url = 'https://sgi.bpse.com.br';
                break;
            case 'https://qa.mybp.com.br':
                $url = 'https://qasgi.bpse.com.br';
                break;
            default:
                $url = 'http://192.168.1.10:8884';
                break;
        }

        $endpoint = "$url/api/carta-oferta/$token/integramybp";

        $client = new Client();
        $headers = [
            'X-API-TOKEN' => (string) env('SGI_X_API_TOKEN', env('X_API_TOKEN')),
            'Content-Type' => 'application/json',
            'User-Agent' => 'MyBPIN'
        ];

        $response = $client->post($endpoint, [
            'headers' => $headers,
        ]);


        return $response->getBody()->getContents();
    }

}
