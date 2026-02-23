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
                <form v-if="!preload && (!cadastrado && !atualizado) " :id="`form_${hash}`" onsubmit="return false;">
                    <fieldset>
                        <legend>Para qual colaborador?</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Colaborador <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :caminho="`autocomplete/colaboradores`"
                                        :formsm="true"
                                        :valido="form.colaborador_id !== ''"
                                        v-model="form.autocomplete_label_colaborador"
                                        placeholder="Selecione um(a) colaborador(a)"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        :id="`colaborador_${hash}`"
                                        @onblur="resetaCampoColaborador"
                                        @onselect="selecionaColaborador"
                                    ></autocomplete>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id">
                        <legend>Dados Atuais</legend>
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Centro de Custo Atual</label>
                                    <select
                                        v-model="form.anterior_centro_custo_id"
                                        class="form-control form-control-sm"
                                        disabled
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-1" v-if="centroCustoTemFilial">
                                <div class="form-group">
                                    <label>CNPJ Atual</label>
                                    <select
                                        v-model="form.anterior_filial"
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeCnpj()"
                                        disabled
                                    >
                                        <option :value="false">Matriz</option>
                                        <option :value="true">Filial</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-5" v-if="temFilial && form.anterior_filial">
                                <div class="form-group">
                                    <label>Filial</label>
                                    <select
                                        v-model="form.anterior_centro_custo_filial_id"
                                        class="form-control"
                                        disabled
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centroCustoSelecionado" :value="item.id" :key="item.id">
                                            {{ item.filial.razao_social }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Cargo Atual</label>
                                    <autocomplete
                                        :disabled="true" :caminho="caminho_autocomplete_vagas"
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
                                    <label>Função Atual</label>
                                    <input
                                        type="text" class="form-control form-control-sm"
                                        onblur="valida_campo_vazio(this,2)"
                                        v-model="form.anterior_funcao"
                                        disabled
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label>Salário Atual R$</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-mascara:dinheiro
                                        v-model="form.anterior_salario"
                                        disabled
                                    >
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id">
                        <legend>Sobre Centro de Custo</legend>
                        <div class="row">
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label>Mantém Centro de Custo</label>
                                    <select
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeMantemCentroDeCusto()"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.mantem_centro_custo"
                                    >
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="!form.mantem_centro_custo">
                                <div class="form-group">
                                    <label>Novo Centro de Custo <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.novo_centro_custo_id"
                                        class="form-control form-control-sm"
                                        @change.prevent="changeCentroCusto()"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-2" v-if="centroCustoTemFilialNovo && !form.mantem_centro_custo">
                                <div class="form-group">
                                    <label>Novo CNPJ</label>
                                    <select
                                        v-model="form.novo_filial"
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeCnpj()"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                    >
                                        <option :value="false">Matriz</option>
                                        <option :value="true">Filial</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4"
                                 v-if="temFilial && form.novo_filial && !form.mantem_centro_custo"
                            >
                                <div class="form-group">
                                    <label>Nova Filial</label>
                                    <select
                                        v-model="form.novo_centro_custo_filial_id"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centroCustoSelecionadoNovo" :value="item.id"
                                                :key="item.id"
                                        >
                                            {{ item.filial.razao_social }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id">
                        <legend>Sobre a Função</legend>
                        <div class="row">
                            <div class="col-12 col-md-2" v-if="form.anterior_funcao">
                                <div class="form-group">
                                    <label>Mantém Função</label>
                                    <select
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeMantemFuncao()"
                                        v-model="form.mantem_funcao"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    >
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6" v-if="!form.mantem_funcao">
                                <div class="form-group">
                                    <label>Nova Função</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        onblur="valida_campo_vazio(this,2)"
                                        v-model="form.nova_funcao"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    >
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id">
                        <legend>Treinamento/Certificado</legend>
                        <div class="row">
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label>Treinamento na Função</label>
                                    <select class="form-control form-control-sm"
                                            @change.p.prevent="changeTreinamentoFuncao()"
                                            v-model="form.treinamento_funcao"
                                            :disabled="visualizar || aprovandoRh || aprovando"
                                    >
                                        <option :value="false">Não</option>
                                        <option :value="true">Sim</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-3" v-if="form.treinamento_funcao">
                                <div class="form-group">
                                    <label>Data Início</label>
                                    <datepicker formsm label="" class="corrigiDatepicker"
                                                v-model="form.treinamento_data_inicio"
                                                :disabled="visualizar || aprovandoRh || aprovando"
                                    ></datepicker>
                                </div>
                            </div>
                            <div class="col-12 col-md-3" v-if="form.treinamento_funcao">
                                <div class="form-group">
                                    <label>Data Fim</label>
                                    <datepicker formsm label="" class="corrigiDatepicker"
                                                v-model="form.treinamento_data_fim"
                                                :disabled="visualizar || aprovandoRh || aprovando"
                                    ></datepicker>
                                </div>
                            </div>
                        </div>
                        <div class="row" v-if="form.treinamento_funcao">
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Treinamento</h6>
                                    </div>
                                    <div class="card-body">
                                        <upload :model="form.treinamento_certificado"
                                                :model-delete="form.treinamento_certificadoDel" :url="url_anexo"
                                                :tipos="mimes"
                                                :leitura="!podeanexar || visualizar || aprovandoRh || aprovando"
                                                label="Selecionar" @onProgresso="anexoUploadAndamento = true"
                                                @onFinalizado="anexoUploadAndamento = false"
                                        ></upload>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Termo de Ciência</h6>
                                    </div>
                                    <div class="card-body">
                                        <upload :model="form.treinamento_termo_ciencia"
                                                :model-delete="form.treinamento_termo_cienciaDel" :url="url_anexo"
                                                :tipos="mimes"
                                                :leitura="!podeanexar || visualizar || aprovandoRh || aprovando"
                                                label="Selecionar" @onProgresso="anexoUploadAndamento = true"
                                                @onFinalizado="anexoUploadAndamento = false"
                                        ></upload>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id">
                        <legend>Sobre o Cargo</legend>
                        <div class="row">
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label>Mantém Cargo</label>
                                    <select
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeMantemCargo()"
                                        v-model="form.mantem_cargo"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    >
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6" v-if="!form.mantem_cargo">
                                <div class="form-group">
                                    <label>Novo Cargo <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :caminho="caminho_autocomplete_vagas"
                                        :valido="form.autocomplete_label_vaga_nova !== ''"
                                        v-model="form.autocomplete_label_vaga_nova"
                                        placeholder="Novo Cargo"
                                        @onselect="selecionaVagaNovo"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    ></autocomplete>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id">
                        <legend>Sobre o Salário</legend>
                        <div class="row">
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label>Mantém Salário</label>
                                    <select
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeMantemSalario()"
                                        v-model="form.mantem_salario"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    >
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-3" v-if="!form.mantem_salario">
                                <div class="form-group">
                                    <label>Novo Salário R$</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-mascara:dinheiro
                                        v-model="form.novo_salario"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    >
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id">
                        <legend>Gestor Responsável</legend>
                        <div class="row">
                            <gestoraprovacao
                                label="Gestor Aprovação *"
                                formsm
                                :model="form"
                                :verifica="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                :hash="hash_gestor"
                                obrigatorio
                                :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                            >
                            </gestoraprovacao>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id">
                        <legend>Informações Extras</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.obs_solicitante"
                                        cols="5" rows="5"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    ></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 mt-4 mb-4" v-if="visualizar">
                                <legend>Solicitação feita por: {{
                                        form.solicitante !== null ? form.solicitante.nome : ''
                                    }} {{ form.data_solicitacao }}
                                </legend>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="!form.data_aprovacao_gestor && !cadastrando">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo gestor!
                    </div>

                    <fieldset v-if="visualizar || aprovando">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div
                                v-if="!aprovando && (form.gestor_aprovacao && (form.gestor_aprovacao.nome || typeof form.gestor_aprovacao === 'string'))"
                                class="col-12"
                            >
                                <legend>{{ form.status_aprovacao_gestor }}
                                    por:
                                    {{ (form.gestor_aprovacao && form.gestor_aprovacao.nome) || form.gestor_aprovacao || ''
                                    }} em {{ form.data_aprovacao_gestor }}
                                </legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control form-control-sm" :disabled="!aprovando"
                                              v-model="form.obs_gestor_aprovacao"
                                              cols="5" rows="5"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select :disabled="!aprovando"
                                            v-model="form.status_aprovacao_gestor"
                                            class="form-control form-control-sm validacampo"
                                            onchange="valida_campo_vazio(this, 1)" onblur="valida_campo_vazio(this, 1)"
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
                            <div v-if="!aprovandoExtra && form.aprovacao_extra_nome" class="col-12">
                                <legend>{{ form.status_aprovacao_extra }}
                                    por: {{ form.aprovacao_extra_nome }} em {{ form.data_aprovacao_extra }}
                                </legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control form-control-sm" :disabled="!aprovandoExtra"
                                              v-model="form.obs_aprovacao_extra"
                                              cols="5" rows="5"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select :disabled="!aprovandoExtra"
                                            v-model="form.status_aprovacao_extra"
                                            class="form-control form-control-sm validacampo"
                                            onchange="valida_campo_vazio(this, 1)" onblur="valida_campo_vazio(this, 1)"
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
                            <div
                                v-if="!aprovandoRh && (form.rh_aprovacao && (form.rh_aprovacao.nome || typeof form.rh_aprovacao === 'string'))"
                                class="col-12"
                            >
                                <legend>{{ form.status_aprovacao_rh }} por:
                                    {{ (form.rh_aprovacao && form.rh_aprovacao.nome) || form.rh_aprovacao || '' }} em
                                    {{ form.data_aprovacao_rh }}
                                </legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control form-control-sm" :disabled="!aprovandoRh"
                                              v-model="form.obs_rh"
                                              cols="5" rows="5"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select :disabled="!aprovandoRh"
                                            v-model="form.status_aprovacao_rh"
                                            class="form-control form-control-sm validacampo"
                                            onchange="valida_campo_vazio(this, 1)" onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione...</option>
                                        <option value="aprovado">Aprovar</option>
                                        <option value="reprovado">Reprovar</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.colaborador_id">
                        <legend>Anexos</legend>
                        <upload :model="form.anexos"
                                :model-delete="form.anexosDel"
                                :url="url_anexo"
                                :tipos="mimes"
                                :leitura="!podeanexar"
                                label="Selecionar"
                                @onProgresso="anexoUploadAndamento=true"
                                @onFinalizado="anexoUploadAndamento=false"
                        ></upload>
                    </fieldset>
                </form>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="cadastrando && !preload"
                        @click.prevent="cadastrar"
                >
                    <i class="fa fa-save"></i> Cadastrar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovando && !preload" @click.prevent="aprovarGestor"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovandoExtra && !preload" @click.prevent="aprovarExtra"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovandoRh && !preload" @click.prevent="aprovarRh"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <fieldset class="mt-0">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <date-range-filter
                    :enabled.sync="controle.dados.filtroPeriodo"
                    :start-date.sync="controle.dados.dataInicio"
                    :end-date.sync="controle.dados.dataFim"
                    :disabled="controle.carregando"
                    :id-suffix="hash"
                    wrapper-class="col-12 col-md-3"
                />
                <div class="col-12 col-md-5">
                    <div class="form-group">
                        <label>Pesquisar</label>
                        <input type="text"
                               placeholder="Buscar por colaborador"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca"
                        >
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoStatusAprovacao"
                                :disabled="controle.carregando" @change="atualizar()"
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
                        <select class="form-control form-control-sm" v-model="controle.dados.ordenacao"
                                :disabled="controle.carregando" @change="atualizar()"
                        >
                            <option value="created_at_desc">Mais recente</option>
                            <option value="created_at_asc">Mais antigo</option>
                            <option value="updated_at_desc">Última modificação</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar()"
                                :disabled="controle.carregando"
                                v-model="controle.dados.pages"
                        >
                            <option v-for="item in por_pagina" :value="item">{{ item }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12"></div>

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar"
                    >
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                            :disabled="controle.carregando"
                            :data-target="`#${hash}`"
                            @click.prevent="formNovo"
                    >
                        Solicitar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary  mr-1"
                            @click.prevent="exportaExcel()"
                            :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && !lista.length) "
                    >
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                    </button>

                    <button type="submit" class="btn btn-sm btn-primary mr-1" v-show="selecionados.length > 0"
                            :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="selecionados.length === 0"
                            data-toggle="modal"
                            data-target="#janelaAtualizaStatus"
                    >
                        Atualizar Status <span class="badge badge-light">{{ selecionados.length }}</span>
                    </button>
                </div>
            </form>
        </fieldset>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
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
                            <!-- <label class="checkbox-inline">
                                <input type="checkbox"
                                       class="custom-checkbox"
                                       :value="item.id"
                                       v-model="selecionados">
                            </label> -->
                            <span class="badge-id">#{{ item.id }}</span>
                            <div class="colaborador-principal">
                                <i class="fas fa-user-circle text-primary mr-1"></i>
                                <strong>{{ item.colaborador ? item.colaborador.nome : '' }}</strong>
                            </div>
                            <div class="data-info ml-3">
                                <i class="fas fa-calendar-plus text-muted" style="font-size: 0.75rem;"></i>
                                <small class="text-muted">{{ item.created_at }}</small>
                                <span v-if="item.updated_at && item.updated_at !== item.created_at"
                                      class="mx-2 text-muted"
                                >|</span>
                                <template v-if="item.updated_at && item.updated_at !== item.created_at">
                                    <i class="fas fa-calendar-check text-info" style="font-size: 0.75rem;"></i>
                                    <small class="text-info">{{ item.updated_at }}</small>
                                </template>
                            </div>
                        </div>
                        <div class="card-right">
                            <span
                                class="status-badge"
                                :class="{
                                    'status-reprovado': item.status_aprovacao_gestor === 'reprovado' || item.status_aprovacao_extra === 'reprovado' || item.status_aprovacao_rh === 'reprovado',
                                    'status-aprovado': item.status_aprovacao_rh === 'aprovado' || (item.status_aprovacao_gestor === 'aprovado' && item.status_aprovacao_rh === null && item.aprovado_via_script),
                                    'status-aprovado-gestor': item.status_aprovacao_gestor === 'aprovado' && item.status_aprovacao_rh === null && !item.aprovado_via_script && (!temAprovacaoExtra || item.status_aprovacao_extra === 'aprovado'),
                                    'status-aprovado-extra': temAprovacaoExtra && item.status_aprovacao_gestor === 'aprovado' && item.status_aprovacao_extra === 'aprovado' && !item.status_aprovacao_rh,
                                    'status-pendente': !item.status_aprovacao_gestor,
                                }"
                            >
                                <span
                                    v-if="item.status_aprovacao_gestor === 'reprovado' || item.status_aprovacao_extra === 'reprovado' || item.status_aprovacao_rh === 'reprovado'"
                                >
                                    <i class="fas fa-times-circle"></i> REPROVADO
                                </span>
                                <span v-else-if="item.status_aprovacao_rh === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO RH
                                </span>
                                <span
                                    v-else-if="temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado' && !item.status_aprovacao_rh"
                                >
                                    <i class="fas fa-check-circle"></i> APROVADO {{ nomeAprovacaoExtra.toUpperCase() }}
                                </span>
                                <span
                                    v-else-if="item.status_aprovacao_gestor === 'aprovado' && !item.status_aprovacao_rh"
                                >
                                    <i class="fas fa-check-circle"></i> APROVADO GESTOR
                                </span>
                                <span v-else>
                                    <i class="fas fa-clock"></i> EM ABERTO
                                </span>
                            </span>
                            <div class="dropdown show">
                                <a class="btn-actions-compact" href="#" role="button"
                                   id="dropdownMenuLink"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                     aria-labelledby="dropdownMenuLink"
                                >
                                    <a class="dropdown-item" href="javascript://" title="Aprovação Gestor"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = true; aprovandoExtra = false; aprovandoRh = false; podeanexar = true"
                                       v-if="item.gestor_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor"
                                    >
                                        Aprovação Gestor
                                    </a>

                                    <a class="dropdown-item" href="javascript://"
                                       :title="nomeAprovacaoExtra || 'Aprovação Extra'"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; podeanexar = false"
                                       v-if="temAprovacaoExtra && aprovaExtra && item.status_aprovacao_gestor === 'aprovado' && !item.aprovacao_extra_id && !item.aprovado_via_script && !item.rh_aprovacao_id"
                                    >
                                        {{ nomeAprovacaoExtra || 'Aprovação Extra' }}
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Aprovação RH"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; podeanexar = false"
                                       v-if="((item.status_aprovacao_gestor === 'aprovado' && !temAprovacaoExtra) || (item.status_aprovacao_extra === 'aprovado')) && !item.aprovado_via_script && item.rh_aprovacao_id === null && aprovaRh"
                                    >
                                        Aprovação Rh
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = false; podeanexar = false"
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
                            <i class="fas fa-exchange-alt text-muted"></i>
                            <span class="detail-label">Centro Custo:</span>
                            <span class="detail-value">{{ item.mantem_centro_custo ? 'Não mudou' : 'Mudou' }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-briefcase text-muted"></i>
                            <span class="detail-label">Cargo:</span>
                            <span class="detail-value">{{ item.mantem_cargo ? 'Não mudou' : 'Mudou' }}</span>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-tasks text-muted"></i>
                            <span class="detail-label">Função:</span>
                            <span class="detail-value">{{ item.mantem_funcao ? 'Não mudou' : 'Mudou' }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign text-success"></i>
                            <span class="detail-label">Salário:</span>
                            <span class="detail-value">{{ item.mantem_salario ? 'Não mudou' : 'Mudou' }}</span>
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
                                        {{ item.solicitante.nome }}
                                    </small>
                                    <small class="fluxo-data">{{ item.created_at }}</small>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted mx-2"></i>
                            <!-- Gestor -->
                            <div class="fluxo-step">
                                <i v-if="item.status_aprovacao_gestor === 'aprovado'"
                                   class="fas fa-check-circle text-success"
                                ></i>
                                <i v-else-if="item.status_aprovacao_gestor === 'reprovado'"
                                   class="fas fa-times-circle text-danger"
                                ></i>
                                <i v-else
                                   class="fas fa-clock text-muted"
                                ></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Gestor</small>
                                    <small v-if="item.status_aprovacao_gestor === 'aprovado'"
                                           class="fluxo-aprovador text-success"
                                    >
                                        {{ item.gestor_aprovacao.nome || '' }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_gestor === 'reprovado'"
                                           class="fluxo-aprovador text-danger"
                                    >
                                        {{ (item.gestor_aprovacao && item.gestor_aprovacao.nome) || item.gestor_aprovacao || ''
                                        }}
                                    </small>
                                    <small v-else class="fluxo-status text-warning">Aguardando</small>
                                    <small v-if="item.data_aprovacao_gestor" class="fluxo-data"
                                    >{{ item.data_aprovacao_gestor }}</small>
                                </div>
                            </div>

                            <i class="fas fa-chevron-right text-muted mx-2"></i>

                            <!-- Aprovação Extra (se configurada) -->
                            <div class="fluxo-step" v-if="temAprovacaoExtra">
                                <i v-if="item.status_aprovacao_gestor === 'reprovado'"
                                   class="fas fa-ban text-secondary"
                                ></i>
                                <i v-else-if="item.status_aprovacao_extra === 'aprovado'"
                                   class="fas fa-check-circle text-success"
                                ></i>
                                <i v-else-if="item.status_aprovacao_extra === 'reprovado'"
                                   class="fas fa-times-circle text-danger"
                                ></i>
                                <i v-else-if="item.status_aprovacao_gestor === 'aprovado' && !item.status_aprovacao_extra"
                                   class="fas fa-clock text-warning"
                                ></i>
                                <i v-else
                                   class="fas fa-circle text-muted"
                                ></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">{{ nomeAprovacaoExtra }}</small>
                                    <small v-if="item.status_aprovacao_gestor === 'reprovado'"
                                           class="fluxo-status text-secondary"
                                    >
                                        Cancelada
                                    </small>
                                    <small v-else-if="item.status_aprovacao_extra === 'aprovado'"
                                           class="fluxo-aprovador text-success"
                                    >
                                        {{ item.aprovacao_extra_nome || '' }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_extra === 'reprovado'"
                                           class="fluxo-aprovador text-danger"
                                    >
                                        {{ item.aprovacao_extra_nome || '' }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_gestor === 'aprovado'"
                                           class="fluxo-status text-warning"
                                    >
                                        Aguardando
                                    </small>
                                    <small v-else class="fluxo-status">Pendente</small>
                                    <small v-if="item.data_aprovacao_extra" class="fluxo-data"
                                    >{{ item.data_aprovacao_extra }}</small>
                                </div>
                            </div>

                            <i class="fas fa-chevron-right text-muted mx-2" v-if="temAprovacaoExtra"></i>

                            <!-- RH -->
                            <div class="fluxo-step">
                                <i v-if="item.status_aprovacao_gestor === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')"
                                   class="fas fa-ban text-secondary"
                                ></i>
                                <i v-else-if="item.status_aprovacao_rh === 'aprovado'"
                                   class="fas fa-check-circle text-success"
                                ></i>
                                <i v-else-if="item.status_aprovacao_rh === 'reprovado'"
                                   class="fas fa-times-circle text-danger"
                                ></i>
                                <i v-else-if="(temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') || (!temAprovacaoExtra && item.status_aprovacao_gestor === 'aprovado')"
                                   class="fas fa-clock text-warning"
                                ></i>
                                <i v-else
                                   class="fas fa-circle text-muted"
                                ></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">RH</small>
                                    <small
                                        v-if="item.status_aprovacao_gestor === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')"
                                        class="fluxo-status text-secondary"
                                    >
                                        Cancelada
                                    </small>
                                    <small v-else-if="item.status_aprovacao_rh === 'aprovado'"
                                           class="fluxo-aprovador text-success"
                                    >
                                        {{ item.rh_aprovacao.nome || '' }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_rh === 'reprovado'"
                                           class="fluxo-aprovador text-danger"
                                    >
                                        {{ (item.rh_aprovacao && item.rh_aprovacao.nome) || item.rh_aprovacao || '' }}
                                    </small>
                                    <small
                                        v-else-if="(temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') || (!temAprovacaoExtra && item.status_aprovacao_gestor === 'aprovado')"
                                        class="fluxo-status text-warning"
                                    >
                                        Aguardando
                                    </small>
                                    <small v-else class="fluxo-status">Pendente</small>
                                    <small v-if="item.data_aprovacao_rh" class="fluxo-data">{{ item.data_aprovacao_rh
                                        }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            :url="urlPaginacao" :por-pagina="controle.dados.pages"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"
        />
    </div>
</template>

<script>
import Upload from '../../Upload'
import colaborador from '../../Colaborador'
import gestoraprovacao from '../../GestorAprovacao'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Utils from '../../../mixins/Utils'
import configuracoes from '../../../mixins/Configuracoes'
import DateRangeFilter from '../../DateRangeFilter'

export default {
    mixins: [ExportacaoMixin, Utils, configuracoes],
    inject: {
        atualizarUrlMovimentacao: {
            default: () => () => {
            }
        }
    },
    components: {
        colaborador,
        DateRangeFilter,
        gestoraprovacao,
        Upload
    },
    data() {
        return {
            tituloJanela: 'Solicitacao de Mudança de Cargo',
            preload: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovando: false,
            aprovandoExtra: false,
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaExtra: false,
            aprovaRh: false,
            preloadExportacao: false,
            temAprovacaoExtra: false,
            nomeAprovacaoExtra: 'Aprovação Extra',

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/export`,
            url_anexo: `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`,
            anexoUploadAndamento: false,
            podeanexar: false,
            mimes: [],
            caminho_autocomplete_vagas: `autocomplete/todas-vagas-ativas`,

            hash: `mybp_${parseInt((Math.random() * 999999))}`,
            hash_gestor: `${parseInt((Math.random() * 999999))}`,

            colunasTabela: {
                cliente: false
            },

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
                admissao_id: '',
                colaborador_id: '',
                autocomplete_label_colaborador: '',

                mantem_centro_custo: true,
                anterior_centro_custo_id: '',
                anterior_centro_custo_filial_id: '',
                anterior_filial: '',
                novo_centro_custo_id: '',
                novo_centro_custo_filial_id: '',
                novo_filial: '',
                tipo_contrato: '',

                mantem_cargo: true,
                anterior_vaga_aberta_id: '',
                autocomplete_label_vaga_anterior: '',
                nova_vaga_aberta_id: '',
                autocomplete_label_vaga_nova: '',

                mantem_funcao: true,
                anterior_funcao: '',
                nova_funcao: '',

                treinamento_funcao: false,
                treinamento_data_inicio: '',
                treinamento_data_fim: '',
                treinamento_termo_ciencia: [],
                treinamento_termo_cienciaDel: [],
                treinamento_certificado: [],
                treinamento_certificadoDel: [],

                mantem_salario: true,
                anterior_salario: '0,00',
                novo_salario: '0,00',

                solicitante_id: '',
                autocomplete_label_solicitante: '',
                obs_solicitante: '',
                data_solicitacao: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',
                gestor_aprovacao_id: '',
                autocomplete_label_gestor_aprovacao: '',
                obs_gestor_aprovacao: '',
                status_aprovacao_gestor: '',
                data_aprovacao_gestor: '',

                aprovacao_extra_id: '',
                aprovacao_extra_nome: '',
                obs_aprovacao_extra: '',
                status_aprovacao_extra: '',
                data_aprovacao_extra: '',

                rh_aprovacao_id: '',
                autocomplete_label_rh: '',
                obs_rh: '',
                status_aprovacao_rh: '',
                data_aprovacao_rh: '',
                aprovado_via_script: false,

                anexos: [],
                anexosDel: []
            },

            formDefault: null,
            lista: [],
            centro_custos: [],
            filiais_centro_custos: [],

            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: '',
                    filtroPeriodo: false,
                    periodo: '',
                    campoStatusAprovacao: '',
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
        this.$nextTick(() => this.atualizar())
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
            return this.lista.filter(item => {
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
            this.naoAprovados.forEach(item => {
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
            if (this.form.anterior_centro_custo_id === undefined || this.form.anterior_centro_custo_id === null || this.form.anterior_centro_custo_id === '') {
                return []
            }
            let centroSelecionado = _.find(this.centro_custos, { id: this.form.anterior_centro_custo_id })
            if (centroSelecionado && centroSelecionado.filiais && centroSelecionado.filiais.length) {
                return centroSelecionado.filiais
            }
            return []
        },
        centroCustoSelecionadoNovo() {
            if (this.form.novo_centro_custo_id === undefined || this.form.novo_centro_custo_id === null || this.form.novo_centro_custo_id === '') {
                return []
            }
            let centroSelecionado = _.find(this.centro_custos, { id: this.form.novo_centro_custo_id })
            if (centroSelecionado && centroSelecionado.filiais && centroSelecionado.filiais.length) {
                return centroSelecionado.filiais
            }
            return []
        },
        centroCustoTemFilial() {
            return this.temFilial && this.centroCustoSelecionado.length > 0
        },
        centroCustoTemFilialNovo() {
            return this.temFilial && this.centroCustoSelecionadoNovo.length > 0
        },
        paramsExport() {
            return this.controle.dados
        }
    },
    methods: {
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
            this.form.novo_filial = false
            this.form.novo_centro_custo_filial_id = ''
        },
        changeMantemCentroDeCusto() {
            this.form.novo_centro_custo_filial_id = ''
            this.form.novo_centro_custo_id = ''
            this.form.novo_filial = ''
        },
        changeMantemFuncao() {
            this.form.nova_funcao = ''
        },
        changeTreinamentoFuncao() {
            if (!this.form.treinamento_funcao) {
                this.form.treinamento_data_inicio = ''
                this.form.treinamento_data_fim = ''
                this.form.treinamento_termo_ciencia = []
                this.form.treinamento_termo_cienciaDel = []
                this.form.treinamento_certificado = []
                this.form.treinamento_certificadoDel = []
            }
        },
        changeMantemCargo() {
            this.form.autocomplete_label_vaga_nova = ''
            this.form.nova_vaga_aberta_id = ''
        },
        changeMantemSalario() {
            this.form.novo_salario = ''
        },
        changeCnpj() {
            this.form.novo_centro_custo_filial_id = ''
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.naoAprovados.map(item => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.naoAprovados.map(item => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },
        selecionaVaga(obj) {
            this.form.anterior_vaga_aberta_id = obj.id
            this.form.autocomplete_label_vaga_anterior = obj.vaga.nome
        },
        selecionaVagaNovo(obj) {
            this.form.nova_vaga_aberta_id = obj.id
            this.form.autocomplete_label_vaga_nova = obj.vaga.nome
        },
        selecionaColaborador(obj) {
            this.form.colaborador_id = obj.curriculo_id
            this.form.autocomplete_label_colaborador = obj.label
            this.form.autocomplete_label_colaborador_anterior = obj.label

            this.form.anterior_centro_custo_id = obj.admissao.centro_custo_id
            this.form.anterior_filial = obj.admissao.filial
            this.form.anterior_centro_custo_filial_id = this.form.anterior_filial ? obj.admissao.centro_custo_filial_id : null
            this.form.admissao_id = obj.admissao.id
            this.form.anterior_funcao = obj.admissao.funcao
            this.form.anterior_vaga_aberta_id = obj.vaga_aberta.id
            this.form.autocomplete_label_vaga_anterior = obj.vaga_aberta.vaga.nome
            this.form.anterior_salario = obj.admissao.salario
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = ''
                this.form.autocomplete_label_colaborador = ''
                this.form.colaborador_id = ''

                this.form.anterior_centro_custo_id = ''
                this.form.anterior_filial = ''
                this.form.anterior_centro_custo_filial_id = ''
                this.form.admissao_id = ''
                this.form.anterior_funcao = ''
                this.form.anterior_vaga_aberta_id = ''
                this.form.autocomplete_label_vaga_anterior = ''
                this.form.anterior_salario = ''

                setTimeout(() => {
                    if (this.form.colaborador_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                        $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Colaborador não pode ficar vazio')
                    }
                }, 100)
            }
        },
        selecionaFilialCentroDeCusto(centro_custo_id, empresa_id) {
            axios.post(`${URL_ADMIN}/get-filiais/`, {
                centro_custo_id: centro_custo_id,
                empresa_id: empresa_id
            }).then(res => {
                this.filiais_centro_de_custo = res.data.filiais_centro_de_custo
            }).catch(error => {
                this.preload = false
            })
        },
        selecionaNovoCargo(obj) {
            this.form.novo_cargo_id = obj.id
            this.form.autocomplete_label_novo_cargo = obj.label
            this.form.autocomplete_label_novo_cargo_anterior = obj.label

            setTimeout(() => {
                if (this.form.novo_cargo_id !== '' && this.form.novo_cargo_id === this.form.cargo_anterior_id) {
                    valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1)
                    $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur')
                    mostraErro('Erro', 'O NOVO CARGO não pode ser igual ao CARGO ANTERIOR')
                    this.form.novo_cargo_id = ''
                    this.form.autocomplete_label_novo_cargo = ''
                    this.form.autocomplete_label_novo_cargo_anterior = ''
                }
            }, 100)

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
            axios.post(`${URL_PUBLICO}/centro-custos/`)
                .then(res => {
                    this.centro_custos = res.data.centro_custos
                })
                .catch(error => {
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
            this.visualizar = false
            this.podeanexar = true

            this.tituloJanela = 'Solicitação de Mudança de Cargo'
            this.form = _.cloneDeep(this.formDefault) //copia
            formReset()
            setupCampo()
            this.listaCentroCusto()
        },

        validarTreinamento() {
            if (!this.form.treinamento_funcao) {
                return true
            }

            if (!this.form.treinamento_data_inicio || this.form.treinamento_data_inicio.trim() === '') {
                mostraErro('', 'A data de início do treinamento é obrigatória')
                return false
            }

            if (!this.form.treinamento_data_fim || this.form.treinamento_data_fim.trim() === '') {
                mostraErro('', 'A data de fim do treinamento é obrigatória')
                return false
            }

            // Validar que data início não é maior que data fim
            if (this.form.treinamento_data_inicio && this.form.treinamento_data_fim) {
                const dataInicioParts = this.form.treinamento_data_inicio.split('/')
                const dataFimParts = this.form.treinamento_data_fim.split('/')

                if (dataInicioParts.length === 3 && dataFimParts.length === 3) {
                    const dataInicio = new Date(parseInt(dataInicioParts[2]), parseInt(dataInicioParts[1]) - 1, parseInt(dataInicioParts[0]))
                    const dataFim = new Date(parseInt(dataFimParts[2]), parseInt(dataFimParts[1]) - 1, parseInt(dataFimParts[0]))

                    if (dataInicio > dataFim) {
                        mostraErro('', 'A data de início do treinamento não pode ser maior que a data de fim do treinamento')
                        return false
                    }
                }
            }

            if (!this.form.treinamento_termo_ciencia || this.form.treinamento_termo_ciencia.length === 0) {
                mostraErro('', 'O Termo de Ciência de Treinamento é obrigatório quando há treinamento na função')
                return false
            }

            if (!this.form.treinamento_certificado || this.form.treinamento_certificado.length === 0) {
                mostraErro('', 'O Certificado de Treinamento é obrigatório quando há treinamento na função')
                return false
            }

            return true
        },
        cadastrar() {
            if (this.form.colaborador_id === '') {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                this.resetaCampoColaborador()
                return false
            }

            if (this.form.gestor_id === '') {
                valida_campo_vazio($(`#gestor_${this.hash_gestor}`), 1)
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo GESTOR não pode ficar vazio')
                return false
            }

            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            if (this.form.mantem_centro_custo && this.form.mantem_cargo && this.form.mantem_salario && this.form.mantem_funcao) {
                mostraErro('', 'Nenhuma mudança foi solicitada')
                return false
            }

            // Validações de treinamento
            if (!this.validarTreinamento()) {
                return false
            }

            this.preload = true

            axios.post(`${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo`, this.form)
                .then(response => {
                    $(`#${this.hash} `).modal('hide')
                    let data = response.data
                    mostraSucesso('', 'Solicitação registrada com sucesso!')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch(error => {
                    this.preload = false
                })
        },

        formOpen(id) {
            Object.assign(this.form, this.formDefault)
            this.form.id = id
            this.cadastrando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false

            this.tituloJanela = `#${id}`

            formReset()
            this.preload = true

            axios.get(`${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/${id}/editar`)
                .then(response => {
                    let data = response.data
                    this.form.centro_custo_id = data.centro_custo_id
                    this.form.colaborador_id = data.colaborador_id
                    Object.assign(this.form, data)
                    this.listaCentroCusto()

                    this.tituloJanela = `#${id} Solicitação de Mudança de Cargo`

                    this.form.status_aprovacao_gestor = data.status_aprovacao_gestor === null ? '' : data.status_aprovacao_gestor
                    this.form.status_aprovacao_extra = data.status_aprovacao_extra === null ? '' : data.status_aprovacao_extra
                    this.form.status_aprovacao_rh = data.status_aprovacao_rh === null ? '' : data.status_aprovacao_rh
                    this.form.obs_gestor_aprovacao = data.obs_gestor_aprovacao
                    this.form.obs_aprovacao_extra = data.obs_aprovacao_extra
                    this.form.obs_rh = data.obs_rh

                    // Dados de aprovação extra
                    if (data.aprovacao_extra) {
                        this.form.aprovacao_extra_nome = data.aprovacao_extra.nome || ''
                    }
                    this.form.data_aprovacao_extra = data.data_aprovacao_extra || ''

                    this.preload = false
                })
                .catch(error => {
                    this.preload = false
                })
        },
        aprovarGestor() {
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            // Validações de treinamento
            if (!this.validarTreinamento()) {
                return false
            }

            this.preload = true

            axios.put(`${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/${this.form.id}/aprovarextra`, this.form)
                .then(response => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    $(`#${this.hash} `).modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch(error => {
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

            axios.put(`${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/${this.form.id}/aprovarrh`, this.form)
                .then(response => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    $(`#${this.hash} `).modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                })
                .catch(error => {
                    this.preload = false
                })
        },
        selecionarTodos(event) {
            if (event.target.checked) {
                this.selecionados = this.lista.map(item => item.id)
            } else {
                this.selecionados = []
            }
        },
        carregou(dados) {
            console.log('SolicitacaoMudaCargo - Dados recebidos:', {
                pode_aprovar_extra: dados.pode_aprovar_extra,
                tem_aprovacao_extra: dados.tem_aprovacao_extra,
                nome_aprovacao_extra: dados.nome_aprovacao_extra,
                aprovar_por_gestor: dados.aprovar_por_gestor,
                aprovar_por_rh: dados.aprovar_por_rh
            })

            this.lista = dados.itens
            this.aprovaGestor = dados.aprovar_por_gestor
            this.aprovaExtra = dados.pode_aprovar_extra || false
            this.aprovaRh = dados.aprovar_por_rh
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || 'Aprovação Extra'

            console.log('SolicitacaoMudaCargo - Variáveis setadas:', {
                aprovaExtra: this.aprovaExtra,
                temAprovacaoExtra: this.temAprovacaoExtra,
                nomeAprovacaoExtra: this.nomeAprovacaoExtra
            })

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
/* Container de Cards */
.cards-lista {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Checkbox Geral */
.checkbox-geral-container {
    background: #fff;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e9ecef;
}

.checkbox-geral-label {
    display: flex;
    align-items: center;
    margin: 0;
    cursor: pointer;
    font-weight: 500;
    color: #495057;
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

.data-info {
    display: flex;
    align-items: center;
    gap: 0.25rem;
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
</style>
