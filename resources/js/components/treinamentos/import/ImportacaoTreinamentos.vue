<template>
  <div class="importacao-treinamentos">
    <ul class="imp-tabs nav nav-tabs mb-3" role="tablist">
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: aba === 'importacao' }"
          href="#"
          role="tab"
          :aria-selected="aba === 'importacao'"
          @click.prevent="aba = 'importacao'"
        >
          <i class="fas fa-file-import mr-1" aria-hidden="true"></i>
          Importação
        </a>
      </li>
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: aba === 'importados' }"
          href="#"
          role="tab"
          :aria-selected="aba === 'importados'"
          @click.prevent="abrirImportados"
        >
          <i class="fas fa-history mr-1" aria-hidden="true"></i>
          Importados
        </a>
      </li>
    </ul>

    <div v-show="aba === 'importacao'" class="imp-painel">
      <header class="imp-intro">
        <p class="imp-intro-texto mb-0">
          Importe treinamentos em lote com o mesmo comportamento da carteira:
          cria se não existir, atualiza o vencimento se já existir.
        </p>
      </header>

      <div class="imp-dicas" role="region" aria-label="Dicas rápidas">
        <span class="imp-chip"><i class="fas fa-calendar-alt" aria-hidden="true"></i> Datas em <code>dd/mm/aaaa</code></span>
        <span class="imp-chip"><i class="fas fa-list" aria-hidden="true"></i> Use os dropdowns da planilha</span>
        <span class="imp-chip"><i class="fas fa-clock" aria-hidden="true"></i> <code>data_vencimento</code> opcional</span>
      </div>

      <div class="imp-manual card mb-3">
        <div class="card-header">
          <h2 class="imp-manual-titulo mb-0">Manual de importação</h2>
        </div>
        <div class="card-body">
          <ol class="imp-manual-lista mb-3 pl-3">
            <li>Baixe o modelo Excel e preencha a aba <strong>Dados</strong> (não altere os cabeçalhos).</li>
            <li>Em <strong>colaborador</strong> e <strong>treinamento</strong>, use os dropdowns.</li>
            <li>
              Em <strong>data_treinamento</strong>, use <code>dd/mm/aaaa</code>
              (ex.: <code>15/03/2026</code>).
            </li>
            <li>
              Em <strong>data_vencimento</strong> (opcional): vazio = prazo do treinamento;
              preenchido = data informada.
            </li>
            <li>
              Criar e atualizar seguem o fluxo da <strong>carteira/etiquetas</strong>
              (histórico 1 a 1 do vencimento).
            </li>
            <li>Envie o .xlsx e acompanhe o resultado na aba <strong>Importados</strong>.</li>
          </ol>
          <div class="imp-manual-nota">
            Evite <code>15-03-2026</code>, <code>3/15/2026</code> ou ano com 2 dígitos.
            O modelo também traz a aba <strong>Instrucoes</strong>.
          </div>
        </div>
      </div>

      <ol class="imp-passos" aria-label="Passos da importação">
        <li class="imp-passo" :class="{ 'imp-passo-feito': modeloBaixado }">
          <span class="imp-passo-num" aria-hidden="true">1</span>
          <div>
            <strong>Baixar modelo</strong>
            <span>Planilha com colaboradores e treinamentos</span>
          </div>
        </li>
        <li class="imp-passo" :class="{ 'imp-passo-feito': arquivoSelecionado }">
          <span class="imp-passo-num" aria-hidden="true">2</span>
          <div>
            <strong>Preencher</strong>
            <span>Datas em dd/mm/aaaa · vencimento opcional</span>
          </div>
        </li>
        <li class="imp-passo" :class="{ 'imp-passo-feito': mensagemTipo === 'sucesso' && !!mensagem }">
          <span class="imp-passo-num" aria-hidden="true">3</span>
          <div>
            <strong>Enviar</strong>
            <span>Processamento em segundo plano</span>
          </div>
        </li>
      </ol>

      <div class="imp-acoes row">
        <div class="col-lg-5 mb-3 mb-lg-0">
          <section class="imp-card card h-100">
            <div class="card-body imp-card-body">
              <div class="imp-card-topo mb-3">
                <span class="imp-card-icone" aria-hidden="true">
                  <i class="fas fa-download"></i>
                </span>
                <div>
                  <h2 class="imp-card-titulo">1. Baixar modelo</h2>
                  <p class="imp-card-desc mb-0">
                    Excel com dropdowns da sua empresa. Preencha a aba Dados.
                  </p>
                </div>
              </div>

              <div class="imp-card-conteudo">
                <div class="imp-card-ajuda">
                  <span class="imp-card-ajuda-item">
                    <i class="fas fa-list" aria-hidden="true"></i>
                    Dropdowns de colaborador e treinamento
                  </span>
                  <span class="imp-card-ajuda-item">
                    <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                    Datas em <code>dd/mm/aaaa</code>
                  </span>
                </div>
              </div>

              <div class="imp-enviar">
                <a
                  :href="urlModelo"
                  class="btn btn-primary imp-btn-acao"
                  :class="{ disabled: baixandoModelo }"
                  role="button"
                  @click="onBaixarModelo"
                >
                  <i class="fas fa-file-excel" aria-hidden="true"></i>
                  <span>{{ baixandoModelo ? 'Baixando…' : 'Baixar modelo Excel' }}</span>
                </a>
              </div>
            </div>
          </section>
        </div>

        <div class="col-lg-7">
          <section class="imp-card card h-100">
            <div class="card-body imp-card-body">
              <div class="imp-card-topo mb-3">
                <span class="imp-card-icone imp-card-icone-destaque" aria-hidden="true">
                  <i class="fas fa-cloud-upload-alt"></i>
                </span>
                <div>
                  <h2 class="imp-card-titulo">2. Enviar planilha</h2>
                  <p class="imp-card-desc mb-0">
                    Arquivo .xlsx preenchido. Resultado na aba Importados, no sino e por e-mail.
                  </p>
                </div>
              </div>

              <form class="imp-card-form" @submit.prevent="enviarPlanilha">
                <div class="imp-card-conteudo">
                  <div
                    class="upload-dropzone"
                    :class="{
                      'upload-dropzone-arrasto': arrastando,
                      'upload-dropzone-desabilitada': enviando,
                      'upload-dropzone-ativa': !!arquivoSelecionado
                    }"
                    @dragenter.prevent="aoArrastarEntrar"
                    @dragover.prevent="aoArrastarSobre"
                    @dragleave.prevent="aoArrastarSair"
                    @drop.prevent="aoSoltar"
                  >
                    <div
                      class="upload-dropzone-info"
                      role="button"
                      tabindex="0"
                      :aria-disabled="enviando"
                      @click="abrirSeletor"
                      @keydown.enter.prevent="abrirSeletor"
                      @keydown.space.prevent="abrirSeletor"
                    >
                      <div class="upload-dropzone-icone">
                        <i class="fas fa-file-excel" aria-hidden="true"></i>
                      </div>
                      <div class="upload-dropzone-corpo">
                        <strong class="upload-dropzone-titulo">
                          {{ arquivoSelecionado ? arquivoSelecionado.name : 'Selecione ou arraste a planilha' }}
                        </strong>
                        <span class="upload-dropzone-ajuda">
                          {{
                            arquivoSelecionado
                              ? tamanhoArquivoLabel
                              : 'Excel (.xlsx) · até 20 MB'
                          }}
                        </span>
                      </div>
                    </div>
                    <div class="upload-dropzone-acoes">
                      <button
                        type="button"
                        class="btn-filter btn-filter-neutro"
                        :disabled="enviando"
                        @click.stop="abrirSeletor"
                      >
                        <i class="fas fa-folder-open" aria-hidden="true"></i>
                        <span>{{ arquivoSelecionado ? 'Trocar' : 'Escolher' }}</span>
                      </button>
                      <button
                        v-if="arquivoSelecionado && !enviando"
                        type="button"
                        class="btn-filter btn-filter-neutro"
                        title="Remover arquivo"
                        @click.stop="limparArquivo"
                      >
                        <i class="fas fa-times" aria-hidden="true"></i>
                        <span>Remover</span>
                      </button>
                    </div>
                  </div>
                  <input
                    id="planilha"
                    ref="inputPlanilha"
                    type="file"
                    accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    :disabled="enviando"
                    class="d-none"
                    @change="onArquivoSelecionado"
                  />

                  <div
                    v-if="mensagem"
                    class="imp-feedback mt-3"
                    :class="mensagemTipo === 'erro' ? 'imp-feedback-erro' : 'imp-feedback-ok'"
                    role="alert"
                  >
                    <div class="imp-feedback-corpo">
                      <i
                        class="fas"
                        :class="mensagemTipo === 'erro' ? 'fa-exclamation-circle' : 'fa-check-circle'"
                        aria-hidden="true"
                      ></i>
                      <span>{{ mensagem }}</span>
                    </div>
                    <button
                      v-if="mensagemTipo === 'sucesso'"
                      type="button"
                      class="btn btn-sm btn-success"
                      @click="abrirImportados"
                    >
                      Ver Importados
                    </button>
                  </div>
                </div>

                <div class="imp-enviar">
                  <button
                    type="submit"
                    class="btn btn-primary imp-btn-acao"
                    :disabled="!arquivoSelecionado || enviando"
                  >
                    <i
                      class="fas"
                      :class="enviando ? 'fa-spinner fa-spin' : 'fa-upload'"
                      aria-hidden="true"
                    ></i>
                    <span>{{ enviando ? 'Enviando…' : 'Enviar para importação' }}</span>
                  </button>
                </div>
              </form>
            </div>
          </section>
        </div>
      </div>
    </div>

    <div v-show="aba === 'importados'">
      <importacao-treinamentos-historico
        ref="historico"
        :url-historico="urlHistorico"
        :url-csv-base="urlCsvBase"
      />
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import ImportacaoTreinamentosHistorico from './ImportacaoTreinamentosHistorico.vue'

