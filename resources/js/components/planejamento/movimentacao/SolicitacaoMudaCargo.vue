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
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Colaborador </label>
                                    <autocomplete :caminho="`autocomplete/colaboradores`"
                                                  :formsm="true"
                                                  :valido="form.colaborador_id !== ''"
                                                  v-model="form.autocomplete_label_colaborador"
                                                  placeholder="Selecione um(a) colaborador(a)"
                                                  :disabled="visualizar || editando"
                                                  :id="`colaborador_${hash}`"
                                                  @onblur="resetaCampoColaborador"
                                                  @onselect="selecionaColaborador"></autocomplete>
                                </div>
                            </div>
                        </div>
                        <div class="row" v-if="form.colaborador_id">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Centro de Custo Atual</label>
                                    <select v-model="form.anterior_centro_custo_id"
                                            class="form-control form-control-sm"
                                            disabled>
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
                                        disabled
                                        v-model="form.anterior_filial"
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeCnpj()"
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
                                        disabled
                                        v-model="form.anterior_centro_custo_filial_id"
                                        class="form-control"
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
                                    <autocomplete :disabled="true" :caminho="caminho_autocomplete_vagas"
                                                  :valido="form.autocomplete_label_vaga_anterior !== ''"
                                                  v-model="form.autocomplete_label_vaga_anterior"
                                                  placeholder="Vaga Atual"
                                                  @onblur="resetaCampo"
                                                  @onselect="selecionaVaga"></autocomplete>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Função Atual</label>
                                    <input type="text" class="form-control form-control-sm"
                                           onblur="valida_campo_vazio(this,2)"
                                           disabled
                                           v-model="form.anterior_funcao">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label>Salário Atual R$</label>
                                    <input type="text" class="form-control form-control-sm"
                                           v-mascara:dinheiro
                                           disabled
                                           v-model="form.anterior_salario">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label>Mantém Centro de Custo</label>
                                    <select class="form-control form-control-sm"
                                            @change.p.prevent="changeMantemCentroDeCusto()"
                                            v-model="form.mantem_centro_custo">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-3" v-if="!form.mantem_centro_custo">
                                <div class="form-group">
                                    <label>Novo Centro de Custo</label>
                                    <select v-model="form.novo_centro_custo_id"
                                            class="form-control form-control-sm"
                                            @change.prevent="changeCentroCusto()"
                                            onchange="valida_campo_vazio(this,1)"
                                            onblur="valida_campo_vazio(this,1)">
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-1" v-if="centroCustoTemFilialNovo && !form.mantem_centro_custo">
                                <div class="form-group">
                                    <label>Novo CNPJ</label>
                                    <select
                                        :disabled="visualizar"
                                        v-model="form.novo_filial"
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeCnpj()"
                                    >
                                        <option :value="false">Matriz</option>
                                        <option :value="true">Filial</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="temFilial && form.novo_filial && !form.mantem_centro_custo">
                                <div class="form-group">
                                    <label>Nova Filial</label>
                                    <select
                                        :disabled="visualizar"
                                        v-model="form.novo_centro_custo_filial_id"
                                        class="form-control form-control-sm"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centroCustoSelecionadoNovo" :value="item.id" :key="item.id">
                                            {{ item.filial.razao_social }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-2" v-if="form.anterior_funcao">
                                <div class="form-group">
                                    <label>Mantém Função</label>
                                    <select class="form-control form-control-sm"
                                            @change.p.prevent="changeMantemFuncao()"
                                            v-model="form.mantem_funcao">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-3" v-if="!form.mantem_funcao">
                                <div class="form-group">
                                    <label>Nova Função</label>
                                    <input type="text" class="form-control form-control-sm" onblur="valida_campo_vazio(this,2)"
                                           v-model="form.nova_funcao">
                                </div>
                            </div>
                            <div class="col-12 col-md-2" v-if="centroCustoTemFilialNovo">
                                <div class="form-group">
                                    <label>Mantém Cargo</label>
                                    <select class="form-control form-control-sm"
                                            @change.p.prevent="changeMantemCargo()"
                                            v-model="form.mantem_cargo">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-3" v-if="!form.mantem_cargo">
                                <div class="form-group">
                                    <label>Novo Cargo</label>
                                    <autocomplete :caminho="caminho_autocomplete_vagas"
                                                  :valido="form.autocomplete_label_vaga_nova !== ''"
                                                  v-model="form.autocomplete_label_vaga_nova"
                                                  placeholder="Novo Cargo"
                                                  @onselect="selecionaVagaNovo"></autocomplete>
                                </div>
                            </div>
                            <div class="col-12 col-md-2" v-if="form.colaborador_id">
                                <div class="form-group">
                                    <label>Mantém Salário</label>
                                    <select class="form-control form-control-sm"
                                            @change.p.prevent="changeMantemSalario()"
                                            v-model="form.mantem_salario">
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-2" v-if="!form.mantem_salario">
                                <div class="form-group">
                                    <label>Novo Salário R$</label>
                                    <input type="text" class="form-control form-control-sm"
                                           v-mascara:dinheiro
                                           v-model="form.novo_salario">
                                </div>
                            </div>
                            <gestoraprovacao formsm :model="form" :verifica="visualizar || aprovando" :hash="hash"></gestoraprovacao>

<!--                            <div class="col-12 col-sm-3" v-if="temFilial && form.anterior_filial">-->
<!--                                <div class="form-group">-->
<!--                                    <label>Filial</label>-->
<!--                                    <select-->
<!--                                        :disabled="visualizar || disabled"-->
<!--                                        v-model="form.centro_custo_filial_id"-->
<!--                                        class="form-control"-->
<!--                                    >-->
<!--                                        <option value="">Selecione</option>-->
<!--                                        <option v-for="item in centroCustoSelecionado" :value="item.id" :key="item.id">-->
<!--                                            {{ item.filial.razao_social }}-->
<!--                                        </option>-->
<!--                                    </select>-->
<!--                                </div>-->
<!--                            </div>-->
                        </div>
<!--                        <div class="row">-->
<!--                            <div class="col-12 col-md-6">-->
<!--                                <div class="form-group">-->
<!--                                    <label>Centro de Custo</label>-->
<!--                                    <select v-model="form.centro_custo_id" class="form-control form-control-sm"-->
<!--                                            :disabled="visualizar "-->
<!--                                            onchange="valida_campo_vazio(this,1)"-->
<!--                                            onblur="valida_campo_vazio(this,1)">-->
<!--                                        <option value="">Selecione</option>-->
<!--                                        <option v-for="item in centro_custos" :value="item.id">-->
<!--                                            {{ item.label }}-->
<!--                                        </option>-->
<!--                                    </select>-->
<!--                                </div>-->
<!--                            </div>-->

<!--                            <div class="col-12 col-md-6">-->
<!--                                <div class="form-group">-->
<!--                                    <label>Cargo Anterior</label>-->
<!--                                    <autocomplete :caminho="`autocomplete/cargosEmpresa`"-->
<!--                                                  :formsm="true"-->
<!--                                                  :valido="form.cargo_anterior_id !== ''"-->
<!--                                                  v-model="form.autocomplete_label_cargoanterior"-->
<!--                                                  placeholder="Selecione um cargo"-->
<!--                                                  :disabled="true"-->
<!--                                                  :id="`cargo_anterior_${hash}`"-->
<!--                                                  @onblur="resetaCampoCargoAnterior"-->
<!--                                                  @onselect="selecionaCargoAnterior"></autocomplete>-->
<!--                                </div>-->
<!--                            </div>-->

<!--                            <div class="col-12 col-md-3">-->
<!--                                <div class="form-group">-->
<!--                                    <label>Salário Anterior</label>-->
<!--                                    <input type="text" class="form-control form-control-sm" v-mascara:dinheiro-->
<!--                                           onblur="valida_dinheiro(this,1)"-->
<!--                                           :disabled="visualizar "-->
<!--                                           v-model="form.salario_anterior_format">-->
<!--                                </div>-->
<!--                            </div>-->

<!--                            <div class="col-12 col-md-6">-->
<!--                                <div class="form-group">-->
<!--                                    <label>Novo Cargo</label>-->
<!--                                    <autocomplete :caminho="`autocomplete/cargosEmpresa`"-->
<!--                                                  :formsm="true"-->
<!--                                                  :valido="form.novo_cargo_id !== ''"-->
<!--                                                  v-model="form.autocomplete_label_novo_cargo"-->
<!--                                                  placeholder="Selecione um cargo"-->
<!--                                                  :disabled="visualizar  || editando"-->
<!--                                                  :id="`novo_cargo_${hash}`"-->
<!--                                                  @onblur="resetaCampoNovoCargo"-->
<!--                                                  @onselect="selecionaNovoCargo"></autocomplete>-->
<!--                                </div>-->
<!--                            </div>-->


<!--                            <div class="col-12 col-md-3">-->
<!--                                <div class="form-group">-->
<!--                                    <label>Novo Salário</label>-->
<!--                                    <input type="text" class="form-control form-control-sm" v-mascara:dinheiro-->
<!--                                           onblur="valida_dinheiro(this,1)"-->
<!--                                           :disabled="visualizar "-->
<!--                                           v-model="form.novo_salario_format">-->
<!--                                </div>-->
<!--                            </div>-->

<!--                            <gestoraprovacao :model="form" :verifica="visualizar" :hash="hash"></gestoraprovacao>-->

                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control form-control-sm" v-model="form.obs_solicitante" cols="5" rows="5"
                                              :disabled="visualizar "></textarea>
                                </div>
                            </div>
<!--                        </div>-->

                        <fieldset>
                            <legend>Anexos</legend>
                            <upload :model="form.anexos"
                                    :model-delete="form.anexosDel"
                                    :url="url_anexo"
                                    :tipos="mimes"
                                    :leitura="!podeanexar"
                                    label="Selecionar"
                                    @onProgresso="anexoUploadAndamento=true"
                                    @onFinalizado="anexoUploadAndamento=false"></upload>
                        </fieldset>

<!--                        <div class="alert alert-warning" v-if="!form.data_aprovacao && !cadastrando">-->
<!--                            Esta solicitação ainda não foi aprovada ou reprovada!-->
<!--                        </div>-->

<!--                        <fieldset v-if="visualizar || editando">-->
<!--                            <legend>Aprovação</legend>-->
<!--                            <div class="row">-->
<!--                                <div class="col-12">-->
<!--                                    <div class="form-group">-->
<!--                                        <label>Observação</label>-->
<!--                                        <textarea class="form-control form-control-sm"-->
<!--                                                  :disabled="form.data_aprovacao || !aprovando"-->
<!--                                                  v-model="form.obs_aprovacao"-->
<!--                                                  cols="5" rows="5"></textarea>-->
<!--                                    </div>-->
<!--                                </div>-->

<!--                                <div class="col-md-6">-->
<!--                                    <div class="form-group">-->
<!--                                        <label>Status</label>-->
<!--                                        <select :disabled="form.data_aprovacao || !aprovando " v-if="editando"-->
<!--                                                v-model="form.status_aprovacao"-->
<!--                                                class="form-control form-control-sm">-->
<!--                                            <option value="">Selecione...</option>-->
<!--                                            <option value="aprovado">Aprovar</option>-->
<!--                                            <option value="reprovado">Reprovar</option>-->
<!--                                        </select>-->

<!--                                        <select :disabled="form.data_aprovacao || !aprovando " v-if="!editando"-->
<!--                                                v-model="form.status_aprovacao"-->
<!--                                                onblur="valida_campo_vazio(this,1)"-->
<!--                                                onchange="valida_campo_vazio(this,1)"-->
<!--                                                class="form-control form-control-sm">-->
<!--                                            <option value="">Selecione...</option>-->
<!--                                            <option value="aprovado">Aprovar</option>-->
<!--                                            <option value="reprovado">Reprovar</option>-->
<!--                                        </select>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </fieldset>-->
                    </fieldset>

                </form>
            </template>
            <template slot="rodape">
                <div v-show="!visualizar">
                    <button type="button" class="btn btn-sm btn-primary"
                            v-show="editando && !aprovando && !atualizado  && !preload"
                            @click.prevent="alterar">
                        <i class="fa fa-edit"></i> Alterar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary"
                            v-show="!editando && !cadastrado  && !preload"
                            @click.prevent="cadastrar">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovando && !atualizado  && !preload && !form.data_aprovacao"
                        @click.prevent="aprovar">
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
                        <textarea class="form-control form-control-sm"
                                  v-model="formConfirmacao.obs_aprovacao"
                                  cols="5" rows="5"></textarea>
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

        <fieldset class="mt-0">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
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
                </div>


                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Pesquisar</label>
                        <input type="text"
                               placeholder="Buscar por colaborador"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoStatus"
                                :disabled="controle.carregando" @change="atualizar()">
                            <option value="">Todos os Status</option>
                            <option value="aberto">Aberto</option>
                            <option value="aprovado">Aprovado</option>
                            <option value="reprovado">Reprovado</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar()"
                                :disabled="controle.carregando"
                                v-model="controle.dados.pages">
                            <option v-for="item in por_pagina" :value="item">{{ item }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12"></div>

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                            :disabled="controle.carregando"
                            :data-target="`#${hash}`"
                            @click.prevent="formNovo">
                        Solicitar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary  mr-1"
                            @click.prevent="exportaExcel()"
                            :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && !lista.length) ">
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                    </button>

                    <button type="submit" class="btn btn-sm btn-primary mr-1" v-show="selecionados.length > 0"
                            :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                            :disabled="selecionados.length === 0"
                            data-toggle="modal"
                            data-target="#janelaAtualizaStatus">
                        Atualizar Status <span class="badge badge-light">{{ selecionados.length }}</span>
                    </button>
                </div>
            </form>
        </fieldset>

        <div class="mb-2 mt-2 pt-1 pb-1 border-bottom" v-show="!controle.carregando && lista.length > 0">
        <span class="small text-right">
                Legenda:
                <i class="fas fa-circle text-warning"></i> Aguardando
                <i class="fas fa-circle text-success ml-2"></i> Aprovado
                <i class="fas fa-circle text-danger ml-2"></i> Reprovado
            </span>
        </div>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <th class="text-center">
                            <input type="checkbox"
                                   :style="naoAprovados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                                   :disabled="naoAprovados.length === 0" :checked="tudoMarcado"
                                   @click="selecionaTodos">
                        </th>
                        <th>CÓD</th>
                        <th>Solicitante</th>
                        <th>Centro de custo</th>
                        <th>Colaborador</th>
                        <th>Cargo Anterior</th>
                        <th>Salário Anterior</th>
                        <th>Cargo Anterior</th>
                        <th>Salário Anterior</th>
                        <th>Novo Cargo</th>
                        <th>Novo Salário</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista"
                        :class="!item.status_aprovacao ? 'table-warning' : item.status_aprovacao === 'reprovado' ? 'table-danger' : item.status_aprovacao === 'aprovado' ? 'table-success' : null">
                        <td class="text-center">
                            <label :for="item.id">
                                <input
                                    type="checkbox"
                                    v-model="selecionados"
                                    :value="item.id"
                                    :id="item.id"
                                    :style="!item.status_aprovacao ? 'cursor:pointer' : 'cursor: not-allowed'"
                                    :title="item.status_aprovacao ? null : 'Não possui aprovação'"
                                    v-if="!item.status_aprovacao"
                                >
                                <input type="checkbox" v-else disabled="disabled" title="Status já atualizado">

                            </label>
                        </td>
                        <td>
                            {{ item.id }}
                        </td>

                        <td>
                            {{ item.user_cadastrou.nome }} <br> {{ item.created_at }}
                        </td>

                        <td>
                            {{ item.centro_custo.label }}
                        </td>

                        <td>
                            {{ item.colaborador ? item.colaborador.nome : '' }}
                        </td>

                        <td>
                            {{ item.cargo_anterior ? item.cargo_anterior.nome : '' }}
                        </td>


                        <td>
                            {{ item.salario_anterior_format }}
                        </td>
                        <td>
                            {{ item.novo_cargo.nome }}
                        </td>

                        <td>
                            {{ item.novo_salario_format }}
                        </td>

                        <td>
                        <span v-if="item.status_aprovacao !== null">
                            <span class="text-uppercase">{{ item.status_aprovacao }}</span> em {{ item.data_aprovacao }}<br/>
                            Por: {{ item.user_aprovacao.nome }}
                        </span>

                            <span v-else>
                            Aguardando
                        </span>
                        </td>


                        <td class="text-center">
                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Aprovar"
                               v-if="!item.data_aprovacao && aprovar_por_gestor"
                               @click.prevent="formOpen(item.id); aprovando = true; editando = false; visualizar = false; podeanexar = true;"
                               data-toggle="modal"
                               :data-target="`#${hash}`">
                                <i class="fa fa-check"></i>
                            </a>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar"
                               v-if="!item.data_aprovacao"
                               @click.prevent="formOpen(item.id); editando = true; aprovando = false; visualizar = false; podeanexar = true"
                               data-toggle="modal"
                               :data-target="`#${hash}`">
                                <i class="fa fa-edit"></i>
                            </a>

                            <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Visualizar"
                               @click.prevent="formOpen(item.id); editando = false;  aprovando = false; visualizar = true; podeanexar= false"
                               data-toggle="modal"
                               :data-target="`#${hash}`">
                                <i class="fa fa-search-plus"></i>
                            </a>
                        </td>

                    </tr>
                    </tbody>
                </table>

            </div>

        </div>


        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            :url="urlPaginacao" :por-pagina="controle.dados.pages"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"/>
    </div>
</template>

<script>
import Upload from "../../Upload";
import colaborador from "../../Colaborador";
import gestoraprovacao from "../../GestorAprovacao";
import ExportacaoMixin from "../../../mixins/Exportacoes";
import Utils from "../../../mixins/Utils";
import configuracoes from "../../../mixins/Configuracoes";

export default {
    mixins: [ExportacaoMixin, Utils, configuracoes],

    components: {
        colaborador,
        gestoraprovacao,
        Upload
    },
    data() {
        return {
            tituloJanela: 'Solicitacao de Mudança de Cargo',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovando: false,
            aprovar_por_gestor: false,
            preloadExportacao: false,

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/export`,
            url_anexo: `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`,
            anexoUploadAndamento: false,
            podeanexar: false,
            mimes: [],
            caminho_autocomplete_vagas: `autocomplete/todas-vagas-ativas`,

            hash: `mastertag_${parseInt((Math.random() * 999999))}`,

            colunasTabela: {
                cliente: false,
            },

            selecionados: [],
            selecionaTudo: false,

            formConfirmacao: {
                selecionados: [],
                obs_aprovacao: '',
                status_aprovacao: '',
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
                anterior_filial: false,
                novo_centro_custo_id: "",
                novo_centro_custo_filial_id: '',
                novo_filial: false,
                tipo_contrato: '',

                mantem_cargo: true,
                anterior_vaga_aberta_id: '',
                autocomplete_label_vaga_anterior: '',
                nova_vaga_aberta_id: '',
                autocomplete_label_vaga_nova: '',

                mantem_funcao: true,
                anterior_funcao: '',
                nova_funcao: '',

                mantem_salario: true,
                anterior_salario: '0,00',
                novo_salario: '0,00',

                solicitante_id: '',
                autocomplete_label_solicitante: '',
                obs_solicitante: '',
                data_solicitacao: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: "",
                autocomplete_label_gestor_modal_anterior: "",
                gestor_aprovacao_id: '',
                autocomplete_label_gestor_aprovacao: '',
                obs_gestor_aprovacao: '',
                status_aprovacao_gestor: '',
                data_aprovacao_gestor: '',

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

            // colaborador_ativo: `autocomplete/colaboradores/`,
            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
                    campoBusca: '',
                    filtroPeriodo: false,
                    periodo: '',
                    campoStatus: '',
                },
            },
        }
    },
    mounted() {
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formConfirmacaoDefault = _.cloneDeep(this.formConfirmacao);
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
            return [20, 50, 100, 150];
        },
        centroCustoSelecionado() {
            if (this.form.anterior_centro_custo_id === undefined || this.form.anterior_centro_custo_id === null || this.form.anterior_centro_custo_id === '') {
                return [];
            }

            let centroSelecionado = _.find(this.centro_custos, {id: this.form.anterior_centro_custo_id});
            if (centroSelecionado.filiais.length) {
                return centroSelecionado.filiais;
            }

            return [];
        },
        centroCustoSelecionadoNovo() {
            if (this.form.novo_centro_custo_id === undefined || this.form.novo_centro_custo_id === null || this.form.novo_centro_custo_id === '') {
                return [];
            }

            let centroSelecionado = _.find(this.centro_custos, {id: this.form.novo_centro_custo_id});
            if (centroSelecionado.filiais.length) {
                return centroSelecionado.filiais;
            }

            return [];
        },
        centroCustoTemFilial() {
            return this.temFilial && this.centroCustoSelecionado.length > 0;
        },
        centroCustoTemFilialNovo() {
            return this.temFilial && this.centroCustoSelecionadoNovo.length > 0;
        },
    },
    methods: {
        changeCentroCusto() {
            this.form.novo_filial = false;
            this.form.novo_centro_custo_filial_id = ''
        },
        changeMantemCentroDeCusto() {
            this.form.novo_centro_custo_filial_id = '';
            this.form.novo_centro_custo_id = '';
            this.form.novo_filial = '';
        },
        changeMantemFuncao() {
            this.form.nova_funcao = '';
        },
        changeMantemCargo() {
            this.form.autocomplete_label_vaga_nova = '';
            this.form.nova_vaga_aberta_id = '';
        },
        changeMantemSalario() {
            this.form.novo_salario = '';
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
        confirmaAtualizacaoStatus(confirmacao) {

            this.preloadAtualizacao = true;
            this.formConfirmacao.status_aprovacao = confirmacao;
            this.formConfirmacao.selecionados.push(this.selecionados)

            axios.post(`${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/atualizacao-status`, this.formConfirmacao)
                .then(res => {
                    this.preloadAtualizacao = false;
                    $('#janelaAtualizaStatus').modal('hide');
                    mostraSucesso('Status atualizados com sucesso!');
                    this.selecionados = [];
                    this.formConfirmacao = _.cloneDeep(this.formConfirmacaoDefault) //copia
                    this.$refs.componente.buscar();
                })
                .catch(error => {
                    this.preloadAtualizacao = false;
                });
        },
        selecionaVaga(obj) {
            this.form.anterior_vaga_aberta_id = obj.id;
            this.form.autocomplete_label_vaga_anterior = obj.label;
        },
        selecionaVagaNovo(obj) {
            this.form.nova_vaga_aberta_id = obj.id;
            this.form.autocomplete_label_vaga_nova = obj.label;
        },
        selecionaColaborador(obj) {
            this.form.colaborador_id = obj.curriculo_id;
            this.form.autocomplete_label_colaborador = obj.label;
            this.form.autocomplete_label_colaborador_anterior = obj.label;

            this.form.anterior_centro_custo_id = obj.admissao.centro_custo_id;
            this.form.anterior_filial = obj.admissao.filial;
            this.form.anterior_centro_custo_filial_id = this.form.anterior_filial ? obj.admissao.centro_custo_filial_id : null;
            this.form.admissao_id = obj.admissao.id;
            this.form.anterior_funcao = obj.admissao.funcao;
            this.form.anterior_vaga_aberta_id = obj.vaga_aberta.vaga_id;
            this.form.autocomplete_label_vaga_anterior = obj.vaga_aberta.vaga.nome;
            this.form.anterior_salario = obj.admissao.salario;
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = '';
                this.form.autocomplete_label_colaborador = '';
                this.form.colaborador_id = '';

                this.form.anterior_centro_custo_id = '';
                this.form.anterior_filial = '';
                this.form.anterior_centro_custo_filial_id = '';
                this.form.admissao_id = '';
                this.form.anterior_funcao = '';
                this.form.anterior_vaga_aberta_id = '';
                this.form.autocomplete_label_vaga_anterior = '';
                this.form.anterior_salario = '';

                setTimeout(() => {
                    if (this.form.colaborador_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                        $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Colaborador não pode ficar vazio');
                    }
                }, 100);
            }
        },
        selecionaCargoAnterior(obj) {
            this.form.cargo_anterior_id = obj.id;
            this.form.autocomplete_label_cargo_anterior = obj.label;
            this.form.autocomplete_label_cargoanterior = obj.label;
        },
        resetaCampoCargoAnterior() {
            if (this.form.autocomplete_label_cargo_anterior !== this.form.autocomplete_label_cargoanterior) {
                this.form.autocomplete_label_cargo_anterior = '';
                this.form.autocomplete_label_cargoanterior = '';
                this.form.cargo_anterior_id = '';

                setTimeout(() => {
                    if (this.form.cargo_anterior_id === '') {
                        valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1);
                        $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Cargo Anterior não pode ficar vazio');
                    }
                }, 100);
            }
        },
        selecionaFilialCentroDeCusto(centro_custo_id, empresa_id) {
            axios.post(`${URL_ADMIN}/get-filiais/`, {
                centro_custo_id: centro_custo_id,
                empresa_id: empresa_id,
            }).then(res => {
                this.filiais_centro_de_custo = res.data.filiais_centro_de_custo;
                console.log(this.filiais_centro_de_custo)
            }).catch(error => {
                this.preload = false;
            });
        },
        selecionaNovoCargo(obj) {
            this.form.novo_cargo_id = obj.id;
            this.form.autocomplete_label_novo_cargo = obj.label;
            this.form.autocomplete_label_novo_cargo_anterior = obj.label;

            setTimeout(() => {
                if (this.form.novo_cargo_id !== '' && this.form.novo_cargo_id === this.form.cargo_anterior_id) {
                    valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1);
                    $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur');
                    mostraErro('Erro', 'O NOVO CARGO não pode ser igual ao CARGO ANTERIOR');
                    this.form.novo_cargo_id = '';
                    this.form.autocomplete_label_novo_cargo = '';
                    this.form.autocomplete_label_novo_cargo_anterior = '';
                }
            }, 100);

        },
        resetaCampoNovoCargo() {
            if (this.form.autocomplete_label_novo_cargo_anterior !== this.form.autocomplete_label_novo_cargo) {
                this.form.autocomplete_label_novo_cargo_anterior = '';
                this.form.autocomplete_label_novo_cargo = '';
                this.form.novo_cargo_id = '';

                setTimeout(() => {
                    if (this.form.novo_cargo_id === '') {
                        valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1);
                        $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Novo Cargo não pode ficar vazio');
                    }
                }, 100);
            }
        },

        listaCentroCusto() {
            axios.post(`${URL_PUBLICO}/centro-custos/`)
                .then(res => {
                    this.centro_custos = res.data.centro_custos;
                })
                .catch(error => {
                    this.preload = false;
                });
        },

        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.aprovando = false;
            this.visualizar = false;
            this.podeanexar = true;

            this.tituloJanela = "Solicitação de Mudança de Cargo";
            this.form = _.cloneDeep(this.formDefault) //copia
            formReset();
            setupCampo();
            this.listaCentroCusto();
        },

        cadastrar() {

            if (this.form.colaborador_id === '') {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo COLABORADOR não pode ficar vazio');
                this.resetaCampoColaborador();
                return false;
            }
            if (this.form.novo_cargo_id === '') {
                valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1);
                $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo NOVO CARGO não pode ficar vazio');
                this.resetaCampoNovoCargo();
                return false;
            }
            if (this.form.cargo_anterior_id === '') {
                valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1);
                $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo CARGO ANTERIOR não pode ficar vazio');
                this.resetaCampoCargoAnterior();
                return false;
            }
            if (this.form.gestor_id === '') {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1);
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo GESTOR não pode ficar vazio');
                this.resetaCampoGestor();
                return false;
            }

            $(`#${this.hash} :input:visible`).trigger('blur');
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;

            axios.post(`${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo`, this.form)
                .then(response => {
                    $(`#${this.hash} `).modal('hide');
                    let data = response.data;
                    mostraSucesso('', 'Solicitação registrada com sucesso!');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        formOpen(id) {
            Object.assign(this.form, this.formDefault);
            this.form.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.cadastrando = false;
            this.visualizar = false;
            this.editando = false;

            this.tituloJanela = `#${id}`;

            formReset();
            this.preload = true;

            axios.get(`${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);
                    this.listaCentroCusto();
                    this.form.centro_custo_id = data.centro_custo_id;

                    this.tituloJanela = `#${id} Solicitação de Mudança de Cargo`;
                    if (this.aprovando) {
                        this.form.status_aprovacao = data.status_aprovacao === null ? '' : data.status_aprovacao;
                        this.form.observacao = data.status_aprovacao === null ? '' : data.observacao;
                    }

                    this.editando = true;

                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        alterar() {

            if (this.form.colaborador_id === '') {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo COLABORADOR não pode ficar vazio');
                this.resetaCampoColaborador();
                return false;
            }

            if (this.form.novo_cargo_id === '') {
                valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1);
                $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo NOVO CARGO não pode ficar vazio');
                this.resetaCampoNovoCargo();
                return false;
            }
            if (this.form.cargo_anterior_id === '') {
                valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1);
                $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo CARGO ANTERIOR não pode ficar vazio');
                this.resetaCampoCargoAnterior();
                return false;
            }
            if (this.form.gestor_id === '') {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1);
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur');
                mostraErro('', 'Campo GESTOR não pode ficar vazio');
                this.resetaCampoGestor();
                return false;
            }

            $(`#${this.hash} :input:visible`).trigger('blur');
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;

            axios.put(`${URL_ADMIN}/planejamento/movimentacao/muda-cargo-prevista/${this.form.id}`, this.form)
                .then(response => {
                    $(`#${this.hash} `).modal('hide');
                    let data = response.data;
                    mostraSucesso('', 'Solicitação alterada com sucesso!');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        aprovar() {

            $(`#${this.hash} :input:visible`).trigger('blur');
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false;
            }

            this.preload = true;
            axios.put(`${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/${this.form.id}/aprovar`, this.form)
                .then(response => {
                    let data = response.data;
                    mostraSucesso('', 'Registro salvo com sucesso!');
                    $(`#${this.hash} `).modal('hide');
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },


        carregou(dados) {
            this.lista = dados.itens;
            this.aprovar_por_gestor = dados.aprovar_por_gestor;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        },
    }
}
</script>

<style scoped>

</style>
