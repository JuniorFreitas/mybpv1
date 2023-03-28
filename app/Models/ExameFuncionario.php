<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use App\Scopes\ScopeEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\ExameFuncionario
 *
 * @property int $id
 * @property int $empresa_id
 * @property int $formulario_id
 * @property int $feedback_id
 * @property mixed $respostas
 * @property int $empresa_exame_id
 * @property int $user_encaminhou_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EmpresaExame|null $EmpresaExame
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\Formulario|null $Formulario
 * @property-read \App\Models\User|null $QuemEncaminhou
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereEmpresaExameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereRespostas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereUserEncaminhouId($value)
 * @mixin \Eloquent
 * @property string $token
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereToken($value)
 * @property-read \App\Models\Examesesmt|null $Sesmt
 * @property bool|null $pcmso
 * @property int|null $pcmso_id
 * @property int|null $exame_tipo_id
 * @property mixed|null $encaminhamento_data
 * @property-read \App\Models\ExameTipo|null $ExameTipo
 * @property-read \App\Models\Pcmso|null $PcmsoDados
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereEncaminhamentoData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario whereExameTipoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario wherePcmso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameFuncionario wherePcmsoId($value)
 */
class ExameFuncionario extends Model
{
    use HasFactory;

    protected $fillable = [
        "empresa_id",
        "formulario_id",
        "feedback_id",
        "respostas",
        "empresa_exame_id",
        "user_encaminhou_id",
        "token",
        "pcmso",
        "pcmso_id",
        "exame_tipo_id",
        "encaminhamento_data",
    ];

    protected $casts = [
        "id" => 'int',
        "empresa_id" => 'int',
        "formulario_id" => 'int',
        "feedback_id" => 'int',
        "respostas" => 'array',
        "empresa_exame_id" => 'int',
        "user_encaminhou_id" => 'int',
        "token" => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i\h',
        'updated_at' => 'datetime:d/m/Y à\s H:i\h',
        "pcmso" => 'boolean',
        "pcmso_id" => 'int',
        "exame_tipo_id" => 'int',
        'encaminhamento_data' => 'date:d/m/Y',
    ];

    public function getEncaminhamentoDataAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['encaminhamento_data']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->encaminhamento_data
    public function setEncaminhamentoDataAttribute($value)
    {
        $this->attributes['encaminhamento_data'] = null;
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['encaminhamento_data'] = $data->dataInsert();
        }
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function Formulario()
    {
        return $this->hasOne(Formulario::class, 'id', 'formulario_id');
    }

    public function EmpresaExame()
    {
        return $this->hasOne(EmpresaExame::class, 'id', 'empresa_exame_id');
    }

    public function Sesmt(){
        return $this->hasOne(Examesesmt::class, 'exame_funcionario_id', 'id');
    }

    public function QuemEncaminhou()
    {
        return $this->hasOne(User::class, 'id', 'user_encaminhou_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function PcmsoDados()
    {
        return $this->hasOne(Pcmso::class, 'id', 'pcmso_id');
    }

    public function ExameTipo()
    {
        return $this->hasOne(ExameTipo::class, 'id', 'exame_tipo_id');
    }


//    public function Colaborador()
//    {
//        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id')->with('Curriculo');
//    }

    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id ?? $model->empresa_id;
            $model->user_encaminhou_id = auth()->id() ?? $model->user_encaminhou_id;
        });

        static::updating(function ($model) {
            $model->user_encaminhou_id = $model->user_encaminhou_id ?? auth()->id();
        });

        static::addGlobalScope(new ScopeEmpresa());
    }
}
