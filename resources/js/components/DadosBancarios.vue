<template>
    <div :id="`dados-bancarios-${hash}`">
        <fieldset class="mybp-modal-secao">
            <legend>Dados Bancários</legend>
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`db-banco-${hash}`">
                            Banco <span class="text-danger">*</span>
                        </label>
                        <input
                            :id="`db-banco-${hash}`"
                            type="text"
                            class="form-control form-control-sm"
                            v-model="model.banco"
                            onblur="valida_campo_vazio(this, 1)"
                            :disabled="visualizar"
                        />
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`db-agencia-${hash}`">
                            Agência <span class="text-danger">*</span>
                        </label>
                        <input
                            :id="`db-agencia-${hash}`"
                            type="text"
                            class="form-control form-control-sm"
                            v-model="model.agencia"
                            onblur="valida_campo_vazio(this, 1)"
                            :disabled="visualizar"
                        />
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`db-conta-${hash}`">
                            Conta <span class="text-danger">*</span>
                        </label>
                        <input
                            :id="`db-conta-${hash}`"
                            type="text"
                            class="form-control form-control-sm"
                            v-model="model.conta"
                            onblur="valida_campo_vazio(this, 1)"
                            :disabled="visualizar"
                        />
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`db-pix-${hash}`">
                            Tem PIX? <span class="text-danger">*</span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <select
                                :id="`db-pix-${hash}`"
                                class="form-control form-control-sm"
                                :value="pixCombo"
                                :disabled="visualizar"
                                @change="onPixSelectChange"
                            >
                                <option value="">Selecione...</option>
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4" v-show="model.pix === true">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`db-tipo-chave-${hash}`">
                            Tipo de Chave <span class="text-danger">*</span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <select
                                :id="`db-tipo-chave-${hash}`"
                                class="form-control form-control-sm"
                                v-model="model.tipochavepix"
                                :disabled="visualizar"
                                @change="limparComboboxInvalido(`db-tipo-chave-${hash}`)"
                            >
                                <option value="">Selecione...</option>
                                <option
                                    v-for="opt in opcoesTipoChavePix"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4" v-show="model.pix === true">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`db-chave-${hash}`">
                            Chave do Pix <span class="text-danger">*</span>
                        </label>
                        <input
                            :id="`db-chave-${hash}`"
                            type="text"
                            class="form-control form-control-sm"
                            v-model="model.chavepix"
                            onblur="valida_campo_vazio(this, 1)"
                            :disabled="visualizar"
                        />
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</template>

<script>
import ComboboxValidation from '../mixins/ComboboxValidation'

/** Domínio alinhado a App\Models\UsuarioConta::TIPOS_CHAVES */
const TIPOS_CHAVE_PIX = [
    { value: 'cpf', label: 'CPF' },
    { value: 'cnpj', label: 'CNPJ' },
    { value: 'email', label: 'E-mail' },
    { value: 'telefone', label: 'Telefone' },
    { value: 'aleatoria', label: 'Aleatória' }
]

