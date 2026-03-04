@extends('layouts.sistema')
@section('title', 'Entrevista - Parecer RH')
@section('content_header','Entrevista - Parecer RH')
@section('content')
    <modal id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template #conteudo>
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.pcd" @click="colunasTabela.pcd = !colunasTabela.pcd"
                       class="custom-control-input" id="pcd">
                <label class="custom-control-label"
                       for="pcd">PCD</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.rota_transporte"
                       @click="colunasTabela.rota_transporte = !colunasTabela.rota_transporte"
                       class="custom-control-input" id="rota_transporte">
                <label class="custom-control-label"
                       for="rota_transporte">ROTA TRANSPORTE</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.rh_nota"
                       @click="colunasTabela.rh_nota = !colunasTabela.rh_nota"
                       class="custom-control-input" id="rh_nota">
                <label class="custom-control-label"
                       for="rh_nota">PARECER RH NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.entrevista_tecnica"
                       @click="colunasTabela.entrevista_tecnica = !colunasTabela.entrevista_tecnica"
                       class="custom-control-input" id="entrevista_tecnica">
                <label class="custom-control-label"
                       for="entrevista_tecnica">ENTREVISTA TÉCNICA NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.teste_pratico"
                       @click="colunasTabela.teste_pratico = !colunasTabela.teste_pratico" class="custom-control-input"
                       id="teste_pratico">
                <label class="custom-control-label"
                       for="teste_pratico">TESTE PRÁTICO</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id > 1">
                <input type="checkbox" v-model="colunasTabela.parecer_individual"
                       @click="colunasTabela.parecer_individual = !colunasTabela.parecer_individual"
                       class="custom-control-input" id="parecer_individual">
                <label class="custom-control-label"
                       for="parecer_individual">PARECER INDIVIDUAL</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id > 1">
                <input type="checkbox" v-model="colunasTabela.nota_individual"
                       @click="colunasTabela.nota_individual = !colunasTabela.nota_individual"
                       class="custom-control-input" id="nota_individual">
                <label class="custom-control-label"
                       for="nota_individual">NOTA INDIVIDUAL</label>
            </div>
        </template>
    </modal>

    <modal id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preloadForm">
        <template #conteudo>
            <preload v-if="preloadForm"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''">
                <form-rh :form="form"
                         :cliente_id="form.id"
                         :visualizar="visualizar"
                         :disabled-parecer-rh="false"
                         @finalizou="()=>{preloadForm = false}"
                >
                </form-rh>
            </div>
        </template>
        <template #rodape>
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm btn-primary" v-show="editando && !atualizado  && !preloadForm"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Alterar
                </button>
                <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !cadastrado  && !preloadForm"
                        @click.prevent="cadastrar">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </div>
        </template>
    </modal>

    <fieldset>
        <legend>Filtro</legend>
        <form @submit.prevent="$refs.componente.buscar()">
            <div class="row">
                <div class="col-12 col-md-4">
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

                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text"
                               placeholder="Buscar por nome"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text"
                               placeholder="Buscar por cpf"
                               autocomplete="mastertag"
                               onblur="valida_cpf(this)"
                               v-mascara:cpf
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoCPF">
                    </div>
                </div>


                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="form-group">
                        <label>Cargo</label>
                        <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                      :valido="controle.dados.campoVaga !== ''"
                                      v-model="controle.dados.autocomplete_label"
                                      :disabled="controle.carregando"
                                      placeholder="Por cargo"
                                      @onblur="resetaCampo"
                                     @onselect="selecionaVaga"></autocomplete>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoUf">
                            <option value="">SEM FILTRO</option>
                            <option value="MA">MA</option>
                            <option value="AC">AC</option>
                            <option value="AL">AL</option>
                            <option value="AP">AP</option>
                            <option value="AM">AM</option>
                            <option value="BA">BA</option>
                            <option value="CE">CE</option>
                            <option value="DF">DF</option>
                            <option value="ES">ES</option>
                            <option value="GO">GO</option>
                            <option value="MT">MT</option>
                            <option value="MS">MS</option>
                            <option value="MG">MG</option>
                            <option value="PA">PA</option>
                            <option value="PB">PB</option>
                            <option value="PR">PR</option>
                            <option value="PE">PE</option>
                            <option value="PI">PI</option>
                            <option value="RJ">RJ</option>
                            <option value="RN">RN</option>
                            <option value="RS">RS</option>
                            <option value="RO">RO</option>
                            <option value="RR">RR</option>
                            <option value="SC">SC</option>
                            <option value="SP">SP</option>
                            <option value="SE">SE</option>
                            <option value="TO">TO</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label>Nota Rh</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoRh">
                            <option value="">Geral</option>
                            <option value="realizado">Realizado</option>
                            @foreach(range(1,10) as $i)
                                <option value="{{ $i }}">Nota {{ $i }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label for="">Final Rh</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoFinalRh">
                            <option value="">Todos</option>
                            <option value="favoravel">Favorável</option>
                            <option value="restricao">Restrição</option>
                            <option value="desfavoravel">Desfavorável</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2" v-if="industria">
                    <div class="form-group">
                        <label for="">Rota</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoRota">
                            <option value="">Geral</option>
                            <option :value="true">Tem Rota</option>
                            <option :value="false">Não tem Rota</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2" v-if="industria">
                    <div class="form-group">
                        <label for="">Técnica</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoTecnica">
                            <option value="">Geral</option>
                            <option value="realizado">Realizado</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2" v-if="industria">
                    <div class="form-group">
                        <label for="">Teste</label>
                        <select class="form-control form-control-sm" @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.campoTeste">
                            <option value="">Geral</option>
                            <option value="realizado">Realizado</option>
                            <option value="0">Não se Aplica</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3" v-if="servico">
                    <label for="">Por classificação</label>
                    <div class="form-group">
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.parecer_individual">
                            <option value="">Sem filtro</option>
                            <option value="entrevistado">Entrevistados</option>
                            <option value="nao_entrevistado">Não Entrevistados</option>
                            <option value="favoravel">Favorável</option>
                            <option value="destaque">Destaque</option>
                            <option value="stand_by">Stand By</option>
                            <option value="desfavoravel">Desfavorável</option>
                        </select>

                    </div>
                </div>


                <div class="col-12 col-sm-4 col-md-3 col-lg-2" v-if="servico">
                    <div class="form-group">
                        <label for="">Por nota individual</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoRh">
                            <option value="">Sem filtro</option>
                            <option value="0">0</option>
                            <option value="1-5">1 à 5</option>
                            <option value="5-7">5 à 7</option>
                            <option value="8-10">8 à 10</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.pages">
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>

        <div class="col-12">
            <div class="row">
                <button type="button" class="btn btn-sm btn-success mr-1 mb-1" :disabled="controle.carregando"
                        @click.prevent="atualizar">
                    <i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button class="btn btn-sm btn-danger mb-1 mr-1"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0" @click.prevent="selecionados = []">
                    <i class="fa fa-times"></i> Limpar seleção
                </button>

                <button type="button" class="btn btn-sm btn-primary mb-1 mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && lista.length===0 && selecionados.length === 0) ">
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL <span class="badge badge-light"
                                                                           v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                </button>

{{--                <form target="_blank"--}}
{{--                      action="{{ route('parecerrh.excel') }}"--}}
{{--                      --}}{{--                      action="{{ \App\Models\Sistema::UrlServidor }}/parecer_rh/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS"--}}
{{--                      method="get">--}}
{{--                    @csrf--}}
{{--                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">--}}

{{--                    <input type="hidden" name="campoVaga" :value="controle.dados.campoVaga">--}}
{{--                    <input type="hidden" name="campoCliente" :value="controle.dados.campoCliente">--}}
{{--                    <input type="hidden" name="campoUf" :value="controle.dados.campoUf">--}}
{{--                    <input type="hidden" name="campoRh" :value="controle.dados.campoRh">--}}
{{--                    <input type="hidden" name="campoFinalRh" :value="controle.dados.campoFinalRh">--}}
{{--                    <input type="hidden" name="campoRota" :value="controle.dados.campoRota">--}}
{{--                    <input type="hidden" name="campoTecnica" :value="controle.dados.campoTecnica">--}}
{{--                    <input type="hidden" name="campoTeste" :value="controle.dados.campoTeste">--}}

{{--                    <input type="hidden" name="campoPcd" :value="controle.dados.campoPcd">--}}

{{--                    <button type="submit" class="btn btn-sm btn-primary mb-1"--}}
{{--                            :disabled="(selecionados.length === 0  && controle.dados.campoCliente === '' ||  lista.length===0 ) || controle.carregando">--}}
{{--                        <i class="fas fa-file-excel"></i> Exportar Excel <span class="badge badge-light"--}}
{{--                                                                               v-show="selecionados.length > 0">@{{ selecionados.length }}</span>--}}
{{--                    </button>--}}
{{--                </form>--}}
            </div>
        </div>

    </fieldset>
    <preload class="text-center" v-if="controle.carregando"></preload>
    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length===0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>
    <div id="conteudo">
        <table class="tabela" v-show="!controle.carregando && lista.length > 0">
            <thead>
            <tr class="bg-default">
                <th style="width: 30px;">
                    <input type="checkbox"
                           :checked="tudoMarcado"
                           :disabled="comRh.length === 0"
                           :content="comRh.length > 0 ? 'Selecionar todos' : 'Não possui cadastrodo no RH'"
                           v-tippy
                           style="cursor: pointer"
                           @change.prevent="selecionaTodos">
                </th>
{{--                <th>Cód</th>--}}
                <th>Nome</th>
{{--                <th v-if="cliente_id === 0 && colunasTabela.cliente">Empresa</th>--}}
                <th v-if="colunasTabela.pcd">PCD</th>
                <th>Vaga</th>
                <th>Entrevista</th>
                <th v-show="colunasTabela.rh_nota">Parecer RH Nota</th>
                <th v-show="colunasTabela.rota_transporte">Rota Transporte</th>
                <th v-show="colunasTabela.entrevista_tecnica">Entrevista Técnica Nota</th>
                <th v-show="colunasTabela.teste_pratico">Teste Prático Nota</th>
                <th v-show="colunasTabela.parecer_individual">Parecer Individual</th>
                <th v-show="colunasTabela.nota_individual">Nota Individual</th>
                <th>
                    <button class="btn btn-sm btn-primary mb-2" content="Mostrar e Ocultar Colunas" v-tippy
                            data-toggle="modal"
                            data-target="#filtroColunas">
                        <i class="bx bxs-filter-alt" aria-hidden="true"></i>
                    </button>
                </th>
            </tr>
            </thead>
            <tbody v-for="entrevista in lista">
            <tr style="background: white !important; border-bottom: none">
                <td class="text-center" style="width: 1em;">
                    <label :for="entrevista.id">
                        <input
                            type="checkbox"
                            v-model="selecionados"
                            :value="entrevista.id"
                            :id="entrevista.id"
                            :style="entrevista.parecer_rh ? 'cursor:pointer' : 'cursor: not-allowed'"
                            :content="entrevista.parecer_rh ? 'Selecionar' : 'Não possui parecer Cadastrado'"
                            v-tippy
                            v-if="entrevista.parecer_rh"
                        >
                        <input type="checkbox" v-else disabled="disabled" content="Sem parecer RH" v-tippy>

                    </label>
                </td>
{{--                <td class="text-center">--}}
{{--                    @{{entrevista.id}}--}}
{{--                </td>--}}
                <td class="text-center">
                    @{{entrevista.curriculo.nome}} <br>
{{--                    @{{entrevista.curriculo.cpf}}--}}
                </td>
{{--                <td class="text-center" v-if="cliente_id === 0  && colunasTabela.cliente">--}}
{{--                    @{{entrevista.cliente.razao_social}}--}}
{{--                </td>--}}
                <td class="text-center" v-show="colunasTabela.pcd">
                    @{{entrevista.curriculo.pcd ? 'Sim' : 'Não'}}
                </td>
                <td class="text-center">
                    @{{entrevista.vaga_aberta_municipio}}
                </td>

                <td class="text-center">
                    Data: @{{entrevista.data_entrevista}}<br>
                    Local: @{{entrevista.local_entrevista}}<br>
                </td>

                <td class="text-center" v-show="colunasTabela.rh_nota">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-show="colunasTabela.rota_transporte">
                   <span v-if="entrevista.parecer_rota && entrevista.parecer_rota.tem_rota">
                                Tem rota? @{{ entrevista.parecer_rota.tem_rota ? "Sim" : "Não" }} <br>
                                Data da entrevista: @{{ entrevista.parecer_rota.updated_at }}
                            </span>
                    <span v-else>
                        Aguardando
                    </span>
                </td>

                <td class="text-center" v-show="colunasTabela.entrevista_tecnica">
                    @{{entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-show="colunasTabela.teste_pratico">
                    @{{entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'}}
                </td>

                <td class="text-center" v-show="colunasTabela.parecer_individual">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.individual_rh ?
                    entrevista.parecer_rh.individual_rh.parecer : 'aguardando' : 'aguardando'}}
                </td>
                <td class="text-center" v-show="colunasTabela.nota_individual">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.individual_rh ?
                    entrevista.parecer_rh.individual_rh.nota : 'aguardando' : 'aguardando'}}
                </td>


                <td class="text-center">

                    <form :action="`${URL_ADMIN}/entrevistas/parecer_rh/ficha_pdf`" target="_blank" method="post">
                        <button class="btn btn-sm btn-primary mb-2" content="Entrevistar" v-tippy
                                v-show="!entrevista.parecer_rh"
                                @click.prevent="formEntrevistar(entrevista.id)"
                                data-toggle="modal"
                                data-target="#janelaParecerEntrevista">
                            <i class="far fa-list-alt"></i>
                        </button>

                        @can('entrevista_parecer_rh_update')
                            <button class="btn btn-sm btn-primary mb-2" content="Editar" v-tippy
                                    v-show="entrevista.parecer_rh"
                                    @click.prevent="formEntrevistar(entrevista.id); editando = true"
                                    data-toggle="modal"
                                    data-target="#janelaParecerEntrevista">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </button>
                        @endcan

                        <button class="btn btn-sm btn-primary mb-2" content="Visualizar" v-tippy
                                v-show="entrevista.parecer_rh"
                                @click.prevent="formEntrevistar(entrevista.id); visualizar = true"
                                data-toggle="modal"
                                data-target="#janelaParecerEntrevista">
                            <i class="fa fa-search-plus" aria-hidden="true"></i>
                        </button>

                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_rh.id"
                               v-if="entrevista.parecer_rh">
                        <button type="submit" content="Gerar PDF" v-tippy v-show="entrevista.parecer_rh"
                                class="btn btn-sm btn-primary mb-2">
                            <i class="fa fa-file-pdf" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{route('g.entrevista.parecer_rh.atualizar')}}"
                            :por-pagina="controle.dados.porPagina"
                            :dados="controle.dados"
                            v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/entrevistas/parecer_rh/app.js')}}"></script>
@endpush
