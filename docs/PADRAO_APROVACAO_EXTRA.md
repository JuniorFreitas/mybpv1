# Padrão de Aprovação Extra - Guia de Implementação

## Visão Geral

Sistema de aprovação extra configurável que permite adicionar uma camada adicional de aprovação entre a aprovação do gestor e a aprovação do RH. Implementado em:

-   ✅ Demissão Prevista
-   ✅ Mudança de Cargo
-   ✅ Férias Prevista
-   ✅ Valor Extra Prevista

## Conceito

A aprovação extra é uma camada **opcional** e **configurável** que permite que determinados usuários aprovem solicitações após o gestor e antes do RH. A configuração determina:

1. Se a aprovação extra está ativa
2. Quais usuários podem aprovar
3. O nome/rótulo da aprovação

## Permissões

### Quem pode aprovar na Aprovação Extra?

Três tipos de usuários podem aprovar:

1. **Usuários com `privilegio_gestao_rh`** - Sempre podem aprovar qualquer aprovação extra
2. **Usuários com `privilegio_aprovar_por_rh`** - Sempre podem aprovar qualquer aprovação extra
3. **Usuários na lista de aprovadores** - Definidos na configuração específica via `AprovacaoExtraConfig`

## Estrutura do Banco de Dados

### Tabela: `aprovacao_extra_configs`

```sql
CREATE TABLE aprovacao_extra_configs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empresa_id INT NOT NULL,
    tipo_processo VARCHAR(50) NOT NULL, -- 'demissao', 'mudanca_cargo', 'ferias', 'valor_extra'
    nome_aprovacao VARCHAR(100) NOT NULL, -- Ex: "Gerência", "Diretoria"
    usuarios_autorizados JSON, -- Array de IDs dos usuários
    emails_aprovadores JSON, -- Array de emails dos aprovadores
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY (empresa_id, tipo_processo, ativo)
);
```

### Colunas nas tabelas de processos

Adicionar nas tabelas (`demissoes_previstas`, `mudanca_cargos`, etc.):

```sql
ALTER TABLE {tabela} ADD COLUMN aprovacao_extra_id INT NULL;
ALTER TABLE {tabela} ADD COLUMN status_aprovacao_extra VARCHAR(20) NULL;
ALTER TABLE {tabela} ADD COLUMN obs_aprovacao_extra TEXT NULL;
ALTER TABLE {tabela} ADD COLUMN data_aprovacao_extra DATETIME NULL;
```

## Implementação Backend (Laravel)

### 1. Model AprovacaoExtraConfig

```php
// app/Models/AprovacaoExtraConfig.php

class AprovacaoExtraConfig extends Model
{
    protected $casts = [
        'usuarios_autorizados' => 'array',
        'emails_aprovadores' => 'array',
        'ativo' => 'boolean',
    ];

    /**
     * Busca configuração ativa para um processo específico
     */
    public static function getConfigAtiva($empresaId, $tipoProcesso)
    {
        return self::where('empresa_id', $empresaId)
            ->where('tipo_processo', $tipoProcesso)
            ->where('ativo', true)
            ->first();
    }

    /**
     * Verifica se usuário pode aprovar
     * - Usuários com privilegio_gestao_rh sempre podem
     * - Usuários com privilegio_aprovar_por_rh sempre podem
     * - Usuários na lista usuarios_autorizados podem
     */
    public function podeAprovar($userId)
    {
        $userId = is_numeric($userId) ? (int) $userId : null;
        if (!$userId) return false;

        $user = \App\Models\User::find($userId);
        if (!$user) return false;

        // Verifica permissões globais
        $habilidades = $user->listaDeHabilidades();
        if (in_array('privilegio_gestao_rh', $habilidades) ||
            in_array('privilegio_aprovar_por_rh', $habilidades)) {
            return true;
        }

        // Verifica lista de autorizados
        if (is_array($this->usuarios_autorizados)) {
            return in_array($userId, $this->usuarios_autorizados);
        }

        return false;
    }
}
```

### 2. Controller - Método atualizar()

```php
public function atualizar(Request $request)
{
    $resultado = $this->filtro($request)->paginate($request->pages);

    // Busca configuração de aprovação extra ativa
    $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'nome_processo');
    $podeAprovarExtra = false;
    $nomeAprovacaoExtra = '';

    if ($config) {
        $podeAprovarExtra = $config->podeAprovar(auth()->id());
        $nomeAprovacaoExtra = $config->nome_aprovacao;
    }

    return response()->json([
        'atual' => $resultado->currentPage(),
        'ultima' => $resultado->lastPage(),
        'total' => $resultado->total(),
        'dados' => [
            'itens' => $resultado->items(),
            'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
            'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
            'pode_aprovar_extra' => $podeAprovarExtra, // ⚠️ IMPORTANTE: pode_aprovar_extra
            'tem_aprovacao_extra' => $config ? true : false,
            'nome_aprovacao_extra' => $nomeAprovacaoExtra,
        ]
    ]);
}
```

