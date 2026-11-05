<template>
    <modal id="modalPcmsoCargo" ref="modal" :titulo="`Exames do cargo: ${cargo ? cargo.nome : ''}`" :size="80" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos" v-if="cargo">
                <p class="mybp-modal-legenda mb-2" v-if="cargo.riscos.length">
                    Riscos do cargo no PGR: <span v-for="r in cargo.riscos" :key="r" class="badge badge-warning mr-1">{{ r }}</span>
                </p>
                <p class="mybp-modal-legenda mb-2">O exame clínico é sempre feito. Aqui entram só os exames complementares.</p>

                <fieldset class="mybp-modal-secao" v-for="(e, i) in linhas" :key="e.chave">
                    <legend>Exame {{ i + 1 }}<span v-if="e.origem === 'sugerido'"> — sugerido pelo PGR</span></legend>
                    <div class="form-row align-items-end">
                        <div class="col-12 col-md-4 form-group">
                            <label class="mybp-label">Exame</label>
                            <select class="form-control form-control-sm" v-model.number="e.exame_id">
                                <option :value="0">Escolha</option>
                                <option v-for="op in examesDisponiveis(e)" :key="op.id" :value="op.id">{{ op.label }}</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="mybp-label">Periodicidade</label>
                            <select class="form-control form-control-sm" v-model="e.preset" @change="aplicarPreset(e)">
                                <option value="aso">Do ASO</option>
                                <option value="6m">6 meses (180 dias)</option>
                                <option value="1a">1 ano</option>
                                <option value="2a">2 anos</option>
                                <option value="d">Personalizado (dias)</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2 form-group" v-if="e.preset === 'd'">
                            <label class="mybp-label">Dias</label>
                            <input type="number" min="1" max="730" class="form-control form-control-sm" v-model.number="e.valor" />
                        </div>
                        <div class="col-6 col-md-2 form-group">
                            <label class="mybp-label font-weight-normal mb-0"><input type="checkbox" v-model="e.obrigatorio" /> Obrigatório</label>
                        </div>
                        <div class="col-6 col-md-1 form-group text-right">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Remover exame" @click="linhas.splice(i, 1)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap">
                        <label v-for="t in tipos" :key="t.id" class="mybp-label font-weight-normal mr-3 mb-0">
                            <input type="checkbox" :value="t.id" v-model="e.exame_tipo_ids" /> {{ t.label }}
                        </label>
                    </div>
                </fieldset>

                <button type="button" class="btn btn-sm btn-outline-primary" @click="adicionar">
                    <i class="fa fa-plus"></i> Adicionar exame
                </button>
                <p class="small text-muted mt-2 mb-0" v-if="!exames.length">Nenhum exame no catálogo. Cadastre em <strong>Catálogo de exames</strong>.</p>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Cancelar</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> Salvar
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

let sequencia = 0

function presetDe(unidade, valor) {
    if (!unidade) return 'aso'
    if (unidade === 'dias' && Number(valor) === 180) return '6m'
    if (unidade === 'anos' && [1, 2].includes(Number(valor))) return `${valor}a`
    return 'd'
}

function linha(dados = {}, tiposPadrao = []) {
    sequencia += 1
    return {
        chave: sequencia,
        exame_id: dados.exame_id || 0,
        exame_tipo_ids: [...(dados.exame_tipo_ids || tiposPadrao)],
        obrigatorio: dados.obrigatorio !== undefined ? !!dados.obrigatorio : true,
        unidade: dados.unidade || null,
        valor: dados.valor || null,
        preset: presetDe(dados.unidade, dados.valor),
        origem: dados.origem || 'manual',
        observacao: dados.observacao || ''
    }
}

export default defineComponent({
    name: 'PcmsoCargoModal',

    props: {
        exames: { type: Array, default: () => [] },
        tipos: { type: Array, default: () => [] }
    },

    emits: ['salvo'],

    data() {
        return { versaoId: null, cargo: null, linhas: [], salvando: false }
    },

    methods: {
        abrir(versaoId, cargo) {
            this.versaoId = versaoId
            this.cargo = cargo
            this.linhas = (cargo.exames || []).map((e) => linha(e))
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        examesDisponiveis(atual) {
            const usados = new Set(this.linhas.filter((l) => l !== atual).map((l) => l.exame_id))
            return this.exames.filter((e) => (e.ativo || e.id === atual.exame_id) && !usados.has(e.id))
        },

        adicionar() {
            // Padrão: todos os tipos exceto Demissional e Pericial; o usuário ajusta.
            const padrao = this.tipos.filter((t) => !/demissional|pericial/i.test(t.label)).map((t) => t.id)
            this.linhas.push(linha({}, padrao))
        },

        aplicarPreset(e) {
            const mapa = { aso: [null, null], '6m': ['dias', 180], '1a': ['anos', 1], '2a': ['anos', 2], d: ['dias', e.valor || 90] }
            ;[e.unidade, e.valor] = mapa[e.preset]
        },

        async salvar() {
            const exames = this.linhas
                .filter((l) => l.exame_id > 0)
                .map(({ chave, preset, ...l }) => ({ ...l, unidade: l.unidade || null, valor: l.unidade ? l.valor : null }))

            if (exames.some((e) => !e.exame_tipo_ids.length)) {
                mostraErro('', 'Marque em quais tipos de ASO cada exame é feito.')
                return
            }

            this.salvando = true
            try {
                const { data } = await axios.put(`${URL_ADMIN}/saude-ocupacional/pcmso/versoes/${this.versaoId}/cargos/${this.cargo.vaga_id}`, { exames })
                mostraSucesso('', data.mensagem)
                this.fechar()
                this.$emit('salvo')
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.salvando = false
            }
        }
    }
})
</script>
