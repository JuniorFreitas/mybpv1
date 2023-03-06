<template>
    <div id="componenteRelatorioSintetico">

        <fieldset class="mt-3">
            <legend>Filtro</legend>
            <div class="row">

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="">Mes</label>
                        <select class="form-control form-control-sm"
                                :disabled="controle.carregando"
                                v-model="form.mes">
                            <option value="">Selecione</option>
                            <option v-for="item in meses" :value="item.value">{{ item.label }}</option>
                        </select>
                    </div>
                </div>


                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="">Ano</label>
                        <select class="form-control form-control-sm"
                                :disabled="controle.carregando"
                                v-model="form.ano">
                            <option v-for="item in anos" :value="item.label">{{ item.label }}</option>
                        </select>
                    </div>
                </div>

                <!--                <div class="col-12 col-md-3">-->
                <!--                    <div class="form-group">-->
                <!--                        <label for="">Exibir</label>-->
                <!--                        <select class="form-control form-control-sm" @change="atualizar()"-->
                <!--                                :disabled="controle.carregando"-->
                <!--                                v-model="controle.dados.pages">-->
                <!--                            <option v-for="item in por_pagina" :value="item">{{ item }}</option>-->
                <!--                        </select>-->
                <!--                    </div>-->
                <!--                </div>-->

                <div class="col-12"></div>

                <div class="col-12 col-md-9">

                    <button type="button" class="btn btn-sm btn-primary mr-1" @click.prevent="geraRealatorio()">
                        GERAR RELATORIO
                    </button>

                </div>
            </div>
        </fieldset>


    </div>
</template>

<script>
import modal from '../../Modal';
import DatePicker from "../../DatePicker";
import ExportacaoMixin from "../../../mixins/Exportacoes";

export default {
    name: 'RelatorioSintetico',
    mixins: [ExportacaoMixin],
    components: {
        modal
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),

            tituloJanela: "Demissão",
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

            selecionaTudo: false,

            form: {
                mes: '',
                ano: '',
                selecionados: [],
            },
            formDefault: null,

            urlPdf: `${URL_ADMIN}/controle-ponto/folha-ponto/relatorio-sintetico/exportacao`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: "",
                    campoStatus: "",
                    filtroPeriodo: false,
                    periodo: "",
                }
            }
        }
    },
    mounted() {
        this.form.ano = moment().year();
        let mes = moment().month();
        if (mes < 10) {
            mes = '0' + mes;
        }
        this.form.mes = mes;
    },
    computed: {
        meses() {
            return [
                {label: 'Janeiro', value: '01'},
                {label: 'Fevereiro', value: '02'},
                {label: 'Março', value: '03'},
                {label: 'Abril', value: '04'},
                {label: 'Maio', value: '05'},
                {label: 'Junho', value: '06'},
                {label: 'Julho', value: '07'},
                {label: 'Agosto', value: '08'},
                {label: 'Setembro', value: '09'},
                {label: 'Outubro', value: '10'},
                {label: 'Novembro', value: '11'},
                {label: 'Dezembro', value: '12'},
            ]
        },
        anos() {
            let anos = []
            let ano = moment().year()
            for (let i = 0; i < 2; i++) {
                anos.push({label: ano})
                ano--
            }
            return anos
        },
        paramsExport() {
            return this.form
        },
        listaCheck() {
            return this.lista.filter(item => item.id)
        },
        tudoMarcado() {
            let total = this.listaCheck.length
            let totalEncontrado = 0

            if (total === 0) {
                return false
            }

            this.listaCheck.forEach(item => {
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
            return [20, 50, 100, 150];
        }
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.listaCheck.map(item => {
                    let id = item.id
                    if (this.form.selecionados.indexOf(id) === -1) {
                        this.form.selecionados.push(id)
                    }
                })
            } else {
                this.listaCheck.map(item => {
                    let id = item.id
                    let index = this.form.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.form.selecionados.splice(index, 1)
                    }
                })
            }
        },

        geraRealatorio(){
            let url = `${URL_ADMIN}/controle-ponto/folha-ponto/relatorio-sintetico/exportacao?mes=${this.form.mes}&ano=${this.form.ano}`;
            window.open(url, '_blank');
        }


    }

}
</script>
