<template>
    <div>
        <modal id="janelaCadastrarPasta" :titulo="tituloNovaPasta" size="g" :fechar="!preload_pasta">
            <template slot="conteudo">
                <div class="alert alert-success text-center" v-show="cadastrado">
                    <h5><i class="icon fa fa-check"></i> Pasta criada com sucesso!</h5>
                </div>

                <div class="alert alert-success text-center" v-show="atualizado">
                    <h5><i class="icon fa fa-check"></i> Alteração realizada com sucesso!</h5>
                </div>
                <p class=" mt-2 text-center" v-show="preload_pasta">
                    <preload></preload>
                </p>
                <div v-if="!preload_pasta && (!cadastrado && !atualizado)">
                    <div v-if="!preload_pasta">
                        <div class="row">
                            <div class="col-12">
                                <fieldset>
                                    <legend>NOME</legend>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" class="form-control" v-model="form.label"
                                                   placeholder="Nome"
                                                   onblur="valida_campo_vazio(this,1)">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="col-12">
                                <fieldset>
                                    <legend>GRUPOS PERMITIDOS</legend>

                                    <div class="table-responsive">
                                        <table class="table table-bordered bg-white">
                                            <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Descrição</th>
                                                <th class="text-center">
                                                    <a class="btn btn-sm btn-success" href="javascript://"
                                                       @click.prevent="selecionarTodos" v-if="!form.todosGrupos">
                                                        <span class="fa fa-ok" aria-hidden="true"></span> Todos
                                                    </a>
                                                    <a class="btn btn-sm btn-danger" href="javascript://"
                                                       @click.prevent="selecionarTodos" v-if="form.todosGrupos">
                                                        <span class="fa fa-remove" aria-hidden="true"></span> Nenhum
                                                    </a>
                                                </th>
                                            </tr>
                                            </thead>

                                            <tbody>

                                            <tr v-for="grupo in grupos">
                                                <td>{{ grupo.nome }}</td>
                                                <td>{{ grupo.descricao }}</td>
                                                <td class="text-center">
                                                    <a class="btn btn-sm btn-success" href="#"
                                                       @click.prevent="grupo.permitido = !grupo.permitido; removePermissao(grupo)"
                                                       v-if="grupo.permitido">
                                                        <span class="fa fa-ok" aria-hidden="true"></span> Permitido
                                                    </a>
                                                    <a class="btn btn-sm btn-danger" href="#"
                                                       @click.prevent="grupo.permitido = !grupo.permitido; adicionaPermissao(grupo)"
                                                       v-if="!grupo.permitido">
                                                        <span class="fa fa-remove" aria-hidden="true"></span> Negado
                                                    </a>
                                                </td>

                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>

                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template slot="rodape">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !atualizado && (!preload_pasta)"
                        @click="alterarPasta()">
                    Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !cadastrado && (!preload_pasta)"
                        @click="criaPasta">
                    <i class="far fa-save"></i> Salvar
                </button>
            </template>
        </modal>
        <modal id="janelaVisualizadora" :size="90" :titulo="titulojanelavisualizar">
            <template slot="conteudo">
                <div class="col-12" v-if="exibindo && exibindo.imagem">
                    <img :src="exibindo.url" class="img-fluid d-flex mx-auto">
                </div>
                <div class="row" v-if="exibindo && !exibindo.imagem">
                    <div id="quadrado" class="preview-eyer">
                        <i class="fa fa-eye"></i></div>
                    <iframe :src="`https://docs.google.com/viewer?url=${exibindo.url}?file=fdg46fgd&embedded=true`"
                            v-show="!exibindo.imagem" frameborder="0" style="height: 70vh; width: 100%"></iframe>
                </div>
            </template>
            <template slot="rodape">
                <!--                <a :href="urlBase+srcDownload" download class="btn btn-sm btn-outline-default"><i class="fa fa-download"></i> Download</a>-->
            </template>
        </modal>
        <modal id="janelaDetalhes" titulo="Detalhes">
            <template slot="conteudo">
                <fieldset v-if="detalhes">
                    <legend>Expecificações</legend>
                    <p style="font-size: 11pt;">
                        Nome: {{ detalhes.label }}{{ detalhes.arquivo.extensao }} <br>
                        Tamanho: {{ detalhes.arquivo.bytes | formatBytes }} <br>
                        Data Criação: {{ detalhes.created_at }} <br>
                        Lançado por: {{ detalhes.criou.nome }} <br>
                        <span v-if="detalhes.editou">Atualizado por: {{ detalhes.editou.nome }} <br>
        Data da Atualização: {{ detalhes.updated_at }}
        </span>
                    </p>
                </fieldset>
            </template>
        </modal>

        <!-- Modal encaminhamento de e-mail -->
        <modal id="janelaEnviarRevisao" :fechar="!formEnviarRevisao.preload" titulo="Enviar para Revisão">
            <template slot="conteudo">
            <span v-show="formEnviarRevisao.preload">
                <preload label="Enviando ..."></preload>
            </span>
                <div class="alert alert-success alert-dismissible" v-show="formEnviarRevisao.enviado">
                    <h5>
                        <i class="icon fa fa-check"></i>
                        Item enviado com sucesso!
                    </h5>
                </div>
                <fieldset v-show="!formEnviarRevisao.enviado && !formEnviarRevisao.preload">
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            Arquivo: <p>{{ formEnviarRevisao.item }}</p>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" onblur="valida_campo_vazio(this,1)" class="form-control"
                                       v-model="formEnviarRevisao.nome">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="text" onblur="validaEmailVazio(this)" class="form-control"
                                       v-model="formEnviarRevisao.email">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Mensagem</label>
                                <textarea class="form-control" v-model="formEnviarRevisao.texto_livre" rows="3"
                                          cols="3"></textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <div v-show="!formEnviarRevisao.preload">
                    <button type="button" class="btn btn-sm btn-primary"
                            @click="enviarRevisao"
                            v-show="!formEnviarRevisao.enviado">
                        <i class="fa fa-envelope"></i> Enviar
                    </button>
                </div>
            </template>
        </modal>

        <modal id="janelaEnviarAprovacao" :fechar="!formEnviarAprovacao.preload" titulo="Enviar para Aprovação">
            <template slot="conteudo">
            <span v-show="formEnviarAprovacao.preload">
                <preload label="Enviando ..."></preload>
            </span>
                <div class="alert alert-success alert-dismissible" v-show="formEnviarAprovacao.enviado">
                    <h5>
                        <i class="icon fa fa-check"></i>
                        Item enviado com sucesso!
                    </h5>
                </div>
                <fieldset v-show="!formEnviarAprovacao.enviado && !formEnviarAprovacao.preload">
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12">
                            Arquivo: <p>{{ formEnviarAprovacao.item }}</p>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" onblur="valida_campo_vazio(this,1)" class="form-control"
                                       v-model="formEnviarAprovacao.nome">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="text" onblur="validaEmailVazio(this)" class="form-control"
                                       v-model="formEnviarAprovacao.email">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Mensagem</label>
                                <textarea class="form-control" v-model="formEnviarAprovacao.texto_livre" rows="3"
                                          cols="3"></textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </template>
            <template slot="rodape">
                <div v-show="!formEnviarAprovacao.preload">
                    <button type="button" class="btn btn-sm btn-primary"
                            @click="enviarAprovacao"
                            v-show="!formEnviarAprovacao.enviado">
                        <i class="fa fa-envelope"></i> Enviar
                    </button>
                </div>
            </template>
        </modal>

        <!-- Modal confirmar -->
        <modal id="janelaConfirmarApagar" :titulo="tituloApagar">
            <template slot="conteudo">
            <span v-show="preloadDel">
                <preload></preload>
            </span>
                <div class="alert alert-success alert-dismissible" v-show="apagado">
                    <h5>
                        <i class="icon fa fa-check"></i>
                        Registro apagado com sucesso!
                    </h5>
                </div>
                <div style="font-size: 17.6px" v-show="!apagado && !preloadDel" class="text-center">
                    Tem certeza que deseja apagar {{ form.tipo === 'pasta' ? 'esta pasta' : 'este arquivo' }} - <strong>
                    {{ form.label }}</strong>?
                    <br>
                    <p class="text-danger" v-show="form.tipo === 'pasta'">
                        ATENÇÃO: Todas os arquivos e pastas internas serão removidos
                    </p>
                </div>
            </template>
            <template slot="rodape">
                <div v-show="!preloadDel">
                    <button type="button" class="btn btn-sm btn-danger" @click="apagar()" v-show="!apagado">Apagar</button>
                </div>
            </template>
        </modal>
        <modal id="janelaConfirmarAprovar" :titulo="tituloAprovar">
            <template slot="conteudo">
            <span v-show="preloadAprovado">
                <preload></preload>
            </span>
                <div class="alert alert-success alert-dismissible" v-show="aprovado">
                    <h5>
                        <i class="icon fa fa-check"></i>
                        Item aprovado com sucesso!
                    </h5>
                </div>
                <div style="font-size: 17.6px" v-show="!aprovado && !preloadAprovado" class="text-center">
                    Tem certeza que deseja aprovar
                    <strong>{{ form.label }}</strong>?
                </div>
            </template>
            <template slot="rodape">
                <div v-show="!preloadAprovado">
                    <button type="button" class="btn btn-sm btn-danger" @click="aprovar()" v-show="!aprovado">Sim</button>
                </div>
            </template>
        </modal>
        <modal id="janelaConfirmarRevisar" :titulo="tituloRevisar">
            <template slot="conteudo">
            <span v-show="preloadRevisado">
                <preload></preload>
            </span>
                <div class="alert alert-success alert-dismissible" v-show="revisado">
                    <h5>
                        <i class="icon fa fa-check"></i>
                        Item revisado com sucesso!
                    </h5>
                </div>
                <div style="font-size: 17.6px" v-show="!revisado && !preloadRevisado" class="text-center">
                    Tem certeza que deseja marcar como revisado o arquivo
                    <strong>{{ form.label }}</strong>?
                </div>
            </template>
            <template slot="rodape">
                <div v-show="!preloadRevisado">
                    <button type="button" class="btn btn-sm btn-danger" @click="revisar" v-show="!revisado">SIM</button>
                </div>
            </template>
        </modal>
        <modal id="janelaMover" titulo="Mover Arquivo" @fechou="janelaMover = false" :fechar="!preloadMover">
            <template slot="conteudo">
                <pasta :model="mover" ref="pasta" v-if="janelaMover"
                       @carregando="preloadMover = true"
                       @carregou="preloadMover = false"
                       @pastaAtual="pastaAtual"
                       @moveu="movidoItem"
                >
                </pasta>
            </template>
            <template slot="rodape">
                <div v-if="!movido">
                    <button type="button"
                            class="btn btn-sm btn-default"
                            v-show="removeMover"
                            :disabled="preloadMover || !removeMover"
                            @click="moverArquivo">
                        Mover pra cá
                    </button>
                </div>
            </template>
        </modal>
        <!-- Modal atualizar arquivo-->
        <modal id="janelaAtualizar" titulo="Atualizando arquivo" :fechar="!emprogressoAtualizar">
            <template slot="conteudo">
                <div v-show="atualizadoSucesso" class="col-12  alert alert-success">
                    <i class="fa fa-check"></i> Arquivo atualizado com sucesso!
                </div>
                <div v-show="!atualizadoSucesso">
                    <div class="col-12 text-center alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> ATENÇÃO! <br>
                        Ao atualizar o arquivo a versão anterior será substituida.
                    </div>
                    <div class="col-12 py-3">
                        <upload :class-block="true" v-show="itemBusca !== '' && !preload"
                                label="Atualizar arquivo"
                                :model="arquivosAtualizarUpload"
                                :url="urlArquivoAtualizarUpload"
                                @onprogresso="(arquivo) => {arquivoAtualizarUploadAtual = arquivo; emprogressoAtualizar = true; }"
                                @onprogressogeral="(info)=>{pctGeralAtualizar=info.pct}"
                                @onfinalizado="uploadAtualizarFinalizado" :simples="true"
                                :quantidade="1"
                                :multi="false"
                                :dados-ajax="{cloud_id: cloud, pertence_id: itemBusca, anterior_id: id_anterior_atualizar}"/>
                    </div>
                    <div class="progress my-3" style="height: 15px;" v-if="arquivosAtualizarUpload.length > 0">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                             :style="{'width': pctGeralAtualizar + '%'}" :aria-valuenow="pctGeralAtualizar"
                             aria-valuemin="0" aria-valuemax="100">
                            {{ pctGeralAtualizar }}%
                        </div>
                    </div>
                    <div class="mb-3" v-if="arquivosAtualizarUpload.length > 0 && arquivoAtualizarUploadAtual!=null">
                        <span>{{ arquivoAtualizarUploadAtual.nome }}</span>
                        <br>
                        <div class="progress" style="height: 1px;">
                            <div class="progress-bar" role="progressbar"
                                 :style="{'width': arquivoAtualizarUploadAtual.pctProgresso + '%'}"
                                 :aria-valuenow="arquivoAtualizarUploadAtual.pctProgresso" aria-valuemin="0"
                                 aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

            </template>
        </modal>

        <button class="btn btn-sm btn-outline-primary" v-if="!forbidden"
                :disabled="preload"
                @click.prevent="formNovaPasta"
                data-toggle="modal"
                data-target="#janelaCadastrarPasta">
            <i class="fas fa-folder-plus"></i> Nova Pasta
        </button>

        <button class="btn btn-sm btn-outline-primary" :disabled="preload" @click="atualizar">
            <i class="fas fa-sync"></i> Atualizar
        </button>

        <upload v-show="itemBusca !== '' && !preload" label="Upload" :model="arquivosUpload" :url="urlArquivoUpload"
                @onprogresso="(arquivo) => {arquivoUploadAtual = arquivo}"
                @onprogressogeral="(info)=>{pctGeral=info.pct}"
                @onfinalizado="uploadFinalizado" :simples="true"
                :dados-ajax="{cloud_id: cloud, pertence_id: itemBusca}"/>
        <div class="progress my-3" style="height: 15px;" v-if="arquivosUpload.length > 0">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                 :style="{'width': pctGeral + '%'}" :aria-valuenow="pctGeral" aria-valuemin="0" aria-valuemax="100">
                {{ pctGeral }}%
            </div>
        </div>
        <div class="mb-3" v-if="arquivosUpload.length > 0 && arquivoUploadAtual!=null">
            <span>{{ arquivoUploadAtual.nome }}</span>
            <br>
            <div class="progress" style="height: 1px;">
                <div class="progress-bar" role="progressbar" :style="{'width': arquivoUploadAtual.pctProgresso + '%'}"
                     :aria-valuenow="arquivoUploadAtual.pctProgresso" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <!--Caminho de Rato-->
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb"
                        style="margin-top: 10px; margin-bottom: 0px; padding: 0.2rem 1rem; font-size: 13.5px;">
                        <li class="breadcrumb-item" :class="index === Number.MAX_VALUE ? 'active' : ''"
                            v-for="(folder,index) in caminho">
                            <button class="btn btn-sm btn-outline-default border-0" :disabled="preload"
                                    @click.prevent="abriPasta(folder.id); caminho.splice(index+1)">{{ folder.label }}
                            </button>

                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mt-3" id="corpo_cloud">
            <div class="col-12">
                <div class="table-responsive" id="table-responsive">
                    <table class="table table-hover bg-white">
                        <thead>
                        <tr class="bg-default">
                            <th>Nome</th>
                            <!--                            <th class="text-center">Tamanho</th>-->
                            <th class="text-center">Data de Criação</th>
                            <th class="text-center">Criado por</th>
                            <th class="text-center">Última Atualização</th>
                            <th class="text-center">Atualizado por</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="preload">
                            <td colspan="6">
                                <preload></preload>
                            </td>
                        </tr>
                        <tr v-if="!preload && itemBusca ">
                            <td colspan="6">
                                <button class="btn btn-sm btn-outline-dark border-0" :disabled="preload"
                                        @click="abriPasta(anterior); caminho.pop()">
                                    <i class="fa fa-long-arrow-alt-left"></i> Voltar
                                </button>
                            </td>
                        </tr>
                        <tr v-for="(item, index) in lista" v-if="!preload && lista.length > 0 && item.TemPermissao">
                            <td>
                                <div v-if="item.tipo === 'pasta'">
                                    <button class="btn btn-outline-default text-left border-0"
                                            @click="abriPasta(item.id); adicionaCaminho(item)">
                                        <i class="fas fa-folder mr-1" style="color: #EECD6D"></i> {{ item.label }}
                                    </button>
                                    <br/>

                                </div>
                                <div v-if="item.tipo === 'arquivo'">

                                    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                                         v-if="!item.arquivo.imagem" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
                                         y="0px" viewBox="0 0 512 512"
                                         style="enable-background:new 0 0 512 512; height: 45px" xml:space="preserve"><path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
                                        <path style="fill:#B0B7BD;"
                                              d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
                                        <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
                                        <path style="fill:#184056;"
                                              d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"/>
                                        <g><text x="45" y="380" style="font-size:130px; fill:#FFFFFF;">
                                            {{ item.arquivo.extensao }}
                                        </text>
                                        </g>
                                        <path style="fill:#CAD1D8;"
                                              d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                        <g></g>
                                    </svg>

                                    <img :src="item.arquivo.urlThumb" alt="" style="width: 45px;"
                                         v-if="item.arquivo.imagem">
                                    <div class="btn-group dropright" v-if="item.TemPermissao">
                                        <button type="button"
                                                :class="{
                                                'marcador dropdown-toggle': !item.revisado && !item.aprovado,
                                                'marcadorRevisado dropdown-toggle': item.revisado && !item.aprovado,
                                                'normal dropdown-toggle': item.revisado && item.aprovado || !item.revisado && item.aprovado ,
                                                }"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            {{ item.label }}{{ item.arquivo.extensao }}
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" role="menu">
                                            <div class="col-12 py-1"
                                                 v-if="habilidades.find( habilidade => habilidade.nome === 'Download')">
                                                <a :href="`${url_publico}/anexoDownload/${item.arquivo.file}`"
                                                   class="btn btn-sm btn-block btn-outline-primary"
                                                   target="_blank"
                                                   download>
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                            <div class="col-12 py-1"
                                                 v-if="habilidades.find( habilidade => habilidade.nome === 'Visualizar')">
                                                <a href="#"
                                                   class="btn btn-sm btn-block btn-outline-primary"
                                                   data-toggle="modal"
                                                   @click.prevent="visualizar(item.arquivo)"
                                                   data-target="#janelaVisualizadora">
                                                    <i class="fas fa-search"></i> Visualizar
                                                </a>
                                            </div>
                                            <div class="col-12 py-1"
                                                 v-if="habilidades.find( habilidade => habilidade.nome === 'Detalhes')">
                                                <a class="btn btn-sm btn-block btn-outline-primary"
                                                   href="#"
                                                   data-toggle="modal"
                                                   @click.prevent="exibirDetalhes(item)"
                                                   data-target="#janelaDetalhes">
                                                    <i class="fas fa-list"></i> Detalhes
                                                </a>
                                            </div>
                                            <div class="col-12 py-1"
                                                 v-if="habilidades.find( habilidade => habilidade.nome === 'Editar')">
                                                <a class="btn btn-sm btn-block btn-outline-primary"
                                                   href="#"
                                                   @click.prevent="formAlterar(item.id)"
                                                   data-toggle="modal"
                                                   data-target="#janelaCadastrarPasta"
                                                >
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                            </div>
                                            <div class="col-12 py-1"
                                                 v-if="habilidades.find( habilidade => habilidade.nome === 'Mover')">
                                                <a class="btn btn-sm btn-block btn-outline-primary"
                                                   href="#"
                                                   data-toggle="modal"
                                                   data-target="#janelaMover"
                                                   @click="pastaMover(item.id)"
                                                >
                                                    <i class="fas fa-arrows-alt-v"></i> Mover
                                                </a>
                                            </div>
                                            <div class="col-12 py-1"
                                                 v-if="habilidades.find( habilidade => habilidade.nome === 'Deletar')">
                                                <a class="btn btn-sm btn-block btn-outline-primary"
                                                   href="#"
                                                   @click.prevent="janelaConfirmar(item)"
                                                   data-toggle="modal"
                                                   data-target="#janelaConfirmarApagar"
                                                >
                                                    <i class="far fa-trash-alt"></i> Deletar
                                                </a>
                                            </div>
                                            <div class="col-12 py-1"
                                                 v-if="habilidades.find( habilidade => habilidade.nome === 'Atualizar')">
                                                <a class="btn btn-sm btn-block btn-outline-primary"
                                                   href="#"
                                                   @click.prevent="janelaAtualizar(item)"
                                                   data-toggle="modal"
                                                   data-target="#janelaAtualizar"
                                                >
                                                    <i class="fas fa-sync"></i> Atualizar
                                                </a>
                                            </div>
                                            <div class="col-12 py-1"
                                                 v-if="habilidades.find( habilidade => habilidade.nome === 'Revisar')"
                                                 v-show="!item.revisado && !item.aprovado || !item.revisado">
                                                <a class="btn btn-sm btn-block btn-outline-primary"
                                                   href="#"
                                                   @click.prevent="janelaConfirmarRevisar(item)"
                                                   data-toggle="modal"
                                                   data-target="#janelaConfirmarRevisar"
                                                >
                                                    <i class="fas fa-recycle"></i> Revisar
                                                </a>
                                            </div>
                                            <div class="col-12 py-1"
                                                 v-show="!item.revisado && !item.aprovado || !item.revisado">
                                                <a class="btn btn-sm btn-block btn-outline-primary"
                                                   href="#"
                                                   @click.prevent="janelaEnviarRevisao(item)"
                                                   data-toggle="modal"
                                                   data-target="#janelaEnviarRevisao"
                                                >
                                                    <i class="fas fa-share-square"></i> Enviar para Revisão
                                                </a>
                                            </div>
                                            <div class="col-12 py-1"
                                                 v-if="habilidades.find( habilidade => habilidade.nome === 'Aprovar')"
                                                 v-show="!item.aprovado || item.revisado && !item.aprovado">
                                                <a class="btn btn-sm btn-block btn-outline-primary"
                                                   href="#"
                                                   @click.prevent="janelaConfirmarAprovar(item)"
                                                   data-toggle="modal"
                                                   data-target="#janelaConfirmarAprovar"
                                                >
                                                    <i class="fas fa-tasks"></i> Aprovar
                                                </a>
                                            </div>

                                            <div class="col-12 py-1"
                                                 v-show="!item.aprovado || item.revisado && !item.aprovado">
                                                <a class="btn btn-sm btn-block btn-outline-primary"
                                                   href="#"
                                                   @click.prevent="janelaEnviarAprovacao(item)"
                                                   data-toggle="modal"
                                                   data-target="#janelaEnviarAprovacao"
                                                >
                                                    <i class="fas fa-share-square"></i> Enviar para Aprovação
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center" v-if="item.tipo === 'pasta'">
                                {{ item.created_at }}
                            </td>
                            <td class="text-center" v-if="item.tipo === 'pasta'">
                                {{ item.criou ? item.criou.nome : "" }}
                            </td>
                            <td class="text-center" v-if="item.tipo === 'pasta'">
                                {{ item.updated_at }}
                            </td>
                            <td class="text-center" v-if="item.tipo === 'pasta'">
                                {{ item.editou ? item.editou.nome : "" }}
                            </td>
                            <td class="text-right" :colspan="item.tipo === 'arquivo' ? 5 : 0">
                                <div class="btn-group dropright" v-if="item.tipo === 'pasta'">
                                    <button type="button" class="btn btn-sm btn-default dropdown-toggle"
                                            v-if="habilidades.find( habilidade => habilidade.nome === 'Mover') || habilidades.find( habilidade => habilidade.nome === 'Editar') || habilidades.find( habilidade => habilidade.nome === 'Deletar')"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <div class="col-12 py-1"
                                             v-show="item.pertence && habilidades.find( habilidade => habilidade.nome === 'Mover')">
                                            <a class="btn btn-sm btn-block btn-outline-primary"
                                               href="#"
                                               data-toggle="modal"
                                               data-target="#janelaMover"
                                               @click="pastaMover(item.id)"
                                            >
                                                <i class="fas fa-arrows-alt-v"></i> Mover
                                            </a>
                                        </div>
                                        <div class="col-12 py-1"
                                             v-if="habilidades.find( habilidade => habilidade.nome === 'Editar')">
                                            <a class="btn btn-sm btn-block btn-outline-primary" href="javascript://"
                                               v-if="item.tipo === 'pasta'"
                                               @click.prevent="formAlterar(item.id)"
                                               data-toggle="modal"
                                               data-target="#janelaCadastrarPasta">
                                                <i class="fa fa-edit" aria-hidden="true"></i> Editar
                                            </a>
                                        </div>
                                        <div class="col-12 py-1"
                                             v-if="habilidades.find( habilidade => habilidade.nome === 'Deletar')">
                                            <a class="btn btn-sm btn-block btn-outline-primary"
                                               href="javascript://"
                                               v-if="item.tipo === 'pasta'"
                                               @click.prevent="janelaConfirmar(item)"
                                               data-toggle="modal"
                                               data-target="#janelaConfirmarApagar">
                                                <i class="fa fa-trash" aria-hidden="true"></i> Deletar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import upload from '../components/Upload';
