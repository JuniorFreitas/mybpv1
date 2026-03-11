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
                <form v-if="!preload && !cadastrado && !atualizado" :id="`form_${hash}`" onsubmit="return false">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Centro de Custo <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.centro_custo_id"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovando || aprovandoRh"
                                        @change.prevent="changeCentroCusto()"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id" :key="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-2" v-if="centroCustoTemFilial">
                                <div class="form-group">
                                    <label>Novo CNPJ</label>
                                    <select
                                        v-model="form.filial"
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeCnpj()"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                    >
                                        <option :value="false">Matriz</option>
                                        <option :value="true">Filial</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="temFilial && form.filial">
                                <div class="form-group">
                                    <label>Filial</label>
                                    <select
                                        v-model="form.centro_custo_filial_id"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centroCustoSelecionado" :value="item.id" :key="item.id">
                                            {{ item.filial.razao_social }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Colaborador <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :caminho="`autocomplete/colaboradorIntermitente`"
                                        :formsm="true"
                                        :valido="form.colaborador_id !== ''"
                                        v-model="form.autocomplete_label_colaborador"
                                        placeholder="Selecione um(a) colaborador(a)"
                                        :disabled="visualizar || aprovando || aprovandoRh"
                                        :id="`colaborador_${hash}`"
                                        @onblur="resetaCampoColaborador"
                                        @onselect="selecionaColaborador"
                                    ></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Cargo Anterior <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :disabled="true"
                                        :caminho="caminho_autocomplete_vagas"
                                        :valido="form.autocomplete_label_vaga_anterior !== ''"
                                        v-model="form.autocomplete_label_vaga_anterior"
                                        placeholder="Vaga Atual"
                                        @onblur="resetaCampoNovoCargo"
                                        @onselect="selecionaVaga"
                                    ></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Salário Anterior</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-mascara:dinheiro
                                        :disabled="true"
                                        v-model="form.salario_anterior_format"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Novo Cargo <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :caminho="caminho_autocomplete_vagas"
                                        :valido="form.autocomplete_label_vaga_nova !== ''"
                                        v-model="form.autocomplete_label_vaga_nova"
                                        placeholder="Novo Cargo"
                                        @onselect="selecionaVagaNovo"
                                        :disabled="visualizar || aprovando || aprovandoRh"
                                    ></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Novo Salário</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-mascara:dinheiro
                                        onblur="valida_dinheiro(this)"
                                        :disabled="visualizar || aprovando || aprovandoRh"
                                        v-model="form.novo_salario_format"
                                    />
                                </div>
                            </div>

                            <gestoraprovacao
                                label="Gestor Aprovação *"
                                :model="form"
                                :verifica="visualizar || aprovando || aprovandoRh"
                                :hash="hash"
                            ></gestoraprovacao>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Motivos para modificação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.motivos"
                                        cols="5"
                                        rows="5"
                                        :disabled="visualizar || aprovando || aprovandoRh"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning" v-if="!form.data_aprovacao && !cadastrando">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                        <fieldset v-if="visualizar || aprovando || aprovandoRh">
                            <legend>Aprovação Gestor</legend>
                            <div class="row">
                                <div v-if="!aprovando && form.user_aprovacao" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao }} por: {{ form.user_aprovacao.nome }} em
                                        {{ form.data_aprovacao }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovando || aprovandoRh"
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
                                            :disabled="!aprovando || aprovandoRh"
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

                        <div class="alert alert-warning" v-if="aprovandoRh">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                        <fieldset v-if="visualizar || aprovandoRh">
                            <legend>Aprovação RH</legend>
                            <div class="row">
                                <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao.nome }} em
                                        {{ form.data_aprovacao_rh }}
                                    </legend>
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
                                            v-model="form.status_aprovacao_rh"
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
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="cadastrando && !preload" @click.prevent="cadastrar">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovando && !preload && !form.data_aprovacao" @click.prevent="aprovar">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovandoRh && !preload && !form.data_aprovacao_rh" @click.prevent="aprovarRh">
                    <i class="fa fa-save"></i> Salvar
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
            <form
                class="row"
                @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null"
            >
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
                        <select
                            class="form-control form-control-sm"
                            v-model="controle.dados.campoStatusAprovacao"
                            :disabled="controle.carregando"
                            @change="atualizar()"
                        >
                            <option value="">Todos os Status</option>
                            <option value="aberto">Em aberto</option>
                            <option value="aprovado_gestor">Aprovado Gestor</option>
                            <option value="aprovado_rh">Aprovado Rh</option>
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

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm mr-1 btn-primary"
                        :disabled="controle.carregando"
                        @click.prevent="formNovo(); $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
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
                        type="submit"
                        class="btn btn-sm mr-1 btn-primary mr-1"
                        v-show="selecionados.length > 0"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0"
                        @click.prevent="$refs.modal_janelaAtualizaStatus && $refs.modal_janelaAtualizaStatus.abrirModal()"
                    >
                        Atualizar Status <span class="badge badge-light">{{ selecionados.length }}</span>
                    </button>
                </div>
            </form>
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
                            <span
                                class="status-badge"
                                :class="{
                                    'status-reprovado':
                                        item.status_aprovacao === 'reprovado' ||
                                        item.status_aprovacao_extra === 'reprovado' ||
                                        item.status_aprovacao_rh === 'reprovado',
                                    'status-aprovado': item.status_aprovacao_rh === 'aprovado',
                                    'status-aprovado-extra': temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado' && !item.status_aprovacao_rh,
                                    'status-aprovado-gestor':
                                        item.status_aprovacao === 'aprovado' &&
                                        (!temAprovacaoExtra || !item.status_aprovacao_extra) &&
                                        !item.status_aprovacao_rh,
                                    'status-pendente': !item.status_aprovacao
                                }"
                            >
                                <span
                                    v-if="
                                        item.status_aprovacao === 'reprovado' ||
                                        item.status_aprovacao_extra === 'reprovado' ||
                                        item.status_aprovacao_rh === 'reprovado'
                                    "
                                >
                                    <i class="fas fa-times-circle"></i> REPROVADO
                                </span>
                                <span v-else-if="item.status_aprovacao_rh === 'aprovado'"> <i class="fas fa-check-circle"></i> APROVADO RH </span>
                                <span v-else-if="temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO {{ nomeAprovacaoExtra.toUpperCase() }}
                                </span>
                                <span v-else-if="item.status_aprovacao === 'aprovado'"> <i class="fas fa-check-circle"></i> APROVADO GESTOR </span>
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
                                        title="Aprovação Gestor"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = true; aprovandoRh = false; podeanexar = true; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="item.user_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor"
                                    >
                                        Aprovação Gestor
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="temAprovacaoExtra && item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra && podeAprovarExtra"
                                    >
                                        {{ nomeAprovacaoExtra }}
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoRh = true; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="
                                            ((temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                                (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')) &&
                                            !item.aprovado_via_script &&
                                            item.rh_aprovacao_id === null &&
                                            aprovaRh
                                        "
                                    >
                                        Aprovação RH
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
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
                            <i class="fas fa-briefcase text-muted"></i>
                            <span class="detail-label">Cargo Anterior:</span>
                            <span class="detail-value">{{ item.vaga_aberta_anterior.titulo }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-briefcase text-muted"></i>
                            <span class="detail-label">Novo Cargo:</span>
                            <span class="detail-value">{{ item.vaga_aberta_nova.titulo }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-building text-muted"></i>
                            <span class="detail-label">Centro Custo:</span>
                            <span class="detail-value">{{ item.centro_custo.label }}</span>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign text-muted"></i>
                            <span class="detail-label">Salário Anterior:</span>
                            <span class="detail-value">{{ item.salario_anterior_format }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign text-muted"></i>
                            <span class="detail-label">Novo Salário:</span>
                            <span class="detail-value">{{ item.novo_salario_format }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-user text-muted"></i>
                            <span class="detail-label">Solicitante:</span>
                            <span class="detail-value">{{ item.solicitante.nome }}</span>
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
                                        {{ item.solicitante.nome }}
                                    </small>
                                    <small v-if="item.created_at" class="fluxo-data">{{ item.created_at }}</small>
                                </div>
                            </div>

                            <i class="fas fa-chevron-right text-muted mx-2"></i>

                            <!-- Gestor -->
                            <div class="fluxo-step">
                                <i v-if="item.status_aprovacao === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i v-else class="fas fa-clock text-warning"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Gestor</small>
                                    <small v-if="item.status_aprovacao === 'aprovado' && item.user_aprovacao" class="fluxo-aprovador text-success">
                                        {{ item.user_aprovacao.nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao === 'reprovado' && item.user_aprovacao" class="fluxo-aprovador text-danger">
                                        {{ item.user_aprovacao.nome }}
                                    </small>
                                    <small v-else class="fluxo-status text-warning">Aguardando</small>
                                    <small v-if="item.data_aprovacao" class="fluxo-data">{{ item.data_aprovacao }}</small>
                                </div>
                            </div>

                            <template v-if="temAprovacaoExtra">
                                <i class="fas fa-chevron-right text-muted mx-2"></i>
                                <!-- Aprovação Extra -->
                                <div class="fluxo-step">
                                    <i v-if="item.status_aprovacao === 'reprovado'" class="fas fa-ban text-secondary"></i>
                                    <i v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                    <i v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                    <i v-else-if="item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra" class="fas fa-clock text-warning"></i>
                                    <i v-else class="fas fa-circle text-muted"></i>
                                    <div class="fluxo-info">
                                        <small class="fluxo-etapa">{{ nomeAprovacaoExtra }}</small>
                                        <small v-if="item.status_aprovacao === 'reprovado'" class="fluxo-status text-secondary">Cancelada</small>
                                        <small
                                            v-else-if="item.status_aprovacao_extra === 'aprovado' && item.aprovacao_extra_nome"
                                            class="fluxo-aprovador text-success"
                                        >
                                            {{ item.aprovacao_extra_nome }}
                                        </small>
                                        <small
                                            v-else-if="item.status_aprovacao_extra === 'reprovado' && item.aprovacao_extra_nome"
                                            class="fluxo-aprovador text-danger"
                                        >
                                            {{ item.aprovacao_extra_nome }}
                                        </small>
                                        <small v-else-if="item.status_aprovacao === 'aprovado'" class="fluxo-status text-warning">Aguardando</small>
                                        <small v-else class="fluxo-status">Pendente</small>
                                        <small v-if="item.data_aprovacao_extra" class="fluxo-data">{{ item.data_aprovacao_extra }}</small>
                                    </div>
                                </div>
                            </template>

                            <i class="fas fa-chevron-right text-muted mx-2"></i>

                            <!-- RH -->
                            <div class="fluxo-step">
                                <i
                                    v-if="item.status_aprovacao === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')"
                                    class="fas fa-ban text-secondary"
                                ></i>
                                <i v-else-if="item.status_aprovacao_rh === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao_rh === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i
                                    v-else-if="
                                        (temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                        (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')
                                    "
                                    class="fas fa-clock text-warning"
                                ></i>
                                <i v-else class="fas fa-circle text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">RH</small>
                                    <small
                                        v-if="item.status_aprovacao === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')"
                                        class="fluxo-status text-secondary"
                                        >Cancelada</small
                                    >
                                    <small v-else-if="item.status_aprovacao_rh === 'aprovado' && item.rh_aprovacao" class="fluxo-aprovador text-success">
                                        {{ item.rh_aprovacao.nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_rh === 'reprovado' && item.rh_aprovacao" class="fluxo-aprovador text-danger">
                                        {{ item.rh_aprovacao.nome }}
                                    </small>
                                    <small
                                        v-else-if="
                                            (temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                            (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')
                                        "
                                        class="fluxo-status text-warning"
                                        >Aguardando</small
                                    >
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
import Upload from '../../Upload'
import gestoraprovacao from '../../GestorAprovacao'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import configuracoes from '../../../mixins/Configuracoes'
import Utils from '../../../mixins/Utils'
import DateRangeFilter from '../../DateRangeFilter'

export default {
    mixins: [ExportacaoMixin, Utils, configuracoes],
    inject: {
        atualizarUrlMovimentacao: { default: () => () => {} }
    },
    components: {
        gestoraprovacao,
        Upload,
        DateRangeFilter
    },
    data() {
        return {
            tituloJanela: 'Solicitação de troca de Contrato Intermitente para Fixo',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovando: false,
            aprovar_por_gestor: false,
            aprovandoExtra: false,
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaRh: false,
            temAprovacaoExtra: false,
            podeAprovarExtra: false,
            nomeAprovacaoExtra: '',
            preloadExportacao: false,

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/export`,
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            url_anexo: `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`,
            anexoUploadAndamento: false,
            podeanexar: false,
            mimes: [],

            colunasTabela: {
                cliente: false
            },

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

                centro_custo_id: '',
                filial: false,
                centro_custo_filial_id: '',
                area_etiqueta_id: '',
                tipo_contrato: '',

                cargo_anterior_id: '',
                autocomplete_label_cargoanterior: '',
                autocomplete_label_cargoanterior_anterior: '',

                novo_cargo_id: '',
                autocomplete_label_novo_cargo: '',
                autocomplete_label_novo_cargo_anterior: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',

                data_admissao: '',
                salario_anterior_format: '0,00',
                novo_salario_format: '0,00',

                user_id: '',
                solicitante: '',
                status: '',
                motivos: '',

                obs_aprovacao: '',
                status_aprovacao: '',

                anexos: [],
                anexosDel: [],

                anterior_vaga_aberta_id: '',
                autocomplete_label_vaga_anterior: '',
                nova_vaga_aberta_id: '',
                autocomplete_label_vaga_nova: '',

                rh_aprovacao_id: '',
                autocomplete_label_rh: '',
                obs_rh: '',
                status_aprovacao_rh: '',
                data_aprovacao_rh: '',
                aprovado_via_script: false
            },

            formDefault: null,
            lista: [],
            centro_custos: [],
            filiais_centro_custos: [],

            // colaborador_ativo: `autocomplete/colaboradores/`,
            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/atualizar`,
            caminho_autocomplete_vagas: `autocomplete/todas-vagas-ativas`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: '',
                    filtroPeriodo: false,
                    dataInicio: '',
                    dataFim: '',
                    campoStatusAprovacao: '',
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
        paramsExport() {
            return this.controle.dados
        }
    },
    methods: {
        toggleDropdown(itemId) {
            if (!itemId) {
                return
            }
            const key = `mov_intermitente:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `mov_intermitente:${itemId}`
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
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            this.controle.dados.token = urlParams.get('token') || ''
            if (urlParams.get('pages')) this.controle.dados.pages = parseInt(urlParams.get('pages'), 10) || 20
            if (urlParams.get('ordenacao')) this.controle.dados.ordenacao = urlParams.get('ordenacao')
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoStatusAprovacao')) this.controle.dados.campoStatusAprovacao = urlParams.get('campoStatusAprovacao')
            if (urlParams.get('dataInicio')) this.controle.dados.dataInicio = urlParams.get('dataInicio')
            if (urlParams.get('dataFim')) this.controle.dados.dataFim = urlParams.get('dataFim')
            if (urlParams.get('dataInicio') || urlParams.get('dataFim')) this.controle.dados.filtroPeriodo = true
        },
        syncUrlFiltros() {
            if (typeof this.atualizarUrlMovimentacao !== 'function') return
            const d = this.controle.dados
            const params = { pages: d.pages || 20, ordenacao: d.ordenacao || 'created_at_desc' }
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoStatusAprovacao) params.campoStatusAprovacao = d.campoStatusAprovacao
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            if (d.token) params.token = d.token
            this.atualizarUrlMovimentacao(params)
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
                .post(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/atualizacao-status`, this.formConfirmacao)
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
            this.form.anterior_vaga_aberta_id = obj.id
            this.form.cargo_anterior_id = obj.vaga_id
            this.form.autocomplete_label_vaga_anterior = obj.vaga.nome
        },
        selecionaVagaNovo(obj) {
            this.form.nova_vaga_aberta_id = obj.id
            this.form.novo_cargo_id = obj.vaga_id
            this.form.autocomplete_label_vaga_nova = obj.vaga.nome
        },
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
                this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },

        selecionaColaborador(obj) {
            this.form.colaborador_id = obj.curriculo_id
            this.form.autocomplete_label_colaborador = obj.label
            this.form.autocomplete_label_colaborador_anterior = obj.label

            //Cargo Anterior
            this.form.cargo_anterior_id = obj.vaga_id
            this.form.autocomplete_label_cargoanterior = obj.vaga_aberta.vaga.nome
            this.form.autocomplete_label_cargoanterior_anterior = obj.vaga_aberta.vaga.nome
            this.form.anterior_vaga_aberta_id = obj.vaga_aberta.id
            this.form.autocomplete_label_vaga_anterior = obj.vaga_aberta.vaga.nome
            this.form.salario_anterior_format = obj.admissao.salario
            this.form.area_etiqueta_id = obj.admissao.area_etiqueta_id
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = ''
                this.form.autocomplete_label_colaborador = ''
                this.form.colaborador_id = ''

                //Cargo Anterior
                this.form.cargo_anterior_id = ''
                this.form.autocomplete_label_cargoanterior = ''
                this.form.autocomplete_label_cargoanterior_anterior = ''
                this.form.anterior_vaga_aberta_id = ''
                this.form.autocomplete_label_vaga_anterior = ''
                this.form.salario_anterior_format = ''
                this.form.area_etiqueta_id = ''

                setTimeout(() => {
                    if (this.form.colaborador_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                        $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Colaborador não pode ficar vazio')
                    }
                }, 100)
            }
        },

        selecionaCargoAnterior(obj) {
            this.form.cargo_anterior_id = obj.id
            this.form.autocomplete_label_cargoanterior = obj.label
            this.form.autocomplete_label_cargoanterior_anterior = obj.label
        },
        resetaCampoCargoAnterior() {
            if (this.form.autocomplete_label_cargoanterior_anterior !== this.form.autocomplete_label_cargoanterior) {
                this.form.autocomplete_label_cargoanterior_anterior = ''
                this.form.autocomplete_label_cargoanterior = ''
                this.form.cargo_anterior_id = ''

                setTimeout(() => {
                    if (this.form.cargo_anterior_id === '') {
                        valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1)
                        $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Cargo Anterior não pode ficar vazio')
                    }
                }, 100)
            }
        },

        selecionaNovoCargo(obj) {
            this.form.novo_cargo_id = obj.id
            this.form.autocomplete_label_novo_cargo = obj.label
            this.form.autocomplete_label_novo_cargo_anterior = obj.label

            // setTimeout(() => {
            //     if (this.form.novo_cargo_id !== '' && this.form.novo_cargo_id === this.form.cargo_anterior_id) {
            //         valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1);
            //         $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur');
            //         mostraErro('Erro', 'O NOVO CARGO não pode ser igual ao CARGO ANTERIOR');
            //         this.form.novo_cargo_id = '';
            //         this.form.autocomplete_label_novo_cargo = '';
            //         this.form.autocomplete_label_novo_cargo_anterior = '';
            //     }
            // }, 100);
        },
        resetaCampoNovoCargo() {
            if (this.form.autocomplete_label_novo_cargo !== this.form.autocomplete_label_novo_cargo) {
                this.form.autocomplete_label_novo_cargo = ''
                this.form.autocomplete_label_novo_cargo = ''
                this.form.novo_cargo_id = ''

                setTimeout(() => {
                    if (this.form.novo_cargo_id === '') {
                        valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1)
                        $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Novo Cargo não pode ficar vazio')
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

        formNovo() {
            this.cadastrado = false
            this.cadastrando = true
            this.atualizado = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false
            this.podeanexar = true

            this.tituloJanela = 'Solicitação de troca de Contrato Intermitente para Fixo'

            formReset()
            setupCampo()
            this.form = _.cloneDeep(this.formDefault) //copia
            this.listaCentroCusto()
        },

        cadastrar() {
            if (this.form.colaborador_id === '') {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo COLABORADOR não pode ficar vazio')
                this.resetaCampoColaborador()
                return false
            }
            if (this.form.novo_cargo_id === '') {
                valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1)
                $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo NOVO CARGO não pode ficar vazio')
                this.resetaCampoNovoCargo()
                return false
            }
            if (this.form.cargo_anterior_id === '') {
                valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1)
                $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo CARGO ANTERIOR não pode ficar vazio')
                this.resetaCampoCargoAnterior()
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
                .post(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista`, this.form)
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
                .get(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)
                    this.listaCentroCusto()
                    this.form.centro_custo_id = data.centro_custo_id

                    this.tituloJanela = `#${id} Solicitação de troca de Contrato Intermitente para Fixo`
                    if (this.aprovando) {
                        this.form.status_aprovacao = data.status_aprovacao === null ? '' : data.status_aprovacao
                        this.form.observacao = data.status_aprovacao === null ? '' : data.observacao
                        this.form.status_aprovacao_rh = data.status_aprovacao_rh === null ? '' : data.status_aprovacao_rh
                        this.form.obs_rh = data.status_aprovacao_rh === null ? '' : data.obs_rh
                    }
                    this.editando = false
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
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

            if (this.form.novo_cargo_id === '') {
                valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1)
                $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo NOVO CARGO não pode ficar vazio')
                this.resetaCampoNovoCargo()
                return false
            }
            if (this.form.cargo_anterior_id === '') {
                valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1)
                $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo CARGO ANTERIOR não pode ficar vazio')
                this.resetaCampoCargoAnterior()
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
                .put(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/${this.form.id}`, this.form)
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

        aprovar() {
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true
            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/${this.form.id}/aprovar`, this.form)
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
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }
            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/intermitente-fixo-prevista/${this.form.id}/aprovarrh`, this.form)
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
            this.aprovaGestor = dados.aprovar_por_gestor
            this.aprovaRh = dados.aprovar_por_rh
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.podeAprovarExtra = dados.pode_aprovar_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || ''
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
}
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

/* Status Badge */
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

.status-aprovado-gestor {
    background: #ffc107;
    color: #212529;
}

.status-aprovado-extra {
    background: #17a2b8;
    color: white;
}

.status-pendente {
    background: #e9ecef;
    color: #495057;
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
