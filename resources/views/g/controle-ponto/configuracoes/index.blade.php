@extends('layouts.sistema')
@section('title', 'Controle de ponto: Configurações')
@section('content_header', 'Controle de ponto: configurações')
@section('content')
    {{--Janela confirmar pagar--}}
    <modal id="janelaConfirmar" titulo="Apagar perimetro">
        <template slot="conteudo">
            <preload v-show="formPerimetro.preload" label="Aguarde..."></preload>
            <div class="alert alert-success alert-dismissible" v-show="formPerimetro.save">
                <h4><i class="icon fa fa-check"></i> Perímetro apagado com sucesso!</h4>
            </div>
            <h4 v-show="!formPerimetro.save && !formPerimetro.preload">Atenção! Deseja realmente apagar perímetro:</h4>

        </template>
        <template slot="rodape">
            <button type="button" class="btn btn-sm btn-danger" @click="apagarPerimetro()" v-show="!formPerimetro.save && !formPerimetro.preload">Apagar</button>
        </template>
    </modal>
    <!--Janela de Associar Perimetro-->
    <modal id="janelaFormPerimetro"  :titulo="formPerimetro.titulo" :fechar="!formPerimetro.preload" size="g">
        <template slot="conteudo">
            <h4 class="text-success text-center" v-if="!formPerimetro.preload && formPerimetro.save">
                <i class="fas fa-check fa-2x"></i><br>
                Perímetro
                <span v-if="formPerimetro.editando">atualizado</span>
                <span v-else> cadastrado</span>
            </h4>
            <p class="text-center">
                <preload v-if="formPerimetro.preload" label="Aguarde..."></preload>
                <preload v-if="preloadGoogleMaps" label="Aguarde módulo Google Maps"></preload>
            </p>
            <div v-show="!formPerimetro.preload && !formPerimetro.save">

                <div class="form-group">
                    <label>Descrição:</label>
                    <input type="text" class="form-control" placeholder="Nome para o local" onblur="valida_campo_vazio(this,3)" v-model="formPerimetro.descricao">
                </div>
                <div class="form-group">
                    <label >Registrar ponto em um local específico:</label>
                    <select class="form-control" v-model="formPerimetro.obrigatorio" @change="formPerimetro.obrigatorio ? initMapTime():false">
                        <option :value="true" >Sim</option>
                        <option :value="false" >Não</option>
                    </select>
                    <small>
                        Define se será obrigatório registrar ponto dentro do perímetro espeficicado abaixo. Deixe com valor <strong>Não</strong> para casos de home office
                    </small>
                </div>
                <div class="row" v-if="formPerimetro.obrigatorio">
                    <div class="col-4">
                        <div class="form-group">
                            <label>Latitude:</label>
                            <input type="text" :disabled="true" class="form-control" v-model="formPerimetro.lat">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Longitude:</label>
                            <input type="text" :disabled="true" class="form-control" v-model="formPerimetro.long">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Raio:</label>
                            <input type="text" :disabled="true" class="form-control" v-model="formPerimetro.perimetro">
                            <small>Em metros</small>
                        </div>
                    </div>
                </div>
                <div class="form-group" v-if="formPerimetro.obrigatorio">
                    <label>Buscar por endereço</label>
                    <input type="text" :autocomplete="`mastertag_${parseInt((Math.random() * 999999))}`" class="form-control enderecoGoogle" placeholder="Buscar por endereço" style="width: 100%;">
                </div>
                <div class="googleMaps" id="mapaPrimetro" style="float:left;width:100%;height: 600px" v-if="formPerimetro.obrigatorio"></div>
                <!--  <div id="directionsPanel" style="float:right;width:30%;height:800px"></div>-->
            </div>

        </template>
        <template slot="rodape">
            <button v-if="!formPerimetro.preload && !formPerimetro.save" class="btn btn-sm btn-success" type="button" @click="salvarPerimetro">
                <span v-if="formPerimetro.editando">Alterar</span>
                <span v-else> Cadastrar </span>
            </button>
        </template>
    </modal>

    <!--Janela de Associar Perimetro-->
    <modal id="janelaAssociarPerimetro"  titulo="Associar perímetros" :fechar="!formPerimetroFuncionarios.preload" @fechou="resetFuncionariosSelecionados">
        <template slot="conteudo">
            <h4 class="text-success text-center" v-if="!formPerimetroFuncionarios.preload && formPerimetroFuncionarios.update">
                <i class="fas fa-check fa-2x"></i><br>
                Perímetro
                <span v-if="formPerimetroFuncionarios.perimetro_id > 0">associado a</span>
                <span v-if="formPerimetroFuncionarios.perimetro_id === 0">removido</span>
                 @{{ formPerimetroFuncionarios.funcionariosSelecionados.length }} colaborador(es).
            </h4>
            <p class="text-center">
                <preload v-if="formPerimetroFuncionarios.preload" label="Aguarde..."></preload>
            </p>
            <div v-if="!formPerimetroFuncionarios.preload && !formPerimetroFuncionarios.update">
                <h4 v-if="listaPerimetros.length === 0" class="text-center">
                    <i class="fas fa-map-marked-alt fa-2x"></i><br>
                    Nenhum perímetro cadastrado
                </h4>
                <h5 v-if="listaPerimetros.length > 0" class="text-danger">
                     <i class="fas fa-users fa-2x"></i> selecionado(s) @{{ formPerimetroFuncionarios.funcionariosSelecionados.length }} coladorador(es)
                </h5>
                <div class="form-group">
                    <label >Selecione um perímetro para associar:</label>
                    <select class="form-control" v-model="formPerimetroFuncionarios.perimetro_id">
                        <option :value="null" >Selecione....</option>
                        <option :value="0" >REMOVER PERÍMETRO DO COLABORADOR</option>
                        <option v-for="(p,index) in listaPerimetros" :key="p.id" :value="p.id" >@{{ p.descricao }}</option>
                    </select>
                </div>
            </div>

        </template>
        <template slot="rodape">
            <button :disabled="listaPerimetros.length=== 0 || formPerimetroFuncionarios.perimetro_id===null" v-if="!formPerimetroFuncionarios.preload && !formPerimetroFuncionarios.update" class="btn btn-sm btn-success" type="button" @click="assosicarPerimetros">
                <i class="fas fa-link"></i> Aplicar
            </button>
        </template>
    </modal>

    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs mt-5" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#config_frequencia" role="tab" aria-controls="home" aria-selected="true">Controle de ponto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#perimetros" role="tab" aria-controls="profile" aria-selected="false">Perimetros</a>
                </li>
