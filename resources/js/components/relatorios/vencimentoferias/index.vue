<template>
    <div>
        <div>
            <fieldset>
                <legend>Filtro</legend>
                <form class="row">
<!--                    <div class="col-12 col-md-3 pb-3">-->
<!--                        <label for="">Escolha o período:</label>-->
<!--                        <select class="form-control form-control-sm" :disabled="preload" v-model="filtrar.periodo"-->
<!--                                @change="buscarDados()">-->
<!--                            <option v-for="(item, key) in filtro.periodo_aquisitivo" :value="item.id">{{ item.label }}-->
<!--                            </option>-->
<!--                        </select>-->
<!--                    </div>-->

                    <div class="clearfix"></div>

                    <div class="col-12">
                        <button class="btn btn-sm btn-primary" :disabled="preload" @click.prevent="buscarDados()"
                                type="button">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" :disabled="preload || !dados.length"
                                @click.prevent="exportaExcel()">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                    </div>
                </form>
            </fieldset>
            <preload v-if="preload"/>
            <template v-if="!preload">

                <div class="alert alert-warning" v-show="!dados.length">
                    <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
                </div>

                <div v-for="(item, index) in dados" :key="index" class="mb-3" v-show="dados.length">
                    <div class="row">
                        <div class="col-md-12">
                            <table :class="item.pintar" class="mt-4 table table-bordered">
                                <thead>
                                    <tr class="text-center">
                                        <th :rowspan="item.periodos.length + 3"
                                            style="display: table-cell; vertical-align: middle; text-align: center; /* Não necessário */">
                                            {{ index + 1 }}
                                        </th>
                                        <th colspan="6">{{ item.nome }}<br>(Admitido em: {{ item.data_admissao }}) - (Centro de Custo: {{ item.centro_custo }})
                                            <br><span v-if="item.dias_atraso >= 546" class="text-white">Férias atrasadas {{item.tempo_atrasado}}</span>
                                        </th>
                                    </tr>

                                    <tr :class="item.pintar" class="text-center">
                                        <th colspan="6">{{ item.cargo }}</th>
                                    </tr>

                                    <tr :class="item.pintar">
                                        <th style="text-align: center">Período Aquisitivo</th>
                                        <th style="text-align: center">QTD. dias</th>
                                        <th style="text-align: center">Última Atualização</th>
                                        <th style="text-align: center">Status</th>
                                    </tr>

                                    <tr v-for="(periodo, ind) in item.periodos">
                                        <th style="text-align: center">{{periodo.periodo_aquisitivo}}</th>
                                        <th style="text-align: center">{{periodo.avos ? periodo.avos.total_avos : 'Nada encontrado'}}</th>
                                        <th style="text-align: center">{{periodo.avos ? periodo.avos.ultima_atualizacao : 'Sem atualizacao'}}</th>
                                        <th style="text-align: center; text-transform: capitalize">{{periodo.status_ferias ? periodo.status_ferias : 'Aguardando'}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
<script>
import ExportacaoMixin from '../../../mixins/Exportacoes';

export default {
    mixins: [ExportacaoMixin],
    data() {
        return {
            preload: false,
            dados: [],
            filtro: [],
            periodo: "",
            urlExportacao: `${URL_ADMIN}/relatorios/vencimento-ferias/export-excel`,
            filtrar: {
                periodo: '',
                status_ferias: ''
            }
        };
    },
    async mounted() {
        await this.periodosAquisitivosList();

        // this.filtrar.periodo = this.filtro.periodo_aquisitivo[1].id
        await this.buscarDados();
    },
    filters:{
        letterCase(value) {
            return value.charAt(0).toUpperCase() + value.slice(1);
        }
    },
    computed: {
        paramsExport() {
            return this.filtrar;
        }
    },
    methods: {
        async periodosAquisitivosList() {
            await axios.post(`${URL_ADMIN}/relatorios/ferias/listaperiodos`)
                .then(({data}) => {
                    this.filtro = data.filtro;
                });
        },
        async buscarDados() {
            this.preload = true;
            await axios.post(`${URL_ADMIN}/relatorios/vencimento-ferias`, this.filtrar)
                .then(({data}) => {
                    this.dados = data;
                    this.preload = false;
                });
        }
    }

};
</script>
