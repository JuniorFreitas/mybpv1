<template>
    <div class="container-fluid requisicao-vaga-page">
        <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90" ref="modal_janelaCadastrar">
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
                    id="form"
                    class="mybp-modal-form mybp-filtros-compactos"
                    onsubmit="return false"
                >
                    <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>
                    <fieldset class="mybp-modal-secao">
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Selecione um cargo <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :formsm="true"
                                        :caminho="controle.dados.caminho_autocomplete"
                                        :disabled="modalCamposBloqueados"
                                        :valido="form.cargo_id !== ''"
                                        v-model="form.autocomplete_label_cargo_modal"
                                        placeholder="Digite o nome do cargo"
                                        :id="`vaga_modal_${hash}`"
                                        @onblur="resetaCampoVagaModal"
                                        @onselect="selecionaVagaModal"
                                    ></autocomplete>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-area-${hash}`">Área <span class="text-danger">*</span></label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormArea"
                                            instance-id="form-req-vaga-area"
                                            :input-id="`req-vaga-area-${hash}`"
                                            v-model="form.area_id"
                                            :options="opcoesArea"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione"
                                            empty-message="Nenhuma área encontrada."
                                            :max-results="100"
                                            @opening="fecharOutrosComboboxes('form-req-vaga-area')"
                                            @select="limparComboboxInvalido(`req-vaga-area-${hash}`)"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-tipo-${hash}`">Tipo de Contratação <span class="text-danger">*</span></label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormTipo"
                                            instance-id="form-req-vaga-tipo"
                                            :input-id="`req-vaga-tipo-${hash}`"
                                            v-model="form.tipo_contratacao"
                                            :options="opcoesTipoContratacao"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione"
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="20"
                                            @opening="fecharOutrosComboboxes('form-req-vaga-tipo')"
                                            @select="limparComboboxInvalido(`req-vaga-tipo-${hash}`)"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-prioridade-${hash}`">Prioridade <span class="text-danger">*</span></label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormPrioridade"
                                            instance-id="form-req-vaga-prioridade"
                                            :input-id="`req-vaga-prioridade-${hash}`"
                                            v-model="form.prioridade"
                                            :options="opcoesPrioridade"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione"
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-req-vaga-prioridade')"
                                            @select="limparComboboxInvalido(`req-vaga-prioridade-${hash}`)"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-quantidade-${hash}`">Quantidade <span class="text-danger">*</span></label>
                                    <input
                                        :id="`req-vaga-quantidade-${hash}`"
                                        type="text"
                                        class="form-control form-control-sm"
                                        onblur="valida_campo_vazio(this, 1)"
                                        :disabled="modalCamposBloqueados"
                                        v-mascara:numero
                                        v-model="form.quantidade"
                                    />
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Lotação</legend>
                        <div class="row">
                            <div class="col-12 col-md-6" v-if="lista_ccs && temFilial">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-lotacao-${hash}`">
                                        Lotação (CNPJ) <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormLotacao"
                                            instance-id="form-req-vaga-lotacao"
                                            :input-id="`req-vaga-lotacao-${hash}`"
                                            v-model="formLotacaoCnpj"
                                            :options="formLotacaoOpcoes"
                                            :disabled="modalCamposBloqueados"
                                            placeholder-blur="Selecione a lotação..."
                                            empty-message="Nenhuma lotação encontrada."
                                            :max-results="50"
                                            @opening="fecharOutrosComboboxes('form-req-vaga-lotacao')"
                                            @select="onSelectFormLotacao"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div :class="temFilial ? 'col-12 col-md-6' : 'col-12'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-cc-${hash}`">
                                        Centro de Custo <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormCc"
                                            instance-id="form-req-vaga-cc"
                                            :input-id="`req-vaga-cc-${hash}`"
                                            v-model="formCentroCustoCombo"
                                            :options="formCentroCustoOpcoes"
                                            :disabled="modalCamposBloqueados || (temFilial && !formLotacaoCnpj)"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhum centro de custo para esta lotação."
                                            :max-results="200"
                                            @opening="fecharOutrosComboboxes('form-req-vaga-cc')"
                                            @select="onSelectFormCentroCusto"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Demais informações</legend>
                        <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label" :for="`req-vaga-posicao-${hash}`">Posição <span class="text-danger">*</span></label>
                                                <div class="mybp-combobox-wrap">
                                                    <combobox-auto-complete
                                                        ref="comboFormPosicao"
                                                        instance-id="form-req-vaga-posicao"
                                                        :input-id="`req-vaga-posicao-${hash}`"
                                                        v-model="form.outras_informacoes.posicao"
                                                        :options="opcoesPosicao"
                                                        :disabled="modalCamposBloqueados"
                                                        placeholder-blur="Selecione"
                                                        empty-message="Nenhuma opção encontrada."
                                                        :max-results="20"
                                                        @opening="fecharOutrosComboboxes('form-req-vaga-posicao')"
                                                        @select="limparComboboxInvalido(`req-vaga-posicao-${hash}`)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label" :for="`req-vaga-processo-${hash}`">Processo <span class="text-danger">*</span></label>
                                                <div class="mybp-combobox-wrap">
                                                    <combobox-auto-complete
                                                        ref="comboFormProcesso"
                                                        instance-id="form-req-vaga-processo"
                                                        :input-id="`req-vaga-processo-${hash}`"
                                                        v-model="form.outras_informacoes.processo"
                                                        :options="opcoesProcesso"
                                                        :disabled="modalCamposBloqueados"
                                                        placeholder-blur="Selecione"
                                                        empty-message="Nenhuma opção encontrada."
                                                        :max-results="20"
                                                        @opening="fecharOutrosComboboxes('form-req-vaga-processo')"
                                                        @select="limparComboboxInvalido(`req-vaga-processo-${hash}`)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4" v-if="form.outras_informacoes.processo === 'Indicação'">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label">Nome Indicação <span class="text-danger">*</span></label>
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    :disabled="modalCamposBloqueados"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    v-model="form.outras_informacoes.nome_indicacao"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label" :for="`req-vaga-contrato-${hash}`">Tipo <span class="text-danger">*</span></label>
                                                <div class="mybp-combobox-wrap">
                                                    <combobox-auto-complete
                                                        ref="comboFormContrato"
                                                        instance-id="form-req-vaga-contrato"
                                                        :input-id="`req-vaga-contrato-${hash}`"
                                                        v-model="form.outras_informacoes.contrato"
                                                        :options="opcoesContrato"
                                                        :disabled="modalCamposBloqueados"
                                                        placeholder-blur="Selecione"
                                                        empty-message="Nenhuma opção encontrada."
                                                        :max-results="20"
                                                        @opening="fecharOutrosComboboxes('form-req-vaga-contrato')"
                                                        @select="limparComboboxInvalido(`req-vaga-contrato-${hash}`)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label">Horário</label>
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    :disabled="modalCamposBloqueados"
                                                    v-model="form.outras_informacoes.horario"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label">Gestor da vaga <span class="text-danger">*</span></label>
                                                <autocomplete
                                                    :caminho="'autocomplete/todos-gestores-ativos'"
                                                    :formsm="true"
                                                    :valido="form.outras_informacoes.gestor_id !== ''"
                                                    v-model="form.outras_informacoes.autocomplete_label_gestor"
                                                    placeholder="Digite o nome do(a) gestor(a)"
                                                    :disabled="modalCamposBloqueados"
                                                    :id="`gestor_${hash}`"
                                                    @onblur="resetaCampoGestor"
                                                    @onselect="selecionaGestor"
                                                ></autocomplete>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label" :for="`req-vaga-ppra-${hash}`">Cargo está no PPRA e PCMSO? <span class="text-danger">*</span></label>
                                                <div class="mybp-combobox-wrap">
                                                    <combobox-auto-complete
                                                        ref="comboFormPpra"
                                                        instance-id="form-req-vaga-ppra"
                                                        :input-id="`req-vaga-ppra-${hash}`"
                                                        v-model="form.outras_informacoes.ppra"
                                                        :options="opcoesSimNao"
                                                        :disabled="modalCamposBloqueados"
                                                        placeholder-blur="Selecione"
                                                        empty-message="Nenhuma opção encontrada."
                                                        :max-results="10"
                                                        @opening="fecharOutrosComboboxes('form-req-vaga-ppra')"
                                                        @select="limparComboboxInvalido(`req-vaga-ppra-${hash}`)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label" :for="`req-vaga-salario-${hash}`">Salário <span class="text-danger">*</span></label>
                                                <div class="mybp-combobox-wrap">
                                                    <combobox-auto-complete
                                                        ref="comboFormSalario"
                                                        instance-id="form-req-vaga-salario"
                                                        :input-id="`req-vaga-salario-${hash}`"
                                                        v-model="form.outras_informacoes.salario"
                                                        :options="opcoesSalario"
                                                        :disabled="modalCamposBloqueados"
                                                        placeholder-blur="Selecione"
                                                        empty-message="Nenhuma opção encontrada."
                                                        :max-results="10"
                                                        @opening="fecharOutrosComboboxes('form-req-vaga-salario')"
                                                        @select="limparComboboxInvalido(`req-vaga-salario-${hash}`)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4" v-if="form.outras_informacoes.salario === 'exceção'">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label">Salário exceção <span class="text-danger">*</span></label>
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    onblur="valida_dinheiro(this)"
                                                    :disabled="modalCamposBloqueados"
                                                    v-mascara:dinheiro
                                                    v-model="form.outras_informacoes.salario_valor_format"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label" :for="`req-vaga-beneficio-${hash}`">Benefício <span class="text-danger">*</span></label>
                                                <div class="mybp-combobox-wrap">
                                                    <combobox-auto-complete
                                                        ref="comboFormBeneficio"
                                                        instance-id="form-req-vaga-beneficio"
                                                        :input-id="`req-vaga-beneficio-${hash}`"
                                                        v-model="form.outras_informacoes.beneficio"
                                                        :options="opcoesBeneficio"
                                                        :disabled="modalCamposBloqueados"
                                                        placeholder-blur="Selecione"
                                                        empty-message="Nenhuma opção encontrada."
                                                        :max-results="10"
                                                        @opening="fecharOutrosComboboxes('form-req-vaga-beneficio')"
                                                        @select="limparComboboxInvalido(`req-vaga-beneficio-${hash}`)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4" v-if="form.outras_informacoes.beneficio === 'exceção'">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label">Benefício exceção <span class="text-danger">*</span></label>
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    :disabled="modalCamposBloqueados"
                                                    v-model="form.outras_informacoes.beneficio_excecao"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label" :for="`req-vaga-treinamento-${hash}`">Treinamento</label>
                                                <div class="mybp-combobox-wrap">
                                                    <combobox-auto-complete
                                                        ref="comboFormTreinamento"
                                                        instance-id="form-req-vaga-treinamento"
                                                        :input-id="`req-vaga-treinamento-${hash}`"
                                                        v-model="form.outras_informacoes.treinamento"
                                                        :options="opcoesTreinamento"
                                                        :disabled="modalCamposBloqueados"
                                                        placeholder-blur="Selecione"
                                                        empty-message="Nenhuma opção encontrada."
                                                        :max-results="10"
                                                        @opening="fecharOutrosComboboxes('form-req-vaga-treinamento')"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4" v-if="form.outras_informacoes.treinamento === 'exceção'">
                                            <div class="form-group mybp-filtro-campo">
                                                <label class="mybp-label">Treinamento exceção <span class="text-danger">*</span></label>
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    :disabled="modalCamposBloqueados"
                                                    v-model="form.outras_informacoes.treinamento_excecao"
                                                />
                                            </div>
                                        </div>
                        </div>

                        <div class="row" v-if="camposCustom.length > 0">
                            <div class="col-12">
                                <fieldset class="mybp-modal-secao">
                                    <legend>Outras informações</legend>
                                    <div class="row">
                                        <div class="col-12 col-md-4" v-for="campo in camposCustom" :key="campo.id">
                                            <div class="form-group mybp-filtro-campo" v-if="campo.tipo === 'sim_nao'">
                                                <label class="mybp-label">{{ campo.label }}<span class="text-danger" v-if="campo.obrigatorio"> *</span></label>
                                                <select
                                                    v-model="form.custom_values[campo.id]"
                                                    class="form-control form-control-sm"
                                                    :disabled="modalCamposBloqueados"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option value="Sim">Sim</option>
                                                    <option value="Não">Não</option>
                                                </select>
                                            </div>
                                            <div class="form-group mybp-filtro-campo" v-else-if="campo.tipo === 'texto'">
                                                <label class="mybp-label">{{ campo.label }}<span class="text-danger" v-if="campo.obrigatorio"> *</span></label>
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    :disabled="modalCamposBloqueados"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    v-model="form.custom_values[campo.id]"
                                                    :placeholder="campo.label"
                                                />
                                            </div>
                                            <div class="form-group mybp-filtro-campo" v-else-if="campo.tipo === 'textarea'">
                                                <label class="mybp-label">{{ campo.label }}<span class="text-danger" v-if="campo.obrigatorio"> *</span></label>
                                                <textarea
                                                    class="form-control form-control-sm"
                                                    rows="3"
                                                    :disabled="modalCamposBloqueados"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    v-model="form.custom_values[campo.id]"
                                                    :placeholder="campo.label"
                                                ></textarea>
                                            </div>
                                            <div class="form-group mybp-filtro-campo" v-else-if="campo.tipo === 'select'">
                                                <label class="mybp-label">{{ campo.label }}<span class="text-danger" v-if="campo.obrigatorio"> *</span></label>
                                                <select
                                                    v-model="form.custom_values[campo.id]"
                                                    class="form-control form-control-sm"
                                                    :disabled="modalCamposBloqueados"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option v-for="opt in campo.opcoes || []" :key="opt" :value="opt">{{ opt }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Previsão de início</label>
                                    <div class="custom-control custom-switch">
                                        <input
                                            type="checkbox"
                                            v-model="form.imediata"
                                            class="custom-control-input"
                                            :disabled="modalCamposBloqueados"
                                            :id="`imediata_${hash}`"
                                        />
                                        <label class="custom-control-label" :for="`imediata_${hash}`">Imediata</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="!form.imediata">
                                <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                                    <label class="mybp-label">Previsão <span class="text-danger">*</span></label>
                                    <datepicker
                                        :id="`req-vaga-previsao-${hash}`"
                                        formsm
                                        label=""
                                        class="corrigiDatepicker"
                                        v-model="form.previsao_inicio"
                                        :disabled="modalCamposBloqueados"
                                    ></datepicker>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-solicitante-${hash}`">Solicitante <span class="text-danger">*</span></label>
                                    <input
                                        :id="`req-vaga-solicitante-${hash}`"
                                        type="text"
                                        class="form-control form-control-sm"
                                        onblur="valida_campo_vazio(this, 1)"
                                        :disabled="modalCamposBloqueados"
                                        v-model="form.solicitante"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.observacao"
                                        cols="5"
                                        rows="3"
                                        :disabled="modalCamposBloqueados"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="alert alert-warning" v-if="!form.data_aprovacao && !cadastrando">
                        Esta solicitação ainda não foi aprovada ou reprovada pelo gestor!
                    </div>

                    <fieldset v-if="visualizar || aprovando" class="mybp-modal-secao">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.user_aprovacao" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao }} por: {{ form.user_aprovacao }} em {{ form.data_aprovacao }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-status-gestor-${hash}`">
                                        Status <span class="text-danger" v-if="aprovando">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusGestor"
                                            instance-id="form-req-vaga-status-gestor"
                                            :input-id="`req-vaga-status-gestor-${hash}`"
                                            v-model="form.status_aprovacao"
                                            :options="opcoesStatusAprovacao"
                                            :disabled="!aprovando || aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-req-vaga-status-gestor')"
                                            @select="limparComboboxInvalido(`req-vaga-status-gestor-${hash}`)"
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
                                        v-model="form.obs_aprovacao"
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
                            <div v-if="!aprovandoExtra && form.aprovacao_extra_nome" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_extra }} por: {{ form.aprovacao_extra_nome }} em
                                    {{ form.data_aprovacao_extra }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-status-extra-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoExtra">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusExtra"
                                            instance-id="form-req-vaga-status-extra"
                                            :input-id="`req-vaga-status-extra-${hash}`"
                                            v-model="form.status_aprovacao_extra"
                                            :options="opcoesStatusAprovacao"
                                            :disabled="!aprovandoExtra || aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-req-vaga-status-extra')"
                                            @select="limparComboboxInvalido(`req-vaga-status-extra-${hash}`)"
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
                            <div v-if="!aprovandoRh && form.rh_aprovacao_nome" class="col-12 mb-2">
                                <p class="mb-0 text-muted">
                                    {{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao_nome }} em
                                    {{ form.data_aprovacao_rh }}
                                </p>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" :for="`req-vaga-status-rh-${hash}`">
                                        Status <span class="text-danger" v-if="aprovandoRh">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboFormStatusRh"
                                            instance-id="form-req-vaga-status-rh"
                                            :input-id="`req-vaga-status-rh-${hash}`"
                                            v-model="form.status_aprovacao_rh"
                                            :options="opcoesStatusAprovacao"
                                            :disabled="!aprovandoRh"
                                            placeholder-blur="Selecione..."
                                            empty-message="Nenhuma opção encontrada."
                                            :max-results="10"
                                            @opening="fecharOutrosComboboxes('form-req-vaga-status-rh')"
                                            @select="limparComboboxInvalido(`req-vaga-status-rh-${hash}`)"
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
                <div>
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="editando && !preload && !cadastrando && !aprovando" @click.prevent="alterar">
                        <i class="fa fa-edit"></i> Alterar
                    </button>
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!editando && !preload && cadastrando && !aprovando" @click.prevent="cadastrar">
                        <i class="fa fa-save"></i> Cadastrar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm mr-1 btn-primary"
                        v-show="aprovando && !editando && !form.data_aprovacao && !cadastrando"
                        @click.prevent="aprovar"
                    >
                        <i class="fa fa-save"></i> Aprovar
                    </button>
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovandoExtra && !preload && !cadastrando" @click.prevent="aprovarExtra">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="aprovandoRh && !preload && !cadastrando" @click.prevent="aprovarRh">
                        <i class="fa fa-save"></i> Salvar
                    </button>
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
                    :id-suffix="'req-vaga-' + hash"
                    label="Período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="req-vaga-filtro-busca">Pesquisar</label>
                        <input
                            id="req-vaga-filtro-busca"
                            type="text"
                            placeholder="Buscar por cargo"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="req-vaga-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="req-vaga-status"
                                input-id="req-vaga-filtro-status"
                                v-model="controle.dados.campoStatusAprovacao"
                                :options="opcoesStatus"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhum status encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('req-vaga-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="req-vaga-filtro-ordenacao">Ordenar por</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroOrdenacao"
                                instance-id="req-vaga-ordenacao"
                                input-id="req-vaga-filtro-ordenacao"
                                v-model="controle.dados.ordenacao"
                                :options="opcoesOrdenacao"
                                :disabled="controle.carregando"
                                placeholder-blur="Mais recentes"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('req-vaga-ordenacao')"
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
                    @click.prevent="formNovo(); $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
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
                                <i class="fas fa-briefcase text-primary mr-1"></i>
                                <strong>{{ (item.cargo && item.cargo.nome) || 'Não informado' }}</strong>
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
                                        @click.prevent="formOpen(item.id); visualizar = true; aprovando = true; aprovandoExtra = false; aprovandoRh = false; editando = false; cadastrando = false; $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                        v-if="!item.data_aprovacao && aprovaGestor"
                                    >
                                        <i class="fa fa-user-check mr-1"></i> Aprovação Gestor
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra"
                                        @click.prevent="formOpen(item.id); visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; editando = false; cadastrando = false; $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                        v-if="temAprovacaoExtra && item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra && aprovaExtra"
                                    >
                                        <i class="fa fa-user-check mr-1"></i> {{ nomeAprovacaoExtra }}
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        @click.prevent="formOpen(item.id); visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; editando = false; cadastrando = false; $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                        v-if="
                                            ((item.status_aprovacao === 'aprovado' && !temAprovacaoExtra) || item.status_aprovacao_extra === 'aprovado') &&
                                            !item.rh_aprovacao_id &&
                                            aprovaRh
                                        "
                                    >
                                        <i class="fa fa-users mr-1"></i> Aprovação RH
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="formOpen(item.id); visualizar = true; editando = false; aprovando = false; aprovandoExtra = false; aprovandoRh = false; cadastrando = false; $refs.modal_janelaCadastrar && $refs.modal_janelaCadastrar.abrirModal()"
                                    >
                                        <i class="fa fa-eye mr-1"></i> Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mybp-card-corpo" :class="classeBordaStatusLista(item)">
                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo icon="fas fa-hashtag" label="Quantidade" :valor="item.quantidade" forte />
                                <mybp-card-campo icon="fas fa-user" label="Solicitante" :valor="item.solicitante" />
                                <mybp-card-campo
                                    icon="fas fa-user-tie"
                                    label="Gestor"
                                    :valor="item.gestor_nome || '-'"
                                />
                            </div>
                        </section>

                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo
                                    icon="fas fa-map-marker-alt"
                                    label="Lotação"
                                    :valor="item.lotacao || 'Não informado'"
                                />
                                <mybp-card-campo
                                    icon="fas fa-building"
                                    label="Centro de custo"
                                    :valor="(item.centro_custo && item.centro_custo.label) || '-'"
                                />
                                <mybp-card-campo
                                    icon="fas fa-tag"
                                    label="Área"
                                    :valor="(item.area && item.area.label) || '-'"
                                />
                            </div>
                        </section>

                        <section class="mybp-card-secao">
                            <div class="mybp-card-row">
                                <mybp-card-campo icon="fas fa-calendar-alt" label="Solicitação" :valor="item.data_solicitacao" />
                                <mybp-card-campo icon="fas fa-file-contract" label="Contratação" :valor="item.tipo_contratacao" />
                                <mybp-card-campo icon="fas fa-exclamation-triangle" label="Prioridade" :valor="item.prioridade" />
                                <mybp-card-campo
                                    icon="fas fa-clock"
                                    label="Início"
                                    :valor="item.imediata ? 'Imediata' : item.previsao_inicio"
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
            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlAtualizar"
                por-pagina="50"
                :dados="controle.dados"
                v-on:carregou="carregou"
                v-on:carregando="carregando"
            >
            </controle-paginacao>
        </div>
    </div>
</template>

<script>
import datepicker from '../../DatePicker'
import DateRangeFilter from '../../DateRangeFilter.vue'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import MybpCardCampo from '../../ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../ui/MybpStatusBadge.vue'
import ComboboxValidation from '../../../mixins/ComboboxValidation'
import configuracoes from '../../../mixins/Configuracoes'
import { buildOpcoesStatusFluxoAprovacao } from '../../../utils/opcoesStatusFluxoAprovacao'

const _ = window._ || { cloneDeep: (x) => JSON.parse(JSON.stringify(x)) }

export default {
    name: 'RequisicaoVaga',
    props: {
        urlAtualizar: { type: String, default: '' }
    },
    components: {
        datepicker,
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem,
        MybpCardCampo,
        MybpFluxoAprovacao,
        MybpStatusBadge
    },
    mixins: [ExportacaoMixin, ComboboxValidation, configuracoes],
    data() {
        return {
            tituloJanela: 'Planejamento - Requisição de Vaga',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            aprovando: false,
            aprovandoExtra: false,
            aprovaGestor: false,
            aprovaExtra: false,
            temAprovacaoExtra: false,
            nomeAprovacaoExtra: 'Aprovação Extra',
            aprovandoRh: false,
            aprovaRh: false,
            preloadExportacao: false,
            urlExportacao: (typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : '') + '/planejamento/requisicao-vaga/export',
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,
            cliente_id: '',
            colunasTabela: { cliente: false },
            selecionados: [],
            selecionaTudo: false,
            dropdownAbertoKey: null,
            form: {
                id: '',
                centro_custo_id: '',
                empresa_id: '',
                cargo_id: '',
                autocomplete_label_cargo_modal: '',
                autocomplete_label_cargo_modal_anterior: '',
                area_id: '',
                quantidade: '',
                tipo_contratacao: '',
                prioridade: '',
                imediata: false,
                previsao_inicio: '',
                solicitante: '',
                observacao: '',
                status_aprovacao: '',
                aprovacao_extra_id: '',
                aprovacao_extra_nome: '',
                obs_aprovacao_extra: '',
                status_aprovacao_extra: '',
                data_aprovacao_extra: '',
                rh_aprovacao_id: '',
                rh_aprovacao: '',
                obs_rh: '',
                status_aprovacao_rh: '',
                data_aprovacao_rh: '',
                outras_informacoes: {
                    posicao: '',
                    processo: '',
                    contrato: '',
                    local_trabalho: '',
                    horario: '',
                    gestor: '',
                    gestor_id: '',
                    autocomplete_label_gestor: '',
                    autocomplete_label_gestor_anterior: '',
                    nome_indicacao: '',
                    ppra: '',
                    salario: '',
                    salario_valor: '',
                    salario_valor_format: '',
                    beneficio: '',
                    beneficio_excecao: '',
                    treinamento: '',
                    treinamento_excecao: ''
                },
                custom_values: {}
            },
            camposCustom: [],
            formDefault: null,
            lista: [],
            vagas: [],
            opened: [],
            areas_etiquetas: [],
            lista_ccs: null,
            formLotacaoCnpj: '',
            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: 'autocomplete/cargos_ativos',
                    autocomplete_label_anterior: '',
                    autocomplete_label: '',
                    pages: 20,
                    campoBusca: '',
                    campoVaga: '',
                    campoFiltro: '',
                    campoStatusAprovacao: '',
                    cliente_custom: '',
                    filtroPeriodo: false,
                    dataInicio: '',
                    dataFim: '',
                    ordenacao: 'created_at_desc'
                }
            }
        }
    },
    computed: {
        paramsExport() {
            return {
                campoBusca: this.controle.dados.campoBusca,
                campoStatusAprovacao: this.controle.dados.campoStatusAprovacao,
                filtroPeriodo: this.controle.dados.filtroPeriodo,
                dataInicio: this.controle.dados.dataInicio,
                dataFim: this.controle.dados.dataFim,
                periodo: this.controle.dados.periodo,
                ordenacao: this.controle.dados.ordenacao
            }
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            let total = [d.campoBusca, d.campoStatusAprovacao].filter((v) => v !== '' && v !== null && v !== undefined).length
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) total++
            if (d.ordenacao && d.ordenacao !== 'created_at_desc') total++
            return total
        },
        opcoesStatus() {
            return buildOpcoesStatusFluxoAprovacao({
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
        opcoesArea() {
            const opts = [{ value: '', label: 'Selecione' }]
            ;(this.areas_etiquetas || []).forEach((item) => {
                if (item && item.id != null) opts.push({ value: item.id, label: item.label })
            })
            return opts
        },
        modalCamposBloqueados() {
            return this.visualizar || this.aprovando || this.aprovandoExtra || this.aprovandoRh
        },
        formListaCentroCustoLotacao() {
            if (!this.lista_ccs) return []
            if (this.temFilial) {
                if (!this.formLotacaoCnpj) return []
                return this.lista_ccs.centros_custos[this.formLotacaoCnpj] || []
            }
            const keys = Object.keys(this.lista_ccs.centros_custos || {})
            return keys.length ? this.lista_ccs.centros_custos[keys[0]] || [] : []
        },
        formLotacaoOpcoes() {
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
        formCentroCustoOpcoes() {
            const opts = [{ value: '', label: 'Selecione...' }]
            ;(this.formListaCentroCustoLotacao || []).forEach((item) => {
                if (item == null || item.id == null || item.id === '') return
                opts.push({
                    value: String(item.id),
                    label: item.label,
                    meta: item.matriz ? 'Matriz' : 'Filial'
                })
            })
            return opts
        },
        formCentroCustoCombo: {
            get() {
                return this.form.centro_custo_id != null && this.form.centro_custo_id !== ''
                    ? String(this.form.centro_custo_id)
                    : ''
            },
            set(valor) {
                if (valor == null || valor === '') {
                    this.form.centro_custo_id = ''
                    return
                }
                this.form.centro_custo_id = String(valor)
            }
        },
        opcoesTipoContratacao() {
            return [
                { value: '', label: 'Selecione' },
                { value: 'APRENDIZ', label: 'APRENDIZ' },
                { value: 'FIXO', label: 'FIXO' },
                { value: 'INTERMITENTE', label: 'INTERMITENTE' },
                { value: 'PJ', label: 'PJ' },
                { value: 'ESTÁGIO', label: 'ESTÁGIO' }
            ]
        },
        opcoesPrioridade() {
            return [
                { value: '', label: 'Selecione' },
                { value: 'ALTA', label: 'ALTA' },
                { value: 'MÉDIA', label: 'MÉDIA' },
                { value: 'URGENTE', label: 'URGENTE' }
            ]
        },
        opcoesPosicao() {
            return [
                { value: '', label: 'Selecione' },
                { value: 'Efetiva', label: 'Efetiva' },
                { value: 'Estágio', label: 'Estágio' },
                { value: 'Temporária', label: 'Temporária' },
                { value: 'Aumento de Quadro', label: 'Aumento de Quadro' },
                { value: 'Substituição', label: 'Substituição' }
            ]
        },
        opcoesProcesso() {
            return [
                { value: '', label: 'Selecione' },
                { value: 'Externo', label: 'Externo' },
                { value: 'Confidencial', label: 'Confidencial' },
                { value: 'Interno', label: 'Interno' },
                { value: 'Indicação', label: 'Indicação' }
            ]
        },
        opcoesContrato() {
            return [
                { value: '', label: 'Selecione' },
                { value: 'Admissão', label: 'Admissão' },
                { value: 'Readmissão', label: 'Readmissão' },
                { value: 'Reintegração', label: 'Reintegração' }
            ]
        },
        opcoesSimNao() {
            return [
                { value: '', label: 'Selecione' },
                { value: true, label: 'Sim' },
                { value: false, label: 'Não' }
            ]
        },
        opcoesSalario() {
            return [
                { value: '', label: 'Selecione' },
                { value: 'conforme', label: 'Conforme o plano de cargo' },
                { value: 'exceção', label: 'Exceção' }
            ]
        },
        opcoesBeneficio() {
            return [
                { value: '', label: 'Selecione' },
                { value: 'conforme', label: 'Conforme o plano da empresa' },
                { value: 'exceção', label: 'Exceção' }
            ]
        },
        opcoesTreinamento() {
            return [
                { value: '', label: 'Selecione' },
                { value: 'conforme', label: 'Conforme o padrão' },
                { value: 'exceção', label: 'Exceção' }
            ]
        },
        opcoesStatusAprovacao() {
            return [
                { value: '', label: 'Selecione...' },
                { value: 'aprovado', label: 'Aprovar' },
                { value: 'reprovado', label: 'Reprovar' }
            ]
        }
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
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
        this.urlParamGet()
        this.usuarioAutenticado()
        this.carregarCamposCustom()
        this.$nextTick(() => {
            const page = this.controle.dados.page
            if (this.$refs.componente && page >= 1) this.$refs.componente.atual = page
        })
        setTimeout(() => this.atualizar(), 200)
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    methods: {
        toggleDropdown(itemId) {
            if (!itemId) {
                return
            }
            const key = `req:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `req:${itemId}`
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
        chaveStatusLista(item) {
            if (!item) return 'aberto'
            if (
                item.status_aprovacao === 'reprovado' ||
                item.status_aprovacao_extra === 'reprovado' ||
                item.status_aprovacao_rh === 'reprovado'
            ) {
                return 'reprovado'
            }
            if (item.status_aprovacao_rh === 'aprovado') return 'rh'
            if (this.temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') return 'extra'
            if (item.status_aprovacao === 'aprovado') return 'gestor'
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
                    nome: item.solicitante,
                    data: item.created_at_br || item.data_solicitacao
                },
                {
                    key: 'gestor',
                    label: 'Gestor',
                    status:
                        item.status_aprovacao === 'aprovado'
                            ? 'aprovado'
                            : item.status_aprovacao === 'reprovado'
                              ? 'reprovado'
                              : 'aguardando',
                    nome: item.user_aprovacao_nome || (item.user_aprovacao && item.user_aprovacao.nome) || '',
                    data: item.data_aprovacao_br || item.data_aprovacao
                }
            ]

            if (this.temAprovacaoExtra) {
                let statusExtra = 'pendente'
                if (item.status_aprovacao === 'reprovado') {
                    statusExtra = 'cancelado'
                } else if (item.status_aprovacao_extra === 'aprovado') {
                    statusExtra = 'aprovado'
                } else if (item.status_aprovacao_extra === 'reprovado') {
                    statusExtra = 'reprovado'
                } else if (item.status_aprovacao === 'aprovado') {
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
                item.status_aprovacao === 'reprovado' ||
                (this.temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')
            ) {
                statusRh = 'cancelado'
            } else if (item.status_aprovacao_rh === 'aprovado') {
                statusRh = 'aprovado'
            } else if (item.status_aprovacao_rh === 'reprovado') {
                statusRh = 'reprovado'
            } else if (
                (this.temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                (!this.temAprovacaoExtra && item.status_aprovacao === 'aprovado')
            ) {
                statusRh = 'aguardando'
            }

            steps.push({
                key: 'rh',
                label: 'RH',
                status: statusRh,
                nome: item.rh_aprovacao_nome,
                data: item.data_aprovacao_rh_br || item.data_aprovacao_rh
            })

            return steps
        },
        onSelectFiltro() {
            this.atualizar()
        },
        limparFiltros() {
            const d = this.controle.dados
            d.campoBusca = ''
            d.campoStatusAprovacao = ''
            d.filtroPeriodo = false
            d.dataInicio = ''
            d.dataFim = ''
            d.ordenacao = 'created_at_desc'
            this.atualizar()
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'req-vaga-status': 'comboFiltroStatus',
                'req-vaga-ordenacao': 'comboFiltroOrdenacao',
                'form-req-vaga-lotacao': 'comboFormLotacao',
                'form-req-vaga-area': 'comboFormArea',
                'form-req-vaga-cc': 'comboFormCc',
                'form-req-vaga-tipo': 'comboFormTipo',
                'form-req-vaga-prioridade': 'comboFormPrioridade',
                'form-req-vaga-posicao': 'comboFormPosicao',
                'form-req-vaga-processo': 'comboFormProcesso',
                'form-req-vaga-contrato': 'comboFormContrato',
                'form-req-vaga-ppra': 'comboFormPpra',
                'form-req-vaga-salario': 'comboFormSalario',
                'form-req-vaga-beneficio': 'comboFormBeneficio',
                'form-req-vaga-treinamento': 'comboFormTreinamento',
                'form-req-vaga-status-gestor': 'comboFormStatusGestor',
                'form-req-vaga-status-extra': 'comboFormStatusExtra',
                'form-req-vaga-status-rh': 'comboFormStatusRh'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        limparFormCentroCusto() {
            this.form.centro_custo_id = ''
        },
        onSelectFormLotacao() {
            this.limparFormCentroCusto()
            this.limparComboboxInvalido(`req-vaga-lotacao-${this.hash}`)
        },
        onSelectFormCentroCusto() {
            this.limparComboboxInvalido(`req-vaga-cc-${this.hash}`)
        },
        resolverLotacaoDoForm() {
            this.formLotacaoCnpj = ''
            if (!this.lista_ccs || !this.lista_ccs.centros_custos) {
                return
            }
            if (!this.temFilial) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                this.formLotacaoCnpj = keys[0] || ''
                return
            }
            const ccId = this.form.centro_custo_id != null ? String(this.form.centro_custo_id) : ''
            if (!ccId) return
            Object.keys(this.lista_ccs.centros_custos).some((cnpjKey) => {
                const lista = this.lista_ccs.centros_custos[cnpjKey] || []
                const encontrado = _.find(lista, (item) => String(item.id) === ccId)
                if (encontrado) {
                    this.formLotacaoCnpj = cnpjKey
                    return true
                }
                return false
            })
        },
        carregarCamposCustom() {
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .get(`${base}/planejamento/requisicao-vaga/campos-custom`)
                .then((r) => {
                    this.camposCustom = r.data || []
                })
                .catch(() => {
                    this.camposCustom = []
                })
        },
        initFormCustomValues() {
            const cv = {}
            this.camposCustom.forEach((c) => {
                cv[c.id] = this.form.custom_values[c.id] != null ? this.form.custom_values[c.id] : ''
            })
            this.form.custom_values = cv
        },
        selecionaVagaModal(obj) {
            this.form.cargo_id = obj.id
            this.form.autocomplete_label_cargo_modal = obj.label
            this.form.autocomplete_label_cargo_modal_anterior = obj.label
        },
        resetaCampoVagaModal() {
            if (this.form.autocomplete_label_cargo_modal_anterior !== this.form.autocomplete_label_cargo_modal) {
                this.form.autocomplete_label_cargo_modal_anterior = ''
                this.form.autocomplete_label_cargo_modal = ''
                this.form.cargo_id = ''
                setTimeout(() => {
                    if (this.form.cargo_id === '') {
                        if (typeof valida_campo_vazio === 'function') valida_campo_vazio($('#vaga_modal_' + this.hash), 1)
                        if (typeof mostraErro === 'function') mostraErro('Erro', 'O Campo CARGO não pode ficar vazio')
                    }
                }, 100)
            }
        },
        selecionaGestor(obj) {
            this.form.outras_informacoes.gestor_id = obj.id
            this.form.outras_informacoes.autocomplete_label_gestor = obj.label
            this.form.outras_informacoes.autocomplete_label_gestor_anterior = obj.label
        },
        resetaCampoGestor() {
            if (this.form.outras_informacoes.autocomplete_label_gestor_anterior !== this.form.outras_informacoes.autocomplete_label_gestor) {
                this.form.outras_informacoes.autocomplete_label_gestor_anterior = ''
                this.form.outras_informacoes.autocomplete_label_gestor = ''
                this.form.outras_informacoes.gestor_id = ''
                setTimeout(() => {
                    if (this.form.outras_informacoes.gestor_id === '' && typeof mostraErro === 'function') {
                        mostraErro('Erro', 'O Campo GESTOR DA VAGA não pode ficar vazio')
                    }
                }, 100)
            }
        },
        formNovo() {
            this.cadastrando = true
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false
            this.cadastrado = false
            this.atualizado = false
            this.tituloJanela = 'Solicitando Vaga'
            if (typeof formReset === 'function') formReset()
            if (typeof setupCampo === 'function') setupCampo()
            this.form = _.cloneDeep(this.formDefault)
            this.initFormCustomValues()
            this.listaAreasEtiquetas()
            this.formLotacaoCnpj = ''
            if (!this.temFilial && this.lista_ccs && this.lista_ccs.centros_custos) {
                const keys = Object.keys(this.lista_ccs.centros_custos || {})
                this.formLotacaoCnpj = keys[0] || ''
            }
        },
        formOpen(id) {
            this.cadastrando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false
            this.cadastrado = false
            this.atualizado = false
            this.listaAreasEtiquetas()
            Object.assign(this.form, this.formDefault)
            this.form.id = id
            this.tituloJanela = `#${id}`
            if (typeof formReset === 'function') formReset()
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .get(`${base}/planejamento/requisicao-vaga/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.initFormCustomValues()
                    if (this.form.outras_informacoes && this.form.outras_informacoes.salario_valor != null) {
                        this.form.outras_informacoes.salario_valor_format =
                            this.form.outras_informacoes.salario_valor_format ||
                            this.form.outras_informacoes.salario_valor
                    }
                    this.resolverLotacaoDoForm()
                    this.tituloJanela = `#${id} Planejamento - Requisição de vagas`
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        validarCamposCustomObrigatorios() {
            for (let i = 0; i < this.camposCustom.length; i++) {
                const c = this.camposCustom[i]
                if (c.obrigatorio !== true && c.obrigatorio !== 1) continue
                const val = this.form.custom_values[c.id]
                if (val === undefined || val === null || String(val).trim() === '') {
                    if (typeof mostraErro === 'function') mostraErro('', `O campo "${c.label}" é obrigatório.`)
                    return false
                }
            }
            return true
        },
        validarCamposSolicitacao() {
            if (this.modalCamposBloqueados) {
                return true
            }
            const h = this.hash
            if (!this.form.cargo_id) {
                if (typeof mostraErro === 'function') mostraErro('', 'Campo CARGO não pode ficar vazio')
                this.resetaCampoVagaModal()
                return false
            }
            if (
                !this.exigirCombobox(this.form.area_id, `req-vaga-area-${h}`, { toastMsg: 'Selecione a área' }) ||
                !this.exigirCombobox(this.form.tipo_contratacao, `req-vaga-tipo-${h}`, {
                    toastMsg: 'Selecione o tipo de contratação'
                }) ||
                !this.exigirCombobox(this.form.prioridade, `req-vaga-prioridade-${h}`, {
                    toastMsg: 'Selecione a prioridade'
                })
            ) {
                return false
            }
            if (this.temFilial) {
                if (
                    !this.exigirCombobox(this.formLotacaoCnpj, `req-vaga-lotacao-${h}`, {
                        toastMsg: 'Selecione a lotação'
                    })
                ) {
                    return false
                }
            }
            if (
                !this.exigirCombobox(this.form.centro_custo_id || this.formCentroCustoCombo, `req-vaga-cc-${h}`, {
                    toastMsg: 'Selecione o centro de custo'
                })
            ) {
                return false
            }
            if (
                !this.exigirCombobox(this.form.outras_informacoes.posicao, `req-vaga-posicao-${h}`, {
                    toastMsg: 'Selecione a posição'
                }) ||
                !this.exigirCombobox(this.form.outras_informacoes.processo, `req-vaga-processo-${h}`, {
                    toastMsg: 'Selecione o processo'
                }) ||
                !this.exigirCombobox(this.form.outras_informacoes.contrato, `req-vaga-contrato-${h}`, {
                    toastMsg: 'Selecione o tipo'
                }) ||
                !this.exigirCombobox(this.form.outras_informacoes.ppra, `req-vaga-ppra-${h}`, {
                    toastMsg: 'Informe se o cargo está no PPRA/PCMSO'
                }) ||
                !this.exigirCombobox(this.form.outras_informacoes.salario, `req-vaga-salario-${h}`, {
                    toastMsg: 'Selecione o salário'
                }) ||
                !this.exigirCombobox(this.form.outras_informacoes.beneficio, `req-vaga-beneficio-${h}`, {
                    toastMsg: 'Selecione o benefício'
                })
            ) {
                return false
            }
            if (!this.form.outras_informacoes.gestor_id) {
                if (typeof mostraErro === 'function') mostraErro('', 'O campo GESTOR DA VAGA não pode ficar vazio')
                this.resetaCampoGestor()
                return false
            }
            if (!this.form.imediata) {
                if (
                    !this.exigirCampoData(this.form.previsao_inicio, `req-vaga-previsao-${h}`, {
                        toastMsg: 'Informe a previsão de início'
                    })
                ) {
                    return false
                }
            }
            if (!this.validarInputsAtivosVisiveis('janelaCadastrar', { preservarIds: [`req-vaga-previsao-${h}`] })) {
                return false
            }
            if (!this.validarCamposCustomObrigatorios()) return false
            return true
        },
        cadastrar() {
            if (!this.validarCamposSolicitacao()) return false
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .post(`${base}/planejamento/requisicao-vaga/`, this.form)
                .then(() => {
                    if (typeof mostraSucesso === 'function') mostraSucesso('', 'Solicitação registrada com sucesso!')
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        alterar() {
            if (!this.validarCamposSolicitacao()) return false
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .put(`${base}/planejamento/requisicao-vaga/${this.form.id}`, this.form)
                .then(() => {
                    if (typeof mostraSucesso === 'function') mostraSucesso('', 'Solicitação alterada com sucesso!')
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        aprovar() {
            if (
                !this.exigirCombobox(this.form.status_aprovacao, `req-vaga-status-gestor-${this.hash}`, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return false
            }
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .put(`${base}/planejamento/requisicao-vaga/${this.form.id}/aprovar`, this.form)
                .then(() => {
                    if (typeof mostraSucesso === 'function') mostraSucesso('', 'Registro salvo com sucesso!')
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        aprovarExtra() {
            if (
                !this.exigirCombobox(this.form.status_aprovacao_extra, `req-vaga-status-extra-${this.hash}`, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return false
            }
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .put(`${base}/planejamento/requisicao-vaga/${this.form.id}/aprovarextra`, this.form)
                .then(() => {
                    if (typeof mostraSucesso === 'function') mostraSucesso('', 'Registro salvo com sucesso!')
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        aprovarRh() {
            if (
                !this.exigirCombobox(this.form.status_aprovacao_rh, `req-vaga-status-rh-${this.hash}`, {
                    toastMsg: 'Selecione o status da aprovação'
                })
            ) {
                return false
            }
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .put(`${base}/planejamento/requisicao-vaga/${this.form.id}/aprovarrh`, {
                    id: this.form.id,
                    obs_rh: this.form.obs_rh || null,
                    status_aprovacao_rh: this.form.status_aprovacao_rh || ''
                })
                .then(() => {
                    if (typeof mostraSucesso === 'function') mostraSucesso('', 'Registro salvo com sucesso!')
                    this.$refs.modal_janelaCadastrar && this.$refs.modal_janelaCadastrar.fecharModal()
                    if (this.$refs.componente && typeof this.$refs.componente.buscar === 'function')
                        this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                    const msg =
                        error.response && error.response.data && error.response.data.msg
                            ? error.response.data.msg
                            : 'Houve um erro ao aprovar. Tente novamente.'
                    if (typeof mostraErro === 'function') mostraErro('', msg)
                })
        },
        listaAreasEtiquetas() {
            const base = typeof URL_PUBLICO !== 'undefined' ? URL_PUBLICO : ''
            axios
                .get(`${base}/lista-areas`)
                .then((res) => {
                    this.areas_etiquetas = res.data.areas || []
                })
                .catch(() => {})
        },
        usuarioAutenticado() {
            this.controle.carregando = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .get(`${base}/usuario/autenticado/`)
                .then((response) => {
                    const data = response.data
                    this.cliente_id = data.cliente_id
                    this.colunasTabela.cliente = this.cliente_id === 0
                    this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente
                })
                .catch(() => {
                    this.preload = false
                })
        },
        carregou(dados) {
            this.lista = dados.itens || []
            this.aprovaGestor = dados.aprovar_por_gestor || false
            this.aprovaExtra = dados.pode_aprovar_extra || false
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || 'Aprovação Extra'
            this.aprovaRh = dados.aprovar_por_rh || false
            if (dados.cc) {
                this.lista_ccs = dados.cc
            }
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.syncUrlFiltros()
            if (this.$refs.componente) {
                this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
                this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            if (urlParams.get('page')) {
                const p = parseInt(urlParams.get('page'), 10)
                if (p >= 1) this.controle.dados.page = p
            }
            if (urlParams.get('ordenacao')) this.controle.dados.ordenacao = urlParams.get('ordenacao')
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoStatusAprovacao')) {
                this.controle.dados.campoStatusAprovacao = urlParams.get('campoStatusAprovacao')
            } else if (urlParams.get('campoStatus')) {
                this.controle.dados.campoStatusAprovacao = urlParams.get('campoStatus')
            }
            if (urlParams.get('dataInicio')) this.controle.dados.dataInicio = urlParams.get('dataInicio')
            if (urlParams.get('dataFim')) this.controle.dados.dataFim = urlParams.get('dataFim')
            if (urlParams.get('dataInicio') || urlParams.get('dataFim')) this.controle.dados.filtroPeriodo = true
            const fp = urlParams.get('filtroPeriodo')
            if (fp === '1' || fp === 'true') this.controle.dados.filtroPeriodo = true
        },
        syncUrlFiltros() {
            const d = this.controle.dados
            const atual = this.$refs.componente && this.$refs.componente.atual ? this.$refs.componente.atual : 1
            const params = {}
            if (atual > 1) params.page = atual
            if (d.ordenacao && d.ordenacao !== 'created_at_desc') params.ordenacao = d.ordenacao
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoStatusAprovacao) params.campoStatusAprovacao = d.campoStatusAprovacao
            if (d.filtroPeriodo) params.filtroPeriodo = 1
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            const qs = new URLSearchParams(params).toString()
            const url = qs ? `${window.location.pathname}?${qs}` : window.location.pathname
            window.history.replaceState({}, '', url)
        }
    }
}
</script>

<style scoped>
/* Estilos de card/fluxo: _mybp-card-detalhe.scss / _mybp-listagem-ui.scss */
</style>
