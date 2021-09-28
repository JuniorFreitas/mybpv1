<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

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
