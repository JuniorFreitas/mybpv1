<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\LogoCliente
 *
 * @property int $id
 * @property string $nome
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Fotos
 * @property-read int|null $fotos_count
 * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogoCliente whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LogoCliente extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'LogoCliente';
    protected $table = 'cliente_logo_sites';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    public function Fotos() {
        return $this->belongsToMany(Arquivo::class, 'cliente_logo_foto', 'cliente_id', 'arquivo_id')->withPivot(['ordem'])->orderBy('ordem');
    }
}
