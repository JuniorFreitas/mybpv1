@extends('layouts.sistema')
@section('title', 'VAGAS ABERTAS')
@section('content_header')
    <h4 class="text-default">VAGAS ABERTAS</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template slot="conteudo">
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Vaga cadastrada com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Vaga alterada com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <div class="form-group">
                    <label for="">Informe a Vaga</label>
                    <autocomplete :caminho="vagas_ativas"
                                  :valido="form.vaga_id !== ''"
                                  v-model="form.autocomplete_label_vaga_modal"
                                  placeholder="Selecione uma vaga"
                                  :disabled="editando"
                                  :id="hash"
                                  @onblur="resetaCampoVagaModal"
                                 @onselect="selecionaVagaModal"></autocomplete>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <editor :api-key='tinyPadrao.key' v-model="form.descricao" :init="tinyPadrao"></editor>
                </div>

                <div class="form-group">
                    <label for="Cidade">Cidade</label>
                    <autocomplete :caminho="todos_municipios"
                                  :valido="form.municipio_id !== ''"
                                  v-model="form.autocomplete_label_municipio_modal"
                                  placeholder="Selecione um municipio"
                                  :id="`mun_${hash}`"
                                  @onblur="resetaCampoMunicipioModal"
                                 @onselect="selecionaMunicipioModal"></autocomplete>
                </div>

                <fieldset>
                    <legend>Provas</legend>

                    <button class="btn btn-sm btn-primary mb-3" @click="addLISimulado">
                        <i class="fa fa-plus"></i> Adicionar
                    </button>

                    <fieldset class=" mb-2" v-if="form.simulados.length > 0"
                              v-for="(obj, index) in form.simulados" :key="index">
                        <legend>#@{{ index + 1 }}</legend>
                        <div class="row">

                            <div class="col-md-4">
                                <label>Prova</label>

                                <select class="form-control" v-model="obj.simulado_id"
                                        onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">
                                    <option value="">Selecione...</option>
                                    <option v-for="item in listaSimulados" :value="item.id">
                                        @{{ item.titulo }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Duração em minutos</label>
                                <input type="number" min="15" placeholder="duração da prova" v-model="obj.duracao" v-mascara:numero
                                       class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>

                            <div class="col-md-2">
                                <label>Online</label>
                                <select class="form-control" v-model="obj.online"
                                        onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label>Ativo</label>
                                <select class="form-control" v-model="obj.ativo"
                                        onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <div class="col-12" v-if="obj.simulado_id">
{{--                                <p>Link da prova: <span v-text="`${URL_SITE}/provas/${obj.vagas_abertas_id}/${obj.simulado_id}/${listaSimulados.find(el => el.id === obj.simulado_id).titulo}`"></span></p>--}}
                            </div>

                            <div class="col-12 mt-3">
                                <button class="btn btn-sm btn-danger" @click="removerLISimulado(index)"><i
                                        class="fa fa-times"></i> Remover
                                </button>

                                <button class="btn btn-sm btn-primary mt" @click="addLISimulado" v-show="index >=1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>

                </fieldset>

                <div class="form-group">
                    <div class="switchToggle">
                        <input type="checkbox" v-model="form.ativo" id="switch">
                        <label for="switch">Ativo</label>
                    </div>

            </form>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-primary" v-show="editando && !atualizado && !preloadAjax"
                    @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !cadastrado && !preloadAjax"
                    @click="cadastrar()">
                Cadastrar
            </button>
        </template>
    </modal>

    <fieldset>
        <legend>Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-prepend">
                            <span class="input-group-text">Buscar</span>
                        </span>
                        <input type="text"
                               placeholder="Buscar por nome"
                               autocomplete="off"
                               class="form-control" :disabled="controle.carregando" v-model="controle.dados.campoBusca">
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-prepend">
                            <span class="input-group-text">Status</span>
                        </span>
                        <select class="custom-select" v-model="controle.dados.campoStatus" @change="atualizar()">
                            <option value="">Todos os Status</option>
                            <option :value="true">Apenas Ativos</option>
                            <option :value="false">Apenas Inativos</option>
                        </select>

                    </div>
                </div>
            </div>

            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" :disabled="controle.carregando"
                        data-target="#janelaCadastrar"
                        @click="formNovo()">
                    Cadastrar
                </button>
            </div>
        </form>
    </fieldset>

    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">ID</th>
                    <th>Vaga</th>
                    <th>Descrição</th>
                    <th>Local</th>
                    <th>Ativo</th>
                    <th>Ação</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="vaga in lista">
                    <td class="text-center">
                        @{{vaga.id}}
                    </td>

                    <td>
                        @{{vaga.vaga.nome}}
                    </td>


                    <td>
                        <span v-html="vaga.descricao.substring(0,300)"></span>
                    </td>

                    <td>
                        @{{vaga.municipio.nome}} - @{{vaga.municipio.uf}}
                    </td>

                    <td class="text-center">
                        <bt-ativo :rota="`cadastro/vagas-abertas/${vaga.id}/ativa-desativa`" :model="vaga"></bt-ativo>
                    </td>

                    <td class="text-center">
                        <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar"
                           @click.prevent="formAlterar(vaga.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao
            class="d-flex justify-content-center"
            id="controle"
            ref="componente"
            url="{{route('g.vagas.vagas_abertas.atualizar')}}"
            por-pagina="100"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando">
        </controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/vagas_abertas/app.js')}}"></script>
@endpush
