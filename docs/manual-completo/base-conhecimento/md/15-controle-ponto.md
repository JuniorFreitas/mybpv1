# 15. Controle de Ponto
O menu **Controle de Ponto** cobre todo o ciclo da jornada: as regras da empresa, os locais onde o ponto pode ser batido, as escalas, o registro diário pelo colaborador, a conferência pelo gestor e a emissão das folhas de ponto.
Configurar regras e perímetros*→*Cadastrar escalas*→*Associar aos colaboradores*→*Registrar ponto*→*Ajustar jornadas*→*Folha de ponto

## 15.1 Configurações
Caminho no menu: Controle de Ponto › Configurações
Define as regras gerais do ponto da empresa. A tela tem duas abas: **Controle de ponto** e **Perímetros**.
- **Regime de compensação de horários:** Horas extras, Banco de horas ou Híbrido.
- **Tempo de tolerância para entrada e saída:** Minutos de tolerância antes de considerar hora extra ou banco de horas.
- **Tempo limite para falta:** Tempo máximo, em minutos, até o atraso ser considerado falta.
- **Tempo limite para saída:** Minutos após o fim da jornada a partir dos quais o registro deve ser verificado pelo gestor.
- **Início de nova frequência:** Dia do mês em que começa uma nova ficha de frequência.
Grave com **Salvar**.

### Perímetros
Perímetros são os locais onde o colaborador pode registrar o ponto. A aba lista os **perímetros cadastrados**, com busca por descrição e os botões **Editar** e **Excluir**.
1. Clique em **Adicionar perímetro**.
2. Informe a **Descrição** (nome do local).
3. Em **Registrar ponto em um local específico**, escolha *Sim* para exigir que o ponto seja batido dentro do perímetro, ou *Não* para casos de home office.
4. Use **Buscar por endereço** para posicionar o mapa. Arraste o círculo para ajustar o ponto exato; **Latitude**, **Longitude** e **Raio** (em metros) são preenchidos pelo mapa.
5. Em **Ajuste do perímetro**, aumente ou reduza o raio, e clique em **Cadastrar**.
Na mesma aba, o bloco **Associar perímetro aos colaboradores** lista os colaboradores e o perímetro de cada um: marque os colaboradores, clique em **Associar perímetro** e escolha o local.

## 15.2 Ocorrências de jornada
Caminho no menu: Controle de Ponto › Ocorrências
Cadastro dos tipos de ocorrência usados para classificar cada dia da jornada (férias, atestado, folga, falta…). A lista mostra a **Descrição**, se é **Dia trabalhado** e se **Conta horas**.
Em **Cadastrar** (ou **Alterar**), informe:
- **Descrição** — nome da ocorrência;
- **Dia trabalhado** — se o dia conta como trabalhado;
- **Conta horas** — se entra no saldo de horas;
- **Ativo** — se está disponível para uso.

## 15.3 Feriados
Caminho no menu: Controle de Ponto › Feriados
Feriados considerados no cálculo da jornada. Busque pelo nome ou clique em **Cadastrar** e informe **Descrição**, **Data** e **Ativo**.

## 15.4 Escalas
Caminho no menu: Controle de Ponto › Escalas
A escala define os horários previstos de trabalho. A tela tem duas partes: as escalas cadastradas e a associação das escalas aos colaboradores.

### Criar uma escala
1. Clique em **Adicionar escala**.
2. Informe a **Descrição** e a **Data inicial** (a partir de quando a sequência de jornadas começa a contar).
3. Em cada **Jornada**, defina **Repetir por (dias)** e a **Ocorrência**: *Dia trabalhado*, *Folga* ou *Jornada externa*.
4. Preencha os **períodos trabalhados**: hora de entrada e de saída. O sistema calcula o descanso entre períodos e as horas trabalhadas. **Adicionar período** inclui mais um período no mesmo dia.
5. Use **Adicionar jornada** para montar sequências (por exemplo, 5 dias de trabalho e 2 de folga, ou 12x36).
6. Clique em **Cadastrar**.
**Escala circular:** as jornadas se repetem em sequência a partir da data inicial. Com um único tipo de jornada, ela se repete todos os dias.

