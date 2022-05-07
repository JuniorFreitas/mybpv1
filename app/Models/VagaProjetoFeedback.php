<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VagaProjetoFeedback
 *
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\VagaProjeto|null $VagaProjeto
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjetoFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjetoFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjetoFeedback query()
 * @mixin \Eloquent
 */
class VagaProjetoFeedback extends Model
{
    use HasFactory;
    protected $primaryKey = 'feedback_id';

    protected $fillable = [
        'feedback_id',
        'vaga_projeto_id'
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'vaga_projeto_id' => 'int'
    ];

    public function VagaProjeto()
    {
        return $this->hasOne(VagaProjeto::class, 'id', 'vaga_projeto_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }
}
