<template>
    <div :id="`endereco-${hash}`" class="mybp-endereco">
        <div class="row">
            <div class="col-12 col-md-3">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" :for="`end-cep-${hash}`">
                        CEP
                        <span class="text-danger" v-if="obrigatorio">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <input
                            :id="`end-cep-${hash}`"
                            type="text"
                            class="form-control form-control-sm"
                            :disabled="preload || disabled"
                            v-mascara:cep
                            placeholder="00000-000"
                            v-model="model.cep"
                            @blur="onBlurCep($event)"
                            @keyup.enter.prevent="onClick"
                        />
                        <div class="input-group-append">
                            <button
                                class="btn btn-secondary btn-sm"
                                type="button"
                                title="Buscar CEP"
                                :disabled="preload || disabled"
                                @click.prevent="onClick"
                            >
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" :for="`end-logradouro-${hash}`">
                        Endereço
                        <span class="text-danger" v-if="obrigatorio">*</span>
                    </label>
                    <input
                        :id="`end-logradouro-${hash}`"
                        type="text"
                        class="form-control form-control-sm"
                        :disabled="preload || disabled"
                        placeholder="Logradouro"
                        v-model="model.logradouro"
                        @blur="onBlurObrigatorio($event, 3)"
                    />
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" :for="`end-numero-${hash}`">Número</label>
                    <input
                        :id="`end-numero-${hash}`"
                        type="text"
                        class="form-control form-control-sm"
                        :disabled="preload || disabled"
                        v-model="model.end_numero"
                    />
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" :for="`end-complemento-${hash}`">Complemento</label>
                    <input
                        :id="`end-complemento-${hash}`"
                        type="text"
                        class="form-control form-control-sm"
                        :disabled="preload || disabled"
                        v-model="model.complemento"
                    />
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" :for="`end-bairro-${hash}`">
                        Bairro
                        <span class="text-danger" v-if="obrigatorio">*</span>
                    </label>
                    <input
                        :id="`end-bairro-${hash}`"
                        type="text"
                        class="form-control form-control-sm"
                        :disabled="preload || disabled"
                        placeholder="Bairro"
                        v-model="model.bairro"
                        @blur="onBlurObrigatorio($event, 3)"
                    />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" :for="`end-municipio-${hash}`">
                        Município
                        <span class="text-danger" v-if="obrigatorio">*</span>
                    </label>
                    <input
                        :id="`end-municipio-${hash}`"
                        type="text"
                        class="form-control form-control-sm"
                        :disabled="preload || disabled"
                        placeholder="Cidade"
                        v-model="model.municipio"
                        @blur="onBlurObrigatorio($event, 3)"
                    />
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" :for="`end-uf-${hash}`">
                        UF
                        <span class="text-danger" v-if="obrigatorio">*</span>
                    </label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            :instance-id="`end-uf-${hash}`"
                            :input-id="`end-uf-${hash}`"
                            v-model="model.uf"
                            :options="opcoesUf"
                            :disabled="preload || disabled"
                            placeholder-blur="UF"
                            empty-message="Nenhuma UF."
                            :max-results="30"
                            @opening="fecharOutrosComboboxes(`end-uf-${hash}`)"
                            @select="limparComboboxInvalido(`end-uf-${hash}`)"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ComboboxAutoComplete from './ComboboxAutoComplete.vue'
import ComboboxValidation from '../mixins/ComboboxValidation'

const UF_OPTIONS = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
]

