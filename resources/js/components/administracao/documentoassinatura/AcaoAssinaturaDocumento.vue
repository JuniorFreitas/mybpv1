<template>
    <div>
        <modal :id="modalEnviarId" :titulo="tituloEnviar" :fechar="!preloadEnvio" :size="75">
            <template slot="conteudo">
                <div v-if="contextoEnvio">
                    <p class="mb-2">
                        Documento: <strong>{{ nomeDocumentoAtual }}</strong>
                    </p>
                    <fieldset>
                        <legend>Signatários</legend>
                        <div class="row mb-2" v-for="(s, idx) in signatarios" :key="idx">
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-sm" v-model="s.nome" placeholder="Nome" :disabled="camposSignatarioBloqueados" />
                            </div>
                            <div class="col-md-4">
                                <input type="email" class="form-control form-control-sm" v-model="s.email" placeholder="E-mail" :disabled="camposSignatarioBloqueados" />
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control form-control-sm" v-model="s.cpf" placeholder="CPF (opcional)" :disabled="camposSignatarioBloqueados" />
                            </div>
                            <div class="col-md-2" v-if="!camposSignatarioBloqueados">
                                <button type="button" class="btn btn-sm btn-outline-danger" @click="removerSignatario(idx)">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button v-if="!camposSignatarioBloqueados" type="button" class="btn btn-sm btn-primary" @click="adicionarSignatario">
                            <i class="fa fa-plus"></i> Adicionar signatário
                        </button>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-success" :disabled="preloadEnvio || !signatariosValidos" @click="enviar">
                    <span v-if="preloadEnvio"><i class="fa fa-spinner fa-pulse"></i> Enviando...</span>
                    <span v-else>Enviar para assinatura</span>
                </button>
            </template>
        </modal>

        <modal :id="modalGerenciarId" titulo="Gerenciar assinatura" :fechar="!preloadGerenciar" :size="75">
            <template slot="conteudo">
                <div v-if="documentoDetalhe" class="container-fluid">
                    <p><strong>ID:</strong> {{ documentoDetalhe.id }} &nbsp;|&nbsp; <strong>Tipo:</strong> {{ labelTipoDoc(documentoDetalhe.tipo_documento) }} &nbsp;|&nbsp; <strong>Status:</strong> <span class="badge" :class="badgeStatusDoc(documentoDetalhe.status)">{{ labelStatusDoc(documentoDetalhe.status) }}</span></p>
                    <p><strong>Solicitante:</strong> {{ (documentoDetalhe.solicitante && documentoDetalhe.solicitante.nome) || '—' }} &nbsp;|&nbsp; <strong>Criado em:</strong> {{ formatarDataDoc(documentoDetalhe.created_at) }}</p>
                    <p v-if="documentoDetalhe.status === 'em_assinatura'" class="text-warning mb-2"><i class="fas fa-hourglass-half"></i> Documento pendente de assinatura</p>
                    <p v-else-if="podeBaixarAssinadoDoc(documentoDetalhe)" class="mb-2">
                        <a :href="urlDownloadAssinadoDoc(documentoDetalhe)" target="_blank" rel="noopener" class="btn btn-sm btn-success"><i class="fa fa-download"></i> Baixar documento assinado</a>
                    </p>
                    <fieldset>
                        <legend>Signatários</legend>
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Ordem</th><th>Nome</th><th>E-mail</th><th>Status</th></tr></thead>
                            <tbody>
                            <tr v-for="s in (documentoDetalhe.signatarios || [])" :key="s.id">
                                <td>{{ s.ordem }}</td>
                                <td>{{ s.nome }}</td>
                                <td>{{ s.email }}</td>
                                <td><span class="badge badge-sm" :class="s.status === 'assinado' ? 'badge-success' : 'badge-secondary'">{{ s.status }}</span></td>
                            </tr>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend>Eventos (auditoria)</legend>
                        <div class="eventos-auditoria">
                            <div v-for="ev in (documentoDetalhe.eventos || [])" :key="ev.id" class="evento-item" :class="'evento-' + (ev.evento || '')">
                                <div class="evento-cabecalho">
                                    <span class="evento-icone"><i :class="iconeEventoDoc(ev.evento)"></i></span>
                                    <span class="evento-titulo">{{ labelEventoDoc(ev.evento) }}</span>
                                    <span class="evento-data">{{ formatarDataDoc(ev.created_at) }}</span>
                                </div>
                                <div class="evento-detalhes" v-if="detalhesEventoDoc(ev).length">
                                    <div class="evento-detalhe" v-for="(linha, idx) in detalhesEventoDoc(ev)" :key="idx">
                                        <span class="evento-detalhe-label">{{ linha.label }}:</span>
                                        <span class="evento-detalhe-value">{{ linha.value }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <p v-else class="text-center"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>
            </template>
            <template slot="rodape">
                <button v-if="documentoDetalhe && podeCancelarDoc(documentoDetalhe)" type="button" class="btn btn-sm btn-danger mr-1" @click="cancelarDocNoModal">Cancelar documento</button>
                <button v-if="documentoDetalhe && podeReenviarDoc(documentoDetalhe)" type="button" class="btn btn-sm btn-warning mr-1" @click="reenviarDocNoModal">Reenviar e-mail</button>
                <button v-if="documentoDetalhe && documentoExpiradoOuCanceladoDoc(documentoDetalhe)" type="button" class="btn btn-sm btn-success mr-1" @click="enviarNovamenteNoModal">
                    <span class="fas fa-redo"></span> Enviar novamente
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import Modal from '../../Modal';

export default {
    name: 'AcaoAssinaturaDocumento',
    components: { Modal },
    props: {
        idPrefix: { type: String, required: true },
        tituloEnviar: { type: String, default: 'Enviar para assinatura digital' },
        getNomeDocumento: { type: Function, default: () => 'Documento' },
        getSignatariosIniciais: { type: Function, default: () => [{ nome: '', email: '', cpf: '' }] },
        validarSignatarios: { type: Function, default: (signatarios) => signatarios.length > 0 && signatarios.every((s) => s.nome && s.email) },
        camposSignatarioBloqueados: { type: Boolean, default: true },
        enviarHandler: { type: Function, required: true },
        atualizarHandler: { type: Function, default: null },
    },
    data() {
        return {
            contextoEnvio: null,
            contextoGerenciar: null,
            signatarios: [],
            preloadEnvio: false,
            preloadGerenciar: false,
            documentoDetalhe: null,
        };
    },
    computed: {
        modalEnviarId() {
            return `modalAssinatura_${this.idPrefix}`;
        },
        modalGerenciarId() {
            return `modalGerenciarAssinatura_${this.idPrefix}`;
        },
        signatariosValidos() {
            return this.validarSignatarios(this.signatarios || []);
        },
        nomeDocumentoAtual() {
            return this.getNomeDocumento(this.contextoEnvio);
        },
    },
    methods: {
        abrirEnvio(contexto) {
            this.contextoEnvio = contexto;
            this.signatarios = this.getSignatariosIniciais(contexto) || [{ nome: '', email: '', cpf: '' }];
            this.preloadEnvio = false;
            this.$nextTick(() => $(`#${this.modalEnviarId}`).modal('show'));
        },
        abrirGerenciar(documento, contexto = null) {
            if (!documento || !documento.id) return;
            this.contextoGerenciar = contexto;
            this.documentoDetalhe = null;
            this.preloadGerenciar = true;
            $(`#${this.modalGerenciarId}`).modal('show');
            const idOrToken = documento.token || documento.id;
            axios.get(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}`).then((res) => {
                this.documentoDetalhe = res.data;
                this.preloadGerenciar = false;
            }).catch(() => {
                this.preloadGerenciar = false;
                mostraErro('', 'Erro ao carregar detalhe do documento.');
            });
        },
        adicionarSignatario() {
            this.signatarios.push({ nome: '', email: '', cpf: '' });
        },
        removerSignatario(index) {
            this.signatarios.splice(index, 1);
        },
        enviar() {
            if (!this.signatariosValidos) return;
            this.preloadEnvio = true;
            Promise.resolve(this.enviarHandler({ contexto: this.contextoEnvio, signatarios: this.signatarios }))
                .then((res) => {
                    this.preloadEnvio = false;
                    $(`#${this.modalEnviarId}`).modal('hide');
                    if (res && res.data) {
                        mostraSucesso(res.data.message || 'Documento enviado para assinatura.');
                        if (res.data.links && res.data.links.length && this.$swal) {
                            const msg = res.data.links.map((l) => `${l.email}: ${l.link}`).join('\n');
                            this.$swal.fire({ title: 'Links enviados', text: msg, icon: 'info' });
                        }
                    } else {
                        mostraSucesso('Documento enviado para assinatura.');
                    }
                    if (this.atualizarHandler) this.atualizarHandler();
                })
                .catch((err) => {
                    this.preloadEnvio = false;
                    const msg = err && err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao enviar para assinatura.';
                    mostraErro(msg);
                });
        },
        documentoExpiradoOuCanceladoDoc(doc) {
            return doc && (doc.status === 'expirado' || doc.status === 'cancelado');
        },
        enviarNovamenteNoModal() {
            $(`#${this.modalGerenciarId}`).modal('hide');
            this.$nextTick(() => this.abrirEnvio(this.contextoGerenciar));
        },
        labelTipoDoc(tipo) {
            const map = { contrato_legal: 'Contrato (Documentos Legais)', contrato_trabalho: 'Contrato de Trabalho', carta_oferta: 'Carta Oferta', termo_demissao: 'Termo de Demissão', ficha_encaminhamento: 'Ficha de Encaminhamento', termo_confidencialidade: 'Termo de Confidencialidade', opcao_vale_transporte: 'Opção Vale Transporte', acordo_compensacao_horas: 'Acordo de Compensação de Horas', termo_salario_familia: 'Termo Salário Família', declaracao_dependentes_ir: 'Declaração Dependentes IR', medida_administrativa: 'Medida Administrativa', documento_demissao: 'Documento de Demissão (Aviso Prévio)' };
            return map[tipo] || tipo || '—';
        },
        labelStatusDoc(status) {
            const map = { rascunho: 'Rascunho', enviado: 'Enviado', em_assinatura: 'Em assinatura', concluido: 'Concluído', expirado: 'Expirado', cancelado: 'Cancelado' };
            return map[status] || status || '—';
        },
        badgeStatusDoc(status) {
            const map = { em_assinatura: 'badge-warning', concluido: 'badge-success', cancelado: 'badge-danger', expirado: 'badge-secondary', rascunho: 'badge-secondary', enviado: 'badge-info' };
            return map[status] || 'badge-secondary';
        },
        formatarDataDoc(val) {
            if (!val) return '—';
            const d = typeof val === 'string' ? new Date(val) : val;
            return d.toLocaleDateString('pt-BR') + ' ' + (d.toLocaleTimeString ? d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : '');
        },
        labelEventoDoc(evento) {
            const map = { enviado: 'Documento enviado', reenviado: 'E-mail reenviado', visualizado: 'Visualizado pelo signatário', assinado: 'Assinado', recusado: 'Recusado', expirado: 'Documento expirado', cancelado: 'Documento cancelado', download: 'Download do documento assinado' };
            return map[evento] || evento;
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
                download: 'fas fa-download text-success',
            };
            return map[evento] || 'fas fa-circle text-muted';
        },
        getSignatarioByIdDoc(signatarioId) {
            const list = (this.documentoDetalhe && this.documentoDetalhe.signatarios) ? this.documentoDetalhe.signatarios : [];
            const s = list.find((x) => x.id === signatarioId);
            return s ? (s.nome || s.email || `#${signatarioId}`) : null;
        },
        detalhesEventoDoc(ev) {
            const p = ev.payload || {};
            const linhas = [];
            const signatarioNome = p.signatario_id ? this.getSignatarioByIdDoc(p.signatario_id) : null;
            switch (ev.evento) {
                case 'enviado':
                    if (p.nome) linhas.push({ label: 'Enviado por', value: p.nome });
                    if (p.signatarios_count !== undefined) linhas.push({ label: 'Signatários', value: `${p.signatarios_count} signatário(s)` });
                    break;
                case 'reenviado':
                    if (p.nome) linhas.push({ label: 'Reenviado por', value: p.nome });
                    if (p.user_id && !p.nome) linhas.push({ label: 'Usuário', value: `ID ${p.user_id}` });
                    break;
                case 'visualizado':
                    if (signatarioNome) linhas.push({ label: 'Signatário', value: signatarioNome });
                    if (p.ip) linhas.push({ label: 'IP', value: p.ip });
                    break;
                case 'assinado':
                    if (signatarioNome) linhas.push({ label: 'Signatário', value: signatarioNome });
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email });
                    if (p.data_utc) linhas.push({ label: 'Data/hora (UTC)', value: this.formatarDataDoc(p.data_utc) });
                    if (p.ip) linhas.push({ label: 'IP', value: p.ip });
                    if (p.hash_evidencia) linhas.push({ label: 'Hash evidência', value: p.hash_evidencia.length > 20 ? p.hash_evidencia.substring(0, 20) + '…' : p.hash_evidencia });
                    break;
                case 'recusado':
                    if (signatarioNome) linhas.push({ label: 'Signatário', value: signatarioNome });
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email });
                    if (p.motivo) linhas.push({ label: 'Motivo', value: p.motivo });
                    break;
                case 'cancelado':
                    if (p.user_id) linhas.push({ label: 'Usuário', value: `ID ${p.user_id}` });
                    break;
                case 'download':
                    if (p.nome) linhas.push({ label: 'Usuário', value: p.nome });
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email });
                    if (p.ip) linhas.push({ label: 'IP', value: p.ip });
                    break;
                default:
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email });
                    if (p.motivo) linhas.push({ label: 'Motivo', value: p.motivo });
            }
            return linhas;
        },
        podeCancelarDoc(item) {
            return item && ['rascunho', 'em_assinatura'].indexOf(item.status) !== -1;
        },
        podeReenviarDoc(item) {
            return item && item.status === 'em_assinatura';
        },
        podeBaixarAssinadoDoc(item) {
            return item && item.status === 'concluido' && item.arquivo_assinado_id;
        },
        urlDownloadAssinadoDoc(doc) {
            const idOrToken = (doc && doc.token) ? doc.token : (doc && doc.id) ? doc.id : '';
            return `${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/download-assinado`;
        },
        cancelarDocNoModal() {
            if (!this.documentoDetalhe) return;
            const confirmar = () => this.executarCancelarDoc(this.documentoDetalhe);
            if (!this.$swal) {
                if (confirm('Cancelar este documento? Os signatários não poderão mais assinar.')) confirmar();
                return;
            }
            this.$swal.fire({ title: 'Cancelar documento?', text: 'Os signatários não poderão mais assinar. Esta ação não pode ser desfeita.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonText: 'Não', confirmButtonText: 'Sim, cancelar' }).then((result) => {
                if (result.isConfirmed) confirmar();
            });
        },
        executarCancelarDoc(doc) {
            const idOrToken = (doc && doc.token) ? doc.token : (doc && doc.id) ? doc.id : '';
            axios.post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/cancelar`).then((res) => {
                mostraSucesso(res.data.message || 'Documento cancelado.');
                this.documentoDetalhe = null;
                $(`#${this.modalGerenciarId}`).modal('hide');
                if (this.atualizarHandler) this.atualizarHandler();
            }).catch((err) => {
                const msg = err && err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao cancelar.';
                mostraErro(msg);
            });
        },
        reenviarDocNoModal() {
            if (!this.documentoDetalhe || this.documentoDetalhe.status !== 'em_assinatura') return;
            const idOrToken = this.documentoDetalhe.token || this.documentoDetalhe.id;
            axios.post(`${URL_ADMIN}/administracao/documento-assinatura/${idOrToken}/reenviar-email`).then((res) => {
                mostraSucesso(res.data.message || 'E-mail reenviado.');
            }).catch((err) => {
                const msg = err && err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao reenviar e-mail.';
                mostraErro(msg);
            });
        },
    },
};
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
.evento-item.evento-enviado { border-left-color: #17a2b8; }
.evento-item.evento-reenviado { border-left-color: #ffc107; }
.evento-item.evento-visualizado { border-left-color: #007bff; }
.evento-item.evento-assinado { border-left-color: #28a745; }
.evento-item.evento-recusado { border-left-color: #dc3545; }
.evento-item.evento-expirado { border-left-color: #6c757d; }
.evento-item.evento-cancelado { border-left-color: #dc3545; }
.evento-item.evento-download { border-left-color: #28a745; }
.evento-cabecalho {
    display: flex;
    align-items: center;
    gap: 0.5rem 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 0.35rem;
}
.evento-icone { font-size: 1rem; width: 1.25rem; text-align: center; flex-shrink: 0; }
.evento-titulo { font-weight: 600; color: #212529; font-size: 0.938rem; }
.evento-data { margin-left: auto; font-size: 0.813rem; color: #6c757d; }
.evento-detalhes { padding-left: 1.9rem; font-size: 0.813rem; }
.evento-detalhe { display: flex; gap: 0.35rem; margin-top: 0.2rem; }
.evento-detalhe-label { color: #6c757d; font-weight: 500; flex-shrink: 0; }
.evento-detalhe-value { color: #212529; word-break: break-word; }
</style>
