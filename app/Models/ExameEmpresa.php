<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExameEmpresa
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $razao_social
 * @property string|null $cnpj
 * @property string|null $cep
 * @property string|null $logradouro
 * @property string|null $numero
 * @property string|null $complemento
 * @property string|null $bairro
 * @property string|null $municipio
 * @property string|null $uf
 * @property string|null $telefone
 * @property string|null $email
 * @property int $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereBairro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereCep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereCnpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereComplemento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereLogradouro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereMunicipio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereRazaoSocial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereTelefone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereUf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class ExameEmpresa extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'ExameEmpresa';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

}
