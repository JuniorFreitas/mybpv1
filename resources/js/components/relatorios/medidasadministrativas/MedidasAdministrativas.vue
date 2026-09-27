<template>
    <div class="relatorio-medidas-adm">
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
                    id-suffix="medidas-adm"
                    label="Por período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="onPeriodoChange"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="medidas-busca">Colaborador</label>
                        <input
                            id="medidas-busca"
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Buscar por nome ou código"
                            autocomplete="off"
                            :disabled="preload"
                            v-model="campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && AUTENTICADO.temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="medidas-cnpj">CNPJ</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                input-id="medidas-cnpj"
                                instance-id="medidas-cnpj"
                                :disabled="preload"
                                :options="opcoesCnpj"
                                placeholder-blur="Todos os CNPJs"
                                empty-message="Nenhum CNPJ encontrado."
                                :max-results="50"
                                v-model="campoCnpj"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="medidas-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                input-id="medidas-status"
                                instance-id="medidas-status"
                                :disabled="preload"
                                :options="opcoesStatus"
                                placeholder-blur="Sem filtro"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                v-model="status"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="medidas-tipo">Tipo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroTipo"
                                input-id="medidas-tipo"
                                instance-id="medidas-tipo"
                                :disabled="preload"
                                :options="opcoesTipo"
                                placeholder-blur="Todos os tipos"
                                empty-message="Nenhum tipo encontrado."
                                :max-results="20"
                                v-model="campoTipo"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="medidas-causa">Causa</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCausa"
                                input-id="medidas-causa"
                                instance-id="medidas-causa"
                                :disabled="preload"
                                :options="opcoesCausa"
                                placeholder-blur="Todas as causas"
                                empty-message="Nenhuma causa encontrada."
                                :max-results="30"
                                v-model="campoCausa"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12" v-if="lista_ccs">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="medidas-cc">
                            Centros de custo
                            <span v-if="centrosSelecionados.length" class="ma-filtro-hint">
                                {{ centrosSelecionados.length }}
                            </span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                input-id="medidas-cc"
                                instance-id="medidas-cc"
                                :disabled="preload || !opcoesCentroCustoDisponiveis.length"
                                :options="opcoesCentroCustoDisponiveis"
                                placeholder-blur="Adicionar centro de custo"
                                placeholder-focus="Digite para filtrar…"
                                empty-message="Nenhum centro disponível."
                                :max-results="80"
                                v-model="comboCentro"
                                @select="onSelectCentro"
                            />
                        </div>
                    </div>
                    <div
                        class="ma-filtro-badges"
                        v-if="centrosSelecionados.length"
                        role="list"
                        aria-label="Centros de custo selecionados"
                    >
                        <span
                            v-for="(item, ind) in centrosSelecionados"
                            :key="`${item.value}-${ind}`"
                            class="ma-filtro-badge"
                            role="listitem"
                        >
                            <span class="ma-filtro-badge__text">{{ item.label }}</span>
                            <button
                                type="button"
                                class="ma-filtro-badge__remove"
                                :aria-label="`Remover ${item.label}`"
                                :disabled="preload"
                                @click="removerCentro(ind)"
                            >
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                        </span>
                        <button
                            type="button"
                            class="btn btn-sm btn-link px-1 py-0"
                            :disabled="preload"
                            @click="limparCentrosSelecionados"
                        >
                            Limpar
                        </button>
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
                    :disabled="preload || !dados.length"
                    @click.prevent="exportaExcel()"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </template>
        </FiltroListagem>

        <preload v-if="preload" class="text-center" />

        <div v-show="!preload">
            <div class="alert alert-info text-center mt-3 mb-0" v-if="dados.length">
                <strong>TOTAL DE MEDIDAS: {{ totalGeral }}</strong>
            </div>

            <div class="aso-tabs-bar mt-3" role="tablist" aria-label="Visualização do relatório" v-if="dados.length">
                <button
                    type="button"
                    class="aso-tab"
                    role="tab"
                    :aria-selected="abaAtiva === 'lista'"
                    :class="{ 'aso-tab--active': abaAtiva === 'lista' }"
                    @click="abaAtiva = 'lista'"
                >
                    <span class="aso-tab__icon"><i class="fas fa-list"></i></span>
                    <span class="aso-tab__text">
                        <span class="aso-tab__title">Lista</span>
                        <span class="aso-tab__desc">Leitura por caso</span>
                    </span>
                    <span class="aso-tab__count">{{ dados.length }}</span>
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
                        <span class="aso-tab__desc">Tipo, causa e centros</span>
                    </span>
                </button>
            </div>

            <div class="aso-tab-panel">
                <div
                    class="alert alert-warning text-center mt-3 mb-0"
                    v-show="!dados.length"
                >
                    <i class="fa fa-exclamation-triangle"></i>
                    {{
                        temFiltrosAtivos
                            ? 'Nenhum registro encontrado com os filtros atuais'
                            : 'Nenhum Registro Encontrado'
                    }}
                </div>

                <div v-show="abaAtiva === 'lista' && dados.length" class="ma-lista mt-3">
                    <article
                        class="ma-item"
                        v-for="(medida, index) in dados"
                        :key="medida.id || medida.feedback_id || index"
                        :class="classeSeveridade(medida.tipo)"
                    >
                        <header class="ma-item__topo">
                            <div class="ma-item__quando">
                                <span class="ma-item__id" v-if="medida.id">#{{ medida.id }}</span>
                                <span class="ma-item__data-label">Data da medida</span>
                                <time class="ma-item__data">{{ medida.data_solicitacao || '—' }}</time>
                                <span v-if="medida.data_retorno" class="ma-item__retorno">
                                    Retorno {{ medida.data_retorno }}
                                </span>
                            </div>
                            <span class="ma-item__tipo" :class="classeTipo(medida.tipo)">
                                {{ medida.tipo }}
                            </span>
                        </header>

                        <h3 class="ma-item__nome">{{ medida.nome }}</h3>
                        <p class="ma-item__contexto">
                            {{ medida.cargo || 'Cargo não informado' }}
                            <span class="ma-item__dot">·</span>
                            {{ medida.centro_custo || 'Centro não informado' }}
                        </p>
                        <p
                            class="ma-item__lotacao"
                            v-if="medida.emp_nome_fantasia || medida.emp_cnpj"
                        >
                            <span v-if="medida.emp_nome_fantasia">{{ medida.emp_nome_fantasia }}</span>
                            <span
                                class="ma-item__dot"
                                v-if="medida.emp_nome_fantasia && medida.emp_cnpj"
                            >·</span>
                            <span v-if="medida.emp_cnpj">{{ medida.emp_cnpj }}</span>
                        </p>

                        <p class="ma-item__causa">{{ medida.causa }}</p>

                        <div
                            class="ma-item__motivo"
                            v-if="medida.motivo && medida.motivo !== 'Não informado'"
                        >
                            <p
                                class="ma-item__motivo-texto"
                                :class="{ 'ma-item__motivo-texto--aberto': motivoAberto(medida) }"
                            >
                                {{ medida.motivo }}
                            </p>
                            <button
                                type="button"
                                class="ma-item__mais"
                                v-if="motivoLongo(medida)"
                                @click="alternarMotivo(medida)"
                            >
                                {{ motivoAberto(medida) ? 'Ver menos' : 'Ver motivo completo' }}
                            </button>
                        </div>

                        <footer class="ma-item__rodape">
                            Solicitante: {{ medida.solicitante || '—' }}
                        </footer>
                    </article>
                </div>

                <div v-show="abaAtiva === 'graficos' && dados.length" class="aso-graficos mt-3">
                    <div class="row">
                        <div class="col-12 col-lg-5 mb-3">
                            <div class="aso-chart-card">
                                <ChartsDoughnut
                                    title="Por tipo de medida"
                                    :labels="graficoTipos.labels"
                                    :values="graficoTipos.values"
                                    :colors="graficoTipos.colors"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-lg-7 mb-3">
                            <div class="aso-chart-card">
                                <ChartsBar
                                    title="Top causas"
                                    :labels="graficoCausas.labels"
                                    :datasets="graficoCausas.datasets"
                                    :altura="Math.max(280, graficoCausas.labels.length * 28)"
                                />
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="aso-chart-card">
                                <ChartsBar
                                    title="Por centro de custo"
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
import ChartsBar from '../../Charts/Bar.vue'
import ChartsDoughnut from '../../Charts/Doughnut.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import DateRangeFilter from '../../DateRangeFilter.vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import preload from '../../preload.vue'

