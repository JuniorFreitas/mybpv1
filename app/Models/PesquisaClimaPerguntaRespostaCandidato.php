<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PesquisaClimaPerguntaRespostaCandidato
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $pergunta_id
 * @property int|null $resposta_id
 * @property string|null $respostadigitada
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\PesquisaClimaPergunta|null $Pergunta
 * @property-read \App\Models\PesquisaClimaPerguntaResposta|null $Resposta
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato query()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato wherePerguntaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereRespostaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereRespostadigitada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $cliente_id
 * @property-read \App\Models\Cliente|null $Cliente
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaRespostaCandidato whereClienteId($value)
 */
class PesquisaClimaPerguntaRespostaCandidato extends Model
{
    protected $fillable = [
        'feedback_id',
        'cliente_id',
        'pergunta_id',
        'resposta_id',
        'respostadigitada',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'pergunta_id' => 'int',
    ];

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id','feedback_id');
    }

    public function Pergunta()
    {
        return $this->hasOne(PesquisaClimaPergunta::class, 'id','pergunta_id');
    }

    public function Resposta()
    {
        return $this->hasOne(PesquisaClimaPerguntaResposta::class, 'id','resposta_id');
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id','cliente_id');
    }
}
