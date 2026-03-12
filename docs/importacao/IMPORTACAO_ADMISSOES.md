# Importação de Admissões – Especificação técnica e de uso

Este documento descreve o fluxo, a arquitetura e o uso da importação de colaboradores (admissões) via planilha Excel no MyBP.

---

## 1. Objetivo

Permitir que o cliente importe colaboradores em lote a partir de uma planilha Excel (.xlsx), com validação campo a campo, processamento em background (sem travar a interface) e relatório de erros para correção. A importação **nunca duplica** um colaborador na mesma empresa: se o CPF já existir, os dados são **atualizados**; se não existir, são **criados**.

**Público:** RH ou operadores que precisam cadastrar ou atualizar muitos colaboradores de uma vez.

---

## 2. Pré-requisitos

- Planilha no formato do guia (aba **"Dados"**, primeira linha = cabeçalho com nomes das colunas).
- **cod_vaga**, **cod_area** e **centro_custo**: podem ser preenchidos por **código** (número) ou por **nome** (importação inicial). Se usar nome e o registro não existir, o sistema pode criar (centro de custo) ou retornar erro (vaga/área — conforme configuração).
- Arquivo .xlsx válido; tamanho máximo definido na configuração do upload (recomendado: ex.: 10–20 MB).

---

## 3. Formas de uso

### 3.1 Upload pela interface

1. Acesse a tela de importação de admissões pelo menu **Admissão > IMPORTAÇÃO** (visível apenas para usuários com a habilidade **admissao_importacao** ativa).
2. Faça o upload do arquivo .xlsx (arrastar ou selecionar).
3. O sistema valida o arquivo (formato, tamanho) e envia para processamento em **background**.
4. Você recebe uma mensagem imediata: **"Importação enfileirada. Você será notificado ao concluir."**
5. Ao terminar o processamento:
   - Um **e-mail** é enviado com o resumo (X linhas com sucesso, Y com erro) e link para **baixar o relatório de erros** (se houver).
   - Na interface, se existir tela de "Importações" ou notificações, aparecerá o resultado e um botão **"Baixar relatório de erros"** para abrir o arquivo com os detalhes por linha/campo.

O frontend é em **Vue 3**, com componentes no padrão do projeto; todas as chamadas ao backend (upload, status, download do relatório) usam **async/await** com axios.

### 3.2 Command (linha de comando)

Para processar um arquivo já salvo no servidor (ex.: enviado por FTP ou gerado por outro sistema):

```bash
php artisan mybp:importar-admissoes {arquivo} {empresa_id} [--user_id=] [--chunk=100] [--relatorio=]
```

- **arquivo**: caminho relativo a `storage/app/` ou caminho absoluto do .xlsx.
- **empresa_id**: ID da empresa para a qual importar.
- **--user_id** (opcional): ID do usuário responsável (logs/auditoria).
- **--chunk** (opcional): tamanho do lote de linhas (default 100).
- **--relatorio** (opcional): caminho do arquivo CSV/Excel de resultado (linhas com erro e/ou sucesso).

Exemplo:

```bash
php artisan mybp:importar-admissoes importacao_admissoes/empresa_1/planilha.xlsx 1 --relatorio=storage/app/relatorio_importacao.csv
```

Ao final, o terminal exibe um resumo: total processado, sucesso, erros e caminho do relatório (se informado).

---

## 3.3 Menu e habilidade (permissão)

- **Menu:** o item **IMPORTAÇÃO** fica no menu lateral em **Admissão**, entre PROCESSO e HISTÓRICO. O menu **Admissão** só é exibido se o usuário tiver ao menos uma das habilidades do módulo (incluindo `admissao_importacao`).
- **Habilidade:** para acessar a tela e usar o upload, o usuário precisa da habilidade **`admissao_importacao`** (“Acessar rota/menu Importação de Admissões”). Ela é cadastrada pelo seeder `HabilidadesTableSeeder` e pode ser atribuída a papéis (grupos) em **Configurações > Grupos de Usuários**.
- **Rotas protegidas:** `GET /g/admissao/import` (exibir tela) e `POST /g/admissao/import` (upload) usam o middleware `can:admissao_importacao`; sem a habilidade o acesso é negado.

