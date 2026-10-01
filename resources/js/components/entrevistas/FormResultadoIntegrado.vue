<template>
    <div :id="`form-ri-${hash}`" class="mybp-ri-form">
        <fieldset class="mybp-modal-secao">
            <legend>Documentos</legend>
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`ri-docs-${hash}`">
                            Encaminhado <span class="text-danger">*</span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                :instance-id="`ri-docs-${hash}`"
                                :input-id="`ri-docs-${hash}`"
                                v-model="documentosCombo"
                                :options="opcoesSimNao"
                                :disabled="visualizar || disabled || encaminhado.documentos"
                                placeholder-blur="Selecione..."
                                empty-message="Nenhuma opção."
                                :max-results="5"
                                @opening="fecharOutrosComboboxes(`ri-docs-${hash}`)"
                                @select="limparComboboxInvalido(`ri-docs-${hash}`)"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4" v-show="form.documentos_entregue">
                    <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                        <label class="mybp-label">Data do encaminhamento</label>
                        <datepicker
                            label=""
                            formsm
                            class="corrigiDatepicker"
                            :disabled="visualizar || disabled || encaminhado.documentos"
                            v-model="form.documentos_entregue_data"
                        ></datepicker>
                    </div>
                </div>
            </div>
            <div class="row" v-show="form.documentos_entregue">
                <div class="col-12">
                    <div class="mybp-ri-notificacoes">
                        <span class="mybp-ri-notificacoes__titulo">Notificar</span>
                        <div class="custom-control custom-switch mybp-ri-notificacoes__item">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                v-model="form.envia_email_documentos"
                                :disabled="visualizar || disabled || encaminhado.documentos"
                                :id="`ri-email-docs-${hash}`"
                            />
                            <label class="custom-control-label" :for="`ri-email-docs-${hash}`">E-mail</label>
                        </div>
                        <div
                            class="custom-control custom-switch mybp-ri-notificacoes__item"
                            v-show="whatsappPodeNotificar('admissao_documentos', telefonePrincipal)"
                        >
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                v-model="form.envia_whatsapp_documentos"
                                :disabled="visualizar || disabled || encaminhado.documentos"
                                :id="`ri-wa-docs-${hash}`"
                            />
                            <label class="custom-control-label" :for="`ri-wa-docs-${hash}`">WhatsApp</label>
                        </div>
                        <button
                            type="button"
                            class="btn btn-link btn-sm p-0 mybp-ri-notificacoes__preview"
                            v-show="whatsappPodeNotificar('admissao_documentos', telefonePrincipal)"
                            @click="previewWhatsapp('admissao_documentos')"
                        >
                            Visualizar mensagem
                        </button>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="mybp-modal-secao">
            <legend>Exame</legend>
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`ri-exame-${hash}`">Encaminhado</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                :instance-id="`ri-exame-${hash}`"
                                :input-id="`ri-exame-${hash}`"
                                v-model="exameCombo"
                                :options="opcoesSimNao"
                                :disabled="visualizar || disabled || encaminhado.exame"
                                placeholder-blur="Selecione..."
                                empty-message="Nenhuma opção."
                                :max-results="5"
                                @opening="fecharOutrosComboboxes(`ri-exame-${hash}`)"
                                @select="limparComboboxInvalido(`ri-exame-${hash}`)"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4" v-show="form.encaminhado_exame">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`ri-pcmso-${hash}`">
                            PCMSO
                            <span class="text-danger" v-if="exigePcmsoEmpresa">*</span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                :instance-id="`ri-pcmso-${hash}`"
                                :input-id="`ri-pcmso-${hash}`"
                                v-model="form.pcmso_id"
                                :options="opcoesPcmso"
                                :disabled="visualizar || disabled || encaminhado.exame"
                                placeholder-blur="Selecione..."
                                empty-message="Nenhum PCMSO encontrado."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes(`ri-pcmso-${hash}`)"
                                @select="limparComboboxInvalido(`ri-pcmso-${hash}`)"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4" v-show="form.encaminhado_exame">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`ri-empresa-exame-${hash}`">
                            Empresa exame
                            <span class="text-danger" v-if="exigePcmsoEmpresa">*</span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                :instance-id="`ri-empresa-exame-${hash}`"
                                :input-id="`ri-empresa-exame-${hash}`"
                                v-model="form.empresa_exame_id"
                                :options="opcoesEmpresaExame"
                                :disabled="visualizar || disabled || encaminhado.exame"
                                placeholder-blur="Selecione..."
                                empty-message="Nenhuma empresa encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes(`ri-empresa-exame-${hash}`)"
                                @select="limparComboboxInvalido(`ri-empresa-exame-${hash}`)"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" v-show="form.encaminhado_exame">
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                        <label class="mybp-label">Data da realização</label>
                        <datepicker
                            label=""
                            formsm
                            class="corrigiDatepicker"
                            :disabled="visualizar || disabled || encaminhado.exame"
                            v-model="form.encaminhado_exame_data"
                        ></datepicker>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <div class="mybp-ri-notificacoes mybp-ri-notificacoes--com-label">
                        <span class="mybp-ri-notificacoes__titulo">Notificar</span>
                        <div class="custom-control custom-switch mybp-ri-notificacoes__item">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                v-model="form.envia_email_exame"
                                :disabled="visualizar || disabled || encaminhado.exame"
                                :id="`ri-email-exame-${hash}`"
                            />
                            <label class="custom-control-label" :for="`ri-email-exame-${hash}`">E-mail</label>
                        </div>
                        <div
                            class="custom-control custom-switch mybp-ri-notificacoes__item"
                            v-show="whatsappPodeNotificar('admissao_exame', telefonePrincipal)"
                        >
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                v-model="form.envia_whatsapp_exame"
                                :disabled="visualizar || disabled || encaminhado.exame"
                                :id="`ri-wa-exame-${hash}`"
                            />
                            <label class="custom-control-label" :for="`ri-wa-exame-${hash}`">WhatsApp</label>
                        </div>
                        <button
                            type="button"
                            class="btn btn-link btn-sm p-0 mybp-ri-notificacoes__preview"
                            v-show="whatsappPodeNotificar('admissao_exame', telefonePrincipal)"
                            @click="previewWhatsapp('admissao_exame')"
                        >
                            Visualizar mensagem
                        </button>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="mybp-modal-secao">
            <legend>Treinamento</legend>
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`ri-trein-${hash}`">
                            Encaminhado <span class="text-danger">*</span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                :instance-id="`ri-trein-${hash}`"
                                :input-id="`ri-trein-${hash}`"
                                v-model="treinamentoCombo"
                                :options="opcoesSimNao"
                                :disabled="visualizar || disabled || encaminhado.treinamento"
                                placeholder-blur="Selecione..."
                                empty-message="Nenhuma opção."
                                :max-results="5"
                                @opening="fecharOutrosComboboxes(`ri-trein-${hash}`)"
                                @select="limparComboboxInvalido(`ri-trein-${hash}`)"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4" v-show="form.encaminhado_treinamento">
                    <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                        <label class="mybp-label">Data do encaminhamento</label>
                        <datepicker
                            label=""
                            formsm
                            class="corrigiDatepicker"
                            :disabled="visualizar || disabled || encaminhado.treinamento"
                            v-model="form.encaminhado_treinamento_data"
                        ></datepicker>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="mybp-modal-secao">
            <legend>Exceção e responsável</legend>
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`ri-excessao-${hash}`">
                            Exceção <span class="text-danger">*</span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                :instance-id="`ri-excessao-${hash}`"
                                :input-id="`ri-excessao-${hash}`"
                                v-model="excessaoCombo"
                                :options="opcoesSimNao"
                                :disabled="visualizar || disabled"
                                placeholder-blur="Selecione..."
                                empty-message="Nenhuma opção."
                                :max-results="5"
                                @opening="fecharOutrosComboboxes(`ri-excessao-${hash}`)"
                                @select="limparComboboxInvalido(`ri-excessao-${hash}`)"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`ri-autorizado-${hash}`">
                            Autorizado por
                            <span class="text-danger" v-show="form.excessao === true">*</span>
                        </label>
                        <input
                            :id="`ri-autorizado-${hash}`"
                            type="text"
                            class="form-control form-control-sm"
                            autocomplete="off"
                            :disabled="visualizar || disabled || form.excessao !== true"
                            :placeholder="form.excessao === true ? '' : 'Somente se houver exceção'"
                            v-bind="
                                form.excessao === true && !visualizar && !disabled
                                    ? { onblur: 'valida_campo_vazio(this, 3)' }
                                    : {}
                            "
                            v-model="form.autorizado_por"
                        />
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`ri-responsavel-${hash}`">
                            Responsável pelo envio <span class="text-danger">*</span>
                        </label>
                        <input
                            :id="`ri-responsavel-${hash}`"
                            type="text"
                            class="form-control form-control-sm"
                            autocomplete="off"
                            :disabled="visualizar || disabled"
                            onblur="valida_campo_vazio(this, 3)"
                            v-model="form.responsavel_envio"
                        />
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`ri-obs-${hash}`">Observações</label>
                        <input
                            :id="`ri-obs-${hash}`"
                            type="text"
                            class="form-control form-control-sm"
                            autocomplete="off"
                            :disabled="visualizar || disabled || form.obs === 'ADMISSÃO AVULSA' || form.obs === 'RECONTRATAÇÃO'"
                            v-model="form.obs"
                        />
                    </div>
                </div>
            </div>
        </fieldset>

        <whatsapp-preview-modal
            v-model="previewWhatsappAberto"
            :tipo-mensagem="previewWhatsappTipo"
            :contexto="previewWhatsappContexto"
        />
    </div>
