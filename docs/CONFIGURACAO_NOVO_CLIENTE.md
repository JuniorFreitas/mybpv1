# Configuração de um novo cliente

Guia de implantação do MyBPIN no estado atual do sistema (outubro de 2026).
Cobre o cadastro em **Administração › Clientes** e tudo o que o cliente precisa
ter pronto para operar os módulos contratados.

Quem executa a etapa 1 é um usuário da empresa administradora com a habilidade
`administracao_clientes_insert`. As etapas seguintes são feitas já no ambiente
do cliente (login da empresa ou de um administrador dela).

Um **Prospect** só guarda dados comerciais. As abas de serviços, carteira,
filiais e WhatsApp, e a operação de RH, existem para o tipo **Cliente**.

## Ordem

1. Reunir dados e decidir os módulos.
2. Cadastrar o cliente e ligar os módulos.
3. Reabrir o cadastro: filiais, modelo CIH, WhatsApp e alertas.
4. Trocar a senha inicial e criar usuários.
5. Fazer os cadastros base, nesta ordem.
6. Configurar só os módulos contratados.
7. Conferir o checklist de aceite.

## 1. Antes de abrir a tela

Tenha em mãos:

- Tipo: **Cliente** ou **Prospect**. Pessoa jurídica (CNPJ) ou física (CPF).
- Razão social ou nome, nome fantasia, ramo e área de atuação (a lista vem das
  áreas ativas da empresa administradora).
- Endereço completo, responsável, data de aniversário do contato, e-mail e ao
  menos um telefone.
- Como conheceu a BPSE.
- Lista de módulos que o contrato libera.
- Se houver carteira de treinamento: política de EHS, missão, visão, valores,
  política de gestão da qualidade, apelido curto (exemplo: `BPSE`), telefone
  principal, logo e mascote (imagens).
- Decisões das flags da seção 3 (férias, ASO, WhatsApp, MFA, assinatura, FAT).

O servidor recusa o cadastro sem documento (CNPJ ou CPF único), sem razão
social ou nome, e sem pelo menos um telefone. Preencha também área, endereço,
responsável, aniversário, e-mail e a chave **Ativo**. Sem **Ativo** ligado, o
cliente nasce inativo.

## 2. Cadastrar o cliente

Caminho: **Administração › Clientes › Cadastrar**.

### Dados cadastrais

| Campo | Regra |
|---|---|
| Tipo do cliente | Cliente ou Prospect. Cliente não volta para Prospect na edição. |
| Tipo | Pessoa Jurídica ou Pessoa Física. Não muda depois de salvo. |
| CNPJ ou CPF | Único. Não muda depois de salvo. |
| Razão social ou Nome | Obrigatório. |
| Nome fantasia | Pessoa jurídica. Entra no nome do grupo Administrador. |
| Área de atuação | Selecione uma área ativa. |
| Ramo | Texto livre. |
| Endereço | CEP, logradouro, número, complemento, bairro, município e UF. |
| Responsável | Nome do contato. |
| Data de aniversário | Do contato comercial. |
| E-mail | Vira o **login** do usuário Empresa. |
| Telefones | Pelo menos um. Tipo, número, ramal e detalhe. |
| Como conheceu a BPSE | Se for **OUTROS**, descreva no campo que aparece. |
| Ativo | Ligue antes de salvar. |

### Informações para carteira de treinamento

Só aparece para **Cliente**. Obrigatório quando o cliente for emitir carteira:

- Política de EHS, Missão, Visão, Valores e Política de Gestão da Qualidade.
- Apelido (sigla curta). O sistema usa o apelido no grupo de clínica de exame.
- Telefone principal.
- Logo (1 imagem) e mascote (1 imagem).

### Serviços e propostas

Opcionais no primeiro salvamento.

- **Serviços** (só Cliente): serviço ativo, situação e anexos.
- **Propostas**: proposta e anexos. Vale também para Prospect.

### O que o sistema cria sozinho

Ao salvar um Cliente, o sistema grava, na mesma transação:

- Usuário do tipo **Empresa**. O login é o e-mail informado. A senha inicial é
  `mybp2021`. O `empresa_id` desse usuário passa a ser o próprio id (o id do
  cliente é o id desse usuário).
- Vínculo do novo cliente na carteira de clientes da empresa administradora.
- Registro em `cliente_configs` com as flags da aba Configurações.
- Papel **MASTER** (`{razão social ou nome} - MASTER`) com as habilidades
  marcadas em **Parametrização - Módulos**.
