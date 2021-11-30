<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use DateTimeInterface;
use MasterTag\DataHora;

class TransferenciaPrevista extends Model
{

    use TenantTrait, HasFactory;

    protected $fillable = [
        'colaborador_id',
        'centro_custo_origem_id',
        'centro_custo_destino_id',
        'data_transferencia',
        'user_id',
        'solicitante',
        'obs',
        'user_aprovacao_id',
        'data_aprovacao',
        'obs_aprovacao',
        'status_aprovacao',
        'empresa_id',
        'gestor_id',
    ];

    protected $casts = [
        'id' => 'int',
        'colaborador_id' => 'int',
        'centro_custo_origem_id' => 'int',
        'centro_custo_destino_id' => 'int',
        'data_transferencia' => 'date:d/m/Y',
        'user_id' => 'int',
        'solicitante' => 'string',
        'obs' => 'string',
        'user_aprovacao_id' => 'int',
        'obs_aprovacao' => 'string',
        'data_aprovacao' => 'date:d/m/Y',
        'status_aprovacao' => 'string',
        'empresa_id' => 'int',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
        'gestor_id' => 'int'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function setDataTransferenciaAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_transferencia'] = $data->dataInsert();
        } else {
            $this->attributes['data_transferencia'] = null;
        }
    }

    public function Colaborador()
    {
        return $this->hasOne(Curriculo::class, 'id', 'colaborador_id');
    }

    public function CentroCustoOrigem()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'centro_custo_origem_id');
    }

    public function CentroCustoDestino()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'centro_custo_destino_id');
    }

    public function UserCadastrou()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function QuemAprovou()
    {
        return $this->hasOne(User::class, 'id', 'user_aprovacao_id');
    }

    public function GestorAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }
}
