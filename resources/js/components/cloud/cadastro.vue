<template>
    <div>
        <div v-if="open && !openGroup">
            <h4 class="text-default">{{ upper(tituloJanela) }}</h4>

            <button class="btn btn-sm mr-1 btn-secondary" @click.prevent="voltar"><i class="fa fa-arrow-left"></i> Voltar</button>

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

                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preloadAjax" @click="alterar">Alterar</button>
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !preloadAjax" @click="cadastrar">Cadastrar</button>
                </fieldset>
            </form>

            <fieldset v-if="editando">
                <legend>Membros</legend>
                <div class="alert alert-info">Todos os membros do Grupo Administradores automaticamente são adicionados.</div>

                <div class="form-group">
                    <label>Colaborador </label>
                    <autocomplete
                        :caminho="`autocomplete/buscaUsuariosAtivos`"
                        :formsm="true"
                        v-model="form.autocomplete_label_colaborador"
                        placeholder="Selecione um(a) colaborador(a)"
                        :id="`colaborador_${hash}`"
                        @onselect="selecionaColaborador"
                    ></autocomplete>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed bg-white" v-if="form.usuarios.length > 0">
                        <thead>
                            <tr class="bg-default">
                                <th class="text-center">Nome</th>
                                <th class="text-center">Remover</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(colaborador, index) in form.usuarios" :key="colaborador.id || index">
                                <td class="text-center">{{ colaborador.nome }}</td>
                                <td class="text-center">
                                    <a href="javascript://" class="btn btn-sm mr-1 btn-danger" @click.prevent="removerLIColaborador(index)">
                                        <i class="fa fa-times" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>

        <div v-if="openGroup && !open">
            <button class="btn btn-sm mr-1 btn-secondary" @click.prevent="voltar"><i class="fa fa-arrow-left"></i> Voltar</button>
            <!--            <grupo></grupo>-->
        </div>

        <div v-show="!open && !openGroup">
            <h4 class="text-default">CLOUD</h4>

            <div class="row">
                <div class="col-md-4 column mb-2">
                    <form id="formBusca" onsubmit="return false">
                        <label>Buscar:</label>
                        <input type="text" placeholder="Nome do cloud" autocomplete="off" class="form-control form-control-sm" />
                    </form>
                </div>
            </div>

            <button type="button" class="btn btn-sm mr-1 btn-success" @click.prevent="atualizar"><i class="fa fa-sync"></i> Atualizar</button>

            <button type="button" class="btn btn-sm mr-1 btn-primary" @click="formNovo"><i class="fa fa-upload"></i> Novo Cloud</button>

            <!--            <button type="button" class="btn btn-sm mr-1 btn-primary" @click="openGroup=true">-->
            <!--                <i class="fas fa-users-cog"></i> Grupo-->
            <!--            </button>-->

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
                                    <!--                                <a class="btn btn-sm mr-1 btn-danger btnFormExcluir" href="javascript://"-->
                                    <!--                                   @click.prevent="janelaConfirmar(item.id)" data-toggle="modal"-->
                                    <!-- @click="$refs.modal_janelaConfirmar && $refs.modal_janelaConfirmar.abrirModal()">-->
                                    <!--                                    <i class="fa fa-trash" aria-hidden="true"></i>-->
                                    <!--                                </a>-->
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
import grupo from './grupo'

export default {
    components: {
        grupo
    },
    data() {
        return {
            tituloJanela: 'Cadastrando Cloud',
            preloadAjax: false,
            editando: false,
            cadastrado: false,
            atualizado: false,
            apagado: false,
            open: false,
            openGroup: false,

            form: {
                nome: '',
                usuarios: [],
                usuariosDelete: [],
                ativo: true
            },

            formDefault: null,
            URL_ADMIN,
            _,
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

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
        removerLIColaborador(index) {
            if (this.editando && !this.form.usuarios[index].novo) {
                this.form.usuariosDelete.push(this.form.usuarios[index].id)
            }
            this.form.usuarios.splice(index, 1)
        },
        selecionaColaborador(obj) {
            const usuario = {}
            usuario.novo = true
            usuario.id = obj.id
            usuario.nome = obj.nome

            let atual = this.form.usuarios.findIndex((val) => val.id === usuario.id)

            if (atual < 0) {
                //Se não existir ainda no array
                this.form.usuarios.push(usuario)
            } else {
                mostraErro('', `O colaborador(a) ${usuario.nome} já está na lista.`)
                this.form.autocomplete_label_colaborador = ''
                return false
            }
            this.form.autocomplete_label_colaborador = ''
        },

        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = ''
                this.form.autocomplete_label_colaborador = ''
                this.form.colaborador_id = ''

                setTimeout(() => {
                    if (this.form.colaborador_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                        $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Colaborador não pode ficar vazio')
                    }
                }, 100)
            }
        },
        voltar() {
            this.atualizar()
            this.open = false
            this.openGroup = false
        },
        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.open = true
            this.openGroup = false

            this.tituloJanela = 'Cadastrando Cloud'

            formReset()
            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.habilidades = _.cloneDeep(this.listaDeHabilidades)
        },

        submitForm() {
            this.editando ? this.alterar() : this.cadastrar()
        },

        async cadastrar() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.preloadAjax = true
            await axios
                .post(`${URL_ADMIN}/clouds/cadastro`, this.form)
                .then((response) => {
                    mostraSucesso('Cadastro realizado com sucesso!')
                    this.preloadAjax = false
                    this.openGroup = false
                    this.open = false
                    this.atualizar()
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        async formAlterar(id) {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.id = id
            this.open = true
            this.openGroup = false
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando Cloud'

            this.preloadAjax = true

            formReset()
            await axios
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
