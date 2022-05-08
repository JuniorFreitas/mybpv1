<template>
    <div>
        <fieldset>
            <legend>Filtro</legend>
            <form class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Projetos</label>
                        <select2 :settings="settings2" v-model="projeto_id" :options="listProjetos"
                                 @change="abriRelatorio()"></select2>
                    </div>
                </div>
            </form>
        </fieldset>

        <preload v-if="preload"></preload>

        <div class="alert alert-warning mt-2" v-show="!preload && !projeto_id">
            Selecione um projeto
        </div>

        <div class="row" v-if="!preload && showRelatorio">
            <div class="col-12">
                <div class="text-center">
                    <h5 class="text-uppercase">Projeto: {{ dados.projeto.nome }}</h5>
                    <pre style="margin-bottom: -8px">{{ dados.projeto.preenchidas
                        }} preenchida(s) de {{ dados.projeto.qnt_total }} vaga(s)
                    </pre>
                    <a class="btn btn-primary btn-sm" target="_blank" :href="`mobilizacao/pdf/${projeto_id}`"
                       v-if="dados.vagas_projeto.length">
                        <i class="fas fa-file-pdf"></i> GERAR PDF
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" v-if="dados.vagas_projeto.length"
                            @click.prevent="exportaExcel()"
                            :disabled="preloadExportacao">
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                    </button>
                </div>
            </div>

            <div class="col-12" v-if="!dados.vagas_projeto.length">
                <div class="alert alert-warning mt-2">Nenhum registro encontrado!</div>
            </div>

            <template v-if="dados.vagas_projeto.length">
                <div class="col-12 p-3" v-for="(item, index) in dados.vagas_projeto">
                    <div class="text-center">
                        <h5>{{ item.vaga_aberta.titulo }}<br>
                            <pre>({{ item.vaga_aberta.vaga.nome }})</pre>
                        </h5>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Total: <strong>
                                    {{ item.qnt_total }}
                                </strong></li>
                                <li class="list-group-item">Preenchidas: <strong>
                                    {{ item.qnt_preenchida }}
                                </strong></li>
                                <li class="list-group-item">Em Processo de Seleção: <strong>
                                    {{ item.em_processo_selecao }}
                                </strong></li>
                                <li class="list-group-item">Treinamento Fase 1: <strong>
                                    {{ item.treinamento_fase_1 }}
                                </strong></li>
                                <li class="list-group-item">Pendente Treinamento: <strong>
                                    {{ item.status_pendente_treinamento }}
                                </strong></li>
                                <li class="list-group-item">Exames: <strong>
                                    {{ item.status_encaminhado_exame }}
                                </strong></li>
                                <li class="list-group-item">Portaria: <strong>
                                    {{ item.documento_portaria }}
                                </strong></li>
                                <li class="list-group-item">Aguardando Qualificação: <strong>
                                    {{ item.status_aguardando_qualificacao }}
                                </strong></li>
                                <li class="list-group-item">ASO no Ambulatório: <strong>
                                    {{ item.status_aso_no_ambulatorio }}
                                </strong></li>
                                <li class="list-group-item">Cancelados: <strong>
                                    {{ item.status_cancelado }}
                                </strong></li>
                                <li class="list-group-item">Desistências: <strong>
                                    {{ item.status_desistencia }}
                                </strong></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Encaminhado para Exames: <strong>
                                    {{ item.status_encaminhado_exame }}
                                </strong></li>
                                <li class="list-group-item">Pendente ASO: <strong>
                                    {{ item.status_pendente_aso }}
                                </strong></li>
                                <li class="list-group-item">Pendente Documentos: <strong>
                                    {{ item.status_pendente_documento }}
                                </strong></li>
                                <li class="list-group-item">Pronto para admissão: <strong>
                                    {{ item.status_pronto_para_admissao }}
                                </strong></li>
                                <li class="list-group-item">Admitidos: <strong>
                                    {{ item.status_admitido }}
                                </strong></li>
                                <li class="list-group-item">Entregues na área: <strong>
                                    {{ item.entregue_area }}
                                </strong></li>
                                <li class="list-group-item">StandBy: <strong>
                                    {{ item.status_standby }}
                                </strong></li>
                                <li class="list-group-item">Admissão tipo determinada: <strong>
                                    {{ item.tipo_admissao_determinado }}
                                </strong></li>
                                <li class="list-group-item">Admissão tipo fixo: <strong>
                                    {{ item.tipo_admissao_fixo }}
                                </strong></li>
                                <li class="list-group-item">Admissão tipo intermitente: <strong>
                                    {{ item.tipo_admissao_intermitente }}
                                </strong></li>
                                <li class="list-group-item">Admissão tipo temporária: <strong>
                                    {{ item.tipo_admissao_temporario }}
                                </strong></li>
                            </ul>
                        </div>
                    </div>


                    <div v-if="false" class="table-responsive p-2">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr class="table-secondary">
                                <!--                            <th class="text-center">Vaga/Cargo</th>-->
                                <th class="text-center">Total</th>
                                <th class="text-center">Preenchida</th>
                                <th class="text-center">Em Processo de Seleção</th>
                                <th class="text-center">Treinamento Fase 1</th>
                                <th class="text-center">Exame</th>
                                <th class="text-center">Portaria</th>
                                <th class="text-center">Admitido</th>
                                <th class="text-center">Ag. Qualificação</th>
                                <th class="text-center">ASO no Ambulatório</th>
                                <th class="text-center">Cancelado</th>
                                <th class="text-center">Desistência</th>
                                <th class="text-center">Enc. Exame</th>
                                <th class="text-center">Pendente ASO</th>
                                <th class="text-center">Pendente Documento</th>
                                <th class="text-center">Pendente Treinamento</th>
                                <th class="text-center">Pronto para admissão</th>
                                <th class="text-center">Admitido</th>
                                <th class="text-center">StandBy</th>
                                <th class="text-center">Admissão Determinada</th>
                                <th class="text-center">Admissão Fixo</th>
                                <th class="text-center">Admissão Intermitente</th>
                                <th class="text-center">Admissão Temporária</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="table-light">
                                <!--                            <td class="text-center">{{ item.vaga_aberta.titulo }}<br>-->
                                <!--                                ({{ item.vaga_aberta.vaga.nome }})<br>-->
                                <!--                            </td>-->
                                <td class="text-center">{{ item.qnt_total }}</td>
                                <td class="text-center">{{ item.qnt_preenchida }}</td>
                                <td class="text-center">{{ item.em_processo_selecao }}</td>
                                <td class="text-center">Treinamento Fase 1</td>
                                <td class="text-center">{{ item.status_encaminhado_exame }}</td>
                                <td class="text-center">Portaria</td>
                                <td class="text-center">{{ item.status_admitido }}</td>
                                <td class="text-center">{{ item.status_aguardando_qualificacao }}</td>
                                <td class="text-center">{{ item.status_aso_no_ambulatorio }}</td>
                                <td class="text-center">{{ item.status_cancelado }}</td>
                                <td class="text-center">{{ item.status_desistencia }}</td>
                                <td class="text-center">{{ item.status_encaminhado_exame }}</td>
                                <td class="text-center">{{ item.status_pendente_aso }}</td>
                                <td class="text-center">{{ item.status_pendente_documento }}</td>
                                <td class="text-center">{{ item.status_pendente_treinamento }}</td>
                                <td class="text-center">{{ item.status_pronto_para_admissao }}</td>
                                <td class="text-center">{{ item.status_admitido }}</td>
                                <td class="text-center">{{ item.status_standby }}</td>
                                <td class="text-center">{{ item.tipo_admissao_determinado }}</td>
                                <td class="text-center">{{ item.tipo_admissao_fixo }}</td>
                                <td class="text-center">{{ item.tipo_admissao_intermitente }}</td>
                                <td class="text-center">{{ item.tipo_admissao_temporario }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 py-3">
                    <div class="row">
                        <div class="col-12 text-center">
                            <h5 class="mb-4 text-uppercase">Resumo geral mobilização:</h5>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Total de currículos cadastrados:
                                    <strong>{{ dados.total_geral_curriculos }}</strong></li>
                                <li class="list-group-item">Total de mobilizados:
                                    <strong>{{ dados.total_geral_curriculos_feedbacks }}</strong></li>
                                <li class="list-group-item">Total de currículos selecionados:
                                    <strong>{{ dados.total_geral_curriculos_selecionados
                                        }}</strong></li>
                                <li class="list-group-item">Total de currículos standby:
                                    <strong>{{ dados.total_geral_curriculos_standby }}</strong></li>
                                <li class="list-group-item">Total de currículos em Parecer RH:
                                    <strong>{{ dados.total_em_parecer_rh }}</strong></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Total de currículos em Parecer Rota - Transporte:
                                    <strong>{{ dados.total_em_parecer_rota
                                        }}</strong></li>
                                <li class="list-group-item">Total de currículos em Parecer Entrevista Técnica:
                                    <strong>{{ dados.total_em_parecer_tecnica
                                        }}</strong></li>
                                <li class="list-group-item">Total de currículos em Parecer Teste Prático:
                                    <strong>{{ dados.total_em_parecer_teste }}</strong>
                                </li>
                                <li class="list-group-item">Total de currículos em Resultado Integrado:
                                    <strong>{{ dados.total_em_resultado_integrado
                                        }}</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </template>

        </div>
    </div>
</template>

<script>
import Select2 from "../../../components/Select2/Select2";
import configselect2 from "../../../components/Select2/mixSelec2";


export default {
    name: "Mobilizacao",
    mixins: [configselect2],
    components: {
        Select2
    },
    async mounted() {
        this.preload = true;
        await axios.get(`${this.urlBase}/get-projetos`).then(response => {
            this.listProjetos = response.data;
            this.preload = false;
        }).catch(error => {
            this.preload = false;
        });
    },
    computed: {
        urlBase() {
            return `${URL_ADMIN}/planejamento/mobilizacao`;
        }
    },
    data() {
        return {
            preload: false,
            preloadExportacao: false,
            projeto_id: null,
            listProjetos: [],
            showRelatorio: false,
            dados: []
        };
    },
    methods: {
        async abriRelatorio() {
            this.preload = true;
            this.showRelatorio = false;
            if (this.projeto_id == null) {
                this.dados = [];
                this.preload = false;
                this.showRelatorio = false;
                return false;
            }
            await axios.get(`${this.urlBase}/seleciona-projeto/${this.projeto_id}`).then(({ data }) => {
                this.dados = data;
                this.showRelatorio = true;
                this.preload = false;
            }).catch(error => {
                this.preload = false;
            });
        },
        exportaExcel() {
            this.preloadExportacao = true;
            axios.post(`${this.urlBase}/export-excel`, {
                projeto: this.projeto_id
            }).then(({ data }) => {
                mostraSucesso(data.msg);
                this.preloadExportacao = false;
            }).catch(erro => {
                mostraErro(erro);
                this.preloadExportacao = false;
            });
        }
    }
};
</script>

<style scoped>
.table thead th, .table th, .table td {
    vertical-align: middle;
}

.resumo p {
    line-height: 1.3rem !important;
    margin: auto !important;
}
</style>
