<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Beneficio
 *
 * @property int $id
 * @property string $nome
 * @property int $tipobeneficio_id
 * @property int|null $cliente_id
 * @property float $valor
 * @property string $aplicacao
 * @property string $periodicidade
 * @property float $valor_descontado
 * @property string $opcao_desconto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Beneficio> $BeneficioFeedback
 * @property-read int|null $beneficio_feedback_count
 * @property-read \App\Models\User|null $Empresa
 * @property-read \App\Models\TipoBeneficio|null $TipoBeneficio
 * @property-read mixed $valor_format
 * @property-read mixed $valordescontado_format
 * @property-write mixed $valordescontado
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio query()
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereAplicacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereOpcaoDesconto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio wherePeriodicidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereTipobeneficioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereValor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Beneficio whereValorDescontado($value)
 * @mixin \Eloquent
 */
class Beneficio extends Model
{
    use LogsActivity, HasActivitylogOptions, TenantTrait;

    protected static $logName = 'Beneficio';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'nome',
        'tipobeneficio_id',
        'empresa_id',
        'valor',
        'aplicacao',
        'periodicidade',
        'valor_descontado',
        'opcao_desconto',
    ];

    protected $casts = [
        'nome' => 'string',
        'tipobeneficio_id' => 'int',
        'empresa_id' => 'int',
        'valor' => 'float',
        'aplicacao' => 'string',
        'periodicidade' => 'string',
        'valor_descontado' => 'float',
        'opcao_desconto' => 'string',
    ];

    protected $table = 'beneficios';


    protected $appends = ['valor_format', 'valordescontado_format'];

    //Modificador ->valor
    public function setValorAttribute($value)
    {
        if ($value) {
            $this->attributes['valor'] = Sistema::DinheiroInsert($value);
        }
    }

    public function getValorFormatAttribute()
    {
        return number_format($this->attributes['valor'], 2, ',', '.');
    }

    //Modificador ->valor
    public function setValordescontadoAttribute($value)
    {
        if ($value) {
            $this->attributes['valor_descontado'] = Sistema::DinheiroInsert($value);
        }
    }

    public function getValordescontadoFormatAttribute()
    {
        return number_format($this->attributes['valor_descontado'], 2, ',', '.');
    }

    public function TipoBeneficio()
    {
        return $this->hasOne(TipoBeneficio::class, 'id', 'tipobeneficio_id');
    }

    public function BeneficioFeedback()
    {
        return $this->belongsToMany(Beneficio::class, 'beneficio_feedbacks', 'beneficio_id', 'feedback_id');
    }

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

//    //Scopo de ClienteID (Empresa)
//    protected static function booted()
//    {
//        static::addGlobalScope(new ScopeClientesEmpresa);
//    }
}