export default {
    name: 'DadosBancarios',
    mixins: [ComboboxValidation],
    props: {
        model: {
            type: Object,
            required: true,
            default: () => ({
                banco: '',
                agencia: '',
                conta: '',
                pix: false,
                tipochavepix: '',
                chavepix: ''
            })
        },
        visualizar: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            hash: parseInt(Math.random() * 999999, 10)
        }
    },
    computed: {
        opcoesTipoChavePix() {
            const base = TIPOS_CHAVE_PIX.slice()
            const atual = this.model && this.model.tipochavepix
            if (!atual) return base

            const normalizado = String(atual).trim().toLowerCase()
            if (normalizado && !base.some((o) => o.value === normalizado)) {
                base.push({ value: atual, label: String(atual) })
            }
            return base
        },
        pixCombo() {
            return this.boolToCombo(this.model ? this.model.pix : undefined)
        }
    },
    mounted() {
        this.normalizarTipoChave()
    },
    watch: {
        'model.tipochavepix'(val) {
            this.normalizarTipoChave(val)
        }
    },
    methods: {
        normalizarTipoChave(val) {
            if (!this.model) return
            const bruto = val !== undefined ? val : this.model.tipochavepix
            if (bruto == null || bruto === '') return
            const normalizado = String(bruto).trim().toLowerCase()
            if (TIPOS_CHAVE_PIX.some((o) => o.value === normalizado) && bruto !== normalizado) {
                this.model.tipochavepix = normalizado
            }
        },
        boolToCombo(val) {
            if (val === true || val === 1 || val === '1') return 'sim'
            // false é resposta válida ("Não") — não tratar como vazio
            if (val === false || val === 0 || val === '0') return 'nao'
            if (val == null || val === '') return ''
            const s = String(val).trim().toLowerCase()
            if (s === 'sim' || s === 'true' || s === 's') return 'sim'
            if (s === 'nao' || s === 'não' || s === 'false' || s === 'n') return 'nao'
            return ''
        },
        comboToBool(v) {
            if (v === 'sim') return true
            if (v === 'nao') return false
            return ''
        },
        aplicarPixCombo(v) {
            if (!this.model) return
            this.model.pix = this.comboToBool(v)
            if (this.model.pix !== true) {
                this.model.tipochavepix = ''
                this.model.chavepix = ''
            }
        },
        onPixSelectChange(event) {
            const v = event && event.target ? event.target.value : ''
            this.aplicarPixCombo(v)
            this.limparComboboxInvalido(`db-pix-${this.hash}`)
        },
        /**
         * Garante model.pix alinhado ao <select> (evita desync Vue/DOM
         * em que a tela mostra "Não" mas o model ainda está vazio).
         */
        sincronizarPixDoSelect() {
            const el = document.getElementById(`db-pix-${this.hash}`)
            if (!el || !this.model) return
            const v = String(el.value || '')
            if (v !== 'sim' && v !== 'nao') return
            if (this.boolToCombo(this.model.pix) !== v) {
                this.aplicarPixCombo(v)
            }
        },
        obterPixComboValor() {
            this.sincronizarPixDoSelect()
            const doModel = this.boolToCombo(this.model ? this.model.pix : undefined)
            if (doModel === 'sim' || doModel === 'nao') return doModel
            const el = document.getElementById(`db-pix-${this.hash}`)
            const doDom = el ? String(el.value || '') : ''
            if (doDom === 'sim' || doDom === 'nao') {
                this.aplicarPixCombo(doDom)
                return doDom
            }
            return ''
        },
        validarCampos() {
            if (this.visualizar) return true
            if (!this.model) {
                if (typeof mostraErro === 'function') mostraErro('', 'Dados bancários indisponíveis')
                return false
            }

            if (!String(this.model.banco || '').trim()) {
                const el = document.getElementById(`db-banco-${this.hash}`)
                if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 1)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe o banco')
                return false
            }
            if (!String(this.model.agencia || '').trim()) {
                const el = document.getElementById(`db-agencia-${this.hash}`)
                if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 1)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe a agência')
                return false
            }
            if (!String(this.model.conta || '').trim()) {
                const el = document.getElementById(`db-conta-${this.hash}`)
                if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 1)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe a conta')
                return false
            }
            if (
                !this.exigirCombobox(this.obterPixComboValor(), `db-pix-${this.hash}`, {
                    toastMsg: 'Selecione se tem PIX'
                })
            ) {
                return false
            }
            if (this.model.pix === true) {
                if (
                    !this.exigirCombobox(this.model.tipochavepix, `db-tipo-chave-${this.hash}`, {
                        toastMsg: 'Selecione o tipo de chave PIX'
                    })
                ) {
                    return false
                }
                if (!String(this.model.chavepix || '').trim()) {
                    const el = document.getElementById(`db-chave-${this.hash}`)
                    if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 1)
                    if (typeof mostraErro === 'function') mostraErro('', 'Informe a chave PIX')
                    return false
                }
            }

            return this.validarInputsAtivosVisiveis(`dados-bancarios-${this.hash}`, {
                preservarIds: [`db-pix-${this.hash}`, `db-tipo-chave-${this.hash}`]
            })
        }
    }
}
</script>
