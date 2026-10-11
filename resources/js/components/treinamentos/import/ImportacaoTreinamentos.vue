<template>
  <div class="importacao-treinamentos">
    <ul class="nav nav-tabs bg-light mb-3" role="tablist" style="border-bottom: 1px solid #653232">
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: aba === 'importacao' }"
          href="#"
          role="tab"
          @click.prevent="aba = 'importacao'"
        >
          Importação
        </a>
      </li>
      <li class="nav-item">
        <a
          class="nav-link"
          :class="{ active: aba === 'importados' }"
          href="#"
          role="tab"
          @click.prevent="abrirImportados"
        >
          Importados
        </a>
      </li>
    </ul>

    <div v-show="aba === 'importacao'">
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Manual de importação</h5>
          <button type="button" class="btn btn-sm btn-outline-secondary" @click="manualAberto = !manualAberto">
            {{ manualAberto ? 'Ocultar' : 'Ver manual' }}
          </button>
        </div>
        <div v-show="manualAberto" class="card-body">
          <ol class="mb-3 pl-3">
            <li class="mb-2">Baixe o modelo Excel e preencha a aba <strong>Dados</strong> (não altere os cabeçalhos).</li>
            <li class="mb-2">
              Em <strong>colaborador</strong> e <strong>treinamento</strong>, use os dropdowns da planilha.
            </li>
            <li class="mb-2">
              Em <strong>data_treinamento</strong>, preencha no formato
              <code>dd/mm/aaaa</code> — exemplo: <code>15/03/2026</code>.
            </li>
            <li class="mb-2">
              Em <strong>data_vencimento</strong> (opcional): deixe vazio para calcular com o prazo do treinamento
             , ou preencha em <code>dd/mm/aaaa</code>.
            </li>
            <li class="mb-2">
              Criar e atualizar seguem o <strong>mesmo fluxo da carteira/etiquetas</strong>:
              cria se não existir; se existir, só altera aquele vencimento e registra o histórico.
            </li>
            <li class="mb-2">Envie o arquivo .xlsx nesta tela e acompanhe o resultado na aba <strong>Importados</strong>.</li>
          </ol>
          <div class="alert alert-light border mb-0">
            <strong>Datas:</strong> <code>dd/mm/aaaa</code> — <code>data_treinamento</code> obrigatória;
            <code>data_vencimento</code> opcional.<br>
            <span class="text-muted">
              Exemplos válidos: <code>01/05/2026</code>, <code>15/03/2026</code>.
              Evite <code>15-03-2026</code>, <code>15.03.2026</code>, <code>3/15/2026</code> ou ano com 2 dígitos.
            </span>
            <div class="small text-muted mt-2 mb-0">
              Processamento em lote (chunks). O modelo também traz a aba <strong>Instrucoes</strong>.
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          <h5 class="mb-0">Baixar modelo</h5>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            Baixe a planilha Excel com as listas de colaboradores e treinamentos da sua empresa.
            Use os dropdowns das colunas <strong>colaborador</strong> e <strong>treinamento</strong>.
            Em <strong>data_treinamento</strong>, use <code>dd/mm/aaaa</code>.
            Em <strong>data_vencimento</strong> (opcional), deixe vazio para o prazo padrão da carteira.
          </p>
          <a :href="urlModelo" class="btn btn-outline-primary" :class="{ disabled: baixandoModelo }" @click="onBaixarModelo">
            <i class="fa fa-download"></i> Baixar modelo Excel
          </a>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Enviar planilha</h5>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            Envie o arquivo Excel (.xlsx) preenchido conforme o modelo e o manual.
            Ao concluir, o resultado fica na aba <strong>Importados</strong>
            (CSV de sucesso e de erro), no sino e por e-mail.
          </p>
          <form @submit.prevent="enviarPlanilha" class="mb-3">
            <div class="form-group mb-3">
              <label class="mb-2">Planilha</label>
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
                <div class="upload-dropzone-info" @click="abrirSeletor">
                  <div class="upload-dropzone-icone">
                    <i class="fas fa-file-excel"></i>
                  </div>
                  <div class="upload-dropzone-corpo">
                    <strong class="upload-dropzone-titulo">
                      {{ arquivoSelecionado ? arquivoSelecionado.name : 'Selecione ou arraste a planilha' }}
                    </strong>
                    <span class="upload-dropzone-ajuda">
                      {{
                        arquivoSelecionado
                          ? tamanhoArquivoLabel
                          : 'Arquivo Excel (.xlsx), até 20 MB'
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
                    <i class="fas fa-folder-open"></i>
                    <span>{{ arquivoSelecionado ? 'Trocar' : 'Escolher' }}</span>
                  </button>
                  <button
                    v-if="arquivoSelecionado && !enviando"
                    type="button"
                    class="btn-filter btn-filter-neutro"
                    title="Remover arquivo"
                    @click.stop="limparArquivo"
                  >
                    <i class="fas fa-times"></i>
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
            </div>
            <div v-if="mensagem" :class="['alert', mensagemTipo === 'erro' ? 'alert-danger' : 'alert-success']" role="alert">
              {{ mensagem }}
            </div>
            <button type="submit" class="btn btn-primary" :disabled="!arquivoSelecionado || enviando">
              <span v-if="enviando">
                <i class="fa fa-spinner fa-spin"></i> Enviando...
              </span>
              <span v-else>
                <i class="fas fa-upload"></i> Enviar para importação
              </span>
            </button>
          </form>
          <p class="small text-muted mb-0">
            Após o envio, acompanhe o andamento na aba Importados.
          </p>
        </div>
      </div>
    </div>

    <div v-show="aba === 'importados'" class="card">
      <div class="card-header">
        <h5 class="mb-0">Importados</h5>
      </div>
      <div class="card-body">
        <importacao-treinamentos-historico
          ref="historico"
          :url-historico="urlHistorico"
          :url-csv-base="urlCsvBase"
        />
      </div>
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
      manualAberto: true,
      arquivoSelecionado: null,
      enviando: false,
      baixandoModelo: false,
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
          'Importação enfileirada. Ao concluir, o resultado fica na aba Importados (CSV de sucesso e erro), no sino e por e-mail.'
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
.upload-dropzone {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  min-height: 4.25rem;
  padding: 0.75rem 0.875rem;
  text-align: left;
  background: #ffffff;
  border: 1px dashed #ced4da;
  border-radius: 8px;
  transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.upload-dropzone:hover:not(.upload-dropzone-desabilitada) {
  border-color: #174257;
  background: rgba(23, 66, 87, 0.04);
}

.upload-dropzone-ativa {
  border-style: solid;
  border-color: rgba(23, 66, 87, 0.28);
  background: rgba(23, 66, 87, 0.04);
}

.upload-dropzone-arrasto {
  border-style: solid;
  border-color: #174257;
  background: rgba(23, 66, 87, 0.1);
  box-shadow: 0 0 0 3px rgba(23, 66, 87, 0.12);
}

.upload-dropzone-desabilitada {
  opacity: 0.7;
  cursor: not-allowed;
}

.upload-dropzone-info {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 0.75rem;
  min-width: 0;
  flex: 1;
  cursor: pointer;
  text-align: left;
}

.upload-dropzone-icone {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  flex-shrink: 0;
  border-radius: 8px;
  background: rgba(23, 66, 87, 0.08);
  color: #174257;
  font-size: 1rem;
}

.upload-dropzone-corpo {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  gap: 0.15rem;
  min-width: 0;
  text-align: left;
}

.upload-dropzone-titulo {
  font-size: 0.875rem;
  font-weight: 600;
  color: #212529;
  line-height: 1.25;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-align: left;
  width: 100%;
}

.upload-dropzone-ajuda {
  font-size: 0.75rem;
  color: #6c757d;
  line-height: 1.25;
  text-align: left;
  width: 100%;
}

.upload-dropzone-acoes {
  display: inline-flex;
  align-items: center;
  justify-content: flex-start;
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
  color: #174257;
  border-color: rgba(23, 66, 87, 0.25);
  background: #fff;
}

.btn-filter-neutro:hover:not(:disabled) {
  background: rgba(23, 66, 87, 0.06);
  border-color: #174257;
}

@media (max-width: 768px) {
  .upload-dropzone {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }

  .upload-dropzone-acoes {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>
