<template>
  <div class="importacao-admissoes card">
    <div class="card-header">
      <h5 class="mb-0">Enviar planilha</h5>
    </div>
    <div class="card-body">
      <p class="text-muted mb-3">
        Selecione um arquivo Excel (.xlsx) no formato da aba "Dados" conforme o guia de importação.
        O processamento é feito em background; você será notificado ao concluir.
      </p>
      <form @submit.prevent="enviarPlanilha" class="mb-3">
        <div class="form-group">
          <label for="planilha">Planilha</label>
          <input
            id="planilha"
            ref="inputPlanilha"
            type="file"
            class="form-control-file"
            accept=".xlsx"
            :disabled="enviando"
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
          <span v-else>Enviar para importação</span>
        </button>
      </form>
      <p class="small text-muted mb-0">
        Tamanho máximo recomendado: 20 MB. Após o envio, a importação será processada em segundo plano.
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ImportacaoAdmissoes',
  props: {
    urlUpload: {
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
      arquivoSelecionado: null,
      enviando: false,
      mensagem: '',
      mensagemTipo: 'sucesso'
    }
  },
  methods: {
    onArquivoSelecionado(event) {
      const file = event.target.files?.[0]
      this.arquivoSelecionado = file || null
      this.mensagem = ''
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
        this.mensagem = data.msg || 'Importação enfileirada. Você será notificado quando o processamento terminar.'
        this.mensagemTipo = data.status === 'error' ? 'erro' : 'sucesso'
        this.arquivoSelecionado = null
        if (this.$refs.inputPlanilha) {
          this.$refs.inputPlanilha.value = ''
        }
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
