<template>
    <div class="relatorio-ferias">
        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="preload"
            @submit="buscarDados"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-tipo">Filtrar por</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroTipo"
                                input-id="ferias-filtro-tipo"
                                instance-id="ferias-filtro-tipo"
                                :disabled="preload"
                                :options="opcoesTipo"
                                placeholder-blur="Selecione"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                v-model="filtrar.tipo"
                                @opening="fecharOutrosComboboxes('ferias-filtro-tipo')"
                                @select="onSelectTipo"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="filtrar.tipo === 'aquisitivo'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-periodo">Período aquisitivo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPeriodo"
                                input-id="ferias-filtro-periodo"
                                instance-id="ferias-filtro-periodo"
                                :disabled="preload"
                                :options="opcoesPeriodoAquisitivo"
                                placeholder-blur="Selecione o período"
                                empty-message="Nenhum período encontrado."
                                :max-results="50"
                                v-model="filtrar.periodo"
                                @opening="fecharOutrosComboboxes('ferias-filtro-periodo')"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <date-range-filter
                    v-if="filtrar.tipo === 'data'"
                    v-model:enabled="filtroPeriodoData"
                    v-model:start-date="dataInicio"
                    v-model:end-date="dataFim"
                    :disabled="preload"
                    id-suffix="ferias-saida"
                    label="Data de saída"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="onPeriodoChange"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-colaborador">
                            Colaborador / CPF
                            <span v-if="buscaUnificadaEhCpf" class="ferias-filtro-hint">CPF</span>
                        </label>
                        <input
                            id="ferias-filtro-colaborador"
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Nome ou CPF"
                            autocomplete="off"
                            inputmode="search"
                            :disabled="preload"
                            :value="campoBuscaUnificada"
                            @input="onInputBuscaUnificada"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-status">Por status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                input-id="ferias-filtro-status"
                                instance-id="ferias-filtro-status"
                                :disabled="preload"
                                :options="opcoesStatus"
                                placeholder-blur="Todos"
                                empty-message="Nenhum status encontrado."
                                :max-results="30"
                                v-model="filtrar.status_ferias"
                                @opening="fecharOutrosComboboxes('ferias-filtro-status')"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && AUTENTICADO && AUTENTICADO.temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-cnpj">CNPJ</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                input-id="ferias-filtro-cnpj"
                                instance-id="ferias-filtro-cnpj"
                                :disabled="preload"
                                :options="opcoesCnpj"
                                placeholder-blur="Todos os CNPJs"
                                empty-message="Nenhum CNPJ encontrado."
                                :max-results="50"
                                v-model="campoCnpj"
                                @opening="fecharOutrosComboboxes('ferias-filtro-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12" v-if="lista_ccs">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-cc">
                            Centros de custo
                            <span v-if="centrosSelecionados.length" class="ferias-filtro-hint">
                                {{ centrosSelecionados.length }}
                            </span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                input-id="ferias-filtro-cc"
                                instance-id="ferias-filtro-cc"
                                :disabled="preload || !opcoesCentroCustoDisponiveis.length"
                                :options="opcoesCentroCustoDisponiveis"
                                placeholder-blur="Adicionar centro de custo"
                                placeholder-focus="Digite para filtrar…"
                                empty-message="Nenhum centro disponível."
                                :max-results="80"
                                v-model="comboCentro"
                                @opening="fecharOutrosComboboxes('ferias-filtro-cc')"
                                @select="onSelectCentro"
                            />
                        </div>
                    </div>
                    <div
                        class="ferias-filtro-badges"
                        v-if="centrosSelecionados.length"
                        role="list"
                        aria-label="Centros de custo selecionados"
                    >
                        <span
                            v-for="(item, ind) in centrosSelecionados"
                            :key="`${item.value}-${ind}`"
                            class="ferias-filtro-badge"
                            role="listitem"
                        >
                            <span class="ferias-filtro-badge__text">{{ item.label }}</span>
                            <button
                                type="button"
                                class="ferias-filtro-badge__remove"
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
                <strong>TOTAL DE FÉRIAS: {{ totalGeral }}</strong>
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
                        <span class="aso-tab__desc">Detalhes por colaborador</span>
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
                        <span class="aso-tab__desc">Status e centros</span>
                    </span>
                </button>
            </div>

            <div class="aso-tab-panel">
                <div
                    class="alert alert-warning mt-3 mb-0"
                    v-show="!dados.length"
                >
                    <i class="fa fa-exclamation-triangle"></i>
                    {{
                        temFiltrosAtivos
                            ? 'Nenhum registro encontrado com os filtros atuais'
                            : 'Nenhum Registro Encontrado'
                    }}
                </div>

                <div v-show="abaAtiva === 'lista' && dados.length" class="mt-3">
                    <div v-for="(item, index) in dados" :key="item.ferias_id || index" class="mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="mt-2 table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr class="bg-white text-center">
                                            <th
                                                rowspan="4"
                                                style="display: table-cell; vertical-align: middle; text-align: center"
                                            >
                                                {{ index + 1 }}
                                            </th>
                                            <th colspan="6">
                                                {{ item.nome }}
                                                <br />
                                                (Admitido em: {{ item.data_admissao }})
                                                <template v-if="item.emp_nome_fantasia || item.emp_cnpj">
                                                    <br />
                                                    <small class="text-muted">
                                                        <span v-if="item.emp_nome_fantasia">{{ item.emp_nome_fantasia }}</span>
                                                        <span v-if="item.emp_nome_fantasia && item.emp_cnpj"> · </span>
                                                        <span v-if="item.emp_cnpj">{{ item.emp_cnpj }}</span>
                                                    </small>
                                                </template>
                                            </th>
                                        </tr>
                                        <tr class="bg-white text-center">
                                            <th colspan="6">{{ item.cargo }}</th>
                                        </tr>
                                        <tr class="bg-white">
                                            <th style="text-align: center">Centro de custo</th>
                                            <th style="text-align: center">Qnt dias</th>
                                            <th style="text-align: center">Férias</th>
                                            <th style="text-align: center">Período Aquisitivo</th>
                                            <th style="text-align: center">Data Limite</th>
                                            <th style="text-align: center">Status</th>
                                        </tr>
                                        <tr
                                            :class="{
                                                'table-danger': item.pintar && item.status === 'aguardando',
                                                'table-success': item.status === 'gozando'
                                            }"
                                        >
                                            <th style="text-align: center">{{ item.centro_custo }}</th>
                                            <th style="text-align: center">{{ item.qnt_dias }}</th>
                                            <th style="text-align: center">
                                                {{ item.data_saida }} à {{ item.data_retorno }}
                                            </th>
                                            <th style="text-align: center">{{ item.periodo_aquisitivo }}</th>
                                            <th style="text-align: center">{{ item.ultima_data }}</th>
                                            <th style="text-align: center">
                                                <span class="text-uppercase">{{ item.status }}</span>
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="abaAtiva === 'graficos' && dados.length" class="aso-graficos mt-3">
                    <div class="row">
                        <div class="col-12 col-lg-5 mb-3">
                            <div class="aso-chart-card">
                                <ChartsDoughnut
                                    title="Por status"
                                    :labels="graficoStatus.labels"
                                    :values="graficoStatus.values"
                                    :colors="graficoStatus.colors"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-lg-7 mb-3">
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

