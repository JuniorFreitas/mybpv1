<template>
    <div class="container-fluid requisicao-vaga-page">
        <modal id="janelaCadastrar" :titulo="tituloJanela" :size="90">
            <template #conteudo>
                <preload v-show="preload" class="text-center"></preload>
                <div class="alert alert-success alert-dismissible" v-show="cadastrado">
                    <h4><i class="icon fa fa-check"></i>Solicitação cadastrada com sucesso!</h4>
                </div>
                <div class="alert alert-success alert-dismissible" v-show="atualizado">
                    <h4><i class="icon fa fa-check"></i>Solicitação alterada com sucesso!</h4>
                </div>
                <form v-if="!preload && !cadastrado && !atualizado" id="form" onsubmit="return false">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Selecione um cargo <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :formsm="false"
                                        :caminho="controle.dados.caminho_autocomplete"
                                        :disabled="visualizar"
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
                                <div class="form-group">
                                    <label>Área <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.area_id"
                                        class="form-control"
                                        :disabled="visualizar"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in areas_etiquetas" :key="item.id" :value="item.id">{{ item.label }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Centro de Custo <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.centro_custo_id"
                                        class="form-control"
                                        :disabled="visualizar"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :key="item.id" :value="item.id">{{ item.label }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Tipo de Contratação <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.tipo_contratacao"
                                        class="form-control"
                                        :disabled="visualizar"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione</option>
                                        <option value="APRENDIZ">APRENDIZ</option>
                                        <option value="FIXO">FIXO</option>
                                        <option value="INTERMITENTE">INTERMITENTE</option>
                                        <option value="PJ">PJ</option>
                                        <option value="ESTÁGIO">ESTÁGIO</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Prioridade <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.prioridade"
                                        class="form-control"
                                        :disabled="visualizar"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione</option>
                                        <option value="ALTA">ALTA</option>
                                        <option value="MÉDIA">MÉDIA</option>
                                        <option value="URGENTE">URGENTE</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Quantidade <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        onblur="valida_campo_vazio(this, 1)"
                                        :disabled="visualizar"
                                        v-mascara:numero
                                        v-model="form.quantidade"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <fieldset>
                                    <legend>DEMAIS INFORMAÇÕES</legend>
                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Posição <span class="text-danger">*</span></label>
                                                <select
                                                    v-model="form.outras_informacoes.posicao"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option value="Efetiva">Efetiva</option>
                                                    <option value="Estágio">Estágio</option>
                                                    <option value="Temporária">Temporária</option>
                                                    <option value="Aumento de Quadro">Aumento de Quadro</option>
                                                    <option value="Substituição">Substituição</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Processo <span class="text-danger">*</span></label>
                                                <select
                                                    v-model="form.outras_informacoes.processo"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option value="Externo">Externo</option>
                                                    <option value="Confidencial">Confidencial</option>
                                                    <option value="Interno">Interno</option>
                                                    <option value="Indicação">Indicação</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4" v-if="form.outras_informacoes.processo === 'Indicação'">
                                            <div class="form-group">
                                                <label>Nome Indicação</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    v-model="form.outras_informacoes.nome_indicacao"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Tipo <span class="text-danger">*</span></label>
                                                <select
                                                    v-model="form.outras_informacoes.contrato"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option value="Admissão">Admissão</option>
                                                    <option value="Readmissão">Readmissão</option>
                                                    <option value="Reintegração">Reintegração</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Horário</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    :disabled="visualizar"
                                                    v-model="form.outras_informacoes.horario"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Gestor da vaga <span class="text-danger">*</span></label>
                                                <autocomplete
                                                    :caminho="'autocomplete/todos-gestores-ativos'"
                                                    :formsm="false"
                                                    :valido="form.outras_informacoes.gestor_id !== ''"
                                                    v-model="form.outras_informacoes.autocomplete_label_gestor"
                                                    placeholder="Digite o nome do(a) gestor(a)"
                                                    :disabled="visualizar"
                                                    :id="`gestor_${hash}`"
                                                    @onblur="resetaCampoGestor"
                                                    @onselect="selecionaGestor"
                                                ></autocomplete>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Cargo está no PPRA e PCMSO? <span class="text-danger">*</span></label>
                                                <select
                                                    v-model="form.outras_informacoes.ppra"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option :value="true">Sim</option>
                                                    <option :value="false">Não</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Salário <span class="text-danger">*</span></label>
                                                <select
                                                    v-model="form.outras_informacoes.salario"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option value="conforme">Conforme o plano de cargo</option>
                                                    <option value="exceção">Exceção</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4" v-if="form.outras_informacoes.salario === 'exceção'">
                                            <div class="form-group">
                                                <label>Salário exceção</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    onblur="valida_dinheiro(this)"
                                                    :disabled="visualizar"
                                                    v-mascara:dinheiro
                                                    v-model="form.outras_informacoes.salario_valor_format"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Benefício <span class="text-danger">*</span></label>
                                                <select
                                                    v-model="form.outras_informacoes.beneficio"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option value="conforme">Conforme o plano da empresa</option>
                                                    <option value="exceção">Exceção</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4" v-if="form.outras_informacoes.beneficio === 'exceção'">
                                            <div class="form-group">
                                                <label>Benefício exceção</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    :disabled="visualizar"
                                                    v-model="form.outras_informacoes.beneficio_excecao"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label>Treinamento</label>
                                                <select
                                                    v-model="form.outras_informacoes.treinamento"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option value="conforme">Conforme o padrão</option>
                                                    <option value="exceção">Exceção</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4" v-if="form.outras_informacoes.treinamento === 'exceção'">
                                            <div class="form-group">
                                                <label>Treinamento exceção</label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    :disabled="visualizar"
                                                    v-model="form.outras_informacoes.treinamento_excecao"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col-12 campos-personalizados-modal" v-if="camposCustom.length > 0">
                                <fieldset>
                                    <legend>Outras informações</legend>
                                    <div class="row">
                                        <div class="col-12 col-md-4" v-for="campo in camposCustom" :key="campo.id">
                                            <div class="form-group" v-if="campo.tipo === 'sim_nao'">
                                                <label>{{ campo.label }}<span class="text-danger" v-if="campo.obrigatorio"> *</span></label>
                                                <select
                                                    v-model="form.custom_values[campo.id]"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onchange="valida_campo_vazio(this, 1)"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                >
                                                    <option value="">Selecione</option>
                                                    <option value="Sim">Sim</option>
                                                    <option value="Não">Não</option>
                                                </select>
                                            </div>
                                            <div class="form-group" v-else-if="campo.tipo === 'texto'">
                                                <label>{{ campo.label }}<span class="text-danger" v-if="campo.obrigatorio"> *</span></label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    :disabled="visualizar"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    v-model="form.custom_values[campo.id]"
                                                    :placeholder="campo.label"
                                                />
                                            </div>
                                            <div class="form-group" v-else-if="campo.tipo === 'textarea'">
                                                <label>{{ campo.label }}<span class="text-danger" v-if="campo.obrigatorio"> *</span></label>
                                                <textarea
                                                    class="form-control"
                                                    rows="3"
                                                    :disabled="visualizar"
                                                    onblur="valida_campo_vazio(this, 1)"
                                                    v-model="form.custom_values[campo.id]"
                                                    :placeholder="campo.label"
                                                ></textarea>
                                            </div>
                                            <div class="form-group" v-else-if="campo.tipo === 'select'">
                                                <label>{{ campo.label }}<span class="text-danger" v-if="campo.obrigatorio"> *</span></label>
                                                <select
                                                    v-model="form.custom_values[campo.id]"
                                                    class="form-control"
                                                    :disabled="visualizar"
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
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Previsão de Ínicio</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" v-model="form.imediata" class="custom-control-input" :disabled="visualizar" id="imediata" />
                                        <label class="custom-control-label" for="imediata">Imediata</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12"></div>
                            <div class="col-12 col-md-4" v-if="!form.imediata">
                                <datepicker label="Previsão" v-model="form.previsao_inicio" :disabled="visualizar"></datepicker>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Solicitante <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        onblur="valida_campo_vazio(this, 1)"
                                        :disabled="visualizar"
                                        v-model="form.solicitante"
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control" v-model="form.observacao" cols="5" rows="5" :disabled="visualizar"></textarea>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="alert alert-warning d-flex align-items-center" v-if="!form.data_aprovacao && !cadastrando">
                        <i class="fas fa-hourglass-half fa-2x mr-3"></i>
                        <div>
                            <strong>Aguardando Aprovação</strong><br />
                            <small>Esta solicitação ainda não foi aprovada ou reprovada pelo gestor.</small>
                        </div>
                    </div>
                    <fieldset v-if="aprovando || (visualizar && form.status_aprovacao)">
                        <legend>Aprovação Gestor</legend>
                        <div class="row">
                            <div v-if="!aprovando && form.user_aprovacao" class="col-12">
                                <legend>{{ form.status_aprovacao }} por: {{ form.user_aprovacao }} em {{ form.data_aprovacao }}</legend>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        :disabled="!aprovando"
                                        v-model="form.obs_aprovacao"
                                        cols="5"
                                        rows="5"
                                    ></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select
                                        :disabled="!aprovando"
                                        v-model="form.status_aprovacao"
                                        class="form-control form-control-sm validacampo"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione...</option>
                                        <option value="aprovado">Aprovar</option>
                                        <option value="reprovado">Reprovar</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="alert alert-warning" v-if="aprovandoExtra && temAprovacaoExtra">
                        Esta solicitação ainda não foi aprovada ou reprovada pela {{ nomeAprovacaoExtra }}!
                    </div>
                    <fieldset v-if="(aprovandoExtra || (visualizar && form.status_aprovacao_extra)) && temAprovacaoExtra">
                        <legend v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</legend>
                        <div class="row" v-if="temAprovacaoExtra">
                            <div v-if="!aprovandoExtra && form.aprovacao_extra_nome" class="col-12">
                                <legend>{{ form.status_aprovacao_extra }} por: {{ form.aprovacao_extra_nome }} em {{ form.data_aprovacao_extra }}</legend>
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
                                <div class="form-group">
                                    <label>Status</label>
                                    <select
                                        :disabled="!aprovandoExtra || aprovandoRh"
                                        v-model="form.status_aprovacao_extra"
                                        class="form-control form-control-sm validacampo"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione...</option>
                                        <option value="aprovado">Aprovar</option>
                                        <option value="reprovado">Reprovar</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="alert alert-warning" v-if="aprovandoRh">Esta solicitação ainda não foi aprovada ou reprovada!</div>
                    <fieldset v-if="aprovandoRh || (visualizar && form.status_aprovacao_rh)">
                        <legend>Aprovação RH</legend>
                        <div class="row">
                            <div v-if="!aprovandoRh && form.rh_aprovacao_nome" class="col-12">
                                <legend>{{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao_nome }} em {{ form.data_aprovacao_rh }}</legend>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea class="form-control form-control-sm" :disabled="!aprovandoRh" v-model="form.obs_rh" cols="5" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select
                                        :disabled="!aprovandoRh"
                                        v-model="form.status_aprovacao_rh"
                                        class="form-control form-control-sm validacampo"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione...</option>
                                        <option value="aprovado">Aprovar</option>
                                        <option value="reprovado">Reprovar</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </template>
            <template #rodape>
                <div>
                    <button type="button" class="btn btn-sm btn-primary" v-show="editando && !preload && !cadastrando && !aprovando" @click.prevent="alterar">
                        <i class="fa fa-edit"></i> Alterar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" v-show="!editando && !preload && cadastrando && !aprovando" @click.prevent="cadastrar">
                        <i class="fa fa-save"></i> Cadastrar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        v-show="aprovando && !editando && !form.data_aprovacao && !cadastrando"
                        @click.prevent="aprovar"
                    >
                        <i class="fa fa-save"></i> Aprovar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" v-show="aprovandoExtra && !preload && !cadastrando" @click.prevent="aprovarExtra">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" v-show="aprovandoRh && !preload && !cadastrando" @click.prevent="aprovarRh">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                </div>
            </template>
        </modal>
        <fieldset>
            <legend>Filtro</legend>
            <form class="row" @submit.prevent="$refs.componente && this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null">
                <date-range-filter
                    v-model:enabled="controle.dados.filtroPeriodo"
                    v-model:start-date="controle.dados.dataInicio"
                    v-model:end-date="controle.dados.dataFim"
                    :disabled="controle.carregando"
                    :id-suffix="hash"
                    wrapper-class="col-12 col-md-3"
                >
                </date-range-filter>

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Pesquisar</label>
                        <input
                            type="text"
                            placeholder="Buscar por cargo"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            :disabled="controle.carregando"
                            v-model="controle.dados.campoBusca"
                        />
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.campoStatus" :disabled="controle.carregando" @change="atualizar()">
                            <option value="">Todos os Status</option>
                            <option value="aberto">Aberto</option>
                            <option value="aprovado">Aprovado</option>
                            <option value="reprovado">Reprovado</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label>Ordenar por</label>
                        <select class="form-control form-control-sm" v-model="controle.dados.ordenacao" :disabled="controle.carregando" @change="atualizar()">
                            <option value="created_at_desc">Mais Recentes</option>
                            <option value="created_at_asc">Mais Antigos</option>
                            <option value="updated_at_desc">Última Modificação</option>
                        </select>
                    </div>
                </div>

                <div class="col-12"></div>

                <div class="col-12 col-md-9">
                    <button type="button" class="btn btn-sm btn-success" :disabled="controle.carregando" @click="atualizar">
                        <i :class="controle.carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        data-toggle="modal"
                        data-target="#janelaCadastrar"
                        :disabled="controle.carregando"
                        @click.prevent="formNovo"
                    >
                        Solicitar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary mr-1"
                        @click.prevent="exportaExcel()"
                        :disabled="controle.carregando || preloadExportacao || (!controle.carregando && !lista.length)"
                    >
                        <i class="fas fa-file-excel"></i> EXPORTAR EXCEL
                    </button>
                </div>
            </form>
        </fieldset>
        <preload class="text-center" v-if="controle.carregando"></preload>
        <div id="conteudo">
            <div class="alert alert-warning" v-show="!controle.carregando && lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>
            <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="solicitacao-card" v-for="item in lista" :key="item.id">
                    <div class="card-header-row">
                        <div class="card-left">
                            <span class="badge-id">#{{ item.id }}</span>
                            <div class="colaborador-principal">
                                <i class="fas fa-briefcase text-primary mr-1"></i>
                                <strong>{{ item.cargo.nome }}</strong>
                            </div>
                            <div class="data-info ml-3">
                                <i class="fas fa-calendar-plus text-muted" style="font-size: 0.75rem"></i>
                                <small class="text-muted">{{ item.data_solicitacao || item.created_at_br }}</small>
                            </div>
                        </div>
                        <div class="card-right">
                            <span
                                class="status-badge"
                                :class="{
                                    'status-reprovado':
                                        item.status_aprovacao === 'reprovado' ||
                                        item.status_aprovacao_extra === 'reprovado' ||
                                        item.status_aprovacao_rh === 'reprovado',
                                    'status-aprovado': item.status_aprovacao_rh === 'aprovado',
                                    'status-aprovado-extra': temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado' && !item.status_aprovacao_rh,
                                    'status-aprovado-gestor':
                                        item.status_aprovacao === 'aprovado' &&
                                        (!temAprovacaoExtra || !item.status_aprovacao_extra) &&
                                        !item.status_aprovacao_rh,
                                    'status-pendente': !item.status_aprovacao
                                }"
                            >
                                <span
                                    v-if="
                                        item.status_aprovacao === 'reprovado' ||
                                        item.status_aprovacao_extra === 'reprovado' ||
                                        item.status_aprovacao_rh === 'reprovado'
                                    "
                                >
                                    <i class="fas fa-times-circle"></i> REPROVADO
                                </span>
                                <span v-else-if="item.status_aprovacao_rh === 'aprovado'"> <i class="fas fa-check-circle"></i> APROVADO RH </span>
                                <span v-else-if="temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO {{ (nomeAprovacaoExtra || 'Extra').toUpperCase() }}
                                </span>
                                <span v-else-if="item.status_aprovacao === 'aprovado'"> <i class="fas fa-check-circle"></i> APROVADO GESTOR </span>
                                <span v-else><i class="fas fa-clock"></i> EM ABERTO</span>
                            </span>
                            <div class="dropdown show">
                                <a
                                    class="btn-actions-compact"
                                    href="#"
                                    role="button"
                                    :id="`dropdownMenuLink_${item.id}`"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-custom dropdown-menu-right" :aria-labelledby="`dropdownMenuLink_${item.id}`">
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação Gestor"
                                        data-toggle="modal"
                                        data-target="#janelaCadastrar"
                                        @click.prevent="formOpen(item.id); visualizar = true; aprovando = true; aprovandoExtra = false; aprovandoRh = false; editando = false; cadastrando = false"
                                        v-if="!item.data_aprovacao && aprovaGestor"
                                        >Aprovação Gestor</a
                                    >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra"
                                        data-toggle="modal"
                                        data-target="#janelaCadastrar"
                                        @click.prevent="formOpen(item.id); visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; editando = false; cadastrando = false"
                                        v-if="temAprovacaoExtra && item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra && aprovaExtra"
                                        >{{ nomeAprovacaoExtra }}</a
                                    >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        data-toggle="modal"
                                        data-target="#janelaCadastrar"
                                        @click.prevent="formOpen(item.id); visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; editando = false; cadastrando = false"
                                        v-if="
                                            ((item.status_aprovacao === 'aprovado' && !temAprovacaoExtra) || item.status_aprovacao_extra === 'aprovado') &&
                                            !item.rh_aprovacao_id &&
                                            aprovaRh
                                        "
                                        >Aprovação RH</a
                                    >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        data-toggle="modal"
                                        data-target="#janelaCadastrar"
                                        @click.prevent="formOpen(item.id); visualizar = true; editando = false; aprovando = false; aprovandoExtra = false; aprovandoRh = false; cadastrando = false"
                                        >Visualizar</a
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-hashtag text-muted"></i>
                            <span class="detail-label">Quantidade:</span>
                            <span class="detail-value">{{ item.quantidade }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-user text-muted"></i>
                            <span class="detail-label">Solicitante:</span>
                            <span class="detail-value">{{ item.solicitante }}</span>
                        </div>
                        <div class="detail-item" v-if="item.OutrasInformacoes && item.OutrasInformacoes.GestorAprovacao">
                            <i class="fas fa-user-tie text-muted"></i>
                            <span class="detail-label">Gestor:</span>
                            <span class="detail-value">{{ item.OutrasInformacoes.GestorAprovacao.nome }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt text-muted"></i>
                            <span class="detail-label">Solicitação:</span>
                            <span class="detail-value">{{ item.data_solicitacao }}</span>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-building text-muted"></i>
                            <span class="detail-label">Centro Custo:</span>
                            <span class="detail-value">{{ item.centro_custo.label }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-tag text-muted"></i>
                            <span class="detail-label">Área:</span>
                            <span class="detail-value">{{ (item.area && item.area.label) || '-' }}</span>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-file-contract text-muted"></i>
                            <span class="detail-label">Contratação:</span>
                            <span class="detail-value">{{ item.tipo_contratacao }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-exclamation-triangle text-muted"></i>
                            <span class="detail-label">Prioridade:</span>
                            <span class="detail-value">{{ item.prioridade }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock text-muted"></i>
                            <span class="detail-label">Início:</span>
                            <span class="detail-value" v-if="item.imediata">Imediata</span>
                            <span class="detail-value" v-else>{{ item.previsao_inicio }}</span>
                        </div>
                    </div>
                    <div class="card-aprovacao-row">
                        <div class="fluxo-icons">
                            <div class="fluxo-step">
                                <i class="fas fa-check-circle text-success"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Solicitante</small>
                                    <small class="fluxo-aprovador text-success">{{ item.solicitante || 'Sem informação' }}</small>
                                    <small class="fluxo-data">{{ item.created_at_br }}</small>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted mx-2"></i>
                            <div class="fluxo-step">
                                <i v-if="item.status_aprovacao === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i v-else class="fas fa-clock text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Gestor</small>
                                    <small v-if="item.status_aprovacao === 'aprovado'" class="fluxo-aprovador text-success">{{
                                        item.user_aprovacao ? item.user_aprovacao.nome : ''
                                    }}</small>
                                    <small v-else-if="item.status_aprovacao === 'reprovado'" class="fluxo-aprovador text-danger">{{
                                        item.user_aprovacao ? item.user_aprovacao.nome : ''
                                    }}</small>
                                    <small v-else class="fluxo-status text-warning">Aguardando</small>
                                    <small v-if="item.data_aprovacao_br" class="fluxo-data">{{ item.data_aprovacao_br }}</small>
                                </div>
                            </div>
                            <template v-if="temAprovacaoExtra">
                                <i class="fas fa-chevron-right text-muted mx-2"></i>
                                <div class="fluxo-step">
                                    <i v-if="item.status_aprovacao === 'reprovado'" class="fas fa-ban text-secondary"></i>
                                    <i v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                    <i v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                    <i v-else-if="item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra" class="fas fa-clock text-warning"></i>
                                    <i v-else class="fas fa-circle text-muted"></i>
                                    <div class="fluxo-info">
                                        <small class="fluxo-etapa">{{ nomeAprovacaoExtra }}</small>
                                        <small v-if="item.status_aprovacao === 'reprovado'" class="fluxo-status text-secondary">Cancelada por reprovação</small>
                                        <small v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fluxo-aprovador text-success">{{
                                            item.aprovacao_extra_nome
                                        }}</small>
                                        <small v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fluxo-aprovador text-danger">{{
                                            item.aprovacao_extra_nome
                                        }}</small>
                                        <small v-else-if="item.status_aprovacao === 'aprovado'" class="fluxo-status text-warning">Aguardando</small>
                                        <small v-else class="fluxo-status">Pendente</small>
                                        <small v-if="item.data_aprovacao_extra" class="fluxo-data">{{ item.data_aprovacao_extra }}</small>
                                    </div>
                                </div>
                            </template>
                            <i class="fas fa-chevron-right text-muted mx-2" v-if="temAprovacaoExtra"></i>
                            <div class="fluxo-step">
                                <i
                                    v-if="item.status_aprovacao === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')"
                                    class="fas fa-ban text-secondary"
                                ></i>
                                <i v-else-if="item.status_aprovacao_rh === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao_rh === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i
                                    v-else-if="
                                        (temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                        (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')
                                    "
                                    class="fas fa-clock text-warning"
                                ></i>
                                <i v-else class="fas fa-circle text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">RH</small>
                                    <small
                                        v-if="item.status_aprovacao === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')"
                                        class="fluxo-status text-secondary"
                                        >Cancelada por reprovação</small
                                    >
                                    <small v-else-if="item.status_aprovacao_rh === 'aprovado'" class="fluxo-aprovador text-success">{{
                                        item.rh_aprovacao_nome
                                    }}</small>
                                    <small v-else-if="item.status_aprovacao_rh === 'reprovado'" class="fluxo-aprovador text-danger">{{
                                        item.rh_aprovacao_nome
                                    }}</small>
                                    <small
                                        v-else-if="
                                            (temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                            (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')
                                        "
                                        class="fluxo-status text-warning"
                                        >Aguardando</small
                                    >
                                    <small v-else class="fluxo-status">Pendente</small>
                                    <small v-if="item.data_aprovacao_rh_br || item.data_aprovacao_rh" class="fluxo-data">{{
                                        item.data_aprovacao_rh_br || item.data_aprovacao_rh
                                    }}</small>
                                </div>
                            </div>
                        </div>
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

const _ = window._ || { cloneDeep: (x) => JSON.parse(JSON.stringify(x)) }

export default {
    name: 'RequisicaoVaga',
    props: {
        urlAtualizar: { type: String, default: '' }
    },
    components: {
        datepicker,
        DateRangeFilter
    },
    mixins: [ExportacaoMixin],
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
            centro_custos: [],
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
                    campoStatus: '',
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
                campoStatus: this.controle.dados.campoStatus,
                filtroPeriodo: this.controle.dados.filtroPeriodo,
                dataInicio: this.controle.dados.dataInicio,
                dataFim: this.controle.dados.dataFim,
                periodo: this.controle.dados.periodo,
                ordenacao: this.controle.dados.ordenacao
            }
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
    },
    methods: {
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
            this.listaCentroCusto()
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
                    this.listaCentroCusto()
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
        cadastrar() {
            if (this.form.cargo_id === '') {
                if (typeof mostraErro === 'function') mostraErro('', 'Campo CARGO não pode ficar vazio')
                this.resetaCampoVagaModal()
                return false
            }
            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                if (typeof mostraErro === 'function') mostraErro('', 'Verifique os campos marcados')
                return false
            }
            if (!this.validarCamposCustomObrigatorios()) return false
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .post(`${base}/planejamento/requisicao-vaga/`, this.form)
                .then(() => {
                    if (typeof mostraSucesso === 'function') mostraSucesso('', 'Solicitação registrada com sucesso!')
                    $('#janelaCadastrar').modal('hide')
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        alterar() {
            if (this.form.cargo_id === '') {
                if (typeof mostraErro === 'function') mostraErro('', 'Campo CARGO não pode ficar vazio')
                this.resetaCampoVagaModal()
                return false
            }
            $('#janelaCadastrar :input:visible').trigger('blur')
            $('#janelaCadastrar .campos-personalizados-modal :input').removeClass('is-invalid')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                if (typeof mostraErro === 'function') mostraErro('', 'Verifique os campos marcados')
                return false
            }
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .put(`${base}/planejamento/requisicao-vaga/${this.form.id}`, this.form)
                .then(() => {
                    if (typeof mostraSucesso === 'function') mostraSucesso('', 'Solicitação alterada com sucesso!')
                    $('#janelaCadastrar').modal('hide')
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        aprovar() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            $('#janelaCadastrar .campos-personalizados-modal :input').removeClass('is-invalid')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                if (typeof mostraErro === 'function') mostraErro('', 'Verifique os campos marcados')
                return false
            }
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .put(`${base}/planejamento/requisicao-vaga/${this.form.id}/aprovar`, this.form)
                .then(() => {
                    if (typeof mostraSucesso === 'function') mostraSucesso('', 'Registro salvo com sucesso!')
                    $('#janelaCadastrar').modal('hide')
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        aprovarExtra() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            $('#janelaCadastrar .campos-personalizados-modal :input').removeClass('is-invalid')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                if (typeof mostraErro === 'function') mostraErro('', 'Verifique os campos marcados')
                return false
            }
            this.preload = true
            const base = typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : ''
            axios
                .put(`${base}/planejamento/requisicao-vaga/${this.form.id}/aprovarextra`, this.form)
                .then(() => {
                    if (typeof mostraSucesso === 'function') mostraSucesso('', 'Registro salvo com sucesso!')
                    $('#janelaCadastrar').modal('hide')
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        aprovarRh() {
            $('#janelaCadastrar :input:visible').trigger('blur')
            $('#janelaCadastrar .campos-personalizados-modal :input').removeClass('is-invalid')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                if (typeof mostraErro === 'function') mostraErro('', 'Verifique os campos marcados')
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
                    $('#janelaCadastrar').modal('hide')
                    if (this.$refs.componente && typeof this.$refs.componente.buscar === 'function') this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
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
        listaCentroCusto() {
            const base = typeof URL_PUBLICO !== 'undefined' ? URL_PUBLICO : ''
            axios
                .post(`${base}/centro-custos/`, { empresa_id: this.form.empresa_id })
                .then((res) => {
                    this.centro_custos = res.data.centro_custos || []
                })
                .catch(() => {
                    this.preload = false
                })
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
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.syncUrlFiltros()
            if (this.$refs.componente) {
                this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
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
            if (urlParams.get('campoStatus')) this.controle.dados.campoStatus = urlParams.get('campoStatus')
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
            if (d.campoStatus) params.campoStatus = d.campoStatus
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
/* Container de Cards - mesmo padrão Demissão Prevista */
.requisicao-vaga-page .cards-lista {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.requisicao-vaga-page .solicitacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.requisicao-vaga-page .solicitacao-card:hover {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    border-color: #007bff;
    transform: translateY(-2px);
}

.requisicao-vaga-page .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.requisicao-vaga-page .card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
}

.requisicao-vaga-page .card-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.requisicao-vaga-page .badge-id {
    background: #174257;
    color: white;
    padding: 0.25rem 0.625rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.75rem;
    white-space: nowrap;
    flex-shrink: 0;
}

.requisicao-vaga-page .colaborador-principal {
    display: flex;
    align-items: center;
    font-size: 0.938rem;
    color: #212529;
    overflow: hidden;
}

.requisicao-vaga-page .colaborador-principal strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.requisicao-vaga-page .data-info {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    flex-shrink: 0;
}

.requisicao-vaga-page .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.requisicao-vaga-page .status-reprovado {
    background: #dc3545;
    color: white;
}
.requisicao-vaga-page .status-aprovado {
    background: #28a745;
    color: white;
}
.requisicao-vaga-page .status-aprovado-extra {
    background: #17a2b8;
    color: white;
}
.requisicao-vaga-page .status-aprovado-gestor {
    background: #ffc107;
    color: #212529;
}
.requisicao-vaga-page .status-pendente {
    background: #e9ecef;
    color: #495057;
}

.requisicao-vaga-page .btn-actions-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #495057;
    transition: all 0.2s ease;
    text-decoration: none;
    flex-shrink: 0;
}

.requisicao-vaga-page .btn-actions-compact:hover {
    background: #007bff;
    border-color: #007bff;
    color: white;
    transform: rotate(90deg);
}

.requisicao-vaga-page .card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.requisicao-vaga-page .detail-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.813rem;
    min-width: 0;
}

.requisicao-vaga-page .detail-item i {
    flex-shrink: 0;
    font-size: 0.875rem;
}

.requisicao-vaga-page .detail-label {
    font-weight: 500;
    color: #6c757d;
    white-space: nowrap;
}
.requisicao-vaga-page .detail-value {
    color: #212529;
    font-weight: 400;
}

.requisicao-vaga-page .card-aprovacao-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.requisicao-vaga-page .fluxo-icons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    flex: 1;
}

.requisicao-vaga-page .fluxo-step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.requisicao-vaga-page .fluxo-step i {
    font-size: 1.125rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

.requisicao-vaga-page .fluxo-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    min-width: 0;
}

.requisicao-vaga-page .fluxo-etapa {
    font-size: 0.688rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.requisicao-vaga-page .fluxo-aprovador {
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.requisicao-vaga-page .fluxo-status {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6c757d;
}

.requisicao-vaga-page .fluxo-data {
    font-size: 0.688rem;
    color: #6c757d;
    white-space: nowrap;
}

.requisicao-vaga-page .dropdown-menu-custom {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
    padding: 0.5rem 0;
}

.requisicao-vaga-page .dropdown-menu-custom .dropdown-item {
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.requisicao-vaga-page .dropdown-menu-custom .dropdown-item:hover {
    background: #f8f9fa;
    color: #007bff;
    padding-left: 1.5rem;
}

@media (max-width: 768px) {
    .requisicao-vaga-page .card-header-row {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .requisicao-vaga-page .card-left {
        width: 100%;
    }
    .requisicao-vaga-page .card-right {
        width: 100%;
        justify-content: space-between;
    }
    .requisicao-vaga-page .card-details-row {
        flex-direction: column;
        gap: 0.5rem;
    }
    .requisicao-vaga-page .card-aprovacao-row {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
