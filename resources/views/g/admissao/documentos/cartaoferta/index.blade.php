@extends('layouts.sistema')
@section('title', 'Documentos - Carta Oferta')
@section('content_header')
    <h4 class="text-default">Documentos - Carta Oferta</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
    <modal modal-pai="janelaVisualizar" titulo="Recusar Carta Oferta"
           :fechar="!atualizando"
           label-fechar="NÃO"
           id="janelaRecusar">
        <template slot="conteudo">
            <preload v-if="atualizando"></preload>
            <div v-if="objopen" class="text-center">
                <h5>Você tem certeza que deseja recusar a carta oferta de <br>
                    <strong class="text-danger">@{{objopen.curriculo.nome}}?</strong>
                </h5>
                {{--                <div class="form-group">--}}
                {{--                    <label for="">Motivo</label>--}}
                {{--                    <textarea class="form-control"></textarea>--}}
                {{--                </div>--}}
            </div>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-outline-danger" v-if="!atualizando"
                    @click.prevent="responder(objopen,'Recusado pelo RH')"
            > sim, recusar
            </button>
        </template>
    </modal>

    <modal id="janelaVisualizar" :titulo="tituloJanela" size="g">
        <template slot="conteudo">
            <preload class=" mt-2 text-center" v-if="preload"></preload>

            <div class="row" v-if="objopen">
                <div class="col-12">
                    <div class="alert"
                         :class="{
                                    'alert-danger': ['Recusado pelo RH','Expirado'].includes(objopen.status),
                                    'alert-warning': objopen.status === 'Pendente Anexo',
                                    'alert-info': objopen.status === 'Aguardando RH',
                                    'alert-success': objopen.status === 'Aceito pelo RH'
                                }"
                    >
                        <p>
                            Nome: <strong>@{{ objopen.curriculo.nome }}</strong><br>
                            Cargo: <strong>@{{ objopen.vaga_aberta.cargo.nome }}</strong><br>
                            Status: <strong>@{{ objopen.status }}</strong><br>
                            Contato: <br>
                            <strong>@{{ objopen.curriculo.tel_principal.numero }}
                                (@{{objopen.curriculo.tel_principal.tipo}})</strong>
                            <br><strong>@{{ objopen.curriculo.email }}</strong>


                        </p>
                    </div>
                </div>
            </div>

            <div v-if="abriupdf && objopen">
                <visualizador-pdf
                    :urldownload="objopen.anexo.urlDownload" :url="objopen.anexo.url"></visualizador-pdf>
            </div>
        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-primary"
                    v-if="objopen?.status === 'Aguardando RH' && !atualizando"
                    @click.prevent="responder(objopen,'Aceito pelo RH')">
                <i class="fa fa-save"></i> Aprovar
            </button>
            <button type="button" class="btn btn-sm btn-danger"
                    v-if="objopen?.status === 'Aguardando RH' && !atualizando"
                    data-toggle="modal"
                    data-target="#janelaRecusar"
            >
                <i class="fa fa-save"></i> Recusar
            </button>
        </template>
    </modal>


    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
        <form class="row" @submit.prevent="$refs.componente.buscar()">

            <div class="col-12 col-sm-3 col-md-3 col-lg-2">
                <div class="form-check" style="margin-bottom: -11px;">
                    <input type="checkbox" class="form-check-input" :disabled="controle.carregando"
                           @change="$refs.componente.buscar()"
                           :id="`filtroIntervalo_${hash}`"
                           v-model="controle.dados.filtroPeriodo">
                    <label class="form-check-label cursor-pointer" :for="`filtroIntervalo_${hash}`">Por
                        período</label>
                </div>
                <div class="form-group">
                    <datepicker range formsm label=""
                                :disabled="controle.carregando || !controle.dados.filtroPeriodo"
                                v-model="controle.dados.periodo"></datepicker>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <label>Buscar</label>
                <input type="text"
                       placeholder="Buscar por nome ou cpf"
                       autocomplete="mastertag"
                       class="form-control form-control-sm" :disabled="controle.carregando"
                       v-model="controle.dados.campoBusca">
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <div class="form-group">
                    <label for="">Status</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.status">
                        <option value="">Todos</option>
                        <option v-for="item in lista_status" :value="item">@{{ item }}</option>
                    </select>
                </div>
            </div>

