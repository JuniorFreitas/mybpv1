<template>
    <modal id="modalEnviarDocumentoSst" ref="modal" :titulo="documento ? `Nova versão — ${documento.titulo}` : 'Enviar documento SST'" :size="60" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos">
                <fieldset class="mybp-modal-secao" v-if="!documento">
                    <legend>Documento</legend>
                    <div class="form-row">
                        <div class="col-md-5 mb-2">
                            <label class="mybp-label" for="doc-sst-tipo">Tipo</label>
                            <select id="doc-sst-tipo" class="form-control form-control-sm" v-model="form.tipo" @change="aoTrocarTipo">
                                <option value="">Escolha</option>
                                <option v-for="t in opcoes.tipos" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                        <div class="col-md-7 mb-2">
                            <label class="mybp-label" for="doc-sst-titulo">Título</label>
                            <input id="doc-sst-titulo" type="text" maxlength="200" class="form-control form-control-sm" v-model.trim="form.titulo" placeholder="Ex.: LTCAT 2026 — unidade matriz" />
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="mybp-label" for="doc-sst-responsavel">Responsável técnico (opcional)</label>
                            <input id="doc-sst-responsavel" type="text" maxlength="150" class="form-control form-control-sm" v-model.trim="form.responsavel" placeholder="Nome e registro (CRM, CREA…)" />
                        </div>
                        <div class="col-md-6 mb-2 position-relative">
                            <label class="mybp-label" for="doc-sst-colaborador">Colaborador (opcional)</label>
                            <input id="doc-sst-colaborador" type="search" class="form-control form-control-sm" v-model="buscaColaborador" @input="buscarComAtraso" placeholder="Só para documento individual" autocomplete="off" />
                            <ul class="list-group resultados" v-if="resultados.length">
                                <li v-for="r in resultados" :key="r.feedback_id" class="list-group-item list-group-item-action py-1" role="button" @click="escolherColaborador(r)">
                                    {{ r.nome }} <small class="text-muted">{{ r.cargo || '' }}</small>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox" v-if="opcoes.ve_dados_sensiveis">
                        <input id="doc-sst-sigilo" type="checkbox" class="custom-control-input" v-model="sigiloso" :disabled="tipoSempreSigiloso" />
                        <label class="custom-control-label small" for="doc-sst-sigilo">Sigiloso — só o perfil Saúde vê (laudos e exames médicos)</label>
                    </div>
                </fieldset>

                <fieldset class="mybp-modal-secao">
                    <legend>Arquivo e vigência</legend>
                    <div class="mb-2">
                        <label class="mybp-label" for="doc-sst-arquivo">Arquivo</label>
                        <input id="doc-sst-arquivo" ref="arquivo" type="file" class="form-control-file form-control-sm" :accept="aceitos" @change="aoEscolherArquivo" />
                        <p class="mybp-modal-legenda mb-0 mt-1">{{ opcoes.extensoes.map((e) => e.toUpperCase()).join(', ') }} · até {{ opcoes.tamanho_maximo_mb }} MB. Guardado em área privada; abre só por aqui.</p>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-2">
                            <datepicker id="doc-sst-emissao" label="Emissão" formsm v-model="form.emissao" :config="{ format: 'DD/MM/YYYY', useCurrent: false }" />
                        </div>
                        <div class="col-md-4 mb-2">
                            <datepicker id="doc-sst-inicio" label="Vigência — início" formsm v-model="form.vigencia_inicio" :config="{ format: 'DD/MM/YYYY', useCurrent: false }" />
                        </div>
                        <div class="col-md-4 mb-2">
                            <datepicker id="doc-sst-fim" label="Vigência — fim" formsm v-model="form.vigencia_fim" :config="{ format: 'DD/MM/YYYY', useCurrent: false }" />
                        </div>
                    </div>
                    <p class="mybp-modal-legenda mb-0">Com fim de vigência, o documento aparece em "Vencendo" {{ opcoes.janela_vencendo_dias }} dias antes e no Painel. Sem fim: "Sem validade".</p>
                </fieldset>

                <fieldset class="mybp-modal-secao" v-if="!documento">
                    <legend>Observação (opcional)</legend>
                    <textarea class="form-control form-control-sm" rows="2" maxlength="1000" v-model.trim="form.observacao" aria-label="Observação"></textarea>
                    <p class="mybp-modal-legenda mb-0 mt-1">Não escreva diagnóstico ou CID aqui.</p>
                </fieldset>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Cancelar</button>
            <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="enviar">
                <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-upload'"></i> Enviar
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import { BASE_DOCUMENTOS } from './utilsDocumentosSst'

