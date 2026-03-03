<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoRecebeEmail
 *
 * @property int $id
 * @property string $nome
 * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoRecebeEmail whereNome($value)
 * @mixin \Eloquent
 */
class TipoRecebeEmail extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'TipoRecebeEmail';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'nome',
    ];

    protected $casts = [
        'nome' => 'string',
    ];

    protected $table = 'tipo_recebe_email';

    public $timestamps = false;

    const AVALIACAO_90_DIAS  = 'Avaliação 90 Dias';
    const VENCIMENTO_ASO  = 'Vencimento ASO';
    const VENCIMENTO_FERIAS  = 'Vencimento Férias';
    const VENCIMENTO_TREINAMENTO  = 'Vencimento Treinamento';

    const LISTA_TIPOS  = [
        self::AVALIACAO_90_DIAS,
        self::VENCIMENTO_ASO,
        self::VENCIMENTO_FERIAS,
        self::VENCIMENTO_TREINAMENTO,
    ];
}
