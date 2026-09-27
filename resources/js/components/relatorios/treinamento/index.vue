<template>
    <div class="trn-vencimento">
        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="preload"
            @submit="buscarDados"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <date-range-filter
                    v-model:enabled="filtroPeriodo"
                    v-model:start-date="dataInicio"
                    v-model:end-date="dataFim"
                    :disabled="preload"
                    id-suffix="trn-vencimento"
                    label="Período de vencimento"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="onPeriodoChange"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="trn-filtro-colaborador">Colaborador</label>
                        <input
                            id="trn-filtro-colaborador"
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Buscar por nome ou cargo"
                            autocomplete="off"
                            :disabled="preload"
                            v-model="filtroColaborador"
                            @input="onFiltroLocalInput"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && AUTENTICADO.temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="trn-filtro-cnpj">Por CNPJ</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                input-id="trn-filtro-cnpj"
                                instance-id="trn-filtro-cnpj"
                                :disabled="preload"
                                :options="opcoesCnpj"
                                placeholder-blur="Todos os CNPJs"
                                empty-message="Nenhum CNPJ encontrado."
                                :max-results="50"
                                v-model="campoCnpj"
                                @opening="fecharOutrosComboboxes('trn-filtro-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="trn-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                input-id="trn-filtro-cc"
                                instance-id="trn-filtro-cc"
                                :disabled="preload || !opcoesCentroCusto.length"
                                :options="opcoesCentroCusto"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro encontrado."
                                :max-results="80"
                                v-model="campoCentroCusto"
                                @opening="fecharOutrosComboboxes('trn-filtro-cc')"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="trn-filtro-segmento">Segmento</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroSegmento"
                                input-id="trn-filtro-segmento"
                                instance-id="trn-filtro-segmento"
                                :disabled="preload"
                                :options="opcoesSegmento"
                                placeholder-blur="Todos os segmentos"
                                empty-message="Nenhum segmento encontrado."
                                :max-results="30"
                                v-model="segmentoTreinamentoId"
                                @opening="fecharOutrosComboboxes('trn-filtro-segmento')"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="trn-filtro-treinamento">
                            Treinamento
                            <span class="trn-filtro-hint" v-if="filtroTreinamentos.length">
                                {{ filtroTreinamentos.length }} selecionado{{ filtroTreinamentos.length === 1 ? '' : 's' }}
                            </span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroTreinamento"
                                input-id="trn-filtro-treinamento"
                                instance-id="trn-filtro-treinamento"
                                :disabled="preload || !opcoesTreinamentoDisponiveis.length"
                                :options="opcoesTreinamentoDisponiveis"
                                placeholder-blur="Adicionar treinamento"
                                placeholder-focus="Digite para filtrar…"
                                empty-message="Nenhum treinamento disponível."
                                :max-results="80"
                                v-model="comboTreinamento"
                                @opening="fecharOutrosComboboxes('trn-filtro-treinamento')"
                                @select="onSelectTreinamento"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12" v-if="filtroTreinamentos.length">
                    <div class="trn-treino-badges" role="list" aria-label="Treinamentos selecionados">
                        <span
                            v-for="label in filtroTreinamentos"
                            :key="label"
                            class="trn-treino-badge"
                            role="listitem"
                        >
                            <span class="trn-treino-badge__text">{{ label }}</span>
                            <button
                                type="button"
                                class="trn-treino-badge__remove"
                                :aria-label="`Remover ${label}`"
                                :title="`Remover ${label}`"
                                @click="removerTreinamentoFiltro(label)"
                            >
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </template>

            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="preload">
                    <i :class="preload ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                    Buscar
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-success"
                    :disabled="preload || preloadExportacao || !dados.length"
                    @click.prevent="exportaExcel()"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                <span class="trn-filtro-alerta text-muted small">
                    <i class="fas fa-bell"></i> Alerta: até {{ diasAlerta }} dias
                </span>
            </template>
        </FiltroListagem>

        <preload v-if="preload" />

        <div v-if="!preload">
            <div class="row trn-kpis mt-3" v-if="dados.length">
                <div class="col-6 col-md-3 mb-2">
                    <button
                        type="button"
                        class="trn-kpi trn-kpi--btn"
                        :class="{ 'trn-kpi--active': filtroSituacao === 'todos' }"
                        @click="setFiltroSituacao('todos')"
                    >
                        <div class="trn-kpi__label">Total</div>
                        <div class="trn-kpi__value">{{ resumo.total }}</div>
                    </button>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <button
                        type="button"
                        class="trn-kpi trn-kpi--btn trn-kpi--danger"
                        :class="{ 'trn-kpi--active': filtroSituacao === 'vencidos' }"
                        @click="setFiltroSituacao('vencidos')"
                    >
                        <div class="trn-kpi__label">Vencidos</div>
                        <div class="trn-kpi__value">{{ resumo.vencidos }}</div>
                    </button>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <button
                        type="button"
                        class="trn-kpi trn-kpi--btn trn-kpi--warn"
                        :class="{ 'trn-kpi--active': filtroSituacao === 'avencer' }"
                        @click="setFiltroSituacao('avencer')"
                    >
                        <div class="trn-kpi__label">A vencer (≤ {{ diasAlerta }} dias)</div>
                        <div class="trn-kpi__value">{{ resumo.aVencer }}</div>
                    </button>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <button
                        type="button"
                        class="trn-kpi trn-kpi--btn trn-kpi--ok"
                        :class="{ 'trn-kpi--active': filtroSituacao === 'emdia' }"
                        @click="setFiltroSituacao('emdia')"
                    >
                        <div class="trn-kpi__label">Em dia</div>
                        <div class="trn-kpi__value">{{ resumo.emDia }}</div>
                    </button>
                </div>
            </div>

            <div class="trn-tabs-bar mt-3" role="tablist" aria-label="Visualização do relatório" v-if="dados.length">
                <button
                    type="button"
                    class="trn-tab"
                    role="tab"
                    :aria-selected="abaAtiva === 'lista'"
                    :class="{ 'trn-tab--active': abaAtiva === 'lista' }"
                    @click="abaAtiva = 'lista'"
                >
                    <span class="trn-tab__icon"><i class="fas fa-list"></i></span>
                    <span class="trn-tab__text">
                        <span class="trn-tab__title">Lista</span>
                        <span class="trn-tab__desc">Detalhes por colaborador</span>
                    </span>
                    <span class="trn-tab__count" v-if="linhasBase.length">{{ linhasFiltradas.length }}</span>
                </button>
                <button
                    type="button"
                    class="trn-tab"
                    role="tab"
                    :aria-selected="abaAtiva === 'graficos'"
                    :class="{ 'trn-tab--active': abaAtiva === 'graficos' }"
                    @click="abaAtiva = 'graficos'"
                >
                    <span class="trn-tab__icon"><i class="fas fa-chart-pie"></i></span>
                    <span class="trn-tab__text">
                        <span class="trn-tab__title">Gráficos</span>
                        <span class="trn-tab__desc">Visão por situação e lotação</span>
                    </span>
                </button>
            </div>

            <div class="trn-tab-panel">
                <div class="alert alert-warning mt-3 mb-0" v-show="!dados.length">
                    <i class="fa fa-exclamation-triangle"></i> Nenhum registro encontrado
                </div>
                <div class="alert alert-info mt-3 mb-0" v-show="dados.length && !linhasBase.length">
                    <i class="fa fa-info-circle"></i> Nenhum registro corresponde aos filtros de colaborador/treinamento
                </div>

                <div v-show="abaAtiva === 'lista' && linhasBase.length" class="trn-lista mt-3">
                    <div class="trn-lista-toolbar d-flex flex-wrap align-items-center justify-content-between mb-2">
                        <div class="trn-legenda">
                            <span class="trn-legenda__item">
                                <span class="trn-legenda__swatch trn-legenda__swatch--vencido"></span>
                                Vencido
                            </span>
                            <span class="trn-legenda__item">
                                <span class="trn-legenda__swatch trn-legenda__swatch--alerta"></span>
                                A vencer (≤ {{ diasAlerta }} dias)
                            </span>
                            <span class="trn-legenda__item">
                                <span class="trn-legenda__swatch trn-legenda__swatch--ok"></span>
                                Em dia
                            </span>
                            <span class="trn-legenda__sep">·</span>
                            <span class="trn-legenda__count">
                                <template v-if="filtroSituacao === 'todos'">
                                    {{ resumo.total }} treinamento{{ resumo.total === 1 ? '' : 's' }}
                                    · {{ gruposFiltrados.length }} colaborador{{ gruposFiltrados.length === 1 ? '' : 'es' }}
                                </template>
                                <template v-else>
                                    Exibindo <strong>{{ totalTreinamentosFiltrados }}</strong>
                                    de {{ resumo.total }}
                                    ({{ labelFiltroSituacao }})
                                    · {{ gruposFiltrados.length }} colaborador{{ gruposFiltrados.length === 1 ? '' : 'es' }}
                                </template>
                            </span>
                        </div>
                        <div class="d-flex align-items-center">
                            <label class="mb-0 mr-2 small text-muted" for="trn-por-pagina">Colaboradores / página</label>
                            <select
                                id="trn-por-pagina"
                                class="form-control form-control-sm trn-page-size"
                                v-model.number="porPagina"
                                @change="paginaAtual = 1"
                            >
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                                <option :value="250">250</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive trn-table-wrap">
                        <table class="table table-sm table-hover trn-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 44px">#</th>
                                    <th class="trn-col-sticky trn-th-sort" @click="ordenarPor('nome')">
                                        Colaborador
                                        <i :class="iconeOrdenacao('nome')"></i>
                                    </th>
                                    <th class="trn-th-sort" @click="ordenarPor('emp_centro_custo')">
                                        Centro de Custo
                                        <i :class="iconeOrdenacao('emp_centro_custo')"></i>
                                    </th>
                                    <th class="trn-th-sort" @click="ordenarPor('segmento')">
                                        Segmento
                                        <i :class="iconeOrdenacao('segmento')"></i>
                                    </th>
                                    <th class="trn-th-sort" @click="ordenarPor('label')">
                                        Treinamento
                                        <i :class="iconeOrdenacao('label')"></i>
                                    </th>
                                    <th class="text-center text-nowrap trn-th-sort" @click="ordenarPor('data_treinamento')">
                                        Data treino
                                        <i :class="iconeOrdenacao('data_treinamento')"></i>
                                    </th>
                                    <th class="text-center text-nowrap trn-th-sort" @click="ordenarPor('data_vencimento')">
                                        Vencimento
                                        <i :class="iconeOrdenacao('data_vencimento')"></i>
                                    </th>
                                    <th class="text-center trn-th-sort" @click="ordenarPor('dias_vencer')">
                                        Situação
                                        <i :class="iconeOrdenacao('dias_vencer')"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(grupo, gIndex) in gruposPaginados" :key="grupo._pessoaKey">
                                    <tr
                                        v-for="(row, tIndex) in grupo.treinamentos"
                                        :key="row._key"
                                        :class="[linhaClasse(row), { 'trn-row--group-start': tIndex === 0 }]"
                                    >
                                        <td
                                            v-if="tIndex === 0"
                                            class="text-center text-muted align-middle"
                                            :rowspan="grupo.treinamentos.length"
                                        >
                                            {{ (paginaAtual - 1) * porPagina + gIndex + 1 }}
                                        </td>
                                        <td
                                            v-if="tIndex === 0"
                                            class="trn-col-sticky trn-col-pessoa align-middle"
                                            :rowspan="grupo.treinamentos.length"
                                        >
                                            <div class="trn-nome">{{ grupo.nome }}</div>
                                            <div class="trn-meta">{{ grupo.cargo || '—' }}</div>
                                            <div class="trn-meta" v-if="AUTENTICADO.temFilial && grupo.emp_nome_fantasia">
                                                {{ grupo.emp_nome_fantasia }}
                                                <span v-if="grupo.emp_tipo"> · {{ grupo.emp_tipo }}</span>
                                            </div>
                                            <div class="trn-meta trn-meta--count" v-if="grupo.treinamentos.length > 1">
                                                {{ grupo.treinamentos.length }} treinamentos
                                            </div>
                                        </td>
                                        <td
                                            v-if="tIndex === 0"
                                            class="align-middle"
                                            :rowspan="grupo.treinamentos.length"
                                        >
                                            {{ grupo.emp_centro_custo || '—' }}
                                        </td>
                                        <td
                                            v-if="tIndex === 0"
                                            class="align-middle"
                                            :rowspan="grupo.treinamentos.length"
                                        >
                                            {{ grupo.segmento || '—' }}
                                        </td>
                                        <td>
                                            <div class="trn-treino-label">{{ row.label }}</div>
                                            <div class="trn-meta" v-if="row.descricao">{{ row.descricao }}</div>
                                        </td>
                                        <td class="text-center text-nowrap">{{ formatarDataBr(row.data_treinamento) }}</td>
                                        <td class="text-center text-nowrap trn-venc-data">{{ formatarDataBr(row.data_vencimento) }}</td>
                                        <td class="text-center">
                                            <span class="trn-pill" :class="pillClasse(row)">{{ textoSituacao(row) }}</span>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="!gruposPaginados.length">
                                    <td colspan="8" class="text-center text-muted py-4">Nenhum registro neste filtro</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="trn-paginacao d-flex flex-wrap align-items-center justify-content-between mt-2"
                        v-if="totalPaginas > 1"
                    >
                        <small class="text-muted">Página {{ paginaAtual }} de {{ totalPaginas }}</small>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" :disabled="paginaAtual <= 1" @click="paginaAtual = 1">«</button>
                            <button type="button" class="btn btn-outline-secondary" :disabled="paginaAtual <= 1" @click="paginaAtual--">‹</button>
                            <button type="button" class="btn btn-outline-secondary" :disabled="paginaAtual >= totalPaginas" @click="paginaAtual++">›</button>
                            <button type="button" class="btn btn-outline-secondary" :disabled="paginaAtual >= totalPaginas" @click="paginaAtual = totalPaginas">»</button>
                        </div>
                    </div>
                </div>

                <div v-show="abaAtiva === 'graficos' && linhasBase.length" class="trn-graficos mt-3">
                    <div class="row">
                        <div class="col-12 col-lg-5 mb-3">
                            <div class="trn-chart-card">
                                <ChartsDoughnut
                                    title="Situação dos treinamentos"
                                    :labels="graficoSituacao.labels"
                                    :values="graficoSituacao.values"
                                    :colors="graficoSituacao.colors"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-lg-7 mb-3">
                            <div class="trn-chart-card">
                                <ChartsBar
                                    title="Por treinamento"
                                    :labels="graficoTipos.labels"
                                    :datasets="graficoTipos.datasets"
                                    :altura="300"
                                />
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="trn-chart-card">
                                <ChartsBar
                                    title="Top centros de custo (vencidos + a vencer + em dia)"
                                    horizontal
                                    :labels="graficoCentros.labels"
                                    :datasets="graficoCentros.datasets"
                                    :altura="Math.max(280, graficoCentros.labels.length * 32)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ExportacaoMixin from '../../../mixins/Exportacoes'