---

## 4. Estrutura da planilha

- **Aba utilizada:** "Dados" (a primeira linha deve ser o cabeçalho; não alterar).
- **A partir da linha 2:** cada linha = um colaborador.

### 4.1 Colunas obrigatórias (resumo)

| Campo             | Descrição                                      |
|-------------------|------------------------------------------------|
| cpf*              | CPF (11 dígitos ou 000.000.000-00), válido     |
| nome*             | Nome completo                                  |
| cep*              | CEP (8 dígitos ou 00000-000)                   |
| endereco*         | Logradouro                                     |
| numero*           | Número ou S/N                                  |
| bairro*           | Bairro                                         |
| municipio*        | Cidade                                         |
| uf*               | UF (2 letras)                                  |
| telefone_numero*  | Telefone com DDD                               |
| cod_vaga*         | Código ou nome da vaga                         |
| centro_custo*     | Código ou nome do centro de custo              |
| tipo_admissao*    | FIXO, TEMPORARIO, INTERMITENTE, DETERMINADO, PJ, ESTÁGIO, APRENDIZ |
| data_admissao*    | Data de admissão (dd/mm/aaaa)                 |
| data_aso*         | Data do ASO (dd/mm/aaaa)                      |

### 4.2 Regras por tipo de admissão

| Tipo                          | Campo adicional obrigatório     |
|-------------------------------|----------------------------------|
| FIXO                          | **prazo_experiencia** (Nenhum, 30+15, 30+30, 45+45, 30+60, 60+30) |
| TEMPORARIO, INTERMITENTE, DETERMINADO | **admissao_encerramento** (data fim do contrato, dd/mm/aaaa) |
| PJ, ESTÁGIO, APRENDIZ         | Nenhum                          |

### 4.3 Outras colunas (opcionais ou condicionais)

- naturalidade, email, cnh (SIM/NAO), cnh_vencimento, rg, rg_emissao, nascimento, sexo, estado_civil, pai, mae, pcd (SIM/NAO), cid (obrigatório se pcd = SIM), complemento, whatsapp (SIM/NAO), cod_area, vaga_mun, data_entrega_area, salario, pis, ctps_*, titulo_eleitor_*, banco, agencia, conta, pix (SIM/NAO), pix_tipo_chave, pix_chave (obrigatórios se pix = SIM), encaminhado_documento/exame/treinamento e datas, numero_cracha, matricula.

Listas permitidas (ex.: cnh, whatsapp, pix): **SIM**, **NAO**. Sexo: **MASCULINO**, **FEMININO** (ou M, F). Datas: sempre **dd/mm/aaaa**.

---

## 5. Comportamento (regra de negócio)

- **Unicidade:** um CPF identifica no máximo um colaborador por empresa.
- **Se o CPF já existe na empresa:** todos os dados são **atualizados** com os valores da planilha (User, Curriculo, endereço, telefone, admissão, dados admissão, ASO, etc.). Não gera erro.
- **Se o CPF não existe:** o colaborador é **criado** (User, Curriculo, Telefone, Feedback, Admissão, etc.).
- **Mesmo CPF em várias linhas da planilha:** as linhas são processadas em ordem; cada linha **atualiza** o mesmo colaborador (a última linha com aquele CPF prevalece para os campos que ela informar). Não é tratado como erro.

---

## 6. Erros e relatório

### 6.1 Tipos de erro

- **Validação:** campo obrigatório vazio, formato inválido (ex.: data, CPF), valor fora da lista, regra condicional não atendida (ex.: FIXO sem prazo_experiencia).
- **Resolução:** cod_vaga, cod_area ou centro_custo não encontrado (nem por ID nem por nome).
- **Persistência:** falha de banco de dados ou exceção ao criar/atualizar (constraint, timeout, etc.).

### 6.2 Formato do relatório

Arquivo CSV ou Excel com as colunas:

