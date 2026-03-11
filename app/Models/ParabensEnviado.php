<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ParabensEnviado
 *
 * @property int $id
 * @property int|null $curriculo_id
 * @property int|null $empresa_id
 * @property int $ano
 * @property string|null $status
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado query()
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereAno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParabensEnviado whereStatus($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class ParabensEnviado extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'ParabensEnviado';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'empresa_id',
        'curriculo_id',
        'ano',
        'status'
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'curriculo_id' => 'int',
        'ano' => 'int',
        'status' => 'string'
    ];

    const STATUS_ENVIADO = "enviado";
    const STATUS_ENVIANDO = "enviando";
    const STATUS_NAO = "não";
    const STATUS_ERRO = "erro";

    const LISTA_MESES = [
        '1' => 'Janeiro',
        '2' => 'Fevereiro',
        '3' => 'Março',
        '4' => 'Abril',
        '5' => 'Maio',
        '6' => 'Junho',
        '7' => 'Julho',
        '8' => 'Agosto',
        '9' => 'Setembro',
        '10' => 'Outubro',
        '11' => 'Novembro',
        '12' => 'Dezembro',
    ];

    protected $table = 'parabens_enviados';

    public $timestamps = false;

}
