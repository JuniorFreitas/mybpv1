@extends('layouts.sistema')
@section('title', 'Histórico')
@section('content_header')
    <h4 class="text-default">Histórico</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')

    <modal id="janelaHistorico" :titulo="tituloJanela" :size="95">
        <template slot="conteudo">
            <div v-if="!openHistorico">
                <fieldset>
                    <legend>Informações do Colaborador</legend>
                    <div style="text-transform: uppercase">
                        <span>Nome: <strong>@{{ form.curriculo.nome }}</strong></span><br>
                        <span>CPF: <strong>@{{ form.curriculo.cpf }}</strong></span><br>
                        <span>
                            Cargo: <strong>@{{ form.admissao.cargo }}</strong> | Função: <strong>
                                @{{ form.admissao.funcao }}</strong></span><br>
                        <span>Data de admissão: <strong>@{{ form.admissao.data_admissao }}</strong></span><br>
                    </div>
                </fieldset>

                <ul class="nav nav-tabs bg-light" id="tabslist" role="tablist"
                    style="border-bottom: 1px solid #653232">
                    <li class="nav-item" v-if="permissoes.dossie">
                        <a class="nav-item nav-link active" id="nav-dossie-tab" data-toggle="tab"
                           @click.prevent="trocaAba('abrirDossie')"
                           href="#nav-dossie"
                           role="tab" aria-controls="nav-dossie" aria-selected="true">DOSSIÊ</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.feedback">
                        <a class="nav-item nav-link" id="nav-feedback-historico-tab"
                           @click.prevent="trocaAba('abrirFeedbackHistorico')" data-toggle="tab"
                           href="#nav-feedback-historico"
                           role="tab" aria-controls="nav-feedback-historico" aria-selected="true">
                            FEEDBACK</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.medida_administrativa">
                        <a class="nav-item nav-link" id="nav-medidas-administrativas-tab"
                           @click.prevent="trocaAba('abrirMedidas')" data-toggle="tab"
                           href="#nav-medidas-administrativas"
                           role="tab" aria-controls="nav-medidas-administrativas" aria-selected="true">
                            MEDIDAS ADMINISTRATIVAS</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.avaliacao_noventa_dias">
                        <a class="nav-item nav-link" id="nav-formulario-noventa-tab" data-toggle="tab"
                           @click.prevent="trocaAba('abrirFormularioNoventa')"
                           href="#nav-formulario-noventa"
                           role="tab" aria-controls="nav-formulario-noventa" aria-selected="false">AVALIAÇÃO 90 DIAS</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.avaliacao_anual">
                        <a class="nav-item nav-link" id="nav-avaliacao-anual-tab" data-toggle="tab"
                           @click.prevent="trocaAba('abrirAvaliacaoAnual')"
                           href="#nav-avaliacao-anual"
                           role="tab" aria-controls="nav-avaliacao-anual" aria-selected="false">AVALIAÇÃO ANUAL</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.ferias">
                        <a class="nav-item nav-link" id="nav-ferias-tab" data-toggle="tab"
                           @click.prevent="trocaAba('abrirFerias')"
                           href="#nav-ferias"
                           role="tab" aria-controls="nav-ferias" aria-selected="false">FÉRIAS</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.afastamento">
                        <a class="nav-item nav-link" id="nav-afastamento-tab" data-toggle="tab"
                           @click.prevent="trocaAba('abrirAfastamento')"
                           href="#nav-afastamento"
                           role="tab" aria-controls="nav-ferias" aria-selected="false">AFASTAMENTO</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.beneficio">
                        <a class="nav-item nav-link" id="nav-beneficio-tab" data-toggle="tab"
                           @click.prevent="trocaAba('abrirBeneficio')"
                           href="#nav-beneficio"
                           role="tab" aria-controls="nav-beneficio" aria-selected="false">BENEFÍCIO</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.cih">
                        <a class="nav-item nav-link" id="nav-cih-tab" data-toggle="tab"
                           @click.prevent="trocaAba('abrirCih')"
                           href="#nav-cih"
                           role="tab" aria-controls="nav-cih" aria-selected="false">CIH</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.promocao">
                        <a class="nav-item nav-link" id="nav-promocao-tab" data-toggle="tab"
                           @click.prevent="trocaAba('abrirPromocao')"
                           href="#nav-promocao"
                           role="tab" aria-controls="nav-promocao" aria-selected="false">PROMOÇÃO</a>
                    </li>
                    <li class="nav-item" v-if="permissoes.metas">
                        <a class="nav-item nav-link" id="nav-meta-tab" data-toggle="tab"
                           @click.prevent="trocaAba('abrirMetas')"
                           href="#nav-meta"
                           role="tab" aria-controls="nav-meta" aria-selected="false">METAS</a>
                    </li>
                </ul>

                <div class="tab-content py-3 p-2">
                    <div class="tab-pane fade show active" id="nav-dossie" role="tabpanel"
                         aria-labelledby="nav-dossie-tab">
                        <dossie v-if="abas.abrirDossie && permissoes.dossie" :feedback_id="form.feedback_id"></dossie>
                    </div>
                    <div class="tab-pane fade show" id="nav-feedback-historico" role="tabpanel"
                         aria-labelledby="nav-feedback-historico-tab">
                        <feedback-historico v-if="abas.abrirFeedbackHistorico && permissoes.feedback"
                                            :feedback_id="form.feedback_id"></feedback-historico>
                    </div>
                    <div class="tab-pane fade show" id="nav-medidas-administrativas" role="tabpanel"
                         aria-labelledby="nav-medidas-administrativas-tab">
                        <medidas-administrativas v-if="abas.abrirMedidas && permissoes.medida_administrativa"
                                                 :feedback_id="form.feedback_id"></medidas-administrativas>
                    </div>
                    <div class="tab-pane fade show" id="nav-formulario-noventa" role="tabpanel"
                         aria-labelledby="nav-formulario-noventa-tab">
                        <formulario-noventa-dias v-if="abas.abrirFormularioNoventa && permissoes.avaliacao_noventa_dias"
                                                 :feedback_id="form.feedback_id"></formulario-noventa-dias>
                    </div>
                    <div class="tab-pane fade show" id="nav-avaliacao-anual" role="tabpanel"
                         aria-labelledby="nav-avaliacao-anual-tab">
                        <avaliacao-anual v-if="abas.abrirAvaliacaoAnual" v-if="avaliacao_anual"
                                                :feedback_id="form.feedback_id"></avaliacao-anual>
                    </div>
                    <div class="tab-pane fade show" id="nav-ferias" role="tabpanel"
                         aria-labelledby="nav-ferias-tab">
                        <ferias v-if="abas.abrirFerias && permissoes.ferias"
                        :feedback_id="form.feedback_id" :curriculo_id="form.curriculo_id"></ferias>
                    </div>
                    <div class="tab-pane fade show" id="nav-afastamento" role="tabpanel"
                         aria-labelledby="nav-afastamento-tab">
                        <afastamento v-if="abas.abrirAfastamento"
                        :feedback_id="form.feedback_id"></afastamento>
                    </div>
                    <div class="tab-pane fade show" id="nav-beneficio" role="tabpanel"
                         aria-labelledby="nav-beneficio-tab">
                        <beneficio v-if="abas.abrirBeneficio && permissoes.beneficio"
                        :feedback_id="form.feedback_id"></beneficio>
                    </div>
                    <div class="tab-pane fade show" id="nav-cih" role="tabpanel"
                         aria-labelledby="nav-cih-tab">
                        <cih v-if="abas.abrirCih && permissoes.cih"
                        :fc_token="form.fc_token"></cih>
                    </div>
                    <div class="tab-pane fade show" id="nav-promocao" role="tabpanel"
                         aria-labelledby="nav-promocao-tab">
                        <promocao v-if="abas.abrirPromocao && permissoes.promocao"
                        :feedback_id="form.feedback_id"></promocao>
                    </div>
                    <div class="tab-pane fade show" id="nav-meta" role="tabpanel"
                         aria-labelledby="nav-meta-tab">
                        <metas v-if="abas.abrirMetas && permissoes.metas"
                        :feedback_id="form.feedback_id"></metas>
                    </div>
                </div>

            </div>
        </template>
        <template slot="rodape">
        </template>
    </modal>
    <fieldset>
        <legend class="text-uppercase">Filtro</legend>
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
            <div class="col-12 col-sm-4">
                <div class="form-group">
                    <label>Por cargo</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoCargo">
                        <option value="">Todos os Cargos</option>
                        <option v-for="cargo in cargos" :value="cargo.nome">@{{cargo.nome}}</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-sm-2" v-if="permissoes.filtrar_demitido">
                <div class="form-group">
                    <label>Por Demitido</label>
                    <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                            v-model="controle.dados.campoDemitido">
                        <option :value="true">Sim</option>
                        <option :value="false">Não</option>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-2">
                <label>Exibir</label>
                <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                        v-model="controle.dados.pages">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </form>
        <div class="row mt-2">
            <div class="col-12">
                <button type="button" class="btn btn-sm btn-success mb-1" :disabled="controle.carregando"
                        :style="controle.carregando ? 'cursor: not-allowed' : 'cursor: pointer'" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
            </div>
        </div>

    </fieldset>

    <div id="conteudo">

        <div class="alert alert-warning" v-show="!controle.carregando && lista.length===0">
            <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
        </div>
        <div class="table-responsive" v-show="!controle.carregando && lista.length > 0">
            <table class="tabela">
                <thead>
                <tr class="bg-default">
                    <th class="text-center">ID</th>
                    <th class="text-center">Nome</th>
                    <th class="text-center">Cargo</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Ação</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in lista">
                    <td class="text-center">
                        @{{item.id}}
                    </td>
                    <td class="text-center">
                        @{{item.curriculo.nome}}
                    </td>
                    <td class="text-center">
                        @{{item.admissao.cargo}}
                    </td>
                    <td class="text-center">
                        @{{item.admissao.status}}
                        @{{item.admissao.status === 'ADMITIDO' ? item.admissao.data_admissao : null}}
                    </td>

                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-primary mb-1" title="Editar"
                                @click.prevent="abrirHistorico(item)"
                                data-toggle="modal"
                                data-target="#janelaHistorico">
                            <i class="fa fa-edit"></i>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.historico.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/admissao/historico/app.js')}}"></script>
@endpush
