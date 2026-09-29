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
                <form v-if="!preload && !cadastrado && !atualizado" :id="`form_${hash}`" class="form_default" onsubmit="return false">
                    <fieldset>
                        <legend>Informações</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Cargo <span class="text-danger">*</span></label>
                                    <autocomplete
                                        :caminho="`autocomplete/cargosEmpresa`"
                                        formsm
                                        :valido="form.cargo_id !== ''"
                                        v-model="form.autocomplete_label_cargo"
                                        placeholder="Selecione um cargo"
                                        :disabled="visualizar || editando"
                                        :id="`cargo_${hash}`"
                                        @onblur="resetaCampoCargo"
                                        @onselect="selecionaCargo"
                                    ></autocomplete>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Nome do Colaborador</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.nome_pessoa"
                                        :id="`nome_pessoa_${hash}`"
                                        placeholder="Nome do colaborador a ser admitido"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        @blur="validaNomePessoa"
                                        @keyup="validaNomePessoa"
                                    />
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Centro de Custo <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.centro_custo_id"
                                        class="form-control form-control-sm"
                                        @change.prevent="changeCentroCusto()"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centro_custos" :value="item.id" :key="item.id">
                                            {{ item.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-2" v-if="centroCustoTemFilial">
                                <div class="form-group">
                                    <label>CNPJ</label>
                                    <select
                                        v-model="form.filial"
                                        class="form-control form-control-sm"
                                        @change.p.prevent="changeCnpj()"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                    >
                                        <option :value="false">Matriz</option>
                                        <option :value="true">Filial</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4" v-if="temFilial && form.filial">
                                <div class="form-group">
                                    <label>Filial <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.centro_custo_filial_id"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in centroCustoSelecionado" :value="item.id" :key="item.id">
                                            {{ item.filial.razao_social }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Tipo de admissão <span class="text-danger">*</span></label>
                                    <select
                                        v-model="form.tipo_contrato"
                                        class="form-control form-control-sm"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        onchange="valida_campo_vazio(this, 1)"
                                        onblur="valida_campo_vazio(this, 1)"
                                    >
                                        <option value="">Selecione</option>
                                        <option v-for="item in tiposAdmissao" :key="item" :value="item">{{ item }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label>Data admissão</label>
                                <datepicker
                                    formsm
                                    label=""
                                    class="corrigiDatepicker"
                                    v-model="form.data_admissao"
                                    :disabled="visualizar || aprovandoRh || aprovando"
                                ></datepicker>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Salário <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-mascara:dinheiro
                                        onblur="valida_dinheiro(this, 1)"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                        v-model="form.salario_format"
                                    />
                                </div>
                            </div>

                            <gestoraprovacao
                                label="Gestor Aprovação *"
                                :model="form"
                                :verifica="visualizar || aprovandoRh || aprovando"
                                :hash="hash"
                            ></gestoraprovacao>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea
                                        class="form-control form-control-sm"
                                        v-model="form.obs"
                                        cols="5"
                                        rows="5"
                                        :disabled="visualizar || aprovandoRh || aprovando"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="col-12">
                                <fieldset>
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
                            </div>
                        </div>
                        <div class="alert alert-warning" v-if="!form.data_aprovacao && !cadastrando">Esta solicitação ainda não foi aprovada ou reprovada!</div>

                        <fieldset v-if="visualizar || aprovando || aprovandoRh">
                            <legend>Aprovação Gestor</legend>
                            <div class="row">
                                <div v-if="!aprovando && form.user_aprovacao" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_gestor }} por: {{ form.user_aprovacao.nome }} em
                                        {{ form.data_aprovacao }}
                                    </legend>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Observação</label>
                                        <textarea
                                            class="form-control form-control-sm"
                                            :disabled="!aprovando || aprovandoRh"
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
                                            :disabled="!aprovando || aprovandoRh"
                                            v-model="form.status_aprovacao"
                                            onblur="valida_campo_vazio(this, 1)"
                                            onchange="valida_campo_vazio(this, 1)"
                                            class="form-control form-control-sm validacampo"
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
                                <div v-if="!aprovandoExtra && form.aprovacao_extra_nome" class="col-12">
                                    <legend>
                                        {{ form.status_aprovacao_extra }} por: {{ form.aprovacao_extra_nome }} em
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
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select
                                            :disabled="!aprovandoExtra || aprovandoRh"
                                            v-model="form.status_aprovacao_extra"
                                            onblur="valida_campo_vazio(this, 1)"
                                            onchange="valida_campo_vazio(this, 1)"
                                            class="form-control form-control-sm validacampo"
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
                                    <legend>
                                        {{ form.status_aprovacao_rh }} por: {{ form.rh_aprovacao.nome }} em
                                        {{ form.data_aprovacao_rh }}
                                    </legend>
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
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select
                                            :disabled="visualizar && !aprovando && !aprovandoRh"
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

        <modal id="janelaAtualizaStatus" titulo="Deseja APROVAR ou REPROVAR todos os colaboradores selecionados?" :centralizada="true" label-fechar="Fechar" ref="modal_janelaAtualizaStatus">
            <template #conteudo>
                <div class="col-12">
                    <div class="form-group">
                        <label>Observação</label>
                        <textarea class="form-control" v-model="formConfirmacao.obs_aprovacao" cols="5" rows="5"></textarea>
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
                    :id-suffix="'admissao-' + hash"
                    label="Período"
                    wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    @change="atualizar"
                />

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-busca">
                            Colaborador / CPF
                            <span v-if="buscaUnificadaEhCpf" class="admissao-filtro-hint">CPF</span>
                        </label>
                        <input
                            id="admissao-filtro-busca"
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
                        <label class="mybp-label" for="admissao-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="admissao-status"
                                input-id="admissao-filtro-status"
                                v-model="controle.dados.campoStatusAprovacao"
                                :options="opcoesStatus"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os status"
                                empty-message="Nenhum status encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('admissao-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-tipo-contrato">Tipo contrato</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroTipoContrato"
                                instance-id="admissao-tipo-contrato"
                                input-id="admissao-filtro-tipo-contrato"
                                v-model="controle.dados.tipo_contrato"
                                :options="opcoesTipoContrato"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os tipos"
                                empty-message="Nenhum tipo encontrado."
                                :max-results="20"
                                @opening="fecharOutrosComboboxes('admissao-tipo-contrato')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="lista_ccs && temFilial">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-cnpj">Lotação (CNPJ)</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="admissao-cnpj"
                                input-id="admissao-filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="opcoesCnpj"
                                :disabled="controle.carregando"
                                placeholder-blur="Todas as lotações"
                                empty-message="Nenhuma lotação encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('admissao-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="lista_ccs" :class="temFilial ? 'col-12 col-md-8' : 'col-12'">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                instance-id="admissao-cc"
                                input-id="admissao-filtro-cc"
                                v-model="controle.dados.campoCentroCusto"
                                :options="opcoesCentroCusto"
                                :disabled="controle.carregando || !opcoesCentroCusto.length"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro de custo encontrado."
                                :max-results="200"
                                @opening="fecharOutrosComboboxes('admissao-cc')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-ordenacao">Ordenar por</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroOrdenacao"
                                instance-id="admissao-ordenacao"
                                input-id="admissao-filtro-ordenacao"
                                v-model="controle.dados.ordenacao"
                                :options="opcoesOrdenacao"
                                :disabled="controle.carregando"
                                placeholder-blur="Mais recentes"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('admissao-ordenacao')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="admissao-filtro-pages">Por página</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroPages"
                                instance-id="admissao-pages"
                                input-id="admissao-filtro-pages"
                                v-model="campoPagesCombo"
                                :options="opcoesPages"
                                :disabled="controle.carregando"
                                placeholder-blur="20"
                                empty-message="Nenhuma opção encontrada."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('admissao-pages')"
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

            <!-- Cards Compactos -->
            <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div class="solicitacao-card" v-for="item in lista" :key="item.id">
                    <!-- Cabeçalho do Card -->
                    <div class="card-header-row">
                        <div class="card-left">
                            <span class="badge-id">#{{ item.id }}</span>
                            <div class="colaborador-principal">
                                <i class="fas fa-briefcase text-primary mr-1"></i>
                                <strong>{{ item.cargo.nome }}</strong>
                                <span v-if="item.nome_pessoa" class="ml-2 text-muted">— {{ item.nome_pessoa }}</span>
                            </div>
                            <div class="data-info ml-3">
                                <i class="fas fa-calendar-plus text-muted" style="font-size: 0.75rem"></i>
                                <small class="text-muted">{{ item.created_at }}</small>
                                <span v-if="item.updated_at && item.updated_at !== item.created_at" class="mx-2 text-muted">|</span>
                                <template v-if="item.updated_at && item.updated_at !== item.created_at">
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
                                    <i class="fas fa-check-circle"></i> APROVADO {{ nomeAprovacaoExtra.toUpperCase() }}
                                </span>
                                <span v-else-if="item.status_aprovacao === 'aprovado'"> <i class="fas fa-check-circle"></i> APROVADO GESTOR </span>
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
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = true; aprovandoExtra = false; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="item.user_aprovacao_id === null && !item.aprovado_via_script && aprovaGestor"
                                    >
                                        Aprovação Gestor
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        :title="nomeAprovacaoExtra"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = false; aprovando = false; aprovandoExtra = true; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="temAprovacaoExtra && item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra && podeAprovarExtra"
                                    >
                                        {{ nomeAprovacaoExtra }}
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Aprovação RH"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = true; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                        v-if="
                                            ((temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                                (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')) &&
                                            !item.aprovado_via_script &&
                                            item.rh_aprovacao_id === null &&
                                            aprovaRh
                                        "
                                    >
                                        Aprovação RH
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Visualizar"
                                        @click.prevent="formOpen(item.id); cadastrando = false; visualizar = true; aprovando = false; aprovandoExtra = false; aprovandoRh = false; podeanexar = false; $refs[`${hash}`] && $refs[`${hash}`].abrirModal()"
                                    >
                                        Visualizar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes do Card -->
                    <div class="card-details-row">
                        <div class="detail-item" v-if="item.nome_pessoa">
                            <i class="fas fa-user-tag text-muted"></i>
                            <span class="detail-label">Nome do Colaborador:</span>
                            <span class="detail-value">{{ item.nome_pessoa }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-user text-muted"></i>
                            <span class="detail-label">Solicitante:</span>
                            <span class="detail-value">{{ item.user_cadastrou.nome }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt text-muted"></i>
                            <span class="detail-label">Solicitação:</span>
                            <span class="detail-value">{{ item.created_at }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-check text-muted"></i>
                            <span class="detail-label">Data Admissão:</span>
                            <span class="detail-value">{{ item.data_admissao }}</span>
                        </div>
                    </div>
                    <div class="card-details-row">
                        <div class="detail-item">
                            <i class="fas fa-building text-muted"></i>
                            <span class="detail-label">Centro Custo:</span>
                            <span class="detail-value">{{ item.centro_custo.label }}</span>
                        </div>
                        <div class="detail-item" v-if="temFilial">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                            <span class="detail-label">Lotação:</span>
                            <span class="detail-value">{{ item.lotacao || 'Não informado' }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-file-contract text-muted"></i>
                            <span class="detail-label">Tipo contrato:</span>
                            <span class="detail-value">{{ item.tipo_contrato }}</span>
                        </div>
                        <div class="detail-item" v-if="item.salario_format">
                            <i class="fas fa-dollar-sign text-muted"></i>
                            <span class="detail-label">Salário:</span>
                            <span class="detail-value">{{ item.salario_format }}</span>
                        </div>
                    </div>

                    <!-- Fluxo de Aprovação -->
                    <div class="card-aprovacao-row">
                        <div class="fluxo-icons">
                            <!-- Solicitante -->
                            <div class="fluxo-step">
                                <i class="fas fa-check-circle text-primary"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Solicitante</small>
                                    <small class="fluxo-aprovador text-primary">
                                        {{ item.user_cadastrou.nome }}
                                    </small>
                                    <small v-if="item.created_at" class="fluxo-data">{{ item.created_at }}</small>
                                </div>
                            </div>

                            <i class="fas fa-chevron-right text-muted mx-2"></i>

                            <!-- Gestor -->
                            <div class="fluxo-step">
                                <i v-if="item.status_aprovacao === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                <i v-else-if="item.status_aprovacao === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                <i v-else class="fas fa-clock text-warning"></i>
                                <div class="fluxo-info">
                                    <small class="fluxo-etapa">Gestor</small>
                                    <small v-if="item.status_aprovacao === 'aprovado' && item.user_aprovacao" class="fluxo-aprovador text-success">
                                        {{ item.user_aprovacao.nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao === 'reprovado' && item.user_aprovacao" class="fluxo-aprovador text-danger">
                                        {{ item.user_aprovacao.nome }}
                                    </small>
                                    <small v-else class="fluxo-status text-warning">Aguardando</small>
                                    <small v-if="item.data_aprovacao" class="fluxo-data">{{ item.data_aprovacao }}</small>
                                </div>
                            </div>

                            <template v-if="temAprovacaoExtra">
                                <i class="fas fa-chevron-right text-muted mx-2"></i>
                                <!-- Aprovação Extra -->
                                <div class="fluxo-step">
                                    <i v-if="item.status_aprovacao === 'reprovado'" class="fas fa-ban text-secondary"></i>
                                    <i v-else-if="item.status_aprovacao_extra === 'aprovado'" class="fas fa-check-circle text-success"></i>
                                    <i v-else-if="item.status_aprovacao_extra === 'reprovado'" class="fas fa-times-circle text-danger"></i>
                                    <i v-else-if="item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra" class="fas fa-clock text-warning"></i>
                                    <i v-else class="fas fa-circle text-muted"></i>
                                    <div class="fluxo-info">
                                        <small class="fluxo-etapa">{{ nomeAprovacaoExtra }}</small>
                                        <small v-if="item.status_aprovacao === 'reprovado'" class="fluxo-status text-secondary">Cancelada</small>
                                        <small
                                            v-else-if="item.status_aprovacao_extra === 'aprovado' && item.aprovacao_extra_nome"
                                            class="fluxo-aprovador text-success"
                                        >
                                            {{ item.aprovacao_extra_nome }}
                                        </small>
                                        <small
                                            v-else-if="item.status_aprovacao_extra === 'reprovado' && item.aprovacao_extra_nome"
                                            class="fluxo-aprovador text-danger"
                                        >
                                            {{ item.aprovacao_extra_nome }}
                                        </small>
                                        <small v-else-if="item.status_aprovacao === 'aprovado'" class="fluxo-status text-warning">Aguardando</small>
                                        <small v-else class="fluxo-status">Pendente</small>
                                        <small v-if="item.data_aprovacao_extra" class="fluxo-data">{{ item.data_aprovacao_extra }}</small>
                                    </div>
                                </div>
                            </template>

                            <i class="fas fa-chevron-right text-muted mx-2"></i>

                            <!-- RH -->
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
                                        >Cancelada</small
                                    >
                                    <small v-else-if="item.status_aprovacao_rh === 'aprovado' && item.rh_aprovacao" class="fluxo-aprovador text-success">
                                        {{ item.rh_aprovacao.nome }}
                                    </small>
                                    <small v-else-if="item.status_aprovacao_rh === 'reprovado' && item.rh_aprovacao" class="fluxo-aprovador text-danger">
                                        {{ item.rh_aprovacao.nome }}
                                    </small>
                                    <small
                                        v-else-if="
                                            (temAprovacaoExtra && item.status_aprovacao_extra === 'aprovado') ||
                                            (!temAprovacaoExtra && item.status_aprovacao === 'aprovado')
                                        "
                                        class="fluxo-status text-warning"
                                        >Aguardando</small
                                    >
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
import gestoraprovacao from '../../GestorAprovacao'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Utils from '../../../mixins/Utils'
import Upload from '../../Upload'
import configuracoes from '../../../mixins/Configuracoes'
import DateRangeFilter from '../../DateRangeFilter.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'
import FiltroListagem from '../../ui/FiltroListagem.vue'

export default {
    mixins: [ExportacaoMixin, Utils, configuracoes],
    inject: {
        atualizarUrlMovimentacao: { default: () => () => {} }
    },
    components: {
        gestoraprovacao,
        Upload,
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem
    },
    data() {
        return {
            tituloJanela: 'Solicitacao de admissão',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            visualizando: false,
            aprovar_por_gestor: false,
            aprovando: false,
            aprovandoRh: false,
            aprovandoExtra: false,
            aprovaGestor: false,
            aprovaRh: false,
            podeAprovarExtra: false,
            temAprovacaoExtra: false,
            nomeAprovacaoExtra: '',
            preloadExportacao: false,

            urlExportacao: `${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/export`,
            url_anexo: `${URL_ADMIN}/planejamento/movimentacao/uploadAnexos`,
            anexoUploadAndamento: false,
            podeanexar: false,
            mimes: [],

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

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
                centro_custo_id: '',
                filial: false,
                centro_custo_filial_id: '',
                tipo_contrato: '',

                cargo_id: '',
                autocomplete_label_cargo: '',
                autocomplete_label_cargo_anterior: '',

                nome_pessoa: '',

                gestor_id: '',
                autocomplete_label_gestor_modal: '',
                autocomplete_label_gestor_modal_anterior: '',

                data_admissao: '',
                salario: '',
                salario_format: '0,00',

                user_id: '',
                solicitante: '',
                status: '',
                obs: '',

                obs_aprovacao: '',
                status_aprovacao: '',
                obs_aprovacao_extra: '',
                status_aprovacao_extra: '',
                data_aprovacao_extra: '',
                aprovacao_extra_nome: '',
                anexos: [],
                anexosDel: [],

                rh_aprovacao_id: '',
                obs_rh: '',
                status_aprovacao_rh: '',
                data_aprovacao_rh: '',
                aprovado_via_script: false
            },

            formDefault: null,
            lista: [],
            centro_custos: [],
            tiposAdmissao: [],
            lista_ccs: null,

            urlPaginacao: `${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/atualizar`,
            controle: {
                carregando: false,
                dados: {
                    pages: 20,
                    campoBusca: '',
                    campoCPF: '',
                    campoStatusAprovacao: '',
                    tipo_contrato: '',
                    campoCnpj: '',
                    campoCentroCusto: '',
                    filtroPeriodo: false,
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
        this.carregarTiposAdmissao()
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
        centroCustoSelecionado() {
            if (this.form.centro_custo_id === undefined || this.form.centro_custo_id === null || this.form.centro_custo_id === '') {
                return []
            }
            let centroSelecionado = _.find(this.centro_custos, { id: this.form.centro_custo_id })
            if (centroSelecionado && centroSelecionado.filiais && centroSelecionado.filiais.length) {
                return centroSelecionado.filiais
            }
            return []
        },
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
        paramsExport() {
            return this.controle.dados
        },
        centroCustoTemFilial() {
            return this.temFilial && this.centroCustoSelecionado.length > 0
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            let total = [d.campoBusca, d.campoCPF, d.campoStatusAprovacao, d.tipo_contrato, d.campoCentroCusto].filter(
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
        opcoesTipoContrato() {
            const opts = [{ value: '', label: 'Todos os tipos' }]
            ;(this.tiposAdmissao || []).forEach((item) => {
                opts.push({ value: item, label: item })
            })
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
            const key = `mov_admissao:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `mov_admissao:${itemId}`
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
        carregarTiposAdmissao() {
            axios
                .get(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/tipos-contrato`)
                .then((res) => {
                    this.tiposAdmissao = Array.isArray(res.data) ? res.data : []
                })
                .catch(() => {
                    this.tiposAdmissao = []
                })
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            this.controle.dados.token = urlParams.get('token') || ''
            if (urlParams.get('pages')) this.controle.dados.pages = parseInt(urlParams.get('pages'), 10) || 20
            if (urlParams.get('ordenacao')) this.controle.dados.ordenacao = urlParams.get('ordenacao')
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoCPF')) this.controle.dados.campoCPF = urlParams.get('campoCPF')
            if (urlParams.get('campoStatusAprovacao')) this.controle.dados.campoStatusAprovacao = urlParams.get('campoStatusAprovacao')
            if (urlParams.get('tipo_contrato')) this.controle.dados.tipo_contrato = urlParams.get('tipo_contrato')
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
            if (d.tipo_contrato) params.tipo_contrato = d.tipo_contrato
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto
            if (d.filtroPeriodo && d.dataInicio) params.dataInicio = d.dataInicio
            if (d.filtroPeriodo && d.dataFim) params.dataFim = d.dataFim
            if (d.token) params.token = d.token
            this.atualizarUrlMovimentacao(params)
        },
        changeCentroCusto() {
            this.form.filial = false
            this.form.centro_custo_filial_id = ''
        },

        changeCnpj() {
            this.form.centro_custo_filial_id = ''
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
                .post(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/atualizacao-status`, this.formConfirmacao)
                .then((res) => {
                    this.preloadAtualizacao = false
                    this.$refs.modal_janelaAtualizaStatus && this.$refs.modal_janelaAtualizaStatus.fecharModal()
                    mostraSucesso('Status atualizados com sucesso!')
                    this.selecionados = []
                    this.formConfirmacao = _.cloneDeep(this.formConfirmacaoDefault) //copia
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                })
                .catch((error) => {
                    this.preloadAtualizacao = false
                })
        },
        selecionaCargo(obj) {
            this.form.cargo_id = obj.id
            this.form.autocomplete_label_cargo = obj.label
            this.form.autocomplete_label_cargo_anterior = obj.label
        },
        resetaCampoCargo() {
            if (this.form.autocomplete_label_cargo_anterior !== this.form.autocomplete_label_cargo) {
                this.form.autocomplete_label_cargo_anterior = ''
                this.form.autocomplete_label_cargo = ''
                this.form.cargo_id = ''

                setTimeout(() => {
                    if (this.form.cargo_id === '') {
                        valida_campo_vazio($(`#cargo_${this.hash}`), 1)
                        $(`#${this.hash} #cargo_${this.hash}`).focus().trigger('blur')
                        mostraErro('Erro', 'O Campo Cargo não pode ficar vazio')
                    }
                }, 100)
            }
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

        validaNomePessoa(event) {
            const el = event && event.target ? event.target : document.getElementById(`nome_pessoa_${this.hash}`)
            if (!el) return
            el.classList.remove('is-invalid')
        },

        formNovo() {
            this.cadastrando = true
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.visualizar = false
            this.podeanexar = true

            this.tituloJanela = 'Solicitação de admissão'

            formReset()
            setupCampo()
            this.form = _.cloneDeep(this.formDefault) //copia
            this.listaCentroCusto()
        },

        cadastrar() {
            if (this.form.gestor_id === '') {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1)
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo GESTOR não pode ficar vazio')
                this.resetaCampoGestor()
                return false
            }
            if (this.form.cargo_id === '') {
                valida_campo_vazio($(`#cargo_${this.hash}`), 1)
                $(`#${this.hash} #cargo_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo CARGO não pode ficar vazio')
                this.resetaCampoCargo()
                return false
            }

            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .post(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista`, this.form)
                .then((response) => {
                    this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                    let data = response.data
                    mostraSucesso('', 'Solicitação registrada com sucesso!')
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        formOpen(id, so_visualizar = false) {
            Object.assign(this.form, this.formDefault)
            this.form.id = id
            this.cadastrado = false
            this.atualizado = false
            this.cadastrando = false
            this.aprovando = false
            this.aprovandoExtra = false
            this.aprovandoRh = false
            this.editando = false
            this.visualizar = false

            this.tituloJanela = `#${id}`

            formReset()
            this.preload = true

            axios
                .get(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)
                    this.listaCentroCusto()
                    this.form.centro_custo_id = data.centro_custo_id

                    this.tituloJanela = `#${id} Solicitação de admissão`
                    if (this.aprovando) {
                        this.form.status_aprovacao = data.status_aprovacao === null ? '' : data.status_aprovacao
                        this.form.observacao = data.status_aprovacao === null ? '' : data.observacao
                    }
                    this.editando = true
                    this.preload = false
                    this.sovizualiza(so_visualizar)
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        sovizualiza(so_visualizar) {
            if (so_visualizar) {
                setTimeout(() => {
                    $('.form_default :input').attr('disabled', 'true')
                }, 100)
            }
            this.visualizando = so_visualizar
        },

        alterar() {
            if (this.form.gestor_id === '') {
                valida_campo_vazio($(`#gestor_${this.hash}`), 1)
                $(`#${this.hash} #gestor_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo GESTOR não pode ficar vazio')
                this.resetaCampoGestor()
                return false
            }
            if (this.form.cargo_id === '') {
                valida_campo_vazio($(`#cargo_${this.hash}`), 1)
                $(`#${this.hash} #cargo_${this.hash}`).focus().trigger('blur')
                mostraErro('', 'Campo CARGO não pode ficar vazio')
                this.resetaCampoCargo()
                return false
            }

            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${this.form.id}`, this.form)
                .then((response) => {
                    this.$refs[this.hash] && this.$refs[this.hash].fecharModal()
                    let data = response.data
                    mostraSucesso('', 'Solicitação alterada com sucesso!')
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
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
                .put(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${this.form.id}/aprovar`, this.form)
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
                .put(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${this.form.id}/aprovar-extra`, this.form)
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
        aprovarRh() {
            $(`#${this.hash} :input:visible`).trigger('blur')
            if ($(`#${this.hash} :input:visible.is-invalid`).length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }
            this.preload = true
            axios
                .put(`${URL_ADMIN}/planejamento/movimentacao/admissoes-prevista/${this.form.id}/aprovarrh`, this.form)
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
            this.aprovar_por_gestor = dados.aprovar_por_gestor
            this.aprovaGestor = dados.aprovar_por_gestor
            this.aprovaRh = dados.aprovar_por_rh
            this.podeAprovarExtra = dados.pode_aprovar_extra || false
            this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || ''
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
            d.tipo_contrato = ''
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
                'admissao-status': 'comboFiltroStatus',
                'admissao-tipo-contrato': 'comboFiltroTipoContrato',
                'admissao-cnpj': 'comboFiltroCnpj',
                'admissao-cc': 'comboFiltroCc',
                'admissao-ordenacao': 'comboFiltroOrdenacao',
                'admissao-pages': 'comboFiltroPages'
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
/* Container de Cards */
.cards-lista {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

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

/* Status Badge */
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
}

.fluxo-etapa {
    font-weight: 600;
    color: #495057;
    font-size: 0.688rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.fluxo-aprovador,
.fluxo-status {
    font-size: 0.75rem;
    font-weight: 500;
}

.fluxo-data {
    font-size: 0.688rem;
    color: #6c757d;
}

/* Botão de ações compacto */
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

.admissao-filtro-hint {
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
    letter-spacing: 0.02em;
    text-transform: uppercase;
    vertical-align: middle;
}
</style>