import modal from '../components/Modal';
import pasta from '../components/PastaCloud';

export default {
    components: {
        upload,
        modal,
        pasta,
    },
    props: {
        cloud: {
            type: Number,
            required: true,
            default: () => '',
        },
        itemBusca: {
            type: Number | String,
            required: false,
            default: () => '',
        },
    },
    data() {
        return {
            url_site: '',
            crsf: '',
            tituloNovaPasta: '',
            tituloApagar: '',
            tituloAprovar: '',
            tituloRevisar: '',
            titulojanelavisualizar: '',
            cadastrado: false,
            editando: false,
            atualizado: false,
            apagado: false,
            aprovado: false,
            revisado: false,

            preload: false,
            preload_pasta: false,
            preloadDel: false,
            preloadAprovado: false,
            preloadRevisado: false,
            forbidden: true,

            //folder
            removeMover: false,

            caminho: [],

            src: '',
            srcDownload: '',
            imagem: true,

            exibindo: null,
            detalhes: null,

            formEnviarRevisao: {
                enviado: false,
                preload: false,
                caminho: '',
                item: '',
                nome: '',
                email: '',
                texto_livre: '',
            },
            formEnviarRevisaoDefault: null,

            formEnviarAprovacao: {
                enviado: false,
                preload: false,
                caminho: '',
                item: '',
                nome: '',
                email: '',
                texto_livre: '',
            },
            formEnviarAprovacaoDefault: null,

            form: {
                id: 0,
                cloud_id: 0,
                label: '',
                tipo: 'pasta',
                pertence: null,
                todosGrupos: false,
                permissoes: [],
            },
            formDefault: null,

            //Upload
            arquivosUpload: [],
            urlArquivoUpload: `${URL_ADMIN}/itenscloud/uploadAnexos`,
            pctGeral: 0,
            arquivoUploadAtual: null,

            //AtualizarArquivo
            arquivosAtualizarUpload: [],
            urlArquivoAtualizarUpload: `${URL_ADMIN}/itenscloud/uploadAtualizarAnexos`,
            pctGeralAtualizar: 0,
            arquivoAtualizarUploadAtual: null,
            id_anterior_atualizar: 0,
            emprogressoAtualizar: false,
            atualizadoSucesso: false,

            anterior: '',
            anteriorListaVazia: '',

            lista: [],
            grupos: [],
            meu_grupo: null,

            habilidades: [],

            //Janela Cloud
            preloadMover: false,
            janelaMover: false,
            movido: false,
            mover: {}
        }
    },
    computed: {
        urlBase() {
            return `${URL_ADMIN}/cloud/anexoDownload/`;
        }
    },
    mounted() {
        /*Retira Clique do botão direito*/
        document.oncontextmenu = document.body.oncontextmenu = function () {
            return false;
        }
        let raiz = {
            label: "Inicio",
            id: ""
        };
        this.form.cloud_id = this.cloud; // incluindo o id do Cloud
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formEnviarRevisaoDefault = _.cloneDeep(this.formEnviarRevisao) //copia
        this.formEnviarAprovacaoDefault = _.cloneDeep(this.formEnviarAprovacao) //copia
        this.csrf = CSRF_token;
        this.url_publico = `${URL_PUBLICO}/cloud`;
        this.caminho.push(raiz);
        this.atualizar();

        // $('.table-responsive').on('show.bs.dropdown', function () {
        //     $('.table-responsive').css("overflow", "inherit");
        // });

    },
    filters: {
        formatBytes(bytes, decimals, kib) {
            kib = kib || false;
            if (bytes === 0) return '0 Bytes';
            if (isNaN(parseFloat(bytes)) && !isFinite(bytes)) return 'Not an number';
            const k = kib ? 1024 : 1000;
            const dm = decimals || 2;
            const sizes = kib ? ['Bytes', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB', 'BiB'] : ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'BB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    },
    methods: {
        /*--------MOVER----------*/
        pastaAtual(dados) {
            this.removeMover = !(dados.atual === dados.inicial || dados.atual === dados.arquivo || !dados.atual);
        },
        pastaMover(arquivo) {
            setTimeout(() => {
                this.movido = false;
                this.mover.cloud = this.cloud;
                this.mover.item = this.itemBusca;
                this.mover.arquivo = arquivo;
                this.janelaMover = true;
            }, 200);
        },
        moverArquivo() {
            setTimeout(() => {
                this.movido = this.janelaMover;
                this.$refs.pasta.moverArquivo();
            }, 10)
        },
        movidoItem() {
            this.atualizar();
            this.movido = true
        },

        /*--------UPLOADS--------*/
        uploadFinalizado() {
            this.arquivosUpload = [];
            setTimeout(() => {
                this.atualizar();
            }, 100)
        },

        /*--------UPLOADS ATUALIZAR--------*/
        janelaAtualizar(obj) {
            this.atualizadoSucesso = false;
            this.id_anterior_atualizar = obj.arquivo_id;
        },
        uploadAtualizarFinalizado() {
            this.arquivosAtualizarUpload = [];
            this.emprogressoAtualizar = false;
            this.atualizadoSucesso = true;
            setTimeout(() => {
                this.atualizar();
            }, 100)
        },

        /*--------PERMISSÕES EM PASTA E ITENS--------*/
        adicionaPermissao(id) {
            this.form.permissoes.push(id);
        },
        removePermissao(id) {
            let index = _.indexOf(this.form.permissoes, id);
            this.form.permissoes.splice(index, 1);
        },
        selecionarTodos() {
            this.form.todosGrupos = !this.form.todosGrupos;
            _.forEach(this.grupos, (grupo) => {
                grupo.permitido = this.form.todosGrupos;
                if (this.form.todosGrupos) {
                    this.adicionaPermissao(grupo);
                } else {
                    this.form.permissoes = []
                }
            });
        },

        /*--------FORM PASTA E ITENS--------*/
        formNovaPasta() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloNovaPasta = "NOVA PASTA";

            formReset();
            setupCampo();
            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.pertence = this.itemBusca;

            // desmarco todos e deixo somente o meu ativo
            _.forEach(this.grupos, (grupo) => {
                grupo.permitido = false;
                if (grupo.id === this.meu_grupo) {
                    grupo.permitido = true;
                }
            });
        },
        criaPasta() {
            $('#janelaCadastrarPasta :input:visible').trigger('blur');
            if ($('#janelaCadastrarPasta :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            this.preload_pasta = true;
            axios.post(`${URL_ADMIN}/itenscloud/`, this.form)
                .then(response => {
                    let data = response.data;
                    this.preload_pasta = false;
                    this.cadastrado = true;
                    this.atualizar();
                }).catch(error => (this.preload_pasta = false));
        },

        /*--------ALTERAR PASTA E ITENS--------*/
        formAlterar(id) {
            this.form.id = id;

            this.cadastrado = false;
            this.atualizado = false;
            this.editando = true;
            this.tituloNovaPasta = "Alterando";

            this.preload_pasta = true;

            // desmarco todos e deixo somente o meu ativo
            _.forEach(this.grupos, (grupo) => {
                grupo.permitido = false;
                if (grupo.id === this.meu_grupo) {
                    grupo.permitido = true;
                }
            });

            Object.assign(this.form, this.formDefault);
            formReset();
            axios.get(`${URL_ADMIN}/itenscloud/${id}/editar`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);
                    this.tituloNovaPasta = `Alterando - ${data.label}`;
                    setupCampo();

                    // muda para todos se for igual a qnt de grupo
                    this.form.todosGrupos = data.permissoes.length - 2 === this.grupos.length;

                    //ligando os botoes
                    _.forEach(this.grupos, function (grupo) {
                        let achou = _.find(data.permissoes, {'id': grupo.id});
                        if (achou) {
                            grupo.permitido = true;
                        }
                    });

                    this.preload_pasta = false;
                }).catch(error => (this.preload_pasta = false));
        },
        alterarPasta() {
            $('#janelaCadastrarPasta :input:visible').trigger('blur');
            if ($('#janelaCadastrarPasta :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }
            this.form._method = 'PUT';
            this.preload_pasta = true;
            axios.put(`${URL_ADMIN}/itenscloud/${this.form.id}`, this.form)
                .then(response => {
                    let data = response.data;
                    this.preload_pasta = false;
                    this.atualizado = true;
                    this.atualizar();
                }).catch(error => (this.preload_pasta = false));
        },

        /*--------VISUALIZAR ITEM--------*/
        visualizar(obj) {
            this.exibindo = null;
            this.titulojanelavisualizar = `Visualizando`;
            this.exibindo = _.cloneDeep(obj);
        },

        /*--------DETALHE ITEM--------*/
        exibirDetalhes(obj) {
            this.detalhes = null;
            this.detalhes = _.cloneDeep(obj);
        },

        /*--------CAMINHO DE RATO--------*/
        adicionaCaminho(item) {
            this.caminho.push(item);
        },

        /*--------ABRIR PASTA--------*/
        abriPasta(id) {
            this.preload = true;
            let itemBusca = this.itemBusca;
            this.anteriorListaVazia = itemBusca;
            itemBusca = id;
            this.$emit("abri-pasta", itemBusca);
            setTimeout(() => {
                this.atualizar();
            }, 50);
        },

        /*--------JANELA APAGAR--------*/
        janelaConfirmar(obj) {
            this.form = obj;
            this.tituloApagar = `Apagar ${this.form.tipo}`;
            this.apagado = false;
            this.preloadDel = false;
        },
        apagar() {
            this.erros = [];
            this.preloadDel = true;
            axios.delete(`${URL_ADMIN}/itenscloud/${this.form.id}`, this.form)
                .then(response => {
                    let data = response.data;
                    this.preloadDel = false;
                    this.apagado = true;
                    this.atualizar();
                });
        },

        /*--------JANELA ENVIAR PARA APROVACAO--------*/
        janelaEnviarAprovacao(obj) {
            this.formEnviarAprovacao = _.cloneDeep(this.formEnviarAprovacaoDefault) //copia
            formReset();

            _.forEach(this.caminho, (item) => {
                this.formEnviarAprovacao.caminho += item.label + '/';
            });

            this.formEnviarAprovacao.caminho += obj.label + obj.arquivo.extensao;

            this.formEnviarAprovacao.item = obj.label + obj.arquivo.extensao;

        },
        enviarAprovacao() {
            $('#janelaEnviarRevisao :input:visible').trigger('blur');
            if ($('#janelaEnviarRevisao :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            this.formEnviarAprovacao.preload = true;
            axios.post(`${URL_ADMIN}/itenscloud/enviar-para-aprovacao`, this.formEnviarAprovacao)
                .then(response => {
                    let data = response.data;
                    this.formEnviarAprovacao.preload = false;
                    this.formEnviarAprovacao.enviado = data.enviado;
                })
                .catch(error => {
                    this.formEnviarAprovacao.preload = false;
                    this.formEnviarAprovacao.enviado = false;
                })
        },

        /*--------JANELA APROVAR--------*/
        janelaConfirmarAprovar(obj) {
            this.form = obj;
            this.tituloAprovar = `Aprovar`;
            this.aprovado = false;
            this.preloadAprovado = false;
        },
        aprovar() {
            this.erros = [];
            this.preloadAprovado = true;
            axios.put(`${URL_ADMIN}/itenscloud/${this.form.id}/aprovar`, this.form)
                .then(response => {
                    let data = response.data;
                    this.aprovado = true;
                    this.preloadAprovado = false;
                    this.atualizar();
                });
        },

        /*--------JANELA ENVIAR PARA REVISAO--------*/
        janelaEnviarRevisao(obj) {
            this.formEnviarRevisao = _.cloneDeep(this.formEnviarRevisaoDefault) //copia
            formReset();

            _.forEach(this.caminho, (item) => {
                this.formEnviarRevisao.caminho += item.label + '/';
            });

            this.formEnviarRevisao.caminho += obj.label + obj.arquivo.extensao;

            this.formEnviarRevisao.item = obj.label + obj.arquivo.extensao;

        },

        enviarRevisao() {
            $('#janelaEnviarRevisao :input:visible').trigger('blur');
            if ($('#janelaEnviarRevisao :input:visible.is-invalid').length) {
                alert('Verificar os erros');
                return false;
            }

            this.formEnviarRevisao.preload = true;
            axios.post(`${URL_ADMIN}/itenscloud/enviar-para-revisao`, this.formEnviarRevisao)
                .then(response => {
                    let data = response.data;
                    this.formEnviarRevisao.preload = false;
                    this.formEnviarRevisao.enviado = data.enviado;
                })
                .catch(error => {
                    this.formEnviarRevisao.preload = false;
                    this.formEnviarRevisao.enviado = false;
                })
        },


        /*--------JANELA REVISAR--------*/
        janelaConfirmarRevisar(obj) {
            this.form = obj;
            this.tituloRevisar = `Revisar`;
            this.revisado = false;
            this.preloadRevisado = false;
        },
        revisar() {
            this.erros = [];
            this.preloadRevisado = true;
            axios.put(`${URL_ADMIN}/itenscloud/${this.form.id}/revisar`, this.form)
                .then(response => {
                    let data = response.data;
                    this.revisado = true;
                    this.preloadRevisado = false;
                    this.atualizar();
                }).catch(error => (this.preloadRevisado = false));
        },

        /*--------ATUALIZAR LISTA DE ITENS--------*/
        atualizar() {
            this.preload = true;
            this.forbidden = true;
            this.form.pertence = this.itemBusca; // incluindo a pasta
            axios.get(`${URL_ADMIN}/cloud/atualizar/${this.cloud}/${this.itemBusca}`)
                .then(response => {
                    this.forbidden = false;
                    let data = response.data;
                    this.lista = data.lista;
                    //Nivel de Pastas
                    if (this.lista.length >= 1) {
                        if (!this.lista[0].pertence) {
                            this.anteriorListaVazia = this.anterior;
                        } else {
                            this.anterior = this.anterior != null ? this.lista[0].pertence.pertence : "";
                            if (this.anterior == null) {
                                this.anterior = "";
                            }
                        }
                    } else {
                        this.anterior = this.anteriorListaVazia;
                    }
                    this.grupos = data.grupos;
                    this.habilidades = data.habilidades;
                    this.meu_grupo = this.habilidades[0].pivot.grupo_cloud_id;
                    this.preload = false;
                    let tableResponsive = document.getElementById("table-responsive");
                    setTimeout(() => {
                        if (tableResponsive.offsetHeight <= 550) {
                            tableResponsive.style.minHeight = "550px";
                        }
                    }, 100)
                })
                .catch(error => {
                    if (error.response.status === 403) {
                        this.forbidden = true;
                    }
                    this.preload = false;
                });
        },
    }
}
</script>

<style scoped>
.aprovado {
    padding-left: 5px;
    padding-right: 5px;
    transition: all 300ms;
}

.aprovado:hover, .aprovado:focus, .aprovado:active {
    color: #ffffff;
    background: #184056;
    transition: all 300ms;
}

.marcador {
    background: #f700f7;
    color: #ffffff;
    padding-left: 5px;
    padding-right: 5px;
    transition: all 300ms;
}

.marcadorRevisado {
    background: #41941c;
    color: #ffffff;
    padding-left: 5px;
    padding-right: 5px;
    transition: all 300ms;
}

.normal {
    color: #223f6d !important;
    background: transparent !important;
}

.normal:hover, .normal:focus, .normal:active {
    color: #1c274e !important;
    background: transparent !important;
}

.dropdown-toggle {
    border: none;
    color: #184056;
    transition: all 300ms;
}

.dropdown-toggle:hover, .dropdown-toggle:focus, .dropdown-toggle:active {
    border: 1px;
    color: #223f6d;
}

.dropdown-toggle::after {
    content: none;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.25rem 1.5rem;
    clear: both;
    font-weight: 400;
    color: #184056;
    text-align: inherit;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
    transition: all 300ms;
}

.dropdown-item:hover {
    color: #ffffff;
    background-color: #184056;
    transition: all 300ms;
}

.preview-eyer {
    color: rgb(255, 255, 255);
    visibility: visible;
    position: absolute;
    z-index: 8;
    top: 28px;
    left: auto;
    padding: 9px;
    text-align: center;
    width: 41px;
    right: 13px;
    background: rgb(0, 0, 0);
    opacity: 1;
}

.list-group .popover-content .list-group-item {
    padding: 0.25rem 0.75rem !important;
    border: none !important;
}

.popover-header {
    display: none !important;
}

.dropdown-menu-arrow {
    top: -17px;
    left: -3%;
    width: 0;
    height: 0;
    position: relative;
}

.dropdown-menu-arrow:after {
    bottom: -28px;
    right: -6px;
    transform: rotate(-90deg);
    border-bottom-color: #fff;
}

.dropdown-menu-arrow:before {
    bottom: -28px;
    right: -6px;
    transform: rotate(-90deg);
}

.table-responsive {
    /*min-height: 350px;*/
    /*overflow: inherit;*/
}

.chip {
    display: inline-flex;
    flex-direction: row;
    background-color: #e5e5e5;
    border: none;
    cursor: default;
    height: 36px;
    outline: none;
    padding: 0;
    font-size: 12px;
    font-color: #333333;
    font-family: "Open Sans", sans-serif;
    white-space: nowrap;
    align-items: center;
    border-radius: 16px;
    vertical-align: middle;
    text-decoration: none;
    justify-content: center;
}

.chip-head {
    display: flex;
    position: relative;
    overflow: hidden;
    background-color: #32C5D2;
    font-size: 1.25rem;
    flex-shrink: 0;
    align-items: center;
    user-select: none;
    border-radius: 50%;
    justify-content: center;
    width: 36px;
    color: #fff;
    height: 36px;
    margin-right: -4px;
}

.chip-content {
    cursor: inherit;
    display: flex;
    align-items: center;
    user-select: none;
    white-space: nowrap;
    padding-left: 12px;
    padding-right: 12px;
}

.chip-svg {
    color: #999999;
    cursor: pointer;
    height: auto;
    margin: 4px 4px 0 -8px;
    fill: currentColor;
    width: 1em;
    height: 1em;
    display: inline-block;
    font-size: 24px;
    transition: fill 200ms cubic-bezier(0.4, 0, 0.2, 1) 0ms;
    user-select: none;
    flex-shrink: 0;
}

.chip-svg:hover {
    color: #666666;
}


</style>
