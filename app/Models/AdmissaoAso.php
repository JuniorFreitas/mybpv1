<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MasterTag\DataHora;


class AdmissaoAso extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 
        'admissao_id',
        'user_alterou_id',
        'data_aso',
        'data_vencimento',
        'ativo'
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'admissao_id' => 'int',
        'user_alterou_id' => 'int',
        'data_aso' => 'string',
        'data_vencimento' => 'string',
        'ativo' => 'boolean',
    ];

    //Acessor ->data_aso
    public function getDataAsoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_aso']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_aso
    public function setDataAsoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_aso'] = $data->dataInsert();
        }
    }

    public function Admissao(){
        return $this->hasOne(Admissao::class, 'id', 'admissao_id');
    }

    protected static function booted()
    {
       static::creating(function ($model) {
            $dataExpiracao = (new DataHora($model->data_aso))->addAno(1);

           $model->empresa_id = auth()->check() ? auth()->user()->empresa_id : $model->empresa_id;
           $model->data_vencimento = (new DataHora($dataExpiracao))->dataInsert();
       });

        static::updating(function ($model) {
            $dataExpiracao = (new DataHora($model->data_aso))->addAno(1);

            $model->empresa_id = auth()->check() ? auth()->user()->empresa_id : $model->empresa_id;
           $model->data_vencimento = (new DataHora($dataExpiracao))->dataInsert();

        });
    }
    

}
