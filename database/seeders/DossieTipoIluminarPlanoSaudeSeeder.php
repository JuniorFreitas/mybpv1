<?php

namespace Database\Seeders;

use App\Models\DossieTipo;
use Illuminate\Database\Seeder;

class DossieTipoIluminarPlanoSaudeSeeder extends Seeder
{
    public const EMPRESA_ID = 57861;

    public function run(): void
    {
        $global = DossieTipo::query()
            ->whereNull('empresa_id')
            ->where('tipo', 'PlanoSaudeAssinado')
            ->first();

        $payload = [
            'empresa_id' => self::EMPRESA_ID,
            'tipo' => 'PlanoSaudeAssinado',
            'chave' => 'plano_saude_assinado',
            'label' => 'DECLARAÇÃO DE CIÊNCIA E CONCORDÂNCIA – PLANO DE SAÚDE HAPVIDA',
            'tipo_modelo' => 'declaracaocienciaplanosaude',
            'tipo_documento' => null,
            'tem_modelo' => true,
            'permite_assinatura' => false,
            'ordem' => $global?->ordem ?? 30,
            'ativo' => true,
        ];

        $existente = DossieTipo::query()
            ->where('empresa_id', self::EMPRESA_ID)
            ->where('tipo', 'PlanoSaudeAssinado')
            ->first();

        if ($existente) {
            $existente->update($payload);
        } else {
            DossieTipo::create($payload);
        }

        DossieTipo::limparCache();
    }
}
