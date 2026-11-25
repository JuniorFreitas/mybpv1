import { test, expect } from '@playwright/test';
import { expectPaginaOperacional } from './helpers';

test.describe('Regressão — Cadastros base (P0/P1)', () => {
    test('CAD-UI-01 Centro de custo', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/cadastro/centrocusto');
        await expect(page.locator('body')).toContainText(/centro|custo/i);
    });

    test('CAD-UI-02 Vagas / cargos', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/cadastro/vagas');
        await expect(page.locator('body')).toContainText(/vaga|cargo/i);
    });

    test('CAD-UI-03 Documentos pré-admissão (se permitido)', async ({ page }) => {
        const r = await expectPaginaOperacional(page, '/g/cadastro/documentos-preadmissao', {
            permitirNegado: true,
        });
        if (r === 'ok') {
            await expect(page.locator('body')).toContainText(/documento|pré-admiss|preadmiss/i);
        }
    });
});
