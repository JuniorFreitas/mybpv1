<template>
    <div v-if="mostrarModal">
        <modal id="modalNps" :titulo="mensagens.titulo" size="g" :fechar="!loading" :mostrar-botao-fechar-no-rodape="false" @fechou="onFechou">
            <template #conteudo>
                <div v-if="loading" class="nps-loading">
                    <div class="nps-spinner"></div>
                    <p class="nps-loading-text">Carregando...</p>
                </div>
                <div v-else class="nps-body">
                    <div class="nps-hero">
                        <div class="nps-hero-icon" aria-hidden="true">★</div>
                        <p class="nps-subtitle">Sua opinião nos ajuda a melhorar. Leva menos de um minuto.</p>
                    </div>
                    <div class="nps-progress" v-if="perguntas.length > 0">
                        <div class="nps-progress-bar">
                            <div class="nps-progress-fill" :style="{ width: progressoPercentual + '%' }"></div>
                        </div>
                        <span class="nps-progress-text">{{ quantidadeRespondidas }} de {{ perguntas.length }} respondidas</span>
                    </div>
                    <div v-for="(pergunta, index) in perguntas" :key="pergunta.id" class="nps-card" :style="{ animationDelay: index * 0.06 + 's' }">
                        <span class="nps-card-badge">Pergunta {{ index + 1 }}</span>
                        <span class="nps-card-label">{{ pergunta.texto }}</span>
                        <div class="nps-scale">
                            <div v-for="n in 5" :key="n" class="nps-scale-item">
                                <button
                                    type="button"
                                    class="nps-scale-btn"
                                    :class="{ 'nps-scale-btn--active': respostas[pergunta.id] === n }"
                                    :aria-pressed="respostas[pergunta.id] === n"
                                    :aria-label="'Nota ' + n + ': ' + labelsEscala[n - 1]"
                                    @click="respostas[pergunta.id] = n"
                                >
                                    {{ n }}
                                </button>
                                <span class="nps-scale-item-label">{{ labelsEscala[n - 1] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #rodape>
                <template v-if="!loading">
                    <div class="nps-footer">
                        <button type="button" class="nps-btn nps-btn--ghost" @click="fecharResponderDepois">
                            {{ mensagens.botao_responder_depois }}
                        </button>
                        <button
                            type="button"
                            class="nps-btn nps-btn--primary"
                            :class="{ 'nps-btn--ready': todasRespondidas && !enviando }"
                            :disabled="!todasRespondidas || enviando"
                            @click="enviar"
                        >
                            <span v-if="enviando" class="nps-btn-spinner"></span>
                            <template v-else>
                                <span class="nps-btn-icon">{{ todasRespondidas ? '✓' : '' }}</span>
                                {{ mensagens.botao_enviar }}
                            </template>
                        </button>
                    </div>
                </template>
            </template>
        </modal>
    </div>
</template>

<script>
export default {
    name: 'NpsModal',

    data() {
        return {
            mostrarModal: false,
            loading: true,
            enviando: false,
            mensagens: {
                titulo: '',
                botao_enviar: 'Enviar',
                botao_responder_depois: 'Responder depois'
            },
            perguntas: [],
            respostas: {},
            labelsEscala: ['Muito insatisfeito', 'Insatisfeito', 'Neutro', 'Satisfeito', 'Muito satisfeito']
        }
    },

    computed: {
        todasRespondidas() {
            if (this.perguntas.length === 0) return false
            return this.perguntas.every((p) => {
                const nota = this.respostas[p.id]
                return typeof nota === 'number' && nota >= 1 && nota <= 5
            })
        },
        quantidadeRespondidas() {
            return this.perguntas.filter((p) => {
                const nota = this.respostas[p.id]
                return typeof nota === 'number' && nota >= 1 && nota <= 5
            }).length
        },
        progressoPercentual() {
            if (this.perguntas.length === 0) return 0
            return Math.round((this.quantidadeRespondidas / this.perguntas.length) * 100)
        }
    },

    mounted() {
        this.carregar()
    },

    methods: {
        carregar() {
            this.loading = true
            axios
                .get(`${window.URL_ADMIN}/nps/deve-exibir`)
                .then((res) => {
                    const data = res.data
                    if (!data.mostrar || !data.perguntas || data.perguntas.length === 0) {
                        return
                    }
                    this.mensagens = data.mensagens || this.mensagens
                    this.perguntas = data.perguntas
                    this.respostas = {}
                    this.perguntas.forEach((p) => {
                        this.respostas[p.id] = null
                    })
                    this.mostrarModal = true
                    this.loading = false
                    this.$nextTick(() => {
                        $('#modalNps').modal('show')
                    })
                })
                .catch(() => {})
                .finally(() => {
                    this.loading = false
                })
        },

        fecharResponderDepois() {
            $('#modalNps').modal('hide')
        },

        onFechou() {
            this.mostrarModal = false
        },

        enviar() {
            if (!this.todasRespondidas || this.enviando) return
            const respostas = this.perguntas.map((p) => ({
                nps_pergunta_id: p.id,
                nota: this.respostas[p.id]
            }))
            this.enviando = true
            axios
                .post(`${window.URL_ADMIN}/nps`, { respostas })
                .then(() => {
                    $('#modalNps').modal('hide')
                    if (typeof mostraSucesso === 'function') {
                        mostraSucesso('Obrigado pela sua avaliação!')
                    }
                })
                .catch((err) => {
                    const msg =
                        err.response && err.response.data && err.response.data.mensagem ? err.response.data.mensagem : 'Erro ao enviar. Tente novamente.'
                    if (typeof mostraErro === 'function') {
                        mostraErro('', msg)
                    }
                })
                .finally(() => {
                    this.enviando = false
                })
        }
    }
}
</script>

<style scoped>
.nps-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
    gap: 1rem;
}

.nps-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e9ecef;
    border-top-color: #072433;
    border-radius: 50%;
    animation: nps-spin 0.8s linear infinite;
}

