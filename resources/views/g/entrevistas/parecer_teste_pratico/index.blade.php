@extends('layouts.sistema')
@section('title', 'Entrevista - Teste Prático')
@section('content_header','Entrevista - Teste Prático')
@section('content')
    <modal ref="filtroColunas" id="filtroColunas" titulo="Mostrar e Ocultar colunas">
        <template #conteudo>

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

    <modal ref="janelaParecerEntrevista" id="janelaParecerEntrevista" :titulo="tituloJanela" :size="80" :fechar="!preload">
        <template #conteudo>
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
        <template #rodape>
            <div v-show="!visualizar">
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="editando && !atualizado  && !preload"
                        @click.prevent="alterar">
                    <i class="fa fa-edit"></i> Alterar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary"
                        v-show="!editando && !cadastrado  && !preload"
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
        id-prefix="teste-pratico"
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
                    input-id="teste-pratico-uf"
                    label="Estado"
                    placeholder="Todos os estados"
                    v-model="controle.dados.campoUf"
                    :options="opcoesUfFiltro"
                    :disabled="controle.carregando"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroRota"
                    input-id="teste-pratico-rota"
                    label="Rota"
                    placeholder="Geral"
                    v-model="controle.dados.campoRota"
                    :options="opcoesRotaBooleana"
                    :disabled="controle.carregando"
                    @opening="fecharCombosEntrevista"
                    @select="atualizar"
                ></combo-filtro-entrevista>

                <combo-filtro-entrevista
                    ref="comboFiltroPages"
                    input-id="teste-pratico-pages"
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
    @include('g.entrevistas.partials.barra_visualizacao', ['tela' => 'entrevista-teste-pratico', 'colunas' => true])

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
                    <label class="mb-0 mr-1" :for="'sel-lista-' + entrevista.id" v-if="entrevista.parecer_teste" style="cursor: pointer;">
                        <input type="checkbox" :id="'sel-lista-' + entrevista.id" v-model="selecionados" :value="entrevista.id" />
                    </label>
                    <span class="mb-0 mr-1" v-else>
                        <input type="checkbox" disabled />
                    </span>
                </template>
                <template #acoes>
                    <a class="dropdown-item" href="javascript://" v-show="!entrevista.parecer_teste"
                       @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()">
                        <i class="far fa-list-alt mr-1"></i> Entrevistar
                    </a>
                    @can('entrevista_parecer_entrevista_update')
                    <a class="dropdown-item" href="javascript://" v-show="entrevista.parecer_teste"
                       @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()">
                        <i class="fa fa-edit mr-1"></i> Editar
                    </a>
                    @endcan
                    <a class="dropdown-item" href="javascript://" v-show="entrevista.parecer_teste"
                       @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()">
                        <i class="fa fa-search-plus mr-1"></i> Visualizar
                    </a>
                    <form :action="`${URL_ADMIN}/entrevistas/parecer-teste-pratico/ficha_pdf`" target="_blank" method="post" v-if="entrevista.parecer_teste">
                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_teste.id">
                        <button type="submit" class="dropdown-item">
                            <i class="fa fa-file-pdf mr-1"></i> Gerar PDF
                        </button>
                    </form>
                </template>
                <template #campos>
                    <mybp-card-campo icon="fas fa-briefcase" label="Vaga" :valor="entrevista.vaga_aberta_municipio" forte></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-wheelchair" label="PCD" :valor="entrevista.curriculo && entrevista.curriculo.pcd ? 'Sim' : 'Não'" v-if="colunasTabela.pcd"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-star" label="Parecer RH" :valor="entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'" v-if="colunasTabela.parecer_rh"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-route" label="Rota" :valor="entrevista.parecer_rota ? (entrevista.parecer_rota.rota_atende != null ? (entrevista.parecer_rota.rota_atende === true ? 'Sim' : 'Não') : 'Não informado') : 'aguardando'" v-if="colunasTabela.parecer_rota"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-user-tie" label="Ent. técnica" :valor="entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'" v-if="colunasTabela.tecnica"></mybp-card-campo>
                    <mybp-card-campo icon="fas fa-tools" label="Teste prático" :valor="entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'" forte></mybp-card-campo>
                </template>
            </cartao-entrevista-lista>
        </div>

        <div class="table-responsive mybp-tabela-wrap" v-if="!controle.carregando && lista.length > 0 && visualizacao === 'tabela'">
        <table class="tabela mybp-tabela">
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
                <th>CPF</th>
{{--                <th v-if="cliente_id === 0 && colunasTabela.cliente">Empresa</th>--}}
                <th class="text-center">Vaga</th>
                <th class="text-center" v-if="colunasTabela.pcd">PCD</th>
                <th class="text-center" v-if="colunasTabela.parecer_rh">Parecer RH Nota</th>
                <th class="text-center" v-if="colunasTabela.parecer_rota">Rota Transporte</th>
                <th class="text-center" v-if="colunasTabela.tecnica">Entrevista Técnica Nota</th>
                <th class="text-center">Teste Prático Nota</th>
                <th class="text-center">Ações</th>
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
                <td class="mybp-col-texto">@{{ entrevista.curriculo.nome }}</td>
                <td>@{{ entrevista.curriculo && entrevista.curriculo.cpf ? entrevista.curriculo.cpf : 'Não informado' }}</td>
{{--                <td class="text-center" v-if="cliente_id === 0 && colunasTabela.cliente">--}}
{{--                    @{{entrevista.cliente.razao_social}}--}}
{{--                </td>--}}
                <td class="text-center">
                    @{{entrevista.vaga_aberta_municipio}}
                </td>
                <td class="text-center" v-if="colunasTabela.pcd">
                    @{{entrevista.curriculo.pcd ? 'Sim' : 'Não'}}
                </td>
                <td class="text-center" v-if="colunasTabela.parecer_rh">
                    @{{entrevista.parecer_rh ? entrevista.parecer_rh.nota : 'aguardando'}}
                </td>

                <td class="text-center" v-if="colunasTabela.parecer_rota">
                    @{{ entrevista.parecer_rota ? entrevista.parecer_rota.rota_atende != null ?
                    entrevista.parecer_rota.rota_atende === true ? 'Sim': 'Não' : 'Não Informado' : 'aguardando' }}
                    {{--                        @{{entrevista.parecer_rota ? entrevista.parecer_rota.TemRotaFormat : 'aguardando'}}--}}
                </td>

                <td class="text-center" v-if="colunasTabela.tecnica">
                    @{{entrevista.parecer_tecnica ? entrevista.parecer_tecnica.nota : 'aguardando'}}
                </td>

                <td class="text-center" >
                    @{{entrevista.parecer_teste ? entrevista.parecer_teste.NotaTesteFormat : 'aguardando'}}

                </td>

                <td class="text-center">
                    <form :action="`${URL_ADMIN}/entrevistas/parecer-teste-pratico/ficha_pdf`" target="_blank"
                          method="post">
                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Entrevistar" v-tippy
                                v-show="!entrevista.parecer_teste"
                                @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="far fa-list-alt"></i>
                        </button>

                        @can('entrevista_parecer_entrevista_update')
                            <button class="btn btn-sm mr-1 btn-primary mb-2" content="Editar" v-tippy
                                    v-show="entrevista.parecer_teste"
                                    @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </button>
                        @endcan

                        <button class="btn btn-sm mr-1 btn-primary mb-2" content="Visualizar" v-tippy
                                v-show="entrevista.parecer_teste"
                                @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()">
                            <i class="fa fa-search-plus" aria-hidden="true"></i>
                        </button>

                        @csrf
                        <input type="hidden" name="id" :value="entrevista.parecer_teste.id"
                               v-if="entrevista.parecer_teste">
                        <button type="submit" content="Gerar PDF" v-tippy v-show="entrevista.parecer_teste"
                                class="btn btn-sm mr-1 btn-primary mb-2">
                            <i class="fa fa-file-pdf" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
            </tr>
        </table>
        </div>
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
