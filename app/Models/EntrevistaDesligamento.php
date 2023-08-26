<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\EntrevistaDesligamento
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property string|null $superior_imediato
 * @property string|null $motivo
 * @property string|null $trabalharia_novamente
 * @property string|null $contr_melhoria
 * @property string|null $relacao_interpessoal
 * @property string|null $recursos_fisicos
 * @property string|null $valores_normas
 * @property string|null $planejamento
 * @property string|null $sob_superior_imediato
 * @property string|null $direcao_empresa
 * @property string|null $oportunidades
 * @property string|null $salario_beneficio
 * @property string|null $atividade
 * @property string|null $comentarios
 * @property string|null $parecer_entrevistador
 * @property bool $pode_voltar
 * @property string|null $porque_pode_voltar
 * @property string $quem_entrevistou
 * @property int $user_entrevista
 * @property string|null $data_entrevista
 * @property string|null $preenchido_por
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $formulario_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereAtividade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereComentarios($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereContrMelhoria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereDataEntrevista($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereDirecaoEmpresa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereOportunidades($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereParecerEntrevistador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento wherePlanejamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento wherePodeVoltar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento wherePorquePodeVoltar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento wherePreenchidoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereQuemEntrevistou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereRecursosFisicos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereRelacaoInterpessoal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereSalarioBeneficio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereSobSuperiorImediato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereSuperiorImediato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereTrabalhariaNovamente($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereUserEntrevista($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EntrevistaDesligamento whereValoresNormas($value)
 * @mixin \Eloquent
 */
class EntrevistaDesligamento extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'entrevista_desligamento';
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

    protected $table = 'entrevista_desligamentos';
    protected $fillable = [
        'feedback_id',
        'superior_imediato',
        'motivo',
        'trabalharia_novamente',
        'contr_melhoria',
        'relacao_interpessoal',
        'recursos_fisicos',
        'valores_normas',
        'planejamento',
        'sob_superior_imediato',
        'direcao_empresa',
        'oportunidades',
        'salario_beneficio',
        'atividade',
        'comentarios',
        'parecer_entrevistador',
        'pode_voltar',
        'porque_pode_voltar',
        'quem_entrevistou',
        'user_entrevista',
        'data_entrevista',
        'preenchido_por',
    ];
    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'superior_imediato' => 'string',
        'motivo' => 'string',
        'trabalharia_novamente' => 'string',
        'contr_melhoria' => 'string',
        'relacao_interpessoal' => 'string',
        'recursos_fisicos' => 'string',
        'valores_normas' => 'string',
        'planejamento' => 'string',
        'sob_superior_imediato' => 'string',
        'direcao_empresa' => 'string',
        'oportunidades' => 'string',
        'salario_beneficio' => 'string',
        'atividade' => 'string',
        'comentarios' => 'string',
        'parecer_entrevistador' => 'string',
        'pode_voltar' => 'boolean',
        'porque_pode_voltar' => 'string',
        'quem_entrevistou' => 'string',
        'user_entrevista' => 'int',
        'data_entrevista' => 'string',
        'preenchido_por' => 'string',
    ];
}
