<?php

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Heurística TD-003 / SEC-008: em arquivos de alto risco, cada linha com
 * withoutGlobalScopes deve ter empresa_id (ou apelido/token/find($) de contexto)
 * nas linhas seguintes.
 *
 * Nota: ControleExameController::getFichaPdf usa token público — bypass legítimo
 * documentado em SEC-008; não entra na watchlist de filtro por empresa_id.
 */
class WithoutGlobalScopesHeuristicTest extends TestCase
{
    private const WATCHLIST = [
        'app/Helpers/RHHelper.php',
        'app/Services/AssinaturaDigital/AssinaturaCotaService.php',
        'app/Services/Lotacao/LotacaoDoCentroCusto.php',
        'app/Domain/Whatsapp/Services/WhatsappNotificationGateService.php',
        'app/Domain/Whatsapp/Services/WhatsappConfigService.php',
        'app/Services/CartaOferta/CartaOfertaInternaService.php',
        'app/Services/AssinaturaDigital/AssinaturaDigitalService.php',
    ];

    /** Selects multilinha antes de ->find($empresaId). */
    private const JANELA_LINHAS = 20;

    #[Test]
    public function watchlist_reaplica_filtro_tenant_apos_without_global_scopes(): void
    {
        $falhas = [];

        foreach (self::WATCHLIST as $relative) {
            $path = base_path($relative);
            if (!is_file($path)) {
                $falhas[] = "Arquivo ausente: {$relative}";
                continue;
            }

            $lines = file($path, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $i => $line) {
                if (!str_contains($line, 'withoutGlobalScopes')) {
                    continue;
                }
                if (str_contains(ltrim($line), '*') || str_contains(ltrim($line), '//')) {
                    continue;
                }

                $janela = implode("\n", array_slice($lines, $i, self::JANELA_LINHAS));
                $ok = preg_match('/empresa_id|whereEmpresaId|apelido|->find\(\$|whereToken/i', $janela) === 1;
                if (!$ok) {
                    $falhas[] = sprintf('%s:%d sem filtro tenant aparente', $relative, $i + 1);
                }
            }
        }

        $this->assertSame([], $falhas, implode("\n", $falhas));
    }
}
