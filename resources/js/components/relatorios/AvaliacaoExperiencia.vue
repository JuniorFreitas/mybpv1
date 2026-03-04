<template>
    <div class="avaliacao-experiencia">
        <fieldset class="mt-2">
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="atualizar">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.status" :disabled="controle.carregando"
                                @change="atualizar">
                            <option value="">Todos</option>
                            <option value="VENCIDO">Vencido</option>
                            <option value="VENCE HOJE">Vence Hoje</option>
                            <option value="A VENCER">A Vencer</option>
                            <option value="COMPLETA">Completa</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Colaborador</label>
                        <input type="text" class="form-control form-control-sm" v-model="controle.dados.nome" placeholder="Digite o nome..." :disabled="controle.carregando"
                               @keyup.enter="atualizar">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Centro de Custo</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.centroCusto" :disabled="controle.carregando"
                                @change="atualizar">
                            <option value="">Todos</option>
                            <option value="__SEM_CENTRO__">Sem Centro de Custo</option>
                            <option v-for="cc in centrosCusto" :key="cc" :value="cc">{{ cc }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Gestor</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.gestor" :disabled="controle.carregando"
                                @change="atualizar">
                            <option value="">Todos</option>
                            <option value="__SEM_GESTOR__">Sem Gestor</option>
                            <option v-for="g in gestores" :key="g.id" :value="String(g.id)">{{ g.nome }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Avaliações</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.avaliacoes" :disabled="controle.carregando"
                                @change="atualizar">
                            <option value="">Todas</option>
                            <option value="0">Sem Avaliação</option>
                            <option value="1">Uma Avaliação</option>
                            <option value="2">Duas Avaliações</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Exibir</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.pages" :disabled="controle.carregando"
                                @change="atualizar">
                            <option v-for="n in por_pagina" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Cargo</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.cargo" :disabled="controle.carregando"
                                @change="atualizar">
                            <option value="">Todos</option>
                            <option v-for="c in cargos" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Função</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.funcao" :disabled="controle.carregando"
                                @change="atualizar">
                            <option value="">Todos</option>
                            <option v-for="f in funcoes" :key="f" :value="f">{{ f }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Definição</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.definicaoContrato" :disabled="controle.carregando"
                                @change="atualizar">
                            <option value="">Todas</option>
                            <option value="prorroga">Prorroga o contrato</option>
                            <option value="finaliza">Finaliza o contrato</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 d-flex flex-wrap">
                    <button type="button" class="btn btn-sm btn-success mr-1" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-1" :disabled="controle.carregando" @click="limparFiltros">
                        <i class="fas fa-eraser"></i> Limpar Filtros
                    </button>
                    <button type="button" class="btn btn-sm btn-primary mr-1"
                            :disabled="controle.carregando || preloadExportacao"
                            @click.prevent="exportaExcel">
                        <i :class="preloadExportacao ? 'fa fa-spinner fa-spin' : 'fas fa-file-excel'"></i> Exportar Excel
                    </button>
                </div>
            </form>
        </fieldset>

        <preload v-show="controle.carregando" class="text-center"></preload>
     

        <div v-if="!controle.carregando">
            <div class="alert alert-warning" v-show="lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <template v-if="lista.length > 0">
            <!-- Toggle Resumo -->
            <div class="mb-3">
                <button class="btn btn-sm btn-outline-secondary" @click="toggleResumo">
                    <i :class="resumoOculto ? 'fas fa-eye' : 'fas fa-eye-slash'"></i>
                    {{ resumoOculto ? 'Mostrar Resumo' : 'Ocultar Resumo' }}
                </button>
            </div>

            <!-- Cards de Resumo -->
            <div v-show="!resumoOculto" id="cardsResumo" class="mb-4">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total de Avaliações</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ resumo.total }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-clipboard-check fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Vencidas</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ resumo.vencidos }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Vence Hoje</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ resumo.vence_hoje }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">A Vencer</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ resumo.a_vencer }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-calendar-alt fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-left-secondary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Sem Avaliação</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ resumo.sem_avaliacao }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Com Uma Avaliação</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ resumo.uma_avaliacao }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Completas</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ resumo.completas }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-check-double fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-left-dark shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Gestores Envolvidos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ resumo.gestores_unicos }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-user-tie fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-dark shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Sem Gestor</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ resumo.sem_gestor }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-user-slash fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Cards -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold" style="color: #003755;">
                        Avaliações Pendentes
                        <small class="text-muted">(Gerado em: {{ dataGeracao }})</small>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informações:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Os links de avaliação são gerados automaticamente e têm validade de 60 dias.</li>
                            <li>Cada colaborador pode realizar no máximo 2 avaliações de 90 dias.</li>
                            <li>Links expirados ou já utilizados não podem ser reutilizados.</li>
                            <li>Você pode copiar os links individualmente ou todos de uma vez para compartilhar com os gestores.</li>
                        </ul>
                    </div>

                    <div class="mb-3 d-flex justify-content-end align-items-center">
                        <button v-if="podeGerarLinks"
                                class="btn btn-sm btn-outline-primary mr-2"
                                :disabled="gerandoLote"
                                @click="gerarLinksLote">
                            <span class="spinner-border spinner-border-sm" v-show="gerandoLote" role="status"></span>
                            <span>{{ gerandoLote ? 'Processando...' : 'Gerar todos os links (página atual)' }}</span>
                        </button>
                    </div>

                    <div class="cards-lista-avaliacao90">
                        <div v-for="v in lista"
                             :key="v.feedback_id || v.colaborador"
                             :class="['avaliacao-card', 'card-status-' + statusSlug(v.status), definicaoCardClass(v.definicao_contrato)]">
                            <div class="card-header-row">
                                <div class="card-left">
                                    <span :class="['status-badge', 'status-' + statusSlug(v.status)]">
                                        <i :class="statusIcon(v.status)"></i> {{ v.status }}
                                    </span>
                                    <div class="colaborador-principal">
                                        <i class="fas fa-user-circle mr-1"></i>
                                        <strong>{{ v.colaborador }}</strong>
                                    </div>
                                </div>
                                <div class="card-right card-link-cell">
                                    <template v-if="v.link_avaliacao">
                                        <a :href="v.link_avaliacao" target="_blank" class="btn btn-sm btn-success" title="Abrir avaliação em nova aba">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </template>
                                    <template v-else>
                                        <button v-if="podeGerarLink(v)"
                                                class="btn btn-sm btn-outline-primary btn-gerar-link"
                                                :disabled="gerandoLinkId === v.feedback_id"
                                                @click="gerarLink(v)">
                                            <span class="spinner-border spinner-border-sm" v-show="gerandoLinkId === v.feedback_id"></span>
                                            <span>{{ gerandoLinkId === v.feedback_id ? 'Gerando...' : 'Gerar link' }}</span>
                                        </button>
                                        <span v-else-if="ehAvaliacaoCompleta(v)" class="text-muted small"><i class="fas fa-check-double"></i> Avaliação completa</span>
                                        <span v-else class="text-muted small">Restrito ao gestor</span>
                                    </template>
                                </div>
                            </div>
                            <div class="card-details-row card-details-main">
                                <div class="detail-item">
                                    <i class="fas fa-user-tie"></i>
                                    <span class="detail-label">Gestor</span>
                                    <span class="detail-value">{{ v.gestor_nome || '—' }}<small v-if="v.gestor_login" class="text-muted d-block">{{ v.gestor_login }}</small></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span class="detail-label">Cargo</span>
                                    <span class="detail-value">{{ v.cargo || '—' }}</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-tasks"></i>
                                    <span class="detail-label">Função</span>
                                    <span class="detail-value">{{ v.funcao || '—' }}</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-building"></i>
                                    <span class="detail-label">Centro de Custo</span>
                                    <span class="detail-value">{{ v.centro_custo || '—' }}</span>
                                </div>
                            </div>
                            <div class="card-details-row card-details-fixas">
                                <div class="detail-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span class="detail-label">Vencimento</span>
                                    <span class="detail-value">{{ v.prazo_vencido }}</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-hourglass-half"></i>
                                    <span class="detail-label">Dias</span>
                                    <span class="detail-value">
                                        <span v-if="v.status === 'A VENCER'" class="badge badge-info">{{ v.dias_para_vencer }} dias</span>
                                        <span v-else-if="v.status === 'VENCE HOJE'" class="badge badge-warning">Hoje</span>
                                        <span v-else-if="v.status === 'COMPLETA'" class="badge badge-secondary">—</span>
                                        <span v-else class="badge badge-danger">{{ v.dias_atraso }} dias atrás</span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clipboard-check"></i>
                                    <span class="detail-label">Avaliações</span>
                                    <span class="detail-value">
                                        <span v-if="v.qnt_avaliacoes === 0" class="badge badge-secondary"><i class="fas fa-times"></i> Nenhuma</span>
                                        <span v-else-if="v.qnt_avaliacoes === 1" class="badge badge-primary"><i class="fas fa-check"></i> 1 Avaliação</span>
                                        <span v-else class="badge badge-success"><i class="fas fa-check-double"></i> {{ v.qnt_avaliacoes }} Avaliações</span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-gavel"></i>
                                    <span class="detail-label">Definição</span>
                                    <span class="detail-value">
                                        <span v-if="v.definicao_contrato === 'prorroga'" class="badge badge-success"><i class="fas fa-check-circle"></i> Prorroga o contrato</span>
                                        <span v-else-if="v.definicao_contrato === 'finaliza'" class="badge badge-danger"><i class="fas fa-times-circle"></i> Finaliza o contrato</span>
                                        <span v-else class="text-muted">—</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            </template>
        </div>

           <!-- Sempre no DOM para ref e primeira carga -->
           <controle-paginacao
            v-show="lista.length > 0"
            class="d-flex justify-content-center mt-3 mb-2"
            ref="componente"
            :url="urlPaginacao"
            :por-pagina="controle.dados.pages"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando"
        />
    </div>
