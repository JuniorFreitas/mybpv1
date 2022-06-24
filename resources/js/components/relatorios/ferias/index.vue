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
                            <datepicker range formsm label=""
                                        v-model="periodo"></datepicker>
                            <button class="btn btn-sm btn-primary" @click.prevent="buscarDados()" type="button">
                                <i class="fa fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-sm btn-primary"
                                @click.prevent="exportaExcel()">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                        </div>
                    </div>
                </form>
            </fieldset>
            <preload v-if="preload" />
            <template v-if="!preload">

                <div class="alert alert-warning" v-show="!dados.length">
                    <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
                </div>

                <div v-for="(item, index) in dados" :key="index" class="mb-3" v-show="dados.length">
                    <h5 class="text-center">{{ item.nome }} - (Admitido em: {{ item.data_admissao }})</h5>
                    <h6 class="text-center">{{ item.cargo }}</h6>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="mt-4 table table-bordered tabela">
                                <thead>
                                <tr>
                                    <th style="text-align: center">Centro de custo</th>
                                    <th style="text-align: center">Qnt dias</th>
                                    <th style="text-align: center">Férias</th>
                                    <th style="text-align: center">Faltas</th>
                                    <th style="text-align: center">Saldo</th>
                                    <th style="text-align: center">Período Aquisitivo</th>
                                    <th style="text-align: center">Data Limite</th>
                                    <th style="text-align: center">Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    :class="item.pintar ? 'table-danger': ''">
                                    <td style="text-align: center">{{ item.centro_custo }}</td>
                                    <td style="text-align: center">{{ item.qnt_dias }}</td>
                                    <td style="text-align: center">{{ item.data_saida }} à {{ item.data_retorno }}</td>
                                    <td style="text-align: center">{{ item.qnt_faltas }}</td>
                                    <td style="text-align: center">{{ item.dias_saldo }}</td>
                                    <td style="text-align: center">{{ item.periodo_aquisitivo }}</td>
                                    <td style="text-align: center">{{ item.ultima_data }}</td>
                                    <td style="text-align: center">{{ item.dias_vencer < 0 ?
                                        Math.abs(item.dias_vencer) + ' dia(s) vencido(s)' : item.dias_vencer + ' dia(s) à vencer'}}
                                    </td>

                                </tr>
                                </tbody>
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
            periodo: "",
            urlExportacao: `${URL_ADMIN}/relatorios/vencimento-ferias/export-excel`,
        };
    },
    mounted() {
        let inicio_de_mes = moment().startOf("month").format("DD/MM/YYYY");
        let fim_de_mes = moment().add(1, "M").endOf("month").format("DD/MM/YYYY");
        this.periodo = `${inicio_de_mes} até ${fim_de_mes}`;
        this.buscarDados();
    },
    computed: {
        paramsExport() {
            return {
                periodo: this.periodo
            }
        }
    },
    methods: {
        buscarDados() {
            this.preload = true;
            axios.post(`${URL_ADMIN}/relatorios/vencimento-ferias`, { periodo: this.periodo }).then(res => {
                this.dados = res.data;
                this.preload = false;
            });
        }
    }

};
</script>
