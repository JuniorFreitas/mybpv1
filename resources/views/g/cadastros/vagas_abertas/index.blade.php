@extends('layouts.sistema')
@section('title', 'VAGAS ABERTAS')
@section('content_header')
    <h4 class="text-default">VAGAS ABERTAS</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template #conteudo>
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Vaga cadastrada com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Vaga alterada com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <div class="form-group">
                    <label for="">Informe o cargo</label>
                    <autocomplete :caminho="cargos_ativos"
                                  :valido="form.vaga_id !== ''"
                                  v-model="form.autocomplete_label_vaga_modal"
                                  placeholder="Selecione um cargo"
                                  :disabled="editando"
                                  :id="hash"
                                  @onblur="resetaCampoVagaModal"
                                  @onselect="selecionaVagaModal"></autocomplete>
                </div>

                <div class="form-group">
                    <label for="descricao">Titulo</label>
                    <input class="form-control" v-model="form.titulo" onblur="valida_campo_vazio(this,1)"
                           placeholder="Informe o título da vaga"
                    >
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

{{--                <fieldset>--}}
{{--                    <legend>Projetos</legend>--}}

{{--                    <button class="btn btn-sm mr-1 btn-primary mb-3" @click="addLIProjeto">--}}
{{--                        <i class="fa fa-plus"></i> Adicionar--}}
{{--                    </button>--}}

{{--                    <fieldset class=" mb-2" v-if="form.projetos.length > 0"--}}
{{--                              v-for="(obj, index) in form.projetos" :key="index">--}}
{{--                        <legend>#@{{ index + 1 }}</legend>--}}
{{--                        <div class="row">--}}

{{--                            <div class="col-md-6">--}}
{{--                                <label>Projeto</label>--}}

{{--                                <select class="form-control" v-model="obj.projeto_id"--}}
{{--                                        @change="selecionaProjeto(obj.projeto_id, index)"--}}
{{--                                        onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">--}}
{{--                                    <option value="">Selecione...</option>--}}
{{--                                    <option v-for="item in listaProjetos" :value="item.id">--}}
{{--                                        @{{ item.nome }}--}}
{{--                                    </option>--}}
{{--                                </select>--}}
{{--                            </div>--}}

{{--                            <div class="col-md-2" v-show="obj.projeto_id > 0 && !editando">--}}
{{--                                <label>Vagas disponíveis</label>--}}

{{--                                <input type="number" disabled v-model="obj.qnt_disponivel"--}}
{{--                                       v-mascara:numero--}}
{{--                                       class="form-control"--}}
{{--                                       onblur="valida_campo_vazio(this,1)">--}}
{{--                            </div>--}}

{{--                            <div class="col-md-4">--}}
{{--                                  <label>Quantidade total de vagas</label>--}}
{{--                                <input type="number" placeholder="Quantidade total de vagas para este projeto"--}}
{{--                                       @change="verificaQuantidadeVagas(obj.qnt_disponivel,obj.qnt_total,obj.projeto_id)"--}}
{{--                                       v-model="obj.qnt_total"--}}
{{--                                       v-mascara:numero--}}
{{--                                       class="form-control"--}}
{{--                                       onblur="valida_campo_vazio(this,1)">--}}
{{--                            </div>--}}

{{--                            <div class="col-12 mt-3">--}}
{{--                                <button class="btn btn-sm mr-1 btn-danger" @click="removerLIProjeto(index)"><i--}}
{{--                                        class="fa fa-times"></i> Remover--}}
{{--                                </button>--}}

{{--                                <button class="btn btn-sm mr-1 btn-primary mt" @click="addLIProjeto" v-show="index >=1">--}}
{{--                                    <i class="fa fa-plus"></i> Adicionar--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </fieldset>--}}
{{--                </fieldset>--}}

                <fieldset>
                    <legend>Provas</legend>

                    <button class="btn btn-sm mr-1 btn-primary mb-3" @click="addLISimulado">
                        <i class="fa fa-plus"></i> Adicionar
                    </button>

                    <fieldset class=" mb-2" v-if="form.simulados.length > 0"
                              v-for="(obj, index) in form.simulados" :key="index">
                        <legend>#@{{ index + 1 }}</legend>
                        <div class="row">

                            <div class="col-md-4">
                                <label>Prova</label>

                                <select class="form-control" v-model="obj.simulado_id"
                                        @change="selecionaSimulado(obj.simulado_id, index)"
                                        onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">
                                    <option value="">Selecione...</option>
                                    <option v-for="item in listaSimulados" :value="item.id">
                                        @{{ item.titulo }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3" v-if="obj.tipo_prova === 'objetiva'">
                                <label>Duração em minutos</label>
                                <input type="number" min="15" placeholder="duração da prova" v-model="obj.duracao"
                                       v-mascara:numero
                                       class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>

                            <div class="col-md-2" v-if="obj.tipo_prova === 'objetiva'">
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

                            <div class="col-md-4 mt-4" v-if="obj.tipo_prova === 'subjetiva'">
                                <label>Imprimir Prova:</label>
                                <button class="btn btn-sm mr-1 btn-primary" @click="imprimeProva(obj.simulado_id, form.id)">
                                    <i class="fa fa-files-pdf"></i> Gerar PDF
                                </button>
                            </div>

                            <div class="col-12 mt-3">
                                <button class="btn btn-sm mr-1 btn-danger" @click="removerLISimulado(index)"><i
                                        class="fa fa-times"></i> Remover
                                </button>

                                <button class="btn btn-sm mr-1 btn-primary mt" @click="addLISimulado" v-show="index >=1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>

                </fieldset>

                <div class="col-md-6">
                    <label>Ativo Site</label>
                    <select class="form-control" v-model="form.ativo"
                            onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Ativo Sistema</label>
                    <select class="form-control" v-model="form.ativo_sistema"
                            onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </form>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado && !preloadAjax"
                    @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !cadastrado && !preloadAjax"
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
                    <label>Buscar</label>
                    <input type="text"
                           placeholder="Buscar por nome"
                           autocomplete="off"
                           class="form-control form-control-sm" :disabled="controle.carregando"
                           v-model="controle.dados.campoBusca">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control form-control-sm" v-model="controle.dados.campoStatus"
                            @change="atualizar()">
                        <option value="">Todos os Status</option>
                        <option :value="true">Apenas Ativos</option>
                        <option :value="false">Apenas Inativos</option>
                    </select>

                </div>
            </div>

            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm mr-1 btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>Atualizar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" data-toggle="modal" :disabled="controle.carregando"
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
                    <th>Título</th>
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
                        @{{vaga.vaga.nome}} <br>
                        <small style="word-break: break-all">@{{ urlVaga }}/@{{ vaga.slug }}</small>
                    <td>
                        @{{vaga.titulo}}
                    </td>

                    <td>
                        <span v-html="vaga.descricao.substring(0,300)" v-if="vaga.descricao"></span>
                    </td>

                    <td>
                        @{{vaga.municipio.nome}} - @{{vaga.municipio.uf}}
                    </td>


                    <td class="text-center">
                        <bt-ativo :rota="`cadastro/vagas-abertas/${vaga.id}/ativa-desativa`" :model="vaga"></bt-ativo>
                    </td>

                    <td class="text-center">
                        <a href="javascript://" class="btn btn-sm mr-1 btn-primary mb-1" title="Editar"
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
