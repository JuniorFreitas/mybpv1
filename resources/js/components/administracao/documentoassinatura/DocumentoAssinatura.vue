<template>
    <div id="componente" class="documento-assinatura-grid">
        <fieldset class="filtros-documento-assinatura">
            <legend>Filtro</legend>
            <form @submit.prevent="atualizar">
                <div class="row mb-2">
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2 mb-md-0">
                        <label class="mb-0">Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.status">
                            <option value="">Todos</option>
                            <option value="rascunho">Rascunho</option>
                            <option value="enviado">Enviado</option>
                            <option value="em_assinatura">Em assinatura</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="expirado">Expirado</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2 mb-md-0">
                        <label class="mb-0">Tipo documento</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.tipo_documento">
                            <option value="">Todos</option>
                            <option v-for="(label, key) in tiposDocumento" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2 mb-md-0">
                        <label class="mb-0">Solicitante</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.solicitante_id">
                            <option value="">Todos</option>
                            <option v-for="s in listaSolicitantes" :key="s.id" :value="s.id">{{ s.nome }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 col-lg-3 mb-2 mb-md-0">
                        <label class="mb-0">Signatário (nome, e-mail ou CPF)</label>
                        <input type="text" class="form-control form-control-sm" v-model="controle.dados.signatario" placeholder="Buscar signatário..." />
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-12 col-md-6 col-lg-4 mb-2 mb-md-0">
                        <date-range-filter
                            v-model:enabled="controle.dados.filtroPeriodo"
                            v-model:start-date="controle.dados.data_inicio"
                            v-model:end-date="controle.dados.data_fim"
                            :disabled="controle.carregando"
                            label="Filtrar por período (criação)"
                            :id-suffix="hash"
                            wrapper-class="w-100 mb-0"
                        >
                        </date-range-filter>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 d-flex align-items-center flex-wrap">
                        <button type="submit" class="btn btn-sm btn-success mr-2 mb-1" :disabled="controle.carregando">
                            <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary mb-1" @click="limparFiltros"><i class="fa fa-eraser"></i> Limpar</button>
                    </div>
                </div>
            </form>
        </fieldset>

        <div class="assinatura-uso-card" v-if="resumoAssinaturas">
            <div class="uso-header">
                <div>
                    <div class="uso-titulo">Consumo mensal de assinaturas</div>
                    <div class="uso-subtitulo">Competência: {{ formatarCompetencia(resumoAssinaturas.competencia) }}</div>
                </div>
            </div>
            <div class="uso-metricas">
                <div class="uso-metrica">
                    <span class="uso-label">Limite mensal</span>
                    <strong class="uso-valor">{{ resumoAssinaturas.limite_mensal === null ? 'Sem limite' : resumoAssinaturas.limite_mensal }}</strong>
                </div>
                <div class="uso-metrica">
                    <span class="uso-label">Usadas</span>
                    <strong class="uso-valor">{{ resumoAssinaturas.usadas || 0 }}</strong>
                </div>
                <div class="uso-metrica">
                    <span class="uso-label">Restantes</span>
                    <strong class="uso-valor">{{ resumoAssinaturas.restantes === null ? 'Ilimitado' : resumoAssinaturas.restantes }}</strong>
                </div>
            </div>
            <div class="uso-progress-wrap" v-if="resumoAssinaturas.limite_mensal !== null">
                <div class="uso-progress">
                    <div class="uso-progress-bar" :style="{ width: percentualUsoBar + '%' }"></div>
                </div>
                <span class="uso-progress-text">{{ percentualUsoBar }}% utilizado</span>
            </div>
            <div class="uso-extrato" v-if="resumoAssinaturas.extrato_por_tipo && resumoAssinaturas.extrato_por_tipo.length">
                <span class="uso-extrato-label">Extrato do mês por tipo:</span>
                <span class="uso-chip" v-for="item in resumoAssinaturas.extrato_por_tipo" :key="item.tipo_documento"> {{ item.label }}: {{ item.total }} </span>
            </div>
            <div class="uso-acoes">
                <button type="button" class="btn btn-sm btn-outline-primary mr-2" :disabled="exportandoXlsx" @click="exportarExtrato('xlsx')">
                    <i :class="exportandoXlsx ? 'fa fa-spinner fa-spin' : 'fa fa-file-excel'"></i> Extrato XLSX
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" :disabled="exportandoPdf" @click="exportarExtrato('pdf')">
                    <i :class="exportandoPdf ? 'fa fa-spinner fa-spin' : 'fa fa-file-pdf'"></i> Extrato PDF
                </button>
            </div>
        </div>

        <div class="assinatura-uso-card" v-if="configCota">
            <div class="uso-header">
                <div>
                    <div class="uso-titulo">Configuração de cota e alertas</div>
                    <div class="uso-subtitulo">Defina limite mensal e destinatários dos alertas de 80%, 90% e 100%</div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 col-md-3 mb-2">
                    <label class="mb-1">Limite mensal</label>
                    <input
                        type="number"
                        min="0"
                        class="form-control form-control-sm"
                        v-model.number="configCota.limite_assinaturas_mensal"
                        placeholder="Ex: 100"
                    />
                    <small class="text-muted">Vazio = sem limite</small>
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <label class="mb-1">Usuários para alerta</label>
                    <select class="form-control form-control-sm" multiple v-model="configCota.assinatura_alerta_user_ids">
                        <option v-for="u in configCota.usuarios || []" :key="u.id" :value="u.id">{{ u.nome }} ({{ u.email || 'sem e-mail' }})</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <label class="mb-1">Grupos para alerta</label>
                    <select class="form-control form-control-sm" multiple v-model="configCota.assinatura_alerta_grupo_ids">
                        <option v-for="g in configCota.grupos || []" :key="g.id" :value="g.id">{{ g.nome }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-success w-100" :disabled="salvandoConfigCota" @click="salvarConfigCota">
                        <i :class="salvandoConfigCota ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i>
                    </button>
                </div>
            </div>
        </div>

        <preload class="mt-2 text-center" v-if="controle.carregando"></preload>

        <div class="empty-state" v-show="!controle.carregando && lista.length === 0">
            <div class="empty-state-icon"><i class="fas fa-file-signature"></i></div>
            <h3 class="empty-state-title">Nenhum registro encontrado</h3>
            <p class="empty-state-text">Ajuste os filtros ou aguarde documentos enviados para assinatura.</p>
        </div>

        <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
            <div class="solicitacao-card" v-for="item in lista" :key="item.id" :class="'card-status-' + (item.status || '')">
                <div class="card-header-row">
                    <div class="card-left">
                        <span class="badge-id">#{{ item.id }}</span>
                        <div class="colaborador-principal">
                            <i class="fas fa-file-contract mr-1"></i>
                            <strong>{{ labelTipo(item.tipo_documento) }}</strong>
                        </div>
                        <span class="status-badge" :class="'status-' + (item.status || '')">
                            {{ labelStatus(item.status) }}
                        </span>
                    </div>
                    <div class="card-right">
                        <div class="dropdown show">
                            <a
                                class="btn-actions-compact"
                                href="#"
                                role="button"
                                :id="'dropdownDoc_' + item.id"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                                title="Opções"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right" :aria-labelledby="'dropdownDoc_' + item.id">
                                <a class="dropdown-item" href="javascript://" @click.prevent="abrirDetalhe(item.id)">
                                    <i class="fas fa-eye mr-2 text-primary"></i> Ver detalhes e eventos
                                </a>
                                <a v-if="podeCancelar(item)" class="dropdown-item" href="javascript://" @click.prevent="confirmarCancelar(item)">
                                    <i class="fas fa-times mr-2 text-danger"></i> Cancelar documento
                                </a>
                                <a v-if="podeReenviar(item)" class="dropdown-item" href="javascript://" @click.prevent="reenviarEmail(item)">
                                    <i class="fas fa-envelope mr-2 text-warning"></i> Reenviar e-mail
                                </a>
                                <a v-if="podeBaixarAssinado(item)" class="dropdown-item" :href="urlDownloadAssinado(item.id)" target="_blank" rel="noopener">
                                    <i class="fas fa-download mr-2 text-success"></i> Baixar documento assinado
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-details-row card-details-main" v-if="item.status === 'em_assinatura'">
                    <div class="detail-item detail-item-alerta">
                        <i class="fas fa-hourglass-half"></i>
                        <span class="detail-value">Documento pendente de assinatura</span>
                    </div>
                </div>
                <div class="card-details-row card-details-main">
                    <div class="detail-item">
                        <i class="fas fa-user"></i>
                        <span class="detail-label">Solicitante</span>
                        <span class="detail-value" :class="{ 'detail-value-empty': !(item.solicitante && item.solicitante.nome) }">
                            {{ (item.solicitante && item.solicitante.nome) || 'Não informado' }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-plus"></i>
                        <span class="detail-label">Criado em</span>
                        <span class="detail-value">{{ formatarData(item.created_at) }}</span>
                    </div>
                </div>
                <div class="card-details-row card-signatarios" v-if="item.signatarios && item.signatarios.length > 0">
                    <div class="signatarios-legend"><i class="fas fa-pen-fancy"></i> Signatários</div>
                    <div class="signatarios-lista">
                        <div class="signatario-linha" v-for="s in item.signatarios" :key="s.id">
                            <span class="signatario-status" :class="statusSignatarioClass(s.status)" :title="statusSignatarioTitle(s.status)">
                                <i class="fas" :class="statusSignatarioIcon(s.status)"></i>
                            </span>
                            <span class="signatario-nome">{{ s.nome || '—' }}</span>
                            <span class="signatario-email">{{ s.email || '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-details-row card-details-main" v-if="item.data_expiracao || item.ordem_assinatura">
                    <div class="detail-item" v-if="item.data_expiracao">
                        <i class="fas fa-clock"></i>
                        <span class="detail-label">Expira em</span>
                        <span class="detail-value" :class="{ 'detail-value-expirado': isExpirado(item.data_expiracao) }">
                            {{ formatarData(item.data_expiracao) }}
                        </span>
                    </div>
                    <div class="detail-item" v-if="item.ordem_assinatura">
                        <i class="fas fa-sort-amount-down"></i>
                        <span class="detail-label">Ordem</span>
                        <span class="detail-value">{{ item.ordem_assinatura === 'sequencial' ? 'Sequencial' : 'Paralelo' }}</span>
                    </div>
                </div>
                <div class="card-details-row card-details-fixas">
                    <div class="detail-item" v-if="item.updated_at">
                        <i class="fas fa-sync-alt"></i>
                        <span class="detail-label">Última atualização</span>
                        <span class="detail-value">{{ formatarData(item.updated_at) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <controle-paginacao
            class="d-flex justify-content-center mt-2"
            id="controle"
            ref="componente"
            :url="urlPaginacao"
            :por-pagina="porPagina"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando"
        ></controle-paginacao>

        <!-- Modal detalhe: eventos e signatários -->
        <modal :id="'modalDetalheDoc_' + hash" titulo="Documento para assinatura" :size="75">
            <template #conteudo>
                <div v-if="detalhe" class="container-fluid">
                    <p>
                        <strong>ID:</strong> {{ detalhe.id }} &nbsp;|&nbsp; <strong>Tipo:</strong> {{ labelTipo(detalhe.tipo_documento) }} &nbsp;|&nbsp;
                        <strong>Status:</strong> <span class="badge" :class="badgeStatus(detalhe.status)">{{ labelStatus(detalhe.status) }}</span>
                    </p>
                    <p>
                        <strong>Solicitante:</strong> {{ (detalhe.solicitante && detalhe.solicitante.nome) || '—' }} &nbsp;|&nbsp; <strong>Criado em:</strong>
                        {{ formatarData(detalhe.created_at) }}
                    </p>
                    <p v-if="detalhe.status === 'em_assinatura'" class="text-warning mb-2">
                        <i class="fas fa-hourglass-half"></i> Documento pendente de assinatura
                    </p>
                    <p v-else-if="podeBaixarAssinado(detalhe)" class="mb-2">
                        <a :href="urlDownloadAssinado(detalhe.id)" target="_blank" rel="noopener" class="btn btn-sm btn-success"
                            ><i class="fa fa-download"></i> Baixar documento assinado</a
                        >
                    </p>

                    <fieldset>
                        <legend>Signatários</legend>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Ordem</th>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in detalhe.signatarios || []" :key="s.id">
                                    <td>{{ s.ordem }}</td>
                                    <td>{{ s.nome }}</td>
                                    <td>{{ s.email }}</td>
                                    <td>
                                        <span class="badge badge-sm" :class="s.status === 'assinado' ? 'badge-success' : 'badge-secondary'">{{
                                            s.status
                                        }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>

                    <fieldset>
                        <legend>Eventos (auditoria)</legend>
                        <div class="eventos-auditoria">
                            <div v-for="ev in detalhe.eventos || []" :key="ev.id" class="evento-item" :class="'evento-' + (ev.evento || '')">
                                <div class="evento-cabecalho">
                                    <span class="evento-icone"><i :class="iconeEvento(ev.evento)"></i></span>
                                    <span class="evento-titulo">{{ labelEvento(ev.evento) }}</span>
                                    <span class="evento-data">{{ formatarData(ev.created_at) }}</span>
                                </div>
                                <div class="evento-detalhes" v-if="detalhesEvento(ev).length">
                                    <div class="evento-detalhe" v-for="(linha, idx) in detalhesEvento(ev)" :key="idx">
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
            <template #rodape>
                <button v-if="detalhe && podeCancelar(detalhe)" type="button" class="btn btn-sm btn-danger mr-1" @click="cancelarNoModal">
                    Cancelar documento
                </button>
                <button v-if="detalhe && podeReenviar(detalhe)" type="button" class="btn btn-sm btn-warning mr-1" @click="reenviarNoModal">
                    Reenviar e-mail
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import ControlePaginacao from '../../ControlePaginacao'
import Modal from '../../Modal'
import DateRangeFilter from '../../DateRangeFilter.vue'

const STATUS_LABELS = {
    rascunho: 'Rascunho',
    enviado: 'Enviado',
    em_assinatura: 'Em assinatura',
    concluido: 'Concluído',
    expirado: 'Expirado',
    cancelado: 'Cancelado'
}

export default {
    name: 'DocumentoAssinatura',
    components: { ControlePaginacao, Modal, DateRangeFilter },
    props: {
        documentoIdInicial: { type: [String, Number], default: null }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2, 8),
            lista: [],
            detalhe: null,
            porPagina: 15,
            urlPaginacao: `${typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''}/administracao/documento-assinatura/atualizar`,
            listaSolicitantes: [],
            resumoAssinaturas: null,
            configCota: null,
            salvandoConfigCota: false,
            exportandoXlsx: false,
            exportandoPdf: false,
            controle: {
                carregando: false,
                dados: {
                    status: '',
                    tipo_documento: '',
                    solicitante_id: '',
                    signatario: '',
                    filtroPeriodo: false,
                    data_inicio: '',
                    data_fim: '',
                    page: '',
                    id: ''
                }
            },
            tiposDocumento: {
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
        }
    },
    mounted() {
        this.urlParamGet()
        this.carregarSolicitantes()
        this.carregarConfigCota()
        this.$nextTick(() => {
            if (this.$refs.componente) {
                const p = parseInt(this.controle.dados.page, 10)
                if (p >= 1) this.$refs.componente.atual = p
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            } else {
                this.atualizar()
            }
            const idInicial = this.documentoIdInicial != null ? this.documentoIdInicial : new URLSearchParams(window.location.search).get('id')
            const id = parseInt(idInicial, 10)
            if (!isNaN(id) && id > 0) {
                this.$nextTick(() => this.abrirDetalhe(id))
            }
        })
    },
    watch: {
        'controle.dados': {
            handler() {
                if (this._syncUrlTimer) clearTimeout(this._syncUrlTimer)
                this._syncUrlTimer = setTimeout(() => this.syncUrlFiltros(), 400)
            },
            deep: true
        }
    },
    methods: {
        formatarCompetencia(competencia) {
            if (!competencia) return '—'
            const [ano, mes] = String(competencia).split('-')
            const data = new Date(Number(ano), Number(mes) - 1, 1)
            return data.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            if (urlParams.get('status')) this.controle.dados.status = urlParams.get('status')
            if (urlParams.get('tipo_documento')) this.controle.dados.tipo_documento = urlParams.get('tipo_documento')
            if (urlParams.get('solicitante_id')) this.controle.dados.solicitante_id = urlParams.get('solicitante_id')
            if (urlParams.get('signatario')) this.controle.dados.signatario = urlParams.get('signatario')
            if (urlParams.get('data_inicio')) this.controle.dados.data_inicio = urlParams.get('data_inicio')
            if (urlParams.get('data_fim')) this.controle.dados.data_fim = urlParams.get('data_fim')
            if (urlParams.get('data_inicio') || urlParams.get('data_fim')) this.controle.dados.filtroPeriodo = true
            if (urlParams.get('page')) this.controle.dados.page = urlParams.get('page')
            const idParam = urlParams.get('id') || (this.documentoIdInicial != null && this.documentoIdInicial !== '' ? String(this.documentoIdInicial) : '')
            if (idParam) this.controle.dados.id = idParam
        },
        syncUrlFiltros() {
            const d = this.controle.dados
            const atual = this.$refs.componente && this.$refs.componente.atual ? this.$refs.componente.atual : 1
            const params = {}
            if (d.status) params.status = d.status
            if (d.tipo_documento) params.tipo_documento = d.tipo_documento
            if (d.solicitante_id) params.solicitante_id = d.solicitante_id
            if (d.signatario) params.signatario = d.signatario
            if (d.data_inicio) params.data_inicio = d.data_inicio
            if (d.data_fim) params.data_fim = d.data_fim
            if (d.id) params.id = d.id
            if (atual > 1) params.page = atual
            const qs = new URLSearchParams(params).toString()
            const url = qs ? `${window.location.pathname}?${qs}` : window.location.pathname
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, '', url)
            }
        },
        carregou(dados) {
            this.lista = dados && dados.itens ? dados.itens : []
            this.resumoAssinaturas = dados && dados.resumo_assinaturas ? dados.resumo_assinaturas : null
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            if (this.$refs.componente) {
                this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },
        limparFiltros() {
            this.controle.dados.status = ''
            this.controle.dados.tipo_documento = ''
            this.controle.dados.solicitante_id = ''
            this.controle.dados.signatario = ''
            this.controle.dados.filtroPeriodo = false
            this.controle.dados.data_inicio = ''
            this.controle.dados.data_fim = ''
            this.controle.dados.page = ''
            this.controle.dados.id = ''
            this.syncUrlFiltros()
            this.atualizar()
        },
        carregarSolicitantes() {
            const url = `${typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''}/administracao/documento-assinatura/solicitantes`
            axios
                .get(url)
                .then((res) => {
                    this.listaSolicitantes = Array.isArray(res.data) ? res.data : []
                })
                .catch(() => {
                    this.listaSolicitantes = []
                })
        },
        carregarConfigCota() {
            axios
                .get(`${URL_ADMIN}/administracao/documento-assinatura/config`)
                .then((res) => {
                    const data = res.data || {}
                    this.configCota = {
                        limite_assinaturas_mensal: data.limite_assinaturas_mensal,
                        assinatura_alerta_user_ids: (data.assinatura_alerta_user_ids || []).map((id) => Number(id)),
                        assinatura_alerta_grupo_ids: (data.assinatura_alerta_grupo_ids || []).map((id) => Number(id)),
                        usuarios: data.usuarios || [],
                        grupos: data.grupos || []
                    }
                    if (!this.resumoAssinaturas && data.resumo_assinaturas) {
                        this.resumoAssinaturas = data.resumo_assinaturas
                    }
                })
                .catch(() => {
                    this.configCota = null
                })
        },
        salvarConfigCota() {
            if (!this.configCota) return
            this.salvandoConfigCota = true
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/config`, {
                    limite_assinaturas_mensal: this.configCota.limite_assinaturas_mensal === '' ? null : this.configCota.limite_assinaturas_mensal,
                    assinatura_alerta_user_ids: this.configCota.assinatura_alerta_user_ids || [],
                    assinatura_alerta_grupo_ids: this.configCota.assinatura_alerta_grupo_ids || []
                })
                .then((res) => {
                    if (typeof mostraSucesso !== 'undefined') mostraSucesso(res.data && res.data.message ? res.data.message : 'Configuração salva.')
                    this.salvandoConfigCota = false
                    this.carregarConfigCota()
                    this.atualizar()
                })
                .catch((err) => {
                    this.salvandoConfigCota = false
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao salvar configuração.'
                    if (typeof mostraErro !== 'undefined') mostraErro(msg)
                })
        },
        exportarExtrato(formato) {
            if (formato === 'xlsx') this.exportandoXlsx = true
            if (formato === 'pdf') this.exportandoPdf = true
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/extrato/exportar`, {
                    formato,
                    referencia: this.resumoAssinaturas ? this.resumoAssinaturas.competencia : null
                })
                .then((res) => {
                    if (typeof mostraSucesso !== 'undefined') mostraSucesso(res.data && res.data.message ? res.data.message : 'Exportação solicitada.')
                })
                .catch((err) => {
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao solicitar exportação.'
                    if (typeof mostraErro !== 'undefined') mostraErro(msg)
                })
                .finally(() => {
                    if (formato === 'xlsx') this.exportandoXlsx = false
                    if (formato === 'pdf') this.exportandoPdf = false
                })
        },
        labelTipo(tipo) {
            return this.tiposDocumento[tipo] || tipo || '—'
        },
        labelStatus(status) {
            return STATUS_LABELS[status] || status || '—'
        },
        badgeStatus(status) {
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
        formatarData(val) {
            if (!val) return '—'
            const d = typeof val === 'string' ? new Date(val) : val
            return d.toLocaleDateString('pt-BR') + ' ' + (d.toLocaleTimeString ? d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : '')
        },
        contagemSignatarios(item) {
            const list = item.signatarios || []
            const total = list.length
            const assinados = list.filter((s) => s.status === 'assinado').length
            return { total, assinados }
        },
        isExpirado(dataExpiracao) {
            if (!dataExpiracao) return false
            const d = typeof dataExpiracao === 'string' ? new Date(dataExpiracao) : dataExpiracao
            return d.getTime() < Date.now()
        },
        podeCancelar(item) {
            return item && ['rascunho', 'em_assinatura'].indexOf(item.status) !== -1
        },
        podeReenviar(item) {
            return item && item.status === 'em_assinatura'
        },
        statusSignatarioIcon(status) {
            if (status === 'assinado') return 'fa-check-circle'
            if (status === 'recusado') return 'fa-times-circle'
            if (status === 'expirado') return 'fa-clock'
            return 'fa-clock'
        },
        statusSignatarioClass(status) {
            if (status === 'assinado') return 'assinado'
            if (status === 'recusado') return 'recusado'
            if (status === 'expirado') return 'expirado'
            return 'pendente'
        },
        statusSignatarioTitle(status) {
            const t = { assinado: 'Assinado', recusado: 'Recusado', expirado: 'Expirado' }
            return t[status] || 'Pendente'
        },
        podeBaixarAssinado(item) {
            return item && item.status === 'concluido' && item.arquivo_assinado_id
        },
        urlDownloadAssinado(id) {
            return `${typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''}/administracao/documento-assinatura/${id}/download-assinado`
        },
        abrirDetalhe(id) {
            this.detalhe = null
            axios
                .get(`${URL_ADMIN}/administracao/documento-assinatura/${id}`)
                .then((res) => {
                    this.detalhe = res.data
                    this.$nextTick(() => {
                        $(`#modalDetalheDoc_${this.hash}`).modal('show')
                    })
                })
                .catch(() => {
                    if (typeof mostraErro !== 'undefined') mostraErro('', 'Erro ao carregar detalhe.')
                })
        },
        fecharDetalhe() {
            $(`#modalDetalheDoc_${this.hash}`).modal('hide')
        },
        confirmarCancelar(item) {
            if (!this.$swal) {
                if (confirm('Cancelar este documento? Os signatários não poderão mais assinar.')) this.executarCancelar(item.id)
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
                    if (result.isConfirmed) this.executarCancelar(item.id)
                })
        },
        executarCancelar(id) {
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/${id}/cancelar`)
                .then((res) => {
                    if (res.data.success && typeof mostraSucesso !== 'undefined') mostraSucesso(res.data.message || 'Documento cancelado.')
                    this.atualizar()
                    if (this.detalhe && this.detalhe.id === id) this.fecharDetalhe()
                })
                .catch((err) => {
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao cancelar.'
                    if (typeof mostraErro !== 'undefined') mostraErro(msg)
                    else alert(msg)
                })
        },
        cancelarNoModal() {
            if (this.detalhe) this.confirmarCancelar(this.detalhe)
        },
        reenviarEmail(item) {
            if (!item || item.status !== 'em_assinatura') return
            axios
                .post(`${URL_ADMIN}/administracao/documento-assinatura/${item.id}/reenviar-email`)
                .then((res) => {
                    if (res.data.success && typeof mostraSucesso !== 'undefined') mostraSucesso(res.data.message || 'E-mail reenviado.')
                })
                .catch((err) => {
                    const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Erro ao reenviar e-mail.'
                    if (typeof mostraErro !== 'undefined') mostraErro(msg)
                    else alert(msg)
                })
        },
        reenviarNoModal() {
            if (this.detalhe) this.reenviarEmail(this.detalhe)
        },
        getSignatarioById(signatarioId) {
            const list = this.detalhe && this.detalhe.signatarios ? this.detalhe.signatarios : []
            const s = list.find((x) => x.id === signatarioId)
            return s ? s.nome || s.email || `#${signatarioId}` : null
        },
        iconeEvento(evento) {
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
        labelEvento(evento) {
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
        detalhesEvento(ev) {
            const p = ev.payload || {}
            const linhas = []
            const signatarioNome = p.signatario_id ? this.getSignatarioById(p.signatario_id) : null

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
                    if (p.data_utc) linhas.push({ label: 'Data/hora (UTC)', value: this.formatarData(p.data_utc) })
                    if (p.ip) linhas.push({ label: 'IP', value: p.ip })
                    if (p.hash_evidencia)
                        linhas.push({
                            label: 'Hash evidência',
                            value: p.hash_evidencia.length > 20 ? p.hash_evidencia.substring(0, 20) + '…' : p.hash_evidencia
                        })
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
                case 'expirado':
                    break
                default:
                    if (p.email) linhas.push({ label: 'E-mail', value: p.email })
                    if (p.motivo) linhas.push({ label: 'Motivo', value: p.motivo })
            }
            return linhas
        }
    },
    computed: {
        percentualUsoBar() {
            if (!this.resumoAssinaturas || this.resumoAssinaturas.percentual_uso === null || this.resumoAssinaturas.percentual_uso === undefined) {
                return 0
            }
            const v = Number(this.resumoAssinaturas.percentual_uso)
            if (isNaN(v)) return 0
            return Math.max(0, Math.min(100, Math.round(v)))
        }
    }
}
</script>

<style scoped>
.documento-assinatura-grid .assinatura-uso-card {
    background: linear-gradient(145deg, #f8fbff 0%, #ffffff 100%);
    border: 1px solid #dbe7f3;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}
.documento-assinatura-grid .uso-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.documento-assinatura-grid .uso-titulo {
    font-size: 1rem;
    font-weight: 700;
    color: #174257;
}
.documento-assinatura-grid .uso-subtitulo {
    font-size: 0.813rem;
    color: #6c757d;
}
.documento-assinatura-grid .uso-metricas {
    margin-top: 0.75rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.5rem;
}
.documento-assinatura-grid .uso-metrica {
    min-width: 120px;
}
.documento-assinatura-grid .uso-label {
    display: block;
    font-size: 0.75rem;
    color: #6c757d;
}
.documento-assinatura-grid .uso-valor {
    font-size: 1.1rem;
    color: #212529;
}
.documento-assinatura-grid .uso-progress-wrap {
    margin-top: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.documento-assinatura-grid .uso-progress {
    flex: 1;
    height: 8px;
    border-radius: 999px;
    background: #e9ecef;
    overflow: hidden;
}
.documento-assinatura-grid .uso-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #17a2b8 0%, #174257 100%);
}
.documento-assinatura-grid .uso-progress-text {
    font-size: 0.75rem;
    color: #495057;
    font-weight: 600;
}
.documento-assinatura-grid .uso-extrato {
    margin-top: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}
.documento-assinatura-grid .uso-extrato-label {
    font-size: 0.75rem;
    color: #6c757d;
}
.documento-assinatura-grid .uso-chip {
    border-radius: 999px;
    border: 1px solid #dbe7f3;
    background: #fff;
    color: #174257;
    font-size: 0.75rem;
    padding: 0.22rem 0.55rem;
}
.documento-assinatura-grid .uso-acoes {
    margin-top: 0.75rem;
}

/* Filtros */
.filtros-documento-assinatura label {
    font-size: 0.875rem;
    font-weight: 500;
}
.filtros-documento-assinatura .form-control-sm {
    height: calc(1.5em + 0.5rem);
}

/* Empty state */
.documento-assinatura-grid .empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
    border-radius: 12px;
    border: 1px dashed #dee2e6;
}
.documento-assinatura-grid .empty-state-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: #6c757d;
}
.documento-assinatura-grid .empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}
.documento-assinatura-grid .empty-state-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0;
}

/* Cards list */
.documento-assinatura-grid .cards-lista {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.documento-assinatura-grid .solicitacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 0;
    transition: all 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border-left: 4px solid #6c757d;
    overflow: hidden;
}
.documento-assinatura-grid .solicitacao-card.card-status-em_assinatura {
    border-left-color: #ffc107;
}
.documento-assinatura-grid .solicitacao-card.card-status-concluido {
    border-left-color: #28a745;
}
.documento-assinatura-grid .solicitacao-card.card-status-cancelado {
    border-left-color: #dc3545;
}
.documento-assinatura-grid .solicitacao-card.card-status-expirado {
    border-left-color: #6c757d;
}
.documento-assinatura-grid .solicitacao-card.card-status-rascunho {
    border-left-color: #6c757d;
}
.documento-assinatura-grid .solicitacao-card.card-status-enviado {
    border-left-color: #17a2b8;
}
.documento-assinatura-grid .solicitacao-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    border-color: #ced4da;
    transform: translateY(-1px);
}

.documento-assinatura-grid .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
    border-bottom: 1px solid #f1f3f5;
}
.documento-assinatura-grid .card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
    min-width: 0;
}
.documento-assinatura-grid .card-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}
.documento-assinatura-grid .badge-id {
    background: #174257;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.75rem;
    white-space: nowrap;
    flex-shrink: 0;
}
.documento-assinatura-grid .colaborador-principal {
    display: flex;
    align-items: center;
    font-size: 1rem;
    color: #212529;
    overflow: hidden;
    min-width: 0;
}
.documento-assinatura-grid .colaborador-principal i {
    color: #174257;
    flex-shrink: 0;
}
.documento-assinatura-grid .colaborador-principal strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 600;
}
.documento-assinatura-grid .status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    border-radius: 20px;
    font-size: 0.688rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    white-space: nowrap;
    flex-shrink: 0;
}
.documento-assinatura-grid .status-em_assinatura {
    background: #ffc107;
    color: #212529;
}
.documento-assinatura-grid .status-concluido {
    background: #28a745;
    color: white;
}
.documento-assinatura-grid .status-cancelado {
    background: #dc3545;
    color: white;
}
.documento-assinatura-grid .status-expirado {
    background: #6c757d;
    color: white;
}
.documento-assinatura-grid .status-rascunho {
    background: #6c757d;
    color: white;
}
.documento-assinatura-grid .status-enviado {
    background: #17a2b8;
    color: white;
}

