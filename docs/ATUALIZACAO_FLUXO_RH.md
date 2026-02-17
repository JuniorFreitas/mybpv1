# ⚠️ ATUALIZAÇÃO IMPORTANTE - Sistema de Aprovação Extra

## 🔄 Mudanças no Fluxo de Aprovação

### ❌ FLUXO ANTIGO (INCORRETO)

```
Gestor → RH → Aprovação Extra
```

### ✅ FLUXO NOVO (CORRETO)

```
Gestor → Aprovação Extra → RH (SEMPRE A ÚLTIMA)
```

---

## 🎯 Principais Mudanças

### 1. **RH Sempre é a Última Aprovação**

O RH agora sempre é a aprovação final, independente de haver ou não aprovação extra configurada.

#### Fluxo COM Aprovação Extra:

```
┌─────────┐    ┌──────────────┐    ┌──────┐
│ Gestor  │ -> │ Aprox. Extra │ -> │  RH  │ ✓ Final
└─────────┘    └──────────────┘    └──────┘
```

#### Fluxo SEM Aprovação Extra:

```
┌─────────┐    ┌──────┐
│ Gestor  │ -> │  RH  │ ✓ Final
└─────────┘    └──────┘
```

---

### 2. **Controle de Usuários Autorizados**

Agora é possível definir **usuários específicos** que podem aprovar como aprovação extra.

#### Quem pode aprovar?

1. **Usuários selecionados** na configuração (`usuarios_autorizados`)
2. **Usuários com `privilegio_rh`** (sempre podem aprovar tudo)

```php
// Exemplo de configuração
AprovacaoExtraConfig::create([
    'empresa_id' => 1,
    'tipo_processo' => 'demissao',
    'nome_aprovacao' => 'SESMT',
    'usuarios_autorizados' => [5, 12, 23], // IDs específicos
    'ativo' => true
]);

// Verificar se usuário pode aprovar
$config->podeAprovar($userId); // true ou false
```

---

## 📊 Estrutura Atualizada

### Tabela: `aprovacao_extra_configs`

| Campo                    | Tipo     | Descrição                    |
| ------------------------ | -------- | ---------------------------- |
| id                       | bigint   | ID                           |
| empresa_id               | bigint   | FK para clientes             |
| tipo_processo            | enum     | demissao, ferias, etc        |
| nome_aprovacao           | string   | Ex: SESMT, Supervisor        |
| **usuarios_autorizados** | **json** | **[NOVO] Array de user_ids** |
| ativo                    | boolean  | Ativo/Inativo                |

---

## 🔧 Arquivos Criados/Atualizados

### Novos Arquivos:

1. **Migration para usuários autorizados**

    - `2025_01_30_000004_add_usuarios_autorizados_to_aprovacao_extra_configs_table.php`
    - Adiciona campo JSON `usuarios_autorizados`

2. **Exemplo atualizado com novo fluxo**

    - `EXEMPLO_USO_APROVACAO_EXTRA_V2.php`
    - Contém 6 exemplos atualizados
    - Fluxo correto: Gestor → Extra → RH

3. **Este arquivo de atualização**
    - `ATUALIZACAO_FLUXO_RH.md`
    - Documenta as mudanças

### Arquivos Atualizados:

1. **AprovacaoExtraConfig.php** (Model)

    - Campo `usuarios_autorizados` adicionado
    - Método `podeAprovar($userId)` criado
    - Método `UsuariosAutorizados()` criado

2. **AprovacaoExtraConfigController.php** (Controller)

    - Validação de `usuarios_autorizados`
    - Método `podeAprovar()` adicionado
    - Método `listarUsuarios()` adicionado

3. **RESUMO_APROVACAO_EXTRA.md** (Documentação)
    - Fluxo atualizado
    - Exemplos corrigidos

---

## 💻 Exemplos de Código Atualizados

### Exemplo 1: Aprovar como Aprovação Extra

```php
public function aprovarExtra(Request $request, $id)
{
    $empresaId = auth()->user()->empresa_id;
    $userId = auth()->user()->id;
    $demissao = DemissaoPrevista::findOrFail($id);

    // 1. Verificar se gestor já aprovou
    if ($demissao->status_aprovacao !== 'aprovado') {
        return response()->json([
            'message' => 'Gestor precisa aprovar primeiro'
        ], 400);
    }

    // 2. Buscar configuração e verificar permissão
    $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

    if (!$config->podeAprovar($userId)) {
        return response()->json([
            'message' => 'Você não tem permissão'
        ], 403);
    }

    // 3. Aprovar
    $demissao->update([
        'aprovacao_extra_id' => $userId,
        'status_aprovacao_extra' => 'aprovado',
        'data_aprovacao_extra' => now()
    ]);

    return response()->json([
        'message' => 'Aprovado! Aguardando aprovação final do RH.'
    ]);
}
```

### Exemplo 2: Aprovar como RH (SEMPRE A ÚLTIMA)

