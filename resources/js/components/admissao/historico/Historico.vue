<template>
    <div class="historico-admissao">
        <modal id="janelaHistorico" :titulo="tituloJanela" :size="95" ref="modal_janelaHistorico">
            <template #conteudo>
                <div v-if="!openHistorico">
                    <aside
                        class="historico-modal-perfil"
                        :class="'historico-modal-perfil--' + varianteStatusHistorico(form)"
                        aria-label="Dados do colaborador"
                    >
                        <div class="historico-modal-perfil__foto-wrap">
                            <img
                                v-if="form.curriculo && form.curriculo.foto_3x4_thumb"
                                :src="form.curriculo.foto_3x4_thumb"
                                :alt="(form.curriculo && form.curriculo.nome) || 'Foto 3x4'"
                                width="48"
                                height="64"
                                loading="lazy"
                                decoding="async"
                                class="historico-modal-perfil__foto"
                                :class="{ 'is-ready': form._foto3x4Ready }"
                                @load="onFoto3x4ThumbLoad(form)"
                                @error="onFoto3x4ThumbError(form)"
                            />
                            <div
                                v-if="form.curriculo && form.curriculo.foto_3x4_thumb && !form._foto3x4Ready"
                                class="historico-modal-perfil__foto historico-modal-perfil__foto--loading"
                                aria-hidden="true"
                            >
                                <i class="fa fa-spinner fa-spin"></i>
                            </div>
                            <div
                                v-else-if="!(form.curriculo && form.curriculo.foto_3x4_thumb)"
                                class="historico-modal-perfil__foto historico-modal-perfil__foto--vazia"
                                aria-hidden="true"
                            >
                                <i class="fa fa-user"></i>
                            </div>
                        </div>

                        <div class="historico-modal-perfil__corpo">
                            <div class="historico-modal-perfil__titulo">
                                <h3 class="historico-modal-perfil__nome">
                                    {{ (form.curriculo && form.curriculo.nome) || 'Colaborador' }}
                                </h3>
                                <p class="historico-modal-perfil__meta-id">
                                    <span class="historico-status-id">#{{ form.feedback_id || form.id }}</span>
                                    <span class="historico-modal-perfil__sep" aria-hidden="true">·</span>
                                    <span
                                        class="historico-status-texto"
                                        :class="'historico-status-texto--' + statusHistoricoNormalizado(form)"
                                    >
                                        {{ textoStatusHistorico(form) }}
                                    </span>
                                </p>
                            </div>

                            <div class="historico-modal-perfil__linhas">
                                <p
                                    v-for="(linha, linhaIdx) in modalPerfilLinhas"
                                    :key="'linha-' + linhaIdx"
                                    class="historico-modal-perfil__linha"
                                >
                                    <span
                                        v-for="(item, idx) in linha"
                                        :key="'l' + linhaIdx + '-' + idx"
                                        class="historico-modal-perfil__item"
                                    >
                                        <template v-if="idx > 0">
                                            <span class="historico-modal-perfil__sep" aria-hidden="true">·</span>
                                        </template>
                                        <span class="historico-modal-perfil__k">{{ item.label }}:</span>
                                        {{ item.valor }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </aside>

                    <ul class="nav nav-tabs bg-light historico-modal-tabs" id="tabslist" role="tablist">
                        <li class="nav-item" v-if="permissoes.dossie">
                            <a
                                class="nav-item nav-link active"
                                id="nav-dossie-tab"
                                data-toggle="tab"
                                @click.prevent="trocaAba('abrirDossie')"
                                href="#nav-dossie"
                                role="tab"
                                aria-controls="nav-dossie"
                                aria-selected="true"
                                >DOSSIÊ</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.feedback">
                            <a
                                class="nav-item nav-link"
                                id="nav-feedback-historico-tab"
                                @click.prevent="trocaAba('abrirFeedbackHistorico')"
                                data-toggle="tab"
                                href="#nav-feedback-historico"
                                role="tab"
                                aria-controls="nav-feedback-historico"
                                aria-selected="true"
                                >FEEDBACK</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.medida_administrativa">
                            <a
                                class="nav-item nav-link"
                                id="nav-medidas-administrativas-tab"
                                @click.prevent="trocaAba('abrirMedidas')"
                                data-toggle="tab"
                                href="#nav-medidas-administrativas"
                                role="tab"
                                aria-controls="nav-medidas-administrativas"
                                aria-selected="true"
                                >MEDIDAS ADMINISTRATIVAS</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.avaliacao_noventa_dias">
                            <a
                                class="nav-item nav-link"
                                id="nav-formulario-noventa-tab"
                                data-toggle="tab"
                                @click.prevent="trocaAba('abrirFormularioNoventa')"
                                href="#nav-formulario-noventa"
                                role="tab"
                                aria-controls="nav-formulario-noventa"
                                aria-selected="false"
                                >AVALIAÇÃO DE EXPERIÊNCIA</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.avaliacao_anual">
                            <a
                                class="nav-item nav-link"
                                id="nav-avaliacao-anual-tab"
                                data-toggle="tab"
                                @click.prevent="trocaAba('abrirAvaliacaoAnual')"
                                href="#nav-avaliacao-anual"
                                role="tab"
                                aria-controls="nav-avaliacao-anual"
                                aria-selected="false"
                                >AVALIAÇÃO ANUAL</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.ferias">
                            <a
                                class="nav-item nav-link"
                                id="nav-ferias-tab"
                                data-toggle="tab"
                                @click.prevent="trocaAba('abrirFerias')"
                                href="#nav-ferias"
                                role="tab"
                                aria-controls="nav-ferias"
                                aria-selected="false"
                                >FÉRIAS</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.afastamento">
                            <a
                                class="nav-item nav-link"
                                id="nav-afastamento-tab"
                                data-toggle="tab"
                                @click.prevent="trocaAba('abrirAfastamento')"
                                href="#nav-afastamento"
                                role="tab"
                                aria-controls="nav-ferias"
                                aria-selected="false"
                                >AFASTAMENTO</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.beneficio">
                            <a
                                class="nav-item nav-link"
                                id="nav-beneficio-tab"
                                data-toggle="tab"
                                @click.prevent="trocaAba('abrirBeneficio')"
                                href="#nav-beneficio"
                                role="tab"
                                aria-controls="nav-beneficio"
                                aria-selected="false"
                                >BENEFÍCIO</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.cih">
                            <a
                                class="nav-item nav-link"
                                id="nav-cih-tab"
                                data-toggle="tab"
                                @click.prevent="trocaAba('abrirCih')"
                                href="#nav-cih"
                                role="tab"
                                aria-controls="nav-cih"
                                aria-selected="false"
                                >CIH</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.promocao">
                            <a
                                class="nav-item nav-link"
                                id="nav-promocao-tab"
                                data-toggle="tab"
                                @click.prevent="trocaAba('abrirPromocao')"
                                href="#nav-promocao"
                                role="tab"
                                aria-controls="nav-promocao"
                                aria-selected="false"
                                >PROMOÇÃO</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.metas">
                            <a
                                class="nav-item nav-link"
                                id="nav-meta-tab"
                                data-toggle="tab"
                                @click.prevent="trocaAba('abrirMetas')"
                                href="#nav-meta"
                                role="tab"
                                aria-controls="nav-meta"
                                aria-selected="false"
                                >METAS</a
                            >
                        </li>
                        <li class="nav-item" v-if="permissoes.logs">
                            <a
                                class="nav-item nav-link"
                                id="nav-log-historico-tab"
                                @click.prevent="trocaAba('abrirLogHistorico')"
                                data-toggle="tab"
                                href="#nav-log-historico"
                                role="tab"
                                aria-controls="nav-log-historico"
                                aria-selected="true"
                                >LOGS</a
                            >
                        </li>
                    </ul>

                    <div class="tab-content py-3 p-2">
                        <div class="tab-pane fade show active" id="nav-dossie" role="tabpanel" aria-labelledby="nav-dossie-tab">
                            <dossie v-if="abas.abrirDossie && permissoes.dossie" :feedback_id="form.feedback_id"></dossie>
                        </div>
                        <div class="tab-pane fade show" id="nav-feedback-historico" role="tabpanel" aria-labelledby="nav-feedback-historico-tab">
                            <feedback-historico v-if="abas.abrirFeedbackHistorico && permissoes.feedback" :feedback_id="form.feedback_id"></feedback-historico>
                        </div>
                        <div class="tab-pane fade show" id="nav-medidas-administrativas" role="tabpanel" aria-labelledby="nav-medidas-administrativas-tab">
                            <medidas-administrativas
                                v-if="abas.abrirMedidas && permissoes.medida_administrativa"
                                :feedback_id="form.feedback_id"
                            ></medidas-administrativas>
                        </div>
                        <div class="tab-pane fade show" id="nav-formulario-noventa" role="tabpanel" aria-labelledby="nav-formulario-noventa-tab">
                            <formulario-noventa-dias
                                v-if="abas.abrirFormularioNoventa && permissoes.avaliacao_noventa_dias"
                                :feedback_id="form.feedback_id"
                                :url-base-gerar-link="urlGerarLinkAvaliacaoExperiencia"
                            ></formulario-noventa-dias>
                        </div>
                        <div class="tab-pane fade show" id="nav-avaliacao-anual" role="tabpanel" aria-labelledby="nav-avaliacao-anual-tab">
                            <avaliacao-anual v-if="abas.abrirAvaliacaoAnual && permissoes.avaliacao_anual" :feedback_id="form.feedback_id"></avaliacao-anual>
                        </div>
                        <div class="tab-pane fade show" id="nav-ferias" role="tabpanel" aria-labelledby="nav-ferias-tab">
                            <ferias v-if="abas.abrirFerias && permissoes.ferias" :feedback_id="form.feedback_id" :curriculo_id="form.curriculo_id"></ferias>
                        </div>
                        <div class="tab-pane fade show" id="nav-afastamento" role="tabpanel" aria-labelledby="nav-afastamento-tab">
                            <afastamento v-if="abas.abrirAfastamento && permissoes.afastamento" :feedback_id="form.feedback_id"></afastamento>
                        </div>
                        <div class="tab-pane fade show" id="nav-beneficio" role="tabpanel" aria-labelledby="nav-beneficio-tab">
                            <beneficio v-if="abas.abrirBeneficio && permissoes.beneficio" :feedback_id="form.feedback_id"></beneficio>
                        </div>
                        <div class="tab-pane fade show" id="nav-cih" role="tabpanel" aria-labelledby="nav-cih-tab">
                            <cih v-if="abas.abrirCih && permissoes.cih" :fc_token="form.fc_token" modal-pai="janelaHistorico"></cih>
                        </div>
                        <div class="tab-pane fade show" id="nav-promocao" role="tabpanel" aria-labelledby="nav-promocao-tab">
                            <promocao v-if="abas.abrirPromocao && permissoes.promocao" :feedback_id="form.feedback_id"></promocao>
                        </div>
                        <div class="tab-pane fade show" id="nav-meta" role="tabpanel" aria-labelledby="nav-meta-tab">
                            <metas v-if="abas.abrirMetas && permissoes.metas" :feedback_id="form.feedback_id"></metas>
                        </div>
                        <div class="tab-pane fade show" id="nav-log-historico" role="tabpanel" aria-labelledby="nav-log-historico-tab">
                            <logs-historico v-if="abas.abrirLogHistorico && permissoes.logs" :feedback_id="form.feedback_id"></logs-historico>
                        </div>
                    </div>
                </div>
            </template>
            <template #rodape> </template>
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
                        <label class="mybp-label" for="historico-filtro-busca">
                            Colaborador / CPF
                            <span v-if="buscaUnificadaEhCpf" class="text-muted small">CPF</span>
                        </label>
                        <input
                            id="historico-filtro-busca"
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

                <div class="col-12 col-md-4" v-if="permissoes.filtrar_demitido">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="historico-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="historico-status"
                                input-id="historico-filtro-status"
                                v-model="campoStatusCombo"
                                :options="opcoesStatus"
                                :disabled="controle.carregando"
                                placeholder-blur="Admitidos"
                                empty-message="Nenhum status encontrado."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('historico-status')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4" v-if="temFilial && opcoesCnpj.length > 1">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="historico-filtro-cnpj">Lotação</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCnpj"
                                instance-id="historico-cnpj"
                                input-id="historico-filtro-cnpj"
                                v-model="controle.dados.campoCnpj"
                                :options="opcoesCnpj"
                                :disabled="controle.carregando"
                                placeholder-blur="Todas as lotações"
                                empty-message="Nenhuma lotação encontrada."
                                :max-results="50"
                                @opening="fecharOutrosComboboxes('historico-cnpj')"
                                @select="onSelectCnpj"
                            />
                        </div>
                    </div>
                </div>

                <div
                    v-if="mostrarFiltroCentroCusto"
                    :class="temFilial ? 'col-12 col-md-8' : 'col-12 col-md-4'"
                >
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="historico-filtro-cc">Centro de custo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCc"
                                instance-id="historico-cc"
                                input-id="historico-filtro-cc"
                                v-model="controle.dados.campoCentroCusto"
                                :options="opcoesCentroCusto"
                                :disabled="controle.carregando || !opcoesCentroCusto.length"
                                placeholder-blur="Todos os centros"
                                empty-message="Nenhum centro de custo encontrado."
                                :max-results="200"
                                @opening="fecharOutrosComboboxes('historico-cc')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group mybp-filtro-campo">
                        <label class="mybp-label" for="historico-filtro-cargo">Cargo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroCargo"
                                instance-id="historico-cargo"
                                input-id="historico-filtro-cargo"
                                v-model="controle.dados.campoCargo"
                                :options="opcoesCargo"
                                :disabled="controle.carregando"
                                placeholder-blur="Todos os cargos"
                                empty-message="Nenhum cargo encontrado."
                                :max-results="100"
                                @opening="fecharOutrosComboboxes('historico-cargo')"
                                @select="onSelectFiltro"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="col-12 mybp-filtros-avancados-shell"
                    :class="{ 'is-open': filtrosAvancadosAbertos }"
                    :aria-hidden="filtrosAvancadosAbertos ? 'false' : 'true'"
                >
                    <div class="mybp-filtros-avancados-shell__inner">
                        <div class="mybp-filtros-avancados">
                            <div class="mybp-filtros-avancados__block">
                                <p class="mybp-filtros-avancados__title">Outros filtros</p>
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group mybp-filtro-campo">
                                            <label class="mybp-label" for="historico-filtro-matricula">Matrícula</label>
                                            <input
                                                id="historico-filtro-matricula"
                                                type="text"
                                                placeholder="Matrícula"
                                                autocomplete="off"
                                                class="form-control form-control-sm"
                                                :disabled="controle.carregando"
                                                v-model="controle.dados.campoMatricula"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group mybp-filtro-campo">
                                            <label class="mybp-label" for="historico-filtro-funcao">Função</label>
                                            <input
                                                id="historico-filtro-funcao"
                                                type="text"
                                                placeholder="Função"
                                                autocomplete="off"
                                                class="form-control form-control-sm"
                                                :disabled="controle.carregando"
                                                v-model="controle.dados.campoFuncao"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4" v-if="tiposAdmissao.length">
                                        <div class="form-group mybp-filtro-campo">
                                            <label class="mybp-label" for="historico-filtro-tipo">Tipo admissão</label>
                                            <div class="mybp-combobox-wrap">
                                                <combobox-auto-complete
                                                    ref="comboFiltroTipo"
                                                    instance-id="historico-tipo"
                                                    input-id="historico-filtro-tipo"
                                                    v-model="controle.dados.campoTipoAdmissao"
                                                    :options="opcoesTipoAdmissao"
                                                    :disabled="controle.carregando"
                                                    placeholder-blur="Todos"
                                                    empty-message="Nenhum tipo encontrado."
                                                    :max-results="20"
                                                    @opening="fecharOutrosComboboxes('historico-tipo')"
                                                    @select="onSelectFiltro"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group mybp-filtro-campo">
                                            <label class="mybp-label" for="historico-filtro-ordenacao">Ordenar por</label>
                                            <div class="mybp-combobox-wrap">
                                                <combobox-auto-complete
                                                    ref="comboFiltroOrdenacao"
                                                    instance-id="historico-ordenacao"
                                                    input-id="historico-filtro-ordenacao"
                                                    v-model="controle.dados.ordenacao"
                                                    :options="opcoesOrdenacao"
                                                    :disabled="controle.carregando"
                                                    placeholder-blur="Mais recentes"
                                                    empty-message="Nenhuma opção encontrada."
                                                    :max-results="15"
                                                    @opening="fecharOutrosComboboxes('historico-ordenacao')"
                                                    @select="onSelectFiltro"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group mybp-filtro-campo">
                                            <label class="mybp-label" for="historico-filtro-pages">Por página</label>
                                            <div class="mybp-combobox-wrap">
                                                <combobox-auto-complete
                                                    ref="comboFiltroPages"
                                                    instance-id="historico-pages"
                                                    input-id="historico-filtro-pages"
                                                    v-model="campoPagesCombo"
                                                    :options="opcoesPages"
                                                    :disabled="controle.carregando"
                                                    placeholder-blur="20"
                                                    empty-message="Nenhuma opção encontrada."
                                                    :max-results="10"
                                                    @opening="fecharOutrosComboboxes('historico-pages')"
                                                    @select="onSelectFiltro"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                    class="btn btn-sm mybp-btn-mais-filtros"
                    :class="filtrosAvancadosAbertos ? 'btn-primary is-open' : 'btn-outline-primary'"
                    :aria-expanded="filtrosAvancadosAbertos ? 'true' : 'false'"
                    :disabled="controle.carregando"
                    @click="filtrosAvancadosAbertos = !filtrosAvancadosAbertos"
                >
                    <i class="fa" :class="filtrosAvancadosAbertos ? 'fa-chevron-up' : 'fa-sliders-h'"></i>
                    {{ filtrosAvancadosAbertos ? 'Menos filtros' : 'Mais filtros' }}
                    <span class="badge badge-light ml-1" v-if="totalFiltrosAtivosAvancados">
                        {{ totalFiltrosAtivosAvancados }}
                    </span>
                </button>
            </template>
        </FiltroListagem>

        <preload class="text-center" v-if="controle.carregando"></preload>

        <div id="conteudo">
            <div class="empty-state" v-show="!controle.carregando && lista.length === 0">
                <div class="empty-state-icon"><i class="fas fa-history"></i></div>
                <h3 class="empty-state-title">Nenhum registro encontrado</h3>
                <p class="empty-state-text">Ajuste os filtros ou aguarde registros no histórico de admissão.</p>
            </div>

            <div class="cards-lista" v-show="!controle.carregando && lista.length > 0">
                <div
                    class="solicitacao-card"
                    v-for="item in lista"
                    :key="item.id"
                    :class="{
                        'card-status-admitido': item.admissao && (item.admissao.status === 'ADMITIDO' || item.admissao.status === 'Admitido'),
                        'card-status-demitido': item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido')
                    }"
                >
                    <div class="card-header-row">
                        <div class="card-left">
                            <div class="colab-foto-3x4-wrap">
                                <img
                                    v-if="item.curriculo && item.curriculo.foto_3x4_thumb"
                                    :src="item.curriculo.foto_3x4_thumb"
                                    alt=""
                                    width="40"
                                    height="53"
                                    loading="lazy"
                                    decoding="async"
                                    class="colab-foto-3x4"
                                    :class="{ 'is-ready': item._foto3x4Ready }"
                                    @load="onFoto3x4ThumbLoad(item)"
                                    @error="onFoto3x4ThumbError(item)"
                                />
                                <div
                                    v-if="item.curriculo && item.curriculo.foto_3x4_thumb && !item._foto3x4Ready"
                                    class="colab-foto-3x4 colab-foto-3x4--loading"
                                    aria-hidden="true"
                                >
                                    <i class="fa fa-spinner fa-spin"></i>
                                </div>
                                <div
                                    v-else-if="!(item.curriculo && item.curriculo.foto_3x4_thumb)"
                                    class="colab-foto-3x4 colab-foto-3x4--vazia"
                                    aria-hidden="true"
                                >
                                    <i class="fa fa-user"></i>
                                </div>
                            </div>
                            <div class="colaborador-principal">
                                <strong>{{ item.curriculo && item.curriculo.nome }}</strong>
                                <div class="colaborador-meta-id">
                                    <span class="historico-status-id">#{{ item.id }}</span>
                                    <span class="historico-modal-perfil__sep" aria-hidden="true">·</span>
                                    <span
                                        class="historico-status-texto"
                                        :class="'historico-status-texto--' + statusHistoricoNormalizado(item)"
                                    >
                                        {{ textoStatusHistorico(item) }}
                                    </span>
                                </div>
                                <div class="colaborador-cpf" v-if="item.curriculo && item.curriculo.cpf">
                                    CPF: {{ item.curriculo.cpf }}
                                </div>
                            </div>
                        </div>
                        <div class="card-right">
                            <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                                <a
                                    class="btn-actions-compact"
                                    href="#"
                                    role="button"
                                    :id="'dropdownHistorico_' + item.id"
                                    aria-haspopup="true"
                                    :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                                    @click.prevent.stop="toggleDropdown(item.id)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu dropdown-menu-custom dropdown-menu-right"
                                    :class="{ show: isDropdownOpen(item.id) }"
                                    :aria-labelledby="'dropdownHistorico_' + item.id"
                                    @click="fecharDropdown"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="javascript://"
                                        title="Abrir histórico"
                                        @click.prevent="abrirHistorico(item); $refs.modal_janelaHistorico && $refs.modal_janelaHistorico.abrirModal()"
                                    >
                                        <i class="fas fa-edit mr-2 text-primary"></i> Abrir histórico
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-details-row card-details-main">
                        <div class="detail-item">
                            <i class="fas fa-briefcase"></i>
                            <span class="detail-label">Cargo</span>
                            <span class="detail-value" :class="{ 'detail-value-empty': !item.admissao || !item.admissao.cargo }">
                                {{ (item.admissao && item.admissao.cargo) || 'Não informado' }}
                            </span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && item.admissao.funcao">
                            <i class="fas fa-user-tag"></i>
                            <span class="detail-label">Função</span>
                            <span class="detail-value">{{ item.admissao.funcao }}</span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && item.admissao.matricula">
                            <i class="fas fa-hashtag"></i>
                            <span class="detail-label">Matrícula</span>
                            <span class="detail-value">{{ item.admissao.matricula }}</span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && item.admissao.emp_centro_custo">
                            <i class="fas fa-sitemap"></i>
                            <span class="detail-label">Centro de custo</span>
                            <span class="detail-value">{{ item.admissao.emp_centro_custo }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="detail-label">Lotação</span>
                            <span
                                class="detail-value"
                                :class="{
                                    'detail-value-empty': !(
                                        item.admissao &&
                                        (item.admissao.emp_nome_fantasia || item.admissao.emp_cnpj)
                                    )
                                }"
                            >
                                <template v-if="item.admissao && (item.admissao.emp_nome_fantasia || item.admissao.emp_cnpj)">
                                    {{ item.admissao.emp_nome_fantasia || '—'
                                    }}<template v-if="item.admissao.emp_cnpj">
                                        - {{ item.admissao.emp_cnpj }}
                                    </template>
                                </template>
                                <template v-else>Não informado</template>
                            </span>
                        </div>
                        <div class="detail-item" v-if="item.admissao && item.admissao.tipo_admissao">
                            <i class="fas fa-file-contract"></i>
                            <span class="detail-label">Tipo admissão</span>
                            <span class="detail-value">{{ item.admissao.tipo_admissao }}</span>
                        </div>
                    </div>
                    <div class="card-details-row card-details-fixas">
                        <div class="detail-item" v-if="item.admissao && item.admissao.data_admissao">
                            <i class="fas fa-calendar-check"></i>
                            <span class="detail-label">Data admissão</span>
                            <span class="detail-value">{{ item.admissao.data_admissao }}</span>
                        </div>
                        <div
                            class="detail-item"
                            v-if="
                                item.admissao && (item.admissao.status === 'DEMITIDO' || item.admissao.status === 'Demitido') && item.admissao.data_encerramento
                            "
                        >
                            <i class="fas fa-calendar-times"></i>
                            <span class="detail-label">Data desligamento</span>
                            <span class="detail-value">{{ item.admissao.data_encerramento }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span class="detail-label">Última atualização</span>
                            <span class="detail-value" :class="{ 'detail-value-empty': !item.ultima_atualizacao }">
                                {{ item.ultima_atualizacao || '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <controle-paginacao
                class="d-flex justify-content-center"
                id="controle"
                ref="componente"
                :url="urlAtualizar"
                :por-pagina="controle.dados.pages"
                :dados="controle.dados"
                @carregou="carregou"
                @carregando="carregando"
            ></controle-paginacao>
        </div>
    </div>
</template>

<script>
import Dossie from './Dossie'
import MedidasAdministrativas from '../MedidasAdministrativas.vue'
import FormularioNoventaDias from './FormularioNoventaDias'
import AvaliacaoAnual from './AvaliacaoAnual'
import Ferias from './Ferias'
import Afastamento from './Afastamento'
import Beneficio from './Beneficio'
import Cih from './CIH'
import Promocao from './Promocao'
import Metas from './Meta'
import FeedbackHistorico from './FeedbackHistorico'
import LogsHistorico from './LogsHistorico'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete'

export default {
    name: 'Historico',

    components: {
        Dossie,
        MedidasAdministrativas,
        FeedbackHistorico,
        FormularioNoventaDias,
        AvaliacaoAnual,
        Ferias,
        Afastamento,
        Beneficio,
        Cih,
        Promocao,
        Metas,
        LogsHistorico,
        FiltroListagem,
        ComboboxAutoComplete
    },

    props: {
        urlAtualizar: {
            type: String,
            required: true
        },
        urlGerarLinkAvaliacaoExperiencia: {
            type: String,
            default: ''
        }
    },

    data() {
        const abasDefault = {
            abrirDossie: false,
            abrirMedidas: false,
            abrirFeedbackHistorico: false,
            abrirFormularioNoventa: false,
            abrirAvaliacaoAnual: false,
            abrirFerias: false,
            abrirAfastamento: false,
            abrirBeneficio: false,
            abrirCih: false,
            abrirPromocao: false,
            abrirMetas: false,
            abrirLogHistorico: false
        }
        return {
            tituloJanela: 'Histórico',
            openHistorico: true,
            abas: { ...abasDefault, abrirDossie: true },
            abasDefault,
            permissoes: {},
            form: {
                feedback_id: 0,
                curriculo_id: 0,
                curriculo: { nome: '', cpf: '' },
                admissao: { cargo: '', funcao: '', data_admissao: '', status: '' },
                fc_token: null
            },
            lista: [],
            cargos: [],
            listaCentrosCusto: [],
            listaCnpjs: {},
            listaCentrosPorCnpj: {},
            temFilial: false,
            tiposAdmissao: [],
            porPagina: [10, 20, 50, 100],
            dropdownAbertoKey: null,
            filtrosAvancadosAbertos: false,
            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: 'autocomplete/todas-vagas-ativas',
                    pages: 20,
                    campoBusca: '',
                    campoCPF: '',
                    campoCargo: '',
                    campoMatricula: '',
                    campoFuncao: '',
                    campoTipoAdmissao: '',
                    campoDemitido: false,
                    campoCnpj: '',
                    campoCentroCusto: '',
                    ordenacao: 'created_at_desc'
                }
            },
            initialPage: null
        }
    },

    computed: {
        filtroListaCentroCustoCnpj() {
            if (this.listaCentrosCusto && this.listaCentrosCusto.length > 0) {
                return this.listaCentrosCusto
            }
            if (!this.listaCentrosPorCnpj || typeof this.listaCentrosPorCnpj !== 'object') {
                return []
            }
            const cnpj = this.controle.dados.campoCnpj
            if (cnpj && this.listaCentrosPorCnpj[cnpj] && this.listaCentrosPorCnpj[cnpj].length > 0) {
                return this.listaCentrosPorCnpj[cnpj]
            }
            if (!this.temFilial) {
                const keys = Object.keys(this.listaCentrosPorCnpj)
                return keys.length ? this.listaCentrosPorCnpj[keys[0]] || [] : []
            }
            const todas = []
            Object.values(this.listaCentrosPorCnpj).forEach((arr) => {
                if (Array.isArray(arr)) todas.push(...arr)
            })
            return todas
        },
        mostrarFiltroCentroCusto() {
            if (this.listaCentrosCusto && this.listaCentrosCusto.length > 0) return true
            if (this.listaCentrosPorCnpj && Object.keys(this.listaCentrosPorCnpj).length > 0) return true
            return false
        },
        campoBuscaUnificada() {
            const d = this.controle.dados
            if (d.campoCPF) return d.campoCPF
            return d.campoBusca || ''
        },
        buscaUnificadaEhCpf() {
            return !!this.controle.dados.campoCPF
        },
        campoStatusCombo: {
            get() {
                return this.controle.dados.campoDemitido ? 'true' : 'false'
            },
            set(valor) {
                this.controle.dados.campoDemitido = valor === 'true'
            }
        },
        campoPagesCombo: {
            get() {
                return String(this.controle.dados.pages || 20)
            },
            set(valor) {
                this.controle.dados.pages = parseInt(valor, 10) || 20
            }
        },
        opcoesStatus() {
            return [
                { value: 'false', label: 'Admitidos' },
                { value: 'true', label: 'Demitidos' }
            ]
        },
        modalPerfilLinhas() {
            const a = this.form.admissao || {}
            const c = this.form.curriculo || {}
            const lotacao = [a.emp_nome_fantasia, a.emp_cnpj].filter(Boolean).join(' - ')
            const demitido = String(a.status || '').toUpperCase() === 'DEMITIDO'

            const linha1 = [
                { label: 'CPF', valor: c.cpf || 'Não informado' },
                { label: 'Matrícula', valor: a.matricula || 'Não informado' },
                { label: 'Cargo', valor: a.cargo || 'Não informado' },
                { label: 'Função', valor: a.funcao || 'Não informado' }
            ]
            const linha2 = [
                { label: 'Lotação', valor: lotacao || 'Não informado' },
                { label: 'Centro de custo', valor: a.emp_centro_custo || 'Não informado' }
            ]
            const linha3 = [
                { label: 'Tipo admissão', valor: a.tipo_admissao || 'Não informado' },
                { label: 'Data admissão', valor: a.data_admissao || 'Não informado' }
            ]
            if (demitido) {
                linha3.push({
                    label: 'Data desligamento',
                    valor: a.data_encerramento || 'Não informado'
                })
            }
            return [linha1, linha2, linha3]
        },
        opcoesCnpj() {
            const opts = [{ value: '', label: 'Todas as lotações' }]
            Object.keys(this.listaCnpjs || {}).forEach((key) => {
                const item = this.listaCnpjs[key]
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
            opts.push({ value: '--naoinformado--', label: '— Não informado —' })
            return opts
        },
        opcoesCargo() {
            const opts = [{ value: '', label: 'Todos os cargos' }]
            ;(this.cargos || []).forEach((cargo) => {
                if (!cargo || !cargo.nome) return
                opts.push({ value: cargo.nome, label: cargo.nome })
            })
            return opts
        },
        opcoesTipoAdmissao() {
            const opts = [{ value: '', label: 'Todos' }]
            ;(this.tiposAdmissao || []).forEach((t) => {
                opts.push({ value: t, label: t })
            })
            return opts
        },
        opcoesOrdenacao() {
            return [
                { value: 'created_at_desc', label: 'Mais recentes' },
                { value: 'created_at_asc', label: 'Mais antigos' },
                { value: 'updated_at_desc', label: 'Última atualização (mais recente)' },
                { value: 'updated_at_asc', label: 'Última atualização (mais antiga)' },
                { value: 'nome_asc', label: 'Nome (A–Z)' },
                { value: 'nome_desc', label: 'Nome (Z–A)' },
                { value: 'data_admissao_desc', label: 'Data admissão (mais recente)' },
                { value: 'data_admissao_asc', label: 'Data admissão (mais antiga)' },
                { value: 'cargo_asc', label: 'Cargo (A–Z)' },
                { value: 'cargo_desc', label: 'Cargo (Z–A)' }
            ]
        },
        opcoesPages() {
            return this.porPagina.map((n) => ({ value: String(n), label: String(n) }))
        },
        totalFiltrosAtivos() {
            const d = this.controle.dados
            let total = [d.campoBusca, d.campoCPF, d.campoCargo, d.campoCentroCusto].filter(
                (v) => v !== '' && v !== null && v !== undefined
            ).length
            if (d.campoDemitido === true) total++
            if (this.temFilial && d.campoCnpj) total++
            total += this.totalFiltrosAtivosAvancados
            return total
        },
        totalFiltrosAtivosAvancados() {
            const d = this.controle.dados
            let total = [d.campoMatricula, d.campoFuncao, d.campoTipoAdmissao].filter(
                (v) => v !== '' && v !== null && v !== undefined
            ).length
            if (d.ordenacao && d.ordenacao !== 'created_at_desc') total++
            if (Number(d.pages) !== 20) total++
            return total
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
        this.urlParamGet()
        this.abasDefault = _.cloneDeep(this.abas)
        if (this.initialPage != null && this.initialPage >= 1) {
            this.$nextTick(() => {
                if (this.$refs.componente) {
                    this.$refs.componente.atual = this.initialPage
                    this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                }
            })
        } else {
            this.atualizar()
        }
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
            const key = `hist:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `hist:${itemId}`
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
            if (urlParams.get('pages')) this.controle.dados.pages = parseInt(urlParams.get('pages'), 10) || 20
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoCPF')) this.controle.dados.campoCPF = urlParams.get('campoCPF')
            if (urlParams.get('campoCargo')) this.controle.dados.campoCargo = urlParams.get('campoCargo')
            if (urlParams.get('campoMatricula')) this.controle.dados.campoMatricula = urlParams.get('campoMatricula')
            if (urlParams.get('campoFuncao')) this.controle.dados.campoFuncao = urlParams.get('campoFuncao')
            if (urlParams.get('campoTipoAdmissao')) this.controle.dados.campoTipoAdmissao = urlParams.get('campoTipoAdmissao')
            if (urlParams.get('campoDemitido') !== null && urlParams.get('campoDemitido') !== '') {
                this.controle.dados.campoDemitido = urlParams.get('campoDemitido') === 'true'
            }
            if (urlParams.get('campoCnpj')) this.controle.dados.campoCnpj = urlParams.get('campoCnpj')
            if (urlParams.get('campoCentroCusto')) {
                this.controle.dados.campoCentroCusto = String(urlParams.get('campoCentroCusto'))
            }
            if (urlParams.get('ordenacao')) this.controle.dados.ordenacao = urlParams.get('ordenacao')
            if (urlParams.get('page')) {
                const p = parseInt(urlParams.get('page'), 10)
                if (p >= 1) this.initialPage = p
            }
        },

        syncUrlFiltros() {
            const d = this.controle.dados
            const atual = this.$refs.componente && this.$refs.componente.atual ? this.$refs.componente.atual : 1
            const params = {}
            if (d.pages && d.pages !== 20) params.pages = d.pages
            if (d.ordenacao && d.ordenacao !== 'created_at_desc') params.ordenacao = d.ordenacao
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoCPF) params.campoCPF = d.campoCPF
            if (d.campoCargo) params.campoCargo = d.campoCargo
            if (d.campoMatricula) params.campoMatricula = d.campoMatricula
            if (d.campoFuncao) params.campoFuncao = d.campoFuncao
            if (d.campoTipoAdmissao) params.campoTipoAdmissao = d.campoTipoAdmissao
            if (d.campoDemitido === true) params.campoDemitido = 'true'
            if (d.campoCnpj) params.campoCnpj = d.campoCnpj
            if (d.campoCentroCusto) params.campoCentroCusto = d.campoCentroCusto
            if (atual > 1) params.page = atual
            const qs = new URLSearchParams(params).toString()
            const url = qs ? `${window.location.pathname}?${qs}` : window.location.pathname
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, '', url)
            }
        },

        formatarCpfDigitos(valor) {
            const digitos = String(valor || '').replace(/\D/g, '').slice(0, 11)
            if (digitos.length <= 3) return digitos
            if (digitos.length <= 6) return `${digitos.slice(0, 3)}.${digitos.slice(3)}`
            if (digitos.length <= 9) {
                return `${digitos.slice(0, 3)}.${digitos.slice(3, 6)}.${digitos.slice(6)}`
            }
            return `${digitos.slice(0, 3)}.${digitos.slice(3, 6)}.${digitos.slice(6, 9)}-${digitos.slice(9)}`
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
                'historico-status': 'comboFiltroStatus',
                'historico-cnpj': 'comboFiltroCnpj',
                'historico-cc': 'comboFiltroCc',
                'historico-cargo': 'comboFiltroCargo',
                'historico-tipo': 'comboFiltroTipo',
                'historico-ordenacao': 'comboFiltroOrdenacao',
                'historico-pages': 'comboFiltroPages'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.close === 'function') ref.close()
            })
        },

        limparFiltros() {
            this.controle.dados.campoBusca = ''
            this.controle.dados.campoCPF = ''
            this.controle.dados.campoCargo = ''
            this.controle.dados.campoMatricula = ''
            this.controle.dados.campoFuncao = ''
            this.controle.dados.campoTipoAdmissao = ''
            this.controle.dados.campoDemitido = false
            this.controle.dados.campoCnpj = ''
            this.controle.dados.campoCentroCusto = ''
            this.controle.dados.pages = 20
            this.controle.dados.ordenacao = 'created_at_desc'
            this.filtrosAvancadosAbertos = false
            this.syncUrlFiltros()
            if (this.$refs.componente) {
                this.$refs.componente.atual = 1
                if (this.$refs.componente.buscar) this.$refs.componente.buscar()
            }
        },

        trocaAba(aba) {
            this.abas = _.cloneDeep(this.abasDefault)
            this.abas[aba] = true
        },

        abrirHistorico(obj) {
            this.openHistorico = true
            this.tituloJanela = 'Histórico do colaborador'
            this.form = _.cloneDeep(obj)
            this.form.feedback_id = obj.id
            this.form.curriculo_id = obj.curriculo_id
            this.form.fc_token = obj.fc_token || null
            this.form._foto3x4Ready = !!(obj.curriculo && obj.curriculo.foto_3x4_thumb && obj._foto3x4Ready)
            if (!this.form.curriculo) {
                this.form.curriculo = { nome: '', cpf: '', foto_3x4_thumb: null }
            }
            if (!this.form.admissao) {
                this.form.admissao = {
                    cargo: '',
                    funcao: '',
                    data_admissao: '',
                    status: '',
                    matricula: '',
                    tipo_admissao: '',
                    emp_nome_fantasia: null,
                    emp_cnpj: null,
                    emp_centro_custo: null
                }
            }

            setTimeout(() => {
                this.trocaAba('abrirDossie')
                this.openHistorico = false
            }, 100)
            this.$nextTick(() => {
                if (typeof $ !== 'undefined' && $('#nav-dossie-tab').length) {
                    $('#nav-dossie-tab').tab('show')
                }
            })
        },

        onFoto3x4ThumbLoad(item) {
            if (item) {
                item._foto3x4Ready = true
            }
        },
        onFoto3x4ThumbError(item) {
            if (item && item.curriculo) {
                item.curriculo.foto_3x4_thumb = null
                item._foto3x4Ready = false
            }
        },
        statusHistoricoNormalizado(item) {
            const raw = String((item && item.admissao && item.admissao.status) || '')
                .trim()
                .toUpperCase()
            if (raw === 'ADMITIDO') return 'admitido'
            if (raw === 'DEMITIDO') return 'demitido'
            return 'outro'
        },
        varianteStatusHistorico(item) {
            const s = this.statusHistoricoNormalizado(item)
            if (s === 'admitido') return 'aprovado'
            if (s === 'demitido') return 'reprovado'
            return 'neutro'
        },
        textoStatusHistorico(item) {
            const s = this.statusHistoricoNormalizado(item)
            if (s === 'admitido') return 'Admitido'
            if (s === 'demitido') return 'Demitido'
            const raw = item && item.admissao && item.admissao.status
            return raw ? String(raw) : 'Sem status'
        },

        carregou(dados) {
            this.lista = (dados.itens || []).map((item) => {
                item._foto3x4Ready = false
                return item
            })
            this.cargos = dados.cargos || []
            this.permissoes = dados.permissoes || {}
            this.listaCentrosCusto = dados.lista_centros_custo || []
            this.listaCnpjs = dados.lista_cnpjs || {}
            this.listaCentrosPorCnpj = dados.lista_centros_por_cnpj || {}
            this.temFilial = !!dados.tem_filial
            this.tiposAdmissao = dados.tipos_admissao || []
            // Combobox exige string no value do CC
            if (this.controle.dados.campoCentroCusto != null && this.controle.dados.campoCentroCusto !== '') {
                this.controle.dados.campoCentroCusto = String(this.controle.dados.campoCentroCusto)
            }
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
        },

        carregando() {
            this.controle.carregando = true
        },

        buscar() {
            if (this.$refs.componente) {
                this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },

        atualizar() {
            this.syncUrlFiltros()
            if (this.$refs.componente) {
                this.$refs && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
                this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        }
    }
}
</script>

<style scoped>
/* Empty state */
.historico-admissao .empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
    border-radius: 12px;
    border: 1px dashed #dee2e6;
}
.historico-admissao .empty-state-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: #6c757d;
}
.historico-admissao .empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}
.historico-admissao .empty-state-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0;
}

/* Cards list */
.historico-admissao .cards-lista {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.historico-admissao .solicitacao-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 0;
    transition: all 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border-left: 4px solid #6c757d;
    overflow: hidden;
}
.historico-admissao .solicitacao-card.card-status-admitido {
    border-left-color: #28a745;
}
.historico-admissao .solicitacao-card.card-status-demitido {
    border-left-color: #dc3545;
}
.historico-admissao .solicitacao-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    border-color: #ced4da;
    transform: translateY(-1px);
}

.historico-admissao .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
    border-bottom: 1px solid #f1f3f5;
}
.historico-admissao .card-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    overflow: hidden;
    min-width: 0;
}
.historico-admissao .card-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}
.historico-admissao .colab-foto-3x4-wrap {
    position: relative;
    width: 40px;
    height: 53px;
    flex-shrink: 0;
}
.historico-admissao .colab-foto-3x4 {
    display: block;
    width: 40px;
    height: 53px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.12);
    background: #eef1f4;
    flex-shrink: 0;
}
.historico-admissao .colab-foto-3x4-wrap > img.colab-foto-3x4 {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.historico-admissao .colab-foto-3x4-wrap > img.colab-foto-3x4.is-ready {
    opacity: 1;
}
.historico-admissao .colab-foto-3x4--vazia,
.historico-admissao .colab-foto-3x4--loading {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #8a93a0;
    font-size: 0.85rem;
}
.historico-admissao .colab-foto-3x4--loading {
    position: absolute;
    inset: 0;
    background: #eef1f4;
}
.historico-admissao .colaborador-principal {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    font-size: 1rem;
    color: #212529;
    overflow: hidden;
    min-width: 0;
    line-height: 1.25;
}
.historico-admissao .colaborador-principal strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 600;
    max-width: 100%;
}
.historico-admissao .colaborador-cpf {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6c757d;
    white-space: nowrap;
    margin-top: 0.1rem;
}
.historico-admissao .colaborador-meta-id {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    font-size: 0.75rem;
    line-height: 1.35;
    margin-top: 0.1rem;
}
.historico-admissao .historico-status-id {
    font-weight: 600;
    color: #6c757d;
}
.historico-admissao .historico-status-texto {
    font-weight: 600;
    color: #6c757d;
}
.historico-admissao .historico-status-texto--admitido {
    color: #1e7e34;
}
.historico-admissao .historico-status-texto--demitido {
    color: #dc3545;
}
.historico-admissao .historico-status-texto--outro {
    color: #6c757d;
}

/* Cabeçalho compacto da modal — identidade + meta em linha */
.historico-admissao .historico-modal-perfil {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin: 0 0 0.75rem;
    padding: 0.7rem 0.9rem;
    background: #fff;
    border: 1px solid #e4e8ec;
    border-radius: 8px;
    border-left: 4px solid #174257;
}
.historico-admissao .historico-modal-perfil--aprovado {
    border-left-color: #28a745;
}
.historico-admissao .historico-modal-perfil--reprovado {
    border-left-color: #dc3545;
}
.historico-admissao .historico-modal-perfil--neutro {
    border-left-color: #adb5bd;
}
.historico-admissao .historico-modal-perfil__foto-wrap {
    position: relative;
    width: 48px;
    height: 64px;
    flex-shrink: 0;
}
.historico-admissao .historico-modal-perfil__foto {
    display: block;
    width: 48px;
    height: 64px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    background: #eef1f4;
}
.historico-admissao .historico-modal-perfil__foto-wrap > img.historico-modal-perfil__foto {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.historico-admissao .historico-modal-perfil__foto-wrap > img.historico-modal-perfil__foto.is-ready {
    opacity: 1;
}
.historico-admissao .historico-modal-perfil__foto--vazia,
.historico-admissao .historico-modal-perfil__foto--loading {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #8a93a0;
    font-size: 0.9rem;
}
.historico-admissao .historico-modal-perfil__foto--loading {
    position: absolute;
    inset: 0;
    background: #eef1f4;
}
.historico-admissao .historico-modal-perfil__corpo {
    min-width: 0;
    flex: 1;
}
.historico-admissao .historico-modal-perfil__titulo {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.15rem;
    margin-bottom: 0.35rem;
}
.historico-admissao .historico-modal-perfil__nome {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.3;
    color: #174257;
    word-break: break-word;
}
.historico-admissao .historico-modal-perfil__meta-id {
    margin: 0;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0;
    font-size: 0.8125rem;
    line-height: 1.35;
}
.historico-admissao .historico-modal-perfil__linhas {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.historico-admissao .historico-modal-perfil__linha {
    margin: 0;
    font-size: 0.8125rem;
    line-height: 1.45;
    color: #495057;
}
.historico-admissao .historico-modal-perfil__item {
    display: inline;
}
.historico-admissao .historico-modal-perfil__k {
    font-weight: 600;
    color: #6c757d;
    margin-right: 0.2rem;
}
.historico-admissao .historico-modal-perfil__sep {
    margin: 0 0.4rem;
    color: #ced4da;
}
.historico-admissao .historico-modal-tabs {
    border-bottom: 1px solid #174257 !important;
}
@media (max-width: 575.98px) {
    .historico-admissao .historico-modal-perfil {
        gap: 0.65rem;
        padding: 0.65rem 0.75rem;
    }
}

.historico-admissao .btn-actions-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #dee2e6;
    color: #6c757d;
    transition: all 0.2s ease;
    text-decoration: none;
    flex-shrink: 0;
}
.historico-admissao .btn-actions-compact:hover {
    background: #174257;
    border-color: #174257;
    color: white;
    transform: rotate(90deg);
}

.historico-admissao .card-details-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.5rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f3f5;
}
.historico-admissao .card-details-row:last-child {
    border-bottom: none;
}
.historico-admissao .card-details-main {
    padding-top: 0.75rem;
}
.historico-admissao .card-details-fixas {
    background: #fafbfc;
    padding: 0.75rem 1.25rem;
}
.historico-admissao .detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.813rem;
    min-width: 0;
}
.historico-admissao .detail-item i:first-child {
    flex-shrink: 0;
    font-size: 0.875rem;
    color: #6c757d;
}
.historico-admissao .detail-label {
    font-weight: 500;
    color: #6c757d;
    white-space: nowrap;
}
.historico-admissao .detail-value {
    color: #212529;
    font-weight: 400;
}
.historico-admissao .detail-value-empty {
    color: #adb5bd;
    font-style: italic;
}
.historico-admissao .detail-item-action {
    margin-left: auto;
}

.historico-admissao .dropdown-menu-custom {
    min-width: 11rem;
    padding: 0.25rem 0;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border: 1px solid #e9ecef;
    border-radius: 8px;
}
.historico-admissao .dropdown-menu-custom .dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
}
.historico-admissao .dropdown-menu-custom .dropdown-item i {
    width: 1.25rem;
    text-align: center;
}
.historico-admissao .dropdown-menu-custom .dropdown-item:hover {
    background-color: #f8f9fa;
    color: #174257;
}

@media (max-width: 768px) {
    .historico-admissao .card-header-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
    }
    .historico-admissao .card-right {
        width: 100%;
        justify-content: flex-end;
    }
    .historico-admissao .card-details-row {
        flex-direction: column;
        gap: 0.5rem;
    }
    .historico-admissao .detail-item-action {
        margin-left: 0;
        width: 100%;
    }
    .historico-admissao .detail-item-action .btn {
        width: 100%;
    }
}
</style>
