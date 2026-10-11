<template>
  <div class="importacao-treinamentos-historico">
    <p class="text-muted mb-3">
      Histórico das importações processadas. Baixe o CSV de sucesso e o de erro de cada execução.
    </p>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="small text-muted" v-if="!carregando">{{ itens.length }} registro(s)</span>
      <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="carregando" @click="carregar">
        <i class="fa fa-sync" :class="{ 'fa-spin': carregando }"></i> Atualizar
      </button>
    </div>

    <div v-if="erro" class="alert alert-danger" role="alert">{{ erro }}</div>

    <div v-if="carregando && itens.length === 0" class="text-muted py-4 text-center">
      <i class="fa fa-spinner fa-spin"></i> Carregando...
    </div>

    <div v-else-if="itens.length === 0" class="alert alert-light border mb-0">
      Nenhuma importação registrada ainda.
    </div>

    <div v-else class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead>
          <tr>
            <th>Data</th>
            <th>Status</th>
            <th class="text-center">Total</th>
            <th class="text-center">Importados</th>
            <th class="text-center">Erros</th>
            <th class="text-right">CSVs</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in itens" :key="item.id">
            <td>{{ item.created_at_br }}</td>
            <td>
              <span :class="badgeStatus(item.status)">{{ labelStatus(item.status) }}</span>
              <div v-if="item.status === 'falha' && item.mensagem_erro" class="small text-danger mt-1">
                {{ item.mensagem_erro }}
              </div>
            </td>
            <td class="text-center">{{ item.total }}</td>
            <td class="text-center">
              <strong class="text-success">{{ item.sucesso }}</strong>
            </td>
            <td class="text-center">
              <strong :class="item.erros > 0 ? 'text-danger' : 'text-muted'">{{ item.erros }}</strong>
            </td>
            <td class="text-right text-nowrap">
              <a
                v-if="item.tem_csv_sucesso"
                :href="urlCsv(item.id, 'sucesso')"
                class="btn btn-sm btn-outline-success mr-1"
                target="_blank"
              >
                <i class="fa fa-download"></i> Sucesso
              </a>
              <a
                v-if="item.tem_csv_erro"
                :href="urlCsv(item.id, 'erro')"
                class="btn btn-sm btn-outline-danger"
                target="_blank"
              >
                <i class="fa fa-download"></i> Erro
              </a>
              <span v-if="!item.tem_csv_sucesso && !item.tem_csv_erro && item.status === 'processando'" class="small text-muted">
                Aguardando...
              </span>
              <span v-else-if="!item.tem_csv_sucesso && !item.tem_csv_erro && item.status !== 'processando'" class="small text-muted">
                —
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ImportacaoTreinamentosHistorico',
  props: {
    urlHistorico: {
      type: String,
      required: true
    },
    urlCsvBase: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      itens: [],
      carregando: false,
      erro: ''
    }
  },
  mounted() {
    this.carregar()
  },
  methods: {
    async carregar() {
      this.carregando = true
      this.erro = ''
      try {
        const { data } = await axios.get(this.urlHistorico)
        this.itens = data.data || []
      } catch (err) {
        this.erro = err.response?.data?.msg || err.message || 'Erro ao carregar o histórico.'
      } finally {
        this.carregando = false
      }
    },
    urlCsv(id, tipo) {
      return this.urlCsvBase.replace('__ID__', id).replace('__TIPO__', tipo)
    },
    labelStatus(status) {
      if (status === 'concluida') return 'Concluída'
      if (status === 'falha') return 'Falha'
      if (status === 'processando') return 'Processando'
      return status
    },
    badgeStatus(status) {
      if (status === 'concluida') return 'badge badge-success'
      if (status === 'falha') return 'badge badge-danger'
      return 'badge badge-secondary'
    }
  }
}
</script>
