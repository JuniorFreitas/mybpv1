<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;


/**
 * App\Models\FeedbackCurriculo
 *
 * @property int $curriculo_id
 * @property int $id
 * @property string|null $selecionado
 * @property int|null $vaga_id
 * @property int|null $usuario_entrevista_marcado
 * @property int|null $cliente_id
 * @property bool|null $contato_realizado
 * @property bool|null $interesse
 * @property string|null $data_entrevista
 * @property string|null $local_entrevista
 * @property int|null $telefone_id
 * @property string|null $obs
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $status
 * @property bool|null $envia_mail_provas
 * @property string|null $data_envia_mail_provas
 * @property int|null $user_envia_mail_provas
 * @property bool|null $envia_mail_proxima_etapa
 * @property string|null $data_envia_mail_proxima_etapa
 * @property int|null $user_envia_mail_proxima_etapa
 * @property bool|null $envia_mail_desclassificacao
 * @property string|null $data_envia_mail_desclassificacao
 * @property int|null $user_envia_mail_desclassificacao
 * @property bool|null $envia_whatsapp
 * @property string|null $data_envia_whatsapp
 * @property int|null $user_envia_whatsapp
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $AcordoHora
 * @property-read int|null $acordo_hora_count
 * @property-read \App\Models\Admissao|null $Admissao
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ArquivamentoDossie
 * @property-read int|null $arquivamento_dossie_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ArquivamentoEletronico
 * @property-read int|null $arquivamento_eletronico_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $AvisoFerias
 * @property-read int|null $aviso_ferias_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $BookRescisao
 * @property-read int|null $book_rescisao_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $CartoesPonto
 * @property-read int|null $cartoes_ponto_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $CertificadoTreinSeg
 * @property-read int|null $certificado_trein_seg_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CertificadoNr[] $CertificadosNr
 * @property-read int|null $certificados_nr_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ChaveFgts
 * @property-read int|null $chave_fgts_count
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ComprovanteDevCtp
 * @property-read int|null $comprovante_dev_ctp_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ComprovanteDevolucaoCtps
 * @property-read int|null $comprovante_devolucao_ctps_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ComprovantePagamento
 * @property-read int|null $comprovante_pagamento_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ContraChequeMensais
 * @property-read int|null $contra_cheque_mensais_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ContratoTrabalhoAssinado
 * @property-read int|null $contrato_trabalho_assinado_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ControleAsos
 * @property-read int|null $controle_asos_count
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CursoFormacaoRH[] $CursosFormacoes
 * @property-read int|null $cursos_formacoes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $DeclaracaoDependentesImposto
 * @property-read int|null $declaracao_dependentes_imposto_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $DocChecklist
 * @property-read int|null $doc_checklist_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $DocSelecao
 * @property-read int|null $doc_selecao_count
 * @property-read \App\Models\EntrevistaDesligamento|null $EntrevistaDesligamento
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Etapas[] $EtapaStatus
 * @property-read int|null $etapa_status_count
 * @property-read \App\Models\ExameTreinamento|null $Exame
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ExameDemissional
 * @property-read int|null $exame_demissional_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $FichaEntregaEpi
 * @property-read int|null $ficha_entrega_epi_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $FichaRegistrada
 * @property-read int|null $ficha_registrada_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $GuiaSeguroDesemprego
 * @property-read int|null $guia_seguro_desemprego_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MedidaAdministrativa[] $MedidasAdministrativas
 * @property-read int|null $medidas_administrativas_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $NadaConstaFichaEpi
 * @property-read int|null $nada_consta_ficha_epi_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $OrdemServicoAssinada
 * @property-read int|null $ordem_servico_assinada_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $PppAssinado
 * @property-read int|null $ppp_assinado_count
 * @property-read \App\Models\User|null $QuemMarcou
 * @property-read \App\Models\ResultadoIntegrado|null $ResultadoIntegrado
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $SalarioFamiliaAssinado
 * @property-read int|null $salario_familia_assinado_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SimuladoCandidato[] $Simulados
 * @property-read int|null $simulados_count
 * @property-read \App\Models\TelefoneCurriculo|null $TelPrincipal
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $TermoConfiabilidade
 * @property-read int|null $termo_confiabilidade_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $TermoRescisao
 * @property-read int|null $termo_rescisao_count
 * @property-read \App\Models\Treinamento|null $Treinamento
 * @property-read \App\Models\Vaga|null $VagaSelecionada
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ValeTransporteAssinado
 * @property-read int|null $vale_transporte_assinado_count
 * @property-read \App\Models\Vinculo|null $Vinculo
 * @property-read \App\Models\NotificacaoWhats|null $WhatsAppNotificacao
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\EntrevistaRh|null $entrevistaRh
 * @property-read \App\Models\GestorRh|null $gestorRh
 * @property mixed $datalido
 * @property-read \App\Models\IndividualRh|null $individualRh
 * @property-read \App\Models\ParecerRh|null $parecerRh
 * @property-read \App\Models\ParecerRota|null $parecerRota
 * @property-read \App\Models\ParecerEntrevistaTecnica|null $parecerTecnica
 * @property-read \App\Models\ParecerTestePratico|null $parecerTeste
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereContatoRealizado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEntrevista($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEnviaMailDesclassificacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEnviaMailProvas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEnviaMailProximaEtapa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereDataEnviaWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEnviaMailDesclassificacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEnviaMailProvas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEnviaMailProximaEtapa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEnviaWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereInteresse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereLocalEntrevista($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereSelecionado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereTelefoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUserEnviaMailDesclassificacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUserEnviaMailProvas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUserEnviaMailProximaEtapa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUserEnviaWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereUsuarioEntrevistaMarcado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereVagaId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\ClassificacaoRescisaoCurriculo|null $ClassificacaoRescisao
 * @property-read \App\Models\MotivoRescisaoCurriculo|null $MotivoRescisao
 * @property-read \App\Models\TipoAvisoCurriculo|null $TipoAviso
 * @property int|null $empresa_id
 * @property int|null $vagas_abertas_id
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackCurriculo whereVagasAbertasId($value)
 */
