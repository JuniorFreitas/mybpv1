<template>
    <div :id='hash'>
        <modal :modal-pai="modal" :titulo="titulo_janela_pcmso" :size='90'
               id="janelaPcmso">
            <template slot="conteudo">
                <pcmso v-if="pcmsoOpen"></pcmso>
            </template>
        </modal>

        <modal id='janelaCadastrar' :titulo='titulo_janela' :fechar='!preload' :size='90'>
            <template slot='conteudo'>
                <preload v-show='preload'></preload>
                <div v-if='!preload && !cadastrado'>
                    <fieldset>
                        <legend>Informações</legend>
                        <div class='row'>
                            <div class='col-12 col-md-4'>
                                <div class='form-group'>
                                    <label>CNPJ</label>
                                    <input v-model='form.dados.cnpj' class='form-control' type='text'
                                           placeholder='Informe o CNPJ'
                                           onblur='valida_cnpj_vazio(this)' v-mascara:cnpj>
                                </div>
                            </div>

                            <div class='col-12'></div>

                            <div class='col-12 col-md-6'>
                                <div class='form-group'>
                                    <label>Razão Social</label>
                                    <input v-model='form.nome' class='form-control' type='text'
                                           placeholder='Informe a Razão Social'
                                           onblur='valida_campo_vazio(this,1)'>
                                </div>
                            </div>

                            <div class='col-12 col-md-6'>
                                <div class='form-group'>
                                    <label>Nome Fantasia</label>
                                    <input v-model='form.dados.nome_fantasia' class='form-control' type='text'
                                           placeholder='Informe o nome fantasia'>
                                </div>
                            </div>

                            <div class='col-12 col-md-12'>
                                <endereco :model='form.dados.endereco'></endereco>
                            </div>

                            <div class='col-12 col-md-3'>
                                <div class='form-group'>
                                    <label>Telefone</label>
                                    <input v-model='form.dados.telefone' class='form-control'
                                           onblur='valida_telefone(this)' type='text' v-mascara:telefone
                                           placeholder='Informe o numero de telefone'>
                                </div>
                            </div>

                            <div class='col-12 col-md-4'>
                                <div class='form-group'>
                                    <label>E-mail</label>
                                    <input v-model='form.dados.email' class='form-control' type='text'
                                           onblur='validaEmailVazio(this)'
                                           placeholder='Informe o e-mail da empresa'>
                                </div>
                            </div>

                            <div class='col-12'></div>

                            <div class="col-12 mt-2">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="form.ativo" class="custom-control-input" id="EmpresaAtivo">
                                    <label class="custom-control-label"
                                           for="EmpresaAtivo">{{ form.ativo ? "Ativo" : "Inativo" }}</label>
                                </div>
                            </div>
