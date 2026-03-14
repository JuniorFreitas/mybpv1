<template>
    <div>
        <fieldset class="mt-2">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                <div class="col-12 col-md-6">
                    <div class="form-check" style="margin-bottom: -11px;">
                        <input type="checkbox" class="form-check-input" @change="buscarDados()"
                               :disabled="preload"
                               id="filtroVencimento"
                               v-model="controle.dados.filtroVencimento">
                        <label class="form-check-label cursor-pointer" for="filtroVencimento">Por período de
                            vencimento</label>
                    </div>
                    <div class="form-group">
                        <datepicker range formsm label="" @onselect="buscarDados()"
                                    :disabled="controle.carregando || !controle.dados.filtroVencimento"
                                    v-model="controle.dados.campoVencimento"></datepicker>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Pesquisar</label>
                        <input type="text"
                               placeholder="Buscar por colaborador"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="preload"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3" v-if="lista_ccs && AUTENTICADO.temFilial">
                    <div class="form-group">
                        <label for="">Por Cnpj</label>
                        <select class="form-control form-control-sm" @change="changeCnpj()"
                                :disabled="preload"
                                v-model="controle.dados.campoCnpj">
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
                                v-model="controle.dados.campoCentroCusto">
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

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Tipos de Exame</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoTipoExame"
                                :disabled="preload" @change="buscarDados()">
                            <option value="">Todos os tipos</option>
                            <option v-for="(item, index) in listaTiposExame" :value="item.id" :key="index">{{ item.label }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Vencido</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoVencido"
                                :disabled="preload" @change="buscarDados()">
                            <option value="">Indiferente</option>
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>
                </div>

                <div class="col-12"></div>
            </form>

            <div class="d-flex">
                <button type="button" class="btn btn-sm mr-1 btn-success mr-1" :disabled="preload"
                        @click="buscarDados()">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button type="button" class="btn btn-sm mr-1 btn-primary" :disabled="preload || !dados.length"
                        @click.prevent="gerarArquivoXls()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </div>
        </fieldset>
        <preload v-show="preload" class="text-center"></preload>
        <div v-if="!preload">
            <p class="py-3 mt-3">
                <i class="fas fa-circle text-danger ml-2"></i>
                Colaborador com menos de {{ periodo_vencimento_extenso }} para o vencimento.
            </p>

            <div class="alert alert-warning" v-show="!dados.length">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive">
                <table class="mt-4 table table-bordered" v-if="dados.length">
                    <thead>
                    <tr>
                        <th style="text-align: center; width: 45px ;">#</th>
                        <th style="text-align: center">Nome</th>
                        <th class="text-center text-nowrap"
                            v-if="AUTENTICADO.temFilial"
                        >CNPJ
                        </th>
                        <th class="text-center text-nowrap">Centro de Custo</th>
                        <th style="text-align: center">Cargo</th>
                        <th style="text-align: center">Data da Admissão</th>
                        <th style="text-align: center">Tipo de Exame</th>
                        <th style="text-align: center">Data do ASO</th>
                        <th style="text-align: center">Data de Vencimento</th>
                        <th style="text-align: center">Dias</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(vencimento, index) in dados" :key="vencimento.id || index"
                        :class="vencimento.dias_vencer <= periodo_vencimento_numero ? 'table-danger': ''">
                        <td style="text-align: center">{{ index + 1 }}</td>
                        <td style="text-align: center">{{ vencimento.colaborador }}</td>
                        <td class="text-center"
                            v-if="AUTENTICADO.temFilial"
                        >
                        <span v-if="vencimento.emp_cnpj">
                            {{ vencimento.emp_nome_fantasia }}<br>
                            ({{ vencimento.emp_tipo }})
                        </span>
                            <span v-else>---</span>
                        </td>
                        <td class="text-center">
                        <span v-if="vencimento.emp_centro_custo">
                             {{ vencimento.emp_centro_custo }}
                        </span>
                            <span v-else>---</span>
                        </td>
                        <td style="text-align: center">{{ vencimento.cargo }}</td>
                        <td style="text-align: center">{{ vencimento.data_admissao }}</td>
                        <td style="text-align: center">{{ vencimento.exame_tipo }}</td>
                        <td style="text-align: center">{{ vencimento.data_aso }}</td>
                        <td style="text-align: center">{{ vencimento.data_vencimento }}</td>
                        <td style="text-align: center">
                            {{ Math.abs(vencimento.dias_vencer) }}
                            <span class="p-1 badge badge-danger" v-if="vencimento.dias_vencer < 0">VENCIDO</span>
                            <span class="p-1 badge badge-info" v-else>A VENCER</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import configselect2 from "../../Select2/mixSelec2";
import ExportacaoMixin from "../../../mixins/Exportacoes";
import Utils from "../../../mixins/Utils";
import Validacoes from "../../../mixins/Validacoes";
import Configuracoes from "../../../mixins/Configuracoes.js";
import XLSX from "@e965/xlsx";

export default {
    mixins: [configselect2, ExportacaoMixin, Utils, Validacoes, Configuracoes],
    data() {
        return {
            AUTENTICADO,
            lista_ccs: null,

            hash: String(Math.random()).substr(2),

            preload: false,
            dados: [],
            listaTiposExame: [],
            periodo_vencimento_numero: null,
            periodo_vencimento_extenso: null,

            urlExportacao: `${URL_ADMIN}/relatorios/vencimentoasos/export-excel`,

            controle: {
                carregando: false,
                dados: {
                    campoBusca: "",
                    campoTipoExame: "",
                    campoVencido: "",
                    filtroVencimento: false,
                    campoVencimento: "",
                    campoCnpj: "",
                    campoCentroCusto: ""
                }
            }
        }
    },
    mounted() {
        this.buscarDados();
        this.tiposExames();
    },
    computed: {
        paramsExport() {
            return this.controle.dados;
        },
        filtroListaCentroCustoCnpj() {
            if (this.controle.dados.campoCnpj !== "" && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.controle.dados.campoCnpj];
            }
            if (!this.AUTENTICADO.temFilial && this.lista_ccs) {
                return this.lista_ccs.centros_custos[Object.keys(this.lista_ccs.centros_custos)[0]];
            }
            return [];
        }
    },
    methods: {
        async gerarArquivoXls() {
            const XLSX = require("@e965/xlsx");

            const dataHoraAtual = new Date().toLocaleString("en-US", {
                timeZone: "America/Sao_Paulo",
                hour12: false,
            }).replace(/\/|,|\s|:/g, "_")
                .replace(/\//g, "-");

            const filename = `relatorio_asos_${AUTENTICADO.empresa_id}_${AUTENTICADO.user_id}_${dataHoraAtual}.xlsx`;
            const jsonDataArray = this.dados;

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet([]);

            let cabecalho = [
                "Nome",
                "Cargo",
                "CNPJ da Empresa",
                "Empresa",
                "Centro de Custo",
                "Data da Admissão",
                "Tipo do Exame",
                "Data do Aso",
                "Vencimento ASO",
                "Dias",
                "Status"
            ];

            XLSX.utils.sheet_add_aoa(ws, [
                cabecalho
            ], {origin: 0});

            jsonDataArray.forEach(function (jsonData) {
                XLSX.utils.sheet_add_aoa(ws, [
                    [
                        jsonData.colaborador,
                        jsonData.cargo,
                        jsonData.emp_cnpj,
                        jsonData.emp_nome_fantasia,
                        jsonData.emp_centro_custo,
                        jsonData.data_admissao,
                        jsonData.exame_tipo,
                        jsonData.data_aso,
                        jsonData.data_vencimento,
                        jsonData.dias_vencer,
                        jsonData.dias_vencer < 0 ? "VENCIDO" : "A VENCER",
                    ]
                ], {origin: -1});
            });

            XLSX.utils.book_append_sheet(wb, ws, 'planilha');


            XLSX.writeFile(wb, filename);
        },

        async changeCnpj() {
            this.controle.dados.campoCentroCusto = "";
            await this.buscarDados();
        },

        async buscarDados() {
            this.preload = true;
            try {
                const res = await axios.post(`${URL_ADMIN}/relatorios/vencimentoasos`, this.controle.dados);
                this.dados = res.data.dados;
                this.periodo_vencimento_numero = res.data.periodo_vencimento_numero;
                this.periodo_vencimento_extenso = res.data.periodo_vencimento_extenso;
                this.lista_ccs = res.data.cc;
            } finally {
                this.preload = false;
            }
        },
        gerarPdf() {
            // let link = `${URL_ADMIN}/relatorios/controleusuarios/pdf/${this.form}`;
            // open(link, '_blank');
        },
        async tiposExames() {
            try {
                const response = await axios.get(`${URL_ADMIN}/relatorios/tipos-exames`);
                this.listaTiposExame = response.data;
            } catch (err) {
                this.listaTiposExame = [];
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
