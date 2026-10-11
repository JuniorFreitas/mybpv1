# 4. Cadastros
Os cadastros são a base do sistema: cargos, áreas, centros de custo, benefícios, treinamentos e avaliações definidos aqui aparecem como opções em todos os outros módulos. Uma requisição de vaga, por exemplo, só pode ser aberta para um cargo, uma área e um centro de custo já cadastrados. Comece a implantação por este menu.
**Ordem sugerida de implantação:** Centro de Custos → Áreas → Cargos → Benefícios → Exames (clínicas e PCMSO) → Lista de Treinamentos → Vagas Abertas → Avaliações.

## 4.1 Áreas
Caminho no menu: Cadastro › Áreas
As áreas representam os setores da empresa. Cada uma tem um **Gestor responsável**, um **Centro de custo** e o telefone do **Supervisor**, que é impresso na etiqueta do colaborador.
- **Buscar:** Localiza pelo nome da área ou pelo número (ID) que aparece no selo do card.
- **Status:** Ativo, Inativo ou todos os status.

### Cadastrar ou editar
1. Clique em **+ Cadastrar Área**, ou em ⋮ › **Editar** no card da área.
2. Informe o **Nome** *.
3. Em **Contato supervisor para etiqueta**, informe o telefone do supervisor.
4. Em **Gestor responsável**, digite o nome e selecione na lista; o campo fica com ✓ verde quando o gestor foi reconhecido.
5. Escolha o **Centro de Custo** da área.
6. Mantenha **Ativo** ligado e clique em **Alterar** (ou **Cadastrar**).

## 4.2 Departamentos
Caminho no menu: Cadastro › Departamentos
Lista simples de departamentos. Os filtros são **Buscar** (por nome) e **Status** (*Apenas Ativos*, *Apenas Inativos* ou *Todos*). Para incluir, clique em **+ Departamento**, informe o **Nome** e mantenha **Ativo** ligado.

## 4.3 Centro de Custos
Caminho no menu: Cadastro › Centro de Custos
Cada centro de custo pertence a uma **Lotação** (o CNPJ da matriz ou de uma filial) e tem um **Gestor principal** e um **Gestor substituto**.
**Importante:** o gestor principal e o substituto definidos aqui são usados automaticamente no fluxo de aprovação da transferência prevista (Planejamento › Movimentação). Mantenha os gestores sempre atualizados.
- **Buscar:** Localiza pelo nome ou pelo ID do centro de custo.
- **Status:** Ativo, Inativo ou todos.
- **Por CNPJ:** Mostra apenas os centros de custo de uma lotação (matriz ou filial). Clique no campo para escolher.

### Cadastrar ou editar
1. Clique em **+ Cadastrar** ou em ⋮ › **Editar**.
2. Informe o **Nome** *.
3. Em **Gestor responsável**, digite e selecione o gestor principal.
4. Em **Gestor substituto**, selecione quem assume a aprovação quando o gestor principal estiver indisponível.
5. Em **CNPJ** *, selecione a matriz ou a filial à qual o centro de custo pertence.
6. Clique em **Salvar**.

## 4.4 Benefícios
Caminho no menu: Cadastro › Benefícios
Catálogo dos benefícios que podem ser oferecidos nas vagas e concedidos aos colaboradores. Cada card mostra o Tipo, o Valor, a Periodicidade, a forma de **Aplicação** e o **Desconto**.
- **Buscar:** Localiza pelo nome do benefício ou pelo tipo.
- **Tipo de benefício:** Mostra apenas os benefícios de um tipo.

### Cadastrar ou editar
1. Se o tipo ainda não existe, clique em **Tipo de Benefício** e cadastre-o (nome e situação).
2. Clique em **+ Cadastrar Benefício** ou em ⋮ › **Editar**.
3. Informe o **Nome** e escolha o **Tipo**.
4. Informe o **Valor** e a **Aplicação**: *Em Reais* (valor fixo) ou *Percentual do Salário*.
5. Escolha a **Periodicidade**: Diário, Semanal, Quinzenal, Mensal ou Anual.
6. Informe o **Valor Descontado** do colaborador e a **Opção de Desconto**: *Valor Fixo* ou *Percentual do Salário*.
7. Clique em **Alterar** (ou **Cadastrar**).

## 4.5 Cargos
Caminho no menu: Cadastro › Cargos
Lista de cargos da empresa. Cada card mostra o **CBO** vinculado e a quantidade de **treinamentos do cargo**.
- **Buscar:** Localiza pelo nome do cargo ou pelo ID.
- **Status:** Ativo, Inativo ou todos. Cargos inativos não aparecem para escolha em novas vagas e requisições.

