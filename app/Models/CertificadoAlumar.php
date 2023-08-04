<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\CertificadoAlumar
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property int $cliente_id
 * @property bool $nacional
 * @property int|null $empresa_treinamento_trinta_tres_id
 * @property int|null $empresa_treinamento_trinta_cinco_id
 * @property int|null $instrutor_trinta_tres_id
 * @property int|null $instrutor_trinta_cinco_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTreinamentoTrintaCinco
 * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTreinamentoTrintaTres
 * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTrintaCinco
 * @property-read \App\Models\EmpresaTreinamento|null $EmpresaTrintaTres
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\Instrutor|null $InstrutorTrintaCinco
 * @property-read \App\Models\Instrutor|null $InstrutorTrintaTres
 * @property-read \App\Models\Treinamento|null $Treinamento
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar query()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereEmpresaTreinamentoTrintaCincoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereEmpresaTreinamentoTrintaTresId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereInstrutorTrintaCincoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereInstrutorTrintaTresId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereNacional($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificadoAlumar whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CertificadoAlumar extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'certificado_alumar';
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

    protected $table = 'certificado_alumar';
    protected $fillable = [
        'id',
        'feedback_id',
        'cliente_id',
        'nacional',
        'empresa_treinamento_trinta_tres_id',
        'empresa_treinamento_trinta_cinco_id',
        'instrutor_trinta_tres_id',
        'instrutor_trinta_cinco_id',
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'cliente_id' => 'int',
        'nacional' => 'boolean',
        'empresa_treinamento_trinta_tres_id' => 'int',
        'empresa_treinamento_trinta_cinco_id' => 'int',
        'instrutor_trinta_tres_id' => 'int',
        'instrutor_trinta_cinco_id' => 'int',
    ];

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }


    public function Treinamento()
    {
        return $this->hasOne(Treinamento::class, 'feedback_id', 'feedback_id');
    }

    public function EmpresaTreinamentoTrintaTres()
    {
        return $this->hasOne(EmpresaTreinamento::class, 'id', 'empresa_treinamento_trinta_tres_id');
    }

    public function EmpresaTreinamentoTrintaCinco()
    {
        return $this->hasOne(EmpresaTreinamento::class, 'id', 'empresa_treinamento_trinta_cinco_id');
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function InstrutorTrintaTres()
    {
        return $this->hasOne(Instrutor::class, 'id', 'instrutor_trinta_tres_id');
    }

    public function InstrutorTrintaCinco()
    {
        return $this->hasOne(Instrutor::class, 'id', 'instrutor_trinta_cinco_id');
    }

    public function EmpresaTrintaTres()
    {
        return $this->hasOne(EmpresaTreinamento::class, 'id', 'empresa_treinamento_trinta_tres_id');
    }

    public function EmpresaTrintaCinco()
    {
        return $this->hasOne(EmpresaTreinamento::class, 'id', 'empresa_treinamento_trinta_cinco_id');
    }
}
