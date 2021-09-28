<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExameEmpresa
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $razao_social
 * @property string|null $cnpj
 * @property string|null $cep
 * @property string|null $logradouro
 * @property string|null $numero
 * @property string|null $complemento
 * @property string|null $bairro
 * @property string|null $municipio
 * @property string|null $uf
 * @property string|null $telefone
 * @property string|null $email
 * @property int $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereBairro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereCep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereCnpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereComplemento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereLogradouro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereMunicipio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereRazaoSocial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereTelefone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereUf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameEmpresa whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExameEmpresa extends Model
{
    use HasFactory;
}
