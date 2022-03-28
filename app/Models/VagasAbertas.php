<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\VagasAbertas
 *
 * @property int $id
 * @property int $vagas_id
 * @property string|null $titulo
 * @property string|null $descricao
 * @property string|null $requerimentos
 * @property string|null $uf_vaga
 * @property string|null $municipio_id
 * @property int $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereMunicipioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereRequerimentos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereUfVaga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VagasAbertas whereVagasId($value)
 * @mixin \Eloquent
 * @property int $vaga_id
 * @property-read \App\Models\Municipio $Municipio
 * @property-read \App\Models\Vaga $Vaga
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VagasAbertas whereVagaId($value)
 * @property int|null $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|VagasAbertas whereEmpresaId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SimuladoVaga[] $Simulados
 * @property-read int|null $simulados_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SimuladoVaga[] $SimuladosAtivos
 * @property-read int|null $simulados_ativos_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
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
    ];
    protected $casts = [
        'vaga_id' => 'int',
        'titulo' => 'string',
        'descricao' => 'string',
        'municipio_id' => 'int',
        'empresa_id' => 'int',
        'ativo' => 'boolean',
    ];

    public function Empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

    public function Vaga()
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

}
