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
 * @property bool|null $envia_whatsapp
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereEnviaWhatsapp($value)
 * @property int|null $vencimento_aso
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteConfig whereVencimentoAso($value)
 */
class ClienteConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'envia_whatsapp',
        'verifica_mes_vencimento',
        'cliente_id',
        'vencimento_aso',
    ];

    protected $casts = [
        'envia_whatsapp' => 'boolean',
        'verifica_mes_vencimento' => 'int',
        'cliente_id' => 'int',
        'vencimento_aso' => 'int',
    ];

    public $timestamps = false;

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

}
