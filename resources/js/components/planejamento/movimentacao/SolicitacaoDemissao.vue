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
                    class="demissao-modal-form mybp-filtros-compactos"
                    onsubmit="return false"
                >
                    <p class="mybp-campo-obrigatorio-legenda demissao-modal-legenda">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>

                    <fieldset class="demissao-modal-secao">
                        <legend>Colaborador</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`colaborador_${hash}`">
                                        Colaborador <span class="text-danger">*</span>
                                    </label>
                                    <autocomplete
                                        :caminho="`autocomplete/colaboradores`"
                                        :formsm="true"
                                        :valido="form.colaborador_id !== ''"
                                        v-model="form.autocomplete_label_colaborador"
                                        placeholder="Digite o nome do(a) colaborador(a)"
                                        :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"
                                        :id="`colaborador_${hash}`"
                                        @onblur="resetaCampoColaborador"
                                        @onselect="selecionaColaborador"
                                    ></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Centro de Custo Atual</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        :value="labelCentroCustoAtual || (form.colaborador_id ? 'Colaborador sem centro de custo' : 'Preenchido ao selecionar o colaborador')"
                                        disabled
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Lotação</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        :value="labelLotacaoAtual || (form.colaborador_id ? 'Não informado' : 'Preenchido ao selecionar o colaborador')"
                                        disabled
                                    />
                                </div>
                            </div>

                            <div class="col-12" v-if="colaboradorSemCentroCusto">
                                <div class="alert alert-warning py-2 mb-0" role="alert">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Este colaborador está sem centro de custo. A solicitação pode ser registrada mesmo assim.
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="demissao-modal-secao">
                        <legend>Solicitação</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo demissao-modal-campo-data">
                                    <label class="mybp-label">Data da Demissão <span class="text-danger">*</span></label>
                                    <datepicker
                                        :id="`demissao-data-${hash}`"
                                        label=""
                                        class="corrigiDatepicker"
                                        formsm
                                        v-model="form.data_demissao"
                                        :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"
                                        @onselect="onSelectDataDemissao"
                                    ></datepicker>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`demissao-tipo-aviso-${hash}`">
                                        Tipo de Aviso <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormTipoAviso"
                                            instance-id="form-tipo-aviso"
                                            :input-id="`demissao-tipo-aviso-${hash}`"
                                            v-model="form.tipo_aviso"
                                            :options="formTipoAvisoOpcoes"
                                            :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-tipo-aviso')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <gestoraprovacao
                                label="Gestor Aprovação"
                                :obrigatorio="true"
                                :model="form"
                                :verifica="visualizar || aprovando || aprovandoExtra || aprovandoRh"
                                :hash="hash"
                            ></gestoraprovacao>
                        </div>
                    </fieldset>

                    <fieldset class="demissao-modal-secao">
                        <legend>Detalhes</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.obs"
                                        rows="3"
                                        placeholder="Informações relevantes sobre a demissão"
                                        :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"
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
                        Esta solicitação ainda não foi aprovada ou reprovada pelo gestor!
                    </div>

                    <fieldset v-if="visualizar || aprovando" class="demissao-modal-secao">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.user_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao }} por: {{ form.user_aprovacao.nome }} em {{ form.data_aprovacao }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`demissao-status-gestor-${hash}`">
                                        Status <span class="text-danger" v-if="aprovando">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusGestor"
                                            instance-id="form-status-gestor"
                                            :input-id="`demissao-status-gestor-${hash}`"
                                            v-model="form.status_aprovacao"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovando || aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-status-gestor')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovando || aprovandoExtra || aprovandoRh"
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

                    <fieldset v-if="visualizar || aprovandoExtra" class="demissao-modal-secao">
                        <div v-if="!temAprovacaoExtra" class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Esta empresa não possui aprovação extra configurada.
                        </div>

                        <legend v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</legend>
                        <div class="row" v-if="temAprovacaoExtra">
                            <div v-if="!aprovandoExtra && form.aprovacao_extra" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_extra }} por: {{ form.aprovacao_extra.nome }} em
                                    {{ form.data_aprovacao_extra }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`demissao-status-extra-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoExtra">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusExtra"
                                            instance-id="form-status-extra"
                                            :input-id="`demissao-status-extra-${hash}`"
                                            v-model="form.status_aprovacao_extra"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-status-extra')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovandoExtra || aprovandoRh"
                                        v-model="form.obs_aprovacao_extra"
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="aprovandoRh">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                    <fieldset v-if="visualizar || aprovandoRh" class="demissao-modal-secao">
                        <legend>Aprovação RH</legend>
                        <div class="row">
                            <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao.nome }} em {{ form.data_aprovacao_rh }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`demissao-status-rh-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoRh">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusRh"
                                            instance-id="form-status-rh"
                                            :input-id="`demissao-status-rh-${hash}`"
                                            v-model="form.status_aprovacao_rh"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-status-rh')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovandoRh"
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

        <acao-assinatura-documento
            ref="acaoAssinaturaDemissao"
            :id-prefix="`demissao_${hash}`"
            :titulo-enviar="'Enviar Aviso Prévio para assinatura digital'"
            :get-nome-documento="getNomeDocumentoAssinaturaDemissao"
            :get-signatarios-iniciais="getSignatariosIniciaisAssinaturaDemissao"
            :enviar-handler="enviarAssinaturaDemissao"
            :atualizar-handler="atualizar"
        >
        </acao-assinatura-documento>

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
                    :id-suffix="'demissao-' + hash"
                    label="Período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="demissao-filtro-busca">
                            Colaborador / CPF
                            <span v-if="buscaUnificadaEhCpf" class="demissao-filtro-hint">CPF</span>
                        </label>
                        <input
                            id="demissao-filtro-busca"
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
                        <label class="mybp-label" for="demissao-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="demissao-status"
                                input-id="demissao-filtro-status"
                                v-model="controle.dados.campoStatusAprovacao"
                                :options="opcoesStatus"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhum status encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('demissao-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="demissao-filtro-cnpj">Lotação (CNPJ)</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="demissao-cnpj"
                                input-id="demissao-filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="opcoesCnpj"
                                :disabled="controle.carregando"
                                placeholder-blur="Todas as lotações"
                                empty-message="Nenhuma lotação encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('demissao-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div
                    v-if="lista_ccs"
                    :class="temFilial ? 'col-12 col-md-8' : 'col-12'"
                >
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="demissao-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                instance-id="demissao-cc"
                                input-id="demissao-filtro-cc"
                                v-model="controle.dados.campoCentroCusto"
                                :options="opcoesCentroCusto"
                                :disabled="controle.carregando || !opcoesCentroCusto.length"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro de custo encontrado."
                                :max-results="200"
                                @opening="fecharOutrosComboboxes('demissao-cc')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="demissao-filtro-ordenacao">Ordenar por</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroOrdenacao"
                                instance-id="demissao-ordenacao"
                                input-id="demissao-filtro-ordenacao"
                                v-model="controle.dados.ordenacao"
                                :options="opcoesOrdenacao"
                                :disabled="controle.carregando"
                                placeholder-blur="Mais recentes"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('demissao-ordenacao')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="demissao-filtro-pages">Por página</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPages"
                                instance-id="demissao-pages"
                                input-id="demissao-filtro-pages"
                                v-model="campoPagesCombo"
                                :options="opcoesPages"
                                :disabled="controle.carregando"
                                placeholder-blur="20"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('demissao-pages')"
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

            <!-- Checkbox Geral -->
            <!-- <div class="checkbox-geral-container" v-show="!controle.carregando && lista.length > 0">
                <label class="checkbox-geral-label">
                    <input
                        type="checkbox"
                        class="custom-checkbox"
                        :style="naoAprovados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="naoAprovados.length === 0"
                        :checked="tudoMarcado"
                        @click="selecionaTodos"
                    />
                    <span class="ml-2">Selecionar todos</span>
                </label>
            </div> -->

            <!-- Cards -->
            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="mybp-card" v-for="item in lista" :key="item.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ item.id }}</span>
                            <div class="mybp-card-titulo">
                                <strong>{{ item.colaborador_nome || 'Não informado' }}</strong>
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
                                        v-if="!item.user_aprovacao_nome && !item.rh_aprovacao_nome && !item.aprovado_via_script && aprovaGestor"
                                    >
                                        <i class="fa fa-user-check mr-1"></i> Aprovação Gestor
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra || 'Aprovação Extra'"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="
                                            temAprovacaoExtra &&
                                            podeAprovarExtra &&
                                            item.status_aprovacao === 'aprovado' &&
                                            !item.aprovacao_extra_nome &&
                                            !item.aprovado_via_script &&
                                            !item.rh_aprovacao_nome
                                        "
                                    >
                                        <i class="fa fa-user-check mr-1"></i> {{ nomeAprovacaoExtra || 'Aprovação Extra' }}
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="
                                            ((item.status_aprovacao === 'aprovado' && !temAprovacaoExtra) || item.status_aprovacao_extra === 'aprovado') &&
                                            !item.aprovado_via_script &&
                                            item.rh_aprovacao_nome === null &&
                                            aprovaRh
                                        "
                                    >
                                        <i class="fa fa-users mr-1"></i> Aprovação Rh
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                    >
                                        <i class="fa fa-search mr-1"></i> Visualizar
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        :href="`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${item.id}/pdf`"
                                        target="_blank"
                                        title="Aviso Prévio (PDF)"
                                    >
                                        <i class="fas fa-file-pdf mr-1"></i> Aviso Prévio (PDF)
                                    </a>
                                    <template v-if="assinaturaDigitalHabilitada && temDocumentoAssinaturaDemissao(item)">
                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            title="Gerenciar assinatura digital"
                                            @click.prevent="abrirGerenciamentoAssinaturaDemissao(item)"
                                        >
                                            <i class="fas fa-cog mr-1"></i> Gerenciar assinatura
                                        </a>
                                    </template>
                                    <template v-else-if="assinaturaDigitalHabilitada">
                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            title="Enviar para assinatura digital"
                                            @click.prevent="abrirEnvioAssinaturaDemissao(item)"
                                        >
                                            <i class="fas fa-pen-fancy mr-1"></i> Enviar para assinatura
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-corpo" :class="classeBordaStatusLista(item)">
                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-calendar-times"
                                    label="Data da demissão"
                                    :valor="item.data_demissao"
                                    forte
                                />
                                <mybp-card-campo
                                    icon="fas fa-bell"
                                    label="Tipo de aviso"
                                    :valor="item.tipo_aviso"
                                />
                                <mybp-card-campo
                                    icon="fas fa-briefcase"
                                    label="Cargo"
                                    :valor="item.cargo"
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
                                    :valor="item.centro_custo"
                                />
                                <mybp-card-campo
                                    icon="fas fa-user-tie"
                                    label="Gestor responsável"
                                    :valor="item.gestor_nome"
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
import Upload from '../../Upload'
import Utils from '../../../mixins/Utils'
import configuracoes from '../../../mixins/Configuracoes'
import DateRangeFilter from '../../DateRangeFilter.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'
import AcaoAssinaturaDocumento from '../../administracao/documentoassinatura/AcaoAssinaturaDocumento.vue'

export default {
    mixins: [ExportacaoMixin, Utils, configuracoes],
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
        MybpStatusBadge,
        AcaoAssinaturaDocumento
    },
    data() {
        return {
            tituloJanela: 'Demissão',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovando: false,
            aprovandoExtra: false,
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaRh: false,
            aprovar_por_gestor: false,
            podeAprovarExtra: false,
            temAprovacaoExtra: false,
            nomeAprovacaoExtra: '',
            URL_ADMIN,
            preloadExportacao: false,
            assinaturaDigitalHabilitada: typeof window !== 'undefined' ? !!window.MYBP_ASSINATURA_DIGITAL_HABILITADA : true,

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/export`,

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
                empresa_id: '',

                colaborador_id: '',
                autocomplete_label_colaborador: '',
                autocomplete_label_colaborador_anterior: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',

                centro_custo_id: '',
                filial: false,
                centro_custo_filial_id: '',
                aviso: '',
                data_demissao: '',
                tipo_aviso: '',
                valor: '',
                valor_format: '0,00',
                user_id: '',
                solicitante: '',
                status: '',
                obs: '',

                obs_aprovacao: '',
                status_aprovacao: '',

                obs_aprovacao_extra: '',
                status_aprovacao_extra: '',

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
            lista_ccs: null,

            demissaoAssinaturaSelecionada: null,
            signatariosAssinaturaDemissao: [],
            preloadAssinaturaDemissao: false,
            documentoAssinaturaDetalheDemissao: null,
            preloadGerenciarAssinaturaDemissao: false,
            demissaoParaReenvio: null,

            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: '',
                    campoCPF: '',
                    campoStatusAprovacao: '',
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
        this.$nextTick(() => {
            this.atualizar()
        })
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
        centroCustoSelecionado() {
            if ([undefined, null, ''].includes(this.form.centro_custo_id)) {
                return []
            }
            let centroSelecionado = _.find(this.centro_custos, { id: this.form.centro_custo_id })
            if (centroSelecionado && centroSelecionado.filiais && centroSelecionado.filiais.length) {
                return centroSelecionado.filiais
            }
            return []
        },
        centroCustoTemFilial() {
            return this.temFilial && this.centroCustoSelecionado.length > 0
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            let total = [d.campoBusca, d.campoCPF, d.campoStatusAprovacao, d.campoCentroCusto].filter(
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
            const opts = [
                { value: '', label: 'Todos os status' },
                { value: 'aberto', label: 'Em aberto' },
                { value: 'aprovado_gestor', label: 'Aprovado Gestor' }
            ]
            if (this.temAprovacaoExtra) {
                opts.push({
                    value: 'aprovado_extra',
                    label: `Aprovado ${this.nomeAprovacaoExtra || 'Extra'}`
                })
            }
            opts.push(
                { value: 'aprovado_rh', label: 'Aprovado Rh' },
                { value: 'reprovado', label: 'Reprovado' }
            )
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
        },
        formTipoAvisoOpcoes() {
            return [
                { value: '', label: 'Selecione...' },
                { value: 'Trabalhado', label: 'Trabalhado' },
                { value: 'Indenizado', label: 'Indenizado' },
                { value: 'NA', label: 'NA' }
            ]
        },
        formStatusAprovacaoOpcoes() {
            return [
                { value: '', label: 'Selecione...' },
                { value: 'aprovado', label: 'Aprovar' },
                { value: 'reprovado', label: 'Reprovar' }
            ]
        },
        labelCentroCustoAtual() {
            if ([undefined, null, ''].includes(this.form.centro_custo_id)) {
                return ''
            }
            const item = _.find(this.centro_custos, { id: this.form.centro_custo_id })
            return item && item.label ? item.label : ''
        },
        colaboradorSemCentroCusto() {
            return !!this.form.colaborador_id && [undefined, null, '', 0, '0'].includes(this.form.centro_custo_id)
        },
        labelLotacaoAtual() {
            if ([undefined, null, ''].includes(this.form.colaborador_id) && [undefined, null, ''].includes(this.form.centro_custo_id)) {
                return ''
            }

            const formatLotacao = (nomeFantasia, razaoSocial, cnpj) => {
                const nome = String(nomeFantasia || razaoSocial || '').trim()
                const doc = String(cnpj || '').trim()
                if (!nome && !doc) return ''
                if (!nome) return doc
                if (!doc) return nome
                return `${nome} - ${doc}`
            }

            const ehFilial =
                this.form.filial === true || this.form.filial === 1 || this.form.filial === '1'
            const filiaisAuth =
                (this.authconfiguracao && this.authconfiguracao.cnpjs && this.authconfiguracao.cnpjs.filiais) || []

            if (ehFilial && this.form.centro_custo_filial_id) {
                const vinculo = _.find(this.centroCustoSelecionado, { id: this.form.centro_custo_filial_id })
                const filialRel = vinculo && (vinculo.filial || vinculo.Filial)
                if (filialRel) {
                    let viaRel = formatLotacao(filialRel.nome_fantasia, filialRel.razao_social, filialRel.cnpj)
                    if (viaRel && String(filialRel.cnpj || '').trim()) return viaRel

                    const clienteFilialId = vinculo.cliente_filial_id
                    if (clienteFilialId != null && clienteFilialId !== '') {
                        const cf = _.find(filiaisAuth, { id: clienteFilialId })
                        if (cf) {
                            const viaAuth = formatLotacao(cf.nome_fantasia || cf.nome_fntasia, cf.razao_social, cf.cnpj)
                            if (viaAuth) return viaAuth
                        }
                    }

                    if (viaRel) return viaRel
                }

                if (vinculo && vinculo.cliente_filial_id != null && vinculo.cliente_filial_id !== '') {
                    const cf = _.find(filiaisAuth, { id: vinculo.cliente_filial_id })
                    if (cf) {
                        const viaAuth = formatLotacao(cf.nome_fantasia || cf.nome_fntasia, cf.razao_social, cf.cnpj)
                        if (viaAuth) return viaAuth
                    }
                }
            }

            const matrizAuth = this.authconfiguracao && this.authconfiguracao.cnpjs && this.authconfiguracao.cnpjs.matriz
            if (matrizAuth) {
                const viaMatriz = formatLotacao(matrizAuth.nome_fantasia, matrizAuth.razao_social, matrizAuth.cnpj)
                if (viaMatriz) return viaMatriz
            }

            if (this.lista_ccs && this.lista_ccs.cnpjs) {
                const matrizLista = Object.values(this.lista_ccs.cnpjs).find((item) => item && item.matriz)
                if (matrizLista) {
                    return formatLotacao(matrizLista.nome_fantasia, matrizLista.razao_social, matrizLista.cnpj)
                }
            }

            return ''
        }
    },
    methods: {
        toggleDropdown(itemId) {
            if (!itemId) {
                return
            }
            const key = `mov_demissao:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        chaveStatusLista(item) {
            if (!item) return 'aberto'
            if (
                item.status_aprovacao === 'reprovado' ||
                item.status_aprovacao_extra === 'reprovado' ||
                item.status_aprovacao_rh === 'reprovado'
            ) {
                return 'reprovado'
            }
            if (item.status_aprovacao_rh === 'aprovado') return 'rh'
            if (this.temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') return 'extra'
            if (item.status_aprovacao === 'aprovado') return 'gestor'
            return 'aberto'
        },
        classeBordaStatusLista(item) {
            return `mybp-card-corpo--${this.chaveStatusLista(item)}`
        },
        textoStatusLista(item) {
            const chave = this.chaveStatusLista(item)
            if (chave === 'reprovado') return 'Reprovado'
            if (chave === 'rh') return 'Aprovado RH'
            if (chave === 'extra') return `Aprovado ${this.nomeAprovacaoExtra || 'Extra'}`
            if (chave === 'gestor') return 'Aprovado Gestor'
            return 'Em aberto'
        },
        fluxoStepsLista(item) {
            if (!item) return []
            const steps = [
                {
                    key: 'solicitante',
                    label: 'Solicitante',
                    status: 'aprovado',
                    nome: item.solicitante_nome,
                    data: item.data_solicitacao
                },
                {
                    key: 'gestor',
                    label: 'Gestor',
                    status: item.status_aprovacao === 'aprovado'
                        ? 'aprovado'
                        : item.status_aprovacao === 'reprovado'
                          ? 'reprovado'
                          : 'aguardando',
                    nome: item.user_aprovacao_nome,
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
                    data: item.data_aprovacao_extra
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
                nome: item.rh_aprovacao_nome,
                data: item.data_aprovacao_rh
            })

            return steps
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `mov_demissao:${itemId}`
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
        abrirEnvioAssinaturaDemissao(item) {
            this.$refs.acaoAssinaturaDemissao.abrirEnvio(item)
        },
        abrirGerenciamentoAssinaturaDemissao(item) {
            const doc = item && item.documento_para_assinatura
            if (!doc || !doc.id) return
            this.$refs.acaoAssinaturaDemissao.abrirGerenciar(doc, item)
        },
        getNomeDocumentoAssinaturaDemissao(item) {
            const nome = item && item.colaborador_nome ? item.colaborador_nome : ''
            return nome ? `Aviso Prévio - ${nome}` : 'Aviso Prévio'
        },
        getSignatariosIniciaisAssinaturaDemissao(item) {
            return [
                {
                    nome: (item && item.colaborador_nome) || '',
                    email: (item && item.colaborador_email) || '',
                    cpf: (item && item.colaborador_cpf) || ''
                }
            ]
        },
        enviarAssinaturaDemissao({ contexto, signatarios }) {
            return axios.post(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/enviar-para-assinatura`, {
                demissao_prevista_id: contexto.id,
                signatarios: signatarios.map((s) => ({ nome: s.nome, email: s.email, cpf: s.cpf || null }))
            })
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            const token = urlParams.get('token')
            this.controle.dados.token = token || ''
            if (urlParams.get('pages')) this.controle.dados.pages = parseInt(urlParams.get('pages'), 10) || 20
            if (urlParams.get('ordenacao')) this.controle.dados.ordenacao = urlParams.get('ordenacao')
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoCPF')) this.controle.dados.campoCPF = urlParams.get('campoCPF')
            if (urlParams.get('campoStatusAprovacao')) this.controle.dados.campoStatusAprovacao = urlParams.get('campoStatusAprovacao')
            if (urlParams.get('campoCentroCusto')) this.controle.dados.campoCentroCusto = urlParams.get('campoCentroCusto')
            if (urlParams.get('dataInicio')) this.controle.dados.dataInicio = urlParams.get('dataInicio')
            if (urlParams.get('dataFim')) this.controle.dados.dataFim = urlParams.get('dataFim')
            if (urlParams.get('dataInicio') || urlParams.get('dataFim')) this.controle.dados.filtroPeriodo = true
        },
        syncUrlFiltros() {
            if (typeof this.atualizarUrlMovimentacao !== 'function') return
            const d = this.controle.dados
            const params = {
                pages: d.pages || 20,
                ordenacao: d.ordenacao || 'created_at_desc'
            }
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoCPF) params.campoCPF = d.campoCPF
            if (d.campoStatusAprovacao) params.campoStatusAprovacao = d.campoStatusAprovacao
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            if (d.token) params.token = d.token
            this.atualizarUrlMovimentacao(params)
        },
        /** Remove filtro por token de e-mail (deep link) para a listagem mostrar todos os registros após nova solicitação. */
        limparTokenFiltroListagem() {
            this.controle.dados.token = ''
            this.syncUrlFiltros()
        },
        changeCentroCusto() {
            this.form.filial = false
            this.form.centro_custo_filial_id = ''
        },
        changeCnpj() {
            this.form.centro_custo_filial_id = ''
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
                .post(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/atualizacao-status`, this.formConfirmacao)
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

            this.tituloJanela = 'Solicitação de Demissão'

            formReset()
            setupCampo()
            this.form = _.cloneDeep(this.formDefault) //copia
            this.limparTokenFiltroListagem()
            this.listaCentroCusto()
        },

        cadastrar() {
            this.sincronizarDataDemissaoDoInput()

            if (this.form.colaborador_id === '') {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo COLABORADOR não pode ficar vazio')
                this.resetaCampoColaborador()
                return false
            }
            if (this.form.gestor_id === '') {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1)
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo GESTOR não pode ficar vazio')
                this.resetaCampoGestor()
                return false
            }
            if (!this.form.data_demissao || this.form.data_demissao === 'Invalid date') {
                mostraErro('', 'Campo DATA DA DEMISSÃO não pode ficar vazio')
                return false
            }
            if (!this.form.tipo_aviso) {
                mostraErro('', 'Campo TIPO DE AVISO não pode ficar vazio')
                return false
            }

            if (this.colaboradorSemCentroCusto) {
                const ok = confirm(
                    'O colaborador selecionado está sem centro de custo. Deseja continuar com a solicitação mesmo assim?'
                )
                if (!ok) {
                    return false
                }
            }

            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            const payload = this.montarPayloadDemissao()
            this.preload = true

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista`, payload)
                .then((response) => {
                    if (response.status === 201) {
                        this.limparTokenFiltroListagem()
                        this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                        mostraSucesso('', 'Solicitação registrada com sucesso!')
                        this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    } else {
                        mostraErro('', 'Resposta inesperada do servidor ao registrar a solicitação.')
                    }
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                    const d = error.response && error.response.data ? error.response.data : {}
                    let msg = d.msg || 'Houve um erro ao registrar a solicitação. Tente novamente.'
                    if (d.erros && typeof d.erros === 'object') {
                        const partes = []
                        Object.keys(d.erros).forEach((k) => {
                            const v = d.erros[k]
                            if (Array.isArray(v)) {
                                v.forEach((t) => partes.push(t))
                            } else if (v != null && v !== '') {
                                partes.push(String(v))
                            }
                        })
                        if (partes.length) {
                            msg = partes.join(' ')
                        }
                    }
                    mostraErro('', msg)
                })
        },

        formOpen(id) {
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
                .get(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)
                    this.listaCentroCusto()
                    this.form.centro_custo_id = data.centro_custo_id

                    this.tituloJanela = `#${id} Solicitação de Demissão`

                    if (this.aprovando) {
                        this.form.status_aprovacao = data.status_aprovacao === null ? '' : data.status_aprovacao
                        this.form.observacao = data.status_aprovacao === null ? '' : data.observacao
                    }
                    this.editando = true

                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        abrirModalAssinaturaDemissao(item) {
            this.demissaoAssinaturaSelecionada = item
            this.signatariosAssinaturaDemissao = [{ nome: item.colaborador_nome || '', email: '', cpf: '' }]
            this.preloadAssinaturaDemissao = false
            this.$nextTick(() => this.$refs[`modalAssinaturaDemissao_${this.hash}`] && this.$refs[`modalAssinaturaDemissao_${this.hash}`].abrirModal())
        },
        temDocumentoAssinaturaDemissao(item) {
            const doc = item && item.documento_para_assinatura
            return !!(doc && doc.id)
        },
        abrirModalGerenciarAssinaturaDemissao(item) {
            const doc = item && item.documento_para_assinatura
            if (!doc || !doc.id) return
            this.demissaoParaReenvio = item
            this.documentoAssinaturaDetalheDemissao = null
            this.preloadGerenciarAssinaturaDemissao = true
            this.$refs[`modalGerenciarAssinaturaDemissao_${this.hash}`] && this.$refs[`modalGerenciarAssinaturaDemissao_${this.hash}`].abrirModal()
            const idOrToken = doc.token || doc.id
            axios
                .get(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}`)
                .then((res) => {
                    this.documentoAssinaturaDetalheDemissao = res.data
                    this.preloadGerenciarAssinaturaDemissao = false
                })
                .catch(() => {
                    this.preloadGerenciarAssinaturaDemissao = false
                    mostraErro('', 'Erro ao carregar detalhe do documento.')
                })
        },
        documentoExpiradoOuCanceladoDocDemissao(doc) {
            return doc && (doc.status === 'expirado' || doc.status === 'cancelado')
        },
        enviarNovamenteNoModalDemissao() {
            if (!this.demissaoParaReenvio) return
            this.$refs[`modalGerenciarAssinaturaDemissao_${this.hash}`] && this.$refs[`modalGerenciarAssinaturaDemissao_${this.hash}`].fecharModal()
            this.$nextTick(() => this.abrirModalAssinaturaDemissao(this.demissaoParaReenvio))
        },
        labelTipoDocDemissao(tipo) {
            const map = {
                contrato_legal: 'Contrato (Documentos Legais)',
                contrato_trabalho: 'Contrato de Trabalho',
                carta_oferta: 'Carta Oferta',
                termo_demissao: 'Termo de Demissão',
                ficha_encaminhamento: 'Ficha de Encaminhamento',
                termo_confidencialidade: 'Termo de Confidencialidade',
                opcao_vale_transporte: 'Opção Vale Transporte',
                acordo_compensacao_horas: 'Acordo de Compensação de Horas',
                termo_salario_familia: 'Termo Salário Família',
                declaracao_dependentes_ir: 'Declaração Dependentes IR',
                medida_administrativa: 'Medida Administrativa',
                documento_demissao: 'Documento de Demissão (Aviso Prévio)'
            }
            return map[tipo] || tipo || '—'
        },
        labelStatusDocDemissao(status) {
            const map = {
                rascunho: 'Rascunho',
                enviado: 'Enviado',
                em_assinatura: 'Em assinatura',
                concluido: 'Concluído',
                expirado: 'Expirado',
                cancelado: 'Cancelado'
            }
            return map[status] || status || '—'
        },
        badgeStatusDocDemissao(status) {
            const map = {
                em_assinatura: 'badge-warning',
                concluido: 'badge-success',
                cancelado: 'badge-danger',
                expirado: 'badge-secondary',
                rascunho: 'badge-secondary',
                enviado: 'badge-info'
            }
            return map[status] || 'badge-secondary'
        },
        formatarDataDocDemissao(val) {
            if (!val) return '—'
            const d = typeof val === 'string' ? new Date(val) : val
            return d.toLocaleDateString('pt-BR') + ' ' + (d.toLocaleTimeString ? d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : '')
        },
        labelEventoDocDemissao(evento) {
            const map = {
                enviado: 'Documento enviado',
                reenviado: 'E-mail reenviado',
                visualizado: 'Visualizado pelo signatário',
                assinado: 'Assinado',
                recusado: 'Recusado',
                expirado: 'Documento expirado',
                cancelado: 'Documento cancelado'
            }
            return map[evento] || evento
        },
        podeCancelarDocDemissao(item) {
            return item && ['rascunho', 'em_assinatura'].indexOf(item.status) !== -1
        },
        podeReenviarDocDemissao(item) {
            return item && item.status === 'em_assinatura'
        },
        podeBaixarAssinadoDocDemissao(item) {
            return item && item.status === 'concluido' && item.arquivo_assinado_id
        },
        urlDownloadAssinadoDocDemissao(doc) {
            const idOrToken = doc && doc.token ? doc.token : doc && doc.id ? doc.id : ''
            return `${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/download-assinado`
        },
        cancelarDocNoModalDemissao() {
            if (!this.documentoAssinaturaDetalheDemissao) return
            const confirmar = () => this.executarCancelarDocDemissao(this.documentoAssinaturaDetalheDemissao)
            if (!this.$swal) {
                if (confirm('Cancelar este documento? Os signatários não poderão mais assinar.')) confirmar()
                return
            }
            this.$swal
                .fire({
                    title: 'Cancelar documento?',
                    text: 'Os signatários não poderão mais assinar. Esta ação não pode ser desfeita.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Não',
                    confirmButtonText: 'Sim, cancelar'
                })
                .then((result) => {
                    if (result.isConfirmed) confirmar()
                })
        },
        executarCancelarDocDemissao(doc) {
            const idOrToken = doc && doc.token ? doc.token : doc && doc.id ? doc.id : ''
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/cancelar`)
                .then((res) => {
                    mostraSucesso(res.data.message || 'Documento cancelado.')
                    this.documentoAssinaturaDetalheDemissao = null
                    this.atualizar()
                    this.$refs[`modalGerenciarAssinaturaDemissao_${this.hash}`] && this.$refs[`modalGerenciarAssinaturaDemissao_${this.hash}`].fecharModal()
                })
                .catch((err) => {
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao cancelar.'
                    mostraErro(msg)
                })
        },
        reenviarDocNoModalDemissao() {
            if (!this.documentoAssinaturaDetalheDemissao || this.documentoAssinaturaDetalheDemissao.status !== 'em_assinatura') return
            const idOrToken = this.documentoAssinaturaDetalheDemissao.token || this.documentoAssinaturaDetalheDemissao.id
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/reenviar-email`)
                .then((res) => {
                    mostraSucesso(res.data.message || 'E-mail reenviado.')
                })
                .catch((err) => {
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao reenviar e-mail.'
                    mostraErro(msg)
                })
        },
        adicionarSignatarioAssinaturaDemissao() {
            this.signatariosAssinaturaDemissao.push({ nome: '', email: '', cpf: '' })
        },
        removerSignatarioAssinaturaDemissao(index) {
            this.signatariosAssinaturaDemissao.splice(index, 1)
        },
        enviarParaAssinaturaDemissao() {
            const payload = {
                demissao_prevista_id: this.demissaoAssinaturaSelecionada.id,
                signatarios: this.signatariosAssinaturaDemissao.map((s) => ({ nome: s.nome, email: s.email, cpf: s.cpf || null }))
            }
            this.preloadAssinaturaDemissao = true
            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/enviar-para-assinatura`, payload)
                .then((res) => {
                    this.preloadAssinaturaDemissao = false
                    this.$refs[`modalAssinaturaDemissao_${this.hash}`] && this.$refs[`modalAssinaturaDemissao_${this.hash}`].fecharModal()
                    mostraSucesso(res.data.message || 'Documento enviado para assinatura.')
                    this.atualizar()
                    if (res.data.links && res.data.links.length && this.$swal) {
                        const msg = res.data.links.map((l) => `${l.email}: ${l.link}`).join('\n')
                        this.$swal.fire({ title: 'Links enviados', text: msg, icon: 'info' })
                    }
                })
                .catch((err) => {
                    this.preloadAssinaturaDemissao = false
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao enviar para assinatura.'
                    mostraErro(msg)
                })
        },

        alterar() {
            if (this.form.colaborador_id === '') {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo COLABORADOR não pode ficar vazio')
                this.resetaCampoColaborador()
                return false
            }
            if (this.form.gestor_id === '') {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1)
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo GESTOR não pode ficar vazio')
                this.resetaCampoGestor()
                return false
            }

            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${this.form.id}`, this.form)
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

        aprovarGestor() {
            if (!this.form.status_aprovacao) {
                mostraErro('', 'Selecione o status da aprovação')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${this.form.id}/aprovar`, this.form)
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
            if (!this.form.status_aprovacao_extra) {
                mostraErro('', 'Selecione o status da aprovação')
                return false
            }
            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${this.form.id}/aprovarextra`, this.form)
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
            if (!this.form.status_aprovacao_rh) {
                mostraErro('', 'Selecione o status da aprovação')
                return false
            }
            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${this.form.id}/aprovarrh`, this.form)
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
        selecionaColaborador(obj) {
            const admissao = (obj && obj.admissao) || {}
            this.form.colaborador_id = obj.curriculo_id
            this.form.autocomplete_label_colaborador = obj.label
            this.form.autocomplete_label_colaborador_anterior = obj.label
            this.form.centro_custo_id = admissao.centro_custo_id || ''
            this.form.filial = !!admissao.filial
            this.form.centro_custo_filial_id = this.form.filial ? admissao.centro_custo_filial_id || null : null

            if (this.colaboradorSemCentroCusto && typeof mostraWarning === 'function') {
                mostraWarning(
                    'O colaborador selecionado está sem centro de custo. A solicitação poderá ser registrada mesmo assim.',
                    'Atenção'
                )
            }
        },
        montarPayloadDemissao() {
            const payload = _.cloneDeep(this.form)
            if ([undefined, null, '', 0, '0'].includes(payload.centro_custo_id)) {
                payload.centro_custo_id = null
                payload.filial = false
                payload.centro_custo_filial_id = null
            }
            return payload
        },
        onSelectDataDemissao(valor) {
            const texto = typeof valor === 'string' ? valor : valor && valor.value ? valor.value : ''
            this.form.data_demissao = String(texto || '').trim()
        },
        sincronizarDataDemissaoDoInput() {
            const seletor = `#demissao-data-${this.hash}`
            let valor = ''
            const input = document.querySelector(seletor)
            if (input && input.value) {
                valor = String(input.value).trim()
            } else {
                const fallback = $(`#${this.hash} .demissao-modal-campo-data input:text`).first()
                if (fallback.length) {
                    valor = String(fallback.val() || '').trim()
                }
            }
            if (valor && valor !== 'Invalid date') {
                this.form.data_demissao = valor
            }
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = ''
                this.form.autocomplete_label_colaborador = ''
                this.form.colaborador_id = ''
                this.form.centro_custo_id = ''
                this.form.filial = ''
                this.form.centro_custo_filial_id = ''
                setTimeout(() => {
                    if (this.form.colaborador_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                        $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Colaborador não pode ficar vazio')
                    }
                }, 100)
            }
        },
        carregou(dados) {
            this.lista = dados.itens
            this.mimes = dados.mimes
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
                'demissao-status': 'comboFiltroStatus',
                'demissao-cnpj': 'comboFiltroCnpj',
                'demissao-cc': 'comboFiltroCc',
                'demissao-ordenacao': 'comboFiltroOrdenacao',
                'demissao-pages': 'comboFiltroPages',
                'form-tipo-aviso': 'comboFormTipoAviso',
                'form-status-gestor': 'comboFormStatusGestor',
                'form-status-extra': 'comboFormStatusExtra',
                'form-status-rh': 'comboFormStatusRh'
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
/* Modal no mesmo padrão visual dos filtros compactos (altura/fonte/borda) */
.demissao-modal-form {
    margin-top: 0.15rem;
    --mybp-fc-gap: 0.75rem;
}

.demissao-modal-legenda {
    margin-bottom: 0.65rem;
    font-size: var(--mybp-fc-label-fs, 0.7rem);
    color: #6c757d;
}

.demissao-modal-form .demissao-modal-secao {
    margin-top: 0.55rem;
    margin-bottom: 0.55rem;
    padding: 0.75rem 0.85rem 0.55rem;
}

.demissao-modal-form .demissao-modal-secao > legend {
    font-size: 0.7rem;
    line-height: 1.25;
    padding: 0.25rem 0.7rem;
    letter-spacing: 0.02em;
}

.demissao-modal-form .demissao-modal-secao:first-of-type {
    margin-top: 0;
}

.demissao-modal-form .row > [class*='col-'] {
    margin-bottom: var(--mybp-fc-gap, 0.75rem);
}

.demissao-modal-form :deep(.form-group > .form-group) {
    margin-bottom: 0;
}

.demissao-modal-form :deep(.mybp-filtro-campo .form-group) {
    margin-bottom: 0;
}

.demissao-modal-form :deep(.mybp-filtro-campo .form-group > div > label:empty),
.demissao-modal-form :deep(.form-group > .form-group > div > label:empty) {
    display: none;
    margin: 0;
    padding: 0;
    height: 0;
    line-height: 0;
}

.demissao-modal-form :deep(.demissao-modal-campo-data .corrigiDatepicker),
.demissao-modal-form :deep(.corrigiDatepicker) {
    margin-top: 0;
}

/* Autocomplete / DatePicker herdam altura compacta do filtro */
.demissao-modal-form :deep(.mybp-filtro-campo .autocomplete input),
.demissao-modal-form :deep(.mybp-filtro-campo input.form-control),
.demissao-modal-form :deep(.demissao-modal-campo-data .form-control),
.demissao-modal-form :deep(.demissao-modal-campo-data .form-control-sm) {
    height: var(--mybp-fc-ctrl-h, 1.625rem) !important;
    min-height: var(--mybp-fc-ctrl-h, 1.625rem) !important;
    max-height: var(--mybp-fc-ctrl-h, 1.625rem) !important;
    padding: 0.15rem 0.45rem !important;
    font-size: var(--mybp-fc-ctrl-fs, 0.6875rem) !important;
    line-height: 1.25 !important;
    border-radius: var(--mybp-fc-radius, 6px) !important;
    box-sizing: border-box !important;
}

.demissao-filtro-hint {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    margin-left: 0.35rem;
    padding: 0.05rem 0.35rem;
    border-radius: 999px;
    background: rgba(23, 66, 87, 0.12);
    color: var(--primary, #174257);
    font-size: 0.68rem;
    font-weight: 700;
}
</style>
