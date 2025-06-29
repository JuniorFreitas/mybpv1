<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\UsuarioTelefone
 *
 * @property int $id
 * @property string $tipo
 * @property string $pais
 * @property string $numero
 * @property string|null $ramal
 * @property string|null $detalhe
 * @property int $user_id
 * @property bool $principal
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $sonumero
 * @property-read mixed $tipo_text
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone query()
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereDetalhe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone wherePais($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereRamal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UsuarioTelefone whereTipo($value)
 * @property-read \App\Models\User $user
 * @mixin \Eloquent
 */
class UsuarioTelefone extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'usuarios_telefone';
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

    protected $table = 'usuarios_telefone';
    protected $fillable = [
        'tipo',
        'pais',
        'numero',
        'ramal',
        'detalhe',
        'user_id',
        'principal'
    ];
    protected $casts = [
        'id' => 'int',
        'tipo' => 'string',
        'pais' => 'string',
        'numero' => 'string',
        'ramal' => 'string',
        'detalhe' => 'string',
        'user_id' => 'int',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    //https://laravel.com/docs/5.7/eloquent-mutators
    public function getTipoTextAttribute()
    {
        switch ($this->tipo) {

            case self::$CELULAR:
                return "Celular";

            case self::$WHATS:
                return "WhatsApp";

            case self::$COMERCIAL:
                return "Comercial";

            default:
                return self::TIPO_RESIDENCIAL;
        }
    }
}
