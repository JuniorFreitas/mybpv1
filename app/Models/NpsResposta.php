<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NpsResposta
 *
 * @property int $id
 * @property int $user_id
 * @property int $empresa_id
 * @property int|null $nps_ciclo_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NpsRespostaItem> $itens
 * @property-read \App\Models\NpsCiclo|null $npsCiclo
 * @property-read \App\Models\User $User
 * @mixin \Eloquent
 */
class NpsResposta extends Model
{
    protected $table = 'nps_respostas';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'nps_ciclo_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'empresa_id' => 'integer',
        'nps_ciclo_id' => 'integer',
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function npsCiclo()
    {
        return $this->belongsTo(NpsCiclo::class, 'nps_ciclo_id');
    }

    public function itens()
    {
        return $this->hasMany(NpsRespostaItem::class, 'nps_resposta_id');
    }
}
