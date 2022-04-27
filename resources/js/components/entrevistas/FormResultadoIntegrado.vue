<template>
    <div>
        <fieldset>
            <legend>Encaminhamento documentos</legend>
            <div class="row">
                <div class="col-12 col-sm-6">
                    <div class="form-group">
                        <label>Encaminhado para Documentos</label>
                        <select
                            class="form-control"
                            onchange="valida_campo_vazio(this,1)"
                            onblur="valida_campo_vazio(this,1)"
                            :disabled="visualizar || disabled"
                            v-model="form.documentos_entregue"
                        >
                            <option value="">Selecione</option>
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-6" v-if="form.documentos_entregue">
                    <label>Data do Encaminhamento</label>
                    <datepicker
                        style="margin-top: -19px;"
                        label=""
                        :disabled="visualizar || disabled"
                        onblur="valida_campo_vazio(this,1)"
                        v-model="form.documentos_entregue_data"
                    ></datepicker>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Encaminhamento Exame</legend>
            <div class="row">
                <div class="col-12 col-sm-6">
                    <div class="form-group">
                        <label>Encaminhado para Exame</label>
                        <select
                            class="form-control"
                            onchange="valida_campo_vazio(this,1)"
                            onblur="valida_campo_vazio(this,1)"
                            :disabled="visualizar || disabled"
                            v-model="form.encaminhado_exame"
                        >
                            <option value="">Selecione</option>
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-6" v-if="form.encaminhado_exame">
                    <label>PCMSO</label>
                    <select
                        class="form-control"
                        onchange="valida_campo_vazio(this,1)"
                        onblur="valida_campo_vazio(this,1)"
                        :disabled="visualizar || disabled"
                        v-model="form.pcmso_id"
                    >
                        <option value="">Selecione</option>
                        <option v-for="item in listaPcmso" :value="item.id">{{ item.label }}</option>
                        >
                    </select>
                </div>

                <div class="col-12 col-sm-6" v-if="form.encaminhado_exame">
                    <label>Empresa Exame</label>
                    <select
                        class="form-control"
                        onchange="valida_campo_vazio(this,1)"
                        onblur="valida_campo_vazio(this,1)"
                        :disabled="visualizar || disabled"
                        v-model="form.empresa_exame_id"
                    >
                        <option value="">Selecione</option>
                        <option v-for="item in listaEmpresaExame" :value="item.user_id">{{ item.nome }}</option>
                        >
                    </select>
                </div>

                <div class="col-12 col-sm-6" v-if="form.encaminhado_exame">
                    <label>Data do encaminhamento</label>
                    <datepicker
                        label=""
                        style="margin-top: -19px;"
                        :disabled="visualizar || disabled"
                        onblur="valida_campo_vazio(this,1)"
                        v-model="form.encaminhado_exame_data"
                    ></datepicker>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Encaminhameto Treinameto</legend>
            <div class="row">
                <div class="col-12 col-sm-6">
                    <div class="form-group">
                        <label>Encaminhado para Treinamento</label>
                        <select
                            class="form-control"
                            onchange="valida_campo_vazio(this,1)"
                            onblur="valida_campo_vazio(this,1)"
                            :disabled="visualizar || disabled"
                            v-model="form.encaminhado_treinamento"
                        >
                            <option value="">Selecione</option>
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-6" v-if="form.encaminhado_treinamento">
                    <label>Data do encaminhamento</label>
                    <datepicker
                        label=""
                        style="margin-top: -19px;"
                        :disabled="visualizar || disabled"
                        onblur="valida_campo_vazio(this,1)"
                        v-model="form.encaminhado_treinamento_data"
                    ></datepicker>
                </div>
            </div>
        </fieldset>

        <div class="row">
            <div class="col-12 col-sm-6">
                <div class="form-group">
                    <label>Exceção</label>
                    <select
                        class="form-control"
                        onchange="valida_campo_vazio(this,1)"
                        onblur="valida_campo_vazio(this,1)"
                        :disabled="visualizar || disabled"
                        v-model="form.excessao"
                    >
                        <option value="">Selecione</option>
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-6" v-if="form.excessao">
                <div class="form-group">
                    <label>Autorizado por</label>
                    <input
                        type="text"
                        :disabled="visualizar || disabled"
                        autocomplete="off"
                        class="form-control"
                        onblur="valida_campo_vazio(this,3)"
                        v-model="form.autorizado_por"
                    />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-6">
                <div class="form-group">
                    <label>Responsável pelo envio</label>
                    <input
                        type="text"
                        :disabled="visualizar || disabled"
                        autocomplete="off"
                        class="form-control"
                        onblur="valida_campo_vazio(this,3)"
                        v-model="form.responsavel_envio"
                    />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label>Observações</label>
                    <input type="text" :disabled="visualizar || disabled || form.obs === 'ADMISSÃO AVULSA'"
                           autocomplete="off"
                           class="form-control" v-model="form.obs" />
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
                feedback_id: "",
                documentos_entregue: "",
                documentos_entregue_data: "",
                documento_email: false,
                documento_whatsapp: true,
                encaminhado_exame: "",
                encaminhado_exame_data: "",
                exame_email: false,
                exame_whatsapp: false,
                pcmso_id: "",
                empresa_exame_id: "",
                encaminhado_treinamento: "",
                encaminhado_treinamento_data: "",
                excessao: "",
                autorizado_por: "",
                responsavel_envio: "",
                obs: ""
            }
        },
        visualizar: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            listaPcmso: [],
            listaEmpresaExame: []
        };
    },
    mounted() {
        axios.get(`${URL_ADMIN}/get-pcmso`).then(response => {
            this.listaPcmso = response.data;
        });
        axios.get(`${URL_ADMIN}/get-empresa-exames`
        ).then(response => {
            this.listaEmpresaExame = response.data;
        });
        this.form.pcmso_id = !this.form.pcmso_id ? "" : this.form.pcmso_id;
        this.form.empresa_exame_id = !this.form.empresa_exame_id ? "" : this.form.empresa_exame_id;
    }
};
</script>

<style scoped></style>
