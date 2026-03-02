<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\ServicosProspects
 *
 * @property int $id
 * @property int|null $cliente_id
 * @property int|null $servico_id
 * @property \Illuminate\Support\Carbon $data_envio_proposta
 * @property string|null $escopo
 * @property string $status
 * @property string|null $feedback
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\Servico|null $Servico
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereDataEnvioProposta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereEscopo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereServicoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosProspects whereStatus($value)
 * @mixin \Eloquent
 */
class ServicosProspects extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'servico_prospect';
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

    public function usesTimestamps(): bool
    {
        return false;
    }

    protected $fillable = [
        'id',
        'cliente_id',
        'servico_id',
        'data_envio_proposta',
        'escopo',
        'status',
        'feedback',
    ];
    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'servico_id' => 'int',
        'data_envio_proposta' => 'date:d/m/Y',
        'escopo' => 'string',
        'status' => 'string',
        'feedback' => 'string',
    ];

    //Modificador ->data_envio_proposta
    public function setDataEnvioPropostaAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_envio_proposta'] = $data->dataInsert();
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function Servico()
    {
        return $this->hasOne(Servico::class, 'id', 'servico_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'prospect_servicos_imagens', 'servicos_prospect_id', 'arquivo_id');
    }
}
