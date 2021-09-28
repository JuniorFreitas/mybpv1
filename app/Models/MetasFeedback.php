<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\MetasFeedback
 *
 * @property int $id
 * @property int $feedback_id
 * @property string $nome
 * @property string $descricao
 * @property mixed $data_inicio
 * @property mixed $data_fim
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MetasFeedback whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MetasFeedback extends Model
{
    protected $fillable = [
        'feedback_id',
        'nome',
        'descricao',
        'data_inicio',
        'data_fim',
    ];
    protected $casts = [
        'feedback_id' => 'int',
        'nome' => 'string',
        'descricao' => 'string',
        'data_inicio' => 'date:d/m/Y',
        'data_fim' => 'date:d/m/Y',
    ];

    protected $table = 'metas_feedbacks';

    //Acessor ->data_fim
    public function getDataFimAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_fim']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_fim
    public function setDataFimAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_fim'] = $data->dataInsert();
        }
    }

    //Acessor ->data_inicio
    public function getDataInicioAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_inicio']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_inicio
    public function setDataInicioAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_inicio'] = $data->dataInsert();
        }
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }
}
