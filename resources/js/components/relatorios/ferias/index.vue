<template>
    <div>
        <div>
            <fieldset>
                <legend>Filtro</legend>
                <form class="row">
                    <div class="col-12 col-md-3">
                        <label for="">Filtrar por:</label>
                        <select class="form-control form-control-sm" :disabled="preload" v-model="filtrar.tipo"
                                @change="buscarDados()">
                            <option value="aquisitivo">Período aquisitivo</option>
                            <option value="data">Por data de saida</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3" v-if="filtrar.tipo === 'aquisitivo'">
                        <label for="">Escolha o período:</label>
                        <select class="form-control form-control-sm" :disabled="preload" v-model="filtrar.periodo"
                                @change="buscarDados()">
                            <option v-for="(item, key) in filtro.periodo_aquisitivo" :value="item.id">{{ item.label }}
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3" v-if="filtrar.tipo === 'data'">
                        <datepicker range formsm label="Escolha a data de saida:" :disabled="preload" @onselect="buscarDados()"
                                    v-model="filtrar.periodo_range"></datepicker>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <label for="">Por status:</label>
                        <select class="form-control form-control-sm" :disabled="preload" @change="buscarDados()"
                                v-model="filtrar.status_ferias">
                            <option value="">Todos</option>
                            <option v-for="item in filtro.status_ferias" :value="item">{{ item | letterCase }}</option>
                        </select>
                    </div>

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
                            <table class="mt-4 table table-bordered table-striped">
                                <thead>
                                <tr class="bg-white text-center">
                                    <th rowspan="4"
                                        style="display: table-cell; vertical-align: middle; text-align: center; /* Não necessário */">
                                        {{ index + 1 }}
                                    </th>
                                    <th colspan="6">{{ item.nome }}<br>(Admitido em: {{ item.data_admissao }})</th>
                                </tr>
                                <tr class="bg-white text-center">
                                    <th colspan="6">{{ item.cargo }}</th>
                                </tr>
                                <tr class="bg-white">
                                    <th style="text-align: center">Centro de custo</th>
                                    <th style="text-align: center">Qnt dias</th>
                                    <th style="text-align: center">Férias</th>
                                    <!--                                    <th style="text-align: center">Faltas</th>-->
                                    <!--                                    <th style="text-align: center">Saldo</th>-->
                                    <th style="text-align: center">Período Aquisitivo</th>
                                    <th style="text-align: center">Data Limite</th>
                                    <th style="text-align: center">Status</th>
                                </tr>
                                <tr
                                    :class="{
                                    'table-danger': item.pintar && item.status === 'aguardando',
                                    'table-success': item.status === 'gozando',
                                    }
                                    ">
                                    <th style="text-align: center">{{ item.centro_custo }}</th>
                                    <th style="text-align: center">{{ item.qnt_dias }}</th>
                                    <th style="text-align: center">{{ item.data_saida }} à {{ item.data_retorno }}</th>
                                    <!--                                    <th style="text-align: center">{{ item.qnt_faltas }}</th>-->
                                    <!--                                    <th style="text-align: center">{{ item.dias_saldo }}</th>-->
                                    <th style="text-align: center">{{ item.periodo_aquisitivo }}</th>
                                    <th style="text-align: center">{{ item.ultima_data }}</th>
                                    <th style="text-align: center">
                                        <span class="text-uppercase">{{ item.status }}</span>
                                        <!--                                        <span v-if="item.status === 'aguardando'">{{-->
                                        <!--                                                item.dias_vencer < 0 ? Math.abs(item.dias_vencer) + ' dia(s) vencido(s)' : item.dias_vencer + ' dia(s) à vencer'-->
                                        <!--                                            }}-->
                                        <!--                                        </span> -->
                                        <!--                                        <span v-if="item.status === 'aguardando'">-->
                                        <!--                                            Aguardando-->
                                        <!--                                        </span>-->
                                        <!--                                        <span v-if="item.status === 'gozando'">Gozando</span>-->
                                    </th>

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
            urlExportacao: `${URL_ADMIN}/relatorios/ferias/export-excel`,
            filtrar: {
                tipo: 'data',
                periodo: '',
                periodo_range: '',
                status_ferias: ''
            }
        };
    },
    async mounted() {
        let inicio_de_mes = moment().startOf("month").format("DD/MM/YYYY");
        let fim_de_mes = moment().add(1, "M").endOf("month").format("DD/MM/YYYY");
        this.filtrar.periodo_range = `${inicio_de_mes} até ${fim_de_mes}`;
        await this.periodosAquisitivosList();

        this.filtrar.periodo = this.filtro.periodo_aquisitivo[1].id
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
                    console.log(data)
                    this.filtro = data.filtro;
                });
        },
        async buscarDados() {
            this.preload = true;
            await axios.post(`${URL_ADMIN}/relatorios/ferias`, this.filtrar)
                .then(({data}) => {
                    this.dados = data.dados;
                    this.preload = false;
                });
        }
    }

};
</script>
