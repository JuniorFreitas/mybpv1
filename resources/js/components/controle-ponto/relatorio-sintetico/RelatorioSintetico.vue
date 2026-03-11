<template>
    <div id="componenteRelatorioSintetico">
        <preload v-show="preload"></preload>
        <fieldset class="mt-3">
            <legend>Filtro</legend>
            <div class="row">
                <div class="col-12 col-md-5" v-if="temFilial">
                    <div class="form-group">
                        <label for="">CNPJ</label>
                        <select class="form-control form-control-sm" v-model="form.centro_custo_filial_id">
                            <option value="">MATRIZ</option>
                            <option v-for="item in lista" :key="item.id" :value="item.id">{{ item.razao_social }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label for="">Mês</label>
                        <select class="form-control form-control-sm" v-model="form.mes">
                            <option v-for="item in meses" :key="item.value" :value="item.value">{{ item.label }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label for="">Ano</label>
                        <select class="form-control form-control-sm" v-model="form.ano">
                            <option v-for="item in anos" :key="item.label" :value="item.label">{{ item.label }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm mr-1 btn-primary mr-1" @click.prevent="geraRealatorio()">GERAR RELATÓRIO</button>
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script>
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Configuracoes from '../../../mixins/Configuracoes'

export default {
    name: 'RelatorioSintetico',
    mixins: [ExportacaoMixin, Configuracoes],
    data() {
        return {
            hash: String(Math.random()).substr(2),
            URL_ADMIN,
            lista: [],
            preload: false,
            selecionaTudo: false,
            form: {
                mes: '',
                ano: '',
                centro_custo_filial_id: ''
            },
            formDefault: null,

            urlPdf: `${URL_ADMIN}/controle-ponto/folha-ponto/relatorio-sintetico/exportacao`
        }
    },
    mounted() {
        this.form.ano = moment().year()
        let mes = moment().month()
        if (mes < 10) {
            mes = '0' + mes
        }
        this.form.mes = mes
        this.getCentroCustoFilial()
    },
    computed: {
        meses() {
            return [
                { label: 'Janeiro', value: '01' },
                { label: 'Fevereiro', value: '02' },
                { label: 'Março', value: '03' },
                { label: 'Abril', value: '04' },
                { label: 'Maio', value: '05' },
                { label: 'Junho', value: '06' },
                { label: 'Julho', value: '07' },
                { label: 'Agosto', value: '08' },
                { label: 'Setembro', value: '09' },
                { label: 'Outubro', value: '10' },
                { label: 'Novembro', value: '11' },
                { label: 'Dezembro', value: '12' }
            ]
        },
        anos() {
            let anos = []
            let ano = moment().year()
            for (let i = 0; i < 2; i++) {
                anos.push({ label: ano })
                ano--
            }
            return anos
        },
        paramsExport() {
            return this.form
        }
    },
    methods: {
        geraRealatorio() {
            let url = `${URL_ADMIN}/controle-ponto/folha-ponto/relatorio-sintetico/exportacao?mes=${this.form.mes}&ano=${this.form.ano}&centro_custo_filial_id=${this.form.centro_custo_filial_id}`
            window.open(url, '_blank')
        },
        getCentroCustoFilial() {
            let url = `${URL_ADMIN}/get-filiais?ativo=1`
            this.preload = true
            axios
                .get(url)
                .then((response) => {
                    this.lista = response.data
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        }
    }
}
</script>
