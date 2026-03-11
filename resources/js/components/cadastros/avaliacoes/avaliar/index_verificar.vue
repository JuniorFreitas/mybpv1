<template>
    <div :id="hash">
        <!-- Modais -->
        <AvaliacaoModal
            ref="avaliacaoModal"
            @salvar="handleSalvarAvaliacao"
            @atualizar="atualizar"
        />

        <AvaliacaoFinalModal
            ref="avaliacaoFinalModal"
            @salvar="handleSalvarAvaliacaoFinal"
            @atualizar="atualizar"
        />

        <div id="conteudo">
            <!-- Filtros -->
            <AvaliacaoFilter
                :loading="controle.carregando"
                :avaliacoes="lista_avaliacoes"
                :status-options="statusAvaliacaoSelecionada"
                :filtro-data="controle.dados"
                @filtrar="atualizar"
                @atualizar="atualizar"
            />

            <!-- Legenda -->
            <AvaliacaoLegenda
                :avaliacao="selecionadaAvaliacao"
                :loading="controle.carregando"
            />

            <!-- Loading -->
            <p class="mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <!-- Mensagem vazia -->
            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <!-- Tabela -->
            <AvaliacaoTable
                v-show="!controle.carregando && lista.length > 0"
                :lista="lista"
                :avaliacao="selecionadaAvaliacao"
                :url-impressao="urlImpressao"
                :tem-privilegio="tem_privilegio_gestao_rh"
                @avaliar="handleAvaliar"
                @avaliar-final="handleAvaliarFinal"
                @visualizar="handleVisualizar"
                @visualizar-final="handleVisualizarFinal"
            />

            <!-- Paginação -->
            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlPaginacao"
                :por-pagina="qntPag"
                :dados="controle.dados"
                @carregou="carregou"
                @carregando="carregando"
            />
        </div>
    </div>
</template>

<script>
import controlePaginacao from '../../../ControlePaginacao'
import AvaliacaoModal from './components/AvaliacaoModal.vue'
import AvaliacaoFinalModal from './components/AvaliacaoFinalModal.vue'
import AvaliacaoTable from './components/AvaliacaoTable.vue'
import AvaliacaoFilter from './components/AvaliacaoFilter.vue'
import AvaliacaoLegenda from './components/AvaliacaoLegenda.vue'
import avaliacaoMixin from './mixins/avaliacaoMixin'

export default {
    name: 'AvaliacaoList',
    components: {
        controlePaginacao,
        AvaliacaoModal,
        AvaliacaoFinalModal,
        AvaliacaoTable,
        AvaliacaoFilter,
        AvaliacaoLegenda
    },
    mixins: [avaliacaoMixin],
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
        },
        filtro: {
            type: Boolean,
            required: false,
            default: true
        },
        modal: {
            type: String,
            required: false,
            default: ''
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            lista: [],
            lista_avaliacoes: [],
            tem_privilegio_gestao_rh: false,
            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliar/atualizar`,
            urlImpressao: `${URL_ADMIN}/cadastro/avaliacoes/avaliar/impressao`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoAvaliacao: '',
                    campoStatus: '',
                    ano_avaliacao: new Date().getFullYear(),
                    tipo_avaliacao: ''
                }
            }
        }
    },
    computed: {
        selecionadaAvaliacao() {
            return this.lista_avaliacoes.find(item =>
                item.id === this.controle.dados.campoAvaliacao
            ) ?? null
        },
        statusAvaliacaoSelecionada() {
            const statusSemAutoAvaliacao = [
                { label: 'Pendente avaliação gestor', value: 'Pendente' },
                { label: 'Avaliada pelo Gestor', value: 'Avaliada' },
                { label: 'Completa', value: 'Finalizada' }
            ]
            const statusComAutoAvaliacao = [
                { label: 'Pendente', value: 'Pendente' },
                { label: 'Avaliada', value: 'Avaliada' },
                { label: 'Finalizada', value: 'Finalizada' }
            ]

            return this.selecionadaAvaliacao?.auto_avaliacao
                ? statusComAutoAvaliacao
                : statusSemAutoAvaliacao
        }
    },
    async mounted() {
        try {
            await this.inicializar()
        } catch (error) {
            console.error('Erro ao inicializar componente:', error)
            toastr.error('Erro ao carregar dados iniciais', 'Erro!')
        }
    },
    methods: {
        async inicializar() {
            await this.listaAvaliacao()

            if (this.lista_avaliacoes?.length > 0) {
                this.controle.dados.campoAvaliacao = this.lista_avaliacoes[0].id
            }

            await this.atualizar()
        },

        async listaAvaliacao() {
            try {
                const response = await axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliar/lista/listavaliacoes`)
                this.lista_avaliacoes = response.data.lista_avaliacoes || []
            } catch (error) {
                console.error('Erro ao carregar lista de avaliações:', error)
                throw error
            }
        },

        // Handlers para eventos dos componentes filhos
        handleAvaliar(item) {
            this.$refs.avaliacaoModal.avaliar(item)
        },

        handleAvaliarFinal(item) {
            this.$refs.avaliacaoFinalModal.avaliarFinal(item)
        },

        handleVisualizar(item) {
            this.$refs.avaliacaoModal.visualizar(item)
        },

        handleVisualizarFinal(item) {
            this.$refs.avaliacaoFinalModal.visualizarFinal(item)
        },

        async handleSalvarAvaliacao() {
            await this.atualizar()
        },

        async handleSalvarAvaliacaoFinal() {
            await this.atualizar()
        },

        carregou(dados) {
            this.lista = dados.itens || []
            this.tem_privilegio_gestao_rh = dados.tem_privilegio_gestao_rh || false
            this.controle.carregando = false
        },

        carregando() {
            this.controle.carregando = true
        },

        async atualizar() {
            try {
                this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
                await this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            } catch (error) {
                console.error('Erro ao atualizar dados:', error)
                toastr.error('Erro ao atualizar dados', 'Erro!')
            }
        }
    }
}
</script>

<style scoped>
.card-header {
    background-color: white;
}

.btn-link {
    font-weight: 400;
    color: white;
    text-decoration: none;
}

.btn-link:hover {
    color: #dddddd;
}

.text-pink {
    color: pink !important;
}

.bg-pink {
    background: pink !important;
}

.text-azul {
    color: powderblue !important;
}

.bg-azul {
    background: powderblue !important;
}

.bg-cinza {
    background: #f1f1f1 !important;
}
</style>
