@extends('layouts.sistema')
@section('title', 'Controle de ponto: Ajuste de jornadas')
@section('content_header', 'Controle de ponto: Ajuste de jornadas')
@section('content')

    <!--Janela de detalhes-->
    <modal ref="janelaFormDetalhes" id="janelaFormDetalhes" titulo="Detalhes da jornada" :size="90" :fechar="!formPonto.preload" @fechou="atualizarComponentePaginacao();">
        <template #conteudo>
            <p class="text-center">
                <preload v-if="formPonto.preload" label="Aguarde..."></preload>
            </p>
            <div class="alert alert-success alert-dismissible" v-show="formPonto.save">
                <h3 class="text-center"><i class="icon fa fa-check"></i> Ajustes salvos</h3>
            </div>
            <div v-if="!formPonto.preload && !formPonto.save && paginacaoRef">

                <!-- Tabs -->
                <ul class="nav nav-pills nav-fill mt-3">
<!--                    <li class="nav-item">
                        <a class="nav-link show active" id="intervalos-tab" data-toggle="tab" href="#intervalos" role="tab" aria-controls="home" aria-selected="true">Jornada</a>
                    </li>
                   <li class="nav-item">
                        <a class="nav-link" id="avisos-tab" data-toggle="tab" href="#avisos" role="tab" aria-controls="profile" aria-selected="false">Avisos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="historicos-tab" data-toggle="tab" href="#historicos" role="tab" aria-controls="profile" aria-selected="false">Histórico</a>
                    </li>-->
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active show p-2 " id="intervalos" role="tabpanel" aria-labelledby="intervalos-tab">
                        <div class="row">
                            <div class="col-4 col-sm-1">
                                <div class="avatar-md align-self-center mr-3">
                            <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                @{{ formatNome(formPonto.funcionario.nome.toUpperCase()) }}
                            </span>
                                </div>
                            </div>
                            <div class="col-4 col-sm-6">
                                <i class="fas fa-calendar-day fa-2x mr-2"></i> <span style="font-size: 20px">@{{ formPonto.dia }}</span>
                                <button type="button" class="btn btn-sm mr-1 btn-default btn-outline-primary ml-3 mr-4" @click="jornadaAnterior()" v-show="paginacaoRef.atual > 1">
                                    <i class="fas fa-chevron-left fa-2x" ></i>
                                </button>
                                <button type="button" class="btn btn-sm mr-1 btn-default btn-outline-primary ml-3 mr-4" @click="proximaJornada()" v-show="paginacaoRef.atual < paginacaoRef.total">
                                    <i class="fas fa-chevron-right fa-2x"></i>
                                </button>
                                <br>
                                <h4>@{{ formPonto.funcionario.nome }}</h4>


                            </div>
                            <div class="col-4 col-sm-5">
                                <div class="form-group">
                                    <label>Ocorrência</label>
                                    <select class="form-control" v-model="formPonto.ocorrencia_id">
                                        <option v-for="ocorrencia in formPonto.ocorrencias_jornadas" :value="ocorrencia.id">@{{ ocorrencia.descricao }}</option>
                                    </select>
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="checkBoxVerificar" v-model="formPonto.verificado">
                                    <label class="form-check-label" for="checkBoxVerificar">
                                        <h5 v-show="!formPonto.verificado" class="mb-1"><span class="badge badge-danger" >Não verificado</span></h5>
                                        <h5 v-show="formPonto.verificado" ><span class="badge badge-success" >Verificado</span></h5>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="accordion" id="accordion">
                            <div class="card">
                                <div class="card-header bg-primary" id="headingOne">
                                    <h4 class="mb-0">
                                        <button class="btn btn-link text-white collapsed" type="button" data-toggle="collapse" data-target="#campoJornadaPrevista" aria-expanded="true" aria-controls="campoJornadaPrevista">
                                            Jornada prevista
                                        </button>
                                    </h4>
                                </div>

                                <div id="campoJornadaPrevista" class="collapse" aria-labelledby="campoJornadaPrevista" >
                                    <div class="card-body">
                                        <escala :model="jornada_prevista" :ocorrencias="formPonto.ocorrencias_jornadas" :ocorrencia_padrao="formPonto.ocorrencia_id_padrao"
                                        :botao_add_jornada="false"
                                        :botao_add_periodo="false"
                                        :info="false"
                                        :excluir_jornada="false"
                                        :exibir_controles_jornada="false"
                                        :topo_card="false"
                                        :bloqueado="true"
                                        :botao_remover_periodo="false"
                                        :filtro_jornadas_id="jornada_atual.id"
                                        ></escala>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header bg-primary" id="headingTwo">
                                    <h4 class="mb-0">
                                        <button class="btn btn-link text-white" type="button" data-toggle="collapse" data-target="#campoJornadaRealizada" aria-expanded="false" aria-controls="collapseTwo">
                                            Jornada realizada
                                        </button>
                                    </h4>
                                </div>
                                <div id="campoJornadaRealizada" class="collapse multi-collapse show" aria-labelledby="headingTwo" >
                                    <div class="card-body">
                                        <escala v-if="ocorrenciaSelecionada && ocorrenciaSelecionada.trabalhado" :model="formPonto.jornada.escala" :ocorrencias="formPonto.ocorrencias_jornadas" :ocorrencia_padrao="formPonto.ocorrencia_id_padrao"
                                                :botao_add_jornada="false"
                                                :botao_add_periodo="true"
                                                :info="false"
                                                :excluir_jornada="false"
                                                :exibir_controles_jornada="false"
                                                :topo_card="false"
                                                :bloqueado="false"
                                                :botao_remover_periodo="true"
                                        ></escala>

                                        <div class="alert alert-warning" role="alert" v-if="ocorrenciaSelecionada && !ocorrenciaSelecionada.trabalhado">
                                           <h3 class="text-center">
                                               <i class="fas fa-exclamation-triangle fa-2x"></i><br>
                                               @{{ ocorrenciaSelecionada.descricao }}
                                           </h3>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Justificativa</label>
                            <textarea class="form-control" rows="3" v-model="formPonto.justificativa"></textarea>
                        </div>


                    </div>
