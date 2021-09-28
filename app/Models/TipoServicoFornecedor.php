<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoServicoFornecedor
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TipoServicoFornecedor extends Model
{
    use HasFactory;

    protected $table = 'tipo_servico_fornecedor';
    protected $fillable = ['label', 'ativo'];
    protected $casts = ['label' => 'string', 'ativo' => 'boolean'];
}
