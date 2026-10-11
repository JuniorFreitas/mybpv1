# 11. Treinamento
O menu **Treinamento** controla os treinamentos de cada colaborador, os vencimentos, a emissão de carteiras e etiquetas, a importação em lote e os certificados de NR33 e NR35. A lista de treinamentos, os padrões por cliente e os instrutores são configurados em **Cadastro** (capítulo 4).

## 11.1 Portaria
Caminho no menu: Treinamento › Portaria
Lista de pessoas com os dados exigidos para liberação de acesso em portaria: documento, filiação, cargo, função e foto 3x4. É daqui que se imprime a relação para a portaria.

### Filtros
- **Buscar:** Localiza por nome ou CPF.
- **Por Vaga:** Pessoas vinculadas a uma vaga.
- **Estado:** Filtra pela UF.
- **PCD:** Geral, Sim ou Não.
- **Exibir:** 20, 50, 100 ou 500 registros por página.

### Imprimir e exportar
1. Marque a caixa no início da linha de cada pessoa. O contador do botão **Imprimir** mostra quantas estão selecionadas.
2. Clique em **Imprimir** para gerar a relação dos selecionados. **Limpar seleção** desmarca todos.
3. **Exportar Excel** gera a planilha conforme os filtros.
A coluna **Foto 3x4** indica se a pessoa já tem foto cadastrada.

### Atualizar dados
O ícone de lápis abre a janela **Atualizar dados**:
- **Dados pessoais:** Nome, Nascimento, RG, Emitente, Pai e Mãe;
- **Endereço:** CEP * (a lupa busca o endereço), Endereço *, Número, Complemento, Bairro *, Município * e UF *;
- **Informações:** Cargo, Cidade, Função, **Acessará a área do porto** e **Avaliação Psicológica** (Não informado, Sim ou Não);
- **Foto escaneada:** imagem JPG, PNG, WEBP ou GIF.

## 11.2 Carteira/Etiquetas
Caminho no menu: Treinamento › Carteira/Etiquetas
Tela central dos treinamentos. Cada card é um colaborador, com os treinamentos realizados, as datas de vencimento e a situação de cada um.

### Filtros
- **Período de vencimento:** Ao marcar, busca treinamentos que vencem entre as duas datas.
- **Período treinado:** Ao marcar, busca treinamentos realizados entre as duas datas.
- **Treinados:** Separa quem já tem treinamento lançado de quem não tem. “Sem filtro” mostra todos.
- **Colaborador / CPF:** Localiza pelo nome ou CPF.
- **CNPJ:** Colaboradores de uma das empresas (CNPJs) do grupo.
- **Centro de custo:** Colaboradores de um centro de custo.
- **Vaga / Cargo:** Filtram pela vaga ou pelo cargo.
- **Situação:** Situação do colaborador. O padrão é Ativos.
- **Treinamentos específicos:** Mostra apenas os colaboradores que têm os treinamentos escolhidos.
**Mais filtros** abre o bloco **Outros filtros**:
- **Padrão:** Padrão de treinamento (por cliente/segmento) do colaborador.
- **Foto 3x4:** Com foto, sem foto ou Geral.
- **Crachá:** Com crachá, sem crachá ou Geral.
- **PCD:** Sim, Não ou Geral.
- **UF:** Estado do colaborador.
- **Por página:** Quantidade de colaboradores por página.

### O card do colaborador
- **Cabeçalho:** nome, quantidade de treinamentos, CPF, cargo, tipo de admissão e status;
- **Empresa**, **Centro de custo** e **Padrão de treinamento**;
- **Tabela:** Treinamento, Data do treinamento, Data de vencimento, Anexo FAT, Status e se é exibido na carteira.
As cores seguem a legenda acima da lista: **Em dia** (verde), **Vencendo** (amarelo) e **Vencido** (vermelho).
**Filtrar treinamentos** abre a janela **Mostrar e Ocultar Treinamentos**, onde você liga e desliga as colunas de treinamento que quer ver nos cards.

### Gerar carteira e etiquetas
1. Filtre os colaboradores e marque a caixa de cada card (ou **Selecionar todos**).
2. Clique em **Gerar carteira** e escolha o modelo: **Treinamento**, **Bloqueio** ou **Treinamento/Bloqueio**.
**Exportar Excel** gera a planilha dos treinamentos filtrados.

### Lançar ou atualizar treinamentos de um colaborador
1. No card, abra ⋮ › **Atualizar treinamento**.
2. Confira os **dados do funcionário** e escolha o **Segmento** (padrão de treinamento) a aplicar.
3. Os contadores mostram **Todos**, **Não realizados**, **Realizados em dia**, **A vencer** e **Vencidos**; clique em um deles para filtrar a lista. Há também a busca por nome e o filtro *Com vínculo ao cargo* / *Sem vínculo ao cargo*.
4. Clique no nome do treinamento para abri-lo. A faixa **A quem se destina** lembra o público do treinamento.
5. Em **Realizou este treinamento?** marque *Sim* e informe a **Data do treinamento**. O **Vencimento** é calculado pelo prazo do treinamento.
6. No bloco **FAT**, informe o **Número da FAT** e anexe o arquivo, se houver.
7. Clique em **Salvar este treinamento**. Repita para os demais.

## 11.3 Importação
Caminho no menu: Treinamento › Importação
Lança treinamentos em lote a partir de uma planilha, com o mesmo comportamento da carteira: cria o treinamento se não existir e atualiza o vencimento se já existir.
1. Clique em **Baixar modelo Excel**. O modelo já vem com as listas de colaboradores e treinamentos da sua empresa.
2. Preencha a aba **Dados** sem alterar os cabeçalhos. Em **colaborador** e **treinamento**, use as listas da própria planilha.
3. Em **data_treinamento**, use o formato dd/mm/aaaa (ex.: 15/03/2026).
4. **data_vencimento** é opcional: em branco, vale o prazo do treinamento; preenchida, vale a data informada.
5. Em **Enviar planilha**, escolha o arquivo .xlsx (até 20 MB) e clique em **Enviar para importação**.
Evite formatos como 15-03-2026, 3/15/2026 ou ano com dois dígitos: a linha é recusada.

### Aba Importados
O processamento ocorre em segundo plano; o resultado chega pelo sino, por e-mail e fica no **Histórico de importações**. Cada execução mostra a situação (*Concluída* ou *Falha*), o **Total** de linhas, os **Importados** e os **Erros**, além dos botões **Sucesso** e **Erro**, que baixam o CSV das linhas importadas e das recusadas com o motivo.

## 11.4 Emissão de Certificados (NR33/NR35)
Caminho no menu: Treinamento › Emissão Certificados (NR33/NR35)
Emite os certificados de NR33 (espaço confinado) e NR35 (trabalho em altura) para os colaboradores que realizaram os treinamentos.
- **Filtrar por período / Período:** Ao marcar, considera apenas o intervalo de datas informado.
- **Buscar / CPF:** Localiza por nome ou CPF.
- **Por Vaga / Por Cliente:** Filtram pela vaga ou pelo cliente.
- **Áreas / Cargo / Estado:** Filtram pela área, pelo cargo e pela UF.
- **Tem Instrutor NR33 / NR35:** Mostra quem tem (ou não) instrutor definido para o treinamento.
- **NR33 / NR35:** Realizado, Não Realizado ou Não se aplica.
- **Admitidos:** Geral, Sim ou Não.
- **Exibir:** 20, 50, 100 ou 500 registros por página.
Marque os colaboradores na lista e clique em **Certificados** (o contador mostra quantos estão selecionados). **Limpar seleção** desmarca todos.
