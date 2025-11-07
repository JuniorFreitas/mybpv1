<template>
    <div class="avaliacao-noventa-dias-wrapper">
        <!-- Preloader -->
        <p class="mt-2" v-if="preloadAvaliacao"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

        <!-- Modal de Formulário -->
        <modal v-if="exibirModal" :fechar="!preloadSalvarAvaliacao" :id="modalId" size="g" :modal-pai="modalPai" titulo="Formulário Avaliação 90 Dias">
            <template slot="conteudo">
                <p class="mt-2" v-if="preloadSalvarAvaliacao"><i class="fa fa-spinner fa-pulse"></i> Salvando, aguarde...</p>
                <fieldset class="mb-2" v-show="!preloadSalvarAvaliacao">
                    <div class="form-group" v-for="(obj, index) in formAvaliacao.perguntas" :key="index">
                        <label>{{ obj.id }}) {{ obj.pergunta }}</label>
                        <div>
                            <select class="form-control" v-model="obj.nota" onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)">
                                <option value="">Selecione a nota</option>
                                <option v-for="nota in 5" :key="nota" :value="nota">{{ nota }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Gestor Imediato</label>
                        <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)" v-model="formAvaliacao.gestor_imediato" />
                    </div>
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea class="form-control" rows="4" v-model="formAvaliacao.observacao"></textarea>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <button class="btn btn-primary" v-if="!preloadSalvarAvaliacao" @click="handleSalvar"><i class="fa fa-save"></i> Salvar</button>
            </template>
        </modal>

        <!-- Conteúdo Principal -->
        <div v-if="!preloadAvaliacao">
            <!-- Tabela de Vencimentos -->
            <div class="table-responsive" v-if="avNoventaVencimentoData && exibirVencimentos">
                <label><strong>Vencimentos da Avaliação 90 dias</strong></label>
                <table class="table table-bordered table-hover table-condensed">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Tipo</td>
                            <td class="text-center">Prazo</td>
                            <td class="text-center" v-if="avNoventaVencimentoData.tipo_admissao === 'FIXO'">1º Vencimento</td>
                            <td class="text-center" v-if="avNoventaVencimentoData.tipo_admissao === 'FIXO'">2º Vencimento</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="avNoventaVencimentoData.tipo_admissao === 'FIXO'">
                            <td class="text-center">{{ avNoventaVencimentoData.tipo_admissao }}</td>
                            <td class="text-center">
                                {{ avNoventaVencimentoData.prazo_experiencia }}
                            </td>
                            <td class="text-center">
                                {{ avNoventaVencimentoData.feedback.avaliacao_noventa_vencimento.prazo_dia_inicial }}
                            </td>
                            <td class="text-center">
                                {{ avNoventaVencimentoData.feedback.avaliacao_noventa_vencimento.prazo_dia_final }}
                            </td>
                        </tr>
                        <tr v-if="avNoventaVencimentoData.tipo_admissao === 'TEMPORARIO' || avNoventaVencimentoData.tipo_admissao === 'DETERMINADO'">
                            <td class="text-center">{{ avNoventaVencimentoData.tipo_admissao }}</td>
                            <td class="text-center">
                                {{ avNoventaVencimentoData.data_encerramento }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Botão Adicionar -->
            <button
                v-if="exibirBotaoAdicionar && podeAdicionarAvaliacao()"
                class="btn btn-primary mb-3"
                @click="handleAdicionarAvaliacao"
            >
                <i class="fa fa-plus"></i> Adicionar Avaliação
            </button>

            <!-- Mensagem quando atingir limite -->
            <div v-if="!podeAdicionarAvaliacao()" class="alert alert-info mb-3"><i class="fa fa-info-circle"></i> {{ getStatusAvaliacao() }}</div>

            <!-- Tabela de Avaliações Realizadas -->
            <div class="table-responsive" v-if="tabelaNoventaAvaliacao.length > 0">
                <label><strong>Avaliações Realizadas</strong></label>
                <table class="table table-bordered table-hover table-condensed">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Avaliação</td>
                            <td class="text-center">Avaliado em</td>
                            <td class="text-center">PDF</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in tabelaNoventaAvaliacao" :key="index">
                            <td class="text-center">{{ item.quantidade_avaliacao }}ª</td>
                            <td class="text-center">{{ item.created_at }}</td>
                            <td class="text-center">
                                <button class="btn btn-outline-default" @click="gerarPdfAvaliacao(item)"><i class="fas fa-file-pdf"></i> GERAR PDF</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mensagem quando não há avaliações -->
            <div v-else class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Nenhuma avaliação realizada ainda.</div>
        </div>
    </div>
</template>

<script>
import avaliacaoNoventaMixin from '../../mixins/avaliacaoNoventaMixin'

export default {
    name: 'AvaliacaoNoventaDias',

    mixins: [avaliacaoNoventaMixin],

    props: {
        /**
         * ID do feedback/admissão (obrigatório)
         */
        feedbackId: {
            type: Number,
            required: true
        },

        /**
         * ID do modal (para casos de múltiplos modais)
         */
        modalId: {
            type: String,
            default: 'janelaFormularioNoventaDias'
        },

        /**
         * ID do modal pai (para modais aninhados)
         */
        modalPai: {
            type: String,
            default: ''
        },

        /**
         * Exibir tabela de vencimentos
         */
        exibirVencimentos: {
            type: Boolean,
            default: true
        },

        /**
         * Exibir botão de adicionar avaliação
         */
        exibirBotaoAdicionar: {
            type: Boolean,
            default: true
        },

        /**
         * Exibir modal de formulário
         */
        exibirModal: {
            type: Boolean,
            default: true
        },

        /**
         * Modo somente leitura (sem ações de adicionar/editar)
         */
        readonly: {
            type: Boolean,
            default: false
        }
    },

    mounted() {
        this.inicializar()
    },

    watch: {
        feedbackId: {
            handler(newVal) {
                if (newVal) {
                    this.carregarDadosAvaliacao(newVal)
                }
            },
            immediate: false
        }
    },

    methods: {
        /**
         * Inicializa o componente
         */
        inicializar() {
            this.inicializarFormularioAvaliacao()
            if (this.feedbackId) {
                this.carregarDadosAvaliacao(this.feedbackId)
            }
        },

        /**
         * Handler para adicionar nova avaliação
         */
        handleAdicionarAvaliacao() {
            // Abre o link de avaliação com token em nova aba
            try {
                const venc = this.avNoventaVencimentoData && this.avNoventaVencimentoData.feedback
                    ? this.avNoventaVencimentoData.feedback.avaliacao_noventa_vencimento
                    : null

                const token = venc && venc.token_avaliacao ? venc.token_avaliacao : null
                const expiracao = venc && venc.token_expiracao ? new Date(venc.token_expiracao) : null
                const realizada = venc && typeof venc.avaliacao_realizada !== 'undefined' ? !!venc.avaliacao_realizada : false

                // Validações básicas antes de abrir o link
                if (!token) {
                    if (typeof mostraErro === 'function') {
                        mostraErro('', 'Token de avaliação indisponível para este colaborador. Gere pelo relatório de Avaliação 90 Dias.')
                    }
                    return
                }

                if (realizada === true) {
                    if (typeof mostraErro === 'function') {
                        mostraErro('', 'Esta avaliação já foi realizada. Se necessário, gere um novo token no relatório.')
                    }
                    return
                }

                if (expiracao && expiracao < new Date()) {
                    if (typeof mostraErro === 'function') {
                        mostraErro('', 'Token expirado. Gere um novo token no relatório de Avaliação 90 Dias.')
                    }
                    return
                }

                const base = window.location.origin
                const url = `${base}/avaliacao-90-dias/${token}`
                window.open(url, '_blank')

                // Mantém o evento para quem escuta fora (telemetria/analytics)
                this.$emit('adicionar-avaliacao', this.feedbackId)
            } catch (e) {
                console.error('Erro ao abrir link de avaliação:', e)
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'Não foi possível abrir o formulário de avaliação. Tente novamente.')
                }
            }
        },

        /**
         * Handler para salvar avaliação
         */
        handleSalvar() {
            this.salvarAvaliacao(this.modalId)
                .then(() => {
                    this.$emit('avaliacao-salva', this.formAvaliacao)
                })
                .catch((error) => {
                    this.$emit('erro-salvar', error)
                })
        }
    }
}
</script>

<style scoped>
.avaliacao-noventa-dias-wrapper {
    padding: 15px 0;
}

.table-responsive label strong {
    color: #653232;
    font-size: 1.1em;
}

.alert {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>
