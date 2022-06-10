<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Admissao
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $curriculo_id
 * @property int|null $formulario_id
 * @property string|null $contrato
 * @property string|null $funcao
 * @property float|null $salario
 * @property string|null $status
 * @property string|null $documento
 * @property string|null $documento_portaria
 * @property string|null $tipo_admissao
 * @property string|null $tipo_treinamento
 * @property string|null $treinamento
 * @property mixed|null $data_treinamento
 * @property string|null $carteira_treinamento
 * @property string|null $nr_trinta_tres
 * @property mixed|null $data_nr_trinta_tres
 * @property string|null $nr_trinta_cinco
 * @property mixed|null $data_nr_trinta_cinco
 * @property string|null $trinta_dois_sessenta
 * @property mixed|null $data_trinta_dois_sessenta
 * @property string|null $numero_cracha
 * @property mixed|null $data_aso
 * @property bool|null $foto_escaneada
 * @property string|null $status_carteira_treinamento
 * @property mixed|null $data_admissao
 * @property mixed|null $data_desmobilizacao
 * @property string|null $avaliacao
 * @property string|null $obs_avaliacao
 * @property int|null $user_avaliacao
 * @property string|null $responsavel_feedback
 * @property mixed|null $data_avaliacao
 * @property int|null $area_etiqueta_id
 * @property bool|null $deu_baixa_epi
 * @property bool|null $cipa
 * @property array|null $alternativas
 * @property mixed|null $data_desmob
 * @property int|null $usuario_desmob
 * @property bool|null $pendencia
 * @property string|null $pendencias_quais
 * @property string|null $outros
 * @property string|null $preenchido_por_rh
 * @property string|null $preenchido_por_adm
 * @property string|null $preenchido_por_ssma
 * @property mixed|null $data_entrega_area
 * @property bool|null $biometria
 * @property mixed|null $data_biometria
 * @property int|null $usuario_id
 * @property int|null $editado_usuario_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexo
 * @property-read int|null $anexo_count
 * @property-read \App\Models\AreaEtiqueta|null $AreaEtiqueta
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\User|null $QuemAdmitiu
 * @property-read \App\Models\User|null $QuemAlterou
 * @property-read \App\Models\ResultadoIntegrado|null $ResultadoIntegrado
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $cargo
 * @property mixed $data_biometria_area
 * @property-read \App\Models\ParecerRh|null $parecerRh
 * @property-read \App\Models\ParecerRota|null $parecerRota
 * @property-read \App\Models\ParecerEntrevistaTecnica|null $parecerTecnica
 * @property-read \App\Models\ParecerTestePratico|null $parecerTeste
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereAlternativas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereAreaEtiquetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereAvaliacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereBiometria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCarteiraTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCipa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataAdmissao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataAso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataAvaliacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataBiometria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataDesmob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataDesmobilizacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataEntregaArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataNrTrintaCinco($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataNrTrintaTres($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataTrintaDoisSessenta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDeuBaixaEpi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDocumentoPortaria($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereEditadoUsuarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereFotoEscaneada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereFuncao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereNrTrintaCinco($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereNrTrintaTres($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereNumeroCracha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereObsAvaliacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereOutros($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePendencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePendenciasQuais($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePreenchidoPorAdm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePreenchidoPorRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePreenchidoPorSsma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereResponsavelFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereSalario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereStatusCarteiraTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereTipoAdmissao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereTipoTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereTrintaDoisSessenta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereUserAvaliacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereUsuarioDesmob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereUsuarioId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Treinamento|null $Carteira
 * @property-read \App\Models\ClassificacaoRescisaoCurriculo|null $ClassificacaoRescisao
 * @property-read \App\Models\EntrevistaDesligamento|null $EntrevistaDesligamento
 * @property-read \App\Models\MotivoRescisaoCurriculo|null $MotivoRescisao
 * @property-read \App\Models\TipoAvisoCurriculo|null $TipoAviso
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereCargo($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $FotoTres
 * @property-read int|null $foto_tres_count
 * @property string|null $pis
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePis($value)
 * @property-read \App\Models\DadosAdmissao|null $DadosAdmissoes
 * @property mixed $prazo_encerramento
 * @property mixed $prazo_experiencia
 * @property string|null $data_encerramento
 * @property string|null $data_adm_prevista
 * @property-read \App\Models\Demissao|null $Demissao
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataAdmPrevista($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao whereDataEncerramento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admissao wherePrazoExperiencia($value)
 */
class Admissao extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'admissao';
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

    protected $table = 'admissoes';


    protected $fillable = [
        'feedback_id',
        'formulario_id',
        'funcao',
        'cargo',
        'salario',
        'contrato',
        'ex_funcionario',
        'status',
        'documento',
        'documento_portaria',
        'tipo_admissao',
        'tipo_treinamento',
        'treinamento',
        'data_treinamento',
        'carteira_treinamento',
        'nr_trinta_tres',
        'data_nr_trinta_tres',
        'nr_trinta_cinco',
        'data_nr_trinta_cinco',
        'trinta_dois_sessenta',
        'data_trinta_dois_sessenta',
        'numero_cracha',
        'data_aso',
        'foto_escaneada',
        'status_carteira_treinamento',
        'usuario_id',
        'editado_usuario_id',
        'data_admissao',
        'data_adm_prevista',
        'data_desmobilizacao',
        'avaliacao',
        'obs_avaliacao',
        'user_avaliacao',
        'responsavel_feedback',
        'data_avaliacao',
        'area_etiqueta_id',
        'cipa',
        'deu_baixa_epi',
        'alternativas',
        'data_desmob',
        'usuario_desmob',
        'pendencia',
        'pendencias_quais',
        'outros',
        'preenchido_por_rh',
        'preenchido_por_adm',
        'preenchido_por_ssma',
        'data_entrega_area',
        'biometria',
        'data_biometria',
        'pis',
        'prazo_experiencia',
        'data_encerramento'
    ];
    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'formulario_id' => 'int',
        'salario' => 'float',
        'contrato' => 'string',
        'funcao' => 'string',
        'cargo' => 'string',
        'status' => 'string',
        'documento' => 'string',
        'documento_portaria' => 'string',
        'tipo_admissao' => 'string',
        'tipo_treinamento' => 'string',
        'treinamento' => 'string',
        'data_treinamento' => 'string',
        'carteira_treinamento' => 'string',
        'nr_trinta_tres' => 'string',
        'data_nr_trinta_tres' => 'string',
        'nr_trinta_cinco' => 'string',
        'data_nr_trinta_cinco' => 'string',
        'trinta_dois_sessenta' => 'string',
        'data_trinta_dois_sessenta' => 'string',
        'numero_cracha' => 'string',
        'data_aso' => 'string',
        'foto_escaneada' => 'boolean',
        'status_carteira_treinamento' => 'string',
        'usuario_id' => 'int',
        'editado_usuario_id' => 'int',
        'data_admissao' => 'string',
        'data_adm_prevista' => 'string',
        'data_desmobilizacao' => 'string',
        'avaliacao' => 'string',
        'obs_avaliacao' => 'string',
        'user_avaliacao' => 'int',
        'responsavel_feedback' => 'string',
        'data_avaliacao' => 'string',
        'area_etiqueta_id' => 'int',
        'cipa' => 'boolean',
        'deu_baixa_epi' => 'boolean',
        'alternativas' => 'array',
        'data_desmob' => 'string',
        'usuario_desmob' => 'int',
        'pendencia' => 'boolean',
        'pendencias_quais' => 'string',
        'outros' => 'string',
        'preenchido_por_rh' => 'string',
        'preenchido_por_adm' => 'string',
        'preenchido_por_ssma' => 'string',
        'data_entrega_area' => 'string',
        'biometria' => 'boolean',
        'data_biometria' => 'string',
        'pis' => 'string',
        'prazo_experiencia' => 'string',
        'data_encerramento' => 'string',
    ];

    const STATUS_ADMISSAO_AGUARDANDOQUALIFICACAO = "AGUARDANDO QUALIFICAÇÃO";
    const STATUS_ADMISSAO_PRONTOPARAADMISSAO = "PRONTO PARA ADMISSÃO";
    const STATUS_ADMISSAO_ADMITIDO = "ADMITIDO";
    const STATUS_ADMISSAO_STANDBY = "STAND BY";
    const STATUS_ADMISSAO_PENDENTEASO = "PENDENTE ASO";
    const STATUS_ADMISSAO_ASO_NO_AMBULATORIO = "ASO NO AMBULATÓRIO";
    const STATUS_ADMISSAO_PENDENTEDOCUMENTO = "PENDENTE DOCUMENTO";
    const STATUS_ADMISSAO_PENDENTETREINAMENTO = "PENDENTE TREINAMENTO";
    const STATUS_ADMISSAO_CANCELADO = "CANCELADO";
    const STATUS_ADMISSAO_ENCAMINHADOEXAME = "ENCAMINHADO EXAME";
    const STATUS_ADMISSAO_DESISTENCIA = "DESISTÊNCIA";


    const STATUS_CARTEIRA_TREINAMENTO_PENDENTE = "PENDENTE";
    const STATUS_CARTEIRA_TREINAMENTO_AGUARDANDOTREINAMENTO = "AGUARDANDO TREINAMENTO";
    const STATUS_CARTEIRA_TREINAMENTO_ENTREGUE = "ENTREGUE";

    const TIPO_ADMISSAO_TEMPORARIO = 'TEMPORARIO';
    const TIPO_ADMISSAO_INTERMITENTE = 'INTERMITENTE';
    const TIPO_ADMISSAO_DETERMINADO = 'DETERMINADO';
    const TIPO_ADMISSAO_FIXO = 'FIXO';

    const TODOS_TIPOS_ADMISSAO = [
        self::TIPO_ADMISSAO_TEMPORARIO,
        self::TIPO_ADMISSAO_INTERMITENTE,
        self::TIPO_ADMISSAO_DETERMINADO,
        self::TIPO_ADMISSAO_FIXO
    ];

    const TODOS_STATUS_ADMISSAO = [
        self::STATUS_ADMISSAO_AGUARDANDOQUALIFICACAO,
        self::STATUS_ADMISSAO_PRONTOPARAADMISSAO,
        self::STATUS_ADMISSAO_ADMITIDO,
        self::STATUS_ADMISSAO_STANDBY,
        self::STATUS_ADMISSAO_PENDENTEASO,
        self::STATUS_ADMISSAO_ASO_NO_AMBULATORIO,
        self::STATUS_ADMISSAO_PENDENTEDOCUMENTO,
        self::STATUS_ADMISSAO_PENDENTETREINAMENTO,
        self::STATUS_ADMISSAO_CANCELADO,
        self::STATUS_ADMISSAO_ENCAMINHADOEXAME,
        self::STATUS_ADMISSAO_DESISTENCIA
    ];

    const STATUS_EM_PROCESSO_SELECAO = [
        self::STATUS_ADMISSAO_AGUARDANDOQUALIFICACAO,
        self::STATUS_ADMISSAO_PRONTOPARAADMISSAO,
        self::STATUS_ADMISSAO_STANDBY,
        self::STATUS_ADMISSAO_PENDENTEASO,
        self::STATUS_ADMISSAO_PENDENTEDOCUMENTO,
        self::STATUS_ADMISSAO_PENDENTETREINAMENTO,
        self::STATUS_ADMISSAO_ENCAMINHADOEXAME
    ];

    const TODOS_STATUS_CARTEIRA_TREINAMETO = [
        self::STATUS_CARTEIRA_TREINAMENTO_PENDENTE,
        self::STATUS_CARTEIRA_TREINAMENTO_AGUARDANDOTREINAMENTO,
        self::STATUS_CARTEIRA_TREINAMENTO_ENTREGUE
    ];

    const PRAZO_NENHUM = 'Nenhum';
    const TRINTA_MAIS_TRINTA = '30+30';
    const QUARENTAECINCO_MAIS_QUARENTAECINCO = '45+45';
    const TRINTA_MAIS_SESSENTA = '30+60';
    const SESSENTA_MAIS_TRINTA = '60+30';

    const TODOS_PRAZOS = [
        self::PRAZO_NENHUM,
        self::TRINTA_MAIS_TRINTA,
        self::QUARENTAECINCO_MAIS_QUARENTAECINCO,
        self::TRINTA_MAIS_SESSENTA,
        self::SESSENTA_MAIS_TRINTA,
    ];

    const DOC_PENDENTE = "PENDENTE";
    const DOC_INCOMPLETO = "INCOMPLETO";
    const DOC_CONCLUIDO = "CONCLUIDO";

    const TODOS_STATUS_DOCUMENTOS = [
        self::DOC_PENDENTE,
        self::DOC_INCOMPLETO,
        self::DOC_CONCLUIDO
    ];

    const TODOS_STATUS_DOCUMENTOS_PORTARIA = [
        self::DOC_PENDENTE,
        self::DOC_CONCLUIDO
    ];

    const STATUS_TREINAMENTO_AGENDAR = "AGENDAR";
    const STATUS_TREINAMENTO_NAO_SE_APLICA = "NÃO SE APLICA";
    const STATUS_TREINAMENTO_REALIZADO = "REALIZADO";

    const TODOS_STATUS_TREINAMENTOS = [
        self::STATUS_TREINAMENTO_AGENDAR,
        self::STATUS_TREINAMENTO_NAO_SE_APLICA,
        self::STATUS_TREINAMENTO_REALIZADO
    ];

    public function pExperiencia()
    {
        switch ($this->attributes['prazo_experiencia']) {
            case self::PRAZO_NENHUM:
                return false;
            case self::TRINTA_MAIS_TRINTA:
                return [30, 30];
            case self::QUARENTAECINCO_MAIS_QUARENTAECINCO:
                return [45, 45];
            case self::TRINTA_MAIS_SESSENTA:
                return [30, 60];
            case self::SESSENTA_MAIS_TRINTA:
                return [60, 30];
        }
    }

    public function getCipaAttribute($value)
    {
        return is_null($value) ? "" : (boolean)$value;
    }

    public function getPendenciaAttribute($value)
    {
        return is_null($value) ? "" : (boolean)$value;
    }

    public function getDeuBaixaEpiAttribute($value)
    {
        return is_null($value) ? "" : (boolean)$value;
    }

    public function getBiometriaAttribute($value)
    {
        return is_null($value) ? "" : (boolean)$value;
    }

    //Acessor ->data_adm_prevista
    public function getDataAdmPrevistaAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_adm_prevista']);
            return $data->dataCompleta();
        }
        return null;
    }

    //Acessor ->data_adm_prevista
    public function setDataAdmPrevistaAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_adm_prevista'] = $data->dataInsert();
        } else {
            $this->attributes['data_adm_prevista'] = null;
        }
    }

    public function getDataEntregaAreaAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_entrega_area']);
            return $data->dataCompleta();
        }
        return null;
    }

    public function setDataEntregaAreaAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_entrega_area'] = $data->dataInsert();
        } else {
            $this->attributes['data_entrega_area'] = null;
        }
    }

    public function getDataEncerramentoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_encerramento']);
            return $data->dataCompleta();
        }
        return null;
    }

    public function setDataEncerramentoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_encerramento'] = $data->dataInsert();
        } else {
            $this->attributes['data_encerramento'] = null;
        }
    }

    //Acessor ->data_nr_trinta_tres
    public function getDataBiometriaAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_biometria']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_nr_trinta_tres
    public function setDataBiometriaAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_biometria'] = $data->dataInsert();
        } else {
            $this->attributes['data_biometria'] = null;
        }
    }

    //Acessor ->data_nr_trinta_tres
    public function getDataDesmobilizacaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_desmobilizacao']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_nr_trinta_tres
    public function setDataDesmobilizacaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_desmobilizacao'] = $data->dataInsert();
        } else {
            $this->attributes['data_desmobilizacao'] = null;
        }
    }

    //Acessor ->data_nr_trinta_tres
    public function getDataDesmobAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_desmob']);
            return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto();
        }
    }

    //Acessor ->data_nr_trinta_tres
    public function setDataDesmobAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_desmob'] = $data->dataHoraInsert();
        } else {
            $this->attributes['data_desmob'] = null;
        }
    }


    //Acessor ->data_nr_trinta_tres
    public function getDataAvaliacaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_avaliacao']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_nr_trinta_tres
    public function setDataAvaliacaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_avaliacao'] = $data->dataHoraInsert();
        } else {
            $this->attributes['data_avaliacao'] = null;
        }
    }


    //Acessor ->data_admissao
    public function getFuncaoAttribute($value)
    {
        if ($value) {
            return mb_strtoupper($this->attributes['funcao']);
        }
    }

    //Acessor ->data_admissao
    public function getCargoAttribute($value)
    {
        if ($value) {
            return mb_strtoupper($this->attributes['cargo']);
        }
    }

    //Acessor ->data_admissao
    public function getDataAdmissaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_admissao']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_admissao
    public function setDataAdmissaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_admissao'] = $data->dataInsert();
        } else {
            $this->attributes['data_admissao'] = null;
        }
    }


    //Acessor ->data_treinamento
    public function getDataTreinamentoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_treinamento']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_treinamento
    public function setDataTreinamentoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_treinamento'] = $data->dataInsert();
        }
    }

    //Acessor ->data_nr_trinta_tres
    public function getDataNrTrintaTresAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_nr_trinta_tres']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_nr_trinta_tres
    public function setDataNrTrintaTresAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_nr_trinta_tres'] = $data->dataInsert();
        }
    }

    //Acessor ->data_nr_trinta_cinco
    public function getDataNrTrintaCincoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_nr_trinta_cinco']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_nr_trinta_cinco
    public function setDataNrTrintaCincoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_nr_trinta_cinco'] = $data->dataInsert();
        }
    }

    //Acessor ->data_trinta_dois_sessenta
    public function getDataTrintaDoisSessentaAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_trinta_dois_sessenta']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_trinta_dois_sessenta
    public function setDataTrintaDoisSessentaAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_trinta_dois_sessenta'] = $data->dataInsert();
        }
    }

    //Modificador ->salario
    public function setSalarioAttribute($value)
    {
        if ($value) {
            $this->attributes['salario'] = Sistema::DinheiroInsert($value);
        }
    }

    public function getSalarioAttribute($value)
    {
        if ($value) {
            return number_format($value, 2, ',', '.');
        }
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }

    public function ResultadoIntegrado()
    {
        return $this->hasOne(ResultadoIntegrado::class, 'feedback_id', 'feedback_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function parecerRh()
    {
        return $this->hasOne(ParecerRh::class, 'feedback_id', 'feedback_id');
    }

    public function parecerTecnica()
    {
        return $this->hasOne(ParecerEntrevistaTecnica::class, 'feedback_id', 'feedback_id');
    }

    public function parecerRota()
    {
        return $this->hasOne(ParecerRota::class, 'feedback_id', 'feedback_id');
    }

    public function parecerTeste()
    {
        return $this->hasOne(ParecerTestePratico::class, 'feedback_id', 'feedback_id');
    }

    public function Anexo()
    {
        return $this->belongsToMany(Arquivo::class, 'foto_admissaos', 'curriculo_id', 'arquivo_id');
    }

    public function QuemAdmitiu()
    {
        return $this->hasOne(User::class, 'id', 'usuario_id');
    }

    public function QuemAlterou()
    {
        return $this->hasOne(User::class, 'id', 'editado_usuario_id');
    }

    public function FotoTres()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->withPivot(['tipo'])->whereTipo('foto3x4');
    }

    public function Carteira()
    {
        return $this->hasOne(Treinamento::class, 'feedback_id', 'feedback_id');
    }

    public function AreaEtiqueta()
    {
        return $this->hasOne(AreaEtiqueta::class, 'id', 'area_etiqueta_id');
    }

    public function DadosAdmissoes()
    {
        return $this->hasOne(DadosAdmissao::class, 'admissao_id', 'id');
    }

    public function Demissao()
    {
        return $this->hasOne(Demissao::class, 'feedback_id', 'feedback_id');
    }

    public function scopeAdmitidos($query)
    {
        return $query->whereDoesntHave('Demissao');
    }
//    public function ChecklistDemissao()
//    {
//        return $this->hasOne(ChecklistDemissao::class, 'curriculo_id', 'curriculo_id');
//    }

    /*

    public function TipoAviso()
    {
        return $this->belongsToMany(TipoAviso::class, 'tipo_aviso_curriculo', 'curriculo_id', 'tipo_aviso_id');
    }

    public function ClassificacaoRescisao()
    {
        return $this->belongsToMany(ClassificacaoRescisao::class, 'classificacao_rescisao_curriculo', 'curriculo_id', 'classificacao_id')
            ->withPivot(['quem_classificou']);
    }*/


    public static function getNumeroSupervisor($cliente_id, $area_etiqueta_id)
    {
        return ClienteAreaEtiqueta::whereClienteId($cliente_id)->whereAreaEtiquetaId($area_etiqueta_id)->first()->numero_supervisor;
    }

    public function UltimoAsoAtivo(){
        return $this->hasOne(AdmissaoAso::class, 'admissao_id', 'id')->whereAtivo(true);
    }
}
