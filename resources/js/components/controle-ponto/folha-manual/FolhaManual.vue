<template>
    <div id="componenteFolhamanual">
        <modal id="janelaGerarFolha" :fechar="!preload" size="g" :centralizada="true" titulo="Geração de Folha Manual" ref="modal_janelaGerarFolha">
            <template #conteudo>
                <preload v-if="preloadExportacao" msg="Gerando aguarde..."></preload>
                <div v-if="!preloadExportacao">
                    <div class="row mb-2">
                        <h5>
                            <strong>{{ form.selecionados.length }}</strong> colaborador(es) selecionado(s)
                        </h5>
                    </div>
                    <div class="row mb-3 py-2" style="border: 1px dashed #ccc">
                        <div class="col-12 col-md-6">
                            <label>Início do período</label>
                            <datepicker label="" class="corrigiDatepicker" formsm v-model="form.data_inicio"></datepicker>
                        </div>
                        <div class="col-12 col-md-6">
                            <label>Fim do período</label>
                            <datepicker label="" class="corrigiDatepicker" formsm v-model="form.data_fim"></datepicker>
                        </div>
                    </div>
                    <div class="row mb-3 py-2" v-for="(item, dia) in form.dias" :key="dia" style="border: 1px dashed #ccc" >
                        <div class="col-12 col-md-12">
                            <label>{{ item.label }}</label>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" :id="dia" v-model="item.repouso" />
                                <label class="form-check-label" :for="dia">Repouso</label>
                            </div>
                            <div class="row" v-if="!item.repouso">
                                <div class="col-3">
                                    <label for="">Entrada</label>
                                    <input type="time" class="form-control form-control-sm" v-model="item.entrada" />
                                </div>
                                <div class="col-3">
                                    <label for="">Intervalo</label>
                                    <input type="time" class="form-control form-control-sm" v-model="item.intervalo_almoco" />
                                </div>
                                <div class="col-3">
                                    <label for="">Fim de Intervalo</label>
                                    <input type="time" class="form-control form-control-sm" v-model="item.fim_intervalo_almoco" />
                                </div>
                                <div class="col-3">
                                    <label for="">Saída</label>
                                    <input type="time" class="form-control form-control-sm" v-model="item.saida" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #rodape>
                <div v-show="!preloadExportacao">
                    <button
                        type="button"
                        class="btn btn-sm mr-1 btn-primary"
                        @click="exportaPdf(); fecharJanela()"
                    >
                        <i class="fa fa-print"></i> Gerar
                    </button>
                </div>
            </template>
        </modal>

        <fieldset class="mt-3">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                <div class="col-12 col-md-4">
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

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label for="">Por Centro de Custo</label>
                        <select
                            class="form-control form-control-sm"
                            @change="atualizar()"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoCentroDeCusto"
                        >
                            <option value="">Todos...</option>
                            <option v-for="item in lista_centro_custos" :value="item.cc_id" :key="item.cc_id">
                                {{ item.centro_custo_label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label for="">Por Cargo</label>
                        <select class="form-control form-control-sm" @change="atualizar()" :disabled="controle.carregando" v-model="controle.dados.campoCargo">
                            <option value="">Todos...</option>
                            <option v-for="item in lista_cargos" :value="item.cargo" :key="item.cargo">
                                {{ item.cargo }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="">Status</label>
                        <select class="form-control form-control-sm" @change="atualizar()" v-model="controle.dados.campoStatus">
                            <option value="">Admitidos</option>
                            <option value="demitido">Demitidos</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar()" :disabled="controle.carregando" v-model="controle.dados.pages">
                            <option v-for="(item, index) in por_pagina" :value="item" :key="item.id || index">{{ item }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12"></div>

                <div class="col-12 col-md-9">
                    <button type="submit" class="btn btn-sm mr-1 btn-success mr-1" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button
                        class="btn btn-sm mr-1 btn-danger mr-1"
                        :style="form.selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="form.selecionados.length === 0 || controle.carregando"
                        @click="form.selecionados = []"
                    >
                        <i class="fa fa-times"></i> LIMPAR SELEÇÃO
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm mr-1 btn-primary mr-1"
                        :style="form.selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="form.selecionados.length === 0 || controle.carregando"
                     @click="$refs.modal_janelaGerarFolha && $refs.modal_janelaGerarFolha.abrirModal()">
                        GERAR FOLHA <span class="badge badge-light">{{ form.selecionados.length }}</span>
                    </button>
                </div>
            </form>
        </fieldset>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div class="mb-2 mt-2 pt-1 pb-1 border-bottom" v-show="!controle.carregando && lista.length === 0">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>
        </div>

        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr class="bg-white">
                        <th class="text-center" style="width: 30px">
                            <input type="checkbox" :checked="tudoMarcado" @click="selecionaTodos" style="cursor: pointer" />
                        </th>
                        <th>Nome</th>
                        <th>Cargo</th>
                        <th>Centro de Custo</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <template v-if="listaCheck.length">
                        <tr v-for="item in listaCheck" :key="item.id">
                            <td class="text-center">
                                <label :for="item.id">
                                    <input
                                        type="checkbox"
                                        v-model="form.selecionados"
                                        :value="item.id"
                                        :id="item.id"
                                        :style="item.id ? 'cursor:pointer' : 'cursor: not-allowed'"
                                    />
                                </label>
                            </td>
                            <td>{{ item.nome }}</td>
                            <td>{{ item.cargo }}</td>
                            <td>{{ item.centro_custo_label }}</td>
                        </tr>
                    </template>
                </tbody>
            </table>
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
import modal from '../../Modal'
import DatePicker from '../../DatePicker'
import ExportacaoMixin from '../../../mixins/Exportacoes'

export default {
    name: 'FolhaManual',
    mixins: [ExportacaoMixin],
    components: {
        DatePicker,
        modal
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),

            tituloJanela: 'Demissão',
            preload: false,
            preloadExportacao: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovando: false,
            aprovar_por_gestor: false,
            URL_ADMIN,

            lista: [],
            lista_centro_custos: [],
            lista_cargos: [],

            selecionaTudo: false,

            form: {
                selecionados: [],
                data_inicio: '',
                data_fim: '',
                dias: {
                    segunda: {
                        label: 'Segunda',
                        entrada: '07:30',
                        saida: '17:30',
                        intervalo_almoco: '12:00',
                        fim_intervalo_almoco: '13:00',
                        repouso: false
                    },
                    terca: {
                        label: 'Terça',
                        entrada: '07:30',
                        saida: '17:30',
                        intervalo_almoco: '12:00',
                        fim_intervalo_almoco: '13:00',
                        repouso: false
                    },
                    quarta: {
                        label: 'Quarta',
                        entrada: '07:30',
                        saida: '17:30',
                        intervalo_almoco: '12:00',
                        fim_intervalo_almoco: '13:00',
                        repouso: false
                    },
                    quinta: {
                        label: 'Quinta',
                        entrada: '07:30',
                        saida: '17:30',
                        intervalo_almoco: '12:00',
                        fim_intervalo_almoco: '13:00',
                        repouso: false
                    },
                    sexta: {
                        label: 'Sexta',
                        entrada: '07:30',
                        saida: '17:30',
                        intervalo_almoco: '12:00',
                        fim_intervalo_almoco: '13:00',
                        repouso: false
                    },
                    sabado: {
                        label: 'Sábado',
                        entrada: '07:30',
                        saida: '17:30',
                        intervalo_almoco: '12:00',
                        fim_intervalo_almoco: '13:00',
                        repouso: true
                    },
                    domingo: {
                        label: 'Domingo',
                        entrada: '07:30',
                        saida: '17:30',
                        intervalo_almoco: '12:00',
                        fim_intervalo_almoco: '13:00',
                        repouso: true
                    }
                }
            },
            formDefault: null,

            urlPaginacao: `${URL_ADMIN}/controle-ponto/folha-manual/atualizar`,
            urlPdf: `${URL_ADMIN}/controle-ponto/folha-manual/imprimir`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: '',
                    campoStatus: '',
                    campoCargo: '',
                    campoCentroDeCusto: '',
                    filtroPeriodo: false,
                    periodo: ''
                }
            }
        }
    },
    mounted() {
        this.atualizar()
        this.form.data_inicio = moment().startOf('month').add(20, 'days').format('DD/MM/YYYY')
        this.form.data_fim = moment(moment().startOf('month').add(20, 'days').format('YYYY-MM-DD')).add(30, 'days').format('DD/MM/YYYY')
        this.formDefault = _.cloneDeep(this.form)
    },
    computed: {
        paramsExport() {
            return this.form
        },
        listaCheck() {
            return this.lista.filter((item) => item.id)
        },
        tudoMarcado() {
            let total = this.listaCheck.length
            let totalEncontrado = 0

            if (total === 0) {
                return false
            }

            this.listaCheck.forEach((item) => {
                let id = item.id
                if (this.form.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                } else {
                    return false
                }
            })
            let resultado = total === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        },
        por_pagina() {
            return [20, 50, 100, 150]
        }
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.listaCheck.map((item) => {
                    let id = item.id
                    if (this.form.selecionados.indexOf(id) === -1) {
                        this.form.selecionados.push(id)
                    }
                })
            } else {
                this.listaCheck.map((item) => {
                    let id = item.id
                    let index = this.form.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.form.selecionados.splice(index, 1)
                    }
                })
            }
        },

        fecharJanela() {
            this.$refs.modal_janelaGerarFolha && this.$refs.modal_janelaGerarFolha.fecharModal()
        },

        carregou(dados) {
            this.lista = dados.itens
            this.lista_centro_custos = dados.lista_centro_custos
            this.lista_cargos = dados.lista_cargos
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
