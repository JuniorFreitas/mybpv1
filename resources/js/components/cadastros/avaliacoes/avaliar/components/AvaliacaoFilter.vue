<template>
    <fieldset>
        <legend>Filtro</legend>
        <form class="row" @submit.prevent="$emit('filtrar')">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Avaliações</label>
                    <select
                        class="form-control form-control-sm"
                        v-model="filtroDataLocal.campoAvaliacao"
                        :disabled="loading"
                        @change="$emit('filtrar')"
                    >
                        <option
                            v-for="item in avaliacoes"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ item.titulo }} - ({{ item.status }})
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Status</label>
                    <select
                        class="form-control form-control-sm"
                        v-model="filtroDataLocal.campoStatus"
                        :disabled="loading"
                        @change="$emit('filtrar')"
                    >
                        <option value="">Todos os Status</option>
                        <option
                            v-for="item in statusOptions"
                            :key="item.value"
                            :value="item.value"
                        >
                            {{ item.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button
                            type="button"
                            class="btn btn-sm mr-1 btn-success"
                            :disabled="loading"
                            @click="$emit('atualizar')"
                        >
                            <i :class="iconeBotaoAtualizar"></i>
                            Atualizar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </fieldset>
</template>

<script>
export default {
    name: 'AvaliacaoFilter',
    props: {
        loading: {
            type: Boolean,
            default: false
        },
        avaliacoes: {
            type: Array,
            required: true
        },
        statusOptions: {
            type: Array,
            required: true
        },
        filtroData: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            filtroDataLocal: { ...this.filtroData }
        }
    },
    computed: {
        iconeBotaoAtualizar() {
            return this.loading ? 'fa fa-sync fa-spin' : 'fa fa-sync'
        }
    },
    watch: {
        filtroData: {
            handler(newVal) {
                this.filtroDataLocal = { ...newVal }
            },
            deep: true
        },
        filtroDataLocal: {
            handler(newVal) {
                this.$emit('update:filtroData', newVal)
            },
            deep: true
        }
    }
}
</script>
