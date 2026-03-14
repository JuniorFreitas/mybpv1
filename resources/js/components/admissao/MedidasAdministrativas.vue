<template>
    <div>
        <p class="mt-2" v-if="preload"><i class="fa fa-spinner fa-pulse"></i> Aguarde ...</p>
        <div v-if="!preload" :id="`form_${hash}`">
            <button class="btn btn-sm mr-1 btn-primary mb-3" @click="abrirModalNovaMedida"><i class="fa fa-plus"></i> Adicionar Medida</button>

            <template v-if="form.medidas_administrativas.length > 0">
                <fieldset class="mb-2" v-for="(obj, index) in form.medidas_administrativas" :key="index">
                    <legend>#{{ index + 1 }}</legend>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Tipo</label>
                            <select
                                class="form-control"
                                v-model="obj.tipo"
                                :disabled="!obj.novo"
                                onchange="valida_campo_vazio(this, 1)"
                                onblur="valida_campo_vazio(this, 1)"
                            >
                                <option value="">Selecione ...</option>
                                <option v-for="item in tipos" :key="item" :value="item">
                                    {{ item }}
                                </option>
                            </select>
                        </div>


                        <div class="col-md-8">
                            <label>Causa</label>
                            <select
                                class="form-control"
                                v-model="obj.causa"
                                :disabled="!obj.novo"
                                onchange="valida_campo_vazio(this, 1)"
                                onblur="valida_campo_vazio(this, 1)"
                            >
                                <option value="">Selecione ...</option>
                                <option v-for="item in causas" :key="item" :value="item">
                                    {{ item }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Motivo</label>
                            <input type="text" class="form-control" v-model="obj.motivo" :disabled="!obj.novo" onblur="valida_campo_vazio(this, 1)" />
                        </div>

                        <div class="col-md-4">
                            <label>Solicitante</label>
                            <input type="text" class="form-control" v-model="obj.solicitante" :disabled="!obj.novo" onblur="valida_campo_vazio(this, 1)" />
                        </div>

                        <div class="col-md-2">
                            <date-picker label="Data Solicitação" v-model="obj.data_solicitacao" :max="restricao" :disabled="!obj.novo"></date-picker>
                        </div>

                        <div class="col-md-2">
                            <date-picker
                                v-if="!naoExibiRetorno.includes(obj.tipo)"
                                label="Data Retorno"
                                v-model="obj.data_retorno"
                                :min="hoje"
                                :disabled="!obj.novo"
                            ></date-picker>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>Anexo</legend>
                                <upload
                                    :model="obj.anexos"
                                    :model-delete="obj.anexosDel"
                                    :url="url_anexo"
                                    label="Selecionar"
                                    @onProgresso="anexoUploadAndamento = true"
                                    @onFinalizado="anexoUploadAndamento = false"
                                ></upload>
                            </fieldset>
                        </div>

                        <div class="col-12 mt-3" v-show="obj.novo">
                            <button class="btn btn-sm mr-1 btn-danger" @click="removerLIMedida(index)"><i class="fa fa-times"></i> Remover</button>

                            <button class="btn btn-sm mr-1 btn-primary mt" @click="abrirModalNovaMedida" v-show="index >= 1"><i class="fa fa-plus"></i> Adicionar</button>
                        </div>

                        <div class="col-12 mt-3" v-show="!obj.novo && validTypes.includes(obj.tipo)">
                            <button class="btn btn-sm mr-1 btn-outline-primary" @click="gerarPdf(obj)" v-show="!obj.novo">
                                <i class="fas fa-file-pdf"></i> GERAR PDF
                            </button>
                            <template v-if="assinaturaDigitalHabilitada && temDocumentoAssinatura(obj)">
                                <button type="button" class="btn btn-sm mr-1 ml-2 btn-info" @click="abrirGerenciamentoAssinaturaMedida(obj)">
                                    <span class="fas fa-cog"></span> Gerenciar assinatura
                                </button>
                            </template>
                            <template v-else-if="assinaturaDigitalHabilitada">
                                <button type="button" class="btn btn-sm mr-1 ml-2 btn-success" @click="abrirEnvioAssinaturaMedida(obj)">
                                    <span class="fas fa-pen-fancy"></span> Enviar para assinatura
                                </button>
                            </template>
                        </div>

                        <div class="col-12 mt-3" v-show="!obj.novo && privilegio_gestao_rh">
                            <button class="btn btn-sm mr-1 btn-danger" @click="abrirModalRemover(obj)"><i class="fa fa-trash"></i> Remover Medida</button>
                        </div>
                    </div>
                </fieldset>
            </template>

            <!-- <button class="btn btn-sm mr-1 btn-primary mb-3" v-if="form.medidas_administrativas.length > 0" @click="salvar">
                <i class="fa fa-save"></i> Salvar
            </button> -->
        </div>

        <acao-assinatura-documento
            ref="acaoAssinaturaMedida"
            :id-prefix="`medida_${hash}`"
            :titulo-enviar="'Enviar para assinatura digital'"
            :get-nome-documento="getNomeDocumentoAssinaturaMedida"
            :get-signatarios-iniciais="getSignatariosIniciaisAssinaturaMedida"
            :validar-signatarios="validarSignatariosAssinaturaMedida"
            :enviar-handler="enviarAssinaturaMedida"
            :atualizar-handler="atualizar"
        >
        </acao-assinatura-documento>

        <!-- Modal para Cadastrar Nova Medida Administrativa -->
        <modal ref="modalNovaMedida" :id="`janelaNovaMedida_${hash}`" titulo="Adicionar Medida Administrativa" :fechar="true" size="g" :mostrarBotaoFecharNoRodape="false">
            <template #conteudo>
                <div class="form-nova-medida-modal">
                    <fieldset class="mb-3">
                        <legend class="mb-2">Tipo e causa</legend>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="mb-1">Tipo</label>
                                <select class="form-control form-control-sm" v-model="formNovaMedida.tipo">
                                    <option value="">Selecione ...</option>
                                    <option v-for="item in tipos" :key="item" :value="item">{{ item }}</option>
                                </select>
                            </div>
                            <div class="col-md-8 form-group">
                                <label class="mb-1">Causa</label>
                                <select class="form-control form-control-sm" v-model="formNovaMedida.causa">
                                    <option value="">Selecione ...</option>
                                    <option v-for="item in causas" :key="item" :value="item">{{ item }}</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="mb-3">
                        <legend class="mb-2">Descrição</legend>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="mb-1">Motivo</label>
                                <input type="text" class="form-control form-control-sm" v-model="formNovaMedida.motivo" placeholder="Informe o motivo" />
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="mb-1">Solicitante</label>
                                <input type="text" class="form-control form-control-sm" v-model="formNovaMedida.solicitante" placeholder="Nome do solicitante" />
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="mb-3">
                        <legend class="mb-2">Datas</legend>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <date-picker label="Data Solicitação" v-model="formNovaMedida.data_solicitacao" :max="restricao"></date-picker>
                            </div>
                            <div class="col-md-4 form-group" v-if="!naoExibiRetorno.includes(formNovaMedida.tipo)">
                                <date-picker label="Data Retorno" v-model="formNovaMedida.data_retorno" :min="hoje"></date-picker>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="mb-0">
                        <legend class="mb-2">Anexo</legend>
                        <upload
                            :model="formNovaMedida.anexos"
                            :model-delete="formNovaMedida.anexosDel"
                            :url="url_anexo"
                            label="Selecionar"
                            @onProgresso="anexoUploadAndamento = true"
                            @onFinalizado="anexoUploadAndamento = false"
                        ></upload>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2" @click="fecharModalNovaMedida" :disabled="preloadNovaMedida">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" @click="salvarMedidaDaModal" :disabled="preloadNovaMedida">
                    <i class="fa" :class="preloadNovaMedida ? 'fa-spinner fa-pulse' : 'fa-save'"></i> {{ preloadNovaMedida ? 'Salvando...' : 'Salvar' }}
                </button>
            </template>
        </modal>

        <!-- Modal para Remover Medida Administrativa -->
        <modal ref="modalRemoverMedida" :id="`janelaRemoverMedida_${hash}`" :titulo="tituloModalRemover" :fechar="!preloadRemover" :size="75">
            <template #conteudo>
                <div v-if="!preloadRemover && medidaSelecionada">
                    <fieldset>
                        <legend>Informações do Colaborador</legend>
                        <div style="text-transform: uppercase" v-if="feedbackInfo">
                            <p>
                                Nome: <strong>{{ feedbackInfo.curriculo ? feedbackInfo.curriculo.nome : '' }}</strong
                                ><br />
                                CPF: <strong>{{ feedbackInfo.curriculo ? feedbackInfo.curriculo.cpf : '' }}</strong
                                ><br />
                                <span v-if="feedbackInfo.empresa">
                                    Empresa: <strong>{{ feedbackInfo.empresa.nome_fantasia || feedbackInfo.empresa.nome }}</strong
                                    ><br />
                                </span>
                                <span v-if="feedbackInfo.vaga_aberta && feedbackInfo.vaga_aberta.vaga">
                                    Vaga: <strong>{{ feedbackInfo.vaga_aberta.vaga.nome }}</strong
                                    ><br />
                                </span>
                            </p>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Informações da Medida Administrativa</legend>
                        <div>
                            <p>
                                Tipo: <strong>{{ medidaSelecionada.tipo }}</strong
                                ><br />
                                Causa: <strong>{{ medidaSelecionada.causa }}</strong
                                ><br />
                                Motivo: <strong>{{ medidaSelecionada.motivo }}</strong
                                ><br />
                                Data Solicitação: <strong>{{ medidaSelecionada.data_solicitacao }}</strong
                                ><br />
                                <span v-if="medidaSelecionada.data_retorno">
                                    Data Retorno: <strong>{{ medidaSelecionada.data_retorno }}</strong
                                    ><br />
                                </span>
                            </p>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Termo de Responsabilidade para Remover Medida Administrativa</legend>
                        <div v-html="textoTermoResponsabilidade"></div>
                        <div class="form-group">
                            <label for="">Motivo da remoção</label>
                            <textarea
                                v-model="auditoriaForm.descricao"
                                class="form-control"
                                cols="5"
                                rows="5"
                                placeholder="Informe o motivo da remoção da medida administrativa"
                            ></textarea>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-danger" v-if="!preloadRemover && auditoriaForm.descricao.length" @click="removerMedida">
                    Remover Medida
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import DatePicker from '../DatePicker'
import Upload from '../Upload'
import Modal from '../Modal'
import AcaoAssinaturaDocumento from '../administracao/documentoassinatura/AcaoAssinaturaDocumento.vue'

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
        DatePicker,
        Upload,
        Modal,
        AcaoAssinaturaDocumento
    },
    data() {
        return {
            preload: false,
            preloadNovaMedida: false,
            URL_ADMIN,

            url_anexo: `${URL_ADMIN}/historico/medidas-administrativas/uploadAnexos`,
            anexoUploadAndamento: false,

            hoje: '',
            restricao: '',

            validTypes: [
                'Advertência Escrita',
                'Advertência Verbal',
                'Suspensão de 1 dia',
                'Suspensão de 2 ou 3 dias',
                'Suspensão acima de 3 dias',
                'Re-orientação'
            ],
            naoExibiRetorno: ['Advertência Escrita', 'Advertência Verbal', 'Desligamento', 'Re-orientação', ''],

            form: {
                medidas_administrativas: [],
                medidas_administrativasDelete: []
            },
            formDefault: null,

            causas: [],
            tipos: [],
            definicao: [],
            privilegio_gestao_rh: false,
            assinaturaDigitalHabilitada: typeof window !== 'undefined' ? !!window.MYBP_ASSINATURA_DIGITAL_HABILITADA : true,

            // Modal nova medida
            formNovaMedida: {
                tipo: '',
                causa: '',
                motivo: '',
                solicitante: '',
                data_solicitacao: '',
                data_retorno: '',
                anexos: [],
                anexosDel: []
            },

            // Modal remover medida
            medidaSelecionada: null,
            feedbackInfo: null,
            tituloModalRemover: 'Remover Medida Administrativa',
            preloadRemover: false,
            auditoriaForm: {
                descricao: '',
                medida_id: null,
                feedback_id: null
            },
            // Modal assinatura digital
            medidaAssinaturaSelecionada: null,
            signatariosAssinatura: [],
            preloadAssinatura: false,
            // Modal gerenciar assinatura (detalhe do documento)
            documentoAssinaturaDetalhe: null,
            preloadGerenciarAssinatura: false,
            medidaParaReenvio: null
        }
    },
    computed: {
        signatariosValidos() {
            if (!this.signatariosAssinatura.length) return false
            return this.signatariosAssinatura.every((s) => {
                const cpfNumeros = (s.cpf || '').replace(/\D/g, '')
                return s.nome && s.nome.trim() && s.email && s.email.trim() && cpfNumeros.length >= 11
            })
        },
        textoTermoResponsabilidade() {
            const nomeColaborador = this.feedbackInfo && this.feedbackInfo.curriculo ? this.feedbackInfo.curriculo.nome : ''
            const nomeUsuario = typeof AUTENTICADO !== 'undefined' && AUTENTICADO ? AUTENTICADO.nome : ''
            const tipoMedida = this.medidaSelecionada ? this.medidaSelecionada.tipo : ''

            return `<p>
                        Ao clicar em "Remover Medida Administrativa" e remover a medida administrativa do tipo
                        <strong>${tipoMedida}</strong> do colaborador
                        <strong>${nomeColaborador}</strong>, eu,
                        <strong>${nomeUsuario}</strong>, reconheço e aceito que estou assumindo a
                        responsabilidade por esta ação.
                        <br>
                        Além disso, declaro que:
                        <br><br>
                        Estou ciente de que a remoção da medida administrativa implica em uma ação
                        irreversível no sistema.
                        <br><br>
                        Confirmo que revisei cuidadosamente todas as informações relevantes relacionadas à remoção da
                        medida administrativa.
                        <br><br>
                        Comprometo-me a fornecer um motivo válido e justificável para esta ação, conforme solicitado
                        pelo sistema.
                        <br><br>
                        Aceito total responsabilidade por quaisquer consequências decorrentes da remoção da medida administrativa.
                        <br><br>
                        Assumo que, ao clicar em "Remover Medida Administrativa" no sistema MyBP, estou ciente e concordo com as disposições deste termo de responsabilidade.
                    </p>`
        }
    },
    mounted() {
        this.atualizar()
    },

    methods: {
        abrirEnvioAssinaturaMedida(obj) {
            this.$refs.acaoAssinaturaMedida.abrirEnvio(obj)
        },
        abrirGerenciamentoAssinaturaMedida(obj) {
            const doc = obj && obj.documento_para_assinatura
            if (!doc || !doc.id) return
            this.$refs.acaoAssinaturaMedida.abrirGerenciar(doc, obj)
        },
        getNomeDocumentoAssinaturaMedida(contexto) {
            return `${(contexto && contexto.tipo) || 'Documento'} (Medida Administrativa)`
        },
        getSignatariosIniciaisAssinaturaMedida() {
            const curriculo = this.feedbackInfo && this.feedbackInfo.curriculo ? this.feedbackInfo.curriculo : null
            const nome = curriculo ? curriculo.nome || '' : ''
            const email = curriculo && curriculo.email ? curriculo.email : ''
            const cpf = curriculo && curriculo.cpf ? this.formatarCpf(String(curriculo.cpf).replace(/\D/g, '').slice(0, 11)) : ''
            if (!nome && !email && !cpf) return [{ nome: '', email: '', cpf: '' }]
            return [{ nome, email, cpf }]
        },
        validarSignatariosAssinaturaMedida(signatarios) {
            if (!signatarios.length) return false
            return signatarios.every((s) => {
                const cpfNumeros = (s.cpf || '').replace(/\D/g, '')
                return s.nome && s.nome.trim() && s.email && s.email.trim() && cpfNumeros.length >= 11
            })
        },
        enviarAssinaturaMedida({ contexto, signatarios }) {
            const payload = {
                medida_id: contexto.id,
                signatarios: signatarios.map((s) => ({
                    nome: s.nome,
                    email: s.email,
                    cpf: (s.cpf || '').replace(/\D/g, '') || null
                }))
            }
            return axios.post(`${URL_ADMIN}/historico/medidas-administrativas/enviar-para-assinatura`, payload)
        },
        addLIMedida() {
            const obj = {}
            obj.novo = true
            obj.feedback_id = this.feedback_id
            obj.solicitante = ''
            obj.tipo = ''
            obj.causa = ''
            obj.definicao = ''
            obj.motivo = ''
            obj.data_solicitacao = this.hoje
            obj.data_retorno = this.hoje
            obj.anexos = []
            obj.anexosDel = []

            this.form.medidas_administrativas.unshift(obj)
        },
        resetFormNovaMedida() {
            this.formNovaMedida = {
                tipo: '',
                causa: '',
                motivo: '',
                solicitante: '',
                data_solicitacao: this.hoje || '',
                data_retorno: this.hoje || '',
                anexos: [],
                anexosDel: []
            }
        },
        abrirModalNovaMedida() {
            this.resetFormNovaMedida()
            this.$nextTick(() => {
                if (this.$refs.modalNovaMedida && typeof this.$refs.modalNovaMedida.abrirModal === 'function') {
                    this.$refs.modalNovaMedida.abrirModal()
                }
            })
        },
        fecharModalNovaMedida() {
            if (this.$refs.modalNovaMedida && typeof this.$refs.modalNovaMedida.fecharModal === 'function') {
                this.$refs.modalNovaMedida.fecharModal()
            }
        },
        salvarMedidaDaModal() {
            const f = this.formNovaMedida
            if (!f.tipo || !f.causa || !f.motivo || !f.solicitante || !f.data_solicitacao) {
                if (typeof mostraErro !== 'undefined') {
                    mostraErro('', 'Preencha Tipo, Causa, Motivo, Solicitante e Data Solicitação.')
                }
                return
            }
            const obj = {
                novo: true,
                feedback_id: this.feedback_id,
                tipo: f.tipo,
                causa: f.causa,
                definicao: '',
                motivo: f.motivo,
                solicitante: f.solicitante,
                data_solicitacao: f.data_solicitacao,
                data_retorno: f.data_retorno || f.data_solicitacao,
                anexos: Array.isArray(f.anexos) ? [...f.anexos] : [],
                anexosDel: Array.isArray(f.anexosDel) ? [...f.anexosDel] : []
            }
            const payload = {
                medidas_administrativas: [...this.form.medidas_administrativas, obj],
                medidas_administrativasDelete: this.form.medidas_administrativasDelete || []
            }
            this.preloadNovaMedida = true
            axios
                .post(`${URL_ADMIN}/historico/${this.feedback_id}`, payload)
                .then((response) => {
                    if (response.status === 200 || response.status === 201) {
                        if (typeof mostraSucesso !== 'undefined') mostraSucesso('Medida administrativa salva com sucesso.')
                        this.resetFormNovaMedida()
                        this.fecharModalNovaMedida()
                        this.atualizar()
                    }
                })
                .catch((error) => {
                    const data = error.response && error.response.data ? error.response.data : {}
                    const msg = data.msg || data.message || (data.erros ? (typeof data.erros === 'object' ? 'Verifique os campos.' : data.erros) : 'Erro ao salvar medida administrativa.')
                    if (typeof mostraErro !== 'undefined') mostraErro(msg)
                })
                .finally(() => {
                    this.preloadNovaMedida = false
                })
        },
        removerLIMedida(index) {
            if (this.editando) {
                this.form.medidas_administrativasDelete.push(this.form.medidas_administrativas[index].id)
            }
            this.form.medidas_administrativas.splice(index, 1)
        },
        gerarPdf(obj) {
            let link = `${URL_ADMIN}/historico/medidas-administrativas/${obj.id}/${obj.feedback_id}/pdf`
            open(link, 'blank')
        },
        /** Existe documento para assinatura vinculado → mostrar "Gerenciar assinatura" (qualquer status). */
        temDocumentoAssinatura(obj) {
            const doc = obj.documento_para_assinatura
            return !!(doc && doc.id)
        },
        /** Documento expirado ou cancelado → mostrar "Enviar novamente" junto com Gerenciar. */
        documentoExpiradoOuCancelado(obj) {
            const doc = obj.documento_para_assinatura
            return doc && doc.id && (doc.status === 'expirado' || doc.status === 'cancelado')
        },
        abrirModalGerenciarAssinatura(obj) {
            const doc = obj.documento_para_assinatura
            if (!doc || !doc.id) return
            this.medidaParaReenvio = obj
            this.documentoAssinaturaDetalhe = null
            this.preloadGerenciarAssinatura = false
            this.$refs[`modalGerenciarAssinatura_${this.hash}`] && this.$refs[`modalGerenciarAssinatura_${this.hash}`].abrirModal()
            const idOrToken = doc.token || doc.id
            const url = `${typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''}/administracao/documento-assinatura/${idOrToken}`
            axios
                .get(url)
                .then((res) => {
                    this.documentoAssinaturaDetalhe = res.data
                    this.preloadGerenciarAssinatura = false
                })
                .catch(() => {
                    this.preloadGerenciarAssinatura = false
                    if (typeof mostraErro !== 'undefined') mostraErro('', 'Erro ao carregar detalhe do documento.')
                })
        },
        documentoExpiradoOuCanceladoDoc(doc) {
            return doc && (doc.status === 'expirado' || doc.status === 'cancelado')
        },
        enviarNovamenteNoModal() {
            if (!this.medidaParaReenvio) return
            this.$refs[`modalGerenciarAssinatura_${this.hash}`] && this.$refs[`modalGerenciarAssinatura_${this.hash}`].fecharModal()
            this.$nextTick(() => this.abrirModalAssinatura(this.medidaParaReenvio))
        },
        labelTipoDoc(tipo) {
            const map = {
                contrato_legal: 'Contrato (Documentos Legais)',
                contrato_trabalho: 'Contrato de Trabalho',
                carta_oferta: 'Carta Oferta',
                termo_demissao: 'Termo de Demissão',
                ficha_encaminhamento: 'Ficha de Encaminhamento',
                termo_confidencialidade: 'Termo de Confidencialidade',
                opcao_vale_transporte: 'Opção Vale Transporte',
                acordo_compensacao_horas: 'Acordo de Compensação de Horas',
                termo_salario_familia: 'Termo Salário Família',
                declaracao_dependentes_ir: 'Declaração Dependentes IR',
                medida_administrativa: 'Medida Administrativa',
                documento_demissao: 'Documento de Demissão (Aviso Prévio)'
            }
            return map[tipo] || tipo || '—'
        },
        labelStatusDoc(status) {
            const map = {
                rascunho: 'Rascunho',
                enviado: 'Enviado',
                em_assinatura: 'Em assinatura',
                concluido: 'Concluído',
                expirado: 'Expirado',
                cancelado: 'Cancelado'
            }
            return map[status] || status || '—'
        },
        badgeStatusDoc(status) {
            const map = {
                em_assinatura: 'badge-warning',
                concluido: 'badge-success',
                cancelado: 'badge-danger',
                expirado: 'badge-secondary',
                rascunho: 'badge-secondary',
                enviado: 'badge-info'
            }
            return map[status] || 'badge-secondary'
        },
        formatarDataDoc(val) {
            if (!val) return '—'
            const d = typeof val === 'string' ? new Date(val) : val
            return d.toLocaleDateString('pt-BR') + ' ' + (d.toLocaleTimeString ? d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : '')
        },
        podeCancelarDoc(item) {
            return item && ['rascunho', 'em_assinatura'].indexOf(item.status) !== -1
        },
        podeReenviarDoc(item) {
            return item && item.status === 'em_assinatura'
        },
        podeBaixarAssinadoDoc(item) {
            return item && item.status === 'concluido' && item.arquivo_assinado_id
        },
        urlDownloadAssinadoDoc(doc) {
            const idOrToken = doc && doc.token ? doc.token : doc && doc.id ? doc.id : ''
            return `${typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''}/administracao/documento-assinatura/${idOrToken}/download-assinado`
        },
        cancelarDocNoModal() {
            if (!this.documentoAssinaturaDetalhe) return
            if (!this.$swal) {
                if (confirm('Cancelar este documento? Os signatários não poderão mais assinar.')) this.executarCancelarDoc(this.documentoAssinaturaDetalhe)
                return
            }
            this.$swal
                .fire({
                    title: 'Cancelar documento?',
                    text: 'Os signatários não poderão mais assinar. Esta ação não pode ser desfeita.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Não',
                    confirmButtonText: 'Sim, cancelar'
                })
                .then((result) => {
                    if (result.isConfirmed) this.executarCancelarDoc(this.documentoAssinaturaDetalhe)
                })
        },
        executarCancelarDoc(doc) {
            const idOrToken = doc && doc.token ? doc.token : doc && doc.id ? doc.id : ''
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/cancelar`)
                .then((res) => {
                    if (res.data.success && typeof mostraSucesso !== 'undefined') mostraSucesso(res.data.message || 'Documento cancelado.')
                    this.documentoAssinaturaDetalhe = null
                    this.atualizar()
                    this.$refs[`modalGerenciarAssinatura_${this.hash}`] && this.$refs[`modalGerenciarAssinatura_${this.hash}`].fecharModal()
                })
                .catch((err) => {
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao cancelar.'
                    if (typeof mostraErro !== 'undefined') mostraErro(msg)
                    else alert(msg)
                })
        },
        reenviarDocNoModal() {
            if (!this.documentoAssinaturaDetalhe || this.documentoAssinaturaDetalhe.status !== 'em_assinatura') return
            const idOrToken = this.documentoAssinaturaDetalhe.token || this.documentoAssinaturaDetalhe.id
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/reenviar-email`)
                .then((res) => {
                    if (res.data.success && typeof mostraSucesso !== 'undefined') mostraSucesso(res.data.message || 'E-mail reenviado.')
                })
                .catch((err) => {
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao reenviar e-mail.'
                    if (typeof mostraErro !== 'undefined') mostraErro(msg)
                    else alert(msg)
                })
        },
        getSignatarioByIdDoc(signatarioId) {
            const list = this.documentoAssinaturaDetalhe && this.documentoAssinaturaDetalhe.signatarios ? this.documentoAssinaturaDetalhe.signatarios : []
            const s = list.find((x) => x.id === signatarioId)
            return s ? s.nome || s.email || `#${signatarioId}` : null
        },
        iconeEventoDoc(evento) {
            const map = {
                enviado: 'fas fa-paper-plane text-info',
                reenviado: 'fas fa-paper-plane text-warning',
                visualizado: 'fas fa-eye text-primary',
                assinado: 'fas fa-pen-fancy text-success',
                recusado: 'fas fa-times-circle text-danger',
                expirado: 'fas fa-clock text-secondary',
                cancelado: 'fas fa-ban text-danger'
            }
            return map[evento] || 'fas fa-circle text-muted'
        },
        labelEventoDoc(evento) {
            const map = {
                enviado: 'Documento enviado',
                reenviado: 'E-mail reenviado',
                visualizado: 'Visualizado pelo signatário',
                assinado: 'Assinado',
                recusado: 'Recusado',
                expirado: 'Documento expirado',
                cancelado: 'Documento cancelado'
            }
            return map[evento] || evento
        },
        detalhesEventoDoc(ev) {
            const p = ev.payload || {}
            const linhas = []
            const signatarioNome = p.signatario_id ? this.getSignatarioByIdDoc(p.signatario_id) : null
            switch (ev.evento) {
                case 'enviado':
                    if (p.nome) linhas.push({ label: 'Enviado por', value: p.nome })
                    if (p.signatarios_count !== undefined) linhas.push({ label: 'Signatários', value: `${p.signatarios_count} signatário(s)` })
                    break
                case 'reenviado':
                    if (p.nome) linhas.push({ label: 'Reenviado por', value: p.nome })
                    if (p.user_id && !p.nome) linhas.push({ label: 'Usuário', value: `ID ${p.user_id}` })
                    break
                case 'visualizado':
                    if (signatarioNome) linhas.push({ label: 'Signatário', value: signatarioNome })
                    if (p.ip) linhas.push({ label: 'IP', value: p.ip })
                    break
                case 'assinado':
                    if (signatarioNome) linhas.push({ label: 'Signatário', value: signatarioNome })
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email })
                    if (p.data_utc) linhas.push({ label: 'Data/hora (UTC)', value: this.formatarDataDoc(p.data_utc) })
                    if (p.ip) linhas.push({ label: 'IP', value: p.ip })
                    break
                case 'recusado':
                    if (signatarioNome) linhas.push({ label: 'Signatário', value: signatarioNome })
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email })
                    if (p.motivo) linhas.push({ label: 'Motivo', value: p.motivo })
                    break
                case 'cancelado':
                    if (p.user_id) linhas.push({ label: 'Usuário', value: `ID ${p.user_id}` })
                    break
                default:
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email })
                    if (p.motivo) linhas.push({ label: 'Motivo', value: p.motivo })
            }
            return linhas
        },
        salvar() {
            formReset()
            $(`#form_${this.hash} :input:visible`).trigger('blur')
            if ($(`#form_${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os erros')
                return false
            }

            this.preload = true

            if (this.form.medidas_administrativas[0].id) {
                //alterar
                axios
                    .put(`${URL_ADMIN}/historico/${this.feedback_id}`, this.form)
                    .then((response) => {
                        if (response.status === 201) {
                            this.preload = false
                            // this.cadastrado = true;
                            mostraSucesso('Medida administrativa alterada com sucesso')
                            this.atualizar()
                        }
                    })
                    .catch((error) => (this.preload = false))
            } else {
                //criar
                axios
                    .post(`${URL_ADMIN}/historico/${this.feedback_id}`, this.form)
                    .then((response) => {
                        if (response.status === 201) {
                            this.preload = false
                            mostraSucesso('Medida administrativa criada com sucesso')
                            // this.cadastrado = true;
                            this.atualizar()
                        }
                    })
                    .catch((error) => (this.preload = false))
            }
        },
        async atualizar() {
            this.preload = true
            this.form.medidas_administrativas = []
            this.form.medidas_administrativasDelete = []
            try {
                const res = await axios.get(`${URL_ADMIN}/historico/${this.feedback_id}`)
                const data = res.data
                this.form.medidas_administrativas = data.feedback.medidas_administrativas
                this.feedbackInfo = data.feedback
                this.causas = data.causas
                this.tipos = data.tipos
                this.definicao = data.definicao
                this.hoje = data.hoje
                this.restricao = data.restricao
                this.privilegio_gestao_rh = data.privilegio_gestao_rh || false
            } finally {
                this.preload = false
            }
        },
        formatarCpf(numeros) {
            const d = (numeros || '').replace(/\D/g, '').slice(0, 11)
            if (d.length <= 3) return d
            if (d.length <= 6) return d.replace(/(\d{3})(\d+)/, '$1.$2')
            return d.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4').replace(/-$/, '')
        },
        atualizarCpf(idx, value) {
            const numeros = (value || '').replace(/\D/g, '').slice(0, 11)
            this.signatariosAssinatura[idx].cpf = this.formatarCpf(numeros)
        },
        abrirModalAssinatura(obj) {
            this.medidaAssinaturaSelecionada = obj
            const curriculo = this.feedbackInfo && this.feedbackInfo.curriculo ? this.feedbackInfo.curriculo : null
            const nome = curriculo ? curriculo.nome || '' : ''
            const email = curriculo && curriculo.email ? curriculo.email : ''
            const cpfNumeros = curriculo && curriculo.cpf ? String(curriculo.cpf).replace(/\D/g, '').slice(0, 11) : ''
            const cpf = this.formatarCpf(cpfNumeros)
            const fromBase = !!(nome || email || cpfNumeros)
            this.signatariosAssinatura = [{ nome, email, cpf, fromBase }]
            if (!nome && !email && !cpfNumeros) {
                this.signatariosAssinatura = [{ nome: '', email: '', cpf: '', fromBase: false }]
            }
            this.preloadAssinatura = false
            this.$refs[`modalAssinaturaMedida_${this.hash}`] && this.$refs[`modalAssinaturaMedida_${this.hash}`].abrirModal()
        },
        adicionarSignatarioAssinatura() {
            this.signatariosAssinatura.push({ nome: '', email: '', cpf: '', fromBase: false })
        },
        removerSignatarioAssinatura(index) {
            this.signatariosAssinatura.splice(index, 1)
        },
        enviarParaAssinatura() {
            const payload = {
                medida_id: this.medidaAssinaturaSelecionada.id,
                signatarios: this.signatariosAssinatura.map((s) => ({
                    nome: s.nome,
                    email: s.email,
                    cpf: (s.cpf || '').replace(/\D/g, '') || null
                }))
            }
            this.preloadAssinatura = true
            axios
                .post(`${URL_ADMIN}/historico/medidas-administrativas/enviar-para-assinatura`, payload)
                .then((res) => {
                    this.preloadAssinatura = false
                    this.$refs[`modalAssinaturaMedida_${this.hash}`] && this.$refs[`modalAssinaturaMedida_${this.hash}`].fecharModal()
                    mostraSucesso(res.data.message || 'Documento enviado para assinatura.')
                    if (res.data.links && res.data.links.length) {
                        const msg = res.data.links.map((l) => `${l.email}: ${l.link}`).join('\n')
                        this.$swal && this.$swal.fire({ title: 'Links enviados', text: msg, icon: 'info' })
                    }
                })
                .catch((err) => {
                    this.preloadAssinatura = false
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao enviar para assinatura.'
                    mostraErro(msg)
                })
        },
        abrirModalRemover(obj) {
            this.medidaSelecionada = obj
            this.auditoriaForm = {
                descricao: '',
                medida_id: obj.id,
                feedback_id: this.feedback_id
            }
            this.tituloModalRemover = `Remover Medida Administrativa: ${obj.tipo}`
            this.preloadRemover = false
            if (this.$refs && this.$refs.modalRemoverMedida && typeof this.$refs.modalRemoverMedida.abrirModal === 'function') {
                this.$refs.modalRemoverMedida.abrirModal()
            }
        },
        removerMedida() {
            if (!this.auditoriaForm.descricao || this.auditoriaForm.descricao.length === 0) {
                mostraErro('', 'Informe o motivo da remoção da medida administrativa')
                return false
            }

            this.preloadRemover = true
            axios
                .put(`${URL_ADMIN}/historico/medidas-administrativas/remover-medida-administrativa`, {
                    medida_id: this.auditoriaForm.medida_id,
                    feedback_id: this.auditoriaForm.feedback_id,
                    motivo: this.auditoriaForm.descricao
                })
                .then((response) => {
                    if (response.status === 201) {
                        this.preloadRemover = false
                        if (this.$refs && this.$refs.modalRemoverMedida && typeof this.$refs.modalRemoverMedida.fecharModal === 'function') {
                            this.$refs.modalRemoverMedida.fecharModal()
                        }
                        mostraSucesso('Medida administrativa removida com sucesso')
                        this.medidaSelecionada = null
                        this.auditoriaForm = {
                            descricao: '',
                            medida_id: null,
                            feedback_id: null
                        }
                        this.atualizar()
                    }
                })
                .catch((error) => {
                    this.preloadRemover = false
                    const errorMsg =
                        error.response && error.response.data && error.response.data.msg ? error.response.data.msg : 'Erro ao remover medida administrativa'
                    mostraErro(errorMsg)
                })
        }
    }
}
</script>

<style scoped>
.eventos-auditoria {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.evento-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    border-left: 4px solid #6c757d;
}
.evento-item.evento-enviado {
    border-left-color: #17a2b8;
}
.evento-item.evento-reenviado {
    border-left-color: #ffc107;
}
.evento-item.evento-visualizado {
    border-left-color: #007bff;
}
.evento-item.evento-assinado {
    border-left-color: #28a745;
}
.evento-item.evento-recusado {
    border-left-color: #dc3545;
}
.evento-item.evento-expirado {
    border-left-color: #6c757d;
}
.evento-item.evento-cancelado {
    border-left-color: #dc3545;
}
.evento-cabecalho {
    display: flex;
    align-items: center;
    gap: 0.5rem 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 0.35rem;
}
.evento-icone {
    font-size: 1rem;
    width: 1.25rem;
    text-align: center;
    flex-shrink: 0;
}
.evento-titulo {
    font-weight: 600;
    color: #212529;
    font-size: 0.938rem;
}
.evento-data {
    margin-left: auto;
    font-size: 0.813rem;
    color: #6c757d;
}
.evento-detalhes {
    padding-left: 1.9rem;
    font-size: 0.813rem;
}
.evento-detalhe {
    display: flex;
    gap: 0.35rem;
    margin-top: 0.2rem;
}
.evento-detalhe-label {
    color: #6c757d;
    font-weight: 500;
    flex-shrink: 0;
}
.evento-detalhe-value {
    color: #212529;
    word-break: break-word;
}
</style>
