<template>
    <div v-if="mostrarModal">
        <modal
            id="modalTelefoneUsuario"
            ref="modalTelefoneUsuario"
            titulo="Atualize seu telefone de contato"
            size="g"
            :fechar="false"
            :mostrar-botao-fechar-no-rodape="false"
            @fechou="onFechou"
        >
            <template #conteudo>
                <div v-if="loading" class="text-center py-4">
                    <preload />
                </div>
                <div v-else>
                    <div class="alert alert-warning" v-if="mensagem">
                        <i class="fa fa-exclamation-triangle"></i> {{ mensagem }}
                    </div>
                    <div class="alert alert-info">
                        <i class="fab fa-whatsapp text-success"></i>
                        O ideal é cadastrar um telefone do tipo <strong>WhatsApp</strong> e marcá-lo como
                        <strong>Principal</strong> para receber as notificações do sistema via WhatsApp.
                    </div>
                    <fieldset>
                        <legend>Telefones</legend>
                        <telefone
                            :model="form.telefones"
                            :model-delete="form.telefonesDelete"
                            :pais="false"
                            :ramal="false"
                            :detalhe="false"
                            :qnt_min="1"
                        />
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-if="!loading"
                    :disabled="salvando"
                    @click="salvar"
                >
                    <i v-if="salvando" class="fa fa-spinner fa-spin"></i>
                    Salvar
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import telefone from './Telefones'

const telefonePadrao = () => ({
    id: 0,
    nova: true,
    tipo: 'whatsapp',
    pais: '55',
    numero: '',
    ramal: '',
    detalhe: '',
    principal: true
})

export default {
    name: 'TelefoneUsuarioModal',

    components: {
        telefone
    },

    data() {
        return {
            mostrarModal: false,
            loading: true,
            salvando: false,
            mensagem: '',
            form: {
                telefones: [telefonePadrao()],
                telefonesDelete: []
            }
        }
    },

    mounted() {
        this.carregar()
        window.addEventListener('mybp:termos-aceitos', this.carregar)
    },

    beforeUnmount() {
        window.removeEventListener('mybp:termos-aceitos', this.carregar)
    },

    methods: {
        carregar() {
            this.loading = true
            axios
                .get(`${window.URL_ADMIN}/usuario/telefone/deve-atualizar`)
                .then((res) => {
                    const data = res.data || {}
                    if (!data.mostrar) {
                        return
                    }

                    this.mensagem = data.mensagem || ''
                    const telefones = Array.isArray(data.telefones) && data.telefones.length > 0
                        ? data.telefones
                        : [telefonePadrao()]

                    this.form.telefones = telefones
                    this.form.telefonesDelete = []
                    this.mostrarModal = true

                    this.$nextTick(() => {
                        this.$refs.modalTelefoneUsuario?.abrirModal?.()
                    })
                })
                .catch(() => {})
                .finally(() => {
                    this.loading = false
                })
        },

        validarFormulario() {
            const telefonesValidos = (this.form.telefones || []).filter(
                (tel) => (tel.numero || '').trim() !== ''
            )

            if (telefonesValidos.length === 0) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'Informe pelo menos um telefone')
                }
                return false
            }

            const temPrincipal = telefonesValidos.some((tel) => tel.principal === true || tel.principal === 'true')
            if (!temPrincipal) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'Marque um telefone como principal')
                }
                return false
            }

            if (typeof formReset === 'function') {
                formReset()
            }

            const inputs = document.querySelectorAll('#modalTelefoneUsuario input.telefone:not([disabled])')
            inputs.forEach((el) => el.dispatchEvent(new Event('blur', { bubbles: true })))

            const invalidos = document.querySelectorAll('#modalTelefoneUsuario .is-invalid')
            if (invalidos.length > 0) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'Verifique os telefones informados')
                }
                return false
            }

            return true
        },

        salvar() {
            if (!this.validarFormulario() || this.salvando) {
                return
            }

            this.salvando = true
            axios
                .put(`${window.URL_ADMIN}/usuario/telefone`, {
                    telefones: this.form.telefones,
                    telefonesDelete: this.form.telefonesDelete
                })
                .then(() => {
                    this.$refs.modalTelefoneUsuario?.fecharModal?.()
                    if (typeof mostraSucesso === 'function') {
                        mostraSucesso('', 'Telefone atualizado com sucesso!')
                    }
                })
                .catch((err) => {
                    const msg = err.response?.data?.msg || 'Erro ao salvar telefone. Tente novamente.'
                    if (typeof mostraErro === 'function') {
                        mostraErro('', msg)
                    }
                })
                .finally(() => {
                    this.salvando = false
                })
        },

        onFechou() {
            this.mostrarModal = false
        }
    }
}
</script>
