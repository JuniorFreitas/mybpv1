<?php

namespace App\Models;

use App\Models\User;
use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

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
    use LogsActivity, HasActivitylogOptions, SoftDeletes, TenantTrait;

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_EM_ELABORACAO = 'em_elaboracao';
    public const STATUS_AGUARDANDO_REVISAO = 'aguardando_revisao';
    public const STATUS_AGUARDANDO_APROVACAO = 'aguardando_aprovacao';
    public const STATUS_AJUSTES_SOLICITADOS = 'ajustes_solicitados';
    public const STATUS_APROVADA = 'aprovada';
    public const STATUS_PUBLICADA = 'publicada';
    public const STATUS_ENCERRADA = 'encerrada';
    public const STATUS_CANCELADA = 'cancelada';


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
        'codigo',
        'uuid_publico',
        'quem_cadastrou',
        'titulo',
        'objetivo',
        'status',
        'nivel_acesso',
        'classificacao_confidencialidade',
        'organizador_id',
        'redator_id',
        'aprovacao_modo',
        'versao_atual',
        'timezone',
        'link_videoconferencia',
        'observacoes',
        'local',
        'data_inicio',
        'data_fim',
        'aprovada_em',
        'publicada_em',
        'bloqueada_em',
        'cancelada_em',
        'empresa_id',
        'area_etiqueta_id',
        'centro_custo_id',
    ];

    protected $casts = [
        'codigo' => 'string',
        'uuid_publico' => 'string',
        'quem_cadastrou' => 'int',
        'titulo' => 'string',
        'objetivo' => 'string',
        'status' => 'string',
        'nivel_acesso' => 'string',
        'classificacao_confidencialidade' => 'string',
        'organizador_id' => 'int',
        'redator_id' => 'int',
        'aprovacao_modo' => 'string',
        'versao_atual' => 'string',
        'timezone' => 'string',
        'link_videoconferencia' => 'string',
        'observacoes' => 'string',
        'local' => 'string',
        'data_inicio' => 'string',
        'data_fim' => 'string',
        'aprovada_em' => 'datetime',
        'publicada_em' => 'datetime',
        'bloqueada_em' => 'datetime',
        'cancelada_em' => 'datetime',
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

    public function Organizador()
    {
        return $this->hasOne(User::class, 'id', 'organizador_id');
    }

    public function Redator()
    {
        return $this->hasOne(User::class, 'id', 'redator_id');
    }

    public function Acessos()
    {
        return $this->hasMany(AtaReuniaoAcesso::class, 'ata_reuniao_id', 'id');
    }

    public function Aprovacoes()
    {
        return $this->hasMany(AtaReuniaoAprovacao::class, 'ata_reuniao_id', 'id');
    }

    public function Versoes()
    {
        return $this->hasMany(AtaReuniaoVersao::class, 'ata_reuniao_id', 'id');
    }

    public function Comentarios()
    {
        return $this->hasMany(AtaReuniaoComentario::class, 'ata_reuniao_id', 'id');
    }

    public function Anexos()
    {
        return $this->hasMany(AtaReuniaoAnexo::class, 'ata_reuniao_id', 'id');
    }

    public function Ciencias()
    {
        return $this->hasMany(AtaReuniaoCiencia::class, 'ata_reuniao_id', 'id');
    }

}
