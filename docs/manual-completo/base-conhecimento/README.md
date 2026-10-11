# Base de conhecimento do MyBPIN (RAG)

Conteúdo do Manual Completo preparado para busca semântica e para o futuro chatbot de atendimento e dúvidas.
**Não edite estes arquivos à mão**: eles são gerados a partir de `../secoes/*.html` por `../gerar-base-conhecimento.py`
(executado automaticamente pelo `../gerar-pdf.sh`). Para corrigir uma resposta do chatbot, corrija a seção do manual e gere de novo.

## Arquivos

| Arquivo | Para que serve |
|---|---|
| `chunks.jsonl` | Um trecho por linha, pronto para gerar embeddings e indexar. |
| `md/NN-capitulo.md` | O mesmo conteúdo em Markdown, um arquivo por capítulo. |
| `indice.json` | Versão do manual, data de geração, quantidade de trechos e lista de capítulos. |

## Formato de `chunks.jsonl`

| Campo | Conteúdo |
|---|---|
| `id` | Identificador estável: `ancora#subsecao` (ex.: `cloud-2#enviar-arquivos`). Use como chave no índice. |
| `capitulo` / `secao` / `subsecao` | Títulos do manual. |
| `caminho_menu` | Onde a tela fica no sistema (ex.: `Treinamento › Importação`). |
| `ancora` | Âncora da seção em `manual.html` (`manual.html#ancora`). |
| `pagina_pdf` | Página da seção no PDF, para citar a fonte. |
| `imagens` | Capturas de tela da seção (caminhos relativos a `docs/manual-completo/`). |
| `fonte` | Arquivo de origem em `secoes/`. |
| `texto` | Texto em Markdown. Começa com a trilha `Capítulo > Seção > Subseção`, para fazer sentido sozinho. |
| `hash` | Hash do texto. Se não mudou, não é preciso gerar o embedding de novo. |

Regra de corte: cada seção (`<h2>`) é um trecho; seções com mais de ~1.800 caracteres são divididas nas subseções (`<h3>`).

## Como usar no RAG

1. **Indexar:** gere o embedding do campo `texto` de cada linha e grave junto com os metadados. Reindexe só os trechos cujo `hash` mudou e remova os `id` que deixaram de existir.
2. **Buscar:** combine busca vetorial com busca por palavra-chave (nomes de menus, botões e campos são termos exatos — "CIH", "ASO", "Perímetro"). Recupere de 4 a 6 trechos.
3. **Responder:** entregue ao modelo os trechos recuperados e peça a resposta apenas com base neles.
4. **Citar:** mostre ao usuário o `caminho_menu` e a seção (`capitulo` › `secao`), com a `pagina_pdf`. Se fizer sentido, exiba a primeira imagem de `imagens`.

## Regras recomendadas para o chatbot

- Responder **somente** com o que está na base. Sem trecho relevante, dizer que não encontrou e orientar a abrir um chamado.
- **Atendimento:** todo atendimento é pelo menu **Chamados**; os canais oficiais de WhatsApp continuam disponíveis. O chatbot tira dúvidas de uso, não substitui o chamado.
- **Permissões:** o manual descreve o sistema completo. O usuário pode não ver um menu por causa do grupo de permissão; nesse caso, orientar a falar com o administrador.
- **Não executar ações** nem pedir senha, código de verificação ou dados pessoais.
- **Dados fictícios:** nomes de pessoas, empresas e valores que aparecem no manual são exemplos; não tratá-los como dados reais.
- Responder em português, em passos numerados quando for um "como fazer", usando os nomes exatos dos botões e menus.

## O que esta base ainda não tem

- Perguntas frequentes reais. Quando o módulo de Chamados tiver histórico, vale criar `faq.jsonl` com pergunta, resposta e seção de origem.
- Regras de negócio que não aparecem na tela (cálculos, prazos legais, integrações).
- Telas que o manual declara não cobrir (ver capítulo "Observações sobre este manual").
