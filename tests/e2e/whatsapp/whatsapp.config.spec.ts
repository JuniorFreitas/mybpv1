import { test, expect } from '@playwright/test';
import { expectAutenticado, fetchWhatsappStatus, pularSeEmpresaWhatsappInvalida, ROTAS } from './helpers';

async function abrirConfigWhatsappOuPular(
    page: import('@playwright/test').Page,
    request: import('@playwright/test').APIRequestContext,
): Promise<boolean> {
    const status = await fetchWhatsappStatus(request);
    pularSeEmpresaWhatsappInvalida(status);

    const response = await page.goto(ROTAS.configWhatsapp);
    if (response?.status() === 403) {
        test.skip(true, 'Usuário sem habilidade configuracao_whatsapp');
        return false;
    }
    await expectAutenticado(page);
    await expect(page.getByRole('link', { name: 'Dados de contato' })).toBeVisible({ timeout: 30_000 });
    return true;
}

test.describe('Configuração WhatsApp — RH', () => {
    test('tela Customizações → WhatsApp carrega', async ({ page, request }) => {
        await abrirConfigWhatsappOuPular(page, request);
        await expect(page.getByRole('link', { name: 'Templates' })).toBeVisible();
    });

    test('aba Módulos exibe toggles dos módulos', async ({ page, request }) => {
        await abrirConfigWhatsappOuPular(page, request);
        await page.getByRole('link', { name: 'Módulos' }).click();
        await expect(page.getByText('Locais de envio habilitados')).toBeVisible();
        await expect(page.getByText('Recrutamento', { exact: true }).first()).toBeVisible();
    });

    test('alerta quando empresa sem WhatsApp liberado', async ({ page, request }) => {
        const status = await fetchWhatsappStatus(request);
        pularSeEmpresaWhatsappInvalida(status);

        const response = await page.goto(ROTAS.configWhatsapp);
        if (response?.status() === 403) {
            test.skip(true, 'Usuário sem habilidade configuracao_whatsapp');
            return;
        }

        if (!status.whatsapp_liberado) {
            await expect(page.getByText(/whatsapp.*não.*habilitado|sem whatsapp/i).first()).toBeVisible();
        }
    });
});

test.describe('Configuração WhatsApp — Admin Clientes', () => {
    test.skip(true, 'Requer perfil admin MyBP (administracao_clientes)');

    test('aba WHATSAPP no cliente', async ({ page }) => {
        await page.goto(ROTAS.adminClientes);
        // TODO: abrir cliente de teste → aba WHATSAPP
    });
});
