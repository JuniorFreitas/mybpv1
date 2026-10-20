<?php

namespace App\Models;

use App\Models\Concerns\HasActivitylogOptions;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Padrão de treinamento por empresa (agrupamento + carteira/etiqueta + lotações).
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $nome
 * @property string $slug
 * @property bool $ativo
 * @property bool $tem_carteira
 * @property int|null $carteira_treinamento_id
 * @property bool $tem_etiqueta_bloqueio
 * @property int|null $etiqueta_bloqueio_id
 */
class PadraoTreinamento extends Model
{
    use LogsActivity, HasActivitylogOptions, TenantTrait;

    protected static $logName = 'PadraoTreinamento';

    protected $table = 'padroes_treinamento';

    protected $fillable = [
        'empresa_id',
        'nome',
        'slug',
        'ativo',
        'tem_carteira',
        'carteira_treinamento_id',
        'tem_etiqueta_bloqueio',
        'etiqueta_bloqueio_id',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ativo' => 'boolean',
        'tem_carteira' => 'boolean',
        'carteira_treinamento_id' => 'int',
        'tem_etiqueta_bloqueio' => 'boolean',
        'etiqueta_bloqueio_id' => 'int',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id', 'id');
    }

    public function CarteiraTreinamento()
    {
        return $this->belongsTo(CarteiraTreinamento::class, 'carteira_treinamento_id', 'id');
    }

    public function EtiquetaBloqueio()
    {
        return $this->belongsTo(EtiquetaBloqueio::class, 'etiqueta_bloqueio_id', 'id');
    }

    public function Lotacoes()
    {
        return $this->hasMany(PadraoTreinamentoLotacao::class, 'padrao_treinamento_id', 'id');
    }

    public function Vencimentos()
    {
        return $this->belongsToMany(
            Vencimento::class,
            'padrao_treinamento_vencimento',
            'padrao_treinamento_id',
            'vencimento_id'
        );
    }

    public function Admissoes()
    {
        return $this->hasMany(Admissao::class, 'padrao_treinamento_id', 'id');
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    /**
     * Padrões válidos para Matriz (is_matriz) ou para a filial informada.
     */
    public function scopeParaLotacao(Builder $query, bool $isMatriz, ?int $clienteFilialId = null): Builder
    {
        return $query->whereHas('Lotacoes', function (Builder $q) use ($isMatriz, $clienteFilialId) {
            if ($isMatriz) {
                $q->where('is_matriz', true);
            } else {
                $q->where('cliente_filial_id', $clienteFilialId);
            }
        });
    }

    /**
     * Monta array no formato antigo config_carteira para dual-read no PDF.
     */
    public function toConfigCarteira(): array
    {
        $config = [
            'cabecalho_img' => '',
            'verso_img' => '',
            'exibir_etiqueta_bloqueio' => false,
            'ramal_emergencia' => '1199',
            'bloqueio_texto_nao_use' => '',
            'bloqueio_texto_demissao' => '',
            'bloqueio_texto_cuidado' => '',
            'bloqueio_texto_homens_trabalhando' => '',
        ];

        if ($this->tem_carteira && $this->CarteiraTreinamento) {
            $config['cabecalho_img'] = (string) ($this->CarteiraTreinamento->cabecalho_img ?? '');
            $config['verso_img'] = (string) ($this->CarteiraTreinamento->verso_img ?? '');
            $config['identificador'] = (string) ($this->CarteiraTreinamento->identificador ?: 'chapa');
            $config['blade_carteira'] = $this->CarteiraTreinamento->blade;
        }

        if ($this->tem_etiqueta_bloqueio && $this->EtiquetaBloqueio) {
            $config = array_merge($config, $this->EtiquetaBloqueio->toConfigCarteiraFragment());
        }

        return $config;
    }
}
