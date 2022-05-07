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
                    <h5>Projeto: {{ dados.projeto.nome }}</h5>
                    <p>{{ dados.projeto.preenchidas }} preenchida(s) de {{ dados.projeto.qnt_total }} vaga(s)</p>
                </div>
            </div>

            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="text-center">Vaga/Cargo</th>
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
                        <tr v-for="(item, index) in dados.vagas_projeto">
                            <td class="text-center">{{ item.vaga_aberta.titulo }}<br>
                                ({{ item.vaga_aberta.vaga.nome }})<br>
                            </td>
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

            <div class="col-12">
                <h5>Resumo de mobilização geral:</h5>
                <div class="alert alert-info p-2 resumo">
                    <p>Total de curriculos cadastrados: <strong>{{ dados.total_geral_curriculos }}</strong></p>
                    <p>Total de mobilizados: <strong>{{ dados.total_geral_curriculos_feedbacks }}</strong></p>
                    <p>Total de curriculos selecionados: <strong>{{ dados.total_geral_curriculos_selecionados
                        }}</strong></p>
                    <p>Total de curriculos selecionados: <strong>{{ dados.total_geral_curriculos_selecionados
                        }}</strong></p>
                    <p>Total de curriculos standby: <strong>{{ dados.total_geral_curriculos_standby }}</strong></p>
                    <p>Total de curriculos em Parecer RH: <strong>{{ dados.total_em_parecer_rh }}</strong></p>
                    <p>Total de curriculos em Parecer Rota - Transporte: <strong>{{ dados.total_em_parecer_rota
                        }}</strong></p>
                    <p>Total de curriculos em Parecer Entrevista Técnica: <strong>{{ dados.total_em_parecer_tecnica
                        }}</strong></p>
                    <p>Total de curriculos em Parecer Teste Prático: <strong>{{ dados.total_em_parecer_teste }}</strong>
                    </p>
                    <p>Total de curriculos em Resultado Integrado: <strong>{{ dados.total_em_resultado_integrado
                        }}</strong></p>
                </div>
            </div>
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
            await axios.get(`${this.urlBase}/seleciona-projeto/${this.projeto_id}`).then(response => {
                this.dados = response.data;
                this.showRelatorio = true;
                this.preload = false;
            }).catch(error => {
                this.preload = false;
            });
        }
    }
};
</script>

<style scoped>
.table thead th {
    vertical-align: middle;
}

.resumo p {
    line-height: 1.3rem !important;
    margin: auto !important;
}
</style>
