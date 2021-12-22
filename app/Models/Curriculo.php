<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Curriculo
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CurriculoExperiencia[] $Experiencias
 * @property-read int|null $experiencias_count
 * @property-read \App\Models\Escolaridade|null $Formacao
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CurriculoQualificacao[] $Qualificacoes
 * @property-read int|null $qualificacoes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TelefoneCurriculo[] $Telefones
 * @property-read int|null $telefones_count
 * @property-read \App\Models\User|null $Usuario
 * @property-read \App\Models\Vaga|null $Vaga
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $created_at
 * @property-read mixed $email
 * @property-read mixed $endereco_completo
 * @property-read mixed $filiacao_mae
 * @property-read mixed $filiacao_pai
 * @property-read mixed $idade
 * @property mixed $nascimento
 * @property-read mixed $nome
 * @property-read mixed $rg_format
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo query()
 * @mixin \Eloquent
 * @property int $id
 * @property string $cpf
 * @property string|null $rg
 * @property string|null $orgao_expeditor
 * @property string|null $carteira_trabalho
 * @property string|null $cnh
 * @property string|null $sexo
 * @property string|null $logradouro
 * @property string|null $complemento
 * @property string|null $bairro
 * @property string|null $municipio
 * @property string|null $uf
 * @property string|null $cep
 * @property int|null $formacao
 * @property string|null $formacao_instituicao
 * @property string|null $formacao_curso
 * @property string|null $formacao_status
 * @property int|null $vaga_pretendida
 * @property string|null $uf_vaga
 * @property int|null $municipio_id
 * @property bool|null $pcd
 * @property string|null $cid
 * @property bool|null $viajar
 * @property string $lido
 * @property int|null $usuario_lido
 * @property string|null $datalido
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereBairro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCarteiraTrabalho($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCnh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereComplemento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereDatalido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFiliacaoMae($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFiliacaoPai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFormacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFormacaoCurso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFormacaoInstituicao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereFormacaoStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereLido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereLogradouro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereMunicipio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereMunicipioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereNascimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereOrgaoExpeditor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo wherePcd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereRg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereSexo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereUf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereUfVaga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereUsuarioLido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereVagaPretendida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Curriculo whereViajar($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexo
 * @property-read int|null $anexo_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $AnexoCpfRg
 * @property-read int|null $anexo_cpf_rg_count
 * @property-read \App\Models\CurriculoAtualizacao|null $Atualizacao
 * @property-read \App\Models\Municipio|null $Cidade
 * @property-read \App\Models\FeedbackCurriculo|null $FeedBack
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $FotoTres
 * @property-read int|null $foto_tres_count
 * @property-read \App\Models\ParecerRh|null $ParecerRh
 * @property-read \App\Models\ParecerEntrevistaTecnica|null $ParecerTecnica
 * @property-read \App\Models\NotificacaoWhats|null $WhatsAppNotificacao
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $AnexosCpfRg
 * @property-read int|null $anexos_cpf_rg_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Antecedentes
 * @property-read int|null $antecedentes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $CartaSindicato
 * @property-read int|null $carta_sindicato_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $CartaoVacinaFilho
 * @property-read int|null $cartao_vacina_filho_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $CarteiraVacina
 * @property-read int|null $carteira_vacina_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $CertificadoEscolaridade
 * @property-read int|null $certificado_escolaridade_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $CertificadoReservista
 * @property-read int|null $certificado_reservista_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ComprovanteEnd
 * @property-read int|null $comprovante_end_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ContaBanco
 * @property-read int|null $conta_banco_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $CtpsFrente
 * @property-read int|null $ctps_frente_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $CtpsVerso
 * @property-read int|null $ctps_verso_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $DeclaracaoEscolarFilho
 * @property-read int|null $declaracao_escolar_filho_count
 * @property-read \App\Models\ParecerRota|null $ParecerRota
 * @property-read \App\Models\ParecerTestePratico|null $ParecerTeste
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $PisRescisao
 * @property-read int|null $pis_rescisao_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $RgcpfFilho
 * @property-read int|null $rgcpf_filho_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $TituloEleitor
 * @property-read int|null $titulo_eleitor_count
 * @property-read \App\Models\Treinamento|null $Treinamentos
 * @property-read \App\Models\User|null $Pessoa
 * @property-read \App\Models\ParabensEnviado|null $Parabens
 * @property-read \App\Models\UsuarioConta|null $BancoConta
 * @property-read mixed $cpf_format
 * @property-read \App\Models\User|null $User
 */
class Curriculo extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'curriculo';
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

    protected $table = 'curriculos';
    protected $fillable = [
        "cpf",
        "rg",
        "orgao_expeditor",
        "carteira_trabalho",
        "nome",
        "cnh",
        "nascimento",
        "logradouro",
        "complemento",
        "bairro",
        "municipio",
        "uf",
        "cep",
        "email",
        "formacao",
        "formacao_instituicao",
        "formacao_curso",
        "formacao_status",
        "vaga_pretendida",
        "uf_vaga",
        "municipio_id",
        "pcd",
        "cid",
        "viajar",
        "lido",
        "usuario_lido",
        "datalido",
        "filiacao_pai",
        "filiacao_mae",

    ];
    protected $casts = [
        "id" => "int",
        "cpf" => "string",
        "rg" => "string",
        "orgao_expeditor" => "string",
        "carteira_trabalho" => "string",
        "nome" => "string",
        "cnh" => "string",
        "nascimento" => "string",
        "logradouro" => "string",
        "complemento" => "string",
        "bairro" => "string",
        "municipio" => "string",
        "uf" => "string",
        "cep" => "string",
        "email" => "string",
        "formacao" => "int",
        "formacao_instituicao" => "string",
        "formacao_curso" => "string",
        "formacao_status" => "string",
        "vaga_pretendida" => "int",
        "uf_vaga" => "string",
        "municipio_id" => "int",
        "pcd" => "boolean",
        "cid" => "string",
        "viajar" => "boolean",
        "lido" => "boolean",
        "usuario_lido" => "int",
        "datalido" => "string",
        "filiacao_pai" => "string",
        "filiacao_mae" => "string",
        'created_at' => 'date:d/m/Y \\à\\s H:m\\h',
        'updated_at' => 'date:d/m/Y \\à\\s H:m\\h',
    ];

    protected $appends = ['idade', 'endereco_completo', 'rg_format'];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

//    public function getCpfFormatAttribute()
    public function getCpfFormatAttribute()
    {
        $cpf = $this->attributes['cpf'];
        $pt1 = substr($cpf, 4, 3);
        $pt2 = substr($cpf, 8, 3);

        return "XXX.{$pt1}.{$pt2}-XX";
//        return $pt1 . '.XXX.XXX-' . $pt2;
    }


    public function getRgFormatAttribute()
    {
        if (!is_null($this->attributes['rg'])) {
            $rg = "RG: {$this->attributes['rg']} <br/> Emitente: {$this->attributes['orgao_expeditor']}";
            return $rg;
        }
        return null;
    }

    public function getNomeAttribute($value)
    {
        return mb_strtoupper($value);
    }

    public function getFiliacaoPaiAttribute($value)
    {
        return mb_strtoupper($value);
    }

    //Acessor ->filiacaomae
    public function getFiliacaoMaeAttribute($value)
    {
        return mb_strtoupper($value);
    }

    public function getEmailAttribute($value)
    {
        return trim(mb_strtolower($value));
    }

    //Modificador ->nascimento
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = trim(mb_strtolower($value));
    }

    public function getIdadeAttribute()
    {
        $dataH = new DataHora();
        return DataHora::distanciaTempo($this->nascimento . ' ' . $dataH->hora() . ':' . $dataH->minuto() . ':00', $dataH->dataHoraInsert())['ano'];
    }

    //Acessor ->nascimento
//    public function getCreatedAtAttribute($value)
//    {
//        $data = new DataHora($this->attributes['created_at']);
//        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto() . 'h';
//    }

    //Acessor ->nascimento
    public function getNascimentoAttribute($value)
    {
        $data = new DataHora($this->attributes['nascimento']);
        return $data->dataCompleta();
    }

    //Modificador ->nascimento
    public function setNascimentoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['nascimento'] = $data->dataInsert();
    }

//    //Acessor ->datalido
//    public function getDatalidoAttribute($value)
//    {
//        $data = new DataHora($this->attributes['datalido']);
//        return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto() . 'h';
//    }

    /* //Modificador ->datalido
     public function setDatalidoAttribute($value)
     {
         $data = new DataHora($value);
         $this->attributes['datalido'] = $data->dataInsert();
     }*/

    public function getEnderecoCompletoAttribute()
    {
        $endereco = $this->logradouro;
        $cep = $this->cep;
        $numero = $this->numero == '' ? 'S/N' : $this->numero;
        $complemento = $this->complemento;

        if ($complemento) {
            $endereco_completo = "{$endereco}, {$complemento}, {$numero}, {$cep}, {$this->bairro}, {$this->municipio} / {$this->uf}.";
        } else {
            $endereco_completo = "{$endereco}, {$numero}, {$cep}, {$this->bairro}, {$this->municipio} / {$this->uf}.";
        }

        return $endereco_completo;
    }

    public function Qualificacoes()
    {
        return $this->hasMany(CurriculoQualificacao::class, 'curriculo_id', 'id');
    }

//   Novo Relacionamento
    public function Pessoa()
    {
        return $this->hasOne(User::class, 'id', 'id');
    }


    public function Experiencias()
    {
        return $this->hasMany(CurriculoExperiencia::class, 'curriculo_id', 'id');
    }

    public function Vaga()
    {
        return $this->hasOne(Vaga::class, 'id', 'vaga_pretendida');
    }

    public function Formacao()
    {
        return $this->hasOne(Escolaridade::class, 'id', 'formacao');
    }

    public function Telefones()
    {
        return $this->hasMany(TelefoneCurriculo::class, 'curriculo_id', 'id');
    }

    public function AnexosCpfRg()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('anexoscpfrg');
    }

    public function ComprovanteEnd()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('comprovante_end');
    }

    public function CtpsFrente()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('ctps_frente');
    }

    public function CtpsVerso()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('ctps_verso');
    }

    public function Antecedentes()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('antecedentes');
    }

    public function TituloEleitor()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('titulo_eleitor');
    }

    public function CertificadoReservista()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('certificado_reservista');
    }

    public function PisRescisao()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('pis_rescisao');
    }

    public function CertificadoEscolaridade()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('certificado_escolaridade');
    }

    public function ContaBanco()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('conta_banco');
    }

    public function CartaSindicato()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('carta_sindicato');
    }

    public function CarteiraVacina()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('carteira_vacina');
    }

    public function RgcpfFilho()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('rgcpf_filho');
    }

    public function CartaoVacinaFilho()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('cartao_vacina_filho');
    }

    public function DeclaracaoEscolarFilho()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->whereTipo('declaracao_escolar_filho');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'usuario_lido');
    }

    public function FeedBack()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'curriculo_id', 'id');
    }

    public function Cidade()
    {
        return $this->hasOne(Municipio::class, 'id', 'municipio_id');
    }

    public function Atualizacao()
    {
        return $this->hasOne(CurriculoAtualizacao::class, 'curriculo_id', 'id')->orderByDesc('updated_at');
    }

    public function Anexo()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->withPivot(['tipo']);
    }

    public function FotoTres()
    {
        return $this->belongsToMany(Arquivo::class, 'documentos_curriculos', 'curriculo_id', 'arquivo_id')->withPivot(['tipo'])->whereTipo('foto3x4');
    }

    public function WhatsAppNotificacao()
    {
        return $this->hasOne(NotificacaoWhats::class, 'curriculo_id', 'id');
    }

    public function ParecerRh()
    {
        return $this->hasOne(ParecerRh::class, 'curriculo_id', 'id');
    }

    public function ParecerTecnica()
    {
        return $this->hasOne(ParecerEntrevistaTecnica::class, 'curriculo_id', 'id');
    }

    public function ParecerRota()
    {
        return $this->hasOne(ParecerRota::class, 'curriculo_id', 'id');
    }

    public function ParecerTeste()
    {
        return $this->hasOne(ParecerTestePratico::class, 'curriculo_id', 'id');
    }

    public function Treinamentos()
    {
        return $this->hasOne(Treinamento::class, 'curriculo_id', 'id');
    }

    public function Parabens()
    {
        return $this->hasOne(ParabensEnviado::class, 'curriculo_id', 'id');
    }

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'id');
    }

    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::updating(function ($model) {
            $model->User->find($model->id)->update([
                'nome' => $model->nome,
                'login' => $model->email,
            ]);
        });

        static::addGlobalScope(new ScopeEmpresa);
    }

}
