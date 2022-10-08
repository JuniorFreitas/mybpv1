<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ZapNumeros
 *
 * @property int $id
 * @property string $telefone
 * @property int $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros query()
 * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZapNumeros whereTelefone($value)
 * @mixin \Eloquent
 */
class ZapNumeros extends Model
{
    use HasFactory;
}
