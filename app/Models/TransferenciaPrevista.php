<?php

namespace App\Models;

use App\Models\User;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use MasterTag\DataHora;

/**
 * App\Models\TransferenciaPrevista
 *
 * @property int $id
 * @property int|null $colaborador_id
 * @property int $centro_custo_origem_id
 * @property int $centro_custo_destino_id
 * @property mixed $data_transferencia
 * @property int|null $user_id
 * @property string|null $solicitante
 * @property string|null $obs
 * @property int|null $user_aprovacao_id
 * @property mixed|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property int|null $empresa_id
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property int|null $gestor_id
 * @property-read \App\Models\CentroCusto|null $CentroCustoDestino
 * @property-read \App\Models\CentroCusto|null $CentroCustoOrigem
 * @property-read \App\Models\Curriculo|null $Colaborador
 * @property-read User|null $GestorAprovacao
 * @property-read User|null $QuemAprovou
 * @property-read User|null $UserCadastrou
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereCentroCustoDestinoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereCentroCustoOrigemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereDataTransferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereUserId($value)
 * @mixin \Eloquent
 * @property-read User|null $UserAprovacao
 * @property int|null $user_rh_id
 * @property string|null $resposta_rh
 * @property string|null $obs_rh
 * @property string|null $data_aprovacao_rh
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereRespostaRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferenciaPrevista whereUserRhId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 */
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

    public function UserAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'user_aprovacao_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'transferencia_previstas_anexos', 'transferencia_prevista_id', 'arquivo_id');
    }
}
