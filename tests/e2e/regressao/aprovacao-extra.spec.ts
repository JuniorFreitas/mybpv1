import { test, expect } from '@playwright/test';
import { expectPaginaOperacional } from './helpers';

test.describe('Regressão — Aprovação Extra (P0)', () => {
    test('CFG-UI-01 Configuração carrega ou nega com mensagem', async ({ page }) => {
        const r = await expectPaginaOperacional(page, '/g/administracao/aprovacao-extra-config', {
            permitirNegado: true,
        });
        if (r === 'ok') {
            await expect(page.locator('body')).toContainText(/aprova|tipo|processo|usuário|usuario/i);
        }
    });
});
