<template>
    <div>
        <modal ref="modalEnviar" :id="modalEnviarId" :titulo="tituloEnviar" :fechar="!preloadEnvio" size="g">
            <template #conteudo>
                <div v-if="contextoEnvio" class="mybp-modal-form mybp-filtros-compactos">
                    <div class="mybp-card-corpo mb-3">
                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-file-signature"
                                    label="Documento"
                                    :valor="nomeDocumentoAtual"
                                    :forte="true"
                                ></mybp-card-campo>
                            </div>
                        </section>
                    </div>

                    <fieldset class="mybp-modal-secao">
                        <legend>Signatários</legend>
                        <p class="mybp-campo-obrigatorio-legenda mb-3" v-if="!camposSignatarioBloqueados">
                            Campos com <span class="text-danger">*</span> são obrigatórios.
                        </p>
                        <div
                            class="mybp-assinatura-signatario"
                            v-for="(s, idx) in signatarios"
                            :key="s.id || idx"
                        >
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label">
                                            Nome
                                            <span class="text-danger" v-if="!camposSignatarioBloqueados">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="s.nome"
                                            placeholder="Nome completo"
                                            :disabled="camposSignatarioBloqueados"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label">
                                            E-mail
                                            <span class="text-danger" v-if="!camposSignatarioBloqueados">*</span>
                                        </label>
                                        <input
                                            type="email"
                                            class="form-control form-control-sm"
                                            v-model="s.email"
                                            placeholder="E-mail"
                                            :disabled="camposSignatarioBloqueados"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label">CPF</label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            v-model="s.cpf"
                                            placeholder="Opcional"
                                            :disabled="camposSignatarioBloqueados"
                                        />
                                    </div>
                                </div>
                                <div class="col-12 col-md-1 d-flex align-items-end" v-if="!camposSignatarioBloqueados">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger mb-3"
                                        title="Remover signatário"
                                        @click="removerSignatario(idx)"
                                    >
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button
                            v-if="!camposSignatarioBloqueados"
                            type="button"
                            class="btn btn-sm btn-outline-primary mybp-btn-acao-compact"
                            @click="adicionarSignatario"
                        >
                            <i class="fa fa-plus"></i> Adicionar signatário
                        </button>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button
                    type="button"
                    class="btn btn-sm btn-success"
                    :disabled="preloadEnvio || !signatariosValidos"
                    @click="enviar"
                >
                    <span v-if="preloadEnvio"><i class="fa fa-spinner fa-pulse"></i> Enviando...</span>
                    <span v-else><i class="fa fa-paper-plane"></i> Enviar para assinatura</span>
                </button>
            </template>
        </modal>

        <modal
            ref="modalGerenciar"
            :id="modalGerenciarId"
            titulo="Gerenciar assinatura"
            :fechar="!preloadGerenciar"
            size="g"
        >
            <template #conteudo>
                <p v-if="preloadGerenciar && !documentoDetalhe" class="text-center mt-2 mb-0">
                    <i class="fa fa-spinner fa-pulse"></i> Carregando...
                </p>

                <div v-if="documentoDetalhe" class="mybp-modal-form mybp-filtros-compactos">
                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-2" style="gap: 8px;">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <span class="mybp-badge-id">#{{ documentoDetalhe.id }}</span>
                            <mybp-status-badge
                                :variante="varianteStatusDoc(documentoDetalhe.status)"
                                :texto="labelStatusDoc(documentoDetalhe.status)"
                            ></mybp-status-badge>
                        </div>
                        <a
                            v-if="podeBaixarAssinadoDoc(documentoDetalhe)"
                            :href="urlDownloadAssinadoDoc(documentoDetalhe)"
                            target="_blank"
                            rel="noopener"
                            class="btn btn-sm btn-outline-success"
                        >
                            <i class="fa fa-download"></i> Baixar assinado
                        </a>
                    </div>

                    <div class="mybp-card-corpo mb-3" :class="classeCorpoStatusDoc(documentoDetalhe.status)">
                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-file-alt"
                                    label="Tipo"
                                    :valor="labelTipoDoc(documentoDetalhe.tipo_documento)"
                                    :forte="true"
                                ></mybp-card-campo>
                                <mybp-card-campo
                                    icon="fas fa-user"
                                    label="Solicitante"
                                    :valor="textoCampo(documentoDetalhe.solicitante && documentoDetalhe.solicitante.nome)"
                                ></mybp-card-campo>
                                <mybp-card-campo
                                    icon="fas fa-clock"
                                    label="Criado em"
                                    :valor="formatarDataDoc(documentoDetalhe.created_at)"
                                ></mybp-card-campo>
                            </div>
                        </section>
                        <section class="mybp-card-secao" v-if="documentoDetalhe.status === 'em_assinatura'">
                            <div class="alert alert-warning mb-0 py-2 px-3">
                                <i class="fas fa-hourglass-half"></i>
                                Documento pendente de assinatura
                            </div>
                        </section>
                    </div>

                    <fieldset class="mybp-modal-secao">
                        <legend>Signatários</legend>
                        <div
                            class="alert alert-secondary mb-0"
                            v-if="!(documentoDetalhe.signatarios || []).length"
                        >
                            Nenhum signatário encontrado.
                        </div>
                        <div
                            class="mybp-assinatura-signatario"
                            v-for="s in documentoDetalhe.signatarios || []"
                            :key="s.id"
                        >
                            <div class="d-flex align-items-start justify-content-between flex-wrap mb-2" style="gap: 8px;">
                                <span class="mybp-badge-id">Ordem {{ s.ordem }}</span>
                                <mybp-status-badge
                                    :variante="varianteStatusSignatario(s.status)"
                                    :texto="labelStatusSignatario(s.status)"
                                ></mybp-status-badge>
                            </div>
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-user"
                                    label="Nome"
                                    :valor="textoCampo(s.nome)"
                                    :forte="true"
                                ></mybp-card-campo>
                                <mybp-card-campo
                                    icon="fas fa-envelope"
                                    label="E-mail"
                                    :valor="textoCampo(s.email)"
                                ></mybp-card-campo>
                                <mybp-card-campo
                                    v-if="s.cpf"
                                    icon="fas fa-id-card"
                                    label="CPF"
                                    :valor="textoCampo(s.cpf)"
                                ></mybp-card-campo>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Eventos (auditoria)</legend>
                        <div
                            class="alert alert-secondary mb-0"
                            v-if="!(documentoDetalhe.eventos || []).length"
                        >
                            Nenhum evento registrado.
                        </div>
                        <div class="mybp-assinatura-eventos" v-else>
                            <div
                                class="mybp-assinatura-evento"
                                v-for="ev in documentoDetalhe.eventos || []"
                                :key="ev.id"
                                :class="'mybp-assinatura-evento--' + (ev.evento || 'neutro')"
                            >
                                <div class="mybp-assinatura-evento__cabecalho">
                                    <span class="mybp-assinatura-evento__icone">
                                        <i :class="iconeEventoDoc(ev.evento)"></i>
                                    </span>
                                    <span class="mybp-assinatura-evento__titulo">{{ labelEventoDoc(ev.evento) }}</span>
                                    <span class="mybp-assinatura-evento__data">{{ formatarDataDoc(ev.created_at) }}</span>
                                </div>
                                <div
                                    class="mybp-assinatura-evento__detalhes"
                                    v-if="detalhesEventoDoc(ev).length"
                                >
                                    <div
                                        class="mybp-assinatura-evento__detalhe"
                                        v-for="(linha, idx) in detalhesEventoDoc(ev)"
                                        :key="linha.id || idx"
                                    >
                                        <span class="mybp-assinatura-evento__label">{{ linha.label }}:</span>
                                        <span class="mybp-assinatura-evento__value">{{ linha.value }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button
                    v-if="documentoDetalhe && podeCancelarDoc(documentoDetalhe)"
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    @click="cancelarDocNoModal"
                >
                    <i class="fa fa-ban"></i> Cancelar documento
                </button>
                <button
                    v-if="documentoDetalhe && podeReenviarDoc(documentoDetalhe)"
                    type="button"
                    class="btn btn-sm btn-warning"
                    @click="reenviarDocNoModal"
                >
                    <i class="fa fa-envelope"></i> Reenviar e-mail
                </button>
                <button
                    v-if="documentoDetalhe && documentoExpiradoOuCanceladoDoc(documentoDetalhe)"
                    type="button"
                    class="btn btn-sm btn-success"
                    @click="enviarNovamenteNoModal"
                >
                    <i class="fas fa-redo"></i> Enviar novamente
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import Modal from '../../Modal'
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'

export default {
    name: 'AcaoAssinaturaDocumento',
    components: { Modal, MybpCardCampo, MybpStatusBadge },
    props: {
        idPrefix: { type: String, required: true },
        tituloEnviar: { type: String, default: 'Enviar para assinatura digital' },
        getNomeDocumento: { type: Function, default: () => 'Documento' },
        getSignatariosIniciais: { type: Function, default: () => [{ nome: '', email: '', cpf: '' }] },
        validarSignatarios: {
            type: Function,
            default: (signatarios) => signatarios.length > 0 && signatarios.every((s) => s.nome && s.email)
        },
        camposSignatarioBloqueados: { type: Boolean, default: true },
        enviarHandler: { type: Function, required: true },
        atualizarHandler: { type: Function, default: null }
    },
    data() {
        return {
            contextoEnvio: null,
            contextoGerenciar: null,
            signatarios: [],
            preloadEnvio: false,
            preloadGerenciar: false,
            documentoDetalhe: null
        }
    },
    computed: {
        modalEnviarId() {
            return `modalAssinatura_${this.idPrefix}`
        },
        modalGerenciarId() {
            return `modalGerenciarAssinatura_${this.idPrefix}`
        },
        signatariosValidos() {
            return this.validarSignatarios(this.signatarios || [])
        },
        nomeDocumentoAtual() {
            return this.getNomeDocumento(this.contextoEnvio)
        }
    },
    methods: {
        textoCampo(valor) {
            const texto = String(valor ?? '').trim()
            return texto !== '' ? texto : 'Não informado'
        },
        abrirEnvio(contexto) {
            this.contextoEnvio = contexto
            this.signatarios = this.getSignatariosIniciais(contexto) || [{ nome: '', email: '', cpf: '' }]
            this.preloadEnvio = false
            this.$nextTick(() => {
                this.$refs.modalEnviar?.abrirModal?.()
            })
        },
        abrirGerenciar(documento, contexto = null) {
            if (!documento || !documento.id) return
            this.contextoGerenciar = contexto
            this.documentoDetalhe = null
            this.preloadGerenciar = true
            this.$refs.modalGerenciar?.abrirModal?.()
            const idOrToken = documento.token || documento.id
            axios
                .get(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}`)
                .then((res) => {
                    this.documentoDetalhe = res.data
                    this.preloadGerenciar = false
                })
                .catch(() => {
                    this.preloadGerenciar = false
                    mostraErro('', 'Erro ao carregar detalhe do documento.')
                })
        },
        adicionarSignatario() {
            this.signatarios.push({ nome: '', email: '', cpf: '' })
        },
        removerSignatario(index) {
            this.signatarios.splice(index, 1)
        },
        enviar() {
            if (!this.signatariosValidos) return
            this.preloadEnvio = true
            Promise.resolve(this.enviarHandler({ contexto: this.contextoEnvio, signatarios: this.signatarios }))
                .then((res) => {
                    this.preloadEnvio = false
                    this.$refs.modalEnviar?.fecharModal?.()
                    if (res && res.data) {
                        mostraSucesso(res.data.message || 'Documento enviado para assinatura.')
                        if (res.data.links && res.data.links.length && this.$swal) {
                            const msg = res.data.links.map((l) => `${l.email}: ${l.link}`).join('\n')
                            this.$swal.fire({ title: 'Links enviados', text: msg, icon: 'info' })
                        }
                    } else {
                        mostraSucesso('Documento enviado para assinatura.')
                    }
                    if (this.atualizarHandler) this.atualizarHandler()
                })
                .catch((err) => {
                    this.preloadEnvio = false
                    const msg =
                        err?.response?.data?.message || 'Erro ao enviar para assinatura.'
                    mostraErro(msg)
                })
        },
        documentoExpiradoOuCanceladoDoc(doc) {
            return doc && (doc.status === 'expirado' || doc.status === 'cancelado')
        },
        enviarNovamenteNoModal() {
            this.$refs.modalGerenciar?.fecharModal?.()
            this.$nextTick(() => this.abrirEnvio(this.contextoGerenciar))
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
        varianteStatusDoc(status) {
            const map = {
                em_assinatura: 'pendente',
                concluido: 'aprovado',
                cancelado: 'reprovado',
                expirado: 'neutro',
                rascunho: 'aberto',
                enviado: 'rh'
            }
            return map[status] || 'neutro'
        },
        classeCorpoStatusDoc(status) {
            return `mybp-card-corpo--${this.varianteStatusDoc(status)}`
        },
        labelStatusSignatario(status) {
            const map = {
                pendente: 'Pendente',
                aguardando: 'Aguardando',
                assinado: 'Assinado',
                recusado: 'Recusado',
                expirado: 'Expirado'
            }
            return map[status] || status || '—'
        },
        varianteStatusSignatario(status) {
            const map = {
                pendente: 'pendente',
                aguardando: 'gestor',
                assinado: 'aprovado',
                recusado: 'reprovado',
                expirado: 'neutro'
            }
            return map[status] || 'neutro'
        },
        formatarDataDoc(val) {
            if (!val) return '—'
            const d = typeof val === 'string' ? new Date(val) : val
            return (
                d.toLocaleDateString('pt-BR') +
                ' ' +
                (d.toLocaleTimeString
                    ? d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    : '')
            )
        },
        labelEventoDoc(evento) {
            const map = {
                enviado: 'Documento enviado',
                reenviado: 'E-mail reenviado',
                visualizado: 'Visualizado pelo signatário',
                assinado: 'Assinado',
                recusado: 'Recusado',
                expirado: 'Documento expirado',
                cancelado: 'Documento cancelado',
                download: 'Download do documento assinado'
            }
            return map[evento] || evento
        },
        iconeEventoDoc(evento) {
            const map = {
                enviado: 'fas fa-paper-plane text-info',
                reenviado: 'fas fa-paper-plane text-warning',
                visualizado: 'fas fa-eye text-primary',
                assinado: 'fas fa-pen-fancy text-success',
                recusado: 'fas fa-times-circle text-danger',
                expirado: 'fas fa-clock text-secondary',
                cancelado: 'fas fa-ban text-danger',
                download: 'fas fa-download text-success'
            }
            return map[evento] || 'fas fa-circle text-muted'
        },
        getSignatarioByIdDoc(signatarioId) {
            const list = this.documentoDetalhe?.signatarios || []
            const s = list.find((x) => x.id === signatarioId)
            return s ? s.nome || s.email || `#${signatarioId}` : null
        },
        detalhesEventoDoc(ev) {
            const p = ev.payload || {}
            const linhas = []
            const signatarioNome = p.signatario_id ? this.getSignatarioByIdDoc(p.signatario_id) : null
            switch (ev.evento) {
                case 'enviado':
                    if (p.nome) linhas.push({ label: 'Enviado por', value: p.nome })
                    if (p.signatarios_count !== undefined) {
                        linhas.push({ label: 'Signatários', value: `${p.signatarios_count} signatário(s)` })
                    }
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
                    if (p.hash_evidencia) {
                        linhas.push({
                            label: 'Hash evidência',
                            value:
                                p.hash_evidencia.length > 20
                                    ? p.hash_evidencia.substring(0, 20) + '…'
                                    : p.hash_evidencia
                        })
                    }
                    break
                case 'recusado':
                    if (signatarioNome) linhas.push({ label: 'Signatário', value: signatarioNome })
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email })
                    if (p.motivo) linhas.push({ label: 'Motivo', value: p.motivo })
                    break
                case 'cancelado':
                    if (p.user_id) linhas.push({ label: 'Usuário', value: `ID ${p.user_id}` })
                    break
                case 'download':
                    if (p.nome) linhas.push({ label: 'Usuário', value: p.nome })
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email })
                    if (p.ip) linhas.push({ label: 'IP', value: p.ip })
                    break
                default:
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email })
                    if (p.motivo) linhas.push({ label: 'Motivo', value: p.motivo })
            }
            return linhas
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
            const idOrToken = doc?.token || doc?.id || ''
            return `${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/download-assinado`
        },
        cancelarDocNoModal() {
            if (!this.documentoDetalhe) return
            const confirmar = () => this.executarCancelarDoc(this.documentoDetalhe)
            if (!this.$swal) {
                if (confirm('Cancelar este documento? Os signatários não poderão mais assinar.')) confirmar()
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
                    if (result.isConfirmed) confirmar()
                })
        },
        executarCancelarDoc(doc) {
            const idOrToken = doc?.token || doc?.id || ''
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/cancelar`)
                .then((res) => {
                    mostraSucesso(res.data.message || 'Documento cancelado.')
                    this.documentoDetalhe = null
                    this.$refs.modalGerenciar?.fecharModal?.()
                    if (this.atualizarHandler) this.atualizarHandler()
                })
                .catch((err) => {
                    const msg = err?.response?.data?.message || 'Erro ao cancelar.'
                    mostraErro(msg)
                })
        },
        reenviarDocNoModal() {
            if (!this.documentoDetalhe || this.documentoDetalhe.status !== 'em_assinatura') return
            const idOrToken = this.documentoDetalhe.token || this.documentoDetalhe.id
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/reenviar-email`)
                .then((res) => {
                    mostraSucesso(res.data.message || 'E-mail reenviado.')
                })
                .catch((err) => {
                    const msg = err?.response?.data?.message || 'Erro ao reenviar e-mail.'
                    mostraErro(msg)
                })
        }
    }
}
</script>

