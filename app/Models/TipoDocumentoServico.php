<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class TipoDocumentoServico extends Model
{
    use HasFactory, TenantTrait;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'tipo_documento_servicos';
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'empresa_id',
        'tipo_empresa',
        'ativo'
    ];

    protected $casts = [
        'nome' => 'string',
        'empresa_id' => 'int',
        'tipo_empresa' => 'boolean',
        'ativo' => 'boolean'
    ];
}
