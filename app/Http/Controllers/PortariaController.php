<?php

namespace App\Http\Controllers;

use App\Exports\treinamentoExport;
use App\Jobs\JobExportaExcel;
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
        $resultado = ResultadoIntegrado::whereFeedbackId($resultado)->first();
        $resultado->load('Feedback.Curriculo.FotoTres', 'Feedback.Admissao');

        $resultado->funcao = $resultado->Admissao ? $resultado->Admissao->funcao : '';
        $resultado->Feedback->Curriculo->foto_tresDel = [];

        $resultado->Feedback->Curriculo->autocomplete_label_municipio_modal = $resultado->Feedback->Curriculo->Cidade ? $resultado->Feedback->Curriculo->Cidade->nome . ' - ' . $resultado->Feedback->Curriculo->Cidade->uf : '';
        $resultado->Feedback->Curriculo->autocomplete_label_municipio_modal_anterior = $resultado->Feedback->Curriculo->Cidade ? $resultado->Feedback->Curriculo->Cidade->nome . ' - ' . $resultado->Feedback->Curriculo->Cidade->uf : '';

        $resultado->Feedback->autocomplete_label_vaga_modal = $resultado->Feedback->VagaAberta->VagaSelecionada->nome . ' - ' . $resultado->Feedback->VagaAberta->Municipio->uf;
        $resultado->Feedback->autocomplete_label_vaga_modal_anterior = $resultado->Feedback->autocomplete_label_vaga_modal;

        return $resultado;

    }

    public function update(Request $request, FeedbackCurriculo $resultado)
    {
        $this->authorize('treinamento_portaria_insert');

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
                $resultado->Admissao->update([
                    'funcao' => $dados['admissao']['funcao'],
                    'acessar_area_porto' => $dados['admissao']['acessar_area_porto'],
                    'avaliacao_psicologica' => $dados['admissao']['avaliacao_psicologica'],
                ]);
            } else {
                $resultado->Admissao()->create([
                    'funcao' => $dados['admissao']['funcao'],
                    'acessar_area_porto' => $dados['admissao']['acessar_area_porto'],
                    'avaliacao_psicologica' => $dados['admissao']['avaliacao_psicologica'],
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
        $resultado = $this->filtro($request)->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items()
            ]
        ]);

    }

    public function filtro(Request $request)
    {
        $resultado = FeedbackCurriculo::whereHas('ResultadoIntegrado', function ($q) {
            $q->whereEncaminhadoTreinamento(true);
        })->with(
            'Curriculo.FotoTres:id',
            'VagaSelecionada:id,nome',
            'Admissao:id,feedback_id,funcao',
        );

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
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

        return $resultado->orderByDesc('created_at');

    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();
        $head = [
            'ID',
            'Nome',
            'CPF',
            'RG/Eminente',
            'Filiação',
            'Vaga',
            'Função',
            'Endereço'
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->Admissao->id,
                $row->Curriculo->nome,
                $row->Curriculo->cpf,
                $row->Curriculo->rg .' '. $row->Curriculo->orgao_expeditor,
                'Mãe: '.$row->Curriculo->filiacao_mae .' - Pai: '.$row->Curriculo->filiacao_pai,
                $row->VagaSelecionada->nome,
                $row->Admissao->funcao,
                $row->Curriculo->endereco_completo,
            ];
        }

        $nameArquivo = "portaria" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Portaria", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }


    public function pdf(Request $request)
    {
        $this->authorize('treinamento_portaria');
        $feedbacks = FeedbackCurriculo::whereIn('id', $request->selecionados)->get();
        return view('pdf.portaria.ficha', compact('feedbacks'));
    }

}
