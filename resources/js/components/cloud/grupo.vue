<template>
    <div>
        <div v-if="open">
            <h4 class="text-default my-2">{{ upper(tituloJanela) }}</h4>

            <button class="btn btn-sm mr-1 btn-secondary" @click.prevent="voltar"><i class="fa fa-arrow-left"></i> Voltar a lista de grupos</button>

            <preload class="my-2" v-show="preloadAjax"></preload>
            <form v-show="!preloadAjax && !cadastrado && !atualizado" @submit.prevent="submitForm">
                <fieldset>
                    <legend>INFORMAÇÕES</legend>
                    <div class="form-group">
                        <label>Nome</label>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            v-model="form.nome"
                            placeholder="Nome do Cloud"
                            autocomplete="off"
                            onblur="valida_campo_vazio(this, 2)"
                        />
                    </div>

                    <div class="form-group">
                        <label>Ativo</label>
                        <select class="form-control form-control-sm" v-model="form.ativo">
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>
                </fieldset>
            </form>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preloadAjax" @click="alterar">Alterar</button>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !preloadAjax" @click="cadastrar">Cadastrar</button>
        </div>

        <div v-show="!open" class="mt-2">
            <h4 class="text-default">GRUPOS</h4>

            <div class="row">
                <div class="col-md-4 column mb-2">
                    <form id="formBusca" onsubmit="return false">
                        <label>Buscar:</label>
                        <input type="text" placeholder="Nome do cloud" autocomplete="off" class="form-control form-control-sm" />
                    </form>
                </div>
            </div>

            <button type="button" class="btn btn-sm mr-1 btn-success" @click.prevent="atualizar"><i class="fa fa-sync"></i> Atualizar</button>

            <button type="button" class="btn btn-sm mr-1 btn-primary" id="btnFormCadastrar" @click="formNovo(); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()">
                <i class="fa fa-plus"></i> Cadastrar
            </button>

            <preload class="mt-2" v-if="controle.carregando"></preload>

            <div id="conteudo">
                <p class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                    <i class="fa fa-exclamation-triangle"></i> Nenhum registro encontrado!
                </p>
                <div class="table-responsive">
                    <table class="tabela" v-if="!controle.carregando && lista.length > 0">
                        <thead>
                            <tr class="bg-default">
                                <th class="text-center">ID</th>
                                <th class="text-center">Nome</th>
                                <th class="text-center">Ativo</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="item in lista" :key="item.id">
                                <td class="text-center">{{ item.id }}</td>
                                <td class="text-center">{{ item.nome }}</td>
                                <td class="text-center">
                                    <bt-ativo :rota="`clouds/cadastro/${item.id}/ativa-desativa`" :model="item"></bt-ativo>
                                </td>
                                <td class="text-center">
                                    <a
                                        class="btn btn-sm mr-1 btn-success btnFormAlterar"
                                        href="javascript://"
                                        @click.prevent="formAlterar(item.id); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a
                                        class="btn btn-sm mr-1 btn-danger btnFormExcluir"
                                        href="javascript://"
                                        @click.prevent="janelaConfirmar(item.id); $refs.modal_janelaConfirmar && $refs.modal_janelaConfirmar.abrirModal()"
                                    >
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="url_paginacao"
                :por-pagina="controle.dados.pages"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            tituloJanela: 'Cadastrando Grupo',
            preloadAjax: false,
            editando: false,
            cadastrado: false,
            atualizado: false,
            apagado: false,
            open: false,

            form: {
                nome: '',
                ativo: true
            },

            formDefault: null,
            URL_ADMIN,
            _,

            url_paginacao: `${URL_ADMIN}/clouds/cadastro/atualizar`,

            lista: [],

            controle: {
                carregando: false,
                dados: {}
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar()
    },
    methods: {
        upper(value) {
            if (!value) return ''
            return value.toUpperCase()
        },
        voltar() {
            this.atualizar()
            this.open = false
        },
        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.open = true

            this.tituloJanela = 'Cadastrando Grupo'

            formReset()
            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.habilidades = _.cloneDeep(this.listaDeHabilidades)
        },

        submitForm() {
            this.editando ? this.alterar() : this.cadastrar()
        },

        cadastrar() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.preloadAjax = true
            axios
                .post(`${URL_ADMIN}/clouds/cadastro`, this.form)
                .then((response) => {
                    mostraSucesso('Cadastro realizado com sucesso!')
                    this.preloadAjax = false
                    this.atualizar()
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        formAlterar(id) {
            this.form.id = id
            this.open = true
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando Grupo'

            this.preloadAjax = true

            formReset()
            axios
                .get(`${URL_ADMIN}/clouds/cadastro/${id}/editar`)
                .then(({ data }) => {
                    this.editando = true
                    Object.assign(this.form, data)
                    this.preloadAjax = false
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        alterar() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/clouds/cadastro/${this.form.id}`, this.form)
                .then(({ data }) => {
                    mostraSucesso('Altereção realizada com sucesso!')
                    this.open = false
                    this.preloadAjax = false
                    this.atualizar()
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false
            this.preloadAjax = false
        },

        apagar() {
            this.preloadAjax = true

            axios
                .delete(`${URL_ADMIN}/clouds/cadastro/${this.form.id}`, this.form)
                .then(({ data }) => {
                    this.preloadAjax = false
                    this.apagado = true
                    this.atualizar()
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        carregou(dados) {
            this.lista = dados.lista
            this.controle.carregando = false
        },

        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            if (this.$refs && this.$refs.componente) {
                this.$refs.componente.atual = 1
            }
            if (this.$refs && this.$refs.componente && typeof this.$refs.componente.buscar === 'function') {
                this.$refs.componente.buscar()
            }
        }
    }
}
</script>

<style scoped></style>
