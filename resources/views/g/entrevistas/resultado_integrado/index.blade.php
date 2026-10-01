@extends('layouts.sistema')
@section('title', 'Resultado Integrado')
@section('content_header', 'Resultado Integrado')
@section('content')

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
                v-show="selecionados.length > 0"
                @click.prevent="selecionados = []"
            >
                <i class="fa fa-times"></i> Limpar seleção
            </button>
            <button
                type="button"
                class="btn btn-sm btn-outline-primary mybp-btn-acao-compact"
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

    <div id="conteudo" v-show="!controle.carregando && lista.length > 0">
        <div class="d-flex align-items-center flex-wrap mb-2 mybp-lista-selecao">
            <label class="mb-0 mr-3 d-flex align-items-center" style="gap: 0.4rem; cursor: pointer;">
                <input
                    type="checkbox"
                    :checked="tudoMarcado"
                    :disabled="comResultado.length === 0"
                    @change.prevent="selecionaTodos"
                />
                <span class="small text-muted">
                    Selecionar todos (@{{ comResultado.length }})
                </span>
            </label>
            <span class="small text-muted" v-if="selecionados.length">
                @{{ selecionados.length }} selecionado(s)
            </span>
        </div>

        <div class="mybp-cards-lista">
            <div class="mybp-card" v-for="entrevista in lista" :key="entrevista.id">
                <div class="mybp-card-header-row">
                    <div class="mybp-card-left">
                        <label
                            class="mb-0 mr-1"
                            :for="'ri-sel-' + entrevista.id"
                            v-if="entrevista.resultado_integrado"
                            style="cursor: pointer;"
                        >
                            <input
                                type="checkbox"
                                :id="'ri-sel-' + entrevista.id"
                                v-model="selecionados"
                                :value="entrevista.id"
                            />
                        </label>
                        <span
                            class="mb-0 mr-1 text-muted"
                            v-else
                            content="Sem resultado integrado"
                            v-tippy
                        >
                            <input type="checkbox" disabled />
                        </span>
                        <span class="mybp-badge-id">#@{{ entrevista.id }}</span>
                        <div class="mybp-card-titulo">
                            <strong>@{{ entrevista.curriculo ? entrevista.curriculo.nome : 'Não informado' }}</strong>
                        </div>
                    </div>
                    <div class="mybp-card-right">
                        <mybp-status-badge
                            :variante="chaveStatusRi(entrevista)"
                            :texto="textoStatusRi(entrevista)"
                        ></mybp-status-badge>
                        <div class="dropdown" :class="{ show: isDropdownOpen(entrevista.id) }">
                            <a
                                class="mybp-btn-acoes-compact"
                                href="#"
                                role="button"
                                :id="'ri-acoes-' + entrevista.id"
                                aria-haspopup="true"
                                :aria-expanded="isDropdownOpen(entrevista.id) ? 'true' : 'false'"
                                @click.prevent.stop="toggleDropdown(entrevista.id)"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div
                                class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                :class="{ show: isDropdownOpen(entrevista.id) }"
                                :aria-labelledby="'ri-acoes-' + entrevista.id"
                                @click="fecharDropdown"
                            >
                                <a
                                    class="dropdown-item"
                                    href="javascript://"
                                    v-show="!entrevista.resultado_integrado"
                                    @click.prevent="formEntrevistar(entrevista.id); $refs.janelaParecerEntrevista?.abrirModal()"
                                >
                                    <i class="far fa-share-square mr-1"></i> Encaminhar
                                </a>
                                @can('entrevista_resultado_integrado_update')
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        v-show="entrevista.resultado_integrado"
                                        @click.prevent="formEntrevistar(entrevista.id); editando = true; $refs.janelaParecerEntrevista?.abrirModal()"
                                    >
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                @endcan
                                <a
                                    class="dropdown-item"
                                    href="javascript://"
                                    v-show="entrevista.resultado_integrado"
                                    @click.prevent="formEntrevistar(entrevista.id); visualizar = true; $refs.janelaParecerEntrevista?.abrirModal()"
                                >
                                    <i class="fa fa-search-plus mr-1"></i> Visualizar
                                </a>
                                <a
                                    class="dropdown-item"
                                    v-if="entrevista.resultado_integrado"
                                    :href="`${URL_ADMIN}/entrevistas/resultado-integrado/ficha/${entrevista.id}`"
                                    target="_blank"
                                >
                                    <i class="fa fa-file-pdf mr-1"></i> Imprimir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mybp-card-corpo" :class="'mybp-card-corpo--' + chaveStatusRi(entrevista)">
                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-briefcase"
                                label="Cargo"
                                :valor="entrevista.vaga_aberta_municipio"
                                forte
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-wheelchair"
                                label="PCD"
                                :valor="entrevista.curriculo && entrevista.curriculo.pcd ? 'Sim' : 'Não'"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-user-check"
                                label="Resp. encaminhamento"
                                :valor="textoRespRi(entrevista)"
                            ></mybp-card-campo>
                        </div>
                    </section>

                    <section class="mybp-card-secao">
                        <div class="mybp-card-row">
                            <mybp-card-campo
                                icon="fas fa-folder-open"
                                label="Enc. documentos"
                                :valor="textoEncRi(entrevista, 'documentos_entregue', 'documentos_entregue_data')"
                                :tom="tomEncRi(entrevista, 'documentos_entregue')"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-notes-medical"
                                label="Enc. exame"
                                :valor="textoEncRi(entrevista, 'encaminhado_exame', 'encaminhado_exame_data')"
                                :tom="tomEncRi(entrevista, 'encaminhado_exame')"
                            ></mybp-card-campo>
                            <mybp-card-campo
                                icon="fas fa-chalkboard-teacher"
                                label="Enc. treinamento"
                                :valor="textoEncRi(entrevista, 'encaminhado_treinamento', 'encaminhado_treinamento_data')"
                                :tom="tomEncRi(entrevista, 'encaminhado_treinamento')"
                            ></mybp-card-campo>
                        </div>
                    </section>
                </div>
            </div>
        </div>

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
