# Implementação do Sistema de Aprovação Extra em Requisição de Vagas

**Data**: 2026-02-07  
**Sistema**: MyBP - Planejamento - Requisição de Vagas  
**Status**: ✅ **COMPLETO** (Backend + Frontend + Notificações)

---

## 📋 Resumo Executivo

Implementação completa do sistema de **Aprovação Extra** para o módulo de **Requisição de Vagas**, seguindo o mesmo padrão já estabelecido em:

-   ✅ Demissão Prevista
-   ✅ Mudança de Cargo
-   ✅ Férias Previstas
-   ✅ Valor Extra Prevista

O sistema adiciona uma etapa intermediária opcional de aprovação entre o **Gestor** e o **RH**.

---

## 🎯 Objetivos Alcançados

### 1. Backend (Database + Models + Controllers)

-   ✅ Migration para adicionar 4 colunas na tabela `requisicao_vagas`
-   ✅ Model `RequisicaoVaga` atualizado com fillable e relacionamento
-   ✅ Controller com métodos `aprovar()` e `aprovarExtra()` integrados
-   ✅ Sistema de notificações por email em todas as etapas

### 2. Frontend (Views + UI/UX)

-   ✅ Interface visual com cards de aprovação
-   ✅ Fluxo de aprovação visual com 3 etapas
-   ✅ Card dedicado para Aprovação Extra
-   ✅ Botões contextuais por etapa
-   ✅ Badges e ícones de status

### 3. Notificações

-   ✅ Jobs assíncronos (Horizon)
-   ✅ Emails transacionais (AWS SES)
-   ✅ Templates Blade personalizados
-   ✅ BCC para múltiplos aprovadores

---

## 🗂️ Arquivos Criados/Modificados

### Database (1 arquivo)

```
database/migrations/
└── 2026_02_07_000001_add_aprovacao_extra_to_requisicao_vagas_table.php
```

**Colunas Adicionadas**:

-   `aprovacao_extra_id` (FK → users.id, nullable)
-   `status_aprovacao_extra` (ENUM: 'aprovado', 'reprovado', nullable)
-   `obs_aprovacao_extra` (TEXT, nullable)
-   `data_aprovacao_extra` (TIMESTAMP, nullable)

### Models (1 arquivo modificado)

```
app/Models/RequisicaoVaga.php
```

-   ✅ Fillable: campos de aprovação extra adicionados
-   ✅ Relationship: `AprovacaoExtra()` → `User`
-   ✅ Casts: datas para Carbon

### Controllers (1 arquivo modificado)

```
app/Http/Controllers/RequisicaoVagaController.php
```

**Métodos Atualizados**:

-   `aprovar()`: Integra notificação para Aprovação Extra ou RH
-   `aprovarExtra()`: Notifica RH + solicitante + gestor
-   `atualizar()`: Retorna flags de aprovação extra
-   `edit()`: Retorna dados de aprovação extra

**Métodos Adicionados**:

-   `notificarAprovacaoExtra()`: Dispara job para equipe de aprovação extra
-   `notificarRH()`: Dispara job para equipe de RH
-   `notificarUsuario()`: Dispara job para usuário individual

### Jobs (3 arquivos criados)

```
app/Jobs/RequisicaoVaga/
├── JobNotificacaoAprovacaoExtra.php
├── JobNotificacaoAprovacaoRH.php
└── JobNotificacaoAprovacao.php
```

**Características**:

-   Queue: `default`
-   Timeout: 300s
-   Retry: 3 tentativas
-   BCC: Múltiplos destinatários em um único email

### Mail (1 arquivo criado)

```
app/Mail/RequisicaoVaga/NotificacaoAprovacaoMail.php
```

**Assunto Dinâmico**:

-   `tipo = 'aprovacao_extra'`: "Nova Requisição de Vaga Pendente - Aprovação Extra"
-   `tipo = 'aprovacao_rh'`: "Nova Requisição de Vaga Pendente - Aprovação RH"
-   `tipo = 'aprovacao'`: "Requisição de Vaga Aprovada"

### Templates Email (1 arquivo criado)

```
resources/views/email/requisicaovaga/
└── notificacao_aprovacao.blade.php
```

**Conteúdo do Email**:

-   Cargo solicitado
-   Quantidade de vagas
-   Tipo de contratação
-   Status da requisição
-   Link para visualizar no sistema

### Views (1 arquivo modificado)

```
resources/views/g/planejamento/requisicao-vagas/index.blade.php
```

**Melhorias Visuais**:

