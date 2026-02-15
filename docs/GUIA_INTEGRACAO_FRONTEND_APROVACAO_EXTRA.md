# 🎨 Guia de Integração Frontend - Aprovação Extra

## 📋 Visão Geral

Este guia mostra como integrar o sistema de aprovação extra no frontend Vue.js dos componentes de Requisição de Vaga e Valor Extra.

---

## 🔍 1. Identificar Componentes Vue Existentes

### Arquivos Prováveis:

```
resources/js/components/
├── planejamento/
│   └── requisicao-vaga/
│       └── Index.vue  (ou similar)
└── movimentacao/
    └── valor-extra/
        └── Index.vue  (ou similar)
```

---

## 📝 2. Modificações Necessárias

### 2.1 Adicionar Variáveis no `data()`

```javascript
data() {
    return {
        dados: [],

        // Adicionar estas variáveis
        podeAprovarExtra: false,
        temAprovacaoExtra: false,
        nomeAprovacaoExtra: '',

        // Modal
        itemSelecionado: null,
        formAprovacaoExtra: {
            id: null,
            status_aprovacao_extra: '',
            obs_aprovacao_extra: ''
        }
    }
}
```

### 2.2 Atualizar Método `atualizar()`

```javascript
methods: {
    atualizar() {
        axios.post('/planejamento/requisicao-vaga/atualizar', {
            pages: this.pagina,
            // ... outros filtros
        }).then(response => {
            this.dados = response.data.dados.itens

            // ✨ ADICIONAR ESTAS LINHAS
            this.podeAprovarExtra = response.data.dados.pode_aprovar_extra || false
            this.temAprovacaoExtra = response.data.dados.tem_aprovacao_extra || false
            this.nomeAprovacaoExtra = response.data.dados.nome_aprovacao_extra || 'Aprovação Extra'

            this.pagina = response.data.atual
            this.ultima = response.data.ultima
        })
    }
}
```

---

## 🖼️ 3. Template - Adicionar Botão

### Na Tabela de Listagem

```vue
<template>
    <b-table :items="dados" :fields="campos">
        <!-- Coluna de Status -->
        <template #cell(status)="data">
            <b-badge v-if="data.item.status_aprovacao === 'aprovado'" variant="success"> Aprovado Gestor </b-badge>

            <!-- ✨ ADICIONAR BADGES DE APROVAÇÃO EXTRA -->
            <b-badge v-if="data.item.status_aprovacao_extra === 'aprovado'" variant="success" class="ml-1">
                <i class="fas fa-check-circle"></i>
                {{ nomeAprovacaoExtra }}
            </b-badge>

            <b-badge v-if="data.item.status_aprovacao_extra === 'reprovado'" variant="danger" class="ml-1">
                <i class="fas fa-times-circle"></i>
                {{ nomeAprovacaoExtra }}
            </b-badge>
        </template>

        <!-- Coluna de Ações -->
        <template #cell(acoes)="data">
            <!-- Botão Gestor (já existe) -->
            <b-button v-if="!data.item.status_aprovacao" variant="primary" size="sm" @click="abrirModalAprovarGestor(data.item)"> Aprovar </b-button>

            <!-- ✨ ADICIONAR BOTÃO DE APROVAÇÃO EXTRA -->
            <b-button
                v-if="temAprovacaoExtra && podeAprovarExtra && data.item.status_aprovacao === 'aprovado' && !data.item.status_aprovacao_extra"
                variant="warning"
                size="sm"
                @click="abrirModalAprovacaoExtra(data.item)"
                class="ml-1"
            >
                <i class="fas fa-star"></i>
                {{ nomeAprovacaoExtra }}
            </b-button>

            <!-- Botão RH (se aplicável - apenas Valor Extra) -->
            <b-button
                v-if="data.item.status_aprovacao_extra === 'aprovado' && !data.item.status_aprovacao_rh"
                variant="success"
                size="sm"
                @click="abrirModalAprovarRH(data.item)"
                class="ml-1"
            >
                Aprovar RH
            </b-button>
        </template>
    </b-table>
</template>
```

---

## 🎭 4. Modal de Aprovação Extra

