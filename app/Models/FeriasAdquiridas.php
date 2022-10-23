<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\FeriasAdquiridas
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property mixed $data_limite
 * @property mixed $data_retorno
 * @property mixed $data_saida
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas query()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Admissao[] $Admissao
 * @property-read int|null $admissao_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $UsuarioCadastrou
 * @property-read int|null $usuario_cadastrou_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $UsuarioEditou
 * @property-read int|null $usuario_editou_count
 * @property int $id
 * @property int $admissao_id
 * @property string $periodo_gozado
 * @property int $qnt_dias
 * @property string $proximo_periodo
 * @property int $user_cadastrou_id
 * @property int|null $user_alterou_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereAdmissaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereDataLimite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereDataRetorno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereDataSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas wherePeriodoGozado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereProximoPeriodo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereQntDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereUserAlterouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasAdquiridas whereUserCadastrouId($value)
 */
class FeriasAdquiridas extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'ferias_adquiridas';
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
        'admissao_id',
        'periodo_gozado',
        'qnt_dias',
        'data_saida',
        'data_retorno',
        'proximo_periodo',
        'data_limite',
        'user_cadastrou_id',
        'user_alterou_id',
    ];

    protected $casts = [
        'id' => 'int',
        'admissao_id' => 'int',
        'periodo_gozado' => 'string',
        'qnt_dias' => 'int',
        'data_saida' => 'string',
        'data_retorno' => 'string',
        'proximo_periodo' => 'string',
        'data_limite' => 'string',
        'user_cadastrou_id' => 'int',
        'user_alterou_id' => 'int',
    ];

    //Acessor ->data_saida
    public function getDataSaidaAttribute($value)
    {
        $data = new DataHora($this->attributes['data_saida']);
        return $data->dataCompleta();
    }

    //Modificador ->data_saida
    public function setDataSaidaAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_saida'] = $data->dataInsert();
    }

    //Acessor ->data_retorno
    public function getDataRetornoAttribute($value)
    {
        $data = new DataHora($this->attributes['data_retorno']);
        return $data->dataCompleta();
    }

    //Modificador ->data_retorno
    public function setDataRetornoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_retorno'] = $data->dataInsert();
    }

    //Acessor ->data_limite
    public function getDataLimiteAttribute($value)
    {
        $data = new DataHora($this->attributes['data_limite']);
        return $data->dataCompleta();
    }

    //Modificador ->data_limite
    public function setDataLimiteAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_limite'] = $data->dataInsert();
    }

    public function Admissao()
    {
        return $this->hasMany(Admissao::class, 'id', 'admissao_id');
    }

    public function UsuarioCadastrou()
    {
        return $this->hasMany(User::class, 'id', 'user_cadastrou_id')->select(['id', 'nome']);
    }

    public function UsuarioEditou()
    {
        return $this->hasMany(User::class, 'id', 'user_editou_id')->select(['id', 'nome']);
    }


    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_cadastrou_id = auth()->id();
        });

        static::updating(function ($model) {
            $model->user_cadastrou_id = auth()->id();
        });
    }
}
