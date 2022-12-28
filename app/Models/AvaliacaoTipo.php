<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AvaliacaoTipo
 *
 * @property int $id
 * @property string $nome
 * @property string $descricao
 * @property int $empresa_id
 * @property int $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Avaliacao[] $Avaliacoes
 * @property-read int|null $avaliacoes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AvaliacaoTopico[] $AvaliacoesTopicos
 * @property-read int|null $avaliacoes_topicos_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereNome($value)
 * @mixin \Eloquent
 * @property-read \App\Models\AvaliacaoTopico $AvaliacaoTipo
 */
class AvaliacaoTipo extends Model
{
    use HasFactory, TenantTrait, LogsActivity;

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
        'ativo'
    ];

    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'descricao' => 'string',
        'empresa_id' => 'int',
        'ativo' => 'boolean'
    ];

    public $timestamps = false;

    public function AvaliacaoTipo()
    {
        return $this->belongsTo(AvaliacaoTopico::class, 'avaliacao_tipo_id', 'id');
    }
}
