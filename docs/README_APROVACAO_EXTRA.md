# Sistema de Aprovação Extra Dinâmica

## Visão Geral

Este sistema permite configurar aprovações extras personalizadas para diferentes processos de movimentação de pessoal, de forma dinâmica e configurável por empresa.

## Funcionalidades

### 1. Configuração por Empresa e Processo

Cada empresa pode configurar aprovações extras específicas para diferentes tipos de processos:

-   **Demissão**: Ex: SESMT, Gerente de Área, Diretor
-   **Férias**: Ex: Supervisor, Coordenador
-   **Mudança de Cargo**: Ex: Gerente RH, Diretor
-   **Transferência**: Ex: Gestor da área destino
-   **Intermitente para Fixo**: Ex: Jurídico, Contabilidade
-   **Valor Extra**: Ex: Financeiro, Diretor

### 2. Nomenclatura Personalizada

Cada empresa pode nomear sua aprovação extra conforme sua necessidade:

-   Empresa A pode chamar de "SESMT" para demissões
-   Empresa B pode chamar de "Supervisor" para demissões
-   Empresa C pode chamar de "Gerente de Área" para demissões

## Estrutura do Banco de Dados

### Tabela: `aprovacao_extra_configs`

```sql
- id (bigint, PK)
- empresa_id (bigint, FK -> clientes)
- tipo_processo (enum: demissao, ferias, mudanca_cargo, transferencia, intermitente_fixo, valor_extra)
- nome_aprovacao (string) - Ex: "SESMT", "Supervisor", "Gerente"
- ativo (boolean)
- created_at
- updated_at
```

### Campos Adicionados nas Tabelas de Processos

#### `demissao_previstas` e `ferias_previstas`:

```sql
- aprovacao_extra_id (bigint, FK -> users) - Quem aprovou
- status_aprovacao_extra (string) - "aprovado" ou "reprovado"
- obs_aprovacao_extra (text) - Observações do aprovador
- data_aprovacao_extra (timestamp) - Data/hora da aprovação
```

## Como Usar

### 1. Configurar Aprovação Extra

```php
// Criar uma configuração de aprovação extra
AprovacaoExtraConfig::create([
    'empresa_id' => 1,
    'tipo_processo' => 'demissao',
    'nome_aprovacao' => 'SESMT',
    'ativo' => true
]);
```

### 2. Verificar se Existe Aprovação Extra

```php
$empresaId = auth()->user()->empresa_id;
$config = AprovacaoExtraConfig::getConfigAtiva($empresaId, 'demissao');

if ($config) {
    // Mostrar campo de aprovação extra na interface
    // com o nome configurado: $config->nome_aprovacao
}
```

### 3. Registrar Aprovação Extra

```php
$demissao = DemissaoPrevista::find($id);
$demissao->update([
    'aprovacao_extra_id' => $aprovadorId,
    'status_aprovacao_extra' => 'aprovado', // ou 'reprovado'
    'obs_aprovacao_extra' => 'Aprovado pelo SESMT',
    'data_aprovacao_extra' => now()
]);
```

### 4. Consultar Aprovação Extra

```php
$demissao = DemissaoPrevista::with('AprovacaoExtra')->find($id);

if ($demissao->aprovacao_extra_id) {
    $aprovador = $demissao->AprovacaoExtra; // Relacionamento com User
    $nomeAprovador = $aprovador->nome;
    $status = $demissao->status_aprovacao_extra;
}
```

## API Endpoints

### Controller: `AprovacaoExtraConfigController`

#### Listar Configurações

```
GET /g/administracao/aprovacao-extra-config/listar
```

#### Buscar por Tipo

```
POST /g/administracao/aprovacao-extra-config/buscar-por-tipo
Body: { "tipo_processo": "demissao" }
```

#### Criar Configuração

```
POST /g/administracao/aprovacao-extra-config
Body: {
    "tipo_processo": "demissao",
    "nome_aprovacao": "SESMT",
    "ativo": true
}
```

#### Atualizar Configuração

```
PUT /g/administracao/aprovacao-extra-config/{id}
Body: {
    "nome_aprovacao": "SESMT Revisado",
    "ativo": true
}
```

#### Deletar Configuração

```
DELETE /g/administracao/aprovacao-extra-config/{id}
```

#### Ativar/Desativar

```
POST /g/administracao/aprovacao-extra-config/{id}/toggle-ativo
```

#### Tipos Disponíveis

```
GET /g/administracao/aprovacao-extra-config/tipos-processo
```

## Models

### AprovacaoExtraConfig

