<template>
    <div id="componentePainelSaudeOcupacional">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div class="text-muted">
                <span v-if="resumo">Situação em {{ resumo.data_referencia_br }}</span>
            </div>
            <button type="button" class="btn btn-sm btn-success" :disabled="carregando" @click="carregar">
                <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
            </button>
        </div>

        <p v-if="carregando && !resumo" class="text-center"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

        <div v-if="erro" class="alert alert-danger">{{ erro }}</div>

        <template v-if="resumo">
            <div v-if="!resumo.janela_configurada" class="alert alert-info py-2">
                A janela de alerta de vencimento não está configurada para esta empresa. Usando {{ resumo.janela_vencendo_dias }} dias.
            </div>

            <h5 class="painel-secao">ASO</h5>
            <div class="row">
                <div class="col-12 col-sm-6 col-xl-3 mb-3">
                    <painel-indicador
                        titulo="Vencidos"
                        icone="fa fa-exclamation-circle"
                        cor="perigo"
                        :valor="resumo.aso.vencidos"
                        descricao="Colaboradores com o último ASO vencido."
                        :link="linkConformidade('vencido')"
                        texto-acao="Ver e encaminhar"
                    />
                </div>
                <div class="col-12 col-sm-6 col-xl-3 mb-3">
                    <painel-indicador
                        titulo="Vencendo"
                        icone="fa fa-clock"
                        cor="alerta"
                        :valor="resumo.aso.vencendo"
                        :descricao="`Vencem nos próximos ${resumo.janela_vencendo_dias} dias.`"
                        :link="linkConformidade('vencendo')"
                        texto-acao="Ver e encaminhar"
                    />
                </div>
                <div class="col-12 col-sm-6 col-xl-3 mb-3">
                    <painel-indicador
                        titulo="Sem ASO"
                        icone="fa fa-user-times"
                        cor="perigo"
                        :valor="resumo.aso.sem_aso"
                        descricao="Ativos sem nenhum ASO válido registrado."
                        :link="linkConformidade('sem_aso')"
                        texto-acao="Encaminhar exame"
                    />
                </div>
                <div class="col-12 col-sm-6 col-xl-3 mb-3">
                    <painel-indicador
                        titulo="Em dia"
                        icone="fa fa-check-circle"
                        cor="sucesso"
                        :valor="resumo.aso.em_dia"
                        :descricao="`De ${resumo.aso.total} colaboradores ativos.`"
                    />
                </div>
            </div>

            <h5 class="painel-secao">Afastamentos</h5>
            <div class="row">
                <div class="col-12 col-sm-6 col-xl-4 mb-3">
                    <painel-indicador
                        titulo="Afastados hoje"
                        icone="fa fa-procedures"
                        cor="info"
                        :valor="resumo.afastamentos.afastados_hoje"
                        descricao="Colaboradores ativos com afastamento em curso."
                        emite-acao
                        @acao="focarAfastados"
                    />
                </div>
                <div class="col-12 col-sm-6 col-xl-4 mb-3">
                    <painel-indicador
                        titulo="Voltando em breve"
                        icone="fa fa-calendar-check"
                        cor="alerta"
                        :valor="resumo.afastamentos.voltando_em_breve"
                        descricao="Voltam nos próximos 7 dias. Confira se precisa de ASO de retorno."
                        emite-acao
                        @acao="focarAfastados"
                    />
                </div>
                <div class="col-12 col-sm-6 col-xl-4 mb-3">
                    <painel-indicador
                        titulo="Mais de 15 dias"
                        icone="fa fa-file-medical"
                        cor="perigo"
                        :valor="resumo.afastamentos.acima_limite_inss"
                        descricao="Afastamentos acima de 15 dias: encaminhar ao INSS."
                        emite-acao
                        @acao="focarAfastados"
                    />
                </div>
            </div>

            <template v-if="resumo.programas">
                <h5 class="painel-secao">PCMSO, PGR e documentos</h5>
                <div class="row">
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="painel-programa" :class="resumo.programas.coordenador ? 'ok' : 'alerta'">
                            <div class="painel-programa__titulo"><i class="fa fa-user-md"></i> Médico coordenador</div>
                            <template v-if="resumo.programas.coordenador">
                                <strong>{{ resumo.programas.coordenador.nome }}</strong>
                                <div class="small text-muted">{{ resumo.programas.coordenador.crm_completo }}<span v-if="resumo.programas.coordenador.vigencia_fim_br"> · até {{ resumo.programas.coordenador.vigencia_fim_br }}</span></div>
                            </template>
                            <div v-else class="small">Nenhum coordenador do PCMSO vigente (exigido pela NR-7).</div>
                            <a v-if="links.medicos" :href="links.medicos" class="small font-weight-bold">Médicos responsáveis <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <painel-indicador
                            titulo="Vínculos vencendo"
                            icone="fa fa-id-badge"
                            cor="alerta"
                            :valor="resumo.programas.vinculos_vencendo"
                            descricao="Vínculos de médicos que terminam em até 30 dias."
                            :link="links.medicos"
                            texto-acao="Renovar vínculos"
                        />
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="painel-programa" :class="resumo.programas.pgr_vigente ? 'ok' : 'alerta'">
                            <div class="painel-programa__titulo"><i class="fa fa-shield-alt"></i> PGR</div>
                            <template v-if="resumo.programas.pgr_vigente">
                                <strong>Versão {{ resumo.programas.pgr_vigente.versao }} vigente</strong>
                                <div class="small text-muted">Expostos a risco: periódico anual (NR-7).</div>
                            </template>
                            <div v-else class="small">Nenhum PGR publicado. Exposição a risco não é considerada nos vencimentos.</div>
                            <a v-if="links.pgr" :href="links.pgr" class="small font-weight-bold">Riscos por cargo <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3" v-if="resumo.programas.pcmso">
                        <div class="painel-programa" :class="pcmsoOk ? 'ok' : 'alerta'">
                            <div class="painel-programa__titulo"><i class="fa fa-notes-medical"></i> PCMSO</div>
                            <strong>{{ resumo.programas.pcmso.programas_ativos - resumo.programas.pcmso.sem_vigente }} de {{ resumo.programas.pcmso.programas_ativos }} com versão vigente</strong>
                            <div class="small">
                                <span v-if="resumo.programas.pcmso.vencidos" class="text-danger">{{ resumo.programas.pcmso.vencidos }} vencido(s). </span>
                                <span v-if="resumo.programas.pcmso.vencendo" class="text-warning">{{ resumo.programas.pcmso.vencendo }} vence(m) em até 60 dias.</span>
                                <span v-if="pcmsoOk" class="text-muted">Tudo em dia.</span>
                            </div>
                            <a v-if="links.pcmso" :href="links.pcmso" class="small font-weight-bold">Programas PCMSO <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3" v-if="resumo.programas.documentos">
                        <div class="painel-programa" :class="documentosOk ? 'ok' : 'alerta'">
                            <div class="painel-programa__titulo"><i class="fa fa-folder-open"></i> Documentos SST</div>
                            <strong>{{ resumo.programas.documentos.total }} documento(s)</strong>
                            <div class="small">
                                <span v-if="resumo.programas.documentos.vencidos" class="text-danger">{{ resumo.programas.documentos.vencidos }} vencido(s). </span>
                                <span v-if="resumo.programas.documentos.vencendo" class="text-warning">{{ resumo.programas.documentos.vencendo }} vence(m) em até 60 dias.</span>
                                <span v-if="documentosOk" class="text-muted">Nenhum documento vencendo.</span>
                            </div>
                            <a v-if="links.documentos" :href="linkDocumentos" class="small font-weight-bold">Documentos SST <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <painel-indicador
                            titulo="Cargos sem avaliação"
                            icone="fa fa-clipboard-list"
                            cor="info"
                            :valor="resumo.programas.cargos_sem_avaliacao"
                            descricao="Cargos com colaboradores ativos que não estão no PGR vigente."
                            :link="links.pgr"
                            texto-acao="Avaliar cargos"
                        />
                    </div>
                </div>
            </template>

            <painel-afastados-lista
                ref="afastados"
                :itens="resumo.afastamentos.lista"
                :total="resumo.afastamentos.afastados_hoje"
            />
        </template>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import PainelIndicador from './PainelIndicador.vue'
