import { test, expect } from '@playwright/test';
import { expectAutenticado, expectPaginaOperacional } from './helpers';

test.describe('Regressão — Admissão (P0)', () => {
    test('ADM-UI-01 Processo carrega listagem/filtros', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/admissao/admissao');
        await expectAutenticado(page);
        await expect(page.locator('body')).toContainText(/admiss|processo|filtro|candidato|colaborador/i);
    });

    test('ADM-UI-02 Pré-admissão carrega', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/preadmissao');
        await expect(page.locator('body')).toContainText(/pré-admiss|pre-admiss|preadmiss|documento|exame|candidato/i);
    });

    test('ADM-UI-03 Pós-admissão carrega', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/posadmissao');
        await expect(page.locator('body')).toContainText(/pós-admiss|pos-admiss|colaborador|ativo/i);
    });

    test('ADM-UI-04 Histórico carrega', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/historico');
        await expect(page.locator('body')).toContainText(/históric|historico|admiss/i);
    });

    test('ADM-UI-05 Controle de exames carrega', async ({ page }) => {
        await expectPaginaOperacional(page, '/g/controle-exames');
        await expect(page.locator('body')).toContainText(/exame|aso|encaminh/i);
    });
});
