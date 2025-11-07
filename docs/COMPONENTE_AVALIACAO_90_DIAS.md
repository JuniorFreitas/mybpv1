# Componente Reutilizável: Avaliação 90 Dias

## 📋 Descrição

O componente `AvaliacaoNoventaDias` é um componente Vue.js reutilizável que gerencia avaliações de 90 dias de colaboradores. Ele pode ser usado em qualquer parte do sistema, não apenas no histórico.

## 📁 Localização dos Arquivos

```
resources/js/
├── components/
│   ├── shared/
│   │   └── AvaliacaoNoventaDias.vue  # Componente reutilizável
│   └── admissao/
│       └── historico/
│           └── FormularioNoventaDias.vue  # Implementação no histórico
└── mixins/
    └── avaliacaoNoventaMixin.js  # Lógica compartilhada
```

## 🚀 Como Usar

### 1. Uso Básico

```vue
<template>
  <div>
    <avaliacao-noventa-dias
      :feedback-id="123"
    />
  </div>
</template>

<script>
import AvaliacaoNoventaDias from '@/components/shared/AvaliacaoNoventaDias.vue'

export default {
  components: {
    AvaliacaoNoventaDias
  }
}
</script>
```

### 2. Uso Avançado (Todas as Props)

```vue
<template>
  <div>
    <avaliacao-noventa-dias
      :feedback-id="feedbackId"
      modal-id="meuModalCustomizado"
      modal-pai="modalPrincipal"
      :exibir-vencimentos="true"
      :exibir-botao-adicionar="true"
      :exibir-modal="true"
      :readonly="false"
      @adicionar-avaliacao="onAdicionar"
      @avaliacao-salva="onSalvar"
      @erro-salvar="onErro"
    />
  </div>
</template>

<script>
import AvaliacaoNoventaDias from '@/components/shared/AvaliacaoNoventaDias.vue'

export default {
  components: {
    AvaliacaoNoventaDias
  },
  
  data() {
    return {
      feedbackId: 123
    }
  },
  
  methods: {
    onAdicionar(feedbackId) {
      console.log('Nova avaliação sendo criada:', feedbackId)
    },
    
    onSalvar(form) {
      console.log('Avaliação salva:', form)
      // Executar ações adicionais
    },
    
    onErro(error) {
      console.error('Erro ao salvar:', error)
      // Tratar erro customizado
    }
  }
}
</script>
```

### 3. Uso Somente Leitura (Visualização)

```vue
<template>
  <div>
    <avaliacao-noventa-dias
      :feedback-id="feedbackId"
      :exibir-botao-adicionar="false"
      :exibir-modal="false"
      :readonly="true"
    />
  </div>
</template>
```

## 🎛️ Props

| Prop | Tipo | Obrigatório | Padrão | Descrição |
|------|------|-------------|--------|-----------|
| `feedback-id` | Number | ✅ Sim | - | ID do feedback/admissão |
| `modal-id` | String | ❌ Não | `'janelaFormularioNoventaDias'` | ID único do modal |
| `modal-pai` | String | ❌ Não | `''` | ID do modal pai (para modais aninhados) |
| `exibir-vencimentos` | Boolean | ❌ Não | `true` | Mostrar tabela de vencimentos |
| `exibir-botao-adicionar` | Boolean | ❌ Não | `true` | Mostrar botão "Adicionar Avaliação" |
| `exibir-modal` | Boolean | ❌ Não | `true` | Renderizar modal de formulário |
| `readonly` | Boolean | ❌ Não | `false` | Modo somente leitura |

## 📤 Eventos Emitidos

| Evento | Parâmetros | Descrição |
|--------|-----------|-----------|
| `adicionar-avaliacao` | `(feedbackId: Number)` | Disparado ao clicar em "Adicionar Avaliação" |
| `avaliacao-salva` | `(form: Object)` | Disparado quando avaliação é salva com sucesso |
| `erro-salvar` | `(error: Error)` | Disparado quando ocorre erro ao salvar |

## 🔧 Usando o Mixin Diretamente

Se você precisa de mais controle, pode usar o mixin diretamente:

```vue
<script>
import avaliacaoNoventaMixin from '@/mixins/avaliacaoNoventaMixin'

export default {
  mixins: [avaliacaoNoventaMixin],
  
  mounted() {
    // Inicializa formulário
    this.inicializarFormularioAvaliacao()
    
    // Carrega dados
    this.carregarDadosAvaliacao(123)
      .then(data => {
        console.log('Dados carregados:', data)
      })
  },
  
  methods: {
    salvarMinhaAvaliacao() {
      this.salvarAvaliacao('meuModal')
        .then(() => {
          console.log('Salvo com sucesso!')
        })
    }
  }
}
</script>
```

### Métodos Disponíveis no Mixin

- `inicializarFormularioAvaliacao()` - Inicializa formulário padrão
- `carregarDadosAvaliacao(feedbackId)` - Carrega dados da API
- `prepararNovaAvaliacao(feedbackId)` - Prepara formulário para nova avaliação
- `salvarAvaliacao(modalId)` - Salva avaliação (retorna Promise)
- `gerarPdfAvaliacao(item)` - Abre PDF em nova aba
- `podeAdicionarAvaliacao()` - Verifica se pode adicionar (máx. 2)
- `getStatusAvaliacao()` - Retorna status textual

