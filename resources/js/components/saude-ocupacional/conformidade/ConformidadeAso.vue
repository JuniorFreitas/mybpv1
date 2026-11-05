<template>
    <div id="componenteConformidadeAso">
        <div class="d-flex flex-wrap mb-2 conformidade-chips">
            <button
                v-for="chip in chips"
                :key="chip.valor"
                type="button"
                class="btn btn-sm mr-1 mb-1"
                :class="filtros.status === chip.valor ? chip.ativo : 'btn-outline-secondary'"
                @click="filtrarStatus(chip.valor)"
            >
                {{ chip.label }} <span class="badge badge-light ml-1">{{ chip.total }}</span>
            </button>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex flex-wrap align-items-center mybp-filtros-compactos flex-grow-1 mr-2">
                    <input type="search" class="form-control form-control-sm mr-2 mb-1 filtro-busca" placeholder="Buscar colaborador" v-model="filtros.busca" @input="buscarComAtraso" aria-label="Buscar colaborador" />
                    <select class="form-control form-control-sm mr-2 mb-1 filtro-cargo" v-model.number="filtros.vaga_id" @change="recarregar(1)" aria-label="Filtrar por cargo">
                        <option :value="0">Todos os cargos</option>
                        <option v-for="c in opcoes ? opcoes.cargos : []" :key="c.value" :value="c.value">{{ c.label }}</option>
                    </select>
                </div>
                <div class="d-flex flex-wrap">
                    <button v-if="opcoes && opcoes.pode_encaminhar" type="button" class="btn btn-sm btn-primary mr-1 mb-1" :disabled="!selecionados.length" @click="$refs.lote.abrir(selecionados)">
                        <i class="fa fa-paper-plane"></i> Encaminhar selecionados ({{ selecionados.length }})
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success mr-1 mb-1" :disabled="exportando" @click="exportar">
                        <i :class="exportando ? 'fa fa-spinner fa-pulse' : 'fa fa-file-excel'"></i> Exportar
                    </button>
                    <button v-if="opcoes && opcoes.pode_configurar" type="button" class="btn btn-sm btn-outline-secondary mb-1" @click="$refs.alertas.abrir(opcoes.alertas)">
                        <i class="fa fa-bell"></i> Alertas
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <p v-if="carregando && !itens.length" class="text-center p-3 mb-0"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>
                <p v-else-if="!itens.length" class="text-center text-muted p-3 mb-0">Nenhum colaborador neste filtro.</p>
                <div v-else class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 32px">
                                    <input type="checkbox" :checked="todosDaPaginaMarcados" @change="marcarPagina($event.target.checked)" aria-label="Selecionar todos da página" />
                                </th>
                                <th>Colaborador</th>
                                <th>Situação</th>
                                <th>Vencimento</th>
                                <th>Último ASO</th>
                                <th>Por que vence</th>
                                <th>Encaminhado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in itens" :key="item.feedback_id">
                                <td><input type="checkbox" :value="item.feedback_id" v-model="selecionados" :aria-label="`Selecionar ${item.colaborador}`" /></td>
                                <td>
                                    <a :href="`${historicoBase}?colaborador=${item.feedback_id}`" title="Histórico de ASO"><strong>{{ item.colaborador }}</strong></a>
                                    <div class="small text-muted">{{ item.cargo || 'Cargo não informado' }}</div>
                                </td>
                                <td><span class="badge" :class="badge(item.status)">{{ rotulo(item.status) }}</span></td>
                                <td class="text-nowrap">
                                    {{ item.vencimento_br || '—' }}
                                    <div class="small" :class="item.dias !== null && item.dias < 0 ? 'text-danger' : 'text-muted'" v-if="item.dias !== null">
                                        {{ item.dias < 0 ? `há ${-item.dias} dias` : item.dias === 0 ? 'hoje' : `em ${item.dias} dias` }}
                                    </div>
                                </td>
                                <td class="small">{{ item.ultimo_tipo || '—' }}<div class="text-muted" v-if="item.ultimo_realizado_br">{{ item.ultimo_realizado_br }}</div></td>
                                <td class="small text-muted">{{ item.motivo || '—' }}</td>
                                <td class="small">
                                    <span v-if="item.encaminhado_em_br" class="badge badge-info" title="Encaminhamento ainda sem resultado">{{ item.encaminhado_em_br }}</span>
                                    <span v-else class="text-muted">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between small" v-if="total">
                <span class="text-muted">{{ total }} colaborador(es) · página {{ pagina }} de {{ ultimaPagina }}<span v-if="selecionados.length"> · {{ selecionados.length }} selecionado(s)</span></span>
                <div>
                    <button type="button" class="btn btn-xs btn-outline-secondary mr-1" :disabled="pagina <= 1 || carregando" @click="recarregar(pagina - 1)">Anterior</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" :disabled="pagina >= ultimaPagina || carregando" @click="recarregar(pagina + 1)">Próxima</button>
                </div>
            </div>
        </div>

        <encaminhar-lote-modal v-if="opcoes" ref="lote" :opcoes="opcoes" @enviado="aposEncaminhar" />
        <alertas-config-modal ref="alertas" @salvo="(cfg) => (opcoes.alertas = cfg)" />
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import EncaminharLoteModal from './EncaminharLoteModal.vue'
import AlertasConfigModal from './AlertasConfigModal.vue'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/conformidade`
const STATUS = {
    vencido: { label: 'Vencido', badge: 'badge-danger', ativo: 'btn-danger' },
    vencendo: { label: 'Vencendo', badge: 'badge-warning', ativo: 'btn-warning' },
    sem_aso: { label: 'Sem ASO', badge: 'badge-dark', ativo: 'btn-dark' },
    em_dia: { label: 'Em dia', badge: 'badge-success', ativo: 'btn-success' }
}

export default defineComponent({
    name: 'ConformidadeAso',

    components: { EncaminharLoteModal, AlertasConfigModal },

    data() {
        const url = new URLSearchParams(window.location.search)
        return {
            carregando: false,
            exportando: false,
            opcoes: null,
            itens: [],
            total: 0,
            pagina: 1,
            ultimaPagina: 1,
            totais: { total: 0, vencido: 0, vencendo: 0, sem_aso: 0, em_dia: 0 },
            janelaDias: 90,
            selecionados: [],
            timer: null,
            filtros: {
                status: STATUS[url.get('status')] ? url.get('status') : '',
                busca: url.get('busca') || '',
                vaga_id: Number(url.get('vaga_id')) || 0
            }
        }
    },

    computed: {
        historicoBase() {
            return `${URL_ADMIN}/saude-ocupacional/historico-aso`
        },
        chips() {
            return [
                { valor: '', label: 'Todos', total: this.totais.total, ativo: 'btn-primary' },
                { valor: 'vencido', label: 'Vencidos', total: this.totais.vencido, ativo: STATUS.vencido.ativo },
                { valor: 'vencendo', label: `Vencendo (${this.janelaDias} dias)`, total: this.totais.vencendo, ativo: STATUS.vencendo.ativo },
                { valor: 'sem_aso', label: 'Sem ASO', total: this.totais.sem_aso, ativo: STATUS.sem_aso.ativo },
                { valor: 'em_dia', label: 'Em dia', total: this.totais.em_dia, ativo: STATUS.em_dia.ativo }
            ]
        },
        todosDaPaginaMarcados() {
            return this.itens.length > 0 && this.itens.every((i) => this.selecionados.includes(i.feedback_id))
        }
    },

    async mounted() {
        try {
            const { data } = await axios.get(`${BASE()}/opcoes`)
            this.opcoes = data
        } catch (e) {
            // erro exibido pelo interceptor global do axios
        }
        this.recarregar(Number(new URLSearchParams(window.location.search).get('page')) || 1)
    },

    beforeUnmount() {
        clearTimeout(this.timer)
    },

    methods: {
        rotulo(status) {
            return (STATUS[status] || {}).label || status
        },
        badge(status) {
            return (STATUS[status] || {}).badge || 'badge-light'
        },

        parametros() {
            const p = { page: this.pagina, pages: 30 }
            if (this.filtros.status) p.status = this.filtros.status
            if (this.filtros.busca.trim()) p.busca = this.filtros.busca.trim()
            if (this.filtros.vaga_id) p.vaga_id = this.filtros.vaga_id
            return p
        },

        sincronizarUrl() {
            const p = this.parametros()
            delete p.pages
            if (p.page === 1) delete p.page
            const qs = new URLSearchParams(p).toString()
            window.history.replaceState(null, '', window.location.pathname + (qs ? `?${qs}` : ''))
        },

        async recarregar(pagina = 1) {
            this.pagina = pagina
            this.carregando = true
            this.sincronizarUrl()
            try {
                const { data } = await axios.get(`${BASE()}/dados`, { params: this.parametros() })
                this.itens = data.itens || []
                this.total = data.total
                this.ultimaPagina = data.ultima_pagina || 1
                this.totais = data.totais
                this.janelaDias = data.janela_dias
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.carregando = false
            }
        },

        filtrarStatus(status) {
            this.filtros.status = status
            this.selecionados = []
            this.recarregar(1)
        },

        buscarComAtraso() {
            clearTimeout(this.timer)
            this.timer = setTimeout(() => this.recarregar(1), 400)
        },

        marcarPagina(marcar) {
            const ids = this.itens.map((i) => i.feedback_id)
            this.selecionados = marcar
                ? [...new Set([...this.selecionados, ...ids])]
                : this.selecionados.filter((id) => !ids.includes(id))
        },

        aposEncaminhar() {
            this.selecionados = []
            // O lote roda em fila: recarrega em seguida para mostrar "Encaminhado".
            setTimeout(() => this.recarregar(this.pagina), 3000)
        },

        async exportar() {
            this.exportando = true
            try {
                const p = this.parametros()
                delete p.page
                delete p.pages
                const { data } = await axios.post(`${BASE()}/exportar`, p)
                mostraSucesso('', data.mensagem)
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.exportando = false
            }
        }
    }
})
</script>

<style scoped>
.filtro-busca {
    max-width: 280px;
}
.filtro-cargo {
    max-width: 260px;
}
</style>
