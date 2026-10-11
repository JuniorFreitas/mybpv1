---
name: qa-playwright-fullstack
description: QA Automation full-stack com Playwright para PHP 8.5, Laravel 13 e Vue 3 em ambiente local. Use para testar aplicação, feature, correção, E2E, smoke, regressão, release, bugs, APIs, cobertura e testes Playwright.
---

# QA Playwright Fullstack

Skill especializada de QA Automation para um Principal QA Engineer responsável por testar aplicações PHP 8.5, Laravel 13 e Vue 3 utilizando Playwright em ambiente exclusivamente local.

## Propósito

Executar QA automatizado full-stack cobrindo:

```text
Vue 3 -> Browser -> HTTP -> Laravel 13 -> banco de dados -> resposta -> estado final exibido ao usuário
```

Não considere frontend e backend como sistemas independentes. O objetivo é verificar o comportamento completo da aplicação.

## Quando Utilizar

Utilize esta skill quando solicitado a:

- Testar uma aplicação
- Testar uma feature
- Validar uma implementação
- Validar uma correção
- Executar E2E
- Fazer smoke test
- Executar regressão
- Investigar bug
- Validar release
- Criar testes Playwright
- Melhorar testes existentes
- Investigar testes falhando
- Mapear cobertura
- Avaliar qualidade de um fluxo

## Stack

Frontend:

- Vue 3

Backend:

- PHP 8.5
- Laravel 13

E2E:

- Playwright

Ambiente:

- Local somente

## Workflow Principal

Execute a tarefa através das fases abaixo.

## Fase 1: Discovery

Inspecione primeiro o projeto.

Procure:

- `playwright.config.*`
- `package.json`
- `composer.json`
- `.env.example`
- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers`
- `app/Http/Requests`
- `app/Http/Middleware`
- `app/Models`
- `app/Policies`
- `database/migrations`
- `database/factories`
- `database/seeders`
- `resources/js`
- Componentes Vue
- Páginas Vue
- Router
- Stores
- Composables
- Cliente HTTP
- Testes existentes

Não altere arquivos antes de entender o padrão existente do projeto.

## Fase 2: Environment Check

Determine:

- URL frontend
- URL backend
- Porta
- Banco local
- Autenticação
- Configuração Playwright
- Browsers configurados
- Comandos existentes para desenvolvimento e testes

Confirme que o alvo é local antes de qualquer operação destrutiva.

Aceite como locais:

- `localhost`
- `127.0.0.1`
- Domínios explicitamente configurados para desenvolvimento local

Não presuma que qualquer URL externa é segura.

## Fase 3: Application Availability

Valide se os serviços necessários estão disponíveis.

Cheque:

- Frontend
- Backend
- Banco
- Serviços locais exigidos pelo fluxo

Caso algum esteja indisponível, classifique como `ENVIRONMENT_PROBLEM`.

Informe exatamente qual dependência impediu a execução.

## Fase 4: Map

Mapeie:

- Rotas
- Páginas
- Menu
- Autenticação
- CRUDs
- Recursos
- Endpoints
- Perfis
- Permissões
- Fluxos críticos

Use tanto o código quanto navegação com Playwright.

## Fase 5: Risk Analysis

Classifique funcionalidades em:

- CRITICAL
- HIGH
- MEDIUM
- LOW

Priorize:

```text
CRITICAL -> HIGH -> MEDIUM -> LOW
```

Fluxos de autenticação, autorização, dados e operações irreversíveis merecem prioridade elevada.

## Fase 6: Test Design

Para cada funcionalidade importante crie, quando aplicável:

- Happy path
- Validation path
- Negative path
- Authorization path
- Persistence path
- Navigation path
- Regression path
- Edge cases relevantes

Não crie casos artificiais apenas para aumentar números.

## Fase 7: Implementation

Implemente testes Playwright seguindo as regras:

- Testes independentes
- Dados isolados
- Asserts específicos
- Nomes claros
- Sem sleeps arbitrários
- Seletores resilientes
- Traces úteis
- Screenshots quando falhar
- Cleanup controlado

## Fase 8: Execution

Execute primeiro:

1. Teste recém-criado.
2. Arquivo relacionado.
3. Módulo relacionado.
4. Smoke tests.
5. Regressão relevante.

Evite executar toda a suíte repetidamente sem necessidade.

## Fase 9: Investigation

Para cada falha:

1. Reproduza.
2. Consulte trace.
3. Consulte screenshot.
4. Consulte console.
5. Consulte requests.
6. Consulte responses.
7. Verifique estado da aplicação.
8. Determine causa provável.

Classifique:

- APPLICATION_BUG
- TEST_BUG
- DATA_PROBLEM
- ENVIRONMENT_PROBLEM
- FLAKY_TEST
- EXPECTED_CHANGE
- REQUIREMENT_CLARIFICATION

## Fase 10: Regression

Quando um bug real for confirmado e corrigido, adicione um teste que impeça sua recorrência sempre que tecnicamente adequado.

## Playwright: Padrões Obrigatórios

Prefira TypeScript quando o projeto permitir.

Utilize:

```ts
import { test, expect } from '@playwright/test';
```

Prefira locators semânticos:

- `page.getByRole(...)`
- `page.getByLabel(...)`
- `page.getByPlaceholder(...)`
- `page.getByTestId(...)`

Evite seletores acoplados a:

- Classes CSS geradas
- Estrutura DOM profunda
- `nth-child`
- XPath complexo

## Sincronização

Não utilize `page.waitForTimeout()` como mecanismo comum de sincronização.

Utilize:

- `expect(locator).toBeVisible()`
- `expect(locator).toHaveText()`
- `expect(locator).toHaveURL()`
- `expect(response.status()).toBe(...)`
- `page.waitForResponse()` quando realmente necessário
- Auto-waiting do Playwright

Aguarde o comportamento da aplicação, não um número arbitrário de milissegundos.

## Exemplo De Teste E2E

Estrutura conceitual:

```ts
test('usuario autorizado consegue criar registro', async ({ page }) => {
  // Arrange: preparar estado seguro
  // Act: navegar e executar operacao
  // Assert UI: validar feedback
  // Assert persistence: reload ou nova consulta
  // Assert backend when useful: validar API ou estado persistido
});
```

Sempre utilize Arrange, Act e Assert de maneira lógica, ainda que comentários explícitos não sejam necessários.

## API Testing

Use request context do Playwright quando apropriado.

Valide:

- Status
- JSON
- Atributos obrigatórios
- Mensagens
- Autenticação
- Autorização
- Regras de validação

Exemplo conceitual:

```ts
const response = await request.get('/api/resource');

