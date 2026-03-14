<template>
    <div>
        <p class="mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
        <div v-if="!preload" id="formPesquisaClima">
            <div class="alert alert-warning alert-dismissible">
                <h6 class="text-center">
                    <strong><i class="fa fa-exclamation-triangle"></i> Atenção, responda todas as perguntas.</strong><br />
                </h6>
            </div>

            <fieldset v-for="(item, index) in form" :key="index">
                <legend>Pergunta {{ parseInt(index) + 1 }}</legend>
                <div class="form-group" v-if="item.id !== 43">
                    <label>{{ item.pergunta }}</label>

                    <div class="clearfix"></div>

                    <div class="form-check form-check-inline" v-for="res in item.resposta" :key="res.id">
                        <input
                            class="form-check-input"
                            v-model="item.respostacandidato"
                            type="radio"
                            :id="res.id"
                            :name="res.id"
                            :value="res.id"
                            onblur="valida_select(this)"
                        />
                        <label class="form-check-label" style="margin-top: 3px" :for="res.id">{{ res.resposta }}</label>
                    </div>
                </div>
                <div class="form-group" v-if="item.id === 43">
                    <label>{{ item.pergunta }}</label>
                    <textarea class="form-control" type="text" cols="3" v-model="item.respostacandidato"></textarea>
                </div>
            </fieldset>
        </div>
        <button @click="salvar" class="btn btn-primary"><i class="fa fa-save"></i> Salvar</button>
    </div>
</template>
<script>
import modal from '../Modal'
import upload from '../Upload'
import telefones from '../Telefones'

export default {
    components: {
        modal,
        upload,
        telefones
    },
    props: {
        curriculo: {
            type: Object,
            required: true
        },

        qntPag: {
            type: Number,
            required: false,
            default: 20
        }
    },

    mounted() {
        this.preload = true
        this.formDefault = _.cloneDeep(this.form)
        Object.assign(this.form, this.curriculo.cliente.pesquisa_clima_cliente.tipo.pesquisa_clima_pergunta)

        setInterval(() => {
            this.preload = false
        }, 200)
    },

    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Formulário Pesquisa Clima',

            preload: false,
            cadastrado: false,
            exibindo: false,
            mensagem: false,

            form: {}
        }
    },
    methods: {
        redirecionar() {
            window.location.href = `${URL_SITE}/pesquisaclima`
        },

        salvar() {
            $(`#formPesquisaClima :input:visible`).trigger('blur')
            if ($(`#formPesquisaClima :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros')
                return false
            }
            axios
                .post(`${URL_SITE}/pesquisaclima/`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.preload = false
                        mostraSucesso('Formulário Pesquisa de Clima concluído com sucesso!')
                        this.redirecionar()
                    }
                })
                .catch(error)
            //axios.put enviar formulário de edição dos dados do candidato e fazer a validação para saber se os campos estão preenchidos.
        }
    }
}
</script>

<style scoped>
.card {
    border: none;
    background: transparent;
}

ul.timeline {
    list-style-type: none;
    position: relative;
}

ul.timeline:before {
    content: ' ';
    background: #d4d9df;
    display: inline-block;
    position: absolute;
    left: 29px;
    width: 2px;
    height: 100%;
    z-index: 400;
}

ul.timeline > li {
    margin: 20px 0;
    padding-left: 20px;
}

ul.timeline > li:before {
    content: ' ';
    background: white;
    display: inline-block;
    position: absolute;
    border-radius: 50%;
    border: 3px solid #653132;
    left: 20px;
    width: 20px;
    height: 20px;
    z-index: 400;
}

.trackind {
    padding: 0.5rem 0.8rem;
    background-color: #f4f4f4;
    border-radius: 0.5rem;
}
</style>
