<?php

namespace App\Models;

use App\Models\User;
use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AtaReuniao
 *
 * @property int $id
 * @property int $quem_cadastrou Usuario da sessão
 * @property string $local
 * @property string $data_inicio
 * @property string $data_fim
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @property int|null $area_etiqueta_id
 * @property int|null $centro_custo_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AtaReuniaoAcao> $Acoes
 * @property-read int|null $acoes_count
 * @property-read \App\Models\AreaEtiqueta|null $Area
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AtaReuniaoAssunto> $Assuntos
 * @property-read int|null $assuntos_count
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AtaReuniaoParticipante> $Participantes
 * @property-read int|null $participantes_count
 * @property-read User|null $QuemCadastrou
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AtaReuniaoTipo> $Tipos
 * @property-read int|null $tipos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao query()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao vinculados()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereAreaEtiquetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereLocal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereQuemCadastrou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniao whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AtaReuniao extends Model
{
    use LogsActivity;
    use TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'atareuniao';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }


    protected $fillable = [
        'id',
        'quem_cadastrou',
        'local',
        'data_inicio',
        'data_fim',
        'empresa_id',
        'area_etiqueta_id',
        'centro_custo_id',
    ];

    protected $casts = [
        'quem_cadastrou' => 'int',
        'local' => 'string',
        'data_inicio' => 'string',
        'data_fim' => 'string',
        'empresa_id' => 'int',
        'area_etiqueta_id' => 'int',
        'centro_custo_id' => 'int',
    ];

    /**
     * Scope a query para mostrar apenas cihs vinculados ao user autenticado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVinculados($query)
    {
        return $query->where('quem_cadastrou', auth()->user()->id);
    }

    //Acessor ->data_inicio
    public function getDataInicioAttribute($value)
    {
        $data = new DataHora($this->attributes['data_inicio']);
        return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
    }

    //Acessor ->data_fim
    public function getDataFimAttribute($value)
    {
        $data = new DataHora($this->attributes['data_fim']);
        return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
    }

//    //Modificador ->data_inicio
//    public function setDataInicioAttribute($value)
//    {
//        if ($value) {
//            $this->attributes['data_inicio'] = (new DataHora())->dataHoraInsert();
//        }
//    }
//
//    //Modificador ->data_fim
//    public function setDataFimAttribute($value)
//    {
//        if ($value) {
//            $this->attributes['data_fim'] = (new DataHora())->dataHoraInsert();
//        }
//    }

    public function QuemCadastrou()
    {
        return $this->hasOne(User::class, 'id', 'quem_cadastrou');
    }

    public function Assuntos()
    {
        return $this->hasMany(AtaReuniaoAssunto::class, 'ata_reuniao_id', 'id');
    }

    public function Tipos()
    {
        return $this->hasMany(AtaReuniaoTipo::class, 'ata_reuniao_id', 'id');
    }

    public function Acoes()
    {
        return $this->hasMany(AtaReuniaoAcao::class, 'ata_reuniao_id', 'id');
    }

    public function Participantes()
    {
        return $this->hasMany(AtaReuniaoParticipante::class, 'ata_reuniao_id', 'id');
    }

    public function Area(){
        return $this->hasOne(AreaEtiqueta::class, 'id', 'area_etiqueta_id');
    }

    public function CentroCusto(){
        return $this->hasOne(CentroCusto::class, 'id', 'centro_custo_id');
    }

}
