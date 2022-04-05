<?php

namespace App\Http\Controllers;

use App\Models\FeedbackCurriculo;
use App\Models\NotificacaoWhats;
use App\Models\SimuladoCandidato;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class CurriculosSelecionadosController extends Controller
{
    public function index()
    {
        return view('g.curriculos.selecionados.index');
    }

    public function getCurriculo(FeedbackCurriculo $feedback)
    {
        $feedback->load(
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email',
            'Curriculo.Formacao',
            'VagaAberta.VagaSelecionada',
            'VagaAberta.Municipio',
            'TelPrincipal',
            'Cliente',
            'Simulados.SimuladoVaga.Simulado',
            'EtapaStatus'
        )
            ->load(['Vinculo' => function ($q) use ($feedback) {
                $q->whereVagaId($feedback->vaga_id);
            }]);

        return $feedback;
    }

    public function modificaStatus(Request $request)
    {
        if ($request->simulado_id > 0) {
            $simulado = SimuladoCandidato::find($request->simulado_id);

            $simulado->status = $request->status;
            $simulado->save();
            $simulado->refresh();
            return response()->json(['ativo' => $simulado->status], 201);
        }

        return response()->json([], 201);
    }

    public function atualizar(Request $request)
    {
        $resultado = FeedbackCurriculo::whereInteresse(true)
            ->whereSelecionado('sim')
            ->with(
                'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email',
                'Cliente:id,razao_social,cnpj',
                'Simulados.SimuladoVaga.Simulado',
                'Curriculo.Formacao',
                'VagaAberta.VagaSelecionada',
                'VagaAberta.Municipio',
                'EtapaStatus'
            );

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->where('updated_at', '>=', $dataInicio->dataInsert())->where('updated_at', '<=', $dataFim->dataInsert());
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            })->orWhere('id', $request->campoBusca);;
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->whereCpf($request->campoCPF);
            });
        }

        if ($request->filled('campoCliente')) {
            $resultado->whereClienteId($request->campoCliente);
        }

        if ($request->filled('campoProvas')) {
            if ($request->campoProvas == 'sim') {
                $resultado->whereHas('Simulados.SimuladoVaga', function ($q) {
                    $q->whereFinalizado(true);
                });
            }
            if ($request->campoProvas == 'nao') {
                $resultado->doesntHave('Simulados.SimuladoVaga');
            }
            if ($request->campoProvas == 'andamento') {
                $resultado->whereHas('Simulados.SimuladoVaga', function ($q) {
                    $q->whereFinalizado(false);
                });
            }
        }

        //Notas
        if ($request->filled('campoNota')) {
            if ($request->campoNota == 0) {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->whereAcertos(0);
                });
            }
            if ($request->campoNota == '1-5') {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->where('acertos', '>=', 1)->where('acertos', '<=', 5);
                });
            }
            if ($request->campoNota == '5-7') {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->where('acertos', '>=', 5)->where('acertos', '<=', 7);
                });
            }
            if ($request->campoNota == '8-10') {
                $resultado->whereHas('Simulados', function ($q) {
                    $q->where('acertos', '>=', 8)->where('acertos', '<=', 10);
                });
            }

        }

        if ($request->filled('campoStatus')) {
            if ($request->campoStatus != 'sem_status') {
                $resultado->whereStatus($request->campoStatus);
            }
            if ($request->campoStatus == 'sem_status') {
                $resultado->doesntHave('EtapaStatus');
            }
        }
        if ($request->filled('campoEtapa')) {
            $resultado->whereHas('EtapaStatus', function ($q) use ($request) {
                $q->whereEtapa($request->campoEtapa);
            });
        }

        $resultado = $resultado->orderBy('updated_at', 'desc')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $resultado->items(), 'usuario_cliente_id' => auth()->user()->cliente_id]
        ]);
    }

    public function proximaEtapa()
    {
    }

    public function emailProva()
    {
    }

    public function enviaNotificacao(Request $request)
    {
        $dados = $request->input();
//         $dados['fone'] = preg_replace('/[^0-9]/i', '', "+55 (98) 9 9902-3762");

        NotificacaoWhats::sendNotificacaoAptoAdmissao($dados, null);
    }
}
