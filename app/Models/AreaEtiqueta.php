<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AreaEtiqueta
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta query()
 * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AreaEtiqueta whereLabel($value)
 * @mixin \Eloquent
 */
class AreaEtiqueta extends Model
{
    use HasFactory;
    use TenantTrait;

    protected $fillable = ['label', 'ativo', 'empresa_id'];
    protected $casts = ['id' => 'int', 'label' => 'string', 'ativo' => 'boolean', 'empresa_id' => 'int'];

    public function usesTimestamps()
    {
        return false;
    }

}
