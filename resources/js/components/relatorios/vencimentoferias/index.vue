<template>
    <div>
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
                        <label class="mybp-label" for="venc-ferias-busca">Buscar</label>
                        <input
                            id="venc-ferias-busca"
                            type="text"
                            placeholder="Buscar por nome"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="preload"
                            v-model="filtrar.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="venc-ferias-cargo">Cargo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCargo"
                                input-id="venc-ferias-cargo"
                                instance-id="venc-ferias-cargo"
                                :disabled="preload"
                                :options="opcoesCargo"
                                placeholder-blur="Todos"
                                empty-message="Nenhum cargo encontrado."
                                :max-results="80"
                                v-model="filtrar.campoCargo"
                                @opening="fecharOutrosComboboxes('venc-ferias-cargo')"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="venc-ferias-situacao">Situação</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroSituacao"
                                input-id="venc-ferias-situacao"
                                instance-id="venc-ferias-situacao"
                                :disabled="preload"
                                :options="opcoesSituacao"
                                placeholder-blur="Todas"
                                empty-message="Nenhuma situação encontrada."
                                :max-results="20"
                                v-model="filtrar.campoSituacao"
                                @opening="fecharOutrosComboboxes('venc-ferias-situacao')"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="venc-ferias-periodo">Período</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPeriodo"
                                input-id="venc-ferias-periodo"
                                instance-id="venc-ferias-periodo"
                                :disabled="preload"
                                :options="opcoesPeriodo"
                                placeholder-blur="Todos os períodos"
                                empty-message="Nenhum período encontrado."
                                :max-results="20"
                                v-model="filtrar.campoPeriodoVencido"
                                @opening="fecharOutrosComboboxes('venc-ferias-periodo')"
                                @select="buscarDados"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="venc-ferias-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                input-id="venc-ferias-cc"
                                instance-id="venc-ferias-cc"
                                :disabled="preload"
                                :options="opcoesCentroCusto"
                                placeholder-blur="Todos"
                                empty-message="Nenhum centro encontrado."
                                :max-results="80"
                                v-model="filtrar.campoCentroCusto"
                                @opening="fecharOutrosComboboxes('venc-ferias-cc')"
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
            </template>
        </FiltroListagem>

            <preload v-if="preload" />
            <template v-if="!preload">
                <div class="alert alert-warning" v-show="!dados.length"><i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado</div>

                <div v-for="(item, index) in dados" :key="index" class="mb-3" v-show="dados.length">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="mt-4 table bg-white table-bordered">
                                <thead>
                                    <tr class="text-center">
                                        <th
                                            :rowspan="item.todos_periodos.length + 3"
                                            :class="item.pintar"
                                            style="display: table-cell; vertical-align: middle; text-align: center"
                                        >
                                            {{ index + 1 }}
                                        </th>
                                        <th colspan="6">
                                            {{ item.nome }} ({{ item.cargo }}) <br />(Admitido em: {{ item.data_admissao }}) - (Centro de Custo:
                                            {{ item.centro_custo }}) <br /><span v-if="item.dias_atraso > 0">Férias vencidas {{ item.tempo_atrasado }}</span>
                                        </th>
                                    </tr>

                                    <tr>
                                        <th style="text-align: center">Período Aquisitivo</th>
                                        <th style="text-align: center">Situação</th>
                                        <th style="text-align: center">Saldo</th>
                                        <th style="text-align: center">Última Atualização</th>
                                    </tr>

                                    <tr v-for="(periodo, ind) in item.todos_periodos" :key="ind">
                                        <th style="text-align: center; vertical-align: middle">
                                            {{ periodo.periodo_aquisitivo }}
                                        </th>
                                        <th style="text-align: center; vertical-align: middle">
                                            <div class="badge font-size-12 p-2 text-cappitalize" :class="periodo.colorir">
                                                {{ periodo.status_ferias }}
                                            </div>
                                            <span v-show="periodo.tempo_atrasado">
                                                <br />
                                                Vencida à {{ periodo.tempo_atrasado }}
                                            </span>
                                            <span v-if="periodo.tem_tb_ferias">
                                                <br />
                                                ({{ periodo.data_saida }} à {{ periodo.data_retorno }})
                                            </span>
                                        </th>
                                        <th style="text-align: center; vertical-align: middle">
                                            {{ periodo.total_avos ? periodo.total_avos : 0 }}
                                        </th>
                                        <th style="text-align: center; vertical-align: middle">
                                            {{ periodo.ultima_atualizacao ? periodo.ultima_atualizacao : 'Sem atualização' }}
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
    </div>
</template>
<script>
import ExportacaoMixin from '../../../mixins/Exportacoes'
import XLSX from '@e965/xlsx'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'