### Associar escala aos colaboradores
1. Role até **Associar escala aos colaboradores** e, se quiser, busque pelo nome.
2. Marque os colaboradores na lista. A coluna **Escala** mostra a escala atual de cada um.
3. Clique em **Associar escala** e escolha a escala.

## 15.5 Ponto Eletrônico
Caminho no menu: Controle de Ponto › Ponto Eletrônico
É a tela em que o próprio colaborador bate o ponto. A aba **Marcações do dia** mostra a data e a hora atuais e os registros já feitos; **Histórico** traz os dias anteriores.
Para registrar, clique em **Registrar ponto**. Se o colaborador estiver vinculado a um perímetro com local obrigatório, o registro só é aceito dentro da área definida.

## 15.6 Ajustar Jornadas
Caminho no menu: Controle de Ponto › Ajustar Jornadas
Tela de conferência do gestor ou do RH. As jornadas ficam separadas em três abas: **Jornadas pendentes**, **Jornadas incompletas** e **Jornadas verificadas**.
- **Intervalo:** Período das jornadas (por padrão, o mês atual).
- **Colaborador:** Jornadas de um funcionário.
**Colunas:** Dia, Funcionário, Ocorrência, Entrada, Saída, Trabalhado (com o saldo do dia) e Verificado. **Limpar** remove os filtros.

### Verificar ou corrigir uma jornada
1. Clique em **Verificar** na linha da jornada. A janela **Detalhes da jornada** mostra o colaborador e a data; a seta ao lado da data passa para a próxima.
2. Em **Ocorrência**, classifique o dia: Dia trabalhado, Abono, Afastado, Atestado, Falta, Falta BH, Feriado, Folga, Férias, Jornada externa, Suspensão ou Suspensão de contrato.
3. Compare a **Jornada prevista** com a **Jornada realizada**. Corrija as horas de entrada e saída ou use **Adicionar período ao início** / **Adicionar período ao fim**.
4. Escreva a **Justificativa** do ajuste.
5. Clique em **Ajustar**. Com **Ajustar e buscar o próximo** marcado, a próxima jornada pendente abre em seguida.

## 15.7 Folha de Ponto
Caminho no menu: Controle de Ponto › Folha de Ponto
Consulta e impressão da frequência de cada colaborador.
- **Buscar:** Localiza pelo nome do colaborador.
- **Por Escala:** Colaboradores de uma escala.
- **Status:** Admitidos ou Demitidos.
**Detalhes** abre a folha do colaborador, com os dados pessoais, o filtro de **Intervalo** e a tabela **Frequência**: Data, Escala, Períodos trabalhados, e as horas Prevista, Normal, Noturna, Extra e Negativa de cada dia.
No fim da folha ficam os totais do período: **Faltas**, **Horas normais**, **Horas noturnas**, **Horas extra**, **Horas negativas** e **Saldo**. **Imprimir** gera a folha do intervalo escolhido.

## 15.8 Folha de Ponto Manual
Caminho no menu: Controle de Ponto › Folha de Ponto Manual
Gera folhas de ponto em branco, para preenchimento à mão, dos colaboradores selecionados.
- **Pesquisar:** Localiza pelo nome do colaborador.
- **Por Centro de Custo:** Colaboradores de um centro de custo.
- **Por Cargo:** Colaboradores de um cargo.
- **Status:** Admitidos ou Demitidos.
- **Exibir:** 20, 50, 100 ou 150 por página.
Marque os colaboradores (ou a caixa do cabeçalho para todos) e clique em **Gerar folha**; o contador mostra quantos estão selecionados. **Limpar seleção** desmarca todos.

## 15.9 Relatório Sintético
Caminho no menu: Controle de Ponto › Relatório Sintético
Resumo mensal do ponto por empresa. Escolha o **CNPJ**, o **Mês** e o **Ano** e clique em **Gerar relatório**.
