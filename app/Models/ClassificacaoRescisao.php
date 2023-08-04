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
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereClasse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassificacaoRescisao wherePeriodo($value)
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
