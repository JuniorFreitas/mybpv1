<?php

namespace App\Http\Controllers\Api;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappCurriculoTelefoneResolver;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Http\Controllers\Controller;
use App\Jobs\JobEnvioCartaOferta;
use App\Models\Admissao;
use App\Models\CartaOferta;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\VagaProjeto;
use App\Models\VagasAbertas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntegraSgiMybpController extends Controller
{
    public static function integra($dados)
    {

        //loga como empresa para usar withGlobalScope
        $empresa_id = $dados['empresa_id'];
        $curriculoDados = $dados['curriculo'];
        $feedbackDados = $dados['feedback'];
        $parecerRhDados = $dados['parecer_rh'];
        $gestorRhDados = $dados['gestor_rh'];
        $entrevistaRhDados = $dados['entrevista_rh'];

        try {
            DB::beginTransaction();
            //Cria Usuario

            $usuario = User::where('empresa_id', $empresa_id)
                ->whereHas('Curriculo', function ($q) use ($curriculoDados) {
                    $q->withoutGlobalScopes()->where('cpf', $curriculoDados['cpf']);
                });

            $dadosUser = [
                'nome' => $curriculoDados['nome'],
                'login' => $curriculoDados['email'],
                'password' => Sistema::SenhaCpf($curriculoDados['cpf']),
                'tipo' => User::FUNCIONARIO,
                'ativo' => true,
                'temp' => false,
                'termos' => false,
                'empresa_id' => $empresa_id
            ];

            if ($usuario->count() == 0) {
                $novoUsuario = true;
                $usuario = User::create($dadosUser);
            } else {
                $novoUsuario = false;
                $usuario = $usuario->first();
                $usuario->update($dadosUser);
            }
            //loga como o usuario para nao precisar de withGlobalScope
            $vagaAbertaSelecionada = VagasAbertas::find($curriculoDados['vaga_pretendida']);

            //Cria ou atualiza o Curriculo
            $dadosCurriculo = [
                'id' => $usuario->id,
                'cpf' => Sistema::mascaraCpf($curriculoDados['cpf']),
                'nome' => $curriculoDados['nome'],
                'estado_civil' => $curriculoDados['estado_civil'] ?? null,
                'cnh' => $curriculoDados['cnh'],
                'cnh_vencimento' => $curriculoDados['cnh_vencimento'] ?? null,
                'email' => trim($curriculoDados['email']),
                'nascimento' => $curriculoDados['nascimento'],
                'naturalidade' => $curriculoDados['naturalidade'] ?? null,
                'logradouro' => $curriculoDados['logradouro'],
                'end_numero' => $curriculoDados['numero'] ?? null,
                'complemento' => $curriculoDados['complemento'],
                'bairro' => $curriculoDados['bairro'],
                'municipio' => $curriculoDados['municipio'],
                'uf' => $curriculoDados['uf'],
                'cep' => $curriculoDados['cep'],
                "formacao" => $curriculoDados['formacao'],
                "formacao_instituicao" => $curriculoDados['formacao_instituicao'],
                "formacao_curso" => $curriculoDados['formacao_curso'],
                "formacao_status" => $curriculoDados['formacao_status'],
                "disponibilidade_sabado" => $curriculoDados['disponibilidade_sabado'],
                "disponibilidade_domingo" => $curriculoDados['disponibilidade_domingo'],
                'uf_vaga' => $vagaAbertaSelecionada->Municipio->uf,
                'municipio_id' => $vagaAbertaSelecionada->municipio_id,
                'rg' => $curriculoDados['rg'],
                'rg_data_emissao' => $curriculoDados['rg_data_emissao'] ?? null,
                'filiacao_pai' => $curriculoDados['filiacao_pai'],
                'filiacao_mae' => $curriculoDados['filiacao_mae'],
                'sexo' => $curriculoDados['sexo'] ?? null,
                'pcd' => $curriculoDados['pcd'],
                'cid' => $curriculoDados['cid'],
                'vaga_pretendida' => $curriculoDados['vaga_pretendida']
            ];

            $curriculo = Curriculo::find($usuario->id);

            if (is_null($curriculo)) {
                $curriculo = Curriculo::create($dadosCurriculo);
            } else {
                $curriculo->update($dadosCurriculo);
            }

            if ($curriculo->Telefones()->count() == 0) {
                $telefone = $curriculo->Telefones()->create([
                    'tipo' => $curriculoDados['telefone']['tipo'],
                    'pais' => $curriculoDados['telefone']['pais'],
                    'numero' => $curriculoDados['telefone']['numero'],
                    'ramal' => $curriculoDados['telefone']['ramal'],
                    'detalhe' => $curriculoDados['telefone']['detalhe'],
                    'principal' => true
                ]);
            } else {
                $telefone = $curriculo->Telefones()->first();
            }


            //Criar Feedback
            $curriculo->Feedback()->updateOrCreate([
                'curriculo_id' => $feedbackDados['curriculo_id'],
                'selecionado' => $feedbackDados['selecionado'],
                'vaga_id' => $vagaAbertaSelecionada->vaga_id,
                'usuario_entrevista_marcado' => $feedbackDados['usuario_entrevista_marcado'],
                'cliente_id' => $empresa_id,
                'contato_realizado' => $feedbackDados['contato_realizado'],
                'interesse' => $feedbackDados['interesse'],
                'data_entrevista' => $feedbackDados['data_entrevista'],
                'local_entrevista' => $feedbackDados['local_entrevista'],
                'telefone_id' => $telefone->id,
                'obs' => $feedbackDados['obs'],
                'status' => $feedbackDados['status'],
                'vagas_abertas_id' => $vagaAbertaSelecionada->id,
                'vaga_projeto_id' => $dados['admissao']['vaga_projeto_mybp_id'],
                'empresa_id' => $empresa_id,
            ]);

            // Vaga Projeto atualizacao
            $vagaProjeto = VagaProjeto::find($dados['admissao']['vaga_projeto_mybp_id']);
            if ($vagaProjeto->tem_vaga) {
                $vagaProjeto->increment('qnt_preenchida');
                $vagaProjeto->save();
                $vagaProjeto->Projeto->increment('preenchidas');
            } else {
                \Log::critical('Vaga não disponível para o projeto selecionado' . $vagaProjeto->id . ' Curriculo: ' . $curriculo->id);
            }

            unset($parecerRhDados['curriculo_id']);
            $parecerRhDados['feedback_id'] = $curriculo->Feedback->id;

            //Criações de entrevistas
            $curriculo->Feedback->parecerRh()->updateOrCreate([
                'destro' => $parecerRhDados['destro'] ?? null,
                'ex_funcionario' => $parecerRhDados['ex_funcionario'] ?? null,
                'formulario_id' => $parecerRhDados['formulario_id'] ?? null,
                'cnh' => $parecerRhDados['cnh'] ?? null,
                'cnh_tipo' => $parecerRhDados['cnh_tipo'] ?? null,
                'rota_bairro' => $parecerRhDados['rota_bairro'] ?? null,
                'calca' => $parecerRhDados['calca'] ?? null,
                'bota' => $parecerRhDados['bota'] ?? null,
                'camisa_protecao' => $parecerRhDados['camisa_protecao'] ?? null,
                'camisa_meia' => $parecerRhDados['camisa_meia'] ?? null,
                'mora_com_quem' => $parecerRhDados['mora_com_quem'] ?? null,
                'casado' => $parecerRhDados['casado'] ?? null,
                'tempodeconvivencia' => $parecerRhDados['tempodeconvivencia'] ?? null,
                'filhos' => $parecerRhDados['filhos'] ?? null,
                'qnt_filhos' => $parecerRhDados['qnt_filhos'] ?? null,
                'conjuge_trabalha' => $parecerRhDados['conjuge_trabalha'] ?? null,
                'trabalho_conjuge' => $parecerRhDados['trabalho_conjuge'] ?? null,
                'religioso' => $parecerRhDados['religioso'] ?? null,
                'religiao_praticante' => $parecerRhDados['religiao_praticante'] ?? null,
                'fuma' => $parecerRhDados['fuma'] ?? null,
                'frequencia_fuma' => $parecerRhDados['frequencia_fuma'] ?? null,
                'bebe' => $parecerRhDados['bebe'] ?? null,
                'frequencia_bebe' => $parecerRhDados['frequencia_bebe'] ?? null,
                'indicacao' => $parecerRhDados['indicacao'] ?? null,
                'indicado_por' => $parecerRhDados['indicado_por'] ?? null,
                'alumar_experiencia' => $parecerRhDados['alumar_experiencia'] ?? null,
                'alumar_experiencia_area' => $parecerRhDados['alumar_experiencia_area'] ?? null,
                'outra_industria_experiencia' => $parecerRhDados['outra_industria_experiencia'] ?? null,
                'outra_industria_nome' => $parecerRhDados['outra_industria_nome'] ?? null,
                'grau_instrucao' => $parecerRhDados['grau_instrucao'] ?? null,
                'horaextra' => $parecerRhDados['horaextra'] ?? null,
                'turnos_seis_por_dois' => $parecerRhDados['turnos_seis_por_dois'] ?? null,
                'noturno' => $parecerRhDados['noturno'] ?? null,
                'acidente_trabalho' => $parecerRhDados['acidente_trabalho'] ?? null,
                'acidente_trabalho_qual' => $parecerRhDados['acidente_trabalho_qual'] ?? null,
                'afastamento_inss' => $parecerRhDados['afastamento_inss'] ?? null,
                'afastamento_inss_qual' => $parecerRhDados['afastamento_inss_qual'] ?? null,
                'situacao_saude' => $parecerRhDados['situacao_saude'] ?? null,
                'nr_dez' => $parecerRhDados['nr_dez'] ?? null,
                'comportamento_seguro' => $parecerRhDados['comportamento_seguro'] ?? null,
                'energia_para_trabalho' => $parecerRhDados['energia_para_trabalho'] ?? null,
                'postura' => $parecerRhDados['postura'] ?? null,
                'historico_profissional' => $parecerRhDados['historico_profissional'] ?? null,
                'historico_educacional' => $parecerRhDados['historico_educacional'] ?? null,
                'objetivos_expectativas' => $parecerRhDados['objetivos_expectativas'] ?? null,
                'auto_imagem' => $parecerRhDados['auto_imagem'] ?? null,
                'competencias' => $parecerRhDados['competencias'] ?? null,
                'comportamento_etico' => $parecerRhDados['comportamento_etico'] ?? null,
                'comprometimento' => $parecerRhDados['comprometimento'] ?? null,
                'comunicacao' => $parecerRhDados['comunicacao'] ?? null,
                'cultura_qualidade' => $parecerRhDados['cultura_qualidade'] ?? null,
                'foco_cliente' => $parecerRhDados['foco_cliente'] ?? null,
                'iniciativa' => $parecerRhDados['iniciativa'] ?? null,
                'orientacao_resultados' => $parecerRhDados['orientacao_resultados'] ?? null,
                'trabalho_equipe' => $parecerRhDados['trabalho_equipe'] ?? null,
                'parecer_final' => $parecerRhDados['parecer_final'] ?? null,
                'parecer_final_um' => $parecerRhDados['parecer_final_um'] ?? null,
                'nota' => $parecerRhDados['nota'] ?? null,
                'comentarios' => $parecerRhDados['comentarios'] ?? null,
                'entrevistador' => $parecerRhDados['entrevistador'] ?? null,
                'quem_entrevistou' => $parecerRhDados['quem_entrevistou'] ?? null,
                'tipo_entrevista' => $parecerRhDados['tipo_entrevista'] ?? null,
                'nota_digitacao' => $parecerRhDados['nota_digitacao'] ?? null,
                'dinamicadegrupo' => $parecerRhDados['dinamicadegrupo'] ?? null,
                'obs_dinamicadegrupo' => $parecerRhDados['obs_dinamicadegrupo'] ?? null,
                'experiencia_callcenter' => $parecerRhDados['experiencia_callcenter'] ?? null,
                'disponibilidade_horarios' => $parecerRhDados['disponibilidade_horarios'] ?? null,
                'turnos_seis_por_um' => $parecerRhDados['turnos_seis_por_um'] ?? null,
                'horario_preferencial' => $parecerRhDados['horario_preferencial'] ?? null,
                'obs_call' => $parecerRhDados['obs_call'] ?? null,
                'obs_horario' => $parecerRhDados['obs_horario'] ?? null,
            ]);

            $curriculo->Feedback->parecerRota()->updateOrCreate([]);

            $curriculo->Feedback->parecerTecnica()->updateOrCreate([]);
            $curriculo->Feedback->parecerTeste()->updateOrCreate([]);
            $curriculo->Feedback->individualRh()->updateOrCreate([]);
            $curriculo->Feedback->gestorRh()->updateOrCreate([]);
            $curriculo->Feedback->entrevistaRh()->updateOrCreate([]);


            $curriculo->Feedback->ResultadoIntegrado()->updateOrCreate([
                'responsavel_envio' => 'Interegracao SGI',
                'documentos_entregue' => true,
                'encaminhado_exame' => true,
                'encaminhado_exame_data' => null,
                'encaminhado_treinamento' => true,
                'encaminhado_treinamento_data' => null
            ]);


            //Criações de admissao
            $curriculo->Feedback->Admissao()->updateOrCreate([
                'centro_custo_id' => null,
                'area_etiqueta_id' => null,
                'data_entrega_area' => null,
                'data_admissao' => null,
                'cargo' => $vagaAbertaSelecionada->Vaga->nome,
                'funcao' => $vagaAbertaSelecionada->Vaga->nome,
                'status' => Admissao::STATUS_ADMISSAO_PENDENTEDOCUMENTO,
                'salario' => $dados['admissao'] ['salario'],
                'pis' => null,
                'tipo_admissao' => Admissao::TIPO_ADMISSAO_FIXO,
                'prazo_experiencia' => Admissao::QUARENTAECINCO_MAIS_QUARENTAECINCO,
                'data_encerramento' => null,
                'usuario_id' => auth()->user()->id,
            ]);

//            Admissao::tipoAdmissaoAvalNoventaCriarAtualizar($curriculo->Feedback->id, Admissao::TIPO_ADMISSAO_FIXO, Admissao::QUARENTAECINCO_MAIS_QUARENTAECINCO, $record['admissao']['data_admissao'], $record['admissao']['admissao_encerramento']);

            //DadosAdmissoes
            $curriculo->Feedback->Admissao->DadosAdmissoes()->updateOrCreate([
                'ctps_numero' => null,
                'ctps_serie' => null,
                'ctps_data_emissao' => null,
                'titulo_eleitor_numero' => null,
                'titulo_eleitor_sessao' => null,
                'titulo_eleitor_zona' => null,
            ]);

            DB::commit();

            return response()->json(['msg' => "sucesso"], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Sistema::telegram('Erro ao integrar MyBP - ' . $e->getMessage());
            Sistema::telegram(print_r(json_encode($curriculoDados, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 1));
            Sistema::telegram(print_r(json_encode($feedbackDados, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 1));
            Sistema::telegram(print_r(json_encode($parecerRhDados, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 1));
            Sistema::telegram(print_r(json_encode($dados['admissao'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 1));

            return response()->json(['msg' => $e->getMessage() . ' - ' . $e->getLine()], 400);
        }


//        return $request->input();


//       return response()->json($request->input(), 200);
    }

    public function aceiteCartaOferta(Request $request)
    {

        $cache = 'integra_' . $request->curriculo['cpf'];

        if (is_null(\Cache::get($cache))) {
            \Cache::put($cache, $request->input(), now()->addHours(72));
        }

        $dados = \Cache::get($cache);

        $empresa_id = $dados['empresa_id'];
        $curriculoDados = $dados['curriculo'];

        try {
            DB::beginTransaction();
            //Cria Usuario
            $usuario = User::where('empresa_id', $empresa_id)
                ->whereHas('Curriculo', function ($q) use ($curriculoDados) {
                    $q->withoutGlobalScopes()->where('cpf', $curriculoDados['cpf']);
                });

            $dadosUser = [
                'nome' => $curriculoDados['nome'],
                'login' => $curriculoDados['email'],
                'password' => Sistema::SenhaCpf($curriculoDados['cpf']),
                'tipo' => User::FUNCIONARIO,
                'ativo' => false,
                'temp' => false,
                'termos' => false,
                'empresa_id' => $empresa_id
            ];

            if ($usuario->count() == 0) {
                $usuario = User::create($dadosUser);
            } else {
                $usuario = $usuario->where('login', $curriculoDados['email'])->where('empresa_id', $empresa_id);
                $usuario->update($dadosUser);
                $usuario = $usuario->first();
            }

            $vagaAbertaSelecionada = VagasAbertas::withoutGlobalScopes()->find($curriculoDados['vaga_pretendida']);

            //Cria ou atualiza o Curriculo
            $dadosCurriculo = [
                'id' => $usuario->id,
                'cpf' => Sistema::mascaraCpf($curriculoDados['cpf']),
                'nome' => $curriculoDados['nome'],
                'estado_civil' => $curriculoDados['estado_civil'] ?? null,
                'cnh' => $curriculoDados['cnh'],
                'cnh_vencimento' => $curriculoDados['cnh_vencimento'] ?? null,
                'email' => trim($curriculoDados['email']),
                'nascimento' => $curriculoDados['nascimento'],
                'naturalidade' => $curriculoDados['naturalidade'] ?? null,
                'logradouro' => $curriculoDados['logradouro'],
                'end_numero' => $curriculoDados['numero'] ?? null,
                'complemento' => $curriculoDados['complemento'],
                'bairro' => $curriculoDados['bairro'],
                'municipio' => $curriculoDados['municipio'],
                'uf' => $curriculoDados['uf'],
                'cep' => $curriculoDados['cep'],
                "formacao" => $curriculoDados['formacao'],
                "formacao_instituicao" => $curriculoDados['formacao_instituicao'],
                "formacao_curso" => $curriculoDados['formacao_curso'],
                "formacao_status" => $curriculoDados['formacao_status'],
                "disponibilidade_sabado" => $curriculoDados['disponibilidade_sabado'],
                "disponibilidade_domingo" => $curriculoDados['disponibilidade_domingo'],
                'uf_vaga' => $vagaAbertaSelecionada->Municipio->uf,
                'municipio_id' => $vagaAbertaSelecionada->municipio_id,
                'rg' => $curriculoDados['rg'],
                'rg_data_emissao' => $curriculoDados['rg_data_emissao'] ?? null,
                'filiacao_pai' => $curriculoDados['filiacao_pai'],
                'filiacao_mae' => $curriculoDados['filiacao_mae'],
                'sexo' => $curriculoDados['sexo'] ?? null,
                'pcd' => $curriculoDados['pcd'],
                'cid' => $curriculoDados['cid'],
                'vaga_pretendida' => $curriculoDados['vaga_pretendida']
            ];

            $curriculo = Curriculo::withoutGlobalScopes()->find($usuario->id);

            if (is_null($curriculo)) {
                $curriculo = Curriculo::withoutGlobalScopes()->where('empresa_id', $empresa_id)->create($dadosCurriculo);
            } else {
                $curriculo->update($dadosCurriculo);
            }

            if ($curriculo->Telefones()->count() == 0) {
                $curriculo->Telefones()->create([
                    'tipo' => $curriculoDados['telefone']['tipo'],
                    'pais' => $curriculoDados['telefone']['pais'],
                    'numero' => $curriculoDados['telefone']['numero'],
                    'ramal' => $curriculoDados['telefone']['ramal'],
                    'detalhe' => $curriculoDados['telefone']['detalhe'],
                    'principal' => true,
                ]);
            }

            $telefoneWhatsapp = app(WhatsappCurriculoTelefoneResolver::class)
                ->resolverPrincipalWhatsapp((int) $curriculo->id);

            $vagaAbertaSelecionada = VagasAbertas::withoutGlobalScopes()->find($curriculoDados['vaga_pretendida']);

            // Cria Carta Oferta
            CartaOferta::withoutGlobalScopes()->updateOrCreate(
                [
                    'token' => $dados['token'],
                    'empresa_id' => $empresa_id,
                    'curriculo_id' => $curriculo->id,
                    'vagas_abertas_id' => $vagaAbertaSelecionada->id,
                    'vaga_projeto_id' => $dados['admissao']['vaga_projeto_mybp_id'],
                    'local' => CartaOferta::LOCAL_SGI
                ]
            );

            $Empresa = Cliente::withoutGlobalScopes()->select(['id', 'apelido'])->find($empresa_id);
            DB::commit();


            $link_carta_oferta = env('APP_URL')."/{$Empresa->apelido}/carta-oferta/{$dados['token']}";

            JobEnvioCartaOferta::dispatch([
                'nome' => $curriculo->nome,
                'email' => $curriculo->email,
                'empresa_id' => $empresa_id,
                'url_documento' => $link_carta_oferta,
            ]);

            if (
                $telefoneWhatsapp
                && app(WhatsappNotificationGateService::class)->podeEnviar(
                    TipoMensagemWhatsapp::CartaOfertaSgi,
                    (int) $empresa_id,
                )
            ) {
                $dados['telefone'] = $telefoneWhatsapp->sonumero;

                $ambiente = env('AMBIENTE', 'local') == 'prod' ?: 'local';
                if ($ambiente != 'prod') {
                    $zapTelAtivo = \DB::table("zap_numeros")->where("ativo", true)->first();
                    $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '559899023762';
                }

                $mensagemWhats = app(WhatsappMessageFactory::class)->render(
                    TipoMensagemWhatsapp::CartaOfertaSgi,
                    (int) $empresa_id,
                    [
                        'nome_destinatario' => $curriculo->nome,
                        'url_carta' => $link_carta_oferta,
                        'prazo_resposta' => '24h úteis',
                    ]
                );

                (new ZapNotificacao())->enviar([
                    'enviado_id' => 1,
                    'telefone' => preg_replace('/[^0-9]/', '', $dados['telefone']),
                    'mensagem' => $mensagemWhats,
                    '_whatsapp_meta' => ZapNotificacao::meta(
                        TipoMensagemWhatsapp::CartaOfertaSgi,
                        (int) $empresa_id,
                    ),
                ]);
            }

            return "sucesso";

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => $e->getMessage() . ' - ' . $e->getLine()], 400);
        }
    }

}
