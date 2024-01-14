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
                                    :disabled="!dados.length"
                                    @click.prevent="gerarArquivoXls()">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 col-md-3" v-if="lista_ccs && AUTENTICADO.temFilial">
                        <div class="form-group">
                            <label for="">Por Cnpj</label>
                            <select class="form-control form-control-sm" @change="changeCnpj()"
                                    :disabled="preload"
                                    v-model="campoCnpj">
                                <option value="">Todos</option>
                                <option v-for="(item, key) in lista_ccs.cnpjs" :value="key" :key="key">
                                    {{ item.nome_fantasia }} - {{ item.cnpj }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4 col-md-3" v-if="lista_ccs">
                        <div class="form-group">
                            <label for="">Centro de Custo</label>
                            <select class="form-control form-control-sm" @change="buscarDados()"
                                    :disabled="preload"
                                    v-model="campoCentroCusto">
                                <option value="">Todos</option>
                                <option :title="item.label" v-for="(item, key) in filtroListaCentroCustoCnpj"
                                        :value="item.matriz ? item.id : item.filial_id"
                                        :key="key">
                                    {{ item.label }}
                                </option>
                                <option value="--naoinformado--">--- Não Informado ---</option>
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

                <div v-for="(item, index) in dados" :key="index" class="mb-3" v-if="dados.length">
                    <div class="row">
                        <div class="col-md-12">
                        </div>
                        <table class="mt-4 table bg-white table-bordered">
                            <thead>
                            <tr class="text-center">
                                <th :rowspan="item.treinamentos.length + 3"
                                    :class="item.pintar ? 'text-white bg-danger': ''"
                                    style="display: table-cell; vertical-align: middle; text-align: center;"
                                >
                                    {{ index + 1 }}
                                </th>
                                <th colspan="6">{{ item.nome }} ({{ item.cargo }})
                                    <br>(Centro de Custo: {{ item.emp_centro_custo }}) - {{ item.emp_nome_fantasia }}
                                </th>
                            </tr>
                            <tr>
                                <th style="text-align: center">Treinamento</th>
                                <th style="text-align: center">Data do treinamento:</th>
                                <th style="text-align: center">Data de vencimento</th>
                                <th style="text-align: center">Vence em</th>
                            </tr>
                            <tr v-for="(tr, ind) in item.treinamentos">
                                <th style="text-align: center; vertical-align: middle;">
                                    {{ tr.label }}
                                </th>
                                <th style="text-align: center; vertical-align: middle;">
                                    {{ tr.data_treinamento }}
                                </th>
                                <th style="text-align: center; vertical-align: middle;">
                                    {{ tr.data_vencimento }}
                                </th>
                                <th style="text-align: center; vertical-align: middle;"
                                    :class="tr.pintar ? 'text-white bg-danger': ''"
                                >
                                    {{ tr.dias_vencer }} dias
                                </th>
                            </tr>
                            </thead>
                        </table>
                        <!--                    <h5 class="text-center">{{ item.nome }}</h5>-->
                        <!--                    <h6 class="text-center">{{ item.cargo }}</h6>-->
                        <!--                    <div class="row">-->
                        <!--                        <div class="col-md-6" v-for="(tr, idx) in item.treinamentos" :key="idx">-->
                        <!--                            <ul class="list-group list-group-flush mb-3">-->
                        <!--                                <li class="list-group-item" :class="tr.dias_vencer <= 30 ? 'text-white bg-danger': ''">-->
                        <!--                                    <strong>Treinamento: </strong>{{ tr.label }} / ({{ item.tipo }})-->
                        <!--                                </li>-->
                        <!--                                <li class="list-group-item" :class="tr.dias_vencer <= 30 ? 'text-white bg-danger': ''">-->
                        <!--                                    <strong>Data do treinamento: </strong>{{ tr.data_treinamento }}-->
                        <!--                                </li>-->
                        <!--                                <li class="list-group-item" :class="tr.dias_vencer <= 30 ? 'text-white bg-danger': ''">-->
                        <!--                                    <strong>Data de vencimento: </strong>{{ tr.data_vencimento }}-->
                        <!--                                </li>-->
                        <!--                                <li class="list-group-item" :class="tr.dias_vencer <= 30 ? 'text-white bg-danger': ''">-->
                        <!--                                    <strong>Vence em {{ tr.dias_vencer }} dias</strong>-->
                        <!--                                </li>-->
                        <!--                            </ul>-->
                        <!--                        </div>-->
                        <!--                    </div>-->
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
<script>
import ExportacaoMixin from '../../../mixins/Exportacoes';

const XLSX = require("xlsx");
export default {
    mixins: [ExportacaoMixin],
    data() {
        return {
            AUTENTICADO,
            preload: true,
            dados: [],
            lista_ccs: null,
            periodo: "",
            campoCnpj: "",
            campoCentroCusto: "",

            urlExportacao: `${URL_ADMIN}/relatorios/vencimento-treinamento/export-excel`,


        };
    },
    async mounted() {
        let inicio_de_mes = moment().startOf("month").format("DD/MM/YYYY");
        let fim_de_mes = moment().add(1, 'M').endOf("month").format("DD/MM/YYYY");
        this.periodo = `${inicio_de_mes} até ${fim_de_mes}`;
        await this.buscarDados();
    },

    computed: {
        paramsExport() {
            return {
                periodo: this.periodo,
                campoCnpj: this.campoCnpj,
                campoCentroCusto: this.campoCentroCusto,
            }
        },
        filtroListaCentroCustoCnpj() {
            if (this.campoCnpj !== "" && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.campoCnpj];
            }
            if (!this.AUTENTICADO.temFilial && this.lista_ccs) {
                return this.lista_ccs.centros_custos[Object.keys(this.lista_ccs.centros_custos)[0]];
            }
            return [];
        }
    },
    methods: {
        async gerarArquivoXls() {
            const dataHoraAtual = new Date().toLocaleString("en-US", {
                timeZone: "America/Sao_Paulo",
                hour12: false,
            });

            const dataHoraFormatada = dataHoraAtual
                .replace(/\/|,|\s|:/g, "_")
                .replace(/\//g, "-");

            const jsonDataArray = this.dados;

            // Cria um objeto de workbook
            const wb = XLSX.utils.book_new();

            // Cria uma planilha de treinamentos
            const wsTreinamentos = XLSX.utils.json_to_sheet([]);

            let cabecalho = [
                "Nome",
                "Cargo",
                "CNPJ da Empresa",
                "Empresa",
                "Centro de Custo",
                "Tipo",
                "Treinamento",
                "Descrição",
                "Vencimento",
                "Treinamento",
                "Dias para Vencer",
                "Status"
            ];


            XLSX.utils.sheet_add_aoa(wsTreinamentos, [
                cabecalho
            ], {origin: 0});

            // Adiciona todas as linhas dos treinamentos à planilha
            jsonDataArray.forEach(function (jsonData) {
                jsonData.treinamentos.forEach(function (treinamento) {
                    XLSX.utils.sheet_add_aoa(wsTreinamentos, [
                        [
                            jsonData.nome,
                            jsonData.cargo,
                            jsonData.emp_cnpj,
                            jsonData.emp_nome_fantasia,
                            jsonData.emp_centro_custo,
                            jsonData.tipo,
                            treinamento.label,
                            treinamento.descricao,
                            treinamento.data_vencimento,
                            treinamento.data_treinamento,
                            treinamento.dias_vencer,
                            treinamento.dias_vencer < 0 ? 'Vencido' : 'A vencer'
                        ]
                    ], {origin: -1});
                });
            });

            // Adiciona a planilha de treinamentos ao workbook
            XLSX.utils.book_append_sheet(wb, wsTreinamentos, 'Treinamentos');

            // Salva o arquivo XLSX
            XLSX.writeFile(wb, `relatorio_treinamento_${AUTENTICADO.empresa_id}_${AUTENTICADO.user_id}__${dataHoraFormatada}.xlsx`);
        },

        async changeCnpj() {
            this.campoCentroCusto = "";
            await this.buscarDados();
        },
        async buscarDados() {
            this.preload = true;
            await axios.post(`${URL_ADMIN}/relatorios/vencimento-treinamento`, {
                periodo: this.periodo,
                campoCnpj: this.campoCnpj,
                campoCentroCusto: this.campoCentroCusto,
            }).then(res => {
                this.dados = res.data.itens;
                this.lista_ccs = res.data.cc;
                this.preload = false;
            });
        }
    }

};
</script>
