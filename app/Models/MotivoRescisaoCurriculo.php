<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MotivoRescisaoCurriculo
 *
 * @property int $motivo_id
 * @property int|null $feedback_id
 * @property string|null $outro
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo query()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo whereMotivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo whereOutro($value)
 * @mixin \Eloquent
 */
class MotivoRescisaoCurriculo extends Model
{
    use HasFactory;
    protected $table = 'motivo_rescisao_curriculo';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'motivo_id',
        'feedback_id',
        'outro',
    ];

    protected $casts = [
        'id' => 'int',
        'motivo_id' => 'int',
        'feedback_id' => 'int',
        'outro' => 'string',
    ];
}
