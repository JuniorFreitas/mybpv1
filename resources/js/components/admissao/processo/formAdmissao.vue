<template>
    <div>
        <preload v-if="preload"></preload>
        <div v-if="!preload" class="mybp-modal-form mybp-filtros-compactos" id="form-admissao-modal">
            <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda">
                Campos com <span class="text-danger">*</span> são obrigatórios.
            </p>

            <fieldset class="mybp-modal-secao">
                <legend>Lotação e contrato</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-contrato">Número do Contrato</label>
                            <input
                                id="fa-contrato"
                                type="text"
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabled"
                                v-model="form.contrato"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-area">Área</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-area"
                                    input-id="fa-area"
                                    v-model="form.area_etiqueta_id"
                                    :options="opcoesArea"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma área encontrada."
                                    :max-results="50"
                                    @opening="fecharOutrosComboboxes('fa-area')"
                                    @select="limparComboboxInvalido('fa-area')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-cc">Centro de Custo</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-cc"
                                    input-id="fa-cc"
                                    v-model="form.centro_custo_id"
                                    :options="opcoesCentroCusto"
                                    :disabled="visualizar || disabled || form.status === 'ADMITIDO'"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhum centro de custo."
                                    :max-results="50"
                                    @opening="fecharOutrosComboboxes('fa-cc')"
                                    @select="onSelectCentroCusto"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4" v-if="centroCustoTemFilial">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-tipo-cnpj">CNPJ</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-tipo-cnpj"
                                    input-id="fa-tipo-cnpj"
                                    v-model="filialCombo"
                                    :options="opcoesTipoCnpj"
                                    :disabled="visualizar || disabled || form.status === 'ADMITIDO'"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="5"
                                    @opening="fecharOutrosComboboxes('fa-tipo-cnpj')"
                                    @select="onSelectTipoCnpj"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4" v-if="temFilial && form.filial">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-filial">Filial</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-filial"
                                    input-id="fa-filial"
                                    v-model="form.centro_custo_filial_id"
                                    :options="opcoesFilial"
                                    :disabled="visualizar || disabled || form.status === 'ADMITIDO'"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma filial."
                                    :max-results="50"
                                    @opening="fecharOutrosComboboxes('fa-filial')"
                                    @select="limparComboboxInvalido('fa-filial')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-funcao">Função <span class="text-danger">*</span></label>
                            <input
                                id="fa-funcao"
                                type="text"
                                class="form-control form-control-sm"
                                onblur="valida_campo_vazio(this, 2)"
                                :disabled="visualizar || disabled || form.status === 'ADMITIDO'"
                                v-model="form.funcao"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-salario">Salário R$</label>
                            <input
                                id="fa-salario"
                                type="text"
                                class="form-control form-control-sm"
                                v-mascara:dinheiro
                                :disabled="visualizar || disabled || form.status === 'ADMITIDO'"
                                v-model="form.salario"
                            />
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Documentos e tipo</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-documento">Documento</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-documento"
                                    input-id="fa-documento"
                                    v-model="form.documento"
                                    :options="opcoesDocumento"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="30"
                                    @opening="fecharOutrosComboboxes('fa-documento')"
                                    @select="limparComboboxInvalido('fa-documento')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-doc-portaria">Documento Portaria</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-doc-portaria"
                                    input-id="fa-doc-portaria"
                                    v-model="form.documento_portaria"
                                    :options="opcoesDocumentoPortaria"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="30"
                                    @opening="fecharOutrosComboboxes('fa-doc-portaria')"
                                    @select="limparComboboxInvalido('fa-doc-portaria')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-tipo">Tipo de admissão</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-tipo"
                                    input-id="fa-tipo"
                                    v-model="form.tipo_admissao"
                                    :options="opcoesTipoAdmissao"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="30"
                                    @opening="fecharOutrosComboboxes('fa-tipo')"
                                    @select="limparComboboxInvalido('fa-tipo')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4" v-if="form.tipo_admissao === 'FIXO'">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-prazo">
                                Prazo de experiência <span class="text-danger">*</span>
                            </label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-prazo"
                                    input-id="fa-prazo"
                                    v-model="form.prazo_experiencia"
                                    :options="opcoesPrazo"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="30"
                                    @opening="fecharOutrosComboboxes('fa-prazo')"
                                    @select="limparComboboxInvalido('fa-prazo')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div
                        class="col-12 col-md-4"
                        v-if="['TEMPORARIO', 'DETERMINADO', 'INTERMITENTE'].includes(form.tipo_admissao)"
                    >
                        <div class="form-group mybp-filtro-campo mybp-modal-campo-data">
                            <label class="mybp-label">Data de encerramento</label>
                            <datepicker
                                id="fa-encerramento"
                                label=""
                                class="corrigiDatepicker"
                                formsm
                                v-model="form.data_encerramento"
                                :disabled="visualizar || disabled"
                            ></datepicker>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>Treinamento e status</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-treinamento">Treinamento</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-treinamento"
                                    input-id="fa-treinamento"
                                    v-model="form.treinamento"
                                    :options="opcoesTreinamento"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="30"
                                    @opening="fecharOutrosComboboxes('fa-treinamento')"
                                    @select="limparComboboxInvalido('fa-treinamento')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4" v-if="form.treinamento === 'REALIZADO'">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-tipo-trein">Tipo de Treinamento</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-tipo-trein"
                                    input-id="fa-tipo-trein"
                                    v-model="form.tipo_treinamento"
                                    :options="opcoesTipoTreinamento"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="10"
                                    @opening="fecharOutrosComboboxes('fa-tipo-trein')"
                                    @select="limparComboboxInvalido('fa-tipo-trein')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-cracha">Número Crachá</label>
                            <input
                                id="fa-cracha"
                                type="text"
                                class="form-control form-control-sm"
                                onblur="valida_campo(this, 2)"
                                :disabled="visualizar || disabled"
                                v-model="form.numero_cracha"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-status-carteira">Status Carteira / Etiqueta</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-status-carteira"
                                    input-id="fa-status-carteira"
                                    v-model="form.status_carteira_treinamento"
                                    :options="opcoesStatusCarteira"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="30"
                                    @opening="fecharOutrosComboboxes('fa-status-carteira')"
                                    @select="limparComboboxInvalido('fa-status-carteira')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-segmento">Padrão de treinamento</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-segmento"
                                    input-id="fa-segmento"
                                    v-model="form.segmento_treinamento_id"
                                    :options="opcoesSegmento"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhum padrão."
                                    :max-results="50"
                                    @opening="fecharOutrosComboboxes('fa-segmento')"
                                    @select="limparComboboxInvalido('fa-segmento')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-status">Status <span class="text-danger">*</span></label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-status"
                                    input-id="fa-status"
                                    v-model="form.status"
                                    :options="opcoesStatus"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="30"
                                    @opening="fecharOutrosComboboxes('fa-status')"
                                    @select="limparComboboxInvalido('fa-status')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-aso">Data do ASO</label>
                            <input
                                id="fa-aso"
                                type="text"
                                class="form-control form-control-sm"
                                placeholder="dd/mm/aaaa"
                                :disabled="visualizar || disabled"
                                v-model="form.ultimo_aso.data_realizacao"
                                v-mascara:data
                                readonly
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-data-adm">
                                Data da Admissão
                                <span v-if="verificaStatusAdmitidoProntoAdmissao" class="text-danger">*</span>
                            </label>
                            <input
                                id="fa-data-adm"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                placeholder="dd/mm/aaaa"
                                :disabled="visualizar || disabled"
                                v-model="form.data_admissao"
                                v-mascara:data
                                @keyup.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_data_vazio($event.target) : valida_data($event.target)"
                                @blur.prevent="verificaStatusAdmitidoProntoAdmissao ? valida_data_vazio($event.target) : valida_data($event.target)"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-entrega">Data da Entrega na área</label>
                            <input
                                id="fa-entrega"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                placeholder="dd/mm/aaaa"
                                :disabled="visualizar || disabled"
                                v-model="form.data_entrega_area"
                                v-mascara:data
                                @keyup.prevent="valida_data($event.target)"
                                @blur.prevent="valida_data($event.target)"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-biometria">Biometria</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    instance-id="fa-biometria"
                                    input-id="fa-biometria"
                                    v-model="biometriaCombo"
                                    :options="opcoesSimNao"
                                    :disabled="visualizar || disabled"
                                    placeholder-blur="Selecione..."
                                    empty-message="Nenhuma opção."
                                    :max-results="5"
                                    @opening="fecharOutrosComboboxes('fa-biometria')"
                                    @select="limparComboboxInvalido('fa-biometria')"
                                ></combobox-auto-complete>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4" v-if="form.biometria">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-data-bio">Data Biometria</label>
                            <input
                                id="fa-data-bio"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                placeholder="dd/mm/aaaa"
                                :disabled="visualizar || disabled"
                                v-model="form.data_biometria"
                                v-mascara:data
                                @keyup.prevent="valida_data($event.target)"
                                @blur.prevent="valida_data($event.target)"
                            />
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mybp-modal-secao">
                <legend>PIS e CTPS</legend>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-pis">
                                PIS <span v-if="verificaStatusAdmitidoProntoAdmissao" class="text-danger">*</span>
                            </label>
                            <input
                                id="fa-pis"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                @keyup.prevent="valida_campo($event.target, 8)"
                                @blur.prevent="valida_campo($event.target, 8)"
                                :disabled="visualizar || disabled"
                                v-model="form.pis"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-ctps-num">Número CTPS</label>
                            <input
                                id="fa-ctps-num"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                @keyup.prevent="valida_campo($event.target, 2)"
                                @blur.prevent="valida_campo($event.target, 2)"
                                :disabled="visualizar || disabled"
                                v-model="form.dados_admissoes.ctps_numero"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-ctps-serie">
                                Série CTPS
                                <span
                                    v-if="verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.ctps_numero && String(form.dados_admissoes.ctps_numero).length"
                                    class="text-danger"
                                >*</span>
                            </label>
                            <input
                                id="fa-ctps-serie"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                @keyup.prevent="
                                    verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.ctps_numero.length
                                        ? valida_campo_vazio($event.target, 2)
                                        : valida_campo($event.target, 2)
                                "
                                @blur.prevent="
                                    verificaStatusAdmitidoProntoAdmissao && form.dados_admissoes.ctps_numero.length
                                        ? valida_campo_vazio($event.target, 2)
                                        : valida_campo($event.target, 2)
                                "
                                :disabled="visualizar || disabled"
                                v-model="form.dados_admissoes.ctps_serie"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-ctps-uf">CTPS UF</label>
                            <input
                                id="fa-ctps-uf"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                maxlength="2"
                                @keyup.prevent="valida_campo($event.target, 2)"
                                @blur.prevent="valida_campo($event.target, 2)"
                                :disabled="visualizar || disabled"
                                v-model="form.dados_admissoes.ctps_uf"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-ctps-emissao">Data Emissão CTPS</label>
                            <input
                                id="fa-ctps-emissao"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                placeholder="dd/mm/aaaa"
                                :disabled="visualizar || disabled"
                                v-model="form.dados_admissoes.ctps_data_emissao"
                                v-mascara:data
                                @keyup.prevent="valida_data($event.target)"
                                @blur.prevent="valida_data($event.target)"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-reservista-num">Cert. Reservista Nº</label>
                            <input
                                id="fa-reservista-num"
                                type="text"
                                class="form-control form-control-sm"
                                :disabled="visualizar || disabled"
                                @keyup.prevent="valida_campo($event.target, 2)"
                                @blur.prevent="valida_campo($event.target, 2)"
                                v-model="form.dados_admissoes.cert_reservista_num"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-reservista-cat">Cert. Reservista Categoria</label>
                            <input
                                id="fa-reservista-cat"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                @keyup.prevent="valida_campo($event.target, 2)"
                                @blur.prevent="valida_campo($event.target, 2)"
                                :disabled="visualizar || disabled"
                                v-model="form.dados_admissoes.cert_reservista_categoria"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-titulo">Título de Eleitor</label>
                            <input
                                id="fa-titulo"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                @keyup.prevent="valida_campo($event.target, 8)"
                                @blur.prevent="valida_campo($event.target, 8)"
                                :disabled="visualizar || disabled"
                                v-model="form.dados_admissoes.titulo_eleitor_numero"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-titulo-sessao">Título Sessão</label>
                            <input
                                id="fa-titulo-sessao"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                @keyup.prevent="valida_campo($event.target, 2)"
                                @blur.prevent="valida_campo($event.target, 2)"
                                :disabled="visualizar || disabled"
                                v-model="form.dados_admissoes.titulo_eleitor_sessao"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="fa-titulo-zona">Título Zona</label>
                            <input
                                id="fa-titulo-zona"
                                type="text"
                                class="form-control form-control-sm validacampo"
                                @keyup.prevent="valida_campo($event.target, 2)"
                                @blur.prevent="valida_campo($event.target, 2)"
                                :disabled="visualizar || disabled"
                                v-model="form.dados_admissoes.titulo_eleitor_zona"
                            />
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="col-12 px-0">
                <ferias-adquiridas
                    ref="feriasAdquiridas"
                    :model="form.ferias_adquiridas"
                    :model-delete="form.ferias_adquiridasDelete"
                    :visualizar="visualizar || disabled"
                ></ferias-adquiridas>
            </div>
        </div>
    </div>
