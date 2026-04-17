<template>
    <fieldset>
        <template v-if="!visualizando">
            <legend>MINHAS CONSIDERAÇÕES</legend>
            <textarea
                :value="modelValue"
                @input="emitUpdate($event.target.value)"
                class="form-control"
                @blur.prevent="validarCampo($event.target)"
                @change.prevent="validarCampo($event.target)"
                placeholder="Se desejar, faça considerações"
                rows="4"
            ></textarea>

            <h5 class="mt-3" v-if="principal && comentarioFuncionario">Considerações do colaborador: {{ comentarioFuncionario }}</h5>
        </template>

        <div v-else-if="painelVisualizacaoVisivel" class="ca-ref-panel">
            <div class="ca-ref-panel__head">
                <i class="fa fa-comments" aria-hidden="true"></i>
                <span>Referência: comentários já enviados</span>
            </div>
            <div v-if="principal && comentarioFuncionario" class="ca-ref-coment">
                <div class="ca-ref-coment__label"><i class="fa fa-user mr-1 text-primary"></i>Colaborador</div>
                <p class="ca-ref-coment__text">{{ comentarioFuncionario }}</p>
            </div>
            <div v-if="temComentarioProprio" class="ca-ref-coment">
                <div class="ca-ref-coment__label"><i class="fa fa-pen mr-1 text-primary"></i>Minhas considerações</div>
                <p class="ca-ref-coment__text">{{ modelValue }}</p>
            </div>
        </div>
    </fieldset>
</template>

<script>
export default {
    name: 'ConsideracoesAvaliacao',
    emits: ['update:modelValue', 'input'],
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        comentarioFuncionario: {
            type: String,
            default: ''
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
    computed: {
        temComentarioProprio() {
            return this.modelValue != null && String(this.modelValue).trim() !== ''
        },
        painelVisualizacaoVisivel() {
            return this.temComentarioProprio || (this.principal && Boolean(this.comentarioFuncionario))
        }
    },
    methods: {
        emitUpdate(value) {
            this.$emit('update:modelValue', value)
            this.$emit('input', value)
        },
        validarCampo(target) {
            if (typeof valida_campo_vazio === 'function') {
                valida_campo_vazio(target, 1)
            }
        }
    }
}
</script>

<style scoped>
.ca-ref-panel {
    margin-top: 0;
    padding: 0.85rem 1rem;
    border-radius: 10px;
    background: linear-gradient(135deg, #f3f7fb 0%, #ffffff 60%, #f9fbfd 100%);
    border: 1px solid rgba(0, 55, 85, 0.12);
    border-left: 4px solid #1565c0;
    box-shadow: 0 4px 14px rgba(0, 55, 85, 0.06);
}
.ca-ref-panel__head {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #003755;
    margin-bottom: 0.65rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(0, 55, 85, 0.1);
}
.ca-ref-coment {
    padding: 0.5rem 0 0.75rem;
    border-bottom: 1px dashed rgba(0, 55, 85, 0.1);
}
.ca-ref-coment:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.ca-ref-coment__label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #003755;
    margin-bottom: 0.35rem;
}
.ca-ref-coment__text {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.45;
    color: #212529;
    white-space: pre-wrap;
}
</style>
