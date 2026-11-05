<template>
    <modal id="modalPgrCargo" ref="modal" :titulo="`Riscos do cargo: ${cargo ? cargo.nome : ''}`" :size="80" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos" v-if="cargo">
                <fieldset class="mybp-modal-secao">
                    <legend>Avaliação</legend>
                    <label class="mybp-label font-weight-normal">
                        <input type="checkbox" v-model="form.ausencia_risco" @change="form.ausencia_risco && (form.riscos = [])" />
                        Ausência de fator de risco (cargo avaliado, sem exposição)
                    </label>
                    <div class="form-group mt-2 mb-0">
                        <label class="mybp-label" for="pgr-cargo-obs">Observação</label>
                        <input id="pgr-cargo-obs" type="text" maxlength="500" class="form-control form-control-sm" v-model.trim="form.observacao" />
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao" v-if="!form.ausencia_risco">
                    <legend>Riscos</legend>
                    <p class="mybp-modal-legenda">Com ao menos um risco, o colaborador deste cargo fica <strong>exposto</strong> e o periódico passa a ser anual (NR-7).</p>

                    <div v-for="(r, i) in form.riscos" :key="r.chave" class="form-row align-items-end risco-linha">
                        <div class="col-12 col-md-3 form-group">
                            <label class="mybp-label">Risco</label>
                            <select class="form-control form-control-sm" v-model.number="r.exame_risco_id">
                                <option :value="0">Escolha</option>
                                <option v-for="op in riscosAtivos" :key="op.id" :value="op.id">{{ op.label }}{{ op.tipo ? ` (${op.tipo})` : '' }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 form-group">
                            <label class="mybp-label">Fonte geradora</label>
                            <input type="text" maxlength="200" class="form-control form-control-sm" v-model.trim="r.fonte_geradora" />
                        </div>
                        <div class="col-6 col-md-2 form-group">
                            <label class="mybp-label">Intensidade</label>
                            <input type="text" maxlength="100" class="form-control form-control-sm" v-model.trim="r.intensidade" placeholder="Ex.: 87 dB(A)" />
                        </div>
                        <div class="col-6 col-md-3 form-group">
                            <label class="mybp-label">Medidas de controle</label>
                            <input type="text" maxlength="1000" class="form-control form-control-sm" v-model.trim="r.medidas_controle" placeholder="Ex.: protetor auricular" />
                        </div>
                        <div class="col-12 col-md-1 form-group text-right">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Remover risco" @click="form.riscos.splice(i, 1)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary" @click="adicionar">
                        <i class="fa fa-plus"></i> Adicionar risco
                    </button>
                    <p class="small text-muted mt-2 mb-0" v-if="!riscosAtivos.length">
                        Nenhum risco no catálogo. Cadastre em <strong>Catálogo de riscos</strong>.
                    </p>
                </fieldset>
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
function riscoVazio(dados = {}) {
    sequencia += 1
    return {
        chave: sequencia,
        exame_risco_id: dados.exame_risco_id || 0,
        fonte_geradora: dados.fonte_geradora || '',
        intensidade: dados.intensidade || '',
        medidas_controle: dados.medidas_controle || ''
    }
}

export default defineComponent({
    name: 'PgrCargoModal',

    props: {
        riscos: { type: Array, default: () => [] }
    },

    emits: ['salvo'],

    data() {
        return {
            inventarioId: null,
            cargo: null,
            form: { ausencia_risco: false, observacao: '', riscos: [] },
            salvando: false
        }
    },

    computed: {
        riscosAtivos() {
            const usados = new Set(this.form.riscos.map((r) => r.exame_risco_id))
            return this.riscos.filter((r) => r.ativo || usados.has(r.id))
        }
    },

    methods: {
        abrir(inventarioId, cargo) {
            this.inventarioId = inventarioId
            this.cargo = cargo
            this.form = {
                ausencia_risco: !!cargo.ausencia_risco,
                observacao: cargo.observacao || '',
                riscos: (cargo.riscos || []).map((r) => riscoVazio(r))
            }
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        adicionar() {
            this.form.riscos.push(riscoVazio())
        },

        async salvar() {
            const riscos = this.form.riscos.filter((r) => r.exame_risco_id > 0)
            this.salvando = true
            try {
                const { data } = await axios.put(
                    `${URL_ADMIN}/saude-ocupacional/pgr/versoes/${this.inventarioId}/cargos/${this.cargo.vaga_id}`,
                    {
                        ausencia_risco: this.form.ausencia_risco,
                        observacao: this.form.observacao || null,
                        riscos: this.form.ausencia_risco ? [] : riscos.map(({ chave, ...r }) => r)
                    }
                )
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

<style scoped>
.risco-linha {
    border-bottom: 1px dashed #dee2e6;
    margin-bottom: 0.5rem;
}
</style>
