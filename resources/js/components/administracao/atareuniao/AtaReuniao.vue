<template>
    <div id="componenteAtaReuniao">

        <modal :modal-pai="modal" :titulo="titulo_janela_form_atareuniao" id="janelaFormAtaReuniao"
               :size="65">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preload"><i class="fa fa-spinner fa-pulse"></i>Carregando...</p>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h6 class="text-center"><i class="icon fa fa-check"></i> Cadastrado com sucesso!</h6>
                </div>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12">
                                <label>Local</label>
                                <input class="form-control" v-model="form.local"
                                       onblur="valida_campo_vazio(this,1)" type="text">
                            </div>
                            <div class="col-6">
                                <date-picker formsm label="Data Iníco" v-model="form.data_inicio" :disabled="editando"
                                             :hora="true"></date-picker>
                            </div>
                            <div class="col-6">
                                <date-picker formsm label="Data Fim" v-model="form.data_fim" :disabled="editando"
                                             :hora="true"></date-picker>
                            </div>
                        </div>
                    </fieldset>


                    <button class="btn btn-sm btn-primary mb-3" :disabled="editando" @click="addLIAssuntos">
                        <i class="fa fa-plus"></i> Adicionar Assuntos
                    </button>

                    <fieldset class=" mb-2" v-if="form.assuntos.length > 0"
                              v-for="(obj, index) in form.assuntos" :key="index + 1">
                        <legend>#Assuntos {{ index + 1 }}</legend>
                        <div class="row">
                            <div class="col-12">
                                <label>Assunto</label>
                                <textarea v-model="obj.assunto" :disabled="!obj.novo"
                                          onblur="valida_campo_vazio(this,1)" class="form-control"
                                          rows="3"></textarea>
                            </div>
                            <div class="col-12 mt-3" v-show="obj.novo">
                                <button class="btn btn-sm btn-danger" @click="removerLIAssuntos(index)"><i
                                    class="fa fa-times"></i> Remover
                                </button>

                                <button class="btn btn-sm btn-primary mt" @click="addLIAssuntos" v-show="index >= 1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>


                    <button class="btn btn-sm btn-primary mb-3" :disabled="editando" @click="addLITipos">
                        <i class="fa fa-plus"></i> Adicionar Comentários / Assuntos Pendentes / Próxima Reunião
                    </button>

                    <fieldset class=" mb-2" v-if="form.tipos.length > 0"
                              v-for="(obj, index) in form.tipos" :key="index + 100">
                        <legend>#Tipos {{ index + 1 }}</legend>
                        <div class="row">
                            <div class="col-12">
                                <label>Local</label>
                                <select class="form-control" :disabled="!obj.novo" v-model="obj.tipo"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option value="comentarios">Comentários</option>
                                    <option value="assuntos_pendentes">Assuntos Pendentes</option>
                                    <option value="proxima_reuniao">Próxima Reunião</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label>Comentários / Assuntos Pendentes / Próxima Reunião</label>
                                <textarea v-model="obj.observacao" :disabled="!obj.novo"
                                          onblur="valida_campo_vazio(this,1)"
                                          class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12 mt-3" v-show="obj.novo">
                                <button class="btn btn-sm btn-danger" @click="removerLITipos(index)"><i
                                    class="fa fa-times"></i> Remover
                                </button>

                                <button class="btn btn-sm btn-primary mt" @click="addLITipos" v-show="index >= 1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>


                    <button class="btn btn-sm btn-primary mb-3" :disabled="editando" @click="addLIAcoes">
                        <i class="fa fa-plus"></i> Ações - Próximos passos
                    </button>

                    <fieldset class=" mb-2" v-if="form.acoes.length > 0"
                              v-for="(obj, index) in form.acoes" :key="index + 1000">
                        <legend>#Ações {{ index + 1 }}</legend>
                        <div class="row">
                            <div class="col-12">
                                <label>Responsável</label>
                                <input class="form-control" :disabled="!obj.novo"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-model="obj.responsavel">
                            </div>
                            <div class="col-12">
                                <label>Email</label>
                                <input class="form-control" type="email" :disabled="!obj.novo"
                                       onblur="valida_campo_vazio(this,1)"
                                       v-model="obj.email">
                            </div>

                            <div class="col-12">
                                <label>Contínuo</label>
                                <select class="form-control" :disabled="!obj.novo"
                                        onblur="valida_campo_vazio(this,1)"
                                        v-model="obj.continuo">
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <div class="col-12" v-show="obj.continuo == false">
                                <date-picker formsm label="Prazo" v-model="obj.prazo" :disabled="!obj.novo"></date-picker>
                            </div>

                            <div class="col-12" v-show="!obj.novo">
                                <label>Status</label>
                                <select class="form-control" onblur="valida_campo_vazio(this,1)"
                                        v-model="obj.status">
                                    <option value="andamento">Andamento</option>
                                    <option value="concluido">Concluído</option>
                                    <option value="pendente">Pendente</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label>Ações</label>
                                <textarea v-model="obj.acao" :disabled="!obj.novo" onblur="valida_campo_vazio(this,1)"
                                          class="form-control"
                                          rows="3"></textarea>
                            </div>


                            <div class="col-12 mt-3" v-show="obj.novo">
                                <button class="btn btn-sm btn-danger" @click="removerLIAcoes(index)"><i
                                    class="fa fa-times"></i> Remover
                                </button>

                                <button class="btn btn-sm btn-primary mt" @click="addLIAcoes" v-show="index >= 1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>


                    <button class="btn btn-sm btn-primary mb-3" :disabled="editando" @click="addLIParticipantes">
                        <i class="fa fa-plus"></i> Participantes
                    </button>

                    <fieldset class=" mb-2" v-show="form.participantes.length > 0"
                              v-for="(obj, index) in form.participantes" :key="index + 1500">
                        <legend>#Participantes {{ index + 1 }}</legend>
                        <div class="row">
                            <div class="col-12">

                                <label>Nome</label>
                                <input class="form-control" onblur="valida_campo_vazio(this,1)"
                                       v-model="obj.nome" :disabled="!obj.novo">
                            </div>
                            <div class="col-12">

                                <label>Função</label>
                                <input class="form-control" onblur="valida_campo_vazio(this,1)"
                                       v-model="obj.funcao" :disabled="!obj.novo">
                            </div>

                            <div class="col-12 mt-3" v-show="obj.novo">
                                <button class="btn btn-sm btn-danger" @click="removerLIParticipantes(index)"><i
                                    class="fa fa-times"></i> Remover
                                </button>

                                <button class="btn btn-sm btn-primary mt" @click="addLIParticipantes"
                                        v-show="index >= 1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>


                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando"
                        @click="cadastrar()">
                    Cadastrar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="editando"
                        @click="alterarformAtaReuniao()">
                    Alterar
                </button>
            </template>
        </modal>


        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
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

                <div class="col-12 col-md-12">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                            @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                        Atualizar
                    </button>

                    <button type="button" class="btn btn-sm btn-primary" :disabled="controle.carregando"
                            @click="formNovo"
                            data-toggle="modal"
                            data-target="#janelaFormAtaReuniao">
                        <i class="fa fa-plus"></i> Cadastrar Ata de Reunião
                    </button>
                </div>
            </form>
        </fieldset>

        <div id="conteudo">

            <p class=" mt-2 text-center" v-if="controle.carregando">
                <i class="fa fa-spinner fa-pulse"></i> Carregando...
            </p>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
                <table class="tabela">
                    <thead>
                    <tr class="bg-default">
                        <td class="text-center">Nº</td>
                        <td class="text-center">Quem Cadastrou</td>
                        <td class="text-center">Local</td>
                        <td class="text-center">Data Início</td>
                        <td class="text-center">Data Fim</td>
                        <td class="text-center">Opções</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="atareuniao in lista">
                        <td class="text-center">{{atareuniao.id}}</td>
                        <td class="text-center">{{atareuniao.quem_cadastrou.nome}}</td>
                        <td class="text-center">{{atareuniao.local}}</td>
                        <td class="text-center">{{atareuniao.data_inicio}}</td>
                        <td class="text-center">{{atareuniao.data_fim}}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary"
                                    @click="alterarAtaReuniao(atareuniao.id)"
                                    data-toggle="modal"
                                    title="Editar"
                                    data-target="#janelaFormAtaReuniao">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" title="Gerar PDF" @click="gerarPdf(atareuniao.id)">
                                <i class="fa fa-file-pdf"></i>
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
    import controlePaginacao from '../../ControlePaginacao';
    import modal from '../../Modal';
    import editor from '@tinymce/tinymce-vue';
    import DatePicker from "../../DatePicker";

    export default {
        components: {
            DatePicker,
            modal,
            controlePaginacao,
            editor,
        },
        props: {
            qntPag: {
                type: Number,
                required: false,
                default: 20
            },

            status: {
                type: Boolean,
                required: false,
                default: true
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
            },
        },
        data() {
            return {
                hash: String(Math.random()).substr(2),
                titulo_janela_form_atareuniao: 'Ata Reunião',

                preload: false,
                editando: false,
                cadastrado: false,
                atualizado: false,

                form: {
                    local: '',
                    data_inicio: '',
                    data_fim: '',
                    assuntos: [],
                    assuntosDelete: [],
                    tipos: [],
                    tiposDelete: [],
                    acoes: [],
                    acoesDelete: [],
                    participantes: [],
                    participantesDelete: [],
                },
                formDefault: null,

                lista: [],

                urlPaginacao: `${URL_ADMIN}/administracao/atareuniao/atualizar`,
                controle: {
                    carregando: false,
                    dados: {
                        campoBusca: '',
                        campoFiltro: '',
                    },
                },
            }
        },
        mounted() {
            this.atualizar();
            this.formDefault = _.cloneDeep(this.form);
        },
        methods: {
            addLIAssuntos() {
                const obj = {};
                obj.novo = true;
                obj.assunto = '';

                this.form.assuntos.push(obj);
            },
            removerLIAssuntos(index) {
                if (this.editando) {
                    this.form.assuntosDelete.push(this.form.assuntos[index].id);
                }
                this.form.assuntos.splice(index, 1);
            },
            addLITipos() {
                const obj = {};
                obj.novo = true;
                obj.tipo = '';
                obj.observacao = '';

                this.form.tipos.push(obj);
            },
            removerLITipos(index) {
                if (this.editando) {
                    this.form.tiposDelete.push(this.form.tipos[index].id);
                }
                this.form.tipos.splice(index, 1);
            },
            addLIAcoes() {
                const obj = {};
                obj.novo = true;
                obj.responsavel = '';
                obj.email = '';
                obj.prazo = '';
                obj.continuo = '';
                obj.acao = '';
                obj.observacao = '';

                this.form.acoes.push(obj);
            },
            removerLIAcoes(index) {
                if (this.editando) {
                    this.form.acoesDelete.push(this.form.acoes[index].id);
                }
                this.form.acoes.splice(index, 1);
            },
            addLIParticipantes() {
                const obj = {};
                obj.novo = true;
                obj.nome = '';
                obj.funcao = '';

                this.form.participantes.push(obj);
            },
            removerLIParticipantes(index) {
                if (this.editando) {
                    this.form.participantesDelete.push(this.form.participantes[index].id);
                }
                this.form.participantes.splice(index, 1);
            },
            formNovo() {
                this.form = _.cloneDeep(this.formDefault) //copia
                this.titulo_janela_form_atareuniao = 'Cadastro de Ata Reunião';
                this.cadastrado = false;
                this.finalizado = false;
                this.atualizado = false;
                this.editando = false;

                formReset();
                setupCampo();
            },
            cadastrar() {
                $('#janelaFormAtaReuniao :input:visible').trigger('blur');
                if ($('#janelaFormAtaReuniao :input:visible.is-invalid').length) {
                    mostraErro('', 'Verificar os erros');
                    return false;
                }
                this.preload = true;
                axios.post(`${URL_ADMIN}/administracao/atareuniao`, this.form)
                    .then(res => {
                        if (res.status === 201) {
                            $('#janelaFormAtaReuniao').modal('hide');
                            mostraSucesso('', 'Ata Reunião Cadastrado com sucesso');
                            this.preload = false;
                            this.cadastrado = true;
                            this.atualizar();
                        } else {
                            this.cadastrado = false;
                            this.preload = false;
                        }
                    })
                    .catch(error => {
                        this.cadastrado = false;
                        this.preload = false;
                    });
            },
            alterarAtaReuniao(atareuniao) {
                this.cadastrado = false;
                this.editando = true;
                this.titulo_janela_form_atareuniao = "Alterando Ata Reunião";
                formReset();

                this.form = _.cloneDeep(this.formDefault) //copia

                axios.get(`${URL_ADMIN}/administracao/atareuniao/${atareuniao}/editar`)
                    .then(response => {
                        Object.assign(this.form, response.data);
                        this.editando = true;
                        setupCampo();
                    }).catch(
                    error => (this.preloadAjax = false)
                );

            },
            alterarformAtaReuniao() {
                formReset();
                $('#janelaFormAtaReuniao :input:enabled').trigger('blur');

                if ($('#janelaFormAtaReuniao :input:enabled.is-invalid').length) {
                    mostraErro('', 'Verificar os erros');
                    return false;
                }

                this.preloadAjax = true;

                axios.put(`${URL_ADMIN}/administracao/atareuniao/${this.form.id}`, this.form).then(response => {
                    $('#janelaFormAtaReuniao').modal('hide');
                    mostraSucesso('', 'Ata Reunião Editado com sucesso');
                    this.preloadAjax = false;
                    this.controle.carregando = true;
                    this.atualizado = true;
                    this.atualizar();
                }).catch(error => (this.preloadAjax = false));

            },
            gerarPdf(item) {
                let link = `${URL_ADMIN}/administracao/atareuniao/pdf/${item}`;
                open(link, 'blank');
            },
            carregou(dados) {
                this.lista = dados.items;
                this.controle.carregando = false;
            },
            carregando() {
                this.controle.carregando = true;
            },
            atualizar() {
                this.$refs.componente.atual = 1;
                this.$refs.componente.buscar();
            },
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