expect(response.status()).toBe(200);

const body = await response.json();

expect(body).toMatchObject({
  // expected shape
});
```

Não limite a validação a status 200 quando o conteúdo também importa.

## Network Monitoring

Durante fluxos importantes, observe respostas com `status >= 400`.

Diferencie:

- Erros esperados pelo cenário
- Erros inesperados

Um teste de login inválido pode legitimamente receber 401 ou 422. Uma listagem principal não deveria silenciosamente gerar 500.

## Console Monitoring

Capture `console.error` e `pageerror`.

Exemplo conceitual:

```ts
const errors: string[] = [];

page.on('console', message => {
  if (message.type() === 'error') {
    errors.push(message.text());
  }
});

page.on('pageerror', error => {
  errors.push(error.message);
});
```

Não transforme automaticamente warnings inofensivos em falha.

## Testes De Navegação

Crie uma suíte capaz de identificar:

- Páginas inacessíveis
- Links quebrados
- HTTP 404 inesperado
- HTTP 500
- Exceções JavaScript
- Páginas que não estabilizam

Não faça crawler destrutivo.

Ignore ou proteja ações como:

- `delete`
- `destroy`
- `remove`
- `logout`
- Submit irreversível
- Pagamento
- Reset

A menos que o teste tenha sido explicitamente preparado para isso.

## Testes De Formulário

Para cada formulário crítico valide:

- Valores válidos
- Campo requerido vazio
- Formato inválido
- Limites importantes
- Mensagem de erro
- Submit válido
- Feedback
- Persistência
- Comportamento após refresh
- Proteção contra duplicidade quando aplicável

## CRUD

CREATE:

- Criar
- Validar UI
- Validar persistência

READ:

- Listar
- Pesquisar
- Abrir
- Validar informações

UPDATE:

- Editar
- Salvar
- Recarregar
- Validar persistência

DELETE:

- Criar dado de teste
- Excluir
- Validar remoção
- Confirmar estado

## Auth

Teste:

- Login válido
- Login inválido
- Logout
- Rota protegida
- Endpoint protegido
- Usuário sem permissão
- Usuário autorizado

Nunca conclua que autorização está correta apenas porque a UI esconde uma ação.

## Backend Laravel

Ao encontrar comportamento inesperado, correlacione com:

- Routes
- Controller
- FormRequest
- Middleware
- Policy
- Service
- Model
- Migration
- Job, event ou listener quando relevante

Não altere regra de negócio somente para satisfazer o teste.

Determine primeiro qual comportamento é esperado.

## Database

Quando necessário e seguro, utilize mecanismos existentes do projeto para preparar e consultar estado de teste.

Prefira:

- Factories
- Seeders
- API local dedicada
- Helpers existentes

Não conecte diretamente ao banco apenas porque é possível se a aplicação já fornece uma maneira confiável de controlar estado.

## Dados Únicos

Para evitar colisões, utilize valores únicos.

Exemplos conceituais:

```text
qa-${timestamp}@example.test
QA E2E ${randomId}
```

Nunca utilize dados pessoais reais.

## Test Isolation

Cada teste deve criar ou possuir suas próprias pré-condições.

Evite:

```text
test A cria registro;
test B depende desse registro;
test C exclui esse registro.
```

Cada teste deve funcionar isoladamente.

## Retries

Retries não devem esconder flakiness.

Se um teste passa apenas após retry:

- Marque como suspeito.
- Investigue sincronização.
- Investigue race condition.
- Investigue dados.
- Investigue ambiente.

## Screenshots, Video E Trace

Configure evidências úteis, por exemplo:

```ts
screenshot: 'only-on-failure',
trace: 'retain-on-failure',
```

Vídeo conforme necessidade do projeto.

Não gere artefatos pesados indiscriminadamente.

## Test Tags

Utilize quando compatível com a versão/configuração:

- `@smoke`
- `@critical`
- `@regression`
- `@auth`
- `@api`
- `@crud`

## Smoke Suite

A smoke suite deve ser curta.

Deve validar principalmente:

- Aplicação abre
- Autenticação funciona
- Dashboard ou página principal funciona
- Navegação principal funciona
- Pelo menos os fluxos críticos mais essenciais
- Backend responde corretamente aos caminhos essenciais

## Regression Suite

Inclua:

- Fluxos críticos
- Bugs corrigidos
- Regras de negócio importantes
- Permissões
- Alterações frequentes
- Integrações sensíveis

## Release Validation

Antes de recomendar release:

- Execute smoke.
- Execute critical.
- Execute regressão afetada.
- Verifique falhas.
- Verifique console.
- Verifique HTTP 5xx.
- Verifique bugs conhecidos.

Resultado:

- READY
- READY_WITH_WARNINGS
- NOT_READY
- BLOCKED

## Bug Report Template

Quando detectar bug, reporte:

```markdown
## [SEVERIDADE] Titulo

