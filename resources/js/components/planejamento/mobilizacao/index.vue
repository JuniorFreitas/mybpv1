<template>
    <div class="container-fluid mobilizacao-page">
        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
            :desabilitado="preload"
            @submit="abriRelatorio"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <date-range-filter
                    v-model:enabled="filtroPeriodo"
                    v-model:start-date="dataInicio"
                    v-model:end-date="dataFim"
                    :disabled="preload"
                    id-suffix="mobilizacao"
                    label="Período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="onPeriodoChange"
                />

                <div class="col-12 col-md-8">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="mobilizacao-filtro-projeto">Projeto</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroProjeto"
                                instance-id="mobilizacao-projeto"
                                input-id="mobilizacao-filtro-projeto"
                                v-model="projeto_id"
                                :options="opcoesProjetos"
                                :disabled="preload"
                                placeholder-blur="Selecione um projeto"
                                empty-message="Nenhum projeto encontrado."
                                :max-results="100"
                                @select="onSelectProjeto"
                            />
                        </div>
                    </div>
                </div>
            </template>

            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="preload || !projeto_id">
                    <i :class="preload ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                    Buscar
                </button>
                <a
                    class="btn btn-sm btn-outline-danger"
                    target="_blank"
                    :href="`mobilizacao/pdf/${projeto_id}`"
                    v-if="temVagasProjeto"
                >
                    <i class="fas fa-file-pdf"></i> Gerar PDF
                </a>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    v-if="temVagasProjeto"
                    @click.prevent="exportaExcel()"
                    :disabled="preloadExportacao"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </template>
        </FiltroListagem>

        <preload class="text-center" v-if="preload"></preload>

        <div class="alert alert-warning mt-2" v-show="!preload && !projeto_id">
            <i class="fa fa-exclamation-triangle"></i> Selecione um projeto
        </div>

        <div id="conteudo" v-if="!preload && showRelatorio">
            <div class="mybp-card mybp-card--projeto mb-3" v-if="dados.projeto">
                <div class="mybp-card-header-row">
                    <div class="mybp-card-left">
                        <span class="mybp-badge-id">#{{ dados.projeto.id }}</span>
                        <div class="mybp-card-titulo">
                            <strong>{{ dados.projeto.nome }}</strong>
                            <small class="text-muted" v-if="filtroPeriodo && labelPeriodo">
                                Período: {{ labelPeriodo }}
                            </small>
                        </div>
                    </div>
                    <div class="mybp-card-right">
                        <mybp-status-badge
                            :variante="variantePreenchimento(dados.projeto.preenchidas, dados.projeto.qnt_total)"
                            :texto="textoPreenchimento(dados.projeto.preenchidas, dados.projeto.qnt_total)"
                        />
                    </div>
                </div>

                <div class="mybp-projeto-card-progresso">
                    <div class="mybp-projeto-card-progresso-topo">
                        <span class="mybp-projeto-card-progresso-label">Preenchimento do projeto</span>
                        <span class="mybp-projeto-card-progresso-valor">
                            {{ dados.projeto.preenchidas || 0 }} / {{ dados.projeto.qnt_total || 0 }}
                            ({{ pct(dados.projeto.preenchidas, dados.projeto.qnt_total) }}%)
                        </span>
                    </div>
                    <div class="progress mobilizacao-progress">
                        <div
                            class="progress-bar"
                            :class="classeBarra(dados.projeto.preenchidas, dados.projeto.qnt_total)"
                            role="progressbar"
                            :style="{ width: pct(dados.projeto.preenchidas, dados.projeto.qnt_total) + '%' }"
                            :aria-valuenow="pct(dados.projeto.preenchidas, dados.projeto.qnt_total)"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>
                    <div class="mybp-projeto-card-progresso-rodape">
                        <span>{{ agregados.admitidos }} admitido(s)</span>
                        <span>{{ agregados.standby }} standby</span>
                        <span>{{ agregados.emProcesso }} em processo</span>
                        <span>{{ agregados.cancelados + agregados.desistencias }} saídas</span>
                    </div>
                </div>

                <div class="mobilizacao-kpi-row">
                    <div class="mobilizacao-kpi" v-for="kpi in kpisProjeto" :key="kpi.label">
                        <span class="mobilizacao-kpi__valor">{{ kpi.valor }}</span>
                        <span class="mobilizacao-kpi__label">{{ kpi.label }}</span>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning" v-if="!temVagasProjeto">
                <i class="fa fa-exclamation-triangle"></i> Nenhum registro encontrado!
            </div>

            <template v-if="temVagasProjeto">
                <div class="aso-tabs-bar" role="tablist" aria-label="Visualização da mobilização">
                    <button
                        type="button"
                        class="aso-tab"
                        role="tab"
                        :aria-selected="abaAtiva === 'lista'"
                        :class="{ 'aso-tab--active': abaAtiva === 'lista' }"
                        @click="abaAtiva = 'lista'"
                    >
                        <span class="aso-tab__icon"><i class="fas fa-th-large"></i></span>
                        <span class="aso-tab__text">
                            <span class="aso-tab__title">Lista</span>
                            <span class="aso-tab__desc">Cards por vaga</span>
                        </span>
                        <span class="aso-tab__count">{{ dados.vagas_projeto.length }}</span>
                    </button>
                    <button
                        type="button"
                        class="aso-tab"
                        role="tab"
                        :aria-selected="abaAtiva === 'graficos'"
                        :class="{ 'aso-tab--active': abaAtiva === 'graficos' }"
                        @click="abaAtiva = 'graficos'"
                    >
                        <span class="aso-tab__icon"><i class="fas fa-chart-pie"></i></span>
                        <span class="aso-tab__text">
                            <span class="aso-tab__title">Gráficos</span>
                            <span class="aso-tab__desc">Pipeline e preenchimento</span>
                        </span>
                    </button>
                </div>

                <div class="aso-tab-panel">
                    <div class="mybp-cards-lista" v-show="abaAtiva === 'lista'">
                        <div class="mybp-card" v-for="item in dados.vagas_projeto" :key="item.id">
                            <div class="mybp-card-header-row">
                                <div class="mybp-card-left">
                                    <span class="mybp-badge-id">#{{ item.id }}</span>
                                    <div class="mybp-card-titulo">
                                        <strong>{{ tituloVaga(item) }}</strong>
                                        <small class="text-muted" v-if="cargoVaga(item)">{{ cargoVaga(item) }}</small>
                                    </div>
                                </div>
                                <div class="mybp-card-right">
                                    <mybp-status-badge
                                        :variante="variantePreenchimento(item.qnt_preenchida, item.qnt_total)"
                                        :texto="textoPreenchimento(item.qnt_preenchida, item.qnt_total)"
                                    />
                                </div>
                            </div>

                            <div class="mybp-card-corpo" :class="classeBordaVaga(item)">
                                <section class="mybp-card-secao mobilizacao-vaga-resumo">
                                    <div class="mybp-projeto-card-progresso mobilizacao-vaga-progresso">
                                        <div class="mybp-projeto-card-progresso-topo">
                                            <span class="mybp-projeto-card-progresso-label">Preenchimento</span>
                                            <span class="mybp-projeto-card-progresso-valor">
                                                {{ item.qnt_preenchida || 0 }}/{{ item.qnt_total || 0 }}
                                                ({{ pct(item.qnt_preenchida, item.qnt_total) }}%)
                                            </span>
                                        </div>
                                        <div class="progress mobilizacao-progress">
                                            <div
                                                class="progress-bar"
                                                :class="classeBarra(item.qnt_preenchida, item.qnt_total)"
                                                role="progressbar"
                                                :style="{ width: pct(item.qnt_preenchida, item.qnt_total) + '%' }"
                                            ></div>
                                        </div>
                                    </div>

                                    <div class="mobilizacao-metricas">
                                        <div
                                            class="mobilizacao-metrica"
                                            v-for="metrica in metricasResumoVaga(item)"
                                            :key="metrica.label"
                                            :class="{ 'mobilizacao-metrica--ativa': metrica.valor > 0, ['mobilizacao-metrica--' + metrica.tom]: true }"
                                        >
                                            <span class="mobilizacao-metrica__valor">{{ metrica.valor }}</span>
                                            <span class="mobilizacao-metrica__label">{{ metrica.label }}</span>
                                        </div>
                                    </div>
                                </section>

                                <section class="mybp-card-secao">
                                    <div class="mybp-card-secao__titulo">
                                        <i class="fas fa-stream" aria-hidden="true"></i> Pipeline
                                        <span class="mobilizacao-secao-hint" v-if="totalPipelineVaga(item) === 0">sem movimento</span>
                                    </div>
                                    <div class="mobilizacao-chips">
                                        <span
                                            class="mobilizacao-chip"
                                            v-for="chip in chipsPipelineVaga(item)"
                                            :key="chip.label"
                                            :class="{ 'mobilizacao-chip--ativa': chip.valor > 0 }"
                                            :title="chip.label"
                                        >
                                            <span class="mobilizacao-chip__label">{{ chip.curto }}</span>
                                            <strong class="mobilizacao-chip__valor">{{ chip.valor }}</strong>
                                        </span>
                                    </div>
                                </section>

                                <section class="mybp-card-secao mobilizacao-vaga-acoes">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link mobilizacao-toggle-detalhes"
                                        :aria-expanded="vagaExpandida(item.id) ? 'true' : 'false'"
                                        @click="toggleDetalhesVaga(item.id)"
                                    >
                                        <i :class="vagaExpandida(item.id) ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                                        {{ vagaExpandida(item.id) ? 'Ocultar detalhes' : 'Ver resultado e tipos de admissão' }}
                                    </button>
                                </section>

                                <div v-show="vagaExpandida(item.id)">
                                    <section class="mybp-card-secao">
                                        <div class="mybp-card-secao__titulo">
                                            <i class="fas fa-flag-checkered" aria-hidden="true"></i> Resultado
                                        </div>
                                        <div class="mobilizacao-chips">
                                            <span
                                                class="mobilizacao-chip"
                                                v-for="chip in chipsResultadoVaga(item)"
                                                :key="chip.label"
                                                :class="{ 'mobilizacao-chip--ativa': chip.valor > 0, ['mobilizacao-chip--' + chip.tom]: chip.valor > 0 }"
                                            >
                                                <span class="mobilizacao-chip__label">{{ chip.curto }}</span>
                                                <strong class="mobilizacao-chip__valor">{{ chip.valor }}</strong>
                                            </span>
                                        </div>
                                    </section>

                                    <section class="mybp-card-secao">
                                        <div class="mybp-card-secao__titulo">
                                            <i class="fas fa-file-contract" aria-hidden="true"></i> Tipos de admissão
                                        </div>
                                        <div class="mobilizacao-chips">
                                            <span
                                                class="mobilizacao-chip"
                                                v-for="chip in chipsTiposVaga(item)"
                                                :key="chip.label"
                                                :class="{ 'mobilizacao-chip--ativa': chip.valor > 0 }"
                                            >
                                                <span class="mobilizacao-chip__label">{{ chip.curto }}</span>
                                                <strong class="mobilizacao-chip__valor">{{ chip.valor }}</strong>
                                            </span>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>

                        <div class="mybp-card mt-3">
                            <div class="mybp-card-header-row">
                                <div class="mybp-card-left">
                                    <div class="mybp-card-titulo">
                                        <strong>Resumo geral da mobilização</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="mybp-card-corpo">
                                <section class="mybp-card-secao">
                                    <div class="mybp-card-row">
                                        <mybp-card-campo
                                            icon="fas fa-users"
                                            label="Currículos"
                                            :valor="n(dados.total_geral_curriculos)"
                                            forte
                                        />
                                        <mybp-card-campo
                                            icon="fas fa-user-friends"
                                            label="Mobilizados"
                                            :valor="n(dados.total_geral_curriculos_feedbacks)"
                                        />
                                        <mybp-card-campo
                                            icon="fas fa-user-check"
                                            label="Selecionados"
                                            :valor="n(dados.total_geral_curriculos_selecionados)"
                                        />
                                        <mybp-card-campo
                                            icon="fas fa-pause"
                                            label="StandBy"
                                            :valor="n(dados.total_geral_curriculos_standby)"
                                        />
                                    </div>
                                </section>
                                <section class="mybp-card-secao">
                                    <div class="mybp-card-row">
                                        <mybp-card-campo icon="fas fa-comments" label="Parecer RH" :valor="n(dados.total_em_parecer_rh)" />
                                        <mybp-card-campo icon="fas fa-route" label="Parecer rota" :valor="n(dados.total_em_parecer_rota)" />
                                        <mybp-card-campo
                                            icon="fas fa-user-tie"
                                            label="Entrevista técnica"
                                            :valor="n(dados.total_em_parecer_tecnica)"
                                        />
                                        <mybp-card-campo
                                            icon="fas fa-tasks"
                                            label="Teste prático"
                                            :valor="n(dados.total_em_parecer_teste)"
                                        />
                                        <mybp-card-campo
                                            icon="fas fa-layer-group"
                                            label="Resultado integrado"
                                            :valor="n(dados.total_em_resultado_integrado)"
                                        />
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>

                    <div class="mobilizacao-graficos mt-3" v-show="abaAtiva === 'graficos'">
                        <div class="row">
                            <div class="col-12 col-lg-4 mb-3">
                                <div class="aso-chart-card">
                                    <ChartsDoughnut
                                        title="Preenchimento do projeto"
                                        :labels="graficoPreenchimento.labels"
                                        :values="graficoPreenchimento.values"
                                        :colors="graficoPreenchimento.colors"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 mb-3">
                                <div class="aso-chart-card">
                                    <ChartsDoughnut
                                        title="Resultados (agregado)"
                                        :labels="graficoResultados.labels"
                                        :values="graficoResultados.values"
                                        :colors="graficoResultados.colors"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 mb-3">
                                <div class="aso-chart-card">
                                    <ChartsDoughnut
                                        title="Tipos de admissão"
                                        :labels="graficoTipos.labels"
                                        :values="graficoTipos.values"
                                        :colors="graficoTipos.colors"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 mb-3">
                                <div class="aso-chart-card">
                                    <ChartsBar
                                        title="Pipeline do projeto"
                                        :labels="graficoPipeline.labels"
                                        :datasets="graficoPipeline.datasets"
                                        :altura="320"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 mb-3">
                                <div class="aso-chart-card">
                                    <ChartsBar
                                        title="Preenchimento por vaga"
                                        horizontal
                                        :labels="graficoPorVaga.labels"
                                        :datasets="graficoPorVaga.datasets"
                                        :altura="Math.max(280, graficoPorVaga.labels.length * 36)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import DateRangeFilter from '../../DateRangeFilter.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'
