<template>
    <div id="componenteHistoricoAso">
        <div class="card card-outline card-primary mb-3">
            <div class="card-body mybp-filtros-compactos">
                <div class="position-relative busca-colaborador">
                    <label class="mybp-label" for="historico-busca">Colaborador (ativos e desligados)</label>
                    <input id="historico-busca" type="search" class="form-control form-control-sm" placeholder="Digite ao menos 3 letras do nome" v-model="busca" @input="buscarComAtraso" autocomplete="off" />
                    <ul class="list-group resultados" v-if="resultados.length">
                        <li v-for="r in resultados" :key="r.feedback_id" class="list-group-item list-group-item-action py-1" role="button" @click="abrir(r.feedback_id)">
                            {{ r.nome }} <small class="text-muted">{{ r.cargo || '' }}</small>
                            <span v-if="r.desligado" class="badge badge-secondary ml-1">desligado</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <p v-if="carregando" class="text-center"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

        <div class="card card-outline card-info" v-if="dados && !carregando">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    {{ dados.colaborador.nome }}
                    <span v-if="dados.colaborador.desligado" class="badge badge-secondary ml-1">desligado</span>
                </h3>
                <div class="small text-muted mt-1">
                    CPF {{ dados.colaborador.cpf || 'não informado' }} · {{ dados.colaborador.cargo || 'cargo não informado' }}
                    <span v-if="dados.colaborador.admissao_br"> · admitido em {{ dados.colaborador.admissao_br }}</span>
                    · <a :href="`${urlDocumentos}?feedback_id=${feedbackId}`"><i class="fa fa-folder-open"></i> Documentos SST</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="alert alert-light small m-2 mb-0" v-if="!dados.ve_dados_sensiveis">
                    Observações clínicas e anexos (laudos, resultados) são visíveis só para o perfil Saúde/SESMT.
                </div>
                <p v-if="!dados.itens.length" class="text-muted text-center p-3 mb-0">Nenhum encaminhamento ou ASO registrado.</p>
                <div v-else class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Exame</th>
                                <th>Encaminhado</th>
                                <th>Resultado</th>
                                <th>Vencimento</th>
                                <th>ASO</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in dados.itens" :key="item.encaminhamento_id">
                                <td>
                                    <strong>{{ item.tipo || '—' }}</strong>
                                    <div class="small text-muted">{{ item.clinica || 'Clínica não informada' }}<span v-if="item.pcmso"> · PCMSO {{ item.pcmso }}</span></div>
                                </td>
                                <td class="small">{{ item.encaminhado_em_br || '—' }}</td>
                                <td class="small">
                                    <template v-if="item.situacao === 'realizado'">
                                        <span class="badge" :class="badgeConclusao(item.conclusao)">{{ item.conclusao || 'sem conclusão' }}</span>
                                        <div class="text-muted">em {{ item.realizado_em_br }}</div>
                                        <div class="text-muted" v-if="item.trabalho_altura || item.espaco_confinado">
                                            Altura: {{ item.trabalho_altura || '—' }} · Confinado: {{ item.espaco_confinado || '—' }}
                                        </div>
                                        <div v-if="item.observacoes" class="text-info">Obs.: {{ item.observacoes }}</div>
                                        <div v-if="item.pendencias_quais" class="text-warning">Pendências: {{ item.pendencias_quais }}</div>
                                    </template>
                                    <span v-else-if="item.situacao === 'aguardando_resultado'" class="badge badge-info">Aguardando resultado</span>
                                    <span v-else class="badge badge-light">Não realizado</span>
                                    <div v-if="item.anexos === 'restrito'" class="text-muted"><i class="fa fa-lock"></i> anexos restritos</div>
                                    <div v-else-if="item.anexos" class="text-muted"><i class="fa fa-paperclip"></i> {{ item.anexos }} anexo(s)</div>
                                </td>
                                <td class="small">
                                    {{ item.vencimento_br || '—' }}
                                    <span v-if="item.atual && item.vencimento_br" class="badge badge-primary ml-1">atual</span>
                                </td>
                                <td class="small">
                                    <template v-if="item.aso">
                                        Emitido {{ item.aso.emitido_em_br }}
                                        <div><span class="badge" :class="badgeAssinatura(item.aso.assinatura)">{{ rotuloAssinatura(item.aso.assinatura) }}</span></div>
                                    </template>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td class="text-right text-nowrap">
                                    <template v-if="item.situacao === 'realizado' && item.resultado_id">
                                        <button v-if="dados.pode_emitir && podeReemitir(item)" type="button" class="btn btn-xs btn-outline-primary mr-1" @click="$refs.emitir.abrir(item)">
                                            <i class="fa fa-file-medical"></i> {{ item.aso ? 'Reemitir' : 'Emitir ASO' }}
                                        </button>
                                        <a v-if="item.aso" class="btn btn-xs btn-outline-secondary mr-1" :href="urlPdf(item)" target="_blank" rel="noopener" title="PDF do ASO"><i class="fa fa-file-pdf"></i></a>
                                        <button
                                            v-if="item.aso && dados.pode_emitir && dados.assinatura_habilitada && !item.aso.assinatura"
                                            type="button"
                                            class="btn btn-xs btn-outline-success"
                                            :disabled="enviando === item.resultado_id"
                                            @click="assinar(item)"
                                        >
                                            <i :class="enviando === item.resultado_id ? 'fa fa-spinner fa-pulse' : 'fa fa-signature'"></i> Assinar
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <emitir-aso-modal ref="emitir" @emitido="recarregar" />
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import EmitirAsoModal from './EmitirAsoModal.vue'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/historico-aso`
const ASSINATURA = {
    enviado: ['Enviado para assinatura', 'badge-info'],
    em_assinatura: ['Em assinatura', 'badge-info'],
    concluido: ['Assinado', 'badge-success'],
    expirado: ['Assinatura expirada', 'badge-warning'],
    cancelado: ['Assinatura cancelada', 'badge-secondary']
}

export default defineComponent({
    name: 'HistoricoAso',

    components: { EmitirAsoModal },

    data() {
        return { busca: '', resultados: [], timer: null, carregando: false, dados: null, feedbackId: null, enviando: null }
    },

    computed: {
        urlDocumentos() {
            return `${URL_ADMIN}/saude-ocupacional/documentos`
        }
    },

    mounted() {
        const id = Number(new URLSearchParams(window.location.search).get('colaborador'))
        if (id) this.abrir(id)
    },

    beforeUnmount() {
        clearTimeout(this.timer)
    },

    methods: {
        badgeConclusao(c) {
            if (!c) return 'badge-light'
            if (/inapto/i.test(c)) return 'badge-danger'
            if (/restri/i.test(c)) return 'badge-warning'
            return 'badge-success'
        },
        rotuloAssinatura(s) {
            return (ASSINATURA[s] || ['Não enviado'])[0]
        },
        badgeAssinatura(s) {
            return (ASSINATURA[s] || [null, 'badge-light'])[1]
        },
        podeReemitir(item) {
            return !item.aso || !['enviado', 'em_assinatura', 'concluido'].includes(item.aso.assinatura)
        },
        urlPdf(item) {
            return `${BASE()}/resultados/${item.resultado_id}/pdf`
        },

        buscarComAtraso() {
            clearTimeout(this.timer)
            this.timer = setTimeout(this.buscar, 300)
        },

        async buscar() {
            if (this.busca.trim().length < 3) {
                this.resultados = []
                return
            }
            try {
                const { data } = await axios.get(`${BASE()}/colaboradores`, { params: { busca: this.busca.trim() } })
                this.resultados = data.itens || []
            } catch (e) {
                this.resultados = []
            }
        },

        async abrir(feedbackId) {
            this.resultados = []
            this.feedbackId = feedbackId
            window.history.replaceState(null, '', `${window.location.pathname}?colaborador=${feedbackId}`)
            await this.recarregar()
            if (this.dados) this.busca = this.dados.colaborador.nome
        },

        async recarregar() {
            if (!this.feedbackId) return
            this.carregando = true
            try {
                const { data } = await axios.get(`${BASE()}/colaboradores/${this.feedbackId}`)
                this.dados = data
            } catch (e) {
                this.dados = null
            } finally {
                this.carregando = false
            }
        },

        async assinar(item) {
            if (!window.confirm('Enviar o ASO para assinatura do médico examinador e ciência do colaborador?')) return
            this.enviando = item.resultado_id
            try {
                const { data } = await axios.post(`${BASE()}/resultados/${item.resultado_id}/assinar`)
                mostraSucesso('', data.mensagem)
                this.recarregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.enviando = null
            }
        }
    }
})
</script>

<style scoped>
.busca-colaborador {
    max-width: 520px;
}
.resultados {
    position: absolute;
    z-index: 20;
    left: 0;
    right: 0;
    max-height: 280px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}
</style>
