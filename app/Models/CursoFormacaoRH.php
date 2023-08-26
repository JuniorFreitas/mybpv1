<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;


/**
 * App\Models\CursoFormacaoRH
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property string $curso
 * @property string $instituicao
 * @property \Illuminate\Support\Carbon $emissao
 * @property \Illuminate\Support\Carbon|null $validade
 * @property bool|null $certificado
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH query()
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereCertificado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereCurso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereEmissao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereInstituicao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CursoFormacaoRH whereValidade($value)
 * @mixin \Eloquent
 */
class CursoFormacaoRH extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'curso_formacao_rh';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'curso_formacao_rh';
    public $timestamps = false;

    protected $fillable = [
        'feedback_id',
        'curriculo_id',
        'curso',
        'instituicao',
        'emissao',
        'validade',
        'certificado',

    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'curriculo_id' => 'int',
        'curso' => 'string',
        'instituicao' => 'string',
        'emissao' => 'date:d/m/Y',
        'validade' => 'date:d/m/Y',
        'certificado' => 'boolean',

    ];

    //Modificador ->emissao
    public function setEmissaoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['emissao'] = $data->dataInsert();
    }

    //Modificador ->validade
    public function setValidadeAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['validade'] = $data->dataInsert();
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }
}