### 3. Controller - Método aprovarGestor()

```php
public function aprovarGestor(Request $request)
{
    $this->authorize('privilegio_aprovar_por_gestor');
    $dados = $request->input();

    try {
        DB::beginTransaction();

        $registro = Modelo::find($dados['id']);
        $registro->update([
            'gestor_aprovacao_id' => auth()->id(),
            'data_aprovacao_gestor' => now(),
            'obs_gestor_aprovacao' => $dados['obs_gestor_aprovacao'],
            'status_aprovacao_gestor' => $dados['status_aprovacao_gestor'],
        ]);

        DB::commit();

        // Notifica solicitante
        $this->notificarUsuario($registro->id, $registro->solicitante_id, 'atualização', 'Gestor', $dados['status_aprovacao_gestor']);

        // Se aprovado, verifica se tem aprovação extra
        if ($dados['status_aprovacao_gestor'] === 'aprovado') {
            $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'tipo_processo');

            if ($config && !empty($config->emails_aprovadores)) {
                // Tem aprovação extra - notifica equipe extra
                $this->notificarAprovacaoExtra($registro->id);
            } else {
                // Não tem aprovação extra - notifica RH diretamente
                $this->notificarRH($registro->id);
            }
        }

        return response()->json([], 201);
    } catch (\Exception $e) {
        DB::rollback();
        \Log::error("Erro ao aprovar (Gestor): " . $e->getMessage());
        return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
    }
}
```

### 4. Controller - Método aprovarExtra()

```php
public function aprovarExtra(Request $request)
{
    $dados = $request->input();

    // Busca configuração ativa
    $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'tipo_processo');

    if (!$config) {
        return response()->json(['msg' => 'Não existe configuração de aprovação extra ativa'], 400);
    }

    // ⚠️ IMPORTANTE: Verifica permissão via podeAprovar()
    if (!$config->podeAprovar(auth()->id())) {
        return response()->json(['msg' => 'Você não tem permissão para aprovar esta solicitação'], 403);
    }

    try {
        DB::beginTransaction();

        $registro = Modelo::find($dados['id']);
        $registro->update([
            'aprovacao_extra_id' => auth()->id(),
            'data_aprovacao_extra' => now(),
            'obs_aprovacao_extra' => $dados['obs_aprovacao_extra'] ?? null,
            'status_aprovacao_extra' => $dados['status_aprovacao_extra'],
        ]);

        DB::commit();

        // Notifica solicitante
        $this->notificarUsuario($registro->id, $registro->solicitante_id, 'atualização', 'Aprovação Extra', $dados['status_aprovacao_extra']);

        // Notifica gestor
        $this->notificarUsuario($registro->id, $registro->gestor_id, 'atualização', 'Aprovação Extra', $dados['status_aprovacao_extra']);

        // Se aprovado, notifica RH
        if ($dados['status_aprovacao_extra'] === 'aprovado') {
            $this->notificarRH($registro->id);
        }

        return response()->json([], 201);
    } catch (\Exception $e) {
        DB::rollback();
        \Log::error("Erro ao aprovar (Extra): " . $e->getMessage());
        return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
    }
}
```

## Implementação Frontend (Vue.js)

### 1. Data Properties

```javascript
data() {
    return {
        aprovaGestor: false,
        aprovaExtra: false,      // ⚠️ Recebe pode_aprovar_extra do backend
        aprovaRh: false,
        temAprovacaoExtra: false,
        nomeAprovacaoExtra: '',
        aprovandoExtra: false,
        // ...
    }
}
```

### 2. Método carregou()

```javascript
carregou(dados) {
    this.lista = dados.itens;
    this.aprovaGestor = dados.aprovar_por_gestor;
    this.aprovaExtra = dados.pode_aprovar_extra || false; // ⚠️ IMPORTANTE: pode_aprovar_extra
    this.aprovaRh = dados.aprovar_por_rh;
    this.temAprovacaoExtra = dados.tem_aprovacao_extra || false;
    this.nomeAprovacaoExtra = dados.nome_aprovacao_extra || 'Aprovação Extra';
    this.controle.carregando = false;
}
```

### 3. Template - Botão no Dropdown

```vue
<a
    class="dropdown-item"
    href="javascript://"
    :title="nomeAprovacaoExtra || 'Aprovação Extra'"
    data-toggle="modal"
    :data-target="`#${hash}`"
    @click.prevent="
        formOpen(item.id)
        aprovandoExtra = true
        visualizar = false
        aprovando = false
        aprovandoRh = false
    "
    v-if="
        temAprovacaoExtra &&
        aprovaExtra &&
        item.status_aprovacao_gestor === 'aprovado' &&
        !item.aprovacao_extra_id &&
        !item.aprovado_via_script &&
        !item.rh_aprovacao_id
    "