export default {
  name: 'ImportacaoTreinamentos',
  components: {
    ImportacaoTreinamentosHistorico
  },
  props: {
    urlUpload: {
      type: String,
      required: true
    },
    urlModelo: {
      type: String,
      required: true
    },
    urlHistorico: {
      type: String,
      required: true
    },
    urlCsvBase: {
      type: String,
      required: true
    },
    empresaId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      aba: 'importacao',
      arquivoSelecionado: null,
      enviando: false,
      baixandoModelo: false,
      modeloBaixado: false,
      arrastando: false,
      dragCounter: 0,
      mensagem: '',
      mensagemTipo: 'sucesso'
    }
  },
  computed: {
    tamanhoArquivoLabel() {
      if (!this.arquivoSelecionado) return ''
      const bytes = this.arquivoSelecionado.size || 0
      if (bytes < 1024) return `${bytes} B`
      if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
      return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
    }
  },
  methods: {
    abrirImportados() {
      this.aba = 'importados'
      this.$nextTick(() => {
        this.$refs.historico?.carregar?.()
      })
    },
    onBaixarModelo() {
      this.baixandoModelo = true
      this.modeloBaixado = true
      setTimeout(() => {
        this.baixandoModelo = false
      }, 1500)
    },
    abrirSeletor() {
      if (this.enviando) return
      this.$refs.inputPlanilha?.click()
    },
    aoArrastarEntrar() {
      if (this.enviando) return
      this.dragCounter += 1
      this.arrastando = true
    },
    aoArrastarSobre() {
      if (this.enviando) return
      this.arrastando = true
    },
    aoArrastarSair() {
      this.dragCounter = Math.max(0, this.dragCounter - 1)
      if (this.dragCounter === 0) {
        this.arrastando = false
      }
    },
    aoSoltar(event) {
      this.dragCounter = 0
      this.arrastando = false
      if (this.enviando) return
      const file = event.dataTransfer?.files?.[0]
      this.definirArquivo(file)
    },
    onArquivoSelecionado(event) {
      const file = event.target.files?.[0]
      this.definirArquivo(file)
    },
    definirArquivo(file) {
      this.mensagem = ''
      if (!file) {
        this.arquivoSelecionado = null
        return
      }
      const nome = (file.name || '').toLowerCase()
      if (!nome.endsWith('.xlsx')) {
        this.arquivoSelecionado = null
        this.mensagem = 'Selecione um arquivo Excel no formato .xlsx.'
        this.mensagemTipo = 'erro'
        if (this.$refs.inputPlanilha) {
          this.$refs.inputPlanilha.value = ''
        }
        return
      }
      this.arquivoSelecionado = file
    },
    limparArquivo() {
      this.arquivoSelecionado = null
      if (this.$refs.inputPlanilha) {
        this.$refs.inputPlanilha.value = ''
      }
    },
    async enviarPlanilha() {
      if (!this.arquivoSelecionado || this.enviando) return
      this.enviando = true
      this.mensagem = ''
      const formData = new FormData()
      formData.append('planilha', this.arquivoSelecionado)
      formData.append('empresa_id', this.empresaId)
      try {
        const { data } = await axios.post(this.urlUpload, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        this.mensagem =
          data.msg ||
          'Importação enfileirada. Ao concluir, o resultado fica na aba Importados, no sino e por e-mail.'
        this.mensagemTipo = data.status === 'error' ? 'erro' : 'sucesso'
        this.limparArquivo()
      } catch (err) {
        const msg = err.response?.data?.message || err.response?.data?.msg || err.message || 'Erro ao enviar a planilha.'
        this.mensagem = typeof msg === 'object' ? JSON.stringify(msg) : msg
        this.mensagemTipo = 'erro'
      } finally {
        this.enviando = false
      }
    }
  }
}
</script>

