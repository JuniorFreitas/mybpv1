<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Facades\Schema;

/**
 * Cache compartilhado de Schema::hasColumn(deleted_at).
 * Evita static por classe (comportamento de traits PHP) e permite limpar nos testes.
 */
final class SoftDeleteSchema
{
    /** @var array<string, bool> */
    private static array $cache = [];

    public static function limpar(): void
    {
        self::$cache = [];
    }

    public static function tem(string $table): bool
    {
        if (array_key_exists($table, self::$cache)) {
            return self::$cache[$table];
        }

        try {
            self::$cache[$table] = Schema::hasColumn($table, 'deleted_at');
        } catch (\Throwable) {
            self::$cache[$table] = false;
        }

        return self::$cache[$table];
    }
}