class FeedbackCurriculo extends Model
{
    use HasFactory, LogsActivity;
    use TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'Feedback';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName)
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    public function usesTimestamps()
    {
        return false;
    }

    protected $fillable = [
        'selecionado',
        'vaga_id',
        'usuario_entrevista_marcado',
        'cliente_id',
        'contato_realizado',
        'interesse',
        'data_entrevista',
        'local_entrevista',
        'telefone_id',
        'obs',
        'status',
        'envia_mail_provas',
        'data_envia_mail_provas',
        'user_envia_mail_provas',
        'envia_mail_proxima_etapa',
        'data_envia_mail_proxima_etapa',
        'user_envia_mail_proxima_etapa',
        'envia_mail_desclassificacao',
        'data_envia_mail_desclassificacao',
        'user_envia_mail_desclassificacao',
        'envia_whatsapp',
        'data_envia_whatsapp',
        'user_envia_whatsapp',
        'vagas_abertas_id',
        'empresa_id',
    ];
    protected $casts = [
        'id' => 'int',
        'selecionado' => 'string',
        'vaga_id' => 'int',
        'usuario_entrevista_marcado' => 'int',
        'cliente_id' => 'int',
        'contato_realizado' => 'boolean',
        'interesse' => 'boolean',
        'data_entrevista' => 'string',
        'local_entrevista' => 'string',
        'telefone_id' => 'int',
        'obs' => 'string',
        'status' => 'string',
        'envia_mail_provas' => 'boolean',
        'data_envia_mail_provas' => 'string',
        'user_envia_mail_provas' => 'int',
        'envia_mail_proxima_etapa' => 'boolean',
        'data_envia_mail_proxima_etapa' => 'string',
        'user_envia_mail_proxima_etapa' => 'int',
        'envia_mail_desclassificacao' => 'boolean',
        'data_envia_mail_desclassificacao' => 'string',
        'user_envia_mail_desclassificacao' => 'int',
        'envia_whatsapp' => 'boolean',
        'data_envia_whatsapp' => 'string',
        'user_envia_whatsapp' => 'int',
        'vagas_abertas_id' => 'int',
        'empresa_id' => 'int',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    //Modificador ->datalido
    public function setDatalidoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['datalido'] = $data->dataHoraInsert();
    }

    //Acessor ->datalido
    public function getDatalidoAttribute($value)
    {
        $data = new DataHora($this->attributes['datalido']);
        return $data->dataCompleta();
    }

    //Modificador ->updated_at
    public function setUpdatedAtAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['updated_at'] = (new DataHora())->dataHoraInsert();
        } else {
            $this->attributes['updated_at'] = null;
        }
    }

    //Acessor ->updated_at
    public function getUpdatedAtAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['updated_at']);
            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
        } else {
            $this->attributes['updated_at'] = null;
        }
    }

    //Modificador ->entrevista
    public function setDataEntrevistaAttribute($value)
    {
        if (!is_null($value)) {
            $newTime = explode(' às ', $value);
            $newDH = $newTime[0] . ' ' . $newTime[1];
            $data = new DataHora($newDH);
            $this->attributes['data_entrevista'] = $data->dataHoraInsert();
        } else {
            $this->attributes['data_entrevista'] = null;
        }
    }

    //Acessor ->entrevista
    public function getDataEntrevistaAttribute($value)
    {
        $data = new DataHora($this->attributes['data_entrevista']);
        return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
    }


    //Modificador ->data_envia_mail_provas
    public function setDataEnviaMailProvasAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['data_envia_mail_provas'] = (new DataHora())->dataHoraInsert();
        } else {
            $this->attributes['data_envia_mail_provas'] = null;
        }
    }

    //Acessor ->data_envia_mail_provas
    public function getDataEnviaMailProvasAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_envia_mail_provas']);
            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
        } else {
            $this->attributes['data_envia_mail_provas'] = null;
        }
    }

    //Modificador ->data_envia_mail_proxima_etapa
    public function setDataEnviaMailProximaEtapaAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['data_envia_mail_proxima_etapa'] = (new DataHora())->dataHoraInsert();
        } else {
            $this->attributes['data_envia_mail_proxima_etapa'] = null;
        }
    }

    //Acessor ->data_envia_mail_desclassificacao
    public function getDataEnviaMailProximaEtapaAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_envia_mail_proxima_etapa']);
            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
        } else {
            $this->attributes['data_envia_mail_proxima_etapa'] = null;
        }
    }

    //Modificador ->data_envia_mail_desclassificacao
    public function setDataEnviaMailDesclassificacaoAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['data_envia_mail_desclassificacao'] = (new DataHora())->dataHoraInsert();
        } else {
            $this->attributes['data_envia_mail_desclassificacao'] = null;
        }
    }

    //Acessor ->data_envia_mail_proxima_etapa
    public function getDataEnviaMailDesclassificacaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_envia_mail_desclassificacao']);
            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
        } else {
            $this->attributes['data_envia_mail_desclassificacao'] = null;
        }
    }

    //Modificador ->data_envia_whatsapp
    public function setDataEnviaWhatsappAttribute($value)
    {
        if (!is_null($value)) {
            $this->attributes['data_envia_whatsapp'] = (new DataHora())->dataHoraInsert();
        } else {
            $this->attributes['data_envia_whatsapp'] = null;
        }
    }

    //Acessor ->data_envia_whatsapp
    public function getDataEnviaWhatsappAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_envia_whatsapp']);
            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
        } else {
            $this->attributes['data_envia_whatsapp'] = null;
        }
    }

    //-----------------------Models

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }

    public function VagaSelecionada()
    {
        return $this->hasOne(Vaga::class, 'id', 'vaga_id');
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function QuemMarcou()
    {
        return $this->hasOne(User::class, 'id', 'usuario_entrevista_marcado');
    }

    public function TelPrincipal()
    {
        return $this->hasOne(TelefoneCurriculo::class, 'id', 'telefone_id');
    }

    public function parecerRh()
    {
        return $this->hasOne(ParecerRh::class, 'feedback_id', 'id');
    }

    public function parecerTecnica()
    {
        return $this->hasOne(ParecerEntrevistaTecnica::class, 'feedback_id', 'id');
    }

    public function parecerRota()
    {
        return $this->hasOne(ParecerRota::class, 'feedback_id', 'id');
    }

    public function parecerTeste()
    {
        return $this->hasOne(ParecerTestePratico::class, 'feedback_id', 'id');
    }

    public function individualRh()
    {
        return $this->hasOne(IndividualRh::class, 'feedback_id', 'id');
    }

    public function gestorRh()
    {
        return $this->hasOne(GestorRh::class, 'feedback_id', 'id');
    }

    public function entrevistaRh()
    {
        return $this->hasOne(EntrevistaRh::class, 'feedback_id', 'id');
    }

    public function ResultadoIntegrado()
    {
        return $this->hasOne(ResultadoIntegrado::class, 'feedback_id', 'id');
    }

    public function Exame()
    {
        return $this->hasOne(ExameTreinamento::class, 'feedback_id', 'id');
    }

    public function Simulados()
    {
        return $this->hasMany(SimuladoCandidato::class, 'feedback_id', 'id');
    }

    public function EtapaStatus()
    {
        return $this->hasMany(Etapas::class, 'feedback_id', 'id')->orderByDesc('id');
    }

    public function Vinculo()
    {
        return $this->hasOne(Vinculo::class, 'feedback_id', 'id');
    }

    public function CursosFormacoes()
    {
        return $this->hasMany(CursoFormacaoRH::class, 'feedback_id', 'id');
    }

    public function CertificadosNr()
    {
        return $this->hasMany(CertificadoNr::class, 'feedback_id', 'id');
    }

    public function WhatsAppNotificacao()
    {
        return $this->hasOne(NotificacaoWhats::class, 'feedback_id', 'id');
    }

    public function MedidasAdministrativas()
    {
        return $this->hasMany(MedidaAdministrativa::class, 'feedback_id', 'id');
    }

    public function Admissao()
    {
        return $this->hasOne(Admissao::class, 'feedback_id', 'id');
    }

    public function MotivoRescisao()
    {
        return $this->hasOne(MotivoRescisaoCurriculo::class, 'feedback_id', 'id');
    }

    public function TipoAviso()
    {
        return $this->hasOne(TipoAvisoCurriculo::class, 'feedback_id', 'id');
    }

    public function ClassificacaoRescisao()
    {
        return $this->hasOne(ClassificacaoRescisaoCurriculo::class, 'feedback_id', 'id');
    }

    public function EntrevistaDesligamento()
    {
        return $this->hasOne(EntrevistaDesligamento::class, 'feedback_id', 'id');
    }


    public function Treinamento()
    {
        return $this->hasOne(Treinamento::class, 'feedback_id', 'id');
    }

    public function BancoConta()
    {
        return $this->hasOne(UsuarioConta::class, 'user_id','curriculo_id');
    }

    public function DocSelecao()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('DocSelecao');
    }

    public function DocChecklist()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('DocChecklist');
    }

    public function FichaRegistrada()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('FichaRegistrada');
    }

    public function ContratoTrabalhoAssinado()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ContratoTrabalhoAssinado');
    }

    public function TermoConfiabilidade()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('TermoConfiabilidade');
    }

    public function ValeTransporteAssinado()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ValeTransporteAssinado');
    }

    public function AcordoHora()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('AcordoHora');
    }

    public function SalarioFamiliaAssinado()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('SalarioFamiliaAssinado');
    }

    public function DeclaracaoDependentesImposto()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('DeclaracaoDependentesImposto');
    }

    public function ComprovanteDevCtp()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ComprovanteDevCtp');
    }

    public function OrdemServicoAssinada()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('OrdemServicoAssinada');
    }

    public function CertificadoTreinSeg()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('CertificadoTreinSeg');
    }

    public function FichaEntregaEpi()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('FichaEntregaEpi');
    }

    public function ContraChequeMensais()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ContraChequeMensais');
    }

    public function CartoesPonto()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('CartoesPonto');
    }

    public function AvisoFerias()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('AvisoFerias');
    }

    public function ControleAsos()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ControleAsos');
    }

    public function BookRescisao()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('BookRescisao');
    }

    public function TermoRescisao()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('TermoRescisao');
    }

    public function GuiaSeguroDesemprego()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('GuiaSeguroDesemprego');
    }

    public function ChaveFgts()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ChaveFgts');
    }

    public function ComprovantePagamento()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ComprovantePagamento');
    }

    public function ExameDemissional()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ExameDemissional');
    }

    public function NadaConstaFichaEpi()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('NadaConstaFichaEpi');
    }

    public function ComprovanteDevolucaoCtps()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ComprovanteDevolucaoCtps');
    }

    public function PppAssinado()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('PppAssinado');
    }

    public function ArquivamentoEletronico()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ArquivamentoEletronico');
    }

    public function ArquivamentoDossie()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ArquivamentoDossie');
    }


    /**/
    //scopeManual
//    public function scopeEmpresa($query)
//    {
//        if (auth()->user()->cliente_id !== User::BPSE) {
//            return $query->where('cliente_id', auth()->user()->cliente_id);
//        }
//    }

    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->usuario_entrevista_marcado = auth()->id();
        });

        static::updating(function ($model) {
            $model->usuario_entrevista_marcado = auth()->id();
        });

//        static::addGlobalScope(new ScopeClientesEmpresa);
    }
}
