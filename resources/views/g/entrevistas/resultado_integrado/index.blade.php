@extends('layouts.sistema')
@section('title', 'Resultado Integrado')
@section('content_header', 'Resultado Integrado')
@section('content')

    <modal ref="filtroColunas" id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template #conteudo>

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.pcd" @click="colunasTabela.pcd = !colunasTabela.pcd"
                       class="custom-control-input" id="pcd">
                <label class="custom-control-label" for="pcd">PCD</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.rota_transporte"
                       @click="colunasTabela.rota_transporte = !colunasTabela.rota_transporte"
                       class="custom-control-input"
                       id="rota_transporte">
                <label class="custom-control-label" for="rota_transporte">ROTA TRANSPORTE</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.rh_nota"
                       @click="colunasTabela.rh_nota = !colunasTabela.rh_nota" class="custom-control-input"
                       id="rh_nota">
                <label class="custom-control-label" for="rh_nota">PARECER RH NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.entrevista_tecnica"
                       @click="colunasTabela.entrevista_tecnica = !colunasTabela.entrevista_tecnica"
                       class="custom-control-input" id="entrevista_tecnica">
                <label class="custom-control-label" for="entrevista_tecnica">ENTREVISTA TÉCNICA NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id === 1">
                <input type="checkbox" v-model="colunasTabela.teste_pratico"
                       @click="colunasTabela.teste_pratico = !colunasTabela.teste_pratico" class="custom-control-input"
                       id="teste_pratico">
                <label class="custom-control-label" for="teste_pratico">TESTE PRÁTICO</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id > 1">
                <input type="checkbox" v-model="colunasTabela.parecer_individual"
                       @click="colunasTabela.parecer_individual = !colunasTabela.parecer_individual"
                       class="custom-control-input" id="parecer_individual">
                <label class="custom-control-label" for="parecer_individual">PARECER INDIVIDUAL</label>
            </div>

            <div class="custom-control custom-switch mb-2" v-show="cliente_id === 0 || cliente_area_id > 1">
                <input type="checkbox" v-model="colunasTabela.nota_individual"
                       @click="colunasTabela.nota_individual = !colunasTabela.nota_individual"
                       class="custom-control-input"
                       id="nota_individual">
                <label class="custom-control-label" for="nota_individual">NOTA INDIVIDUAL</label>
            </div>
        </template>
    </modal>

    <modal ref="janelaParecerEntrevista" id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preloadForm">
        <template #conteudo>
            <preload v-if="preloadForm"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''">
                <form-rh :form="form" :cliente_id="cliente_id" :visualizar="true" :entrevistado-rh="false"
                         :entrevista-rh="false" disabled-parecer-rh :entrevista-gestor="false"
                         entrevista-gestor-disabled
                         entrevista-rh-disabled @finalizou="()=>{preloadForm = false}"></form-rh>

                <fieldset v-if="!preloadForm">
                    <legend class="text-uppercase">ENCAMINHAMENTO PARA ADMISSÃO</legend>
                    <form-resultado-integrado :form="form.resultado_integrado" :nome-candidato="form.curriculo ? form.curriculo.nome : 'Candidato'" :telefone-principal="form.tel_principal"></form-resultado-integrado>
                </fieldset>

            </div>
        </template>
        <template #rodape>
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado  && !preloadForm"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Alterar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !cadastrado  && !preloadForm"
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
                        <input type="text" placeholder="Buscar por nome" autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                    <div class="form-group">
                        <label>CPF</label>
                        <input type="text" placeholder="Buscar por cpf" autocomplete="mastertag"
                               onblur="valida_cpf(this)"
                               v-mascara:cpf class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoCPF">
                    </div>
                </div>


                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="form-group">
                        <label>Cargo</label>
                        <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                      :valido="controle.dados.campoVaga !== ''"
                                      v-model="controle.dados.autocomplete_label"
                                      :disabled="controle.carregando" placeholder="Por cargo" @onblur="resetaCampo"
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

                <div class="col-12 col-sm-4 col-md-3 ">
                    <label for="">Por classificação individual</label>
                    <div class="form-group">
                        <select class="form-control  form-control-sm" @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.parecer_individual">
                            <option value="">Sem filtro</option>
                            <option value="favoravel">Favorável</option>
                            <option value="destaque">Destaque</option>
                            {{-- <option value="stand_by">Stand By</option> --}}
                            {{-- <option value="desfavoravel">Desfavorável</option> --}}
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
                        <label for="">Por nota RH</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.entrevista_rh_nota">
                            <option value="">Sem filtro</option>
                            <option value="0">0</option>
                            <option value="1-5">1 à 5</option>
                            <option value="5-7">5 à 7</option>
                            <option value="8-10">8 à 10</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-4 col-md-3 ">
                    <label for="">Por classificação RH</label>
                    <div class="form-group">
                        <select class="form-control  form-control-sm" @change="atualizar"
                                :disabled="controle.carregando"
                                v-model="controle.dados.entrevista_rh">
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


                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                    <div class="form-group">
                        <label for="">Exibir</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
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
                <button type="button" class="btn btn-sm mr-1 btn-success mr-1 mb-1" :disabled="controle.carregando"
                        @click.prevent="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>

                <button class="btn btn-sm mr-1 btn-danger mb-1 mr-1"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0" @click.prevent="selecionados = []">
                    <i class="fa fa-times"></i> Limpar seleção
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary  mr-1 mb-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando|| preloadExportacao || (!controle.carregando && !lista.length) ">
                    <i class="fas fa-file-excel"></i> EXPORTAR EXCEL <span class="badge badge-light"
                                                                           v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
                </button>
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
                <th style="width: 1em;">
                    <input type="checkbox" :checked="tudoMarcado" :disabled="comResultado.length === 0"
                           :content="comResultado.length > 0 ? 'Selecionar todos' : 'Não possui cadastrodo no RH'"
                           v-tippy
                           style="cursor: pointer" @change.prevent="selecionaTodos">
                </th>
                {{-- <th>Cód</th> --}}
                <th>Nome</th>
                {{--                <th>Empresa</th>--}}
                <th v-if="colunasTabela.pcd">PCD</th>
                <th>Cargo</th>
                <th>Enc. Doc</th>
                <th>Enc. Exame</th>
                <th>Enc. Treinamento</th>
                <th>Resp. Encaminhamento</th>
                {{-- <th>Entrevista</th> --}}
                {{-- <th v-show="colunasTabela.rh_nota">Parecer RH Nota</th> --}}
                {{-- <th v-show="colunasTabela.rota_transporte">Rota Transporte</th> --}}
                {{-- <th v-show="colunasTabela.entrevista_tecnica">Entrevista Técnica Nota</th> --}}
                {{-- <th v-show="colunasTabela.teste_pratico">Teste Prático Nota</th> --}}
                {{-- <th v-show="colunasTabela.parecer_individual">Parecer Individual</th> --}}
                {{-- <th v-show="colunasTabela.nota_individual">Nota Individual</th> --}}
                <th>
                    <button class="btn btn-sm mr-1 btn-primary mb-2" content="Mostrar e Ocultar Colunas" v-tippy
                            @click="$refs.filtroColunas?.abrirModal()">
                        <i class="bx bxs-filter-alt" aria-hidden="true"></i>
                    </button>
                </th>
            </tr>
            </thead>
            <tbody v-for="entrevista in lista">
            <tr style="background: white !important; border-bottom: none">
                <td class="text-center" style="width: 1em;">
                    <label :for="entrevista.id">
                        <input type="checkbox" v-model="selecionados" :value="entrevista.id" :id="entrevista.id"
                               :style="entrevista.resultado_integrado ? 'cursor:pointer' : 'cursor: not-allowed'"
                               :content="entrevista.resultado_integrado ? 'Selecionar' : 'Não possui parecer Cadastrado'"
                               v-tippy v-if="entrevista.resultado_integrado">
                        <input type="checkbox" v-else disabled="disabled" content="Sem parecer RH" v-tippy>

                    </label>
                </td>
                {{-- <td class="text-center"> --}}
                {{-- @{{entrevista.id}} --}}
                {{-- </td> --}}
                <td class="text-center">
                    @{{ entrevista . curriculo . nome }}
                </td>
                {{--                <td class="text-center" v-if="cliente_id === 0  && colunasTabela.cliente">--}}
                {{--                    @{{ entrevista . cliente . razao_social }}--}}
                {{--                </td>--}}
                <td class="text-center" v-show="colunasTabela.pcd">
                    @{{ entrevista . curriculo . pcd ? 'Sim' : 'Não' }}
                </td>
                <td class="text-center">
                    @{{ entrevista . vaga_aberta_municipio }}
                </td>

                <td class="text-center">
                        <span v-if="entrevista.resultado_integrado">
                            @{{ entrevista . resultado_integrado . documentos_entregue ? 'Sim' : 'Não' }} <br>
                            @{{ entrevista . resultado_integrado . documentos_entregue_data }} <br>
                        </span>
                    <span v-else>---</span>
                </td>

                <td class="text-center">
                        <span v-if="entrevista.resultado_integrado">
                            @{{ entrevista . resultado_integrado . encaminhado_exame ? 'Sim' : 'Não' }} <br>
                            @{{ entrevista . resultado_integrado . encaminhado_exame_data }} <br>
                        </span>
                    <span v-else>---</span>
                </td>

                <td class="text-center">
                        <span v-if="entrevista.resultado_integrado">
                            @{{ entrevista . resultado_integrado . encaminhado_treinamento ? 'Sim' : 'Não' }} <br>
                            @{{ entrevista . resultado_integrado . encaminhado_treinamento_data }} <br>
                        </span>
                    <span v-else>---</span>
                </td>

                <td class="text-center">
                        <span v-if="entrevista.resultado_integrado">
                            @{{ entrevista . resultado_integrado . responsavel_envio }}
                        </span>
                    <span v-else>---</span>
                </td>

                <!--                <td class="text-center">
                                                                                        Data: @{{ entrevista . data_entrevista }}<br>
                                                                                        Local: @{{ entrevista . local_entrevista }}<br>
                                                                                    </td>

                                                                                    <td class="text-center" v-show="colunasTabela.rh_nota">
                                                                                        @{{ entrevista . parecer_rh ? entrevista . parecer_rh . nota : 'aguardando' }}
                                                                                    </td>

                                                                                    <td class="text-center" v-show="colunasTabela.rota_transporte">
                                                                                         <span v-if="entrevista.parecer_rota && entrevista.parecer_rota.tem_rota">
                                                                                                    Tem rota? @{{ entrevista . parecer_rota . tem_rota ? 'Sim' : 'Não' }} <br>
                                                                                                    Data da entrevista: @{{ entrevista . parecer_rota . updated_at }}
                                                                                                </span>
                                                                                        <span v-else>
                                                                                            Aguardando
                                                                                        </span>
                                                                                    </td>

                                                                                    <td class="text-center" v-show="colunasTabela.entrevista_tecnica">
                                                                                        @{{ entrevista . parecer_tecnica ? entrevista . parecer_tecnica . nota : 'aguardando' }}
                                                                                    </td>

                                                                                    <td class="text-center" v-show="colunasTabela.teste_pratico">
                                                                                        @{{ entrevista . parecer_teste ? entrevista . parecer_teste . NotaTesteFormat : 'aguardando' }}
                                                                                    </td>

                                                                                    <td class="text-center" v-show="colunasTabela.parecer_individual">
                                                                                        @{{ entrevista . parecer_rh ? (entrevista . parecer_rh . individual_rh ? entrevista . parecer_rh . individual_rh . parecer : 'aguardando') : 'aguardando' }}
                                                                                    </td>
                                                                                    <td class="text-center" v-show="colunasTabela.nota_individual">
                                                                                        @{{ entrevista . parecer_rh ? (entrevista . parecer_rh . individual_rh ? entrevista . parecer_rh . individual_rh . nota : 'aguardando') : 'aguardando' }}
                                                                                    </td>-->


                <td class="text-center">

                    <form :action="`${URL_ADMIN}/entrevistas/resultado-integrado/ficha/${entrevista.id}`"
                          target="_blank" method="get">
                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Encaminhar" v-tippy
                                v-show="!entrevista.resultado_integrado" @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="far fa-share-square"></i>
                        </button>

                        @can('entrevista_resultado_integrado_update')
                            <button class="btn btn-sm mr-1 btn-primary mb-2" content="Editar" v-tippy
                                    v-show="entrevista.resultado_integrado"
                                    @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </button>
                        @endcan

                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Visualizar" v-tippy
                                v-show="entrevista.resultado_integrado"
                                @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="fa fa-search-plus" aria-hidden="true"></i>
                        </button>

                        @csrf
                        <input type="hidden" name="id" :value="entrevista.curriculo_id">
                        <button v-if="entrevista.resultado_integrado" type="submit" content="Imprimir" v-tippy
                                class="btn btn-sm mr-1 btn-primary mb-2">
                            <i class="fa fa-file-pdf" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
        <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                            url="{{ route('g.entrevista.resultado-integrado.resultado_integrado.atualizar') }}"
                            :por-pagina="controle.dados.porPagina" :dados="controle.dados" @carregou="carregou"
                            @carregando="carregando">
        </controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{ mix('js/g/entrevistas/resultado_integrado/app.js') }}"></script>
@endpush
