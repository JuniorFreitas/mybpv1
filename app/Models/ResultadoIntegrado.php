<?php

namespace App\Models;

use App\Classes\ZapNotificacao;
use App\Jobs\Entrevista\JobEnvioDocumento;
use App\Jobs\Entrevista\ResultadoIntegrado\JobEncaminhamentoExame;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ResultadoIntegrado
 *
 * @property int $id
 * @property int $feedback_id
 * @property int|null $formulario_id
 * @property int $curriculo_id
 * @property bool|null $documentos_entregue
 * @property mixed|null $documentos_entregue_data
 * @property bool|null $encaminhado_exame
 * @property mixed|null $encaminhado_exame_data
 * @property bool|null $encaminhado_treinamento
 * @property mixed|null $encaminhado_treinamento_data
 * @property bool|null $excessao
 * @property string|null $autorizado_por
 * @property int $usuario_id
 * @property string $responsavel_envio
 * @property string|null $obs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admissao|null $Admissao
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexo
 * @property-read int|null $anexo_count
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\ParecerRh|null $parecerRh
 * @property-read \App\Models\ParecerRota|null $parecerRota
 * @property-read \App\Models\ParecerEntrevistaTecnica|null $parecerTecnica
 * @property-read \App\Models\ParecerTestePratico|null $parecerTeste
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado query()
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereAutorizadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereDocumentosEntregue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereDocumentosEntregueData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEncaminhadoExame($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEncaminhadoExameData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEncaminhadoTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEncaminhadoTreinamentoData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereExcessao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereResponsavelEnvio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereUsuarioId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\CertificadoAlumar|null $Certificado
 * @property-read \App\Models\Treinamento|null $Treinamento
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $FotoTres
 * @property-read int|null $foto_tres_count
 * @property int|null $pcmso_id
 * @property int|null $empresa_exame_id
 * @property-read \App\Models\Pcmso|null $Pcmso
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado whereEmpresaExameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResultadoIntegrado wherePcmsoId($value)
 */
class ResultadoIntegrado extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'resultado_integrado';
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
        'feedback_id',
        'formulario_id',
        'curriculo_id',
        "documentos_entregue",
        "documentos_entregue_data",
        "encaminhado_exame",
        "encaminhado_exame_data",
        "pcmso_id",
        "empresa_exame_id",
        "encaminhado_treinamento",
        "encaminhado_treinamento_data",
        "excessao",
        "autorizado_por",
        "usuario_id",
        "responsavel_envio",
        "obs",
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'formulario_id' => 'int',
        'curriculo_id' => 'int',
        "documentos_entregue" => 'boolean',
        "documentos_entregue_data" => 'string',
        "encaminhado_exame" => 'boolean',
        "encaminhado_exame_data" => 'string',
        "pcmso_id" => 'int',
        "empresa_exame_id" => 'int',
        "encaminhado_treinamento" => 'boolean',
        "encaminhado_treinamento_data" => 'string',
        "excessao" => 'boolean',
        "autorizado_por" => 'string',
        "usuario_id" => 'int',
        "responsavel_envio" => 'string',
        "obs" => 'string',
    ];

    //Acessor ->documentos_entregue_data