<!--                            <div class='col-12 col-md-4'>-->
<!--                                <div class='form-group'>-->
<!--                                    <label>Ativo</label>-->
<!--                                    <select class='form-control' onblur='valida_campo_vazio(this,1)'-->
<!--                                            onchange='valida_campo_vazio(this,1)' v-model='form.ativo'>-->
<!--                                        <option :value="''">Selecione</option>-->
<!--                                        <option :value='true'>Sim</option>-->
<!--                                        <option :value='false'>Não</option>-->
<!--                                    </select>-->
<!--                                </div>-->
<!--                            </div>-->

                        </div>
                    </fieldset>
                </div>
            </template>
            <template slot='rodape'>
                <button type='button' class='btn btn-sm btn-primary' v-show='editando && !preload'
                        @click='alterarformEmpresaExame()'>
                    Alterar
                </button>
                <button type='button' class='btn btn-sm btn-primary' v-show='!editando && !preload'
                        @click='cadastrar()'>
                    Cadastrar
                </button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class='row' @submit.prevent='$refs.componente.buscar()'>
                <div class='col-12 col-md-4'>
                    <div class='form-group'>
                        <label>Buscar</label>
                        <input type='text'
                               placeholder='Buscar por razão social'
                               autocomplete='off'
                               class='form-control form-control-sm' :disabled='controle.carregando'
                               v-model='controle.dados.campoBusca'>
                    </div>
                </div>

                <div class='col-12 col-md-12'>
                    <button type='button' class='btn btn-sm btn-success' :disabled='controle.carregando'
                            @click='atualizar'><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button type='button' class='btn btn-sm btn-primary' :disabled='controle.carregando'
                            @click='formNovo'
                            data-toggle='modal'
                            data-target='#janelaCadastrar'>
                        <i class='fa fa-plus'></i> Cadastrar
                    </button>

                    <button type="button" class="btn btn-sm btn-secondary"
                            data-toggle="modal"
                            v-if="pcmsoOpen"
                            data-target="#janelaPcmso">
                        <i class="fa fa-plus"></i> PCMSO
                    </button>
                </div>
            </form>
        </fieldset>

        <div id='conteudo'>

            <p class=' mt-2 text-center' v-if='controle.carregando'>
                <preload></preload>
            </p>

            <div class='alert alert-warning text-center' v-show='!controle.carregando && lista.length === 0'>
                <i class='fa fa-exclamation-triangle'></i> Nenhum Registro Encontrado
            </div>

            <div class='table-responsive' v-show='!controle.carregando && lista.length > 0'>
                <table class='tabela'>
                    <thead>
                    <tr class='bg-default'>
                        <td class='text-center'>Nº</td>
                        <td class='text-center'>Razão Social</td>
                        <td class='text-center'>Endereço</td>
                        <td class='text-center'>Ativo</td>
                        <td class='text-center'>Ação</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for='item in lista'>
                        <td class='text-center'>{{ item.id }}</td>
                        <td class='text-center'>{{ item.nome }}</td>
                        <td class='text-center'>{{ item.dados.endereco.endereco_completo }}</td>
                        <td class='text-center'>
                            <bt-ativo :rota='`cadastro/empresa-exame/${item.id}/ativa-desativa`'
                                      :model='item'></bt-ativo>
                        </td>
                        <td class='text-center'>
                            <button type='button' class='btn btn-sm btn-primary mb-1' data-toggle='modal'
                                    data-target='#janelaCadastrar' @click='alterarEmpresaExame(item.id)'>
                                <i class='fa fa-edit'></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao class='d-flex justify-content-center' id='controle' ref='componente'
                                :url='urlPaginacao' :por-pagina='qntPag'
                                :dados='controle.dados'
                                v-on:carregou='carregou' v-on:carregando='carregando'></controle-paginacao>
        </div>
    </div>
</template>

<script>
import controlePaginacao from '../../ControlePaginacao'
import Endereco from '../../Endereco'
import modal from '../../Modal'
import Pcmso from "../pcmso/Pcmso";

export default {
    components: {
        Pcmso,
        modal,
        controlePaginacao,
        Endereco
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
        modal: { // modal Pai
            type: String,
            required: false,
            default: ''
        }
    },
    mounted() {
        this.atualizar()
        this.formDefault = _.cloneDeep(this.form)
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            titulo_janela: '',
            titulo_janela_pcmso: 'PCMSO',
            titulo_janela_form_pcmso: 'PCMSO',

            preload: false,
            editando: false,
            cadastrado: false,
            atualizado: false,

            pcmsoOpen: false,
            permissoes:[],

            form: {
                nome: '',
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
                ativo: true
            },
            formDefault: null,

            lista: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/empresa-exame/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: ''
                }
            }
        }
    },
    methods: {
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
            axios.post(`${URL_ADMIN}/cadastro/empresa-exame`, this.form)
                .then(res => {
                    if (res.status === 201) {
                        $('#janelaCadastrar').modal('hide')
                        mostraSucesso('', 'Empresa cadastrada com sucesso')
                        this.cadastrado = true
                        this.preload = false
                        this.atualizar()
                    }
                })
                .catch(error => {
                    this.cadastrado = false
                    this.preload = false
                })
        },

        alterarEmpresaExame(empresaexame) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela = 'Alterando Empresa'
            this.preload = true

            this.form = _.cloneDeep(this.formDefault) //copia
            formReset()

            axios.get(`${URL_ADMIN}/cadastro/empresa-exame/${empresaexame}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data)
                    this.editando = true
                    setupCampo()
                    this.preload = false
                }).catch(
                error => (this.preloadAjax = false)
            )

        },

        alterarformEmpresaExame() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preload = true

            axios.put(`${URL_ADMIN}/cadastro/empresa-exame/${this.form.id}`, this.form).then(response => {
                $('#janelaCadastrar').modal('hide')
                mostraSucesso('', 'Empresa Exame atualizado com sucesso')
                this.preload = false
                this.atualizado = true
                this.atualizar()
            }).catch(error => (this.preload = false))

        },
        carregou(dados) {
            this.lista = dados.itens
            this.permissoes = dados.permissoes;
            this.pcmsoOpen = this.permissoes.pcmso;
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs.componente.atual = 1
            this.$refs.componente.buscar()
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
    padding: .5rem .8rem;
    background-color: #f4f4f4;
    border-radius: .5rem;
}
</style>
