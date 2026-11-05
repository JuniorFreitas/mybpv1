<template>
    <modal id="modalDetalheDocumentoSst" ref="modal" :titulo="dados ? dados.documento.titulo : 'Documento SST'" :size="70" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <p v-if="carregando" class="mb-0"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>
            <div v-else-if="dados" class="mybp-modal-form mybp-filtros-compactos">
                <fieldset class="mybp-modal-secao">
                    <legend>Dados</legend>
                    <template v-if="!editando">
                        <div class="small">
                            <strong>{{ dados.documento.tipo_label }}</strong>
                            <span v-if="dados.documento.sigilo === 'sensivel'" class="badge badge-dark ml-1"><i class="fa fa-lock"></i> sigiloso</span>
                            <span v-if="dados.documento.gerado_pelo_sistema" class="badge badge-light ml-1">gerado pelo MyBP</span>
                        </div>
                        <div class="small text-muted" v-if="dados.documento.colaborador">Colaborador: {{ dados.documento.colaborador }}</div>
                        <div class="small text-muted" v-if="dados.documento.responsavel">Responsável: {{ dados.documento.responsavel }}</div>
                        <div class="small mt-1" v-if="dados.documento.observacao">{{ dados.documento.observacao }}</div>
                    </template>
                    <div v-else class="form-row">
                        <div class="col-md-7 mb-2">
                            <label class="mybp-label" for="doc-sst-edit-titulo">Título</label>
                            <input id="doc-sst-edit-titulo" type="text" maxlength="200" class="form-control form-control-sm" v-model.trim="form.titulo" />
                        </div>
                        <div class="col-md-5 mb-2">
                            <label class="mybp-label" for="doc-sst-edit-resp">Responsável técnico</label>
                            <input id="doc-sst-edit-resp" type="text" maxlength="150" class="form-control form-control-sm" v-model.trim="form.responsavel" />
                        </div>
                        <div class="col-12">
                            <label class="mybp-label" for="doc-sst-edit-obs">Observação</label>
                            <textarea id="doc-sst-edit-obs" rows="2" maxlength="1000" class="form-control form-control-sm" v-model.trim="form.observacao"></textarea>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Versões</legend>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Versão</th>
                                    <th>Situação</th>
                                    <th>Vigência</th>
                                    <th>Enviado</th>
                                    <th class="text-right">Arquivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(v, i) in dados.versoes" :key="v.id" :class="{ 'text-muted': i > 0 }">
                                    <td>v{{ v.versao }} <span v-if="i === 0" class="badge badge-primary ml-1">atual</span></td>
                                    <td><span class="badge" :class="badge(v.situacao)">{{ rotulo(v.situacao) }}</span></td>
                                    <td class="small text-nowrap">
                                        <span v-if="v.vigencia_inicio_br || v.vigencia_fim_br">{{ v.vigencia_inicio_br || '—' }} a {{ v.vigencia_fim_br || 'indeterminado' }}</span>
                                        <span v-else-if="v.emissao_br">emitido em {{ v.emissao_br }}</span>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="small">{{ v.enviado_por || '—' }}<div class="text-muted">{{ v.enviado_em_br }}</div></td>
                                    <td class="text-right text-nowrap small">
                                        <span v-if="v.assinado_digitalmente" class="badge badge-success mr-1" title="Assinado digitalmente no MyBP"><i class="fa fa-signature"></i></span>
                                        <a v-if="abreNoNavegador(extensao(v.arquivo))" :href="urlArquivo(v.id, true)" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary mr-1" title="Ver"><i class="fa fa-eye"></i></a>
                                        <a :href="urlArquivo(v.id, false)" class="btn btn-xs btn-outline-secondary" :title="`Baixar ${tamanhoLegivel(v.bytes)}`"><i class="fa fa-download"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Quem acessou</legend>
                    <p v-if="!dados.acessos.length" class="small text-muted mb-0">Ninguém abriu este documento ainda.</p>
                    <ul v-else class="list-unstyled small mb-0 acessos">
                        <li v-for="(a, i) in dados.acessos" :key="i">
                            <strong>{{ a.usuario }}</strong> {{ rotuloAcesso(a.acao) }} a v{{ a.versao }} <span class="text-muted">em {{ a.em_br }}</span>
                        </li>
                    </ul>
                    <p class="mybp-modal-legenda mb-0 mt-1">Últimos 30 acessos. Cada visualização e download fica registrado.</p>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <template v-if="dados && opcoes.pode_configurar">
                <button v-if="!editando" type="button" class="btn btn-sm btn-outline-danger mr-auto" :disabled="salvando" @click="arquivar">
                    <i class="fa fa-archive"></i> Arquivar
                </button>
                <template v-if="editando">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="editando = false">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                        <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> Salvar
                    </button>
                </template>
                <template v-else>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="editar">
                        <i class="fa fa-pen"></i> Editar dados
                    </button>
                    <button v-if="!dados.documento.gerado_pelo_sistema" type="button" class="btn btn-sm btn-primary" @click="novaVersao">
                        <i class="fa fa-upload"></i> Nova versão
                    </button>
                </template>
            </template>
            <button v-if="!editando" type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Fechar</button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import { BASE_DOCUMENTOS, SITUACOES, urlArquivo, abreNoNavegador, tamanhoLegivel } from './utilsDocumentosSst'