const TIPOS = [
    'Re-orientação',
    'Advertência Verbal',
    'Advertência Escrita',
    'Suspensão de 1 dia',
    'Suspensão de 2 ou 3 dias',
    'Suspensão acima de 3 dias',
    'Desligamento'
]

const CAUSAS = [
    'Comportamentos Contrários aos Valores',
    'Inadequação no desempenho das funções',
    'Desrespeito às normas de SSMA',
    'Descumprimento dos procedimentos internos',
    'Insubordinação',
    'Desidia',
    'Sob efeito ou uso de drogas',
    'Negociação administrativa e comercial sem consentimento',
    'Violação de segredos estratégicos',
    'Abandono de emprego',
    'Abandono do local de serviço',
    'Aceitação de vantagens oferecidas por terceiros',
    'Outros comportamentos contrários ao contrato'
]

const CORES = [
    '#174257',
    '#2a6f8f',
    '#3d8ab0',
    '#28a745',
    '#e0a800',
    '#fd7e14',
    '#c82333',
    '#6c757d'
]

export default {
    name: 'MedidasAdministrativasRelatorio',
    components: {
        preload,
        ChartsBar,
        ChartsDoughnut,
        ComboboxAutoComplete,
        DateRangeFilter,
        FiltroListagem
    },
    mixins: [ExportacaoMixin],
    data() {
        return {
            AUTENTICADO,
            preload: false,
            abaAtiva: 'lista',
            dados: [],
            lista_ccs: null,
            graficos: { tipos: [], causas: [], centros: [] },
            totalGeral: 0,
            filtroPeriodo: true,
            dataInicio: '',
            dataFim: '',
            periodo: '',
            campoBusca: '',
            campoCnpj: '',
            status: '',
            campoTipo: '',
            campoCausa: '',
            comboCentro: '',
            centrosSelecionados: [],
            motivosAbertos: {},
            urlExportacao: `${URL_ADMIN}/relatorios/medidasadministrativas/export-excel`
        }
    },
    mounted() {
        this.filtroPeriodo = true
        this.dataInicio = moment().startOf('month').format('YYYY-MM-DD')
        this.dataFim = moment().endOf('month').format('YYYY-MM-DD')
        this.syncPeriodo()
        this.buscarDados()
    },
    computed: {
        paramsExport() {
            return this.payloadFiltros()
        },
        temFiltrosAtivos() {
            return !!(
                this.status ||
                this.campoTipo ||
                this.campoCausa ||
                this.campoCnpj ||
                (this.campoBusca || '').trim() ||
                this.centrosSelecionados.length ||
                (this.filtroPeriodo && this.dataInicio && this.dataFim)
            )
        },
        opcoesStatus() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: 'admitidos', label: 'Admitidos' },
                { value: 'demitidos', label: 'Demitidos' }
            ]
        },
        opcoesTipo() {
            const opts = [{ value: '', label: 'Todos os tipos' }]
            TIPOS.forEach((t) => opts.push({ value: t, label: t }))
            return opts
        },
        opcoesCausa() {
            const opts = [{ value: '', label: 'Todas as causas' }]
            CAUSAS.forEach((c) => opts.push({ value: c, label: c }))
            return opts
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
        opcoesCentroCustoDisponiveis() {
            const selecionados = new Set(
                (this.centrosSelecionados || []).map((item) => String(item.value))
            )
            const opts = []
            if (
                this.filtroListaCentroCustoCnpj.length &&
                selecionados.size < this.filtroListaCentroCustoCnpj.length
            ) {
                opts.push({ value: 'todos', label: 'Adicionar todos' })
            }
            ;(this.filtroListaCentroCustoCnpj || []).forEach((item) => {
                const value = String(item.id)
                if (selecionados.has(value)) return
                opts.push({
                    value,
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        graficoTipos() {
            const itens = this.graficos.tipos || []
            const labels = itens.map((i) => i.label)
            return {
                labels,
                values: itens.map((i) => Number(i.total) || 0),
                colors: labels.map((_, i) => CORES[i % CORES.length])
            }
        },
        graficoCausas() {
            const itens = this.graficos.causas || []
            return {
                labels: itens.map((i) => i.label),
                datasets: [
                    {
                        label: 'Medidas',
                        data: itens.map((i) => Number(i.total) || 0),
                        backgroundColor: '#174257'
                    }
                ]
            }
        },
        graficoCentros() {
            const itens = this.graficos.centros || []
            return {
                labels: itens.map((i) => i.label),
                datasets: [
                    {
                        label: 'Medidas',
                        data: itens.map((i) => Number(i.total) || 0),
                        backgroundColor: '#2a6f8f'
                    }
                ]
            }
        }
    },
    methods: {
        chaveMotivo(medida) {
            return String(medida.id || medida.feedback_id || medida.nome || '')
        },
        motivoLongo(medida) {
            return String(medida.motivo || '').trim().length > 160
        },
        motivoAberto(medida) {
            return !!this.motivosAbertos[this.chaveMotivo(medida)]
        },
        alternarMotivo(medida) {
            const chave = this.chaveMotivo(medida)
            this.motivosAbertos = {
                ...this.motivosAbertos,
                [chave]: !this.motivosAbertos[chave]
            }
        },
        classeSeveridade(tipo) {
            const t = String(tipo || '').toLowerCase()
            if (t.includes('desligamento')) return 'ma-item--critico'
            if (t.includes('suspensão') || t.includes('suspensao')) return 'ma-item--alto'
            if (t.includes('escrita')) return 'ma-item--medio'
            return 'ma-item--baixo'
        },
        classeTipo(tipo) {
            const t = String(tipo || '').toLowerCase()
            if (t.includes('desligamento')) return 'ma-item__tipo--critico'
            if (t.includes('suspensão') || t.includes('suspensao')) return 'ma-item__tipo--alto'
            if (t.includes('escrita')) return 'ma-item__tipo--medio'
            return 'ma-item__tipo--baixo'
        },
        payloadFiltros() {
            return {
                periodo: this.periodo,
                campoPeriodo: this.filtroPeriodo,
                dataInicio: this.dataInicio,
                dataFim: this.dataFim,
                campoBusca: this.campoBusca,
                campoCnpj: this.campoCnpj,
                status: this.status,
                campoTipo: this.campoTipo,
                campoCausa: this.campoCausa,
                campoCentrosCusto: (this.centrosSelecionados || []).map((i) => String(i.value))
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
        syncPeriodo() {
            if (this.filtroPeriodo && this.dataInicio && this.dataFim) {
                this.periodo = `${this.formatarDataFiltro(this.dataInicio)} até ${this.formatarDataFiltro(this.dataFim)}`
            } else {
                this.periodo = ''
            }
        },
        async onPeriodoChange() {
            this.syncPeriodo()
            await this.buscarDados()
        },
        onSelectCnpj() {
            this.centrosSelecionados = []
            this.comboCentro = ''
            this.buscarDados()
        },
        onSelectCentro(opt) {
            const valor = opt && opt.value != null ? String(opt.value) : ''
            if (!valor) {
                this.comboCentro = ''
                return
            }
            if (valor === 'todos') {
                const mapa = new Map(
                    (this.centrosSelecionados || []).map((item) => [String(item.value), item])
                )
                ;(this.filtroListaCentroCustoCnpj || []).forEach((item) => {
                    const id = String(item.id)
                    if (!mapa.has(id)) {
                        mapa.set(id, { value: id, label: item.label })
                    }
                })
                this.centrosSelecionados = Array.from(mapa.values())
            } else if (
                !(this.centrosSelecionados || []).some((item) => String(item.value) === valor)
            ) {
                this.centrosSelecionados.push({
                    value: valor,
                    label: (opt && opt.label) || valor
                })
            }
            this.$nextTick(() => {
                this.comboCentro = ''
            })
            this.buscarDados()
        },
        removerCentro(indice) {
            this.centrosSelecionados.splice(indice, 1)
            this.buscarDados()
        },
        limparCentrosSelecionados() {
            this.centrosSelecionados = []
            this.comboCentro = ''
            this.buscarDados()
        },
        async limparFiltros() {
            this.status = ''
            this.campoTipo = ''
            this.campoCausa = ''
            this.campoBusca = ''
            this.campoCnpj = ''
            this.centrosSelecionados = []
            this.comboCentro = ''
            this.abaAtiva = 'lista'
            this.filtroPeriodo = true
            this.dataInicio = moment().startOf('month').format('YYYY-MM-DD')
            this.dataFim = moment().endOf('month').format('YYYY-MM-DD')
            if (!this.AUTENTICADO.temFilial && this.lista_ccs && this.lista_ccs.cnpjs) {
                const keys = Object.keys(this.lista_ccs.cnpjs)
                this.campoCnpj = keys.length ? keys[0] : ''
            }
            this.syncPeriodo()
            await this.buscarDados()
        },
        async buscarDados() {
            this.syncPeriodo()
            this.preload = true
            try {
                const res = await axios.post(
                    `${URL_ADMIN}/relatorios/medidasadministrativas`,
                    this.payloadFiltros()
                )
                const payload = res.data || {}
                this.dados = payload.itens || (Array.isArray(payload) ? payload : [])
                this.motivosAbertos = {}
                this.lista_ccs = payload.cc || null
                this.graficos = {
                    tipos: (payload.graficos && payload.graficos.tipos) || [],
                    causas: (payload.graficos && payload.graficos.causas) || [],
                    centros: (payload.graficos && payload.graficos.centros) || []
                }
                this.totalGeral = Number(payload.total != null ? payload.total : this.dados.length)
                if (!this.AUTENTICADO.temFilial && this.lista_ccs && this.lista_ccs.cnpjs) {
                    const keys = Object.keys(this.lista_ccs.cnpjs)
                    if (keys.length && !this.campoCnpj) {
                        this.campoCnpj = keys[0]
                    }
                }
            } finally {
                this.preload = false
            }
        }
    }
}
</script>

<style scoped>
.relatorio-medidas-adm :deep(.mybp-filtros-form > [class*='col-']) {
    margin-bottom: var(--mybp-fc-gap, 0.45rem);
}

.ma-filtro-hint {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    margin-left: 0.35rem;
    padding: 0.05rem 0.35rem;
    border-radius: 999px;
    background: rgba(23, 66, 87, 0.12);
    color: var(--primary, #174257);
    font-size: 0.68rem;
    font-weight: 700;
}

.ma-filtro-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
    margin-top: 0.3rem;
    padding: 0.1rem 0;
}

.ma-filtro-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    max-width: 100%;
    padding: 0.1rem 0.25rem 0.1rem 0.4rem;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(23, 66, 87, 0.18);
    color: var(--primary, #174257);
    font-size: 0.68rem;
    font-weight: 600;
    line-height: 1.2;
}

.ma-filtro-badge__text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 220px;
}

.ma-filtro-badge__remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.1rem;
    height: 1.1rem;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: inherit;
    padding: 0;
    cursor: pointer;
}

.ma-filtro-badge__remove:hover {
    background: rgba(23, 66, 87, 0.12);
}

.aso-tabs-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.35rem;
    background: #f4f5f7;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
}

.aso-tab {
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

.aso-tab:hover {
    background: #fff;
    border-color: #dde1e6;
    color: #343a40;
}

.aso-tab:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(23, 66, 87, 0.25);
}

.aso-tab--active {
    background: #fff;
    border-color: var(--primary, #174257);
    box-shadow: 0 1px 3px rgba(23, 66, 87, 0.15);
    color: var(--primary, #174257);
}

.aso-tab__icon {
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

.aso-tab--active .aso-tab__icon {
    background: rgba(23, 66, 87, 0.12);
    color: var(--primary, #174257);
}

.aso-tab__text {
    display: flex;
    flex-direction: column;
    min-width: 0;
    line-height: 1.2;
}

.aso-tab__title {
    font-weight: 700;
    font-size: 0.92rem;
}

.aso-tab__desc {
    font-size: 0.72rem;
    color: #6c757d;
    margin-top: 0.12rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.aso-tab--active .aso-tab__desc {
    color: #4a6d7c;
}

.aso-tab__count {
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

.aso-tab--active .aso-tab__count {
    background: var(--primary, #174257);
    color: #fff;
}

.aso-tab-panel {
    margin-top: 0.25rem;
}

.aso-chart-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    padding: 1rem;
    height: 100%;
}

.ma-lista {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.ma-item {
    background: #fff;
    border: 1px solid #e6eaed;
    border-left: 4px solid #6c757d;
    border-radius: 4px;
    padding: 0.85rem 1rem;
}

.ma-item--baixo {
    border-left-color: #6c757d;
}

.ma-item--medio {
    border-left-color: #174257;
}

.ma-item--alto {
    border-left-color: #c48a00;
}

.ma-item--critico {
    border-left-color: #c0392b;
}

.ma-item__topo {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.35rem;
}

.ma-item__quando {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.4rem 0.55rem;
}

.ma-item__id {
    display: inline-block;
    margin-right: 0.15rem;
    padding: 0.12rem 0.45rem;
    border-radius: 3px;
    background: #174257;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: 0.01em;
}

.ma-item__data-label {
    width: 100%;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    line-height: 1.2;
}

.ma-item__data {
    font-size: 0.88rem;
    font-weight: 700;
    color: #174257;
    line-height: 1.2;
}

.ma-item__retorno {
    font-size: 0.78rem;
    font-weight: 400;
    color: #6c757d;
}

.ma-item__tipo {
    flex-shrink: 0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    padding: 0.22rem 0.55rem;
    border-radius: 3px;
    line-height: 1.2;
    max-width: 14rem;
    text-align: right;
}

.ma-item__tipo--baixo {
    background: #eef1f3;
    color: #495057;
}

.ma-item__tipo--medio {
    background: #e4eef3;
    color: #174257;
}

.ma-item__tipo--alto {
    background: #fff3cd;
    color: #856404;
}

.ma-item__tipo--critico {
    background: #f8d7da;
    color: #721c24;
}

.ma-item__nome {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #212529;
    line-height: 1.3;
}

.ma-item__contexto {
    margin: 0.15rem 0 0;
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1.35;
}

.ma-item__lotacao {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: #495057;
    line-height: 1.35;
}

.ma-item__dot {
    margin: 0 0.3rem;
}

.ma-item__causa {
    margin: 0.65rem 0 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #343a40;
    line-height: 1.35;
}

.ma-item__motivo {
    margin-top: 0.35rem;
}

.ma-item__motivo-texto {
    margin: 0;
    font-size: 0.86rem;
    color: #495057;
    line-height: 1.45;
    white-space: pre-wrap;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    overflow: hidden;
}

.ma-item__motivo-texto--aberto {
    display: block;
    -webkit-line-clamp: unset;
    overflow: visible;
}

.ma-item__mais {
    margin-top: 0.25rem;
    padding: 0;
    border: 0;
    background: transparent;
    color: #174257;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
}

.ma-item__mais:hover {
    text-decoration: underline;
}

.ma-item__rodape {
    margin-top: 0.65rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f0f2f4;
    font-size: 0.78rem;
    color: #6c757d;
}

@media (max-width: 576px) {
    .ma-item__topo {
        flex-direction: column;
        align-items: flex-start;
    }

    .ma-item__tipo {
        text-align: left;
        max-width: 100%;
    }
}
</style>
