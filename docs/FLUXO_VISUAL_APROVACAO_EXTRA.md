# 📊 Fluxo Visual do Sistema de Aprovação Extra

## 🔄 Diagrama de Fluxo - Demissão com Aprovação Extra

```
┌─────────────────────────────────────────────────────────────────┐
│                    INÍCIO - Solicitação de Demissão             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Sistema verifica: Empresa tem aprovação extra para demissão?  │
│  AprovacaoExtraConfig::getConfigAtiva($empresa_id, 'demissao')  │
└─────────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────┴─────────┐
                    │                   │
                  SIM                  NÃO
                    │                   │
                    ▼                   ▼
    ┌───────────────────────┐   ┌──────────────────┐
    │  Fluxo com 3 níveis   │   │  Fluxo padrão    │
    │  1. Gestor            │   │  1. Gestor       │
    │  2. RH                │   │  2. RH           │
    │  3. Extra (SESMT)     │   │  FIM             │
    └───────────────────────┘   └──────────────────┘
                │
                ▼
    ┌───────────────────────┐
    │  1ª APROVAÇÃO         │
    │  Gestor analisa       │
    │  ├─ Aprova            │
    │  └─ Reprova           │
    └───────────────────────┘
                │
                ▼
    ┌───────────────────────┐
    │  2ª APROVAÇÃO         │
    │  RH analisa           │
    │  ├─ Aprova            │
    │  └─ Reprova           │
    └───────────────────────┘
                │
                ▼
    ┌───────────────────────┐
    │  3ª APROVAÇÃO EXTRA   │
    │  SESMT analisa        │
    │  ├─ Aprova            │
    │  └─ Reprova           │
    └───────────────────────┘
                │
                ▼
    ┌───────────────────────┐
    │  CONCLUÍDO            │
    │  Demissão processada  │
    └───────────────────────┘
```

## 🗄️ Estrutura de Dados

```
┌──────────────────────────────────────────────────────────────┐
│  Tabela: aprovacao_extra_configs                             │
├──────────────────────────────────────────────────────────────┤
│  id               │  1                                       │
│  empresa_id       │  1                                       │
│  tipo_processo    │  'demissao'                              │
│  nome_aprovacao   │  'SESMT'                                 │
│  ativo            │  true                                    │
└──────────────────────────────────────────────────────────────┘
                              ↓ usa para determinar
┌──────────────────────────────────────────────────────────────┐
│  Tabela: demissao_previstas                                  │
├──────────────────────────────────────────────────────────────┤
│  id                        │  100                            │
│  colaborador_id            │  50                             │
│  data_demissao            │  '2025-02-15'                    │
│  ...                       │  ...                            │
│  ─────────────────────────────────────────────────────────── │
│  user_aprovacao_id         │  10  (Gestor)                   │
│  status_aprovacao          │  'aprovado'                     │
│  data_aprovacao            │  '2025-01-30 10:00'             │
│  ─────────────────────────────────────────────────────────── │
│  rh_aprovacao_id           │  20  (RH)                       │
│  status_aprovacao_rh       │  'aprovado'                     │
│  data_aprovacao_rh         │  '2025-01-30 14:00'             │
│  ─────────────────────────────────────────────────────────── │
│  aprovacao_extra_id        │  30  (SESMT)     ← NOVO!        │
│  status_aprovacao_extra    │  'aprovado'      ← NOVO!        │
│  obs_aprovacao_extra       │  'Doc OK'        ← NOVO!        │
│  data_aprovacao_extra      │  '2025-01-30 16:00' ← NOVO!     │
└──────────────────────────────────────────────────────────────┘
```

## 🎭 Comparação: Antes vs Depois

### ANTES (Sistema Fixo)

```
Empresa A:  Gestor → RH → FIM
Empresa B:  Gestor → RH → FIM
Empresa C:  Gestor → RH → FIM

❌ Todas as empresas com mesmo fluxo
❌ Sem flexibilidade
❌ Precisa customizar código para mudar
```

### DEPOIS (Sistema Dinâmico)

```
Empresa A:  Gestor → RH → SESMT → FIM
Empresa B:  Gestor → RH → FIM (sem extra)
Empresa C:  Gestor → RH → Jurídico → FIM

✅ Cada empresa configura seu fluxo
✅ Total flexibilidade
✅ Configuração via interface, sem código
```

## 📋 Estados da Solicitação

```
┌─────────────────────────────────────────────────────────────┐
│                      CICLO DE VIDA                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  [Criada]                                                   │
│     │                                                       │
│     ├─► [Aguardando Gestor]                                │
│     │      │                                                │
│     │      ├─► [Aprovado Gestor] ──┐                       │
│     │      └─► [Reprovado] ─────────┼──► [Finalizado]      │
│     │                               │                       │
│     ├─► [Aguardando RH]             │                       │
│     │      │                        │                       │
│     │      ├─► [Aprovado RH] ───────┤                       │
│     │      └─► [Reprovado] ─────────┤                       │
│     │                               │                       │
│     └─► [Aguardando SESMT]*         │  * Se configurado    │
│            │                        │                       │
│            ├─► [Aprovado SESMT] ────┤                       │
│            └─► [Reprovado] ─────────┘                       │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## 🔍 Consultas Importantes

### 1. Verificar se Empresa Tem Aprovação Extra

```php
$config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

