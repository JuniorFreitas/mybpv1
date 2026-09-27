<template>
    <div class="relatorio-efetivo">
        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="controle.carregando"
            @submit="atualizar"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ef-filtro-busca">Colaborador</label>
                        <input
                            id="ef-filtro-busca"
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Buscar por nome ou código"
                            autocomplete="off"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && AUTENTICADO.temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ef-filtro-cnpj">CNPJ</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                input-id="ef-filtro-cnpj"
                                instance-id="ef-filtro-cnpj"
                                :disabled="controle.carregando"
                                :options="opcoesCnpj"
                                placeholder-blur="Todos os CNPJs"
                                empty-message="Nenhum CNPJ encontrado."
                                :max-results="50"
                                v-model="controle.dados.campoCnpj"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ef-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                input-id="ef-filtro-cc"
                                instance-id="ef-filtro-cc"
                                :disabled="controle.carregando || !opcoesCentroCusto.length"
                                :options="opcoesCentroCusto"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro encontrado."
                                :max-results="80"
                                v-model="controle.dados.campoCentroCusto"
                                @select="atualizar"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ef-filtro-tipo">Tipo de admissão</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroTipo"
                                input-id="ef-filtro-tipo"
                                instance-id="ef-filtro-tipo"
                                :disabled="controle.carregando"
                                :options="opcoesTipoAdmissao"
                                placeholder-blur="Todos os tipos"
                                empty-message="Nenhum tipo encontrado."
                                :max-results="20"
                                v-model="campoTipoCombo"
                                @select="atualizar"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ef-filtro-cargo">Cargo</label>
                        <input
                            id="ef-filtro-cargo"
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Buscar por cargo"
                            autocomplete="off"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoCargo"
                        />
                    </div>
                </div>

                <date-range-filter
                    v-model:enabled="controle.dados.campoPeriodo"
                    v-model:start-date="controle.dados.dataInicio"
                    v-model:end-date="controle.dados.dataFim"
                    :disabled="controle.carregando"
                    id-suffix="ef-admissao"
                    label="Período de admissão"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />
            </template>
            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                    Buscar
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-success"
                    @click.prevent="exportaExcel()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && lista.length === 0)"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </template>
        </FiltroListagem>

        <preload v-if="controle.carregando" class="text-center" />

        <div id="conteudo" v-show="!controle.carregando">
            <div class="alert alert-info text-center mt-3 mb-0" v-if="lista.length > 0">
                <strong>TOTAL DE FUNCIONÁRIOS: {{ totalGeral }}</strong>
                <span class="text-muted ml-2">
                    · {{ lista.length }} nesta página
                    · {{ listaAgrupada.length }} centro{{ listaAgrupada.length === 1 ? '' : 's' }}
                </span>
            </div>

            <div class="aso-tabs-bar mt-3" role="tablist" aria-label="Visualização do relatório" v-if="lista.length > 0">
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
                        <span class="aso-tab__desc">Agrupado por centro de custo</span>
                    </span>
                    <span class="aso-tab__count">{{ lista.length }}</span>
                </button>
                <button
                    type="button"
                    class="aso-tab"
                    role="tab"
                    :aria-selected="abaAtiva === 'graficos'"
                    :class="{ 'aso-tab--active': abaAtiva === 'graficos' }"
                    @click="abrirGraficos"
                >
                    <span class="aso-tab__icon"><i class="fas fa-chart-pie"></i></span>
                    <span class="aso-tab__text">
                        <span class="aso-tab__title">Gráficos</span>
                        <span class="aso-tab__desc">Tipo de admissão e centros</span>
                    </span>
                </button>
            </div>

            <div class="aso-tab-panel">
                <div
                    class="alert alert-warning text-center mt-3 mb-0"
                    v-show="lista.length === 0"
                >
                    <i class="fa fa-exclamation-triangle"></i>
                    {{
                        temFiltrosAtivos
                            ? 'Nenhum registro encontrado com os filtros atuais'
                            : 'Nenhum Registro Encontrado'
                    }}
                </div>

                <div v-show="abaAtiva === 'lista' && lista.length > 0" class="mt-3">
                    <div class="mybp-cards-lista">
                        <div
                            class="mybp-card"
                            v-for="centro in listaAgrupada"
                            :key="centro.key"
                        >
                            <div class="mybp-card-header-row">
                                <div class="mybp-card-left">
                                    <span class="mybp-badge-id" v-if="centro.id">
                                        #{{ centro.id }}
                                    </span>
                                    <div class="mybp-card-titulo">
                                        <strong>{{ centro.label }}</strong>
                                    </div>
                                </div>
                                <div class="mybp-card-right">
                                    <span class="badge badge-primary">
                                        {{ centro.admissao.length }}
                                        {{ centro.admissao.length === 1 ? 'funcionário' : 'funcionários' }}
                                    </span>
                                </div>
                            </div>

                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Nome</th>
                                            <th>Cargo</th>
                                            <th>Salário</th>
                                            <th>Tipo Admissão</th>
                                            <th>Data da Admissão</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="item in centro.admissao"
                                            :key="chaveFuncionario(item)"
                                        >
                                            <td>{{ codigoFuncionario(item) || '—' }}</td>
                                            <td><strong>{{ nomeFuncionario(item) }}</strong></td>
                                            <td>{{ item.cargo || 'Não informado' }}</td>
                                            <td>{{ formatarSalario(item.salario) }}</td>
                                            <td>{{ item.tipo_admissao || 'Não informado' }}</td>
                                            <td>{{ item.data_admissao || 'Não informado' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="abaAtiva === 'graficos' && lista.length > 0" class="aso-graficos mt-3">
                    <div class="row">
                        <div class="col-12 col-lg-5 mb-3">
                            <div class="aso-chart-card">
                                <ChartsDoughnut
                                    title="Por tipo de admissão"
                                    :labels="graficoTipos.labels"
                                    :values="graficoTipos.values"
                                    :colors="graficoTipos.colors"
                                />
                            </div>
                        </div>
                        <div class="col-12 col-lg-7 mb-3">
                            <div class="aso-chart-card">
                                <ChartsBar
                                    title="Top cargos"
                                    :labels="graficoCargos.labels"
                                    :datasets="graficoCargos.datasets"
                                    :altura="Math.max(280, graficoCargos.labels.length * 28)"
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

            <controle-paginacao
                v-show="abaAtiva === 'lista'"
                class="d-flex justify-content-center mt-3"
                id="controle"
                ref="componente"
                :url="urlPaginacao"
                :por-pagina="controle.dados.pages"
                :dados="controle.dados"
                @carregou="carregou"
                @carregando="carregando"
            />
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

const TIPOS_ADMISSAO = [
    'TEMPORARIO',
    'INTERMITENTE',
    'DETERMINADO',
    'FIXO',
    'PJ',
    'ESTÁGIO',
    'APRENDIZ'
]

const CORES_TIPO = [
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
    name: 'EfetivoRelatorio',
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
            preload: false,
            preloadExportacao: false,
            AUTENTICADO,
            abaAtiva: 'lista',
            lista: [],
            lista_ccs: null,
            graficos: {
                tipos: [],
                cargos: [],
                centros: []
            },
            totalGeral: 0,
            urlExportacao: `${URL_ADMIN}/relatorios/efetivo/export-excel`,
            urlPdf: `${URL_ADMIN}/relatorios/efetivo/pdf`,
            urlPaginacao: `${URL_ADMIN}/relatorios/efetivo/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 50,
                    campoBusca: '',
                    campoCnpj: '',
                    campoCentroCusto: '',
                    campoTipoAdmissao: '',
                    campoCargo: '',
                    campoPeriodo: false,
                    dataInicio: '',
                    dataFim: ''
                }
            }
        }
    },
    computed: {
        paramsExport() {
            return {
                campoBusca: this.controle.dados.campoBusca,
                campoCnpj: this.controle.dados.campoCnpj,
                campoCentroCusto: this.controle.dados.campoCentroCusto,
                campoTipoAdmissao: this.controle.dados.campoTipoAdmissao,
                campoCargo: this.controle.dados.campoCargo,
                campoPeriodo: this.controle.dados.campoPeriodo,
                dataInicio: this.controle.dados.dataInicio,
                dataFim: this.controle.dados.dataFim
            }
        },
        temFiltrosAtivos() {
            const d = this.controle.dados
            return !!(
                (d.campoBusca || '').trim() ||
                d.campoCnpj ||
                d.campoCentroCusto ||
                d.campoTipoAdmissao ||
                (d.campoCargo || '').trim() ||
                d.campoPeriodo
            )
        },
        campoTipoCombo: {
            get() {
                const val = this.controle.dados.campoTipoAdmissao
                return val === '' || val == null ? '' : String(val)
            },
            set(val) {
                this.controle.dados.campoTipoAdmissao = val === '' || val == null ? '' : val
            }
        },
        filtroListaCentroCustoCnpj() {
            if (!this.lista_ccs) return []
            if (this.controle.dados.campoCnpj !== '' && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.controle.dados.campoCnpj] || []
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
                    value: String(value),
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        opcoesTipoAdmissao() {
            const opts = [{ value: '', label: 'Todos os tipos' }]
            TIPOS_ADMISSAO.forEach((tipo) => {
                opts.push({ value: tipo, label: tipo })
            })
            return opts
        },
        listaAgrupada() {
            const grupos = new Map()
            ;(this.lista || []).forEach((item) => {
                const id = item.centro_custo_id
                const key = id == null || id === '' ? 'nenhum' : String(id)
                if (!grupos.has(key)) {
                    grupos.set(key, {
                        key,
                        id: id == null || id === '' ? null : id,
                        label: item.centro_custo_label || 'SEM CENTRO DE CUSTO',
                        admissao: []
                    })
                }
                grupos.get(key).admissao.push(item)
            })
            return Array.from(grupos.values()).sort((a, b) =>
                String(a.label).localeCompare(String(b.label), 'pt-BR')
            )
        },
        graficoTipos() {
            const itens = this.graficos.tipos || []
            const labels = itens.map((i) => i.label)
            return {
                labels,
                values: itens.map((i) => Number(i.total) || 0),
                colors: labels.map((_, i) => CORES_TIPO[i % CORES_TIPO.length])
            }
        },
        graficoCargos() {
            const itens = this.graficos.cargos || []
            const labels = itens.map((i) => i.label)
            return {
                labels,
                datasets: [
                    {
                        label: 'Funcionários',
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
                        label: 'Funcionários',
                        data: itens.map((i) => Number(i.total) || 0),
                        backgroundColor: '#2a6f8f'
                    }
                ]
            }
        }
    },
    mounted() {
        this.atualizar()
    },
    methods: {
        abrirGraficos() {
            this.abaAtiva = 'graficos'
        },
        onSelectCnpj() {
            this.controle.dados.campoCentroCusto = ''
            this.atualizar()
        },
        codigoFuncionario(item) {
            const curriculo = item && item.feedback && item.feedback.curriculo
            return curriculo && curriculo.id != null ? curriculo.id : ''
        },
        nomeFuncionario(item) {
            const curriculo = item && item.feedback && item.feedback.curriculo
            return (curriculo && curriculo.nome) || 'Não informado'
        },
        chaveFuncionario(item) {
            return item.id || this.codigoFuncionario(item) || this.nomeFuncionario(item)
        },
        formatarSalario(salario) {
            if (salario == null || salario === '') {
                return 'R$ 0,00'
            }
            const texto = String(salario)
            return texto.indexOf('R$') === 0 ? texto : `R$ ${texto}`
        },
        limparFiltros() {
            this.controle.dados.campoBusca = ''
            this.controle.dados.campoCnpj = ''
            this.controle.dados.campoCentroCusto = ''
            this.controle.dados.campoTipoAdmissao = ''
            this.controle.dados.campoCargo = ''
            this.controle.dados.campoPeriodo = false
            this.controle.dados.dataInicio = ''
            this.controle.dados.dataFim = ''
            this.abaAtiva = 'lista'
            if (!this.AUTENTICADO.temFilial && this.lista_ccs && this.lista_ccs.cnpjs) {
                const keys = Object.keys(this.lista_ccs.cnpjs)
                this.controle.dados.campoCnpj = keys.length ? keys[0] : ''
            }
            this.atualizar()
        },
        carregou(dados) {
            this.lista = (dados && dados.itens) || []
            this.lista_ccs = (dados && dados.cc) || null
            this.graficos = {
                tipos: (dados && dados.graficos && dados.graficos.tipos) || [],
                cargos: (dados && dados.graficos && dados.graficos.cargos) || [],
                centros: (dados && dados.graficos && dados.graficos.centros) || []
            }
            if (!this.AUTENTICADO.temFilial && this.lista_ccs && this.lista_ccs.cnpjs) {
                const keys = Object.keys(this.lista_ccs.cnpjs)
                if (keys.length && !this.controle.dados.campoCnpj) {
                    this.controle.dados.campoCnpj = keys[0]
                }
            }
            if (dados && dados.total_geral != null) {
                this.totalGeral = Number(dados.total_geral) || 0
            } else if (this.$refs.componente && this.$refs.componente.total != null) {
                this.totalGeral = Number(this.$refs.componente.total) || 0
            } else {
                this.totalGeral = this.lista.length
            }
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            if (this.$refs.componente) {
                this.$refs.componente.atual = 1
                if (typeof this.$refs.componente.buscar === 'function') {
                    this.$refs.componente.buscar()
                }
            }
        }
    }
}
</script>

<style scoped>
.relatorio-efetivo :deep(.mybp-filtros-form > [class*='col-']) {
    margin-bottom: var(--mybp-fc-gap, 0.45rem);
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
</style>
