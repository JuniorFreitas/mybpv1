<template>
    <div class="col-12">
        <div class="form-group mybp-filtro-campo">
            <label class="mybp-label">
                {{ labelTexto }} <span v-if="obrigatorio" class="text-danger">*</span>
            </label>
            <autocomplete :caminho="urlAutocomplete"
                          :formsm="formsm"
                          :valido="model.colaborador_id !== ''"
                          v-model="model.autocomplete_label_colaborador"
                          placeholder="Digite o nome do(a) colaborador(a)"
                          :disabled="verifica"
                          :id="hash"
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
        label: {
            type: String,
            default: 'Colaborador',
        },
        model: {
            type: Object,
            required: true,
            default: () => {
                return {
                    colaborador_id: '',
                    centro_custo_id: '',
                    autocomplete_label_colaborador: '',
                    autocomplete_label_colaborador_anterior: '',

                    hash: `colaborador_${parseInt((Math.random() * 999999))}`,
                }
            }
        },
        tipo: {
            type: String,
            required: false,
            default: '',
        },
        formsm: {
            type: Boolean,
            default: true,
            required: false,
        },
        verifica: {
            type: Boolean,
            required: true,
        },
        obrigatorio: {
            type: Boolean,
            default: true
        }
    },
    computed: {
        hash() {
            return `colaborador_${parseInt((Math.random() * 999999))}`;
        },
        labelTexto() {
            return String(this.label || 'Colaborador').replace(/\s*\*\s*$/, '').trim() || 'Colaborador'
        },
        urlAutocomplete() {
            return this.tipo === 'ferias' ? 'autocomplete/colaboradores-ferias/' : 'autocomplete/colaboradores/'
        }
    },
    methods: {
        selecionaColaborador(obj) {
            this.model.colaborador_id = this.tipo === 'ferias' ? obj.feedback_id : obj.curriculo_id;
            this.model.admissao_id = obj.id;
            this.model.centro_custo_id = obj.centro_custo_id ?? '';
            this.model.filial = !!(obj.filial === true || obj.filial === 1 || obj.filial === '1');
            this.model.centro_custo_filial_id = this.model.filial
                ? (obj.centro_custo_filial_id ?? null)
                : null;
            this.model.autocomplete_label_colaborador = obj.label;
            this.model.autocomplete_label_colaborador_anterior = obj.label;

            this.$emit('evtseleciona', this.model)
        },
        resetaCampoColaborador() {
            if (this.model.autocomplete_label_colaborador_anterior !== this.model.autocomplete_label_colaborador) {
                this.model.autocomplete_label_colaborador_anterior = '';
                this.model.autocomplete_label_colaborador = '';
                this.model.colaborador_id = '';
                this.model.centro_custo_id = '';
                this.model.filial = false;
                this.model.centro_custo_filial_id = '';

                setTimeout(() => {
                    if (this.model.colaborador_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.model.hash}`), 1);
                        $(`#${this.model.hash} #colaborador_${this.model.hash}`).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Colaborador não pode ficar vazio');
                    }
                }, 100);

                this.$emit('evtreseta', true)
            }

        }
    }
}
</script>

<style scoped>

</style>
