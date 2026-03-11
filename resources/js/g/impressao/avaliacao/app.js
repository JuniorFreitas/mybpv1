import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import radarchart from '../../../components/Charts/Radar.vue'

const app = createApp({
    data() {
        return {
            chartsRadares: [],
            formAvaliarFinal: {
                dados_do_funcionario: [],
                avaliador_principal: '',
                status_avaliacao: '',
                total_aval: '',
                media_aval: '',
                nota_final: 0,
                resultado_topico_pai: [],
                result_topico_pai_agrupado: [],
                result_topico: [],
                result_subtopico: [],
                resultChart: [],
                planos_acoes: [],
                planos_acoes_delete: []
            }
        }
    },
    components: {
        radarchart
    },
    methods: {
        casasDecimais(valor) {
            return valor.toFixed(1)
        },
        print: function () {
            window.print()

            window.addEventListener(
                'afterprint',
                () => {
                    this.fecharJanela()
                },
                { once: true }
            )
        },
        fecharJanela: function () {
            window.close()
        }
    },
    mounted() {
        this.lista_topicos = dados.topicos
        this.formAvaliarFinal.dados_do_funcionario = dados.dados_do_funcionario
        this.formAvaliarFinal.avaliador_principal = dados.avaliador_principal
        this.formAvaliarFinal.status_avaliacao = dados.status_avaliacao
        this.formAvaliarFinal.total_aval = dados.total_aval
        this.formAvaliarFinal.media_aval = dados.media_aval
        this.formAvaliarFinal.nota_final = dados.nota_final
        this.formAvaliarFinal.resultado_topico_pai = dados.resultado_topico_pai
        this.formAvaliarFinal.result_topico_pai_agrupado = dados.result_topico_pai_agrupado
        this.formAvaliarFinal.result_topico = dados.result_topico
        this.formAvaliarFinal.result_subtopico = dados.result_subtopico
        this.formAvaliarFinal.resultChart = dados.resultChart
        this.formAvaliarFinal.planos_acoes = dados.planos_acoes

        this.print()
    }
})

registerGlobals(app)
app.mount('#app')
