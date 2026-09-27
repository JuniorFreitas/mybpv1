<template>
    <div class="aso-vencimento">
        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="preload"
            @submit="buscarDados"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <date-range-filter
                    v-model:enabled="controle.dados.filtroVencimento"
                    v-model:start-date="controle.dados.dataInicioVencimento"
                    v-model:end-date="controle.dados.dataFimVencimento"
                    :disabled="preload"
                    id-suffix="aso-vencimento"
                    label="Período de vencimento"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="onPeriodoChange"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="aso-filtro-colaborador">Colaborador</label>
                        <input
                            id="aso-filtro-colaborador"
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Buscar por colaborador"
                            autocomplete="off"
                            :disabled="preload"
                            v-model="controle.dados.campoBusca"
                            @input="onBuscaInput"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && AUTENTICADO.temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="aso-filtro-cnpj">Por CNPJ</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                input-id="aso-filtro-cnpj"
                                instance-id="aso-filtro-cnpj"
                                :disabled="preload"
                                :options="opcoesCnpj"
                                placeholder-blur="Todos os CNPJs"
                                empty-message="Nenhum CNPJ encontrado."
                                :max-results="50"
                                v-model="controle.dados.campoCnpj"
                                @opening="fecharOutrosComboboxes('aso-filtro-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="aso-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                input-id="aso-filtro-cc"
                                instance-id="aso-filtro-cc"
                                :disabled="preload || !opcoesCentroCusto.length"
                                :options="opcoesCentroCusto"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro encontrado."
                                :max-results="80"
                                v-model="controle.dados.campoCentroCusto"
                                @opening="fecharOutrosComboboxes('aso-filtro-cc')"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="aso-filtro-tipo">Tipo de exame</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroTipo"
                                input-id="aso-filtro-tipo"
                                instance-id="aso-filtro-tipo"
                                :disabled="preload"
                                :options="opcoesTipoExame"
                                placeholder-blur="Todos os tipos"
                                empty-message="Nenhum tipo encontrado."
                                :max-results="50"
                                v-model="controle.dados.campoTipoExame"
                                @opening="fecharOutrosComboboxes('aso-filtro-tipo')"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="aso-filtro-vencido">Vencido</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroVencido"
                                input-id="aso-filtro-vencido"
                                instance-id="aso-filtro-vencido"
                                :disabled="preload"
                                :options="opcoesVencido"
                                placeholder-blur="Indiferente"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                v-model="controle.dados.campoVencido"
                                @opening="fecharOutrosComboboxes('aso-filtro-vencido')"
                                @select="buscarDados"
                            />
                        </div>
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
                    @click.prevent="gerarArquivoXls()"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                <span class="aso-filtro-alerta text-muted small" v-if="periodo_vencimento_extenso">
                    <i class="fas fa-bell"></i> Alerta: {{ periodo_vencimento_extenso }}
                </span>
            </template>
        </FiltroListagem>

        <preload v-show="preload" class="text-center" />

        <div v-if="!preload">
            <div class="row aso-kpis mt-3" v-if="dados.length">
                <div class="col-6 col-md-3 mb-2">
                    <button
                        type="button"
                        class="aso-kpi aso-kpi--btn"
                        :class="{ 'aso-kpi--active': filtroSituacao === 'todos' }"
                        @click="setFiltroSituacao('todos')"
                    >
                        <div class="aso-kpi__label">Total</div>
                        <div class="aso-kpi__value">{{ resumo.total }}</div>
                    </button>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <button
                        type="button"
                        class="aso-kpi aso-kpi--btn aso-kpi--danger"
                        :class="{ 'aso-kpi--active': filtroSituacao === 'vencidos' }"
                        @click="setFiltroSituacao('vencidos')"
                    >
                        <div class="aso-kpi__label">Vencidos</div>
                        <div class="aso-kpi__value">{{ resumo.vencidos }}</div>
                    </button>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <button
                        type="button"
                        class="aso-kpi aso-kpi--btn aso-kpi--warn"
                        :class="{ 'aso-kpi--active': filtroSituacao === 'avencer' }"
                        @click="setFiltroSituacao('avencer')"
                    >
                        <div class="aso-kpi__label">A vencer ({{ periodo_vencimento_extenso || 'alerta' }})</div>
                        <div class="aso-kpi__value">{{ resumo.aVencer }}</div>
                    </button>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <button
                        type="button"
                        class="aso-kpi aso-kpi--btn aso-kpi--ok"
                        :class="{ 'aso-kpi--active': filtroSituacao === 'emdia' }"
                        @click="setFiltroSituacao('emdia')"
                    >
                        <div class="aso-kpi__label">Em dia</div>
                        <div class="aso-kpi__value">{{ resumo.emDia }}</div>
                    </button>
                </div>
            </div>

            <div class="aso-tabs-bar mt-3" role="tablist" aria-label="Visualização do relatório">
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
                    <span class="aso-tab__count" v-if="dados.length">{{ dadosFiltrados.length }}</span>
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
                        <span class="aso-tab__desc">Visão por situação e lotação</span>
                    </span>
                </button>
            </div>

            <div class="aso-tab-panel">
            <div class="alert alert-warning mt-3 mb-0" v-show="!dados.length">
                <i class="fa fa-exclamation-triangle"></i> Nenhum registro encontrado
            </div>

            <div v-show="abaAtiva === 'lista' && dados.length" class="aso-lista mt-3">
                <div class="aso-lista-toolbar d-flex flex-wrap align-items-center justify-content-between mb-2">
                    <div class="aso-legenda">
                        <span class="aso-legenda__item">
                            <span class="aso-legenda__swatch aso-legenda__swatch--vencido"></span>
                            Vencido
                        </span>
                        <span class="aso-legenda__item">
                            <span class="aso-legenda__swatch aso-legenda__swatch--alerta"></span>
                            A vencer (≤ {{ periodo_vencimento_extenso || 'alerta' }})
                        </span>
                        <span class="aso-legenda__item">
                            <span class="aso-legenda__swatch aso-legenda__swatch--ok"></span>
                            Em dia
                        </span>
                        <span class="aso-legenda__sep">·</span>
                        <span class="aso-legenda__count">
                            <template v-if="filtroSituacao === 'todos'">
                                {{ resumo.total }} registro{{ resumo.total === 1 ? '' : 's' }}
                            </template>
                            <template v-else>
                                Exibindo <strong>{{ dadosFiltrados.length }}</strong>
                                de {{ resumo.total }}
                                ({{ labelFiltroSituacao }})
                            </template>
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2 small text-muted" for="aso-por-pagina">Por página</label>
                        <select
                            id="aso-por-pagina"
                            class="form-control form-control-sm aso-page-size"
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

                <div class="table-responsive aso-table-wrap">
                    <table class="table table-sm table-hover aso-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center aso-col-num" style="width: 44px">#</th>
                                <th class="aso-col-sticky aso-th-sort" @click="ordenarPor('colaborador')">
                                    Colaborador
                                    <i :class="iconeOrdenacao('colaborador')"></i>
                                </th>
                                <th class="text-nowrap" v-if="AUTENTICADO.temFilial">Lotação</th>
                                <th class="aso-th-sort" @click="ordenarPor('emp_centro_custo')">
                                    Centro de Custo
                                    <i :class="iconeOrdenacao('emp_centro_custo')"></i>
                                </th>
                                <th class="text-center aso-th-sort" @click="ordenarPor('exame_tipo')">
                                    Tipo
                                    <i :class="iconeOrdenacao('exame_tipo')"></i>
                                </th>
                                <th class="text-center text-nowrap aso-th-sort" @click="ordenarPor('data_aso')">
                                    Data ASO
                                    <i :class="iconeOrdenacao('data_aso')"></i>
                                </th>
                                <th class="text-center text-nowrap aso-th-sort" @click="ordenarPor('data_vencimento')">
                                    Vencimento
                                    <i :class="iconeOrdenacao('data_vencimento')"></i>
                                </th>
                                <th class="text-center aso-th-sort" @click="ordenarPor('dias_vencer')">
                                    Situação
                                    <i :class="iconeOrdenacao('dias_vencer')"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(vencimento, index) in dadosPaginados"
                                :key="vencimento.feedback_id || index"
                                :class="linhaClasse(vencimento)"
                            >
                                <td class="text-center text-muted aso-col-num">
                                    {{ (paginaAtual - 1) * porPagina + index + 1 }}
                                </td>
                                <td class="aso-col-sticky aso-col-pessoa">
                                    <div class="aso-nome">{{ vencimento.colaborador }}</div>
                                    <div class="aso-meta">{{ vencimento.cargo || '—' }}</div>
                                    <div class="aso-meta" v-if="vencimento.data_admissao && vencimento.data_admissao !== 'Não informada'">
                                        Admissão: {{ formatarDataBr(vencimento.data_admissao) }}
                                    </div>
                                </td>
                                <td v-if="AUTENTICADO.temFilial" class="aso-col-lotacao">
                                    <template v-if="vencimento.emp_cnpj">
                                        <div class="aso-lotacao-nome">{{ vencimento.emp_nome_fantasia }}</div>
                                        <div class="aso-meta">{{ vencimento.emp_tipo }} · {{ vencimento.emp_cnpj }}</div>
                                    </template>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td>
                                    <span :title="vencimento.emp_centro_custo || ''">
                                        {{ vencimento.emp_centro_custo || '—' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="aso-tipo">{{ vencimento.exame_tipo || '—' }}</span>
                                </td>
                                <td class="text-center text-nowrap">{{ formatarDataBr(vencimento.data_aso) }}</td>
                                <td class="text-center text-nowrap aso-venc-data">
                                    {{ formatarDataBr(vencimento.data_vencimento) }}
                                </td>
                                <td class="text-center aso-col-situacao">
                                    <span class="aso-pill" :class="pillClasse(vencimento)">
                                        {{ textoSituacao(vencimento) }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!dadosPaginados.length">
                                <td :colspan="AUTENTICADO.temFilial ? 8 : 7" class="text-center text-muted py-4">
                                    Nenhum registro neste filtro
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="aso-paginacao d-flex flex-wrap align-items-center justify-content-between mt-2" v-if="totalPaginas > 1">
                    <small class="text-muted">
                        Página {{ paginaAtual }} de {{ totalPaginas }}
                    </small>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" :disabled="paginaAtual <= 1" @click="paginaAtual = 1">
                            «
                        </button>
                        <button type="button" class="btn btn-outline-secondary" :disabled="paginaAtual <= 1" @click="paginaAtual--">
                            ‹
                        </button>
                        <button type="button" class="btn btn-outline-secondary" :disabled="paginaAtual >= totalPaginas" @click="paginaAtual++">
                            ›
                        </button>
                        <button type="button" class="btn btn-outline-secondary" :disabled="paginaAtual >= totalPaginas" @click="paginaAtual = totalPaginas">
                            »
                        </button>
                    </div>
                </div>
            </div>

            <div v-show="abaAtiva === 'graficos' && dados.length" class="aso-graficos mt-3">
                <div class="row">
                    <div class="col-12 col-lg-5 mb-3">
                        <div class="aso-chart-card">
                            <ChartsDoughnut
                                title="Situação dos ASOs"
                                :labels="graficoSituacao.labels"
                                :values="graficoSituacao.values"
                                :colors="graficoSituacao.colors"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-lg-7 mb-3">
                        <div class="aso-chart-card">
                            <ChartsBar
                                title="Por tipo de exame"
                                :labels="graficoTipos.labels"
                                :datasets="graficoTipos.datasets"
                                :altura="300"
                            />
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="aso-chart-card">
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
import configselect2 from '../../Select2/mixSelec2'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Utils from '../../../mixins/Utils'
import Validacoes from '../../../mixins/Validacoes'
import Configuracoes from '../../../mixins/Configuracoes.js'
import ChartsDoughnut from '../../Charts/Doughnut.vue'
import ChartsBar from '../../Charts/Bar.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import DateRangeFilter from '../../DateRangeFilter.vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'

export default {
    mixins: [configselect2, ExportacaoMixin, Utils, Validacoes, Configuracoes],
    components: { ChartsDoughnut, ChartsBar, ComboboxAutoComplete, DateRangeFilter, FiltroListagem },
    data() {
        return {
            AUTENTICADO,
            lista_ccs: null,
            preload: false,
            dados: [],
            listaTiposExame: [],
            periodo_vencimento_numero: null,
            periodo_vencimento_extenso: null,
            abaAtiva: 'lista',
            buscaTimer: null,
            filtroSituacao: 'todos',
            ordenacaoCampo: 'dias_vencer',
            ordenacaoDir: 'asc',
            paginaAtual: 1,
            porPagina: 50,
            urlExportacao: `${URL_ADMIN}/relatorios/vencimentoasos/export-excel`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoTipoExame: '',
                    campoVencido: '',
                    filtroVencimento: false,
                    dataInicioVencimento: '',
                    dataFimVencimento: '',
                    campoVencimento: '',
                    campoCnpj: '',
                    campoCentroCusto: ''
                }
            }
        }
    },
    mounted() {
        this.buscarDados()
        this.tiposExames()
    },
    beforeUnmount() {
        if (this.buscaTimer) clearTimeout(this.buscaTimer)
    },
    watch: {
        totalPaginas(val) {
            if (this.paginaAtual > val) this.paginaAtual = val
        }
    },
    computed: {
        paramsExport() {
            return this.controle.dados
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
                    value,
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        opcoesTipoExame() {
            const opts = [{ value: '', label: 'Todos os tipos' }]
            ;(this.listaTiposExame || []).forEach((item) => {
                opts.push({ value: item.id, label: item.label })
            })
            return opts
        },
        opcoesVencido() {
            return [
                { value: '', label: 'Indiferente' },
                { value: 'true', label: 'Sim' },
                { value: 'false', label: 'Não' }
            ]
        },
        temFiltrosAtivos() {
            const d = this.controle.dados
            return !!(
                d.filtroVencimento ||
                (d.campoBusca || '').trim() ||
                d.campoCnpj ||
                d.campoCentroCusto ||
                d.campoTipoExame ||
                d.campoVencido !== ''
            )
        },
        resumo() {
            const total = this.dados.length
            let vencidos = 0
            let aVencer = 0
            let emDia = 0
            this.dados.forEach((item) => {
                const cat = this.categoriaItem(item)
                if (cat === 'vencidos') vencidos++
                else if (cat === 'avencer') aVencer++
                else emDia++
            })
            return { total, vencidos, aVencer, emDia }
        },
        labelFiltroSituacao() {
            const map = {
                todos: 'Todos',
                vencidos: 'Vencidos',
                avencer: 'A vencer',
                emdia: 'Em dia'
            }
            return map[this.filtroSituacao] || 'Todos'
        },
        dadosFiltrados() {
            let lista = [...this.dados]
            if (this.filtroSituacao !== 'todos') {
                lista = lista.filter((item) => this.categoriaItem(item) === this.filtroSituacao)
            }
            const campo = this.ordenacaoCampo
            const dir = this.ordenacaoDir === 'asc' ? 1 : -1
            lista.sort((a, b) => {
                let va = a[campo]
                let vb = b[campo]
                if (campo === 'dias_vencer') {
                    return (Number(va) - Number(vb)) * dir
                }
                if (campo === 'data_aso' || campo === 'data_vencimento') {
                    const da = this.dataParaSort(va)
                    const db = this.dataParaSort(vb)
                    return (da - db) * dir
                }
                va = (va ?? '').toString().toLowerCase()
                vb = (vb ?? '').toString().toLowerCase()
                if (va < vb) return -1 * dir
                if (va > vb) return 1 * dir
                return 0
            })
            return lista
        },
        totalPaginas() {
            return Math.max(1, Math.ceil(this.dadosFiltrados.length / this.porPagina))
        },
        dadosPaginados() {
            const inicio = (this.paginaAtual - 1) * this.porPagina
            return this.dadosFiltrados.slice(inicio, inicio + this.porPagina)
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
            this.dados.forEach((item) => {
                const key = item.exame_tipo || '—'
                if (!mapa[key]) mapa[key] = { vencidos: 0, aVencer: 0, emDia: 0 }
                const dias = Number(item.dias_vencer)
                if (dias < 0) mapa[key].vencidos++
                else if (item.pintar || dias <= Number(this.periodo_vencimento_numero || 0)) mapa[key].aVencer++
                else mapa[key].emDia++
            })
            const labels = Object.keys(mapa).sort((a, b) => {
                const ta = mapa[a].vencidos + mapa[a].aVencer + mapa[a].emDia
                const tb = mapa[b].vencidos + mapa[b].aVencer + mapa[b].emDia
                return tb - ta
            })
            return {
                labels,
                datasets: [
                    {
                        label: 'Vencidos',
                        data: labels.map((l) => mapa[l].vencidos),
                        backgroundColor: '#c82333'
                    },
                    {
                        label: 'A vencer',
                        data: labels.map((l) => mapa[l].aVencer),
                        backgroundColor: '#e0a800'
                    },
                    {
                        label: 'Em dia',
                        data: labels.map((l) => mapa[l].emDia),
                        backgroundColor: '#218838'
                    }
                ]
            }
        },
        graficoCentros() {
            const mapa = {}
            this.dados.forEach((item) => {
                const key = item.emp_centro_custo || 'Não informado'
                if (!mapa[key]) mapa[key] = { vencidos: 0, aVencer: 0, emDia: 0 }
                const dias = Number(item.dias_vencer)
                if (dias < 0) mapa[key].vencidos++
                else if (item.pintar || dias <= Number(this.periodo_vencimento_numero || 0)) mapa[key].aVencer++
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
                    {
                        label: 'Vencidos',
                        data: ordenado.map((i) => i.vencidos),
                        backgroundColor: '#c82333'
                    },
                    {
                        label: 'A vencer',
                        data: ordenado.map((i) => i.aVencer),
                        backgroundColor: '#e0a800'
                    },
                    {
                        label: 'Em dia',
                        data: ordenado.map((i) => i.emDia),
                        backgroundColor: '#218838'
                    }
                ]
            }
        }
    },
    methods: {
        categoriaItem(item) {
            const dias = Number(item.dias_vencer)
            if (dias < 0) return 'vencidos'
            if (item.pintar || dias <= Number(this.periodo_vencimento_numero || 0)) return 'avencer'
            return 'emdia'
        },
        setFiltroSituacao(valor) {
            this.filtroSituacao = this.filtroSituacao === valor && valor !== 'todos' ? 'todos' : valor
            this.paginaAtual = 1
            this.abaAtiva = 'lista'
        },
        ordenarPor(campo) {
            if (this.ordenacaoCampo === campo) {
                this.ordenacaoDir = this.ordenacaoDir === 'asc' ? 'desc' : 'asc'
            } else {
                this.ordenacaoCampo = campo
                this.ordenacaoDir = campo === 'dias_vencer' ? 'asc' : 'asc'
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
        pillClasse(vencimento) {
            const cat = this.categoriaItem(vencimento)
            if (cat === 'vencidos') return 'aso-pill--vencido'
            if (cat === 'avencer') return Number(vencimento.dias_vencer) === 0 ? 'aso-pill--hoje' : 'aso-pill--alerta'
            return 'aso-pill--ok'
        },
        abrirGraficos() {
            this.abaAtiva = 'graficos'
        },
        onBuscaInput() {
            if (this.buscaTimer) clearTimeout(this.buscaTimer)
            this.buscaTimer = setTimeout(() => this.buscarDados(), 400)
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'aso-filtro-cnpj': 'comboFiltroCnpj',
                'aso-filtro-cc': 'comboFiltroCc',
                'aso-filtro-tipo': 'comboFiltroTipo',
                'aso-filtro-vencido': 'comboFiltroVencido'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        async onPeriodoChange() {
            this.syncCampoVencimento()
            await this.buscarDados()
        },
        syncCampoVencimento() {
            const d = this.controle.dados
            if (d.filtroVencimento && d.dataInicioVencimento && d.dataFimVencimento) {
                d.campoVencimento = `${this.formatarDataFiltro(d.dataInicioVencimento)} até ${this.formatarDataFiltro(d.dataFimVencimento)}`
            } else {
                d.campoVencimento = ''
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
        async onSelectCnpj() {
            this.controle.dados.campoCentroCusto = ''
            await this.buscarDados()
        },
        async limparFiltros() {
            this.controle.dados = {
                campoBusca: '',
                campoTipoExame: '',
                campoVencido: '',
                filtroVencimento: false,
                dataInicioVencimento: '',
                dataFimVencimento: '',
                campoVencimento: '',
                campoCnpj: '',
                campoCentroCusto: ''
            }
            this.filtroSituacao = 'todos'
            this.paginaAtual = 1
            await this.buscarDados()
        },
        linhaClasse(vencimento) {
            const cat = this.categoriaItem(vencimento)
            if (cat === 'vencidos') return 'aso-row--vencido'
            if (cat === 'avencer') return 'aso-row--alerta'
            return ''
        },
        async gerarArquivoXls() {
            const XLSX = require('@e965/xlsx')
            const dataHoraAtual = new Date()
                .toLocaleString('en-US', {
                    timeZone: 'America/Sao_Paulo',
                    hour12: false
                })
                .replace(/\/|,|\s|:/g, '_')
                .replace(/\//g, '-')

            const filename = `relatorio_asos_${AUTENTICADO.empresa_id}_${AUTENTICADO.user_id}_${dataHoraAtual}.xlsx`
            const wb = XLSX.utils.book_new()
            const ws = XLSX.utils.json_to_sheet([])

            XLSX.utils.sheet_add_aoa(
                ws,
                [
                    [
                        'Nome',
                        'Cargo',
                        'CNPJ da Empresa',
                        'Empresa',
                        'Centro de Custo',
                        'Data da Admissão',
                        'Tipo do Exame',
                        'Data do Aso',
                        'Vencimento ASO',
                        'Dias',
                        'Status'
                    ]
                ],
                { origin: 0 }
            )

            this.dados.forEach((jsonData) => {
                const dias = Number(jsonData.dias_vencer)
                XLSX.utils.sheet_add_aoa(
                    ws,
                    [
                        [
                            jsonData.colaborador,
                            jsonData.cargo,
                            jsonData.emp_cnpj,
                            jsonData.emp_nome_fantasia,
                            jsonData.emp_centro_custo,
                            this.formatarDataBr(jsonData.data_admissao),
                            jsonData.exame_tipo,
                            this.formatarDataBr(jsonData.data_aso),
                            this.formatarDataBr(jsonData.data_vencimento),
                            dias,
                            dias < 0 ? 'VENCIDO' : 'A VENCER'
                        ]
                    ],
                    { origin: -1 }
                )
            })

            XLSX.utils.book_append_sheet(wb, ws, 'planilha')
            XLSX.writeFile(wb, filename)
        },
        async buscarDados() {
            this.syncCampoVencimento()
            this.preload = true
            try {
                const res = await axios.post(`${URL_ADMIN}/relatorios/vencimentoasos`, this.controle.dados)
                this.dados = res.data.dados || []
                this.periodo_vencimento_numero = res.data.periodo_vencimento_numero
                this.periodo_vencimento_extenso = res.data.periodo_vencimento_extenso
                this.lista_ccs = res.data.cc
                this.paginaAtual = 1
            } finally {
                this.preload = false
            }
        },
        async tiposExames() {
            try {
                const response = await axios.get(`${URL_ADMIN}/relatorios/tipos-exames`)
                this.listaTiposExame = response.data || []
            } catch (err) {
                this.listaTiposExame = []
            }
        },
        formatarDataBr(data) {
            if (!data || data === 'Não informada') return data || '—'
            if (typeof data === 'string' && data.includes('/')) return data
            const m = moment(data)
            return m.isValid() ? m.format('DD/MM/YYYY') : data
        },
        textoSituacao(vencimento) {
            const dias = Number(vencimento.dias_vencer)
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
.aso-vencimento :deep(.mybp-filtros-form) {
    align-items: start;
    row-gap: 0;
}

.aso-vencimento :deep(.mybp-filtros-form > [class*='col-']) {
    margin-bottom: var(--mybp-fc-gap, 0.45rem);
}

.aso-filtro-alerta {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-left: 0.25rem;
    font-size: 0.75rem;
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

.aso-kpis .aso-kpi {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-left: 4px solid #6c757d;
    border-radius: 4px;
    padding: 0.75rem 1rem;
    height: 100%;
    width: 100%;
    text-align: left;
}

.aso-kpi--btn {
    cursor: pointer;
    transition: box-shadow 0.15s ease, border-color 0.15s ease;
}

.aso-kpi--btn:hover,
.aso-kpi--active {
    box-shadow: 0 0 0 2px rgba(101, 50, 50, 0.18);
    border-color: #653232;
}

.aso-kpi--danger {
    border-left-color: #c82333;
}

.aso-kpi--warn {
    border-left-color: #e0a800;
}

.aso-kpi--ok {
    border-left-color: #218838;
}

.aso-kpi__label {
    font-size: 0.78rem;
    color: #6c757d;
    margin-bottom: 0.15rem;
}

.aso-kpi__value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
    color: #212529;
}

.aso-legenda {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.55rem 0.9rem;
    font-size: 0.82rem;
    color: #495057;
}

.aso-legenda__item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.aso-legenda__swatch {
    width: 0.7rem;
    height: 0.7rem;
    border-radius: 3px;
    flex-shrink: 0;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.aso-legenda__swatch--vencido {
    background: #f8d7da;
    border-color: #f1b0b7;
}

.aso-legenda__swatch--alerta {
    background: #fff3cd;
    border-color: #ffe08a;
}

.aso-legenda__swatch--ok {
    background: #d4edda;
    border-color: #b1dfbb;
}

.aso-legenda__sep {
    color: #adb5bd;
}

.aso-legenda__count {
    color: #6c757d;
}

.aso-page-size {
    width: auto;
    min-width: 72px;
}

.aso-table-wrap {
    max-height: 68vh;
    overflow: auto;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.aso-table {
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: 0;
}

.aso-table thead th {
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

.aso-th-sort {
    cursor: pointer;
    user-select: none;
}

.aso-th-sort:hover {
    color: #653232;
}

.aso-th-sort i {
    margin-left: 4px;
    font-size: 0.7rem;
}

.aso-table tbody td {
    vertical-align: middle;
    font-size: 0.86rem;
    padding: 0.55rem 0.75rem;
    border-top: 1px solid #eceff1;
    background: #fff;
}

.aso-col-sticky {
    position: sticky;
    left: 0;
    z-index: 1;
    min-width: 220px;
    max-width: 280px;
    background: #fff;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.04);
}

.aso-table thead .aso-col-sticky {
    z-index: 4;
    background: #f1f3f5;
}

.aso-col-pessoa {
    text-align: left !important;
}

.aso-nome {
    font-weight: 700;
    color: #212529;
    line-height: 1.25;
}

.aso-meta {
    font-size: 0.75rem;
    color: #6c757d;
    line-height: 1.3;
}

.aso-lotacao-nome {
    font-weight: 600;
    line-height: 1.25;
}

.aso-tipo {
    display: inline-block;
    max-width: 140px;
    white-space: normal;
    line-height: 1.2;
}

.aso-venc-data {
    font-weight: 700;
    color: #343a40;
}

.aso-col-situacao {
    min-width: 150px;
}

.aso-pill {
    display: inline-block;
    padding: 0.28rem 0.55rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
}

.aso-pill--vencido {
    background: #f8d7da;
    color: #721c24;
}

.aso-pill--alerta {
    background: #fff3cd;
    color: #856404;
}

.aso-pill--hoje {
    background: #d1ecf1;
    color: #0c5460;
}

.aso-pill--ok {
    background: #d4edda;
    color: #155724;
}

.aso-row--vencido td {
    background-color: #fff5f5 !important;
}

.aso-row--vencido .aso-col-sticky {
    background-color: #fff5f5 !important;
}

.aso-row--alerta td {
    background-color: #fffbeb !important;
}

.aso-row--alerta .aso-col-sticky {
    background-color: #fffbeb !important;
}

.aso-row--vencido:hover td,
.aso-row--vencido:hover .aso-col-sticky,
.aso-row--alerta:hover td,
.aso-row--alerta:hover .aso-col-sticky,
.aso-table tbody tr:hover td,
.aso-table tbody tr:hover .aso-col-sticky {
    filter: brightness(0.985);
}

.aso-chart-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    padding: 1rem;
    height: 100%;
}

.aso-paginacao .btn {
    min-width: 36px;
}

@media (max-width: 767.98px) {
    .aso-col-sticky {
        position: static;
        box-shadow: none;
        max-width: none;
    }
}
</style>
