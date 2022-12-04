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
        'funcionario_id',
        'feedback_id',
        'origem_feedback',
        'principal',
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
        'feedback_id' => 'int',
        'funcionario_id' => 'int',
        'principal' => 'boolean',
        'origem_feedback' => 'string',
        'avaliador_id' => 'int',
        'nota_final_total' => 'int',
        'inicio_feedback' => 'string',
        'fim_feedback' => 'string',
        'comentario' => 'int',
        'status' => 'string'
    ];

    public $timestamps = false;

    const ORIGEM_FUNCIONARIO = 'Funcionario';
    const ORIGEM_AVALIADOR = 'Avaliador';
    const STATUS_AGUARDANDO = 'aguardando';

    public function Avaliador()
    {
        return $this->hasOne(User::class, 'id', 'avaliador_id');
    }

    public function Funcionario()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function scopeOrigemAvaliador($query)
    {
        return $query->where('origem_feedback', AvaliacaoFeedback::ORIGEM_AVALIADOR);
    }
}
