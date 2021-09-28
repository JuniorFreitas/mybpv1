<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\MensagemChat
 *
 * @property int $id
 * @property int $de_id
 * @property int|null $para_id
 * @property int|null $grupo_id
 * @property string $tipo
 * @property string|null $mensagem
 * @property int|null $arquivo_id
 * @property bool $visto
 * @property \datetime|null $datahora_visto
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property-read \App\Models\User|null $De
 * @property-read \App\Models\GruposChat|null $Grupo
 * @property-read \App\Models\User|null $Para
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat query()
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereArquivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereDatahoraVisto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereDeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereGrupoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereMensagem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereParaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MensagemChat whereVisto($value)
 * @mixin \Eloquent
 */
class MensagemChat extends Model {
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'MensagemChat';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps = true;
    protected $table = 'mensagem_chats';
    protected $fillable = [
        'para_id',
        'grupo_id',
        'tipo'  ,
        'mensagem' ,
        'arquivo_id' ,
        'visto'  ,
        'datahora_visto' ,
    ];
    protected $casts = [
        'id' => 'int',
        'de_id' => 'int',
        'para_id' => 'int',
        'grupo_id' => 'int',
        'tipo' => 'string',
        'mensagem' => 'string',
        'arquivo_id' => 'int',
        'visto' => 'boolean',
        'datahora_visto' => 'datetime:d/m/Y H:i',


        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected $with=['De','Para','Grupo'];

    public const TIPO_TEXT='txt';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }



    protected static function booted() {
        static::creating(function ($model) {
            $model->de_id = auth()->id();
        });

    }

    /*public function Anexos(){
        return $this->belongsToMany(Arquivo::class,'tarefa_anexos','tarefa_id','arquivo_id');
    }*/

    public function De(){
        return $this->hasOne(User::class,'id','de_id')->select(['id','nome','empresa_id']);
    }

    public function Para(){
        return $this->hasOne(User::class,'id','para_id')->select(['id','nome','empresa_id']);
    }

    public function Grupo(){
        return $this->hasOne(GruposChat::class,'id','grupo_id');
    }
}
