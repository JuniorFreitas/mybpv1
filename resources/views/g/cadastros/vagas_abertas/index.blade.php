@extends('layouts.sistema')
@section('title', 'VAGAS ABERTAS')
@section('content_header')
    <h4 class="text-default">VAGAS ABERTAS</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
@push('css')
    <style>
        /* <details> no modal: esconde marcador nativo e mantém layout alinhado ao restante do formulário */
        #janelaCadastrar .treinamentos-cargo-details > summary::-webkit-details-marker {
            display: none;
        }

        #janelaCadastrar .treinamentos-cargo-details > summary {
            list-style: none;
        }

        #janelaCadastrar .treinamentos-cargo-details > summary .treinamentos-cargo-chevron {
            transition: transform 0.2s ease;
        }

        #janelaCadastrar .treinamentos-cargo-details[open] > summary .treinamentos-cargo-chevron {
            transform: rotate(180deg);
        }
    </style>
@endpush

    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :size="90">
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

                <fieldset class="mt-2"
                            v-if="form.vaga_id && (cargoCboResumo.cbo_codigo || cargoCboResumo.codigo_familia || cargoCboResumo.cbo_titulo || cargoCboResumo.cbo_familia || cargoCboResumo.cbo_descricao_sumaria)">
                    <legend class="font-size-14 mb-0">CBO do cargo</legend>
                    <div class="mt-2 p-3 border rounded bg-light">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-1"><small class="text-uppercase text-muted">Código CBO</small></div>
                                <div class="mb-2 font-weight-bold">@{{ cargoCboResumo.cbo_codigo || '—' }}</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-1"><small class="text-uppercase text-muted">Código da família</small></div>
                                <div class="mb-2 font-weight-bold">@{{ cargoCboResumo.codigo_familia || '—' }}</div>
                            </div>
                        </div>

                        <div class="mb-1"><small class="text-uppercase text-muted">Título</small></div>
                        <div class="mb-2 font-weight-bold">@{{ cargoCboResumo.cbo_titulo || 'Não informado' }}</div>

                        <div class="mb-1"><small class="text-uppercase text-muted">Família</small></div>
                        <div class="mb-2">@{{ cargoCboResumo.cbo_familia || 'Não informada' }}</div>

                        <div class="mb-1"><small class="text-uppercase text-muted">Descrição sumária</small></div>
                        <div class="mb-0">@{{ cargoCboResumo.cbo_descricao_sumaria || 'Não informada' }}</div>
                    </div>
                </fieldset>

                <div class="form-group" v-if="treinamentosCargo.length > 0">
                    {{-- <details>: abre/fecha nativo no navegador (evita conflito Collapse Bootstrap + modal/Vue). Fechado: sem atributo open. --}}
                    <details class="treinamentos-cargo-details border rounded overflow-hidden bg-white">
                        <summary
                            class="d-flex justify-content-between align-items-center bg-default py-2 px-3 mb-0 text-left"
                            style="cursor: pointer; box-shadow: none;">
                            <span>
                                <span class="font-weight-bold">Treinamentos vinculados ao cargo</span>
                                <small class="text-muted ml-1">(@{{ treinamentosCargo.length }})</small>
                            </span>
                            <i class="fa fa-fw fa-chevron-down treinamentos-cargo-chevron" aria-hidden="true"></i>
                        </summary>
                        <div class="border-top">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-condensed bg-white mb-0">
                                    <thead>
                                    <tr class="bg-default">
                                        <th>Treinamento</th>
                                        <th>Padrão de Treinamento</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="t in treinamentosCargo" :key="t.id">
                                        <td>@{{ t.label }}</td>
                                        <td>@{{ t.padrao_treinamento }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                </div>

                <div class="form-group" v-else-if="form.vaga_id && !editando">
                    <p class="text-muted mb-0"><small>Nenhum treinamento vinculado a este cargo.</small></p>
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
                <button type="button" class="btn btn-sm mr-1 btn-primary" :disabled="controle.carregando"
                        @click="formNovo(); $refs.janelaCadastrar?.abrirModal()">
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
                    <th>Treinamentos Vinculados</th>
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
                    </td>

                    <td>
                        <template v-if="vaga.vaga && vaga.vaga.vencimentos && vaga.vaga.vencimentos.length">
                            <span>Sim</span>
                            <br>
                            <span class="text-muted"> @{{ vaga.vaga.vencimentos.length }}
                                @{{ vaga.vaga.vencimentos.length === 1 ? 'treinamento' : 'treinamentos' }}</span>
                        </template>
                        <span v-else-if="vaga.vaga" class="text-muted">Não</span>
                        <span v-else class="text-muted">—</span>
                    </td>

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
                           @click.prevent="formAlterar(vaga.id); $refs.janelaCadastrar?.abrirModal()">
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
