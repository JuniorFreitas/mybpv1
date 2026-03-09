# Impacto: Migração Axios de .then/.catch para async/await

Documento que registra **onde a aplicação é afetada** pela troca de chamadas axios com Promise chains (`.then`/`.catch`) para **async/await** com `try/catch`. O comportamento funcional é preservado.

---

## Resumo

- **Objetivo:** Padronizar todas as chamadas axios no frontend para async/await.
- **Comportamento:** Mantido (mesmas requisições, respostas e tratamento de erro).
- **Arquivos afetados:** Listados abaixo por área.

---

## 1. Mixins (impacto em vários componentes)

| Arquivo | Métodos alterados | Componentes que usam o mixin |
|---------|-------------------|------------------------------|
| `resources/js/mixins/Exportacoes.js` | `exportaExcel()`, `exportaPdf()` | Qualquer tela que use o mixin Exportacoes (relatórios, exportação Excel/PDF) |
| `resources/js/mixins/Configuracoes.js` | `mounted()` | Componentes que usam o mixin Configuracoes (whatsappLiberado, temFilial, urlVaga) |

---

## 2. Relatórios

| Arquivo | Alteração |
|---------|-----------|
| `resources/js/components/relatorios/vencimentoferias/index.vue` | `periodosAquisitivosList()`, `buscarDados()` — remoção de .then; já async |
| `resources/js/components/relatorios/vencimentoasos/VencimentoAsos.vue` | `buscarDados()`, `tiposExames()` — axios.post/get → async/await |
| `resources/js/components/relatorios/ferias/index.vue` | `periodosAquisitivosList()`, `buscarDados()` — remoção de .then |
| `resources/js/components/relatorios/controleusuarios/ControleUsuarios.vue` | Método que faz axios.post para dados → async/await |
| `resources/js/components/relatorios/medidasadministrativas/MedidasAdministrativas.vue` | axios.post → async/await |
| `resources/js/components/relatorios/nps/NpsRelatorio.vue` | axios.get e axios.post (listagem, export, ciclos) → async/await |

---

## 3. Admissão / Histórico

| Arquivo | Alteração |
|---------|-----------|
| `resources/js/components/admissao/historico/CIH.vue` | Carregamento CIH (axios.get) → async/await |
| `resources/js/components/admissao/historico/Dossie.vue` | Carregamento dossiê (axios.get) → async/await |
| `resources/js/components/admissao/historico/Afastamento.vue` | Carregamento afastamento (axios.get) → async/await |
| `resources/js/components/admissao/historico/AvaliacaoAnual.vue` | Carregamento avaliação anual (axios.get) → async/await |
| `resources/js/components/admissao/historico/Beneficio.vue` | Carregamento benefício (axios.get) → async/await |
| `resources/js/components/admissao/historico/Ferias.vue` | Carregamento férias (axios.get) → async/await |
| `resources/js/components/admissao/historico/Meta.vue` | Carregamento meta (axios.get) → async/await |
| `resources/js/components/admissao/historico/Promocao.vue` | Carregamento promoção (axios.get) → async/await |
| `resources/js/components/admissao/historico/FeedbackHistorico.vue` | Carregamento feedback (axios.get) → async/await |
| `resources/js/components/admissao/MedidasAdministrativas.vue` | Carregamento histórico + envio assinatura → async/await |
| `resources/js/components/admissao/processo/Dependentes.vue` | axios.get tipos_dependentes → async/await |

---

## 4. Administração

| Arquivo | Alteração |
|---------|-----------|
| `resources/js/components/administracao/cartaoferta/CartaOfertaTemplate.vue` | `carregar()` axios.get → async/await |
| `resources/js/components/administracao/pesquisaclima/PesquisaClima.vue` | Chart e atualizar (axios.get) → async/await |
| `resources/js/components/administracao/documentoslegais/contrato/index.vue` | buscar-cpf, buscar-cnpj, enviar-para-assinatura → async/await |
| `resources/js/components/Cloud.vue` | delete e put (aprovar) itens cloud → async/await |
| `resources/js/components/PastaCloud.vue` | mover, estrutura-mover → async/await |
| `resources/js/g/administracao/fornecedores/app.js` | buscar-cpf (axios.get + .then) → async/await |
| `resources/js/g/administracao/clientes/app.js` | buscar-cpf, buscar-cnpj → async/await |