<style scoped>
.importacao-treinamentos {
  --imp-primary: #174257;
  --imp-primary-soft: rgba(23, 66, 87, 0.08);
  --imp-border: #e3e8ec;
  --imp-muted: #6c757d;
  --imp-radius: 10px;
}

.imp-tabs {
  border-bottom: 2px solid var(--imp-primary);
  background: #f8fafb;
  border-radius: 8px 8px 0 0;
  padding: 0.25rem 0.35rem 0;
}

.imp-tabs .nav-link {
  color: var(--imp-muted);
  font-weight: 600;
  border: none;
  border-radius: 6px 6px 0 0;
  padding: 0.65rem 1rem;
}

.imp-tabs .nav-link:hover {
  color: var(--imp-primary);
  background: var(--imp-primary-soft);
}

.imp-tabs .nav-link.active {
  color: #fff;
  background: var(--imp-primary);
}

.imp-intro {
  margin-bottom: 0.85rem;
}

.imp-intro-texto {
  color: #495057;
  font-size: 0.95rem;
  line-height: 1.45;
  max-width: 46rem;
}

.imp-dicas {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.imp-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.65rem;
  border-radius: 999px;
  background: var(--imp-primary-soft);
  color: var(--imp-primary);
  font-size: 0.78rem;
  font-weight: 600;
}

