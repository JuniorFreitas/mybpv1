<template>
    <div>
        <fieldset v-for="item in topicos" :key="item.id">
            <legend>{{ item.topico }}</legend>
            <div class="alert alert-info" v-if="item.topico_explicacao">
                {{ item.topico_explicacao }}
            </div>

            <fieldset v-for="(subtopico, index) in item.subtopicos" :key="subtopico.id || index">
                <legend>{{ subtopico.topico }}</legend>
                <p class="quebra_linha_textarea">{{ subtopico.topico_explicacao }}</p>

                <div class="form-group">
                    <label>{{ visualizando ? 'Nota' : 'Informe sua nota' }}</label>
                    <select
                        :disabled="visualizando"
                        class="form-control validacampo"
                        @blur.prevent="validarCampo($event.target)"
                        @change.prevent="validarCampo($event.target)"
                        v-model="respostas[item.id][index].nota"
                    >
                        <option value="">Selecione</option>
                        <option v-for="resp in 5" :value="resp" :key="resp">{{ resp }}</option>
                    </select>
                </div>

                <h5 v-if="principal && respostasFunc[item.id] && respostasFunc[item.id][index]">
                    Nota do colaborador: {{ respostasFunc[item.id][index].nota }}
                </h5>
            </fieldset>
        </fieldset>
    </div>
</template>

<script>
export default {
    name: 'TopicosAvaliacao',
    props: {
        topicos: {
            type: Array,
            default: () => []
        },
        respostas: {
            type: Array,
            default: () => []
        },
        respostasFunc: {
            type: Array,
            default: () => []
        },
        principal: {
            type: Boolean,
            default: false
        },
        visualizando: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        validarCampo(target) {
            if (typeof valida_campo_vazio === 'function') {
                valida_campo_vazio(target, 1)
            }
        }
    }
}
</script>
