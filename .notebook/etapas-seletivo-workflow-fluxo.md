# Workflow do Seletivo — editor visual em fluxo

## Escopo
- Tela: `/g/administracao/etapas-seletivo` (permissão `administracao_etapas_seletivo`)
- Vue: `resources/js/components/administracao/etapas-seletivo/EtapasSeletivo.vue` (container, filtro, modal, chamadas API) + `WorkflowFluxo.vue` (trilha visual)
- Entrada: `resources/js/g/administracao/etapas-seletivo/app.js` → `public/js/g/administracao/etapas-seletivo/app.js`
- Backend: `EtapaTipoSeletivoController` (`listar`, `salvar`, `reordenar`, `restaurarPadrao`, `personalizarCargo`, `voltarPadraoCargo`) + `app/Domain/Recrutamento/*` (WorkflowSeletivoService, GateEtapaSeletivo, WorkflowSeletivoPadrao)

## O que foi feito
- **Modo Fluxo (padrão) / Lista:** toggle ao lado dos botões do filtro. Lista = cards antigos.
- **Fluxo:** trilha Candidato selecionado → etapas (avanço + sistema, por `ordem`) → Admissão; desclassificações numa área "Saídas do funil" abaixo.
- **Interações:** arrastar (HTML5 DnD) reordena; "+" entre etapas insere (ordem = ponto médio entre vizinhas); estrela = obrigatória, power = ativa (update otimista com rollback); hover destaca pré-requisito (âmbar) e dependentes (azul); nome/lápis abre modal.
- **Filtro:** no modo Fluxo só aparece "Cargo"; Buscar/Tipo/Status só no modo Lista.
- **Modal:** `:size="68"` (número = % de largura; `size="lg"` NÃO é reconhecido pelo `Modal.vue`, só `p`/`g`/número). Tipo em cartões radio, regras em cartões com switch, textos de ajuda; "Ordem"→"Posição", "Depende de"→"Só liberar depois de".
- **Endpoint novo:** `POST administracao/etapas-seletivo/reordenar` `{ids:[...], vaga_id?}` → `ordem = posição × 10` dentro do escopo (empresa/cargo).

## Gotchas
- Cargo sem override é somente leitura (fluxo e modal bloqueados até "Personalizar este cargo").
- Etapa `sistema` existente: tipo/chave travados; só o tipo "Sistema" fica habilitado no modal.
- Inserção entre etapas usa ponto médio; após alguns inserts sem espaço usa `anterior+1` (pode empatar) — um arraste normaliza para múltiplos de 10.
- Reordenar envia pipeline + desclassificações; desclassificação não é arrastável.
- `sed -i` no macOS exige `-i ''`; usar Edit.

## Validado em 2026-10-15 (navegador embutido)
- Render do fluxo, hover de pré-requisitos, reordenar (persistiu após reload), modal larga.
- NÃO testado: "+" de inserção, toggles estrela/power, modo cargo específico, layout mobile, salvar pela modal nova, PHPUnit (sem teste para `reordenar`).

## Próximos passos sugeridos
- Teste PHPUnit (SQLite memória) para `reordenar` (escopo empresa/cargo, ids de outro tenant ignorados).
- Desenhar linhas/setas de dependência no canvas (hoje só chip + highlight).
- Documentar a tela no manual (`manual-usuario`).
- Nada foi commitado (branch `feature/curriculo-selecionados`).