.imp-chip code {
  color: inherit;
  background: rgba(255, 255, 255, 0.65);
  padding: 0.05rem 0.3rem;
  border-radius: 4px;
  font-size: 0.75rem;
}

.imp-manual {
  border: 1px solid var(--imp-border);
  border-radius: var(--imp-radius);
  background: #fff;
}

.imp-manual .card-header {
  background: #f8fafb;
  border-bottom: 1px solid var(--imp-border);
  padding: 0.75rem 1rem;
}

.imp-manual-titulo {
  font-size: 1rem;
  font-weight: 700;
  color: #212529;
}

.imp-manual-lista {
  color: #343a40;
  font-size: 0.9rem;
  line-height: 1.45;
}

.imp-manual-lista li + li {
  margin-top: 0.45rem;
}

.imp-manual-nota {
  font-size: 0.8rem;
  color: var(--imp-muted);
  background: #f8f9fa;
  border: 1px solid var(--imp-border);
  border-radius: 8px;
  padding: 0.65rem 0.85rem;
}

.imp-passos {
  list-style: none;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.65rem;
  padding: 0;
  margin: 0 0 1rem;
}

.imp-passo {
  display: flex;
  align-items: flex-start;
  gap: 0.65rem;
  padding: 0.75rem 0.85rem;
  border: 1px solid var(--imp-border);
  border-radius: var(--imp-radius);
  background: #fff;
  min-height: 4.25rem;
}

.imp-passo strong {
  display: block;
  font-size: 0.85rem;
  color: #212529;
}

.imp-passo span:last-child {
  display: block;
  font-size: 0.75rem;
  color: var(--imp-muted);
  margin-top: 0.15rem;
  line-height: 1.3;
}

.imp-passo-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.65rem;
  height: 1.65rem;
  border-radius: 50%;
  background: var(--imp-primary-soft);
  color: var(--imp-primary);
  font-size: 0.8rem;
  font-weight: 700;
  flex-shrink: 0;
}

