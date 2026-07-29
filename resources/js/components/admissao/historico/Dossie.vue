<template>
    <div>
        <p class="mt-2" v-if="preload"><i class="fa fa-spinner fa-pulse"></i> Aguarde ...</p>
        <div v-if="!preload" :id="`form_${hash}`">
            <fieldset>
                <legend>FOTO 3x4 (Foto Escaneada)</legend>
                <upload
                    label="Selecionar Imagem"
                    :url="urlFotoTresUpload"
                    :model="form.foto_tres"
                    :model-delete="form.foto_tresDel"
                    :apenas-imagens="true"
                    :quantidade="1"
                    @onprogresso="anexoUploadAndamento = true"
                    @onfinalizado="anexoUploadAndamento = false"
                ></upload>
            </fieldset>

            <fieldset v-for="secao in secoes" :key="secao.chave">
                <legend>{{ secao.label }}</legend>
                <upload
                    label="Selecionar anexo(s)"
                    :dados-ajax="{
                        tipo: secao.tipo,
                        label: secao.label
                    }"
                    :model="form[secao.chave]"
                    :model-delete="form[`${secao.chave}Del`]"
                    :url="urlAnexoUpload"
                    @onprogresso="anexoUploadAndamento = true"
                    @onfinalizado="anexoUploadAndamento = false"
                ></upload>
                <button
                    v-if="secao.tem_modelo && secao.tipo_modelo"
                    type="button"
                    class="btn btn-sm mr-1 ml-2 btn-primary"
                    @click="modelo(secao.tipo_modelo)"
                >
                    <span class="fas fa-file-pdf"></span> Baixar Modelo
                </button>
                <template v-if="assinaturaDigitalHabilitada && secao.permite_assinatura && secao.tipo_modelo">
                    <template v-if="temDocumentoAssinaturaTipo(secao.tipo_modelo)">
                        <button
                            type="button"
                            class="btn btn-sm mr-1 ml-2 btn-info"
                            @click="abrirModalGerenciarAssinatura(secao.tipo_modelo)"
                        >
                            <span class="fas fa-cog"></span> Gerenciar assinatura
                        </button>
                    </template>
                    <template v-else>
                        <button
                            type="button"
                            class="btn btn-sm mr-1 ml-2 btn-success"
                            @click="abrirModalAssinatura(secao.tipo_modelo)"
                        >
                            <span class="fas fa-pen-fancy"></span> Enviar para assinatura
                        </button>
                    </template>
                </template>
            </fieldset>

            <button class="btn btn-sm mr-1 btn-primary mb-3" @click="salvar"><i class="fa fa-save"></i> Salvar</button>

            <acao-assinatura-documento
                ref="acaoAssinaturaDossie"
                :id-prefix="`dossie_${hash}`"
                :titulo-enviar="'Enviar para assinatura digital'"
                :get-nome-documento="getNomeDocumentoAssinaturaDossie"
                :get-signatarios-iniciais="getSignatariosIniciaisAssinaturaDossie"
                :enviar-handler="enviarAssinaturaDossie"
                :atualizar-handler="atualizar"
            >
            </acao-assinatura-documento>
        </div>
    </div>
</template>

<script>
import Upload from '../../Upload'
import AcaoAssinaturaDocumento from '../../administracao/documentoassinatura/AcaoAssinaturaDocumento.vue'

