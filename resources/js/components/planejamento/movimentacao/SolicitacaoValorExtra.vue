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
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Colaborador </label>
                                    <autocomplete
                                        :caminho="`autocomplete/colaboradores`"
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
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Centro de Custo</label>
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
                                    <label>CNPJ Atual</label>
                                    <select v-model="form.filial" class="form-control form-control-sm"
                                            @change.p.prevent="changeCnpj()" disabled>
                                        <option :value="false">Matriz</option>
                                        <option :value="true">Filial</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="temFilial && form.filial">
                                <div class="form-group">
                                    <label>Filial</label>
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
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.tipo"
                                        onblur="valida_campo_vazio(this,1)"
                                        :disabled="visualizar"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Período em dias</label>
                                <input
                                    type="number"
                                    class="form-control form-control-sm"
                                    v-model="form.periodo_dias"
                                    step=".5"
                                    onblur="valida_campo_vazio(this,1)"
                                    :disabled="visualizar"
                                />
                            </div>

                            <gestoraprovacao :model="form" :verifica="visualizar" :hash="hash"></gestoraprovacao>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control form-control-sm" v-model="form.obs" cols="5" rows="5"
                                              :disabled="visualizar"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
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
                        </div>
                        <div class="alert alert-warning" v-if="!form.data_aprovacao && !cadastrando">Esta solicitação
                            ainda não foi aprovada ou reprovada!
                        </div>

                        <fieldset v-if="visualizar || aprovando || aprovandoRh">
                            <legend>Aprovação Gestor</legend>
                            <div class="row">
                                <div v-if="!aprovando && form.user_aprovacao" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_gestor }} por: {{ form.user_aprovacao.nome }} em
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

                        <div class="alert alert-warning" v-if="aprovandoRh">Esta solicitação ainda não foi aprovada ou
                            reprovada!
                        </div>

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

        <fieldset class="mt-0">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-check" style="margin-bottom: -11px">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            :disabled="controle.carregando"
                            :id="`filtroIntervalo_${hash}`"
                            v-model="controle.dados.filtroPeriodo"
                        />
                        <label class="form-check-label cursor-pointer" :for="`filtroIntervalo_${hash}`">Por
                            período</label>
                    </div>
                    <div class="form-group">
                        <datepicker
                            range
                            formsm
                            label=""
                            :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                            v-model="controle.dados.periodo"
                        ></datepicker>
                    </div>
                </div>

                <div class="col-12 col-md-3">
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
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar()"
                                :disabled="controle.carregando" v-model="controle.dados.pages">
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
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        data-toggle="modal"
                        :disabled="controle.carregando"
                        :data-target="`#${hash}`"
                        @click.prevent="formNovo"
                    >
                        Solicitar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
                    >
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                    </button>

                    <button
                        type="submit"
                        class="btn btn-sm btn-primary mr-1"
                        v-show="selecionados.length > 0"
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

        <div class="mb-2 mt-2 pt-1 pb-1 border-bottom bg-white" v-show="!controle.carregando && lista.length > 0">
            <span class="text-right ml-2">
                Legenda:
                <i class="fas fa-circle text-light ml-2"></i> Em aberto <i
                class="fas fa-circle text-warning ml-2"></i> Aprovado pelo Gestor
                <i class="fas fa-circle text-success ml-2"></i> Aprovado pelo RH <i
                class="fas fa-circle text-danger ml-2"></i> Reprovado
            </span>
        </div>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="table table-centered bg-white">
                    <thead>
                    <tr>
                        <!--                        <th class="text-center">-->
                        <!--                            <input type="checkbox"-->
                        <!--                                   :style="naoAprovados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"-->
                        <!--                                   :disabled="naoAprovados.length === 0" :checked="tudoMarcado"-->
                        <!--                                   @click="selecionaTodos">-->
                        <!--                        </th>-->
                        <th class="text-center">CÓD</th>
                        <th class="text-center">Colaborador</th>
                        <th class="text-center text-nowrap">Centro de custo</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center text-nowrap">Período em Dias</th>
                        <th class="text-center">Solicitante</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="item in lista" :key="item.id"
                    >
                        <!--                        <td class="text-center">-->
                        <!--                            <label :for="item.id">-->
                        <!--                                <input-->
                        <!--                                    type="checkbox"-->
                        <!--                                    v-model="selecionados"-->
                        <!--                                    :value="item.id"-->
                        <!--                                    :id="item.id"-->
                        <!--                                    :style="!item.status_aprovacao ? 'cursor:pointer' : 'cursor: not-allowed'"-->
                        <!--                                    :title="item.status_aprovacao ? null : 'Não possui aprovação'"-->
                        <!--                                    v-if="!item.status_aprovacao"-->
                        <!--                                >-->
                        <!--                                <input type="checkbox" v-else disabled="disabled" title="Status já atualizado">-->

                        <!--                            </label>-->
                        <!--                        </td>-->
                        <td class="text-center vertical-align-middle">
                            {{ item.id }}
                        </td>

                        <td class="text-center vertical-align-middle">
                            {{ item.colaborador.nome }}
                        </td>

                        <td class="text-center vertical-align-middle">
                            {{ item.centro_custo.label }}
                        </td>

                        <td class="text-center vertical-align-middle">
                            {{ item.tipo }}
                        </td>

                        <td class="text-center vertical-align-middle">
                            {{ item.periodo_dias }}
                        </td>

                        <td class="text-center vertical-align-middle">
                            {{ item.user_cadastrou.nome }} <br/>
                            {{ item.created_at }}
                        </td>

                        <td class="text-center font-weight-bold vertical-align-middle"
                            :class="{
                                'bg-danger text-white': item.status_aprovacao === 'reprovado' || item.status_aprovacao_rh === 'reprovado',
                                'bg-success':
                                    item.status_aprovacao_rh === 'aprovado' ||
                                    (item.status_aprovacao === 'aprovado' && item.status_aprovacao_rh === null && item.aprovado_via_script),
                                'bg-warning': item.status_aprovacao === 'aprovado' && item.status_aprovacao_rh === null && !item.aprovado_via_script,
                                'bg-light': !item.status_aprovacao
                            }">
                                <span class="text-uppercase" v-if="item.user_aprovacao || item.rh_aprovacao">
                                    <span
                                        v-if="item.status_aprovacao === 'aprovado' && item.status_aprovacao_rh === null">
                                        {{ item.status_aprovacao }} em {{ item.data_aprovacao }}<br/>
                                        Por gestor(a): {{ item.user_aprovacao.nome }}
                                    </span>
                                    <span v-if="item.status_aprovacao_rh === 'aprovado'">
                                        {{ item.status_aprovacao_rh }} em {{ item.data_aprovacao_rh }}<br/>
                                        Por RH: {{ item.rh_aprovacao.nome }}
                                    </span>
                                    <span
                                        v-if="item.status_aprovacao === 'reprovado' && item.status_aprovacao_rh === null">
                                        {{ item.status_aprovacao }} em {{ item.data_aprovacao }}<br/>
                                        Por gestor(a): {{ item.user_aprovacao.nome }}
                                    </span>
                                    <span v-if="item.status_aprovacao_rh === 'reprovado'">
                                        {{ item.status_aprovacao_rh }} em {{ item.data_aprovacao_rh }}<br/>
                                        Por RH: {{ item.rh_aprovacao.nome }}
                                    </span>
                                </span>
                            <span v-else> EM ABERTO </span>
                        </td>
                        <td class="text-center vertical-align-middle">
                            <div class="dropdown show">
                                <a
                                    class="btn btn-secondary dropdown-toggle"
                                    href="#"
                                    role="button"
                                    id="dropdownMenuLink"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação Gestor"
                                        data-toggle="modal"
                                        :data-target="`#${hash}`"
                                        @click.prevent="
                                                formOpen(item.id)
                                                cadastrando = false
                                                visualizar = false
                                                aprovando = true
                                                aprovandoRh = false
                                                podeanexar = false
                                            "
                                        v-if="item.user_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor"
                                    >
                                        Aprovação Gestor
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        data-toggle="modal"
                                        :data-target="`#${hash}`"
                                        @click.prevent="
                                                formOpen(item.id)
                                                cadastrando = false
                                                visualizar = true
                                                aprovando = false
                                                aprovandoRh = true
                                                podeanexar = false
                                            "
                                        v-if="
                                                item.status_aprovacao === 'aprovado' && !item.aprovado_via_script && item.rh_aprovacao_id === null && aprovaRh
                                            "
                                    >
                                        Aprovação Rh
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        data-toggle="modal"
                                        :data-target="`#${hash}`"
                                        @click.prevent="
                                                formOpen(item.id)
                                                cadastrando = false
                                                visualizar = true
                                                aprovando = false
                                                aprovandoRh = false
                                                podeanexar = false
                                            "
                                    >
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

