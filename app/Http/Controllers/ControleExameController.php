<?php

namespace App\Http\Controllers;

use App\Models\Admissao;
use App\Models\AlternativaFormulario;
use App\Models\Arquivo;
use App\Models\CentroCusto;
use App\Models\EmpresaExame;
use App\Models\ExameFuncionario;
use App\Models\Examesesmt;
use App\Models\ExameTipo;
use App\Models\FeedbackCurriculo;
use App\Models\Pcmso;
use App\Models\RespostaAlternativas;
use App\Models\Sistema;
use App\Models\Cliente;
use App\Models\User;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use App\Services\ControleExames\ColaboradorExameIdentificacao;
use App\Services\ControleExames\ExameEncaminhamentoNotificacaoService;
use App\Services\ControleExames\RestricaoOrdemExameDemitido;
use App\Services\Demissao\ComunicadoDispensaSemJustaCausaPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;
use RuntimeException;

class ControleExameController extends Controller
{
    public function index()
    {
        return view('g.controle-exames.index');
    }

    public function carregaResposta(Request $request)
    {
        if ($request->filled('feedback_id') && $request->filled('formulario')) {
            $resposta = ExameFuncionario::whereFeedbackId($request->feedback_id)
                ->whereFormularioId($request->formulario)
                ->with('EmpresaExame:id,nome', 'QuemEncaminhou:id,nome', 'Formulario.Setores.Alternativas.Opcoes', 'PcmsoDados:id,label')
                ->orderByDesc('created_at')->get();

            $resposta->transform(function ($item) {
                if (is_null($item->exame_tipo_id)) {
                    $tipoOrdem = AlternativaFormulario::whereNome('Tipo de ordem')->whereEmpresaId(auth()->user()->empresa_id)->first()->id;
                    $item->tipo_exame = RespostaAlternativas::whereValue($item->respostas['alternativa_id_' . $tipoOrdem]['valor'])->first()?->label;
                } else {
                    $item->tipo_exame = ExameTipo::find($item->exame_tipo_id)->label;
                }
                $item->encaminhamento_data = is_null($item->encaminhamento_data) ? (new DataHora($item->created_at))->dataCompleta() : (new DataHora($item->encaminhamento_data))->dataCompleta();
                $item->pcmso_label = $item->pcmso ? $item->PcmsoDados->label : 'Não se aplica';
                $item->resultado = Examesesmt::whereExameFuncionarioId($item->id)->first();
                return $item;
            });

            $pcmos = Pcmso::whereAtivo(true)->get();
            $restricao = app(RestricaoOrdemExameDemitido::class);
            $somenteOrdemDemissional = $restricao->colaboradorDemitido((int) $request->feedback_id);
            $feedback = FeedbackCurriculo::query()->find($request->feedback_id);

            return [
                'tipo' => 'cadastrar',
                'historico' => $resposta,
                'pcmsos' => $pcmos,
                'exame_tipos' => $restricao->filtrarTipos(ExameTipo::whereAtivo(true)->get(), $somenteOrdemDemissional),
                'somente_ordem_demissional' => $somenteOrdemDemissional,
                'identificacao' => app(ColaboradorExameIdentificacao::class)->paraFeedback($feedback),
            ];
        } else {
            return response()->json(['msg' => "Erro -> Faltando parametros"], 400);
        }
    }

