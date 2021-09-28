<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\GruposChat
 *
 * @property int $id
 * @property string $nome
 * @property int $empresa_id
 * @property int $criou_id
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|GruposChat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GruposChat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GruposChat query()
 * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereCriouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GruposChat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GruposChat extends Model
{
    use HasFactory,LogsActivity;
    protected static $logFillable = true;
    protected static $logName = 'GrupoChat';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps = true;
    protected $table = 'grupos_chat';
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
        'nome' => 'string',
        'empresa_id' => 'int',
        'criou_id' => 'int',

        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

}