.imp-passo-feito {
  border-color: rgba(23, 66, 87, 0.35);
  background: linear-gradient(180deg, rgba(23, 66, 87, 0.04), #fff);
}

.imp-passo-feito .imp-passo-num {
  background: var(--imp-primary);
  color: #fff;
}

.imp-acoes.row {
  align-items: stretch;
}

.imp-acoes .col-lg-5,
.imp-acoes .col-lg-7 {
  display: flex;
}

.imp-card {
  display: flex;
  flex-direction: column;
  width: 100%;
  border: 1px solid var(--imp-border);
  border-radius: var(--imp-radius);
  box-shadow: 0 1px 2px rgba(23, 66, 87, 0.04);
}

.imp-card-body {
  display: flex;
  flex-direction: column;
  flex: 1 1 auto;
  min-height: 0;
}

.imp-card-form {
  display: flex;
  flex-direction: column;
  flex: 1 1 auto;
  min-height: 0;
  margin: 0;
}

.imp-card-conteudo {
  flex: 1 1 auto;
  min-height: 0;
}

.imp-card-ajuda {
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  gap: 0.5rem;
  min-height: 4.5rem;
  padding: 0.65rem;
  border: 1px dashed #ced4da;
  border-radius: 8px;
  background: #fff;
}

.imp-card-ajuda-item {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  flex: 1 1 10rem;
  min-width: 0;
  padding: 0.55rem 0.7rem;
  border-radius: 6px;
  background: var(--imp-primary-soft);
  color: var(--imp-primary);
  font-size: 0.78rem;
  font-weight: 600;
  line-height: 1.3;
  word-break: break-word;
  overflow-wrap: anywhere;
}

.imp-card-ajuda-item i {
  flex-shrink: 0;
  font-size: 0.75rem;
}

.imp-card-ajuda-item code {
  white-space: nowrap;
  background: rgba(255, 255, 255, 0.75);
  padding: 0.05rem 0.3rem;
  border-radius: 4px;
  font-size: 0.74rem;
  color: inherit;
}

.imp-card-topo {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.imp-card-icone {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 8px;
  background: var(--imp-primary-soft);
  color: var(--imp-primary);
  flex-shrink: 0;
}

.imp-card-icone-destaque {
  background: var(--imp-primary);
  color: #fff;
}

.imp-card-titulo {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #212529;
}

.imp-card-desc {
  margin-top: 0.25rem;
  font-size: 0.82rem;
  color: var(--imp-muted);
  line-height: 1.4;
}

.upload-dropzone {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  min-height: 4.5rem;
  padding: 0.85rem 1rem;
  text-align: left;
  background: #fff;
  border: 1px dashed #ced4da;
  border-radius: 8px;
  transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.upload-dropzone:hover:not(.upload-dropzone-desabilitada) {
  border-color: var(--imp-primary);
  background: var(--imp-primary-soft);
}

.upload-dropzone-ativa {
  border-style: solid;
  border-color: rgba(23, 66, 87, 0.35);
  background: var(--imp-primary-soft);
}

.upload-dropzone-arrasto {
  border-style: solid;
  border-color: var(--imp-primary);
  background: rgba(23, 66, 87, 0.12);
  box-shadow: 0 0 0 3px rgba(23, 66, 87, 0.12);
}

.upload-dropzone-desabilitada {
  opacity: 0.7;
  cursor: not-allowed;
}

.upload-dropzone-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
  flex: 1;
  cursor: pointer;
}

.upload-dropzone-info:focus {
  outline: 2px solid var(--imp-primary);
  outline-offset: 2px;
  border-radius: 6px;
}

.upload-dropzone-icone {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  flex-shrink: 0;
  border-radius: 8px;
  background: rgba(40, 167, 69, 0.1);
  color: #218838;
  font-size: 1.05rem;
}

.upload-dropzone-corpo {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  min-width: 0;
}

.upload-dropzone-titulo {
  font-size: 0.875rem;
  font-weight: 600;
  color: #212529;
  line-height: 1.25;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  width: 100%;
}

.upload-dropzone-ajuda {
  font-size: 0.75rem;
  color: var(--imp-muted);
  line-height: 1.25;
}

.upload-dropzone-acoes {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.btn-filter {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  min-height: 2rem;
  padding: 0.35rem 0.7rem;
  border-radius: 6px;
  border: 1px solid transparent;
  font-size: 0.8rem;
  font-weight: 600;
  line-height: 1.2;
  cursor: pointer;
  background: #fff;
}

.btn-filter:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-filter-neutro {
  color: var(--imp-primary);
  border-color: rgba(23, 66, 87, 0.25);
  background: #fff;
}

.btn-filter-neutro:hover:not(:disabled) {
  background: var(--imp-primary-soft);
  border-color: var(--imp-primary);
}

.imp-feedback {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.75rem 0.9rem;
  border-radius: 8px;
  font-size: 0.875rem;
}

.imp-feedback-corpo {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  min-width: 0;
}

.imp-feedback-ok {
  background: #eaf7ee;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.imp-feedback-erro {
  background: #fdecea;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.imp-enviar {
  margin-top: auto;
  padding-top: 1rem;
  flex-shrink: 0;
}

.imp-btn-acao {
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  width: 14rem;
  min-width: 14rem;
  min-height: 2.375rem;
  height: 2.375rem;
  padding: 0.375rem 0.9rem;
  font-size: 0.875rem;
  font-weight: 600;
  line-height: 1.2;
  border-radius: 0.25rem;
  box-sizing: border-box;
}

a.imp-btn-acao {
  text-decoration: none;
}

a.imp-btn-acao.disabled {
  pointer-events: none;
  opacity: 0.65;
}

@media (max-width: 991px) {
  .imp-passos {
    grid-template-columns: 1fr;
  }

}

@media (max-width: 768px) {
  .upload-dropzone {
    flex-direction: column;
    align-items: stretch;
  }

  .imp-feedback {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
