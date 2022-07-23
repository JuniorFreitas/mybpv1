@extends('layouts.sistema')
@section('title', 'CIH')
@section('content_header')
    <h4 class="text-default">CIH</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

    <modal id="janelaRelatorio" :titulo="tituloJanela" :fechar="!preloadAjax" size="g">
        <template slot="conteudo">
            <fieldset>
                <legend>Escolha o período</legend>
                <div class="form-group">
                    <date-picker label="Período" v-model="datarelatorio" :id="`data_relatorio_${hash}`"
                                 :range="true"></date-picker>
                </div>
            </fieldset>

        </template>
        <template slot="rodape">
            <form method="post" target="_blank" v-show="tipoRelatorio === 'pdf'"
                  action="{{ route('g.admissao.cih.relatorioPdf') }}">
                @csrf
                <input type="hidden" name="cliente_relatorio" :value="cliente_relatorio">
                <input type="hidden" name="intervalo" :value="datarelatorio">
                <button class="btn btn-sm btn-primary">Gerar PDF</button>
            </form>

            <form method="post" target="_blank" v-show="tipoRelatorio === 'excel'"
                  action="{{ route('g.admissao.cih.export') }}">
                @csrf
                <input type="hidden" name="cliente_relatorio" :value="cliente_relatorio">
                <input type="hidden" name="intervalo" :value="datarelatorio">
                <button class="btn btn-sm btn-primary">Gerar Excel</button>
            </form>
        </template>
    </modal>

    <modal id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90">
        <template slot="conteudo">
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Ocorrrência cadastrada com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Ocorrrência alterada com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">
                <fieldset>
                    <legend>Lançamento</legend>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Data da Ocorrência</label>
                                <date-picker label="" :disabled="aprovando" v-model="form.data_lancamento"
                                             style="margin-top: -19px"
                                             max="{{ (new \MasterTag\DataHora())->dataCompleta() }}"></date-picker>
                            </div>
                        </div>

                        <div class="col-12"></div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select :disabled="aprovando" v-model="form.tag_id" onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option v-for="item in listaTags" :value="item.id">@{{item.label}}</option>
                                    <option :value="0">Outro</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6" v-if="form.tag_id === 0">
                            <div class="form-group">
                                <label>Especifique</label>
                                <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                       :disabled="aprovando" v-model="form.outra_tag">
                            </div>
                        </div>

                        <div class="col-12"></div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Área</label>
                                <select :disabled="aprovando" v-model="form.area_id" onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option v-for="item in listaAreas" :value="item.id">@{{item.label}}</option>
                                    <option :value="0">Outra</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6" v-if="form.area_id === 0">
                            <div class="form-group">
                                <label>Especifique</label>
                                <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)"
                                       :disabled="aprovando" v-model="form.outra_area">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Para vários colaboradores</label>
                                <select :disabled="aprovando" v-model="form.varios_colaboradores"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)" class="form-control">
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12" v-if="form.varios_colaboradores">
                            <div class="form-group">
                                <label>Informe os colaboradores</label>
                                <textarea class="form-control validacampo" :disabled="aprovando"
                                          v-model="form.colaboradores_avulso"
                                          cols="5" rows="5"></textarea>
                            </div>
                        </div>

                        <div class="col-12" v-if="!form.varios_colaboradores">
                            <div class="form-group">
                                <label>Colaborador</label>
                                <autocomplete :caminho="colaborador_ativo"
                                              :formsm="false"
                                              :valido="form.feedback_id !== ''"
                                              v-model="form.autocomplete_label_colaborador"
                                              placeholder="Selecione um(a) colaborador(a)"
                                              :disabled="aprovando"
                                              :id="`colaborador_${hash}`"
                                              @onblur="resetaCampoColaborador"
                                              @onselect="selecionaColaborador"></autocomplete>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Ação</label>
                                <input type="text" class="form-control" :disabled="aprovando"
                                       onblur="valida_campo_vazio(this,1)" v-model="form.acao">
                            </div>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>Anexo (Evidência)</legend>
                                <upload :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :leitura="form.id ? true : false"
                                        :url="url_anexo"
                                        label="Selecionar"
                                        @onProgresso="anexoUploadAndamento=true"
                                        @onFinalizado="anexoUploadAndamento=false"></upload>
                            </fieldset>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea class="form-control" :disabled="aprovando" v-model="form.obs_lancamento"
                                          cols="5" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset v-if="aprovando">
                    <legend>Aprovação</legend>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea class="form-control" :disabled="form.data_aprovacao"
                                          v-model="form.obs_aprovacao"
                                          cols="5" rows="5"></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select :disabled="form.data_aprovacao" v-model="form.status"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="aprovado">Aprovar</option>
                                    <option value="reprovado">Reprovar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </template>
        <template slot="rodape">
            <div v-if="form.status !== ''">
                <button type="button" class="btn btn-sm btn-primary" v-show="aprovando && !atualizado && !preloadAjax"
                        @click="aprovar">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!aprovando && !cadastrado && !preloadAjax"
                        @click="cadastrar">
                    <i class="fa fa-save"></i> Lançar
                </button>
            </div>
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
                    <select class="form-control form-control-sm" v-model="controle.dados.campoStatus">
                        <option value="">Todos os Status</option>
                        <option value="aprovado">Aprovado</option>
                        <option value="reprovado">Reprovado</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Tipo</label>
                    <select v-model="controle.dados.campoTags" onblur="valida_campo_vazio(this,1)"
                            onchange="valida_campo_vazio(this,1)" class="form-control form-control-sm">
                        <option value="">Selecione...</option>
                        <option v-for="item in listaTags" :value="item.id">@{{item.label}}</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Área</label>
                    <select v-model="controle.dados.campoAreas" onblur="valida_campo_vazio(this,1)"
                            onchange="valida_campo_vazio(this,1)" class="form-control form-control-sm">
                        <option value="">Selecione...</option>
                        <option v-for="item in listaAreas" :value="item.id">@{{item.label}}</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>Atualizar
                </button>

                @can('admissao_cih_lancar')
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                            :disabled="controle.carregando"
                            data-target="#janelaCadastrar"
                            @click="formNovo()">
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>
                @endcan
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" :disabled="controle.carregando"
                        data-target="#janelaRelatorio"
                        @click="tituloJanela = `Gerar Relatório em PDF`; tipoRelatorio = 'pdf'">
                    <i class="fa fa-files-pdf"></i> Gerar PDF
                </button>
                <button type="button" class="btn btn-sm btn-primary mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0 && selecionados.length === 0) ">
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL <span class="badge badge-light"
                                                                           v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                </button>
            </div>
        </form>
    </fieldset>

    <div class="col-12 mb-2 mt-2 pt-1 pb-1 border-bottom">
        <p>
            Legenda:
            <i class="fas fa-circle text-warning"></i> Aberto
            <i class="fas fa-circle text-success ml-2"></i> Aprovado
            <i class="fas fa-circle text-danger ml-2"></i> Reprovado
        </p>
    </div>

    <p class="text-center" v-if="controle.carregando">
        <i class="fa fa-spinner fa-pulse"></i> Carregando...
    </p>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>


        <div v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">ID</th>
                    <th>Colaborador</th>
                    <th>Tipo</th>
                    <th class="text-center">Data Ocorrência</th>
                    <th class="text-center">Lançamento</th>
                    <th class="text-center">Aprovação</th>
                    <th class="text-center">Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in lista" :class="{
                    'table-warning': item.status.includes('aberto'),
                    'table-danger': item.status.includes('reprovado'),
                    'table-success': item.status.includes('aprovado'),
                }">
                    <td class="text-center">
                        @{{item.id}}
                    </td>
                    <td>
                        @{{item.varios_colaboradores ? 'Varios colaboradores' :item.colaborador.curriculo.nome}}
                    </td>

                    <td class="text-center">
                        @{{item.tag ? item.tag.label : item.outra_tag}}
                    </td>

                    <td class="text-center">
                        @{{item.data_lancamento}}
                    </td>

                    <td class="text-center">
                        Lançado por @{{item.responsavel_lancamento.nome}} <br>
                        em @{{item.created_at}}
                    </td>
                    <td class="text-center">
                        <span v-if="item.status.includes('aprovado') || item.status.includes('reprovado')">
                            @{{ item.status | capitalize() }} por @{{item.responsavel_aprovacao.nome}} <br>
                            em @{{item.updated_at}}
                        </span>
                    </td>

                    <td class="text-center">
                        @{{item.status | capitalize() }}
                    </td>

                    <td class="text-center">
                        @can('admissao_cih_aprovar')
                            <a v-show="item.status.includes('aberto')" href="javascript://"
                               class="btn btn-sm btn-primary"
                               content="Visualizar"
                               v-tippy
                               @click.prevent="formAprovar(item.id)"
                               data-toggle="modal"
                               data-target="#janelaCadastrar">
                                <i class="fa fa-check"></i>
                            </a>
                        @endcan

                        <a v-show="item.status.includes('aprovado') || item.status.includes('reprovado')"
                           href="javascript://"
                           class="btn btn-sm btn-primary"
                           content="Visualizar"
                           v-tippy
                           @click.prevent="formAprovar(item.id)"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-search"></i>
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
            url="{{route('g.admissao.cih.atualizar')}}"
            por-pagina="50"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando">
        </controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/admissao/apontamento/cih/app.js')}}"></script>
@endpush