- Papel **Administrador - {nome fantasia}**, com as mesmas habilidades do MASTER.
- Vínculo da empresa com ela mesma em `empresa_clientes`.
- Papel **{APELIDO} - Clinica Exame**, para o acesso da clínica, quando o
  apelido permite criar o grupo.

Troque a senha inicial no primeiro acesso. Ela é a mesma para todo cliente novo.

## 3. Aba Configurações

Abra essa aba **antes** do primeiro salvamento. Parte das opções (modelo CIH,
alertas de cota, aba WhatsApp e filiais) só aparece na edição, depois que o
cliente já existe.

### Configurações gerais

| Campo | Valores | Efeito |
|---|---|---|
| Vencimento de férias | 30, 60, 90 ou 120 dias | Antecedência do alerta de férias (`verifica_mes_vencimento`: 1, 2, 3 ou 4). |
| Vencimento de ASO | 30, 60 ou 90 dias | Antecedência do alerta de ASO (`vencimento_aso`: 1, 2 ou 3). |
| Modelo CIH | Centro de Custo ou Área | Só na edição. Define o agrupamento do CIH. Sem isso o apontamento não tem modelo. |
| Etiqueta bloqueio (número do supervisor) | Sim ou Não | Padrão **Sim**. Imprime o telefone do supervisor na etiqueta. |

### Notificações

| Campo | Padrão | Efeito |
|---|---|---|
| Envia notificação no WhatsApp | vazio até escolher | Porta de entrada de **todo** WhatsApp da empresa. MFA por WhatsApp e aniversário dependem de **Sim**. |
| Notificar gestor origem na transferência | Sim | Desligado, a transferência não envia e-mail nem WhatsApp ao gestor de origem. A etapa de aprovação continua. |
| Exigir aprovação do gestor origem na transferência | Sim | Desligado, a etapa do gestor de origem já nasce aprovada e some das notificações. O fluxo segue para destino, aprovação extra e RH. |

### Rotinas

| Campo | Padrão | Efeito |
|---|---|---|
| Schedule de treinamento vencimento | Habilitado | Liga ou desliga o aviso de treinamento a vencer desta empresa. |
| WhatsApp de aniversário | Desabilitado | Envia a mensagem do dia no WhatsApp principal do admitido. Exige também **Envia notificação no WhatsApp = Sim**. Ao ligar, o sistema cria os padrões de mensagem de aniversário. |
| Schedule de avaliação de experiência | Habilitado | Liga ou desliga o aviso da avaliação de experiência desta empresa. |
| Permitir alterar “Realizou este treinamento?” para Não | Desabilitado | Usuários de Gestão RH podem desmarcar treinamento já realizado na carteira e nas etiquetas. |
| FAT em treinamentos obrigatório | Desabilitado | O número FAT passa a ser obrigatório ao salvar ou alterar treinamento realizado. |

### MFA no login

| Campo | Padrão | Efeito |
|---|---|---|
| Autenticação em dois fatores | Desabilitado | O login web pede um código de 8 caracteres depois da senha. |
| Canal e-mail | Habilitado (se o MFA estiver ligado) | Envia o código para o login (e-mail) do usuário. |
| Canal WhatsApp | Desabilitado | Exige WhatsApp da empresa ligado e telefone cadastrado no usuário. |
| Pedir MFA novamente | A cada 30 dias | Opções: a cada login (0), 30 dias, 90 dias ou customizado de 1 a 3650 dias. Vale no mesmo navegador. Outro dispositivo pede de novo. |

Com o MFA ligado, escolha ao menos um canal. WhatsApp de MFA sem a notificação
de WhatsApp da empresa faz o salvamento falhar.

### Assinatura digital

| Campo | Padrão | Efeito |
|---|---|---|
| Assinatura digital de documentos | Desabilitado | Desligado, menu e botões de assinatura ficam ocultos. |
| Limite mensal | em branco = ilimitado | Cota de documentos assinados no mês. |
| Alertas de cota por usuário | só na edição, com a assinatura ligada | E-mails em 80%, 90% e 100% da cota. |
| Alertas de cota por grupo | idem | Todos os usuários ativos do grupo recebem os mesmos alertas. |

Os destinatários de alerta só podem ser escolhidos depois que existem usuários
e grupos do cliente. Volte nesta tela ao final da implantação se a assinatura
estiver no contrato.

### Inteligência artificial

| Campo | Padrão | Efeito |
|---|---|---|
| Descrição de vaga aberta por IA | Desabilitado | Mostra ou esconde **Gerar com IA** em Cadastro › Vagas Abertas. |

### Parametrização - Módulos

