<?php

namespace Tests\Support;

use App\Models\User;
use App\Services\ApiExterna\ApiExternaCredencialService;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait ApiExternaSchema
{
    protected function garantirSchemaApiExterna(): void
    {
        if (Schema::hasTable('empresa_api_credenciais')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('tipo')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('temp')->default(false);
            $table->boolean('termos')->default(false);
            $table->string('api_token')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('grupo_cloud_id')->nullable();
            $table->boolean('require_password_reset')->default(false);
            $table->unsignedInteger('password_reset_days')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('descricao')->nullable();
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('empresa_api_credenciais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('user_id');
            $table->string('client_id', 64);
            $table->string('client_secret_hash', 255);
            $table->json('scopes');
            $table->boolean('ativo')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('client_id');
        });

        Schema::create('clouds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->string('slug')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('grupo_clouds', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('empresa_id')->nullable();
        });

        Schema::create('user_clouds', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cloud_id');
        });

        Schema::create('itens_cloud', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cloud_id');
            $table->unsignedBigInteger('arquivo_id')->nullable();
            $table->string('label')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('pertence')->nullable();
            $table->unsignedBigInteger('pertence_anterior')->nullable();
            $table->unsignedBigInteger('quem_criou')->nullable();
            $table->unsignedBigInteger('quem_editou')->nullable();
            $table->unsignedBigInteger('quem_aprovou')->nullable();
            $table->unsignedBigInteger('quem_revisou')->nullable();
            $table->unsignedBigInteger('quem_moveu')->nullable();
            $table->unsignedBigInteger('quem_excluiu')->nullable();
            $table->boolean('aprovado')->default(false);
            $table->timestamp('data_aprovacao')->nullable();
            $table->boolean('revisado')->default(false);
            $table->timestamp('data_revisao')->nullable();
            $table->boolean('movido')->default(false);
            $table->timestamp('data_movido')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('permissoes_itens_clouds', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('grupo_cloud_id');
        });

        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('tipo')->nullable();
            $table->string('tipo_pessoa')->nullable();
            $table->string('nome')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('cpf')->nullable();
            $table->string('email')->nullable();
            $table->string('contato')->nullable();
            $table->string('aniversario')->nullable();
            $table->string('cep')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('municipio')->nullable();
            $table->string('uf')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('tipo_servico_fornecedor', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('fornecedor_servico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fornecedor_id');
            $table->unsignedBigInteger('tipo_servico_fornecedor_id')->nullable();
            $table->string('vencimento')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_encerramento')->nullable();
            $table->text('escopo')->nullable();
            $table->string('valor')->nullable();
            $table->string('tipo_faturamento')->nullable();
            $table->string('status')->nullable();
            $table->string('feedback')->nullable();
            $table->boolean('ativo')->default(true);
        });

        Schema::create('usuarios_telefone', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('tipo')->nullable();
            $table->string('pais')->nullable();
            $table->string('numero')->nullable();
            $table->string('ramal')->nullable();
            $table->string('detalhe')->nullable();
            $table->boolean('principal')->default(false);
        });

        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('extensao')->nullable();
            $table->unsignedBigInteger('bytes')->nullable();
            $table->string('file')->nullable();
            $table->string('thumb')->nullable();
            $table->string('disco')->nullable();
            $table->boolean('imagem')->default(false);
            $table->timestamps();
        });

        Schema::create('fornecedor_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('fornecedor_id');
            $table->unsignedBigInteger('arquivo_id');
        });

        Schema::create('fornecedor_servico_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('fornecedor_servico_id');
            $table->unsignedBigInteger('arquivo_id');
        });
    }

    protected function garantirSchemaAvaliacoesApiExterna(): void
    {
        $this->garantirSchemaApiExterna();

        if (Schema::hasTable('avaliacoes')) {
            return;
        }

        Schema::create('avaliacoes_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('tipo_pj')->default(false);
        });

        Schema::create('avaliacao_avaliadores_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('label');
            $table->string('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('tipo_pj')->default(false);
        });

        Schema::create('avaliacoes_topicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('avaliacao_tipo_id')->nullable();
            $table->unsignedBigInteger('topico_pai_id')->nullable();
            $table->string('topico');
            $table->string('topico_explicacao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('tipo_pj')->default(false);
        });

        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('avaliacao_tipo_id')->nullable();
            $table->string('titulo');
            $table->integer('ano_avaliacao')->nullable();
            $table->string('data_inicio_prazo')->nullable();
            $table->string('data_fim_prazo')->nullable();
            $table->string('status')->default('Aberta');
            $table->boolean('ativo')->default(true);
            $table->boolean('auto_avaliacao')->default(false);
            $table->json('fluxo')->nullable();
            $table->boolean('tipo_pj')->default(false);
            $table->boolean('mostrar_notas_avaliador_final')->default(false);
        });

        Schema::create('avaliacoes_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('avaliacao_id')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('funcionario_id')->nullable();
            $table->unsignedBigInteger('avaliador_id')->nullable();
            $table->unsignedBigInteger('avaliacao_tipo_id')->nullable();
            $table->string('origem_feedback')->nullable();
            $table->boolean('principal')->default(false);
            $table->integer('nota_final_total')->nullable();
            $table->string('inicio_feedback')->nullable();
            $table->string('fim_feedback')->nullable();
            $table->text('comentario')->nullable();
            $table->string('status')->default('Pendente');
            $table->string('estado_atual')->nullable();
            $table->string('estado_desejado')->nullable();
            $table->boolean('tipo_pj')->default(false);
        });

        Schema::create('avaliacoes_respostas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('avaliacao_feedback_id');
            $table->unsignedBigInteger('topico_id');
            $table->integer('nota')->nullable();
        });

        Schema::create('avaliacoes_resultados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('avaliacao_feedback_id')->nullable();
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->unsignedBigInteger('topico_id')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->text('plano_de_acao')->nullable();
            $table->string('responsavel')->nullable();
            $table->string('inicio')->nullable();
            $table->string('termino')->nullable();
            $table->string('status')->nullable();
            $table->json('dados_extras')->nullable();
            $table->boolean('tipo_pj')->default(false);
        });
    }

    protected function garantirSchemaProcessosApiExterna(): void
    {
        $this->garantirSchemaApiExterna();

        if (Schema::hasTable('feedback_curriculos')) {
            return;
        }

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->boolean('ativo')->default(true);
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social')->nullable();
            $table->string('nome')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('cpf')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
        });

        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('uf', 2)->nullable();
        });

        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('empresa_id')->nullable();
        });

        Schema::create('vagas_abertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('vaga_id')->nullable();
            $table->string('titulo')->nullable();
            $table->unsignedBigInteger('municipio_id')->nullable();
            $table->boolean('ativo')->default(true);
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('naturalidade')->nullable();
            $table->string('nacionalidade')->nullable();
            $table->string('carteira_trabalho')->nullable();
            $table->string('cnh')->nullable();
            $table->string('cnh_vencimento')->nullable();
            $table->string('sexo')->nullable();
            $table->string('cpf')->nullable();
            $table->string('rg')->nullable();
            $table->string('rg_data_emissao')->nullable();
            $table->string('orgao_expeditor')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('end_numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('municipio')->nullable();
            $table->string('uf')->nullable();
            $table->string('cep')->nullable();
            $table->string('filiacao_pai')->nullable();
            $table->string('filiacao_mae')->nullable();
            $table->boolean('pcd')->nullable();
            $table->string('nascimento')->nullable();
            $table->string('email')->nullable();
            $table->boolean('disponibilidade_sabado')->nullable();
            $table->boolean('disponibilidade_domingo')->nullable();
            $table->unsignedBigInteger('formacao')->nullable();
            $table->string('formacao_curso')->nullable();
            $table->string('formacao_status')->nullable();
            $table->unsignedBigInteger('vaga_pretendida')->nullable();
            $table->string('uf_vaga', 2)->nullable();
            $table->boolean('lido')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('escolaridades', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->nullable();
        });

        Schema::create('usuario_dependentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('nome')->nullable();
            $table->string('nascimento')->nullable();
            $table->string('tipo')->nullable();
            $table->string('outro_tipo')->nullable();
            $table->string('cpf')->nullable();
        });

        Schema::create('curriculo_experiencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id');
            $table->string('empresa')->nullable();
            $table->string('cargo')->nullable();
            $table->text('principais_atv')->nullable();
            $table->string('data_inicio')->nullable();
            $table->string('data_fim')->nullable();
            $table->string('referencia_nome')->nullable();
            $table->string('referencia_telefone')->nullable();
        });

        Schema::create('curriculo_telefone', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->string('numero')->nullable();
            $table->string('pais')->nullable();
            $table->string('tipo')->nullable();
            $table->boolean('principal')->default(false);
        });

        Schema::create('usuario_contas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('banco')->nullable();
            $table->string('agencia')->nullable();
            $table->string('conta')->nullable();
            $table->boolean('pix')->default(false);
            $table->string('tipochavepix')->nullable();
            $table->string('chavepix')->nullable();
        });

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('vaga_id')->nullable();
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->unsignedBigInteger('vaga_projeto_id')->nullable();
            $table->unsignedBigInteger('telefone_id')->nullable();
            $table->string('status')->nullable();
            $table->string('selecionado')->nullable();
            $table->boolean('interesse')->nullable();
            $table->boolean('contato_realizado')->nullable();
            $table->string('data_entrevista')->nullable();
            $table->string('local_entrevista')->nullable();
            $table->text('obs')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('resultado_integrados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->unsignedBigInteger('formulario_id')->nullable();
            $table->boolean('documentos_entregue')->nullable();
            $table->string('documentos_entregue_data')->nullable();
            $table->boolean('encaminhado_exame')->nullable();
            $table->string('encaminhado_exame_data')->nullable();
            $table->unsignedBigInteger('pcmso_id')->nullable();
            $table->unsignedBigInteger('empresa_exame_id')->nullable();
            $table->boolean('encaminhado_treinamento')->nullable();
            $table->string('encaminhado_treinamento_data')->nullable();
            $table->boolean('excessao')->nullable();
            $table->string('autorizado_por')->nullable();
            $table->string('responsavel_envio')->nullable();
            $table->text('obs')->nullable();
            $table->timestamps();
        });

        Schema::create('parecer_rh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->string('destro')->nullable();
            $table->string('cnh_tipo')->nullable();
            $table->integer('calca')->nullable();
            $table->integer('bota')->nullable();
            $table->string('camisa_meia')->nullable();
            $table->integer('camisa_protecao')->nullable();
            $table->boolean('ex_funcionario')->nullable();
            $table->boolean('turnos_seis_por_dois')->nullable();
            $table->string('indicado_por')->nullable();
            $table->timestamps();
        });

        Schema::create('parecer_entrevista_tecnica', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->string('indicado_area')->nullable();
            $table->boolean('experiencia_cargas_rigger')->nullable();
            $table->boolean('opera_plat_movel')->nullable();
            $table->boolean('opera_plat_ponte')->nullable();
            $table->timestamps();
        });

        Schema::create('parecer_rotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->boolean('tem_rota')->nullable();
            $table->string('qual')->nullable();
            $table->string('bairro_rota')->nullable();
            $table->string('ponto_referencia_rota')->nullable();
            $table->boolean('pega_onibus')->nullable();
            $table->string('pega_onibus_qual_ponto')->nullable();
            $table->string('bairro_residencia')->nullable();
            $table->string('ponto_referencia_residencia')->nullable();
            $table->timestamps();
        });

        Schema::create('parecer_teste_pratico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->string('qual_teste')->nullable();
            $table->string('nota_teste')->nullable();
            $table->timestamps();
        });

        Schema::create('admissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->string('status')->nullable();
            $table->string('data_admissao')->nullable();
            $table->string('data_adm_prevista')->nullable();
            $table->string('cargo')->nullable();
            $table->string('funcao')->nullable();
            $table->float('salario')->nullable();
            $table->string('contrato')->nullable();
            $table->string('tipo_admissao')->nullable();
            $table->unsignedBigInteger('centro_custo_id')->nullable();
            $table->unsignedBigInteger('area_etiqueta_id')->nullable();
            $table->string('matricula')->nullable();
            $table->string('numero_cracha')->nullable();
            $table->string('prazo_experiencia')->nullable();
            $table->string('pis')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('demissaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->string('data_desmobilizacao')->nullable();
            $table->timestamps();
        });

        Schema::create('examesesmts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->boolean('exame_realizado')->default(false);
            $table->boolean('atual')->default(false);
            $table->json('resultado')->nullable();
            $table->string('data_realizacao')->nullable();
            $table->string('data_vencimento')->nullable();
            $table->timestamps();
        });

        Schema::create('feedback_preadmissao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->unsignedBigInteger('user_finalizou_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ferias_adquiridas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admissao_id');
            $table->string('periodo_gozado')->nullable();
            $table->integer('qnt_dias')->nullable();
            $table->string('data_saida')->nullable();
            $table->string('data_retorno')->nullable();
            $table->string('proximo_periodo')->nullable();
            $table->string('data_limite')->nullable();
            $table->timestamps();
        });

        if (! Schema::hasTable('arquivos')) {
            Schema::create('arquivos', function (Blueprint $table) {
                $table->id();
                $table->string('nome')->nullable();
                $table->string('extensao')->nullable();
                $table->unsignedBigInteger('bytes')->nullable();
                $table->string('file')->nullable();
                $table->string('disco')->nullable();
                $table->timestamps();
            });
        }

        Schema::create('documentos_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id');
            $table->unsignedBigInteger('arquivo_id');
            $table->string('tipo')->nullable();
        });
    }

    protected function garantirSchemaModulosExtrasApiExterna(): void
    {
        $this->garantirSchemaProcessosApiExterna();

        if (! Schema::hasColumn('vagas_abertas', 'descricao')) {
            Schema::table('vagas_abertas', function (Blueprint $table) {
                $table->text('descricao')->nullable();
                $table->boolean('ativo_sistema')->default(true);
            });
        }

        if (! Schema::hasTable('simulado_vagas')) {
            Schema::create('simulado_vagas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('simulado_id')->nullable();
                $table->unsignedBigInteger('vaga_id')->nullable();
                $table->unsignedBigInteger('vagas_abertas_id')->nullable();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->string('data_inicio')->nullable();
                $table->string('data_fim')->nullable();
                $table->integer('duracao')->nullable();
                $table->boolean('online')->default(false);
                $table->boolean('ativo')->default(true);
            });
        }

        if (! Schema::hasTable('projetos')) {
            Schema::create('projetos', function (Blueprint $table) {
                $table->id();
                $table->string('nome')->nullable();
                $table->unsignedBigInteger('empresa_id')->nullable();
            });
        }

        if (! Schema::hasTable('vaga_projetos')) {
            Schema::create('vaga_projetos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('projeto_id')->nullable();
                $table->unsignedBigInteger('vaga_aberta_id')->nullable();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->integer('qnt_total')->nullable();
                $table->integer('qnt_preenchida')->nullable();
            });
        }

        if (! Schema::hasTable('centro_custos')) {
            Schema::create('centro_custos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id');
                $table->string('label')->nullable();
                $table->boolean('ativo')->default(true);
                $table->unsignedBigInteger('gestor_id')->nullable();
            });
        }

        if (! Schema::hasTable('centro_custo_filials')) {
            Schema::create('centro_custo_filials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->unsignedBigInteger('centro_custo_id');
                $table->unsignedBigInteger('cliente_filial_id')->nullable();
                $table->boolean('ativo')->default(true);
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('cliente_filials')) {
            Schema::create('cliente_filials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id');
                $table->json('dados');
                $table->boolean('ativo')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('centro_custo_gestores')) {
            Schema::create('centro_custo_gestores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('centro_custo_id');
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('tipo')->nullable();
                $table->boolean('ativo')->default(true);
                $table->unsignedBigInteger('empresa_id')->nullable();
            });
        }

        if (! Schema::hasTable('cih_tags')) {
            Schema::create('cih_tags', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->string('label');
                $table->boolean('ativo')->default(true);
            });
        }

        if (! Schema::hasTable('cihs')) {
            Schema::create('cihs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id');
                $table->unsignedBigInteger('tag_id')->nullable();
                $table->unsignedBigInteger('gestor_id')->nullable();
                $table->unsignedBigInteger('area_id')->nullable();
                $table->unsignedBigInteger('centro_custo_id')->nullable();
                $table->unsignedBigInteger('cliente_id')->nullable();
                $table->unsignedBigInteger('feedback_id')->nullable();
                $table->string('status')->nullable();
                $table->string('acao')->nullable();
                $table->string('data_lancamento')->nullable();
                $table->string('data_aprovacao')->nullable();
                $table->string('data_aprovacao_rh')->nullable();
                $table->text('obs_lancamento')->nullable();
                $table->text('obs_aprovacao')->nullable();
                $table->text('obs_rh')->nullable();
                $table->string('resposta_rh')->nullable();
                $table->boolean('varios_colaboradores')->default(false);
                $table->string('colaboradores_avulso')->nullable();
                $table->string('outra_tag')->nullable();
                $table->string('outra_area')->nullable();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('cih_feedback')) {
            Schema::create('cih_feedback', function (Blueprint $table) {
                $table->unsignedBigInteger('cih_id');
                $table->unsignedBigInteger('feedback_id');
            });
        }

        if (! Schema::hasTable('medida_administrativas')) {
            Schema::create('medida_administrativas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('feedback_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('solicitante')->nullable();
                $table->string('tipo')->nullable();
                $table->string('definicao')->nullable();
                $table->text('motivo')->nullable();
                $table->string('causa')->nullable();
                $table->string('data_solicitacao')->nullable();
                $table->string('data_retorno')->nullable();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('afastamentos')) {
            Schema::create('afastamentos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('feedback_id');
                $table->string('motivo')->nullable();
                $table->string('data_inicio')->nullable();
                $table->string('data_fim')->nullable();
                $table->text('observacao')->nullable();
            });
        }

        if (! Schema::hasTable('entrevista_desligamentos')) {
            Schema::create('entrevista_desligamentos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('feedback_id');
                $table->string('motivo')->nullable();
                $table->string('trabalharia_novamente')->nullable();
                $table->boolean('pode_voltar')->nullable();
                $table->string('quem_entrevistou')->nullable();
                $table->string('preenchido_por')->nullable();
                $table->string('data_entrevista')->nullable();
                $table->text('parecer_entrevistador')->nullable();
                $table->text('comentarios')->nullable();
                $table->string('salario_beneficio')->nullable();
            });
        }

        if (! Schema::hasColumn('admissoes', 'centro_custo_id')) {
            // já existe no schema processos
        }
    }

    /**
     * @return array{client_id: string, client_secret: string, empresa_id: int, user_id: int, scopes: array}
     */
    protected function criarCredencialApiExterna(
        int $empresaId = 91001,
        ?array $scopes = null
    ): array {
        return app(ApiExternaCredencialService::class)->criarOuRotacionar(
            $empresaId,
            false,
            $scopes ?: ApiExternaScopes::all()
        );
    }

    protected function bearerToken(string $clientId, string $clientSecret): string
    {
        $response = $this->postJson('/api/v3/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        $response->assertOk();

        return (string) $response->json('access_token');
    }
}
