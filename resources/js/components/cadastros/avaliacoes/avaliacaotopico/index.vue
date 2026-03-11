<template>
    <div :id="hash">
        <modal id="janelaCadastrar" :titulo="titulo_janela" :fechar="!preload" :size="90" ref="modal_janelaCadastrar">
            <template v-slot:conteudo>
                <preload v-show="preload"></preload>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Competência</label>
                                    <input v-model="form.topico" class="form-control" type="text"
                                           placeholder="Informe o nome de competência"
                                           onblur="valida_campo_vazio(this,1)">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Descrição de competência</label>
                                    <textarea v-model="form.topico_explicacao" class="form-control"
                                              placeholder="Informe a descrição de competência" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Tipo de avaliação</label>
                                    <select class="form-control" v-model="form.avaliacao_tipo_id"
                                            onchange="valida_campo_vazio(this,1)" onblur="valida_campo_vazio(this,1)">
                                        <option value="">Selecione ...</option>
                                        <option v-for="item in lista_avaliacoes_tipos" :value="item.id" :key="item.id">
                                            {{ item.nome }}
                                        </option>

                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Ativo</label>
                                    <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                            onchange="valida_campo_vazio(this,1)" v-model="form.ativo">
                                        <option :value="''">Selecione</option>
                                        <option :value="true">Sim</option>
                                        <option :value="false">Não</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.avaliacao_tipo_id > 0">
                        <legend>Indicador</legend>
                        <button class="btn btn-sm mr-1 btn-primary mb-2" @click="addLiSubtopico($event.target)">Adicionar
                            Indicador
                        </button>
                        <div class="accordion" id="topico">
                            <div class="card mb-3 border" v-for="(objsubtopico, ind) in form.subtopicos"
                            :key="ind"
                                 v-show="form.subtopicos.length> 0">
                                <div class="card-header" style="background: #072433; color: white"
                                     :id="objsubtopico.id">
                                    <h2 class="mb-0">
                                        <a class="btn btn-link btn-block text-left" href="javascript://"
                                           data-toggle="collapse" aria-expanded="true"
                                           :aria-controls="`collapse${objsubtopico.id}`" @click="$refs[`collapse${objsubtopico.id}`] && $refs[`collapse${objsubtopico.id}`].abrirModal()">
                                            Indicador - {{ ind + 1 }}
                                        </a>
                                    </h2>
                                </div>

                                <div :id="`collapse${objsubtopico.id}`" class="collapse show"
                                     :aria-labelledby="objsubtopico.id"
                                     data-parent="#topico">
                                    <div class="card-body">
                                        <div class="col-12 mt-2">
                                            <label>Indicador</label>
                                            <input v-model="objsubtopico.topico" class="form-control" type="text"
                                                   placeholder="Informe o nome do indicador"
                                                   onblur="valida_campo_vazio(this,1)">
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label>Descrição do indicador</label>
                                            <textarea v-model="objsubtopico.topico_explicacao" class="form-control"
                                                      placeholder="Informe a descrição de competência"
                                                      rows="4"></textarea>
                                        </div>
                                    </div>
                                    <a class="btn btn-sm mr-1 btn-danger" href="javascript://"
                                       @click="removerLISubtopico(ind)">
                                        <i class="fa fa-trash"></i> Apagar subtópico {{ ind + 1 }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                </div>
            </template>
            <template v-slot:rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preload"
                        @click="alterar()">
                    Alterar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-if="lista_avaliacoes_tipos.length > 0"
                        v-show="!editando && !preload"
                        @click="cadastrar()">
                    Cadastrar
                </button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input type="text"
                               placeholder="Buscar por tópico"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando"
                            @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button type="button" class="btn btn-sm mr-1 btn-primary" :disabled="controle.carregando"
                            @click="formNovo(); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()">
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">

            <p class=" mt-2 text-center" v-if="controle.carregando">
                <preload></preload>
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <td class="text-center">Competência</td>
                        <td class="text-center">Qnt Indicadores</td>
                        <td class="text-center">Tipo Avaliação</td>
                        <td class="text-center">Ativo</td>
                        <td class="text-center">Ação</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista" :key="item.id">
                        <td class="text-center">{{ item.topico }}</td>
                        <td class="text-center">{{ item.qnt_subtopicos }}</td>
                        <td class="text-center">{{ item.avaliacao_tipo.nome }}</td>
                        <td class="text-center">
                            <bt-ativo :rota="`cadastro/avaliacoes/avaliacaotopico/${item.id}/ativa-desativa`"
                                      :model="item"></bt-ativo>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm mr-1 btn-primary mb-1" @click="alterarForm(item); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                                :url="urlPaginacao" :por-pagina="qntPag"
                                :dados="controle.dados"
                                v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
        </div>
    </div>
</template>

<script>
import controlePaginacao from '../../../ControlePaginacao'
import modal from '../../../Modal'

export default {
    components: {
        modal,
        controlePaginacao
    },
    props: {
        tipoPj: {
            type: Boolean,
            required: false,
            default: false
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
            titulo_janela: 'Montagem de competência',
            preload: false,
            editando: false,
            cadastrado: false,

            form: {
                topico: '',
                topico_explicacao: '',
                ativo: true,
                avaliacao_tipo_id: '',
                subtopicos: [],
                subtopicosDelete: [],
                tipo_pj: this.tipoPj
            },

            formDefault: null,

            lista: [],
            lista_avaliacoes_tipos: [],

            urlPaginacao: `${URL_ADMIN}/cadastro/avaliacoes/avaliacaotopico/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    tipo_pj: this.tipoPj
                }
            }
        }
    },
    methods: {
        addLiSubtopico() {
            let objsubtopico = {}
            objsubtopico.nova = true
            objsubtopico.topico = ''
            objsubtopico.topico_pai_id = ''
            objsubtopico.qnt_linhas = ''
            objsubtopico.topico_explicacao = ''
            this.form.subtopicos.push(objsubtopico)
        },
        removerLISubtopico(index) {
            if (this.editando) {
                this.form.subtopicosDelete.push(this.form.subtopicos[index].id)
            }
            this.form.subtopicos.splice(index, 1)
        },

        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela = 'Montagem de competência'
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
            axios.post(`${URL_ADMIN}/cadastro/avaliacoes/avaliacaotopico`, this.form)
                .then(res => {
                    if (res.status === 201) {
                        this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                        mostraSucesso('', 'Competência de Avaliação cadastrado com sucesso')
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

        async alterarForm(avaliacaotopico) {
            this.cadastrado = false
            this.editando = true
            this.titulo_janela = `Alterande competência ${avaliacaotopico.id}`
            this.preload = true

            this.form = _.cloneDeep(this.formDefault) //copia
            formReset()

            try {
                const response = await axios.get(`${URL_ADMIN}/cadastro/avaliacoes/avaliacaotopico/${avaliacaotopico.id}/editar`)
                Object.assign(this.form, response.data)
                this.editando = true
                setupCampo()
            } catch (error) {
                this.preloadAjax = false
            } finally {
                this.preload = false
            }
        },

        async alterar() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }
            this.preload = true
            try {
                await axios.put(`${URL_ADMIN}/cadastro/avaliacoes/avaliacaotopico/${this.form.id}`, this.form)
                this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                mostraSucesso('', 'Competência de avaliação alterado com sucesso')
                this.atualizado = true
                this.atualizar()
            } catch (error) {
                // erro
            } finally {
                this.preload = false
            }
        },
        carregou(dados) {
            this.lista = dados.itens
            this.lista_avaliacoes_tipos = dados.avaliacoes_tipos
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
.card-header {
    background-color: white;
}

.btn-link {
    font-weight: 400;
    color: white;
    text-decoration: none;
}

.btn-link:hover {
    color: #dddddd;
}

</style>
