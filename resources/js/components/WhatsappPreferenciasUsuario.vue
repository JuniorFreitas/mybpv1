<template>
    <div v-if="visivel" class="card mb-3">
        <div class="card-header py-2">
            <strong><i class="fab fa-whatsapp text-success"></i> Notificações WhatsApp</strong>
        </div>
        <div class="card-body">
            <preload v-if="loading" class="text-center" />
            <div v-else>
                <whatsapp-preferencias-form
                    :preferencias="preferencias"
                    :whatsapp-liberado="whatsappLiberado"
                    input-prefix="dashboard-whatsapp-pref"
                    descricao="Escolha quais tipos de notificação deseja receber no seu WhatsApp cadastrado."
                    @update:preferencias="preferencias = $event"
                />
                <button
                    v-if="whatsappLiberado && preferencias.length"
                    type="button"
                    class="btn btn-success btn-sm mt-2"
                    :disabled="salvando"
                    @click="salvar"
                >
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i>
                    Salvar preferências
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import WhatsappPreferenciasForm from './WhatsappPreferenciasForm.vue'

export default {
    name: 'WhatsappPreferenciasUsuario',

    components: { WhatsappPreferenciasForm },

    data() {
        return {
            visivel: false,
            loading: true,
            salvando: false,
            whatsappLiberado: false,
            preferencias: [],
        }
    },

    mounted() {
        this.carregar()
    },

    methods: {
        carregar() {
            this.loading = true
            axios
                .get(`${window.URL_ADMIN}/usuario/whatsapp-preferencias`)
                .then((res) => {
                    this.visivel = true
                    this.whatsappLiberado = !!res.data.whatsapp_liberado
                    this.preferencias = this.normalizarPreferencias(res.data.preferencias || [])
                })
                .catch(() => {
                    this.visivel = false
                })
                .finally(() => {
                    this.loading = false
                })
        },

        normalizarPreferencias(preferencias) {
            return preferencias.map((item) => ({
                ...item,
                receber: !!item.receber,
            }))
        },

        salvar() {
            if (this.salvando) return

            this.salvando = true
            axios
                .put(`${window.URL_ADMIN}/usuario/whatsapp-preferencias`, {
                    preferencias: this.preferencias.map((item) => ({
                        modulo: item.modulo,
                        receber: !!item.receber,
                    })),
                })
                .then((res) => {
                    this.preferencias = this.normalizarPreferencias(res.data.preferencias || [])
                    if (typeof mostraSucesso === 'function') {
                        mostraSucesso('', 'Preferências salvas com sucesso!')
                    }
                })
                .catch((err) => {
                    const msg = err.response?.data?.message || 'Erro ao salvar preferências.'
                    if (typeof mostraErro === 'function') {
                        mostraErro('', msg)
                    }
                })
                .finally(() => {
                    this.salvando = false
                })
        },
    },
}
</script>
