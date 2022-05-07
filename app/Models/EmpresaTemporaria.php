<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EmpresaTemporaria
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $razao_social
 * @property array $dados
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereDados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereRazaoSocial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTemporaria whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