<!--
            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <label>Por Projeto</label>
                <select class="form-control form-control-sm" @change="atualizar(); controle.dados.vaga_projeto_id = ''" :disabled="controle.carregando"
                        v-model="controle.dados.projeto_id">
                    <option value="">Todos</option>
                    <option v-for="item in lista_projetos" :value="item.id">@{{ item.nome }}: (@{{ item.preenchidas }}
                        de @{{ item.qnt_total }})
                    </option>
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                <label>Por Vaga</label>
                <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.vaga_projeto_id">
                    <option value="">Todos</option>
                    <option v-for="item in filterVagasProjeto[0]?.vagas_projeto" :value="item.id" :key="item.id">
                        @{{ item.vaga_aberta.titulo }}
                    </option>
                </select>
            </div>
-->


            <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                <label>Ordenar por</label>
                <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.order">
                    <option value="nome">Nome</option>
                    <option value="data_atualizacao">Data de atualização</option>
                </select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <label>Exibir</label>
                <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.pages">
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="150">150</option>
                </select>
            </div>
        </form>

        <div class="row mt-2">
            <div class="col-12">
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando"
                        @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

            </div>
        </div>

    </fieldset>
    <preload v-if="controle.carregando"></preload>

    <div id="conteudo">
        <div class="alert alert-warning" v-show="!controle.carregando && !lista.length">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>

        <div class="col-12 mb-2 mt-2 border-bottom bg-white" v-show="!controle.carregando && lista.length">
            <p class="pt-3">
                Legenda:
                <i class="fas fa-circle text-warning ml-2"></i> Pendente Anexo
                <i class="fas fa-circle text-info ml-2"></i> Aguardando RH
                <i class="fas fa-circle text-success ml-2"></i> Aceito pelo RH
                <i class="fas fa-circle text-danger ml-2"></i> Recusado pelo RH ou Expirado
            </p>
        </div>

        <div class="table-responsive" v-show="!controle.carregando && lista.length">
            <table class="table table-bordered table-striped ">
                <thead>
                <tr class="bg-white">
                    <th class="text-center">CÓD</th>
                    <th>Nome</th>
                    <th class="text-center">Projeto</th>
                    <th class="text-center">Cargo</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Ultima atualização</th>
                    <th class="text-center"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in lista"
                    :class="{
                        'bg-danger text-white': ['Recusado pelo RH','Expirado'].includes(item.status),
                        'bg-warning': item.status === 'Pendente Anexo',
                        'bg-info text-white': item.status === 'Aguardando RH',
                        'bg-success text-white': item.status === 'Aceito pelo RH'
                    }"
                >
                    <td class="text-center">
                        @{{item.curriculo_id}}
                    </td>
                    <td>
                        @{{item.curriculo.nome}}
                    </td>
                    <td class="text-center">
                        @{{item.vaga_projeto?.projeto?.nome}}
                    </td>
                    <td class="text-center">
                        @{{item.vaga_aberta?.cargo.nome}}
                    </td>

                    <td class="text-center">
                        @{{item.status}}
                    </td>

                    <td class="text-center">
                        @{{item.ultima_atualizacao}}
                    </td>


                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" title="Visuzalizar"
                                @click.prevent="formVisualizar(item)"
                                data-toggle="modal"
                                data-target="#janelaVisualizar"><i class="fa fa-search-plus"></i>
                        </button>
                    </td>

                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            :url="urlPaginacao"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/admissao/documentos/cartaoferta/app.js')}}"></script>
@endpush
