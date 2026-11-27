<template>
    <div>
        <ModalComponent
            ref="modalForm"
            id="janelaFormNotificacaoGrupo"
            :fechar="!salvando"
            :mostrar-botao-fechar-no-rodape="false"
            size="g"
            :titulo="tituloModal"
        >
            <template #conteudo>
                <preload class="text-center" v-if="carregandoModal"></preload>
                <div v-if="!carregandoModal" class="mybp-modal-form mybp-filtros-compactos">
                    <p class="mybp-campo-obrigatorio-legenda mybp-modal-legenda">
                        Campos com <span class="text-danger">*</span> são obrigatórios.
                    </p>

                    <fieldset class="mybp-modal-secao">
                        <legend>Dados do grupo</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="notif-form-nome">
                                        Nome <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        id="notif-form-nome"
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.nome"
                                        placeholder="Ex.: RH Matriz"
                                        autocomplete="off"
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="notif-form-tipo">
                                        Tipo <span class="text-danger">*</span>
                                    </label>
                                    <div v-if="editandoId" class="mybp-modal-campo-fix">
                                        <input
                                            id="notif-form-tipo"
                                            type="text"
                                            class="form-control form-control-sm"
                                            :value="labelTipo(form.tipo_codigo)"
                                            readonly
                                            tabindex="-1"
                                        />
                                        <small class="form-text text-muted">Definido no cadastro — não pode mudar.</small>
                                    </div>
                                    <div v-else class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboTipoForm"
                                            instance-id="notif-form-tipo"
                                            input-id="notif-form-tipo"
                                            v-model="form.tipo_codigo"
                                            :options="opcoesTipoForm"
                                            placeholder-blur="Selecione o tipo"
                                            empty-message="Nenhum tipo editável."
                                            :max-results="20"
                                            @opening="fecharOutrosComboboxes('notif-form-tipo')"
                                            @select="onSelectTipoForm"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label" for="notif-form-escopo">
                                        Abrange <span class="text-danger">*</span>
                                    </label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            ref="comboEscopo"
                                            instance-id="notif-form-escopo"
                                            input-id="notif-form-escopo"
                                            v-model="form.escopo_cc"
                                            :options="opcoesEscopo"
                                            placeholder-blur="Selecione a cobertura"
                                            empty-message="Nenhuma opção."
                                            :max-results="20"
                                            @opening="fecharOutrosComboboxes('notif-form-escopo')"
                                        />
                                    </div>
                                    <small v-if="textoAjudaEscopo" class="form-text text-muted">{{ textoAjudaEscopo }}</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mybp-modal-status-bar">
                                    <span class="mybp-modal-status-bar__label">Envio de e-mails</span>
                                    <div
                                        class="mybp-modal-status-pills"
                                        role="group"
                                        aria-label="Status do envio de e-mails"
                                    >
                                        <button
                                            type="button"
                                            class="mybp-modal-status-pill"
                                            :class="{ 'is-ativo': form.ativo }"
                                            :aria-pressed="form.ativo ? 'true' : 'false'"
                                            @click="form.ativo = true"
                                        >
                                            Ativo
                                        </button>
                                        <button
                                            type="button"
                                            class="mybp-modal-status-pill"
                                            :class="{ 'is-inativo': !form.ativo }"
                                            :aria-pressed="!form.ativo ? 'true' : 'false'"
                                            @click="form.ativo = false"
                                        >
                                            Inativo
                                        </button>
                                    </div>
                                    <span class="mybp-modal-status-bar__hint">
                                        {{ form.ativo ? 'Este grupo envia e-mails.' : 'Não envia e-mails enquanto inativo.' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset v-if="form.escopo_cc === 'especificos'" class="mybp-modal-secao">
                        <legend>
                            Centros de custo
                            <span v-if="centrosSelecionados.length" class="mybp-modal-secao-count">
                                ({{ centrosSelecionados.length }})
                            </span>
                        </legend>
                        <p class="mybp-modal-legenda mb-2">
                            Busque e selecione os centros atendidos por este grupo. A escolha é adicionada na hora.
                        </p>
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="notif-form-cc">Adicionar centro de custo</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    ref="comboCc"
                                    instance-id="notif-form-cc"
                                    input-id="notif-form-cc"
                                    v-model="ccCombo"
                                    :options="opcoesCcDisponiveis"
                                    :disabled="!opcoesCcDisponiveis.length"
                                    placeholder-blur="Digite para buscar e selecionar"
                                    placeholder-focus="Digite para filtrar…"
                                    empty-message="Nenhum centro disponível ou todos já foram adicionados."
                                    :max-results="50"
                                    @opening="fecharOutrosComboboxes('notif-form-cc')"
                                    @select="onSelectCc"
                                />
                            </div>
                        </div>

                        <div
                            v-if="centrosSelecionados.length"
                            class="mybp-modal-chip-lista"
                            role="list"
                            aria-label="Centros de custo selecionados"
                        >
                            <div
                                v-for="cc in centrosSelecionados"
                                :key="cc.value"
                                class="mybp-modal-chip"
                                role="listitem"
                            >
                                <span class="mybp-modal-chip__texto" :title="cc.label">{{ cc.label }}</span>
                                <span v-if="metaCc(cc)" class="mybp-modal-chip__meta">{{ metaCc(cc) }}</span>
                                <button
                                    type="button"
                                    class="mybp-modal-chip__remove"
                                    :aria-label="`Remover ${cc.label}`"
                                    @click="removerCc(cc.value)"
                                >
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div v-else class="mybp-modal-empty-hint">
                            <i class="fas fa-building text-muted mr-1" aria-hidden="true"></i>
                            Nenhum centro selecionado. Busque e escolha ao menos um.
                        </div>

                        <div class="mybp-modal-chip-acoes" v-if="centrosCusto.length">
                            <button
                                v-if="opcoesCcDisponiveis.length > 1"
                                type="button"
                                class="btn btn-link btn-sm p-0"
                                @click="adicionarTodosCc"
                            >
                                Adicionar todos restantes ({{ opcoesCcDisponiveis.length }})
                            </button>
                            <button
                                v-if="empresaTemFiliais && qtdCcMatrizDisponivel"
                                type="button"
                                class="btn btn-link btn-sm p-0"
                                @click="adicionarCcMatriz"
                            >
                                Adicionar só matriz
                            </button>
                            <button
                                v-if="empresaTemFiliais && qtdCcFilialDisponivel"
                                type="button"
                                class="btn btn-link btn-sm p-0"
                                @click="adicionarCcFiliais"
                            >
                                Adicionar só filiais
                            </button>
                            <button
                                v-if="centrosSelecionados.length"
                                type="button"
                                class="btn btn-link btn-sm p-0 text-muted"
                                @click="limparCc"
                            >
                                Limpar
                            </button>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>
                            Destinatários
                            <span v-if="destinatariosSelecionados.length" class="mybp-modal-secao-count">
                                ({{ destinatariosSelecionados.length }})
                            </span>
                        </legend>
                        <p class="mybp-modal-legenda mb-2">
                            Quem recebe os e-mails deste grupo (apenas RH ativos).
                        </p>

                        <div class="form-group mybp-filtro-campo mb-2">
                            <label class="mybp-label" for="notif-form-user">Buscar e adicionar</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    ref="comboUser"
                                    instance-id="notif-form-user"
                                    input-id="notif-form-user"
                                    v-model="userCombo"
                                    :options="opcoesCandidatosDisponiveis"
                                    :disabled="!opcoesCandidatosDisponiveis.length"
                                    placeholder-blur="Digite nome ou e-mail e selecione"
                                    placeholder-focus="Digite para filtrar…"
                                    empty-message="Nenhum RH elegível restante ou lista vazia."
                                    :max-results="50"
                                    @opening="fecharOutrosComboboxes('notif-form-user')"
                                    @select="onSelectUser"
                                />
                            </div>
                        </div>

                        <div
                            v-if="destinatariosSelecionados.length"
                            class="mybp-modal-selecionados"
                        >
                            <div class="mybp-modal-selecionados__topo">
                                <span class="mybp-modal-selecionados__titulo">
                                    Selecionados
                                    <strong>{{ destinatariosSelecionados.length }}</strong>
                                </span>
                                <div class="mybp-modal-selecionados__acoes">
                                    <button
                                        v-if="opcoesCandidatosDisponiveis.length > 0"
                                        type="button"
                                        class="btn btn-link btn-sm p-0"
                                        @click="adicionarTodosUsers"
                                    >
                                        Adicionar restantes ({{ opcoesCandidatosDisponiveis.length }})
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-link btn-sm p-0 text-muted"
                                        @click="limparUsers"
                                    >
                                        Limpar todos
                                    </button>
                                </div>
                            </div>
                            <div
                                class="mybp-modal-chip-lista mybp-modal-chip-lista--pessoas"
                                role="list"
                                aria-label="Destinatários selecionados"
                            >
                                <div
                                    v-for="u in destinatariosSelecionados"
                                    :key="u.id"
                                    class="mybp-modal-chip mybp-modal-chip--pessoa"
                                    role="listitem"
                                >
                                    <div class="mybp-modal-chip__corpo">
                                        <span
                                            class="mybp-modal-chip__texto"
                                            :title="u.nome || u.label"
                                        >{{ formatNomeExibicao(u.nome || u.label) }}</span>
                                        <span
                                            v-if="u.login"
                                            class="mybp-modal-chip__meta"
                                            :title="u.login"
                                        >{{ u.login }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        class="mybp-modal-chip__remove"
                                        :aria-label="`Remover ${formatNomeExibicao(u.nome || u.label)}`"
                                        @click="removerUser(u.id)"
                                    >
                                        <i class="fa fa-times" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="mybp-modal-empty-hint mybp-modal-empty-hint--painel">
                            <i class="fas fa-user-plus text-muted mr-1" aria-hidden="true"></i>
                            Ninguém selecionado ainda. Busque acima para adicionar.
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm mr-1 btn-secondary" @click="fecharModal" :disabled="salvando">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-sm mr-1 btn-primary" @click="salvar" :disabled="salvando || carregandoModal">
                    <span v-if="salvando" class="spinner-border spinner-border-sm me-1" role="status"></span>
                    <i v-else class="fa fa-save"></i> Salvar
                </button>
            </template>
        </ModalComponent>

        <div id="componente">
            <FiltroListagem
                class="mt-2 mybp-filtros-compactos"
                @submit="onSubmitFiltro"
                :mostrar-limpar-filtros="temFiltrosAtivos"
                :desabilitado="carregando"
                @limpar="limparFiltros"
            >
                <template #filtros>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="notif-filtro-busca">Buscar</label>
                            <input
                                id="notif-filtro-busca"
                                type="text"
                                class="form-control form-control-sm"
                                placeholder="Buscar por nome ou ID"
                                autocomplete="off"
                                :disabled="carregando"
                                v-model="filtros.busca"
                            />
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="notif-filtro-tipo">Tipo</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    ref="comboFiltroTipo"
                                    instance-id="notif-filtro-tipo"
                                    input-id="notif-filtro-tipo"
                                    v-model="filtros.tipo_codigo"
                                    :options="opcoesTipo"
                                    :disabled="carregando"
                                    placeholder-blur="Selecione o tipo"
                                    empty-message="Nenhum tipo."
                                    :max-results="20"
                                    @opening="fecharOutrosComboboxes('notif-filtro-tipo')"
                                    @select="onSelectFiltro"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" for="notif-filtro-status">Status</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    ref="comboFiltroStatus"
                                    instance-id="notif-filtro-status"
                                    input-id="notif-filtro-status"
                                    v-model="filtros.status"
                                    :options="opcoesStatus"
                                    :disabled="carregando"
                                    placeholder-blur="Todos os status"
                                    empty-message="Nenhuma opção."
                                    :max-results="10"
                                    @opening="fecharOutrosComboboxes('notif-filtro-status')"
                                    @select="onSelectFiltro"
                                />
                            </div>
                        </div>
                    </div>
                </template>
                <template #acoes>
                    <button type="submit" class="btn btn-sm btn-success" :disabled="carregando">
                        <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-secondary"
                        :disabled="carregando || !tipoAtualEditavel"
                        @click="abrirNovo"
                    >
                        <i class="fa fa-plus"></i> Cadastrar
                    </button>
                </template>
            </FiltroListagem>

            <div class="d-flex justify-content-end my-2">
                <mybp-visualizacao-listagem v-model="visualizacao" tela="config-notificacoes"></mybp-visualizacao-listagem>
            </div>

            <div class="alert alert-warning border-0 py-2" role="alert" v-if="!carregando && avisoSemGrupos && tipoAtualEditavel">
                <i class="fa fa-exclamation-triangle me-1"></i>
                <strong>Atenção:</strong> sem grupo ativo para este tipo, <strong>nenhum e-mail RH</strong> será enviado nas aprovações.
            </div>

            <div class="alert alert-info border-0 py-2" role="alert" v-if="!tipoAtualEditavel">
                <i class="fa fa-info-circle me-1"></i>
                Este tipo está no catálogo e ainda não permite cadastro de destinatários nesta versão.
            </div>

            <preload class="text-center" v-if="carregando"></preload>

            <div class="alert alert-warning text-center" v-show="!carregando && lista.length === 0 && tipoAtualEditavel">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>

            <div class="mybp-cards-lista" v-show="!carregando && lista.length > 0 && visualizacao === 'lista'">
                <div
                    class="mybp-card mybp-card--acao"
                    :class="{
                        'mybp-card--inativo': !item.ativo,
                        'mybp-card--alerta': !(item.destinatarios_count > 0)
                    }"
                    v-for="item in lista"
                    :key="item.id"
                    role="button"
                    tabindex="0"
                    :aria-label="`Abrir grupo ${item.nome}`"
                    @click="abrirEditar(item.id)"
                    @keydown.enter.prevent="abrirEditar(item.id)"
                >
                    <div class="mybp-card-header-row mybp-notif-header">
                        <div class="mybp-card-left mybp-card-cabecalho">
                            <span class="mybp-badge-id">#{{ item.id }}</span>
                            <div class="mybp-card-cabecalho__corpo">
                                <div class="mybp-card-cabecalho__topo">
                                    <strong class="mybp-card-cabecalho__nome">{{ item.nome }}</strong>
                                </div>
                                <div class="mybp-card-cabecalho__metas" aria-label="Resumo do grupo">
                                    <span class="mybp-card-meta">
                                        <span class="mybp-card-meta__label">Tipo</span>
                                        <span class="mybp-card-meta__valor">{{ labelTipo(item.tipo_codigo) }}</span>
                                    </span>
                                    <span class="mybp-card-meta">
                                        <span class="mybp-card-meta__label">Abrange</span>
                                        <span class="mybp-card-meta__valor">{{ labelEscopoCard(item) }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mybp-card-right" @click.stop>
                            <bt-ativo
                                :rota="`configuracoes/notificacoes/grupos/${item.id}/ativa-desativa`"
                                :model="item"
                                @atualizou="buscar"
                            ></bt-ativo>
                            <div class="dropdown" :class="{ show: dropdownId === item.id }">
                                <a
                                    class="mybp-btn-acoes-compact"
                                    href="#"
                                    role="button"
                                    aria-haspopup="true"
                                    :aria-expanded="dropdownId === item.id ? 'true' : 'false'"
                                    :aria-label="`Ações do grupo ${item.nome}`"
                                    @click.prevent.stop="toggleDropdown(item.id, $event)"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div
                                    class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                    :class="{ show: dropdownId === item.id }"
                                    @click="fecharDropdown"
                                >
                                    <a class="dropdown-item" href="javascript://" @click.prevent="abrirEditar(item.id)">
                                        <i class="fa fa-edit mr-1"></i> Editar
                                    </a>
                                    <a class="dropdown-item text-danger" href="javascript://" @click.prevent="excluir(item)">
                                        <i class="fa fa-trash mr-1"></i> Excluir
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="item.destinatarios_count > 0"
                        class="mybp-notif-quem"
                    >
                        <div class="mybp-notif-quem__titulo">
                            <i class="fas fa-envelope-open-text text-muted" aria-hidden="true"></i>
                            <span>
                                {{ item.destinatarios_count }}
                                {{ item.destinatarios_count === 1 ? 'pessoa recebe' : 'pessoas recebem' }}
                                estes e-mails
                            </span>
                        </div>
                        <p
                            v-if="textoAmostraDestinatarios(item)"
                            class="mybp-notif-quem__lista mb-0"
                            :title="textoAmostraDestinatarios(item)"
                        >
                            {{ textoAmostraDestinatarios(item) }}
                        </p>
                    </div>
                    <div v-else class="mybp-notif-quem mybp-notif-quem--vazio">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        <span>Ninguém recebe e-mail neste grupo — adicione destinatários.</span>
                    </div>

                    <p
                        v-if="item.escopo_cc === 'especificos'"
                        class="mybp-card-texto-resumo mb-0 mt-2"
                    >
                        <i class="fas fa-building text-muted mr-1" aria-hidden="true"></i>
                        Centros: {{ textoAmostraCentros(item) }}
                    </p>
                </div>
            </div>

            <div class="table-responsive mybp-tabela-wrap" v-show="!carregando && lista.length > 0 && visualizacao === 'tabela'">
                <table class="tabela mybp-tabela">
                    <thead>
                        <tr>
                            <th>Cód</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Escopo</th>
                            <th>Destinatários</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in lista" :key="'t-' + item.id">
                            <td data-label="Cód">{{ item.id }}</td>
                            <td class="mybp-col-texto" data-label="Nome">{{ item.nome }}</td>
                            <td class="mybp-col-texto" data-label="Tipo">{{ labelTipo(item.tipo_codigo) }}</td>
                            <td class="mybp-col-texto" data-label="Escopo">{{ labelEscopo(item.escopo_cc) }}</td>
                            <td class="mybp-col-texto" data-label="Destinatários">
                                {{ item.destinatarios_count || 0 }}
                                <small v-if="textoAmostraDestinatarios(item)" class="d-block text-muted">
                                    {{ textoAmostraDestinatarios(item) }}
                                </small>
                            </td>
                            <td data-label="Status" @click.stop>
                                <bt-ativo
                                    :rota="`configuracoes/notificacoes/grupos/${item.id}/ativa-desativa`"
                                    :model="item"
                                    @atualizou="buscar"
                                ></bt-ativo>
                            </td>
                            <td data-label="Ações">
                                <div class="dropdown d-inline-block" :class="{ show: dropdownId === item.id }">
                                    <a
                                        class="mybp-btn-acoes-compact"
                                        href="#"
                                        role="button"
                                        @click.prevent.stop="toggleDropdown(item.id, $event)"
                                    >
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div
                                        class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                        :class="{ show: dropdownId === item.id }"
                                        @click="fecharDropdown"
                                    >
                                        <a class="dropdown-item" href="javascript://" @click.prevent="abrirEditar(item.id)">
                                            <i class="fa fa-edit mr-1"></i> Editar
                                        </a>
                                        <a class="dropdown-item text-danger" href="javascript://" @click.prevent="excluir(item)">
                                            <i class="fa fa-trash mr-1"></i> Excluir
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import ModalComponent from '../../Modal.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import FiltroListagem from '../../ui/FiltroListagem.vue'
import { lerVisualizacaoListagem } from '../../../utils/visualizacaoListagem'
import { posicionarMenuAcoes } from '../../../utils/posicionarMenuAcoes'

const BASE = `${URL_ADMIN}/configuracoes/notificacoes`
const visualizacao = ref(lerVisualizacaoListagem('config-notificacoes', 'lista'))
const carregando = ref(false)
const carregandoModal = ref(false)
const salvando = ref(false)
const lista = ref([])
const tipos = ref([])
const candidatos = ref([])
const centrosCusto = ref([])
const temFiliaisApi = ref(false)
const avisoSemGrupos = ref(false)
const dropdownId = ref(null)
const modalForm = ref(null)
const comboFiltroTipo = ref(null)
const comboFiltroStatus = ref(null)
const comboTipoForm = ref(null)
const comboEscopo = ref(null)
const comboCc = ref(null)
const comboUser = ref(null)
const editandoId = ref(null)
const ccCombo = ref('')
const userCombo = ref('')

const filtros = reactive({
    busca: '',
    tipo_codigo: 'aprovacao_rh',
    status: '',
    pages: 50
})

const form = reactive({
    tipo_codigo: 'aprovacao_rh',
    nome: '',
    ativo: true,
    escopo_cc: 'todos',
    user_ids: [],
    centro_custo_ids: []
})

const ESCOPO_LABELS = {
    todos: 'Toda a empresa',
    matriz: 'Somente matriz',
    filiais: 'Somente filiais (CCs não-matriz)',
    especificos: 'Centros de custo específicos'
}

const TODAS_OPCOES_ESCOPO = [
    { value: 'todos', label: ESCOPO_LABELS.todos },
    { value: 'matriz', label: ESCOPO_LABELS.matriz },
    { value: 'filiais', label: ESCOPO_LABELS.filiais },
    { value: 'especificos', label: ESCOPO_LABELS.especificos }
]

const opcoesStatus = [
    { value: '', label: 'Todos os status' },
    { value: 'ativo', label: 'Ativos' },
    { value: 'inativo', label: 'Inativos' }
]

const opcoesTipo = computed(() =>
    tipos.value.map((t) => ({
        value: t.codigo,
        label: t.editavel ? t.nome : `${t.nome} (em breve)`,
        meta: t.editavel ? 'Editável' : 'Catálogo'
    }))
)

const opcoesTipoForm = computed(() =>
    tipos.value
        .filter((t) => t.editavel || t.codigo === form.tipo_codigo)
        .map((t) => ({
            value: t.codigo,
            label: t.nome,
            meta: t.editavel ? 'Editável' : 'Somente leitura'
        }))
)

const tipoAtual = computed(() => tipos.value.find((t) => t.codigo === filtros.tipo_codigo) || null)
const tipoAtualEditavel = computed(() => {
    if (tipoAtual.value) {
        return Boolean(tipoAtual.value.editavel)
    }
    return filtros.tipo_codigo === 'aprovacao_rh'
})
const temFiltrosAtivos = computed(() =>
    !!(filtros.busca || filtros.status || (filtros.tipo_codigo && filtros.tipo_codigo !== 'aprovacao_rh'))
)
const tituloModal = computed(() =>
    editandoId.value ? 'Alterando grupo de notificação' : 'Cadastrando grupo de notificação'
)

/** Empresa com ao menos um CC de filial: mostra atalhos/opções de matriz vs filiais. */
const empresaTemFiliais = computed(() => {
    if (temFiliaisApi.value) return true
    return centrosCusto.value.some((c) => c.matriz === false)
})

const opcoesEscopo = computed(() => {
    if (empresaTemFiliais.value) return TODAS_OPCOES_ESCOPO
    return TODAS_OPCOES_ESCOPO.filter((o) => o.value !== 'matriz' && o.value !== 'filiais')
})

const textoAjudaEscopo = computed(() => {
    // Em "toda a empresa" o próprio label já basta — evita ruído.
    if (form.escopo_cc === 'especificos') {
        return 'Só dos centros que você selecionar abaixo.'
    }
    if (form.escopo_cc === 'matriz') {
        return 'Somente centros da matriz.'
    }
    if (form.escopo_cc === 'filiais') {
        return 'Somente centros de filiais.'
    }
    return ''
})

const centrosSelecionados = computed(() =>
    centrosCusto.value.filter((c) => form.centro_custo_ids.map(String).includes(String(c.value)))
)
const opcoesCcDisponiveis = computed(() =>
    centrosCusto.value.filter((c) => !form.centro_custo_ids.map(String).includes(String(c.value)))
)
const qtdCcMatrizDisponivel = computed(() =>
    opcoesCcDisponiveis.value.filter((c) => c.matriz).length
)
const qtdCcFilialDisponivel = computed(() =>
    opcoesCcDisponiveis.value.filter((c) => !c.matriz).length
)
const destinatariosSelecionados = computed(() =>
    candidatos.value.filter((u) => form.user_ids.includes(u.id))
)
const opcoesCandidatosDisponiveis = computed(() =>
    candidatos.value
        .filter((u) => !form.user_ids.includes(u.id))
        .map((u) => ({ value: String(u.id), label: u.label }))
)

function formatNomeExibicao(nome) {
    const texto = String(nome || '').trim()
    if (!texto) return 'Não informado'
    const letras = texto.replace(/[^A-Za-zÀ-ÿ]/g, '')
    if (!letras) return texto
    const soMaiusculas = letras === letras.toLocaleUpperCase('pt-BR')
    if (!soMaiusculas) return texto
    const particulas = new Set(['da', 'das', 'de', 'do', 'dos', 'e'])
    return texto
        .toLocaleLowerCase('pt-BR')
        .split(/\s+/)
        .map((parte, idx) => {
            if (idx > 0 && particulas.has(parte)) return parte
            return parte.replace(/(^|['-])(\S)/g, (_, sep, ch) => sep + ch.toLocaleUpperCase('pt-BR'))
        })
        .join(' ')
}

function labelEscopo(escopo) {
    return ESCOPO_LABELS[escopo] || escopo || 'Não informado'
}

function labelEscopoCard(item) {
    if (item.escopo_cc === 'especificos') {
        const total =
            (item.centros_custo || []).length ||
            (item.centros_custo_amostra || []).length ||
            0
        if (total === 1) return '1 centro específico'
        if (total > 1) return `${total} centros específicos`
        return 'Centros específicos'
    }
    if (item.escopo_cc === 'matriz') return 'Somente matriz'
    if (item.escopo_cc === 'filiais') return 'Somente filiais'
    return 'Toda a empresa'
}

function metaCc(cc) {
    if (!empresaTemFiliais.value) return ''
    if (cc.matriz === true) return 'Matriz'
    if (cc.matriz === false) return 'Filial'
    return ''
}

function normalizarEscopoSemFiliais() {
    if (empresaTemFiliais.value) return
    if (form.escopo_cc === 'matriz' || form.escopo_cc === 'filiais') {
        form.escopo_cc = 'todos'
    }
}

function labelTipo(codigo) {
    return tipos.value.find((t) => t.codigo === codigo)?.nome || codigo || 'Não informado'
}

function formatarParteNome(parte) {
    const p = String(parte || '')
    if (!p) return ''
    return p.charAt(0).toUpperCase() + p.slice(1).toLowerCase()
}

function encurtarNome(nome) {
    const partes = String(nome || '')
        .trim()
        .split(/\s+/)
        .filter(Boolean)
    if (!partes.length) return 'Não informado'
    if (partes.length === 1) return formatarParteNome(partes[0])
    return `${formatarParteNome(partes[0])} ${formatarParteNome(partes[partes.length - 1])}`
}

function textoAmostraDestinatarios(item) {
    const amostra = item.destinatarios_amostra || []
    if (!amostra.length) return ''
    const total = Number(item.destinatarios_count || 0)
    const nomes = amostra.map(encurtarNome)
    if (total > nomes.length) {
        return `${nomes.join(' · ')} e mais ${total - nomes.length}`
    }
    return nomes.join(' · ')
}

function textoAmostraCentros(item) {
    const amostra = item.centros_custo_amostra || []
    if (!amostra.length) return 'Não informado'
    const total = (item.centros_custo || []).length || amostra.length
    const resto = total > amostra.length ? ` +${total - amostra.length}` : ''
    return amostra.join(', ') + resto
}

function fecharOutrosComboboxes(manter) {
    const mapa = {
        'notif-filtro-tipo': comboFiltroTipo,
        'notif-filtro-status': comboFiltroStatus,
        'notif-form-tipo': comboTipoForm,
        'notif-form-escopo': comboEscopo,
        'notif-form-cc': comboCc,
        'notif-form-user': comboUser
    }
    Object.entries(mapa).forEach(([id, refCombo]) => {
        if (id !== manter && refCombo.value?.close) {
            refCombo.value.close()
        }
    })
}

function toggleDropdown(id, event) {
    dropdownId.value = dropdownId.value === id ? null : id
    if (dropdownId.value && event) {
        posicionarMenuAcoes(event)
    }
}

function fecharDropdown() {
    dropdownId.value = null
}

function onClickFora(event) {
    if (event?.target?.closest?.('.dropdown')) return
    if (comboFiltroTipo.value?.containsTarget?.(event.target)) return
    if (comboFiltroStatus.value?.containsTarget?.(event.target)) return
    if (comboTipoForm.value?.containsTarget?.(event.target)) return
    if (comboEscopo.value?.containsTarget?.(event.target)) return
    if (comboCc.value?.containsTarget?.(event.target)) return
    if (comboUser.value?.containsTarget?.(event.target)) return
    dropdownId.value = null
    fecharOutrosComboboxes(null)
}

function resetForm() {
    form.tipo_codigo = filtros.tipo_codigo || 'aprovacao_rh'
    form.nome = ''
    form.ativo = true
    form.escopo_cc = 'todos'
    form.user_ids = []
    form.centro_custo_ids = []
    ccCombo.value = ''
    userCombo.value = ''
    editandoId.value = null
}

async function carregarTipos() {
    const { data } = await axios.get(`${BASE}/tipos`)
    tipos.value = data.tipos || []
    if (!filtros.tipo_codigo && tipos.value.length) {
        filtros.tipo_codigo = tipos.value[0].codigo
    }
}

async function carregarCandidatos(tipoCodigo = null) {
    const tipo = tipoCodigo || form.tipo_codigo || filtros.tipo_codigo || 'aprovacao_rh'
    const tipoInfo = tipos.value.find((t) => t.codigo === tipo)
    if (tipos.value.length && tipoInfo && !tipoInfo.editavel) {
        candidatos.value = []
        return
    }
    const { data } = await axios.get(`${BASE}/candidatos`, { params: { tipo_codigo: tipo } })
    candidatos.value = data.candidatos || []
}

async function carregarCentros() {
    const { data } = await axios.get(`${BASE}/centros-custo`)
    centrosCusto.value = data.centros_custo || []
    temFiliaisApi.value = !!data.tem_filiais
}

async function buscar() {
    const tipoCodigo = filtros.tipo_codigo || 'aprovacao_rh'
    if (!tipoCodigo) {
        lista.value = []
        avisoSemGrupos.value = false
        return
    }
    if (tipos.value.length && !tipoAtualEditavel.value) {
        lista.value = []
        avisoSemGrupos.value = false
        return
    }
    carregando.value = true
    dropdownId.value = null
    try {
        const { data } = await axios.get(`${BASE}/grupos`, {
            params: {
                tipo_codigo: tipoCodigo,
                busca: filtros.busca,
                status: filtros.status,
                pages: filtros.pages
            }
        })
        lista.value = data.itens || []
        avisoSemGrupos.value = !!data.aviso_sem_grupos
    } catch (e) {
        lista.value = []
        mostraErro('', 'Erro ao carregar grupos de notificação')
    } finally {
        carregando.value = false
    }
}

function onSubmitFiltro() {
    buscar()
}

function onSelectFiltro() {
    buscar()
}

function limparFiltros() {
    filtros.busca = ''
    filtros.status = ''
    filtros.tipo_codigo = 'aprovacao_rh'
    buscar()
}

async function abrirNovo() {
    if (!tipoAtualEditavel.value) return
    resetForm()
    carregandoModal.value = true
    modalForm.value?.abrirModal?.()
    try {
        await Promise.all([carregarCandidatos(), carregarCentros()])
        normalizarEscopoSemFiliais()
    } finally {
        carregandoModal.value = false
    }
}

async function abrirEditar(id) {
    dropdownId.value = null
    resetForm()
    editandoId.value = id
    carregandoModal.value = true
    modalForm.value?.abrirModal?.()
    try {
        const [, grupoResp] = await Promise.all([
            carregarCentros(),
            axios.get(`${BASE}/grupos/${id}`)
        ])
        const item = grupoResp.data.item
        form.tipo_codigo = item.tipo_codigo || filtros.tipo_codigo || 'aprovacao_rh'
        form.nome = item.nome
        form.ativo = !!item.ativo
        form.escopo_cc = item.escopo_cc
        form.user_ids = (item.user_ids || []).map((n) => Number(n))
        form.centro_custo_ids = (item.centro_custo_ids || []).map((n) => Number(n))
        await carregarCandidatos(form.tipo_codigo)
        normalizarEscopoSemFiliais()
    } catch (e) {
        mostraErro('', 'Erro ao abrir grupo')
        fecharModal()
    } finally {
        carregandoModal.value = false
    }
}

function fecharModal() {
    modalForm.value?.fecharModal?.()
    resetForm()
}

function onSelectCc() {
    const val = String(ccCombo.value || '')
    if (!val) return
    const id = Number(val)
    if (!form.centro_custo_ids.includes(id)) form.centro_custo_ids.push(id)
    ccCombo.value = ''
}

function removerCc(value) {
    form.centro_custo_ids = form.centro_custo_ids.filter((id) => String(id) !== String(value))
}

function adicionarTodosCc() {
    const atuais = new Set(form.centro_custo_ids.map(String))
    opcoesCcDisponiveis.value.forEach((c) => {
        const id = Number(c.value)
        if (!atuais.has(String(id))) form.centro_custo_ids.push(id)
    })
}
function adicionarCcMatriz() {
    const atuais = new Set(form.centro_custo_ids.map(String))
    centrosCusto.value
        .filter((c) => c.matriz)
        .forEach((c) => {
            const id = Number(c.value)
            if (!atuais.has(String(id))) form.centro_custo_ids.push(id)
        })
}
function adicionarCcFiliais() {
    const atuais = new Set(form.centro_custo_ids.map(String))
    centrosCusto.value
        .filter((c) => !c.matriz)
        .forEach((c) => {
            const id = Number(c.value)
            if (!atuais.has(String(id))) form.centro_custo_ids.push(id)
        })
}
function limparCc() {
    form.centro_custo_ids = []
}

function onSelectUser() {
    const val = String(userCombo.value || '')
    if (!val) return
    const id = Number(val)
    if (!form.user_ids.includes(id)) form.user_ids.push(id)
    userCombo.value = ''
}
function removerUser(id) {
    form.user_ids = form.user_ids.filter((x) => x !== id)
}
function adicionarTodosUsers() {
    const atuais = new Set(form.user_ids)
    candidatos.value.forEach((u) => {
        if (!atuais.has(u.id)) form.user_ids.push(u.id)
    })
}
function limparUsers() {
    form.user_ids = []
}

async function onSelectTipoForm() {
    if (editandoId.value) return
    form.user_ids = []
    userCombo.value = ''
    await carregarCandidatos(form.tipo_codigo)
}

async function salvar() {
    if (!form.tipo_codigo) {
        mostraErro('', 'Selecione o tipo de notificação')
        return
    }
    if (!form.nome.trim()) {
        mostraErro('', 'Informe o nome do grupo')
        return
    }
    salvando.value = true
    const payload = {
        tipo_codigo: form.tipo_codigo,
        nome: form.nome.trim(),
        ativo: form.ativo,
        escopo_cc: form.escopo_cc,
        user_ids: form.user_ids,
        centro_custo_ids: form.escopo_cc === 'especificos' ? form.centro_custo_ids : []
    }
    try {
        if (editandoId.value) {
            await axios.put(`${BASE}/grupos/${editandoId.value}`, payload)
            mostraSucesso('', 'Grupo atualizado')
        } else {
            await axios.post(`${BASE}/grupos`, payload)
            mostraSucesso('', 'Grupo cadastrado')
        }
        if (form.tipo_codigo && form.tipo_codigo !== filtros.tipo_codigo) {
            filtros.tipo_codigo = form.tipo_codigo
        }
        fecharModal()
        await buscar()
    } catch (e) {
        const msg =
            e?.response?.data?.errors?.tipo_codigo?.[0] ||
            e?.response?.data?.errors?.user_ids?.[0] ||
            e?.response?.data?.errors?.centro_custo_ids?.[0] ||
            e?.response?.data?.message ||
            e?.response?.data?.msg ||
            'Erro ao salvar grupo'
        mostraErro('', msg)
    } finally {
        salvando.value = false
    }
}

async function excluir(item) {
    dropdownId.value = null
    if (!confirm(`Excluir o grupo "${item.nome}"? Ele deixará de enviar e-mails.`)) return
    try {
        await axios.delete(`${BASE}/grupos/${item.id}`)
        mostraSucesso('', 'Grupo excluído')
        await buscar()
    } catch (e) {
        mostraErro('', 'Erro ao excluir grupo')
    }
}

watch(
    () => filtros.tipo_codigo,
    (novo, antigo) => {
        if (!novo || novo === antigo) return
        buscar()
    }
)

onMounted(async () => {
    document.addEventListener('click', onClickFora)
    try {
        await carregarTipos()
        if (!filtros.tipo_codigo) {
            filtros.tipo_codigo = 'aprovacao_rh'
        }
        await buscar()
    } catch (e) {
        mostraErro('', 'Erro ao iniciar configurações de notificações')
    }
})

onBeforeUnmount(() => {
    document.removeEventListener('click', onClickFora)
})
</script>
