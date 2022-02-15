<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

class DadosAdmissao extends Model
{
    use HasFactory;

    protected $fillable = [
        'admissao_id',
        'ctps_numero',
        'ctps_serie',
        'ctps_data_emissao',
        'titulo_eleitor_numero',
        'titulo_eleitor_sessao',
        'titulo_eleitor_zona',
    ];

    protected $casts = [
        'admissao_id' => 'int',
        'ctps_numero' => 'string',
        'ctps_serie' => 'string',
        'ctps_data_emissao' => 'string',
        'titulo_eleitor_numero' => 'string',
        'titulo_eleitor_sessao' => 'string',
        'titulo_eleitor_zona' => 'string',
    ];

    public $timestamps = false;

    //Acessor ->ctps_data_emissao
    public function getCtpsDataEmissaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['ctps_data_emissao']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->ctps_data_emissao
    public function setCtpsDataEmissaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['ctps_data_emissao'] = $data->dataInsert();
        }
    }

//    public function Admissao()
//    {
//        return $this->hasOne(Admissao::class, 'id','admissao_id');
//    }

}