const CORES = [
    '#174257',
    '#2a6f8f',
    '#c48a00',
    '#c0392b',
    '#28a745',
    '#6c757d',
    '#fd7e14',
    '#6f42c1',
    '#20c997',
    '#e83e8c'
]

export default {
    mixins: [ExportacaoMixin],
    components: {
        ChartsBar,
        ChartsDoughnut,
        ComboboxAutoComplete,
        DateRangeFilter,
        FiltroListagem
    },
    data() {
        return {
            AUTENTICADO,
            preload: false,
            dados: [],
            filtro: [],
            periodo: '',
            filtroPeriodoData: true,
            dataInicio: '',
            dataFim: '',
            abaAtiva: 'lista',
            lista_ccs: null,
            graficos: { status: [], centros: [] },
            totalGeral: 0,
            campoBusca: '',
            campoCPF: '',
            campoCnpj: '',
            comboCentro: '',
            centrosSelecionados: [],
            urlExportacao: `${URL_ADMIN}/relatorios/ferias/export-excel`,
            filtrar: {
                tipo: 'data',
                periodo: '',
                periodo_range: '',
                status_ferias: ''
            }
        }
    },
    async mounted() {
        this.aplicarPeriodoPadrao()
        await this.periodosAquisitivosList()
        if (this.filtro.periodo_aquisitivo && this.filtro.periodo_aquisitivo[1]) {
            this.filtrar.periodo = this.filtro.periodo_aquisitivo[1].id
        }
        await this.buscarDados()
    },
    computed: {
        paramsExport() {
            return this.payloadFiltros()
        },
        temFiltrosAtivos() {
            return !!(
                this.filtrar.status_ferias ||
                this.filtrar.tipo !== 'data' ||
                (this.campoBusca || '').trim() ||
                (this.campoCPF || '').trim() ||
                this.campoCnpj ||
                this.centrosSelecionados.length
            )
        },
        campoBuscaUnificada() {
            if (this.campoCPF) return this.campoCPF
            return this.campoBusca || ''
        },
        buscaUnificadaEhCpf() {
            return !!this.campoCPF
        },
        opcoesTipo() {
            return [
                { value: 'aquisitivo', label: 'Período aquisitivo' },
                { value: 'data', label: 'Por data de saída' }
            ]
        },
        opcoesPeriodoAquisitivo() {
            const opts = []
            ;((this.filtro && this.filtro.periodo_aquisitivo) || []).forEach((item) => {
                opts.push({ value: item.id, label: item.label })
            })
            return opts
        },
        opcoesStatus() {
            const opts = [{ value: '', label: 'Todos' }]
            ;((this.filtro && this.filtro.status_ferias) || []).forEach((item) => {
                opts.push({ value: item, label: this.letterCase(item) })
            })
            return opts
        },
        filtroListaCentroCustoCnpj() {
            if (!this.lista_ccs) return []
            const temFilial = !!(this.AUTENTICADO && this.AUTENTICADO.temFilial)
            if (this.campoCnpj !== '' && temFilial) {
                return this.lista_ccs.centros_custos[this.campoCnpj] || []
            }
            if (!temFilial) {
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
            this.filtroListaCentroCustoCnpj.forEach((item) => {
                const value = item.matriz ? item.id : item.filial_id
                if (value == null || selecionados.has(String(value))) return
                opts.push({
                    value: String(value),
                    label: item.label || String(value)
                })
            })
            return opts
        },
        graficoStatus() {
            const itens = this.graficos.status || []
            return {
                labels: itens.map((i) => this.letterCase(i.label)),
                values: itens.map((i) => Number(i.total) || 0),
                colors: itens.map((_, idx) => CORES[idx % CORES.length])
            }
        },
        graficoCentros() {
            const itens = this.graficos.centros || []
            return {
                labels: itens.map((i) => i.label),
                datasets: [
                    {
                        label: 'Férias',
                        data: itens.map((i) => Number(i.total) || 0),
                        backgroundColor: '#2a6f8f'
                    }
                ]
            }
        }
    },
    methods: {
        payloadFiltros() {
            return {
                ...this.filtrar,
                campoBusca: (this.campoBusca || '').trim(),
                campoCPF: (this.campoCPF || '').trim(),
                campoCnpj: this.campoCnpj,
                campoCentrosCusto: (this.centrosSelecionados || []).map((i) => String(i.value))
            }
        },
        formatarCpfDigitos(valor) {
            const d = String(valor || '').replace(/\D/g, '').slice(0, 11)
            if (d.length <= 3) return d
            if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`
            if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`
            return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`
        },
        parecePadraoCpf(valor) {
            const raw = String(valor || '').trim()
            if (!raw) return false
            if (!/^[\d.\-\s]+$/.test(raw)) return false
            const digitos = raw.replace(/\D/g, '')
            return digitos.length > 0 && digitos.length <= 11
        },
        onInputBuscaUnificada(event) {
            const valor = event && event.target ? event.target.value : ''
            if (!valor) {
                this.campoBusca = ''
                this.campoCPF = ''
                return
            }
            if (this.parecePadraoCpf(valor)) {
                const mascarado = this.formatarCpfDigitos(valor)
                this.campoCPF = mascarado
                this.campoBusca = ''
                if (event.target && event.target.value !== mascarado) {
                    event.target.value = mascarado
                }
                return
            }
            this.campoBusca = valor
            this.campoCPF = ''
        },
        letterCase(value) {
            if (!value) return ''
            return value.charAt(0).toUpperCase() + value.slice(1)
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'ferias-filtro-tipo': 'comboFiltroTipo',
                'ferias-filtro-periodo': 'comboFiltroPeriodo',
                'ferias-filtro-status': 'comboFiltroStatus',
                'ferias-filtro-cnpj': 'comboFiltroCnpj',
                'ferias-filtro-cc': 'comboFiltroCc'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
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
        aplicarPeriodoPadrao() {
            this.filtroPeriodoData = true
            this.dataInicio = moment().startOf('month').format('YYYY-MM-DD')
            this.dataFim = moment().add(1, 'M').endOf('month').format('YYYY-MM-DD')
            this.syncPeriodoRange()
        },
        syncPeriodoRange() {
            if (this.filtroPeriodoData && this.dataInicio && this.dataFim) {
                this.filtrar.periodo_range = `${this.formatarDataFiltro(this.dataInicio)} até ${this.formatarDataFiltro(this.dataFim)}`
            } else {
                this.filtrar.periodo_range = ''
            }
        },
        async onPeriodoChange() {
            this.syncPeriodoRange()
            await this.buscarDados()
        },
        async onSelectTipo() {
            if (this.filtrar.tipo === 'data') {
                this.aplicarPeriodoPadrao()
            }
            await this.buscarDados()
        },
        async onSelectCnpj() {
            this.centrosSelecionados = []
            this.comboCentro = ''
            await this.buscarDados()
        },
        onSelectCentro(opcao) {
            const valor = opcao && opcao.value != null ? String(opcao.value) : String(this.comboCentro || '')
            if (!valor) return
            if (valor === '__multi__' && Array.isArray(opcao && opcao.items)) {
                const mapa = new Map(
                    (this.centrosSelecionados || []).map((item) => [String(item.value), item])
                )
                opcao.items.forEach((item) => {
                    if (item && item.value != null) {
                        mapa.set(String(item.value), {
                            value: String(item.value),
                            label: item.label || String(item.value)
                        })
                    }
                })
                this.centrosSelecionados = Array.from(mapa.values())
            } else if (
                !(this.centrosSelecionados || []).some((item) => String(item.value) === valor)
            ) {
                this.centrosSelecionados.push({
                    value: valor,
                    label: (opcao && opcao.label) || valor
                })
            }
            this.comboCentro = ''
            this.buscarDados()
        },
        removerCentro(indice) {
            this.centrosSelecionados.splice(indice, 1)
            this.buscarDados()
        },
        limparCentrosSelecionados() {
            this.centrosSelecionados = []
            this.buscarDados()
        },
        async limparFiltros() {
            this.filtrar.tipo = 'data'
            this.filtrar.status_ferias = ''
            this.campoBusca = ''
            this.campoCPF = ''
            this.campoCnpj = ''
            this.centrosSelecionados = []
            this.comboCentro = ''
            this.abaAtiva = 'lista'
            this.aplicarPeriodoPadrao()
            if (this.filtro.periodo_aquisitivo && this.filtro.periodo_aquisitivo[1]) {
                this.filtrar.periodo = this.filtro.periodo_aquisitivo[1].id
            }
            if (!this.AUTENTICADO?.temFilial && this.lista_ccs && this.lista_ccs.cnpjs) {
                const keys = Object.keys(this.lista_ccs.cnpjs)
                this.campoCnpj = keys.length ? keys[0] : ''
            }
            await this.buscarDados()
        },
        async periodosAquisitivosList() {
            try {
                const { data } = await axios.post(`${URL_ADMIN}/relatorios/ferias/listaperiodos`)
                this.filtro = data.filtro
            } catch (err) {
                this.filtro = []
            }
        },
        async buscarDados() {
            this.syncPeriodoRange()
            this.preload = true
            try {
                const { data } = await axios.post(
                    `${URL_ADMIN}/relatorios/ferias`,
                    this.payloadFiltros()
                )
                const payload = data || {}
                this.dados = payload.dados || []
                this.lista_ccs = payload.cc || null
                this.graficos = {
                    status: (payload.graficos && payload.graficos.status) || [],
                    centros: (payload.graficos && payload.graficos.centros) || []
                }
                this.totalGeral = Number(payload.total != null ? payload.total : this.dados.length)
                if (!this.AUTENTICADO?.temFilial && this.lista_ccs && this.lista_ccs.cnpjs) {
                    const keys = Object.keys(this.lista_ccs.cnpjs)
                    if (keys.length && !this.campoCnpj) {
                        this.campoCnpj = keys[0]
                    }
                }
            } catch (err) {
                this.dados = []
                this.totalGeral = 0
                this.graficos = { status: [], centros: [] }
            } finally {
                this.preload = false
            }
        }
    }
}
</script>

<style scoped>
.relatorio-ferias :deep(.mybp-filtros-form > [class*='col-']) {
    margin-bottom: var(--mybp-fc-gap, 0.45rem);
}

.ferias-filtro-hint {
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

.ferias-filtro-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
    margin-top: 0.3rem;
    padding: 0.1rem 0;
}

.ferias-filtro-badge {
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

.ferias-filtro-badge__text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 220px;
}

.ferias-filtro-badge__remove {
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

.ferias-filtro-badge__remove:hover {
    background: rgba(23, 66, 87, 0.12);
}

.aso-tabs-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.35rem;
}

.aso-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    border: 1px solid transparent;
    border-radius: 6px 6px 0 0;
    background: #f8f9fa;
    color: #495057;
    padding: 0.55rem 0.85rem;
    min-width: 180px;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.aso-tab:hover {
    background: #eef3f6;
    color: #174257;
}

.aso-tab:focus {
    outline: 2px solid rgba(23, 66, 87, 0.35);
    outline-offset: 1px;
}

.aso-tab--active {
    background: #fff;
    border-color: #dee2e6;
    border-bottom-color: #fff;
    color: #174257;
    box-shadow: 0 -1px 0 #fff;
}

.aso-tab__icon {
    font-size: 1rem;
    opacity: 0.85;
}

.aso-tab__text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
    text-align: left;
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
</style>
