<?php

namespace App\Models;

use App\Jobs\JobBoasVindasClinica;
use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Models\Models\EmpresaExame
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $nome
 * @property mixed $dados
 * @property int $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereDados($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmpresaExame whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User|null $Empresa
 */
class EmpresaExame extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = [
        "user_id",
        "empresa_id",
        "nome",
        "dados",
        "ativo",
    ];

    protected $casts = [
        "id" => 'int',
        "user_id" => 'int',
        "empresa_id" => 'int',
        "nome" => 'string',
        "dados" => 'array',
        "ativo" => 'boolean',
    ];

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    protected static function booted()
    {

        static::creating(function ($model) {
            $password = Str::random(8);
            $user = User::create([
                'nome' => $model->nome,
                'login' => $model->dados['email'],
                'tipo' =>  User::CLINICA_EXAME,
                'password' => bcrypt($password),
                'temp' => false,
                'empresa_id' => $model->empresa_id,
                'ativo' => $model->ativo,
            ]);
            $model->setAttribute('user_id', $user->id);

            $dadosEmail = [
                'nome' => $model->nome,
                'email' => $model->dados['email'],
                'senha' => $password,
                'empresa_id' => $model->empresa_id,
            ];
            JobBoasVindasClinica::dispatch($dadosEmail);
        });

    }
}
