import { test, expect } from '@playwright/test';
import { expectAutenticado, expectPaginaOperacional } from './helpers';

test.describe('Regressão — Planejamento / Movimentação (P0)', () => {
    test('PLA-UI-01 Hub movimentação carrega', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/planejamento/movimentacao');
        await expectAutenticado(page);
        await expect(page.locator('body')).toContainText(/moviment|férias|ferias|demiss|transfer/i);
    });

    test('PLA-UI-02 Requisição de vaga carrega', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/planejamento/requisicao-vaga');
        await expect(page.locator('body')).toContainText(/requisi|vaga/i);
    });

    test('PLA-UI-03 Férias prevista — index redireciona ao hub', async ({ page }) => {
        await page.goto('/g/planejamento/movimentacao/ferias-prevista');
        await expectAutenticado(page);
        await expect(page).toHaveURL(/\/g\/planejamento\/movimentacao\/?/);
    });

    test('PLA-UI-04 Demissão prevista — index redireciona ao hub', async ({ page }) => {
        await page.goto('/g/planejamento/movimentacao/demissao-prevista');
        await expectAutenticado(page);
        await expect(page).toHaveURL(/\/g\/planejamento\/movimentacao\/?/);
        await expect(page.locator('body')).toContainText(/moviment/i);
    });

    test('PLA-UI-05 Transferência prevista — index redireciona ao hub', async ({ page }) => {
        await page.goto('/g/planejamento/movimentacao/transferencia-prevista');
        await expectAutenticado(page);
        await expect(page).toHaveURL(/\/g\/planejamento\/movimentacao\/?/);
    });

    test('PLA-UI-06 Valor extra previsto — index redireciona ao hub', async ({ page }) => {
        await page.goto('/g/planejamento/movimentacao/valor-extra-prevista');
        await expectAutenticado(page);
        await expect(page).toHaveURL(/\/g\/planejamento\/movimentacao\/?/);
    });
});
