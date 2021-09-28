<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Ocorrencia
 *
 * @property int $id
 * @property int $setor_id
 * @property string $assunto
 * @property int $quem_criou
 * @property int $quem_atualizou
 * @property mixed|null $datahora_finalizou
 * @property int|null $quem_finalizou
 * @property string $status
 * @property string $tipo
 * @property int $imovel_id
 * @property int|null $contrato_id
 * @property mixed|null $created_at
 * @property mixed|null $updated_at
 * @property-read \App\User $Atualizou
 * @property-read \App\User $Criou
 * @property-read \App\User $Finalizou
 * @property-read mixed $status_novo
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereAssunto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereContratoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereDatahoraFinalizou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereImovelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereQuemAtualizou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereQuemCriou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereQuemFinalizou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereSetorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read mixed $de_contrato
 * @property-read mixed $de_imovel
 * @property-read mixed $status_andamento
 * @property-read mixed $status_finalizado
 * @property-read mixed $status_text
 * @property-read mixed $tipo_anotacao
 * @property-read mixed $tipo_documentacao
 * @property-read mixed $tipo_problema
 * @property-read mixed $tipo_text
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia deContrato()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ocorrencia deImovel()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RespostaOcorrencia[] $Respostas
 * @property-read \App\Models\OcorrenciaSetor $Setor
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $Tags
 * @property-read int|null $respostas_count
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property int|null $cliente_id
 * @property int|null $usuario_id
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $Usuario
 * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ocorrencia whereUsuarioId($value)
 */
class Ocorrencia extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'ocorrencias';
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

    protected $table = 'ocorrencias';
    protected $fillable = [
        'id',
        'cliente_id',
        'usuario_id',
        'setor_id',
        'assunto',
        'quem_criou',
        'quem_atualizou',
        'datahora_finalizou',
        'quem_finalizou',
        'status',
        'tipo',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'id' => 'int',
        'setor_id' => 'int',
        'cliente_id' => 'int',
        'usuario_id' => 'int',
        'assunto' => 'string',
        'quem_criou' => 'int',
        'quem_atualizou' => 'int',
        'datahora_finalizou' => 'datetime:d/m/Y à\s H:i',
        'quem_finalizou' => 'int',
        'status' => 'string',
        'tipo' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i',
        'updated_at' => 'datetime:d/m/Y à\s H:i',
    ];

    const STATUS_NOVO = 'novo';
    const STATUS_ANDAMENTO = 'andamento';
    const STATUS_FINALIZADO = 'finalizado';

    const TIPO_ANOTACAO = 'anotacao';
    const TIPO_PROBLEMA = 'problema';
    const TIPO_DOCUMENTACAO = "documentacao";

    const SETOR_RESCISAO = 1;
    const SETOR_CONTRATO = 4;
    const SETOR_CORRETORES = 6;

    //Relacionamentos----------------------------------------------------------------------

    public function Criou()
    {
        return $this->hasOne(User::class, 'id', 'quem_criou');
    }

    public function Atualizou()
    {
        return $this->hasOne(User::class, 'id', 'quem_atualizou');
    }

    public function Finalizou()
    {
        return $this->hasOne(User::class, 'id', 'quem_finalizou');
    }

    public function Respostas()
    {
        return $this->hasMany(RespostaOcorrencia::class, 'ocorrencia_id', 'id');
    }

    public function Setor()
    {
        return $this->hasOne(OcorrenciaSetor::class, 'id', 'setor_id');
    }

    public function Tags()
    {
        return $this->belongsToMany(Tag::class, 'ocorrencias_tags', 'ocorrencia_id', 'tag_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'usuario_id');
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    //Mutators----------------------------------------------------------------------
    public function getStatusNovoAttribute()
    {
        return $this->status == self::STATUS_NOVO ? true : false;
    }

    public function getStatusAndamentoAttribute()
    {
        return $this->status == self::STATUS_ANDAMENTO ? true : false;
    }

    public function getStatusFinalizadoAttribute()
    {
        return $this->status == self::STATUS_FINALIZADO ? true : false;
    }

    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::STATUS_NOVO:
                return "Novo";
                break;

            case self::STATUS_ANDAMENTO:
                return "Em andamento";
                break;

            case self::STATUS_FINALIZADO:
                return "Finalizado";
                break;
        }
    }

    public function getTipoAnotacaoAttribute()
    {
        return $this->tipo == self::TIPO_ANOTACAO ? true : false;
    }

    public function getTipoProblemaAttribute()
    {
        return $this->tipo == self::TIPO_PROBLEMA ? true : false;
    }

    public function getTipoDocumentacaoAttribute()
    {
        return $this->tipo == self::TIPO_DOCUMENTACAO ? true : false;
    }

    public function getTipoTextAttribute()
    {
        switch ($this->tipo) {
            case self::TIPO_ANOTACAO:
                return "Anotação";
                break;

            case self::TIPO_DOCUMENTACAO:
                return "Documentação";
                break;

            case self::TIPO_PROBLEMA:
                return "Problema";
                break;
        }
    }

    public static function Criar($ID_SETOR, $tipo, $assunto, $ID_IMOVEL, $ID_CONTRATO = NULL)
    {

        $ocorrencia = new Ocorrencia();
        $ocorrencia->setor_id = $ID_SETOR;
        $ocorrencia->assunto = $assunto;
        $ocorrencia->quem_criou = auth()->id();
        $ocorrencia->quem_atualizou = auth()->id();

        switch ($tipo) {
            case self::TIPO_ANOTACAO:
                $ocorrencia->status = self::STATUS_FINALIZADO;
                break;

            case self::TIPO_DOCUMENTACAO:
            case self::TIPO_PROBLEMA:
                $ocorrencia->status = self::STATUS_NOVO;
                break;

        }

        $ocorrencia->tipo = $tipo;
        $ocorrencia->imovel_id = $ID_IMOVEL;
        $ocorrencia->contrato_id = $ID_CONTRATO;
        $ocorrencia->save();

        return $ocorrencia;

    }

    // Finalizar uma ocorrencia
    public static function Finalizar($ID_OCORRENCIA)
    {
        $agora = new DataHora();
        $agora = $agora->dataHoraInsert();

        $ocorrencia = Ocorrencia::find($ID_OCORRENCIA);
        $ocorrencia->datahora_finalizou = $agora;
        $ocorrencia->quem_finalizou = auth()->id();
        $ocorrencia->status = Ocorrencia::STATUS_FINALIZADO;
        $ocorrencia->save();
    }


}
