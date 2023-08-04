<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\IntermitenteTipo
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteTipo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IntermitenteTipo extends Model
{
    use TenantTrait;

    protected $fillable = ['label', 'ativo','empresa_id'];
    protected $casts = ['label' => 'string', 'ativo' => 'boolean','empresa_id' => 'int'];

}
