<template>
    <div id="componenteTreinamentoIndustria">
        <modal :modal-pai="modal" :titulo="titulo_janela_assinatura" :size="90" id="janelaAssinatura" ref="modal_janelaAssinatura">
            <template #conteudo>
                <assinatura-carteira modal="janelaAssinatura"></assinatura-carteira>
            </template>
        </modal>

        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90" ref="modal_janelaCadastrar">
            <template #conteudo>
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input
                                        v-model="form.label"
                                        class="form-control form-control-sm"
                                        type="text"
                                        placeholder="Informe o Nome"
                                        onblur="valida_campo_vazio(this, 1)"
                                    />
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="form.exibir_na_carteira" class="custom-control-input" id="exibir_na_carteira" />
                                    <label class="custom-control-label" for="exibir_na_carteira">Exibir Carteira Treinamento</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-12" v-if="form.exibir_na_carteira">
                                <div class="form-group">
                                    <label>Nome Reduzido</label>
                                    <input
                                        v-model="form.label_reduzida"
                                        class="form-control form-control-sm"
                                        type="text"
                                        placeholder="Informe o Nome reduzido"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>A quem se destina</label>
                                    <input
                                        v-model="form.descricao"
                                        class="form-control form-control-sm"
                                        type="text"
                                        placeholder="Informe para quem se destina"
                                        onblur="valida_campo_vazio(this, 1)"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Prazo fixo (dias para vencimento)</label>
                                    <input v-model="form.prazo_fixo" class="form-control form-control-sm" type="number" placeholder="Ex: 365" min="1" />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Ordem</label>
                                    <input v-model="form.ordem" class="form-control form-control-sm" type="number" />
                                </div>
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label>Segmento / Padrão de treinamento</label>
                                    <select v-model="form.segmento_treinamento_id" class="form-control form-control-sm">
                                        <option :value="null">Selecione o segmento</option>
                                        <option v-for="s in segmentos" :key="s.id" :value="s.id">{{ s.nome }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="custom-control custom-switch">
                                    <input
                                        type="checkbox"
                                        v-model="form.vinculo_todos_cargos"
                                        class="custom-control-input"
                                        id="vinculo_todos_cargos"
                                        @change="onToggleVinculoTodosCargos"
                                    />
                                    <label class="custom-control-label" for="vinculo_todos_cargos">Vincular a todos os cargos</label>
                                </div>
                                <small v-if="form.vinculo_todos_cargos" class="text-muted d-block">
                                    Quando ativo, o treinamento vale para qualquer cargo, independentemente da lista abaixo.
                                </small>
                            </div>
                            <div class="col-12 col-md-12" v-show="!form.vinculo_todos_cargos">
                                <fieldset>
                                    <legend>Cargos vinculados</legend>
                                    <div class="form-group">
                                        <label>Adicionar cargo</label>
                                        <autocomplete
                                            :caminho="`autocomplete/cargos_ativos`"
                                            :formsm="true"
                                            v-model="form.autocomplete_label_cargo"
                                            placeholder="Selecione um cargo"
                                            :id="`cargo_${hash}`"
                                            @onselect="selecionaCargo"
                                        ></autocomplete>
                                    </div>

                                    <div class="table-responsive" v-if="form.cargos.length > 0">
                                        <table class="table table-bordered table-hover table-condensed bg-white">
                                            <thead>
                                                <tr class="bg-default">
                                                    <th class="text-center">#</th>
                                                    <th>Cargo</th>
                                                    <th class="text-center">Remover</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(cargo, index) in form.cargos" :key="cargo.id">
                                                    <td class="text-center">{{ index + 1 }}</td>
                                                    <td>{{ cargo.nome }}</td>
                                                    <td class="text-center">
                                                        <a href="javascript://" class="btn btn-sm mr-1 btn-danger" @click.prevent="removerCargo(index)">
                                                            <i class="fa fa-times" aria-hidden="true"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="ativo" />
                                    <label class="custom-control-label" for="ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando" @click="alterarformTreinamentoIndustria()">Salvar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando" @click="cadastrar()">Cadastrar</button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                <div class="col-12 col-md-5">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input
                            type="text"
                            placeholder="Buscar por nome"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" :disabled="controle.carregando" v-model="controle.dados.campoStatus" @change="atualizar()">
                            <option value="">Todos os Status</option>
                            <option :value="true">Apenas Ativos</option>
                            <option :value="false">Apenas Inativos</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Segmento</label>
                        <select
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.segmento_treinamento_id"
                            @change="atualizar()"
                        >
                            <option value="">Todos os segmentos</option>
                            <option v-for="s in segmentos" :key="s.id" :value="s.id">{{ s.nome }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm mr-1 btn-primary"
                        :disabled="controle.carregando"
                        @click="formNovo(); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                    >
                        <i class="fa fa-plus"></i> Treinamento Indústria
                    </button>

                    <button type="button" class="btn btn-sm mr-1 btn-secondary" @click="$refs.modal_janelaAssinatura && $refs.modal_janelaAssinatura.abrirModal()">
                        <i class="fa fa-plus"></i> Assinatura Carteira
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">
            <p class="mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Nº</td>
                            <td class="text-center">Nome</td>
                            <td class="text-center">Padrão de Treinamento</td>
                            <td class="text-center">Todos os cargos</td>
                            <td class="text-center">A Quem se destina</td>
                            <td class="text-center">Prazo fixo (dias)</td>
                            <td class="text-center">Ordem</td>
                            <td class="text-center">Ativo</td>
                            <td class="text-center">Ação</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in lista" :key="index">
                            <td class="text-center">{{ item.id }}</td>
                            <td class="text-center">{{ item.label }}</td>
                            <td class="text-center">{{ item.segmento_treinamento ? item.segmento_treinamento.nome : '-' }}</td>
                            <td class="text-center">
                                <span v-if="item.vinculo_todos_cargos" class="badge badge-info">Sim</span>
                                <span v-else class="text-muted">Não</span>
                            </td>
                            <td class="text-center">{{ item.descricao }}</td>
                            <td class="text-center">{{ item.prazo_fixo }}</td>
                            <td class="text-center">{{ item.ordem }}</td>
                            <td class="text-center">
                                <bt-ativo :rota="`cadastro/treinamentoindustria/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                            </td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm mr-1 btn-primary mb-1"
                                    @click="alterarTreinamentoIndustria(item.id); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
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
import modal from '../../Modal'
import AssinaturaCarteira from './AssinaturaCarteira.vue'

export default {
    components: {
        modal,
        controlePaginacao,
        AssinaturaCarteira
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
        this.carregarSegmentos()
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Treinamento Indústria',
            titulo_janela_assinatura: 'Assinatura Carteira',

            preload: false,
            editando: false,
            cadastrado: false,

            form: {
                label: '',
                label_reduzida: '',
                exibir_na_carteira: false,
                descricao: '',
                prazo_fixo: 365,
                ordem: 1,
                ativo: true,
                segmento_treinamento_id: null,
                vinculo_todos_cargos: false,
                cargos: [],
                cargo_ids: [],
                cargo_id: '',
                autocomplete_label_cargo: ''
            },

            formDefault: null,
            segmentos: [],

            lista: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/treinamentoindustria/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
                    segmento_treinamento_id: ''
                }
            }
        }
    },
    methods: {
        removerCargo(index) {
            this.form.cargos.splice(index, 1)
            this.form.cargo_ids = this.form.cargos.map((item) => item.id)
        },
        selecionaCargo(obj) {
            if (this.form.vinculo_todos_cargos) {
                return
            }
            const cargo = {
                id: obj.id,
                nome: obj.nome || obj.label
            }

            const atual = this.form.cargos.findIndex((item) => item.id === cargo.id)
            if (atual >= 0) {
                mostraErro('', `O cargo ${cargo.nome} já está na lista.`)
                this.form.autocomplete_label_cargo = ''
                return
            }

            this.form.cargos.push(cargo)
            this.form.cargo_ids = this.form.cargos.map((item) => item.id)
            this.form.autocomplete_label_cargo = ''
        },
        onToggleVinculoTodosCargos() {
            if (this.form.vinculo_todos_cargos) {
                this.form.cargos = []
                this.form.cargo_ids = []
                this.form.autocomplete_label_cargo = ''
            }
        },
        carregarSegmentos() {
            axios
                .get(`${URL_ADMIN}/cadastro/segmentostreinamento/lista`)
                .then((res) => {
                    this.segmentos = res.data || []
                })
                .catch(() => {
                    this.segmentos = []
                })
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela = 'Treinamento Indústria'
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
            const payload = {
                ...this.form,
                prazo_parada: null,
                cargo_ids: this.form.vinculo_todos_cargos ? [] : this.form.cargos.map((item) => item.id)
            }
            axios
                .post(`${URL_ADMIN}/cadastro/treinamentoindustria`, payload)
                .then((res) => {
                    if (res.status === 201) {
                        this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                        mostraSucesso('', 'Treinamento Indústria cadastrado com sucesso')
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
        alterarTreinamentoIndustria(treinamentoindustria) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela = 'Alterando Treinamento Indústria'
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia

            axios
                .get(`${URL_ADMIN}/cadastro/treinamentoindustria/${treinamentoindustria}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    if (this.form.vinculo_todos_cargos) {
                        this.form.cargos = []
                        this.form.cargo_ids = []
                    } else {
                        this.form.cargos = response.data.vagas || []
                        this.form.cargo_ids = this.form.cargos.map((item) => item.id)
                    }
                    this.form.autocomplete_label_cargo = ''
                    this.editando = true
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarformTreinamentoIndustria() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preload = true
            const payload = {
                ...this.form,
                prazo_parada: null,
                cargo_ids: this.form.vinculo_todos_cargos ? [] : this.form.cargos.map((item) => item.id)
            }
            axios
                .put(`${URL_ADMIN}/cadastro/treinamentoindustria/${this.form.id}`, payload)
                .then((response) => {
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    mostraSucesso('', 'Treinamento Indústria atualizado com sucesso')
                    this.preload = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preload = false))
        },
        carregou(dados) {
            this.lista = dados.items
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