export default {
    name: 'Endereco',
    components: { ComboboxAutoComplete },
    mixins: [ComboboxValidation],
    props: {
        model: {
            type: Object,
            required: true,
            default: () => ({
                cep: '',
                logradouro: '',
                bairro: '',
                end_numero: '',
                complemento: '',
                bairro_id: 0,
                municipio: '',
                municipio_id: 0,
                uf: 'MA'
            })
        },
        disabled: {
            type: Boolean,
            default: false
        },
        select: {
            type: Boolean,
            required: false,
            default: false
        },
        obrigatorio: {
            type: Boolean,
            required: false,
            default: true
        }
    },
    data() {
        return {
            hash: parseInt(Math.random() * 999999, 10),
            preload: false,
            load: 'buscando ...',
            listaDeBairros: [],
            listaDeMunicipios: [{ nome: 'São Luís', id: 0 }]
        }
    },
    computed: {
        opcoesUf() {
            const base = UF_OPTIONS.map((uf) => ({ value: uf, label: uf }))
            const atual = this.model && this.model.uf
            if (atual && !base.some((o) => o.value === atual)) {
                base.unshift({ value: atual, label: String(atual) })
            }
            return base
        }
    },
    methods: {
        fecharOutrosComboboxes() {},
        onBlurCep(event) {
            if (!this.obrigatorio || !event || !event.target) return
            if (typeof valida_cep_vazio === 'function') valida_cep_vazio(event.target)
        },
        onBlurObrigatorio(event, min) {
            if (!this.obrigatorio || !event || !event.target) return
            if (typeof valida_campo_vazio === 'function') valida_campo_vazio(event.target, min)
        },
        trocaUF() {
            if (!this.select) return false
            this.preload = true
            $.post(`${URL_PUBLICO}/lista-municipios`, { UF: this.model.municipio.uf })
                .done((data) => {
                    this.preload = false
                    this.listaDeBairros = data.bairros
                    this.listaDeMunicipios = data.municipios
                    const encontrou = _.find(this.listaDeMunicipios, { id: this.model.municipio_id })
                    if (!encontrou) {
                        this.model.municipio_id = this.listaDeMunicipios[0].id
                    }
                })
                .fail(() => {
                    this.preload = false
                })
        },
        trocaMunicipio() {
            if (!this.select) return false
            this.preload = true
            $.post(`${URL_PUBLICO}/lista-bairros`, { id_municipio: this.model.municipio_id })
                .done((data) => {
                    this.preload = false
                    this.listaDeBairros = data.bairros
                    const encontrou = _.find(this.listaDeBairros, { id: this.model.bairro_id })
                    if (!encontrou) {
                        this.model.bairro_id = this.listaDeBairros[0].id
                    }
                })
                .fail(() => {
                    this.preload = false
                })
        },
        limparEndereco() {
            this.model.cep = ''
            this.model.logradouro = ''
            this.model.bairro = ''
            this.model.municipio = ''
            this.model.uf = ''
        },
        onClick() {
            if (!this.model.cep || String(this.model.cep).length < 9) return

            this.model.logradouro = this.load
            this.model.bairro = this.load
            this.model.municipio = this.load
            this.model.uf = this.load
            this.preload = true

            $.getJSON(`https://viacep.com.br/ws/${this.model.cep}/json/?callback=?`)
                .done((data) => {
                    this.preload = false
                    if (data.erro) {
                        this.limparEndereco()
                        return
                    }
                    this.model.logradouro = data.logradouro
                    this.model.bairro = data.bairro
                    this.model.municipio = data.localidade
                    this.model.uf = data.uf
                    this.limparComboboxInvalido(`end-uf-${this.hash}`)
                })
                .fail(() => {
                    this.preload = false
                    this.limparEndereco()
                })
        },
        validarCampos() {
            if (this.disabled || !this.obrigatorio) return true

            if (!String(this.model.cep || '').trim()) {
                const el = document.getElementById(`end-cep-${this.hash}`)
                if (el && typeof valida_cep_vazio === 'function') valida_cep_vazio(el)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe o CEP')
                return false
            }
            if (!String(this.model.logradouro || '').trim()) {
                const el = document.getElementById(`end-logradouro-${this.hash}`)
                if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 3)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe o endereço')
                return false
            }
            if (!String(this.model.bairro || '').trim()) {
                const el = document.getElementById(`end-bairro-${this.hash}`)
                if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 3)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe o bairro')
                return false
            }
            if (!String(this.model.municipio || '').trim()) {
                const el = document.getElementById(`end-municipio-${this.hash}`)
                if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 3)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe o município')
                return false
            }
            if (
                !this.exigirCombobox(this.model.uf, `end-uf-${this.hash}`, {
                    toastMsg: 'Selecione a UF'
                })
            ) {
                return false
            }

            return this.validarInputsAtivosVisiveis(`endereco-${this.hash}`, { preservarIds: [] })
        }
    }
}
</script>

<style scoped>
.mybp-endereco :deep(.input-group-sm > .input-group-append > .btn) {
    height: var(--mybp-fc-ctrl-h, 1.625rem);
    padding: 0 0.5rem;
    font-size: var(--mybp-fc-ctrl-fs, 0.6875rem);
    line-height: 1;
}
</style>
