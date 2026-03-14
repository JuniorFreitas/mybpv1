<template>
    <div>
        <modal ref="modalFilial" id="janelaFilialCadastrar" :titulo="titulo_janela" :size="80" :fechar="!preload" :modal-pai="modal">
            <template #conteudo>
                <preload v-if="preload"></preload>
                <div v-if="form.dados && !preload">
                    <fieldset>
                        <legend class="text-uppercase">Dados da Filial</legend>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label>CNPJ</label>
                                    <input
                                        type="text"
                                        id="cnpj"
                                        class="form-control"
                                        placeholder="CNPJ"
                                        v-model="form.dados.cnpj"
                                        :disabled="editando"
                                        autocomplete="off"
                                        v-mascara:cnpj
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label>Razão Social</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="form.dados.razao_social"
                                        placeholder="Razão Social"
                                        autocomplete="off"
                                        onblur="valida_campo_vazio(this, 3)"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label>Nome Fantasia</label>
                                    <input type="text" class="form-control" v-model="form.dados.nome_fantasia" placeholder="Nome Fantasia" autocomplete="off" />
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label>Área de Atuação</label>
                                    <select v-model="form.dados.area_id" class="form-control" onblur="valida_campo_vazio(this, 1)">
                                        <option value="">Selecione</option>
                                        <option v-for="(item, index) in listaAreas" :key="index" :value="item.id">{{ item.label }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label>Ramo</label>
                                    <input type="text" class="form-control" v-model="form.dados.ramo" placeholder="Ramo" autocomplete="off" />
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="text-uppercase">Endereço</legend>
                        <endereco :model="form.dados"></endereco>
                    </fieldset>

                    <fieldset>
                        <legend class="text-uppercase">Contatos</legend>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label>Responsável</label>
                                    <input type="text" class="form-control" placeholder="Nome do Responsável" v-model="form.dados.contato" autocomplete="off" />
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label>Telefone</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Telefone"
                                        v-mascara:telefone
                                        v-model="form.dados.telefone"
                                        autocomplete="off"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label>E-mail</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="email"
                                        placeholder="E-mail"
                                        v-model="form.dados.email"
                                        autocomplete="off"
                                        onblur="validaEmailVazio(this)"
                                        v-mascara:email
                                    />
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="ativo" />
                        <label class="custom-control-label" for="ativo">{{ form.ativo ? 'Ativo' : 'Inativo' }}</label>
                    </div>
                </div>
            </template>

            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando" @click="alterarform()">Salvar</button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando" @click="cadastrar()">Cadastrar</button>
            </template>
        </modal>

        <div class="row">
            <div class="col-12 col-md-12">
                <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar()">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button type="button" class="btn btn-sm mr-1 btn-primary" :disabled="controle.carregando" @click="formNovo()">
                    <i class="fa fa-plus"></i> Cadastrar
                </button>
            </div>
        </div>

        <div id="conteudo">
            <preload v-if="controle.carregando"></preload>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                        <tr class="bg-default">
                            <td class="text-center">Nº</td>
                            <td class="text-center">CNPJ</td>
                            <td class="text-center">Razão Social</td>
                            <td class="text-center">Ativo</td>
                            <td class="text-center">Ação</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in lista" :key="index">
                            <td class="text-center">{{ item.id }}</td>
                            <td class="text-center">{{ item.dados.cnpj }}</td>
                            <td class="text-center">{{ item.dados.razao_social }}</td>
                            <td class="text-center">
                                <bt-ativo :rota="`administracao/clientes/filial/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm mr-1 btn-primary mb-1" @click="alterarFilial(item.id)">
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
import Endereco from '../../Endereco.vue'
import Validacoes from '../../../mixins/Validacoes'

export default {
    name: 'Filial',
    mixins: [Validacoes],
    components: {
        modal,
        Endereco,
        controlePaginacao
    },
    props: {
        empresa_id: {
            type: Number,
            required: true
        },
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
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: 'Nova Filial',

            preload: false,
            editando: false,
            cadastrado: false,

            form: {
                id: 0,
                empresa_id: this.empresa_id,
                dados: null,
                ativo: true
            },

            formDefault: null,

            lista: [],
            listaServicos: [],
            listaAreas: [],

            urlPaginacao: `${URL_ADMIN}/administracao/clientes/filial/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    empresa_id: this.empresa_id,
                    campoBusca: '',
                    campoStatus: ''
                }
            }
        }
    },
    methods: {
        abrirModalFilial() {
            if (this.$refs && this.$refs.modalFilial && typeof this.$refs.modalFilial.abrirModal === 'function') {
                this.$refs.modalFilial.abrirModal()
            }
        },
        fecharModalFilial() {
            if (this.$refs && this.$refs.modalFilial && typeof this.$refs.modalFilial.fecharModal === 'function') {
                this.$refs.modalFilial.fecharModal()
            }
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela = 'Nova Filial'
            this.editando = false
            this.cadastrado = false
            this.preload = false
            formReset()
            setupCampo()
            this.abrirModalFilial()
        },

        cadastrar() {
            $('#janelaFilialCadastrar :input:visible').trigger('blur')
            if ($('#janelaFilialCadastrar :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            axios
                .post(`${URL_ADMIN}/administracao/clientes/filial`, this.form)
                .then((res) => {
                    this.fecharModalFilial()
                    mostraSucesso('', 'Filial cadastrada com sucesso')
                    this.cadastrado = true
                    this.atualizar()
                    this.preload = false
                })
                .catch((error) => {
                    this.cadastrado = false
                    this.preload = false
                })
        },
        alterarFilial(filial) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela = 'Alterando Filial '
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.preload = true
            this.abrirModalFilial()
            axios
                .get(`${URL_ADMIN}/administracao/clientes/filial/${filial}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    this.preload = false
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterarform() {
            formReset()
            $('#janelaFilialCadastrar :input:enabled').trigger('blur')

            if ($('#janelaFilialCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/administracao/clientes/filial/${this.form.id}`, this.form)
                .then((response) => {
                    this.fecharModalFilial()
                    mostraSucesso('', 'Filial atualizada com sucesso')
                    this.atualizado = true
                    this.atualizar()
                    this.preload = false
                })
                .catch((error) => (this.preload = false))
        },
        carregou(dados) {
            this.lista = dados.itens
            this.listaServicos = dados.servicos
            this.listaAreas = dados.areas
            this.form.dados = dados.dto
            this.formDefault = _.cloneDeep(this.form)
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

<style scoped></style>
