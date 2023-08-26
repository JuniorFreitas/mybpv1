<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Fornecedor
 *
 * @property int $id
 * @property string $tipo Fornecedor, Terceiro, Parceiro
 * @property string|null $cnpj
 * @property string|null $cpf
 * @property string|null $nome
 * @property string $tipo_pessoa
 * @property string|null $razao_social
 * @property string|null $nome_fantasia
 * @property string|null $cep
 * @property string|null $logradouro
 * @property string|null $numero
 * @property string|null $complemento
 * @property string|null $bairro
 * @property string|null $municipio
 * @property string|null $uf
 * @property string|null $contato
 * @property string|null $email
 * @property string|null $aniversario
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
 * @property-read int|null $anexos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FornecedorServico> $Servicos
 * @property-read int|null $servicos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TelefoneFornecedor> $Telefones
 * @property-read int|null $telefones_count
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $endereco_completo
 * @method static Builder|Fornecedor newModelQuery()
 * @method static Builder|Fornecedor newQuery()
 * @method static Builder|Fornecedor query()
 * @method static Builder|Fornecedor whereAniversario($value)
 * @method static Builder|Fornecedor whereAtivo($value)
 * @method static Builder|Fornecedor whereBairro($value)
 * @method static Builder|Fornecedor whereCep($value)
 * @method static Builder|Fornecedor whereCnpj($value)
 * @method static Builder|Fornecedor whereComplemento($value)
 * @method static Builder|Fornecedor whereContato($value)
 * @method static Builder|Fornecedor whereCpf($value)
 * @method static Builder|Fornecedor whereCreatedAt($value)
 * @method static Builder|Fornecedor whereEmail($value)
 * @method static Builder|Fornecedor whereId($value)
 * @method static Builder|Fornecedor whereLogradouro($value)
 * @method static Builder|Fornecedor whereMunicipio($value)
 * @method static Builder|Fornecedor whereNome($value)
 * @method static Builder|Fornecedor whereNomeFantasia($value)
 * @method static Builder|Fornecedor whereNumero($value)
 * @method static Builder|Fornecedor whereRazaoSocial($value)
 * @method static Builder|Fornecedor whereTipo($value)
 * @method static Builder|Fornecedor whereTipoPessoa($value)
 * @method static Builder|Fornecedor whereUf($value)
 * @method static Builder|Fornecedor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Fornecedor extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'fornecedores';
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

    protected $table = 'fornecedores';

    protected $fillable = [
        'id',
        'tipo',
        'cnpj',
        'cpf',
        'nome',
        'tipo_pessoa',
        'razao_social',
        'nome_fantasia',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'municipio',
        'uf',
        'contato',
        'aniversario',
        'email',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'tipo' => 'string',
        'cnpj' => 'string',
        'cpf' => 'string',
        'nome' => 'string',
        'tipo_pessoa' => 'string',
        'razao_social' => 'string',
        'nome_fantasia' => 'string',
        'cep' => 'string',
        'logradouro' => 'string',
        'numero' => 'string',
        'complemento' => 'string',
        'bairro' => 'string',
        'municipio' => 'string',
        'uf' => 'string',
        'contato' => 'string',
        'aniversario' => 'string',
        'email' => 'string',
        'ativo' => 'boolean',
    ];

    protected $appends = ['endereco_completo'];

    public const PESSOA_FISICA = 'pessoa_física';
    public const PESSOA_JURIDICA = 'pessoa_jurídica';

    public const TIPO_FORNECEDOR = 'fornecedor';
    public const TIPO_TERCEIRO = 'terceiro';
    public const TIPO_PARCEIRO = 'parceiro';

    public const VENCIMENTO_MENSAL = 'mensal';
    public const VENCIMENTO_TRIMESTRAL = 'trimestral';
    public const VENCIMENTO_SEMESTRAL = 'semestral';
    public const VENCIMENTO_ANUAL = 'anual';

//    public const STATUS_INICIADO = 'iniciado';
//    public const STATUS_CONCLUIDO = 'concluido';
//    public const STATUS_NAO_INICIADO = 'não iniciado';


    public function getEnderecoCompletoAttribute()
    {
        $endereco = $this->logradouro;
        $bairro = $this->bairro;
        $cep = $this->cep;
        $numero = $this->numero ? $this->numero : 'S/N';
        $complemento = $this->complemento;

        if ($complemento) {
            $endereco_completo = "{$endereco}, {$complemento}, {$numero}, {$bairro}, {$cep}, {$this->municipio}-{$this->uf}";
        } else {
            $endereco_completo = "{$endereco}, {$numero}, {$bairro}, {$cep}, {$this->municipio}-{$this->uf}";
        }

        return $endereco_completo;
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'id');
    }

    public function Servicos()
    {
        return $this->hasMany(FornecedorServico::class, 'fornecedor_id', 'id')->orderByDesc('id');
    }

    public function Telefones()
    {
        return $this->hasMany(TelefoneFornecedor::class, 'fornecedor_id', 'id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'fornecedor_anexos', 'fornecedor_id', 'arquivo_id');
    }

    protected static function booted()
    {

        static::updating(function ($model) {
            if ($model->tipo_pessoa == self::PESSOA_JURIDICA) {
                $model->Usuario->find($model->id)->update([
                    'nome' => $model->razao_social,
                    'login' => $model->email,
                    'ativo' => $model->ativo
                ]);
            } else {
                $model->Usuario->find($model->id)->update([
                    'nome' => $model->nome,
                    'login' => $model->email,
                    'ativo' => $model->ativo
                ]);
            }
        });

        static::addGlobalScope('scopeFornecedor', function (Builder $builder) {
            $builder->whereIn('id', auth()->user()->FornecedoresEmpresa->pluck('id'));
        });
    }
}