</template>

<script>
import preload from '../preload.vue';
import ControlePaginacao from '../ControlePaginacao.vue';

export default {
    name: 'AvaliacaoExperiencia',

    components: {
        preload,
        ControlePaginacao
    },

    inject: {
        atualizarUrlMovimentacao: { default: () => () => {} }
    },

    props: {
        apiBase: {
            type: String,
            default: '/g/relatorios/avaliacao-de-experiencia'
        },
        currentUserId: {
            type: [Number, String],
            default: null
        }
    },

    data() {
        return {
            urlPaginacao: '',
            resumo: {
                total: 0,
                vencidos: 0,
                vence_hoje: 0,
                a_vencer: 0,
                sem_avaliacao: 0,
                uma_avaliacao: 0,
                completas: 0,
                gestores_unicos: 0,
                sem_gestor: 0
            },
            lista: [],
            dataGeracao: '',
            centrosCusto: [],
            gestores: [],
            cargos: [],
            funcoes: [],
            userCanGestaoRh: false,
            isGestorGlobal: false,
            controle: {
                carregando: true,
                dados: {
                    pages: 20,
                    status: '',
                    nome: '',
                    centroCusto: '',
                    gestor: '',
                    avaliacoes: '',
                    cargo: '',
                    funcao: '',
                    definicaoContrato: ''
                }
            },
            resumoOculto: false,
            gerandoLinkId: null,
            gerandoLote: false,
            preloadExportacao: false
        };
    },

    computed: {
        por_pagina() {
            return [20, 50, 100, 150];
        },
        podeGerarLinks() {
            return this.userCanGestaoRh || this.isGestorGlobal;
        }
    },

    watch: {
        'controle.dados': {
            handler() {
                if (this._syncUrlTimer) clearTimeout(this._syncUrlTimer);
                this._syncUrlTimer = setTimeout(() => this.syncUrlFiltros(), 400);
            },
            deep: true
        }
    },

    mounted() {
        const oculto = localStorage.getItem('avaliacao90dias_resumo_oculto') === 'true';
        this.resumoOculto = oculto;
        this.urlPaginacao = (this.apiBase || '').replace(/\/$/, '') + '/atualizar';
        this.urlParamGet();
        this.$nextTick(() => {
            const comp = this.$refs.componente;
            if (comp && typeof comp.buscar === 'function') {
                comp.atual = 1;
                comp.buscar();
            }
        });
    },

    methods: {
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('pages')) this.controle.dados.pages = parseInt(urlParams.get('pages'), 10) || 20;
            if (urlParams.get('status')) this.controle.dados.status = urlParams.get('status');
            if (urlParams.get('nome')) this.controle.dados.nome = urlParams.get('nome');
            if (urlParams.get('centroCusto')) this.controle.dados.centroCusto = urlParams.get('centroCusto');
            if (urlParams.get('gestor')) this.controle.dados.gestor = urlParams.get('gestor');
            if (urlParams.get('avaliacoes') !== null && urlParams.get('avaliacoes') !== undefined) this.controle.dados.avaliacoes = urlParams.get('avaliacoes');
            if (urlParams.get('cargo')) this.controle.dados.cargo = urlParams.get('cargo');
            if (urlParams.get('funcao')) this.controle.dados.funcao = urlParams.get('funcao');
            if (urlParams.get('definicaoContrato')) this.controle.dados.definicaoContrato = urlParams.get('definicaoContrato');
        },
        syncUrlFiltros() {
            const d = this.controle.dados;
            const params = new URLSearchParams();
            const pages = d.pages || 20;
            if (pages !== 20) params.set('pages', String(pages));
            if (d.status) params.set('status', d.status);
            if (d.nome) params.set('nome', d.nome);
            if (d.centroCusto) params.set('centroCusto', d.centroCusto);
            if (d.gestor) params.set('gestor', d.gestor);
            if (d.avaliacoes !== '' && d.avaliacoes !== undefined) params.set('avaliacoes', d.avaliacoes);
            if (d.cargo) params.set('cargo', d.cargo);
            if (d.funcao) params.set('funcao', d.funcao);
            if (d.definicaoContrato) params.set('definicaoContrato', d.definicaoContrato);
            const query = params.toString();
            const url = query
                ? window.location.pathname + '?' + query
                : window.location.pathname;
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, '', url);
            }
            if (typeof this.atualizarUrlMovimentacao === 'function') {
                this.atualizarUrlMovimentacao({ pages, status: d.status, nome: d.nome, centroCusto: d.centroCusto, gestor: d.gestor, avaliacoes: d.avaliacoes, cargo: d.cargo, funcao: d.funcao, definicaoContrato: d.definicaoContrato });
            }
        },

        carregou(dados) {
            this.controle.carregando = false;
            if (!dados || typeof dados !== 'object') {
                this.lista = [];
                return;
            }
            this.lista = Array.isArray(dados.itens) ? dados.itens : [];
            if (dados.resumo) this.resumo = dados.resumo;
            if (dados.data_geracao) this.dataGeracao = dados.data_geracao;
            if (Array.isArray(dados.centros_custo)) this.centrosCusto = dados.centros_custo;
            if (Array.isArray(dados.gestores)) this.gestores = dados.gestores;
            if (Array.isArray(dados.cargos)) this.cargos = dados.cargos;
            if (Array.isArray(dados.funcoes)) this.funcoes = dados.funcoes;
            if (typeof dados.user_can_gestao_rh !== 'undefined') this.userCanGestaoRh = !!dados.user_can_gestao_rh;
            if (typeof dados.is_gestor_global !== 'undefined') this.isGestorGlobal = !!dados.is_gestor_global;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            if (!this.$refs.componente) return;
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1);
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null;
        },

        statusSlug(s) {
            if (!s) return '';
            return String(s).toLowerCase().replace(/\s+/g, '-');
        },

        statusIcon(s) {
            if (s === 'VENCIDO') return 'fas fa-exclamation-triangle';
            if (s === 'VENCE HOJE') return 'fas fa-clock';
            if (s === 'COMPLETA') return 'fas fa-check';
            return 'fas fa-calendar-alt';
        },

        definicaoCardClass(definicao) {
            if (definicao === 'prorroga') return 'card-definicao-prorroga';
            if (definicao === 'finaliza') return 'card-definicao-finaliza';
            return '';
        },

        ehAvaliacaoCompleta(v) {
            return v.status === 'COMPLETA' || (v.qnt_avaliacoes >= 2);
        },
        podeGerarLink(v) {
            if (this.ehAvaliacaoCompleta(v)) return false;
            const userId = this.currentUserId != null ? this.currentUserId : (typeof window !== 'undefined' && window.Laravel && window.Laravel.user && window.Laravel.user.id) ? window.Laravel.user.id : null;
            const ehGestor = userId && v.gestor_id && parseInt(v.gestor_id, 10) === parseInt(userId, 10);
            return ehGestor || this.userCanGestaoRh;
        },

        gerarLink(v) {
            const feedbackId = v.feedback_id ? parseInt(v.feedback_id, 10) : null;
            if (!feedbackId) {
                if (typeof toastr !== 'undefined') toastr.error('ID de feedback não encontrado.');
                return;
            }
            this.gerandoLinkId = feedbackId;
            const urlPost = this.apiBase.replace(/\/$/, '') + '/' + feedbackId + '/gerar-link';
            axios.post(urlPost)
                .then(() => {
                    this.consultarLinkPolling(feedbackId, (link) => {
                        this.gerandoLinkId = null;
                        if (link) {
                            const item = this.lista.find(x => x.feedback_id == feedbackId);
                            if (item) item.link_avaliacao = link;
                            if (typeof toastr !== 'undefined') toastr.success('Link gerado com sucesso.');
                        } else {
                            if (typeof toastr !== 'undefined') toastr.info('Geração enfileirada. O link deve aparecer em instantes.');
                        }
                    });
                })
                .catch((err) => {
                    this.gerandoLinkId = null;
                    const msg = (err.response && err.response.data && err.response.data.message) || 'Erro ao enfileirar geração do link';
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                });
        },

        consultarLinkPolling(feedbackId, callback, tentativas = 10, intervalo = 1000) {
            const urlGet = this.apiBase.replace(/\/$/, '') + '/' + feedbackId + '/link';
            let count = 0;
            const timer = setInterval(() => {
                axios.get(urlGet)
                    .then((r) => {
                        const link = r.data && r.data.link ? r.data.link : null;
                        if (link) {
                            clearInterval(timer);
                            callback(link);
                        } else if (++count >= tentativas) {
                            clearInterval(timer);
                            callback(null);
                        }
                    })
                    .catch(() => {
                        if (++count >= tentativas) {
                            clearInterval(timer);
                            callback(null);
                        }
                    });
            }, intervalo);
        },

        gerarLinksLote() {
            if (!this.podeGerarLinks) {
                if (typeof toastr !== 'undefined') toastr.warning('Você não tem permissão para gerar links em lote.');
                return;
            }
            const userId = this.currentUserId != null ? this.currentUserId : (typeof window !== 'undefined' && window.Laravel && window.Laravel.user && window.Laravel.user.id) ? window.Laravel.user.id : null;
            const feedbackIds = this.lista
                .filter(v => {
                    if (v.link_avaliacao) return false;
                    if (this.ehAvaliacaoCompleta(v)) return false;
                    if (!this.userCanGestaoRh && userId && String(v.gestor_id) !== String(userId)) return false;
                    return v.feedback_id;
                })
                .map(v => parseInt(v.feedback_id, 10))
                .filter(id => id && !isNaN(id));
            if (feedbackIds.length === 0) {
                if (typeof toastr !== 'undefined') toastr.info('Nenhuma linha requer geração de link.');
                return;
            }
            this.gerandoLote = true;
            const url = this.apiBase.replace(/\/$/, '') + '/gerar-links-lote';
            axios.post(url, { feedback_ids: feedbackIds }, { headers: { 'Content-Type': 'application/json' } })
                .then((r) => {
                    const total = (r.data && r.data.total) || feedbackIds.length;
                    if (typeof toastr !== 'undefined') {
                        toastr.info(
                            total + ' link(s) enfileirado(s) para geração em segundo plano.<br><strong>Atualize a página em alguns minutos para ver os links.</strong>',
                            'Processando em background',
                            { timeOut: 8000, extendedTimeOut: 3000, closeButton: true, progressBar: true }
                        );
                    }
                })
                .catch((err) => {
                    const msg = (err.response && err.response.data && err.response.data.message) || 'Erro ao processar lote';
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                })
                .finally(() => {
                    this.gerandoLote = false;
                });
        },

        copiarTodosLinks() {
            if (!this.podeGerarLinks) {
                if (typeof toastr !== 'undefined') toastr.warning('Você não tem permissão para copiar todos os links.');
                return;
            }
            const userId = this.currentUserId != null ? this.currentUserId : (typeof window !== 'undefined' && window.Laravel && window.Laravel.user && window.Laravel.user.id) ? window.Laravel.user.id : null;
            const links = this.lista
                .filter(v => userId && v.gestor_id && parseInt(v.gestor_id, 10) === parseInt(userId, 10) && v.link_avaliacao)
                .map(v => `${v.colaborador}: ${v.link_avaliacao}`);
            if (links.length === 0) {
                if (typeof toastr !== 'undefined') toastr.warning('Nenhum link disponível para copiar! (Apenas do seu centro de custo)');
                return;
            }
            const texto = links.join('\n');
            const ta = document.createElement('textarea');
            ta.value = texto;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            if (typeof toastr !== 'undefined') toastr.success(links.length + ' links copiados para a área de transferência!');
        },

        limparFiltros() {
            this.controle.dados.status = '';
            this.controle.dados.nome = '';
            this.controle.dados.centroCusto = '';
            this.controle.dados.gestor = '';
            this.controle.dados.avaliacoes = '';
            this.controle.dados.cargo = '';
            this.controle.dados.funcao = '';
            this.controle.dados.definicaoContrato = '';
            this.atualizar();
        },

        exportaExcel() {
            this.preloadExportacao = true;
            const url = this.apiBase.replace(/\/$/, '') + '/exportar';
            const filtros = {
                status: this.controle.dados.status || '',
                nome: this.controle.dados.nome || '',
                centroCusto: this.controle.dados.centroCusto || '',
                gestor: this.controle.dados.gestor || '',
                avaliacoes: this.controle.dados.avaliacoes !== undefined ? this.controle.dados.avaliacoes : '',
                cargo: this.controle.dados.cargo || '',
                funcao: this.controle.dados.funcao || '',
                definicaoContrato: this.controle.dados.definicaoContrato || ''
            };
            axios.post(url, filtros)
                .then((r) => {
                    const msg = (r.data && r.data.msg) || 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.';
                    if (typeof toastr !== 'undefined') toastr.success(msg);
                })
                .catch((err) => {
                    const data = err.response && err.response.data;
                    const msg = (data && (data.message || data.msg)) || 'Erro ao solicitar exportação.';
                    if (err.response && err.response.status === 429 && data && data.em_processamento) {
                        if (typeof toastr !== 'undefined') toastr.info(msg);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                    }
                })
                .finally(() => {
                    this.preloadExportacao = false;
                });
        },

        toggleResumo() {
            this.resumoOculto = !this.resumoOculto;
            try {
                localStorage.setItem('avaliacao90dias_resumo_oculto', this.resumoOculto ? 'true' : 'false');
            } catch (e) {}
        },

    }
};
</script>

