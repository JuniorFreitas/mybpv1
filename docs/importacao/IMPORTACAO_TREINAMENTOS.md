# Importação de Treinamentos – Manual de uso

Guia para importar treinamentos em lote via planilha Excel no MyBP.

---

## 1. Objetivo

Registrar ou atualizar treinamentos de colaboradores a partir de uma planilha `.xlsx`, com o **mesmo comportamento da carteira/etiquetas** (criar ou atualizar um vencimento por linha), processamento em segundo plano em **lotes**, notificação no sino, e-mail e relatório na aba **Importados**.

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
   - **data_treinamento** — preencha a data no formato abaixo (obrigatório).
   - **data_vencimento** — opcional (veja seção 5).
4. Salve o `.xlsx` e envie pela dropzone da tela.
5. Acompanhe o resultado na aba **Importados**, no sino e no e-mail.

Tamanho máximo recomendado do arquivo: **20 MB**.

---

## 4. Formato das datas

| Coluna | Obrigatório | Formato |
|--------|-------------|---------|
| `data_treinamento` | Sim | **dd/mm/aaaa** |
| `data_vencimento` | Não | **dd/mm/aaaa** ou vazio |

Exemplos válidos: `15/03/2026`, `01/05/2026`.

### O que evitar

- `15-03-2026` ou `15.03.2026`
- `3/15/2026` (padrão americano)
- `15/03/26` (ano com 2 dígitos)

O Excel também aceita célula de data nativa (convertida para dd/mm/aaaa).

---

## 5. `data_vencimento` (opcional)

| Valor na planilha | Comportamento (igual carteira) |
|-------------------|--------------------------------|
| **Vazio** | `data_vencimento` = `data_treinamento` + `prazo_fixo` do vencimento |
| **Preenchido** | Usa a data informada no Excel |

---

## 6. Estrutura da planilha

### 6.1 Aba Dados

| Coluna | Cabeçalho | Obrigatório | Conteúdo |
|--------|-----------|-------------|----------|
| A | colaborador | Sim | Valor do dropdown da aba Listas |
| B | treinamento | Sim | Valor do dropdown da aba Listas |
| C | data_treinamento | Sim | Data **dd/mm/aaaa** |
| D | data_vencimento | Não | Data **dd/mm/aaaa** ou vazio |

- Não renomeie nem remova a linha de cabeçalho.
- Planilhas antigas só com A–C continuam válidas (`data_vencimento` = vazio → prazo_fixo).
- Linhas totalmente vazias são ignoradas.
- Uma linha = um “salvar card” da carteira (um vencimento para um colaborador).

### 6.2 Aba Listas

Preenchida automaticamente pelo sistema (não edite):

- Coluna A: treinamentos (`vencimento_id|contrato|label`)
- Coluna B: colaboradores (`feedback_id|cpf|nome|cargo`)

### 6.3 Aba Instrucoes

Resumo do manual dentro do próprio arquivo modelo.

---

## 7. Criação, atualização e histórico (paridade carteira)

Salvar na carteira e importar uma linha usam o mesmo domínio:

| Situação | Comportamento |
|----------|----------------|
| Sem `Treinamento` para o `feedback_id` | Cria (`tipo=Fixo`, `cadastrou=usuário`) e vincula o vencimento |
| Já existe `Treinamento` | Não duplica; altera só aquele `vencimento_id` |
| Vencimento já vinculado | Atualiza datas (equivalente a detach+attach); preserva `numero_fat` / `arquivo_id` |
| Vencimento ainda não vinculado | Attach novo |
| Outros vencimentos do colaborador | Permanecem intactos |
| Histórico | Registro **1 a 1** do vencimento alterado (`removido=false`) |

**Idempotência:** reimportar a mesma linha (`feedback_id` + `vencimento_id`) deixa o mesmo estado final.

**Sem deduplicação da planilha:** linhas repetidas são processadas na ordem do arquivo; a **última** prevalece no banco; **todas** entram no CSV de resultado.

---

## 8. Performance (processamento em lote)

A importação da tela processa em chunks (~1000 linhas), com preload enxuto e multi-insert/update (mesmo espírito do export CIH), sem N+1 linha a linha.

---

## 9. Resultado da importação

| Canal | O que aparece |
|-------|----------------|
| Aba **Importados** | Total, sucessos, erros + CSV de sucesso e de erro |
| Sino | Notificação de conclusão com os totais |
| E-mail | Relatório completo |
| Meus Downloads | Cópia do relatório consolidado |

---

## 10. Erros comuns

| Situação | O que fazer |
|----------|-------------|
| Data rejeitada / inválida | Use **dd/mm/aaaa** |
| Colaborador não aparece no dropdown | Confirme encaminhamento a treinamento e vínculo ativo |
| Treinamento não aparece | Confirme vencimento ativo no cadastro |
| Cabeçalhos inválidos | Baixe novamente o modelo (A–D) |
| Planilha sem linhas válidas | Preencha colaborador, treinamento e data_treinamento |

---

## 11. Referência técnica (equipe)

- UI: `GET/POST /g/treinamento/import`, histórico e download de CSV
- Job: `ImportacaoTreinamentoJob` → `mybp:importa-treinamento` (sem flags legadas de limpar/substituir)
- Lote: `ImportadorLoteTreinamentoService::processarChunk`
- Modelo: `GeradorModeloImportacaoTreinamento` (coluna D `data_vencimento`)
- Leitura: `LeitorPlanilhaImportacaoTreinamento` (A–D; compatível A–C)
- Paridade: `TreinamentoController::atualizarVencimento`
- Habilidade: `treinamento_importacao`
