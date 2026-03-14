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
                            :disabled="visualizar || disabled || encaminhado.documentos"
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
                        :disabled="visualizar || disabled || encaminhado.documentos"
                        v-model="form.documentos_entregue_data"
                    ></datepicker>
                </div>
                <div class="col-2" v-if="form.documentos_entregue">
                    <div class="switchToggle">
                        <input type="checkbox" v-model="form.envia_email_documentos"
                               :disabled="visualizar || disabled || encaminhado.documentos" id="envia_email_documentos">
                        <label for="envia_email_documentos">Enviar E-mail</label>
                    </div>
                </div>
                <div class="col-2" v-if="form.documentos_entregue">
                    <div class="switchToggle" v-show="whatsappLiberado">
                        <input type="checkbox" v-model="form.envia_whatsapp_documentos"
                               :disabled="visualizar || disabled || encaminhado.documentos"
                               id="envia_whatsapp_documentos">
                        <label for="envia_whatsapp_documentos">Enviar Whatsapp</label>
                    </div>
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
                            :disabled="visualizar || disabled || encaminhado.exame"
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
                        v-bind:onchange="!empresasSemValidacao.includes(AUTENTICADO.cliente_id) ? 'valida_campo_vazio(this,1)' : null"
                        v-bind:onblur="!empresasSemValidacao.includes(AUTENTICADO.cliente_id) ? 'valida_campo_vazio(this,1)' : null"
                        :disabled="visualizar || disabled || encaminhado.exame"
                        v-model="form.pcmso_id"
                    >
                        <option value="">Selecione</option>
                        <option v-for="(item, index) in listaPcmso" :value="item.id" :key="index">{{ item.label }}</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6" v-if="form.encaminhado_exame">
                    <label>Empresa Exame</label>
                    <select
                        class="form-control"
                        v-bind:onchange="!empresasSemValidacao.includes(AUTENTICADO.cliente_id) ? 'valida_campo_vazio(this,1)' : null"
                        v-bind:onblur="!empresasSemValidacao.includes(AUTENTICADO.cliente_id) ? 'valida_campo_vazio(this,1)' : null"
                        :disabled="visualizar || disabled || encaminhado.exame"
                        v-model="form.empresa_exame_id"
                    >
                        <option value="">Selecione</option>
                        <option v-for="(item, index) in listaEmpresaExame" :value="item.id" :key="index">{{ item.nome }}</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6" v-if="form.encaminhado_exame">
                    <label>Data da Realização</label>
                    <datepicker
                        label=""
                        style="margin-top: -19px;"
                        :disabled="visualizar || disabled || encaminhado.exame"
                        v-model="form.encaminhado_exame_data"
                    ></datepicker>
                </div>

                <div class="col-2" v-if="form.encaminhado_exame">
                    <div class="switchToggle">
                        <input type="checkbox" v-model="form.envia_email_exame"
                               :disabled="visualizar || disabled || encaminhado.exame" id="envia_email_exame">
                        <label for="envia_email_exame">Enviar E-mail</label>
                    </div>
                </div>
                <div class="col-2" v-if="form.encaminhado_exame">
                    <div class="switchToggle" v-show="whatsappLiberado">
                        <input type="checkbox" v-model="form.envia_whatsapp_exame"
                               :disabled="visualizar || disabled || encaminhado.exame" id="envia_whatsapp_exame">
                        <label for="envia_whatsapp_exame">Enviar Whatsapp</label>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Encaminhamento Treinamento</legend>
            <div class="row">
                <div class="col-12 col-sm-6">
                    <div class="form-group">
                        <label>Encaminhado para Treinamento</label>
                        <select
                            class="form-control"
                            onchange="valida_campo_vazio(this,1)"
                            onblur="valida_campo_vazio(this,1)"
                            :disabled="visualizar || disabled || encaminhado.treinamento"
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
                        :disabled="visualizar || disabled || encaminhado.treinamento"
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
                    <input type="text"
                           :disabled="visualizar || disabled || form.obs === 'ADMISSÃO AVULSA' || form.obs === 'RECONTRATAÇÃO'"
                           autocomplete="off"
                           class="form-control" v-model="form.obs"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import MixinConfig from '../../mixins/Configuracoes';

export default {
    mixins: [MixinConfig],
    name: "FormResultadoIntegrado",
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
                obs: "",
                envia_email_documentos: false,
                envia_whatsapp_documentos: false,
                envia_email_exame: false,
                envia_whatsapp_exame: false,
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
            listaEmpresaExame: [],
            encaminhado: {
                documentos: false,
                exame: false,
                treinamento: false
            },
            AUTENTICADO,
            empresasSemValidacao: [78862]
        };
    },
    async mounted() {
        try {
            const resPcmso = await axios.get(`${URL_ADMIN}/get-pcmso`);
            this.listaPcmso = resPcmso.data;
        } catch (err) {
            this.listaPcmso = [];
        }
        try {
            const resEmpresa = await axios.get(`${URL_ADMIN}/get-empresa-exames`);
            this.listaEmpresaExame = resEmpresa.data;
        } catch (err) {
            this.listaEmpresaExame = [];
        }
        this.form.pcmso_id = !this.form.pcmso_id ? "" : this.form.pcmso_id;
        this.form.empresa_exame_id = !this.form.empresa_exame_id ? "" : this.form.empresa_exame_id;
        this.form.envia_email_documentos = false;
        this.form.envia_whatsapp_documentos = false;
        this.form.envia_email_exame = false;
        this.form.envia_whatsapp_exame = false;

        this.encaminhado = {
            documentos: !!this.form.documentos_entregue,
            exame: !!this.form.encaminhado_exame,
            treinamento: !!this.form.encaminhado_treinamento
        };
    }
};
</script>

<style scoped></style>
