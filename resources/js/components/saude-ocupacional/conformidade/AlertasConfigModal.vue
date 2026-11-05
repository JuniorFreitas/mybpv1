<template>
    <modal id="modalAlertasConfig" ref="modal" titulo="Alertas de exames periódicos" :size="50" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos">
                <p class="mybp-modal-legenda">
                    O RH (quem marcou "Vencimento ASO" nas notificações) recebe um e-mail quando um ASO chega a cada aviso e no dia
                    seguinte ao vencimento. Cada aviso é enviado uma vez só.
                </p>
                <fieldset class="mybp-modal-secao">
                    <legend>Avisar quantos dias antes</legend>
                    <div class="form-row">
                        <div class="col-3" v-for="(d, i) in janelas" :key="i">
                            <input type="number" min="1" max="180" class="form-control form-control-sm" v-model.number="janelas[i]" :aria-label="`Aviso ${i + 1}`" />
                        </div>
                    </div>
                    <p class="mybp-modal-legenda mt-2 mb-0">Até 4 avisos. Deixe em branco para remover. Padrão: 60, 30 e 7 dias.</p>
                </fieldset>
                <label class="mybp-label font-weight-normal">
                    <input type="checkbox" v-model="resumoSemanal" /> Enviar resumo da semana às segundas-feiras
                </label>
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

export default defineComponent({
    name: 'AlertasConfigModal',

    emits: ['salvo'],

    data() {
        return { janelas: [60, 30, 7, null], resumoSemanal: true, salvando: false }
    },

    methods: {
        abrir(config) {
            const atuais = [...(config.janelas || [60, 30, 7])]
            while (atuais.length < 4) atuais.push(null)
            this.janelas = atuais.slice(0, 4)
            this.resumoSemanal = config.resumo_semanal !== false
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        async salvar() {
            const janelas = this.janelas.filter((d) => Number(d) > 0).map(Number)
            this.salvando = true
            try {
                const { data } = await axios.put(`${URL_ADMIN}/saude-ocupacional/conformidade/alertas`, { janelas, resumo_semanal: this.resumoSemanal })
                mostraSucesso('', data.mensagem)
                this.fechar()
                this.$emit('salvo', { janelas: data.janelas, resumo_semanal: data.resumo_semanal })
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.salvando = false
            }
        }
    }
})
</script>
