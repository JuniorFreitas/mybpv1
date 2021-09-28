<template>
    <div>
        <div class='row'>
            <div class='col-12 col-sm-6'>
                <div class='form-group'>
                    <label>Encaminhado para Documentos</label>
                    <select
                        class='form-control'
                        onchange='valida_campo_vazio(this,1)'
                        onblur='valida_campo_vazio(this,1)'
                        :disabled='visualizar'
                        v-model='form.documentos_entregue'
                    >
                        <option value=''>Selecione</option>
                        <option :value='true'>Sim</option>
                        <option :value='false'>Não</option>
                    </select>
                </div>
            </div>

            <div class='col-12 col-sm-6' v-if='form.documentos_entregue'>
                <label>Data do Encaminhamento</label>
                <datepicker
                    style='margin-top: -19px;'
                    label=''
                    :disabled='visualizar'
                    onblur='valida_campo_vazio(this,1)'
                    v-model='form.documentos_entregue_data'
                ></datepicker>
            </div>
        </div>

        <div class='row'>
            <div class='col-12 col-sm-6'>
                <div class='form-group'>
                    <label>Encaminhado para Exame</label>
                    <select
                        class='form-control'
                        onchange='valida_campo_vazio(this,1)'
                        onblur='valida_campo_vazio(this,1)'
                        :disabled='visualizar'
                        v-model='form.encaminhado_exame'
                    >
                        <option value=''>Selecione</option>
                        <option :value='true'>Sim</option>
                        <option :value='false'>Não</option>
                    </select>
                </div>
            </div>

            <div class='col-12 col-sm-6' v-if='form.encaminhado_exame'>
                <label>Data do encaminhamento</label>
                <datepicker
                    label=''
                    style='margin-top: -19px;'
                    :disabled='visualizar'
                    onblur='valida_campo_vazio(this,1)'
                    v-model='form.encaminhado_exame_data'
                ></datepicker>
            </div>
        </div>

        <div class='row'>
            <div class='col-12 col-sm-6'>
                <div class='form-group'>
                    <label>Encaminhado para Treinamento</label>
                    <select
                        class='form-control'
                        onchange='valida_campo_vazio(this,1)'
                        onblur='valida_campo_vazio(this,1)'
                        :disabled='visualizar'
                        v-model='form.encaminhado_treinamento'
                    >
                        <option value=''>Selecione</option>
                        <option :value='true'>Sim</option>
                        <option :value='false'>Não</option>
                    </select>
                </div>
            </div>

            <div class='col-12 col-sm-6' v-if='form.encaminhado_treinamento'>
                <label>Data do encaminhamento</label>
                <datepicker
                    label=''
                    style='margin-top: -19px;'
                    :disabled='visualizar'
                    onblur='valida_campo_vazio(this,1)'
                    v-model='form.encaminhado_treinamento_data'
                ></datepicker>
            </div>
        </div>

        <div class='row'>
            <div class='col-12 col-sm-6'>
                <div class='form-group'>
                    <label>Exceção</label>
                    <select
                        class='form-control'
                        onchange='valida_campo_vazio(this,1)'
                        onblur='valida_campo_vazio(this,1)'
                        :disabled='visualizar'
                        v-model='form.excessao'
                    >
                        <option value=''>Selecione</option>
                        <option :value='true'>Sim</option>
                        <option :value='false'>Não</option>
                    </select>
                </div>
            </div>

            <div class='col-12 col-sm-6' v-if='form.excessao'>
                <div class='form-group'>
                    <label>Autorizado por</label>
                    <input
                        type='text'
                        :disabled='visualizar'
                        autocomplete='off'
                        class='form-control'
                        onblur='valida_campo_vazio(this,3)'
                        v-model='form.autorizado_por'
                    />
                </div>
            </div>
        </div>

        <div class='row'>
            <div class='col-12 col-sm-6'>
                <div class='form-group'>
                    <label>Responsável pelo envio</label>
                    <input
                        type='text'
                        :disabled='visualizar'
                        autocomplete='off'
                        class='form-control'
                        onblur='valida_campo_vazio(this,3)'
                        v-model='form.responsavel_envio'
                    />
                </div>
            </div>
        </div>

        <div class='row'>
            <div class='col-12'>
                <div class='form-group'>
                    <label>Observações</label>
                    <input type='text' :disabled="visualizar || form.obs === 'ADMISSÃO AVULSA'" autocomplete='off'
                           class='form-control' v-model='form.obs' />
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
            required: true,
            default: {
                feedback_id: '',
                documentos_entregue: '',
                documentos_entregue_data: '',
                encaminhado_exame: '',
                encaminhado_exame_data: '',
                encaminhado_treinamento: '',
                encaminhado_treinamento_data: '',
                excessao: '',
                autorizado_por: '',
                responsavel_envio: '',
                obs: ''
            }
        },
        visualizar: {
            type: Boolean,
            default: false
        }
    }
}
</script>

<style scoped></style>