import ChartsDoughnut from '../../Charts/Doughnut.vue'
import ChartsBar from '../../Charts/Bar.vue'

const CORES = {
    preenchidas: '#28a745',
    restantes: '#e9ecef',
    admitidos: '#174257',
    standby: '#ffc107',
    processo: '#17a2b8',
    cancelados: '#dc3545',
    desistencias: '#fd7e14',
    pipeline: '#653232',
    determinado: '#174257',
    fixo: '#28a745',
    intermitente: '#17a2b8',
    temporario: '#6c757d'
}

export default {
    name: 'Mobilizacao',
    components: {
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem,
        MybpCardCampo,
        MybpStatusBadge,
        ChartsDoughnut,
        ChartsBar
    },
    data() {
        return {
            preload: false,
            preloadExportacao: false,
            filtroPeriodo: false,
            dataInicio: '',
            dataFim: '',
            projeto_id: '',
            listProjetos: [],
            showRelatorio: false,
            abaAtiva: 'lista',
            detalhesVagaAbertos: {},
            dados: {
                projeto: null,
                vagas_projeto: []
            }
        }
    },
    computed: {
        urlBase() {
            return `${URL_ADMIN}/planejamento/mobilizacao`
        },
        opcoesProjetos() {
            const opts = [{ value: '', label: 'Selecione um projeto' }]
            ;(this.listProjetos || []).forEach((p) => {
                if (p && p.id != null) {
                    opts.push({ value: p.id, label: p.nome || p.text || `#${p.id}` })
                }
            })
            return opts
        },
        temVagasProjeto() {
            return !!(this.dados && this.dados.vagas_projeto && this.dados.vagas_projeto.length)
        },
        totalFiltrosAtivos() {
            let total = this.projeto_id ? 1 : 0
            if (this.filtroPeriodo && this.dataInicio && this.dataFim) total++
            return total
        },
        labelPeriodo() {
            if (!this.filtroPeriodo || !this.dataInicio || !this.dataFim) return ''
            return `${this.formatarDataBr(this.dataInicio)} até ${this.formatarDataBr(this.dataFim)}`
        },
        agregados() {
            const vagas = this.dados.vagas_projeto || []
            const sum = (key) => vagas.reduce((acc, item) => acc + (Number(item[key]) || 0), 0)
            return {
                admitidos: sum('status_admitido'),
                standby: sum('status_standby'),
                emProcesso: sum('em_processo_selecao'),
                cancelados: sum('status_cancelado'),
                desistencias: sum('status_desistencia'),
                pendenteTreinamento: sum('status_pendente_treinamento'),
                treinamentoFase1: sum('treinamento_fase_1'),
                exames: sum('status_encaminhado_exame'),
                pendenteAso: sum('status_pendente_aso'),
                asoAmbulatorio: sum('status_aso_no_ambulatorio'),
                pendenteDocs: sum('status_pendente_documento'),
                aguardandoQualificacao: sum('status_aguardando_qualificacao'),
                prontoAdmissao: sum('status_pronto_para_admissao'),
                entregueArea: sum('entregue_area'),
                portaria: sum('documento_portaria'),
                tipoDeterminado: sum('tipo_admissao_determinado'),
                tipoFixo: sum('tipo_admissao_fixo'),
                tipoIntermitente: sum('tipo_admissao_intermitente'),
                tipoTemporario: sum('tipo_admissao_temporario')
            }
        },
        kpisProjeto() {
            const a = this.agregados
            return [
                { label: 'Admitidos', valor: a.admitidos },
                { label: 'Em seleção', valor: a.emProcesso },
                { label: 'StandBy', valor: a.standby },
                { label: 'Pronto admissão', valor: a.prontoAdmissao },
                { label: 'Exames', valor: a.exames },
                { label: 'Cancel./Desist.', valor: a.cancelados + a.desistencias }
            ]
        },
        graficoPreenchimento() {
            const preenchidas = Number(this.dados.projeto && this.dados.projeto.preenchidas) || 0
            const total = Number(this.dados.projeto && this.dados.projeto.qnt_total) || 0
            const restantes = Math.max(total - preenchidas, 0)
            return {
                labels: ['Preenchidas', 'Restantes'],
                values: [preenchidas, restantes],
                colors: [CORES.preenchidas, CORES.restantes]
            }
        },
        graficoResultados() {
            const a = this.agregados
            return {
                labels: ['Admitidos', 'StandBy', 'Em seleção', 'Cancelados', 'Desistências'],
                values: [a.admitidos, a.standby, a.emProcesso, a.cancelados, a.desistencias],
                colors: [CORES.admitidos, CORES.standby, CORES.processo, CORES.cancelados, CORES.desistencias]
            }
        },
        graficoTipos() {
            const a = this.agregados
            return {
                labels: ['Determinada', 'Fixo', 'Intermitente', 'Temporária'],
                values: [a.tipoDeterminado, a.tipoFixo, a.tipoIntermitente, a.tipoTemporario],
                colors: [CORES.determinado, CORES.fixo, CORES.intermitente, CORES.temporario]
            }
        },
        graficoPipeline() {
            const a = this.agregados
            return {
                labels: [
                    'Pend. trein.',
                    'Trein. F1',
                    'Exames',
                    'Pend. ASO',
                    'ASO amb.',
                    'Pend. docs',
                    'Ag. qualif.',
                    'Pronto adm.'
                ],
                datasets: [
                    {
                        label: 'Quantidade',
                        data: [
                            a.pendenteTreinamento,
                            a.treinamentoFase1,
                            a.exames,
                            a.pendenteAso,
                            a.asoAmbulatorio,
                            a.pendenteDocs,
                            a.aguardandoQualificacao,
                            a.prontoAdmissao
                        ],
                        backgroundColor: CORES.pipeline
                    }
                ]
            }
        },
        graficoPorVaga() {
            const vagas = this.dados.vagas_projeto || []
            return {
                labels: vagas.map((item) => {
                    const titulo = this.tituloVaga(item)
                    return titulo.length > 28 ? `${titulo.slice(0, 28)}…` : titulo
                }),
                datasets: [
                    {
                        label: 'Preenchidas',
                        data: vagas.map((item) => Number(item.qnt_preenchida) || 0),
                        backgroundColor: CORES.preenchidas
                    },
                    {
                        label: 'Total',
                        data: vagas.map((item) => Number(item.qnt_total) || 0),
                        backgroundColor: '#adb5bd'
                    }
                ]
            }
        }
    },
    async mounted() {
        this.preload = true
        try {
            const response = await axios.get(`${this.urlBase}/get-projetos`)
            this.listProjetos = response.data || []
        } catch (error) {
            this.listProjetos = []
        } finally {
            this.preload = false
        }
    },
    methods: {
        n(valor) {
            return Number(valor) || 0
        },
        pct(parcial, total) {
            const p = Number(parcial) || 0
            const t = Number(total) || 0
            if (t <= 0) return 0
            return Math.min(100, Math.round((p / t) * 100))
        },
        formatarDataBr(iso) {
            const m = String(iso || '').match(/^(\d{4})-(\d{2})-(\d{2})/)
            if (!m) return iso || ''
            return `${m[3]}/${m[2]}/${m[1]}`
        },
        tituloVaga(item) {
            return (item && item.vaga_aberta && item.vaga_aberta.titulo) || 'Vaga'
        },
        cargoVaga(item) {
            return (item && item.vaga_aberta && item.vaga_aberta.vaga && item.vaga_aberta.vaga.nome) || ''
        },
        metricasResumoVaga(item) {
            return [
                { label: 'Admitidos', valor: this.n(item.status_admitido), tom: 'ok' },
                { label: 'Em seleção', valor: this.n(item.em_processo_selecao), tom: 'info' },
                { label: 'StandBy', valor: this.n(item.status_standby), tom: 'warn' },
                {
                    label: 'Saídas',
                    valor: this.n(item.status_cancelado) + this.n(item.status_desistencia),
                    tom: 'danger'
                }
            ]
        },
        chipsPipelineVaga(item) {
            return [
                { label: 'Pendente treinamento', curto: 'Pend. trein.', valor: this.n(item.status_pendente_treinamento) },
                { label: 'Treinamento fase 1', curto: 'Trein. F1', valor: this.n(item.treinamento_fase_1) },
                { label: 'Exames', curto: 'Exames', valor: this.n(item.status_encaminhado_exame) },
                { label: 'Pendente ASO', curto: 'Pend. ASO', valor: this.n(item.status_pendente_aso) },
                { label: 'ASO ambulatório', curto: 'ASO amb.', valor: this.n(item.status_aso_no_ambulatorio) },
                { label: 'Pendente docs', curto: 'Pend. docs', valor: this.n(item.status_pendente_documento) },
                { label: 'Ag. qualificação', curto: 'Ag. qualif.', valor: this.n(item.status_aguardando_qualificacao) },
                { label: 'Pronto admissão', curto: 'Pronto adm.', valor: this.n(item.status_pronto_para_admissao) }
            ]
        },
        chipsResultadoVaga(item) {
            return [
                { label: 'Entregues área', curto: 'Entregues', valor: this.n(item.entregue_area), tom: 'ok' },
                { label: 'StandBy', curto: 'StandBy', valor: this.n(item.status_standby), tom: 'warn' },
                { label: 'Portaria', curto: 'Portaria', valor: this.n(item.documento_portaria), tom: 'info' },
                { label: 'Cancelados', curto: 'Cancel.', valor: this.n(item.status_cancelado), tom: 'danger' },
                { label: 'Desistências', curto: 'Desist.', valor: this.n(item.status_desistencia), tom: 'danger' }
            ]
        },
        chipsTiposVaga(item) {
            return [
                { label: 'Determinada', curto: 'Determinada', valor: this.n(item.tipo_admissao_determinado) },
                { label: 'Fixo', curto: 'Fixo', valor: this.n(item.tipo_admissao_fixo) },
                { label: 'Intermitente', curto: 'Intermitente', valor: this.n(item.tipo_admissao_intermitente) },
                { label: 'Temporária', curto: 'Temporária', valor: this.n(item.tipo_admissao_temporario) }
            ]
        },
        totalPipelineVaga(item) {
            return this.chipsPipelineVaga(item).reduce((acc, chip) => acc + chip.valor, 0)
        },
        vagaExpandida(id) {
            return !!this.detalhesVagaAbertos[id]
        },
        toggleDetalhesVaga(id) {
            this.detalhesVagaAbertos = {
                ...this.detalhesVagaAbertos,
                [id]: !this.detalhesVagaAbertos[id]
            }
        },
        textoPreenchimento(preenchidas, total) {
            const p = Number(preenchidas) || 0
            const t = Number(total) || 0
            if (t <= 0) return 'Sem vagas'
            if (p >= t) return 'Completo'
            if (p <= 0) return 'Em aberto'
            return `${p}/${t}`
        },
        variantePreenchimento(preenchidas, total) {
            const p = Number(preenchidas) || 0
            const t = Number(total) || 0
            if (t <= 0) return 'neutro'
            if (p >= t) return 'rh'
            if (p <= 0) return 'aberto'
            return 'gestor'
        },
        classeBarra(preenchidas, total) {
            const p = this.pct(preenchidas, total)
            if (p >= 100) return 'bg-success'
            if (p >= 50) return 'bg-info'
            if (p > 0) return 'bg-warning'
            return 'bg-secondary'
        },
        classeBordaVaga(item) {
            const chave = this.variantePreenchimento(item.qnt_preenchida, item.qnt_total)
            return `mybp-card-corpo--${chave}`
        },
        onSelectProjeto() {
            this.abriRelatorio()
        },
        onPeriodoChange() {
            if (this.projeto_id) this.abriRelatorio()
        },
        limparFiltros() {
            this.projeto_id = ''
            this.filtroPeriodo = false
            this.dataInicio = ''
            this.dataFim = ''
            this.showRelatorio = false
            this.abaAtiva = 'lista'
            this.detalhesVagaAbertos = {}
            this.dados = { projeto: null, vagas_projeto: [] }
        },
        async abriRelatorio() {
            this.preload = true
            this.showRelatorio = false
            this.detalhesVagaAbertos = {}
            if (!this.projeto_id) {
                this.dados = { projeto: null, vagas_projeto: [] }
                this.preload = false
                return false
            }
            try {
                const { data } = await axios.get(`${this.urlBase}/seleciona-projeto/${this.projeto_id}`)
                this.dados = data || { projeto: null, vagas_projeto: [] }
                this.showRelatorio = true
                this.abaAtiva = 'lista'
            } catch (error) {
                this.dados = { projeto: null, vagas_projeto: [] }
            } finally {
                this.preload = false
            }
        },
        async exportaExcel() {
            this.preloadExportacao = true
            try {
                const { data } = await axios.post(`${this.urlBase}/export-excel`, {
                    projeto: this.projeto_id
                })
                if (typeof mostraSucesso === 'function') mostraSucesso(data.msg)
            } catch (erro) {
                if (typeof mostraErro === 'function') mostraErro(erro)
            } finally {
                this.preloadExportacao = false
            }
        }
    }
}
</script>

