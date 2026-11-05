<template>
    <div id="componenteAsoPeriodicidade" class="mybp-listagem-ui">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <h3 class="card-title mb-0">Periodicidade por tipo de exame</h3>
                <div>
                    <button type="button" class="btn btn-sm btn-success mr-1" :disabled="carregando" @click="carregar">
                        <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" :disabled="carregando || salvando" @click="abrirModalAplicacao">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Configure a validade do ASO por tipo. Em <strong>anos</strong>, o vencimento é realização + N anos − 1 dia.
                    Em <strong>dias</strong>, a soma é exata.
                </p>

                <p class="text-center" v-if="carregando"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

                <div v-if="!carregando" class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Tipo de exame</th>
                                <th style="width: 220px">Preset</th>
                                <th style="width: 140px">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in itens" :key="item.exame_tipo_id">
                                <td>{{ item.label }}</td>
                                <td colspan="2" v-if="item.sem_vencimento">
                                    <span class="text-muted">Sem vencimento</span>
                                </td>
                                <template v-else>
                                    <td>
                                        <select class="form-control form-control-sm" v-model="item.preset" @change="onPresetChange(item)">
                                            <option value="1">1 ano</option>
                                            <option value="2">2 anos</option>
                                            <option value="3">3 anos</option>
                                            <option value="custom">Personalizado (dias)</option>
                                        </select>
                                    </td>
                                    <td>
                                        <template v-if="item.preset === 'custom'">
                                            <input
                                                type="number"
                                                min="1"
                                                max="3650"
                                                class="form-control form-control-sm"
                                                v-model.number="item.valor"
                                            />
                                        </template>
                                        <span v-else class="text-muted">{{ item.valor }} ano(s)</span>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card card-outline card-secondary mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Histórico</h3>
                <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="carregandoHistorico" @click="carregarHistorico">
                    <i :class="carregandoHistorico ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                </button>
            </div>
            <div class="card-body">
                <p class="text-center" v-if="carregandoHistorico"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>
                <div v-else-if="!historico.length" class="text-muted">Nenhuma alteração registrada.</div>
                <div v-else class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Quando</th>
                                <th>Usuário</th>
                                <th>Modo</th>
                                <th>O que mudou</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="h in historico" :key="h.id">
                                <td class="text-nowrap">{{ h.created_at_br || h.created_at }}</td>
                                <td>{{ (h.usuario && h.usuario.nome) || '—' }}</td>
                                <td>
                                    {{ labelModo(h.modo_aplicacao) }}
                                    <span v-if="h.data_aplicacao_br"> ({{ h.data_aplicacao_br }})</span>
                                    <span v-if="h.job_despachado" class="badge badge-info ml-1">job</span>
                                </td>
                                <td>{{ h.descricao }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <modal
            id="modalAplicacaoPeriodicidadeAso"
            ref="modalAplicacao"
            titulo="Como aplicar a alteração?"
            :size="50"
            :mostrar-botao-fechar-no-rodape="false"
        >
            <template #conteudo>
                <div class="mybp-modal-form mybp-filtros-compactos">
                    <fieldset class="mybp-modal-secao">
                        <div class="form-group">
                            <label class="mybp-label">
                                <input type="radio" value="futuro" v-model="modoAplicacao" />
                                Somente futuros (não recalcula ASOs já cadastrados)
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="mybp-label">
                                <input type="radio" value="retroativo" v-model="modoAplicacao" />
                                Retroativo (recalcula todos os ASOs atuais)
                            </label>
                        </div>
                        <div class="form-group mb-2">
                            <label class="mybp-label">
                                <input type="radio" value="a_partir_de" v-model="modoAplicacao" />
                                A partir de uma data
                            </label>
                        </div>
                        <div class="form-group" v-if="modoAplicacao === 'a_partir_de'">
                            <label class="mybp-label" for="data-aplicacao-aso">Data de aplicação</label>
                            <datepicker
                                id="data-aplicacao-aso"
                                v-model="dataAplicacao"
                                :config="{ format: 'DD/MM/YYYY', useCurrent: false }"
                            />
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="fecharModalAplicacao">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                    <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> Confirmar
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

const API = {
    listar: () => `${URL_ADMIN}/cadastro/aso-periodicidade/listar`,
    historico: () => `${URL_ADMIN}/cadastro/aso-periodicidade/historico`,
    salvar: () => `${URL_ADMIN}/cadastro/aso-periodicidade`
}

function presetFromItem(item) {
    if (item.unidade === 'anos' && [1, 2, 3].includes(Number(item.valor))) {
        return String(item.valor)
    }
    return 'custom'
}

export default defineComponent({
    name: 'AsoPeriodicidade',

    data() {
        return {
            carregando: false,
            carregandoHistorico: false,
            salvando: false,
            itens: [],
            historico: [],
            modoAplicacao: 'futuro',
            dataAplicacao: ''
        }
    },

    mounted() {
        this.carregar()
        this.carregarHistorico()
    },

    methods: {
        onPresetChange(item) {
            if (item.preset === 'custom') {
                item.unidade = 'dias'
                if (!item.valor || item.valor < 1) item.valor = 365
                return
            }
            item.unidade = 'anos'
            item.valor = Number(item.preset)
        },

        labelModo(modo) {
            if (modo === 'retroativo') return 'Retroativo'
            if (modo === 'a_partir_de') return 'A partir de'
            if (modo === 'futuro') return 'Somente futuros'
            return modo || '—'
        },

        async carregar() {
            this.carregando = true
            try {
                const { data } = await axios.get(API.listar())
                this.itens = (data.itens || []).map((item) => ({
                    ...item,
                    preset: item.sem_vencimento ? null : presetFromItem(item)
                }))
            } catch (e) {
                mostraErro('', 'Não foi possível carregar as periodicidades.')
            } finally {
                this.carregando = false
            }
        },

        async carregarHistorico() {
            this.carregandoHistorico = true
            try {
                const { data } = await axios.get(API.historico(), { params: { pages: 20 } })
                this.historico = data.data || data || []
            } catch (e) {
                this.historico = []
            } finally {
                this.carregandoHistorico = false
            }
        },

        abrirModalAplicacao() {
            this.modoAplicacao = 'futuro'
            this.dataAplicacao = ''
            this.$refs.modalAplicacao && this.$refs.modalAplicacao.abrirModal()
        },

        fecharModalAplicacao() {
            this.$refs.modalAplicacao && this.$refs.modalAplicacao.fecharModal()
        },

        payloadItens() {
            return this.itens
                .filter((item) => !item.sem_vencimento)
                .map((item) => {
                    if (item.preset === 'custom') {
                        return {
                            exame_tipo_id: item.exame_tipo_id,
                            unidade: 'dias',
                            valor: Number(item.valor) || 1
                        }
                    }
                    return {
                        exame_tipo_id: item.exame_tipo_id,
                        unidade: 'anos',
                        valor: Number(item.preset)
                    }
                })
        },

        async salvar() {
            if (this.modoAplicacao === 'a_partir_de' && (!this.dataAplicacao || String(this.dataAplicacao).length < 10)) {
                mostraErro('', 'Informe a data de aplicação.')
                return
            }

            this.salvando = true
            try {
                const { data } = await axios.post(API.salvar(), {
                    itens: this.payloadItens(),
                    modo_aplicacao: this.modoAplicacao,
                    data_aplicacao: this.modoAplicacao === 'a_partir_de' ? this.dataAplicacao : null
                })
                mostraSucesso('', data.mensagem || 'Periodicidade salva.')
                this.fecharModalAplicacao()
                await this.carregar()
                await this.carregarHistorico()
            } catch (e) {
                const msg = (e.response && e.response.data && e.response.data.msg) || 'Não foi possível salvar.'
                mostraErro('', msg)
            } finally {
                this.salvando = false
            }
        }
    }
})
</script>
