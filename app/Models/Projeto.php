<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Projeto
 *
 * @property int $id
 * @property string $nome
 * @property int $qnt_total
 * @property int $qnt_total_restante
 * @property int $preenchidas
 * @property int $empresa_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto query()
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto wherePreenchidas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereQntTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereQntTotalRestante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Projeto whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Projeto extends Model
{
    use HasFactory, TenantTrait;

    protected $table = 'projetos';

    protected $fillable = [
        'nome',
        'qnt_total',
        'qnt_total_restante',
        'preenchidas',
        'empresa_id',
    ];

    protected $casts = [
        'nome' => 'string',
        'qnt_total' => 'int',
        'qnt_total_restante' => 'int',
        'preenchidas' => 'int',
        'empresa_id' => 'int',
    ];
}
