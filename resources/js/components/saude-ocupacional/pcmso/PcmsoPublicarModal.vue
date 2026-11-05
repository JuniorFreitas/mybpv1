<template>
    <modal id="modalPcmsoPublicar" ref="modal" titulo="Publicar versão do PCMSO" :size="50" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos" v-if="versao">
                <p class="mb-2">
                    Versão <strong>{{ versao.versao }}</strong> passa a valer. A versão vigente (se houver) é arquivada com fim um dia antes do início.
                </p>
                <p class="small mb-2" :class="versao.coordenador ? 'text-muted' : 'text-danger'">
                    Médico coordenador: {{ versao.coordenador || 'não definido — escolha antes de publicar (NR-7)' }}
                </p>
                <fieldset class="mybp-modal-secao">
                    <legend>Vigência</legend>
                    <div class="form-row">
                        <div class="col-6">
                            <datepicker id="pcmso-vigencia-inicio" label="Início" formsm v-model="inicio" :config="{ format: 'DD/MM/YYYY', useCurrent: false }" />
                        </div>
                        <div class="col-6">
                            <datepicker id="pcmso-vigencia-fim" label="Fim (opcional)" formsm v-model="fim" :config="{ format: 'DD/MM/YYYY', useCurrent: false }" />
                        </div>
                    </div>
                    <p class="mybp-modal-legenda mb-0">Sugestão: revisar o PCMSO a cada ano (fim = início + 1 ano − 1 dia).</p>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Cancelar</button>
            <button type="button" class="btn btn-sm btn-success" :disabled="publicando" @click="publicar">
                <i :class="publicando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> Publicar
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

export default defineComponent({
    name: 'PcmsoPublicarModal',

    emits: ['publicado'],

    data() {
        return { versao: null, inicio: '', fim: '', publicando: false }
    },

    methods: {
        abrir(versao) {
            this.versao = versao
            this.inicio = moment().format('DD/MM/YYYY')
            this.fim = moment().add(1, 'year').subtract(1, 'day').format('DD/MM/YYYY')
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        async publicar() {
            this.publicando = true
            try {
                const { data } = await axios.post(`${URL_ADMIN}/saude-ocupacional/pcmso/versoes/${this.versao.id}/publicar`, {
                    vigencia_inicio: this.inicio,
                    vigencia_fim: this.fim || null
                })
                mostraSucesso('', data.mensagem)
                this.fechar()
                this.$emit('publicado')
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.publicando = false
            }
        }
    }
})
</script>
