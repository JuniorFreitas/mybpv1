<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MotivoRescisao
 *
 * @property int $id
 * @property string $descricao
 * @property bool $ativo
 * @property string|null $nome_pdf
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao query()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisao whereNomePdf($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class MotivoRescisao extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'MotivoRescisao';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'motivo_rescisao';
    protected $fillable = [
        'descricao',
        'nome_pdf',
        'ativo',
    ];
    protected $casts = [
        'id' => 'int',
        'descricao' => 'string',
        'nome_pdf' => 'string',
        'ativo' => 'boolean',
    ];
    public $timestamps = false;
}
