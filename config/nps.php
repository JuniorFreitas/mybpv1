<?php

return [
    'habilitado' => filter_var(env('NPS_HABILITADO', false), FILTER_VALIDATE_BOOLEAN),

    /** ID da empresa que pode acessar gerenciamento e resultados do NPS (ex.: 100 = MyBP) */
    'empresa_id_gerenciamento' => (int) (env('NPS_EMPRESA_GERENCIAMENTO', 100)),

    'empresas_excluidas' => array_map('intval', array_filter(
        array_map('trim', explode(',', env('NPS_EMPRESAS_EXCLUIDAS', '100')))
    )),

    'mensagens' => [
        'titulo' => 'Avalie sua experiência',
        'botao_enviar' => 'Enviar',
        'botao_responder_depois' => 'Responder depois',
    ],

    'dias_entre_respostas' => 90,

    /** Mínimo de acessos (logins) nos últimos 90 dias para o modal NPS ser exibido */
    'min_acessos_ultimos_90_dias' => (int) (env('NPS_MIN_ACESSOS_90_DIAS', 3)),
];
