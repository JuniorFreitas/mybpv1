<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvaliacaoFeedback extends Model
{
    use HasFactory, TenantTrait;

    protected $table = 'avaliacoes_feedbacks';

    protected $fillable = [
        'avaliacao_id',
        'empresa_id',
        'origem_feedback',
        'feedback_id',
        'avaliador_id',
        'nota_final_total',
        'inicio_feedback',
        'fim_feedback',
        'comentario',
        'status'
    ];

    protected $casts = [
        'id' => 'int',
        'avaliacao_id' => 'int',
        'empresa_id' => 'int',
        'origem_feedback' => 'string',
        'feedback_id' => 'int',
        'avaliador_id' => 'int',
        'nota_final_total' => 'int',
        'inicio_feedback' => 'string',
        'fim_feedback' => 'string',
        'comentario' => 'int',
        'status' => 'string'
    ];

    public function Avaliador()
    {
        return $this->hasOne(User::class, 'id', 'avaliador_id');
    }

    public function Funcionario()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }
}
