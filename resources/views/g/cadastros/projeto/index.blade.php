@extends('layouts.sistema')
@section('title', 'Projetos')
@section('content_header','Projetos')
@section('content')

    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template #conteudo>
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Projeto cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Projeto alterado com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <fieldset>
                    <legend>Dados do projeto</legend>

                    <p class="mybp-campo-obrigatorio-legenda mb-3">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>

                    <div class="row">
                        <div class="col-12 col-md-8">
                            <div class="form-group mb-md-0">
                                <label class="mybp-label" for="projeto-nome">
                                    Nome do projeto <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="projeto-nome"
                                    type="text"
                                    class="form-control form-control-sm"
                                    v-model="form.nome"
                                    placeholder="Ex.: Projeto expansão 2026"
                                    autocomplete="off"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-group mb-0">
                                <label class="mybp-label" for="projeto-qnt-total">
                                    Capacidade total <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="projeto-qnt-total"
                                    type="number"
                                    class="form-control form-control-sm"
                                    v-model="form.qnt_total"
                                    oninput="this.value = Math.abs(this.value)"
                                    :min="quantidadeMinimaProjetoTotal"
                                    min="1"
                                    placeholder="Total de vagas"
                                    autocomplete="off"
                                >
                                <small class="text-muted" v-if="editando && quantidadeMinimaProjetoTotal > 1">
                                    Não pode ser menor que @{{ quantidadeMinimaProjetoTotal }} (já distribuído).
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-projeto-resumo mt-3">
                        <div class="mybp-projeto-resumo-item">
                            <span class="mybp-projeto-resumo-label">Capacidade</span>
                            <strong class="mybp-projeto-resumo-valor">@{{ form.qnt_total || 0 }}</strong>
                        </div>
                        <div class="mybp-projeto-resumo-item">
                            <span class="mybp-projeto-resumo-label">Distribuído</span>
                            <strong class="mybp-projeto-resumo-valor">@{{ totalAlocadoVagas }}</strong>
                            <small class="text-muted">em vagas abertas</small>
                        </div>
                        <div
                            class="mybp-projeto-resumo-item"
                            :class="{ 'mybp-projeto-resumo-item--destaque': totalRestanteVagas > 0 }"
                        >
                            <span class="mybp-projeto-resumo-label">Disponível</span>
                            <strong class="mybp-projeto-resumo-valor">@{{ totalRestanteVagas }}</strong>
                            <small class="text-muted">para novos vínculos</small>
                        </div>
                        <div class="mybp-projeto-resumo-item" v-if="editando">
                            <span class="mybp-projeto-resumo-label">Ocupadas</span>
                            <strong class="mybp-projeto-resumo-valor">@{{ form.preenchidas || 0 }}</strong>
                            <small class="text-muted">já contratadas</small>
                        </div>
                    </div>

                    <p class="text-muted small mb-0 mt-2">
                        <strong>Capacidade</strong> é o tamanho do projeto.
                        <strong>Distribuído</strong> é o que você reservou para vagas abertas.
                        <strong>Disponível</strong> ainda pode ser vinculado abaixo.
                    </p>
                </fieldset>

                <fieldset class="mt-4">
                    <legend>Distribuição por vaga aberta</legend>

                    <p class="text-muted small mb-3">
                        Opcional. Cada linha abaixo reserva parte da capacidade do projeto para uma vaga aberta específica.
                    </p>

                    <div class="form-group mb-3" v-if="totalRestanteVagas > 0">
                        <label class="mybp-label">Vincular vaga aberta</label>
                        <autocomplete
                            :caminho="`autocomplete/todas-vagas-abertas-ativas`"
                            :formsm="true"
                            v-model="form.autocomplete_label_vaga_aberta"
                            placeholder="Buscar por título ou cargo"
                            :id="`vagas_projeto_${hash}`"
                            @onselect="selecionaVaga"
                        ></autocomplete>
                    </div>

                    <div class="alert alert-light border py-2 small mb-3" v-else-if="form.vagas_projeto.length > 0">
                        Toda a capacidade já foi distribuída. Para vincular outra vaga aberta, aumente a capacidade total ou reduza a quantidade em um vínculo existente.
                    </div>

                    <div class="alert alert-light border py-3 text-center text-muted small mb-0" v-if="form.vagas_projeto.length === 0">
                        Nenhuma vaga aberta vinculada. Você pode salvar o projeto apenas com nome e capacidade.
                    </div>

                    <div
                        class="mybp-projeto-vinculo"
                        v-for="(item, index) in form.vagas_projeto"
                        :key="item.id || `novo-${item.vaga_aberta_id}`"
                    >
                        <div class="mybp-projeto-vinculo-top">
                            <div class="mybp-projeto-vinculo-identidade">
                                <span class="mybp-badge-id">#@{{ item.vaga_aberta_id || item.vaga_aberta?.id || '—' }}</span>
                                <div class="mybp-projeto-vinculo-info">
                                    <strong class="mybp-projeto-vinculo-nome">@{{ tituloVagaProjeto(item) }}</strong>
                                    <div class="mybp-projeto-vinculo-chips">
                                        <span class="mybp-projeto-vinculo-chip" v-if="exibirCargoVagaProjeto(item)">
                                            <i class="fas fa-briefcase"></i>
                                            @{{ cargoVagaProjeto(item) }}
                                        </span>
                                        <span class="mybp-projeto-vinculo-chip">
                                            <i class="fas fa-map-marker-alt"></i>
                                            @{{ localVagaProjeto(item) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="btn btn-sm btn-link text-danger p-0 mybp-projeto-vinculo-remover"
                                :disabled="!podeRemoverVagaProjeto(item)"
                                :title="podeRemoverVagaProjeto(item) ? 'Remover vínculo' : 'Não é possível remover — há vagas ocupadas'"
                                @click.prevent="removerVagaProjeto(index)"
                            >
                                <i class="fa fa-times"></i>
                            </button>
                        </div>

                        <div class="mybp-projeto-vinculo-metricas">
                            <div
                                class="mybp-projeto-vinculo-metrica mybp-projeto-vinculo-metrica--leitura"
                                :class="{ 'mybp-projeto-vinculo-metrica--alerta': Number(item.qnt_preenchida) > 0 }"
                            >
                                <span class="mybp-projeto-vinculo-metrica-label">Ocupadas</span>
                                <strong class="mybp-projeto-vinculo-metrica-valor">@{{ item.qnt_preenchida || 0 }}</strong>
                                <small class="text-muted">contratadas</small>
                            </div>

                            <div class="mybp-projeto-vinculo-metrica">
                                <label class="mybp-projeto-vinculo-metrica-label" :for="`projeto-vinculo-qtd-${index}`">
                                    Reservadas neste vínculo
                                </label>
                                <div class="mybp-projeto-vinculo-input-row">
                                    <input
                                        :id="`projeto-vinculo-qtd-${index}`"
                                        type="number"
                                        class="form-control form-control-sm text-center mybp-projeto-vinculo-input"
                                        :min="quantidadeMinimaVagaLinha(item)"
                                        :max="quantidadeMaximaVagaLinha(item) || null"
                                        v-model="item.qnt_total"
                                        @input="ajustarQuantidadeVaga(index)"
                                        @change="ajustarQuantidadeVaga(index)"
                                    >
                                    <span class="mybp-projeto-vinculo-limite">
                                        / @{{ quantidadeMaximaVagaLinha(item) }} máx.
                                    </span>
                                </div>
                                <div class="mybp-projeto-vinculo-barra" aria-hidden="true">
                                    <span
                                        class="mybp-projeto-vinculo-barra-fill"
                                        :style="{ width: `${percentualReservaVinculo(item)}%` }"
                                    ></span>
                                </div>
                                <small class="text-muted">
                                    @{{ item.qnt_total || 0 }} reservada(s) · projeto com @{{ form.qnt_total || 0 }} vagas
                                </small>
                            </div>
                        </div>

                        <div class="mybp-projeto-vinculo-alerta" v-if="hintVagaLinha(item)">
                            <div class="mybp-projeto-vinculo-alerta-icone">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="mybp-projeto-vinculo-alerta-texto">
                                <strong>@{{ hintVagaLinhaTitulo() }}</strong>
                                <span>@{{ hintVagaLinha(item) }}</span>
                            </div>
                        </div>
                    </div>
                </fieldset>

            </form>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado && !preloadAjax"
                    @click="alterar()">
                Alterar
            </button>
            <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !cadastrado && !preloadAjax"
                    @click="cadastrar()">
                Cadastrar
            </button>
        </template>
    </modal>

    <div>
        <filtro-listagem @submit="onSubmitFiltro">
            <template #filtros>
                <div class="col-12 col-lg-4">
                    <div class="form-group mb-0">
                        <label class="mybp-label" for="projeto-filtro-busca">Buscar</label>
                        <input
                            id="projeto-filtro-busca"
                            type="text"
                            placeholder="Nome ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="form-group mb-0">
                        <label class="mybp-label" for="projeto-filtro-disponibilidade">Disponibilidade</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroDisponibilidade"
                                instance-id="filtro-disponibilidade"
                                v-model="controle.dados.campoDisponibilidade"
                                :options="filtroDisponibilidadeOpcoes"
                                :disabled="controle.carregando"
                                input-id="projeto-filtro-disponibilidade"
                                placeholder-blur="Todos"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-disponibilidade')"
                                @select="onSelectFiltro"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 mybp-combobox-wrap">
                    <div class="form-group mb-0">
                        <label class="mybp-label" for="projeto-filtro-vinculo-vagas">Vagas abertas</label>
                        <combobox-auto-complete
                            ref="comboFiltroVinculoVagas"
                            instance-id="filtro-vinculo-vagas"
                            v-model="controle.dados.campoVinculoVagas"
                            :options="filtroVinculoVagasOpcoes"
                            :disabled="controle.carregando"
                            input-id="projeto-filtro-vinculo-vagas"
                            placeholder-blur="Todas"
                            empty-message="Nenhuma opção encontrada."
                            :max-results="10"
                            @opening="fecharOutrosComboboxes('filtro-vinculo-vagas')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>
            </template>

            <template #acoes>
                <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-secondary"
                    :disabled="controle.carregando"
                    @click="formNovo(); $refs.janelaCadastrar?.abrirModal()"
                >
                    <i class="fa fa-plus"></i> Cadastrar Projeto
                </button>
            </template>
        </filtro-listagem>

        <div id="conteudo">
            <preload class="text-center" v-if="controle.carregando"></preload>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="mybp-card mybp-card--projeto" v-for="projeto in lista" :key="projeto.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#@{{ projeto.id }}</span>
                            <div class="mybp-card-titulo mybp-card-titulo--stacked">
                                <strong>@{{ projeto.nome }}</strong>
                                <small class="mybp-card-meta-atualizacao" v-if="projeto.updated_at_br">
                                    <i class="far fa-calendar-alt" aria-hidden="true"></i>
                                    <span>Atualizado em @{{ projeto.updated_at_br }}</span>
                                </small>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <span :class="classeStatusProjeto(projeto)">
                                @{{ projeto.tem_vagas_restantes ? 'Com vagas' : 'Esgotado' }}
                            </span>
                            <div class="dropdown" :class="{ show: isDropdownOpen(projeto.id) }">
                                <a
                                    class="mybp-btn-acoes-compact"
                                    href="#"
                                    role="button"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(projeto.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(projeto.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(projeto.id) }"
                                    @click="fecharDropdown"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Editar"
                                        @click.prevent="abrirEdicaoProjeto(projeto.id)"
                                    >
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-projeto-resumo mybp-projeto-resumo--card">
                        <div class="mybp-projeto-resumo-item">
                            <span class="mybp-projeto-resumo-label">Capacidade</span>
                            <strong class="mybp-projeto-resumo-valor">@{{ projeto.qnt_total }}</strong>
                        </div>
                        <div class="mybp-projeto-resumo-item">
                            <span class="mybp-projeto-resumo-label">Distribuído</span>
                            <strong class="mybp-projeto-resumo-valor">@{{ totalDistribuidoProjeto(projeto) }}</strong>
                            <small class="text-muted">em vagas abertas</small>
                        </div>
                        <div
                            class="mybp-projeto-resumo-item"
                            :class="{ 'mybp-projeto-resumo-item--destaque': projeto.tem_vagas_restantes }"
                        >
                            <span class="mybp-projeto-resumo-label">Disponível</span>
                            <strong class="mybp-projeto-resumo-valor">@{{ projeto.qnt_total_restante }}</strong>
                            <small class="text-muted">para novos vínculos</small>
                        </div>
                        <div class="mybp-projeto-resumo-item">
                            <span class="mybp-projeto-resumo-label">Ocupadas</span>
                            <strong class="mybp-projeto-resumo-valor">@{{ projeto.preenchidas }}</strong>
                            <small class="text-muted">contratadas</small>
                        </div>
                    </div>

                    <div class="mybp-projeto-card-progresso">
                        <div class="mybp-projeto-card-progresso-topo">
                            <span class="mybp-projeto-card-progresso-label">Ocupação do projeto</span>
                            <span class="mybp-projeto-card-progresso-valor">@{{ percentualOcupacaoProjeto(projeto) }}%</span>
                        </div>
                        <div class="mybp-projeto-vinculo-barra" aria-hidden="true">
                            <span
                                class="mybp-projeto-vinculo-barra-fill mybp-projeto-vinculo-barra-fill--ocupacao"
                                :style="{ width: `${percentualOcupacaoProjeto(projeto)}%` }"
                            ></span>
                        </div>
                        <div class="mybp-projeto-card-progresso-rodape">
                            <span>@{{ resumoPreenchimento(projeto) }}</span>
                            <span>@{{ resumoVagasVinculadas(projeto) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center mt-3"
                id="controle"
                ref="componente"
                url="{{route('g.projetos.projetos.atualizar')}}"
                por-pagina="100"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando">
            </controle-paginacao>
        </div>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/projeto/app.js')}}"></script>
@endpush
