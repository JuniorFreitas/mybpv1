<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NpsRespostaItem
 *
 * @property int $id
 * @property int $nps_resposta_id
 * @property int $nps_pergunta_id
 * @property int $nota
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NpsPergunta $npsPergunta
 * @property-read \App\Models\NpsResposta $npsResposta
 * @mixin \Eloquent
 */
class NpsRespostaItem extends Model
{
    protected $table = 'nps_resposta_itens';

    protected $fillable = [
        'nps_resposta_id',
        'nps_pergunta_id',
        'nota',
    ];

    protected $casts = [
        'nps_resposta_id' => 'integer',
        'nps_pergunta_id' => 'integer',
        'nota' => 'integer',
    ];

    public function npsResposta()
    {
        return $this->belongsTo(NpsResposta::class, 'nps_resposta_id');
    }

    public function npsPergunta()
    {
        return $this->belongsTo(NpsPergunta::class, 'nps_pergunta_id');
    }
}