@keyframes nps-spin {
    to {
        transform: rotate(360deg);
    }
}

.nps-loading-text {
    margin: 0;
    color: #6c757d;
    font-size: 0.9375rem;
}

.nps-body {
    padding: 0.25rem 0;
}

/* Hero / intro */
.nps-hero {
    text-align: center;
    margin-bottom: 1.5rem;
    padding: 1rem 0.5rem 1.25rem;
    background: linear-gradient(180deg, rgba(7, 36, 51, 0.04) 0%, transparent 100%);
    border-radius: 12px;
}

.nps-hero-icon {
    font-size: 2rem;
    color: #072433;
    margin-bottom: 0.5rem;
    opacity: 0.9;
    line-height: 1;
}

.nps-subtitle {
    margin: 0;
    color: #495057;
    font-size: 0.9375rem;
    line-height: 1.5;
}

/* Progress */
.nps-progress {
    margin-bottom: 1.25rem;
}

.nps-progress-bar {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.nps-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #072433, #0d3a52);
    border-radius: 3px;
    transition: width 0.35s ease;
}

.nps-progress-text {
    font-size: 0.8125rem;
    color: #868e96;
}

/* Cards */
.nps-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.35rem 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    transition:
        box-shadow 0.25s ease,
        border-color 0.25s ease,
        transform 0.2s ease;
    animation: nps-card-in 0.4s ease both;
}

@keyframes nps-card-in {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.nps-card:hover {
    border-color: #dee2e6;
    box-shadow: 0 4px 12px rgba(7, 36, 51, 0.08);
}

.nps-card:last-child {
    margin-bottom: 0;
}

.nps-card-badge {
    display: inline-block;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #072433;
    background: rgba(7, 36, 51, 0.08);
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.nps-card-label {
    display: block;
    font-weight: 500;
    color: #212529;
    font-size: 0.9375rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.nps-scale {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.25rem;
    flex-wrap: wrap;
}

.nps-scale-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.nps-scale-item-label {
    font-size: 0.6875rem;
    color: #868e96;
    text-align: center;
    margin-top: 0.4rem;
    line-height: 1.25;
    max-width: 82px;
}

.nps-scale-btn {
    width: 46px;
    height: 46px;
    min-width: 46px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    background: #fff;
    color: #495057;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.nps-scale-btn:hover {
    border-color: #072433;
    color: #072433;
    background: #f8f9fa;
    transform: scale(1.06);
}

.nps-scale-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(7, 36, 51, 0.2);
}

.nps-scale-btn--active {
    border-color: #072433;
    background: #072433;
    color: #fff;
}

.nps-scale-btn--active:hover {
    background: #0d3a52;
    border-color: #0d3a52;
    color: #fff;
    transform: scale(1.06);
}

.nps-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
    width: 100%;
    padding-top: 0.5rem;
    border-top: 1px solid #e9ecef;
}

.nps-btn {
    padding: 0.55rem 1.35rem;
    border-radius: 10px;
    font-size: 0.9375rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.nps-btn:focus {
    outline: none;
}

.nps-btn--ghost {
    background: transparent;
    color: #6c757d;
}

.nps-btn--ghost:hover {
    color: #495057;
    background: #f1f3f5;
}

.nps-btn--ghost:focus {
    box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.3);
}

.nps-btn--primary {
    background: #072433;
    color: #fff;
}

.nps-btn--primary:hover:not(:disabled) {
    background: #0d3a52;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(7, 36, 51, 0.25);
}

.nps-btn--primary.nps-btn--ready {
    box-shadow: 0 2px 8px rgba(7, 36, 51, 0.2);
}

.nps-btn--primary:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.nps-btn--primary:focus {
    box-shadow: 0 0 0 3px rgba(7, 36, 51, 0.3);
}

.nps-btn-icon {
    font-size: 1rem;
    line-height: 1;
}

.nps-btn-spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: nps-spin 0.7s linear infinite;
}
</style>