<style scoped>
.mobilizacao-progress {
    height: 0.55rem;
    border-radius: 999px;
    background: #eef1f4;
    overflow: hidden;
}

.mobilizacao-progress .progress-bar {
    border-radius: 999px;
}

.mobilizacao-vaga-resumo {
    padding-bottom: 0.85rem;
}

.mobilizacao-vaga-progresso {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
    margin-bottom: 0.75rem;
}

.mobilizacao-metricas {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.45rem;
}

.mobilizacao-metrica {
    background: #f8fafb;
    border: 1px solid #eef1f4;
    border-radius: 8px;
    padding: 0.5rem 0.55rem;
    text-align: center;
    min-width: 0;
    opacity: 0.72;
}

.mobilizacao-metrica--ativa {
    opacity: 1;
}

.mobilizacao-metrica__valor {
    display: block;
    font-size: 1.15rem;
    font-weight: 700;
    color: #174257;
    line-height: 1.15;
}

.mobilizacao-metrica__label {
    display: block;
    margin-top: 0.12rem;
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mobilizacao-metrica--ok.mobilizacao-metrica--ativa {
    background: #f1f8f3;
    border-color: #cfe8d6;
}

.mobilizacao-metrica--ok.mobilizacao-metrica--ativa .mobilizacao-metrica__valor {
    color: #1e7e34;
}

.mobilizacao-metrica--info.mobilizacao-metrica--ativa {
    background: #eef7fa;
    border-color: #cde5ee;
}

.mobilizacao-metrica--info.mobilizacao-metrica--ativa .mobilizacao-metrica__valor {
    color: #0c5460;
}

.mobilizacao-metrica--warn.mobilizacao-metrica--ativa {
    background: #fff8e8;
    border-color: #f0e0b2;
}

.mobilizacao-metrica--warn.mobilizacao-metrica--ativa .mobilizacao-metrica__valor {
    color: #856404;
}

.mobilizacao-metrica--danger.mobilizacao-metrica--ativa {
    background: #fdf2f3;
    border-color: #f0d0d4;
}

.mobilizacao-metrica--danger.mobilizacao-metrica--ativa .mobilizacao-metrica__valor {
    color: #a71d2a;
}

.mobilizacao-secao-hint {
    margin-left: 0.35rem;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: none;
    letter-spacing: 0;
    color: #98a2ab;
}

.mobilizacao-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.mobilizacao-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    max-width: 100%;
    padding: 0.28rem 0.5rem;
    border-radius: 999px;
    border: 1px solid #e6eaee;
    background: #f7f9fb;
    color: #8a949c;
    font-size: 0.72rem;
    line-height: 1.2;
}