import PainelAfastadosLista from './PainelAfastadosLista.vue'

const API = {
    dados: () => `${URL_ADMIN}/saude-ocupacional/painel/dados`
}

export default defineComponent({
    name: 'PainelSaudeOcupacional',

    components: { PainelIndicador, PainelAfastadosLista },

    data() {
        return {
            carregando: false,
            erro: '',
            resumo: null,
            links: {}
        }
    },

    computed: {
        documentosOk() {
            const d = this.resumo && this.resumo.programas && this.resumo.programas.documentos
            return !!d && d.vencidos === 0 && d.vencendo === 0
        },

        /** Abre já filtrado no que precisa de ação. */
        linkDocumentos() {
            const d = this.resumo.programas.documentos
            if (d.vencidos) return `${this.links.documentos}?situacao=vencido`
            if (d.vencendo) return `${this.links.documentos}?situacao=vencendo`
            return this.links.documentos
        },

        pcmsoOk() {
            const p = this.resumo && this.resumo.programas && this.resumo.programas.pcmso
            return !!p && p.sem_vigente === 0 && p.vencidos === 0 && p.vencendo === 0
        }
    },

    mounted() {
        this.carregar()
    },

    methods: {
        /** Card → Conformidade já filtrada; sem acesso, cai no relatório antigo. */
        linkConformidade(status) {
            if (this.links.conformidade) {
                return `${this.links.conformidade}?status=${status}`
            }
            return status === 'sem_aso' ? this.links.controle_exames : this.links.vencimento_asos
        },

        async carregar() {
            this.carregando = true
            this.erro = ''
            try {
                const { data } = await axios.get(API.dados())
                this.resumo = data.resumo
                this.links = data.links || {}
            } catch (e) {
                this.erro = e?.response?.data?.message || 'Não foi possível carregar o Painel.'
            } finally {
                this.carregando = false
            }
        },

        focarAfastados() {
            this.$refs.afastados?.focar()
        }
    }
})
</script>

<style scoped>
.painel-programa {
    height: 100%;
    border: 1px solid #e3e6ea;
    border-left: 4px solid #6c757d;
    border-radius: 6px;
    padding: 12px 14px;
    background: #fff;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.painel-programa.ok { border-left-color: #28a745; }
.painel-programa.alerta { border-left-color: #f0ad4e; }
.painel-programa__titulo {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}
.painel-secao {
    font-size: 0.95rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #495057;
    margin: 8px 0 12px;
}
</style>
