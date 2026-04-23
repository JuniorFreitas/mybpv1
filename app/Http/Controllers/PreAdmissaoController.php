<?php

namespace App\Http\Controllers;

use App\Classes\ZapNotificacao;
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
        $feedback = FeedbackCurriculo::select(['id', 'curriculo_id', 'vaga_id', 'vaga_projeto_id', 'vagas_abertas_id', 'telefone_id'])
            ->whereId($feedback)
            ->first()
            ->load('Curriculo:id,nome,cpf,email,nascimento,rg,orgao_expeditor,logradouro,complemento,bairro,municipio,uf', 'TelPrincipal');

        $feedback->docs_curriculo_pre_adm = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(auth()->user()->empresa_id)
            ->transform(function ($doc) use ($feedback) {
                $doc->docs_curriculo_anexos = DB::table('documentos_curriculos')
                    ->whereTipo($doc->tipo)
                    ->where('curriculo_id', $feedback->curriculo_id)
                    ->join('arquivos', 'arquivos.id', '=', 'documentos_curriculos.arquivo_id')
                    ->get()->transform(function ($doc) {
                        $doc->url = "";
                        $doc->url_download = "";
                        if (in_array($doc->disco, Arquivo::LISTAGEM_DISCOS)) {
                            $doc->url = config('filesystems.disks.' . $doc->disco . '.urlShow') . "/{$doc->file}";
                            $doc->urlDownload = config('filesystems.disks.' . $doc->disco . '.urlDownload') . "/{$doc->file}";
                            $doc->urlThumb = config('filesystems.disks.' . $doc->disco . '.urlThumb') . "/{$doc->file}";
                        };
                        return $doc;
                    });
                $doc->qnt_anexos = count($doc->docs_curriculo_anexos);
                return $doc;
            });

        return $feedback;
    }

    public function showFinalizar($feedback)
    {
        $feedback = FeedbackCurriculo::select(['id', 'curriculo_id', 'vaga_id', 'vaga_projeto_id', 'vagas_abertas_id', 'telefone_id'])
            ->whereId($feedback)
            ->first()
            ->load('Curriculo:id,nome,cpf,email,nascimento,rg,orgao_expeditor,logradouro,complemento,bairro,municipio,uf', 'VagaAberta.Vaga', 'TelPrincipal');

        $feedback->docs_curriculo_pre_adm = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(auth()->user()->empresa_id)
            ->transform(function ($doc) use ($feedback) {
                $doc->docs_curriculo_anexos = DB::table('documentos_curriculos')
                    ->whereTipo($doc->tipo)
                    ->where('curriculo_id', $feedback->curriculo_id)
                    ->join('arquivos', 'arquivos.id', '=', 'documentos_curriculos.arquivo_id')
                    ->get()->transform(function ($doc) {
                        $doc->url = "";
                        $doc->url_download = "";
                        if (in_array($doc->disco, Arquivo::LISTAGEM_DISCOS)) {
                            $doc->url = config('filesystems.disks.' . $doc->disco . '.urlShow') . "/{$doc->file}";
                            $doc->urlDownload = config('filesystems.disks.' . $doc->disco . '.urlDownload') . "/{$doc->file}";
                            $doc->urlThumb = config('filesystems.disks.' . $doc->disco . '.urlThumb') . "/{$doc->file}";
                        };
                        return $doc;
                    });
                $doc->qnt_anexos = count($doc->docs_curriculo_anexos);
                return $doc;
            });

        $pcmso = Pcmso::whereAtivo(true)->get();
        $empresas_exames = EmpresaExame::whereAtivo(true)->get();

        return response()->json([
            'empresas_exames' => $empresas_exames,
            'pcmsos' => $pcmso,
            'dados' => $feedback
        ]);
    }


    public function atualizar(Request $request)
    {

        $resultado = FeedbackCurriculo::select(
            ['id', 'curriculo_id', 'vaga_id', 'vaga_projeto_id', 'vagas_abertas_id', 'telefone_id']
        )->with(['Curriculo' => function ($model) {
            $model->select(['id', 'nome', 'cpf', 'email', 'nascimento', 'rg', 'orgao_expeditor', 'logradouro', 'complemento', 'bairro', 'municipio', 'uf']);
        }])->with(['TelPrincipal']);

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('nome', 'like', '%' . $request->campoBusca . '%')
                        ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                        ->orWhere('id', $request->campoBusca);
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

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        $items = collect($resultado->items())->transform(function ($item) {
            $docs_curriculo_pre_adm = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(auth()->user()->empresa_id)
                ->transform(function ($doc) use ($item) {
                    $doc->docs_curriculo_anexos = DB::table('documentos_curriculos')
                        ->whereTipo($doc->tipo)
                        ->where('curriculo_id', $item->curriculo_id)
                        ->join('arquivos', 'arquivos.id', '=', 'documentos_curriculos.arquivo_id')
                        ->get()->transform(function ($doc) {
                            $doc->url = "";
                            $doc->url_download = "";
                            if (in_array($doc->disco, Arquivo::LISTAGEM_DISCOS)) {
                                $doc->url = config('filesystems.disks.' . $doc->disco . '.urlShow') . "/{$doc->file}";
                                $doc->url_download = config('filesystems.disks.' . $doc->disco . '.urlDownload') . "/{$doc->file}";
                            };
                            return $doc;
                        });
                    $doc->qnt_anexos = count($doc->docs_curriculo_anexos);
                    return $doc;
                });
            $item->docs_curriculo_pre_adm = $docs_curriculo_pre_adm;
            $item->qnt_anexos = $docs_curriculo_pre_adm->sum('qnt_anexos');

            $estaFinalizado = DB::table('feedback_preadmissao')->where('feedback_id', $item->id)->first();
            if ($estaFinalizado) {
                $user_finalizou = DB::table('users')->where('id', $estaFinalizado->user_finalizou_id)->first()->nome;
                $item->finalizado = true;
                $item->quem_finalizou = $user_finalizou;
                $item->data_finalizacao = (new DataHora($estaFinalizado->created_at))->dataHoraCompleta();
            }
            return $item;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['items' => $items, 'usuario_cliente_id' => auth()->user()->cliente_id, 'email_padrao' => Sistema::EMAILPADRAO]
        ]);
    }

    public function edit(FeedbackCurriculo $feedback)
    {
        return $feedback->load('Curriculo.Pessoa', 'TelPrincipal');
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
            $data_realizacao = (new DataHora($request->encaminhamento_data))->dataCompleta();

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
                if (auth()->user()->EmpresaConfiguracoes->envia_whatsapp && $colaborador->TelPrincipal->tipo == 'whatsapp' && !is_null($empExame)) {
                    $mensagem = "Prezado(a) sr(a) *{$colaborador->Curriculo->nome}*, Tudo bem?\n\nEstamos encaminhando para realização de *Exame de ordem *{$tipoExame->label}*, " .
                        "no primeiro dia útil após recebimento dessa notificação (considerar de segunda à sábado).\n\n" .
                        "🏥 Local do Exame: \n*{$empExame->nome}*.\n" .
                        "📍 Endereço: *{$empExame->dados['endereco']['endereco_completo']}*\n" .
                        "📞 Contato: *{$empExame->dados['telefone']}*\n" .
                        "🗓️ Data de encaminhamento: *{$data_encaminhamento}*\n" .
                        "🗓️ Data de realização: *{$data_realizacao}*" .
                        "\n\n" .
                        "Atenciosamente,\n\n" .
                        "Equipe " . auth()->user()->Empresa->razao_social . "\n\n" .
                        "_Esta mensagem foi enviada automaticamente pela plataforma *MyBP*, por favor não responda._";

                    (new ZapNotificacao())->enviar([
                        'enviado_id' => $colaborador->curriculo_id,
                        'telefone' => $colaborador->TelPrincipal->sonumero,
                        'mensagem' => $mensagem
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
                    'url_documento' => env('APP_URL') . "/" . auth()->user()->Empresa->apelido . "/documentos",
                ]);
            }

            if (auth()->user()->enviaWhatsApp() && $dados['temwhatsapp'] && $dados['envia_whatsapp'] && $dados['numero_telefone']) {
                $ambiente = env('AMBIENTE', 'local') == 'prod' ?: 'local';
                if ($ambiente != 'prod') {
                    $zapTelAtivo = \DB::table("zap_numeros")->where("ativo", true)->first();
                    $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '559899023762';
                }

                if (auth()->user()->empresa_id == 65974) {
                    //Equatorial
                    $mensagemWhats = "Olá, " . $curriculo->nome . "!\n\n";
                    $mensagemWhats .= $dados['observacao'] . "\n\n";
                    $mensagemWhats .= "Atenciosamente,\n";
                    $mensagemWhats .= "*Time Recrutamento e Seleção $Empresa->nome_fantasia*\n";

                    if (strlen($dados['observacao']) == 0) {
                        $mensagemWhats = '';
                    }
                } else {
                    //Todas
                    $mensagemWhats = "Olá, " . $curriculo->nome . "!\n\n";
                    $mensagemWhats .= "Parabéns por chegado até esta etapa! Você foi aprovado(a) na etapa de entrevista e seleção e agora vamos ";
                    $mensagemWhats .= "para a etapa de documentos para admissão.!\n\n";
                    $mensagemWhats .= "Para continuidade no processo, segue o link abaixo para que seja anexado os documentos conforme descrição.\n\n";
                    $mensagemWhats .= "Link: " . env('APP_URL') . "/" . auth()->user()->Empresa->apelido . "/documentos\n\n";

                    $mensagemWhats .= "Destaca-se que é muito importante que todos os documentos sejam anexados corretamente ";
                    $mensagemWhats .= "para que possamos dar continuidade no processo de admissão.\n\n";

                    $mensagemWhats .= "\n\nAtenciosamente,\n";
                    $mensagemWhats .= "*Time Recrutamento e Seleção $Empresa->nome_fantasia*\n";
                }

                if (strlen($mensagemWhats) > 0) {
                    (new ZapNotificacao())->enviar([
                        'enviado_id' => 1,
                        'telefone' => preg_replace('/[^0-9]/', '', $dados['numero_telefone']),
                        'mensagem' => $mensagemWhats,
                    ]);
                }
            }


            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::debug("Erro ao enviar e-mail: " . $e->getMessage());
            return response()->json(['msg' => 'Erro ao enviar e-mail', 'erros' => $e->getMessage()], 400);
        }
    }
}
