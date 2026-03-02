<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoServicoFornecedor
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoServicoFornecedor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TipoServicoFornecedor extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'TipoServicoFornecedor';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'tipo_servico_fornecedor';
    protected $fillable = ['label', 'ativo'];
    protected $casts = ['label' => 'string', 'ativo' => 'boolean'];
}
