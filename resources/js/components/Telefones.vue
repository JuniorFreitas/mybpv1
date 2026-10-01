<template>
    <div :id="`telefones-${hash}`" class="mybp-telefones">
        <div class="mb-2" v-if="!disabled && model.length < qnt_max">
            <button class="btn btn-sm btn-secondary" type="button" @click.prevent="add">
                <span class="fas fa-plus" aria-hidden="true"></span>
                Adicionar
            </button>
        </div>

        <div
            v-for="(tel, index) in lista"
            :key="tel.id || `novo-${index}`"
            class="mybp-telefone-item"
        >
            <div class="row align-items-end">
                <div class="col-auto" v-if="!disabled && model.length > qnt_min">
                    <div class="form-group mybp-filtro-campo mb-0">
                        <label class="mybp-label">&nbsp;</label>
                        <button
                            class="btn btn-sm btn-danger"
                            type="button"
                            title="Remover telefone"
                            @click.prevent="remove(index)"
                        >
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>

                <div :class="detalhe || pais || ramal ? 'col-12 col-md-3' : 'col-12 col-md-4'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`tel-tipo-${hash}-${index}`">
                            Tipo <span class="text-danger">*</span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                :instance-id="`tel-tipo-${hash}-${index}`"
                                :input-id="`tel-tipo-${hash}-${index}`"
                                v-model="tel.tipo"
                                :options="opcoesTipo"
                                :disabled="disabled"
                                placeholder-blur="Selecione..."
                                empty-message="Nenhuma opção."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes(`tel-tipo-${hash}-${index}`)"
                                @select="limparComboboxInvalido(`tel-tipo-${hash}-${index}`)"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-2" v-if="pais">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`tel-pais-${hash}-${index}`">País</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text">+</span>
                            </div>
                            <input
                                :id="`tel-pais-${hash}-${index}`"
                                type="text"
                                class="form-control form-control-sm"
                                :disabled="disabled"
                                v-model="tel.pais"
                            />
                        </div>
                    </div>
                </div>

                <div :class="detalhe || pais || ramal ? 'col-12 col-md-3' : 'col-12 col-md-5'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`tel-numero-${hash}-${index}`">
                            Número <span class="text-danger">*</span>
                        </label>
                        <input
                            :id="`tel-numero-${hash}-${index}`"
                            type="text"
                            class="form-control form-control-sm"
                            :disabled="disabled"
                            v-mascara:telefone
                            onblur="valida_telefone_vazio(this)"
                            v-model="tel.numero"
                        />
                    </div>
                </div>

                <div class="col-6 col-md-2" v-if="ramal">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`tel-ramal-${hash}-${index}`">Ramal</label>
                        <input
                            :id="`tel-ramal-${hash}-${index}`"
                            type="text"
                            class="form-control form-control-sm"
                            :disabled="disabled"
                            v-model="tel.ramal"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-3" v-if="detalhe">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" :for="`tel-obs-${hash}-${index}`">Obs</label>
                        <input
                            :id="`tel-obs-${hash}-${index}`"
                            type="text"
                            class="form-control form-control-sm"
                            :disabled="disabled"
                            v-model="tel.detalhe"
                        />
                    </div>
                </div>

                <div :class="detalhe || pais || ramal ? 'col-12 col-md-2' : 'col-12 col-md-3'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label">&nbsp;</label>
                        <div class="custom-control custom-switch mybp-telefone-principal">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                :id="`tel-principal-${hash}-${index}`"
                                :checked="!!tel.principal"
                                :disabled="disabled || lista.length <= 1"
                                @click.prevent="definirPrincipal(tel)"
                            />
                            <label
                                class="custom-control-label"
                                :for="`tel-principal-${hash}-${index}`"
                                :title="lista.length <= 1 ? 'Único telefone — sempre principal' : ''"
                            >Principal</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p v-if="!lista.length" class="text-muted small mb-0 mybp-telefone-vazio">
            Nenhum telefone cadastrado.
        </p>
    </div>
</template>

<script>
import ComboboxAutoComplete from './ComboboxAutoComplete.vue'
import ComboboxValidation from '../mixins/ComboboxValidation'

