<?php

namespace Database\Seeders;

use App\Models\Habilidade;
use DB;
use Exception;
use Illuminate\Database\Seeder;

class HabilidadesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Habilidades------------------------------
        $lista[] = ['nome' => 'configuracao_habilidades', 'descricao' => 'Acessar rota/menu habilidades'];
        $lista[] = ['nome' => 'configuracao_habilidades_insert', 'descricao' => 'Pode inserir uma nova habilidade'];
        $lista[] = ['nome' => 'configuracao_habilidades_update', 'descricao' => 'Pode alterar uma habilidade'];
        $lista[] = ['nome' => 'configuracao_habilidades_delete', 'descricao' => 'Pode apagar uma habilidade'];

        //Papeis------------------------------
        $lista[] = ['nome' => 'configuracao_papel', 'descricao' => 'Acessar rota/menu papeis'];
        $lista[] = ['nome' => 'configuracao_papel_insert', 'descricao' => 'Pode cadastrar um papel'];
        $lista[] = ['nome' => 'configuracao_papel_update', 'descricao' => 'Pode alterar um papel'];
        $lista[] = ['nome' => 'configuracao_papel_delete', 'descricao' => 'Pode apagar um papel'];

        //Usuários------------------------------
        $lista[] = ['nome' => 'usuario_usuarios', 'descricao' => 'Acessar rota/menu usuários'];
        $lista[] = ['nome' => 'usuario_usuarios_insert', 'descricao' => 'Pode cadastrar um usuário'];
        $lista[] = ['nome' => 'usuario_usuarios_update', 'descricao' => 'Pode alterar um usuário'];
        $lista[] = ['nome' => 'usuario_usuarios_delete', 'descricao' => 'Pode apagar um usuário'];
        $lista[] = ['nome' => 'usuario_alterar-senha', 'descricao' => 'Pode alterar sua propria senha de acesso ao sistema'];

        //Clientes------------------------------
        $lista[] = ['nome' => 'administracao_clientes', 'descricao' => 'Acessar rota/menu clientes'];
        $lista[] = ['nome' => 'administracao_clientes_insert', 'descricao' => 'Pode cadastrar um cliente'];
        $lista[] = ['nome' => 'administracao_clientes_update', 'descricao' => 'Pode alterar um cliente'];
        $lista[] = ['nome' => 'administracao_clientes_delete', 'descricao' => 'Pode apagar um cliente'];

        //Documentos Legais------------------------------
        $lista[] = ['nome' => 'administracao_documentos_legais', 'descricao' => 'Acessar rota/menu documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_insert', 'descricao' => 'Pode cadastrar um documento legal'];
        $lista[] = ['nome' => 'administracao_documentos_legais_update', 'descricao' => 'Pode alterar um documento legal'];
        $lista[] = ['nome' => 'administracao_documentos_legais_delete', 'descricao' => 'Pode apagar um documento legal'];

        //Documentos Legais Contrato ------------------------------
        $lista[] = ['nome' => 'administracao_documentos_legais_contrato', 'descricao' => 'Acessar rota/menu contrato de documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_contrato_insert', 'descricao' => 'Pode cadastrar contratos em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_contrato_update', 'descricao' => 'Pode alterar contratos em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_contrato_delete', 'descricao' => 'Pode apagar contratos em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_contrato_show', 'descricao' => 'Pode visualizar contratos em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_contrato_pdf', 'descricao' => 'Exportar contrato em pdf em documentos legais'];

        //Documentos Legais Documentos Empresa ------------------------------
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_empresa', 'descricao' => 'Acessar rota/menu documentos empresa de documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_empresa_insert', 'descricao' => 'Pode cadastrar documentos empresa em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_empresa_update', 'descricao' => 'Pode alterar documentos empresa em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_empresa_delete', 'descricao' => 'Pode apagar documentos empresa em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_empresa_show', 'descricao' => 'Pode visualizar documentos empresa em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_empresa_pdf', 'descricao' => 'Exportar documentos empresa em pdf em documentos legais'];

        //Documentos Legais Documentos SSMA ------------------------------
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_ssma', 'descricao' => 'Acessar rota/menu documentos ssma de documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_ssma_insert', 'descricao' => 'Pode cadastrar documentos ssma em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_ssma_update', 'descricao' => 'Pode alterar documentos ssma em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_ssma_delete', 'descricao' => 'Pode apagar documentos ssma em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_ssma_show', 'descricao' => 'Pode visualizar documentos ssma em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_documentos_ssma_pdf', 'descricao' => 'Exportar documentos ssma em pdf em documentos legais'];

        //Documentos Legais Tipos Documentos ------------------------------
        $lista[] = ['nome' => 'administracao_documentos_legais_tipos_documentos', 'descricao' => 'Acessar rota/menu tipos documentos de documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_tipos_documentos_insert', 'descricao' => 'Pode cadastrar tipos documentos em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_tipos_documentos_update', 'descricao' => 'Pode alterar tipos documentos em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_tipos_documentos_delete', 'descricao' => 'Pode apagar tipos documentos em documentos legais'];

        //Documentos Legais Tipos Serviços ------------------------------
        $lista[] = ['nome' => 'administracao_documentos_legais_tipos_servicos', 'descricao' => 'Acessar rota/menu tipos serviços de documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_tipos_servicos_insert', 'descricao' => 'Pode cadastrar tipos serviços em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_tipos_servicos_update', 'descricao' => 'Pode alterar tipos serviços em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_tipos_servicos_delete', 'descricao' => 'Pode apagar tipos serviços em documentos legais'];

        //Documentos Legais Formas Contratos ------------------------------
        $lista[] = ['nome' => 'administracao_documentos_legais_formas_contratos', 'descricao' => 'Acessar rota/menu formas contratos de documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_formas_contratos_insert', 'descricao' => 'Pode cadastrar formas contratos em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_formas_contratos_update', 'descricao' => 'Pode alterar formas contratos em documentos legais'];
        $lista[] = ['nome' => 'administracao_documentos_legais_formas_contratos_delete', 'descricao' => 'Pode apagar formas contratos em documentos legais'];

        //Curriculos Recrutamento------------------------------
        $lista[] = ['nome' => 'curriculos_recrutamento', 'descricao' => 'Acessar rota/menu curriculos recrutamento'];
        $lista[] = ['nome' => 'curriculos_recrutamento_insert', 'descricao' => 'Pode cadastrar um curriculo recrutamento'];
        $lista[] = ['nome' => 'curriculos_recrutamento_update', 'descricao' => 'Pode alterar um curriculo recrutamento'];
        $lista[] = ['nome' => 'curriculos_recrutamento_delete', 'descricao' => 'Pode apagar um curriculo recrutamento'];

        $lista[] = ['nome' => 'curriculos_selecionados', 'descricao' => 'Pode acessar menu curriculo'];

        //FeedBack Curriculos------------------------------
        $lista[] = ['nome' => 'feedback_curriculos', 'descricao' => 'Acessar rota/menu Feedback Curriculos'];
        $lista[] = ['nome' => 'feedback_curriculos_insert', 'descricao' => 'Pode cadastrar um Feedback Curriculo'];
        $lista[] = ['nome' => 'feedback_curriculos_update', 'descricao' => 'Pode alterar um Feedback Curriculo'];
        $lista[] = ['nome' => 'feedback_curriculos_delete', 'descricao' => 'Pode apagar um Feedback Curriculo'];

        //ParecerRH------------------------------
        $lista[] = ['nome' => 'entrevista_parecer_rh', 'descricao' => 'Acessar rota/menu Parecer Rh'];
        $lista[] = ['nome' => 'entrevista_parecer_rh_insert', 'descricao' => 'Pode cadastrar um Parecer Rh'];
        $lista[] = ['nome' => 'entrevista_parecer_rh_update', 'descricao' => 'Pode alterar um Parecer Rh'];
        $lista[] = ['nome' => 'entrevista_parecer_rh_delete', 'descricao' => 'Pode apagar um Parecer Rh'];
