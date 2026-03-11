<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $feedback_id
 * @property int $empresa_id
 * @property int $treinamento_id
 * @property int $user_id
 * @property array<array-key, mixed> $treinamentos_vencimentos
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico whereTreinamentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico whereTreinamentosVencimentos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TreinamentoVencimentoHistorico whereUserId($value)
 * @mixin \Eloquent
 */
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
