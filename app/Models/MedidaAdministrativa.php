<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\MedidaAdministrativa
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $user_id
 * @property string|null $solicitante
 * @property string $tipo
 * @property string|null $definicao
 * @property string|null $motivo
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $causa
 * @property mixed $data_solicitacao
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa query()
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereCausa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereDataSolicitacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereDefinicao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 * @property string|null $data_retorno
 * @method static \Illuminate\Database\Eloquent\Builder|MedidaAdministrativa whereDataRetorno($value)
 */
class MedidaAdministrativa extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'MedidaAdministrativa';
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

    protected $fillable = [
        'feedback_id',
        'user_id',
        'solicitante',
        'tipo',
        'definicao',
        'motivo',
        'causa',
        'data_solicitacao',
        'data_retorno'
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'user_id' => 'int',
        'solicitante' => 'string',
        'tipo' => 'string',
        'definicao' => 'string',
        'motivo' => 'string',
        'causa' => 'string',
        'data_solicitacao' => 'string',
        'data_retorno' => 'string',
        'created_at' => 'date:d/m/Y'
    ];

    const TIPOS = [
        'Re-orientação',
        'Advertência Verbal',
        'Advertência Escrita',
        'Suspensão de 1 dia',
        'Suspensão de 2 ou 3 dias',
        'Suspensão acima de 3 dias',
        'Desligamento',
    ];

    const CAUSAS = [
        'Comportamentos Contrários aos Valores',
        'Inadequação no desempenho das funções',
        'Desrespeito às normas de SSMA',
        'Descumprimento dos procedimentos internos',
        'Insubordinação',
        'Desidia',
        'Sob efeito ou uso de drogas',
        'Negociação administrativa e comercial sem consentimento',
        'Violação de segredos estratégicos',
        'Abandono de emprego',
        'Abandono do local de serviço',
        'Aceitação de vantagens oferecidas por terceiros',
        'Outros comportamentos contrários ao contrato',
    ];

    const DEFINICAO = [
        'DESIDIA',
        'INSUBORDINAÇÃO',
        'MAU PROCEDIMENTO',
    ];

    public function getTipoMedidaAttribute($value)
    {
        switch ($this->attributes['tipo']) {
            case 'Re-orientação':
                return 'reorientado';
            case 'Advertência Verbal':
            case 'Advertência Escrita':
                return 'advertido';
            default:
                return 'suspenso';
        }
    }

    public function getDataSolicitacaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_solicitacao']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_fim
    public function setDataSolicitacaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_solicitacao'] = $data->dataInsert();
        }
    }

    public function getDataRetornoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_retorno']);
            return $data->dataCompleta();
        }
        return null;
    }

    //Modificador ->data_fim
    public function setDataRetornoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_retorno'] = $data->dataInsert();
        }
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'medida_evidencia', 'medida_id', 'arquivo_id');
    }

}
