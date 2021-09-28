<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\RequisicaoVaga
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $centro_custo_id
 * @property int $cargo_id
 * @property int|null $area_id
 * @property int $quantidade
 * @property string $tipo_contratacao
 * @property string $prioridade
 * @property bool $imediata
 * @property string|null $previsao_inicio
 * @property string|null $solicitante
 * @property int $user_id
 * @property string|null $observacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AreaEtiqueta|null $Area
 * @property-read \App\Models\Vaga|null $Cargo
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $User
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga query()
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereCargoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereImediata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga wherePrevisaoInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga wherePrioridade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereQuantidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereTipoContratacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\TipoContratacao|null $OutrasInformacoes
 * @property-read mixed $data_solicitacao
 */
class RequisicaoVaga extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'centro_custo_id',
        'cargo_id',
        'area_id',
        'quantidade',
        'tipo_contratacao',
        'prioridade',
        'imediata',
        'previsao_inicio',
        'solicitante',
        'user_id',
        'observacao',
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'centro_custo_id' => 'int',
        'cargo_id' => 'int',
        'area_id' => 'int',
        'quantidade' => 'int',
        'tipo_contratacao' => 'string',
        'prioridade' => 'string',
        'imediata' => 'boolean',
        'previsao_inicio' => 'date:d/m/Y',
        'solicitante' => 'string',
        'user_id' => 'int',
        'observacao' => 'string',
    ];

    protected $appends = ['data_solicitacao'];

    public function getDataSolicitacaoAttribute()
    {
        return (new DataHora($this->created_at))->dataCompleta();
    }

    public function setPrevisaoInicioAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['previsao_inicio'] = $data->dataInsert();
        } else {
            $this->attributes['previsao_inicio'] = null;
        }
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function CentroCusto()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'centro_custo_id');
    }

    public function Cargo()
    {
        return $this->hasOne(Vaga::class, 'id', 'cargo_id');
    }

    public function Area()
    {
        return $this->hasOne(AreaEtiqueta::class, 'id', 'area_id');
    }

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function OutrasInformacoes()
    {
        return $this->hasOne(TipoContratacao::class,'requisicao_vaga_id','id');
    }

    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = auth()->id();
        });

        static::updating(function ($model) {
            $model->user_id = auth()->id();
        });

        static::addGlobalScope(new ScopeClientesEmpresa);
    }
}
