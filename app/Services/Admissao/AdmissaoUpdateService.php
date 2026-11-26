<?php

namespace App\Services\Admissao;

use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\AvaliacaoNoventaVencimento;
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
 * Atualização do processo de Admissão (TD-002 fatia 6).
 */
class AdmissaoUpdateService
{
    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function atualizar(Request $request, FeedbackCurriculo $admissao): array
    {
        $dados = $request->input();
        $dadosCurriculo = $dados['curriculo'];
        $feedback = $admissao;
        $dadosVagaAberta = VagasAbertas::find($dados['vagas_abertas_id']);
        $dados['admissao']['cargo'] = $dadosVagaAberta->Vaga->nome;
        $admissaoDados = $dados['admissao'];
        $admissaoDados['feedback_id'] = $feedback->id;
        $admissaoDados = array_merge(
            $admissaoDados,
            app(\App\Services\Lotacao\LotacaoDoCentroCusto::class)->aPartirDoCentro(
                (int) ($admissaoDados['centro_custo_id'] ?? 0),
                (int) $feedback->empresa_id
            )
        );

        $dados['curriculo']['email'] = $dados['curriculo']['email'] == "" ? Sistema::EMAILPADRAO : $dados['curriculo']['email'];

        $data_nascimento = new DataHora($dados['curriculo']['nascimento']);

        $dados['curriculo']['nascimento'] = $data_nascimento->dataInsert();

        if (in_array($dados['admissao']['status'], [Admissao::STATUS_ADMISSAO_ADMITIDO, Admissao::STATUS_ADMISSAO_PRONTOPARAADMISSAO])) {
            if (trim($dados['admissao']['data_admissao']) == 0) {
                return ['status' => 400, 'body' => ['msg' => 'Informe a data da Admissão']];
            }
        }

        // Validação estrutural via UpdateAdmissaoRequest no controller.
        $padraoId = app(AdmissaoPadraoTreinamentoResolver::class)->resolver($admissaoDados['padrao_treinamento_id'] ?? null);
        if (!empty($admissaoDados['padrao_treinamento_id']) && !$padraoId) {
            return ['status' => 400, 'body' => ['msg' => 'Padrão de treinamento inválido ou de outra empresa.', 'erros' => ['admissao.padrao_treinamento_id' => ['Padrão inválido.']]]];
        }
        $admissaoDados['padrao_treinamento_id'] = $padraoId;

        {
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
                $dados['resultado_integrado'] = array_merge(
                    $dados['resultado_integrado'],
                    app(\App\Services\Lotacao\LotacaoDoCentroCusto::class)->aPartirDoCentro(
                        (int) ($dados['resultado_integrado']['centro_custo_id'] ?? 0),
                        (int) $feedback->empresa_id
                    )
                );

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
                return ['status' => 201, 'body' => []];

            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Erro ao atualizar admissão', [
                    'feedback_id' => $feedback->id ?? null,
                    'arquivo' => $e->getFile(),
                    'linha' => $e->getLine(),
                    'codigo' => $e->getCode(),
                    'erro' => $e->getMessage(),
                ]);

                return ['status' => 400, 'body' => ['msg' => 'Houve um erro por favor tente novamente!']];
            }
        }
        }
}
