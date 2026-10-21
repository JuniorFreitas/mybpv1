<template>
    <div :id="hash">
        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90" ref="modal_janelaCadastrar">
            <template #conteudo>
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>CNPJ</label>
                                    <input
                                        v-model="form.dados.cnpj"
                                        class="form-control"
                                        type="text"
                                        placeholder="Informe o CNPJ"
                                        onblur="valida_cnpj_vazio(this)"
                                        v-mascara:cnpj
                                    />
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Razão Social</label>
                                    <input
                                        v-model="form.razao_social"
                                        class="form-control"
                                        type="text"
                                        placeholder="Informe a Razão Social"
                                        onblur="valida_campo_vazio(this, 1)"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Nome Fantasia</label>
                                    <input v-model="form.dados.nome_fantasia" class="form-control" type="text" placeholder="Informe o nome fantasia" />
                                </div>
                            </div>

                            <div class="col-12 col-md-12">
                                <endereco :model="form.dados.endereco"></endereco>
                            </div>

                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Telefone</label>
                                    <input
                                        v-model="form.dados.telefone"
                                        class="form-control"
                                        onblur="valida_telefone(this)"
                                        type="text"
                                        v-mascara:telefone
                                        placeholder="Informe o numero de telefone"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>E-mail</label>
                                    <input
                                        v-model="form.dados.email"
                                        class="form-control"
                                        type="text"
                                        onblur="validaEmailVazio(this)"
                                        placeholder="Informe o e-mail da empresa"
                                    />
                                </div>
                            </div>

                            <div class="col-12"></div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Ativo</label>
                                    <select
                                        class="form-control"
                                        onblur="valida_campo_vazio(this, 1)"
                                        onchange="valida_campo_vazio(this, 1)"
                                        v-model="form.ativo"
                                    >
                                        <option :value="''">Selecione</option>
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preload" @click="alterarformEmpresaExame()">Alterar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !preload" @click="cadastrar()">Cadastrar</button>
            </template>
        </modal>

        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            @submit="atualizar"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="controle.carregando"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div class="col-12 col-md-6">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="empresa-temporaria-filtro-busca">Buscar</label>
                        <input
                            id="empresa-temporaria-filtro-busca"
                            type="text"
                            placeholder="Buscar por razão social ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>
            </template>
            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-secondary" :disabled="controle.carregando" @click="formNovo(); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()">
                    <i class="fa fa-plus"></i> Cadastrar
                </button>
            </template>
        </FiltroListagem>
        <div class="d-flex justify-content-end my-2">
            <mybp-visualizacao-listagem v-model="visualizacao" tela="cadastro-empresa-temporaria"></mybp-visualizacao-listagem>
        </div>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0 && visualizacao === 'lista'">
                <div class="mybp-card" v-for="item in lista" :key="'card-' + item.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ item.id }}</span>
                            <div class="mybp-card-titulo"><strong>{{ item.razao_social || 'Não informado' }}</strong></div>
                        </div>
                        <div class="mybp-card-right">
                            <bt-ativo :rota="`cadastro/empresa-temporaria/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                            <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                                <a class="mybp-btn-acoes-compact" href="#" role="button" @click.prevent.stop="toggleDropdown(item.id)"><i class="fas fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu mybp-dropdown-menu dropdown-menu-right" :class="{ show: isDropdownOpen(item.id) }">
                                    <a class="dropdown-item" href="javascript://" @click.prevent="fecharDropdown(); alterarEmpresaExame(item.id); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"><i class="fa fa-edit mr-1"></i> Editar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mybp-card-details-row">
                        <div class="mybp-detail-item"><span class="mybp-detail-label">Endereço</span><span class="mybp-detail-value">{{ item.dados?.endereco?.endereco_completo || 'Não informado' }}</span></div>
                    </div>
                </div>
            </div>
            <div class="table-responsive mybp-tabela-wrap" v-show="!controle.carregando && lista.length > 0 && visualizacao === 'tabela'">
                <table class="tabela mybp-tabela">
                    <thead>
                        <tr>
                            <th>Cód</th>
                            <th>Razão social</th>
                            <th>Endereço</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in lista":key="index">
                            <td class="text-center">{{ item.id }}</td>
                            <td class="text-center">{{ item.razao_social }}</td>
                            <td class="text-center">{{ item.dados.endereco.endereco_completo }}</td>
                            <td class="text-center">
                                <bt-ativo :rota="`cadastro/empresa-temporaria/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                            </td>
                            <td data-label="Ações">
                                <div class="dropdown d-inline-block" :class="{ show: isDropdownOpen(item.id) }">
                                    <a class="mybp-btn-acoes-compact" href="#" role="button" @click.prevent.stop="toggleDropdown(item.id); posicionarMenuAcoes($event)"><i class="fas fa-ellipsis-v"></i></a>
                                    <div class="dropdown-menu mybp-dropdown-menu dropdown-menu-right" :class="{ show: isDropdownOpen(item.id) }">
                                        <a class="dropdown-item" href="javascript://" @click.prevent="fecharDropdown(); alterarEmpresaExame(item.id); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"><i class="fa fa-edit mr-1"></i> Editar</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlPaginacao"
                :por-pagina="qntPag"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script>
