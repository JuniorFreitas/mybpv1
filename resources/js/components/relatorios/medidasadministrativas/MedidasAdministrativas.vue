<template>
    <div>
        <div>
            <fieldset>
                <legend>Filtro</legend>
                <form class="row">
                    <div class="col-12 col-md-3">
                        <div class="form-check" style="margin-bottom: -11px; margin-left: -15px;">
                            <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                        </div>
                        <div class="form-group">
                            <datepicker range formsm label="" :disabled="preload"
                                        v-model="periodo"></datepicker>
                            <button type="button" class="btn btn-sm mr-1 btn-primary" @click.prevent="buscarDados()"
                                    :disabled="preload">
                                <i class="fa fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-sm mr-1 btn-primary" :disabled="preload"
                                    @click.prevent="exportaExcel()">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                        <div class="form-group">
                            <label for="">Status</label>
                            <select class="form-control form-control-sm" @change="buscarDados()"
                                    v-model="status" :disabled="preload">
                                <option value="">Sem filtro</option>
                                <option value="admitidos">Admitidos</option>
                                <option value="demitidos">Demitidos</option>
                            </select>
                        </div>
                    </div>
                </form>
            </fieldset>
            <preload v-if="preload"/>
            <template v-if="!preload">
                <div class="alert alert-warning" v-show="!dados.length">
                    <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
                </div>
                <div v-for="(medidas, index) in dados" :key="index" v-show="dados.length" class="mb-3">
                    <h5 class="text-center">{{ medidas.nome }}</h5>
                    <h6 class="text-center">{{ medidas.cargo }}</h6>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Motivo: </strong>{{ medidas.motivo }}</li>
                        <li class="list-group-item"><strong>Causa: </strong>{{ medidas.causa }}</li>
                        <li class="list-group-item"><strong>Data da Solicitação: </strong>{{ medidas.data_solicitacao }}
                        </li>
                        <li class="list-group-item"><strong>Tipo: </strong>{{ medidas.tipo }}</li>
                        <li class="list-group-item"><strong>Solicitante: </strong>{{ medidas.solicitante }}</li>
                    </ul>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import preload from '../../preload.vue';
import ExportacaoMixin from '../../../mixins/Exportacoes';

export default {
    components: {preload},
    mixins: [ExportacaoMixin],
    data() {
        return {
            hash: String(Math.random()).substr(2),

            preload: false,
            dados: [],
            periodo: '',
            status: '',
            urlExportacao: `${URL_ADMIN}/relatorios/medidasadministrativas/export-excel`,
        }
    },
    mounted() {
        let inicio_de_mes = moment().startOf('month').format('DD/MM/YYYY');
        let fim_de_mes = moment().endOf('month').format('DD/MM/YYYY');
        this.periodo = `${inicio_de_mes} até ${fim_de_mes}`
        this.buscarDados();
    },
    computed: {
        paramsExport() {
            return {
                periodo: this.periodo,
                status: this.status
            }
        }
    },
    methods: {
        async buscarDados() {
            this.preload = true;
            try {
                const res = await axios.post(`${URL_ADMIN}/relatorios/medidasadministrativas`, {
                    periodo: this.periodo,
                    status: this.status
                });
                this.dados = res.data;
            } finally {
                this.preload = false;
            }
        },
    }

}
</script>

<style scoped>
.card {
    border: none;
    background: transparent;
}

ul.timeline {
    list-style-type: none;
    position: relative;
}

ul.timeline:before {
    content: ' ';
    background: #d4d9df;
    display: inline-block;
    position: absolute;
    left: 29px;
    width: 2px;
    height: 100%;
    z-index: 400;
}

ul.timeline > li {
    margin: 20px 0;
    padding-left: 20px;
}

ul.timeline > li:before {
    content: ' ';
    background: white;
    display: inline-block;
    position: absolute;
    border-radius: 50%;
    border: 3px solid #184056;
    left: 20px;
    width: 20px;
    height: 20px;
    z-index: 400;
}

.trackind {
    padding: .5rem .8rem;
    background-color: #f4f4f4;
    border-radius: .5rem;
}
</style>