if ($config) {
    echo "Tem aprovação extra: " . $config->nome_aprovacao;
} else {
    echo "Não tem aprovação extra";
}
```

### 2. Verificar se Demissão Está Totalmente Aprovada

```php
function estaAprovada($demissao) {
    $gestorOK = $demissao->status_aprovacao === 'aprovado';
    $rhOK = $demissao->status_aprovacao_rh === 'aprovado';

    // Verificar se tem aprovação extra
    $config = AprovacaoExtraConfig::getConfigAtiva(
        $demissao->empresa_id,
        'demissao'
    );

    if ($config) {
        $extraOK = $demissao->status_aprovacao_extra === 'aprovado';
        return $gestorOK && $rhOK && $extraOK;
    }

    return $gestorOK && $rhOK;
}
```

### 3. Buscar Pendências por Aprovador

```php
// Demissões pendentes para o SESMT aprovar
DemissaoPrevista::where('empresa_id', $empresaId)
    ->where('status_aprovacao', 'aprovado')
    ->where('status_aprovacao_rh', 'aprovado')
    ->whereNull('status_aprovacao_extra')
    ->get();
```

## 🎯 Interface do Usuário

### Tela de Configuração (Admin)

```
┌────────────────────────────────────────────────────────────┐
│  Configuração de Aprovações Extras          [+ Nova]      │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Tipo         Nome           Status      Ações        │ │
│  ├──────────────────────────────────────────────────────┤ │
│  │ Demissão     SESMT         ● Ativo    [✎][✓][✗]    │ │
│  │ Férias       Supervisor    ○ Inativo  [✎][✓][✗]    │ │
│  │ Mudança      Gerente RH    ● Ativo    [✎][✓][✗]    │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

### Tela de Solicitação (Demissão)

```
┌────────────────────────────────────────────────────────────┐
│  Nova Solicitação de Demissão                              │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Colaborador: [João Silva ▼]                              │
│  Data: [15/02/2025]                                        │
│  Motivo: [________________]                                │
│                                                            │
│  ℹ️ Esta solicitação será analisada por:                   │
│     1. Gestor                                              │
│     2. RH                                                  │
│     3. SESMT  ← Configurado para esta empresa             │
│                                                            │
│                              [Cancelar] [Solicitar]        │
└────────────────────────────────────────────────────────────┘
```

### Tela de Aprovação

```
┌────────────────────────────────────────────────────────────┐
│  Demissão #100 - João Silva                                │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Status das Aprovações:                                    │
│                                                            │
│  ✅ Gestor (Maria Santos)    30/01/2025 10:00             │
│  ✅ RH (Carlos Oliveira)     30/01/2025 14:00             │
│  ⏳ SESMT                      Aguardando...               │
│                                                            │
│  Observações do SESMT:                                     │
│  [____________________________]                            │
│                                                            │
│                              [Reprovar] [Aprovar]          │
└────────────────────────────────────────────────────────────┘
```

## 📊 Dashboard de Pendências

```
┌────────────────────────────────────────────────────────────┐
│  Minhas Pendências - SESMT                                 │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  📋 Demissões Aguardando Aprovação: 5                      │
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ #100  João Silva      15/02/2025    [Ver Detalhes]  │ │
│  │ #101  Maria Santos    20/02/2025    [Ver Detalhes]  │ │
│  │ #102  Pedro Costa     22/02/2025    [Ver Detalhes]  │ │
│  │ #103  Ana Souza       25/02/2025    [Ver Detalhes]  │ │
│  │ #104  Lucas Lima      28/02/2025    [Ver Detalhes]  │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

## 🔔 Notificações

```
Fluxo de Notificações:

1. Demissão criada
   ├─► Notifica: Gestor

2. Gestor aprova
   ├─► Notifica: RH

3. RH aprova
   ├─► Verifica se tem aprovação extra
   │   ├─► SIM: Notifica aprovador extra (SESMT)
   │   └─► NÃO: Processo concluído

4. SESMT aprova
   ├─► Notifica: RH (processo concluído)
   └─► Notifica: Solicitante
```

## 📈 Métricas e Relatórios

```sql
-- Tempo médio de aprovação por tipo
SELECT
    tipo_processo,
    AVG(DATEDIFF(data_aprovacao_extra, created_at)) as dias_media
FROM demissao_previstas d
JOIN aprovacao_extra_configs c ON d.empresa_id = c.empresa_id
WHERE c.ativo = 1
GROUP BY tipo_processo;

-- Taxa de aprovação/reprovação
SELECT
    status_aprovacao_extra,
    COUNT(*) as total,
    (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM demissao_previstas)) as percentual
FROM demissao_previstas
WHERE status_aprovacao_extra IS NOT NULL
GROUP BY status_aprovacao_extra;
```

## 🎨 Personalização por Empresa

```
┌─────────────────────────────────────────────────────────┐
│  EMPRESA A - Hospital                                   │
├─────────────────────────────────────────────────────────┤
│  Demissão:          SESMT                               │
│  Férias:            Coordenador                         │
│  Mudança Cargo:     Nenhum                              │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  EMPRESA B - Construção                                 │
├─────────────────────────────────────────────────────────┤
│  Demissão:          Engenheiro de Segurança             │
│  Férias:            Nenhum                              │
│  Transferência:     Gerente de Projeto                  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  EMPRESA C - Indústria                                  │
├─────────────────────────────────────────────────────────┤
│  Demissão:          Supervisor                          │
│  Férias:            Supervisor                          │
│  Mudança Cargo:     Diretor                             │
│  Valor Extra:       Financeiro                          │
└─────────────────────────────────────────────────────────┘
```

---

**Sistema 100% Dinâmico e Configurável!** 🚀
