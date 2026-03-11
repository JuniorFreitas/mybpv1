<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PesquisaClimaTipo
 *
 * @property int $id
 * @property string $nome
 * @property int $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesquisaClimaPergunta> $PesquisaClimaPergunta
 * @property-read int|null $pesquisa_clima_pergunta_count
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaTipo whereNome($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class PesquisaClimaTipo extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'PesquisaClimaTipo';
    protected $fillable = [
        'nome',
        'ativo'
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    public $timestamps = false;


    public function PesquisaClimaPergunta()
    {
        return $this->hasMany(PesquisaClimaPergunta::class, 'tipo_id', 'id');
    }

}
