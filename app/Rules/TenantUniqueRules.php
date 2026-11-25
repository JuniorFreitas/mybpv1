<?php

namespace App\Rules;

use App\Http\Requests\Concerns\SoftDeleteSchema;
use App\Tenant\ManagerTenant;
use Illuminate\Contracts\Validation\Rule;

class TenantUniqueRules implements Rule
{
    private $table;

    private $column;

    private $columnValue;

    /**
     * @param  string  $table
     * @param  mixed  $columnValue  ID a ignorar no update (mesmo registro)
     * @param  string  $column
     */
    public function __construct($table, $columnValue = null, $column = 'id')
    {
        $this->table = $table;
        $this->column = $column;
        $this->columnValue = $columnValue;
    }

    public function passes($attribute, $value)
    {
        $tenant = app(ManagerTenant::class)->getTenantIdentify();

        $query = \DB::table($this->table)
            ->where($attribute, $value)
            ->where('empresa_id', $tenant);

        if ($this->tabelaTemSoftDeletes($this->table)) {
            $query->whereNull('deleted_at');
        }

        $result = $query->first();

        if ($result && $result->{$this->column} == $this->columnValue) {
            return true;
        }

        return is_null($result);
    }

    public function message()
    {
        return 'O valor para :attribute já está em uso!';
    }

    private function tabelaTemSoftDeletes(string $table): bool
    {
        return SoftDeleteSchema::tem($table);
    }
}
