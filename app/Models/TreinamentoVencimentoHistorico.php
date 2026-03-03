<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreinamentoVencimentoHistorico extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'TreinamentoVencimentoHistorico';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

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