```php
public function aprovarRH(Request $request, $id)
{
    $empresaId = auth()->user()->empresa_id;
    $demissao = DemissaoPrevista::findOrFail($id);

    // 1. Verificar se gestor aprovou
    if ($demissao->status_aprovacao !== 'aprovado') {
        return response()->json([
            'message' => 'Gestor precisa aprovar primeiro'
        ], 400);
    }

    // 2. SE TIVER aprovação extra, verificar se foi aprovada
    $config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

    if ($config && $demissao->status_aprovacao_extra !== 'aprovado') {
        return response()->json([
            'message' => "Aguardando aprovação do {$config->nome_aprovacao}"
        ], 400);
    }

    // 3. RH pode aprovar (todas as anteriores foram feitas)
    $demissao->update([
        'rh_aprovacao_id' => auth()->id(),
        'status_aprovacao_rh' => 'aprovado',
        'data_aprovacao_rh' => now()
    ]);

    return response()->json([
        'message' => 'Processo concluído!'
    ]);
}
```

### Exemplo 3: Verificar Status do Fluxo

```php
public function statusFluxo($demissao)
{
    $config = AprovacaoExtraConfig::getConfigAtiva(
        $demissao->empresa_id,
        'demissao'
    );

    $fluxo = [
        [
            'etapa' => 'Gestor',
            'ordem' => 1,
            'status' => $demissao->status_aprovacao,
            'concluida' => $demissao->status_aprovacao === 'aprovado'
        ]
    ];

    if ($config) {
        $fluxo[] = [
            'etapa' => $config->nome_aprovacao,
            'ordem' => 2,
            'status' => $demissao->status_aprovacao_extra,
            'concluida' => $demissao->status_aprovacao_extra === 'aprovado'
        ];
    }

    $fluxo[] = [
        'etapa' => 'RH (Final)',
        'ordem' => $config ? 3 : 2,
        'status' => $demissao->status_aprovacao_rh,
        'concluida' => $demissao->status_aprovacao_rh === 'aprovado',
        'final' => true
    ];

    return $fluxo;
}
```

---

## 🎨 Interface Vue.js - Mudanças Necessárias

### Seletor de Usuários Autorizados

```vue
<!-- No componente de configuração -->
<div class="form-group">
    <label>Usuários Autorizados</label>
    <multiselect
        v-model="form.usuarios_autorizados"
        :options="todosUsuarios"
        :multiple="true"
        track-by="id"
        label="nome"
        placeholder="Selecione os usuários"
    >
    </multiselect>
    <small class="form-text text-muted">
        Usuários selecionados + quem tem "privilegio_rh" podem aprovar
    </small>
</div>
```

### Verificação de Permissão

```javascript
// Verificar se usuário atual pode aprovar
async verificarPermissao() {
    const response = await axios.post(
        '/g/administracao/aprovacao-extra-config/pode-aprovar',
        { tipo_processo: 'demissao' }
    );

    this.podeAprovar = response.data.pode_aprovar;
    this.nomeAprovacao = response.data.nome_aprovacao;
}
```

---

## 📋 Checklist de Migração

### Para Desenvolvedores:

-   [ ] **1. Executar nova migration**

    ```bash
    php artisan migrate
    ```

-   [ ] **2. Atualizar controllers**

    -   Inverter ordem: Extra ANTES do RH
    -   Adicionar verificação de permissão com `podeAprovar()`

-   [ ] **3. Atualizar componentes Vue**

    -   Adicionar seletor de usuários
    -   Atualizar ordem de exibição do fluxo

-   [ ] **4. Atualizar notificações**

    -   Notificar aprovador extra após gestor
    -   Notificar RH após aprovação extra

-   [ ] **5. Testar fluxos**
    -   COM aprovação extra: Gestor → Extra → RH
    -   SEM aprovação extra: Gestor → RH
    -   Verificar permissões de usuários

---

## ⚠️ Compatibilidade com Dados Antigos

✅ **Sistema é retrocompatível!**

-   Registros antigos sem aprovação extra continuam funcionando
-   Campo `usuarios_autorizados` é nullable
-   Se não tiver usuários configurados, apenas `privilegio_rh` pode aprovar

---

## 🆘 Troubleshooting

### Problema: "Aguardando aprovação do RH" mas já aprovou

**Causa:** Ordem incorreta, RH tentou aprovar antes da aprovação extra

**Solução:** Verificar se aprovação extra foi feita primeiro

```php
if ($config && !$demissao->status_aprovacao_extra) {
    // Ainda precisa de aprovação extra
}
```

### Problema: Usuário não consegue aprovar

**Verificar:**

1. Está na lista de `usuarios_autorizados`?
2. Tem `privilegio_rh`?
3. Configuração está ativa?

```php
$config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');
dd($config->podeAprovar($userId)); // true ou false
```

---

## 📞 Referências

-   **Código atualizado:** `EXEMPLO_USO_APROVACAO_EXTRA_V2.php`
-   **Documentação principal:** `README_APROVACAO_EXTRA.md`
-   **Model:** `app/Models/AprovacaoExtraConfig.php`
-   **Controller:** `app/Http/Controllers/AprovacaoExtraConfigController.php`

---

**Data da Atualização:** 30/01/2026
**Versão:** 2.0
**Status:** ✅ Implementado - Pronto para uso
