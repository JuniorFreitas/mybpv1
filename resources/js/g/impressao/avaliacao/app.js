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
                planos_acoes_delete: [],
                fluxo_etapas: []
            }
        }
    },
    components: {
        radarchart
    },
    methods: {
        normalizarTituloEtapa(titulo) {
            const valor = String(titulo || '')
                .trim()
                .toUpperCase()

            if (/^AUTO\s*AVALIA[CÇ][AÃ]O$/i.test(valor)) {
                return 'AUTOAVALIAÇÃO'
            }

            return valor
        },
        tituloEtapaFluxoPdf(indice, avaliador) {
            if (avaliador && avaliador.tipo) {
                return this.normalizarTituloEtapa(avaliador.tipo)
            }
            const etapas = this.formAvaliarFinal.fluxo_etapas
            if (etapas && etapas[indice] && etapas[indice].label) {
                return this.normalizarTituloEtapa(etapas[indice].label)
            }
            if (avaliador && avaliador.origem === 'Funcionario') {
                return 'AUTOAVALIAÇÃO'
            }
            return 'AVALIADOR ' + (indice + 1)
        },
        tituloConsideracoesPdf(indice, avaliador) {
            const titulo = this.tituloEtapaFluxoPdf(indice, avaliador)
            return titulo === 'AUTOAVALIAÇÃO' ? 'CONSIDERAÇÕES DA AUTOAVALIAÇÃO' : 'CONSIDERAÇÕES DO ' + titulo
        },
        comentarioVisivel(avaliador) {
            return Boolean(avaliador && String(avaliador.comentario || '').trim())
        },
        ordemFluxoComentario(avaliador, indice) {
            const titulo = this.tituloEtapaFluxoPdf(indice, avaliador)

            if (titulo === 'AUTOAVALIAÇÃO') {
                return -1
            }

            const etapas = this.formAvaliarFinal.fluxo_etapas || []
            const indiceFluxo = etapas.findIndex((etapa) => this.normalizarTituloEtapa(etapa.label) === titulo)

            if (indiceFluxo !== -1) {
                return indiceFluxo
            }

            return 999 + indice
        },
        comentariosOrdenados() {
            const avaliadores = this.formAvaliarFinal.result_topico_pai_agrupado?.[0]?.[0]?.avaliadores || []

            return avaliadores
                .map((avaliador, indice) => ({
                    ...avaliador,
                    __indice: indice,
                    __titulo: this.tituloEtapaFluxoPdf(indice, avaliador)
                }))
                .filter((avaliador) => this.comentarioVisivel(avaliador))
                .sort((a, b) => this.ordemFluxoComentario(a, a.__indice) - this.ordemFluxoComentario(b, b.__indice))
        },
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
        this.formAvaliarFinal.fluxo_etapas = dados.fluxo_etapas || []

        this.print()
    }
})

registerGlobals(app)
app.mount('#app')
