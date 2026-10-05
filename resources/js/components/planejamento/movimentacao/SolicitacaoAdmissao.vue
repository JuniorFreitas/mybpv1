<template>
    <div>
        <modal :id="hash" :titulo="tituloJanela" :size="90" :ref="hash">
            <template #conteudo>
                <preload v-show="preload" class="text-center"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i>Solicitação cadastrada com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i>Solicitação alterada com sucesso!</h4>
                </div>
                <form
                    v-if="!preload && !cadastrado && !atualizado"
                    :id="`form_${hash}`"
                    class="mybp-modal-form mybp-filtros-compactos"
                    onsubmit="return false"
                >
                    <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>

                    <fieldset class="mybp-modal-secao">
                        <legend>Colaborador</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`cargo_${hash}`">
                                        Cargo <span class="text-danger">*</span>
                                    </label>
                                    <autocomplete
                                        :caminho="`autocomplete/cargosEmpresa`"
                                        formsm
                                        :valido="form.cargo_id !== ''"
                                        v-model="form.autocomplete_label_cargo"
                                        placeholder="Selecione um cargo"
                                        :disabled="modalCamposBloqueados || editando"
                                        :id="`cargo_${hash}`"
                                        @onblur="resetaCampoCargo"
                                        @onselect="selecionaCargo"
                                    ></autocomplete>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`nome_pessoa_${hash}`">Nome do Colaborador</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.nome_pessoa"
                                        :id="`nome_pessoa_${hash}`"
                                        placeholder="Nome do colaborador a ser admitido"
                                        :disabled="modalCamposBloqueados"
                                        @blur="validaNomePessoa"
                                        @keyup="validaNomePessoa"
                                    />
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Lotação</legend>
                        <div class="row">
                            <div class="col-12 col-md-6" v-if="lista_ccs && temFilial">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`admissao-lotacao-${hash}`">
                                        Lotação (CNPJ) <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormLotacao"
                                            instance-id="form-admissao-lotacao"
                                            :input-id="`admissao-lotacao-${hash}`"
                                            v-model="formLotacaoCnpj"
                                            :options="formLotacaoOpcoes"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione a lotação..."
                                            empty-message="Nenhuma lotação encontrada."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-admissao-lotacao')"
                                            @select="onSelectFormLotacao"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div :class="temFilial ? 'col-12 col-md-6' : 'col-12'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`admissao-cc-${hash}`">
                                        Centro de Custo <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormCc"
                                            instance-id="form-admissao-cc"
                                            :input-id="`admissao-cc-${hash}`"
                                            v-model="formCentroCustoCombo"
                                            :options="formCentroCustoOpcoes"
                                            :disabled="modalCamposBloqueados || (temFilial && !formLotacaoCnpj)"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhum centro de custo para esta lotação."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-admissao-cc')"
                                            @select="onSelectFormCentroCusto"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Solicitação</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`admissao-tipo-${hash}`">
                                        Tipo de admissão <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormTipo"
                                            instance-id="form-admissao-tipo"
                                            :input-id="`admissao-tipo-${hash}`"
                                            v-model="form.tipo_contrato"
                                            :options="formTipoContratoOpcoes"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="30"
                                            @opening="fecharOutrosComboboxes('form-admissao-tipo')"
                                            @select="limparComboboxInvalido('admissao-tipo-' + hash)"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                                    <label class="mybp-label">Data admissão <span class="text-danger">*</span></label>
                                    <datepicker
                                        :id="`admissao-data-${hash}`"
                                        formsm
                                        label=""
                                        class="corrigiDatepicker"
                                        v-model="form.data_admissao"
                                        :disabled="modalCamposBloqueados"
                                        @onselect="onSelectDataAdmissao"
                                    ></datepicker>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">
                                        Salário <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-mascara:dinheiro
                                        onblur="valida_dinheiro(this, 1)"
                                        :disabled="modalCamposBloqueados"
                                        v-model="form.salario_format"
                                    />
                                </div>
                            </div>

                            <gestoraprovacao
                                label="Gestor Aprovação"
                                :obrigatorio="true"
                                :model="form"
                                :verifica="modalCamposBloqueados"
                                :hash="hash"
                            ></gestoraprovacao>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Detalhes</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.obs"
                                        rows="3"
                                        placeholder="Informações relevantes sobre a admissão"
                                        :readonly="modalCamposBloqueados"
                                    ></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Anexos</label>
                                    <upload
                                        :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :url="url_anexo"
                                        :tipos="mimes"
                                        :leitura="!podeanexar"
                                        label="Selecionar"
                                        @onProgresso="anexoUploadAndamento = true"
                                        @onFinalizado="anexoUploadAndamento = false"
                                    ></upload>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="!form.data_aprovacao && !cadastrando">
                        Esta solicitação ainda não foi aprovada ou reprovada!
                    </div>

                    <fieldset v-if="visualizar || aprovando || aprovandoRh" class="mybp-modal-secao">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.user_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_gestor || form.status_aprovacao }} por:
                                    {{ form.user_aprovacao.nome }} em {{ form.data_aprovacao }}
                                </p>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`admissao-status-gestor-${hash}`">
                                        Status <span class="text-danger" v-if="aprovando">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusGestor"
                                            instance-id="form-admissao-status-gestor"
                                            :input-id="`admissao-status-gestor-${hash}`"
                                            v-model="form.status_aprovacao"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovando || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-admissao-status-gestor')"
                                            @select="limparComboboxInvalido('admissao-status-gestor-' + hash)"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :readonly="!aprovando || aprovandoRh"
                                        v-model="form.obs_aprovacao"
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="aprovandoExtra">
                        Esta solicitação ainda não foi aprovada ou reprovada pela {{ nomeAprovacaoExtra }}!
                    </div>

                    <fieldset v-if="visualizar || aprovandoExtra" class="mybp-modal-secao">
                        <div v-if="!temAprovacaoExtra" class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Esta empresa não possui aprovação extra configurada.
                        </div>
                        <legend v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</legend>
                        <div class="row" v-if="temAprovacaoExtra">
                            <div v-if="!aprovandoExtra && form.aprovacao_extra_nome" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_extra }} por: {{ form.aprovacao_extra_nome }} em
                                    {{ form.data_aprovacao_extra }}
                                </p>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`admissao-status-extra-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoExtra">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusExtra"
                                            instance-id="form-admissao-status-extra"
                                            :input-id="`admissao-status-extra-${hash}`"
                                            v-model="form.status_aprovacao_extra"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-admissao-status-extra')"
                                            @select="limparComboboxInvalido('admissao-status-extra-' + hash)"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :readonly="!aprovandoExtra || aprovandoRh"
                                        v-model="form.obs_aprovacao_extra"
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="aprovandoRh">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                    <fieldset v-if="visualizar || aprovandoRh" class="mybp-modal-secao">
                        <legend>Aprovação RH</legend>
                        <div class="row">
                            <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao.nome }} em
                                    {{ form.data_aprovacao_rh }}
                                </p>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`admissao-status-rh-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoRh">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusRh"
                                            instance-id="form-admissao-status-rh"
                                            :input-id="`admissao-status-rh-${hash}`"
                                            v-model="form.status_aprovacao_rh"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-admissao-status-rh')"
                                            @select="limparComboboxInvalido('admissao-status-rh-' + hash)"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :readonly="!aprovandoRh"
                                        v-model="form.obs_rh"
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="cadastrando && !preload" @click.prevent="cadastrar">
                    <i class="fa fa-save"></i> Cadastrar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovando && !preload" @click.prevent="aprovarGestor">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovandoExtra && !preload" @click.prevent="aprovarExtra">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovandoRh && !preload" @click.prevent="aprovarRh">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <modal id="janelaAtualizaStatus" titulo="Deseja APROVAR ou REPROVAR todos os colaboradores selecionados?" :centralizada="true" label-fechar="Fechar" ref="modal_janelaAtualizaStatus">
            <template #conteudo>
                <div class="col-12">
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea class="form-control" v-model="formConfirmacao.obs_aprovacao" cols="5" rows="5"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-sm mr-1 btn-success" @click="confirmaAtualizacaoStatus('aprovado')">APROVAR</button>
                    <button type="button" class="btn btn-sm mr-1 btn-danger" @click="confirmaAtualizacaoStatus('reprovado')">REPROVAR</button>
                </div>
            </template>
        </modal>

        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
            :desabilitado="controle.carregando"
            @submit="atualizar"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <date-range-filter
                    v-model:enabled="controle.dados.filtroPeriodo"
                    v-model:start-date="controle.dados.dataInicio"
                    v-model:end-date="controle.dados.dataFim"
                    :disabled="controle.carregando"
                    :id-suffix="'admissao-' + hash"
                    label="Período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-busca">
                            Colaborador / CPF
                            <span v-if="buscaUnificadaEhCpf" class="admissao-filtro-hint">CPF</span>
                        </label>
                        <input
                            id="admissao-filtro-busca"
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
                        <label class="mybp-label" for="admissao-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="admissao-status"
                                input-id="admissao-filtro-status"
                                v-model="controle.dados.campoStatusAprovacao"
                                :options="opcoesStatus"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhum status encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('admissao-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-tipo-contrato">Tipo contrato</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroTipoContrato"
                                instance-id="admissao-tipo-contrato"
                                input-id="admissao-filtro-tipo-contrato"
                                v-model="controle.dados.tipo_contrato"
                                :options="opcoesTipoContrato"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os tipos"
                                empty-message="Nenhum tipo encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('admissao-tipo-contrato')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-cnpj">Lotação (CNPJ)</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="admissao-cnpj"
                                input-id="admissao-filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="opcoesCnpj"
                                :disabled="controle.carregando"
                                placeholder-blur="Todas as lotações"
                                empty-message="Nenhuma lotação encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('admissao-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="lista_ccs" :class="temFilial ? 'col-12 col-md-8' : 'col-12'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                instance-id="admissao-cc"
                                input-id="admissao-filtro-cc"
                                v-model="controle.dados.campoCentroCusto"
                                :options="opcoesCentroCusto"
                                :disabled="controle.carregando || !opcoesCentroCusto.length"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro de custo encontrado."
                                :max-results="200"
                                @opening="fecharOutrosComboboxes('admissao-cc')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-ordenacao">Ordenar por</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroOrdenacao"
                                instance-id="admissao-ordenacao"
                                input-id="admissao-filtro-ordenacao"
                                v-model="controle.dados.ordenacao"
                                :options="opcoesOrdenacao"
                                :disabled="controle.carregando"
                                placeholder-blur="Mais recentes"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('admissao-ordenacao')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-pages">Por página</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPages"
                                instance-id="admissao-pages"
                                input-id="admissao-filtro-pages"
                                v-model="campoPagesCombo"
                                :options="opcoesPages"
                                :disabled="controle.carregando"
                                placeholder-blur="20"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('admissao-pages')"
                                @select="onSelectFiltro"
                            />
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
                    class="btn btn-sm btn-primary"
                    :disabled="controle.carregando"
                    @click.prevent="formNovo(); $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                >
                    Solicitar
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click.prevent="exportaExcel()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    v-show="selecionados.length > 0"
                    :disabled="selecionados.length === 0"
                    @click.prevent="$refs.modal_janelaAtualizaStatus && $refs.modal_janelaAtualizaStatus.abrirModal()"
                >
                    Atualizar Status <span class="badge badge-light">{{ selecionados.length }}</span>
                </button>
            </template>
        </FiltroListagem>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <!-- Cards -->
            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="mybp-card" v-for="item in lista" :key="item.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ item.id }}</span>
                            <div class="mybp-card-titulo">
                                <strong>{{ tituloCardLista(item) }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <mybp-status-badge
                                :variante="chaveStatusLista(item)"
                                :texto="textoStatusLista(item)"
                            />
                            <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                                <a
                                    class="mybp-btn-acoes-compact"
                                    href="#"
                                    role="button"
                                    :id="`dropdownMenuLink_${item.id}`"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(item.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(item.id) }"
                                    :aria-labelledby="`dropdownMenuLink_${item.id}`"
                                    @click="fecharDropdown"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação Gestor"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = true; aprovandoExtra = false; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="item.user_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor"
                                    >
                                        <i class="fa fa-user-check mr-1"></i> Aprovação Gestor
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="temAprovacaoExtra && item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra && podeAprovarExtra"
                                    >
                                        <i class="fa fa-user-check mr-1"></i> {{ nomeAprovacaoExtra }}
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="
                                            ((temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                                (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')) &&
                                            !item.aprovado_via_script &&
                                            item.rh_aprovacao_id === null &&
                                            aprovaRh
                                        "
                                    >
                                        <i class="fa fa-users mr-1"></i> Aprovação RH
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                    >
                                        <i class="fa fa-search mr-1"></i> Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-corpo" :class="classeBordaStatusLista(item)">
                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-calendar-check"
                                    label="Data admissão"
                                    :valor="item.data_admissao"
                                    forte
                                />
                                <mybp-card-campo
                                    icon="fas fa-file-contract"
                                    label="Tipo de contrato"
                                    :valor="item.tipo_contrato"
                                />
                                <mybp-card-campo
                                    icon="fas fa-dollar-sign"
                                    label="Salário"
                                    :valor="item.salario_format"
                                />
                            </div>
                        </section>

                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-building"
                                    label="Lotação"
                                    :valor="item.lotacao"
                                />
                                <mybp-card-campo
                                    icon="fas fa-sitemap"
                                    label="Centro de custo"
                                    :valor="centroCustoLista(item)"
                                />
                                <mybp-card-campo
                                    icon="fas fa-user-edit"
                                    label="Solicitante"
                                    :valor="solicitanteLista(item)"
                                />
                            </div>
                        </section>

                        <section class="mybp-card-secao mybp-card-secao--fluxo">
                            <div class="mybp-card-secao__titulo">
                                <i class="fas fa-project-diagram" aria-hidden="true"></i> Fluxo de aprovação
                            </div>
                            <mybp-fluxo-aprovacao :steps="fluxoStepsLista(item)" />
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <controle-paginacao
            class="d-flex justify-content-center"
            id="controle"
            ref="componente"
            :url="urlPaginacao"
            :por-pagina="controle.dados.pages"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando"
        />
    </div>
</template>

<script>
import gestoraprovacao from '../../GestorAprovacao'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Utils from '../../../mixins/Utils'
import Upload from '../../Upload'
import configuracoes from '../../../mixins/Configuracoes'
import DateRangeFilter from '../../DateRangeFilter.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'
import ComboboxValidation from '../../../mixins/ComboboxValidation'
import {
    etapaAtualFluxoAprovacao,
    normalizarStatusUrlFluxo,
    opcoesStatusFluxoAtual,
    textoStatusFluxo,
    varianteStatusFluxo
} from '../../../utils/opcoesStatusFluxoAprovacao'

export default {
    mixins: [ExportacaoMixin, Utils, configuracoes, ComboboxValidation],
    inject: {
        atualizarUrlMovimentacao: { default: () => () => {} }
    },
    components: {
        gestoraprovacao,
        Upload,
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem,
        MybpCardCampo,
        MybpFluxoAprovacao,
        MybpStatusBadge
    },
    data() {
        return {
            tituloJanela: 'Solicitacao de admissão',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            visualizando: false,
            aprovar_por_gestor: false,
            aprovando: false,
            aprovandoRh: false,
            aprovandoExtra: false,
            aprovaGestor: false,
            aprovaRh: false,
            podeAprovarExtra: false,
            temAprovacaoExtra: false,
            nomeAprovacaoExtra: '',
            preloadExportacao: false,

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/export`,
            url_anexo: `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`,
            anexoUploadAndamento: false,
            podeanexar: false,
            mimes: [],

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            selecionados: [],
            selecionaTudo: false,

            dropdownAbertoKey: null,

            formConfirmacao: {
                selecionados: [],
                obs_aprovacao: '',
                status_aprovacao: ''
            },

            formConfirmacaoDefault: null,

            form: {
                centro_custo_id: '',
                filial: false,
                centro_custo_filial_id: '',
                tipo_contrato: '',

                cargo_id: '',
                autocomplete_label_cargo: '',
                autocomplete_label_cargo_anterior: '',

                nome_pessoa: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',

                data_admissao: '',
                salario: '',
                salario_format: '0,00',

                user_id: '',
                solicitante: '',
                status: '',
                obs: '',

                obs_aprovacao: '',
                status_aprovacao: '',
                obs_aprovacao_extra: '',
                status_aprovacao_extra: '',
                data_aprovacao_extra: '',
                aprovacao_extra_nome: '',
                anexos: [],
                anexosDel: [],

                rh_aprovacao_id: '',
                obs_rh: '',
                status_aprovacao_rh: '',
                data_aprovacao_rh: '',
                aprovado_via_script: false
            },

            formDefault: null,
            lista: [],
            centro_custos: [],
            tiposAdmissao: [],
            lista_ccs: null,
            formLotacaoCnpj: '',

            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: '',
                    campoCPF: '',
                    campoStatusAprovacao: '',
                    tipo_contrato: '',
                    campoCnpj: '',
                    campoCentroCusto: '',
                    filtroPeriodo: false,
                    dataInicio: '',
                    dataFim: '',
                    token: '',
                    ordenacao: 'created_at_desc'
                }
            }
        }
    },
    mounted() {
        this.urlParamGet()
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formConfirmacaoDefault = _.cloneDeep(this.formConfirmacao)
        this.carregarTiposAdmissao()
        this.$nextTick(() => this.atualizar())
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    watch: {
        'controle.dados': {
            handler() {
                if (this._syncUrlTimer) clearTimeout(this._syncUrlTimer)
                this._syncUrlTimer = setTimeout(() => this.syncUrlFiltros(), 400)
            },
            deep: true
        }
    },
    computed: {
        modalCamposBloqueados() {
            return this.visualizar || this.aprovando || this.aprovandoExtra || this.aprovandoRh
        },
        formStatusAprovacaoOpcoes() {
            return [
                { value: '', label: 'Selecione...' },
                { value: 'aprovado', label: 'Aprovar' },
                { value: 'reprovado', label: 'Reprovar' }
            ]
        },
        formListaCentroCustoLotacao() {
            if (!this.lista_ccs) return []
            if (this.temFilial) {
                if (!this.formLotacaoCnpj) return []
                return this.lista_ccs.centros_custos[this.formLotacaoCnpj] || []
            }
            const keys = Object.keys(this.lista_ccs.centros_custos || {})
            return keys.length ? this.lista_ccs.centros_custos[keys[0]] || [] : []
        },
        formLotacaoOpcoes() {
            const opts = [{ value: '', label: 'Selecione a lotação...' }]
            if (!this.lista_ccs || !this.lista_ccs.cnpjs) return opts
            Object.keys(this.lista_ccs.cnpjs).forEach((key) => {
                const item = this.lista_ccs.cnpjs[key]
                opts.push({
                    value: key,
                    label: `${item.nome_fantasia} - ${item.cnpj}`,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        formCentroCustoOpcoes() {
            const opts = [{ value: '', label: 'Selecione...' }]
            ;(this.formListaCentroCustoLotacao || []).forEach((item) => {
                const value = item.matriz ? item.id : item.filial_id
                if (value == null || value === '') return
                opts.push({
                    value: String(value),
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        formCentroCustoCombo: {
            get() {
                if (this.form.filial === true || this.form.filial === 1 || this.form.filial === '1') {
                    return this.form.centro_custo_filial_id != null && this.form.centro_custo_filial_id !== ''
                        ? String(this.form.centro_custo_filial_id)
                        : ''
                }
                return this.form.centro_custo_id != null && this.form.centro_custo_id !== ''
                    ? String(this.form.centro_custo_id)
                    : ''
            },
            set(valor) {
                if (valor == null || valor === '') {
                    this.limparFormCentroCusto()
                    return
                }
                this.aplicarCentroCustoPorValorCombo(String(valor))
            }
        },
        formTipoContratoOpcoes() {
            const opts = [{ value: '', label: 'Selecione...' }]
            ;(this.tiposAdmissao || []).forEach((item) => {
                opts.push({ value: item, label: item })
            })
            return opts
        },
        naoAprovados() {
            return this.lista.filter((item) => {
                if (item.status_aprovacao === null) {
                    return item.id
                }
            })
        },
        tudoMarcado() {
            let totalAprovado = this.naoAprovados.length
            let totalEncontrado = 0

            if (totalAprovado === 0) {
                return false
            }

            this.naoAprovados.forEach((item) => {
                let id = item.id
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                } else {
                    return false
                }
            })
            let resultado = totalAprovado === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        },
        por_pagina() {
            return [20, 50, 100, 150]
        },
        paramsExport() {
            return this.controle.dados
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            let total = [d.campoBusca, d.campoCPF, d.campoStatusAprovacao, d.tipo_contrato, d.campoCentroCusto].filter(
                (v) => v !== '' && v !== null && v !== undefined
            ).length
            if (this.temFilial && d.campoCnpj) total++
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) total++
            if (d.ordenacao && d.ordenacao !== 'created_at_desc') total++
            if (Number(d.pages) !== 20) total++
            return total
        },
        campoBuscaUnificada() {
            const d = this.controle.dados
            if (d.campoCPF) return d.campoCPF
            return d.campoBusca || ''
        },
        buscaUnificadaEhCpf() {
            return !!this.controle.dados.campoCPF
        },
        filtroListaCentroCustoCnpj() {
            if (!this.lista_ccs) return []
            if (this.controle.dados.campoCnpj !== '' && this.temFilial) {
                return this.lista_ccs.centros_custos[this.controle.dados.campoCnpj] || []
            }
            if (!this.temFilial) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                return keys.length ? this.lista_ccs.centros_custos[keys[0]] || [] : []
            }
            const all = []
            Object.values(this.lista_ccs.centros_custos || {}).forEach((lista) => {
                ;(lista || []).forEach((item) => all.push(item))
            })
            return all
        },
        opcoesCnpj() {
            const opts = [{ value: '', label: 'Todas as lotações' }]
            if (!this.lista_ccs || !this.lista_ccs.cnpjs) return opts
            Object.keys(this.lista_ccs.cnpjs).forEach((key) => {
                const item = this.lista_ccs.cnpjs[key]
                opts.push({
                    value: key,
                    label: `${item.nome_fantasia} - ${item.cnpj}`,
                    meta: item.cnpj
                })
            })
            return opts
        },
        opcoesCentroCusto() {
            const opts = [{ value: '', label: 'Todos os centros' }]
            ;(this.filtroListaCentroCustoCnpj || []).forEach((item) => {
                const value = item.matriz ? item.id : item.filial_id
                if (value == null || value === '') return
                opts.push({
                    value: String(value),
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        opcoesStatus() {
            return opcoesStatusFluxoAtual({
                temAprovacaoExtra: this.temAprovacaoExtra,
                nomeAprovacaoExtra: this.nomeAprovacaoExtra
            })
        },
        opcoesTipoContrato() {
            const opts = [{ value: '', label: 'Todos os tipos' }]
            ;(this.tiposAdmissao || []).forEach((item) => {
                opts.push({ value: item, label: item })
            })
            return opts
        },
        opcoesOrdenacao() {
            return [
                { value: 'created_at_desc', label: 'Mais recentes' },
                { value: 'created_at_asc', label: 'Mais antigos' },
                { value: 'updated_at_desc', label: 'Última modificação' }
            ]
        },
        opcoesPages() {
            return this.por_pagina.map((n) => ({ value: String(n), label: String(n) }))
        },
        campoPagesCombo: {
            get() {
                return String(this.controle.dados.pages || 20)
            },
            set(valor) {
                this.controle.dados.pages = parseInt(valor, 10) || 20
            }
        }
    },
    methods: {
        toggleDropdown(itemId) {
            if (!itemId) {
                return
            }
            const key = `mov_admissao:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        tituloCardLista(item) {
            const cargo = item?.cargo?.nome || 'Cargo não informado'
            if (item?.nome_pessoa) return `${cargo} — ${item.nome_pessoa}`
            return cargo
        },
        centroCustoLista(item) {
            return item?.centro_custo?.label || 'Não informado'
        },
        solicitanteLista(item) {
            return item?.user_cadastrou?.nome || 'Não informado'
        },
        etapaAtualLista(item) {
            return etapaAtualFluxoAprovacao(item, {
                campoGestor: 'status_aprovacao',
                temAprovacaoExtra: this.temAprovacaoExtra
            })
        },
        chaveStatusLista(item) {
            return varianteStatusFluxo(this.etapaAtualLista(item))
        },
        classeBordaStatusLista(item) {
            return `mybp-card-corpo--${this.chaveStatusLista(item)}`
        },
        textoStatusLista(item) {
            return textoStatusFluxo(this.etapaAtualLista(item), this.nomeAprovacaoExtra)
        },
        fluxoStepsLista(item) {
            if (!item) return []
            const steps = [
                {
                    key: 'solicitante',
                    label: 'Solicitante',
                    status: 'aprovado',
                    nome: item.user_cadastrou?.nome,
                    data: item.created_at
                },
                {
                    key: 'gestor',
                    label: 'Gestor',
                    status:
                        item.status_aprovacao === 'aprovado'
                            ? 'aprovado'
                            : item.status_aprovacao === 'reprovado'
                              ? 'reprovado'
                              : 'aguardando',
                    nome: item.user_aprovacao?.nome,
                    data: item.data_aprovacao
                }
            ]

            if (this.temAprovacaoExtra) {
                let statusExtra = 'pendente'
                if (item.status_aprovacao === 'reprovado') {
                    statusExtra = 'cancelado'
                } else if (item.status_aprovacao_extra === 'aprovado') {
                    statusExtra = 'aprovado'
                } else if (item.status_aprovacao_extra === 'reprovado') {
                    statusExtra = 'reprovado'
                } else if (item.status_aprovacao === 'aprovado') {
                    statusExtra = 'aguardando'
                }
                steps.push({
                    key: 'extra',
                    label: this.nomeAprovacaoExtra || 'Extra',
                    status: statusExtra,
                    nome: item.aprovacao_extra_nome,
                    data: item.data_aprovacao_extra,
                    statusTexto: statusExtra === 'cancelado' ? 'Cancelada' : undefined
                })
            }

            let statusRh = 'pendente'
            if (
                item.status_aprovacao === 'reprovado' ||
                (this.temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')
            ) {
                statusRh = 'cancelado'
            } else if (item.status_aprovacao_rh === 'aprovado') {
                statusRh = 'aprovado'
            } else if (item.status_aprovacao_rh === 'reprovado') {
                statusRh = 'reprovado'
            } else if (
                (this.temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                (!this.temAprovacaoExtra && item.status_aprovacao === 'aprovado')
            ) {
                statusRh = 'aguardando'
            }

            steps.push({
                key: 'rh',
                label: 'RH',
                status: statusRh,
                nome: item.rh_aprovacao?.nome,
                data: item.data_aprovacao_rh,
                statusTexto: statusRh === 'cancelado' ? 'Cancelada' : undefined
            })

            return steps
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `mov_admissao:${itemId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        onClickOutside(event) {
            if (event && event.target && event.target.closest && event.target.closest('.dropdown')) {
                return
            }
            this.dropdownAbertoKey = null
        },
        carregarTiposAdmissao() {
            axios
                .get(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/tipos-contrato`)
                .then((res) => {
                    this.tiposAdmissao = Array.isArray(res.data) ? res.data : []
                })
                .catch(() => {
                    this.tiposAdmissao = []
                })
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            this.controle.dados.token = urlParams.get('token') || ''
            if (urlParams.get('pages')) this.controle.dados.pages = parseInt(urlParams.get('pages'), 10) || 20
            if (urlParams.get('ordenacao')) this.controle.dados.ordenacao = urlParams.get('ordenacao')
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoCPF')) this.controle.dados.campoCPF = urlParams.get('campoCPF')
            this.controle.dados.campoStatusAprovacao = normalizarStatusUrlFluxo(urlParams.get('campoStatusAprovacao'))
            if (urlParams.get('tipo_contrato')) this.controle.dados.tipo_contrato = urlParams.get('tipo_contrato')
            if (urlParams.get('campoCentroCusto')) this.controle.dados.campoCentroCusto = urlParams.get('campoCentroCusto')
            if (urlParams.get('dataInicio')) this.controle.dados.dataInicio = urlParams.get('dataInicio')
            if (urlParams.get('dataFim')) this.controle.dados.dataFim = urlParams.get('dataFim')
            if (urlParams.get('dataInicio') || urlParams.get('dataFim')) this.controle.dados.filtroPeriodo = true
        },
        syncUrlFiltros() {
            if (typeof this.atualizarUrlMovimentacao !== 'function') return
            const d = this.controle.dados
            const params = { pages: d.pages || 20, ordenacao: d.ordenacao || 'created_at_desc' }
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoCPF) params.campoCPF = d.campoCPF
            if (d.campoStatusAprovacao) params.campoStatusAprovacao = d.campoStatusAprovacao
            if (d.tipo_contrato) params.tipo_contrato = d.tipo_contrato
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            if (d.token) params.token = d.token
            this.atualizarUrlMovimentacao(params)
        },
        limparFormCentroCusto() {
            this.form.centro_custo_id = ''
            this.form.filial = false
            this.form.centro_custo_filial_id = ''
        },

        onSelectFormLotacao() {
            this.limparFormCentroCusto()
            this.limparComboboxInvalido(`admissao-lotacao-${this.hash}`)
        },

        aplicarCentroCustoPorValorCombo(valor) {
            const lista = this.formListaCentroCustoLotacao || []
            const item = _.find(lista, (cc) => {
                const chave = cc.matriz ? String(cc.id) : String(cc.filial_id)
                return chave === String(valor)
            })
            if (!item) {
                this.limparFormCentroCusto()
                return
            }
            this.form.centro_custo_id = String(item.id)
            if (item.matriz) {
                this.form.filial = false
                this.form.centro_custo_filial_id = ''
            } else {
                this.form.filial = true
                this.form.centro_custo_filial_id = String(item.filial_id)
            }
        },

        onSelectFormCentroCusto() {
            if (this.formCentroCustoCombo) {
                this.aplicarCentroCustoPorValorCombo(this.formCentroCustoCombo)
            } else {
                this.limparFormCentroCusto()
            }
            this.limparComboboxInvalido(`admissao-cc-${this.hash}`)
        },

        normalizarDataBr(valor) {
            if (valor == null || valor === '' || valor === 'Invalid date') {
                return ''
            }
            const texto = String(valor).trim()
            if (!texto || texto === 'Invalid date') {
                return ''
            }
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(texto)) {
                return texto
            }
            const iso = texto.match(/^(\d{4})-(\d{2})-(\d{2})/)
            if (iso) {
                return `${iso[3]}/${iso[2]}/${iso[1]}`
            }
            return texto
        },

        onSelectDataAdmissao(valor) {
            const texto = typeof valor === 'string' ? valor : valor && valor.value ? valor.value : ''
            this.form.data_admissao = this.normalizarDataBr(texto)
            this.limparCampoDataInvalido(`admissao-data-${this.hash}`)
        },

        sincronizarDataAdmissaoDoInput() {
            const seletor = `#admissao-data-${this.hash}`
            let valor = ''
            const input = document.querySelector(seletor)
            if (input && input.value) {
                valor = String(input.value).trim()
            } else {
                const fallback = $(`#${this.hash} .mybp-modal-campo-data input:text`).first()
                if (fallback.length) {
                    valor = String(fallback.val() || '').trim()
                }
            }
            const normalizado = this.normalizarDataBr(valor)
            if (normalizado) {
                this.form.data_admissao = normalizado
            } else if (this.form.data_admissao === 'Invalid date') {
                this.form.data_admissao = ''
            }
        },

        resolverLotacaoDoForm() {
            this.formLotacaoCnpj = ''
            if (!this.lista_ccs || !this.lista_ccs.centros_custos) {
                return
            }

            const ehFilial =
                this.form.filial === true || this.form.filial === 1 || this.form.filial === '1'
            const ccId = this.form.centro_custo_id != null ? String(this.form.centro_custo_id) : ''
            const ccfId =
                this.form.centro_custo_filial_id != null ? String(this.form.centro_custo_filial_id) : ''

            if (!this.temFilial) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                this.formLotacaoCnpj = keys[0] || ''
                return
            }

            Object.keys(this.lista_ccs.centros_custos).some((cnpjKey) => {
                const lista = this.lista_ccs.centros_custos[cnpjKey] || []
                const encontrado = _.find(lista, (item) => {
                    if (ehFilial) {
                        return String(item.filial_id) === ccfId
                    }
                    return item.matriz && String(item.id) === ccId
                })
                if (encontrado) {
                    this.formLotacaoCnpj = cnpjKey
                    return true
                }
                return false
            })
        },

        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.naoAprovados.map((item) => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.naoAprovados.map((item) => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },
        confirmaAtualizacaoStatus(confirmacao) {
            this.preloadAtualizacao = true
            this.formConfirmacao.status_aprovacao = confirmacao
            this.formConfirmacao.selecionados.push(this.selecionados)

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/atualizacao-status`, this.formConfirmacao)
                .then((res) => {
                    this.preloadAtualizacao = false
                    this.$refs.modal_janelaAtualizaStatus && this.$refs.modal_janelaAtualizaStatus.fecharModal()
                    mostraSucesso('Status atualizados com sucesso!')
                    this.selecionados = []
                    this.formConfirmacao = _.cloneDeep(this.formConfirmacaoDefault) //copia
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                })
                .catch((error) => {
                    this.preloadAtualizacao = false
                })
        },
        selecionaCargo(obj) {
            this.form.cargo_id = obj.id
            this.form.autocomplete_label_cargo = obj.label
            this.form.autocomplete_label_cargo_anterior = obj.label
        },
        resetaCampoCargo() {
            if (this.form.autocomplete_label_cargo_anterior !== this.form.autocomplete_label_cargo) {
                this.form.autocomplete_label_cargo_anterior = ''
                this.form.autocomplete_label_cargo = ''
                this.form.cargo_id = ''

                setTimeout(() => {
                    if (this.form.cargo_id === '') {
                        valida_campo_vazio($(`#cargo_${this.hash}`), 1)
                        $(`#${this.hash} #cargo_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Cargo não pode ficar vazio')
                    }
                }, 100)
            }
        },

        listaCentroCusto() {
            axios
                .post(`${URL_PUBLICO}/centro-custos/`)
                .then((res) => {
                    this.centro_custos = res.data.centro_custos
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        validaNomePessoa(event) {
            const el = event && event.target ? event.target : document.getElementById(`nome_pessoa_${this.hash}`)
            if (!el) return
            el.classList.remove('is-invalid')
        },

        formNovo() {
            this.cadastrando = true
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.visualizar = false
            this.podeanexar = true

            this.tituloJanela = 'Solicitação de admissão'

            formReset()
            setupCampo()
            this.form = _.cloneDeep(this.formDefault) //copia
            this.formLotacaoCnpj = ''
            if (!this.temFilial && this.lista_ccs && this.lista_ccs.centros_custos) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                this.formLotacaoCnpj = keys[0] || ''
            }
        },

        cadastrar() {
            if (!this.validarCamposSolicitacao()) {
                return false
            }

            this.preload = true

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista`, this.form)
                .then((response) => {
                    this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                    let data = response.data
                    mostraSucesso('', 'Solicitação registrada com sucesso!')
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        formOpen(id, so_visualizar = false) {
            Object.assign(this.form, this.formDefault)
            this.form.id = id
            this.cadastrado = false
            this.atualizado = false
            this.cadastrando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false

            this.tituloJanela = `#${id}`

            formReset()
            this.preload = true

            axios
                .get(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)
                    if (data.centro_custo_id != null && data.centro_custo_id !== '') {
                        this.form.centro_custo_id = String(data.centro_custo_id)
                    } else {
                        this.form.centro_custo_id = ''
                    }
                    this.form.filial = !!(data.filial === true || data.filial === 1 || data.filial === '1')
                    if (this.form.filial && data.centro_custo_filial_id != null && data.centro_custo_filial_id !== '') {
                        this.form.centro_custo_filial_id = String(data.centro_custo_filial_id)
                    } else {
                        this.form.centro_custo_filial_id = ''
                    }
                    this.form.data_admissao = this.normalizarDataBr(data.data_admissao)
                    this.resolverLotacaoDoForm()

                    this.tituloJanela = `#${id} Solicitação de admissão`
                    if (this.aprovando) {
                        this.form.status_aprovacao = data.status_aprovacao === null ? '' : data.status_aprovacao
                        this.form.observacao = data.status_aprovacao === null ? '' : data.observacao
                    }
                    this.editando = true
                    this.preload = false
                    this.sovizualiza(so_visualizar)
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        sovizualiza(so_visualizar) {
            if (so_visualizar) {
                setTimeout(() => {
                    $(`#${this.hash} .mybp-modal-form :input`).attr('disabled', 'true')
                }, 100)
            }
            this.visualizando = so_visualizar
        },

        alterar() {
            if (!this.validarCamposSolicitacao()) {
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${this.form.id}`, this.form)
                .then((response) => {
                    this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                    let data = response.data
                    mostraSucesso('', 'Solicitação alterada com sucesso!')
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        validarCamposSolicitacao() {
            this.sincronizarDataAdmissaoDoInput()
            if (this.form.data_admissao === 'Invalid date') {
                this.form.data_admissao = ''
            }

            if (this.modalCamposBloqueados) {
                return true
            }

            if (!this.form.cargo_id) {
                valida_campo_vazio($(`#cargo_${this.hash}`), 1)
                $(`#${this.hash} #cargo_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo CARGO não pode ficar vazio')
                this.resetaCampoCargo()
                return false
            }

            if (this.temFilial) {
                if (
                    !this.exigirCombobox(this.formLotacaoCnpj, `admissao-lotacao-${this.hash}`, {
                        toastMsg: 'Selecione a lotação'
                    })
                ) {
                    return false
                }
            }

            if (
                !this.exigirCombobox(this.form.centro_custo_id || this.formCentroCustoCombo, `admissao-cc-${this.hash}`, {
                    toastMsg: 'Selecione o centro de custo'
                })
            ) {
                return false
            }

            if (this.form.filial && !this.form.centro_custo_filial_id) {
                mostraErro('', 'Centro de custo de filial inválido')
                return false
            }

            if (
                !this.exigirCombobox(this.form.tipo_contrato, `admissao-tipo-${this.hash}`, {
                    toastMsg: 'Selecione o tipo de admissão'
                })
            ) {
                return false
            }

            const dataId = `admissao-data-${this.hash}`
            if (
                !this.exigirCampoData(this.form.data_admissao, dataId, {
                    toastMsg: 'Campo DATA ADMISSÃO não pode ficar vazio'
                })
            ) {
                return false
            }

            if (!this.form.gestor_id) {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1)
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo GESTOR não pode ficar vazio')
                this.resetaCampoGestor()
                return false
            }

            return this.validarInputsAtivosVisiveis(this.hash, { preservarIds: [dataId] })
        },

        aprovarGestor() {
            const statusId = `admissao-status-gestor-${this.hash}`
            if (
                !this.exigirCombobox(this.form.status_aprovacao, statusId, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }

            this.preload = true
            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${this.form.id}/aprovar`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        aprovarExtra() {
            const statusId = `admissao-status-extra-${this.hash}`
            if (
                !this.exigirCombobox(this.form.status_aprovacao_extra, statusId, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }

            this.preload = true
            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${this.form.id}/aprovar-extra`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        aprovarRh() {
            const statusId = `admissao-status-rh-${this.hash}`
            if (
                !this.exigirCombobox(this.form.status_aprovacao_rh, statusId, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            this.preload = true
            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${this.form.id}/aprovarrh`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        carregou(dados) {
            this.lista = dados.itens
            this.aprovar_por_gestor = dados.aprovar_por_gestor
            this.aprovaGestor = dados.aprovar_por_gestor
            this.aprovaRh = dados.aprovar_por_rh
            this.podeAprovarExtra = dados.pode_aprovar_extra || false
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || ''
            if (dados.cc) {
                this.lista_ccs = dados.cc
            }
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.syncUrlFiltros()
            this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        },
        limparFiltros() {
            const d = this.controle.dados
            d.campoBusca = ''
            d.campoCPF = ''
            d.campoStatusAprovacao = ''
            d.tipo_contrato = ''
            d.campoCentroCusto = ''
            d.filtroPeriodo = false
            d.dataInicio = ''
            d.dataFim = ''
            d.ordenacao = 'created_at_desc'
            d.pages = 20
            d.token = ''
            d.campoCnpj = ''
            this.atualizar()
        },
        formatarCpfDigitos(valor) {
            const d = String(valor || '').replace(/\D/g, '').slice(0, 11)
            if (d.length <= 3) return d
            if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`
            if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`
            return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`
        },
        parecePadraoCpf(valor) {
            const raw = String(valor || '').trim()
            if (!raw) return false
            if (!/^[\d.\-\s]+$/.test(raw)) return false
            const digitos = raw.replace(/\D/g, '')
            return digitos.length > 0 && digitos.length <= 11
        },
        onInputBuscaUnificada(event) {
            const valor = event && event.target ? event.target.value : ''
            const d = this.controle.dados
            if (!valor) {
                d.campoBusca = ''
                d.campoCPF = ''
                return
            }
            if (this.parecePadraoCpf(valor)) {
                const mascarado = this.formatarCpfDigitos(valor)
                d.campoCPF = mascarado
                d.campoBusca = ''
                if (event.target && event.target.value !== mascarado) {
                    event.target.value = mascarado
                }
                return
            }
            d.campoBusca = valor
            d.campoCPF = ''
        },
        onSelectCnpj() {
            this.controle.dados.campoCentroCusto = ''
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'admissao-status': 'comboFiltroStatus',
                'admissao-tipo-contrato': 'comboFiltroTipoContrato',
                'admissao-cnpj': 'comboFiltroCnpj',
                'admissao-cc': 'comboFiltroCc',
                'admissao-ordenacao': 'comboFiltroOrdenacao',
                'admissao-pages': 'comboFiltroPages',
                'form-admissao-lotacao': 'comboFormLotacao',
                'form-admissao-cc': 'comboFormCc',
                'form-admissao-tipo': 'comboFormTipo',
                'form-admissao-status-gestor': 'comboFormStatusGestor',
                'form-admissao-status-extra': 'comboFormStatusExtra',
                'form-admissao-status-rh': 'comboFormStatusRh'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
        }
    }
}
</script>

<style scoped>
.admissao-filtro-hint {
    margin-left: 0.35rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #0d6efd;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
</style>
