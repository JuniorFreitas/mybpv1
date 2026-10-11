<template>
  <div class="imp-hist">
    <div class="imp-hist-topo">
      <div>
        <h2 class="imp-hist-titulo">Histórico de importações</h2>
        <p class="imp-hist-desc mb-0">
          Baixe o CSV de sucesso e o de erro de cada execução.
        </p>
      </div>
      <button
        type="button"
        class="btn btn-sm btn-outline-secondary"
        :disabled="carregando"
        @click="carregar"
      >
        <i class="fa fa-sync" :class="{ 'fa-spin': carregando }" aria-hidden="true"></i>
        Atualizar
      </button>
    </div>

    <div v-if="erro" class="alert alert-danger" role="alert">{{ erro }}</div>

    <div v-if="carregando && itens.length === 0" class="imp-hist-vazio" aria-live="polite">
      <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
      Carregando histórico…
    </div>

    <div v-else-if="itens.length === 0" class="imp-hist-vazio">
      <div class="imp-hist-vazio-icone" aria-hidden="true">
        <i class="fas fa-inbox"></i>
      </div>
      <strong>Nenhuma importação ainda</strong>
      <span>Envie uma planilha na aba Importação para ver o resultado aqui.</span>
    </div>

    <div v-else class="imp-hist-lista">
      <article
        v-for="item in itens"
        :key="item.id"
        class="imp-hist-card"
        :class="'imp-hist-card-' + item.status"
      >
        <div class="imp-hist-card-main">
          <div class="imp-hist-card-meta">
            <span :class="badgeStatus(item.status)">{{ labelStatus(item.status) }}</span>
            <time class="imp-hist-data">{{ item.created_at_br }}</time>
          </div>
          <div class="imp-hist-metricas">
            <div class="imp-hist-metrica">
              <span class="imp-hist-metrica-label">Total</span>
              <strong>{{ item.total }}</strong>
            </div>
            <div class="imp-hist-metrica">
              <span class="imp-hist-metrica-label">Importados</span>
              <strong class="text-success">{{ item.sucesso }}</strong>
            </div>
            <div class="imp-hist-metrica">
              <span class="imp-hist-metrica-label">Erros</span>
              <strong :class="item.erros > 0 ? 'text-danger' : 'text-muted'">{{ item.erros }}</strong>
            </div>
          </div>
          <p v-if="item.status === 'falha' && mensagemErroAmigavel(item)" class="imp-hist-erro mb-0">
            {{ mensagemErroAmigavel(item) }}
          </p>
        </div>
        <div class="imp-hist-card-acoes">
          <a
            v-if="item.tem_csv_sucesso"
            :href="urlCsv(item.id, 'sucesso')"
            class="btn btn-sm btn-outline-success"
            target="_blank"
            rel="noopener"
          >
            <i class="fa fa-download" aria-hidden="true"></i> Sucesso
          </a>
          <a
            v-if="item.tem_csv_erro"
            :href="urlCsv(item.id, 'erro')"
            class="btn btn-sm btn-outline-danger"
            target="_blank"
            rel="noopener"
          >
            <i class="fa fa-download" aria-hidden="true"></i> Erro
          </a>
          <span
            v-if="!item.tem_csv_sucesso && !item.tem_csv_erro && item.status === 'processando'"
            class="imp-hist-aguardando"
          >
            <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
            Processando…
          </span>
          <span
            v-else-if="!item.tem_csv_sucesso && !item.tem_csv_erro && item.status !== 'processando'"
            class="text-muted small"
          >
            Sem CSV
          </span>
        </div>
      </article>
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
    },
    mensagemErroAmigavel(item) {
      const raw = String(item?.mensagem_erro || '').trim()
      if (!raw) return ''
      // Não expor SQL/stack técnico na UI
      if (/SQLSTATE|SQL:|Connection:|stack trace|Exception/i.test(raw)) {
        return 'Falha no processamento. Tente novamente ou baixe o CSV de erro, se disponível.'
      }
      return raw.length > 180 ? `${raw.slice(0, 177)}…` : raw
    }
  }
}
</script>

<style scoped>
.imp-hist {
  --imp-primary: #174257;
  --imp-border: #e3e8ec;
  --imp-muted: #6c757d;
}

.imp-hist-topo {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.imp-hist-titulo {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #212529;
}

.imp-hist-desc {
  margin-top: 0.2rem;
  font-size: 0.85rem;
  color: var(--imp-muted);
}

.imp-hist-vazio {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  text-align: center;
  padding: 2.5rem 1rem;
  border: 1px dashed var(--imp-border);
  border-radius: 10px;
  background: #fafbfc;
  color: var(--imp-muted);
}

.imp-hist-vazio-icone {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  background: rgba(23, 66, 87, 0.08);
  color: var(--imp-primary);
  font-size: 1.15rem;
  margin-bottom: 0.35rem;
}

.imp-hist-vazio strong {
  color: #343a40;
  font-size: 0.95rem;
}

.imp-hist-vazio span {
  font-size: 0.82rem;
  max-width: 22rem;
}

.imp-hist-lista {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.imp-hist-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.9rem 1rem;
  border: 1px solid var(--imp-border);
  border-radius: 10px;
  background: #fff;
  border-left: 4px solid #adb5bd;
}

.imp-hist-card-concluida {
  border-left-color: #28a745;
}

.imp-hist-card-falha {
  border-left-color: #dc3545;
}

.imp-hist-card-processando {
  border-left-color: #174257;
}

.imp-hist-card-main {
  min-width: 0;
  flex: 1;
}

.imp-hist-card-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem 0.75rem;
  margin-bottom: 0.55rem;
}

.imp-hist-data {
  font-size: 0.82rem;
  color: var(--imp-muted);
}

.imp-hist-metricas {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem 1.5rem;
}

.imp-hist-metrica {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.imp-hist-metrica-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--imp-muted);
  font-weight: 600;
}

.imp-hist-metrica strong {
  font-size: 1rem;
  line-height: 1.2;
}

.imp-hist-erro {
  margin-top: 0.55rem;
  font-size: 0.8rem;
  color: #721c24;
}

.imp-hist-card-acoes {
  display: inline-flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.45rem;
  flex-shrink: 0;
}

.imp-hist-aguardando {
  font-size: 0.8rem;
  color: var(--imp-muted);
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

@media (max-width: 768px) {
  .imp-hist-topo {
    flex-direction: column;
  }

  .imp-hist-card {
    flex-direction: column;
    align-items: stretch;
  }

  .imp-hist-card-acoes {
    justify-content: flex-start;
  }
}
</style>
