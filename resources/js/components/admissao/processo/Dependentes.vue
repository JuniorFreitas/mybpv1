<template>
    <div :id="`dependentes-${hash}`" class="mybp-dependentes">
        <fieldset class="mybp-modal-secao">
            <legend>Dependentes</legend>

            <div class="mb-2" v-if="!visualizar">
                <button class="btn btn-sm btn-secondary" type="button" @click.prevent="add">
                    <span class="fas fa-plus" aria-hidden="true"></span>
                    Adicionar dependente
                </button>
            </div>

            <div
                v-for="(dependente, index) in lista"
                :key="dependente.id || `dep-${index}`"
                class="mybp-lista-item"
            >
                <div class="mybp-lista-item__cabecalho">
                    <span class="mybp-lista-item__titulo">Dependente {{ index + 1 }}</span>
                    <button
                        v-if="!visualizar"
                        class="btn btn-sm btn-outline-danger"
                        type="button"
                        @click.prevent="remove(index)"
                    >
                        <span class="fas fa-times" aria-hidden="true"></span>
                        Remover
                    </button>
                </div>

                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`dep-tipo-${hash}-${index}`">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`dep-tipo-${hash}-${index}`"
                                    :input-id="`dep-tipo-${hash}-${index}`"
                                    v-model="dependente.tipo"
                                    :options="opcoesTipo"
                                    :disabled="visualizar"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhum tipo."
                                    :max-results="20"
                                    @opening="fecharOutrosComboboxes(`dep-tipo-${hash}-${index}`)"
                                    @select="onSelectTipo(dependente, index)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-8">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`dep-nome-${hash}-${index}`">
                                Nome <span class="text-danger">*</span>
                            </label>
                            <input
                                :id="`dep-nome-${hash}-${index}`"
                                type="text"
                                class="form-control form-control-sm"
                                :disabled="visualizar"
                                placeholder="Nome completo"
                                v-model="dependente.nome"
                                @blur="onBlurObrigatorio($event, 2)"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4" v-show="dependente.tipo === 'outro'">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`dep-outro-${hash}-${index}`">
                                Especifique <span class="text-danger">*</span>
                            </label>
                            <input
                                :id="`dep-outro-${hash}-${index}`"
                                type="text"
                                class="form-control form-control-sm"
                                :disabled="visualizar"
                                placeholder="Tipo do dependente"
                                v-model="dependente.outro_tipo"
                                @blur="onBlurMin($event, 2)"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`dep-cpf-${hash}-${index}`">CPF</label>
                            <input
                                :id="`dep-cpf-${hash}-${index}`"
                                type="text"
                                class="form-control form-control-sm"
                                :disabled="visualizar"
                                placeholder="000.000.000-00"
                                v-model="dependente.cpf"
                                v-mascara:cpf
                                @blur="onBlurCpf($event)"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`dep-nasc-${hash}-${index}`">Nascimento</label>
                            <input
                                :id="`dep-nasc-${hash}-${index}`"
                                type="text"
                                class="form-control form-control-sm"
                                placeholder="dd/mm/aaaa"
                                v-model="dependente.nascimento"
                                v-mascara:data
                                :disabled="visualizar"
                                @keyup.prevent="valida_data($event.target, true)"
                                @blur.prevent="valida_data($event.target, true)"
                            />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`dep-obs-${hash}-${index}`">Observação</label>
                            <input
                                :id="`dep-obs-${hash}-${index}`"
                                type="text"
                                class="form-control form-control-sm"
                                :disabled="visualizar"
                                placeholder="Opcional"
                                v-model="dependente.observacao"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <p v-if="!lista.length" class="text-muted small mb-0 mybp-lista-vazia">
                Nenhum dependente cadastrado.
            </p>
        </fieldset>
    </div>
</template>

<script>
import Validacoes from '../../../mixins/Validacoes'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import ComboboxValidation from '../../../mixins/ComboboxValidation'

