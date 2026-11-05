<template>
    <modal id="modalRegraSaudeAcao" ref="modal" :titulo="titulo" :size="50" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos" v-if="regra">
                <p class="mb-2">
                    <strong>{{ regra.nome }}</strong><br />
                    <span class="text-muted small">{{ regra.condicoes_texto }} → {{ regra.periodicidade_texto }}</span>
                </p>
                <p class="small mb-3" v-if="acao === 'excluir'">
                    A regra sai da lista, mas fica no histórico.
                </p>
                <previa-impacto class="mb-3" :previa="previa" :carregando="carregandoPrevia" />
                <modo-aplicacao-campos v-model="aplicacao" id-data="regra-acao-data" />
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Cancelar</button>
            <button type="button" class="btn btn-sm" :class="acao === 'excluir' ? 'btn-danger' : 'btn-primary'" :disabled="executando" @click="confirmar">
                <i :class="executando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> {{ textoBotao }}
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import ModoAplicacaoCampos from './ModoAplicacaoCampos.vue'
import PreviaImpacto from './PreviaImpacto.vue'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/regras`

export default defineComponent({
    name: 'RegraAcaoModal',

    components: { ModoAplicacaoCampos, PreviaImpacto },

    emits: ['concluido'],

    data() {
        return {
            regra: null,
            acao: 'desativar', // ativar | desativar | excluir
            aplicacao: { modo: 'futuro', data: '' },
            previa: null,
            carregandoPrevia: false,
            executando: false
        }
    },

    computed: {
        titulo() {
            return { ativar: 'Ativar regra', desativar: 'Desativar regra', excluir: 'Excluir regra' }[this.acao]
        },
        textoBotao() {
            return { ativar: 'Ativar', desativar: 'Desativar', excluir: 'Excluir' }[this.acao]
        }
    },

    methods: {
        async abrir(regra, acao) {
            this.regra = regra
            this.acao = acao
            this.aplicacao = { modo: 'futuro', data: '' }
            this.previa = null
            this.$refs.modal && this.$refs.modal.abrirModal()

            this.carregandoPrevia = true
            try {
                const { data } = acao === 'ativar'
                    ? await axios.post(`${BASE()}/previa`, {
                        regra_id: regra.id,
                        nome: regra.nome,
                        exame_tipo_ids: regra.exame_tipo_ids,
                        condicoes: regra.condicoes,
                        unidade: regra.unidade,
                        valor: regra.valor
                    })
                    : await axios.get(`${BASE()}/${regra.id}/previa-remocao`)
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

        async confirmar() {
            if (this.aplicacao.modo === 'a_partir_de' && !this.aplicacao.data) {
                mostraErro('', 'Informe a data de aplicação.')
                return
            }

            const corpo = { modo_aplicacao: this.aplicacao.modo, data_aplicacao: this.aplicacao.data || null }
            this.executando = true
            try {
                const { data } = this.acao === 'excluir'
                    ? await axios.delete(`${BASE()}/${this.regra.id}`, { data: corpo })
                    : await axios.patch(`${BASE()}/${this.regra.id}/ativo`, { ...corpo, ativo: this.acao === 'ativar' })
                mostraSucesso('', data.mensagem || 'Pronto.')
                this.fechar()
                this.$emit('concluido')
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.executando = false
            }
        }
    }
})
</script>
