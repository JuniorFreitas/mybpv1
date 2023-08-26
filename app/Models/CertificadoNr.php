<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\CertificadoNr
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property string|null $nr_dez_instituicao
 * @property \Illuminate\Support\Carbon|null $nr_dez_emissao
 * @property \Illuminate\Support\Carbon|null $nr_dez_validade
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr query()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereNrDezEmissao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereNrDezInstituicao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoNr whereNrDezValidade($value)
 * @mixin \Eloquent
 */
class CertificadoNr extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'certificado_nr';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    public $timestamps = false;

    protected $fillable = [
        'feedback_id',
        'curriculo_id',
        'nr_dez_instituicao',
        'nr_dez_emissao',
        'nr_dez_validade',
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'curriculo_id' => 'int',
        'nr_dez_instituicao' => 'string',
        'nr_dez_emissao' => 'date:d/m/Y',
        'nr_dez_validade' => 'date:d/m/Y',
    ];

    //Modificador ->nr_dez_emissao
    public function setNrDezEmissaoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['nr_dez_emissao'] = $data->dataInsert();
    }

    //Modificador ->nr_dez_validade
    public function setNrDezValidadeAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['nr_dez_validade'] = $data->dataInsert();
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }
}
