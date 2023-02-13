<template>
    <div>
        <modal :id="hash" :titulo="tituloJanela" :size="90">
            <template slot="conteudo">
                <preload v-show="preload" class="text-center"></preload>
                <form v-if="!preload" :id="`${hash}`" onsubmit="return false;">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">

                            <colaborador :model="form" :verifica="visualizar" :hash="hash"></colaborador>

                            <div class="col-12 col-md-4" v-if="form.colaborador_id !== ''">
                                <div class="form-group">
                                    <label>Data de Admissão</label>
                                    <input type="text" class="form-control form-control-sm" v-model="dataAdmissao"
                                           readonly="readonly"
                                           disabled="disabled">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Centro de Custo</label>
                                    <select v-model="form.centro_custo_id" class="form-control form-control-sm"
                                            :disabled="visualizar || aprovandoRh"
                                            onchange="valida_campo_vazio(this,1)"
                                            onblur="valida_campo_vazio(this,1)">
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>

                                    <!--                                    <select2 :settings="settings2" :options="centro_custos" :disabled="controle.carregando"-->
                                    <!--                                             v-model="form.centro_custo_id"></select2>-->
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Período Aquisitivo</label>
                                    <select v-model="form.periodo_aquisitivo_id" class="form-control form-control-sm"
                                            :disabled="visualizar || aprovandoRh">
                                        <option value="">Selecione</option>
                                        <option v-for="periodo in periodos" :value="periodo.id">{{ periodo.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="ultimaData !== ''">
                                <div class="form-group">
                                    <label>Última Data</label>
                                    <input type="text" class="form-control form-control-sm" v-model="ultimaData"
                                           readonly="readonly"
                                           disabled="disabled">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Tem Falta?</label>
                                <select type="text" class="form-control form-control-sm" v-model="form.tem_faltas"
                                        :disabled="visualizar || aprovandoRh"
                                        @change.prevent="verificaFaltas()">
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4" v-if="form.tem_faltas === true">
                                <label>Quantidade de faltas</label>
                                <select class="form-control form-control-sm" v-model="form.qnt_faltas"
                                        :disabled="visualizar || aprovandoRh"
                                        @change.prevent="form.qnt_dias=5">
                                    <option v-for="cont in 32" :value="cont" v-show="cont >= 1">{{ cont }}</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4" v-if="!aprovando">
                                <label>Quantidade de dias disponíveis</label>
                                <input type="text" class="form-control form-control-sm" v-model="qntDias"
                                       readonly="readonly"
                                       disabled="disabled">
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Dias de férias:</label>
                                <select class="form-control form-control-sm" v-model="form.qnt_dias"
                                        :disabled="visualizar || aprovandoRh">
                                    <option v-for="cont in qntDias" :value="cont" v-show="cont >= 5">
                                        {{ cont }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Data da saída</label>
                                <datepicker label="" formsm class="corrigiDatepicker" v-model="form.data_saida"
                                            :disabled="visualizar || aprovandoRh"></datepicker>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Data do retorno</label>
                                <input type="text" class="form-control form-control-sm" v-model="dataRetorno"
                                       readonly="readonly"
                                       disabled="disabled">
                            </div>

                            <div class="col-12 col-md-4 mb-3" v-if="!aprovando">
                                <label>Dias de saldo</label>
                                <input type="text" class="form-control form-control-sm" v-model="qntSaldo"
                                       readonly="readonly"
                                       disabled="disabled">
                            </div>

                            <gestoraprovacao formsm :model="form" :verifica="visualizar" :hash="hash"></gestoraprovacao>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control form-control-sm" v-model="form.obs_gestor" cols="5" rows="5"
                                              :disabled="visualizar || aprovandoRh"></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 mt-4 mb-4" v-if="visualizar">
                                <legend>Solicitação feita por: {{ form.solicitante !== null ? form.solicitante.nome : '' }} {{ form.data_solicitacao }}
                                </legend>
                            </div>
                        </div>

                        <div class="alert alert-warning" v-if="aprovando">
                            Esta solicitação ainda não foi aprovada ou reprovada!
                        </div>

                        <fieldset v-if="visualizar || aprovando || aprovandoRh">
                            <legend>Aprovação Gestor</legend>
                            <div class="row">

                                <div v-if="!aprovando && form.gestor_aprovacao !== null" class="col-12">
                                    <legend>{{ form.status_aprovacao_gestor }}
                                        por: {{ form.gestor_aprovacao.nome }} em
                                        {{ form.data_aprovacao_gestor }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea class="form-control form-control-sm" :disabled="!aprovando || aprovandoRh"
                                                  v-model="form.obs_rh"
                                                  cols="5" rows="5"></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select :disabled="!aprovando || aprovandoRh"
                                                v-model="form.status_aprovacao_rh"
                                                class="form-control form-control-sm validacampo"
                                                @change.prevent="valida_campo_vazio($event.target, 1)"  onblur="valida_campo_vazio(this, 1)">
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

                                <div v-if="!aprovandoRh && form.user_rh_id !== null" class="col-12">
                                    <legend>{{ form.status_aprovacao_rh }}
                                        por: {{ form.rh_aprovacao.nome }} em
                                        {{ form.data_aprovacao_rh }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea class="form-control form-control-sm" :disabled="visualizar && !aprovando && !aprovandoRh"
                                                  v-model="form.obs_rh"
                                                  cols="5" rows="5"></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select :disabled="visualizar && !aprovando && !aprovandoRh"
                                                v-model="form.status_aprovacao_rh"
                                                class="form-control form-control-sm validacampo"
                                                @change.prevent="valida_campo_vazio($event.target, 1)"  onblur="valida_campo_vazio(this, 1)">
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
                            <upload :model="form.anexos"
                                    :model-delete="form.anexosDel"
                                    :url="url_anexo"
                                    :tipos="mimes"
                                    label="Selecionar"
                                    :leitura="!podeanexar"
                                    @onProgresso="anexoUploadAndamento=true"
                                    @onFinalizado="anexoUploadAndamento=false"></upload>
                        </fieldset>


                    </fieldset>
                </form>
            </template>
            <template slot="rodape">
                <div v-show="cadastrando">
                    <button type="button" class="btn btn-sm btn-primary"
                            v-show="!preload"
                            @click.prevent="cadastrar">
                        <i class="fa fa-save"></i> Cadastrar
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovando && !preload" @click.prevent="aprovarGestor">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="aprovandoRh && !preload" @click.prevent="aprovarRh">
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

                <div class="col-12 col-md-4">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" @change="atualizar()"
                               :disabled="controle.carregando || controle.dados.filtroVencimento || controle.dados.filtroInicioFerias"
                               id="filtroIntervalo"
                               v-model="controle.dados.filtroPeriodo">
                        <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período
                            cadastrado</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label="" @onselect="atualizar()"
                                    :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                    v-model="controle.dados.periodo"></datepicker>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" @change="atualizar()"
                               :disabled="controle.carregando || controle.dados.filtroPeriodo || controle.dados.filtroInicioFerias"
                               id="filtroVencimento"
                               v-model="controle.dados.filtroVencimento">
                        <label class="form-check-label cursor-pointer" for="filtroVencimento">Por período de
                            vencimento</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label="" @onselect="atualizar()"
                                    :disabled="controle.carregando || !controle.dados.filtroVencimento"
                                    v-model="controle.dados.vencimento"></datepicker>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" @change="atualizar()"
                               :disabled="controle.carregando || controle.dados.filtroVencimento || controle.dados.filtroPeriodo"
                               id="filtroInicioFerias"
                               v-model="controle.dados.filtroInicioFerias">
                        <label class="form-check-label cursor-pointer" for="filtroInicioFerias">Por período de início
                            das férias</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label="" @onselect="atualizar()"
                                    :disabled="controle.carregando || !controle.dados.filtroInicioFerias"
                                    v-model="controle.dados.inicioFerias"></datepicker>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Pesquisar</label>
                        <input type="text"
                               placeholder="Buscar por colaborador"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoStatusAprovacao"
                                :disabled="controle.carregando" @change="atualizar()">
                            <option value="">Todos os Status</option>
                            <option value="aberto">Aguardando</option>
                            <option value="aprovado_gestor">Aprovado Gestor</option>
                            <option value="aprovado_rh">Aprovado Rh</option>
                            <option value="reprovado">Reprovado</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-2">
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
            </form>

            <div class="d-flex">
                <button type="button" class="btn btn-sm btn-success mr-1" :disabled="controle.carregando"
                        @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button type="button" class="btn btn-sm btn-primary mr-1" data-toggle="modal"
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
        </fieldset>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mb-2 mt-2 pt-1 pb-1 border-bottom" v-show="!controle.carregando && lista.length > 0">
                <span class="small text-right">
                    Legenda:
                    <i class="fas fa-circle text-warning ml-2"></i> Aguardando
                    <i class="fas fa-circle text-info ml-2"></i> Aprovado pelo Gestor
                    <i class="fas fa-circle text-success ml-2"></i> Aprovado pelo RH
                    <i class="fas fa-circle text-danger ml-2"></i> Reprovado
                </span>
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
                        <th>Centro de custo</th>
                        <th>Colaborador</th>
                        <th>Data Admissão</th>
                        <th>Qnt dias</th>
                        <th>Férias</th>
                        <th>Saldo</th>
                        <th>Limite</th>
                        <th>Período</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista"
                        :class="!item.status_aprovacao_gestor ? 'table-warning'
                        : item.status_aprovacao_gestor == 'reprovado' || item.status_aprovacao_rh == 'reprovado' ? 'table-danger'
                        : item.status_aprovacao_gestor == 'aprovado' && item.status_aprovacao_rh == null ? 'table-info'
                        : item.status_aprovacao_gestor == 'aprovado' && item.status_aprovacao_rh == 'aprovado' ? 'table-success'
                        : null">
                        <td class="text-center">
                            <label :for="item.id">
                                <input
                                    type="checkbox"
                                    v-model="selecionados"
                                    :value="item.id"
                                    :id="item.id"
                                    :style="!item.status_aprovacao_gestor ? 'cursor:pointer' : 'cursor: not-allowed'"
                                    :title="item.status_aprovacao_gestor ? null : 'Não possui aprovação'"
                                    v-if="!item.status_aprovacao_gestor"
                                >
                                <input type="checkbox" v-else disabled="disabled" title="Status já atualizado">

                            </label>
                        </td>
                        <td>
                            {{ item.id }}
                        </td>

                        <td>
                            {{ item.ferias_prevista ? item.ferias_prevista.centro_custo.label : '' }}
                        </td>

                        <td>
                            {{ item.admissao.feedback.curriculo.nome }}
                        </td>

                        <td>
                            {{ item.admissao.data_admissao }}
                        </td>

                        <td>
                            {{ item.qnt_dias }}
                        </td>

                        <td>
                            {{ item.data_saida }} até {{ item.data_retorno }}
                        </td>
                        <td>
                            {{ item.dias_saldo }}
                        </td>

                        <td>
                            {{ item.ultima_data }}
                        </td>

                        <td>
                            {{ item.periodo_aquisitivo.label }}
                        </td>

                        <td>
                        <span class="text-uppercase" v-if="item.gestor_aprovacao || item.rh_aprovacao">
                            <span
                                v-if="item.status_aprovacao_gestor == 'aprovado' && item.status_aprovacao_rh == null">
                                {{ item.status_aprovacao_gestor }} em {{ item.data_aprovacao_gestor }}<br/>
                                Por gestor(a): {{ item.gestor_aprovacao.nome }}
                            </span>
                            <span
                                v-if="item.status_aprovacao_rh == 'aprovado'">
                                {{ item.status_aprovacao_rh }} em {{ item.data_aprovacao_rh }}<br/>
                                Por RH: {{ item.rh_aprovacao.nome }}
                            </span>
                            <span v-if="item.status_aprovacao_gestor == 'reprovado' && item.status_aprovacao_rh == null">
                                {{ item.status_aprovacao_gestor }} em {{ item.data_aprovacao_gestor }}<br/>
                                Por gestor(a): {{ item.gestor_aprovacao.nome }}
                            </span>
                            <span v-if="item.status_aprovacao_rh == 'reprovado'">
                                {{ item.status_aprovacao_rh }} em {{ item.data_aprovacao_rh }}<br/>
                                Por RH: {{ item.rh_aprovacao.nome }}
                            </span>
                        </span>
                            <span v-else>
                            AGUARDANDO
                        </span>
                        </td>

                        <td class="text-center">

                            <div class="dropdown show">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                                   id="dropdownMenuLink"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="javascript://" title="Aprovação Gestor"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); visualizar = false; aprovando = true; aprovandoRh = false; podeanexar = true"
                                       v-if="item.gestor_aprovacao_id === null && aprovaGestor">
                                        Aprovação Gestor
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Aprovação RH"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); visualizar = true; aprovando = false; aprovandoRh = true; podeanexar = false"
                                       v-if="item.status_aprovacao_gestor === 'aprovado' && item.rh_aprovacao_id === null && aprovaRh">
                                        Aprovação Rh
                                    </a>

                                    <a class="dropdown-item" href="javascript://" title="Visualizar"
                                       data-toggle="modal"
                                       :data-target="`#${hash}`"
                                       @click.prevent="formOpen(item.id); visualizar = true; aprovando = false; aprovandoRh = false; podeanexar = false">
                                        Visualizar
                                    </a>
                                </div>
                            </div>
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
import colaborador from "../../Colaborador";
import gestoraprovacao from "../../GestorAprovacao";
import configselect2 from "../../../components/Select2/mixSelec2";
import Select2 from "../../../components/Select2/Select2";
import ExportacaoMixin from "../../../mixins/Exportacoes";
import Utils from "../../../mixins/Utils";
import Upload from "../../Upload";
import Validacoes from "../../../mixins/Validacoes";

export default {
    mixins: [configselect2, ExportacaoMixin, Utils, Validacoes],
    data() {
        return {
            tituloJanela: "Solicitacao de férias",
            preload: false,
            cadastrando: false,
            visualizar: false,
            aprovando: false,
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaRh: false,
            preloadExportacao: false,

            hash: `mastertag_${parseInt((Math.random() * 999999))}`,
            caminho_gestor: `autocomplete/todos-gestores-ativos`,
            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/export`,

            url_anexo: `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`,
            anexoUploadAndamento: false,
            podeanexar:false,
            mimes: [],

            selecionados: [],
            selecionaTudo: false,

            formConfirmacao: {
                selecionados: [],
                obs_aprovacao: "",
                status_aprovacao: ""
            },
            formConfirmacaoDefault: null,


            form: {
                id: "",
                colaborador_id: "",
                autocomplete_label_colaborador: "",
                autocomplete_label_colaborador_anterior: "",

                admissao_id: "",
                periodo_aquisitivo_id: "",
                data_saida: "",
                data_retorno: "",
                ultima_data: "",
                qnt_dias: 5,
                dias_saldo: "",
                tem_faltas: false,
                qnt_faltas: 0,
                solicitante: [],
                obs_solicitante: "",
                data_solicitacao: "",
                gestor_aprovacao: [],
                obs_gestor: "",
                status_aprovacao_gestor: "",
                data_aprovacao_gestor: "",
                data_aprovacao_rh: "",
                rh_aprovacao: [],
                obs_rh: "",
                status_aprovacao_rh: "",
                aprovado_via_script: false,
                autocomplete_label_gestor_modal: "",
                autocomplete_label_gestor_modal_anterior: "",
                centro_custo: [],
                anexos: [],
                anexosDel: []
            },

            formDefault: null,
            lista: [],
            periodos: [],
            ultimaData: "",
            periodo_label: "",
            centro_custos: [],

            /**
             *
             * aprovaRH -> apenas para mostrar o formulário
             * aprova_RH -> permissão
             *
             * **/

            // colaborador_ativo: `autocomplete/colaboradores/`,
            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    filtroPeriodo: false,
                    periodo: "",
                    campoBusca: "",
                    campoStatusAprovacao: "",
                    pages: 50,
                    filtroVencimento: false,
                    vencimento: "",
                    filtroInicioFerias: false,
                    inicioFerias: ""
                }
            }
        };
    },
    components: {
        colaborador,
        gestoraprovacao,
        Select2,
        Upload
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form); //copia
        this.formConfirmacaoDefault = _.cloneDeep(this.formConfirmacao); //copia
        this.atualizar();
        this.periodosAquisitivos();
    },
    computed: {
        naoAprovados() {
            return this.lista.filter(item => {
                if (item.status_aprovacao === null) {
                    return item.id;
                }
            });
        },
        tudoMarcado() {
            let totalAprovado = this.naoAprovados.length;
            let totalEncontrado = 0;

            if (totalAprovado === 0) {
                return false;
            }

            this.naoAprovados.forEach(item => {
                let id = item.id;
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++;
                } else {
                    return false;
                }
            });
            let resultado = totalAprovado === totalEncontrado;
            this.selecionaTudo = resultado;
            return resultado;
        },
        qntDias() {
            if (this.form.qnt_faltas <= 5) {
                return 30;
            }
            if (this.form.qnt_faltas >= 6 && this.form.qnt_faltas <= 14) {
                return 24;
            }
            if (this.form.qnt_faltas >= 15 && this.form.qnt_faltas <= 23) {
                return 18;
            }
            if (this.form.qnt_faltas >= 24 && this.form.qnt_faltas <= 32) {
                return 12;
            }
            if (this.form.qnt_faltas >= 33) {
                return 0;
            }
        },
        qntSaldo() {
            this.form.dias_saldo = this.qntDias - this.form.qnt_dias;

            return this.form.dias_saldo;
        },

        dataAdmissao() {
            if (this.form.id !== "") {
                axios.post(`${URL_ADMIN}/busca-data-admissao`, {
                    ferias_id: this.form.id,
                    visualizar: this.visualizar
                }).then(response => {

                    this.form.data_admissao = response.data.data_admissao;
                    this.ultimaData = response.data.ultimaData;

                    if (response.data.periodo.length > 1) {
                        this.periodos = response.data.periodo;
                    } else {
                        this.form.periodo_aquisitivo_id = response.data.periodo.id;
                        this.periodo_label = response.data.periodo.label;
                    }

                    if (response.data.ultimaData === "") {
                        let dataAtual = new Date();
                        let dia = dataAtual.getDate();
                        let mes = dataAtual.getMonth();
                        let ano = dataAtual.getFullYear();
                        let dataHoje = this.padTo2Digits(dia) + "/" + this.padTo2Digits((mes + 1)) + "/" + ano;
                        this.form.ultima_data = dataHoje;
                        this.form.data_saida = dataHoje;
                        this.form.data_retorno = dataHoje;
                    } else {
                        this.form.ultima_data = response.data.ultimaData;
                        this.form.data_saida = response.data.data_saida;
                        this.form.data_retorno = response.data.data_retorno;
                    }
                });
                return this.form.data_admissao;
            }
        },
        dataRetorno() {
            let dias_ferias = this.form.qnt_dias;
            let data_saida = this.form.data_saida.split("/");
            let data_saida_convert = data_saida[2] + "-" + data_saida[1] + "-" + data_saida[0];

            let data_retorno = new Date(data_saida_convert);
            data_retorno.setDate(data_retorno.getDate() + dias_ferias);
            let data_retorno_ptbr = this.padTo2Digits(data_retorno.getDate()) + "/" + this.padTo2Digits((data_retorno.getMonth() + 1)) + "/" + data_retorno.getFullYear();
            this.form.data_retorno = data_retorno_ptbr;

            return data_retorno_ptbr;
        },
        por_pagina() {
            return [20, 50, 100, 150];
        }
    },
    methods: {
        padTo2Digits(num) {
            return num.toString().padStart(2, "0");
        },
        verificaFaltas() {
            this.form.qnt_faltas = 1;
            if (!this.form.tem_faltas) {
                this.form.qnt_faltas = 0;
            }
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.naoAprovados.map(item => {
                    let id = item.id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id);
                    }
                });
            } else {
                this.naoAprovados.map(item => {
                    let id = item.id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1);
                    }
                });
            }
        },
        confirmaAtualizacaoStatus(confirmacao) {

            this.preloadAtualizacao = true;
            this.formConfirmacao.status_aprovacao = confirmacao;
            this.formConfirmacao.selecionados.push(this.selecionados);

            axios.post(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/atualizacao-status`, this.formConfirmacao)
                .then(res => {
                    this.preloadAtualizacao = false;
                    $("#janelaAtualizaStatus").modal("hide");
                    mostraSucesso("Status das Férias atualizado com sucesso!");
                    this.selecionados = [];
                    this.formConfirmacao = _.cloneDeep(this.formConfirmacaoDefault); //copia
                    this.$refs.componente.buscar();
                })
                .catch(error => {
                    this.preloadAtualizacao = false;
                });
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

        periodosAquisitivos() {
            axios.get(`${URL_ADMIN}/periodos-aquisitivos`).then(response => {
                this.periodos = response.data.periodos;
            });
        },

        formNovo() {
            this.cadastrando = true;
            this.aprovando = false;
            this.visualizar = false;
            this.podeanexar = true;
            this.tituloJanela = "Solicitação de férias";

            formReset();
            this.form = _.cloneDeep(this.formDefault); //copia
            this.form.centro_custo_id = "";
            this.listaCentroCusto();
        },

        cadastrar() {

            if (this.form.colaborador_id === "") {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1);
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger("blur");
                mostraErro("", "Campo COLABORADOR não pode ficar vazio");
                this.resetaCampoColaborador();
                return false;
            }
            if (this.form.gestor_id === "") {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1);
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger("blur");
                mostraErro("", "Campo GESTOR não pode ficar vazio");
                this.resetaCampoGestor();
                return false;
            }

            $(`#${this.hash} :input:visible`).trigger("blur");
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro("", "Verifique os campos marcados");
                return false;
            }

            this.preload = true;

            axios.post(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        $(`#${this.hash} `).modal("hide");
                        let data = response.data;
                        mostraSucesso("", "Solicitação registrada com sucesso!");
                        this.$refs.componente.buscar();
                        this.preload = false;
                    }
                })
                .catch(error => {
                    this.preload = false;
                });
        },

        formOpen(id) {
            Object.assign(this.form, this.formDefault);
            this.cadastrando = false;
            this.form.id = id;

            this.tituloJanela = `#${id}`;

            formReset();
            this.preload = true;
            this.form.data_admissao = "";

            axios.get(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    this.form.centro_custo_id = data.centro_custo_id;
                    this.form.colaborador_id = data.colaborador_id;
                    Object.assign(this.form, data);
                    this.listaCentroCusto();

                    this.tituloJanela = `#${id} Solicitação de férias`;

                    this.form.status_aprovacao = data.status_aprovacao === null ? "" : data.status_aprovacao;
                    this.form.resposta_rh = data.resposta_rh === null ? "" : data.resposta_rh;
                    this.form.obs_aprovacao = data.status_aprovacao === null ? "" : data.obs_aprovacao;
                    this.periodo_label = data.periodo_aquistivo.label;

                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                });
        },

        aprovarGestor() {

            $(`#${this.hash} :input:visible`).trigger("blur");
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro("", "Verifique os campos marcados");
                return false;
            }

            this.preload = true;

            axios.put(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}/aprovargestor`, this.form)
                .then(response => {
                    let data = response.data;
                    mostraSucesso("", "Registro salvo com sucesso!");
                    $(`#${this.hash} `).modal("hide");
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                });
        },
        aprovarRh() {

            $(`#${this.hash} :input:visible`).trigger("blur");
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro("", "Verifique os campos marcados");
                return false;
            }
            this.preload = true;

            axios.put(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}/aprovarrh`, this.form)
                .then(response => {
                    let data = response.data;
                    mostraSucesso("", "Registro salvo com sucesso!");
                    $(`#${this.hash} `).modal("hide");
                    this.$refs.componente.buscar();
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                });
        },


        carregou(dados) {
            this.lista = dados.itens;
            this.periodos = dados.periodo;
            this.aprovaGestor = dados.aprovar_por_gestor;
            this.aprovaRh = dados.aprovar_por_rh;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        }
    }
};
</script>

<style scoped>

</style>
