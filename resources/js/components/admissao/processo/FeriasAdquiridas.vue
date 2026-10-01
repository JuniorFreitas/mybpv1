<template>
    <div :id="`ferias-adq-${hash}`" class="mybp-ferias-adquiridas">
        <fieldset class="mybp-modal-secao">
            <legend>Férias</legend>

            <div class="mb-2" v-if="!visualizar">
                <button class="btn btn-sm btn-secondary" type="button" @click.prevent="add">
                    <span class="fas fa-plus" aria-hidden="true"></span>
                    Adicionar período
                </button>
            </div>

            <div
                v-for="(item, index) in lista"
                :key="item.id || `ferias-${index}`"
                class="mybp-lista-item"
            >
                <div class="mybp-lista-item__cabecalho">
                    <div class="mybp-lista-item__meta">
                        <span class="mybp-lista-item__titulo">Período {{ index + 1 }}</span>
                        <span
                            v-if="item.status"
                            class="mybp-lista-item__badge"
                            :class="`is-${item.status}`"
                        >{{ labelStatus(item.status) }}</span>
                    </div>
                    <button
                        v-if="!visualizar && item.status === 'aguardando'"
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
                            <label class="mybp-label" :for="`fer-periodo-${hash}-${index}`">
                                Período aquisitivo <span class="text-danger">*</span>
                            </label>
                            <input
                                :id="`fer-periodo-${hash}-${index}`"
                                type="text"
                                class="form-control form-control-sm"
                                v-mascara:per_aquisitivo
                                :disabled="itemBloqueado(item)"
                                placeholder="2020/2021"
                                v-model="item.periodo_gozado"
                                @blur="onBlurObrigatorio($event, 9)"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`fer-dias-${hash}-${index}`">
                                Quantidade de dias <span class="text-danger">*</span>
                            </label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`fer-dias-${hash}-${index}`"
                                    :input-id="`fer-dias-${hash}-${index}`"
                                    v-model="item.qnt_dias"
                                    :options="opcoesDias"
                                    :disabled="itemBloqueado(item)"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="30"
                                    @opening="fecharOutrosComboboxes(`fer-dias-${hash}-${index}`)"
                                    @select="onSelectDias(item, index)"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                            <label class="mybp-label">Data de saída <span class="text-danger">*</span></label>
                            <datepicker
                                :id="`fer-saida-${hash}-${index}`"
                                label=""
                                formsm
                                class="corrigiDatepicker"
                                v-model="item.data_saida"
                                :disabled="itemBloqueado(item)"
                                @onselect="dataRetorno(item, index)"
                            ></datepicker>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`fer-retorno-${hash}-${index}`">Data de retorno</label>
                            <input
                                :id="`fer-retorno-${hash}-${index}`"
                                type="text"
                                class="form-control form-control-sm"
                                placeholder="dd/mm/aaaa"
                                v-model="item.data_retorno"
                                v-mascara:data
                                readonly
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`fer-prox-${hash}-${index}`">
                                Próximo período <span class="text-danger">*</span>
                            </label>
                            <input
                                :id="`fer-prox-${hash}-${index}`"
                                type="text"
                                class="form-control form-control-sm"
                                v-mascara:per_aquisitivo
                                :disabled="itemBloqueado(item)"
                                placeholder="2021/2022"
                                v-model="item.proximo_periodo"
                                @blur="onBlurObrigatorio($event, 9)"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                            <label class="mybp-label">Data limite</label>
                            <datepicker
                                :id="`fer-limite-${hash}-${index}`"
                                label=""
                                formsm
                                class="corrigiDatepicker"
                                v-model="item.data_limite"
                                :disabled="itemBloqueado(item)"
                            ></datepicker>
                        </div>
                    </div>
                </div>
            </div>

            <p v-if="!lista.length" class="text-muted small mb-0 mybp-lista-vazia">
                Nenhum período de férias cadastrado.
            </p>
        </fieldset>
    </div>
</template>

<script>
import Validacoes from '../../../mixins/Validacoes'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import ComboboxValidation from '../../../mixins/ComboboxValidation'

