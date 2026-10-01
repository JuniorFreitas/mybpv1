@extends('layouts.sistema')
@section('title', 'Recrutamento')
@section('content_header','Recrutamento')
@section('content')

    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
        <template #conteudo>
            <preload v-show="preloadAjax" :label="editando ? 'Salvando ...' : 'Carregando ...'"></preload>
            <form v-show="!preloadAjax && (!cadastrado && !atualizado)" id="form" class="mybp-modal-form mybp-filtros-compactos" onsubmit="return false;">
                <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda" v-show="editando">
                    Campos com <span class="text-danger">*</span> são obrigatórios.
                </p>

                <fieldset class="mybp-modal-secao">
                    <legend>Dados pessoais</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" v-model="form.nome"
                                       placeholder="Nome" autocomplete="off"
                                       onblur="valida_campo_vazio(this,3)">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">CPF <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" v-model="form.cpf"
                                       placeholder="CPF" disabled autocomplete="off" v-mascara:cpf>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">RG</label>
                                <input type="text" class="form-control form-control-sm" v-model="form.rg"
                                       placeholder="RG" autocomplete="off" v-mascara:numero
                                       onblur="valida_campo(this,1)">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Órgão expeditor (RG)</label>
                                <input type="text" class="form-control form-control-sm" v-model="form.orgao_expeditor"
                                       placeholder="Órgão" autocomplete="off" onblur="valida_campo(this,1)">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">CNH</label>
                                <input type="text" class="form-control form-control-sm" v-model="form.cnh"
                                       placeholder="Tipo da CNH" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Nascimento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" v-model="form.nascimento"
                                       placeholder="Ex: 10/10/2010" v-mascara:data autocomplete="off"
                                       onblur="valida_data_vazio(this)">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="rec-modal-sexo">Sexo</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="rec-modal-sexo"
                                        input-id="rec-modal-sexo"
                                        v-model="form.sexo"
                                        :options="opcoesSexoModal"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="20"
                                        @opening="fecharOutrosComboboxesModal('rec-modal-sexo')"
                                        @select="limparComboboxInvalido('rec-modal-sexo')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="rec-modal-estado-civil">Estado civil</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="rec-modal-estado-civil"
                                        input-id="rec-modal-estado-civil"
                                        v-model="form.estado_civil"
                                        :options="opcoesEstadoCivilModal"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="20"
                                        @opening="fecharOutrosComboboxesModal('rec-modal-estado-civil')"
                                        @select="limparComboboxInvalido('rec-modal-estado-civil')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">E-mail <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" v-model="form.email"
                                       placeholder="Ex.: email@email.com" autocomplete="off"
                                       onblur="validaEmailVazio(this)">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Nome do pai</label>
                                <input type="text" class="form-control form-control-sm" v-model="form.filiacao_pai"
                                       placeholder="Nome" autocomplete="off" onblur="valida_campo(this,3)">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Nome da mãe</label>
                                <input type="text" class="form-control form-control-sm" v-model="form.filiacao_mae"
                                       placeholder="Nome" autocomplete="off" onblur="valida_campo(this,3)">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Endereço</legend>
                    <endereco :model="form"></endereco>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Contatos</legend>
                    <telefone :model="form.telefones" :model-delete="form.telefonesDelete" :qnt_min="1"
                              :pais="false" :ramal="false"></telefone>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Formação</legend>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Formação</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.formacao?.tipo || 'Não informado'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Curso</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.formacao_curso ? (form.formacao_curso + ' (' + (form.formacao_status || '') + ')') : 'Não informado'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Instituição</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.formacao_instituicao || 'Não informado'">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao" v-if="form.experiencias && form.experiencias.length">
                    <legend>Experiências</legend>
                    <div
                        class="mybp-modal-bloco-experiencia"
                        v-for="(item, idx) in form.experiencias"
                        :key="'exp-' + idx"
                    >
                        <p class="mybp-modal-bloco-experiencia__titulo">Experiência @{{ idx + 1 }}</p>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Empresa</label>
                                    <input type="text" class="form-control form-control-sm" disabled
                                           :value="item.empresa || 'Não informado'">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Cargo</label>
                                    <input type="text" class="form-control form-control-sm" disabled
                                           :value="item.cargo || 'Não informado'">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Período</label>
                                    <input type="text" class="form-control form-control-sm" disabled
                                           :value="(item.data_inicio || '—') + ' até ' + (item.data_fim || '—')">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Nome referência</label>
                                    <input type="text" class="form-control form-control-sm" disabled
                                           :value="item.referencia_nome || 'Não informado'">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Telefone referência</label>
                                    <input type="text" class="form-control form-control-sm" disabled
                                           :value="item.referencia_telefone || 'Não informado'">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Principais atividades</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        rows="4"
                                        disabled
                                        :value="item.principais_atv || 'Não informado'"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao" v-if="form.qualificacoes && form.qualificacoes.length">
                    <legend>Qualificações</legend>
                    <div
                        class="mybp-modal-bloco-experiencia"
                        v-for="(item, idx) in form.qualificacoes"
                        :key="'qual-' + idx"
                    >
                        <p class="mybp-modal-bloco-experiencia__titulo" v-if="form.qualificacoes.length > 1">
                            Qualificação @{{ idx + 1 }}
                        </p>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Curso</label>
                                    <input type="text" class="form-control form-control-sm" disabled :value="item.nome || 'Não informado'">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Instituição</label>
                                    <input type="text" class="form-control form-control-sm" disabled :value="item.instituicao || 'Não informado'">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Conclusão</label>
                                    <input type="text" class="form-control form-control-sm" disabled
                                           :value="(item.mes_conclusao || '—') + '/' + (item.ano_conclusao || '—')">
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Feedback</legend>
                    <div class="alert alert-warning py-2 px-3 mb-2" v-if="form.atualizacao">
                        Este currículo foi atualizado em: @{{ form.atualizacao.created_at }}
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-4" v-if="editando">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Vaga pretendida</label>
                                <input type="text" disabled class="form-control form-control-sm"
                                       :value="form.vaga_aberta?.vaga_selecionada?.nome || 'Não informado'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4" v-if="editando">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="rec-modal-viajar">Disponibilidade para viajar</label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="rec-modal-viajar"
                                        input-id="rec-modal-viajar"
                                        v-model="viajarCombo"
                                        :options="opcoesSimNaoModal"
                                        :disabled="true"
                                        placeholder-blur="Não informado"
                                        empty-message="Nenhuma opção."
                                        :max-results="5"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4" v-if="editando">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Cota PCD (Lei nº 8.213/91)</label>
                                <input type="text" disabled class="form-control form-control-sm"
                                       :value="!form.pcd ? 'Não' : 'Sim'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4" v-if="editando && form.pcd">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">CID</label>
                                <input type="text" disabled class="form-control form-control-sm" :value="form.cid">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="rec-modal-selecionado">Selecionado <span class="text-danger">*</span></label>
                                <div class="mybp-combobox-wrap">
                                    <combobox-auto-complete
                                        instance-id="rec-modal-selecionado"
                                        input-id="rec-modal-selecionado"
                                        v-model="form_feedback.selecionado"
                                        :options="opcoesSelecionadoModal"
                                        placeholder-blur="Selecione..."
                                        empty-message="Nenhuma opção."
                                        :max-results="10"
                                        @opening="fecharOutrosComboboxesModal('rec-modal-selecionado')"
                                        @select="onSelectFeedbackCombo('rec-modal-selecionado')"
                                    ></combobox-auto-complete>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="form_feedback.selecionado === 'nao'">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="rec-modal-mail-desclass">E-mail desclassificação <span class="text-danger">*</span></label>
                                <mybp-bool-combobox
                                    input-id="rec-modal-mail-desclass"
                                    v-model="form_feedback.envia_mail_desclassificacao"
                                    @opening="fecharOutrosComboboxesModal('rec-modal-mail-desclass')"
                                    @select="limparComboboxInvalido('rec-modal-mail-desclass')"
                                ></mybp-bool-combobox>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="form_feedback.selecionado && form_feedback.selecionado !== 'nao'">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Selecione uma vaga <span class="text-danger">*</span></label>
                                <autocomplete
                                    :formsm="true"
                                    :caminho="controle.dados.caminho_autocomplete"
                                    :valido="form_feedback.vagas_abertas_id !== ''"
                                    v-model="form_feedback.autocomplete_label_vaga_modal"
                                    placeholder="Digite o nome da vaga"
                                    id="rec-modal-vaga"
                                    @onblur="resetaCampoVagaModal"
                                    @onselect="selecionaVagaModal"
                                ></autocomplete>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="form_feedback.selecionado === 'sim' && form_feedback.tem_provas">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="rec-modal-mail-provas">E-mail links de provas <span class="text-danger">*</span></label>
                                <mybp-bool-combobox
                                    input-id="rec-modal-mail-provas"
                                    v-model="form_feedback.envia_mail_provas"
                                    @opening="fecharOutrosComboboxesModal('rec-modal-mail-provas')"
                                    @select="limparComboboxInvalido('rec-modal-mail-provas')"
                                ></mybp-bool-combobox>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="form_feedback.selecionado === 'sim'">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="rec-modal-mail-etapa">E-mail avanço de etapa <span class="text-danger">*</span></label>
                                <mybp-bool-combobox
                                    input-id="rec-modal-mail-etapa"
                                    v-model="form_feedback.envia_mail_proxima_etapa"
                                    @opening="fecharOutrosComboboxesModal('rec-modal-mail-etapa')"
                                    @select="limparComboboxInvalido('rec-modal-mail-etapa')"
                                ></mybp-bool-combobox>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="form_feedback.selecionado && form_feedback.selecionado !== 'nao'">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="rec-modal-contato">Contato realizado <span class="text-danger">*</span></label>
                                <mybp-bool-combobox
                                    input-id="rec-modal-contato"
                                    v-model="form_feedback.contato_realizado"
                                    @opening="fecharOutrosComboboxesModal('rec-modal-contato')"
                                    @select="limparComboboxInvalido('rec-modal-contato')"
                                ></mybp-bool-combobox>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="telefonePrincipal">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Contato principal</label>
                                <input type="text" class="form-control form-control-sm" disabled :value="telefonePrincipalNumero">
                            </div>
                        </div>

                        <template v-if="permite_envio_whatsapp && telefonePrincipal && telefonePrincipal.tipo === 'whatsapp'">
                            <div class="col-12 col-md-4" v-if="form_feedback.contato_realizado">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="rec-modal-whatsapp">Notificação WhatsApp <span class="text-danger">*</span></label>
                                    <mybp-bool-combobox
                                        input-id="rec-modal-whatsapp"
                                        v-model="form_feedback.envia_whatsapp"
                                        @opening="fecharOutrosComboboxesModal('rec-modal-whatsapp')"
                                        @select="limparComboboxInvalido('rec-modal-whatsapp')"
                                    ></mybp-bool-combobox>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="form_feedback.contato_realizado && form_feedback.envia_whatsapp">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">&nbsp;</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-block mybp-btn-acao-compact"
                                            @click="previewRecrutamentoWhatsapp">
                                        <i class="fab fa-whatsapp"></i> Visualizar mensagem
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.contato_realizado && form_feedback.selecionado && form_feedback.selecionado !== 'nao'">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label" for="rec-modal-interesse">Interesse <span class="text-danger">*</span></label>
                                <mybp-bool-combobox
                                    input-id="rec-modal-interesse"
                                    v-model="form_feedback.interesse"
                                    @opening="fecharOutrosComboboxesModal('rec-modal-interesse')"
                                    @select="limparComboboxInvalido('rec-modal-interesse')"
                                ></mybp-bool-combobox>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.interesse && form_feedback.contato_realizado && form_feedback.selecionado && form_feedback.selecionado !== 'nao'">
                            <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                                <datepicker :hora="true" label="Entrevista" formsm
                                            min="{{(new \MasterTag\DataHora())->dataCompleta()}}"
                                            posicao="up" v-model="form_feedback.data_entrevista"></datepicker>
                            </div>
                        </div>

                        <div class="col-12 col-md-4"
                             v-if="form_feedback.interesse && form_feedback.contato_realizado && form_feedback.selecionado && form_feedback.selecionado !== 'nao'">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Local entrevista <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-model="form_feedback.local_entrevista">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Observações</label>
                                <textarea
                                    class="form-control form-control-sm"
                                    rows="3"
                                    v-model="form_feedback.obs"
                                ></textarea>
                            </div>
                        </div>

                        <div class="col-12 col-md-4" v-if="feedback && form.lido">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Lido por</label>
                                <input type="text" class="form-control form-control-sm" disabled
                                       :value="form.usuario?.nome || 'Não informado'">
                            </div>
                        </div>
                        <div class="col-12 col-md-4" v-if="feedback && form.lido">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Em</label>
                                <input type="text" class="form-control form-control-sm" disabled :value="form.datalido">
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado && !preloadAjax"
                    @click="alterar"
            >
                Salvar
            </button>

        </template>
    </modal>

    <modal ref="janelaConfirmar" id="janelaConfirmar" titulo="Apagar Curriculo">
        <template #conteudo>
            <span v-show="preloadAjax"><preload></preload></span>
            <div class="alert alert-success alert-dismissible" v-show="apagado">
                <h4><i class="icon fa fa-check"></i>Curriculo apagado com sucesso!</h4>
            </div>
            <h4 v-show="!apagado">Tem certeza que deseja apagar este curriculo?</h4>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-danger" @click="apagar()" v-show="!apagado">Apagar</button>
        </template>
    </modal>

    <div class="mybp-kpi-row" role="group" aria-label="Resumo do recrutamento">
        <article class="mybp-kpi mybp-kpi--neutro">
            <div class="mybp-kpi__icon" aria-hidden="true">
                <i class="fas fa-users"></i>
            </div>
            <div class="mybp-kpi__body">
                <span class="mybp-kpi__label">Total cadastrados</span>
                <span class="mybp-kpi__valor">{{ number_format($curriculos, 0, ',', '.') }}</span>
                <span class="mybp-kpi__hint">Base completa com vaga</span>
            </div>
        </article>

        <article class="mybp-kpi mybp-kpi--info">
            <div class="mybp-kpi__icon" aria-hidden="true">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="mybp-kpi__body">
                <span class="mybp-kpi__label">Cadastrados</span>
                <span class="mybp-kpi__valor">{{ number_format($curriculos90dias, 0, ',', '.') }}</span>
                <span class="mybp-kpi__hint">Últimos 90 dias</span>
            </div>
        </article>

        <article class="mybp-kpi mybp-kpi--ok">
            <div class="mybp-kpi__icon" aria-hidden="true">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="mybp-kpi__body">
                <span class="mybp-kpi__label">Selecionados</span>
                <span class="mybp-kpi__valor">{{ number_format($selecionados90dias, 0, ',', '.') }}</span>
                <span class="mybp-kpi__hint">
                    Últimos 90 dias
                    <span class="mybp-kpi__meta">· {{ number_format($taxaSelecao90dias, 1, ',', '.') }}% dos cadastrados</span>
                </span>
            </div>
        </article>
    </div>

    <filtro-listagem
        class="mt-2 mybp-filtros-compactos"
        :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
        :desabilitado="controle.carregando"
        @submit="atualizar"
        @limpar="limparFiltros"
    >
        <template #filtros>
            <date-range-filter
                :key="'rec-filtro-periodo'"
                v-model:enabled="controle.dados.filtroPeriodo"
                v-model:start-date="controle.dados.dataInicio"
                v-model:end-date="controle.dados.dataFim"
                :disabled="!!controle.carregando"
                :id-suffix="'periodo-' + hash"
                label="Por período"
                wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                @change="onPeriodoChange"
            ></date-range-filter>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-busca">
                        Candidato / CPF
                        <span v-if="buscaUnificadaEhCpf" class="text-muted small">CPF</span>
                    </label>
                    <input
                        id="rec-filtro-busca"
                        type="text"
                        placeholder="Nome ou CPF"
                        autocomplete="off"
                        inputmode="search"
                        class="form-control form-control-sm"
                        :disabled="controle.carregando"
                        :value="campoBuscaUnificada"
                        @input="onInputBuscaUnificada"
                    />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label">Cargo</label>
                    <autocomplete
                        :caminho="controle.dados.caminho_autocomplete"
                        :valido="controle.dados.campoVaga !== ''"
                        :formsm="true"
                        v-model="controle.dados.autocomplete_label"
                        :disabled="controle.carregando"
                        placeholder="Por cargo"
                        @onblur="resetaCampo"
                        @onselect="selecionaVaga"
                    ></autocomplete>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-uf">Estado</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroUf"
                            instance-id="rec-filtro-uf"
                            input-id="rec-filtro-uf"
                            v-model="controle.dados.campoUf"
                            :options="filtroUfOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos os estados"
                            empty-message="Nenhum estado encontrado."
                            :max-results="30"
                            @opening="fecharOutrosComboboxes('rec-filtro-uf')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-lido">Lido</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroLido"
                            instance-id="rec-filtro-lido"
                            input-id="rec-filtro-lido"
                            v-model="controle.dados.campoLido"
                            :options="filtroSimNaoOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Geral"
                            empty-message="Nenhuma opção."
                            :max-results="5"
                            @opening="fecharOutrosComboboxes('rec-filtro-lido')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-pcd">PCD</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroPcd"
                            instance-id="rec-filtro-pcd"
                            input-id="rec-filtro-pcd"
                            v-model="controle.dados.campoPcd"
                            :options="filtroSimNaoOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Geral"
                            empty-message="Nenhuma opção."
                            :max-results="5"
                            @opening="fecharOutrosComboboxes('rec-filtro-pcd')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="rec-filtro-pages">Exibir</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroPages"
                            instance-id="rec-filtro-pages"
                            input-id="rec-filtro-pages"
                            v-model="campoPagesCombo"
                            :options="filtroPagesOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="20"
                            empty-message="Nenhuma opção."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('rec-filtro-pages')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>
        </template>

        <template #acoes>
            <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                Buscar
            </button>
            <button
                type="button"
                class="btn btn-sm btn-outline-primary mybp-btn-acao-compact"
                @click.prevent="exportaExcel()"
                :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
            >
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </template>
    </filtro-listagem>

    <p class="text-center" v-if="controle.carregando">
        <preload></preload>
    </p>

    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>

    <div id="conteudo" v-show="!controle.carregando && lista.length > 0">
        <div class="mybp-cards-lista">
            <div class="mybp-card" v-for="curriculo in lista" :key="curriculo.id">
                <div class="mybp-card-header-row">
                    <div class="mybp-card-left">
                        <span class="mybp-badge-id">#@{{ curriculo.id }}</span>
                        <div class="mybp-card-titulo">
                            <strong>@{{ curriculo.nome || 'Não informado' }}</strong>
                        </div>
                    </div>
                    <div class="mybp-card-right">
                        <mybp-status-badge
                            :variante="chaveStatusRec(curriculo)"
                            :texto="textoStatusRec(curriculo)"
                        ></mybp-status-badge>
                        <div class="dropdown" :class="{ show: isDropdownOpen(curriculo.id) }">
                            <a
                                class="mybp-btn-acoes-compact"
                                href="#"
                                role="button"
                                :id="'rec-acoes-' + curriculo.id"
                                aria-haspopup="true"
                                :aria-expanded="isDropdownOpen(curriculo.id) ? 'true' : 'false'"
                                @click.prevent.stop="toggleDropdown(curriculo.id)"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div
                                class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                :class="{ show: isDropdownOpen(curriculo.id) }"
                                :aria-labelledby="'rec-acoes-' + curriculo.id"
                                @click="fecharDropdown"
                            >
                                <a
                                    class="dropdown-item"
                                    href="javascript://"
                                    @click.prevent="formAlterar(curriculo.id)"
                                >
                                    <i class="fa fa-edit mr-1"></i> Recrutar
                                </a>
                                <a
                                    class="dropdown-item"
                                    :href="`recrutamentos/${curriculo.ctoken}`"
                                    target="_blank"
                                >
                                    <i class="far fa-file-pdf mr-1"></i> Gerar PDF
                                </a>
                                @can('curriculos_recrutamento_delete')
                                    <a
                                        class="dropdown-item text-danger"
                                        href="javascript://"
                                        @click.prevent="janelaConfirmar(curriculo.id); $refs.janelaConfirmar?.abrirModal()"
                                    >
                                        <i class="fa fa-trash mr-1"></i> Remover
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mybp-card-corpo" :class="'mybp-card-corpo--' + chaveStatusRec(curriculo)">
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-id-card"
                                label="CPF"
                                :valor="curriculo.cpf"
                                forte
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-map-marker-alt"
                                label="UF"
                                :valor="curriculo.uf_vaga"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-briefcase"
                                label="Vaga"
                                :valor="curriculo.vaga_aberta?.vaga_selecionada?.nome"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-wheelchair"
                                label="PCD"
                                :valor="curriculo.pcd ? 'Sim' : 'Não'"
                                :tom="curriculo.pcd ? 'positivo' : 'meta'"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-user-check"
                                label="Selecionado"
                                :valor="textoSelecionadoRec(curriculo)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-phone"
                                label="Contato realizado"
                                :valor="textoSimNaoFeed(curriculo, 'contato_realizado')"
                                :tom="tomSimNaoFeed(curriculo, 'contato_realizado')"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-briefcase"
                                label="Experiências"
                                :valor="textoTemCountRec(curriculo, 'experiencias_count')"
                                :tom="tomTemCountRec(curriculo, 'experiencias_count')"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-graduation-cap"
                                label="Qualificações"
                                :valor="textoTemCountRec(curriculo, 'qualificacoes_count')"
                                :tom="tomTemCountRec(curriculo, 'qualificacoes_count')"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-heart"
                                label="Interesse"
                                :valor="textoInteresseRec(curriculo)"
                                :tom="tomInteresseRec(curriculo)"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-calendar"
                                label="Data cadastro"
                                :valor="curriculo.created_at"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-eye"
                                label="Lido"
                                :valor="curriculo.lido ? 'Sim' : 'Não'"
                                :tom="curriculo.lido ? 'positivo' : 'negativo'"
                            ></mybp-card-campo>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.recrutamento.recrutamentos.atualizar')}}"
                            :por-pagina="controle.dados.pages"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"
        ></controle-paginacao>

        <whatsapp-preview-modal
            v-model="previewWhatsappAberto"
            :tipo-mensagem="previewWhatsappTipo"
            :contexto="previewWhatsappContexto"
        />
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/recrutamento/app.js')}}"></script>
@endpush