</template>

<script>
import MixinConfig from '../../mixins/Configuracoes'
import ComboboxAutoComplete from '../ComboboxAutoComplete.vue'
import ComboboxValidation from '../../mixins/ComboboxValidation'

export default {
    name: 'FormResultadoIntegrado',
    components: { ComboboxAutoComplete },
    mixins: [MixinConfig, ComboboxValidation],
    props: {
        form: {
            type: Object,
            required: true,
            default: () => ({
                feedback_id: '',
                documentos_entregue: '',
                documentos_entregue_data: '',
                documento_email: false,
                documento_whatsapp: true,
                encaminhado_exame: '',
                encaminhado_exame_data: '',
                exame_email: false,
                exame_whatsapp: false,
                pcmso_id: '',
                empresa_exame_id: '',
                encaminhado_treinamento: '',
                encaminhado_treinamento_data: '',
                excessao: '',
                autorizado_por: '',
                responsavel_envio: '',
                obs: '',
                envia_email_documentos: false,
                envia_whatsapp_documentos: false,
                envia_email_exame: false,
                envia_whatsapp_exame: false
            })
        },
        visualizar: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        nomeCandidato: {
            type: String,
            default: 'Candidato'
        },
        telefonePrincipal: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            hash: parseInt(Math.random() * 999999, 10),
            listaPcmso: [],
            listaEmpresaExame: [],
            encaminhado: {
                documentos: false,
                exame: false,
                treinamento: false
            },
            AUTENTICADO,
            empresasSemValidacao: [78862],
            previewWhatsappAberto: false,
            previewWhatsappTipo: '',
            previewWhatsappContexto: {}
        }
    },
    computed: {
        opcoesSimNao() {
            return [
                { value: 'sim', label: 'Sim' },
                { value: 'nao', label: 'Não' }
            ]
        },
        opcoesPcmso() {
            return (this.listaPcmso || []).map((item) => ({
                value: item.id,
                label: item.label
            }))
        },
        opcoesEmpresaExame() {
            return (this.listaEmpresaExame || []).map((item) => ({
                value: item.id,
                label: item.nome
            }))
        },
        exigePcmsoEmpresa() {
            return !this.empresasSemValidacao.includes(this.AUTENTICADO.cliente_id)
        },
        documentosCombo: {
            get() {
                return this.boolToCombo(this.form && this.form.documentos_entregue)
            },
            set(v) {
                if (!this.form) return
                this.form.documentos_entregue = this.comboToBool(v)
            }
        },
        exameCombo: {
            get() {
                return this.boolToCombo(this.form && this.form.encaminhado_exame)
            },
            set(v) {
                if (!this.form) return
                this.form.encaminhado_exame = this.comboToBool(v)
                if (this.form.encaminhado_exame !== true) {
                    this.form.pcmso_id = ''
                    this.form.empresa_exame_id = ''
                    this.form.encaminhado_exame_data = ''
                }
            }
        },
        treinamentoCombo: {
            get() {
                return this.boolToCombo(this.form && this.form.encaminhado_treinamento)
            },
            set(v) {
                if (!this.form) return
                this.form.encaminhado_treinamento = this.comboToBool(v)
            }
        },
        excessaoCombo: {
            get() {
                return this.boolToCombo(this.form && this.form.excessao)
            },
            set(v) {
                if (!this.form) return
                this.form.excessao = this.comboToBool(v)
                if (this.form.excessao !== true) {
                    this.form.autorizado_por = ''
                }
            }
        }
    },
    async mounted() {
        try {
            const resPcmso = await axios.get(`${URL_ADMIN}/get-pcmso`)
            this.listaPcmso = resPcmso.data
        } catch (err) {
            this.listaPcmso = []
        }
        try {
            const resEmpresa = await axios.get(`${URL_ADMIN}/get-empresa-exames`)
            this.listaEmpresaExame = resEmpresa.data
        } catch (err) {
            this.listaEmpresaExame = []
        }
        this.form.pcmso_id = !this.form.pcmso_id ? '' : this.form.pcmso_id
        this.form.empresa_exame_id = !this.form.empresa_exame_id ? '' : this.form.empresa_exame_id
        this.form.envia_email_documentos = false
        this.form.envia_whatsapp_documentos = false
        this.form.envia_email_exame = false
        this.form.envia_whatsapp_exame = false

        this.encaminhado = {
            documentos: !!this.form.documentos_entregue,
            exame: !!this.form.encaminhado_exame,
            treinamento: !!this.form.encaminhado_treinamento
        }
    },
    methods: {
        fecharOutrosComboboxes() {},
        boolToCombo(val) {
            if (val === true || val === 'true' || val === 1 || val === '1') return 'sim'
            if (val === false || val === 'false' || val === 0 || val === '0') return 'nao'
            return ''
        },
        comboToBool(v) {
            if (v === 'sim') return true
            if (v === 'nao') return false
            return ''
        },
        previewWhatsapp(tipo) {
            const empExame = this.listaEmpresaExame.find((item) => item.id === this.form.empresa_exame_id)
            const urlDocumentos = `${window.location.origin}/${this.AUTENTICADO.apelido}/documentos`

            if (tipo === 'admissao_documentos') {
                this.previewWhatsappContexto = {
                    nome_destinatario: this.nomeCandidato,
                    url_documentos: urlDocumentos,
                    observacao: ''
                }
            } else {
                this.previewWhatsappContexto = {
                    nome_destinatario: this.nomeCandidato,
                    clinica_nome: empExame ? empExame.nome : '',
                    clinica_endereco: (empExame && empExame.dados && empExame.dados.endereco && empExame.dados.endereco.endereco_completo) || '',
                    clinica_telefone: (empExame && empExame.dados && empExame.dados.telefone) || ''
                }
            }

            this.previewWhatsappTipo = tipo
            this.previewWhatsappAberto = true
        },
        validarCampos() {
            if (this.visualizar || this.disabled) return true

            if (
                !this.exigirCombobox(this.documentosCombo, `ri-docs-${this.hash}`, {
                    toastMsg: 'Selecione o encaminhamento de documentos'
                })
            ) {
                return false
            }

            if (
                !this.exigirCombobox(this.exameCombo, `ri-exame-${this.hash}`, {
                    toastMsg: 'Selecione o encaminhamento de exame'
                })
            ) {
                return false
            }

            if (
                this.form.encaminhado_exame === true &&
                this.exigePcmsoEmpresa &&
                !this.exigirCombobox(this.form.pcmso_id, `ri-pcmso-${this.hash}`, {
                    toastMsg: 'Selecione o PCMSO'
                })
            ) {
                return false
            }

            if (
                this.form.encaminhado_exame === true &&
                this.exigePcmsoEmpresa &&
                !this.exigirCombobox(this.form.empresa_exame_id, `ri-empresa-exame-${this.hash}`, {
                    toastMsg: 'Selecione a empresa de exame'
                })
            ) {
                return false
            }

            if (
                !this.exigirCombobox(this.treinamentoCombo, `ri-trein-${this.hash}`, {
                    toastMsg: 'Selecione o encaminhamento de treinamento'
                })
            ) {
                return false
            }

            if (
                !this.exigirCombobox(this.excessaoCombo, `ri-excessao-${this.hash}`, {
                    toastMsg: 'Selecione se há exceção'
                })
            ) {
                return false
            }

            if (this.form.excessao === true && !String(this.form.autorizado_por || '').trim()) {
                const el = document.getElementById(`ri-autorizado-${this.hash}`)
                if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 3)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe quem autorizou a exceção')
                return false
            }

            if (this.form.excessao !== true) {
                const el = document.getElementById(`ri-autorizado-${this.hash}`)
                if (el) {
                    el.classList.remove('is-invalid')
                    const fb = el.parentElement && el.parentElement.querySelector('div.invalid-feedback')
                    if (fb) fb.remove()
                }
                this.form.autorizado_por = ''
            }

            if (!String(this.form.responsavel_envio || '').trim()) {
                const el = document.getElementById(`ri-responsavel-${this.hash}`)
                if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 3)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe o responsável pelo envio')
                return false
            }

            return this.validarInputsAtivosVisiveis(`form-ri-${this.hash}`, { preservarIds: [] })
        }
    }
}
</script>

<style scoped>
.mybp-ri-notificacoes {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.85rem 1.1rem;
    min-height: var(--mybp-fc-ctrl-h, 1.625rem);
    padding: 0.35rem 0.55rem;
    margin-top: 0.15rem;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    background: #f8f9fa;
}

.mybp-ri-notificacoes--com-label {
    margin-top: 1.35rem;
}

.mybp-ri-notificacoes__titulo {
    font-size: var(--mybp-fc-label-fs, 0.7rem);
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.mybp-ri-notificacoes__item {
    padding-left: 2.1rem;
    margin: 0;
    min-height: 1.25rem;
}

.mybp-ri-notificacoes__item .custom-control-label {
    font-size: var(--mybp-fc-ctrl-fs, 0.6875rem);
    line-height: 1.25rem;
    padding-top: 0;
}

.mybp-ri-notificacoes__preview {
    font-size: var(--mybp-fc-ctrl-fs, 0.6875rem);
    line-height: 1.25;
}
</style>
