<?php

namespace App\Http\Controllers;

use App\Imports\Admissaoimport;
use App\Jobs\Admissao\Importacao\ImportJob;
use App\Jobs\Admissao\Importacao\ImportacaoAdmissaoJob;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Jobs\JobExportaAdmissoes;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\AvaliacaoNoventaVencimento;
use App\Models\CentroCusto;
use App\Models\Curriculo;
use App\Models\DadosAdmissao;
use App\Models\Demissao;
use App\Models\EmpresaExame;
use App\Models\EntrevistaDesligamento;
use App\Models\ExameFuncionario;
use App\Models\FeedbackCurriculo;
use App\Models\Formulario;
use App\Models\Pcmso;
use App\Models\ResultadoIntegrado;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\UsuarioConta;
use App\Models\UsuarioDependente;
use App\Models\VagaProjeto;
use App\Models\VagasAbertas;
use App\Rules\CpfValidoEmpresaRules;
use App\Rules\VagaAbertaEmpresaRules;
use App\Rules\VerificaCpfEmpresaRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MasterTag\DataHora;
use PDF;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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

        $dados['admissao']['cargo'] = $vaga->Vaga->nome;
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

        if (in_array($dados['admissao']['status'], [Admissao::STATUS_ADMISSAO_ADMITIDO, Admissao::STATUS_ADMISSAO_PRONTOPARAADMISSAO])) {
            if (trim($dados['admissao']['data_admissao']) == 0) {
                return response()->json([
                    'msg' => 'Informe a data do Admissão'
                ], 400);
            }
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
                            TelefoneCurriculo::find($linha['id'])->update($linha);
                            if ($linha['principal']) {
                                $dados['telefone_id'] = $linha['id'];
                            }
                        }
                    }
                }

                $feedback = $candidato->FeedBack()->create($dadosFeedback);

                if (isset($dadosCurriculo['dependentesDelete'])) {
                    foreach ($dadosCurriculo['dependentesDelete'] as $id) {
                        $feedback->Curriculo->Dependentes->find($id)->delete();
                    }
                }

                if (isset($dadosCurriculo['dependentes'])) {
                    foreach ($dadosCurriculo['dependentes'] as $linha) {
                        if (isset($linha['nova'])) {
                            $feedback->Curriculo->Dependentes()->create($linha);
                        } else {
                            $feedback->Curriculo->Dependentes->find($linha['id'])->update($linha);
                        }
                    }
                }

                //Logica de Vagas
                if ($feedback->vaga_projeto_id) {
                    if ($request->input('vaga_projeto_id') != $feedback->vaga_projeto_id) {
                        if ($request->filled('vaga_projeto_id')) {
                            $vagaProjeto = VagaProjeto::find($request->input('vaga_projeto_id'));
                            $vagaAnterior = VagaProjeto::find($feedback->vaga_projeto_id);
                            if ($vagaProjeto->tem_vaga) {
                                $vagaAnterior->decrement('qnt_preenchida');
                                $vagaAnterior->save();
                                $vagaAnterior->Projeto->decrement('preenchidas');

                                $vagaProjeto->increment('qnt_preenchida');
                                $vagaProjeto->save();
                                $vagaProjeto->Projeto->increment('preenchidas');

                                $feedback->update(['vaga_projeto_id' => $vagaProjeto->id]);
                            } else {
                                return response()->json([
                                    'msg' => 'Vaga não disponível para o projeto selecionado'
                                ], 400);
                            }
                        } else {
                            $vagaProjeto = VagaProjeto::find($feedback->vaga_projeto_id);
                            $vagaProjeto->decrement('qnt_preenchida');
                            $vagaProjeto->save();
                            $vagaProjeto->Projeto->decrement('preenchidas');
                            $feedback->update(['vaga_projeto_id' => null]);
                        }
                    }
                }

                //Continuacao da Logica de Vagas
                if ($request->filled('vaga_projeto_id')) {
                    if (!$feedback->vaga_projeto_id) {
                        $vagaProjeto = VagaProjeto::find($request->input('vaga_projeto_id'));
                        if ($vagaProjeto->tem_vaga) {
                            $vagaProjeto->increment('qnt_preenchida');
                            $vagaProjeto->save();
                            $vagaProjeto->Projeto->increment('preenchidas');
                            $feedback->update(['vaga_projeto_id' => $vagaProjeto->id]);
                        } else {
                            return response()->json([
                                'msg' => 'Vaga não disponível para o projeto selecionado'
                            ], 400);
                        }
                    }
                }

                $feedback->parecerRh()->create($dadosParecerRh);
                $feedback->parecerRota()->create($dadosParecerRota);
                $feedback->parecerTecnica()->create($dadosParecerTecnica);
                $feedback->parecerTeste()->create($dadosParecerTeste);

                $feedback->ResultadoIntegrado()->create($dadosResultadoIntegrado);

                is_null($dados['resultado_integrado']['empresa_exame_id']) ? $empresaExame = null : $empresaExame = EmpresaExame::find($dados['resultado_integrado']['empresa_exame_id']);
                is_null($dados['resultado_integrado']['pcmso_id']) ? $tipo_pcmso = null : $tipo_pcmso = Pcmso::find($dados['resultado_integrado']['pcmso_id'])->label;

                if (!is_null($empresaExame) && !is_null($tipo_pcmso)) {
                    $empresaExameId = $dados['resultado_integrado']['empresa_exame_id'];
                    $formulario_id = Formulario::whereTitulo('Exames')->first()->id;
                    $token = Sistema::uuid();
                    $exame_tipo_id = 1;
                    $empresa_id = auth()->user()->empresa_id;
                    $pcmso_id = $dados['resultado_integrado']['pcmso_id'];
                    $encaminhamento_data = $dadosResultadoIntegrado['encaminhado_exame_data'];

                    $temExameFuncionario = ExameFuncionario::whereFeedbackId($feedback->id)
                        ->whereEmpresaExameId($empresaExameId)
                        ->where('exame_tipo_id', $exame_tipo_id)
                        ->where('pcmso_id', $pcmso_id)
                        ->where('encaminhamento_data', '=', (new DataHora($encaminhamento_data))->dataInsert())->first();

                    if (is_null($temExameFuncionario)) {
                        $exameFuncionario = ExameFuncionario::create([
                            'feedback_id' => $feedback->id,
                            'empresa_id' => $empresa_id,
                            'empresa_exame_id' => $empresaExameId,
                            'formulario_id' => $formulario_id,
                            'respostas' => (object)[],
                            'token' => $token,
                            'pcmso' => true,
                            'pcmso_id' => $pcmso_id,
                            'exame_tipo_id' => $exame_tipo_id,
                            'encaminhamento_data' => $encaminhamento_data
                        ]);
                    }
                }

                ResultadoIntegrado::Notificacao($feedback, auth()->user(), $dadosResultadoIntegrado, $empresaExame, $tipo_pcmso);
                $admissaoCreate = $feedback->Admissao()->create($dadosAdmissao);

                //Cria Usuario na Empresa
                if ($dadosAdmissao['status'] == Admissao::STATUS_ADMISSAO_ADMITIDO) {
                    User::SincronizaEmpresaFuncionario($feedback->empresa_id, $feedback->curriculo_id);
                    User::find($feedback->curriculo_id)->update(['tipo' => User::FUNCIONARIO]);
                }

                $tableDadosAdmissao['admissao_id'] = $admissaoCreate['id'];
                DadosAdmissao::create($tableDadosAdmissao);

                if ($feedback->Admissao) {
                    if (isset($dados['admissao']['ferias_adquiridasDelete'])) {
                        foreach ($dados['admissao']['ferias_adquiridasDelete'] as $id) {
                            $feedback->Admissao->FeriasAdquiridas->find($id)->delete();
                        }
                    }

                    if (isset($dados['admissao']['ferias_adquiridas'])) {
                        foreach ($dados['admissao']['ferias_adquiridas'] as $linha) {
                            $feedback->Admissao->FeriasAdquiridasCriaOuAtualiza($linha);
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
                    $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback['id'])->first();

                    $datas['feedback_id'] = $feedback['id'];
                    $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);

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

                    $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback['id'])->first();

                    $datas['feedback_id'] = $feedback['id'];
                    $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);
                }
                if ($dadosAdmissao['tipo_admissao'] == 'INTERMITENTE') {
                    $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback['id'])->first();
                    isset($avaliacao) ? $avaliacao->delete() : null;
                }

            } else {

                $dadosAdmissao['editado_usuario_id'] = auth()->id();
                // 1 - Busca o Candidato
                $user = $user->first();
                $user->update($userObj);

                $dados['curriculo']['banco_conta']['user_id'] = $user->id;
                UsuarioConta::criarAtualizar($user->id, $dados['curriculo']['banco_conta']);

                $data_nascimento = new DataHora($dadosCurriculo['nascimento']);

                $candidato = $user->Curriculo;
                // 2 - Atualiza as informações na tabela curriculo
                $candidato->update([
                    'nome' => $dadosCurriculo['nome'],
                    'rg' => $dadosCurriculo['rg'],
                    'rg_data_emissao' => $dadosCurriculo['rg_data_emissao'],
                    'naturalidade' => $dadosCurriculo['naturalidade'],
                    'nascimento' => $data_nascimento->dataInsert(),
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
                    'formacao_curso' => $dadosCurriculo['formacao'] >= 8 ? $dadosCurriculo['formacao_curso'] : null,
                    'estado_civil' => $dadosCurriculo['estado_civil'],
                    'sexo' => $dadosCurriculo['sexo'],
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

//                if ($candidato->FeedBack) {
//                    $candidato->FeedBack->update($dadosFeedback);
//                    $feedback = $candidato->FeedBack;
//                } else {
//                    $dadosFeedback['curriculo_id'] = $candidato->id;
//                    $feedback = FeedbackCurriculo::create($dadosFeedback);
//                }

                $dadosFeedback['curriculo_id'] = $candidato->id;
                $feedback = FeedbackCurriculo::create($dadosFeedback);

                // Dependentes
                if (isset($dadosCurriculo['dependentesDelete'])) {
                    foreach ($dadosCurriculo['dependentesDelete'] as $id) {
                        $feedback->Curriculo->Dependentes->find($id)->delete();
                    }
                }

                if (isset($dadosCurriculo['dependentes'])) {
                    foreach ($dadosCurriculo['dependentes'] as $linha) {
                        if (isset($linha['nova'])) {
                            $feedback->Curriculo->Dependentes()->create($linha);
                        } else {
                            $feedback->Curriculo->Dependentes->find($linha['id'])->update($linha);
                        }
                    }
                }

                !is_null($feedback->parecerRh) ? $feedback->parecerRh->update($dadosParecerRh) : $feedback->parecerRh()->create($dadosParecerRh);
                !is_null($feedback->parecerRota) ? $feedback->parecerRota->update($dadosParecerRota) : $feedback->parecerRota()->create($dadosParecerRota);
                !is_null($feedback->parecerTecnica) ? $feedback->parecerTecnica->update($dadosParecerTecnica) : $feedback->parecerTecnica()->create($dadosParecerTecnica);
                !is_null($feedback->parecerTeste) ? $feedback->parecerTeste->update($dadosParecerTeste) : $feedback->parecerTeste()->create($dadosParecerTeste);
                !is_null($feedback->ResultadoIntegrado) ? $feedback->ResultadoIntegrado->update($dadosResultadoIntegrado) : $feedback->ResultadoIntegrado()->create($dadosResultadoIntegrado);

                is_null($dados['resultado_integrado']['empresa_exame_id']) ? $empresaExame = null : $empresaExame = EmpresaExame::find($dados['resultado_integrado']['empresa_exame_id']);
                is_null($dados['resultado_integrado']['pcmso_id']) ? $tipo_pcmso = null : $tipo_pcmso = Pcmso::find($dados['resultado_integrado']['pcmso_id'])->label;

                ResultadoIntegrado::Notificacao($feedback, auth()->user(), $dadosResultadoIntegrado, $empresaExame, $tipo_pcmso);

                //Logica de Vagas
                if ($feedback->vaga_projeto_id) {
                    if ($request->input('vaga_projeto_id') != $feedback->vaga_projeto_id) {
                        if ($request->filled('vaga_projeto_id')) {
                            $vagaProjeto = VagaProjeto::find($request->input('vaga_projeto_id'));
                            $vagaAnterior = VagaProjeto::find($feedback->vaga_projeto_id);
                            if ($vagaProjeto->tem_vaga) {
                                $vagaAnterior->decrement('qnt_preenchida');
                                $vagaAnterior->save();
                                $vagaAnterior->Projeto->decrement('preenchidas');

                                $vagaProjeto->increment('qnt_preenchida');
                                $vagaProjeto->save();
                                $vagaProjeto->Projeto->increment('preenchidas');

                                $feedback->update(['vaga_projeto_id' => $vagaProjeto->id]);
                            } else {
                                return response()->json([
                                    'msg' => 'Vaga não disponível para o projeto selecionado'
                                ], 400);
                            }
                        } else {
                            $vagaProjeto = VagaProjeto::find($feedback->vaga_projeto_id);
                            $vagaProjeto->decrement('qnt_preenchida');
                            $vagaProjeto->save();
                            $vagaProjeto->Projeto->decrement('preenchidas');
                            $feedback->update(['vaga_projeto_id' => null]);
                        }
                    }
                }

                //Continuacao da Logica de Vagas
                if ($request->filled('vaga_projeto_id')) {
                    if (!$feedback->vaga_projeto_id) {
                        $vagaProjeto = VagaProjeto::find($request->input('vaga_projeto_id'));
                        if ($vagaProjeto->tem_vaga) {
                            $vagaProjeto->increment('qnt_preenchida');
                            $vagaProjeto->save();
                            $vagaProjeto->Projeto->increment('preenchidas');
                            $feedback->update(['vaga_projeto_id' => $vagaProjeto->id]);
                        } else {
                            return response()->json([
                                'msg' => 'Vaga não disponível para o projeto selecionado'
                            ], 400);
                        }
                    }
                }

//                $feedback_id = $candidato->FeedBack ? $candidato->FeedBack->id : '';

                $admissaoCreate = $feedback->Admissao()->create($dadosAdmissao);
                //Cria Usuario na Empresa
                if ($dadosAdmissao['status'] == Admissao::STATUS_ADMISSAO_ADMITIDO) {
                    User::SincronizaEmpresaFuncionario($feedback->empresa_id, $feedback->curriculo_id);
                    User::find($feedback->curriculo_id)->update(['tipo' => User::FUNCIONARIO]);
                }

                $tableDadosAdmissao['admissao_id'] = $admissaoCreate['id'];
                DadosAdmissao::create($tableDadosAdmissao);

                $datas = [];

                if ($feedback->Admissao) {
                    if (isset($dados['admissao']['ferias_adquiridasDelete'])) {
                        foreach ($dados['admissao']['ferias_adquiridasDelete'] as $id) {
                            $feedback->Admissao->FeriasAdquiridas->find($id)->delete();
                        }
                    }

                    if (isset($dados['admissao']['ferias_adquiridas'])) {
                        foreach ($dados['admissao']['ferias_adquiridas'] as $linha) {
                            $feedback->Admissao->FeriasAdquiridasCriaOuAtualiza($linha);
                        }
                    }

                }

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

                    $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback->id)->first();

                    $datas['feedback_id'] = $feedback->id;
                    $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);

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

                    $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback->id)->first();

                    $datas['feedback_id'] = $feedback->id;
                    $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);
                }
                if ($dadosAdmissao['tipo_admissao'] == 'INTERMITENTE') {
                    $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback->id)->first();
                    isset($avaliacao) ? $avaliacao->delete() : null;
                }
            }


            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ADMISSAO AVULSA STORE: {$e->getFile()} , {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            \Log::debug($e->getTraceAsString());
            \Log::info("-------DADOS-------");
            Sistema::telegram(print_r($dados, true));
            \Log::info("-------FIM DE DADOS-------");
            Sistema::LogFormatado($dados);
            return response()->json($msg, 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente, Caso persista entre em contato com o suporte!'], 400);

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
        $this->authorize('admissao_processo');

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
            'Admissao.FeriasAdquiridas',
            'Admissao.UltimoAso',
            'UltimoAso',
            'Curriculo.Escolaridade',
            'Curriculo.Dependentes',
            'Curriculo.FotoTres',
            'Curriculo.Telefones',
            'Curriculo.Dependentes',
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
            'ResultadoIntegrado',
        );

        if (!is_null($feedback->BancoConta)) {
            $feedback->BancoConta->banco = $feedback->BancoConta ? $feedback->BancoConta->banco : 'Banco do Brasil';
            $feedback->BancoConta->agencia = $feedback->BancoConta ? $feedback->BancoConta->agencia : '';
            $feedback->BancoConta->conta = $feedback->BancoConta ? $feedback->BancoConta->conta : '';
            $feedback->BancoConta->pix = $feedback->BancoConta ? $feedback->BancoConta->pix : false;
            $feedback->BancoConta->tipochavepix = $feedback->BancoConta ? $feedback->BancoConta->tipochavepix : '';
            $feedback->BancoConta->chavepix = $feedback->BancoConta ? $feedback->BancoConta->chavepix : '';
        }

//        $feedback->vaga_projeto_id = is_null($feedback->vaga_projeto_id) ? null : $feedback->vaga_projeto_id;

//        if (!is_null($feedback->Projeto)) {
//            $feedback->projeto = $feedback->Projeto ?: '';
//        }

        $feedback->Curriculo->foto_tres_delete = [];
        $feedback->Curriculo->dependentesDelete = [];
        $feedback->Curriculo->telefonesDelete = [];

        if (!is_null($feedback->Admissao)) {
            $feedback->Admissao->ferias_adquiridasDelete = [];
            $feedback->Admissao->documento = $feedback->Admissao->documento ?: '';
            $feedback->Admissao->documento_portaria = $feedback->Admissao->documento_portaria ?: '';
            $feedback->Admissao->tipo_admissao = $feedback->Admissao->tipo_admissao ?: '';
            $feedback->Admissao->treinamento = $feedback->Admissao->treinamento ?: '';
            $feedback->Admissao->nr_trinta_tres = $feedback->Admissao->nr_trinta_tres ?: '';
            $feedback->Admissao->nr_trinta_cinco = $feedback->Admissao->nr_trinta_cinco ?: '';
            $feedback->Admissao->status_carteira_treinamento = $feedback->Admissao->status_carteira_treinamento ?: '';
            $feedback->Admissao->area_etiqueta_id = $feedback->Admissao->area_etiqueta_id == 0 ? null : $feedback->Admissao->area_etiqueta_id;
            $feedback->Admissao->documento = $feedback->Admissao->documento ?? "";
            $feedback->Admissao->documento_portaria = $feedback->Admissao->documento_portaria ?? "";
            $feedback->Admissao->tipo_admissao = $feedback->Admissao->tipo_admissao ?? "";
            $feedback->Admissao->tipo_treinamento = $feedback->Admissao->tipo_treinamento ?? "";
            $feedback->Admissao->treinamento = $feedback->Admissao->treinamento ?? "";
            $feedback->Admissao->nr_trinta_tres = $feedback->Admissao->nr_trinta_tres ?? "";
            $feedback->Admissao->nr_trinta_cinco = $feedback->Admissao->nr_trinta_cinco ?? "";
            $feedback->Admissao->trinta_dois_sessenta = $feedback->Admissao->trinta_dois_sessenta ?? "";
            $feedback->Admissao->foto_escaneada = $feedback->Admissao->foto_escaneada ?? "";
            $feedback->Admissao->status = $feedback->Admissao->status ?? "";
            $feedback->Admissao->data_admissao = $feedback->Admissao->data_admissao ?? "";
//            $feedback->Admissao->data_aso = $feedback->UltimoAso->data_realizacao ?? "";
            $feedback->Admissao->salario = $feedback->Admissao->salario ?? "0,00";
            $feedback->Admissao->prazo_experiencia = $feedback->Admissao->prazo_experiencia ?? "";
        }
        $feedback->parecerRh->indicado_por = $feedback->parecerRh->indicado_por ?: "";
        $feedback->parecerRh->calca = $feedback->parecerRh->calca ?: "";
        $feedback->parecerRh->bota = $feedback->parecerRh->bota ?: "";
        $feedback->parecerRh->camisa_protecao = $feedback->parecerRh->camisa_protecao ?: "";
        $feedback->parecerRh->camisa_meia = $feedback->parecerRh->camisa_meia ?: "";

        if (!is_null($feedback->parecerTecnica)) {
            $feedback->parecerTecnica->indicado_area = $feedback->parecerTecnica->indicado_area ?: "";
        }

        $feedback->autocomplete_label_vaga_modal = $feedback->VagaAberta->VagaSelecionada ? $feedback->VagaAberta->VagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';
        $feedback->autocomplete_label_vaga_modal_anterior = $feedback->VagaAberta->VagaSelecionada ? $feedback->VagaAberta->VagaSelecionada->nome . ' - ' . $feedback->VagaAberta->Municipio->nome . ' - ' . $feedback->VagaAberta->Municipio->uf : '';

        $lista_projetos = $feedback->VagaAberta->Projetos->filter(function ($item) {
            return $item->tem_vaga;
        });

        return response()->json(['feedback' => $feedback,
//            'listaProjetos' => $lista_projetos
        ], 200);
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
        $this->authorize('admissao_processo_update');
        $dados = $request->input();
        $dadosCurriculo = $dados['curriculo'];
        $feedback = $admissao;
        $dadosVagaAberta = VagasAbertas::find($dados['vagas_abertas_id']);
        $dados['admissao']['cargo'] = $dadosVagaAberta->Vaga->nome;
        $admissaoDados = $dados['admissao'];
        $admissaoDados['feedback_id'] = $feedback->id;

        $dados['curriculo']['email'] = $dados['curriculo']['email'] == "" ? Sistema::EMAILPADRAO : $dados['curriculo']['email'];

        $data_nascimento = new DataHora($dados['curriculo']['nascimento']);

        $dados['curriculo']['nascimento'] = $data_nascimento->dataInsert();

        if (in_array($dados['admissao']['status'], [Admissao::STATUS_ADMISSAO_ADMITIDO, Admissao::STATUS_ADMISSAO_PRONTOPARAADMISSAO])) {
            if (trim($dados['admissao']['data_admissao']) == 0) {
                return response()->json([
                    'msg' => 'Informe a data da Admissão'
                ], 400);
            }
        }

        $dadosValidados = \Validator::make($dados, [
            'curriculo.email' => 'required|email:rfc,dns',
            'curriculo.telefones' => 'required|array|min:1|tel_principal_mark',
            'admissao.status' => 'required|in:' . implode(',', Admissao::TODOS_STATUS_ADMISSAO),
            'admissao.status_carteira_treinamento' => 'sometimes|nullable|string|in:' . implode(',', Admissao::TODOS_STATUS_CARTEIRA_TREINAMETO),
            'admissao.segmento_treinamento_id' => 'sometimes|nullable|integer|exists:segmentos_treinamento,id',
        ]);
        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Salvar Admissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                //Logica de Vagas
                if ($feedback->vaga_projeto_id) {
                    if ($request->input('vaga_projeto_id') != $feedback->vaga_projeto_id) {
                        if ($request->filled('vaga_projeto_id')) {
                            $vagaProjeto = VagaProjeto::find($request->input('vaga_projeto_id'));
                            $vagaAnterior = VagaProjeto::find($feedback->vaga_projeto_id);
                            if ($vagaProjeto->tem_vaga) {
                                $vagaAnterior->decrement('qnt_preenchida');
                                $vagaAnterior->save();
                                $vagaAnterior->Projeto->decrement('preenchidas');

                                $vagaProjeto->increment('qnt_preenchida');
                                $vagaProjeto->save();
                                $vagaProjeto->Projeto->increment('preenchidas');

                                $feedback->update(['vaga_projeto_id' => $vagaProjeto->id]);
                            } else {
                                return response()->json([
                                    'msg' => 'Vaga não disponível para o projeto selecionado'
                                ], 400);
                            }
                        } else {
                            $vagaProjeto = VagaProjeto::find($feedback->vaga_projeto_id);
                            $vagaProjeto->decrement('qnt_preenchida');
                            $vagaProjeto->save();
                            $vagaProjeto->Projeto->decrement('preenchidas');
                            $feedback->update(['vaga_projeto_id' => null]);
                        }
                    }
                }

                //Continuacao da Logica de Vagas
                if ($request->filled('vaga_projeto_id')) {
                    if (!$feedback->vaga_projeto_id) {
                        $vagaProjeto = VagaProjeto::find($request->input('vaga_projeto_id'));
                        if ($vagaProjeto->tem_vaga) {
                            $vagaProjeto->increment('qnt_preenchida');
                            $vagaProjeto->save();
                            $vagaProjeto->Projeto->increment('preenchidas');
                            $feedback->update(['vaga_projeto_id' => $vagaProjeto->id]);
                        } else {
                            return response()->json([
                                'msg' => 'Vaga não disponível para o projeto selecionado'
                            ], 400);
                        }
                    }
                }

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
                    'nascimento' => $dados['curriculo']['nascimento'],
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
                    'estado_civil' => $dados['curriculo']['estado_civil'],
                    'sexo' => $dados['curriculo']['sexo'],
                    'formacao' => $dados['curriculo']['formacao'],
                    'formacao_curso' => $dados['curriculo']['formacao'] >= 8 ? $dados['curriculo']['formacao_curso'] : null,
                ]);

                if ($feedback->parecerRh) {
                    $feedback->parecerRh->update(
                        [
                            'indicacao' => $dados['parecer_rh']['indicacao'],
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

                $dados['resultado_integrado']['pcmso_id'] = $dados['resultado_integrado']['pcmso_id'] ?: null;

                $dados['resultado_integrado']['encaminhado_exame_data'] = $dados['resultado_integrado']['encaminhado_exame'] ? $dados['resultado_integrado']['encaminhado_exame_data'] : null;
                $dados['resultado_integrado']['encaminhado_treinamento_data'] = $dados['resultado_integrado']['encaminhado_treinamento'] ? $dados['resultado_integrado']['encaminhado_treinamento_data'] : null;

                $dadosResultadoIntegrado = $dados['resultado_integrado'];

                $feedback->ResultadoIntegrado ? $feedback->ResultadoIntegrado->update($dadosResultadoIntegrado) : $feedback->ResultadoIntegrado()->create($dadosResultadoIntegrado);

                if (!is_null($dados['resultado_integrado']['empresa_exame_id']) && !is_null($dados['resultado_integrado']['pcmso_id'])) {
                    $empresaExameId = $dados['resultado_integrado']['empresa_exame_id'];
                    $formulario_id = Formulario::whereTitulo('Exames')->first()->id;
                    $token = Sistema::uuid();
                    $exame_tipo_id = 1;
                    $empresa_id = auth()->user()->empresa_id;
                    $pcmso_id = $dados['resultado_integrado']['pcmso_id'];
                    $encaminhamento_data = $dadosResultadoIntegrado['encaminhado_exame_data'];

                    $temExameFuncionario = ExameFuncionario::whereFeedbackId($feedback->id)
                        ->whereEmpresaExameId($empresaExameId)
                        ->where('exame_tipo_id', $exame_tipo_id)
                        ->where('pcmso_id', $pcmso_id)
                        ->where('encaminhamento_data', '=', (new DataHora($encaminhamento_data))->dataInsert())->first();

                    if (is_null($temExameFuncionario)) {
                        $exameFuncionario = ExameFuncionario::create([
                            'feedback_id' => $feedback->id,
                            'empresa_id' => $empresa_id,
                            'empresa_exame_id' => $empresaExameId,
                            'formulario_id' => $formulario_id,
                            'respostas' => (object)[],
                            'token' => $token,
                            'pcmso' => true,
                            'pcmso_id' => $pcmso_id,
                            'exame_tipo_id' => $exame_tipo_id,
                            'encaminhamento_data' => $encaminhamento_data
                        ]);
                    }
                }

                $dadosAdmissao = $admissaoDados['dados_admissoes'];
                unset($admissaoDados['dados_admissoes']);

                if ($feedback->Admissao) {
                    if (isset($dados['admissao']['ferias_adquiridasDelete'])) {
                        foreach ($dados['admissao']['ferias_adquiridasDelete'] as $id) {
                            $feedback->Admissao->FeriasAdquiridas->find($id)->delete();
                        }
                    }

                    if (isset($dados['admissao']['ferias_adquiridas'])) {
                        foreach ($dados['admissao']['ferias_adquiridas'] as $linha) {
                            $feedback->Admissao->FeriasAdquiridasCriaOuAtualiza($linha);
                        }
                    }

                    $feedback->Admissao->update($admissaoDados);
                    //Cria Usuario na Empresa
                    if ($admissaoDados['status'] == Admissao::STATUS_ADMISSAO_ADMITIDO) {
                        User::SincronizaEmpresaFuncionario($feedback->empresa_id, $feedback->curriculo_id);
                        User::find($feedback->curriculo_id)->update(['tipo' => User::FUNCIONARIO]);
                    }
                    if (!isset($dadosAdmissao['id'])) {
                        $dadosAdmissao['admissao_id'] = $feedback->Admissao->id;
                        DadosAdmissao::create($dadosAdmissao);
                    } else {
                        $dadosAdmissaoUp = DadosAdmissao::find($dadosAdmissao['id']);
                        $dadosAdmissaoUp->update($dadosAdmissao);
                    }
                } else {
                    $admissao_id = $feedback->Admissao()->create($admissaoDados);

                    //Cria Usuario na Empresa
                    if ($admissaoDados['status'] == Admissao::STATUS_ADMISSAO_ADMITIDO) {
                        User::SincronizaEmpresaFuncionario($feedback->empresa_id, $feedback->curriculo_id);
                        User::find($feedback->curriculo_id)->update(['tipo' => User::FUNCIONARIO]);
                    }

                    $dadosAdmissao['admissao_id'] = $admissao_id['id'];
                    DadosAdmissao::create($dadosAdmissao);
                }

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

                    $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($admissaoDados['feedback_id'])->first();

                    $datas['feedback_id'] = $admissaoDados['feedback_id'];
                    $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);

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

                    $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($admissaoDados['feedback_id'])->first();

                    $datas['feedback_id'] = $admissaoDados['feedback_id'];
                    $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);
                }
                if ($admissaoDados['tipo_admissao'] == 'INTERMITENTE') {
                    $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($admissaoDados['feedback_id'])->first();
                    isset($avaliacao) ? $avaliacao->delete() : null;
                }

                // Excluir telefones marcados para exclusão
                if (isset($dadosCurriculo['telefonesDelete'])) {
                    foreach ($dadosCurriculo['telefonesDelete'] as $index) {
                        TelefoneCurriculo::destroy($index);
                    }
                }

                // Processar telefones
                if (isset($dadosCurriculo['telefones'])) {
                    $telefones = collect($dadosCurriculo['telefones']);
                    // Definir telefone principal se não existir
                    $telPrincipal = $telefones->where('principal', true)->first();
                    if (!$telPrincipal) {
                        $telefones = $telefones->map(function ($item, $key) use ($feedback) {
                            $item['id'] = $item['id'] == 0 ? null : $item['id'];
                            $item['principal'] = $key == 0;
                            return $item;
                        });
                    }

                    // Criar ou atualizar telefones
                    $telefones->each(function ($item) use ($feedback) {
                        $item['curriculo_id'] = $feedback->curriculo_id;
                        if (isset($item['nova'])) {
                            unset($item['id']);
                            $tel = TelefoneCurriculo::create($item);
                        } else {
                            $tel = TelefoneCurriculo::findOrFail($item['id']);
                            $tel->update($item);
                        }

                        if ($item['principal']) {
                            $feedback->update(['telefone_id' => $tel->id]);
                        }
                    });
                }


                if (isset($dadosCurriculo['dependentesDelete'])) {
                    foreach ($dadosCurriculo['dependentesDelete'] as $id) {
                        $feedback->Curriculo->Dependentes->find($id)->delete();
                    }
                }

                if (isset($dadosCurriculo['dependentes'])) {
                    foreach ($dadosCurriculo['dependentes'] as $linha) {
                        if (isset($linha['nova'])) {
                            $feedback->Curriculo->Dependentes()->create($linha);
                        } else {
                            $feedback->Curriculo->Dependentes->find($linha['id'])->update($linha);
                        }
                    }
                }

                is_null($dados['resultado_integrado']['empresa_exame_id']) ? $empresaExame = null : $empresaExame = EmpresaExame::find($dados['resultado_integrado']['empresa_exame_id']);
                is_null($dados['resultado_integrado']['pcmso_id']) ? $tipo_pcmso = null : $tipo_pcmso = Pcmso::find($dados['resultado_integrado']['pcmso_id'])->label;

                ResultadoIntegrado::Notificacao($feedback, auth()->user(), $dadosResultadoIntegrado, $empresaExame, $tipo_pcmso);


                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error ADMISSÃO COMUM:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                \Log::debug($e->getTraceAsString());
                \Log::info("-------DADOS-------");
                \Log::alert(print_r($dados, true));
                \Log::info("-------FIM DE DADOS-------");
                Sistema::LogFormatado($dados);

                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    public function cadastraMassa(Request $request)
    {
        $this->authorize('admissao_processo_update');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, []);
        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Salvar em Massa',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $tipo_admissao = [
                    'TEMPORARIO',
                    'DETERMINADO'
                ];

                foreach ($dados['selecionados'] as $feedback_id) {
                    $feedback = FeedbackCurriculo::find($feedback_id);
                    $info = ['selecionado' => 'sim'];

                    $feedback->update($info);
                    $dados = [
                        "tipo_admissao" => $dados['tipo_admissao'],
                        "prazo_experiencia" => $dados['prazo_experiencia'],
                        "data_encerramento" => $dados['data_encerramento'],
                        "documento_portaria" => $dados['documento_portaria'],
                        "status_carteira_treinamento" => $dados['status_carteira_treinamento'],
                        "segmento_treinamento_id" => $dados['segmento_treinamento_id'] ?? null,
                        "status" => $dados['status'],
                        "data_admissao" => $dados['data_admissao'],
                        "data_entrega_area" => $dados['data_entrega_area'],
                        "biometria" => $dados['biometria'],
                    ];

                    if ($feedback->Admissao) {

                        $feedback->Admissao->update($dados);
                    }
                    $feedback->Admissao()->create($dados);
                    $datas = [];

                    if ($dados['tipo_admissao'] == 'FIXO') {
                        $data = new DataHora($dados['data_admissao']);
                        switch ($dados['prazo_experiencia']) {
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
                        $dados['data_encerramento'] = null;
                        $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback_id)->first();

                        $datas['feedback_id'] = $feedback_id;
                        $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);
                    }
                    if (in_array($dados['tipo_admissao'], $tipo_admissao)) {
                        $data = new DataHora($dados['data_encerramento']);

                        $datas['prazo_dez_inicial'] = $data->subtrairDia(5);
                        $datas['prazo_cinco_inicial'] = $data->subtrairDia(5);
                        $datas['prazo_dia_inicial'] = $dados['data_encerramento'];
                        $datas['prazo_dez_final'] = null;
                        $datas['prazo_cinco_final'] = null;
                        $datas['prazo_dia_final'] = null;
                        $dadosAdmissao['prazo_experiencia'] = null;
                        $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback_id)->first();

                        $datas['feedback_id'] = $feedback_id;
                        $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);
                    }
                    if ($dados['tipo_admissao'] == 'INTERMITENTE') {
                        $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedback_id)->first();
                        isset($avaliacao) ? $avaliacao->delete() : null;
                    }
                }

                //Cria Usuario na Empresa
                if ($dados['status'] == Admissao::STATUS_ADMISSAO_ADMITIDO) {
                    User::SincronizaEmpresaFuncionario($feedback->empresa_id, $feedback->curriculo_id);
                    User::find($feedback->curriculo_id)->update(['tipo' => User::FUNCIONARIO]);
                }

                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error ADMISSÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                \Log::debug($e->getTraceAsString());
                \Log::info("-------DADOS-------");
                Sistema::telegram(print_r($dados, true));
                \Log::info("-------FIM DE DADOS-------");
                Sistema::LogFormatado($dados);
                return response()->json($msg, 400);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente, Caso persista entre em contato com o suporte!'], 400);
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
    public function filtro(Request $request)
    {

        $resultado = FeedbackCurriculo::select(['id', 'curriculo_id', 'vaga_id', 'telefone_id', 'vagas_abertas_id', 'vaga_projeto_id', 'empresa_id'])
            ->whereHas('ResultadoIntegrado')
            ->with([
                'BancoConta',
                'ResultadoIntegrado:id,feedback_id,documentos_entregue,documentos_entregue,encaminhado_exame,encaminhado_exame,encaminhado_treinamento,encaminhado_treinamento,responsavel_envio',
                'Curriculo:id,nome,estado_civil,naturalidade,nacionalidade,carteira_trabalho,cnh,cnh_vencimento,sexo,cpf,rg,rg_data_emissao,orgao_expeditor,logradouro,end_numero,complemento,bairro,municipio,uf,cep,filiacao_pai,filiacao_mae,pcd,nascimento,email,disponibilidade_sabado,disponibilidade_domingo',
                'Curriculo.FotoTres:id',
                'Curriculo.TelPrincipal',
                'parecerRh:id,feedback_id,destro,cnh_tipo,calca,bota,camisa_meia,camisa_protecao,ex_funcionario,turnos_seis_por_dois,indicado_por',
                'parecerTecnica:id,feedback_id,indicado_area,experiencia_cargas_rigger,opera_plat_movel,opera_plat_ponte',
                'parecerRota:id,feedback_id,tem_rota,qual,bairro_rota,ponto_referencia_rota,pega_onibus,pega_onibus_qual_ponto,bairro_residencia,ponto_referencia_residencia',
                'parecerTeste:id,feedback_id,qual_teste,nota_teste',
                'parecerTecnica:id,feedback_id,experiencia_cargas_rigger,opera_plat_movel,opera_plat_ponte',
                'VagaAberta:id,empresa_id,vaga_id,titulo,municipio_id,ativo',
                'VagaAberta.VagaSelecionada:id,nome,empresa_id,ativo',
                'VagaAberta.Municipio:id,nome,uf',
                'Empresa:id,razao_social,cnpj,nome,cpf,area_id',
                'Empresa.Area',
                'VagaAberta.Projetos.Projeto',
                'Admissao',
                'Admissao.UltimoAso:id,feedback_id,data_realizacao,data_vencimento',
                'Admissao.Demissao:id,feedback_id,data_desmobilizacao',
                'UltimoAso:id,feedback_id,data_realizacao,data_vencimento'
            ])->filtrarPorCnpjECentroCusto($request);

        $filtroPeriodo = $request->filtroPeriodo === true || $request->filtroPeriodo === 'true' || $request->filtroPeriodo === '1';

        if ($filtroPeriodo) {
            if ($request->filled('dataInicio') && $request->filled('dataFim')) {
                $dataInicio = new DataHora($request->dataInicio . ' 00:00:00');
                $dataFim = new DataHora($request->dataFim . ' 23:59:59');
            } else {
                $periodo = explode(' até ', $request->periodo ?? '');
                if (count($periodo) === 2) {
                    $dataInicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
                    $dataFim = new DataHora(trim($periodo[1]) . ' 23:59:59');
                } else {
                    $dataInicio = null;
                    $dataFim = null;
                }
            }
            if ($dataInicio && $dataFim) {
                $resultado->whereHas('parecerRh', function ($q) use ($dataInicio, $dataFim) {
                    $q->where('created_at', '>=', $dataInicio->dataHoraInsert())->where('created_at', '<=', $dataFim->dataHoraInsert());
                });
            }
        }

        if ($request->campoDemitido) {
            $resultado->Demitidos();
        } else {
            $resultado->Admitidos();
        }

        $filtroAso = $request->filtroAso === true || $request->filtroAso === 'true' || $request->filtroAso === '1';

        if ($filtroAso) {
            if ($request->filled('dataInicioAso') && $request->filled('dataFimAso')) {
                $dataInicioAso = new DataHora($request->dataInicioAso . ' 00:00:00');
                $dataFimAso = new DataHora($request->dataFimAso . ' 23:59:59');
            } else {
                $periodo = explode(' até ', $request->campoAso ?? '');
                $dataInicioAso = count($periodo) === 2 ? new DataHora(trim($periodo[0]) . ' 00:00:00') : null;
                $dataFimAso = count($periodo) === 2 ? new DataHora(trim($periodo[1]) . ' 23:59:59') : null;
            }
            if ($dataInicioAso && $dataFimAso) {
                $resultado->whereHas('UltimoAso', function ($q) use ($dataInicioAso, $dataFimAso) {
                    $q->where('data_realizacao', '>=', $dataInicioAso->dataHoraInsert())->where('data_realizacao', '<=', $dataFimAso->dataHoraInsert());
                });
            }
        }

        $filtroDataAdmissao = $request->filtroDataAdmissao === true || $request->filtroDataAdmissao === 'true' || $request->filtroDataAdmissao === '1';

        if ($filtroDataAdmissao) {
            if ($request->filled('dataInicioAdmissao') && $request->filled('dataFimAdmissao')) {
                $dataInicioAdm = new DataHora($request->dataInicioAdmissao . ' 00:00:00');
                $dataFimAdm = new DataHora($request->dataFimAdmissao . ' 23:59:59');
            } else {
                $periodo = explode(' até ', $request->campoAdmisaoData ?? '');
                $dataInicioAdm = count($periodo) === 2 ? new DataHora(trim($periodo[0]) . ' 00:00:00') : null;
                $dataFimAdm = count($periodo) === 2 ? new DataHora(trim($periodo[1]) . ' 23:59:59') : null;
            }
            if ($dataInicioAdm && $dataFimAdm) {
                $resultado->whereHas('Admissao', function ($q) use ($dataInicioAdm, $dataFimAdm) {
                    $q->where('data_admissao', '>=', $dataInicioAdm->dataHoraInsert())->where('data_admissao', '<=', $dataFimAdm->dataHoraInsert());
                });
            }
        }

        if ($request->filled('campoCliente')) {
            $resultado->whereClienteId($request->campoCliente);
        }

        if ($request->filled('campoBusca')) {
            $busca = $request->campoBusca;
            $resultado->where(function ($q) use ($busca) {
                $q->whereHas('Curriculo', function ($query) use ($busca) {
                    $query->where('nome', 'like', '%' . $busca . '%')
                        ->orWhere('cpf', 'like', '%' . $busca . '%')
                        ->orWhere('id', $busca);
                });
                if (is_numeric(trim($busca))) {
                    $q->orWhere('id', (int) $busca);
                }
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

        if ($request->filled('campoStatusAdmissao')) {
            if ($request->campoStatusAdmissao == 'EM PROCESSO') {
                $resultado->whereDoesntHave('Admissao');
            } else {
                $resultado->whereHas('Admissao', function ($query) use ($request) {
                    $query->whereStatus($request->campoStatusAdmissao);
                });
            }
        }

        if ($request->filled('campoTipoAdmissao')) {
            $resultado->whereHas('Admissao', function ($query) use ($request) {
                $query->whereTipoAdmissao($request->campoTipoAdmissao);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('VagaAberta.Municipio', function ($q) use ($request) {
                $q->whereUf($request->campoUf);
            });
        }

//        if ($request->filled('campoCnpj')) {
//            $centros_custos = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
//            if (!$request->filled('campoCentroCusto')) {
//                $resultado->whereHas('Admissao', function ($query) use ($request, $centros_custos) {
//                    $cc = $centros_custos['centros_custos'][$request->campoCnpj];
//                    if ($cc[0]['matriz']) {
//                        $query->where(function ($query) use ($cc) {
//                            $query->whereIn('centro_custo_id', $cc->pluck('id')
//                                ->toArray())->orWhere('centro_custo_id', null);
//                        })->where('filial', false);
//                    } else {
//                        $query->where(function ($query) use ($cc) {
//                            $query->whereIn('centro_custo_filial_id', $cc->pluck('filial_id')
//                                ->toArray())->orWhere('centro_custo_filial_id', null);
//                        })->where('filial', true);
//                    }
//                });
//            } else {
//                $resultado->whereHas('Admissao', function ($query) use ($request, $centros_custos) {
//                    $cc = $centros_custos['centros_custos'][$request->campoCnpj];
//                    if ($cc[0]['matriz']) {
//                        $campoCentroCusto = $request->campoCentroCusto != '--naoinformado--' ?: null;
//                        $query->where('centro_custo_id', $campoCentroCusto)
//                            ->where('filial', false);
//                    } else {
//                        $campoCentroCusto = $request->campoCentroCusto != '--naoinformado--' ?: null;
//                        $query->where('centro_custo_filial_id', $campoCentroCusto)
//                            ->where('filial', true);
//                    }
//                });
//            }
//        }
        if ($request->filled('campoCnpj')) {
            $centros_custos = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
            if (!$request->filled('campoCentroCusto')) {
                $resultado->whereHas('Admissao', function ($query) use ($request, $centros_custos) {
                    $cc = $centros_custos['centros_custos'][$request->campoCnpj];
                    if ($cc[0]['matriz']) {
                        $query->where(function ($query) use ($cc) {
                            $query->whereIn('centro_custo_id', $cc->pluck('id')
                                ->toArray())->orWhere('centro_custo_id', null);
                        })->where('filial', false);
                    } else {
                        $query->where(function ($query) use ($cc) {
                            $query->whereIn('centro_custo_filial_id', $cc->pluck('filial_id')
                                ->toArray())->orWhere('centro_custo_filial_id', null);
                        })->where('filial', true);
                    }
                });
            } else {
                $resultado->whereHas('Admissao', function ($query) use ($request, $centros_custos) {
                    $cc = $centros_custos['centros_custos'][$request->campoCnpj];
                    if ($cc[0]['matriz']) {
                        $campoCentroCusto = $request->campoCentroCusto != '--naoinformado--' ? $request->campoCentroCusto : null;
                        $query->where('centro_custo_id', $campoCentroCusto)
                            ->where('filial', false);
                    } else {
                        $campoCentroCusto = $request->campoCentroCusto != '--naoinformado--' ? $request->campoCentroCusto : null;
                        $query->where('centro_custo_filial_id', $campoCentroCusto)
                            ->where('filial', true);
                    }
                });
            }
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
        $pg = $this->filtro($request)->paginate($request->pages ?: 20);
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);

        $itens = collect($pg->items())->transform(function ($item) use ($cc) {
            if ($item->admissao) {
                $cc_colaborador = collect($cc['centros_custos'])->collapse()->where('id', $item->admissao->centro_custo_id)->first();
                $item->admissao->emp_cnpj = null;
                $item->admissao->emp_nome_fantasia = null;
                $item->admissao->emp_centro_custo = null;
                $item->admissao->emp_tipo = null;

                if ($cc_colaborador) {
                    $item->admissao->emp_cnpj = $cc_colaborador['cnpj_format'];
                    $item->admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'];
                    $item->admissao->emp_centro_custo = $cc_colaborador['label'];
                    $item->admissao->emp_tipo = $cc_colaborador['matriz'] ? 'Matriz' : 'Filial';
                }
            }
            return $item;
        });

        $cliente = \App\Models\Cliente::find(auth()->user()->empresa_id);
        $segmentosTreinamento = $cliente && $cliente->SegmentosTreinamento()->where('segmentos_treinamento.ativo', true)->count() > 0
            ? $cliente->SegmentosTreinamento()->where('segmentos_treinamento.ativo', true)->orderBy('nome')->get(['segmentos_treinamento.id', 'nome', 'slug'])
            : \App\Models\SegmentoTreinamento::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'slug']);
        $dados = [
            'itens' => $itens,
            'admissao_processo_dados_editar' => auth()->user()->can('privilegio_admissao_processo_dados_editar'),
            'status_admissao' => array_merge(['EM PROCESSO'], Admissao::TODOS_STATUS_ADMISSAO),
            'tipos_admissao' => Admissao::TODOS_TIPOS_ADMISSAO,
            'status_carteira_treinamento' => Admissao::TODOS_STATUS_CARTEIRA_TREINAMETO,
            'segmentos_treinamento' => $segmentosTreinamento,
            'lista_sexos' => Curriculo::TIPOS_SEXOS,
            'lista_estados_civis' => Curriculo::ESTADOS_CIVIS,
            'permissoes' => [
                'filtrar_demitido' => auth()->user()->can('admissao_historico_filtrar_demitido'),
                'privilegio_processo_demitir' => auth()->user()->can('privilegio_processo_demitir'),
            ],
            'cc' => $cc
        ];

        return response()->json([
            'atual' => $pg->currentPage(),
            'ultima' => $pg->lastPage(),
            'total' => $pg->total(),
            'dados' => $dados
        ]);
    }

    /**
     * Método para obter colunas com cache indefinido para uma empresa e usuário específicos.
     */
    public function getColunasTabela()
    {
        $empresa_id = auth()->user()->empresa_id;
        $usuario_id = auth()->id();

        // Gera o nome do cache com base na empresa e usuário
        $cacheKey = "tabelas_filtro_processo_{$empresa_id}_{$usuario_id}";

        // Usar tags para armazenar o cache (relacionando ao processo)
        $colunasTabela = Cache::tags(['tabelas_filtro_processo'])->get($cacheKey);

        if (!$colunasTabela) {
            // Valor padrão inicial (ou você pode pegar do banco de dados)
            $colunasTabela = [
                ['label' => 'PCD', 'checked' => false, 'id' => 'pcd'],
                ['label' => 'CPF', 'checked' => false, 'id' => 'tbl_cpf'],
                ['label' => 'ENC. DOCUMENTO', 'checked' => true, 'id' => 'enc_documento'],
                ['label' => 'ENC. EXAME', 'checked' => true, 'id' => 'enc_exame'],
                ['label' => 'ENC. TREINAMENTO', 'checked' => true, 'id' => 'enc_treinamento'],
                ['label' => 'RESP. ENCAMINHAMENTO', 'checked' => true, 'id' => 'resp_encaminhamento'],
                ['label' => 'CRACHÁ', 'checked' => true, 'id' => 'cracha'],
                ['label' => 'FOTO 3x4', 'checked' => true, 'id' => 'foto_3x4'],
                ['label' => 'DATA ADMISSÃO', 'checked' => true, 'id' => 'tbl_dt_admissao'],
                ['label' => 'CONTATO', 'checked' => true, 'id' => 'tbl_contato'],
            ];

            // Armazena o valor no cache indefinidamente usando a tag
            Cache::tags(['tabelas_filtro_processo'])->forever($cacheKey, $colunasTabela);
        }

        return response()->json($colunasTabela);
    }

    /**
     * Método para atualizar as colunas e cache
     */
    public function atualizarColunasTabela(Request $request)
    {
        $empresa_id = auth()->user()->empresa_id;
        $usuario_id = auth()->id();

        // Receber os dados das colunas que foram enviadas via request
        $colunasTabela = $request->input('colunasTabela', []);

        // Validação básica para garantir que o formato do array está correto
        if (!is_array($colunasTabela)) {
            return response()->json(['error' => 'Formato de dados inválido'], 400);
        }

        // Gera o nome do cache com base na empresa e usuário
        $cacheKey = "tabelas_filtro_processo_{$empresa_id}_{$usuario_id}";

        // Atualiza o cache com os novos valores indefinidamente usando a tag
        Cache::tags(['tabelas_filtro_processo'])->forever($cacheKey, $colunasTabela);

        return response()->json(['success' => 'Colunas atualizadas e cache atualizado']);
    }

    public function getTiposDependentes()
    {
        return UsuarioDependente::TIPOS_DEPENDENTES;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function listSelects()
    {
        $segmentos = \App\Models\Cliente::find(auth()->user()->empresa_id)
            ? \App\Models\Cliente::find(auth()->user()->empresa_id)->SegmentosTreinamento()->where('segmentos_treinamento.ativo', true)->orderBy('nome')->get(['segmentos_treinamento.id', 'nome', 'slug'])
            : \App\Models\SegmentoTreinamento::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'slug']);
        if ($segmentos->isEmpty()) {
            $segmentos = \App\Models\SegmentoTreinamento::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'slug']);
        }
        return response()->json([
            'tipos_admissao' => Admissao::TODOS_TIPOS_ADMISSAO,
            'status_admissao' => Admissao::TODOS_STATUS_ADMISSAO,
            'status_carteira_treinamento' => Admissao::TODOS_STATUS_CARTEIRA_TREINAMETO,
            'segmentos_treinamento' => $segmentos,
            'todos_prazos' => Admissao::TODOS_PRAZOS,
            'todos_status_treinamentos' => Admissao::TODOS_STATUS_TREINAMENTOS,
            'todos_status_documentos' => Admissao::TODOS_STATUS_DOCUMENTOS,
            'todos_status_documentos_portaria' => Admissao::TODOS_STATUS_DOCUMENTOS_PORTARIA,
        ]);
    }

// Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_FOTOCURRICULO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        if ($request->query('thumb')) {
            $extensaoArquivo = substr($arquivo, strrpos($arquivo, '.') + 1);
            $nomeArquivo = substr($arquivo, 0, strrpos($arquivo, '.'));
            $arquivo = $nomeArquivo . '_p.' . $extensaoArquivo;
        }

        return Arquivo::anexoShow(Arquivo::DISCO_FOTOCURRICULO, $arquivo);
    }

    /**
     * Retorna anexo foto currículo 3x4 em base64 (JSON). Usa o mesmo cache de anexoShow.
     */
    public function anexoShowBase64(Request $request, $arquivo)
    {
        if ($request->query('thumb')) {
            $extensaoArquivo = substr($arquivo, strrpos($arquivo, '.') + 1);
            $nomeArquivo = substr($arquivo, 0, strrpos($arquivo, '.'));
            $arquivo = $nomeArquivo . '_p.' . $extensaoArquivo;
        }
        $data = Arquivo::getFotocurriculoAnexoContentAndMime($arquivo);
        if ($data === null) {
            abort(404);
        }
        return response()->json([
            'base64' => base64_encode($data['content']),
            'mime' => $data['mime'],
        ]);
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

    public function export(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $requestData = $request->all();

            // Criar uma chave única baseada no usuário e parâmetros da request
            $cacheKey = 'export_admissoes_' . $userId . '_' . md5(json_encode($requestData));

            // Verificar se já existe um export em andamento
            if (Cache::get($cacheKey)) {
                $cacheData = Cache::get($cacheKey);

                // Verifica diferentes status
                $status = $cacheData['status'] ?? 'processing';
                $attempts = $cacheData['attempt'] ?? 1;
                $maxTries = $cacheData['max_tries'] ?? 3;

                $message = match ($status) {
                    'processing' => "Exportação em andamento (tentativa {$attempts}/{$maxTries}). Aguarde a conclusão.",
                    'retrying' => "Exportação tentando novamente (tentativa {$attempts}/{$maxTries}). Aguarde.",
                    'completed' => "Exportação já foi concluído. Verifique suas notificações.",
                    'failed' => "Última Exportação falhou após {$maxTries} tentativas. Você pode tentar novamente.",
                    default => "Já existe uma Exportação em andamento. Aguarde a conclusão."
                };

                return response()->json([
                    'msg' => $message,
                    'status' => $status,
                    'initiated_at' => $cacheData['initiated_at'] ?? null,
                    'attempts' => $attempts,
                    'max_tries' => $maxTries,
                    'last_error' => $cacheData['last_error'] ?? null
                ], 200); // 409 Conflict apenas se ainda processando
            }
            
            $nameArquivo = "admissoes-exportado" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
            $expiresAt = now()->addMinutes(15);

            // Armazenar no cache com tempo de expiração definido
            Cache::put($cacheKey, [
                'filename' => $nameArquivo,
                'initiated_at' => now(),
                'expires_at' => $expiresAt, // Para controle de TTL
                'user_id' => $userId,
                'status' => 'queued', // Status inicial
                'attempt' => 0,
                'max_tries' => 3,
                'progress' => 0
            ], $expiresAt);

            // Dispatch do job
            JobExportaAdmissoes::dispatch(
                $userId,
                $requestData,
                $nameArquivo,
                $cacheKey
            );

            return response()->json([
                'msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.',
                'export_id' => $cacheKey,
                'estimated_time' => '5-15 minutos'
            ]);

        } catch (\Exception $e) {
            \Log::error("Erro no controller de export admissões: " . $e->getMessage() . " " . $e->getFile() . " on line " . $e->getLine());

            // Limpar cache em caso de erro
            if (isset($cacheKey)) {
                Cache::forget($cacheKey);
            }

            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

//PDF
    public function getFichaPdf($fc_token)
    {
        $feedback = FeedbackCurriculo::select([
            'id',
            'curriculo_id',
            'vagas_abertas_id',
            'empresa_id'
        ])->find(\Crypt::decrypt($fc_token))
            ->load(
                'ResultadoIntegrado:id,feedback_id',
                'Admissao',
                'UltimoAso',
            );

        $dados = [
            'dados_empresa' => Sistema::getEmpresaFilialMatriz($feedback->Admissao->centro_custo_filial_id, $feedback['empresa_id']),
            'dados_colaborador' => $feedback,
            'solicitante' => User::select('nome')->find(auth()->id())->nome
        ];

        $pdf = PDF::loadView('pdf.admissao.ficha', compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream("ficha_admissao_" . ($dados['dados_colaborador']->Curriculo->nome) . '_' . (new DataHora())->nomeUnico() . ".pdf");
    }

//Excel

    public function buscaCPF(Request $request)
    {
        $cpf = Sistema::transformCpfCnpj($request->cpf);
        $admissao = Admissao::whereHas('Feedback.Curriculo', function ($q) use ($cpf) {
            $q->whereCpf($cpf);
        });

        $demissao = Admissao::whereHas('Demissao')->whereHas('Feedback.Curriculo', function ($q) use ($cpf) {
            $q->whereCpf($cpf);
        });

        // Se o cara ja possui cadastro na Admissão
        if ($admissao->count() > 0 && $demissao->count() == 0) {
            return response()->json([
                'msg' => "Candidato {$admissao->first()->Feedback->Curriculo->id} - {$admissao->first()->Feedback->Curriculo->nome} ja possui cadastro de admissão desde " . DataHora::dataFormatada($admissao->first()->created_at),
            ], 400);
        } else {
            //270.105.033-20
            //cpf virgem = 018.791.043-00
            //cpf no recrutamento ainda = 010.368.413-16

            $curriculo = Curriculo::whereCpf($cpf);

            if ($curriculo->count() > 0) {
                $curriculo = $curriculo->first()->load('Dependentes', 'FotoTres');
                $curriculo->dependentesDelete = [];
                $curriculo->foto_tres_delete = [];

                $curriculo->pcd = $curriculo->pcd ?: false;

                $curriculo->autocomplete_label_municipio_modal = $curriculo->Cidade ? $curriculo->Cidade->nome . ' - ' . $curriculo->Cidade->uf : '';
                $curriculo->autocomplete_label_municipio_modal_anterior = $curriculo->Cidade ? $curriculo->Cidade->nome . ' - ' . $curriculo->Cidade->uf : '';

                $feedback = new \stdClass();
                $feedback->vaga_id = '';
                $feedback->cliente_id = '';
                $feedback->interesse = true;
                $feedback->autocomplete_label_vaga_modal = '';
                $feedback->autocomplete_label_vaga_modal_anterior = '';
                $feedback->autocomplete_label_cliente_modal = '';
                $feedback->autocomplete_label_cliente_modal_anterior = '';

                $parecerRH = new \stdClass();
                $parecerRH->ex_funcionario = false;
                $parecerRH->calca = '';
                $parecerRH->bota = '';
                $parecerRH->camisa_protecao = '';
                $parecerRH->camisa_meia = '';
                $parecerRH->turnos_seis_por_dois = '';
                $parecerRH->indicacao = '';
                $parecerRH->indicado_por = '';
                $parecerRH->ex_funcionario = $admissao->count() > 0;

                $parecerTecnica = new \stdClass();
                $parecerTecnica->experiencia_cargas_rigger = 'NÃO SE APLICA';
                $parecerTecnica->opera_plat_movel = 'NÃO SE APLICA';
                $parecerTecnica->opera_plat_ponte = 'NÃO SE APLICA';
                $parecerTecnica->indicado_area = '';

                $parecerRota = new \stdClass();
                $parecerRota->bairro_rota = '';
                $parecerRota->ponto_referencia_rota = '';
                $parecerRota->ponto_referencia_residencia = '';

                $parecerTeste = new \stdClass();
                $parecerTeste->qual_teste = '';
                $parecerTeste->parecer_final_teste = '';

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
                $resultadoIntegrado->obs = $demissao->count() > 0 ? 'RECONTRATAÇÃO' : 'ADMISSÃO AVULSA';

                $admissao = $demissao->with('DadosAdmissoes')->first();

                if ($admissao && $admissao->DadosAdmissoes) {
                    $dados_admissao = new \stdClass();
                    $dados_admissao->pis = $admissao->pis;
                    $dados_admissao->dados_admissoes = $admissao->DadosAdmissoes;
                } else {
                    $dados_admissao = new \stdClass();
                    $dados_admissao->pis = '';
                    $dados_admissao->dados_admissoes = (object)[
                        'ctps_numero' => '',
                        'ctps_serie' => '',
                        'ctps_uf' => '',
                        'ctps_data_emissao' => '',
                        'titulo_eleitor_numero' => '',
                        'titulo_eleitor_sessao' => '',
                        'titulo_eleitor_zona' => '',
                        'cert_reservista_num' => '',
                        'cert_reservista_categoria' => '',
                    ];
                }

                if ($curriculo->FeedBack && $curriculo->FeedBack->BancoConta) {
                    $feedback->banco_conta = $curriculo->FeedBack->BancoConta;
                }

                //Pode voltar
                $podeVoltar = true;

                if ($curriculo->FeedBack) {
                    $entrevistaDesligamento = EntrevistaDesligamento::select(['id', 'feedback_id', 'pode_voltar'])->where('feedback_id', $curriculo->FeedBack->id)->first();
                    if ($demissao->count() > 0 && $entrevistaDesligamento) {
                        $podeVoltar = !$entrevistaDesligamento->pode_voltar; //Se for false, não pode voltar
                    }
                }

                return response()->json(
                    [
                        'achou' => true,
                        'admissao' => $dados_admissao,
                        'ex_funcionario' => $demissao->count() > 0,
                        'pos_admissao_verificar' => $podeVoltar,
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
                return response()->json(['achou' => false, 'ex_funcionario' => false], 200);
            }
        }
    }

    public function getDadosDemitidoViaPrivilegio(Request $request)
    {

    }

    public function demitirViaPrivilegio(Request $request)
    {
        $dados = $request->input();

        try {
            DB::beginTransaction();
            Admissao::where('id', $dados['admissao_id'])->update([
                'status' => Admissao::STATUS_DEMITIDO
            ]);

            Demissao::create($dados);

            User::where('id', $dados['curriculo_id'])->update([
                'ativo' => false
            ]);

            DB::commit();
            return response()->json(['msg' => 'Colaborador demitido com sucesso!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("| Erro ao demitir colaborador: " . $e->getFile() . " - " . $e->getMessage() . " - " . $e->getCode());
            return response()->json(['msg' => 'Houve um erro por favor tente novamente, Caso persista entre em contato com o suporte!'], 400);
        }

    }


    /**
     * Exibe a tela de importação de admissões (upload de planilha).
     */
    public function import(Request $request)
    {
        return view('g.admissao.import.index');
    }

    /**
     * Recebe o arquivo de importação, armazena e enfileira o job de processamento.
     * Resposta imediata: importação enfileirada; o usuário será notificado ao concluir.
     */
    public function importUpload(Request $request): JsonResponse
    {
        $request->validate([
            'planilha' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
            'empresa_id' => ['required', 'integer', 'min:1'],
        ]);

        $empresaId = (int) $request->input('empresa_id');
        $user = auth()->user();
        if ($user && $user->empresa_id !== null && (int) $user->empresa_id !== $empresaId) {
            throw ValidationException::withMessages(['empresa_id' => ['Empresa não autorizada.']]);
        }

        $file = $request->file('planilha');
        $uuid = Str::uuid()->toString();
        $dir = 'importacao_admissoes/' . $empresaId;
        $path = $file->storeAs($dir, $uuid . '.xlsx', 'local');
        if ($path === false) {
            return response()->json(['msg' => 'Erro ao salvar o arquivo.', 'status' => 'error'], 500);
        }

        $caminhoS3 = $dir . '/' . $uuid . '.xlsx';
        try {
            $conteudo = file_get_contents(storage_path('app/' . $path));
            Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($caminhoS3, $conteudo);
        } catch (\Throwable $e) {
            \Log::warning('Importação admissões: falha ao enviar planilha para S3 no upload', [
                'path' => $path,
                'caminho_s3' => $caminhoS3,
                'erro' => $e->getMessage(),
            ]);
            return response()->json(['msg' => 'Erro ao enviar arquivo para o storage.', 'status' => 'error'], 500);
        }

        ImportacaoAdmissaoJob::dispatch(
            $path,
            $empresaId,
            $user ? (int) $user->id : null,
            100,
            $uuid,
            $caminhoS3
        );

        return response()->json([
            'msg' => 'Importação enfileirada. Você será notificado quando o processamento terminar.',
            'status' => 'success',
            'uuid' => $uuid,
        ], 202);
        /*
        $dadosValidados = \Validator::make($dados, [
            '*.curriculo.cpf' => ['required', 'min:14',
                'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                new CpfValidoEmpresaRules($empresa_id),
                new VerificaCpfEmpresaRules($empresa_id, true)
            ],
            '*.curriculo.nome' => 'required|max:255',
            '*.curriculo.email' => 'email:rfc,dns',
            '*.curriculo.nascimento' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.curriculo.rg' => 'nullable|max:200',
            '*.curriculo.rg_data_emissao' => 'nullable|max:10|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.curriculo.filiacao_pai' => 'max:255',
            '*.curriculo.filiacao_mae' => 'required|max:255',
            '*.curriculo.pcd' => 'required|boolean',
            '*.curriculo.cid' => 'required_if:*.curriculo.pcd,true',
            '*.curriculo.vaga_pretendida' => ['required', new VagaAbertaEmpresaRules($empresa_id)],
            '*.curriculo.endereco.cep' => 'required|min:9',
            '*.curriculo.endereco.logradouro' => 'required|max:255',
            '*.curriculo.endereco.numero' => 'nullable|max:10',
            '*.curriculo.endereco.complemento' => 'nullable|max:255',
            '*.curriculo.endereco.bairro' => 'required|max:255',
            '*.curriculo.endereco.municipio' => 'required|max:255',
            '*.curriculo.endereco.uf' => 'required|max:2|regex:/^[A-Z]{2}$/',
            '*.curriculo.telefone.whatsapp' => 'required|in:' . implode(",", TelefoneCurriculo::TIPOS),
            '*.curriculo.telefone.numero' => 'required|max:16',
            '*.admissao.area_etiqueta_id' => ['required', new AreaEmpresaRules($empresa_id)],
            '*.admissao.data_entrega_area' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.admissao.salario' => 'max:100',
            '*.admissao.pis' => 'nullable|max:200',
            '*.admissao.ctps_numero' => 'nullable|max:200',
            '*.admissao.ctps_serie' => 'nullable|max:200',
            '*.admissao.ctps_data_emissao' => 'nullable|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.admissao.titulo_eleitor_numero' => 'nullable|max:200',
            '*.admissao.titulo_eleitor_sessao' => 'nullable|max:200',
            '*.admissao.titulo_eleitor_zona' => 'nullable|max:200',
            '*.admissao.data_aso' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.admissao.data_admissao' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.admissao.tipo_admissao' => "required|in:" . implode(",", Admissao::TODOS_TIPOS_ADMISSAO),
            '*.admissao.admissao_encerramento' => [
                function ($attribute, $value, $fail) use ($dados) {
                    $i = (int)explode('.', $attribute)[0];

                    if (in_array($dados[$i]['admissao']['tipo_admissao'], [Admissao::TIPO_ADMISSAO_INTERMITENTE, Admissao::TIPO_ADMISSAO_DETERMINADO, Admissao::TIPO_ADMISSAO_TEMPORARIO])
                        && is_null($value)
                        && preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $value) == 0
                    ) {
                        $fail("O {$attribute} deve ser preenchido com formato da data dd/mm/aaaa");
                    }
                }],
            '*.admissao.prazo_experiencia' => [function ($attribute, $value, $fail) use ($dados) {
                $i = (int)explode('.', $attribute)[0];
                if ($dados[$i]['admissao']['tipo_admissao'] == Admissao::TIPO_ADMISSAO_FIXO && !in_array($value, Admissao::TODOS_PRAZOS)) {
                    $fail("A linha {$attribute} só pode ser um dos tipos de prazo: " . implode(',', Admissao::TODOS_PRAZOS));
                }
            }],
            '*.admissao.banco.nome' => 'nullable|max:200',
            '*.admissao.banco.agencia' => 'nullable|max:200',
            '*.admissao.banco.conta' => 'nullable|max:200',
            '*.admissao.banco.pix' => 'boolean',
            '*.admissao.banco.pix_tipo_chave' => 'required_if:*.admissao.banco.pix,true|max:200',
            '*.admissao.banco.pix_chave' => 'required_if:*.admissao.banco.pix,true|max:200',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao fazer importação',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        */

//        $teste = collect($dados)->split(1000);
//
//        try {
//            DB::beginTransaction();
//            foreach ($teste[0]->toArray() as $item) {
//                //fazer a criacão ou update
//
//                //Cria ou atualiza usuario
//
//                $usuario = User::whereEmpresaId($empresa_id)->whereHas('Curriculo', function ($q) use ($item) {
//                    $q->where('cpf', $item['curriculo']['cpf']);
//                });
//
//
//                $dadosUser = [
//                    'nome' => $item['curriculo']['nome'],
//                    'login' => $item['curriculo']['email'],
//                    'password' => Sistema::SenhaCpf($item['curriculo']['cpf']),
//                    'tipo' => User::FUNCIONARIO,
//                    'ativo' => true,
//                    'temp' => false,
//                    'termos' => false,
//                    'empresa_id' => $empresa_id
//                ];
//
//                if (!$usuario->first()) {
//                    $usuario = User::create($dadosUser);
//                } else {
//                    $usuario = $usuario->first();
//                    $usuario->update($dadosUser);
//                }
//
//                //Cria ou atualiza os dados bancarios
//                $dadosConta = [
//                    'banco' => $item['admissao']['banco']['nome'],
//                    'agencia' => $item['admissao']['banco']['agencia'],
//                    'conta' => $item['admissao']['banco']['conta'],
//                    'pix' => $item['admissao']['banco']['pix'],
//                    'tipochavepix' => $item['admissao']['banco']['pix_tipo_chave'],
//                    'chavepix' => $item['admissao']['banco']['pix_chave'],
//                ];
//
//                $usuario->BancoConta ? $usuario->BancoConta->update($dadosConta) : $usuario->BancoConta()->create($dadosConta);
//
//                //Cria ou atualiza o Curriculo
//                $dadosCurriculo = [
//                    'id' => $usuario->id,
//                    'cpf' => $item['curriculo']['cpf'],
//                    'nome' => $item['curriculo']['nome'],
//                    'cnh' => $item['curriculo']['cnh'],
//                    'email' => $item['curriculo']['email'],
//                    'nascimento' => $item['curriculo']['nascimento'],
//                    'naturalidade' => $item['curriculo']['naturalidade'],
//                    'logradouro' => $item['curriculo']['endereco']['logradouro'],
//                    'end_numero' => $item['curriculo']['endereco']['numero'],
//                    'complemento' => $item['curriculo']['endereco']['complemento'],
//                    'bairro' => $item['curriculo']['endereco']['bairro'],
//                    'municipio' => $item['curriculo']['endereco']['municipio'],
//                    'uf' => $item['curriculo']['endereco']['uf'],
//                    'cep' => $item['curriculo']['endereco']['cep'],
//                    'uf_vaga' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Municipio->uf,
//                    'municipio_id' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Municipio->id,
//                    'rg' => $item['curriculo']['rg'],
//                    'rg_data_emissao' => $item['curriculo']['rg_data_emissao'],
//                    'filiacao_pai' => $item['curriculo']['filiacao_pai'],
//                    'filiacao_mae' => $item['curriculo']['filiacao_mae'],
//                    'sexo' => $item['curriculo']['sexo'],
//                    'pcd' => $item['curriculo']['pcd'],
//                    'cid' => $item['curriculo']['cid'],
//                    'vaga_pretendida' => $item['curriculo']['vaga_pretendida']
//                ];
//
//                $usuario->Curriculo ? $usuario->Curriculo->update($dadosCurriculo) : $usuario->Curriculo()->create($dadosCurriculo);
//
//                //Cria ou atualiza o Telefone
//                $dadosTel = [
//                    'tipo' => $item['curriculo']['telefone']['whatsapp'],
//                    'pais' => "55",
//                    'numero' => $item['curriculo']['telefone']['numero'],
//                    'principal' => true,
//                ];
//                $telefone_id = $usuario->Curriculo->Telefones()->updateOrCreate($dadosTel)->id;
//
//                //Cria ou atualiza o Feedback
//                $usuario->Curriculo->Feedback()->updateOrCreate([
//                    'curriculo_id' => $usuario->id,
//                    'selecionado' => 'sim',
//                    'vaga_id' => $item['curriculo']['vaga_pretendida'],
//                    'cliente_id' => $empresa_id,
//                    'empresa_id' => $empresa_id,
//                    'interesse' => true,
//                    'contato_realizado' => true,
//                    'telefone_id' => $telefone_id,
//                    'vagas_abertas_id' => $item['curriculo']['vaga_pretendida']
//                ]);
//
//                //Criações de entrevistas
//                $usuario->Curriculo->Feedback->parecerRh()->updateOrCreate(['nota' => 9]);
//                $usuario->Curriculo->Feedback->parecerRota()->updateOrCreate([]);
//                $usuario->Curriculo->Feedback->parecerTecnica()->updateOrCreate([]);
//                $usuario->Curriculo->Feedback->parecerTeste()->updateOrCreate([]);
//                $usuario->Curriculo->Feedback->individualRh()->updateOrCreate([]);
//                $usuario->Curriculo->Feedback->gestorRh()->updateOrCreate([]);
//                $usuario->Curriculo->Feedback->entrevistaRh()->updateOrCreate([]);
//
//                //Criações de resultado integrado
//                $usuario->Curriculo->Feedback->ResultadoIntegrado()->updateOrCreate([
//                    'responsavel_envio' => 'importacao',
//                    'documentos_entregue' => false,
//                    'encaminhado_exame' => (bool)$item['admissao']['encaminhado_exame'],
//                    'encaminhado_exame_data' => $item['admissao']['encaminhado_exame_data'],
//                    'encaminhado_treinamento' => (bool)$item['admissao']['encaminhado_treinamento'],
//                    'encaminhado_treinamento_data' => $item['admissao']['encaminhado_treinamento_data'],
//                ]);
//
//                //Criações de admissao
//                $usuario->Curriculo->Feedback->Admissao()->updateOrCreate([
//                    'area_etiqueta_id' => $item['admissao']['area_etiqueta_id'],
//                    'data_entrega_area' => $item['admissao']['data_entrega_area'],
//                    'data_admissao' => $item['admissao']['data_admissao'],
//                    'cargo' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Vaga->nome,
//                    'funcao' => VagasAbertas::find($item['curriculo']['vaga_pretendida'])->Vaga->nome,
//                    'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
//                    'salario' => $item['admissao']['salario'],
//                    'pis' => $item['admissao']['pis'],
//                    'tipo_admissao' => $item['admissao']['tipo_admissao'],
//                    'prazo_experiencia' => $item['admissao']['prazo_experiencia'],
//                    'data_encerramento' => $item['admissao']['admissao_encerramento'],
//                    'usuario_id' => auth()->user()->id,
//                ]);
//
//                Admissao::tipoAdmissaoAvalNoventaCriarAtualizar($usuario->Curriculo->Feedback->id, $item['admissao']['tipo_admissao'], $item['admissao']['prazo_experiencia'], $item['admissao']['data_admissao'], $item['admissao']['admissao_encerramento']);
//                AdmissaoAso::criarAtualizar($usuario->Curriculo->Feedback->Admissao->id, $empresa_id, $item['admissao']['data_aso']);
//
//                //DadosAdmissoes
//                $usuario->Curriculo->Feedback->Admissao->DadosAdmissoes()->updateOrCreate([
//                    'ctps_numero' => $item['admissao']['ctps_numero'],
//                    'ctps_serie' => $item['admissao']['ctps_serie'],
//                    'ctps_data_emissao' => $item['admissao']['ctps_data_emissao'],
//                    'titulo_eleitor_numero' => $item['admissao']['titulo_eleitor_numero'],
//                    'titulo_eleitor_sessao' => $item['admissao']['titulo_eleitor_sessao'],
//                    'titulo_eleitor_zona' => $item['admissao']['titulo_eleitor_zona'],
//                ]);
//            }
//            DB::commit();
//            return response()->json(['msg' => 'Importação realizada com sucesso'], 201);
//        } catch (\Exception $e) {
//            DB::rollback();
//            return response()->json(['error' => $e->getMessage() . ' - ' . $e->getLine()], 500);
//        }


        die;

        $arquivo = storage_path('app/public/teste.csv');
        $file = fopen($arquivo, 'r');
        $empresa_id = auth()->user()->empresa_id;

        $i = 0;
        $collect = collect();

        while (($line = fgetcsv($file, 1000, ";")) !== false) {

            if ($i > 0) {
                $collect->push([
                    "curriculo" => [
                        'cpf' => Sistema::mascaraCpf($line[0]),
                        "nome" => $line[1],
                        "email" => mb_strtolower(trim($line[2])),
                        "cnh" => $line[3],
                        "rg" => preg_replace("/[^0-9]/", "", $line[4]),
                        "rg_data_emissao" => (new DataHora($line[5]))->dataCompleta(),
                        "nascimento" => (new DataHora($line[6]))->dataCompleta(),
                        "filiacao_pai" => $line[7],
                        "filiacao_mae" => $line[8],
                        "pcd" => mb_strtolower(trim($line[9])) == "sim",
                        "cid" => $line[10],
                        "vaga_pretendida" => intval($line[20]),
                        "telefone" => [
                            "whatsapp" => mb_strtolower(trim($line[18])) == "sim" ? "whatsapp" : "celular",
                            "telefone_numero" => Sistema::mascaraTelefone($line[19]),
                        ],
                        "endereco" => [
                            "cep" => Sistema::mascaraCep($line[11]),
                            "logradouro" => $line[12],
                            "numero" => $line[13],
                            "complemento" => $line[14],
                            "bairro" => $line[15],
                            "municipio" => $line[16],
                            "uf" => $line[17],
                        ],
                    ],
                    "admissao" => [
                        "funcao" => $line[21],
                        "salario" => $line[22],
                        "pis" => $line[23],
                        "ctps_numero" => $line[24],
                        "ctps_serie" => $line[25],
                        "ctps_data_emissao" => (new DataHora($line[26]))->dataCompleta(),
                        "titulo_eleitor_numero" => $line[27],
                        "titulo_eleitor_sessao" => $line[28],
                        "titulo_eleitor_zona" => $line[29],
                        "tipo_admissao" => mb_strtoupper(\Str::slug($line[30])),
                        "banco" => [
                            "nome" => $line[31],
                            "agencia" => $line[32],
                            "conta" => $line[33],
                            "pix" => mb_strtolower(trim($line[34])) == "sim",
                            "pix_tipo_chave" => $line[35],
                            "pix_chave" => $line[36]
                        ]
                    ]
                ]);
            }
            $i++;
        }

        $filtrado = $collect->filter(function ($item) {
            return $item['curriculo']['cpf'] != '';
        })->unique('curriculo.cpf');

        $dadosValidados = \Validator::make($filtrado->toArray(), [
            '*.curriculo.cpf' => ['required', 'min:14',
                'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/',
                new CpfValidoEmpresaRules($empresa_id),
                new VerificaCpfEmpresaRules($empresa_id, true)
            ],
            '*.curriculo.nome' => 'required|max:255',
            '*.curriculo.email' => 'required|email:rfc,dns',
            '*.curriculo.nascimento' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.curriculo.rg' => 'required',
            '*.curriculo.rg_data_emissao' => 'required|date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.curriculo.filiacao_pai' => 'max:255',
            '*.curriculo.filiacao_mae' => 'required|max:255',
            '*.curriculo.pcd' => 'required|boolean',
            '*.curriculo.cid' => 'required_if:*.curriculo.pcd,true',
            '*.curriculo.vaga_pretendida' => ['required', new VagaAbertaEmpresaRules($empresa_id)],
            '*.curriculo.endereco.cep' => 'required|min:9|regex:/^\d{5}-\d{3}$/',
            '*.curriculo.endereco.logradouro' => 'required|max:255',
            '*.curriculo.endereco.numero' => 'max:10',
            '*.curriculo.endereco.complemento' => 'max:255',
            '*.curriculo.endereco.bairro' => 'required|max:255',
            '*.curriculo.endereco.municipio' => 'required|max:255',
            '*.curriculo.endereco.uf' => 'required|max:2|regex:/^[A-Z]{2}$/',
            '*.curriculo.telefone.whatsapp' => 'required|in:' . implode(",", TelefoneCurriculo::TIPOS),
            '*.curriculo.telefone.numero' => ['required|max:16'],
            '*.admissao.funcao' => 'max:255',
            '*.admissao.salario' => 'max:100',
            '*.admissao.pis' => 'max:200',
            '*.admissao.ctps_numero' => 'max:200',
            '*.admissao.ctps_serie' => 'max:200',
            '*.admissao.ctps_data_emissao' => 'date_format:d/m/Y|regex:/^\d{2}\/\d{2}\/\d{4}$/',
            '*.admissao.titulo_eleitor_numero' => 'max:200',
            '*.admissao.titulo_eleitor_sessao' => 'max:200',
            '*.admissao.titulo_eleitor_zona' => 'max:200',
            '*.admissao.tipo_admissao' => "required|in:" . implode(",", Admissao::TODOS_TIPOS_ADMISSAO),
            '*.admissao.banco.nome' => 'required|max:200',
            '*.admissao.banco.agencia' => 'required|max:200',
            '*.admissao.banco.conta' => 'required|max:200',
            '*.admissao.banco.pix' => 'required|boolean',
            '*.admissao.banco.pix_tipo_chave' => 'required_if:*.admissao.banco.pix,true|max:200',
            '*.admissao.banco.pix_chave' => 'required_if:*.admissao.banco.pix,true|max:200',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao fazer importação',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
    }
}
