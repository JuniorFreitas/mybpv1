<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class AvaliacaoResposta extends Model
{
    use HasFactory, TenantTrait;

    protected $table = "avaliacoes_respostas";

    protected $fillable = [
        'empresa_id',
        'avaliacao_feedback_id',
        'topico_id',
        'nota'
    ];

    protected $casts = [
        'id' => 'int',
        'topico_id' => 'int',
        'avaliacao_feedback_id' => 'int',
        'empresa_id' => 'int',
        'nota' => 'int'
    ];

    public $timestamps = false;
}
