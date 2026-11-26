<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

trait ValidatesTenantOwned
{
    public static function limparCacheSoftDeletes(): void
    {
        SoftDeleteSchema::limpar();
    }

    protected function empresaIdAutenticada(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    /**
     * Exists escopado por empresa. Se a tabela tiver soft deletes, exclui registros apagados
     * por padrão (override com $ignorarSoftDeleted).
     */
    protected function existsNaEmpresa(string $table, string $column = 'id', ?bool $ignorarSoftDeleted = null): Exists
    {
        $rule = Rule::exists($table, $column)->where('empresa_id', $this->empresaIdAutenticada());

        if ($this->deveIgnorarSoftDeleted($table, $ignorarSoftDeleted)) {
            $rule->whereNull('deleted_at');
        }

        return $rule;
    }

    /**
     * Exists sem tenant (cadastros globais). Aplica soft delete automático quando houver deleted_at.
     */
    protected function existsAtivo(string $table, string $column = 'id', ?bool $ignorarSoftDeleted = null): Exists
    {
        $rule = Rule::exists($table, $column);

        if ($this->deveIgnorarSoftDeleted($table, $ignorarSoftDeleted)) {
            $rule->whereNull('deleted_at');
        }

        return $rule;
    }

    /**
     * Exists da empresa autenticada OU registros globais (empresa_id null).
     * Bloqueia IDs de outras empresas.
     */
    protected function existsNaEmpresaOuGlobal(string $table, string $column = 'id', ?bool $ignorarSoftDeleted = null): Exists
    {
        $empresaId = $this->empresaIdAutenticada();
        $rule = Rule::exists($table, $column)->where(function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
        });

        if ($this->deveIgnorarSoftDeleted($table, $ignorarSoftDeleted)) {
            $rule->whereNull('deleted_at');
        }

        return $rule;
    }

    /**
     * @return list<string|Exists>
     */
    protected function regraIdNaEmpresaOuGlobal(
        string $table,
        bool $required = true,
        bool $nullable = false,
        ?bool $ignorarSoftDeleted = null
    ): array {
        $rules = [];

        if ($required) {
            $rules[] = 'required';
        }

        if ($nullable) {
            $rules[] = 'nullable';
        }

        $rules[] = 'integer';
        $rules[] = $this->existsNaEmpresaOuGlobal($table, 'id', $ignorarSoftDeleted);

        return $rules;
    }

    /**
     * @return list<string|Exists>
     */
    protected function regraIdNaEmpresa(
        string $table,
        bool $required = true,
        bool $nullable = false,
        ?bool $ignorarSoftDeleted = null
    ): array {
        $rules = [];

        if ($required) {
            $rules[] = 'required';
        }

        if ($nullable) {
            $rules[] = 'nullable';
        }

        $rules[] = 'integer';
        $rules[] = $this->existsNaEmpresa($table, 'id', $ignorarSoftDeleted);

        return $rules;
    }

    /**
     * @return list<string|Exists>
     */
    protected function regraIdAtivo(
        string $table,
        bool $required = true,
        bool $nullable = false,
        ?bool $ignorarSoftDeleted = null
    ): array {
        $rules = [];

        if ($required) {
            $rules[] = 'required';
        }

        if ($nullable) {
            $rules[] = 'nullable';
        }

        $rules[] = 'integer';
        $rules[] = $this->existsAtivo($table, 'id', $ignorarSoftDeleted);

        return $rules;
    }

    protected function deveIgnorarSoftDeleted(string $table, ?bool $override): bool
    {
        return $override ?? $this->tabelaTemSoftDeletes($table);
    }

    /**
     * Unique por empresa; soft-deleted não bloqueia reuso do valor.
     */
    protected function uniqueNaEmpresa(string $table, ?string $column = null, ?int $ignoreId = null): Unique
    {
        $rule = $column === null
            ? Rule::unique($table)
            : Rule::unique($table, $column);

        $rule->where('empresa_id', $this->empresaIdAutenticada());

        if ($this->tabelaTemSoftDeletes($table)) {
            $rule->whereNull('deleted_at');
        }

        if ($ignoreId !== null && $ignoreId > 0) {
            $rule->ignore($ignoreId);
        }

        return $rule;
    }

    /**
     * Aplica whereNull(deleted_at) em closures de Rule::unique/exists quando a tabela tiver soft delete.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected function aplicarSoftDeleteSeExistir($query, string $table)
    {
        if ($this->tabelaTemSoftDeletes($table)) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    protected function tabelaTemSoftDeletes(string $table): bool
    {
        return SoftDeleteSchema::tem($table);
    }
}
