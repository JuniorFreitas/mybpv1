<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ClassificacaoRescisaoCurriculo
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo query()
 * @mixin \Eloquent
 * @property int $classificacao_id
 * @property int $curriculo_id
 * @property string|null $observacoes
 * @property string|null $quem_classificou
 * @property mixed|null $data_afastamento
 * @property string|null $preenchido_por
 * @property int|null $user_id
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereClassificacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereDataAfastamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereObservacoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo wherePreenchidoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereQuemClassificou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereUserId($value)
 * @property int|null $feedback_id
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisaoCurriculo whereFeedbackId($value)
 */
class ClassificacaoRescisaoCurriculo extends Model
{
    use HasFactory;

    protected $table = 'classificacao_rescisao_curriculo';
    public $timestamps = false;
    protected $primaryKey = 'curriculo_id';

    protected $fillable = [
        'classificacao_id',
        'curriculo_id',
        'observacoes',
        'quem_classificou',
        'data_afastamento',
        'preenchido_por',
        'user_id'
    ];

    protected $casts = [
        'classificacao_id' => 'int',
        'curriculo_id' => 'int',
        'observacoes' => 'string',
        'quem_classificou' => 'string',
        'data_afastamento' => 'date:d/m/Y',
        'preenchido_por' => 'string',
        'user_id' => 'int',
    ];
}
