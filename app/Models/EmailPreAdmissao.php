<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\EmailPreAdmissao
 *
 * @property int $id
 * @property int $empresa_id
 * @property int $curriculo_id
 * @property int $quem_enviou_id
 * @property string|null $observacao
 * @property int $email_atual
 * @property int $email_padrao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereEmailAtual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereEmailPadrao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereQuemEnviouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailPreAdmissao whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmailPreAdmissao extends Model
{
    use HasFactory, TenantTrait, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'curriculo';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'emails_pre_admissao';

    protected $fillable = [
        'empresa_id',
        'curriculo_id',
        'quem_enviou_id',
        'observacao',
        'email_atual',
        'email_padrao'
    ];

    protected $cast = [
        'empresa_id' => 'int',
        'curriculo_id' => 'int',
        'quem_enviou_id' => 'int',
        'observacao' => 'string',
        'email_atual' => 'boolean',
        'email_padrao' => 'boolean'

    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->quem_enviou_id = auth()->check() ? auth()->id() : $model->quem_enviou_id;
        });
    }
}
