<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MotivoRescisao
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao query()
 * @mixin \Eloquent
 * @property int $id
 * @property string $descricao
 * @property bool $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereId($value)
 * @property string|null $nome_pdf
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereNomePdf($value)
 */
class MotivoRescisao extends Model
{
    use HasFactory;

    protected $table = 'motivo_rescisao';
    protected $fillable = [
        'descricao',
        'nome_pdf',
        'ativo',
    ];
    protected $casts = [
        'id' => 'int',
        'descricao' => 'string',
        'nome_pdf' => 'string',
        'ativo' => 'boolean',
    ];
    public $timestamps = false;
}
