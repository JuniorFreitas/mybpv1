<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PesquisaClimaResposta
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaResposta query()
 * @mixin \Eloquent
 */
class PesquisaClimaResposta extends Model
{
    protected $fillable = [
        'tipo_id',
        'feedback_id',
        'pergunta_id',
        'resposta',
    ];

    protected $casts = [
        'tipo_id' => 'int',
        'feedback_id' => 'int',
        'pergunta_id' => 'int',
        'resposta' => 'string',
    ];

}