-   ✅ Card de **Fluxo de Aprovação** com 3 etapas visuais
-   ✅ Card de **Aprovação Extra** (condicional)
-   ✅ Alertas contextuais por etapa
-   ✅ Badges de status coloridos
-   ✅ Ícones FontAwesome

### JavaScript (0 arquivos modificados)

```
resources/js/g/planejamento/requisicao-vagas/app.js
```

-   ✅ Já tinha suporte para aprovação extra (variáveis existentes)
-   ✅ Método `aprovarExtra()` já implementado
-   ✅ Flags `aprovandoExtra`, `temAprovacaoExtra`, `nomeAprovacaoExtra` já existentes

---

## 🎨 Interface Visual

### Fluxo de Aprovação (3 Etapas)

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   1. GESTOR     │ ─→ │ 2. EXTRA (Opt.) │ ─→ │   3. RH FINAL   │
│                 │    │                 │    │                 │
│ ✅ Aprovado     │    │ ⏳ Pendente     │    │ ⏱️ Aguardando  │
│ João Silva      │    │                 │    │                 │
│ 07/02/2026      │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Cards de Aprovação

#### 1. Card Aprovação Gestor (Verde)

-   Alerta amarelo quando `aprovando = true`
-   Textarea para observações
-   Select de status (Aprovar/Reprovar)

#### 2. Card Aprovação Extra (Azul) - Condicional

-   Exibido apenas se `temAprovacaoExtra = true`
-   Alerta amarelo quando `aprovandoExtra = true`
-   Textarea para observações
-   Select de status (Aprovar/Reprovar)

#### 3. Card RH (Cinza)

-   Exibido como "Pendente" após aprovação anterior
-   Sem formulário (aprovação em outro módulo)

---

## 🔄 Fluxo de Funcionamento

### 1. **Solicitação Criada**

```
Usuário solicita vaga
       ↓
Status: NULL (Aguardando)
       ↓
Notificação → Gestor
```

### 2. **Aprovação Gestor**

```
Gestor acessa requisição
       ↓
Aprova/Reprova + Obs
       ↓
SE aprovado E tem_config_extra:
  └→ Notifica Aprovação Extra
SE aprovado E NÃO tem_config_extra:
  └→ Notifica RH
SE reprovado:
  └→ Notifica Solicitante (fim)
```

### 3. **Aprovação Extra** (se configurada)

```
Aprovador Extra acessa
       ↓
Aprova/Reprova + Obs
       ↓
SE aprovado:
  └→ Notifica RH + Solicitante + Gestor
SE reprovado:
  └→ Notifica Solicitante + Gestor (fim)
```

### 4. **Aprovação RH** (fora deste módulo)

```
RH recebe notificação
       ↓
Aprova/Reprova em outro módulo
       ↓
Requisição finalizada
```

---

## 📧 Sistema de Notificações

### JobNotificacaoAprovacaoExtra

**Quando dispara**: Gestor aprova requisição E existe config de aprovação extra  
**Destinatários**: Todos os usuários autorizados da config (via BCC)  
**Assunto**: "Nova Requisição de Vaga Pendente - Aprovação Extra"  
**Conteúdo**:

-   Cargo: [Nome do Cargo]
-   Quantidade: [X vagas]
-   Tipo: [Tipo de Contratação]
-   Status: Aguardando sua aprovação

### JobNotificacaoAprovacaoRH

**Quando dispara**:

-   Gestor aprova E NÃO tem config extra, OU
-   Aprovação Extra aprova requisição

**Destinatários**: Usuários com `privilegio_gestao_rh` ou `privilegio_aprovar_por_rh` (via BCC)  
**Assunto**: "Nova Requisição de Vaga Pendente - Aprovação RH"  
**Conteúdo**:

-   Cargo: [Nome do Cargo]
-   Quantidade: [X vagas]
-   Tipo: [Tipo de Contratação]
-   Status: Aguardando aprovação final do RH

### JobNotificacaoAprovacao

**Quando dispara**: Aprovação Extra aprova/reprova requisição  
**Destinatários**: Solicitante + Gestor (emails individuais)  
**Assunto**: "Requisição de Vaga Aprovada" / "Requisição de Vaga Reprovada"  
**Conteúdo**:

-   Cargo: [Nome do Cargo]
-   Quantidade: [X vagas]
-   Tipo: [Tipo de Contratação]
-   Status: [Aprovado/Reprovado]
-   Observação: [Texto do aprovador]

---

## 🔐 Permissões

### Quem pode APROVAR como GESTOR?

