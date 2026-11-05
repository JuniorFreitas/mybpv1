<template>
    <modal id="modalEncaminharLote" ref="modal" titulo="Encaminhar exame em lote" :size="60" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos">
                <p class="mb-2"><strong>{{ selecionados.length }}</strong> colaborador(es) selecionado(s).</p>

                <fieldset class="mybp-modal-secao">
                    <legend>Encaminhamento</legend>
                    <div class="form-row">
                        <div class="col-12 col-md-6 form-group">
                            <label class="mybp-label" for="lote-tipo">Tipo de exame</label>
                            <select id="lote-tipo" class="form-control form-control-sm" v-model.number="form.exame_tipo_id">
                                <option :value="0">Escolha</option>
                                <option v-for="t in opcoes.tipos_exame" :key="t.id" :value="t.id">{{ t.label }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 form-group">
                            <label class="mybp-label" for="lote-pcmso">PCMSO</label>
                            <select id="lote-pcmso" class="form-control form-control-sm" v-model.number="form.pcmso_id">
                                <option :value="0">Escolha</option>
                                <option v-for="p in opcoes.pcmsos" :key="p.id" :value="p.id">{{ p.label }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 form-group">
                            <label class="mybp-label" for="lote-clinica">Clínica</label>
                            <select id="lote-clinica" class="form-control form-control-sm" v-model.number="form.empresa_exame_id">
                                <option :value="0">Escolha</option>
                                <option v-for="c in opcoes.clinicas" :key="c.id" :value="c.id">{{ c.label }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <datepicker id="lote-data" label="Data de realização" formsm v-model="form.data_realizacao" :config="{ format: 'DD/MM/YYYY', useCurrent: false }" />
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Avisos</legend>
                    <label class="mybp-label font-weight-normal d-block mb-1"><input type="checkbox" v-model="form.envia_email" /> Enviar e-mail à clínica e ao colaborador</label>
                    <label class="mybp-label font-weight-normal d-block mb-1"><input type="checkbox" v-model="form.envia_whatsapp" /> Enviar WhatsApp ao colaborador</label>
                    <label class="mybp-label font-weight-normal d-block mb-0">
                        <input type="checkbox" v-model="form.ignorar_com_encaminhamento_aberto" />
                        Pular quem já tem encaminhamento sem resultado (recomendado)
                    </label>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Cancelar</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="enviando" @click="enviar">
                <i :class="enviando ? 'fa fa-spinner fa-pulse' : 'fa fa-paper-plane'"></i> Encaminhar
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

export default defineComponent({
    name: 'EncaminharLoteModal',

    props: {
        opcoes: { type: Object, required: true }
    },

    emits: ['enviado'],

    data() {
        return {
            selecionados: [],
            enviando: false,
            form: this.formVazio()
        }
    },

    methods: {
        formVazio() {
            const periodico = (this.opcoes && this.opcoes.tipos_exame || []).find((t) => /peri[oó]dico/i.test(t.label))
            return {
                exame_tipo_id: periodico ? periodico.id : 0,
                pcmso_id: 0,
                empresa_exame_id: 0,
                data_realizacao: moment().add(7, 'days').format('DD/MM/YYYY'),
                envia_email: true,
                envia_whatsapp: false,
                ignorar_com_encaminhamento_aberto: true
            }
        },

        abrir(selecionados) {
            this.selecionados = selecionados
            this.form = this.formVazio()
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        async enviar() {
            if (!this.form.exame_tipo_id || !this.form.pcmso_id || !this.form.empresa_exame_id) {
                mostraErro('', 'Escolha tipo de exame, PCMSO e clínica.')
                return
            }
            this.enviando = true
            try {
                const { data } = await axios.post(`${URL_ADMIN}/saude-ocupacional/conformidade/encaminhar`, {
                    ...this.form,
                    feedback_ids: this.selecionados
                })
                mostraSucesso('', data.mensagem)
                this.fechar()
                this.$emit('enviado')
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.enviando = false
            }
        }
    }
})
</script>
