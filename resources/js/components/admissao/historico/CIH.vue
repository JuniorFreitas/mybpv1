<template>
    <div class="historico-cih">
        <preload class="mt-2" v-if="preload"></preload>

        <modal
            :id="hash"
            :titulo="tituloJanela"
            :fechar="!preloadAjax"
            :size="90"
            :ref="hash"
            :modal-pai="modalPai"
        >
            <template #conteudo>
                <preload label="Aguarde..." v-show="preloadAjax"></preload>
                <div
                    v-if="!preloadAjax"
                    class="mybp-modal-form mybp-filtros-compactos"
                >
                    <fieldset class="mybp-modal-secao">
                        <legend>Ocorrência</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Data da ocorrência</label>
                                    <p class="historico-cih-valor mb-0">{{ form.data_lancamento || '—' }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Tipo</label>
                                    <p class="historico-cih-valor mb-0">{{ labelTipoLista(form) }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="config_modelo_cih === 'area'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Área</label>
                                    <p class="historico-cih-valor mb-0">{{ labelAreaLista(form) || '—' }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4" v-if="config_modelo_cih === 'centro_de_custo'">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Centro de custo</label>
                                    <p class="historico-cih-valor mb-0">{{ labelCentroCustoLista(form) || '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Envolvidos</legend>
                        <div class="table-responsive cih-modal-table-wrap" v-if="form.colaboradores && form.colaboradores.length">
                            <table class="table table-bordered table-hover table-sm bg-white cih-modal-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Cargo</th>
                                        <th>Centro de Custo</th>
                                        <th>Lotação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(colaborador, index) in form.colaboradores"
                                        :key="colaborador.id || index"
                                    >
                                        <td>{{ labelNomeColaboradorModal(colaborador) }}</td>
                                        <td>{{ labelCargoColaboradorModal(colaborador) }}</td>
                                        <td>{{ labelCentroCustoColaboradorModal(colaborador) }}</td>
                                        <td>{{ labelLotacaoColaboradorModal(colaborador) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted mb-0" v-else>Nenhum colaborador informado.</p>
                        <p class="mt-2 mb-0" v-if="config_modelo_cih === 'area' && form.gestor_aprovacao">
                            <strong>Gestor aprovação:</strong> {{ form.gestor_aprovacao.nome }}
                        </p>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Detalhes</legend>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Ação</label>
                                    <p class="historico-cih-valor mb-0" style="white-space: pre-wrap">{{ form.acao || '—' }}</p>
                                </div>
                            </div>
                            <div class="col-12" v-if="form.anexos && form.anexos.length">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Anexo (Evidência)</label>
                                    <upload
                                        :model="form.anexos"
                                        :model-delete="form.anexosDel"
                                        :leitura="true"
                                        :url="url_anexo"
                                        label="Anexo"
                                        titulo="Evidência"
                                    ></upload>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mybp-filtro-campo">
                                    <label class="mybp-label">Observação do lançamento</label>
                                    <p class="historico-cih-valor mb-0" style="white-space: pre-wrap">
                                        {{ form.obs_lancamento || '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Aprovação Gestor</legend>
                        <p class="mb-2 text-muted" v-if="form.responsavel_aprovacao">
                            {{ form.status }} por: {{ form.responsavel_aprovacao.nome }}
                            <template v-if="form.data_aprovacao"> em {{ form.data_aprovacao }}</template>
                        </p>
                        <p class="mb-2 text-muted" v-else>Ainda sem aprovação/reprovação do gestor.</p>
                        <div class="form-group mybp-filtro-campo mb-0">
                            <label class="mybp-label">Observação</label>
                            <p class="historico-cih-valor mb-0" style="white-space: pre-wrap">
                                {{ form.obs_aprovacao || '—' }}
                            </p>
                        </div>
                    </fieldset>

                    <fieldset class="mybp-modal-secao">
                        <legend>Aprovação RH</legend>
                        <p class="mb-2 text-muted" v-if="form.rh_aprovacao">
                            {{ form.resposta_rh }} por: {{ form.rh_aprovacao.nome }}
                            <template v-if="form.data_aprovacao_rh"> em {{ form.data_aprovacao_rh }}</template>
                        </p>
                        <p class="mb-2 text-muted" v-else>Ainda sem aprovação/reprovação do RH.</p>
                        <div class="form-group mybp-filtro-campo mb-0">
                            <label class="mybp-label">Observação</label>
                            <p class="historico-cih-valor mb-0" style="white-space: pre-wrap">
                                {{ form.obs_rh || '—' }}
                            </p>
                        </div>
                    </fieldset>
                </div>
            </template>
            <template #rodape></template>
        </modal>

        <div v-if="!preload">
            <form class="historico-cih-filtros mybp-filtros-compactos" @submit.prevent>
                <div class="row">
                    <date-range-filter
                        v-model:enabled="filtros.filtroPeriodo"
                        v-model:start-date="filtros.dataInicio"
                        v-model:end-date="filtros.dataFim"
                        :id-suffix="hash"
                        label="Por período"
                        wrapper-class="col-12 col-md-4 mybp-filtro-periodo"
                    />

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`cih-hist-filtro-tipo-${hash}`">Tipo</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`cih-hist-tipo-${hash}`"
                                    v-model="filtros.tipo"
                                    :options="opcoesTipo"
                                    :input-id="`cih-hist-filtro-tipo-${hash}`"
                                    placeholder-blur="Todos os tipos"
                                    empty-message="Nenhum tipo encontrado."
                                    :max-results="100"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-group mybp-filtro-campo">
                            <label class="mybp-label" :for="`cih-hist-filtro-fluxo-${hash}`">Fluxo</label>
                            <div class="mybp-combobox-wrap">
                                <combobox-auto-complete
                                    :instance-id="`cih-hist-fluxo-${hash}`"
                                    v-model="filtros.fluxo"
                                    :options="opcoesFluxo"
                                    :input-id="`cih-hist-filtro-fluxo-${hash}`"
                                    placeholder-blur="Todos os status"
                                    empty-message="Nenhuma opção encontrada."
                                    :max-results="20"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="historico-cih-filtros__acoes" v-if="temFiltrosAtivos">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="limparFiltros">
                        <i class="fas fa-eraser"></i> Limpar filtros
                    </button>
                    <span class="historico-cih-filtros__contagem">
                        {{ listaFiltrada.length }} de {{ lista.length }}
                    </span>
                </div>
            </form>

            <div class="alert alert-warning" v-show="lista.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum Registro Encontrado
            </div>
            <div class="alert alert-warning" v-show="lista.length > 0 && listaFiltrada.length === 0">
                <i class="fa fa-exclamation-triangle"></i> Nenhum registro com os filtros aplicados
            </div>

            <div class="historico-cih-lista" v-show="listaFiltrada.length > 0">
                <article
                    class="historico-cih-card"
                    v-for="item in listaFiltrada"
                    :key="item.id"
                    :class="'historico-cih-card--' + chaveStatusLista(item)"
                >
                    <header class="historico-cih-card__topo">
                        <div class="historico-cih-card__ident">
                            <strong class="historico-cih-card__titulo">{{ labelTipoLista(item) }}</strong>
                            <p class="historico-cih-card__meta-id">
                                <span class="historico-cih-card__id">#{{ item.id }}</span>
                                <span class="historico-cih-card__sep" aria-hidden="true">·</span>
                                <span
                                    class="historico-cih-card__status"
                                    :class="'historico-cih-card__status--' + chaveStatusLista(item)"
                                >
                                    {{ textoStatusLista(item) }}
                                </span>
                            </p>
                        </div>
                        <div class="dropdown" :class="{ show: isDropdownOpen(item.id) }">
                            <a
                                class="historico-cih-card__acoes"
                                href="#"
                                role="button"
                                :id="`dropdownCihHist_${item.id}`"
                                aria-haspopup="true"
                                :aria-expanded="isDropdownOpen(item.id) ? 'true' : 'false'"
                                @click.prevent.stop="toggleDropdown(item.id)"
                            >
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div
                                class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                                :class="{ show: isDropdownOpen(item.id) }"
                                :aria-labelledby="`dropdownCihHist_${item.id}`"
                                @click="fecharDropdown"
                            >
                                <a
                                    class="dropdown-item"
                                    href="javascript://"
                                    title="Visualizar"
                                    @click.prevent="
                                        formVisualizar(item.id);
                                        $refs[hash] && $refs[hash].abrirModal()
                                    "
                                >
                                    <i class="fa fa-search mr-1"></i> Visualizar
                                </a>
                            </div>
                        </div>
                    </header>

                    <p class="historico-cih-card__linha">
                        <span><span class="historico-cih-card__k">Data:</span> {{ item.data_lancamento || '—' }}</span>
                        <span class="historico-cih-card__sep" aria-hidden="true">·</span>
                        <span>
                            <span class="historico-cih-card__k">Lançamento:</span>
                            {{ (item.responsavel_lancamento && item.responsavel_lancamento.nome) || '—' }}
                        </span>
                    </p>

                    <p class="historico-cih-card__linha" v-if="linhaEscopoCompacta(item)">
                        <template v-for="(parte, idx) in linhaEscopoCompacta(item)" :key="'esc-' + item.id + '-' + idx">
                            <span v-if="idx > 0" class="historico-cih-card__sep" aria-hidden="true">·</span>
                            <span>
                                <span class="historico-cih-card__k">{{ parte.label }}:</span>
                                {{ parte.valor }}
                            </span>
                        </template>
                    </p>

                    <p class="historico-cih-card__linha historico-cih-card__linha--acao" v-if="item.acao">
                        <span class="historico-cih-card__k">Ação:</span>
                        {{ resumoAcaoLista(item) }}
                    </p>

                    <div class="historico-cih-card__fluxo">
                        <mybp-fluxo-aprovacao :steps="fluxoStepsLista(item)" />
                    </div>
                </article>
            </div>
        </div>
    </div>
</template>

<script>
import Upload from '../../Upload'
import DateRangeFilter from '../../DateRangeFilter.vue'
import ComboboxAutoComplete from '../../ComboboxAutoComplete.vue'
import MybpFluxoAprovacao from '../../ui/MybpFluxoAprovacao.vue'
import {
    etapaAtualFluxoAprovacao,
    opcoesStatusFluxoAtual,
    textoStatusFluxo,
    varianteStatusFluxo
} from '../../../utils/opcoesStatusFluxoAprovacao'

export default {
    name: 'HistoricoCIH',
    components: {
        Upload,
        DateRangeFilter,
        ComboboxAutoComplete,
        MybpFluxoAprovacao
    },
    props: {
        fc_token: {
            type: String,
            required: true
        },
        hash: {
            type: String,
            default: () => `historico_cih_${parseInt(Math.random() * 999999, 10)}`
        },
        modalPai: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            tituloJanela: 'CIH',
            preload: false,
            preloadAjax: false,
            AUTENTICADO,
            URL_ADMIN,
            url_anexo: `${URL_ADMIN}/apontamento/cih/uploadAnexos`,
            config_modelo_cih: null,
            lista_ccs: null,
            lista: [],
            filtros: {
                filtroPeriodo: false,
                dataInicio: '',
                dataFim: '',
                tipo: '',
                fluxo: ''
            },
            dropdownAbertoKey: null,
            form: this.formVazio(),
            formDefault: null
        }
    },
    computed: {
        opcoesTipo() {
            const opcoes = [{ value: '', label: 'Todos os tipos' }]
            const vistos = new Set()
            ;(this.lista || []).forEach((item) => {
                const tagId = item.tag_id == null || item.tag_id === '' ? null : Number(item.tag_id)
                if (!tagId) {
                    if (!vistos.has('0')) {
                        vistos.add('0')
                        opcoes.push({ value: '0', label: 'Outro' })
                    }
                    return
                }
                const key = String(tagId)
                if (vistos.has(key)) return
                vistos.add(key)
                opcoes.push({
                    value: key,
                    label: (item.tag && item.tag.label) || `Tipo #${tagId}`
                })
            })
            return opcoes
        },
        opcoesFluxo() {
            return opcoesStatusFluxoAtual({ temAprovacaoExtra: false })
        },
        temFiltrosAtivos() {
            const f = this.filtros
            if (f.filtroPeriodo && (f.dataInicio || f.dataFim)) return true
            if (f.tipo !== '' && f.tipo !== null && f.tipo !== undefined) return true
            if (f.fluxo) return true
            return false
        },
        listaFiltrada() {
            return (this.lista || []).filter((item) => this.passaFiltros(item))
        }
    },
    mounted() {
        this.formDefault = this.cloneDeepLocal(this.form)
        this.atualizar()
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    methods: {
        limparFiltros() {
            this.filtros = {
                filtroPeriodo: false,
                dataInicio: '',
                dataFim: '',
                tipo: '',
                fluxo: ''
            }
        },
        parseDataIso(valor) {
            if (!valor) return null
            const s = String(valor).trim()
            if (/^\d{4}-\d{2}-\d{2}/.test(s)) return s.slice(0, 10)
            const br = s.match(/^(\d{2})\/(\d{2})\/(\d{4})/)
            if (br) return `${br[3]}-${br[2]}-${br[1]}`
            return null
        },
        passaFiltros(item) {
            if (!item) return false
            const f = this.filtros

            if (f.filtroPeriodo && (f.dataInicio || f.dataFim)) {
                const dataItem = this.parseDataIso(item.data_lancamento)
                if (!dataItem) return false
                if (f.dataInicio && dataItem < f.dataInicio) return false
                if (f.dataFim && dataItem > f.dataFim) return false
            }

            if (f.tipo !== '' && f.tipo !== null && f.tipo !== undefined) {
                const tipoFiltro = String(f.tipo)
                const tagId = item.tag_id == null || item.tag_id === '' ? 0 : Number(item.tag_id)
                if (tipoFiltro === '0') {
                    if (tagId) return false
                } else if (String(tagId) !== tipoFiltro) {
                    return false
                }
            }

            if (f.fluxo) {
                if (!this.passaFiltroFluxo(item, String(f.fluxo))) return false
            }

            return true
        },
        passaFiltroFluxo(item, status) {
            const gestor = String(item.status || '')
            const rh = String(item.resposta_rh || '')
            const semRh = !rh

            if (status === 'aberto') {
                return gestor !== 'reprovado' && semRh
            }
            if (status === 'pendente_gestor') {
                return gestor === 'aberto' && semRh
            }
            if (status === 'reprovado_gestor') {
                return gestor === 'reprovado'
            }
            if (status === 'pendente_rh') {
                return gestor === 'aprovado' && semRh
            }
            if (status === 'aprovado_rh') {
                return rh === 'aprovado'
            }
            if (status === 'reprovado_rh') {
                return rh === 'reprovado'
            }
            if (status === 'reprovado') {
                return gestor === 'reprovado' || rh === 'reprovado'
            }
            return true
        },
        formVazio() {
            return {
                id: null,
                tag_id: '',
                outra_tag: '',
                area_id: '',
                outra_area: '',
                centro_custo_id: null,
                acao: '',
                obs_lancamento: '',
                data_lancamento: '',
                obs_aprovacao: '',
                obs_rh: '',
                data_aprovacao: '',
                data_aprovacao_rh: '',
                status: '',
                resposta_rh: '',
                colaboradores: [],
                anexos: [],
                anexosDel: [],
                tag: null,
                area: null,
                centro_de_custo: null,
                gestor_aprovacao: null,
                responsavel_lancamento: { nome: '' },
                responsavel_aprovacao: null,
                rh_aprovacao: null,
                created_at: ''
            }
        },
        cloneDeepLocal(valor) {
            if (typeof window !== 'undefined' && window._ && typeof window._.cloneDeep === 'function') {
                return window._.cloneDeep(valor)
            }
            return JSON.parse(JSON.stringify(valor))
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === itemId
        },
        toggleDropdown(itemId) {
            this.dropdownAbertoKey = this.dropdownAbertoKey === itemId ? null : itemId
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        onClickOutside(e) {
            if (!this.$el || !this.$el.contains(e.target)) {
                this.fecharDropdown()
            }
        },
        formVisualizar(id) {
            this.tituloJanela = `Visualizando CIH #${id}`
            this.preloadAjax = true
            this.form = this.cloneDeepLocal(this.formDefault || this.formVazio())

            axios
                .get(`${URL_ADMIN}/apontamento/cih/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    if (!Array.isArray(this.form.colaboradores)) {
                        this.form.colaboradores = []
                    }
                    if (!Array.isArray(this.form.anexos)) {
                        this.form.anexos = []
                    }
                    if (!Array.isArray(this.form.anexosDel)) {
                        this.form.anexosDel = []
                    }
                    this.preloadAjax = false
                })
                .catch(() => {
                    this.preloadAjax = false
                })
        },
        async atualizar() {
            this.preload = true
            try {
                const res = await axios.get(`${URL_ADMIN}/historico/cih/${this.fc_token}`)
                const data = res.data || {}
                this.lista = data.cih || []
                this.config_modelo_cih = data.config_modelo_cih || null
                this.lista_ccs = data.cc || null
            } finally {
                this.preload = false
            }
        },
        labelTipoLista(item) {
            if (!item) return 'Não informado'
            if (item.tag && item.tag.label) return item.tag.label
            const outra = (item.outra_tag || '').toString().trim()
            if (outra && outra !== '0') return `${outra} (OUTRO)`
            return 'OUTRO'
        },
        labelAreaLista(item) {
            if (!item) return ''
            return (item.area && item.area.label) || item.outra_area || ''
        },
        labelCentroCustoLista(item) {
            if (!item) return ''
            return (item.centro_de_custo && item.centro_de_custo.label) || item.centro_custo_outro || ''
        },
        linhaEscopoCompacta(item) {
            if (!item) return []
            const partes = []
            if (this.AUTENTICADO && this.AUTENTICADO.temFilial) {
                const lotacao = this.labelLotacaoLista(item)
                if (lotacao) partes.push({ label: 'Lotação', valor: lotacao })
            }
            if (this.config_modelo_cih === 'centro_de_custo') {
                const cc = this.labelCentroCustoLista(item)
                if (cc) partes.push({ label: 'CC', valor: cc })
            }
            if (this.config_modelo_cih === 'area') {
                const area = this.labelAreaLista(item)
                if (area) partes.push({ label: 'Área', valor: area })
            }
            const gestor = item.gestor_aprovacao && item.gestor_aprovacao.nome
            if (gestor) partes.push({ label: 'Gestor', valor: gestor })
            return partes
        },
        formatLotacaoPorCentroCustoId(centroCustoId) {
            if (!centroCustoId || !this.lista_ccs || !this.lista_ccs.centros_custos) return ''
            for (const [cnpjKey, centros] of Object.entries(this.lista_ccs.centros_custos)) {
                const encontrado = (centros || []).find((centro) => Number(centro.id) === Number(centroCustoId))
                if (!encontrado) continue
                const info = this.lista_ccs.cnpjs?.[cnpjKey] ?? {}
                const tipo = encontrado.matriz ? 'Matriz' : 'Filial'
                const nome = info.nome_fantasia || info.razao_social || encontrado.nome_fantasia || encontrado.razao_social
                const cnpj = info.cnpj || encontrado.cnpj_format
                if (nome && cnpj) return `${nome} - ${cnpj} (${tipo})`
                if (nome) return `${nome} (${tipo})`
                return tipo
            }
            return ''
        },
        labelLotacaoColaborador(colaborador) {
            const centroId = colaborador?.centro_custo_id || colaborador?.admissao?.centro_custo_id
            if (centroId) return this.formatLotacaoPorCentroCustoId(centroId)
            return colaborador?.lotacao || ''
        },
        labelNomeColaboradorModal(colaborador) {
            return colaborador?.nome || colaborador?.curriculo?.nome || colaborador?.label || ''
        },
        labelCargoColaboradorModal(colaborador) {
            return colaborador?.cargo || colaborador?.admissao?.cargo || ''
        },
        labelCentroCustoColaboradorModal(colaborador) {
            return (
                colaborador?.centro_custo ||
                colaborador?.admissao?.centro_custo?.label ||
                'Não informado'
            )
        },
        labelLotacaoColaboradorModal(colaborador) {
            if (!colaborador) return 'Não informado'
            if (colaborador.lotacao) return colaborador.lotacao
            return this.labelLotacaoColaborador(colaborador) || 'Não informado'
        },
        labelLotacaoLista(item) {
            if (!this.AUTENTICADO?.temFilial || !item) return ''
            const colaboradores = Array.isArray(item.colaboradores) ? item.colaboradores : []
            const labels = [
                ...new Set(colaboradores.map((c) => this.labelLotacaoColaborador(c)).filter(Boolean))
            ]
            if (!labels.length) return this.formatLotacaoPorCentroCustoId(item.centro_custo_id) || ''
            if (labels.length === 1) return labels[0]
            return labels.join(' · ')
        },
        resumoAcaoLista(item) {
            const acao = (item?.acao || '').trim()
            if (!acao) return 'Não informado'
            if (acao.length <= 120) return acao
            return `${acao.slice(0, 117).trim()}...`
        },
        etapaAtualLista(item) {
            return etapaAtualFluxoAprovacao(item, {
                campoGestor: 'status',
                campoRh: 'resposta_rh',
                temAprovacaoExtra: false
            })
        },
        chaveStatusLista(item) {
            return varianteStatusFluxo(this.etapaAtualLista(item))
        },
        textoStatusLista(item) {
            return textoStatusFluxo(this.etapaAtualLista(item))
        },
        fluxoStepsLista(item) {
            if (!item) return []

            let statusGestor = 'aguardando'
            if (item.status === 'aprovado') statusGestor = 'aprovado'
            else if (item.status === 'reprovado') statusGestor = 'reprovado'

            let statusRh = 'pendente'
            if (item.status === 'reprovado') statusRh = 'cancelado'
            else if (item.resposta_rh === 'aprovado') statusRh = 'aprovado'
            else if (item.resposta_rh === 'reprovado') statusRh = 'reprovado'
            else if (item.status === 'aprovado') statusRh = 'aguardando'

            return [
                {
                    key: 'lancamento',
                    label: 'Lançamento',
                    status: 'aprovado',
                    nome: item.responsavel_lancamento?.nome,
                    data: item.created_at
                },
                {
                    key: 'gestor',
                    label: 'Gestor',
                    status: statusGestor,
                    nome: item.responsavel_aprovacao?.nome,
                    data: item.data_aprovacao
                },
                {
                    key: 'rh',
                    label: 'RH',
                    status: statusRh,
                    nome: item.rh_aprovacao?.nome,
                    data: item.data_aprovacao_rh
                }
            ]
        }
    }
}
</script>

<style scoped>
.historico-cih-valor {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #212529;
    line-height: 1.35;
}

.historico-cih-filtros {
    margin-bottom: 0.65rem;
    padding: 0.55rem 0.65rem 0.35rem;
    background: #f4f7f9;
    border: 1px solid #e4e8ec;
    border-radius: 8px;
}
.historico-cih-filtros__acoes {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin: 0 0 0.35rem;
    padding-top: 0.15rem;
}
.historico-cih-filtros__contagem {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
}

.historico-cih-lista {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.historico-cih-card {
    background: #fff;
    border: 1px solid #e4e8ec;
    border-radius: 8px;
    border-left: 3px solid #174257;
    padding: 0.55rem 0.7rem 0.5rem;
}

.historico-cih-card--aprovado,
.historico-cih-card--rh {
    border-left-color: #28a745;
}
.historico-cih-card--reprovado {
    border-left-color: #dc3545;
}
.historico-cih-card--gestor {
    border-left-color: #ffc107;
}
.historico-cih-card--aberto,
.historico-cih-card--pendente {
    border-left-color: #174257;
}

.historico-cih-card__topo {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.2rem;
}

.historico-cih-card__ident {
    min-width: 0;
    flex: 1;
}

.historico-cih-card__titulo {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #174257;
    line-height: 1.25;
}

.historico-cih-card__meta-id {
    margin: 0.1rem 0 0;
    font-size: 0.75rem;
    line-height: 1.3;
}

.historico-cih-card__id {
    font-weight: 600;
    color: #6c757d;
}

.historico-cih-card__status {
    font-weight: 600;
    color: #6c757d;
}
.historico-cih-card__status--aprovado,
.historico-cih-card__status--rh {
    color: #1e7e34;
}
.historico-cih-card__status--reprovado {
    color: #dc3545;
}
.historico-cih-card__status--gestor {
    color: #856404;
}
.historico-cih-card__status--aberto,
.historico-cih-card__status--pendente {
    color: #174257;
}

.historico-cih-card__acoes {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 1px solid #dee2e6;
    color: #6c757d;
    text-decoration: none;
    flex-shrink: 0;
}

.historico-cih-card__linha {
    margin: 0.12rem 0 0;
    font-size: 0.78rem;
    line-height: 1.35;
    color: #495057;
}

.historico-cih-card__linha--acao {
    color: #343a40;
}

.historico-cih-card__k {
    font-weight: 600;
    color: #6c757d;
    margin-right: 0.15rem;
}

.historico-cih-card__sep {
    margin: 0 0.35rem;
    color: #ced4da;
}

.historico-cih-card__fluxo {
    margin-top: 0.4rem;
    padding-top: 0.35rem;
    border-top: 1px solid #eef1f4;
}

.historico-cih-card__fluxo :deep(.mybp-fluxo) {
    gap: 0.35rem;
}
.historico-cih-card__fluxo :deep(.mybp-fluxo-step) {
    padding: 0.25rem 0.4rem;
}
.historico-cih-card__fluxo :deep(.mybp-fluxo-step__label) {
    font-size: 0.65rem;
}
.historico-cih-card__fluxo :deep(.mybp-fluxo-step__nome),
.historico-cih-card__fluxo :deep(.mybp-fluxo-step__data) {
    font-size: 0.7rem;
}

.historico-cih :deep(.mybp-modal-secao) {
    margin-bottom: 0.65rem;
    padding-bottom: 0.55rem;
}
.historico-cih :deep(.mybp-modal-secao > legend) {
    font-size: 0.8rem;
    margin-bottom: 0.4rem;
}
.historico-cih :deep(.mybp-filtro-campo) {
    margin-bottom: 0.45rem;
}
.historico-cih :deep(.cih-modal-table) {
    font-size: 0.78rem;
}
</style>
