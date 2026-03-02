<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\TelefoneCurriculo
 *
 * @property int $id
 * @property string $tipo
 * @property string $pais
 * @property string $numero
 * @property string|null $ramal
 * @property string|null $detalhe
 * @property int $curriculo_id
 * @property bool $principal
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $sonumero
 * @property-read mixed $tipo_text
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo query()
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereDetalhe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo wherePais($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereRamal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneCurriculo whereTipo($value)
 * @mixin \Eloquent
 */
class TelefoneCurriculo extends Model
{
    use LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'telefone_curriculo';
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

    protected $table = 'curriculo_telefone';
    protected $fillable = [
        'tipo',
        'pais',
        'numero',
        'ramal',
        'detalhe',
        'curriculo_id',
        'principal'
    ];
    protected $casts = [
        'id' => 'int',
        'tipo' => 'string',
        'pais' => 'string',
        'numero' => 'string',
        'ramal' => 'string',
        'detalhe' => 'string',
        'curriculo_id' => 'int',
        'principal' => 'boolean',
    ];

    public function usesTimestamps(): bool
    {
        return false;
    }

    public static $RESIDENCIAL = "residencial";
    public static $CELULAR = "celular";
    public static $COMERCIAL = "comercial";
    public static $WHATS = "whatsapp";

    const TIPO_RESIDENCIAL = "residencial";
    const TIPO_CELULAR = "celular";
    const TIPO_COMERCIAL = "comercial";
    const TIPO_WHATS = "whatsapp";

    const TIPOS = [
        self::TIPO_RESIDENCIAL,
        self::TIPO_CELULAR,
        self::TIPO_COMERCIAL,
        self::TIPO_WHATS,
    ];

    protected $appends = ['tipoText', 'sonumero'];

    public function Formatado()
    {
        return $this->numero . " ({$this->tipo_text}) ";
    }

    public function getSonumeroAttribute()
    {
        return preg_replace('/[^0-9]/i', '', $this->pais . $this->numero);
    }

    //https://laravel.com/docs/5.7/eloquent-mutators
    public function getTipoTextAttribute()
    {
        switch ($this->tipo) {
            case self::$RESIDENCIAL:
                return "Residencial";
                break;

            case self::$CELULAR:
                return "Celular";
                break;

            case self::$WHATS:
                return "WhatsApp";
                break;

            case self::$COMERCIAL:
                return "Comercial";
                break;

            default:
                return "Residencial";
                break;
        }
    }
}
