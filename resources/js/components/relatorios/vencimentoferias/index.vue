<template>
    <div>
        <div>
            <fieldset>
                <legend>Ações</legend>
                <form class="row" @submit.prevent="buscarDados()">
                    <!--                    <div class="col-12 col-md-3 pb-3">-->
                    <!--                        <label for="">Escolha o período:</label>-->
                    <!--                        <select class="form-control form-control-sm" :disabled="preload" v-model="filtrar.periodo"-->
                    <!--                                @change="buscarDados()">-->
                    <!--                            <option v-for="(item, key) in filtro.periodo_aquisitivo" :value="item.id">{{ item.label }}-->
                    <!--                            </option>-->
                    <!--                        </select>-->
                    <!--                    </div>-->
                    <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                        <label>Buscar</label>
                        <input type="text"
                               placeholder="Buscar por nome"
                               autocomplete="mastertag"
                               class="form-control form-control-sm" :disabled="preload"
                               v-model="filtrar.campoBusca">
                    </div>

                    <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                        <label>Cargo</label>
                        <select class="form-control form-control-sm" @change.prevent="buscarDados()" :disabled="preload"
                                v-model="filtrar.campoCargo">
                            <option value="">Todos</option>
                            <option v-for="item in lista_cargos" :value="item" :key="item" v-text="item"></option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                        <label>Situação</label>
                        <select class="form-control form-control-sm" @change.prevent="buscarDados()" :disabled="preload"
                                v-model="filtrar.campoSituacao">
                            <option value="">Todas</option>
                            <option value="Saldo insuficiente">Saldo insuficiente</option>
                            <option value="Solicitada">Solicitada</option>
                            <option value="Disponivel">Disponivel</option>
                            <option value="Gozada">Gozada</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                        <label>Periodo</label>
                        <select class="form-control form-control-sm" @change.prevent="buscarDados()" :disabled="preload"
                                v-model="filtrar.campoPeriodoVencido">
                            <option value="">Todos os periodos</option>
                            <option value="apartirdoperiodoconcessivel">Do período concessível à 1 ano e 6 meses
                            </option>
                            <option value="1anoseismesesate1anoe8meses">De 1 ano e 6 meses até 1 ano e 8 meses</option>
                            <option value="1anoe8meseisesuperior">Maior que 1 ano e 8 meses</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                        <label>Centro de Custo</label>
                        <select class="form-control form-control-sm" @change.prevent="buscarDados()" :disabled="preload"
                                v-model="filtrar.campoCentroCusto">
                            <option value="">Todos</option>
                            <option v-for="item in lista_centro_custos" :value="item" :key="item"
                                    v-text="item"></option>
                        </select>
                    </div>

                    <div class="clearfix"></div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-sm btn-primary" :disabled="preload" @click.prevent="buscarDados()"
                                type="button">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" :disabled="preload || !dados.length"
                                @click.prevent="gerarArquivoXls()">
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
                            <table class="mt-4 table bg-white table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th :rowspan="item.todos_periodos.length + 3" :class="item.pintar"
                                        style="display: table-cell; vertical-align: middle; text-align: center; /* Não necessário */">
                                        {{ index + 1 }}
                                    </th>
                                    <th colspan="6">{{ item.nome }} ({{ item.cargo }})
                                        <br>(Admitido em: {{ item.data_admissao }}) - (Centro
                                        de Custo: {{ item.centro_custo }})
                                        <br><span
                                            v-if="item.dias_atraso > 0">Férias vencidas {{
                                                item.tempo_atrasado
                                            }}</span>
                                    </th>
                                </tr>

                                <tr>
                                    <th style="text-align: center">Período Aquisitivo</th>
                                    <th style="text-align: center">Situação</th>
                                    <th style="text-align: center">Saldo</th>
                                    <th style="text-align: center">Última Atualização</th>
                                </tr>

                                <tr v-for="(periodo, ind) in item.todos_periodos">
                                    <th style="text-align: center; vertical-align: middle;">
                                        {{ periodo.periodo_aquisitivo }}
                                    </th>
                                    <th style="text-align: center; vertical-align: middle;">
                                        <div class="badge font-size-12 p-2 text-cappitalize" :class="periodo.colorir">
                                            {{ periodo.status_ferias }}
                                        </div>
                                        <span v-show="periodo.tempo_atrasado">
                                            <br>
                                                Vencida à {{ periodo.tempo_atrasado }}
                                            </span>
                                        <span v-if="periodo.tem_tb_ferias">
                                               <br> ({{ periodo.data_saida }} à {{ periodo.data_retorno }})
                                            </span>
                                    </th>
                                    <th style="text-align: center; vertical-align: middle;">
                                        {{ periodo.total_avos ? periodo.total_avos : 0 }}
                                    </th>
                                    <th style="text-align: center; vertical-align: middle;">
                                        {{
                                            periodo.ultima_atualizacao ? periodo.ultima_atualizacao : 'Sem atualização'
                                        }}
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
            lista_cargos: [],
            lista_funcao: [],
            lista_centro_custos: [],
            filtro: [],
            periodo: "",
            urlExportacao: `${URL_ADMIN}/relatorios/vencimento-ferias/export-excel`,
            filtrar: {
                periodo: '',
                status_ferias: '',
                campoBusca: '',
                campoCargo: '',
                campoSituacao: '',
                campoCentroCusto: '',
                campoPeriodoVencido: ''
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
        async gerarArquivoXls() {
            const XLSX = require("xlsx");

            const dataHoraAtual = new Date().toLocaleString("en-US", {
                timeZone: "America/Sao_Paulo",
                hour12: false,
            }).replace(/\/|,|\s|:/g, "_")
                .replace(/\//g, "-");

            const filename = `relatorio_vencimento_ferias_${AUTENTICADO.empresa_id}_${AUTENTICADO.user_id}_${dataHoraAtual}.xlsx`;
            const jsonDataArray = this.dados;

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet([]);

            let cabecalho = [
                "Nome do colaborador",
                "Data de admissão",
                "Cargo",
                // "CNPJ da Empresa",
                // "Empresa",
                "Centro de Custo",
                "Período aquisitivo",
                "Quantidade de dias de atraso",
                "Tempo atrasado",
                "Situação",
                "Data saida",
                "Data retorno",
                "Saldo",
                "Última atualização",
            ];


            XLSX.utils.sheet_add_aoa(ws, [
                cabecalho
            ], {origin: 0});

            jsonDataArray.forEach(function (jsonData) {
                jsonData.todos_periodos.forEach(function (periodo) {
                    XLSX.utils.sheet_add_aoa(ws, [
                        [
                            jsonData.nome,
                            jsonData.data_admissao,
                            jsonData.cargo,
                            // jsonData.emp_cnpj,
                            // jsonData.emp_nome_fantasia,
                            // jsonData.emp_centro_custo,
                            jsonData.centro_custo,
                            periodo.periodo_aquisitivo,
                            periodo.dias_atraso,
                            periodo.dias_atraso > 0 ? periodo.tempo_atrasado : '',
                            periodo.status_ferias,
                            periodo.data_saida ? periodo.data_saida : '---',
                            periodo.data_retorno ? periodo.data_retorno : '---',
                            periodo.total_avos,
                            periodo.ultima_atualizacao,

                        ]
                    ], {origin: -1});
                });
            });

            XLSX.utils.book_append_sheet(wb, ws, 'planilha');


            XLSX.writeFile(wb, filename);
        },
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
                    this.dados = data.result;
                    this.lista_cargos = data.lista_cargos;
                    this.lista_funcao = data.lista_funcao;
                    this.lista_centro_custos = data.lista_centro_custos;
                    this.preload = false;
                });
        }
    }

};
</script>
