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

    <modal ref="janelaParecerEntrevista" id="janelaParecerEntrevista" :titulo="tituloJanela" :size="90" :fechar="!preloadForm">
        <template #conteudo>
            <preload v-if="preloadForm"></preload>
            <div v-if="!preload && (!cadastrado && !atualizado) && form.id !== ''" class="mybp-modal-form mybp-filtros-compactos">
                <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda" v-show="!visualizar">
                    Campos com <span class="text-danger">*</span> são obrigatórios.
                </p>
                <form-rh :form="form" :cliente_id="cliente_id" :visualizar="true" :entrevistado-rh="false"
                         :entrevista-rh="false" disabled-parecer-rh :entrevista-gestor="false"
                         entrevista-gestor-disabled
                         entrevista-rh-disabled @finalizou="()=>{preloadForm = false}"></form-rh>

                <form-resultado-integrado
                    v-if="!preloadForm"
                    ref="formResultadoIntegrado"
                    :form="form.resultado_integrado"
                    :visualizar="visualizar"
                    :disabled="visualizar"
                    :nome-candidato="form.curriculo ? form.curriculo.nome : 'Candidato'"
                    :telefone-principal="form.tel_principal"></form-resultado-integrado>
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

    <filtro-listagem
        class="mt-2 mybp-filtros-compactos"
        :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
        :desabilitado="controle.carregando"
        @submit="atualizar"
        @limpar="limparFiltros"
    >
        <template #filtros>
            <date-range-filter
                :key="'ri-filtro-periodo'"
                v-model:enabled="controle.dados.filtroPeriodo"
                v-model:start-date="controle.dados.dataInicio"
                v-model:end-date="controle.dados.dataFim"
                :disabled="!!controle.carregando"
                :id-suffix="'periodo-' + hash"
                label="Por período"
                wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                @change="onPeriodoChange"
            ></date-range-filter>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="ri-filtro-busca">
                        Candidato / CPF
                        <span v-if="buscaUnificadaEhCpf" class="text-muted small">CPF</span>
                    </label>
                    <input
                        id="ri-filtro-busca"
                        type="text"
                        placeholder="Nome ou CPF"
                        autocomplete="off"
                        inputmode="search"
                        class="form-control form-control-sm"
                        :disabled="controle.carregando"
                        :value="campoBuscaUnificada"
                        @input="onInputBuscaUnificada"
                    />
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label">Cargo</label>
                    <autocomplete
                        :caminho="controle.dados.caminho_autocomplete"
                        :valido="controle.dados.campoVaga !== ''"
                        :formsm="true"
                        v-model="controle.dados.autocomplete_label"
                        :disabled="controle.carregando"
                        placeholder="Por cargo"
                        @onblur="resetaCampo"
                        @onselect="selecionaVaga"
                    ></autocomplete>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="ri-filtro-uf">Estado</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroUf"
                            instance-id="ri-filtro-uf"
                            input-id="ri-filtro-uf"
                            v-model="controle.dados.campoUf"
                            :options="filtroUfOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Todos os estados"
                            empty-message="Nenhum estado encontrado."
                            :max-results="30"
                            @opening="fecharOutrosComboboxes('ri-filtro-uf')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="ri-filtro-class-ind">Classificação individual</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroClassInd"
                            instance-id="ri-filtro-class-ind"
                            input-id="ri-filtro-class-ind"
                            v-model="controle.dados.parecer_individual"
                            :options="filtroClassIndividualOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Sem filtro"
                            empty-message="Nenhuma opção."
                            :max-results="20"
                            @opening="fecharOutrosComboboxes('ri-filtro-class-ind')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group mybp-filtro-campo">
                    <label class="mybp-label" for="ri-filtro-class-rh">Classificação RH</label>
                    <div class="mybp-combobox-wrap">
                        <combobox-auto-complete
                            ref="comboFiltroClassRh"
                            instance-id="ri-filtro-class-rh"
                            input-id="ri-filtro-class-rh"
                            v-model="controle.dados.entrevista_rh"
                            :options="filtroClassRhOpcoes"
                            :disabled="controle.carregando"
                            placeholder-blur="Sem filtro"
                            empty-message="Nenhuma opção."
                            :max-results="20"
                            @opening="fecharOutrosComboboxes('ri-filtro-class-rh')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </div>

            <div
                class="col-12 mybp-filtros-avancados-shell"
                :class="filtrosAvancadosAbertos ? 'is-open' : ''"
                :aria-hidden="filtrosAvancadosAbertos ? 'false' : 'true'"
            >
                <div class="mybp-filtros-avancados-shell__inner">
                    <div class="mybp-filtros-avancados">
                        <div class="mybp-filtros-avancados__block">
                            <p class="mybp-filtros-avancados__title">Outros filtros</p>
                            <div class="row">
                                <div class="col-12 col-md-4" v-if="servico">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="ri-filtro-nota-ind">Nota individual</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroNotaInd"
                                                instance-id="ri-filtro-nota-ind"
                                                input-id="ri-filtro-nota-ind"
                                                v-model="controle.dados.campoRh"
                                                :options="filtroNotaOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="Sem filtro"
                                                empty-message="Nenhuma opção."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('ri-filtro-nota-ind')"
                                                @select="onSelectFiltro"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="ri-filtro-nota-rh">Nota RH</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroNotaRh"
                                                instance-id="ri-filtro-nota-rh"
                                                input-id="ri-filtro-nota-rh"
                                                v-model="controle.dados.entrevista_rh_nota"
                                                :options="filtroNotaOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="Sem filtro"
                                                empty-message="Nenhuma opção."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('ri-filtro-nota-rh')"
                                                @select="onSelectFiltro"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" for="ri-filtro-pages">Exibir</label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                ref="comboFiltroPages"
                                                instance-id="ri-filtro-pages"
                                                input-id="ri-filtro-pages"
                                                v-model="campoPagesCombo"
                                                :options="filtroPagesOpcoes"
                                                :disabled="controle.carregando"
                                                placeholder-blur="20"
                                                empty-message="Nenhuma opção."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('ri-filtro-pages')"
                                                @select="onSelectFiltro"
                                            ></combobox-auto-complete>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #acoes>
            <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                Buscar
            </button>
            <button
                type="button"
                class="btn btn-sm mybp-btn-mais-filtros"
                :class="filtrosAvancadosAbertos ? 'btn-primary is-open' : 'btn-outline-primary'"
                :aria-expanded="filtrosAvancadosAbertos ? 'true' : 'false'"
                @click="filtrosAvancadosAbertos = !filtrosAvancadosAbertos"
            >
                <i class="fa" :class="filtrosAvancadosAbertos ? 'fa-chevron-up' : 'fa-sliders-h'"></i>
                @{{ filtrosAvancadosAbertos ? 'Menos filtros' : 'Mais filtros' }}
                <span class="badge badge-light ml-1" v-if="totalFiltrosAtivosAvancados">
                    @{{ totalFiltrosAtivosAvancados }}
                </span>
            </button>
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary mybp-btn-acao-compact"
                :style="selecionados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                :disabled="selecionados.length === 0"
                @click.prevent="selecionados = []"
            >
                <i class="fa fa-times"></i> Limpar seleção
            </button>
            <button
                type="button"
                class="btn btn-sm btn-success mybp-btn-acao-compact"
                @click.prevent="exportaExcel()"
                :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
            >
                <i class="fas fa-file-excel"></i> Exportar Excel
                <span class="badge badge-light" v-show="selecionados.length > 0">@{{ selecionados.length }}</span>
            </button>
        </template>
    </filtro-listagem>

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
                            :por-pagina="controle.dados.pages" :dados="controle.dados" @carregou="carregou"
                            @carregando="carregando">
        </controle-paginacao>
    </div>
@stop
@push('js')
    <script src="{{ mix('js/g/entrevistas/resultado_integrado/app.js') }}"></script>
@endpush