import ChartsDoughnut from '../../Charts/Doughnut.vue'
import ChartsBar from '../../Charts/Bar.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import DateRangeFilter from '../../DateRangeFilter.vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'

const DIAS_ALERTA = 30

export default {
    mixins: [ExportacaoMixin],
    components: { ChartsDoughnut, ChartsBar, ComboboxAutoComplete, DateRangeFilter, FiltroListagem },
    data() {
        return {
            AUTENTICADO,
            diasAlerta: DIAS_ALERTA,
            preload: true,
            dados: [],
            lista_ccs: null,
            filtroPeriodo: true,
            dataInicio: '',
            dataFim: '',
            periodo: '',
            campoCnpj: '',
            campoCentroCusto: '',
            segmentoTreinamentoId: '',
            segmentosTreinamento: [],
            filtroColaborador: '',
            filtroTreinamentos: [],
            comboTreinamento: '',
            filtroLocalTimer: null,
            abaAtiva: 'lista',
            filtroSituacao: 'todos',
            ordenacaoCampo: 'dias_vencer',
            ordenacaoDir: 'asc',
            paginaAtual: 1,
            porPagina: 50,
            urlExportacao: `${URL_ADMIN}/relatorios/vencimento-treinamento/export-excel`
        }
    },
    async mounted() {
        this.aplicarPeriodoPadrao()
        await this.carregarSegmentos()
        await this.buscarDados()
    },
    beforeUnmount() {
        if (this.filtroLocalTimer) clearTimeout(this.filtroLocalTimer)
    },
    watch: {
        totalPaginas(val) {
            if (this.paginaAtual > val) this.paginaAtual = val
        }
    },
    computed: {
        paramsExport() {
            return {
                periodo: this.periodo,
                campoCnpj: this.campoCnpj,
                campoCentroCusto: this.campoCentroCusto,
                segmento_treinamento_id: this.segmentoTreinamentoId
            }
        },
        filtroListaCentroCustoCnpj() {
            if (!this.lista_ccs) return []
            if (this.campoCnpj !== '' && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.campoCnpj] || []
            }
            if (!this.AUTENTICADO.temFilial) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                return keys.length ? this.lista_ccs.centros_custos[keys[0]] || [] : []
            }
            const all = []
            Object.values(this.lista_ccs.centros_custos || {}).forEach((lista) => {
                ;(lista || []).forEach((item) => all.push(item))
            })
            return all
        },
        opcoesCnpj() {
            const opts = [{ value: '', label: 'Todos os CNPJs' }]
            if (!this.lista_ccs || !this.lista_ccs.cnpjs) return opts
            Object.keys(this.lista_ccs.cnpjs).forEach((key) => {
                const item = this.lista_ccs.cnpjs[key]
                opts.push({
                    value: key,
                    label: `${item.nome_fantasia} - ${item.cnpj}`,
                    meta: item.cnpj
                })
            })
            return opts
        },
        opcoesCentroCusto() {
            const opts = [
                { value: '', label: 'Todos os centros' },
                { value: '--naoinformado--', label: 'Não informado' }
            ]
            ;(this.filtroListaCentroCustoCnpj || []).forEach((item) => {
                const value = item.matriz ? item.id : item.filial_id
                opts.push({
                    value,
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        opcoesSegmento() {
            const opts = [{ value: '', label: 'Todos os segmentos' }]
            ;(this.segmentosTreinamento || []).forEach((s) => {
                opts.push({ value: String(s.id), label: s.nome })
            })
            return opts
        },
        temFiltrosAtivos() {
            return !!(
                this.campoCnpj ||
                this.campoCentroCusto ||
                this.segmentoTreinamentoId ||
                (this.filtroColaborador || '').trim() ||
                this.filtroTreinamentos.length
            )
        },
        opcoesTreinamento() {
            const set = new Set()
            this.linhas.forEach((row) => {
                if (row.label) set.add(row.label)
            })
            return Array.from(set).sort((a, b) => a.localeCompare(b, 'pt-BR'))
        },
        opcoesTreinamentoDisponiveis() {
            const selecionados = new Set(this.filtroTreinamentos)
            return this.opcoesTreinamento
                .filter((label) => !selecionados.has(label))
                .map((label) => ({ value: label, label }))
        },
        linhas() {
            const out = []
            ;(this.dados || []).forEach((pessoa, pi) => {
                const pessoaKey = `p-${pi}`
                ;(pessoa.treinamentos || []).forEach((tr, ti) => {
                    out.push({
                        _key: `${pessoaKey}-${tr.label || ti}-${tr.data_vencimento || ti}`,
                        _pessoaKey: pessoaKey,
                        nome: pessoa.nome,
                        cargo: pessoa.cargo,
                        emp_cnpj: pessoa.emp_cnpj,
                        emp_nome_fantasia: pessoa.emp_nome_fantasia,
                        emp_centro_custo: pessoa.emp_centro_custo,
                        emp_tipo: pessoa.emp_tipo,
                        segmento: pessoa.segmento,
                        tipo: pessoa.tipo,
                        label: tr.label,
                        descricao: tr.descricao,
                        data_treinamento: tr.data_treinamento,
                        data_vencimento: tr.data_vencimento,
                        dias_vencer: tr.dias_vencer,
                        pintar: tr.pintar
                    })
                })
            })
            return out
        },
        linhasBase() {
            const nomeBusca = (this.filtroColaborador || '').trim().toLowerCase()
            const treinos = this.filtroTreinamentos || []
            return this.linhas.filter((row) => {
                if (treinos.length && !treinos.includes(row.label)) return false
                if (nomeBusca) {
                    const nome = (row.nome || '').toLowerCase()
                    const cargo = (row.cargo || '').toLowerCase()
                    if (!nome.includes(nomeBusca) && !cargo.includes(nomeBusca)) return false
                }
                return true
            })
        },
        resumo() {
            let vencidos = 0
            let aVencer = 0
            let emDia = 0
            this.linhasBase.forEach((item) => {
                const cat = this.categoriaItem(item)
                if (cat === 'vencidos') vencidos++
                else if (cat === 'avencer') aVencer++
                else emDia++
            })
            return { total: this.linhasBase.length, vencidos, aVencer, emDia }
        },
        labelFiltroSituacao() {
            return { todos: 'Todos', vencidos: 'Vencidos', avencer: 'A vencer', emdia: 'Em dia' }[this.filtroSituacao] || 'Todos'
        },
        linhasFiltradas() {
            if (this.filtroSituacao === 'todos') return this.linhasBase
            return this.linhasBase.filter((item) => this.categoriaItem(item) === this.filtroSituacao)
        },
        totalTreinamentosFiltrados() {
            return this.linhasFiltradas.length
        },
        gruposFiltrados() {
            const mapa = new Map()
            this.linhasFiltradas.forEach((row) => {
                if (!mapa.has(row._pessoaKey)) {
                    mapa.set(row._pessoaKey, {
                        _pessoaKey: row._pessoaKey,
                        nome: row.nome,
                        cargo: row.cargo,
                        emp_cnpj: row.emp_cnpj,
                        emp_nome_fantasia: row.emp_nome_fantasia,
                        emp_centro_custo: row.emp_centro_custo,
                        emp_tipo: row.emp_tipo,
                        segmento: row.segmento,
                        tipo: row.tipo,
                        treinamentos: []
                    })
                }
                mapa.get(row._pessoaKey).treinamentos.push(row)
            })

            const grupos = Array.from(mapa.values())
            const campo = this.ordenacaoCampo
            const dir = this.ordenacaoDir === 'asc' ? 1 : -1

            grupos.forEach((grupo) => {
                grupo.treinamentos.sort((a, b) => this.compararCampo(a, b, campo, dir))
                const dias = grupo.treinamentos.map((t) => Number(t.dias_vencer))
                grupo._sortDias = Math.min(...dias)
                grupo._sortLabel = (grupo.treinamentos[0] && grupo.treinamentos[0].label) || ''
                grupo._sortDataTreino = grupo.treinamentos[0] ? grupo.treinamentos[0].data_treinamento : null
                grupo._sortDataVenc = grupo.treinamentos[0] ? grupo.treinamentos[0].data_vencimento : null
            })

            grupos.sort((a, b) => {
                if (campo === 'nome' || campo === 'emp_centro_custo' || campo === 'segmento') {
                    return this.compararValores(a[campo], b[campo], dir)
                }
                if (campo === 'dias_vencer') {
                    return (a._sortDias - b._sortDias) * dir
                }
                if (campo === 'label') {
                    return this.compararValores(a._sortLabel, b._sortLabel, dir)
                }
                if (campo === 'data_treinamento') {
                    return (this.dataParaSort(a._sortDataTreino) - this.dataParaSort(b._sortDataTreino)) * dir
                }
                if (campo === 'data_vencimento') {
                    return (this.dataParaSort(a._sortDataVenc) - this.dataParaSort(b._sortDataVenc)) * dir
                }
                return this.compararValores(a.nome, b.nome, 1)
            })

            return grupos
        },
        totalPaginas() {
            return Math.max(1, Math.ceil(this.gruposFiltrados.length / this.porPagina))
        },
        gruposPaginados() {
            const inicio = (this.paginaAtual - 1) * this.porPagina
            return this.gruposFiltrados.slice(inicio, inicio + this.porPagina)
        },
        graficoSituacao() {
            return {
                labels: ['Vencidos', 'A vencer', 'Em dia'],
                values: [this.resumo.vencidos, this.resumo.aVencer, this.resumo.emDia],
                colors: ['#c82333', '#e0a800', '#218838']
            }
        },
        graficoTipos() {
            const mapa = {}
            this.linhasBase.forEach((item) => {
                const key = item.label || '—'
                if (!mapa[key]) mapa[key] = { vencidos: 0, aVencer: 0, emDia: 0 }
                const cat = this.categoriaItem(item)
                if (cat === 'vencidos') mapa[key].vencidos++
                else if (cat === 'avencer') mapa[key].aVencer++
                else mapa[key].emDia++
            })
            const labels = Object.keys(mapa)
                .sort((a, b) => {
                    const ta = mapa[a].vencidos + mapa[a].aVencer + mapa[a].emDia
                    const tb = mapa[b].vencidos + mapa[b].aVencer + mapa[b].emDia
                    return tb - ta
                })
                .slice(0, 12)
            return {
                labels,
                datasets: [
                    { label: 'Vencidos', data: labels.map((l) => mapa[l].vencidos), backgroundColor: '#c82333' },
                    { label: 'A vencer', data: labels.map((l) => mapa[l].aVencer), backgroundColor: '#e0a800' },
                    { label: 'Em dia', data: labels.map((l) => mapa[l].emDia), backgroundColor: '#218838' }
                ]
            }
        },
        graficoCentros() {
            const mapa = {}
            this.linhasBase.forEach((item) => {
                const key = item.emp_centro_custo || 'Não informado'
                if (!mapa[key]) mapa[key] = { vencidos: 0, aVencer: 0, emDia: 0 }
                const cat = this.categoriaItem(item)
                if (cat === 'vencidos') mapa[key].vencidos++
                else if (cat === 'avencer') mapa[key].aVencer++
                else mapa[key].emDia++
            })
            const ordenado = Object.keys(mapa)
                .map((label) => ({
                    label,
                    total: mapa[label].vencidos + mapa[label].aVencer + mapa[label].emDia,
                    ...mapa[label]
                }))
                .sort((a, b) => b.total - a.total)
                .slice(0, 12)
            return {
                labels: ordenado.map((i) => i.label),
                datasets: [
                    { label: 'Vencidos', data: ordenado.map((i) => i.vencidos), backgroundColor: '#c82333' },
                    { label: 'A vencer', data: ordenado.map((i) => i.aVencer), backgroundColor: '#e0a800' },
                    { label: 'Em dia', data: ordenado.map((i) => i.emDia), backgroundColor: '#218838' }
                ]
            }
        }
    },
    methods: {
        categoriaItem(item) {
            const dias = Number(item.dias_vencer)
            if (dias < 0) return 'vencidos'
            if (item.pintar || dias <= DIAS_ALERTA) return 'avencer'
            return 'emdia'
        },
        compararValores(va, vb, dir) {
            const a = (va ?? '').toString().toLowerCase()
            const b = (vb ?? '').toString().toLowerCase()
            if (a < b) return -1 * dir
            if (a > b) return 1 * dir
            return 0
        },
        compararCampo(a, b, campo, dir) {
            if (campo === 'dias_vencer') return (Number(a.dias_vencer) - Number(b.dias_vencer)) * dir
            if (campo === 'data_treinamento' || campo === 'data_vencimento') {
                return (this.dataParaSort(a[campo]) - this.dataParaSort(b[campo])) * dir
            }
            return this.compararValores(a[campo], b[campo], dir)
        },
        setFiltroSituacao(valor) {
            this.filtroSituacao = this.filtroSituacao === valor && valor !== 'todos' ? 'todos' : valor
            this.paginaAtual = 1
            this.abaAtiva = 'lista'
        },
        onFiltroLocalInput() {
            if (this.filtroLocalTimer) clearTimeout(this.filtroLocalTimer)
            this.filtroLocalTimer = setTimeout(() => {
                this.paginaAtual = 1
            }, 250)
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'trn-filtro-cnpj': 'comboFiltroCnpj',
                'trn-filtro-cc': 'comboFiltroCc',
                'trn-filtro-segmento': 'comboFiltroSegmento',
                'trn-filtro-treinamento': 'comboFiltroTreinamento'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        async onSelectCnpj() {
            this.campoCentroCusto = ''
            await this.buscarDados()
        },
        aplicarPeriodoPadrao() {
            this.filtroPeriodo = true
            this.dataInicio = moment().format('YYYY-MM-DD')
            this.dataFim = moment().add(DIAS_ALERTA, 'days').format('YYYY-MM-DD')
            this.syncPeriodo()
        },
        syncPeriodo() {
            if (this.filtroPeriodo && this.dataInicio && this.dataFim) {
                this.periodo = `${this.formatarDataFiltro(this.dataInicio)} até ${this.formatarDataFiltro(this.dataFim)}`
            } else {
                this.periodo = ''
            }
        },
        formatarDataFiltro(valor) {
            const raw = (valor || '').trim()
            if (!raw) return ''
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(raw)) return raw
            if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                const [y, m, d] = raw.split('-')
                return `${d}/${m}/${y}`
            }
            return raw
        },
        async onPeriodoChange() {
            this.syncPeriodo()
            await this.buscarDados()
        },
        onSelectTreinamento(opt) {
            const label = opt && opt.value != null ? String(opt.value) : ''
            if (!label) return
            if (!this.filtroTreinamentos.includes(label)) {
                this.filtroTreinamentos.push(label)
            }
            this.$nextTick(() => {
                this.comboTreinamento = ''
            })
            this.paginaAtual = 1
        },
        removerTreinamentoFiltro(label) {
            this.filtroTreinamentos = this.filtroTreinamentos.filter((item) => item !== label)
            this.paginaAtual = 1
        },
        async limparFiltros() {
            this.campoCnpj = ''
            this.campoCentroCusto = ''
            this.segmentoTreinamentoId = ''
            this.filtroColaborador = ''
            this.filtroTreinamentos = []
            this.comboTreinamento = ''
            this.filtroSituacao = 'todos'
            this.paginaAtual = 1
            this.aplicarPeriodoPadrao()
            await this.buscarDados()
        },
        ordenarPor(campo) {
            if (this.ordenacaoCampo === campo) {
                this.ordenacaoDir = this.ordenacaoDir === 'asc' ? 'desc' : 'asc'
            } else {
                this.ordenacaoCampo = campo
                this.ordenacaoDir = 'asc'
            }
            this.paginaAtual = 1
        },
        iconeOrdenacao(campo) {
            if (this.ordenacaoCampo !== campo) return 'fas fa-sort text-muted'
            return this.ordenacaoDir === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'
        },
        dataParaSort(valor) {
            if (!valor) return 0
            if (typeof valor === 'string' && valor.includes('/')) {
                const m = moment(valor, 'DD/MM/YYYY')
                return m.isValid() ? m.valueOf() : 0
            }
            const m = moment(valor)
            return m.isValid() ? m.valueOf() : 0
        },
        pillClasse(row) {
            const cat = this.categoriaItem(row)
            if (cat === 'vencidos') return 'trn-pill--vencido'
            if (cat === 'avencer') return Number(row.dias_vencer) === 0 ? 'trn-pill--hoje' : 'trn-pill--alerta'
            return 'trn-pill--ok'
        },
        linhaClasse(row) {
            const cat = this.categoriaItem(row)
            if (cat === 'vencidos') return 'trn-row--vencido'
            if (cat === 'avencer') return 'trn-row--alerta'
            return ''
        },
        async carregarSegmentos() {
            try {
                const res = await axios.get(`${URL_ADMIN}/cadastro/segmentostreinamento/habilitados-empresa`)
                this.segmentosTreinamento = res.data || []
            } catch (e) {
                this.segmentosTreinamento = []
            }
        },
        async buscarDados() {
            this.syncPeriodo()
            this.preload = true
            try {
                const res = await axios.post(`${URL_ADMIN}/relatorios/vencimento-treinamento`, {
                    periodo: this.periodo,
                    campoCnpj: this.campoCnpj,
                    campoCentroCusto: this.campoCentroCusto,
                    segmento_treinamento_id: this.segmentoTreinamentoId
                })
                this.dados = res.data.itens || []
                this.lista_ccs = res.data.cc
                this.paginaAtual = 1
                const disponiveis = new Set(this.opcoesTreinamento)
                this.filtroTreinamentos = this.filtroTreinamentos.filter((label) => disponiveis.has(label))
                this.comboTreinamento = ''
            } finally {
                this.preload = false
            }
        },
        formatarDataBr(data) {
            if (!data) return '—'
            if (typeof data === 'string' && data.includes('/')) return data
            const m = moment(data)
            return m.isValid() ? m.format('DD/MM/YYYY') : data
        },
        textoSituacao(tr) {
            const dias = Number(tr.dias_vencer)
            if (Number.isNaN(dias)) return '—'
            if (dias < 0) {
                const atraso = Math.abs(dias)
                return atraso === 1 ? 'Vencido há 1 dia' : `Vencido há ${atraso} dias`
            }
            if (dias === 0) return 'Vence hoje'
            return dias === 1 ? 'Vence em 1 dia' : `Vence em ${dias} dias`
        }
    }
}
</script>

<style scoped>
.trn-vencimento :deep(.mybp-filtros-form) {
    align-items: start;
    row-gap: 0;
}

.trn-vencimento :deep(.mybp-filtros-form > [class*='col-']) {
    margin-bottom: var(--mybp-fc-gap, 0.45rem);
}

.trn-filtro-hint {
    margin-left: 0.3rem;
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--primary, #174257);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.trn-filtro-alerta {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-left: 0.25rem;
    font-size: 0.75rem;
}

.trn-treino-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin: 0.1rem 0 0.15rem;
    padding: 0.55rem 0.65rem;
    background: #f8f9fa;
    border: 1px dashed #dee2e6;
    border-radius: 8px;
}

.trn-treino-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    max-width: 100%;
    padding: 0.28rem 0.4rem 0.28rem 0.65rem;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(23, 66, 87, 0.2);
    color: var(--primary, #174257);
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.2;
    box-shadow: 0 1px 2px rgba(23, 66, 87, 0.06);
}

.trn-treino-badge__text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 280px;
}

