<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CihTag
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $label
 * @property bool $ativo
 * @property bool $anexo_obrigatorio
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereAnexoObrigatorio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CihTag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CihTag extends Model
{
    use HasFactory, TenantTrait;

    protected $table = 'cih_tags';
    protected $fillable = [
        'empresa_id',
        'label',
        'ativo',
        'anexo_obrigatorio'
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean',
        'anexo_obrigatorio' => 'boolean'
    ];
}
