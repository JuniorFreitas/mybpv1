<template>
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title mb-0">Simulador: por que o ASO vence nesta data?</h3>
        </div>
        <div class="card-body mybp-filtros-compactos">
            <div class="form-row">
                <div class="col-12 col-md-6 mb-2 position-relative">
                    <label class="mybp-label" for="simulador-busca">Colaborador</label>
                    <input
                        id="simulador-busca"
                        type="search"
                        class="form-control form-control-sm"
                        placeholder="Digite ao menos 3 letras do nome"
                        v-model="busca"
                        @input="buscarComAtraso"
                        autocomplete="off"
                    />
                    <ul class="list-group simulador-resultados" v-if="resultados.length">
                        <li
                            v-for="item in resultados"
                            :key="item.feedback_id"
                            class="list-group-item list-group-item-action py-1"
                            role="button"
                            @click="simular(item)"
                        >
                            {{ item.nome }} <small class="text-muted" v-if="item.cargo">— {{ item.cargo }}</small>
                        </li>
                    </ul>
                </div>
            </div>

            <p v-if="carregando" class="mb-0"><i class="fa fa-spinner fa-pulse"></i> Simulando...</p>

            <template v-if="resultado && !carregando">
                <p class="mb-2">
                    <strong>{{ resultado.colaborador.nome }}</strong>
                    <span class="text-muted">
                        — {{ resultado.colaborador.idade !== null ? `${resultado.colaborador.idade} anos` : 'sem data de nascimento' }}
                        · cargo: {{ resultado.colaborador.cargo || 'não identificado' }}
                    </span>
                </p>
                <p class="small mb-2">
                    <template v-if="!resultado.pgr_vigente">
                        <span class="text-muted">Sem PGR publicado: exposição a risco não é avaliada.</span>
                    </template>
                    <template v-else-if="resultado.colaborador.riscos.length">
                        Exposto a (PGR v{{ resultado.pgr_vigente.versao }}):
                        <span v-for="r in resultado.colaborador.riscos" :key="r" class="badge badge-warning mr-1">{{ r }}</span>
                    </template>
                    <template v-else>
                        <span class="text-muted">Sem risco no PGR v{{ resultado.pgr_vigente.versao }} para o cargo deste colaborador.</span>
                    </template>
                </p>
                <p class="small text-warning mb-2" v-if="resultado.colaborador.sem_nascimento">
                    Sem data de nascimento, as regras por idade não se aplicam a este colaborador.
                </p>

                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Último ASO</th>
                                <th>Se fizer hoje, vence em</th>
                                <th>Regras avaliadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tipo in resultado.tipos" :key="tipo.exame_tipo_id">
                                <td>{{ tipo.tipo }}<br /><small class="text-muted">padrão: {{ tipo.padrao }}</small></td>
                                <td>
                                    <template v-if="tipo.ultimo_aso">
                                        {{ tipo.ultimo_aso.data_realizacao_br }} → vence {{ tipo.ultimo_aso.vencimento_gravado_br || '—' }}
                                        <div v-if="tipo.ultimo_aso.divergente" class="small text-warning">
                                            Pelas regras de hoje: {{ tipo.ultimo_aso.vencimento_pelas_regras_br }} (recalcule em modo retroativo)
                                        </div>
                                    </template>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td>
                                    <strong>{{ tipo.se_realizado_hoje.vencimento_br }}</strong>
                                    <div class="small text-muted">{{ tipo.se_realizado_hoje.motivo }}</div>
                                </td>
                                <td>
                                    <span v-if="!tipo.regras.length" class="text-muted small">Nenhuma regra para este tipo.</span>
                                    <div v-for="r in tipo.regras" :key="r.regra_id" class="small">
                                        <i :class="r.aplica ? 'fa fa-check text-success' : 'fa fa-times text-muted'"></i>
                                        {{ r.nome }} ({{ r.periodicidade }})
                                        <div class="text-muted ml-3">
                                            <span v-for="(c, i) in r.condicoes" :key="i">
                                                {{ c.ok ? '✓' : '✗' }} {{ c.texto }}<span v-if="i < r.condicoes.length - 1">; </span>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

const API = {
    buscar: () => `${URL_ADMIN}/saude-ocupacional/regras/colaboradores`,
    simular: (id) => `${URL_ADMIN}/saude-ocupacional/regras/simular/${id}`
}

export default defineComponent({
    name: 'RegrasSimulador',

    data() {
        return {
            busca: '',
            resultados: [],
            resultado: null,
            carregando: false,
            timer: null,
            ultimoSelecionado: null
        }
    },

    beforeUnmount() {
        clearTimeout(this.timer)
    },

    methods: {
        buscarComAtraso() {
            clearTimeout(this.timer)
            this.timer = setTimeout(this.buscar, 300)
        },

        async buscar() {
            const termo = this.busca.trim()
            if (termo.length < 3) {
                this.resultados = []
                return
            }
            try {
                const { data } = await axios.get(API.buscar(), { params: { busca: termo } })
                this.resultados = data.itens || []
            } catch (e) {
                this.resultados = []
            }
        },

        async simular(item) {
            this.resultados = []
            this.busca = item.nome
            this.ultimoSelecionado = item
            this.carregando = true
            try {
                const { data } = await axios.get(API.simular(item.feedback_id))
                this.resultado = data
            } catch (e) {
                this.resultado = null
            } finally {
                this.carregando = false
            }
        },

        /** Chamado pela tela quando as regras mudam. */
        atualizar() {
            if (this.ultimoSelecionado) {
                this.simular(this.ultimoSelecionado)
            }
        }
    }
})
</script>

<style scoped>
.simulador-resultados {
    position: absolute;
    z-index: 20;
    left: 5px;
    right: 5px;
    max-height: 260px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}
</style>
