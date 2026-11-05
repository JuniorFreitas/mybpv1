<template>
    <modal id="modalPgrPublicar" ref="modal" titulo="Publicar versão do PGR" :size="60" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos" v-if="versao">
                <p class="mb-2">
                    Versão <strong>{{ versao.versao }}</strong> passa a ser a vigente. A versão atual (se houver) é arquivada com fim um dia antes.
                </p>

                <fieldset class="mybp-modal-secao">
                    <legend>Vigência</legend>
                    <datepicker id="pgr-vigencia-inicio" label="Início" formsm v-model="vigenciaInicio" :config="{ format: 'DD/MM/YYYY', useCurrent: false }" />
                </fieldset>

                <div class="alert alert-warning py-2 small" v-if="previa && previa.cargos_sem_avaliacao">
                    {{ previa.cargos_sem_avaliacao }} cargo(s) com colaboradores ativos ainda não foram avaliados nesta versão.
                    Eles ficam como <strong>não expostos</strong>.
                </div>

                <previa-impacto class="mb-3" :previa="previa" :carregando="carregandoPrevia" />
                <modo-aplicacao-campos v-model="aplicacao" id-data="pgr-data-aplicacao" />
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
import ModoAplicacaoCampos from '../regras/ModoAplicacaoCampos.vue'
import PreviaImpacto from '../regras/PreviaImpacto.vue'

export default defineComponent({
    name: 'PgrPublicarModal',

    components: { ModoAplicacaoCampos, PreviaImpacto },

    emits: ['publicado'],

    data() {
        return {
            versao: null,
            vigenciaInicio: '',
            aplicacao: { modo: 'futuro', data: '' },
            previa: null,
            carregandoPrevia: false,
            publicando: false
        }
    },

    methods: {
        async abrir(versao) {
            this.versao = versao
            this.vigenciaInicio = moment().format('DD/MM/YYYY')
            this.aplicacao = { modo: 'futuro', data: '' }
            this.previa = null
            this.$refs.modal && this.$refs.modal.abrirModal()

            this.carregandoPrevia = true
            try {
                const { data } = await axios.get(`${URL_ADMIN}/saude-ocupacional/pgr/versoes/${versao.id}/previa`)
                this.previa = data
            } catch (e) {
                this.previa = null
            } finally {
                this.carregandoPrevia = false
            }
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        async publicar() {
            if (!this.vigenciaInicio) {
                mostraErro('', 'Informe o início da vigência.')
                return
            }
            if (this.aplicacao.modo === 'a_partir_de' && !this.aplicacao.data) {
                mostraErro('', 'Informe a data de aplicação.')
                return
            }

            this.publicando = true
            try {
                const { data } = await axios.post(`${URL_ADMIN}/saude-ocupacional/pgr/versoes/${this.versao.id}/publicar`, {
                    vigencia_inicio: this.vigenciaInicio,
                    modo_aplicacao: this.aplicacao.modo,
                    data_aplicacao: this.aplicacao.data || null
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