### Cadastrar ou editar
1. Clique em **+ Cadastrar Cargo** ou em ⋮ › **Editar**.
2. Em **Sobre o cargo**, informe o **Título do cargo** * (por exemplo, “Analista de RH”).
3. Mantenha **Ativo** ligado. Para tirar um cargo de uso sem apagá-lo, desligue a chave.
4. Em **CBO** (opcional), busque pelo código, título ou família da Classificação Brasileira de Ocupações e selecione.
5. Clique em **Alterar** (ou **Cadastrar**).
**Treinamentos do cargo são somente leitura nesta tela.** O vínculo entre treinamento e cargo é feito em **Cadastro › Lista de Treinamentos**; aqui aparece apenas o resumo do que está configurado.

## 4.6 Vagas Abertas
Caminho no menu: Cadastro › Vagas Abertas
É aqui que uma vaga é publicada para receber candidatos. Cada vaga aparece no portal de vagas da empresa (seção 6.1) e gera um **link público** de candidatura, que pode ser copiado ou compartilhado direto no WhatsApp, Facebook, Instagram e LinkedIn pelos ícones abaixo do link.
- **Buscar:** Localiza pelo título da vaga, cargo, cidade ou ID.
- **Ativo no site:** Vagas publicadas (ou não) no site público de vagas.
- **Ativo no sistema:** Vagas liberadas (ou não) para uso nos processos internos.
- **Cargo:** Vagas de um cargo específico.
- **Cidade:** Vagas de uma cidade — a lista traz apenas as cidades que têm vaga aberta.
- **Provas vinculadas:** Vagas com ou sem prova vinculada.
- **Projeto:** Vagas vinculadas a um projeto.
Em cada card você vê o Cargo, o Local, a data de atualização, a Descrição e três indicadores: **Treinamentos do cargo**, **Provas vinculadas** e **Projetos vinculados**. Os selos **Ativo site** e **Ativo sistema** mostram onde a vaga está valendo.

### Cadastrar ou editar
1. Clique em **+ Cadastrar Vaga Aberta** ou em ⋮ › **Editar**.
2. Em **Cargo** *, digite e selecione o cargo. A faixa **Treinamentos vinculados ao cargo** pode ser aberta para conferir os treinamentos exigidos.
3. Informe o **Título** * — é o nome que o candidato verá.
4. Escreva a **Descrição** no editor (atividades, requisitos, benefícios).
5. Em **Cidade** *, digite e selecione o município.
6. Em **Projetos** (opcional), clique em **+ Adicionar** para vincular a vaga a um ou mais projetos, respeitando a quantidade disponível em cada um.
7. Em **Provas** (opcional), clique em **+ Adicionar** para vincular provas que o candidato fará.
8. Defina a publicação com as chaves **Ativo no site** e **Ativo no sistema**.
9. Clique em **Alterar** (ou **Cadastrar**).

## 4.7 Projetos
Caminho no menu: Cadastro › Projetos
Um projeto define uma **capacidade** de posições a preencher e permite distribuir essa capacidade entre vagas abertas.
- **Buscar:** Localiza pelo nome ou ID do projeto.
- **Disponibilidade:** Projetos com vagas ainda disponíveis ou já esgotados.
- **Vagas abertas:** Projetos com ou sem vagas abertas vinculadas.
Os números do card:
- **Capacidade** — tamanho total do projeto;
- **Distribuído** — quanto já foi reservado em vagas abertas;
- **Disponível** — quanto ainda pode ser vinculado a novas vagas;
- **Ocupadas** — posições já contratadas.
O selo **Esgotado** aparece quando toda a capacidade já foi distribuída.

### Cadastrar ou editar
1. Clique em **+ Cadastrar Projeto** ou em ⋮ › **Editar**.
2. Informe o **Nome do projeto** * e a **Capacidade total** *. A capacidade não pode ser menor do que o total já distribuído.
3. Em **Distribuição por vaga aberta** (opcional), cada linha reserva parte da capacidade para uma vaga. Ajuste a quantidade em **Reservadas neste vínculo**; o ✕ vermelho remove o vínculo.
4. Clique em **Alterar**.
**Restrição por vagas ocupadas:** se já existem contratados em um vínculo, a reserva não pode ficar abaixo dessa quantidade, e o vínculo não pode ser removido.

