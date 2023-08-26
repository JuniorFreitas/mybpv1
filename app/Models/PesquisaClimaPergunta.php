<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PesquisaClimaPergunta
 *
 * @property int $id
 * @property int $tipo_id
 * @property string $pergunta
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesquisaClimaPerguntaRespostaCandidato> $PerguntaResposta
 * @property-read int|null $pergunta_resposta_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesquisaClimaPerguntaResposta> $Resposta
 * @property-read int|null $resposta_count
 * @property-read \App\Models\PesquisaClimaTipo|null $Tipo
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta query()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta wherePergunta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPergunta whereTipoId($value)
 * @mixin \Eloquent
 */
class PesquisaClimaPergunta extends Model
{
    protected $fillable = [
        'tipo_id',
        'pergunta',
        'ativo'
    ];

    public $timestamps = false;

    protected $casts = [
        'tipo_id' => 'int',
        'pergunta' => 'string',
        'ativo' => 'boolean'
    ];


    public function Tipo()
    {
        return $this->hasOne(PesquisaClimaTipo::class, 'id', 'tipo_id');
    }

    public function Resposta()
    {
        return $this->hasMany(PesquisaClimaPerguntaResposta::class, 'pergunta_id', 'id');
    }

    public function PerguntaResposta()
    {
        return $this->hasMany(PesquisaClimaPerguntaRespostaCandidato::class, 'pergunta_id', 'id');
    }

}
