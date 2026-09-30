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
                                    <label class="mybp-label" :for="`colaborador_${hash}`">
                                        Colaborador <span class="text-danger">*</span>
                                    </label>
                                    <autocomplete
                                        :caminho="`autocomplete/colaboradores`"
                                        :formsm="true"
                                        :valido="form.colaborador_id !== ''"
                                        v-model="form.autocomplete_label_colaborador"
                                        placeholder="Selecione um(a) colaborador(a)"
                                        :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"
                                        :id="`colaborador_${hash}`"
                                        @onblur="resetaCampoColaborador"
                                        @onselect="selecionaColaborador"
                                    ></autocomplete>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Lotação / Centro de custo</legend>
                        <div class="row">
                            <div class="col-12 col-md-6" v-if="temFilial">
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
                            <div :class="temFilial ? 'col-12 col-md-6' : 'col-12'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Centro de Custo</label>
                                    <select v-model="form.centro_custo_id" class="form-control form-control-sm" disabled>
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id" :key="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="centroCustoTemFilial">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">CNPJ Atual</label>
                                    <select v-model="form.filial" class="form-control form-control-sm" @change.p.prevent="changeCnpj()" disabled>
                                        <option :value="false">Matriz</option>
                                        <option :value="true">Filial</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-8" v-if="temFilial && form.filial">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Filial</label>
                                    <select v-model="form.centro_custo_filial_id" class="form-control form-control-sm" disabled>
                                        <option value="">Selecione</option>
                                        <option v-for="item in centroCustoSelecionado" :value="item.id" :key="item.id">
                                            {{ item.filial.razao_social }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Solicitação</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">
                                        Tipo <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        :id="`valor-extra-tipo-${hash}`"
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.tipo"
                                        onblur="valida_campo_vazio(this, 1)"
                                        :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">
                                        Período em dias <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        :id="`valor-extra-periodo-${hash}`"
                                        type="number"
                                        class="form-control form-control-sm"
                                        v-model="form.periodo_dias"
                                        step=".5"
                                        onblur="valida_campo_vazio(this, 1)"
                                        :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"
                                    />
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

                    <fieldset v-if="visualizar || aprovando" class="mybp-modal-secao">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.user_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao }} por: {{ form.user_aprovacao.nome }} em {{ form.data_aprovacao }}
                                </p>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`valorextra-status-gestor-${hash}`">
                                        Status <span class="text-danger" v-if="aprovando">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusGestor"
                                            instance-id="form-valorextra-status-gestor"
                                            :input-id="`valorextra-status-gestor-${hash}`"
                                            v-model="form.status_aprovacao"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovando || aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-valorextra-status-gestor')"
                                            @select="limparComboboxInvalido('valorextra-status-gestor-' + hash)"
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

                    <fieldset v-if="visualizar || aprovandoExtra" class="mybp-modal-secao">
                        <div v-if="!temAprovacaoExtra" class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Esta empresa não possui aprovação extra configurada.
                        </div>
                        <legend v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</legend>
                        <div class="row" v-if="temAprovacaoExtra">
                            <div v-if="!aprovandoExtra && form.aprovacao_extra_nome" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_extra }} por: {{ form.aprovacao_extra_nome }} em {{ form.data_aprovacao_extra }}
                                </p>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`valorextra-status-extra-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoExtra">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusExtra"
                                            instance-id="form-valorextra-status-extra"
                                            :input-id="`valorextra-status-extra-${hash}`"
                                            v-model="form.status_aprovacao_extra"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-valorextra-status-extra')"
                                            @select="limparComboboxInvalido('valorextra-status-extra-' + hash)"
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

                    <fieldset v-if="visualizar || aprovandoRh" class="mybp-modal-secao">
                        <legend>Aprovação RH</legend>
                        <div class="row">
                            <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao.nome }} em {{ form.data_aprovacao_rh }}
                                </p>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`valorextra-status-rh-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoRh">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusRh"
                                            instance-id="form-valorextra-status-rh"
                                            :input-id="`valorextra-status-rh-${hash}`"
                                            v-model="form.status_aprovacao_rh"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-valorextra-status-rh')"
                                            @select="limparComboboxInvalido('valorextra-status-rh-' + hash)"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="visualizar && !aprovando && !aprovandoRh"
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
                    :id-suffix="'valorextra-' + hash"
                    label="Período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="valorextra-filtro-busca">
                            Colaborador / CPF
                            <span v-if="buscaUnificadaEhCpf" class="valorextra-filtro-hint">CPF</span>
                        </label>
                        <input
                            id="valorextra-filtro-busca"
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
                        <label class="mybp-label" for="valorextra-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="valorextra-status"
                                input-id="valorextra-filtro-status"
                                v-model="controle.dados.campoStatusAprovacao"
                                :options="opcoesStatus"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhum status encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('valorextra-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="valorextra-filtro-cnpj">Lotação (CNPJ)</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="valorextra-cnpj"
                                input-id="valorextra-filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="opcoesCnpj"
                                :disabled="controle.carregando"
                                placeholder-blur="Todas as lotações"
                                empty-message="Nenhuma lotação encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('valorextra-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="lista_ccs" :class="temFilial ? 'col-12 col-md-8' : 'col-12'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="valorextra-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                instance-id="valorextra-cc"
                                input-id="valorextra-filtro-cc"
                                v-model="controle.dados.campoCentroCusto"
                                :options="opcoesCentroCusto"
                                :disabled="controle.carregando || !opcoesCentroCusto.length"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro de custo encontrado."
                                :max-results="200"
                                @opening="fecharOutrosComboboxes('valorextra-cc')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="valorextra-filtro-ordenacao">Ordenar por</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroOrdenacao"
                                instance-id="valorextra-ordenacao"
                                input-id="valorextra-filtro-ordenacao"
                                v-model="controle.dados.ordenacao"
                                :options="opcoesOrdenacao"
                                :disabled="controle.carregando"
                                placeholder-blur="Mais recentes"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('valorextra-ordenacao')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="valorextra-filtro-pages">Por página</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPages"
                                instance-id="valorextra-pages"
                                input-id="valorextra-filtro-pages"
                                v-model="campoPagesCombo"
                                :options="opcoesPages"
                                :disabled="controle.carregando"
                                placeholder-blur="20"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('valorextra-pages')"
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

            <!-- Checkbox Selecionar Todos -->
            <!-- <div class="checkbox-geral-container" v-show="!controle.carregando && lista.length > 0">
                <label class="checkbox-geral-label">
                    <input type="checkbox"
                           class="custom-checkbox mr-2"
                           @change="selecionarTodos"
                           :checked="lista.length > 0 && selecionados.length === lista.length">
                    Selecionar todos
                </label>
            </div> -->

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
                                        :title="nomeAprovacaoExtra || 'Aprovação Extra'"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="temAprovacaoExtra && item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra && aprovaExtra"
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
                                    icon="fas fa-user"
                                    label="Colaborador"
                                    :valor="item.colaborador ? item.colaborador.nome : 'Não informado'"
                                    forte
                                />
                                <mybp-card-campo icon="fas fa-tag" label="Tipo" :valor="item.tipo" />
                                <mybp-card-campo
                                    icon="fas fa-calendar-alt"
                                    label="Período"
                                    :valor="item.periodo_dias != null && item.periodo_dias !== '' ? `${item.periodo_dias} dias` : 'Não informado'"
                                />
                            </div>
                        </section>
                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    v-if="temFilial"
                                    icon="fas fa-building"
                                    label="Lotação"
                                    :valor="item.lotacao || 'Não informado'"
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
import FiltroListagem from '../../ui/FiltroListagem.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'
import ComboboxValidation from '../../../mixins/ComboboxValidation'
import { buildOpcoesStatusFluxoAprovacao } from '../../../utils/opcoesStatusFluxoAprovacao'

export default {
    mixins: [ExportacaoMixin, Utils, configuracoes, ComboboxValidation],
    inject: {
        atualizarUrlMovimentacao: { default: () => () => {} }
    },
    components: {
        gestoraprovacao,
        DateRangeFilter,
        Upload,
        FiltroListagem,
        ComboboxAutoComplete,
        MybpCardCampo,
        MybpFluxoAprovacao,
        MybpStatusBadge
    },
    data() {
        return {
            tituloJanela: 'Liderança de Pessoal e Valor Extra',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovar_por_gestor: false,
            aprovando: false,
            aprovandoExtra: false,
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaExtra: false,
            aprovaRh: false,
            temAprovacaoExtra: false,
            nomeAprovacaoExtra: 'Aprovação Extra',
            preloadExportacao: false,

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/export`,

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
                colaborador_id: '',
                autocomplete_label_colaborador: '',
                autocomplete_label_colaborador_anterior: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',

                centro_custo_id: '',
                filial: false,
                centro_custo_filial_id: '',

                tipo: '',
                periodo_dias: '',

                user_id: '',
                solicitante: '',
                status: '',
                obs: '',

                obs_aprovacao: '',
                status_aprovacao: '',

                aprovacao_extra_id: '',
                aprovacao_extra_nome: '',
                obs_aprovacao_extra: '',
                status_aprovacao_extra: '',
                data_aprovacao_extra: '',

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

            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/atualizar`,
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
        formStatusAprovacaoOpcoes() {
            return [
                { value: '', label: 'Selecione...' },
                { value: 'aprovado', label: 'Aprovar' },
                { value: 'reprovado', label: 'Reprovar' }
            ]
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

        centroCustoSelecionado() {
            if (this.form.centro_custo_id === undefined || this.form.centro_custo_id === null || this.form.centro_custo_id === '') {
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
            return buildOpcoesStatusFluxoAprovacao({
                temAprovacaoExtra: this.temAprovacaoExtra,
                nomeAprovacaoExtra: this.nomeAprovacaoExtra
            })
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
        tituloCardLista(item) {
            return item?.colaborador?.nome || 'Colaborador não informado'
        },
        centroCustoLista(item) {
            return item?.centro_custo?.label || 'Não informado'
        },
        solicitanteLista(item) {
            return item?.user_cadastrou?.nome || 'Não informado'
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
                    nome: item.aprovacao_extra_nome || item.user_aprovacao_extra?.nome,
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
        toggleDropdown(itemId) {
            if (!itemId) {
                return
            }
            const key = `mov_valextra:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `mov_valextra:${itemId}`
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
        limparTokenFiltroListagem() {
            this.controle.dados.token = ''
            this.syncUrlFiltros()
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
        selecionaColaborador(obj) {
            this.form.colaborador_id = obj.curriculo_id
            this.form.autocomplete_label_colaborador = obj.label
            this.form.autocomplete_label_colaborador_anterior = obj.label
            this.form.centro_custo_id = obj.admissao.centro_custo_id
            this.form.filial = obj.admissao.filial
            this.form.centro_custo_filial_id = this.form.filial ? obj.admissao.centro_custo_filial_id : null
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
        selecionarTodos() {
            if (this.selecionados.length === this.lista.length) {
                this.selecionados = []
            } else {
                this.selecionados = this.lista.map((item) => item.id)
            }
        },
        confirmaAtualizacaoStatus(confirmacao) {
            this.preloadAtualizacao = true
            this.formConfirmacao.status_aprovacao = confirmacao
            this.formConfirmacao.selecionados.push(this.selecionados)

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/atualizacao-status`, this.formConfirmacao)
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
        /***Campos de Filtros ****/
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
            this.controle.carregando = true
            setTimeout(() => {
                this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }, 600)
        },
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
                this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
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

        formNovo() {
            this.cadastrando = true
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.visualizar = false
            this.podeanexar = true
            this.aprovandoRh = false

            this.tituloJanela = 'Liderança de Pessoal e Valor Extra'

            formReset()
            setupCampo()
            this.form = _.cloneDeep(this.formDefault) //copia
            this.limparTokenFiltroListagem()
            this.listaCentroCusto()
        },

        cadastrar() {
            if (!this.validarCamposSolicitacao()) {
                return false
            }

            this.preload = true

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.limparTokenFiltroListagem()
                        this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                        mostraSucesso('', 'Solicitação registrada com sucesso!')
                        this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    }
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
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
                .get(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)
                    this.listaCentroCusto()
                    this.form.centro_custo_id = data.centro_custo_id

                    this.tituloJanela = `#${id} Liderança de Pessoal e Valor Extra`
                    if (this.aprovando) {
                        this.form.status_aprovacao = data.status_aprovacao === null ? '' : data.status_aprovacao
                        this.form.observacao = data.status_aprovacao === null ? '' : data.observacao
                    }
                    this.editando = true

                    this.preload = false
                    this.$nextTick(() => {
                        this.sovizualiza(this.visualizar && !this.aprovando && !this.aprovandoExtra && !this.aprovandoRh)
                    })
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
        },

        alterar() {
            if (!this.validarCamposSolicitacao()) {
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/${this.form.id}`, this.form)
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
            const bloqueados = this.visualizar || this.aprovando || this.aprovandoExtra || this.aprovandoRh
            if (bloqueados) {
                return true
            }

            if (!this.form.colaborador_id) {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo COLABORADOR não pode ficar vazio')
                this.resetaCampoColaborador()
                return false
            }

            if (!this.form.tipo) {
                valida_campo_vazio($(`#valor-extra-tipo-${this.hash}`), 1)
                mostraErro('', 'Campo TIPO não pode ficar vazio')
                return false
            }

            if (this.form.periodo_dias === '' || this.form.periodo_dias === null || this.form.periodo_dias === undefined) {
                valida_campo_vazio($(`#valor-extra-periodo-${this.hash}`), 1)
                mostraErro('', 'Campo PERÍODO EM DIAS não pode ficar vazio')
                return false
            }

            if (!this.form.gestor_id) {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1)
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo GESTOR não pode ficar vazio')
                this.resetaCampoGestor()
                return false
            }

            return this.validarInputsAtivosVisiveis(this.hash)
        },

        aprovarGestor() {
            const statusId = `valorextra-status-gestor-${this.hash}`
            if (
                !this.exigirCombobox(this.form.status_aprovacao, statusId, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }

            this.preload = true
            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/${this.form.id}/aprovar`, this.form)
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
            const statusId = `valorextra-status-extra-${this.hash}`
            if (
                !this.exigirCombobox(this.form.status_aprovacao_extra, statusId, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }

            this.preload = true
            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/${this.form.id}/aprovarextra`, this.form)
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
            const statusId = `valorextra-status-rh-${this.hash}`
            if (
                !this.exigirCombobox(this.form.status_aprovacao_rh, statusId, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/${this.form.id}/aprovarrh`, this.form)
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
            this.mimes = dados.mimes
            this.aprovar_por_gestor = dados.aprovar_por_gestor
            this.aprovaGestor = dados.aprovar_por_gestor
            this.aprovaExtra = dados.pode_aprovar_extra || false
            this.aprovaRh = dados.aprovar_por_rh
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || 'Aprovação Extra'
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
                'valorextra-status': 'comboFiltroStatus',
                'valorextra-cnpj': 'comboFiltroCnpj',
                'valorextra-cc': 'comboFiltroCc',
                'valorextra-ordenacao': 'comboFiltroOrdenacao',
                'valorextra-pages': 'comboFiltroPages'
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
.valorextra-filtro-hint {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    margin-left: 0.35rem;
    padding: 0.05rem 0.35rem;
    border-radius: 999px;
    background: rgba(23, 66, 87, 0.12);
    color: var(--primary, #174257);
    font-size: 0.65rem;
    font-weight: 600;
    vertical-align: middle;
}
</style>
