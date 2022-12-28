<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;


/**
 * App\Models\User
 *
 * @property int $id
 * @property string $nome
 * @property string $login
 * @property string $password
 * @property int $grupo_id
 * @property int $grupo_cloud_id
 * @property int $cliente_id
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $logradouro
 * @property string|null $complemento
 * @property string|null $bairro
 * @property string|null $municipio
 * @property string|null $uf
 * @property string|null $cep
 * @property string|null $cadastrou
 * @property bool $ativo
 * @property bool $temp
 * @property mixed $ultimo_acesso
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GrupoCloud|null $GrupoCloud
 * @property-read \App\Models\Papel|null $Papel
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBairro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCadastrou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereComplemento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrupoCloudId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGrupoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLogradouro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMunicipio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUltimoAcesso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property bool $termos
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTermos($value)
 * @property string $tipo
 * @property string|null $device_token
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTipo($value)
 * @property int|null $empresa_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ArquivamentoDossie
 * @property-read int|null $arquivamento_dossie_count
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $ClienteFuncionarios
 * @property-read int|null $cliente_funcionarios_count
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $ClientesEmpresa
 * @property-read int|null $clientes_empresa_count
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\Fornecedor|null $Fornecedor
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $FornecedoresEmpresa
 * @property-read int|null $fornecedores_empresa_count
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmpresaId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FormaPagamento[] $FormasPagamento
 * @property-read int|null $formas_pagamento_count
 * @property-read \App\Models\EmpresaConfig|null $ConfigEmpresa
 * @property-read User|null $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $EmpresaFuncionarios
 * @property-read int|null $empresa_funcionarios_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmpresaPerimetro[] $Perimetros
 * @property-read int|null $perimetros_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmpresaPerimetro[] $PerimetrosEmpresa
 * @property-read int|null $perimetros_empresa_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmpresaPerimetro[] $PerimetrosFuncionario
 * @property-read int|null $perimetros_funcionario_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmpresaEscala[] $EmpresaEscalas
 * @property-read int|null $empresa_escalas_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmpresaEscala[] $EscalasFuncionario
 * @property-read int|null $escalas_funcionario_count
 * @property mixed $loginl
 * @property-read \App\Models\UsuarioConta|null $BancoConta
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $FotoPerfil
 * @property-read int|null $foto_perfil_count
 * @property int|null $gestor
 * @method static Builder|User whereGestor($value)
 * @property-read \App\Models\Cliente|null $DadosEmpresa
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ClientesLogo
 * @property-read int|null $clientes_logo_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $ClientesMascote
 * @property-read int|null $clientes_mascote_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Cloud[] $Clouds
 * @property-read int|null $clouds_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GrupoCloud[] $GrupoClouds
 * @property-read int|null $grupo_clouds_count
 * @property string|null $api_token
 * @property-read \App\Models\ClienteConfig|null $EmpresaConfiguracoes
 * @property-read \App\Models\EmpresaExame|null $EmpresaExame
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Exportacao[] $Exportacoes
 * @property-read int|null $exportacoes_count
 * @property-read \App\Models\RecuperacaoSenha|null $RecuperacaoSenha
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TipoRecebeEmail[] $UserRecebeEmail
 * @property-read int|null $user_recebe_email_count
 * @method static Builder|User whereApiToken($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @property-read \App\Models\EmpresaConfig|null $EmpresaPontoConfiguracoes
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity, SoftDeletes;

    protected static $logFillable = true;
    protected static $logName = 'user';
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'logradouro',
        'complemento',
        'bairro',
        'municipio',
        'uf',
        'cep',
        'login',
        'password',
        'tipo',
        'grupo_id',
        'grupo_cloud_id',
//        'cliente_id',
        'cadastrou',
        'ativo',
        'temp',
        'termos',
        'ultimo_acesso',
        'device_token',
        'api_token',
        'empresa_id',
        'gestor',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'login' => 'string',
        'password' => 'string',
        'grupo_id' => 'int',
        'grupo_cloud_id' => 'int',
//        'cliente_id' => 'int',
        'tipo' => 'string',
        'email' => 'string',
        'email_verified_at' => 'datetime',
        'logradouro' => 'string',
        'complemento' => 'string',
        'bairro' => 'string',
        'municipio' => 'string',
        'uf' => 'string',
        'cep' => 'string',
        'cadastrou' => 'string',
        'ativo' => 'boolean',
        'temp' => 'boolean',
        'termos' => 'boolean',
        'ultimo_acesso' => 'datetime:d/m/Y H:i:s',
        'device_token' => 'string',
        'api_token' => 'string',
        'empresa_id' => 'int',
        'gestor' => 'boolean'
    ];

    private $listaDeHabilidade = [];
    public const BPSE = 104; // Empresa BPSE
    public const MYBP_EMPRESA_ID = 100; // Empresa MYBP_EMPRESA_ID
    public const SUPORTE = "Suporte";
    public const ADMINISTRADOR = "Administrador";
    public const FUNCIONARIO = "Funcionario";
    public const EMPRESA = "Empresa";
    public const GESTOR = "Gestor";
    public const CANDIDATO = "Candidato";
    public const CLINICA_EXAME = "ClinicaExame";
    public const LISTA_SUPORTE = [1, 54831];

    public const TIPOS_USUARIOS_GERENCIAIS = [
        self::SUPORTE,
        self::ADMINISTRADOR,
        self::FUNCIONARIO,
        self::GESTOR
    ];

    public const TIPOS_USUARIOS_COMUNS = [
        self::ADMINISTRADOR,
        self::FUNCIONARIO,
        self::GESTOR
    ];


    public static function getUser($fields = null)
    {
        if ($fields) {
            return User::find(auth()->id(), $fields);;
        }
        return User::find(auth()->id());;
    }

    // retorna um array de habilidades
    public function listaDeHabilidades()
    {
        if (count($this->listaDeHabilidade) == 0) {
            // buscar no banco qual é o papel dele. e dair fazer o array com todas as habilidades que ele tem
            $lista = collect([]);

            //foreach ($this->papel as $papel) {

            //$habilidades = $papel->habilidades->pluck('nome');
            $habilidades = $this->papel->habilidades->pluck('nome');
            foreach ($habilidades as $habilidade) {

                if ($lista->search($habilidade) === false) {

                    $lista->push($habilidade);

                }
            }

            //}
            $this->listaDeHabilidade = $lista->toArray();
        }
        return $this->listaDeHabilidade;
    }

    public function getLoginlAttribute($value)
    {
        return trim(mb_strtolower($value));
    }

    public function setUltimoAcessoAttribute($value)
    {
        $datahora = new DataHora($value);
        $this->attributes['ultimo_acesso'] = $datahora->dataHoraInsert();
    }

    //relacionamento Tokens() esta dentro de HasApiTokens::class

    //Modificador ->nascimento
    public function setLoginlAttribute($value)
    {
        $this->attributes['login'] = trim(mb_strtolower($value));
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'id');
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'id');
    }

    public function Empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

    public function Fornecedor()
    {
        return $this->hasOne(Fornecedor::class, 'id', 'id');
    }


    public function Papel()
    {
        return $this->hasOne(Papel::class, 'id', 'grupo_id');
    }

    public function GrupoCloud()
    {
        return $this->hasOne(GrupoCloud::class, 'id', 'grupo_cloud_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'curriculo_id', 'id');
    }

    public function ArquivamentoDossie()
    {
        return $this->belongsToMany(Arquivo::class, 'dossie', 'feedback_id', 'arquivo_id')
            ->withPivot(['tipo', 'feedback_id', 'label'])
            ->whereTipo('ArquivamentoDossie');
    }

    public function ClientesEmpresa()
    {
        return $this->belongsToMany(User::class, 'empresa_clientes', 'empresa_id', 'cliente_id', 'empresa_id');
    }

    public function UserRecebeEmail()
    {
        return $this->belongsToMany(TipoRecebeEmail::class, 'user_recebe_email', 'user_id', 'tipo_email_id')->withPivot(['ativo']);;
    }

    public function ClientesLogo()
    {
        return $this->belongsToMany(Arquivo::class, 'cliente_logotipo', 'cliente_id', 'arquivo_id', 'empresa_id');
    }

    public function ClientesMascote()
    {
        return $this->belongsToMany(Arquivo::class, 'cliente_mascote', 'cliente_id', 'arquivo_id', 'empresa_id');
    }

    public function ClienteFuncionarios()
    {
        return $this->belongsToMany(User::class, 'cliente_funcionarios', 'funcionario_id', 'cliente_id');
    }

    public function EmpresaFuncionarios()
    {
        return $this->belongsToMany(User::class, 'empresa_funcionarios', 'empresa_id', 'funcionario_id');
    }

    public function FornecedoresEmpresa()
    {
        return $this->belongsToMany(User::class, 'empresa_fornecedores', 'empresa_id', 'fornecedor_id', 'empresa_id');
    }

    public function FormasPagamento()
    {
        return $this->hasMany(FormaPagamento::class, 'empresa_id', 'id');
    }

    //--------------------------------

    public function DadosEmpresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

    public function ConfigEmpresa()
    {
        return $this->hasOne(EmpresaConfig::class, 'empresa_id', 'empresa_id');
    }

    public function PerimetrosEmpresa()
    {
        return $this->hasMany(EmpresaPerimetro::class, 'empresa_id', 'empresa_id');
    }

    public function PerimetrosFuncionario()
    {
        return $this->belongsToMany(EmpresaPerimetro::class, 'funcionario_perimetros', 'funcionario_id', 'perimetro_id');
    }

    public function EmpresaEscalas()
    {
        return $this->hasMany(EmpresaEscala::class, 'empresa_id', 'empresa_id');
    }

    public function EscalasFuncionario()
    {
        return $this->belongsToMany(EmpresaEscala::class, 'funcionario_escalas', 'funcionario_id', 'escala_id');
    }

    public function avaliadoresFuncionario()
    {
        return $this->belongsToMany(Avaliacao::class, 'avaliacoes', 'funcionario_id', 'avaliador_id');
    }

    public function FotoPerfil()
    {
        return $this->belongsToMany(Arquivo::class, 'user_anexos', 'user_id', 'arquivo_id');
    }

    public function BancoConta()
    {
        return $this->hasOne(UsuarioConta::class, 'user_id', 'id');
    }

    /**
     * Relacionamento com os clouds para o usuário referente a empresa que ele faz parte.
     */
    public function Clouds()
    {
        return $this->belongsToMany(Cloud::class, 'user_clouds', 'user_id', 'cloud_id');
    }

    public function CloudsAtivo()
    {
        return $this->Clouds()->where('ativo', true);
    }

    public function GrupoClouds()
    {
        return $this->belongsToMany(GrupoCloud::class, 'user_grupo_cloud', 'grupo_cloud_id', 'user_id');
    }

    public function RecuperacaoSenha()
    {
        return $this->hasOne(RecuperacaoSenha::class, 'user_id', 'id');
    }

    public function Exportacoes()
    {
        return $this->hasMany(Exportacao::class, 'user_id', 'id')->whereRemovido(false)->orderBy('created_at', 'desc');
    }

    public function EmpresaConfiguracoes()
    {
        return $this->hasOne(ClienteConfig::class, 'cliente_id', 'empresa_id');
    }

    public function EmpresaPontoConfiguracoes()
    {
        return $this->hasOne(EmpresaConfig::class, 'empresa_id', 'empresa_id');
    }

    public function EmpresaExame()
    {
        return $this->hasOne(EmpresaExame::class, 'user_id', 'id');
    }

    /**
     * Sincronizamento de Empresa e Funcionario e Funcionario Empresa
     * @param $empresa_id
     * @param $colaborador_id
     * @return void
     */
    public static function SincronizaEmpresaFuncionario($empresa_id, $colaborador_id)
    {
        $Empresa = User::select(['id'])->find($empresa_id);
        $Colaborador = User::find($colaborador_id);

        if ($Empresa->EmpresaFuncionarios()->whereFuncionarioId($Colaborador->id)->first()){
            $Empresa->EmpresaFuncionarios()->detach($Colaborador->id);
        }
        $Empresa->EmpresaFuncionarios()->attach($Colaborador->id);
        $Colaborador->ClienteFuncionarios()->sync($Empresa->id);
    }

    public function Avaliadores()
    {
        return $this->hasMany(AvaliacaoFeedback::class, 'funcionario_id', 'id')->where('origem_feedback', AvaliacaoFeedback::ORIGEM_AVALIADOR);
    }

    public function scopeTiposGerenciais($query)
    {
        return $query->whereIn('tipo', User::TIPOS_USUARIOS_GERENCIAIS);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if ($model->tipo == self::CLINICA_EXAME) {
                $model->setAttribute('api_token', Str::uuid());
            }
        });
//        static::created(function ($model) {
//            \Cache::forget("contatosEmpresa" . auth()->user()->empresa_id);
//        });
//        static::updated(function ($model) {
//            \Cache::forget("contatosEmpresa" . auth()->user()->empresa_id);
//        });
    }
}
