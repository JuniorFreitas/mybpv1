<template>
    <div id="componentePgrRiscos">
        <p class="text-center" v-if="carregando && !opcoes"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

        <template v-if="opcoes">
            <div class="alert py-2" :class="opcoes.vigente ? 'alert-success' : 'alert-info'">
                <template v-if="opcoes.vigente">
                    <i class="fa fa-shield-alt"></i> PGR vigente: <strong>versão {{ opcoes.vigente.versao }}</strong>.
                    Quem tem cargo com risco nesta versão faz o periódico <strong>anual</strong> (NR-7), aplicado sozinho pelo sistema.
                </template>
                <template v-else>
                    <i class="fa fa-info-circle"></i> Nenhum PGR publicado. Crie uma versão, avalie os riscos de cada cargo e publique.
                    A partir daí, os expostos a risco passam a ter periódico anual.
                </template>
            </div>

            <div class="card card-outline card-primary mb-3">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Versões do PGR</h3>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-1 mb-1" @click="$refs.catalogo.abrir()">
                            <i class="fa fa-list"></i> Catálogo de riscos
                        </button>
                        <button type="button" class="btn btn-sm btn-primary mr-1 mb-1" :disabled="!!rascunho" :title="rascunho ? 'Já existe um rascunho' : ''" @click="criarRascunho">
                            <i class="fa fa-plus"></i> Nova versão
                        </button>
                        <button type="button" class="btn btn-sm btn-success mb-1" :disabled="carregando" @click="carregar">
                            <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="!versoes.length" class="text-center text-muted py-3">Nenhuma versão ainda.</div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Versão</th>
                                    <th>Status</th>
                                    <th>Vigência</th>
                                    <th>Responsável técnico</th>
                                    <th class="text-center">Cargos</th>
                                    <th class="text-center">Riscos</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="v in versoes" :key="v.id" :class="{ 'table-active': selecionada && selecionada.id === v.id }">
                                    <td><strong>{{ v.versao }}</strong></td>
                                    <td><span class="badge" :class="badgeStatus(v.status)">{{ v.status }}</span></td>
                                    <td class="small">{{ v.vigencia_inicio_br || '—' }}<span v-if="v.vigencia_fim_br"> a {{ v.vigencia_fim_br }}</span></td>
                                    <td class="small">{{ v.responsavel_tecnico || '—' }}</td>
                                    <td class="text-center">{{ v.total_cargos }}</td>
                                    <td class="text-center">{{ v.total_riscos }}</td>
                                    <td class="text-right text-nowrap">
                                        <button type="button" class="btn btn-xs btn-outline-primary mr-1" @click="abrirVersao(v)">
                                            <i class="fa fa-eye"></i> {{ v.status === 'rascunho' ? 'Editar' : 'Ver' }}
                                        </button>
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
                        Versão {{ detalhe.inventario.versao }} — riscos por cargo
                        <span class="badge ml-1" :class="badgeStatus(detalhe.inventario.status)">{{ detalhe.inventario.status }}</span>
                    </h3>
                    <div class="d-flex align-items-center mybp-filtros-compactos">
                        <input type="search" class="form-control form-control-sm mr-2" placeholder="Filtrar cargo" v-model="filtro" aria-label="Filtrar cargo" />
                        <label class="mybp-label font-weight-normal mb-0 text-nowrap">
                            <input type="checkbox" v-model="soComColaboradores" /> Só com colaboradores
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-row mybp-filtros-compactos" v-if="detalhe.editavel">
                        <div class="col-12 col-md-4 form-group">
                            <label class="mybp-label" for="pgr-resp">Responsável técnico</label>
                            <input id="pgr-resp" type="text" maxlength="150" class="form-control form-control-sm" v-model.trim="cabecalho.responsavel_tecnico" />
                        </div>
                        <div class="col-12 col-md-3 form-group">
                            <label class="mybp-label" for="pgr-registro">Registro (CREA/MTE)</label>
                            <input id="pgr-registro" type="text" maxlength="60" class="form-control form-control-sm" v-model.trim="cabecalho.responsavel_registro" />
                        </div>
                        <div class="col-12 col-md-4 form-group">
                            <label class="mybp-label" for="pgr-obs">Observação</label>
                            <input id="pgr-obs" type="text" maxlength="1000" class="form-control form-control-sm" v-model.trim="cabecalho.observacao" />
                        </div>
                        <div class="col-12 col-md-1 form-group d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="salvarCabecalho">Salvar</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Cargo</th>
                                    <th class="text-center">Colaboradores ativos</th>
                                    <th>Avaliação</th>
                                    <th class="text-right" v-if="detalhe.editavel">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in cargosFiltrados" :key="c.vaga_id">
                                    <td>{{ c.nome }}</td>
                                    <td class="text-center">{{ c.colaboradores }}</td>
                                    <td class="small">
                                        <span v-if="!c.avaliado" class="badge badge-light">Não avaliado</span>
                                        <span v-else-if="c.ausencia_risco" class="badge badge-success">Ausência de risco</span>
                                        <template v-else>
                                            <span v-for="r in c.riscos" :key="r.exame_risco_id" class="badge badge-warning mr-1">
                                                {{ r.label }}<span v-if="r.intensidade"> · {{ r.intensidade }}</span>
                                            </span>
                                        </template>
                                    </td>
                                    <td class="text-right" v-if="detalhe.editavel">
                                        <button type="button" class="btn btn-xs btn-outline-primary" @click="$refs.cargo.abrir(detalhe.inventario.id, c)">
                                            <i class="fa fa-pen"></i> Riscos
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!cargosFiltrados.length">
                                    <td :colspan="detalhe.editavel ? 4 : 3" class="text-center text-muted">Nenhum cargo no filtro.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <pgr-cargo-modal ref="cargo" :riscos="opcoes.riscos" @salvo="recarregarDetalhe" />
            <pgr-publicar-modal ref="publicar" @publicado="aposPublicar" />
            <risco-catalogo-modal ref="catalogo" :riscos="opcoes.riscos" :tipos="opcoes.tipos_risco" @alterado="carregar" />
        </template>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import PgrCargoModal from './PgrCargoModal.vue'
