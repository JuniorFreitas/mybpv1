<template>
    <div class="container-fluid etapas-seletivo-page">
        <div class="etapas-seletivo-escopo" :class="escopoClasse">
            <div class="etapas-seletivo-escopo__icon">
                <i :class="vagaId ? 'fas fa-briefcase' : 'fas fa-building'"></i>
            </div>
            <div class="etapas-seletivo-escopo__body">
                <strong>{{ escopoTitulo }}</strong>
                <p class="mb-0">{{ escopoDescricao }}</p>
            </div>
            <div class="etapas-seletivo-escopo__stats" v-if="!loading && etapas.length">
                <span class="badge badge-primary">{{ contagem.avanco }} avanços</span>
                <span class="badge badge-info">{{ contagem.sistema }} sistema</span>
                <span class="badge badge-danger">{{ contagem.desclassificacao }} desclass.</span>
                <span class="badge badge-secondary">{{ contagem.inativas }} inativas</span>
            </div>
        </div>

        <filtro-listagem
            titulo="Filtro"
            @submit="onSubmitFiltro"
            :mostrar-limpar-filtros="temFiltrosAtivos"
            :desabilitado="loading"
            @limpar="limparFiltros"
        >
            <template #filtros>
                <div v-if="modo === 'lista'" class="col-12 col-lg-3">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="etp-filtro-busca">Buscar</label>
                        <input
                            id="etp-filtro-busca"
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Nome, chave ou permissão"
                            autocomplete="off"
                            :disabled="loading"
                            v-model="filtro.campoBusca"
                        />
                    </div>
                </div>

                <div v-if="modo === 'lista'" class="col-12 col-lg-3">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="etp-filtro-tipo">Tipo</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroTipo"
                                instance-id="etp-filtro-tipo"
                                input-id="etp-filtro-tipo"
                                v-model="filtro.campoTipo"
                                :options="filtroTipoOpcoes"
                                :disabled="loading"
                                placeholder-blur="Todos os tipos"
                                empty-message="Nenhuma opção."
                                :max-results="10"
                                @opening="fecharOutrosComboboxes('etp-filtro-tipo')"
                                @select="onSelectFiltro"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>

                <div v-if="modo === 'lista'" class="col-12 col-lg-2">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="etp-filtro-status">Status</label>
                        <div class="mybp-combobox-wrap">
                            <combobox-auto-complete
                                ref="comboFiltroStatus"
                                instance-id="etp-filtro-status"
                                input-id="etp-filtro-status"
                                v-model="filtro.campoStatus"
                                :options="filtroStatusOpcoes"
                                :disabled="loading"
                                placeholder-blur="Todos"
                                empty-message="Nenhuma opção."
                                :max-results="5"
                                @opening="fecharOutrosComboboxes('etp-filtro-status')"
                                @select="onSelectFiltro"
                            ></combobox-auto-complete>
                        </div>
                    </div>
                </div>

                <div class="col-12" :class="modo === 'lista' ? 'col-lg-4' : 'col-lg-6'">
                    <div class="form-group mb-2 mb-lg-0">
                        <label class="mybp-label" for="etp-filtro-cargo">Cargo (override opcional)</label>
                        <autocomplete
                            id="etp-filtro-cargo"
                            caminho="autocomplete/cargosEmpresa"
                            v-model="cargoLabel"
                            :disabled="loading"
                            placeholder="Vazio = padrão da empresa"
                            @onselect="selecionaCargo"
                            @onblur="resetaCargo"
                        ></autocomplete>
                    </div>
                </div>
            </template>

            <template #acoes>
                <div class="btn-group btn-group-sm etapas-seletivo-modo" role="group" aria-label="Modo de visualização">
                    <button
                        type="button"
                        class="btn"
                        :class="modo === 'fluxo' ? 'btn-dark' : 'btn-outline-secondary'"
                        @click="modo = 'fluxo'"
                    >
                        <i class="fas fa-project-diagram"></i> Fluxo
                    </button>
                    <button
                        type="button"
                        class="btn"
                        :class="modo === 'lista' ? 'btn-dark' : 'btn-outline-secondary'"
                        @click="modo = 'lista'"
                    >
                        <i class="fas fa-list"></i> Lista
                    </button>
                </div>
                <button type="submit" class="btn btn-sm btn-success" :disabled="loading">
                    <i :class="loading ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-secondary"
                    :disabled="loading || escopoSomenteLeitura"
                    @click="abrirModal()"
                >
                    <i class="fa fa-plus"></i> Nova etapa
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    v-if="vagaId && !temOverride"
                    :disabled="loading"
                    @click="personalizarCargo"
                >
                    <i class="fa fa-copy"></i> Personalizar este cargo
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-warning"
                    v-if="vagaId && temOverride"
                    :disabled="loading"
                    @click="voltarPadraoCargo"
                >
                    <i class="fa fa-undo"></i> Voltar ao padrão
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="loading" @click="restaurarPadrao">
                    <i class="fa fa-magic"></i> Restaurar padrão MyBP
                </button>
            </template>
        </filtro-listagem>

        <div
            class="alert alert-warning border-0 py-2"
            v-if="vagaId && !temOverride && !loading"
            role="alert"
        >
            <i class="fa fa-lock mr-1"></i>
            Este cargo ainda usa o padrão da empresa. Clique em
            <strong>Personalizar este cargo</strong> para editar etapas só deste cargo.
        </div>

        <preload class="text-center mt-3" v-if="loading"></preload>

        <workflow-fluxo
            v-if="!loading && modo === 'fluxo' && etapas.length"
            :etapas="etapas"
            :somente-leitura="escopoSomenteLeitura"
            @editar="abrirModal"
            @nova="novaEtapa"
            @alternar="alternarCampo"
            @reordenar="reordenar"
        ></workflow-fluxo>

        <div
            class="alert alert-warning text-center"
            v-show="!loading && (modo === 'lista' ? etapasFiltradas.length === 0 : etapas.length === 0)"
        >
            <i class="fa fa-exclamation-triangle"></i>
            {{ etapas.length ? 'Nenhuma etapa com esses filtros' : 'Nenhuma etapa neste escopo' }}
        </div>

        <template v-if="!loading && modo === 'lista' && etapasFiltradas.length">
            <section
                class="etapas-seletivo-grupo"
                v-for="grupo in gruposVisiveis"
                :key="grupo.tipo"
            >
                <header class="etapas-seletivo-grupo__header">
                    <span class="etapas-seletivo-grupo__badge" :class="'is-' + grupo.tipo">
                        <i :class="grupo.icone"></i>
                    </span>
                    <div>
                        <strong>{{ grupo.titulo }}</strong>
                        <small class="text-muted d-block">{{ grupo.subtitulo }}</small>
                    </div>
                    <span class="badge badge-light ml-auto">{{ grupo.itens.length }}</span>
                </header>

                <div class="mybp-cards-lista">
                    <div
                        class="mybp-card"
                        v-for="etapa in grupo.itens"
                        :key="etapa.id"
                        :class="{ 'is-inactive': !etapa.ativo }"
                    >
                        <div class="mybp-card-header-row">
                            <div class="mybp-card-left">
                                <span class="mybp-badge-id">#{{ etapa.ordem }}</span>
                                <div class="mybp-card-titulo">
                                    <strong>{{ etapa.nome }}</strong>
                                    <span class="text-muted small d-block">
                                        <code>{{ etapa.chave || 'sem_chave' }}</code>
                                    </span>
                                </div>
                            </div>
                            <div class="mybp-card-right">
                                <span
                                    class="badge"
                                    :class="etapa.ativo ? 'badge-success' : 'badge-secondary'"
                                >
                                    {{ etapa.ativo ? 'Ativa' : 'Inativa' }}
                                </span>
                                <span class="badge badge-warning ml-1" v-if="etapa.obrigatoria">Obrigatória</span>
                                <span class="badge badge-success ml-1" v-if="etapa.is_apto_admissao">Apto</span>
                                <div class="dropdown ml-1" :class="{ show: isDropdownOpen(etapa.id) }">
                                    <a
                                        class="mybp-btn-acoes-compact"
                                        href="#"
                                        role="button"
                                        aria-haspopup="true"
                                        :aria-expanded="isDropdownOpen(etapa.id) ? 'true' : 'false'"
                                        @click.prevent.stop="toggleDropdown(etapa.id)"
                                    >
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div
                                        class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                        :class="{ show: isDropdownOpen(etapa.id) }"
                                        @click="fecharDropdown"
                                    >
                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            :class="{ disabled: escopoSomenteLeitura }"
                                            @click.prevent="!escopoSomenteLeitura && abrirModal(etapa)"
                                        >
                                            <i class="fa fa-edit mr-1"></i> Editar
                                        </a>
                                        <a
                                            class="dropdown-item"
                                            href="javascript://"
                                            :class="{ disabled: escopoSomenteLeitura }"
                                            @click.prevent="!escopoSomenteLeitura && alternarAtivo(etapa)"
                                        >
                                            <i class="fa fa-power-off mr-1"></i>
                                            {{ etapa.ativo ? 'Desativar' : 'Ativar' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mybp-card-conteudo">
                            <div class="mybp-card-details-row">
                                <div class="mybp-detail-item">
                                    <span class="mybp-detail-label">Tipo</span>
                                    <span class="mybp-detail-value">{{ labelTipo(etapa.tipo) }}</span>
                                </div>
                                <div class="mybp-detail-item">
                                    <span class="mybp-detail-label">Depende de</span>
                                    <span class="mybp-detail-value">{{ nomeEtapa(etapa.depende_de_id) || '—' }}</span>
                                </div>
                                <div class="mybp-detail-item">
                                    <span class="mybp-detail-label">Permissão</span>
                                    <span class="mybp-detail-value">{{ labelHabilidade(etapa.habilidade) }}</span>
                                </div>
                                <div class="mybp-detail-item">
                                    <span class="mybp-detail-label">E-mail</span>
                                    <span class="mybp-detail-value">{{ etapa.permite_email !== false ? 'Sim' : 'Não' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>

        <modal
            ref="modalEtapa"
            id="modalEtapaSeletivo"
            :fechar="!salvando"
            :mostrar-botao-fechar-no-rodape="false"
            :size="68"
            :titulo="(form.id ? 'Editar' : 'Nova') + ' etapa do seletivo'"
        >
            <template #conteudo>
                <form class="mybp-modal-form mybp-filtros-compactos" @submit.prevent="salvar">
                    <fieldset class="mybp-modal-secao">
                        <legend>Identificação</legend>
                        <div class="row">
                            <div class="col-12 col-md-9">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">
                                        Nome da etapa <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.nome"
                                        placeholder="Ex.: Entrevista com a diretoria"
                                        maxlength="190"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">
                                        Posição <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        class="form-control form-control-sm"
                                        v-model.number="form.ordem"
                                        min="0"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="mybp-label">
                                    Tipo da etapa <span class="text-danger">*</span>
                                </label>
                                <div class="etapas-seletivo-tipos">
                                    <label
                                        class="etapas-seletivo-tipo"
                                        v-for="t in tiposCard"
                                        :key="t.value"
                                        :class="[
                                            'is-' + t.value,
                                            { 'is-ativo': form.tipo === t.value, 'is-desabilitado': !tipoPermitido(t.value) }
                                        ]"
                                    >
                                        <input
                                            type="radio"
                                            name="etp-tipo"
                                            :value="t.value"
                                            v-model="form.tipo"
                                            :disabled="!tipoPermitido(t.value)"
                                        />
                                        <i :class="t.icone"></i>
                                        <span>
                                            <strong>{{ t.titulo }}</strong>
                                            <small>{{ t.descricao }}</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mybp-filtro-campo mt-2">
                                    <label class="mybp-label">Permissão necessária</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            instance-id="etp-modal-hab"
                                            input-id="etp-modal-hab"
                                            v-model="form.habilidade"
                                            :options="habilidadeOpcoes"
                                            placeholder-blur="Nenhuma"
                                            empty-message="Nenhuma habilidade."
                                            :max-results="30"
                                        ></combobox-auto-complete>
                                    </div>
                                    <small class="etapas-seletivo-ajuda">Quem pode mover candidatos para esta etapa.</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mybp-filtro-campo mt-2">
                                    <label class="mybp-label">Chave interna</label>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="form.chave"
                                        :disabled="ehSistemaExistente"
                                        placeholder="Gerada a partir do nome"
                                    />
                                    <small class="etapas-seletivo-ajuda">Identificador usado pelo sistema; não aparece ao candidato.</small>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Regras do funil</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Só liberar depois de</label>
                                    <div class="mybp-combobox-wrap">
                                        <combobox-auto-complete
                                            instance-id="etp-modal-dep"
                                            input-id="etp-modal-dep"
                                            v-model="form.depende_de_id"
                                            :options="dependeDeOpcoes"
                                            placeholder-blur="Nenhuma — pode ser feita a qualquer momento"
                                            empty-message="Nenhuma etapa."
                                            :max-results="40"
                                        ></combobox-auto-complete>
                                    </div>
                                    <small class="etapas-seletivo-ajuda">Pré-requisito: o candidato precisa ter concluído esta etapa antes.</small>
                                </div>
                            </div>
                        </div>
                        <div class="etapas-seletivo-switches">
                            <label
                                class="etapas-seletivo-switch"
                                v-for="r in regrasSwitch"
                                :key="r.campo"
                                :class="{ 'is-on': form[r.campo] }"
                            >
                                <input type="checkbox" v-model="form[r.campo]" />
                                <i :class="r.icone"></i>
                                <span>
                                    <strong>{{ r.titulo }}</strong>
                                    <small>{{ r.descricao }}</small>
                                </span>
                                <span class="etapas-seletivo-switch__trilho"></span>
                            </label>
                        </div>
                    </fieldset>
                </form>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-secondary mr-1" :disabled="salvando" @click="fecharModal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                    <i :class="salvando ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i> Salvar
                </button>
            </template>
        </modal>
    </div>
</template>

<style scoped>
.etapas-seletivo-modo {
    margin-right: 0.25rem;
}
.etapas-seletivo-escopo {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 0.85rem;
    padding: 0.9rem 1rem;
    margin-bottom: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #dbe3ef;
    background: linear-gradient(135deg, #f7f9fc 0%, #eef3f9 100%);
}
.etapas-seletivo-escopo.is-override {
    border-color: #f0c36d;
    background: linear-gradient(135deg, #fffaf0 0%, #fff4df 100%);
}
.etapas-seletivo-escopo.is-readonly {
    border-color: #cfd8e6;
    background: #f5f7fa;
}
.etapas-seletivo-escopo__icon {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.45rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #3b5bdb;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
}
.etapas-seletivo-escopo.is-override .etapas-seletivo-escopo__icon {
    color: #b7791f;
}
.etapas-seletivo-escopo__body {
    flex: 1 1 220px;
    min-width: 0;
}
.etapas-seletivo-escopo__body p {
    color: #5b6b7c;
    font-size: 0.875rem;
}
.etapas-seletivo-escopo__stats {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    align-items: center;
}
.etapas-seletivo-grupo {
    margin-top: 1.1rem;
}
.etapas-seletivo-grupo__header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.65rem;
}
.etapas-seletivo-grupo__badge {
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: #4c6ef5;
}
.etapas-seletivo-grupo__badge.is-sistema {
    background: #0ca678;
}
.etapas-seletivo-grupo__badge.is-desclassificacao {
    background: #e03131;
}
.mybp-card.is-inactive {
    opacity: 0.72;
}
.dropdown-item.disabled {
    pointer-events: none;
    opacity: 0.45;
}
.etapas-seletivo-ajuda {
    display: block;
    margin-top: 0.2rem;
    font-size: 0.72rem;
    color: #868e96;
}
.etapas-seletivo-tipos {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 0.6rem;
    margin-bottom: 0.25rem;
}
.etapas-seletivo-tipo,
.etapas-seletivo-switch {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin: 0;
    padding: 0.55rem 0.75rem;
    border: 1px solid #dbe3ef;
    border-radius: 0.5rem;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
}
.etapas-seletivo-tipo input,
.etapas-seletivo-switch input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.etapas-seletivo-tipo > i,
.etapas-seletivo-switch > i {
    font-size: 1.1rem;
    color: #94a3b8;
    width: 1.4rem;
    text-align: center;
}
.etapas-seletivo-tipo strong,
.etapas-seletivo-switch strong {
    display: block;
    font-size: 0.85rem;
    line-height: 1.2;
}
.etapas-seletivo-tipo small,
.etapas-seletivo-switch small {
    display: block;
    font-size: 0.72rem;
    color: #868e96;
    line-height: 1.25;
}
.etapas-seletivo-tipo:hover:not(.is-desabilitado),
.etapas-seletivo-switch:hover {
    border-color: #91a7ff;
}
.etapas-seletivo-tipo.is-ativo.is-avanco {
    border-color: #4c6ef5;
    background: #edf2ff;
    box-shadow: 0 0 0 2px rgba(76, 110, 245, 0.18);
}
.etapas-seletivo-tipo.is-ativo.is-avanco > i {
    color: #4c6ef5;
}
.etapas-seletivo-tipo.is-ativo.is-desclassificacao {
    border-color: #e03131;
    background: #fff5f5;
    box-shadow: 0 0 0 2px rgba(224, 49, 49, 0.15);
}
.etapas-seletivo-tipo.is-ativo.is-desclassificacao > i {
    color: #e03131;
}
.etapas-seletivo-tipo.is-ativo.is-sistema {
    border-color: #0ca678;
    background: #e6fcf5;
}
.etapas-seletivo-tipo.is-ativo.is-sistema > i {
    color: #0ca678;
}
.etapas-seletivo-tipo.is-desabilitado {
    opacity: 0.4;
    cursor: not-allowed;
}
.etapas-seletivo-switches {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
}
.etapas-seletivo-switch__trilho {
    margin-left: auto;
    flex: 0 0 auto;
    width: 2.1rem;
    height: 1.15rem;
    border-radius: 999px;
    background: #cbd5e1;
    position: relative;
    transition: background 0.15s;
}
.etapas-seletivo-switch__trilho::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: calc(1.15rem - 4px);
    height: calc(1.15rem - 4px);
    border-radius: 50%;
    background: #fff;
    transition: transform 0.15s;
}
.etapas-seletivo-switch.is-on {
    border-color: #96f2d7;
    background: #f3fdf9;
}
.etapas-seletivo-switch.is-on > i {
    color: #0ca678;
}
.etapas-seletivo-switch.is-on .etapas-seletivo-switch__trilho {
    background: #0ca678;
}
.etapas-seletivo-switch.is-on .etapas-seletivo-switch__trilho::after {
    transform: translateX(0.95rem);
}
.etapas-seletivo-switch input:focus-visible ~ .etapas-seletivo-switch__trilho {
    outline: 2px solid #4c6ef5;
    outline-offset: 2px;
}
@media (max-width: 767.98px) {
    .etapas-seletivo-switches {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
import FiltroListagem from '../../ui/FiltroListagem.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import WorkflowFluxo from './WorkflowFluxo.vue'

export default {
    components: {
        FiltroListagem,
        ComboboxAutoComplete,
        WorkflowFluxo
    },
    data() {
        return {
            URL_ADMIN: typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : '/g',
            modo: 'fluxo',
            loading: false,
            salvando: false,
            vagaId: null,
            cargoLabel: '',
            cargoLabelAnterior: '',
            temOverride: false,
            etapas: [],
            habilidades: [],
            dropdownAbertoId: null,
            filtro: {
                campoBusca: '',
                campoTipo: '',
                campoStatus: ''
            },
            form: {
                id: null,
                nome: '',
                chave: '',
                tipo: 'avanco',
                ordem: 10,
                obrigatoria: false,
                ativo: true,
                is_apto_admissao: false,
                permite_email: true,
                habilidade: 'curriculos_selecionados',
                depende_de_id: ''
            }
        }
    },
    computed: {
        escopoSomenteLeitura() {
            return !!(this.vagaId && !this.temOverride)
        },
        escopoClasse() {
            if (this.vagaId && this.temOverride) return 'is-override'
            if (this.escopoSomenteLeitura) return 'is-readonly'
            return ''
        },
        escopoTitulo() {
            if (!this.vagaId) return 'Padrão da empresa'
            if (this.temOverride) return `Override: ${this.cargoLabel || 'Cargo'}`
            return `Visualizando padrão (cargo: ${this.cargoLabel || 'selecionado'})`
        },
        escopoDescricao() {
            if (!this.vagaId) {
                return 'Este funil vale para todos os cargos sem personalização.'
            }
            if (this.temOverride) {
                return 'Alterações aqui afetam apenas este cargo. Técnica/rota e ordem podem ser ajustadas.'
            }
            return 'Somente leitura até personalizar. O cargo ainda herda o workflow da empresa.'
        },
        temFiltrosAtivos() {
            const f = this.filtro
            return !!(f.campoBusca || f.campoTipo || f.campoStatus || this.vagaId)
        },
        filtroTipoOpcoes() {
            return [
                { value: '', label: 'Todos os tipos' },
                { value: 'avanco', label: 'Avanço' },
                { value: 'sistema', label: 'Sistema' },
                { value: 'desclassificacao', label: 'Desclassificação' }
            ]
        },
        filtroStatusOpcoes() {
            return [
                { value: '', label: 'Todos' },
                { value: 'true', label: 'Apenas ativas' },
                { value: 'false', label: 'Apenas inativas' }
            ]
        },
        tipoModalOpcoes() {
            if (this.ehSistemaExistente) {
                return [{ value: 'sistema', label: 'Sistema' }]
            }
            return [
                { value: 'avanco', label: 'Avanço' },
                { value: 'desclassificacao', label: 'Desclassificação' }
            ]
        },
        tiposCard() {
            return [
                {
                    value: 'avanco',
                    titulo: 'Avanço',
                    descricao: 'Etapa que o candidato percorre no funil.',
                    icone: 'fas fa-flag'
                },
                {
                    value: 'desclassificacao',
                    titulo: 'Desclassificação',
                    descricao: 'Motivo de saída do funil.',
                    icone: 'fas fa-times-circle'
                },
                ...(this.ehSistemaExistente
                    ? [
                          {
                              value: 'sistema',
                              titulo: 'Sistema',
                              descricao: 'Concluída automaticamente por um módulo.',
                              icone: 'fas fa-cogs'
                          }
                      ]
                    : [])
            ]
        },
        regrasSwitch() {
            return [
                {
                    campo: 'obrigatoria',
                    titulo: 'Obrigatória',
                    descricao: 'Não pode ser pulada no funil.',
                    icone: 'fas fa-star'
                },
                {
                    campo: 'ativo',
                    titulo: 'Ativa',
                    descricao: 'Disponível para uso no Classificar.',
                    icone: 'fas fa-power-off'
                },
                {
                    campo: 'permite_email',
                    titulo: 'Permite e-mail',
                    descricao: 'Permite avisar o candidato ao entrar.',
                    icone: 'fas fa-envelope'
                },
                {
                    campo: 'is_apto_admissao',
                    titulo: 'Apto para admissão',
                    descricao: 'Marca o candidato como pronto para admitir.',
                    icone: 'fas fa-check-double'
                }
            ]
        },
        habilidadeOpcoes() {
            const base = [{ value: '', label: 'Nenhuma (qualquer com acesso)' }]
            return base.concat(
                (this.habilidades || []).map((h) => ({
                    value: h.nome,
                    label: h.descricao || h.nome
                }))
            )
        },
        dependeDeOpcoes() {
            const base = [{ value: '', label: 'Nenhuma' }]
            return base.concat(
                this.etapas
                    .filter((e) => e.id !== this.form.id)
                    .map((e) => ({
                        value: String(e.id),
                        label: `${e.ordem} · ${e.nome}`
                    }))
            )
        },
        ehSistemaExistente() {
            return this.form.tipo === 'sistema' && !!this.form.id
        },
        etapasFiltradas() {
            const busca = (this.filtro.campoBusca || '').toLowerCase().trim()
            const tipo = this.filtro.campoTipo
            const status = this.filtro.campoStatus

            return this.etapas.filter((e) => {
                if (tipo && e.tipo !== tipo) return false
                if (status === 'true' && !e.ativo) return false
                if (status === 'false' && e.ativo) return false
                if (!busca) return true
                const hay = `${e.nome || ''} ${e.chave || ''} ${e.habilidade || ''}`.toLowerCase()
                return hay.includes(busca)
            })
        },
        contagem() {
            const all = this.etapas
            return {
                avanco: all.filter((e) => e.tipo === 'avanco').length,
                sistema: all.filter((e) => e.tipo === 'sistema').length,
                desclassificacao: all.filter((e) => e.tipo === 'desclassificacao').length,
                inativas: all.filter((e) => !e.ativo).length
            }
        },
        gruposVisiveis() {
            const defs = [
                {
                    tipo: 'avanco',
                    titulo: 'Funil de avanço',
                    subtitulo: 'Etapas manuais do Classificar',
                    icone: 'fas fa-arrow-right'
                },
                {
                    tipo: 'sistema',
                    titulo: 'Etapas de sistema',
                    subtitulo: 'Concluídas por módulos (carta, docs, técnica, rota, apto)',
                    icone: 'fas fa-cogs'
                },
                {
                    tipo: 'desclassificacao',
                    titulo: 'Desclassificação',
                    subtitulo: 'Motivos de saída do funil',
                    icone: 'fas fa-times'
                }
            ]
            return defs
                .map((d) => ({
                    ...d,
                    itens: this.etapasFiltradas.filter((e) => e.tipo === d.tipo)
                }))
                .filter((g) => g.itens.length > 0)
        }
    },
    mounted() {
        this.carregar()
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    methods: {
        formVazio() {
            return {
                id: null,
                nome: '',
                chave: '',
                tipo: 'avanco',
                ordem: 10,
                obrigatoria: false,
                ativo: true,
                is_apto_admissao: false,
                permite_email: true,
                habilidade: 'curriculos_selecionados',
                depende_de_id: ''
            }
        },
        tipoPermitido(valor) {
            if (this.ehSistemaExistente) return valor === 'sistema'
            return valor !== 'sistema'
        },
        labelTipo(tipo) {
            if (tipo === 'sistema') return 'Sistema'
            if (tipo === 'desclassificacao') return 'Desclassificação'
            return 'Avanço'
        },
        labelHabilidade(nome) {
            if (!nome) return '—'
            const hab = this.habilidades.find((h) => h.nome === nome)
            return hab?.descricao || nome
        },
        nomeEtapa(id) {
            if (!id) return ''
            const e = this.etapas.find((x) => x.id === id)
            return e ? e.nome : ''
        },
        onSubmitFiltro() {
            // filtros são client-side; só recarrega se precisar sincronizar escopo
            this.fecharDropdown()
        },
        onSelectFiltro() {
            this.fecharDropdown()
        },
        limparFiltros() {
            this.filtro.campoBusca = ''
            this.filtro.campoTipo = ''
            this.filtro.campoStatus = ''
            this.cargoLabel = ''
            this.cargoLabelAnterior = ''
            this.vagaId = null
            this.fecharOutrosComboboxes(null)
            this.carregar()
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'etp-filtro-tipo': 'comboFiltroTipo',
                'etp-filtro-status': 'comboFiltroStatus'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.fechar === 'function') ref.fechar()
                else if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        selecionaCargo(obj) {
            this.vagaId = obj?.id || null
            this.cargoLabel = obj?.label || ''
            this.cargoLabelAnterior = this.cargoLabel
            this.carregar()
        },
        resetaCargo() {
            if (this.cargoLabelAnterior !== this.cargoLabel) {
                this.cargoLabel = ''
                this.cargoLabelAnterior = ''
                this.vagaId = null
                this.carregar()
            }
        },
        carregar() {
            this.loading = true
            this.fecharDropdown()
            const params = {}
            if (this.vagaId) params.vaga_id = this.vagaId
            axios
                .get(`${this.URL_ADMIN}/administracao/etapas-seletivo/listar`, { params })
                .then((r) => {
                    this.etapas = r.data.etapas || []
                    this.temOverride = !!r.data.tem_override
                    this.habilidades = r.data.habilidades_disponiveis || []
                    this.loading = false
                })
                .catch(() => {
                    this.loading = false
                })
        },
        novaEtapa({ ordem, tipo } = {}) {
            if (this.escopoSomenteLeitura) {
                mostraErro('', 'Personalize o cargo antes de editar etapas')
                return
            }
            this.form = {
                ...this.formVazio(),
                ordem: ordem ?? 10,
                tipo: tipo || 'avanco',
                habilidade: tipo === 'desclassificacao' ? '' : 'curriculos_selecionados'
            }
            this.$refs.modalEtapa?.abrirModal()
        },
        payloadEtapa(etapa, extra = {}) {
            const payload = {
                id: etapa.id,
                nome: etapa.nome,
                chave: etapa.chave,
                tipo: etapa.tipo,
                ordem: etapa.ordem,
                obrigatoria: !!etapa.obrigatoria,
                ativo: !!etapa.ativo,
                is_apto_admissao: !!etapa.is_apto_admissao,
                permite_email: etapa.permite_email !== false,
                habilidade: etapa.habilidade || null,
                depende_de_id: etapa.depende_de_id || null,
                ...extra
            }
            if (this.vagaId) payload.vaga_id = this.vagaId
            return payload
        },
        alternarCampo(etapa, campo) {
            if (this.escopoSomenteLeitura) return
            const valor = !etapa[campo]
            const anterior = etapa[campo]
            etapa[campo] = valor // otimista
            axios
                .post(
                    `${this.URL_ADMIN}/administracao/etapas-seletivo/salvar`,
                    this.payloadEtapa(etapa, { [campo]: valor })
                )
                .catch((e) => {
                    etapa[campo] = anterior
                    mostraErro('', e?.response?.data?.msg || 'Não foi possível alterar a etapa')
                })
        },
        reordenar(ids) {
            if (this.escopoSomenteLeitura) return
            const anterior = this.etapas.map((e) => ({ id: e.id, ordem: e.ordem }))
            ids.forEach((id, i) => {
                const e = this.etapas.find((x) => x.id === id)
                if (e) e.ordem = (i + 1) * 10
            })
            const payload = { ids }
            if (this.vagaId) payload.vaga_id = this.vagaId
            axios
                .post(`${this.URL_ADMIN}/administracao/etapas-seletivo/reordenar`, payload)
                .then(() => mostraSucesso('', 'Ordem atualizada'))
                .catch(() => {
                    anterior.forEach((a) => {
                        const e = this.etapas.find((x) => x.id === a.id)
                        if (e) e.ordem = a.ordem
                    })
                    mostraErro('', 'Não foi possível reordenar')
                })
        },
        abrirModal(etapa = null) {
            if (this.escopoSomenteLeitura) {
                mostraErro('', 'Personalize o cargo antes de editar etapas')
                return
            }
            this.form = etapa
                ? {
                      id: etapa.id,
                      nome: etapa.nome,
                      chave: etapa.chave,
                      tipo: etapa.tipo,
                      ordem: etapa.ordem,
                      obrigatoria: !!etapa.obrigatoria,
                      ativo: !!etapa.ativo,
                      is_apto_admissao: !!etapa.is_apto_admissao,
                      permite_email: etapa.permite_email !== false,
                      habilidade: etapa.habilidade || '',
                      depende_de_id: etapa.depende_de_id ? String(etapa.depende_de_id) : ''
                  }
                : this.formVazio()
            this.$refs.modalEtapa?.abrirModal()
        },
        fecharModal() {
            this.$refs.modalEtapa?.fecharModal()
        },
        payloadForm(extra = {}) {
            const f = this.form
            const payload = {
                id: f.id,
                nome: f.nome,
                chave: f.chave,
                tipo: f.tipo,
                ordem: f.ordem,
                obrigatoria: !!f.obrigatoria,
                ativo: !!f.ativo,
                is_apto_admissao: !!f.is_apto_admissao,
                permite_email: !!f.permite_email,
                habilidade: f.habilidade || null,
                depende_de_id: f.depende_de_id ? Number(f.depende_de_id) : null,
                ...extra
            }
            if (this.vagaId) payload.vaga_id = this.vagaId
            return payload
        },
        salvar() {
            if (!this.form.nome) {
                mostraErro('', 'Informe o nome da etapa')
                return
            }
            this.salvando = true
            axios
                .post(`${this.URL_ADMIN}/administracao/etapas-seletivo/salvar`, this.payloadForm())
                .then(() => {
                    mostraSucesso('', 'Etapa salva')
                    this.salvando = false
                    this.fecharModal()
                    this.carregar()
                })
                .catch((e) => {
                    this.salvando = false
                    const msg = e?.response?.data?.msg || 'Não foi possível salvar'
                    mostraErro('', msg)
                })
        },
        alternarAtivo(etapa) {
            if (this.escopoSomenteLeitura) return
            this.loading = true
            const payload = {
                id: etapa.id,
                nome: etapa.nome,
                chave: etapa.chave,
                tipo: etapa.tipo,
                ordem: etapa.ordem,
                obrigatoria: !!etapa.obrigatoria,
                ativo: !etapa.ativo,
                is_apto_admissao: !!etapa.is_apto_admissao,
                permite_email: etapa.permite_email !== false,
                habilidade: etapa.habilidade || null,
                depende_de_id: etapa.depende_de_id || null
            }
            if (this.vagaId) payload.vaga_id = this.vagaId
            axios
                .post(`${this.URL_ADMIN}/administracao/etapas-seletivo/salvar`, payload)
                .then(() => {
                    this.loading = false
                    mostraSucesso('', payload.ativo ? 'Etapa ativada' : 'Etapa desativada')
                    this.carregar()
                })
                .catch((e) => {
                    this.loading = false
                    const msg = e?.response?.data?.msg || 'Não foi possível alterar o status'
                    mostraErro('', msg)
                })
        },
        personalizarCargo() {
            if (!this.vagaId) return
            this.loading = true
            axios
                .post(`${this.URL_ADMIN}/administracao/etapas-seletivo/personalizar-cargo`, { vaga_id: this.vagaId })
                .then((r) => {
                    this.etapas = r.data.etapas || []
                    this.temOverride = true
                    this.loading = false
                    mostraSucesso('', 'Override do cargo criado a partir do padrão')
                })
                .catch(() => {
                    this.loading = false
                })
        },
        voltarPadraoCargo() {
            if (!this.vagaId) return
            if (!confirm('Remover o override deste cargo e voltar ao padrão da empresa?')) return
            this.loading = true
            axios
                .post(`${this.URL_ADMIN}/administracao/etapas-seletivo/voltar-padrao-cargo`, { vaga_id: this.vagaId })
                .then(() => {
                    this.temOverride = false
                    this.loading = false
                    this.carregar()
                    mostraSucesso('', 'Override removido')
                })
                .catch(() => {
                    this.loading = false
                })
        },
        restaurarPadrao() {
            const msg = this.vagaId
                ? 'Recriar o override do cargo a partir do padrão MyBP/empresa?'
                : 'Garantir etapas padrão MyBP na empresa (não remove etapas customizadas já existentes)?'
            if (!confirm(msg)) return
            this.loading = true
            const payload = this.vagaId ? { vaga_id: this.vagaId } : {}
            axios
                .post(`${this.URL_ADMIN}/administracao/etapas-seletivo/restaurar-padrao`, payload)
                .then(() => {
                    this.loading = false
                    this.carregar()
                    mostraSucesso('', 'Padrão aplicado')
                })
                .catch(() => {
                    this.loading = false
                })
        },
        toggleDropdown(id) {
            this.dropdownAbertoId = this.dropdownAbertoId === id ? null : id
        },
        isDropdownOpen(id) {
            return this.dropdownAbertoId === id
        },
        fecharDropdown() {
            this.dropdownAbertoId = null
        },
        onClickOutside(e) {
            if (e.target.closest('.dropdown') || e.target.closest('.mybp-combobox-wrap')) return
            this.fecharDropdown()
        }
    }
}
</script>
