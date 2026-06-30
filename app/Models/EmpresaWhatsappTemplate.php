<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

class EmpresaWhatsappTemplate extends Model
{
    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'EmpresaWhatsappTemplate';

    protected $table = 'empresa_whatsapp_templates';

    protected $fillable = [
        'empresa_id',
        'tipo_mensagem',
        'corpo',
        'ativo',
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'ativo' => 'boolean',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->descricao = "Template WhatsApp: {$this->tipo_mensagem}";
    }

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id', 'id');
    }
}
