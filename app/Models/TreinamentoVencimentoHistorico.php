<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreinamentoVencimentoHistorico extends Model
{
    use HasFactory, TenantTrait;

//    protected $table = 'treinamentos_vencimento_historicos';

    protected $fillable = [
        'feedback_id',
        'empresa_id',
        'treinamento_id',
        'user_id',
        'treinamentos_vencimentos',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'empresa_id' => 'int',
        'treinamento_id' => 'int',
        'user_id' => 'int',
        'treinamentos_vencimentos' => 'json'
    ];

    public function getTreinamentosVencimentosAttribute($value)
    {
        return json_decode($value);
    }
}
