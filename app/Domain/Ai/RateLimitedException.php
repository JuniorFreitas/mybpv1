<?php

namespace App\Domain\Ai;

use RuntimeException;

/**
 * O provedor recusou a chamada por limite de taxa (HTTP 429). O proxy trata
 * isso de forma diferente de uma falha comum: como os modelos de um mesmo
 * provedor aqui compartilham a mesma chave/cota, insistir em outro modelo
 * imediatamente só sufoca ainda mais o provedor em vez de contornar o problema.
 */
final class RateLimitedException extends RuntimeException
{
}