.mobilizacao-chip--ativa {
    background: #f0f6f9;
    border-color: #c9dbe5;
    color: #174257;
}

.mobilizacao-chip__label {
    white-space: nowrap;
}

.mobilizacao-chip__valor {
    font-size: 0.78rem;
    font-weight: 700;
}

.mobilizacao-chip--ok {
    background: #f1f8f3;
    border-color: #cfe8d6;
    color: #1e7e34;
}

.mobilizacao-chip--warn {
    background: #fff8e8;
    border-color: #f0e0b2;
    color: #856404;
}

.mobilizacao-chip--info {
    background: #eef7fa;
    border-color: #cde5ee;
    color: #0c5460;
}

.mobilizacao-chip--danger {
    background: #fdf2f3;
    border-color: #f0d0d4;
    color: #a71d2a;
}

.mobilizacao-vaga-acoes {
    padding-top: 0.45rem;
    padding-bottom: 0.45rem;
}

.mobilizacao-toggle-detalhes {
    padding: 0;
    font-size: 0.78rem;
    font-weight: 600;
    color: #174257;
    text-decoration: none;
}

.mobilizacao-toggle-detalhes:hover,
.mobilizacao-toggle-detalhes:focus {
    color: #0f2f3d;
    text-decoration: none;
}

.mobilizacao-toggle-detalhes i {
    margin-right: 0.3rem;
    font-size: 0.7rem;
}

