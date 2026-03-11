<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EmpresaTreinamento
 *
 * @property int $id
 * @property string $nome
 * @property string $endereco
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereEndereco($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaTreinamento whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class EmpresaTreinamento extends Model
{
    use HasFactory, TenantTrait, LogsActivity, HasActivitylogOptions;



    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }


    protected $table = 'empresa_treinamentos';
    protected $fillable = [
        'id',
        'nome',
        'endereco',
        'ativo',
        'empresa_id'
    ];

    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'endereco' => 'string',
        'ativo' => 'boolean',
        'empresa_id' => 'int'
    ];

}