```vue
<!-- ✨ ADICIONAR ESTE MODAL -->
<b-modal id="modal-aprovacao-extra" ref="modalAprovacaoExtra" :title="`${nomeAprovacaoExtra} - Aprovação`" size="lg" @ok="aprovarExtra">
    <div v-if="itemSelecionado">
        <!-- Informações -->
        <b-card class="mb-3">
            <h6>Informações da Solicitação</h6>
            <p><strong>ID:</strong> #{{ itemSelecionado.id }}</p>
            <p><strong>Solicitante:</strong> {{ itemSelecionado.user_cadastrou?.nome }}</p>
            <p><strong>Data:</strong> {{ itemSelecionado.created_at }}</p>
        </b-card>
        
        <!-- Histórico -->
        <b-card class="mb-3" v-if="itemSelecionado.status_aprovacao">
            <h6>Aprovação do Gestor</h6>
            <b-badge variant="success">
                Aprovado por: {{ itemSelecionado.user_aprovacao?.nome }}
            </b-badge>
            <p class="text-muted small mt-1">
                {{ itemSelecionado.data_aprovacao }}
            </p>
            <p v-if="itemSelecionado.obs_aprovacao">
                <strong>Obs:</strong> {{ itemSelecionado.obs_aprovacao }}
            </p>
        </b-card>
        
        <!-- Formulário -->
        <b-form-group label="Decisão *" label-class="font-weight-bold">
            <b-form-radio-group
                v-model="formAprovacaoExtra.status_aprovacao_extra"
                :options="[
                    { text: '✅ Aprovar', value: 'aprovado' },
                    { text: '❌ Reprovar', value: 'reprovado' }
                ]"
                buttons
                button-variant="outline-primary"
                size="lg"
            />
        </b-form-group>
        
        <b-form-group 
            label="Observações" 
            label-class="font-weight-bold"
            :description="formAprovacaoExtra.status_aprovacao_extra === 'reprovado' ? 'Obrigatório informar o motivo' : 'Opcional'"
        >
            <b-form-textarea
                v-model="formAprovacaoExtra.obs_aprovacao_extra"
                rows="3"
                placeholder="Digite suas observações"
                :required="formAprovacaoExtra.status_aprovacao_extra === 'reprovado'"
            />
        </b-form-group>
    </div>
    
    <template #modal-footer="{ ok, cancel }">
        <b-button variant="secondary" @click="cancel()">
            Cancelar
        </b-button>
        <b-button 
            variant="primary" 
            @click="ok()"
            :disabled="!formAprovacaoExtra.status_aprovacao_extra || 
                      (formAprovacaoExtra.status_aprovacao_extra === 'reprovado' && 
                       !formAprovacaoExtra.obs_aprovacao_extra)"
        >
            <i class="fas fa-check"></i>
            Confirmar
        </b-button>
    </template>
</b-modal>
```

---

## ⚙️ 5. Métodos JavaScript

```javascript
methods: {
    /**
     * Abre modal de aprovação extra
     */
    abrirModalAprovacaoExtra(item) {
        this.itemSelecionado = item
        this.formAprovacaoExtra = {
            id: item.id,
            status_aprovacao_extra: '',
            obs_aprovacao_extra: ''
        }
        this.$refs.modalAprovacaoExtra.show()
    },

    /**
     * Envia aprovação extra
     */
    aprovarExtra() {
        // Validação
        if (!this.formAprovacaoExtra.status_aprovacao_extra) {
            this.$swal({
                icon: 'warning',
                title: 'Atenção',
                text: 'Selecione aprovar ou reprovar'
            })
            return
        }

        if (this.formAprovacaoExtra.status_aprovacao_extra === 'reprovado' &&
            !this.formAprovacaoExtra.obs_aprovacao_extra) {
            this.$swal({
                icon: 'warning',
                title: 'Atenção',
                text: 'Informe o motivo da reprovação'
            })
            return
        }

        // URL - AJUSTE CONFORME O PROCESSO
        // Para Requisição de Vaga:
        const url = `/planejamento/requisicao-vaga/${this.formAprovacaoExtra.id}/aprovarextra`

        // Para Valor Extra:
        // const url = `/planejamento/movimentacao/valor-extra-prevista/${this.formAprovacaoExtra.id}/aprovarextra`

        // Enviar
        axios.put(url, this.formAprovacaoExtra)
            .then(response => {
                const acao = this.formAprovacaoExtra.status_aprovacao_extra === 'aprovado'
                    ? 'aprovada'
                    : 'reprovada'

                this.$swal({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: `Solicitação ${acao} com sucesso!`,
                    timer: 2000
                })

                this.$refs.modalAprovacaoExtra.hide()
                this.atualizar()
            })
            .catch(error => {
                const mensagem = error.response?.data?.msg || 'Erro ao processar aprovação'

                this.$swal({
                    icon: 'error',
                    title: 'Erro',
                    text: mensagem
                })
            })
    }
}
```

---

## 🎨 6. Estilos CSS (Opcional)

```vue
<style scoped>
.badge {
    margin-left: 5px;
    font-size: 0.85rem;
}

.btn-sm {
    margin: 2px;
}

.aprovacao-extra-info {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 10px;
    margin-bottom: 15px;
}
</style>
```

