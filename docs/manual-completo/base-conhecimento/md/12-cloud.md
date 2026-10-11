# 12. Cloud
O **Cloud** é o repositório de documentos do MyBPIN: espaços com pastas e arquivos, acesso controlado por grupo ou por pessoa e um fluxo simples de revisão e aprovação de arquivos.
Criar o cloud*→*Definir quem acessa*→*Criar pastas*→*Enviar arquivos*→*Revisar e aprovar

## 12.1 Cadastro de clouds
Caminho no menu: Cloud › Cadastro
Lista os clouds existentes, com **ID**, **Nome**, quantidade de **Membros**, o botão **Ativo** (ativa/desativa) e **Editar**. O campo **Buscar** localiza pelo nome.

### Criar um cloud
1. Clique em **+ Novo Cloud**.
2. Informe o **Nome** e mantenha **Ativo** em *Sim*.
3. Clique em **Cadastrar**.
Depois de gravar, a tela passa para o modo de alteração e libera o bloco **Acesso**, onde se define quem enxerga o cloud.

### Definir quem acessa
Há duas formas de incluir membros, escolhidas pelos botões **Grupo** e **Colaborador**:
- **Grupo:** selecione um grupo e clique em **+ Incluir membros do grupo**. Todos os colaboradores ativos do grupo entram na lista.
- **Colaborador:** digite o nome em **Buscar colaborador** e clique no resultado para incluir uma pessoa.
A lista mostra o nome, o grupo de cada membro e o botão ✕ para removê-lo. O campo **Filtrar membros da lista** ajuda em listas longas. Para gravar, clique em **Salvar alterações**.
Os membros do grupo **Administradores** entram automaticamente em todos os clouds e não podem ser removidos por esta tela.

## 12.2 Usar o cloud
Caminho no menu: Cloud › (nome do cloud)
Cada cloud ativo do qual você é membro aparece no menu **Cloud**, acima de Cadastro. Ao abrir, a tela mostra o conteúdo da pasta atual e o caminho percorrido (**Início / pasta / subpasta**).
- **Nova Pasta:** Cria uma pasta no local atual.
- **Atualizar:** Recarrega o conteúdo.
- **Pesquisar:** Abre o campo de busca. Digite e pressione Enter; a lista ao lado restringe a Todos, Pastas ou Arquivos.
- **Upload:** Envia arquivos para a pasta atual. Aparece depois que você entra em uma pasta.

### Criar uma pasta
1. Clique em **Nova Pasta** e informe o **Nome**.
2. Em **Grupos permitidos**, defina quais grupos enxergam a pasta: o botão ao lado de cada grupo alterna entre *Permitido* e *Negado*; **Nenhum** nega para todos.
3. Clique em **Salvar**.
A pasta passa a aparecer na lista com a data de criação, quem criou e a última atualização. O menu ⋮ da pasta traz **Mover**, **Editar** (nome e grupos permitidos) e **Deletar**.

### Enviar arquivos
1. Clique no nome da pasta para entrar nela.
2. Clique em **Upload** e escolha os arquivos, ou arraste-os para a tela.
3. Acompanhe a barra de progresso. Ao terminar, o arquivo aparece na lista.
A linha **← Voltar** retorna à pasta anterior.

### Ações do arquivo
Clique no nome do arquivo para abrir o menu de ações:
- **Download:** Baixa o arquivo.
- **Visualizar:** Abre o arquivo na tela (imagens e PDF).
- **Detalhes:** Mostra nome, tamanho, data de criação e quem lançou.
- **Editar:** Altera o nome e os grupos permitidos.
- **Mover:** Transfere o arquivo para outra pasta: navegue até o destino na janela **Mover item**.
- **Deletar:** Exclui o arquivo.
- **Atualizar:** Substitui o arquivo por uma nova versão.
- **Revisar:** Marca o arquivo como revisado.
- **Enviar para Revisão:** Envia o arquivo por e-mail a um revisor.
- **Aprovar:** Marca o arquivo como aprovado. Aparece para quem tem essa permissão, depois da revisão.
- **Enviar para Aprovação:** Envia o arquivo por e-mail a um aprovador.
As ações disponíveis para cada pessoa dependem das **habilidades** do grupo dela (seção 12.3).

### Revisão e aprovação
A cor do nome do arquivo indica em que ponto ele está:
- **rosa** — ainda não revisado;
- **verde** — revisado.
1. Para pedir a revisão a alguém, use **Enviar para Revisão**: informe **Nome**, **E-mail** e **Mensagem** e clique em **Enviar**.
2. Quem revisa usa a ação **Revisar** e confirma em **SIM**. O arquivo fica verde.
3. Depois de revisado, o menu passa a oferecer **Enviar para Aprovação** (mesmo formulário) e, para quem tem permissão, **Aprovar**.

### Pesquisar

## 12.3 Configurações (grupos do Cloud)
Caminho no menu: Cloud › Configurações
Os grupos do Cloud definem **o que** cada pessoa pode fazer com pastas e arquivos. A lista mostra **Nome**, **Descrição**, **Qnt membros** e **Ativo**, com os ícones de editar e excluir.

### Cadastrar um grupo
1. Clique em **Cadastrar**.
2. Na aba **Identificação**, preencha **Nome**, **Descrição** e **Ativo**.
3. Em **Habilidades**, clique no botão de cada função para alternar entre *Negar* e *Permitir*; **Permitir todas** libera tudo de uma vez.
As habilidades correspondem às ações do arquivo: **Download**, **Visualizar**, **Detalhes**, **Editar**, **Mover**, **Deletar**, **Atualizar**, **Revisar** e **Aprovar**.
4. Na aba **Membros**, busque cada colaborador em **Colaborador** para incluí-lo no grupo.
5. Clique em **Cadastrar**.
