<template>
    <modal id="modalExamesPorRisco" ref="modal" titulo="Exames por risco" :size="70" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos">
                <p class="mybp-modal-legenda">
                    Para cada risco do PGR, quais exames complementares fazer. O botão <strong>Sugerir pelo PGR</strong> usa este mapa
                    para preencher os exames de cada cargo exposto.
                </p>
                <div class="form-group">
                    <label class="mybp-label" for="risco-mapa">Risco</label>
                    <select id="risco-mapa" class="form-control form-control-sm" v-model.number="riscoId" @change="carregarRisco">
                        <option :value="0">Escolha</option>
                        <option v-for="r in riscos" :key="r.id" :value="r.id">{{ r.label }}{{ r.tipo ? ` (${r.tipo})` : '' }} — {{ (examesPorRisco[r.id] || []).length }} exame(s)</option>
                    </select>
                </div>

                <template v-if="riscoId">
                    <fieldset class="mybp-modal-secao" v-for="(l, i) in linhas" :key="l.chave">
                        <legend>Exame {{ i + 1 }}</legend>
                        <div class="form-row align-items-end">
                            <div class="col-10 col-md-6 form-group">
                                <select class="form-control form-control-sm" v-model.number="l.exame_id">
                                    <option :value="0">Escolha</option>
                                    <option v-for="e in examesAtivos" :key="e.id" :value="e.id">{{ e.label }}</option>
                                </select>
                            </div>
                            <div class="col-2 col-md-6 form-group text-right">
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Remover" @click="linhas.splice(i, 1)"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap">
                            <label v-for="t in tipos" :key="t.id" class="mybp-label font-weight-normal mr-3 mb-0">
                                <input type="checkbox" :value="t.id" v-model="l.exame_tipo_ids" /> {{ t.label }}
                            </label>
                        </div>
                    </fieldset>
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="adicionar"><i class="fa fa-plus"></i> Adicionar exame</button>
                </template>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Fechar</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="!riscoId || salvando" @click="salvar">
                <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> Salvar exames do risco
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

let sequencia = 0
const linha = (dados = {}) => ({ chave: ++sequencia, exame_id: dados.exame_id || 0, exame_tipo_ids: [...(dados.exame_tipo_ids || [])] })

export default defineComponent({
    name: 'ExamesPorRiscoModal',

    props: {
        riscos: { type: Array, default: () => [] },
        exames: { type: Array, default: () => [] },
        tipos: { type: Array, default: () => [] },
        examesPorRisco: { type: Object, default: () => ({}) }
    },

    emits: ['alterado'],

    data() {
        return { riscoId: 0, linhas: [], salvando: false }
    },

    computed: {
        examesAtivos() {
            return this.exames.filter((e) => e.ativo)
        }
    },

    methods: {
        abrir() {
            this.riscoId = 0
            this.linhas = []
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        carregarRisco() {
            this.linhas = (this.examesPorRisco[this.riscoId] || []).map((l) => linha(l))
        },

        adicionar() {
            const padrao = this.tipos.filter((t) => !/pericial/i.test(t.label)).map((t) => t.id)
            this.linhas.push(linha({ exame_tipo_ids: padrao }))
        },

        async salvar() {
            const exames = this.linhas.filter((l) => l.exame_id > 0).map(({ chave, ...l }) => l)
            this.salvando = true
            try {
                const { data } = await axios.put(`${URL_ADMIN}/saude-ocupacional/pcmso/riscos/${this.riscoId}/exames`, { exames })
                mostraSucesso('', data.mensagem)
                this.$emit('alterado')
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.salvando = false
            }
        }
    }
})
</script>
