<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ParecerRh
 *
 * @property int $id
 * @property int|null $formulario_id
 * @property int $feedback_id
 * @property int $curriculo_id
 * @property bool|null $cnh
 * @property bool|null $ex_funcionario
 * @property string|null $cnh_tipo
 * @property string|null $rota_bairro
 * @property string|null $destro
 * @property string|null $tipo_entrevista
 * @property int|null $nota_digitacao
 * @property string|null $dinamicadegrupo
 * @property string|null $obs_dinamicadegrupo
 * @property bool|null $experiencia_callcenter
 * @property string|null $disponibilidade_horarios
 * @property bool|null $turnos_seis_por_um
 * @property string|null $horario_preferencial
 * @property string|null $obs_call
 * @property string|null $obs_horario
 * @property int|null $calca
 * @property int|null $bota
 * @property int|null $camisa_protecao
 * @property string|null $camisa_meia
 * @property string|null $mora_com_quem
 * @property bool|null $casado
 * @property string|null $tempodeconvivencia
 * @property bool|null $filhos
 * @property string|null $qnt_filhos
 * @property bool|null $conjuge_trabalha
 * @property string|null $trabalho_conjuge
 * @property bool|null $religioso
 * @property string|null $religiao_praticante
 * @property bool|null $fuma
 * @property string|null $frequencia_fuma
 * @property bool|null $bebe
 * @property string|null $frequencia_bebe
 * @property bool|null $indicacao
 * @property string|null $indicado_por
 * @property bool|null $alumar_experiencia
 * @property string|null $alumar_experiencia_area
 * @property bool $outra_industria_experiencia
 * @property string|null $outra_industria_nome
 * @property string|null $grau_instrucao
 * @property bool|null $horaextra
 * @property bool|null $turnos_seis_por_dois
 * @property bool|null $noturno
 * @property bool|null $acidente_trabalho
 * @property string|null $acidente_trabalho_qual
 * @property bool|null $afastamento_inss
 * @property string|null $afastamento_inss_qual
 * @property string|null $situacao_saude
 * @property string|null $nr_dez
 * @property string|null $comportamento_seguro
 * @property string|null $energia_para_trabalho
 * @property string|null $postura
 * @property string|null $historico_profissional
 * @property string|null $historico_educacional
 * @property string|null $objetivos_expectativas
 * @property string|null $auto_imagem
 * @property int|null $competencias
 * @property int|null $comportamento_etico
 * @property int|null $comprometimento
 * @property int|null $comunicacao
 * @property int|null $cultura_qualidade
 * @property int|null $foco_cliente
 * @property int|null $iniciativa
 * @property int|null $orientacao_resultados
 * @property int|null $trabalho_equipe
 * @property string|null $parecer_final
 * @property string|null $parecer_final_um
 * @property int $nota
 * @property string|null $comentarios
 * @property int $entrevistador
 * @property string|null $quem_entrevistou
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CursoFormacaoRH[] $CursosFormacao
 * @property-read int|null $cursos_formacao_count
 * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
 * @property-read int|null $nr_count
 * @property-read \App\Models\User|null $QuemEntrevistou
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\EntrevistaRh|null $entrevistaRh
 * @property-read \App\Models\GestorRh|null $gestorRh
 * @property-read \App\Models\IndividualRh|null $individualRh
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh query()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAcidenteTrabalho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAcidenteTrabalhoQual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAfastamentoInss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAfastamentoInssQual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAlumarExperiencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAlumarExperienciaArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereAutoImagem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereBebe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereBota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCalca($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCamisaMeia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCamisaProtecao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCasado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCnh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCnhTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComentarios($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCompetencias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComportamentoEtico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComportamentoSeguro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComprometimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereComunicacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereConjugeTrabalha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCulturaQualidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereDestro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereDinamicadegrupo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereDisponibilidadeHorarios($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereEnergiaParaTrabalho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereEntrevistador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereExFuncionario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereExperienciaCallcenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFilhos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFocoCliente($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFrequenciaBebe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFrequenciaFuma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereFuma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereGrauInstrucao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereHistoricoEducacional($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereHistoricoProfissional($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereHoraextra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereHorarioPreferencial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereIndicacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereIndicadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereIniciativa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereMoraComQuem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereNotaDigitacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereNoturno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereNrDez($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereObjetivosExpectativas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereObsCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereObsDinamicadegrupo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereObsHorario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereOrientacaoResultados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereOutraIndustriaExperiencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereOutraIndustriaNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereParecerFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereParecerFinalUm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh wherePostura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereQntFilhos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereQuemEntrevistou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereReligiaoPraticante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereReligioso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereRotaBairro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereSituacaoSaude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTempodeconvivencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTipoEntrevista($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTrabalhoConjuge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTrabalhoEquipe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTurnosSeisPorDois($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereTurnosSeisPorUm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerRh whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read mixed $data_entrevista
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CertificadoNr[] $Nr
 */
class ParecerRh extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'parecer_rh';
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

    protected $table = 'parecer_rh';
    protected $fillable = [
        'destro',
        'ex_funcionario',
        'feedback_id',
        'formulario_id',
        'cnh',
        'cnh_tipo',
        'rota_bairro',
        'calca',
        'bota',
        'camisa_protecao',
        'camisa_meia',
        'mora_com_quem',
        'casado',
        'tempodeconvivencia',
        'filhos',
        'qnt_filhos',
        'conjuge_trabalha',
        'trabalho_conjuge',
        'religioso',
        'religiao_praticante',
        'fuma',
        'frequencia_fuma',
        'bebe',
        'frequencia_bebe',
        'indicacao',
        'indicado_por',
        'alumar_experiencia',
        'alumar_experiencia_area',
        'outra_industria_experiencia',
        'outra_industria_nome',
        'grau_instrucao',
        'horaextra',
        'turnos_seis_por_dois',
        'noturno',
        'acidente_trabalho',
        'acidente_trabalho_qual',
        'afastamento_inss',
        'afastamento_inss_qual',
        'situacao_saude',
        'nr_dez',
        'comportamento_seguro',
        'energia_para_trabalho',
        'postura',
        'historico_profissional',
        'historico_educacional',
        'objetivos_expectativas',
        'auto_imagem',
        'competencias',
        'comportamento_etico',
        'comprometimento',
        'comunicacao',
        'cultura_qualidade',
        'foco_cliente',
        'iniciativa',
        'orientacao_resultados',
        'trabalho_equipe',
        'parecer_final',
        'parecer_final_um',
        'nota',
        'comentarios',
        'entrevistador',
        'quem_entrevistou',
        'tipo_entrevista',

        'nota_digitacao',
        'dinamicadegrupo',
        'obs_dinamicadegrupo',
        'experiencia_callcenter',
        'disponibilidade_horarios',
        'turnos_seis_por_um',
        'horario_preferencial',
        'obs_call',
        'obs_horario'
    ];
    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'formulario_id' => 'int',
        'destro' => 'string',
        'ex_funcionario' => 'boolean',
        'cnh' => 'boolean',
        'cnh_tipo' => 'string',
        'rota_bairro' => 'string',
        'calca' => 'int',
        'bota' => 'int',
        'camisa_protecao' => 'int',
        'camisa_meia' => 'string',
        'mora_com_quem' => 'string',
        'casado' => 'boolean',
        'tempodeconvivencia' => 'string',
        'filhos' => 'boolean',
        'qnt_filhos' => 'string',
        'conjuge_trabalha' => 'boolean',
        'trabalho_conjuge' => 'string',
        'religioso' => 'boolean',
        'religiao_praticante' => 'string',
        'fuma' => 'boolean',
        'frequencia_fuma' => 'string',
        'bebe' => 'boolean',
        'frequencia_bebe' => 'string',
        'indicacao' => 'boolean',
        'indicado_por' => 'string',
        'alumar_experiencia' => 'boolean',
        'alumar_experiencia_area' => 'string',
        'outra_industria_experiencia' => 'boolean',
        'outra_industria_nome' => 'string',
        'grau_instrucao' => 'string',
        'horaextra' => 'boolean',
        'turnos_seis_por_dois' => 'boolean',
        'noturno' => 'boolean',
        'acidente_trabalho' => 'boolean',
        'acidente_trabalho_qual' => 'string',
        'afastamento_inss' => 'boolean',
        'afastamento_inss_qual' => 'string',
        'situacao_saude' => 'string',
        'nr_dez' => 'string',
        'comportamento_seguro' => 'string',
        'energia_para_trabalho' => 'string',
        'postura' => 'string',
        'historico_profissional' => 'string',
        'historico_educacional' => 'string',
        'objetivos_expectativas' => 'string',
        'auto_imagem' => 'string',
        'competencias' => 'int',
        'comportamento_etico' => 'int',
        'comprometimento' => 'int',
        'comunicacao' => 'int',
        'cultura_qualidade' => 'int',
        'foco_cliente' => 'int',
        'iniciativa' => 'int',
        'orientacao_resultados' => 'int',
        'trabalho_equipe' => 'int',
        'parecer_final' => 'string',
        'parecer_final_um' => 'string',
        'nota' => 'int',
        'comentarios' => 'string',
        'entrevistador' => 'int',
        'quem_entrevistou' => 'string',
        'tipo_entrevista' => 'string',

        'nota_digitacao' => 'int',
        'dinamicadegrupo' => 'string',
        'obs_dinamicadegrupo' => 'string',
        'experiencia_callcenter' => 'boolean',
        'disponibilidade_horarios' => 'string',
        'turnos_seis_por_um' => 'boolean',
        'horario_preferencial' => 'string',
        'obs_call' => 'string',
        'obs_horario' => 'string',
    ];

    public function setCalcaAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['calca'] = $value;
        } else {
            $this->attributes['calca'] = null;
        }
    }

    //Acessor ->datalido
    public function getCalcaAttribute($value)
    {
        return is_null($value) ? "" : $value;
    }

    public function setCamisaMeiaAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['camisa_meia'] = $value;
        } else {
            $this->attributes['camisa_meia'] = null;
        }
    }

    //Acessor ->datalido
    public function getCamisaMeiaAttribute($value)
    {
        return is_null($value) ? "" : $value;
    }

    public function setCamisaProtecaoAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['camisa_protecao'] = $value;
        } else {
            $this->attributes['camisa_protecao'] = null;
        }
    }

    //Acessor ->datalido
    public function getCamisaProtecaoAttribute($value)
    {
        return is_null($value) ? "" : $value;
    }

    //Acessor ->datalido
    public function getBotaAttribute($value)
    {
        return is_null($value) ? "" : $value;
    }

    public function setBotaAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['bota'] = $value;
        } else {
            $this->attributes['bota'] = null;
        }
    }

    // Indicado por alguém
    public function getIndicacaoAttribute($value)
    {
        return is_null($value) ? false : $value;
    }

    public function setIndicacaoAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['indicacao'] = $value;
        } else {
            $this->attributes['indicacao'] = false;
        }
    }

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

    public function QuemEntrevistou()
    {
        return $this->hasOne(User::class, 'id', 'entrevistador');
    }

    public function individualRh()
    {
        return $this->hasOne(IndividualRh::class, 'feedback_id', 'feedback_id');
    }

    public function gestorRh()
    {
        return $this->hasOne(GestorRh::class, 'feedback_id', 'feedback_id');
    }

    public function entrevistaRh()
    {
        return $this->hasOne(EntrevistaRh::class, 'feedback_id', 'feedback_id');
    }

    public function Nr()
    {
        return $this->hasMany(CertificadoNr::class, 'feedback_id', 'feedback_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->entrevistador = auth()->id();
        });

        static::updating(function ($model) {
            $model->entrevistador = auth()->id();
        });


    }
}