<!--                    <div class="tab-pane fade p-2 " id="avisos" role="tabpanel" aria-labelledby="avisos-tab"></div>
                    <div class="tab-pane fade p-2 " id="historicos" role="tabpanel" aria-labelledby="historicos-tab"></div>-->
                </div>
            </div>

        </template>
        <template #rodape v-if="!formPonto.preload && !formPonto.save && paginacaoRef">
            <button type="button" class="btn btn-sm mr-1 btn-success" @click="salvar">Ajustar</button>
            <div class="form-group form-check" v-if="(!formPonto.preload && !formPonto.save) && (paginacaoRef.atual < paginacaoRef.total)">
                <input type="checkbox" class="form-check-input" id="checkBoxProximo" v-model="irParaProximaJornada">
                <label class="form-check-label" for="checkBoxProximo">
                    Ajustar e buscar o próximo
                </label>
            </div>
        </template>
    </modal>


    <div class="row">

        <div class="col-12">
            <div class="row">
                <div class="col-12 mt-3">
                    <form>
                        <div class="form-row">
                            <div class="col-3">
                                <datepicker label="Intervalo" formsm :range="true" v-model="formBusca.intervalo" :disabled="formBusca.preload" @onselect="atualizarTudo()"></datepicker>
                            </div>
                            <div class="col">
                                <label>Colaborador</label>
                                <autocomplete formsm caminho="autocomplete/funcionarios"
                                              :valido="formBusca.funcionario_id!=null"
                                              v-model="formBusca.funcionarioNome"
                                              placeholder="Nome do funcionário"
                                              @onblur="resetaCampoFunccionario"
                                              @onselect="selecionaFuncionario"></autocomplete>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-sm mr-1 btn-success mr-1 mb-2" :disabled="formBusca.preload"
                                        @click="atualizarTudo()"><i
                                        :class="formBusca.preload ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                                    Buscar
                                </button>
                                <button type="button" class="btn btn-danger btn-sm mr-1 mb-2" @click="botaoResetCampos()">Limpar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <ul class="nav nav-pills nav-fill mt-3">
                <li class="nav-item">
                    <a class="nav-link show active" id="pendentes-tab" data-toggle="tab" href="#jornadas_pendentes" @click.prevent="atualizarPendentes()" role="tab" aria-controls="home" aria-selected="true">Jornadas pendentes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="incompletas-tab" data-toggle="tab" href="#jornadas_incompletas" role="tab" @click.prevent="atualizarIncompletas()" aria-controls="profile" aria-selected="false">Jornadas incompletas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="verificadas-tab" data-toggle="tab" href="#jornadas_verificadas" role="tab" @click.prevent="atualizarVerificadas()" aria-controls="profile" aria-selected="false">Jornadas verificadas</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade active show p-2 " id="jornadas_pendentes" role="tabpanel" aria-labelledby="pendentes-tab">
                    <preload v-if="formBusca.preload"></preload>
                    <div class="col-12" v-else>
                        <h4 class="text-center" v-if="listaPendentes.length === 0 && !formBusca.preload">Nenhum registro encontrado</h4>

                        <table class="tabela" v-if="listaPendentes.length > 0 && !formBusca.preload">
                            <thead class="bg-default">
                            <tr>
                                <th>Dia</th>
                                <th>Funcionário</th>
                                <th>Ocorrência</th>
                                <th>Entrada</th>
                                <th>Saída</th>
                                <th>Trabalhado</th>
                                <th>Verificado</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="ponto in listaPendentes">
                                <td>
                                    @{{ ponto.dia }}
                                </td>
                                <td>
                                    @{{ ponto.funcionario.nome }}
                                </td>
                                <td>
                                    <h5><span :class="{'badge badge-success':ponto.ocorrencia.trabalhado,'badge badge-warning':!ponto.ocorrencia.trabalhado}">@{{ ponto.ocorrencia.descricao }}</span></h5>
                                </td>
                                <td class="text-center">
                                    <template v-if="ponto.ocorrencia.trabalhado">
                                        <template v-for="periodo in ponto.periodos">
                                            <span>@{{ periodo.entrada }}</span><br>
                                        </template>
                                    </template>
                                    <br>

                                </td>
                                <td class="text-center">
                                    <template v-if="ponto.ocorrencia.trabalhado">
                                        <template v-for="periodo in ponto.periodos">
                                            <template v-if="periodo.saida">
                                                <span>@{{ periodo.saida }}</span><br>
                                            </template>
                                            <span v-else><h5><span class="badge badge-warning">Trabalhando</span></h5></span>
                                        </template>
                                    </template>
                                    <br>

                                </td>
                                <td class="text-center">
                                    <br>
                                    <div v-if="ponto.ocorrencia.trabalhado && ponto.periodos_em_aberto.length===0">
                                        <template v-for="periodo in ponto.periodos">
                                            <span>@{{ periodo.horasTrabalhadasFormat }}</span><br>
                                        </template>
                                        <br>
                                        <span v-if="ponto.horasExtra > 0" class="text-success">+@{{ ponto.horasExtraFormat }}</span>
                                        <span v-if="ponto.horasExtra < 0" class="text-danger">-@{{ ponto.horasExtraFormat }}</span>
                                    </div>
                                    <div v-else>
                                        <span>--</span><br>
                                        <br>
                                        <span>--</span>
                                    </div>

                                </td>
                                <td>
                                    <h5><span
                                            :class="{'badge badge-success':ponto.verificado,'badge badge-danger':!ponto.verificado}">@{{ ponto.verificado ? 'Sim':'Não' }}</span>
                                    </h5>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                            @click="verDetalhes(ponto.id,'pag_pendentes'); $refs.janelaFormDetalhes?.abrirModal()"><i
                                            class="fas fa-info-circle"></i> Verificar
                                    </button>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <controle-paginacao class="d-flex justify-content-center" ref="pag_pendentes"
                                        url="{{route('g.controle-ponto.ajustar-jornadas.atualizaJornadasPendentes')}}"
                                        :por-pagina="porPagina"
                                        :dados="{intervalo:formBusca.intevalo,funcionario_id:formBusca.funcionario_id,intervalo:formBusca.intervalo}"
                                        v-on:carregou="carregouPendentes" v-on:carregando="carregandoPendentes"></controle-paginacao>


                </div>

                <div class="tab-pane fade p-2 " id="jornadas_incompletas" role="tabpanel" aria-labelledby="incompletas-tab">
                    <preload v-if="formBusca.preload"></preload>
                    <div class="col-12" v-else>
                        <h4 class="text-center" v-if="listaIncompletas.length === 0 && !formBusca.preload">Nenhum registro encontrado</h4>

                        <table class="tabela" v-if="listaIncompletas.length > 0 && !formBusca.preload">
                            <thead class="bg-default">
                            <tr>
                                <th>Dia</th>
                                <th>Funcionário</th>
                                <th>Ocorrência</th>
                                <th>Entrada</th>
                                <th>Saída</th>
                                <th>Trabalhado</th>
                                <th>Verificado</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="ponto in listaIncompletas">
                                <td>
                                    @{{ ponto.dia }}
                                </td>
                                <td>
                                    @{{ ponto.funcionario.nome }}
                                </td>
                                <td>
                                    <h5><span :class="{'badge badge-success':ponto.ocorrencia.trabalhado,'badge badge-warning':!ponto.ocorrencia.trabalhado}">@{{ ponto.ocorrencia.descricao }}</span></h5>
                                </td>
                                <td class="text-center">
                                    <template v-if="ponto.ocorrencia.trabalhado">
                                        <template v-for="periodo in ponto.periodos">
                                            <span>@{{ periodo.entrada }}</span><br>
                                        </template>
                                    </template>
                                    <br>

                                </td>
                                <td class="text-center">
                                    <template v-if="ponto.ocorrencia.trabalhado">
                                        <template v-for="periodo in ponto.periodos">
                                            <template v-if="periodo.saida">
                                                <span>@{{ periodo.saida }}</span><br>
                                            </template>
                                            <span v-else><h5><span class="badge badge-warning">Trabalhando</span></h5></span>
                                        </template>
                                    </template>
                                    <br>

                                </td>
                                <td class="text-center">
                                    <br>
                                    <div v-if="ponto.ocorrencia.trabalhado && ponto.periodos_em_aberto.length===0">
                                        <template v-for="periodo in ponto.periodos">
                                            <span>@{{ periodo.horasTrabalhadasFormat }}</span><br>
                                        </template>
                                        <br>
                                        <span v-if="ponto.horasExtra > 0" class="text-success">+@{{ ponto.horasExtraFormat }}</span>
                                        <span v-if="ponto.horasExtra < 0" class="text-danger">-@{{ ponto.horasExtraFormat }}</span>
                                    </div>
                                    <div v-else>
                                        <span>--</span><br>
                                        <br>
                                        <span>--</span>
                                    </div>

                                </td>
                                <td>
                                    <h5><span
                                            :class="{'badge badge-success':ponto.verificado,'badge badge-danger':!ponto.verificado}">@{{ ponto.verificado ? 'Sim':'Não' }}</span>
                                    </h5>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                            @click="verDetalhes(ponto.id,'pag_incompletas'); $refs.janelaFormDetalhes?.abrirModal()"><i
                                            class="fas fa-info-circle"></i> Verificar
                                    </button>
                                </td>
                            </tr>

                            </tbody>
                        </table>

                    </div>
                    <controle-paginacao class="d-flex justify-content-center" ref="pag_incompletas"
                                        url="{{route('g.controle-ponto.ajustar-jornadas.atualizaJornadasIncompletas')}}"
                                        :por-pagina="porPagina"
                                        :dados="{intervalo:formBusca.intevalo,funcionario_id:formBusca.funcionario_id,intervalo:formBusca.intervalo}"
                                        v-on:carregou="carregouIncompletas" v-on:carregando="carregandoIncompletas"></controle-paginacao>


                </div>

                <div class="tab-pane fade p-2" id="jornadas_verificadas" role="tabpanel" aria-labelledby="verificadas-tab">
                    <preload v-if="formBusca.preload"></preload>
                    <div class="col-12" v-else>
                        <h4 class="text-center" v-if="listaVerificada.length === 0 && !formBusca.preload">Nenhum registro encontrado</h4>

                        <table class="tabela" v-if="listaVerificada.length > 0 && !formBusca.preload">
                            <thead class="bg-default">
                            <tr>
                                <th>Dia</th>
                                <th>Funcionário</th>
                                <th>Ocorrência</th>
                                <th>Entrada</th>
                                <th>Saída</th>
                                <th>Trabalhado</th>
                                <th>Verificado</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="ponto in listaVerificada">
                                <td>
                                    @{{ ponto.dia }}
                                </td>
                                <td>
                                    @{{ ponto.funcionario.nome }}
                                </td>
                                <td>
                                    <h5><span :class="{'badge badge-success':ponto.ocorrencia.trabalhado,'badge badge-warning':!ponto.ocorrencia.trabalhado}">@{{ ponto.ocorrencia.descricao }}</span></h5>
                                </td>
                                <td class="text-center">
                                    <template v-if="ponto.ocorrencia.trabalhado">
                                        <template v-for="periodo in ponto.periodos">
                                            <span>@{{ periodo.entrada }}</span><br>
                                        </template>
                                    </template>
                                    <br>

                                </td>
                                <td class="text-center">
                                    <template v-if="ponto.ocorrencia.trabalhado">
                                        <template v-for="periodo in ponto.periodos">
                                            <template v-if="periodo.saida">
                                                <span>@{{ periodo.saida }}</span><br>
                                            </template>
                                            <span v-else><h5><span class="badge badge-warning">Trabalhando</span></h5></span>
                                        </template>
                                    </template>
                                    <br>

                                </td>
                                <td class="text-center">
                                    <br>
                                    <div v-if="ponto.ocorrencia.trabalhado">
                                        <template v-for="periodo in ponto.periodos">
                                            <span >@{{ periodo.horasTrabalhadasFormat }}</span><br>
                                        </template>
                                        <br>
                                        <span v-if="ponto.horasExtra > 0" class="text-success">+@{{ ponto.horasExtraFormat }}</span>
                                        <span v-if="ponto.horasExtra < 0" class="text-danger">-@{{ ponto.horasExtraFormat }}</span>
                                    </div>

                                </td>
                                <td>
                                    <h5><span :class="{'badge badge-success':ponto.verificado,'badge badge-danger':!ponto.verificado}">@{{ ponto.verificado ? 'Sim':'Não' }}</span></h5>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm" @click="verDetalhes(ponto.id,'pag_verificadas'); $refs.janelaFormDetalhes?.abrirModal()"> <i class="fas fa-info-circle"></i> Verificar</button>
                                </td>
                            </tr>

                            </tbody>
                        </table>

                    </div>
                    <controle-paginacao class="d-flex justify-content-center" ref="pag_verificadas"
                                        url="{{route('g.controle-ponto.ajustar-jornadas.atualizaJornadasVerificadas')}}"
                                        :por-pagina="porPagina"
                                        :dados="{intervalo:formBusca.intevalo,funcionario_id:formBusca.funcionario_id,intervalo:formBusca.intervalo}"
                                        v-on:carregou="carregouVerificadas" v-on:carregando="carregandoVerificadas"></controle-paginacao>
                </div>
            </div>

        </div>

    </div>
@stop
@push('js')
    <script src="{{mix('js/g/controle-ponto/ajuste-jornadas/app.js')}}"></script>
@endpush

@push('css')
    <style type="text/css">
        .googleMaps {
            height: 100%;
            border: 1px solid #aeb9c2;
        }
        .pac-container {
            z-index: 1051 !important;
        }
        .pointer{
            cursor: pointer;
        }
    </style>
@endpush
