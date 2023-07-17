<template>
    <div>
        <fieldset class="mt-2">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
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

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Tipos de Exame</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoTipoExame"
                                :disabled="preload" @change="buscarDados()">
                            <option value="">Todos os tipos</option>
                            <option v-for="item in listaTiposExame" :value="item.id">{{ item.label }}</option>
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
                <button type="button" class="btn btn-sm btn-success mr-1" :disabled="preload"
                        @click="buscarDados()">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button type="button" class="btn btn-sm btn-primary" :disabled="preload || !dados.length"
                        @click.prevent="exportaExcel()">
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

            <table class="mt-4 table table-bordered tabela" v-if="dados.length">
                <thead>
                <tr>
                    <th style="text-align: center; width: 45px ;">#</th>
                    <th style="text-align: center">Nome</th>
                    <th style="text-align: center">Cargo</th>
                    <th style="text-align: center">Data da Admissão</th>
                    <th style="text-align: center">Tipo de Exame</th>
                    <th style="text-align: center">Data do ASO</th>
                    <th style="text-align: center">Data de Vencimento</th>
                    <th style="text-align: center">Dias</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(vencimento, index) in dados" :key="index"
                    :class="vencimento.dias_vencer <= periodo_vencimento_numero ? 'table-danger': ''">
                    <td style="text-align: center">{{ index + 1 }}</td>
                    <td style="text-align: center">{{ vencimento.colaborador }}</td>
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
</template>

<script>
import configselect2 from "../../Select2/mixSelec2";
import ExportacaoMixin from "../../../mixins/Exportacoes";
import Utils from "../../../mixins/Utils";
import Validacoes from "../../../mixins/Validacoes";
import Configuracoes from "../../../mixins/Configuracoes.js";

export default {
    mixins: [configselect2, ExportacaoMixin, Utils, Validacoes, Configuracoes],
    data() {
        return {
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
        }
    },
    methods: {
        buscarDados() {
            this.preload = true;
            axios.post(`${URL_ADMIN}/relatorios/vencimentoasos`, this.controle.dados).then(res => {
                this.dados = res.data.dados;
                this.periodo_vencimento_numero = res.data.periodo_vencimento_numero;
                this.periodo_vencimento_extenso = res.data.periodo_vencimento_extenso;
                // this.listaTiposExame = this.dados.lista_tipos_exame;
                this.preload = false;
            })
        },
        gerarPdf() {
            // let link = `${URL_ADMIN}/relatorios/controleusuarios/pdf/${this.form}`;
            // open(link, '_blank');
        },
        tiposExames() {
            axios.get(`${URL_ADMIN}/relatorios/tipos-exames`).then(response => {
                this.listaTiposExame = response.data;
            });
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
