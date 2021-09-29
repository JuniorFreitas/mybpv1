<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\TreinamentoSgi
 *
 * @property int $id
 * @property string $nome
 * @property string $titulo_certificado
 * @property string|null $conteudo_abordado
 * @property string|null $conteudo_programatico
 * @property int $carga_horaria
 * @property int|null $validade
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi query()
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereCargaHoraria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereConteudoAbordado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereConteudoProgramatico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereTituloCertificado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoSgi whereValidade($value)
 * @mixin \Eloquent
 */
class TreinamentoSgi extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'TreinamentoSgi';
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

    protected $table = 'treinamento_sgi';

    protected $fillable = [
        'nome',
        'titulo_certificado',
        'conteudo_abordado',
        'conteudo_programatico',
        'carga_horaria',
        'validade',
        'empresa_id'
    ];

    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'titulo_certificado' => 'string',
        'conteudo_abordado' => 'string',
        'conteudo_programatico' => 'string',
        'carga_horaria' => 'int',
        'validade' => 'int',
        'empresa_id' => 'int'
    ];


    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::addGlobalScope(new ScopeEmpresa);
    }
}
