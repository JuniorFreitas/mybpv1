<?php

return [
    /*
    |--------------------------------------------------------------------------
    | URL pública dos links do logotipo no JSON (opcional)
    |--------------------------------------------------------------------------
    |
    | Origem absoluta usada ao montar `logotipo.url` / `url_thumb` na API v2
    | (ex.: https://rh.exemplo.com). Se vazio, usa APP_URL.
    |
    */
    'public_url' => env('INTEGRACAO_SPA_PUBLIC_URL'),

    /*
    |--------------------------------------------------------------------------
    | TTL do cache da listagem GET /api/v2/integracao/ (empresas ativas)
    |--------------------------------------------------------------------------
    |
    | Em segundos (mínimo 60; padrão 7 dias). O cache é invalidado ao salvar/excluir
    | Cliente (Eloquent) ou ao alterar/excluir Arquivo vinculado em cliente_logotipo.
    |
    */
    'empresas_ativas_cache_ttl_seconds' => (int) env('INTEGRACAO_SPA_EMPRESAS_ATIVAS_CACHE_TTL_SECONDS', 604800),

    /*
    |--------------------------------------------------------------------------
    | Itens por página — GET …/vagas-abertas
    |--------------------------------------------------------------------------
    */
    'vagas_abertas_por_pagina' => (int) env('INTEGRACAO_SPA_VAGAS_POR_PAGINA', 50),

    /*
    |--------------------------------------------------------------------------
    | TTL do cache por página — GET …/{apelido}/vagas-abertas
    |--------------------------------------------------------------------------
    |
    | Em segundos (mínimo 60; padrão 24 h). Invalidação por alteração em VagasAbertas
    | da empresa (observer). O bloco `empresa` no JSON não é cacheado aqui.
    |
    */
    'vagas_abertas_pagina_cache_ttl_seconds' => (int) env('INTEGRACAO_SPA_VAGAS_PAGINA_CACHE_TTL_SECONDS', 86400),
];
