<?php

namespace App\Http\Controllers;

use App\Exports\treinamentoExport;
use App\Models\Arquivo;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\ResultadoIntegrado;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PortariaController extends Controller
{
    public function index()
    {
        return view('g.portaria.index');
    }

    public function edit($resultado)
    {
        $feedback = $resultado;

        $resultado = ResultadoIntegrado::whereFeedbackId($resultado)->first();

        $resultado->load('Feedback.Curriculo.FotoTres','Feedback.Admissao');

        $resultado->funcao = $resultado->Admissao ? $resultado->Admissao->funcao : '';
        $resultado->Feedback->Curriculo->foto_tresDel = [];

        $resultado->Feedback->Curriculo->autocomplete_label_municipio_modal = $resultado->Feedback->Curriculo->Cidade ? $resultado->Feedback->Curriculo->Cidade->nome . ' - ' . $resultado->Feedback->Curriculo->Cidade->uf : '';
        $resultado->Feedback->Curriculo->autocomplete_label_municipio_modal_anterior = $resultado->Feedback->Curriculo->Cidade ? $resultado->Feedback->Curriculo->Cidade->nome . ' - ' . $resultado->Feedback->Curriculo->Cidade->uf : '';

        $resultado->Feedback->autocomplete_label_vaga_modal = $resultado->Feedback->VagaSelecionada->nome;
        $resultado->Feedback->autocomplete_label_vaga_modal_anterior = $resultado->Feedback->VagaSelecionada->nome;
        $resultado->Feedback->autocomplete_label_cliente_modal = $resultado->Feedback->Empresa->razao_social . ' | ' . $resultado->Feedback->Empresa->cnpj;
        $resultado->Feedback->autocomplete_label_cliente_modal_anterior = $resultado->Feedback->Empresa->razao_social . ' | ' . $resultado->Feedback->Empresa->cnpj;

        return $resultado;

    }

    public function update(Request $request,FeedbackCurriculo $resultado)
    {
        $this->authorize('portaria_insert');

        $dados = $request->input();
        $curriculo = $dados['feedback']['curriculo'];

        $curriculo['uf_vaga'] = substr($curriculo['autocomplete_label_municipio_modal'], -2, 2);
        $feedback = $dados['feedback'];

        try {
            \DB::beginTransaction();

            $resultado->Curriculo->update($curriculo);

            $resultado->update($feedback);

            //Remove a foto de anexo
            if (isset($curriculo['foto_tresDel'])) {
                foreach ($curriculo['foto_tresDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }
            // inseri uma nova foto de anexo
            if (isset($curriculo['foto_tres'])) {
                foreach ($curriculo['foto_tres'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $resultado->Curriculo->FotoTres()->attach($arquivo->id, ['tipo' => 'foto3x4']);
                    }
                }
            }

            if ($resultado->Admissao) {
                $resultado->Admissao->update(['funcao' => $dados['funcao']]);
            } else {
                $resultado->Admissao()->create([
                    'funcao' => $dados['funcao']
                ]);
            }
            \DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            $msg = "error PORTARIA UPDATE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
//            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function atualizar(Request $request)
    {

        $resultado = FeedbackCurriculo::whereHas('ResultadoIntegrado',function ($q){
            $q->whereEncaminhadoTreinamento(true);
        })->with(
            'Curriculo.FotoTres:id',
            'VagaSelecionada:id,nome',
        );

        /*$resultado = ResultadoIntegrado::whereEncaminhadoTreinamento(true)->with(
            'Admissao',
            'Feedback.Curriculo.FotoTres',
            'Feedback.VagaSelecionada:id,nome',
            'Feedback.Cliente:id,nome_fantasia,nome'
        );*/

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaSelecionada', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->whereUfVaga($request->campoUf);
            });
        }

        if ($request->filled('campoPcd')) {
            $campoPcd = $request->campoPcd == 'true' ? true : false;
            $resultado->whereHas('Curriculo', function ($query) use ($campoPcd) {
                $query->wherePcd($campoPcd);
            });
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        $itens = collect($resultado->items());

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $itens]
        ]);
    }

    public function pdf(Request $request)
    {
        $this->authorize('portaria');
        $curriculos = Curriculo::whereIn('id', $request->selecionados)->get();
        return view('pdf.portaria.ficha', compact('curriculos'));
    }

    //Excel
    public function export(Request $request)
    {
        $this->authorize('portaria');
//        $curriculo = ResultadoIntegrado::whereEncaminhadoTreinamento(true)->orderBy('curriculo_id');
//
//        if ($request->selecionados) {
//            $curriculo = $curriculo->whereIn('curriculo_id', $request->selecionados);
//        } else {
//
//            if ($request->filled('campoCliente')) {
//                $curriculo->whereHas('Feedback', function ($q) use ($request) {
//                    $q->whereClienteId(auth()->user()->cliente_id == User::BPSE ? $request->campoCliente : auth()->user()->cliente_id);
//                });
//            }
//
//            if ($request->filled('vaga_id')) {
//                $curriculo->whereHas('Feedback', function ($query) use ($request) {
//                    $query->whereVagaId($request->vaga_id);
//                });
//            }
//
//            if ($request->filled('uf')) {
//                $curriculo->whereHas('Feedback.Curriculo', function ($q) use ($request) {
//                    $q->whereUfVaga($request->uf);
//                });
//            }
//        }
//
//        $curriculo = $curriculo->get();
//
//
//        return Excel::download(new treinamentoExport($curriculo), 'portaria.xlsx');
    }
}
