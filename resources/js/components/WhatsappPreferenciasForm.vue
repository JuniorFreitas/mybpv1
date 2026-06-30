<template>
    <fieldset v-if="visivel">
        <legend>Notificações WhatsApp</legend>
        <div class="alert alert-warning py-2 mb-2" v-if="!whatsappLiberado">
            O WhatsApp não está habilitado para esta empresa.
        </div>
        <template v-else>
            <p class="text-muted small mb-2">
                {{ descricao }}
            </p>
            <div
                v-for="item in preferencias"
                :key="item.modulo"
                class="custom-control custom-switch mb-2"
            >
                <input
                    :id="`${inputPrefix}-${slug(item.modulo)}`"
                    type="checkbox"
                    class="custom-control-input"
                    :checked="item.receber"
                    :disabled="disabled || !item.habilitado_empresa"
                    @change="onToggle(item, $event)"
                >
                <label class="custom-control-label" :for="`${inputPrefix}-${slug(item.modulo)}`">
                    {{ item.modulo }}
                    <small v-if="!item.habilitado_empresa" class="text-muted">(desabilitado pela empresa)</small>
                </label>
            </div>
        </template>
    </fieldset>
</template>

<script>
export default {
    name: 'WhatsappPreferenciasForm',

    props: {
        preferencias: { type: Array, default: () => [] },
        whatsappLiberado: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false },
        inputPrefix: { type: String, default: 'whatsapp-pref' },
        descricao: {
            type: String,
            default: 'Escolha quais notificações WhatsApp este usuário deve receber.',
        },
        ocultarQuandoVazio: { type: Boolean, default: false },
    },

    emits: ['update:preferencias'],

    computed: {
        visivel() {
            if (!this.ocultarQuandoVazio) {
                return true
            }

            return this.preferencias.length > 0
        },
    },

    methods: {
        slug(texto) {
            return String(texto).toLowerCase().replace(/[^a-z0-9]+/g, '-')
        },

        onToggle(item, event) {
            const atualizadas = this.preferencias.map((pref) => {
                if (pref.modulo !== item.modulo) {
                    return { ...pref }
                }

                return {
                    ...pref,
                    receber: event.target.checked,
                }
            })

            this.$emit('update:preferencias', atualizadas)
        },
    },
}
</script>
