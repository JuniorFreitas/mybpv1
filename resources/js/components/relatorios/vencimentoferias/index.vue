<template>
    <div>
        <div>
            <fieldset>
                <legend>Ações</legend>
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
<!--                        <button class="btn btn-sm btn-primary" :disabled="preload" @click.prevent="buscarDados()"-->
<!--                                type="button">-->
<!--                            <i class="fa fa-search"></i> Buscar-->
<!--                        </button>-->
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
                                    <th :rowspan="item.todos_periodos.length + 3"
                                        style="display: table-cell; vertical-align: middle; text-align: center; /* Não necessário */">
                                        {{ index + 1 }}
                                    </th>
                                    <th colspan="6">{{ item.nome }}<br>(Admitido em: {{ item.data_admissao }}) - (Centro
                                        de Custo: {{ item.centro_custo }})
                                        <br><span
                                            v-if="item.dias_atraso > 0">Férias atrasadas {{ item.tempo_atrasado }}</span>
                                    </th>
                                </tr>

                                <tr :class="item.pintar" class="text-center">
                                    <th colspan="6">{{ item.cargo }}</th>
                                </tr>

                                <tr :class="item.pintar">
                                    <th style="text-align: center">Período Aquisitivo</th>
                                    <th style="text-align: center">Status</th>
                                    <th style="text-align: center">Qnt. Avos</th>
                                    <th style="text-align: center">Última Atualização</th>
                                </tr>

                                <tr v-for="(periodo, ind) in item.todos_periodos">
                                    <th style="text-align: center; vertical-align: middle;">
                                        {{ periodo.periodo_aquisitivo }}
                                    </th>
                                    <th style="text-align: center; vertical-align: middle; text-transform: capitalize">
                                        {{ periodo.status_ferias }}
                                        <span v-if="periodo.tem_tb_ferias">
                                               <br> ({{ periodo.data_saida }} à {{ periodo.data_retorno }})
                                            </span>
                                    </th>
                                    <th style="text-align: center; vertical-align: middle;">
                                        {{ periodo.total_avos ? periodo.total_avos : 0 }}
                                    </th>
                                    <th style="text-align: center; vertical-align: middle;">
                                        {{ periodo.ultima_atualizacao ? periodo.ultima_atualizacao : 'Sem atualização' }}
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
    filters: {
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