export default {
    name: 'FeriasAdquiridas',
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
        opcoesDias() {
            const opts = []
            for (let cont = 5; cont <= 30; cont++) {
                opts.push({ value: cont, label: String(cont) })
            }
            return opts
        }
    },
    methods: {
        fecharOutrosComboboxes() {},
        labelStatus(status) {
            const map = {
                aguardando: 'Aguardando',
                gozando: 'Gozando',
                gozada: 'Gozada'
            }
            return map[status] || status
        },
        itemBloqueado(item) {
            return this.visualizar || item.status === 'gozada' || item.status === 'gozando'
        },
        onBlurObrigatorio(event, min) {
            if (!event || !event.target || this.visualizar) return
            if (typeof valida_campo_vazio === 'function') valida_campo_vazio(event.target, min)
        },
        onSelectDias(item, index) {
            this.limparComboboxInvalido(`fer-dias-${this.hash}-${index}`)
            this.dataRetorno(item, index)
        },
        add() {
            const dataAtual = new Date()
            const dia = dataAtual.getDate()
            const mes = dataAtual.getMonth()
            const ano = dataAtual.getFullYear()
            const dataHoje = `${this.padTo2Digits(dia)}/${this.padTo2Digits(mes + 1)}/${ano}`
            const dataLimite = `${this.padTo2Digits(dia)}/${this.padTo2Digits(mes + 1)}/${ano + 1}`

            this.lista.push({
                nova: true,
                periodo_gozado: '',
                qnt_dias: 5,
                data_saida: dataHoje,
                data_retorno: dataHoje,
                proximo_periodo: '',
                data_limite: dataLimite,
                status: 'aguardando'
            })

            this.dataRetorno(this.lista[this.lista.length - 1], this.lista.length - 1)
        },
        dataRetorno(obj, index) {
            if (!obj || !obj.data_saida || !String(obj.data_saida).includes('/')) return
            const data_saida = obj.data_saida.split('/')
            if (data_saida.length !== 3) return
            const data_saida_convert = `${data_saida[2]}-${data_saida[1]}-${data_saida[0]}`
            const data_retorno = new Date(data_saida_convert)
            if (Number.isNaN(data_retorno.getTime())) return
            data_retorno.setDate(data_retorno.getDate() + Number(obj.qnt_dias || 0))

            this.model[index].data_retorno =
                `${this.padTo2Digits(data_retorno.getDate())}/${this.padTo2Digits(data_retorno.getMonth() + 1)}/${data_retorno.getFullYear()}`
        },
        padTo2Digits(num) {
            return num.toString().padStart(2, '0')
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
                const item = this.lista[i]
                if (this.itemBloqueado(item)) continue

                if (!String(item.periodo_gozado || '').trim()) {
                    const el = document.getElementById(`fer-periodo-${this.hash}-${i}`)
                    if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 9)
                    if (typeof mostraErro === 'function') mostraErro('', 'Informe o período aquisitivo')
                    return false
                }
                if (
                    !this.exigirCombobox(item.qnt_dias, `fer-dias-${this.hash}-${i}`, {
                        toastMsg: 'Selecione a quantidade de dias'
                    })
                ) {
                    return false
                }
                if (!String(item.data_saida || '').trim()) {
                    if (typeof mostraErro === 'function') mostraErro('', 'Informe a data de saída')
                    return false
                }
                if (!String(item.proximo_periodo || '').trim()) {
                    const el = document.getElementById(`fer-prox-${this.hash}-${i}`)
                    if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 9)
                    if (typeof mostraErro === 'function') mostraErro('', 'Informe o próximo período')
                    return false
                }
            }

            return this.validarInputsAtivosVisiveis(`ferias-adq-${this.hash}`, {
                preservarIds: this.lista.map((_, i) => `fer-saida-${this.hash}-${i}`)
            })
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

.mybp-lista-item__meta {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.mybp-lista-item__titulo {
    font-size: 0.72rem;
    font-weight: 600;
    color: #495057;
    letter-spacing: 0.01em;
}

.mybp-lista-item__badge {
    font-size: 0.62rem;
    font-weight: 600;
    line-height: 1;
    padding: 0.2rem 0.4rem;
    border-radius: 999px;
    background: #e9ecef;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.mybp-lista-item__badge.is-aguardando {
    background: #fff3cd;
    color: #856404;
}

.mybp-lista-item__badge.is-gozando {
    background: #cce5ff;
    color: #004085;
}

.mybp-lista-item__badge.is-gozada {
    background: #d4edda;
    color: #155724;
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
