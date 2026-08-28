@extends('layouts.sistema')
@section('title', 'Cargos')
@section('content_header','Cargos')
@section('content')

    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template #conteudo>
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Cargo cadastrado com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Cargo alterado com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <fieldset>
                    <legend>Sobre o cargo</legend>
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <div class="form-group">
                                <label>Título do cargo</label>
                                <input type="text" class="form-control form-control-sm" v-model="form.nome"
                                    placeholder="Ex.: Analista de RH"
                                    autocomplete="off">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    v-model="form.ativo"
                                    :id="`switch_ativo_${hash}`">
                                <label class="custom-control-label font-weight-bold" :for="`switch_ativo_${hash}`">
                                    Ativo
                                </label>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mt-3">
                    <legend>CBO</legend>
                    <div class="form-group">
                        <label>Buscar CBO por código, título ou família</label>
                        <autocomplete :caminho="caminhoAutocompleteCbos()"
                                      :formsm="true"
                                      v-model="form.autocomplete_label_cbo"
                                      placeholder="Ex.: 317110"
                                      :id="`cbo_${hash}`"
                                      @onselect="selecionaCbo"
                                      @onblur="validarCboSelecionado"></autocomplete>
                    </div>
                    <div v-if="form.cbo_codigo || form.codigo_familia || form.cbo_titulo || form.cbo_familia || form.cbo_descricao_sumaria" class="mt-2 p-3 border rounded bg-light">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-1"><small class="text-uppercase text-muted">Código CBO</small></div>
                                <div class="mb-2 font-weight-bold">@{{ form.cbo_codigo || '—' }}</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-1"><small class="text-uppercase text-muted">Código da família</small></div>
                                <div class="mb-2 font-weight-bold">@{{ form.codigo_familia || '—' }}</div>
                            </div>
                        </div>

                        <div class="mb-1"><small class="text-uppercase text-muted">Título</small></div>
                        <div class="mb-2 font-weight-bold">@{{ form.cbo_titulo || 'Não informado' }}</div>

                        <div class="mb-1"><small class="text-uppercase text-muted">Família</small></div>
                        <div class="mb-2">@{{ form.cbo_familia || 'Não informada' }}</div>

                        <div class="mb-1"><small class="text-uppercase text-muted">Descrição sumária</small></div>
                        <div class="mb-0">@{{ form.cbo_descricao_sumaria || 'Não informada' }}</div>
                    </div>
                </fieldset>

                <fieldset class="mt-3">
                    <legend>Treinamentos do cargo</legend>

                    <div class="alert alert-info border-0 py-2 mb-3" role="alert">
                        <i class="fa fa-info-circle mr-1"></i>
                        O vínculo treinamento ↔ cargo é gerenciado em
                        <a :href="urlTreinamentoIndustria" target="_blank" rel="noopener">Treinamento Indústria</a>.
                        Esta tela exibe apenas a leitura do que já está configurado.
                    </div>

                    <p class="mb-2"><strong>Resumo:</strong> @{{ resumoTreinamentosForm() }}</p>

                    <div class="alert alert-warning py-2 mb-3" v-if="resumoTreinamentosForm() === 'Nenhum'">
                        Nenhum treinamento aplicável a este cargo.
                    </div>

                    <p v-else-if="form.treinamentos_globais_count > 0 && !form.vencimentos.length" class="text-muted small mb-2">
                        Todos os treinamentos deste cargo vêm de &quot;vincular a todos os cargos&quot;.
                    </p>

                    <div class="table-responsive" v-if="form.vencimentos.length > 0">
                        <table class="table table-bordered table-hover table-condensed bg-white mb-0">
                            <thead>
                            <tr class="bg-default">
                                <th class="text-center">#</th>
                                <th>Treinamento</th>
                                <th>Padrão de Treinamento</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(vencimento, index) in form.vencimentos" :key="vencimento.id">
                                <td class="text-center">@{{ index + 1 }}</td>
                                <td>@{{ vencimento.label }}</td>
                                <td>@{{ vencimento.segmento_nome || 'Geral' }}</td>
                            </tr>
                            </tbody>
                        </table>
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
                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="vaga-filtro-busca">Buscar</label>
                        <input
                            id="vaga-filtro-busca"
                            type="text"
                            placeholder="Buscar por nome ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="vaga-filtro-status">Status</label>
                        <select
                            id="vaga-filtro-status"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoStatus"
                        >
                            <option value="">Todos os status</option>
                            <option :value="true">Apenas ativos</option>
                            <option :value="false">Apenas inativos</option>
                        </select>
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
                    <i class="fa fa-plus"></i> Cadastrar Cargo
                </button>
            </template>
        </filtro-listagem>

        <div id="conteudo">
            <preload class="text-center" v-if="controle.carregando"></preload>

            <div class="alert alert-warning text-center" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="mybp-card" v-for="vaga in lista" :key="vaga.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#@{{ vaga.id }}</span>
                            <div class="mybp-card-titulo">
                                <strong>@{{ vaga.nome }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <bt-ativo
                                :rota="`cadastro/vagas/${vaga.id}/ativa-desativa`"
                                :model="vaga"
                                @atualizou="atualizar()"
                            ></bt-ativo>
                            <div class="dropdown" :class="{ show: isDropdownOpen(vaga.id) }">
                                <a
                                    class="mybp-btn-acoes-compact"
                                    href="#"
                                    role="button"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(vaga.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(vaga.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(vaga.id) }"
                                    @click="fecharDropdown"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Editar"
                                        @click.prevent="abrirEdicaoVaga(vaga.id)"
                                    >
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-secoes-row" v-if="temCbo(vaga)">
                        <div class="mybp-card-destaque mybp-card-destaque--primary mybp-card-destaque--full">
                            <i class="fas fa-briefcase text-primary"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">
                                    CBO @{{ vaga.cbo_codigo || '—' }}
                                    <span v-if="vaga.cbo_codigo_familia"> · Família @{{ vaga.cbo_codigo_familia }}</span>
                                </small>
                                <strong class="mybp-card-destaque-valor mybp-card-destaque-valor--wrap">@{{ vaga.cbo_titulo }}</strong>
                                <small class="text-muted d-block mt-1" v-if="vaga.cbo_familia">@{{ vaga.cbo_familia }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-details-row" v-else>
                        <div class="mybp-detail-item">
                            <i class="fas fa-briefcase text-muted"></i>
                            <span class="mybp-detail-label">CBO</span>
                            <span class="mybp-detail-value text-muted">Não vinculado</span>
                        </div>
                    </div>

                    <div class="mybp-card-secoes-row">
                        <div class="mybp-card-destaque mybp-card-destaque--info mybp-card-destaque--full">
                            <i class="fas fa-graduation-cap text-info"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Treinamentos do cargo</small>
                                <strong class="mybp-card-destaque-valor mybp-card-destaque-valor--wrap">@{{ resumoTreinamentos(vaga) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center mt-3"
                id="controle"
                ref="componente"
                url="{{route('g.vagas.vagas.atualizar')}}"
                por-pagina="100"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando">
            </controle-paginacao>
        </div>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/vagas/app.js')}}"></script>
@endpush
