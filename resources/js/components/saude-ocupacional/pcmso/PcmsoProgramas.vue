<template>
    <div id="componentePcmsoProgramas">
        <p class="text-center" v-if="carregando && !opcoes"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

        <template v-if="opcoes">
            <div class="alert alert-info py-2 small">
                Cada <strong>programa</strong> é um PCMSO (ex.: por contrato ou cliente). Cada versão define, por cargo, os exames complementares
                de cada tipo de ASO. A versão vigente aparece na ficha de encaminhamento enviada à clínica.
                <span v-if="!opcoes.coordenadores.length" class="d-block text-danger mt-1">
                    Nenhum médico com vínculo de coordenador vigente — cadastre em Configurações → Médicos responsáveis.
                </span>
            </div>

            <div class="card card-outline card-primary mb-3">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Programas (PCMSO)</h3>
                    <div class="d-flex flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-1 mb-1" @click="$refs.catalogo.abrir()">
                            <i class="fa fa-vial"></i> Catálogo de exames
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-1 mb-1" @click="$refs.porRisco.abrir()">
                            <i class="fa fa-link"></i> Exames por risco
                        </button>
                        <button type="button" class="btn btn-sm btn-primary mr-1 mb-1" @click="novoPrograma">
                            <i class="fa fa-plus"></i> Novo programa
                        </button>
                        <button type="button" class="btn btn-sm btn-success mb-1" :disabled="carregando" @click="carregar">
                            <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <input type="search" class="form-control form-control-sm mb-2" placeholder="Filtrar programa" v-model="filtro" aria-label="Filtrar programa" />
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Programa</th>
                                    <th>Versão vigente</th>
                                    <th>Rascunho</th>
                                    <th class="text-center">Encaminhamentos</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in programasFiltrados" :key="p.id" :class="{ 'table-active': programa && programa.id === p.id, 'text-muted': !p.ativo }">
                                    <td>{{ p.label }} <span v-if="!p.ativo" class="badge badge-secondary">inativo</span></td>
                                    <td class="small">
                                        <template v-if="p.vigente">
                                            <span class="badge" :class="p.vigente.vencida ? 'badge-danger' : 'badge-success'">v{{ p.vigente.versao }}</span>
                                            {{ p.vigente.vigencia_inicio_br }}{{ p.vigente.vigencia_fim_br ? ' a ' + p.vigente.vigencia_fim_br : '' }}
                                            <span v-if="p.vigente.vencida" class="text-danger"> (vencido)</span>
                                        </template>
                                        <span v-else class="text-muted">Sem versão</span>
                                    </td>
                                    <td class="small">{{ p.rascunho ? 'v' + p.rascunho.versao : '—' }}</td>
                                    <td class="text-center">{{ p.encaminhamentos }}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-xs btn-outline-primary" @click="abrirPrograma(p)">
                                            <i class="fa fa-folder-open"></i> Abrir
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!programasFiltrados.length">
                                    <td colspan="5" class="text-center text-muted">Nenhum programa.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary mb-3" v-if="programa">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Versões — {{ programa.label }}</h3>
                    <button type="button" class="btn btn-sm btn-primary" :disabled="!!rascunho" :title="rascunho ? 'Já existe um rascunho' : ''" @click="criarRascunho">
                        <i class="fa fa-plus"></i> Nova versão
                    </button>
                </div>
                <div class="card-body">
                    <div v-if="!versoes.length" class="text-center text-muted py-2">Nenhuma versão. Crie a primeira.</div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Versão</th>
                                    <th>Status</th>
                                    <th>Vigência</th>
                                    <th>Coordenador</th>
                                    <th class="text-center">Cargos</th>
                                    <th class="text-center">Exames</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="v in versoes" :key="v.id" :class="{ 'table-active': detalhe && detalhe.versao.id === v.id }">
                                    <td><strong>{{ v.versao }}</strong></td>
                                    <td><span class="badge" :class="badgeStatus(v.status)">{{ v.status }}</span></td>
                                    <td class="small">{{ v.vigencia_inicio_br || '—' }}<span v-if="v.vigencia_fim_br"> a {{ v.vigencia_fim_br }}</span></td>
                                    <td class="small">{{ v.coordenador || '—' }}</td>
                                    <td class="text-center">{{ v.total_cargos }}</td>
                                    <td class="text-center">{{ v.total_exames }}</td>
                                    <td class="text-right text-nowrap">
                                        <button type="button" class="btn btn-xs btn-outline-primary mr-1" @click="abrirVersao(v)">
                                            <i class="fa fa-eye"></i> {{ v.status === 'rascunho' ? 'Editar' : 'Ver' }}
                                        </button>
                                        <a class="btn btn-xs btn-outline-secondary mr-1" :href="urlPdf(v)" target="_blank" rel="noopener" title="PDF"><i class="fa fa-file-pdf"></i></a>
                                        <button v-if="v.status === 'rascunho'" type="button" class="btn btn-xs btn-success mr-1" @click="$refs.publicar.abrir(v)">
                                            <i class="fa fa-check"></i> Publicar
                                        </button>
                                        <button v-if="v.status === 'rascunho'" type="button" class="btn btn-xs btn-outline-danger" title="Excluir rascunho" @click="excluirRascunho(v)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info" v-if="detalhe">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h3 class="card-title mb-0">
                        Versão {{ detalhe.versao.versao }} — exames por cargo
                        <span class="badge ml-1" :class="badgeStatus(detalhe.versao.status)">{{ detalhe.versao.status }}</span>
                    </h3>
                    <div class="d-flex align-items-center mybp-filtros-compactos">
                        <input type="search" class="form-control form-control-sm mr-2" placeholder="Filtrar cargo" v-model="filtroCargo" aria-label="Filtrar cargo" />
                        <label class="mybp-label font-weight-normal mb-0 text-nowrap">
                            <input type="checkbox" v-model="soComColaboradores" /> Só com colaboradores
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-row mybp-filtros-compactos align-items-end" v-if="detalhe.editavel">
                        <div class="col-12 col-md-5 form-group">
                            <label class="mybp-label" for="pcmso-coordenador">Médico coordenador</label>
                            <select id="pcmso-coordenador" class="form-control form-control-sm" v-model="cabecalho.saude_medico_id">
                                <option :value="null">Escolha</option>
                                <option v-for="m in opcoes.coordenadores" :key="m.value" :value="m.value">{{ m.label }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-5 form-group">
                            <label class="mybp-label" for="pcmso-obs">Observação</label>
                            <input id="pcmso-obs" type="text" maxlength="1000" class="form-control form-control-sm" v-model.trim="cabecalho.observacao" />
                        </div>
                        <div class="col-12 col-md-2 form-group">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-block" @click="salvarCabecalho">Salvar</button>
                        </div>
                        <div class="col-12 mb-2">
                            <button type="button" class="btn btn-sm btn-outline-info" :disabled="sugerindo" @click="sugerir">
                                <i :class="sugerindo ? 'fa fa-spinner fa-pulse' : 'fa fa-magic'"></i> Sugerir exames pelo PGR
                            </button>
                            <small class="text-muted ml-2" v-if="!detalhe.pgr_vigente">Publique o PGR para usar a sugestão.</small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Cargo</th>
                                    <th class="text-center">Colaboradores</th>
                                    <th>Riscos (PGR)</th>
                                    <th>Exames complementares</th>
                                    <th class="text-right" v-if="detalhe.editavel">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in cargosFiltrados" :key="c.vaga_id">
                                    <td>{{ c.nome }}</td>
                                    <td class="text-center">{{ c.colaboradores }}</td>
                                    <td class="small">
                                        <span v-for="r in c.riscos" :key="r" class="badge badge-warning mr-1">{{ r }}</span>
                                        <span v-if="!c.riscos.length" class="text-muted">—</span>
                                    </td>
                                    <td class="small">
                                        <div v-for="e in c.exames" :key="e.exame_id">
                                            <strong>{{ e.label }}</strong><span v-if="!e.obrigatorio"> (opcional)</span>
                                            <span class="text-muted"> · {{ e.tipos.join(', ') }}<span v-if="e.periodicidade"> · {{ e.periodicidade }}</span></span>
                                        </div>
                                        <span v-if="!c.exames.length" class="text-muted">Só exame clínico</span>
                                    </td>
                                    <td class="text-right" v-if="detalhe.editavel">
                                        <button type="button" class="btn btn-xs btn-outline-primary" @click="$refs.cargo.abrir(detalhe.versao.id, c)">
                                            <i class="fa fa-pen"></i> Exames
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!cargosFiltrados.length">
                                    <td :colspan="detalhe.editavel ? 5 : 4" class="text-center text-muted">Nenhum cargo no filtro.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <pcmso-cargo-modal ref="cargo" :exames="opcoes.exames" :tipos="opcoes.tipos_exame" @salvo="recarregarVersao" />
            <pcmso-publicar-modal ref="publicar" @publicado="aposPublicar" />
            <exames-catalogo-modal ref="catalogo" :exames="opcoes.exames" @alterado="carregar" />
            <exames-por-risco-modal
                ref="porRisco"
                :riscos="opcoes.riscos"
                :exames="opcoes.exames"
                :tipos="opcoes.tipos_exame"
                :exames-por-risco="opcoes.exames_por_risco || {}"
                @alterado="carregar"
            />
        </template>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import PcmsoCargoModal from './PcmsoCargoModal.vue'
import PcmsoPublicarModal from './PcmsoPublicarModal.vue'
import ExamesCatalogoModal from './ExamesCatalogoModal.vue'
import ExamesPorRiscoModal from './ExamesPorRiscoModal.vue'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/pcmso`

export default defineComponent({
    name: 'PcmsoProgramas',

    components: { PcmsoCargoModal, PcmsoPublicarModal, ExamesCatalogoModal, ExamesPorRiscoModal },

    data() {
        return {
            carregando: false,
            programas: [],
            opcoes: null,
            programa: null,
            versoes: [],
            detalhe: null,
            cabecalho: { saude_medico_id: null, observacao: '' },
            filtro: '',
            filtroCargo: '',
            soComColaboradores: true,
            sugerindo: false
        }
    },

    computed: {
        programasFiltrados() {
            const termo = this.filtro.trim().toUpperCase()
            return this.programas.filter((p) => !termo || p.label.toUpperCase().includes(termo))
        },
        rascunho() {
            return this.versoes.find((v) => v.status === 'rascunho') || null
        },
        cargosFiltrados() {
            if (!this.detalhe) return []
            const termo = this.filtroCargo.trim().toUpperCase()
            return this.detalhe.cargos.filter((c) =>
                (!termo || c.nome.toUpperCase().includes(termo)) &&
                (!this.soComColaboradores || c.colaboradores > 0 || c.exames.length > 0)
            )
        }
    },

    mounted() {
        this.carregar()
    },

    methods: {
        badgeStatus(status) {
            return { vigente: 'badge-success', rascunho: 'badge-warning', arquivado: 'badge-secondary' }[status] || 'badge-light'
        },

        urlPdf(versao) {
            return `${BASE()}/versoes/${versao.id}/pdf`
        },

        async carregar() {
            this.carregando = true
            try {
                const { data } = await axios.get(`${BASE()}/dados`)
                this.programas = data.programas || []
                this.opcoes = data.opcoes
                if (this.programa) {
                    const atual = this.programas.find((p) => p.id === this.programa.id)
                    if (atual) await this.abrirPrograma(atual, this.detalhe ? this.detalhe.versao.id : null)
                }
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.carregando = false
            }
        },

        async abrirPrograma(programa, versaoId = null) {
            this.programa = programa
            const { data } = await axios.get(`${BASE()}/programas/${programa.id}/versoes`)
            this.versoes = data.versoes || []
            const alvo = (versaoId && this.versoes.find((v) => v.id === versaoId)) || this.rascunho || this.versoes.find((v) => v.status === 'vigente')
            if (alvo) {
                await this.abrirVersao(alvo)
            } else {
                this.detalhe = null
            }
        },

        async abrirVersao(versao) {
            const { data } = await axios.get(`${BASE()}/versoes/${versao.id}`)
            this.detalhe = data
            this.cabecalho = { saude_medico_id: data.versao.saude_medico_id || null, observacao: data.versao.observacao || '' }
        },

        recarregarVersao() {
            this.carregar()
        },

        async novoPrograma() {
            const label = window.prompt('Nome do programa (ex.: contrato ou cliente):')
            if (!label) return
            try {
                const { data } = await axios.post(`${BASE()}/programas`, { label })
                mostraSucesso('', data.mensagem)
                this.programa = { id: data.id, label }
                await this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        },

        async criarRascunho() {
            try {
                const { data } = await axios.post(`${BASE()}/programas/${this.programa.id}/versoes`)
                mostraSucesso('', data.mensagem)
                this.detalhe = { versao: { id: data.id } }
                await this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        },

        async salvarCabecalho() {
            try {
                const { data } = await axios.put(`${BASE()}/versoes/${this.detalhe.versao.id}`, this.cabecalho)
                mostraSucesso('', data.mensagem)
                this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        },

        async sugerir() {
            this.sugerindo = true
            try {
                const { data } = await axios.post(`${BASE()}/versoes/${this.detalhe.versao.id}/sugerir`)
                mostraSucesso('', data.mensagem)
                this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.sugerindo = false
            }
        },

        async excluirRascunho(versao) {
            if (!window.confirm(`Excluir o rascunho da versão ${versao.versao}?`)) return
            try {
                const { data } = await axios.delete(`${BASE()}/versoes/${versao.id}`)
                mostraSucesso('', data.mensagem)
                this.detalhe = null
                this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        },

        aposPublicar() {
            this.detalhe = null
            this.carregar()
        }
    }
})
</script>
