<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * Segmento de treinamento (ALUMAR, VALE, Hidro, etc.) para carteira e vencimentos por padrão.
 * config_carteira: cabecalho_img, verso_img (carteira); exibir_etiqueta_bloqueio, ramal_emergencia,
 * bloqueio_texto_nao_use, bloqueio_texto_demissao, bloqueio_texto_cuidado, bloqueio_texto_homens_trabalhando (etiqueta bloqueio).
 *
 * @property int $id
 * @property string $nome
 * @property string $slug
 * @property bool $ativo
 * @property array|null $config_carteira
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SegmentoTreinamento extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'SegmentoTreinamento';
    protected $table = 'segmentos_treinamento';

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
        'slug',
        'ativo',
        'config_carteira',
    ];

    protected $casts = [
        'id' => 'int',
        'ativo' => 'boolean',
        'config_carteira' => 'array',
    ];

    public function Vencimentos()
    {
        return $this->hasMany(Vencimento::class, 'segmento_treinamento_id', 'id');
    }

    public function Clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_segmento_treinamento', 'segmento_treinamento_id', 'cliente_id');
    }

    public function Admissoes()
    {
        return $this->hasMany(Admissao::class, 'segmento_treinamento_id', 'id');
    }

    /**
     * Slug do segmento ALUMAR (default do sistema).
     */
    public const SLUG_ALUMAR = 'alumar';

    /**
     * Retorna o ID do segmento ALUMAR para uso como default quando segmento_treinamento_id for null.
     */
    public static function getIdAlumar(): ?int
    {
        $segmento = self::where('slug', self::SLUG_ALUMAR)->first();

        return $segmento ? $segmento->id : null;
    }
}
