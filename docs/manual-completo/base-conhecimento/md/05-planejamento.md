# 5. Planejamento
O menu **Planejamento** concentra tudo o que precisa de aprovação antes de acontecer: a abertura de vagas e as movimentações de pessoal (demissão, férias, admissão prevista, valor extra, mudança de cargo, troca de contrato e transferência). Cada solicitação percorre um **fluxo de aprovação** visível no próprio card.

## 5.1 Como funciona o fluxo de aprovação
Toda solicitação nasce com o solicitante, passa pelo gestor e termina no RH. Se houver uma **aprovação extra** configurada para o tipo de processo (ver *Cadastro › Customizações › Aprovações Extras*), ela entra entre o gestor e o RH.
Solicitante*→*Gestor*→*Aprovação extra (se houver)*→*RH
- **Etapa em verde, com nome e data:** Etapa concluída — mostra quem aprovou e quando.
- **Etapa em laranja, “Aguardando”:** É a etapa atual: a solicitação está parada com esse aprovador.
- **Etapa em cinza, “Pendente”:** Etapa futura, ainda não alcançada.
- **Selo à direita do card:** Resume a situação: Em aberto, Pendente Gestor, Aprovado Gestor, Pendente Gerente de RH, Pendente RH, Aprovado RH.
As ações de aprovação ficam no menu ⋮ de cada card e só aparecem para quem tem permissão e quando a solicitação está na etapa correspondente.

## 5.2 Requisição de Vaga
Caminho no menu: Planejamento › Requisição de Vaga
É onde o gestor ou o RH formaliza a necessidade de contratação. Cada card mostra o cargo, a Quantidade, o Solicitante, o Gestor, a Lotação, o Centro de custo, a Área, a data da Solicitação, o tipo de Contratação, a Prioridade, a previsão de Início e o fluxo de aprovação.

### Filtros
- **Período:** Ao marcar a caixa, libera as datas inicial e final para buscar requisições abertas nesse intervalo.
- **Pesquisar:** Localiza pelo nome do cargo.
- **Status:** Mostra apenas as requisições em uma situação do fluxo (em aberto, aprovadas pelo gestor, aprovadas pelo RH…).
- **Ordenar por:** Define a ordem da lista; o padrão é Mais recentes.
Clique em **Buscar** para aplicar. **Exportar Excel** gera a planilha das requisições filtradas.

### Abrir uma requisição
1. Clique em **Solicitar**.
2. **Informações:** selecione o cargo * (digite e escolha na lista), a Área *, o Tipo de Contratação *, a Prioridade * e a Quantidade * de vagas.
3. **Lotação:** escolha a Lotação (CNPJ) *. O Centro de Custo * é liberado depois dessa escolha e lista apenas os centros daquela lotação.
4. **Demais informações:** Posição *, Processo *, Tipo *, Horário, Gestor da vaga *, se o cargo está no PPRA e PCMSO *, Salário *, Benefício * e Treinamento.
5. **Previsão de início:** ligue **Imediata** se a vaga é para já; caso contrário, informe a data em **Previsão** *.
6. Informe o **Solicitante** * e, se quiser, uma **Observação**.
7. Clique em **Cadastrar**. A requisição entra na lista como *Em aberto*, aguardando o gestor.
Se a sua empresa criou campos personalizados para a requisição, eles aparecem neste mesmo formulário.

### Ações de cada requisição
- **Visualizar:** Abre a requisição completa, somente para leitura, com o parecer de cada etapa.
- **Aprovação Gestor:** Abre a requisição para o gestor registrar a decisão.
- **Aprovação extra (nome configurado):** Aparece quando há uma etapa extra — no exemplo das telas, “Gerente de RH”.
- **Aprovação RH:** Decisão final do RH.
Na visualização, todos os campos aparecem preenchidos e bloqueados. Enquanto ninguém decidiu, o sistema avisa: “Esta solicitação ainda não foi aprovada ou reprovada pelo gestor!”.

