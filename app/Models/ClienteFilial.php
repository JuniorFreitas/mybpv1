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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Cliente $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $endereco_completo
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial userEmpresa()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereDados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteFilial withoutTrashed()
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
    public function getEnderecoCompletoAttribute()
    {
        $endereco = $this->dados->logradouro;
        $bairro = $this->dados->bairro;
        $cep = $this->dados->cep;
        $numero = $this->dados->numero ?? 'S/N';
        $complemento = $this->dados->complemento;

        if ($complemento) {
            $endereco_completo = "{$endereco}, {$complemento}, {$numero}, {$bairro}, {$cep}, {$this->dados->municipio}-{$this->dados->uf}";
        } else {
            $endereco_completo = "{$endereco}, {$numero}, {$bairro}, {$cep}, {$this->dados->municipio}-{$this->dados->uf}";
        }

        return $endereco_completo;
    }

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

    /**
     * @param null $empresa_id
     * @return bool
     */
    public function temFilial($empresa_id = null)
    {
        $empresa_id = $empresa_id ?? auth()->user()->empresa_id;
        return $this->where('empresa_id', $empresa_id)->where('ativo',true)->count() > 0;
    }

    public function getListaFilialAtiva($empresa_id = null)
    {
        $empresa_id = $empresa_id ?? auth()->user()->empresa_id;
        return $this->select(['id','dados','ativo'])->where('empresa_id', $empresa_id)->where('ativo',true)->orderBy('dados->razao_social')->get();
    }

    public function scopeUserEmpresa($query)
    {
        return $query->where('empresa_id', auth()->user()->empresa_id);
    }
}
