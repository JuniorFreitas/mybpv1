# Exemplo de Exportação - Avaliações Completas

## Estrutura do CSV

### Cabeçalhos (37 colunas)
```
ID Avaliação;Título Avaliação;Tipo de Avaliação;Status Avaliação;Ano Avaliação;Data Início;Data Fim;Auto Avaliação;Tipo PJ;Ativo;Data Criação Avaliação;ID Feedback;Status Feedback;Origem Feedback;Avaliador Principal;ID Funcionário;Nome Funcionário;Login Funcionário;Matrícula;CPF;Cargo;Área;Centro de Custo;Data Admissão;ID Avaliador;Nome Avaliador;Login Avaliador;Comentário Avaliador;Nota Final;Data Início Feedback;Data Fim Feedback;Total Respostas;Média Geral
```

## Exemplo de Dados

### Cenário 1: Avaliação com Múltiplos Feedbacks

**Avaliação de Desempenho 2025**
- Uma avaliação
- 3 funcionários avaliados
- Cada um com auto-avaliação + avaliação do gestor
- Total: 6 linhas no CSV

#### Linha 1 - Auto-avaliação João
```csv
1;Avaliação de Desempenho 2025;Desempenho Anual;Aberta;2025;01/01/2025;31/01/2025;Sim;CLT;Sim;15/10/2025 10:30:00;101;Concluida;Funcionário;Não;1001;João da Silva;joao.silva@empresa.com;2024001;123.456.789-00;Analista de Sistemas;TI;São Paulo - Matriz;10/01/2024;1001;João da Silva;joao.silva@empresa.com;Busco sempre melhorar minhas habilidades técnicas;N/A;10/01/2025 09:00:00;10/01/2025 10:30:00;15;4.2
```

#### Linha 2 - Avaliação do Gestor sobre João
```csv
1;Avaliação de Desempenho 2025;Desempenho Anual;Aberta;2025;01/01/2025;31/01/2025;Sim;CLT;Sim;15/10/2025 10:30:00;102;Concluida;Avaliador;Sim;1001;João da Silva;joao.silva@empresa.com;2024001;123.456.789-00;Analista de Sistemas;TI;São Paulo - Matriz;10/01/2024;2001;Maria Santos;maria.santos@empresa.com;Excelente profissional com grande capacidade técnica e proatividade;N/A;12/01/2025 14:00:00;12/01/2025 15:30:00;15;4.5
```

#### Linha 3 - Auto-avaliação Maria
```csv
1;Avaliação de Desempenho 2025;Desempenho Anual;Aberta;2025;01/01/2025;31/01/2025;Sim;CLT;Sim;15/10/2025 10:30:00;103;Concluida;Funcionário;Não;1002;Maria Oliveira;maria.oliveira@empresa.com;2024002;987.654.321-00;Analista Financeiro;Financeiro;São Paulo - Matriz;15/02/2024;1002;Maria Oliveira;maria.oliveira@empresa.com;Tenho me dedicado ao aprendizado de novas ferramentas;N/A;11/01/2025 08:00:00;11/01/2025 09:15:00;15;4.0
```

### Cenário 2: Avaliação sem Feedbacks

**Avaliação Aguardando Início**

```csv
2;Avaliação 360 Graus - Q2 2025;Avaliação 360;Aguardando Inicio;2025;01/04/2025;30/04/2025;Sim;PJ;Sim;15/10/2025 11:00:00;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;N/A;0;0
```

## Casos de Uso Comuns

### 1. Análise de Desempenho por Área
**Filtros aplicados**: Ano = 2025, Status = Encerrada

Permite analisar:
- Média geral por área
- Funcionários mais bem avaliados
- Áreas com maior número de avaliações
- Comparação entre auto-avaliação e avaliação do gestor

### 2. Auditoria de Avaliações
**Filtros aplicados**: Todos os dados

Permite verificar:
- Quem avaliou cada funcionário
- Datas de realização das avaliações
- Comentários dos avaliadores
- Avaliações pendentes

### 3. Relatório Gerencial
**Filtros aplicados**: Tipo = Desempenho Anual, Ano = 2025

