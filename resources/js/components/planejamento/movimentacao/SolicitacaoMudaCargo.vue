<template>
    <div>
        <modal :id="hash" :titulo="tituloJanela" :size="90" :ref="hash">
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
                        <legend>Colaborador</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Colaborador <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :caminho="`autocomplete/colaboradores`"
                                        :formsm="true"
                                        :valido="form.colaborador_id !== ''"
                                        v-model="form.autocomplete_label_colaborador"
                                        placeholder="Selecione um(a) colaborador(a)"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        :id="`colaborador_${hash}`"
                                        @onblur="resetaCampoColaborador"
                                        @onselect="selecionaColaborador"
                                    ></autocomplete>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id" class="mybp-modal-secao">
                        <legend>Dados atuais</legend>
                        <div class="row">
                            <div class="col-12" v-if="labelLotacaoAtual">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Lotação</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        :value="labelLotacaoAtual"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-cc-atual-${hash}`">Centro de Custo Atual</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormCcAtual"
                                            instance-id="form-mudacargo-cc-atual"
                                            :input-id="`mudacargo-cc-atual-${hash}`"
                                            v-model="formAnteriorCcCombo"
                                            :options="formAnteriorCcOpcoes"
                                            :disabled="true"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-cc-atual')"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2" v-if="centroCustoTemFilial">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-cnpj-atual-${hash}`">CNPJ Atual</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormCnpjAtual"
                                            instance-id="form-mudacargo-cnpj-atual"
                                            :input-id="`mudacargo-cnpj-atual-${hash}`"
                                            v-model="formAnteriorFilialCombo"
                                            :options="formMatrizFilialOpcoes"
                                            :disabled="true"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-cnpj-atual')"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="temFilial && form.anterior_filial">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-filial-atual-${hash}`">Filial</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormFilialAtual"
                                            instance-id="form-mudacargo-filial-atual"
                                            :input-id="`mudacargo-filial-atual-${hash}`"
                                            v-model="formAnteriorFilialIdCombo"
                                            :options="formAnteriorFilialOpcoes"
                                            :disabled="true"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-filial-atual')"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Cargo Atual</label>
                                    <autocomplete
                                        :disabled="true"
                                        :caminho="caminho_autocomplete_vagas"
                                        :valido="form.autocomplete_label_vaga_anterior !== ''"
                                        v-model="form.autocomplete_label_vaga_anterior"
                                        placeholder="Vaga Atual"
                                        @onblur="resetaCampoNovoCargo"
                                        @onselect="selecionaVaga"
                                    ></autocomplete>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Função Atual</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        onblur="valida_campo_vazio(this, 2)"
                                        v-model="form.anterior_funcao"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Salário Atual R$</label>
                                    <input type="text" class="form-control form-control-sm" v-mascara:dinheiro v-model="form.anterior_salario" disabled />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id" class="mybp-modal-secao">
                        <legend>Centro de custo</legend>
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-mantem-cc-${hash}`">Mantém Centro de Custo</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormMantemCc"
                                            instance-id="form-mudacargo-mantem-cc"
                                            :input-id="`mudacargo-mantem-cc-${hash}`"
                                            v-model="formMantemCcCombo"
                                            :options="formSimNaoOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovando"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-mantem-cc')"
                                            @select="changeMantemCentroDeCusto"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-5" v-if="!form.mantem_centro_custo && lista_ccs && temFilial">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-lotacao-nova-${hash}`">
                                        Lotação (CNPJ) <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormLotacaoNova"
                                            instance-id="form-mudacargo-lotacao-nova"
                                            :input-id="`mudacargo-lotacao-nova-${hash}`"
                                            v-model="formLotacaoCnpjNovo"
                                            :options="formLotacaoNovaOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovando"
                                            placeholder-blur="Selecione a lotação..."
                                            empty-message="Nenhuma lotação encontrada."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-lotacao-nova')"
                                            @select="onSelectFormLotacaoNova"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="!form.mantem_centro_custo"
                                :class="temFilial ? 'col-12 col-md-5' : 'col-12 col-md-6'"
                            >
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-cc-novo-${hash}`">
                                        Novo Centro de Custo <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormCcNovo"
                                            instance-id="form-mudacargo-cc-novo"
                                            :input-id="`mudacargo-cc-novo-${hash}`"
                                            v-model="formCentroCustoNovoCombo"
                                            :options="formCentroCustoNovoOpcoes"
                                            :disabled="
                                                visualizar ||
                                                aprovandoRh ||
                                                aprovando ||
                                                (temFilial && !formLotacaoCnpjNovo)
                                            "
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhum centro de custo para esta lotação."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-cc-novo')"
                                            @select="onSelectFormCentroCustoNovo"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id" class="mybp-modal-secao">
                        <legend>Função</legend>
                        <div class="row">
                            <div class="col-12 col-md-3" v-if="form.anterior_funcao">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-mantem-funcao-${hash}`">Mantém Função</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormMantemFuncao"
                                            instance-id="form-mudacargo-mantem-funcao"
                                            :input-id="`mudacargo-mantem-funcao-${hash}`"
                                            v-model="formMantemFuncaoCombo"
                                            :options="formSimNaoOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-mantem-funcao')"
                                            @select="changeMantemFuncao"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6" v-if="!form.mantem_funcao">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Nova Função</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        onblur="valida_campo_vazio(this, 2)"
                                        v-model="form.nova_funcao"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id" class="mybp-modal-secao">
                        <legend>Treinamento / certificado</legend>
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-treinamento-${hash}`">Treinamento na Função</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormTreinamento"
                                            instance-id="form-mudacargo-treinamento"
                                            :input-id="`mudacargo-treinamento-${hash}`"
                                            v-model="formTreinamentoCombo"
                                            :options="formSimNaoOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovando"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-treinamento')"
                                            @select="changeTreinamentoFuncao"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3" v-if="form.treinamento_funcao">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Data Início</label>
                                    <datepicker
                                        :id="`mudacargo-trein-inicio-${hash}`"
                                        formsm
                                        label=""
                                        class="corrigiDatepicker"
                                        v-model="form.treinamento_data_inicio"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        @onselect="limparCampoDataInvalido('mudacargo-trein-inicio-' + hash)"
                                    ></datepicker>
                                </div>
                            </div>
                            <div class="col-12 col-md-3" v-if="form.treinamento_funcao">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Data Fim</label>
                                    <datepicker
                                        :id="`mudacargo-trein-fim-${hash}`"
                                        formsm
                                        label=""
                                        class="corrigiDatepicker"
                                        v-model="form.treinamento_data_fim"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        @onselect="limparCampoDataInvalido('mudacargo-trein-fim-' + hash)"
                                    ></datepicker>
                                </div>
                            </div>
                        </div>
                        <div class="row" v-if="form.treinamento_funcao">
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Treinamento</h6>
                                    </div>
                                    <div class="card-body">
                                        <upload
                                            :model="form.treinamento_certificado"
                                            :model-delete="form.treinamento_certificadoDel"
                                            :url="url_anexo"
                                            :tipos="mimes"
                                            :leitura="!podeanexar || visualizar || aprovandoRh || aprovando"
                                            label="Selecionar"
                                            @onProgresso="anexoUploadAndamento = true"
                                            @onFinalizado="anexoUploadAndamento = false"
                                        ></upload>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Termo de Ciência</h6>
                                    </div>
                                    <div class="card-body">
                                        <upload
                                            :model="form.treinamento_termo_ciencia"
                                            :model-delete="form.treinamento_termo_cienciaDel"
                                            :url="url_anexo"
                                            :tipos="mimes"
                                            :leitura="!podeanexar || visualizar || aprovandoRh || aprovando"
                                            label="Selecionar"
                                            @onProgresso="anexoUploadAndamento = true"
                                            @onFinalizado="anexoUploadAndamento = false"
                                        ></upload>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id" class="mybp-modal-secao">
                        <legend>Cargo</legend>
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-mantem-cargo-${hash}`">Mantém Cargo</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormMantemCargo"
                                            instance-id="form-mudacargo-mantem-cargo"
                                            :input-id="`mudacargo-mantem-cargo-${hash}`"
                                            v-model="formMantemCargoCombo"
                                            :options="formSimNaoOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-mantem-cargo')"
                                            @select="changeMantemCargo"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6" v-if="!form.mantem_cargo">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Novo Cargo <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :id="`novo_cargo_${hash}`"
                                        :caminho="caminho_autocomplete_vagas"
                                        :valido="!!form.nova_vaga_aberta_id"
                                        v-model="form.autocomplete_label_vaga_nova"
                                        placeholder="Novo Cargo"
                                        @onselect="selecionaVagaNovo"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    ></autocomplete>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id" class="mybp-modal-secao">
                        <legend>Salário</legend>
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-mantem-salario-${hash}`">Mantém Salário</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormMantemSalario"
                                            instance-id="form-mudacargo-mantem-salario"
                                            :input-id="`mudacargo-mantem-salario-${hash}`"
                                            v-model="formMantemSalarioCombo"
                                            :options="formSimNaoOpcoes"
                                            :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-mantem-salario')"
                                            @select="changeMantemSalario"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3" v-if="!form.mantem_salario">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Novo Salário R$</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-mascara:dinheiro
                                        v-model="form.novo_salario"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id" class="mybp-modal-secao">
                        <legend>Gestor responsável</legend>
                        <div class="row">
                            <gestoraprovacao
                                label="Gestor Aprovação"
                                formsm
                                :model="form"
                                :verifica="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                :hash="hash_gestor"
                                :obrigatorio="true"
                            >
                            </gestoraprovacao>
                        </div>
                    </fieldset>
                    <fieldset v-if="form.colaborador_id" class="mybp-modal-secao">
                        <legend>Informações extras</legend>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.obs_solicitante"
                                        cols="5"
                                        rows="5"
                                        :readonly="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    ></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 mt-4 mb-4" v-if="visualizar">
                                <legend>Solicitação feita por: {{ form.solicitante !== null ? form.solicitante.nome : '' }} {{ form.data_solicitacao }}</legend>
                            </div>
                        </div>
                    </fieldset>

                    <div class="alert alert-warning" v-if="!form.data_aprovacao_gestor && !cadastrando">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo gestor!
                    </div>

                    <fieldset v-if="visualizar || aprovando" class="mybp-modal-secao">
                        <legend>Aprovação gestor</legend>
                        <div class="row">
                            <div
                                v-if="!aprovando && form.gestor_aprovacao && (form.gestor_aprovacao.nome || typeof form.gestor_aprovacao === 'string')"
                                class="col-12"
                            >
                                <legend>
                                    {{ form.status_aprovacao_gestor }}
                                    por:
                                    {{ (form.gestor_aprovacao && form.gestor_aprovacao.nome) || form.gestor_aprovacao || '' }} em
                                    {{ form.data_aprovacao_gestor }}
                                </legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :readonly="!aprovando"
                                        v-model="form.obs_gestor_aprovacao"
                                        cols="5"
                                        rows="5"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-status-gestor-${hash}`">
                                        Status <span class="text-danger" v-if="aprovando">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusGestor"
                                            instance-id="form-mudacargo-status-gestor"
                                            :input-id="`mudacargo-status-gestor-${hash}`"
                                            v-model="form.status_aprovacao_gestor"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovando"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-status-gestor')"
                                            @select="limparComboboxInvalido('mudacargo-status-gestor-' + hash)"
                                        />
                                    </div>
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
                            <div v-if="!aprovandoExtra && form.aprovacao_extra_nome" class="col-12">
                                <legend>{{ form.status_aprovacao_extra }} por: {{ form.aprovacao_extra_nome }} em {{ form.data_aprovacao_extra }}</legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :readonly="!aprovandoExtra"
                                        v-model="form.obs_aprovacao_extra"
                                        cols="5"
                                        rows="5"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-status-extra-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoExtra">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusExtra"
                                            instance-id="form-mudacargo-status-extra"
                                            :input-id="`mudacargo-status-extra-${hash}`"
                                            v-model="form.status_aprovacao_extra"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoExtra"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-status-extra')"
                                            @select="limparComboboxInvalido('mudacargo-status-extra-' + hash)"
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
                            <div v-if="!aprovandoRh && form.rh_aprovacao && (form.rh_aprovacao.nome || typeof form.rh_aprovacao === 'string')" class="col-12">
                                <legend>
                                    {{ form.status_aprovacao_rh }} por: {{ (form.rh_aprovacao && form.rh_aprovacao.nome) || form.rh_aprovacao || '' }} em
                                    {{ form.data_aprovacao_rh }}
                                </legend>
                            </div>

                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea class="form-control form-control-sm" :readonly="!aprovandoRh" v-model="form.obs_rh" cols="5" rows="5"></textarea>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`mudacargo-status-rh-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoRh">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusRh"
                                            instance-id="form-mudacargo-status-rh"
                                            :input-id="`mudacargo-status-rh-${hash}`"
                                            v-model="form.status_aprovacao_rh"
                                            :options="formStatusAprovacaoOpcoes"
                                            :disabled="!aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-mudacargo-status-rh')"
                                            @select="limparComboboxInvalido('mudacargo-status-rh-' + hash)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.colaborador_id" class="mybp-modal-secao">
                        <legend>Anexos</legend>
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
                <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovandoRh && !preload" @click.prevent="aprovarRh">
                    <i class="fa fa-save"></i> Salvar
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
                    :id-suffix="'mudacargo-' + hash"
                    label="Período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="mudacargo-filtro-busca">
                            Colaborador / CPF
                            <span v-if="buscaUnificadaEhCpf" class="mudacargo-filtro-hint">CPF</span>
                        </label>
                        <input
                            id="mudacargo-filtro-busca"
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
                        <label class="mybp-label" for="mudacargo-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="mudacargo-status"
                                input-id="mudacargo-filtro-status"
                                v-model="controle.dados.campoStatusAprovacao"
                                :options="opcoesStatus"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhum status encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('mudacargo-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="mudacargo-filtro-cnpj">Lotação (CNPJ)</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="mudacargo-cnpj"
                                input-id="mudacargo-filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="opcoesCnpj"
                                :disabled="controle.carregando"
                                placeholder-blur="Todas as lotações"
                                empty-message="Nenhuma lotação encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('mudacargo-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="lista_ccs" :class="temFilial ? 'col-12 col-md-8' : 'col-12'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="mudacargo-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                instance-id="mudacargo-cc"
                                input-id="mudacargo-filtro-cc"
                                v-model="controle.dados.campoCentroCusto"
                                :options="opcoesCentroCusto"
                                :disabled="controle.carregando || !opcoesCentroCusto.length"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro de custo encontrado."
                                :max-results="200"
                                @opening="fecharOutrosComboboxes('mudacargo-cc')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="mudacargo-filtro-ordenacao">Ordenar por</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroOrdenacao"
                                instance-id="mudacargo-ordenacao"
                                input-id="mudacargo-filtro-ordenacao"
                                v-model="controle.dados.ordenacao"
                                :options="opcoesOrdenacao"
                                :disabled="controle.carregando"
                                placeholder-blur="Mais recentes"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('mudacargo-ordenacao')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="mudacargo-filtro-pages">Por página</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPages"
                                instance-id="mudacargo-pages"
                                input-id="mudacargo-filtro-pages"
                                v-model="campoPagesCombo"
                                :options="opcoesPages"
                                :disabled="controle.carregando"
                                placeholder-blur="20"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('mudacargo-pages')"
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

            <!-- Checkbox Selecionar Todos -->
            <!-- <div class="checkbox-geral-container" v-show="!controle.carregando && lista.length > 0">
                <label class="checkbox-geral-label">
                    <input type="checkbox"
                           class="custom-checkbox mr-2"
                           @change="selecionarTodos"
                           :checked="lista.length > 0 && selecionados.length === lista.length">
                    Selecionar todos
                </label>
            </div> -->

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
                                        title="Aprovação Gestor"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = true; aprovandoExtra = false; aprovandoRh = false; podeanexar = true; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="item.gestor_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor"
                                    >
                                        <i class="fa fa-user-check mr-1"></i> Aprovação Gestor
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra || 'Aprovação Extra'"
                                        @click.prevent="formOpen(item.id); visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="
                                            temAprovacaoExtra &&
                                            aprovaExtra &&
                                            item.status_aprovacao_gestor === 'aprovado' &&
                                            !item.aprovacao_extra_id &&
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
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="
                                            ((item.status_aprovacao_gestor === 'aprovado' && !temAprovacaoExtra) ||
                                                item.status_aprovacao_extra === 'aprovado') &&
                                            !item.aprovado_via_script &&
                                            item.rh_aprovacao_id === null &&
                                            aprovaRh
                                        "
                                    >
                                        <i class="fa fa-users mr-1"></i> Aprovação RH
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
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
                                    icon="fas fa-building"
                                    label="Lotação"
                                    :valor="item.lotacao || 'Não informado'"
                                />
                                <mybp-card-campo
                                    icon="fas fa-sitemap"
                                    label="Centro de custo"
                                    :valor="centroCustoLista(item)"
                                />
                                <mybp-card-campo
                                    icon="fas fa-exchange-alt"
                                    label="Alterações"
                                    :valor="cargoResumoLista(item)"
                                    forte
                                />
                            </div>
                        </section>

                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-user-edit"
                                    label="Solicitante"
                                    :valor="solicitanteLista(item)"
                                />
                                <mybp-card-campo
                                    icon="fas fa-calendar-plus"
                                    label="Datas"
                                    :valor="datasLista(item)"
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
import Upload from '../../Upload'
import colaborador from '../../Colaborador'
import gestoraprovacao from '../../GestorAprovacao'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Utils from '../../../mixins/Utils'
import configuracoes from '../../../mixins/Configuracoes'
import DateRangeFilter from '../../DateRangeFilter'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'
import ComboboxValidation from '../../../mixins/ComboboxValidation'
import {
    etapaAtualFluxoAprovacao,
    normalizarStatusUrlFluxo,
    opcoesStatusFluxoAtual,
    textoStatusFluxo,
    varianteStatusFluxo
} from '../../../utils/opcoesStatusFluxoAprovacao'

export default {
    mixins: [ExportacaoMixin, Utils, configuracoes, ComboboxValidation],
    inject: {
        atualizarUrlMovimentacao: {
            default: () => () => {}
        }
    },
    components: {
        colaborador,
        DateRangeFilter,
        gestoraprovacao,
        Upload,
        FiltroListagem,
        ComboboxAutoComplete,
        MybpCardCampo,
        MybpFluxoAprovacao,
        MybpStatusBadge
    },
    data() {
        return {
            tituloJanela: 'Solicitacao de Mudança de Cargo',
            preload: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovando: false,
            aprovandoExtra: false,
            aprovandoRh: false,
            aprovaGestor: false,
            aprovaExtra: false,
            aprovaRh: false,
            preloadExportacao: false,
            temAprovacaoExtra: false,
            nomeAprovacaoExtra: 'Aprovação Extra',

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/export`,
            url_anexo: `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`,
            anexoUploadAndamento: false,
            podeanexar: false,
            mimes: [],
            caminho_autocomplete_vagas: `autocomplete/todas-vagas-ativas`,

            hash: `mybp_${parseInt(Math.random() * 999999)}`,
            hash_gestor: `${parseInt(Math.random() * 999999)}`,

            colunasTabela: {
                cliente: false
            },

            selecionados: [],
            selecionaTudo: false,

            dropdownAbertoKey: null,

            formConfirmacao: {
                selecionados: [],
                obs_aprovacao: '',
                status_aprovacao: ''
            },

            formConfirmacaoDefault: null,
            form: {
                empresa_id: '',
                admissao_id: '',
                colaborador_id: '',
                autocomplete_label_colaborador: '',

                mantem_centro_custo: true,
                anterior_centro_custo_id: '',
                anterior_centro_custo_filial_id: '',
                anterior_filial: '',
                novo_centro_custo_id: '',
                novo_centro_custo_filial_id: '',
                novo_filial: '',
                tipo_contrato: '',

                mantem_cargo: true,
                anterior_vaga_aberta_id: '',
                autocomplete_label_vaga_anterior: '',
                nova_vaga_aberta_id: '',
                autocomplete_label_vaga_nova: '',

                mantem_funcao: true,
                anterior_funcao: '',
                nova_funcao: '',

                treinamento_funcao: false,
                treinamento_data_inicio: '',
                treinamento_data_fim: '',
                treinamento_termo_ciencia: [],
                treinamento_termo_cienciaDel: [],
                treinamento_certificado: [],
                treinamento_certificadoDel: [],

                mantem_salario: true,
                anterior_salario: '0,00',
                novo_salario: '0,00',

                solicitante_id: '',
                autocomplete_label_solicitante: '',
                obs_solicitante: '',
                data_solicitacao: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',
                gestor_aprovacao_id: '',
                autocomplete_label_gestor_aprovacao: '',
                obs_gestor_aprovacao: '',
                status_aprovacao_gestor: '',
                data_aprovacao_gestor: '',

                aprovacao_extra_id: '',
                aprovacao_extra_nome: '',
                obs_aprovacao_extra: '',
                status_aprovacao_extra: '',
                data_aprovacao_extra: '',

                rh_aprovacao_id: '',
                autocomplete_label_rh: '',
                obs_rh: '',
                status_aprovacao_rh: '',
                data_aprovacao_rh: '',
                aprovado_via_script: false,

                anexos: [],
                anexosDel: []
            },

            formDefault: null,
            lista: [],
            centro_custos: [],
            filiais_centro_custos: [],
            lista_ccs: null,
            formLotacaoCnpjNovo: '',

            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: '',
                    campoCPF: '',
                    campoStatusAprovacao: '',
                    campoCnpj: '',
                    campoCentroCusto: '',
                    filtroPeriodo: false,
                    periodo: '',
                    dataInicio: '',
                    dataFim: '',
                    token: '',
                    ordenacao: 'created_at_desc'
                }
            }
        }
    },
    mounted() {
        this.urlParamGet()
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formConfirmacaoDefault = _.cloneDeep(this.formConfirmacao)
        this.$nextTick(() => this.atualizar())
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
        por_pagina() {
            return [20, 50, 100, 150]
        },
        centroCustoSelecionado() {
            if (this.form.anterior_centro_custo_id === undefined || this.form.anterior_centro_custo_id === null || this.form.anterior_centro_custo_id === '') {
                return []
            }
            let centroSelecionado = _.find(this.centro_custos, { id: this.form.anterior_centro_custo_id })
            if (centroSelecionado && centroSelecionado.filiais && centroSelecionado.filiais.length) {
                return centroSelecionado.filiais
            }
            return []
        },
        centroCustoSelecionadoNovo() {
            if (this.form.novo_centro_custo_id === undefined || this.form.novo_centro_custo_id === null || this.form.novo_centro_custo_id === '') {
                return []
            }
            let centroSelecionado = _.find(this.centro_custos, { id: this.form.novo_centro_custo_id })
            if (centroSelecionado && centroSelecionado.filiais && centroSelecionado.filiais.length) {
                return centroSelecionado.filiais
            }
            return []
        },
        centroCustoTemFilial() {
            return this.temFilial && this.centroCustoSelecionado.length > 0
        },
        centroCustoTemFilialNovo() {
            return this.temFilial && this.centroCustoSelecionadoNovo.length > 0
        },
        labelLotacaoAtual() {
            if ([undefined, null, ''].includes(this.form.anterior_centro_custo_id)) {
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
                this.form.anterior_filial === true ||
                this.form.anterior_filial === 1 ||
                this.form.anterior_filial === '1'
            const filiaisAuth =
                (this.authconfiguracao && this.authconfiguracao.cnpjs && this.authconfiguracao.cnpjs.filiais) || []

            if (ehFilial && this.form.anterior_centro_custo_filial_id) {
                const ccfId = String(this.form.anterior_centro_custo_filial_id)
                const vinculo = _.find(this.centroCustoSelecionado, (v) => String(v.id) === ccfId)
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
            }

            if (this.lista_ccs && this.lista_ccs.cnpjs) {
                const viaLista = this.resolverLabelLotacaoViaListaCcs(
                    this.form.anterior_centro_custo_id,
                    ehFilial ? this.form.anterior_centro_custo_filial_id : null,
                    ehFilial
                )
                if (viaLista) return viaLista
            }

            const matrizAuth = this.authconfiguracao && this.authconfiguracao.cnpjs && this.authconfiguracao.cnpjs.matriz
            if (matrizAuth) {
                const viaMatriz = formatLotacao(matrizAuth.nome_fantasia, matrizAuth.razao_social, matrizAuth.cnpj)
                if (viaMatriz) return viaMatriz
            }

            return ''
        },
        formListaCentroCustoLotacaoNova() {
            if (!this.lista_ccs) return []
            if (this.temFilial) {
                if (!this.formLotacaoCnpjNovo) return []
                return this.lista_ccs.centros_custos[this.formLotacaoCnpjNovo] || []
            }
            const keys = Object.keys(this.lista_ccs.centros_custos || {})
            return keys.length ? this.lista_ccs.centros_custos[keys[0]] || [] : []
        },
        formLotacaoNovaOpcoes() {
            const opts = [{ value: '', label: 'Selecione a lotação...' }]
            if (!this.lista_ccs || !this.lista_ccs.cnpjs) return opts
            Object.keys(this.lista_ccs.cnpjs).forEach((key) => {
                const item = this.lista_ccs.cnpjs[key]
                opts.push({
                    value: key,
                    label: `${item.nome_fantasia} - ${item.cnpj}`,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        formCentroCustoNovoOpcoes() {
            const opts = [{ value: '', label: 'Selecione...' }]
            ;(this.formListaCentroCustoLotacaoNova || []).forEach((item) => {
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
        formCentroCustoNovoCombo: {
            get() {
                if (
                    this.form.novo_filial === true ||
                    this.form.novo_filial === 1 ||
                    this.form.novo_filial === '1'
                ) {
                    return this.form.novo_centro_custo_filial_id != null &&
                        this.form.novo_centro_custo_filial_id !== ''
                        ? String(this.form.novo_centro_custo_filial_id)
                        : ''
                }
                return this.form.novo_centro_custo_id != null && this.form.novo_centro_custo_id !== ''
                    ? String(this.form.novo_centro_custo_id)
                    : ''
            },
            set(valor) {
                if (valor == null || valor === '') {
                    this.limparFormCentroCustoNovo()
                    return
                }
                this.aplicarCentroCustoNovoPorValorCombo(String(valor))
            }
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
        formMatrizFilialOpcoes() {
            return [
                { value: '0', label: 'Matriz' },
                { value: '1', label: 'Filial' }
            ]
        },
        formAnteriorCcOpcoes() {
            const opts = [{ value: '', label: 'Selecione...' }]
            ;(this.centro_custos || []).forEach((item) => {
                if (item == null || item.id == null || item.id === '') return
                opts.push({ value: String(item.id), label: item.label || String(item.id) })
            })
            return opts
        },
        formAnteriorFilialOpcoes() {
            const opts = [{ value: '', label: 'Selecione...' }]
            ;(this.centroCustoSelecionado || []).forEach((item) => {
                if (item == null || item.id == null || item.id === '') return
                const nome = item.filial?.razao_social || item.filial?.nome_fantasia || item.label || String(item.id)
                opts.push({ value: String(item.id), label: nome })
            })
            return opts
        },
        formAnteriorCcCombo: {
            get() {
                return this.form.anterior_centro_custo_id != null && this.form.anterior_centro_custo_id !== ''
                    ? String(this.form.anterior_centro_custo_id)
                    : ''
            },
            set(valor) {
                this.form.anterior_centro_custo_id = valor == null || valor === '' ? '' : String(valor)
            }
        },
        formAnteriorFilialCombo: {
            get() {
                return this.form.anterior_filial === true ||
                    this.form.anterior_filial === 1 ||
                    this.form.anterior_filial === '1'
                    ? '1'
                    : '0'
            },
            set(valor) {
                this.form.anterior_filial = valor === true || valor === 1 || valor === '1'
            }
        },
        formAnteriorFilialIdCombo: {
            get() {
                return this.form.anterior_centro_custo_filial_id != null &&
                    this.form.anterior_centro_custo_filial_id !== ''
                    ? String(this.form.anterior_centro_custo_filial_id)
                    : ''
            },
            set(valor) {
                this.form.anterior_centro_custo_filial_id = valor == null || valor === '' ? '' : String(valor)
            }
        },
        formMantemCcCombo: {
            get() {
                return this.boolToCombo(this.form.mantem_centro_custo)
            },
            set(valor) {
                this.form.mantem_centro_custo = this.comboToBool(valor)
            }
        },
        formMantemFuncaoCombo: {
            get() {
                return this.boolToCombo(this.form.mantem_funcao)
            },
            set(valor) {
                this.form.mantem_funcao = this.comboToBool(valor)
            }
        },
        formTreinamentoCombo: {
            get() {
                return this.boolToCombo(this.form.treinamento_funcao)
            },
            set(valor) {
                this.form.treinamento_funcao = this.comboToBool(valor)
            }
        },
        formMantemCargoCombo: {
            get() {
                return this.boolToCombo(this.form.mantem_cargo)
            },
            set(valor) {
                this.form.mantem_cargo = this.comboToBool(valor)
            }
        },
        formMantemSalarioCombo: {
            get() {
                return this.boolToCombo(this.form.mantem_salario)
            },
            set(valor) {
                this.form.mantem_salario = this.comboToBool(valor)
            }
        },
        paramsExport() {
            return this.controle.dados
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            let total = [d.campoBusca, d.campoCPF, d.campoStatusAprovacao, d.campoCentroCusto].filter(
                (v) => v !== '' && v !== null && v !== undefined
            ).length
            if (this.temFilial && d.campoCnpj) total++
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) total++
            if (d.ordenacao && d.ordenacao !== 'created_at_desc') total++
            if (Number(d.pages) !== 20) total++
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
        opcoesStatus() {
            return opcoesStatusFluxoAtual({
                temAprovacaoExtra: this.temAprovacaoExtra,
                nomeAprovacaoExtra: this.nomeAprovacaoExtra
            })
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
                return String(this.controle.dados.pages || 20)
            },
            set(valor) {
                this.controle.dados.pages = parseInt(valor, 10) || 20
            }
        }
    },
    methods: {
        toggleDropdown(itemId) {
            if (!itemId) {
                return
            }
            const key = `mov_mudacargo:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        tituloCardLista(item) {
            return item?.colaborador?.nome || 'Colaborador não informado'
        },
        centroCustoLista(item) {
            if (!item) return 'Não informado'
            if (item.mantem_centro_custo) {
                return 'Mantém centro de custo atual'
            }
            return 'Alteração de centro de custo'
        },
        cargoResumoLista(item) {
            if (!item) return 'Não informado'
            const partes = []
            if (!item.mantem_centro_custo) partes.push('CC')
            if (!item.mantem_cargo) partes.push('Cargo')
            if (!item.mantem_funcao) partes.push('Função')
            if (!item.mantem_salario) partes.push('Salário')
            if (!partes.length) return 'Sem alteração registrada'
            return partes.join(' · ')
        },
        solicitanteLista(item) {
            return item?.solicitante?.nome || 'Não informado'
        },
        datasLista(item) {
            if (!item?.created_at) return 'Não informado'
            if (item.updated_at && item.updated_at !== item.created_at) {
                return `${item.created_at} · atualizado ${item.updated_at}`
            }
            return item.created_at
        },
        etapaAtualLista(item) {
            return etapaAtualFluxoAprovacao(item, {
                campoGestor: 'status_aprovacao_gestor',
                temAprovacaoExtra: this.temAprovacaoExtra
            })
        },
        chaveStatusLista(item) {
            return varianteStatusFluxo(this.etapaAtualLista(item))
        },
        classeBordaStatusLista(item) {
            return `mybp-card-corpo--${this.chaveStatusLista(item)}`
        },
        textoStatusLista(item) {
            return textoStatusFluxo(this.etapaAtualLista(item), this.nomeAprovacaoExtra)
        },
        fluxoStepsLista(item) {
            if (!item) return []
            const steps = [
                {
                    key: 'solicitante',
                    label: 'Solicitante',
                    status: 'aprovado',
                    nome: item.solicitante?.nome,
                    data: item.created_at
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
                    data: item.data_aprovacao_extra,
                    statusTexto: statusExtra === 'cancelado' ? 'Cancelada' : undefined
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
                data: item.data_aprovacao_rh,
                statusTexto: statusRh === 'cancelado' ? 'Cancelada' : undefined
            })

            return steps
        },
        sovizualiza(soVisualizar) {
            if (soVisualizar) {
                setTimeout(() => {
                    $(`#${this.hash} .mybp-modal-form :input`).attr('disabled', 'true')
                }, 100)
            }
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `mov_mudacargo:${itemId}`
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
            if (urlParams.get('pages')) this.controle.dados.pages = parseInt(urlParams.get('pages'), 10) || 20
            if (urlParams.get('ordenacao')) this.controle.dados.ordenacao = urlParams.get('ordenacao')
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoCPF')) this.controle.dados.campoCPF = urlParams.get('campoCPF')
            this.controle.dados.campoStatusAprovacao = normalizarStatusUrlFluxo(urlParams.get('campoStatusAprovacao'))
            if (urlParams.get('campoCentroCusto')) this.controle.dados.campoCentroCusto = urlParams.get('campoCentroCusto')
            if (urlParams.get('dataInicio')) this.controle.dados.dataInicio = urlParams.get('dataInicio')
            if (urlParams.get('dataFim')) this.controle.dados.dataFim = urlParams.get('dataFim')
            if (urlParams.get('dataInicio') || urlParams.get('dataFim')) this.controle.dados.filtroPeriodo = true
        },
        syncUrlFiltros() {
            if (typeof this.atualizarUrlMovimentacao !== 'function') return
            const d = this.controle.dados
            const params = { pages: d.pages || 20, ordenacao: d.ordenacao || 'created_at_desc' }
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoCPF) params.campoCPF = d.campoCPF
            if (d.campoStatusAprovacao) params.campoStatusAprovacao = d.campoStatusAprovacao
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            if (d.token) params.token = d.token
            this.atualizarUrlMovimentacao(params)
        },
        changeCentroCusto() {
            this.form.novo_filial = false
            this.form.novo_centro_custo_filial_id = ''
        },
        boolToCombo(valor) {
            return valor === true || valor === 1 || valor === '1' ? '1' : '0'
        },
        comboToBool(valor) {
            return valor === true || valor === 1 || valor === '1'
        },
        limparFormCentroCustoNovo() {
            this.form.novo_centro_custo_id = ''
            this.form.novo_filial = false
            this.form.novo_centro_custo_filial_id = ''
        },
        onSelectFormLotacaoNova() {
            this.limparFormCentroCustoNovo()
            this.limparComboboxInvalido(`mudacargo-lotacao-nova-${this.hash}`)
        },
        aplicarCentroCustoNovoPorValorCombo(valor) {
            const lista = this.formListaCentroCustoLotacaoNova || []
            const item = _.find(lista, (cc) => {
                const chave = cc.matriz ? String(cc.id) : String(cc.filial_id)
                return chave === String(valor)
            })
            if (!item) {
                this.limparFormCentroCustoNovo()
                return
            }
            this.form.novo_centro_custo_id = String(item.id)
            if (item.matriz) {
                this.form.novo_filial = false
                this.form.novo_centro_custo_filial_id = ''
            } else {
                this.form.novo_filial = true
                this.form.novo_centro_custo_filial_id = String(item.filial_id)
            }
        },
        onSelectFormCentroCustoNovo() {
            if (this.formCentroCustoNovoCombo) {
                this.aplicarCentroCustoNovoPorValorCombo(this.formCentroCustoNovoCombo)
            } else {
                this.limparFormCentroCustoNovo()
            }
            this.limparComboboxInvalido(`mudacargo-cc-novo-${this.hash}`)
        },
        resolverLabelLotacaoViaListaCcs(centroCustoId, centroCustoFilialId, ehFilial) {
            if (!this.lista_ccs || !this.lista_ccs.centros_custos || !this.lista_ccs.cnpjs) return ''
            const ccId = centroCustoId != null ? String(centroCustoId) : ''
            const ccfId = centroCustoFilialId != null ? String(centroCustoFilialId) : ''
            let cnpjKey = ''
            Object.keys(this.lista_ccs.centros_custos).some((key) => {
                const lista = this.lista_ccs.centros_custos[key] || []
                const encontrado = _.find(lista, (item) => {
                    if (ehFilial) return String(item.filial_id) === ccfId
                    return item.matriz && String(item.id) === ccId
                })
                if (encontrado) {
                    cnpjKey = key
                    return true
                }
                return false
            })
            if (!cnpjKey || !this.lista_ccs.cnpjs[cnpjKey]) return ''
            const info = this.lista_ccs.cnpjs[cnpjKey]
            const nome = String(info.nome_fantasia || info.razao_social || '').trim()
            const doc = String(info.cnpj || '').trim()
            if (!nome && !doc) return ''
            if (!nome) return doc
            if (!doc) return nome
            return `${nome} - ${doc}`
        },
        resolverLotacaoNovaDoForm() {
            this.formLotacaoCnpjNovo = ''
            if (this.form.mantem_centro_custo || !this.lista_ccs || !this.lista_ccs.centros_custos) {
                if (!this.temFilial && !this.form.mantem_centro_custo && this.lista_ccs?.centros_custos) {
                    const keys = Object.keys(this.lista_ccs.centros_custos || {})
                    this.formLotacaoCnpjNovo = keys[0] || ''
                }
                return
            }

            const ehFilial =
                this.form.novo_filial === true ||
                this.form.novo_filial === 1 ||
                this.form.novo_filial === '1'
            const ccId = this.form.novo_centro_custo_id != null ? String(this.form.novo_centro_custo_id) : ''
            const ccfId =
                this.form.novo_centro_custo_filial_id != null
                    ? String(this.form.novo_centro_custo_filial_id)
                    : ''

            if (!this.temFilial) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                this.formLotacaoCnpjNovo = keys[0] || ''
                return
            }

            Object.keys(this.lista_ccs.centros_custos).some((cnpjKey) => {
                const lista = this.lista_ccs.centros_custos[cnpjKey] || []
                const encontrado = _.find(lista, (item) => {
                    if (ehFilial) return String(item.filial_id) === ccfId
                    return item.matriz && String(item.id) === ccId
                })
                if (encontrado) {
                    this.formLotacaoCnpjNovo = cnpjKey
                    return true
                }
                return false
            })
        },
        changeMantemCentroDeCusto() {
            this.limparFormCentroCustoNovo()
            this.formLotacaoCnpjNovo = ''
            if (!this.form.mantem_centro_custo && !this.temFilial && this.lista_ccs?.centros_custos) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                this.formLotacaoCnpjNovo = keys[0] || ''
            }
        },
        changeMantemFuncao() {
            this.form.nova_funcao = ''
        },
        changeTreinamentoFuncao() {
            if (!this.form.treinamento_funcao) {
                this.form.treinamento_data_inicio = ''
                this.form.treinamento_data_fim = ''
                this.form.treinamento_termo_ciencia = []
                this.form.treinamento_termo_cienciaDel = []
                this.form.treinamento_certificado = []
                this.form.treinamento_certificadoDel = []
            }
        },
        changeMantemCargo() {
            this.form.autocomplete_label_vaga_nova = ''
            this.form.nova_vaga_aberta_id = ''
        },
        changeMantemSalario() {
            this.form.novo_salario = ''
        },
        changeCnpj() {
            this.form.novo_centro_custo_filial_id = ''
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
        selecionaVaga(obj) {
            this.form.anterior_vaga_aberta_id = obj.id
            this.form.autocomplete_label_vaga_anterior = obj.vaga.nome
        },
        selecionaVagaNovo(obj) {
            this.form.nova_vaga_aberta_id = obj && obj.id != null ? obj.id : ''
            const nomeVaga =
                (obj && obj.vaga && obj.vaga.nome) ||
                (obj && obj.Vaga && obj.Vaga.nome) ||
                (obj && obj.label) ||
                ''
            this.form.autocomplete_label_vaga_nova = nomeVaga
        },
        selecionaColaborador(obj) {
            this.form.colaborador_id = obj.curriculo_id
            this.form.autocomplete_label_colaborador = obj.label
            this.form.autocomplete_label_colaborador_anterior = obj.label

            this.form.anterior_centro_custo_id = obj.admissao.centro_custo_id
            this.form.anterior_filial = obj.admissao.filial
            this.form.anterior_centro_custo_filial_id = this.form.anterior_filial ? obj.admissao.centro_custo_filial_id : null
            this.form.admissao_id = obj.admissao.id
            this.form.anterior_funcao = obj.admissao.funcao
            this.form.anterior_vaga_aberta_id = obj.vaga_aberta.id
            this.form.autocomplete_label_vaga_anterior = obj.vaga_aberta.vaga.nome
            this.form.anterior_salario = obj.admissao.salario
        },
        resetaCampoColaborador() {
            if (this.form.autocomplete_label_colaborador_anterior !== this.form.autocomplete_label_colaborador) {
                this.form.autocomplete_label_colaborador_anterior = ''
                this.form.autocomplete_label_colaborador = ''
                this.form.colaborador_id = ''

                this.form.anterior_centro_custo_id = ''
                this.form.anterior_filial = ''
                this.form.anterior_centro_custo_filial_id = ''
                this.form.admissao_id = ''
                this.form.anterior_funcao = ''
                this.form.anterior_vaga_aberta_id = ''
                this.form.autocomplete_label_vaga_anterior = ''
                this.form.anterior_salario = ''

                setTimeout(() => {
                    if (this.form.colaborador_id === '') {
                        valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                        $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Colaborador não pode ficar vazio')
                    }
                }, 100)
            }
        },
        async selecionaFilialCentroDeCusto(centro_custo_id, empresa_id) {
            try {
                const { data } = await axios.post(`${URL_ADMIN}/get-filiais/`, {
                    centro_custo_id,
                    empresa_id
                })
                this.filiais_centro_de_custo = data.filiais_centro_de_custo
            } catch {
                this.preload = false
            }
        },
        selecionaNovoCargo(obj) {
            this.form.novo_cargo_id = obj.id
            this.form.autocomplete_label_novo_cargo = obj.label
            this.form.autocomplete_label_novo_cargo_anterior = obj.label

            setTimeout(() => {
                if (this.form.novo_cargo_id !== '' && this.form.novo_cargo_id === this.form.cargo_anterior_id) {
                    valida_campo_vazio($(`#cargo_anterior_${this.hash}`), 1)
                    $(`#${this.hash} #cargo_anterior_${this.hash}`).focus().trigger('blur')
                    mostraErro('Erro', 'O NOVO CARGO não pode ser igual ao CARGO ANTERIOR')
                    this.form.novo_cargo_id = ''
                    this.form.autocomplete_label_novo_cargo = ''
                    this.form.autocomplete_label_novo_cargo_anterior = ''
                }
            }, 100)
        },
        resetaCampoNovoCargo() {
            if (this.form.autocomplete_label_novo_cargo !== this.form.autocomplete_label_novo_cargo) {
                this.form.autocomplete_label_novo_cargo = ''
                this.form.autocomplete_label_novo_cargo = ''
                this.form.novo_cargo_id = ''

                setTimeout(() => {
                    if (this.form.novo_cargo_id === '') {
                        valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1)
                        $(`#${this.hash} #novo_cargo_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Novo Cargo não pode ficar vazio')
                    }
                }, 100)
            }
        },
        async listaCentroCusto() {
            try {
                const { data } = await axios.post(`${URL_PUBLICO}/centro-custos/`)
                this.centro_custos = data.centro_custos
            } catch {
                this.preload = false
            }
        },
        formNovo() {
            this.cadastrado = false
            this.cadastrando = true
            this.atualizado = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.visualizar = false
            this.podeanexar = true

            this.tituloJanela = 'Solicitação de Mudança de Cargo'
            this.form = _.cloneDeep(this.formDefault) //copia
            this.formLotacaoCnpjNovo = ''
            formReset()
            setupCampo()
            this.listaCentroCusto()
        },

        validarTreinamento() {
            if (!this.form.treinamento_funcao) {
                return true
            }

            const inicioId = `mudacargo-trein-inicio-${this.hash}`
            const fimId = `mudacargo-trein-fim-${this.hash}`

            if (
                !this.exigirCampoData(this.form.treinamento_data_inicio, inicioId, {
                    toastMsg: 'A data de início do treinamento é obrigatória'
                })
            ) {
                return false
            }

            if (
                !this.exigirCampoData(this.form.treinamento_data_fim, fimId, {
                    toastMsg: 'A data de fim do treinamento é obrigatória'
                })
            ) {
                return false
            }

            // Validar que data início não é maior que data fim
            if (this.form.treinamento_data_inicio && this.form.treinamento_data_fim) {
                const dataInicioParts = this.form.treinamento_data_inicio.split('/')
                const dataFimParts = this.form.treinamento_data_fim.split('/')

                if (dataInicioParts.length === 3 && dataFimParts.length === 3) {
                    const dataInicio = new Date(parseInt(dataInicioParts[2]), parseInt(dataInicioParts[1]) - 1, parseInt(dataInicioParts[0]))
                    const dataFim = new Date(parseInt(dataFimParts[2]), parseInt(dataFimParts[1]) - 1, parseInt(dataFimParts[0]))

                    if (dataInicio > dataFim) {
                        mostraErro('', 'A data de início do treinamento não pode ser maior que a data de fim do treinamento')
                        return false
                    }
                }
            }

            if (!this.form.treinamento_termo_ciencia || this.form.treinamento_termo_ciencia.length === 0) {
                mostraErro('', 'O Termo de Ciência de Treinamento é obrigatório quando há treinamento na função')
                return false
            }

            if (!this.form.treinamento_certificado || this.form.treinamento_certificado.length === 0) {
                mostraErro('', 'O Certificado de Treinamento é obrigatório quando há treinamento na função')
                return false
            }

            return true
        },
        async cadastrar() {
            if (!this.validarCamposSolicitacao()) {
                return false
            }

            if (this.form.mantem_centro_custo && this.form.mantem_cargo && this.form.mantem_salario && this.form.mantem_funcao) {
                mostraErro('', 'Nenhuma mudança foi solicitada')
                return false
            }

            if (!this.validarTreinamento()) {
                return false
            }

            this.preload = true
            try {
                await axios.post(`${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo`, this.form)
                mostraSucesso('', 'Solicitação registrada com sucesso!')
                this.encerrarModalERecarregar()
            } catch {
                // Erro tratado pelo interceptor ou feedback visual
            } finally {
                this.preload = false
            }
        },

        validarCamposSolicitacao() {
            const bloqueados = this.visualizar || this.aprovando || this.aprovandoExtra || this.aprovandoRh
            if (bloqueados) {
                return true
            }

            if (!this.form.colaborador_id) {
                valida_campo_vazio($(`#colaborador_${this.hash}`), 1)
                $(`#${this.hash} #colaborador_${this.hash}`).focus().trigger('blur')
                this.resetaCampoColaborador()
                return false
            }

            if (!this.form.gestor_id) {
                valida_campo_vazio($(`#gestor_${this.hash_gestor}`), 1)
                $(`#gestor_${this.hash_gestor}`).focus().trigger('blur')
                mostraErro('', 'Campo GESTOR não pode ficar vazio')
                return false
            }

            if (!this.form.mantem_centro_custo) {
                if (this.temFilial) {
                    if (
                        !this.exigirCombobox(this.formLotacaoCnpjNovo, `mudacargo-lotacao-nova-${this.hash}`, {
                            toastMsg: 'Selecione a lotação'
                        })
                    ) {
                        return false
                    }
                }
                if (
                    !this.exigirCombobox(
                        this.form.novo_centro_custo_id || this.formCentroCustoNovoCombo,
                        `mudacargo-cc-novo-${this.hash}`,
                        { toastMsg: 'Selecione o novo centro de custo' }
                    )
                ) {
                    return false
                }
                if (this.form.novo_filial && !this.form.novo_centro_custo_filial_id) {
                    mostraErro('', 'Centro de custo de filial inválido')
                    return false
                }
            }

            if (!this.form.mantem_cargo && !this.form.nova_vaga_aberta_id) {
                valida_campo_vazio($(`#novo_cargo_${this.hash}`), 1)
                mostraErro('', 'Campo NOVO CARGO não pode ficar vazio')
                return false
            }

            return this.validarInputsAtivosVisiveis(this.hash)
        },

        async formOpen(id) {
            Object.assign(this.form, this.formDefault)
            this.form.id = id
            this.cadastrando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false

            this.tituloJanela = `#${id}`

            formReset()
            this.preload = true
            try {
                const { data } = await axios.get(
                    `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/${id}/editar`
                )
                this.form.centro_custo_id = data.centro_custo_id
                this.form.colaborador_id = data.colaborador_id
                Object.assign(this.form, data)
                await this.listaCentroCusto()

                this.form.mantem_centro_custo = !!(
                    data.mantem_centro_custo === true ||
                    data.mantem_centro_custo === 1 ||
                    data.mantem_centro_custo === '1'
                )
                this.form.novo_filial = !!(
                    data.novo_filial === true || data.novo_filial === 1 || data.novo_filial === '1'
                )
                if (data.novo_centro_custo_id != null && data.novo_centro_custo_id !== '') {
                    this.form.novo_centro_custo_id = String(data.novo_centro_custo_id)
                } else {
                    this.form.novo_centro_custo_id = ''
                }
                if (
                    this.form.novo_filial &&
                    data.novo_centro_custo_filial_id != null &&
                    data.novo_centro_custo_filial_id !== ''
                ) {
                    this.form.novo_centro_custo_filial_id = String(data.novo_centro_custo_filial_id)
                } else {
                    this.form.novo_centro_custo_filial_id = ''
                }
                this.resolverLotacaoNovaDoForm()

                this.tituloJanela = `#${id} Solicitação de Mudança de Cargo`
                this.form.status_aprovacao_gestor = data.status_aprovacao_gestor ?? ''
                this.form.status_aprovacao_extra = data.status_aprovacao_extra ?? ''
                this.form.status_aprovacao_rh = data.status_aprovacao_rh ?? ''
                this.form.obs_gestor_aprovacao = data.obs_gestor_aprovacao
                this.form.obs_aprovacao_extra = data.obs_aprovacao_extra
                this.form.obs_rh = data.obs_rh
                this.form.aprovacao_extra_nome = data.aprovacao_extra?.nome ?? ''
                this.form.data_aprovacao_extra = data.data_aprovacao_extra ?? ''
                this.sovizualiza(
                    this.visualizar && !this.aprovando && !this.aprovandoExtra && !this.aprovandoRh
                )
            } catch {
                // Erro tratado pelo interceptor ou feedback visual
            } finally {
                this.preload = false
            }
        },
        validarFormularioAprovacao() {
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }
            return true
        },
        encerrarModalERecarregar() {
            this.$refs[this.hash]?.fecharModal?.()
            this.$refs?.componente?.buscar?.()
        },
        async aprovarGestor() {
            const statusId = `mudacargo-status-gestor-${this.hash}`
            if (
                !this.exigirCombobox(this.form.status_aprovacao_gestor, statusId, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            if (!this.validarFormularioAprovacao() || !this.validarTreinamento()) return

            this.preload = true
            try {
                await axios.put(
                    `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/${this.form.id}/aprovargestor`,
                    this.form
                )
                mostraSucesso('', 'Registro salvo com sucesso!')
                this.encerrarModalERecarregar()
            } catch {
                // Erro tratado pelo interceptor ou feedback visual
            } finally {
                this.preload = false
            }
        },
        async aprovarExtra() {
            const statusId = `mudacargo-status-extra-${this.hash}`
            if (
                !this.exigirCombobox(this.form.status_aprovacao_extra, statusId, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            if (!this.validarFormularioAprovacao() || !this.validarTreinamento()) return

            this.preload = true
            try {
                const { data } = await axios.put(
                    `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/${this.form.id}/aprovarextra`,
                    this.form
                )
                mostraSucesso('', data?.msg ?? 'Aprovação extra registrada com sucesso!')
                this.encerrarModalERecarregar()
            } catch (error) {
                const msg =
                    error.response?.data?.msg ?? 'Houve um erro. Tente novamente.'
                mostraErro('', msg)
            } finally {
                this.preload = false
            }
        },
        async aprovarRh() {
            const statusId = `mudacargo-status-rh-${this.hash}`
            if (
                !this.exigirCombobox(this.form.status_aprovacao_rh, statusId, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return
            }
            if (!this.validarFormularioAprovacao()) return

            this.preload = true
            try {
                await axios.put(
                    `${URL_ADMIN}/planejamento/movimentacao/mudanca-cargo/${this.form.id}/aprovarrh`,
                    this.form
                )
                mostraSucesso('', 'Registro salvo com sucesso!')
                this.encerrarModalERecarregar()
            } catch {
                // Erro tratado pelo interceptor ou feedback visual
            } finally {
                this.preload = false
            }
        },
        selecionarTodos(event) {
            if (event.target.checked) {
                this.selecionados = this.lista.map((item) => item.id)
            } else {
                this.selecionados = []
            }
        },
        carregou(dados) {
            this.lista = dados.itens
            this.mimes = dados.mimes
            this.aprovaGestor = dados.aprovar_por_gestor
            this.aprovaExtra = dados.pode_aprovar_extra || false
            this.aprovaRh = dados.aprovar_por_rh
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || 'Aprovação Extra'
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
            d.filtroPeriodo = false
            d.dataInicio = ''
            d.dataFim = ''
            d.ordenacao = 'created_at_desc'
            d.pages = 20
            d.token = ''
            d.campoCnpj = ''
            this.atualizar()
        },
        limparTokenFiltroListagem() {
            this.controle.dados.token = ''
            this.syncUrlFiltros()
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
                'mudacargo-status': 'comboFiltroStatus',
                'mudacargo-cnpj': 'comboFiltroCnpj',
                'mudacargo-cc': 'comboFiltroCc',
                'mudacargo-ordenacao': 'comboFiltroOrdenacao',
                'mudacargo-pages': 'comboFiltroPages',
                'form-mudacargo-lotacao-nova': 'comboFormLotacaoNova',
                'form-mudacargo-cc-novo': 'comboFormCcNovo',
                'form-mudacargo-cc-atual': 'comboFormCcAtual',
                'form-mudacargo-cnpj-atual': 'comboFormCnpjAtual',
                'form-mudacargo-filial-atual': 'comboFormFilialAtual',
                'form-mudacargo-mantem-cc': 'comboFormMantemCc',
                'form-mudacargo-mantem-funcao': 'comboFormMantemFuncao',
                'form-mudacargo-treinamento': 'comboFormTreinamento',
                'form-mudacargo-mantem-cargo': 'comboFormMantemCargo',
                'form-mudacargo-mantem-salario': 'comboFormMantemSalario',
                'form-mudacargo-status-gestor': 'comboFormStatusGestor',
                'form-mudacargo-status-extra': 'comboFormStatusExtra',
                'form-mudacargo-status-rh': 'comboFormStatusRh'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
        }
    }
}
</script>

<style scoped>
.mudacargo-filtro-hint {
    margin-left: 0.35rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #0d6efd;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
</style>
