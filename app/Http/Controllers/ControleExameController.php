<?php

namespace App\Http\Controllers;

use App\Classes\ZapNotificacao;
use App\Jobs\ControleExames\JobExame;
use App\Jobs\Entrevista\ResultadoIntegrado\JobEncaminhamentoExame;
use App\Models\Admissao;
use App\Models\AlternativaFormulario;
use App\Models\Arquivo;
use App\Models\Curriculo;
use App\Models\EmpresaExame;
use App\Models\ExameFuncionario;
use App\Models\Examesesmt;
use App\Models\ExameTipo;
use App\Models\FeedbackCurriculo;
use App\Models\Formulario;
use App\Models\Pcmso;
use App\Models\RespostaAlternativas;
use App\Models\Sistema;
use App\Models\User;
use App\Scopes\ScopeClientesEmpresa;
use App\Scopes\ScopeEmpresa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

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
                    $item->tipo_exame = RespostaAlternativas::whereValue($item->respostas['alternativa_id_' . $tipoOrdem]['valor'])->first()->label;
                } else {
                    $item->tipo_exame = ExameTipo::find($item->exame_tipo_id)->label;
                }
                $item->encaminhamento_data = is_null($item->encaminhamento_data) ? (new DataHora($item->created_at))->dataCompleta() : (new DataHora($item->encaminhamento_data))->dataCompleta();
                $item->pcmso_label = $item->pcmso ? $item->PcmsoDados->label : 'Não se aplica';
                $item->resultado = Examesesmt::whereExameFuncionarioId($item->id)->first();
                return $item;
            });

            $pcmos = Pcmso::whereAtivo(true)->get();
            $exame_tipos = ExameTipo::whereAtivo(true)->get();

            return [
                'tipo' => 'cadastrar',
                'historico' => $resposta,
                'pcmsos' => $pcmos,
                'exame_tipos' => $exame_tipos,
            ];
        } else {
            return response()->json(['msg' => "Erro -> Faltando parametros"], 400);
        }
    }

    public function salvaUpdate(Request $request)
    {
        try {
            \DB::beginTransaction();
            $token = Sistema::uuid();

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
                        'encaminhamento_data' => (new DataHora($request->encaminhamento_data))->dataInsert()
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
                        'encaminhamento_data' => (new DataHora($request->encaminhamento_data))->dataInsert(),
                        'exame_tipo_id' => (int)$tipoExame->value,
                    ]);
                }

                $colaborador = FeedbackCurriculo::select(['curriculo_id', 'id','telefone_id'])->find($request->feedback_id);

                if ($request->envia_email) {
                    $dtEmailClinica = [
                        'clinica' => $empExame->nome,
                        'email' => trim(mb_strtolower($empExame->dados['email'])),
                        'assunto' => "Encaminhamento de Exame {$tipoExame->label} colaborador {$colaborador->Curriculo->nome}",
                        'colaborador' => $colaborador->Curriculo->nome,
                        'colaborador_email' => trim(mb_strtolower($colaborador->Curriculo->email)),
                        'idade' => $colaborador->Curriculo->idade,
                        'tipoExame' => $tipoExame->label,
                        'empresa_id' => $empExame->id,
                        'link' => route('publico.encaminhamento_exame_fichapdf', ['exame' => $exame, 'token' => $token])
                    ];

                    $dtEmailColaborador = [
                        'clinica' => $empExame,
                        'email' => trim(mb_strtolower($colaborador->Curriculo->email)),
                        'assunto' => "Encaminhamento de Exame {$tipoExame->label}",
                        'colaborador' => $colaborador->Curriculo->nome,
                        'tipoExame' => $tipoExame->label,
                        'empresa_id' => $empExame->id,
                        'link' => route('publico.encaminhamento_exame_fichapdf', ['exame' => $exame, 'token' => $token])
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
                            "📞 Contato: *{$empExame->dados['telefone']}*" .
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
            $msg = "Erro ao Encaminhar para exame:  {$e->getMessage()} , CODIGO:  {$e->getCode()}, Linha: {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            Sistema::LogFormatado($request->input());
            \DB::rollback();
            return response()->json(['msg' => $msg,
                'request' => $request->input(),
            ], 400);
            return response()->json(['msg' => 'Houve um erro ao encaminhar!'], 400);
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
        $resultado = FeedbackCurriculo::select(['id', 'curriculo_id', 'vaga_id', 'telefone_id', 'vagas_abertas_id', 'vaga_projeto_id', 'empresa_id'])->with(
            'Curriculo:id,nome,nascimento,id,nome,email,nascimento,rg,orgao_expeditor,logradouro,cep,end_numero,complemento,bairro,municipio,uf',
            'Cliente:id,razao_social,area_id',
            'VagaAberta',
            'TelPrincipal');

        if ($request->filled('status')) {
            if ($request->status == 'em_processo') {
                $resultado->whereHas('ResultadoIntegrado', function ($q) {
                    $q->whereEncaminhadoExame(true);
                })->whereDoesntHave('Admissao')->whereDoesntHave('Demissao')->orWhereHas('Admissao', function ($q) {
                    $q->whereNotIn('status', [Admissao::STATUS_ADMISSAO_ADMITIDO, Admissao::STATUS_ADMISSAO_DESISTENCIA]);
                });
            }
            if ($request->status == 'admitidos') {
                $resultado->whereHas('ResultadoIntegrado', function ($q) {
                    $q->whereEncaminhadoExame(true);
                })->whereHas('Admissao', function ($q) {
                    $q->where('status', Admissao::STATUS_ADMISSAO_ADMITIDO);
                })->whereDoesntHave('Demissao');
            }
            if ($request->status == 'demitidos') {
                $resultado->whereHas('ResultadoIntegrado', function ($q) {
                    $q->whereEncaminhadoExame(true);
                })->demitidos();
            }
        }

        $filtroPeriodo = $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                $q->where('created_at', '>=', $dataInicio->dataInsert())->where('created_at', '<=', $dataFim->dataInsert());
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

        $items = collect($resultado->items())->transform(function ($item) {
            $exameFuncionario = ExameFuncionario::whereFeedbackId($item->id)->orderByDesc('id')->first();
            $item->ultimo_encaminhamento = 'Sem encaminhamento';
            if (!is_null($exameFuncionario)) {
                $item->ultimo_encaminhamento = is_null($exameFuncionario->encaminhamento_data) ? (new DataHora($exameFuncionario->created_at))->dataCompleta() : $exameFuncionario->encaminhamento_data;
                return $item;
            }
            return $item;
        });

        $empresaExames = EmpresaExame::whereAtivo(true)->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $items, 'emp_exames' => $empresaExames]
        ]);
    }

    public function getFichaPdf(Request $request, $exame, $token = null)
    {
        if (!$token) {
            abort(404);
        }

        if ($token) {
            $ExameFuncionario = ExameFuncionario::withoutGlobalScopes()
                ->with(['PcmsoDados' => function ($query) {
                    $query->withoutGlobalScopes();
                }])->with(['Formulario' => function ($query) {
                    $query->withoutGlobalScopes();
                }])->with(['EmpresaExame' => function ($query) {
                    $query->withoutGlobalScopes();
                }])->with(['Feedback' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->with(['Curriculo' => function ($query) {
                            $query->withoutGlobalScopes();
                        }]);
                }])->whereId($exame)->whereToken($request->token)
                ->first();
        }

        if (!$ExameFuncionario) {
            abort(404);
        }

        $ExameFuncionario->dados_empresa = Sistema::getEmpresaFilialMatriz($ExameFuncionario->Feedback->centro_custo_filial_id, $ExameFuncionario->Feedback->empresa_id);

        $tipoexame = $request->tipo_exame;
        $pdf = PDF::loadView('pdf.controle-exames.ficha', compact('ExameFuncionario'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("Exame {$tipoexame} " . Str::slug($ExameFuncionario->Feedback->Curriculo->nome) . ".pdf");
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
