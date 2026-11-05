<template>
    <div id="componenteDocumentosSst">
        <div class="d-flex flex-wrap mb-2">
            <button
                v-for="chip in chips"
                :key="chip.valor"
                type="button"
                class="btn btn-sm mr-1 mb-1"
                :class="filtros.situacao === chip.valor ? chip.ativo : 'btn-outline-secondary'"
                @click="filtrarSituacao(chip.valor)"
            >
                {{ chip.label }} <span class="badge badge-light ml-1">{{ chip.total }}</span>
            </button>
        </div>

        <div class="alert alert-info py-1 small d-flex align-items-center justify-content-between" v-if="filtros.feedback_id">
            <span><i class="fa fa-user"></i> Documentos de <strong>{{ colaboradorFiltro || 'um colaborador' }}</strong></span>
            <button type="button" class="btn btn-xs btn-outline-secondary" @click="limparColaborador">Ver todos</button>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex flex-wrap align-items-center mybp-filtros-compactos flex-grow-1 mr-2">
                    <input type="search" class="form-control form-control-sm mr-2 mb-1 filtro-busca" placeholder="Buscar título ou colaborador" v-model="filtros.busca" @input="buscarComAtraso" aria-label="Buscar documento" />
                    <select class="form-control form-control-sm mr-2 mb-1 filtro-tipo" v-model="filtros.tipo" @change="recarregar(1)" aria-label="Filtrar por tipo">
                        <option value="">Todos os tipos</option>
                        <option v-for="t in opcoes ? opcoes.tipos : []" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                </div>
                <button v-if="opcoes && opcoes.pode_configurar" type="button" class="btn btn-sm btn-primary mb-1" @click="$refs.enviar.abrirNovo()">
                    <i class="fa fa-upload"></i> Enviar documento
                </button>
            </div>
            <div class="card-body p-0">
                <p v-if="carregando && !itens.length" class="text-center p-3 mb-0"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>
                <div v-else-if="!itens.length" class="text-center text-muted p-3 mb-0">
                    Nenhum documento neste filtro.
                    <div class="small mt-1" v-if="!temFiltro">PCMSO publicado e ASO assinado entram aqui sozinhos. PGR, LTCAT, laudos e ordens de serviço: use "Enviar documento".</div>
                </div>
                <div v-else class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Situação</th>
                                <th>Vigência</th>
                                <th>Versão</th>
                                <th class="text-right">Arquivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in itens" :key="item.id">
                                <td>
                                    <a href="javascript://" @click="$refs.detalhe.abrir(item.id)"><strong>{{ item.titulo }}</strong></a>
                                    <span v-if="item.sigilo === 'sensivel'" class="badge badge-dark ml-1" title="Visível só para o perfil Saúde"><i class="fa fa-lock"></i> sigiloso</span>
                                    <span v-if="item.gerado_pelo_sistema" class="badge badge-light ml-1" title="Gerado pelo MyBP">automático</span>
                                    <div class="small text-muted">
                                        <span v-if="item.colaborador">{{ item.colaborador }}</span>
                                        <span v-if="item.colaborador && item.responsavel"> · </span>
                                        <span v-if="item.responsavel">{{ item.responsavel }}</span>
                                    </div>
                                </td>
                                <td class="small">{{ item.tipo_label }}</td>
                                <td><span class="badge" :class="badge(item.situacao)">{{ rotulo(item.situacao) }}</span></td>
                                <td class="text-nowrap small">
                                    <template v-if="item.vigencia_fim_br">
                                        até {{ item.vigencia_fim_br }}
                                        <div :class="item.dias_para_vencer < 0 ? 'text-danger' : 'text-muted'">
                                            {{ item.dias_para_vencer < 0 ? `venceu há ${-item.dias_para_vencer} dias` : item.dias_para_vencer === 0 ? 'vence hoje' : `em ${item.dias_para_vencer} dias` }}
                                        </div>
                                    </template>
                                    <span v-else class="text-muted">{{ item.emissao_br ? `emitido em ${item.emissao_br}` : '—' }}</span>
                                </td>
                                <td class="small">v{{ item.versao }}</td>
                                <td class="text-right text-nowrap">
                                    <a v-if="abreNoNavegador(item.extensao)" :href="urlArquivo(item.versao_id, true)" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary mr-1" title="Ver"><i class="fa fa-eye"></i></a>
                                    <a :href="urlArquivo(item.versao_id, false)" class="btn btn-xs btn-outline-secondary" :title="`Baixar (${(item.extensao || '').toUpperCase()})`"><i class="fa fa-download"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between small" v-if="total">
                <span class="text-muted">{{ total }} documento(s) · página {{ pagina }} de {{ paginas }}</span>
                <div>
                    <button type="button" class="btn btn-xs btn-outline-secondary mr-1" :disabled="pagina <= 1 || carregando" @click="recarregar(pagina - 1)">Anterior</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" :disabled="pagina >= paginas || carregando" @click="recarregar(pagina + 1)">Próxima</button>
                </div>
            </div>
        </div>

        <documento-enviar-modal v-if="opcoes" ref="enviar" :opcoes="opcoes" @enviado="recarregar(pagina)" />
        <documento-detalhe-modal
            v-if="opcoes"
            ref="detalhe"
            :opcoes="opcoes"
            @nova-versao="(doc) => $refs.enviar.abrirVersao(doc)"
            @alterado="recarregar(pagina)"
        />
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import DocumentoEnviarModal from './DocumentoEnviarModal.vue'
import DocumentoDetalheModal from './DocumentoDetalheModal.vue'
import { BASE_DOCUMENTOS, SITUACOES, urlArquivo, abreNoNavegador } from './utilsDocumentosSst'