### Dados Disponíveis no Mixin

```javascript
{
  preloadAvaliacao: false,
  preloadSalvarAvaliacao: false,
  perguntasAvaliacao: [],
  tabelaNoventaAvaliacao: [],
  avNoventaVencimentoData: null,
  formAvaliacao: {
    gestor_imediato: '',
    feedback_id: '',
    observacao: '',
    perguntas: []
  }
}
```

## 📦 Exemplos de Uso no Sistema

### 1. Em uma Tela de Dashboard

```vue
<template>
  <div class="dashboard">
    <h2>Avaliações Pendentes</h2>
    <avaliacao-noventa-dias
      v-for="colaborador in colaboradores"
      :key="colaborador.feedback_id"
      :feedback-id="colaborador.feedback_id"
      :exibir-vencimentos="false"
    />
  </div>
</template>
```

### 2. Em um Modal Externo

```vue
<template>
  <modal id="avaliacaoExterna">
    <avaliacao-noventa-dias
      :feedback-id="feedbackSelecionado"
      modal-pai="avaliacaoExterna"
      modal-id="formularioInterno"
    />
  </modal>
</template>
```

### 3. Em um Relatório Público

```vue
<template>
  <div class="relatorio-publico">
    <h1>Histórico de Avaliações</h1>
    <avaliacao-noventa-dias
      :feedback-id="parametroUrl"
      :exibir-botao-adicionar="false"
      :exibir-modal="false"
      :readonly="true"
    />
  </div>
</template>
```

## 🎨 Customização de Estilos

O componente possui estilos básicos que podem ser sobrescritos:

```vue
<style>
/* Sobrescrever estilos globalmente */
.avaliacao-noventa-dias-wrapper {
  background: #f5f5f5;
  padding: 20px;
}

.avaliacao-noventa-dias-wrapper .table {
  font-size: 14px;
}
</style>
```

## 🔒 Regras de Negócio

1. **Máximo de 2 avaliações** por colaborador
2. **Vencimentos automáticos** baseados em tipo de admissão:
   - **FIXO**: 45+45 dias (1º e 2º vencimento)
   - **TEMPORÁRIO/DETERMINADO**: Data de encerramento
3. **Perguntas dinâmicas** carregadas do backend
4. **PDF gerado** automaticamente para cada avaliação

## 🐛 Troubleshooting

### Modal não abre
- Verifique se `exibir-modal="true"`
- Confirme que Bootstrap está carregado
- Verifique conflitos de IDs de modais

### Dados não carregam
- Confirme que `feedback_id` é válido
- Verifique rota da API: `/g/historico/{feedbackId}`
- Veja console para erros de rede

### Formulário não valida
- Funções globais `valida_campo_vazio`, `formReset`, `setupCampo` devem existir
- Verifique se jQuery está disponível

## 📚 Dependências

- Vue.js 2.x
- Axios
- Lodash (`_.cloneDeep`)
- jQuery (para validações e modal)
- Bootstrap 4 (para modal)

## 🔄 Fluxo de Dados

```
1. Componente montado
   ↓
2. carregarDadosAvaliacao(feedbackId)
   ↓
3. API: GET /g/historico/{feedbackId}
   ↓
4. Renderiza: vencimentos + avaliações realizadas
   ↓
5. Usuário clica "Adicionar"
   ↓
6. prepararNovaAvaliacao() → Abre modal
   ↓
7. Usuário preenche e salva
   ↓
8. salvarAvaliacao() → API: POST /g/historico/formulario-noventa-dias/{feedbackId}
   ↓
9. Evento @avaliacao-salva emitido
   ↓
10. carregarDadosAvaliacao() novamente (atualiza lista)
```

## ✅ Checklist de Implementação

- [x] Componente reutilizável criado
- [x] Mixin com lógica extraída
- [x] Props configuráveis
- [x] Eventos emitidos
- [x] Exemplo no histórico refatorado
- [x] Documentação completa
- [ ] Registrar componente globalmente (opcional)
- [ ] Criar testes unitários
- [ ] Adicionar TypeScript types (opcional)

## 📝 Registro Global (Opcional)

Para usar sem importar em cada componente:

```javascript
// resources/js/app.js
import AvaliacaoNoventaDias from './components/shared/AvaliacaoNoventaDias.vue'

Vue.component('avaliacao-noventa-dias', AvaliacaoNoventaDias)
```

Depois use direto em qualquer template:

```vue
<template>
  <avaliacao-noventa-dias :feedback-id="123" />
</template>
```

## 🤝 Contribuindo

Para adicionar funcionalidades ao componente:

1. Modifique o mixin `avaliacaoNoventaMixin.js` para lógica
2. Atualize o componente `AvaliacaoNoventaDias.vue` para UI
3. Adicione props/eventos conforme necessário
4. Atualize esta documentação