### Aprovar ou reprovar
1. No menu ⋮, escolha a ação da sua etapa (por exemplo, **Aprovação Gestor**).
2. Confira os dados da requisição e role até o final da janela.
3. No bloco da sua etapa, escolha o **Status** * (aprovar ou reprovar) e escreva uma **Observação**. Os blocos das outras etapas ficam bloqueados.
4. Clique em **Aprovar** para gravar a decisão.

## 5.3 Movimentação
Caminho no menu: Planejamento › Movimentação
Reúne, em sete abas, as movimentações de pessoal que dependem de aprovação. Todas funcionam do mesmo jeito: filtros no topo, botão **Solicitar** para abrir uma nova solicitação, **Exportar Excel** e um card por solicitação, com o fluxo de aprovação.
- **Demissão:** Solicitar o desligamento de um colaborador.
- **Férias:** Programar as férias de um colaborador.
- **Admissão:** Registrar uma admissão prevista (contratação planejada).
- **Liderança de Pessoal e Valor Extra:** Solicitar pagamento adicional por liderança ou outro valor extra.
- **Mudança de Cargo:** Alterar cargo, função ou salário.
- **Mudança de Intermitente p/ Fixo:** Converter um contrato intermitente em fixo.
- **Transferência:** Mudar o colaborador de centro de custo ou de lotação.

### Filtros comuns a todas as abas
- **Período:** Ao marcar, libera as datas para buscar solicitações de um intervalo.
- **Colaborador / CPF:** Localiza pelo nome ou pelo CPF do colaborador.
- **Status:** Situação no fluxo (pendente gestor, pendente RH, aprovado, reprovado…).
- **Lotação (CNPJ):** Solicitações de uma matriz ou filial.
- **Centro de custo:** Solicitações de um centro de custo.
- **Ordenar por:** Ordem da lista; o padrão é Mais recentes.
- **Por página:** Quantidade de solicitações exibidas por página.
O menu ⋮ de cada card traz **Visualizar** e as ações de aprovação da etapa atual (por exemplo, **Aprovação RH**).

### Aba Demissão
Cada card mostra a Data da demissão, o Tipo de aviso, o Cargo, a Lotação, o Centro de custo e o Gestor responsável.
Além de Visualizar e Aprovação RH, a demissão tem a ação **Aviso Prévio (PDF)**, que gera o documento de aviso prévio.

#### Solicitar uma demissão
1. Clique em **Solicitar**.
2. Em **Colaborador** *, digite o nome e selecione. **Centro de Custo Atual** e **Lotação** são preenchidos automaticamente.
3. Informe a **Data da Demissão** * e o **Tipo de Aviso** *.
4. Em **Gestor Aprovação** *, selecione o gestor que deve aprovar.
5. Em **Detalhes**, escreva a **Observação** e inclua **Anexos** (arraste o arquivo ou clique em Escolher).
6. Clique em **Cadastrar**.

### Aba Férias
Cada card mostra o período de Férias, a quantidade de Dias (e o saldo), o Período aquisitivo com a data limite, a Lotação, o Centro de custo e a data de Admissão.
Esta aba tem filtros próprios, além dos comuns:
- **Período aquisitivo:** Restringe aos períodos aquisitivos mais recentes; o padrão é Últimos 3 períodos.
- **Período cadastrado:** Solicitações cadastradas entre duas datas.
- **Período de vencimento:** Férias cujo prazo limite vence entre duas datas — útil para evitar férias vencidas.
- **Início das férias:** Férias que começam entre duas datas.

#### Solicitar férias
1. Clique em **Solicitar** e selecione o **Colaborador** *.
2. Escolha o **Período Aquisitivo** *.
3. Indique em **Tem Falta?** se há faltas no período. O sistema calcula a **Quantidade de dias disponíveis**.
4. Escolha os **Dias de férias** * e informe a **Data da saída** *. A **Data do retorno** e os **Dias de saldo** são calculados automaticamente.
5. Indique se haverá **Abono Pecuniário** e **Adiantamento do Décimo Terceiro**.
6. Selecione o **Gestor Aprovação** *, complete a observação e os anexos e clique em **Cadastrar**.

