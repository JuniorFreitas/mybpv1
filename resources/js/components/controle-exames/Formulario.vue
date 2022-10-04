<template>
    <div class='row'>
        <div class='col-12 col-sm-6'>
            <div class='form-group'>
                <label>EXAME DE ORDEM </label>
                <select
                    class='form-control'
                    onchange='valida_campo_vazio(this,1)'
                    onblur='valida_campo_vazio(this,1)'
                    :disabled='visualizar'
                >
                    <option value=''>Selecione</option>
                    <option v-for='tipoexame in listaTiposExames' :value='tipoexame.id'>{{ tipoexame.label }}</option>
                </select>
            </div>
        </div>

        <div class='col-12'>RISCOS OCUPACIONAIS A QUE ESTÁ EXPOSTO O COLABORADOR</div>


        <div class='col-12 col-sm-6' v-for='(riscotipo,label) in listaRiscos'>
            <div class='form-group'>
                <fieldset>
                    <legend>{{ label }}</legend>
                </fieldset>
                <div class='custom-control custom-switch'>
                    <input type='checkbox' class='custom-control-input'
                           :value='riscotipo.id'
                           :id='`alternativa_${riscotipo.id}`'>
                    <label class='custom-control-label' style='cursor: pointer'
                           :for='`alternativa_${riscotipo.id}`'>
                        @{{ riscotipo.label }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        form: {
            type: Object,
            required: false,
            default: {}
        },
        empresa_id: {
            type: Number,
            default: 104
        },
        visualizar: {
            type: Boolean,
            default: false
        }
    },
    mounted() {
        this.getTiposExamesTipoRiscos()
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            listaTiposExames: [],
            listaExames: [],
            listaRiscos: []
        }
    },
    methods: {
        async getTiposExamesTipoRiscos() {
            await axios.post(`${URL_ADMIN}/controle-exames/TiposExamesTipoRiscos`, { 'empresa_id': this.empresa_id }).then(response => {
                this.listaTiposExames = response.data.listaTiposExames
                this.listaRiscos = response.data.listaRiscos
            }).catch(error => {
            })
        }
    }
}
</script>

<style scoped>

</style>
