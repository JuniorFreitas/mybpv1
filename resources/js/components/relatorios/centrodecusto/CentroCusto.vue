<template>
    <div class="relatorio-centro-custo">
        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="controle.carregando"
            @submit="atualizar"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div class="col-12 col-md-4" v-if="lista_ccs && AUTENTICADO.temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="cc-filtro-cnpj">CNPJ</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                input-id="cc-filtro-cnpj"
                                instance-id="cc-filtro-cnpj"
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

                <div class="col-12" v-if="lista_ccs">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="cc-filtro-centro">
                            Centros de custo
                            <span v-if="centrosSelecionados.length" class="cc-filtro-hint">
                                {{ centrosSelecionados.length }}
                            </span>
                        </label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                input-id="cc-filtro-centro"
                                instance-id="cc-filtro-centro"
                                :disabled="controle.carregando || !opcoesCentroCustoDisponiveis.length"
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
                        class="cc-filtro-badges"
                        v-if="centrosSelecionados.length"
                        role="list"
                        aria-label="Centros de custo selecionados"
                    >
                        <span
                            v-for="(item, ind) in centrosSelecionados"
                            :key="`${item.value}-${ind}`"
                            class="cc-filtro-badge"
                            role="listitem"
                        >
                            <span class="cc-filtro-badge__text">{{ item.label }}</span>
                            <button
                                type="button"
                                class="cc-filtro-badge__remove"
                                :aria-label="`Remover ${item.label}`"
                                :disabled="controle.carregando"
                                @click="removerCentro(ind)"
                            >
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                        </span>
                        <button
                            type="button"
                            class="btn btn-sm btn-link px-1 py-0"
                            :disabled="controle.carregando"
                            @click="limparCentrosSelecionados"
                        >
                            Limpar
                        </button>
                    </div>
                </div>
            </template>
            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                    Buscar
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-success"
                    @click.prevent="exportaPdf()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && lista.length === 0)"
                >
                    <i class="fas fa-file-pdf"></i> Exportar PDF
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
                    · {{ totalCentros }} centro{{ totalCentros === 1 ? '' : 's' }} de custo
                    · {{ lista.length }} nesta página
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
                            v-for="centro_de_custo in lista"
                            :key="centro_de_custo.id || centro_de_custo.label"
                        >
                            <div class="mybp-card-header-row">
                                <div class="mybp-card-left">
                                    <span class="mybp-badge-id" v-if="centro_de_custo.id">
                                        #{{ centro_de_custo.id }}
                                    </span>
                                    <div class="mybp-card-titulo">
                                        <strong>{{ centro_de_custo.label }}</strong>
                                    </div>
                                </div>
                                <div class="mybp-card-right">
                                    <span class="badge badge-primary">
                                        {{ qtdFuncionarios(centro_de_custo) }}
                                        {{ qtdFuncionarios(centro_de_custo) === 1 ? 'funcionário' : 'funcionários' }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="alert alert-warning mb-0 mt-2"
                                v-if="!qtdFuncionarios(centro_de_custo)"
                            >
                                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
                            </div>

                            <div class="table-responsive mt-2" v-else>
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Nome</th>
                                            <th>Cargo</th>
                                            <th>Tipo Admissão</th>
                                            <th>Data da Admissão</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="item in centro_de_custo.admissao"
                                            :key="chaveFuncionario(item)"
                                        >
                                            <td>{{ codigoFuncionario(item) || '—' }}</td>
                                            <td><strong>{{ nomeFuncionario(item) }}</strong></td>
                                            <td>{{ item.cargo || 'Não informado' }}</td>
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
import FiltroListagem from '../../ui/FiltroListagem.vue'

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
    name: 'CentroCustoRelatorio',
    mixins: [ExportacaoMixin],
    components: {
        ChartsBar,
        ChartsDoughnut,
        ComboboxAutoComplete,
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
            totalGeral: 0,
            totalCentros: 0,
            graficos: {
                tipos: [],
                cargos: [],
                centros: []
            },
            urlExportacao: `${URL_ADMIN}/relatorios/centrodecusto/export-excel`,
            urlPdf: `${URL_ADMIN}/relatorios/centrodecusto/pdf`,
            urlPaginacao: `${URL_ADMIN}/relatorios/centrodecusto/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 50,
                    campoCnpj: '',
                    campoCentrosCusto: []
                }
            },
            comboCentro: '',
            centrosSelecionados: []
        }
    },
    computed: {
        paramsExport() {
            return {
                campoCnpj: this.controle.dados.campoCnpj,
                campoCentrosCusto: this.controle.dados.campoCentrosCusto
            }
        },
        temFiltrosAtivos() {
            const d = this.controle.dados
            return !!(d.campoCnpj || (Array.isArray(d.campoCentrosCusto) && d.campoCentrosCusto.length))
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
        opcoesCentroCustoDisponiveis() {
            const selecionados = new Set(
                (this.centrosSelecionados || []).map((item) => String(item.value))
            )
            const opts = []
            if (this.filtroListaCentroCustoCnpj.length && selecionados.size < this.filtroListaCentroCustoCnpj.length) {
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
                colors: labels.map((_, i) => CORES_TIPO[i % CORES_TIPO.length])
            }
        },
        graficoCargos() {
            const itens = this.graficos.cargos || []
            return {
                labels: itens.map((i) => i.label),
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
        syncCampoCentrosCusto() {
            this.controle.dados.campoCentrosCusto = (this.centrosSelecionados || []).map((item) =>
                String(item.value)
            )
        },
        onSelectCnpj() {
            this.centrosSelecionados = []
            this.comboCentro = ''
            this.syncCampoCentrosCusto()
            this.atualizar()
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
            } else {
                const existe = (this.centrosSelecionados || []).some(
                    (item) => String(item.value) === valor
                )
                if (!existe) {
                    this.centrosSelecionados.push({
                        value: valor,
                        label: (opt && opt.label) || valor
                    })
                }
            }
            this.syncCampoCentrosCusto()
            this.$nextTick(() => {
                this.comboCentro = ''
            })
            this.atualizar()
        },
        removerCentro(indice) {
            this.centrosSelecionados.splice(indice, 1)
            this.syncCampoCentrosCusto()
            this.atualizar()
        },
        limparCentrosSelecionados() {
            this.centrosSelecionados = []
            this.comboCentro = ''
            this.syncCampoCentrosCusto()
            this.atualizar()
        },
        qtdFuncionarios(centro) {
            return (centro && centro.admissao && centro.admissao.length) || 0
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
        limparFiltros() {
            this.controle.dados.campoCnpj = ''
            this.centrosSelecionados = []
            this.comboCentro = ''
            this.syncCampoCentrosCusto()
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
            this.totalGeral = Number((dados && dados.total_geral) || 0)
            this.totalCentros = Number((dados && dados.total_centros) || this.lista.length)
            if (!this.AUTENTICADO.temFilial && this.lista_ccs && this.lista_ccs.cnpjs) {
                const keys = Object.keys(this.lista_ccs.cnpjs)
                if (keys.length && !this.controle.dados.campoCnpj) {
                    this.controle.dados.campoCnpj = keys[0]
                }
            }
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.syncCampoCentrosCusto()
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
.relatorio-centro-custo :deep(.mybp-filtros-form > [class*='col-']) {
    margin-bottom: var(--mybp-fc-gap, 0.45rem);
}

.cc-filtro-hint {
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

.cc-filtro-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
    margin-top: 0.3rem;
    padding: 0.1rem 0;
}

.cc-filtro-badge {
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

.cc-filtro-badge__text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 220px;
}

.cc-filtro-badge__remove {
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

.cc-filtro-badge__remove:hover {
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
</style>
