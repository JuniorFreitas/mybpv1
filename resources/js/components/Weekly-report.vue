<template>
    <div class="mt-4">

        <!--Janela de Apagar quadro-->
        <modal id="janelaApagarQuadro" :fechar="!formApagarQuadro.preload" :modal-pai="modalPai"
               titulo="Excluir quadro">
            <template slot="conteudo">
                <span v-show="formApagarQuadro.preload">
                            <i class="fa fa-spinner fa-pulse"></i> Apagando quadro...
                        </span>

                <div v-show="!formApagarQuadro.preload && !formApagarQuadro.delete && !formApagarQuadro.erro"
                     class="alert alert-warning"
                     role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-exclamation-triangle"></i><br>
                        Atenção! Deseja excluir o quadro?
                    </h4>
                </div>

                <div v-show="!formApagarQuadro.preload && formApagarQuadro.delete && !formApagarQuadro.erro"
                     class="alert alert-success"
                     role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-check"></i><br>
                        O quadro foi apagado
                    </h4>
                </div>

                <div v-show="!formApagarQuadro.preload && !formApagarQuadro.delete && formApagarQuadro.erro"
                     class="alert alert-danger"
                     role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-times-circle"></i><br>
                        {{ formApagarQuadro.msg }}
                    </h4>
                </div>


            </template>
            <template slot="rodape">
                <button v-show="!formApagarQuadro.preload && !formApagarQuadro.delete && !formApagarQuadro.erro"
                        class="btn btn-sm btn-danger"
                        type="button"
                        @click="deleteQuadro">
                    <i class="fas fa-trash-alt"></i> Apagar quadro
                </button>
            </template>
        </modal>

        <!--Janela de Apagar lista de tarefas-->
        <modal id="janelaApagarListaTarefa" :fechar="!formApagarLista.preload" :modal-pai="modalPai"
               titulo="Excluir lista de tarefas">
            <template slot="conteudo">
                <span v-show="formApagarLista.preload">
                            <i class="fa fa-spinner fa-pulse"></i> Apagando lista...
                        </span>

                <div v-show="!formApagarLista.preload && !formApagarLista.delete && !formApagarLista.erro"
                     class="alert alert-warning"
                     role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-exclamation-triangle"></i><br>
                        Atenção! A lista e todas as tarefas serão apagada. Deseja continuar?
                    </h4>
                </div>

                <div v-show="!formApagarLista.preload && formApagarLista.delete && !formApagarLista.erro"
                     class="alert alert-success"
                     role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-check"></i><br>
                        A lista foi apagada
                    </h4>
                </div>

                <div v-show="!formApagarLista.preload && !formApagarLista.delete && formApagarLista.erro"
                     class="alert alert-danger"
                     role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-times-circle"></i><br>
                        {{ formApagarLista.msg }}
                    </h4>
                </div>


            </template>
            <template slot="rodape">
                <button v-show="!formApagarLista.preload && !formApagarLista.delete && !formApagarLista.erro"
                        class="btn btn-sm btn-danger"
                        type="button"
                        @click="deleteLista">
                    <i class="fas fa-trash-alt"></i> Apagar lista
                </button>
            </template>
        </modal>

        <!--Janela de Apagar tarefa-->
        <modal id="janelaApagarTarefa" :fechar="!formApagarTarefa.preload" :modal-pai="modalPai"
               titulo="Excluir tarefa">
            <template slot="conteudo">
                <span v-show="formApagarTarefa.preload">
                            <i class="fa fa-spinner fa-pulse"></i> Apagando tarefa...
                        </span>

                <div v-show="!formApagarTarefa.preload && !formApagarTarefa.delete && !formApagarTarefa.erro"
                     class="alert alert-warning"
                     role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-exclamation-triangle"></i><br>
                        Atenção! A tarefa será apagada. Deseja continuar?
                    </h4>
                </div>

                <div v-show="!formApagarTarefa.preload && formApagarTarefa.delete && !formApagarTarefa.erro"
                     class="alert alert-success"
                     role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-check"></i><br>
                        A tarefa foi apagada
                    </h4>
                </div>

                <div v-show="!formApagarTarefa.preload && !formApagarTarefa.delete && formApagarTarefa.erro"
                     class="alert alert-danger"
                     role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-times-circle"></i><br>
                        {{ formApagarTarefa.msg }}
                    </h4>
                </div>


            </template>
            <template slot="rodape">
                <button v-show="!formApagarTarefa.preload && !formApagarTarefa.delete && !formApagarTarefa.erro"
                        class="btn btn-sm btn-danger"
                        type="button"
                        @click="deleteTarefa">
                    <i class="fas fa-trash-alt"></i> Apagar tarefa
                </button>
            </template>
        </modal>

        <!--Janela Tarefa-->
        <modal v-if="TAREFA" id="janelaTarefa" :fechar="!formTarefa.preload" :modal-pai="modalPai"
               :titulo="TAREFA.titulo" size="g" topo>
            <template slot="topo">

                <input v-if="tarefaEditandoTitulo" ref="campoTituloTarefa" v-model="TAREFA.titulo" class="form-control"
                       type="text" @blur="updateTarefa" @mouseover="$refs.campoTituloTarefa.focus()"
                       @keydown.enter="updateTarefa">
                <h3 v-else class="modal-title"
                    @click="tarefa_update ? tarefaEditandoTitulo = !tarefaEditandoTitulo:false">
                    {{ TAREFA.titulo }}</h3>
                <p>na lista <span class="text-primary">{{ LISTA.titulo }}</span></p>
            </template>
            <template slot="conteudo">
                <i v-show="formTarefa.preload" class="fa fa-spinner fa-pulse"></i>
                <div v-if="!formTarefa.preload" class="row">
                    <div class="col-12 col-sm-9">
                        <!--    MEMBROS  -->
                        <div v-if="TAREFA.membros && TAREFA.membros.length > 0" class="row mb-3">
                            <div class="col-1 text-right">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <div class="col-11">
                                <h4>Membros</h4>
                                <span v-for="(membro,index) in TAREFA.membros" class="dropdown mb-1">
                                    <a class="btn btn-secondary rounded-circle" data-toggle="dropdown" href="#"
                                       role="button" style="width: 30px; height: 30px">
                                        <span class="d-flex justify-content-center align-items-center"
                                              style="margin-top: -2px">{{
                                                membro.nome.toUpperCase() | formataNome
                                            }}</span>
                                    </a>
                                    <div aria-labelledby="dropdownMenuLink" class="dropdown-menu mb-1">
                                        <div class="card" style="width: 300px">
                                            <div class="card-header">
                                                Detalhes do membro
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-2">
                                                        <a class="btn btn-secondary rounded-circle"
                                                           data-toggle="dropdown" href="#" role="button"
                                                           style="width: 30px; height: 30px">
                                                            <span
                                                                class="d-flex justify-content-center align-items-center"
                                                                style="margin-top: -2px">{{
                                                                    membro.nome.toUpperCase() | formataNome
                                                                }}</span>
                                                        </a>
                                                    </div>
                                                    <div class="col-10">
                                                        {{ membro.nome }}
                                                    </div>
                                                </div>
                                                <button class="btn btn-danger btn-sm btn-block" type="button"
                                                        @click="removerMembro(membro)">Remover da tarefa</button>
                                            </div>
                                        </div>
                                    </div>
                                </span>

                                <!-- bt de adicionar -->
                                <span class="dropdown mb-1">
                                    <a class="btn btn-secondary rounded-circle" data-toggle="dropdown" href="#"
                                       role="button" style="width: 30px; height: 30px">
                                        <i class="fas fa-plus d-flex justify-content-center align-items-center"></i>
                                    </a>
                                    <div aria-labelledby="dropdownMenuLink" class="dropdown-menu mb-1">
                                        <div class="card" style="width: 300px">
                                            <div class="card-header">
                                                Adicionar membros
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <autocomplete v-model="autoCompleteNovoMembros"
                                                                  :caminho="`${rotaTarefa}/buscarMembros`"
                                                                  :formsm="false"
                                                                  placeholder="Pesquisar membros"
                                                                  @onblur="autoCompleteNovoMembros=''"
                                                                  @onselect="addMembro"></autocomplete>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </span>


                            </div>
                        </div>
                        <!--    DATAS DE INÍCIO E ENTREGA  -->
                        <div v-if="TAREFA.datahora_inicio!=null || TAREFA.datahora_entrega!=null" class="row mb-3">

                            <div class="col-12">
                                <h4>
                                    <i class="far fa-clock"></i> Cronograma
                                    <span v-if="!TAREFA.concluido && TAREFA.datahora_entrega!=null && TAREFA.emAtraso"
                                          class="badge badge-danger" style="font-size: 10px">EM ATRASO</span>
                                    <span v-if="TAREFA.concluido" class="badge badge-success" style="font-size: 10px">CONCLUÍDO</span>
                                </h4>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div v-if="TAREFA.datahora_inicio!=null" class="col-12 col-sm-4">
                                        <div class="card-title text-center">
                                            <label class="form-check-label font-weight-lighter">Data de início</label>
                                        </div>
                                        <datepicker v-model="TAREFA.datahora_inicio" :hora="true" label=""
                                                    @onselect="addDataHoraInicio"></datepicker>
                                        <button class="btn btn-danger btn-sm btn-block" type="button"
                                                @click="removerDataHoraInicio">Remover
                                        </button>
                                    </div>
                                    <div v-if="TAREFA.datahora_entrega!=null" class="col-12 col-sm-4">
                                        <div class="card-title text-center">
                                            <input id="checkboxDataEntrega" v-model="TAREFA.concluido"
                                                   class="form-check-input" type="checkbox" value=""
                                                   @change="updataConcluirTarefa">
                                            <label class="form-check-label font-weight-lighter"
                                                   for="checkboxDataEntrega">Data de entrega</label>
                                        </div>
                                        <datepicker v-model="TAREFA.datahora_entrega" :hora="true" label=""
                                                    @onselect="addDataHoraEntrega"></datepicker>
                                        <button class="btn btn-danger btn-sm btn-block" type="button"
                                                @click="removerDataHoraEntrega">Remover
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--    DESCRICAO  -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <h4><i class="fas fa-align-left"></i> Descrição</h4>
                            </div>
                            <div class="col-11 text-left">
                                <div class="form-group">
                                    <p v-if="TAREFA.descricao!=='' && !tarefaEditandoDescricao"
                                       :inner-html.prop="TAREFA.descricao | nl2br"
                                       @click="tarefaEditandoDescricao= !tarefaEditandoDescricao; aplicarAutoResize()"></p>
                                    <div
                                        v-if="(!tarefaEditandoDescricao && TAREFA.descricao==null) || tarefaEditandoDescricao"
                                        @mouseover="$refs.campoDescricaoTarefa.focus();">
                                        <textarea ref="campoDescricaoTarefa" v-model="TAREFA.descricao"
                                                  class="form-control mb-2 autoresize"
                                                  placeholder="Adicione uma descrição mais detalhada ..."
                                                  rows="3" style="resize: none"
                                                  @blur="updateTarefa"
                                                  @keydown="tarefaEditandoDescricao=true;autoresize()"></textarea>
                                        <button v-if="TAREFA.descricao!=null" class="btn btn-sm btn-success"
                                                @click="updateTarefa"> Salvar
                                        </button>
                                    </div>
                                </div>
                            </div>


                        </div>

                        <!--    Anexos  -->
                        <div v-if="TAREFA.anexos && TAREFA.anexos.length > 0" class="row mb-3">
                            <div class="col-12">
                                <h4><i class="fas fa-paperclip"></i> Anexos</h4>
                            </div>
                            <div class="col-11 text-left">
                                <table class="table table-sm table-hover">
                                    <tbody>
                                    <tr v-for="(anexo,index) in TAREFA.anexos" v-if="!anexo.falhou">
                                        <td>
                                            <div class="row no-gutters align-items-center">
                                                <div v-if="anexo.imagem && !anexo.enviando"
                                                     class="col-md-4 text-center">
                                                    <a :href="anexo.urlDownload" class="tarefa" target="_blank">
                                                        <img :alt="anexo.nome" :src="anexo.urlThumb"
                                                             class="card-img img-fluid"
                                                             style="max-width: 100px; max-height: 100px;">
                                                    </a>
                                                </div>
                                                <div v-if="!anexo.imagem && !anexo.enviando"
                                                     class="col-md-4 text-center">
                                                    <a :href="anexo.urlDownload" class="tarefa" target="_blank">
                                                        <svg id="Layer_1"
                                                             style="enable-background:new 0 0 512 512; height: 45px"
                                                             version="1.1"
                                                             viewBox="0 0 512 512" x="0px" xml:space="preserve"
                                                             xmlns="http://www.w3.org/2000/svg"
                                                             xmlns:xlink="http://www.w3.org/1999/xlink"
                                                             y="0px">
                                                            <path
                                                                d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"
                                                                style="fill:#E2E5E7;" />
                                                            <path d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"
                                                                  style="fill:#B0B7BD;" />
                                                            <polygon points="480,224 384,128 480,128 "
                                                                     style="fill:#CAD1D8;" />
                                                            <path
                                                                d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"
                                                                style="fill:#184056;" />
                                                            <g><!--	<path style="fill:#FFFFFF;" d=""/>-->
                                                                <text style="font-size:130px;fill:#FFFFFF;" x="45"
                                                                      y="380">{{ anexo.extensao }}</text></g>
                                                            <path
                                                                d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"
                                                                style="fill:#CAD1D8;" />
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
                                                    </a>
                                                </div>
                                                <div v-if="anexo.enviando" class="col-md-4 text-center">
                                                    <i class="fa fa-spinner fa-pulse fa-2x"></i>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ anexo.nome }}</h5>
                                                        <div v-if="anexo.enviando"
                                                             class="progress animated-progess mb-4">
                                                            <div :aria-valuenow="anexo.pctProgresso"
                                                                 :style="`width: ${anexo.pctProgresso}%`"
                                                                 aria-valuemax="100"
                                                                 aria-valuemin="0" class="progress-bar bg-info"
                                                                 role="progressbar">{{ anexo.pctProgresso }}%
                                                            </div>
                                                        </div>
                                                        <p v-if="!anexo.enviando" class="card-text">

                                                        <div class="dropdown mb-1">
                                                            <a class="dropdown-toggle" data-toggle="dropdown"
                                                               href="#" role="button"
                                                               @click.prevent="abrirFormEditarTituloAnexo(anexo)">
                                                                Editar
                                                            </a>
                                                            <div aria-labelledby="dropdownMenuLink"
                                                                 class="dropdown-menu mb-1">
                                                                <div class="card" style="width: 300px">
                                                                    <div class="card-header">
                                                                        Vincular nome
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <div class="form-group">
                                                                            <input :ref="`campoTituloAnexo${anexo.id}`"
                                                                                   v-model="tituloEditarAnexo"
                                                                                   class="form-control" type="text"
                                                                                   @keydown.enter="updateAnexo(anexo)">
                                                                        </div>
                                                                        <button class="btn  btn-sm btn-success"
                                                                                @click="updateAnexo(anexo)">Atualizar
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            </div>
                                                            -
                                                            <span class="dropdown mb-1">
                                                                    <a class="dropdown-toggle" data-toggle="dropdown"
                                                                       href="#" role="button">
                                                                        Excluir
                                                                    </a>
                                                                    <div aria-labelledby="dropdownMenuLink"
                                                                         class="dropdown-menu mb-1">
                                                                        <div class="card" style="width: 300px">
                                                                            <div class="card-header">
                                                                                Excluir anexo?
                                                                            </div>
                                                                            <div class="card-body">
                                                                                <p class="card-text">
                                                                                    A exclusão de um anexo é permanente. Não é possível desfazer.
                                                                                </p>
                                                                                <button
                                                                                    class="btn  btn-sm btn-block btn-danger"
                                                                                    @click="deleteAnexo(anexo)">Excluir</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </span>

                                                            </p>
                                                            <small v-if="!anexo.enviando" class="text-muted">Adcionado:
                                                                {{ anexo.created_at }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                        </td>
                                    </tr>
                                    </tbody>

                                </table>

                            </div>


                        </div>

                        <!--    Check list  -->
                        <draggable v-model="TAREFA.checklists" class="col-12" draggable=".listaChecklist"
                                   ghost-class="placeholder"
                                   group="listaChecklist"
                                   handle=".listaChecklist" @change="moveuCheckList">
                            <div v-for="(checklist,index) in TAREFA.checklists" class="row mb-3 listaChecklist">
                                <!--  Topo -->
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-1">
                                            <h4><i class="far fa-check-square"></i></h4>
                                        </div>
                                        <div class="col-9">
                                            <div v-if="checklistIdEditando===checklist.id" class="form-group">
                                                <input :ref="`checklist_${checklist.id}`" v-model="checklist.titulo"
                                                       class="form-control" type="text"
                                                       @blur="updateChecklist"
                                                       @mouseover="$refs[`checklist_${checklist.id}`][0].focus();"
                                                       @keydown.enter="updateChecklist">
                                            </div>
                                            <h4 v-if="checklistIdEditando==null || checklistIdEditando!==checklist.id"
                                                @mouseup="editarTituloChecklist(checklist)"> {{
                                                    checklist.titulo
                                                }} </h4>

                                        </div>
                                        <div class="col-2">
                                            <div class="dropdown mb-1">
                                                <a aria-expanded="false" aria-haspopup="true"
                                                   class="btn btn-sm btn-light dropdown-toggle text-left "
                                                   data-toggle="dropdown" href="#" role="button">
                                                    Excluir
                                                </a>
                                                <div aria-labelledby="dropdownMenuLink" class="dropdown-menu mb-1">
                                                    <div class="card" style="width: 300px">
                                                        <div class="card-header text-center">
                                                            Excluir Checklist ?
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="card-text">
                                                                A exclusão de uma checklist é permanente e não é
                                                                possível
                                                                recuperá-la.
                                                            </p>
                                                            <button class="btn btn-sm btn-danger btn-block"
                                                                    @click="removerChecklist(checklist)">Excluir
                                                                checklist
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--  progress bar -->
                                <div class="col-12">

                                    <div class="progress animated-progess mb-4">
                                        <div :aria-valuenow="infoProgresso(checklist).pct"
                                             :style="`width: ${Math.round(infoProgresso(checklist).pct)}%`"
                                             aria-valuemax="100"
                                             aria-valuemin="0" class="progress-bar bg-info" role="progressbar">
                                            {{ Math.round(infoProgresso(checklist).pct) }}%
                                        </div>
                                    </div>
                                </div>
                                <!--  lista de itens -->
                                <div class="col-12 mb-2">
                                    <draggable v-model="checklist.itens" class="col-12" draggable=".item"
                                               ghost-class="placeholder" group="itemChecklist" handle=".item"
                                               @change="moveuItem" @start="podeMoverItem=true" @end="podeMoverItem=true"
                                               :move="()=>podeMoverItem">
                                        <div v-for="(item,index) in checklist.itens" class="row item">
                                            <div class="col-12 col-sm-10">
                                                <div class="form-check">
                                                    <input v-model="item.concluido" class="form-check-input"
                                                           type="checkbox" @change="updateItemConcluir(item)">
                                                    <div v-if="checklistItemIdEditando!=null && ITEM_ID === item.id"
                                                         class="form-group">
                                                        <input :ref="`campoInputItem${item.id}`" v-model="ITEM.titulo"
                                                               class="form-control" placeholder="Descrição do item"
                                                               type="text" @blur="updateItem"
                                                               @keydown.enter="updateItem">
                                                    </div>
                                                    <label
                                                        v-if="checklistItemIdEditando==null && ITEM_ID==null || (checklistItemIdEditando!=null && ITEM_ID !== item.id)"
                                                        :class="{'textoRiscado':item.concluido}"
                                                        class="form-check-label"
                                                        @mouseup="abriFormItem(item)">{{ item.titulo }}</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-2">
                                                <div class="dropdown mb-1">
                                                    <a aria-expanded="false" aria-haspopup="true"
                                                       class="btn btn-sm btn-outline-danger dropdown-toggle border-0"
                                                       data-toggle="dropdown" href="#" role="button">
                                                        <i class="far fa-trash-alt"></i>
                                                    </a>
                                                    <div aria-labelledby="dropdownMenuLink" class="dropdown-menu mb-1">
                                                        <div class="card" style="width: 300px">
                                                            <div class="card-header text-center">
                                                                Excluir o item ?
                                                            </div>
                                                            <div class="card-body">
                                                                <p class="card-text">
                                                                    A exclusão de um item é permanente e não é possível
                                                                    recuperá-lo.
                                                                </p>
                                                                <button class="btn btn-sm btn-danger btn-block"
                                                                        @click="removerItemChecklist(item)">Excluir item
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <!-- Ultimo item novoForm-->
                                        <div class="row">
                                            <div class="col-12">
                                                <div v-if="formItem.id==null && formItem.checklist_id === checklist.id "
                                                     class="form-group">
                                                    <input :ref="`formNovoItemChecklist${CHECKLIST.id}`"
                                                           v-model="formItem.titulo" class="form-control"
                                                           placeholder="Adicionar um item"
                                                           type="text"
                                                           @blur="sairFormNovoItem"
                                                           @keydown.enter="addNovoItemChecklist">
                                                </div>
                                                <button v-if="formItem.id==null && formItem.checklist_id==null "
                                                        aria-expanded="false" aria-haspopup="true"
                                                        class="btn btn-sm btn-light"
                                                        @click.stop="abriFormNovoItem(checklist)">
                                                    Adicionar um item
                                                </button>
                                                <button
                                                    v-if="formItem.id==null && formItem.checklist_id === checklist.id "
                                                    :disabled="formItem.titulo===''"
                                                    aria-expanded="false" aria-haspopup="true"
                                                    class="btn btn-sm btn-success" @click="addNovoItemChecklist">
                                                    Adicionar
                                                </button>
                                                <button
                                                    v-if="formItem.id==null && formItem.checklist_id === checklist.id "
                                                    aria-expanded="false" aria-haspopup="true"
                                                    class="btn btn-sm btn-outline-secondary border-0"
                                                    @click="formItem.checklist_id=null">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </draggable>


                                </div>


                            </div>
                        </draggable>

                        <!-- Atividades (logs) -->
                        <!--    DATAS DE INÍCIO E ENTREGA  -->
                        <div class="row mb-3">

                            <div class="col-12">
                                <h4><i class="fas fa-tasks"></i> Atividades</h4>
                                <h5 class="text-center" v-show="TAREFA && TAREFA.logs.length===0">Nenhuma atividade
                                    recente</h5>
                            </div>
                            <div class="col-12" v-if="TAREFA && TAREFA.logs && TAREFA.logs.length > 0">
                                <div class="row">
                                    <div class="media mt-2 ml-2" v-for="atividade in TAREFA.logs">
                                        <div class="avatar-xs mr-3">
                                                <span class="avatar-title bg-primary rounded-circle font-size-16">
                                                    {{ atividade.usuario.nome.toUpperCase() | formataNome }}
                                                </span>
                                        </div>
                                        <div class="media-body">
                                            <!-- <h6 class="mt-0 mb-1"></h6>-->
                                            <p class="card-text"><strong>{{ atividade.usuario.nome }}</strong>
                                                {{ atividade.descricao }}
                                                <br>
                                                {{ atividade.created_at }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <!-- coluna direita  -->
                    <div class="col-12 col-sm-3">

                        <div class="dropdown mb-1">
                            <a aria-expanded="false" aria-haspopup="true"
                               class="btn btn-light btn-sm btn-block dropdown-toggle text-left " data-toggle="dropdown"
                               href="#" role="button">
                                <i class="fas fa-user"></i> Membros
                            </a>
                            <div aria-labelledby="dropdownMenuLink" class="dropdown-menu mb-1">
                                <div class="card" style="width: 300px">
                                    <div class="card-header">
                                        Adicionar membros
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <autocomplete v-model="autoCompleteNovoMembros"
                                                          :caminho="`${rotaTarefa}/buscarMembros`"
                                                          :formsm="false"
                                                          placeholder="Pesquisar membros"
                                                          @onblur="autoCompleteNovoMembros=''"
                                                          @onselect="addMembro"></autocomplete>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown mb-1">
                            <button aria-expanded="false" aria-haspopup="true"
                                    class="btn btn-light btn-sm btn-block dropdown-toggle text-left "
                                    data-toggle="dropdown" href="#" role="button"
                                    @click="$refs.campoTituloCheckList.focus()">
                                <i class="far fa-check-square"></i> Checklist
                            </button>
                            <div aria-labelledby="dropdownMenuLink" class="dropdown-menu mb-1">
                                <div class="card" style="width: 300px">
                                    <div class="card-header">
                                        Adicionar Checklist
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Título</label>
                                            <input ref="campoTituloCheckList" class="form-control" type="text"
                                                   value="Checklist">
                                        </div>
                                        <button class="btn btn-sm btn-success" @click="addCheckList">Adicionar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button :disabled="TAREFA.datahora_inicio!=null"
                                class="btn btn-light btn-sm btn-block mb-1 text-left" type="button"
                                @click="addDataHoraInicio">
                            <i class="far fa-clock"></i> Data de início
                        </button>
                        <button :disabled="TAREFA.datahora_entrega!=null"
                                class="btn btn-light btn-sm btn-block mb-1 text-left" type="button"
                                @click="addDataHoraEntrega">
                            <i class="far fa-clock"></i> Data Entrega
                        </button>
                        <upload :model="TAREFA.anexos" :simples="true" :template="true"
                                :url="url_tarefa_anexos"
                                label="Anexo"
                                @onFinalizado="anexoUploadAndamento=false"
                                @onProgresso="anexoUploadAndamento=true">
                            <template v-slot:template>
                                <button class="btn btn-light btn-sm btn-block mb-1 text-left" type="button">
                                    <i class="fas fa-paperclip"></i> Anexo
                                </button>
                            </template>
                        </upload>
                        <!-- Lembretes  -->
                        <div class="form-group mt-2" v-if="TAREFA.datahora_entrega!=null">
                            <label for="comboxLembreteTarefa">Definir lembrete</label>
                            <select class="form-control form-control-sm" id="comboxLembreteTarefa"
                                    v-model="TAREFA.lembreteText" @change="updateTarefaLembrete">
                                <option :value="null">Nenhum</option>
                                <option value="5m">5 minutos antes</option>
                                <option value="10m">10 minutos antes</option>
                                <option value="15m">15 minutos antes</option>
                                <option value="1H">1 hora antes</option>
                                <option value="2H">2 horas antes</option>
                                <option value="1d">1 dia antes</option>
                                <option value="2d">2 dias antes</option>
                            </select>
                        </div>

                    </div>
                </div>


            </template>
        </modal>

        <!-- Tela quadros-->
        <div v-if="!QUADRO" class="row">
            <div class="col-12">
                <div class="row">
                    <div v-if="quadro_insert" class="col form-inline">
                        <form @submit.prevent="addQuadro">
                            <input v-model="formQuadros.titulo" :disabled="preloadQuadro" class="form-control form-control-sm"
                                   placeholder="Nome do Quadro" type="text">
                            <button :disabled="formQuadros.titulo==='' || preloadQuadro" class="btn btn-primary btn-sm ml-3"
                                    type="submit" @click="addQuadro"><i class="fa fa-plus"></i> Novo quadro
                            </button>
                        </form>
                    </div>
                </div>
                <div v-show="listaQuadros.length==0" class="alert alert-info mt-5" role="alert">
                    <h4 class="text-center"> Nenhum quadro criado </h4>
                </div>

                <div class="row mt-2">
                    <div v-for="(quadro,index) in listaQuadros" class="col-md-4">
                        <div class="card mt-3" style="border: 1px solid #cccccc">
                            <h5 v-if="alterandoQuadro==null || quadro.id !== alterandoQuadro.id"
                                class="card-header bg-transparent border-bottom text-uppercase"
                                @click="quadro_update ? alterandoQuadro = quadro:false;">{{ quadro.titulo }}</h5>
                            <input v-if="alterandoQuadro!=null && quadro.id===alterandoQuadro.id"
                                   :ref="'card_quadro_'+quadro.id" v-model="quadro.titulo" class="form-control"
                                   placeholder="Nome do Quadro" type="text" @blur="updateQuadro"
                                   @mouseover="$refs[`card_quadro_${quadro.id}`][0].focus();"
                                   @keydown.enter="updateQuadro">
                            <div class="card-body">

                                <button class="btn btn-sm btn-primary" @click="verQuadro(quadro)">Abrir quadro</button>
                                <button v-if="quadro_delete" class="btn btn-sm btn-danger"
                                        data-target="#janelaApagarQuadro"
                                        data-toggle="modal"
                                        @click="abrirFormApagarQuadro(quadro)">
                                    <span class="fa fa-trash"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <!-- Tela de listas -->
        <div v-if="QUADRO" class="row " @click="fecharCampos()">
            <div class="col-12">
                <!-- botao de voltar-->
                <div class="row mb-3">
                    <div class="col-12">
                        <a class="btn btn-sm btn-primary" href="#" @click.prevent="voltarParaQuadros">
                            <i class="fas fa-arrow-left"></i>
                            Voltar para quadros
                        </a>
                    </div>
                </div>

                <div class="bg-primary mb-3 navbar navbar-expand-lg navbar-light pt-3 rounded text-white">
                    <h4 class="text-white">{{ QUADRO.titulo }}</h4>
                </div>
                <!-- Lista de tarefas-->
                <div class="row">
                    <div class="col-sm-10 col-12">
                        <div class="row mr-1" style="overflow-x:auto; min-height: 80vh;">
                            <draggable v-model="arrayListas" class="d-flex" draggable=".listaTarefa"
                                       ghost-class="placeholder"
                                       group="listaTarefas"
                                       handle=".corpoListaTarefa" @change="moveuLista">
                                <!-- elemento lista-->
                                <div v-for="(lista,index) in arrayListas" class="flex-fill mr-3 listaTarefa"
                                     style="width: 300px;margin-left: 0.8rem!important;">
                                    <div class="card corpoListaTarefa" style="border: 1px solid #cccccc">
                                        <div class="card-body">
                                            <!-- topo-->
                                            <div class="row">
                                                <div class="col-10">
                                                    <h4 v-if="alterandoLista==null || lista.id !== alterandoLista.id"
                                                        class="card-title mb-4"
                                                        @click="lista_update ? alterandoLista = lista:false">
                                                        {{ lista.titulo }}</h4>
                                                    <input v-if="alterandoLista!=null && lista.id===alterandoLista.id"
                                                           :ref="'lista_tarefa_'+lista.id" v-model="lista.titulo"
                                                           class="form-control"
                                                           placeholder="Nome da lista" type="text" @blur="updateLista"
                                                           @mouseover="$refs[`lista_tarefa_${lista.id}`][0].focus();"
                                                           @keydown.enter="updateLista">
                                                </div>
                                                <div v-if="lista_update || lista_delete" class="col-2">
                                                    <div class="dropdown">
                                                        <button aria-expanded="false" aria-haspopup="true"
                                                                class="btn border-0 dropdown-toggle btn-outline-secondary"
                                                                data-toggle="dropdown"
                                                                type="button">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </button>
                                                        <div aria-labelledby="dropdownMenuButton" class="dropdown-menu">
                                                            <a v-if="lista_update" class="dropdown-item text-primary"
                                                               href="#" @click.prevent="alterandoLista=lista">
                                                                <i class="far fa-edit"></i> Editar
                                                            </a>
                                                            <a v-if="lista_delete" class="dropdown-item text-danger"
                                                               data-target="#janelaApagarListaTarefa"
                                                               data-toggle="modal"
                                                               href="#"
                                                               @click="abrirFormApagarLista(lista)">
                                                                <i class="far fa-trash-alt"></i> Apagar
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--  Tarefas -->
                                            <div class="row">

                                                <draggable v-model="lista.tarefas" class="col-12" draggable=".tarefa"
                                                           ghost-class="placeholder" group=".tarefas" handle=".tarefa"
                                                           @change="moveuTarefa" @start="podeMoverTarefa=true"
                                                           @end="podeMoverTarefa=true" :move="()=>podeMoverTarefa">
                                                    <div v-for="(tarefa,index) in lista.tarefas" class="w-100 tarefa">
                                                        <div
                                                            :class="{'text-white bg-danger':tarefa.datahora_entrega && tarefa.emAtraso && !tarefa.concluido, 'text-white bg-success':tarefa.concluido}"
                                                            class="card card-body" data-target="#janelaTarefa"
                                                            data-toggle="modal"
                                                            style="border: 1px solid #cccccc"
                                                            @click="verTarefa(tarefa,lista)">

                                                            <div class="row">
                                                                <div class="col-10">
                                                                    <p class="card-text"> {{ tarefa.titulo }} </p>
                                                                </div>

                                                                <div v-if="tarefa_delete" class="col-1">
                                                                    <button aria-expanded="false" aria-haspopup="true"
                                                                            class="btn border-0 p-0 pb-1 pl-1 pr-1 btn-outline-secondary"
                                                                            type="button"
                                                                            @click.prevent.stop="abrirFormApagarTarefa(lista,tarefa)">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Form nova tarefa -->
                                                    <div v-if="tarefa_insert" class="w-100">
                                                        <div class="card card-body" style="border: 2px dashed #bcb8b8"
                                                             @click.stop="abriFormNovaTarefa(lista)">
                                                            <p v-if="TAREFA!=null || (formTarefa.lista_id===null || (LISTA && LISTA.id!==lista.id))"
                                                               class="card-text">
                                                                <i class="fas fa-tasks"></i>
                                                                <template v-if="lista.tarefas.length > 0">Adicionar
                                                                    outra
                                                                    tarefa
                                                                </template>
                                                                <template v-if="lista.tarefas.length === 0">Adicionar
                                                                    uma
                                                                    tarefa
                                                                </template>
                                                            </p>

                                                            <template
                                                                v-if="formTarefa.lista_id !=null && LISTA && LISTA.id===lista.id && TAREFA==null">
                                                                <div class="card-text">
                                                                    <div class="form-group">
                                                                <textarea :ref="`formNovaTarefa_lista${lista.id}`"
                                                                          v-model="formTarefa.titulo"
                                                                          :disabled="preload" class="form-control mb-2"
                                                                          placeholder="Insira o título para esta tarefa"
                                                                          rows="3"
                                                                          @keydown.enter="addTarefa"></textarea>
                                                                    </div>
                                                                    <button :disabled="preload"
                                                                            class="btn btn-sm btn-success waves-effect waves-light"
                                                                            @click="addTarefa">Adicionar tarefa
                                                                    </button>
                                                                    <button :disabled="preload"
                                                                            class="btn btn-sm btn-outline-primary border-0"
                                                                            @click.stop="fecharCampos()">
                                                                        <i class="fas fa-times fa-2x"></i>
                                                                    </button>
                                                                </div>
                                                            </template>

                                                        </div>
                                                    </div>

                                                </draggable>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- Form nova lista-->
                                <div v-if="lista_insert" class="flex-fill mr-3"
                                     style="width: 300px; margin-left: 0.8rem!important;">
                                    <div class="card card-body" style="border: 2px dashed #cccccc"
                                         @click.stop="abriFormNovaLista">
                                        <p v-if="formLista.quadro_id===null" class="card-text">
                                            <i class="fas fa-plus"></i>
                                            <template v-if="arrayListas.length > 0">Adicionar outra lista</template>
                                            <template v-if="arrayListas.length === 0">Adicionar uma lista</template>
                                        </p>
                                        <template v-if="formLista.quadro_id !=null">
                                            <div class="card-text">
                                                <input
                                                    ref="formNovaLista" v-model="formLista.titulo" :disabled="preload"
                                                    class="form-control mb-2" placeholder="Insira o título da lista"
                                                    type="text"
                                                    @keydown.enter="addLista">
                                                <button :disabled="preload"
                                                        class="btn btn-sm btn-success waves-effect waves-light"
                                                        @click="addLista">Adicionar lista
                                                </button>
                                                <button :disabled="preload"
                                                        class="btn btn-sm btn-outline-primary border-0"
                                                        @click.stop="fecharCampos()">
                                                    <i class="fas fa-times fa-2x"></i>
                                                </button>
                                            </div>
                                        </template>

                                    </div>
                                </div>
                            </draggable>

                        </div>
                    </div>
                    <div class="col-sm-2 col-12 corpoListaTarefa">
                        <h4 class="mt-3"><i class="fas fa-tasks"></i> Atividades</h4>
                        <h5 class="text-center" v-show="atividadesQuadro.length===0">Nenhuma atividade recente</h5>
                        <div class="card" v-for="atividade in atividadesQuadro">
                            <div class="card-body">
                                <div class="media">
                                    <div class="avatar-xs mr-3">
                                        <span class="avatar-title bg-primary rounded-circle font-size-16">
                                            {{ atividade.usuario.nome.toUpperCase() | formataNome }}
                                        </span>
                                    </div>
                                    <div class="media-body">
                                        <!-- <h6 class="mt-0 mb-1"></h6>-->
                                        <p class="card-text mt-2"><strong>{{ atividade.usuario.nome }}</strong>
                                            {{ atividade.descricao }}</p>
                                    </div>
                                </div>
                                <p class="card-text mt-2">{{ atividade.created_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</template>

<script>
//import draggable
import draggable from "vuedraggable";
import autocomplete from "./AutoComplete";
import nl2br from "../filters/nl2br";
import formataNome from "../filters/formataNomeUser";
import datepicker from "../components/DatePicker";
import upload from "../components/Upload";


export default {
    filters: {
        nl2br,
        formataNome
    },
    components: {
        draggable,
        autocomplete,
        datepicker,
        upload
    },
    props: {
        id: { // cliente_id
            type: Number,
            required: true,
            default: 0
        },
        modalPai: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            URL_ADMIN: URL_ADMIN,
            quadro_insert: false,
            quadro_update: false,
            quadro_delete: false,
            lista_insert: false,
            lista_update: false,
            lista_delete: false,
            tarefa_insert: false,
            tarefa_update: false,
            tarefa_delete: false,

            listaQuadros: [],
            preload: false,
            preloadQuadro: false,
            formQuadros: {
                id: null,
                titulo: ""
            },
            alterandoQuadro: null,

            formApagarQuadro: {
                id: null,
                preload: false,
                delete: false,
                erro: false,
                msg: ""
            },

            //QUADRO: null,
            QUADRO_ID: null,
            formQuadrosDefault: null,
            atividadesQuadro: [],

            //Tela Listas --------------------
            formLista: {
                id: null,
                quadro_id: null,
                user_id: null,
                titulo: "",
                ordem: null,
                created_at: null,
                updated_at: null,
                tarefas: []
            },
            preloadFormLista: false,
            formListaDefault: null,
            arrayListas: [],
            alterandoLista: null,

            formApagarLista: {
                id: null,
                preload: false,
                delete: false,
                erro: false,
                msg: ""
            },

            //LISTA: null,
            LISTA_ID: null,

            // Tarefas -------------------
            podeMoverTarefa: true,
            formTarefa: {
                id: null,
                lista_id: null,
                user_id: null,
                titulo: "",
                descricao: "",
                ordem: null,
                datahora_entrega: null,
                created_at: null,
                updated_at: null,
                preload: false,
                membros: []
            },
            formTarefaDefault: null,
            tarefaEditandoTitulo: false,
            tarefaEditandoDescricao: false,
            autoCompleteNovoMembros: "",
            TAREFA_ANTERIOR: null, // para evento de mover tarefa entre listas
            TAREFA_ID: null,

            formApagarTarefa: {
                id: null,
                preload: false,
                delete: false,
                erro: false,
                msg: ""
            },
            anexoUploadAndamento: false,

            // Checklists -----------------------
            CHECKLIST_ID: null,
            checklistIdEditando: null,
            // Itens -----------------------
            podeMoverItem: true,
            ITEM_ID: null,
            ITEM_ANTERIOR: null, // para evento de mover itens entre checklists
            formItem: {
                id: null,
                checklist_id: null,
                titulo: "",
                concluido: false,
                ordem: null,
                preload: false
            },
            checklistItemIdEditando: null,
            // Anexos -----------------------
            tituloEditarAnexo: ""


        };
    },
    computed: {
        rotaTarefa() {
            return `weekly-report/${this.id}/quadros/${this.QUADRO_ID}/listas/${this.LISTA_ID}/tarefas/${this.TAREFA_ID}`;
        },
        rotaChecklist() {
            return `weekly-report/${this.id}/quadros/${this.QUADRO_ID}/listas/${this.LISTA_ID}/tarefas/${this.TAREFA_ID}/checklist/${this.CHECKLIST_ID}`;
        },
        rotaChecklistItem() {
            return `weekly-report/${this.id}/quadros/${this.QUADRO_ID}/listas/${this.LISTA_ID}/tarefas/${this.TAREFA_ID}/checklist/${this.CHECKLIST_ID}/item/${this.ITEM_ID}`;
        },
        QUADRO() {
            let quadro = null;
            let busca = _.find(this.listaQuadros, { id: this.QUADRO_ID });
            if (busca) {
                return busca;
            }
            return quadro;
        },
        LISTA() {
            let lista = null;
            let busca = _.find(this.arrayListas, { id: this.LISTA_ID });
            if (busca) {
                return busca;
            }
            return lista;
        },
        TAREFA() {
            let tarefa = null;
            this.arrayListas.forEach((lista) => {
                let tarefaFind = _.find(lista.tarefas, { id: this.TAREFA_ID });
                if (tarefaFind) {
                    tarefa = tarefaFind;
                    return false;
                }
            });
            return tarefa;
        },
        CHECKLIST() {
            let checklist = null;
            if (this.TAREFA_ID) {
                let checklistFind = _.find(this.TAREFA.checklists, { id: this.CHECKLIST_ID });
                if (checklistFind) {
                    checklist = checklistFind;
                }
            }

            return checklist;
        },
        ITEM() {
            let item = null;
            if (this.CHECKLIST_ID) {
                let itemFind = _.find(this.CHECKLIST.itens, { id: this.ITEM_ID });
                if (itemFind) {
                    item = itemFind;
                }
            }

            return item;
        },
        url_tarefa_anexos() {
            //return `${this.rotaTarefa}/uploadAnexo`
            return `${this.rotaTarefa}/uploadAnexos`;
        }
    },
    mounted() {
        this.formQuadrosDefault = _.cloneDeep(this.formQuadros);
        this.formListaDefault = _.cloneDeep(this.formLista);
        this.formTarefaDefault = _.cloneDeep(this.formTarefa);
        //this.$emit('carregandoQuadros', {});

        //Tela de quadros ------------------------------------------
        this.preload = true;
        axios.get(`${URL_ADMIN}/weekly-report/${this.id}`)
            .then((response) => {
                this.listaQuadros = response.data.lista;

                this.quadro_insert = response.data.quadro_insert;
                this.quadro_update = response.data.quadro_update;
                this.quadro_delete = response.data.quadro_delete;
                this.lista_insert = response.data.lista_insert;
                this.lista_update = response.data.lista_update;
                this.lista_delete = response.data.lista_delete;
                this.tarefa_insert = response.data.tarefa_insert;
                this.tarefa_update = response.data.tarefa_update;
                this.tarefa_delete = response.data.tarefa_delete;

                this.preload = false;

                //this.$emit('carregou', {});
            })
            .catch((data) => {
                this.preload = false;
            });

        // Logs
        Echo.join(`weekly-report.log.${this.id}`)
            .listen(".log", (e) => {
                if (e.log.tarefa_id != null) {
                    if (this.TAREFA_ID === e.log.tarefa_id) {
                        this.TAREFA.logs.unshift(e.log);
                    }
                } else {
                    this.atividadesQuadro.unshift(e.log);
                }
            });

        // Quadros
        Echo.join(`weekly-report.quadros.${this.id}`)
            .listen(".insert", (e) => {
                this.listaQuadros.push(e.quadro);
            })
            .listen(".update", (e) => {
                let quadroFind = _.find(this.listaQuadros, { id: e.quadro.id });
                if (quadroFind) {
                    Object.assign(quadroFind, e.quadro);
                }
            })
            .listen(".delete", (e) => {
                let indexDelete = _.findIndex(this.listaQuadros, { id: e.id });
                this.listaQuadros.splice(indexDelete, 1);
            });

        // Lista de tarefas
        Echo.join(`weekly-report.listas.${this.id}`)
            .listen(".insert", (e) => {
                e.lista.forEach((lista) => {
                    let listaFind = _.find(this.arrayListas, { id: lista.id });
                    if (listaFind) {
                        Object.assign(listaFind, lista);
                    } else {
                        this.arrayListas.push(lista);
                    }
                });
                //this.arrayListas = e.lista;
            })
            .listen(".update", (e) => {
                e.lista.forEach((lista) => {
                    let listaFind = _.find(this.arrayListas, { id: lista.id });
                    if (listaFind) {
                        Object.assign(listaFind, lista);
                    } else {
                        this.arrayListas.push(lista);
                    }
                });
            })
            .listen(".delete", (e) => {
                e.lista.forEach((lista) => {
                    let listaFind = _.find(this.arrayListas, { id: lista.id });
                    if (listaFind) {
                        Object.assign(listaFind, lista);
                    }
                });
                //agaga da lista pelo ID
                let listaFindDelete = _.findIndex(this.arrayListas, { id: e.idDelete });
                if (listaFindDelete !== -1) {
                    this.arrayListas.splice(listaFindDelete, 1);
                }
            })
            .listen(".ordenar", (e) => {
                e.lista.forEach((lista) => {
                    let listaFind = _.find(this.arrayListas, { id: lista.id });
                    if (listaFind) {
                        Object.assign(listaFind, lista);
                    }
                });
                this.arrayListas = _.orderBy(this.arrayListas, ["ordem"]);
            });

        // Tarefas
        Echo.join(`weekly-report.tarefas.${this.id}`)
            .listen(".insert", (e) => {
                e.tarefas.forEach((tarefa) => {
                    //busca em todas as lista, onde essa tarefa esta...
                    let listaFind = _.find(this.arrayListas, { id: tarefa.lista_id });
                    if (listaFind) {
                        //encontrou a lista, entao atualiza a tarefa ou inserir
                        let tarefaFind = _.find(listaFind.tarefas, { id: tarefa.id });
                        if (tarefaFind) {
                            Object.assign(tarefaFind, tarefa);
                        } else {
                            listaFind.tarefas.push(tarefa);
                        }
                    }
                });
            })
            .listen(".update", (e) => {
                this.arrayListas.forEach((lista) => {
                    let tarefaFind = _.find(lista.tarefas, { id: e.tarefa.id });
                    if (tarefaFind) {
                        Object.assign(tarefaFind, e.tarefa);
                        return false;
                    }
                });
            })
            .listen(".delete", (e) => {
                e.tarefas.forEach((tarefa) => {
                    //busca em todas as lista, onde essa tarefa esta...
                    let listaFind = _.find(this.arrayListas, { id: tarefa.lista_id });
                    if (listaFind) {
                        //encontrou a lista, entao atualiza a tarefa
                        let tarefaFind = _.find(listaFind.tarefas, { id: tarefa.id });
                        if (tarefaFind) {
                            Object.assign(tarefaFind, tarefa);
                        }
                        return false;
                    }
                });
                // removendo o ID da tarefa em alguma lista
                this.arrayListas.forEach((lista) => {
                    //agora ir nas taregas
                    let tarefaFindDelete = _.findIndex(lista.tarefas, { id: e.idDelete });
                    if (tarefaFindDelete !== -1) {
                        lista.tarefas.splice(tarefaFindDelete, 1);
                        return false;
                    }
                });
            })
            .listen(".ordenar", (e) => {
                //setTimeout(() => {
                this.podeMoverTarefa = false;
                if (e.tarefas.length === 0) {
                    return false;
                } else {
                    //remover de outras listas
                    let outrasListas = this.arrayListas.filter(lista => lista.id !== e.lista_id);
                    if (outrasListas) {
                        outrasListas.forEach((outra_lista) => {
                            e.tarefas.forEach((novaTarefa) => {
                                _.remove(outra_lista.tarefas, (o) => o.id === novaTarefa.id);
                            });
                        });
                    }
                    //inserir
                    let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                    if (listaFind) {
                        e.tarefas.forEach((novaTarefa) => {
                            let buscarTarefa = _.find(listaFind.tarefas, (tarefa) => tarefa.id === novaTarefa.id);
                            if (buscarTarefa) {
                                Object.assign(buscarTarefa, novaTarefa);
                            } else {
                                listaFind.tarefas.push(novaTarefa);
                            }
                        });
                        //listaFind.tarefas = e.tarefas;
                        listaFind.tarefas = _.orderBy(listaFind.tarefas, ["ordem"]);
                    }

                }
                //}, 5000);

            })
            .listen(".ordenarChecklist", (e) => {
                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarafa_id });
                    if (tarefaFind) {
                        tarefaFind.checklists = e.checklists;
                        tarefaFind.checklists = _.orderBy(tarefaFind.checklists, ["ordem"]);
                    }

                }


            })

            .listen(".updateMembro", (e) => {
                this.arrayListas.forEach((lista) => {
                    let tarefaFind = _.find(lista.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        tarefaFind.membros = e.membros;
                        return false;
                    }
                });

            })
            .listen(".updateDataHoraInicio", (e) => {
                this.arrayListas.forEach((lista) => {
                    let tarefaFind = _.find(lista.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        tarefaFind.datahora_inicio = e.datahora_inicio;
                        return false;
                    }
                });
            })
            .listen(".updateDataHoraEntrega", (e) => {
                this.arrayListas.forEach((lista) => {
                    let tarefaFind = _.find(lista.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        tarefaFind.datahora_entrega = e.datahora_entrega;
                        tarefaFind.concluido = e.concluido;
                        tarefaFind.emAtraso = e.emAtraso;
                        return false;
                    }
                });
            });

        // Anexos tarefas
        Echo.join(`weekly-report.tarefas.anexos.${this.id}`)
            .listen(".insert", (e) => {
                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        tarefaFind.anexos = e.anexos;
                        return false;
                    }
                }
            })
            .listen(".update", (e) => {
                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        tarefaFind.anexos = e.anexos;
                        return false;
                    }
                }
            })
            .listen(".delete", (e) => {

                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        tarefaFind.anexos = e.anexos;
                        return false;
                    }
                }
            });

        // Checklists
        Echo.join(`weekly-report.tarefas.checklists.${this.id}`)
            .listen(".insert", (e) => {
                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        e.checklists.forEach((checklist) => {
                            let checkListFind = _.find(tarefaFind.checklists, { id: checklist.id });
                            if (checkListFind) {
                                Object.assign(checkListFind, checklist);
                            } else {
                                tarefaFind.checklists.push(checklist);
                            }
                        });
                    }
                }
            })
            .listen(".update", (e) => {
                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        e.checklists.forEach((checklist) => {
                            let checkListFind = _.find(tarefaFind.checklists, { id: checklist.id });
                            if (checkListFind) {
                                Object.assign(checkListFind, checklist);
                            }
                        });
                    }
                }
            })
            .listen(".delete", (e) => {
                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        let checklistFind = _.findIndex(tarefaFind.checklists, { id: e.checklist_id });
                        if (checklistFind !== -1) {
                            tarefaFind.checklists.splice(checklistFind, 1);
                            return false;
                        }
                    }
                }
            })
            .listen(".ordenar_itens", (e) => {
                //setTimeout(() => {
                this.podeMoverItem = false;
                if (e.checklist.itens.length === 0) {
                    return false;
                } else {
                    let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                    if (listaFind) {
                        let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                        if (tarefaFind) {
                            //remover de outras checklists
                            let outrasChecklists = tarefaFind.checklists.filter(cl => cl.id !== e.checklist.id);
                            if (outrasChecklists) {
                                //de todas essas listas...remover todos esse itens que chegaram
                                outrasChecklists.forEach((novaCL) => {
                                    e.checklist.itens.forEach((novoItem) => {
                                        _.remove(outrasChecklists.itens, (o) => o.id === novoItem.id);
                                    });
                                });
                            }
                            //inserir
                            let checklistFind = _.find(tarefaFind.checklists, { id: e.checklist.id });
                            if (checklistFind) {
                                e.checklist.itens.forEach((novoItem) => {
                                    let buscarItem = _.find(checklistFind.itens, (item) => item.id === novoItem.id);
                                    if (buscarItem) {
                                        Object.assign(buscarItem, novoItem);
                                    } else {
                                        checklistFind.itens.push(novoItem);
                                    }
                                });
                                //checklistFind.itens = e.checklist.itens;
                                checklistFind.itens = _.orderBy(checklistFind.itens, ["ordem"]);
                            }
                        }
                    }
                }

                //}, 5000);
            });

        // Checklists > itens
        Echo.join(`weekly-report.tarefas.checklists.itens.${this.id}`)
            .listen(".insert", (e) => {
                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        let checklistFind = _.find(tarefaFind.checklists, { id: e.checklist_id });
                        if (checklistFind) {
                            e.itens.forEach((item) => {
                                let itemFind = _.find(checklistFind.itens, { id: item.id });
                                if (itemFind) {
                                    Object.assign(itemFind, item);
                                } else {
                                    checklistFind.itens.push(item);
                                }
                            });
                        }

                    }
                }
            })
            .listen(".update", (e) => {
                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        let checklistFind = _.find(tarefaFind.checklists, { id: e.checklist_id });
                        if (checklistFind) {
                            let itemFind = _.find(checklistFind.itens, { id: e.item_id });
                            if (itemFind) {
                                itemFind.titulo = e.item.titulo;
                                itemFind.concluido = e.item.concluido;
                            }
                        }

                    }
                }
            })
            .listen(".delete", (e) => {
                let listaFind = _.find(this.arrayListas, { id: e.lista_id });
                if (listaFind) {
                    let tarefaFind = _.find(listaFind.tarefas, { id: e.tarefa_id });
                    if (tarefaFind) {
                        let checklistFind = _.find(tarefaFind.checklists, { id: e.checklist_id });
                        if (checklistFind) {
                            let itemFind = _.findIndex(checklistFind.itens, { id: e.item_id });
                            if (itemFind !== -1) {
                                checklistFind.itens.splice(itemFind, 1);
                                return false;
                            }
                        }

                    }
                }
            });
    },
    methods: {
        //Tela de quadros ------------------
        voltarParaQuadros() {
            this.QUADRO_ID = null;
            this.fecharCampos();
        },
        addQuadro() {
            this.preloadQuadro = true;
            let quadro = _.cloneDeep(this.formQuadros);

            axios.post(`${URL_ADMIN}/weekly-report/${this.id}/quadros`, quadro)
                .then((response) => {
                    this.preloadQuadro = false;
                })
                .catch((data) => {
                    this.preloadQuadro = false;
                });
            this.formQuadros = _.cloneDeep(this.formQuadrosDefault);
        },
        updateQuadro() {
            if (this.alterandoQuadro && this.alterandoQuadro.titulo.trim() === "") {
                mostraErro("", "Informa um nome para o quadro");
                return false;
            }
            if (this.alterandoQuadro) {
                axios.put(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.alterandoQuadro.id}`, this.alterandoQuadro);
            }
            this.alterandoQuadro = null;
        },
        abrirFormApagarQuadro(obj) {
            this.formApagarQuadro.id = obj.id;
            this.formApagarQuadro.preload = false;
            this.formApagarQuadro.delete = false;
            this.formApagarQuadro.erro = false;
            this.formApagarQuadro.msg = "";

        },
        deleteQuadro() {
            this.formApagarQuadro.preload = true;
            axios.delete(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.formApagarQuadro.id}`)
                .then((response) => {
                    this.formApagarQuadro.preload = false;
                    this.formApagarQuadro.delete = true;
                    //this.listaQuadros = response.data.lista;
                })
                .catch((response) => {
                    this.formApagarQuadro.msg = response.data.msg;
                    this.formApagarQuadro.preload = false;
                    this.formApagarQuadro.erro = true;
                    //this.listaQuadros = response.data.lista;
                });
        },
        verQuadro(objQuadro) {
            this.QUADRO_ID = objQuadro.id;
            this.preload = true;
            axios.get(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas`)
                .then((response) => {
                    this.arrayListas = response.data.lista;
                    this.atividadesQuadro = response.data.atividades;
                    this.preload = false;
                })
                .catch((data) => {
                    this.preload = false;
                });
        },
        //Tela Listas --------------------
        abriFormNovaLista() {
            this.formLista.quadro_id = this.QUADRO.id;
            setTimeout(() => {
                this.$refs.formNovaLista.focus();
            }, 100);
        },
        fecharCampos() {
            // nova lista
            if (this.formLista.quadro_id != null) {
                this.formLista.quadro_id = null;
            }
            // nova tarefa
            if (this.formTarefa.lista_id != null) {
                this.formTarefa.lista_id = null;
            }
            // nova item do checklist
            /*if (this.formItem.checklist_id != null) {
                this.formItem.checklist_id=null;
            }
            console.log('teste');*/

        },
        addLista() {
            let lista = _.cloneDeep(this.formLista);
            this.preload = true;
            axios.post(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas`, lista)
                .then((response) => {
                    this.formLista.titulo = "";
                    this.preload = false;
                    setTimeout(() => {
                        this.$refs.formNovaLista.focus();
                    }, 100);
                })
                .catch((data) => {
                    this.preload = false;
                    setTimeout(() => {
                        this.$refs.formNovaLista.focus();
                    }, 100);
                });


        },
        updateLista() {
            if (this.alterandoLista && this.alterandoLista.titulo.trim() === "") {
                mostraErro("", "Informa um nome para a lista");
                return false;
            }
            if (this.alterandoLista) {
                axios.put(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${this.alterandoLista.id}`, this.alterandoLista);
            }
            this.alterandoLista = null;
        },
        abrirFormApagarLista(obj) {
            this.formApagarLista.id = obj.id;
            this.formApagarLista.preload = false;
            this.formApagarLista.delete = false;
            this.formApagarLista.erro = false;
            this.formApagarLista.msg = "";
        },
        deleteLista() {
            this.formApagarLista.preload = true;
            axios.delete(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${this.formApagarLista.id}`)
                .then((response) => {
                    this.formApagarLista.preload = false;
                    this.formApagarLista.delete = true;
                    //this.listaQuadros = response.data.lista;
                })
                .catch((response) => {
                    this.formApagarLista.msg = response.data.msg;
                    this.formApagarLista.preload = false;
                    this.formApagarLista.erro = true;
                    //this.listaQuadros = response.data.lista;
                });
        },
        moveuLista(event, originalEvent) {
            let ordem = 1;
            this.arrayListas.forEach((obj) => {
                obj.ordem = ordem;
                ordem++;
            });
            let novaLista = this.arrayListas.map((obj) => {
                return {
                    id: obj.id,
                    ordem: obj.ordem
                };
            });
            axios.put(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas`, { novaLista })
                .then((response) => {
                    //this.arrayListas = response.data.lista;
                    //this.pode_insert = data.pode_insert;
                    //this.pode_update = data.pode_update;
                    //this.pode_delete = data.pode_delete;
                    //this.pode_realizar = data.pode_realizar;
                    //this.preload = false;

                    //this.$emit('carregou', {});
                })
                .catch((data) => {
                    //this.preload = false;
                });
        },
        // Tarefas ---------------------------
        aplicarAutoResize() {
            setTimeout(this.autoresize, 50);
        },
        autoresize() {
            let textArea = this.$refs.campoDescricaoTarefa;
            textArea.style.height = "auto";
            textArea.style.height = (textArea.scrollHeight + 30) + "px";
            textArea.scrollTop = textArea.scrollHeight;
            //window.scrollTo(window.scrollLeft,(textArea.scrollTop + textArea.scrollHeight));
        },
        abriFormNovaTarefa(objLista) {
            this.LISTA_ID = objLista.id;
            this.TAREFA_ID = null;
            this.formTarefa.lista_id = this.LISTA.id;
            setTimeout(() => {
                this.$refs[`formNovaTarefa_lista${this.LISTA.id}`][0].focus();
            }, 100);
        },
        addTarefa() {
            let tarefa = _.cloneDeep(this.formTarefa);
            this.preload = true;
            axios.post(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${this.LISTA.id}/tarefas`, tarefa)
                .then((response) => {
                    this.formTarefa.titulo = "";
                    this.preload = false;
                    //this.listaQuadros = response.data.lista;
                    setTimeout(() => {
                        this.$refs[`formNovaTarefa_lista${this.LISTA.id}`][0].focus();
                    }, 100);
                })
                .catch((data) => {
                    this.preload = false;
                    setTimeout(() => {
                        this.$refs[`formNovaTarefa_lista${this.LISTA.id}`][0].focus();
                    }, 100);
                });
        },
        abrirFormApagarTarefa(objLista, obj) {
            this.LISTA_ID = objLista.id;
            this.formApagarTarefa.id = obj.id;
            this.formApagarTarefa.preload = false;
            this.formApagarTarefa.delete = false;
            this.formApagarTarefa.erro = false;
            this.formApagarTarefa.msg = "";
            $("#janelaApagarTarefa").modal("show");
        },
        deleteTarefa() {
            this.formApagarTarefa.preload = true;
            axios.delete(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${this.LISTA.id}/tarefas/${this.formApagarTarefa.id}`)
                .then((response) => {
                    this.formApagarTarefa.preload = false;
                    this.formApagarTarefa.delete = true;
                })
                .catch((response) => {
                    this.formApagarTarefa.msg = response.data.msg;
                    this.formApagarTarefa.preload = false;
                    this.formApagarTarefa.erro = true;
                });
        },
        moveuTarefa(event) {
            let TAREFA = null;
            if (event.moved) {
                TAREFA = event.moved.element;
                let ordem = 1;
                let LISTA = null;
                let listaFind = _.find(this.arrayListas, { id: TAREFA.lista_id });
                if (listaFind) {
                    listaFind.tarefas.forEach((obj) => {
                        obj.ordem = ordem;
                        obj.lista_id = listaFind.id;
                        LISTA = listaFind;
                        ordem++;
                    });
                    let novaLista = listaFind.tarefas.map((obj) => {
                        return {
                            id: obj.id,
                            lista_id: obj.lista_id,
                            ordem: obj.ordem
                        };
                    });
                    axios.put(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${LISTA.id}/tarefas`, {
                        "evento": "moveu",
                        "novaLista": novaLista
                    });
                }
            }
            if (event.removed) {
                let LISTA = _.find(this.arrayListas, { id: this.TAREFA_ANTERIOR.lista_id });
                if (LISTA) {
                    let ordem = 1;
                    LISTA.tarefas.forEach((obj) => {
                        obj.ordem = ordem;
                        obj.lista_id = LISTA.id;
                        ordem++;
                    });
                    let novaLista = LISTA.tarefas.map((obj) => {
                        return {
                            id: obj.id,
                            lista_id: obj.lista_id,
                            ordem: obj.ordem
                        };
                    });
                    axios.put(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${LISTA.id}/tarefas`, {
                        "evento": "remover",
                        "novaLista": novaLista
                    });
                }
            }
            if (event.added) {
                TAREFA = event.added.element;
                this.TAREFA_ANTERIOR = _.cloneDeep(TAREFA);
                let LISTA = null;
                // varrer todas as listas
                this.arrayListas.forEach((lista) => {
                    let novoLugar = _.find(lista.tarefas, { id: TAREFA.id });
                    if (novoLugar) {
                        LISTA = lista;
                        return false;
                    }
                });
                if (LISTA) {
                    let ordem = 1;
                    LISTA.tarefas.forEach((obj) => {
                        obj.ordem = ordem;
                        obj.lista_id = LISTA.id;
                        ordem++;
                    });
                    let novaLista = LISTA.tarefas.map((obj) => {
                        return {
                            id: obj.id,
                            lista_id: obj.lista_id,
                            ordem: obj.ordem
                        };
                    });
                    axios.put(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${LISTA.id}/tarefas`, {
                        "evento": "adicionar",
                        "novaLista": novaLista,
                        "tarefa_id": this.TAREFA_ANTERIOR.id
                    });


                }
            }

        },
        verTarefa(objTarefa, objLista) {
            this.LISTA_ID = objLista.id;
            this.formTarefa.lista_id = this.LISTA.id;
            this.TAREFA_ID = objTarefa.id;
            this.formTarefa.preload = true;
            axios.get(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${this.LISTA.id}/tarefas/${this.TAREFA.id}`)
                .then((response) => {
                    Vue.set(this.TAREFA, "logs", []);
                    Object.assign(this.TAREFA, response.data);
                    this.formTarefa.preload = false;
                })
                .catch((data) => {
                    this.formTarefa.preload = false;
                });

        },
        updateTarefa() {
            if (this.tarefaEditandoTitulo && this.TAREFA.titulo.trim() === "") {
                mostraErro("", "Informa um título para a tarefa");
                return false;
            }
            if (this.tarefaEditandoTitulo || this.tarefaEditandoDescricao) {
                axios.put(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${this.LISTA.id}/tarefas/${this.TAREFA.id}`, {
                    titulo: this.TAREFA.titulo,
                    descricao: this.TAREFA.descricao
                });
            }
            this.tarefaEditandoTitulo = false;
            this.tarefaEditandoDescricao = false;

        },
        updateTarefaLembrete() {
            axios.put(`${URL_ADMIN}/weekly-report/${this.id}/quadros/${this.QUADRO.id}/listas/${this.LISTA.id}/tarefas/${this.TAREFA.id}`, {
                lembrete: this.TAREFA.lembreteText
            });
        },
        updataConcluirTarefa() {
            axios.put(`${this.rotaTarefa}`, {
                concluido: this.TAREFA.concluido
            });
        },
        //membros
        addMembro(usuario) {
            this.autoCompleteNovoMembros = "";
            let encontrar = _.find(this.TAREFA.membros, { id: usuario.id });
            if (encontrar) {
                mostraErro("", "O membro já está na tarefa");
                return false;
            }
            axios.put(`${this.rotaTarefa}/updateMembro`, {
                user_id: usuario.id,
                acao: "add"
            });
        },
        removerMembro(usuario) {
            this.autoCompleteNovoMembros = "";
            axios.put(`${this.rotaTarefa}/updateMembro`, {
                user_id: usuario.id,
                acao: "remove"
            });
        },
        //cronograma
        addDataHoraInicio() {
            if (this.TAREFA.datahora_inicio == null) {
                this.TAREFA.datahora_inicio = moment().hour(8).minute(0).format("L [às] HH:mm");
            }
            axios.put(`${this.rotaTarefa}/updateDataHoraInicio`, {
                tarefa_id: this.TAREFA.id,
                datahora: this.TAREFA.datahora_inicio,
                acao: "add"
            });
        },
        removerDataHoraInicio() {
            this.TAREFA.datahora_inicio = null;
            axios.put(`${this.rotaTarefa}/updateDataHoraInicio`, {
                tarefa_id: this.TAREFA.id,
                acao: "remove"
            });
        },
        addDataHoraEntrega() {
            if (this.TAREFA.datahora_entrega == null) {
                this.TAREFA.datahora_entrega = moment().add(2, "days").format("L [às] HH:mm");
            }
            axios.put(`${this.rotaTarefa}/updateDataHoraEntrega`, {
                tarefa_id: this.TAREFA.id,
                datahora: this.TAREFA.datahora_entrega,
                acao: "add",
                lembrete: this.TAREFA.lembreteText
            });
        },
        removerDataHoraEntrega() {
            this.TAREFA.datahora_entrega = null;
            axios.put(`${this.rotaTarefa}/updateDataHoraEntrega`, {
                tarefa_id: this.TAREFA.id,
                acao: "remove"
            });
        },
        //checklist
        addCheckList() {
            let nomeChecklist = this.$refs.campoTituloCheckList.value;
            if (nomeChecklist === "") {
                mostraErro("", "Escolha um título para a checklist");
                return false;
            }
            this.$refs.campoTituloCheckList.value = "Checklist";
            axios.post(`${this.rotaTarefa}/checklist`, {
                titulo: nomeChecklist
            }).then((response) => {
                this.abriFormNovoItem({ id: response.data.id });
            });
        },
        editarTituloChecklist(objChecklist) {
            //if (!this.CHECKLIST) {
            this.CHECKLIST_ID = objChecklist.id;
            this.checklistIdEditando = this.CHECKLIST.id;
            //}
        },
        updateChecklist() {
            if (this.CHECKLIST) {
                if (this.CHECKLIST.titulo.trim() === "") {
                    mostraErro("", "Escolha um título para a checklist");
                    return false;
                }
                let titulo = this.CHECKLIST.titulo;
                axios.put(`${this.rotaChecklist}`, {
                    titulo: titulo
                });
                this.checklistIdEditando = null;
                this.CHECKLIST_ID = null;
            }
        },
        removerChecklist(objChecklist) {
            if (!this.CHECKLIST) {
                this.CHECKLIST_ID = objChecklist.id;
            }
            axios.delete(`${this.rotaChecklist}`);
        },
        moveuCheckList(event) {
            let ordem = 1;
            this.TAREFA.checklists.forEach((obj) => {
                obj.ordem = ordem;
                ordem++;
            });
            let novaLista = this.TAREFA.checklists.map((obj) => {
                return {
                    id: obj.id,
                    ordem: obj.ordem
                };
            });
            axios.put(`${this.rotaTarefa}/atualizarOrdemCheckList`, { novaLista });
        },
        //Itens do checklist
        infoProgresso(objChecklist) {
            if (objChecklist.itens.length > 0) {
                let progresso = objChecklist.itens.filter(item => item.concluido).length;
                let total = objChecklist.itens.length;
                return {
                    total,
                    progresso,
                    pct: pctDoValor(progresso, total)
                };
            }
            return {
                total: 0,
                progresso: 0,
                pct: 0
            };

        },
        abriFormNovoItem(objChecklist) {
            this.CHECKLIST_ID = objChecklist.id;
            this.ITEM_ID = null;
            this.formItem.checklist_id = this.CHECKLIST.id;
            setTimeout(() => {
                this.$refs[`formNovoItemChecklist${this.CHECKLIST.id}`][0].focus();
            }, 100);
        },
        sairFormNovoItem() {

            setTimeout(() => {
                this.formItem.checklist_id = null;
            }, 150);

        },
        addNovoItemChecklist() {
            if (this.formItem.checklist_id != null) {
                if (this.formItem.titulo === "") {
                    this.CHECKLIST_ID = null;
                    return false;
                }
                axios.post(`${this.rotaChecklist}/item`, {
                    titulo: this.formItem.titulo
                });
                this.formItem.titulo = "";

            }

        },
        abriFormItem(objItem) {
            //this.formItem.checklist_id=null;
            this.CHECKLIST_ID = objItem.checklist_id;
            this.ITEM_ID = objItem.id;
            this.checklistItemIdEditando = objItem.id;

            this.formItem.titulo = this.ITEM.titulo;
            setTimeout(() => {
                this.$refs[`campoInputItem${this.ITEM_ID}`][0].focus();
            }, 100);

        },
        updateItem() {
            if (this.checklistItemIdEditando) {
                if (this.ITEM.titulo.trim() === "") {
                    this.ITEM.titulo = this.formItem.titulo;
                    this.formItem.titulo = "";
                    this.checklistItemIdEditando = null;
                    this.CHECKLIST_ID = null;
                    this.ITEM_ID = null;

                    return false;
                }
                let titulo = this.ITEM.titulo;
                axios.put(`${this.rotaChecklistItem}`, {
                    titulo: titulo
                });
                this.formItem.titulo = "";
                this.checklistItemIdEditando = null;
                this.CHECKLIST_ID = null;
                this.ITEM_ID = null;

            }
        },
        updateItemConcluir(objItem) {
            this.CHECKLIST_ID = objItem.checklist_id;
            this.ITEM_ID = objItem.id;
            axios.put(`${this.rotaChecklistItem}`, {
                concluido: objItem.concluido
            });
            this.CHECKLIST_ID = null;
            this.ITEM_ID = null;
        },
        removerItemChecklist(objItem) {
            //this.formItem.checklist_id=null;
            this.CHECKLIST_ID = objItem.checklist_id;
            this.ITEM_ID = objItem.id;
            axios.delete(`${this.rotaChecklistItem}`);
            this.ITEM_ID = null;
            this.CHECKLIST_ID = null;
        },
        moveuItem(event) {
            let ITEM = null;
            if (event.moved) {
                ITEM = event.moved.element;
                this.CHECKLIST_ID = ITEM.checklist_id;
                let ordem = 1;
                this.CHECKLIST.itens.forEach((obj) => {
                    obj.ordem = ordem;
                    obj.checklist_id = this.CHECKLIST.id;
                    ordem++;
                });
                let novaLista = this.CHECKLIST.itens.map((obj) => {
                    return {
                        id: obj.id,
                        checklist_id: obj.checklist_id,
                        ordem: obj.ordem
                    };
                });
                axios.put(`${this.rotaChecklist}/atualizarOrdemItens`, {
                    "evento": "moveu",
                    "novaLista": novaLista
                });
                this.CHECKLIST_ID = null;
            }
            if (event.added) {
                ITEM = event.added.element;
                this.ITEM_ANTERIOR = _.cloneDeep(ITEM);

                let novaChecklist = null;
                // varrer todas as checklist
                this.TAREFA.checklists.forEach((ck) => {
                    let checkListFind = _.find(ck.itens, { id: ITEM.id });
                    if (checkListFind) {
                        novaChecklist = ck;
                        return false;
                    }
                });
                if (novaChecklist) {
                    let ordem = 1;
                    novaChecklist.itens.forEach((obj) => {
                        obj.ordem = ordem;
                        obj.checklist_id = novaChecklist.id;
                        ordem++;
                    });
                    let lista = novaChecklist.itens.map((obj) => {
                        return {
                            id: obj.id,
                            checklist_id: obj.checklist_id,
                            ordem: obj.ordem
                        };
                    });
                    axios.put(`${this.rotaTarefa}/checklist/${novaChecklist.id}/atualizarOrdemItens/`, {
                        "evento": "adicionar",
                        "novaChecklist": lista
                    });
                }
            }
            if (event.removed) {
                // varrer todas as checklist
                let antigaChecklist = _.find(this.TAREFA.checklists, { id: this.ITEM_ANTERIOR.checklist_id });
                if (antigaChecklist) {
                    let ordem = 1;
                    antigaChecklist.itens.forEach((obj) => {
                        obj.ordem = ordem;
                        obj.checklist_id = antigaChecklist.id;
                        ordem++;
                    });
                    let lista = antigaChecklist.itens.map((obj) => {
                        return {
                            id: obj.id,
                            lista_id: obj.lista_id,
                            ordem: obj.ordem
                        };
                    });
                    axios.put(`${this.rotaTarefa}/checklist/${antigaChecklist.id}/atualizarOrdemItens/`, {
                        "evento": "remover",
                        "antigaChecklist": lista
                    });


                }
            }
        },
        //Anexos
        abrirFormEditarTituloAnexo(objAnexo) {
            this.tituloEditarAnexo = objAnexo.nome;
            setTimeout(() => {
                this.$refs[`campoTituloAnexo${objAnexo.id}`][0].focus();
            }, 100);
        },
        updateAnexo(objAnexo) {
            if (this.tituloEditarAnexo.trim() === "") {
                mostraErro("", "O anexo precisa de um nome");
                return false;
            }
            objAnexo.nome = this.tituloEditarAnexo;
            axios.put(`${this.rotaTarefa}/anexo/${objAnexo.id}`, {
                nome: objAnexo.nome
            });

        },
        deleteAnexo(objAnexo) {

            axios.delete(`${this.rotaTarefa}/anexo/${objAnexo.file}`);
            let indexDelete = _.findIndex(this.TAREFA.anexos, { id: objAnexo.id });
            this.TAREFA.anexos.splice(indexDelete, 1);
        }


    }
};
</script>

<style>
/* light stylings for the kanban columns */
.placeholder {
    opacity: 0.7;
}

.textoRiscado {
    text-decoration: line-through;
}

.corpoListaTarefa {
    background-color: #ebecf0;
}

.tarefa {
    cursor: pointer;
}

textarea[autoresize] {
    display: block;
    overflow: hidden;
    resize: none;
}
</style>
