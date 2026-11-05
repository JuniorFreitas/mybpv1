<template>
    <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Histórico</h3>
            <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="carregando" @click="carregar" aria-label="Atualizar histórico">
                <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
            </button>
        </div>
        <div class="card-body">
            <p class="text-center" v-if="carregando && !itens.length"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>
            <div v-else-if="!itens.length" class="text-muted">Nenhuma alteração registrada.</div>
            <div v-else class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Quando</th>
                            <th>Usuário</th>
                            <th>Modo</th>
                            <th>O que mudou</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="h in itens" :key="h.id">
                            <td class="text-nowrap">{{ h.created_at_br }}</td>
                            <td>{{ (h.usuario && h.usuario.nome) || '—' }}</td>
                            <td class="text-nowrap">
                                {{ labelModo(h.modo_aplicacao) }}
                                <span v-if="h.data_aplicacao_br"> ({{ h.data_aplicacao_br }})</span>
                                <span v-if="h.job_despachado" class="badge badge-info ml-1">recálculo</span>
                            </td>
                            <td>{{ h.descricao }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

export default defineComponent({
    name: 'RegrasHistorico',

    data() {
        return { itens: [], carregando: false }
    },

    mounted() {
        this.carregar()
    },

    methods: {
        labelModo(modo) {
            return { futuro: 'Só próximos', retroativo: 'Retroativo', a_partir_de: 'A partir de' }[modo] || '—'
        },

        async carregar() {
            this.carregando = true
            try {
                const { data } = await axios.get(`${URL_ADMIN}/saude-ocupacional/regras/historico`, { params: { pages: 20 } })
                this.itens = data.data || []
            } catch (e) {
                this.itens = []
            } finally {
                this.carregando = false
            }
        }
    }
})
</script>
