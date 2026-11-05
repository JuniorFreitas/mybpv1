<template>
    <div id="componenteRegrasEmpresa">
        <p class="text-center" v-if="carregando && !opcoes"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

        <template v-if="opcoes">
            <regras-mercado class="mb-3" :regras="opcoes.regras_mercado" />

            <div class="card card-outline card-primary mb-3">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Regras da empresa</h3>
                    <div class="d-flex flex-wrap">
                        <div class="dropdown mr-1 mb-1" ref="menuModelos">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary dropdown-toggle"
                                aria-haspopup="true"
                                :aria-expanded="menuModelosAberto ? 'true' : 'false'"
                                @click="menuModelosAberto = !menuModelosAberto"
                            >
                                <i class="fa fa-magic"></i> Usar modelo
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" :class="{ show: menuModelosAberto }">
                                <a v-for="m in opcoes.modelos" :key="m.modelo" class="dropdown-item" href="javascript://" @click="novaDoModelo(m)">
                                    {{ m.nome }}
                                </a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary mr-1 mb-1" @click="nova">
                            <i class="fa fa-plus"></i> Nova regra
                        </button>
                        <button type="button" class="btn btn-sm btn-success mb-1" :disabled="carregando" @click="carregar">
                            <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Monte regras do tipo <strong>QUANDO</strong> (idade, cargo) <strong>ENTÃO</strong> (validade do ASO).
                        O padrão de cada tipo de exame continua em <a :href="urlPeriodicidade">Periodicidade ASO</a>;
                        as regras só encurtam esse prazo.
                    </p>

                    <div v-if="!regras.length" class="text-center text-muted py-4">
                        Nenhuma regra ainda. Comece por um modelo, como <strong>Idade 40+ anual</strong>.
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Regra</th>
                                    <th>Quando</th>
                                    <th>Validade</th>
                                    <th>Tipos de exame</th>
                                    <th>Status</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="regra in regras" :key="regra.id" :class="{ 'text-muted': !regra.ativo }">
                                    <td>
                                        <strong>{{ regra.nome }}</strong>
                                        <div class="small text-muted">{{ regra.updated_at_br }}<span v-if="regra.usuario"> · {{ regra.usuario }}</span></div>
                                    </td>
                                    <td>{{ regra.condicoes_texto }}</td>
                                    <td class="text-nowrap">{{ regra.periodicidade_texto }}</td>
                                    <td class="small">{{ textoTipos(regra) }}</td>
                                    <td>
                                        <span class="badge" :class="regra.ativo ? 'badge-success' : 'badge-secondary'">{{ regra.ativo ? 'Ativa' : 'Inativa' }}</span>
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <button type="button" class="btn btn-xs btn-outline-primary mr-1" title="Editar" @click="editar(regra)">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-xs mr-1"
                                            :class="regra.ativo ? 'btn-outline-warning' : 'btn-outline-success'"
                                            :title="regra.ativo ? 'Desativar' : 'Ativar'"
                                            @click="acao(regra, regra.ativo ? 'desativar' : 'ativar')"
                                        >
                                            <i :class="regra.ativo ? 'fa fa-pause' : 'fa fa-play'"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-danger" title="Excluir" @click="acao(regra, 'excluir')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <regras-simulador ref="simulador" class="mb-3" />
            <regras-historico ref="historico" />

            <regra-form-modal ref="formModal" :opcoes="opcoes" @salvo="aposAlterar" />
            <regra-acao-modal ref="acaoModal" @concluido="aposAlterar" />
        </template>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import RegrasMercado from './RegrasMercado.vue'
import RegraFormModal from './RegraFormModal.vue'
import RegraAcaoModal from './RegraAcaoModal.vue'
import RegrasSimulador from './RegrasSimulador.vue'
import RegrasHistorico from './RegrasHistorico.vue'

export default defineComponent({
    name: 'RegrasEmpresa',

    components: { RegrasMercado, RegraFormModal, RegraAcaoModal, RegrasSimulador, RegrasHistorico },

    data() {
        return {
            carregando: false,
            regras: [],
            opcoes: null,
            menuModelosAberto: false
        }
    },

    computed: {
        urlPeriodicidade() {
            return `${URL_ADMIN}/cadastro/aso-periodicidade`
        },
        totalTipos() {
            return (this.opcoes && this.opcoes.tipos_exame.length) || 0
        }
    },

    mounted() {
        this.carregar()
        document.addEventListener('click', this.fecharMenuForaDele)
    },

    beforeUnmount() {
        document.removeEventListener('click', this.fecharMenuForaDele)
    },

    methods: {
        fecharMenuForaDele(evento) {
            if (this.menuModelosAberto && this.$refs.menuModelos && !this.$refs.menuModelos.contains(evento.target)) {
                this.menuModelosAberto = false
            }
        },

        async carregar() {
            this.carregando = true
            try {
                const { data } = await axios.get(`${URL_ADMIN}/saude-ocupacional/regras/dados`)
                this.regras = data.regras || []
                this.opcoes = data.opcoes
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.carregando = false
            }
        },

        textoTipos(regra) {
            if (regra.exame_tipo_ids.length >= this.totalTipos && this.totalTipos > 0) {
                return 'Todos com vencimento'
            }
            return regra.tipos.join(', ')
        },

        nova() {
            this.$refs.formModal.abrir(null)
        },

        novaDoModelo(modelo) {
            this.menuModelosAberto = false
            this.$refs.formModal.abrir({ ...modelo, id: null })
        },

        editar(regra) {
            this.$refs.formModal.abrir(regra)
        },

        acao(regra, tipo) {
            this.$refs.acaoModal.abrir(regra, tipo)
        },

        aposAlterar() {
            this.carregar()
            this.$refs.historico && this.$refs.historico.carregar()
            this.$refs.simulador && this.$refs.simulador.atualizar()
        }
    }
})
</script>
