<?php

namespace App\Http\Controllers;

use App\Exports\Entrevistas\admissaoExport;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\ResultadoIntegrado;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\UsuarioConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use MasterTag\DataHora;
use PDF;

class AdmissaoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.admissao.processo.index');
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();

        $dadosCurriculo = $dados['curriculo'];
        $dadosCurriculo['email'] = trim(strtolower($dadosCurriculo['email']));
        $dadosCurriculo['vaga_pretendida'] = $dados['feedback']['vaga_id'];
        $dadosCurriculo['uf_vaga'] = substr($dadosCurriculo['autocomplete_label_municipio_modal'], -2, 2);
        $dadosCurriculo['rg'] = null;

        $dadosCurriculo['pcd'] = $dadosCurriculo['pcd'] == 'true';

        $dadosFeedback = $dados['feedback'];
        $dadosFeedback['interesse'] = $dadosFeedback['interesse'] == 'true';
        $dadosFeedback['usuario_entrevista_marcado'] = auth()->id();
        $dadosFeedback['data_entrevista'] = null;
        $dadosFeedback['selecionado'] = 'sim';

        $dadosParecerRh = $dados['parecer_rh'];
        $dadosParecerRh['ex_funcionario'] = $dadosParecerRh['ex_funcionario'] == 'true';
        $dadosParecerRh['indicacao'] = $dadosParecerRh['indicacao'] == 'true';
        $dadosParecerRh['turnos_seis_por_dois'] = $dadosParecerRh['turnos_seis_por_dois'] == 'true';
        $dadosParecerRh['entrevistador'] = auth()->id();

        $dadosParecerRota = $dados['parecer_rota'];
        $dadosParecerRota['tem_rota'] = null;
        $dadosParecerRota['aprovado_por'] = auth()->id();

        $dadosParecerTecnica = $dados['parecer_tecnica'];

        $dadosParecerTecnica['entrevistado_por'] = auth()->id();

        $dadosParecerTeste = $dados['parecer_teste'];
        $dadosParecerTeste['fez_teste'] = null;
        $dadosParecerTeste['nota_teste'] = null;
        $dadosParecerTeste['data_horario_realizacao'] = null;
        $dadosParecerTeste['entrevistador'] = auth()->id();

        $dadosAdmissao = $dados['admissao'];
        $dadosAdmissao['usuario_id'] = auth()->id();

        $dadosResultadoIntegrado = $dados['resultado_integrado'];

        $dadosResultadoIntegrado['documentos_entregue'] = $dadosResultadoIntegrado['documentos_entregue'] == 'true';
        $dadosResultadoIntegrado['documentos_entregue_data'] = $dadosResultadoIntegrado['documentos_entregue_data'] ? (new DataHora($dadosResultadoIntegrado['documentos_entregue_data']))->dataInsert() : null;

        $dadosResultadoIntegrado['encaminhado_exame'] = $dadosResultadoIntegrado['encaminhado_exame'] == 'true';
        $dadosResultadoIntegrado['encaminhado_exame_data'] = $dadosResultadoIntegrado['encaminhado_exame_data'] ? (new DataHora($dadosResultadoIntegrado['encaminhado_exame_data']))->dataInsert() : null;

        $dadosResultadoIntegrado['encaminhado_treinamento'] = $dadosResultadoIntegrado['encaminhado_treinamento'] == 'true';
        $dadosResultadoIntegrado['encaminhado_treinamento_data'] = $dadosResultadoIntegrado['encaminhado_treinamento_data'] ? (new DataHora($dadosResultadoIntegrado['encaminhado_treinamento_data']))->dataInsert() : null;

        $dadosResultadoIntegrado['usuario_id'] = auth()->id();
        $dadosResultadoIntegrado['selecionado'] = 'sim';
        $dadosResultadoIntegrado['obs'] = 'ADMISSÃO AVULSA';

        $dadosCurriculo['email'] = $dadosCurriculo['email'] == "" ? Sistema::EMAILPADRAO : $dadosCurriculo['email'];

        if (count($dadosCurriculo['telefones']) == 0) {
            return response()->json(['msg' => 'Por favor insira um telefone'], 400);
        }

        try {
            DB::beginTransaction();

            $empresa_id = auth()->user()->empresa_id;

            $user = User::whereHas('Curriculo', function ($q) use ($dadosCurriculo) {
                $q->whereCpf($dadosCurriculo['cpf']);
            });

            $userObj = [
                'nome' => $dadosCurriculo['nome'],
                'login' => $dadosCurriculo['email'],
                'password' => Sistema::SenhaCpf($dadosCurriculo['cpf']),
                'tipo' => isset($dadosCurriculo['tipo']) ?: 'Funcionario',
                'ativo' => true,
                'temp' => false,
                'termos' => false,
                'empresa_id' => $empresa_id
            ];

            if ($user->count() === 0) {
                $usuario = $user->create($userObj);

                $dados['feedback']['banco_conta']['user_id'] = $usuario->id;
                UsuarioConta::criarAtualizar($usuario->id, $dados['feedback']['banco_conta']);

                $usuario->Curriculo()->create($dadosCurriculo);

                $candidato = Curriculo::find($usuario->id);

                if (isset($dadosCurriculo['foto_tresDelete'])) {
                    foreach ($dadosCurriculo['foto_tresDelete'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }

                if (isset($dadosCurriculo['foto_tres'])) {
                    foreach ($dadosCurriculo['foto_tres'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $candidato->FotoTres()->attach($arquivo->id, ['tipo' => 'foto3x4']);
                        }
                    }
                }

                if (isset($dadosCurriculo['telefonesDelete'])) {
                    foreach ($dadosCurriculo['telefonesDelete'] as $index) {
                        TelefoneCurriculo::find($index)->delete();
                    }
                }

                if (isset($dadosCurriculo['telefones'])) {
                    foreach ($dadosCurriculo['telefones'] as $linha) {
                        $linha['principal'] = $linha['principal'] == 'true';
                        if ($linha['id'] == 0) {
                            $telPrincipal = $candidato->Telefones()->create($linha)->id;
                            if ($linha['principal']) {
                                $dadosFeedback['telefone_id'] = $telPrincipal;
                            }
                        } else {
                            $candidato->Telefones->find($linha['id'])->update($linha);
                            if ($linha['principal']) {
                                $dados['telefone_id'] = $linha['id'];
                            }
                        }
                    }
                }

                $feedback = $candidato->FeedBack()->create($dadosFeedback);

                $feedback->ParecerRh()->create($dadosParecerRh);
                $feedback->ParecerRota()->create($dadosParecerRota);
                $feedback->ParecerTecnica()->create($dadosParecerTecnica);
                $feedback->ParecerTeste()->create($dadosParecerTeste);
                $feedback->ResultadoIntegrado()->create($dadosResultadoIntegrado);

                $feedback->Admissao()->create($dadosAdmissao);
            } else {

                $dadosAdmissao['editado_usuario_id'] = auth()->id();
                // 1- Busca o Candidato
                $user = $user->first();
                $user->update($userObj);

                $dados['curriculo']['banco_conta']['user_id'] = $user->id;
                UsuarioConta::criarAtualizar($user->id, $dados['curriculo']['banco_conta']);

                $candidato = $user->Curriculo;
                // 2- Atualiza as informações na tabela curriculo
                $candidato->update([
                    'nome' => $dadosCurriculo['nome'],
                    'nascimento' => $dadosCurriculo['nascimento'],
                    'pcd' => $dadosCurriculo['pcd'],
                    'cid' => $dadosCurriculo['cid'],
                    'email' => $dadosCurriculo['email'],
                    'logradouro' => $dadosCurriculo['logradouro'],
                    'complemento' => $dadosCurriculo['complemento'],
                    'bairro' => $dadosCurriculo['bairro'],
                    'municipio' => $dadosCurriculo['municipio'],
                    'uf' => $dadosCurriculo['uf'],
                    'cep' => $dadosCurriculo['cep'],
                    'municipio_id' => $dadosCurriculo['municipio_id'],
                    'filiacao_pai' => $dadosCurriculo['filiacao_pai'],
                    'formacao' => $dadosCurriculo['formacao'],
                    'formacao_curso' => $dadosCurriculo['formacao_curso'],
                ]);

                if (isset($dadosAdmissao['foto_tres_delete'])) {
                    foreach ($dadosAdmissao['foto_tres_delete'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // 3- coloca a foto 3x4
                if (isset($dadosAdmissao['foto_tres'])) {
                    foreach ($dadosAdmissao['foto_tres'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $candidato->FotoTres()->attach($arquivo->id, ['tipo' => 'foto3x4']);
                        }
                    }
                }
                // 3- telefones para remoção
                if (isset($dadosCurriculo['telefonesDelete'])) {
                    foreach ($dadosCurriculo['telefonesDelete'] as $index) {
                        TelefoneCurriculo::find($index)->delete();
                    }
                }

                if (isset($dadosCurriculo['telefones'])) {
                    foreach ($dadosCurriculo['telefones'] as $linha) {
                        $linha['principal'] = $linha['principal'] == 'true';
                        if ($linha['id'] == 0) {
                            $telPrincipal = $candidato->Telefones()->create($linha);
                            if ($linha['principal']) {
                                $dadosFeedback['telefone_id'] = $telPrincipal->id;
                            }
                        } else {
                            $candidato->Telefones->find($linha['id'])->update($linha);
                            if ($linha['principal']) {
                                $dados['telefone_id'] = $linha['id'];
                            }
                        }
                    }
                }

                // 4- Atualiza ou cria o FeedbackCurriculo
                $candidato->FeedBack ? $candidato->FeedBack->update($dadosFeedback) : $candidato->FeedBack()->create($dadosFeedback);
                $candidato->FeedBack->ParecerRh ? $candidato->FeedBack->ParecerRh->update($dadosParecerRh) : $candidato->FeedBack->ParecerRh()->create($dadosParecerRh);
                $candidato->FeedBack->ParecerRota ? $candidato->FeedBack->ParecerRota->update($dadosParecerRota) : $candidato->FeedBack->ParecerRota()->create($dadosParecerRota);
                $candidato->FeedBack->ParecerTecnica ? $candidato->FeedBack->ParecerTecnica->update($dadosParecerTecnica) : $candidato->FeedBack->ParecerTecnica()->create($dadosParecerTecnica);
                $candidato->FeedBack->ParecerTeste ? $candidato->FeedBack->ParecerTeste->update($dadosParecerTeste) : $candidato->FeedBack->ParecerTeste()->create($dadosParecerTeste);
                $candidato->FeedBack->ResultadoIntegrado ? $candidato->FeedBack->ResultadoIntegrado->update($dadosResultadoIntegrado) : $candidato->FeedBack->ResultadoIntegrado()->create($dadosResultadoIntegrado);

                $candidato->FeedBack->Admissao()->create($dadosAdmissao);
            }
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ADMISSAO AVULSA STORE: {$e->getFile()} , {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($dados);
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Admissao $admissao
     * @return Admissao|ResultadoIntegrado|\Illuminate\Http\Response
     */
    public function show(FeedbackCurriculo $admissao)
    {
        $this->authorize('admissao');

        /*$admissao = ResultadoIntegrado::whereFeedbackId($admissao)->first();

        $admissao->load(['Admissao.FotoTres', 'Feedback' => function ($q) {
            $q->with('Curriculo',
                'Curriculo.FotoTres',
                'Curriculo.AnexosCpfRg',
                'Curriculo.ComprovanteEnd',
                'Curriculo.CtpsFrente',
                'Curriculo.CtpsVerso',
                'Curriculo.Antecedentes',
                'Curriculo.TituloEleitor',
                'Curriculo.CertificadoReservista',
                'Curriculo.PisRescisao',
                'Curriculo.CertificadoEscolaridade',
                'Curriculo.ContaBanco',
                'Curriculo.CartaSindicato',
                'Curriculo.CarteiraVacina',
                'Curriculo.RgcpfFilho',
                'Curriculo.CartaoVacinaFilho',
                'Curriculo.DeclaracaoEscolarFilho',
                'Curriculo.Formacao', 'Cliente.AreasEtiquetas', 'parecerRh', 'parecerTecnica', 'parecerRota', 'parecerTeste', 'VagaSelecionada', 'TelPrincipal');
        }]);

        return $admissao;*/
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Admissao $admissao
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function edit(FeedbackCurriculo $admissao)
    {
        $feedback = $admissao;

        $feedback->load(
            'Admissao',
            'Curriculo.Formacao',
            'Curriculo.FotoTres',
            'Curriculo.Telefones',
            'BancoConta',
            'parecerRh',
            'parecerTecnica',
            'parecerRota',
            'parecerTeste',
            'VagaSelecionada',
            'Cliente:id,razao_social,cnpj,nome,cpf,area_id',
            'Cliente.Area',
            'Cliente.AreasEtiquetas',
            'TelPrincipal',
            'BancoConta'
        );

        $feedback->BancoConta->banco = $feedback->BancoConta->banco ?: 'Banco do Brasil';
        $feedback->BancoConta->agencia = $feedback->BancoConta->agencia ?: '';
        $feedback->BancoConta->conta = $feedback->BancoConta->conta ?: '';
        $feedback->BancoConta->pix = $feedback->BancoConta->pix ?: false;
        $feedback->BancoConta->tipochavepix = $feedback->BancoConta->tipochavepix ?: '';
        $feedback->BancoConta->chavepix = $feedback->BancoConta->chavepix ?: '';

        $feedback->Curriculo->foto_tres_delete = [];

        $feedback->Admissao->documento = $feedback->Admissao->documento ?: '';
        $feedback->Admissao->documento_portaria = $feedback->Admissao->documento_portaria ?: '';
        $feedback->Admissao->tipo_admissao = $feedback->Admissao->tipo_admissao ?: '';
        $feedback->Admissao->treinamento = $feedback->Admissao->treinamento ?: '';
        $feedback->Admissao->nr_trinta_tres = $feedback->Admissao->nr_trinta_tres ?: '';
        $feedback->Admissao->nr_trinta_cinco = $feedback->Admissao->nr_trinta_cinco ?: '';
        $feedback->Admissao->status_carteira_treinamento = $feedback->Admissao->status_carteira_treinamento ?: '';
        $feedback->Admissao->area_etiqueta_id = $feedback->Admissao->area_etiqueta_id ?: "";
        $feedback->Admissao->documento = $feedback->Admissao->documento ?: "";
        $feedback->Admissao->documento_portaria = $feedback->Admissao->documento_portaria ?: "";
        $feedback->Admissao->tipo_admissao = $feedback->Admissao->tipo_admissao ?: "";
        $feedback->Admissao->tipo_treinamento = $feedback->Admissao->tipo_treinamento ?: "";
        $feedback->Admissao->treinamento = $feedback->Admissao->treinamento ?: "";
        $feedback->Admissao->nr_trinta_tres = $feedback->Admissao->nr_trinta_tres ?: "";
        $feedback->Admissao->nr_trinta_cinco = $feedback->Admissao->nr_trinta_cinco ?: "";
        $feedback->Admissao->trinta_dois_sessenta = $feedback->Admissao->trinta_dois_sessenta ?: "";
        $feedback->Admissao->foto_escaneada = $feedback->Admissao->foto_escaneada ?: "";
        $feedback->Admissao->status_carteira_treinamento = $feedback->Admissao->status_carteira_treinamento ?: "";
        $feedback->Admissao->data_admissao = $feedback->Admissao->data_admissao ?: "";
        $feedback->Admissao->data_aso = $feedback->Admissao->data_aso ?: "";
        $feedback->Admissao->salario = $feedback->Admissao->salario ?: "0,00";

        $feedback->parecerRh->indicado_por = $feedback->parecerRh->indicado_por ?: "";
        $feedback->parecerRh->calca = $feedback->parecerRh->calca ?: "";
        $feedback->parecerRh->bota = $feedback->parecerRh->bota ?: "";
        $feedback->parecerRh->camisa_protecao = $feedback->parecerRh->camisa_protecao ?: "";
        $feedback->parecerRh->camisa_meia = $feedback->parecerRh->camisa_meia ?: "";

        $feedback->parecerTecnica->indicado_area = $feedback->parecerTecnica->indicado_area ?: "";

        return response()->json(['feedback' => $feedback], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Admissao $admissao
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, FeedbackCurriculo $admissao)
    {
        $this->authorize('admissao_update');
        $dados = $request->input();

        $feedback = $admissao;
        $admissaoDados = $dados['admissao'];

        $dados['curriculo']['email'] = $dados['curriculo']['email'] == "" ? Sistema::EMAILPADRAO : $dados['curriculo']['email'];
        Sistema::telegram(print_r($dados));
//        if ($request->filled('admissao.foto_escaneada')) {
//            $dados['foto_escaneada'] = $dados['foto_escaneada'] == 'true' ? true : false;
//        }

//        $adm = Admissao::whereCurriculoId($request->curriculo_id);

        $dadosValidados = \Validator::make($dados, [
//            'nome' => 'required|min:3',
//            'email' => 'required|email',
//            'descricao' => 'required|min:3',
//            'ativo' => 'required|boolean',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Admissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $feedback->Curriculo->update([
                    'nome' => $dados['curriculo']['nome'],
                    'email' => $dados['curriculo']['email'],
                    'filiacao_pai' => $dados['curriculo']['filiacao_pai'],
                    'filiacao_mae' => $dados['curriculo']['filiacao_mae'],
                ]);

                if ($feedback->parecerRh) {
                    $feedback->parecerRh->update(
                        [
                            'indicado_por' => $dados['parecer_rh']['indicado_por'],
                            'calca' => $dados['parecer_rh']['calca'],
                            'bota' => $dados['parecer_rh']['bota'],
                            'camisa_protecao' => $dados['parecer_rh']['camisa_protecao'],
                            'camisa_meia' => $dados['parecer_rh']['camisa_meia'],
                        ]
                    );
                } else {
                    $feedback->parecerRh()->create(['indicado_por' => $dados['parecer_rh']['indicado_por']]);
                }

                if ($feedback->parecerTecnica) {
                    $feedback->parecerTecnica->update(['indicado_area' => $dados['parecer_tecnica']['indicado_area']]);
                } else {
                    $feedback->parecerTecnica()->create(['indicado_area' => $dados['parecer_tecnica']['indicado_area']]);
                }

                $dados['banco_conta']['user_id'] = $feedback->curriculo_id;

                UsuarioConta::criarAtualizar($feedback->curriculo_id, $dados['banco_conta']);

                if (isset($dados['curriculo']['foto_tres_delete'])) {
                    foreach ($dados['curriculo']['foto_tres_delete'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }

                if (isset($dados['curriculo']['foto_tres'])) {
                    foreach ($dados['curriculo']['foto_tres'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->Curriculo->FotoTres()->attach($arquivo->id, ['tipo' => 'foto3x4']);
                        }
                    }
                }


                $feedback->Admissao ? $feedback->Admissao->update($admissaoDados) : $feedback->Admissao()->create($admissaoDados);

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error ADMISSÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Admissao $admissao
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admissao $admissao)
    {
        //
    }

    /**
     * @param Request $request
     * @return FeedbackCurriculo|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function filtro(Request $request)
    {
        $resultado = FeedbackCurriculo::whereHas('ResultadoIntegrado')
            ->with(
                'Admissao:id,feedback_id,status,numero_cracha',
                'ResultadoIntegrado',
                'Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
                'Curriculo.FotoTres:id',
                'vagaSelecionada',
                'Cliente:id,razao_social,cnpj,nome,cpf,area_id',
                'Cliente.Area',
            );

        $filtroPeriodo = $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                $q->where('created_at', '>=', $dataInicio->dataInsert())->where('created_at', '<=', $dataFim->dataInsert());
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
                $query->whereCpf($request->campoBusca);
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

        $resultado = $resultado->orderByDesc('created_at');

        return $resultado;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request)
    {
        $pg = $this->filtro($request)->paginate($request->porPag ?: 20);
        return Sistema::pg($pg);
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_FOTOCURRICULO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_FOTOCURRICULO, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_FOTOCURRICULO, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_FOTOCURRICULO, $arquivo);
    }

    //PDF
    public function getFichaPdf(FeedbackCurriculo $feedback)
    {
//        $dados = ResultadoIntegrado::whereFeedbackId($curriculo_id)->first();

//        $dados = $feedback;

        $dados = $feedback->load('ResultadoIntegrado');

        $pdf = PDF::loadView('pdf.admissao.ficha', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("ficha_admissao_" . ($dados->Curriculo->nome) . ".pdf");
    }

    //Excel
    public function export(Request $request)
    {
        $admissao = Admissao::has('ResultadoIntegrado');

        if ($request->selecionados) {
            $admissao = $admissao->whereIn('curriculo_id', $request->selecionados);
        } else {
            if ($request->filled('campoVaga')) {
                $admissao->whereHas('Feedback.VagaSelecionada', function ($query) use ($request) {
                    $query->whereId($request->campoVaga);
                });
            }

            if ($request->filled('campoCliente')) {
                $admissao->whereHas('Feedback', function ($q) use ($request) {
                    $q->whereClienteId(auth()->user()->cliente_id == User::BPSE ? $request->campoCliente : auth()->user()->cliente_id);
                });
            }

            if ($request->filled('campoUf')) {
                $admissao->whereHas('Curriculo', function ($q) use ($request) {
                    $q->whereUfVaga($request->campoUf);
                });
            }

            if ($request->filled('campoPcd')) {
                $campoPcd = $request->campoPcd == 'true' ? true : false;
                $admissao->whereHas('Curriculo', function ($query) use ($campoPcd) {
                    $query->wherePcd($campoPcd);
                });
            }
        }

        $admissao = $admissao->get();
        return Excel::download(new admissaoExport($admissao), 'admissao.xlsx');
    }

    public function buscaCPF(Request $request)
    {
        $cpf = Sistema::transformCpfCnpj($request->cpf);
        $admissao = Admissao::whereHas('Feedback.Curriculo', function ($q) use ($cpf) {
            $q->whereCpf($cpf);
        });

        // Se o cara ja possui cadastro na Admissão
        if ($admissao->count() > 0) {
            return response()->json([
                'msg' => "Candidato {$admissao->first()->Feedback->Curriculo->id} - {$admissao->first()->Feedback->Curriculo->nome} ja possui cadastro de admissão desde " . DataHora::dataFormatada($admissao->first()->created_at),
            ], 400);
        } else {

            //cpf virgem = 018.791.043-00
            //cpf no recrutamento ainda = 010.368.413-16

            $curriculo = Curriculo::whereCpf($cpf);
            if ($curriculo->count() > 0) {
                $curriculo = $curriculo->first();

                $curriculo->pcd = $curriculo->pcd ?: false;

                $curriculo->autocomplete_label_municipio_modal = $curriculo->Cidade ? $curriculo->Cidade->nome . ' - ' . $curriculo->Cidade->uf : '';
                $curriculo->autocomplete_label_municipio_modal_anterior = $curriculo->Cidade ? $curriculo->Cidade->nome . ' - ' . $curriculo->Cidade->uf : '';


                if ($curriculo->FeedBack) {
                    $feedback = $curriculo->FeedBack;

                    $feedback->vaga_id = $feedback->vaga_id ? $feedback->vaga_id : '';
                    $feedback->autocomplete_label_vaga_modal = $feedback->vaga_id ? $feedback->VagaSelecionada->nome : '';
                    $feedback->autocomplete_label_vaga_modal_anterior = $feedback->vaga_id ? $feedback->VagaSelecionada->nome : '';
                    $feedback->autocomplete_label_cliente_modal = $feedback->vaga_id ? $feedback->Cliente->razao_social . ' | ' . $feedback->Cliente->cnpj : '';
                    $feedback->autocomplete_label_cliente_modal_anterior = $feedback->vaga_id ? $feedback->Cliente->razao_social . ' | ' . $feedback->Cliente->cnpj : '';
                } else {
                    $feedback = new \stdClass();
                    $feedback->vaga_id = '';
                    $feedback->cliente_id = '';
                    $feedback->interesse = true;
                    $feedback->autocomplete_label_vaga_modal = '';
                    $feedback->autocomplete_label_vaga_modal_anterior = '';
                    $feedback->autocomplete_label_cliente_modal = '';
                    $feedback->autocomplete_label_cliente_modal_anterior = '';
                }

                if ($curriculo->FeedBack && $curriculo->FeedBack->parecerRh) {
                    $parecerRH = $curriculo->FeedBack->parecerRh;
                    $parecerRH->ex_funcionario = $parecerRH->ex_funcionario ? $parecerRH->ex_funcionario : false;
                } else {
                    $parecerRH = new \stdClass();
                    $parecerRH->ex_funcionario = false;
                    $parecerRH->calca = '';
                    $parecerRH->bota = '';
                    $parecerRH->camisa_protecao = '';
                    $parecerRH->camisa_meia = '';
                    $parecerRH->turnos_seis_por_dois = '';
                    $parecerRH->indicacao = '';
                    $parecerRH->indicado_por = '';
                }

                if ($curriculo->FeedBack && $curriculo->FeedBack->parecerTecnica) {
                    $parecerTecnica = $curriculo->FeedBack->parecerTecnica;
                } else {
                    $parecerTecnica = new \stdClass();
                    $parecerTecnica->indicado_area = 'NÃO SE APLICA';
                    $parecerTecnica->experiencia_cargas_rigger = 'NÃO SE APLICA';
                    $parecerTecnica->opera_plat_movel = 'NÃO SE APLICA';
                    $parecerTecnica->opera_plat_ponte = 'NÃO SE APLICA';
                }


                if ($curriculo->FeedBack && $curriculo->FeedBack->ParecerRota) {
                    $parecerRota = $curriculo->FeedBack->ParecerRota;
                } else {
                    $parecerRota = new \stdClass();
                    $parecerRota->bairro_rota = '';
                    $parecerRota->ponto_referencia_rota = '';
                    $parecerRota->ponto_referencia_residencia = '';
                }

                if ($curriculo->FeedBack && $curriculo->FeedBack->ParecerTeste) {
                    $parecerTeste = $curriculo->FeedBack->ParecerTeste;
                } else {
                    $parecerTeste = new \stdClass();
                    $parecerTeste->qual_teste = '';
                    $parecerTeste->parecer_final_teste = '';
                }


                if ($curriculo->FeedBack && $curriculo->FeedBack->ResultadoIntegrado) {
                    $resultadoIntegrado = $curriculo->FeedBack->ResultadoIntegrado;

                    $resultadoIntegrado->documentos_entregue = $resultadoIntegrado->documentos_entregue ?: false;
                    $resultadoIntegrado->encaminhado_exame = $resultadoIntegrado->encaminhado_exame ?: false;
                    $resultadoIntegrado->encaminhado_treinamento = $resultadoIntegrado->encaminhado_treinamento ?: false;
                    $resultadoIntegrado->excessao = $resultadoIntegrado->excessao ?: false;

                } else {
                    $resultadoIntegrado = new \stdClass();
                    $resultadoIntegrado->documentos_entregue = '';
                    $resultadoIntegrado->documentos_entregue_data = '';
                    $resultadoIntegrado->encaminhado_exame = '';
                    $resultadoIntegrado->encaminhado_exame_data = '';
                    $resultadoIntegrado->encaminhado_treinamento = '';
                    $resultadoIntegrado->encaminhado_treinamento_data = '';
                    $resultadoIntegrado->excessao = '';
                    $resultadoIntegrado->autorizado_por = '';
                    $resultadoIntegrado->responsavel_envio = '';
                }

                return response()->json(
                    [
                        'achou' => true,
                        'curriculo' => $curriculo->load('Telefones'),
                        'feedback' => $feedback,
                        'parecer_rh' => $parecerRH,
                        'parecer_tecnica' => $parecerTecnica,
                        'parecer_rota' => $parecerRota,
                        'parecer_teste' => $parecerTeste,
                        'resultado_integrado' => $resultadoIntegrado
                    ]
                    , 200);
            } else {
                return response()->json(['achou' => false], 200);
            }
        }

    }
}
