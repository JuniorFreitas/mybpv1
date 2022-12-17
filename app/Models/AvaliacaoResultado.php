<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

class AvaliacaoResultado extends Model
{
    use HasFactory, TenantTrait;

    protected $table = "avaliacoes_resultados";

    protected $fillable = [
        'avaliacao_feedback_id',
        'gestor_id',
        'topico_id',
        'plano_de_acao',
        'responsavel',
        'empresa_id',
        'inicio',
        'termino',
        'status',
        'dados_extras',
    ];

    protected $casts = [
        'id' => 'int',
        'avaliacao_feedback_id' => 'int',
        'gestor_id' => 'int',
        'topico_id' => 'int',
        'plano_de_acao' => 'string',
        'responsavel' => 'string',
        'empresa_id' => 'int',
        'inicio' => 'date',
        'termino' => 'date',
        'status' => 'string',
        'dados_extras' => 'json',
    ];

    public $timestamps = false;

    const STATUS_DEFINIDO = 'Definido';

    public function getDadosExtrasAttribute($value)
    {
        return json_decode($value);
    }

    //Acessor ->data_fim
    public function getInicioAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['inicio']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_inicio
    public function setInicioAttribute($value)
    {
        if ($value) {
            $dt = $value.' 00:00:00';
            $data = new DataHora($dt);
            $this->attributes['inicio'] = $data->dataInsert();
        }
    }

    //Acessor ->data_fim
    public function getTerminoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['termino']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_fim
    public function setTerminoAttribute($value)
    {
        if ($value) {
            $dt = $value.' 00:00:00';
            $data = new DataHora($dt);
            $this->attributes['termino'] = $data->dataInsert();
        }
    }

    public function Topico(){
        return $this->hasOne(AvaliacaoTopico::class, 'id', 'topico_id');
    }

}