//@can
        //ParecerRota------------------------------
        $lista[] = ['nome' => 'entrevista_parecer_rota', 'descricao' => 'Acessar rota/menu Parecer Rota Transporte'];
        $lista[] = ['nome' => 'entrevista_parecer_rota_insert', 'descricao' => 'Pode cadastrar um Parecer Rota Transporte'];
        $lista[] = ['nome' => 'entrevista_parecer_rota_update', 'descricao' => 'Pode alterar um Parecer Rota Transporte'];
        $lista[] = ['nome' => 'entrevista_parecer_rota_delete', 'descricao' => 'Pode apagar um Parecer Rota Transporte'];

        //ParecerTestePratico------------------------------
        $lista[] = ['nome' => 'entrevista_parecer_teste_pratico', 'descricao' => 'Acessar rota/menu Parecer Teste Prático'];
        $lista[] = ['nome' => 'entrevista_parecer_teste_pratico_insert', 'descricao' => 'Pode cadastrar um Parecer Teste Prático'];
        $lista[] = ['nome' => 'entrevista_parecer_teste_pratico_update', 'descricao' => 'Pode alterar um Parecer Teste Prático'];
        $lista[] = ['nome' => 'entrevista_parecer_teste_pratico_delete', 'descricao' => 'Pode apagar um Parecer Teste Prático'];

        //Pa`recerEntrevista------------------------------
        $lista[] = ['nome' => 'entrevista_parecer_entrevista', 'descricao' => 'Acessar rota/menu Parecer Entrevistas Técnicas'];
        $lista[] = ['nome' => 'entrevista_parecer_entrevista_insert', 'descricao' => 'Pode cadastrar um Parecer Entrevista Técnica'];
        $lista[] = ['nome' => 'entrevista_parecer_entrevista_update', 'descricao' => 'Pode alterar um Parecer Entrevista Técnica'];
        $lista[] = ['nome' => 'entrevista_parecer_entrevista_delete', 'descricao' => 'Pode apagar um Parecer Entrevista Técnica'];

        //Resultado Integrado------------------------------
        $lista[] = ['nome' => 'entrevista_resultado_integrado', 'descricao' => 'Acessar rota/menu Resultados Integrados'];
        $lista[] = ['nome' => 'entrevista_resultado_integrado_insert', 'descricao' => 'Pode cadastrar um Resultado'];
        $lista[] = ['nome' => 'entrevista_resultado_integrado_update', 'descricao' => 'Pode alterar um Resultado'];
        $lista[] = ['nome' => 'entrevista_resultado_integrado_delete', 'descricao' => 'Pode apagar um Resultado'];

        //Admissão------------------------------
        $lista[] = ['nome' => 'admissao_processo', 'descricao' => 'Acessar rota/menu Admissões'];
        $lista[] = ['nome' => 'admissao_processo_insert', 'descricao' => 'Pode cadastrar um Admissão'];
        $lista[] = ['nome' => 'admissao_processo_update', 'descricao' => 'Pode alterar um Admissão'];
        $lista[] = ['nome' => 'admissao_processo_delete', 'descricao' => 'Pode apagar um Admissão'];


        //Cloud------------------------------
        $lista[] = ['nome' => 'cloud', 'descricao' => 'Acessar rota/menu Cloud'];
        $lista[] = ['nome' => 'cloud_insert', 'descricao' => 'Pode cadastrar um Cloud'];
        $lista[] = ['nome' => 'cloud_update', 'descricao' => 'Pode alterar um Cloud'];
        $lista[] = ['nome' => 'cloud_delete', 'descricao' => 'Pode apagar um Cloud'];

        //Cloud Cadastro------------------------------
        $lista[] = ['nome' => 'cloud_cadastro', 'descricao' => 'Acessar rota/menu Cloud Cadastro'];
        $lista[] = ['nome' => 'cloud_cadastro_insert', 'descricao' => 'Pode cadastrar um Cloud'];
        $lista[] = ['nome' => 'cloud_cadastro_update', 'descricao' => 'Pode alterar ativar e desativar um Cloud'];
        $lista[] = ['nome' => 'cloud_cadastro_delete', 'descricao' => 'Pode apagar um Cloud '];

        //Cloud------------------------------
        $lista[] = ['nome' => 'cloud_configuracoes', 'descricao' => 'Acessar rota/menu Cloud Configurações'];
        $lista[] = ['nome' => 'cloud_configuracoes_insert', 'descricao' => 'Pode cadastrar um Cloud Configurações'];
        $lista[] = ['nome' => 'cloud_configuracoes_update', 'descricao' => 'Pode alterar um Cloud Configurações'];
        $lista[] = ['nome' => 'cloud_configuracoes_delete', 'descricao' => 'Pode apagar um Cloud Configurações'];

        //Galeria------------------------------
        $lista[] = ['nome' => 'site_galeria_site', 'descricao' => 'Acessar rota/menu Site - Galeria'];
        $lista[] = ['nome' => 'site_galeria_site_insert', 'descricao' => 'Pode cadastrar uma Galeria'];
        $lista[] = ['nome' => 'site_galeria_site_update', 'descricao' => 'Pode alterar uma Galeria'];
        $lista[] = ['nome' => 'site_galeria_site_delete', 'descricao' => 'Pode apagar uma Galeria'];

        //Cartela Cliente------------------------------
        $lista[] = ['nome' => 'site_cartela_cliente_site', 'descricao' => 'Acessar rota/menu Site - Cartela Cliente'];
        $lista[] = ['nome' => 'site_cartela_cliente_site_insert', 'descricao' => 'Pode cadastrar uma Cartela Cliente'];
        $lista[] = ['nome' => 'site_cartela_cliente_site_update', 'descricao' => 'Pode alterar uma Cartela Cliente'];
        $lista[] = ['nome' => 'site_cartela_cliente_site_delete', 'descricao' => 'Pode apagar uma Cartela Cliente'];

        //DEPOIMENTOS------------------------------
        $lista[] = ['nome' => 'site_depoimento_site', 'descricao' => 'Acessar rota/menu Site - Depoimento'];
        $lista[] = ['nome' => 'site_depoimento_site_insert', 'descricao' => 'Pode cadastrar um Depoimento'];
        $lista[] = ['nome' => 'site_depoimento_site_update', 'descricao' => 'Pode alterar um Depoimento'];
        $lista[] = ['nome' => 'site_depoimento_site_delete', 'descricao' => 'Pode apagar um Depoimento'];


        //Vagas------------------------------
        $lista[] = ['nome' => 'cadastro_vagas', 'descricao' => 'Acessar rota/menu Vagas'];
        $lista[] = ['nome' => 'cadastro_vagas_insert', 'descricao' => 'Pode cadastrar uma vaga'];
        $lista[] = ['nome' => 'cadastro_vagas_update', 'descricao' => 'Pode alterar uma vaga'];


        //Vagas Abertas------------------------------
        $lista[] = ['nome' => 'cadastro_vagas_abertas', 'descricao' => 'Acessar rota/menu Vagas Abertas'];
        $lista[] = ['nome' => 'cadastro_vagas_abertas_insert', 'descricao' => 'Pode cadastrar uma vaga aberta'];
        $lista[] = ['nome' => 'cadastro_vagas_abertas_update', 'descricao' => 'Pode alterar uma vaga aberta'];


        //TREINAMENTOS------------------------------
        $lista[] = ['nome' => 'treinamento_carteira-etiquetas', 'descricao' => 'Acessar rota/menu Treinamentos'];
        $lista[] = ['nome' => 'treinamento_carteira-etiquetas_insert', 'descricao' => 'Pode cadastrar um treinamento'];
        $lista[] = ['nome' => 'treinamento_carteira-etiquetas_update', 'descricao' => 'Pode alterar um treinamento'];
        $lista[] = ['nome' => 'treinamento_envio', 'descricao' => 'Pode enviar por email ou imprimir'];


        //Fornecedores------------------------------
        $lista[] = ['nome' => 'administracao_fornecedores', 'descricao' => 'Acessar rota/menu Fornecedores'];
        $lista[] = ['nome' => 'administracao_fornecedores_insert', 'descricao' => 'Pode cadastrar um Fornecedor'];
        $lista[] = ['nome' => 'administracao_fornecedores_update', 'descricao' => 'Pode alterar um Fornecedor'];
        $lista[] = ['nome' => 'administracao_fornecedores_delete', 'descricao' => 'Pode apagar um Fornecedor'];


        //Portaria------------------------------
        $lista[] = ['nome' => 'treinamento_portaria', 'descricao' => 'Acessar rota/menu Portaria'];
        $lista[] = ['nome' => 'treinamento_portaria_insert', 'descricao' => 'Pode cadastrar um Portaria'];
        $lista[] = ['nome' => 'treinamento_portaria_update', 'descricao' => 'Pode alterar um Portaria'];

        //PÓSADMISSAO------------------------------
        $lista[] = ['nome' => 'admissao_pos_admissao', 'descricao' => 'Acessar rota/menu Pós-Admissão'];
        $lista[] = ['nome' => 'admissao_pos_admissao_insert', 'descricao' => 'Pode cadastrar um Pós-Admissão'];
        $lista[] = ['nome' => 'admissao_pos_admissao_update', 'descricao' => 'Pode alterar um Pós-Admissão'];


        //Certificado------------------------------
        $lista[] = ['nome' => 'treinamento_certificado', 'descricao' => 'Acessar rota/menu Certificado'];
        $lista[] = ['nome' => 'treinamento_certificado_insert', 'descricao' => 'Pode cadastrar um Certificado'];
        $lista[] = ['nome' => 'treinamento_certificado_update', 'descricao' => 'Pode alterar um Certificado'];


        //PosAdmissoes_form_rh------------------------------
        $lista[] = ['nome' => 'posadmissao_form_rh', 'descricao' => 'Acessar formulario pos-admissao rh'];
        //PosAdmissoes_form_adm------------------------------
        $lista[] = ['nome' => 'posadmissao_form_adm', 'descricao' => 'Acessar formulario pos-admissao adm'];
        //PosAdmissoes_form_ssma------------------------------
        $lista[] = ['nome' => 'posadmissao_form_ssma', 'descricao' => 'Acessar formulario pos-admissao ssma'];


        $lista[] = ['nome' => 'posadmissao_avaliar', 'descricao' => 'Acessar formulario avaliar'];
        $lista[] = ['nome' => 'posadmissao_avaliar_insert', 'descricao' => 'Pode cadastrar avaliação'];
        $lista[] = ['nome' => 'posadmissao_avaliar_update', 'descricao' => 'Pode alterar avaliação'];

        $lista[] = ['nome' => 'posadmissao_desmobilizar', 'descricao' => 'Acessar formulario desmobilizar'];
        $lista[] = ['nome' => 'posadmissao_desmobilizar_insert', 'descricao' => 'Pode cadastrar desmobilização'];
        $lista[] = ['nome' => 'posadmissao_desmobilizar_update', 'descricao' => 'Pode alterar desmobilização'];
        $lista[] = ['nome' => 'posadmissao_entrevista_desligamento', 'descricao' => 'Acessar formulario de entrevista desligamento'];
        $lista[] = ['nome' => 'posadmissao_entrevista_desligamento_insert', 'descricao' => 'Pode cadastrar entrevista desligamento'];
        $lista[] = ['nome' => 'posadmissao_entrevista_desligamento_update', 'descricao' => 'Pode alterar entrevista desligamento'];


        $lista[] = ['nome' => 'entrevista_rh_cliente', 'descricao' => 'Acessar menu EntrevistaRH cliente'];
        $lista[] = ['nome' => 'entrevista_rh_cliente_insert', 'descricao' => 'Pode cadastrar EntrevistaRH cliente'];
        $lista[] = ['nome' => 'entrevista_rh_cliente_update', 'descricao' => 'Pode alterar EntrevistaRH cliente'];

        $lista[] = ['nome' => 'entrevista_gestor_cliente', 'descricao' => 'Acessar menu EntrevistaGestor cliente'];
        $lista[] = ['nome' => 'entrevista_gestor_cliente_insert', 'descricao' => 'Pode cadastrar EntrevistaGestor cliente'];
        $lista[] = ['nome' => 'entrevista_gestor_cliente_update', 'descricao' => 'Pode alterar EntrevistaGestor cliente'];

        $lista[] = ['nome' => 'admissao_cih', 'descricao' => 'Acessar menu Apontamento - CIH'];
        $lista[] = ['nome' => 'admissao_cih_lancar', 'descricao' => 'Pode lançar uma ocorrencia CIH'];
        $lista[] = ['nome' => 'admissao_cih_aprovar', 'descricao' => 'Pode aprovar uma ocorrencia CIH'];
        $lista[] = ['nome' => 'admissao_cih_privilegio_adm', 'descricao' => 'Pode visualizar todas as CIH'];


        $lista[] = ['nome' => 'admissao_historico', 'descricao' => 'Acessar menu Historico'];
        $lista[] = ['nome' => 'admissao_historico_aba_medidas_administrativas', 'descricao' => 'Pode ver o Componente Medidas Administrativa'];

        $lista[] = ['nome' => 'ocorrencia', 'descricao' => 'Acessar menu Ocorrencia'];
        $lista[] = ['nome' => 'ocorrencia_tag', 'descricao' => 'Acessar Ocorrencia Tag'];
        $lista[] = ['nome' => 'ocorrencia_setor', 'descricao' => 'Acessar Ocorrencia Setor'];

        $lista[] = ['nome' => 'admissao_historico_aba_avaliacao_noventa_dias', 'descricao' => 'Acessar Avaliação'];
        $lista[] = ['nome' => 'avaliacao_noventa_insert', 'descricao' => 'Inserir Avaliação'];

        $lista[] = ['nome' => 'cadastro_beneficio', 'descricao' => 'Acessar Beneficio'];
        $lista[] = ['nome' => 'cadastro_beneficio_insert', 'descricao' => 'Acessar Beneficio Insert'];
        $lista[] = ['nome' => 'cadastro_beneficio_update', 'descricao' => 'Acessar Beneficio Update'];

        $lista[] = ['nome' => 'treinamento_lista_presenca', 'descricao' => 'Acessar Treinamento Lista de Presença'];
        $lista[] = ['nome' => 'treinamento_lista_presenca_insert', 'descricao' => 'Inseri uma lista de presença'];
        $lista[] = ['nome' => 'treinamento_lista_presenca_update', 'descricao' => 'Atualiza lista de presença'];

        $lista[] = ['nome' => 'administracao_atareuniao', 'descricao' => 'Acessar Ata Reunião'];
        $lista[] = ['nome' => 'administracao_atareuniao_insert', 'descricao' => 'Acessar Ata Reunião Insert'];
        $lista[] = ['nome' => 'administracao_atareuniao_update', 'descricao' => 'Acessar Ata Reunião Update'];
        $lista[] = ['nome' => 'administracao_atareuniao_privilegio_adm', 'descricao' => 'Pode visualizar todas as Atas de Reunião'];

        $lista[] = ['nome' => 'admissao_pre_admissao', 'descricao' => 'Acessar Menu Pré Admissão'];

        $lista[] = ['nome' => 'admissao_historico_aba_dossie', 'descricao' => 'Acessar Dossie'];
        $lista[] = ['nome' => 'historico_dossie_insert', 'descricao' => 'Acessar Dossie Insert'];
        $lista[] = ['nome' => 'historico_dossie_update', 'descricao' => 'Acessar Dossie Update'];

        $lista[] = ['nome' => 'admissao_historico_aba_feedback', 'descricao' => 'Pode Acessar aba de Feedback no historico'];
        $lista[] = ['nome' => 'admissao_historico_aba_avaliacao_anual', 'descricao' => 'Pode Acessar aba de Avaliacao Anual no historico'];
        $lista[] = ['nome' => 'admissao_historico_aba_ferias', 'descricao' => 'Pode Acessar aba de Ferias no historico'];
        $lista[] = ['nome' => 'admissao_historico_aba_promocao', 'descricao' => 'Pode Acessar aba de Promocao no historico'];
        $lista[] = ['nome' => 'admissao_historico_aba_metas', 'descricao' => 'Pode Acessar aba de Metas no historico'];
        $lista[] = ['nome' => 'admissao_historico_aba_log', 'descricao' => 'Pode Acessar aba de Logs no historico'];
        $lista[] = ['nome' => 'admissao_historico_aba_afastamento', 'descricao' => 'Pode Acessar aba de Afastamento no historico'];
        $lista[] = ['nome' => 'admissao_historico_filtrar_demitido', 'descricao' => 'Acessar Usuarios Demitidos'];

        $lista[] = ['nome' => 'administracao_pesquisaclima', 'descricao' => 'Acessar Pesquisa Clima'];
        $lista[] = ['nome' => 'administracao_pesquisaclima_insert', 'descricao' => 'Acessar Pesquisa Clima Insert'];
        $lista[] = ['nome' => 'administracao_pesquisaclima_update', 'descricao' => 'Acessar Pesquisa Clima Update'];

        $lista[] = ['nome' => 'admissao_intermitente', 'descricao' => 'Acessar Intermitentes'];

        $lista[] = ['nome' => 'administracao_planejamentodiario', 'descricao' => 'Acessar Planejamento Diário'];

