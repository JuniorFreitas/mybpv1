@extends('layouts.sistema')
@section('title', 'Entrevista - Teste Prático')
@section('content_header','Entrevista - Teste Prático')
@section('content')
    <modal id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template slot="conteudo">

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.pcd" @click="colunasTabela.pcd = !colunasTabela.pcd"
                       class="custom-control-input" id="pcd">
                <label class="custom-control-label"
                       for="pcd">PCD</label>
            </div>

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.parecer_rh"
                       @click="colunasTabela.parecer_rh = !colunasTabela.parecer_rh" class="custom-control-input"
                       id="parecer_rh">
                <label class="custom-control-label"
                       for="parecer_rh">PARECER RH NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.tecnica_nota"
                       @click="colunasTabela.tecnica_nota = !colunasTabela.tecnica_nota" class="custom-control-input"
                       id="tecnica_nota">
                <label class="custom-control-label"
                       for="tecnica_nota">ENTREVISTA TÉCNICA NOTA</label>
            </div>

            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" v-model="colunasTabela.teste_pratico_nota"
                       @click="colunasTabela.teste_pratico_nota = !colunasTabela.teste_pratico_nota"
                       class="custom-control-input" id="teste_pratico_nota">
                <label class="custom-control-label"
                       for="teste_pratico_nota">TESTE PRÁTICO NOTA</label>
            </div>
        </template>
    </modal>

    <modal id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preload">
        <template slot="conteudo">
            <preload v-if="preload"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''">
                <dados-pessoais :form="form"></dados-pessoais>
                <fieldset>
                    <legend class="text-uppercase">Informações</legend>

                    <div class="row">
                        <div class="col-12 col-sm-4">
                            <div class="form-group">
                                <label>Fez teste prático</label>
                                <select class="form-control" :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)" v-model="form.parecer_teste.fez_teste">
                                    <option value="">Selecione</option>
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <fieldset v-if="form.parecer_teste.fez_teste">
                        <legend class="text-uppercase">Sobre o Teste Prático</legend>

                        <div class="row">
                            <div class="col-12 col-sm-4">
                                <label>Data e Hora da Realização</label>
                                <datepicker label="" :hora="true" :disabled="visualizar" style="margin-top: -19px"
                                            v-model="form.parecer_teste.data_horario_realizacao"></datepicker>
                            </div>

                            <div class="col-12 col-sm-8">
                                <div class="form-group">
                                    <label>Responsável pelo Teste</label>
                                    <input type="text" :disabled="visualizar" class="form-control"
                                           onblur="valida_campo_vazio(this,1)"
                                           v-model="form.parecer_teste.responsavel_pelo_teste">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Qual o Teste foi aplicado</label>
                                    <input type="text" :disabled="visualizar" class="form-control"
                                           v-model="form.parecer_teste.qual_teste">
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label>Qual o Resultado do teste</label>
                                    <select class="form-control" :disabled="visualizar"
                                            onblur="valida_campo_vazio(this,1)"
                                            onchange="valida_campo_vazio(this,1)"
                                            v-model="form.parecer_teste.resultado_teste">
                                        <option value="">Selecione</option>
                                        <option value="1">Não atende ao desempenho esperado</option>
                                        <option value="2">Atende parcialmente</option>
                                        <option value="3">Atende ao esperado</option>
                                        <option value="4">Supera as expectativas</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </fieldset>


                </fieldset>
                <fieldset>
                    <legend class="text-uppercase">Parecer Final do Teste Prático</legend>
                    <div class="row">

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Parecer do Teste</label>
                                <select class="form-control" :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_teste.parecer_final_teste">
                                    <option value="">Selecione</option>
                                    <option value="favoravel">Favorável</option>
                                    <option value="restricao">Restrição</option>
                                    <option value="desfavoravel">Desfavorável</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Nota</label>
                                <select class="form-control" :disabled="visualizar"
                                        onblur="valida_campo_vazio(this,1)"
                                        onchange="valida_campo_vazio(this,1)"
                                        v-model="form.parecer_teste.nota_teste">
                                    <option value="">Selecione ...</option>
                                    <option value="0">Não se Aplica</option>
                                    @foreach(range(1,10) as $i)
                                        <option value="{{$i}}">{{$i}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label>Entrevistado Por:</label>
                                <input type="text" :disabled="visualizar" autocomplete="off" class="form-control"
                                       onblur="valida_campo_vazio(this,3)"
                                       v-model="form.parecer_teste.quem_entrevistou">
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
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
        <form @submit.prevent="$refs.componente.buscar()">
            <div class="row">
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

                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text"
                               placeholder="Buscar por nome"
                               autocomplete="off"
                               class="form-control form-control-sm" :disabled="controle.carregando"
                               v-model="controle.dados.campoBusca">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
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

                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="form-group">
                        <label>Vaga</label>
                        <autocomplete :caminho="controle.dados.caminho_autocomplete"
                                      :valido="controle.dados.campoVaga !== ''"
                                      v-model="controle.dados.autocomplete_label"
                                      :disabled="controle.carregando"
                                      placeholder="Por vaga"
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
                        <label for="">Rota</label>
                        <select class="form-control form-control-sm" @change="atualizar" :disabled="controle.carregando"
                                v-model="controle.dados.campoRota">
                            <option value="">Geral</option>
                            <option :value="true">Tem Rota</option>
                            <option :value="false">Não tem Rota</option>
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
                <button type="button" class="btn btn-sm btn-success mb-1 mr-1" :disabled="controle.carregando"
                        @click="atualizar"><i
                        :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i>
                    Atualizar
                </button>
                <button class="btn btn-sm btn-danger mb-1 mr-1"
                        :checked="tudoMarcado"
                        :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="selecionados.length === 0" @click="selecionados = []">
                    <i class="fa fa-times"></i> Limpar seleção
                </button>
                <form target="_blank"
                      {{--                      action="{{\App\Models\Sistema::UrlServidor}}/parecer_rota_transporte/export/3hmMaxB0QB0zvE48exportsBGQG3bheYiaQP1cWIqdhPL1lbv5g9tWBnBhRUDIJCRFM2gqbZSALev3zPcZVbHlZS"--}}
                      action="{{route('parecer_teste_pratico.excel')}}"
                      method="get">
                    @csrf
                    <input type="hidden" name="selecionados[]" v-for="item in selecionados" :value="item">
                    <input type="hidden" name="campoVaga" :value="controle.dados.campoVaga">
                    <input type="hidden" name="campoCliente" :value="controle.dados.campoCliente">
                    <input type="hidden" name="campoUf" :value="controle.dados.campoUf">
                    <input type="hidden" name="campoRota" :value="controle.dados.campoRota">
                    <input type="hidden" name="campoPcd" :value="controle.dados.campoPcd">
{{--                    <button type="submit" class="btn btn-sm btn-primary mb-1"--}}
{{--                            :disabled="(selecionados.length === 0  && controle.dados.campoCliente === '' ||  lista.length===0 ) || controle.carregando">--}}
{{--                        <i class="fas fa-file-excel"></i> Exportar Excel <span class="badge badge-light"--}}
{{--                                                                               v-show="selecionados.length > 0">@{{ selecionados.length }}</span>--}}
{{--                    </button>--}}
                </form>
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
                    <input type="checkbox"
                           :checked="tudoMarcado"
                           :disabled="comTeste.length === 0"
                           :content="comTeste.length > 0 ? 'Selecionar todos' : 'Não possui cadastrodo no RH'"
                           v-tippy
                           style="cursor: pointer"
                           @change.prevent="selecionaTodos">
                </th>
                <th class="text-center">ID</th>
                <th>Nome</th>
{{--                <th v-if="cliente_id === 0 && colunasTabela.cliente">Empresa</th>--}}
                <th class="text-center">Vaga</th>
                <th class="text-center" v-show="colunasTabela.pcd">PCD</th>
                <th class="text-center" v-show="colunasTabela.parecer_rh">Parecer RH Nota</th>
                <th class="text-center" v-show="colunasTabela.parecer_rota">Rota Transporte</th>
                <th class="text-center" v-show="colunasTabela.tecnica">Entrevista Técnica Nota</th>
                <th class="text-center">Teste Prático Nota</th>
                <th>
                    <button class="btn btn-sm btn-primary mb-2" content="Mostrar e Ocultar Colunas" v-tippy
                            data-toggle="modal"
                            data-target="#filtroColunas">
                        <i class="bx bxs-filter-alt" aria-hidden="true"></i>
                    </button>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="entrevista in lista">
                <td class="text-center">
                    <label :for="entrevista.id">
                        <input
                            type="checkbox"
                            v-model="selecionados"
                            :value="entrevista.id"
                            :id="entrevista.id"
                            :style="entrevista.parecer_teste ? 'cursor:pointer' : 'cursor: not-allowed'"
                            :title="entrevista.parecer_teste ? null : 'Não possui cadastro em rotas'"
                            v-if="entrevista.parecer_teste"
                        >
                        <input type="checkbox" v-else disabled="disabled" title="Sem parecer Rota">

                    </label>
                </td>
                <td class="text-center">
                    @{{entrevista.id}}
                </td>
                <td>
                    @{{entrevista.curriculo.nome}}
                    <br>
                    @{{entrevista.curriculo.cpf}}
                </td>
{{--                <td class="text-center" v-if="cliente_id === 0 && colunasTabela.cliente">--}}
{{--                    @{{entrevista.cliente.razao_social}}--}}
{{--                </td>--}}
                <td class="text-center">
                    @{{entrevista.vaga_selecionada.nome}}
                </td>
                <td class="text-center" v-show="colunasTabela.pcd">
                    @{{entrevista.curriculo.pcd ? 'Sim' : 'Não'}}
                </td>
                <td class="text-center" v-show="colunasTabela.parecer_rh">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-show="colunasTabela.parecer_rota">
                    @{{ entrevista.parecer_rota ? entrevista.parecer_rota.rota_atende != null ?
                    entrevista.parecer_rota.rota_atende === true ? 'Sim': 'Não' : 'Não Informado' : 'aguardando' }}
                    {{--                        @{{entrevista.parecer_rota ? entrevista.parecer_rota.TemRotaFormat : 'aguardando'}}--}}
                </td>

                <td class="text-center" v-show="colunasTabela.tecnica">
                    @{{entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'}}
                </td>

                <td class="text-center" >
                    @{{entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'}}

                </td>

                <td class="text-center">
                    <form :action="`${URL_ADMIN}/entrevistas/parecer-teste-pratico/ficha_pdf`" target="_blank"
                          method="post">
                        <button class="btn btn-sm btn-primary mb-2" content="Entrevistar" v-tippy
                                v-show="!entrevista.parecer_teste"
                                @click.prevent="formEntrevistar(entrevista.id)"
                                data-toggle="modal"
                                data-target="#janelaParecerEntrevista">
                            <i class="far fa-list-alt"></i>
                        </button>

                        @can('parecer_entrevista_update')
                            <button class="btn btn-sm btn-primary mb-2" content="Editar" v-tippy
                                    v-show="entrevista.parecer_teste"
                                    @click.prevent="formEntrevistar(entrevista.id); editando = true"
                                    data-toggle="modal"
                                    data-target="#janelaParecerEntrevista">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </button>
                        @endcan

                        <button class="btn btn-sm btn-primary mb-2" content="Visualizar" v-tippy
                                v-show="entrevista.parecer_teste"
                                @click.prevent="formEntrevistar(entrevista.id); visualizar = true"
                                data-toggle="modal"
                                data-target="#janelaParecerEntrevista">
                            <i class="fa fa-search-plus" aria-hidden="true"></i>
                        </button>

                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_teste.id"
                               v-if="entrevista.parecer_teste">
                        <button type="submit" content="Gerar PDF" v-tippy v-show="entrevista.parecer_teste"
                                class="btn btn-sm btn-primary mb-2">
                            <i class="fa fa-file-pdf" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
            </tr>
        </table>
    </div>

    <controle-paginacao class="d-flex justify-content-center" id="controle" ref="componente"
                        url="{{route('g.entrevista.parecer_teste_pratico.atualizar')}}"
                        :por-pagina="controle.dados.porPagina"
                        :dados="controle.dados"
                        v-on:carregou="carregou" v-on:carregando="carregando"></controle-paginacao>

@endsection
@push('js')
    <script src="{{mix('js/g/entrevistas/parecer_teste_pratico/app.js')}}"></script>
@endpush
