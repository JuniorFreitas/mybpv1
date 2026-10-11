---
name: manual-usuario
description: >-
  Mantém o Manual Completo do MyBPIN (docs/manual-completo): captura telas no
  navegador com dados mascarados, escreve/atualiza capítulos em secoes/*.html,
  gera o PDF paginado e regenera a base de conhecimento (RAG) usada pelo chatbot.
  Use when the user asks to atualizar/complementar o manual, documentar tela ou
  módulo novo, "faltou tal tela no manual", gerar o PDF do manual, ou preparar
  documentação para chatbot/RAG.
---

# Manual do Usuário · MyBPIN

Fonte única: `docs/manual-completo/secoes/*.html`. Dela saem o PDF (`Manual-Completo-MyBPIN.pdf`) e a base do
chatbot (`base-conhecimento/`). Nunca edite `manual.html`, o PDF ou `base-conhecimento/` à mão.

## Quick start

```bash
# gerar PDF (2 passadas: paginação + sumário numerado) e a base de conhecimento
./docs/manual-completo/gerar-pdf.sh
# só a base de conhecimento
python3 docs/manual-completo/gerar-base-conhecimento.py
```

## Estrutura

| Caminho | Conteúdo |
|---|---|
| `secoes/NN-nome.html` | Um capítulo por arquivo. A ordem do nome define a ordem no manual. |
| `img/` | Capturas (JPG) e logos. Prefixo por módulo: `adm-`, `cad-`, `plan-`, `cur-`, `ent-`, `adm8-`, `aval-`, `ocor-`, `cham-`, `trn-`, `cloud-`, `rel-`, `usr-`, `grp-`, `ponto-`, `wr-`, `top-`. |
| `modelo/` | `estilo.css`, `capa.html`, `contracapa.html`, `paginas-texto.swift`. |
| `montar.py` / `paginas.py` | Montam `manual.html`, o sumário e os números de página. |
| `ferramentas/` | Máscara de dados para captura (`mascara.js`, `mascara-pre.js`, `preparar-mascara.sh`). |
| `base-conhecimento/` | Saída para RAG (`chunks.jsonl`, `md/`, `indice.json`, `README.md`). |

## Regras inegociáveis

1. **Nenhum dado real** em imagem ou texto: pessoas, CPF/RG, e-mails, telefones, empresa, clientes, centros de custo, fornecedores, clínicas, endereços, salários, textos livres, logo antigo. A base local tem dados reais.
2. **Conferir cada captura** antes de arquivar, inclusive o fundo atrás de modais e textos dentro de gráficos (canvas não é mascarável: cortar ou descartar).
3. **Somente leitura** no sistema: abrir formulários, nunca clicar em Salvar/Cadastrar/Aprovar/Enviar/Excluir. Criar dados só com autorização explícita do usuário, com nomes fictícios, e avisar o que ficou na base.
4. **Navegador embutido**, com o usuário logado na janela. Não usar Playwright. Não deslogar o usuário nem digitar senhas sem ele pedir.
5. Documentar **só o que o usuário logado enxerga**. O que não foi visto não é afirmado; o que foi deduzido sem executar é sinalizado no relato final.
6. Não inventar contatos, números ou promessas comerciais.

## Fluxo para documentar uma tela nova

1. **Descobrir** o menu e a URL (`resources/views/layouts/menu.blade.php`, `routes/web.php`) e ler o componente Vue quando o comportamento não for óbvio.
2. **Preparar a máscara:** `docs/manual-completo/ferramentas/preparar-mascara.sh`; no navegador, viewport 1200x750 e, na página do sistema: `(0,eval)(await (await fetch('/_manual_mascara.js?'+Date.now())).text())`.
3. **Capturar** por página: `await READY('<trecho da url>'); await G({opções})` → screenshot. Formulários: `await OPEN(/texto do botão/ ou 'edit', {opções})`; rolar modal: `SC(y, {opções})`. `G` devolve os metadados da tela (filtros, opções de select, botões, colunas) — use-os para escrever a tabela de filtros.
4. **Arquivar** a imagem em `img/` com o prefixo do módulo; cortar com PIL o que sobrar de sensível.
5. **Escrever** a seção em `secoes/` (padrão abaixo) e incluir o módulo na tabela do menu (`01-acesso.html`) e, se fizer sentido, no ciclo (`17-ciclo.html`).
6. **Gerar** com `gerar-pdf.sh`, renderizar as páginas alteradas e olhar o resultado.
7. **Limpar:** `preparar-mascara.sh --limpar`, remover chaves `__*` do `sessionStorage`, voltar o viewport para desktop.
8. **Relatar** com franqueza: o que entrou, o que ficou sem imagem e por quê, o que foi deduzido, o que foi criado na base.

### Opções da máscara (`G`, `MM`, `OPEN`, `SC`)

| Opção | Uso |
|---|---|
| `titulo: 'p'\|'cc'\|'ben'\|'forn'\|'proj'` | Categoria do título dos cards. |
| `skip: /regex/` | Rótulos/colunas que não devem ser mascarados (ex.: nome de treinamento). |
| `cat: [[/rótulo/, 'forn'\|'cc'\|'skip']]` | Força a categoria de um campo. |
| `fixcol: [[/cabeçalho/, 'valor' ou [lista]]]` | Substitui uma coluna inteira. |
| `dict: [[/regex/g, 'texto']]` | Trocas extras de texto. |

Lições práticas:
- Página lenta re-renderiza depois da máscara: use `G({}, 3500)` ou mais e confira o screenshot.
- Vue desfaz trocas de texto em componentes reativos: injete CSS (`font-size:0` + `::after{content}`) em vez de alterar o DOM.
- `browser_batch`: no máximo 25 ações; um erro de JS perde as imagens do lote — envolva em `try/catch`.
- Tela pública (login, recuperar senha, 2FA): capture sem sessão; mascare o e-mail exibido.
- Mapas e gráficos em canvas: cortar a imagem; não tentar mascarar.
- `ferramentas/mascara.js` contém um dicionário com nomes reais de empresas/clientes **daquela base** (é o que ele procura para trocar). Em outra base, ajuste o dicionário. Não distribuir esse arquivo com o manual.

## Padrão de escrita das seções

```html
<h2>N.M Nome da tela</h2>
<span class="caminho">Menu › Item</span>
<p>Para que serve a tela, em uma ou duas frases.</p>
<figure><img src="img/prefixo-tela.jpg" alt="..."><figcaption>Legenda</figcaption></figure>
<h3>Filtros</h3>
<table class="filtros"><tr><th>Filtro</th><th>O que faz</th></tr>...</table>
<h3>Cadastrar ou editar</h3>
<ol><li>Clique em <b class="btn">Cadastrar</b>...</li></ol>
```

- Classes disponíveis: `intro`, `caminho`, `filtros`, `btn`, `req` (asterisco de obrigatório), `nota`, `dica`, `fluxo`, `figure.media` (62%), `figure.pequena` (36%).
- Linguagem para o usuário de RH: direta, em passos, com o **nome exato** de botões, campos e menus. Sem jargão técnico.
- Cada seção precisa fazer sentido sozinha (ela vira um trecho da base do chatbot): diga o caminho no menu e para que a tela serve antes dos passos.
- Um assunto por `<h3>`; títulos descritivos ("Abrir um chamado", não "Passos").
- Capítulo novo: criar `secoes/NN-nome.html` com `<h1 id="slug">N. Título</h1>`; renumerar os capítulos finais (ciclo e observações) e as referências cruzadas.
- Limitações e itens não cobertos vão em `18-observacoes.html`.

## Base de conhecimento (RAG)

`gerar-base-conhecimento.py` corta cada `<h2>` em um trecho (dividindo nos `<h3>` quando passa de ~1.800 caracteres) e grava
`chunks.jsonl` com `id`, `capitulo`, `secao`, `subsecao`, `caminho_menu`, `ancora`, `pagina_pdf`, `imagens`, `texto`, `hash`.
Regras de indexação, citação e comportamento do chatbot: `docs/manual-completo/base-conhecimento/README.md`.

Ao mudar o manual, confira que o trecho gerado responde à pergunta que um usuário faria ("como faço X?") sem depender de outro trecho.

## Checklist antes de entregar

- [ ] Nenhum dado real nas imagens novas (olhadas uma a uma)
- [ ] Nada gravado no sistema sem autorização; o que foi criado está relatado
- [ ] Seção com caminho, filtros, colunas e passo a passo
- [ ] Menu lateral (cap. 1), ciclo (cap. 17) e observações (cap. 18) atualizados
- [ ] `gerar-pdf.sh` rodou; páginas alteradas conferidas visualmente
- [ ] `base-conhecimento/` regenerada
- [ ] `public/_manual_mascara.js` removido; viewport e `sessionStorage` limpos
