<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\PessoaEmpresa
 *
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property mixed $cpf
 * @property mixed $email
 * @method static \Illuminate\Database\Eloquent\Builder|PessoaEmpresa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PessoaEmpresa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PessoaEmpresa query()
 * @mixin \Eloquent
 */
class PessoaEmpresa extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'PessoaEmpresa';
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
        'cliente_id',
        'nome',
        'cpf',
        'email',
        'telefone',
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'nome' => 'string',
        'cpf' => 'string',
        'email' => 'string',
        'telefone' => 'string',
    ];

    public function getCpfAttribute($value)
    {
        return Sistema::transformCpfCnpj($this->attributes['cpf']);
    }

    public function setCpfAttribute($value)
    {
        $this->attributes['cpf'] = Sistema::transformCpfCnpj($value);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function getEmailAttribute($value)
    {
        return strtolower($this->attributes['email']);
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }
}
