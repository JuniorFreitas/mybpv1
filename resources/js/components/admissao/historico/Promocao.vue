<template>
    <div>
        <p class="mt-2" v-if="preload"><i class="fa fa-spinner fa-pulse"></i> Aguarde ...</p>
        <modal :fechar="!preloadSalvar" id="janelaPromocao" size="g" modal-pai="janelaHistorico" titulo="Promoção" ref="modal_janelaPromocao">
            <template #conteudo>
                <p class="mt-2" v-if="preloadSalvar"><i class="fa fa-spinner fa-pulse"></i> Salvando aguarde ...</p>
                <fieldset v-show="!preloadSalvar">
                    <legend>Outras Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Novo Cargo</label>
                                <input
                                    type="text"
                                    v-model="form.novo_cargo"
                                    class="form-control validacampo"
                                    @blur.prevent="valida_campo_vazio($event.target, 1)"
                                    @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                />
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Salário</label>
                                <input
                                    type="text"
                                    v-model="form.novo_salario"
                                    class="form-control validacampo"
                                    v-mascara:dinheiro
                                    @blur.prevent="valida_dinheiro($event.target)"
                                    @keyup.prevent="valida_dinheiro($event.target)"
                                />
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Percentual</label>
                                <input
                                    type="text"
                                    v-model="form.percentual"
                                    v-mascara:pct
                                    class="form-control validacampo"
                                    @blur.prevent="valida_campo_vazio($event.target, 1)"
                                    @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                />
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select
                                    v-model="form.tipo"
                                    class="custom-select validacampo"
                                    @blur.prevent="valida_campo_vazio($event.target, 1)"
                                    @change.prevent="valida_campo_vazio($event.target, 1)"
                                >
                                    <option value="">Selecione</option>
                                    <option value="promocao">Promoção</option>
                                    <option value="reajuste">Reajuste</option>
                                    <option value="acordocoletivo">Acordo Coletivo</option>
                                    <option value="merito">Mérito</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Motivo da Promoção</label>
                                <textarea
                                    type="text"
                                    rows="3"
                                    v-model="form.motivo"
                                    class="form-control validacampo"
                                    @blur.prevent="valida_campo_vazio($event.target, 1)"
                                    @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button class="btn btn-sm mr-1 btn-primary" v-if="!preloadSalvar" @click="salvar"><i class="fa fa-save"></i> Salvar</button>
            </template>
        </modal>

        <div v-if="!preload" :id="`form_${hash}`">
            <button class="btn btn-sm mr-1 btn-primary mb-3" @click="addPromocao; $refs.modal_janelaPromocao && $refs.modal_janelaPromocao.abrirModal()">
                <i class="fa fa-plus"></i> Adicionar Promoção
            </button>

            <fieldset v-if="promocao.length > 0">
                <legend>Promoções</legend>
                <div class="table-responsive">
                    <table class="tabela">
                        <thead>
                            <tr class="bg-default">
                                <td class="text-center">Novo Cargo</td>
                                <td class="text-center">Motivo</td>
                                <td class="text-center">Percentual</td>
                                <td class="text-center">Tipo</td>
                                <td class="text-center">Tipo</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in promocao" :key="item.id || item.novo_cargo">
                                <td class="text-center">{{ item.novo_cargo }}</td>
                                <td class="text-center">{{ item.motivo }}</td>
                                <td class="text-center">{{ item.percentual }}</td>
                                <td class="text-center">{{ item.tipo }}</td>
                                <td class="text-center">{{ item.tipo }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
</template>

<script>
import DatePicker from '../../DatePicker'
import Validacoes from '../../../mixins/Validacoes'

export default {
    mixins: [Validacoes],
    props: {
        feedback_id: {
            type: Number,
            required: true
        },
        model: {
            type: Array
        },
        hash: {
            type: String,
            default: `mastertag_${parseInt(Math.random() * 999999)}`
        }
    },
    components: {
        DatePicker
    },
    data() {
        return {
            preload: false,
            preloadSalvar: false,
            URL_ADMIN,

            promocao: [],

            form: {
                feedback_id: '',
                novo_cargo: '',
                novo_salario: '0,00',
                motivo: '',
                percentual: 0.0,
                tipo: ''
            },
            formDefault: null
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },

    methods: {
        addPromocao() {
            this.form = _.cloneDeep(this.formDefault)
            this.form.feedback_id = this.feedback_id
            this.preloadSalvar = false
            formReset()
            setupCampo()
        },
        salvar() {
            this.validaBlur()
            this.$nextTick(() => {
                $(`#janelaPromocao :input:visible`).trigger('blur')
                if ($(`#janelaPromocao :input:visible.is-invalid`).length) {
                    mostraErro('', 'Verifique os erros.')
                    return false
                }

                this.preloadSalvar = true
                //criar
                axios
                    .post(`${URL_ADMIN}/historico/promocao/${this.feedback_id}`, this.form)
                    .then((response) => {
                        if (response.status === 201) {
                            this.preloadSalvar = false
                            mostraSucesso('Promoção adicionada com sucesso.')
                            this.$refs.modal_janelaPromocao && this.$refs.modal_janelaPromocao.fecharModal()
                            this.form = _.cloneDeep(this.formDefault)
                            // this.cadastrado = true;
                            this.atualizar()
                        }
                    })
                    .catch((error) => (this.preloadSalvar = false))
            })
        },
        async atualizar() {
            this.preload = true
            try {
                const res = await axios.get(`${URL_ADMIN}/historico/promocao/atualizar/${this.feedback_id}`)
                const data = res.data
                this.form.feedback_id = data.feedback
                this.promocao = data.promocoes
                this.formDefault = _.cloneDeep(this.form)
            } finally {
                this.preload = false
            }
        }
    }
}
</script>

<style scoped></style>