<style scoped>
.avaliacao-experiencia .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.avaliacao-experiencia .border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.avaliacao-experiencia .border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.avaliacao-experiencia .border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.avaliacao-experiencia .border-left-secondary { border-left: 0.25rem solid #858796 !important; }
.avaliacao-experiencia .border-left-success { border-left: 0.25rem solid #1cc88a !important; }

.cards-lista-avaliacao90 { display: flex; flex-direction: column; gap: 1rem; }
.avaliacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 0;
    transition: all 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border-left: 4px solid #6c757d;
    overflow: hidden;
}
.avaliacao-card.card-status-vencido { border-left-color: #dc3545; }
.avaliacao-card.card-status-vence-hoje { border-left-color: #f6c23e; }
.avaliacao-card.card-status-completa { border-left-color: #28a745; }
.avaliacao-card.card-status-a-vencer { border-left-color: #17a2b8; }
.avaliacao-card:hover { box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08); border-color: #ced4da; transform: translateY(-1px); }

.avaliacao-card .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
    border-bottom: 1px solid #f1f3f5;
}
.avaliacao-card .card-left { display: flex; align-items: center; gap: 0.75rem; flex: 1; overflow: hidden; min-width: 0; }
.avaliacao-card .card-right { flex-shrink: 0; }
.avaliacao-card .colaborador-principal { display: flex; align-items: center; font-size: 1rem; color: #212529; overflow: hidden; min-width: 0; }
.avaliacao-card .colaborador-principal i { color: #003755; flex-shrink: 0; }
.avaliacao-card .colaborador-principal strong { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 600; }

.avaliacao-card .status-badge {
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
.avaliacao-card .status-vencido { background: #dc3545; color: white; }
.avaliacao-card .status-vence-hoje { background: #f6c23e; color: #212529; }
.avaliacao-card .status-completa { background: #28a745; color: white; }
.avaliacao-card .status-a-vencer { background: #17a2b8; color: white; }

/* Definição: Prorroga = card todo verde; Finaliza = card todo vermelho */
.avaliacao-card.card-definicao-prorroga {
    border-left-color: #28a745 !important;
    border-color: #b8ddc9;
    background: linear-gradient(180deg, #f0f9f4 0%, #fff 30%);
}
.avaliacao-card.card-definicao-prorroga .card-header-row {
    background: linear-gradient(180deg, #d4edda 0%, #e8f5eb 100%);
    border-bottom-color: #c3e6cb;
}
.avaliacao-card.card-definicao-prorroga .card-details-fixas {
    background: #e8f5eb;
}
.avaliacao-card.card-definicao-prorroga .status-badge { background: #28a745 !important; color: white; }

.avaliacao-card.card-definicao-finaliza {
    border-left-color: #dc3545 !important;
    border-color: #f5c6cb;
    background: linear-gradient(180deg, #fdf2f3 0%, #fff 30%);
}
.avaliacao-card.card-definicao-finaliza .card-header-row {
    background: linear-gradient(180deg, #f8d7da 0%, #fce8ea 100%);
    border-bottom-color: #f5c6cb;
}
.avaliacao-card.card-definicao-finaliza .card-details-fixas {
    background: #fce8ea;
}
.avaliacao-card.card-definicao-finaliza .status-badge { background: #dc3545 !important; color: white; }

.avaliacao-card .card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.5rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f3f5;
}
.avaliacao-card .card-details-row:last-child { border-bottom: none; }
.avaliacao-card .card-details-main { padding-top: 0.75rem; }
.avaliacao-card .card-details-fixas { background: #fafbfc; padding: 0.75rem 1.25rem; }
.avaliacao-card .detail-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.813rem; min-width: 0; }
.avaliacao-card .detail-item i:first-child { flex-shrink: 0; font-size: 0.875rem; color: #6c757d; }
.avaliacao-card .detail-label { font-weight: 500; color: #6c757d; white-space: nowrap; }
.avaliacao-card .detail-value { color: #212529; font-weight: 400; }
.avaliacao-card .detail-value .badge { font-size: 0.75rem; }

@media (max-width: 768px) {
    .avaliacao-card .card-header-row { flex-direction: column; align-items: flex-start; gap: 0.5rem; padding: 0.75rem 1rem; }
    .avaliacao-card .card-right { width: 100%; justify-content: flex-end; }
}
</style>