    public function salvaUpdate(Request $request)
    {
        if ($request->input('tipo') === 'store') {
            $restricao = app(RestricaoOrdemExameDemitido::class);
            if (!$restricao->encaminhamentoPermitido((int) $request->feedback_id, $request)) {
                return response()->json(['msg' => RestricaoOrdemExameDemitido::MENSAGEM], 422);
            }
        }

        try {
            \DB::beginTransaction();
            $token = Sistema::uuid();
            $data_encaminhamento = (new DataHora())->dataHoraCompleta();
            $dataRealizacaoInformada = $request->encaminhado_exame_data ?? $request->encaminhamento_data;
            $data_realizacao = (new DataHora($dataRealizacaoInformada))->dataCompleta();
            $data_realizacao_insert = (new DataHora($dataRealizacaoInformada))->dataInsert();

            if ($request->tipo == 'store') {
                $empExame = EmpresaExame::find($request->empresa_exame_id);
                $pcmso_id = $request->pcmso_id;

                // Select se tem

                if (!$pcmso_id == "") {
                    $exame_tipo_id = $request->exame_tipo_id;

                    $exame = ExameFuncionario::create([
                        'feedback_id' => $request->feedback_id,
                        'empresa_exame_id' => $request->empresa_exame_id,
                        'formulario_id' => $request->formulario_id,
                        'respostas' => (object)[],
                        'token' => $token,
                        'pcmso' => true,
                        'pcmso_id' => $pcmso_id,
                        'exame_tipo_id' => $exame_tipo_id,
                        'encaminhamento_data' => $data_realizacao_insert
                    ]);

                    $tipoExame = ExameTipo::find($exame_tipo_id);
                } else {
                    $tipoOrdem = AlternativaFormulario::whereNome('Tipo de ordem')->whereEmpresaId(auth()->user()->empresa_id)->first();
                    $tipoExame = RespostaAlternativas::whereValue($request->respostas['alternativa_id_' . $tipoOrdem['id']]['valor'])->first();


                    $exame = ExameFuncionario::create([
                        'feedback_id' => $request->feedback_id,
                        'empresa_exame_id' => $request->empresa_exame_id,
                        'formulario_id' => $request->formulario_id,
                        'respostas' => $request->respostas,
                        'token' => $token,
                        'pcmso' => false,
                        'encaminhamento_data' => $data_realizacao_insert,
                        'exame_tipo_id' => (int)$tipoExame->value,
                    ]);
                }

                $colaborador = FeedbackCurriculo::with(['Curriculo', 'TelPrincipal'])
                    ->select(['curriculo_id', 'id', 'telefone_id'])
                    ->find($request->feedback_id);
                $notificacao = app(ExameEncaminhamentoNotificacaoService::class);

                if ($request->envia_email && !is_null($empExame)) {
                    $notificacao->enviarEmail(
                        $exame,
                        $colaborador,
                        $empExame,
                        (string) $tipoExame->label,
                        $token,
                        $data_encaminhamento,
                        $data_realizacao
                    );
                }

                if ($request->envia_whatsapp && !is_null($empExame)) {
                    $notificacao->enviarWhatsapp(
                        $colaborador,
                        $empExame,
                        (string) $tipoExame->label,
                        $data_encaminhamento,
                        $data_realizacao
                    );
                }

                \DB::commit();
                return response()->json("");
            } else {
                \DB::beginTransaction();
                ExameFuncionario::find($request->id)->update([
                    'respostas' => $request->respostas
                ]);
                \DB::commit();
                return response()->json("Editou", 201);
            }
        } catch (\ErrorException $e) {
            \DB::rollback();
            $msg = "Erro ao Encaminhar para exame:  {$e->getMessage()} , CODIGO:  {$e->getCode()}, Linha: {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            Sistema::LogFormatado($request->input());
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro ao encaminhar!'], 400);
        }
    }