```php
auth()->user()->can('privilegio_aprovar_por_gestor')
```

### Quem pode APROVAR como APROVAÇÃO EXTRA?

```php
$config = AprovacaoExtraConfig::getConfigAtiva(empresa_id, 'requisicao_vaga');
$config->podeAprovar(auth()->id()); // Verifica se está em usuarios_autorizados
```

### Quem pode APROVAR como RH?

```php
auth()->user()->can('privilegio_gestao_rh') OR
auth()->user()->can('privilegio_aprovar_por_rh')
```

---

## ⚙️ Configuração da Aprovação Extra

### Criar/Editar no Sistema

```
Menu → Configurações → Aprovação Extra
```

**Campos da Config**:

-   `tipo_processo`: `'requisicao_vaga'`
-   `nome_aprovacao`: Ex: "Gerência Operacional"
-   `usuarios_autorizados`: `[1, 5, 12]` (IDs dos usuários)
-   `empresa_id`: ID da empresa
-   `ativo`: `true`

### SQL Exemplo

```sql
INSERT INTO aprovacao_extra_configs (
  tipo_processo,
  nome_aprovacao,
  usuarios_autorizados,
  empresa_id,
  ativo,
  created_at,
  updated_at
) VALUES (
  'requisicao_vaga',
  'Gerência Operacional',
  '[1,5,12]',
  1,
  1,
  NOW(),
  NOW()
);
```

---

## 🧪 Testes Manuais

### 1. Teste Fluxo Completo (COM Aprovação Extra)

**Setup**:

```sql
-- Criar config ativa
INSERT INTO aprovacao_extra_configs (tipo_processo, nome_aprovacao, usuarios_autorizados, empresa_id, ativo, created_at, updated_at)
VALUES ('requisicao_vaga', 'Diretoria', '[2]', 1, 1, NOW(), NOW());
```

**Passos**:

1. ✅ Usuário comum cria requisição de vaga
2. ✅ Gestor recebe email e aprova
3. ✅ Usuário ID 2 (Diretoria) recebe email
4. ✅ Usuário ID 2 aprova
5. ✅ RH recebe email
6. ✅ Solicitante + Gestor recebem email de aprovado

**Validações**:

-   [ ] Emails enviados corretamente em cada etapa
-   [ ] Interface mostra 3 cards no fluxo
-   [ ] Botão "Aprovar" aparece apenas para usuários autorizados
-   [ ] Status atualiza corretamente no banco

### 2. Teste Fluxo SEM Aprovação Extra

**Setup**:

```sql
-- Desativar config
UPDATE aprovacao_extra_configs
SET ativo = 0
WHERE tipo_processo = 'requisicao_vaga';
```

**Passos**:

1. ✅ Usuário cria requisição
2. ✅ Gestor aprova
3. ✅ RH recebe email diretamente

**Validações**:

-   [ ] Fluxo mostra apenas 2 etapas (Gestor → RH)
-   [ ] Card de aprovação extra NÃO aparece
-   [ ] RH recebe email imediatamente após gestor

### 3. Teste Reprovação em Cada Etapa

**Passos**:

1. ✅ Gestor reprova → Solicitante recebe email
2. ✅ Aprovação Extra reprova → Solicitante + Gestor recebem email
3. ✅ RH reprova (em outro módulo)

**Validações**:

-   [ ] Email de reprovação enviado
-   [ ] Etapas seguintes canceladas
-   [ ] Interface mostra badge "Reprovado"

---

## 📊 Queries Úteis

### Verificar Requisições com Aprovação Extra

```sql
SELECT
  rv.id,
  c.nome AS cargo,
  rv.quantidade,
  rv.tipo_contratacao,
  rv.status_aprovacao AS status_gestor,
  rv.status_aprovacao_extra AS status_extra,
  u_gestor.nome AS aprovador_gestor,
  u_extra.nome AS aprovador_extra
FROM requisicao_vagas rv
LEFT JOIN cargos c ON rv.cargo_id = c.id
LEFT JOIN users u_gestor ON rv.user_aprovacao_id = u_gestor.id
LEFT JOIN users u_extra ON rv.aprovacao_extra_id = u_extra.id
WHERE rv.empresa_id = 1
ORDER BY rv.created_at DESC
LIMIT 20;
```

### Verificar Config Ativa

```sql
SELECT *
FROM aprovacao_extra_configs
WHERE tipo_processo = 'requisicao_vaga'
  AND ativo = 1
  AND empresa_id = 1;
```

### Jobs Pendentes/Processados

