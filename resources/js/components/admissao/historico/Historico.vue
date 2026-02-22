<template>
    <div class="historico-admissao">
        <modal id="janelaHistorico" :titulo="tituloJanela" :size="95">
            <template slot="conteudo">
                <div v-if="!openHistorico">
                    <fieldset>
                        <legend>Informações do Colaborador</legend>
                        <div style="text-transform: uppercase">
                            <span>Nome: <strong>{{ form.curriculo.nome }}</strong></span><br>
                            <span>CPF: <strong>{{ form.curriculo.cpf }}</strong></span><br>
                            <span>
                                Cargo: <strong>{{ form.admissao.cargo }}</strong> | Função: <strong>{{ form.admissao.funcao }}</strong></span><br>
                            <span>Data de admissão: <strong>{{ form.admissao.data_admissao }}</strong></span><br>
                        </div>
                    </fieldset>

                    <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist" style="border-bottom: 1px solid #653232">
                        <li class="nav-item" v-if="permissoes.dossie">
                            <a class="nav-item nav-link active" id="nav-dossie-tab" data-toggle="tab"
                               @click.prevent="trocaAba('abrirDossie')"
                               href="#nav-dossie"
                               role="tab" aria-controls="nav-dossie" aria-selected="true">DOSSIÊ</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.feedback">
                            <a class="nav-item nav-link" id="nav-feedback-historico-tab"
                               @click.prevent="trocaAba('abrirFeedbackHistorico')" data-toggle="tab"
                               href="#nav-feedback-historico"
                               role="tab" aria-controls="nav-feedback-historico" aria-selected="true">FEEDBACK</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.medida_administrativa">
                            <a class="nav-item nav-link" id="nav-medidas-administrativas-tab"
                               @click.prevent="trocaAba('abrirMedidas')" data-toggle="tab"
                               href="#nav-medidas-administrativas"
                               role="tab" aria-controls="nav-medidas-administrativas" aria-selected="true">MEDIDAS ADMINISTRATIVAS</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.avaliacao_noventa_dias">
                            <a class="nav-item nav-link" id="nav-formulario-noventa-tab" data-toggle="tab"
                               @click.prevent="trocaAba('abrirFormularioNoventa')"
                               href="#nav-formulario-noventa"
                               role="tab" aria-controls="nav-formulario-noventa" aria-selected="false">AVALIAÇÃO DE EXPERIÊNCIA</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.avaliacao_anual">
                            <a class="nav-item nav-link" id="nav-avaliacao-anual-tab" data-toggle="tab"
                               @click.prevent="trocaAba('abrirAvaliacaoAnual')"
                               href="#nav-avaliacao-anual"
                               role="tab" aria-controls="nav-avaliacao-anual" aria-selected="false">AVALIAÇÃO ANUAL</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.ferias">
                            <a class="nav-item nav-link" id="nav-ferias-tab" data-toggle="tab"
                               @click.prevent="trocaAba('abrirFerias')"
                               href="#nav-ferias"
                               role="tab" aria-controls="nav-ferias" aria-selected="false">FÉRIAS</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.afastamento">
                            <a class="nav-item nav-link" id="nav-afastamento-tab" data-toggle="tab"
                               @click.prevent="trocaAba('abrirAfastamento')"
                               href="#nav-afastamento"
                               role="tab" aria-controls="nav-ferias" aria-selected="false">AFASTAMENTO</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.beneficio">
                            <a class="nav-item nav-link" id="nav-beneficio-tab" data-toggle="tab"
                               @click.prevent="trocaAba('abrirBeneficio')"
                               href="#nav-beneficio"
                               role="tab" aria-controls="nav-beneficio" aria-selected="false">BENEFÍCIO</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.cih">
                            <a class="nav-item nav-link" id="nav-cih-tab" data-toggle="tab"
                               @click.prevent="trocaAba('abrirCih')"
                               href="#nav-cih"
                               role="tab" aria-controls="nav-cih" aria-selected="false">CIH</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.promocao">
                            <a class="nav-item nav-link" id="nav-promocao-tab" data-toggle="tab"
                               @click.prevent="trocaAba('abrirPromocao')"
                               href="#nav-promocao"
                               role="tab" aria-controls="nav-promocao" aria-selected="false">PROMOÇÃO</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.metas">
                            <a class="nav-item nav-link" id="nav-meta-tab" data-toggle="tab"
                               @click.prevent="trocaAba('abrirMetas')"
                               href="#nav-meta"
                               role="tab" aria-controls="nav-meta" aria-selected="false">METAS</a>
                        </li>
                        <li class="nav-item" v-if="permissoes.logs">
                            <a class="nav-item nav-link" id="nav-log-historico-tab"
                               @click.prevent="trocaAba('abrirLogHistorico')" data-toggle="tab"
                               href="#nav-log-historico"
                               role="tab" aria-controls="nav-log-historico" aria-selected="true">LOGS</a>
                        </li>
                    </ul>

                    <div class="tab-content py-3 p-2">
                        <div class="tab-pane fade show active" id="nav-dossie" role="tabpanel" aria-labelledby="nav-dossie-tab">
                            <dossie v-if="abas.abrirDossie && permissoes.dossie" :feedback_id="form.feedback_id"></dossie>
                        </div>
                        <div class="tab-pane fade show" id="nav-feedback-historico" role="tabpanel" aria-labelledby="nav-feedback-historico-tab">
                            <feedback-historico v-if="abas.abrirFeedbackHistorico && permissoes.feedback"
                                                :feedback_id="form.feedback_id"></feedback-historico>
                        </div>
                        <div class="tab-pane fade show" id="nav-medidas-administrativas" role="tabpanel" aria-labelledby="nav-medidas-administrativas-tab">
                            <medidas-administrativas v-if="abas.abrirMedidas && permissoes.medida_administrativa"
                                                     :feedback_id="form.feedback_id"></medidas-administrativas>
                        </div>
                        <div class="tab-pane fade show" id="nav-formulario-noventa" role="tabpanel" aria-labelledby="nav-formulario-noventa-tab">
                            <formulario-noventa-dias v-if="abas.abrirFormularioNoventa && permissoes.avaliacao_noventa_dias"
                                                     :feedback_id="form.feedback_id"
                                                     :url-base-gerar-link="urlGerarLinkAvaliacaoExperiencia"></formulario-noventa-dias>
                        </div>
                        <div class="tab-pane fade show" id="nav-avaliacao-anual" role="tabpanel" aria-labelledby="nav-avaliacao-anual-tab">
                            <avaliacao-anual v-if="abas.abrirAvaliacaoAnual && permissoes.avaliacao_anual"
                                             :feedback_id="form.feedback_id"></avaliacao-anual>
                        </div>
                        <div class="tab-pane fade show" id="nav-ferias" role="tabpanel" aria-labelledby="nav-ferias-tab">
                            <ferias v-if="abas.abrirFerias && permissoes.ferias"
                                    :feedback_id="form.feedback_id" :curriculo_id="form.curriculo_id"></ferias>
                        </div>
                        <div class="tab-pane fade show" id="nav-afastamento" role="tabpanel" aria-labelledby="nav-afastamento-tab">
                            <afastamento v-if="abas.abrirAfastamento && permissoes.afastamento"
                                         :feedback_id="form.feedback_id"></afastamento>
                        </div>
                        <div class="tab-pane fade show" id="nav-beneficio" role="tabpanel" aria-labelledby="nav-beneficio-tab">
                            <beneficio v-if="abas.abrirBeneficio && permissoes.beneficio"
                                       :feedback_id="form.feedback_id"></beneficio>
                        </div>
                        <div class="tab-pane fade show" id="nav-cih" role="tabpanel" aria-labelledby="nav-cih-tab">
                            <cih v-if="abas.abrirCih && permissoes.cih"
                                 :fc_token="form.fc_token"></cih>
                        </div>
                        <div class="tab-pane fade show" id="nav-promocao" role="tabpanel" aria-labelledby="nav-promocao-tab">
                            <promocao v-if="abas.abrirPromocao && permissoes.promocao"
                                      :feedback_id="form.feedback_id"></promocao>
                        </div>
                        <div class="tab-pane fade show" id="nav-meta" role="tabpanel" aria-labelledby="nav-meta-tab">
                            <metas v-if="abas.abrirMetas && permissoes.metas"
                                   :feedback_id="form.feedback_id"></metas>
                        </div>
                        <div class="tab-pane fade show" id="nav-log-historico" role="tabpanel" aria-labelledby="nav-log-historico-tab">
                            <logs-historico v-if="abas.abrirLogHistorico && permissoes.logs"
                                            :feedback_id="form.feedback_id"></logs-historico>
                        </div>
                    </div>
                </div>
            </template>
            <template slot="rodape">
            </template>
        </modal>

        <fieldset>
            <legend class="text-uppercase">Filtro</legend>
            <form class="row" @submit.prevent="buscar">
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Nome ou ID</label>
                        <input type="text"
                               placeholder="Buscar por nome ou ID"
                               autocomplete="off"
                               class="form-control form-control-sm"
                               :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>
                <div class="col-12 col-sm-3 col-md-2">
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text"
                               placeholder="CPF"
                               autocomplete="off"
                               class="form-control form-control-sm"
                               :disabled="controle.carregando"
                               v-model="controle.dados.campoCPF"
                               v-mascara:cpf>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="form-group">
                        <label>Cargo</label>
                        <select class="form-control form-control-sm"
                                @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.campoCargo">
                            <option value="">Todos os cargos</option>
                            <option v-for="cargo in cargos" :key="cargo.id || cargo.nome" :value="cargo.nome">{{ cargo.nome }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-sm-3 col-md-2">
                    <div class="form-group">
                        <label>Matrícula</label>
                        <input type="text"
                               placeholder="Matrícula"
                               autocomplete="off"
                               class="form-control form-control-sm"
                               :disabled="controle.carregando"
                               v-model="controle.dados.campoMatricula">
                    </div>
                </div>
                <div class="col-12 col-sm-3 col-md-2">
                    <div class="form-group">
                        <label>Função</label>
                        <input type="text"
                               placeholder="Função"
                               autocomplete="off"
                               class="form-control form-control-sm"
                               :disabled="controle.carregando"
                               v-model="controle.dados.campoFuncao">
                    </div>
                </div>
                <div class="col-12 col-sm-3 col-md-2" v-if="tiposAdmissao.length">
                    <div class="form-group">
                        <label>Tipo admissão</label>
                        <select class="form-control form-control-sm"
                                @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.campoTipoAdmissao">
                            <option value="">Todos</option>
                            <option v-for="t in tiposAdmissao" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-sm-2" v-if="permissoes.filtrar_demitido">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm"
                                @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.campoDemitido">
                            <option :value="false">Admitidos</option>
                            <option :value="true">Demitidos</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-sm-4 col-md-3" v-if="listaCnpjs && Object.keys(listaCnpjs).length > 0">
                    <div class="form-group">
                        <label>Lotação</label>
                        <select class="form-control form-control-sm"
                                @change="onLotacaoChange"
                                :disabled="controle.carregando"
                                v-model="controle.dados.campoCnpj">
                            <option value="">Todos</option>
                            <option v-for="(item, key) in listaCnpjs" :key="key" :value="key">
                                {{ item.nome_fantasia }} - {{ item.cnpj }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-sm-4 col-md-3" v-if="mostrarFiltroCentroCusto">
                    <div class="form-group">
                        <label>Centro de custo</label>
                        <select class="form-control form-control-sm"
                                @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.campoCentroCusto">
                            <option value="">Todos</option>
                            <option v-for="(item, key) in filtroListaCentroCustoCnpj" :key="(item.matriz ? item.id : item.filial_id) + '-' + key"
                                    :value="item.matriz ? item.id : item.filial_id"
                                    :title="item.label">
                                {{ item.label }}
                            </option>
                            <option value="--naoinformado--">— Não informado —</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label>Ordenar por</label>
                        <select class="form-control form-control-sm"
                                v-model="controle.dados.ordenacao"
                                :disabled="controle.carregando"
                                @change="atualizar">
                            <option value="created_at_desc">Mais recentes</option>
                            <option value="created_at_asc">Mais antigos</option>
                            <option value="updated_at_desc">Última atualização (mais recente)</option>
                            <option value="updated_at_asc">Última atualização (mais antiga)</option>
                            <option value="nome_asc">Nome (A–Z)</option>
                            <option value="nome_desc">Nome (Z–A)</option>
                            <option value="data_admissao_desc">Data admissão (mais recente)</option>
                            <option value="data_admissao_asc">Data admissão (mais antiga)</option>
                            <option value="cargo_asc">Cargo (A–Z)</option>
                            <option value="cargo_desc">Cargo (Z–A)</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label>Exibir</label>
                        <select class="form-control form-control-sm"
                                @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.pages">
                            <option v-for="n in porPagina" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="row mt-2">
                <div class="col-12">
                    <button type="button"
                            class="btn btn-sm btn-success mb-1"
                            :disabled="controle.carregando"
                            :style="controle.carregando ? 'cursor: not-allowed' : 'cursor: pointer'"
                            @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary mb-1 ml-1"
                            :disabled="controle.carregando"
                            @click="limparFiltros">
                        <i class="fa fa-eraser"></i>
                        Limpar filtros
                    </button>
                </div>
            </div>
        </fieldset>

        <div id="conteudo">
            <div class="empty-state" v-show="!controle.carregando && lista.length === 0">
                <div class="empty-state-icon"><i class="fas fa-history"></i></div>
                <h3 class="empty-state-title">Nenhum registro encontrado</h3>
                <p class="empty-state-text">Ajuste os filtros ou aguarde registros no histórico de admissão.</p>
            </div>

            <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="solicitacao-card" v-for="item in lista" :key="item.id"
                     :class="{
                         'card-status-admitido': item.admissao && (item.admissao.status === 'ADMITIDO' || item.admissao.status === 'Admitido'),
                         'card-status-demitido': item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido')
                     }">
                    <div class="card-header-row">
                        <div class="card-left">
                            <span class="badge-id">#{{ item.id }}</span>
                            <div class="colaborador-principal">
                                <i class="fas fa-user-circle mr-1"></i>
                                <strong>{{ item.curriculo.nome }}</strong>
                            </div>
                            <span class="status-badge"
                                  :class="{
                                      'status-admitido': item.admissao && (item.admissao.status === 'ADMITIDO' || item.admissao.status === 'Admitido'),
                                      'status-demitido': item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido')
                                  }">
                                {{ item.admissao ? item.admissao.status : '—' }}
                            </span>
                        </div>
                        <div class="card-right">
                            <div class="dropdown show">
                                <a class="btn-actions-compact" href="#" role="button"
                                   :id="'dropdownHistorico_' + item.id"
                                   data-toggle="dropdown"
                                   aria-haspopup="true"
                                   aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                     :aria-labelledby="'dropdownHistorico_' + item.id">
                                    <a class="dropdown-item" href="javascript://" title="Abrir histórico"
                                       @click.prevent="abrirHistorico(item)"
                                       data-toggle="modal"
                                       data-target="#janelaHistorico">
                                        <i class="fas fa-edit mr-2 text-primary"></i> Abrir histórico
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-details-row card-details-main">
                        <div class="detail-item">
                            <i class="fas fa-briefcase"></i>
                            <span class="detail-label">Cargo</span>
                            <span class="detail-value" :class="{ 'detail-value-empty': !item.admissao || !item.admissao.cargo }">
                                {{ (item.admissao && item.admissao.cargo) || 'Não informado' }}
                            </span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && item.admissao.funcao">
                            <i class="fas fa-user-tag"></i>
                            <span class="detail-label">Função</span>
                            <span class="detail-value">{{ item.admissao.funcao }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-id-card"></i>
                            <span class="detail-label">CPF</span>
                            <span class="detail-value" :class="{ 'detail-value-empty': !item.curriculo || !item.curriculo.cpf }">
                                {{ (item.curriculo && item.curriculo.cpf) || 'Não informado' }}
                            </span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && item.admissao.matricula">
                            <i class="fas fa-hashtag"></i>
                            <span class="detail-label">Matrícula</span>
                            <span class="detail-value">{{ item.admissao.matricula }}</span>
                        </div>
                        <div class="detail-item" v-if="item.cliente">
                            <i class="fas fa-building"></i>
                            <span class="detail-label">Empresa</span>
                            <span class="detail-value">{{ item.cliente.nome_fantasia || item.cliente.nome || '—' }}</span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && item.admissao.emp_centro_custo">
                            <i class="fas fa-sitemap"></i>
                            <span class="detail-label">Centro de custo</span>
                            <span class="detail-value">{{ item.admissao.emp_centro_custo }}</span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && (item.admissao.emp_nome_fantasia || item.admissao.emp_tipo || item.admissao.filial !== undefined)">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="detail-label">Lotação</span>
                            <span class="detail-value">
                                {{ nomeCompletoLotacao(item.admissao) }}
                            </span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && item.admissao.tipo_admissao">
                            <i class="fas fa-file-contract"></i>
                            <span class="detail-label">Tipo admissão</span>
                            <span class="detail-value">{{ item.admissao.tipo_admissao }}</span>
                        </div>
                    </div>
                    <div class="card-details-row card-details-fixas">
                        <div class="detail-item" v-if="item.admissao && item.admissao.data_admissao">
                            <i class="fas fa-calendar-check"></i>
                            <span class="detail-label">Data admissão</span>
                            <span class="detail-value">{{ item.admissao.data_admissao }}</span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido') && item.admissao.data_encerramento">
                            <i class="fas fa-calendar-times"></i>
                            <span class="detail-label">Data desligamento</span>
                            <span class="detail-value">{{ item.admissao.data_encerramento }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span class="detail-label">Última atualização</span>
                            <span class="detail-value" :class="{ 'detail-value-empty': !item.ultima_atualizacao }">
                                {{ item.ultima_atualizacao || '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <controle-paginacao class="d-flex justify-content-center"
                               id="controle"
                               ref="componente"
                               :url="urlAtualizar"
                               :por-pagina="controle.dados.pages"
                               :dados="controle.dados"
                               @carregou="carregou"
                               @carregando="carregando"></controle-paginacao>
        </div>
    </div>
</template>

<script>
import Dossie from './Dossie';
import MedidasAdministrativas from '../MedidasAdministrativas.vue';
import FormularioNoventaDias from './FormularioNoventaDias';
import AvaliacaoAnual from './AvaliacaoAnual';
import Ferias from './Ferias';
import Afastamento from './Afastamento';
import Beneficio from './Beneficio';
import Cih from './CIH';
import Promocao from './Promocao';
import Metas from './Meta';
import FeedbackHistorico from './FeedbackHistorico';
import LogsHistorico from './LogsHistorico';

export default {
    name: 'Historico',

    components: {
        Dossie,
        MedidasAdministrativas,
        FeedbackHistorico,
        FormularioNoventaDias,
        AvaliacaoAnual,
        Ferias,
        Afastamento,
        Beneficio,
        Cih,
        Promocao,
        Metas,
        LogsHistorico
    },

    props: {
        urlAtualizar: {
            type: String,
            required: true
        },
        urlGerarLinkAvaliacaoExperiencia: {
            type: String,
            default: ''
        }
    },

    data() {
        const abasDefault = {
            abrirDossie: false,
            abrirMedidas: false,
            abrirFeedbackHistorico: false,
            abrirFormularioNoventa: false,
            abrirAvaliacaoAnual: false,
            abrirFerias: false,
            abrirAfastamento: false,
            abrirBeneficio: false,
            abrirCih: false,
            abrirPromocao: false,
            abrirMetas: false,
            abrirLogHistorico: false
        };
        return {
            tituloJanela: 'Histórico',
            openHistorico: true,
            abas: { ...abasDefault, abrirDossie: true },
            abasDefault,
            permissoes: {},
            form: {
                feedback_id: 0,
                curriculo_id: 0,
                curriculo: { nome: '', cpf: '' },
                admissao: { cargo: '', funcao: '', data_admissao: '', status: '' },
                fc_token: null
            },
            lista: [],
            cargos: [],
            listaCentrosCusto: [],
            listaCnpjs: {},
            listaCentrosPorCnpj: {},
            tiposAdmissao: [],
            porPagina: [10, 20, 50, 100],
            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: 'autocomplete/todas-vagas-ativas',
                    pages: 20,
                    campoBusca: '',
                    campoCPF: '',
                    campoCargo: '',
                    campoMatricula: '',
                    campoFuncao: '',
                    campoTipoAdmissao: '',
                    campoDemitido: false,
                    campoCnpj: '',
                    campoCentroCusto: '',
                    ordenacao: 'created_at_desc'
                }
            },
            initialPage: null
        };
    },

    computed: {
        /** Lista de centros de custo para o select. Quando há Lotação selecionada, retorna só os centros daquela lotação; senão, todos (empresa só matriz) ou todos flatten (várias lotações). */
        filtroListaCentroCustoCnpj() {
            if (this.listaCentrosCusto && this.listaCentrosCusto.length > 0) {
                return this.listaCentrosCusto;
            }
            if (!this.listaCentrosPorCnpj || typeof this.listaCentrosPorCnpj !== 'object') {
                return [];
            }
            const cnpj = this.controle.dados.campoCnpj;
            if (cnpj && this.listaCentrosPorCnpj[cnpj] && this.listaCentrosPorCnpj[cnpj].length > 0) {
                return this.listaCentrosPorCnpj[cnpj];
            }
            const todas = [];
            Object.values(this.listaCentrosPorCnpj).forEach(arr => {
                if (Array.isArray(arr)) todas.push(...arr);
            });
            return todas;
        },
        /** Exibe o filtro Centro de custo quando existir lista de centros (matriz ou por lotação). */
        mostrarFiltroCentroCusto() {
            if (this.listaCentrosCusto && this.listaCentrosCusto.length > 0) return true;
            if (this.listaCentrosPorCnpj && Object.keys(this.listaCentrosPorCnpj).length > 0) return true;
            return false;
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
        this.urlParamGet();
        this.abasDefault = _.cloneDeep(this.abas);
        if (this.initialPage != null && this.initialPage >= 1) {
            this.$nextTick(() => {
                if (this.$refs.componente) {
                    this.$refs.componente.atual = this.initialPage;
                    this.$refs.componente.buscar();
                }
            });
        } else {
            this.atualizar();
        }
    },

    methods: {
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('pages')) this.controle.dados.pages = parseInt(urlParams.get('pages'), 10) || 20;
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca');
            if (urlParams.get('campoCPF')) this.controle.dados.campoCPF = urlParams.get('campoCPF');
            if (urlParams.get('campoCargo')) this.controle.dados.campoCargo = urlParams.get('campoCargo');
            if (urlParams.get('campoMatricula')) this.controle.dados.campoMatricula = urlParams.get('campoMatricula');
            if (urlParams.get('campoFuncao')) this.controle.dados.campoFuncao = urlParams.get('campoFuncao');
            if (urlParams.get('campoTipoAdmissao')) this.controle.dados.campoTipoAdmissao = urlParams.get('campoTipoAdmissao');
            if (urlParams.get('campoDemitido') !== null && urlParams.get('campoDemitido') !== '') {
                this.controle.dados.campoDemitido = urlParams.get('campoDemitido') === 'true';
            }
            if (urlParams.get('campoCnpj')) this.controle.dados.campoCnpj = urlParams.get('campoCnpj');
            if (urlParams.get('campoCentroCusto')) this.controle.dados.campoCentroCusto = urlParams.get('campoCentroCusto');
            if (urlParams.get('ordenacao')) this.controle.dados.ordenacao = urlParams.get('ordenacao');
            if (urlParams.get('page')) {
                const p = parseInt(urlParams.get('page'), 10);
                if (p >= 1) this.initialPage = p;
            }
        },

        syncUrlFiltros() {
            const d = this.controle.dados;
            const atual = (this.$refs.componente && this.$refs.componente.atual) ? this.$refs.componente.atual : 1;
            const params = {};
            if (d.pages && d.pages !== 20) params.pages = d.pages;
            if (d.ordenacao && d.ordenacao !== 'created_at_desc') params.ordenacao = d.ordenacao;
            if (d.campoBusca) params.campoBusca = d.campoBusca;
            if (d.campoCPF) params.campoCPF = d.campoCPF;
            if (d.campoCargo) params.campoCargo = d.campoCargo;
            if (d.campoMatricula) params.campoMatricula = d.campoMatricula;
            if (d.campoFuncao) params.campoFuncao = d.campoFuncao;
            if (d.campoTipoAdmissao) params.campoTipoAdmissao = d.campoTipoAdmissao;
            if (d.campoDemitido === true) params.campoDemitido = 'true';
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj;
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto;
            if (atual > 1) params.page = atual;
            const qs = new URLSearchParams(params).toString();
            const url = qs ? `${window.location.pathname}?${qs}` : window.location.pathname;
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, '', url);
            }
        },

        nomeCompletoLotacao(admissao) {
            if (!admissao) return '—';
            const tipo = admissao.emp_tipo || (admissao.filial ? 'Filial' : 'Matriz');
            const nome = admissao.emp_nome_fantasia || admissao.emp_razao_social;
            if (nome) return nome + ' (' + tipo + ')';
            return tipo;
        },

        onLotacaoChange() {
            this.controle.dados.campoCentroCusto = '';
            this.atualizar();
        },

        limparFiltros() {
            this.controle.dados.campoBusca = '';
            this.controle.dados.campoCPF = '';
            this.controle.dados.campoCargo = '';
            this.controle.dados.campoMatricula = '';
            this.controle.dados.campoFuncao = '';
            this.controle.dados.campoTipoAdmissao = '';
            this.controle.dados.campoDemitido = false;
            this.controle.dados.campoCnpj = '';
            this.controle.dados.campoCentroCusto = '';
            this.controle.dados.pages = 20;
            this.controle.dados.ordenacao = 'created_at_desc';
            this.syncUrlFiltros();
            if (this.$refs.componente) {
                this.$refs.componente.atual = 1;
                this.$refs.componente.buscar();
            }
        },

        trocaAba(aba) {
            this.abas = _.cloneDeep(this.abasDefault);
            this.abas[aba] = true;
        },

        abrirHistorico(obj) {
            this.openHistorico = true;
            this.tituloJanela = `#${obj.id} - Histórico: ${obj.curriculo.nome}`;
            this.form = _.cloneDeep(obj);
            this.form.feedback_id = obj.id;
            this.form.curriculo_id = obj.curriculo_id;

            setTimeout(() => {
                this.trocaAba('abrirDossie');
                this.openHistorico = false;
            }, 100);
            this.$nextTick(() => {
                if (typeof $ !== 'undefined' && $('#nav-dossie-tab').length) {
                    $('#nav-dossie-tab').tab('show');
                }
            });
        },

        carregou(dados) {
            this.lista = dados.itens || [];
            this.cargos = dados.cargos || [];
            this.permissoes = dados.permissoes || {};
            this.listaCentrosCusto = dados.lista_centros_custo || [];
            this.listaCnpjs = dados.lista_cnpjs || {};
            this.listaCentrosPorCnpj = dados.lista_centros_por_cnpj || {};
            this.tiposAdmissao = dados.tipos_admissao || [];
            this.controle.carregando = false;
            this.$nextTick(() => this.syncUrlFiltros());
        },

        carregando() {
            this.controle.carregando = true;
        },

        buscar() {
            if (this.$refs.componente) {
                this.$refs.componente.buscar();
            }
        },

        atualizar() {
            this.syncUrlFiltros();
            if (this.$refs.componente) {
                this.$refs.componente.atual = 1;
                this.$refs.componente.buscar();
            }
        }
    }
};
</script>

