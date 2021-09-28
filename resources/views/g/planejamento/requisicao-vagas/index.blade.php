@extends('layouts.sistema')
@section('title', 'Planejamento - Requisição de Vagas')
@section('content_header','Planejamento - Requisição de Vagas')
@section('content')

    <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template slot="conteudo">
            <preload v-show="preload" class="text-center"></preload>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Solicitação cadastrada com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Solicitação alterada com sucesso!</h4>
            </div>
            <form v-if="!preload && (!cadastrado && !atualizado) " id="form" onsubmit="return false;">
                <fieldset>
                    <legend>Informações</legend>
                    <div class="row">
                        <div class="col-12 col-md-4" v-if="cliente_id === 0">
                            <div class="form-group">
                                <label for="">Selecione um cliente</label>
                                <autocomplete :formsm="false" :caminho="controle.dados.caminho_cliente_autocomplete" :disabled="visualizar"
                                              :valido="form.cliente_id !== ''"
                                              v-model="form.autocomplete_label_cliente_modal"
                                              :id="`cliente_modal_${hash}`"
                                              placeholder="Digite o nome cliente"
                                              @onblur="resetaCampoClienteModal"
                                             @onselect="selecionaClienteModal"></autocomplete>
                            </div>
                        </div>


                        <div class="col-12"></div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Selecione um cargo</label>
                                <autocomplete :formsm="false" :caminho="controle.dados.caminho_autocomplete" :disabled="visualizar"
                                              :valido="form.cargo_id !== ''"
                                              v-model="form.autocomplete_label_cargo_modal"
                                              placeholder="Digite o nome do cargo"
                                              :id="`vaga_modal_${hash}`"
                                              @onblur="resetaCampoVagaModal"
                                             @onselect="selecionaVagaModal"></autocomplete>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Área</label>
                                <select v-model="form.area_id" class="form-control" :disabled="visualizar"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option v-for="item in areas_etiquetas" :value="item.id">@{{ item.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Centro de Custo</label>
                                <select v-model="form.centro_custo_id" class="form-control" :disabled="visualizar"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option v-for="item in centro_custos" :value="item.id">@{{ item.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Tipo de Contratação</label>
                                <select v-model="form.tipo_contratacao" class="form-control" :disabled="visualizar"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option value="APRENDIZ">APRENDIZ</option>
                                    <option value="FIXO">FIXO</option>
                                    <option value="INTERMITENTE">INTERMITENTE</option>
                                    <option value="PJ">PJ</option>
                                    <option value="ESTÁGIO">ESTÁGIO</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Prioridade</label>
                                <select v-model="form.prioridade" class="form-control" :disabled="visualizar"
                                        onchange="valida_campo_vazio(this,1)"
                                        onblur="valida_campo_vazio(this,1)">
                                    <option value="">Selecione</option>
                                    <option value="ALTA">ALTA</option>
                                    <option value="MÉDIA">MÉDIA</option>
                                    <option value="URGENTE">URGENTE</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Quantidade</label>
                                <input type="text" class="form-control"
                                       onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                       v-mascara:numero v-model="form.quantidade">
                            </div>
                        </div>

                        <div class="col-12">
                            <fieldset>
                                <legend>DEMAIS INFORMAÇÕES</legend>
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Posição</label>
                                            <select v-model="form.outras_informacoes.posicao" class="form-control" :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this,1)"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option value="Efetiva">Efetiva</option>
                                                <option value="Estágio">Estágio</option>
                                                <option value="Temporária">Temporária</option>
                                                <option value="Aumento de Quadro">Aumento de Quadro</option>
                                                <option value="Substituição">Substituição</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Processo</label>
                                            <select v-model="form.outras_informacoes.processo" class="form-control" :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this,1)"
                                                    onblur="valida_campo_vazio(this,1)">
                                                <option value="">Selecione</option>
                                                <option value="ALTA">ALTA</option>
                                                <option value="MÉDIA">MÉDIA</option>
                                                <option value="URGENTE">URGENTE</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Contrato</label>
                                            <input type="text" class="form-control" :disabled="visualizar"
                                                   onblur="valida_campo_vazio(this,1)"
                                                   v-model="form.outras_informacoes.contrato">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Local de Trabalho</label>
                                            <input type="text" class="form-control"
                                                   onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                   v-model="form.outras_informacoes.local_trabalho">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Horário</label>
                                            <input type="text" class="form-control"
                                                   onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                   v-model="form.outras_informacoes.horario">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Gestor</label>
                                            <input type="text" class="form-control"
                                                   onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                   v-model="form.outras_informacoes.gestor">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Cargo está no PPRA e PCMSO?</label>
                                            <input type="text" class="form-control"
                                                   onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                                   v-model="form.outras_informacoes.ppra">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Previsão de Ínicio</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="form.imediata" class="custom-control-input" :disabled="visualizar"
                                           id="imediata">
                                    <label class="custom-control-label"
                                           for="imediata">Imediata</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12"></div>
                        <div class="col-12 col-md-4" v-if="!form.imediata">
                            <datepicker label="Previsão" v-model="form.previsao_inicio" :disabled="visualizar"></datepicker>
                        </div>


                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label>Solicitante</label>
                                <input type="text" class="form-control" onblur="valida_campo_vazio(this,1)" :disabled="visualizar"
                                       v-model="form.solicitante">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>Observação</label>
                                <textarea class="form-control" v-model="form.observacao" cols="5" rows="5" :disabled="visualizar"></textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>

            </form>
        </template>
        <template slot="rodape">
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="editando && !atualizado  && !preload"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary"
                        v-show="!editando && !cadastrado  && !preload"
                        @click.prevent="cadastrar">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </div>
        </template>
    </modal>

    <fieldset>
        <legend>Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">
            <div class="col-12 col-md-3">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                           id="filtroIntervalo"
                           v-model="controle.dados.filtroPeriodo">
                    <label class="form-check-label cursor-pointer" for="filtroIntervalo">Por período</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label=""
                                :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                v-model="controle.dados.periodo"></datepicker>
                </div>
            </div>

            {{--            <div class="col-12 col-md-4">--}}
            {{--                <div class="form-group">--}}
            {{--                    <label>Buscar</label>--}}
            {{--                    <input type="text"--}}
            {{--                           placeholder="Buscar por nome"--}}
            {{--                           autocomplete="off"--}}
            {{--                           class="form-control form-control-sm" :disabled="controle.carregando" v-model="controle.dados.campoBusca">--}}
            {{--                </div>--}}
            {{--            </div>--}}

            {{--            <div class="col-12 col-md-4">--}}
            {{--                <div class="form-group">--}}
            {{--                    <label>Status</label>--}}
            {{--                    <select class="form-control form-control-sm" v-model="controle.dados.campoStatus" @change="atualizar()">--}}
            {{--                        <option value="">Todos os Status</option>--}}
            {{--                        <option :value="true">Apenas Ativos</option>--}}
            {{--                        <option :value="false">Apenas Inativos</option>--}}
            {{--                    </select>--}}
            {{--                </div>--}}
            {{--            </div>--}}

            <div class="col-12"></div>

            <div class="col-12 col-md-9">
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                </button>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" :disabled="controle.carregando"
                        data-target="#janelaCadastrar"
                        @click.prevent="formNovo">
                    Solicitar
                </button>
            </div>
        </form>
    </fieldset>

    <preload class="text-center" v-if="controle.carregando"></preload>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th>QNT</th>
                    <th>Cargo</th>
                    <th>Solicitação</th>
                    <th>Solicitante</th>
                    <th v-if="cliente_id === 0">Cliente</th>
                    <th>Área</th>
                    <th>Centro de Custo</th>
                    <th>Contratação</th>
                    <th>Prioridade</th>
                    <th>Ínicio</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in lista">
                    <td>
                        @{{item.quantidade}}
                    </td>

                    <td>
                        @{{item.cargo.nome}}
                    </td>

                    <td>
                        @{{item.data_solicitacao}}
                    </td>

                    <td>
                        @{{item.solicitante}}
                    </td>

                    <td v-if="cliente_id === 0">
                        @{{item.cliente.razao_social}}
                    </td>

                    <td>
                        @{{item.area.label}}
                    </td>

                    <td>
                        @{{item.centro_custo.label}}
                    </td>

                    <td>
                        @{{item.tipo_contratacao}}
                    </td>

                    <td>
                        @{{item.prioridade}}
                    </td>

                    <td>
                        <span v-show="item.imediata">
                             Imediata
                        </span>
                        <span v-show="!item.imediata">
                             @{{ item.previsao_inicio }}
                        </span>
                    </td>


                    <td class="text-center">
                        <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar"
                           @click.prevent="formOpen(item.id); editando = true"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-edit"></i>
                        </a>

                        <a href="javascript://" class="btn btn-sm btn-primary mb-1" title="Editar"
                           @click.prevent="formOpen(item.id); visualizar = true"
                           data-toggle="modal"
                           data-target="#janelaCadastrar">
                            <i class="fa fa-search-plus"></i>
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
            url="{{route('g.requisicao_vagas.atualizar')}}"
            por-pagina="50"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando">
        </controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/planejamento/requisicao-vagas/app.js')}}"></script>
@endpush
