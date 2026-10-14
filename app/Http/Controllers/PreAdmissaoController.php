<?php

namespace App\Http\Controllers;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappCurriculoTelefoneResolver;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Jobs\ControleExames\JobExame;
use App\Jobs\Entrevista\JobEnvioDocumento;
use App\Jobs\Entrevista\JobEnvioFeedbackDocumento;
use App\Models\Admissao;
use App\Models\AlternativaFormulario;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\DocumentosCurriculosAdmissaoEmpresa;
use App\Models\EmpresaExame;
use App\Models\ExameFuncionario;
use App\Models\ExameTipo;
use App\Models\FeedbackCurriculo;
use App\Models\FeedbackPreadmissao;
use App\Models\Formulario;
use App\Models\Pcmso;
use App\Models\RespostaAlternativas;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class PreAdmissaoController extends Controller
{

    public function index()
    {
        return view('g.admissao.preadmissao.index');
    }

    public function show($feedback)
    {
        $item = $this->queryBaseResumoPreadmissao()
            ->whereKey($feedback)
            ->firstOrFail();

        $finalizacao = $this->buscarFinalizacaoPreadmissao([$item->id])->get($item->id);
        $documentos = $this->montarDocumentosPreadmissao((int) $item->curriculo_id);
        $qntAnexos = collect($documentos)->sum('qnt_anexos');

        $resumo = $this->montarResumoPreadmissao($item, $finalizacao, (int) $qntAnexos);
        $resumo['docs_curriculo_pre_adm'] = $documentos;

        return response()->json($resumo);
    }

    public function showFinalizar($feedback)
    {
        $dados = $this->montarPayloadAcaoPreadmissao((int) $feedback);

        $pcmsos = Pcmso::query()
            ->whereAtivo(true)
            ->orderBy('label')
            ->get(['id', 'label']);

        $empresasExames = EmpresaExame::query()
            ->whereAtivo(true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'dados'])
            ->map(function (EmpresaExame $empresa) {
                $dados = is_array($empresa->dados) ? $empresa->dados : [];
                $endereco = is_array($dados['endereco'] ?? null) ? $dados['endereco'] : [];

                return [
                    'id' => $empresa->id,
                    'nome' => $empresa->nome,
                    'email' => $dados['email'] ?? null,
                    'telefone' => $dados['telefone'] ?? null,
                    'endereco' => $endereco['endereco_completo'] ?? null,
                ];
            })
            ->values();

        return response()->json([
            'empresas_exames' => $empresasExames,
            'pcmsos' => $pcmsos,
            'dados' => $dados,
        ]);
    }


    public function atualizar(Request $request)
    {
        $resultado = $this->queryBaseResumoPreadmissao();

        if ($request->filled('campoBusca')) {
            $busca = $request->campoBusca;
            $resultado->whereHas('Curriculo', function ($query) use ($busca) {
                $query->where(function ($query) use ($busca) {
                    $query->where('nome', 'like', '%' . $busca . '%')
                        ->orWhere('cpf', 'like', '%' . $busca . '%')
                        ->orWhere('id', $busca);
                });
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;

            $resultado->whereHas('ResultadoIntegrado', fn($q) => $q->whereDocumentosEntregue(true));

            match ($status) {
                'admitidos' => $resultado->whereHas('Admissao', fn($q) => $q->where('status', Admissao::STATUS_ADMISSAO_ADMITIDO)
                )->whereDoesntHave('Demissao'),

                'demitidos' => $resultado->demitidos(),

                'em_processo' => $resultado->where(function ($query) {
                    $query->whereDoesntHave('Admissao')
                        ->orWhereHas('Admissao', fn($q) => $q->whereIn('status', Admissao::STATUS_EM_PROCESSO_SELECAO)
                        );
                })->whereDoesntHave('Demissao'),

                default => $resultado
            };
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

        if ($request->filled('campoCliente')) {
            $resultado->where('cliente_id', $request->campoCliente);
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('VagaAberta.Municipio', function ($query) use ($request) {
                $query->where('uf', $request->campoUf);
            });
        }

        if ($request->filled('statusDocumento')) {
            match ($request->statusDocumento) {
                'concluido' => $resultado->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('feedback_preadmissao')
                        ->whereColumn('feedback_preadmissao.feedback_id', 'feedback_curriculos.id');
                }),
                'pendente' => $resultado->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('feedback_preadmissao')
                        ->whereColumn('feedback_preadmissao.feedback_id', 'feedback_curriculos.id');
                }),
                default => $resultado,
            };
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        $pagina = collect($resultado->items());
        $feedbackIds = $pagina->pluck('id')->all();
        $curriculoIds = $pagina->pluck('curriculo_id')->filter()->unique()->values()->all();

        $anexosPorCurriculo = $this->contarAnexosPreadmissaoPorCurriculo($curriculoIds);
        $finalizacaoPorFeedback = $this->buscarFinalizacaoPreadmissao($feedbackIds);

        $items = $pagina->map(function (FeedbackCurriculo $item) use ($anexosPorCurriculo, $finalizacaoPorFeedback) {
            return $this->montarResumoPreadmissao(
                $item,
                $finalizacaoPorFeedback->get($item->id),
                (int) ($anexosPorCurriculo[$item->curriculo_id] ?? 0)
            );
        })->values();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $items,
                'usuario_cliente_id' => auth()->user()->cliente_id,
                'email_padrao' => Sistema::EMAILPADRAO,
            ],
        ]);
    }

    public function edit(FeedbackCurriculo $feedback)
    {
        return response()->json($this->montarPayloadAcaoPreadmissao((int) $feedback->id));
    }

    public function finalizarEncaminhar(Request $request)
    {
        //TODO : Criar dados para feedback_preadmissao
        try {
            \DB::beginTransaction();
            $token = Sistema::uuid();
            $formulario_id = Formulario::whereTitulo('Exames')->first()->load('Setores.Alternativas.Opcoes')->id;
            $empExame = EmpresaExame::find($request->empresa_exame_id);
            $pcmso_id = $request->pcmso_id;
            $exame_tipo_id = 1; // Admissional
            $data_encaminhamento_insert = (new DataHora())->dataHoraInsert();
            $data_encaminhamento = (new DataHora())->dataHoraCompleta();
            $data_realizacao = (new DataHora($request->encaminhado_exame_data ?? $request->encaminhamento_data))->dataCompleta();

            if (!$pcmso_id == "") {
                $exame = ExameFuncionario::create([
                    'feedback_id' => $request->feedback_id,
                    'empresa_exame_id' => $request->empresa_exame_id,
                    'formulario_id' => $formulario_id,
                    'respostas' => (object)[],
                    'token' => $token,
                    'pcmso' => true,
                    'pcmso_id' => $pcmso_id,
                    'exame_tipo_id' => $exame_tipo_id,
                    'encaminhamento_data' => $data_encaminhamento_insert
                ]);

                $tipoExame = ExameTipo::find($exame_tipo_id);
            } else {
                $tipoOrdem = AlternativaFormulario::whereNome('Tipo de ordem')->whereEmpresaId(auth()->user()->empresa_id)->first();
                $tipoExame = RespostaAlternativas::whereValue($request->respostas['alternativa_id_' . $tipoOrdem['id']]['valor'])->first();


                $exame = ExameFuncionario::create([
                    'feedback_id' => $request->feedback_id,
                    'empresa_exame_id' => $request->empresa_exame_id,
                    'formulario_id' => $formulario_id,
                    'respostas' => $request->respostas,
                    'token' => $token,
                    'pcmso' => false,
                    'encaminhamento_data' => $data_encaminhamento_insert,
                    'exame_tipo_id' => (int)$tipoExame->value,
                ]);
            }

            $dados_feedback_preadmissao = [
                'feedback_id' => $request->feedback_id,
                'user_finalizou_id' => auth()->user()->id,
            ];

            FeedbackPreadmissao::create($dados_feedback_preadmissao);

            $colaborador = FeedbackCurriculo::select(['curriculo_id', 'id', 'telefone_id'])->find($request->feedback_id);

            if ($request->envia_email) {

                $dtEmailClinica = [
                    'clinica' => $empExame->nome,
                    'email' => trim(mb_strtolower($empExame->dados['email'])),
                    'assunto' => "Encaminhamento de Exame {$tipoExame->label} colaborador {$colaborador->Curriculo->nome}",
                    'colaborador' => $colaborador->Curriculo->nome,
                    'colaborador_email' => trim(mb_strtolower($colaborador->Curriculo->email)),
                    'idade' => $colaborador->Curriculo->idade,
                    'tipoExame' => $tipoExame->label,
                    'empresa_id' => $empExame->empresa_id,
                    'link' => route('publico.encaminhamento_exame_fichapdf', ['exame' => $exame, 'token' => $token]),
                    'encaminhamento_data' => $data_encaminhamento,
                    'data_realizacao' => $data_realizacao,
                ];

                $dtEmailColaborador = [
                    'clinica' => $empExame,
                    'email' => trim(mb_strtolower($colaborador->Curriculo->email)),
                    'assunto' => "Encaminhamento de Exame {$tipoExame->label}",
                    'colaborador' => $colaborador->Curriculo->nome,
                    'tipoExame' => $tipoExame->label,
                    'empresa_id' => $empExame->empresa_id,
                    'link' => route('publico.encaminhamento_exame_fichapdf', ['exame' => $exame, 'token' => $token]),
                    'encaminhamento_data' => $data_encaminhamento,
                    'data_realizacao' => $data_realizacao,
                ];

                $dados_email = [
                    'dtEmailClinica' => $dtEmailClinica,
                    'dtEmailColaborador' => $dtEmailColaborador
                ];

                JobExame::dispatch($dados_email);
            }

            if ($request->envia_whatsapp) {
                $podeEnviarWhatsapp = app(WhatsappNotificationGateService::class)->podeEnviar(
                    TipoMensagemWhatsapp::ExameEncaminhamento,
                    (int) auth()->user()->empresa_id,
                );

                $telefoneResolver = app(WhatsappCurriculoTelefoneResolver::class);
                $telefonePrincipal = $telefoneResolver->telefonePrincipalPermiteWhatsapp($colaborador->TelPrincipal)
                    ? $colaborador->TelPrincipal
                    : null;

                if ($podeEnviarWhatsapp && $telefonePrincipal && !is_null($empExame)) {
                    $mensagem = app(WhatsappMessageFactory::class)->render(
                        TipoMensagemWhatsapp::ExameEncaminhamento,
                        (int) auth()->user()->empresa_id,
                        [
                            'nome_destinatario' => $colaborador->Curriculo->nome,
                            'tipo_exame' => $tipoExame->label,
                            'clinica_nome' => $empExame->nome,
                            'clinica_endereco' => $empExame->dados['endereco']['endereco_completo'] ?? '',
                            'clinica_telefone' => $empExame->dados['telefone'] ?? '',
                            'data_encaminhamento' => $data_encaminhamento,
                            'data_realizacao' => $data_realizacao,
                        ]
                    );

                    (new ZapNotificacao())->enviar([
                        'enviado_id' => $colaborador->curriculo_id,
                        'telefone' => $telefonePrincipal->sonumero,
                        'mensagem' => $mensagem,
                        '_whatsapp_meta' => ZapNotificacao::meta(
                            TipoMensagemWhatsapp::ExameEncaminhamento,
                            (int) auth()->user()->empresa_id,
                        ),
                    ]);
                }
            }

            \DB::commit();
            return response()->json("", 201);
        } catch (\ErrorException $e) {
            \DB::rollback();
            $msg = "Erro ao Encaminhar para exame:  {$e->getMessage()} , CODIGO:  {$e->getCode()}, Linha: {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            Sistema::LogFormatado($request->input());
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro ao encaminhar!'], 400);
        }
    }

    public function enviarEmail(Request $request)
    {
        $dados = $request->input();

        if ($dados['email'] == Sistema::EMAILPADRAO) {
            return response()->json([
                'msg' => 'O e-mail não pode ser ' . Sistema::EMAILPADRAO
            ], 400);
        }

        $dadosValidados = \Validator::make($dados, [
            'email' => 'required|email:rfc,dns',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Atualizar Email',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $feedback = FeedbackCurriculo::whereId($dados['id'])->first();
            $feedback['email'] = $dados['email'];
            $curriculo = Curriculo::whereId($dados['curriculo_id'])->with('Pessoa')->first();
            $email_atual = $curriculo->email == $dados['email'];
            $email_padrao = $curriculo->email == Sistema::EMAILPADRAO;
            $Empresa = Cliente::select('nome_fantasia')->where('id', $feedback->empresa_id)->first();

            if (!$email_atual) {
                $curriculo->update(['email' => $dados['email']]);
                $curriculo->Pessoa->update(['login' => $dados['email']]);
            }

            $curriculo->EmailsPreAdmissao()->create([
                'observacao' => $dados['observacao'],
                'email_atual' => $email_atual,
                'email_padrao' => $email_padrao
            ]);

            DB::commit();

            $urlDocumentos = app(\App\Services\Preadmissao\DocumentosPreAdmissaoAcessoService::class)
                ->urlPublica((int) $feedback->empresa_id, (int) $curriculo->id, auth()->user()->Empresa->apelido);

            if (auth()->user()->empresa_id == 65974) { //Equatorial
                JobEnvioFeedbackDocumento::dispatch([
                    'nome' => $curriculo->nome,
                    'email' => $feedback['email'],
                    'empresa_id' => $feedback->empresa_id,
                    'observacao' => $dados['observacao'],
                ]);

            } else {
                JobEnvioDocumento::dispatch([
                    'nome' => $curriculo->nome,
                    'email' => $feedback['email'],
                    'empresa_id' => $feedback->empresa_id,
                    'url_documento' => $urlDocumentos,
                ]);
            }

            $podeEnviarWhatsappDocumentos = app(WhatsappNotificationGateService::class)->podeEnviar(
                TipoMensagemWhatsapp::AdmissaoDocumentos,
                (int) auth()->user()->empresa_id,
            );

            if ($podeEnviarWhatsappDocumentos && $dados['temwhatsapp'] && $dados['envia_whatsapp'] && $dados['numero_telefone']) {
                $telefonePrincipal = app(WhatsappCurriculoTelefoneResolver::class)
                    ->resolverPrincipalWhatsapp((int) $curriculo->id);

                if ($telefonePrincipal) {
                    $ambiente = env('AMBIENTE', 'local') == 'prod' ?: 'local';
                    if ($ambiente != 'prod') {
                        $zapTelAtivo = \DB::table("zap_numeros")->where("ativo", true)->first();
                        $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '559899023762';
                    }

                    $observacao = trim((string) ($dados['observacao'] ?? ''));
                    $mensagemWhats = app(WhatsappMessageFactory::class)->render(
                        TipoMensagemWhatsapp::AdmissaoDocumentos,
                        (int) auth()->user()->empresa_id,
                        [
                            'nome_destinatario' => $curriculo->nome,
                            'url_documentos' => $urlDocumentos,
                            'observacao' => $observacao !== '' ? $observacao . "\n\n" : '',
                        ]
                    );

                    if (auth()->user()->empresa_id == 65974 && $observacao === '') {
                        $mensagemWhats = '';
                    }

                    if (strlen($mensagemWhats) > 0) {
                        (new ZapNotificacao())->enviar([
                            'enviado_id' => $curriculo->id,
                            'telefone' => $telefonePrincipal->sonumero,
                            'mensagem' => $mensagemWhats,
                            '_whatsapp_meta' => ZapNotificacao::meta(
                                TipoMensagemWhatsapp::AdmissaoDocumentos,
                                (int) auth()->user()->empresa_id,
                            ),
                        ]);
                    }
                }
            }
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::debug("Erro ao enviar e-mail: " . $e->getMessage());
            return response()->json(['msg' => 'Erro ao enviar e-mail', 'erros' => $e->getMessage()], 400);
        }
    }

    private function queryBaseResumoPreadmissao()
    {
        return FeedbackCurriculo::select([
            'id',
            'curriculo_id',
            'cliente_id',
            'vagas_abertas_id',
            'telefone_id',
            'created_at',
        ])->with([
            'Curriculo:id,nome,cpf,email,uf',
            'TelPrincipal:id,numero,tipo,pais',
            'Cliente:id,nome_fantasia,nome,razao_social',
            'VagaAberta:id,vaga_id,municipio_id,titulo',
            'VagaAberta.Vaga:id,nome',
            'VagaAberta.Municipio:id,nome,uf',
            'Admissao:id,feedback_id,status',
            'Demissao:id,feedback_id',
        ]);
    }

    /**
     * Payload enxuto para ações (reenviar e-mail / finalizar): mesmo resumo da listagem + telefone WhatsApp.
     *
     * @return array<string, mixed>
     */
    private function montarPayloadAcaoPreadmissao(int $feedbackId): array
    {
        $item = $this->queryBaseResumoPreadmissao()
            ->whereKey($feedbackId)
            ->firstOrFail();

        $finalizacao = $this->buscarFinalizacaoPreadmissao([$item->id])->get($item->id);
        $qntAnexos = (int) ($this->contarAnexosPreadmissaoPorCurriculo([(int) $item->curriculo_id])[$item->curriculo_id] ?? 0);
        $resumo = $this->montarResumoPreadmissao($item, $finalizacao, $qntAnexos);
        $resumo['tel_principal'] = $this->montarTelPrincipalLeitura($item->TelPrincipal);

        return $resumo;
    }

    /**
     * @param  \App\Models\TelefoneCurriculo|null  $telefone
     * @return array<string, mixed>|null
     */
    private function montarTelPrincipalLeitura($telefone): ?array
    {
        if (!$telefone) {
            return null;
        }

        return [
            'tipo' => $telefone->tipo,
            'numero' => $telefone->numero,
            'sonumero' => $telefone->sonumero,
        ];
    }

    /**
     * @param  object|null  $finalizacao
     * @return array<string, mixed>
     */
    private function montarResumoPreadmissao(FeedbackCurriculo $item, $finalizacao, int $qntAnexos): array
    {
        $cargo = $item->VagaAberta?->Vaga?->nome
            ?? $item->VagaAberta?->titulo
            ?? null;
        $municipio = $item->VagaAberta?->Municipio;
        $localVaga = $municipio
            ? trim(($municipio->nome ?? '') . ' - ' . ($municipio->uf ?? ''), ' -')
            : null;

        return [
            'id' => $item->id,
            'curriculo_id' => $item->curriculo_id,
            'candidato_nome' => $item->Curriculo?->nome,
            'candidato_cpf' => $this->formatarCpfExibicao($item->Curriculo?->cpf),
            'candidato_email' => $item->Curriculo?->email,
            'candidato_uf' => $item->Curriculo?->uf,
            'contato_telefone' => $item->TelPrincipal?->numero,
            'cliente_nome' => $item->Cliente?->nome_fantasia
                ?: $item->Cliente?->nome
                ?: $item->Cliente?->razao_social,
            'cargo_nome' => $cargo,
            'vaga_local' => $localVaga,
            'vaga_aberta_municipio' => $cargo && $localVaga
                ? "{$cargo} - {$localVaga}"
                : ($cargo ?: $localVaga),
            'qnt_anexos' => $qntAnexos,
            'situacao_colaborador' => $this->resolverSituacaoColaborador($item),
            'finalizado' => $finalizacao !== null,
            'quem_finalizou' => $finalizacao?->quem_finalizou,
            'data_finalizacao' => $finalizacao
                ? (new DataHora($finalizacao->created_at))->dataHoraCompleta()
                : null,
        ];
    }

    private function resolverSituacaoColaborador(FeedbackCurriculo $item): string
    {
        if ($item->Demissao) {
            return 'demitido';
        }

        if ($item->Admissao?->status === Admissao::STATUS_ADMISSAO_ADMITIDO) {
            return 'admitido';
        }

        return 'em_processo';
    }

    /**
     * Documentos da empresa + anexos do currículo (1 query de anexos).
     *
     * @return array<int, array<string, mixed>>
     */
    private function montarDocumentosPreadmissao(int $curriculoId): array
    {
        $docsEmpresa = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(auth()->user()->empresa_id);
        $tipos = $docsEmpresa->pluck('tipo')->filter()->unique()->values()->all();

        $anexosPorTipo = collect();
        if ($tipos !== []) {
            $anexosPorTipo = DB::table('documentos_curriculos')
                ->join('arquivos', 'arquivos.id', '=', 'documentos_curriculos.arquivo_id')
                ->where('documentos_curriculos.curriculo_id', $curriculoId)
                ->whereIn('documentos_curriculos.tipo', $tipos)
                ->select([
                    'documentos_curriculos.tipo',
                    'documentos_curriculos.arquivo_id',
                    'arquivos.id',
                    'arquivos.file',
                    'arquivos.thumb',
                    'arquivos.disco',
                    'arquivos.nome',
                    'arquivos.extensao',
                    'arquivos.imagem',
                    'arquivos.chave',
                ])
                ->get()
                ->groupBy('tipo');
        }

        return $docsEmpresa->map(function ($doc) use ($anexosPorTipo) {
            $anexos = ($anexosPorTipo->get($doc->tipo) ?? collect())->map(function ($arquivo) {
                $ehImagem = (bool) $arquivo->imagem;
                // URLs relativas e assinadas (o disco é privado; a rota confere assinatura + empresa)
                $url = Arquivo::urlAssinadaPreAdmissao('show', $arquivo->file);
                $urlDownload = Arquivo::urlAssinadaPreAdmissao('download', $arquivo->file);
                $urlThumb = $ehImagem && $arquivo->thumb
                    ? Arquivo::urlAssinadaPreAdmissao('show', $arquivo->thumb)
                    : $url;

                return [
                    'id' => $arquivo->id,
                    'arquivo_id' => $arquivo->arquivo_id,
                    'file' => $arquivo->file,
                    'nome' => $arquivo->nome,
                    'extensao' => $arquivo->extensao,
                    'imagem' => $ehImagem,
                    'chave' => $arquivo->chave,
                    'disco' => $arquivo->disco,
                    // Upload em leitura exige enviado=true quando há chave
                    'enviado' => true,
                    'url' => $url,
                    'url_download' => $urlDownload,
                    'urlDownload' => $urlDownload,
                    'urlThumb' => $urlThumb,
                ];
            })->values()->all();

            return [
                'label' => $doc->label,
                'descricao' => $doc->descricao,
                'tipo' => $doc->tipo,
                'qnt_anexos' => count($anexos),
                'enviado' => count($anexos) > 0,
                'docs_curriculo_anexos' => $anexos,
            ];
        })->values()->all();
    }

    /**
     * Conta anexos de pré-admissão por currículo (1 query), só tipos ativos da empresa.
     *
     * @param  array<int>  $curriculoIds
     * @return array<int, int>
     */
    private function contarAnexosPreadmissaoPorCurriculo(array $curriculoIds): array
    {
        if ($curriculoIds === []) {
            return [];
        }

        $tipos = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(auth()->user()->empresa_id)
            ->pluck('tipo')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($tipos === []) {
            return [];
        }

        return DB::table('documentos_curriculos')
            ->join('arquivos', 'arquivos.id', '=', 'documentos_curriculos.arquivo_id')
            ->whereIn('documentos_curriculos.curriculo_id', $curriculoIds)
            ->whereIn('documentos_curriculos.tipo', $tipos)
            ->groupBy('documentos_curriculos.curriculo_id')
            ->selectRaw('documentos_curriculos.curriculo_id, COUNT(*) as qnt')
            ->pluck('qnt', 'curriculo_id')
            ->map(fn ($qnt) => (int) $qnt)
            ->all();
    }

    /**
     * Finalização da pré-admissão em lote (1 query + join users).
     *
     * @param  array<int>  $feedbackIds
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function buscarFinalizacaoPreadmissao(array $feedbackIds)
    {
        if ($feedbackIds === []) {
            return collect();
        }

        return DB::table('feedback_preadmissao')
            ->leftJoin('users', 'users.id', '=', 'feedback_preadmissao.user_finalizou_id')
            ->whereIn('feedback_preadmissao.feedback_id', $feedbackIds)
            ->select([
                'feedback_preadmissao.feedback_id',
                'feedback_preadmissao.created_at',
                'users.nome as quem_finalizou',
            ])
            ->get()
            ->keyBy('feedback_id');
    }

    private function formatarCpfExibicao(?string $cpf): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $cpf);
        if (strlen($digitos) !== 11) {
            return null;
        }

        return Sistema::mascaraCpf($digitos);
    }
}
