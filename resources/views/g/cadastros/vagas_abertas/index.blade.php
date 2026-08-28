@extends('layouts.sistema')
@section('title', 'VAGAS ABERTAS')
@section('content_header')
    <h4 class="text-default">VAGAS ABERTAS</h4>
    <hr class="bg-default" style="margin-top: -5px;">
@stop
@section('content')
@push('css')
    <style>
        /* <details> no modal: esconde marcador nativo e mantém layout alinhado ao restante do formulário */
        #janelaCadastrar .treinamentos-cargo-details > summary::-webkit-details-marker {
            display: none;
        }

        #janelaCadastrar .treinamentos-cargo-details > summary {
            list-style: none;
        }

        #janelaCadastrar .treinamentos-cargo-details > summary .treinamentos-cargo-chevron {
            transition: transform 0.2s ease;
        }

        #janelaCadastrar .treinamentos-cargo-details[open] > summary .treinamentos-cargo-chevron {
            transform: rotate(180deg);
        }
    </style>
@endpush

    <modal ref="janelaCadastrar" id="janelaCadastrar" :titulo="tituloJanela" :size="90">
        <template #conteudo>
            <div v-show="preloadAjax"><i class="fa fa-spinner fa-pulse"></i> Aguarde...</div>
            <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                <h4><i class="icon fa fa-check"></i>Vaga cadastrada com sucesso!</h4>
            </div>
            <div class="alert alert-success alert-dismissible" v-show="atualizado">
                <h4><i class="icon fa fa-check"></i>Vaga alterada com sucesso!</h4>
            </div>
            <form v-if="!preloadAjax && (!cadastrado && !atualizado)" id="form" onsubmit="return false;">

                <div class="form-group">
                    <label for="">Informe o cargo</label>
                    <autocomplete :caminho="cargos_ativos"
                                  :valido="form.vaga_id !== ''"
                                  v-model="form.autocomplete_label_vaga_modal"
                                  placeholder="Selecione um cargo"
                                  :disabled="editando"
                                  :id="hash"
                                  @onblur="resetaCampoVagaModal"
                                  @onselect="selecionaVagaModal"></autocomplete>
                </div>

                <fieldset class="mt-2"
                            v-if="form.vaga_id && (cargoCboResumo.cbo_codigo || cargoCboResumo.codigo_familia || cargoCboResumo.cbo_titulo || cargoCboResumo.cbo_familia || cargoCboResumo.cbo_descricao_sumaria)">
                    <legend class="font-size-14 mb-0">CBO do cargo</legend>
                    <div class="mt-2 p-3 border rounded bg-light">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-1"><small class="text-uppercase text-muted">Código CBO</small></div>
                                <div class="mb-2 font-weight-bold">@{{ cargoCboResumo.cbo_codigo || '—' }}</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-1"><small class="text-uppercase text-muted">Código da família</small></div>
                                <div class="mb-2 font-weight-bold">@{{ cargoCboResumo.codigo_familia || '—' }}</div>
                            </div>
                        </div>

                        <div class="mb-1"><small class="text-uppercase text-muted">Título</small></div>
                        <div class="mb-2 font-weight-bold">@{{ cargoCboResumo.cbo_titulo || 'Não informado' }}</div>

                        <div class="mb-1"><small class="text-uppercase text-muted">Família</small></div>
                        <div class="mb-2">@{{ cargoCboResumo.cbo_familia || 'Não informada' }}</div>

                        <div class="mb-1"><small class="text-uppercase text-muted">Descrição sumária</small></div>
                        <div class="mb-0">@{{ cargoCboResumo.cbo_descricao_sumaria || 'Não informada' }}</div>
                    </div>
                </fieldset>

                <div class="form-group" v-if="treinamentosCargo.length > 0">
                    {{-- <details>: abre/fecha nativo no navegador (evita conflito Collapse Bootstrap + modal/Vue). Fechado: sem atributo open. --}}
                    <details class="treinamentos-cargo-details border rounded overflow-hidden bg-white">
                        <summary
                            class="d-flex justify-content-between align-items-center bg-default py-2 px-3 mb-0 text-left"
                            style="cursor: pointer; box-shadow: none;">
                            <span>
                                <span class="font-weight-bold">Treinamentos vinculados ao cargo</span>
                                <small class="text-muted ml-1">(@{{ treinamentosCargo.length }})</small>
                            </span>
                            <i class="fa fa-fw fa-chevron-down treinamentos-cargo-chevron" aria-hidden="true"></i>
                        </summary>
                        <div class="border-top">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-condensed bg-white mb-0">
                                    <thead>
                                    <tr class="bg-default">
                                        <th>Treinamento</th>
                                        <th>Padrão de Treinamento</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="t in treinamentosCargo" :key="t.id">
                                        <td>@{{ t.label }}</td>
                                        <td>@{{ t.padrao_treinamento }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                </div>

                <div class="form-group" v-else-if="form.vaga_id && !editando">
                    <p class="text-muted mb-0"><small>Nenhum treinamento vinculado a este cargo.</small></p>
                </div>

                <div class="form-group">
                    <label for="descricao">Titulo</label>
                    <input class="form-control" v-model="form.titulo" onblur="valida_campo_vazio(this,1)"
                           placeholder="Informe o título da vaga"
                    >
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <editor :api-key='tinyPadrao.key' v-model="form.descricao" :init="tinyPadrao"></editor>
                </div>

                <div class="form-group">
                    <label for="Cidade">Cidade</label>
                    <autocomplete :caminho="todos_municipios"
                                  :valido="form.municipio_id !== ''"
                                  v-model="form.autocomplete_label_municipio_modal"
                                  placeholder="Selecione um municipio"
                                  :id="`mun_${hash}`"
                                  @onblur="resetaCampoMunicipioModal"
                                  @onselect="selecionaMunicipioModal"></autocomplete>
                </div>

                <fieldset>
                    <legend>Projetos</legend>

                    <p class="text-muted small mb-2">
                        Opcional. Vincule a vaga a um ou mais projetos respeitando a quantidade disponível em cada um.
                    </p>

                    <div class="alert alert-warning py-2 small mb-3" v-if="!temProjetosDisponiveis && !editando">
                        Não há projetos com vagas disponíveis no momento. Cadastre ou libere vagas em Projetos antes de vincular.
                    </div>

                    <button class="btn btn-sm mr-1 btn-primary mb-3"
                            type="button"
                            :disabled="!podeAdicionarNovoProjeto"
                            @click="addLIProjeto">
                        <i class="fa fa-plus"></i> Adicionar
                    </button>

                    <p class="text-muted small mb-3" v-if="form.projetos.length === 0">
                        Nenhum projeto vinculado. Clique em Adicionar para incluir.
                    </p>

                    <fieldset class="mybp-projeto-linha mb-2"
                              v-if="form.projetos.length > 0"
                              v-for="(obj, index) in form.projetos"
                              :key="obj.id || `novo-${index}`">
                        <legend>#@{{ index + 1 }}</legend>

                        <div class="alert py-2 small mb-3"
                             :class="projetoPossuiVinculos(obj) ? 'alert-warning' : 'alert-info'"
                             v-if="projetoLinhaExistente(obj)">
                            @{{ mensagemProjetoExistente(obj) }}
                        </div>

                        <div class="row" v-if="projetoLinhaExistente(obj)">
                            <div class="col-md-5">
                                <label>Projeto</label>
                                <input type="text"
                                       class="form-control"
                                       readonly
                                       :value="nomeProjetoPorId(obj.projeto_id, obj)">
                            </div>

                            <div class="col-md-2">
                                <label>Preenchidas</label>
                                <input type="number"
                                       class="form-control"
                                       readonly
                                       :value="obj.qnt_preenchida || 0">
                            </div>

                            <div class="col-md-2">
                                <label>Qtd. total</label>
                                <input type="number"
                                       :min="quantidadeMinimaProjeto(obj)"
                                       :max="quantidadeMaximaProjeto(obj, index) || null"
                                       v-model="obj.qnt_total"
                                       v-mascara:numero
                                       @input="ajustarQuantidadeProjeto(index)"
                                       @change="ajustarQuantidadeProjeto(index)"
                                       class="form-control">
                                <small class="text-muted">
                                    Preenchidas: @{{ obj.qnt_preenchida || 0 }} · Livre no projeto: @{{ quantidadeLivreProjetoLinha(obj, index) }} · Mín.: @{{ quantidadeMinimaProjeto(obj) }} · Máx.: @{{ quantidadeMaximaProjeto(obj, index) }}
                                </small>
                            </div>

                            <div class="col-md-3">
                                <label>Livre no projeto</label>
                                <input type="number"
                                       class="form-control"
                                       readonly
                                       :value="quantidadeLivreProjetoLinha(obj, index)">
                            </div>
                        </div>

                        <div class="row" v-else>
                            <div class="col-md-6">
                                <label>Projeto</label>

                                <select class="form-control"
                                        v-model="obj.projeto_id"
                                        @change="selecionaProjeto(obj.projeto_id, index)"
                                        onblur="valida_campo_vazio(this, 1)"
                                        onchange="valida_campo_vazio(this, 1)">
                                    <option value="">Selecione...</option>
                                    <option v-for="item in projetosOpcoesPara(index)"
                                            :key="item.id"
                                            :value="item.id"
                                            :disabled="projetoOpcaoDesabilitada(item, obj)">
                                        @{{ labelOpcaoProjeto(item) }}
                                    </option>
                                </select>
                                <small class="text-muted" v-if="listaProjetos.length === 0">
                                    Carregando projetos… Atualize a listagem se a lista continuar vazia.
                                </small>
                                <small class="text-muted" v-else-if="projetosOpcoesPara(index).length === 0">
                                    Todos os projetos já foram selecionados nesta vaga.
                                </small>
                                <small class="text-muted" v-else-if="!projetosOpcoesPara(index).some((item) => !projetoOpcaoDesabilitada(item, obj))">
                                    Nenhum projeto com vagas disponíveis no momento. Projetos esgotados aparecem desabilitados.
                                </small>
                            </div>

                            <div class="col-md-2" v-if="obj.projeto_id">
                                <label>Livre no projeto</label>
                                <input type="number"
                                       disabled
                                       :value="quantidadeLivreProjetoLinha(obj, index)"
                                       class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>Quantidade total de vagas</label>
                                <input type="number"
                                       :min="quantidadeMinimaProjeto(obj)"
                                       :max="quantidadeMaximaProjeto(obj, index) || null"
                                       placeholder="Quantidade para este projeto"
                                       @input="ajustarQuantidadeProjeto(index)"
                                       @change="ajustarQuantidadeProjeto(index)"
                                       v-model="obj.qnt_total"
                                       v-mascara:numero
                                       class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                                <small class="text-muted" v-if="obj.projeto_id">
                                    Preenchidas: @{{ obj.qnt_preenchida || 0 }} · Livre no projeto: @{{ quantidadeLivreProjetoLinha(obj, index) }} · Mín.: @{{ quantidadeMinimaProjeto(obj) }} · Máx.: @{{ quantidadeMaximaProjeto(obj, index) }}
                                </small>
                            </div>
                        </div>

                        <div class="col-12 mt-3 px-0">
                            <button class="btn btn-sm mr-1 btn-danger"
                                    type="button"
                                    :disabled="!podeRemoverProjeto(obj)"
                                    :title="!podeRemoverProjeto(obj) ? mensagemProjetoExistente(obj) : 'Remover vínculo'"
                                    @click="removerLIProjeto(index)">
                                <i class="fa fa-times"></i> Remover
                            </button>
                        </div>
                    </fieldset>
                </fieldset>

                <fieldset>
                    <legend>Provas</legend>

                    <button class="btn btn-sm mr-1 btn-primary mb-3" @click="addLISimulado">
                        <i class="fa fa-plus"></i> Adicionar
                    </button>

                    <fieldset class=" mb-2" v-if="form.simulados.length > 0"
                              v-for="(obj, index) in form.simulados" :key="index">
                        <legend>#@{{ index + 1 }}</legend>
                        <div class="row">

                            <div class="col-md-4">
                                <label>Prova</label>

                                <select class="form-control" v-model="obj.simulado_id"
                                        @change="selecionaSimulado(obj.simulado_id, index)"
                                        onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">
                                    <option value="">Selecione...</option>
                                    <option v-for="item in listaSimulados" :value="item.id">
                                        @{{ item.titulo }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3" v-if="obj.tipo_prova === 'objetiva'">
                                <label>Duração em minutos</label>
                                <input type="number" min="15" placeholder="duração da prova" v-model="obj.duracao"
                                       v-mascara:numero
                                       class="form-control"
                                       onblur="valida_campo_vazio(this,1)">
                            </div>

                            <div class="col-md-2" v-if="obj.tipo_prova === 'objetiva'">
                                <label>Online</label>
                                <select class="form-control" v-model="obj.online"
                                        onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label>Ativo</label>
                                <select class="form-control" v-model="obj.ativo"
                                        onblur="valida_campo_vazio(this, 1)" onchange="valida_campo_vazio(this, 1)">
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <div class="col-md-4 mt-4" v-if="obj.tipo_prova === 'subjetiva'">
                                <label>Imprimir Prova:</label>
                                <button class="btn btn-sm mr-1 btn-primary" @click="imprimeProva(obj.simulado_id, form.id)">
                                    <i class="fa fa-files-pdf"></i> Gerar PDF
                                </button>
                            </div>

                            <div class="col-12 mt-3">
                                <button class="btn btn-sm mr-1 btn-danger" @click="removerLISimulado(index)"><i
                                        class="fa fa-times"></i> Remover
                                </button>

                                <button class="btn btn-sm mr-1 btn-primary mt" @click="addLISimulado" v-show="index >=1">
                                    <i class="fa fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </fieldset>

                </fieldset>

                <div class="d-flex flex-wrap align-items-center mt-2">
                    <div class="custom-control custom-switch mb-0 mr-4">
                        <input
                            type="checkbox"
                            class="custom-control-input"
                            :id="`ativo_site_${hash}`"
                            v-model="form.ativo"
                        />
                        <label class="custom-control-label" :for="`ativo_site_${hash}`">
                            Ativo no site
                            <small class="text-muted">(@{{ form.ativo ? 'Sim' : 'Não' }})</small>
                        </label>
                    </div>

                    <div class="custom-control custom-switch mb-0">
                        <input
                            type="checkbox"
                            class="custom-control-input"
                            :id="`ativo_sistema_${hash}`"
                            v-model="form.ativo_sistema"
                        />
                        <label class="custom-control-label" :for="`ativo_sistema_${hash}`">
                            Ativo no sistema
                            <small class="text-muted">(@{{ form.ativo_sistema ? 'Sim' : 'Não' }})</small>
                        </label>
                    </div>
                </div>
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
                        <label class="mybp-label" for="vaga-aberta-filtro-busca">Buscar</label>
                        <input
                            id="vaga-aberta-filtro-busca"
                            type="text"
                            placeholder="Título, cargo, cidade ou ID"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="form-group mb-0">
                        <label class="mybp-label" for="vaga-aberta-filtro-ativo-site">Ativo no site</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroAtivoSite"
                                instance-id="filtro-ativo-site"
                                v-model="controle.dados.campoAtivoSite"
                                :options="filtroAtivoSiteOpcoes"
                                :disabled="controle.carregando"
                                input-id="vaga-aberta-filtro-ativo-site"
                                placeholder-blur="Todos"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-ativo-site')"
                                @select="onSelectFiltro"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="form-group mb-0">
                        <label class="mybp-label" for="vaga-aberta-filtro-ativo-sistema">Ativo no sistema</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroAtivoSistema"
                                instance-id="filtro-ativo-sistema"
                                v-model="controle.dados.campoAtivoSistema"
                                :options="filtroAtivoSistemaOpcoes"
                                :disabled="controle.carregando"
                                input-id="vaga-aberta-filtro-ativo-sistema"
                                placeholder-blur="Todos"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-ativo-sistema')"
                                @select="onSelectFiltro"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 mybp-combobox-wrap">
                    <div class="form-group mb-0">
                        <label class="mybp-label" for="vaga-aberta-filtro-cargo">Cargo</label>
                        <combobox-auto-complete
                            ref="comboFiltroCargo"
                            instance-id="filtro-cargo"
                            v-model="controle.dados.campoCargoId"
                            :options="filtroCargoOpcoes"
                            :disabled="controle.carregando || !filtroCargoOpcoes.length"
                            input-id="vaga-aberta-filtro-cargo"
                            placeholder-blur="Todos os cargos"
                            empty-message="Nenhum cargo encontrado."
                            :max-results="50"
                            @opening="fecharOutrosComboboxes('filtro-cargo')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>

                <div class="col-12 col-lg-4 mybp-combobox-wrap">
                    <div class="form-group mb-0">
                        <label class="mybp-label" for="vaga-aberta-filtro-municipio">Cidade</label>
                        <combobox-auto-complete
                            ref="comboFiltroMunicipio"
                            instance-id="filtro-municipio"
                            v-model="controle.dados.campoMunicipioId"
                            :options="filtroMunicipioOpcoes"
                            :disabled="controle.carregando || filtroMunicipioOpcoes.length <= 1"
                            input-id="vaga-aberta-filtro-municipio"
                            placeholder-blur="Cidades com vaga aberta"
                            empty-message="Nenhuma cidade encontrada."
                            :max-results="50"
                            @opening="fecharOutrosComboboxes('filtro-municipio')"
                            @select="onSelectFiltro"
                        ></combobox-auto-complete>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="form-group mb-0">
                        <label class="mybp-label" for="vaga-aberta-filtro-provas">Provas vinculadas</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroProvas"
                                instance-id="filtro-provas"
                                v-model="controle.dados.campoComProvas"
                                :options="filtroComProvasOpcoes"
                                :disabled="controle.carregando"
                                input-id="vaga-aberta-filtro-provas"
                                placeholder-blur="Todas"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-provas')"
                                @select="onSelectFiltro"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 mybp-combobox-wrap">
                    <div class="form-group mb-0">
                        <label class="mybp-label" for="vaga-aberta-filtro-projeto">Projeto</label>
                        <combobox-auto-complete
                            ref="comboFiltroProjeto"
                            instance-id="filtro-projeto"
                            v-model="controle.dados.campoProjetoId"
                            :options="filtroProjetoOpcoes"
                            :disabled="controle.carregando"
                            input-id="vaga-aberta-filtro-projeto"
                            placeholder-blur="Todos os projetos"
                            empty-message="Nenhum projeto encontrado."
                            :max-results="50"
                            @opening="fecharOutrosComboboxes('filtro-projeto')"
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
                    <i class="fa fa-plus"></i> Cadastrar Vaga Aberta
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
                                <strong>@{{ vaga.titulo }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <div class="mybp-card-status-botoes">
                                <div class="mybp-card-status-item">
                                    <bt-ativo
                                        campo="ativo"
                                        ativo-label="Ativo site"
                                        inativo-label="Inativo site"
                                        :rota="`cadastro/vagas-abertas/${vaga.id}/ativa-desativa`"
                                        :model="vaga"
                                        @atualizou="atualizar()"
                                    ></bt-ativo>
                                </div>
                                <div class="mybp-card-status-item">
                                    <bt-ativo
                                        campo="ativo_sistema"
                                        ativo-label="Ativo sistema"
                                        inativo-label="Inativo sistema"
                                        :rota="`cadastro/vagas-abertas/${vaga.id}/ativa-desativa-sistema`"
                                        :model="vaga"
                                        @atualizou="atualizar()"
                                    ></bt-ativo>
                                </div>
                            </div>
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
                                        @click.prevent="abrirEdicaoVagaAberta(vaga.id)"
                                    >
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-details-row">
                        <div class="mybp-detail-item">
                            <i class="fas fa-briefcase text-muted"></i>
                            <span class="mybp-detail-label">Cargo</span>
                            <span class="mybp-detail-value">@{{ vaga.cargo_nome || '—' }}</span>
                        </div>
                        <div class="mybp-detail-item">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                            <span class="mybp-detail-label">Local</span>
                            <span class="mybp-detail-value">@{{ vaga.municipio_label || '—' }}</span>
                        </div>
                        <div class="mybp-detail-item" v-if="vaga.updated_at_br">
                            <i class="fas fa-clock text-muted"></i>
                            <span class="mybp-detail-label">Atualizado</span>
                            <span class="mybp-detail-value">@{{ vaga.updated_at_br }}</span>
                        </div>
                    </div>

                    <div class="mybp-card-link-publico">
                        <div class="mybp-card-link-publico-header">
                            <i class="fas fa-link text-muted"></i>
                            <span class="mybp-detail-label">Link público</span>
                        </div>
                        <div class="mybp-card-link-publico-acoes">
                            <a
                                class="mybp-card-link-publico-url"
                                :href="urlVagaPublica(vaga)"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                @{{ urlVagaPublica(vaga) }}
                            </a>
                        </div>
                        <div class="mybp-card-link-compartilhar">
                            <div class="mybp-card-share-botoes">
                                <button
                                    type="button"
                                    class="mybp-btn-share mybp-btn-share--copy"
                                    title="Copiar link"
                                    @click="copiarLinkPublico(vaga)"
                                >
                                    <i class="fa fa-copy"></i>
                                </button>
                                <a
                                    class="mybp-btn-share mybp-btn-share--whatsapp"
                                    :href="urlCompartilharWhatsapp(vaga)"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Compartilhar no WhatsApp"
                                >
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a
                                    class="mybp-btn-share mybp-btn-share--facebook"
                                    :href="urlCompartilharFacebook(vaga)"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Compartilhar no Facebook"
                                >
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <button
                                    type="button"
                                    class="mybp-btn-share mybp-btn-share--instagram"
                                    title="Copiar link para Instagram"
                                    @click="compartilharInstagram(vaga)"
                                >
                                    <i class="fab fa-instagram"></i>
                                </button>
                                <a
                                    class="mybp-btn-share mybp-btn-share--linkedin"
                                    :href="urlCompartilharLinkedin(vaga)"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Compartilhar no LinkedIn"
                                >
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-secoes-row" v-if="vaga.descricao_tem_conteudo">
                        <div class="mybp-card-destaque mybp-card-destaque--full">
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Descrição</small>
                                <p class="mybp-card-texto-resumo mb-0">@{{ vaga.descricao_resumo }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-secoes-row" v-if="temCbo(vaga)">
                        <div class="mybp-card-destaque mybp-card-destaque--primary mybp-card-destaque--full">
                            <i class="fas fa-id-card text-primary"></i>
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

                    <div class="mybp-card-secoes-row">
                        <div class="mybp-card-destaque mybp-card-destaque--info">
                            <i class="fas fa-graduation-cap text-info"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Treinamentos do cargo</small>
                                <strong class="mybp-card-destaque-valor mybp-card-destaque-valor--wrap">
                                    @{{ vaga.treinamentos_tem_vinculo ? 'Sim' : 'Não' }}
                                    <span class="text-muted font-weight-normal" v-if="vaga.treinamentos_tem_vinculo">
                                        · @{{ resumoTreinamentos(vaga) }}
                                    </span>
                                </strong>
                                <div
                                    class="mybp-card-tags"
                                    v-if="vaga.treinamentos_vinculados_labels && vaga.treinamentos_vinculados_labels.length"
                                >
                                    <span
                                        class="mybp-card-tag"
                                        v-for="(label, idx) in vaga.treinamentos_vinculados_labels"
                                        :key="`treinamento-${vaga.id}-${idx}`"
                                    >
                                        @{{ label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mybp-card-destaque mybp-card-destaque--warning">
                            <i class="fas fa-file-alt text-warning"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Provas vinculadas</small>
                                <strong class="mybp-card-destaque-valor mybp-card-destaque-valor--wrap">@{{ resumoSimulados(vaga) }}</strong>
                                <div class="mybp-card-tags" v-if="vaga.simulados_titulos && vaga.simulados_titulos.length">
                                    <span
                                        class="mybp-card-tag"
                                        v-for="(titulo, idx) in vaga.simulados_titulos"
                                        :key="`simulado-${vaga.id}-${idx}`"
                                    >
                                        @{{ titulo }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mybp-card-destaque mybp-card-destaque--success">
                            <i class="fas fa-project-diagram text-success"></i>
                            <div class="mybp-card-destaque-info">
                                <small class="mybp-card-destaque-etapa">Projetos vinculados</small>
                                <strong class="mybp-card-destaque-valor mybp-card-destaque-valor--wrap">@{{ resumoProjetos(vaga) }}</strong>
                                <div class="mybp-card-tags" v-if="vaga.projetos_titulos && vaga.projetos_titulos.length">
                                    <span
                                        class="mybp-card-tag"
                                        v-for="(titulo, idx) in vaga.projetos_titulos"
                                        :key="`projeto-${vaga.id}-${idx}`"
                                    >
                                        @{{ titulo }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center mt-3"
                id="controle"
                ref="componente"
                url="{{route('g.vagas.vagas_abertas.atualizar')}}"
                por-pagina="100"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando">
            </controle-paginacao>
        </div>
    </div>
@stop
@push('js')
    <script src="{{mix('js/g/vagas_abertas/app.js')}}"></script>
@endpush
