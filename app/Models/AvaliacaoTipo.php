<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\AvaliacaoTipo
 *
 * @property int $id
 * @property string $nome
 * @property string $descricao
 * @property int $empresa_id
 * @property bool $ativo
 * @property-read \App\Models\AvaliacaoTopico $AvaliacaoTipo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereNome($value)
 * @property bool $tipo_pj
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereTipoPj($value)
 * @mixin \Eloquent
 */
class AvaliacaoTipo extends Model
{
    use TenantTrait, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'avaliacoes_tipos';
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


    protected $table = "avaliacoes_tipos";

    protected $fillable = [
        'nome',
        'descricao',
        'empresa_id',
        'ativo',
        'tipo_pj'
    ];

    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'descricao' => 'string',
        'empresa_id' => 'int',
        'ativo' => 'boolean',
        'tipo_pj' => 'boolean',
    ];

    public $timestamps = false;

    public function AvaliacaoTipo()
    {
        return $this->belongsTo(AvaliacaoTopico::class, 'avaliacao_tipo_id', 'id');
    }

    /**
     * @return void
     */
    protected static function booted(): void
    {
        static::created(function ($model) {
            (new Avaliacao())->forgetsCache($model->empresa_id);
        });

        static::updated(function ($model) {
            (new Avaliacao())->forgetsCache($model->empresa_id);
        });
    }
}
