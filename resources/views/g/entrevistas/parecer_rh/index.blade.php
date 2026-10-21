@extends('layouts.sistema')
@section('title', 'Entrevista - Parecer RH')
@section('content_header','Entrevista - Parecer RH')
@section('content')
    <modal ref="filtroColunas" id="filtroColunas" titulo="Mostrar e Ocultar colunas">
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

    <modal ref="janelaParecerEntrevista" id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preloadForm">
        <template #conteudo>
            <preload v-if="preloadForm"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''" class="mybp-modal-form mybp-filtros-compactos">
                <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda" v-show="!visualizar">
                    Campos com <span class="text-danger">*</span> são obrigatórios.
                </p>
                <form-rh ref="formRh"
                         :form="form"
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

    <filtro-entrevista-compacto
        :dados="controle.dados"
        :carregando="controle.carregando"
        :abertos="filtrosAvancadosAbertos"
        :tem-filtros="temFiltrosEntrevista"
        :total-avancados="totalFiltrosAvancadosEntrevista"
        id-prefix="parecer-rh"
        label-cargo="Cargo"
        @update:abertos="filtrosAvancadosAbertos = $event"
        @submit="atualizar"
        @limpar="limparFiltrosEntrevista"
        @periodo="onPeriodoEntrevista"
        @select-vaga="selecionaVaga"
        @blur-vaga="resetaCampo"
    >
        <template #avancados>

                <combo-filtro-entrevista
                    ref="comboFiltroUf"
                    input-id="parecer-rh-uf"
                    label="Estado"
                    placeholder="Todos os estados"
                    v-model="controle.dados.campoUf"
                    :options="opcoesUfFiltro"
                    :disabled="controle.carregando"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroNota"
                    input-id="parecer-rh-nota"
                    label="Nota RH"
                    placeholder="Geral"
                    v-model="controle.dados.campoRh"
                    :options="opcoesNotaRhUmADez"
                    :disabled="controle.carregando"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroFinal"
                    input-id="parecer-rh-final"
                    label="Final RH"
                    placeholder="Todos"
                    v-model="controle.dados.campoFinalRh"
                    :options="opcoesFinalRh"
                    :disabled="controle.carregando"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroRota"
                    input-id="parecer-rh-rota"
                    label="Rota"
                    placeholder="Geral"
                    v-model="controle.dados.campoRota"
                    :options="opcoesRotaBooleana"
                    :disabled="controle.carregando"
                    :visivel="industria"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroTecnica"
                    input-id="parecer-rh-tecnica"
                    label="Técnica"
                    placeholder="Geral"
                    v-model="controle.dados.campoTecnica"
                    :options="opcoesTecnicaFiltro"
                    :disabled="controle.carregando"
                    :visivel="industria"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroTeste"
                    input-id="parecer-rh-teste"
                    label="Teste"
                    placeholder="Geral"
                    v-model="controle.dados.campoTeste"
                    :options="opcoesTesteFiltro"
                    :disabled="controle.carregando"
                    :visivel="industria"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroClass"
                    input-id="parecer-rh-class"
                    label="Classificação"
                    placeholder="Sem filtro"
                    v-model="controle.dados.parecer_individual"
                    :options="opcoesClassRh"
                    :disabled="controle.carregando"
                    :visivel="servico"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroNotaInd"
                    input-id="parecer-rh-nota-ind"
                    label="Nota individual"
                    placeholder="Sem filtro"
                    v-model="controle.dados.campoRh"
                    :options="opcoesNotaFaixa"
                    :disabled="controle.carregando"
                    :visivel="servico"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroPages"
                    input-id="parecer-rh-pages"
                    label="Exibir"
                    placeholder="20"
                    v-model="controle.dados.pages"
                    :options="opcoesPagesFiltro"
                    :disabled="controle.carregando"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>
        </template>
        <template #acoes>

            <button type="button" class="btn btn-sm btn-outline-secondary mybp-btn-acao-compact"
                    v-show="selecionados.length" @click.prevent="selecionados = []">
                <i class="fa fa-times"></i> Limpar seleção
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary mybp-btn-acao-compact"
                    @click.prevent="exportaExcel()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && lista.length===0 && selecionados.length === 0)">
                <i class="fas fa-file-excel"></i> Exportar Excel
                <span class="badge badge-light" v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
            </button>

        </template>
    </filtro-entrevista-compacto>
    @include('g.entrevistas.partials.barra_visualizacao', ['tela' => 'entrevista-parecer-rh', 'colunas' => true])

    <preload class="text-center" v-if="controle.carregando"></preload>
    <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length===0">
        <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
    </div>
    <div id="conteudo">
        
        <div class="mybp-cards-lista" v-if="!controle.carregando && lista.length > 0 && visualizacao === 'lista'">
            <cartao-entrevista-lista
                v-for="entrevista in lista"
                :key="'lista-' + entrevista.id"
                :entrevista="entrevista"
                :aberto="isDropdownOpen('lista-' + entrevista.id)"
                @toggle="toggleDropdown('lista-' + entrevista.id)"
                @fechar="fecharDropdown"
            >
                <template #selecao>
                    <label class="mb-0 mr-1" :for="'sel-lista-' + entrevista.id" v-if="entrevista.parecer_rh" style="cursor: pointer;">
                        <input type="checkbox" :id="'sel-lista-' + entrevista.id" v-model="selecionados" :value="entrevista.id" />
                    </label>
                    <span class="mb-0 mr-1" v-else>
                        <input type="checkbox" disabled />
                    </span>
                </template>
                <template #acoes>
                    <a class="dropdown-item" href="javascript://" v-show="!entrevista.parecer_rh"
                       @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()">
                        <i class="far fa-list-alt mr-1"></i> Entrevistar
                    </a>
                    @can('entrevista_parecer_rh_update')
                    <a class="dropdown-item" href="javascript://" v-show="entrevista.parecer_rh"
                       @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()">
                        <i class="fa fa-edit mr-1"></i> Editar
                    </a>
                    @endcan
                    <a class="dropdown-item" href="javascript://" v-show="entrevista.parecer_rh"
                       @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()">
                        <i class="fa fa-search-plus mr-1"></i> Visualizar
                    </a>
                    <form :action="`${URL_ADMIN}/entrevistas/parecer_rh/ficha_pdf`" target="_blank" method="post" v-if="entrevista.parecer_rh">
                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_rh.id">
                        <button type="submit" class="dropdown-item">
                            <i class="fa fa-file-pdf mr-1"></i> Gerar PDF
                        </button>
                    </form>
                </template>
                <template #campos>
                    <mybp-card-campo icon="fas fa-briefcase" label="Vaga" :valor="entrevista.vaga_aberta_municipio" forte></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-calendar-alt" label="Entrevista" :valor="(entrevista.data_entrevista ? ('Data: ' + entrevista.data_entrevista + (entrevista.local_entrevista ? ' · ' + entrevista.local_entrevista : '')) : '')"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-wheelchair" label="PCD" :valor="entrevista.curriculo && entrevista.curriculo.pcd ? 'Sim' : 'Não'" v-if="colunasTabela.pcd"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-star" label="Parecer RH" :valor="entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'" v-if="colunasTabela.rh_nota"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-route" label="Rota" :valor="(entrevista.parecer_rota && entrevista.parecer_rota.tem_rota ? ('Tem rota? ' + (entrevista.parecer_rota.tem_rota ? 'Sim' : 'Não')) : 'Aguardando')" v-if="colunasTabela.rota_transporte"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-user-tie" label="Ent. técnica" :valor="entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'" v-if="colunasTabela.entrevista_tecnica"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-tools" label="Teste prático" :valor="entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'" v-if="colunasTabela.teste_pratico"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-clipboard-check" label="Parecer individual" :valor="(entrevista.parecer_rh && entrevista.parecer_rh.individual_rh ? entrevista.parecer_rh.individual_rh.parecer : 'aguardando')" v-if="colunasTabela.parecer_individual"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-sort-numeric-up" label="Nota individual" :valor="(entrevista.parecer_rh && entrevista.parecer_rh.individual_rh ? entrevista.parecer_rh.individual_rh.nota : 'aguardando')" v-if="colunasTabela.nota_individual"></mybp-card-campo>
                </template>
            </cartao-entrevista-lista>
        </div>

        <div class="table-responsive mybp-tabela-wrap" v-if="!controle.carregando && lista.length > 0 && visualizacao === 'tabela'">
        <table class="tabela mybp-tabela">
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
                <th>CPF</th>
{{--                <th v-if="cliente_id === 0 && colunasTabela.cliente">Empresa</th>--}}
                <th v-if="colunasTabela.pcd">PCD</th>
                <th>Vaga</th>
                <th>Entrevista</th>
                <th v-if="colunasTabela.rh_nota">Parecer RH Nota</th>
                <th v-if="colunasTabela.rota_transporte">Rota Transporte</th>
                <th v-if="colunasTabela.entrevista_tecnica">Entrevista Técnica Nota</th>
                <th v-if="colunasTabela.teste_pratico">Teste Prático Nota</th>
                <th v-if="colunasTabela.parecer_individual">Parecer Individual</th>
                <th v-if="colunasTabela.nota_individual">Nota Individual</th>
                <th class="text-center">Ações</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="entrevista in lista" :key="entrevista.id">
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
                <td class="mybp-col-texto">@{{ entrevista.curriculo.nome }}</td>
                <td>@{{ entrevista.curriculo && entrevista.curriculo.cpf ? entrevista.curriculo.cpf : 'Não informado' }}</td>
{{--                <td class="text-center" v-if="cliente_id === 0  && colunasTabela.cliente">--}}
{{--                    @{{entrevista.cliente.razao_social}}--}}
{{--                </td>--}}
                <td class="text-center" v-if="colunasTabela.pcd">
                    @{{entrevista.curriculo.pcd ? 'Sim' : 'Não'}}
                </td>
                <td class="text-center">
                    @{{entrevista.vaga_aberta_municipio}}
                </td>

                <td class="text-center">
                    Data: @{{entrevista.data_entrevista}}<br>
                    Local: @{{entrevista.local_entrevista}}<br>
                </td>

                <td class="text-center" v-if="colunasTabela.rh_nota">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-if="colunasTabela.rota_transporte">
                   <span v-if="entrevista.parecer_rota && entrevista.parecer_rota.tem_rota">
                                Tem rota? @{{ entrevista.parecer_rota.tem_rota ? "Sim" : "Não" }} <br>
                                Data da entrevista: @{{ entrevista.parecer_rota.updated_at }}
                            </span>
                    <span v-else>
                        Aguardando
                    </span>
                </td>

                <td class="text-center" v-if="colunasTabela.entrevista_tecnica">
                    @{{entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-if="colunasTabela.teste_pratico">
                    @{{entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'}}
                </td>

                <td class="text-center" v-if="colunasTabela.parecer_individual">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.individual_rh ?
                    entrevista.parecer_rh.individual_rh.parecer : 'aguardando' : 'aguardando'}}
                </td>
                <td class="text-center" v-if="colunasTabela.nota_individual">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.individual_rh ?
                    entrevista.parecer_rh.individual_rh.nota : 'aguardando' : 'aguardando'}}
                </td>


                <td class="text-center">

                    <form :action="`${URL_ADMIN}/entrevistas/parecer_rh/ficha_pdf`" target="_blank" method="post">
                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Entrevistar" v-tippy
                                v-show="!entrevista.parecer_rh"
                                @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="far fa-list-alt"></i>
                        </button>

                        @can('entrevista_parecer_rh_update')
                            <button class="btn btn-sm mr-1 btn-primary mb-2" content="Editar" v-tippy
                                    v-show="entrevista.parecer_rh"
                                    @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </button>
                        @endcan

                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Visualizar" v-tippy
                                v-show="entrevista.parecer_rh"
                                @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="fa fa-search-plus" aria-hidden="true"></i>
                        </button>

                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_rh.id"
                               v-if="entrevista.parecer_rh">
                        <button type="submit" content="Gerar PDF" v-tippy v-show="entrevista.parecer_rh"
                                class="btn btn-sm mr-1 btn-primary mb-2">
                            <i class="fa fa-file-pdf" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
        </div>
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
