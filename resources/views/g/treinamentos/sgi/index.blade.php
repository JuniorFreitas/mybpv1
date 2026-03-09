@extends('layouts.sistema')
@section('title', 'Treinamentos SGI')
@section('content_header')
    <h4 class="text-default">Treinamento - Evento</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

    <modal ref="janelaTreinamento" id="janelaTreinamento" titulo="Treinamentos" :size="95">
        <template #conteudo>
            <div class="alert alert-success text-center" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i> Treinamento atualizado com sucesso</h4>
            </div>
            <p class=" mt-2 text-center" v-if="preload">
                <i class="fa fa-spinner fa-pulse"></i> Aguarde ...
            </p>
            <div v-if="!preload && (!cadastrado && !atualizado)">
                <fieldset>
                    <legend>EVENTO</legend>
                    <div class="row">

                        <div class="col-md-6">
                            <label>Treinamento</label>
                            <select class="form-control" v-model="form.treinamento_sgi_id"
                                    onchange="valida_campo_vazio(this,1)"
                                    onblur="valida_campo_vazio(this,1)">
                                <option value="">Selecione ...</option>
                                <option v-for="item in listaTreinamentos" :value="item.id">@{{ item.nome }}</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Local treinamento</label>
                            <select class="form-control" v-model="form.empresa_treinamento_id"
                                    onchange="valida_campo_vazio(this,1)"
                                    onblur="valida_campo_vazio(this,1)">
                                <option value="">Selecione ...</option>
                                <option v-for="item in listaEmpresaTreinamentos" :value="item.id">@{{ item.nome }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 mt-3" v-if="treinamentoSelecionado">
                            <fieldset>
                                <legend>DETALHES</legend>
                                <strong>Conteúdo programatico:</strong> <br>
                                @{{ treinamentoSelecionado.conteudo_programatico || '' }}
                                <hr>

                                <strong>Carga horária:</strong> @{{
                                treinamentoSelecionado.carga_horaria || '' }} horas
                                <hr>
                            </fieldset>
                        </div>

                        <div class="col-md-6">
                            <label for="">Data e hora ínicio</label>
                            <datepicker v-model="form.data_inicio" :hora="true"
                                        onblur="valida_data_vazio(this)"></datepicker>
                        </div>
                        <div class="col-md-6">
                            <label for="">Data e hora termino</label>
                            <datepicker v-model="form.data_fim" :hora="true"
                                        onblur="valida_data_vazio(this)"></datepicker>
                        </div>


                    </div>

                </fieldset>

                <fieldset>
                    <legend>Instrutor(es)</legend>

                    <button class="btn btn-primary mb-3" @click="addLIInstrutor"><i class="fa fa-plus"></i> Adicionar
                        instrutor
                    </button>

                    <div class="row mb-1" v-if="form.instrutores_evento.length > 0"
                         v-for="(obj, index) in form.instrutores_evento" :key="index">
                        <div class="col-md-6">
                            <label>Instrutor</label>
                            <select class="form-control" v-model="obj.instrutor_id"
                                    onchange="valida_campo_vazio(this,1)"
                                    onblur="valida_campo_vazio(this,1)">
                                <option value="">Selecione ...</option>
                                <option v-for="item in listaInstrutores" :value="item.id">@{{ item.nome }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 mt-3" v-show="obj.novo">
                            <button class="btn btn-danger" @click="removerLIInstrutor(index)"><i
                                    class="fa fa-times"></i> Remover
                            </button>
                        </div>
                        <div class="col-12">
                            <hr>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Frequência</legend>

                    <button class="btn btn-primary mb-3" @click="addLIPessoa">
                        <i class="fa fa-plus"></i> Adicionar pessoa
                    </button>

                    <fieldset class=" mb-2" v-if="form.pessoas_evento.length > 0"
                              v-for="(obj, index) in form.pessoas_evento" :key="index">
                        <legend>#@{{ index + 1 }}</legend>
                        <div class="row">

                            <div class="col-md-4">
                                <label for="">CPF</label>
                                <input type="text" class="form-control" @blur="buscaCPF(obj.cpf,index)"
                                       v-model="obj.cpf" onblur="valida_cpf(this)" v-mascara:cpf>
                            </div>

                            <div class="col-md-4">
                                <label for="">Nome</label>
                                <input type="text" placeholder="Informe o nome" v-model="obj.nome" class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>

                            <div class="col-md-4">
                                <label for="">E-mail</label>
                                <input type="text" class="form-control" v-model="obj.email" onblur="validaEmail(this)">
                            </div>

                            <div class="col-md-4">
                                <label for="">Telefone</label>
                                <input type="text" class="form-control" v-model="obj.telefone" v-mascara:telefone>
                            </div>

                            <div class="col-md-4">
                                <label>Empresa</label>
                                <select class="form-control" v-model="obj.cliente_id"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione ...</option>
                                    <option v-for="item in listaClientes" :value="item.id">@{{ item.cpf ? item.nome :
                                        item.razao_social }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Nota</label>
                                <select class="form-control" v-model="obj.nota">
                                    <option value="">Selecione ...</option>
                                    <option v-for="nota in 10" :value="nota">
                                        @{{ nota }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 mt-3" v-show="obj.novo">
                                <button class="btn btn-danger" @click="removerLIPessoa(index)"><i
                                        class="fa fa-times"></i> Remover
                                </button>

                                <button class="btn btn-primary mt" @click="addLIPessoa" v-show="index >=1">
                                    <i class="fa fa-plus"></i> Adicionar pessoa
                                </button>
                            </div>
                        </div>
                    </fieldset>

                </fieldset>


                <fieldset>
                    <legend>Anexo lista de frequência</legend>
                    <upload :model="form.anexos"
                            :model-delete="form.anexosDel"
                            :url="url_anexo"
                            label="Selecionar"
                            @onProgresso="anexoUploadAndamento=true"
                            @onFinalizado="anexoUploadAndamento=false"></upload>
                </fieldset>

            </div>
        </template>
        <template #rodape>
            <button class="btn btn-default" @click.prevent="salvar"><i class="fa fa-save"></i> Salvar</button>
        </template>
    </modal>

    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <div class="row">
            {{--            <div class="col-12 col-sm-6 col-md-6 col-lg-3">--}}
            {{--                <div class="form-group">--}}
            {{--                    <div class="input-group">--}}
            {{--                        <span class="input-group-prepend">--}}
            {{--                            <span class="input-group-text">Buscar</span>--}}
            {{--                        </span>--}}
            {{--                        <input type="text"--}}
            {{--                               placeholder="Buscar por nome"--}}
            {{--                               autocomplete="off"--}}
            {{--                               class="form-control" :disabled="controle.carregando" v-model="controle.dados.campoBusca">--}}

            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            </div>--}}

            <div class="col-12 col-md-2">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-prepend">
                            <span class="input-group-text">Exibir</span>
                        </span>
                        <select class="custom-select" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.pages">
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="500">500</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-success mr-1" :disabled="controle.carregando"
                        :style="controle.carregando ? 'cursor: not-allowed' : 'cursor: pointer'" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button type="button" class="btn btn-primary mr-1" :disabled="controle.carregando"
                        @click.prevent="formCadastrar"
                        @click="$refs.janelaTreinamento?.abrirModal()">
                    <i class="fa fa-plus"></i> Novo Evento
                </button>

                {{-- <form target="_blank" action="{{ route('g.treinamentos.carteiraPdf') }}" method="post">
                     @csrf
                     <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                     <button type="submit" class="btn btn-primary mr-1"
                             :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                             :disabled="selecionados.length === 0">
                         Gerar Carteira <span class="badge badge-light">@{{ selecionados.length }}</span>
                     </button>
                 </form>

                 <button class="btn btn-danger"
                         :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                         :disabled="selecionados.length === 0" @click="selecionados = []">
                     <i class="fa fa-times"></i> Limpar seleção
                 </button>

                 <form target="_blank"
                       action="{{ \App\Models\Sistema::UrlServidor }}/carteira-etiqueta/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS"
                       --}}{{--                      action="{{ route('carteira.excel') }}"--}}{{--
                       method="get">
                     @csrf
                     <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                     <input type="hidden" name="campo_dataInicio" :value="controle.dados.campo_dataInicio">
                     <input type="hidden" name="campo_dataFim" :value="controle.dados.campo_dataFim">

                     <button type="submit" class="btn btn-primary ml-1"
                             :disabled="controle.carregando || (!controle.carregando && lista.length===0 && selecionados.length === 0) ">
                         <i class="fas fa-file-excel"></i> Exportar Excel <span class="badge badge-light"
                                                                                v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                     </button>
                 </form>--}}
            </div>
        </div>

    </fieldset>

    <div id="conteudo">

        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>
        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="table table-bordered table-hover table-condensed" style="font-size: 0.85em;">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">ID</th>
                    <th class="text-center">Evento</th>
                    <th class="text-center">Local</th>
                    <th class="text-center">Data Realização</th>
                    <th class="text-center">Qnt de Pessoas</th>
                    <th class="text-center">Qnt de Instrutores</th>
                    <th class="text-center">Anexo</th>
                    <th class="text-center"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="evento in lista">
                    <td class="text-center">
                        @{{evento.id}}
                    </td>
                    <td class="text-center">
                        @{{evento.treinamento_sgi.nome}}
                    </td>
                    <td class="text-center">
                        @{{evento.empresa_treinamento.nome}}
                    </td>
                    <td class="text-center">
                        @{{evento.data_inicio}} - @{{evento.data_fim}}
                    </td>
                    <td class="text-center">
                        @{{evento.qnt_pessoas}}
                    </td>
                    <td class="text-center">
                        @{{evento.qnt_instrutores}}
                    </td>
                    <td class="text-center">
                        @{{evento.anexos.length > 0 ? 'SIM' : 'NÃO'}}
                    </td>

                    {{--                    <td class="text-center">--}}
                    {{--                        <a :href="`clientes/${cliente.id}/pdf`"--}}
                    {{--                           class="btn btn-default" title="Ficha"--}}
                    {{--                           target="_blank">--}}
                    {{--                            <i class="fa fa-file-pdf"></i>--}}
                    {{--                        </a>--}}
                    {{--                    </td>--}}
                    <td class="text-center">
                        <a href="javascript://" class="btn btn-default" title="Editar"
                           @click.prevent="formAlterar(evento.id)"
                           @click="$refs.janelaTreinamento?.abrirModal()">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                        <a :href="`treinamento/${evento.id}/listapresenca`"
                           class="btn btn-default" title="Lista PDF"
                           target="_blank">
                            <i class="fa fa-file-pdf"></i>
                        </a>
                    </td>
                    {{--                    <td class="text-center">--}}
                    {{--                        <a href="javascript://" class="btn btn-danger" title="Excluir"--}}
                    {{--                           @click.prevent="janelaConfirmar(cliente.id)"--}}
                    {{--                           data-toggle="modal"--}}
                    {{--                           data-target="#janelaConfirmar">--}}
                    {{--                            <i class="fa fa-trash" aria-hidden="true"></i>--}}
                    {{--                        </a>--}}
                    {{--                    </td>--}}
                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.treinamento_sgi.treinamento_sgi.atualizar',auth()->user()->cliente_id)}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/treinamentos/sgi/app.js')}}"></script>
@endpush
