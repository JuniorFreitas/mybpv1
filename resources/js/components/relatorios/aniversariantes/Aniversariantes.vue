<template>
    <div id="componenteAniversariante">
        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            :mostrar-limpar-filtros="false"
            :desabilitado="controle.carregando"
            @submit="atualizar"
        >
            <template #filtros>
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="aniversariantes-mes">Aniversariantes do mês</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroMes"
                                input-id="aniversariantes-mes"
                                instance-id="aniversariantes-mes"
                                :disabled="controle.carregando"
                                :options="opcoesMeses"
                                placeholder-blur="Selecione o mês"
                                empty-message="Nenhum mês encontrado."
                                :max-results="12"
                                v-model="campoMesCombo"
                                @select="atualizar"
                            />
                        </div>
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
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </template>
        </FiltroListagem>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <h4 class="text-center mt-3">Aniversariantes de {{ listaMeses[controle.dados.campoMes] }}</h4>
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Nome</td>
                            <td class="text-center">Data</td>
                            <td class="text-center">Email</td>
                            <td class="text-center">WhatsApp</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="aniversariantes in lista" :key="aniversariantes.id">
                            <td class="text-center"><i class="fa fa-birthday-cake mr-2" v-if="aniversariantes.hoje"></i>{{ aniversariantes.nome }}</td>
                            <td class="text-center">{{ aniversariantes.aniversario }}</td>
                            <td class="text-center">{{ aniversariantes.email }}</td>
                            <td class="text-center">{{ aniversariantes.whatsapp }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import ExportacaoMixin from '../../../mixins/Exportacoes'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'

export default {
    mixins: [ExportacaoMixin],
    components: { ComboboxAutoComplete, FiltroListagem },
    props: {
        filtro: {
            type: Boolean,
            required: false,
            default: true
        }
    },
    data() {
        const dataAtual = new Date()
        const mesAtual = dataAtual.getMonth() + 1

        return {
            preload: false,
            lista: [],
            listaMeses: {},
            urlPaginacao: `${URL_ADMIN}/relatorios/aniversariantes/atualizar`,
            urlPdf: `${URL_ADMIN}/relatorios/aniversariantes/pdf`,
            urlExportacao: `${URL_ADMIN}/relatorios/aniversariantes/export`,
            controle: {
                carregando: false,
                dados: {
                    campoMes: mesAtual
                }
            }
        }
    },
    mounted() {
        this.atualizar()
    },
    computed: {
        paramsExport() {
            return this.controle.dados
        },
        campoMesCombo: {
            get() {
                return this.controle.dados.campoMes === '' || this.controle.dados.campoMes == null
                    ? ''
                    : String(this.controle.dados.campoMes)
            },
            set(val) {
                this.controle.dados.campoMes = val === '' || val == null ? '' : Number(val)
            }
        },
        opcoesMeses() {
            const meses = this.listaMeses || {}
            return Object.keys(meses)
                .map((key) => ({ value: String(key), label: meses[key] }))
                .sort((a, b) => Number(a.value) - Number(b.value))
        }
    },
    methods: {
        atualizar() {
            this.controle.carregando = true
            axios
                .post(this.urlPaginacao, this.controle.dados)
                .then(({ data }) => {
                    this.lista = data.dados.funcionarios
                    this.listaMeses = data.dados.lista_meses
                    this.controle.carregando = false
                })
                .catch(() => {
                    this.controle.carregando = false
                })
        }
    }
}
</script>
