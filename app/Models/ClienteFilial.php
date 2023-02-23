<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ClienteFilial
 *
 * @property int $id
 * @property int $empresa_id
 * @property mixed $dados
 * @property bool $ativo
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Cliente $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial newQuery()
 * @method static \Illuminate\Database\Query\Builder|ClienteFilial onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial userEmpresa()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereDados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ClienteFilial withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ClienteFilial withoutTrashed()
 * @mixin \Eloquent
 */
class ClienteFilial extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'ClienteFilial';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName)
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    public function usesTimestamps()
    {
        return false;
    }

    protected $fillable = [
        'empresa_id',
        'dados',
        'ativo'
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'dados' => 'json',
        'ativo' => 'boolean'
    ];

    /**
     * @param $value
     * @return mixed
     */
    public function getDadosAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }

    /**
     * @return object
     */
    public static function DTO(): object
    {
        return (object)[
            'cnpj' => '',
            'razao_social' => '',
            'nome_fantasia' => '',
            'area_id' => '',
            'ramo' => '',
            'cep' => '',
            'logradouro' => '',
            'end_numero' => '',
            'complemento' => '',
            'bairro' => '',
            'municipio' => '',
            'uf' => '',
            'tel_principal' => '',
            'contato' => '',
            'email' => '',
        ];
    }

    public function scopeUserEmpresa($query)
    {
        return $query->where('empresa_id', auth()->user()->empresa_id);
    }
}
