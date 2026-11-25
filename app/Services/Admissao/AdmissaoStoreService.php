<?php

namespace App\Services\Admissao;

use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\AvaliacaoNoventaVencimento;
use App\Models\Curriculo;
use App\Models\DadosAdmissao;
use App\Models\EmpresaExame;
use App\Models\ExameFuncionario;
use App\Models\FeedbackCurriculo;
use App\Models\Formulario;
use App\Models\Pcmso;
use App\Models\ResultadoIntegrado;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\UsuarioConta;
use App\Models\VagaProjeto;
use App\Models\VagasAbertas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasterTag\DataHora;

/**
 * Criação (store) do processo de Admissão (TD-002 fatia 7).
 */
class AdmissaoStoreService
{
    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function criar(Request $request): array
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
        $dadosAdmissao['padrao_treinamento_id'] = app(AdmissaoPadraoTreinamentoResolver::class)->resolver($dadosAdmissao['padrao_treinamento_id'] ?? null);

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
            return ['status' => 400, 'body' => ['msg' => 'Por favor insira um telefone']];
        }

        if (in_array($dados['admissao']['status'], [Admissao::STATUS_ADMISSAO_ADMITIDO, Admissao::STATUS_ADMISSAO_PRONTOPARAADMISSAO])) {
            if (trim($dados['admissao']['data_admissao']) == 0) {
                return ['status' => 400, 'body' => ['msg' => 'Informe a data do Admissão']];
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
                                DB::rollBack();
                                return ['status' => 400, 'body' => ['msg' => 'Vaga não disponível para o projeto selecionado']];
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
                            DB::rollBack();
                            return ['status' => 400, 'body' => ['msg' => 'Vaga não disponível para o projeto selecionado']];
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
                                DB::rollBack();
                                return ['status' => 400, 'body' => ['msg' => 'Vaga não disponível para o projeto selecionado']];
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
                            DB::rollBack();
                            return ['status' => 400, 'body' => ['msg' => 'Vaga não disponível para o projeto selecionado']];
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
            return ['status' => 201, 'body' => []];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao criar admissão avulsa', [
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'codigo' => $e->getCode(),
                'erro' => $e->getMessage(),
            ]);
            return ['status' => 400, 'body' => ['msg' => 'Houve um erro por favor tente novamente, Caso persista entre em contato com o suporte!']];

        }
    }
}