import PgrPublicarModal from './PgrPublicarModal.vue'
import RiscoCatalogoModal from './RiscoCatalogoModal.vue'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/pgr`

export default defineComponent({
    name: 'PgrRiscos',

    components: { PgrCargoModal, PgrPublicarModal, RiscoCatalogoModal },

    data() {
        return {
            carregando: false,
            versoes: [],
            opcoes: null,
            selecionada: null,
            detalhe: null,
            cabecalho: { responsavel_tecnico: '', responsavel_registro: '', observacao: '' },
            filtro: '',
            soComColaboradores: false
        }
    },

    computed: {
        rascunho() {
            return this.versoes.find((v) => v.status === 'rascunho') || null
        },
        cargosFiltrados() {
            if (!this.detalhe) return []
            const termo = this.filtro.trim().toUpperCase()
            return this.detalhe.cargos.filter((c) =>
                (!termo || c.nome.toUpperCase().includes(termo)) && (!this.soComColaboradores || c.colaboradores > 0)
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

        async carregar() {
            this.carregando = true
            try {
                const { data } = await axios.get(`${BASE()}/dados`)
                this.versoes = data.versoes || []
                this.opcoes = data.opcoes
                const alvo = this.selecionada
                    ? this.versoes.find((v) => v.id === this.selecionada.id)
                    : this.rascunho || this.versoes.find((v) => v.status === 'vigente')
                if (alvo) {
                    await this.abrirVersao(alvo)
                } else {
                    this.selecionada = null
                    this.detalhe = null
                }
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.carregando = false
            }
        },

        async abrirVersao(versao) {
            this.selecionada = versao
            const { data } = await axios.get(`${BASE()}/versoes/${versao.id}`)
            this.detalhe = data
            this.cabecalho = {
                responsavel_tecnico: data.inventario.responsavel_tecnico || '',
                responsavel_registro: data.inventario.responsavel_registro || '',
                observacao: data.inventario.observacao || ''
            }
        },

        recarregarDetalhe() {
            this.carregar()
        },

        async criarRascunho() {
            try {
                const { data } = await axios.post(`${BASE()}/versoes`)
                mostraSucesso('', data.mensagem)
                this.selecionada = { id: data.id }
                await this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        },

        async salvarCabecalho() {
            try {
                const { data } = await axios.put(`${BASE()}/versoes/${this.detalhe.inventario.id}`, this.cabecalho)
                mostraSucesso('', data.mensagem)
                this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        },

        async excluirRascunho(versao) {
            if (!window.confirm(`Excluir o rascunho da versão ${versao.versao}?`)) {
                return
            }
            try {
                const { data } = await axios.delete(`${BASE()}/versoes/${versao.id}`)
                mostraSucesso('', data.mensagem)
                this.selecionada = null
                this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        },

        aposPublicar() {
            this.selecionada = null
            this.carregar()
        }
    }
})
</script>
