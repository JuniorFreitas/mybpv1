<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

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
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'ZapNumeros';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

}