Gera relatório com:
- Total de funcionários avaliados
- Distribuição de notas
- Taxa de conclusão
- Tempo médio de resposta

### 4. Exportação por Tipo PJ
**Filtros aplicados**: Tipo PJ = PJ

Separa dados de:
- Colaboradores PJ
- Avaliações específicas para PJ
- Análise separada de performance

## Campos Detalhados

### Status do Feedback
- **Pendente**: Avaliação ainda não iniciada
- **Concluida**: Avaliação finalizada
- **Finalizada**: Feedback final dado pelo gestor

### Origem do Feedback
- **Funcionário**: Auto-avaliação
- **Avaliador**: Avaliação feita por gestor, par ou outro avaliador

### Avaliador Principal
- **Sim**: É o gestor direto responsável pela avaliação final
- **Não**: Avaliador secundário (par, auto-avaliação, etc.)

### Cálculo da Média Geral
```
Média Geral = Soma de todas as notas / Total de respostas
```

Exemplo:
- 15 questões respondidas
- Notas: [5, 4, 5, 4, 5, 4, 4, 5, 4, 5, 4, 4, 5, 4, 4]
- Soma: 66
- Média: 66 / 15 = 4.4

## Importação em Excel

### Passos para abrir corretamente:
1. Abrir Excel
2. Ir em **Dados > Obter Dados > De Arquivo > De Texto/CSV**
3. Selecionar o arquivo exportado
4. Configurar:
   - **Origem do arquivo**: 65001 (Unicode UTF-8)
   - **Delimitador**: Ponto e vírgula
5. Clicar em **Carregar**

### Formatação Recomendada:
- **Colunas de data**: Formato dd/mm/yyyy
- **Colunas numéricas**: Formato número com 2 casas decimais
- **Congelar primeira linha**: Para manter cabeçalhos visíveis
- **Filtros**: Ativar filtros automáticos

## Análises Possíveis

### Com Tabela Dinâmica (Pivot Table)

#### 1. Média por Cargo
- Linhas: Cargo
- Valores: Média de "Média Geral"
- Ordenar: Decrescente

#### 2. Avaliações por Status
- Linhas: Status Feedback
- Valores: Contar "ID Feedback"
- Gráfico: Pizza

#### 3. Comparação Auto-avaliação vs Avaliador
- Linhas: Nome Funcionário
- Colunas: Origem Feedback
- Valores: Média de "Média Geral"

#### 4. Performance por Centro de Custo
- Linhas: Centro de Custo
- Valores: 
  - Média de "Média Geral"
  - Contar "ID Funcionário" (únicos)

### Com Fórmulas Excel

#### Funcionários com Média Acima de 4.5
```excel
=CONT.SE(AB:AB;">4.5")
```

#### Média Geral da Empresa
```excel
=MÉDIA(AB:AB)
```

#### Taxa de Conclusão
```excel
=CONT.SE(M:M;"Concluida")/CONT.NÚM(M:M)*100
```

## Dicas de Uso

### 1. Remover Duplicatas
Se precisar ver apenas dados únicos de funcionários:
- Selecionar dados
- Dados > Remover Duplicatas
- Selecionar colunas: ID Funcionário

### 2. Filtrar Avaliações Principais
Para ver apenas avaliação final de cada funcionário:
- Filtrar coluna "Avaliador Principal" = "Sim"

### 3. Agrupar por Período
Para análises mensais ou trimestrais:
- Usar função TEXTO() para extrair mês
- Criar tabela dinâmica agrupada

### 4. Exportar para BI Tools
O CSV pode ser importado em:
- Power BI
- Tableau
- Google Data Studio
- Looker

## Observações Importantes

1. **Encoding UTF-8**: Garante acentuação correta
2. **Separador ponto e vírgula**: Padrão brasileiro
3. **Datas no formato DD/MM/YYYY**: Padrão brasileiro
4. **N/A para campos vazios**: Facilita identificação
5. **Textos sem quebras de linha**: Evita problemas de importação
6. **Uma linha por feedback**: Permite análise granular

---

**Gerado em**: 15/10/2025
**Sistema**: MyBP - Exportação de Avaliações
