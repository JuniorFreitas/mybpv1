<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MasterTag\DataHora;

/**
 * App\Models\AtaReuniaoAcao
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string $responsavel
 * @property string $email
 * @property string $acao
 * @property string|null $prazo
 * @property int|null $continuo
 * @property string|null $observacao
 * @property string $status
 * @property-read \App\Models\AtaReuniao|null $AtaReuniao
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao query()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereContinuo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao wherePrazo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereResponsavel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAcao whereStatus($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class AtaReuniaoAcao extends Model
{

    use LogsActivity, HasActivitylogOptions, SoftDeletes;

    public const STATUS_NAO_INICIADA = 'nao_iniciada';
    public const STATUS_EM_ANDAMENTO = 'em_andamento';
    public const STATUS_AGUARDANDO_TERCEIRO = 'aguardando_terceiro';
    public const STATUS_AGUARDANDO_VALIDACAO = 'aguardando_validacao';
    public const STATUS_CONCLUIDA = 'concluida';
    public const STATUS_ATRASADA = 'atrasada';
    public const STATUS_CANCELADA = 'cancelada';
    public const STATUS_REPROGRAMADA = 'reprogramada';

    protected static $logName = 'AtaReuniaoAcao';
    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'titulo',
        'descricao',
        'responsavel',
        'responsavel_id',
        'criado_por',
        'email',
        'acao',
        'prazo',
        'continuo',
        'observacao',
        'status',
        'prioridade',
        'percentual_conclusao',
        'evidencia_esperada',
        'data_conclusao',
        'validador_id',
        'validado_em',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'titulo' => 'string',
        'descricao' => 'string',
        'responsavel' => 'string',
        'responsavel_id' => 'int',
        'criado_por' => 'int',
        'email' => 'string',
        'acao' => 'string',
        'continuo' => 'boolean',
        'observacao' => 'string',
        'status' => 'string',
        'prioridade' => 'string',
        'percentual_conclusao' => 'int',
        'evidencia_esperada' => 'string',
        'data_conclusao' => 'datetime',
        'validador_id' => 'int',
        'validado_em' => 'datetime',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    //Acessor ->prazo
    public function getPrazoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['prazo']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->prazo
    public function setPrazoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['prazo'] = $data->dataInsert();
        }
    }


    public $timestamps = true;

    public function AtaReuniao()
    {
        return $this->hasOne(AtaReuniao::class, 'id', 'ata_reuniao_id');
    }

    public function ResponsavelUsuario()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'responsavel_id');
    }
}
