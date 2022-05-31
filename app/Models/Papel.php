<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Papel
 *
 * @property int $id
 * @property string $nome
 * @property string $descricao
 * @property string $email
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Habilidade[] $habilidades
 * @property-read int|null $habilidades_count
 * @method static \Illuminate\Database\Eloquent\Builder|Papel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Papel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Papel query()
 * @method static \Illuminate\Database\Eloquent\Builder|Papel whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Papel whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Papel whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Papel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Papel whereNome($value)
 * @mixin \Eloquent
 * @property int|null $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|Papel whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Papel clinica()
 * @method static \Illuminate\Database\Eloquent\Builder|Papel notClinica()
 */
class Papel extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'papel';
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

    protected $table = 'papeis';
    protected $fillable = [
        'id', 'nome', 'email', 'descricao', 'ativo', 'master', 'empresa_id'
    ];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'descricao' => 'string',
        'email' => 'string',
        'empresa_id' => 'int',
        'ativo' => 'boolean',
        'master' => 'boolean',
    ];

    public function usesTimestamps(): bool
    {
        return false;
    }

    public function habilidades()
    {
        return $this->belongsToMany(Habilidade::class, 'papeis_habilidades');
    }

    public function scopeNotClinica($query)
    {
        return $query->where('nome', 'NOT LIKE', '%Clinica Exame');
    }

    public function scopeClinica($query)
    {
        return $query->where('nome', 'LIKE', '%Clinica Exame');
    }

    //Scopo de ClienteID (Empresa)
//    protected static function booted() {
//        static::creating(function ($model) {
//            $model->empresa_id = auth()->user()->empresa_id;
//        });
//        static::addGlobalScope(new ScopeEmpresa);
//    }
}
