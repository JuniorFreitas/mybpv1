<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Instrutor
 *
 * @property int $id
 * @property string $nome
 * @property int|null $arquivo_id
 * @property string|null $assinatura
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereArquivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereAssinatura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $cargo
 * @property string|null $registro
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereCargo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Instrutor whereRegistro($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 */
class Instrutor extends Model
{
    use HasFactory;

    protected $table = 'instrutores';

    protected $fillable = ['id', 'nome', 'cargo', 'registro', 'arquivo_id', 'assinatura', 'ativo'];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'cargo' => 'string',
        'registro' => 'string',
        'arquivo_id' => 'int',
        'assinatura' => 'string',
        'ativo' => 'boolean'
    ];

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'instrutor_anexos', 'instrutor_id', 'arquivo_id');
    }


}
