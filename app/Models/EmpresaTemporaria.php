<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaTemporaria extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = [
        "empresa_id",
        "razao_social",
        "dados",
        "ativo",
    ];

    protected $casts = [
        "id" => 'int',
        "empresa_id" => 'int',
        "razao_social" => 'string',
        "dados" => 'array',
        "ativo" => 'boolean',
    ];

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }
}
