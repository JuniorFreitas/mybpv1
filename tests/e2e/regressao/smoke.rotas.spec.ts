import { test, expect } from '@playwright/test';
import {
    anotarSkipPermissao,
    expectAutenticado,
    expectPaginaOperacional,
    rotasPorPrioridade,
} from './helpers';

/**
 * Smoke de regressão: cada rota crítica deve abrir autenticada sem 5xx.
 * Casos sem permissão (403) são anotados quando `permitirNegado`.
 */
test.describe('Regressão — smoke de rotas P0/P1', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/g/dashboard');
        await expectAutenticado(page);
    });

    for (const rota of rotasPorPrioridade('P0', 'P1')) {
        test(`${rota.id} ${rota.modulo} → ${rota.path}`, async ({ page }) => {
            const resultado = await expectPaginaOperacional(page, rota.path, {
                permitirNegado: rota.permitirNegado ?? false,
            });

            if (resultado === 'negado') {
                anotarSkipPermissao(rota.id, rota.path);
                return;
            }

            // Algumas rotas resource redirecionam ao hub (movimentação)
            const url = page.url();
            const okPath =
                url.includes(rota.path) ||
                (rota.path.includes('movimentacao/') && url.includes('/g/planejamento/movimentacao'));
            expect(okPath).toBeTruthy();
        });
    }
});

test.describe('Regressão — smoke P2 (opcional)', () => {
    test.skip(process.env.PLAYWRIGHT_INCLUIR_P2 !== '1', 'Defina PLAYWRIGHT_INCLUIR_P2=1 para P2');

    for (const rota of rotasPorPrioridade('P2')) {
        test(`${rota.id} ${rota.modulo}`, async ({ page }) => {
            await expectPaginaOperacional(page, rota.path, { permitirNegado: true });
        });
    }
});