## 4.8 Exames (clínicas e PCMSO)
Caminho no menu: Cadastro › Exames
Cadastro das clínicas de medicina ocupacional para onde os colaboradores são encaminhados. O e-mail cadastrado aqui é o que recebe os encaminhamentos de exame.
- **Buscar:** Localiza pela razão social, nome fantasia, CNPJ ou ID.
- **Status:** Apenas Ativos, Apenas Inativos ou todos.

### Cadastrar ou editar uma clínica
Em **+ Cadastrar** ou ⋮ › **Editar**, informe CNPJ, Razão Social e Nome Fantasia; o endereço (CEP * com busca pela lupa, Endereço *, Número, Complemento, Bairro *, Município *, UF *); o Telefone e o E-mail. Mantenha **Ativo** ligado.

### PCMSO
O botão **+ PCMSO** abre a lista de programas de controle médico (PCMSO) usados no encaminhamento de exames. A janela tem busca por nome, filtro de Status, o botão **+ Cadastrar** e o ícone de lápis para editar cada programa.

## 4.9 Instrutor
Caminho no menu: Cadastro › Instrutor
Instrutores que ministram treinamentos e assinam os certificados. A listagem tem busca por nome. No cadastro (**+ Instrutor**) informe o **Nome**, o **Cargo**, o **Registro** profissional, a situação (**Ativo**: Sim/Não) e envie o arquivo da **Assinatura**, que será impressa nos certificados.

## 4.10 Lista de Treinamentos
Caminho no menu: Cadastro › Lista de Treinamentos
Catálogo de treinamentos da empresa. É aqui que se define a validade de cada treinamento e para quais cargos ele é exigido.
- **Buscar:** Localiza pelo nome, descrição ou ID do treinamento.
- **Status:** Ativo, Inativo ou todos.
- **Segmento:** Treinamentos de um segmento ou padrão (por exemplo, os exigidos por um cliente).
Cada card mostra o **Padrão** (segmento), os **Cargos vinculados**, o **Prazo fixo** de validade em dias, a **Ordem** de exibição, **A quem se destina** e o nome usado na **Carteira de treinamento**.

### Cadastrar ou editar
1. Clique em **+ Treinamento Indústria** ou em ⋮ › **Editar**.
2. Informe o **Nome** *.
3. Ligue **Exibir Carteira Treinamento** se ele deve ser impresso na carteira e informe o **Nome Reduzido** *, que é o texto impresso.
4. Preencha **A quem se destina** *.
5. Informe o **Prazo fixo (dias para vencimento)** — por exemplo, 365 — e a **Ordem** em que aparece nas listas.
6. Escolha o **Segmento / Padrão de treinamento**.
7. Ligue **Vincular a todos os cargos** se o treinamento vale para qualquer cargo. Desligado, escolha os cargos na lista que aparece.
8. Mantenha **Ativo** e clique em **Salvar**.

### Assinatura da carteira
O botão **+ Assinatura Carteira** lista as assinaturas impressas na carteira de treinamento, com Nome, Tipo (*Gerente/RH* ou *Técnico de Segurança*), Segmento e situação. Use **+ Cadastrar** para incluir e o lápis para editar.

## 4.11 Certificados
Caminho no menu: Cadastro › Certificados
Modelos de certificado emitidos pelo sistema (por exemplo NR-33 e NR-35). A listagem mostra Nome, Título do certificado, Carga horária e Validade, com busca por nome.
No cadastro (**+ Treinamento**) informe o **Nome**, o **Título Certificado**, o **Conteúdo Abordado**, o **Conteúdo Programático**, a **Carga Horária** (em horas) e a **Validade** (em dias). Esses textos são impressos no certificado.

## 4.12 Empresa Treinamento
Caminho no menu: Cadastro › Empresa Treinamento
Empresas que ministram treinamentos. Listagem com busca por nome; no cadastro (**+ Empresa Treinamento**) informe **Nome**, **Endereço** e a situação (**Ativo**: Sim/Não).

## 4.13 Provas
Caminho no menu: Cadastro › Provas
Provas aplicadas aos candidatos e vinculadas às vagas abertas. Listagem com busca por título. Em **+ Cadastrar** (janela “Montagem da Prova”), informe o **Título**, o **Tipo da prova** (*Objetiva* ou *Subjetiva*) e a situação. Depois de escolher o tipo, monte as questões.

## 4.14 Avaliações
Caminho no menu: Cadastro › Avaliações
Caminho no menu: Cadastro › Peformance
Os grupos **Avaliações** e **Peformance** têm exatamente as mesmas quatro telas e são usados para ciclos diferentes de avaliação. Em ambos, a montagem segue esta ordem:
1. Tipos de Avaliadores*→*2. Tipos de Avaliações*→*3. Competências*→*4. Avaliações