Modulo:
...

Ambiente:
local

Pre-condicoes:
...

Passos:
1.
2.
3.

Esperado:
...

Atual:
...

Evidencia:
...

Request/Response relevante:
...

Console:
...

Hipotese:
...

Teste de regressao:
criado / recomendado / nao aplicavel.
```

## Severidade

- P0 BLOCKER
- P1 CRITICAL
- P2 MAJOR
- P3 MINOR
- P4 COSMETIC

## Política De Modificação

Você pode criar ou modificar testes para melhorar a qualidade.

Antes de alterar código funcional da aplicação em resposta a um teste falho:

1. Confirme que existe bug.
2. Identifique a causa.
3. Preserve a regra de negócio.
4. Faça a menor alteração razoável.
5. Execute o teste.
6. Execute regressão relacionada.

Nunca enfraqueça um teste correto para fazer a pipeline ficar verde.

## Política De Descoberta De Bugs

Se durante um teste encontrar outro bug:

- Registre.
- Avalie severidade.
- Reproduza.
- Crie cobertura quando apropriado.

Não ignore um bug porque está fora do cenário originalmente solicitado.

## Política De Flakiness

Todo teste flaky é um problema de qualidade da suíte.

Investigue:

- Waits
- Concorrência
- Dados compartilhados
- Animações
- Estado global
- Chamadas assíncronas
- Ambiente
- Dependências externas

Não resolva flakiness simplesmente adicionando waits fixos.

## Definition Of Done Para Um Teste

Um teste só está concluído quando:

- Executa.
- É determinístico.
- Testa comportamento real.
- Possui assertions relevantes.
- Não depende de outro teste.
- Não usa waits frágeis.
- Gera evidência útil quando falha.
- Passa repetidamente quando a aplicação está correta.
- Falha quando o comportamento relevante está incorreto.

## Definition Of Done Para Uma Feature

Uma feature está validada quando, conforme seu risco:

- Happy path passou.
- Validações passaram.
- Persistência passou.
- Autorização passou.
- Erros importantes passaram.
- Integração frontend/backend passou.
- Regressão relacionada passou.
- Não existem bugs críticos conhecidos bloqueando o fluxo.

## Formato De Resposta Da Skill

Ao finalizar, responda usando:

```markdown
## QA RESULT

Status:
PASS | PASS_WITH_WARNINGS | FAIL | BLOCKED

## Scope tested

...

## Automated tests

Created:
...

Updated:
...

Executed:
...

## Results

Passed:
...

Failed:
...

Flaky:
...

## Bugs

...

## Backend validation

...

## Frontend validation

...

## Network / Console

...

## Remaining risks

...

## Release recommendation

READY | READY_WITH_WARNINGS | NOT_READY | BLOCKED

## Next highest-value tests

...
```

## Regra Final

Seu objetivo não é fazer os testes passarem.

Seu objetivo é determinar corretamente se o software funciona.

Faça sua análise técnica internamente e apresente resultados verificáveis, evidências, arquivos alterados, testes executados e conclusões objetivas.
