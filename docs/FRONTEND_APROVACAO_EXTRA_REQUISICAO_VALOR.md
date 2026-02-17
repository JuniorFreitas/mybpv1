# Frontend - Aprovação Extra: Requisição de Vaga e Valor Extra

## 📋 Resumo da Implementação

Implementação completa do sistema de **Aprovação Extra** no frontend para:

-   ✅ **Valor Extra Prevista** (Liderança de Pessoal)
-   ✅ **Requisição de Vaga** (Planejamento)

---

## 🎯 Arquivos Modificados

### 1. SolicitacaoValorExtra.vue

**Caminho**: `resources/js/components/planejamento/movimentacao/SolicitacaoValorExtra.vue`

#### Alterações realizadas:

**✅ Data() - Novas propriedades**

```javascript
aprovandoExtra: false,
aprovaExtra: false,
temAprovacaoExtra: false,
nomeAprovacaoExtra: 'Aprovação Extra',

form: {
    aprovacao_extra_id: '',
    aprovacao_extra_nome: '',
    obs_aprovacao_extra: '',
    status_aprovacao_extra: '',
    data_aprovacao_extra: '',
}
```

**✅ Método aprovarExtra()**

```javascript
aprovarExtra() {
    $(`#${this.hash} :input:visible`).trigger('blur')
    if ($(`#${this.hash} :input:visible.is-invalid`).length) {
        mostraErro('', 'Verifique os campos marcados')
        return false
    }

    this.preload = true
    axios
        .put(`${URL_ADMIN}/planejamento/movimentacao/valor-extra-prevista/${this.form.id}/aprovarextra`, this.form)
        .then((response) => {
            mostraSucesso('', 'Registro salvo com sucesso!')
            $(`#${this.hash}`).modal('hide')
            this.$refs.componente.buscar()
            this.preload = false
        })
        .catch((error) => {
            this.preload = false
        })
}
```

**✅ Método carregou() atualizado**

```javascript
carregou(dados) {
    this.lista = dados.itens
    this.aprovaGestor = dados.aprovar_por_gestor
    this.aprovaExtra = dados.pode_aprovar_extra || false
    this.aprovaRh = dados.aprovar_por_rh
    this.temAprovacaoExtra = dados.tem_aprovacao_extra || false
    this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || 'Aprovação Extra'
    this.controle.carregando = false
}
```

**✅ Template - Fieldset de Aprovação Extra**

```vue
<div v-if="(visualizar || aprovandoExtra) && temAprovacaoExtra" class="card shadow-sm mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> {{ nomeAprovacaoExtra }}</h5>
    </div>
    <div class="card-body">
        <!-- Conteúdo do fieldset -->
    </div>
</div>
```

**✅ Template - Botão de aprovação**

```vue
<button type="button" class="btn btn-sm btn-primary" v-show="aprovandoExtra && !preload" @click.prevent="aprovarExtra">
    <i class="fa fa-save"></i> Salvar
</button>
```

**✅ Template - Opção no dropdown**

```vue
<a
    class="dropdown-item"
    v-if="temAprovacaoExtra && item.status_aprovacao === 'aprovado' && !item.status_aprovacao_extra && aprovaExtra"
    @click.prevent="
        formOpen(item.id)
        aprovandoExtra = true
    "
>
    {{ nomeAprovacaoExtra }}
</a>
```

---

### 2. Requisição de Vaga - app.js

**Caminho**: `resources/js/g/planejamento/requisicao-vagas/app.js`

#### Alterações realizadas:

**✅ Data() - Novas propriedades**

```javascript
aprovandoExtra: false,
aprovaGestor: false,
aprovaExtra: false,
temAprovacaoExtra: false,
nomeAprovacaoExtra: 'Aprovação Extra',

form: {
    aprovacao_extra_id: '',
    aprovacao_extra_nome: '',
    obs_aprovacao_extra: '',
    status_aprovacao_extra: '',
    data_aprovacao_extra: '',
}
```

**✅ Método aprovarExtra()**

```javascript
aprovarExtra() {
    $('#janelaCadastrar :input:visible').trigger('blur');
    if ($('#janelaCadastrar :input:visible.is-invalid').length) {
        mostraErro('', 'Verifique os campos marcados')
        return false;
    }

    this.preload = true;

    axios.put(`${URL_ADMIN}/planejamento/requisicao-vaga/${this.form.id}/aprovarextra`, this.form)
        .then(response => {
            mostraSucesso('', 'Registro salvo com sucesso!');
            $('#janelaCadastrar').modal('hide');
            this.$refs.componente.buscar();
            this.preload = false;
        })
        .catch(error => {
            this.preload = false;
        })
}
```

**✅ Método carregou() atualizado**

```javascript
carregou(dados) {
    this.lista = dados.itens;
    this.aprovaGestor = dados.aprovar_por_gestor || false;
    this.aprovaExtra = dados.pode_aprovar_extra || false;
    this.temAprovacaoExtra = dados.tem_aprovacao_extra || false;
    this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || 'Aprovação Extra';
    this.controle.carregando = false;
}
```

---

### 3. Requisição de Vaga - index.blade.php

**Caminho**: `resources/views/g/planejamento/requisicao-vagas/index.blade.php`

#### Alterações realizadas:

**✅ Fieldset de Aprovação Extra**

```php
<div v-if="(visualizar || aprovandoExtra) && temAprovacaoExtra" class="card shadow-sm mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> @{{ nomeAprovacaoExtra }}</h5>
    </div>
    <div class="card-body">
        <!-- Campos de observação e status -->
    </div>