//        Categoria planos de conta
        $lista[] = ['nome' => 'financeiro_classificacao-plano-conta', 'descricao' => 'Acessar rota/menu Classificação de planos de conta'];
        $lista[] = ['nome' => 'financeiro_classificacao-plano-conta_insert', 'descricao' => 'Pode cadastrar uma nova classificação de planos de conta'];
        $lista[] = ['nome' => 'financeiro_classificacao-plano-conta_update', 'descricao' => 'Pode alterar uma classificação de planos de conta'];
        $lista[] = ['nome' => 'financeiro_classificacao-plano-conta_delete', 'descricao' => 'Pode excluir uma classificação de planos de conta'];
        // planos de conta
        $lista[] = ['nome' => 'financeiro_plano-conta', 'descricao' => 'Acessar rota/menu planos de conta'];
        $lista[] = ['nome' => 'financeiro_plano-conta_insert', 'descricao' => 'Pode cadastrar um novo plano de conta'];
        $lista[] = ['nome' => 'financeiro_plano-conta_update', 'descricao' => 'Pode alterar um plano de conta'];
        $lista[] = ['nome' => 'financeiro_plano-conta_delete', 'descricao' => 'Pode excluir um plano de conta'];
        //Formas de pagamento
        $lista[] = ['nome' => 'financeiro_formas-pagamento', 'descricao' => 'Acessar rota/menu formas de pagamento'];
        $lista[] = ['nome' => 'financeiro_formas-pagamento_insert', 'descricao' => 'Pode cadastrar uma nova forma de pagamento'];
        $lista[] = ['nome' => 'financeiro_formas-pagamento_update', 'descricao' => 'Pode alterar uma nova forma de pagamento'];
        $lista[] = ['nome' => 'financeiro_formas-pagamento_delete', 'descricao' => 'Pode excluir uma nova forma de pagamento'];
        //Fluxo de caixa
        $lista[] = ['nome' => 'financeiro_fluxo-caixa', 'descricao' => 'Acessar rota/menu fluxo de caixa'];
        $lista[] = ['nome' => 'financeiro_fluxo-caixa_insert', 'descricao' => 'Pode cadastrar um novo lançamento'];
        $lista[] = ['nome' => 'financeiro_fluxo-caixa_update', 'descricao' => 'Pode alterar um lançamento'];
        $lista[] = ['nome' => 'financeiro_fluxo-caixa_delete', 'descricao' => 'Pode excluir um lançamento'];

        //Migração
        $lista[] = ['nome' => 'cadastro_instrutor', 'descricao' => 'Acessar Cadastro Instrutor'];
        $lista[] = ['nome' => 'cadastro_instrutor_insert', 'descricao' => 'Inseri Cadastro Instrutor Insert'];
        $lista[] = ['nome' => 'cadastro_instrutor_update', 'descricao' => 'Atualiza Cadastro Instrutor Update'];

        $lista[] = ['nome' => 'cadastro_departamento', 'descricao' => 'Acessar Cadastro Departamento'];
        $lista[] = ['nome' => 'cadastro_departamento_insert', 'descricao' => 'Inseri Cadastro Departamento Insert'];
        $lista[] = ['nome' => 'cadastro_departamento_update', 'descricao' => 'Atualiza Cadastro Departamento Update'];

        $lista[] = ['nome' => 'cadastro_treinamento_industria', 'descricao' => 'Acessar Cadastro Treinamento Industria'];
        $lista[] = ['nome' => 'cadastro_treinamento_industria_insert', 'descricao' => 'Inseri Cadastro Treinamento Industria Insert'];
        $lista[] = ['nome' => 'cadastro_treinamento_industria_update', 'descricao' => 'Atualiza Cadastro Treinamento Industria Update'];

        $lista[] = ['nome' => 'cadastro_treinamento_sgi', 'descricao' => 'Acessar Cadastro Treinamento SGI'];
        $lista[] = ['nome' => 'cadastro_treinamento_sgi_insert', 'descricao' => 'Inseri Cadastro Treinamento SGI Insert'];
        $lista[] = ['nome' => 'cadastro_treinamento_sgi_update', 'descricao' => 'Atualiza Cadastro Treinamento SGI Update'];
        $lista[] = ['nome' => 'cadastro_carteira_assinatura', 'descricao' => 'Cadastro Assinatura Carteira'];

        $lista[] = ['nome' => 'cadastro_empresa_treinamento', 'descricao' => 'Acessar Cadastro Empresa Treinamento'];
        $lista[] = ['nome' => 'cadastro_empresa_treinamento_insert', 'descricao' => 'Inseri Cadastro Empresa Treinamento Insert'];
        $lista[] = ['nome' => 'cadastro_empresa_treinamento_update', 'descricao' => 'Atualiza Cadastro Empresa Treinamento Update'];

        $lista[] = ['nome' => 'cadastro_provas', 'descricao' => 'Acessar Cadastro Provas'];
        $lista[] = ['nome' => 'cadastro_provas_insert', 'descricao' => 'Inseri Cadastro Provas Insert'];
        $lista[] = ['nome' => 'cadastro_provas_update', 'descricao' => 'Atualiza Cadastro Provas Update'];

        $lista[] = ['nome' => 'cadastro_tipos_cih', 'descricao' => 'Pode cadastrar um tipo de CIH'];

        $lista[] = ['nome' => 'administracao_aniversariantes', 'descricao' => 'Acessar Aniversariantes'];
        $lista[] = ['nome' => 'administracao_aniversariantes_enviar', 'descricao' => 'Enviar Email Para os Aniversariantes'];

        //Relatorios
        $lista[] = ['nome' => 'relatorio_relatorios', 'descricao' => 'Acessar Relatórios'];
        $lista[] = ['nome' => 'relatorio_controleusuarios', 'descricao' => 'Acessar Controle de Usuarios'];
        $lista[] = ['nome' => 'relatorio_asos', 'descricao' => 'Acessar Relatorio Aso'];
        $lista[] = ['nome' => 'relatorio_medidas_administrativas', 'descricao' => 'Acessar Relatorio Medidas Administrativas'];
        $lista[] = ['nome' => 'relatorio_treinamento', 'descricao' => 'Acessar Relatorio de Treinamentos'];
        $lista[] = ['nome' => 'relatorio_ferias', 'descricao' => 'Acessar Relatorio de Férias'];
        $lista[] = ['nome' => 'relatorio_centro_de_custo', 'descricao' => 'Acessar Relatorio de Centro de Custo'];
        $lista[] = ['nome' => 'relatorio_aniversariantes', 'descricao' => 'Acessar Relatórios de Aniversariantes'];
        $lista[] = ['nome' => 'relatorio_avaliacao_90_dias', 'descricao' => 'Pode acessar o relatório de avaliação de 90 dias'];

        $lista[] = ['nome' => 'planejamento_requisicao_vaga', 'descricao' => 'Acessa menu Requisição de Vaga dentro do menu Planejamento'];
        $lista[] = ['nome' => 'planejamento_requisicao_vaga_insert', 'descricao' => 'Inseri'];

        //weekly-report
        $lista[] = ['nome' => 'weekly_report', 'descricao' => 'Acessar rota Weekley report'];
        $lista[] = ['nome' => 'weekly_report_quadro_insert', 'descricao' => 'Pode criar um novo quadro no Weekly report'];
        $lista[] = ['nome' => 'weekly_report_quadro_update', 'descricao' => 'Pode alterar um quadro no Weekly report'];
        $lista[] = ['nome' => 'weekly_report_quadro_delete', 'descricao' => 'Pode excluir um quadro no Weekly report'];

        $lista[] = ['nome' => 'weekly_report_quadro_lista_insert', 'descricao' => 'Pode criar uma nova lista de tarefas em um quadro no Weekly report'];
        $lista[] = ['nome' => 'weekly_report_quadro_lista_update', 'descricao' => 'Pode editar uma lista de tarefas em um quadro no Weekly report'];
        $lista[] = ['nome' => 'weekly_report_quadro_lista_delete', 'descricao' => 'Pode excluir uma lista de tarefas em um quadro no Weekly report'];

        $lista[] = ['nome' => 'weekly_report_quadro_tarefa_insert', 'descricao' => 'Pode criar uma nova tarefa em Weekly report'];
        $lista[] = ['nome' => 'weekly_report_quadro_tarefa_update', 'descricao' => 'Pode editar título e descrição de uma tarefa em Weekly report'];
        $lista[] = ['nome' => 'weekly_report_quadro_tarefa_delete', 'descricao' => 'Pode excluir uma tarefa em Weekly report'];

        $lista[] = ['nome' => 'cadastro_areaetiqueta', 'descricao' => 'Acessar rota Area Etiquetas'];

        $lista[] = ['nome' => 'cadastro_centrocusto', 'descricao' => 'Acessar a rota Centro de Custo'];
        $lista[] = ['nome' => 'cadastro_centrocusto_insert', 'descricao' => 'Pode criar uma nova centrocusto'];
        $lista[] = ['nome' => 'cadastro_centrocusto_update', 'descricao' => 'Pode editar um centrocusto'];

        //Configurações da empresa
        $lista[] = ['nome' => 'controle_ponto_config_empresa', 'descricao' => 'Acesso a tela de configurações da empresa'];

        //PONTO MANUAL
        $lista[] = ['nome' => 'controle_ponto_folha_ponto_manual', 'descricao' => 'Acesso a tela de folha de ponto manual'];
        $lista[] = ['nome' => 'controle_ponto_relatorio_sintetico', 'descricao' => 'Acesso a tela de relatorio sintetico'];


        //Perimetros
        $lista[] = ['nome' => 'controle_ponto_perimetros', 'descricao' => 'Acessar a rota de perímetros'];
        $lista[] = ['nome' => 'controle_ponto_perimetros_insert', 'descricao' => 'Pode cadastrar perímetros da empresa'];
        $lista[] = ['nome' => 'controle_ponto_perimetros_update', 'descricao' => 'Pode editar perímetros da empresa'];
        $lista[] = ['nome' => 'controle_ponto_perimetros_delete', 'descricao' => 'Pode excluir perímetros da empresa'];
        $lista[] = ['nome' => 'controle_ponto_perimetros_funcionarios', 'descricao' => 'Pode associar perímetros aos funcionários'];

        //Ocorrencias jornadas
        $lista[] = ['nome' => 'controle_ponto_ocorrencias_jornadas', 'descricao' => 'Acesso a tela de ocorrências de jornadas'];
        $lista[] = ['nome' => 'controle_ponto_ocorrencias_jornadas_insert', 'descricao' => 'Pode cadastrar uma ocorrência de jornadas'];
        $lista[] = ['nome' => 'controle_ponto_ocorrencias_jornadas_update', 'descricao' => 'Pode editar uma ocorrência de jornadas'];
        $lista[] = ['nome' => 'controle_ponto_ocorrencias_jornadas_delete', 'descricao' => 'Pode excluir ocorrência de jornadas'];

        //Escalas
        $lista[] = ['nome' => 'controle_ponto_escalas', 'descricao' => 'Acesso a tela de escalas'];
        $lista[] = ['nome' => 'controle_ponto_escalas_insert', 'descricao' => 'Pode cadastrar uma escala'];
        $lista[] = ['nome' => 'controle_ponto_escalas_update', 'descricao' => 'Pode editar uma escala'];
        $lista[] = ['nome' => 'controle_ponto_escalas_delete', 'descricao' => 'Pode excluir escala'];
        $lista[] = ['nome' => 'controle_ponto_escalas_funcionarios', 'descricao' => 'Pode associar escala aos funcionários'];

        //Ponto
        $lista[] = ['nome' => 'controle_ponto_ponto-eletronico', 'descricao' => 'O usuáro terá que bater ponto nas escalas definidas'];
        $lista[] = ['nome' => 'controle_ponto_ajustar-jornadas', 'descricao' => 'Pode fazer correções/ajuste nas jornadas de trabalho já registradas'];
        $lista[] = ['nome' => 'controle_ponto_folha-ponto', 'descricao' => 'Pode verificar e imprimir a folha de ponto do funcionário'];
        $lista[] = ['nome' => 'controle_ponto_folha-ponto_adm', 'descricao' => 'Administrar e ver todas as folhas de ponto'];

        //Feriados------------------------------
        $lista[] = ['nome' => 'controle_ponto_feriados', 'descricao' => 'Acessar rota/menu feriados'];
        $lista[] = ['nome' => 'controle_ponto_feriados_insert', 'descricao' => 'Pode cadastrar feriados'];
        $lista[] = ['nome' => 'controle_ponto_feriados_update', 'descricao' => 'Pode alterar feriados'];
        $lista[] = ['nome' => 'controle_ponto_feriados_delete', 'descricao' => 'Pode apagar feriados'];

        //EMPRESA EXAMES------------------------------
        $lista[] = ['nome' => 'cadastro_empresa_exame', 'descricao' => 'Acessar rota/menu empresa_exame'];
        $lista[] = ['nome' => 'cadastro_empresa_exame_insert', 'descricao' => 'Pode cadastrar empresa_exame'];
        $lista[] = ['nome' => 'cadastro_empresa_exame_update', 'descricao' => 'Pode alterar empresa_exame'];
        $lista[] = ['nome' => 'cadastro_empresa_exame_delete', 'descricao' => 'Pode apagar feriados'];

        //EXAMES PCMSO------------------------------
        $lista[] = ['nome' => 'cadastro_empresa_pcmso', 'descricao' => 'Acessar PCMSO'];
        $lista[] = ['nome' => 'cadastro_empresa_pcmso_insert', 'descricao' => 'Pode cadastrar um PCMSO'];
        $lista[] = ['nome' => 'cadastro_empresa_pcmso_update', 'descricao' => 'Pode alterar um PCMSO'];

        //EMPRESA TEMPORARIA------------------------------
        $lista[] = ['nome' => 'cadastro_empresa_temporaria', 'descricao' => 'Acessar rota/menu empresa_temporaria'];
        $lista[] = ['nome' => 'cadastro_empresa_temporaria_insert', 'descricao' => 'Pode cadastrar empresa_temporaria'];
        $lista[] = ['nome' => 'cadastro_empresa_temporaria_update', 'descricao' => 'Pode alterar empresa_temporaria'];
        $lista[] = ['nome' => 'cadastro_empresa_temporaria_delete', 'descricao' => 'Pode apagar empresa_temporaria'];

        //HABILIDADES CLINICA------------------------------
        $lista[] = ['nome' => 'acesso_clinica', 'descricao' => 'Acesso clinica'];
        $lista[] = ['nome' => 'acesso_clinica_insert', 'descricao' => 'Pode cadastrar exame'];
        $lista[] = ['nome' => 'acesso_clinica_update', 'descricao' => 'Pode alterar exame'];
