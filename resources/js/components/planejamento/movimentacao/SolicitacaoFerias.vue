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
                <form v-if="!preload" :id="`${hash}`" onsubmit="return false">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <colaborador
                                label="Colaborador *"
                                tipo="ferias"
                                @evtseleciona="dataAdmissao"
                                @evtreseta="dataAdmissao"
                                :model="form"
                                :verifica="visualizar || aprovando || aprovandoRh || editando"
                                :hash="hash"
                            ></colaborador>

                            <div class="col-12 col-md-4" v-if="form.colaborador_id !== ''">
                                <div class="form-group">
                                    <label>Data de Admissão</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.data_admissao"
                                        readonly="readonly"
                                        disabled="disabled"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Centro de Custo <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.centro_custo_id"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id" :key="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>

                                    <!--                                    <select2 :settings="settings2" :options="centro_custos" :disabled="controle.carregando"-->
                                    <!--                                             v-model="form.centro_custo_id"></select2>-->
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Período Aquisitivo <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.periodo_aquisitivo_id"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="periodo in periodos" :value="periodo.id" :key="periodo.id">{{ periodo.label }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="ultimaData !== ''">
                                <div class="form-group">
                                    <label>Última Data</label>
                                    <input type="text" class="form-control form-control-sm" v-model="ultimaData" readonly="readonly" disabled="disabled" />
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Tem Falta?</label>
                                <select
                                    class="form-control form-control-sm"
                                    v-model="form.tem_faltas"
                                    :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    @change.prevent="verificaFaltas()"
                                >
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4" v-if="form.tem_faltas === true">
                                <label>Quantidade de faltas</label>
                                <select
                                    class="form-control form-control-sm"
                                    v-model="form.qnt_faltas"
                                    :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    @change.prevent="form.qnt_dias = 5"
                                >
                                    <option v-for="(cont, index) in 32" :value="cont" v-show="cont >= 1" :key="index">{{ cont }}</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4" v-if="!aprovando">
                                <label>Quantidade de dias disponíveis</label>
                                <input type="text" class="form-control form-control-sm" v-model="qntDias" readonly="readonly" disabled="disabled" />
                            </div>

                            <div class="col-12 col-md-4 mb-3">
                                <label>Dias de férias: <span class="text-danger">*</span></label>
                                <select
                                    class="form-control form-control-sm"
                                    v-model="form.qnt_dias"
                                    :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                >
                                    <option v-for="(cont, index) in qntDias" :value="cont" v-show="cont >= 5" :key="index">
                                        {{ cont }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Data da saída <span class="text-danger">*</span></label>
                                <datepicker
                                    label=""
                                    formsm
                                    class="corrigiDatepicker"
                                    v-model="form.data_saida"
                                    :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                ></datepicker>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Data do retorno</label>
                                <input type="text" class="form-control form-control-sm" v-model="dataRetorno" readonly="readonly" disabled="disabled" />
                            </div>

                            <div class="col-12 col-md-4 mb-3" v-if="!aprovando">
                                <label>Dias de saldo</label>
                                <input type="text" class="form-control form-control-sm" v-model="qntSaldo" readonly="readonly" disabled="disabled" />
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Abono Pecuniário</label>
                                <select
                                    class="form-control form-control-sm"
                                    v-model="form.abono_pecuniario"
                                    :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                >
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 mb-3">
                                <label>Adiantamento Décimo Terceiros</label>
                                <select
                                    class="form-control form-control-sm"
                                    v-model="form.adiantamento_decimo_terceiro"
                                    :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                >
                                    <option :value="true">Sim</option>
                                    <option :value="false">Não</option>
                                </select>
                            </div>

                            <gestoraprovacao label="Gestor Aprovação *" formsm :model="form" :verifica="visualizar || aprovando" :hash="hash"></gestoraprovacao>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.obs_solicitante"
                                        cols="5"
                                        rows="5"
                                        :disabled="visualizar || aprovandoRh || aprovandoExtra || aprovando"
                                    ></textarea>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 mt-4 mb-4" v-if="visualizar">
                                <legend>Solicitação feita por: {{ form.solicitante || '' }} {{ form.data_solicitacao }}</legend>
                            </div>
                        </div>

                        <div class="alert alert-warning" v-if="!form.data_aprovacao_gestor && !cadastrando">
                            Esta solicitação ainda não foi aprovada ou reprovada pelo gestor!
                        </div>

                        <fieldset v-if="visualizar || aprovando">
                            <legend>Aprovação Gestor</legend>
                            <div class="row">
                                <div v-if="!aprovando && form.gestor_aprovacao" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_gestor }} por: {{ form.gestor_aprovacao.nome }} em {{ form.data_aprovacao_gestor }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovando || aprovandoExtra || aprovandoRh"
                                            v-model="form.obs_gestor"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select
                                            :disabled="!aprovando || aprovandoExtra || aprovandoRh"
                                            v-model="form.status_aprovacao_gestor"
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

                        <div class="alert alert-warning" v-if="aprovandoExtra">
                            Esta solicitação ainda não foi aprovada ou reprovada pela {{ nomeAprovacaoExtra }}!
                        </div>

                        <fieldset v-if="visualizar || aprovandoExtra">
                            <div v-if="!temAprovacaoExtra" class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Esta empresa não possui aprovação extra configurada.
                            </div>

                            <legend v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</legend>
                            <div class="row" v-if="temAprovacaoExtra">
                                <div v-if="!aprovandoExtra && form.aprovacao_extra" class="col-12">
                                    <legend>{{ form.status_aprovacao_extra }} por: {{ form.aprovacao_extra.nome }} em {{ form.data_aprovacao_extra }}</legend>
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

                        <fieldset v-if="visualizar || aprovandoRh">
                            <legend>Aprovação RH</legend>
                            <div class="row">
                                <div v-if="!aprovandoRh && form.rh_aprovacao" class="col-12">
                                    <legend>{{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao.nome }} em {{ form.data_aprovacao_rh }}</legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovandoRh"
                                            v-model="form.obs_rh"
                                            cols="5"
                                            rows="5"
                                        ></textarea>
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

                        <fieldset>
                            <legend>Anexos</legend>
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
                        </fieldset>
                    </fieldset>
                </form>
            </template>
            <template #rodape>
                <div v-show="cadastrando">
                    <button type="button" class="btn btn-sm mr-1 btn-primary" v-show="!preload" @click.prevent="cadastrar">
                        <i class="fa fa-save"></i> Cadastrar
                    </button>
                </div>
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

            <!-- Cards Compactos -->
            <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="solicitacao-card" v-for="item in lista" :key="item.id">
                    <!-- Cabeçalho do Card -->
                    <div class="card-header-row">
                        <div class="card-left">
                            <!-- <label :for="item.id" class="checkbox-inline">
                                <input
                                    type="checkbox"
                                    class="custom-checkbox"
                                    v-model="selecionados"
                                    :value="item.id"
                                    :id="item.id"
                                    :style="!item.status_aprovacao_gestor ? 'cursor:pointer' : 'cursor: not-allowed'"
                                    :title="item.status_aprovacao_gestor ? null : 'Não possui aprovação'"
                                    v-if="!item.status_aprovacao_gestor"
                                />
                                <input type="checkbox" class="custom-checkbox" v-else disabled="disabled" title="Status já atualizado"/>
                            </label> -->
                            <span class="badge-id">#{{ item.id }}</span>
                            <div class="colaborador-principal">
                                <i class="fas fa-user-circle text-primary mr-1"></i>
                                <strong>{{ item.admissao.feedback.curriculo.nome }}</strong>
                            </div>
                            <div class="data-info ml-3">
                                <i class="fas fa-calendar-plus text-muted" style="font-size: 0.75rem"></i>
                                <small class="text-muted">{{ item.data_solicitacao }}</small>
                                <span v-if="item.updated_at && item.updated_at !== item.data_solicitacao" class="mx-2 text-muted">|</span>
                                <template v-if="item.updated_at && item.updated_at !== item.data_solicitacao">
                                    <i class="fas fa-calendar-check text-info" style="font-size: 0.75rem"></i>
                                    <small class="text-info">{{ item.updated_at }}</small>
                                </template>
                            </div>
                        </div>
                        <div class="card-right">
                            <span
                                class="status-badge"
                                :class="{
                                    'status-reprovado':
                                        item.status_aprovacao_gestor === 'reprovado' ||
                                        item.status_aprovacao_extra === 'reprovado' ||
                                        item.status_aprovacao_rh === 'reprovado',
                                    'status-aprovado': item.status_aprovacao_rh === 'aprovado',
                                    'status-aprovado-extra': temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado' && !item.status_aprovacao_rh,
                                    'status-aprovado-gestor':
                                        item.status_aprovacao_gestor === 'aprovado' &&
                                        (!temAprovacaoExtra || !item.status_aprovacao_extra) &&
                                        !item.status_aprovacao_rh,
                                    'status-pendente': !item.status_aprovacao_gestor
                                }"
                            >
                                <span
                                    v-if="
                                        item.status_aprovacao_gestor === 'reprovado' ||
                                        item.status_aprovacao_extra === 'reprovado' ||
                                        item.status_aprovacao_rh === 'reprovado'
                                    "
                                >
                                    <i class="fas fa-times-circle"></i> REPROVADO
                                </span>
                                <span v-else-if="item.status_aprovacao_rh === 'aprovado'"> <i class="fas fa-check-circle"></i> APROVADO RH </span>
                                <span v-else-if="temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado'">
                                    <i class="fas fa-check-circle"></i> APROVADO {{ nomeAprovacaoExtra.toUpperCase() }}
                                </span>
                                <span v-else-if="item.status_aprovacao_gestor === 'aprovado'"> <i class="fas fa-check-circle"></i> APROVADO GESTOR </span>
                                <span v-else> <i class="fas fa-clock"></i> EM ABERTO </span>
                            </span>
                            <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                                <a
                                    class="btn-actions-compact"
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
                                    class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
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
                                        Aprovação Gestor
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
                                        {{ nomeAprovacaoExtra || 'Aprovação Extra' }}
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
                                        Aprovação Rh
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Editar"
                                        @click.prevent="formOpen(item.id); visualizar = false; aprovando = false; aprovandoExtra = false; aprovandoRh = false; editando = true; podeanexar = true; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="item.gestor_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor && permissoes.update"
                                    >
                                        Editar
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Apagar"
                                        @click.prevent="formApagarFerias(item.id); $refs.modal_janelaApagarFerias && $refs.modal_janelaApagarFerias.abrirModal()"
                                        v-if="item.gestor_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor && permissoes.delete"
                                    >
                                        Apagar
                                    </a>

                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="formOpen(item.id); visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = false; editando = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                    >
                                        Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes do Card -->
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-building text-muted"></i>
                            <span class="detail-label">Centro:</span>
                            <span class="detail-value">{{ item.admissao.centro_custo ? item.admissao.centro_custo.label : '' }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                            <span class="detail-label">Lotação:</span>
                            <span class="detail-value">{{ item.lotacao || 'Não informado' }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt text-primary"></i>
                            <span class="detail-label">Admissão:</span>
                            <span class="detail-value">{{ item.admissao.data_admissao }}</span>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-umbrella-beach text-info"></i>
                            <span class="detail-label">Férias:</span>
                            <span class="detail-value text-info font-weight-bold">{{ item.data_saida }} até {{ item.data_retorno }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock text-warning"></i>
                            <span class="detail-label">Dias:</span>
                            <span class="detail-value">{{ item.qnt_dias }} (Saldo: {{ item.dias_saldo }})</span>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-calendar-check text-success"></i>
                            <span class="detail-label">Período:</span>
                            <span class="detail-value">{{ item.periodo_aquisitivo.label }} - Limite: {{ item.ultima_data }}</span>
                        </div>
                    </div>

                    <!-- Fluxo de Aprovação -->
                    <div class="card-aprovacao-row">
                        <div class="fluxo-icons">
                            <div class="fluxo-step">
                                <i class="fas fa-check-circle text-success"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Solicitante</small>
                                    <small class="fluxo-aprovador text-success">
                                        {{ item.solicitante.nome }}
                                    </small>
                                    <small class="fluxo-data">{{ item.data_solicitacao }}</small>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted mx-2"></i>
                            <!-- Gestor -->
                            <div class="fluxo-step">
                                <i v-if="item.status_aprovacao_gestor === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao_gestor === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i v-else class="fas fa-clock text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Gestor</small>
                                    <small v-if="item.status_aprovacao_gestor === 'aprovado'" class="fluxo-aprovador text-success">
                                        {{ item.gestor_aprovacao ? item.gestor_aprovacao.nome : '' }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_gestor === 'reprovado'" class="fluxo-aprovador text-danger">
                                        {{ item.gestor_aprovacao ? item.gestor_aprovacao.nome : '' }}
                                    </small>
                                    <small v-else class="fluxo-status text-warning">Aguardando</small>
                                    <small v-if="item.data_aprovacao_gestor" class="fluxo-data">{{ item.data_aprovacao_gestor }}</small>
                                </div>
                            </div>

                            <i class="fas fa-chevron-right text-muted mx-2"></i>

                            <!-- Aprovação Extra (se configurada) -->
                            <div class="fluxo-step" v-if="temAprovacaoExtra">
                                <i v-if="item.status_aprovacao_gestor === 'reprovado'" class="fas fa-ban text-secondary"></i>
                                <i v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i
                                    v-else-if="item.status_aprovacao_gestor === 'aprovado' && !item.status_aprovacao_extra"
                                    class="fas fa-clock text-warning"
                                ></i>
                                <i v-else class="fas fa-circle text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">{{ nomeAprovacaoExtra }}</small>
                                    <small v-if="item.status_aprovacao_gestor === 'reprovado'" class="fluxo-status text-secondary">
                                        Cancelada por reprovação
                                    </small>
                                    <small v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fluxo-aprovador text-success">
                                        {{ item.aprovacao_extra_nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fluxo-aprovador text-danger">
                                        {{ item.aprovacao_extra_nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_gestor === 'aprovado'" class="fluxo-status text-warning"> Aguardando </small>
                                    <small v-else class="fluxo-status">Pendente</small>
                                    <small v-if="item.data_aprovacao_extra" class="fluxo-data">{{ item.data_aprovacao_extra }}</small>
                                </div>
                            </div>

                            <i class="fas fa-chevron-right text-muted mx-2" v-if="temAprovacaoExtra"></i>

                            <!-- RH -->
                            <div class="fluxo-step">
                                <i
                                    v-if="item.status_aprovacao_gestor === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')"
                                    class="fas fa-ban text-secondary"
                                ></i>
                                <i v-else-if="item.status_aprovacao_rh === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao_rh === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i
                                    v-else-if="
                                        (temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                        (!temAprovacaoExtra && item.status_aprovacao_gestor === 'aprovado')
                                    "
                                    class="fas fa-clock text-warning"
                                ></i>
                                <i v-else class="fas fa-circle text-muted"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">RH</small>
                                    <small
                                        v-if="
                                            item.status_aprovacao_gestor === 'reprovado' || (temAprovacaoExtra && item.status_aprovacao_extra === 'reprovado')
                                        "
                                        class="fluxo-status text-secondary"
                                    >
                                        Cancelada por reprovação
                                    </small>
                                    <small v-else-if="item.status_aprovacao_rh === 'aprovado'" class="fluxo-aprovador text-success">
                                        {{ item.rh_aprovacao ? item.rh_aprovacao.nome : '' }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_rh === 'reprovado'" class="fluxo-aprovador text-danger">
                                        {{ item.rh_aprovacao ? item.rh_aprovacao.nome : '' }}
                                    </small>
                                    <small
                                        v-else-if="
                                            (temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                            (!temAprovacaoExtra && item.status_aprovacao_gestor === 'aprovado')
                                        "
                                        class="fluxo-status text-warning"
                                    >
                                        Aguardando
                                    </small>
                                    <small v-else class="fluxo-status">Pendente</small>
                                    <small v-if="item.data_aprovacao_rh" class="fluxo-data">{{ item.data_aprovacao_rh }}</small>
                                </div>
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
        FiltroListagem
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
            if (urlParams.get('campoCnpj')) this.controle.dados.campoCnpj = urlParams.get('campoCnpj')
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
                            this.form.periodo_aquisitivo_id = response.data.periodo.id
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
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista`, this.form)
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
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/ferias-prevista/${this.form.id}`, this.form)
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
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
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
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
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
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
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
                if (!this.temFilial && dados.cc.cnpjs) {
                    const keys = Object.keys(dados.cc.cnpjs)
                    if (keys.length && !this.controle.dados.campoCnpj) {
                        this.controle.dados.campoCnpj = keys[0]
                    }
                }
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
            if (this.temFilial) {
                d.campoCnpj = ''
            } else if (this.lista_ccs && this.lista_ccs.cnpjs) {
                const keys = Object.keys(this.lista_ccs.cnpjs)
                d.campoCnpj = keys[0] || ''
            } else {
                d.campoCnpj = ''
            }
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
                'ferias-pages': 'comboFiltroPages'
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
.ferias-filtro-hint {
    margin-left: 0.35rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #0d6efd;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

/* Checkbox Geral */
.checkbox-geral-container {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
}

.checkbox-geral-label {
    margin: 0;
    display: flex;
    align-items: center;
    font-weight: 500;
    color: #495057;
    cursor: pointer;
}

/* Container de Cards */
.cards-lista {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Card Individual */
.solicitacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.solicitacao-card:hover {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    border-color: #007bff;
    transform: translateY(-2px);
}

/* Header do Card */
.card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
}

.card-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.checkbox-inline {
    margin: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.custom-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #174257;
}

.badge-id {
    background: #174257;
    color: white;
    padding: 0.25rem 0.625rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.75rem;
    white-space: nowrap;
    flex-shrink: 0;
}

.colaborador-principal {
    display: flex;
    align-items: center;
    font-size: 0.938rem;
    color: #212529;
    overflow: hidden;
}

.colaborador-principal strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.data-badge {
    display: flex;
    align-items: center;
    background: #fff5f5;
    color: #dc3545;
    padding: 0.375rem 0.625rem;
    border-radius: 6px;
    border-left: 3px solid #dc3545;
    font-weight: 500;
    font-size: 0.813rem;
    white-space: nowrap;
}

.status-badge {
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

.status-reprovado {
    background: #dc3545;
    color: white;
}

.status-aprovado {
    background: #28a745;
    color: white;
}

.status-aprovado-extra {
    background: #17a2b8;
    color: white;
}

.status-aprovado-gestor {
    background: #ffc107;
    color: #212529;
}

.status-pendente {
    background: #e9ecef;
    color: #495057;
}

.btn-actions-compact {
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

.btn-actions-compact:hover {
    background: #007bff;
    border-color: #007bff;
    color: white;
    transform: rotate(90deg);
}

/* Detalhes do Card */
.card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.813rem;
    min-width: 0;
}

.detail-item i {
    flex-shrink: 0;
    font-size: 0.875rem;
}

.detail-label {
    font-weight: 500;
    color: #6c757d;
    white-space: nowrap;
}

.detail-value {
    color: #212529;
    font-weight: 400;
}

/* Fluxo de Aprovação */
.card-aprovacao-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.fluxo-label {
    font-size: 0.813rem;
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
    padding-top: 0.25rem;
}

.fluxo-icons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    flex: 1;
}

.fluxo-step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.fluxo-step i {
    font-size: 1.125rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}

.fluxo-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    min-width: 0;
}

.fluxo-etapa {
    font-size: 0.688rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.fluxo-aprovador {
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fluxo-status {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6c757d;
}

.fluxo-data {
    font-size: 0.688rem;
    color: #6c757d;
    white-space: nowrap;
}

/* Dropdown */
.dropdown-menu-custom {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
    padding: 0.5rem 0;
}

.dropdown-menu-custom .dropdown-item {
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.dropdown-menu-custom .dropdown-item:hover {
    background: #f8f9fa;
    color: #007bff;
    padding-left: 1.5rem;
}

/* Responsividade */
@media (max-width: 768px) {
    .card-header-row {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .card-left {
        width: 100%;
    }

    .card-right {
        width: 100%;
        justify-content: space-between;
    }

    .card-details-row {
        flex-direction: column;
        gap: 0.5rem;
    }

    .card-aprovacao-row {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
