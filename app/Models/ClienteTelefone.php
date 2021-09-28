<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ClienteTelefone
 *
 * @property int $id
 * @property string $tipo
 * @property string $pais
 * @property string $numero
 * @property string|null $ramal
 * @property string|null $detalhe
 * @property int $cliente_id
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $tipo_text
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereDetalhe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone wherePais($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereRamal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone whereTipo($value)
 * @mixin \Eloquent
 * @property int $principal
 * @method static \Illuminate\Database\Eloquent\Builder|ClienteTelefone wherePrincipal($value)
 */
class ClienteTelefone extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'cliente_telefone';
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

    protected $table = 'cliente_telefones';
    protected $fillable = ['id','tipo', 'pais', 'numero', 'ramal', 'detalhe', 'cliente_id', 'principal'];
    protected $casts = [
        'id' => 'int',
        'tipo' => 'string',
        'pais' => 'string',
        'numero' => 'string',
        'ramal' => 'string',
        'detalhe' => 'string',
        'cliente_id' => 'int',
        'principal' => 'boolean',
    ];

    public function usesTimestamps(): bool
    {
        return false;
    }

    public const RESIDENCIAL = "residencial";
    public const CELULAR = "celular";
    public const COMERCIAL = "comercial";
    public const WHATS = "whatsapp";

    protected $appends = ['tipoText'];

    //https://laravel.com/docs/5.7/eloquent-mutators
    public function getTipoTextAttribute()
    {
        switch ($this->tipo) {
            case self::RESIDENCIAL:
                return "Residencial";
                break;

            case self::CELULAR:
                return "Celular";
                break;

            case self::WHATS:
                return "WhatsApp";
                break;

            case self::COMERCIAL:
                return "Comercial";
                break;

            default:
                return "Residencial";
                break;
        }
    }
}