</div>
```

**✅ Botão de Aprovação Extra no rodapé**

```php
<button type="button" class="btn btn-sm btn-primary"
        v-show="aprovandoExtra && !preload && !cadastrando"
        @click.prevent="aprovarExtra">
    <i class="fa fa-save"></i> Salvar
</button>
```

**✅ Botão de Aprovação Extra na listagem**

```php
<a href="javascript://" class="btn btn-sm btn-primary mb-1" :title="nomeAprovacaoExtra"
   v-if="temAprovacaoExtra && item.status_aprovacao === 'aprovado' &&
         !item.status_aprovacao_extra && aprovaExtra"
   @click.prevent="formOpen(item.id); aprovandoExtra = true"
   data-toggle="modal" data-target="#janelaCadastrar">
    <i class="fa fa-clipboard-check"></i>
</a>
```

---

## 🔄 Fluxo de Aprovação

### Frontend - Lógica de Exibição

```
1. Sistema verifica configuração (tem_aprovacao_extra)
   ↓
2. Se TRUE: exibe etapa de Aprovação Extra
   ↓
3. Verifica permissão (pode_aprovar_extra)
   ↓
4. Se TRUE: exibe botão de aprovação
   ↓
5. Usuário aprova/reprova
   ↓
6. Chamada PUT /aprovarextra
   ↓
7. Backend atualiza registro
   ↓
8. Frontend atualiza lista
```

---

## 🎨 Interface do Usuário

### Cards de Aprovação Extra

-   **Header azul**: indica etapa de Aprovação Extra
-   **Ícone**: `fa-clipboard-check`
-   **Campos**:
    -   Observação (textarea)
    -   Status (select: Aprovar/Reprovar)
-   **Botão**: "Salvar" (só aparece em modo aprovação)

### Integração com Sistema Existente

✅ **Condicional**: Só exibe se `temAprovacaoExtra === true`
✅ **Permissão**: Botão só aparece se `aprovaExtra === true`
✅ **Workflow**: Integrado entre Gestor → Extra → RH
✅ **Notificações**: SweetAlert2 para feedback

---

## 📦 Endpoints Consumidos

### Valor Extra Prevista

```
PUT /planejamento/movimentacao/valor-extra-prevista/{id}/aprovarextra
```

### Requisição de Vaga

```
PUT /planejamento/requisicao-vaga/{id}/aprovarextra
```

### Resposta do Backend (atualizar)

```json
{
    "atual": 1,
    "dados": {
        "itens": [...],
        "pode_aprovar_extra": true|false,
        "tem_aprovacao_extra": true|false,
        "nome_aprovacao_extra": "Gerência",
        "aprovar_por_gestor": true|false,
        "aprovar_por_rh": true|false
    }
}
```

---

## ✅ Checklist de Validação

-   [x] Campos de aprovação extra no `form` object
-   [x] Flags de controle (`aprovandoExtra`, `aprovaExtra`, `temAprovacaoExtra`)
-   [x] Método `aprovarExtra()` implementado
-   [x] Método `carregou()` atualizado com flags
-   [x] Fieldset condicional no template
-   [x] Botão de aprovação no rodapé
-   [x] Opção no dropdown da listagem
-   [x] Inicialização de flags em `formNovo()` e `formOpen()`
-   [x] Assets compilados (`npm run dev`)
-   [x] Endpoint correto sendo chamado

---

## 🧪 Testes Sugeridos

### 1. Testar sem Aprovação Extra configurada

```
- temAprovacaoExtra = false
- Não deve exibir botão de Aprovação Extra
- Fluxo: Gestor → RH
```

### 2. Testar com Aprovação Extra configurada

```
- temAprovacaoExtra = true
- Exibe etapa de Aprovação Extra
- Fluxo: Gestor → Aprovação Extra → RH
```

### 3. Testar Permissões

```
- pode_aprovar_extra = false → Não exibe botão
- pode_aprovar_extra = true → Exibe botão
```

### 4. Testar Aprovação/Reprovação

```
- Aprovar: status_aprovacao_extra = 'aprovado'
- Reprovar: status_aprovacao_extra = 'reprovado'
- Seguir para RH só se aprovado
```

---

## 📝 Notas Importantes

1. **Padrão seguido**: Baseado em `SolicitacaoMudaCargo.vue` (já implementado)
2. **Bootstrap Vue**: Utiliza componentes existentes do projeto
3. **SweetAlert2**: Mensagens de sucesso/erro
4. **Laravel Mix**: Assets compilados com `npm run dev`
5. **Axios**: Requisições HTTP assíncronas

---

## 🔗 Documentação Relacionada

-   [PADRAO_APROVACAO_EXTRA.md](./PADRAO_APROVACAO_EXTRA.md) - Padrão backend
-   [IMPLEMENTACAO_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.md](./IMPLEMENTACAO_APROVACAO_EXTRA_REQUISICAO_VALOR_EXTRA.md) - Implementação completa backend
-   [GUIA_INTEGRACAO_FRONTEND_APROVACAO_EXTRA.md](./GUIA_INTEGRACAO_FRONTEND_APROVACAO_EXTRA.md) - Guia de integração frontend

---

**Data de Implementação**: 2026-02-07
**Versão Laravel**: 8.12
**Versão Vue**: 2.7.16
**Status**: ✅ Implementado e testado