| Coluna          | Descrição                                                |
|-----------------|----------------------------------------------------------|
| linha_planilha  | Número da linha no Excel (2 = primeira linha de dados)  |
| cpf_planilha    | CPF mascarado (ex.: ***.***.***-12)                      |
| status          | sucesso \| erro_validacao \| erro_resolucao \| erro_persistencia |
| campo           | Nome da coluna com problema                              |
| mensagem        | Texto curto do erro                                      |
| como_corrigir   | Texto acionável para o cliente                          |

Pode haver mais de uma linha no relatório por linha da planilha (uma por campo com erro). Linhas com sucesso podem ser omitidas ou listadas com status "sucesso" para auditoria.

### 6.3 Como o cliente é notificado

- **Upload:** resposta HTTP imediata; e-mail ao concluir o job (resumo + link para download do relatório); opcionalmente tela com botão "Baixar relatório de erros".
- **Command:** resumo no terminal; arquivo de relatório no path informado em `--relatorio`.

Mensagens acionáveis (ex.: "Use 11 dígitos ou formato 000.000.000-00", "Use data no formato dd/mm/aaaa") ficam centralizadas em `lang/pt_BR/importacao_admissao.php` ou no serviço de importação.

---

## 7. Arquitetura (resumo)

| Componente                    | Responsabilidade |
|------------------------------|------------------|
| **LeitorPlanilhaAdmissao**   | Ler o .xlsx em chunks (aba "Dados"), converter datas Excel para dd/mm/aaaa, expor linhas como array associativo. |
| **ValidadorLinhaPlanilhaAdmissao** | Validar uma linha (obrigatórios, formato, listas, regras condicionais); retornar erros por campo (campo, mensagem, como_corrigir). |
| **ResolvedorVagaAreaCentroCusto**  | Dado empresa_id e valor (código ou nome), retornar ID de vaga, área e centro de custo (ou erro). |
| **PersistidorAdmissaoImportada**  | Receber linha validada e IDs resolvidos; montar payload curriculo+admissao; criar ou atualizar User, Curriculo, Telefone, Feedback, Admissão, DadosAdmissao, ASO, etc. Uma transação por linha. |
| **ImportarAdmissoesCommand** | Orquestrar: ler em chunks → validar → resolver → persistir; gerar relatório; exibir resumo no terminal. |
| **ImportacaoAdmissaoJob**    | Mesmo fluxo do command para arquivo em storage; ao final, gerar relatório e notificar (e-mail, etc.). |

**Onde fica o código:**

- Serviços: `app/Services/Admissao/Importacao/`
- Command: `app/Console/Commands/ImportarAdmissoesCommand.php`
- Job: `app/Jobs/Admissao/Importacao/ImportacaoAdmissaoJob.php`
- Controller: `app/Http/Controllers/AdmissaoController.php` (método `import()`)
- Frontend: componentes Vue 3 em `resources/js/...` (upload, status, download do relatório)

**Performance e lock:** A importação é otimizada e não trava tabelas: leitura em chunks, transação **por linha** (ou mini-lote de poucas linhas), sem transação única para todo o arquivo e sem `SELECT ... FOR UPDATE` desnecessário.

---

## 8. Testes

- Testes unitários em `tests/Unit/Services/Admissao/Importacao/` (Leitor, Validador, Resolvedor, Persistidor), `tests/Unit/Console/`, `tests/Unit/Jobs/Admissao/Importacao/`.
- Fixtures (planilhas de teste): `tests/fixtures/planilha_valida.xlsx`, `planilha_com_erros.xlsx`.

Para rodar os testes:

```bash
php artisan test
# ou
./vendor/bin/phpunit
```

---

## 9. Referências

- **Guia do usuário (PDF):** [Guia – Importação de Admissões.pdf](Guia%20–%20Importação%20de%20Admissões.pdf)
- **Planilha exemplo:** [importacao_exemplo.xlsx](importacao_exemplo.xlsx)
- **Padrões do projeto:** [AGENTS.md](../../AGENTS.md) (seções 9 Frontend, 10 Banco de Dados, 16 Fluxo de mudanças)
- **Migração frontend (Vue 3 / Composition API):** [docs/PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md](../PLANO_MIGRACAO_COMPOSITION_API_SERVICES.md)
