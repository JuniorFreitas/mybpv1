<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Exame
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $exame_tipo_id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Exame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Exame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Exame query()
 * @method static \Illuminate\Database\Eloquent\Builder|Exame whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exame whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exame whereExameTipoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exame whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Exame whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Exame extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'exame_tipo_id',
        'label',
        'ativo'
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'exame_tipo_id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean'
    ];


}