<style scoped>
/* Empty state */
.historico-admissao .empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
    border-radius: 12px;
    border: 1px dashed #dee2e6;
}
.historico-admissao .empty-state-icon {
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
.historico-admissao .empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}
.historico-admissao .empty-state-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0;
}

/* Cards list */
.historico-admissao .cards-lista {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.historico-admissao .solicitacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 0;
    transition: all 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border-left: 4px solid #6c757d;
    overflow: hidden;
}
.historico-admissao .solicitacao-card.card-status-admitido { border-left-color: #28a745; }
.historico-admissao .solicitacao-card.card-status-demitido { border-left-color: #dc3545; }
.historico-admissao .solicitacao-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    border-color: #ced4da;
    transform: translateY(-1px);
}

.historico-admissao .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
    border-bottom: 1px solid #f1f3f5;
}
.historico-admissao .card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
    min-width: 0;
}
.historico-admissao .card-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}
.historico-admissao .badge-id {
    background: #174257;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.75rem;
    white-space: nowrap;
    flex-shrink: 0;
}
.historico-admissao .colaborador-principal {
    display: flex;
    align-items: center;
    font-size: 1rem;
    color: #212529;
    overflow: hidden;
    min-width: 0;
}
.historico-admissao .colaborador-principal i { color: #174257; flex-shrink: 0; }
.historico-admissao .colaborador-principal strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 600;
}
.historico-admissao .status-badge {
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
.historico-admissao .status-admitido { background: #28a745; color: white; }
.historico-admissao .status-demitido { background: #dc3545; color: white; }

.historico-admissao .btn-actions-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #dee2e6;
    color: #6c757d;
    transition: all 0.2s ease;
    text-decoration: none;
    flex-shrink: 0;
}
.historico-admissao .btn-actions-compact:hover {
    background: #174257;
    border-color: #174257;
    color: white;
    transform: rotate(90deg);
}

.historico-admissao .card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.5rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f3f5;
}
.historico-admissao .card-details-row:last-child { border-bottom: none; }
.historico-admissao .card-details-main { padding-top: 0.75rem; }
.historico-admissao .card-details-fixas {
    background: #fafbfc;
    padding: 0.75rem 1.25rem;
}
.historico-admissao .detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.813rem;
    min-width: 0;
}
.historico-admissao .detail-item i:first-child { flex-shrink: 0; font-size: 0.875rem; color: #6c757d; }
.historico-admissao .detail-label { font-weight: 500; color: #6c757d; white-space: nowrap; }
.historico-admissao .detail-value { color: #212529; font-weight: 400; }
.historico-admissao .detail-value-empty { color: #adb5bd; font-style: italic; }
.historico-admissao .detail-item-action { margin-left: auto; }

.historico-admissao .dropdown-menu-custom {
    min-width: 11rem;
    padding: 0.25rem 0;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border: 1px solid #e9ecef;
    border-radius: 8px;
}
.historico-admissao .dropdown-menu-custom .dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
}
.historico-admissao .dropdown-menu-custom .dropdown-item i { width: 1.25rem; text-align: center; }
.historico-admissao .dropdown-menu-custom .dropdown-item:hover { background-color: #f8f9fa; color: #174257; }

@media (max-width: 768px) {
    .historico-admissao .card-header-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
    }
    .historico-admissao .card-right { width: 100%; justify-content: flex-end; }
    .historico-admissao .card-details-row { flex-direction: column; gap: 0.5rem; }
    .historico-admissao .detail-item-action { margin-left: 0; width: 100%; }
    .historico-admissao .detail-item-action .btn { width: 100%; }
}
</style>