const formVazio = () => ({ tipo: '', titulo: '', responsavel: '', observacao: '', feedback_id: null, emissao: '', vigencia_inicio: '', vigencia_fim: '' })

export default defineComponent({
    name: 'DocumentoEnviarModal',

    props: {
        opcoes: { type: Object, required: true }
    },

    emits: ['enviado'],

    data() {
        return { documento: null, form: formVazio(), arquivo: null, sigiloso: false, salvando: false, buscaColaborador: '', resultados: [], timer: null }
    },

    computed: {
        aceitos() {
            return this.opcoes.extensoes.map((e) => `.${e}`).join(',')
        },
        tipoSempreSigiloso() {
            const t = this.opcoes.tipos.find((x) => x.value === this.form.tipo)
            return !!t && t.sigilo === 'sensivel'
        }
    },

    beforeUnmount() {
        clearTimeout(this.timer)
    },

    methods: {
        abrirNovo() {
            this.documento = null
            this.reiniciar()
            this.$refs.modal.abrirModal()
        },

        abrirVersao(documento) {
            this.documento = documento
            this.reiniciar()
            this.$refs.modal.abrirModal()
        },

        reiniciar() {
            this.form = formVazio()
            this.arquivo = null
            this.sigiloso = false
            this.buscaColaborador = ''
            this.resultados = []
            if (this.$refs.arquivo) this.$refs.arquivo.value = ''
        },

        fechar() {
            this.$refs.modal.fecharModal()
        },

        aoTrocarTipo() {
            this.sigiloso = this.tipoSempreSigiloso
            const t = this.opcoes.tipos.find((x) => x.value === this.form.tipo)
            if (t && !this.form.titulo) this.form.titulo = t.label
        },

        aoEscolherArquivo(e) {
            const arquivo = e.target.files && e.target.files[0]
            if (arquivo && arquivo.size > this.opcoes.tamanho_maximo_mb * 1024 * 1024) {
                mostraErro('', `O arquivo pode ter no máximo ${this.opcoes.tamanho_maximo_mb} MB.`)
                e.target.value = ''
                this.arquivo = null
                return
            }
            this.arquivo = arquivo || null
        },

        buscarComAtraso() {
            this.form.feedback_id = null
            clearTimeout(this.timer)
            this.timer = setTimeout(this.buscarColaboradores, 300)
        },

        async buscarColaboradores() {
            const termo = this.buscaColaborador.trim()
            if (termo.length < 3) {
                this.resultados = []
                return
            }
            try {
                const { data } = await axios.get(`${URL_ADMIN}/saude-ocupacional/historico-aso/colaboradores`, { params: { busca: termo } })
                this.resultados = data.itens || []
            } catch (e) {
                this.resultados = []
            }
        },

        escolherColaborador(r) {
            this.form.feedback_id = r.feedback_id
            this.buscaColaborador = r.nome
            this.resultados = []
        },

        async enviar() {
            if (!this.arquivo) {
                mostraErro('', 'Escolha o arquivo.')
                return
            }
            if (!this.documento && (!this.form.tipo || !this.form.titulo)) {
                mostraErro('', 'Informe o tipo e o título.')
                return
            }

            const dados = new FormData()
            dados.append('arquivo', this.arquivo)
            const campos = this.documento ? ['emissao', 'vigencia_inicio', 'vigencia_fim'] : Object.keys(this.form)
            campos.forEach((c) => {
                if (this.form[c] !== null && this.form[c] !== '') dados.append(c, this.form[c])
            })
            if (!this.documento) dados.append('sigilo', this.sigiloso ? 'sensivel' : 'comum')

            this.salvando = true
            try {
                const url = this.documento ? `${BASE_DOCUMENTOS()}/${this.documento.id}/versoes` : BASE_DOCUMENTOS()
                const { data } = await axios.post(url, dados, { headers: { 'Content-Type': 'multipart/form-data' } })
                mostraSucesso('', data.mensagem)
                this.fechar()
                this.$emit('enviado', this.documento ? this.documento.id : data.id)
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
.resultados {
    position: absolute;
    z-index: 20;
    left: 5px;
    right: 5px;
    max-height: 220px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}
</style>