const ACESSOS = { visualizar: 'visualizou', baixar: 'baixou', enviar: 'enviou' }

export default defineComponent({
    name: 'DocumentoDetalheModal',

    props: {
        opcoes: { type: Object, required: true }
    },

    emits: ['nova-versao', 'alterado'],

    data() {
        return { id: null, dados: null, carregando: false, salvando: false, editando: false, form: { titulo: '', responsavel: '', observacao: '' } }
    },

    methods: {
        urlArquivo,
        abreNoNavegador,
        tamanhoLegivel,
        rotulo(s) {
            return (SITUACOES[s] || {}).label || s
        },
        badge(s) {
            return (SITUACOES[s] || {}).badge || 'badge-light'
        },
        rotuloAcesso(acao) {
            return ACESSOS[acao] || acao
        },
        extensao(nome) {
            const partes = String(nome || '').split('.')
            return partes.length > 1 ? partes.pop() : ''
        },

        async abrir(id) {
            this.id = id
            this.dados = null
            this.editando = false
            this.$refs.modal.abrirModal()
            await this.carregar()
        },

        async carregar() {
            this.carregando = true
            try {
                const { data } = await axios.get(`${BASE_DOCUMENTOS()}/${this.id}`)
                this.dados = data
            } catch (e) {
                this.fechar()
            } finally {
                this.carregando = false
            }
        },

        fechar() {
            this.$refs.modal.fecharModal()
        },

        editar() {
            const d = this.dados.documento
            this.form = { titulo: d.titulo, responsavel: d.responsavel || '', observacao: d.observacao || '' }
            this.editando = true
        },

        async salvar() {
            if (!this.form.titulo) {
                mostraErro('', 'Informe o título.')
                return
            }
            this.salvando = true
            try {
                const { data } = await axios.put(`${BASE_DOCUMENTOS()}/${this.id}`, this.form)
                mostraSucesso('', data.mensagem)
                this.editando = false
                await this.carregar()
                this.$emit('alterado')
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.salvando = false
            }
        },

        novaVersao() {
            const doc = { id: this.dados.documento.id, titulo: this.dados.documento.titulo }
            this.fechar()
            this.$emit('nova-versao', doc)
        },

        async arquivar() {
            if (!window.confirm('Arquivar este documento? Ele sai das listas, mas o arquivo e o histórico de acessos continuam guardados.')) return
            this.salvando = true
            try {
                const { data } = await axios.delete(`${BASE_DOCUMENTOS()}/${this.id}`)
                mostraSucesso('', data.mensagem)
                this.fechar()
                this.$emit('alterado')
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.salvando = false
            }
        }
    }
})
</script>

<style scoped>
.acessos {
    max-height: 180px;
    overflow-y: auto;
}
</style>
