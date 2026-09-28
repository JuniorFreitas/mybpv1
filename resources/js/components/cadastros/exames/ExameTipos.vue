<template>
    <div id="componenteExameTipos">
        <modal id="janelaExameTipo" :titulo="tituloJanela" :size="70" :mostrar-botao-fechar-no-rodape="false" ref="modalTipo">
            <template #conteudo>
                <preload v-show="salvando" class="text-center"></preload>
                <form v-if="!salvando" @submit.prevent>
                    <p class="mybp-campo-obrigatorio-legenda mb-3">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>
                    <div class="alert alert-info" v-if="form.empresa_id == null && form.id">
                        Tipo global do sistema. Ao salvar, será criado um tipo da sua empresa (sem alterar o global).
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <label class="mybp-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" v-model="form.label" placeholder="Ex: Admissional" />
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="mybp-label">Ordem</label>
                                <input type="number" min="0" class="form-control form-control-sm" v-model.number="form.ordem" />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="exame-tipo-ativo" v-model="form.ativo" />
                                <label class="custom-control-label" for="exame-tipo-ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                            </div>
                        </div>
                    </div>
                </form>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-secondary" @click="$refs.modalTipo?.fecharModal?.()">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar
                </button>
            </template>
        </modal>

        <filtro-listagem
            @submit="atualizar"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="controle.carregando"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div class="col-12 col-lg-4">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label">Buscar</label>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Buscar por nome ou ID"
                            v-model="controle.dados.campoBusca"
                            :disabled="controle.carregando"
                        />
                    </div>
                </div>
                <div class="col-12 col-lg-4">
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
                <div class="col-12 col-lg-4">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label">Escopo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                v-model="controle.dados.campoEscopo"
                                :options="escopoOpcoes"
                                placeholder-blur="Todos os escopos"
                                @select="atualizar"
                            />
                        </div>
                    </div>
                </div>
            </template>
            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                    <i class="fa fa-sync"></i> Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-secondary" @click="abrirNovo">
                    <i class="fa fa-plus"></i> Cadastrar
                </button>
            </template>
        </filtro-listagem>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div class="mybp-cards-lista" v-show="!controle.carregando">
            <div class="alert alert-warning" v-if="lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum tipo encontrado
            </div>
            <div class="mybp-card" v-for="item in lista" :key="item.id">
                <div class="mybp-card-header-row">
                    <div class="mybp-card-left">
                        <span class="mybp-badge-id">#{{ item.id }}</span>
                        <div class="mybp-card-titulo">
                            <strong>{{ item.label }}</strong>
                            <span class="badge ml-2" :class="item.ativo ? 'badge-success' : 'badge-secondary'">
                                {{ item.ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                            <span class="badge badge-info ml-1">{{ item.empresa_id ? 'Empresa' : 'Global' }}</span>
                        </div>
                    </div>
                    <div class="mybp-card-right">
                        <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                            <a
                                class="mybp-btn-acoes-compact"
                                href="#"
                                role="button"
                                aria-haspopup="true"
                                :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
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
                                <a
                                    class="dropdown-item"
                                    href="javascript://"
                                    v-if="item.empresa_id"
                                    @click.prevent="ativaDesativa(item)"
                                >
                                    <i class="fa fa-power-off mr-1"></i>
                                    {{ item.ativo ? 'Desativar' : 'Ativar' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mybp-card-body">
                    <small class="text-muted">Ordem: {{ item.ordem ?? 0 }}</small>
                    <small class="text-muted ml-3" v-if="item.formulario_encaminhamento_id">
                        Form. encaminhamento: #{{ item.formulario_encaminhamento_id }}
                    </small>
                    <small class="text-muted ml-3" v-if="item.formulario_resultado_id">
                        Form. resultado: #{{ item.formulario_resultado_id }}
                    </small>
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
import {
    lerFiltrosDaUrl,
    sincronizarFiltrosNaUrl,
    temFiltrosPreenchidos,
    limparFiltrosListagem
} from '../../../utils/listagemQueryParams'

const CAMPOS = ['campoBusca', 'campoStatus', 'campoEscopo']

export default defineComponent({
    name: 'ExameTipos',
    components: { FiltroListagem, ComboboxAutoComplete },
    data() {
        return {
            tituloJanela: 'Tipo de exame',
            salvando: false,
            lista: [],
            dropdownAbertoKey: null,
            form: { id: null, label: '', ativo: true, ordem: 0, empresa_id: null },
            urlPaginacao: `${URL_ADMIN}/cadastro/exame-tipos/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
                    campoEscopo: '',
                    pages: 20
                }
            },
            statusOpcoes: [
                { value: '', label: 'Todos os status' },
                { value: 'true', label: 'Apenas ativos' },
                { value: 'false', label: 'Apenas inativos' }
            ],
            escopoOpcoes: [
                { value: '', label: 'Todos os escopos' },
                { value: 'global', label: 'Global' },
                { value: 'empresa', label: 'Da empresa' }
            ]
        }
    },
    computed: {
        temFiltrosAtivos() {
            return temFiltrosPreenchidos(this.controle.dados, CAMPOS)
        }
    },
    mounted() {
        lerFiltrosDaUrl(this.controle.dados, CAMPOS)
        document.addEventListener('click', this.fecharDropdown)
        this.$nextTick(() => this.atualizar())
    },
    beforeUnmount() {
        document.removeEventListener('click', this.fecharDropdown)
        this.limparBootstrapModal()
    },
    methods: {
        formVazio() {
            return { id: null, label: '', ativo: true, ordem: 0, empresa_id: null }
        },
        toggleDropdown(id) {
            const key = `exame-tipo:${id}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoKey === `exame-tipo:${id}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        limparBootstrapModal() {
            document.body.classList.remove('modal-open')
            document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove())
        },
        atualizar() {
            sincronizarFiltrosNaUrl(this.controle.dados, CAMPOS, {})
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
            this.form = this.formVazio()
            this.tituloJanela = 'Novo tipo de exame'
            this.$refs.modalTipo?.abrirModal?.()
        },
        editar(item) {
            this.fecharDropdown()
            this.form = { ...item }
            this.tituloJanela = 'Editar tipo de exame'
            this.$refs.modalTipo?.abrirModal?.()
        },
        async salvar() {
            if (!this.form.label?.trim()) {
                return toastr.warning('Informe o nome do tipo.')
            }
            this.salvando = true
            try {
                if (this.form.id) {
                    await axios.put(`${URL_ADMIN}/cadastro/exame-tipos/${this.form.id}`, this.form)
                } else {
                    await axios.post(`${URL_ADMIN}/cadastro/exame-tipos`, this.form)
                }
                toastr.success('Tipo salvo.')
                this.$refs.modalTipo?.fecharModal?.()
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
                await axios.put(`${URL_ADMIN}/cadastro/exame-tipos/${item.id}/ativa-desativa`)
                toastr.success('Status atualizado.')
                this.atualizar()
            } catch (e) {
                toastr.error(e?.response?.data?.msg || 'Erro ao alterar status.')
            }
        }
    }
})
</script>
