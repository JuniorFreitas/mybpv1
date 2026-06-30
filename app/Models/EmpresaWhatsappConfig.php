<?php

namespace App\Models;

use App\Domain\Whatsapp\Services\WhatsappConfigService;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

class EmpresaWhatsappConfig extends Model
{
    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'EmpresaWhatsappConfig';

    protected $table = 'empresa_whatsapp_configs';

    protected $fillable = [
        'empresa_id',
        'nome_exibicao',
        'telefone_contato',
        'endereco_completo',
        'texto_assinatura',
        'modulos_habilitados',
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'modulos_habilitados' => 'array',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->descricao = 'Configuração WhatsApp da empresa';
    }

    public function Empresa()
    {
        return $this->belongsTo(User::class, 'empresa_id', 'id');
    }

    protected static function booted(): void
    {
        $invalidar = static function (EmpresaWhatsappConfig $config): void {
            app(WhatsappConfigService::class)->invalidateCache((int) $config->empresa_id);
        };

        static::saved($invalidar);
        static::deleted($invalidar);
    }
}
