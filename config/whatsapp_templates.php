<?php

return [
    'cache_ttl_minutes' => 15,

    'rodape_padrao' => '_Esta mensagem foi enviada automaticamente pela plataforma *MyBP*, por favor não responda._',

    'max_corpo_length' => 4096,

    'templates' => [
        'recrutamento_selecao' => <<<'TXT'
👏🏽👏🏽Parabéns, *{{nome_destinatario}}*. Você foi *selecionado(a)*!
Para a vaga *{{vaga_titulo}} - {{vaga_cidade}}* fique atento as próximas etapas do processo!

📆 Data da entrevista: {{data_entrevista}}
📍Local da entrevista: {{local_entrevista}}

Sucesso e esperamos vê-lo em breve.

*☺️ Um forte abraço da equipe {{empresa_nome}}*

{{rodape_mybp}}
TXT,

        'recrutamento_provas' => <<<'TXT'
{{intro_provas}}

{{links_provas}}

Cuidado para não perder o prazo! Esperamos te ver em breve!

*Equipe RH {{empresa_nome}}*
TXT,

        'exame_encaminhamento' => <<<'TXT'
Prezado(a) sr(a) *{{nome_destinatario}}*, Tudo bem?

Estamos encaminhando para realização de *Exame de ordem {{tipo_exame}}*, no primeiro dia útil após recebimento dessa notificação (considerar de segunda à sábado).

🏥 Local do Exame:
*{{clinica_nome}}*.
📍 Endereço: *{{clinica_endereco}}*
📞 Contato: *{{clinica_telefone}}*
🗓️ Data de encaminhamento: *{{data_encaminhamento}}*
🗓️ Data de realização: *{{data_realizacao}}*

Atenciosamente,

Equipe {{empresa_nome}}

{{rodape_mybp}}
TXT,

        'admissao_documentos' => <<<'TXT'
Prezado(a) sr(a) *{{nome_destinatario}}*, Tudo bem?

👏🏽 Parabéns por chegado até esta etapa! Você foi aprovado na etapa de entrevista e seleção e agora vamos para a etapa de documentos para admissão.

Para continuidade no processo, segue o link abaixo para que seja anexado os documentos conforme descrição.

{{url_documentos}}

{{observacao}}

Destaca-se que é muito importante que todos os documentos sejam anexados corretamente e sem omissões para que não haja atraso na etapa de documentação, necessária para a continuidade de sua admissão.

Atenciosamente,

{{assinatura}}

{{rodape_mybp}}
TXT,

        'admissao_exame' => <<<'TXT'
Prezado(a) sr(a) *{{nome_destinatario}}*, Tudo bem?

Estamos encaminhando para realização de *Exame de ordem admissional*, no primeiro dia útil após recebimento dessa notificação (considerar de segunda à sábado).

🏥 Local do Exame:
*{{clinica_nome}}*.
📍 Endereço: *{{clinica_endereco}}*
📞 Contato: *{{clinica_telefone}}*

Atenciosamente,

Equipe {{empresa_nome}}

{{rodape_mybp}}
TXT,

        'intermitente_convocacao' => <<<'TXT'
Prezado(a), *{{nome_destinatario}}*
Conforme seu modelo de contrato INTERMITENTE prevê a convocação ao trabalho, viemos através dessa mensagem informá-lo(a) que o(a) Sr(a). está convocado(a) para trabalho no período de *{{periodo}}* no *{{centro_custo}} / {{area}}*.
Para isso, gentileza confirmar aceite de convocação, conforme links abaixo ⬇️

Para *aceitar*, clique no link a seguir:
{{link_sim}}

Para *recusar*, clique no link a seguir:
{{link_nao}}

Informamos que você tem até *{{prazo_resposta}}* para sinalizar a sua resposta.

Um forte abraço da equipe *{{empresa_nome}}*

{{rodape_mybp}}
TXT,

        'carta_oferta_gerencial' => <<<'TXT'
Prezado(a) sr(a), {{nome_destinatario}}, tudo bem?

Parabéns por chegado até esta etapa! Você foi aprovado(a) na etapa de entrevista e seleção e agora vamos para a etapa de documentos para admissão.

Estamos enviando em anexo o PDF do checklist.

Para continuidade no processo, segue o link abaixo para que seja anexado os documentos conforme descrição.

{{url_documentos}}

Destaca-se que é muito importante que todos os documentos sejam anexados corretamente e sem omissões para que não haja atraso na etapa de documentação, necessária para a continuidade de sua admissão

Atenciosamente,
{{assinatura}}
TXT,

        'carta_oferta_sgi' => <<<'TXT'
Olá, {{nome_destinatario}}!

Para continuidade no processo, segue o link abaixo para que seja anexada a *CARTA OFERTA ASSINADA*.

{{url_carta}}

*Atenção:* A carta oferta deve ser assinada até {{prazo_resposta}}

Atenciosamente,
{{assinatura}}
TXT,

        'parecer_rota_transporte' => <<<'TXT'
Prezado(a) sr(a) *{{nome_destinatario}}*, Tudo bem?

Seguem as informações da rota de transporte:

🚌 Rota: *{{rota}}*
📍 Bairro: *{{bairro}}*
📌 Ponto de referência: *{{ponto_referencia}}*
Atenciosamente,

Equipe de Transporte
*{{empresa_nome}}*
TXT,

        'movimentacao_aprovacao' => <<<'TXT'
Prezado(a) *{{nome_destinatario}}*,

*{{titulo_notificacao}}*

{{mensagem_notificacao}}

*Módulo:* {{modulo_movimentacao}}
*Colaborador:* {{colaborador}}

Acesse o sistema: {{url_sistema}}

{{assinatura}}

{{rodape_mybp}}
TXT,
    ],

    'tipos' => [
        'recrutamento_selecao' => [
            'label' => 'Recrutamento — Seleção de candidato',
            'modulo' => 'Recrutamento',
            'placeholders' => ['nome_destinatario', 'vaga_titulo', 'vaga_cidade', 'data_entrevista', 'local_entrevista', 'empresa_nome', 'rodape_mybp'],
        ],
        'recrutamento_provas' => [
            'label' => 'Recrutamento — Convite provas online',
            'modulo' => 'Recrutamento',
            'placeholders' => ['nome_destinatario', 'vaga_titulo', 'intro_provas', 'links_provas', 'empresa_nome'],
        ],
        'exame_encaminhamento' => [
            'label' => 'Exames — Encaminhamento',
            'modulo' => 'Exames',
            'placeholders' => ['nome_destinatario', 'tipo_exame', 'clinica_nome', 'clinica_endereco', 'clinica_telefone', 'data_encaminhamento', 'data_realizacao', 'empresa_nome', 'rodape_mybp'],
        ],
        'admissao_documentos' => [
            'label' => 'Admissão — Documentos',
            'modulo' => 'Admissão',
            'placeholders' => ['nome_destinatario', 'url_documentos', 'observacao', 'assinatura', 'rodape_mybp'],
        ],
        'admissao_exame' => [
            'label' => 'Admissão — Exame admissional',
            'modulo' => 'Admissão',
            'placeholders' => ['nome_destinatario', 'clinica_nome', 'clinica_endereco', 'clinica_telefone', 'empresa_nome', 'rodape_mybp'],
        ],
        'intermitente_convocacao' => [
            'label' => 'Intermitente — Convocação',
            'modulo' => 'Intermitente',
            'placeholders' => ['nome_destinatario', 'periodo', 'centro_custo', 'area', 'link_sim', 'link_nao', 'prazo_resposta', 'empresa_nome', 'rodape_mybp'],
        ],
        'carta_oferta_gerencial' => [
            'label' => 'Carta oferta — Gerencial',
            'modulo' => 'Carta Oferta',
            'placeholders' => ['nome_destinatario', 'url_documentos', 'assinatura'],
        ],
        'carta_oferta_sgi' => [
            'label' => 'Carta oferta — SGI',
            'modulo' => 'Carta Oferta',
            'placeholders' => ['nome_destinatario', 'url_carta', 'prazo_resposta', 'assinatura'],
        ],
        'parecer_rota_transporte' => [
            'label' => 'Transporte — Parecer rota',
            'modulo' => 'Transporte',
            'placeholders' => ['nome_destinatario', 'rota', 'bairro', 'ponto_referencia', 'empresa_nome'],
        ],
        'movimentacao_aprovacao' => [
            'label' => 'Movimentação — Notificação de aprovação',
            'modulo' => 'Movimentação',
            'placeholders' => [
                'nome_destinatario',
                'titulo_notificacao',
                'mensagem_notificacao',
                'modulo_movimentacao',
                'colaborador',
                'url_sistema',
                'assinatura',
                'rodape_mybp',
            ],
        ],
    ],

    'placeholders_globais' => [
        'nome_destinatario',
        'empresa_nome',
        'empresa_telefone',
        'empresa_endereco',
        'assinatura',
        'rodape_mybp',
    ],
];