export default {
    mixins: [ExportacaoMixin],
    components: { ComboboxAutoComplete, FiltroListagem },
    data() {
        return {
            preload: false,
            dados: [],
            lista_cargos: [],
            lista_funcao: [],
            lista_centro_custos: [],
            filtro: [],
            periodo: '',
            urlExportacao: `${URL_ADMIN}/relatorios/vencimento-ferias/export-excel`,
            filtrar: {
                periodo: '',
                status_ferias: '',
                campoBusca: '',
                campoCargo: '',
                campoSituacao: '',
                campoCentroCusto: '',
                campoPeriodoVencido: ''
            }
        }
    },
    async mounted() {
        await this.periodosAquisitivosList()
        await this.buscarDados()
    },
    computed: {
        paramsExport() {
            return this.filtrar
        },
        temFiltrosAtivos() {
            const f = this.filtrar
            return !!(f.campoBusca || f.campoCargo || f.campoSituacao || f.campoCentroCusto || f.campoPeriodoVencido)
        },
        opcoesCargo() {
            const opts = [{ value: '', label: 'Todos' }]
            ;(this.lista_cargos || []).forEach((item) => opts.push({ value: item, label: item }))
            return opts
        },
        opcoesSituacao() {
            return [
                { value: '', label: 'Todas' },
                { value: 'Saldo insuficiente', label: 'Saldo insuficiente' },
                { value: 'Solicitada', label: 'Solicitada' },
                { value: 'Disponivel', label: 'Disponivel' },
                { value: 'Gozada', label: 'Gozada' }
            ]
        },
        opcoesPeriodo() {
            return [
                { value: '', label: 'Todos os períodos' },
                { value: 'apartirdoperiodoconcessivel', label: 'Do período concessível à 1 ano e 6 meses' },
                { value: '1anoseismesesate1anoe8meses', label: 'De 1 ano e 6 meses até 1 ano e 8 meses' },
                { value: '1anoe8meseisesuperior', label: 'Maior que 1 ano e 8 meses' }
            ]
        },
        opcoesCentroCusto() {
            const opts = [{ value: '', label: 'Todos' }]
            ;(this.lista_centro_custos || []).forEach((item) => opts.push({ value: item, label: item }))
            return opts
        }
    },
    methods: {
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'venc-ferias-cargo': 'comboFiltroCargo',
                'venc-ferias-situacao': 'comboFiltroSituacao',
                'venc-ferias-periodo': 'comboFiltroPeriodo',
                'venc-ferias-cc': 'comboFiltroCc'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        async limparFiltros() {
            this.filtrar.campoBusca = ''
            this.filtrar.campoCargo = ''
            this.filtrar.campoSituacao = ''
            this.filtrar.campoCentroCusto = ''
            this.filtrar.campoPeriodoVencido = ''
            await this.buscarDados()
        },
        async gerarArquivoXls() {
            const dataHoraAtual = new Date()
                .toLocaleString('en-US', {
                    timeZone: 'America/Sao_Paulo',
                    hour12: false
                })
                .replace(/\/|,|\s|:/g, '_')
                .replace(/\//g, '-')

            const filename = `relatorio_vencimento_ferias_${AUTENTICADO.empresa_id}_${AUTENTICADO.user_id}_${dataHoraAtual}.xlsx`
            const jsonDataArray = this.dados

            const wb = XLSX.utils.book_new()
            const ws = XLSX.utils.json_to_sheet([])

            let cabecalho = [
                'Nome do colaborador',
                'Data de admissão',
                'Cargo',
                // "CNPJ da Empresa",
                // "Empresa",
                'Centro de Custo',
                'Período aquisitivo',
                'Quantidade de dias de atraso',
                'Tempo atrasado',
                'Situação',
                'Data saida',
                'Data retorno',
                'Saldo',
                'Última atualização'
            ]

            XLSX.utils.sheet_add_aoa(ws, [cabecalho], { origin: 0 })

            jsonDataArray.forEach(function (jsonData) {
                jsonData.todos_periodos.forEach(function (periodo) {
                    XLSX.utils.sheet_add_aoa(
                        ws,
                        [
                            [
                                jsonData.nome,
                                jsonData.data_admissao,
                                jsonData.cargo,
                                // jsonData.emp_cnpj,
                                // jsonData.emp_nome_fantasia,
                                // jsonData.emp_centro_custo,
                                jsonData.centro_custo,
                                periodo.periodo_aquisitivo,
                                periodo.dias_atraso,
                                periodo.dias_atraso > 0 ? periodo.tempo_atrasado : '',
                                periodo.status_ferias,
                                periodo.data_saida ? periodo.data_saida : '---',
                                periodo.data_retorno ? periodo.data_retorno : '---',
                                periodo.total_avos,
                                periodo.ultima_atualizacao
                            ]
                        ],
                        { origin: -1 }
                    )
                })
            })

            XLSX.utils.book_append_sheet(wb, ws, 'planilha')

            XLSX.writeFile(wb, filename)
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
            this.preload = true
            try {
                const { data } = await axios.post(`${URL_ADMIN}/relatorios/vencimento-ferias`, this.filtrar)
                this.dados = data.result || []
                this.lista_cargos = data.lista_cargos || []
                this.lista_funcao = data.lista_funcao || []
                this.lista_centro_custos = data.lista_centro_custos || []
            } catch (err) {
                this.dados = []
                this.lista_cargos = []
                this.lista_funcao = []
                this.lista_centro_custos = []
                const msg = err.response?.data?.message || err.message || 'Erro ao carregar relatório.'
                if (typeof toastr !== 'undefined') toastr.error(msg); else alert(msg)
            } finally {
                this.preload = false
            }
        }
    }
}
</script>
