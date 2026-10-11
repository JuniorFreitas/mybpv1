# Importação de Treinamentos – Manual de uso

Guia para importar treinamentos em lote via planilha Excel no MyBP.

---

## 1. Objetivo

Registrar ou atualizar treinamentos de colaboradores a partir de uma planilha `.xlsx`, com processamento em segundo plano, notificação no sino, e-mail e relatório na aba **Importados** (CSV de sucesso e de erro).

**Público:** usuários de RH/treinamento com a habilidade `treinamento_importacao`.

---

## 2. Acesso

1. Menu **Treinamento > Importação** (ou rota `/g/treinamento/import`).
2. A tela tem duas abas:
   - **Importação** — baixar modelo, ler o manual e enviar a planilha.
   - **Importados** — histórico com quantidades e download dos CSVs.

---

## 3. Passo a passo

1. Na aba **Importação**, clique em **Baixar modelo Excel**.
2. Abra o arquivo e use a aba **Dados** (não altere os cabeçalhos).
3. Em cada linha (a partir da 2):
   - **colaborador** — escolha no dropdown (formato `feedback_id|cpf|nome|cargo`).
   - **treinamento** — escolha no dropdown (formato `vencimento_id|contrato|label`).
   - **data_treinamento** — preencha a data no formato abaixo.
4. Salve o `.xlsx` e envie pela dropzone da tela.
5. Acompanhe o resultado na aba **Importados**, no sino e no e-mail.

Tamanho máximo recomendado do arquivo: **20 MB**.

---

## 4. Formato da data (`data_treinamento`)

| Regra | Detalhe |
|-------|---------|
| Formato obrigatório | **dd/mm/aaaa** |
| Exemplo válido | `15/03/2026` |
| Separador | barra `/` (não use ponto nem hífen na digitação) |
| Dia e mês | sempre com 2 dígitos (`01/05/2026`, não `1/5/2026`) |
| Ano | 4 dígitos |

### O que o sistema aceita

- Texto digitado como **dd/mm/aaaa** (recomendado).
- Célula de data do Excel formatada como data (o sistema converte para dd/mm/aaaa).
- Valor ISO `aaaa-mm-dd` (convertido internamente para dd/mm/aaaa).

### O que evitar

- `15-03-2026` ou `15.03.2026`
- `3/15/2026` (padrão americano)
- `15/03/26` (ano com 2 dígitos)
- Célula vazia quando a linha tem colaborador/treinamento

No modelo Excel, a coluna **data_treinamento** exibe uma mensagem de ajuda com o formato e o exemplo.

---

## 5. Estrutura da planilha

### 5.1 Aba Dados

| Coluna | Cabeçalho | Obrigatório | Conteúdo |
|--------|-----------|-------------|----------|
| A | colaborador | Sim | Valor do dropdown da aba Listas |
| B | treinamento | Sim | Valor do dropdown da aba Listas |
| C | data_treinamento | Sim | Data **dd/mm/aaaa** |

- Não renomeie nem remova a linha de cabeçalho.
- Linhas totalmente vazias são ignoradas.
- Uma linha = um treinamento para um colaborador.

### 5.2 Aba Listas

Preenchida automaticamente pelo sistema (não edite):

- Coluna A: treinamentos (`vencimento_id|contrato|label`)
- Coluna B: colaboradores (`feedback_id|cpf|nome|cargo`)

### 5.3 Aba Instrucoes

Resumo do manual dentro do próprio arquivo modelo (formato da data, colunas e fluxo).

---

## 6. Criação, atualização e histórico

| Situação | Comportamento |
|----------|----------------|
| Colaborador ainda sem treinamento | Cria o treinamento, vincula o vencimento e registra histórico |
| Treinamento já existe | **Somente atualiza** a data/vencimento importado; não apaga os demais vencimentos |
| Mesmo vencimento já vinculado | Atualiza `data_treinamento` / `data_vencimento` e registra histórico |
| Histórico | Igual à carteira (`atualizarVencimento`): registro **1 a 1** do vencimento alterado |

Ou seja: reimportar a mesma linha atualiza o que já existe e mantém o histórico, sem duplicar o treinamento.

---

## 7. Resultado da importação

Ao concluir:

| Canal | O que aparece |
|-------|----------------|
| Aba **Importados** | Total, importados com sucesso, erros + CSV de sucesso e CSV de erro |
| Sino | Notificação de conclusão com os totais |
| E-mail | Relatório completo (mesmo conteúdo do CSV consolidado) |
| Meus Downloads | Cópia do relatório consolidado |

---

## 8. Erros comuns

| Situação | O que fazer |
|----------|-------------|
| Data rejeitada / inválida | Use **dd/mm/aaaa** (ex.: `15/03/2026`) |
| Colaborador não aparece no dropdown | Confirme se está encaminhado a treinamento e ativo na empresa |
| Treinamento não aparece | Confirme se o vencimento está ativo no cadastro |
| Cabeçalhos inválidos | Baixe novamente o modelo e não altere A1/B1/C1 |
| Planilha sem linhas válidas | Preencha colaborador, treinamento e data em pelo menos uma linha |

---

## 9. Referência técnica (equipe)

- UI: `GET/POST /g/treinamento/import`, histórico e download de CSV
- Job: `ImportacaoTreinamentoJob` → `mybp:importa-treinamento`
- Modelo: `GeradorModeloImportacaoTreinamento`
- Leitura: `LeitorPlanilhaImportacaoTreinamento` (normaliza data para `d/m/Y`)
- Habilidade: `treinamento_importacao`
