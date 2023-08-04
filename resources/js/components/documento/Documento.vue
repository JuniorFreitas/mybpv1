<template>
    <!--    <modal id='componenteDocumentos' label-fechar="Concluir" @fechou="redirecionar" :titulo="titulo_janela" :size="65">-->
    <!--        <template slot="conteudo">-->
    <div>
<!--        <p class=" mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>-->
        <preload :msg="salvando ? 'Salvando ... ' : 'Carregando ...'" v-if="preload"></preload>
        <div v-if="!preload && !salvando" id="formDocumentos">
            <fieldset>
                <legend>INFORMAÇÕES</legend>
                <div class="form-group">
                    <label>Nome</label>
                    <input v-model="formUser.nome" onblur="valida_campo_vazio(this,3)" class="form-control">
                </div>
                <div class="form-group">
                    <label>Nome da Mãe</label>
                    <input v-model="formUser.filiacao_mae" onblur="valida_campo_vazio(this,3)" class="form-control">
                </div>
                <div class="form-group">
                    <label>Nome do Pai</label>
                    <input v-model="formUser.filiacao_pai" onblur="valida_campo(this,3)" class="form-control">
                </div>
                <telefones :model="formUser.telefones" :model-delete="formUser.telefonesDelete" :ramal="false"
                           :pais="false" :qnt_min="1"></telefones>
                <button type="button" class="btn btn-success" v-show="formUser.telefones.length > 0"
                        @click="alterar">
                    Alterar
                </button>
            </fieldset>
            <div class="alert alert-info alert-dismissible">
                <h6 class="text-left">
                    <strong> Obs.: Os arquivos para anexar deve ser no formato PDF, JPG, PNG ou JPEG.</strong><br>
                </h6>
            </div>

            <div class="row">
                <div class="col-md-12" v-for="doc in formUser.docs_curriculo_pre_adm">
                    <fieldset v-if="!doc.configuracoes.sogestao">
                        <legend>{{ doc.label }}</legend>
                        <p v-html="doc.descricao"></p>
                        <upload label="Selecionar anexo(s)" :dados-ajax="{tipo: doc.tipo, curriculo_id: curriculo.id}"
                                :model="doc.docs_curriculo_anexos"
                                :model-delete="doc.docs_curriculo_anexosDelete" :url="urlAnexoUpload"
                                :apenas-imagens="doc.configuracoes.apenas_img"
                                :apenas-pdf="doc.configuracoes.apenas_pdf"
                                :apenas-pdf-img="doc.configuracoes.apenas_pdf_img"
                                @onprogresso="anexoUploadAndamento=true"
                                @onfinalizado="anexoUploadAndamento=false" :quantidade="doc.configuracoes.max" :multi="doc.configuracoes.multiple"></upload>
                    </fieldset>
                </div>
            </div>
        </div>
        <button @click="alterar" class="btn btn-primary"><i class="fa fa-save"></i> Salvar</button>
    </div>
    <!--        </template>-->
    <!--    </modal>-->
</template>

<script>
import modal from '../Modal';
import upload from '../Upload';
import telefones from "../Telefones"

export default {
    components: {
        modal,
        upload,
        telefones
    },
    props: {
        apelido: { // modal Pai
            type: String,
            required: true,
            default: ''
        },

        curriculo: {
            type: Object,
            required: true,
        },

        qntPag: {
            type: Number,
            required: false,
            default: 20
        },

        modal: { // modal Pai
            type: String,
            required: false,
            default: ''
        },
    },

    mounted() {
        this.preload = true;
        this.formDefault = _.cloneDeep(this.form);
        Object.assign(this.formUser, this.curriculo)
        this.formUser.docs_curriculo_pre_adm = this.curriculo.docs_curriculo_pre_adm
        this.preload = false;
        // setTimeout(() => {
        // this.formUser = this.curriculo;
        // }, 100)
    },

    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Documentos Pré Admissão',

            anexoUploadAndamento: false,
            urlAnexoUpload: `${URL_SITE}/documentos/uploadAnexos`,

            preload: false,
            cadastrado: false,
            exibindo: false,
            mensagem: false,
            salvando: false,

            formUser: {
                nome: '',
                nome_pai: '',
                nome_mae: '',
                telefones: [],
                telefonesDelete: [],

                docs_curriculo_pre_adm: []
            },
        }
    },
    methods: {

        redirecionar() {
            window.location.href = `${URL_SITE}/${this.apelido}/documentos`;
        },

        alterar() {
            $(`#formDocumentos :input:visible`).trigger('blur');
            if ($(`#formDocumentos :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros')
                return false;
            }
            this.salvando = true;
            this.preload = true;
            axios.put(`${URL_SITE}/${this.apelido}/documentos/${this.formUser.id}`, this.formUser)
                .then(response => {
                    if (response.status === 201) {
                        mostraSucesso('Suas informações foram alteradas com sucesso!');
                        setTimeout(() => {
                            this.redirecionar();
                        }, 2000)
                        // this.preload = false;
                    }
                })
                .catch(error => {
                    this.preload = false;
                    this.salvando = false;
                });
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
    padding: .5rem .8rem;
    background-color: #f4f4f4;
    border-radius: .5rem;
}
</style>
