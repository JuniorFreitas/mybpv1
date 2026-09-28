<template>
    <div>
        <modal id="janelaCadastrar" :titulo="tituloJanela" :fechar="!preloadAjax" :size="90" ref="modal_janelaCadastrar">
            <template #conteudo>
                <preload label="Aguarde..." v-show="preloadAjax"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado || atualizado">
                    <h4><i class="icon fa fa-check"></i>Ocorrrência {{ cadastrado ? 'cadastrada' : 'atualizada' }} com sucesso!</h4>
                </div>
                <form
                    v-if="!preloadAjax && !cadastrado && !atualizado"
                    id="form"
                    class="cih-modal-form mybp-filtros-compactos"
                    onsubmit="return false"
                >
                    <p class="mybp-campo-obrigatorio-legenda cih-modal-legenda">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>

                    <fieldset class="cih-modal-secao">
                        <legend>Ocorrência</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Data da Ocorrência <span class="text-danger">*</span></label>
                                    <date-picker
                                        id="cih-form-data"
                                        label=""
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        formsm
                                        v-model="form.data_lancamento"
                                        :max="hoje"
                                        @input="marcarInputInvalido('#cih-form-data', !form.data_lancamento)"
                                    ></date-picker>
                                </div>
                            </div>

                            <div :class="form.tag_id === 0 ? 'col-md-4' : 'col-md-8'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Tipo <span class="text-danger">*</span></label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormTipo"
                                            instance-id="form-tipo"
                                            v-model="form.tag_id"
                                            :options="formTipoOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovando"
                                            input-id="cih-form-tipo"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhum tipo encontrado."
                                            :max-results="100"
                                            @opening="fecharOutrosComboboxes('form-tipo')"
                                            @select="marcarInputInvalido('#cih-form-tipo', false)"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4" v-if="form.tag_id === 0">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Especifique <span class="text-danger">*</span></label>
                                    <input
                                        id="cih-form-outra-tag"
                                        type="text"
                                        class="form-control form-control-sm validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @change.prevent="valida_campo_vazio($event.target, 1)"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.outra_tag"
                                    />
                                </div>
                            </div>

                            <div class="col-md-6" v-if="config_modelo_cih === 'area'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Área <span class="text-danger">*</span></label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormArea"
                                            instance-id="form-area"
                                            v-model="form.area_id"
                                            :options="formAreaOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovando"
                                            input-id="cih-form-area"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma área encontrada."
                                            :max-results="100"
                                            @opening="fecharOutrosComboboxes('form-area')"
                                            @select="marcarInputInvalido('#cih-form-area', false)"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6" v-if="form.area_id === 0">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Especifique <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm validacampo"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.outra_area"
                                    />
                                </div>
                            </div>

                            <div
                                class="col-md-6"
                                v-if="config_modelo_cih === 'centro_de_custo' && lista_ccs && AUTENTICADO.temFilial"
                            >
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">CNPJ <span class="text-danger">*</span></label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormCnpj"
                                            instance-id="form-cnpj"
                                            v-model="form.campoCnpj"
                                            :options="filtroCnpjOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovando"
                                            input-id="cih-form-cnpj"
                                            placeholder-blur="Selecione o CNPJ..."
                                            empty-message="Nenhum CNPJ encontrado."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-cnpj')"
                                            @select="onSelectFormCnpj"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12" v-if="config_modelo_cih === 'centro_de_custo'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Centro de Custo <span class="text-danger">*</span></label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormCentroCusto"
                                            instance-id="form-centro-custo"
                                            v-model="form.centro_custo_id"
                                            :options="formCentroCustoOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovando || formCentroCustoDesabilitado"
                                            input-id="cih-form-centro-custo"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhum centro de custo encontrado."
                                            :max-results="200"
                                            @opening="fecharOutrosComboboxes('form-centro-custo')"
                                            @select="marcarInputInvalido('#cih-form-centro-custo', false)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="cih-modal-secao">
                        <legend>Envolvidos</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Colaborador(es) <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :key="colaboradorCihCaminho"
                                        :caminho="colaboradorCihCaminho"
                                        formsm
                                        :valido="form.feedback_id !== ''"
                                        v-model="form.autocomplete_label_colaborador"
                                        placeholder="Selecione um(a) colaborador(a)"
                                        :disabled="aprovando || aprovandoRh || visualizar || colaboradorCihDesabilitado"
                                        :id="`colaborador_${hash}`"
                                        @onselect="selecionaColaborador"
                                        v-if="!editando"
                                    ></autocomplete>
                                </div>

                                <div class="table-responsive cih-modal-table-wrap" v-if="form.colaboradores.length">
                                    <table class="table table-bordered table-hover table-sm bg-white cih-modal-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Cargo</th>
                                                <th>Centro de Custo</th>
                                                <th>Lotação</th>
                                                <th class="text-center" width="10%" v-if="!editando">Remover</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(colaborador, index) in form.colaboradores" :key="colaborador.id || index">
                                                <td>
                                                    {{ labelNomeColaboradorModal(colaborador) }}
                                                </td>
                                                <td>
                                                    {{ labelCargoColaboradorModal(colaborador) }}
                                                </td>
                                                <td>
                                                    {{ labelCentroCustoColaboradorModal(colaborador) }}
                                                </td>
                                                <td>
                                                    {{ labelLotacaoColaboradorModal(colaborador) }}
                                                </td>
                                                <td class="text-center" v-if="!editando">
                                                    <a
                                                        href="javascript://"
                                                        class="btn btn-sm btn-danger cih-modal-table-btn"
                                                        @click.prevent="removerLIColaborador(index)"
                                                    >
                                                        <i class="fa fa-times" aria-hidden="true"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <gestoraprovacao
                                :model="form"
                                :verifica="aprovando || aprovandoRh || visualizar"
                                :hash="hash"
                                :obrigatorio="true"
                                formsm
                                v-if="config_modelo_cih === 'area'"
                            ></gestoraprovacao>
                        </div>
                    </fieldset>

                    <fieldset class="cih-modal-secao">
                        <legend>Detalhes</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Ação <span class="text-danger">*</span></label>
                                    <textarea
                                        id="cih-form-acao"
                                        class="form-control form-control-sm validacampo"
                                        rows="3"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        @blur.prevent="valida_campo_vazio($event.target, 1)"
                                        @keyup.prevent="valida_campo_vazio($event.target, 1)"
                                        v-model="form.acao"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-12">
                                <div id="cih-form-anexo" class="cih-form-anexo form-group mybp-filtro-campo">
                                    <label class="mybp-label">
                                        Anexo (Evidência)
                                        <span v-if="anexoObrigatorioAtual" class="text-danger">*</span>
                                    </label>
                                    <upload
                                        :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :leitura="!!form.id"
                                        :url="url_anexo"
                                        label="Selecionar"
                                        @onProgresso="anexoUploadAndamento = true"
                                        @onFinalizado="onAnexoFinalizado"
                                    ></upload>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.obs_lancamento"
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="(visualizar && !form.responsavel_aprovacao) || aprovando">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo GESTOR!
                    </div>

                    <fieldset v-if="!cadastrando" class="cih-modal-secao">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.responsavel_aprovacao" class="col-12">
                                <legend>
                                    {{ form.status }} por: {{ form.responsavel_aprovacao.nome }} em
                                    {{ form.data_aprovacao }}
                                </legend>
                            </div>
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovando || aprovandoRh"
                                        v-model="form.obs_aprovacao"
                                        rows="3"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Status <span class="text-danger" v-if="aprovando">*</span></label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusGestor"
                                            instance-id="form-status-gestor"
                                            v-model="form.status"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovando || aprovandoRh"
                                            input-id="cih-form-status-gestor"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-status-gestor')"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="(visualizar && !form.rh_aprovacao) || aprovandoRh">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo RH!
                    </div>

                    <fieldset v-if="visualizar || aprovandoRh" class="cih-modal-secao">
                        <legend>Aprovação RH</legend>
                        <div class="row">
                            <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12">
                                <legend>
                                    {{ form.resposta_rh }} por: {{ form.rh_aprovacao.nome }} em
                                    {{ form.data_aprovacao_rh }}
                                </legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="visualizar && !aprovando && !aprovandoRh"
                                        v-model="form.obs_rh"
                                        rows="3"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Status <span class="text-danger" v-if="aprovandoRh">*</span></label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusRh"
                                            instance-id="form-status-rh"
                                            v-model="form.resposta_rh"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="visualizar && !aprovando && !aprovandoRh"
                                            input-id="cih-form-status-rh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-status-rh')"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="(aprovando || aprovandoRh) && !preloadAjax" @click="aprovar">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="!aprovando && !aprovandoRh && !cadastrado && !atualizado && !visualizar && !preloadAjax"
                    @click="cadastrar"
                >
                    <i class="fa fa-save"></i> Lançar
                </button>
            </template>
        </modal>

        <FiltroListagem
            class="mt-2 mybp-filtros-compactos"
            :mostrar-limpar-filtros="totalFiltrosAtivos > 0"
            :desabilitado="controle.carregando"
            @submit="atualizar"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <date-range-filter
                    v-model:enabled="controle.dados.filtroPeriodo"
                    v-model:start-date="controle.dados.dataInicio"
                    v-model:end-date="controle.dados.dataFim"
                    :disabled="controle.carregando"
                    :id-suffix="hash"
                    label="Por período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="onPeriodoChange"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="cih-filtro-busca">Buscar</label>
                        <input
                            id="cih-filtro-busca"
                            type="text"
                            placeholder="Nome ou CÓD"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="cih-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="controle.carregando"
                                input-id="cih-filtro-status"
                                placeholder-blur="Todos os Status"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('filtro-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="cih-filtro-tipo">Tipo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroTipo"
                                instance-id="filtro-tipo"
                                v-model="controle.dados.campoTags"
                                :options="filtroTipoOpcoes"
                                :disabled="controle.carregando || !listaTags.length"
                                input-id="cih-filtro-tipo"
                                placeholder-blur="Todos os tipos"
                                empty-message="Nenhum tipo encontrado."
                                :max-results="100"
                                @opening="fecharOutrosComboboxes('filtro-tipo')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && AUTENTICADO.temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="cih-filtro-cnpj">CNPJ</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="filtroCnpjOpcoes"
                                :disabled="controle.carregando"
                                input-id="cih-filtro-cnpj"
                                placeholder-blur="Todos os CNPJs"
                                empty-message="Nenhum CNPJ encontrado."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('filtro-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="config_modelo_cih === 'area'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="cih-filtro-area">Área</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroArea"
                                instance-id="filtro-area"
                                v-model="controle.dados.campoAreas"
                                :options="filtroAreaOpcoes"
                                :disabled="controle.carregando || !listaAreas.length"
                                input-id="cih-filtro-area"
                                placeholder-blur="Todas as áreas"
                                empty-message="Nenhuma área encontrada."
                                :max-results="100"
                                @opening="fecharOutrosComboboxes('filtro-area')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="config_modelo_cih === 'centro_de_custo'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="cih-filtro-centro-custo">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCentroCusto"
                                instance-id="filtro-centro-custo"
                                v-model="controle.dados.campoCentrosDeCusto"
                                :options="filtroCentroCustoOpcoes"
                                :disabled="controle.carregando || !filtroCentroCustoOpcoes.length"
                                input-id="cih-filtro-centro-custo"
                                placeholder-blur="Todos os centros de custo"
                                empty-message="Nenhum centro de custo encontrado."
                                :max-results="200"
                                @opening="fecharOutrosComboboxes('filtro-centro-custo')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="cih-filtro-gestor">Gestor</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroGestor"
                                instance-id="filtro-gestor"
                                v-model="controle.dados.campoGestores"
                                :options="filtroGestorOpcoes"
                                :disabled="controle.carregando"
                                input-id="cih-filtro-gestor"
                                placeholder-blur="Todos os gestores"
                                empty-message="Nenhum gestor encontrado."
                                :max-results="100"
                                @opening="fecharOutrosComboboxes('filtro-gestor')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>
            </template>

            <template #acoes>
                <button type="submit" class="btn btn-sm btn-success" :disabled="controle.carregando">
                    <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-search'"></i>
                    {{ controle.carregando ? 'Buscando...' : 'Buscar' }}
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    :disabled="controle.carregando"
                    @click.prevent="formNovo(); cadastrando = true; aprovandoRh = false; aprovando = false; visualizar = false; $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                    v-if="permissoes.admissao_cih_lancar"
                >
                    <i class="fa fa-plus"></i> Cadastrar
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click.prevent="exportaPdf()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && lista.length === 0)"
                >
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click.prevent="exportaExcel()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length && !selecionados.length)"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                    <span class="badge badge-light ml-1" v-show="selecionados.length" v-text="selecionados.length"></span>
                </button>
            </template>
        </FiltroListagem>

        <div class="cih-lista-legenda" v-show="!controle.carregando && lista.length > 0">
            <span class="cih-lista-legenda__label">Legenda:</span>
            <span class="cih-status-badge cih-status-badge--aberto">Em aberto</span>
            <span class="cih-status-badge cih-status-badge--gestor">Aprovado pelo Gestor</span>
            <span class="cih-status-badge cih-status-badge--rh">Aprovado pelo RH</span>
            <span class="cih-status-badge cih-status-badge--reprovado">Reprovado</span>
        </div>

        <preload v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && !lista.length">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length">
                <div class="mybp-card" v-for="item in lista" :key="item.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ item.id }}</span>
                            <div class="mybp-card-titulo">
                                <strong>{{ nomeColaboradorLista(item) }}</strong>
                            </div>
                        </div>
                        <div class="mybp-card-right">
                            <span class="cih-status-badge" :class="classeStatusLista(item)">
                                {{ textoStatusLista(item) }}
                            </span>
                            <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                                <a
                                    class="mybp-btn-acoes-compact"
                                    href="#"
                                    role="button"
                                    :id="`dropdownMenuLink_${item.id}`"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(item.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(item.id) }"
                                    :aria-labelledby="`dropdownMenuLink_${item.id}`"
                                    @click="fecharDropdown"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação Gestor"
                                        @click.prevent="formAprovar(item.id); visualizar = false; aprovando = true; aprovandoRh = false; cadastrando = false; $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                        v-if="podeAprovarComoGestor(item)"
                                    >
                                        <i class="fa fa-user-check mr-1"></i> Aprovação Gestor
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        @click.prevent="formAprovar(item.id); visualizar = false; aprovando = false; aprovandoRh = true; cadastrando = false; $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                        v-if="item.status === 'aprovado' && item.user_rh_id === null && aprovaRh"
                                    >
                                        <i class="fa fa-users mr-1"></i> Aprovação RH
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="formAprovar(item.id); visualizar = true; aprovando = false; aprovandoRh = false; cadastrando = false; $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                    >
                                        <i class="fa fa-search mr-1"></i> Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cih-card-corpo" :class="classeBordaStatusLista(item)">
                        <section class="cih-card-secao">
                            <div class="cih-card-row">
                                <div class="cih-card-campo">
                                    <span class="cih-card-campo__label">
                                        <i class="fas fa-tag" aria-hidden="true"></i> Tipo
                                    </span>
                                    <span class="cih-card-campo__valor cih-card-campo__valor--forte">{{ labelTipoLista(item) }}</span>
                                </div>
                                <div class="cih-card-campo">
                                    <span class="cih-card-campo__label">
                                        <i class="fas fa-calendar-day" aria-hidden="true"></i> Data da ocorrência
                                    </span>
                                    <span class="cih-card-campo__valor">{{ item.data_lancamento || 'Não informado' }}</span>
                                </div>
                                <div class="cih-card-campo">
                                    <span class="cih-card-campo__label">
                                        <i class="fas fa-user-edit" aria-hidden="true"></i> Lançamento
                                    </span>
                                    <span class="cih-card-campo__valor">
                                        {{ item.responsavel_lancamento?.nome || 'Não informado' }}
                                        <template v-if="item.created_at">
                                            <br />
                                            <span class="cih-card-campo__meta">{{ item.created_at }}</span>
                                        </template>
                                    </span>
                                </div>
                            </div>
                        </section>

                        <section class="cih-card-secao" v-if="mostrarLinhaEscopoLista(item)">
                            <div class="cih-card-row">
                                <div class="cih-card-campo" v-if="AUTENTICADO.temFilial">
                                    <span class="cih-card-campo__label">
                                        <i class="fas fa-building" aria-hidden="true"></i> Empresa
                                    </span>
                                    <span class="cih-card-campo__valor">{{ labelLotacaoLista(item) || 'Não informado' }}</span>
                                </div>
                                <div class="cih-card-campo" v-if="config_modelo_cih === 'centro_de_custo'">
                                    <span class="cih-card-campo__label">
                                        <i class="fas fa-sitemap" aria-hidden="true"></i> Centro de Custo
                                    </span>
                                    <span class="cih-card-campo__valor">{{ labelCentroCustoLista(item) || 'Não informado' }}</span>
                                </div>
                                <div class="cih-card-campo" v-if="config_modelo_cih === 'area'">
                                    <span class="cih-card-campo__label">
                                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i> Área
                                    </span>
                                    <span class="cih-card-campo__valor">{{ labelAreaLista(item) || 'Não informado' }}</span>
                                </div>
                                <div class="cih-card-campo" v-if="item.gestor_aprovacao?.nome || config_modelo_cih === 'area'">
                                    <span class="cih-card-campo__label">
                                        <i class="fas fa-user-tie" aria-hidden="true"></i> Gestor responsável
                                    </span>
                                    <span class="cih-card-campo__valor">{{ item.gestor_aprovacao?.nome || 'Não informado' }}</span>
                                </div>
                            </div>
                        </section>

                        <section class="cih-card-secao cih-card-secao--acao" v-if="item.acao">
                            <div class="cih-card-campo cih-card-campo--full">
                                <span class="cih-card-campo__label">
                                    <i class="fas fa-bolt" aria-hidden="true"></i> Ação
                                </span>
                                <span class="cih-card-campo__valor cih-card-campo__valor--acao">{{ resumoAcaoLista(item) }}</span>
                            </div>
                        </section>

                        <section class="cih-card-secao cih-card-secao--historico">
                            <div class="cih-card-secao__titulo">
                                <i class="fas fa-history" aria-hidden="true"></i> Histórico de aprovação
                            </div>
                            <div class="cih-card-row cih-card-row--historico">
                                <div class="cih-card-campo">
                                    <span class="cih-card-campo__label">
                                        <i class="fas fa-user-check" aria-hidden="true"></i> Aprovação gestor
                                    </span>
                                    <span
                                        class="cih-card-campo__valor cih-card-campo__valor--meta cih-card-campo__valor--linhas"
                                        :class="{
                                            'cih-card-campo__valor--negativo': aprovacaoGestorNegativaLista(item),
                                            'cih-card-campo__valor--positivo': aprovacaoGestorPositivaLista(item)
                                        }"
                                    >{{ detalheAprovacaoGestorLista(item) }}</span>
                                </div>
                                <div class="cih-card-campo">
                                    <span class="cih-card-campo__label">
                                        <i class="fas fa-users" aria-hidden="true"></i> Aprovação RH
                                    </span>
                                    <span
                                        class="cih-card-campo__valor cih-card-campo__valor--meta cih-card-campo__valor--linhas"
                                        :class="{
                                            'cih-card-campo__valor--negativo': aprovacaoRhNegativaLista(item),
                                            'cih-card-campo__valor--positivo': aprovacaoRhPositivaLista(item)
                                        }"
                                    >{{ detalheAprovacaoRhLista(item) }}</span>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center mt-3"
                id="controle"
                ref="componente"
                :url="urlAtualizar"
                :por-pagina="controle.dados.pages"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            >
            </controle-paginacao>
        </div>
    </div>
