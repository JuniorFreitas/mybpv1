<template>
    <div>
        <p class="mt-2" v-if="preload"><i class="fa fa-spinner fa-pulse"></i> Aguarde ...</p>
        <modal :fechar="!preloadSalvar" id="janelaMeta" size="g" modal-pai="janelaHistorico" titulo="Meta">
            <template #conteudo>
                <p class="mt-2" v-if="preloadSalvar"><i class="fa fa-spinner fa-pulse"></i> Salvando aguarde ...</p>
                <fieldset v-show="!preloadSalvar">
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            <label>Nome</label>
                            <input type="text" v-model="form.nome" class="form-control" onblur="valida_campo_vazio(this, 1)" />
                        </div>
                        <div class="col-12">
                            <label>Descrição</label>
                            <textarea type="text" cols="3" v-model="form.descricao" class="form-control" onblur="valida_campo_vazio(this, 2)"></textarea>
                        </div>
                        <div class="col-6">
                            <date-picker formsm label="Data Início" v-model="form.data_inicio"></date-picker>
                        </div>
                        <div class="col-6">
                            <date-picker formsm label="Data Fim" v-model="form.data_fim"></date-picker>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button class="btn btn-sm btn-primary" v-if="!preloadSalvar" @click="salvar"><i class="fa fa-save"></i> Salvar</button>
            </template>
        </modal>

        <div v-if="!preload" :id="`form_${hash}`">
            <button class="btn btn-sm btn-primary mb-3" data-toggle="modal" data-target="#janelaMeta" @click="addMeta">
                <i class="fa fa-plus"></i> Adicionar Meta
            </button>

            <fieldset v-if="meta.length > 0">
                <legend>Metas</legend>
                <div class="table-responsive">
                    <table class="tabela">
                        <thead>
                            <tr class="bg-default">
                                <td class="text-center">Nome</td>
                                <td class="text-center">Descrição</td>
                                <td class="text-center">Data Início</td>
                                <td class="text-center">Data Fim</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in meta">
                                <td class="text-center">{{ item.nome }}</td>
                                <td class="text-center">{{ item.descricao }}</td>
                                <td class="text-center">{{ item.data_inicio }}</td>
                                <td class="text-center">{{ item.data_fim }}</td>
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

export default {
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

            meta: [],

            form: {
                feedback_id: '',
                nome: '',
                descricao: '',
                data_inicio: '',
                data_fim: ''
            },
            formDefault: null
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    methods: {
        addMeta() {
            this.form = _.cloneDeep(this.formDefault)
            this.form.feedback_id = this.feedback_id
            this.preloadSalvar = false
            formReset()
            setupCampo()
        },
        salvar() {
            $(`#janelaMeta :input:visible`).trigger('blur')
            if ($(`#janelaMeta :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros.')
                return false
            }

            this.preloadSalvar = true
            //criar
            axios
                .post(`${URL_ADMIN}/historico/meta/${this.feedback_id}`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.preloadSalvar = false
                        mostraSucesso('Meta adicionada com sucesso.')
                        $('#janelaMeta').modal('hide')
                        this.form = _.cloneDeep(this.formDefault)
                        // this.cadastrado = true;
                        this.atualizar()
                    }
                })
                .catch((error) => (this.preloadSalvar = false))
        },
        atualizar() {
            this.preload = true
            axios.get(`${URL_ADMIN}/historico/meta/atualizar/${this.feedback_id}`).then((res) => {
                let data = res.data
                this.form.feedback_id = data.feedback
                this.meta = data.metas
                this.formDefault = _.cloneDeep(this.form)
                this.preload = false
            })
        }
    }
}
</script>

<style scoped></style>
