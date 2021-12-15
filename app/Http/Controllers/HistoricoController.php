<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\AvaliacaoNoventaDias;
use App\Models\AvaliacaoNoventaFeedback;
use App\Models\AvaliacaoNoventaFeedbackQuantidade;
use App\Models\FeedbackCurriculo;
use App\Models\MedidaAdministrativa;
use App\Models\User;
use App\Models\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

class HistoricoController extends Controller
{
    public function index()
    {
        return view('g.admissao.historico.index');
    }

    public function show(Request $request, $feedback)
    {
        $feedback_id = $feedback;

        $feedback = FeedbackCurriculo::select('id')->whereId($feedback_id)->with('MedidasAdministrativas.Anexos')->first();
        $perguntas = AvaliacaoNoventaDias::get()->transform(function ($item) {
            $item->nota = '';
            return $item;
        });
        $tabelaNoventa = AvaliacaoNoventaFeedbackQuantidade::with('Feedback')->whereFeedbackId($feedback_id)->get();

        return response()->json([
            'feedback' => $feedback,
            'causas' => MedidaAdministrativa::CAUSAS,
            'definicao' => MedidaAdministrativa::DEFINICAO,
            'tipos' => MedidaAdministrativa::TIPOS,
            'perguntas' => $perguntas,
            'tabelaNoventa' => $tabelaNoventa,
            'hoje' => (new DataHora())->dataCompleta()
        ], 200);
    }

    public function atualizar(Request $request)
    {
//        $resultado = Admissao::with(['Feedback' => function ($q) {
//            $q->with('Curriculo', 'Cliente:id,nome,razao_social,cpf,cnpj,nome_fantasia', 'VagaSelecionada:id,nome');
//        }])->whereIn('status', ['ADMITIDO']);

        $resultado = FeedbackCurriculo::whereHas('Admissao', function ($q) {
            $q->whereIn('status', ['ADMITIDO']);
        })->with('Admissao', 'Curriculo', 'Cliente:id,nome,razao_social,cpf,cnpj,nome_fantasia', 'VagaSelecionada:id,nome');
        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%');
            });
        }
        if ($request->filled('campoCargo')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->where('cargo', 'like', '%' . $request->campoCargo . '%');
            });
        }
        $cargos = Vaga::whereAtivo(true)->orderBy('nome')->get(['id', 'nome']);

     /*   $idsCargos = DB::table('feedback_curriculos')->distinct('vaga_id')->pluck('vaga_id');

        $cargos = [];
        foreach ($idsCargos as $id) {
            $cargos[]=[
                'id' => $id,
                'nome' => Vaga::find($id)->nome
                ];
        }*/

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'cargos' => $cargos,
            ]
        ]);
    }

    //************MEDIDAS ADMINISTRATIVAS**************//
    public function storeMedidas(Request $request, $feedback)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'medidas_administrativas.*.solicitante' => 'required',
            'medidas_administrativas.*.tipo' => 'required',
            'medidas_administrativas.*.causa' => 'required',
            'medidas_administrativas.*.definicao' => 'required',
            'medidas_administrativas.*.motivo' => 'required',
            'medidas_administrativas.*.data_solicitacao' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                //todo Verifica se ja existe a pessoa se existir so da um increment
                DB::beginTransaction();
                foreach ($dados['medidas_administrativas'] as $medida) {
                    $medida['user_id'] = auth()->id();
                    $medidaAdm = MedidaAdministrativa::create($medida);
                    //Remove a foto de anexo

                    if (isset($medida['anexosDel'])) {
                        foreach ($medida['anexosDel'] as $id_anexo) {
                            $arquivo = Arquivo::find($id_anexo);
                            $arquivo->excluir();
                        }
                    }

                    // inseri uma nova foto de anexo
                    if (isset($medida['anexos'])) {
                        foreach ($medida['anexos'] as $index => $anexo) {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                $medidaAdm->Anexos()->attach($arquivo->id);
                            }
                        }
                    }

                }
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE MEDIDAS ADMINISTRATIVAS:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    public function updateMedidas(Request $request, $feedback)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
//            'data_inicio' => 'required',
//            'data_fim' => 'required',
//            'empresa_treinamento_id' => 'required',
//            'treinamento_sgi_id' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                foreach ($dados['medidas_administrativas'] as $medida) {
                    $medida['user_id'] = auth()->id();
                    if (!isset($medida['id'])) {
                        $medidaSingle = MedidaAdministrativa::create($medida);

                        if (isset($medida['anexosDel'])) {
                            foreach ($medida['anexosDel'] as $id_anexo) {
                                $arquivo = Arquivo::find($id_anexo);
                                $arquivo->excluir();
                            }
                        }

                        // inseri uma nova foto de anexo
                        if (isset($medida['anexos'])) {
                            foreach ($medida['anexos'] as $index => $anexo) {
                                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                if ($arquivo) {
                                    $arquivo->temporario = false;
                                    $arquivo->chave = '';
                                    $arquivo->save();
                                    $medidaSingle->Anexos()->attach($arquivo->id);
                                }
                            }
                        }

                    } else {
                        $medidaSingle = MedidaAdministrativa::find($medida['id']);
                        if (isset($medida['anexosDel'])) {
                            foreach ($medida['anexosDel'] as $id_anexo) {
                                $arquivo = Arquivo::find($id_anexo);
                                $arquivo->excluir();
                            }
                        }

                        // inseri uma nova foto de anexo
                        if (isset($medida['anexos'])) {
                            foreach ($medida['anexos'] as $index => $anexo) {
                                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                if ($arquivo) {
                                    $arquivo->temporario = false;
                                    $arquivo->chave = '';
                                    $arquivo->save();
                                    $medidaSingle->Anexos()->attach($arquivo->id);
                                }
                            }
                        }

                        $medidaSingle->update($medida);
                    }
                }
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE MEDIDAS ADMINISTRATIVAS:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    public function medidasAdministrativasPDF($medida, $feedback_id)
    {
        $medida = MedidaAdministrativa::whereId($medida)->with('Feedback');

        if ($medida->count() == 0) {
            return abort(404);
        } else {
            $medida = $medida->with(
                'Anexos',
                'Feedback:id,curriculo_id,cliente_id',
                'Feedback.Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento',
                'Feedback.Cliente:id,cnpj,razao_social,nome_fantasia,cep,logradouro,numero,complemento,bairro,municipio,uf,contato',
                'Feedback.Cliente.Logo:id,nome,layout,imagem,file,thumb',
                'Feedback.Admissao:curriculo_id,data_admissao'
            )->first();

            $pdf = PDF::loadView('pdf.admissao.historico.medidasadministrativas.carta-advertencia', compact('medida'));
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream("carta_" . Str::slug($medida->tipo) . (new DataHora())->nomeUnico() . ".pdf");
        }
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_SERVICO_FORNECEDOR);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_OCORRENCIA, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_OCORRENCIA, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_OCORRENCIA, $arquivo);
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

                $avaliacao = AvaliacaoNoventaFeedbackQuantidade::whereFeedbackId($dados['feedback_id'])->get('quantidade_avaliacao')->count();


                $qntAvaliacao = $avaliacao > 0 ? intval($avaliacao['quantidade_avaliacao']) + 1 : 1;

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
