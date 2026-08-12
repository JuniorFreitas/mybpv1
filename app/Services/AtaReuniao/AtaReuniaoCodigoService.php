<?php

namespace App\Services\AtaReuniao;

use Illuminate\Support\Facades\DB;

class AtaReuniaoCodigoService
{
    public function gerar(int $empresaId, ?int $areaEtiquetaId = null): string
    {
        $ano = now()->year;
        $prefixo = $areaEtiquetaId ? "ATA-AREA{$areaEtiquetaId}-{$ano}-" : "ATA-{$ano}-";

        $ultimoCodigo = DB::table('ata_reuniaos')
            ->where('empresa_id', $empresaId)
            ->where('codigo', 'like', $prefixo . '%')
            ->orderByDesc('codigo')
            ->lockForUpdate()
            ->value('codigo');

        $ultimoNumero = 0;
        if ($ultimoCodigo && preg_match('/(\d{6})$/', $ultimoCodigo, $matches)) {
            $ultimoNumero = (int) $matches[1];
        }

        return $prefixo . str_pad((string) ($ultimoNumero + 1), 6, '0', STR_PAD_LEFT);
    }
}