</template>

<script>
import Validacoes from '../../../mixins/Validacoes'
import ComboboxValidation from '../../../mixins/ComboboxValidation'
import FeriasAdquiridas from './FeriasAdquiridas'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import configuracoes from '../../../mixins/Configuracoes'

function mapStr(lista) {
    return (lista || []).map((item) => ({ value: item, label: String(item) }))
}

export default {
    mixins: [Validacoes, configuracoes, ComboboxValidation],
    components: { FeriasAdquiridas, ComboboxAutoComplete },
    props: {
        form: { type: Object, required: true },
        visualizar: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false }
    },
    data() {
        return {
            preload: true,
            areasetiquetas: [],
            listSelects: {},
            centro_custos: []
        }
    },
    computed: {
        verificaStatusAdmitidoProntoAdmissao() {
            return ['ADMITIDO', 'PRONTO PARA ADMISSÃO'].includes(this.form.status)
        },
        centroCustoSelecionado() {
            if (!this.form.centro_custo_id) return []
            const centro = _.find(this.centro_custos, { id: this.form.centro_custo_id })
            return centro && centro.filiais && centro.filiais.length ? centro.filiais : []
        },
        centroCustoTemFilial() {
            return this.temFilial && this.centroCustoSelecionado.length > 0
        },
        opcoesArea() {
            return (this.areasetiquetas || []).map((i) => ({ value: i.id, label: i.label }))
        },
        opcoesCentroCusto() {
            return (this.centro_custos || []).map((i) => ({ value: i.id, label: i.label }))
        },
        opcoesFilial() {
            return (this.centroCustoSelecionado || []).map((i) => ({
                value: i.id,
                label: (i.filial && i.filial.razao_social) || String(i.id)
            }))
        },
        opcoesTipoCnpj() {
            return [
                { value: 'matriz', label: 'Matriz' },
                { value: 'filial', label: 'Filial' }
            ]
        },
        opcoesSimNao() {
            return [
                { value: 'sim', label: 'SIM' },
                { value: 'nao', label: 'NÃO' }
            ]
        },
        opcoesDocumento() {
            return mapStr(this.listSelects.todos_status_documentos)
        },
        opcoesDocumentoPortaria() {
            return mapStr(this.listSelects.todos_status_documentos_portaria)
        },
        opcoesTipoAdmissao() {
            return mapStr(this.listSelects.tipos_admissao)
        },
        opcoesPrazo() {
            return mapStr(this.listSelects.todos_prazos)
        },
        opcoesTreinamento() {
            return mapStr(this.listSelects.todos_status_treinamentos)
        },
        opcoesTipoTreinamento() {
            return ['COMPLETO', 'PARADA', 'LARGO'].map((v) => ({ value: v, label: v }))
        },
        opcoesStatusCarteira() {
            return mapStr(this.listSelects.status_carteira_treinamento)
        },
        opcoesSegmento() {
            return (this.listSelects.segmentos_treinamento || []).map((s) => ({ value: s.id, label: s.nome }))
        },
        opcoesStatus() {
            return mapStr(this.listSelects.status_admissao)
        },
        filialCombo: {
            get() {
                return this.form.filial ? 'filial' : 'matriz'
            },
            set(v) {
                this.form.filial = v === 'filial'
            }
        },
        biometriaCombo: {
            get() {
                if (this.form.biometria === true || this.form.biometria === 1 || this.form.biometria === 'true') return 'sim'
                if (this.form.biometria === false || this.form.biometria === 0 || this.form.biometria === 'false') return 'nao'
                return ''
            },
            set(v) {
                if (v === 'sim') this.form.biometria = true
                else if (v === 'nao') this.form.biometria = false
                else this.form.biometria = ''
            }
        }
    },
    async created() {
        this.preload = true
        try {
            const [areasRes, selectsRes, ccRes] = await Promise.all([
                axios.get(`${URL_PUBLICO}/lista-areas`),
                axios.get(`${URL_ADMIN}/admissao/listSelects`),
                axios.post(`${URL_PUBLICO}/centro-custos/`, { empresa_id: this.form.empresa_id })
            ])
            this.areasetiquetas = (areasRes.data && areasRes.data.areas) || []
            this.listSelects = selectsRes.data || {}
            this.centro_custos = (ccRes.data && ccRes.data.centro_custos) || []
        } catch (e) {
            console.log(e)
        }
        this.form.centro_custo_id = this.form.centro_custo_id ?? ''
        if (!this.form.ultimo_aso) this.form.ultimo_aso = { data_realizacao: '' }
        if (!this.form.dados_admissoes) {
            this.form.dados_admissoes = {
                ctps_numero: '',
                ctps_serie: '',
                ctps_data_emissao: '',
                titulo_eleitor_numero: '',
                titulo_eleitor_sessao: '',
                titulo_eleitor_zona: '',
                ctps_uf: '',
                cert_reservista_num: '',
                cert_reservista_categoria: ''
            }
        }
        this.preload = false
    },
    methods: {
        fecharOutrosComboboxes() {},
        onSelectCentroCusto() {
            this.limparComboboxInvalido('fa-cc')
            this.form.filial = false
            this.form.centro_custo_filial_id = ''
        },
        onSelectTipoCnpj() {
            this.limparComboboxInvalido('fa-tipo-cnpj')
            this.form.centro_custo_filial_id = ''
        },
        validarCampos() {
            if (this.visualizar || this.disabled) return true

            if (!String(this.form.funcao || '').trim()) {
                const el = document.getElementById('fa-funcao')
                if (el && typeof valida_campo_vazio === 'function') valida_campo_vazio(el, 2)
                if (typeof mostraErro === 'function') mostraErro('', 'Informe a função')
                return false
            }

            if (!this.exigirCombobox(this.form.status, 'fa-status', { toastMsg: 'Selecione o status' })) {
                return false
            }

            if (
                this.form.tipo_admissao === 'FIXO' &&
                !this.exigirCombobox(this.form.prazo_experiencia, 'fa-prazo', {
                    toastMsg: 'Selecione o prazo de experiência'
                })
            ) {
                return false
            }

            if (
                this.verificaStatusAdmitidoProntoAdmissao &&
                !this.exigirCampoData(this.form.data_admissao, 'fa-data-adm', {
                    toastMsg: 'Informe a data da admissão'
                })
            ) {
                return false
            }

            const ferias = this.$refs.feriasAdquiridas
            if (ferias && typeof ferias.validarCampos === 'function' && !ferias.validarCampos()) {
                return false
            }

            return this.validarInputsAtivosVisiveis('form-admissao-modal', {
                preservarIds: ['fa-data-adm', 'fa-aso']
            })
        }
    }
}
</script>

<style scoped></style>
