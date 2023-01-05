<template>
    <!--    <modal id='componenteDocumentos' label-fechar="Concluir" @fechou="redirecionar" :titulo="titulo_janela" :size="65">-->
    <!--        <template slot="conteudo">-->
    <div>
        <p class=" mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
        <div v-if="!preload" id="formDocumentos">
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
                <telefones :model="formUser.telefones" :ramal="false" :pais="false" :qnt_min="1"></telefones>
                <button type="button" class="btn btn-success" v-show="formUser.telefones.length > 0"
                        @click="alterar">
                    Alterar
                </button>
            </fieldset>
            <div class="alert alert-warning alert-dismissible">
                <h6 class="text-center">
                    <strong><i class="fa fa-exclamation-triangle"></i> Atenção! Verifique os arquivos antes de enviar, os tipos de arquivos aceitos são: PDF, JPG, PNG e
                        JPEG.</strong><br>
                </h6>
            </div>

            <fieldset>
                <legend>FOTO 3X4</legend>
                <upload label="Selecionar anexo(s)" :dados-ajax="{tipo:'foto3x4',curriculo_id: curriculo.id}"
                        :model="formUser.foto_tres"
                        :model-delete="formUser.foto_tresDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>RG/CPF</legend>
                <upload label="Selecionar anexo(s)" :dados-ajax="{tipo:'anexoscpfrg',curriculo_id: curriculo.id}"
                        :model="formUser.anexos_cpf_rg"
                        :model-delete="formUser.anexos_cpf_rgDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>COMPROVANTE DE ENDEREÇO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{tipo:'comprovante_end',curriculo_id: curriculo.id}"
                        :model="formUser.comprovante_end"
                        :model-delete="formUser.comprovante_endDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>CTPS DIGITAL (FRENTE)</legend>
                <upload label="Selecionar anexo(s)" :dados-ajax="{tipo:'ctps_frente',curriculo_id: curriculo.id}"
                        :model="formUser.ctps_frente"
                        :model-delete="formUser.ctps_frenteDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>CTPS DIGITAL (VERSO)</legend>
                <upload label="Selecionar anexo(s)" :dados-ajax="{tipo:'ctps_verso',curriculo_id: curriculo.id}"
                        :model="formUser.ctps_verso"
                        :model-delete="formUser.ctps_versoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>ANTECEDENTE CRIMINAL</legend>
                <p>SITE PARA EMISSÃO DO ANTECEDENTE:
                    <a target="_blank" href="https://servicos.dpf.gov.br/antecedentes-criminais/certidao">CLIQUE
                        AQUI</a></p>
                <upload label="Selecionar anexo(s)" :dados-ajax="{tipo:'antecedentes',curriculo_id: curriculo.id}"
                        :model="formUser.antecedentes"
                        :model-delete="formUser.antecedentesDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>TITULO ELEITOR</legend>
                <upload label="Selecionar anexo(s)" :dados-ajax="{tipo:'titulo_eleitor',curriculo_id: curriculo.id}"
                        :model="formUser.titulo_eleitor"
                        :model-delete="formUser.titulo_eleitorDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>CERTIFICADO RESERVISTA (Apenas Homens)</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{tipo:'certificado_reservista',curriculo_id: curriculo.id}"
                        :model="formUser.certificado_reservista"
                        :model-delete="formUser.certificado_reservistaDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>CARTÃO DO PIS OU RESCISÃO DE CONTRATO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{tipo:'pis_rescisao',curriculo_id: curriculo.id}"
                        :model="formUser.pis_rescisao"
                        :model-delete="formUser.pis_rescisaoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>CERTIFICADO DE ESCOLARIDADE</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{tipo:'certificado_escolaridade',curriculo_id: curriculo.id}"
                        :model="formUser.certificado_escolaridade"
                        :model-delete="formUser.certificado_escolaridadeDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :multi="true"></upload>
            </fieldset>
            <fieldset>
                <legend>CONTA BANCO</legend>
                <upload label="Selecionar anexo(s)" :dados-ajax="{tipo:'conta_banco',curriculo_id: curriculo.id}"
                        :model="formUser.conta_banco"
                        :model-delete="formUser.conta_bancoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>CARTA DE SINDICALIZAÇÃO EMITIDA PELO SINDICATO</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{tipo:'carta_sindicato',curriculo_id: curriculo.id}"
                        :model="formUser.carta_sindicato"
                        :model-delete="formUser.carta_sindicatoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>CÓPIA DA CARTEIRA DE VACINA; (NÃO OBRIGATÓRIO)</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{tipo:'carteira_vacina',curriculo_id: curriculo.id}"
                        :model="formUser.carteira_vacina"
                        :model-delete="formUser.carteira_vacinaDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :quantidade="1" :multi="false"></upload>
            </fieldset>
            <fieldset>
                <legend>DOCUMENTAÇÃO FILHOS (PARA SALÁRIO FAMÍLIA)</legend>
                <p>IDENTIDADE E CPF</p>
                <upload label="Selecionar anexo(s)" :dados-ajax="{tipo:'rgcpf_filho',curriculo_id: curriculo.id}"
                        :model="formUser.rgcpf_filho"
                        :model-delete="formUser.rgcpf_filhoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :multi="true"></upload>
            </fieldset>
            <fieldset>
                <legend>CARTÃO VACINA (ATÉ 6 ANOS)</legend>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{tipo:'cartao_vacina_filho',curriculo_id: curriculo.id}"
                        :model="formUser.cartao_vacina_filho"
                        :model-delete="formUser.cartao_vacina_filhoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :multi="true"></upload>
            </fieldset>
            <fieldset>
                <legend>DECLARAÇÃO ESCOLAR (DE 7 ANOS ATÉ 14 ANOS)</legend>
                <p>DECLARAÇÃO ESCOLAR DO ANO EM CURSO (ORIGINAL)</p>
                <upload label="Selecionar anexo(s)"
                        :dados-ajax="{tipo:'declaracao_escolar_filho',curriculo_id: curriculo.id}"
                        :model="formUser.declaracao_escolar_filho"
                        :model-delete="formUser.declaracao_escolar_filhoDel" :url="urlAnexoUpload"
                        @onprogresso="anexoUploadAndamento=true"
                        @onfinalizado="anexoUploadAndamento=false" :multi="true"></upload>
            </fieldset>
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
        this.formDefault = _.cloneDeep(this.form);
        Object.assign(this.formUser,this.curriculo)
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

            formUser: {
                nome: '',
                nome_pai: '',
                nome_mae: '',
                telefones: [],

                foto_tres: [], //FOTO 3X4
                foto_tresDel: [],

                anexos_cpf_rg: [],
                anexos_cpf_rgDel: [],

                comprovante_end: [],
                comprovante_endDel: [],

                ctps_frente: [],
                ctps_frenteDel: [],

                ctps_verso: [],
                ctps_versoDel: [],

                antecedentes: [],
                antecedentesDel: [],

                titulo_eleitor: [],
                titulo_eleitorDel: [],

                certificado_reservista: [],
                certificado_reservistaDel: [],

                pis_rescisao: [],
                pis_rescisaoDel: [],

                certificado_escolaridade: [],
                certificado_escolaridadeDel: [],

                conta_banco: [],
                conta_bancoDel: [],

                carta_sindicato: [],
                carta_sindicatoDel: [],

                carteira_vacina: [],
                carteira_vacinaDel: [],

                rgcpf_filho: [],
                rgcpf_filhoDel: [],

                cartao_vacina_filho: [],
                cartao_vacina_filhoDel: [],

                declaracao_escolar_filho: [],
                declaracao_escolar_filhoDel: [],
            },
        }
    },
    methods: {

        redirecionar() {
            window.location.href = `${URL_SITE}/documentos`;
        },

        alterar() {
            $(`#formDocumentos :input:visible`).trigger('blur');
            if ($(`#formDocumentos :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros')
                return false;
            }
            axios.put(`${URL_SITE}/documentos/${this.formUser.id}`, this.formUser)
                .then(response => {
                    if (response.status === 201) {
                        this.preload = false;
                        mostraSucesso('Suas informações foram alteradas com sucesso!');
                        // this.redirecionar();
                    }
                })
                .catch(error);
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
    padding: .5rem .8rem;
    background-color: #f4f4f4;
    border-radius: .5rem;
}
</style>
