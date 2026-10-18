<?php

/**
 * Credenciais de integração via X-API-TOKEN.
 * empresa_ids vazio = todas as empresas (legado; restringir em produção via env).
 * scopes: spa_read, spa_write, whatsapp, cbo, sgi, v1
 */
return [
    'tokens' => [
        env('X_API_TOKEN', '') => [
            'scopes' => array_values(array_filter(array_map('trim', explode(',', env(
                'X_API_TOKEN_SCOPES',
                'spa_read,spa_write,whatsapp,cbo,sgi,v1'
            ))))),
            'empresa_ids' => array_values(array_filter(array_map('intval', explode(',', (string) env('X_API_TOKEN_EMPRESA_IDS', ''))))),
        ],
    ],
];