export default {
    mixins: [ExportacaoMixin, Utils, configuracoes],
    components: {
        gestoraprovacao,
        Upload
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
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaRh: false,
            preloadExportacao: false,

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/export`,

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

            // colaborador_ativo: `autocomplete/colaboradores/`,
            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: '',
                    campoStatusAprovacao: '',
                    filtroPeriodo: false,
                    periodo: ''
                }
            }
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form) //copia

        this.formConfirmacaoDefault = _.cloneDeep(this.formConfirmacao)
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
            if (this.form.centro_custo_id === undefined || this.form.centro_custo_id === null || this.form.centro_custo_id === '') {
                return []
            }
            let centroSelecionado = _.find(this.centro_custos, {id: this.form.centro_custo_id})
            if (centroSelecionado.filiais.length) {
                return centroSelecionado.filiais
            }
            return []
        },

        centroCustoTemFilial() {
            return this.temFilial && this.centroCustoSelecionado.length > 0
        }
    },
    methods: {
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
        confirmaAtualizacaoStatus(confirmacao) {
            this.preloadAtualizacao = true
            this.formConfirmacao.status_aprovacao = confirmacao
            this.formConfirmacao.selecionados.push(this.selecionados)

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/atualizacao-status`, this.formConfirmacao)
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
        /***Campos de Filtros ****/
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
            this.controle.carregando = true
            setTimeout(() => {
                this.$refs.componente.buscar()
            }, 600)
        },
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
                this.$refs.componente.buscar()
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
            this.visualizar = false
            this.podeanexar = true
            this.aprovandoRh = false

            this.tituloJanela = 'Liderança de Pessoal e Valor Extra'

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
                .post(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista`, this.form)
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
            this.visualizar = false
            this.editando = false
            this.aprovando = false

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
                .put(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/${this.form.id}`, this.form)
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
                .put(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/${this.form.id}/aprovar`, this.form)
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
                .put(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/${this.form.id}/aprovarrh`, this.form)
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

        carregou(dados) {
            this.lista = dados.itens
            this.aprovar_por_gestor = dados.aprovar_por_gestor
            this.aprovaGestor = dados.aprovar_por_gestor
            this.aprovaRh = dados.aprovar_por_rh
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

<style scoped></style>
