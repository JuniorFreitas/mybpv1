<template>
    <div id="componenteOcorrencia">
        <modal :fechar="!preload" id="janelaMudaSetor" modal-pai="janelaOcorrencia" titulo="Mudar Setor">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preload">
                    <preload></preload>
                </p>
                <div v-if="!preload && !cadastrado">
                    <fieldset>
                        <legend>Mudar Setor</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text">Setor</span>
                                </span>
                                        <select v-model="camposMudarSetor.setor_id"
                                                onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)" class="custom-select">
                                            <option v-for="item in setores" :value="item.id">{{ item.nome }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !cadastrado"
                        @click="janelaConfirmar"
                        data-toggle="modal"
                        data-target="#janelaConfirmarMudaSetor">
                    Mudar Setor
                </button>
            </template>
        </modal>

        <modal :fechar="!preload" id="janelaConfirmarMudaSetor" modal-pai="janelaMudaSetor"
               titulo="Confirmar Mudança de Setor">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preload">
                    <preload label="Mudando setor aguarde ..."></preload>
                </p>
                <div class="alert alert-success alert-dismissible" v-show="mudado">
                    <h6 class="text-center"><i class="icon fa fa-check"></i> Setor mudado com sucesso!</h6>
                </div>
                <h6 class="text-center" v-show="!mudado && !preload">Você tem certeza que deseja mudar de setor?</h6>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-success" @click="mudarSetor()" v-show="!mudado">Sim</button>
            </template>
        </modal>

        <modal :fechar="!preload" id="janelaConfirmarFinalizar" modal-pai="janelaOcorrencia"
               titulo="Confirmar Mudança de Setor">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preload">
                    <preload label="Finalizando a ocorrência aguarde ..."></preload>
                <div class="alert alert-success alert-dismissible" v-show="finalizado">
                    <h6 class="text-center"><i class="icon fa fa-check"></i> Ocorrência finalizada com sucesso!</h6>
                </div>
                <h6 class="text-center" v-show="!finalizado && !preload">Você tem certeza que deseja Finalizar essa
                    Ocorrência?</h6>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-success" @click="finalizarOcorrencia" v-show="!finalizado">
                    Sim
                </button>
            </template>
        </modal>

        <modal :modal-pai="modal" :titulo="titulo_janela_form_ocorrencia" id="janelaFormOcorrencia"
               :size="65">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preload">
                    <preload></preload>
                </p>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h6 class="text-center"><i class="icon fa fa-check"></i> Mensagem cadastrada com sucesso!</h6>
                </div>
                <div v-if="!preload && !cadastrado">

                    <div v-show="!nova_mensagem">
                        <fieldset>
                            <legend>OCORRÊNCIA PARA</legend>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Tipo</label>
                                        <select v-model="form.tipo_ocorrencia" class="form-control"
                                                onblur="valida_campo_vazio(this,1)">
                                            <option value="">Selecione</option>
                                            <option value="cliente">Cliente</option>
                                            <option value="usuario">Usuário</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6" v-if="form.tipo_ocorrencia === 'cliente'">
                                    <div class="form-group">
                                        <label>
                                            Cliente
                                        </label>
                                        <autocomplete :caminho="caminho_cliente_autocomplete"
                                                      :valido="form.cliente_id !== ''"
                                                      v-model="form.autocomplete_label_cliente_modal"
                                                      placeholder="Digite o nome do cliente"
                                                      @onblur="resetaCampoClienteModal"
                                                      @onselect="selecionaClienteModal"></autocomplete>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6" v-if="form.tipo_ocorrencia === 'usuario'">
                                    <div class="form-group">
                                        <label>Usuário</label>
                                        <autocomplete :caminho="caminho_usuario_autocomplete"
                                                      :valido="form.usuario_id !== ''"
                                                      v-model="form.autocomplete_label_usuario_modal"
                                                      placeholder="Digite o nome do usuário"
                                                      @onblur="resetaCampoUsuarioModal"
                                                      @onselect="selecionaUsuarioModal"></autocomplete>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <fieldset>
                        <legend>MENSAGEM</legend>
                        <div class="row">
                            <div v-show="!nova_mensagem" class="col-12">
                                <div class="form-group">
                                    <div class="input-group">
                                            <span class="input-group-prepend">
                                                <span class="input-group-text">Assunto</span>
                                            </span>
                                        <input type="text" v-model="form.assunto"
                                               onblur="valida_campo_vazio(this,3)"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div v-show="!nova_mensagem" class="col-12 col-sm-4">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text">Setor</span>
                                </span>
                                        <select v-model="form.setor_id" onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)" class="custom-select">
                                            <option value="">Selecione</option>
                                            <option v-for="item in setores" :value="item.id">
                                                {{ item.nome }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div v-show="!nova_mensagem" class="col-12 col-sm-4">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text">Tipo</span>
                                </span>
                                        <select v-model="form.tipo" onchange="valida_campo_vazio(this,1)"
                                                onblur="valida_campo_vazio(this,1)" class="custom-select">
                                            <option value="">Selecione</option>
                                            <option value="anotacao">Anotação</option>
                                            <option value="problema">Problema</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div v-show="!nova_mensagem" class="col-12 col-sm-4">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text">Tag</span>
                                </span>
                                        <select v-model="form.tag_id" class="custom-select">
                                            <option value="">Selecione</option>
                                            <option v-for="tag in tags" :value="tag.id">{{ tag.nome }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <div class="input-group">
                                        <editor :api-key="config.key" onblur="valida_campo_vazio(this,1)"
                                                v-model="form.resposta" :init="config"></editor>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>ANEXO(S)</legend>
                        <upload label="Selecionar anexo(s)" :model="form.anexos"
                                :model-delete="form.anexosDel" :url="urlAnexoUpload"
                                @onprogresso="anexoUploadAndamento=true"
                                @onfinalizado="anexoUploadAndamento=false"></upload>

                    </fieldset>

                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="nova_mensagem && !cadastrado"
                        @click="cadastrarNovaMensagem()">
                    Cadastrar Mensagem
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!nova_mensagem && !cadastrado"
                        @click="cadastrar()">
                    Cadastrar
                </button>
            </template>
        </modal>

        <modal :fechar="!preload" :modal-pai="modal" :size="68" :titulo="titulo_janela" id="janelaOcorrencia">
            <template slot="conteudo">
                <p class="mt-2 text-center" v-if="preload">
                    <preload></preload>
                </p>
                <div v-show="!preload" v-if="exibindo">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="exibi-ocorrencia-tab" data-toggle="tab"
                               href="#exibi-ocorrencia"
                               role="tab" aria-controls="exibi-ocorrencia" aria-selected="true">Ocorrência</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="msg-ocorrencia-tab" data-toggle="tab" href="#msg-ocorrencia"
                               role="tab"
                               aria-controls="msg-ocorrencia" aria-selected="false">Mensagens</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="exibi-ocorrencia" role="tabpanel"
                             aria-labelledby="exibi-ocorrencia-tab" style="margin-bottom: -15px">
                            <div class="col-12 alert" :class="{
                            'alert-danger': ocorrencia.status === 'novo',
                            'alert-warning': ocorrencia.status === 'andamento',
                            'alert-success': ocorrencia.status === 'finalizado' && ocorrencia.tipo === 'problema',
                            }">
                                <p style="line-height: 30px;">
                                    <strong>Ocorrência Nº:</strong> {{ ocorrencia.id }}<br>
                                    <strong>Assunto:</strong> {{ ocorrencia.assunto }}<br>
                                    <strong>Setor:</strong> {{ ocorrencia.setor.nome }}<br>
                                    <span
                                        v-if="ocorrencia.cliente_id"><strong>Cliente: </strong>
                                        {{ ocorrencia.cliente.nome_fantasia }}<br>
                                    </span>
                                    <span
                                        v-if="ocorrencia.usuario_id"><strong>Usuário: </strong>
                                        {{ ocorrencia.usuario.nome }}<br>
                                    </span>
                                    <strong>Criado em:</strong> {{ ocorrencia.created_at }}
                                    <small class="text-muted">(por {{ ocorrencia.criou.nome }})</small>
                                    <br>
                                    <span v-if="ocorrencia.status === 'finalizado' && ocorrencia.tipo === 'problema'">
                                        <strong>Finalizado em:</strong> {{ ocorrencia.datahora_finalizou }}
                                        <small class="text-muted">(por {{ ocorrencia.finalizou.nome }})</small><br>
                                    </span>
                                    <br>
                                    <small class="text-default">
                                        <strong v-if="ocorrencia.tags.length">Tag:</strong>
                                        {{ ocorrencia.tags.length ? ocorrencia.tags[0].nome + ' | ' : null }}
                                        <strong>Tipo:</strong>
                                        {{ ocorrencia.tipo }}
                                    </small>
                                </p>
                            </div>
                        </div>

                        <div class="tab-pane" id="msg-ocorrencia" role="tabpanel"
                             aria-labelledby="msg-ocorrencia-tab">
                            <div class="col-12 mt-2" v-show="ocorrencia.status !== 'finalizado'">

                                <button type="button" class="btn btn-sm btn-primary" :disabled="preloadMsg"
                                        @click="formNovaMensagem"
                                        data-toggle="modal"
                                        data-target="#janelaFormOcorrencia">
                                    Nova Mensagem
                                </button>

                                <button type="button" class="btn btn-sm btn-success" :disabled="preloadMsg"
                                        @click="getMsg(ocorrencia.id)"><i
                                    :class="preloadMsg ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                                    Atualizar
                                </button>

                            </div>

                            <p class="mt-2 text-center" v-if="preloadMsg">
                                <preload></preload>
                            </p>

                            <div class="col-12 mt-2" v-show="!preloadMsg">
                                <ul class="timeline">
                                    <li v-for="item in ocorrencia.respostas">
                                        <div class="trackind">
                                            <span><strong
                                                class="text-default">{{ item.usuario.nome }}</strong> diz:</span>
                                            <small class="float-right text-muted">{{ item.updated_at }}</small>
                                            <p v-html="item.resposta"></p>

                                            <fieldset v-show="item.anexos.length > 0">
                                                <legend>ANEXO(S)</legend>
                                                <ul>
                                                    <li v-for="anexo in item.anexos">
                                                        <a :href="anexo.urlDownload" class="float-left">
                                                            <i class="fa fa-paperclip"></i>
                                                            {{ anexo.nome }}{{ anexo.extensao }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </fieldset>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>

                    <div class="col-12 mt-2" v-show="ocorrencia.status != 'finalizado'">
                        <button type="button" class="btn btn-sm btn-secondary" :disabled="finalizado"
                                @click="formMudarSetor"
                                data-toggle="modal"
                                data-target="#janelaMudaSetor">
                            Mudar Setor
                        </button>

                        <button type="button" class="btn btn-sm btn-secondary" :disabled="finalizado"
                                @click="janelaConfirmarFinalizar"
                                data-toggle="modal"
                                data-target="#janelaConfirmarFinalizar">
                            Finalizar Ocorrência
                        </button>

                    </div>
                </div>
            </template>
            <template slot="rodape">

            </template>
        </modal>

        <modal :modal-pai="modal" :titulo="titulo_janela_form_tag" :fechar="!preloadTag" id="janelaFormTag">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preloadTag">
                    <preload></preload>
                </p>
                <div v-if="!preloadTag && !cadastrado">
                    <fieldset>
                        <legend>Cadastro de Tag</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text">Nome</span>
                                </span>
                                        <input class="form-control" type="text"
                                               onblur="valida_campo_vazio(this,1)" v-model="formTag.nome">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="!cadastrado && !preloadTag"
                        @click="cadastraTag">
                    <i class="fa fa-save"></i>Cadastrar
                </button>
            </template>
        </modal>

        <modal :modal-pai="modal" :titulo="titulo_janela_form_setor" :fechar="!preloadSetor" id="janelaFormSetor">
            <template slot="conteudo">
                <p class=" mt-2 text-center" v-if="preloadSetor">
                    <preload></preload>
                </p>
                <div v-if="!preloadSetor && !cadastrado">
                    <fieldset>
                        <legend>Cadastro de Setor</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text">Nome</span>
                                </span>
                                        <input class="form-control" type="text"
                                               onblur="valida_campo_vazio(this,1)" v-model="formSetor.nome">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="!cadastrado && !preloadSetor"
                        @click="cadastraSetor">
                    <i class="fa fa-save"></i> Cadastrar
                </button>
            </template>
        </modal>

        <!-- Filtro -->
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente.buscar()">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                        <span class="input-group-prepend">
                            <span class="input-group-text">Buscar</span>
                        </span>
                            <input type="text"
                                   placeholder="Buscar por conteudo"
                                   autocomplete="off"
                                   class="form-control" :disabled="controle.carregando"
                                   v-model="controle.dados.campoBusca">
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                        <span class="input-group-prepend">
                            <span class="input-group-text">Setor</span>
                        </span>

                            <select class="custom-select" @change="atualizar" :disabled="controle.carregando"
                                    v-model="controle.dados.campoSetor">
                                <option value="">Todos os setores</option>
                                <option v-for="item in setores" :value="item.id">{{ item.nome }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                        <span class="input-group-prepend">
                            <span class="input-group-text">Tag</span>
                        </span>

                            <select class="custom-select" @change="atualizar" :disabled="controle.carregando"
                                    v-model="controle.dados.campoTag">
                                <option value="">Todas as tags</option>
                                <option v-for="item in tags" :value="item.id">{{ item.nome }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                        <span class="input-group-prepend">
                            <span class="input-group-text">Tipo</span>
                        </span>

                            <select class="custom-select" @change="atualizar"
                                    :disabled="controle.carregando || controle.dados.campoStatus !== '' "
                                    v-model="controle.dados.campoTipo">
                                <option value="">Todos os tipos</option>
                                <option value="anotacao">Anotação</option>
                                <option value="problema">Problema</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3" v-show="controle.dados.campoTipo !== 'anotacao'">
                    <div class="form-group">
                        <div class="input-group">
                        <span class="input-group-prepend">
                            <span class="input-group-text">Status</span>
                        </span>

                            <select class="custom-select" @change="mudaTipo" :disabled="controle.carregando"
                                    v-model="controle.dados.campoStatus">
                                <option value="">Todos os status</option>
                                <option value="novo">Novo</option>
                                <option value="andamento">Andamento</option>
                                <option value="finalizado">Finalizado</option>
                            </select>
                        </div>
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
                            data-target="#janelaFormOcorrencia">
                        <i class="fa fa-plus"></i> Nova Ocorrência
                    </button>

                    <button type="button" class="btn btn-sm btn-secondary" :disabled="controle.carregando"
                            v-if="permissaoTag"
                            @click="formNovoTag"
                            data-toggle="modal"
                            data-target="#janelaFormTag">
                        <i class="fa fa-plus"></i> Cadastrar Tag
                    </button>

                    <button type="button" class="btn btn-sm btn-secondary" :disabled="controle.carregando"
                            v-if="permissaoSetor"
                            @click="formNovoSetor"
                            data-toggle="modal"
                            data-target="#janelaFormSetor">
                        <i class="fa fa-plus"></i> Cadastrar Setor
                    </button>
                </div>
            </form>
        </fieldset>

        <!-- Legenda -->
        <div class="col-12 mb-2 mt-2 pt-1 pb-1" style="background: rgba(220,222,210,0.5)"
             v-show="!controle.carregando && lista.length > 0">
            <span style="margin-left: -17px; font-size: .9rem;">
                <i class="fas fa-circle text-white ml-2"></i> Anotação
                <i class="fas fa-circle text-danger ml-2"></i> Nova ocorrência
                <i class="fas fa-circle text-warning ml-2"></i> Ocorrência em andamento
                <i class="fas fa-circle  text-success ml-2"></i> Ocorrência finalizada
            </span>
        </div>

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
                        <td class="text-center">Nº</td>
                        <td class="text-center">Assunto</td>
                        <td class="text-center">Status</td>
                        <td class="text-center">Última Atualização</td>
                        <td class="text-center">Criado em</td>
                        <td class="text-center">Nome do Usuário</td>
                        <td class="text-center">Exibir</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in lista"
                        :class="{
                        'table-danger': item.status === 'novo',
                        'table-warning': item.status === 'andamento',
                        'table-success': item.status === 'finalizado' && item.tipo === 'problema',
                        }"
                    >
                        <td class="text-center">{{ item.id }}</td>
                        <td class="text-center">{{ item.assunto }}</td>
                        <td class="text-center">{{ item.status }}</td>
                        <td class="text-center">{{ item.updated_at }}</td>
                        <td class="text-center">{{ item.created_at }}</td>
                        <td class="text-center">{{ item.criou.nome }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary mb-1" data-toggle="modal"
                                    data-target="#janelaOcorrencia" @click="formExibir(item.id)">
                                <i class="fa fa-search-plus"></i> Exibir
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
import controlePaginacao from '../ControlePaginacao';
import modal from '../Modal';
import upload from '../Upload';
import editor from '@tinymce/tinymce-vue';
import configTinyMCE from '../configTinyMCE';
import autocomplete from "../AutoComplete";

export default {
    components: {
        modal,
        controlePaginacao,
        upload,
        editor,
        autocomplete
    },
    props: {
        qntPag: {
            type: Number,
            required: false,
            default: 20
        },

        setor: {
            type: Boolean,
            required: false,
            default: true
        },

        tipo: {
            type: Boolean,
            required: false,
            default: true
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

    computed: {
        classObject() {
            return {
                active: this.isActive && !this.error,
                'text-danger': this.error && this.error.type === 'fatal'
            }
        },
        permissaoTag() {
            return this.permissoes.indexOf('ocorrencia_tag') > -1;
        },
        permissaoSetor() {
            return this.permissoes.indexOf('ocorrencia_setor') > -1;
        }
    },

    mounted() {
        this.listaSetoresTags();
        this.atualizar();
        this.formDefault = _.cloneDeep(this.form);
        this.camposMudarSetorDefault = _.cloneDeep(this.camposMudarSetor);
        this.camposFinalizarDefault = _.cloneDeep(this.camposFinalizar);
        this.formTagDefault = _.cloneDeep(this.formTag);
        this.formSetorDefault = _.cloneDeep(this.formSetor);
    },
    data() {
        return {
            hash: String(Math.random()).substr(2),
            config: configTinyMCE,
            titulo_janela: '',
            titulo_janela_form_ocorrencia: '',
            titulo_janela_form_tag: '',
            titulo_janela_form_setor: '',

            anexoUploadAndamento: false,
            urlAnexoUpload: `${URL_ADMIN}/ocorrencia/uploadAnexos`,

            preload: true,
            preloadMsg: true,
            preloadTag: true,
            preloadSetor: true,
            editando: false,
            nova_mensagem: false,
            cadastrado: false,
            mudado: false,
            finalizado: false,
            setores: [],
            tags: [],

            ocorrencia: null,
            exibindo: false,
            mensagem: false,

            camposFinalizar: {
                _method: '',
                status: 'finalizado',
                ocorrencia_id: '',
            },
            camposFinalizarDefault: null,

            camposMudarSetor: {
                setor_id: '',
                ocorrencia_id: '',
            },
            camposMudarSetorDefault: null,

            form: {
                _method: '',
                ocorrencia_id: '',

                cliente_id: '',
                autocomplete_label_cliente_modal: '',
                autocomplete_label_cliente_modal_anterior: '',

                usuario_id: '',
                autocomplete_label_usuario_modal: '',
                autocomplete_label_usuario_modal_anterior: '',

                tipo_ocorrencia: '',
                setor_id: '',
                assunto: '',
                quem_criou: '',
                quem_atualizou: '',
                datahora_finalizou: '',
                quem_finalizou: '',
                status: '',
                tipo: '',
                resposta: '', //dentro de ocorrencias_respostas

                tag_id: '',

                anexos: [],
                anexosDel: [],
            },
            formDefault: null,
            caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
            caminho_usuario_autocomplete: `autocomplete/todos-usuarios-ativos`,


            formTag: {
                nome: '',
            },
            formTagDefault: null,

            formSetor: {
                nome: '',
            },
            formSetorDefault: null,

            //Paginacao
            lista: [],
            permissoes: [],

            urlPaginacao: `${URL_ADMIN}/ocorrencia/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoSetor: '',
                    campoTag: '',
                    campoTipo: '',
                    campoStatus: '',
                    campoFiltro: '',
                },
            },
        }
    },
    methods: {
        listaSetoresTags() {
            this.preload = true;
            axios.get(`${URL_ADMIN}/ocorrencia/listaSetoresTags`)
                .then((res) => {
                    let data = res.data;
                    this.setores = data.setores;
                    this.tags = data.tags;
                    this.preload = false;
                })
                .catch((error) => {
                    this.preload = false;
                });
        },

        selecionaClienteModal(obj) {
            this.form.cliente_id = obj.id;
            this.form.autocomplete_label_cliente_modal = obj.label;
            this.form.autocomplete_label_cliente_modal_anterior = obj.label;
        },
        resetaCampoClienteModal() {
            if (this.form.autocomplete_label_cliente_modal_anterior !== this.form.autocomplete_label_cliente_modal) {
                this.form.autocomplete_label_cliente_modal_anterior = '';
                this.form.autocomplete_label_cliente_modal = '';
                this.form.cliente_id = '';

                setTimeout(() => {
                    if (this.form.cliente_id === '') {
                        valida_campo_vazio($('#cliente_modal_' + this.hash), 1);
                        $('#janelaCadastrar #cliente_modal_' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Cliente não pode ficar vazio');
                    }
                }, 100);

            }

        },

        selecionaUsuarioModal(obj) {
            this.form.usuario_id = obj.id;
            this.form.autocomplete_label_usuario_modal = obj.label;
            this.form.autocomplete_label_usuario_modal_anterior = obj.label;
        },
        resetaCampoUsuarioModal() {
            if (this.form.autocomplete_label_usuario_modal_anterior !== this.form.autocomplete_label_usuario_modal) {
                this.form.autocomplete_label_usuario_modal_anterior = '';
                this.form.autocomplete_label_usuario_modal = '';
                this.form.usuario_id = '';

                setTimeout(() => {
                    if (this.form.usuario_id === '') {
                        valida_campo_vazio($('#usuario_modal_' + this.hash), 1);
                        $('#janelaCadastrar #usuario_modal_' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Usuário não pode ficar vazio');
                    }
                }, 100);

            }

        },

        formExibir(id) {
            $('#myTab li:first a').tab('show');
            this.titulo_janela = 'Ocorrencia #' + id;
            formReset();
            this.exibindo = false;
            this.preload = true;
            this.finalizado = false;
            this.getMsg(id);
        },
        formNovo() {
            this.form = _.cloneDeep(this.formDefault) //copia
            this.titulo_janela_form_ocorrencia = 'Nova Ocorrência';
            this.cadastrado = false;
            this.finalizado = false;
            this.atualizado = false;
            this.nova_mensagem = false;

            formReset();
            setupCampo();
        },
        cadastrar() {
            $('#janelaFormOcorrencia :input:visible').trigger('blur');
            if ($('#janelaFormOcorrencia :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }
            this.preloadTag = true;
            axios.post(`${URL_ADMIN}/ocorrencia`, this.form)
                .then(res => {
                    if (res.status === 201) {
                        this.preloadTag = false;
                        this.cadastrado = true;
                        this.atualizar();
                    } else {
                        this.cadastrado = false;
                        this.preloadTag = false;
                    }
                })
                .catch(error => {
                    this.cadastrado = false;
                    this.preloadTag = false;
                });
        },

        formNovoTag() {
            this.formTag = _.cloneDeep(this.formTagDefault) //copia
            this.titulo_janela_form_tag = 'Nova Tag';
            this.preloadTag = false;
            this.cadastrado = false;
            this.atualizado = false;
            formReset();
        },
        cadastraTag() {
            $('#janelaFormTag :input:visible').trigger('blur');
            if ($('#janelaFormTag :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }
            this.preloadTag = true;
            axios.post(`${URL_ADMIN}/ocorrencia/cadastro-tag`, this.formTag)
                .then((res) => {
                    $('#janelaFormTag').modal('hide');
                    mostraSucesso('', 'Tag cadastrada com sucesso');
                    this.cadastrado = true;
                    this.$refs.componente.buscar();
                    this.preloadTag = false;
                })
                .catch(error => {
                    this.cadastrado = false;
                    this.preloadTag = false;
                });
        },
        formNovoSetor() {
            this.formSetor = _.cloneDeep(this.formSetorDefault);
            this.titulo_janela_form_setor = 'Novo Setor';
            this.preloadSetor = false;
            this.cadastrado = false;
            this.atualizado = false;
            formReset();
        },
        cadastraSetor() {
            $('#janelaFormSetor :input:visible').trigger('blur');
            if ($('#janelaFormSetor :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }
            this.preloadSetor = true;
            axios.post(`${URL_ADMIN}/ocorrencia/cadastro-setor`, this.formSetor)
                .then(res => {
                    if (res.status === 201) {
                        $('#janelaFormSetor').modal('hide');
                        mostraSucesso('', 'Setor cadastrado com sucesso');
                        this.cadastrado = true;
                        this.$refs.componente.buscar();
                        this.preloadSetor = false;
                    } else {
                        this.cadastrado = false;
                        this.preloadSetor = false;
                    }
                })
                .catch((error) => {
                    this.cadastrado = false;
                    this.preloadSetor = false;
                });
        },
        formNovaMensagem() {
            this.form = _.cloneDeep(this.formDefault);
            this.titulo_janela_form_ocorrencia = 'Nova Mensagem';
            this.nova_mensagem = true;
            this.cadastrado = false;
            this.finalizado = false;
            this.atualizado = false;
            this.form.ocorrencia_id = this.ocorrencia.id;
        },

        cadastrarNovaMensagem() {
            this.preload = true;
            axios.post(`${URL_ADMIN}/ocorrencia/nova_mensagem`, this.form)
                .then(res => {
                    if (res.status === 201) {
                        this.preload = false;
                        this.cadastrado = true;
                        this.getMsg(this.ocorrencia.id);
                        this.atualizar();
                    } else {
                        this.preload = false;
                        this.cadastrado = false;
                    }
                })
                .catch(error => {
                    this.cadastrado = false;
                    this.preload = false;
                });
        },

        getMsg(id) {
            this.preloadMsg = true;
            axios.get(`${URL_ADMIN}/ocorrencia/exibir/${id}`)
                .then(res => {
                    let data = res.data;

                    this.ocorrencia = data;
                    this.preloadMsg = false;
                    this.preload = false;
                    this.exibindo = true;
                }).catch(erro => {
                this.exibindo = false;
                this.preloadMsg = false;
            });
        },

        formMudarSetor() {
            this.camposMudarSetor = _.cloneDeep(this.camposMudarSetorDefault);
            this.mudado = false;
            this.cadastrado = false;
            this.camposMudarSetor.ocorrencia_id = this.ocorrencia.id;
            this.camposMudarSetor.setor_id = this.ocorrencia.setor_id;
        },

        janelaConfirmar() {
            this.mudado = false;
            this.preload = false;
        },

        mudarSetor() {
            this.preload = true;
            axios.post(`${URL_ADMIN}/ocorrencia/mudar_setor`, this.camposMudarSetor)
                .then(res => {
                    if (res.status === 201) {
                        this.getMsg(this.ocorrencia.id);
                        // this.atualizar();
                        $('#janelaMudaSetor').modal('hide');
                        this.preload = false;
                        this.mudado = true;
                    } else {
                        this.mudado = false;
                        this.preload = false;
                    }
                })
                .catch(error => {
                    this.mudado = false;
                    this.preload = false;
                });
        },

        janelaConfirmarFinalizar() {
            this.mudado = false;
            this.preload = false;
        },

        finalizarOcorrencia() {
            this.camposFinalizar = _.cloneDeep(this.camposFinalizarDefault);
            this.finalizado = false;
            this.camposFinalizar.ocorrencia_id = this.ocorrencia.id;

            this.preload = true;
            axios.post(`${URL_ADMIN}/ocorrencia/finalizar`, this.camposFinalizar)
                .then(res => {
                    if (res.status === 201) {
                        this.getMsg(this.ocorrencia.id);
                        this.atualizar();
                        this.preload = false;
                        this.finalizado = true;
                        $('#janelaMudaSetor').modal('hide');
                    } else {
                        this.finalizado = false;
                        this.preload = false;
                    }
                })
                .catch(error => {
                    this.finalizado = false;
                    this.preload = false;
                });
        },

        carregou(dados) {
            this.lista = dados.items;
            this.permissoes = dados.permissoes;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        },
        mudaTipo() {
            if (this.controle.dados.campoStatus !== '') {
                this.controle.dados.campoTipo = 'problema';
            }
            this.atualizar();
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
