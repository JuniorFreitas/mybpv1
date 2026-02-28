<template>
    <div>
        <modal :id="hash" :titulo="tituloJanela" :size="90">
            <template slot="conteudo">
                <preload v-show="preload" class="text-center"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i>Solicitação cadastrada com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i>Solicitação alterada com sucesso!</h4>
                </div>
                <form v-if="!preload && !cadastrado && !atualizado" :id="`form_${hash}`" onsubmit="return false;">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informações da Solicitação</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-user"></i> Colaborador <span class="text-danger">*</span></label>
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
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-building"></i> Centro de Custo Atual</label>
                                        <select v-model="form.centro_custo_id" class="form-control form-control-sm"
                                                disabled>
                                            <option value="">Selecione</option>
                                            <option v-for="item in centro_custos" :value="item.id">
                                                {{ item.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2" v-if="centroCustoTemFilial">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-file-alt"></i> CNPJ Atual</label>
                                        <select v-model="form.filial" class="form-control form-control-sm"
                                                @change.p.prevent="changeCnpj()" disabled>
                                            <option :value="false">Matriz</option>
                                            <option :value="true">Filial</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4" v-if="temFilial && form.filial">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-map-marker-alt"></i> Filial</label>
                                        <select v-model="form.centro_custo_filial_id" class="form-control" disabled>
                                            <option value="">Selecione</option>
                                            <option v-for="item in centroCustoSelecionado" :value="item.id" :key="item.id">
                                                {{ item.filial.razao_social }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <label class="font-weight-bold"><i class="fas fa-calendar-alt"></i> Data da Demissão <span class="text-danger">*</span></label>
                                    <datepicker label="" class="corrigiDatepicker" formsm v-model="form.data_demissao"
                                                :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"></datepicker>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-bell"></i> Tipo de Aviso <span class="text-danger">*</span></label>
                                        <select
                                            v-model="form.tipo_aviso"
                                            class="form-control form-control-sm"
                                            :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"
                                            onchange="valida_campo_vazio(this,1)"
                                            onblur="valida_campo_vazio(this,1)"
                                        >
                                            <option value="">Selecione</option>
                                            <option value="Trabalhado">Trabalhado</option>
                                            <option value="Indenizado">Indenizado</option>
                                            <option value="NA">NA</option>
                                        </select>
                                    </div>
                                </div>

                                <gestoraprovacao label="Gestor Aprovação *" :model="form" :verifica="visualizar || aprovando || aprovandoExtra || aprovandoRh" :hash="hash"></gestoraprovacao>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-comment-alt"></i> Observação</label>
                                        <textarea class="form-control" v-model="form.obs" cols="5" rows="5" placeholder="Adicione informações relevantes sobre a demissão..."
                                                  :disabled="visualizar || aprovando || aprovandoExtra || aprovandoRh"></textarea>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 text-info"><i class="fas fa-paperclip"></i> Anexos</h6>
                                        </div>
                                        <div class="card-body">
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
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning" v-if="!form.data_aprovacao && !cadastrando">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo gestor!
                    </div>

                    <fieldset v-if="visualizar || aprovando">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.user_aprovacao" class="col-12">
                                <legend>{{ form.status_aprovacao }}
                                    por: {{ form.user_aprovacao.nome }} em {{ form.data_aprovacao }}
                                </legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovando || aprovandoExtra || aprovandoRh"
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
                                        :disabled="!aprovando || aprovandoExtra || aprovandoRh"
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

                    <div class="alert alert-warning" v-if="aprovandoExtra">
                        Esta solicitação ainda não foi aprovada ou reprovada pela {{ nomeAprovacaoExtra }}!
                    </div>

                    <fieldset v-if="visualizar || aprovandoExtra">
                        <div v-if="!temAprovacaoExtra" class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Esta empresa não possui aprovação extra configurada.
                        </div>

                        <legend v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</legend>
                        <div class="row" v-if="temAprovacaoExtra">
                            <div v-if="!aprovandoExtra && form.aprovacao_extra" class="col-12">
                                <legend>{{ form.status_aprovacao_extra }}
                                    por: {{ form.aprovacao_extra.nome }} em {{ form.data_aprovacao_extra }}
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

                    <div class="alert alert-warning" v-if="aprovandoRh">
                        Esta solicitação ainda não foi aprovada ou reprovada!
                    </div>

                    <fieldset v-if="visualizar || aprovandoRh">
                        <legend>Aprovação RH</legend>
                        <div class="row">
                            <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12">
                                <legend>{{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao.nome }} em {{ form.data_aprovacao_rh }}</legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovandoRh"
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
                                        :disabled="!aprovandoRh"
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
                </form>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="cadastrando && !preload"
                        @click.prevent="cadastrar">
                    <i class="fa fa-save"></i> Cadastrar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="aprovando && !preload"
                        @click.prevent="aprovarGestor">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="aprovandoExtra && !preload"
                        @click.prevent="aprovarExtra">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="aprovandoRh && !preload"
                        @click.prevent="aprovarRh">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <modal id="janelaAtualizaStatus" titulo="Deseja APROVAR ou REPROVAR todos os colaboradores selecionados?"
               :centralizada="true" label-fechar="Fechar">
            <template slot="conteudo">
                <div class="col-12">
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea class="form-control" v-model="formConfirmacao.obs_aprovacao" cols="5"
                                  rows="5"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-sm btn-success" @click="confirmaAtualizacaoStatus('aprovado')">
                        APROVAR
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" @click="confirmaAtualizacaoStatus('reprovado')">
                        REPROVAR
                    </button>
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
            :atualizar-handler="atualizar">
        </acao-assinatura-documento>

        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <date-range-filter
                    :enabled.sync="controle.dados.filtroPeriodo"
                    :start-date.sync="controle.dados.dataInicio"
                    :end-date.sync="controle.dados.dataFim"
                    :disabled="controle.carregando"
                    :id-suffix="hash"
                    wrapper-class="col-12 col-md-3">
                </date-range-filter>

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Pesquisar</label>
                        <input type="text"
                               placeholder="Buscar por colaborador"
                               autocomplete="off"
                               class="form-control form-control-sm"
                               :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm"
                                v-model="controle.dados.campoStatusAprovacao"
                                :disabled="controle.carregando"
                                @change="atualizar()">
                            <option value="">Todos os Status</option>
                            <option value="aberto">Em aberto</option>
                            <option value="aprovado_gestor">Aprovado Gestor</option>
                            <option value="aprovado_extra" v-if="temAprovacaoExtra">Aprovado {{ nomeAprovacaoExtra }}</option>
                            <option value="aprovado_rh">Aprovado Rh</option>
                            <option value="reprovado">Reprovado</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Ordenar por</label>
                        <select class="form-control form-control-sm"
                                v-model="controle.dados.ordenacao"
                                :disabled="controle.carregando"
                                @change="atualizar()">
                            <option value="created_at_desc">Mais Recentes</option>
                            <option value="created_at_asc">Mais Antigos</option>
                            <option value="updated_at_desc">Última Modificação</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label>Exibir</label>
                        <select class="form-control form-control-sm"
                                v-model="controle.dados.pages"
                                :disabled="controle.carregando"
                                @change="atualizar()">
                            <option v-for="item in por_pagina" :key="item" :value="item">{{ item }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12"></div>

                <div class="col-12 col-md-9">
                    <button type="button"
                            class="btn btn-sm btn-success"
                            :disabled="controle.carregando"
                            @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-primary"
                            data-toggle="modal"
                            :data-target="`#${hash}`"
                            :disabled="controle.carregando"
                            @click.prevent="formNovo">
                        Solicitar
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-primary mr-1"
                            @click.prevent="exportaExcel()"
                            :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)">
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-primary mr-1"
                            v-show="selecionados.length > 0"
                            :disabled="selecionados.length === 0"
                            data-toggle="modal"
                            data-target="#janelaAtualizaStatus">
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

            <!-- Cards Compactos -->
            <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div
                    class="solicitacao-card"
                    v-for="item in lista"
                    :key="item.id"
                >
                    <!-- Cabeçalho do Card -->
                    <div class="card-header-row">
                        <div class="card-left">
                            <!-- <label :for="item.id" class="checkbox-inline">
                                <input
                                    type="checkbox"
                                    class="custom-checkbox"
                                    v-model="selecionados"
                                    :value="item.id"
                                    :id="item.id"
                                    :style="!item.status_aprovacao ? 'cursor:pointer' : 'cursor: not-allowed'"
                                    :title="item.status_aprovacao ? null : 'Não possui aprovação'"
                                    v-if="!item.status_aprovacao"
                                />
                                <input type="checkbox" class="custom-checkbox" v-else disabled="disabled" title="Status já atualizado"/>
                            </label> -->
                            <span class="badge-id">#{{ item.id }}</span>
                            <div class="colaborador-principal">
                                <i class="fas fa-user-circle text-primary mr-1"></i>
                                <strong>{{ item.colaborador_nome }}</strong>
                            </div>
                            <div class="data-info ml-3">
                                <i class="fas fa-calendar-plus text-muted" style="font-size: 0.75rem;"></i>
                                <small class="text-muted">{{ item.data_solicitacao }}</small>
                                <span v-if="item.updated_at && item.updated_at !== item.data_solicitacao" class="mx-2 text-muted">|</span>
                                <template v-if="item.updated_at && item.updated_at !== item.data_solicitacao">
                                    <i class="fas fa-calendar-check text-info" style="font-size: 0.75rem;"></i>
                                    <small class="text-info">{{ item.updated_at }}</small>
                                </template>
                            </div>
                        </div>
                        <div class="card-right">
                            <span class="status-badge" :class="{
                                'status-reprovado': item.status_aprovacao === 'reprovado' || item.status_aprovacao_extra === 'reprovado' || item.status_aprovacao_rh === 'reprovado',
                                'status-aprovado': item.status_aprovacao_rh === 'aprovado',
                                'status-aprovado-extra': temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado' && !item.status_aprovacao_rh,
                                'status-aprovado-gestor': item.status_aprovacao === 'aprovado' && (!temAprovacaoExtra || !item.status_aprovacao_extra) && !item.status_aprovacao_rh,
                                'status-pendente': !item.status_aprovacao,
                            }">
                                <span v-if="item.status_aprovacao === 'reprovado' || item.status_aprovacao_extra === 'reprovado' || item.status_aprovacao_rh === 'reprovado'">
                                    <i class="fas fa-times-circle"></i> REPROVADO
                                </span>
                                <span v-else-if="item.status_aprovacao_rh === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO RH
                                </span>
                                <span v-else-if="temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO {{ nomeAprovacaoExtra.toUpperCase() }}
                                </span>
                                <span v-else-if="item.status_aprovacao === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO GESTOR
                                </span>
                                <span v-else>
                                    <i class="fas fa-clock"></i> EM ABERTO
                                </span>
                            </span>
                            <div class="dropdown show">
                                <a class="btn-actions-compact" href="#" role="button"
                                   id="dropdownMenuLink"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                     aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="javascript://" title="Aprovação Gestor"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = true; aprovandoExtra = false; aprovandoRh = false; podeanexar = false"
                                       v-if="!item.user_aprovacao_nome && !item.rh_aprovacao_nome && !item.aprovado_via_script && aprovaGestor">
                                        Aprovação Gestor
                                    </a>

                                    <a class="dropdown-item" href="javascript://" :title="nomeAprovacaoExtra || 'Aprovação Extra'"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; podeanexar = false"
                                       v-if="temAprovacaoExtra && podeAprovarExtra && item.status_aprovacao === 'aprovado' && !item.aprovacao_extra_nome && !item.aprovado_via_script && !item.rh_aprovacao_nome">
                                        {{ nomeAprovacaoExtra || 'Aprovação Extra' }}
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Aprovação RH"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; podeanexar = false"
                                       v-if="((item.status_aprovacao === 'aprovado' && !temAprovacaoExtra) || (item.status_aprovacao_extra === 'aprovado')) && !item.aprovado_via_script && item.rh_aprovacao_nome === null && aprovaRh">
                                        Aprovação Rh
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = false; podeanexar = false">
                                        Visualizar
                                    </a>
                                    <a class="dropdown-item" :href="`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${item.id}/pdf`" target="_blank" title="Aviso Prévio (PDF)">
                                        <i class="fas fa-file-pdf"></i> Aviso Prévio (PDF)
                                    </a>
                                    <template v-if="temDocumentoAssinaturaDemissao(item)">
                                        <a class="dropdown-item" href="javascript://" title="Gerenciar assinatura digital"
                                           @click.prevent="abrirGerenciamentoAssinaturaDemissao(item)">
                                            <i class="fas fa-cog"></i> Gerenciar assinatura
                                        </a>
                                    </template>
                                    <template v-else>
                                        <a class="dropdown-item" href="javascript://" title="Enviar para assinatura digital"
                                           @click.prevent="abrirEnvioAssinaturaDemissao(item)">
                                            <i class="fas fa-pen-fancy"></i> Enviar para assinatura
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes do Card -->
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-briefcase text-muted"></i>
                            <span class="detail-label">Cargo:</span>
                            <span class="detail-value">{{ item.cargo }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-building text-muted"></i>
                            <span class="detail-label">Centro:</span>
                            <span class="detail-value">{{ item.centro_custo }}</span>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-calendar-times text-danger"></i>
                            <span class="detail-label">Data Demissão:</span>
                            <span class="detail-value text-danger font-weight-bold">{{ item.data_demissao }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-bell text-warning"></i>
                            <span class="detail-label">Tipo Aviso:</span>
                            <span class="detail-value">{{ item.tipo_aviso || 'Não informado' }}</span>
                        </div>
                    </div>

                    <!-- Fluxo de Aprovação -->
                    <div class="card-aprovacao-row">

                        <div class="fluxo-icons">
                            <div class="fluxo-step">
                                <i class="fas fa-check-circle text-success"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Solicitante</small>
                                    <small class="fluxo-aprovador text-success">
                                        {{ item.solicitante_nome }}
                                    </small>
                                    <small class="fluxo-data">{{ item.data_solicitacao }}</small>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted mx-2"></i>
                            <!-- Gestor -->
                            <div class="fluxo-step">
                                <i v-if="item.status_aprovacao === 'aprovado'"
                                   class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao === 'reprovado'"
                                   class="fas fa-times-circle text-danger"></i>
                                <i v-else
                                   class="fas fa-clock text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Gestor</small>
                                    <small v-if="item.status_aprovacao === 'aprovado'" class="fluxo-aprovador text-success">
                                        {{ item.user_aprovacao_nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao === 'reprovado'" class="fluxo-aprovador text-danger">
                                        {{ item.user_aprovacao_nome }}
                                    </small>
                                    <small v-else class="fluxo-status text-warning">Aguardando</small>
                                    <small v-if="item.data_aprovacao" class="fluxo-data">{{ item.data_aprovacao }}</small>
                                </div>
                            </div>

                            <i class="fas fa-chevron-right text-muted mx-2"></i>

                            <!-- Aprovação Extra (se configurada) -->
                            <div class="fluxo-step" v-if="temAprovacaoExtra">
                                <i v-if="item.status_aprovacao === 'reprovado'"
                                   class="fas fa-ban text-secondary"></i>
                                <i v-else-if="item.status_aprovacao_extra === 'aprovado'"
                                   class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao_extra === 'reprovado'"
                                   class="fas fa-times-circle text-danger"></i>
                                <i v-else-if="item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra"
                                   class="fas fa-clock text-warning"></i>
                                <i v-else
                                   class="fas fa-circle text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">{{ nomeAprovacaoExtra }}</small>
                                    <small v-if="item.status_aprovacao === 'reprovado'" class="fluxo-status text-secondary">
                                        Cancelada por reprovação
                                    </small>
                                    <small v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fluxo-aprovador text-success">
                                        {{ item.aprovacao_extra_nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fluxo-aprovador text-danger">
                                        {{ item.aprovacao_extra_nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao === 'aprovado'" class="fluxo-status text-warning">
                                        Aguardando
                                    </small>
                                    <small v-else class="fluxo-status">Pendente</small>
                                    <small v-if="item.data_aprovacao_extra" class="fluxo-data">{{ item.data_aprovacao_extra }}</small>
                                </div>
                            </div>

                            <i class="fas fa-chevron-right text-muted mx-2" v-if="temAprovacaoExtra"></i>

                            <!-- RH -->
                            <div class="fluxo-step">
                                <i v-if="item.status_aprovacao === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')"
                                   class="fas fa-ban text-secondary"></i>
                                <i v-else-if="item.status_aprovacao_rh === 'aprovado'"
                                   class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao_rh === 'reprovado'"
                                   class="fas fa-times-circle text-danger"></i>
                                <i v-else-if="(temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') || (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')"
                                   class="fas fa-clock text-warning"></i>
                                <i v-else
                                   class="fas fa-circle text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">RH</small>
                                    <small v-if="item.status_aprovacao === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')" class="fluxo-status text-secondary">
                                        Cancelada por reprovação
                                    </small>
                                    <small v-else-if="item.status_aprovacao_rh === 'aprovado'" class="fluxo-aprovador text-success">
                                        {{ item.rh_aprovacao_nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_rh === 'reprovado'" class="fluxo-aprovador text-danger">
                                        {{ item.rh_aprovacao_nome }}
                                    </small>
                                    <small v-else-if="(temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') || (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')" class="fluxo-status text-warning">
                                        Aguardando
                                    </small>
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
import gestoraprovacao from '../../GestorAprovacao'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Upload from '../../Upload'
import Utils from '../../../mixins/Utils'
import configuracoes from '../../../mixins/Configuracoes'
import DateRangeFilter from '../../DateRangeFilter.vue'
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

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/export`,

            url_anexo: `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`,
            anexoUploadAndamento: false,
            podeanexar: false,
            mimes: [],

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            selecionados: [],
            selecionaTudo: false,

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
                    campoStatusAprovacao: '',
                    filtroPeriodo: false,
                    dataInicio: '',
                    dataFim: '',
                    token: '',
                    ordenacao: 'created_at_desc',
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
            let centroSelecionado = _.find(this.centro_custos, {id: this.form.centro_custo_id})
            if (centroSelecionado && centroSelecionado.filiais && centroSelecionado.filiais.length) {
                return centroSelecionado.filiais
            }
            return []
        },
        centroCustoTemFilial() {
            return this.temFilial && this.centroCustoSelecionado.length > 0
        }
    },
    methods: {
        abrirEnvioAssinaturaDemissao(item) {
            this.$refs.acaoAssinaturaDemissao.abrirEnvio(item);
        },
        abrirGerenciamentoAssinaturaDemissao(item) {
            const doc = item && item.documento_para_assinatura;
            if (!doc || !doc.id) return;
            this.$refs.acaoAssinaturaDemissao.abrirGerenciar(doc, item);
        },
        getNomeDocumentoAssinaturaDemissao(item) {
            const nome = item && item.colaborador_nome ? item.colaborador_nome : '';
            return nome ? `Aviso Prévio - ${nome}` : 'Aviso Prévio';
        },
        getSignatariosIniciaisAssinaturaDemissao(item) {
            return [{
                nome: (item && item.colaborador_nome) || '',
                email: (item && item.colaborador_email) || '',
                cpf: (item && item.colaborador_cpf) || '',
            }];
        },
        enviarAssinaturaDemissao({ contexto, signatarios }) {
            return axios.post(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/enviar-para-assinatura`, {
                demissao_prevista_id: contexto.id,
                signatarios: signatarios.map((s) => ({ nome: s.nome, email: s.email, cpf: s.cpf || null })),
            });
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            const token = urlParams.get('token')
            this.controle.dados.token = token || ''
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
            const params = {
                pages: d.pages || 20,
                ordenacao: d.ordenacao || 'created_at_desc'
            }
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
                .post(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/atualizacao-status`, this.formConfirmacao)
                .then((res) => {
                    this.preloadAtualizacao = false
                    $('#janelaAtualizaStatus').modal('hide')
                    mostraSucesso('Status atualizados com sucesso!')
                    this.selecionados = []
                    this.formConfirmacao = _.cloneDeep(this.formConfirmacaoDefault) //copia
                    this.$refs.componente.buscar()
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
                .post(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista`, this.form)
                .then((response) => {
                    $(`#${this.hash} `).modal('hide')
                    let data = response.data
                    mostraSucesso('', 'Solicitação registrada com sucesso!')
                    this.$refs.componente.buscar()
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
            this.demissaoAssinaturaSelecionada = item;
            this.signatariosAssinaturaDemissao = [{ nome: item.colaborador_nome || '', email: '', cpf: '' }];
            this.preloadAssinaturaDemissao = false;
            this.$nextTick(() => $(`#modalAssinaturaDemissao_${this.hash}`).modal('show'));
        },
        temDocumentoAssinaturaDemissao(item) {
            const doc = item && item.documento_para_assinatura;
            return !!(doc && doc.id);
        },
        abrirModalGerenciarAssinaturaDemissao(item) {
            const doc = item && item.documento_para_assinatura;
            if (!doc || !doc.id) return;
            this.demissaoParaReenvio = item;
            this.documentoAssinaturaDetalheDemissao = null;
            this.preloadGerenciarAssinaturaDemissao = true;
            $(`#modalGerenciarAssinaturaDemissao_${this.hash}`).modal('show');
            const idOrToken = doc.token || doc.id;
            axios.get(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}`).then(res => {
                this.documentoAssinaturaDetalheDemissao = res.data;
                this.preloadGerenciarAssinaturaDemissao = false;
            }).catch(() => {
                this.preloadGerenciarAssinaturaDemissao = false;
                mostraErro('', 'Erro ao carregar detalhe do documento.');
            });
        },
        documentoExpiradoOuCanceladoDocDemissao(doc) {
            return doc && (doc.status === 'expirado' || doc.status === 'cancelado');
        },
        enviarNovamenteNoModalDemissao() {
            if (!this.demissaoParaReenvio) return;
            $(`#modalGerenciarAssinaturaDemissao_${this.hash}`).modal('hide');
            this.$nextTick(() => this.abrirModalAssinaturaDemissao(this.demissaoParaReenvio));
        },
        labelTipoDocDemissao(tipo) {
            const map = { contrato_legal: 'Contrato (Documentos Legais)', contrato_trabalho: 'Contrato de Trabalho', carta_oferta: 'Carta Oferta', termo_demissao: 'Termo de Demissão', ficha_encaminhamento: 'Ficha de Encaminhamento', termo_confidencialidade: 'Termo de Confidencialidade', opcao_vale_transporte: 'Opção Vale Transporte', acordo_compensacao_horas: 'Acordo de Compensação de Horas', termo_salario_familia: 'Termo Salário Família', declaracao_dependentes_ir: 'Declaração Dependentes IR', medida_administrativa: 'Medida Administrativa', documento_demissao: 'Documento de Demissão (Aviso Prévio)' };
            return map[tipo] || tipo || '—';
        },
        labelStatusDocDemissao(status) {
            const map = { rascunho: 'Rascunho', enviado: 'Enviado', em_assinatura: 'Em assinatura', concluido: 'Concluído', expirado: 'Expirado', cancelado: 'Cancelado' };
            return map[status] || status || '—';
        },
        badgeStatusDocDemissao(status) {
            const map = { em_assinatura: 'badge-warning', concluido: 'badge-success', cancelado: 'badge-danger', expirado: 'badge-secondary', rascunho: 'badge-secondary', enviado: 'badge-info' };
            return map[status] || 'badge-secondary';
        },
        formatarDataDocDemissao(val) {
            if (!val) return '—';
            const d = typeof val === 'string' ? new Date(val) : val;
            return d.toLocaleDateString('pt-BR') + ' ' + (d.toLocaleTimeString ? d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : '');
        },
        labelEventoDocDemissao(evento) {
            const map = { enviado: 'Documento enviado', reenviado: 'E-mail reenviado', visualizado: 'Visualizado pelo signatário', assinado: 'Assinado', recusado: 'Recusado', expirado: 'Documento expirado', cancelado: 'Documento cancelado' };
            return map[evento] || evento;
        },
        podeCancelarDocDemissao(item) {
            return item && ['rascunho', 'em_assinatura'].indexOf(item.status) !== -1;
        },
        podeReenviarDocDemissao(item) {
            return item && item.status === 'em_assinatura';
        },
        podeBaixarAssinadoDocDemissao(item) {
            return item && item.status === 'concluido' && item.arquivo_assinado_id;
        },
        urlDownloadAssinadoDocDemissao(doc) {
            const idOrToken = (doc && doc.token) ? doc.token : (doc && doc.id) ? doc.id : '';
            return `${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/download-assinado`;
        },
        cancelarDocNoModalDemissao() {
            if (!this.documentoAssinaturaDetalheDemissao) return;
            const confirmar = () => this.executarCancelarDocDemissao(this.documentoAssinaturaDetalheDemissao);
            if (!this.$swal) {
                if (confirm('Cancelar este documento? Os signatários não poderão mais assinar.')) confirmar();
                return;
            }
            this.$swal.fire({ title: 'Cancelar documento?', text: 'Os signatários não poderão mais assinar. Esta ação não pode ser desfeita.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonText: 'Não', confirmButtonText: 'Sim, cancelar' }).then((result) => {
                if (result.isConfirmed) confirmar();
            });
        },
        executarCancelarDocDemissao(doc) {
            const idOrToken = (doc && doc.token) ? doc.token : (doc && doc.id) ? doc.id : '';
            axios.post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/cancelar`).then(res => {
                mostraSucesso(res.data.message || 'Documento cancelado.');
                this.documentoAssinaturaDetalheDemissao = null;
                this.atualizar();
                $(`#modalGerenciarAssinaturaDemissao_${this.hash}`).modal('hide');
            }).catch(err => {
                const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao cancelar.';
                mostraErro(msg);
            });
        },
        reenviarDocNoModalDemissao() {
            if (!this.documentoAssinaturaDetalheDemissao || this.documentoAssinaturaDetalheDemissao.status !== 'em_assinatura') return;
            const idOrToken = this.documentoAssinaturaDetalheDemissao.token || this.documentoAssinaturaDetalheDemissao.id;
            axios.post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/reenviar-email`).then(res => {
                mostraSucesso(res.data.message || 'E-mail reenviado.');
            }).catch(err => {
                const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao reenviar e-mail.';
                mostraErro(msg);
            });
        },
        adicionarSignatarioAssinaturaDemissao() {
            this.signatariosAssinaturaDemissao.push({ nome: '', email: '', cpf: '' });
        },
        removerSignatarioAssinaturaDemissao(index) {
            this.signatariosAssinaturaDemissao.splice(index, 1);
        },
        enviarParaAssinaturaDemissao() {
            const payload = {
                demissao_prevista_id: this.demissaoAssinaturaSelecionada.id,
                signatarios: this.signatariosAssinaturaDemissao.map(s => ({ nome: s.nome, email: s.email, cpf: s.cpf || null }))
            };
            this.preloadAssinaturaDemissao = true;
            axios.post(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/enviar-para-assinatura`, payload)
                .then(res => {
                    this.preloadAssinaturaDemissao = false;
                    $(`#modalAssinaturaDemissao_${this.hash}`).modal('hide');
                    mostraSucesso(res.data.message || 'Documento enviado para assinatura.');
                    this.atualizar();
                    if (res.data.links && res.data.links.length && this.$swal) {
                        const msg = res.data.links.map(l => `${l.email}: ${l.link}`).join('\n');
                        this.$swal.fire({ title: 'Links enviados', text: msg, icon: 'info' });
                    }
                })
                .catch(err => {
                    this.preloadAssinaturaDemissao = false;
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao enviar para assinatura.';
                    mostraErro(msg);
                });
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
                    $(`#${this.hash} `).modal('hide')
                    let data = response.data
                    mostraSucesso('', 'Solicitação alterada com sucesso!')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        aprovarGestor() {
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${this.form.id}/aprovar`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    $(`#${this.hash} `).modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        aprovarExtra() {
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }
            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${this.form.id}/aprovarextra`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    $(`#${this.hash} `).modal('hide')
                    this.$refs.componente.buscar()
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
                .put(`${URL_ADMIN}/planejamento/movimentacao/demissao-prevista/${this.form.id}/aprovarrh`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    $(`#${this.hash} `).modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
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
        carregou(dados) {
            this.lista = dados.itens
            this.mimes = dados.mimes
            this.aprovar_por_gestor = dados.aprovar_por_gestor
            this.aprovaGestor = dados.aprovar_por_gestor
            this.aprovaRh = dados.aprovar_por_rh
            this.podeAprovarExtra = dados.pode_aprovar_extra || false
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || ''

            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs.componente.atual = 1
            this.$refs.componente.buscar()
        }
    }
}
</script>

<style scoped>
/* ==================== LAYOUT DE CARDS COMPACTOS ==================== */

/* Checkbox Geral */
.checkbox-geral-container {
    background: #fff;
    padding: 0.75rem 1rem;
    border-radius: 8px 8px 0 0;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 0;
}

.checkbox-geral-label {
    display: flex;
    align-items: center;
    margin: 0;
    font-weight: 500;
    color: #495057;
    cursor: pointer;
}

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

.checkbox-inline {
    margin: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.custom-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #174257;
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

.data-badge {
    display: flex;
    align-items: center;
    background: #fff5f5;
    color: #dc3545;
    padding: 0.375rem 0.625rem;
    border-radius: 6px;
    border-left: 3px solid #dc3545;
    font-weight: 500;
    font-size: 0.813rem;
    white-space: nowrap;
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

.status-pendente {
    background: #e9ecef;
    color: #495057;
}

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

.fluxo-label {
    font-size: 0.813rem;
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
    padding-top: 0.25rem;
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
    min-width: 0;
}

.fluxo-etapa {
    font-size: 0.688rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.fluxo-aprovador {
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fluxo-status {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6c757d;
}

.fluxo-data {
    font-size: 0.688rem;
    color: #6c757d;
    white-space: nowrap;
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

/* ==================== CARDS E ESTILOS ORIGINAIS ==================== */

/* Cards com transição suave */
.card {
    transition: all 0.3s ease;
    border-radius: 8px;
}

.card:hover {
    transform: translateY(-2px);
}

/* Headers de card com gradiente sutil */
.card-header {
    border-radius: 8px 8px 0 0 !important;
    padding: 1rem 1.25rem;
    border: none;
}

.card-header h5 {
    color: #fff !important;
}

.card-header.bg-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

.card-header.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
}

.card-header.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%) !important;
}

.card-header.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%) !important;
}

/* Labels modernos */
label {
    font-size: 0.875rem;
    letter-spacing: 0.3px;
    margin-bottom: 0.5rem;
    color: #495057;
}

/* Inputs com foco melhorado */
.form-control:focus,
.form-control-sm:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.form-control,
.form-control-sm {
    border-radius: 6px;
    border: 1.5px solid #ced4da;
    transition: all 0.2s ease;
}

.form-control:hover:not(:disabled),
.form-control-sm:hover:not(:disabled) {
    border-color: #80bdff;
}

/* Textarea melhorado */
textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

/* Select estilizado */
select.form-control,
select.form-control-sm {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
    padding-right: 2.5rem;
}

/* Badges modernos */
.badge {
    padding: 0.4rem 0.8rem;
    font-weight: 500;
    font-size: 0.75rem;
    border-radius: 20px;
    letter-spacing: 0.3px;
}

.badge-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.badge-danger {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
}

.badge-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #212529;
}

.badge-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
}

/* Ícones com animação */
.fas, .far {
    transition: transform 0.2s ease;
}

.card-header .fas {
    margin-right: 0.5rem;
}

/* Alertas modernizados */
.alert {
    border-radius: 8px;
    border-left: 4px solid;
    padding: 1rem 1.25rem;
}

.alert-warning {
    background-color: #fff3cd;
    border-left-color: #ffc107;
}

.alert-success {
    background-color: #d4edda;
    border-left-color: #28a745;
}

.alert-info {
    background-color: #d1ecf1;
    border-left-color: #17a2b8;
}

/* Setas do fluxo com animação */
.fa-arrow-right, .fa-chevron-right {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

/* Cards do fluxo com hover */
.card.border-success:hover {
    border-color: #28a745 !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
}

.card.border-primary:hover {
    border-color: #007bff !important;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
}

.card.border-danger:hover {
    border-color: #dc3545 !important;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
}

/* Ícones do fluxo com hover */
.fas.fa-check-circle,
.fas.fa-times-circle,
.fas.fa-clock,
.fas.fa-circle {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.fas.fa-check-circle:hover,
.fas.fa-times-circle:hover,
.fas.fa-clock:hover {
    transform: scale(1.15);
}

/* Melhorias no card de anexos */
.card.border-info {
    border-width: 2px;
}

.card.border-info .card-header {
    background-color: #f8f9fa !important;
}

/* Espaçamento entre seções */
.card + .card {
    margin-top: 1.5rem;
}

/* Botões com melhor feedback */
.btn {
    font-weight: 500;
    transition: all 0.2s ease;
    letter-spacing: 0.3px;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn:active {
    transform: translateY(0);
}

/* Form groups com melhor espaçamento */
.form-group {
    margin-bottom: 1.25rem;
}

/* Row com melhor espaçamento */
.row {
    margin-bottom: 0.75rem;
}

/* Títulos h5 modernos */
h5 {
    font-weight: 600;
    font-size: 1.1rem;
    letter-spacing: 0.3px;
}

/* Small text melhorado */
small {
    font-size: 0.8rem;
    line-height: 1.4;
}

/* Status legend melhorado */
legend {
    font-weight: 500;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 1rem;
}

/* Asterisco obrigatório */
.text-danger {
    font-weight: 700;
}

/* Responsividade melhorada */
@media (max-width: 768px) {
    .card-header h5 {
        font-size: 1rem;
    }

    .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
    }

    .fa-arrow-right {
        font-size: 18px !important;
    }
}

/* Estados disabled com melhor visual */
.form-control:disabled,
.form-control-sm:disabled {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.7;
}



/* Card body com padding consistente */
.card-body {
    padding: 1.5rem;
}

/* Shadow consistente */
.shadow-sm {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
}
</style>
