<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoAvisoCurriculo
 *
 * @property int $tipo_aviso_id
 * @property int|null $feedback_id
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoAvisoCurriculo whereTipoAvisoId($value)
 * @mixin \Eloquent
 */
class TipoAvisoCurriculo extends Model
{
    use HasFactory;

    protected $table = 'tipo_aviso_curriculo';
    public $timestamps = false;

    protected $fillable = [
        'tipo_aviso_id',
        'curriculo_id',
        'feedback_id',
    ];

    protected $casts = [
        'tipo_aviso_id' => 'int',
        'curriculo_id' => 'int',
        'feedback_id' => 'int',
    ];
}
