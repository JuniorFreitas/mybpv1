@extends('layouts.sistema')
@section('title', 'Controle de ponto: Escalas')
@section('content_header', 'Controle de ponto: Escalas')
@section('content')
    {{--Janela confirmar pagar--}}
    <modal id="janelaConfirmar" titulo="Apagar escala">
        <template #conteudo>
            <preload v-show="formEscala.preload" label="Aguarde..."></preload>
            <div class="alert alert-success alert-dismissible" v-show="formEscala.save">
                <h4><i class="icon fa fa-check"></i> Escala apagada com sucesso!</h4>
            </div>
            <h4 v-show="!formEscala.save && !formEscala.preload">Atenção! Deseja realmente apagar escala? <br><br>
                <span class="text-danger">Funcionários que usam esta escala não poderão registrar ponto.</span></h4>

        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-danger" @click="apagarEscala()" v-show="!formEscala.save && !formEscala.preload">Apagar</button>
        </template>
    </modal>

    <!--Janela de Associar Escala-->
    <modal id="janelaAssociarEscala"  titulo="Associar escalas" :fechar="!formEscalaFuncionarios.preload" @fechou="resetFuncionariosSelecionados">
        <template #conteudo>
            <h4 class="text-success text-center" v-if="!formEscalaFuncionarios.preload && formEscalaFuncionarios.update">
                <i class="fas fa-check fa-2x"></i><br>
                Escala
                <span v-if="formEscalaFuncionarios.escala_id > 0">associada a</span>
                <span v-if="formEscalaFuncionarios.escala_id === 0">removida</span>
                 @{{ formEscalaFuncionarios.funcionariosSelecionados.length }} colaborador(es).
            </h4>
            <p class="text-center">
                <preload v-if="formEscalaFuncionarios.preload" label="Aguarde..."></preload>
            </p>
            <div v-if="!formEscalaFuncionarios.preload && !formEscalaFuncionarios.update">
                <h4 v-if="listaTodasEscalas.length === 0" class="text-center">
                    <i class="fas fa-map-marked-alt fa-2x"></i><br>
                    Nenhuma escala cadastrada
                </h4>
                <h5 v-if="listaTodasEscalas.length > 0" class="text-danger">
                     <i class="fas fa-users fa-2x"></i> selecionado(s) @{{ formEscalaFuncionarios.funcionariosSelecionados.length }} coladorador(es)
                </h5>
                <div class="form-group">
                    <h4>Selecione uma escala para associar:</h4>
                    <div class="custom-control custom-switch mt-2" v-for="(p,index) in listaTodasEscalas" :key="index">
                        <input type="checkbox" v-model="p['selecionado']" :value="p.id" :id="index" class="custom-control-input" @click="selecionarEscala(p)">
                        <label :for="index" class="custom-control-label">@{{ p.descricao }}</label>
                    </div>
                </div>
            </div>

        </template>
        <template #rodape>
            <button :disabled="listaTodasEscalas.length=== 0" v-if="!formEscalaFuncionarios.preload && !formEscalaFuncionarios.update" class="btn btn-sm mr-1 btn-success" type="button" @click="assosicarEscala">
                <i class="fas fa-link"></i> Aplicar
            </button>
        </template>
    </modal>

    <!--Janela Escalas-->
    <modal id="janelaFormEscalas"  :titulo="formEscala.titulo" :fechar="!formEscala.preload" :size="90">
        <template #conteudo>
            <h4 class="text-success text-center" v-if="!formEscala.preload && formEscala.save">
                <i class="fas fa-check fa-2x"></i><br>
                Escala
                <span v-if="formEscala.editando">atualizada</span>
                <span v-else> cadastrada</span>
            </h4>
            <p class="text-center">
                <preload v-if="formEscala.preload" label="Aguarde..."></preload>
            </p>
            <div v-show="!formEscala.preload && !formEscala.save">

                <div class="form-row">
                    <div class="form-group col-md-10">
                        <label>Descrição:</label>
                        <input type="text" class="form-control" placeholder="" onblur="valida_campo_vazio(this,3)" v-model="formEscala.descricao">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Data incial</label>
                        <datepicker v-model="formEscala.inicio" label=""  style="margin-top: -19px"></datepicker>
                    </div>

                </div>

                <escala :model="formEscala" :ocorrencias="ocorrencias" :ocorrencia_padrao="ocorrencia_id_padrao"></escala>

            </div>

        </template>
        <template #rodape>
            <button v-if="!formEscala.preload && !formEscala.save" class="btn btn-sm mr-1 btn-success" type="button" @click="salvarEscala">
                <span v-if="formEscala.editando">Alterar</span>
                <span v-else> Cadastrar </span>
            </button>
        </template>
    </modal>

    <div class="row">
        <div class="col-12">



            <form @submit.prevent="atualizarListaEscalas">
                <div class="form-row align-items-center mb-2">
                    <div class="col-sm-3 my-1">
                        <label class="sr-only">Buscar</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Busca" v-model="paginacaoEscalas.dados.campoBusca" @keyup="paginacaoEscalas.dados.campoBusca===''? atualizarListaEscalas():false">
                            <div class="input-group-append">
                                <div class="input-group-text"><i class="fas fa-search"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 my-1" v-if="escalas_insert">
                        <button  type="button" class="btn btn-primary" data-toggle="modal" data-target="#janelaFormEscalas" @click="formNovaEscala"> <i class="fas fa-user-clock"></i> Adicionar escala</button>
                    </div>

                </div>

            </form>
            <h4 v-show="!paginacaoEscalas.carregando && listaEscalas.length===0" class="text-center mt-3"> Sem escalas cadastradas</h4>
            <preload v-if="paginacaoEscalas.carregando"></preload>
            <table class="tabela"
                   v-if="!paginacaoEscalas.carregando && listaEscalas.length > 0">
                <thead>
                <tr class="bg-default">
                    <th >Descrição</th>
                    <th >Editar</th>
                    <th >Excluir</th>
                </tr>
                </thead>
                <tbody>
                <tr class="pointer" v-for="escala in listaEscalas">
                    <td data-label="descrição" >@{{escala.descricao}}</td>
                    <td data-label="editar">
                        <a v-if="escalas_update" href="javascript://" data-toggle="modal" data-target="#janelaFormEscalas" class="btn btn-sm mr-1 btn-success" @click="formEditarEscala(escala)"><i aria-hidden="true" class="fa fa-edit"></i> Editar
                        </a>
                    </td>
                    <td data-label="excluir">
                        <a v-if="escalas_delete" href="javascript://" data-toggle="modal" data-target="#janelaConfirmar" class="btn btn-sm mr-1 btn-danger" @click="formEscala.id = escala.id"><i aria-hidden="true" class="fa fa-trash"></i> Excluir
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>

            <controle-paginacao class="d-flex justify-content-center" ref="paginacaoEscalas"
                                url="{{route('g.controle-ponto.escalas.atualizarEscalas')}}" por-pagina="10"
                                :dados="paginacaoEscalas.dados"
                                v-on:carregou="carregouEscalas" v-on:carregando="carregandoEscalas"></controle-paginacao>


            <h4 v-show="!paginacaoFuncionarios.carregando && listaFuncionarios.length=== 0" class="text-center mt-3"> Sem colaboradores cadastrados</h4>
            <h4 v-show="!paginacaoFuncionarios.carregando && listaFuncionarios.length > 0" class="mt-3"> Associar escala aos colaboradores</h4>
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
                        <button v-if="escalas_funcionarios" type="button" class="btn btn-secondary" :disabled="formEscalaFuncionarios.funcionariosSelecionados.length===0" data-toggle="modal" data-target="#janelaAssociarEscala" @click="formAssociarEscala" >
                            <i class="fas fa-link"></i> Associar escala
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
                        <div class="form-check" v-if="escalas_funcionarios">
                            <input type="checkbox" class="form-check-input" v-model="todosFuncionariosSelecionados" @change="selecionarTodosFuncionarios">
                            <label class="form-check-label" style="visibility: hidden"></label>
                        </div>
                    </th>
                    <th >Nome</th>
{{--                    <th >Empresa</th>--}}
                    <th >Escala</th>
                </tr>
                </thead>
                <tbody>
                <tr class="pointer" v-for="funcionario in listaFuncionarios" @click="selecionarFuncionario(funcionario)">
                    <td data-label="id" class="text-center" width="10%">
                        <div class="form-check" v-if="escalas_funcionarios">
                            <input type="checkbox" :value="funcionario.id" class="form-check-input" v-model="formEscalaFuncionarios.funcionariosSelecionados">
                            <label class="form-check-label" style="visibility: hidden"></label>
                        </div>
                    </td>
                    <td data-label="nome" >@{{funcionario.nome}}</td>
{{--                    <td data-label="empresa">@{{funcionario.empresa.nome}}</td>--}}
                    <td data-label="escalametro">
                        <template v-if="funcionario.escalas_funcionario.length">
                            <span class="badge badge-secondary ml-1 p-1" v-for="escalas in funcionario.escalas_funcionario">
                                @{{escalas.descricao}}
                            </span>
                        </template>
                    </td>
                </tr>
                </tbody>
            </table>
            <controle-paginacao class="d-flex justify-content-center" ref="paginacaoFuncionarios"
                                url="{{route('g.controle-ponto.escalas.atualizarFuncionarios')}}" por-pagina="10"
                                :dados="paginacaoFuncionarios.dados"
                                v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
        </div>

    </div>
@stop
@push('js')

    <script src="{{mix('js/g/controle-ponto/escalas/app.js')}}"></script>
@endpush

@push('css')
    <style type="text/css">
        .pointer{
            cursor: pointer;
        }
    </style>
@endpush