>
    {{ nomeAprovacaoExtra || 'Aprovação Extra' }}
</a>
```

### 4. Template - Card de Aprovação Extra

```vue
<div v-if="(visualizar || aprovandoExtra) && temAprovacaoExtra" class="card shadow-sm mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">
            <i class="fas fa-shield-alt mr-2"></i>
            {{ nomeAprovacaoExtra || 'Aprovação Extra' }}
        </h6>
    </div>
    <div class="card-body">
        <div class="alert alert-warning d-flex align-items-center mb-3" v-if="aprovandoExtra">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span>Você está aprovando como <strong>{{ nomeAprovacaoExtra }}</strong></span>
        </div>

        <div v-if="!aprovandoExtra && form.aprovacao_extra_nome" class="alert alert-info">
            <strong>Aprovado por:</strong> {{ form.aprovacao_extra_nome }}
        </div>

        <!-- Formulário de aprovação -->
        <div v-if="aprovandoExtra">
            <div class="form-group">
                <label>Status da Aprovação *</label>
                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                    <label class="btn btn-outline-success" :class="{'active': form.status_aprovacao_extra === 'aprovado'}">
                        <input type="radio" v-model="form.status_aprovacao_extra" value="aprovado" required>
                        <i class="fas fa-check"></i> Aprovar
                    </label>
                    <label class="btn btn-outline-danger" :class="{'active': form.status_aprovacao_extra === 'reprovado'}">
                        <input type="radio" v-model="form.status_aprovacao_extra" value="reprovado" required>
                        <i class="fas fa-times"></i> Reprovar
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Observações</label>
                <textarea class="form-control" v-model="form.obs_aprovacao_extra" rows="3"></textarea>
            </div>
        </div>

        <!-- Visualização -->
        <div v-else>
            <p><strong>Status:</strong>
                <span v-if="form.status_aprovacao_extra === 'aprovado'" class="badge badge-success">Aprovado</span>
                <span v-else-if="form.status_aprovacao_extra === 'reprovado'" class="badge badge-danger">Reprovado</span>
                <span v-else class="badge badge-warning">Pendente</span>
            </p>
            <p v-if="form.obs_aprovacao_extra"><strong>Observações:</strong> {{ form.obs_aprovacao_extra }}</p>
            <p v-if="form.data_aprovacao_extra"><strong>Data:</strong> {{ form.data_aprovacao_extra }}</p>
        </div>
    </div>
</div>
```

### 5. Template - Fluxo de Aprovação

```vue
<!-- Fluxo visual das aprovações -->
<div class="fluxo-aprovacao">
    <!-- Gestor -->
    <div class="fluxo-step">
        <div class="fluxo-icone">
            <i class="fas fa-user-tie" :class="getFluxoClass(item.status_aprovacao_gestor)"></i>
        </div>
        <small class="fluxo-label">Gestor</small>
        <small class="fluxo-nome">{{ item.gestor_aprovacao }}</small>
        <small class="fluxo-status">{{ getStatusLabel(item.status_aprovacao_gestor) }}</small>
    </div>

    <i class="fas fa-chevron-right text-muted mx-2"></i>

    <!-- Aprovação Extra (condicional) -->
    <div class="fluxo-step" v-if="temAprovacaoExtra">
        <div class="fluxo-icone">
            <i class="fas fa-shield-alt" :class="getFluxoClass(item.status_aprovacao_extra)"></i>
        </div>
        <small class="fluxo-label">{{ nomeAprovacaoExtra }}</small>
        <small class="fluxo-nome">{{ item.aprovacao_extra_nome }}</small>
        <small class="fluxo-status">{{ getStatusLabel(item.status_aprovacao_extra) }}</small>
    </div>

    <i class="fas fa-chevron-right text-muted mx-2" v-if="temAprovacaoExtra"></i>

    <!-- RH -->
    <div class="fluxo-step">
        <div class="fluxo-icone">
            <i class="fas fa-building" :class="getFluxoClass(item.status_aprovacao_rh)"></i>
        </div>
        <small class="fluxo-label">RH</small>
        <small class="fluxo-nome">{{ item.rh_aprovacao }}</small>
        <small class="fluxo-status">{{ getStatusLabel(item.status_aprovacao_rh) }}</small>
    </div>