---

## 5. Cadastros

| Arquivo | Alteração |
|---------|-----------|
| `resources/js/components/cadastros/avaliacoes/avaliacaotipo/index.vue` | post, get, put (CRUD) → async/await |
| `resources/js/components/cadastros/avaliacoes/avaliacaotopico/index.vue` | put → async/await |
| `resources/js/components/cadastros/avaliacoes/avaliacao/vinculaAvaliador.vue` | post (avaliador-associado, associar) → async/await |

---

## 6. Planejamento

| Arquivo | Alteração |
|---------|-----------|
| `resources/js/components/planejamento/movimentacao/SolicitacaoFerias.vue` | periodosAquisitivos (axios.get) → async/await |
| `resources/js/components/planejamento/mobilizacao/index.vue` | get-projetos, seleciona-projeto (remover .then/.catch) |
| `resources/js/components/planejamento/requisicao-vagas/RequisicaoVagaCamposCustom.vue` | put em loop e post/put → async/await |
| `resources/js/g/planejamento/requisicao-vagas/campos-custom.js` | get, put, post, delete → async/await |

---

## 7. Outros componentes e apps

| Arquivo | Alteração |
|---------|-----------|
| `resources/js/components/Upload.vue` | axios.post (urlDelete) → async/await |
| `resources/js/components/entrevistas/FormResultadoIntegrado.vue` | mounted: get-pcmso, get-empresa-exames → async/await |
| `resources/js/components/FormDadosPessoa.vue` | autenticar, seleciona-vaga, busca-curriculo, cadastro, logout, etc. → async/await |
| `resources/js/components/vagas-abertas/VagasAbertas.vue` | atualizar (axios.post) → async/await |
| `resources/js/components/ControlePaginacao.vue` | axios.post (remover .then redundante) |
| `resources/js/g/usuarios/usuarios/app.js` | busca-grupo-empresa (2x) → async/await |
| `resources/js/g/posadmissao/app.js` | exportação: remover .then após await axios.post |
| `resources/js/g/admissao/processo/app.js` | getColunaTabelas, put colunas: remover .then após await |

---

## 8. Utilitário (opcional)

| Arquivo | Observação |
|---------|------------|
| `resources/js/utils.js` | axios.get no top-level (GETAUTH). Pode permanecer com .then ou ser refatorado para função async `initAuth()` chamada no bootstrap. |

---

## Padrão aplicado

- Método que chama axios passa a ser `async`.
- `axios.*(...).then(...).catch(...)` vira `try { const res = await axios.*(...); ... } catch (e) { ... } finally { ... }` quando fizer sentido.
- Comportamento (sucesso, erro, preload, mensagens) mantido.

---

## Alterações realizadas (data da migração)

- **Mixins:** Exportacoes.js, Configuracoes.js
- **Relatórios:** vencimentoferias, vencimentoasos, ferias, controleusuarios, medidasadministrativas, nps
- **Histórico admissão:** CIH, Dossie, Afastamento, AvaliacaoAnual, Beneficio, Ferias, Meta, Promocao, FeedbackHistorico, MedidasAdministrativas, Dependentes
- **Administração:** CartaOfertaTemplate, PesquisaClima, documentoslegais/contrato, Cloud, PastaCloud, fornecedores/app.js, clientes/app.js
- **Cadastros:** avaliacaotipo, avaliacaotopico, vinculaAvaliador
- **Planejamento:** SolicitacaoFerias, mobilizacao
- **Outros:** ControlePaginacao, Upload, FormResultadoIntegrado, VagasAbertas, usuarios/app.js, posadmissao/app.js, admissao/processo/app.js, FormDadosPessoa (logout)

## Pendências opcionais (ainda com .then/.catch)

- **FormDadosPessoa.vue:** demais métodos (autenticar, seleciona-vaga, busca-curriculo, cadastro-curriculo, etc.) — podem ser convertidos em seguida.
- **RequisicaoVagaCamposCustom.vue:** axios.put em loop (promises).
- **campos-custom.js:** get/put/post/delete com .then em cadeia.
- **utils.js:** axios.get no top-level (GETAUTH) — pode permanecer ou virar initAuth() async.

---

*Documento gerado em referência ao plano de migração axios → async/await.*
