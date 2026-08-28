<!--<bt-ativo :rota="`tipo-imoveis/${tipo.id}/ativa-desativa`" :model="tipo"></bt-ativo>-->
<template>
    <div>
        <button class="btn btn-sm mr-1 btnFormAlterar btn-success" @click.prevent="ativaDesativa()" v-if="isAtivo" :disabled="model.preload">
            <span class="fas fa-check" aria-hidden="true" v-if="!model.preload"></span>
            <span class="fas fa-redo fa-spin" aria-hidden="true" v-if="model.preload"></span>
            {{ ativoLabel }}
        </button>
        <button class="btn btn-sm mr-1 btnFormAlterar btn-danger" @click.prevent="ativaDesativa()" v-if="!isAtivo" :disabled="model.preload">
            <span class="fas fa-times" aria-hidden="true" v-if="!model.preload"></span>
            <span class="fas fa-redo fa-spin" aria-hidden="true" v-if="model.preload"></span>
            {{ inativoLabel }}
        </button>
    </div>
</template>

<script>
export default {
    props: {
        rota: {
            type: String,
            required: true,
            default: () => ''
        },
        ativoLabel: {
            type: String,
            required: false,
            default: () => 'Ativo'
        },
        inativoLabel: {
            type: String,
            required: false,
            default: () => 'Inativo'
        },
        model: {
            type: Object,
            required: true,
            default: () => {}
        },
        campo: {
            type: String,
            required: false,
            default: () => 'ativo'
        }
    },

    computed: {
        isAtivo() {
            return !!this.model?.[this.campo]
        }
    },

    methods: {
        ativaDesativa: function () {
            this.model.preload = true
            this.model[this.campo] = !this.model[this.campo]

            axios
                .put(`${URL_ADMIN}/${this.rota}`, { id: this.model.id })
                .then((response) => {
                    this.model.preload = false
                    if (Object.prototype.hasOwnProperty.call(response.data, this.campo)) {
                        this.model[this.campo] = response.data[this.campo]
                    }
                    this.$emit('atualizou', true)
                })
                .catch((error) => {
                    this.model.preload = false
                    this.model[this.campo] = !this.model[this.campo]
                })
        }
    }
}
</script>

<style scoped></style>