//    public function getDocumentosEntregueDataAttribute($value)
//    {
//        $data = new DataHora($this->attributes['documentos_entregue_data']);
//        return $data->dataCompleta();
//    }

    public function getExcessaoAttribute($value)
    {
        return is_null($value) ? "" : (bool)$this->attributes['excessao'];
    }

    //Modificador ->documentos_entregue_data
    public function setDocumentosEntregueDataAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['documentos_entregue_data'] = $data->dataInsert();
        } else {
            $this->attributes['documentos_entregue_data'] = null;
        }
    }

    //Modificador ->encaminhado_exame_data
    public function setEncaminhadoExameDataAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['encaminhado_exame_data'] = $data->dataInsert();
        } else {
            $this->attributes['encaminhado_exame_data'] = null;
        }
    }

    //Modificador ->encaminhado_treinamento_data
    public function setEncaminhadoTreinamentoDataAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['encaminhado_treinamento_data'] = $data->dataInsert();
        } else {
            $this->attributes['encaminhado_treinamento_data'] = null;
        }
    }

    //Modificador ->documentos_entregue_data
    public function getDocumentosEntregueDataAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['documentos_entregue_data']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->encaminhado_exame_data
    public function getEncaminhadoExameDataAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['encaminhado_exame_data']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->encaminhado_exame_data
    public function getEncaminhadoTreinamentoDataAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['encaminhado_treinamento_data']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->created_at
    public function getCreatedAtAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['created_at']);
            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto() . 'h';
        }
    }

    public function Pcmso()
    {
        return $this->belongsTo(Pcmso::class, 'pcmso_id');
    }

    public function Admissao()
    {
        return $this->hasOne(Admissao::class, 'feedback_id', 'feedback_id');
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
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

    public function Treinamento()
    {
        return $this->hasOne(Treinamento::class, 'feedback_id', 'feedback_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'usuario_id');
    }

    public function Anexo()
    {
        return $this->belongsToMany(Arquivo::class, 'foto_admissaos', 'feedback_id', 'arquivo_id');
    }

    public function FotoTres()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->withPivot(['tipo'])->whereTipo('foto3x4');
    }

    public function Certificado()
    {
        return $this->hasOne(CertificadoAlumar::class, 'feedback_id', 'feedback_id');
    }

    public static function Notificacao(FeedbackCurriculo $feedback, User $user, $dados, EmpresaExame $empresaExame, $tipo_pcmso){
        if ($dados['documentos_entregue']) {
            if ($user->EmpresaConfiguracoes->envia_whatsapp) {
                if ($feedback->TelPrincipal->tipo == 'whatsapp') {
                    if ($dados['envia_whatsapp_documentos']) {
                        $mensagem = "Prezado(a) sr(a) *{$feedback->Curriculo->nome}*, Tudo bem?\n\n👏🏽 Parabéns por chegado até esta etapa! Você foi aprovado na etapa de entrevista e seleção e agora vamos para a etapa de documentos para admissão.\n\n" .
                            "Para continuidade no processo, segue o link abaixo para que seja anexado os documentos conforme descrição.\n\n" .
                            env('APP_URL') . "/documentos\n\n" .
                            "Destaca-se que é muito importante que todos os documentos sejam anexados corretamente e sem omissões para que não haja atraso na etapa de documentação, necessária para a continuidade de sua admissão.\n\n" .
                            "Atenciosamente,\n\n" .
                            "Equipe " . $user->Empresa->razao_social . "\n\n" .
                            "_Esta mensagem foi enviada automaticamente pela plataforma *MyBP*, por favor não responda._";
                        (new ZapNotificacao())->enviar([
                            'enviado_id' => $feedback->curriculo_id,
                            'telefone' => $feedback->TelPrincipal->sonumero,
                            'mensagem' => $mensagem
                        ]);
                    }
                }
            }
            if ($dados['envia_email_documentos']) {
                JobEnvioDocumento::dispatch([
                    'nome' => $feedback->Curriculo->nome,
                    'email' => $feedback->Curriculo->email,
                    'empresa_id' => $feedback->empresa_id,
                ]);
            }
        }

        if ($dados['encaminhado_exame']) {
            if ($user->EmpresaConfiguracoes->envia_whatsapp) {
                if ($feedback->TelPrincipal->tipo == 'whatsapp') {
                    if ($dados['envia_whatsapp_exame']) {
                        $mensagem = "Prezado(a) sr(a) *{$feedback->Curriculo->nome}*, Tudo bem?\n\nEstamos encaminhando para realização de *Exame de ordem admissional*, " .
                            "no primeiro dia útil após recebimento dessa notificação (considerar de segunda à sábado).\n\n" .
                            "🏥 Local do Exame: \n*{$empresaExame->nome}*.\n" .
                            "📍 Endereço: *{$empresaExame->dados['endereco']['endereco_completo']}*\n" .
                            "📞 Contato: *{$empresaExame->dados['telefone']}*" .
                            "\n\n" .
                            "Atenciosamente,\n\n" .
                            "Equipe " . $user->Empresa->razao_social . "\n\n" .
                            "_Esta mensagem foi enviada automaticamente pela plataforma *MyBP*, por favor não responda._";

                        (new ZapNotificacao())->enviar([
                            'enviado_id' => $feedback->curriculo_id,
                            'telefone' => $feedback->TelPrincipal->sonumero,
                            'mensagem' => $mensagem
                        ]);
                    }
                }
            }
            if ($dados['envia_email_exame']) {
                JobEncaminhamentoExame::dispatch([
                    'colaborador' => $feedback->Curriculo,
                    'cargo' => $feedback->VagaAberta->Vaga->nome,
                    'clinica' => $empresaExame,
                    'tipo_pcmso' => $tipo_pcmso,
                    'empresa_id' => $feedback->empresa_id,
                ]);
            }
        }
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->usuario_id = auth()->id();
        });

        static::updating(function ($model) {
            $model->usuario_id = auth()->id();
        });
    }
}