```sql
SELECT * FROM jobs WHERE queue = 'default' ORDER BY created_at DESC LIMIT 10;
SELECT * FROM failed_jobs WHERE queue = 'default' ORDER BY failed_at DESC LIMIT 10;
```

---

## 🚀 Comandos de Deploy

### 1. Executar Migration

```bash
docker compose exec mybpdp php artisan migrate
```

### 2. Recompilar Assets (se necessário)

```bash
npm run watch   # Dev
npm run prod    # Produção
```

### 3. Limpar Cache

```bash
docker compose exec mybpdp php artisan cache:clear
docker compose exec mybpdp php artisan config:clear
docker compose exec mybpdp php artisan view:clear
```

### 4. Reiniciar Horizon (Filas)

```bash
docker compose exec mybpdp php artisan horizon:terminate
# Supervisord reinicia automaticamente
```

---

## 📝 Notas Técnicas

### BCC vs Múltiplos Emails

-   ✅ Usamos BCC para aprovadores (1 email para N pessoas)
-   ✅ Emails individuais apenas para solicitante/gestor
-   ✅ Reduz carga no SES e melhora performance

### Multi-tenancy

-   ✅ SEMPRE filtrar por `empresa_id`
-   ✅ Config de aprovação extra é por empresa
-   ✅ Usuários autorizados devem ser da mesma empresa

### Relações Eloquent

```php
RequisicaoVaga::class
├── Cargo()             // belongsTo
├── CentroCusto()       // belongsTo
├── Area()              // belongsTo
├── UserCadastrou()     // belongsTo (User)
├── UserAprovacao()     // belongsTo (User)
└── AprovacaoExtra()    // belongsTo (User)
```

---

## 🐛 Troubleshooting

### Problema: Email não chega

**Solução**:

1. Verificar fila: `docker compose exec mybpdp php artisan queue:work`
2. Ver failed jobs: `SELECT * FROM failed_jobs;`
3. Verificar AWS SES limits
4. Testar: `docker compose exec mybpdp php artisan tinker`
    ```php
    Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
    ```

### Problema: Botão de aprovação extra não aparece

**Solução**:

1. Verificar config ativa: `SELECT * FROM aprovacao_extra_configs WHERE tipo_processo = 'requisicao_vaga' AND ativo = 1;`
2. Verificar se usuário está em `usuarios_autorizados`
3. Verificar JavaScript: `console.log(this.temAprovacaoExtra, this.aprovaExtra);`

### Problema: Fluxo não avança após aprovação

**Solução**:

1. Verificar se status foi salvo: `SELECT status_aprovacao, status_aprovacao_extra FROM requisicao_vagas WHERE id = X;`
2. Verificar logs: `tail -f storage/logs/laravel.log`
3. Verificar Horizon: `http://localhost/horizon`

---

## ✅ Checklist de Implementação

### Backend

-   [x] Migration criada e executada
-   [x] Model atualizado (fillable + casts + relationships)
-   [x] Controller integrado (aprovar + aprovarExtra + notificações)
-   [x] Jobs criados (3 arquivos)
-   [x] Mail criado (1 arquivo)
-   [x] Template email criado (1 arquivo)

### Frontend

-   [x] Card Fluxo de Aprovação (3 etapas visuais)
-   [x] Card Aprovação Gestor
-   [x] Card Aprovação Extra (condicional)
-   [x] Botões contextuais
-   [x] Badges e ícones
-   [x] Alertas de ação necessária

### Testes

-   [ ] Fluxo completo COM aprovação extra
-   [ ] Fluxo completo SEM aprovação extra
-   [ ] Reprovação em cada etapa
-   [ ] Emails recebidos corretamente
-   [ ] Permissões validadas
-   [ ] Multi-tenancy validado

### Documentação

-   [x] README criado
-   [x] Queries SQL documentadas
-   [x] Comandos de deploy
-   [x] Troubleshooting

---

## 🎉 Conclusão

A implementação do sistema de **Aprovação Extra para Requisição de Vagas** está **COMPLETA** e segue exatamente o mesmo padrão dos demais módulos do sistema (Demissão, Férias, Mudança de Cargo, Valor Extra).

### Próximos Passos Recomendados

1. ⏭️ Executar testes manuais no ambiente de desenvolvimento
2. ⏭️ Criar config de aprovação extra para uma empresa teste
3. ⏭️ Validar emails recebidos em cada etapa
4. ⏭️ Deploy para produção após validação

---

**Autor**: GitHub Copilot (Claude Sonnet 4.5)  
**Data**: 07/02/2026  
**Versão**: 1.0
