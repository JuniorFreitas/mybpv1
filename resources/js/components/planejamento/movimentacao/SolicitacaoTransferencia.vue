<template>
    <div>
        <modal :id="hash" :titulo="tituloJanela" :size="90" ref="modalRef">
            <template #conteudo>
                <preload v-show="preload" class="text-center"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i>Solicitação cadastrada com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i>Solicitação alterada com sucesso!</h4>
                </div>
                <form
                    v-if="!preload && !cadastrado && !atualizado"
                    :id="`form_${hash}`"
                    class="mybp-modal-form mybp-filtros-compactos"
                    onsubmit="return false"
                >
                    <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>

                    <fieldset class="mybp-modal-secao">
                        <legend>Colaborador e centros de custo</legend>
                        <div class="row">
                            <colaborador
                                label="Colaborador"
                                :obrigatorio="true"
                                :model="form"
                                :verifica="visualizar || aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                :hash="hash"
                                @evtseleciona="onColaboradorSelecionado"
                            ></colaborador>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6" v-if="lista_ccs && temFilial">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`transferencia-lotacao-origem-${hash}`">
                                        Lotação origem (CNPJ) <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormLotacaoOrigem"
                                            instance-id="form-transferencia-lotacao-origem"
                                            :input-id="`transferencia-lotacao-origem-${hash}`"
                                            v-model="formLotacaoOrigemCnpj"
                                            :options="formLotacaoOpcoes"
                                            :disabled="emFluxoAprovacao || centroOrigemDesabilitadoPorColaborador"
                                            placeholder-blur="Selecione a lotação origem..."
                                            empty-message="Nenhuma lotação encontrada."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-transferencia-lotacao-origem')"
                                            @select="onSelectFormLotacaoOrigem"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div :class="temFilial ? 'col-12 col-md-6' : 'col-12 col-md-6'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`transferencia-cc-origem-${hash}`">
                                        Centro de Custo Origem <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboCcOrigem"
                                            instance-id="form-cc-origem"
                                            v-model="form.centro_custo_origem_id"
                                            :options="centroCustoOrigemOpcoes"
                                            :disabled="emFluxoAprovacao || centroOrigemDesabilitadoPorColaborador || !centroCustoOrigemOpcoes.length || (temFilial && !formLotacaoOrigemCnpj)"
                                            :input-id="`transferencia-cc-origem-${hash}`"
                                            placeholder-blur="Selecione o centro de custo origem"
                                            placeholder-focus="Digite para filtrar…"
                                            empty-message="Nenhum centro de custo para esta lotação."
                                            :max-results="200"
                                            @opening="fecharOutrosComboboxes('form-cc-origem')"
                                            @select="limparComboboxInvalido(`transferencia-cc-origem-${hash}`)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6" v-if="lista_ccs && temFilial">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`transferencia-lotacao-destino-${hash}`">
                                        Lotação destino (CNPJ) <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormLotacaoDestino"
                                            instance-id="form-transferencia-lotacao-destino"
                                            :input-id="`transferencia-lotacao-destino-${hash}`"
                                            v-model="formLotacaoDestinoCnpj"
                                            :options="formLotacaoOpcoes"
                                            :disabled="emFluxoAprovacao"
                                            placeholder-blur="Selecione a lotação destino..."
                                            empty-message="Nenhuma lotação encontrada."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-transferencia-lotacao-destino')"
                                            @select="onSelectFormLotacaoDestino"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div :class="temFilial ? 'col-12 col-md-6' : 'col-12 col-md-6'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`transferencia-cc-destino-${hash}`">
                                        Centro de Custo Destino <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboCcDestino"
                                            instance-id="form-cc-destino"
                                            v-model="form.centro_custo_destino_id"
                                            :options="centroCustoDestinoOpcoes"
                                            :disabled="emFluxoAprovacao || !centroCustoDestinoOpcoes.length || (temFilial && !formLotacaoDestinoCnpj)"
                                            :input-id="`transferencia-cc-destino-${hash}`"
                                            placeholder-blur="Selecione o centro de custo destino"
                                            placeholder-focus="Digite para filtrar…"
                                            empty-message="Nenhum centro de custo para esta lotação."
                                            :max-results="200"
                                            @opening="fecharOutrosComboboxes('form-cc-destino')"
                                            @select="limparComboboxInvalido(`transferencia-cc-destino-${hash}`)"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                                    <label class="mybp-label">Data para transferência <span class="text-danger">*</span></label>
                                    <datepicker
                                        :id="`transferencia-data-${hash}`"
                                        formsm
                                        label=""
                                        class="corrigiDatepicker"
                                        v-model="form.data_transferencia"
                                        :disabled="visualizar || aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                        @onselect="limparCampoDataInvalido(`transferencia-data-${hash}`)"
                                    ></datepicker>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.modo_aprovacao !== 'gestor_unico'" class="mybp-modal-secao">
                        <legend>Gestores responsáveis</legend>
                        <div class="row">
                            <div class="col-12 col-md-6" v-if="form.modo_aprovacao !== 'gestor_unico' && exigeAprovacaoGestorOrigem">
                                <div class="form-group">
                                    <label>Gestor responsável pela origem</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        :value="form.label_gestor_origem || 'Selecione o centro de custo origem'"
                                        disabled
                                    />
                                    <div class="alert alert-warning mb-0 mt-2 py-2" v-if="gestorOrigemAusente && !emFluxoAprovacao" role="alert">
                                        O centro de custo de origem não possui gestor responsável. A solicitação pode ser registrada e seguirá o fluxo sem a etapa de aprovação do gestor de origem.
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6" v-if="form.modo_aprovacao !== 'gestor_unico'">
                                <div class="form-group">
                                    <label>Gestor responsável pelo destino</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        :class="{ 'is-invalid': gestorDestinoAusente }"
                                        :value="form.label_gestor_destino || 'Selecione o centro de custo destino'"
                                        disabled
                                    />
                                    <div
                                        class="alert alert-danger mb-0 mt-2 py-2"
                                        v-if="mostrarAvisoGestorDestinoAusente"
                                        role="alert"
                                    >
                                        {{ msgGestorDestinoAusente }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.modo_aprovacao === 'gestor_unico'" class="mybp-modal-secao">
                        <legend>Aprovador único</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info mb-0" role="alert">
                                    <i class="fa fa-info-circle"></i> Esta empresa usa aprovador único para transferências —
                                    gestor de origem e destino não participam deste fluxo.
                                    <strong v-if="form.label_gestor_aprovacao_unico">Aprovador: {{ form.label_gestor_aprovacao_unico }}</strong>
                                </div>
                            </div>
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
                                        v-model="form.obs"
                                        rows="3"
                                        placeholder="Informações relevantes sobre a transferência"
                                        :disabled="visualizar || aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                    ></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Anexos</label>
                                    <upload
                                        :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :url="url_anexo"
                                        :tipos="mimes"
                                        :leitura="!podeanexar"
                                        label="Selecionar"
                                        @onProgresso="anexoUploadAndamento = true"
                                        @onFinalizado="anexoUploadAndamento = false"
                                    ></upload>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="aprovando && !form.data_aprovacao">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                    <fieldset
                        v-if="exigeAprovacaoGestorOrigem && (visualizar || aprovando) && form.modo_aprovacao !== 'gestor_unico' && (aprovando || form.status_aprovacao || form.gestor_id)"
                        class="mybp-modal-secao"
                    >
                        <legend>Aprovação Gestor Origem</legend>
                            <div class="row">
                                <div v-if="!aprovando && form.user_aprovacao" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao }} por: {{ labelAprovadorOrigem(form) }} em
                                        {{ form.data_aprovacao }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                            v-model="form.obs_aprovacao"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" :for="`transferencia-status-origem-${hash}`">
                                            Status <span class="text-danger" v-if="aprovando">*</span>
                                        </label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="form-transferencia-status-origem"
                                                :input-id="`transferencia-status-origem-${hash}`"
                                                v-model="form.status_aprovacao"
                                                :options="formStatusAprovacaoOpcoes"
                                                :disabled="!aprovando || aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção encontrada."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('form-transferencia-status-origem')"
                                                @select="limparComboboxInvalido('transferencia-status-origem-' + hash)"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset
                            v-if="(visualizar || aprovandoGestorDestino) && form.exige_aprovacao_gestor_destino"
                            class="mybp-modal-secao"
                        >
                            <legend>Aprovação Gestor Destino</legend>
                            <div class="row">
                                <div v-if="!aprovandoGestorDestino && form.quem_aprovou_gestor_destino" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_gestor_destino }} por: {{ labelAprovadorDestino(form) }} em
                                        {{ form.data_aprovacao_gestor_destino }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                            v-model="form.obs_aprovacao_gestor_destino"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" :for="`transferencia-status-destino-${hash}`">
                                            Status <span class="text-danger" v-if="aprovandoGestorDestino">*</span>
                                        </label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="form-transferencia-status-destino"
                                                :input-id="`transferencia-status-destino-${hash}`"
                                                v-model="form.status_aprovacao_gestor_destino"
                                                :options="formStatusAprovacaoOpcoes"
                                                :disabled="!aprovandoGestorDestino || aprovandoExtra || aprovandoRh"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção encontrada."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('form-transferencia-status-destino')"
                                                @select="limparComboboxInvalido('transferencia-status-destino-' + hash)"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="alert alert-warning" v-if="aprovandoGestorUnico && !form.data_aprovacao_gestor_unico">
                            Esta solicitação ainda não foi aprovada ou reprovada!
                        </div>

                        <fieldset
                            v-if="(visualizar || aprovandoGestorUnico) && form.modo_aprovacao === 'gestor_unico'"
                            class="mybp-modal-secao"
                        >
                            <legend>Aprovação Gestor Aprovação</legend>
                            <div class="row">
                                <div v-if="!aprovandoGestorUnico && form.quem_aprovou_gestor_unico" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_gestor_unico }} por: {{ labelAprovadorGestorUnico(form) }} em
                                        {{ form.data_aprovacao_gestor_unico }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovandoGestorUnico || aprovandoExtra || aprovandoRh"
                                            v-model="form.obs_aprovacao_gestor_unico"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" :for="`transferencia-status-gestor-unico-${hash}`">
                                            Status <span class="text-danger" v-if="aprovandoGestorUnico">*</span>
                                        </label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="form-transferencia-status-gestor-unico"
                                                :input-id="`transferencia-status-gestor-unico-${hash}`"
                                                v-model="form.status_aprovacao_gestor_unico"
                                                :options="formStatusAprovacaoOpcoes"
                                                :disabled="!aprovandoGestorUnico || aprovandoExtra || aprovandoRh"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção encontrada."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('form-transferencia-status-gestor-unico')"
                                                @select="limparComboboxInvalido('transferencia-status-gestor-unico-' + hash)"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="alert alert-warning" v-if="aprovandoExtra">
                            Esta solicitação ainda não foi aprovada ou reprovada por {{ nomeAprovacaoExtra }}!
                        </div>

                        <fieldset v-if="visualizar || aprovandoExtra" class="mybp-modal-secao">
                            <div v-if="!temAprovacaoExtra" class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Esta empresa não possui aprovação extra configurada.
                            </div>

                            <legend v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</legend>
                            <div class="row" v-if="temAprovacaoExtra">
                                <div v-if="!aprovandoExtra && form.user_aprovacao_extra" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_extra }} por: {{ form.user_aprovacao_extra.nome }} em
                                        {{ form.data_aprovacao_extra }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovandoExtra || aprovandoRh"
                                            v-model="form.obs_aprovacao_extra"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" :for="`transferencia-status-extra-${hash}`">
                                            Status <span class="text-danger" v-if="aprovandoExtra">*</span>
                                        </label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="form-transferencia-status-extra"
                                                :input-id="`transferencia-status-extra-${hash}`"
                                                v-model="form.status_aprovacao_extra"
                                                :options="formStatusAprovacaoOpcoes"
                                                :disabled="!aprovandoExtra || aprovandoRh"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção encontrada."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('form-transferencia-status-extra')"
                                                @select="limparComboboxInvalido('transferencia-status-extra-' + hash)"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="alert alert-warning" v-if="aprovandoRh">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                        <fieldset v-if="visualizar || aprovandoRh" class="mybp-modal-secao">
                            <legend>Aprovação RH</legend>
                            <div class="row">
                                <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12">
                                    <legend>{{ form.resposta_rh }} por: {{ form.rh_aprovacao.nome }} em {{ form.data_aprovacao_rh }}</legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="visualizar && !aprovando && !aprovandoRh"
                                            v-model="form.obs_rh"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mybp-filtro-campo">
                                        <label class="mybp-label" :for="`transferencia-status-rh-${hash}`">
                                            Status <span class="text-danger" v-if="aprovandoRh">*</span>
                                        </label>
                                        <div class="mybp-combobox-wrap">
                                            <combobox-auto-complete
                                                instance-id="form-transferencia-status-rh"
                                                :input-id="`transferencia-status-rh-${hash}`"
                                                v-model="form.resposta_rh"
                                                :options="formStatusAprovacaoOpcoes"
                                                :disabled="!aprovandoRh"
                                                placeholder-blur="Selecione..."
                                                empty-message="Nenhuma opção encontrada."
                                                :max-results="10"
                                                @opening="fecharOutrosComboboxes('form-transferencia-status-rh')"
                                                @select="limparComboboxInvalido('transferencia-status-rh-' + hash)"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                </form>
            </template>
            <template #rodape>
                <div v-show="!emFluxoAprovacao">
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !atualizado && !preload" :disabled="bloqueiaSalvarSemGestorDestino" @click.prevent="alterar">
                        <i class="fa fa-edit"></i> Alterar
                    </button>
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="cadastrando && !cadastrado && !preload" :disabled="bloqueiaSalvarSemGestorDestino" @click.prevent="cadastrar">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                </div>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="aprovando && !atualizado && !preload && !form.data_aprovacao"
                    @click.prevent="aprovar"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="aprovandoGestorDestino && !atualizado && !preload && !form.data_aprovacao_gestor_destino"
                    @click.prevent="aprovarGestorDestino"
                >
                    <i class="fa fa-save"></i> Salvar Gestor Destino
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="aprovandoGestorUnico && !atualizado && !preload && !form.data_aprovacao_gestor_unico"
                    @click.prevent="aprovarGestorUnico"
                >
                    <i class="fa fa-save"></i> Salvar
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="aprovandoExtra && !atualizado && !preload && !form.data_aprovacao_extra"
                    @click.prevent="aprovarExtra"
                >
                    <i class="fa fa-save"></i> Salvar {{ nomeAprovacaoExtra }}
                </button>
                <button
                    type="button"
                    class="btn btn-sm mr-1 btn-primary"
                    v-show="aprovandoRh && !atualizado && !preload && !form.user_rh_id"
                    @click.prevent="aprovarRH"
                >
                    <i class="fa fa-save"></i> Salvar RH
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
                <date-range-filter
                    v-model:enabled="controle.dados.filtroPeriodo"
                    v-model:start-date="controle.dados.dataInicio"
                    v-model:end-date="controle.dados.dataFim"
                    :disabled="controle.carregando"
                    :id-suffix="'transferencia-' + hash"
                    label="Período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="transferencia-filtro-busca">
                            Colaborador / CPF
                            <span v-if="buscaUnificadaEhCpf" class="transferencia-filtro-hint">CPF</span>
                        </label>
                        <input
                            id="transferencia-filtro-busca"
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
                        <label class="mybp-label" for="transferencia-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="transferencia-status"
                                input-id="transferencia-filtro-status"
                                v-model="controle.dados.campoStatus"
                                :options="opcoesStatus"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhum status encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('transferencia-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="transferencia-filtro-cnpj">Lotação (CNPJ)</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="transferencia-cnpj"
                                input-id="transferencia-filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="opcoesCnpj"
                                :disabled="controle.carregando"
                                placeholder-blur="Todas as lotações"
                                empty-message="Nenhuma lotação encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('transferencia-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="lista_ccs" :class="temFilial ? 'col-12 col-md-8' : 'col-12'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="transferencia-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                instance-id="transferencia-cc"
                                input-id="transferencia-filtro-cc"
                                v-model="controle.dados.campoCentroCusto"
                                :options="opcoesCentroCusto"
                                :disabled="controle.carregando || !opcoesCentroCusto.length"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro de custo encontrado."
                                :max-results="200"
                                @opening="fecharOutrosComboboxes('transferencia-cc')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="transferencia-filtro-ordenacao">Ordenar por</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroOrdenacao"
                                instance-id="transferencia-ordenacao"
                                input-id="transferencia-filtro-ordenacao"
                                v-model="controle.dados.ordenacao"
                                :options="opcoesOrdenacao"
                                :disabled="controle.carregando"
                                placeholder-blur="Mais recentes"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('transferencia-ordenacao')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="transferencia-filtro-pages">Por página</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPages"
                                instance-id="transferencia-pages"
                                input-id="transferencia-filtro-pages"
                                v-model="campoPagesCombo"
                                :options="opcoesPages"
                                :disabled="controle.carregando"
                                placeholder-blur="50"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('transferencia-pages')"
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
                    @click.prevent="abrirModalSolicitar"
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
                    @click.prevent="abrirModalAtualizarStatus"
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

            <div class="mybp-cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="mybp-card" v-for="item in lista" :key="item.id">
                    <div class="mybp-card-header-row">
                        <div class="mybp-card-left">
                            <span class="mybp-badge-id">#{{ item.id }}</span>
                            <div class="mybp-card-titulo">
                                <strong>{{ tituloCardLista(item) }}</strong>
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
                                        title="Aprovação Gestor Origem"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: false, aprovando: true, aprovandoGestorDestino: false, aprovandoExtra: false, aprovandoRh: false, podeanexar: true })"
                                        v-if="exibeEtapaGestorOrigem(item) && !item.status_aprovacao && podeAprovarGestorOrigemItem(item)"
                                    >
                                        Aprovação Gestor Origem
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação Gestor Destino"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: false, aprovando: false, aprovandoGestorDestino: true, aprovandoExtra: false, aprovandoRh: false, podeanexar: true })"
                                        v-if="item.exige_aprovacao_gestor_destino && origemEtapaConcluida(item) && !item.status_aprovacao_gestor_destino && podeAprovarGestorDestinoItem(item)"
                                    >
                                        Aprovação Gestor Destino
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação Gestor Aprovação"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: false, aprovando: false, aprovandoGestorDestino: false, aprovandoGestorUnico: true, aprovandoExtra: false, aprovandoRh: false, podeanexar: true })"
                                        v-if="item.modo_aprovacao === 'gestor_unico' && !item.status_aprovacao_gestor_unico && podeAprovarGestorUnicoItem(item)"
                                    >
                                        Aprovação Gestor Aprovação
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: false, aprovando: false, aprovandoGestorDestino: false, aprovandoExtra: true, aprovandoRh: false, podeanexar: false })"
                                        v-if="temAprovacaoExtra && gestoresConcluidosItem(item) && !item.status_aprovacao_extra && podeAprovarExtra"
                                    >
                                        {{ nomeAprovacaoExtra }}
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: true, aprovando: false, aprovandoGestorDestino: false, aprovandoExtra: false, aprovandoRh: true, podeanexar: false })"
                                        v-if="
                                            ((temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                                (!temAprovacaoExtra && gestoresConcluidosItem(item))) &&
                                            !item.user_rh_id &&
                                            aprovar_por_rh
                                        "
                                    >
                                        Aprovação RH
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="abrirModalAposFormOpen(item.id, { visualizar: true, aprovando: false, aprovandoGestorDestino: false, aprovandoExtra: false, aprovandoRh: false, podeanexar: false })"
                                    >
                                        Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-corpo" :class="classeBordaStatusLista(item)">
                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-building"
                                    label="Centro de custo origem"
                                    :valor="ccOrigemLista(item)"
                                />
                                <mybp-card-campo
                                    icon="fas fa-building"
                                    label="Centro de custo destino"
                                    :valor="ccDestinoLista(item)"
                                />
                                <mybp-card-campo
                                    icon="fas fa-calendar"
                                    label="Data transferência"
                                    :valor="formatarDataBr(item.data_transferencia)"
                                    forte
                                />
                            </div>
                        </section>
                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    v-if="temFilial"
                                    icon="fas fa-map-marker-alt"
                                    label="Lotação origem"
                                    :valor="item.lotacao_origem || 'Não informado'"
                                />
                                <mybp-card-campo
                                    v-if="temFilial"
                                    icon="fas fa-map-marker-alt"
                                    label="Lotação destino"
                                    :valor="item.lotacao || item.lotacao_destino || 'Não informado'"
                                />
                                <mybp-card-campo
                                    icon="fas fa-user-edit"
                                    label="Solicitante"
                                    :valor="solicitanteLista(item)"
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
import { defineComponent, ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick, getCurrentInstance, inject } from 'vue'
import axios from 'axios'
import _ from 'lodash'
import Upload from '../../Upload'
import colaborador from '../../Colaborador'
import DateRangeFilter from '../../DateRangeFilter'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'
import Validacoes from '../../../mixins/Validacoes'
import configuracoes from '../../../mixins/Configuracoes'
import { exigirCombobox, limparComboboxInvalido, exigirCampoData, limparCampoDataInvalido, validarInputsAtivosVisiveis } from '../../../utils/comboboxValidation'

const BASE_URL = `${URL_ADMIN}/planejamento/movimentacao/transferencia-prevista`
const POR_PAGINA_OPCOES = [20, 50, 100, 150]
const MSG_GESTOR_DESTINO_AUSENTE =
    'O centro de custo de destino não possui gestor responsável cadastrado. Não é possível salvar a solicitação. Entre em contato com o Administrador para cadastrar o gestor de destino.'

function createFormDefault() {
    return {
    colaborador_id: '',
    autocomplete_label_colaborador: '',
    autocomplete_label_colaborador_anterior: '',
    gestor_id: '',
    autocomplete_label_gestor_modal: '',
    autocomplete_label_gestor_modal_anterior: '',
    centro_custo_origem_id: '',
    centro_custo_destino_id: '',
    label_gestor_origem: '',
    label_gestor_destino: '',
    label_gestor_aprovacao_unico: '',
    modo_aprovacao: 'padrao',
    exige_aprovacao_gestor_destino: false,
    fluxo_gestores_automatico: true,
    data_transferencia: '',
    obs: '',
    obs_aprovacao: '',
    status_aprovacao: '',
    obs_aprovacao_gestor_destino: '',
    status_aprovacao_gestor_destino: '',
    obs_aprovacao_gestor_unico: '',
    status_aprovacao_gestor_unico: '',
    anexos: [],
    anexosDel: []
    }
}

function createFormConfirmacaoDefault() {
    return {
    selecionados: [],
    obs_aprovacao: '',
    status_aprovacao: ''
    }
}

function createControleDados() {
    return {
    filtroPeriodo: false,
    dataInicio: '',
    dataFim: '',
    campoBusca: '',
    campoCPF: '',
    campoStatus: '',
    campoCnpj: '',
    campoCentroCusto: '',
    pages: 50,
    token: '',
    ordenacao: 'created_at_desc'
    }
}

export default defineComponent({
    name: 'SolicitacaoTransferencia',
    components: {
        colaborador,
        DateRangeFilter,
        Upload,
        ComboboxAutoComplete,
        FiltroListagem,
        MybpCardCampo,
        MybpFluxoAprovacao,
        MybpStatusBadge
    },
    mixins: [Validacoes, configuracoes],
    inject: {
        atualizarUrlMovimentacao: { default: () => () => {} }
    },
    setup() {
        const atualizarUrlMovimentacao = inject('atualizarUrlMovimentacao', () => () => {})
        const modalRef = ref(null)
        const modalStatusRef = ref(null)
        const componenteRef = ref(null)
        const hash = `mybp_${Math.floor(Math.random() * 999999)}`
        const formStatusAprovacaoOpcoes = [
            { value: '', label: 'Selecione...' },
            { value: 'aprovado', label: 'Aprovar' },
            { value: 'reprovado', label: 'Reprovar' }
        ]
        const tituloJanela = ref('Solicitação de transferência')
        const preload = ref(false)
        const editando = ref(false)
        const cadastrado = ref(false)
        const cadastrando = ref(false)
        const atualizado = ref(false)
        const visualizar = ref(false)
        const aprovando = ref(false)
        const aprovandoGestorDestino = ref(false)
        const aprovandoGestorUnico = ref(false)
        const aprovandoExtra = ref(false)
        const aprovandoRh = ref(false)
        const aprovar_por_gestor = ref(false)
        const aprovar_por_rh = ref(false)
        const usuarioLogadoId = ref(null)
        const temAprovacaoExtra = ref(false)
        const podeAprovarExtra = ref(false)
        const nomeAprovacaoExtra = ref('')
        const exigeAprovacaoGestorOrigem = ref(true)
        const preloadExportacao = ref(false)
        const anexoUploadAndamento = ref(false)
        const podeanexar = ref(false)
        const selecionados = ref([])
        const selecionaTudo = ref(false)
        const dropdownAbertoKey = ref(null)
        const lista = ref([])
        const lista_ccs = ref(null)
        const formLotacaoOrigemCnpj = ref('')
        const formLotacaoDestinoCnpj = ref('')
        const centro_custos = ref([])
        const comboCcOrigem = ref(null)
        const comboCcDestino = ref(null)
        const comboFiltroStatus = ref(null)
        const comboFiltroCnpj = ref(null)
        const comboFiltroCc = ref(null)
        const comboFiltroOrdenacao = ref(null)
        const comboFiltroPages = ref(null)
        const vueInstance = getCurrentInstance()
        // Não usar proxy.temFilial: o return do setup sobrescreve o mixin e criaria ciclo (sempre false).
        const temFilial = computed(() => !!vueInstance?.proxy?.authconfiguracao?.temFilial)
        const preloadAtualizacao = ref(false)
        /** Inicia true (campo desabilitado). Só habilita quando colaborador não possui centro de custo. */
        const centroOrigemDesabilitadoPorColaborador = ref(true)
        const gestorOrigemAusente = ref(false)
        const gestorDestinoAusente = ref(false)
        const gestorDestinoCarregando = ref(false)
        const msgGestorDestinoAusente = MSG_GESTOR_DESTINO_AUSENTE

        const bloqueiaSalvarSemGestorDestino = computed(() => gestorDestinoAusente.value || gestorDestinoCarregando.value)
        const emFluxoAprovacao = computed(
            () =>
                visualizar.value ||
                aprovando.value ||
                aprovandoGestorDestino.value ||
                aprovandoGestorUnico.value ||
                aprovandoExtra.value ||
                aprovandoRh.value
        )
        const mostrarAvisoGestorDestinoAusente = computed(
            () =>
                gestorDestinoAusente.value &&
                !emFluxoAprovacao.value
        )

        const urlExportacao = `${URL_ADMIN}/planejamento/movimentacao/transferencia-prevista/export`
        const url_anexo = `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`
        const urlPaginacao = `${URL_ADMIN}/planejamento/movimentacao/transferencia-prevista/atualizar`
        const urlGestorResponsavel = `${URL_ADMIN}/planejamento/movimentacao/centro-custo`
        const mimes = ref([])

        const formDefault = createFormDefault()
        const formConfirmacaoDefault = createFormConfirmacaoDefault()
        const form = reactive({ ..._.cloneDeep(formDefault) })
        const formConfirmacao = reactive(_.cloneDeep(formConfirmacaoDefault))
        const controle = reactive({
            carregando: false,
            dados: createControleDados()
        })

        let _syncUrlTimer = null

        const naoAprovados = computed(() =>
            lista.value
                .filter((item) => item.status_aprovacao === null && (item.fluxo_gestores_automatico === false || item.gestor_id))
                .map((item) => item.id)
        )
        const centroCustosDestino = computed(() => centro_custos.value.filter((item) => centroCustoEstaAtivo(item)))

        function listaCcPorLotacao(cnpjKey, { somenteAtivos = false } = {}) {
            if (!lista_ccs.value) return []
            let lista = []
            if (temFilial.value) {
                if (!cnpjKey) return []
                lista = lista_ccs.value.centros_custos?.[cnpjKey] || []
            } else {
                const keys = Object.keys(lista_ccs.value.centros_custos || {})
                lista = keys.length ? lista_ccs.value.centros_custos[keys[0]] || [] : []
            }
            if (somenteAtivos) {
                lista = lista.filter((item) => centroCustoEstaAtivo(item))
            }
            return lista
        }

        const formLotacaoOpcoes = computed(() => {
            const opts = [{ value: '', label: 'Selecione a lotação...' }]
            if (!lista_ccs.value || !lista_ccs.value.cnpjs) return opts
            Object.keys(lista_ccs.value.cnpjs).forEach((key) => {
                const item = lista_ccs.value.cnpjs[key]
                opts.push({
                    value: key,
                    label: `${item.nome_fantasia} - ${item.cnpj}`,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        })

        const centroCustoOrigemOpcoes = computed(() =>
            listaCcPorLotacao(formLotacaoOrigemCnpj.value).map((item) => ({
                value: String(item.id),
                label: item.label || String(item.id),
                meta: item.matriz ? 'Matriz' : 'Filial',
                raw: item
            }))
        )
        const centroCustoDestinoOpcoes = computed(() =>
            listaCcPorLotacao(formLotacaoDestinoCnpj.value, { somenteAtivos: true }).map((item) => ({
                value: String(item.id),
                label: item.label || String(item.id),
                meta: item.matriz ? 'Matriz' : 'Filial',
                raw: item
            }))
        )
        const por_pagina = computed(() => POR_PAGINA_OPCOES)
        const paramsExport = computed(() => controle.dados)
        const tudoMarcado = computed(() => {
            const total = naoAprovados.value.length
            if (total === 0) return false
            const encontrados = naoAprovados.value.filter((id) => selecionados.value.indexOf(id) >= 0).length
            selecionaTudo.value = total === encontrados
            return total === encontrados
        })

        const totalFiltrosAtivos = computed(() => {
            const d = controle.dados
            let total = [d.campoBusca, d.campoCPF, d.campoStatus, d.campoCentroCusto].filter(
                (v) => v !== '' && v !== null && v !== undefined
            ).length
            if (temFilial.value && d.campoCnpj) total++
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) total++
            if (d.ordenacao && d.ordenacao !== 'created_at_desc') total++
            if (Number(d.pages) !== 50) total++
            return total
        })

        const campoBuscaUnificada = computed(() => {
            const d = controle.dados
            if (d.campoCPF) return d.campoCPF
            return d.campoBusca || ''
        })

        const buscaUnificadaEhCpf = computed(() => !!controle.dados.campoCPF)

        const filtroListaCentroCustoCnpj = computed(() => {
            if (!lista_ccs.value) return []
            if (controle.dados.campoCnpj !== '' && temFilial.value) {
                return lista_ccs.value.centros_custos[controle.dados.campoCnpj] || []
            }
            if (!temFilial.value) {
                const keys = Object.keys(lista_ccs.value.centros_custos || {})
                return keys.length ? lista_ccs.value.centros_custos[keys[0]] || [] : []
            }
            const all = []
            Object.values(lista_ccs.value.centros_custos || {}).forEach((lista) => {
                ;(lista || []).forEach((item) => all.push(item))
            })
            return all
        })

        const opcoesCnpj = computed(() => {
            const opts = [{ value: '', label: 'Todas as lotações' }]
            if (!lista_ccs.value || !lista_ccs.value.cnpjs) return opts
            Object.keys(lista_ccs.value.cnpjs).forEach((key) => {
                const item = lista_ccs.value.cnpjs[key]
                opts.push({
                    value: key,
                    label: `${item.nome_fantasia} - ${item.cnpj}`,
                    meta: item.cnpj
                })
            })
            return opts
        })

        const opcoesCentroCusto = computed(() => {
            const opts = [{ value: '', label: 'Todos os centros' }]
            ;(filtroListaCentroCustoCnpj.value || []).forEach((item) => {
                const value = item.matriz ? item.id : item.filial_id
                if (value == null || value === '') return
                opts.push({
                    value: String(value),
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        })

        const opcoesStatus = computed(() => {
            const opts = [
                { value: '', label: 'Todos os status' },
                { value: 'aberto', label: 'Em aberto' }
            ]
            if (exigeAprovacaoGestorOrigem.value) {
                opts.push(
                    { value: 'pendente_gestor_origem', label: 'Pendente Gestor Origem' },
                    { value: 'aprovado_gestor_origem', label: 'Aprovado Gestor Origem' },
                    { value: 'reprovado_gestor_origem', label: 'Reprovado Gestor Origem' }
                )
            }
            opts.push(
                { value: 'pendente_gestor_destino', label: 'Pendente Gestor Destino' },
                { value: 'aprovado_gestor_destino', label: 'Aprovado Gestor Destino' },
                { value: 'reprovado_gestor_destino', label: 'Reprovado Gestor Destino' },
                { value: 'pendente_gestor_unico', label: 'Pendente Gestor Aprovação' },
                { value: 'aprovado_gestor_unico', label: 'Aprovado Gestor Aprovação' },
                { value: 'reprovado_gestor_unico', label: 'Reprovado Gestor Aprovação' }
            )
            if (temAprovacaoExtra.value) {
                const nomeExtra = nomeAprovacaoExtra.value || 'Extra'
                opts.push(
                    { value: 'pendente_extra', label: `Pendente ${nomeExtra}` },
                    { value: 'aprovado_extra', label: `Aprovado ${nomeExtra}` },
                    { value: 'reprovado_extra', label: `Reprovado ${nomeExtra}` }
                )
            }
            opts.push(
                { value: 'pendente_rh', label: 'Pendente RH' },
                { value: 'aprovado', label: 'Aprovado RH' },
                { value: 'reprovado_rh', label: 'Reprovado RH' },
                { value: 'reprovado', label: 'Reprovado (qualquer etapa)' }
            )
            return opts
        })

        const opcoesOrdenacao = computed(() => [
            { value: 'created_at_desc', label: 'Mais recentes' },
            { value: 'created_at_asc', label: 'Mais antigos' },
            { value: 'updated_at_desc', label: 'Última modificação' }
        ])

        const opcoesPages = computed(() => por_pagina.value.map((n) => ({ value: String(n), label: String(n) })))

        const campoPagesCombo = computed({
            get() {
                return String(controle.dados.pages || 50)
            },
            set(valor) {
                controle.dados.pages = parseInt(valor, 10) || 50
            }
        })

        function fecharModalPrincipal() {
            try {
                if (modalRef.value?.fecharModal) modalRef.value.fecharModal()
            } catch (_e) {}
        }

        function getComponenteRef() {
            return componenteRef.value
        }

        function recarregarLista() {
            const comp = getComponenteRef()
            if (comp?.atual !== undefined) comp.atual = 1
            if (comp?.buscar) comp.buscar()
        }

        async function listaCentroCusto() {
            try {
                const { data } = await axios.post(`${URL_PUBLICO}/centro-custos/`, { incluir_inativos: true })
                centro_custos.value = data.centro_custos ?? []
                sincronizarDestinoAtivo()
                if (cadastrando.value) {
                    form.centro_custo_id = ''
                    form.autocomplete_label_colaborador_anterior = ''
                    form.autocomplete_label_colaborador = ''
                    form.colaborador_id = ''
                }
            } catch (_err) {
                preload.value = false
            }
        }

        function centroCustoEstaAtivo(item) {
            return item?.ativo === true || item?.ativo === 1 || item?.ativo === '1'
        }

        function sincronizarDestinoAtivo() {
            if (!form.centro_custo_destino_id) return
            const destino = centro_custos.value.find((item) => Number(item.id) === Number(form.centro_custo_destino_id))
            const modoLeitura = emFluxoAprovacao.value
            if (destino && !centroCustoEstaAtivo(destino) && !modoLeitura) {
                form.centro_custo_destino_id = ''
                form.label_gestor_destino = ''
                gestorDestinoAusente.value = false
            }
        }

        async function confirmaAtualizacaoStatus(confirmacao) {
            preloadAtualizacao.value = true
            formConfirmacao.status_aprovacao = confirmacao
            formConfirmacao.selecionados = [selecionados.value]
            try {
                await axios.post(`${BASE_URL}/atualizacao-status`, formConfirmacao)
                modalStatusRef.value?.fecharModal?.()
                if (typeof mostraSucesso === 'function') mostraSucesso('Status atualizados com sucesso!')
                selecionados.value = []
                Object.assign(formConfirmacao, _.cloneDeep(formConfirmacaoDefault))
                recarregarLista()
            } catch (_err) {
                if (typeof mostraErro === 'function') mostraErro(_err)
            } finally {
                preloadAtualizacao.value = false
            }
        }

        function formNovo() {
            cadastrando.value = true
            cadastrado.value = false
            atualizado.value = false
            editando.value = false
            aprovando.value = false
            aprovandoGestorDestino.value = false
            aprovandoGestorUnico.value = false
            aprovandoExtra.value = false
            aprovandoRh.value = false
            visualizar.value = false
            podeanexar.value = true
            tituloJanela.value = 'Solicitação de transferência'
            if (typeof formReset === 'function') formReset()
            if (typeof setupCampo === 'function') setupCampo()
            Object.assign(form, _.cloneDeep(formDefault))
            form.centro_custo_id = ''
            formLotacaoOrigemCnpj.value = ''
            formLotacaoDestinoCnpj.value = ''
            if (!temFilial.value && lista_ccs.value?.centros_custos) {
                const keys = Object.keys(lista_ccs.value.centros_custos || {})
                formLotacaoOrigemCnpj.value = keys[0] || ''
                formLotacaoDestinoCnpj.value = keys[0] || ''
            }
            centroOrigemDesabilitadoPorColaborador.value = true
            gestorOrigemAusente.value = false
            gestorDestinoAusente.value = false
            gestorDestinoCarregando.value = false
            listaCentroCusto()
        }

        function validarFormularioVisivel() {
            if (typeof $ === 'undefined') return true
            return validarInputsAtivosVisiveis(hash, {
                preservarIds: [`transferencia-data-${hash}`]
            })
        }

        /** DatePicker espera d/m/Y; API pode mandar ISO/Y-m-d. */
        function formatarDataBr(valor) {
            if (valor == null || valor === '') return 'Não informado'
            const texto = String(valor).trim()
            if (!texto || texto === 'Invalid date') return 'Não informado'
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(texto)) return texto
            const iso = texto.match(/^(\d{4})-(\d{2})-(\d{2})/)
            if (iso) return `${iso[3]}/${iso[2]}/${iso[1]}`
            if (typeof moment === 'function') {
                const m = moment(texto)
                if (m.isValid()) return m.format('DD/MM/YYYY')
            }
            return texto
        }

        function normalizarDataTransferenciaForm(valor) {
            if (valor == null || valor === '') return ''
            const texto = String(valor).trim()
            if (!texto || texto === 'Invalid date') return ''
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(texto)) return texto
            const iso = texto.match(/^(\d{4})-(\d{2})-(\d{2})/)
            if (iso) return `${iso[3]}/${iso[2]}/${iso[1]}`
            if (typeof moment === 'function') {
                const m = moment(texto)
                if (m.isValid()) return m.format('DD/MM/YYYY')
            }
            return ''
        }

        function validarColaboradorEGestor() {
            if (!form.colaborador_id) {
                const inputId = `colaborador_${hash}`
                if (typeof valida_campo_vazio === 'function') valida_campo_vazio($(`#${inputId}`), 1)
                $(`#${inputId}`).focus().trigger('blur')
                if (typeof mostraErro === 'function') mostraErro('', 'Campo COLABORADOR não pode ficar vazio')
                resetaCampoColaborador()
                return false
            }
            if (temFilial.value) {
                if (
                    !exigirCombobox(formLotacaoOrigemCnpj.value, `transferencia-lotacao-origem-${hash}`, {
                        toastMsg: 'Selecione a lotação de origem'
                    })
                ) {
                    return false
                }
            }
            if (
                !exigirCombobox(form.centro_custo_origem_id, `transferencia-cc-origem-${hash}`, {
                    toastMsg: 'Campo CENTRO DE CUSTO ORIGEM não pode ficar vazio'
                })
            ) {
                return false
            }
            if (temFilial.value) {
                if (
                    !exigirCombobox(formLotacaoDestinoCnpj.value, `transferencia-lotacao-destino-${hash}`, {
                        toastMsg: 'Selecione a lotação de destino'
                    })
                ) {
                    return false
                }
            }
            if (
                !exigirCombobox(form.centro_custo_destino_id, `transferencia-cc-destino-${hash}`, {
                    toastMsg: 'Campo CENTRO DE CUSTO DESTINO não pode ficar vazio'
                })
            ) {
                return false
            }
            const destinoSelecionado = centro_custos.value.find((item) => Number(item.id) === Number(form.centro_custo_destino_id))
            if (destinoSelecionado && !centroCustoEstaAtivo(destinoSelecionado)) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'O centro de custo de destino está inativo. Selecione um centro de custo ativo.')
                }
                form.centro_custo_destino_id = ''
                return false
            }
            if (
                !exigirCampoData(form.data_transferencia, `transferencia-data-${hash}`, {
                    toastMsg: 'Informe a data para transferência'
                })
            ) {
                return false
            }
            if (gestorDestinoCarregando.value) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'Aguarde a verificação do gestor de destino.')
                }
                return false
            }
            if (gestorDestinoAusente.value) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', MSG_GESTOR_DESTINO_AUSENTE)
                }
                return false
            }
            return true
        }

        function resetaCampoColaborador() {
            form.autocomplete_label_colaborador = ''
            form.autocomplete_label_colaborador_anterior = ''
            form.colaborador_id = ''
            form.centro_custo_origem_id = ''
            formLotacaoOrigemCnpj.value = !temFilial.value && lista_ccs.value?.centros_custos
                ? Object.keys(lista_ccs.value.centros_custos || {})[0] || ''
                : ''
            centroOrigemDesabilitadoPorColaborador.value = true
        }

        function resolverLotacaoPorCentroCustoId(centroCustoId) {
            if (!lista_ccs.value?.centros_custos || centroCustoId == null || centroCustoId === '') {
                return ''
            }
            const ccId = String(centroCustoId)
            if (!temFilial.value) {
                const keys = Object.keys(lista_ccs.value.centros_custos || {})
                return keys[0] || ''
            }
            let encontrado = ''
            Object.keys(lista_ccs.value.centros_custos).some((cnpjKey) => {
                const lista = lista_ccs.value.centros_custos[cnpjKey] || []
                const hit = _.find(lista, (item) => String(item.id) === ccId)
                if (hit) {
                    encontrado = cnpjKey
                    return true
                }
                return false
            })
            return encontrado
        }

        function onSelectFormLotacaoOrigem() {
            form.centro_custo_origem_id = ''
            form.label_gestor_origem = ''
            gestorOrigemAusente.value = false
            limparComboboxInvalido(`transferencia-lotacao-origem-${hash}`)
        }

        function onSelectFormLotacaoDestino() {
            form.centro_custo_destino_id = ''
            form.label_gestor_destino = ''
            gestorDestinoAusente.value = false
            limparComboboxInvalido(`transferencia-lotacao-destino-${hash}`)
        }

        /**
         * Ao selecionar colaborador na nova solicitação, preenche o Centro de Custo Origem
         * com o centro de custo atual do colaborador (se possuir). Se possuir, o campo fica desabilitado.
         */
        function onColaboradorSelecionado(model) {
            if (!cadastrando.value) return
            const centroOrigem = model.centro_custo_id ?? ''
            form.centro_custo_origem_id = centroOrigem ? String(centroOrigem) : ''
            formLotacaoOrigemCnpj.value = resolverLotacaoPorCentroCustoId(form.centro_custo_origem_id)
            centroOrigemDesabilitadoPorColaborador.value = !!centroOrigem
            carregarGestorResponsavel('origem', form.centro_custo_origem_id)
        }

        async function carregarGestorResponsavel(tipo, centroCustoId) {
            if (!centroCustoId) {
                if (tipo === 'origem') {
                    form.label_gestor_origem = ''
                    gestorOrigemAusente.value = false
                }
                if (tipo === 'destino') {
                    form.label_gestor_destino = ''
                    gestorDestinoAusente.value = false
                    gestorDestinoCarregando.value = false
                }
                return
            }
            if (tipo === 'destino') gestorDestinoCarregando.value = true
            try {
                const { data } = await axios.get(`${urlGestorResponsavel}/${centroCustoId}/gestor-responsavel`)
                const semGestor = !(data.gestor_resolvido?.id || data.gestor_principal?.id)
                const nome = data.gestor_resolvido?.nome ?? data.gestor_principal?.nome ?? 'Centro de custo sem gestor'
                if (tipo === 'origem') {
                    form.label_gestor_origem = nome
                    gestorOrigemAusente.value = semGestor
                }
                if (tipo === 'destino') {
                    form.label_gestor_destino = nome
                    gestorDestinoAusente.value = semGestor
                }
            } catch (_err) {
                if (tipo === 'origem') {
                    form.label_gestor_origem = 'Não informado'
                    gestorOrigemAusente.value = false
                }
                if (tipo === 'destino') {
                    form.label_gestor_destino = 'Centro de custo sem gestor'
                    gestorDestinoAusente.value = true
                }
            } finally {
                if (tipo === 'destino') gestorDestinoCarregando.value = false
            }
        }

        function origemEtapaDispensada(item) {
            return item.fluxo_gestores_automatico !== false && !item.gestor_id && !item.status_aprovacao
        }

        /** Empresa/config omitiu a etapa: origem não entra no fluxo visual. */
        function origemEtapaOmitidaDoFluxo(item) {
            if (item && item.exige_aprovacao_gestor_origem === false) return true
            if (!exigeAprovacaoGestorOrigem.value) return true
            const obs = item?.obs_aprovacao
            return typeof obs === 'string' && obs.includes('não exigir aprovação do gestor de origem')
        }

        /** Empresa não exige aprovação do gestor origem: etapa some da UI (timeline/modal/badge). */
        function exibeEtapaGestorOrigem(item) {
            if (!item || item.modo_aprovacao === 'gestor_unico') return false
            if (origemEtapaOmitidaDoFluxo(item)) return false
            return true
        }

        function origemEtapaConcluida(item) {
            if (origemEtapaOmitidaDoFluxo(item)) return true
            if (origemEtapaDispensada(item)) return true
            return item.status_aprovacao === 'aprovado'
        }

        function gestorOrigemEhSolicitanteItem(item) {
            return !!item.gestor_id && Number(item.gestor_id) === Number(item.user_id)
        }

        function gestorDestinoEhSolicitanteItem(item) {
            return !!item.gestor_destino_id && Number(item.gestor_destino_id) === Number(item.user_id)
        }

        function podeAprovarGestorOrigemItem(item) {
            if (origemEtapaOmitidaDoFluxo(item)) return false
            if (item.fluxo_gestores_automatico === false) {
                return (aprovar_por_gestor.value || aprovar_por_rh.value) && !item.status_aprovacao
            }
            if (item.status_aprovacao || !item.gestor_id) return false
            if (aprovar_por_rh.value) return true
            if (gestorOrigemEhSolicitanteItem(item)) return false
            return item.gestor_id === usuarioLogadoId.value
        }

        function podeAprovarGestorDestinoItem(item) {
            if (!item.gestor_destino_id) return false
            if (!origemEtapaConcluida(item)) return false
            if (aprovar_por_rh.value) return true
            if (gestorDestinoEhSolicitanteItem(item)) return false
            return item.gestor_destino_id === usuarioLogadoId.value
        }

        function gestorUnicoDispensadoItem(item) {
            if (item.modo_aprovacao !== 'gestor_unico' || !item.gestor_aprovacao_id) return false
            return Number(item.gestor_aprovacao_id) === Number(item.user_id)
        }

        function podeAprovarGestorUnicoItem(item) {
            if (item.modo_aprovacao !== 'gestor_unico' || !item.gestor_aprovacao_id) return false
            if (gestorUnicoDispensadoItem(item)) return false
            return item.gestor_aprovacao_id === usuarioLogadoId.value || aprovar_por_rh.value
        }

        function labelAprovadorOrigem(item) {
            const nome = item.user_aprovacao?.nome
            if (!nome) return ''
            if (Number(item.user_aprovacao_id) && Number(item.gestor_id) && Number(item.user_aprovacao_id) !== Number(item.gestor_id)) {
                return `${nome} (RH)`
            }
            return nome
        }

        function labelAprovadorDestino(item) {
            const nome = item.quem_aprovou_gestor_destino?.nome
            if (!nome) return ''
            if (
                Number(item.user_aprovacao_gestor_destino_id) &&
                Number(item.gestor_destino_id) &&
                Number(item.user_aprovacao_gestor_destino_id) !== Number(item.gestor_destino_id)
            ) {
                return `${nome} (RH)`
            }
            return nome
        }

        function labelAprovadorGestorUnico(item) {
            const nome = item.quem_aprovou_gestor_unico?.nome
            if (!nome) return ''
            if (
                Number(item.user_aprovacao_gestor_unico_id) &&
                Number(item.gestor_aprovacao_id) &&
                Number(item.user_aprovacao_gestor_unico_id) !== Number(item.gestor_aprovacao_id)
            ) {
                return `${nome} (RH)`
            }
            return nome
        }

        function gestoresConcluidosItem(item) {
            if (item.modo_aprovacao === 'gestor_unico') {
                if (gestorUnicoDispensadoItem(item)) return true
                return item.status_aprovacao_gestor_unico === 'aprovado'
            }
            if (item.fluxo_gestores_automatico === false) {
                return item.status_aprovacao === 'aprovado'
            }
            if (!origemEtapaConcluida(item)) return false
            if (item.exige_aprovacao_gestor_destino) {
                return item.status_aprovacao_gestor_destino === 'aprovado'
            }
            return true
        }

        function itemReprovado(item) {
            return (
                item.status_aprovacao === 'reprovado' ||
                item.status_aprovacao_gestor_destino === 'reprovado' ||
                item.status_aprovacao_gestor_unico === 'reprovado' ||
                item.status_aprovacao_extra === 'reprovado' ||
                item.resposta_rh === 'reprovado'
            )
        }

        function fluxoCanceladoAntesExtra(item) {
            if (item.modo_aprovacao === 'gestor_unico') {
                return item.status_aprovacao_gestor_unico === 'reprovado'
            }
            return item.status_aprovacao === 'reprovado' || item.status_aprovacao_gestor_destino === 'reprovado'
        }

        function fluxoCanceladoAntesRh(item) {
            return fluxoCanceladoAntesExtra(item) || item.status_aprovacao_extra === 'reprovado'
        }

        function etapaRhAguardandoItem(item) {
            if (fluxoCanceladoAntesRh(item)) return false
            if (temAprovacaoExtra.value) {
                return item.status_aprovacao_extra === 'aprovado' && !item.resposta_rh
            }
            return gestoresConcluidosItem(item) && !item.resposta_rh
        }

        function tituloCardLista(item) {
            return item?.colaborador?.nome || 'Colaborador não informado'
        }

        function ccOrigemLista(item) {
            return item?.centro_custo_origem?.label ?? 'Não informado'
        }

        function ccDestinoLista(item) {
            return item?.centro_custo_destino?.label ?? 'Não informado'
        }

        function solicitanteLista(item) {
            return item?.user_cadastrou?.nome ?? 'Não informado'
        }

        function chaveStatusLista(item) {
            if (!item) return 'aberto'
            if (itemReprovado(item)) return 'reprovado'
            if (item.resposta_rh === 'aprovado') return 'rh'
            if (temAprovacaoExtra.value && item.status_aprovacao_extra === 'aprovado') return 'extra'
            if (
                item.modo_aprovacao === 'gestor_unico' &&
                (item.status_aprovacao_gestor_unico === 'aprovado' || gestorUnicoDispensadoItem(item))
            ) {
                return 'gestor'
            }
            if (item.exige_aprovacao_gestor_destino && item.status_aprovacao_gestor_destino === 'aprovado') {
                return 'gestor'
            }
            if (exibeEtapaGestorOrigem(item) && item.status_aprovacao === 'aprovado') return 'gestor'
            return 'aberto'
        }

        function classeBordaStatusLista(item) {
            return `mybp-card-corpo--${chaveStatusLista(item)}`
        }

        function textoStatusLista(item) {
            if (itemReprovado(item)) return 'Reprovado'
            if (item.resposta_rh === 'aprovado') return 'Aprovado RH'
            if (temAprovacaoExtra.value && item.status_aprovacao_extra === 'aprovado') {
                return `Aprovado ${nomeAprovacaoExtra.value || 'Extra'}`
            }
            if (item.exige_aprovacao_gestor_destino && item.status_aprovacao_gestor_destino === 'aprovado') {
                return 'Aprovado Gestor Destino'
            }
            if (origemEtapaDispensada(item) && item.exige_aprovacao_gestor_destino) {
                return 'Aguardando Gestor Destino'
            }
            if (
                origemEtapaOmitidaDoFluxo(item) &&
                item.exige_aprovacao_gestor_destino &&
                !item.status_aprovacao_gestor_destino &&
                !itemReprovado(item)
            ) {
                return 'Aguardando Gestor Destino'
            }
            if (exibeEtapaGestorOrigem(item) && item.status_aprovacao === 'aprovado') {
                return 'Aprovado Gestor Origem'
            }
            if (
                item.modo_aprovacao === 'gestor_unico' &&
                (item.status_aprovacao_gestor_unico === 'aprovado' || gestorUnicoDispensadoItem(item))
            ) {
                return 'Aprovado Gestor Aprovação'
            }
            if (item.modo_aprovacao === 'gestor_unico') {
                return 'Aguardando Gestor Aprovação'
            }
            return 'Em aberto'
        }

        function fluxoStepsLista(item) {
            if (!item) return []

            const steps = [
                {
                    key: 'solicitante',
                    label: 'Solicitante',
                    status: 'aprovado',
                    nome: item.user_cadastrou?.nome,
                    data: item.created_at
                }
            ]

            if (exibeEtapaGestorOrigem(item)) {
                let statusOrigem = 'aguardando'
                let nomeOrigem = ''
                let dataOrigem = item.data_aprovacao
                let statusTextoOrigem

                if (origemEtapaDispensada(item)) {
                    statusOrigem = 'aprovado'
                    statusTextoOrigem = 'Sem gestor'
                    dataOrigem = undefined
                } else if (item.status_aprovacao === 'aprovado') {
                    statusOrigem = 'aprovado'
                    nomeOrigem = labelAprovadorOrigem(item)
                } else if (item.status_aprovacao === 'reprovado') {
                    statusOrigem = 'reprovado'
                    nomeOrigem = labelAprovadorOrigem(item)
                }

                steps.push({
                    key: 'gestor-origem',
                    label: 'Gestor Origem',
                    status: statusOrigem,
                    nome: nomeOrigem,
                    data: dataOrigem,
                    statusTexto: statusTextoOrigem
                })
            } else if (item.modo_aprovacao === 'gestor_unico') {
                let statusUnico = 'aguardando'
                let nomeUnico = ''
                let dataUnico = item.data_aprovacao_gestor_unico

                if (gestorUnicoDispensadoItem(item) || item.status_aprovacao_gestor_unico === 'aprovado') {
                    statusUnico = 'aprovado'
                    nomeUnico = gestorUnicoDispensadoItem(item)
                        ? item.gestor_aprovacao_unico?.nome
                        : labelAprovadorGestorUnico(item)
                    if (gestorUnicoDispensadoItem(item) && !dataUnico) {
                        dataUnico = item.created_at
                    }
                } else if (item.status_aprovacao_gestor_unico === 'reprovado') {
                    statusUnico = 'reprovado'
                    nomeUnico = labelAprovadorGestorUnico(item)
                }

                steps.push({
                    key: 'gestor-unico',
                    label: 'Gestor Aprovação',
                    status: statusUnico,
                    nome: nomeUnico,
                    data: dataUnico
                })
            }

            if (item.modo_aprovacao !== 'gestor_unico') {
                let statusDestino = 'pendente'
                let nomeDestino = ''
                let dataDestino = item.data_aprovacao_gestor_destino
                let statusTextoDestino

                if (item.status_aprovacao === 'reprovado') {
                    statusDestino = 'cancelado'
                    statusTextoDestino = 'Cancelada'
                } else if (!item.exige_aprovacao_gestor_destino && item.status_aprovacao === 'aprovado') {
                    statusDestino = 'aprovado'
                    nomeDestino = labelAprovadorOrigem(item)
                    dataDestino = item.data_aprovacao
                } else if (!item.exige_aprovacao_gestor_destino && item.gestor_origem) {
                    statusDestino = 'aguardando'
                    statusTextoDestino = item.gestor_origem?.nome || 'Aguardando'
                } else if (item.status_aprovacao_gestor_destino === 'aprovado') {
                    statusDestino = 'aprovado'
                    nomeDestino = labelAprovadorDestino(item)
                } else if (item.status_aprovacao_gestor_destino === 'reprovado') {
                    statusDestino = 'reprovado'
                    nomeDestino = labelAprovadorDestino(item)
                } else if (origemEtapaConcluida(item)) {
                    statusDestino = 'aguardando'
                }

                steps.push({
                    key: 'gestor-destino',
                    label: 'Gestor Destino',
                    status: statusDestino,
                    nome: nomeDestino,
                    data: dataDestino,
                    statusTexto: statusTextoDestino
                })
            }

            if (temAprovacaoExtra.value) {
                let statusExtra = 'pendente'
                let statusTextoExtra
                if (fluxoCanceladoAntesExtra(item)) {
                    statusExtra = 'cancelado'
                    statusTextoExtra = 'Cancelada'
                } else if (item.status_aprovacao_extra === 'aprovado') {
                    statusExtra = 'aprovado'
                } else if (item.status_aprovacao_extra === 'reprovado') {
                    statusExtra = 'reprovado'
                } else if (gestoresConcluidosItem(item) && !item.status_aprovacao_extra) {
                    statusExtra = 'aguardando'
                }

                steps.push({
                    key: 'extra',
                    label: nomeAprovacaoExtra.value || 'Extra',
                    status: statusExtra,
                    nome: item.user_aprovacao_extra?.nome,
                    data: item.data_aprovacao_extra,
                    statusTexto: statusTextoExtra
                })
            }

            let statusRh = 'pendente'
            let statusTextoRh
            if (fluxoCanceladoAntesRh(item)) {
                statusRh = 'cancelado'
                statusTextoRh = 'Cancelada'
            } else if (item.resposta_rh === 'aprovado') {
                statusRh = 'aprovado'
            } else if (item.resposta_rh === 'reprovado') {
                statusRh = 'reprovado'
            } else if (etapaRhAguardandoItem(item)) {
                statusRh = 'aguardando'
            }

            steps.push({
                key: 'rh',
                label: 'RH',
                status: statusRh,
                nome: item.rh_aprovacao?.nome,
                data: item.data_aprovacao_rh,
                statusTexto: statusTextoRh
            })

            return steps
        }

        async function cadastrar() {
            if (!validarColaboradorEGestor() || !validarFormularioVisivel()) return
            preload.value = true
            try {
                await axios.post(BASE_URL, form, { errorHandle: false })
                fecharModalPrincipal()
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Solicitação registrada com sucesso!')
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err.response?.data || err)
            } finally {
                preload.value = false
            }
        }

        function setModoAprovacao(opt) {
            cadastrando.value = false
            cadastrado.value = false
            atualizado.value = false
            editando.value = false
            visualizar.value = opt.visualizar ?? false
            aprovando.value = opt.aprovando ?? false
            aprovandoGestorDestino.value = opt.aprovandoGestorDestino ?? false
            aprovandoGestorUnico.value = opt.aprovandoGestorUnico ?? false
            aprovandoExtra.value = opt.aprovandoExtra ?? false
            aprovandoRh.value = opt.aprovandoRh ?? false
            podeanexar.value = opt.podeanexar ?? false
        }

        async function formOpen(id) {
            Object.assign(form, formDefault)
            form.id = id
            tituloJanela.value = `#${id}`
            if (typeof formReset === 'function') formReset()
            preload.value = true
            try {
                const { data } = await axios.get(`${BASE_URL}/${id}/editar`)
                Object.assign(form, data)
                form.anexos = Array.isArray(form.anexos) ? form.anexos : []
                form.anexosDel = Array.isArray(form.anexosDel) ? form.anexosDel : []
                form.data_transferencia = normalizarDataTransferenciaForm(data.data_transferencia)
                form.centro_custo_origem_id =
                    data.centro_custo_origem_id != null && data.centro_custo_origem_id !== ''
                        ? String(data.centro_custo_origem_id)
                        : ''
                form.centro_custo_destino_id =
                    data.centro_custo_destino_id != null && data.centro_custo_destino_id !== ''
                        ? String(data.centro_custo_destino_id)
                        : ''
                formLotacaoOrigemCnpj.value = resolverLotacaoPorCentroCustoId(form.centro_custo_origem_id)
                formLotacaoDestinoCnpj.value = resolverLotacaoPorCentroCustoId(form.centro_custo_destino_id)
                await listaCentroCusto()
                form.centro_custo_id = data.centro_custo_id ?? ''
                tituloJanela.value = `#${id} Solicitação de transferência`
                if (aprovando.value) {
                    form.status_aprovacao = data.status_aprovacao == null ? '' : data.status_aprovacao
                    form.obs_aprovacao = data.obs_aprovacao == null ? '' : data.obs_aprovacao
                }
                if (aprovandoExtra.value) {
                    form.status_aprovacao_extra = data.status_aprovacao_extra == null ? '' : data.status_aprovacao_extra
                    form.obs_aprovacao_extra = data.obs_aprovacao_extra == null ? '' : data.obs_aprovacao_extra
                }
                if (aprovandoRh.value) {
                    form.resposta_rh = data.resposta_rh == null ? '' : data.resposta_rh
                    form.obs_rh = data.obs_rh == null ? '' : data.obs_rh
                }
                if (aprovandoGestorDestino.value) {
                    form.status_aprovacao_gestor_destino = data.status_aprovacao_gestor_destino == null ? '' : data.status_aprovacao_gestor_destino
                    form.obs_aprovacao_gestor_destino = data.obs_aprovacao_gestor_destino == null ? '' : data.obs_aprovacao_gestor_destino
                }
                if (aprovandoGestorUnico.value) {
                    form.status_aprovacao_gestor_unico = data.status_aprovacao_gestor_unico == null ? '' : data.status_aprovacao_gestor_unico
                    form.obs_aprovacao_gestor_unico = data.obs_aprovacao_gestor_unico == null ? '' : data.obs_aprovacao_gestor_unico
                }
                form.label_gestor_origem = data.label_gestor_origem ?? ''
                form.label_gestor_destino = data.label_gestor_destino ?? ''
                form.label_gestor_aprovacao_unico = data.label_gestor_aprovacao_unico ?? ''
                form.modo_aprovacao = data.modo_aprovacao ?? 'padrao'
                gestorOrigemAusente.value = !!(data.fluxo_gestores_automatico && !data.gestor_id && data.centro_custo_origem_id)
                gestorDestinoAusente.value = false
                form.exige_aprovacao_gestor_destino = data.exige_aprovacao_gestor_destino ?? false
                form.fluxo_gestores_automatico = data.fluxo_gestores_automatico ?? false
                temAprovacaoExtra.value = data.tem_aprovacao_extra || false
                podeAprovarExtra.value = data.pode_aprovar_extra || false
                nomeAprovacaoExtra.value = data.nome_aprovacao_extra || 'Aprovação Extra'
                centroOrigemDesabilitadoPorColaborador.value = !!(data.centro_custo_id ?? '')
                if (!emFluxoAprovacao.value) {
                    editando.value = true
                }
                sincronizarDestinoAtivo()
                await carregarGestorResponsavel('origem', form.centro_custo_origem_id)
                await carregarGestorResponsavel('destino', form.centro_custo_destino_id)
            } catch (_err) {
                if (typeof mostraErro === 'function') mostraErro(_err)
            } finally {
                preload.value = false
            }
        }

        function abrirModalAposFormOpen(id, modo) {
            setModoAprovacao(modo)
            formOpen(id)
            nextTick(() => {
                if (modalRef.value?.abrirModal) modalRef.value.abrirModal()
            })
        }

        async function alterar() {
            if (!validarColaboradorEGestor() || !validarFormularioVisivel()) return
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}`, form)
                fecharModalPrincipal()
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Solicitação alterada com sucesso!')
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function aprovar() {
            if (
                !exigirCombobox(form.status_aprovacao, `transferencia-status-origem-${hash}`, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}/aprovar`, form)
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Registro salvo com sucesso!')
                fecharModalPrincipal()
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function aprovarExtra() {
            if (
                !exigirCombobox(form.status_aprovacao_extra, `transferencia-status-extra-${hash}`, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}/aprovar-extra`, form)
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Aprovação extra salva com sucesso!')
                fecharModalPrincipal()
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function aprovarGestorDestino() {
            if (
                !exigirCombobox(form.status_aprovacao_gestor_destino, `transferencia-status-destino-${hash}`, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}/aprovar-gestor-destino`, form)
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Aprovação do gestor destino salva com sucesso!')
                fecharModalPrincipal()
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function aprovarGestorUnico() {
            if (
                !exigirCombobox(form.status_aprovacao_gestor_unico, `transferencia-status-gestor-unico-${hash}`, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}/aprovar-gestor-unico`, form)
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Aprovação salva com sucesso!')
                fecharModalPrincipal()
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function aprovarRH() {
            if (
                !exigirCombobox(form.resposta_rh, `transferencia-status-rh-${hash}`, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            preload.value = true
            try {
                await axios.put(`${BASE_URL}/${form.id}/aprovarrh`, form)
                if (typeof mostraSucesso === 'function') mostraSucesso('', 'Aprovação RH salva com sucesso!')
                fecharModalPrincipal()
                recarregarLista()
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preload.value = false
            }
        }

        async function exportaExcel() {
            preloadExportacao.value = true
            if (typeof mostraSucesso === 'function') {
                mostraSucesso('Estamos gerando seu arquivo excel, assim que finalizado você será notificado.')
            }
            try {
                await axios.post(urlExportacao, controle.dados)
            } catch (err) {
                if (typeof mostraErro === 'function') mostraErro(err)
            } finally {
                preloadExportacao.value = false
            }
        }

        function carregou(dados) {
            lista.value = dados.itens ?? []
            aprovar_por_gestor.value = dados.aprovar_por_gestor ?? false
            aprovar_por_rh.value = dados.aprovar_por_rh ?? false
            temAprovacaoExtra.value = dados.tem_aprovacao_extra ?? false
            podeAprovarExtra.value = dados.pode_aprovar_extra ?? false
            nomeAprovacaoExtra.value = dados.nome_aprovacao_extra ?? 'Aprovação Extra'
            usuarioLogadoId.value = dados.usuario_logado_id ?? null
            if (dados && Object.prototype.hasOwnProperty.call(dados, 'exige_aprovacao_gestor_origem')) {
                exigeAprovacaoGestorOrigem.value =
                    dados.exige_aprovacao_gestor_origem === true ||
                    dados.exige_aprovacao_gestor_origem === 1 ||
                    dados.exige_aprovacao_gestor_origem === '1'
            }
            if (dados.cc) {
                lista_ccs.value = dados.cc
            }
            if (dados.mimes) {
                mimes.value = dados.mimes
            }
            controle.carregando = false
        }

        function carregando() {
            controle.carregando = true
        }

        function atualizar() {
            const comp = getComponenteRef()
            if (comp) comp.atual = 1
            recarregarLista()
        }

        function buscarFiltro() {
            getComponenteRef()?.buscar?.()
        }

        function abrirModalSolicitar() {
            formNovo()
            nextTick(() => {
                if (modalRef.value?.abrirModal) modalRef.value.abrirModal()
            })
        }

        function abrirModalAtualizarStatus() {
            modalStatusRef.value?.abrirModal?.()
        }

        function toggleDropdown(itemId) {
            if (!itemId) return
            const key = `mov_trans:${itemId}`
            dropdownAbertoKey.value = dropdownAbertoKey.value === key ? null : key
        }

        function isDropdownOpen(itemId) {
            return dropdownAbertoKey.value === `mov_trans:${itemId}`
        }

        function fecharDropdown() {
            dropdownAbertoKey.value = null
        }

        function fecharOutrosComboboxes(manter) {
            const mapa = {
                'form-cc-origem': comboCcOrigem,
                'form-cc-destino': comboCcDestino,
                'transferencia-status': comboFiltroStatus,
                'transferencia-cnpj': comboFiltroCnpj,
                'transferencia-cc': comboFiltroCc,
                'transferencia-ordenacao': comboFiltroOrdenacao,
                'transferencia-pages': comboFiltroPages
            }
            Object.keys(mapa).forEach((id) => {
                if (id === manter) return
                const comboRef = mapa[id]
                if (comboRef?.value?.close) comboRef.value.close()
            })
        }

        function limparFiltros() {
            const d = controle.dados
            d.campoBusca = ''
            d.campoCPF = ''
            d.campoStatus = ''
            d.campoCentroCusto = ''
            d.filtroPeriodo = false
            d.dataInicio = ''
            d.dataFim = ''
            d.ordenacao = 'created_at_desc'
            d.pages = 50
            d.token = ''
            d.campoCnpj = ''
            atualizar()
        }

        function formatarCpfDigitos(valor) {
            const digitos = String(valor || '')
                .replace(/\D/g, '')
                .slice(0, 11)
            if (digitos.length <= 3) return digitos
            if (digitos.length <= 6) return `${digitos.slice(0, 3)}.${digitos.slice(3)}`
            if (digitos.length <= 9) return `${digitos.slice(0, 3)}.${digitos.slice(3, 6)}.${digitos.slice(6)}`
            return `${digitos.slice(0, 3)}.${digitos.slice(3, 6)}.${digitos.slice(6, 9)}-${digitos.slice(9)}`
        }

        function parecePadraoCpf(valor) {
            const raw = String(valor || '').trim()
            if (!raw) return false
            if (!/^[\d.\-\s]+$/.test(raw)) return false
            const digitos = raw.replace(/\D/g, '')
            return digitos.length > 0 && digitos.length <= 11
        }

        function onInputBuscaUnificada(event) {
            const valor = event && event.target ? event.target.value : ''
            const d = controle.dados
            if (!valor) {
                d.campoBusca = ''
                d.campoCPF = ''
                return
            }
            if (parecePadraoCpf(valor)) {
                const mascarado = formatarCpfDigitos(valor)
                d.campoCPF = mascarado
                d.campoBusca = ''
                if (event.target && event.target.value !== mascarado) {
                    event.target.value = mascarado
                }
                return
            }
            d.campoBusca = valor
            d.campoCPF = ''
        }

        function onSelectCnpj() {
            controle.dados.campoCentroCusto = ''
            atualizar()
        }

        function onSelectFiltro() {
            atualizar()
        }

        function onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            if (event?.target?.closest?.('.mybp-combobox-wrap')) return
            if (event?.target?.closest?.('.ma-autocomplete-list')) return
            dropdownAbertoKey.value = null
            fecharOutrosComboboxes(null)
        }

        function urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            controle.dados.token = urlParams.get('token') || ''
            const pages = urlParams.get('pages')
            if (pages) controle.dados.pages = parseInt(pages, 10) || 50
            const ordenacao = urlParams.get('ordenacao')
            if (ordenacao) controle.dados.ordenacao = ordenacao
            const campoBusca = urlParams.get('campoBusca')
            if (campoBusca) controle.dados.campoBusca = campoBusca
            const campoCPF = urlParams.get('campoCPF')
            if (campoCPF) controle.dados.campoCPF = campoCPF
            const campoStatus = urlParams.get('campoStatus')
            if (campoStatus) controle.dados.campoStatus = campoStatus
            const campoCentroCusto = urlParams.get('campoCentroCusto')
            if (campoCentroCusto) controle.dados.campoCentroCusto = campoCentroCusto
            const dataInicio = urlParams.get('dataInicio')
            if (dataInicio) controle.dados.dataInicio = dataInicio
            const dataFim = urlParams.get('dataFim')
            if (dataFim) controle.dados.dataFim = dataFim
            if (dataInicio || dataFim) controle.dados.filtroPeriodo = true
        }

        function syncUrlFiltros() {
            if (typeof atualizarUrlMovimentacao !== 'function') return
            const d = controle.dados
            const params = { pages: d.pages || 50, ordenacao: d.ordenacao || 'created_at_desc' }
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoCPF) params.campoCPF = d.campoCPF
            if (d.campoStatus) params.campoStatus = d.campoStatus
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            if (d.token) params.token = d.token
            atualizarUrlMovimentacao(params)
        }

        function selecionaTodos() {
            selecionaTudo.value = !selecionaTudo.value
            if (selecionaTudo.value) {
                naoAprovados.value.forEach((id) => {
                    if (selecionados.value.indexOf(id) === -1) selecionados.value.push(id)
                })
            } else {
                naoAprovados.value.forEach((id) => {
                    const idx = selecionados.value.indexOf(id)
                    if (idx >= 0) selecionados.value.splice(idx, 1)
                })
            }
        }

        watch(
            () => form.centro_custo_origem_id,
            (valor) => {
                if (cadastrando.value || (editando.value && !form.status_aprovacao)) {
                    carregarGestorResponsavel('origem', valor)
                }
            }
        )

        watch(
            () => form.centro_custo_destino_id,
            (valor) => {
                if (cadastrando.value || (editando.value && !form.status_aprovacao)) {
                    carregarGestorResponsavel('destino', valor)
                }
            }
        )

        watch(
            () => controle.dados,
            () => {
                if (_syncUrlTimer) clearTimeout(_syncUrlTimer)
                _syncUrlTimer = setTimeout(syncUrlFiltros, 400)
            },
            { deep: true }
        )

        onMounted(() => {
            const instance = getCurrentInstance()
            const validaFn = instance?.proxy?.valida_campo_vazio ?? (typeof valida_campo_vazio === 'function' ? valida_campo_vazio : null)
            if (validaFn) {
                window.validaCampo = (el, tipo) => validaFn(el, tipo)
            }
            urlParamGet()
            Object.assign(formDefault, _.cloneDeep(form))
            Object.assign(formConfirmacaoDefault, _.cloneDeep(formConfirmacao))
            nextTick(atualizar)
            document.addEventListener('click', onClickOutside)
        })

        onBeforeUnmount(() => {
            document.removeEventListener('click', onClickOutside)
        })

        return {
            modalRef,
            modal_janelaAtualizaStatus: modalStatusRef,
            componente: componenteRef,
            hash,
            formStatusAprovacaoOpcoes,
            limparComboboxInvalido,
            limparCampoDataInvalido,
            tituloJanela,
            preload,
            editando,
            cadastrado,
            cadastrando,
            atualizado,
            visualizar,
            aprovando,
            aprovandoGestorDestino,
            aprovandoGestorUnico,
            aprovandoExtra,
            aprovandoRh,
            aprovar_por_gestor,
            aprovar_por_rh,
            temAprovacaoExtra,
            podeAprovarExtra,
            nomeAprovacaoExtra,
            exigeAprovacaoGestorOrigem,
            preloadExportacao,
            urlExportacao,
            url_anexo,
            anexoUploadAndamento,
            podeanexar,
            mimes,
            selecionados,
            selecionaTudo,
            dropdownAbertoKey,
            form,
            formConfirmacao,
            formDefault,
            formConfirmacaoDefault,
            lista,
            lista_ccs,
            formLotacaoOrigemCnpj,
            formLotacaoDestinoCnpj,
            formLotacaoOpcoes,
            onSelectFormLotacaoOrigem,
            onSelectFormLotacaoDestino,
            temFilial,
            totalFiltrosAtivos,
            campoBuscaUnificada,
            buscaUnificadaEhCpf,
            opcoesCnpj,
            opcoesCentroCusto,
            opcoesStatus,
            opcoesOrdenacao,
            opcoesPages,
            campoPagesCombo,
            comboFiltroStatus,
            comboFiltroCnpj,
            comboFiltroCc,
            comboFiltroOrdenacao,
            comboFiltroPages,
            limparFiltros,
            onInputBuscaUnificada,
            onSelectCnpj,
            onSelectFiltro,
            centro_custos,
            centroCustosDestino,
            centroCustoOrigemOpcoes,
            centroCustoDestinoOpcoes,
            comboCcOrigem,
            comboCcDestino,
            centroOrigemDesabilitadoPorColaborador,
            gestorOrigemAusente,
            gestorDestinoAusente,
            gestorDestinoCarregando,
            msgGestorDestinoAusente,
            bloqueiaSalvarSemGestorDestino,
            mostrarAvisoGestorDestinoAusente,
            emFluxoAprovacao,
            urlPaginacao,
            controle,
            naoAprovados,
            por_pagina,
            paramsExport,
            tudoMarcado,
            listaCentroCusto,
            confirmaAtualizacaoStatus,
            formNovo,
            cadastrar,
            formOpen,
            alterar,
            aprovar,
            aprovarGestorDestino,
            aprovarGestorUnico,
            aprovarExtra,
            aprovarRH,
            podeAprovarGestorOrigemItem,
            podeAprovarGestorDestinoItem,
            gestorOrigemEhSolicitanteItem,
            gestorDestinoEhSolicitanteItem,
            podeAprovarGestorUnicoItem,
            gestorUnicoDispensadoItem,
            labelAprovadorOrigem,
            labelAprovadorDestino,
            labelAprovadorGestorUnico,
            origemEtapaDispensada,
            origemEtapaOmitidaDoFluxo,
            exibeEtapaGestorOrigem,
            origemEtapaConcluida,
            gestoresConcluidosItem,
            itemReprovado,
            tituloCardLista,
            ccOrigemLista,
            ccDestinoLista,
            solicitanteLista,
            formatarDataBr,
            chaveStatusLista,
            classeBordaStatusLista,
            textoStatusLista,
            fluxoStepsLista,
            fluxoCanceladoAntesExtra,
            fluxoCanceladoAntesRh,
            etapaRhAguardandoItem,
            carregarGestorResponsavel,
            exportaExcel,
            carregou,
            carregando,
            atualizar,
            toggleDropdown,
            isDropdownOpen,
            fecharDropdown,
            fecharOutrosComboboxes,
            urlParamGet,
            syncUrlFiltros,
            selecionaTodos,
            resetaCampoColaborador,
            onColaboradorSelecionado,
            abrirModalAposFormOpen,
            abrirModalSolicitar,
            abrirModalAtualizarStatus,
            buscarFiltro,
            validarFormularioVisivel,
            validarColaboradorEGestor
        }
    }
})
</script>

<style scoped>
.transferencia-filtro-hint {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    margin-left: 0.35rem;
    padding: 0.05rem 0.35rem;
    border-radius: 999px;
    background: rgba(23, 66, 87, 0.12);
    color: var(--primary, #174257);
    font-size: 0.65rem;
    font-weight: 600;
    vertical-align: middle;
}
</style>
