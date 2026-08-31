<template>
    <div>
        <modal :id="hash" :titulo="tituloJanela" :size="90" ref="modalRef">
            <template #conteudo>
                <preload v-show="preload" class="text-center"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i>Solicitação cadastrada com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i>Solicitação alterada com sucesso!</h4>
                </div>
                <form v-if="!preload && !cadastrado && !atualizado" :id="`form_${hash}`" onsubmit="return false">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <colaborador
                                label="Colaborador *"
                                :model="form"
                                :verifica="visualizar || aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                :hash="hash"
                                @evtseleciona="onColaboradorSelecionado"
                            ></colaborador>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Centro de Custo Origem</label>
                                    <select
                                        v-model="form.centro_custo_origem_id"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh || centroOrigemDesabilitadoPorColaborador"
                                        onchange="valida_campo_vazio(this, 0)"
                                        onblur="valida_campo_vazio(this, 0)"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :key="item.id" :value="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Centro de Custo Destino <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.centro_custo_destino_id"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centroCustosDestino" :value="item.id" :key="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Data para transferência <span class="text-danger">*</span></label>
                                <datepicker
                                    formsm
                                    label=""
                                    class="corrigiDatepicker"
                                    v-model="form.data_transferencia"
                                    :disabled="visualizar || aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                ></datepicker>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Gestor responsável pela origem</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        :value="form.label_gestor_origem || 'Selecione o centro de custo origem'"
                                        disabled
                                    />
                                    <div class="alert alert-warning mb-0 mt-2 py-2" v-if="gestorOrigemAusente && !emFluxoAprovacao" role="alert">
                                        O centro de custo de origem não possui gestor responsável. A solicitação pode ser registrada e seguirá o fluxo sem a etapa de aprovação do gestor de origem.
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Gestor responsável pelo destino</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        :class="{ 'is-invalid': gestorDestinoAusente }"
                                        :value="form.label_gestor_destino || 'Selecione o centro de custo destino'"
                                        disabled
                                    />
                                    <div
                                        class="alert alert-danger mb-0 mt-2 py-2"
                                        v-if="mostrarAvisoGestorDestinoAusente"
                                        role="alert"
                                    >
                                        {{ msgGestorDestinoAusente }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.obs"
                                        cols="5"
                                        rows="5"
                                        :disabled="visualizar || aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning" v-if="aprovando && !form.data_aprovacao">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                        <fieldset v-if="visualizar || aprovando">
                            <legend>Aprovação Gestor Origem</legend>
                            <div class="row">
                                <div v-if="!aprovando && form.user_aprovacao" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao }} por: {{ labelAprovadorOrigem(form) }} em
                                        {{ form.data_aprovacao }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                            v-model="form.obs_aprovacao"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select
                                            :disabled="!aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                            v-model="form.status_aprovacao"
                                            class="form-control form-control-sm validacampo"
                                            onchange="valida_campo_vazio(this, 1)"
                                            onblur="valida_campo_vazio(this, 1)"
                                        >
                                            <option value="">Selecione...</option>
                                            <option value="aprovado">Aprovar</option>
                                            <option value="reprovado">Reprovar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset v-if="(visualizar || aprovandoGestorDestino) && form.exige_aprovacao_gestor_destino">
                            <legend>Aprovação Gestor Destino</legend>
                            <div class="row">
                                <div v-if="!aprovandoGestorDestino && form.quem_aprovou_gestor_destino" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_gestor_destino }} por: {{ labelAprovadorDestino(form) }} em
                                        {{ form.data_aprovacao_gestor_destino }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                            v-model="form.obs_aprovacao_gestor_destino"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select
                                            :disabled="!aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                            v-model="form.status_aprovacao_gestor_destino"
                                            class="form-control form-control-sm validacampo"
                                            onchange="valida_campo_vazio(this, 1)"
                                            onblur="valida_campo_vazio(this, 1)"
                                        >
                                            <option value="">Selecione...</option>
                                            <option value="aprovado">Aprovar</option>
                                            <option value="reprovado">Reprovar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="alert alert-warning" v-if="aprovandoExtra">
                            Esta solicitação ainda não foi aprovada ou reprovada por {{ nomeAprovacaoExtra }}!
                        </div>

                        <fieldset v-if="visualizar || aprovandoExtra">
                            <div v-if="!temAprovacaoExtra" class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Esta empresa não possui aprovação extra configurada.
                            </div>

                            <legend v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</legend>
                            <div class="row" v-if="temAprovacaoExtra">
                                <div v-if="!aprovandoExtra && form.user_aprovacao_extra" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_extra }} por: {{ form.user_aprovacao_extra.nome }} em
                                        {{ form.data_aprovacao_extra }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovandoExtra || aprovandoRh"
                                            v-model="form.obs_aprovacao_extra"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select
                                            :disabled="!aprovandoExtra || aprovandoRh"
                                            v-model="form.status_aprovacao_extra"
                                            class="form-control form-control-sm validacampo"
                                            onchange="valida_campo_vazio(this, 1)"
                                            onblur="valida_campo_vazio(this, 1)"
                                        >
                                            <option value="">Selecione...</option>
                                            <option value="aprovado">Aprovar</option>
                                            <option value="reprovado">Reprovar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="alert alert-warning" v-if="aprovandoRh">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                        <fieldset v-if="visualizar || aprovandoRh">
                            <legend>Aprovação RH</legend>
                            <div class="row">
                                <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12">
                                    <legend>{{ form.resposta_rh }} por: {{ form.rh_aprovacao.nome }} em {{ form.data_aprovacao_rh }}</legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="visualizar && !aprovando && !aprovandoRh"
                                            v-model="form.obs_rh"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select
                                            :disabled="visualizar && !aprovando && !aprovandoRh"
                                            v-model="form.resposta_rh"
                                            class="form-control form-control-sm validacampo"
                                            onchange="valida_campo_vazio(this, 1)"
                                            onblur="valida_campo_vazio(this, 1)"
                                        >
                                            <option value="">Selecione...</option>
                                            <option value="aprovado">Aprovar</option>
                                            <option value="reprovado">Reprovar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Anexos</legend>
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
                        </fieldset>
                    </fieldset>
                </form>
            </template>
            <template #rodape>
                <div v-show="!emFluxoAprovacao">
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado && !preload" :disabled="bloqueiaSalvarSemGestorDestino" @click.prevent="alterar">
                        <i class="fa fa-edit"></i> Alterar
                    </button>
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="cadastrando && !cadastrado && !preload" :disabled="bloqueiaSalvarSemGestorDestino" @click.prevent="cadastrar">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                </div>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="aprovando && !atualizado && !preload && !form.data_aprovacao"
                    @click.prevent="aprovar"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="aprovandoGestorDestino && !atualizado && !preload && !form.data_aprovacao_gestor_destino"
                    @click.prevent="aprovarGestorDestino"
                >
                    <i class="fa fa-save"></i> Salvar Gestor Destino
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="aprovandoExtra && !atualizado && !preload && !form.data_aprovacao_extra"
                    @click.prevent="aprovarExtra"
                >
                    <i class="fa fa-save"></i> Salvar {{ nomeAprovacaoExtra }}
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="aprovandoRh && !atualizado && !preload && !form.user_rh_id"
                    @click.prevent="aprovarRH"
                >
                    <i class="fa fa-save"></i> Salvar RH
                </button>
            </template>
        </modal>

        <modal id="janelaAtualizaStatus" titulo="Deseja APROVAR ou REPROVAR todos os colaboradores selecionados?" :centralizada="true" label-fechar="Fechar" ref="modal_janelaAtualizaStatus">
            <template #conteudo>
                <div class="col-12">
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea class="form-control form-control-sm" v-model="formConfirmacao.obs_aprovacao" cols="5" rows="5"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-sm mr-1 btn-success" @click="confirmaAtualizacaoStatus('aprovado')">APROVAR</button>
                    <button type="button" class="btn btn-sm mr-1 btn-danger" @click="confirmaAtualizacaoStatus('reprovado')">REPROVAR</button>
                </div>
            </template>
        </modal>
        <fieldset class="mt-0">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="buscarFiltro">
                <!-- <div class="col-12 col-md-3">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                               :id="`filtroIntervalo_${hash}`"
                               v-model="controle.dados.filtroPeriodo">
                        <label class="form-check-label cursor-pointer" :for="`filtroIntervalo_${hash}`">Por
                            período</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label=""
                                    :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                    v-model="controle.dados.periodo"></datepicker>
                    </div>
                </div> -->

                <date-range-filter
                    v-model:enabled="controle.dados.filtroPeriodo"
                    v-model:start-date="controle.dados.dataInicio"
                    v-model:end-date="controle.dados.dataFim"
                    :disabled="controle.carregando"
                    :id-suffix="hash"
                    wrapper-class="col-12 col-md-3"
                />

                <div class="col-12 col-md-5">
                    <div class="form-group">
                        <label>Pesquisar</label>
                        <input
                            type="text"
                            placeholder="Buscar por colaborador"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoStatus" :disabled="controle.carregando" @change="atualizar()">
                            <option value="">Todos os Status</option>
                            <option value="aberto">Em aberto</option>
                            <option value="aprovado">Aprovado</option>
                            <option value="reprovado">Reprovado</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label>Ordenar por</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.ordenacao" :disabled="controle.carregando" @change="atualizar()">
                            <option value="created_at_desc">Mais recente</option>
                            <option value="created_at_asc">Mais antigo</option>
                            <option value="updated_at_desc">Última modificação</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar()" :disabled="controle.carregando" v-model="controle.dados.pages">
                            <option v-for="(item, index) in por_pagina" :value="item" :key="index">{{ item }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12"></div>
            </form>

            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    :disabled="controle.carregando"
                    @click.prevent="abrirModalSolicitar"
                >
                    Solicitar
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary mr-1"
                    @click.prevent="exportaExcel()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
                >
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                </button>

                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary mr-1"
                    v-show="selecionados.length > 0"
                    :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                    :disabled="selecionados.length === 0"
                    @click.prevent="abrirModalAtualizarStatus"
                >
                    Atualizar Status <span class="badge badge-light">{{ selecionados.length }}</span>
                </button>
            </div>
        </fieldset>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <!-- Cards Compactos -->
            <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="solicitacao-card" v-for="item in lista" :key="item.id">
                    <!-- Cabeçalho do Card -->
                    <div class="card-header-row">
                        <div class="card-left">
                            <span class="badge-id">#{{ item.id }}</span>
                            <div class="colaborador-principal">
                                <i class="fas fa-user text-primary mr-1"></i>
                                <strong>{{ item.colaborador ? item.colaborador.nome : '' }}</strong>
                            </div>
                            <div class="data-info ml-3">
                                <i class="fas fa-calendar-plus text-muted" style="font-size: 0.75rem"></i>
                                <small class="text-muted">{{ item.created_at }}</small>
                                <span v-if="item.updated_at && item.updated_at !== item.created_at" class="mx-2 text-muted">|</span>
                                <template v-if="item.updated_at && item.updated_at !== item.created_at">
                                    <i class="fas fa-calendar-check text-info" style="font-size: 0.75rem"></i>
                                    <small class="text-info">{{ item.updated_at }}</small>
                                </template>
                            </div>
                        </div>
                        <div class="card-right">
                            <span class="status-badge" :class="classeStatusBadge(item)">
                                <span v-if="itemReprovado(item)">
                                    <i class="fas fa-times-circle"></i> REPROVADO
                                </span>
                                <span v-else-if="item.resposta_rh === 'aprovado'"> <i class="fas fa-check-circle"></i> APROVADO RH </span>
                                <span v-else-if="temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO {{ nomeAprovacaoExtra.toUpperCase() }}
                                </span>
                                <span v-else-if="item.exige_aprovacao_gestor_destino && item.status_aprovacao_gestor_destino === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO GESTOR DESTINO
                                </span>
                                <span v-else-if="origemEtapaDispensada(item) && item.exige_aprovacao_gestor_destino">
                                    <i class="fas fa-clock"></i> AGUARDANDO GESTOR DESTINO
                                </span>
                                <span v-else-if="item.status_aprovacao === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO GESTOR ORIGEM
                                </span>
                                <span v-else> <i class="fas fa-clock"></i> EM ABERTO </span>
                            </span>
                            <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                                <a
                                    class="btn-actions-compact"
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
                                    class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(item.id) }"
                                    :aria-labelledby="`dropdownMenuLink_${item.id}`"
                                    @click="fecharDropdown"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação Gestor Origem"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: false, aprovando: true, aprovandoGestorDestino: false, aprovandoExtra: false, aprovandoRh: false, podeanexar: true })"
                                        v-if="!item.status_aprovacao && podeAprovarGestorOrigemItem(item)"
                                    >
                                        Aprovação Gestor Origem
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação Gestor Destino"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: false, aprovando: false, aprovandoGestorDestino: true, aprovandoExtra: false, aprovandoRh: false, podeanexar: true })"
                                        v-if="item.exige_aprovacao_gestor_destino && origemEtapaConcluida(item) && !item.status_aprovacao_gestor_destino && podeAprovarGestorDestinoItem(item)"
                                    >
                                        Aprovação Gestor Destino
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: false, aprovando: false, aprovandoGestorDestino: false, aprovandoExtra: true, aprovandoRh: false, podeanexar: false })"
                                        v-if="temAprovacaoExtra && gestoresConcluidosItem(item) && !item.status_aprovacao_extra && podeAprovarExtra"
                                    >
                                        {{ nomeAprovacaoExtra }}
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: true, aprovando: false, aprovandoGestorDestino: false, aprovandoExtra: false, aprovandoRh: true, podeanexar: false })"
                                        v-if="
                                            ((temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                                (!temAprovacaoExtra && gestoresConcluidosItem(item))) &&
                                            !item.user_rh_id &&
                                            aprovar_por_rh
                                        "
                                    >
                                        Aprovação RH
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: true, aprovando: false, aprovandoGestorDestino: false, aprovandoExtra: false, aprovandoRh: false, podeanexar: false })"
                                    >
                                        Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes do Card -->
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-building text-muted"></i>
                            <span class="detail-label">Centro Custo Origem:</span>
                            <span class="detail-value">{{ item.centro_custo_origem?.label ?? 'Não informado' }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-building text-muted"></i>
                            <span class="detail-label">Centro Custo Destino:</span>
                            <span class="detail-value">{{ item.centro_custo_destino.label }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar text-muted"></i>
                            <span class="detail-label">Data Transferência:</span>
                            <span class="detail-value">{{ item.data_transferencia }}</span>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-user text-muted"></i>
                            <span class="detail-label">Solicitante:</span>
                            <span class="detail-value">{{ item.user_cadastrou.nome }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock text-muted"></i>
                            <span class="detail-label">Criado em:</span>
                            <span class="detail-value">{{ item.created_at }}</span>
                        </div>
                    </div>

                    <!-- Fluxo de Aprovação -->
                    <div class="card-aprovacao-row">
                        <div class="fluxo-icons">
                            <!-- Solicitante -->
                            <div class="fluxo-step">
                                <i class="fas fa-check-circle text-primary"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Solicitante</small>
                                    <small class="fluxo-aprovador text-primary">
                                        {{ item.user_cadastrou.nome }}
                                    </small>
                                    <small v-if="item.created_at" class="fluxo-data">{{ item.created_at }}</small>
                                </div>
                            </div>

                            <i class="fas fa-chevron-right text-muted mx-2"></i>

                            <!-- Gestor Origem -->
                            <div class="fluxo-step">
                                <i v-if="origemEtapaDispensada(item)" class="fas fa-minus-circle text-muted"></i>
                                <i v-else-if="item.status_aprovacao === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i v-else class="fas fa-clock text-warning"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Gestor Origem</small>
                                    <small v-if="origemEtapaDispensada(item)" class="fluxo-status text-muted">
                                        Sem gestor
                                    </small>
                                    <small v-else-if="item.status_aprovacao === 'aprovado' && item.user_aprovacao" class="fluxo-aprovador text-success">
                                        {{ labelAprovadorOrigem(item) }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao === 'reprovado' && item.user_aprovacao" class="fluxo-aprovador text-danger">
                                        {{ labelAprovadorOrigem(item) }}
                                    </small>
                                    <small v-else-if="item.gestor_origem" class="fluxo-aprovador text-muted">
                                        {{ item.gestor_origem.nome }}
                                    </small>
                                    <small v-else class="fluxo-status text-warning">Aguardando</small>
                                    <small v-if="item.data_aprovacao" class="fluxo-data">{{ item.data_aprovacao }}</small>
                                </div>
                            </div>

                            <template v-if="item.exige_aprovacao_gestor_destino">
                                <i class="fas fa-chevron-right text-muted mx-2"></i>

                                <!-- Gestor Destino -->
                                <div class="fluxo-step">
                                    <i v-if="item.status_aprovacao === 'reprovado'" class="fas fa-ban text-secondary"></i>
                                    <i v-else-if="item.status_aprovacao_gestor_destino === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                    <i v-else-if="item.status_aprovacao_gestor_destino === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                    <i v-else-if="origemEtapaConcluida(item)" class="fas fa-clock text-warning"></i>
                                    <i v-else class="fas fa-circle text-muted"></i>
                                    <div class="fluxo-info">
                                        <small class="fluxo-etapa">Gestor Destino</small>
                                        <small v-if="item.status_aprovacao === 'reprovado'" class="fluxo-status text-secondary">Cancelada</small>
                                        <small
                                            v-else-if="item.status_aprovacao_gestor_destino === 'aprovado' && item.quem_aprovou_gestor_destino"
                                            class="fluxo-aprovador text-success"
                                        >
                                            {{ labelAprovadorDestino(item) }}
                                        </small>
                                        <small
                                            v-else-if="item.status_aprovacao_gestor_destino === 'reprovado' && item.quem_aprovou_gestor_destino"
                                            class="fluxo-aprovador text-danger"
                                        >
                                            {{ labelAprovadorDestino(item) }}
                                        </small>
                                        <small v-else-if="origemEtapaConcluida(item) && item.gestor_destino" class="fluxo-aprovador text-warning">
                                            {{ item.gestor_destino.nome }}
                                        </small>
                                        <small v-else-if="origemEtapaConcluida(item)" class="fluxo-status text-warning">Aguardando</small>
                                        <small v-else class="fluxo-status">Pendente</small>
                                        <small v-if="item.data_aprovacao_gestor_destino" class="fluxo-data">{{ item.data_aprovacao_gestor_destino }}</small>
                                    </div>
                                </div>
                            </template>

                            <template v-if="temAprovacaoExtra">
                                <i class="fas fa-chevron-right text-muted mx-2"></i>
                                <!-- Aprovação Extra -->
                                <div class="fluxo-step">
                                    <i v-if="fluxoCanceladoAntesExtra(item)" class="fas fa-ban text-secondary"></i>
                                    <i v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                    <i v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                    <i v-else-if="gestoresConcluidosItem(item) && !item.status_aprovacao_extra" class="fas fa-clock text-warning"></i>
                                    <i v-else class="fas fa-circle text-muted"></i>
                                    <div class="fluxo-info">
                                        <small class="fluxo-etapa">{{ nomeAprovacaoExtra }}</small>
                                        <small v-if="fluxoCanceladoAntesExtra(item)" class="fluxo-status text-secondary">Cancelada</small>
                                        <small
                                            v-else-if="item.status_aprovacao_extra === 'aprovado' && item.user_aprovacao_extra"
                                            class="fluxo-aprovador text-success"
                                        >
                                            {{ item.user_aprovacao_extra.nome }}
                                        </small>
                                        <small
                                            v-else-if="item.status_aprovacao_extra === 'reprovado' && item.user_aprovacao_extra"
                                            class="fluxo-aprovador text-danger"
                                        >
                                            {{ item.user_aprovacao_extra.nome }}
                                        </small>
                                        <small v-else-if="gestoresConcluidosItem(item)" class="fluxo-status text-warning">Aguardando</small>
                                        <small v-else class="fluxo-status">Pendente</small>
                                        <small v-if="item.data_aprovacao_extra" class="fluxo-data">{{ item.data_aprovacao_extra }}</small>
                                    </div>
                                </div>
                            </template>

                            <i class="fas fa-chevron-right text-muted mx-2"></i>

                            <!-- RH -->
                            <div class="fluxo-step">
                                <i v-if="fluxoCanceladoAntesRh(item)" class="fas fa-ban text-secondary"></i>
                                <i v-else-if="item.resposta_rh === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.resposta_rh === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i v-else-if="etapaRhAguardandoItem(item)" class="fas fa-clock text-warning"></i>
                                <i v-else class="fas fa-circle text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">RH</small>
                                    <small v-if="fluxoCanceladoAntesRh(item)" class="fluxo-status text-secondary">Cancelada</small>
                                    <small v-else-if="item.resposta_rh === 'aprovado' && item.rh_aprovacao" class="fluxo-aprovador text-success">
                                        {{ item.rh_aprovacao.nome }}
                                    </small>
                                    <small v-else-if="item.resposta_rh === 'reprovado' && item.rh_aprovacao" class="fluxo-aprovador text-danger">
                                        {{ item.rh_aprovacao.nome }}
                                    </small>
                                    <small v-else-if="etapaRhAguardandoItem(item)" class="fluxo-status text-warning">Aguardando</small>
                                    <small v-else class="fluxo-status">Pendente</small>
                                    <small v-if="item.data_aprovacao_rh" class="fluxo-data">{{ item.data_aprovacao_rh }}</small>
                                </div>
                            </div>
                        </div>
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
import { defineComponent, ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick, getCurrentInstance, inject } from 'vue'
import axios from 'axios'
import _ from 'lodash'
import Upload from '../../Upload'
import colaborador from '../../Colaborador'
import DateRangeFilter from '../../DateRangeFilter'
import Validacoes from '../../../mixins/Validacoes'

const BASE_URL = `${URL_ADMIN}/planejamento/movimentacao/transferencia-prevista`
const POR_PAGINA_OPCOES = [20, 50, 100, 150]
const MSG_GESTOR_DESTINO_AUSENTE =
    'O centro de custo de destino não possui gestor responsável cadastrado. Não é possível salvar a solicitação. Entre em contato com o Administrador para cadastrar o gestor de destino.'

function createFormDefault() {
    return {
    colaborador_id: '',
    autocomplete_label_colaborador: '',
    autocomplete_label_colaborador_anterior: '',
    gestor_id: '',
    autocomplete_label_gestor_modal: '',
    autocomplete_label_gestor_modal_anterior: '',
    centro_custo_origem_id: '',
    centro_custo_destino_id: '',
    label_gestor_origem: '',
    label_gestor_destino: '',
    exige_aprovacao_gestor_destino: false,
    fluxo_gestores_automatico: true,
    data_transferencia: '',
    obs: '',
    obs_aprovacao: '',
    status_aprovacao: '',
    obs_aprovacao_gestor_destino: '',
    status_aprovacao_gestor_destino: '',
    anexos: [],
    anexosDel: []
    }
}

function createFormConfirmacaoDefault() {
    return {
    selecionados: [],
    obs_aprovacao: '',
    status_aprovacao: ''
    }
}

function createControleDados() {
    return {
    filtroPeriodo: false,
    dataInicio: '',
    dataFim: '',
    campoBusca: '',
    campoStatus: '',
    pages: 50,
    token: '',
    ordenacao: 'created_at_desc'
    }
}

export default defineComponent({
    name: 'SolicitacaoTransferencia',
    components: { colaborador, DateRangeFilter, Upload },
    mixins: [Validacoes],
    inject: {
        atualizarUrlMovimentacao: { default: () => () => {} }
    },
    setup() {
        const atualizarUrlMovimentacao = inject('atualizarUrlMovimentacao', () => () => {})
        const modalRef = ref(null)
        const modalStatusRef = ref(null)
        const componenteRef = ref(null)
        const hash = `mybp_${Math.floor(Math.random() * 999999)}`
        const tituloJanela = ref('Solicitacao de admissão')
        const preload = ref(false)
        const editando = ref(false)
        const cadastrado = ref(false)
        const cadastrando = ref(false)
        const atualizado = ref(false)
        const visualizar = ref(false)
        const aprovando = ref(false)
        const aprovandoGestorDestino = ref(false)
        const aprovandoExtra = ref(false)
        const aprovandoRh = ref(false)
        const aprovar_por_gestor = ref(false)
        const aprovar_por_rh = ref(false)
        const usuarioLogadoId = ref(null)
        const temAprovacaoExtra = ref(false)
        const podeAprovarExtra = ref(false)
        const nomeAprovacaoExtra = ref('')
        const preloadExportacao = ref(false)
        const anexoUploadAndamento = ref(false)
        const podeanexar = ref(false)
        const selecionados = ref([])
        const selecionaTudo = ref(false)
        const dropdownAbertoKey = ref(null)
        const lista = ref([])
        const centro_custos = ref([])
        const preloadAtualizacao = ref(false)
        /** Inicia true (campo desabilitado). Só habilita quando colaborador não possui centro de custo. */
        const centroOrigemDesabilitadoPorColaborador = ref(true)
        const gestorOrigemAusente = ref(false)
        const gestorDestinoAusente = ref(false)
        const gestorDestinoCarregando = ref(false)
        const msgGestorDestinoAusente = MSG_GESTOR_DESTINO_AUSENTE

        const bloqueiaSalvarSemGestorDestino = computed(() => gestorDestinoAusente.value || gestorDestinoCarregando.value)
        const emFluxoAprovacao = computed(
            () =>
                visualizar.value ||
                aprovando.value ||
                aprovandoGestorDestino.value ||
                aprovandoExtra.value ||
                aprovandoRh.value
        )
        const mostrarAvisoGestorDestinoAusente = computed(
            () =>
                gestorDestinoAusente.value &&
                !emFluxoAprovacao.value
        )

        const urlExportacao = `${URL_ADMIN}/planejamento/movimentacao/transferencia-prevista/export`
        const url_anexo = `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`
        const urlPaginacao = `${URL_ADMIN}/planejamento/movimentacao/transferencia-prevista/atualizar`
        const urlGestorResponsavel = `${URL_ADMIN}/planejamento/movimentacao/centro-custo`
        const mimes = ref([])

        const formDefault = createFormDefault()
        const formConfirmacaoDefault = createFormConfirmacaoDefault()
        const form = reactive({ ..._.cloneDeep(formDefault) })
        const formConfirmacao = reactive(_.cloneDeep(formConfirmacaoDefault))
        const controle = reactive({
            carregando: false,
            dados: createControleDados()
        })

        let _syncUrlTimer = null

        const naoAprovados = computed(() =>
            lista.value
                .filter((item) => item.status_aprovacao === null && (item.fluxo_gestores_automatico === false || item.gestor_id))
                .map((item) => item.id)
        )
        const centroCustosDestino = computed(() => centro_custos.value.filter((item) => centroCustoEstaAtivo(item)))
        const por_pagina = computed(() => POR_PAGINA_OPCOES)
        const paramsExport = computed(() => controle.dados)
        const tudoMarcado = computed(() => {
            const total = naoAprovados.value.length
            if (total === 0) return false
            const encontrados = naoAprovados.value.filter((id) => selecionados.value.indexOf(id) >= 0).length
            selecionaTudo.value = total === encontrados
            return total === encontrados
        })

        function fecharModalPrincipal() {
            try {
                if (modalRef.value?.fecharModal) modalRef.value.fecharModal()
            } catch (_e) {}
        }

        function getComponenteRef() {
            return componenteRef.value
        }

        function recarregarLista() {
            const comp = getComponenteRef()
            if (comp?.atual !== undefined) comp.atual = 1
            if (comp?.buscar) comp.buscar()
        }

        async function listaCentroCusto() {
            try {
                const { data } = await axios.post(`${URL_PUBLICO}/centro-custos/`, { incluir_inativos: true })
                centro_custos.value = data.centro_custos ?? []
                sincronizarDestinoAtivo()
                if (cadastrando.value) {
                    form.centro_custo_id = ''
                    form.autocomplete_label_colaborador_anterior = ''
                    form.autocomplete_label_colaborador = ''
                    form.colaborador_id = ''
                }
            } catch (_err) {
                preload.value = false
            }
        }

        function centroCustoEstaAtivo(item) {
            return item?.ativo === true || item?.ativo === 1 || item?.ativo === '1'
        }

        function sincronizarDestinoAtivo() {
            if (!form.centro_custo_destino_id) return
            const destino = centro_custos.value.find((item) => Number(item.id) === Number(form.centro_custo_destino_id))
            const modoLeitura = emFluxoAprovacao.value
            if (destino && !centroCustoEstaAtivo(destino) && !modoLeitura) {
                form.centro_custo_destino_id = ''
                form.label_gestor_destino = ''
                gestorDestinoAusente.value = false
            }
        }

        async function confirmaAtualizacaoStatus(confirmacao) {
            preloadAtualizacao.value = true
            formConfirmacao.status_aprovacao = confirmacao
            formConfirmacao.selecionados = [selecionados.value]
            try {
                await axios.post(`${BASE_URL}/atualizacao-status`, formConfirmacao)
                modalStatusRef.value?.fecharModal?.()
                if (typeof mostraSucesso === 'function') mostraSucesso('Status atualizados com sucesso!')
                selecionados.value = []
                Object.assign(formConfirmacao, _.cloneDeep(formConfirmacaoDefault))
                recarregarLista()
            } catch (_err) {
                if (typeof mostraErro === 'function') mostraErro(_err)
            } finally {
                preloadAtualizacao.value = false
            }
        }

        function formNovo() {
            cadastrando.value = true
            cadastrado.value = false
            atualizado.value = false
            editando.value = false
            aprovando.value = false
            aprovandoGestorDestino.value = false
            aprovandoExtra.value = false
            aprovandoRh.value = false
            visualizar.value = false
            podeanexar.value = true
            tituloJanela.value = 'Solicitação de transferência'
            if (typeof formReset === 'function') formReset()
            if (typeof setupCampo === 'function') setupCampo()
            Object.assign(form, _.cloneDeep(formDefault))
            form.centro_custo_id = ''
            centroOrigemDesabilitadoPorColaborador.value = true
            gestorOrigemAusente.value = false
            gestorDestinoAusente.value = false
            gestorDestinoCarregando.value = false
            listaCentroCusto()
        }

        function limparValidacaoCamposIgnorados() {
            $(`#${hash} :input:disabled, #${hash} :input[readonly]`).each(function () {
                $(this).removeClass('is-invalid')
                $(this).siblings('div.invalid-feedback').remove()
            })
        }

        function validarFormularioVisivel() {
            if (typeof $ === 'undefined') return true
            limparValidacaoCamposIgnorados()
            const seletor = `#${hash} :input:visible:not(:disabled):not([readonly])`
            $(seletor).trigger('blur')
            const invalidos = $(`${seletor}.is-invalid`).length
            if (invalidos > 0) {
                if (typeof mostraErro === 'function') mostraErro('', 'Verifique os campos marcados')
                return false
            }
            return true
        }

        function validarColaboradorEGestor() {
            if (!form.colaborador_id) {
                if (typeof valida_campo_vazio === 'function') valida_campo_vazio($(`#colaborador_${hash}`), 1)
                $(`#${hash} #colaborador_${hash}`).focus().trigger('blur')
                if (typeof mostraErro === 'function') mostraErro('', 'Campo COLABORADOR não pode ficar vazio')
                resetaCampoColaborador()
                return false
            }
            if (!form.centro_custo_origem_id) {
                if (typeof mostraErro === 'function') mostraErro('', 'Campo CENTRO DE CUSTO ORIGEM não pode ficar vazio')
                return false
            }
            if (!form.centro_custo_destino_id) {
                if (typeof mostraErro === 'function') mostraErro('', 'Campo CENTRO DE CUSTO DESTINO não pode ficar vazio')
                return false
            }
            const destinoSelecionado = centro_custos.value.find((item) => Number(item.id) === Number(form.centro_custo_destino_id))
            if (destinoSelecionado && !centroCustoEstaAtivo(destinoSelecionado)) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'O centro de custo de destino está inativo. Selecione um centro de custo ativo.')
                }
                form.centro_custo_destino_id = ''
                return false
            }
            if (gestorDestinoCarregando.value) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'Aguarde a verificação do gestor de destino.')
                }
                return false
            }
            if (gestorDestinoAusente.value) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', MSG_GESTOR_DESTINO_AUSENTE)
                }
                return false
            }
            return true
        }

        function resetaCampoColaborador() {
            form.autocomplete_label_colaborador = ''
            form.autocomplete_label_colaborador_anterior = ''
            form.colaborador_id = ''
            form.centro_custo_origem_id = ''
            centroOrigemDesabilitadoPorColaborador.value = true
        }

        /**
         * Ao selecionar colaborador na nova solicitação, preenche o Centro de Custo Origem
         * com o centro de custo atual do colaborador (se possuir). Se possuir, o campo fica desabilitado.
         */
        function onColaboradorSelecionado(model) {
            if (!cadastrando.value) return
            const centroOrigem = model.centro_custo_id ?? ''
            form.centro_custo_origem_id = centroOrigem
            centroOrigemDesabilitadoPorColaborador.value = !!centroOrigem
            carregarGestorResponsavel('origem', centroOrigem)
        }

        async function carregarGestorResponsavel(tipo, centroCustoId) {
            if (!centroCustoId) {
                if (tipo === 'origem') {
                    form.label_gestor_origem = ''
                    gestorOrigemAusente.value = false
                }
                if (tipo === 'destino') {
                    form.label_gestor_destino = ''
                    gestorDestinoAusente.value = false
                    gestorDestinoCarregando.value = false
                }
                return
            }
            if (tipo === 'destino') gestorDestinoCarregando.value = true
            try {
                const { data } = await axios.get(`${urlGestorResponsavel}/${centroCustoId}/gestor-responsavel`)
                const semGestor = !(data.gestor_resolvido?.id || data.gestor_principal?.id)
                const nome = data.gestor_resolvido?.nome ?? data.gestor_principal?.nome ?? 'Centro de custo sem gestor'
                if (tipo === 'origem') {
                    form.label_gestor_origem = nome
                    gestorOrigemAusente.value = semGestor
                }
                if (tipo === 'destino') {
                    form.label_gestor_destino = nome
                    gestorDestinoAusente.value = semGestor
                }
            } catch (_err) {
                if (tipo === 'origem') {
                    form.label_gestor_origem = 'Não informado'
                    gestorOrigemAusente.value = false
                }
                if (tipo === 'destino') {
                    form.label_gestor_destino = 'Centro de custo sem gestor'
                    gestorDestinoAusente.value = true
                }
            } finally {
                if (tipo === 'destino') gestorDestinoCarregando.value = false
            }
        }

        function origemEtapaDispensada(item) {
            return item.fluxo_gestores_automatico !== false && !item.gestor_id && !item.status_aprovacao
        }

        function origemEtapaConcluida(item) {
            if (origemEtapaDispensada(item)) return true
            return item.status_aprovacao === 'aprovado'
        }

        function podeAprovarGestorOrigemItem(item) {
            if (item.fluxo_gestores_automatico === false) {
                return (aprovar_por_gestor.value || aprovar_por_rh.value) && !item.status_aprovacao
            }
            if (item.status_aprovacao || !item.gestor_id) return false
            return item.gestor_id === usuarioLogadoId.value || aprovar_por_rh.value
        }

        function podeAprovarGestorDestinoItem(item) {
            if (!item.gestor_destino_id) return false
            return item.gestor_destino_id === usuarioLogadoId.value || aprovar_por_rh.value
        }

        function labelAprovadorOrigem(item) {
            const nome = item.user_aprovacao?.nome
            if (!nome) return ''
            if (Number(item.user_aprovacao_id) && Number(item.gestor_id) && Number(item.user_aprovacao_id) !== Number(item.gestor_id)) {
                return `${nome} (RH)`
            }
            return nome
        }

        function labelAprovadorDestino(item) {
            const nome = item.quem_aprovou_gestor_destino?.nome
            if (!nome) return ''
            if (
                Number(item.user_aprovacao_gestor_destino_id) &&
                Number(item.gestor_destino_id) &&
                Number(item.user_aprovacao_gestor_destino_id) !== Number(item.gestor_destino_id)
            ) {
                return `${nome} (RH)`
            }
            return nome
        }

        function gestoresConcluidosItem(item) {
            if (item.fluxo_gestores_automatico === false) {
                return item.status_aprovacao === 'aprovado'
            }
            if (!origemEtapaConcluida(item)) return false
            if (item.exige_aprovacao_gestor_destino) {
                return item.status_aprovacao_gestor_destino === 'aprovado'
            }
            return true
        }

        function itemReprovado(item) {
            return (
                item.status_aprovacao === 'reprovado' ||
                item.status_aprovacao_gestor_destino === 'reprovado' ||
                item.status_aprovacao_extra === 'reprovado' ||
                item.resposta_rh === 'reprovado'
            )
        }

        function classeStatusBadge(item) {
            if (itemReprovado(item)) return 'status-reprovado'
            if (item.resposta_rh === 'aprovado') return 'status-aprovado'
            if (temAprovacaoExtra.value && item.status_aprovacao_extra === 'aprovado') return 'status-aprovado-extra'
            if (item.exige_aprovacao_gestor_destino && item.status_aprovacao_gestor_destino === 'aprovado') {
                return 'status-aprovado-gestor-destino'
            }
            if (item.status_aprovacao === 'aprovado') return 'status-aprovado-gestor'
            return 'status-pendente'
        }

        function fluxoCanceladoAntesExtra(item) {
            return item.status_aprovacao === 'reprovado' || item.status_aprovacao_gestor_destino === 'reprovado'
        }

        function fluxoCanceladoAntesRh(item) {
            return fluxoCanceladoAntesExtra(item) || item.status_aprovacao_extra === 'reprovado'
        }

        function etapaRhAguardandoItem(item) {
            if (fluxoCanceladoAntesRh(item)) return false
            if (temAprovacaoExtra.value) {
                return item.status_aprovacao_extra === 'aprovado' && !item.resposta_rh
            }
            return gestoresConcluidosItem(item) && !item.resposta_rh
        }

        async function cadastrar() {
            if (!validarColaboradorEGestor() || !validarFormularioVisivel()) return
            preload.value = true
            try {
                await axios.post(BASE_URL, form)
                fecharModalPrincipal()
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Solicitação registrada com sucesso!')
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        function setModoAprovacao(opt) {
            cadastrando.value = false
            cadastrado.value = false
            atualizado.value = false
            editando.value = false
            visualizar.value = opt.visualizar ?? false
            aprovando.value = opt.aprovando ?? false
            aprovandoGestorDestino.value = opt.aprovandoGestorDestino ?? false
            aprovandoExtra.value = opt.aprovandoExtra ?? false
            aprovandoRh.value = opt.aprovandoRh ?? false
            podeanexar.value = opt.podeanexar ?? false
        }

        async function formOpen(id) {
            Object.assign(form, formDefault)
            form.id = id
            tituloJanela.value = `#${id}`
            if (typeof formReset === 'function') formReset()
            preload.value = true
            try {
                const { data } = await axios.get(`${BASE_URL}/${id}/editar`)
                Object.assign(form, data)
                form.anexos = Array.isArray(form.anexos) ? form.anexos : []
                form.anexosDel = Array.isArray(form.anexosDel) ? form.anexosDel : []
                await listaCentroCusto()
                form.centro_custo_id = data.centro_custo_id ?? ''
                tituloJanela.value = `#${id} Solicitação de transferência`
                if (aprovando.value) {
                    form.status_aprovacao = data.status_aprovacao == null ? '' : data.status_aprovacao
                    form.obs_aprovacao = data.obs_aprovacao == null ? '' : data.obs_aprovacao
                }
                if (aprovandoExtra.value) {
                    form.status_aprovacao_extra = data.status_aprovacao_extra == null ? '' : data.status_aprovacao_extra
                    form.obs_aprovacao_extra = data.obs_aprovacao_extra == null ? '' : data.obs_aprovacao_extra
                }
                if (aprovandoRh.value) {
                    form.resposta_rh = data.resposta_rh == null ? '' : data.resposta_rh
                    form.obs_rh = data.obs_rh == null ? '' : data.obs_rh
                }
                if (aprovandoGestorDestino.value) {
                    form.status_aprovacao_gestor_destino = data.status_aprovacao_gestor_destino == null ? '' : data.status_aprovacao_gestor_destino
                    form.obs_aprovacao_gestor_destino = data.obs_aprovacao_gestor_destino == null ? '' : data.obs_aprovacao_gestor_destino
                }
                form.label_gestor_origem = data.label_gestor_origem ?? ''
                form.label_gestor_destino = data.label_gestor_destino ?? ''
                gestorOrigemAusente.value = !!(data.fluxo_gestores_automatico && !data.gestor_id && data.centro_custo_origem_id)
                gestorDestinoAusente.value = false
                form.exige_aprovacao_gestor_destino = data.exige_aprovacao_gestor_destino ?? false
                form.fluxo_gestores_automatico = data.fluxo_gestores_automatico ?? false
                temAprovacaoExtra.value = data.tem_aprovacao_extra || false
                podeAprovarExtra.value = data.pode_aprovar_extra || false
                nomeAprovacaoExtra.value = data.nome_aprovacao_extra || 'Aprovação Extra'
                centroOrigemDesabilitadoPorColaborador.value = !!(data.centro_custo_id ?? '')
                if (!emFluxoAprovacao.value) {
                    editando.value = true
                }
                sincronizarDestinoAtivo()
                await carregarGestorResponsavel('origem', form.centro_custo_origem_id)
                await carregarGestorResponsavel('destino', form.centro_custo_destino_id)
            } catch (_err) {
                if (typeof mostraErro === 'function') mostraErro(_err)
            } finally {
                preload.value = false
            }
        }

        function abrirModalAposFormOpen(id, modo) {
            setModoAprovacao(modo)
            formOpen(id)
            nextTick(() => {
                if (modalRef.value?.abrirModal) modalRef.value.abrirModal()
            })
        }

        async function alterar() {
            if (!validarColaboradorEGestor() || !validarFormularioVisivel()) return
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}`, form)
                fecharModalPrincipal()
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Solicitação alterada com sucesso!')
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function aprovar() {
            if (!validarFormularioVisivel()) return
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}/aprovar`, form)
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Registro salvo com sucesso!')
                fecharModalPrincipal()
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function aprovarExtra() {
            if (!validarFormularioVisivel()) return
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}/aprovar-extra`, form)
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Aprovação extra salva com sucesso!')
                fecharModalPrincipal()
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function aprovarGestorDestino() {
            if (!validarFormularioVisivel()) return
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}/aprovar-gestor-destino`, form)
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Aprovação do gestor destino salva com sucesso!')
                fecharModalPrincipal()
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function aprovarRH() {
            if (!validarFormularioVisivel()) return
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}/aprovarrh`, form)
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Aprovação RH salva com sucesso!')
                fecharModalPrincipal()
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function exportaExcel() {
            preloadExportacao.value = true
            if (typeof mostraSucesso === 'function') {
                mostraSucesso('Estamos gerando seu arquivo excel, assim que finalizado você será notificado.')
            }
            try {
                await axios.post(urlExportacao, controle.dados)
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preloadExportacao.value = false
            }
        }

        function carregou(dados) {
            lista.value = dados.itens ?? []
            aprovar_por_gestor.value = dados.aprovar_por_gestor ?? false
            aprovar_por_rh.value = dados.aprovar_por_rh ?? false
            temAprovacaoExtra.value = dados.tem_aprovacao_extra ?? false
            podeAprovarExtra.value = dados.pode_aprovar_extra ?? false
            nomeAprovacaoExtra.value = dados.nome_aprovacao_extra ?? 'Aprovação Extra'
            usuarioLogadoId.value = dados.usuario_logado_id ?? null
            controle.carregando = false
        }

        function carregando() {
            controle.carregando = true
        }

        function atualizar() {
            const comp = getComponenteRef()
            if (comp) comp.atual = 1
            recarregarLista()
        }

        function buscarFiltro() {
            getComponenteRef()?.buscar?.()
        }

        function abrirModalSolicitar() {
            formNovo()
            nextTick(() => {
                if (modalRef.value?.abrirModal) modalRef.value.abrirModal()
            })
        }

        function abrirModalAtualizarStatus() {
            modalStatusRef.value?.abrirModal?.()
        }

        function toggleDropdown(itemId) {
            if (!itemId) return
            const key = `mov_trans:${itemId}`
            dropdownAbertoKey.value = dropdownAbertoKey.value === key ? null : key
        }

        function isDropdownOpen(itemId) {
            return dropdownAbertoKey.value === `mov_trans:${itemId}`
        }

        function fecharDropdown() {
            dropdownAbertoKey.value = null
        }

        function onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            dropdownAbertoKey.value = null
        }

        function urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            controle.dados.token = urlParams.get('token') || ''
            const pages = urlParams.get('pages')
            if (pages) controle.dados.pages = parseInt(pages, 10) || 50
            const ordenacao = urlParams.get('ordenacao')
            if (ordenacao) controle.dados.ordenacao = ordenacao
            const campoBusca = urlParams.get('campoBusca')
            if (campoBusca) controle.dados.campoBusca = campoBusca
            const campoStatus = urlParams.get('campoStatus')
            if (campoStatus) controle.dados.campoStatus = campoStatus
            const dataInicio = urlParams.get('dataInicio')
            if (dataInicio) controle.dados.dataInicio = dataInicio
            const dataFim = urlParams.get('dataFim')
            if (dataFim) controle.dados.dataFim = dataFim
            if (dataInicio || dataFim) controle.dados.filtroPeriodo = true
        }

        function syncUrlFiltros() {
            if (typeof atualizarUrlMovimentacao !== 'function') return
            const d = controle.dados
            const params = { pages: d.pages || 50, ordenacao: d.ordenacao || 'created_at_desc' }
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoStatus) params.campoStatus = d.campoStatus
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            if (d.token) params.token = d.token
            atualizarUrlMovimentacao(params)
        }

        function selecionaTodos() {
            selecionaTudo.value = !selecionaTudo.value
            if (selecionaTudo.value) {
                naoAprovados.value.forEach((id) => {
                    if (selecionados.value.indexOf(id) === -1) selecionados.value.push(id)
                })
            } else {
                naoAprovados.value.forEach((id) => {
                    const idx = selecionados.value.indexOf(id)
                    if (idx >= 0) selecionados.value.splice(idx, 1)
                })
            }
        }

        watch(
            () => form.centro_custo_origem_id,
            (valor) => {
                if (cadastrando.value || (editando.value && !form.status_aprovacao)) {
                    carregarGestorResponsavel('origem', valor)
                }
            }
        )

        watch(
            () => form.centro_custo_destino_id,
            (valor) => {
                if (cadastrando.value || (editando.value && !form.status_aprovacao)) {
                    carregarGestorResponsavel('destino', valor)
                }
            }
        )

        watch(
            () => controle.dados,
            () => {
                if (_syncUrlTimer) clearTimeout(_syncUrlTimer)
                _syncUrlTimer = setTimeout(syncUrlFiltros, 400)
            },
            { deep: true }
        )

        onMounted(() => {
            const instance = getCurrentInstance()
            const validaFn = instance?.proxy?.valida_campo_vazio ?? (typeof valida_campo_vazio === 'function' ? valida_campo_vazio : null)
            if (validaFn) {
                window.validaCampo = (el, tipo) => validaFn(el, tipo)
            }
            urlParamGet()
            Object.assign(formDefault, _.cloneDeep(form))
            Object.assign(formConfirmacaoDefault, _.cloneDeep(formConfirmacao))
            nextTick(atualizar)
            document.addEventListener('click', onClickOutside)
        })

        onBeforeUnmount(() => {
            document.removeEventListener('click', onClickOutside)
        })

        return {
            modalRef,
            modal_janelaAtualizaStatus: modalStatusRef,
            componente: componenteRef,
            hash,
            tituloJanela,
            preload,
            editando,
            cadastrado,
            cadastrando,
            atualizado,
            visualizar,
            aprovando,
            aprovandoGestorDestino,
            aprovandoExtra,
            aprovandoRh,
            aprovar_por_gestor,
            aprovar_por_rh,
            temAprovacaoExtra,
            podeAprovarExtra,
            nomeAprovacaoExtra,
            preloadExportacao,
            urlExportacao,
            url_anexo,
            anexoUploadAndamento,
            podeanexar,
            mimes,
            selecionados,
            selecionaTudo,
            dropdownAbertoKey,
            form,
            formConfirmacao,
            formDefault,
            formConfirmacaoDefault,
            lista,
            centro_custos,
            centroCustosDestino,
            centroOrigemDesabilitadoPorColaborador,
            gestorOrigemAusente,
            gestorDestinoAusente,
            gestorDestinoCarregando,
            msgGestorDestinoAusente,
            bloqueiaSalvarSemGestorDestino,
            mostrarAvisoGestorDestinoAusente,
            emFluxoAprovacao,
            urlPaginacao,
            controle,
            naoAprovados,
            por_pagina,
            paramsExport,
            tudoMarcado,
            listaCentroCusto,
            confirmaAtualizacaoStatus,
            formNovo,
            cadastrar,
            formOpen,
            alterar,
            aprovar,
            aprovarGestorDestino,
            aprovarExtra,
            aprovarRH,
            podeAprovarGestorOrigemItem,
            podeAprovarGestorDestinoItem,
            labelAprovadorOrigem,
            labelAprovadorDestino,
            origemEtapaDispensada,
            origemEtapaConcluida,
            gestoresConcluidosItem,
            itemReprovado,
            classeStatusBadge,
            fluxoCanceladoAntesExtra,
            fluxoCanceladoAntesRh,
            etapaRhAguardandoItem,
            carregarGestorResponsavel,
            exportaExcel,
            carregou,
            carregando,
            atualizar,
            toggleDropdown,
            isDropdownOpen,
            fecharDropdown,
            urlParamGet,
            syncUrlFiltros,
            selecionaTodos,
            resetaCampoColaborador,
            onColaboradorSelecionado,
            abrirModalAposFormOpen,
            abrirModalSolicitar,
            abrirModalAtualizarStatus,
            buscarFiltro,
            validarFormularioVisivel,
            validarColaboradorEGestor
        }
    }
})
</script>

<style scoped>
/* Container de Cards */
.cards-lista {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Card Individual */
.solicitacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.solicitacao-card:hover {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    border-color: #007bff;
    transform: translateY(-2px);
}

/* Header do Card */
.card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
}

.card-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.badge-id {
    background: #174257;
    color: white;
    padding: 0.25rem 0.625rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.75rem;
    white-space: nowrap;
    flex-shrink: 0;
}

.colaborador-principal {
    display: flex;
    align-items: center;
    font-size: 0.938rem;
    color: #212529;
    overflow: hidden;
}

.colaborador-principal strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.status-reprovado {
    background: #dc3545;
    color: white;
}

.status-aprovado {
    background: #28a745;
    color: white;
}

.status-aprovado-extra {
    background: #17a2b8;
    color: white;
}

.status-aprovado-gestor {
    background: #ffc107;
    color: #212529;
}

.status-aprovado-gestor-destino {
    background: #fd7e14;
    color: white;
}

.status-pendente {
    background: #e9ecef;
    color: #495057;
}

/* Detalhes do Card */
.card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.813rem;
    min-width: 0;
}

.detail-item i {
    flex-shrink: 0;
    font-size: 0.875rem;
}

.detail-label {
    font-weight: 500;
    color: #6c757d;
    white-space: nowrap;
}

.detail-value {
    color: #212529;
    font-weight: 400;
}

/* Fluxo de Aprovação */
.card-aprovacao-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.fluxo-icons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    flex: 1;
}

.fluxo-step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.fluxo-step i {
    font-size: 1.125rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

.fluxo-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.fluxo-etapa {
    font-weight: 600;
    color: #495057;
    font-size: 0.688rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.fluxo-aprovador,
.fluxo-status {
    font-size: 0.75rem;
    font-weight: 500;
}

.fluxo-data {
    font-size: 0.688rem;
    color: #6c757d;
}

/* Botão de ações compacto */
.btn-actions-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #495057;
    transition: all 0.2s ease;
    text-decoration: none;
    flex-shrink: 0;
}

.btn-actions-compact:hover {
    background: #007bff;
    border-color: #007bff;
    color: white;
    transform: rotate(90deg);
}

/* Dropdown */
.dropdown-menu-custom {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
    padding: 0.5rem 0;
}

.dropdown-menu-custom .dropdown-item {
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.dropdown-menu-custom .dropdown-item:hover {
    background: #f8f9fa;
    color: #007bff;
    padding-left: 1.5rem;
}

/* Responsividade */
@media (max-width: 768px) {
    .card-header-row {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .card-left {
        width: 100%;
    }

    .card-right {
        width: 100%;
        justify-content: space-between;
    }

    .card-details-row {
        flex-direction: column;
        gap: 0.5rem;
    }

    .card-aprovacao-row {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
