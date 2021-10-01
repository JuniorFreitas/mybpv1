<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\Examesesmt
 *
 * @property int $id
 * @property int $exame_funcionario_id
 * @property int $empresa_id
 * @property bool $exame_realizado
 * @property array $resultado
 * @property mixed $data_realizacao
 * @property mixed $data_vencimento
 * @property bool $vencido
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt query()
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereDataRealizacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereDataVencimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereExameFuncionarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereExameRealizado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereResultado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Examesesmt whereVencido($value)
 * @mixin \Eloquent
 */
class Examesesmt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exame_funcionario_id',
        'empresa_id',
        'exame_realizado',
        'resultado',
        'data_realizacao',
        'data_vencimento',
        'vencido',
        'user_id',
    ];

    protected $casts = [
        'id' => 'int',
        'exame_funcionario_id' => 'int',
        'empresa_id' => 'int',
        'exame_realizado' => 'boolean',
        'resultado' => 'json',
        'data_realizacao' => 'date:d/m/Y',
        'data_vencimento' => 'date:d/m/Y',
        'vencido' => 'boolean',
        'user_id' => 'int',
    ];

    //Modificador ->data_realizacao
    public function setDataRealizacaoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_realizacao'] = $data->dataInsert();
    }

    //Modificador ->data_vencimento
    public function setDataVencimentoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_vencimento'] = $data->dataInsert();
    }

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
            $model->user_id = auth()->user()->id;
        });

        static::updating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
            $model->user_id = auth()->user()->id;
        });

        static::addGlobalScope(new ScopeEmpresa());
    }


}
