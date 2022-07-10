<?php

namespace App\Http\Controllers;

use App\Mail\ControleExames\FichaClinicaMail;
use App\Mail\ControleExames\FichaColaboradorMail;
use App\Models\Arquivo;
use App\Models\Curriculo;
use App\Models\EmpresaExame;
use App\Models\ExameFuncionario;
use App\Models\Examesesmt;
use App\Models\FeedbackCurriculo;
use App\Models\Formulario;
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
                ->with(
                    'EmpresaExame:id,nome',
                    'QuemEncaminhou:id,nome',
                )
                ->with('Formulario.Setores.Alternativas.Opcoes')
                ->orderBy('created_at')->get();

            $resposta->transform(function ($item) {
                $item->tipo_exame = RespostaAlternativas::find($item->respostas['alternativa_id_24']['valor'])->label;
                return $item;
            });

            return [
                'tipo' => 'cadastrar',
                'historico' => $resposta
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
                $exame = ExameFuncionario::create([
                    'feedback_id' => $request->feedback_id,
                    'empresa_exame_id' => $request->empresa_exame_id,
                    'formulario_id' => $request->formulario_id,
                    'respostas' => $request->respostas,
                    'token' => $token
                ]);

                $empExame = EmpresaExame::find($request->empresa_exame_id);
                $tipoExame = RespostaAlternativas::find($request->respostas['alternativa_id_24']['valor'])->label;
                $colaborador = FeedbackCurriculo::select('curriculo_id', 'id')->find($request->feedback_id);

                $dtEmailClinica = [
                    'clinica' => $empExame->nome,
                    'email' => trim(mb_strtolower($empExame->dados['email'])),
                    'assunto' => "Encaminhamento de Exame {$tipoExame} colaborador {$colaborador->Curriculo->nome}",
                    'colaborador' => $colaborador->Curriculo->nome,
                    'colaborador_email' => $colaborador->Curriculo->email,
                    'idade' => $colaborador->Curriculo->idade,
                    'tipoExame' => $tipoExame,
                    'link' => route('publico.encaminhamento_exame_fichapdf', ['exame' => $exame, 'token' => $token])
                ];

                \Mail::send(new FichaClinicaMail($dtEmailClinica));

                $dtEmailColaborador = [
                    'clinica' => $empExame,
                    'email' => trim(mb_strtolower($colaborador->Curriculo->email)),
//                    'email' => trim(mb_strtolower('atendimento@mybp.com.br')),
                    'assunto' => "Encaminhamento de Exame {$tipoExame}",
                    'colaborador' => $colaborador->Curriculo->nome,
                    'tipoExame' => $tipoExame,
                    'link' => route('publico.encaminhamento_exame_fichapdf', ['exame' => $exame, 'token' => $token])
                ];

                \Mail::send(new FichaColaboradorMail($dtEmailColaborador));

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
            $msg = "Erro ao Encaminhar para exame:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            \DB::rollback();
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

        $dadosValidados = \Validator::make($dados, []);

        if ($dados['exame_realizado']) {
            $dadosValidados = \Validator::make($dados, [
                'data_realizacao' => 'required_if:exame_realizado,1|date_format:d/m/Y',
                'resultado.result' => 'required_if:exame_realizado,1|in:Apto,Apto com Restrição,Inapto',
                'resultado.pendencias' => 'required_if:exame_realizado,1|in:Sim,Não',
                'resultado.pendencias_quais' => 'required_if:exame_realizado,1|required_if:resultado.pendencias,Sim',
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
            Examesesmt::create($dados);
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
                'resultado.pendencias_quais' => 'required_if:exame_realizado,1|required_if:resultado.pendencias,Sim',
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
            $resultado->update($dados);
            \DB::commit();
            return response()->json([], 201);
        } catch (\ErrorException $e) {
            \DB::rollback();
            $msg = "Erro ao Atualizar o resultado do exame para exame:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json($msg, 400);
            return response()->json(['msg' => 'Houve um erro ao atualizar o resultado do exame!'], 400);
        }
    }


    public function atualizar(Request $request)
    {
        $resultado = FeedbackCurriculo::whereHas('ResultadoIntegrado', function ($q) {
            $q->whereEncaminhadoExame(true);
        })->select(['id', 'cliente_id', 'curriculo_id', 'telefone_id', 'vaga_id'])->with(
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
            'Cliente:id,razao_social,area_id',
            'vagaSelecionada',
            'TelPrincipal');
        $resultado = $resultado->paginate($request->pages);

        $empresaExames = EmpresaExame::whereAtivo(true)->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $resultado->items(), 'emp_exames' => $empresaExames]
        ]);
    }

    public function getFichaPdf(Request $request, ExameFuncionario $exame, $token = null)
    {
        if ($token) {
            $exame = ExameFuncionario::withoutGlobalScope(new ScopeEmpresa())
                ->with(['Formulario' => function ($query) {
                    $query->withoutGlobalScope(new ScopeEmpresa());
                }])->with(['EmpresaExame' => function ($query) {
                    $query->withoutGlobalScope(new ScopeEmpresa());
                }])->with(['Feedback' => function ($query) {
                    $query->withoutGlobalScope(ScopeClientesEmpresa::class)
                        ->with(['Curriculo' => function ($query) {
                            $query->withoutGlobalScope(new ScopeEmpresa());
                        }]);
                }])->whereId($exame->id)->whereToken($request->token);

            if ($exame->count() == 0) {
                abort(404);
            } else {
                $exame = $exame->first();
            }
        }

        $tipoexame = $request->tipo_exame;
//        return view('pdf.controle-exames.ficha', compact('exame'));
        $pdf = PDF::loadView('pdf.controle-exames.ficha', compact('exame'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("Exame {$tipoexame} " . Str::slug($exame->Feedback->Curriculo->nome) . ".pdf");
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_CONTROLE_EXAMES);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CONTROLE_EXAMES, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CONTROLE_EXAMES, $arquivo);
    }

//anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CONTROLE_EXAMES, $arquivo);
    }
}
