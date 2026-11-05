<?php

namespace App\Domain\SaudeOcupacional\Regras\Condicoes;

use App\Domain\SaudeOcupacional\Regras\PerfilColaborador;
use Carbon\CarbonImmutable;

/**
 * Uma condição do "QUANDO" da regra. Condição nova = nova classe registrada no CatalogoCondicoes.
 */
interface CondicaoRegra
{
    public function tipo(): string;

    public function rotulo(): string;

    /**
     * Valida e normaliza o valor vindo da tela.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function normalizar(mixed $valor): mixed;

    /**
     * @param  CarbonImmutable  $dataReferencia  data em que a idade é medida (vencimento padrão)
     */
    public function aplica(mixed $valor, PerfilColaborador $perfil, CarbonImmutable $dataReferencia): bool;

    /**
     * Texto curto para listas e simulador.
     *
     * @param  array{cargos?: array<int, string>, riscos?: array<int, string>}  $rotulos  nomes por id
     */
    public function descrever(mixed $valor, array $rotulos = []): string;
}
