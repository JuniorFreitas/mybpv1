<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\CertificadoSgi
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $treinamento_evento_id
 * @property int $pessoa_evento_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $0
 * @property mixed $1
 * @property mixed $2
 * @property mixed $3
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi query()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi wherePessoaEventoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereTreinamentoEventoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoSgi whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CertificadoSgi extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'CertificadoSgi';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'certificado_sgis';
    protected $fillable = [
        'cliente_id',
        'treinamento_evento_id',
        'pessoa_evento_id',
    ];
    protected $casts = [
        'id',
        'cliente_id',
        'treinamento_evento_id',
        'pessoa_evento_id',
    ];
}