---

## 🔔 7. Notificações e Feedback Visual

### Adicionar Ícones Informativos

```vue
<template>
    <!-- Info sobre aprovação extra ativa -->
    <b-alert v-if="temAprovacaoExtra" variant="info" show class="mb-3">
        <i class="fas fa-info-circle"></i>
        Este processo possui aprovação extra configurada:
        <strong>{{ nomeAprovacaoExtra }}</strong>
    </b-alert>

    <!-- Tooltip no botão -->
    <b-button
        v-if="temAprovacaoExtra && podeAprovarExtra"
        v-b-tooltip.hover
        :title="`Você pode aprovar como ${nomeAprovacaoExtra}`"
        variant="warning"
        @click="abrirModalAprovacaoExtra(item)"
    >
        <i class="fas fa-star"></i>
        {{ nomeAprovacaoExtra }}
    </b-button>
</template>
```

---

## 📊 8. Exibir Status Detalhado

```vue
<template>
    <!-- Timeline de aprovações -->
    <div class="aprovacao-timeline" v-if="item.id">
        <div class="timeline-item" :class="{ completed: item.status_aprovacao }">
            <i class="fas fa-user"></i>
            <span>Gestor</span>
            <b-badge v-if="item.status_aprovacao" variant="success">
                {{ item.status_aprovacao }}
            </b-badge>
        </div>

        <div v-if="temAprovacaoExtra" class="timeline-item" :class="{ completed: item.status_aprovacao_extra }">
            <i class="fas fa-star"></i>
            <span>{{ nomeAprovacaoExtra }}</span>
            <b-badge v-if="item.status_aprovacao_extra" :variant="item.status_aprovacao_extra === 'aprovado' ? 'success' : 'danger'">
                {{ item.status_aprovacao_extra }}
            </b-badge>
        </div>

        <div class="timeline-item" :class="{ completed: item.status_aprovacao_rh }">
            <i class="fas fa-users"></i>
            <span>RH</span>
            <b-badge v-if="item.status_aprovacao_rh" variant="success">
                {{ item.status_aprovacao_rh }}
            </b-badge>
        </div>
    </div>
</template>

<style scoped>
.aprovacao-timeline {
    display: flex;
    align-items: center;
    gap: 20px;
    margin: 10px 0;
}

.timeline-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 4px;
    opacity: 0.5;
}

.timeline-item.completed {
    opacity: 1;
    background: #d4edda;
}

.timeline-item i {
    font-size: 1.2rem;
}
</style>
```

---

## ✅ Checklist de Implementação

-   [ ] Adicionar variáveis no `data()`
-   [ ] Atualizar método `atualizar()` para capturar flags
-   [ ] Adicionar botão na tabela com condição `v-if`
-   [ ] Criar modal de aprovação extra
-   [ ] Implementar método `abrirModalAprovacaoExtra()`
-   [ ] Implementar método `aprovarExtra()`
-   [ ] Adicionar badges de status
-   [ ] Adicionar estilos CSS
-   [ ] Testar fluxo completo
-   [ ] Validar mensagens de erro

---

## 🧪 Como Testar

1. **Login** com usuário autorizado
2. **Listar** solicitações
3. **Verificar** se botão aparece corretamente
4. **Aprovar** como gestor
5. **Verificar** se botão de aprovação extra aparece
6. **Aprovar** como aprovação extra
7. **Verificar** status atualizado
8. **Testar** reprovação

---

## 💡 Dicas

1. **Reutilize código**: Se já existe modal de aprovação de gestor, duplique e adapte
2. **Mantenha consistência**: Use os mesmos estilos dos outros modais
3. **Validação**: Sempre valide antes de enviar
4. **Feedback**: Use SweetAlert2 para mensagens claras
5. **Loading**: Adicione spinners durante requisições
6. **Permissões**: Use `v-if` para controlar visibilidade baseada em permissões

---

## 📱 Responsividade

```vue
<!-- Botões empilhados em mobile -->
<div class="d-flex flex-column flex-md-row gap-2">
    <b-button>Aprovar Gestor</b-button>
    <b-button v-if="temAprovacaoExtra">{{ nomeAprovacaoExtra }}</b-button>
    <b-button>Aprovar RH</b-button>
</div>
```

---

## 🚀 Próximos Passos

Após implementar no frontend:

1. Testar todos os cenários
2. Validar permissões
3. Verificar responsividade
4. Documentar no README do componente
5. Criar testes E2E (se aplicável)

---

**Última atualização:** 07/02/2026  
**Versão:** 1.0
