<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtaReuniaoNotificacaoConfig extends Model
{
    protected $table = 'ata_reuniao_notificacao_configs';

    protected $fillable = [
        'empresa_id',
        'usar_dias_uteis',
        'dias_antecedencia',
        'horario_envio',
        'timezone',
        'incluir_gestor_copia',
        'reenviar_no_vencimento',
        'cobrar_apos_atraso',
        'dias_escalonamento',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'usar_dias_uteis' => 'boolean',
        'dias_antecedencia' => 'int',
        'horario_envio' => 'string',
        'timezone' => 'string',
        'incluir_gestor_copia' => 'boolean',
        'reenviar_no_vencimento' => 'boolean',
        'cobrar_apos_atraso' => 'boolean',
        'dias_escalonamento' => 'array',
    ];

    public static function obterOuPadrao(int $empresaId): self
    {
        return self::firstOrCreate(['empresa_id' => $empresaId], [
            'usar_dias_uteis' => false,
            'dias_antecedencia' => 2,
            'horario_envio' => '07:00:00',
            'timezone' => 'America/Sao_Paulo',
            'incluir_gestor_copia' => false,
            'reenviar_no_vencimento' => true,
            'cobrar_apos_atraso' => true,
            'dias_escalonamento' => [1, 3, 5, 10],
        ]);
    }

    public function diasEscalonamento(): array
    {
        $dias = $this->dias_escalonamento ?: [1, 3, 5, 10];

        return collect($dias)
            ->map(fn ($dia) => (int) $dia)
            ->filter(fn ($dia) => $dia > 0)
            ->unique()
            ->values()
            ->all();
    }
}
