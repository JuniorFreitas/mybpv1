<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use function PHPUnit\Framework\isNull;

/**
 * App\Models\AvaliacaoNoventaVencimento
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $feedback_id
 * @property string|null $prazo_dez_inicial
 * @property bool|null $enviado_dez_inicial
 * @property string|null $prazo_cinco_inicial
 * @property bool|null $enviado_cinco_inicial
 * @property string|null $prazo_dia_inicial
 * @property bool|null $enviado_dia_inicial
 * @property string|null $prazo_dez_final
 * @property bool|null $enviado_dez_final
 * @property string|null $prazo_cinco_final
 * @property bool|null $enviado_cinco_final
 * @property string|null $prazo_dia_final
 * @property bool|null $enviado_dia_final
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereEnviadoCincoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereEnviadoCincoInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereEnviadoDezFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereEnviadoDezInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereEnviadoDiaFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereEnviadoDiaInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoCincoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoCincoInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoDezFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoDezInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoDiaFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaVencimento wherePrazoDiaInicial($value)
 */
class AvaliacaoNoventaVencimento extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_id',
        'prazo_dez_inicial',
        'prazo_cinco_inicial',
        'prazo_dia_inicial',
        'prazo_dez_final',
        'prazo_cinco_final',
        'prazo_dia_final',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'prazo_dez_inicial' => 'string',
        'prazo_cinco_inicial' => 'string',
        'prazo_dia_inicial' => 'string',
        'prazo_dez_final' => 'string',
        'prazo_cinco_final' => 'string',
        'prazo_dia_final' => 'string',
    ];

    public $timestamps = false;

    public function setPrazoDezInicialAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['prazo_dez_inicial'] = $data->dataInsert();
    }

    public function getPrazoDezInicialAttribute($value)
    {
        $data = new DataHora($this->attributes['prazo_dez_inicial']);
        return $data->dataCompleta();
    }

    public function setPrazoCincoInicialAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['prazo_cinco_inicial'] = $data->dataInsert();
    }

    public function getPrazoCincoInicialAttribute($value)
    {
        $data = new DataHora($this->attributes['prazo_cinco_inicial']);
        return $data->dataCompleta();
    }

    public function setPrazoDiaInicialAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['prazo_dia_inicial'] = $data->dataInsert();
    }

    public function getPrazoDiaInicialAttribute($value)
    {
        $data = new DataHora($this->attributes['prazo_dia_inicial']);
        return $data->dataCompleta();
    }

    public function setPrazoDezFinalAttribute($value)
    {
        if ($value != null) {
            $data = new DataHora($value);
            $this->attributes['prazo_dez_final'] = $data->dataInsert();
        } else {
            $this->attributes['prazo_dez_final'] = null;
        }
    }

    public function getPrazoDezFinalAttribute($value)
    {
        if ($value != null) {
            $data = new DataHora($this->attributes['prazo_dez_final']);
            return $data->dataCompleta();
        } else {
            $this->attributes['prazo_dez_final'] = null;
        }
    }

    public function setPrazoCincoFinalAttribute($value)
    {
        if ($value != null) {
            $data = new DataHora($value);
            $this->attributes['prazo_cinco_final'] = $data->dataInsert();
        } else {
            $this->attributes['prazo_cinco_final'] = null;
        }
    }

    public function getPrazoCincoFinalAttribute($value)
    {
        if ($value != null) {
            $data = new DataHora($this->attributes['prazo_cinco_final']);
            return $data->dataCompleta();
        } else {
            $this->attributes['prazo_cinco_final'] = null;
        }
    }

    public function setPrazoDiaFinalAttribute($value)
    {
        if ($value != null) {
            $data = new DataHora($value);
            $this->attributes['prazo_dia_final'] = $data->dataInsert();
        } else {
            $this->attributes['prazo_dia_final'] = null;
        }
    }

    public function getPrazoDiaFinalAttribute($value)
    {
        if ($value != null) {
            $data = new DataHora($this->attributes['prazo_dia_final']);
            return $data->dataCompleta();
        } else {
            $this->attributes['prazo_dia_final'] = null;
        }
    }

    public function FeedbackCurriculo()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }
}