Marque as habilidades que o contrato libera. **Todos os módulos** liga tudo.
**Todos em {menu}** liga só aquele grupo.

Essas marcas vão para o papel MASTER e são copiadas para o grupo
Administrador. O que ficar desmarcado não aparece no menu do cliente.

Grupos usuais, conforme o contrato:

| Menu na tela | O que libera |
|---|---|
| administracao | Fornecedores, documentos legais, carta oferta, ata, pesquisa de clima, planejamento diário, aniversariantes. |
| cadastro | Áreas, dossiê, documentos de pré-admissão, departamentos, centros de custo, benefícios, cargos, vagas abertas, projetos, exames, instrutor, treinamentos, padrões, empresa de treinamento, empresa temporária, provas, avaliações, customizações, WhatsApp. |
| planejamento | Requisição de vaga e mobilização. Movimentação entra junto com esse menu. |
| curriculos | Recrutamento e selecionados. |
| entrevista | Pareceres, entrevista RH, entrevista gestor e resultado integrado. |
| controle_ponto | Configuração, ocorrências, feriados, escalas, ponto, jornadas, folhas e relatório sintético. |
| admissao | Carta oferta, pré-admissão, exames, CIH, tipos CIH, processo, importação, histórico e pós-admissão. |
| treinamento | Portaria, carteira/etiquetas, importação e certificados NR-33/NR-35. |
| avaliacoes | Minhas avaliações. |
| ocorrencia | Ocorrências. |
| weekly_report | Weekly report. |
| financeiro | Classificação, planos de conta, formas de pagamento e fluxo de caixa. |
| cloud | Pastas e configurações do Cloud. |
| site | Galeria, cartela de cliente e depoimentos. |
| relatorio | Usuários, ASO, treinamentos, férias, medidas, centro de custo, efetivo, avaliação de experiência e aniversariantes. |
| usuario | Usuários do sistema. |
| configuracao | Módulos do sistema e grupos de usuários. Necessário para o RH do cliente administrar acessos. |

O menu **Carteiras e Etiquetas** (modelos de PDF) só aparece para a empresa
administradora (`empresa_id` 100). O cliente escolhe o modelo no padrão de
treinamento. Ele não cadastra o arquivo da carteira.

## 4. Completar na edição

Salve o cliente e abra de novo. Três abas passam a existir.

### Filiais

Caminho: aba **Filiais**. Cada filial é uma lotação (CNPJ) usada em centro de
custo e em padrão de treinamento.

Para cada filial: CNPJ (não muda depois), razão social, nome fantasia, área de
atuação, ramo, endereço, responsável, telefone, e-mail e **Ativo**.

Cadastre a matriz como cliente e as unidades como filiais antes dos centros de
custo. O centro de custo exige um CNPJ de matriz ou filial.

### WhatsApp

Caminho: aba **WhatsApp**, só para Cliente em edição.

O envio só acontece com **Envia notificação no WhatsApp = Sim**. Nesta aba,
configure:

1. Dados de contato (telefone, endereço e assinatura das mensagens).
2. Módulos habilitados (recrutamento, exames, admissão e os demais do contrato).
3. Templates de mensagem.
4. Preferências dos usuários (telefone tipo WhatsApp e o que cada um recebe).

Detalhe de módulos, gate e templates: [WHATSAPP_CONFIGURACAO_EMPRESA.md](WHATSAPP_CONFIGURACAO_EMPRESA.md).

O RH do cliente também alcança a mesma configuração em **Cadastro ›
Customizações › WhatsApp**, se tiver a habilidade `configuracao_whatsapp`.

### Modelo CIH e alertas de assinatura

Volte em **Configurações** e defina **Modelo CIH**. Se a assinatura digital
estiver ligada e já existirem usuários, escolha quem recebe alerta de cota.

## 5. Primeiro acesso, usuários e grupos

1. Entre com o e-mail do cliente e a senha inicial. Troque a senha.
2. Em **Usuários › Usuários do Sistema**, crie o RH, gestores e demais acessos.
   Cada gestor que aprova transferência, requisição ou centro de custo precisa
   existir aqui antes dos cadastros que apontam para ele.
3. Em **Configurações › Grupos de Usuários**, crie os grupos do dia a dia
   (RH, gestor, portaria, clínica) e marque só as habilidades da função.
   O grupo Administrador já nasce com o pacote do MASTER.
4. Associe cada usuário ao grupo correto.
5. Se o MFA estiver ligado, cadastre o telefone tipo WhatsApp de quem for
   receber o código por esse canal.

## 6. Cadastros base