    public function reenviarEncaminhamento(Request $request, ExameEncaminhamentoNotificacaoService $notificacao)
    {
        $request->validate([
            'exame_funcionario_id' => 'required|integer',
            'envia_email' => 'sometimes|boolean',
            'envia_whatsapp' => 'sometimes|boolean',
        ]);

        $enviaEmail = $request->boolean('envia_email');
        $enviaWhatsapp = $request->boolean('envia_whatsapp');

        try {
            $empresaId = (int) auth()->user()->empresa_id;
            $exame = ExameFuncionario::query()
                ->where('empresa_id', $empresaId)
                ->whereHas('Feedback', fn ($q) => $q->where('empresa_id', $empresaId))
                ->findOrFail((int) $request->input('exame_funcionario_id'));
            $resultado = $notificacao->reenviar($exame, $enviaEmail, $enviaWhatsapp);

            return response()->json([
                'msg' => implode(' ', $resultado['mensagens']),
                'email' => $resultado['email'],
                'whatsapp' => $resultado['whatsapp'],
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['msg' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Log::debug('Erro ao reenviar encaminhamento de exame: ' . $e->getMessage(), [
                'exame_funcionario_id' => $request->input('exame_funcionario_id'),
                'user_id' => auth()->id(),
            ]);

            return response()->json(['msg' => 'Houve um erro ao reenviar o encaminhamento.'], 400);
        }
    }

    public function getResultado(Request $request, $exame)
    {
//        $feedback->load(['Afastamentos' => function($query){
//            $query->with('Anexos')->orderBy('id', 'desc');
//        }]);

        $Examesesmt = Examesesmt::whereExameFuncionarioId($exame)->with('Anexos')->first();
        if ($Examesesmt) {
            $Examesesmt->cadastrando = false;
            return $Examesesmt;
        }
        return '';
    }

    public function salvaResultado(Request $request)
    {
        $dados = $request->input();

        unset($dados['id']);

        $dados['data_vencimento'] = (new DataHora($dados['data_realizacao']))->addAno(1);
        $dados['vencido'] = false;

        $feedback_id = ExameFuncionario::find($dados['exame_funcionario_id'])->feedback_id;
        $dados['feedback_id'] = $feedback_id;

        $dadosValidados = \Validator::make($dados, []);

        if ($dados['exame_realizado']) {
            $dadosValidados = \Validator::make($dados, [
                'data_realizacao' => 'required_if:exame_realizado,1|date_format:d/m/Y',
                'resultado.result' => 'required_if:exame_realizado,1|in:Apto,Apto com Restrição,Inapto',
                'resultado.pendencias' => 'required_if:exame_realizado,1|in:Sim,Não',
                'resultado.pendencias_quais' => 'required_if:resultado.pendencias,Sim',
                'resultado.aprovado' => 'required_if:exame_realizado,1',
                'resultado.trabalho_altura' => 'required_if:exame_realizado,1|in:Sim,Não,Não se aplica',
                'resultado.observacao' => 'max:500',
            ]);
        }

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar o resultado',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $Examesesmt = Examesesmt::create($dados);

            if ($dados['resultado']['aprovado'] == "Sim") {
                Examesesmt::whereFeedbackId($feedback_id)->update([
                    'atual' => 0
                ]);

                $Examesesmt->update([
                    'atual' => 1
                ]);
            }

            foreach ($dados['anexos'] as $item) {
                $Examesesmt->Anexos()->attach($item['id']);
                $Examesesmt->Anexos()->where('id', $item['id'])
                    ->where('temporario', true)
                    ->where('chave', $item['chave'])
                    ->update([
                        'temporario' => false,
                        'chave' => '',
                        'nome' => $item['nome']
                    ]);
            }
            \DB::commit();
            return response()->json([]);

        } catch (\ErrorException $e) {
            \DB::rollback();
            $msg = "Erro ao Cadastrar Resultado para exame:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro ao Cadastrar o Resultado do exame'], 400);
        }
    }

    public function updateResultado(Request $request, Examesesmt $resultado)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, []);

        if ($dados['exame_realizado']) {
            $dadosValidados = \Validator::make($dados, [
                'data_realizacao' => 'required_if:exame_realizado,1|date_format:d/m/Y',
                'resultado.result' => 'required_if:exame_realizado,1|in:Apto,Apto com Restrição,Inapto',
                'resultado.pendencias' => 'required_if:exame_realizado,1|in:Sim,Não',
                'resultado.pendencias_quais' => 'required_if:resultado.pendencias,Sim',
                'resultado.aprovado' => 'required_if:exame_realizado,1',
                'resultado.trabalho_altura' => 'required_if:exame_realizado,1|in:Sim,Não,Não se aplica',
                'resultado.observacao' => 'max:500',
            ]);
        }

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar o resultado',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();

            if ($dados['exame_realizado']) {
                if (isset($dados['anexosDel'])) {
                    foreach ($dados['anexosDel'] as $id_anexo) {
                        Arquivo::apagaAnexo($id_anexo);
                    }
                }
                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->nome = $anexo['nome'];
                            $arquivo->chave = '';
                            $arquivo->save();
                            $resultado->Anexos()->attach($arquivo->id);
                        }
                    }
                }
            } else {
                foreach ($resultado->Anexos()->pluck('id') as $id_anexo) {
                    Arquivo::apagaAnexo($id_anexo);
                }
            }

            $resultado->update($dados);

            if ($dados['resultado']['aprovado'] == "Sim") {

                Examesesmt::whereFeedbackId($resultado->feedback_id)->update([
                    'atual' => 0
                ]);

                $resultado->update([
                    'atual' => 1
                ]);
            }

            \DB::commit();
            return response()->json([], 201);
        } catch (\ErrorException $e) {
            \DB::rollback();
            $msg = "Erro ao Atualizar o resultado do exame para exame:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
//            return response()->json($msg, 400);
            return response()->json(['msg' => 'Houve um erro ao atualizar o resultado do exame!'], 400);
        }
    }

    public function atualizar(Request $request)
    {

        $resultado = FeedbackCurriculo::select([
            'id',
            'curriculo_id',
            'vaga_id',
            'telefone_id',
            'vagas_abertas_id',
            'vaga_projeto_id',
            'empresa_id'
        ])->with(
            'Curriculo:id,nome,nascimento,id,nome,email,nascimento,rg,orgao_expeditor,logradouro,cep,end_numero,complemento,bairro,municipio,uf',
            'Cliente:id,razao_social,area_id',
            'VagaAberta', 'telCadPrincipal');

        if ($request->filled('status')) {
            if ($request->status == 'em_processo') {
                $resultado->whereHas('ResultadoIntegrado', function ($q) {
                    $q->whereEncaminhadoExame(true);
                })->whereDoesntHave('Admissao')->whereDoesntHave('Demissao')
                    ->orWhereHas('Admissao', function ($q) {
                        $q->whereNotIn('status', [Admissao::STATUS_ADMISSAO_ADMITIDO, Admissao::STATUS_ADMISSAO_DESISTENCIA, Admissao::STATUS_DEMITIDO]);
                    });
            }

            if ($request->status == 'admitidos') {
                $resultado->whereHas('ResultadoIntegrado', function ($q) {
                    $q->whereEncaminhadoExame(true);
                })->whereHas('Admissao', function ($q) {
                    $q->where('status', Admissao::STATUS_ADMISSAO_ADMITIDO);
                })->whereDoesntHave('Demissao')->filtrarPorCnpjECentroCusto($request);
            }

            if ($request->status == 'demitidos') {
                $resultado->whereHas('ResultadoIntegrado', function ($q) {
                    $q->whereEncaminhadoExame(true);
                })->whereHas('Admissao', function ($q) {
                    $q->where('status', Admissao::STATUS_DEMITIDO);
                })->Has('Demissao')
                    ->with('Demissao')
                    ->filtrarPorCnpjECentroCusto($request);;
            }
        }

        $filtroPeriodo = $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo[1] . ' 23:59:59');
            $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                $q->where('created_at', '>=', $dataInicio->dataHoraInsert())
                    ->where('created_at', '<=', $dataFim->dataHoraInsert());
            });
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%');
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->whereCpf($request->campoCPF);
            });
        }

        $resultado = $resultado->paginate($request->pages);
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        $somenteDemissional = $request->status === 'demitidos';
        $ultimosEncaminhamentos = $this->ultimosEncaminhamentosPorFeedback(
            collect($resultado->items())->pluck('id')->all(),
            $somenteDemissional
        );
        $items = collect($resultado->items())->transform(function ($item) use ($cc, $somenteDemissional, $ultimosEncaminhamentos) {
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
            $exameFuncionario = $ultimosEncaminhamentos->get($item->id);
            $item->ultimo_encaminhamento = $this->textoUltimoEncaminhamento($exameFuncionario, !$somenteDemissional);
            if ($somenteDemissional) {
                $item->encaminhado_demissional = $exameFuncionario ? 'Sim' : 'Não';
            }
            return $item;
        });

        $empresaExames = EmpresaExame::whereAtivo(true)->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $items,
                'emp_exames' => $empresaExames,
                'cc' => $cc
            ]
        ]);
    }

    /**
     * @param  list<int>  $feedbackIds
     * @return \Illuminate\Support\Collection<int, ExameFuncionario>
     */
    private function ultimosEncaminhamentosPorFeedback(array $feedbackIds, bool $somenteDemissional)
    {
        $feedbackIds = array_values(array_filter(array_map('intval', $feedbackIds)));
        if ($feedbackIds === []) {
            return collect();
        }

        $consulta = ExameFuncionario::query()
            ->selectRaw('MAX(id) as id')
            ->whereIn('feedback_id', $feedbackIds)
            ->groupBy('feedback_id');

        if ($somenteDemissional) {
            $tiposDemissionais = ExameTipo::query()
                ->where('label', ExameTipo::LABEL_DEMISSIONAL)
                ->pluck('id');
            if ($tiposDemissionais->isEmpty()) {
                return collect();
            }
            $consulta->whereIn('exame_tipo_id', $tiposDemissionais);
        }

        $ids = $consulta->pluck('id');
        if ($ids->isEmpty()) {
            return collect();
        }

        $exames = ExameFuncionario::query()
            ->with('ExameTipo:id,label')
            ->whereIn('id', $ids)
            ->get();

        if (!$somenteDemissional) {
            $this->preencherTipoOrdemLegado($exames);
        }

        return $exames->keyBy('feedback_id');
    }

    private function textoUltimoEncaminhamento(?ExameFuncionario $exame, bool $comTipo = false): string
    {
        if (!$exame) {
            return 'Sem encaminhamento';
        }

        $data = $exame->getRawOriginal('encaminhamento_data');
        if (!$data) {
            $data = $exame->getRawOriginal('created_at');
        }

        $texto = $data ? (new DataHora($data))->dataCompleta() : 'Sem encaminhamento';
        if (!$comTipo) {
            return $texto;
        }

        $tipo = trim((string) ($exame->ExameTipo?->label ?? $exame->getAttribute('tipo_exame_label') ?? ''));

        return $tipo !== '' ? $texto . ' - ' . $tipo : $texto;
    }

    /**
     * Encaminhamentos antigos guardam o tipo de ordem nas respostas do formulário.
     *
     * @param  \Illuminate\Support\Collection<int, ExameFuncionario>  $exames
     */
    private function preencherTipoOrdemLegado($exames): void
    {
        $semTipo = $exames->filter(fn (ExameFuncionario $exame) => !$exame->exame_tipo_id);
        $empresaId = auth()->user()?->empresa_id;
        if ($semTipo->isEmpty() || !$empresaId) {
            return;
        }

        $tipoOrdem = AlternativaFormulario::query()
            ->where('nome', 'Tipo de ordem')
            ->where('empresa_id', $empresaId)
            ->first();
        if (!$tipoOrdem) {
            return;
        }

        $chave = 'alternativa_id_' . $tipoOrdem->id;
        $valores = [];
        foreach ($semTipo as $exame) {
            $valor = $exame->respostas[$chave]['valor'] ?? null;
            if ($valor !== null && $valor !== '') {
                $valores[] = $valor;
            }
        }
        if ($valores === []) {
            return;
        }

        $labels = RespostaAlternativas::query()
            ->where('alternativa_id', $tipoOrdem->id)
            ->whereIn('value', array_values(array_unique($valores)))
            ->pluck('label', 'value');

        foreach ($semTipo as $exame) {
            $valor = $exame->respostas[$chave]['valor'] ?? null;
            $label = $labels[$valor] ?? $labels[(string) $valor] ?? '';
            if ($label !== '') {
                $exame->setAttribute('tipo_exame_label', $label);
            }
        }
    }

    public function getFichaPdf(Request $request, $exame, $token = null)
    {
        if (!$token) {
            abort(404);
        }

        $ExameFuncionario = ExameFuncionario::withoutGlobalScopes()
            ->with(['PcmsoDados' => function ($query) {
                $query->withoutGlobalScopes();
            }])->with(['Formulario' => function ($query) {
                $query->withoutGlobalScopes()->with(['Setores' => function ($q) {
                    $q->with('Alternativas');
                }]);
            }])->with(['EmpresaExame' => function ($query) {
                $query->withoutGlobalScopes();
            }])->with(['ExameTipo' => function ($query) {
                $query->withoutGlobalScopes();
            }])->with(['Feedback' => function ($query) {
                $query->withoutGlobalScopes()
                    ->with(['Curriculo' => function ($query) {
                        $query->withoutGlobalScopes();
                    }]);
            }])->whereId($exame)->whereToken($token)
            ->first();

        if (!$ExameFuncionario) {
            abort(404);
        }

        $ExameFuncionario->dados_empresa = Sistema::getEmpresaFilialMatriz(
            $ExameFuncionario->Feedback->centro_custo_filial_id,
            $ExameFuncionario->Feedback->empresa_id
        );

        $tipoexame = $request->tipo_exame
            ?? $ExameFuncionario->ExameTipo->label
            ?? 'Exame';

        $solicitante = auth()->guest() ? '' : (string) (auth()->user()?->nome ?? '');
        $identificacao = app(ColaboradorExameIdentificacao::class)->paraFeedback($ExameFuncionario->Feedback);

        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.controle-exames.ficha', compact('ExameFuncionario', 'identificacao'));
        app(ComunicadoDispensaSemJustaCausaPdfService::class)
            ->aplicarPadraoDocumento($pdf, $solicitante);

        return $pdf->stream('Exame ' . $tipoexame . ' ' . Str::slug($ExameFuncionario->Feedback->Curriculo->nome) . '.pdf');
    }

    /**
     * Envia ficha de encaminhamento para assinatura digital.
     */
    public function enviarFichaParaAssinatura(Request $request, AssinaturaDigitalService $service)
    {
        $request->validate([
            'exame_id' => 'required|integer',
            'signatarios' => 'required|array|min:1',
            'signatarios.*.email' => 'required|email',
            'signatarios.*.nome' => 'required|string|max:255',
            'signatarios.*.cpf' => 'nullable|string|max:14',
            'signatarios.*.user_id' => 'nullable|exists:users,id',
        ]);

        $ExameFuncionario = ExameFuncionario::with(['PcmsoDados' => fn ($q) => $q->withoutGlobalScopes()])
            ->with(['Formulario' => fn ($q) => $q->withoutGlobalScopes()->with(['Setores' => fn ($s) => $s->with('Alternativas')])])
            ->with(['EmpresaExame' => fn ($q) => $q->withoutGlobalScopes()])
            ->with(['ExameTipo' => fn ($q) => $q->withoutGlobalScopes()])
            ->with(['Feedback' => fn ($q) => $q->withoutGlobalScopes()->with(['Curriculo' => fn ($q) => $q->withoutGlobalScopes()])])
            ->whereId($request->exame_id)
            ->whereHas('Feedback', fn ($q) => $q->where('empresa_id', auth()->user()->empresa_id))
            ->first();

        if (!$ExameFuncionario) {
            return response()->json(['success' => false, 'message' => 'Exame não encontrado.'], 404);
        }

        $ExameFuncionario->dados_empresa = Sistema::getEmpresaFilialMatriz(
            $ExameFuncionario->Feedback->centro_custo_filial_id,
            $ExameFuncionario->Feedback->empresa_id
        );
        $tipoexame = $request->input('tipo_exame', 'Exame');
        if ($ExameFuncionario->exame_tipo_id) {
            $tipoexame = ExameTipo::find($ExameFuncionario->exame_tipo_id)->label ?? $tipoexame;
        }
        $identificacao = app(ColaboradorExameIdentificacao::class)->paraFeedback($ExameFuncionario->Feedback);

        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.controle-exames.ficha', compact('ExameFuncionario', 'identificacao'));
        app(ComunicadoDispensaSemJustaCausaPdfService::class)
            ->aplicarPadraoDocumento($pdf, (string) (auth()->user()?->nome ?? ''));
        $pdfContent = $pdf->output();

        $nomeArquivo = 'ficha_encaminhamento_' . Str::slug($ExameFuncionario->Feedback->Curriculo->nome ?? 'documento') . '.pdf';
        $empresaId = auth()->user()->empresa_id;
        $cliente = Cliente::withoutGlobalScopes()->find($empresaId);
        $apelido = $cliente && $cliente->apelido ? $cliente->apelido : 'empresa';
        try {
            app(\App\Services\AssinaturaDigital\AssinaturaCotaService::class)->validarDisponibilidadeOrFail($empresaId);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $doc = $service->criarEnvio(
            $empresaId,
            'ficha_encaminhamento',
            ExameFuncionario::class,
            $ExameFuncionario->id,
            auth()->id(),
            $request->signatarios,
            'sequencial',
            $pdfContent,
            $nomeArquivo,
            null
        );

        $baseUrl = rtrim(config('app.url'), '/') . '/' . $apelido . '/assinatura/';
        $links = $doc->signatarios->map(fn ($s) => ['email' => $s->email, 'link' => $baseUrl . $s->token]);

        return response()->json([
            'success' => true,
            'message' => 'Ficha enviada para assinatura.',
            'documento_id' => $doc->id,
            'links' => $links,
        ]);
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_CONTROLE_EXAMES_RESULTADO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CONTROLE_EXAMES_RESULTADO, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CONTROLE_EXAMES_RESULTADO, $arquivo);
    }

//anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CONTROLE_EXAMES_RESULTADO, $arquivo);
    }
}
