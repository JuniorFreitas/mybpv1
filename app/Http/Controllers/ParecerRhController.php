<?php

namespace App\Http\Controllers;

use App\Exports\Entrevistas\entrevistaRhExport;
use App\Exports\Entrevistas\parecerRhExport;
use App\Jobs\JobExportaExcel;
use App\Models\FeedbackCurriculo;
use App\Models\ParecerRh;
use App\Models\SimuladoVaga;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use MasterTag\DataHora;
use PDF;

class ParecerRhController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.entrevistas.parecer_rh.index');
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
        $this->authorize('entrevista_parecer_rh_insert');
        $dados = $request->input();
        $dadosRh = $dados['parecer_rh'];

//        if ($dadosRh['tipo_entrevista'] == 'Fixo') {
//            $dadosValidados = \Validator::make($dados, [
//                'dados.parecer_rh.destro' => 'required',
//                'dados.parecer_rh.rota_bairro' => 'required',
//                'dados.parecer_rh.mora_com_quem' => 'required',
//                'dados.parecer_rh.grau_instrucao' => 'required',
//                'dados.parecer_rh.situacao_saude' => 'required',
//                'dados.parecer_rh.comportamento_seguro' => 'required',
//                'dados.parecer_rh.energia_para_trabalho' => 'required',
//                'dados.parecer_rh.postura' => 'required',
//
//            ]);
//        }

        $dadosValidados = \Validator::make($dados, [
            'curriculo.municipio_id' => 'required',
            'cliente_id' => 'required',
            'vaga_id' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao confirmar Parecer',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $feedback = FeedbackCurriculo::find($dados['id']);
                $dadosRh['curriculo_id'] = $feedback->curriculo_id;

                /****
                 * 1 - atualiza a Vaga e cliente
                 * 2 - atualiza municipio (curriculo)
                 * 3 - se tem cursos formacao cria
                 * 4 - se tem nr cria
                 * 5 - se tem individual
                 */

                // atualiza a Vaga e cliente
                $feedback->update([
                    'vaga_id' => $dados['vaga_id'],
                    'cliente_id' => $dados['cliente_id'],
                ]);

                // atualiza municipio (curriculo)
                $feedback->Curriculo->update([
                    'municipio_id' => $dados['curriculo']['municipio_id']
                ]);

                // cria Cursos de formações se tiver
                if ($request->filled('cursos_formacoes')) {
                    foreach ($dados['cursos_formacoes'] as $curso) {
                        $curso['curriculo_id'] = $dados['curriculo_id'];
                        $feedback->CursosFormacoes()->create($curso);
                    }
                }

                // cria Certificados NR se tiver
                if ($request->filled('certificados_nr')) {
                    foreach ($dados['certificados_nr'] as $curso) {
                        $nr['curriculo_id'] = $dados['curriculo_id'];
                        $feedback->CertificadosNr()->create($curso);
                    }
                }

                // Cira se tiver  entrevista Individual
                if ($request->filled('parecer_rh.individual_rh') && $feedback->Cliente->area_id > 1) {
                    $dadosRh['individual_rh']['curriculo_id'] = $dados['curriculo_id'];
                    $feedback->individualRh()->create($dadosRh['individual_rh']);
                }

                // Por fim cria o parecerRH
                $feedback->parecerRh()->create($dadosRh);

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error PARECER RH STORE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}| Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ParecerRh $parecerRh
     * @return \Illuminate\Http\Response
     */
    public function show(ParecerRh $parecerRh)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ParecerRh $parecerRh
     * @return \Illuminate\Http\Response
     */
    public function edit(FeedbackCurriculo $parecerRh)
    {
        $feedback = $parecerRh; //FeedbackCurriculo

        $feedback->load('parecerRh.individualRh',
            'parecerRh.gestorRh',
            'parecerRh.entrevistaRh',
            'CertificadosNr',
            'CursosFormacoes',
            'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
            'Curriculo.Formacao',
            'TelPrincipal',
            'VagaAberta.VagaSelecionada',
            'Cliente:id,razao_social,cnpj,nome,cpf,area_id',
            'Cliente.Area'
        )
            ->load(['Simulados' => function ($query) {
                $query->with('SimuladoVaga.Simulado');
            }]);

        $feedback->Curriculo->autocomplete_label_municipio_modal = $feedback->Curriculo->Cidade ? $feedback->Curriculo->Cidade->nome . ' - ' . $feedback->Curriculo->Cidade->uf : '';
        $feedback->Curriculo->autocomplete_label_municipio_modal_anterior = $feedback->Curriculo->Cidade ? $feedback->Curriculo->Cidade->nome . ' - ' . $feedback->Curriculo->Cidade->uf : '';


        $feedback->autocomplete_label_vaga_modal = $feedback->VagaAberta->vagaSelecionada ? $feedback->VagaAberta->vagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';
        $feedback->autocomplete_label_vaga_modal_anterior = $feedback->VagaAberta->vagaSelecionada ? $feedback->VagaAberta->vagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';

        $feedback->autocomplete_label_cliente_modal = $feedback->Cliente ? $feedback->Cliente->razao_social . ' | ' . $feedback->Cliente->cnpj : '';
        $feedback->autocomplete_label_cliente_modal_anterior = $feedback->Cliente ? $feedback->Cliente->razao_social . ' | ' . $feedback->Cliente->cnpj : '';

        $simulados = SimuladoVaga::whereVagaId($feedback->vaga_id)
            ->whereHas('Simulado', function ($q) {
                $q->whereAtivo(true);
            })->count();

        return response()->json(['feedback' => $feedback, 'provas' => $simulados], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ParecerRh $parecerRh
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ParecerRh $parecerRh)
    {
        $this->authorize('entrevista_parecer_rh_update');
        $dados = $request->input();
        $dadosRh = $dados['parecer_rh'];

        $dadosValidados = \Validator::make($dados, [
            'curriculo.municipio_id' => 'required',
            'cliente_id' => 'required',
            'vaga_id' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar a Entrevista',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                /****
                 * 1 - atualiza a Vaga e cliente
                 * 2 - atualiza municipio (curriculo)
                 * 3 - se tem cursos formacao cria
                 * 4 - se tem nr cria
                 * 5 - se tem individual
                 */

                // atualiza a Vaga e cliente
                $parecerRh->FeedbackCurriculo->update([
                    'vaga_id' => $dados['vaga_id'],
                    'cliente_id' => $dados['cliente_id'],
                ]);

                // atualiza municipio (curriculo)
                $parecerRh->FeedbackCurriculo->Curriculo->update([
                    'municipio_id' => $dados['curriculo']['municipio_id']
                ]);

                if (isset($infCurriculo['cursos_formacoesDelete'])) {
                    foreach ($infCurriculo['cursos_formacoesDelete'] as $index) {
                        $parecerRh->FeedbackCurriculo->CursosFormacoes->find($index)->delete();
                    }
                }

                // cria Cursos de formações se tiver
                if ($request->filled('cursos_formacoes')) {
                    foreach ($dados['cursos_formacoes'] as $linha) {
                        if (isset($linha['id'])) {
                            $parecerRh->FeedbackCurriculo->CursosFormacoes->find($linha['id'])->update($linha);
                        } else {
                            $linha['curriculo_id'] = $dados['curriculo_id'];
                            $parecerRh->FeedbackCurriculo->CursosFormacoes()->create($linha);
                        }
                    }
                }

                if (isset($infCurriculo['certificados_nrDelete'])) {
                    foreach ($infCurriculo['certificados_nrDelete'] as $index) {
                        $parecerRh->FeedbackCurriculo->CertificadosNr->find($index)->delete();
                    }
                }

                // cria Certificados NR se tiver
                if ($request->filled('certificados_nr')) {
                    foreach ($dados['certificados_nr'] as $linha) {
                        if (isset($linha['id'])) {
                            $parecerRh->FeedbackCurriculo->CertificadosNr->find($linha['id'])->update($linha);
                        } else {
                            $linha['curriculo_id'] = $dados['curriculo_id'];
                            $parecerRh->FeedbackCurriculo->CertificadosNr()->create($linha);
                        }
                    }
                }

                // Cria se tiver  entrevista Individual
                if ($request->filled('parecer_rh.individual_rh')) {
                    $parecerRh->FeedbackCurriculo->individualRh()->create($dadosRh['individual_rh']);
                }

                // Por fim cria o parecerRH
                $parecerRh->update($dadosRh);

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error PARECER RH UPDATE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}| Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ParecerRh $parecerRh
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParecerRh $parecerRh)
    {
        //
    }

    /**
     * @param Request $request
     * @return FeedbackCurriculo|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function filtro(Request $request)
    {
        $resultado = FeedbackCurriculo::whereInteresse(true)
            ->whereIn('selecionado', ['sim', 'standby'])
            ->with(
                'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
                'Cliente:id,razao_social,area_id',
                'Cliente.Area',
                'VagaAberta.VagaSelecionada',
                'parecerRh:id,feedback_id,nota,created_at',
                'parecerRh.individualRh:feedback_id,nota,parecer',
                'parecerRota'
            );
        $filtroPeriodo = $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]. ' 00:00:00');
            $dataFim = new DataHora($periodo[1]. ' 23:59:59');
            $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                $q->where('created_at', '>=', $dataInicio->dataHoraInsert())
                    ->where('created_at', '<=', $dataFim->dataHoraInsert());
            });
        }
        if ($request->filled('campoCliente')) {
            $resultado->whereClienteId($request->campoCliente);
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->whereCpf($request->campoCPF);
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

        $resultado->with(
            'parecerTecnica:feedback_id,nota',
            'parecerRota:feedback_id,tem_rota',
            'parecerTeste:feedback_id,nota_teste');

        if ($request->filled('campoRota')) {
            $campoRota = $request->campoRota == 'true' ? true : false;
            if ($request->campoRota == 'realizado') {
                $resultado->has('parecerRota');
            } else {
                $resultado->whereHas('parecerRota', function ($q) use ($campoRota) {
                    $q->whereTemRota($campoRota);
                });
            }
        }

        if ($request->filled('campoTeste')) {
            if ($request->campoRota == 'realizado') {
                $resultado->has('parecerTeste');
            } else {
                $resultado->whereHas('parecerTeste', function ($q) use ($request) {
                    $q->whereNotaTeste($request->campoTeste);
                });
            }
        }

        if ($request->filled('campoTecnica')) {
            $resultado->has('parecerTecnica');
        }

        if ($request->filled('campoPcd')) {
            $campoPcd = $request->campoPcd == 'true' ? true : false;
            $resultado->whereHas('Curriculo', function ($query) use ($campoPcd) {
                $query->wherePcd($campoPcd);
            });
        }

        if ($request->filled('campoRh')) {
            if ($request->campoRh == 'realizado') {
                $resultado->has('parecerRh');
            } else {
                $resultado->whereHas('parecerRH', function ($q) use ($request) {
                    $q->whereNota($request->campoRh);
                });
            }
        }

        if ($request->filled('campoFinalRh')) {
            $resultado->whereHas('parecerRH', function ($q) use ($request) {
                $q->whereParecerFinalUm($request->campoFinalRh);
            });
        }

        $resultado = $resultado->orderByDesc('created_at');

        return $resultado;
    }

    public function atualizar(Request $request)
    {
        $pg = $this->filtro($request)->paginate($request->porPag ?: 20);
        return Sistema::pg($pg);
    }

    public function export(Request $request)
    {
        if ($request->selecionados) {
            $resultado = $this->filtro($request)->whereIn('id', $request->selecionados)->get();
        } else {
            $resultado = $this->filtro($request)->get();
        }

        $head = [
            'Id',
            'Nome',
            'Destro/Canhoto',
            'Nascimento',
            'Idade',
            'Endereço',
            'Contato',
            'E-mail',
            'Vaga',
            'Escolaridade',
            'Ex Funcionário',
            'CNH',
            'Rota Bairro',
            'Camisa Meia',
            'Camisa Proteção',
            'Calça',
            'Bota',
            'Mora com quem',
            'Casado',
            'Tempo de convivencia',
            'Filhos',
            'Quantos Filhos',
            'Esposa ou Marido Trabalha',
            'Esposa ou Marido em que Trabalha',
            'Praticante de alguma religião',
            'Qual religião',
            'Fuma',
            'Qual frequencia',
            'Bebe',
            'qual frequencia',
            'Indicado por alguem',
            'Quem indicou',
            'Experiência na ALUMAR',
            'Qual área',
            'Experiência em outra industia',
            'Qual',
            'Grau de instrução',
            'Disponibilidade de hora extra',
            'Disponibilidade para turnos 6X2',
            'Disponibilidade para noturno',
            'Acidente de trabalho anterior',
            'Especifique',
            'Afastamento INSS anterior',
            'Especifique',
            'Especifique situações de saúde',
            'Comportamento Seguro',
            'Energia para trabalho',
            'Postura',
            'Histórico Profissional',
            'Histórico Educacional',
            'Parecer Final RH',
            'Parecer RH Resultado',
            'Parecer RH Nota',
            'Parecer RH Entrevistado Por',
            'Data Entrevista',
            'Local Entrevista',
            'Rota Transporte',
            'Entrevista Técnica Nota',
            'Teste Prático Nota',
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $parecer = $row->parecerRh;
            $bool = function ($value) {
                return $value !== null ? ($value ? 'Sim' : 'Não') : 'Não informado';
            };
            $rows[] = array(
                $row->Curriculo->id,
                $row->Curriculo->nome,
                $parecer?->destro ?: 'Não informado',
                $row->Curriculo->nascimento,
                $row->Curriculo->idade,
                $row->Curriculo->logradouro . ', ' . $row->Curriculo->bairro . ', ' . $row->Curriculo->municipio . ' - ' . $row->Curriculo->uf,
                $row->TelPrincipal ? $row->TelPrincipal->numero : 'não informado',
                $row->Curriculo->email,
                $row->VagaAberta->VagaSelecionada->nome . ' - ' . $row->VagaAberta->Municipio->uf,
                $row->Curriculo->Formacao->tipo ? '(' . $row->Curriculo->Formacao->tipo . ')' : 'Não informado',
                $parecer?->ex_funcionario ?: 'Não informado',
                $parecer?->cnh_tipo ?: 'Não informado',
                $parecer?->rota_bairro ?: 'Não informado',
                $parecer?->camisa_meia ?: 'Não informado',
                $parecer?->camisa_protecao ?: 'Não informado',
                $parecer?->calca ?: 'Não informado',
                $parecer?->bota ?: 'Não informado',
                $parecer?->mora_com_quem ?: 'Não informado',
                $bool($parecer?->casado),
                $parecer?->tempodeconvivencia ?: 'Não informado',
                $bool($parecer?->filhos),
                $parecer?->qnt_filhos ?: 'Não informado',
                $bool($parecer?->conjunge_trabalha),
                $parecer?->trabalho_conjunge ?: 'Não informado',
                $bool($parecer?->religioso),
                $parecer?->religiao_praticante ?: 'Não informado',
                $bool($parecer?->fuma),
                $parecer?->frequencia_fuma ?: 'Não informado',
                $bool($parecer?->bebe),
                $parecer?->frequencia_bebe ?: 'Não informado',
                $bool($parecer?->inidicacao),
                $parecer?->indicado_por ?: 'Não informado',
                $bool($parecer?->alumar_experiencia),
                $parecer?->alumar_experiencia_area ?: 'Não informado',
                $bool($parecer?->outra_industria_experiencia),
                $parecer?->outra_industria_nome ?: 'Não informado',
                $parecer?->grau_instrucao ?: 'Não informado',
                $bool($parecer?->horaextra),
                $bool($parecer?->turnos_seis_por_dois),
                $bool($parecer?->noturno),
                $bool($parecer?->acidente_trabalho),
                $parecer?->acidente_trabalho_qual ?: 'Não informado',
                $bool($parecer?->afastamento_inss),
                $parecer?->afastamento_inss_qual ?: 'Não informado',
                $parecer?->situacao_saude ?: 'Não informado',
                $parecer?->comportamento_seguro ?: 'Não informado',
                $parecer?->energia_para_trabalho ?: 'Não informado',
                $parecer?->postura ?: 'Não informado',
                $parecer?->historico_profissional ?: 'Não informado',
                $parecer?->historico_educacional ?: 'Não informado',
                $parecer?->parecer_final ?: 'Não informado',
                $parecer?->parecer_final_um ?: 'Não informado',
                $parecer?->nota ?: 'Não informado',
                $parecer?->quem_entrevistou ?: 'Não informado',
                $row->data_entrevista,
                $row->local_entrevista,
                $row->parecerRota ? $row->parecerRota->tem_rota ? 'Sim' : 'Não' : 'aguardando',
                $row->parecerTecnica ? $row->parecerTecnica->nota : 'aguardando',
                $row->parecerTeste ? $row->parecerTeste->nota_teste == 0 ? 'Não se Aplica' : $row->parecerTeste->nota_teste : 'aguardando',
            );
        }

        $nameArquivo = "parecer_rh" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Entrevista - Parecer RH", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function getFichaPdf(Request $request)
    {
        $parecer_rh = ParecerRh::find($request->id)->append('data_entrevista');
        $dados = $parecer_rh;
        $pdf = PDF::loadView('pdf.entrevista.parecer_rh.ficha', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("parecer_rh" . Str::slug($parecer_rh->FeedbackCurriculo->Curriculo->nome) . ".pdf");
    }
}
