<template>
    <div>
        <p class="mt-2" v-if="preload"><i class="fa fa-spinner fa-pulse"></i> Aguarde ...</p>
        <modal ref="modalAvaliacao" :fechar="!preloadSalvar" id="janelaAvaliacao" size="g" modal-pai="janelaHistorico" titulo="Avaliacao Anual">
            <template #conteudo>
                <p class="mt-2" v-if="preloadSalvar"><i class="fa fa-spinner fa-pulse"></i> Salvando aguarde ...</p>
                <fieldset class="mb-2" v-show="!preloadSalvar" v-for="obj in form.topicos" :key="obj.id || obj.nome">
                    <legend>{{ obj.nome }}</legend>
                    <div class="form-group" v-for="pergunta in obj.perguntas" :key="pergunta.id || pergunta.pergunta">
                        <label><span v-html="pergunta.pergunta"></span></label>
                        <div>
                            <select class="form-control" v-model="pergunta.nota" onchange="valida_campo_vazio(this, 1)" onblur="valida_campo_vazio(this, 1)">
                                <option value="">Selecione a nota</option>
                                <option v-for="item in 5" :key="item">{{ item }}</option>
                            </select>
                        </div>
                    </div>
                </fieldset>
                <fieldset v-show="!preloadSalvar">
                    <legend>Outras Informações</legend>
                    <div class="form-group">
                        <label>Gestor Imediato</label>
                        <input type="text" class="form-control" onblur="valida_campo_vazio(this, 1)" v-model="form.gestor_imediato" />
                    </div>
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea type="text" class="form-control" v-model="form.observacao"></textarea>
                    </div>
                </fieldset>
            </template>
            <template #rodape>
                <button class="btn btn-primary" v-if="!preloadSalvar" @click="salvar"><i class="fa fa-save"></i> Salvar</button>
            </template>
        </modal>

        <div v-if="!preload" :id="`form_${hash}`">
            <button class="btn btn-primary mb-3" @click="addFNDias"><i class="fa fa-plus"></i> Adicionar Avaliação Anual</button>

            <div class="table-responsive" v-if="tabela.length > 0">
                <table class="table table-bordered table-hover table-condensed">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Avaliação</td>
                            <td class="text-center">Avaliado em</td>
                            <td class="text-center">PDF</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in tabela" :key="item.id || item.quantidade_avaliacao">
                            <td class="text-center">{{ item.quantidade_avaliacao }}ª</td>
                            <td class="text-center">{{ item.created_at }}</td>
                            <td class="text-center">
                                <button class="btn btn-outline-default" @click="gerarPdf(item)"><i class="fas fa-file-pdf"></i> GERAR PDF</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
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
    components: {},
    data() {
        return {
            preload: false,
            preloadSalvar: false,
            URL_ADMIN,

            topicos: [],

            tabela: [],

            form: {
                gestor_imediato: '',
                feedback_id: '',
                observacao: '',
                topicos: []
            }
        }
    },
    mounted() {
        this.atualizar()
    },
    methods: {
        addFNDias() {
            this.form.topicos = _.cloneDeep(this.topicos)
            this.form.gestor_imediato = ''
            this.form.observacao = ''
            this.form.feedback_id = this.feedback_id
            this.preloadSalvar = false
            formReset()
            setupCampo()
            if (this.$refs && this.$refs.modalAvaliacao && typeof this.$refs.modalAvaliacao.abrirModal === 'function') {
                this.$refs.modalAvaliacao.abrirModal()
            }
        },
        salvar: function () {
            formReset()
            $(`#janelaAvaliacao :input:visible`).trigger('blur')
            if ($(`#janelaAvaliacao :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros.')
                return false
            }

            this.preloadSalvar = true
            //criar
            axios
                .post(`${URL_ADMIN}/historico/avaliacao-anual/${this.feedback_id}`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.preloadSalvar = false
                        mostraSucesso('Formulário de avaliação anual foi criado com sucesso.')
                        if (this.$refs && this.$refs.modalAvaliacao && typeof this.$refs.modalAvaliacao.fecharModal === 'function') {
                            this.$refs.modalAvaliacao.fecharModal()
                        }
                        // this.cadastrado = true;
                        this.atualizar()
                    }
                })
                .catch((error) => (this.preloadSalvar = false))
        },
        gerarPdf(item) {
            let link = `${URL_ADMIN}/historico/avaliacao-anual/${item.quantidade_avaliacao}/${item.feedback_id}/pdf`
            open(link, 'blank')
        },
        async atualizar() {
            this.preload = true
            this.perguntas = []
            try {
                const res = await axios.get(`${URL_ADMIN}/historico/avaliacao-anual/${this.feedback_id}`)
                const data = res.data
                this.topicos = data.topicos
                this.tabela = data.tabela
                this.form.topicos = _.cloneDeep(this.topicos)
            } finally {
                this.preload = false
            }
        }
    }
}
</script>

<style scoped></style>
