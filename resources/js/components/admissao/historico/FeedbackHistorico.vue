<template>
    <div>
        <p class="mt-2" v-if="preload"><i class="fa fa-spinner fa-pulse"></i> Aguarde ...</p>
        <modal :fechar="!preloadSalvar" id="janelaFeedback" size="g" modal-pai="janelaHistorico" titulo="Feedback" ref="modal_janelaFeedback">
            <template #conteudo>
                <p class="mt-2" v-if="preloadSalvar"><i class="fa fa-spinner fa-pulse"></i> Salvando aguarde ...</p>
                <fieldset v-show="!preloadSalvar">
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label>Situação</label>
                            <input type="text" v-model="form.situacao" class="form-control form-control-sm" onblur="valida_campo_vazio(this, 1)" />
                        </div>
                        <div class="col-12 mb-2">
                            <label>Descrição</label>
                            <editor :api-key="tinySimples.key" v-model="form.descricao" :init="tinySimples"></editor>
                        </div>
                        <div class="col-6">
                            <date-picker formsm label="Data" :max="max" v-model="form.data"></date-picker>
                        </div>
                        <div class="col-12 mb-2">
                            <label>Compromisso</label>
                            <editor :api-key="tinySimples.key" v-model="form.compromisso" :init="tinySimples"></editor>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button class="btn btn-sm mr-1 btn-primary" v-if="!preloadSalvar" @click="salvar"><i class="fa fa-save"></i> Salvar</button>
            </template>
        </modal>

        <div v-if="!preload" :id="`form_${hash}`">
            <button class="btn btn-sm mr-1 btn-primary mb-3" @click="addFeedback; $refs.modal_janelaFeedback && $refs.modal_janelaFeedback.abrirModal()">
                <i class="fa fa-plus"></i> Adicionar Feedback
            </button>

            <fieldset v-if="feedback_historico.length > 0">
                <legend>Feedbacks</legend>
                <div class="table-responsive">
                    <table class="tabela">
                        <thead>
                            <tr class="bg-default">
                                <td class="text-center">Situação</td>
                                <td class="text-center">Descrição</td>
                                <td class="text-center">Compromisso</td>
                                <td class="text-center">Data</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in feedback_historico" :key="item.id || item.data">
                                <td class="text-center">{{ item.situacao }}</td>
                                <td class="text-center">
                                    <div v-html="item.descricao"></div>
                                </td>
                                <td class="text-center">
                                    <div v-html="item.compromisso"></div>
                                </td>
                                <td class="text-center">{{ item.data }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
</template>

<script>
import Utils from '../../../mixins/Utils'
import Validacoes from '../../../mixins/Validacoes'
import Editor from '@tinymce/tinymce-vue'
import DatePicker from '../../DatePicker'

export default {
    name: 'FeedbackHistorico',
    mixins: [Utils, Validacoes],
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
        DatePicker,
        Editor
    },
    data() {
        return {
            preload: false,
            preloadSalvar: false,
            URL_ADMIN,
            hoje: '',
            feedback_historico: [],
            form: {
                feedback_id: '',
                situacao: '',
                descricao: '',
                compromisso: '',
                data: ''
            },
            formDefault: null
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    computed: {
        max() {
            return moment(new Date(), 'DD/MM/YYYY').format('DD/MM/YYYY')
        }
    },
    methods: {
        addFeedback() {
            this.form = _.cloneDeep(this.formDefault)
            this.form.feedback_id = this.feedback_id
            this.preloadSalvar = false
            formReset()
            setupCampo()
        },
        salvar() {
            $(`#janelaFeedback :input:visible`).trigger('blur')
            if ($(`#janelaFeedback :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros.')
                return false
            }

            this.preloadSalvar = true
            //criar
            axios
                .post(`${URL_ADMIN}/historico/feedback-historico/${this.feedback_id}`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.preloadSalvar = false
                        mostraSucesso('Feedback adicionado com sucesso.')
                        this.$refs.modal_janelaFeedback && this.$refs.modal_janelaFeedback.fecharModal()
                        this.form = _.cloneDeep(this.formDefault)
                        // this.cadastrado = true;
                        this.atualizar()
                    }
                })
                .catch((error) => (this.preloadSalvar = false))
        },
        async atualizar() {
            this.preload = true
            try {
                const res = await axios.get(`${URL_ADMIN}/historico/feedback-historico/atualizar/${this.feedback_id}`)
                const data = res.data
                this.form.feedback_id = data.feedback
                this.feedback_historico = data.feedback_historico
                this.formDefault = _.cloneDeep(this.form)
                this.hoje = data.hoje
            } finally {
                this.preload = false
            }
        }
    }
}
</script>

<style scoped></style>
