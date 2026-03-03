<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\TelefoneFornecedor
 *
 * @property int $id
 * @property string $tipo
 * @property string $pais
 * @property string $numero
 * @property string|null $ramal
 * @property string|null $detalhe
 * @property int $fornecedor_id
 * @property bool $principal
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $tipo_text
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor query()
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereDetalhe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereFornecedorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor wherePais($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereRamal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelefoneFornecedor whereTipo($value)
 * @mixin \Eloquent
 */
class TelefoneFornecedor extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'telefone_fornecedor';
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

    protected $table = 'telefone_fornecedores';

    protected $fillable = ['tipo', 'pais', 'numero', 'ramal', 'detalhe', 'fornecedor_id','principal'];
    protected $casts = [
        'id' => 'int',
        'tipo' => 'string',
        'pais' => 'string',
        'numero' => 'string',
        'ramal' => 'string',
        'detalhe' => 'string',
        'fornecedor_id' => 'int',
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


    public function Formatado()
    {
        return $this->numero . " ({$this->tipo_text}) ";
    }

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