```php
// Tipos de processo disponíveis
const TIPO_DEMISSAO = 'demissao';
const TIPO_FERIAS = 'ferias';
const TIPO_MUDANCA_CARGO = 'mudanca_cargo';
const TIPO_TRANSFERENCIA = 'transferencia';
const TIPO_INTERMITENTE_FIXO = 'intermitente_fixo';
const TIPO_VALOR_EXTRA = 'valor_extra';

// Buscar configuração ativa
AprovacaoExtraConfig::getConfigAtiva($empresaId, $tipoProcesso);

// Scopes
->ativo() // Apenas configurações ativas
->tipoProcesso($tipo) // Filtrar por tipo
```

### DemissaoPrevista / FeriasPrevista

```php
// Relacionamento
$demissao->AprovacaoExtra; // Retorna o User que aprovou

// Constantes de status
DemissaoPrevista::STATUS_APROVADO = 'aprovado';
DemissaoPrevista::STATUS_REPROVADO = 'reprovado';
```

## Fluxo de Trabalho

1. **Configuração Inicial** (Administrador)

    - Acessa painel de configurações
    - Define quais processos terão aprovação extra
    - Nomeia cada tipo de aprovação (ex: "SESMT" para demissão)
    - **Seleciona usuários autorizados** a aprovar

2. **Solicitação** (Solicitante)

    - Cria solicitação de demissão/férias
    - Sistema verifica se existe aprovação extra configurada
    - Se sim, adiciona etapa extra no fluxo

3. **Aprovação Gestor** (Gestor)

    - Aprova ou reprova como de costume

4. **Aprovação Extra** (Aprovador Extra - ex: SESMT) ⚠️ **ANTES DO RH**

    - Recebe notificação
    - Verifica se tem permissão (usuário autorizado OU privilegio_rh)
    - Aprova ou reprova
    - Adiciona observações

5. **Aprovação RH** (RH) ⚠️ **SEMPRE A ÚLTIMA**

    - Aguarda aprovação extra (se houver)
    - Aprova ou reprova
    - Processo concluído

6. **Conclusão**
    - Processo só é concluído após **RH aprovar (sempre por último)**

## Vantagens

✅ **Flexível**: Cada empresa configura conforme sua necessidade
✅ **Dinâmico**: Não precisa alterar código para adicionar novos aprovadores
✅ **Escalável**: Fácil adicionar novos tipos de processos
✅ **Rastreável**: Mantém histórico completo de quem aprovou e quando
✅ **Personalizável**: Nome da aprovação pode ser qualquer coisa

## Próximos Passos para Implementação

1. ✅ Executar migrations: `php artisan migrate`
2. ⏳ Adicionar rotas no `web.php` ou `api.php`
3. ⏳ Criar interface Vue.js para configuração
4. ⏳ Atualizar controllers de Demissão e Férias para usar aprovação extra
5. ⏳ Adicionar notificações para aprovadores extras
6. ⏳ Atualizar relatórios para incluir aprovação extra
7. ⏳ Criar permissões/habilidades para aprovação extra

## Exemplo de Uso Real

### Cenário 1: Empresa A - Demissão precisa de aprovação SESMT

```php
// 1. Configuração (uma vez)
AprovacaoExtraConfig::create([
    'empresa_id' => 1,
    'tipo_processo' => 'demissao',
    'nome_aprovacao' => 'SESMT',
    'ativo' => true
]);

// 2. Na criação da demissão
$config = AprovacaoExtraConfig::getConfigAtiva(1, 'demissao');
// Interface mostra: "Aguardando aprovação do SESMT"

// 3. SESMT aprova
$demissao->update([
    'aprovacao_extra_id' => $sesmtUserId,
    'status_aprovacao_extra' => 'aprovado',
    'obs_aprovacao_extra' => 'Documentação médica OK',
    'data_aprovacao_extra' => now()
]);
```

### Cenário 2: Empresa B - Férias precisa de aprovação do Supervisor

```php
// 1. Configuração (uma vez)
AprovacaoExtraConfig::create([
    'empresa_id' => 2,
    'tipo_processo' => 'ferias',
    'nome_aprovacao' => 'Supervisor',
    'ativo' => true
]);

// Interface mostra: "Aguardando aprovação do Supervisor"
```

## Observações Importantes

-   Apenas UMA configuração pode estar ativa por tipo de processo por empresa
-   Se não houver configuração ativa, o processo funciona como antes (sem aprovação extra)
-   Os campos de aprovação extra são nullable, então não quebra registros antigos
-   O relacionamento `AprovacaoExtra` retorna o usuário que aprovou