.documento-assinatura-grid .btn-actions-compact {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #dee2e6;
    color: #6c757d;
    transition: all 0.2s ease;
    flex-shrink: 0;
    padding: 0;
    cursor: pointer;
}
.documento-assinatura-grid .btn-actions-compact:hover {
    background: #174257;
    border-color: #174257;
    color: white;
}
.documento-assinatura-grid .dropdown-menu-custom {
    min-width: 11rem;
    padding: 0.25rem 0;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border: 1px solid #e9ecef;
    border-radius: 8px;
}
.documento-assinatura-grid .dropdown-menu-custom .dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
}
.documento-assinatura-grid .dropdown-menu-custom .dropdown-item i {
    width: 1.25rem;
    text-align: center;
}
.documento-assinatura-grid .dropdown-menu-custom .dropdown-item:hover {
    background-color: #f8f9fa;
    color: #174257;
}
.documento-assinatura-grid .detail-item-alerta .detail-value {
    color: #856404;
    font-weight: 500;
}
.documento-assinatura-grid .detail-item-alerta i {
    color: #856404;
}

.documento-assinatura-grid .card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.5rem;
    padding: 1rem 1.25rem;
}
.documento-assinatura-grid .card-details-row.card-details-main {
    padding-top: 0.75rem;
}
.documento-assinatura-grid .card-details-row.card-signatarios {
    flex-direction: column;
    align-items: stretch;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: #fafbfc;
    border-top: 1px solid #f1f3f5;
}
.documento-assinatura-grid .signatarios-legend {
    font-size: 0.813rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.25rem;
}
.documento-assinatura-grid .signatarios-legend i {
    margin-right: 0.35rem;
    color: #174257;
}
.documento-assinatura-grid .signatarios-lista {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.documento-assinatura-grid .signatario-linha {
    display: flex;
    align-items: center;
    gap: 0.5rem 0.75rem;
    font-size: 0.813rem;
    flex-wrap: wrap;
}
.documento-assinatura-grid .signatario-status {
    flex-shrink: 0;
    width: 1.25rem;
    text-align: center;
}
.documento-assinatura-grid .signatario-status.assinado {
    color: #28a745;
}
.documento-assinatura-grid .signatario-status.pendente {
    color: #6c757d;
}
.documento-assinatura-grid .signatario-status.recusado {
    color: #dc3545;
}
.documento-assinatura-grid .signatario-status.expirado {
    color: #6c757d;
}
.documento-assinatura-grid .signatario-nome {
    font-weight: 500;
    color: #212529;
    min-width: 0;
}
.documento-assinatura-grid .signatario-email {
    color: #6c757d;
    font-size: 0.75rem;
    min-width: 0;
}

.documento-assinatura-grid .card-details-row.card-details-fixas {
    background: #fafbfc;
    padding: 0.75rem 1.25rem;
    border-top: 1px solid #f1f3f5;
}
.documento-assinatura-grid .detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.813rem;
    min-width: 0;
}
.documento-assinatura-grid .detail-item i:first-child {
    flex-shrink: 0;
    font-size: 0.875rem;
    color: #6c757d;
}
.documento-assinatura-grid .detail-label {
    font-weight: 500;
    color: #6c757d;
    white-space: nowrap;
}
.documento-assinatura-grid .detail-value {
    color: #212529;
    font-weight: 400;
}
.documento-assinatura-grid .detail-value-empty {
    color: #adb5bd;
    font-style: italic;
}
.documento-assinatura-grid .detail-value-expirado {
    color: #dc3545;
    font-weight: 600;
}

@media (max-width: 768px) {
    .documento-assinatura-grid .card-header-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
    }
    .documento-assinatura-grid .card-right {
        width: 100%;
        justify-content: flex-end;
    }
    .documento-assinatura-grid .card-details-row {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Eventos auditoria (modal) */
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
.evento-item.evento-download {
    border-left-color: #28a745;
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