import controlePaginacao from '../../ControlePaginacao'
import Endereco from '../../Endereco'
import modal from '../../Modal'
import FiltroListagem from '../../ui/FiltroListagem'
import { lerVisualizacaoListagem } from '../../../utils/visualizacaoListagem'
import { posicionarMenuAcoes } from '../../../utils/posicionarMenuAcoes'

export default {
    components: {
        modal,
        controlePaginacao,
        Endereco,
        FiltroListagem
    },
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
        },
        filtro: {
            type: Boolean,
            required: false,
            default: true
        },
        modal: {
            // modal Pai
            type: String,
            required: false,
            default: ''
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    computed: {
        temFiltrosAtivos() {
            return !!this.controle.dados.campoBusca
        }
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Empresa Exame',
            visualizacao: lerVisualizacaoListagem('cadastro-empresa-temporaria', 'lista'),
            dropdownAbertoKey: null,

            preload: false,
            editando: false,
            cadastrado: false,

            form: {
                razao_social: '',
                dados: {
                    cnpj: '',
                    nome_fantasia: '',
                    telefone: '',
                    email: '',
                    endereco: {
                        cep: '',
                        logradouro: '',
                        bairro: '',
                        end_end_numero: '',
                        complemento: '',
                        municipio: '',
                        uf: 'MA'
                    }
                },
                ativo: ''
            },

            formDefault: null,

            lista: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/empresa-temporaria/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: ''
                }
            }
        }
    },
    methods: {
        posicionarMenuAcoes,
        limparFiltros() {
            this.controle.dados.campoBusca = ''
            this.atualizar()
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            this.dropdownAbertoKey = null
        },
        toggleDropdown(id) {
            const key = `empresa-temporaria:${id}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoKey === `empresa-temporaria:${id}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela = 'Empresa Exame'
            this.editando = false
            this.cadastrado = false
            this.preload = false
            formReset()
            setupCampo()
        },

        cadastrar() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .post(`${URL_ADMIN}/cadastro/empresa-temporaria`, this.form)
                .then((res) => {
                    if (res.status === 201) {
                        this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                        mostraSucesso('', 'Empresa cadastrada com sucesso')
                        this.cadastrado = true
                        this.preload = false
                        this.atualizar()
                    }
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preload = false
                })
        },

        alterarEmpresaExame(empresatemporaria) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela = 'Alterando Empresa'
            this.preload = true

            this.form = _.cloneDeep(this.formDefault) //copia
            formReset()

            axios
                .get(`${URL_ADMIN}/cadastro/empresa-temporaria/${empresatemporaria}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                    this.preload = false
                })
                .catch((error) => (this.preloadAjax = false))
        },

        alterarformEmpresaExame() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/cadastro/empresa-temporaria/${this.form.id}`, this.form)
                .then((response) => {
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    mostraSucesso('', 'Empresa Exame atualizado com sucesso')
                    this.preload = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preload = false))
        },
        carregou(dados) {
            this.lista = dados.itens
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
}
</script>

<style scoped>
.card {
    border: none;
    background: transparent;
}

ul.timeline {
    list-style-type: none;
    position: relative;
}

ul.timeline:before {
    content: ' ';
    background: #d4d9df;
    display: inline-block;
    position: absolute;
    left: 29px;
    width: 2px;
    height: 100%;
    z-index: 400;
}

ul.timeline > li {
    margin: 20px 0;
    padding-left: 20px;
}

ul.timeline > li:before {
    content: ' ';
    background: white;
    display: inline-block;
    position: absolute;
    border-radius: 50%;
    border: 3px solid #184056;
    left: 20px;
    width: 20px;
    height: 20px;
    z-index: 400;
}

.trackind {
    padding: 0.5rem 0.8rem;
    background-color: #f4f4f4;
    border-radius: 0.5rem;
}
</style>