<style scoped>
.mybp-assinatura-signatario {
    padding: 0.75rem 0.85rem;
    margin-bottom: 0.65rem;
    border: 1px solid #eef1f4;
    border-radius: 8px;
    background: #fff;
}

.mybp-assinatura-signatario:last-child {
    margin-bottom: 0;
}

.mybp-assinatura-eventos {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.mybp-assinatura-evento {
    background: #fff;
    border: 1px solid #eef1f4;
    border-radius: 8px;
    padding: 0.7rem 0.85rem;
    border-left: 4px solid #adb5bd;
}

.mybp-assinatura-evento--enviado {
    border-left-color: #17a2b8;
}

.mybp-assinatura-evento--reenviado {
    border-left-color: #ffc107;
}

.mybp-assinatura-evento--visualizado {
    border-left-color: #174257;
}

.mybp-assinatura-evento--assinado,
.mybp-assinatura-evento--download {
    border-left-color: #28a745;
}

.mybp-assinatura-evento--recusado,
.mybp-assinatura-evento--cancelado {
    border-left-color: #dc3545;
}

.mybp-assinatura-evento--expirado {
    border-left-color: #6c757d;
}

.mybp-assinatura-evento__cabecalho {
    display: flex;
    align-items: center;
    gap: 0.5rem 0.75rem;
    flex-wrap: wrap;
}

.mybp-assinatura-evento__icone {
    font-size: 1rem;
    width: 1.25rem;
    text-align: center;
    flex-shrink: 0;
}

.mybp-assinatura-evento__titulo {
    font-weight: 600;
    color: #212529;
    font-size: 0.875rem;
}

.mybp-assinatura-evento__data {
    margin-left: auto;
    font-size: 0.75rem;
    color: #6c757d;
}

.mybp-assinatura-evento__detalhes {
    padding-left: 1.9rem;
    margin-top: 0.35rem;
    font-size: 0.813rem;
}

.mybp-assinatura-evento__detalhe {
    display: flex;
    gap: 0.35rem;
    margin-top: 0.15rem;
}

.mybp-assinatura-evento__label {
    color: #6c757d;
    font-weight: 500;
    flex-shrink: 0;
}

.mybp-assinatura-evento__value {
    color: #212529;
    word-break: break-word;
}
</style>
