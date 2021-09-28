<?php

namespace App\Http\Controllers;

use App\Mail\ControleExames\FichaClinicaMail;
use App\Mail\ControleExames\FichaColaboradorMail;
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
//                    'email' => trim(mb_strtolower($colaborador->Curriculo->email)),
                    'email' => trim(mb_strtolower('atendimento@mybp.com.br')),
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

    }


    public function salvaResultado(Request $request)
    {

        $dados = $request->input();
        try {
            \DB::beginTransaction();

            $dados['data_vencimento'] = (new DataHora($dados['data_realizacao']))->addAno(1);
            $dados['vencido'] = false;
            Examesesmt::create($dados);

            \DB::commit();
            return response()->json("");

            if ($request->tipo == 'store') {

                /*
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
//                    'email' => trim(mb_strtolower($colaborador->Curriculo->email)),
                    'email' => trim(mb_strtolower('atendimento@mybp.com.br')),
                    'assunto' => "Encaminhamento de Exame {$tipoExame}",
                    'colaborador' => $colaborador->Curriculo->nome,
                    'tipoExame' => $tipoExame,
                    'link' => route('publico.encaminhamento_exame_fichapdf', ['exame' => $exame, 'token' => $token])
                ];

                \Mail::send(new FichaColaboradorMail($dtEmailColaborador));

                \DB::commit();
                return response()->json("");*/
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

    public function atualizar(Request $request)
    {
        $resultado = FeedbackCurriculo::select(['id', 'cliente_id', 'curriculo_id', 'telefone_id', 'vaga_id'])->with(
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
            'Cliente:id,razao_social,area_id',
            'vagaSelecionada',
            'TelPrincipal')
            ->whereHas('ResultadoIntegrado', function ($q) {
                $q->whereEncaminhadoExame(true);
            })
//            ->join('curriculos.id', 'feedback_curriculos.curriculo_id')
            ->orderBy(Curriculo::select('nome')
                ->whereColumn('curriculos.id', 'feedback_curriculos.curriculo_id')
                ->latest()
                ->take(1));
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
        $pdf = PDF::loadView('pdf.controle-exames.ficha', compact('exame'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("Exame {$tipoexame} " . Str::slug($exame->Feedback->Curriculo->nome) . ".pdf");
    }

    public function exportExcel()
    {
    }
}
