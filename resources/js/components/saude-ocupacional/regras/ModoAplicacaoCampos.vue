<template>
    <fieldset class="mybp-modal-secao">
        <legend>Como aplicar?</legend>
        <div class="form-group mb-1">
            <label class="mybp-label font-weight-normal">
                <input type="radio" value="futuro" :checked="modelValue.modo === 'futuro'" @change="atualizar('modo', 'futuro')" />
                Só nos próximos ASOs
            </label>
        </div>
        <div class="form-group mb-1">
            <label class="mybp-label font-weight-normal">
                <input type="radio" value="retroativo" :checked="modelValue.modo === 'retroativo'" @change="atualizar('modo', 'retroativo')" />
                Retroativo (recalcula todos os ASOs atuais)
            </label>
        </div>
        <div class="form-group mb-1">
            <label class="mybp-label font-weight-normal">
                <input type="radio" value="a_partir_de" :checked="modelValue.modo === 'a_partir_de'" @change="atualizar('modo', 'a_partir_de')" />
                A partir de uma data de realização
            </label>
        </div>
        <div class="mt-2 mb-0" v-if="modelValue.modo === 'a_partir_de'">
            <datepicker
                label="Data de realização a partir de"
                formsm
                :id="idData"
                :model-value="modelValue.data"
                @update:model-value="atualizar('data', $event)"
                :config="{ format: 'DD/MM/YYYY', useCurrent: false }"
            />
        </div>
    </fieldset>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'ModoAplicacaoCampos',

    props: {
        // { modo: 'futuro' | 'retroativo' | 'a_partir_de', data: 'dd/mm/aaaa' }
        modelValue: { type: Object, required: true },
        idData: { type: String, default: 'modo-aplicacao-data' }
    },

    emits: ['update:modelValue'],

    methods: {
        atualizar(campo, valor) {
            this.$emit('update:modelValue', { ...this.modelValue, [campo]: valor })
        }
    }
})
</script>
