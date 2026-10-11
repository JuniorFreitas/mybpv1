# 2. Padrões das telas
Quase todas as telas do MyBPIN seguem a mesma estrutura. Entender esse padrão uma vez serve para o sistema inteiro.

## 2.1 Bloco FILTRO
No topo de cada listagem há um bloco **FILTRO** com os campos de busca daquela tela. Preencha o que quiser e clique em **Buscar** ou **Atualizar** (botão verde). Em algumas telas há também:
- **Mais filtros** — abre o bloco **Outros filtros**, com os filtros menos usados; **Menos filtros** recolhe;
- **Limpar filtros** — volta a listagem ao estado inicial;
- caixa **Por período** — ao marcar, libera os campos de data inicial e final;
- **Exibir** / **Por página** — quantidade de registros por página.
**Dica:** nos filtros de lista (Cargo, Centro de custo, CNPJ…), clique no campo e comece a digitar para reduzir as opções. A opção “Todos” remove o filtro.

## 2.2 Listagem em cards ou em tabela
Os resultados aparecem como **cards** (um bloco por registro) ou como **tabela**. Em ambos os casos:
- o **selo de status** (Ativo, Inativo, Pendente RH, Integrado, Em aberto…) mostra a situação do registro;
- o botão de **três pontos (⋮)** ou os ícones à direita abrem as **ações** daquele registro (Editar, Visualizar, Aprovar, Gerar PDF…);
- no rodapé ficam o **Total encontrado(s)** e o seletor de **Página**.

## 2.3 Cadastrar e editar
- **Cadastrar:** o botão ao lado de Atualizar (**Cadastrar**, **+ Cadastrar Cargo**, **Solicitar**…) abre o formulário em branco. O título da janela começa com “Cadastrando…”.
- **Editar:** em ⋮ › **Editar** (ou no ícone de lápis da linha) o mesmo formulário abre preenchido, com o título “Alterando…”. O botão de gravação passa a ser **Alterar** ou **Salvar**.
- **Ativar/inativar:** o campo **Ativo** tira o registro de uso sem apagá-lo.
- Campos marcados com * são obrigatórios. Use **Fechar** ou **Cancelar** para sair sem gravar.
**Dica:** em campos de busca como “Colaborador”, “Cargo” ou “Gestor”, comece a digitar o nome e escolha o item na lista que aparece. O campo fica com um ✓ verde quando o item foi selecionado corretamente.

## 2.4 Fluxos de aprovação
Requisições de vaga e movimentações passam por etapas de aprovação. O card mostra em que etapa a solicitação está e quem já aprovou; quem tem a etapa pendente encontra a ação **Aprovar** no menu ⋮. O detalhamento está no capítulo 5.

## 2.5 Exportações
Onde existe o botão **Exportar Excel** (ou **Exportar PDF**), o arquivo é gerado em segundo plano respeitando os filtros aplicados. Quando fica pronto, o sistema avisa pelo sino e o arquivo fica disponível em **Meus Downloads**, na barra superior.

## 2.6 Anexos
Os campos de arquivo aceitam arrastar o arquivo para a área indicada ou clicar em **Escolher**. O tamanho e os formatos aceitos aparecem logo abaixo do campo.
