<template>
    <div class="avaliacao-noventa-dias-wrapper">
        <!-- Preloader -->
        <p class="mt-2" v-if="preloadAvaliacao"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

        <!-- Modal de Formulário -->
        <modal v-if="exibirModal" :fechar="!preloadSalvarAvaliacao" :id="modalId" size="g" :modal-pai="modalPai" titulo="Formulário Avaliação de Experiência">
            <template #conteudo>
                <p class="mt-2" v-if="preloadSalvarAvaliacao"><i class="fa fa-spinner fa-pulse"></i> Salvando, aguarde...</p>
                <fieldset class="mb-2" v-show="!preloadSalvarAvaliacao">
                    <div class="form-group" v-for="(obj, index) in formAvaliacao.perguntas" :key="index">
                        <label>{{ obj.id }}) {{ obj.pergunta }}</label>
                        <div>
                            <select class="form-control" v-model="obj.nota" onchange="valida_campo_vazio(this, 1)" onblur="valida_campo_vazio(this, 1)">
                                <option value="">Selecione a nota</option>
                                <option v-for="nota in 5" :key="nota" :value="nota">{{ nota }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Gestor Imediato</label>
                        <input type="text" class="form-control" onblur="valida_campo_vazio(this, 1)" v-model="formAvaliacao.gestor_imediato" />
                    </div>
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea class="form-control" rows="4" v-model="formAvaliacao.observacao"></textarea>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button class="btn btn-primary" v-if="!preloadSalvarAvaliacao" @click="handleSalvar"><i class="fa fa-save"></i> Salvar</button>
            </template>
        </modal>

        <!-- Conteúdo Principal -->
        <div v-if="!preloadAvaliacao">
            <!-- Padrão relatório (individual): card + alerta de informações -->
            <template v-if="itemAvaliacaoExperiencia && urlBaseGerarLink">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Informações:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Os links de avaliação são gerados e têm validade de 60 dias.</li>
                        <li>Cada colaborador pode realizar no máximo 2 avaliações de experiência.</li>
                        <li>Links expirados ou já utilizados não podem ser reutilizados.</li>
                    </ul>
                </div>
                <div class="cards-lista-avaliacao90 mb-4">
                    <div
                        :class="[
                            'avaliacao-card',
                            'card-status-' + statusSlug(itemAvaliacaoExperiencia.status),
                            definicaoCardClass(itemAvaliacaoExperiencia.definicao_contrato)
                        ]"
                    >
                        <div class="card-header-row">
                            <div class="card-left">
                                <span :class="['status-badge', 'status-' + statusSlug(itemAvaliacaoExperiencia.status)]">
                                    <i :class="statusIcon(itemAvaliacaoExperiencia.status)"></i> {{ itemAvaliacaoExperiencia.status }}
                                </span>
                                <div class="colaborador-principal">
                                    <i class="fas fa-user-circle mr-1"></i>
                                    <strong>{{ itemAvaliacaoExperiencia.colaborador }}</strong>
                                </div>
                            </div>
                            <div class="card-right card-link-cell">
                                <template v-if="itemAvaliacaoExperiencia.link_avaliacao">
                                    <a
                                        :href="itemAvaliacaoExperiencia.link_avaliacao"
                                        target="_blank"
                                        class="btn btn-sm btn-success"
                                        title="Abrir avaliação em nova aba"
                                    >
                                        <i class="fas fa-external-link-alt"></i> Abrir avaliação
                                    </a>
                                </template>
                                <template v-else>
                                    <span v-if="itemAvaliacaoExperiencia.status === 'FORA DO PRAZO'" class="text-muted small"
                                        ><i class="fas fa-calendar-times"></i> Fora do prazo</span
                                    >
                                    <button
                                        v-else-if="!ehAvaliacaoCompletaItem(itemAvaliacaoExperiencia) && !preloadGerarLink"
                                        type="button"
                                        class="btn btn-sm btn-outline-primary btn-gerar-link"
                                        @click="handleGerarLink"
                                    >
                                        <i class="fa fa-link"></i> Gerar link
                                    </button>
                                    <span v-else-if="preloadGerarLink" class="text-muted small"><i class="fa fa-spinner fa-pulse"></i> Gerando...</span>
                                    <span v-else class="text-muted small"><i class="fas fa-check-double"></i> Avaliação completa</span>
                                </template>
                            </div>
                        </div>
                        <div class="card-details-row card-details-main">
                            <div class="detail-item">
                                <i class="fas fa-user-tie"></i>
                                <span class="detail-label">Gestor</span>
                                <span class="detail-value"
                                    >{{ itemAvaliacaoExperiencia.gestor_nome || '—'
                                    }}<small v-if="itemAvaliacaoExperiencia.gestor_login" class="text-muted d-block">{{
                                        itemAvaliacaoExperiencia.gestor_login
                                    }}</small></span
                                >
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-briefcase"></i>
                                <span class="detail-label">Cargo</span>
                                <span class="detail-value">{{ itemAvaliacaoExperiencia.cargo || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-tasks"></i>
                                <span class="detail-label">Função</span>
                                <span class="detail-value">{{ itemAvaliacaoExperiencia.funcao || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-building"></i>
                                <span class="detail-label">Centro de Custo</span>
                                <span class="detail-value">{{ itemAvaliacaoExperiencia.centro_custo || '—' }}</span>
                            </div>
                        </div>
                        <div class="card-details-row card-details-fixas">
                            <div class="detail-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="detail-label">Vencimento</span>
                                <span class="detail-value">{{ itemAvaliacaoExperiencia.prazo_vencido }}</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-hourglass-half"></i>
                                <span class="detail-label">Dias</span>
                                <span class="detail-value">
                                    <span v-if="itemAvaliacaoExperiencia.status === 'FORA DO PRAZO'" class="badge badge-secondary">Fora do prazo</span>
                                    <span v-else-if="itemAvaliacaoExperiencia.status === 'A VENCER'" class="badge badge-info"
                                        >{{ itemAvaliacaoExperiencia.dias_para_vencer }} dias</span
                                    >
                                    <span v-else-if="itemAvaliacaoExperiencia.status === 'VENCE HOJE'" class="badge badge-warning">Hoje</span>
                                    <span v-else-if="itemAvaliacaoExperiencia.status === 'COMPLETA'" class="badge badge-secondary">—</span>
                                    <span v-else class="badge badge-danger">{{ itemAvaliacaoExperiencia.dias_atraso }} dias atrás</span>
                                </span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clipboard-check"></i>
                                <span class="detail-label">Avaliações</span>
                                <span class="detail-value">
                                    <span v-if="itemAvaliacaoExperiencia.qnt_avaliacoes === 0" class="badge badge-secondary"
                                        ><i class="fas fa-times"></i> Nenhuma</span
                                    >
                                    <span v-else-if="itemAvaliacaoExperiencia.qnt_avaliacoes === 1" class="badge badge-primary"
                                        ><i class="fas fa-check"></i> 1 Avaliação</span
                                    >
                                    <span v-else class="badge badge-success"
                                        ><i class="fas fa-check-double"></i> {{ itemAvaliacaoExperiencia.qnt_avaliacoes }} Avaliações</span
                                    >
                                </span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-gavel"></i>
                                <span class="detail-label">Definição</span>
                                <span class="detail-value">
                                    <span v-if="itemAvaliacaoExperiencia.definicao_contrato === 'prorroga'" class="badge badge-success"
                                        ><i class="fas fa-check-circle"></i> Prorroga o contrato</span
                                    >
                                    <span v-else-if="itemAvaliacaoExperiencia.definicao_contrato === 'finaliza'" class="badge badge-danger"
                                        ><i class="fas fa-times-circle"></i> Finaliza o contrato</span
                                    >
                                    <span v-else class="text-muted">—</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Layout legado (sem item do relatório ou sem URL gerar link): tabelas + botões -->
            <template v-else>
                <div class="table-responsive" v-if="avNoventaVencimentoData && exibirVencimentos">
                    <label><strong>Vencimentos da Avaliação de Experiência</strong></label>
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
                                <td class="text-center">{{ avNoventaVencimentoData.prazo_experiencia }}</td>
                                <td class="text-center">{{ avNoventaVencimentoData.feedback.avaliacao_noventa_vencimento.prazo_dia_inicial }}</td>
                                <td class="text-center">{{ avNoventaVencimentoData.feedback.avaliacao_noventa_vencimento.prazo_dia_final }}</td>
                            </tr>
                            <tr v-if="avNoventaVencimentoData.tipo_admissao === 'TEMPORARIO' || avNoventaVencimentoData.tipo_admissao === 'DETERMINADO'">
                                <td class="text-center">{{ avNoventaVencimentoData.tipo_admissao }}</td>
                                <td class="text-center">{{ avNoventaVencimentoData.data_encerramento }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button
                    v-if="urlBaseGerarLink && precisaGerarLink() && !preloadGerarLink"
                    type="button"
                    class="btn btn-outline-primary mb-3 mr-2"
                    @click="handleGerarLink"
                >
                    <i class="fa fa-link"></i> Gerar link
                </button>
                <span v-if="urlBaseGerarLink && precisaGerarLink() && preloadGerarLink" class="mb-3 mr-2"
                    ><i class="fa fa-spinner fa-pulse"></i> Gerando link...</span
                >
                <button
                    v-if="exibirBotaoAdicionar && podeAdicionarAvaliacao() && temTokenValido()"
                    class="btn btn-primary mb-3"
                    @click="handleAdicionarAvaliacao"
                >
                    <i class="fa fa-plus"></i> Adicionar Avaliação de Experiência
                </button>
                <div v-if="!podeAdicionarAvaliacao()" class="alert alert-info mb-3"><i class="fa fa-info-circle"></i> {{ getStatusAvaliacao() }}</div>
            </template>

            <!-- Tabela de Avaliações Realizadas (sempre exibida quando houver dados) -->
            <div class="table-responsive" v-if="tabelaNoventaAvaliacao.length > 0">
                <label><strong>Avaliações de Experiência Realizadas</strong></label>
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
            <div v-else class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Nenhuma avaliação de experiência realizada ainda.</div>
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
        },

        /**
         * Base URL para gerar link (ex.: do Histórico). Se informado, exibe botão "Gerar link" quando não houver token válido.
         */
        urlBaseGerarLink: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            preloadGerarLink: false
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
         * Indica se há token válido (não expirado e avaliação não realizada)
         */
        temTokenValido() {
            const venc =
                this.avNoventaVencimentoData && this.avNoventaVencimentoData.feedback
                    ? this.avNoventaVencimentoData.feedback.avaliacao_noventa_vencimento
                    : null
            if (!venc || !venc.token_avaliacao) return false
            if (venc.avaliacao_realizada === true) return false
            const expiracao = venc.token_expiracao ? new Date(venc.token_expiracao) : null
            if (expiracao && expiracao < new Date()) return false
            return true
        },

        /**
         * Exibe botão "Gerar link" quando pode adicionar avaliação mas não tem token válido
         */
        precisaGerarLink() {
            return this.podeAdicionarAvaliacao() && !this.temTokenValido()
        },

        statusSlug(s) {
            if (!s) return ''
            return String(s).toLowerCase().replace(/\s+/g, '-')
        },
        statusIcon(s) {
            if (s === 'VENCIDO') return 'fas fa-exclamation-triangle'
            if (s === 'VENCE HOJE') return 'fas fa-clock'
            if (s === 'COMPLETA') return 'fas fa-check'
            if (s === 'FORA DO PRAZO') return 'fas fa-calendar-times'
            return 'fas fa-calendar-alt'
        },
        definicaoCardClass(definicao) {
            if (definicao === 'prorroga') return 'card-definicao-prorroga'
            if (definicao === 'finaliza') return 'card-definicao-finaliza'
            return ''
        },
        ehAvaliacaoCompletaItem(item) {
            return item && (item.status === 'COMPLETA' || item.qnt_avaliacoes >= 2)
        },

        /**
         * Gera link de Avaliação de Experiência (enfileira job e faz polling até o token aparecer)
         */
        handleGerarLink() {
            if (!this.urlBaseGerarLink || !this.feedbackId) return
            const url = this.urlBaseGerarLink.replace(/\/$/, '') + '/' + this.feedbackId + '/gerar-link'
            this.preloadGerarLink = true
            axios
                .post(url)
                .then(() => {
                    this.pollingTokenAvaliacao((ok) => {
                        this.preloadGerarLink = false
                        if (ok && typeof mostraSucesso === 'function') {
                            mostraSucesso('Link gerado com sucesso. Você já pode clicar em "Adicionar Avaliação de Experiência".')
                        } else if (typeof toastr !== 'undefined') {
                            toastr.info('Geração enfileirada. Atualize a aba em alguns segundos para ver o link.')
                        }
                    })
                })
                .catch((err) => {
                    this.preloadGerarLink = false
                    const msg = (err.response && err.response.data && err.response.data.message) || 'Erro ao gerar link'
                    if (typeof mostraErro === 'function') mostraErro('', msg)
                    else if (typeof toastr !== 'undefined') toastr.error(msg)
                })
        },

        /**
         * Polling: recarrega dados da avaliação até token aparecer ou atingir tentativas
         */
        pollingTokenAvaliacao(callback, tentativas = 12, intervalo = 1500) {
            let count = 0
            const timer = setInterval(() => {
                this.carregarDadosAvaliacao(this.feedbackId)
                    .then(() => {
                        if (this.temTokenValido()) {
                            clearInterval(timer)
                            callback(true)
                        } else if (++count >= tentativas) {
                            clearInterval(timer)
                            callback(false)
                        }
                    })
                    .catch(() => {
                        if (++count >= tentativas) {
                            clearInterval(timer)
                            callback(false)
                        }
                    })
            }, intervalo)
        },

        /**
         * Handler para adicionar nova avaliação
         */
        handleAdicionarAvaliacao() {
            // Abre o link de avaliação com token em nova aba
            try {
                const venc =
                    this.avNoventaVencimentoData && this.avNoventaVencimentoData.feedback
                        ? this.avNoventaVencimentoData.feedback.avaliacao_noventa_vencimento
                        : null

                const token = venc && venc.token_avaliacao ? venc.token_avaliacao : null
                const expiracao = venc && venc.token_expiracao ? new Date(venc.token_expiracao) : null
                const realizada = venc && typeof venc.avaliacao_realizada !== 'undefined' ? !!venc.avaliacao_realizada : false

                // Validações básicas antes de abrir o link
                if (!token) {
                    if (typeof mostraErro === 'function') {
                        mostraErro('', 'Token de avaliação indisponível para este colaborador. Gere pelo relatório de Avaliação de Experiência.')
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
                        mostraErro('', 'Token expirado. Gere um novo token no relatório de Avaliação de Experiência.')
                    }
                    return
                }

                const base = window.location.origin
                const url = `${base}/avaliacao-de-experiencia/${token}`
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

/* Card padrão relatório (individual) */
.cards-lista-avaliacao90 {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.avaliacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 0;
    transition: all 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border-left: 4px solid #6c757d;
    overflow: hidden;
}
.avaliacao-card.card-status-vencido {
    border-left-color: #dc3545;
}
.avaliacao-card.card-status-vence-hoje {
    border-left-color: #f6c23e;
}
.avaliacao-card.card-status-completa {
    border-left-color: #28a745;
}
.avaliacao-card.card-status-a-vencer {
    border-left-color: #17a2b8;
}
.avaliacao-card .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
    border-bottom: 1px solid #f1f3f5;
}
.avaliacao-card .card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
    min-width: 0;
}
.avaliacao-card .card-right {
    flex-shrink: 0;
}
.avaliacao-card .colaborador-principal {
    display: flex;
    align-items: center;
    font-size: 1rem;
    color: #212529;
    overflow: hidden;
    min-width: 0;
}
.avaliacao-card .colaborador-principal i {
    color: #003755;
    flex-shrink: 0;
}
.avaliacao-card .status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    border-radius: 20px;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    white-space: nowrap;
    flex-shrink: 0;
}
.avaliacao-card .status-vencido {
    background: #dc3545;
    color: white;
}
.avaliacao-card .status-vence-hoje {
    background: #f6c23e;
    color: #212529;
}
.avaliacao-card .status-completa {
    background: #28a745;
    color: white;
}
.avaliacao-card .status-a-vencer {
    background: #17a2b8;
    color: white;
}
.avaliacao-card .status-fora-do-prazo {
    background: #6c757d;
    color: white;
}
.avaliacao-card.card-status-fora-do-prazo {
    border-left-color: #6c757d;
}
.avaliacao-card.card-definicao-prorroga {
    border-left-color: #28a745 !important;
    border-color: #b8ddc9;
    background: linear-gradient(180deg, #f0f9f4 0%, #fff 30%);
}
.avaliacao-card.card-definicao-prorroga .card-header-row {
    background: linear-gradient(180deg, #d4edda 0%, #e8f5eb 100%);
    border-bottom-color: #c3e6cb;
}
.avaliacao-card.card-definicao-prorroga .card-details-fixas {
    background: #e8f5eb;
}
.avaliacao-card.card-definicao-finaliza {
    border-left-color: #dc3545 !important;
    border-color: #f5c6cb;
    background: linear-gradient(180deg, #fdf2f3 0%, #fff 30%);
}
.avaliacao-card.card-definicao-finaliza .card-header-row {
    background: linear-gradient(180deg, #f8d7da 0%, #fce8ea 100%);
    border-bottom-color: #f5c6cb;
}
.avaliacao-card.card-definicao-finaliza .card-details-fixas {
    background: #fce8ea;
}
.avaliacao-card .card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.5rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f3f5;
}
.avaliacao-card .card-details-row:last-child {
    border-bottom: none;
}
.avaliacao-card .card-details-main {
    padding-top: 0.75rem;
}
.avaliacao-card .card-details-fixas {
    background: #fafbfc;
    padding: 0.75rem 1.25rem;
}
.avaliacao-card .detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.813rem;
    min-width: 0;
}
.avaliacao-card .detail-item i:first-child {
    flex-shrink: 0;
    font-size: 0.875rem;
    color: #6c757d;
}
.avaliacao-card .detail-label {
    font-weight: 500;
    color: #6c757d;
    white-space: nowrap;
}
.avaliacao-card .detail-value {
    color: #212529;
    font-weight: 400;
}
.avaliacao-card .detail-value .badge {
    font-size: 0.75rem;
}
</style>
