<?php

/**
 * Importação ENGEKO - empresa 103967.
 *
 * A planilha possui 303 registros de São Luís-MA e não informa vaga_mun,
 * por isso as vagas abertas usam o município fixo 2743.
 *
 * Uso:
 *   docker compose exec mybpdp php scripts/0_IMPORTACAO_ENGEKO_103967.php
 */

define('IMPORTACAO_EMPRESA_ID', 103967);
define('IMPORTACAO_NOME', 'Engeko');
define('IMPORTACAO_SLUG', 'engeko_103967');
define('IMPORTACAO_PLANILHA', 'importacao_engeko_2026.xlsx');
define('IMPORTACAO_MUNICIPIO_ID', 2743);

require __DIR__ . '/0_IMPORTACAO_COIMBRA_RR_111969.php';