//        $lista[] = ['nome' => 'cadastro_empresa_temporaria_delete', 'descricao' => 'Pode apagar empresa_temporaria'];

        //HABILIDADES PROJETO------------------------------
        $lista[] = ['nome' => 'cadastro_projetos', 'descricao' => 'Acesso ao menu Projeto'];
        $lista[] = ['nome' => 'cadastro_projetos_insert', 'descricao' => 'Pode cadastrar Projeto'];
        $lista[] = ['nome' => 'cadastro_projetos_update', 'descricao' => 'Pode alterar Projeto'];
//        $lista[] = ['nome' => 'cadastro_empresa_temporaria_delete', 'descricao' => 'Pode apagar empresa_temporaria'];

        //HABILIDADES PLANEJAMENTO MOBILIZAÇÃO------------------------------
        $lista[] = ['nome' => 'planejamento_mobilizacao', 'descricao' => 'Acesso ao menu Mobilização dentro de Planejamento'];

        // HABILIDADES AVALIAÇÃO

        $lista[] = ['nome' => 'cadastro_avaliacoes', 'descricao' => 'Pode cadastrar avaliações'];

        $lista[] = ['nome' => 'cadastro_avaliacao_tipo', 'descricao' => 'Acessar tipos de avaliação'];
        $lista[] = ['nome' => 'cadastro_avaliacao_tipo_insert', 'descricao' => 'Pode cadastrar tipos de avaliação'];
        $lista[] = ['nome' => 'cadastro_avaliacao_tipo_update', 'descricao' => 'Pode alterar tipos de avaliação'];
        $lista[] = ['nome' => 'cadastro_avaliacao_tipo_show', 'descricao' => 'Pode visualizar tipos de avaliação'];
        $lista[] = ['nome' => 'cadastro_avaliacao_tipo_active', 'descricao' => 'Pode ativar/desativar tipos de avaliação'];

        $lista[] = ['nome' => 'cadastro_avaliador_tipo', 'descricao' => 'Acessar tipos de avaliadores'];
        $lista[] = ['nome' => 'cadastro_avaliador_tipo_insert', 'descricao' => 'Pode cadastrar tipos de avaliadores'];
        $lista[] = ['nome' => 'cadastro_avaliador_tipo_update', 'descricao' => 'Pode alterar tipos de avaliadores'];
        $lista[] = ['nome' => 'cadastro_avaliador_tipo_show', 'descricao' => 'Pode visualizar tipos de avaliadores'];
        $lista[] = ['nome' => 'cadastro_avaliador_tipo_active', 'descricao' => 'Pode ativar/desativar tipos de avaliadores'];

        $lista[] = ['nome' => 'cadastro_avaliacao_topico', 'descricao' => 'Acessar tópicos de avaliação'];
        $lista[] = ['nome' => 'cadastro_avaliacao_topico_insert', 'descricao' => 'Pode cadastrar tópicos de avaliação'];
        $lista[] = ['nome' => 'cadastro_avaliacao_topico_update', 'descricao' => 'Pode alterar tópicos de avaliação'];

        $lista[] = ['nome' => 'cadastro_avaliacao', 'descricao' => 'Acessar cadastro de avaliações'];
        $lista[] = ['nome' => 'cadastro_avaliacao_insert', 'descricao' => 'Pode cadastrar avaliação'];
        $lista[] = ['nome' => 'cadastro_avaliacao_update', 'descricao' => 'Pode alterar cadastro de avaliação'];
        $lista[] = ['nome' => 'cadastro_avaliacao_active', 'descricao' => 'Pode ativar/desativar cadastro de avaliação'];
        $lista[] = ['nome' => 'cadastro_avaliacao_vincular_avaliadores', 'descricao' => 'Pode vincular avaliadores na avaliação'];

        $lista[] = ['nome' => 'avaliacoes_listar', 'descricao' => 'Acessar avaliações'];
        $lista[] = ['nome' => 'avaliacoes_avaliar', 'descricao' => 'Pode fazer avaliação'];
        $lista[] = ['nome' => 'avaliacoes_final', 'descricao' => 'Pode fazer avaliacao final'];

        $lista[] = ['nome' => 'relatorio_efetivo', 'descricao' => 'Pode acessar relatório de efetivo'];

        $lista[] = ['nome' => 'admissao_documentos_carta_oferta', 'descricao' => 'Acessa Menu Carta Oferta'];
        $lista[] = ['nome' => 'admissao_documentos_carta_oferta_aprova_reprova', 'descricao' => 'Pode Aprovar ou Reprovar uma Carta Oferta'];


        // PLANEJAMENTO MOVIMENTAÇÃO
        $lista[] = ['nome' => 'planejamento_movimentacao_exibir_aba_demissao', 'descricao' => 'Exibir aba demissão'];
        $lista[] = ['nome' => 'planejamento_movimentacao_exibir_aba_ferias', 'descricao' => 'Exibir aba férias'];
        $lista[] = ['nome' => 'planejamento_movimentacao_ferias_editar', 'descricao' => 'Editar solicitação de férias'];
        $lista[] = ['nome' => 'planejamento_movimentacao_ferias_deletar', 'descricao' => 'Deletar solicitação de férias'];
        $lista[] = ['nome' => 'planejamento_movimentacao_exibir_aba_admissao', 'descricao' => 'Exibir aba admissão'];
        $lista[] = ['nome' => 'planejamento_movimentacao_exibir_aba_lideranca_de_pessoal_valor_extra', 'descricao' => 'Exibir aba liderança de pessoal e valor extra'];
        $lista[] = ['nome' => 'planejamento_movimentacao_exibir_aba_mudanca_cargo', 'descricao' => 'Exibir aba mudança de cargo'];
        $lista[] = ['nome' => 'planejamento_movimentacao_exibir_aba_mudanca_de_intermitente_para_fixo', 'descricao' => 'Exibir aba mudança de intermitente'];
        $lista[] = ['nome' => 'planejamento_movimentacao_exibir_aba_transferencia', 'descricao' => 'Exibir aba transferencia'];


        // PRIVILEGIOS
        $lista[] = ['nome' => 'privilegio_aprovar_por_gestor', 'descricao' => 'Aprovação de Gestor'];
        $lista[] = ['nome' => 'privilegio_aprovar_por_rh', 'descricao' => 'Aprovação de Rh'];
        $lista[] = ['nome' => 'privilegio_realizar-lancamento', 'descricao' => 'Definir lançamento como realizado ou não'];
        $lista[] = ['nome' => 'privilegio_processo_demitir', 'descricao' => 'Pode demitir um funcionário, diretamente no menu processo'];

        try {
            DB::beginTransaction();

            foreach ($lista as $habilidade) {
                if (Habilidade::whereNome($habilidade['nome'])->count() == 0) {
                    echo "\e[032mCriando habilidade: " . $habilidade['nome'] . " - " . $habilidade['descricao'] . "\n";
                    Habilidade::create($habilidade);
                } else {
                    echo "\e[34mHabilidade já existe: " . $habilidade['nome'] . " - " . $habilidade['descricao'] . "\n";
                }
            }
            \Cache::forget('habilidades_sistema');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getTrace() . ' - ' . $e->getCode() . ' - ' . $e->getCode() . ' - ' . $e->getLine();
        }
    }
}
