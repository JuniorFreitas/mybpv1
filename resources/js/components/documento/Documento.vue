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
                    <input v-model="formUser.nome" onblur="valida_campo_vazio(this, 3)" class="form-control" />
                </div>
                <div class="form-group">
                    <label>Nome da Mãe</label>
                    <input v-model="formUser.filiacao_mae" onblur="valida_campo_vazio(this, 3)" class="form-control" />
                </div>
                <div class="form-group">
                    <label>Nome do Pai</label>
                    <input v-model="formUser.filiacao_pai" onblur="valida_campo(this, 3)" class="form-control" />
                </div>
                <telefones :model="formUser.telefones" :model-delete="formUser.telefonesDelete" :ramal="false" :pais="false" :qnt_min="1"></telefones>
            </fieldset>
            <div class="alert alert-info alert-dismissible">
                <h6 class="text-left"><strong> Obs.: Os arquivos para anexar deve ser no formato PDF, JPG, PNG ou JPEG.</strong><br /></h6>
            </div>

            <div class="row">
                <div class="col-md-12" v-for="doc in formUser.docs_curriculo_pre_adm" :key="doc.id || doc.tipo || doc.label">
                    <fieldset v-if="!(doc.configuracoes && doc.configuracoes.sogestao)">
                        <legend>{{ doc.label }}</legend>
                        <p v-html="doc.descricao"></p>
                        <upload
                            label="Selecionar anexo(s)"
                            :dados-ajax="{ tipo: doc.tipo, curriculo_id: curriculo.id }"
                            :model="doc.docs_curriculo_anexos"
                            :model-delete="doc.docs_curriculo_anexosDelete"
                            :url="urlAnexoUpload"
                            :apenas-imagens="(doc.configuracoes || {}).apenas_img"
                            :apenas-pdf="(doc.configuracoes || {}).apenas_pdf"
                            :apenas-pdf-img="(doc.configuracoes || {}).apenas_pdf_img"
                            @onprogresso="anexoUploadAndamento = true"
                            @onfinalizado="anexoUploadAndamento = false"
                            :quantidade="(doc.configuracoes || {}).max || 1"
                            :multi="(doc.configuracoes || {}).multiple"
                        ></upload>
                    </fieldset>
                </div>
            </div>
        </div>
        <section v-if="termo && !preload && !salvando" class="doc-termo" aria-labelledby="doc-termo-titulo">
            <h2 id="doc-termo-titulo" class="doc-termo__titulo">{{ termo.titulo }}</h2>
            <ol class="doc-termo__itens">
                <li v-for="(item, i) in termo.itens" :key="i">{{ item }}</li>
            </ol>
            <details class="doc-termo__privacidade">
                <summary>Aviso de privacidade (LGPD)</summary>
                <dl>
                    <template v-for="(bloco, i) in termo.privacidade" :key="i">
                        <dt>{{ bloco.titulo }}</dt>
                        <dd>{{ bloco.texto }}</dd>
                    </template>
                </dl>
            </details>
            <label class="doc-termo__aceite">
                <input type="checkbox" v-model="termoAceito" />
                <span>{{ termo.declaracao }}</span>
            </label>
            <p class="doc-termo__versao">Versão do termo: {{ termo.versao }}</p>
        </section>
        <button @click="alterar" class="btn btn-primary" :disabled="salvando || (termo && !termoAceito)">
            <i class="fa fa-save"></i> Salvar e enviar
        </button>
        <p v-if="termo && !termoAceito" class="doc-termo__dica">Marque o aceite do termo para enviar.</p>
    </div>
    <!--        </template>-->
    <!--    </modal>-->
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
        apelido: {
            // modal Pai
            type: String,
            required: true,
            default: ''
        },

        curriculo: {
            type: Object,
            required: true
        },

        qntPag: {
            type: Number,
            required: false,
            default: 20
        },

        // Termo de declaração + aviso de privacidade vigente ({ versao, hash, titulo, itens, privacidade, declaracao }).
        termo: {
            type: Object,
            required: false,
            default: null
        },

        modal: {
            // modal Pai
            type: String,
            required: false,
            default: ''
        }
    },

    mounted() {
        this.preload = true
        this.formDefault = _.cloneDeep(this.form)
        Object.assign(this.formUser, this.curriculo)
        this.formUser.docs_curriculo_pre_adm = this.curriculo.docs_curriculo_pre_adm
        this.preload = false
        // setTimeout(() => {
        // this.formUser = this.curriculo;
        // }, 100)
    },

    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Documentos',

            anexoUploadAndamento: false,
            urlAnexoUpload: `${URL_SITE}/documentos/uploadAnexos`,

            preload: false,
            cadastrado: false,
            exibindo: false,
            mensagem: false,
            salvando: false,
            termoAceito: false,

            formUser: {
                nome: '',
                nome_pai: '',
                nome_mae: '',
                telefones: [],
                telefonesDelete: [],

                docs_curriculo_pre_adm: []
            }
        }
    },
    methods: {
        redirecionar() {
            window.location.href = `${URL_SITE}/${this.apelido}/documentos`
        },

        alterar() {
            $(`#formDocumentos :input:visible`).trigger('blur')
            if ($(`#formDocumentos :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros')
                return false
            }
            this.salvando = true
            this.preload = true
            axios
                .put(`${URL_SITE}/${this.apelido}/documentos/${this.formUser.id}`, {
                    ...this.formUser,
                    termo_aceito: this.termoAceito,
                    termo_hash: this.termo ? this.termo.hash : null
                })
                .then((response) => {
                    if (response.status === 201) {
                        const protocolo = response.data && response.data.protocolo
                        mostraSucesso(
                            protocolo
                                ? `Documentos enviados. Protocolo ${protocolo}. O comprovante vai para o seu e-mail.`
                                : 'Suas informações foram alteradas com sucesso!'
                        )
                        setTimeout(() => {
                            this.redirecionar()
                        }, 4000)
                    }
                })
                .catch((error) => {
                    this.preload = false
                    this.salvando = false
                    mostraErro('', (error.response && error.response.data && error.response.data.msg) || 'Não foi possível enviar. Tente novamente.')
                })
        }
    }
}
</script>

<style scoped>
.doc-termo {
    margin: 1.25rem 0;
    padding: 1rem 1.1rem;
    border: 1px solid rgba(11, 27, 61, 0.14);
    border-radius: 0.8rem;
    background: #f4f6fa;
    font-size: 0.9rem;
}

.doc-termo__titulo {
    margin: 0 0 0.6rem;
    font-size: 1rem;
    font-weight: 700;
}

.doc-termo__itens {
    margin: 0 0 0.75rem;
    padding-left: 1.2rem;
}

.doc-termo__itens li {
    margin-bottom: 0.4rem;
}

.doc-termo__privacidade summary {
    cursor: pointer;
    font-weight: 700;
    color: #0047d6;
}

.doc-termo__privacidade dl {
    margin: 0.6rem 0 0;
}

.doc-termo__privacidade dt {
    font-weight: 700;
}

.doc-termo__privacidade dd {
    margin: 0 0 0.5rem;
}

.doc-termo__aceite {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    margin: 0.9rem 0 0;
    font-weight: 700;
    cursor: pointer;
}

.doc-termo__aceite input {
    width: 20px;
    height: 20px;
    margin-top: 0.1rem;
    flex-shrink: 0;
}

.doc-termo__versao {
    margin: 0.5rem 0 0;
    font-size: 0.78rem;
    color: #5b6b86;
}

.doc-termo__dica {
    margin: 0.4rem 0 0;
    font-size: 0.85rem;
    color: #5b6b86;
}

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