export default {
    name: 'Telefones',
    components: { ComboboxAutoComplete },
    mixins: [ComboboxValidation],
    props: {
        model: {
            type: Array,
            required: true,
            default: () => []
        },
        modelDelete: {
            type: Array,
            required: false,
            default: () => []
        },
        ramal: {
            type: Boolean,
            default: true
        },
        pais: {
            type: Boolean,
            default: true
        },
        detalhe: {
            type: Boolean,
            default: true
        },
        qnt_min: {
            type: Number,
            default: 0,
            required: false
        },
        qnt_max: {
            type: Number,
            required: false,
            default: 99999
        },
        principal: {
            type: Boolean,
            required: false,
            default: false
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    data() {
        return {
            hash: parseInt(Math.random() * 999999, 10)
        }
    },
    computed: {
        lista() {
            return this.model
        },
        listaDelete() {
            return this.modelDelete
        },
        opcoesTipo() {
            return [
                { value: 'whatsapp', label: 'WhatsApp' },
                { value: 'celular', label: 'Celular' },
                { value: 'residencial', label: 'Residencial' },
                { value: 'comercial', label: 'Comercial' }
            ]
        }
    },
    watch: {
        model: {
            immediate: true,
            deep: true,
            handler() {
                this.garantirPrincipal()
            }
        }
    },
    methods: {
        fecharOutrosComboboxes() {},
        garantirPrincipal() {
            if (!this.lista || !this.lista.length) return

            if (this.lista.length === 1) {
                if (!this.lista[0].principal) {
                    this.lista[0].principal = true
                }
                return
            }

            const principais = this.lista.filter((t) => !!t.principal)
            if (principais.length === 1) return

            if (principais.length === 0) {
                this.lista[0].principal = true
                return
            }

            let manteve = false
            this.lista.forEach((t) => {
                if (t.principal && !manteve) {
                    manteve = true
                    return
                }
                t.principal = false
            })
        },
        definirPrincipal(tel) {
            if (this.disabled || !this.lista.length) return

            if (this.lista.length === 1) {
                tel.principal = true
                return
            }

            // Sempre troca o principal; não permite desmarcar sem escolher outro
            this.lista.forEach((obj) => {
                obj.principal = false
            })
            tel.principal = true
        },
        add() {
            const primeiro = this.lista.length === 0
            this.lista.push({
                id: 0,
                nova: true,
                tipo: 'residencial',
                pais: '55',
                numero: '',
                ramal: '',
                detalhe: '',
                principal: primeiro
            })
            this.garantirPrincipal()
        },
        remove(index) {
            this.$emit('ondelete', this.lista[index])
            if (this.lista[index].id) {
                this.listaDelete.push(this.lista[index].id)
            }
            this.lista.splice(index, 1)
            this.garantirPrincipal()
        },
        validarCampos() {
            if (this.disabled) return true

            this.garantirPrincipal()

            if (this.lista.length < this.qnt_min) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', `Informe pelo menos ${this.qnt_min} telefone(s)`)
                }
                return false
            }

            if (this.lista.length > 0 && !this.lista.some((t) => !!t.principal)) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'Defina um telefone principal')
                }
                return false
            }

            for (let i = 0; i < this.lista.length; i++) {
                const tel = this.lista[i]
                if (
                    !this.exigirCombobox(tel.tipo, `tel-tipo-${this.hash}-${i}`, {
                        toastMsg: 'Selecione o tipo do telefone'
                    })
                ) {
                    return false
                }
                if (!String(tel.numero || '').trim()) {
                    const el = document.getElementById(`tel-numero-${this.hash}-${i}`)
                    if (el && typeof valida_telefone_vazio === 'function') valida_telefone_vazio(el)
                    if (typeof mostraErro === 'function') mostraErro('', 'Informe o número do telefone')
                    return false
                }
            }

            return this.validarInputsAtivosVisiveis(`telefones-${this.hash}`, { preservarIds: [] })
        }
    }
}
</script>

<style scoped>
.mybp-telefone-item {
    padding-bottom: 0.35rem;
    margin-bottom: 0.55rem;
    border-bottom: 1px dashed #dee2e6;
}

.mybp-telefone-item:last-of-type {
    border-bottom: 0;
    margin-bottom: 0;
}

.mybp-telefone-principal {
    padding-top: 0.2rem;
    min-height: var(--mybp-fc-ctrl-h, 1.625rem);
}

.mybp-telefone-principal .custom-control-label {
    font-size: var(--mybp-fc-ctrl-fs, 0.6875rem);
    line-height: 1.25rem;
}

.mybp-telefone-vazio {
    font-size: var(--mybp-fc-label-fs, 0.7rem);
}
</style>
