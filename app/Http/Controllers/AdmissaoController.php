<?php

namespace App\Http\Controllers;

use App\Exports\Entrevistas\admissaoExport;
use App\Mail\Admissao\Historico\AvaliacaoNoventaVencimento\AvaliacaoNoventaVencimentoMail;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\AvaliacaoNoventaVencimento;
use App\Models\AvaliacaoVencimento;
use App\Models\Curriculo;
use App\Models\DadosAdmissao;
use App\Models\FeedbackCurriculo;
use App\Models\ResultadoIntegrado;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\UsuarioConta;
use App\Models\VagasAbertas;
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

        $vaga = VagasAbertas::find($dados['feedback']['vaga_id']);

        $dadosCurriculo = $dados['curriculo'];
        $dadosCurriculo['email'] = trim(strtolower($dadosCurriculo['email']));
        $dadosCurriculo['vaga_pretendida'] = $vaga->vaga_id;
        $dadosCurriculo['municipio_id'] = $vaga->municipio_id;
        $dadosCurriculo['uf_vaga'] = substr($dados['feedback']['autocomplete_label_vaga_modal'], -2, 2);

        $dadosCurriculo['pcd'] = $dadosCurriculo['pcd'] == 'true';

        $dadosFeedback = $dados['feedback'];
        $dadosFeedback['interesse'] = $dadosFeedback['interesse'] == 'true';
        $dadosFeedback['usuario_entrevista_marcado'] = auth()->id();
        $dadosFeedback['data_entrevista'] = null;
        $dadosFeedback['selecionado'] = 'sim';
        $dadosFeedback['vagas_abertas_id'] = $dados['feedback']['vaga_id'];
        $dadosFeedback['vaga_id'] = $vaga->vaga_id;

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

        $somenteDadosAdmissao = $dadosAdmissao['dados_admissoes'];
        $tableDadosAdmissao['ctps_numero'] = $somenteDadosAdmissao['ctps_numero'];
        $tableDadosAdmissao['ctps_serie'] = $somenteDadosAdmissao['ctps_serie'];
        $tableDadosAdmissao['ctps_data_emissao'] = $somenteDadosAdmissao['ctps_data_emissao'];
        $tableDadosAdmissao['titulo_eleitor_numero'] = $somenteDadosAdmissao['titulo_eleitor_numero'];
        $tableDadosAdmissao['titulo_eleitor_sessao'] = $somenteDadosAdmissao['titulo_eleitor_sessao'];
        $tableDadosAdmissao['titulo_eleitor_zona'] = $somenteDadosAdmissao['titulo_eleitor_zona'];

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

        $tipo_admissao = [
            'TEMPORARIO',
            'DETERMINADO'
        ];

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

                $datas = [];
                if ($dadosAdmissao['tipo_admissao'] == 'FIXO') {
                    $data = new DataHora($dadosAdmissao['data_admissao']);
                    switch ($dadosAdmissao['prazo_experiencia']) {
                        case '30+30':
                            $datas['prazo_dez_inicial'] = $data->addDia(20);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(20);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                        case '45+45':
                            $datas['prazo_dez_inicial'] = $data->addDia(35);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(35);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                        case '30+60':
                            $datas['prazo_dez_inicial'] = $data->addDia(20);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(50);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                        case '60+30':
                            $datas['prazo_dez_inicial'] = $data->addDia(50);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(20);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                    }
                    $dadosAdmissao['data_encerramento'] = null;
                }
                if (in_array($dadosAdmissao['tipo_admissao'], $tipo_admissao)) {
                    $data = new DataHora($dadosAdmissao['data_encerramento']);

                    $datas['prazo_dez_inicial'] = $data->subtrairDia(5);
                    $datas['prazo_cinco_inicial'] = $data->subtrairDia(5);
                    $datas['prazo_dia_inicial'] = $dadosAdmissao['data_encerramento'];
                    $datas['prazo_dez_final'] = null;
                    $datas['prazo_cinco_final'] = null;
                    $datas['prazo_dia_final'] = null;
                    $dadosAdmissao['prazo_experiencia'] = null;
                }


                $feedback = $candidato->FeedBack()->create($dadosFeedback);

                $feedback->ParecerRh()->create($dadosParecerRh);
                $feedback->ParecerRota()->create($dadosParecerRota);
                $feedback->ParecerTecnica()->create($dadosParecerTecnica);
                $feedback->ParecerTeste()->create($dadosParecerTeste);
                $feedback->ResultadoIntegrado()->create($dadosResultadoIntegrado);

                $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback['id'])->first();

                $datas['feedback_id'] = $feedback['id'];
                $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);

                $admissaoCreate = $feedback->Admissao()->create($dadosAdmissao);

                $tableDadosAdmissao['admissao_id'] = $admissaoCreate['id'];
                DadosAdmissao::create($tableDadosAdmissao);

            } else {

                $dadosAdmissao['editado_usuario_id'] = auth()->id();
                // 1 - Busca o Candidato
                $user = $user->first();
                $user->update($userObj);

                $dados['curriculo']['banco_conta']['user_id'] = $user->id;
                UsuarioConta::criarAtualizar($user->id, $dados['curriculo']['banco_conta']);

                $candidato = $user->Curriculo;
                // 2 - Atualiza as informações na tabela curriculo
                $candidato->update([
                    'nome' => $dadosCurriculo['nome'],
                    'rg' => $dadosCurriculo['rg'],
                    'rg_data_emissao' => $dadosCurriculo['rg_data_emissao'],
                    'naturalidade' => $dadosCurriculo['naturalidade'],
                    'nascimento' => $dadosCurriculo['nascimento'],
                    'pcd' => $dadosCurriculo['pcd'],
                    'cid' => $dadosCurriculo['cid'],
                    'email' => $dadosCurriculo['email'],
                    'logradouro' => $dadosCurriculo['logradouro'],
                    'end_numero' => $dadosCurriculo['end_numero'],
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

                $datas = [];

                if ($dadosAdmissao['tipo_admissao'] === 'FIXO') {
                    $data = new DataHora($dadosAdmissao['data_admissao']);
                    switch ($dadosAdmissao['prazo_experiencia']) {
                        case '30+30':
                            $datas['prazo_dez_inicial'] = $data->addDia(20);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(20);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                        case '45+45':
                            $datas['prazo_dez_inicial'] = $data->addDia(35);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(35);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                        case '30+60':
                            $datas['prazo_dez_inicial'] = $data->addDia(20);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(50);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                        case '60+30':
                            $datas['prazo_dez_inicial'] = $data->addDia(50);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(20);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                    }
                    $dadosAdmissao['data_encerramento'] = null;
                }
                if (in_array($dadosAdmissao['tipo_admissao'], $tipo_admissao)) {
                    $data = new DataHora($dadosAdmissao['data_encerramento']);

                    $datas['prazo_dez_inicial'] = $data->subtrairDia(5);
                    $datas['prazo_cinco_inicial'] = $data->subtrairDia(5);
                    $datas['prazo_dia_inicial'] = $dadosAdmissao['data_encerramento'];
                    $datas['prazo_dez_final'] = null;
                    $datas['prazo_cinco_final'] = null;
                    $datas['prazo_dia_final'] = null;
                    $dadosAdmissao['prazo_experiencia'] = null;
                }


                // 4- Atualiza ou cria o FeedbackCurriculo
                $candidato->FeedBack ? $candidato->FeedBack->update($dadosFeedback) : $candidato->FeedBack()->create($dadosFeedback);
                $candidato->FeedBack->ParecerRh ? $candidato->FeedBack->ParecerRh->update($dadosParecerRh) : $candidato->FeedBack->ParecerRh()->create($dadosParecerRh);
                $candidato->FeedBack->ParecerRota ? $candidato->FeedBack->ParecerRota->update($dadosParecerRota) : $candidato->FeedBack->ParecerRota()->create($dadosParecerRota);
                $candidato->FeedBack->ParecerTecnica ? $candidato->FeedBack->ParecerTecnica->update($dadosParecerTecnica) : $candidato->FeedBack->ParecerTecnica()->create($dadosParecerTecnica);
                $candidato->FeedBack->ParecerTeste ? $candidato->FeedBack->ParecerTeste->update($dadosParecerTeste) : $candidato->FeedBack->ParecerTeste()->create($dadosParecerTeste);
                $candidato->FeedBack->ResultadoIntegrado ? $candidato->FeedBack->ResultadoIntegrado->update($dadosResultadoIntegrado) : $candidato->FeedBack->ResultadoIntegrado()->create($dadosResultadoIntegrado);

                $feedback_id = $candidato->FeedBack ? $candidato->FeedBack->id : '';
                $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback_id)->first();

                $datas['feedback_id'] = $feedback_id;
                $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);


                $admissaoCreate = $candidato->FeedBack->Admissao()->create($dadosAdmissao);
                $tableDadosAdmissao['admissao_id'] = $admissaoCreate['id'];
                DadosAdmissao::create($tableDadosAdmissao);
            }
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ADMISSAO AVULSA STORE: {$e->getFile()} , {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($dados);
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
//            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
            'Admissao.DadosAdmissoes',
            'Curriculo.Formacao',
            'Curriculo.FotoTres',
            'Curriculo.Telefones',
            'parecerRh',
            'parecerTecnica',
            'parecerRota',
            'parecerTeste',
            'VagaSelecionada',
            'Cliente:id,razao_social,cnpj,nome,cpf,area_id',
            'Cliente.Area',
            'Cliente.AreasEtiquetas',
            'TelPrincipal',
            'BancoConta',
            'ResultadoIntegrado'
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
        $feedback->Admissao->area_etiqueta_id = $feedback->Admissao->area_etiqueta_id == 0 ? null : $feedback->Admissao->area_etiqueta_id;
        $feedback->Admissao->documento = $feedback->Admissao->documento ?: "";
        $feedback->Admissao->documento_portaria = $feedback->Admissao->documento_portaria ?: "";
        $feedback->Admissao->tipo_admissao = $feedback->Admissao->tipo_admissao ?: "";
        $feedback->Admissao->tipo_treinamento = $feedback->Admissao->tipo_treinamento ?: "";
        $feedback->Admissao->treinamento = $feedback->Admissao->treinamento ?: "";
        $feedback->Admissao->nr_trinta_tres = $feedback->Admissao->nr_trinta_tres ?: "";
        $feedback->Admissao->nr_trinta_cinco = $feedback->Admissao->nr_trinta_cinco ?: "";
        $feedback->Admissao->trinta_dois_sessenta = $feedback->Admissao->trinta_dois_sessenta ?: "";
        $feedback->Admissao->foto_escaneada = $feedback->Admissao->foto_escaneada ?: "";
        $feedback->Admissao->status = $feedback->Admissao->status ?: "";
        $feedback->Admissao->data_admissao = $feedback->Admissao->data_admissao ?: "";
        $feedback->Admissao->data_aso = $feedback->Admissao->data_aso ?: "";
        $feedback->Admissao->salario = $feedback->Admissao->salario ?: "0,00";
        $feedback->Admissao->prazo_experiencia = $feedback->Admissao->prazo_experiencia ?: "";

        $feedback->parecerRh->indicado_por = $feedback->parecerRh->indicado_por ?: "";
        $feedback->parecerRh->calca = $feedback->parecerRh->calca ?: "";
        $feedback->parecerRh->bota = $feedback->parecerRh->bota ?: "";
        $feedback->parecerRh->camisa_protecao = $feedback->parecerRh->camisa_protecao ?: "";
        $feedback->parecerRh->camisa_meia = $feedback->parecerRh->camisa_meia ?: "";

        $feedback->parecerTecnica->indicado_area = $feedback->parecerTecnica->indicado_area ?: "";

        $feedback->autocomplete_label_vaga_modal = $feedback->VagaAberta->VagaSelecionada ? $feedback->VagaAberta->VagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';
        $feedback->autocomplete_label_vaga_modal_anterior = $feedback->VagaAberta->VagaSelecionada ? $feedback->VagaAberta->VagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';

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

        $dadosVagaAberta = VagasAbertas::find($dados['vagas_abertas_id']);

        $dados['curriculo']['email'] = $dados['curriculo']['email'] == "" ? Sistema::EMAILPADRAO : $dados['curriculo']['email'];

        $dadosValidados = \Validator::make($dados, []);
        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Salvar Admissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $feedback->update([
                    'vaga_id' => $dadosVagaAberta->vaga_id,
                    'vagas_abertas_id' => $dadosVagaAberta->id,
                ]);

                $feedback->Curriculo->update([
                    'nome' => $dados['curriculo']['nome'],
                    'email' => $dados['curriculo']['email'],
                    'rg' => $dados['curriculo']['rg'],
                    'rg_data_emissao' => $dados['curriculo']['rg_data_emissao'],
                    'naturalidade' => $dados['curriculo']['naturalidade'],
                    'filiacao_pai' => $dados['curriculo']['filiacao_pai'],
                    'filiacao_mae' => $dados['curriculo']['filiacao_mae'],
                    'pcd' => $dados['curriculo']['pcd'],
                    'cnh' => $dados['curriculo']['cnh'],
                    'logradouro' => $dados['curriculo']['logradouro'],
                    'complemento' => $dados['curriculo']['complemento'],
                    'end_numero' => $dados['curriculo']['end_numero'],
                    'bairro' => $dados['curriculo']['bairro'],
                    'municipio' => $dados['curriculo']['municipio'],
                    'uf' => $dados['curriculo']['uf'],
                    'cep' => $dados['curriculo']['cep'],
                    'municipio_id' => $dados['curriculo']['municipio_id'],
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

                $dados['resultado_integrado']['documentos_entregue_data'] = $dados['resultado_integrado']['documentos_entregue'] ? $dados['resultado_integrado']['documentos_entregue_data'] : null;
                $dados['resultado_integrado']['encaminhado_exame_data'] = $dados['resultado_integrado']['encaminhado_exame'] ? $dados['resultado_integrado']['encaminhado_exame_data'] : null;
                $dados['resultado_integrado']['encaminhado_treinamento_data'] = $dados['resultado_integrado']['encaminhado_treinamento'] ? $dados['resultado_integrado']['encaminhado_treinamento_data'] : null;

                $dadosResultadoIntegrado = $dados['resultado_integrado'];

                $feedback->ResultadoIntegrado ? $feedback->ResultadoIntegrado->update($dadosResultadoIntegrado) : $feedback->ResultadoIntegrado()->create($dadosResultadoIntegrado);

                $dadosAdmissoes = $admissaoDados['dados_admissoes'];
                isset($admissaoDados['dados_admissoes']['id']) ? $feedback->Admissao->DadosAdmissoes->update($dadosAdmissoes) : $feedback->Admissao->DadosAdmissoes()->create($dadosAdmissoes);

                $datas = [];
                $tipo_admissao = [
                    'TEMPORARIO',
                    'DETERMINADO'
                ];
                if ($admissaoDados['tipo_admissao'] == 'FIXO') {
                    $data = new DataHora($admissaoDados['data_admissao']);
                    switch ($admissaoDados['prazo_experiencia']) {
                        case '30+30':
                            $datas['prazo_dez_inicial'] = $data->addDia(20);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(20);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                        case '45+45':
                            $datas['prazo_dez_inicial'] = $data->addDia(35);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(35);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                        case '30+60':
                            $datas['prazo_dez_inicial'] = $data->addDia(20);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(50);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                        case '60+30':
                            $datas['prazo_dez_inicial'] = $data->addDia(50);
                            $datas['prazo_cinco_inicial'] = $data->addDia(5);
                            $datas['prazo_dia_inicial'] = $data->addDia(5);
                            $datas['prazo_dez_final'] = $data->addDia(20);
                            $datas['prazo_cinco_final'] = $data->addDia(5);
                            $datas['prazo_dia_final'] = $data->addDia(5);
                            break;
                    }
                    $admissaoDados['data_encerramento'] = null;

                }
                if (in_array($admissaoDados['tipo_admissao'], $tipo_admissao)) {
                    $data = new DataHora($admissaoDados['data_encerramento']);

                    $datas['prazo_dez_inicial'] = $data->subtrairDia(5);
                    $datas['prazo_cinco_inicial'] = $data->subtrairDia(5);
                    $datas['prazo_dia_inicial'] = $admissaoDados['data_encerramento'];
                    $datas['prazo_dez_final'] = null;
                    $datas['prazo_cinco_final'] = null;
                    $datas['prazo_dia_final'] = null;
                    $admissaoDados['prazo_experiencia'] = null;
                }

                $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($admissaoDados['feedback_id'])->first();

                $datas['feedback_id'] = $admissaoDados['feedback_id'];
                $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);

                $feedback->Admissao ? $feedback->Admissao->update($admissaoDados) : $feedback->Admissao()->create($admissaoDados);

                if (isset($dadosCurriculo['telefonesDelete'])) {
                    foreach ($dadosCurriculo['telefonesDelete'] as $index) {
                        TelefoneCurriculo::find($index)->delete();
                    }
                }

                if (isset($dadosCurriculo['telefones'])) {
                    foreach ($dadosCurriculo['telefones'] as $linha) {
                        $linha['principal'] = $linha['principal'] == 'true';
                        if ($linha['id'] == 0) {
                            $telPrincipal = $feedback->Telefones()->create($linha);
                            if ($linha['principal']) {
                                $dadosFeedback['telefone_id'] = $telPrincipal->id;
                            }
                        } else {
                            $feedback->Telefones->find($linha['id'])->update($linha);
                            if ($linha['principal']) {
                                $dados['telefone_id'] = $linha['id'];
                            }
                        }
                    }
                }

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error ADMISSÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    public function cadastraMassa(Request $request)
    {
        $this->authorize('admissao_update');
        $dados = $request->input();

//        dd($dados);

        $dadosValidados = \Validator::make($dados, []);
        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Salvar em Massa',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();


                foreach ($dados['selecionados'] as $feedback_id) {
                    $feedback = FeedbackCurriculo::find($feedback_id);


                    $feedback->update(['selecionado' => $dados['selecionado']]);
//                    dd($feedback);

                    $dados = [
                        "tipo_admissao" => $dados['tipo_admissao'],
                        "prazo_experiencia" => $dados['prazo_experiencia'],
                        "data_encerramento" => $dados['data_encerramento'],
                        "documento_portaria" => $dados['documento_portaria'],
                        "data_aso" => $dados['data_aso'],
                        "status_carteira_treinamento" => $dados['status_carteira_treinamento'],
                        "status" => $dados['status'],
                        "data_admissao" => $dados['data_admissao'],
                        "data_entrega_area" => $dados['data_entrega_area'],
                        "biometria" => $dados['biometria'],
                    ];

                    $feedback->Admissao ? $feedback->Admissao->update($dados) : $feedback->Admissao()->create($dados);
                }
                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error ADMISSÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
                'VagaAberta.VagaSelecionada',
                'VagaAberta.Municipio',
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
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('VagaAberta.Municipio', function ($q) use ($request) {
                $q->whereUf($request->campoUf);
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
        $dados = ['admissao_processo_dados_editar' => auth()->user()->can('admissao_processo_dados_editar')];
        return Sistema::pg($pg, $dados);
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

//        $dadosAdmissao = Admissao::whereFeedbackId($feedback->id)->with('DadosAdmissoes')->get();

//        dd($dadosAdmissao);

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
                    $feedback->autocomplete_label_vaga_modal = $feedback->vaga_id ? $feedback->VagaAberta->VagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';
                    $feedback->autocomplete_label_vaga_modal_anterior = $feedback->vaga_id ? $feedback->VagaAberta->VagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';
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