### Tipos de Avaliadores
Definem quem pode avaliar: *Par* (colegas do mesmo nível), *Gestão* (líder avaliando o liderado), *Autoavaliação* e *Subordinado* (liderado avaliando o líder). Busca por nome; cadastro com **Nome**, **Descrição** e **Ativo**.

### Tipos de Avaliações
São os modelos de avaliação (desempenho, experiência, liderança, 360°…). Busca por nome; cadastro com **Nome**, **Descrição** e **Ativo**.

### Competências
As competências são os tópicos avaliados. A listagem mostra a Competência, a quantidade de indicadores e o tipo de avaliação a que pertence.
Em **+ Cadastrar** (“Montagem de competência”), informe a **Competência**, a **Descrição**, o **Tipo de avaliação** em que ela será usada e a situação. Depois inclua os indicadores (as perguntas) da competência.

### Avaliações (ciclos)
Cada linha é um ciclo de avaliação, com Ano, Título, Tipo, Data início, Data fim, Status e situação.
- **Buscar:** Localiza pelo título do ciclo.
- **Ano Avaliação:** Ciclos de um ano específico.
- **Tipo Avaliação:** Ciclos de um tipo de avaliação.
- **Status:** Aguardando Início, Aberta ou Encerrada.
**Exportar Excel** gera a planilha dos ciclos filtrados.

#### Montar um ciclo
1. Clique em **+ Cadastrar**.
2. Em **Informações**, preencha o **Título**, o **Tipo de avaliação**, a **Data Início** e a **Data Fim**, o **Status** (Aguardando Início, Aberta ou Encerrada), se há **Auto Avaliação**, se está **Ativo** e o **Ano**.
3. Em **Fluxo**, defina se o avaliador final verá as notas das etapas anteriores (**Mostrar notas das etapas anteriores para o Avaliador Final**).
4. Em **Tipo de avaliador**, escolha cada etapa do fluxo, na ordem em que deve acontecer. Se Auto Avaliação estiver como *Sim*, ela ocupa automaticamente a primeira posição.
5. Clique em **Cadastrar**.

#### Associar avaliadores
1. Na listagem, abra ⋮ › **Associar avaliadores** no ciclo desejado.
2. Localize os colaboradores pela busca ou filtre por **Vinculados** / **Não vinculados**.
3. Marque os colaboradores e clique em **Associar Avaliadores** para indicar quem avalia cada um em cada etapa do fluxo.
**Atenção:** só é permitido associar avaliadores enquanto o ciclo está com status **Aguardando Início**.

## 4.15 Customizações

### Campos personalizados da Requisição de Vaga
Caminho no menu: Cadastro › Customizações › Requisição de Vaga
Permite criar campos extras no formulário de requisição de vaga, de acordo com a necessidade da sua empresa — por exemplo, “E-mail institucional”, “Notebook” ou “Acesso a sistemas”.
1. Clique em **+ Novo campo**.
2. Informe o **Nome do campo (label)** *, que é o texto exibido no formulário.
3. Escolha o **Tipo** *: *Sim / Não*, *Texto simples*, *Caixa de texto* (várias linhas) ou *Select* (lista de opções que você define).
4. Marque **Campo obrigatório** se o preenchimento for exigido e clique em **Salvar**.

### Aprovações Extras
Caminho no menu: Cadastro › Customizações › Aprovações Extras
Inclui uma etapa adicional de aprovação entre o gestor e o RH para um tipo de processo.
Gestor*→*Aprovação Extra*→*RH (final)
Cada card mostra o tipo de processo, o nome da aprovação, as datas de criação e alteração, os **usuários autorizados** e o fluxo resultante. A busca localiza por nome ou tipo.
1. Clique em **+ Cadastrar** (ou ⋮ › **Editar**).
2. Em **Tipo de Processo** *, escolha onde a etapa será incluída: Demissão, Férias, Mudança de Cargo, Transferência, Intermitente para Fixo, Valor Extra, Requisição de Vaga ou Admissão Prevista.
3. Informe o **Nome da Aprovação** * (por exemplo, “SESMT” ou “Gerente”). Ele aparece no fluxo como “Aguardando aprovação do …”.
4. Em **Colaborador**, selecione os usuários autorizados a aprovar essa etapa. Usuários com privilégio de RH também podem aprovar.
5. Mantenha **Ativo** marcado e clique em **Salvar**.
**Importante:** o RH é sempre a última aprovação, e apenas uma configuração pode estar ativa por tipo de processo.
