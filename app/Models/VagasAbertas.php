<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\VagasAbertas
 *
 * @property int $id
 * @property int $vaga_id
 * @property string|null $titulo
 * @property string|null $descricao
 * @property int|null $municipio_id
 * @property bool $ativo
 * @property bool $ativo_sistema
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @property-read \App\Models\Vaga|null $Cargo
 * @property-read \App\Models\Cliente|null $Empresa
 * @property-read \App\Models\Municipio|null $Municipio
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VagaProjeto> $Projetos
 * @property-read int|null $projetos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoVaga> $Simulados
 * @property-read int|null $simulados_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoVaga> $SimuladosAtivos
 * @property-read int|null $simulados_ativos_count
 * @property-read \App\Models\Vaga|null $Vaga
 * @property-read \App\Models\Vaga|null $VagaSelecionada
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas query()
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereAtivoSistema($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereMunicipioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereVagaId($value)
 * @mixin \Eloquent
 */
class VagasAbertas extends Model
{
    use HasApiTokens;
    use TenantTrait;

    protected $fillable = [
        'vaga_id',
        'titulo',
        'descricao',
        'municipio_id',
        'empresa_id',
        'ativo',
        'ativo_sistema',
    ];
    protected $casts = [
        'vaga_id' => 'int',
        'titulo' => 'string',
        'descricao' => 'string',
        'municipio_id' => 'int',
        'empresa_id' => 'int',
        'ativo' => 'boolean',
        'ativo_sistema' => 'boolean',
    ];

    public function Empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

    public function Vaga()
    {
        return $this->hasOne(Vaga::class, 'id', 'vaga_id');
    }

    public function Cargo()
    {
        return $this->hasOne(Vaga::class, 'id', 'vaga_id');
    }

    public function VagaSelecionada()
    {
        return $this->hasOne(Vaga::class, 'id', 'vaga_id');
    }

    public function Municipio()
    {
        return $this->hasOne(Municipio::class, 'id', 'municipio_id');
    }

    public function Simulados()
    {
        return $this->hasMany(SimuladoVaga::class, 'vagas_abertas_id', 'id');
    }

    public function SimuladosAtivos()
    {
        return $this->hasMany(SimuladoVaga::class, 'vaga_aberta_id', 'id')->whereAtivo(true);
    }

    public function Projetos()
    {
        return $this->hasMany(VagaProjeto::class, 'vaga_aberta_id', 'id');
    }

}