.trn-treino-badge__remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.15rem;
    height: 1.15rem;
    border: 0;
    border-radius: 50%;
    background: rgba(23, 66, 87, 0.12);
    color: var(--primary, #174257);
    cursor: pointer;
    padding: 0;
    line-height: 1;
    flex-shrink: 0;
}

.trn-treino-badge__remove:hover {
    background: var(--primary, #174257);
    color: #fff;
}

.trn-tabs-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.35rem;
    background: #f4f5f7;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
}

.trn-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    flex: 1 1 180px;
    min-width: 160px;
    max-width: 320px;
    border: 1px solid transparent;
    background: transparent;
    border-radius: 8px;
    padding: 0.65rem 0.85rem;
    text-align: left;
    color: #495057;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
}

.trn-tab:hover {
    background: #fff;
    border-color: #dde1e6;
}

.trn-tab:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(23, 66, 87, 0.25);
}

.trn-tab--active {
    background: #fff;
    border-color: var(--primary, #174257);
    box-shadow: 0 1px 3px rgba(23, 66, 87, 0.15);
    color: var(--primary, #174257);
}

.trn-tab__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.1rem;
    height: 2.1rem;
    border-radius: 8px;
    background: #e9ecef;
    color: #6c757d;
    flex-shrink: 0;
}

.trn-tab--active .trn-tab__icon {
    background: rgba(23, 66, 87, 0.12);
    color: var(--primary, #174257);
}

.trn-tab__text {
    display: flex;
    flex-direction: column;
    min-width: 0;
    line-height: 1.2;
}

.trn-tab__title {
    font-weight: 700;
    font-size: 0.92rem;
}

.trn-tab__desc {
    font-size: 0.72rem;
    color: #6c757d;
    margin-top: 0.12rem;
}

.trn-tab--active .trn-tab__desc {
    color: #4a6d7c;
}

.trn-tab__count {
    margin-left: auto;
    background: #e9ecef;
    color: #495057;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 999px;
    padding: 0.15rem 0.5rem;
    min-width: 1.75rem;
    text-align: center;
}

.trn-tab--active .trn-tab__count {
    background: var(--primary, #174257);
    color: #fff;
}

.trn-kpis .trn-kpi {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-left: 4px solid #6c757d;
    border-radius: 4px;
    padding: 0.75rem 1rem;
    height: 100%;
    width: 100%;
    text-align: left;
}

.trn-kpi--btn {
    cursor: pointer;
    transition: box-shadow 0.15s ease, border-color 0.15s ease;
}

.trn-kpi--btn:hover,
.trn-kpi--active {
    box-shadow: 0 0 0 2px rgba(23, 66, 87, 0.18);
    border-color: var(--primary, #174257);
}

.trn-kpi--danger {
    border-left-color: #c82333;
}

.trn-kpi--warn {
    border-left-color: #e0a800;
}

.trn-kpi--ok {
    border-left-color: #218838;
}

.trn-kpi__label {
    font-size: 0.78rem;
    color: #6c757d;
    margin-bottom: 0.15rem;
}

.trn-kpi__value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
    color: #212529;
}

.trn-legenda {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.55rem 0.9rem;
    font-size: 0.82rem;
    color: #495057;
}

.trn-legenda__item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.trn-legenda__swatch {
    width: 0.7rem;
    height: 0.7rem;
    border-radius: 3px;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.trn-legenda__swatch--vencido {
    background: #f8d7da;
    border-color: #f1b0b7;
}

.trn-legenda__swatch--alerta {
    background: #fff3cd;
    border-color: #ffe08a;
}

.trn-legenda__swatch--ok {
    background: #d4edda;
    border-color: #b1dfbb;
}

.trn-legenda__sep {
    color: #adb5bd;
}

.trn-legenda__count {
    color: #6c757d;
}

.trn-page-size {
    width: auto;
    min-width: 72px;
}

.trn-table-wrap {
    max-height: 68vh;
    overflow: auto;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.trn-table {
    border-collapse: separate;
    border-spacing: 0;
}

.trn-table thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    background: #f1f3f5;
    vertical-align: middle;
    white-space: nowrap;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding: 0.65rem 0.75rem;
}

.trn-th-sort {
    cursor: pointer;
    user-select: none;
}

.trn-th-sort:hover {
    color: var(--primary, #174257);
}

.trn-th-sort i {
    margin-left: 4px;
    font-size: 0.7rem;
}

.trn-table tbody td {
    vertical-align: middle;
    font-size: 0.86rem;
    padding: 0.55rem 0.75rem;
    border-top: 1px solid #eceff1;
    background: #fff;
}

.trn-col-sticky {
    position: sticky;
    left: 0;
    z-index: 1;
    min-width: 200px;
    max-width: 260px;
    background: #fff;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.04);
}

.trn-table thead .trn-col-sticky {
    z-index: 4;
    background: #f1f3f5;
}

.trn-nome {
    font-weight: 700;
    line-height: 1.25;
}

.trn-meta {
    font-size: 0.75rem;
    color: #6c757d;
    line-height: 1.3;
}

.trn-meta--count {
    margin-top: 0.25rem;
    font-weight: 600;
    color: var(--primary, #174257);
}

.trn-row--group-start td {
    border-top: 2px solid #ced4da;
}

.trn-table tbody tr:first-child td {
    border-top: none;
}

.trn-treino-label {
    font-weight: 600;
}

.trn-venc-data {
    font-weight: 700;
}

.trn-pill {
    display: inline-block;
    padding: 0.28rem 0.55rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    white-space: nowrap;
}

.trn-pill--vencido {
    background: #f8d7da;
    color: #721c24;
}

.trn-pill--alerta {
    background: #fff3cd;
    color: #856404;
}

.trn-pill--hoje {
    background: #d1ecf1;
    color: #0c5460;
}

.trn-pill--ok {
    background: #d4edda;
    color: #155724;
}

.trn-row--vencido td,
.trn-row--vencido .trn-col-sticky {
    background-color: #fff5f5 !important;
}

.trn-row--alerta td,
.trn-row--alerta .trn-col-sticky {
    background-color: #fffbeb !important;
}

.trn-chart-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    padding: 1rem;
    height: 100%;
}

.trn-paginacao .btn {
    min-width: 36px;
}

@media (max-width: 767.98px) {
    .trn-col-sticky {
        position: static;
        box-shadow: none;
        max-width: none;
    }
}
</style>