Faça nesta ordem. Um item de baixo usa os de cima.

| Ordem | Caminho | O que precisa existir antes |
|---|---|---|
| 1 | Cadastro › Centro de Custos | Filiais (CNPJ) e os dois gestores (principal e substituto). Eles aprovam a transferência prevista. |
| 2 | Cadastro › Áreas | Centro de custo e gestor. O telefone do supervisor sai na etiqueta. |
| 3 | Cadastro › Departamentos | Só o nome. |
| 4 | Cadastro › Cargos | Título. CBO é opcional. Treinamentos do cargo são vinculados na lista de treinamentos, não aqui. |
| 5 | Cadastro › Benefícios | Cadastre o tipo de benefício antes do benefício. |
| 6 | Cadastro › Exames | Clínica (CNPJ, endereço, telefone e e-mail de encaminhamento) e os PCMSO. |
| 7 | Cadastro › Padrões de Treinamento | Filiais e, se for emitir documento, o modelo de carteira e/ou etiqueta (seção 7). |
| 8 | Cadastro › Lista de Treinamentos | Cargos e padrão. Defina validade, nome na carteira e cargos. |
| 9 | Cadastro › Instrutor | Nome, cargo, registro e arquivo da assinatura dos certificados. |
| 10 | Cadastro › Empresa Treinamento | Quem ministra o treinamento. |
| 11 | Cadastro › Certificados | Modelos NR e demais, se o contrato emitir certificado. |
| 12 | Cadastro › Vagas Abertas | Cargo, e opcionalmente projeto, prova e cidade. |
| 13 | Cadastro › Projetos | Capacidade e vagas, se o contrato usar projeto. |
| 14 | Cadastro › Provas | Se a vaga tiver prova. |
| 15 | Cadastro › Avaliações e Peformance | Tipos de avaliador, tipos de avaliação, competências e só então o ciclo. |
| 16 | Cadastro › Tipos de Dossiê e Documentos da Pré-admissão | Se o contrato usar dossiê e pré-admissão. |
| 17 | Cadastro › Empresa Temporária | Se houver temporários. |

O manual do usuário detalha cada tela em
`docs/manual-completo/base-conhecimento/md/04-cadastros.md`.

### Customizações

Em **Cadastro › Customizações**, só o que o contrato pedir:

- **Requisição de Vaga**: campos extras (sim/não, texto, caixa de texto ou lista).
- **Aprovações Extras**: uma etapa entre o gestor e o RH. Uma configuração ativa
  por tipo (demissão, férias, mudança de cargo, transferência, intermitente
  para fixo, valor extra, requisição de vaga ou admissão prevista).
- **Gestor Aprovação**: quem aprova no lugar do fluxo padrão, quando a tela
  estiver no contrato.
- **WhatsApp**: a mesma configuração da aba do cliente, para o RH.

### Documentos da administradora

Ainda na empresa administradora, se o contrato incluir:

- **Administração › Carta Oferta - Template**: texto e placeholders.
- **Administração › Documentos Legais**: tipos, formas de contrato e os
  documentos da empresa.
- **Administração › Fornecedores**: quando o cliente operar com fornecedores.

## 7. Treinamento: modelos, padrão e operação

Há três camadas. Não pule a do meio.

### Modelos (empresa administradora)

Caminho: **Cadastro › Carteiras e Etiquetas** (somente `empresa_id` 100).

Cada modelo de carteira e cada modelo de etiqueta tem:

- Escopo **global** (qualquer empresa) ou **desta empresa** (só o cliente escolhido).
- Nome e situação.
- Imagens de cabeçalho e verso, quando o layout usar arquivo em `public/images/carteira/`.
- Identificador: **Chapa** (número do crachá) ou **Matrícula** (matrícula da admissão).
- Arquivo blade opcional, sem `.blade.php`, dentro de
  `resources/views/pdf/treinamento/carteira/`. Vazio usa o layout padrão
  (`cart_treinamento` ou `cart_bloqueio`).

Um layout visual novo (blade ou imagem) é entrega de sistema, não um campo que
o RH preenche. Combine o identificador com o que a admissão realmente grava:
chapa sem crachá, ou matrícula sem matrícula, imprime o documento em branco
nesse campo.

### Padrão do cliente

Caminho, já no cliente: **Cadastro › Padrões de Treinamento**.

- Nome (exemplo: `Padrão ALUMAR Matriz`) e **Ativo**.
- **Usa carteira** e o modelo. **Usa etiqueta de bloqueio** e o modelo.
- **Válido para**: ao menos um CNPJ (matriz ou filial).