</template>
<script>
import gestoraprovacao from '../../GestorAprovacao'
import autocomplete from '../../AutoComplete'
import DatePicker from '../../DatePicker'
import Upload from '../../Upload'
import ControlePaginacao from '../../ControlePaginacao'
import DateRangeFilter from '../../DateRangeFilter.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Validacoes from '../../../mixins/Validacoes'

export default {
    name: 'CIH',
    components: {
        autocomplete,
        DatePicker,
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem,
        Upload,
        ControlePaginacao,
        gestoraprovacao
    },
    mixins: [ExportacaoMixin, Validacoes],
    data() {
        return {
            tituloJanela: 'Cadastrando CIH',
            preloadAjax: false,
            editando: false,
            leitura: false,
            apagado: false,
            cadastrando: false,
            visualizar: false,
            aprovando: false,
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaRh: false,
            usuarioLogadoId: null,
            preloadExportacao: false,
            AUTENTICADO,
            lista_ccs: null,

            csrf: CSRF_token,

            colaborador_ativo: `autocomplete/colaboradorCih`,
            todos_municipios: `autocomplete/todos-municipios`,

            urlPdf: `${URL_ADMIN}/apontamento/cih/gerapdf`,
            urlExportacao: `${URL_ADMIN}/apontamento/cih/export`,
            urlAtualizar: `${URL_ADMIN}/apontamento/cih/atualizar`,
            selecionados: [],

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            datarelatorio: '',
            tipoRelatorio: 'pdf',
            cliente_relatorio: '',

            hoje: '',

            permissoes: {
                admissao_cih_lancar: false,
                admissao_cih_aprovar: false,
                admissao_cih_privilegio_adm: false,
                admissao_cih_ver_todas: false,
                aprovar_por_gestor: false,
                aprovar_por_rh: false
            },

            config_modelo_cih: '',
            centros_de_custo: [],
            gestores: [],

            form: {
                tag_id: '',
                outra_tag: '',
                feedback_id: '',
                colaboradores: [],
                colaboradoresDelete: [],
                autocomplete_label_colaborador: '',
                autocomplete_label_colaborador_anterior: '',
                cliente_id: '',
                area_id: '',
                centro_custo_id: '',
                campoCnpj: '',
                varios_colaboradores: false,
                colaboradores_avulso: '',
                outra_area: '',
                acao: '',
                user_lancamento_id: '',
                obs_lancamento: '',
                data_lancamento: '',
                user_aprovacao_id: '',
                obs_aprovacao: '',
                data_aprovacao: '',
                status: '',
                status_aprovacao: '',
                resposta_rh: '',
                obs_rh: '',
                anexos: [],
                anexosDel: [],

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: ''
            },

            url_anexo: `${URL_ADMIN}/apontamento/cih/uploadAnexos`,
            anexoUploadAndamento: false,

            formDefault: null,

            campoNome: null,

            cadastrado: false,
            atualizado: false,

            lista: [],
            listaTags: [],
            listaAreas: [],
            listaClientes: [],

            dropdownAbertoKey: null,

            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
                    campoTags: '',
                    campoAreas: '',
                    campoCentrosDeCusto: '',
                    campoGestores: '',
                    campoCnpj: '',
                    filtroPeriodo: false,
                    dataInicio: '',
                    dataFim: '',
                    periodo: '',
                    pages: 50
                }
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar()
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    computed: {
        totalFiltrosAtivos() {
            const d = this.controle.dados
            return !!(
                d.filtroPeriodo ||
                (d.campoBusca || '').trim() ||
                d.campoStatus ||
                d.campoTags ||
                d.campoAreas ||
                d.campoCentrosDeCusto ||
                d.campoGestores ||
                d.campoCnpj
            )
        },
        filtroStatusOpcoes() {
            return [
                { value: '', label: 'Todos os Status' },
                { value: 'aberto', label: 'Em aberto' },
                { value: 'aprovado_gestor', label: 'Aprovado Gestor' },
                { value: 'aprovado_rh', label: 'Aprovado Rh' },
                { value: 'reprovado', label: 'Reprovado' }
            ]
        },
        filtroTipoOpcoes() {
            const opcoes = [{ value: '', label: 'Todos os tipos' }]
            this.listaTags.forEach((item) => {
                opcoes.push({ value: item.id, label: item.label || String(item.id), raw: item })
            })
            // value 0 = "Outro" (mesma regra do formulário: tag_id nulo + outra_tag)
            opcoes.push({ value: 0, label: 'Outro' })
            return opcoes
        },
        filtroCnpjOpcoes() {
            const opcoes = [{ value: '', label: 'Todos os CNPJs' }]
            if (!this.lista_ccs || !this.lista_ccs.cnpjs) return opcoes
            Object.keys(this.lista_ccs.cnpjs).forEach((key) => {
                const item = this.lista_ccs.cnpjs[key]
                opcoes.push({
                    value: key,
                    label: `${item.nome_fantasia} - ${item.cnpj}`,
                    meta: item.cnpj
                })
            })
            return opcoes
        },
        filtroAreaOpcoes() {
            const opcoes = [{ value: '', label: 'Todas as áreas' }]
            this.listaAreas.forEach((item) => {
                opcoes.push({ value: item.id, label: item.label || String(item.id), raw: item })
            })
            return opcoes
        },
        filtroListaCentroCustoCnpj() {
            if (!this.lista_ccs) {
                return this.centros_de_custo || []
            }
            if (this.controle.dados.campoCnpj !== '' && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.controle.dados.campoCnpj] || []
            }
            if (!this.AUTENTICADO.temFilial) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                return keys.length ? this.lista_ccs.centros_custos[keys[0]] || [] : []
            }
            const all = []
            Object.values(this.lista_ccs.centros_custos || {}).forEach((lista) => {
                ;(lista || []).forEach((item) => all.push(item))
            })
            return all
        },
        filtroCentroCustoOpcoes() {
            const opcoes = [{ value: '', label: 'Todos os centros de custo' }]
            const fonte = this.lista_ccs ? this.filtroListaCentroCustoCnpj : this.centros_de_custo
            ;(fonte || []).forEach((item) => {
                if (item.ativo === false) return
                opcoes.push({ value: item.id, label: item.label || String(item.id), raw: item })
            })
            return opcoes
        },
        filtroGestorOpcoes() {
            const opcoes = [{ value: '', label: 'Todos os gestores' }]
            this.gestores.forEach((item) => {
                const gestor = item.gestor_aprovacao
                if (!gestor || gestor.id == null) return
                opcoes.push({ value: gestor.id, label: gestor.nome || String(gestor.id), raw: item })
            })
            return opcoes
        },
        formTipoOpcoes() {
            const opcoes = this.listaTags.map((item) => ({
                value: item.id,
                label: item.label || String(item.id),
                raw: item
            }))
            // value 0 = "Outro" (mesma regra do select antigo: form.tag_id === 0)
            opcoes.push({ value: 0, label: 'Outro' })
            return opcoes
        },
        formAreaOpcoes() {
            const opcoes = this.listaAreas.map((item) => ({
                value: item.id,
                label: item.label || String(item.id),
                raw: item
            }))
            // value 0 = "Outra" (mesma regra do select antigo: form.area_id === 0)
            opcoes.push({ value: 0, label: 'Outra' })
            return opcoes
        },
        formCentroCustoOpcoes() {
            const gestoresPorId = {}
            ;(this.centros_de_custo || []).forEach((item) => {
                gestoresPorId[item.id] = item.gestor
            })

            const fonte = this.listaCcFormulario
            return (fonte || []).map((item) => {
                if (item.ativo === false) {
                    return null
                }
                const gestor = gestoresPorId[item.id] ?? item.gestor ?? null
                return {
                    value: item.id,
                    label:
                        gestor == null
                            ? `${item.label} - Gestor não informado`
                            : `${item.label} - ${gestor.nome}`,
                    raw: item
                }
            }).filter(Boolean)
        },
        listaCcFormulario() {
            if (!this.lista_ccs) {
                return this.centros_de_custo || []
            }
            if (this.form.campoCnpj !== '' && this.AUTENTICADO.temFilial) {
                return this.lista_ccs.centros_custos[this.form.campoCnpj] || []
            }
            if (!this.AUTENTICADO.temFilial) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                return keys.length ? this.lista_ccs.centros_custos[keys[0]] || [] : []
            }
            const all = []
            Object.values(this.lista_ccs.centros_custos || {}).forEach((lista) => {
                ;(lista || []).forEach((item) => all.push(item))
            })
            return all
        },
        formCentroCustoDesabilitado() {
            return !!(
                this.AUTENTICADO?.temFilial &&
                this.lista_ccs &&
                !this.form.campoCnpj
            )
        },
        colaboradorCihDesabilitado() {
            return !!(
                this.config_modelo_cih === 'centro_de_custo' &&
                this.AUTENTICADO?.temFilial &&
                this.lista_ccs &&
                !this.form.campoCnpj
            )
        },
        colaboradorCihCaminho() {
            const base = this.colaborador_ativo || 'autocomplete/colaboradorCih'

            if (
                this.config_modelo_cih === 'centro_de_custo' &&
                this.AUTENTICADO?.temFilial &&
                this.form.campoCnpj
            ) {
                return `${base}?campoCnpj=${encodeURIComponent(this.form.campoCnpj)}`
            }

            return base
        },
        anexoObrigatorioAtual() {
            if (this.form.tag_id === '' || this.form.tag_id === null || this.form.tag_id === 0) {
                return false
            }
            const tag = this.listaTags.find((item) => Number(item.id) === Number(this.form.tag_id))
            return !!(tag && (tag.anexo_obrigatorio || tag.anexos_obrigatorios))
        },
        formStatusAprovacaoOpcoes() {
            return [
                { value: '', label: 'Selecione...' },
                { value: 'aprovado', label: 'Aprovado' },
                { value: 'reprovado', label: 'Reprovado' }
            ]
        },
        paramsExport() {
            return {
                campoBusca: this.controle.dados.campoBusca,
                campoCentrosDeCusto: this.controle.dados.campoCentrosDeCusto,
                campoStatus: this.controle.dados.campoStatus,
                campoTags: this.controle.dados.campoTags,
                campoAreas: this.controle.dados.campoAreas,
                campoGestores: this.controle.dados.campoGestores,
                campoCnpj: this.controle.dados.campoCnpj,
                filtroPeriodo: this.controle.dados.filtroPeriodo,
                periodo: this.controle.dados.periodo
            }
        }
    },
    methods: {
        onSelectFiltro() {
            this.atualizar()
        },
        onSelectCnpj() {
            this.controle.dados.campoCentrosDeCusto = ''
            this.atualizar()
        },
        onSelectFormCnpj() {
            this.form.centro_custo_id = ''
            this.form.colaboradores = []
            this.form.autocomplete_label_colaborador = ''
            this.form.autocomplete_label_colaborador_anterior = ''
            this.form.feedback_id = ''
            this.marcarInputInvalido('#cih-form-cnpj', false)
            this.marcarInputInvalido('#cih-form-centro-custo', false)
        },
        resolverCnpjPorCentroCusto(centroId) {
            if (!centroId || !this.lista_ccs?.centros_custos) {
                return ''
            }
            for (const [cnpjKey, centros] of Object.entries(this.lista_ccs.centros_custos)) {
                if ((centros || []).some((c) => Number(c.id) === Number(centroId))) {
                    return cnpjKey
                }
            }
            return ''
        },
        campoFormVazio(valor) {
            return valor === '' || valor === null || valor === undefined
        },
        marcarInputInvalido(selector, invalido = true) {
            const el = typeof selector === 'string' ? document.querySelector(selector) : selector
            if (!el) {
                return
            }
            el.classList.toggle('is-invalid', !!invalido)
        },
        limparErrosModal() {
            const modal = document.querySelector('#janelaCadastrar')
            if (!modal) {
                return
            }
            modal.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'))
        },
        onAnexoFinalizado() {
            this.anexoUploadAndamento = false
            if (Array.isArray(this.form.anexos) && this.form.anexos.length > 0) {
                this.marcarInputInvalido('#cih-form-anexo', false)
            }
        },
        validarLancamento() {
            this.limparErrosModal()
            this.validaBlur()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            let valido = true
            let mensagem = 'Existem campos obrigatórios não preenchidos'

            if (this.campoFormVazio(this.form.data_lancamento)) {
                this.marcarInputInvalido('#cih-form-data', true)
                valido = false
            }

            if (this.campoFormVazio(this.form.tag_id)) {
                this.marcarInputInvalido('#cih-form-tipo', true)
                valido = false
            }

            if (this.form.tag_id === 0 && !(this.form.outra_tag || '').trim()) {
                this.marcarInputInvalido('#cih-form-outra-tag', true)
                valido = false
            }

            if (this.config_modelo_cih === 'area' && this.campoFormVazio(this.form.area_id)) {
                this.marcarInputInvalido('#cih-form-area', true)
                valido = false
            }

            if (
                this.config_modelo_cih === 'centro_de_custo' &&
                this.AUTENTICADO?.temFilial &&
                this.lista_ccs &&
                this.campoFormVazio(this.form.campoCnpj)
            ) {
                this.marcarInputInvalido('#cih-form-cnpj', true)
                mensagem = 'Selecione o CNPJ para buscar o centro de custo'
                valido = false
            }

            if (this.config_modelo_cih === 'centro_de_custo' && this.campoFormVazio(this.form.centro_custo_id)) {
                this.marcarInputInvalido('#cih-form-centro-custo', true)
                valido = false
            }

            if (!Array.isArray(this.form.colaboradores) || this.form.colaboradores.length === 0) {
                this.marcarInputInvalido(`#colaborador_${this.hash}`, true)
                mensagem = 'Adicione o colaborador'
                valido = false
            }

            if (!(this.form.acao || '').trim()) {
                this.marcarInputInvalido('#cih-form-acao', true)
                valido = false
            }

            if (this.anexoObrigatorioAtual && (!this.form.anexos || this.form.anexos.length === 0)) {
                this.marcarInputInvalido('#cih-form-anexo', true)
                mensagem = 'O Campo Anexo não pode ficar vazio'
                valido = false
            }

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                valido = false
            }

            return { valido, mensagem }
        },
        onPeriodoChange() {
            this.syncPeriodoFromDates()
            this.atualizar()
        },
        limparFiltros() {
            this.controle.dados = {
                campoBusca: '',
                campoStatus: '',
                campoTags: '',
                campoAreas: '',
                campoCentrosDeCusto: '',
                campoGestores: '',
                campoCnpj: '',
                filtroPeriodo: false,
                dataInicio: '',
                dataFim: '',
                periodo: '',
                pages: this.controle.dados.pages || 50
            }
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            const combos = [
                ['filtro-status', 'comboFiltroStatus'],
                ['filtro-tipo', 'comboFiltroTipo'],
                ['filtro-cnpj', 'comboFiltroCnpj'],
                ['filtro-area', 'comboFiltroArea'],
                ['filtro-centro-custo', 'comboFiltroCentroCusto'],
                ['filtro-gestor', 'comboFiltroGestor'],
                ['form-tipo', 'comboFormTipo'],
                ['form-cnpj', 'comboFormCnpj'],
                ['form-area', 'comboFormArea'],
                ['form-centro-custo', 'comboFormCentroCusto'],
                ['form-status-gestor', 'comboFormStatusGestor'],
                ['form-status-rh', 'comboFormStatusRh']
            ]
            combos.forEach(([id, refName]) => {
                if (id !== manter && this.$refs[refName] && typeof this.$refs[refName].close === 'function') {
                    this.$refs[refName].close()
                }
            })
        },
        toggleDropdown(itemId) {
            if (!itemId) {
                return
            }
            const key = `cih:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `cih:${itemId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        onClickOutside(event) {
            if (event && event.target && event.target.closest && event.target.closest('.dropdown')) {
                return
            }
            if (event?.target?.closest?.('.mybp-combobox-wrap')) return
            if (event?.target?.closest?.('.ma-autocomplete-list')) return
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
        },
        syncPeriodoFromDates() {
            const d = this.controle.dados
            if (!d.filtroPeriodo || !d.dataInicio || !d.dataFim) {
                d.periodo = ''
                return
            }
            const fmt = (ymd) => {
                if (!ymd) return ''
                const [y, m, day] = ymd.split('-')
                return `${day}/${m}/${y}`
            }
            d.periodo = `${fmt(d.dataInicio)} até ${fmt(d.dataFim)}`
        },
        exportaExcel() {
            if (!this.controle.dados.filtroPeriodo) {
                mostraErro('', 'Selecione um periodo por favor!')
                return false
            }
            this.preloadExportacao = true
            mostraSucesso('Estamos gerando seu arquivo excel, assim que finalizado você será notificado.')
            setTimeout(() => {
                this.preloadExportacao = false
            }, 500)
            axios
                .post(`${this.urlExportacao}`, this.paramsExport)
                .then(({ data }) => {
                    this.preloadExportacao = false
                })
                .catch((erro) => {
                    mostraErro(erro)
                    this.preloadExportacao = false
                })
        },

        exportaPdf() {
            if (!this.controle.dados.filtroPeriodo) {
                mostraErro('', 'Selecione um periodo por favor!')
                return false
            }
            this.preloadExportacao = true
            mostraSucesso('Estamos gerando seu arquivo pdf, assim que finalizado você será notificado.')
            setTimeout(() => {
                this.preloadExportacao = false
            }, 500)
            axios
                .post(`${this.urlPdf}`, this.paramsExport)
                .then(({ data }) => {
                    this.preloadExportacao = false
                })
                .catch((erro) => {
                    mostraErro(erro)
                    this.preloadExportacao = false
                })
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.comTeste.map((item) => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.comTeste.map((item) => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },
        removerLIColaborador(index) {
            if (!this.form.colaboradores[index].novo) {
                this.form.colaboradoresDelete.push(this.form.colaboradores[index].id)
            }
            this.form.colaboradores.splice(index, 1)
        },
        selecionaColaborador(obj) {
            // this.form.feedback_id = obj.id;
            // this.form.cliente_id = obj.cliente_id;
            // this.form.autocomplete_label_colaborador = obj.label;
            // this.form.autocomplete_label_colaborador_anterior = obj.label;

            const colaborador = {}

            Object.assign(colaborador, obj)
            colaborador.novo = true

            let atual = this.form.colaboradores.findIndex((val) => val.id === colaborador.id)

            if (atual < 0) {
                //Se não existir ainda no array
                this.form.colaboradores.push(colaborador)
                this.marcarInputInvalido(`#colaborador_${this.hash}`, false)
            } else {
                mostraErro('', `O colaborador(a) ${colaborador.nome} já está na lista.`)
                this.form.autocomplete_label_colaborador = ''
                return false
            }
            this.form.autocomplete_label_colaborador = ''
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = ''
                this.form.autocomplete_label_colaborador = ''
                this.form.feedback_id = ''
                this.form.cliente_id = ''

                setTimeout(() => {
                    if (this.form.feedback_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                        // $('#janelaCadastrar #' + this.hash).focus().trigger('blur');
                        $(`#janelaCadastrar #colaborador_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Vaga não pode ficar vazio')
                    }
                }, 100)
            }
        },

        formNovo() {
            formReset()
            setupCampo()
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.aprovando = false
            this.tituloJanela = 'Cadastrando CIH'
            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.status = 'aberto'
            this.form.data_lancamento = this.hoje || ''
        },
        cadastrar() {
            formReset()
            this.$nextTick(() => {
                const { valido, mensagem } = this.validarLancamento()
                if (!valido) {
                    mostraErro('', mensagem)
                    const primeiroInvalido = document.querySelector('#janelaCadastrar .is-invalid')
                    if (primeiroInvalido && typeof primeiroInvalido.focus === 'function') {
                        primeiroInvalido.focus()
                    }
                    return false
                }

                this.preloadAjax = true
                this.form.status = 'aberto'

                axios
                    .post(`${URL_ADMIN}/apontamento/cih`, this.form)
                    .then((response) => {
                        if (response.status === 201) {
                            this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                            mostraSucesso('', 'Ocorrência cadastrada com sucesso')
                            this.preloadAjax = false
                            this.cadastrado = true
                            this.atualizar()
                        }
                    })
                    .catch(() => (this.preloadAjax = false))
            })
        },
        formAlterar(id) {
            formReset()
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.aprovando = true
            this.tituloJanela = `Alterando CIH #${id}`
            this.preloadAjax = true

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true

            axios
                .get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.form.campoCnpj = this.resolverCnpjPorCentroCusto(this.form.centro_custo_id)
                    // this.form.status = this.form.status === "aberto" ? "" : this.form.status;
                    this.editando = true
                    this.preloadAjax = false
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        alterar() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.form._method = 'PUT'
            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/apontamento/cih/${this.form.id}`, this.form)
                .then((response) => {
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    mostraSucesso('', 'Ocorrência alterada com sucesso!')
                    this.preloadAjax = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
        },

        formAprovar(id) {
            formReset()
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.aprovando = true
            this.tituloJanela = `Aprovando CIH #${id}`
            this.preloadAjax = true

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true

            axios
                .get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.form.campoCnpj = this.resolverCnpjPorCentroCusto(this.form.centro_custo_id)
                    this.form.status = this.form.status === 'aberto' ? '' : this.form.status
                    this.form.resposta_rh = this.form.resposta_rh === null ? '' : this.form.resposta_rh
                    this.editando = true
                    this.preloadAjax = false
                    setupCampo()
                })
                .catch((error) => (this.preloadAjax = false))
        },
        aprovar() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')
            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.form._method = 'PUT'
            this.preloadAjax = true
            axios
                .put(`${URL_ADMIN}/apontamento/cih/aprovar/${this.form.id}`, this.form)
                .then((response) => {
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    mostraSucesso('', 'Ocorrência alterada com sucesso!')
                    this.preloadAjax = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => (this.preloadAjax = false))
        },

        gerarPdf() {
            this.preloadAjax = true
            axios
                .post(`${URL_ADMIN}/apontamento/cih/gerapdf`)
                .then((response) => {
                    this.preloadAjax = false
                    window.open(response.data.url, '_blank')
                })
                .catch((error) => (this.preloadAjax = false))
        },

        podeAprovarComoGestor(item) {
            if (!item || item.user_aprovacao_id !== null || item.status !== 'aberto' || !this.aprovaGestor) {
                return false
            }

            if (this.permissoes.admissao_cih_privilegio_adm) {
                return true
            }

            return Number(item.gestor_id) === Number(this.usuarioLogadoId)
        },
        nomeColaboradorLista(item) {
            if (!item) {
                return 'Não informado'
            }
            if (item.varios_colaboradores) {
                const qtd = this.qtdColaboradoresLista(item)
                if (qtd > 1) {
                    return `Vários colaboradores (${qtd})`
                }
                return 'Vários colaboradores'
            }
            return item.colaboradores?.[0]?.curriculo?.nome || 'Não informado'
        },
        qtdColaboradoresLista(item) {
            return Array.isArray(item?.colaboradores) ? item.colaboradores.length : 0
        },
        labelTipoLista(item) {
            if (!item) {
                return 'Não informado'
            }
            if (item.tag?.label) {
                return item.tag.label
            }
            const outra = (item.outra_tag || '').toString().trim()
            if (outra && outra !== '0') {
                return `${outra} (OUTRO)`
            }
            return 'OUTRO'
        },
        labelAreaLista(item) {
            if (!item) {
                return ''
            }
            return item.area?.label || item.outra_area || ''
        },
        labelCentroCustoLista(item) {
            if (!item) {
                return ''
            }
            return item.centro_de_custo?.label || item.centro_custo_outro || ''
        },
        mostrarLinhaEscopoLista(item) {
            if (!item) {
                return false
            }
            if (this.AUTENTICADO?.temFilial) {
                return true
            }
            if (this.config_modelo_cih === 'centro_de_custo') {
                return true
            }
            if (this.config_modelo_cih === 'area') {
                return true
            }
            return !!(item.gestor_aprovacao?.nome)
        },
        formatLotacaoPorCentroCustoId(centroCustoId) {
            if (!centroCustoId || !this.lista_ccs?.centros_custos) {
                return ''
            }
            for (const [cnpjKey, centros] of Object.entries(this.lista_ccs.centros_custos)) {
                const encontrado = (centros || []).find((centro) => Number(centro.id) === Number(centroCustoId))
                if (!encontrado) {
                    continue
                }
                const info = this.lista_ccs.cnpjs?.[cnpjKey] ?? {}
                const tipo = encontrado.matriz ? 'Matriz' : 'Filial'
                const nome = info.nome_fantasia || info.razao_social || encontrado.nome_fantasia || encontrado.razao_social
                const cnpj = info.cnpj || encontrado.cnpj_format
                if (nome && cnpj) {
                    return `${nome} - ${cnpj} (${tipo})`
                }
                if (nome) {
                    return `${nome} (${tipo})`
                }
                return tipo
            }
            return ''
        },
        labelLotacaoColaborador(colaborador) {
            const centroId = colaborador?.admissao?.centro_custo_id || colaborador?.centro_custo_id
            if (centroId) {
                return this.formatLotacaoPorCentroCustoId(centroId)
            }
            return colaborador?.lotacao || ''
        },
        labelNomeColaboradorModal(colaborador) {
            if (!colaborador) {
                return ''
            }
            if (!this.editando) {
                return colaborador.nome || colaborador.label || ''
            }
            return colaborador.curriculo?.nome || colaborador.nome || ''
        },
        labelCargoColaboradorModal(colaborador) {
            if (!colaborador) {
                return ''
            }
            if (!this.editando) {
                return colaborador.cargo || ''
            }
            return (
                colaborador.admissao?.cargo ||
                colaborador.vaga_aberta?.vaga?.nome ||
                colaborador.cargo ||
                ''
            )
        },
        labelCentroCustoColaboradorModal(colaborador) {
            if (!colaborador) {
                return 'Não informado'
            }
            if (colaborador.centro_custo) {
                return colaborador.centro_custo
            }
            return colaborador.admissao?.centro_custo?.label || 'Não informado'
        },
        labelLotacaoColaboradorModal(colaborador) {
            if (!colaborador) {
                return 'Não informado'
            }
            if (colaborador.lotacao) {
                return colaborador.lotacao
            }
            return this.labelLotacaoColaborador(colaborador) || 'Não informado'
        },
        labelLotacaoLista(item) {
            if (!this.AUTENTICADO?.temFilial || !item) {
                return ''
            }
            const colaboradores = Array.isArray(item.colaboradores) ? item.colaboradores : []
            const labels = [
                ...new Set(colaboradores.map((colaborador) => this.labelLotacaoColaborador(colaborador)).filter(Boolean))
            ]
            if (!labels.length) {
                return this.formatLotacaoPorCentroCustoId(item.centro_custo_id) || ''
            }
            if (labels.length === 1) {
                return labels[0]
            }
            return labels.join(' · ')
        },
        resumoAcaoLista(item) {
            const acao = (item?.acao || '').trim()
            if (!acao) {
                return 'Não informado'
            }
            if (acao.length <= 220) {
                return acao
            }
            return `${acao.slice(0, 217).trim()}...`
        },
        chaveStatusLista(item) {
            if (!item) {
                return 'aberto'
            }
            if (item.resposta_rh === 'reprovado' || item.status === 'reprovado') {
                return 'reprovado'
            }
            if (item.resposta_rh === 'aprovado') {
                return 'rh'
            }
            if (item.status === 'aprovado' && item.resposta_rh === null) {
                return 'gestor'
            }
            return 'aberto'
        },
        classeStatusLista(item) {
            return `cih-status-badge--${this.chaveStatusLista(item)}`
        },
        classeBordaStatusLista(item) {
            return `cih-card-corpo--${this.chaveStatusLista(item)}`
        },
        textoStatusLista(item) {
            const chave = this.chaveStatusLista(item)
            if (chave === 'reprovado') {
                return 'Reprovado'
            }
            if (chave === 'rh') {
                return 'Aprovado RH'
            }
            if (chave === 'gestor') {
                return 'Aprovado Gestor'
            }
            return 'Em aberto'
        },
        detalheAprovacaoGestorLista(item) {
            if (!item || !item.responsavel_aprovacao) {
                return 'Pendente'
            }
            const status = item.status === 'reprovado' ? 'Reprovado' : item.status === 'aprovado' ? 'Aprovado' : item.status
            const linhas = [status]
            if (item.data_aprovacao) {
                linhas.push(item.data_aprovacao)
            }
            linhas.push(item.responsavel_aprovacao.nome)
            return linhas.join('\n')
        },
        detalheAprovacaoRhLista(item) {
            if (item?.status === 'reprovado' && !item.resposta_rh) {
                return 'Cancelado'
            }
            if (!item || !item.rh_aprovacao || !item.resposta_rh) {
                return 'Pendente'
            }
            const status = item.resposta_rh === 'reprovado' ? 'Reprovado' : item.resposta_rh === 'aprovado' ? 'Aprovado' : item.resposta_rh
            const linhas = [status]
            if (item.data_aprovacao_rh) {
                linhas.push(item.data_aprovacao_rh)
            }
            linhas.push(item.rh_aprovacao.nome)
            return linhas.join('\n')
        },
        aprovacaoGestorNegativaLista(item) {
            return item?.status === 'reprovado'
        },
        aprovacaoGestorPositivaLista(item) {
            return item?.status === 'aprovado' && !!item?.responsavel_aprovacao
        },
        aprovacaoRhNegativaLista(item) {
            if (item?.status === 'reprovado' && !item.resposta_rh) {
                return true
            }
            return item?.resposta_rh === 'reprovado'
        },
        aprovacaoRhPositivaLista(item) {
            return item?.resposta_rh === 'aprovado'
        },
        carregou(dados) {
            this.lista = dados.itens
            this.listaTags = dados.tags
            this.listaAreas = dados.areas
            this.centros_de_custo = dados.centros_de_custo || []
            this.lista_ccs = dados.cc || null
            this.gestores = dados.gestores
            this.datarelatorio = dados.intervalo
            this.hoje = dados.hoje
            this.permissoes = dados.permissoes
            this.usuarioLogadoId = dados.usuario_logado_id ?? null
            this.config_modelo_cih = dados.config_modelo_cih
            this.controle.carregando = false
            this.aprovaGestor = this.permissoes.aprovar_por_gestor
            this.aprovaRh = this.permissoes.aprovar_por_rh
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
}
</script>

<style scoped>
.cih-modal-form {
    margin-top: 0.15rem;
}

.cih-modal-legenda {
    margin-bottom: 0.65rem;
    font-size: var(--mybp-fc-label-fs, 0.7rem);
    color: #6c757d;
}

.cih-modal-form .cih-modal-secao {
    margin-top: 0.55rem;
    margin-bottom: 0.55rem;
    padding: 0.75rem 0.85rem 0.55rem;
}

.cih-modal-form .cih-modal-secao > legend {
    font-size: 0.7rem;
    line-height: 1.25;
    padding: 0.25rem 0.7rem;
    letter-spacing: 0.02em;
    /* font-weight: 600; */
}

.cih-modal-form .cih-modal-secao:first-of-type {
    margin-top: 0;
}

.cih-modal-form :deep(.form-group > .form-group) {
    margin-bottom: 0;
}

.cih-modal-form :deep(.form-group > .form-group > div > label:empty) {
    display: none;
}

.cih-modal-form :deep(.mybp-filtro-campo .form-group) {
    margin-bottom: 0;
}

.cih-modal-form .row > [class*='col-'] {
    margin-bottom: var(--mybp-fc-gap, 0.45rem);
}

.cih-modal-table-wrap {
    margin-top: 0.35rem;
}

.cih-modal-table {
    font-size: var(--mybp-fc-ctrl-fs, 0.6875rem);
}

.cih-modal-table thead th {
    font-size: var(--mybp-fc-label-fs, 0.7rem);
    font-weight: 600;
    padding: 0.3rem 0.45rem !important;
    vertical-align: middle;
    white-space: nowrap;
    background-color: #eef1f4;
    border-color: #dde2e7;
    text-align: left;
}

.cih-modal-table thead th.text-center {
    text-align: center;
}

.cih-modal-table tbody td {
    font-size: var(--mybp-fc-ctrl-fs, 0.6875rem);
    line-height: 1.3;
    padding: 0.3rem 0.45rem !important;
    vertical-align: middle;
    text-align: left;
}

.cih-modal-table tbody td.text-center {
    text-align: center;
}

.cih-modal-table-btn {
    padding: 0.1rem 0.35rem !important;
    font-size: 0.7rem !important;
    line-height: 1.2 !important;
    border-radius: 4px !important;
}

.cih-form-anexo.is-invalid {
    border: 1px solid #dc3545;
    border-radius: 4px;
    padding: 0.45rem 0.6rem;
    background-color: #fff8f8;
}

.cih-lista-legenda {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem 0.55rem;
    margin: 0.65rem 0 0.45rem;
    padding: 0.35rem 0;
}

.cih-lista-legenda__label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6c757d;
    margin-right: 0.15rem;
}

.cih-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.18rem 0.5rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
    border: 1px solid transparent;
}

.cih-status-badge--aberto {
    background: #f1f3f5;
    color: #495057;
    border-color: #dee2e6;
}

.cih-status-badge--gestor {
    background: #fff3cd;
    color: #856404;
    border-color: #ffeeba;
}

.cih-status-badge--rh {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.cih-status-badge--reprovado {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.cih-card-corpo {
    margin-top: 0.15rem;
    border: 1px solid #e4e8ec;
    border-radius: 8px;
    border-left: 4px solid #adb5bd;
    background: #fff;
    overflow: hidden;
}

.cih-card-corpo--aberto {
    border-left-color: #adb5bd;
}

.cih-card-corpo--gestor {
    border-left-color: #ffc107;
}

.cih-card-corpo--rh {
    border-left-color: #28a745;
}

.cih-card-corpo--reprovado {
    border-left-color: #dc3545;
}

.cih-card-secao {
    padding: 0.7rem 0.85rem;
}

.cih-card-secao + .cih-card-secao {
    border-top: 1px solid #eef1f4;
}

.cih-card-secao--acao {
    background: #f5f9fc;
}

.cih-card-secao--historico {
    background: #fafbfc;
}

.cih-card-secao__titulo {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #4e4f4f;
    margin-bottom: 0.45rem;
}

.cih-card-secao__titulo i {
    font-size: 0.7rem;
    opacity: 0.85;
}

.cih-card-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem 1.25rem;
}

.cih-card-row--historico {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.cih-card-campo {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.cih-card-campo--full {
    width: 100%;
}

.cih-card-campo__label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    text-transform: uppercase;
    color: #4e4f4f;
}

.cih-card-campo__label i {
    font-size: 0.7rem;
    width: 0.85rem;
    text-align: center;
    opacity: 0.9;
}

.cih-card-campo__valor {
    font-size: 0.8125rem;
    line-height: 1.4;
    color: #212529;
    word-break: break-word;
}

.cih-card-campo__valor--forte {
    font-weight: 600;
    color: #174257;
}

.cih-card-campo__valor--acao {
    font-size: 0.8125rem;
    font-weight: 400;
    color: #212529;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.cih-card-campo__valor--meta {
    font-size: 0.78rem;
    color: #495057;
}

.cih-card-campo__valor--linhas {
    white-space: pre-line;
}

.cih-card-campo__valor--negativo {
    color: #dc3545;
    font-weight: 600;
}

.cih-card-campo__valor--positivo {
    color: #28a745;
    font-weight: 600;
}

.cih-card-campo__meta {
    font-size: 0.75rem;
    color: #8a939b;
    font-weight: 400;
}

@media (max-width: 767.98px) {
    .cih-card-row,
    .cih-card-row--historico {
        grid-template-columns: 1fr;
        gap: 0.65rem;
    }
}
</style>
