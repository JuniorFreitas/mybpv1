<?php

namespace App\Models;

use App\Models\Pivot\TreinamentoVencimento;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Treinamento
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property int|null $cadastrou
 * @property string|null $tipo parada, fixo
 * @property int|null $gerou_id
 * @property string|null $data_envio
 * @property bool|null $enviado_email
 * @property int|null $enviou_id
 * @property string|null $email_envio
 * @property bool|null $email_aberto
 * @property \Illuminate\Support\Carbon|null $data_email_aberto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
 * @property-read \App\Models\User|null $QuemCadastrou
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vencimento> $Vencimentos
 * @property-read int|null $vencimentos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $token
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereCadastrou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereDataEmailAberto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereDataEnvio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereEmailAberto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereEmailEnvio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereEnviadoEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereEnviouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereGerouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Treinamento whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Treinamento extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'treinamento';
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
        'cadastrou',
        'tipo',
        'gerou_id',
        'data_envio',
        'enviado_email',
        'enviou_id',
        'email_envio',
        'email_aberto',
        'data_email_aberto',

    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'cadastrou' => 'int',
        'tipo' => 'string',
        'gerou_id' => 'int',
        'data_envio' => 'string',
        'enviado_email' => 'boolean',
        'enviou_id' => 'int',
        'email_envio' => 'string',
        'email_aberto' => 'boolean',
        'data_email_aberto' => 'date:d/m/Y \\à\\s H:m\\h',
        'created_at' => 'date:d/m/Y \\à\\s H:m\\h',
        'updated_at' => 'date:d/m/Y \\à\\s H:m\\h',
    ];

    protected $appends = ['token'];

    public function getTokenAttribute()
    {
        return \Crypt::encrypt($this->attributes['id']);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    //    public function getUpdatedAtAttribute($value)
    //    {
    //        if ($value) {
    //            $data = new DataHora($this->attributes['updated_at']);
    //            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
    //        }
    //    }

    public function FeedbackCurriculo()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function arquivosVencimentos()
    {
        return $this->hasManyThrough(
            Arquivo::class,
            TreinamentoVencimento::class,
            'treinamento_id',
            'id',
            'id',
            'arquivo_id'
        );
    }

    public function Vencimentos()
    {
        return $this->belongsToMany(Vencimento::class, 'treinamento_vencimento', 'treinamento_id', 'vencimento_id')
            ->using(TreinamentoVencimento::class)
            ->withPivot(['data_vencimento', 'data_treinamento', 'numero_fat', 'arquivo_id'])->orderBy('id');

        //        return $this
        //            ->belongsToMany(Vencimento::class, 'treinamento_vencimento')
        //            ->using(TreinamentoVencimento::class)           // pivot customizado
        //            ->withPivot([
        //                'data_vencimento',
        //                'data_treinamento',
        //                'numero_fat',
        //                'arquivo_id',                              // novo campo
        //            ]);
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }

    public function QuemCadastrou()
    {
        return $this->hasOne(User::class, 'id', 'cadastrou');
    }
}
