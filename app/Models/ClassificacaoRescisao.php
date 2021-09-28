<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ClassificacaoRescisao
 *
 * @property int $id
 * @property string $classe
 * @property string $descricao
 * @property string $periodo
 * @property bool $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClassificacaoRescisao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClassificacaoRescisao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClassificacaoRescisao query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClassificacaoRescisao whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClassificacaoRescisao whereClasse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClassificacaoRescisao whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClassificacaoRescisao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ClassificacaoRescisao wherePeriodo($value)
 * @mixin \Eloquent
 */
class ClassificacaoRescisao extends Model
{
    protected $table = 'classificacao_rescisao';
    protected $fillable = [
        'classe',
        'descricao',
        'periodo',
        'ativo',
    ];
    protected $casts = [
        'id' => 'int',
        'classe' => 'string',
        'descricao' => 'string',
        'periodo' => 'string',
        'ativo' => 'boolean',
    ];
    public $timestamps = false;
}