export default {
    name: 'Dependentes',
    components: { ComboboxAutoComplete },
    mixins: [Validacoes, ComboboxValidation],
    props: {
        model: {
            type: Array,
            required: true,
            default: () => []
        },
        modelDelete: {
            type: Array,
            required: true,
            default: () => []
        },
        visualizar: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            hash: parseInt(Math.random() * 999999, 10),
            tipos: {}
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
            return Object.keys(this.tipos || {}).map((key) => ({
                value: key,
                label: this.tipos[key]
            }))
        }
    },
    async mounted() {
        try {
            const response = await axios.get(`${URL_ADMIN}/admissao/tipos_dependentes`)
            this.tipos = response.data || {}
        } catch (e) {
            this.tipos = {}
        }
    },
    methods: {
        fecharOutrosComboboxes() {},
        onSelectTipo(dependente, index) {
            this.limparComboboxInvalido(`dep-tipo-${this.hash}-${index}`)
            if (dependente.tipo !== 'outro') {
                dependente.outro_tipo = ''
            }
        },
        onBlurObrigatorio(event, min) {
            if (!event || !event.target) return
            if (typeof valida_campo_vazio === 'function') valida_campo_vazio(event.target, min)
        },
        onBlurMin(event, min) {
            if (!event || !event.target) return
            if (typeof valida_campo === 'function') valida_campo(event.target, min)
        },
        onBlurCpf(event) {
            if (!event || !event.target) return
            if (typeof valida_cpf === 'function') valida_cpf(event.target)
        },
        add() {
            this.lista.push({
                nova: true,
                tipo: '',
                outro_tipo: '',
                nome: '',
                cpf: '',
                nascimento: '',
                observacao: ''
            })
        },
        remove(index) {
            this.$emit('ondelete', this.lista[index])
            if (!this.lista[index].nova) {
                this.listaDelete.push(this.lista[index].id)
            }
            this.lista.splice(index, 1)
        },
        validarCampos() {
            if (this.visualizar) return true
            if (!this.lista.length) return true

            for (let i = 0; i < this.lista.length; i++) {
                const dep = this.lista[i]
                if (
                    !this.exigirCombobox(dep.tipo, `dep-tipo-${this.hash}-${i}`, {
                        toastMsg: 'Selecione o tipo do dependente'
                    })
                ) {
                    return false
                }
                if (dep.tipo === 'outro' && !String(dep.outro_tipo || '').trim()) {
                    const el = document.getElementById(`dep-outro-${this.hash}-${i}`)
                    if (el && typeof valida_campo === 'function') valida_campo(el, 2)
                    if (typeof mostraErro === 'function') mostraErro('', 'Especifique o tipo do dependente')
                    return false
                }
                if (!String(dep.nome || '').trim()) {
                    const el = document.getElementById(`dep-nome-${this.hash}-${i}`)
                    if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 2)
                    if (typeof mostraErro === 'function') mostraErro('', 'Informe o nome do dependente')
                    return false
                }
            }

            return this.validarInputsAtivosVisiveis(`dependentes-${this.hash}`, { preservarIds: [] })
        }
    }
}
</script>

<style scoped>
.mybp-lista-item {
    padding: 0.65rem 0.75rem 0.25rem;
    margin-bottom: 0.65rem;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    background: #fafbfc;
}

.mybp-lista-item:last-of-type {
    margin-bottom: 0.25rem;
}

.mybp-lista-item__cabecalho {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.45rem;
    padding-bottom: 0.35rem;
    border-bottom: 1px solid #eef0f2;
}

.mybp-lista-item__titulo {
    font-size: 0.72rem;
    font-weight: 600;
    color: #495057;
    letter-spacing: 0.01em;
}

.mybp-lista-item__cabecalho .btn {
    padding: 0.1rem 0.45rem;
    font-size: 0.65rem;
    line-height: 1.3;
}

.mybp-lista-vazia {
    font-size: var(--mybp-fc-label-fs, 0.7rem);
}
</style>
