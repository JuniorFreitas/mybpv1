<template>
    <modal id="modalEmitirAso" ref="modal" titulo="Emitir ASO (NR-7)" :size="60" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos">
                <p v-if="carregando" class="mb-0"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>
                <template v-else-if="prep">
                    <p class="small mb-2" v-if="item">
                        <strong>{{ item.tipo }}</strong> realizado em {{ item.realizado_em_br }} — conclusão: <strong>{{ item.conclusao || 'não informada' }}</strong>
                    </p>
                    <div class="alert alert-warning py-1 small" v-if="prep.emitido">
                        Já emitido em {{ prep.emitido.emitido_em_br }}. Emitir de novo substitui o documento (só antes de enviar para assinatura).
                    </div>

                    <fieldset class="mybp-modal-secao">
                        <legend>Médico examinador</legend>
                        <select class="form-control form-control-sm" v-model.number="form.saude_medico_id" aria-label="Médico examinador">
                            <option :value="null">Escolha</option>
                            <option v-for="m in prep.examinadores" :key="m.value" :value="m.value">
                                {{ m.label }}{{ m.da_clinica ? ' — da clínica do encaminhamento' : '' }}{{ m.tem_email ? '' : ' (sem e-mail)' }}
                            </option>
                        </select>
                        <p class="mybp-modal-legenda mb-0 mt-1" v-if="!prep.examinadores.length">
                            Nenhum médico cadastrado. Cadastre em Configurações → Médicos responsáveis.
                        </p>
                        <p class="mybp-modal-legenda mb-0 mt-1">
                            Responsável pelo PCMSO na data: {{ prep.coordenador ? `${prep.coordenador.nome} (${prep.coordenador.crm_completo})` : 'não cadastrado' }}
                        </p>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Riscos (PGR)</legend>
                        <span v-for="r in prep.riscos" :key="r" class="badge badge-warning mr-1">{{ r }}</span>
                        <span v-if="!prep.riscos.length" class="small text-muted">Sem riscos no PGR vigente para o cargo — o ASO informa ausência de fatores de risco.</span>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Exames complementares realizados</legend>
                        <p class="mybp-modal-legenda">O exame clínico entra sempre. Sugestão vinda do PCMSO; ajuste para o que foi feito.</p>
                        <div v-for="(e, i) in form.exames" :key="i" class="form-row align-items-end mb-1">
                            <div class="col-7"><input type="text" maxlength="120" class="form-control form-control-sm" v-model.trim="e.label" placeholder="Exame" :aria-label="`Exame ${i + 1}`" /></div>
                            <div class="col-4"><input type="text" maxlength="10" class="form-control form-control-sm" v-model.trim="e.data" placeholder="dd/mm/aaaa" :aria-label="`Data do exame ${i + 1}`" /></div>
                            <div class="col-1 text-right">
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Remover" @click="form.exames.splice(i, 1)"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" @click="form.exames.push({ label: '', data: item ? item.realizado_em_br : '' })">
                            <i class="fa fa-plus"></i> Adicionar exame
                        </button>
                    </fieldset>
                </template>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Cancelar</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="salvando || carregando" @click="emitir">
                <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> Emitir ASO
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/historico-aso/resultados`

export default defineComponent({
    name: 'EmitirAsoModal',

    emits: ['emitido'],

    data() {
        return { item: null, prep: null, carregando: false, salvando: false, form: { saude_medico_id: null, exames: [] } }
    },

    methods: {
        async abrir(item) {
            this.item = item
            this.prep = null
            this.$refs.modal && this.$refs.modal.abrirModal()
            this.carregando = true
            try {
                const { data } = await axios.get(`${BASE()}/${item.resultado_id}/preparar`)
                this.prep = data
                this.form = { saude_medico_id: data.saude_medico_id, exames: (data.exames || []).map((e) => ({ ...e })) }
            } catch (e) {
                this.fechar()
            } finally {
                this.carregando = false
            }
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        async emitir() {
            if (!this.form.saude_medico_id) {
                mostraErro('', 'Escolha o médico examinador.')
                return
            }
            this.salvando = true
            try {
                const { data } = await axios.post(`${BASE()}/${this.item.resultado_id}/emitir`, {
                    saude_medico_id: this.form.saude_medico_id,
                    exames: this.form.exames.filter((e) => e.label)
                })
                mostraSucesso('', data.mensagem)
                this.fechar()
                this.$emit('emitido')
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.salvando = false
            }
        }
    }
})
</script>
