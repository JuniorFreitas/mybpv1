<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\EscalaJornada
 *
 * @property int $id
 * @property int $escala_id
 * @property int $ocorrencia_id
 * @property string $tipo
 * @property int $repetir
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_deletou_id
 * @property-read \App\Models\EmpresaEscala|null $Escala
 * @property-read \App\Models\OcorrenciaJornada|null $Ocorrencia
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PeriodoJornada[] $Periodos
 * @property-read int|null $periodos_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada newQuery()
 * @method static \Illuminate\Database\Query\Builder|EscalaJornada onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada query()
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereEscalaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereOcorrenciaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereRepetir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EscalaJornada whereUserDeletouId($value)
 * @method static \Illuminate\Database\Query\Builder|EscalaJornada withTrashed()
 * @method static \Illuminate\Database\Query\Builder|EscalaJornada withoutTrashed()
 * @mixin \Eloquent
 */
class EscalaJornada extends Model
{
    use HasFactory,LogsActivity, SoftDeletes;
    protected static $logFillable = true;
    protected static $logName = 'EmpresaJornada';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps=false;
    protected $table = 'escala_jornadas';
    protected $fillable = [
        'escala_id' ,
        'ocorrencia_id' ,
        'repetir' ,
    ];
    protected $casts = [
        'id' => 'int',
        'escala_id' => 'int',
        'ocorrencia_id' => 'int',
        'repetir' => 'int',

    ];

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    public function Escala(){
        return $this->hasOne(EmpresaEscala::class,'id','escala_id');
    }

    public function Ocorrencia(){
        return $this->hasOne(OcorrenciaJornada::class,'id','ocorrencia_id')
            ->withoutGlobalScopes();
    }

    public function Periodos(){
        return $this->hasMany(PeriodoJornada::class,'jornada_id','id');
    }

    public function getTotalMinutos() {
        $total = 0; // em minutos
        foreach ($this->Periodos as $periodo) {
            $inicio = new DataHora($periodo->entrada);
            $fim = new DataHora($periodo->saida);
            $total += DataHora::diferencaMinutos($inicio->horaInsert(), $fim->horaInsert());
        }
        return $total;
    }
}