### Aba Admissão
Registra as admissões previstas. Cada card mostra a Data de admissão, o Tipo de contrato, o Salário, a Lotação, o Centro de custo e o Solicitante. Esta aba tem um filtro a mais: **Tipo contrato**.

#### Solicitar uma admissão
1. Clique em **Solicitar**.
2. Em **Colaborador**, selecione o **Cargo** * e, se já souber, informe o **Nome do Colaborador** a ser admitido.
3. Em **Lotação**, escolha a Lotação (CNPJ) * e o Centro de Custo *.
4. Em **Solicitação**, informe o **Tipo de admissão** *, a **Data admissão** *, o **Salário** * e o **Gestor Aprovação** *.
5. Complete observação e anexos e clique em **Cadastrar**.

### Aba Liderança de Pessoal e Valor Extra
Cada card mostra o Colaborador, o Tipo do valor extra, o Período (em dias), a Lotação, o Centro de custo e o Solicitante.

#### Solicitar
1. Clique em **Solicitar** e selecione o **Colaborador** *. A Lotação é preenchida automaticamente.
2. Escolha o **Centro de Custo**.
3. Informe o **Tipo** * do valor extra e o **Período em dias** *.
4. Selecione o **Gestor Aprovação** *, complete observação e anexos e clique em **Cadastrar**.

### Aba Mudança de Cargo
Cada card mostra a Lotação, o Centro de custo, as **Alterações** pedidas (por exemplo, Função · Salário), o Solicitante e as datas.
Para solicitar, clique em **Solicitar** e selecione primeiro o **Colaborador** *. Depois dessa escolha, o formulário mostra a situação atual do colaborador e os campos para informar o novo cargo, a nova função e o novo salário. Grave com **Cadastrar**.

### Aba Mudança de Intermitente p/ Fixo
Cada card mostra o Cargo anterior, o Novo cargo, o Centro de custo, a Lotação, o Salário anterior, o Novo salário e o Solicitante.

#### Solicitar
1. Clique em **Solicitar** e selecione o **Colaborador** *. **Cargo anterior** e **Salário anterior** são preenchidos automaticamente.
2. Escolha a **Lotação (CNPJ)** * e o **Centro de Custo** *.
3. Informe o **Novo cargo** * e o **Novo salário**.
4. Selecione o **Gestor Aprovação** *.
5. Descreva os **Motivos para modificação**, inclua anexos e clique em **Salvar**.

### Aba Transferência
Cada card mostra o Centro de custo de origem e o de destino, a Data da transferência, a Lotação de origem e a de destino e o Solicitante.

#### Solicitar uma transferência
1. Clique em **Solicitar** e selecione o **Colaborador** *.
2. Confirme a **Lotação origem** * e o **Centro de Custo Origem** *.
3. Escolha a **Lotação destino** * e o **Centro de Custo Destino** *.
4. Informe a **Data para transferência** *.
5. Em **Gestores responsáveis**, o sistema mostra automaticamente o gestor da origem e o do destino, conforme o cadastro de centros de custo.
6. Complete observação e anexos e clique em **Salvar**.
**Importante:** na transferência, os aprovadores são os gestores cadastrados nos centros de custo de origem e de destino. Se um centro de custo estiver sem gestor, corrija o cadastro em **Cadastro › Centro de Custos** antes de solicitar.

## 5.4 Mobilização
Caminho no menu: Planejamento › Mobilização
Acompanha a mobilização de pessoal por projeto. A tela só exibe dados depois que um projeto é escolhido.
- **Projeto:** Obrigatório. Escolha o projeto que deseja acompanhar.
- **Período:** Opcional. Ao marcar, restringe a consulta a um intervalo de datas.
Selecione o projeto e clique em **Buscar**.