<!--                <li class="nav-item">
                    <a class="nav-link" id="contact-tab" data-toggle="tab" href="#dispositivos_empresa" role="tab" aria-controls="contact" aria-selected="false">Dispositivos</a>
                </li>-->
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active p-2 " id="config_frequencia" role="tabpanel" aria-labelledby="config_frequencia-tab">
                    <div class="row">
                        <preload v-if="preload"></preload>
                        <div class="col-12 col-md-4" v-else>
                            <div class="form-group">
                                <label >Regime de compensação de horários</label>
                                <select :disabled="preloadConfig" class="form-control" v-model="formConfig.tipo_frequencia">
                                    <option value="hora_extra" >Horas extras</option>
                                    <option value="banco_horas" >Banco de horas</option>
                                    <option value="hibrido" >Híbrido</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tempo de tolerância para entrada e saída</label>
                                <input :disabled="preloadConfig" type="number" class="form-control" placeholder="Em minutos" v-mascara:numero value="15" v-model="formConfig.limite_tolerancia" onblur="valida_campo_vazio(this,1)">
                                <small class="text-muted">Tempo de tolerância para considerar hora extra ou banco de horas</small>
                            </div>
                            <div class="form-group">
                                <label>Tempo limite para falta</label>
                                <input :disabled="preloadConfig" type="number" class="form-control" placeholder="Em minutos" v-mascara:numero value="60" v-model="formConfig.tempo_limite_falta" onblur="valida_campo_vazio(this,1)">
                                <small class="text-muted">Tempo máximo em minutos até ser considerado falta do colaborador</small>
                            </div>
                            <div class="form-group">
                                <label>Tempo limite para saída</label>
                                <input :disabled="preloadConfig" type="number" class="form-control" placeholder="Em minutos" v-mascara:numero value="60" v-model="formConfig.tempo_limite_saida" onblur="valida_campo_vazio(this,1)">
                                <small class="text-muted">Tempo em minutos após o fim da jornada que deve ser verificado pelo gestor.</small>
                            </div>
                            <div class="form-group">
                                <label>Início de nova frequência</label>
                                <input :disabled="preloadConfig" type="number" class="form-control" placeholder="Dia do mês" v-mascara:numero value="1" v-model="formConfig.dia_nova_frequencia" onblur="valida_campo_vazio(this,1)">
                                <small class="text-muted">Dia do mês em que incia uma nova ficha de frequência</small>
                            </div>
                            <button v-if="config_empresa" type="button" class="btn btn-success btn-sm" :disabled="preloadConfig" @click="salvarConfiguracoes">
                                <preload v-if="preloadConfig" label="Salvar"></preload>
                                <span v-else>Salvar</span>
                            </button>
                        </div>
                    </div>


                </div>
                <div class="tab-pane fade p-2" id="perimetros" role="tabpanel" aria-labelledby="perimetros-tab">


                    <h4 v-show="!paginacaoPerimetros.carregando && listaPerimetros.length===0" class="text-center mt-3"> Sem perimetros cadastrados</h4>
                    <h4 v-show="!paginacaoPerimetros.carregando && listaPerimetros.length > 0" class="mt-3"> Perímetros cadastrados </h4>
                    <p class="text-right" v-if="perimetros_insert">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#janelaFormPerimetro" @click="formNovoPerimetro"> <i class="fas fa-map-marker-alt"></i> Adicionar perímetro</button>
                    </p>
                    <form @submit.prevent="atualizarListaPeriemetros">
                        <div class="form-row align-items-center">
                            <div class="col-sm-3 my-1">
                                <label class="sr-only">Buscar</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" placeholder="Descrição" v-model="paginacaoPerimetros.dados.campoBusca" @keyup="paginacaoPerimetros.dados.campoBusca===''? atualizarListaPeriemetros():false">
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fas fa-search"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <preload v-if="paginacaoPerimetros.carregando"></preload>
                    <table class="tabela"
                           v-if="!paginacaoPerimetros.carregando && listaPerimetros.length > 0">
                        <thead>
                        <tr class="bg-default">
                            <th >Descrição</th>
                            <th >Editar</th>
                            <th >Excluir</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="pointer" v-for="peri in listaPerimetros">
                            <td data-label="descrição" >@{{peri.descricao}}</td>
                            <td data-label="editar">
                                <a v-if="perimetros_update" href="javascript://" data-toggle="modal" data-target="#janelaFormPerimetro" class="btn btn-sm btn-success" @click="formEditarPerimetro(peri)"><i aria-hidden="true" class="fa fa-edit"></i> Editar
                                </a>
                            </td>
                            <td data-label="excluir">
                                <a v-if="perimetros_delete" href="javascript://" data-toggle="modal" data-target="#janelaConfirmar" class="btn btn-sm btn-danger" @click="formApagarPerimetro(peri.id)"><i aria-hidden="true" class="fa fa-trash"></i> Excluir
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <controle-paginacao class="d-flex justify-content-center" ref="paginacaoPerimetros"
                                        url="{{route('g.controle-ponto.perimetros.atualizarPerimetros')}}" por-pagina="10"
                                        :dados="paginacaoPerimetros.dados"
                                        v-on:carregou="carregouPerimetros" v-on:carregando="carregandoPerimetros"></controle-paginacao>


                    <h4 v-show="!paginacaoFuncionarios.carregando && listaFuncionarios.length===0" class="text-center mt-3"> Sem colaboradores cadastrados</h4>
                    <h4 v-show="!paginacaoFuncionarios.carregando && listaFuncionarios.length > 0" class="mt-3"> Associar perímetro aos colaboradores</h4>
                    <form @submit.prevent="atualizarListaFuncionarios">
                        <div class="form-row align-items-center">
                            <div class="col-sm-3 my-1">
                                <label class="sr-only">Buscar</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" placeholder="Nome colaborador" v-model="paginacaoFuncionarios.dados.campoBusca" @keyup="paginacaoFuncionarios.dados.campoBusca===''? atualizarListaFuncionarios():false">
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fas fa-search"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mb-2">
                                <button v-if="perimetros_funcionarios" type="button" class="btn btn-secondary" :disabled="formPerimetroFuncionarios.funcionariosSelecionados.length===0" data-toggle="modal" data-target="#janelaAssociarPerimetro" @click="formAssociarPerimetro" >
                                    <i class="fas fa-link"></i> Associar perímetro
                                </button>
                            </div>
                        </div>
                    </form>
                    <preload v-if="paginacaoFuncionarios.carregando"></preload>
                    <table class="tabela"
                           v-if="!paginacaoFuncionarios.carregando && listaFuncionarios.length > 0">
                        <thead>
                        <tr class="bg-default">
                            <th>
                                <div class="form-check" v-if="perimetros_funcionarios">
                                    <input type="checkbox" class="form-check-input" v-model="todosFuncionariosSelecionados" @change="selecionarTodosFuncionarios">
                                    <label class="form-check-label" style="visibility: hidden"></label>
                                </div>
                            </th>
                            <th >Nome</th>
{{--                            <th >Empresa</th>--}}
                            <th >Perímetro</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="pointer" v-for="funcionario in listaFuncionarios" @click="selecionarFuncionario(funcionario)">
                            <td data-label="id" class="text-center" width="10%">
                                <div class="form-check" v-if="perimetros_funcionarios">
                                    <input type="checkbox" :value="funcionario.id" class="form-check-input" v-model="formPerimetroFuncionarios.funcionariosSelecionados">
                                    <label class="form-check-label" style="visibility: hidden"></label>
                                </div>
                            </td>
                            <td data-label="nome" >@{{funcionario.nome}}</td>
{{--                            <td data-label="empresa">@{{funcionario.empresa.nome}}</td>--}}
                            <td data-label="perimetro"><span v-if="funcionario.perimetros_funcionario[0]">@{{funcionario.perimetros_funcionario[0].descricao}}</span></td>
                        </tr>
                        </tbody>
                    </table>
                    <controle-paginacao class="d-flex justify-content-center" ref="paginacaoFuncionarios"
                                        url="{{route('g.controle-ponto.configuracoes.atualizarFuncionarios')}}" por-pagina="10"
                                        :dados="paginacaoFuncionarios.dados"
                                        v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>


                </div>
                <div class="tab-pane fade" id="dispositivos_empresa" role="tabpanel" aria-labelledby="dispositivos_empresa-tab">

                </div>
            </div>

        </div>

    </div>
@stop
@push('js')
<!--    <script
        src='https://maps.google.com/maps/api/js?key=AIzaSyAjFL_y1aNK8ROElPeoZWvDwX5h7UYePkI&language=pt-BR&libraries=places&callback=initMap'
        async defer
    ></script>-->
    <script src="{{mix('js/g/controle-ponto/configuracoes/app.js')}}"></script>
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