</div>
```

### 6. Métodos Auxiliares

```javascript
methods: {
    getFluxoClass(status) {
        if (status === 'aprovado') return 'text-success';
        if (status === 'reprovado') return 'text-danger';
        return 'text-warning';
    },
    getStatusLabel(status) {
        if (status === 'aprovado') return 'Aprovado';
        if (status === 'reprovado') return 'Reprovado';
        return 'Pendente';
    }
}
```

## Checklist de Implementação

### Backend

-   [ ] Adicionar colunas na tabela do processo:

    -   `aprovacao_extra_id`
    -   `status_aprovacao_extra`
    -   `obs_aprovacao_extra`
    -   `data_aprovacao_extra`

-   [ ] Adicionar relacionamento no Model:

```php
public function AprovacaoExtra()
{
    return $this->belongsTo(User::class, 'aprovacao_extra_id');
}
```

-   [ ] Atualizar método `atualizar()`:

    -   Buscar config com `AprovacaoExtraConfig::getConfigAtiva()`
    -   Verificar permissão com `$config->podeAprovar(auth()->id())`
    -   Retornar `pode_aprovar_extra` no response

-   [ ] Criar método `aprovarExtra()`:

    -   Validar config existe
    -   Validar permissão com `$config->podeAprovar()`
    -   Atualizar registro
    -   Notificar envolvidos

-   [ ] Atualizar método `aprovarGestor()`:

    -   Verificar se tem config ativa
    -   Se tem, chamar `notificarAprovacaoExtra()`
    -   Se não tem, chamar `notificarRH()`

-   [ ] Criar Jobs de notificação:
    -   `JobNotificacaoAprovacaoExtra.php`
    -   Usar eager loading
    -   Otimizar queries

### Frontend

-   [ ] Adicionar propriedades no `data()`:

    -   `aprovaExtra`
    -   `temAprovacaoExtra`
    -   `nomeAprovacaoExtra`
    -   `aprovandoExtra`

-   [ ] Atualizar método `carregou()`:

    -   `this.aprovaExtra = dados.pode_aprovar_extra`
    -   `this.temAprovacaoExtra = dados.tem_aprovacao_extra`
    -   `this.nomeAprovacaoExtra = dados.nome_aprovacao_extra`

-   [ ] Adicionar botão no dropdown:

    -   Condição: `temAprovacaoExtra && aprovaExtra && item.status_aprovacao_gestor === 'aprovado'`

-   [ ] Criar card de aprovação extra

-   [ ] Atualizar fluxo visual de aprovações

-   [ ] Adicionar campos no form:
    -   `status_aprovacao_extra`
    -   `obs_aprovacao_extra`

## Debugging

### Logs Backend

```php
\Log::info('Verificação de aprovação extra', [
    'user_id' => auth()->id(),
    'habilidades' => auth()->user()->listaDeHabilidades(),
    'config_id' => $config->id ?? null,
    'usuarios_autorizados' => $config->usuarios_autorizados ?? [],
    'pode_aprovar_extra' => $podeAprovarExtra
]);
```

### Logs Frontend

```javascript
console.log('Dados recebidos:', {
    pode_aprovar_extra: dados.pode_aprovar_extra,
    tem_aprovacao_extra: dados.tem_aprovacao_extra,
    nome_aprovacao_extra: dados.nome_aprovacao_extra
})

console.log('Variáveis setadas:', {
    aprovaExtra: this.aprovaExtra,
    temAprovacaoExtra: this.temAprovacaoExtra
})
```

## Nomenclatura Importante

⚠️ **ATENÇÃO à nomenclatura correta:**

| Backend                | Frontend             | Descrição                                     |
| ---------------------- | -------------------- | --------------------------------------------- |
| `pode_aprovar_extra`   | `aprovaExtra`        | Se o usuário PODE aprovar                     |
| `tem_aprovacao_extra`  | `temAprovacaoExtra`  | Se o processo TEM aprovação extra configurada |
| `nome_aprovacao_extra` | `nomeAprovacaoExtra` | Nome/rótulo da aprovação                      |

**Erro comum:** Usar `aprovar_por_extra` no backend (errado) ao invés de `pode_aprovar_extra` (correto).

## Exemplos de Uso

### Cenário 1: Processo COM aprovação extra

1. Solicitante cria → notifica apenas Gestor
2. Gestor aprova → notifica Solicitante + Equipe Extra
3. Extra aprova → notifica Solicitante + Gestor + RH
4. RH aprova → notifica todos os envolvidos

### Cenário 2: Processo SEM aprovação extra

1. Solicitante cria → notifica apenas Gestor
2. Gestor aprova → notifica Solicitante + RH (pula Extra)
3. RH aprova → notifica todos os envolvidos

## Referências

-   Implementação em: `DemissaoPrevistaController.php`
-   Implementação em: `MudancaCargoController.php`
-   Model: `AprovacaoExtraConfig.php`
-   Componente: `SolicitacaoDemissao.vue`
-   Componente: `SolicitacaoMudaCargo.vue`

---

**Última atualização:** 07/02/2026
