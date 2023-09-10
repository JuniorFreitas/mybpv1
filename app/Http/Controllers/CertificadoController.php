<?php

namespace App\Http\Controllers;

use App\Models\CertificadoAlumar;
use App\Models\ResultadoIntegrado;
use App\Models\User;
use App\Models\Vencimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class CertificadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.certificado.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('treinamento_certificado_insert');
        $dados = $request->input();

        try {
            DB::beginTransaction();
            $dados['certificadoDados'] = [
                'feedback_id' => (int)$dados['feedback_id'],
                'cliente_id' => (int)$dados['feedback']['cliente_id'],
                'nacional' => $dados['feedback']['cliente_id'] != 5,
                'empresa_treinamento_trinta_cinco_id' => isset($dados['certificado']['empresa_treinamento_trinta_cinco_id']) ? (int)$dados['certificado']['empresa_treinamento_trinta_cinco_id'] : null,
                'instrutor_trinta_cinco_id' => isset($dados['certificado']['instrutor_trinta_cinco_id']) ? (int)$dados['certificado']['instrutor_trinta_cinco_id'] : null,
                'empresa_treinamento_trinta_tres_id' => isset($dados['certificado']['empresa_treinamento_trinta_tres_id']) ? (int)$dados['certificado']['empresa_treinamento_trinta_tres_id'] : null,
                'instrutor_trinta_tres_id' => isset($dados['certificado']['instrutor_trinta_tres_id']) ? (int)$dados['certificado']['instrutor_trinta_tres_id'] : null,
            ];

            if (!isset($dados['certificado']['id'])) {
                CertificadoAlumar::create($dados['certificadoDados']);
            } else {

                $certificado = CertificadoAlumar::whereFeedbackId($dados['feedback_id'])->first();
                $certificado->update($dados['certificadoDados']);
            }
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error no CERTIFICADO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}, USUARIO: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\CertificadoAlumar $certificadoAlumar
     * @return \Illuminate\Http\Response
     */
    public function show(CertificadoAlumar $certificado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\CertificadoAlumar $certificadoAlumar
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function edit($certificado)
    {
        $certificado = ResultadoIntegrado::whereFeedbackId($certificado)->first();
        $treinamento = $certificado->load('Certificado',
            'Feedback:id,curriculo_id,cliente_id,vaga_id',
            'Feedback.Curriculo:id,nome,nascimento,id,nome,cpf,nascimento,rg,orgao_expeditor',
            'Feedback.Cliente:id,nome_fantasia',
            'Feedback.VagaSelecionada:id,nome'
        );

        $treinamento->listaVencimentos = Vencimento::whereAtivo(true)
            ->whereIn('id', [6, 7])
            ->orderBy('ordem')->get()
            ->transform(function ($item) use ($treinamento) {
                if ($treinamento->Treinamento) {
                    $pivo = $treinamento->Treinamento->Vencimentos()->whereId($item->id);
                    $item->data_treinamento = $pivo->count() > 0 ? $pivo->first()->pivot->data_treinamento : null;
                    $item->data_vencimento = $pivo->count() > 0 ? $pivo->first()->pivot->data_vencimento : null;
                    $item->fez_treinamento = $pivo->count() > 0;
                }
                return $item;
            });

        $treinamento->nr_trinta_tres = $treinamento->listaVencimentos[0]->fez_treinamento;
        $treinamento->nr_trinta_cinco = $treinamento->listaVencimentos[1]->fez_treinamento;

        return response()->json($treinamento, 200);

//         $treinamento = Treinamento::whereCurriculoId($curriculo_id)->first();

//         return $treinamento->load('Vencimentos');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\CertificadoAlumar $certificadoAlumar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CertificadoAlumar $certificadoAlumar)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\CertificadoAlumar $certificadoAlumar
     * @return \Illuminate\Http\Response
     */
    public function destroy(CertificadoAlumar $certificadoAlumar)
    {
        //
    }

    public function certificadoPdf(Request $request)
    {
        $nr33 = $request->nr33 == 'true' ? true : false;
        $nr35 = $request->nr35 == 'true' ? true : false;
        $certificados = CertificadoAlumar::whereIn('feedback_id', $request->selecionados)->get();
        return view('pdf.certificado.modelo', compact('certificados', 'nr33', 'nr35'));
    }

    public function atualizar(Request $request)
    {
        $this->authorize('treinamento_certificado');

        $filtroIntervalo = $request->filtroPeriodo == 'true';

        $resultado = ResultadoIntegrado::whereEncaminhadoTreinamento(true)
            ->with(
                'Feedback.Curriculo:id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
                'Feedback.VagaSelecionada:id,nome',
                'Feedback.Cliente:id,nome_fantasia,nome',
                'Admissao.AreaEtiqueta',
                'Treinamento.Vencimentos',
                'Certificado.InstrutorTrintaTres',
                'Certificado.InstrutorTrintaCinco',
                'Certificado.EmpresaTrintaTres',
                'Certificado.EmpresaTrintaCinco'
            )
            ->whereHas('Treinamento.Vencimentos', function ($query) use ($request, $filtroIntervalo) {
                $query->whereIn('id', [6, 7]);
                if ($filtroIntervalo) {
                    $periodo = explode(' até ', $request->intervalo);
                    $dataInicio = new DataHora($periodo[0]. ' 00:00:00');
                    $dataFim = new DataHora($periodo[1]. ' 23:59:59');
                    $query->where('data_treinamento', '>=', $dataInicio->dataHoraInsert())
                        ->where('data_treinamento', '<=',  $dataFim->dataHoraInsert());
                }
            });


        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->whereCpf($request->campoCPF);
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('Feedback.VagaSelecionada', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoCliente')) {
            $resultado->whereHas('Feedback', function ($q) use ($request) {
                $q->whereClienteId($request->campoCliente);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('Feedback.Curriculo', function ($q) use ($request) {
                $q->whereUfVaga($request->campoUf);
            });
        }

        if ($request->filled('campoArea')) {
            $resultado->whereHas('Admissao', function ($q) use ($request) {
                $q->whereAreaEtiquetaId($request->campoArea);
            });
        }

        if ($request->filled('campoCargo')) {
            $resultado->whereHas('Admissao', function ($query) use ($request) {
                $query->where('cargo', 'like', '%' . $request->campoCargo . '%');
            });
        }

        if ($request->filled('campoAdmitido')) {
            if ($request->campoAdmitido == 'true') {
                $resultado->whereHas('Admissao', function ($q) {
                    $q->whereStatus('ADMITIDO');
                });
            }
            if ($request->campoAdmitido == 'false') {
                $resultado->whereDoesntHave('Admissao');
            }
        }

        if ($request->filled('campoInstrutor_nr_trinta_tres')) {
            if ($request->campoInstrutor_nr_trinta_tres == 'true') {
                $resultado->whereHas('Certificado', function ($query) {
                    $query->whereNotNull('instrutor_trinta_tres_id');
                });
            } else {
                $resultado->whereDoesntHave('Certificado')->orWhereHas('Certificado', function ($query) {
                    $query->whereNull('instrutor_trinta_tres_id');
                });
            }
        }

        if ($request->filled('campoInstrutor_nr_trinta_cinco')) {
            if ($request->campoInstrutor_nr_trinta_cinco == 'true') {
                $resultado->whereHas('Certificado', function ($query) {
                    $query->whereNotNull('instrutor_trinta_cinco_id');
                });
            } else {
                $resultado->whereDoesntHave('Certificado')->orWhereHas('Certificado', function ($query) {
                    $query->whereNull('instrutor_trinta_cinco_id');
                });
            }
        }

        if ($request->filled('campoNr_trinta_tres')) {

            if ($request->campoNr_trinta_tres == 'true') {
                $resultado->whereHas('Treinamento.Vencimentos', function ($query) use ($request) {
                    $query->whereId(7);
                });
            }
            if ($request->campoNr_trinta_tres == 'false') {
                $resultado->doesntHave('Treinamento')->whereHas('Admissao', function ($query) use ($request) {
                    $query->where('nr_trinta_tres', '!=', 'NÃO SE APLICA');
                });
            }
            if ($request->campoNr_trinta_tres == 'NÃO SE APLICA') {
                $resultado->whereHas('Admissao', function ($query) use ($request) {
                    $query->where('nr_trinta_tres', $request->campoNr_trinta_tres);
                });
            }
        }

        if ($request->filled('campoNr_trinta_cinco')) {

            if ($request->campoNr_trinta_cinco == 'true') {
                $resultado->whereHas('Treinamento.Vencimentos', function ($query) use ($request) {
                    $query->whereId(6);
                });
            }
            if ($request->campoNr_trinta_cinco == 'false') {
                $resultado->doesntHave('Treinamento')->whereHas('Admissao', function ($query) use ($request) {
                    $query->where('nr_trinta_cinco', '!=', 'NÃO SE APLICA');
                });
            }
            if ($request->campoNr_trinta_cinco == 'NÃO SE APLICA') {
                $resultado->whereHas('Admissao', function ($query) use ($request) {
                    $query->where('nr_trinta_cinco', $request->campoNr_trinta_cinco);
                });
            }
        }

        if ($request->filled('campoCliente')) {
            $resultado->whereHas('Feedback', function ($q) use ($request) {
                $q->whereClienteId(auth()->user()->cliente_id == User::BPSE ? $request->campoCliente : auth()->user()->cliente_id);
            });
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        $itens = collect($resultado->items());

        $itens->transform(function ($item) {
            if ($item->Treinamento) {
                $item->nr_33 = $item->Treinamento->Vencimentos->where('id', 7)->count() > 0 ? $item->Treinamento->Vencimentos->where('id', 7)->first()->pivot : null;
                $item->nr_35 = $item->Treinamento->Vencimentos->where('id', 6)->count() > 0 ? $item->Treinamento->Vencimentos->where('id', 6)->first()->pivot : null;
            } else {
                $item->nr_33 = null;
                $item->nr_35 = null;
            }
            return $item;
        });

        if (!$filtroIntervalo) {
            $data = new DataHora('01/' . date('m/Y') . ' 00:00:00');
            $intervalo = $data->dataCompleta() . ' até ' . $data->addMes(1);
        } else {
            $intervalo = $request->intervalo;
        }


        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $itens, 'intervalo' => $intervalo]
        ]);
    }
}
