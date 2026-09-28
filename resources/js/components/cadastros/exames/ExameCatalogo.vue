<template>
    <div id="componenteExameCatalogo">
        <div class="alert alert-light border mb-3">
            <strong>Lista de exames</strong>
            <p class="mb-0 text-muted small mt-1">
                Cadastre os <em>nomes</em> dos exames (ex.: Audiometria, Hemograma).
                No <strong>Controle de Exames</strong> / Pré-admissão, ao escolher o tipo,
                esses itens aparecem para marcar no encaminhamento e saem na ficha PDF.
            </p>
        </div>

        <modal id="janelaExameCatalogo" :titulo="tituloJanela" :size="70" :mostrar-botao-fechar-no-rodape="false" ref="modalCatalogo">
            <template #conteudo>
                <preload v-show="salvando" class="text-center"></preload>
                <form v-if="!salvando" @submit.prevent>
                    <p class="mybp-campo-obrigatorio-legenda mb-3">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <label class="mybp-label">Nome do exame <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" v-model="form.label" placeholder="Ex: Audiometria" />
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="mybp-label">Restringir a um tipo</label>
                                <select class="form-control form-control-sm" v-model="form.exame_tipo_id">
                                    <option :value="null">Todos os tipos</option>
                                    <option v-for="t in tipos" :key="t.id" :value="t.id">{{ t.label }}</option>
                                </select>
                                <small class="text-muted">Opcional. Deixe em “Todos” se vale para qualquer tipo.</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="exame-cat-ativo" v-model="form.ativo" />
                                <label class="custom-control-label" for="exame-cat-ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                            </div>
                        </div>
                    </div>
                </form>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-secondary" @click="$refs.modalCatalogo?.fecharModal?.()">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar
                </button>
            </template>
        </modal>

        <filtro-listagem @submit="atualizar" :mostrar-limpar-filtros="temFiltrosAtivos" :desabilitado="controle.carregando" @limpar="limparFiltros">
            <template #filtros>
                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label">Buscar</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Buscar por nome ou ID" v-model="controle.dados.campoBusca" />
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                v-model="controle.dados.campoStatus"
                                :options="statusOpcoes"
                                placeholder-blur="Todos os status"
                                @select="atualizar"
                            />
                        </div>
                    </div>
                </div>
            </template>
            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-sync"></i> Atualizar</button>
                <button type="button" class="btn btn-sm btn-secondary" @click="abrirNovo"><i class="fa fa-plus"></i> Cadastrar</button>
            </template>
        </filtro-listagem>

        <preload class="text-center" v-if="controle.carregando"></preload>
        <div class="mybp-cards-lista" v-show="!controle.carregando">
            <div class="alert alert-warning" v-if="lista.length === 0">Nenhum exame cadastrado nesta lista</div>
            <div class="mybp-card" v-for="item in lista" :key="item.id">
                <div class="mybp-card-header-row">
                    <div class="mybp-card-left">
                        <span class="mybp-badge-id">#{{ item.id }}</span>
                        <div class="mybp-card-titulo">
                            <strong>{{ item.label }}</strong>
                            <span class="badge ml-2" :class="item.ativo ? 'badge-success' : 'badge-secondary'">
                                {{ item.ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>
                    <div class="mybp-card-right">
                        <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                            <a
                                class="mybp-btn-acoes-compact"
                                href="#"
                                role="button"
                                @click.prevent.stop="toggleDropdown(item.id)"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div
                                class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                :class="{ show: isDropdownOpen(item.id) }"
                                @click="fecharDropdown"
                            >
                                <a class="dropdown-item" href="javascript://" @click.prevent="editar(item)">
                                    <i class="fa fa-edit mr-1"></i> Editar
                                </a>
                                <a class="dropdown-item" href="javascript://" @click.prevent="ativaDesativa(item)">
                                    <i class="fa fa-power-off mr-1"></i>
                                    {{ item.ativo ? 'Desativar' : 'Ativar' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <controle-paginacao
                class="d-flex justify-content-center mt-3"
                ref="paginacao"
                :url="urlPaginacao"
                :por-pagina="controle.dados.pages"
                :dados="controle.dados"
                @carregou="onCarregou"
                @carregando="onCarregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import { temFiltrosPreenchidos, limparFiltrosListagem } from '../../../utils/listagemQueryParams'

const CAMPOS = ['campoBusca', 'campoStatus']

export default defineComponent({
    name: 'ExameCatalogo',
    components: { FiltroListagem, ComboboxAutoComplete },
    data() {
        return {
            tituloJanela: 'Exame clínico',
            salvando: false,
            lista: [],
            tipos: [],
            dropdownAbertoKey: null,
            form: { id: null, label: '', exame_tipo_id: null, ativo: true },
            urlPaginacao: `${URL_ADMIN}/cadastro/exame-catalogo/atualizar`,
            controle: {
                carregando: false,
                dados: { campoBusca: '', campoStatus: '', pages: 20 }
            },
            statusOpcoes: [
                { value: '', label: 'Todos os status' },
                { value: 'true', label: 'Apenas ativos' },
                { value: 'false', label: 'Apenas inativos' }
            ]
        }
    },
    computed: {
        temFiltrosAtivos() {
            return temFiltrosPreenchidos(this.controle.dados, CAMPOS)
        }
    },
    async mounted() {
        document.addEventListener('click', this.fecharDropdown)
        try {
            const { data } = await axios.get(`${URL_ADMIN}/cadastro/exame-tipos/ativos`)
            this.tipos = data || []
        } catch (e) {
            this.tipos = []
        }
        this.$nextTick(() => this.atualizar())
    },
    beforeUnmount() {
        document.removeEventListener('click', this.fecharDropdown)
        document.body.classList.remove('modal-open')
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove())
    },
    methods: {
        toggleDropdown(id) {
            const key = `exame-catalogo:${id}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoKey === `exame-catalogo:${id}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        atualizar() {
            if (this.$refs.paginacao) {
                this.$refs.paginacao.atual = 1
                this.$refs.paginacao.buscar?.()
            }
        },
        limparFiltros() {
            limparFiltrosListagem(this.controle.dados, CAMPOS)
            this.atualizar()
        },
        onCarregou(payload) {
            this.lista = payload?.items || payload?.dados?.items || []
            this.controle.carregando = false
        },
        onCarregando() {
            this.controle.carregando = true
        },
        abrirNovo() {
            this.fecharDropdown()
            this.form = { id: null, label: '', exame_tipo_id: null, ativo: true }
            this.tituloJanela = 'Novo exame clínico'
            this.$refs.modalCatalogo?.abrirModal?.()
        },
        editar(item) {
            this.fecharDropdown()
            this.form = { ...item }
            this.tituloJanela = 'Editar exame clínico'
            this.$refs.modalCatalogo?.abrirModal?.()
        },
        async salvar() {
            if (!this.form.label?.trim()) {
                return toastr.warning('Informe o nome.')
            }
            this.salvando = true
            try {
                if (this.form.id) {
                    await axios.put(`${URL_ADMIN}/cadastro/exame-catalogo/${this.form.id}`, this.form)
                } else {
                    await axios.post(`${URL_ADMIN}/cadastro/exame-catalogo`, this.form)
                }
                toastr.success('Exame salvo.')
                this.$refs.modalCatalogo?.fecharModal?.()
                this.atualizar()
            } catch (e) {
                toastr.error(e?.response?.data?.msg || 'Erro ao salvar.')
            } finally {
                this.salvando = false
            }
        },
        async ativaDesativa(item) {
            this.fecharDropdown()
            try {
                await axios.put(`${URL_ADMIN}/cadastro/exame-catalogo/${item.id}/ativa-desativa`)
                toastr.success('Status atualizado.')
                this.atualizar()
            } catch (e) {
                toastr.error(e?.response?.data?.msg || 'Erro ao alterar status.')
            }
        }
    }
})
</script>
