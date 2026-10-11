# 14. Usuários e Grupos
O acesso ao MyBPIN é definido por duas peças: o **usuário** (a pessoa que entra no sistema) e o **grupo** ao qual ele pertence, que determina os menus e as ações liberadas.

## 14.1 Usuários do Sistema
Caminho no menu: Usuários › Usuários do Sistema

### Filtros
- **Buscar:** Localiza pelo nome do usuário.
- **E-mail (login):** Localiza pelo e-mail usado no login.
- **Grupo:** Usuários de um grupo de permissão.
- **Tipo:** Administrador, Funcionario, Fornecedor ou Gestor.
- **Status:** Apenas Ativos, Apenas Inativos ou Todos os Status.
**Colunas:** Nome, Grupo, Tipo, E-mail (login), Status e Ação. O botão de status (**Ativo**/**Inativo**) libera ou bloqueia o acesso; o ícone de lápis abre a edição.

### Criar um usuário
1. Clique em **Criar novo usuário**.
2. Preencha **Nome do usuário** e **E-mail** (será o login).
3. Escolha o **Grupo** (define as permissões) e o **Tipo de Usuário**.
4. Defina **Ativo** e, se a pessoa aprova solicitações da equipe, marque **Gestor** como *Sim*.
5. Em **Contatos**, use **+ Adicionar** para incluir telefones.
6. Em **Notificações WhatsApp**, ligue os avisos que a pessoa deve receber: Recrutamento, Exames, Admissão, Intermitente, Carta Oferta, Transporte e Movimentação.
7. Clique em **Cadastrar**.
Um tipo de notificação aparece como “desabilitado pela empresa” quando o envio está desligado para toda a empresa.

### Editar um usuário
Na edição aparecem dois blocos a mais:
- **Tipos de e-mails que esse usuário pode receber:** Avaliação 90 Dias, Vencimento Férias, Vencimento ASO e Vencimento Treinamento;
- **Privilégios:** **Gestor de área**, **Gestor de centro de custo** e **Gestor superior** (nome do gestor acima deste usuário, usado nos fluxos de aprovação).
Grave com **Alterar**.

## 14.2 Grupos de Usuários
Caminho no menu: Configurações › Grupos de Usuários
Cada grupo é um perfil de acesso: reúne as permissões que valem para todos os usuários vinculados a ele.
O campo **Nome do grupo** filtra a lista enquanto você digita. **Colunas:** Nome, Descrição, Usuários no grupo, Situação (Ativo/Inativo) e Ações, com **Editar** para alterar nome, permissões e ver quem usa o grupo.

### Criar um grupo
1. Clique em **+ Novo grupo**.
2. **Passo 1 — Dados do grupo:** informe o **Nome do grupo** * e a **Descrição** *. A chave **Situação** mantém o grupo ativo; desligada, bloqueia o uso do perfil sem apagar o cadastro.
3. **Passo 2 — Permissões:** as permissões ficam agrupadas por categoria (Acesso, Administração, Admissão, Avaliação, Cadastro, Cloud, Currículos, Entrevista, Histórico, Ocorrência, Planejamento, Pós-admissão, Relatório, Treinamento, Usuário, entre outras).
4. Use **Buscar** e **Categoria** para reduzir a lista. O botão de cada linha alterna a permissão; **Permitir todas** e **Remover todas** atuam sobre o que estiver filtrado.
5. Clique em **Criar grupo**.
Cada módulo costuma ter uma permissão de acesso e permissões separadas para cadastrar e alterar — por exemplo, “Acessar Ata Reunião”, “Pode cadastrar Ata de Reunião” e “Pode alterar uma Ata de Reunião”. Os contadores no topo mostram quantas permissões estão na lista e quantas pertencem ao grupo.
Permissões marcadas como **Permitida (obrigatória)** valem para todos os grupos e não podem ser removidas.
**Dica:** ao implantar o sistema, crie os grupos primeiro (por exemplo: RH, Gestores, Portaria, Treinamento) e só então cadastre os usuários, já vinculando cada um ao grupo certo.
