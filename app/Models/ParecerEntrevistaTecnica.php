<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ParecerEntrevistaTecnica
 *
 * @property int $id
 * @property int $feedback_id
 * @property int|null $formulario_id
 * @property int $curriculo_id
 * @property string|null $tempo_funcao
 * @property bool|null $trabalhou_alumar
 * @property bool|null $indicado
 * @property string|null $indicado_por
 * @property bool|null $rota
 * @property bool|null $ssma
 * @property string|null $ssma_ex
 * @property bool|null $roupa_pvc
 * @property string|null $roupa_pvc_ex
 * @property bool|null $roupa_pvc_dificuldade
 * @property bool|null $turno
 * @property bool|null $trabalhou_mecanico_manutencao
 * @property string|null $trabalhou_mecanico_manutencao_ex
 * @property bool|null $trabalhou_raquete_produto_quimico
 * @property string|null $trabalhou_raquete_produto_quimico_ex
 * @property string|null $tipos_de_talha
 * @property bool|null $fechamento_flange
 * @property string|null $fechamento_flange_ex
 * @property string|null $milimetros_polegada
 * @property bool|null $manuseio_macarico
 * @property string|null $manuseio_macarico_ex
 * @property bool|null $trocou_valvulas
 * @property string|null $trocou_valvulas_ex
 * @property string|null $ferramentas_elevacao_carga
 * @property bool|null $opera_plat_movel
 * @property string|null $opera_plat_movel_ex
 * @property bool|null $opera_plat_ponte
 * @property string|null $opera_plat_onte_ex
 * @property bool|null $experiencia_cargas_rigger
 * @property string|null $experiencia_cargas_rigger_ex
 * @property bool|null $trabalhou_overhaul
 * @property string|null $trabalhou_overhaul_ex
 * @property bool|null $abertura_tubo_seis_polegada
 * @property bool|null $vareta_seis_polegada
 * @property bool|null $filete_acabemento
 * @property string|null $observacao
 * @property string|null $indicado_area
 * @property string|null $resultado_final
 * @property int $nota
 * @property int|null $entrevistado_por
 * @property string|null $quem_entrevistou
 * @property string|null $tipo_contratacao
 * @property string|null $texto_livre
 * @property string|null $tipo_entrevista
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
 * @property-read \App\Models\User|null $QuemEntrevistou
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica query()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereAberturaTuboSeisPolegada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereEntrevistadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereExperienciaCargasRigger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereExperienciaCargasRiggerEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFechamentoFlange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFechamentoFlangeEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFerramentasElevacaoCarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFileteAcabemento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereIndicado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereIndicadoArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereIndicadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereManuseioMacarico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereManuseioMacaricoEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereMilimetrosPolegada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereOperaPlatMovel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereOperaPlatMovelEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereOperaPlatOnteEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereOperaPlatPonte($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereQuemEntrevistou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereResultadoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereRota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereRoupaPvc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereRoupaPvcDificuldade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereRoupaPvcEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereSsma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereSsmaEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTempoFuncao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTextoLivre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTipoContratacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTipoEntrevista($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTiposDeTalha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouAlumar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouMecanicoManutencao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouMecanicoManutencaoEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouOverhaul($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouOverhaulEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouRaqueteProdutoQuimico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrabalhouRaqueteProdutoQuimicoEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrocouValvulas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTrocouValvulasEx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereTurno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerEntrevistaTecnica whereVaretaSeisPolegada($value)
 * @mixin \Eloquent
 * @property-read mixed $data_entrevista
 */
class ParecerEntrevistaTecnica extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'parecer_entrevista_tecnica';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'parecer_entrevista_tecnica';
    protected $fillable = [
        'feedback_id',
        'formulario_id',
        "curriculo_id",
        "tempo_funcao",
        "trabalhou_alumar",
        "indicado",
        "indicado_por",
        "rota",
        "ssma",
        "ssma_ex",
        "roupa_pvc",
        "roupa_pvc_ex",
        "roupa_pvc_dificuldade",
        "turno",
        "trabalhou_mecanico_manutencao",
        "trabalhou_mecanico_manutencao_ex",
        "trabalhou_raquete_produto_quimico",
        "trabalhou_raquete_produto_quimico_ex",
        "tipos_de_talha",
        "fechamento_flange",
        "fechamento_flange_ex",
        "milimetros_polegada",
        "manuseio_macarico",
        "manuseio_macarico_ex",
        "trocou_valvulas",
        "trocou_valvulas_ex",
        "ferramentas_elevacao_carga",
        "opera_plat_movel",
        "opera_plat_movel_ex",
        "opera_plat_ponte",
        "opera_plat_onte_ex",
        "experiencia_cargas_rigger",
        "experiencia_cargas_rigger_ex",
        "trabalhou_overhaul",
        "trabalhou_overhaul_ex",
        "abertura_tubo_seis_polegada",
        "vareta_seis_polegada",
        "filete_acabemento",
        "observacao",
        "indicado_area",
        "resultado_final",
        "nota",
        "entrevistado_por",
        "quem_entrevistou",
        'tipo_contratacao',
        'tipo_entrevista',
        'texto_livre',
    ];
    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'formulario_id' => 'int',
        'curriculo_id' => 'int',
        'tempo_funcao' => 'string',
        'trabalhou_alumar' => 'boolean',
        'indicado' => 'boolean',
        'indicado_por' => 'string',
        'rota' => 'boolean',
        'ssma' => 'boolean',
        'ssma_ex' => 'string',
        'roupa_pvc' => 'boolean',
        'roupa_pvc_ex' => 'string',
        'roupa_pvc_dificuldade' => 'boolean',
        'turno' => 'boolean',
        'trabalhou_mecanico_manutencao' => 'boolean',
        'trabalhou_mecanico_manutencao_ex' => 'string',
        'trabalhou_raquete_produto_quimico' => 'boolean',
        'trabalhou_raquete_produto_quimico_ex' => 'string',
        'tipos_de_talha' => 'string',
        'fechamento_flange' => 'boolean',
        'fechamento_flange_ex' => 'string',
        'milimetros_polegada' => 'string',
        'manuseio_macarico' => 'boolean',
        'manuseio_macarico_ex' => 'string',
        'trocou_valvulas' => 'boolean',
        'trocou_valvulas_ex' => 'string',
        'ferramentas_elevacao_carga' => 'string',
        'opera_plat_movel' => 'boolean',
        'opera_plat_movel_ex' => 'string',
        'opera_plat_ponte' => 'boolean',
        'opera_plat_onte_ex' => 'string',
        'experiencia_cargas_rigger' => 'boolean',
        'experiencia_cargas_rigger_ex' => 'string',
        'trabalhou_overhaul' => 'boolean',
        'trabalhou_overhaul_ex' => 'string',
        'abertura_tubo_seis_polegada' => 'boolean',
        'vareta_seis_polegada' => 'boolean',
        'filete_acabemento' => 'boolean',
        'observacao' => 'string',
        'indicado_area' => 'string',
        'resultado_final' => 'string',
        'nota' => 'int',
        'entrevistado_por' => 'int',
        'quem_entrevistou' => 'string',
        'tipo_contratacao' => 'string',
        'tipo_entrevista' => 'string',
        'texto_livre' => 'string',
//        'created_at' => 'date:d/m/Y H:i',
//        'updated_at' => 'date:d/m/Y H:i'
    ];

    //Acessor ->datalido
    public function getDataEntrevistaAttribute($value)
    {
        $data = new DataHora($this->attributes['created_at']);
        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto() . 'h';
    }

    public function FeedbackCurriculo()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }

    public function QuemEntrevistou()
    {
        return $this->hasOne(User::class, 'id', 'entrevistado_por');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->entrevistado_por = auth()->id();
        });

        static::updating(function ($model) {
            $model->entrevistado_por = auth()->id();
        });
    }
}
