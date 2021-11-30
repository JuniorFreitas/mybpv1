<template>
    <div class="col-12">
        <div class="form-group">
            <label>Colaborador </label>
            <autocomplete :caminho="`autocomplete/colaboradores/`"
                          :formsm="false"
                          :valido="model.colaborador_id !== ''"
                          v-model="model.autocomplete_label_colaborador"
                          placeholder="Digite o nome do(a) colaborador(a)"
                          :disabled="verifica"
                          :id="`colaborador_${model.hash}`"
                          @onblur="resetaCampoColaborador"
                          @onselect="selecionaColaborador"></autocomplete>
        </div>
    </div>
</template>

<script>

import autocomplete from "./AutoComplete";

export default {
    components: {
        autocomplete,
    },
    props: {
        model: {
            type: Object,
            required: true,
            default: () => {
                return {
                    colaborador_id: '',
                    autocomplete_label_colaborador: '',
                    autocomplete_label_colaborador_anterior: '',

                    hash: `colaborador_${parseInt((Math.random() * 999999))}`,
                }
            }
        },
        verifica: {
            type: Boolean,
            required: true,
        }
    },
    methods: {
        selecionaColaborador(obj) {
            this.model.colaborador_id = obj.curriculo_id;
            this.model.autocomplete_label_colaborador = obj.label;
            this.model.autocomplete_label_colaborador_anterior = obj.label;
        },
        resetaCampoColaborador() {
            if (this.model.autocomplete_label_colaborador_anterior !== this.model.autocomplete_label_colaborador) {
                this.model.autocomplete_label_colaborador_anterior = '';
                this.model.autocomplete_label_colaborador = '';
                this.model.colaborador_id = '';

                setTimeout(() => {
                    if (this.model.colaborador_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.model.hash}`), 1);
                        $(`#${this.model.hash} #colaborador_${this.model.hash}`).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Colaborador não pode ficar vazio');
                    }
                }, 100);
            }
        }
    }
}
</script>

<style scoped>

</style>
