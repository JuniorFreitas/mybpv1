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
                    class="mybp-modal-form mybp-filtros-compactos"
                    onsubmit="return false"
                >
                    <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>

                    <fieldset class="mybp-modal-secao">
                        <legend>Ocorrência</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                                    <label class="mybp-label" for="cih-form-data">
                                        Data da Ocorrência <span class="text-danger">*</span>
                                    </label>
                                    <date-picker
                                        id="cih-form-data"
                                        label=""
                                        class="corrigiDatepicker"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        formsm
                                        v-model="form.data_lancamento"
                                        :max="hoje"
                                        @input="limparCampoDataInvalido('cih-form-data')"
                                    ></date-picker>
                                </div>
                            </div>

                            <div :class="form.tag_id === 0 ? 'col-12 col-md-4' : 'col-12 col-md-8'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-tipo">
                                        Tipo <span class="text-danger">*</span>
                                    </label>
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
                                            @select="limparComboboxInvalido('cih-form-tipo')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="form.tag_id === 0">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-outra-tag">
                                        Especifique <span class="text-danger">*</span>
                                    </label>
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

                            <div class="col-12 col-md-4" v-if="config_modelo_cih === 'area'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-area">
                                        Área <span class="text-danger">*</span>
                                    </label>
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
                                            @select="limparComboboxInvalido('cih-form-area')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="form.area_id === 0">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-outra-area">
                                        Especifique <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        id="cih-form-outra-area"
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
                                class="col-12 col-md-4"
                                v-if="config_modelo_cih === 'centro_de_custo' && lista_ccs && AUTENTICADO.temFilial"
                            >
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-cnpj">
                                        Lotação <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormCnpj"
                                            instance-id="form-cnpj"
                                            v-model="form.campoCnpj"
                                            :options="filtroCnpjOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovando"
                                            input-id="cih-form-cnpj"
                                            placeholder-blur="Selecione a lotação..."
                                            empty-message="Nenhuma lotação encontrada."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-cnpj')"
                                            @select="onSelectFormCnpj"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div
                                class="col-12 col-md-4"
                                v-if="config_modelo_cih === 'centro_de_custo'"
                            >
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-centro-custo">
                                        Centro de Custo <span class="text-danger">*</span>
                                    </label>
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
                                            @select="limparComboboxInvalido('cih-form-centro-custo')"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Envolvidos</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`colaborador_${hash}`">
                                        Colaborador(es) <span class="text-danger">*</span>
                                    </label>
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
                                                <td>{{ labelNomeColaboradorModal(colaborador) }}</td>
                                                <td>{{ labelCargoColaboradorModal(colaborador) }}</td>
                                                <td>{{ labelCentroCustoColaboradorModal(colaborador) }}</td>
                                                <td>{{ labelLotacaoColaboradorModal(colaborador) }}</td>
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
                                label="Gestor Aprovação"
                                :model="form"
                                :verifica="aprovando || aprovandoRh || visualizar"
                                :hash="hash"
                                :obrigatorio="true"
                                formsm
                                v-if="config_modelo_cih === 'area'"
                            ></gestoraprovacao>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Detalhes</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-acao">
                                        Ação <span class="text-danger">*</span>
                                    </label>
                                    <textarea
                                        id="cih-form-acao"
                                        class="form-control form-control-sm validacampo"
                                        rows="3"
                                        placeholder="Descreva a ação tomada"
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
                                        titulo="Selecione ou arraste a evidência"
                                        ajuda="PDF, imagens ou documentos · arraste ou clique em Escolher"
                                        @onProgresso="anexoUploadAndamento = true"
                                        @onFinalizado="onAnexoFinalizado"
                                    ></upload>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-obs-lancamento">Observação</label>
                                    <textarea
                                        id="cih-form-obs-lancamento"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.obs_lancamento"
                                        rows="2"
                                        placeholder="Informações complementares do lançamento"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="(visualizar && !form.responsavel_aprovacao) || aprovando">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo gestor!
                    </div>

                    <fieldset v-if="!cadastrando" class="mybp-modal-secao">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.responsavel_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status }} por: {{ form.responsavel_aprovacao.nome }} em
                                    {{ form.data_aprovacao }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-status-gestor">
                                        Status <span class="text-danger" v-if="aprovando">*</span>
                                    </label>
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
                                            @select="limparComboboxInvalido('cih-form-status-gestor')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-obs-gestor">Observação</label>
                                    <textarea
                                        id="cih-form-obs-gestor"
                                        class="form-control form-control-sm"
                                        :disabled="!aprovando || aprovandoRh"
                                        v-model="form.obs_aprovacao"
                                        rows="3"
                                        placeholder="Observação da aprovação do gestor"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="(visualizar && !form.rh_aprovacao) || aprovandoRh">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo RH!
                    </div>

                    <fieldset v-if="visualizar || aprovandoRh" class="mybp-modal-secao">
                        <legend>Aprovação RH</legend>
                        <div class="row">
                            <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.resposta_rh }} por: {{ form.rh_aprovacao.nome }} em
                                    {{ form.data_aprovacao_rh }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-status-rh">
                                        Status <span class="text-danger" v-if="aprovandoRh">*</span>
                                    </label>
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
                                            @select="limparComboboxInvalido('cih-form-status-rh')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="cih-form-obs-rh">Observação</label>
                                    <textarea
                                        id="cih-form-obs-rh"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar && !aprovando && !aprovandoRh"
                                        v-model="form.obs_rh"
                                        rows="3"
                                        placeholder="Observação da aprovação do RH"
                                    ></textarea>
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
                                v-model="controle.dados.campoStatusAprovacao"
                                :options="filtroStatusAprovacaoOpcoes"
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
                        <label class="mybp-label" for="cih-filtro-cnpj">Lotação</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="filtroCnpjOpcoes"
                                :disabled="controle.carregando"
                                input-id="cih-filtro-cnpj"
                                placeholder-blur="Todas as lotações"
                                empty-message="Nenhuma lotação encontrada."
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
                            <mybp-status-badge
                                :variante="chaveStatusLista(item)"
                                :texto="textoStatusLista(item)"
                            />
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

                    <div class="mybp-card-corpo" :class="classeBordaStatusLista(item)">
                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-tag"
                                    label="Tipo"
                                    :valor="labelTipoLista(item)"
                                    forte
                                />
                                <mybp-card-campo
                                    icon="fas fa-calendar-day"
                                    label="Data da ocorrência"
                                    :valor="item.data_lancamento"
                                />
                                <mybp-card-campo
                                    icon="fas fa-user-edit"
                                    label="Lançamento"
                                    :valor="item.responsavel_lancamento?.nome"
                                    :meta="item.created_at || ''"
                                />
                            </div>
                        </section>

                        <section class="mybp-card-secao" v-if="mostrarLinhaEscopoLista(item)">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    v-if="AUTENTICADO.temFilial"
                                    icon="fas fa-building"
                                    label="Lotação"
                                    :valor="labelLotacaoLista(item)"
                                />
                                <mybp-card-campo
                                    v-if="config_modelo_cih === 'centro_de_custo'"
                                    icon="fas fa-sitemap"
                                    label="Centro de Custo"
                                    :valor="labelCentroCustoLista(item)"
                                />
                                <mybp-card-campo
                                    v-if="config_modelo_cih === 'area'"
                                    icon="fas fa-map-marker-alt"
                                    label="Área"
                                    :valor="labelAreaLista(item)"
                                />
                                <mybp-card-campo
                                    v-if="item.gestor_aprovacao?.nome || config_modelo_cih === 'area'"
                                    icon="fas fa-user-tie"
                                    label="Gestor responsável"
                                    :valor="item.gestor_aprovacao?.nome"
                                />
                            </div>
                        </section>

                        <section class="mybp-card-secao mybp-card-secao--acao" v-if="item.acao">
                            <mybp-card-campo
                                icon="fas fa-bolt"
                                label="Ação"
                                :valor="resumoAcaoLista(item)"
                                full
                            />
                        </section>

                        <section class="mybp-card-secao mybp-card-secao--fluxo">
                            <div class="mybp-card-secao__titulo">
                                <i class="fas fa-project-diagram" aria-hidden="true"></i> Fluxo de aprovação
                            </div>
                            <mybp-fluxo-aprovacao :steps="fluxoStepsLista(item)" />
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
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Validacoes from '../../../mixins/Validacoes'
import ComboboxValidation from '../../../mixins/ComboboxValidation'
import { buildOpcoesStatusFluxoAprovacao } from '../../../utils/opcoesStatusFluxoAprovacao'

export default {
    name: 'CIH',
    components: {
        autocomplete,
        DatePicker,
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem,
        MybpCardCampo,
        MybpFluxoAprovacao,
        MybpStatusBadge,
        Upload,
        ControlePaginacao,
        gestoraprovacao
    },
    mixins: [ExportacaoMixin, Validacoes, ComboboxValidation],
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
                    campoStatusAprovacao: '',
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
                d.campoStatusAprovacao ||
                d.campoTags ||
                d.campoAreas ||
                d.campoCentrosDeCusto ||
                d.campoGestores ||
                d.campoCnpj
            )
        },
        filtroStatusAprovacaoOpcoes() {
            return buildOpcoesStatusFluxoAprovacao({ temAprovacaoExtra: false })
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
            const opcoes = [{ value: '', label: 'Todas as lotações' }]
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
                campoStatusAprovacao: this.controle.dados.campoStatusAprovacao,
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
            this.limparComboboxInvalido('cih-form-cnpj')
            this.limparComboboxInvalido('cih-form-centro-custo')
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

            let valido = true
            let mensagem = 'Existem campos obrigatórios não preenchidos'

            if (
                !this.exigirCampoData(this.form.data_lancamento, 'cih-form-data', {
                    toast: false,
                    toastMsg: 'Informe a data da ocorrência'
                })
            ) {
                mensagem = 'Informe a data da ocorrência'
                valido = false
            }

            if (
                !this.exigirCombobox(this.form.tag_id, 'cih-form-tipo', {
                    toast: false,
                    toastMsg: 'Selecione o tipo'
                })
            ) {
                mensagem = 'Selecione o tipo'
                valido = false
            }

            if (this.form.tag_id === 0 && !(this.form.outra_tag || '').trim()) {
                this.marcarInputInvalido('#cih-form-outra-tag', true)
                mensagem = 'Especifique o tipo'
                valido = false
            }

            if (
                this.config_modelo_cih === 'area' &&
                !this.exigirCombobox(this.form.area_id, 'cih-form-area', {
                    toast: false,
                    toastMsg: 'Selecione a área'
                })
            ) {
                mensagem = 'Selecione a área'
                valido = false
            }

            if (
                this.config_modelo_cih === 'centro_de_custo' &&
                this.AUTENTICADO?.temFilial &&
                this.lista_ccs &&
                !this.exigirCombobox(this.form.campoCnpj, 'cih-form-cnpj', {
                    toast: false,
                    toastMsg: 'Selecione a lotação para buscar o centro de custo'
                })
            ) {
                mensagem = 'Selecione a lotação para buscar o centro de custo'
                valido = false
            }

            if (
                this.config_modelo_cih === 'centro_de_custo' &&
                !this.exigirCombobox(this.form.centro_custo_id, 'cih-form-centro-custo', {
                    toast: false,
                    toastMsg: 'Selecione o centro de custo'
                })
            ) {
                mensagem = 'Selecione o centro de custo'
                valido = false
            }

            if (!Array.isArray(this.form.colaboradores) || this.form.colaboradores.length === 0) {
                this.marcarInputInvalido(`#colaborador_${this.hash}`, true)
                mensagem = 'Adicione o colaborador'
                valido = false
            }

            if (!(this.form.acao || '').trim()) {
                this.marcarInputInvalido('#cih-form-acao', true)
                mensagem = 'Informe a ação'
                valido = false
            }

            if (this.anexoObrigatorioAtual && (!this.form.anexos || this.form.anexos.length === 0)) {
                this.marcarInputInvalido('#cih-form-anexo', true)
                mensagem = 'O Campo Anexo não pode ficar vazio'
                valido = false
            }

            if (this.config_modelo_cih === 'area' && this.campoFormVazio(this.form.gestor_id)) {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1)
                mensagem = 'Selecione o gestor de aprovação'
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
                campoStatusAprovacao: '',
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

            if (this.aprovando) {
                if (
                    !this.exigirCombobox(this.form.status, 'cih-form-status-gestor', {
                        toastMsg: 'Selecione o status da aprovação do gestor'
                    })
                ) {
                    return false
                }
            }

            if (this.aprovandoRh) {
                if (
                    !this.exigirCombobox(this.form.resposta_rh, 'cih-form-status-rh', {
                        toastMsg: 'Selecione o status da aprovação do RH'
                    })
                ) {
                    return false
                }
            }

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
            return item.colaboradores?.[0]?.nome || item.colaboradores?.[0]?.curriculo?.nome || 'Não informado'
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
            return colaborador.nome || colaborador.curriculo?.nome || colaborador.label || ''
        },
        labelCargoColaboradorModal(colaborador) {
            if (!colaborador) {
                return ''
            }
            return (
                colaborador.cargo ||
                colaborador.admissao?.cargo ||
                colaborador.vaga_aberta?.vaga?.nome ||
                ''
            )
        },
        labelCentroCustoColaboradorModal(colaborador) {
            if (!colaborador) {
                return 'Não informado'
            }
            return (
                colaborador.centro_custo ||
                colaborador.admissao?.centro_custo?.label ||
                'Não informado'
            )
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
            if (item.status === 'aprovado') {
                return 'gestor'
            }
            return 'aberto'
        },
        classeBordaStatusLista(item) {
            return `mybp-card-corpo--${this.chaveStatusLista(item)}`
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
        fluxoStepsLista(item) {
            if (!item) {
                return []
            }

            let statusGestor = 'aguardando'
            if (item.status === 'aprovado') {
                statusGestor = 'aprovado'
            } else if (item.status === 'reprovado') {
                statusGestor = 'reprovado'
            }

            let statusRh = 'pendente'
            if (item.status === 'reprovado') {
                statusRh = 'cancelado'
            } else if (item.resposta_rh === 'aprovado') {
                statusRh = 'aprovado'
            } else if (item.resposta_rh === 'reprovado') {
                statusRh = 'reprovado'
            } else if (item.status === 'aprovado') {
                statusRh = 'aguardando'
            }

            return [
                {
                    key: 'lancamento',
                    label: 'Lançamento',
                    status: 'aprovado',
                    nome: item.responsavel_lancamento?.nome,
                    data: item.created_at
                },
                {
                    key: 'gestor',
                    label: 'Gestor',
                    status: statusGestor,
                    nome: item.responsavel_aprovacao?.nome,
                    data: item.data_aprovacao
                },
                {
                    key: 'rh',
                    label: 'RH',
                    status: statusRh,
                    nome: item.rh_aprovacao?.nome,
                    data: item.data_aprovacao_rh
                }
            ]
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
.cih-modal-table {
    margin-bottom: 0;
}

.cih-modal-table thead th {
    font-size: 0.72rem;
    font-weight: 600;
    white-space: nowrap;
}

.cih-modal-table tbody td {
    vertical-align: middle;
    font-size: 0.8rem;
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
</style>