export default {
    name: 'Dossie',
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
        Upload,
        AcaoAssinaturaDocumento
    },
    data() {
        return {
            preload: false,
            URL_ADMIN,

            urlAnexoUpload: `${URL_ADMIN}/historico/dossie/uploadAnexos`,
            urlFotoTresUpload: `${URL_ADMIN}/historico/dossie/uploadFotoTres`,
            anexoUploadAndamento: false,

            form: {
                foto_tres: [],
                foto_tresDel: []
            },
            formDefault: null,
            secoes: [],

            documentosAssinaturaPorTipo: {},
            assinaturaDigitalHabilitada: typeof window !== 'undefined' ? !!window.MYBP_ASSINATURA_DIGITAL_HABILITADA : true
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
        this.atualizar()
    },
    methods: {
        addDynamicKey(key) {
            this.form[`${key}`] = []
            this.form[`${key}Del`] = []
        },
        labelPorTipoModelo(tipoModelo) {
            const secao = (this.secoes || []).find((item) => item.tipo_modelo === tipoModelo)
            return secao ? secao.label : tipoModelo
        },
        getDocumentoAssinaturaPorTipo(tipoModelo) {
            return (this.documentosAssinaturaPorTipo && this.documentosAssinaturaPorTipo[tipoModelo]) || null
        },
        temDocumentoAssinaturaTipo(tipoModelo) {
            const doc = this.getDocumentoAssinaturaPorTipo(tipoModelo)
            return !!(doc && doc.id)
        },
        abrirModalGerenciarAssinatura(tipoModelo) {
            const doc = this.getDocumentoAssinaturaPorTipo(tipoModelo)
            if (!doc || !doc.id) return
            this.$refs.acaoAssinaturaDossie.abrirGerenciar(doc, { tipo_modelo: tipoModelo })
        },
        getNomeDocumentoAssinaturaDossie(contexto) {
            const tipo = contexto && contexto.tipo_modelo ? contexto.tipo_modelo : ''
            return `${this.labelPorTipoModelo(tipo) || tipo || 'Documento'} (Dossiê)`
        },
        getSignatariosIniciaisAssinaturaDossie() {
            const curriculo = this.form.curriculo || {}
            const nome = curriculo.nome || ''
            const email = curriculo.email || (curriculo.user && curriculo.user.login ? curriculo.user.login : '')
            const cpf = curriculo.cpf || ''
            if (!nome && !email && !cpf) return [{ nome: '', email: '', cpf: '' }]
            return [{ nome: nome || '', email: email || '', cpf: cpf || '' }]
        },
        enviarAssinaturaDossie({ contexto, signatarios }) {
            const signatariosValidos = signatarios.filter((s) => s.email && s.nome).map((s) => ({ nome: s.nome, email: s.email, cpf: s.cpf || null }))
            return axios.post(`${URL_ADMIN}/historico/dossie/enviar-para-assinatura`, {
                tipo_modelo: contexto.tipo_modelo,
                curriculo_id: this.form.curriculo_id,
                feedback_id: this.feedback_id,
                signatarios: signatariosValidos
            })
        },
        salvar() {
            formReset()
            $(`#form_${this.hash} :input:visible`).trigger('blur')
            if ($(`#form_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros')
                return false
            }

            this.preload = true

            axios
                .post(`${URL_ADMIN}/historico/dossie/${this.feedback_id}`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.preload = false
                        mostraSucesso('Dossiê salvo com sucesso!')
                        this.atualizar()
                    }
                })
                .catch(() => (this.preload = false))
        },
        async atualizar() {
            this.preload = true
            this.form = _.cloneDeep(this.formDefault)
            try {
                const res = await axios.get(`${URL_ADMIN}/historico/dossie/${this.feedback_id}`)
                this.secoes = res.data.secoes || []
                const chaves = (this.secoes.length
                    ? this.secoes.map((secao) => secao.chave)
                    : (res.data.relacionamentos || []).map((relacionamento) => relacionamento.comum))
                chaves.forEach((key) => {
                    this.addDynamicKey(key)
                })
                Object.assign(this.form, res.data.dossie)
                this.documentosAssinaturaPorTipo = res.data.documentos_para_assinatura || {}
            } finally {
                this.preload = false
            }
        },
        modelo(tipo_modelo) {
            const link = `${URL_ADMIN}/historico/dossie/${tipo_modelo}/${this.form.curriculo_id}/${this.form.id}`
            open(link, 'blank')
        },
        abrirModalAssinatura(tipo_modelo) {
            this.$refs.acaoAssinaturaDossie.abrirEnvio({ tipo_modelo })
        }
    }
}
</script>

<style scoped></style>