export default defineComponent({
    name: 'DocumentosSst',

    components: { DocumentoEnviarModal, DocumentoDetalheModal },

    data() {
        const url = new URLSearchParams(window.location.search)
        return {
            carregando: false,
            opcoes: null,
            itens: [],
            total: 0,
            pagina: 1,
            paginas: 1,
            totais: { vigente: 0, vencendo: 0, vencido: 0, sem_validade: 0 },
            timer: null,
            colaboradorFiltro: '',
            filtros: {
                situacao: SITUACOES[url.get('situacao')] ? url.get('situacao') : '',
                tipo: url.get('tipo') || '',
                busca: url.get('busca') || '',
                feedback_id: Number(url.get('feedback_id')) || 0
            }
        }
    },

    computed: {
        chips() {
            const t = this.totais
            const janela = this.opcoes ? this.opcoes.janela_vencendo_dias : 60
            return [
                { valor: '', label: 'Todos', total: t.vigente + t.vencendo + t.vencido + t.sem_validade, ativo: 'btn-primary' },
                { valor: 'vencido', label: 'Vencidos', total: t.vencido, ativo: SITUACOES.vencido.ativo },
                { valor: 'vencendo', label: `Vencendo (${janela} dias)`, total: t.vencendo, ativo: SITUACOES.vencendo.ativo },
                { valor: 'vigente', label: 'Vigentes', total: t.vigente, ativo: SITUACOES.vigente.ativo },
                { valor: 'sem_validade', label: 'Sem validade', total: t.sem_validade, ativo: SITUACOES.sem_validade.ativo }
            ]
        },
        temFiltro() {
            return !!(this.filtros.situacao || this.filtros.tipo || this.filtros.busca.trim() || this.filtros.feedback_id)
        }
    },

    async mounted() {
        try {
            const { data } = await axios.get(`${BASE_DOCUMENTOS()}/opcoes`)
            this.opcoes = data
        } catch (e) {
            // erro exibido pelo interceptor global do axios
        }
        this.recarregar(Number(new URLSearchParams(window.location.search).get('page')) || 1)
    },

    beforeUnmount() {
        clearTimeout(this.timer)
    },

    methods: {
        urlArquivo,
        abreNoNavegador,
        rotulo(situacao) {
            return (SITUACOES[situacao] || {}).label || situacao
        },
        badge(situacao) {
            return (SITUACOES[situacao] || {}).badge || 'badge-light'
        },

        parametros() {
            const p = { page: this.pagina }
            if (this.filtros.situacao) p.situacao = this.filtros.situacao
            if (this.filtros.tipo) p.tipo = this.filtros.tipo
            if (this.filtros.busca.trim()) p.busca = this.filtros.busca.trim()
            if (this.filtros.feedback_id) p.feedback_id = this.filtros.feedback_id
            return p
        },

        sincronizarUrl() {
            const p = this.parametros()
            if (p.page === 1) delete p.page
            const qs = new URLSearchParams(p).toString()
            window.history.replaceState(null, '', window.location.pathname + (qs ? `?${qs}` : ''))
        },

        async recarregar(pagina = 1) {
            this.pagina = pagina
            this.carregando = true
            this.sincronizarUrl()
            try {
                const { data } = await axios.get(`${BASE_DOCUMENTOS()}/dados`, { params: this.parametros() })
                this.itens = data.itens || []
                this.total = data.total
                this.pagina = data.pagina
                this.paginas = data.paginas
                this.totais = data.totais
                if (this.filtros.feedback_id && this.itens.length) {
                    this.colaboradorFiltro = this.itens[0].colaborador || this.colaboradorFiltro
                }
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.carregando = false
            }
        },

        filtrarSituacao(situacao) {
            this.filtros.situacao = situacao
            this.recarregar(1)
        },

        limparColaborador() {
            this.filtros.feedback_id = 0
            this.colaboradorFiltro = ''
            this.recarregar(1)
        },

        buscarComAtraso() {
            clearTimeout(this.timer)
            this.timer = setTimeout(() => this.recarregar(1), 400)
        }
    }
})
</script>

<style scoped>
.filtro-busca {
    max-width: 280px;
}
.filtro-tipo {
    max-width: 260px;
}
</style>
