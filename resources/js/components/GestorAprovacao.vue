<template>
    <div class="col-12">
        <div class="form-group">
            <label>{{ label }}</label>
            <autocomplete :caminho="`autocomplete/todos-gestores-ativos/`"
                          :formsm="formsm"
                          :valido="model.gestor_id !== ''"
                          v-model="model.autocomplete_label_gestor_modal"
                          placeholder="Digite o nome do(a) gestor(a)"
                          :disabled="verifica"
                          :id="hash"
                          @onblur="resetaCampoGestor"
                          @onselect="selecionaGestor"></autocomplete>
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
        label: {
            type: String,
            required: false,
            default: "Gestor Aprovação"
        },
        model: {
            type: Object,
            required: true,
            default: () => {
                return {
                    gestor_id: '',
                    autocomplete_label_gestor_modal: '',
                    autocomplete_label_gestor_modal_anterior: '',

                    hash: `gestor_${parseInt((Math.random() * 999999))}`,
                }
            }
        },
        formsm: {
            type: Boolean,
            default: true,
            required: false,
        },
        obrigatorio: {
            type: Boolean,
            required: false,
            default: false
        },
        verifica: {
            type: Boolean,
            required: true,
        }
    },
    computed: {
        hash() {
            return `gestor_${parseInt((Math.random() * 999999))}`;
        },
    },
    methods: {
        selecionaGestor(obj) {
            this.model.gestor_id = obj.id;
            this.model.autocomplete_label_gestor_modal = obj.label;
            this.model.autocomplete_label_gestor_modal_anterior = obj.label;
        },
        resetaCampoGestor() {
            if (this.model.autocomplete_label_gestor_modal_anterior !== this.model.autocomplete_label_gestor_modal) {
                this.model.autocomplete_label_gestor_modal_anterior = '';
                this.model.autocomplete_label_gestor_modal = '';
                this.model.gestor_id = '';
                if (this.obrigatorio) {
                    setTimeout(() => {
                        if (this.model.gestor_id === '') {
                            valida_campo_vazio($(`#${this.hash}`), 1);
                            $(`#${this.hash}`).focus().trigger('blur');
                            mostraErro('Erro', 'O Campo Gestor não pode ficar vazio');
                        }
                    }, 100);
                }

            }
        },
    }
}
</script>

<style scoped>

</style>
