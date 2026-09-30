<template>
    <div>
        <!--Janela de Apagar Férias-->
        <modal id="janelaApagarFerias" :fechar="!formApagar.preload" :titulo="this.formApagar.titulo" ref="modal_janelaApagarFerias">
            <template #conteudo>
                <span v-show="formApagar.preload"> <i class="fa fa-spinner fa-pulse"></i> Apagando Solicitação de Férias... </span>
                <div v-show="!formApagar.preload && !formApagar.delete && !formApagar.erro" class="alert alert-warning" role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-exclamation-triangle"></i><br />
                        Atenção! Deseja excluir essa Solicitação de Férias?
                    </h4>
                </div>

                <div v-show="!formApagar.preload && formApagar.delete && !formApagar.erro" class="alert alert-success" role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-check"></i><br />
                        A Solicitação de Férias foi apagada
                    </h4>
                </div>

                <div v-show="!formApagar.preload && !formApagar.delete && formApagar.erro" class="alert alert-danger" role="alert">
                    <h4 class="text-center">
                        <i class="fas fa-times-circle"></i><br />
                        {{ formApagar.msg }}
                    </h4>
                </div>
            </template>
            <template #rodape>
                <button
                    v-show="!formApagar.preload && !formApagar.delete && !formApagar.erro"
                    class="btn btn-sm mr-1 btn-danger"
                    type="button"
                    @click="apagarFerias()"
                >
                    <i class="fas fa-trash-alt"></i> Apagar Solicitação de Férias
                </button>
            </template>
        </modal>

        <modal :id="hash" :titulo="tituloJanela" :size="90" :ref="hash">
            <template #conteudo>
                <preload v-show="preload" class="text-center"></preload>
                <form
                    v-if="!preload"
                    :id="`form_${hash}`"
                    class="mybp-modal-form mybp-filtros-compactos"
                    onsubmit="return false"
                >
                    <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>

                    <fieldset class="mybp-modal-secao">
                        <legend>Colaborador</legend>
                        <div class="row">
                            <colaborador
                                label="Colaborador"
                                :obrigatorio="true"
                                tipo="ferias"
                                @evtseleciona="onSelecionaColaboradorFerias"
                                @evtreseta="onResetaColaboradorFerias"
                                :model="form"
                                :verifica="modalCamposBloqueados || editando"
                                :hash="hash"
                            ></colaborador>

                            <div class="col-12 col-md-6">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Centro de Custo Atual</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        :value="labelCentroCustoAtual || (form.colaborador_id ? 'Colaborador sem centro de custo' : 'Preenchido ao selecionar o colaborador')"
                                        disabled
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Lotação</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        :value="labelLotacaoAtual || (form.colaborador_id ? 'Não informado' : 'Preenchido ao selecionar o colaborador')"
                                        disabled
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="form.colaborador_id !== ''">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Data de Admissão</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.data_admissao"
                                        readonly
                                        disabled
                                    />
                                </div>
                            </div>

                            <div class="col-12" v-if="colaboradorSemCentroCusto">
                                <div class="alert alert-warning py-2 mb-0" role="alert">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Este colaborador está sem centro de custo. A solicitação pode ser registrada mesmo assim.
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Solicitação</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`ferias-periodo-${hash}`">
                                        Período Aquisitivo <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormPeriodo"
                                            instance-id="form-ferias-periodo"
                                            :input-id="`ferias-periodo-${hash}`"
                                            v-model="form.periodo_aquisitivo_id"
                                            :options="formPeriodoAquisitivoOpcoes"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-ferias-periodo')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="ultimaData !== ''">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Última Data</label>
                                    <input type="text" class="form-control form-control-sm" v-model="ultimaData" readonly disabled />
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`ferias-tem-falta-${hash}`">Tem Falta?</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormTemFalta"
                                            instance-id="form-ferias-tem-falta"
                                            :input-id="`ferias-tem-falta-${hash}`"
                                            v-model="formTemFaltasModel"
                                            :options="formSimNaoOpcoes"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-ferias-tem-falta')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="form.tem_faltas === true">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`ferias-qnt-faltas-${hash}`">Quantidade de faltas</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormQntFaltas"
                                            instance-id="form-ferias-qnt-faltas"
                                            :input-id="`ferias-qnt-faltas-${hash}`"
                                            v-model="form.qnt_faltas"
                                            :options="formQntFaltasOpcoes"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="40"
                                            @opening="fecharOutrosComboboxes('form-ferias-qnt-faltas')"
                                            @select="onSelectQntFaltas"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="!aprovando">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Quantidade de dias disponíveis</label>
                                    <input type="text" class="form-control form-control-sm" v-model="qntDias" readonly disabled />
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`ferias-qnt-dias-${hash}`">
                                        Dias de férias <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormQntDias"
                                            instance-id="form-ferias-qnt-dias"
                                            :input-id="`ferias-qnt-dias-${hash}`"
                                            v-model="form.qnt_dias"
                                            :options="formQntDiasOpcoes"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="40"
                                            @opening="fecharOutrosComboboxes('form-ferias-qnt-dias')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                                    <label class="mybp-label">Data da saída <span class="text-danger">*</span></label>
                                    <datepicker
                                        :id="`ferias-data-saida-${hash}`"
                                        label=""
                                        formsm
                                        class="corrigiDatepicker"
                                        v-model="form.data_saida"
                                        :disabled="modalCamposBloqueados"
                                    ></datepicker>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Data do retorno</label>
                                    <input type="text" class="form-control form-control-sm" v-model="dataRetorno" readonly disabled />
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="!aprovando">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Dias de saldo</label>
                                    <input type="text" class="form-control form-control-sm" v-model="qntSaldo" readonly disabled />
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`ferias-abono-${hash}`">Abono Pecuniário</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormAbono"
                                            instance-id="form-ferias-abono"
                                            :input-id="`ferias-abono-${hash}`"
                                            v-model="formAbonoModel"
                                            :options="formSimNaoOpcoes"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-ferias-abono')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`ferias-adiantamento-${hash}`">Adiantamento Décimo Terceiro</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormAdiantamento"
                                            instance-id="form-ferias-adiantamento"
                                            :input-id="`ferias-adiantamento-${hash}`"
                                            v-model="formAdiantamentoModel"
                                            :options="formSimNaoOpcoes"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-ferias-adiantamento')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <gestoraprovacao
                                label="Gestor Aprovação"
                                :obrigatorio="true"
                                formsm
                                :model="form"
                                :verifica="visualizar || aprovando || aprovandoExtra || aprovandoRh"
                                :hash="hash"
                            ></gestoraprovacao>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Detalhes</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.obs_solicitante"
                                        rows="3"
                                        placeholder="Informações relevantes sobre as férias"
                                        :disabled="modalCamposBloqueados"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-12" v-if="visualizar && form.solicitante">
                                <p class="mb-0 text-muted">
                                    Solicitação feita por: {{ form.solicitante }}
                                    <template v-if="form.data_solicitacao"> — {{ form.data_solicitacao }}</template>
                                </p>
                            </div>

                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Anexos</label>
                                    <upload
                                        :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :url="url_anexo"
                                        :tipos="mimes"
                                        label="Selecionar"
                                        :leitura="!podeanexar"
                                        @onProgresso="anexoUploadAndamento = true"
                                        @onFinalizado="anexoUploadAndamento = false"
                                    ></upload>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="!form.data_aprovacao_gestor && !cadastrando">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo gestor!
                    </div>

                    <fieldset v-if="visualizar || aprovando" class="mybp-modal-secao">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.gestor_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_gestor }} por: {{ form.gestor_aprovacao.nome }} em {{ form.data_aprovacao_gestor }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`ferias-status-gestor-${hash}`">
                                        Status <span class="text-danger" v-if="aprovando">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusGestor"
                                            instance-id="form-ferias-status-gestor"
                                            :input-id="`ferias-status-gestor-${hash}`"
                                            v-model="form.status_aprovacao_gestor"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovando || aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-ferias-status-gestor')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovando || aprovandoExtra || aprovandoRh"
                                        v-model="form.obs_gestor"
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="aprovandoExtra">
                        Esta solicitação ainda não foi aprovada ou reprovada pela {{ nomeAprovacaoExtra }}!
                    </div>

                    <fieldset v-if="visualizar || aprovandoExtra" class="mybp-modal-secao">
                        <div v-if="!temAprovacaoExtra" class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Esta empresa não possui aprovação extra configurada.
                        </div>

                        <legend v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</legend>
                        <div class="row" v-if="temAprovacaoExtra">
                            <div v-if="!aprovandoExtra && form.aprovacao_extra" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_extra }} por: {{ form.aprovacao_extra.nome }} em {{ form.data_aprovacao_extra }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`ferias-status-extra-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoExtra">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusExtra"
                                            instance-id="form-ferias-status-extra"
                                            :input-id="`ferias-status-extra-${hash}`"
                                            v-model="form.status_aprovacao_extra"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-ferias-status-extra')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovandoExtra || aprovandoRh"
                                        v-model="form.obs_aprovacao_extra"
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="aprovandoRh">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                    <fieldset v-if="visualizar || aprovandoRh" class="mybp-modal-secao">
                        <legend>Aprovação RH</legend>
                        <div class="row">
                            <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao.nome }} em {{ form.data_aprovacao_rh }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`ferias-status-rh-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoRh">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusRh"
                                            instance-id="form-ferias-status-rh"
                                            :input-id="`ferias-status-rh-${hash}`"
                                            v-model="form.status_aprovacao_rh"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-ferias-status-rh')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovandoRh"
                                        v-model="form.obs_rh"
                                        rows="2"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="cadastrando && !preload" @click.prevent="cadastrar">
                    <i class="fa fa-save"></i> Cadastrar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovando && !preload" @click.prevent="aprovarGestor">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovandoExtra && !preload" @click.prevent="aprovarExtra">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preload" @click.prevent="editar">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovandoRh && !preload" @click.prevent="aprovarRh">
                    <i class="fa fa-save"></i> Salvar
                </button>
            </template>
        </modal>

        <modal id="janelaAtualizaStatus" titulo="Deseja APROVAR ou REPROVAR todos os colaboradores selecionados?" :centralizada="true" label-fechar="Fechar" ref="modal_janelaAtualizaStatus">
            <template #conteudo>
                <div class="col-12">
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea class="form-control form-control-sm" v-model="formConfirmacao.obs_aprovacao" cols="5" rows="5"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-sm mr-1 btn-success" @click="confirmaAtualizacaoStatus('aprovado')">APROVAR</button>
                    <button type="button" class="btn btn-sm mr-1 btn-danger" @click="confirmaAtualizacaoStatus('reprovado')">REPROVAR</button>
                </div>
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
                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-periodo-aquisitivo">Período aquisitivo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPeriodoAquisitivo"
                                instance-id="ferias-periodo-aquisitivo"
                                input-id="ferias-filtro-periodo-aquisitivo"
                                v-model="controle.dados.filtroPeriodoAquisitivo"
                                :options="opcoesPeriodoAquisitivo"
                                :disabled="controle.carregando"
                                placeholder-blur="Últimos 3 períodos"
                                empty-message="Nenhum período encontrado."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('ferias-periodo-aquisitivo')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <date-range-filter
                    v-model:enabled="controle.dados.filtroPeriodo"
                    v-model:start-date="controle.dados.dataInicio"
                    v-model:end-date="controle.dados.dataFim"
                    :disabled="controle.carregando || controle.dados.filtroVencimento || controle.dados.filtroInicioFerias"
                    :id-suffix="`cadastrado_${hash}`"
                    label="Período cadastrado"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <date-range-filter
                    v-model:enabled="controle.dados.filtroVencimento"
                    v-model:start-date="controle.dados.dataInicioVencimento"
                    v-model:end-date="controle.dados.dataFimVencimento"
                    :disabled="controle.carregando || controle.dados.filtroPeriodo || controle.dados.filtroInicioFerias"
                    :id-suffix="`vencimento_${hash}`"
                    label="Período de vencimento"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <date-range-filter
                    v-model:enabled="controle.dados.filtroInicioFerias"
                    v-model:start-date="controle.dados.dataInicioFerias"
                    v-model:end-date="controle.dados.dataFimFerias"
                    :disabled="controle.carregando || controle.dados.filtroVencimento || controle.dados.filtroPeriodo"
                    :id-suffix="`inicioferias_${hash}`"
                    label="Início das férias"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-busca">
                            Colaborador / CPF
                            <span v-if="buscaUnificadaEhCpf" class="ferias-filtro-hint">CPF</span>
                        </label>
                        <input
                            id="ferias-filtro-busca"
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
                        <label class="mybp-label" for="ferias-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="ferias-status"
                                input-id="ferias-filtro-status"
                                v-model="controle.dados.campoStatusAprovacao"
                                :options="opcoesStatus"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhum status encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('ferias-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-cnpj">Lotação (CNPJ)</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="ferias-cnpj"
                                input-id="ferias-filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="opcoesCnpj"
                                :disabled="controle.carregando"
                                placeholder-blur="Todas as lotações"
                                empty-message="Nenhuma lotação encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('ferias-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="lista_ccs" :class="temFilial ? 'col-12 col-md-8' : 'col-12'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                instance-id="ferias-cc"
                                input-id="ferias-filtro-cc"
                                v-model="controle.dados.campoCentroCusto"
                                :options="opcoesCentroCusto"
                                :disabled="controle.carregando || !opcoesCentroCusto.length"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro de custo encontrado."
                                :max-results="200"
                                @opening="fecharOutrosComboboxes('ferias-cc')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-ordenacao">Ordenar por</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroOrdenacao"
                                instance-id="ferias-ordenacao"
                                input-id="ferias-filtro-ordenacao"
                                v-model="controle.dados.ordenacao"
                                :options="opcoesOrdenacao"
                                :disabled="controle.carregando"
                                placeholder-blur="Mais recentes"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('ferias-ordenacao')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="ferias-filtro-pages">Por página</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPages"
                                instance-id="ferias-pages"
                                input-id="ferias-filtro-pages"
                                v-model="campoPagesCombo"
                                :options="opcoesPages"
                                :disabled="controle.carregando"
                                placeholder-blur="50"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('ferias-pages')"
                                @select="onSelectFiltro"
                            />
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
                    class="btn btn-sm btn-primary"
                    :disabled="controle.carregando"
                    @click.prevent="formNovo(); $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                >
                    Solicitar
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click.prevent="exportaExcel()"
                    :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
                >
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    v-show="selecionados.length > 0"
                    :disabled="selecionados.length === 0"
                    @click.prevent="$refs.modal_janelaAtualizaStatus && $refs.modal_janelaAtualizaStatus.abrirModal()"
                >
                    Atualizar Status <span class="badge badge-light">{{ selecionados.length }}</span>
                </button>
            </template>
        </FiltroListagem>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <!-- Checkbox Geral -->
            <!-- <div class="checkbox-geral-container" v-show="!controle.carregando && lista.length > 0">
                <label class="checkbox-geral-label">
                    <input
                        type="checkbox"
                        class="custom-checkbox"
                        :style="naoAprovados.length === 0 ? 'cursor: not-allowed' : 'cursor: pointer'"
                        :disabled="naoAprovados.length === 0"
                        :checked="tudoMarcado"
                        @click="selecionaTodos"
                    />
                    <span class="ml-2">Selecionar todos</span>
                </label>
            </div> -->

            <!-- Cards -->
            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
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
                                        @click.prevent="formOpen(item.id); visualizar = false; aprovando = true; aprovandoExtra = false; aprovandoRh = false; podeanexar = false; editando = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="item.gestor_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor"
                                    >
                                        <i class="fa fa-user-check mr-1"></i> Aprovação Gestor
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra || 'Aprovação Extra'"
                                        @click.prevent="formOpen(item.id); visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; podeanexar = false; editando = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="
                                            temAprovacaoExtra &&
                                            podeAprovarExtra &&
                                            item.status_aprovacao_gestor === 'aprovado' &&
                                            !item.aprovacao_extra_nome &&
                                            !item.aprovado_via_script &&
                                            !item.rh_aprovacao_id
                                        "
                                    >
                                        <i class="fa fa-user-check mr-1"></i> {{ nomeAprovacaoExtra || 'Aprovação Extra' }}
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        @click.prevent="formOpen(item.id); visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; editando = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="
                                            ((item.status_aprovacao_gestor === 'aprovado' && !temAprovacaoExtra) ||
                                                item.status_aprovacao_extra === 'aprovado') &&
                                            !item.aprovado_via_script &&
                                            item.rh_aprovacao_id === null &&
                                            aprovaRh
                                        "
                                    >
                                        <i class="fa fa-users mr-1"></i> Aprovação Rh
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Editar"
                                        @click.prevent="formOpen(item.id); visualizar = false; aprovando = false; aprovandoExtra = false; aprovandoRh = false; editando = true; podeanexar = true; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="item.gestor_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor && permissoes.update"
                                    >
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Apagar"
                                        @click.prevent="formApagarFerias(item.id); $refs.modal_janelaApagarFerias && $refs.modal_janelaApagarFerias.abrirModal()"
                                        v-if="item.gestor_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor && permissoes.delete"
                                    >
                                        <i class="fa fa-trash mr-1"></i> Apagar
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="formOpen(item.id); visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = false; editando = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
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
                                    icon="fas fa-umbrella-beach"
                                    label="Férias"
                                    :valor="periodoFeriasLista(item)"
                                    forte
                                />
                                <mybp-card-campo
                                    icon="fas fa-clock"
                                    label="Dias"
                                    :valor="diasFeriasLista(item)"
                                />
                                <mybp-card-campo
                                    icon="fas fa-calendar-check"
                                    label="Período aquisitivo"
                                    :valor="periodoAquisitivoLista(item)"
                                />
                            </div>
                        </section>

                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-building"
                                    label="Lotação"
                                    :valor="item.lotacao"
                                />
                                <mybp-card-campo
                                    icon="fas fa-sitemap"
                                    label="Centro de custo"
                                    :valor="centroCustoLista(item)"
                                />
                                <mybp-card-campo
                                    icon="fas fa-calendar-alt"
                                    label="Admissão"
                                    :valor="dataAdmissaoLista(item)"
                                />
                            </div>
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
        </div>

        <controle-paginacao
            class="d-flex justify-content-center"
            id="controle"
            ref="componente"
            :url="urlPaginacao"
            :por-pagina="controle.dados.pages"
            :dados="controle.dados"
            v-on:carregou="carregou"
            v-on:carregando="carregando"
        />
    </div>
</template>

<script>
import colaborador from '../../Colaborador'
import gestoraprovacao from '../../GestorAprovacao'
import configselect2 from '../../../components/Select2/mixSelec2'
import Select2 from '../../../components/Select2/Select2'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Utils from '../../../mixins/Utils'
import configuracoes from '../../../mixins/Configuracoes'
import Upload from '../../Upload'
import Validacoes from '../../../mixins/Validacoes'
import DateRangeFilter from '../../DateRangeFilter.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'

export default {
    mixins: [configselect2, ExportacaoMixin, Utils, Validacoes, configuracoes],
    inject: {
        atualizarUrlMovimentacao: { default: () => () => {} }
    },
    data() {
        return {
            tituloJanela: 'Solicitacao de férias',
            preload: false,
            cadastrando: false,
            editando: false,
            visualizar: false,
            aprovando: false,
            aprovandoExtra: false,
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaRh: false,
            podeAprovarExtra: false,
            temAprovacaoExtra: false,
            nomeAprovacaoExtra: '',
            preloadExportacao: false,

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,
            caminho_gestor: `autocomplete/todos-gestores-ativos`,
            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/export`,

            url_anexo: `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`,
            anexoUploadAndamento: false,
            podeanexar: false,
            mimes: [],
            permissoes: [],

            selecionados: [],
            selecionaTudo: false,

            dropdownAbertoKey: null,

            formConfirmacao: {
                selecionados: [],
                obs_aprovacao: '',
                status_aprovacao: ''
            },
            formConfirmacaoDefault: null,

            formApagar: {
                id: null,
                titulo: '',
                preload: false,
                delete: false,
                erro: false,
                msg: ''
            },

            form: {
                id: '',
                colaborador_id: '',
                autocomplete_label_colaborador: '',
                autocomplete_label_colaborador_anterior: '',

                admissao_id: '',
                periodo_aquisitivo_id: '',
                data_saida: '',
                data_retorno: '',
                ultima_data: '',
                qnt_dias: 5,
                dias_saldo: '',
                tem_faltas: false,
                qnt_faltas: 0,
                solicitante: '',
                obs_solicitante: '',
                data_solicitacao: '',
                gestor_aprovacao: null,
                obs_gestor: '',
                status_aprovacao_gestor: '',
                data_aprovacao_gestor: '',
                aprovacao_extra: null,
                obs_aprovacao_extra: '',
                status_aprovacao_extra: '',
                data_aprovacao_extra: '',
                data_aprovacao_rh: '',
                rh_aprovacao: null,
                obs_rh: '',
                status_aprovacao_rh: '',
                aprovado_via_script: false,
                abono_pecuniario: false,
                adiantamento_decimo_terceiro: false,
                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',
                centro_custo: null,
                centro_custo_id: '',
                filial: false,
                centro_custo_filial_id: '',
                data_admissao: '',
                anexos: [],
                anexosDel: []
            },

            formDefault: null,
            lista: [],
            periodos: [],
            ultimaData: '',
            periodo_label: '',
            centro_custos: [],
            lista_ccs: null,

            /**
             *
             * aprovaRH -> apenas para mostrar o formulário
             * aprova_RH -> permissão
             *
             * **/

            // colaborador_ativo: `autocomplete/colaboradores/`,
            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    filtroPeriodo: false,
                    filtroPeriodoAquisitivo: '',
                    dataInicio: '',
                    dataFim: '',
                    campoBusca: '',
                    campoCPF: '',
                    campoStatusAprovacao: '',
                    campoCnpj: '',
                    campoCentroCusto: '',
                    pages: 50,
                    filtroVencimento: false,
                    dataInicioVencimento: '',
                    dataFimVencimento: '',
                    filtroInicioFerias: false,
                    dataInicioFerias: '',
                    dataFimFerias: '',
                    token: '',
                    ordenacao: 'created_at_desc'
                }
            }
        }
    },
    components: {
        colaborador,
        gestoraprovacao,
        Select2,
        Upload,
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem,
        MybpCardCampo,
        MybpFluxoAprovacao,
        MybpStatusBadge
    },
    mounted() {
        this.urlParamGet()
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formConfirmacaoDefault = _.cloneDeep(this.formConfirmacao) //copia
        this.$nextTick(() => {
            this.atualizar()
            this.periodosAquisitivos()
        })
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    watch: {
        'controle.dados': {
            handler() {
                if (this._syncUrlTimer) clearTimeout(this._syncUrlTimer)
                this._syncUrlTimer = setTimeout(() => this.syncUrlFiltros(), 400)
            },
            deep: true
        }
    },
    computed: {
        naoAprovados() {
            return this.lista.filter((item) => {
                if (item.status_aprovacao === null) {
                    return item.id
                }
            })
        },
        tudoMarcado() {
            let totalAprovado = this.naoAprovados.length
            let totalEncontrado = 0

            if (totalAprovado === 0) {
                return false
            }

            this.naoAprovados.forEach((item) => {
                let id = item.id
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                } else {
                    return false
                }
            })
            let resultado = totalAprovado === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        },
        qntDias() {
            if (this.form.qnt_faltas <= 5) {
                return 30
            }
            if (this.form.qnt_faltas >= 6 && this.form.qnt_faltas <= 14) {
                return 24
            }
            if (this.form.qnt_faltas >= 15 && this.form.qnt_faltas <= 23) {
                return 18
            }
            if (this.form.qnt_faltas >= 24 && this.form.qnt_faltas <= 32) {
                return 12
            }
            if (this.form.qnt_faltas >= 33) {
                return 0
            }
        },
        qntSaldo() {
            this.form.dias_saldo = this.qntDias - this.form.qnt_dias

            return this.form.dias_saldo
        },
        dataRetorno() {
            let dias_ferias = this.form.qnt_dias
            let data_saida = this.form.data_saida.split('/')
            let data_saida_convert = data_saida[2] + '-' + data_saida[1] + '-' + data_saida[0]

            let data_retorno = new Date(data_saida_convert)
            data_retorno.setDate(data_retorno.getDate() + dias_ferias)
            let data_retorno_ptbr =
                this.padTo2Digits(data_retorno.getDate()) + '/' + this.padTo2Digits(data_retorno.getMonth() + 1) + '/' + data_retorno.getFullYear()
            this.form.data_retorno = data_retorno_ptbr

            return data_retorno_ptbr
        },
        por_pagina() {
            return [20, 50, 100, 150]
        },
        paramsExport() {
            return this.controle.dados
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            let total = [d.campoBusca, d.campoCPF, d.campoStatusAprovacao, d.campoCentroCusto, d.filtroPeriodoAquisitivo].filter(
                (v) => v !== '' && v !== null && v !== undefined
            ).length
            if (this.temFilial && d.campoCnpj) total++
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) total++
            if (d.filtroVencimento && d.dataInicioVencimento && d.dataFimVencimento) total++
            if (d.filtroInicioFerias && d.dataInicioFerias && d.dataFimFerias) total++
            if (d.ordenacao && d.ordenacao !== 'created_at_desc') total++
            if (Number(d.pages) !== 50) total++
            return total
        },
        campoBuscaUnificada() {
            const d = this.controle.dados
            if (d.campoCPF) return d.campoCPF
            return d.campoBusca || ''
        },
        buscaUnificadaEhCpf() {
            return !!this.controle.dados.campoCPF
        },
        filtroListaCentroCustoCnpj() {
            if (!this.lista_ccs) return []
            if (this.controle.dados.campoCnpj !== '' && this.temFilial) {
                return this.lista_ccs.centros_custos[this.controle.dados.campoCnpj] || []
            }
            if (!this.temFilial) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                return keys.length ? this.lista_ccs.centros_custos[keys[0]] || [] : []
            }
            const all = []
            Object.values(this.lista_ccs.centros_custos || {}).forEach((lista) => {
                ;(lista || []).forEach((item) => all.push(item))
            })
            return all
        },
        opcoesCnpj() {
            const opts = [{ value: '', label: 'Todas as lotações' }]
            if (!this.lista_ccs || !this.lista_ccs.cnpjs) return opts
            Object.keys(this.lista_ccs.cnpjs).forEach((key) => {
                const item = this.lista_ccs.cnpjs[key]
                opts.push({
                    value: key,
                    label: `${item.nome_fantasia} - ${item.cnpj}`,
                    meta: item.cnpj
                })
            })
            return opts
        },
        opcoesCentroCusto() {
            const opts = [{ value: '', label: 'Todos os centros' }]
            ;(this.filtroListaCentroCustoCnpj || []).forEach((item) => {
                const value = item.matriz ? item.id : item.filial_id
                if (value == null || value === '') return
                opts.push({
                    value: String(value),
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        opcoesPeriodoAquisitivo() {
            const opts = [{ value: '', label: 'Últimos 3 períodos' }]
            ;(this.periodos || []).forEach((item) => {
                opts.push({ value: String(item.id), label: item.label })
            })
            return opts
        },
        opcoesStatus() {
            const opts = [
                { value: '', label: 'Todos os status' },
                { value: 'aberto', label: 'Em aberto' },
                { value: 'aprovado_gestor', label: 'Aprovado Gestor' }
            ]
            if (this.temAprovacaoExtra) {
                opts.push({
                    value: 'aprovado_extra',
                    label: `Aprovado ${this.nomeAprovacaoExtra || 'Extra'}`
                })
            }
            opts.push(
                { value: 'aprovado_rh', label: 'Aprovado Rh' },
                { value: 'reprovado', label: 'Reprovado' }
            )
            return opts
        },
        opcoesOrdenacao() {
            return [
                { value: 'created_at_desc', label: 'Mais recentes' },
                { value: 'created_at_asc', label: 'Mais antigos' },
                { value: 'updated_at_desc', label: 'Última modificação' }
            ]
        },
        opcoesPages() {
            return this.por_pagina.map((n) => ({ value: String(n), label: String(n) }))
        },
        campoPagesCombo: {
            get() {
                return String(this.controle.dados.pages || 50)
            },
            set(valor) {
                this.controle.dados.pages = parseInt(valor, 10) || 50
            }
        },
        modalCamposBloqueados() {
            return this.visualizar || this.aprovando || this.aprovandoExtra || this.aprovandoRh
        },
        formSimNaoOpcoes() {
            return [
                { value: '1', label: 'Sim' },
                { value: '0', label: 'Não' }
            ]
        },
        formStatusAprovacaoOpcoes() {
            return [
                { value: '', label: 'Selecione...' },
                { value: 'aprovado', label: 'Aprovar' },
                { value: 'reprovado', label: 'Reprovar' }
            ]
        },
        centroCustoSelecionado() {
            if ([undefined, null, ''].includes(this.form.centro_custo_id)) {
                return []
            }
            const id = Number(this.form.centro_custo_id)
            const centroSelecionado =
                _.find(this.centro_custos, { id }) ||
                _.find(this.centro_custos, { id: this.form.centro_custo_id }) ||
                _.find(this.centro_custos, { id: String(this.form.centro_custo_id) })
            if (centroSelecionado && centroSelecionado.filiais && centroSelecionado.filiais.length) {
                return centroSelecionado.filiais
            }
            return []
        },
        labelCentroCustoAtual() {
            if ([undefined, null, ''].includes(this.form.centro_custo_id)) {
                return ''
            }
            const id = String(this.form.centro_custo_id)
            const item =
                _.find(this.centro_custos, (cc) => String(cc.id) === id) ||
                _.find(this.centro_custos, { id: this.form.centro_custo_id })
            return item && item.label ? item.label : ''
        },
        colaboradorSemCentroCusto() {
            return !!this.form.colaborador_id && [undefined, null, '', 0, '0'].includes(this.form.centro_custo_id)
        },
        labelLotacaoAtual() {
            if ([undefined, null, ''].includes(this.form.colaborador_id) && [undefined, null, ''].includes(this.form.centro_custo_id)) {
                return ''
            }

            const formatLotacao = (nomeFantasia, razaoSocial, cnpj) => {
                const nome = String(nomeFantasia || razaoSocial || '').trim()
                const doc = String(cnpj || '').trim()
                if (!nome && !doc) return ''
                if (!nome) return doc
                if (!doc) return nome
                return `${nome} - ${doc}`
            }

            const ehFilial =
                this.form.filial === true || this.form.filial === 1 || this.form.filial === '1'
            const filiaisAuth =
                (this.authconfiguracao && this.authconfiguracao.cnpjs && this.authconfiguracao.cnpjs.filiais) || []

            if (ehFilial && this.form.centro_custo_filial_id) {
                const ccfId = Number(this.form.centro_custo_filial_id)
                const vinculo =
                    _.find(this.centroCustoSelecionado, { id: ccfId }) ||
                    _.find(this.centroCustoSelecionado, { id: this.form.centro_custo_filial_id })
                const filialRel = vinculo && (vinculo.filial || vinculo.Filial)
                if (filialRel) {
                    let viaRel = formatLotacao(filialRel.nome_fantasia, filialRel.razao_social, filialRel.cnpj)
                    if (viaRel && String(filialRel.cnpj || '').trim()) return viaRel

                    const clienteFilialId = vinculo.cliente_filial_id
                    if (clienteFilialId != null && clienteFilialId !== '') {
                        const cf = _.find(filiaisAuth, { id: clienteFilialId })
                        if (cf) {
                            const viaAuth = formatLotacao(cf.nome_fantasia || cf.nome_fntasia, cf.razao_social, cf.cnpj)
                            if (viaAuth) return viaAuth
                        }
                    }

                    if (viaRel) return viaRel
                }

                if (vinculo && vinculo.cliente_filial_id != null && vinculo.cliente_filial_id !== '') {
                    const cf = _.find(filiaisAuth, { id: vinculo.cliente_filial_id })
                    if (cf) {
                        const viaAuth = formatLotacao(cf.nome_fantasia || cf.nome_fntasia, cf.razao_social, cf.cnpj)
                        if (viaAuth) return viaAuth
                    }
                }
            }

            const matrizAuth = this.authconfiguracao && this.authconfiguracao.cnpjs && this.authconfiguracao.cnpjs.matriz
            if (matrizAuth) {
                const viaMatriz = formatLotacao(matrizAuth.nome_fantasia, matrizAuth.razao_social, matrizAuth.cnpj)
                if (viaMatriz) return viaMatriz
            }

            if (this.lista_ccs && this.lista_ccs.cnpjs) {
                const matrizLista = Object.values(this.lista_ccs.cnpjs).find((item) => item && item.matriz)
                if (matrizLista) {
                    return formatLotacao(matrizLista.nome_fantasia, matrizLista.razao_social, matrizLista.cnpj)
                }
            }

            return ''
        },
        formPeriodoAquisitivoOpcoes() {
            const opts = [{ value: '', label: 'Selecione...' }]
            ;(this.periodos || []).forEach((item) => {
                if (item == null || item.id == null || item.id === '') return
                opts.push({ value: String(item.id), label: item.label || String(item.id) })
            })
            return opts
        },
        formQntFaltasOpcoes() {
            const opts = []
            for (let cont = 1; cont <= 32; cont++) {
                opts.push({ value: cont, label: String(cont) })
            }
            return opts
        },
        formQntDiasOpcoes() {
            const max = Number(this.qntDias) || 0
            const opts = [{ value: '', label: 'Selecione...' }]
            for (let cont = 5; cont <= max; cont++) {
                opts.push({ value: cont, label: String(cont) })
            }
            return opts
        },
        formTemFaltasModel: {
            get() {
                return this.form.tem_faltas === true || this.form.tem_faltas === 1 || this.form.tem_faltas === '1' ? '1' : '0'
            },
            set(valor) {
                this.form.tem_faltas = valor === true || valor === 1 || valor === '1'
                this.verificaFaltas()
            }
        },
        formAbonoModel: {
            get() {
                return this.form.abono_pecuniario === true || this.form.abono_pecuniario === 1 || this.form.abono_pecuniario === '1'
                    ? '1'
                    : '0'
            },
            set(valor) {
                this.form.abono_pecuniario = valor === true || valor === 1 || valor === '1'
            }
        },
        formAdiantamentoModel: {
            get() {
                return this.form.adiantamento_decimo_terceiro === true ||
                    this.form.adiantamento_decimo_terceiro === 1 ||
                    this.form.adiantamento_decimo_terceiro === '1'
                    ? '1'
                    : '0'
            },
            set(valor) {
                this.form.adiantamento_decimo_terceiro = valor === true || valor === 1 || valor === '1'
            }
        }
    },
    methods: {
        toggleDropdown(itemId) {
            if (!itemId) {
                return
            }
            const key = `mov_ferias:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        nomeColaboradorLista(item) {
            return item?.admissao?.feedback?.curriculo?.nome || 'Não informado'
        },
        centroCustoLista(item) {
            return item?.admissao?.centro_custo?.label || ''
        },
        dataAdmissaoLista(item) {
            return item?.admissao?.data_admissao || ''
        },
        periodoFeriasLista(item) {
            if (!item?.data_saida && !item?.data_retorno) return ''
            return `${item.data_saida || '—'} até ${item.data_retorno || '—'}`
        },
        diasFeriasLista(item) {
            if (item?.qnt_dias === null || item?.qnt_dias === undefined) return ''
            const saldo = item.dias_saldo ?? '—'
            return `${item.qnt_dias} (Saldo: ${saldo})`
        },
        periodoAquisitivoLista(item) {
            const label = item?.periodo_aquisitivo?.label
            if (!label) return ''
            return item.ultima_data ? `${label} — Limite: ${item.ultima_data}` : label
        },
        chaveStatusLista(item) {
            if (!item) return 'aberto'
            if (
                item.status_aprovacao_gestor === 'reprovado' ||
                item.status_aprovacao_extra === 'reprovado' ||
                item.status_aprovacao_rh === 'reprovado'
            ) {
                return 'reprovado'
            }
            if (item.status_aprovacao_rh === 'aprovado') return 'rh'
            if (this.temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') return 'extra'
            if (item.status_aprovacao_gestor === 'aprovado') return 'gestor'
            return 'aberto'
        },
        classeBordaStatusLista(item) {
            return `mybp-card-corpo--${this.chaveStatusLista(item)}`
        },
        textoStatusLista(item) {
            const chave = this.chaveStatusLista(item)
            if (chave === 'reprovado') return 'Reprovado'
            if (chave === 'rh') return 'Aprovado RH'
            if (chave === 'extra') return `Aprovado ${this.nomeAprovacaoExtra || 'Extra'}`
            if (chave === 'gestor') return 'Aprovado Gestor'
            return 'Em aberto'
        },
        fluxoStepsLista(item) {
            if (!item) return []
            const steps = [
                {
                    key: 'solicitante',
                    label: 'Solicitante',
                    status: 'aprovado',
                    nome: item.solicitante?.nome,
                    data: item.data_solicitacao
                },
                {
                    key: 'gestor',
                    label: 'Gestor',
                    status:
                        item.status_aprovacao_gestor === 'aprovado'
                            ? 'aprovado'
                            : item.status_aprovacao_gestor === 'reprovado'
                              ? 'reprovado'
                              : 'aguardando',
                    nome: item.gestor_aprovacao?.nome,
                    data: item.data_aprovacao_gestor
                }
            ]

            if (this.temAprovacaoExtra) {
                let statusExtra = 'pendente'
                if (item.status_aprovacao_gestor === 'reprovado') {
                    statusExtra = 'cancelado'
                } else if (item.status_aprovacao_extra === 'aprovado') {
                    statusExtra = 'aprovado'
                } else if (item.status_aprovacao_extra === 'reprovado') {
                    statusExtra = 'reprovado'
                } else if (item.status_aprovacao_gestor === 'aprovado') {
                    statusExtra = 'aguardando'
                }
                steps.push({
                    key: 'extra',
                    label: this.nomeAprovacaoExtra || 'Extra',
                    status: statusExtra,
                    nome: item.aprovacao_extra_nome,
                    data: item.data_aprovacao_extra
                })
            }

            let statusRh = 'pendente'
            if (
                item.status_aprovacao_gestor === 'reprovado' ||
                (this.temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')
            ) {
                statusRh = 'cancelado'
            } else if (item.status_aprovacao_rh === 'aprovado') {
                statusRh = 'aprovado'
            } else if (item.status_aprovacao_rh === 'reprovado') {
                statusRh = 'reprovado'
            } else if (
                (this.temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                (!this.temAprovacaoExtra && item.status_aprovacao_gestor === 'aprovado')
            ) {
                statusRh = 'aguardando'
            }

            steps.push({
                key: 'rh',
                label: 'RH',
                status: statusRh,
                nome: item.rh_aprovacao?.nome,
                data: item.data_aprovacao_rh
            })

            return steps
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `mov_ferias:${itemId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        onClickOutside(event) {
            if (event && event.target && event.target.closest && event.target.closest('.dropdown')) {
                return
            }
            this.dropdownAbertoKey = null
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            this.controle.dados.token = urlParams.get('token') || ''
            if (urlParams.get('pages')) this.controle.dados.pages = parseInt(urlParams.get('pages'), 10) || 50
            if (urlParams.get('ordenacao')) this.controle.dados.ordenacao = urlParams.get('ordenacao')
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoCPF')) this.controle.dados.campoCPF = urlParams.get('campoCPF')
            if (urlParams.get('campoStatusAprovacao')) this.controle.dados.campoStatusAprovacao = urlParams.get('campoStatusAprovacao')
            if (urlParams.get('campoCentroCusto')) this.controle.dados.campoCentroCusto = urlParams.get('campoCentroCusto')
            if (urlParams.get('filtroPeriodoAquisitivo')) this.controle.dados.filtroPeriodoAquisitivo = urlParams.get('filtroPeriodoAquisitivo')
            if (urlParams.get('dataInicio')) this.controle.dados.dataInicio = urlParams.get('dataInicio')
            if (urlParams.get('dataFim')) this.controle.dados.dataFim = urlParams.get('dataFim')
            if (urlParams.get('dataInicio') || urlParams.get('dataFim')) this.controle.dados.filtroPeriodo = true
            const filtroPeriodoUrl = urlParams.get('filtroPeriodo')
            if (filtroPeriodoUrl === '1' || filtroPeriodoUrl === 'true') this.controle.dados.filtroPeriodo = true
            if (urlParams.get('dataInicioVencimento')) this.controle.dados.dataInicioVencimento = urlParams.get('dataInicioVencimento')
            if (urlParams.get('dataFimVencimento')) this.controle.dados.dataFimVencimento = urlParams.get('dataFimVencimento')
            const filtroVencimentoUrl = urlParams.get('filtroVencimento')
            if (filtroVencimentoUrl === '1' || filtroVencimentoUrl === 'true' || urlParams.get('dataInicioVencimento') || urlParams.get('dataFimVencimento'))
                this.controle.dados.filtroVencimento = true
            if (urlParams.get('dataInicioFerias')) this.controle.dados.dataInicioFerias = urlParams.get('dataInicioFerias')
            if (urlParams.get('dataFimFerias')) this.controle.dados.dataFimFerias = urlParams.get('dataFimFerias')
            const filtroInicioFeriasUrl = urlParams.get('filtroInicioFerias')
            if (filtroInicioFeriasUrl === '1' || filtroInicioFeriasUrl === 'true' || urlParams.get('dataInicioFerias') || urlParams.get('dataFimFerias'))
                this.controle.dados.filtroInicioFerias = true
        },
        syncUrlFiltros() {
            if (typeof this.atualizarUrlMovimentacao !== 'function') return
            const d = this.controle.dados
            const params = { pages: d.pages || 50, ordenacao: d.ordenacao || 'created_at_desc' }
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoCPF) params.campoCPF = d.campoCPF
            if (d.campoStatusAprovacao) params.campoStatusAprovacao = d.campoStatusAprovacao
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto
            if (d.filtroPeriodoAquisitivo) params.filtroPeriodoAquisitivo = d.filtroPeriodoAquisitivo
            if (d.filtroPeriodo) params.filtroPeriodo = 1
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            if (d.filtroVencimento) params.filtroVencimento = 1
            if (d.filtroVencimento && d.dataInicioVencimento) params.dataInicioVencimento = d.dataInicioVencimento
            if (d.filtroVencimento && d.dataFimVencimento) params.dataFimVencimento = d.dataFimVencimento
            if (d.filtroInicioFerias) params.filtroInicioFerias = 1
            if (d.filtroInicioFerias && d.dataInicioFerias) params.dataInicioFerias = d.dataInicioFerias
            if (d.filtroInicioFerias && d.dataFimFerias) params.dataFimFerias = d.dataFimFerias
            if (d.token) params.token = d.token
            this.atualizarUrlMovimentacao(params)
        },
        limparTokenFiltroListagem() {
            this.controle.dados.token = ''
            this.syncUrlFiltros()
        },
        //apagar férias
        formApagarFerias(id) {
            this.formApagar.id = id
            this.formApagar.titulo = this.tituloJanela = `${id} - Apagar Solicitação de férias`
            this.formApagar.preload = false
            this.formApagar.delete = false
            this.formApagar.erro = false
            this.formApagar.msg = ''
        },
        apagarFerias() {
            this.formApagar.preload = true
            axios
                .delete(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.formApagar.id}`)
                .then((response) => {
                    this.formApagar.preload = false
                    this.formApagar.delete = true
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                })
                .catch((response) => {
                    this.formApagar.msg = response.data.msg
                    this.formApagar.preload = false
                    this.formApagar.erro = true
                })
        },

        dataAdmissao() {
            if (this.form.centro_custo_id != null && this.form.centro_custo_id !== '') {
                this.form.centro_custo_id = String(this.form.centro_custo_id)
            }
            if (this.form.colaborador_id !== '') {
                axios
                    .post(`${URL_ADMIN}/busca-data-admissao`, {
                        ferias_id: this.form.id,
                        colaborador_id: this.form.colaborador_id,
                        visualizar: this.visualizar
                    })
                    .then((response) => {
                        if (!response.data.data_admissao) {
                            mostraErro('', 'Atualize a data de admissão no cadastro do colaborador')
                            return false
                        }

                        this.form.data_admissao = response.data.data_admissao
                        this.ultimaData = response.data.ultimaData

                        if (response.data.periodo.length > 1) {
                            this.periodos = response.data.periodo
                        } else {
                            this.form.periodo_aquisitivo_id = String(response.data.periodo.id)
                            this.periodo_label = response.data.periodo.label
                        }

                        if (response.data.ultimaData === '') {
                            let dataAtual = new Date()
                            let dia = dataAtual.getDate()
                            let mes = dataAtual.getMonth()
                            let ano = dataAtual.getFullYear()
                            let dataHoje = this.padTo2Digits(dia) + '/' + this.padTo2Digits(mes + 1) + '/' + ano
                            this.form.ultima_data = dataHoje
                            this.form.data_saida = dataHoje
                            this.form.data_retorno = dataHoje
                        } else {
                            this.form.ultima_data = response.data.ultimaData
                            this.form.data_saida = response.data.data_saida
                            this.form.data_retorno = response.data.data_retorno
                        }
                    })
                return this.form.data_admissao
            }
        },
        onSelecionaColaboradorFerias() {
            if (this.form.centro_custo_id != null && this.form.centro_custo_id !== '') {
                this.form.centro_custo_id = String(this.form.centro_custo_id)
            } else {
                this.form.centro_custo_id = ''
            }
            this.form.filial = !!(this.form.filial === true || this.form.filial === 1 || this.form.filial === '1')
            if (!this.form.filial) {
                this.form.centro_custo_filial_id = ''
            }
            this.listaCentroCusto()
            this.dataAdmissao()

            if (this.colaboradorSemCentroCusto && typeof mostraWarning === 'function') {
                mostraWarning(
                    'O colaborador selecionado está sem centro de custo. A solicitação poderá ser registrada mesmo assim.',
                    'Atenção'
                )
            }
        },
        montarPayloadFerias() {
            const payload = _.cloneDeep(this.form)
            if ([undefined, null, '', 0, '0'].includes(payload.centro_custo_id)) {
                payload.centro_custo_id = null
            }
            return payload
        },
        onResetaColaboradorFerias() {
            this.form.filial = false
            this.form.centro_custo_filial_id = ''
            this.form.centro_custo_id = ''
            this.form.data_admissao = ''
            this.ultimaData = ''
            this.dataAdmissao()
        },
        padTo2Digits(num) {
            return num.toString().padStart(2, '0')
        },
        verificaFaltas() {
            this.form.qnt_faltas = 1
            if (!this.form.tem_faltas) {
                this.form.qnt_faltas = 0
            }
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.naoAprovados.map((item) => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.naoAprovados.map((item) => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },
        confirmaAtualizacaoStatus(confirmacao) {
            this.preloadAtualizacao = true
            this.formConfirmacao.status_aprovacao = confirmacao
            this.formConfirmacao.selecionados.push(this.selecionados)

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/atualizacao-status`, this.formConfirmacao)
                .then((res) => {
                    this.preloadAtualizacao = false
                    this.$refs.modal_janelaAtualizaStatus && this.$refs.modal_janelaAtualizaStatus.fecharModal()
                    mostraSucesso('Status das Férias atualizado com sucesso!')
                    this.selecionados = []
                    this.formConfirmacao = _.cloneDeep(this.formConfirmacaoDefault) //copia
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                })
                .catch((error) => {
                    this.preloadAtualizacao = false
                })
        },
        listaCentroCusto() {
            axios
                .post(`${URL_PUBLICO}/centro-custos/`)
                .then((res) => {
                    this.centro_custos = res.data.centro_custos
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        async periodosAquisitivos() {
            try {
                const response = await axios.get(`${URL_ADMIN}/periodos-aquisitivos`)
                this.periodos = response.data.periodos
            } catch (err) {
                this.periodos = []
            }
        },

        formNovo() {
            this.cadastrando = true
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.visualizar = false
            this.editando = false
            this.podeanexar = true
            this.tituloJanela = 'Solicitação de férias'

            formReset()
            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.centro_custo_id = ''
            this.limparTokenFiltroListagem()
            this.listaCentroCusto()
            this.ultimaData = ''
        },

        cadastrar() {
            if (!this.form.colaborador_id) {
                mostraErro('', 'Selecione o colaborador')
                return false
            }
            if (!this.form.periodo_aquisitivo_id) {
                mostraErro('', 'Selecione o período aquisitivo')
                return false
            }
            if (!this.form.qnt_dias) {
                mostraErro('', 'Selecione a quantidade de dias de férias')
                return false
            }
            if (!this.form.data_saida) {
                mostraErro('', 'Informe a data de saída')
                return false
            }
            if (!this.form.gestor_id) {
                mostraErro('', 'Selecione o gestor de aprovação')
                return false
            }

            if (this.colaboradorSemCentroCusto) {
                const ok = confirm(
                    'O colaborador selecionado está sem centro de custo. Deseja continuar com a solicitação mesmo assim?'
                )
                if (!ok) {
                    return false
                }
            }

            this.preload = true

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista`, this.montarPayloadFerias())
                .then((response) => {
                    if (response.status === 201) {
                        this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                        let data = response.data
                        mostraSucesso('', 'Solicitação registrada com sucesso!')
                        this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                        this.preload = false
                    }
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        editar() {
            if (!this.form.periodo_aquisitivo_id) {
                mostraErro('', 'Selecione o período aquisitivo')
                return false
            }
            if (!this.form.qnt_dias) {
                mostraErro('', 'Selecione a quantidade de dias de férias')
                return false
            }
            if (!this.form.data_saida) {
                mostraErro('', 'Informe a data de saída')
                return false
            }

            if (this.colaboradorSemCentroCusto) {
                const ok = confirm(
                    'O colaborador selecionado está sem centro de custo. Deseja continuar com a solicitação mesmo assim?'
                )
                if (!ok) {
                    return false
                }
            }

            this.preload = true

            axios
                .put(
                    `${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}`,
                    this.montarPayloadFerias()
                )
                .then((response) => {
                    if (response.status === 201) {
                        this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                        let data = response.data
                        mostraSucesso('', 'Solicitação alterada com sucesso!')
                        this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                        this.preload = false
                    }
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        formOpen(id) {
            Object.assign(this.form, this.formDefault)
            this.cadastrando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false
            this.form.id = id

            this.tituloJanela = `#${id}`

            formReset()
            this.preload = true
            this.form.data_admissao = ''

            axios
                .get(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    this.form.centro_custo_id = data.centro_custo_id
                    this.form.colaborador_id = data.colaborador_id
                    Object.assign(this.form, data)
                    this.listaCentroCusto()

                    this.tituloJanela = `#${id} Solicitação de férias`

                    if (this.form.centro_custo_id != null && this.form.centro_custo_id !== '') {
                        this.form.centro_custo_id = String(this.form.centro_custo_id)
                    } else {
                        this.form.centro_custo_id = ''
                    }
                    if (this.form.periodo_aquisitivo_id != null && this.form.periodo_aquisitivo_id !== '') {
                        this.form.periodo_aquisitivo_id = String(this.form.periodo_aquisitivo_id)
                    } else {
                        this.form.periodo_aquisitivo_id = ''
                    }
                    this.form.filial = !!(data.filial === true || data.filial === 1 || data.filial === '1')
                    this.form.centro_custo_filial_id = this.form.filial
                        ? data.centro_custo_filial_id || ''
                        : ''

                    this.form.status_aprovacao_gestor = data.status_aprovacao_gestor === null ? '' : data.status_aprovacao_gestor
                    this.form.status_aprovacao_extra = data.status_aprovacao_extra === null ? '' : data.status_aprovacao_extra
                    this.form.status_aprovacao_rh = data.status_aprovacao_rh === null ? '' : data.status_aprovacao_rh
                    this.form.obs_gestor = data.status_aprovacao_gestor === null ? '' : data.obs_gestor
                    this.form.obs_aprovacao_extra = data.status_aprovacao_extra === null ? '' : data.obs_aprovacao_extra
                    this.form.obs_rh = data.status_aprovacao_rh === null ? '' : data.obs_rh
                    this.periodo_label = data.periodo_label

                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        aprovarGestor() {
            if (!this.form.status_aprovacao_gestor) {
                mostraErro('', 'Selecione o status da aprovação')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}/aprovargestor`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        aprovarExtra() {
            if (!this.form.status_aprovacao_extra) {
                mostraErro('', 'Selecione o status da aprovação')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}/aprovarextra`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', data.msg || 'Aprovação extra registrada com sucesso!')
                    this.$refs[`${this.hash}`] && this.$refs[`${this.hash}`].fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    console.error('Erro ao aprovar extra:', error)
                    let msg = error.response?.data?.msg || 'Erro ao processar aprovação'
                    mostraErro('', msg)
                    this.preload = false
                })
        },

        aprovarRh() {
            if (!this.form.status_aprovacao_rh) {
                mostraErro('', 'Selecione o status da aprovação')
                return false
            }
            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}/aprovarrh`, this.form)
                .then((response) => {
                    let data = response.data
                    mostraSucesso('', 'Registro salvo com sucesso!')
                    this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        carregou(dados) {
            this.lista = dados.itens
            this.periodos = dados.periodo
            this.aprovaGestor = dados.aprovar_por_gestor
            this.aprovaRh = dados.aprovar_por_rh
            this.podeAprovarExtra = dados.pode_aprovar_extra || false
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || ''
            this.permissoes = dados.permissoes
            this.mimes = dados.mimes || this.mimes
            if (dados.cc) {
                this.lista_ccs = dados.cc
            }
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.syncUrlFiltros()
            this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        },
        limparFiltros() {
            const d = this.controle.dados
            d.campoBusca = ''
            d.campoCPF = ''
            d.campoStatusAprovacao = ''
            d.campoCentroCusto = ''
            d.filtroPeriodoAquisitivo = ''
            d.filtroPeriodo = false
            d.dataInicio = ''
            d.dataFim = ''
            d.filtroVencimento = false
            d.dataInicioVencimento = ''
            d.dataFimVencimento = ''
            d.filtroInicioFerias = false
            d.dataInicioFerias = ''
            d.dataFimFerias = ''
            d.ordenacao = 'created_at_desc'
            d.pages = 50
            d.token = ''
            d.campoCnpj = ''
            this.atualizar()
        },
        formatarCpfDigitos(valor) {
            const d = String(valor || '').replace(/\D/g, '').slice(0, 11)
            if (d.length <= 3) return d
            if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`
            if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`
            return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`
        },
        parecePadraoCpf(valor) {
            const raw = String(valor || '').trim()
            if (!raw) return false
            if (!/^[\d.\-\s]+$/.test(raw)) return false
            const digitos = raw.replace(/\D/g, '')
            return digitos.length > 0 && digitos.length <= 11
        },
        onInputBuscaUnificada(event) {
            const valor = event && event.target ? event.target.value : ''
            const d = this.controle.dados
            if (!valor) {
                d.campoBusca = ''
                d.campoCPF = ''
                return
            }
            if (this.parecePadraoCpf(valor)) {
                const mascarado = this.formatarCpfDigitos(valor)
                d.campoCPF = mascarado
                d.campoBusca = ''
                if (event.target && event.target.value !== mascarado) {
                    event.target.value = mascarado
                }
                return
            }
            d.campoBusca = valor
            d.campoCPF = ''
        },
        onSelectCnpj() {
            this.controle.dados.campoCentroCusto = ''
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'ferias-periodo-aquisitivo': 'comboFiltroPeriodoAquisitivo',
                'ferias-status': 'comboFiltroStatus',
                'ferias-cnpj': 'comboFiltroCnpj',
                'ferias-cc': 'comboFiltroCc',
                'ferias-ordenacao': 'comboFiltroOrdenacao',
                'ferias-pages': 'comboFiltroPages',
                'form-ferias-periodo': 'comboFormPeriodo',
                'form-ferias-tem-falta': 'comboFormTemFalta',
                'form-ferias-qnt-faltas': 'comboFormQntFaltas',
                'form-ferias-qnt-dias': 'comboFormQntDias',
                'form-ferias-abono': 'comboFormAbono',
                'form-ferias-adiantamento': 'comboFormAdiantamento',
                'form-ferias-status-gestor': 'comboFormStatusGestor',
                'form-ferias-status-extra': 'comboFormStatusExtra',
                'form-ferias-status-rh': 'comboFormStatusRh'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        onSelectQntFaltas() {
            this.form.qnt_dias = 5
        }
    }
}
</script>

<style scoped>
.ferias-filtro-hint {
    margin-left: 0.35rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #0d6efd;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
</style>
