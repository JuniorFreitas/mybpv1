@extends('layouts.sistema')
@section('title', 'Pré-admissão')
@section('content_header')
    <h4 class="text-default">Pré-admissão</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal ref="janelaVisualizar" id="janelaVisualizar" :titulo="tituloJanela" size="g">
        <template #conteudo>
            <preload class="mt-2 text-center" v-if="preload"></preload>
            <div v-if="!preload" id="formDocumentos" class="mybp-modal-form mybp-filtros-compactos">
                <div class="mybp-card-corpo mb-3" :class="classeCorpoStatus(form)">
                    <section class="mybp-card-secao mybp-card-secao--meta">
                        <div class="mybp-card-status-botoes">
                            <div class="mybp-card-status-item">
                                <span class="mybp-card-status-label">Colaborador</span>
                                <mybp-status-badge
                                    :variante="chaveSituacaoColaborador(form)"
                                    :texto="textoSituacaoColaborador(form)"
                                ></mybp-status-badge>
                            </div>
                            <div class="mybp-card-status-item">
                                <span class="mybp-card-status-label">Documentos</span>
                                <mybp-status-badge
                                    :variante="chaveStatusDocumento(form)"
                                    :texto="textoStatusDocumento(form)"
                                ></mybp-status-badge>
                            </div>
                        </div>
                    </section>
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-user"
                                label="Candidato"
                                :valor="textoCampo(form.candidato_nome)"
                                :forte="true"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-id-card"
                                label="CPF"
                                :valor="textoCampo(form.candidato_cpf)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-briefcase"
                                label="Cargo / Vaga"
                                :valor="textoCampo(form.cargo_nome)"
                            ></mybp-card-campo>
                        </div>
                    </section>
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-map-marker-alt"
                                label="Local da vaga"
                                :valor="textoCampo(form.vaga_local)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-envelope"
                                label="E-mail"
                                :valor="textoCampo(form.candidato_email)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-phone"
                                label="Telefone"
                                :valor="textoCampo(form.contato_telefone)"
                            ></mybp-card-campo>
                        </div>
                    </section>
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-map"
                                label="UF currículo"
                                :valor="textoCampo(form.candidato_uf)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                v-if="cliente_id === 1"
                                icon="fas fa-building"
                                label="Cliente"
                                :valor="textoCampo(form.cliente_nome)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-paperclip"
                                label="Anexos enviados"
                                :valor="anexosExibicao(form)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                v-if="form.finalizado"
                                icon="fas fa-user-check"
                                label="Finalizado por"
                                :valor="textoCampo(form.quem_finalizou)"
                            ></mybp-card-campo>
                        </div>
                    </section>
                </div>

                <fieldset class="mybp-modal-secao">
                    <legend>Documentos do candidato</legend>
                    <div class="alert alert-warning mb-0" v-if="!form.docs_curriculo_pre_adm.length">
                        Nenhum documento configurado para pré-admissão.
                    </div>
                    <div
                        v-for="(doc, index) in form.docs_curriculo_pre_adm"
                        :key="doc.tipo || index"
                        class="mb-3 pb-3"
                        :class="{ 'border-bottom': index < form.docs_curriculo_pre_adm.length - 1 }"
                    >
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-1">
                            <strong>@{{ doc.label }}</strong>
                            <mybp-status-badge
                                :variante="doc.enviado ? 'aprovado' : 'pendente'"
                                :texto="doc.enviado ? 'Enviado' : 'Pendente'"
                            ></mybp-status-badge>
                        </div>
                        <p class="text-muted small mb-2" v-if="doc.descricao">@{{ doc.descricao }}</p>
                        <div class="alert alert-info mb-0" v-if="!doc.enviado">
                            Nenhum anexo enviado
                        </div>
                        <upload
                            v-else
                            label="Anexo(s)"
                            :leitura="true"
                            :model="doc.docs_curriculo_anexos"
                            :model-delete="[]"
                            :url="urlAnexoUpload"
                            :multi="false"
                        ></upload>
                    </div>
                </fieldset>
            </div>
        </template>
    </modal>

    <modal ref="janelaFinalizar" id="janelaFinalizar" :titulo="tituloJanelaFinalizar" size="g">
        <template #conteudo>
            <preload class="mt-2 text-center" v-if="preloadFinalizar"></preload>
            <div v-if="!preloadFinalizar" id="formFinalizar" class="mybp-modal-form mybp-filtros-compactos">
                <div class="mybp-card-corpo mb-3" :class="classeCorpoStatus(dadosFinalizar)" v-if="dadosFinalizar.id">
                    <section class="mybp-card-secao mybp-card-secao--meta">
                        <div class="mybp-card-status-botoes">
                            <div class="mybp-card-status-item">
                                <span class="mybp-card-status-label">Colaborador</span>
                                <mybp-status-badge
                                    :variante="chaveSituacaoColaborador(dadosFinalizar)"
                                    :texto="textoSituacaoColaborador(dadosFinalizar)"
                                ></mybp-status-badge>
                            </div>
                            <div class="mybp-card-status-item">
                                <span class="mybp-card-status-label">Documentos</span>
                                <mybp-status-badge
                                    :variante="chaveStatusDocumento(dadosFinalizar)"
                                    :texto="textoStatusDocumento(dadosFinalizar)"
                                ></mybp-status-badge>
                            </div>
                        </div>
                    </section>
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo icon="fas fa-user" label="Candidato" :valor="textoCampo(dadosFinalizar.candidato_nome)" :forte="true"></mybp-card-campo>
                            <mybp-card-campo icon="fas fa-id-card" label="CPF" :valor="textoCampo(dadosFinalizar.candidato_cpf)"></mybp-card-campo>
                            <mybp-card-campo icon="fas fa-briefcase" label="Cargo / Vaga" :valor="textoCampo(dadosFinalizar.cargo_nome)"></mybp-card-campo>
                        </div>
                    </section>
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo icon="fas fa-map-marker-alt" label="Local da vaga" :valor="textoCampo(dadosFinalizar.vaga_local)"></mybp-card-campo>
                            <mybp-card-campo icon="fas fa-envelope" label="E-mail" :valor="textoCampo(dadosFinalizar.candidato_email)"></mybp-card-campo>
                            <mybp-card-campo icon="fas fa-phone" label="Telefone" :valor="textoCampo(dadosFinalizar.contato_telefone)"></mybp-card-campo>
                        </div>
                    </section>
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo icon="fas fa-map" label="UF currículo" :valor="textoCampo(dadosFinalizar.candidato_uf)"></mybp-card-campo>
                            <mybp-card-campo v-if="cliente_id === 1" icon="fas fa-building" label="Cliente" :valor="textoCampo(dadosFinalizar.cliente_nome)"></mybp-card-campo>
                            <mybp-card-campo icon="fas fa-paperclip" label="Anexos enviados" :valor="anexosExibicao(dadosFinalizar)"></mybp-card-campo>
                        </div>
                    </section>
                </div>
                <fieldset class="mybp-modal-secao">
                    <legend>Encaminhamento</legend>
                    <p class="mybp-campo-obrigatorio-legenda mb-3">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Clínica <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="formFinalizar.empresa_exame_id"
                                >
                                    <option value="">Selecione</option>
                                    <option v-for="item in listaEmpresasExames" :value="item.id">
                                        @{{ item.nome }}
                                    </option>
                                </select>
                                <small v-if="clinicaSelecionadaEmail" class="my-2 d-block">@{{ clinicaSelecionadaEmail }}</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                                <label class="mybp-label">Data para realização <span class="text-danger">*</span></label>
                                <datepicker
                                    formsm
                                    label=""
                                    class="corrigiDatepicker"
                                    v-model="formFinalizar.encaminhado_exame_data"
                                    :min="dataHoje"
                                ></datepicker>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">PCMSO <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm" v-model="formFinalizar.pcmso_id"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option v-for="pcmso in listaPcmsos" :value="pcmso.id">
                                        @{{ pcmso.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="custom-control custom-switch float-left">
                                <input type="checkbox" v-model="formFinalizar.envia_email" class="custom-control-input"
                                       id="envia_email">
                                <label class="custom-control-label"
                                       for="envia_email">Enviar E-mail</label>
                            </div>

                            <div class="custom-control custom-switch float-left ml-3"
                                 v-if="whatsappPodeNotificar('exame_encaminhamento', dadosFinalizar.tel_principal)">
                                <input type="checkbox" v-model="formFinalizar.envia_whatsapp"
                                       class="custom-control-input"
                                       id="envia_whatsapp">
                                <label class="custom-control-label"
                                       for="envia_whatsapp">Enviar WhatsApp</label>
                            </div>
                            <div class="col-12 mt-2"
                                 v-if="whatsappPodeNotificar('exame_encaminhamento', dadosFinalizar.tel_principal)">
                                <button type="button" class="btn btn-sm btn-outline-success"
                                        @click="previewWhatsappFinalizar">
                                    <i class="fab fa-whatsapp"></i> Visualizar mensagem
                                </button>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <button class="btn btn-primary btn-sm" v-if="!preloadFinalizar"
                    @click="finalizarEncaminhar(dadosFinalizar.id)">
                <i class="fa fa-save"></i> Finalizar e Salvar
            </button>
        </template>
    </modal>

    <modal ref="janelaEnviarEmail" id="janelaEnviarEmail" :titulo="tituloJanela" size="g">
        <template #conteudo>
            <preload class="mt-2 text-center" v-if="preload"></preload>
            <div v-if="!preload" class="mybp-modal-form mybp-filtros-compactos">
                <div class="mybp-card-corpo mb-3" :class="classeCorpoStatus(formEmail)" v-if="formEmail.id">
                    <section class="mybp-card-secao mybp-card-secao--meta">
                        <div class="mybp-card-status-botoes">
                            <div class="mybp-card-status-item">
                                <span class="mybp-card-status-label">Colaborador</span>
                                <mybp-status-badge
                                    :variante="chaveSituacaoColaborador(formEmail)"
                                    :texto="textoSituacaoColaborador(formEmail)"
                                ></mybp-status-badge>
                            </div>
                            <div class="mybp-card-status-item">
                                <span class="mybp-card-status-label">Documentos</span>
                                <mybp-status-badge
                                    :variante="chaveStatusDocumento(formEmail)"
                                    :texto="textoStatusDocumento(formEmail)"
                                ></mybp-status-badge>
                            </div>
                        </div>
                    </section>
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo icon="fas fa-user" label="Candidato" :valor="textoCampo(formEmail.candidato_nome)" :forte="true"></mybp-card-campo>
                            <mybp-card-campo icon="fas fa-id-card" label="CPF" :valor="textoCampo(formEmail.candidato_cpf)"></mybp-card-campo>
                            <mybp-card-campo icon="fas fa-briefcase" label="Cargo / Vaga" :valor="textoCampo(formEmail.cargo_nome)"></mybp-card-campo>
                        </div>
                    </section>
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo icon="fas fa-map-marker-alt" label="Local da vaga" :valor="textoCampo(formEmail.vaga_local)"></mybp-card-campo>
                            <mybp-card-campo icon="fas fa-phone" label="Telefone" :valor="textoCampo(formEmail.contato_telefone)"></mybp-card-campo>
                            <mybp-card-campo icon="fas fa-paperclip" label="Anexos enviados" :valor="anexosExibicao(formEmail)"></mybp-card-campo>
                        </div>
                    </section>
                </div>

                <div class="alert alert-warning">
                    Observação: a troca do e-mail também altera o acesso ao Sistema.
                    <span v-if="whatsappPodeNotificar('admissao_documentos', formEmail.tel_principal)">
                        <br> Quando habilitado, o colaborador também receberá a mensagem via WhatsApp.
                    </span>
                </div>
                <fieldset class="mybp-modal-secao">
                    <legend>Envio de E-mail</legend>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">E-mail</label>
                                <input type="text" class="form-control form-control-sm validacampo"
                                       v-model="formEmail.email"
                                       autocomplete="mybp" @keyup.prevent="validaEmailVazio($event.target)"
                                       @blur.prevent="validaEmailVazio($event.target)">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mybp-filtro-campo">
                                <label class="mybp-label">Mensagem Adicional</label>
                                <textarea cols="3" rows="5" class="form-control form-control-sm"
                                          v-model="formEmail.observacao"
                                          autocomplete="mybp"></textarea>
                            </div>
                        </div>
                        <div class="col-12"
                             v-if="whatsappPodeNotificar('admissao_documentos', formEmail.tel_principal)">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="envia_whatsapp_email"
                                       v-model="formEmail.envia_whatsapp">
                                <label class="custom-control-label" for="envia_whatsapp_email">Enviar WhatsApp</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                    @click="previewWhatsappEmail">
                                <i class="fab fa-whatsapp"></i> Visualizar mensagem
                            </button>
                        </div>
                    </div>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <button class="btn btn-primary btn-sm" v-if="!preload" @click="enviarEmail">
                <i class="fa fa-share"></i> Enviar
            </button>
        </template>
    </modal>

    <filtro-listagem
        class="mt-2 mybp-filtros-compactos"
        titulo="Filtros"
        :desabilitado="controle.carregando"
        :mostrar-limpar-filtros="temFiltrosAtivos"
        @submit="onSubmitFiltro"
        @limpar="limparFiltros"
    >
        <template #filtros>
            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label" for="preadm-busca">Buscar</label>
                    <input
                        id="preadm-busca"
                        type="text"
                        placeholder="Nome ou CPF"
                        autocomplete="off"
                        class="form-control form-control-sm"
                        :disabled="controle.carregando"
                        v-model="controle.dados.campoBusca"
                    >
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label">Por vaga</label>
                    <autocomplete
                        :disabled="controle.carregando"
                        :caminho="controle.dados.caminho_autocomplete"
                        :valido="controle.dados.campoVaga !== ''"
                        v-model="controle.dados.autocomplete_label"
                        placeholder="Buscar vaga"
                        @onblur="resetaCampo"
                        @onselect="selecionaVaga"
                    ></autocomplete>
                </div>
            </div>
            <div class="col-12 col-md-4" v-if="cliente_id === 1">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label">Por cliente</label>
                    <autocomplete
                        :disabled="controle.carregando"
                        :caminho="controle.dados.caminho_cliente_autocomplete"
                        :valido="controle.dados.campoCliente !== ''"
                        v-model="controle.dados.autocomplete_label_cliente"
                        placeholder="Buscar cliente"
                        @onblur="resetaCampoCliente"
                        @onselect="selecionaCliente"
                    ></autocomplete>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label" for="preadm-filtro-uf">Estado</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroUf"
                            instance-id="filtro-uf"
                            input-id="preadm-filtro-uf"
                            v-model="controle.dados.campoUf"
                            :options="filtroUfOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos os estados"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="30"
                            @opening="fecharOutrosComboboxes('filtro-uf')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label" for="preadm-filtro-status">Situação do colaborador</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroStatus"
                            instance-id="filtro-status"
                            input-id="preadm-filtro-status"
                            v-model="controle.dados.status"
                            :options="filtroStatusOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Em processo"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('filtro-status')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label" for="preadm-filtro-status-documento">Documentos</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroStatusDocumento"
                            instance-id="filtro-status-documento"
                            input-id="preadm-filtro-status-documento"
                            v-model="controle.dados.statusDocumento"
                            :options="filtroStatusDocumentoOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('filtro-status-documento')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo mb-2 mb-md-0">
                    <label class="mybp-label" for="preadm-filtro-pages">Exibir</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroPages"
                            instance-id="filtro-pages"
                            input-id="preadm-filtro-pages"
                            v-model="controle.dados.pages"
                            :options="filtroPagesOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="20"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('filtro-pages')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>
        </template>
        <template #acoes>
            <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                Atualizar
            </button>
        </template>
    </filtro-listagem>

    <div class="d-flex justify-content-end my-2">
        <mybp-visualizacao-listagem v-model="visualizacao" tela="admissao-preadmissao"></mybp-visualizacao-listagem>
    </div>

    <preload v-if="controle.carregando"></preload>

    <div id="conteudo" class="mt-3" @click="onClickOutside">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum registro encontrado
        </div>

        <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0 && visualizacao === 'lista'">
            <div
                class="mybp-card"
                v-for="item in lista"
                :key="item.id"
            >
                <div class="mybp-card-header-row">
                    <div class="mybp-card-left">
                        <span class="mybp-badge-id">#@{{ item.id }}</span>
                        <div class="mybp-card-titulo mybp-card-titulo--stacked">
                            <strong>@{{ nomeCandidato(item) }}</strong>
                            <div class="text-muted small mybp-card-subtitulo" v-if="cpfExibicao(item)">
                                CPF @{{ cpfExibicao(item) }}
                            </div>
                        </div>
                    </div>
                    <div class="mybp-card-right">
                        <div class="mybp-card-status-botoes">
                            <div class="mybp-card-status-item">
                                <span class="mybp-card-status-label">Colaborador</span>
                                <mybp-status-badge
                                    :variante="chaveSituacaoColaborador(item)"
                                    :texto="textoSituacaoColaborador(item)"
                                ></mybp-status-badge>
                            </div>
                            <div class="mybp-card-status-item">
                                <span class="mybp-card-status-label">Documentos</span>
                                <mybp-status-badge
                                    :variante="chaveStatusDocumento(item)"
                                    :texto="textoStatusDocumento(item)"
                                ></mybp-status-badge>
                            </div>
                        </div>
                        <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                            <a
                                class="mybp-btn-acoes-compact"
                                href="#"
                                role="button"
                                :id="`dropdownMenuLink_preadm_${item.id}`"
                                aria-haspopup="true"
                                :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                                @click.prevent.stop="toggleDropdown(item.id)"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div
                                class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                :class="{ show: isDropdownOpen(item.id) }"
                                :aria-labelledby="`dropdownMenuLink_preadm_${item.id}`"
                                @click="fecharDropdown"
                            >
                                <a class="dropdown-item" href="javascript://"
                                   @click.prevent="formVisualizar(item.id); $refs.janelaVisualizar?.abrirModal()">
                                    <i class="fa fa-search-plus mr-1"></i> Visualizar documentos
                                </a>
                                @can('privilegio_admissao_pre_admissao_reencaminhar_email')
                                    <a class="dropdown-item" href="javascript://"
                                       @click.prevent="formEnviarEmail(item.id); $refs.janelaEnviarEmail?.abrirModal()">
                                        <i class="fa fa-share-square mr-1"></i> Reenviar e-mail
                                    </a>
                                @endcan
                                <a
                                    v-if="!item.finalizado"
                                    class="dropdown-item text-success"
                                    href="javascript://"
                                    @click.prevent="abrirFormFinalizar(item.id); $refs.janelaFinalizar?.abrirModal()"
                                >
                                    <i class="fa fa-check-circle mr-1"></i> Finalizar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mybp-card-corpo" :class="classeCorpoStatus(item)">
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-briefcase"
                                label="Cargo / Vaga"
                                :valor="textoCampo(item.cargo_nome)"
                                :forte="true"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-map-marker-alt"
                                label="Local da vaga"
                                :valor="textoCampo(item.vaga_local)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-paperclip"
                                label="Anexos"
                                :valor="anexosExibicao(item)"
                            ></mybp-card-campo>
                        </div>
                    </section>
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-envelope"
                                label="E-mail"
                                :valor="textoCampo(item.candidato_email)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-phone"
                                label="Telefone"
                                :valor="textoCampo(item.contato_telefone)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-map"
                                label="UF currículo"
                                :valor="textoCampo(item.candidato_uf)"
                            ></mybp-card-campo>
                        </div>
                    </section>
                    <section class="mybp-card-secao" v-if="cliente_id === 1 || item.finalizado">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                v-if="cliente_id === 1"
                                icon="fas fa-building"
                                label="Cliente"
                                :valor="clienteExibicao(item)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                v-if="item.finalizado"
                                icon="fas fa-user-check"
                                label="Finalizado por"
                                :valor="textoCampo(item.quem_finalizou)"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                v-if="item.finalizado"
                                icon="fas fa-clock"
                                label="Data finalização"
                                :valor="textoCampo(item.data_finalizacao)"
                            ></mybp-card-campo>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div class="table-responsive mybp-tabela-wrap" v-show="!controle.carregando && lista.length > 0 && visualizacao === 'tabela'">
            <table class="tabela mybp-tabela">
                <thead>
                <tr>
                    <th>Cód</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Cargo / Vaga</th>
                    <th>Local da vaga</th>
                    <th>Anexos</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>UF</th>
                    <th v-if="cliente_id === 1">Cliente</th>
                    <th>Finalizado por</th>
                    <th>Data finalização</th>
                    <th>Colaborador</th>
                    <th>Documentos</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in lista" :key="'tab-' + item.id">
                    <td data-label="Cód">@{{ item.id }}</td>
                    <td class="mybp-col-texto" data-label="Nome">@{{ nomeCandidato(item) }}</td>
                    <td data-label="CPF">@{{ cpfExibicao(item) || 'Não informado' }}</td>
                    <td class="mybp-col-texto" data-label="Cargo / Vaga">@{{ textoCampo(item.cargo_nome) }}</td>
                    <td class="mybp-col-texto" data-label="Local da vaga">@{{ textoCampo(item.vaga_local) }}</td>
                    <td data-label="Anexos">@{{ anexosExibicao(item) }}</td>
                    <td class="mybp-col-texto" data-label="E-mail">@{{ textoCampo(item.candidato_email) }}</td>
                    <td data-label="Telefone">@{{ textoCampo(item.contato_telefone) }}</td>
                    <td data-label="UF">@{{ textoCampo(item.candidato_uf) }}</td>
                    <td v-if="cliente_id === 1" class="mybp-col-texto" data-label="Cliente">@{{ clienteExibicao(item) }}</td>
                    <td class="mybp-col-texto" data-label="Finalizado por">@{{ item.finalizado ? textoCampo(item.quem_finalizou) : '—' }}</td>
                    <td data-label="Data finalização">@{{ item.finalizado ? textoCampo(item.data_finalizacao) : '—' }}</td>
                    <td data-label="Colaborador">
                        <mybp-status-badge :variante="chaveSituacaoColaborador(item)" :texto="textoSituacaoColaborador(item)"></mybp-status-badge>
                    </td>
                    <td data-label="Documentos">
                        <mybp-status-badge :variante="chaveStatusDocumento(item)" :texto="textoStatusDocumento(item)"></mybp-status-badge>
                    </td>
                    <td data-label="Ações">
                        <div class="dropdown d-inline-block" :class="{ show: isDropdownOpen(item.id) }">
                            <a class="mybp-btn-acoes-compact" href="#" role="button" @click.prevent.stop="toggleDropdown(item.id); posicionarMenuAcoes($event)">
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu mybp-dropdown-menu dropdown-menu-right" :class="{ show: isDropdownOpen(item.id) }" @click="fecharDropdown">
                                <a class="dropdown-item" href="javascript://" @click.prevent="formVisualizar(item.id); $refs.janelaVisualizar?.abrirModal()">
                                    <i class="fa fa-search-plus mr-1"></i> Visualizar documentos
                                </a>
                                @can('privilegio_admissao_pre_admissao_reencaminhar_email')
                                    <a class="dropdown-item" href="javascript://" @click.prevent="formEnviarEmail(item.id); $refs.janelaEnviarEmail?.abrirModal()">
                                        <i class="fa fa-share-square mr-1"></i> Reenviar e-mail
                                    </a>
                                @endcan
                                <a v-if="!item.finalizado" class="dropdown-item text-success" href="javascript://" @click.prevent="abrirFormFinalizar(item.id); $refs.janelaFinalizar?.abrirModal()">
                                    <i class="fa fa-check-circle mr-1"></i> Finalizar
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao
            class="d-flex justify-content-center mt-3"
            id="controle"
            ref="componente"
            url="{{ route('g.admissao.preadm.atualizar') }}"
            :por-pagina="controle.dados.pages"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando"
        ></controle-paginacao>
    </div>

    <whatsapp-preview-modal
        v-model="previewWhatsappAberto"
        :tipo-mensagem="previewWhatsappTipo"
        :contexto="previewWhatsappContexto"
    />
@stop
@push('js')
    <script src="{{mix('js/g/admissao/preadmissao/app.js')}}"></script>
@endpush
