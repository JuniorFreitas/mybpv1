import { test, expect } from '@playwright/test';
import { expectAutenticado, expectPaginaOperacional } from './helpers';

test.describe('Regressão — Recrutamento (P0)', () => {
    test('REC-UI-01 Lista de recrutamentos carrega', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/curriculos/recrutamentos');
        await expectAutenticado(page);
        await expect(page.locator('body')).toContainText(/recrut|currícul|curricul|candidato|vaga/i);
    });

    test('REC-UI-02 Filtros/listagem interativos presentes', async ({ page }) => {
        await page.goto('/g/curriculos/recrutamentos');
        await expectAutenticado(page);
        const filtro = page.locator('input, select, .filtro, [class*="filtro"], [class*="Filtro"]').first();
        await expect(filtro).toBeVisible({ timeout: 20_000 });
    });
});