.mobilizacao-kpi-row {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0.5rem;
    margin-top: 0.875rem;
}

.mobilizacao-kpi {
    background: #f8fafb;
    border: 1px solid #eef1f4;
    border-radius: 8px;
    padding: 0.55rem 0.65rem;
    text-align: center;
    min-width: 0;
}

.mobilizacao-kpi__valor {
    display: block;
    font-size: 1.05rem;
    font-weight: 700;
    color: #174257;
    line-height: 1.2;
}

.mobilizacao-kpi__label {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.aso-tabs-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 0.75rem 0 0.25rem;
}

.aso-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    border: 1px solid #dee2e6;
    background: #fff;
    border-radius: 8px;
    padding: 0.55rem 0.85rem;
    color: #495057;
    cursor: pointer;
    transition: all 0.15s ease;
}

.aso-tab:hover {
    border-color: #174257;
    color: #174257;
}

.aso-tab--active {
    border-color: #174257;
    background: #f0f6f9;
    color: #174257;
    box-shadow: 0 1px 2px rgba(23, 66, 87, 0.08);
}

.aso-tab__icon {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eef1f4;
    color: #174257;
}

.aso-tab--active .aso-tab__icon {
    background: #174257;
    color: #fff;
}

.aso-tab__text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.15;
}

.aso-tab__title {
    font-size: 0.85rem;
    font-weight: 700;
}

.aso-tab__desc {
    font-size: 0.7rem;
    color: #6c757d;
}

.aso-tab--active .aso-tab__desc {
    color: #4d6b7a;
}

.aso-tab__count {
    margin-left: 0.25rem;
    min-width: 1.5rem;
    padding: 0.1rem 0.4rem;
    border-radius: 999px;
    background: #eef1f4;
    font-size: 0.72rem;
    font-weight: 700;
}

.aso-tab--active .aso-tab__count {
    background: #174257;
    color: #fff;
}

.aso-chart-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 1rem;
    height: 100%;
}

@media (max-width: 991.98px) {
    .mobilizacao-kpi-row {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .mobilizacao-metricas {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 575.98px) {
    .mobilizacao-kpi-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
