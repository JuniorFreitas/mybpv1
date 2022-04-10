<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ClienteConfig
 *
 * @property int $id
 * @property int|null $verifica_mes_vencimento
 * @property int|null $cliente_id
 * @property-read \App\Models\Cliente|null $Cliente
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereVerificaMesVencimento($value)
 * @mixin \Eloquent
 */
class ClienteConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'verifica_mes_vencimento',
        'cliente_id',
    ];

    protected $casts = [
        'verifica_mes_vencimento' => 'int',
        'cliente_id' => 'int',
    ];

    public $timestamps = false;

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

}