O padrão é o que a admissão escolhe. Sem CNPJ, o padrão não pode ser usado
naquela lotação. Sem modelo, a carteira ou a etiqueta não sai.

### Catálogo e pessoas

1. Em **Lista de Treinamentos**, vincule cada treinamento ao padrão, aos cargos,
   à validade e ao nome reduzido da carteira. Cadastre a assinatura da carteira
   (Gerente/RH ou Técnico de Segurança).
2. Na admissão, selecione o padrão da lotação.
3. A operação diária fica em **Treinamento › Carteira/Etiquetas**.
   Importação em massa: [importacao/IMPORTACAO_TREINAMENTOS.md](importacao/IMPORTACAO_TREINAMENTOS.md).
4. Confira as flags da seção 3: supervisor na etiqueta, FAT obrigatório e
   permissão para desmarcar realizado.

## 8. Módulos que pedem configuração além do cadastro

Faça só os que foram marcados na parametrização.

### Admissão e exames

- Modelo CIH definido e, se usar apontamento, **Admissão › Apontamento › Tipos CIH**.
- Clínicas e PCMSO ativos. O grupo de clínica nasce no cadastro do cliente.
- Carta oferta com template, se o fluxo enviar carta.
- Documentos da pré-admissão, se o candidato enviar arquivo antes da admissão.
- Importação de admissões: [importacao/IMPORTACAO_ADMISSOES.md](importacao/IMPORTACAO_ADMISSOES.md).

### Recrutamento

- Cargos, áreas, centros de custo e vagas abertas ativas no sistema (e no site,
  se a vaga for pública).
- Provas vinculadas, se houver teste.
- WhatsApp de recrutamento habilitado no módulo, se o contrato avisar candidato.

### Movimentação

- Gestores principal e substituto em cada centro de custo.
- Flags de notificação e de aprovação do gestor de origem (seção 3).
- Aprovação extra, se o contrato tiver etapa entre gestor e RH.

### Controle de ponto

Em **Controle de Ponto › Configurações**, grave a configuração da empresa,
perímetros e o que a tela pedir para o relógio. Depois: feriados, escalas e
ocorrências, antes de usar ponto eletrônico ou folha.

### Avaliações

Monte na ordem: tipos de avaliador, tipos de avaliação, competências, ciclo.
Associe avaliadores só com o ciclo em **Aguardando Início**. O schedule de
experiência é a flag da seção 3.

### Assinatura digital

Ligue a flag, defina a cota e os alertas. O menu **Documentos para Assinatura**
só aparece com a assinatura habilitada para a empresa.

### API externa

Credencial por empresa, somente leitura:

```bash
php artisan api-externa:credencial {empresa_id}
```

Guia do cliente: [API_V3_EXTERNA.md](API_V3_EXTERNA.md).

### Cloud, financeiro e site

- **Cloud › Cadastro** e **Cloud › Configurações** antes de usar as pastas.
- Financeiro: classificação, planos de conta e formas de pagamento antes do
  fluxo de caixa.
- Site: galeria, cartela e depoimentos, se o portal do cliente estiver no contrato.

## 9. Checklist de aceite

- [ ] Cliente ativo, com CNPJ ou CPF único, e-mail de login e telefone.
- [ ] Senha inicial trocada.
- [ ] Módulos do contrato marcados. O que não foi contratado está desmarcado.
- [ ] Filiais ativas, quando houver mais de um CNPJ.
- [ ] Modelo CIH escolhido, se houver apontamento.
- [ ] WhatsApp: notificação geral, módulos e templates, se o contrato enviar mensagem.
- [ ] MFA testado com o canal escolhido, se estiver ligado.
- [ ] Usuários do RH e dos gestores criados e colocados nos grupos certos.
- [ ] Centros de custo com gestor principal, substituto e CNPJ.
- [ ] Áreas com gestor e telefone de supervisor.
- [ ] Cargos, benefícios e exames (clínica e PCMSO) cadastrados.
- [ ] Padrão de treinamento com CNPJ e, se for o caso, carteira e etiqueta.
- [ ] Treinamentos vinculados ao padrão e aos cargos.
- [ ] Uma admissão de teste escolhe o padrão e gera carteira ou etiqueta legível
      (chapa ou matrícula preenchida).
- [ ] Vaga de teste abre requisição com cargo, área e centro de custo.
- [ ] Flags de transferência conferidas com o combinado do contrato.
- [ ] Cota e alertas de assinatura, se a assinatura estiver ligada.
- [ ] Credencial da API entregue ao cliente, se a integração estiver no contrato.
