<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;


/**
 * App\Models\AdmissaoAso
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $admissao_id
 * @property int|null $user_alterou_id
 * @property string $data_aso
 * @property string $data_vencimento
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admissao|null $Admissao
 * @property-read mixed $data_vencimento_formatada
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereAdmissaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereDataAso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereDataVencimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissaoAso whereUserAlterouId($value)
 * @mixin \Eloquent
 */
class AdmissaoAso extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'admissao_aso';
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
        'empresa_id',
        'admissao_id',
        'user_alterou_id',
        'data_aso',
        'data_vencimento',
        'ativo'
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'admissao_id' => 'int',
        'user_alterou_id' => 'int',
        'data_aso' => 'string',
        'data_vencimento' => 'string',
        'ativo' => 'boolean',
    ];

    protected $appends = [
        'data_vencimento_formatada',
    ];

    public function getDataVencimentoFormatadaAttribute($value)
    {
        return (new DataHora($this->data_vencimento))->dataCompleta();
    }

    //Acessor ->data_aso
    public function getDataAsoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_aso']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_aso
    public function setDataAsoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_aso'] = $data->dataInsert();
        }
    }

    public function Admissao(){
        return $this->hasOne(Admissao::class, 'id', 'admissao_id');
    }

    protected static function booted()
    {
       static::creating(function ($model) {
            $dataExpiracao = (new DataHora($model->data_aso))->addAno(1);

           $model->empresa_id = auth()->check() ? auth()->user()->empresa_id : $model->empresa_id;
           $model->data_vencimento = (new DataHora($dataExpiracao))->dataInsert();
       });

        static::updating(function ($model) {
            $dataExpiracao = (new DataHora($model->data_aso))->addAno(1);

            $model->empresa_id = auth()->check() ? auth()->user()->empresa_id : $model->empresa_id;
           $model->data_vencimento = (new DataHora($dataExpiracao))->dataInsert();

        });
    }


}
